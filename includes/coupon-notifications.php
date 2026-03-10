<?php
/**
 * User Manager Coupon Notifications (independent implementation).
 */

if (!defined('ABSPATH')) {
	exit;
}

if (defined('USER_MANAGER_COUPON_NOTICES_LOADED')) {
	return;
}
define('USER_MANAGER_COUPON_NOTICES_LOADED', true);

final class User_Manager_Coupon_Notices {
	private const MY_ACCOUNT_COUPON_ENDPOINT = 'um-coupons';
	private $settings = [];
	private $enabled = false;
	private $options = [];
	private $debug_log = [];
	private $block_presence = [
		'cart' => false,
		'checkout' => false,
	];

	public static function boot(): void {
		(new self())->init();
	}

	private function init(): void {
		$this->settings = get_option('user_manager_settings', []);
		if (!is_array($this->settings)) {
			$this->settings = [];
		}
		$this->enabled = !empty($this->settings['user_coupon_notifications_enabled']);
		$this->options = [
			'show_cart' => $this->flag('coupon_notifications_show_on_cart'),
			'show_checkout' => $this->flag('coupon_notifications_show_on_checkout'),
			'show_account' => $this->flag('coupon_notifications_show_on_my_account'),
			'show_home' => $this->flag('coupon_notifications_show_on_home'),
			'show_product' => $this->flag('coupon_notifications_show_on_product'),
			'show_archives' => $this->flag('coupon_notifications_show_on_archives'),
			'show_posts' => $this->flag('coupon_notifications_show_on_posts'),
			'show_pages' => $this->flag('coupon_notifications_show_on_pages'),
			'collapse' => isset($this->settings['coupon_notifications_collapse_threshold'])
				? max(0, (int) $this->settings['coupon_notifications_collapse_threshold'])
				: 1,
			'hide_store_credit' => $this->flag('coupon_notifications_hide_store_credit'),
			'block_support' => $this->flag('coupon_notifications_block_support'),
			'debug' => !empty($this->settings['coupon_notifications_debug']),
			'sort_by_expiration' => $this->flag('coupon_notifications_sort_by_expiration'),
		];

		if ($this->options['debug']) {
			add_action('wp', [$this, 'prime_debug_state'], 5);
			add_action('wp_head', [$this, 'debug_assets'], 5);
			if (function_exists('wp_body_open')) {
				add_action('wp_body_open', [$this, 'render_debug_bar'], 4);
			} else {
				add_action('wp_footer', [$this, 'render_debug_bar'], 4);
			}
			add_action('wp_footer', [$this, 'render_debug_overlay'], 1);
			add_action('wp_footer', [$this, 'render_debug_ping'], 999);
			add_action('woocommerce_before_main_content', [$this, 'render_debug_notice'], 1);
		}

		if ($this->options['hide_store_credit']) {
			add_action('wp_head', [$this, 'hide_store_credit_css'], 20);
		}
		add_action('wp_head', [$this, 'add_coupon_notification_styles'], 20);

		if ($this->options['show_cart']) {
			add_action('woocommerce_before_cart', [$this, 'render_coupon_notifications'], 5);
		}
		if ($this->options['show_checkout']) {
			add_action('woocommerce_before_checkout_form', [$this, 'render_coupon_notifications'], 5);
		}
		if ($this->options['show_account']) {
			add_action('woocommerce_account_content', [$this, 'render_coupon_notifications'], 5);
		}
		if ($this->options['show_product']) {
			add_action('woocommerce_before_single_product', [$this, 'render_coupon_notifications'], 5);
		}
		if ($this->options['show_archives']) {
			add_action('woocommerce_before_shop_loop', [$this, 'render_coupon_notifications'], 5);
		}
		add_action('loop_start', [$this, 'render_coupon_notifications_elsewhere']);
		add_action('wp_ajax_umcn_apply_coupon', [$this, 'handle_apply_coupon']);
		if ($this->is_my_account_coupon_screen_enabled()) {
			add_action('init', [$this, 'register_my_account_coupon_endpoint'], 20);
			add_filter('query_vars', [$this, 'add_my_account_coupon_query_var'], 20, 1);
			add_filter('woocommerce_get_query_vars', [$this, 'add_my_account_coupon_wc_query_var'], 20, 1);
			add_filter('woocommerce_account_menu_items', [$this, 'add_my_account_coupon_menu_item'], 40, 1);
			add_action('woocommerce_account_' . self::MY_ACCOUNT_COUPON_ENDPOINT . '_endpoint', [$this, 'render_my_account_coupon_endpoint']);
		}
       if ($this->options['block_support']) {
           add_filter('render_block', [$this, 'maybe_detect_block'], 10, 2);
           add_action('wp_footer', [$this, 'inject_block_markup'], 5);
       } else {
           $this->block_presence = [
               'cart' => false,
               'checkout' => false,
           ];
       }
       
       // Block checkout shipping notice
       if ($this->flag('coupon_notifications_block_checkout_shipping_notice')) {
           add_action('wp_head', [$this, 'add_block_checkout_shipping_notice_styles'], 20);
           add_action('wp_footer', [$this, 'inject_block_checkout_shipping_notice'], 20);
       }
       
       // Classic checkout shipping notice
       if ($this->flag('coupon_notifications_classic_checkout_shipping_notice')) {
           add_action('wp_head', [$this, 'add_classic_checkout_shipping_notice_styles'], 20);
           add_action('woocommerce_review_order_before_payment', [$this, 'render_classic_checkout_shipping_notice'], 10);
       }
	}

	private function flag(string $key): bool {
		return $this->enabled && !empty($this->settings[$key]);
	}

	private function log(string $message, $data = null): void {
		if (!$this->options['debug']) {
			return;
		}
		if ($data !== null) {
			$message .= ': ' . (is_scalar($data) ? $data : wp_json_encode($data));
		}
		$this->debug_log[] = $message;
	}

	public function prime_debug_state(): void {
		if (is_admin()) {
			return;
		}
		if (function_exists('wp_is_json_request') && wp_is_json_request()) {
			return;
		}
		$this->log('Template', get_page_template_slug() ?: 'default');
		$this->should_display(true);
		$this->get_user_coupons();
	}

	public function debug_assets(): void {
		?>
		<style>
			.umcn-debug-bar { position:fixed;top:0;left:0;right:0;z-index:9999;background:rgba(26,32,44,.95);color:#f7fafc;font:12px/1.4 Menlo,monospace;padding:6px 14px;box-shadow:0 2px 8px rgba(0,0,0,.3); }
			.umcn-debug-bar code { color:#bfe3ff; }
			body.umcn-debug-offset { padding-top:40px; }
		</style>
		<script>
			document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('umcn-debug-offset');});
		</script>
		<?php
	}

	public function render_debug_bar(): void {
		if (!is_user_logged_in() || empty($this->debug_log)) {
			return;
		}
		echo '<div class="umcn-debug-bar"><strong>' . esc_html__('Coupon Debug:', 'user-manager') . '</strong> ' . esc_html(implode(' | ', $this->debug_log)) . '</div>';
	}

	public function render_debug_overlay(): void {
		if (!is_user_logged_in() || empty($this->debug_log)) {
			return;
		}
		?>
		<div style="position:fixed;left:20px;bottom:20px;background:rgba(0,0,0,.85);color:#fff;padding:12px 16px;font-size:12px;max-width:420px;z-index:9998;">
			<strong><?php esc_html_e('Coupon Debug Snapshot', 'user-manager'); ?></strong>
			<ul style="margin:6px 0 0 18px;padding:0;">
				<?php foreach ($this->debug_log as $entry) : ?>
					<li><?php echo esc_html($entry); ?></li>
				<?php endforeach; ?>
			</ul>
			<small><?php echo esc_html(wp_json_encode($this->collect_debug_settings())); ?></small>
		</div>
		<?php
	}

	public function render_debug_ping(): void {
		if (!is_user_logged_in()) {
			return;
		}
		echo '<div style="position:fixed;bottom:0;right:0;background:#1a202c;color:#f7fafc;font-size:11px;padding:4px 6px;z-index:9999;">' . esc_html__('Coupon debug ping active.', 'user-manager') . '</div>';
	}

	public function render_debug_notice(): void {
		if (!function_exists('wc_print_notice') || !is_user_logged_in()) {
			return;
		}
		$message = empty($this->debug_log)
			? __('Debug mode active but no messages recorded yet.', 'user-manager')
			: implode(' | ', $this->debug_log);
		wc_print_notice('<strong>' . esc_html__('Coupon Debug:', 'user-manager') . '</strong> ' . esc_html($message), 'notice');
	}

	private function collect_debug_settings(): array {
		return [
			'enabled' => $this->enabled,
			'collapse_threshold' => $this->options['collapse'],
			'show_cart' => $this->options['show_cart'],
			'show_checkout' => $this->options['show_checkout'],
			'show_account' => $this->options['show_account'],
			'show_home' => $this->options['show_home'],
			'show_product' => $this->options['show_product'],
			'show_archives' => $this->options['show_archives'],
			'show_posts' => $this->options['show_posts'],
			'show_pages' => $this->options['show_pages'],
			'debug' => $this->options['debug'],
		];
	}

	private function is_my_account_coupon_screen_enabled(): bool {
		return !empty($this->settings['my_account_coupon_screen_enabled']);
	}

	private function get_my_account_coupon_screen_menu_title(): string {
		$menu_title = isset($this->settings['my_account_coupon_screen_menu_title'])
			? trim((string) $this->settings['my_account_coupon_screen_menu_title'])
			: '';
		return $menu_title !== '' ? $menu_title : __('Coupons', 'user-manager');
	}

	private function get_my_account_coupon_screen_page_title(): string {
		$page_title = isset($this->settings['my_account_coupon_screen_page_title'])
			? trim((string) $this->settings['my_account_coupon_screen_page_title'])
			: '';
		return $page_title !== '' ? $page_title : __('Coupons', 'user-manager');
	}

	private function get_my_account_coupon_screen_page_description(): string {
		return isset($this->settings['my_account_coupon_screen_page_description'])
			? trim((string) $this->settings['my_account_coupon_screen_page_description'])
			: '';
	}

	public function register_my_account_coupon_endpoint(): void {
		if (!$this->is_my_account_coupon_screen_enabled()) {
			return;
		}
		add_rewrite_endpoint(self::MY_ACCOUNT_COUPON_ENDPOINT, EP_ROOT | EP_PAGES);
	}

	public function add_my_account_coupon_query_var(array $vars): array {
		if (!$this->is_my_account_coupon_screen_enabled()) {
			return $vars;
		}
		if (!in_array(self::MY_ACCOUNT_COUPON_ENDPOINT, $vars, true)) {
			$vars[] = self::MY_ACCOUNT_COUPON_ENDPOINT;
		}
		return $vars;
	}

	public function add_my_account_coupon_wc_query_var(array $query_vars): array {
		if (!$this->is_my_account_coupon_screen_enabled()) {
			return $query_vars;
		}
		$query_vars[self::MY_ACCOUNT_COUPON_ENDPOINT] = self::MY_ACCOUNT_COUPON_ENDPOINT;
		return $query_vars;
	}

	public function add_my_account_coupon_menu_item(array $items): array {
		if (!$this->is_my_account_coupon_screen_enabled() || !is_user_logged_in()) {
			return $items;
		}

		$label      = $this->get_my_account_coupon_screen_menu_title();
		$menu_items = [self::MY_ACCOUNT_COUPON_ENDPOINT => $label];
		$merged     = [];
		$inserted   = false;

		foreach ($items as $key => $value) {
			if ($key === 'customer-logout' && !$inserted) {
				$merged   = array_merge($merged, $menu_items);
				$inserted = true;
			}
			$merged[$key] = $value;
		}

		if (!$inserted) {
			$merged = array_merge($merged, $menu_items);
		}

		return $merged;
	}

	public function render_my_account_coupon_endpoint(): void {
		if (!$this->is_my_account_coupon_screen_enabled() || !is_user_logged_in()) {
			return;
		}

		echo '<div class="um-my-account-coupon-screen">';
		echo '<h2>' . esc_html($this->get_my_account_coupon_screen_page_title()) . '</h2>';

		$description = $this->get_my_account_coupon_screen_page_description();
		if ($description !== '') {
			echo '<p>' . esc_html($description) . '</p>';
		}

		$coupons = $this->get_user_coupons();
		if (empty($coupons)) {
			if (function_exists('wc_print_notice')) {
				wc_print_notice(__('No coupon notifications are currently available for your account.', 'user-manager'), 'notice');
			} else {
				echo '<p class="woocommerce-info">' . esc_html__('No coupon notifications are currently available for your account.', 'user-manager') . '</p>';
			}
			echo '</div>';
			return;
		}

		// Show all available coupon notices on this dedicated screen.
		$original_collapse = isset($this->options['collapse']) ? (int) $this->options['collapse'] : 1;
		$this->options['collapse'] = PHP_INT_MAX;
		$this->render_coupon_markup($coupons);
		$this->options['collapse'] = $original_collapse;
		$this->print_apply_script();

		echo '</div>';
	}

	public function hide_store_credit_css(): void {
		echo '<style>.wc-store-credit-cart-coupons-container{display:none!important;}</style>';
	}

	public function add_coupon_notification_styles(): void {
		echo '<style>
		.umcn-show-more{margin-bottom:25px!important;}
		.woocommerce-message[data-coupon-code] {
			display: flex !important;
			align-items: flex-start !important;
			gap: 12px !important;
		}
		.woocommerce-message[data-coupon-code] > *:not(.umcn-apply) {
			flex: 1;
		}
		.woocommerce-message[data-coupon-code] .umcn-apply {
			flex-shrink: 0;
			margin-top: 0 !important;
		}
		.umcn-wrapper .umcn-hidden {
			display: none !important;
		}
		</style>';
	}

	public function render_coupon_notifications(): void {
		if ($this->should_use_block_injection()) {
			return;
		}
		if ($this->is_my_account_coupon_screen_enabled() && $this->is_on_my_account_coupon_screen_endpoint()) {
			return;
		}
		if (!$this->should_display()) {
			return;
		}
		$this->maybe_render_notice($this->get_user_coupons());
	}

	private function is_on_my_account_coupon_screen_endpoint(): bool {
		if (!function_exists('WC') || !WC() || !isset(WC()->query) || !is_object(WC()->query)) {
			return false;
		}
		if (!function_exists('is_account_page') || !is_account_page()) {
			return false;
		}
		$current_endpoint = (string) WC()->query->get_current_endpoint();
		return $current_endpoint === self::MY_ACCOUNT_COUPON_ENDPOINT;
	}

	private function should_display(bool $log_only = false): bool {
		if (!is_user_logged_in()) {
			if ($this->options['debug'] || $log_only) {
				$this->log('User not logged in');
			}
			return false;
		}
		$matches = [
			'cart' => $this->options['show_cart'] && function_exists('is_cart') && is_cart(),
			'checkout' => $this->options['show_checkout'] && function_exists('is_checkout') && is_checkout(),
			'account' => $this->options['show_account'] && function_exists('is_account_page') && is_account_page(),
			'product' => $this->options['show_product'] && function_exists('is_product') && is_product(),
			'archives' => $this->options['show_archives'] && function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag()),
			'home' => $this->options['show_home'] && (is_front_page() || is_home()),
			'posts' => $this->options['show_posts'] && is_singular('post'),
			'pages' => $this->options['show_pages'] && is_page(),
		];
		if ($this->options['debug'] || $log_only) {
			foreach ($matches as $key => $match) {
				$this->log($key . ' => ' . ($match ? 'true' : 'false'));
			}
		}
		foreach ($matches as $match) {
			if ($match) {
				return true;
			}
		}
		return false;
	}

	private function should_use_block_injection(): bool {
		if (!$this->options['block_support']) {
			return false;
		}
		return $this->block_presence['cart'] || $this->block_presence['checkout'] || $this->page_contains_block();
	}

	private function page_contains_block(): bool {
		if (!function_exists('has_block')) {
			return false;
		}
		$post = get_post();
		if (!$post instanceof WP_Post) {
			return false;
		}
		$content = $post->post_content ?? '';
		return has_block('woocommerce/checkout', $content) || has_block('woocommerce/cart', $content);
	}

	private function maybe_render_notice(array $coupons): void {
		if (empty($coupons)) {
			return;
		}
		$this->render_coupon_markup($coupons);
		$this->print_apply_script();
	}

	private function get_user_coupons(): array {
		static $cache = null;
		if ($cache !== null) {
			return $cache;
		}
		$cache = [];
		if (!is_user_logged_in()) {
			return $cache;
		}
		if (!class_exists('WC_Coupon')) {
			return $cache;
		}
		$user = wp_get_current_user();
		$emails = array_values(array_filter(array_unique([
			strtolower($user->user_email ?? ''),
			strtolower((string) get_user_meta($user->ID, 'billing_email', true)),
		])));
		if ($this->options['debug']) {
			$this->log('Coupon lookup user', [
				'id' => $user->ID,
				'username' => $user->user_login ?? '',
				'emails' => $emails,
			]);
		}
		if (empty($emails)) {
			return $cache;
		}
		$meta_query = ['relation' => 'OR'];
		foreach ($emails as $email) {
			$meta_query[] = [
				'key' => 'customer_email',
				'value' => $email,
				'compare' => 'LIKE',
			];
			$meta_query[] = [
				'key' => '_um_user_coupon_user_email',
				'value' => $email,
				'compare' => '=',
			];
		}
		$meta_query[] = [
			'key' => '_um_user_coupon_user_id',
			'value' => $user->ID,
			'compare' => '=',
		];
		if ($this->options['debug']) {
			$this->log('Coupon lookup meta', $meta_query);
		}
		$posts = get_posts([
			'post_type' => 'shop_coupon',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'orderby' => 'date',
			'order' => 'DESC',
			'meta_query' => $meta_query,
		]);
		$now = current_time('timestamp');
		$found = [];
		foreach ($posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}
			$code = strtoupper($coupon->get_code());
			if (isset($found[$code])) {
				if ($this->options['debug']) {
					$this->log('Coupon ' . $code . ' skipped: duplicate code already evaluated.');
				}
				continue;
			}
			if (!$this->coupon_is_valid($coupon, $user->ID, $emails[0])) {
				// coupon_is_valid() will log a human-readable reason in debug mode.
				continue;
			}
			$expires = $coupon->get_date_expires();
			$expiry = '';
			$relative = '';
			$expiry_timestamp = null;
			if ($expires) {
				$expiry_timestamp = $expires->getTimestamp();
				$expiry = $expires->date('m/d/Y');
				$diff_days = floor(($expiry_timestamp - $now) / DAY_IN_SECONDS);
				if ($diff_days < 1) {
					$relative = 'today';
				} elseif ($diff_days === 1) {
					$relative = '1 day';
				} elseif ($diff_days < 7) {
					$relative = $diff_days . ' days';
				} elseif ($diff_days < 30) {
					$relative = floor($diff_days / 7) . ' weeks';
				} else {
					$relative = floor($diff_days / 30) . ' months';
				}
			}
			$found[$code] = [
				'id' => $coupon_id,
				'code' => $coupon->get_code(),
				'amount' => $coupon->get_amount(),
				'discount_type' => $coupon->get_discount_type(),
				'expiry_date' => $expiry,
				'expiry_relative' => $relative,
				'expiry_timestamp' => $expiry_timestamp,
			];
		}
		$result = array_values($found);
		
		// Sort by expiration date if enabled
		if ($this->options['sort_by_expiration']) {
			usort($result, function($a, $b) {
				// Coupons with expiration come first
				$a_has_expiry = $a['expiry_timestamp'] !== null;
				$b_has_expiry = $b['expiry_timestamp'] !== null;
				
				// If one has expiry and the other doesn't, the one with expiry comes first
				if ($a_has_expiry && !$b_has_expiry) {
					return -1;
				}
				if (!$a_has_expiry && $b_has_expiry) {
					return 1;
				}
				
				// Both have expiry: sort by expiration date (soonest first)
				if ($a_has_expiry && $b_has_expiry) {
					if ($a['expiry_timestamp'] !== $b['expiry_timestamp']) {
						return $a['expiry_timestamp'] - $b['expiry_timestamp'];
					}
					// Same expiration date: sort by highest value to lowest
					return $b['amount'] <=> $a['amount'];
				}
				
				// Both have no expiry: sort by highest value to lowest
				return $b['amount'] <=> $a['amount'];
			});
		}
		
		if ($this->options['debug']) {
			$this->log('Coupon lookup result', array_keys($found));
		}
		return $cache = $result;
	}

	private function coupon_is_valid(WC_Coupon $coupon, int $user_id, string $email): bool {
		$code = strtoupper($coupon->get_code());

		if ($coupon->get_discount_type() === 'store_credit') {
			if ($this->options['debug']) {
				$this->log('Coupon ' . $code . ' will not be shown: store credit coupons are hidden by User Coupon Notifications settings.');
			}
			return false;
		}

		if ($coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit()) {
			if ($this->options['debug']) {
				$this->log('Coupon ' . $code . ' will not be shown: global usage limit has been reached.');
			}
			return false;
		}

		$per_user = $coupon->get_usage_limit_per_user();
		if ($per_user > 0) {
			$data_store = WC_Data_Store::load('coupon');
			if ($data_store->get_usage_by_user_id($coupon, $user_id) >= $per_user) {
				if ($this->options['debug']) {
					$this->log('Coupon ' . $code . ' will not be shown: per-user usage limit has been reached for this account.');
				}
				return false;
			}
		}

		$allowed = $coupon->get_email_restrictions();
		if (!empty($allowed)) {
			$match = false;
			foreach ($allowed as $allowed_email) {
				if (strtolower(trim($allowed_email)) === strtolower($email)) {
					$match = true;
					break;
				}
			}
			if (!$match) {
				if ($this->options['debug']) {
					$this->log('Coupon ' . $code . ' will not be shown: email restrictions do not include ' . $email . '.');
				}
				return false;
			}
		}

		if ($this->options['debug']) {
			$this->log('Coupon ' . $code . ' passed validation and is eligible for User Coupon Notifications.');
		}

		return true;
	}

	private function is_coupon_applied(string $code): bool {
		return function_exists('WC') && WC()->cart && in_array($code, WC()->cart->get_applied_coupons(), true);
	}

	private function render_coupon_markup(array $coupons): void {
		// Filter out applied coupons, $0.00 coupons, 0% coupons, and expired coupons before counting
		$displayable_coupons = [];
		$now = current_time('timestamp');
		
		foreach ($coupons as $coupon) {
			$code = strtoupper($coupon['code']);

			if ($this->is_coupon_applied($coupon['code'])) {
				if ($this->options['debug']) {
					$this->log('Coupon ' . $code . ' hidden in banner: already applied in the cart.');
				}
				continue;
			}
			
			// Skip expired coupons
			if (!empty($coupon['expiry_timestamp']) && $coupon['expiry_timestamp'] < $now) {
				if ($this->options['debug']) {
					$this->log('Coupon ' . $code . ' hidden in banner: coupon has expired.');
				}
				continue;
			}
			
			$amount = (float) ($coupon['amount'] ?? 0);
			$discount_type = $coupon['discount_type'] ?? '';
			
			// Skip if amount is 0 or less
			if ($amount <= 0) {
				if ($this->options['debug']) {
					$this->log('Coupon ' . $code . ' hidden in banner: amount is 0.00 or less.');
				}
				continue;
			}
			
			// For percentage coupons, also skip if 0%
			if (($discount_type === 'percent' || $discount_type === 'percent_product') && $amount == 0) {
				if ($this->options['debug']) {
					$this->log('Coupon ' . $code . ' hidden in banner: percentage discount is 0%.');
				}
				continue;
			}
			
			if ($this->options['debug']) {
				$expiry_debug = !empty($coupon['expiry_date']) ? $coupon['expiry_date'] : 'no expiration date';
				$this->log(
					'Coupon ' . $code . ' will be shown in banner: amount ' . $amount . ', type ' . $discount_type . ', ' . $expiry_debug . '.'
				);
			}

			$displayable_coupons[] = $coupon;
		}
		
		$count = count($displayable_coupons);
		$threshold = $this->options['collapse'];
		$has_more = $threshold > 0 && $count > $threshold;

		if ($this->options['debug']) {
			$codes = array_map(static function ($coupon) {
				return strtoupper($coupon['code']);
			}, $displayable_coupons);
			$this->log(
				'Displayable coupons after filtering: ' . implode(', ', $codes) .
				'. Collapse threshold: ' . $threshold .
				'. Initial visible count: ' . ($has_more ? $threshold : $count) .
				'. Hidden count: ' . ($has_more ? $count - $threshold : 0) . '.'
			);
		}
		?>
		<div class="umcn-wrapper">
			<?php
			$index = 0;
			foreach ($displayable_coupons as $coupon) :
				
				$expiry = '';
				if (!empty($coupon['expiry_relative']) && !empty($coupon['expiry_date'])) {
					$expiry = ' (' . sprintf(__('Expires in %1$s on %2$s', 'user-manager'), $coupon['expiry_relative'], $coupon['expiry_date']) . ')';
				}
				
				// Format coupon value based on discount type
				$value_text = '';
				$discount_type = $coupon['discount_type'] ?? '';
				$amount = (float) ($coupon['amount'] ?? 0);
				
				// Get currency symbol and format without extra HTML classes
				$currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$';
				$formatted_amount = $currency_symbol . number_format($amount, 2, '.', ',');
				
				if ($discount_type === 'fixed_cart' || $discount_type === 'fixed_product') {
					$value_text = $formatted_amount . ' ' . __('off', 'user-manager');
				} elseif ($discount_type === 'percent' || $discount_type === 'percent_product') {
					$value_text = number_format($amount, 0) . '% ' . __('off', 'user-manager');
				} elseif ($discount_type === 'store_credit') {
					$value_text = $formatted_amount . ' ' . __('store credit', 'user-manager');
				} else {
					// Fallback for other types
					$value_text = $formatted_amount . ' ' . __('off', 'user-manager');
				}
				
				$is_hidden = $has_more && $index >= $threshold;
				?>
				<div class="woocommerce-message <?php echo $is_hidden ? 'umcn-hidden' : ''; ?>" data-coupon-code="<?php echo esc_attr($coupon['code']); ?>" <?php echo $is_hidden ? 'style="display:none!important;"' : ''; ?>>
					<div>
						<?php printf(__('Coupon available: %s%s', 'user-manager'), $value_text, esc_html($expiry)); ?>
						<div style="font-size:0.85em;margin-top:4px;"><?php echo esc_html(strtoupper($coupon['code'])); ?></div>
					</div>
					<button type="button" class="button umcn-apply" data-coupon="<?php echo esc_attr($coupon['code']); ?>">
						<?php esc_html_e('Apply Now', 'user-manager'); ?>
					</button>
				</div>
				<?php
				$index++;
			endforeach;
			if ($has_more) : ?>
				<button type="button" class="button umcn-show-more">
					<?php printf(__('Show %d More Coupon(s)', 'user-manager'), $count - $threshold); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php
	}

	private function print_apply_script(bool $include_block_script = false): void {
		static $printed = false;
		if ($printed) {
			return;
		}
		$printed = true;
		$ajax_url = admin_url('admin-ajax.php');
		$nonce = wp_create_nonce('umcn_apply_coupon');
		?>
		<script>
		(function(){
			const ajaxUrl = '<?php echo esc_url($ajax_url); ?>';
			const nonce = '<?php echo esc_js($nonce); ?>';

			function handleShowMore(event) {
				const button = event.target.closest('.umcn-show-more');
				if (!button) {
					return;
				}
				event.preventDefault();
				const wrapper = button.closest('.umcn-wrapper');
				if (!wrapper) {
					return;
				}
				wrapper.querySelectorAll('.umcn-hidden').forEach(function(el){
					el.style.display = '';
					el.style.removeProperty('display');
					el.removeAttribute('style');
					el.classList.remove('umcn-hidden');
				});
				button.remove();
			}

			function handleApply(event) {
				const button = event.target.closest('.umcn-apply');
				if (!button) {
					return;
				}
				event.preventDefault();
				const code = button.getAttribute('data-coupon');
				if (!code) {
					return;
				}
				button.disabled = true;
				const originalText = button.textContent;
				button.textContent = '<?php echo esc_js(__('Applying...', 'user-manager')); ?>';

				const body = new FormData();
				body.append('action', 'umcn_apply_coupon');
				body.append('coupon_code', code);
				body.append('security', nonce);

				fetch(ajaxUrl, { method: 'POST', body })
					.then(response => response.json())
					.then(data => {
						if (data && data.success) {
							button.textContent = '<?php echo esc_js(__('Applied!', 'user-manager')); ?>';
							setTimeout(function(){ window.location.reload(); }, 800);
						} else {
							alert((data && data.data) || '<?php echo esc_js(__('Could not apply coupon.', 'user-manager')); ?>');
							button.disabled = false;
							button.textContent = originalText;
						}
					})
					.catch(function(){
						alert('<?php echo esc_js(__('Error applying coupon.', 'user-manager')); ?>');
						button.disabled = false;
						button.textContent = originalText;
					});
			}

			document.addEventListener('click', function(event){
				if (event.target.closest('.umcn-show-more')) {
					handleShowMore(event);
				} else if (event.target.closest('.umcn-apply')) {
					handleApply(event);
				}
			});

			<?php if ($include_block_script) : ?>
			(function attachToBlocks(){
				const targets = [
					{ id: 'umcn-block-injection-checkout', selector: '.wc-block-checkout__main' },
					{ id: 'umcn-block-injection-cart', selector: '.wc-block-cart__main' },
				];

				function inject() {
					let allInjected = true;
					targets.forEach(function(target){
						const source = document.getElementById(target.id);
						if (!source) {
							return;
						}
						const dest = document.querySelector(target.selector);
						if (dest) {
							const html = source.innerHTML;
							if (html) {
								const container = document.createElement('div');
								container.innerHTML = html;
								const node = container.firstElementChild;
								if (node) {
									dest.insertBefore(node, dest.firstChild);
								}
							}
							source.remove();
						} else {
							allInjected = false;
						}
					});
					return allInjected;
				}

				if (inject()) {
					return;
				}

				const observer = new MutationObserver(function(){
					if (inject()) {
						observer.disconnect();
					}
				});
				observer.observe(document.body, { childList: true, subtree: true });
			})();
			<?php endif; ?>
		})();
		</script>
		<?php
	}

	public function render_coupon_notifications_elsewhere($query = null): void {
		if ($this->should_use_block_injection()) {
			return;
		}
		static $rendered = false;
		if ($rendered || is_admin()) {
			return;
		}
		if (!is_user_logged_in()) {
			return;
		}
		if ($query instanceof WP_Query && !$query->is_main_query()) {
			return;
		}
		$handled = (
			($this->options['show_cart'] && function_exists('is_cart') && is_cart()) ||
			($this->options['show_checkout'] && function_exists('is_checkout') && is_checkout()) ||
			($this->options['show_account'] && function_exists('is_account_page') && is_account_page()) ||
			($this->options['show_product'] && function_exists('is_product') && is_product()) ||
			($this->options['show_archives'] && function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag()))
		);
		if ($handled) {
			return;
		}
		$show = (
			($this->options['show_home'] && (is_front_page() || is_home())) ||
			($this->options['show_posts'] && is_singular('post')) ||
			($this->options['show_pages'] && is_page() && !is_front_page())
		);
		if (!$show) {
			return;
		}
		$coupons = $this->get_user_coupons();
		if (empty($coupons)) {
			return;
		}
		$rendered = true;
		$this->render_coupon_markup($coupons);
		$this->print_apply_script();
	}

	public function maybe_detect_block(string $content, array $block): string {
		if (empty($block['blockName']) || is_admin()) {
			return $content;
		}
		if ('woocommerce/cart' === $block['blockName']) {
			$this->block_presence['cart'] = true;
		}
		if ('woocommerce/checkout' === $block['blockName']) {
			$this->block_presence['checkout'] = true;
		}
		return $content;
	}

	public function inject_block_markup(): void {
		if (!$this->options['block_support']) {
			return;
		}
		if (!($this->block_presence['cart'] || $this->block_presence['checkout'])) {
			return;
		}
		if (!$this->enabled || !is_user_logged_in()) {
			return;
		}
		$coupons = $this->get_user_coupons();
		if (empty($coupons)) {
			return;
		}
		$markup = $this->get_coupon_markup_html($coupons);
		if ($markup === '') {
			return;
		}
		if ($this->block_presence['checkout'] && $this->options['show_checkout']) {
			echo '<div id="umcn-block-injection-checkout" class="umcn-block-injection" style="display:none;">' . $markup . '</div>';
		}
		if ($this->block_presence['cart'] && $this->options['show_cart']) {
			echo '<div id="umcn-block-injection-cart" class="umcn-block-injection" style="display:none;">' . $markup . '</div>';
		}
		$this->print_apply_script(true);
	}

	private function get_coupon_markup_html(array $coupons): string {
		ob_start();
		$this->render_coupon_markup($coupons);
		return trim(ob_get_clean() ?? '');
	}

	/**
	 * Check if any applied coupon covers 100% of the cart subtotal.
	 */
	private function check_coupon_covers_full_subtotal(): bool {
		if (!function_exists('WC') || !WC()->cart) {
			return false;
		}
		
		$cart = WC()->cart;
		$applied_coupons = $cart->get_applied_coupons();
		
		if (empty($applied_coupons)) {
			return false;
		}
		
		// Get cart subtotal (excluding tax)
		$subtotal = (float) $cart->get_subtotal();
		
		// Sum all coupon discounts
		$total_discount = 0.0;
		foreach ($cart->get_coupon_discount_totals() as $discount) {
			$total_discount += (float) $discount;
		}
		
		// Check if total discount covers 100% of subtotal (with $0.01 tolerance for rounding)
		return $total_discount >= ($subtotal - 0.01);
	}

	/**
	 * Add CSS styles for block checkout shipping notice.
	 */
	public function add_block_checkout_shipping_notice_styles(): void {
		if (!is_checkout()) {
			return;
		}
		?>
		<style>
		.um-block-checkout-shipping-notice {
			background: #fff3cd !important;
			border: 1px solid #ffc107 !important;
			border-radius: 4px !important;
			padding: 12px 16px !important;
			margin: 12px 0 !important;
			font-size: 14px !important;
			color: #856404 !important;
		}
		.um-block-checkout-shipping-notice strong {
			color: #856404 !important;
			font-weight: 600 !important;
		}
		</style>
		<?php
	}

	/**
	 * Inject shipping notice into block checkout.
	 */
	public function inject_block_checkout_shipping_notice(): void {
		if (!is_checkout()) {
			return;
		}
		
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}
		
		if (!$this->check_coupon_covers_full_subtotal()) {
			return;
		}
		
		$title = !empty($this->settings['coupon_notifications_shipping_notice_title']) 
			? $this->settings['coupon_notifications_shipping_notice_title'] 
			: 'Coupon Notice';
		$description = !empty($this->settings['coupon_notifications_shipping_notice_description']) 
			? $this->settings['coupon_notifications_shipping_notice_description'] 
			: 'Coupons and store credits apply to product prices only, and do not cover shipping costs. Shipping is calculated separately from our shipping carriers.';
		
		$notice_html = '<div class="um-block-checkout-shipping-notice" style="display:none;">';
		$notice_html .= '<strong>' . esc_html($title) . '</strong><br>';
		$notice_html .= nl2br(esc_html($description));
		$notice_html .= '</div>';
		
		echo $notice_html;
		?>
		<script>
		(function() {
			if (typeof jQuery === 'undefined') {
				return;
			}
			
			var noticeData = <?php echo wp_json_encode(['notice_html' => $notice_html]); ?>;
			var noticeInjected = false;
			
			function checkCouponCoversFullSubtotal() {
				// Try WooCommerce Blocks Store API first
				if (typeof wc_store_api !== 'undefined' && wc_store_api.cart && typeof wc_store_api.cart.getCartData === 'function') {
					try {
						var cartData = wc_store_api.cart.getCartData();
						if (cartData && cartData.totals) {
							var subtotal = parseFloat(cartData.totals.total_items || 0);
							var discountTotal = parseFloat(cartData.totals.total_discount || 0);
							return discountTotal >= (subtotal - 0.01);
						}
					} catch(e) {
						// Fallback to DOM parsing
					}
				}
				
				// Fallback: Parse DOM for subtotal and discount
				var subtotalEl = jQuery('.wc-block-components-totals-item__value, .wc-block-components-totals-item:contains("Subtotal")').first();
				var discountEl = jQuery('.wc-block-components-totals-item__value, .wc-block-components-totals-item:contains("Discount")').first();
				
				if (subtotalEl.length && discountEl.length) {
					var subtotalText = subtotalEl.text().replace(/[^0-9.-]/g, '');
					var discountText = discountEl.text().replace(/[^0-9.-]/g, '');
					var subtotal = parseFloat(subtotalText) || 0;
					var discount = Math.abs(parseFloat(discountText) || 0);
					return discount >= (subtotal - 0.01);
				}
				
				// If we can't determine, don't show (PHP already checked, so if we're here, notice should show)
				return true;
			}
			
			function injectCouponNotice() {
				var checkoutBlock = jQuery('.wp-block-woocommerce-checkout, .wc-block-checkout');
				if (!checkoutBlock.length) {
					return false;
				}
				
				if (!checkCouponCoversFullSubtotal()) {
					jQuery('.um-block-checkout-shipping-notice').remove();
					noticeInjected = false;
					return false;
				}
				
				// Check if already injected
				if (jQuery('.um-block-checkout-shipping-notice').length > 0 && noticeInjected) {
					return true;
				}
				
				// Selector array for coupon section
				var selectors = [
					'.wc-block-components-totals-coupon',
					'.wc-block-checkout__coupon-form',
					'.wc-block-components-totals-coupon__content',
					'[class*="coupon"]',
					'.wc-block-components-totals-wrapper'
				];
				
				var target = null;
				for (var i = 0; i < selectors.length; i++) {
					target = checkoutBlock.find(selectors[i]).first();
					if (target.length) {
						break;
					}
				}
				
				if (target && target.length) {
					var notice = jQuery(noticeData.notice_html);
					notice.show();
					
					// Try to find coupon input field first
					var couponInput = target.find('input[type="text"], input[type="email"]').first();
					if (couponInput.length) {
						couponInput.after(notice);
					} else {
						target.prepend(notice);
					}
					
					noticeInjected = true;
					return true;
				}
				
				return false;
			}
			
			// Debounce function
			function debounce(func, wait) {
				var timeout;
				return function() {
					var context = this, args = arguments;
					clearTimeout(timeout);
					timeout = setTimeout(function() {
						func.apply(context, args);
					}, wait);
				};
			}
			
			var debouncedInject = debounce(injectCouponNotice, 300);
			var debouncedRemove = debounce(function() {
				if (!checkCouponCoversFullSubtotal()) {
					jQuery('.um-block-checkout-shipping-notice').remove();
					noticeInjected = false;
				}
			}, 300);
			
			jQuery(document).ready(function($) {
				// Initial attempts with delays
				var attempts = [0, 500, 1500, 3000];
				attempts.forEach(function(delay) {
					setTimeout(function() {
						if (!noticeInjected) {
							injectCouponNotice();
						}
					}, delay);
				});
				
				// Listen for checkout updates
				jQuery(document.body).on('updated_checkout', debouncedInject);
				jQuery(document.body).on('applied_coupon removed_coupon', debouncedInject);
				jQuery(document.body).on('updated_checkout applied_coupon removed_coupon', debouncedRemove);
				
				// MutationObserver for DOM changes
				if (typeof MutationObserver !== 'undefined') {
					var observer = new MutationObserver(debounce(function() {
						if (!noticeInjected) {
							injectCouponNotice();
						} else {
							debouncedRemove();
						}
					}, 1000));
					
					var checkoutBlock = jQuery('.wp-block-woocommerce-checkout, .wc-block-checkout').first()[0];
					if (checkoutBlock) {
						observer.observe(checkoutBlock, {
							childList: true,
							subtree: true
						});
					}
				}
			});
		})();
		</script>
		<?php
	}

	/**
	 * Add CSS styles for classic checkout shipping notice.
	 */
	public function add_classic_checkout_shipping_notice_styles(): void {
		if (!is_checkout()) {
			return;
		}
		?>
		<style>
		.um-classic-checkout-shipping-notice {
			background: #fff3cd !important;
			border: 1px solid #ffc107 !important;
			border-radius: 4px !important;
			padding: 12px 16px !important;
			margin: 12px 0 !important;
			font-size: 14px !important;
			color: #856404 !important;
		}
		.um-classic-checkout-shipping-notice strong {
			color: #856404 !important;
			font-weight: 600 !important;
		}
		</style>
		<?php
	}

	/**
	 * Render shipping notice in classic checkout.
	 */
	public function render_classic_checkout_shipping_notice(): void {
		if (!is_checkout()) {
			return;
		}
		
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}
		
		if (!$this->check_coupon_covers_full_subtotal()) {
			return;
		}
		
		$title = !empty($this->settings['coupon_notifications_shipping_notice_title']) 
			? $this->settings['coupon_notifications_shipping_notice_title'] 
			: 'Coupon Notice';
		$description = !empty($this->settings['coupon_notifications_shipping_notice_description']) 
			? $this->settings['coupon_notifications_shipping_notice_description'] 
			: 'Coupons and store credits apply to product prices only, and do not cover shipping costs. Shipping is calculated separately from our shipping carriers.';
		
		echo '<div class="um-classic-checkout-shipping-notice">';
		echo '<strong>' . esc_html($title) . '</strong><br>';
		echo nl2br(esc_html($description));
		echo '</div>';
		
		// Add JavaScript to handle dynamic updates
		?>
		<script>
		(function() {
			if (typeof jQuery === 'undefined') {
				return;
			}
			
			function checkCouponCoversFullSubtotal() {
				if (typeof wc_checkout_params === 'undefined') {
					return false;
				}
				
				// Get subtotal from order review
				var subtotalEl = jQuery('.woocommerce-checkout-review-order-table .cart-subtotal .amount, .woocommerce-checkout-review-order-table .cart-subtotal td:last-child');
				var discountEl = jQuery('.woocommerce-checkout-review-order-table .cart-discount .amount, .woocommerce-checkout-review-order-table .cart-discount td:last-child');
				
				if (subtotalEl.length && discountEl.length) {
					var subtotalText = subtotalEl.text().replace(/[^0-9.-]/g, '');
					var discountText = discountEl.text().replace(/[^0-9.-]/g, '');
					var subtotal = parseFloat(subtotalText) || 0;
					var discount = Math.abs(parseFloat(discountText) || 0);
					return discount >= (subtotal - 0.01);
				}
				
				// Fallback: check if notice exists (means PHP calculated it should show)
				return jQuery('.um-classic-checkout-shipping-notice').length > 0;
			}
			
			function toggleClassicNotice() {
				var shouldShow = checkCouponCoversFullSubtotal();
				var notice = jQuery('.um-classic-checkout-shipping-notice');
				
				if (shouldShow) {
					notice.show();
				} else {
					notice.hide();
				}
			}
			
			jQuery(document).ready(function($) {
				// Initial check
				toggleClassicNotice();
				
				// Listen for checkout updates
				jQuery(document.body).on('updated_checkout', function() {
					setTimeout(toggleClassicNotice, 100);
				});
				
				// Listen for coupon application/removal
				jQuery(document.body).on('applied_coupon removed_coupon', function() {
					setTimeout(toggleClassicNotice, 100);
				});
			});
		})();
		</script>
		<?php
	}

	public function handle_apply_coupon(): void {
		check_ajax_referer('umcn_apply_coupon', 'security');
		if (!is_user_logged_in()) {
			wp_send_json_error(__('You must be logged in to apply coupons.', 'user-manager'));
		}
		$code = isset($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';
		if ($code === '') {
			wp_send_json_error(__('Invalid coupon code.', 'user-manager'));
		}
		if (!function_exists('WC') || !WC()->cart) {
			wp_send_json_error(__('Cart not available.', 'user-manager'));
		}
		if (in_array($code, WC()->cart->get_applied_coupons(), true)) {
			wp_send_json_error(__('Coupon is already applied.', 'user-manager'));
		}
		$result = WC()->cart->apply_coupon($code);
		if ($result) {
			wp_send_json_success(__('Coupon applied successfully!', 'user-manager'));
		}
		$notices = function_exists('wc_get_notices') ? wc_get_notices('error') : [];
		$error = !empty($notices) ? strip_tags($notices[0]['notice']) : __('Could not apply coupon.', 'user-manager');
		if (function_exists('wc_clear_notices')) {
			wc_clear_notices();
		}
		wp_send_json_error($error);
	}
}

User_Manager_Coupon_Notices::boot();
