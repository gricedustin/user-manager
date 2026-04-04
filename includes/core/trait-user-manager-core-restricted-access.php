<?php
/**
 * Restricted Access helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Restricted_Access_Trait {

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
	 * Boot restricted access controls when enabled.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_restricted_access(array $settings): void {
		if (empty($settings['restricted_access_enabled'])) {
			return;
		}

		add_action('template_redirect', [__CLASS__, 'maybe_enforce_restricted_access'], 1);
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

		// Logged-in users can view by default unless their role is excluded.
		if (is_user_logged_in()) {
			if (self::restricted_access_current_user_has_excluded_role($settings)) {
				self::render_restricted_access_overlay_and_exit($settings, false);
			}
			return;
		}

		// A matching appended URL string grants timed access.
		if (self::restricted_access_request_matches_access_string($settings)) {
			self::restricted_access_log_access_attempt('', true, '');
			self::restricted_access_set_access_cookie($settings);
			return;
		}

		// Existing timed access cookie grants access.
		if (self::restricted_access_has_valid_access_cookie()) {
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
			self::restricted_access_maybe_handle_password_submission($settings);
			self::render_restricted_access_overlay_and_exit($settings, true);
		}

		self::render_restricted_access_overlay_and_exit($settings, false);
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
	 * Handle front-end shared password submission.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function restricted_access_maybe_handle_password_submission(array $settings): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			return;
		}
		if (!isset($_POST['um_restricted_access_submit'])) {
			return;
		}
		$nonce = isset($_POST['um_restricted_access_nonce']) ? sanitize_text_field(wp_unslash($_POST['um_restricted_access_nonce'])) : '';
		if ($nonce === '' || !wp_verify_nonce($nonce, 'um_restricted_access_submit')) {
			self::$restricted_access_password_error = (string) __('Unable to validate request. Please try again.', 'user-manager');
			self::restricted_access_log_access_attempt('', false, 'invalid_nonce');
			return;
		}

		$submitted_password = isset($_POST['um_restricted_access_password']) ? sanitize_text_field(wp_unslash($_POST['um_restricted_access_password'])) : '';
		$saved_password     = self::restricted_access_get_shared_password($settings);
		if ($saved_password !== '' && hash_equals($saved_password, $submitted_password)) {
			self::restricted_access_log_access_attempt($submitted_password, true, '');
			self::restricted_access_set_access_cookie($settings);
			wp_safe_redirect(self::restricted_access_get_current_url());
			exit;
		}

		self::restricted_access_log_access_attempt('', false, $submitted_password);
		self::$restricted_access_password_error = (string) __('Incorrect password. Please try again.', 'user-manager');
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
		nocache_headers();
		status_header(403);

		$message     = self::restricted_access_get_no_access_message($settings);
		$bg_color    = self::restricted_access_get_overlay_background_color($settings);
		$text_color  = self::restricted_access_get_overlay_text_color($settings);
		$overlay_img = self::restricted_access_get_overlay_image_url($settings);
		$overlay_img_max_width = self::restricted_access_get_overlay_image_max_width($settings);
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
				}
				body.um-restricted-access-overlay {
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 100vh;
					text-align: center;
					background-color: <?php echo esc_attr($bg_color); ?>;
					color: <?php echo esc_attr($text_color); ?>;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
				}
				.um-restricted-access-overlay-image-wrap {
					position: fixed;
					inset: 0;
					display: flex;
					align-items: center;
					justify-content: center;
					padding: 20px;
					box-sizing: border-box;
					pointer-events: none;
					z-index: 0;
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
					z-index: 1;
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
			<?php if ($has_image) : ?>
				<div class="um-restricted-access-overlay-image-wrap" aria-hidden="true">
					<img class="um-restricted-access-overlay-image" src="<?php echo esc_url($overlay_img); ?>" alt="" />
				</div>
			<?php endif; ?>
			<div class="um-restricted-access-card">
				<h1><?php echo esc_html($message); ?></h1>
				<?php if ($show_password_form) : ?>
					<p><?php esc_html_e('Enter shared password to continue.', 'user-manager'); ?></p>
					<form class="um-restricted-access-form" method="post" action="">
						<input type="password" name="um_restricted_access_password" autocomplete="current-password" required />
						<br />
						<button type="submit" name="um_restricted_access_submit" value="1"><?php esc_html_e('Access Website', 'user-manager'); ?></button>
						<?php wp_nonce_field('um_restricted_access_submit', 'um_restricted_access_nonce'); ?>
					</form>
					<?php if (self::$restricted_access_password_error !== '') : ?>
						<div class="um-restricted-access-error"><?php echo esc_html(self::$restricted_access_password_error); ?></div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
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
		$path    = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';

		$options = [
			'expires'  => $expires,
			'path'     => $path,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		];
		setcookie('um_restricted_access', $value, $options);
		$_COOKIE['um_restricted_access'] = $value;
	}

	/**
	 * Validate restricted-access cookie signature and expiration.
	 */
	private static function restricted_access_has_valid_access_cookie(): bool {
		$raw = isset($_COOKIE['um_restricted_access']) ? (string) $_COOKIE['um_restricted_access'] : '';
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
		$text = isset($settings['restricted_access_no_access_message'])
			? sanitize_text_field((string) $settings['restricted_access_no_access_message'])
			: '';
		return $text !== '' ? $text : 'This is a private page';
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
}

