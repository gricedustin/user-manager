<?php
/**
 * WooCommerce My Account Site Admin viewers.
 */

if (!defined('ABSPATH')) {
	exit;
}


require_once __DIR__ . '/my-account/trait-user-manager-my-account-site-admin-renderers.php';
final class User_Manager_My_Account_Site_Admin {
	use User_Manager_My_Account_Site_Admin_Renderers_Trait;
	private const PER_PAGE = 20;
	private const DEBUG_PARAM = 'um_my_account_admin_debug';

	/**
	 * Prevent duplicate style output when multiple endpoints render.
	 *
	 * @var bool
	 */
	private static $styles_rendered = false;

	/**
	 * Runtime notice code for order approval actions handled inline.
	 *
	 * @var string
	 */
	private static $order_action_notice_code = '';

	/**
	 * Track whether init() has run.
	 *
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		if (self::$initialized) {
			return;
		}
		self::$initialized = true;

		add_action('init', [__CLASS__, 'register_rewrite_endpoints'], 20, 0);
		add_filter('query_vars', [__CLASS__, 'filter_public_query_vars'], 20, 1);
		add_filter('woocommerce_get_query_vars', [__CLASS__, 'filter_my_account_query_vars'], 20, 1);
		add_filter('woocommerce_account_menu_items', [__CLASS__, 'filter_my_account_menu_items'], 40, 1);
		add_action('woocommerce_before_my_account', [__CLASS__, 'maybe_render_debug_panel'], 1, 0);
		add_action('woocommerce_new_order', [__CLASS__, 'maybe_default_new_order_to_pending'], 20, 1);
		add_filter('woocommerce_payment_complete_order_status', [__CLASS__, 'filter_payment_complete_order_status'], 20, 3);
		add_action('woocommerce_account_admin_orders_endpoint', [__CLASS__, 'render_admin_orders_endpoint']);
		add_action('woocommerce_account_admin_products_endpoint', [__CLASS__, 'render_admin_products_endpoint']);
		add_action('woocommerce_account_admin_coupons_endpoint', [__CLASS__, 'render_admin_coupons_endpoint']);
		add_action('woocommerce_account_admin_users_endpoint', [__CLASS__, 'render_admin_users_endpoint']);
	}

	/**
	 * Register rewrite endpoints for all My Account Site Admin areas.
	 */
	public static function register_rewrite_endpoints(): void {
		foreach (self::get_area_configs() as $config) {
			add_rewrite_endpoint($config['endpoint'], EP_ROOT | EP_PAGES);
		}
	}

	/**
	 * Register public query vars for custom endpoints.
	 *
	 * @param array $vars Existing query vars.
	 * @return array
	 */
	public static function filter_public_query_vars(array $vars): array {
		foreach (self::get_area_configs() as $config) {
			if (!in_array($config['endpoint'], $vars, true)) {
				$vars[] = $config['endpoint'];
			}
		}

		return $vars;
	}

	/**
	 * Render debug panel on My Account when debug parameter is present.
	 */
	public static function maybe_render_debug_panel(): void {
		if (!self::is_debug_requested()) {
			return;
		}

		if (!function_exists('is_account_page') || !is_account_page()) {
			return;
		}

		if (!is_user_logged_in() || !current_user_can('manage_options')) {
			return;
		}

		$settings     = User_Manager_Core::get_settings();
		$current_user = wp_get_current_user();
		$configs      = self::get_area_configs();

		$areas         = [];
		$endpoint_urls = [];
		foreach ($configs as $key => $config) {
			$enabled_key = $config['enabled_key'];
			$list_key    = $config['usernames_key'];
			$roles_key   = $config['roles_key'] ?? '';
			$raw_list    = (string) ($settings[ $list_key ] ?? '');

			$areas[ $key ] = [
				'endpoint'                => $config['endpoint'],
				'enabled'                 => !empty($settings[ $enabled_key ]),
				'allowed_usernames_raw'   => $raw_list,
				'allowed_usernames_parsed'=> self::parse_username_list($raw_list),
				'allowed_roles_raw'       => $roles_key !== '' ? ($settings[ $roles_key ] ?? []) : [],
				'allowed_roles_parsed'    => $roles_key !== '' ? self::parse_role_list($settings[ $roles_key ] ?? []) : [],
				'current_user_has_access' => self::current_user_can_access_area($config),
				'query_var_value'         => get_query_var($config['endpoint'], ''),
			];

			$endpoint_urls[ $config['endpoint'] ] = self::get_endpoint_url($config['endpoint']);
		}

		$wc_query_vars = [];
		$wc_endpoint   = '';
		if (function_exists('WC') && WC() && isset(WC()->query) && is_object(WC()->query)) {
			$wc_query_vars = WC()->query->get_query_vars();
			$wc_endpoint   = WC()->query->get_current_endpoint();
		}

		$menu_keys = function_exists('wc_get_account_menu_items') ? array_keys(wc_get_account_menu_items()) : [];

		$payload = [
			'debug_param'          => self::DEBUG_PARAM,
			'initialized'          => self::$initialized,
			'is_account_page'      => function_exists('is_account_page') ? is_account_page() : false,
			'request_uri'          => isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '',
			'current_user'         => [
				'id'    => (int) ($current_user->ID ?? 0),
				'login' => (string) ($current_user->user_login ?? ''),
			],
			'wc_current_endpoint'  => $wc_endpoint,
			'wc_query_vars'        => $wc_query_vars,
			'account_menu_keys'    => $menu_keys,
			'endpoint_urls'        => $endpoint_urls,
			'order_approval'       => [
				'allowed_usernames_raw'    => (string) ($settings['my_account_admin_order_approval_usernames'] ?? ''),
				'allowed_usernames_parsed' => self::parse_username_list((string) ($settings['my_account_admin_order_approval_usernames'] ?? '')),
				'allowed_roles_raw'        => $settings['my_account_admin_order_approval_roles'] ?? [],
				'allowed_roles_parsed'     => self::parse_role_list($settings['my_account_admin_order_approval_roles'] ?? []),
				'current_user_can_approve' => self::current_user_can_approve_orders(),
				'default_new_orders_pending_enabled' => !empty($settings['my_account_admin_order_default_pending_enabled']),
			],
			'areas'                => $areas,
		];

		echo '<div class="woocommerce-info um-my-account-admin-debug" style="margin-bottom: 16px;">';
		echo '<strong>' . esc_html__('User Manager Debug: My Account Site Admin', 'user-manager') . '</strong>';
		echo '<p style="margin:8px 0 10px;">' . esc_html__('Debug is enabled by URL parameter. Remove the parameter to hide this panel.', 'user-manager') . '</p>';
		echo '<pre style="white-space: pre-wrap; max-height: 420px; overflow: auto; background:#fff; padding:10px; border:1px solid #dcdcde;">' . esc_html(wp_json_encode($payload, JSON_PRETTY_PRINT)) . '</pre>';
		echo '</div>';
	}

	/**
	 * Add custom account endpoints to WooCommerce query vars.
	 *
	 * @param array $query_vars Existing query vars.
	 * @return array
	 */
	public static function filter_my_account_query_vars(array $query_vars): array {
		foreach (self::get_area_configs() as $config) {
			$query_vars[ $config['endpoint'] ] = $config['endpoint'];
		}

		return $query_vars;
	}

	/**
	 * Add custom menu items to My Account navigation for users with access.
	 *
	 * @param array $items Existing menu items.
	 * @return array
	 */
	public static function filter_my_account_menu_items(array $items): array {
		if (!is_user_logged_in()) {
			return $items;
		}

		$custom_items = [];
		foreach (self::get_area_configs() as $config) {
			if (self::current_user_can_access_area($config)) {
				$custom_items[ $config['endpoint'] ] = $config['menu_label'];
			}
		}

		if (empty($custom_items)) {
			return $items;
		}

		$merged   = [];
		$inserted = false;
		foreach ($items as $key => $label) {
			if ($key === 'customer-logout' && !$inserted) {
				$merged   = array_merge($merged, $custom_items);
				$inserted = true;
			}
			$merged[ $key ] = $label;
		}

		if (!$inserted) {
			$merged = array_merge($merged, $custom_items);
		}

		return $merged;
	}

	/**
	 * Validate access for the current area and display an error if blocked.
	 *
	 * @param string $area_key Area key.
	 * @return bool
	 */
	private static function ensure_area_access(string $area_key): bool {
		$configs = self::get_area_configs();
		if (!isset($configs[ $area_key ])) {
			self::print_error_notice(__('Unknown My Account admin area.', 'user-manager'));
			return false;
		}

		if (!self::current_user_can_access_area($configs[ $area_key ])) {
			self::print_error_notice(__('You do not have access to this My Account admin area.', 'user-manager'));
			return false;
		}

		return true;
	}

	/**
	 * Handle approve/decline order actions from My Account admin orders.
	 */
	private static function maybe_handle_order_approval_action(): void {
		$action = '';
		if (isset($_GET['um_approve_order'])) {
			$action = 'approve';
		} elseif (isset($_GET['um_decline_order'])) {
			$action = 'decline';
		}
		if ($action === '') {
			return;
		}

		self::$order_action_notice_code = '';
		$order_param = $action === 'decline' ? 'um_decline_order' : 'um_approve_order';
		$order_id = isset($_GET[$order_param]) ? absint(wp_unslash($_GET[$order_param])) : 0;
		if ($order_id <= 0) {
			self::$order_action_notice_code = 'invalid_order';
			return;
		}

		$nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
		$nonce_action = $action === 'decline' ? 'um_decline_order_' . $order_id : 'um_approve_order_' . $order_id;
		if ($nonce === '' || !wp_verify_nonce($nonce, $nonce_action)) {
			self::$order_action_notice_code = 'invalid_nonce';
			return;
		}

		if (!self::current_user_can_approve_orders()) {
			self::$order_action_notice_code = 'not_allowed';
			return;
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			self::$order_action_notice_code = 'order_not_found';
			return;
		}

		if ($order->has_status('completed')) {
			self::$order_action_notice_code = 'order_completed_locked';
			return;
		}
		if ($action === 'approve' && $order->has_status('processing')) {
			self::$order_action_notice_code = 'order_already_processing';
			return;
		}
		if ($action === 'decline' && $order->has_status(['cancelled', 'canceled'])) {
			self::$order_action_notice_code = 'order_already_cancelled';
			return;
		}

		$current_user = wp_get_current_user();
		$actor_login = isset($current_user->user_login) ? sanitize_text_field((string) $current_user->user_login) : '';
		if ($actor_login === '') {
			$actor_login = __('Unknown user', 'user-manager');
		}
		$action_time = function_exists('wp_date')
			? wp_date(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp'))
			: date_i18n(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp'));

		if ($action === 'decline') {
			$order->update_status(
				'cancelled',
				__('Order declined from My Account Admin Orders.', 'user-manager'),
				true
			);
			$order->add_order_note(
				sprintf(
					/* translators: 1: user login, 2: localized date/time */
					__('Internal note: Order declined by %1$s on %2$s via My Account Admin Orders.', 'user-manager'),
					$actor_login,
					$action_time
				),
				0,
				true
			);
			self::$order_action_notice_code = 'declined';
		} else {
			$order->update_status(
				'processing',
				__('Order approved from My Account Admin Orders.', 'user-manager'),
				true
			);
			$order->add_order_note(
				sprintf(
					/* translators: 1: user login, 2: localized date/time */
					__('Internal note: Order approved by %1$s on %2$s via My Account Admin Orders.', 'user-manager'),
					$actor_login,
					$action_time
				),
				0,
				true
			);
			self::$order_action_notice_code = 'approved';
		}
	}

	/**
	 * Render order approval notices.
	 */
	private static function render_order_approval_notice(): void {
		$code = self::$order_action_notice_code;
		if ($code === '' && isset($_GET['um_order_notice'])) {
			$code = sanitize_text_field(wp_unslash($_GET['um_order_notice']));
		}

		if ($code === '') {
			return;
		}

		$type = 'notice';
		$message = '';

		switch ($code) {
			case 'approved':
				$type = 'success';
				$message = __('Order approved. Status changed to Processing.', 'user-manager');
				break;
			case 'declined':
				$type = 'success';
				$message = __('Order declined. Status changed to Canceled.', 'user-manager');
				break;
			case 'order_completed_locked':
				$type = 'notice';
				$message = __('Completed orders cannot be approved or declined from this area.', 'user-manager');
				break;
			case 'order_already_processing':
				$type = 'notice';
				$message = __('Order is already Processing.', 'user-manager');
				break;
			case 'order_already_cancelled':
				$type = 'notice';
				$message = __('Order is already Canceled.', 'user-manager');
				break;
			case 'order_not_found':
			case 'invalid_order':
				$type = 'error';
				$message = __('Order not found.', 'user-manager');
				break;
			case 'invalid_nonce':
				$type = 'error';
				$message = __('Security check failed for the order action.', 'user-manager');
				break;
			case 'not_allowed':
				$type = 'error';
				$message = __('You are not allowed to approve or decline orders in this area.', 'user-manager');
				break;
		}

		if ($message === '') {
			return;
		}

		if (function_exists('wc_print_notice')) {
			wc_print_notice($message, $type);
		} else {
			$class = $type === 'error' ? 'woocommerce-error' : 'woocommerce-info';
			if ($type === 'success') {
				$class = 'woocommerce-message';
			}
			echo '<p class="' . esc_attr($class) . '">' . esc_html($message) . '</p>';
		}

		// Prevent re-running approve/decline action on page refresh by removing action params from URL.
		echo '<script>(function(){try{var url=new URL(window.location.href);url.searchParams.delete("um_approve_order");url.searchParams.delete("um_decline_order");url.searchParams.delete("_wpnonce");url.searchParams.delete("um_order_notice");window.history.replaceState({},document.title,url.toString());}catch(e){}})();</script>';
	}

	/**
	 * Build approve order URL with nonce.
	 *
	 * @param int   $order_id Order ID.
	 * @param array $args Base query args.
	 * @return string
	 */
	private static function get_approve_order_url(int $order_id, array $args = []): string {
		$args['um_approve_order'] = $order_id;
		$url = self::get_endpoint_url('admin_orders', $args);
		return wp_nonce_url($url, 'um_approve_order_' . $order_id);
	}

	/**
	 * Build decline order URL with nonce.
	 *
	 * @param int   $order_id Order ID.
	 * @param array $args Base query args.
	 * @return string
	 */
	private static function get_decline_order_url(int $order_id, array $args = []): string {
		$args['um_decline_order'] = $order_id;
		$url = self::get_endpoint_url('admin_orders', $args);
		return wp_nonce_url($url, 'um_decline_order_' . $order_id);
	}

	/**
	 * Resolve approve button label from settings.
	 */
	private static function get_order_approve_button_label(): string {
		$settings = User_Manager_Core::get_settings();
		$label = isset($settings['my_account_admin_order_approve_button_label'])
			? sanitize_text_field((string) $settings['my_account_admin_order_approve_button_label'])
			: '';
		return $label !== '' ? $label : __('Move to Processing', 'user-manager');
	}

	/**
	 * Resolve decline button label from settings.
	 */
	private static function get_order_decline_button_label(): string {
		$settings = User_Manager_Core::get_settings();
		$label = isset($settings['my_account_admin_order_decline_button_label'])
			? sanitize_text_field((string) $settings['my_account_admin_order_decline_button_label'])
			: '';
		return $label !== '' ? $label : __('Move to Canceled', 'user-manager');
	}

	/**
	 * Check whether the current user can approve pending orders.
	 *
	 * @return bool
	 */
	private static function current_user_can_approve_orders(): bool {
		if (!is_user_logged_in()) {
			return false;
		}
		$settings = User_Manager_Core::get_settings();
		if (!self::is_addon_enabled($settings)) {
			return false;
		}
		if (current_user_can('manage_options')) {
			return true;
		}
		$allowed  = self::parse_username_list((string) ($settings['my_account_admin_order_approval_usernames'] ?? ''));
		$role_allow = self::parse_role_list($settings['my_account_admin_order_approval_roles'] ?? []);

		$current = wp_get_current_user();
		$login   = strtolower((string) ($current->user_login ?? ''));
		if ($login !== '' && in_array($login, $allowed, true)) {
			return true;
		}

		if (empty($role_allow)) {
			return false;
		}

		$current_roles = is_array($current->roles) ? array_map('sanitize_key', $current->roles) : [];
		return !empty(array_intersect($role_allow, $current_roles));
	}

	/**
	 * Enforce default pending-payment status for new orders when enabled.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function maybe_default_new_order_to_pending($order_id): void {
		if (!self::is_default_new_orders_pending_enabled()) {
			return;
		}

		$order = wc_get_order(absint($order_id));
		if (!$order) {
			return;
		}

		$current_status = (string) $order->get_status();
		if ($current_status === 'pending' || $current_status === 'checkout-draft') {
			return;
		}

		if (in_array($current_status, ['cancelled', 'refunded', 'trash'], true)) {
			return;
		}

		$order->update_status(
			'pending',
			__('Order defaulted to Pending payment by User Manager setting.', 'user-manager'),
			false
		);
	}

	/**
	 * Keep payment-complete transitions in pending when default pending is enabled.
	 *
	 * @param string   $status Proposed status.
	 * @param int      $order_id Order ID.
	 * @param WC_Order $order Order object.
	 * @return string
	 */
	public static function filter_payment_complete_order_status($status, $order_id, $order): string {
		if (!self::is_default_new_orders_pending_enabled()) {
			return (string) $status;
		}

		if ($status === 'pending') {
			return 'pending';
		}

		return 'pending';
	}

	/**
	 * Setting flag: default new orders to pending payment.
	 *
	 * @return bool
	 */
	private static function is_default_new_orders_pending_enabled(): bool {
		$settings = User_Manager_Core::get_settings();
		return self::is_addon_enabled($settings) && !empty($settings['my_account_admin_order_default_pending_enabled']);
	}

	/**
	 * Check whether meta sections should render for a specific area.
	 *
	 * @param string $area_key Area key.
	 * @return bool
	 */
	private static function should_show_meta_for_area(string $area_key): bool {
		$configs = self::get_area_configs();
		if (!isset($configs[ $area_key ]['show_meta_key'])) {
			return false;
		}

		$settings = User_Manager_Core::get_settings();
		$key      = $configs[ $area_key ]['show_meta_key'];

		return !empty($settings[ $key ]);
	}

	/**
	 * Parse configured Order status filter definitions.
	 *
	 * Format: wc_completed:Complete,wc_failed:Failed
	 *
	 * @return array<string,array{status_key:string,status_slug:string,label:string}>
	 */
	private static function get_configured_order_status_filters(): array {
		$settings = User_Manager_Core::get_settings();
		$raw = isset($settings['my_account_admin_order_status_filters'])
			? (string) $settings['my_account_admin_order_status_filters']
			: '';
		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}

		$registered_statuses = [];
		if (function_exists('wc_get_order_statuses')) {
			$registered = wc_get_order_statuses();
			if (is_array($registered)) {
				$registered_statuses = $registered;
			}
		}

		$parts = preg_split('/[\r\n,]+/', $raw);
		if (!is_array($parts)) {
			return [];
		}

		$filters = [];
		foreach ($parts as $part) {
			$part = trim((string) $part);
			if ($part === '') {
				continue;
			}

			$status_raw = $part;
			$label_raw = '';
			if (strpos($part, ':') !== false) {
				$pair = explode(':', $part, 2);
				$status_raw = isset($pair[0]) ? (string) $pair[0] : '';
				$label_raw  = isset($pair[1]) ? (string) $pair[1] : '';
			}

			$status_key = self::normalize_order_status_filter_key($status_raw);
			if ($status_key === '' || isset($filters[$status_key])) {
				continue;
			}

			$status_slug = preg_replace('/^wc-/', '', $status_key);
			$status_slug = is_string($status_slug) ? sanitize_key($status_slug) : '';
			if ($status_slug === '') {
				continue;
			}

			$label = sanitize_text_field(trim($label_raw));
			if ($label === '' && isset($registered_statuses[$status_key])) {
				$label = wp_strip_all_tags((string) $registered_statuses[$status_key]);
			}
			if ($label === '') {
				$label = ucwords(str_replace(['wc-', '-', '_'], ['', ' ', ' '], $status_key));
			}

			$filters[$status_key] = [
				'status_key'  => $status_key,
				'status_slug' => $status_slug,
				'label'       => $label,
			];
		}

		return $filters;
	}

	/**
	 * Normalize order status filter keys like wc_completed/wc-completed/completed.
	 */
	private static function normalize_order_status_filter_key(string $raw): string {
		$raw = trim(strtolower($raw));
		if ($raw === '') {
			return '';
		}
		$raw = str_replace('_', '-', $raw);
		$raw = sanitize_key($raw);
		if ($raw === '') {
			return '';
		}
		if (strpos($raw, 'wc-') !== 0) {
			$raw = 'wc-' . ltrim($raw, '-');
		}
		return sanitize_key($raw);
	}

	/**
	 * Read selected order status filter from the request.
	 *
	 * @param array<string,array{status_key:string,status_slug:string,label:string}> $filters
	 */
	private static function get_selected_order_status_filter_key(array $filters): string {
		if (empty($_GET['um_order_status'])) {
			return '';
		}

		$raw = sanitize_text_field(wp_unslash($_GET['um_order_status']));
		$key = self::normalize_order_status_filter_key($raw);
		if ($key === '') {
			return '';
		}

		return isset($filters[$key]) ? $key : '';
	}

	/**
	 * Check whether an order matches the selected status filter key.
	 *
	 * @param WC_Order $order Order object.
	 */
	private static function order_matches_status_filter($order, string $status_key): bool {
		if ($status_key === '') {
			return true;
		}
		if (!$order instanceof WC_Order) {
			return false;
		}

		$status_slug = preg_replace('/^wc-/', '', $status_key);
		$status_slug = is_string($status_slug) ? sanitize_key($status_slug) : '';
		if ($status_slug === '') {
			return true;
		}

		return sanitize_key((string) $order->get_status()) === $status_slug;
	}

	/**
	 * Whether order status text should be hidden in the Admin: Orders list.
	 */
	private static function should_hide_order_status(): bool {
		$settings = User_Manager_Core::get_settings();
		return !empty($settings['my_account_admin_order_hide_status']);
	}

	/**
	 * Whether to show WebToffee "Download Invoice" button in Admin: Orders rows.
	 */
	private static function should_add_webtoffee_download_invoice_button(): bool {
		$settings = User_Manager_Core::get_settings();
		return !empty($settings['my_account_admin_order_add_webtoffee_download_invoice_button']);
	}

	/**
	 * Whether to show WebToffee "Print Invoice" button in Admin: Orders rows.
	 */
	private static function should_add_webtoffee_print_invoice_button(): bool {
		$settings = User_Manager_Core::get_settings();
		return !empty($settings['my_account_admin_order_add_webtoffee_print_invoice_button']);
	}

	/**
	 * Resolve WebToffee invoice action URLs from My Account order actions.
	 *
	 * @param WC_Order $order Order object.
	 * @return array{download:string,print:string}
	 */
	private static function get_webtoffee_invoice_action_urls($order): array {
		$resolved = [
			'download' => '',
			'print' => '',
		];
		if (!$order instanceof WC_Order) {
			return $resolved;
		}

		$actions = [];
		if (function_exists('wc_get_account_orders_actions')) {
			$actions = wc_get_account_orders_actions($order);
		} else {
			$actions = apply_filters('woocommerce_my_account_my_orders_actions', [], $order);
		}
		if (!is_array($actions) || empty($actions)) {
			return $resolved;
		}

		foreach ($actions as $action_key => $action) {
			if (!is_array($action)) {
				continue;
			}
			$url = isset($action['url']) ? esc_url_raw((string) $action['url']) : '';
			if ($url === '') {
				continue;
			}
			$name = strtolower(wp_strip_all_tags((string) ($action['name'] ?? '')));
			$class = strtolower((string) ($action['class'] ?? ''));
			$key = strtolower((string) $action_key);
			$url_lc = strtolower($url);

			$is_download = strpos($key, 'download_invoice') !== false
				|| strpos($class, 'wt_pklist_invoice_download') !== false
				|| strpos($name, 'download invoice') !== false
				|| strpos($url_lc, 'type=download_invoice') !== false;
			$is_print = strpos($key, 'print_invoice') !== false
				|| strpos($class, 'wt_pklist_invoice_print') !== false
				|| strpos($name, 'print invoice') !== false
				|| strpos($url_lc, 'type=print_invoice') !== false;

			if ($is_download && $resolved['download'] === '') {
				$resolved['download'] = $url;
			}
			if ($is_print && $resolved['print'] === '') {
				$resolved['print'] = $url;
			}
		}

		return $resolved;
	}

	/**
	 * Render order status filters above the orders table.
	 *
	 * @param string                                                       $endpoint Endpoint slug.
	 * @param array<string,array{status_key:string,status_slug:string,label:string}> $filters  Configured filters.
	 * @param string                                                       $selected_status_key Selected status key.
	 * @param string                                                       $search Current search query.
	 */
	private static function render_order_status_filter_links(string $endpoint, array $filters, string $selected_status_key, string $search = ''): void {
		if (empty($filters)) {
			return;
		}

		$base_args = [];
		if ($search !== '') {
			$base_args['um_search'] = $search;
		}

		$link_html = [];
		$all_url = self::get_endpoint_url($endpoint, $base_args);
		$link_html[] = '<a href="' . esc_url($all_url) . '" class="' . ($selected_status_key === '' ? 'current' : '') . '">' . esc_html__('All Statuses', 'user-manager') . '</a>';

		foreach ($filters as $status_key => $meta) {
			$args = $base_args;
			$args['um_order_status'] = $status_key;
			$url = self::get_endpoint_url($endpoint, $args);
			$current = $selected_status_key === $status_key ? 'current' : '';
			$link_html[] = '<a href="' . esc_url($url) . '" class="' . esc_attr($current) . '">' . esc_html($meta['label']) . '</a>';
		}

		echo '<p class="um-order-status-filter-links" style="margin: 6px 0 12px;">' . implode(' <span aria-hidden="true">|</span> ', $link_html) . '</p>';
	}

	/**
	 * Paginate an array of items.
	 *
	 * @param array $items Items.
	 * @param int   $page Page number.
	 * @param int   $per_page Items per page.
	 * @return array{items: array, total_pages: int}
	 */
	private static function paginate_items(array $items, int $page, int $per_page): array {
		$total = count($items);
		$total_pages = (int) ceil($total / max(1, $per_page));
		if ($total_pages < 1) {
			$total_pages = 1;
		}

		$page = max(1, min($page, $total_pages));
		$offset = ($page - 1) * $per_page;
		$slice = array_slice($items, $offset, $per_page);

		return [
			'items' => $slice,
			'total_pages' => $total_pages,
		];
	}

	/**
	 * Search orders by multiple common fields.
	 *
	 * @param string $search Search query.
	 * @param string $status_key Optional status filter key (wc-* format).
	 * @return array<int,WC_Order>
	 */
	private static function search_orders(string $search, string $status_key = ''): array {
		$search = trim($search);
		$needles = self::get_order_search_needles($search);
		if (empty($needles)) {
			return [];
		}

		$orders = [];
		$queries = [];
		$status_slug = preg_replace('/^wc-/', '', $status_key);
		$status_slug = is_string($status_slug) ? sanitize_key($status_slug) : '';
		$has_status_filter = $status_slug !== '';

		foreach ($needles as $needle) {
			if (!ctype_digit($needle)) {
				continue;
			}
			$single = wc_get_order(absint($needle));
			if ($single instanceof WC_Order && self::order_matches_status_filter($single, $status_key)) {
				$orders[(int) $single->get_id()] = $single;
			}
		}

		$seen_search_fragments = [];
		foreach ($needles as $needle) {
			if (isset($seen_search_fragments[$needle])) {
				continue;
			}
			$seen_search_fragments[$needle] = true;
			$query_args = [
				'limit'    => 300,
				'paginate' => false,
				'orderby'  => 'date',
				'order'    => 'DESC',
				'search'   => '*' . $needle . '*',
			];
			if ($has_status_filter) {
				$query_args['status'] = [$status_slug];
			}
			$queries[] = $query_args;
		}

		$queries[] = [
			'limit'    => 800,
			'paginate' => false,
			'orderby'  => 'date',
			'order'    => 'DESC',
		];
		if ($has_status_filter) {
			$queries[count($queries) - 1]['status'] = [$status_slug];
		}

		if (strpos($search, '@') !== false) {
			$queries[] = [
				'limit'         => 300,
				'paginate'      => false,
				'orderby'       => 'date',
				'order'         => 'DESC',
				'billing_email' => $search,
			];
			if ($has_status_filter) {
				$queries[count($queries) - 1]['status'] = [$status_slug];
			}
		}

		$meta_keys = self::get_order_number_search_meta_keys();
		foreach ($needles as $needle) {
			if ($needle === '') {
				continue;
			}
			$meta_query = ['relation' => 'OR'];
			foreach ($meta_keys as $meta_key) {
				$meta_query[] = [
					'key'     => $meta_key,
					'value'   => $needle,
					'compare' => 'LIKE',
				];
			}
			$query_args = [
				'limit'      => 300,
				'paginate'   => false,
				'orderby'    => 'date',
				'order'      => 'DESC',
				'meta_query' => $meta_query,
			];
			if ($has_status_filter) {
				$query_args['status'] = [$status_slug];
			}
			$queries[] = $query_args;
		}

		foreach ($queries as $query_args) {
			$results = wc_get_orders($query_args);
			if (!is_array($results)) {
				continue;
			}
			foreach ($results as $order) {
				if (!$order instanceof WC_Order) {
					continue;
				}
				if (!self::order_matches_search($order, $needles)) {
					continue;
				}
				if (!self::order_matches_status_filter($order, $status_key)) {
					continue;
				}
				$orders[(int) $order->get_id()] = $order;
			}
		}

		$orders = array_values($orders);
		usort($orders, static function ($a, $b) {
			$a_date = $a instanceof WC_Order && $a->get_date_created() ? $a->get_date_created()->getTimestamp() : 0;
			$b_date = $b instanceof WC_Order && $b->get_date_created() ? $b->get_date_created()->getTimestamp() : 0;
			return $b_date <=> $a_date;
		});

		return $orders;
	}

	/**
	 * Determine if an order matches a search term.
	 *
	 * @param WC_Order          $order Order.
	 * @param array<int,string> $needles Lowercased search query variants.
	 * @return bool
	 */
	private static function order_matches_search($order, array $needles): bool {
		if (!$order instanceof WC_Order) {
			return false;
		}
		if (empty($needles)) {
			return false;
		}

		$billing  = $order->get_address('billing');
		$shipping = $order->get_address('shipping');
		$fields = [
			(string) $order->get_id(),
			(string) $order->get_order_number(),
			'#' . (string) $order->get_order_number(),
			(string) $order->get_status(),
			(string) $order->get_order_key(),
			(string) $order->get_billing_email(),
			(string) $order->get_billing_first_name(),
			(string) $order->get_billing_last_name(),
			(string) $order->get_billing_phone(),
			(string) $order->get_payment_method_title(),
			implode(' ', array_map('strval', (array) $billing)),
			implode(' ', array_map('strval', (array) $shipping)),
		];
		foreach (self::get_order_number_search_meta_keys() as $meta_key) {
			$fields[] = (string) $order->get_meta($meta_key, true);
		}

		$haystack = strtolower(implode(' | ', $fields));
		foreach ($needles as $needle) {
			$needle = trim(strtolower((string) $needle));
			if ($needle === '') {
				continue;
			}
			if (strpos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Generate normalized order-search needle variants.
	 *
	 * @return array<int,string>
	 */
	private static function get_order_search_needles(string $search): array {
		$search = trim((string) $search);
		if ($search === '') {
			return [];
		}

		$variants = [];
		$variants[] = strtolower($search);
		$variants[] = strtolower(ltrim($search, '#'));
		$variants[] = strtolower(preg_replace('/^order[\s#:.\-]*/i', '', $search) ?? '');

		$compact = preg_replace('/\s+/', '', $search);
		if (is_string($compact)) {
			$variants[] = strtolower($compact);
			$variants[] = strtolower(ltrim($compact, '#'));
		}

		$digits = preg_replace('/\D+/', '', $search);
		if (is_string($digits) && $digits !== '') {
			$variants[] = strtolower($digits);
		}

		$variants = array_values(array_unique(array_filter(array_map('trim', $variants))));
		return $variants;
	}

	/**
	 * Order-number meta keys checked for sequential order number plugins.
	 *
	 * @return array<int,string>
	 */
	private static function get_order_number_search_meta_keys(): array {
		return [
			'_order_number',
			'_order_number_formatted',
			'_wc_order_number',
			'_alg_wc_order_number',
			'_alg_wc_custom_order_number',
			'_ywsonp_order_number',
		];
	}

	/**
	 * Search products/variations by title, SKU, variation text, and ID.
	 *
	 * @param string $search Search query.
	 * @return array<int,int>
	 */
	private static function search_product_ids(string $search): array {
		$needle = strtolower(trim($search));
		if ($needle === '') {
			return [];
		}

		$ids = [];
		if (is_numeric($search)) {
			$single_id = absint($search);
			$post_type = get_post_type($single_id);
			if (in_array($post_type, ['product', 'product_variation'], true)) {
				$ids[] = $single_id;
			}
		}

		$queries = [
			[
				'post_type'      => ['product', 'product_variation'],
				'post_status'    => ['publish', 'private'],
				'posts_per_page' => 800,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
				's'              => $search,
			],
			[
				'post_type'      => ['product', 'product_variation'],
				'post_status'    => ['publish', 'private'],
				'posts_per_page' => 800,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
				'meta_query'     => [
					[
						'key'     => '_sku',
						'value'   => $search,
						'compare' => 'LIKE',
					],
				],
			],
			[
				'post_type'      => ['product', 'product_variation'],
				'post_status'    => ['publish', 'private'],
				'posts_per_page' => 800,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			],
		];

		foreach ($queries as $query_args) {
			$query = new WP_Query($query_args);
			if (empty($query->posts)) {
				continue;
			}
			foreach ($query->posts as $post_id) {
				$product = wc_get_product($post_id);
				if (!$product) {
					continue;
				}
				if (!self::product_matches_search($product, $needle)) {
					continue;
				}
				$ids[] = (int) $post_id;
			}
		}

		$ids = array_values(array_unique($ids));
		usort($ids, static function ($a, $b) {
			$a_date = (string) get_post_field('post_date_gmt', $a);
			$b_date = (string) get_post_field('post_date_gmt', $b);
			return strcmp($b_date, $a_date);
		});

		return $ids;
	}

	/**
	 * Determine if a product/variation matches a search term.
	 *
	 * @param WC_Product $product Product.
	 * @param string     $needle Lowercased search query.
	 * @return bool
	 */
	private static function product_matches_search($product, string $needle): bool {
		if (!$product) {
			return false;
		}

		$variation_text = $product->is_type('variation')
			? wc_get_formatted_variation($product, true, false, false)
			: '';
		$parent_name = '';
		if ($product->is_type('variation')) {
			$parent_name = get_the_title((int) $product->get_parent_id());
		}

		$fields = [
			(string) $product->get_id(),
			(string) $product->get_name(),
			(string) $parent_name,
			(string) $variation_text,
			(string) $product->get_sku(),
			(string) $product->get_description(),
			(string) $product->get_short_description(),
			(string) $product->get_status(),
		];

		$haystack = strtolower(implode(' | ', $fields));
		return strpos($haystack, $needle) !== false;
	}

	/**
	 * Search coupons by code and core coupon properties.
	 *
	 * @param string $search Search query.
	 * @return array<int,int>
	 */
	private static function search_coupon_ids(string $search): array {
		$needle = strtolower(trim($search));
		if ($needle === '') {
			return [];
		}

		$ids = [];
		if (is_numeric($search)) {
			$single_id = absint($search);
			if (get_post_type($single_id) === 'shop_coupon') {
				$ids[] = $single_id;
			}
		}

		$queries = [
			[
				'post_type'      => 'shop_coupon',
				'post_status'    => ['publish', 'private', 'draft'],
				'posts_per_page' => 1000,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
				's'              => $search,
			],
			[
				'post_type'      => 'shop_coupon',
				'post_status'    => ['publish', 'private', 'draft'],
				'posts_per_page' => 1000,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			],
		];

		foreach ($queries as $query_args) {
			$query = new WP_Query($query_args);
			if (empty($query->posts)) {
				continue;
			}
			foreach ($query->posts as $coupon_id) {
				$coupon = new WC_Coupon($coupon_id);
				if (!$coupon || !$coupon->get_id()) {
					continue;
				}
				if (!self::coupon_matches_search($coupon, $needle)) {
					continue;
				}
				$ids[] = (int) $coupon_id;
			}
		}

		$ids = array_values(array_unique($ids));
		usort($ids, static function ($a, $b) {
			$a_date = (string) get_post_field('post_date_gmt', $a);
			$b_date = (string) get_post_field('post_date_gmt', $b);
			return strcmp($b_date, $a_date);
		});

		return $ids;
	}

	/**
	 * Determine if a coupon matches a search term.
	 *
	 * @param WC_Coupon $coupon Coupon.
	 * @param string    $needle Lowercased query.
	 * @return bool
	 */
	private static function coupon_matches_search($coupon, string $needle): bool {
		if (!$coupon || !$coupon->get_id()) {
			return false;
		}

		$expires = $coupon->get_date_expires();
		$fields = [
			(string) $coupon->get_id(),
			(string) $coupon->get_code(),
			(string) $coupon->get_amount(),
			(string) $coupon->get_discount_type(),
			$coupon->get_free_shipping() ? 'yes' : 'no',
			(string) $coupon->get_usage_count(),
			(string) $coupon->get_usage_limit(),
			(string) $coupon->get_usage_limit_per_user(),
			$expires ? (string) $expires->date_i18n('Y-m-d H:i:s') : '',
			(string) get_post_field('post_date', $coupon->get_id()),
			(string) get_post_field('post_content', $coupon->get_id()),
			implode(', ', (array) $coupon->get_email_restrictions()),
		];

		$haystack = strtolower(implode(' | ', $fields));
		return strpos($haystack, $needle) !== false;
	}

	/**
	 * Search users by login/email/name/roles/id plus first/last name.
	 *
	 * @param string $search Search query.
	 * @return array<int,WP_User>
	 */
	private static function search_users(string $search): array {
		$needle = strtolower(trim($search));
		if ($needle === '') {
			return [];
		}

		$users = [];
		if (is_numeric($search)) {
			$single = get_user_by('id', absint($search));
			if ($single instanceof WP_User) {
				$users[(int) $single->ID] = $single;
			}
		}

		$queries = [
			new WP_User_Query([
				'number'         => 1200,
				'orderby'        => 'registered',
				'order'          => 'DESC',
				'search'         => '*' . $search . '*',
				'search_columns' => ['user_login', 'user_email', 'display_name'],
			]),
			new WP_User_Query([
				'number'  => 1200,
				'orderby' => 'registered',
				'order'   => 'DESC',
			]),
		];

		foreach ($queries as $query) {
			$results = $query->get_results();
			if (empty($results)) {
				continue;
			}

			foreach ($results as $user) {
				if (!$user instanceof WP_User) {
					continue;
				}
				if (!self::user_matches_search($user, $needle)) {
					continue;
				}
				$users[(int) $user->ID] = $user;
			}
		}

		$users = array_values($users);
		usort($users, static function ($a, $b) {
			$a_reg = strtotime((string) ($a->user_registered ?? '')) ?: 0;
			$b_reg = strtotime((string) ($b->user_registered ?? '')) ?: 0;
			return $b_reg <=> $a_reg;
		});

		return $users;
	}

	/**
	 * Determine if a user matches a search term.
	 *
	 * @param WP_User $user User.
	 * @param string  $needle Lowercased query.
	 * @return bool
	 */
	private static function user_matches_search($user, string $needle): bool {
		if (!$user instanceof WP_User) {
			return false;
		}

		$first_name = (string) get_user_meta($user->ID, 'first_name', true);
		$last_name  = (string) get_user_meta($user->ID, 'last_name', true);
		$fields = [
			(string) $user->ID,
			(string) $user->user_login,
			(string) $user->user_email,
			(string) $user->display_name,
			(string) $first_name,
			(string) $last_name,
			implode(', ', (array) $user->roles),
			(string) $user->user_registered,
		];

		$haystack = strtolower(implode(' | ', $fields));
		return strpos($haystack, $needle) !== false;
	}

	/**
	 * Determine if current user can access a configured area.
	 *
	 * @param array $config Area config.
	 * @return bool
	 */
	private static function current_user_can_access_area(array $config): bool {
		$settings = User_Manager_Core::get_settings();
		if (!self::is_addon_enabled($settings)) {
			return false;
		}
		$enabled_key = $config['enabled_key'];
		$list_key    = $config['usernames_key'];
		$roles_key   = $config['roles_key'] ?? '';

		if (empty($settings[ $enabled_key ])) {
			return false;
		}

		if (!is_user_logged_in()) {
			return false;
		}

		if (current_user_can('manage_options')) {
			return true;
		}

		$allowed_usernames = self::parse_username_list((string) ($settings[ $list_key ] ?? ''));
		$allowed_roles     = $roles_key !== '' ? self::parse_role_list($settings[ $roles_key ] ?? []) : [];
		if (empty($allowed_usernames) && empty($allowed_roles)) {
			return false;
		}

		$current = wp_get_current_user();
		$login   = strtolower((string) ($current->user_login ?? ''));
		if ($login !== '' && in_array($login, $allowed_usernames, true)) {
			return true;
		}

		if (empty($allowed_roles)) {
			return false;
		}

		$current_roles = is_array($current->roles) ? array_map('sanitize_key', $current->roles) : [];
		return !empty(array_intersect($allowed_roles, $current_roles));
	}

	/**
	 * Determine whether the My Account Site Admin add-on is enabled.
	 * Falls back to legacy behavior when no explicit toggle is stored.
	 *
	 * @param array $settings Plugin settings.
	 * @return bool
	 */
	private static function is_addon_enabled(array $settings): bool {
		if (array_key_exists('my_account_site_admin_enabled', $settings)) {
			return !empty($settings['my_account_site_admin_enabled']);
		}

		return !empty($settings['my_account_admin_order_viewer_enabled'])
			|| !empty($settings['my_account_admin_product_viewer_enabled'])
			|| !empty($settings['my_account_admin_coupon_viewer_enabled'])
			|| !empty($settings['my_account_admin_user_viewer_enabled']);
	}

	/**
	 * Parse comma/space/newline separated usernames.
	 *
	 * @param string $raw Raw list.
	 * @return array<int,string>
	 */
	private static function parse_username_list(string $raw): array {
		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}

		$parts = preg_split('/[\s,]+/', $raw);
		if (!is_array($parts)) {
			return [];
		}

		$usernames = [];
		foreach ($parts as $part) {
			$part = trim((string) $part);
			if ($part === '') {
				continue;
			}
			$sanitized = sanitize_user($part, false);
			if ($sanitized === '') {
				continue;
			}
			$usernames[] = strtolower($sanitized);
		}

		return array_values(array_unique($usernames));
	}

	/**
	 * Parse selected roles from array or comma/space/newline string.
	 *
	 * @param mixed $raw Raw roles value.
	 * @return array<int,string>
	 */
	private static function parse_role_list($raw): array {
		$parts = [];
		if (is_array($raw)) {
			$parts = $raw;
		} elseif (is_string($raw) && trim($raw) !== '') {
			$split = preg_split('/[\s,]+/', $raw);
			$parts = is_array($split) ? $split : [];
		}

		$roles = [];
		foreach ($parts as $part) {
			$role = sanitize_key((string) $part);
			if ($role === '') {
				continue;
			}
			$roles[] = $role;
		}
		$roles = array_values(array_unique($roles));

		if (function_exists('wp_roles')) {
			$wp_roles = wp_roles();
			if ($wp_roles && isset($wp_roles->roles) && is_array($wp_roles->roles)) {
				$roles = array_values(array_intersect($roles, array_keys($wp_roles->roles)));
			}
		}

		return $roles;
	}

	/**
	 * Area configuration map.
	 *
	 * @return array<string,array<string,string>>
	 */
	private static function get_area_configs(): array {
		return [
			'orders' => [
				'endpoint'      => 'admin_orders',
				'menu_label'    => __('Admin: Orders', 'user-manager'),
				'enabled_key'   => 'my_account_admin_order_viewer_enabled',
				'usernames_key' => 'my_account_admin_order_viewer_usernames',
				'roles_key'     => 'my_account_admin_order_viewer_roles',
				'show_meta_key' => 'my_account_admin_order_viewer_show_meta',
			],
			'products' => [
				'endpoint'      => 'admin_products',
				'menu_label'    => __('Admin: Products', 'user-manager'),
				'enabled_key'   => 'my_account_admin_product_viewer_enabled',
				'usernames_key' => 'my_account_admin_product_viewer_usernames',
				'roles_key'     => 'my_account_admin_product_viewer_roles',
				'show_meta_key' => 'my_account_admin_product_viewer_show_meta',
			],
			'coupons' => [
				'endpoint'      => 'admin_coupons',
				'menu_label'    => __('Admin: Coupons', 'user-manager'),
				'enabled_key'   => 'my_account_admin_coupon_viewer_enabled',
				'usernames_key' => 'my_account_admin_coupon_viewer_usernames',
				'roles_key'     => 'my_account_admin_coupon_viewer_roles',
				'show_meta_key' => 'my_account_admin_coupon_viewer_show_meta',
			],
			'users' => [
				'endpoint'      => 'admin_users',
				'menu_label'    => __('Admin: Users', 'user-manager'),
				'enabled_key'   => 'my_account_admin_user_viewer_enabled',
				'usernames_key' => 'my_account_admin_user_viewer_usernames',
				'roles_key'     => 'my_account_admin_user_viewer_roles',
				'show_meta_key' => 'my_account_admin_user_viewer_show_meta',
			],
		];
	}

	/**
	 * Render compact search form.
	 *
	 * @param string $endpoint Endpoint key.
	 * @param string $placeholder Placeholder text.
	 */
	private static function render_search_form(string $endpoint, string $placeholder): void {
		$search = self::get_search_query();
		$url    = self::get_endpoint_url($endpoint);
		$selected_status_key = '';
		if ($endpoint === 'admin_orders') {
			$status_filters = self::get_configured_order_status_filters();
			$selected_status_key = self::get_selected_order_status_filter_key($status_filters);
		}
		echo '<form class="um-my-account-admin-search-form" method="get" action="' . esc_url($url) . '">';
		if ($selected_status_key !== '') {
			echo '<input type="hidden" name="um_order_status" value="' . esc_attr($selected_status_key) . '" />';
		}
		echo '<input type="search" name="um_search" value="' . esc_attr($search) . '" placeholder="' . esc_attr($placeholder) . '" />';
		echo '<button type="submit" class="button">' . esc_html__('Search', 'user-manager') . '</button>';
		if ($search !== '') {
			$clear_args = [];
			if ($selected_status_key !== '') {
				$clear_args['um_order_status'] = $selected_status_key;
			}
			$clear_url = self::get_endpoint_url($endpoint, $clear_args);
			echo ' <a class="button" href="' . esc_url($clear_url) . '">' . esc_html__('Clear', 'user-manager') . '</a>';
		}
		echo '</form>';
	}

	/**
	 * Render pagination controls.
	 *
	 * @param string $endpoint Endpoint key.
	 * @param int    $current_page Current page.
	 * @param int    $total_pages Total pages.
	 * @param string $search Current search query.
	 */
	private static function render_pagination(string $endpoint, int $current_page, int $total_pages, string $search = ''): void {
		if ($total_pages <= 1) {
			return;
		}

		$base_args = [];
		if ($search !== '') {
			$base_args['um_search'] = $search;
		}
		if ($endpoint === 'admin_orders') {
			$status_filters = self::get_configured_order_status_filters();
			$selected_status_key = self::get_selected_order_status_filter_key($status_filters);
			if ($selected_status_key !== '') {
				$base_args['um_order_status'] = $selected_status_key;
			}
		}

		echo '<div class="um-my-account-admin-pagination">';
		if ($current_page > 1) {
			$prev_args = $base_args;
			$prev_args['um_page'] = $current_page - 1;
			echo '<a class="button" href="' . esc_url(self::get_endpoint_url($endpoint, $prev_args)) . '">&larr; ' . esc_html__('Previous', 'user-manager') . '</a>';
		} else {
			echo '<span class="button disabled">&larr; ' . esc_html__('Previous', 'user-manager') . '</span>';
		}

		echo '<span class="um-my-account-admin-pagination-status">';
		echo esc_html(
			sprintf(
				/* translators: 1: current page, 2: total pages */
				__('Page %1$d of %2$d', 'user-manager'),
				$current_page,
				$total_pages
			)
		);
		echo '</span>';

		if ($current_page < $total_pages) {
			$next_args = $base_args;
			$next_args['um_page'] = $current_page + 1;
			echo '<a class="button" href="' . esc_url(self::get_endpoint_url($endpoint, $next_args)) . '">' . esc_html__('Next', 'user-manager') . ' &rarr;</a>';
		} else {
			echo '<span class="button disabled">' . esc_html__('Next', 'user-manager') . ' &rarr;</span>';
		}
		echo '</div>';
	}

	/**
	 * Render a shared key/value row.
	 *
	 * @param string $label Label.
	 * @param string $value Value.
	 * @param bool   $allow_html Allow HTML in value.
	 */
	private static function render_key_value_row(string $label, string $value, bool $allow_html = false): void {
		echo '<tr class="woocommerce-table__line-item order_item">';
		echo '<td class="woocommerce-table__product-name product-name middle">' . esc_html($label) . '</td>';
		echo '<td class="woocommerce-table__product-total product-total middle">';
		echo $allow_html ? wp_kses_post($value) : esc_html($value);
		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Parse configured additional order meta fields.
	 *
	 * @param string $raw Raw setting value.
	 * @return array<int,array{key:string,label:string,prefix_before_value:string}>
	 */
	private static function parse_order_additional_meta_field_definitions(string $raw): array {
		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}

		$parts = preg_split('/[\r\n,]+/', $raw);
		if (!is_array($parts)) {
			return [];
		}

		$definitions = [];
		$seen_keys = [];
		foreach ($parts as $part) {
			$part = trim((string) $part);
			if ($part === '') {
				continue;
			}

			$meta_key_raw = $part;
			$label_raw = $part;
			$prefix_raw = '';
			if (strpos($part, ':') !== false) {
				$pair = explode(':', $part, 3);
				$meta_key_raw = isset($pair[0]) ? (string) $pair[0] : '';
				$label_raw    = isset($pair[1]) ? (string) $pair[1] : '';
				$prefix_raw   = isset($pair[2]) ? (string) $pair[2] : '';
			}

			$meta_key = sanitize_key(trim($meta_key_raw));
			if ($meta_key === '') {
				continue;
			}
			if (isset($seen_keys[$meta_key])) {
				continue;
			}

			$label = sanitize_text_field(trim($label_raw));
			if ($label === '') {
				$label = $meta_key;
			}

			$definitions[] = [
				'key'                 => $meta_key,
				'label'               => $label,
				'prefix_before_value' => sanitize_text_field(trim($prefix_raw)),
			];
			$seen_keys[$meta_key] = true;
		}

		return $definitions;
	}

	/**
	 * Get additional order meta field definitions from settings.
	 *
	 * @return array<int,array{key:string,label:string,prefix_before_value:string}>
	 */
	private static function get_order_additional_meta_field_definitions(): array {
		$settings = User_Manager_Core::get_settings();
		$raw = isset($settings['my_account_admin_order_additional_meta_fields'])
			? (string) $settings['my_account_admin_order_additional_meta_fields']
			: '';

		return self::parse_order_additional_meta_field_definitions($raw);
	}

	/**
	 * Render configured additional order meta fields.
	 *
	 * @param WC_Order $order Order object.
	 */
	private static function render_order_additional_meta_fields($order): void {
		if (!$order instanceof WC_Order) {
			return;
		}

		$definitions = self::get_order_additional_meta_field_definitions();
		if (empty($definitions)) {
			return;
		}

		$rows_html = '';
		foreach ($definitions as $definition) {
			$meta_values = get_post_meta((int) $order->get_id(), $definition['key']);
			if (empty($meta_values) || !is_array($meta_values)) {
				continue;
			}

			$prefix_before_value = isset($definition['prefix_before_value']) ? (string) $definition['prefix_before_value'] : '';
			$value_html = self::format_meta_values_for_display_with_links($meta_values, $prefix_before_value);
			if ($value_html === '') {
				continue;
			}

			ob_start();
			self::render_key_value_row($definition['label'], $value_html, true);
			$rows_html .= (string) ob_get_clean();
		}

		if ($rows_html === '') {
			return;
		}

		echo '<section class="woocommerce-order-details">';
		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">';
		echo '<thead><tr><th class="middle">' . esc_html__('Additional Order Fields', 'user-manager') . '</th><th class="middle"></th></tr></thead><tbody>';
		echo wp_kses_post($rows_html);
		echo '</tbody></table>';
		echo '</section>';
	}

	/**
	 * Format metadata values and link http(s) values.
	 *
	 * @param array  $values Raw meta values.
	 * @param string $prefix_before_value Optional prefix to prepend before URL detection.
	 * @return string
	 */
	private static function format_meta_values_for_display_with_links(array $values, string $prefix_before_value = ''): string {
		$rendered = [];
		$prefix_before_value = trim($prefix_before_value);
		foreach ($values as $value) {
			if (is_array($value) || is_object($value)) {
				$json = wp_json_encode($value);
				$display_value = is_string($json) ? $json : '';
			} elseif (is_bool($value)) {
				$display_value = $value ? '1' : '0';
			} elseif ($value === null) {
				$display_value = '';
			} else {
				$display_value = (string) $value;
			}

			$value_for_output = $display_value;
			if ($prefix_before_value !== '') {
				$normalized_value = self::normalize_meta_scalar_for_prefixed_link($display_value);
				$value_for_output = $prefix_before_value . ltrim($normalized_value, '/');
			}

			$trimmed = trim($value_for_output);
			if ($trimmed !== '' && preg_match('#^https?://#i', $trimmed)) {
				$url = esc_url($trimmed);
				if ($url !== '') {
					$rendered[] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Open File', 'user-manager') . '</a>';
					continue;
				}
			}

			$rendered[] = esc_html($value_for_output);
		}

		return implode(' | ', $rendered);
	}

	/**
	 * Normalize scalar-like strings before applying URL prefixes.
	 * Converts one-item JSON arrays such as ["abc"] to abc.
	 */
	private static function normalize_meta_scalar_for_prefixed_link(string $value): string {
		$value = trim($value);
		if ($value === '') {
			return '';
		}

		$decoded = json_decode($value, true);
		if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) === 1) {
			$first = reset($decoded);
			if (is_scalar($first) || $first === null) {
				return trim((string) $first);
			}
		}

		return $value;
	}

	/**
	 * Render a post meta data table.
	 *
	 * @param int $post_id Post ID.
	 */
	private static function render_meta_table_from_post(int $post_id): void {
		$meta = get_post_meta($post_id);
		if (empty($meta) || !is_array($meta)) {
			return;
		}

		ksort($meta);
		echo '<section class="woocommerce-order-details">';
		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">';
		echo '<thead><tr><th class="middle">' . esc_html__('Meta Data', 'user-manager') . '</th><th class="middle"></th></tr></thead><tbody>';
		foreach ($meta as $meta_key => $values) {
			if (!is_array($values)) {
				continue;
			}
			echo '<tr class="woocommerce-table__line-item order_item">';
			echo '<td class="woocommerce-table__product-name product-name middle">' . esc_html((string) $meta_key) . '</td>';
			echo '<td class="woocommerce-table__product-total product-total middle">' . esc_html(self::format_meta_values_for_display($values)) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
		echo '</section>';
	}

	/**
	 * Render a user meta data table.
	 *
	 * @param int $user_id User ID.
	 */
	private static function render_meta_table_from_user(int $user_id): void {
		$meta = get_user_meta($user_id);
		if (empty($meta) || !is_array($meta)) {
			return;
		}

		ksort($meta);
		echo '<section class="woocommerce-order-details">';
		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">';
		echo '<thead><tr><th class="middle">' . esc_html__('Meta Data', 'user-manager') . '</th><th class="middle"></th></tr></thead><tbody>';
		foreach ($meta as $meta_key => $values) {
			if (!is_array($values)) {
				continue;
			}
			echo '<tr class="woocommerce-table__line-item order_item">';
			echo '<td class="woocommerce-table__product-name product-name middle">' . esc_html((string) $meta_key) . '</td>';
			echo '<td class="woocommerce-table__product-total product-total middle">' . esc_html(self::format_meta_values_for_display($values)) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
		echo '</section>';
	}

	/**
	 * Format an array of metadata values for display.
	 *
	 * @param array $values Raw values.
	 * @return string
	 */
	private static function format_meta_values_for_display(array $values): string {
		$rendered = [];
		foreach ($values as $value) {
			if (is_array($value) || is_object($value)) {
				$json = wp_json_encode($value);
				$rendered[] = is_string($json) ? $json : '';
				continue;
			}

			if (is_bool($value)) {
				$rendered[] = $value ? '1' : '0';
				continue;
			}

			if ($value === null) {
				$rendered[] = '';
				continue;
			}

			$rendered[] = (string) $value;
		}

		return implode(' | ', $rendered);
	}

	/**
	 * Product stock display.
	 *
	 * @param WC_Product $product Product object.
	 * @return string
	 */
	private static function get_product_stock_display($product): string {
		if (!$product) {
			return '';
		}

		if ($product->managing_stock()) {
			$stock_qty = $product->get_stock_quantity();
			return $stock_qty === null ? '0' : (string) $stock_qty;
		}

		$status = (string) $product->get_stock_status();
		if ($status === 'instock') {
			return __('In stock', 'user-manager');
		}
		if ($status === 'outofstock') {
			return __('Out of stock', 'user-manager');
		}
		if ($status === 'onbackorder') {
			return __('On backorder', 'user-manager');
		}

		return $status;
	}

	/**
	 * Read current page from query args.
	 *
	 * @return int
	 */
	private static function get_current_page(): int {
		$page = isset($_GET['um_page']) ? absint(wp_unslash($_GET['um_page'])) : 1;
		return max(1, $page);
	}

	/**
	 * Read current search query.
	 *
	 * @return string
	 */
	private static function get_search_query(): string {
		return isset($_GET['um_search']) ? sanitize_text_field(wp_unslash($_GET['um_search'])) : '';
	}

	/**
	 * Build endpoint URL.
	 *
	 * @param string $endpoint Endpoint key.
	 * @param array  $query_args Extra query args.
	 * @return string
	 */
	private static function get_endpoint_url(string $endpoint, array $query_args = []): string {
		$base = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');
		$url  = function_exists('wc_get_endpoint_url') ? wc_get_endpoint_url($endpoint, '', $base) : $base;
		if (!empty($query_args)) {
			$url = add_query_arg($query_args, $url);
		}
		return $url;
	}

	/**
	 * Build a list (non-detail) URL preserving list context.
	 *
	 * @param string $endpoint Endpoint key.
	 * @return string
	 */
	private static function get_list_url(string $endpoint): string {
		$args = self::get_list_context_query_args();
		return self::get_endpoint_url($endpoint, $args);
	}

	/**
	 * Capture list context query args used for back links and view links.
	 *
	 * @return array
	 */
	private static function get_list_context_query_args(): array {
		$args   = [];
		$page   = self::get_current_page();
		$search = self::get_search_query();
		if ($page > 1) {
			$args['um_page'] = $page;
		}
		if ($search !== '') {
			$args['um_search'] = $search;
		}
		if (function_exists('get_query_var') && get_query_var('admin_orders', null) !== null) {
			$status_filters = self::get_configured_order_status_filters();
			$selected_status_key = self::get_selected_order_status_filter_key($status_filters);
			if ($selected_status_key !== '') {
				$args['um_order_status'] = $selected_status_key;
			}
		}
		return $args;
	}

	/**
	 * Render shared endpoint styles once.
	 */
	private static function render_shared_styles(): void {
		if (self::$styles_rendered) {
			return;
		}
		self::$styles_rendered = true;
		?>
		<style>
			.woocommerce-MyAccount-content .um-my-account-admin-search-form {
				margin: 0 0 16px;
				display: flex;
				gap: 8px;
				flex-wrap: wrap;
				align-items: center;
			}
			.woocommerce-MyAccount-content .um-my-account-admin-search-form input[type="search"] {
				min-width: 260px;
				max-width: 420px;
				width: 100%;
				padding: 6px 8px;
			}
			table.express_checkout_order_approvals {
				width: 100%;
				border-collapse: collapse;
			}
			table.express_checkout_order_approvals th,
			table.express_checkout_order_approvals td {
				padding: 8px;
				border-top: 1px solid #dcdcde;
				vertical-align: top;
			}
			table.express_checkout_order_approvals th {
				font-weight: 600;
				text-align: left;
			}
			.center {
				text-align: center;
			}
			.full_width {
				width: 100% !important;
				text-align: center !important;
			}
			.breathing_room {
				margin: 5px 0 !important;
			}
			.um-my-account-admin-status {
				display: inline-block;
				margin-top: 2px;
				font-weight: 600;
			}
			.um-my-account-admin-pagination {
				margin-top: 16px;
				display: flex;
				align-items: center;
				gap: 10px;
				flex-wrap: wrap;
			}
			.um-my-account-admin-pagination .disabled {
				opacity: .45;
				pointer-events: none;
			}
			.um-my-account-admin-pagination-status {
				font-weight: 600;
			}
		</style>
		<?php
	}

	/**
	 * Print an error notice in My Account.
	 *
	 * @param string $message Message.
	 */
	private static function print_error_notice(string $message): void {
		if (function_exists('wc_print_notice')) {
			wc_print_notice($message, 'error');
			return;
		}

		echo '<p class="woocommerce-error">' . esc_html($message) . '</p>';
	}

	/**
	 * Determine if the debug URL parameter is enabled.
	 *
	 * @return bool
	 */
	private static function is_debug_requested(): bool {
		if (!isset($_GET[ self::DEBUG_PARAM ])) {
			return false;
		}

		$raw = sanitize_text_field(wp_unslash($_GET[ self::DEBUG_PARAM ]));
		$raw = strtolower(trim($raw));

		return $raw !== '' && $raw !== '0' && $raw !== 'false' && $raw !== 'no';
	}
}

