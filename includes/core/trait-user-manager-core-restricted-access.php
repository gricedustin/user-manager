<?php
/**
 * Restricted Access helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Restricted_Access_Trait {
	private static string $restricted_access_trusted_ip_cookie_name = 'um_restricted_access_ip_trust';

	/**
	 * Query-string parameter used to deliver a signed one-time access grant
	 * across a POST→GET redirect when cookies are dropped by proxies/CDNs.
	 */
	private const RESTRICTED_ACCESS_GRANT_QUERY_PARAM = 'um_ra_grant';

	/**
	 * Transient key prefix for one-time grant tokens stored in the options table.
	 */
	private const RESTRICTED_ACCESS_GRANT_TRANSIENT_PREFIX = 'um_ra_grant_';

	/**
	 * Action name used by the admin-ajax / admin-post style password submit endpoint.
	 */
	private const RESTRICTED_ACCESS_PASSWORD_ACTION = 'um_restricted_access_submit';

	/**
	 * Runtime password error for the current request.
	 */
	private static string $restricted_access_password_error = '';

	/**
	 * Cached geolocation labels by IP to avoid repeated lookups per request.
	 *
	 * @var array<string,string>
	 */
	private static array $restricted_access_geo_cache = [];

	/**
	 * Queued background-overlay context for the current request.
	 *
	 * @var array{settings:array<string,mixed>,show_password_form:bool}|null
	 */
	private static ?array $restricted_access_background_overlay_context = null;

	/**
	 * Boot restricted access controls when enabled.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_restricted_access(array $settings): void {
		if (empty($settings['restricted_access_enabled'])) {
			return;
		}

		add_action('template_redirect', [__CLASS__, 'maybe_enforce_restricted_access'], 1);

		// Always mark ALL public front-end requests as uncacheable whenever
		// the add-on is active — no matter whether the request ends up gated,
		// granted, or re-prompted. This prevents page caches (WP Rocket,
		// LiteSpeed, W3TC, WP Super Cache, Cloudflare edge rules, etc.) from
		// storing an overlay/403 for a URL (most commonly the home page) and
		// then serving that stale overlay to already-authenticated visitors.
		//
		// The hook fires at `send_headers` priority 1 so it runs before the
		// usual template output stage and before most cache layers make their
		// "should I store this?" decision. We still also mark on
		// template_redirect as a belt-and-suspenders for cache layers that
		// check later in the pipeline.
		add_action('send_headers', [__CLASS__, 'restricted_access_mark_public_request_uncacheable'], 1, 0);
		add_action('template_redirect', [__CLASS__, 'restricted_access_mark_public_request_uncacheable'], 0, 0);

		// Lightweight, cache-friendly AJAX + classic POST endpoints. These run
		// regardless of where the request was initiated from so a submission
		// from a background-overlay page still works when the normal
		// template_redirect path does not re-serve the restricted gate.
		$ajax_action = self::RESTRICTED_ACCESS_PASSWORD_ACTION;
		add_action('wp_ajax_' . $ajax_action, [__CLASS__, 'handle_restricted_access_password_ajax']);
		add_action('wp_ajax_nopriv_' . $ajax_action, [__CLASS__, 'handle_restricted_access_password_ajax']);
		add_action('admin_post_' . $ajax_action, [__CLASS__, 'handle_restricted_access_password_post']);
		add_action('admin_post_nopriv_' . $ajax_action, [__CLASS__, 'handle_restricted_access_password_post']);
	}

	/**
	 * Mark the current public-facing request as uncacheable for page-cache
	 * layers (WP Rocket, LiteSpeed, W3TC, WP Super Cache, Hummingbird, etc.)
	 * and for browser/CDN caches. This is the main fix for "the home page
	 * keeps re-prompting for the password": without it, an already-cached
	 * overlay at /, /home, etc., would be served to authenticated users.
	 */
	public static function restricted_access_mark_public_request_uncacheable(): void {
		// Do not affect admin, AJAX, cron, or REST requests — those have
		// their own caching semantics and should not be marked no-store here.
		if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
			return;
		}
		if (defined('REST_REQUEST') && REST_REQUEST) {
			return;
		}

		if (!defined('DONOTCACHEPAGE')) {
			define('DONOTCACHEPAGE', true);
		}
		if (!defined('DONOTCACHEOBJECT')) {
			define('DONOTCACHEOBJECT', true);
		}
		if (!defined('DONOTCACHEDB')) {
			define('DONOTCACHEDB', true);
		}

		// Signal to WP Rocket that this page must not be cached.
		if (!defined('DONOTROCKETCACHE')) {
			define('DONOTROCKETCACHE', true);
		}

		// Signal to LiteSpeed Cache that this page must not be cached.
		do_action('litespeed_control_set_nocache', 'um_restricted_access');

		if (headers_sent()) {
			return;
		}

		// Make sure responsive cache layers see a Cache-Control: no-store AND
		// a Vary: Cookie so cached entries key on cookie state. nocache_headers()
		// alone still lets some layers store the response; these explicit
		// headers cover the remaining edge cases.
		header('Cache-Control: no-store, no-cache, private, must-revalidate, max-age=0', true);
		header('Pragma: no-cache', true);
		header('Expires: 0', true);
		header('Vary: Cookie', false);
	}

	/**
	 * Create restricted access history table when missing.
	 */
	public static function maybe_create_restricted_access_history_table(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'um_restricted_access_history';
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ip_address varchar(100) NOT NULL DEFAULT '',
			ip_location varchar(255) NOT NULL DEFAULT '',
			browser varchar(255) NOT NULL DEFAULT '',
			url_accessed_from text,
			password_used text,
			failed_password text,
			is_success tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY created_at (created_at),
			KEY is_success (is_success)
		) {$charset_collate};";
		dbDelta($sql);
	}

	/**
	 * Restrict front-end access based on add-on configuration.
	 */
	public static function maybe_enforce_restricted_access(): void {
		if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
			return;
		}
		if (defined('REST_REQUEST') && REST_REQUEST) {
			return;
		}

		$settings = self::get_settings();
		if (empty($settings['restricted_access_enabled'])) {
			return;
		}

		// Always allow site administrators through restricted-access gates.
		if (is_user_logged_in() && current_user_can('manage_options')) {
			return;
		}

		// Logged-in users can view by default unless their role is excluded.
		if (is_user_logged_in()) {
			if (self::restricted_access_current_user_has_excluded_role($settings)) {
				self::maybe_render_restricted_access_overlay($settings, false);
			}
			return;
		}

		// Always process an inline password POST first so it cannot be lost
		// behind a redirect/overlay branch decision or a cached response. A
		// successful submission issues its own 303 redirect and exits.
		self::restricted_access_maybe_handle_password_submission($settings);

		// Signed one-time grant token in the URL. Used as a cookie-drop
		// recovery path after a password submit or AJAX success, and also
		// works across CDNs / proxies that may strip Set-Cookie on 3xx.
		if (self::restricted_access_consume_grant_query_param($settings)) {
			self::restricted_access_redirect_removing_grant_param();
		}

		// A matching appended URL string grants timed access.
		if (self::restricted_access_request_matches_access_string($settings)) {
			self::restricted_access_log_access_attempt('', true, '');
			self::restricted_access_set_access_cookie($settings);
			return;
		}

		// Existing timed access cookie grants access.
		if (self::restricted_access_has_valid_access_cookie()) {
			// Refresh the cookie so expiry slides forward and normalize
			// path/domain scope for cross-page navigation consistency.
			self::restricted_access_set_access_cookie($settings);
			return;
		}

		// Optional IP trust bypass after successful password submission.
		if (self::restricted_access_should_trust_ip_after_password_success($settings) && self::restricted_access_has_valid_trusted_ip_cookie()) {
			return;
		}

		$behavior = self::restricted_access_get_logged_out_behavior($settings);
		if ($behavior === 'redirect_my_account') {
			$target_url = self::restricted_access_get_my_account_url();
			// On the target login page, allow rendering and do not show overlay.
			if (self::restricted_access_current_request_matches_url($target_url)) {
				return;
			}
			wp_safe_redirect($target_url);
			exit;
		}
		if ($behavior === 'redirect_wp_admin') {
			$target_url = admin_url();
			wp_safe_redirect($target_url);
			exit;
		}

		$shared_password = self::restricted_access_get_shared_password($settings);
		$has_password    = $shared_password !== '';

		// Overlay mode: when a shared password is set, show a gate form.
		if ($has_password) {
			self::maybe_render_restricted_access_overlay($settings, true);
		}

		self::maybe_render_restricted_access_overlay($settings, false);
	}

	/**
	 * Render overlay immediately or queue background HTML overlay mode.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function maybe_render_restricted_access_overlay(array $settings, bool $show_password_form): void {
		if (self::restricted_access_should_render_background_overlay($settings)) {
			self::queue_restricted_access_background_overlay($settings, $show_password_form);
			return;
		}
		self::render_restricted_access_overlay_and_exit($settings, $show_password_form);
	}

	/**
	 * Whether restricted access should allow normal page HTML rendering in the background.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_should_render_background_overlay(array $settings): bool {
		return !empty($settings['restricted_access_render_background_html_for_social_meta']);
	}

	/**
	 * Queue overlay assets/markup hooks so full template HTML still renders first.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function queue_restricted_access_background_overlay(array $settings, bool $show_password_form): void {
		nocache_headers();
		self::restricted_access_mark_public_request_uncacheable();
		if (!is_array(self::$restricted_access_background_overlay_context)) {
			self::$restricted_access_background_overlay_context = [
				'settings' => $settings,
				'show_password_form' => $show_password_form,
			];
			add_action('wp_head', [__CLASS__, 'render_restricted_access_background_overlay_head_assets'], 999);
			add_action('wp_footer', [__CLASS__, 'render_restricted_access_background_overlay_markup'], 999);
			return;
		}
		self::$restricted_access_background_overlay_context['settings'] = $settings;
		if ($show_password_form) {
			self::$restricted_access_background_overlay_context['show_password_form'] = true;
		}
	}

	/**
	 * Print CSS/JS needed for background overlay mode.
	 */
	public static function render_restricted_access_background_overlay_head_assets(): void {
		$context = self::$restricted_access_background_overlay_context;
		if (!is_array($context) || !isset($context['settings']) || !is_array($context['settings'])) {
			return;
		}
		$settings = $context['settings'];
		$bg_color = self::restricted_access_get_overlay_background_color($settings);
		$text_color = self::restricted_access_get_overlay_text_color($settings);
		$overlay_img_max_width = self::restricted_access_get_overlay_image_max_width($settings);
		$show_overlay_image_as_normal = self::restricted_access_show_overlay_image_as_normal_above_message($settings);
		?>
		<style id="um-restricted-access-background-overlay-style">
			html, body {
				margin: 0 !important;
				padding: 0 !important;
				width: 100%;
				min-height: 100%;
				overflow: hidden !important;
				overscroll-behavior: none;
			}
			.um-restricted-access-background-overlay {
				position: fixed;
				inset: 0;
				z-index: 2147483000;
				display: flex;
				align-items: center;
				justify-content: center;
				width: 100vw;
				height: 100vh;
				min-height: 100svh;
				height: 100dvh;
				text-align: center;
				background-color: <?php echo esc_attr($bg_color); ?>;
				color: <?php echo esc_attr($text_color); ?>;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
				padding: 20px;
				padding: max(20px, env(safe-area-inset-top)) max(20px, env(safe-area-inset-right)) max(20px, env(safe-area-inset-bottom)) max(20px, env(safe-area-inset-left));
				box-sizing: border-box;
				overflow: auto;
				-webkit-overflow-scrolling: touch;
			}
			.um-restricted-access-background-overlay-image-wrap {
				display: flex;
				align-items: center;
				justify-content: center;
				padding: 20px;
				box-sizing: border-box;
				<?php if ($show_overlay_image_as_normal) : ?>
				position: relative;
				inset: auto;
				pointer-events: auto;
				z-index: 1;
				padding: 0 0 14px;
				<?php else : ?>
				position: absolute;
				inset: 0;
				pointer-events: none;
				z-index: 0;
				<?php endif; ?>
			}
			.um-restricted-access-background-overlay-image {
				display: block;
				width: 100%;
				height: auto;
				<?php if ($overlay_img_max_width !== '') : ?>
				max-width: <?php echo esc_html($overlay_img_max_width); ?>;
				<?php else : ?>
				max-width: none;
				<?php endif; ?>
			}
			.um-restricted-access-background-overlay-card {
				position: relative;
				z-index: 2;
				width: 100%;
				max-width: 560px;
				padding: 30px 24px;
				box-sizing: border-box;
			}
			.um-restricted-access-background-overlay-card h1 {
				margin: 0 0 16px;
				font-size: 32px;
				line-height: 1.2;
			}
			.um-restricted-access-background-overlay-card p {
				margin: 0;
				font-size: 18px;
				line-height: 1.5;
			}
			.um-restricted-access-background-overlay-form {
				margin-top: 20px;
			}
			.um-restricted-access-background-overlay-form input[type="password"] {
				width: 100%;
				max-width: 340px;
				padding: 10px 12px;
				font-size: 16px;
				line-height: 1.3;
				border-radius: 4px;
				border: 1px solid rgba(0,0,0,0.25);
				box-sizing: border-box;
			}
			.um-restricted-access-background-overlay-form button {
				margin-top: 10px;
				padding: 10px 16px;
				font-size: 15px;
				cursor: pointer;
			}
			.um-restricted-access-background-overlay-error {
				margin-top: 12px;
				font-size: 14px;
				font-weight: 600;
				color: #ff4f4f;
			}
		</style>
		<?php
	}

	/**
	 * Render overlay markup in footer while preserving page HTML output.
	 */
	public static function render_restricted_access_background_overlay_markup(): void {
		$context = self::$restricted_access_background_overlay_context;
		if (!is_array($context) || !isset($context['settings']) || !is_array($context['settings'])) {
			return;
		}
		$settings = $context['settings'];
		$message = self::restricted_access_get_no_access_message($settings);
		$password_submit_button_text = self::restricted_access_get_password_submit_button_text($settings);
		$overlay_img = self::restricted_access_get_overlay_image_url($settings);
		$has_image = $overlay_img !== '';
		$show_password_form = !empty($context['show_password_form']);
		$show_overlay_image_as_normal = self::restricted_access_show_overlay_image_as_normal_above_message($settings);
		$current_url = self::restricted_access_get_current_url();
		$ajax_url    = self::restricted_access_get_ajax_endpoint_url();
		$post_url    = self::restricted_access_get_post_endpoint_url();
		$inline_error = self::restricted_access_get_inline_error_message();
		?>
		<div class="um-restricted-access-background-overlay" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr($message); ?>">
			<?php if ($has_image && !$show_overlay_image_as_normal) : ?>
				<div class="um-restricted-access-background-overlay-image-wrap" aria-hidden="true">
					<img class="um-restricted-access-background-overlay-image" src="<?php echo esc_url($overlay_img); ?>" alt="" />
				</div>
			<?php endif; ?>
			<div class="um-restricted-access-background-overlay-card">
				<?php if ($has_image && $show_overlay_image_as_normal) : ?>
					<div class="um-restricted-access-background-overlay-image-wrap">
						<img class="um-restricted-access-background-overlay-image" src="<?php echo esc_url($overlay_img); ?>" alt="" />
					</div>
				<?php endif; ?>
				<h1><?php echo esc_html($message); ?></h1>
				<?php if ($show_password_form) : ?>
					<p><?php esc_html_e('Enter shared password to continue.', 'user-manager'); ?></p>
					<form class="um-restricted-access-background-overlay-form um-restricted-access-form-root" method="post" action="<?php echo esc_url($post_url); ?>" data-um-ra-ajax="<?php echo esc_attr($ajax_url); ?>" data-um-ra-redirect="<?php echo esc_attr($current_url); ?>" novalidate>
						<input type="hidden" name="action" value="<?php echo esc_attr(self::RESTRICTED_ACCESS_PASSWORD_ACTION); ?>" />
						<input type="hidden" name="um_restricted_access_submit" value="1" />
						<input type="hidden" name="um_restricted_access_redirect" value="<?php echo esc_attr($current_url); ?>" />
						<input type="password" name="um_restricted_access_password" autocomplete="current-password" required autofocus />
						<br />
						<button type="submit"><?php echo esc_html($password_submit_button_text); ?></button>
						<div class="um-restricted-access-background-overlay-error" data-um-ra-error hidden<?php if ($inline_error !== '' || self::$restricted_access_password_error !== '') : ?> style="display:block;"<?php endif; ?>>
							<?php
							if (self::$restricted_access_password_error !== '') {
								echo esc_html(self::$restricted_access_password_error);
							} elseif ($inline_error !== '') {
								echo esc_html($inline_error);
							}
							?>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
		<?php
		self::render_restricted_access_inline_js();
		self::$restricted_access_background_overlay_context = null;
	}

	/**
	 * Determine whether current user has a role excluded by settings.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_current_user_has_excluded_role(array $settings): bool {
		$excluded_roles = isset($settings['restricted_access_excluded_roles']) && is_array($settings['restricted_access_excluded_roles'])
			? array_map('sanitize_key', $settings['restricted_access_excluded_roles'])
			: [];
		$excluded_roles = array_values(array_filter($excluded_roles, static function (string $role): bool {
			return $role !== '';
		}));
		if (empty($excluded_roles)) {
			return false;
		}

		$user = wp_get_current_user();
		if (!$user instanceof WP_User || empty($user->roles)) {
			return false;
		}

		foreach ((array) $user->roles as $role) {
			$role = sanitize_key((string) $role);
			if ($role !== '' && in_array($role, $excluded_roles, true)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Handle front-end shared password submission posted inline to the
	 * current URL (traditional, non-AJAX path).
	 *
	 * On success this function redirects with 303 + explicit no-store /
	 * Vary: Cookie headers, and appends a signed one-time grant token to
	 * the redirect URL so the user is granted access even if a proxy or
	 * CDN strips the outbound Set-Cookie on the 3xx response.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_maybe_handle_password_submission(array $settings): void {
		if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper((string) $_SERVER['REQUEST_METHOD']) !== 'POST') {
			return;
		}
		if (!isset($_POST['um_restricted_access_submit'])) {
			return;
		}
		$saved_password_early = self::restricted_access_get_shared_password($settings);
		if ($saved_password_early === '') {
			return;
		}
		$submitted_password = isset($_POST['um_restricted_access_password']) ? sanitize_text_field(wp_unslash($_POST['um_restricted_access_password'])) : '';
		$redirect_target_raw = isset($_POST['um_restricted_access_redirect']) ? (string) wp_unslash($_POST['um_restricted_access_redirect']) : '';
		$redirect_target     = self::restricted_access_sanitize_redirect_url($redirect_target_raw);
		if ($redirect_target === '') {
			$redirect_target = self::restricted_access_get_current_url();
		}

		$saved_password = self::restricted_access_get_shared_password($settings);
		$is_correct_password = ($saved_password !== '' && hash_equals($saved_password, $submitted_password));

		if (!$is_correct_password) {
			self::restricted_access_log_access_attempt('', false, $submitted_password);
			self::$restricted_access_password_error = (string) __('Incorrect password. Please try again.', 'user-manager');
			return;
		}

		self::restricted_access_log_access_attempt($submitted_password, true, '');
		self::restricted_access_set_access_cookie($settings);
		if (self::restricted_access_should_trust_ip_after_password_success($settings)) {
			self::restricted_access_set_trusted_ip_cookie();
		}
		self::restricted_access_purge_cached_urls([
			self::restricted_access_get_home_url(),
			$redirect_target,
		]);

		$grant_token = self::restricted_access_create_grant_token($settings);
		$final_redirect = self::restricted_access_append_grant_token_to_url($redirect_target, $grant_token);
		self::restricted_access_send_post_redirect_get_response($final_redirect);
	}

	/**
	 * AJAX submit handler. Returns a small JSON payload with a signed grant
	 * URL the browser can navigate to. Avoids full page reload races when a
	 * submission happens from a background-overlay page.
	 */
	public static function handle_restricted_access_password_ajax(): void {
		nocache_headers();
		header('Cache-Control: no-store, no-cache, private, must-revalidate, max-age=0', true);
		header('Pragma: no-cache', true);

		$settings = self::get_settings();
		if (empty($settings['restricted_access_enabled'])) {
			wp_send_json_error(['code' => 'restricted_access_disabled'], 400);
		}

		$submitted_password = isset($_POST['password']) ? sanitize_text_field(wp_unslash($_POST['password'])) : '';
		if ($submitted_password === '' && isset($_POST['um_restricted_access_password'])) {
			$submitted_password = sanitize_text_field(wp_unslash($_POST['um_restricted_access_password']));
		}
		$redirect_raw = isset($_POST['redirect']) ? (string) wp_unslash($_POST['redirect']) : '';
		if ($redirect_raw === '' && isset($_POST['um_restricted_access_redirect'])) {
			$redirect_raw = (string) wp_unslash($_POST['um_restricted_access_redirect']);
		}
		$redirect_target = self::restricted_access_sanitize_redirect_url($redirect_raw);
		if ($redirect_target === '') {
			$redirect_target = self::restricted_access_get_home_url();
		}

		$saved_password = self::restricted_access_get_shared_password($settings);
		if ($saved_password === '' || !hash_equals($saved_password, $submitted_password)) {
			self::restricted_access_log_access_attempt('', false, $submitted_password);
			wp_send_json_error(
				[
					'code'    => 'invalid_password',
					'message' => (string) __('Incorrect password. Please try again.', 'user-manager'),
				],
				401
			);
		}

		self::restricted_access_log_access_attempt($submitted_password, true, '');
		self::restricted_access_set_access_cookie($settings);
		if (self::restricted_access_should_trust_ip_after_password_success($settings)) {
			self::restricted_access_set_trusted_ip_cookie();
		}
		self::restricted_access_purge_cached_urls([
			self::restricted_access_get_home_url(),
			$redirect_target,
		]);

		$grant_token = self::restricted_access_create_grant_token($settings);
		$final_redirect = self::restricted_access_append_grant_token_to_url($redirect_target, $grant_token);
		wp_send_json_success(
			[
				'redirect'    => $final_redirect,
				'grant_token' => $grant_token,
				'message'     => (string) __('Access granted. Loading the requested page…', 'user-manager'),
			]
		);
	}

	/**
	 * Fallback admin-post handler for browsers without JS and for submissions
	 * made via the traditional full-POST path when the overlay is rendered
	 * via background-HTML mode (wp_footer).
	 */
	public static function handle_restricted_access_password_post(): void {
		$settings = self::get_settings();
		if (empty($settings['restricted_access_enabled'])) {
			wp_safe_redirect(self::restricted_access_get_home_url());
			exit;
		}

		$submitted_password = isset($_POST['um_restricted_access_password']) ? sanitize_text_field(wp_unslash($_POST['um_restricted_access_password'])) : '';
		$redirect_raw = isset($_POST['um_restricted_access_redirect']) ? (string) wp_unslash($_POST['um_restricted_access_redirect']) : '';
		$redirect_target = self::restricted_access_sanitize_redirect_url($redirect_raw);
		if ($redirect_target === '') {
			$redirect_target = self::restricted_access_get_home_url();
		}

		$saved_password = self::restricted_access_get_shared_password($settings);
		$is_correct_password = ($saved_password !== '' && hash_equals($saved_password, $submitted_password));
		if (!$is_correct_password) {
			self::restricted_access_log_access_attempt('', false, $submitted_password);
			// Send the visitor back to the page they came from; the inline
			// overlay error state will render on the next GET.
			$error_url = add_query_arg(
				['um_ra_error' => '1'],
				$redirect_target
			);
			self::restricted_access_send_post_redirect_get_response($error_url);
		}

		self::restricted_access_log_access_attempt($submitted_password, true, '');
		self::restricted_access_set_access_cookie($settings);
		if (self::restricted_access_should_trust_ip_after_password_success($settings)) {
			self::restricted_access_set_trusted_ip_cookie();
		}
		self::restricted_access_purge_cached_urls([
			self::restricted_access_get_home_url(),
			$redirect_target,
		]);

		$grant_token = self::restricted_access_create_grant_token($settings);
		$final_redirect = self::restricted_access_append_grant_token_to_url($redirect_target, $grant_token);
		self::restricted_access_send_post_redirect_get_response($final_redirect);
	}

	/**
	 * Issue a 303 See Other redirect with aggressive no-cache headers so a
	 * POST→GET transition is never cached by a CDN or browser.
	 */
	private static function restricted_access_send_post_redirect_get_response(string $redirect_url): void {
		self::restricted_access_mark_public_request_uncacheable();
		wp_safe_redirect($redirect_url, 303);
		exit;
	}

	/**
	 * Sanitize a visitor-supplied redirect URL so we only redirect back to
	 * same-origin URLs. Empty string means "no trustworthy target".
	 */
	private static function restricted_access_sanitize_redirect_url(string $raw): string {
		$raw = trim($raw);
		if ($raw === '') {
			return '';
		}

		$home_host = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
		$home_host = strtolower($home_host);
		if (strpos($raw, '/') === 0 && strpos($raw, '//') !== 0) {
			// Root-relative path — safe, attach home scheme/host.
			return home_url($raw);
		}

		$parsed = wp_parse_url($raw);
		if (!is_array($parsed) || empty($parsed['host'])) {
			return '';
		}
		$raw_host = strtolower((string) $parsed['host']);
		$request_host = isset($_SERVER['HTTP_HOST']) ? strtolower(preg_replace('/:\d+$/', '', (string) wp_unslash($_SERVER['HTTP_HOST']))) : '';
		$allowed_hosts = array_values(array_unique(array_filter([$home_host, $request_host])));
		if (!in_array($raw_host, $allowed_hosts, true)) {
			return '';
		}
		return esc_url_raw($raw);
	}

	/**
	 * Resolve the home URL with a safe fallback.
	 */
	private static function restricted_access_get_home_url(): string {
		$url = home_url('/');
		return is_string($url) && $url !== '' ? $url : '/';
	}

	/**
	 * Create a short-lived single-use grant token backed by a transient.
	 *
	 * The token value is stored in the options table so it survives across
	 * the POST→GET hop even if cookies are dropped. It is single-use: the
	 * first request that consumes it deletes it immediately.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_create_grant_token(array $settings): string {
		$token = function_exists('wp_generate_password')
			? wp_generate_password(32, false, false)
			: bin2hex(random_bytes(16));

		$minutes = self::restricted_access_get_time_limit_minutes($settings);
		// Cap the grant-redemption window so even unused tokens cannot linger
		// longer than necessary. The access cookie set after redemption still
		// honors the full configured minutes value.
		$grant_ttl = min(max(60, (int) $minutes * MINUTE_IN_SECONDS), 15 * MINUTE_IN_SECONDS);

		$payload = [
			'ip'         => self::restricted_access_get_request_ip_address(),
			'created_at' => time(),
		];
		set_transient(self::RESTRICTED_ACCESS_GRANT_TRANSIENT_PREFIX . $token, $payload, $grant_ttl);
		return $token;
	}

	/**
	 * Append the one-time grant token as a query param while preserving any
	 * existing query params on the redirect URL.
	 */
	private static function restricted_access_append_grant_token_to_url(string $redirect_url, string $grant_token): string {
		if ($grant_token === '') {
			return $redirect_url;
		}
		return add_query_arg(
			self::RESTRICTED_ACCESS_GRANT_QUERY_PARAM,
			rawurlencode($grant_token),
			$redirect_url
		);
	}

	/**
	 * Consume a one-time grant token from the current request URL. Returns
	 * true when the token is valid (in which case an access cookie is set).
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_consume_grant_query_param(array $settings): bool {
		$raw = isset($_GET[self::RESTRICTED_ACCESS_GRANT_QUERY_PARAM])
			? (string) wp_unslash($_GET[self::RESTRICTED_ACCESS_GRANT_QUERY_PARAM])
			: '';
		$raw = trim($raw);
		if ($raw === '') {
			return false;
		}
		// Allow only the tokens we actually generate.
		if (!preg_match('/^[A-Za-z0-9]{16,64}$/', $raw)) {
			return false;
		}

		$transient_key = self::RESTRICTED_ACCESS_GRANT_TRANSIENT_PREFIX . $raw;
		$payload = get_transient($transient_key);
		if (!is_array($payload)) {
			return false;
		}

		// Single-use: invalidate immediately so a leaked URL cannot grant
		// access more than once.
		delete_transient($transient_key);

		self::restricted_access_set_access_cookie($settings);
		if (self::restricted_access_should_trust_ip_after_password_success($settings)) {
			self::restricted_access_set_trusted_ip_cookie();
		}

		// Best-effort cache purge: if a page-cache layer already stored the
		// overlay/403 for the home page or the URL the visitor just landed
		// on, clear it so subsequent visits actually hit PHP and see that
		// the access cookie grants them through.
		self::restricted_access_purge_cached_urls([
			self::restricted_access_get_home_url(),
			self::restricted_access_get_current_url(),
		]);

		return true;
	}

	/**
	 * Best-effort "clear the cached overlay" helper. Tries every widely-used
	 * page-cache plugin and any generic `wp_cache_flush()` hook. Failures are
	 * swallowed silently because this is best-effort and the no-cache headers
	 * added by `restricted_access_mark_public_request_uncacheable()` already
	 * prevent future cache poisoning; this just clears anything that was
	 * cached before the add-on was enabled or the password was set.
	 *
	 * @param array<int,string> $urls Absolute URLs to purge.
	 */
	private static function restricted_access_purge_cached_urls(array $urls): void {
		$urls = array_values(array_unique(array_filter(array_map(static function ($url): string {
			$url = is_string($url) ? trim($url) : '';
			return $url !== '' ? (string) esc_url_raw($url) : '';
		}, $urls))));
		if (empty($urls)) {
			return;
		}

		foreach ($urls as $url) {
			// WP Rocket
			if (function_exists('rocket_clean_files')) {
				@rocket_clean_files($url);
			}
			if (function_exists('rocket_clean_post')) {
				$post_id = url_to_postid($url);
				if ($post_id > 0) {
					@rocket_clean_post($post_id);
				}
			}

			// LiteSpeed Cache
			if (class_exists('LiteSpeed\\Purge') && method_exists('LiteSpeed\\Purge', 'purge_url')) {
				try {
					\LiteSpeed\Purge::purge_url($url);
				} catch (\Throwable $e) {
					// best-effort
				}
			}
			if (has_action('litespeed_purge_url')) {
				do_action('litespeed_purge_url', $url);
			}

			// W3 Total Cache
			if (function_exists('w3tc_flush_url')) {
				@w3tc_flush_url($url);
			}

			// WP Super Cache
			if (function_exists('wp_cache_post_change')) {
				$post_id = url_to_postid($url);
				if ($post_id > 0) {
					@wp_cache_post_change($post_id);
				}
			}

			// WP Fastest Cache
			if (class_exists('WpFastestCache') && method_exists('WpFastestCache', 'singleDeleteCache')) {
				try {
					$wpfc = new \WpFastestCache();
					if (method_exists($wpfc, 'singleDeleteCache')) {
						@$wpfc->singleDeleteCache(false, url_to_postid($url));
					}
				} catch (\Throwable $e) {
					// best-effort
				}
			}

			// Cache Enabler
			if (class_exists('Cache_Enabler') && method_exists('Cache_Enabler', 'clear_page_cache_by_url')) {
				try {
					\Cache_Enabler::clear_page_cache_by_url($url);
				} catch (\Throwable $e) {
					// best-effort
				}
			}

			// Hummingbird
			if (has_action('wphb_clear_page_cache')) {
				do_action('wphb_clear_page_cache', $url);
			}

			// Autoptimize (rare, but it honors generic do_action)
			if (has_action('autoptimize_action_cachepurged')) {
				do_action('autoptimize_action_cachepurged');
			}

			// Generic shared signal for custom cache plugins.
			do_action('user_manager_restricted_access_purge_url', $url);
		}

		// Fall-through: if a cache plugin only exposes a global flush hook,
		// give it one chance to do the right thing without being too broad.
		if (has_action('user_manager_restricted_access_purge_complete')) {
			do_action('user_manager_restricted_access_purge_complete', $urls);
		}
	}

	/**
	 * After consuming a valid grant token, redirect to the same URL with
	 * the grant parameter stripped so it does not remain in the address
	 * bar / browser history / referrer.
	 */
	private static function restricted_access_redirect_removing_grant_param(): void {
		$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
		$cleaned_uri = remove_query_arg(self::RESTRICTED_ACCESS_GRANT_QUERY_PARAM, $request_uri);
		// Also strip the transient error marker so it does not stick around.
		$cleaned_uri = remove_query_arg('um_ra_error', $cleaned_uri);
		$target = home_url($cleaned_uri);
		self::restricted_access_mark_public_request_uncacheable();
		wp_safe_redirect($target, 302);
		exit;
	}

	/**
	 * Insert one restricted-access attempt row.
	 */
	private static function restricted_access_log_access_attempt(string $password_used, bool $is_success, string $failed_password): void {
		global $wpdb;
		$table = $wpdb->prefix . 'um_restricted_access_history';
		$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
		if ($table_exists !== $table) {
			return;
		}
		$ip = self::restricted_access_get_request_ip_address();
		$location = self::restricted_access_resolve_ip_location_label($ip);
		$browser = self::restricted_access_resolve_browser_label();
		$url = self::restricted_access_get_current_url();

		$wpdb->insert(
			$table,
			[
				'ip_address' => $ip,
				'ip_location' => $location,
				'browser' => $browser,
				'url_accessed_from' => $url,
				'password_used' => $password_used,
				'failed_password' => $failed_password,
				'created_at' => current_time('mysql'),
				'is_success' => $is_success ? 1 : 0,
			],
			['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d']
		);
	}

	/**
	 * Resolve client IP from common proxy headers.
	 */
	private static function restricted_access_get_request_ip_address(): string {
		$candidates = [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_REAL_IP',
			'HTTP_X_FORWARDED_FOR',
			'REMOTE_ADDR',
		];
		foreach ($candidates as $header) {
			if (!isset($_SERVER[$header])) {
				continue;
			}
			$raw = trim((string) wp_unslash($_SERVER[$header]));
			if ($raw === '') {
				continue;
			}
			$parts = array_map('trim', explode(',', $raw));
			foreach ($parts as $candidate) {
				$validated = filter_var($candidate, FILTER_VALIDATE_IP);
				if (is_string($validated) && $validated !== '') {
					return $validated;
				}
			}
		}
		return '';
	}

	/**
	 * Build a short browser label from user agent.
	 */
	private static function restricted_access_resolve_browser_label(): string {
		$ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
		if ($ua === '') {
			return '';
		}
		$ua_lc = strtolower($ua);
		$browser = 'Unknown';
		if (strpos($ua_lc, 'edg/') !== false) {
			$browser = 'Edge';
		} elseif (strpos($ua_lc, 'opr/') !== false || strpos($ua_lc, 'opera') !== false) {
			$browser = 'Opera';
		} elseif (strpos($ua_lc, 'firefox/') !== false) {
			$browser = 'Firefox';
		} elseif (strpos($ua_lc, 'chrome/') !== false && strpos($ua_lc, 'edg/') === false && strpos($ua_lc, 'opr/') === false) {
			$browser = 'Chrome';
		} elseif (strpos($ua_lc, 'safari/') !== false && strpos($ua_lc, 'chrome/') === false) {
			$browser = 'Safari';
		}
		if (strpos($ua_lc, 'mobile') !== false || strpos($ua_lc, 'android') !== false || strpos($ua_lc, 'iphone') !== false) {
			$browser .= ' (Mobile)';
		}
		return $browser;
	}

	/**
	 * Resolve a best-effort IP location label.
	 */
	private static function restricted_access_resolve_ip_location_label(string $ip): string {
		if ($ip === '') {
			return '';
		}
		if (isset(self::$restricted_access_geo_cache[$ip])) {
			return self::$restricted_access_geo_cache[$ip];
		}
		$transient_key = 'um_ra_geo_' . md5($ip);
		$cached = get_transient($transient_key);
		if (is_string($cached)) {
			self::$restricted_access_geo_cache[$ip] = $cached;
			return $cached;
		}

		$location = '';
		$response = wp_remote_get(
			'https://ipapi.co/' . rawurlencode($ip) . '/json/',
			[
				'timeout' => 2,
				'redirection' => 2,
				'headers' => [
					'Accept' => 'application/json',
				],
			]
		);
		if (!is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) === 200) {
			$body = wp_remote_retrieve_body($response);
			$data = json_decode((string) $body, true);
			if (is_array($data)) {
				$city = isset($data['city']) ? sanitize_text_field((string) $data['city']) : '';
				$region = isset($data['region']) ? sanitize_text_field((string) $data['region']) : '';
				$country = isset($data['country_name']) ? sanitize_text_field((string) $data['country_name']) : '';
				$parts = array_values(array_filter([$city, $region, $country], static function ($value): bool {
					return (string) $value !== '';
				}));
				$location = implode(', ', $parts);
			}
		}
		if ($location === '') {
			$location = __('Unknown', 'user-manager');
		}
		self::$restricted_access_geo_cache[$ip] = $location;
		set_transient($transient_key, $location, DAY_IN_SECONDS * 7);
		return $location;
	}

	/**
	 * Fetch latest restricted access history rows for admin table.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_restricted_access_history_entries(int $limit = 500): array {
		global $wpdb;
		$table = $wpdb->prefix . 'um_restricted_access_history';
		$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
		if ($table_exists !== $table) {
			return [];
		}
		$limit = max(1, min(2000, $limit));
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, ip_address, ip_location, browser, url_accessed_from, password_used, failed_password, is_success, created_at FROM {$table} ORDER BY created_at DESC, id DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
		if (!is_array($rows)) {
			return [];
		}
		return array_map(static function (array $row): array {
			return [
				'id' => isset($row['id']) ? (int) $row['id'] : 0,
				'ip_address' => isset($row['ip_address']) ? (string) $row['ip_address'] : '',
				'ip_location' => isset($row['ip_location']) ? (string) $row['ip_location'] : '',
				'browser' => isset($row['browser']) ? (string) $row['browser'] : '',
				'url_accessed_from' => isset($row['url_accessed_from']) ? (string) $row['url_accessed_from'] : '',
				'password_used' => isset($row['password_used']) ? (string) $row['password_used'] : '',
				'failed_password' => isset($row['failed_password']) ? (string) $row['failed_password'] : '',
				'is_success' => !empty($row['is_success']),
				'created_at' => isset($row['created_at']) ? (string) $row['created_at'] : '',
			];
		}, $rows);
	}

	/**
	 * Render full-screen overlay (with optional password form) and stop execution.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function render_restricted_access_overlay_and_exit(array $settings, bool $show_password_form): void {
		self::restricted_access_mark_public_request_uncacheable();
		status_header(403);

		$message     = self::restricted_access_get_no_access_message($settings);
		$password_submit_button_text = self::restricted_access_get_password_submit_button_text($settings);
		$bg_color    = self::restricted_access_get_overlay_background_color($settings);
		$text_color  = self::restricted_access_get_overlay_text_color($settings);
		$overlay_img = self::restricted_access_get_overlay_image_url($settings);
		$overlay_img_max_width = self::restricted_access_get_overlay_image_max_width($settings);
		$show_overlay_image_as_normal = self::restricted_access_show_overlay_image_as_normal_above_message($settings);
		$has_image   = $overlay_img !== '';
		?>
		<!doctype html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo('charset'); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html($message); ?></title>
			<style>
				html, body {
					margin: 0;
					padding: 0;
					width: 100%;
					height: 100%;
					min-height: 100%;
					overflow: hidden;
					overscroll-behavior: none;
				}
				body.um-restricted-access-overlay {
					position: fixed;
					inset: 0;
					width: 100vw;
					height: 100vh;
					min-height: 100svh;
					height: 100dvh;
					display: flex;
					align-items: center;
					justify-content: center;
					text-align: center;
					background-color: <?php echo esc_attr($bg_color); ?>;
					color: <?php echo esc_attr($text_color); ?>;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
					padding: 20px;
					padding: max(20px, env(safe-area-inset-top)) max(20px, env(safe-area-inset-right)) max(20px, env(safe-area-inset-bottom)) max(20px, env(safe-area-inset-left));
					box-sizing: border-box;
					overflow: auto;
					-webkit-overflow-scrolling: touch;
				}
				.um-restricted-access-overlay-image-wrap {
					display: flex;
					align-items: center;
					justify-content: center;
					padding: 20px;
					box-sizing: border-box;
					<?php if ($show_overlay_image_as_normal) : ?>
					position: relative;
					inset: auto;
					pointer-events: auto;
					z-index: 1;
					padding: 0 0 14px;
					<?php else : ?>
					position: fixed;
					inset: 0;
					pointer-events: none;
					z-index: 0;
					<?php endif; ?>
				}
				.um-restricted-access-overlay-image {
					display: block;
					width: 100%;
					height: auto;
					<?php if ($overlay_img_max_width !== '') : ?>
					max-width: <?php echo esc_html($overlay_img_max_width); ?>;
					<?php else : ?>
					max-width: none;
					<?php endif; ?>
				}
				.um-restricted-access-card {
					position: relative;
					z-index: 2;
					width: 100%;
					max-width: 560px;
					padding: 30px 24px;
					box-sizing: border-box;
				}
				.um-restricted-access-card h1 {
					margin: 0 0 16px;
					font-size: 32px;
					line-height: 1.2;
				}
				.um-restricted-access-card p {
					margin: 0;
					font-size: 18px;
					line-height: 1.5;
				}
				.um-restricted-access-form {
					margin-top: 20px;
				}
				.um-restricted-access-form input[type="password"] {
					width: 100%;
					max-width: 340px;
					padding: 10px 12px;
					font-size: 16px;
					line-height: 1.3;
					border-radius: 4px;
					border: 1px solid rgba(0,0,0,0.25);
					box-sizing: border-box;
				}
				.um-restricted-access-form button {
					margin-top: 10px;
					padding: 10px 16px;
					font-size: 15px;
					cursor: pointer;
				}
				.um-restricted-access-error {
					margin-top: 12px;
					font-size: 14px;
					font-weight: 600;
					color: #ff4f4f;
				}
			</style>
		</head>
		<body class="um-restricted-access-overlay">
			<?php if ($has_image && !$show_overlay_image_as_normal) : ?>
				<div class="um-restricted-access-overlay-image-wrap" aria-hidden="true">
					<img class="um-restricted-access-overlay-image" src="<?php echo esc_url($overlay_img); ?>" alt="" />
				</div>
			<?php endif; ?>
			<div class="um-restricted-access-card">
				<?php if ($has_image && $show_overlay_image_as_normal) : ?>
					<div class="um-restricted-access-overlay-image-wrap">
						<img class="um-restricted-access-overlay-image" src="<?php echo esc_url($overlay_img); ?>" alt="" />
					</div>
				<?php endif; ?>
				<h1><?php echo esc_html($message); ?></h1>
				<?php if ($show_password_form) :
					$current_url = self::restricted_access_get_current_url();
					$ajax_url    = self::restricted_access_get_ajax_endpoint_url();
					$post_url    = self::restricted_access_get_post_endpoint_url();
					$inline_error = self::restricted_access_get_inline_error_message();
				?>
					<p><?php esc_html_e('Enter shared password to continue.', 'user-manager'); ?></p>
					<form class="um-restricted-access-form um-restricted-access-form-root" method="post" action="<?php echo esc_url($post_url); ?>" data-um-ra-ajax="<?php echo esc_attr($ajax_url); ?>" data-um-ra-redirect="<?php echo esc_attr($current_url); ?>" novalidate>
						<input type="hidden" name="action" value="<?php echo esc_attr(self::RESTRICTED_ACCESS_PASSWORD_ACTION); ?>" />
						<input type="hidden" name="um_restricted_access_submit" value="1" />
						<input type="hidden" name="um_restricted_access_redirect" value="<?php echo esc_attr($current_url); ?>" />
						<input type="password" name="um_restricted_access_password" autocomplete="current-password" required autofocus />
						<br />
						<button type="submit"><?php echo esc_html($password_submit_button_text); ?></button>
						<div class="um-restricted-access-error" data-um-ra-error hidden<?php if ($inline_error !== '' || self::$restricted_access_password_error !== '') : ?> style="display:block;"<?php endif; ?>>
							<?php
							if (self::$restricted_access_password_error !== '') {
								echo esc_html(self::$restricted_access_password_error);
							} elseif ($inline_error !== '') {
								echo esc_html($inline_error);
							}
							?>
						</div>
					</form>
				<?php endif; ?>
			</div>
			<?php self::render_restricted_access_inline_js(); ?>
		</body>
		</html>
		<?php
		exit;
	}

	/**
	 * Check if request URI contains the configured access string.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_request_matches_access_string(array $settings): bool {
		$needle = '';
		if (isset($settings['restricted_access_allow_query_string'])) {
			$needle = trim((string) $settings['restricted_access_allow_query_string']);
		} elseif (isset($settings['restricted_access_url_string'])) {
			$needle = trim((string) $settings['restricted_access_url_string']);
		} elseif (isset($settings['restricted_access_appended_url_key'])) {
			$needle = trim((string) $settings['restricted_access_appended_url_key']);
		}
		if ($needle === '') {
			return false;
		}
		$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
		if ($request_uri === '') {
			return false;
		}
		return strpos($request_uri, $needle) !== false;
	}

	/**
	 * Set signed restricted-access cookie for configured duration.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_set_access_cookie(array $settings): void {
		$minutes = self::restricted_access_get_time_limit_minutes($settings);
		$expires = time() + ($minutes * MINUTE_IN_SECONDS);
		$sig     = hash_hmac('sha256', (string) $expires, wp_salt('auth'));
		$value   = $expires . '.' . $sig;
		$paths   = self::restricted_access_get_cookie_paths();
		$domains = self::restricted_access_get_cookie_domains();

		$options_base = [
			'expires'  => $expires,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		];
		foreach ($paths as $path) {
			foreach ($domains as $domain) {
				$options = $options_base;
				$options['path'] = $path;
				if ($domain !== '') {
					$options['domain'] = $domain;
				}
				setcookie('um_restricted_access', $value, $options);
			}
		}
		$_COOKIE['um_restricted_access'] = $value;
	}

	/**
	 * Whether trusted-IP bypass is enabled in settings.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_should_trust_ip_after_password_success(array $settings): bool {
		return !empty($settings['restricted_access_remember_ip_for_30_days']);
	}

	/**
	 * Set signed trusted-IP cookie for 30 days.
	 */
	private static function restricted_access_set_trusted_ip_cookie(): void {
		$ip = self::restricted_access_get_request_ip_address();
		if ($ip === '') {
			return;
		}
		$expires = time() + (30 * DAY_IN_SECONDS);
		$sig = hash_hmac('sha256', $ip . '|' . (string) $expires, wp_salt('auth'));
		$value = $expires . '.' . $sig;
		$paths = self::restricted_access_get_cookie_paths();
		$domains = self::restricted_access_get_cookie_domains();
		$options_base = [
			'expires' => $expires,
			'secure' => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		];
		foreach ($paths as $path) {
			foreach ($domains as $domain) {
				$options = $options_base;
				$options['path'] = $path;
				if ($domain !== '') {
					$options['domain'] = $domain;
				}
				setcookie(self::$restricted_access_trusted_ip_cookie_name, $value, $options);
			}
		}
		$_COOKIE[self::$restricted_access_trusted_ip_cookie_name] = $value;
	}

	/**
	 * Validate trusted-IP cookie against current visitor IP.
	 */
	private static function restricted_access_has_valid_trusted_ip_cookie(): bool {
		$ip = self::restricted_access_get_request_ip_address();
		if ($ip === '') {
			return false;
		}
		$raw = isset($_COOKIE[self::$restricted_access_trusted_ip_cookie_name]) && is_string($_COOKIE[self::$restricted_access_trusted_ip_cookie_name])
			? (string) $_COOKIE[self::$restricted_access_trusted_ip_cookie_name]
			: '';
		if ($raw === '' || strpos($raw, '.') === false) {
			return false;
		}
		[$expires_raw, $sig] = array_pad(explode('.', $raw, 2), 2, '');
		$expires = absint($expires_raw);
		if ($expires <= 0 || $expires < time() || $sig === '') {
			return false;
		}
		$expected = hash_hmac('sha256', $ip . '|' . (string) $expires, wp_salt('auth'));
		return hash_equals($expected, $sig);
	}

	/**
	 * Resolve all cookie paths that should receive restricted-access cookie.
	 *
	 * @return array<int,string>
	 */
	private static function restricted_access_get_cookie_paths(): array {
		$paths = [];
		if (defined('COOKIEPATH') && is_string(COOKIEPATH) && COOKIEPATH !== '') {
			$paths[] = COOKIEPATH;
		}
		if (defined('SITECOOKIEPATH') && is_string(SITECOOKIEPATH) && SITECOOKIEPATH !== '') {
			$paths[] = SITECOOKIEPATH;
		}
		$paths[] = '/';

		$normalized = [];
		foreach ($paths as $path) {
			$raw_path = trim((string) $path);
			if ($raw_path === '') {
				continue;
			}
			if (strpos($raw_path, '/') !== 0) {
				$raw_path = '/' . $raw_path;
			}
			$normalized[] = $raw_path;
		}
		$normalized = array_values(array_unique($normalized));
		return !empty($normalized) ? $normalized : ['/'];
	}

	/**
	 * Resolve cookie domains to support host-only, current host and
	 * common www/non-www variants for consistent cross-page reads.
	 *
	 * @return array<int,string>
	 */
	private static function restricted_access_get_cookie_domains(): array {
		$domains = [''];
		if (defined('COOKIE_DOMAIN') && is_string(COOKIE_DOMAIN) && COOKIE_DOMAIN !== '') {
			$domains[] = strtolower(trim(COOKIE_DOMAIN));
			return array_values(array_unique(array_filter($domains, static function ($value): bool {
				return is_string($value);
			})));
		}

		$hosts = [];
		$home_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
		if (is_string($home_host) && $home_host !== '') {
			$hosts[] = $home_host;
		}
		$request_host = isset($_SERVER['HTTP_HOST']) ? (string) wp_unslash($_SERVER['HTTP_HOST']) : '';
		if ($request_host !== '') {
			$request_host_no_port = preg_replace('/:\d+$/', '', $request_host);
			if (is_string($request_host_no_port) && $request_host_no_port !== '') {
				$hosts[] = $request_host_no_port;
			}
		}

		foreach ($hosts as $host) {
			$host = strtolower(trim((string) $host));
			if ($host === '' || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
				continue;
			}
			$domains[] = $host;
			if (strpos($host, 'www.') === 0) {
				$root_host = substr($host, 4);
				if ($root_host !== '') {
					$domains[] = $root_host;
				}
			}
		}

		return array_values(array_unique($domains));
	}

	/**
	 * Validate restricted-access cookie signature and expiration.
	 */
	private static function restricted_access_has_valid_access_cookie(): bool {
		$cookie_values = self::restricted_access_get_all_access_cookie_values();
		foreach ($cookie_values as $raw) {
			if (!self::restricted_access_is_valid_access_cookie_value($raw)) {
				continue;
			}
			$_COOKIE['um_restricted_access'] = $raw;
			return true;
		}
		return false;
	}

	/**
	 * Collect all seen restricted-access cookie values, including duplicate
	 * values with the same cookie name from the raw Cookie header.
	 *
	 * @return array<int,string>
	 */
	private static function restricted_access_get_all_access_cookie_values(): array {
		$values = [];
		if (isset($_COOKIE['um_restricted_access']) && is_string($_COOKIE['um_restricted_access'])) {
			$value = (string) $_COOKIE['um_restricted_access'];
			if ($value !== '') {
				$values[] = $value;
			}
		}
		$raw_cookie_header = isset($_SERVER['HTTP_COOKIE']) ? (string) wp_unslash($_SERVER['HTTP_COOKIE']) : '';
		if ($raw_cookie_header !== '') {
			$pairs = explode(';', $raw_cookie_header);
			foreach ($pairs as $pair) {
				$pair = trim((string) $pair);
				if ($pair === '' || strpos($pair, '=') === false) {
					continue;
				}
				[$name, $raw_value] = array_pad(explode('=', $pair, 2), 2, '');
				$name = trim((string) $name);
				if ($name !== 'um_restricted_access') {
					continue;
				}
				$value = rawurldecode(trim((string) $raw_value));
				if ($value !== '') {
					$values[] = $value;
				}
			}
		}
		return array_values(array_unique($values));
	}

	/**
	 * Validate one signed restricted-access cookie value.
	 */
	private static function restricted_access_is_valid_access_cookie_value(string $raw): bool {
		if ($raw === '' || strpos($raw, '.') === false) {
			return false;
		}
		[$expires_raw, $sig] = array_pad(explode('.', $raw, 2), 2, '');
		$expires = absint($expires_raw);
		if ($expires <= 0 || $expires < time()) {
			return false;
		}
		$expected = hash_hmac('sha256', (string) $expires, wp_salt('auth'));
		return hash_equals($expected, (string) $sig);
	}

	/**
	 * Resolve logged-out behavior.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_get_logged_out_behavior(array $settings): string {
		$raw = isset($settings['restricted_access_logged_out_behavior'])
			? sanitize_key((string) $settings['restricted_access_logged_out_behavior'])
			: 'redirect_my_account';
		if ($raw === 'my-account') {
			$raw = 'redirect_my_account';
		} elseif ($raw === 'wp-admin') {
			$raw = 'redirect_wp_admin';
		}
		$allowed = ['redirect_my_account', 'redirect_wp_admin', 'overlay'];
		return in_array($raw, $allowed, true) ? $raw : 'overlay';
	}

	/**
	 * Resolve configured shared password.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_get_shared_password(array $settings): string {
		return isset($settings['restricted_access_shared_password'])
			? trim((string) $settings['restricted_access_shared_password'])
			: '';
	}

	/**
	 * Resolve access duration in minutes.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_get_time_limit_minutes(array $settings): int {
		$minutes = 30;
		if (isset($settings['restricted_access_time_limit_minutes'])) {
			$minutes = absint($settings['restricted_access_time_limit_minutes']);
		} elseif (isset($settings['restricted_access_session_minutes'])) {
			$minutes = absint($settings['restricted_access_session_minutes']);
		}
		if ($minutes < 1) {
			$minutes = 30;
		}
		return min($minutes, 10080);
	}

	/**
	 * Resolve current full URL.
	 */
	private static function restricted_access_get_current_url(): string {
		$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
		return home_url($request_uri);
	}

	/**
	 * URL for the AJAX password submit endpoint.
	 */
	private static function restricted_access_get_ajax_endpoint_url(): string {
		return function_exists('admin_url') ? admin_url('admin-ajax.php') : '/wp-admin/admin-ajax.php';
	}

	/**
	 * URL for the admin-post password submit endpoint (traditional POST fallback).
	 */
	private static function restricted_access_get_post_endpoint_url(): string {
		return function_exists('admin_url') ? admin_url('admin-post.php') : '/wp-admin/admin-post.php';
	}

	/**
	 * Read the inline error message bubbled through the ?um_ra_error=1 flag.
	 */
	private static function restricted_access_get_inline_error_message(): string {
		if (!isset($_GET['um_ra_error']) || (string) $_GET['um_ra_error'] !== '1') {
			return '';
		}
		return (string) __('Incorrect password. Please try again.', 'user-manager');
	}

	/**
	 * Inline JS for the password form:
	 *  - Intercepts submit, posts via fetch() to admin-ajax.php
	 *  - Navigates to the signed grant URL returned by the server
	 *  - Falls back to the native form POST (to admin-post.php) on any JS/network error
	 *  - Guards against double-submission (disables the button while in-flight)
	 */
	private static function render_restricted_access_inline_js(): void {
		?>
		<script id="um-restricted-access-inline-js">
		(function(){
			function once(fn){var done=false;return function(){if(done){return;}done=true;return fn.apply(this, arguments);};}
			function bindForm(form){
				if(!form || form.__umRaBound){return;}
				form.__umRaBound = true;
				var ajaxUrl = form.getAttribute('data-um-ra-ajax') || '';
				var redirectField = form.querySelector('input[name="um_restricted_access_redirect"]');
				var fallbackRedirect = form.getAttribute('data-um-ra-redirect') || (redirectField && redirectField.value) || window.location.href;
				var errorBox = form.querySelector('[data-um-ra-error]');
				var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
				var pwInput = form.querySelector('input[type="password"]');

				function showError(msg){
					if(!errorBox){return;}
					errorBox.textContent = msg || '';
					errorBox.hidden = !msg;
					if(msg){errorBox.style.display = 'block';}
				}
				function setBusy(busy){
					if(submitBtn){submitBtn.disabled = !!busy;}
					form.setAttribute('data-um-ra-busy', busy ? '1' : '0');
				}

				form.addEventListener('submit', function(e){
					if(!window.fetch || !ajaxUrl){return;}
					e.preventDefault();
					showError('');
					setBusy(true);
					var password = pwInput ? pwInput.value : '';
					var body = new URLSearchParams();
					body.set('action', '<?php echo esc_js(self::RESTRICTED_ACCESS_PASSWORD_ACTION); ?>');
					body.set('password', password);
					body.set('redirect', fallbackRedirect);
					fetch(ajaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
							'Accept': 'application/json',
							'X-Requested-With': 'XMLHttpRequest'
						},
						body: body.toString(),
						cache: 'no-store'
					}).then(function(res){
						return res.json().then(function(data){return {status: res.status, data: data};}).catch(function(){return {status: res.status, data: null};});
					}).then(function(wrap){
						var data = wrap && wrap.data;
						if(data && data.success === true && data.data && data.data.redirect){
							window.location.replace(data.data.redirect);
							return;
						}
						var msg = '';
						if(data && data.data && typeof data.data.message === 'string'){msg = data.data.message;}
						if(!msg){msg = '<?php echo esc_js(__('Incorrect password. Please try again.', 'user-manager')); ?>';}
						showError(msg);
						setBusy(false);
						if(pwInput){pwInput.focus(); pwInput.select && pwInput.select();}
					}).catch(function(){
						// Network/CORS/cache issue — fall back to native POST so the
						// visitor is not trapped behind JS errors.
						setBusy(false);
						form.submit();
					});
				}, false);
			}

			function init(){
				var forms = document.querySelectorAll('form.um-restricted-access-form-root');
				for(var i=0;i<forms.length;i++){bindForm(forms[i]);}
			}
			if(document.readyState === 'loading'){
				document.addEventListener('DOMContentLoaded', once(init));
			} else {
				init();
			}
		})();
		</script>
		<?php
	}

	/**
	 * Resolve My Account URL with safe fallback.
	 */
	private static function restricted_access_get_my_account_url(): string {
		if (function_exists('wc_get_page_permalink')) {
			$url = wc_get_page_permalink('myaccount');
			if (is_string($url) && $url !== '') {
				return $url;
			}
		}
		return wp_login_url(self::restricted_access_get_current_url());
	}

	/**
	 * Compare current request path/query with target URL to prevent redirect loops.
	 */
	private static function restricted_access_current_request_matches_url(string $target_url): bool {
		$current_url = self::restricted_access_get_current_url();
		$current     = wp_parse_url($current_url);
		$target      = wp_parse_url($target_url);
		if (!is_array($current) || !is_array($target)) {
			return false;
		}
		$current_path = isset($current['path']) ? (string) $current['path'] : '';
		$target_path  = isset($target['path']) ? (string) $target['path'] : '';
		if ($current_path !== $target_path) {
			return false;
		}
		$current_query = isset($current['query']) ? (string) $current['query'] : '';
		$target_query  = isset($target['query']) ? (string) $target['query'] : '';
		return $current_query === $target_query;
	}

	/**
	 * Resolve no-access message.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_get_no_access_message(array $settings): string {
		return isset($settings['restricted_access_no_access_message'])
			? sanitize_text_field((string) $settings['restricted_access_no_access_message'])
			: '';
	}

	/**
	 * Resolve password submit button text.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_get_password_submit_button_text(array $settings): string {
		$text = isset($settings['restricted_access_password_submit_button_text'])
			? sanitize_text_field((string) $settings['restricted_access_password_submit_button_text'])
			: '';
		return $text !== '' ? $text : 'Access Website';
	}

	/**
	 * Resolve overlay background color.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_get_overlay_background_color(array $settings): string {
		$color = '';
		if (isset($settings['restricted_access_overlay_background_color'])) {
			$color = sanitize_hex_color((string) $settings['restricted_access_overlay_background_color']);
		} elseif (isset($settings['restricted_access_overlay_background'])) {
			$color = sanitize_hex_color((string) $settings['restricted_access_overlay_background']);
		} elseif (isset($settings['restricted_access_overlay_bg'])) {
			$color = sanitize_hex_color((string) $settings['restricted_access_overlay_bg']);
		}
		return $color ? $color : '#ffffff';
	}

	/**
	 * Resolve overlay text color.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_get_overlay_text_color(array $settings): string {
		$color = isset($settings['restricted_access_overlay_text_color'])
			? sanitize_hex_color((string) $settings['restricted_access_overlay_text_color'])
			: '';
		return $color ? $color : '#000000';
	}

	/**
	 * Resolve overlay background image URL.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_get_overlay_image_url(array $settings): string {
		if (isset($settings['restricted_access_overlay_image_url'])) {
			return esc_url_raw((string) $settings['restricted_access_overlay_image_url']);
		}
		if (isset($settings['restricted_access_overlay_image'])) {
			return esc_url_raw((string) $settings['restricted_access_overlay_image']);
		}
		return '';
	}

	/**
	 * Resolve optional overlay image max-width CSS value.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_get_overlay_image_max_width(array $settings): string {
		$value = isset($settings['restricted_access_overlay_image_max_width'])
			? trim((string) $settings['restricted_access_overlay_image_max_width'])
			: '';
		if ($value === '') {
			return '';
		}
		$sanitized = sanitize_text_field($value);
		return preg_match('/^[0-9.]+(px|%|vw|vh|rem|em)$/i', $sanitized) ? $sanitized : '';
	}

	/**
	 * Whether to render overlay image as normal content above the message.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_show_overlay_image_as_normal_above_message(array $settings): bool {
		return !empty($settings['restricted_access_overlay_image_display_as_normal_above_message']);
	}
}

