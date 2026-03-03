<?php
/**
 * WooCommerce My Account Site Admin viewers.
 */

if (!defined('ABSPATH')) {
	exit;
}

final class User_Manager_My_Account_Site_Admin {
	private const PER_PAGE = 20;
	private const DEBUG_PARAM = 'um_my_account_admin_debug';

	/**
	 * Prevent duplicate style output when multiple endpoints render.
	 *
	 * @var bool
	 */
	private static $styles_rendered = false;

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
			$raw_list    = (string) ($settings[ $list_key ] ?? '');

			$areas[ $key ] = [
				'endpoint'                => $config['endpoint'],
				'enabled'                 => !empty($settings[ $enabled_key ]),
				'allowed_usernames_raw'   => $raw_list,
				'allowed_usernames_parsed'=> self::parse_username_list($raw_list),
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
				'current_user_can_approve' => self::current_user_can_approve_orders(),
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
	 * Endpoint renderer: Admin Orders.
	 */
	public static function render_admin_orders_endpoint(): void {
		self::render_shared_styles();
		if (!self::ensure_area_access('orders')) {
			return;
		}
		self::maybe_handle_order_approval_action();
		self::render_order_approval_notice();

		$order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
		if ($order_id > 0) {
			self::render_order_detail($order_id);
			return;
		}

		self::render_orders_list();
	}

	/**
	 * Endpoint renderer: Admin Products.
	 */
	public static function render_admin_products_endpoint(): void {
		self::render_shared_styles();
		if (!self::ensure_area_access('products')) {
			return;
		}

		$product_id = isset($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;
		if ($product_id > 0) {
			self::render_product_detail($product_id);
			return;
		}

		self::render_products_list();
	}

	/**
	 * Endpoint renderer: Admin Coupons.
	 */
	public static function render_admin_coupons_endpoint(): void {
		self::render_shared_styles();
		if (!self::ensure_area_access('coupons')) {
			return;
		}

		$coupon_id = isset($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;
		if ($coupon_id > 0) {
			self::render_coupon_detail($coupon_id);
			return;
		}

		self::render_coupons_list();
	}

	/**
	 * Endpoint renderer: Admin Users.
	 */
	public static function render_admin_users_endpoint(): void {
		self::render_shared_styles();
		if (!self::ensure_area_access('users')) {
			return;
		}

		$user_id = isset($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;
		if ($user_id > 0) {
			self::render_user_detail($user_id);
			return;
		}

		self::render_users_list();
	}

	/**
	 * Render orders list with pagination.
	 */
	private static function render_orders_list(): void {
		$endpoint     = 'admin_orders';
		$current_page = self::get_current_page();
		$search       = self::get_search_query();
		if ($search !== '') {
			$all_orders = self::search_orders($search);
			$paged      = self::paginate_items($all_orders, $current_page, self::PER_PAGE);
			$orders     = $paged['items'];
			$pages      = $paged['total_pages'];
		} else {
			$result = wc_get_orders([
				'limit'    => self::PER_PAGE,
				'page'     => $current_page,
				'paginate' => true,
				'orderby'  => 'date',
				'order'    => 'DESC',
			]);
			$orders = [];
			$pages  = 1;

			if (is_object($result) && isset($result->orders)) {
				$orders = is_array($result->orders) ? $result->orders : [];
				$pages  = isset($result->max_num_pages) ? max(1, (int) $result->max_num_pages) : 1;
			} elseif (is_array($result)) {
				$orders = $result;
				$pages  = 1;
			}
		}

		$can_approve = self::current_user_can_approve_orders();

		echo '<h3 class="swh_order_history_title">' . esc_html__('Admin: Orders', 'user-manager') . '</h3>';
		echo '<p class="swh_order_history_desc"></p>';
		self::render_search_form($endpoint, __('Search orders...', 'user-manager'));

		echo '<table class="express_checkout_order_approvals woocommerce_my_account_admin_tools woocommerce_my_account_admin_tools_orders">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__('Order', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Date', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Address', 'user-manager') . '</th>';
		echo '<th class="center"></th>';
		echo '</tr></thead><tbody>';

		if (empty($orders)) {
			echo '<tr><td colspan="4" class="express_checkout_order_approvals_empty">' . esc_html__('No orders found.', 'user-manager') . '</td></tr>';
		} else {
			foreach ($orders as $order) {
				if (!$order instanceof WC_Order) {
					continue;
				}

				$order_id      = (int) $order->get_id();
				$order_number  = $order->get_order_number();
				$date_created  = $order->get_date_created();
				$date_display  = $date_created ? $date_created->date_i18n('D M d, Y g:ia') : '';
				$status_label  = wc_get_order_status_name($order->get_status());
				$billing_email = $order->get_billing_email();
				$address_html  = $order->get_formatted_shipping_address();
				if ($address_html === '') {
					$address_html = $order->get_formatted_billing_address();
				}
				if ($address_html === '') {
					$address_html = '&ndash;';
				}

				$view_args  = self::get_list_context_query_args();
				$view_args['order_id'] = $order_id;
				$view_url   = self::get_endpoint_url($endpoint, $view_args);
				$print_args = $view_args;
				$print_args['print'] = '1';
				$print_url  = self::get_endpoint_url($endpoint, $print_args);

				echo '<tr class="express_checkout_order_approvals_row">';
				echo '<td><strong>' . esc_html($order_number) . '</strong></td>';
				echo '<td>';
				echo esc_html($date_display);
				if ($billing_email !== '') {
					echo '<br />' . esc_html($billing_email);
				}
				echo '<br /><span class="um-my-account-admin-status">' . esc_html($status_label) . '</span>';
				echo '</td>';
				echo '<td>' . wp_kses_post($address_html) . '</td>';
				echo '<td class="center">';
				echo '<a class="button breathing_room full_width" href="' . esc_url($view_url) . '">' . esc_html__('View Order', 'user-manager') . '</a> ';
				echo '<a class="button breathing_room full_width" href="' . esc_url($print_url) . '">' . esc_html__('Print Order', 'user-manager') . '</a>';
				if ($can_approve && $order->has_status('pending')) {
					$approve_url = self::get_approve_order_url($order_id, self::get_list_context_query_args());
					echo ' <a class="button breathing_room full_width" href="' . esc_url($approve_url) . '">' . esc_html__('Approve', 'user-manager') . '</a>';
				}
				echo '</td>';
				echo '</tr>';
			}
		}

		echo '</tbody></table>';
		self::render_pagination($endpoint, $current_page, $pages, $search);
	}

	/**
	 * Render a single order details view.
	 *
	 * @param int $order_id Order ID.
	 */
	private static function render_order_detail(int $order_id): void {
		$order = wc_get_order($order_id);
		if (!$order) {
			self::print_error_notice(__('Order not found.', 'user-manager'));
			return;
		}

		$back_url = self::get_list_url('admin_orders');
		$can_approve = self::current_user_can_approve_orders() && $order->has_status('pending');
		echo '<h3 class="swh_order_history_title">' . esc_html__('Admin: Orders', 'user-manager') . '</h3>';
		echo '<p class="swh_order_history_desc"></p>';
		echo '<a class="button" href="' . esc_url($back_url) . '">' . esc_html__('Back', 'user-manager') . '</a>';
		if ($can_approve) {
			$approve_args = self::get_list_context_query_args();
			$approve_args['order_id'] = (int) $order->get_id();
			if (isset($_GET['print']) && $_GET['print'] === '1') {
				$approve_args['print'] = '1';
			}
			$approve_url = self::get_approve_order_url((int) $order->get_id(), $approve_args);
			echo ' <a class="button" href="' . esc_url($approve_url) . '">' . esc_html__('Approve', 'user-manager') . '</a>';
		}
		echo '<br><br>';

		$date_created = $order->get_date_created();
		$date_display = $date_created ? $date_created->date_i18n('D M d, Y g:ia') : '';
		$status_label = wc_get_order_status_name($order->get_status());

		echo '<p>';
		echo esc_html__('Order #', 'user-manager');
		echo '<mark class="order-number">' . esc_html($order->get_order_number()) . '</mark> ';
		echo esc_html__('was placed on', 'user-manager') . ' ';
		echo '<mark class="order-date">' . esc_html($date_display) . '</mark> ';
		echo esc_html__('and is currently', 'user-manager') . ' ';
		echo '<mark class="order-status">' . esc_html($status_label) . '</mark>.';
		echo '</p>';

		echo '<section class="woocommerce-order-details">';
		echo '<h2 class="woocommerce-order-details__title">' . esc_html__('Order details', 'user-manager') . '</h2>';
		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details woocommerce_my_account_admin_tools woocommerce_my_account_admin_tools_orders">';
		echo '<thead><tr><th class="product-name">' . esc_html__('Product', 'user-manager') . '</th><th class="product-total">' . esc_html__('Total', 'user-manager') . '</th></tr></thead>';
		echo '<tbody>';

		foreach ($order->get_items() as $item) {
			$name = $item->get_name();
			$qty  = (int) $item->get_quantity();
			$line_total = $order->get_formatted_line_subtotal($item);
			echo '<tr class="woocommerce-table__line-item order_item">';
			echo '<td class="woocommerce-table__product-name product-name">' . esc_html($name) . ' <strong class="product-quantity">&times;&nbsp;' . esc_html((string) $qty) . '</strong></td>';
			echo '<td class="woocommerce-table__product-total product-total">' . wp_kses_post($line_total) . '</td>';
			echo '</tr>';
		}

		echo '</tbody><tfoot>';
		foreach ($order->get_order_item_totals() as $total) {
			$label = isset($total['label']) ? wp_strip_all_tags((string) $total['label']) : '';
			$value = isset($total['value']) ? (string) $total['value'] : '';
			echo '<tr>';
			echo '<th scope="row">' . esc_html(rtrim($label, ':')) . ':</th>';
			echo '<td>' . wp_kses_post($value) . '</td>';
			echo '</tr>';
		}
		echo '</tfoot></table>';
		echo '</section>';

		$billing  = $order->get_formatted_billing_address();
		$shipping = $order->get_formatted_shipping_address();
		$billing_email = $order->get_billing_email();
		$billing_phone = $order->get_billing_phone();

		echo '<section class="woocommerce-customer-details">';
		echo '<h2 class="woocommerce-column__title">' . esc_html__('Billing address', 'user-manager') . '</h2>';
		echo '<address>';
		echo $billing !== '' ? wp_kses_post($billing) : '&ndash;';
		if ($billing_email !== '') {
			echo '<br>' . esc_html($billing_email);
		}
		if ($billing_phone !== '') {
			echo '<br>' . esc_html($billing_phone);
		}
		echo '</address>';
		echo '</section>';

		echo '<section class="woocommerce-customer-details">';
		echo '<h2 class="woocommerce-column__title">' . esc_html__('Shipping address', 'user-manager') . '</h2>';
		echo '<address>';
		echo $shipping !== '' ? wp_kses_post($shipping) : '&ndash;';
		echo '</address>';
		echo '</section>';

		if (self::should_show_meta_for_area('orders')) {
			self::render_meta_table_from_post((int) $order->get_id());
		}

		if (isset($_GET['print']) && $_GET['print'] === '1') {
			echo '<script>window.print();</script>';
		}
	}

	/**
	 * Render products list with pagination.
	 */
	private static function render_products_list(): void {
		$endpoint     = 'admin_products';
		$current_page = self::get_current_page();
		$search       = self::get_search_query();
		$total_pages = 1;

		if ($search !== '') {
			$matching_ids = self::search_product_ids($search);
			$paged        = self::paginate_items($matching_ids, $current_page, self::PER_PAGE);
			$product_ids  = $paged['items'];
			$total_pages  = $paged['total_pages'];
		} else {
			$query = new WP_Query([
				'post_type'      => ['product', 'product_variation'],
				'post_status'    => ['publish', 'private'],
				'posts_per_page' => self::PER_PAGE,
				'paged'          => $current_page,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			]);
			$product_ids = $query->posts;
			$total_pages = max(1, (int) $query->max_num_pages);
		}

		echo '<h3 class="swh_products_title">' . esc_html__('Admin: Products', 'user-manager') . '</h3>';
		echo '<p class="swh_products_desc"></p>';
		self::render_search_form($endpoint, __('Search products...', 'user-manager'));

		echo '<table class="express_checkout_order_approvals woocommerce_my_account_admin_tools woocommerce_my_account_admin_tools_products">';
		echo '<thead><tr>';
		echo '<th></th>';
		echo '<th>' . esc_html__('Product', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Variation', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('SKU', 'user-manager') . '</th>';
		echo '<th class="center">' . esc_html__('Inventory', 'user-manager') . '</th>';
		echo '<th class="center">' . esc_html__('Price', 'user-manager') . '</th>';
		echo '<th></th>';
		echo '</tr></thead><tbody>';

		if (empty($product_ids)) {
			echo '<tr><td colspan="7" class="express_checkout_order_approvals_empty">' . esc_html__('No products found.', 'user-manager') . '</td></tr>';
		} else {
			foreach ($product_ids as $post_id) {
				$product = wc_get_product($post_id);
				if (!$product) {
					continue;
				}

				$is_variation = $product->is_type('variation');
				$product_name = $is_variation ? get_the_title($product->get_parent_id()) : $product->get_name();
				$variation    = '';
				if ($is_variation) {
					$variation = wc_get_formatted_variation($product, true, false, false);
				}

				$image_html = $product->get_image('woocommerce_thumbnail');
				if ($image_html === '' && function_exists('wc_placeholder_img')) {
					$image_html = wc_placeholder_img('woocommerce_thumbnail');
				}

				$sku          = $product->get_sku();
				$stock        = self::get_product_stock_display($product);
				$price_html   = $product->get_price_html();
				if ($price_html === '') {
					$price_html = wc_price((float) $product->get_price());
				}

				$view_args = self::get_list_context_query_args();
				$view_args['id'] = (int) $product->get_id();
				$view_url = self::get_endpoint_url($endpoint, $view_args);

				echo '<tr class="express_checkout_order_approvals_row">';
				echo '<td class="middle" style="max-width:25px !important;">' . wp_kses_post($image_html) . '</td>';
				echo '<td class="middle">' . esc_html($product_name) . '</td>';
				echo '<td class="middle">' . wp_kses_post($variation) . '</td>';
				echo '<td class="middle">' . esc_html($sku) . '</td>';
				echo '<td class="middle center">' . esc_html($stock) . '</td>';
				echo '<td class="middle center">' . wp_kses_post($price_html) . '</td>';
				echo '<td class="middle center">';
				if ($is_variation) {
					echo '<a class="button" href="#" style="pointer-events:none;cursor:default;opacity:.25 !important;">' . esc_html__('Variation Only', 'user-manager') . '</a>';
				} else {
					echo '<a class="button" href="' . esc_url($view_url) . '">' . esc_html__('View Product', 'user-manager') . '</a>';
				}
				echo '</td>';
				echo '</tr>';
			}
		}

		echo '</tbody></table>';
		self::render_pagination($endpoint, $current_page, $total_pages, $search);
	}

	/**
	 * Render single product detail view.
	 *
	 * @param int $product_id Product ID.
	 */
	private static function render_product_detail(int $product_id): void {
		$product = wc_get_product($product_id);
		if (!$product) {
			self::print_error_notice(__('Product not found.', 'user-manager'));
			return;
		}

		$back_url    = self::get_list_url('admin_products');
		$post_status = get_post_status($product_id);

		echo '<h3 class="swh_products_title">' . esc_html__('Admin: Products', 'user-manager') . '</h3>';
		echo '<p class="swh_products_desc"></p>';
		echo '<a class="button" href="' . esc_url($back_url) . '">' . esc_html__('Back', 'user-manager') . '</a><br><br>';

		echo '<p>';
		echo esc_html__('Product #', 'user-manager');
		echo '<mark class="order-number">' . esc_html((string) $product->get_id()) . '</mark> ';
		echo esc_html__('is currently set to', 'user-manager') . ' ';
		echo '<mark class="order-status">' . esc_html((string) $post_status) . '</mark>.';
		echo '</p>';

		echo '<section class="woocommerce-order-details">';
		echo '<h2 class="woocommerce-order-details__title">' . esc_html__('Product details', 'user-manager') . '</h2>';
		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details woocommerce_my_account_admin_tools woocommerce_my_account_admin_tools_products">';
		echo '<thead><tr><th>' . esc_html__('Title', 'user-manager') . '</th><th>' . esc_html($product->get_name()) . '</th></tr></thead>';
		echo '<tbody>';
		self::render_key_value_row(__('SKU', 'user-manager'), $product->get_sku());
		self::render_key_value_row(__('Stock', 'user-manager'), self::get_product_stock_display($product));
		self::render_key_value_row(__('Price', 'user-manager'), $product->get_price_html(), true);
		self::render_key_value_row(__('Description', 'user-manager'), $product->get_description(), true);
		self::render_key_value_row(__('Short Description', 'user-manager'), $product->get_short_description(), true);
		echo '</tbody></table>';
		echo '</section>';

		if ($product->is_type('variable')) {
			$children = $product->get_children();
			foreach ($children as $child_id) {
				$variation = wc_get_product($child_id);
				if (!$variation) {
					continue;
				}
				echo '<section class="woocommerce-order-details">';
				echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details woocommerce_my_account_admin_tools woocommerce_my_account_admin_tools_products">';
				echo '<thead><tr><th>' . esc_html($variation->get_name()) . '</th><th></th></tr></thead>';
				echo '<tbody>';
				self::render_key_value_row(__('SKU', 'user-manager'), $variation->get_sku());
				self::render_key_value_row(__('Stock', 'user-manager'), self::get_product_stock_display($variation));
				self::render_key_value_row(__('Price', 'user-manager'), $variation->get_price_html(), true);
				echo '</tbody></table>';
				echo '</section>';
			}
		}

		echo wp_kses_post($product->get_image('woocommerce_thumbnail'));
		if (self::should_show_meta_for_area('products')) {
			self::render_meta_table_from_post($product_id);
		}
	}

	/**
	 * Render coupons list with pagination.
	 */
	private static function render_coupons_list(): void {
		$endpoint     = 'admin_coupons';
		$current_page = self::get_current_page();
		$search       = self::get_search_query();
		$total_pages = 1;
		if ($search !== '') {
			$matching_ids = self::search_coupon_ids($search);
			$paged        = self::paginate_items($matching_ids, $current_page, self::PER_PAGE);
			$coupon_ids   = $paged['items'];
			$total_pages  = $paged['total_pages'];
		} else {
			$query = new WP_Query([
				'post_type'      => 'shop_coupon',
				'post_status'    => ['publish', 'private', 'draft'],
				'posts_per_page' => self::PER_PAGE,
				'paged'          => $current_page,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			]);
			$coupon_ids  = $query->posts;
			$total_pages = max(1, (int) $query->max_num_pages);
		}

		echo '<h3 class="swh_coupons_title">' . esc_html__('Admin: Coupons', 'user-manager') . '</h3>';
		echo '<p class="swh_coupons_desc"></p>';
		self::render_search_form($endpoint, __('Search coupons...', 'user-manager'));

		echo '<table class="express_checkout_order_approvals woocommerce_my_account_admin_tools woocommerce_my_account_admin_tools_coupons">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__('Coupon', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Amount', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Type', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Free Shipping', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Used', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Limit', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Expiration', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Created', 'user-manager') . '</th>';
		echo '<th></th>';
		echo '</tr></thead><tbody>';

		if (empty($coupon_ids)) {
			echo '<tr><td colspan="9" class="express_checkout_order_approvals_empty">' . esc_html__('No coupons found.', 'user-manager') . '</td></tr>';
		} else {
			foreach ($coupon_ids as $coupon_id) {
				$coupon = new WC_Coupon($coupon_id);
				if (!$coupon || !$coupon->get_id()) {
					continue;
				}

				$expires = $coupon->get_date_expires();
				$created = get_post_time('Y-m-d', false, $coupon_id);
				$view_args = self::get_list_context_query_args();
				$view_args['id'] = (int) $coupon->get_id();
				$view_url = self::get_endpoint_url($endpoint, $view_args);

				echo '<tr class="express_checkout_order_approvals_row">';
				echo '<td class="middle">' . esc_html($coupon->get_code()) . '</td>';
				echo '<td class="middle">' . esc_html((string) $coupon->get_amount()) . '</td>';
				echo '<td class="middle">' . esc_html((string) $coupon->get_discount_type()) . '</td>';
				echo '<td class="middle">' . esc_html($coupon->get_free_shipping() ? __('yes', 'user-manager') : __('no', 'user-manager')) . '</td>';
				echo '<td class="middle">' . esc_html((string) $coupon->get_usage_count()) . '</td>';
				echo '<td class="middle">' . esc_html((string) $coupon->get_usage_limit()) . '</td>';
				echo '<td class="middle">' . esc_html($expires ? $expires->date_i18n('Y-m-d') : '') . '</td>';
				echo '<td class="middle">' . esc_html((string) $created) . '</td>';
				echo '<td class="middle"><a class="button" href="' . esc_url($view_url) . '">' . esc_html__('View Coupon', 'user-manager') . '</a></td>';
				echo '</tr>';
			}
		}

		echo '</tbody></table>';
		self::render_pagination($endpoint, $current_page, $total_pages, $search);
	}

	/**
	 * Render single coupon detail view.
	 *
	 * @param int $coupon_id Coupon ID.
	 */
	private static function render_coupon_detail(int $coupon_id): void {
		$coupon = new WC_Coupon($coupon_id);
		if (!$coupon || !$coupon->get_id()) {
			self::print_error_notice(__('Coupon not found.', 'user-manager'));
			return;
		}

		$back_url    = self::get_list_url('admin_coupons');
		$post_status = get_post_status($coupon_id);
		$created     = get_post_time('Y-m-d H:i:s', false, $coupon_id);
		$expires     = $coupon->get_date_expires();

		echo '<h3 class="swh_coupons_title">' . esc_html__('Admin: Coupons', 'user-manager') . '</h3>';
		echo '<p class="swh_coupons_desc"></p>';
		echo '<a class="button" href="' . esc_url($back_url) . '">' . esc_html__('Back', 'user-manager') . '</a><br><br>';

		echo '<p>';
		echo esc_html__('Coupon', 'user-manager') . ' <mark class="order-number">' . esc_html($coupon->get_code()) . '</mark> ';
		echo esc_html__('was created on', 'user-manager') . ' <mark class="order-date">' . esc_html((string) $created) . '</mark> ';
		echo esc_html__('and is currently set to', 'user-manager') . ' <mark class="order-status">' . esc_html((string) $post_status) . '</mark>.';
		echo '</p>';

		echo '<section class="woocommerce-order-details">';
		echo '<h2 class="woocommerce-order-details__title">' . esc_html__('Coupon details', 'user-manager') . '</h2>';
		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">';
		echo '<thead><tr><th>' . esc_html__('Coupon', 'user-manager') . '</th><th></th></tr></thead>';
		echo '<tbody>';
		self::render_key_value_row(__('Amount', 'user-manager'), (string) $coupon->get_amount());
		self::render_key_value_row(__('Type', 'user-manager'), (string) $coupon->get_discount_type());
		self::render_key_value_row(__('Free Shipping', 'user-manager'), $coupon->get_free_shipping() ? __('yes', 'user-manager') : __('no', 'user-manager'));
		self::render_key_value_row(__('Minimum Spend', 'user-manager'), (string) $coupon->get_minimum_amount());
		self::render_key_value_row(__('Maximum Spend', 'user-manager'), (string) $coupon->get_maximum_amount());
		self::render_key_value_row(__('Individual Use', 'user-manager'), $coupon->get_individual_use() ? __('yes', 'user-manager') : __('no', 'user-manager'));
		self::render_key_value_row(__('Used', 'user-manager'), (string) $coupon->get_usage_count());
		self::render_key_value_row(__('Limit', 'user-manager'), (string) $coupon->get_usage_limit());
		self::render_key_value_row(__('Limit Per User', 'user-manager'), (string) $coupon->get_usage_limit_per_user());
		self::render_key_value_row(__('Expiration', 'user-manager'), $expires ? $expires->date_i18n('Y-m-d H:i:s') : '');
		self::render_key_value_row(__('Created', 'user-manager'), (string) $created);
		self::render_key_value_row(__('Description', 'user-manager'), get_post_field('post_content', $coupon_id), true);
		echo '</tbody></table>';
		echo '</section>';

		if (self::should_show_meta_for_area('coupons')) {
			self::render_meta_table_from_post($coupon_id);
		}
	}

	/**
	 * Render users list with pagination.
	 */
	private static function render_users_list(): void {
		$endpoint     = 'admin_users';
		$current_page = self::get_current_page();
		$search       = self::get_search_query();
		if ($search !== '') {
			$matching_users = self::search_users($search);
			$paged          = self::paginate_items($matching_users, $current_page, self::PER_PAGE);
			$users          = $paged['items'];
			$pages          = $paged['total_pages'];
		} else {
			$offset = ($current_page - 1) * self::PER_PAGE;
			$query  = new WP_User_Query([
				'number'      => self::PER_PAGE,
				'offset'      => $offset,
				'orderby'     => 'registered',
				'order'       => 'DESC',
				'count_total' => true,
			]);
			$users = $query->get_results();
			$total = (int) $query->get_total();
			$pages = (int) ceil($total / self::PER_PAGE);
			if ($pages < 1) {
				$pages = 1;
			}
		}

		echo '<h3 class="swh_users_title">' . esc_html__('Admin: Users', 'user-manager') . '</h3>';
		echo '<p class="swh_users_desc"></p>';
		self::render_search_form($endpoint, __('Search users...', 'user-manager'));

		echo '<table class="express_checkout_order_approvals woocommerce_my_account_admin_tools woocommerce_my_account_admin_tools_users">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__('User ID', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Login', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Email', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Display Name', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Roles', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Registered', 'user-manager') . '</th>';
		echo '<th></th>';
		echo '</tr></thead><tbody>';

		if (empty($users)) {
			echo '<tr><td colspan="7" class="express_checkout_order_approvals_empty">' . esc_html__('No users found.', 'user-manager') . '</td></tr>';
		} else {
			foreach ($users as $user) {
				if (!$user instanceof WP_User) {
					continue;
				}

				$view_args = self::get_list_context_query_args();
				$view_args['id'] = (int) $user->ID;
				$view_url = self::get_endpoint_url($endpoint, $view_args);

				echo '<tr class="express_checkout_order_approvals_row">';
				echo '<td>' . esc_html((string) $user->ID) . '</td>';
				echo '<td>' . esc_html($user->user_login) . '</td>';
				echo '<td>' . esc_html($user->user_email) . '</td>';
				echo '<td>' . esc_html($user->display_name) . '</td>';
				echo '<td>' . esc_html(implode(', ', (array) $user->roles)) . '</td>';
				echo '<td>' . esc_html((string) $user->user_registered) . '</td>';
				echo '<td><a class="button" href="' . esc_url($view_url) . '">' . esc_html__('View User', 'user-manager') . '</a></td>';
				echo '</tr>';
			}
		}

		echo '</tbody></table>';
		self::render_pagination($endpoint, $current_page, $pages, $search);
	}

	/**
	 * Render single user detail view.
	 *
	 * @param int $user_id User ID.
	 */
	private static function render_user_detail(int $user_id): void {
		$user = get_user_by('id', $user_id);
		if (!$user instanceof WP_User) {
			self::print_error_notice(__('User not found.', 'user-manager'));
			return;
		}

		$back_url = self::get_list_url('admin_users');

		echo '<h3 class="swh_users_title">' . esc_html__('Admin: Users', 'user-manager') . '</h3>';
		echo '<p class="swh_users_desc"></p>';
		echo '<a class="button" href="' . esc_url($back_url) . '">' . esc_html__('Back', 'user-manager') . '</a><br><br>';

		echo '<p>';
		echo esc_html__('User', 'user-manager') . ' <mark class="order-number">' . esc_html($user->user_email) . '</mark> ';
		echo esc_html__('was created on', 'user-manager') . ' <mark class="order-date">' . esc_html((string) $user->user_registered) . '</mark>.';
		echo '</p>';

		echo '<section class="woocommerce-order-details">';
		echo '<h2 class="woocommerce-order-details__title">' . esc_html__('User details', 'user-manager') . '</h2>';
		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">';
		echo '<thead><tr><th>' . esc_html__('User', 'user-manager') . '</th><th></th></tr></thead>';
		echo '<tbody>';
		self::render_key_value_row(__('User ID', 'user-manager'), (string) $user->ID);
		self::render_key_value_row(__('User Login', 'user-manager'), $user->user_login);
		self::render_key_value_row(__('User Email', 'user-manager'), $user->user_email);
		self::render_key_value_row(__('Display Name', 'user-manager'), $user->display_name);
		self::render_key_value_row(__('Roles', 'user-manager'), implode(', ', (array) $user->roles));
		self::render_key_value_row(__('Date Registered', 'user-manager'), (string) $user->user_registered);
		echo '</tbody></table>';
		echo '</section>';

		if (self::should_show_meta_for_area('users')) {
			self::render_meta_table_from_user($user->ID);
		}
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
	 * Handle approve-order actions from My Account admin orders.
	 */
	private static function maybe_handle_order_approval_action(): void {
		if (!isset($_GET['um_approve_order'])) {
			return;
		}

		$order_id = absint(wp_unslash($_GET['um_approve_order']));
		$args     = self::get_list_context_query_args();

		if (isset($_GET['order_id']) && absint(wp_unslash($_GET['order_id'])) > 0) {
			$args['order_id'] = absint(wp_unslash($_GET['order_id']));
		}
		if (isset($_GET['print']) && $_GET['print'] === '1') {
			$args['print'] = '1';
		}

		if ($order_id <= 0) {
			self::redirect_order_notice('invalid_order', $args);
		}

		$nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
		if ($nonce === '' || !wp_verify_nonce($nonce, 'um_approve_order_' . $order_id)) {
			self::redirect_order_notice('invalid_nonce', $args);
		}

		if (!self::current_user_can_approve_orders()) {
			self::redirect_order_notice('not_allowed', $args);
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			self::redirect_order_notice('order_not_found', $args);
		}

		if (!$order->has_status('pending')) {
			self::redirect_order_notice('order_not_pending', $args);
		}

		$order->update_status(
			'processing',
			__('Order approved from My Account Admin Orders.', 'user-manager'),
			true
		);

		self::redirect_order_notice('approved', $args);
	}

	/**
	 * Redirect to the orders endpoint with a notice code.
	 *
	 * @param string $notice_code Notice code.
	 * @param array  $args Redirect args.
	 */
	private static function redirect_order_notice(string $notice_code, array $args = []): void {
		$args['um_order_notice'] = $notice_code;
		$url = self::get_endpoint_url('admin_orders', $args);
		wp_safe_redirect($url);
		exit;
	}

	/**
	 * Render order approval notices.
	 */
	private static function render_order_approval_notice(): void {
		if (!isset($_GET['um_order_notice'])) {
			return;
		}

		$code = sanitize_text_field(wp_unslash($_GET['um_order_notice']));
		$type = 'notice';
		$message = '';

		switch ($code) {
			case 'approved':
				$type = 'success';
				$message = __('Order approved. Status changed from Pending payment to Processing.', 'user-manager');
				break;
			case 'order_not_pending':
				$type = 'notice';
				$message = __('Order is not in Pending payment status, so it was not changed.', 'user-manager');
				break;
			case 'order_not_found':
			case 'invalid_order':
				$type = 'error';
				$message = __('Order not found.', 'user-manager');
				break;
			case 'invalid_nonce':
				$type = 'error';
				$message = __('Security check failed for order approval.', 'user-manager');
				break;
			case 'not_allowed':
				$type = 'error';
				$message = __('You are not allowed to approve orders in this area.', 'user-manager');
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
	 * Check whether the current user can approve pending orders.
	 *
	 * @return bool
	 */
	private static function current_user_can_approve_orders(): bool {
		if (!is_user_logged_in()) {
			return false;
		}
		if (current_user_can('manage_options')) {
			return true;
		}

		$settings = User_Manager_Core::get_settings();
		$allowed  = self::parse_username_list((string) ($settings['my_account_admin_order_approval_usernames'] ?? ''));
		if (empty($allowed)) {
			return false;
		}

		$current = wp_get_current_user();
		$login   = strtolower((string) ($current->user_login ?? ''));
		if ($login === '') {
			return false;
		}

		return in_array($login, $allowed, true);
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
	 * @return array<int,WC_Order>
	 */
	private static function search_orders(string $search): array {
		$needle = strtolower(trim($search));
		if ($needle === '') {
			return [];
		}

		$orders = [];
		$queries = [];

		if (is_numeric($search)) {
			$single = wc_get_order(absint($search));
			if ($single instanceof WC_Order) {
				$orders[(int) $single->get_id()] = $single;
			}
		}

		$queries[] = [
			'limit'    => 300,
			'paginate' => false,
			'orderby'  => 'date',
			'order'    => 'DESC',
			'search'   => '*' . $search . '*',
		];
		$queries[] = [
			'limit'    => 400,
			'paginate' => false,
			'orderby'  => 'date',
			'order'    => 'DESC',
		];
		if (strpos($search, '@') !== false) {
			$queries[] = [
				'limit'         => 300,
				'paginate'      => false,
				'orderby'       => 'date',
				'order'         => 'DESC',
				'billing_email' => $search,
			];
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
				if (!self::order_matches_search($order, $needle)) {
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
	 * @param WC_Order $order Order.
	 * @param string   $needle Lowercased search query.
	 * @return bool
	 */
	private static function order_matches_search($order, string $needle): bool {
		if (!$order instanceof WC_Order) {
			return false;
		}

		$billing  = $order->get_address('billing');
		$shipping = $order->get_address('shipping');
		$fields = [
			(string) $order->get_id(),
			(string) $order->get_order_number(),
			(string) $order->get_status(),
			(string) $order->get_billing_email(),
			(string) $order->get_billing_first_name(),
			(string) $order->get_billing_last_name(),
			(string) $order->get_billing_phone(),
			(string) $order->get_payment_method_title(),
			implode(' ', array_map('strval', (array) $billing)),
			implode(' ', array_map('strval', (array) $shipping)),
		];

		$haystack = strtolower(implode(' | ', $fields));
		return strpos($haystack, $needle) !== false;
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
		$enabled_key = $config['enabled_key'];
		$list_key    = $config['usernames_key'];

		if (empty($settings[ $enabled_key ])) {
			return false;
		}

		if (!is_user_logged_in()) {
			return false;
		}

		if (current_user_can('manage_options')) {
			return true;
		}

		$allowed = self::parse_username_list((string) ($settings[ $list_key ] ?? ''));
		if (empty($allowed)) {
			return false;
		}

		$current = wp_get_current_user();
		$login   = strtolower((string) ($current->user_login ?? ''));
		if ($login === '') {
			return false;
		}

		return in_array($login, $allowed, true);
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
				'show_meta_key' => 'my_account_admin_order_viewer_show_meta',
			],
			'products' => [
				'endpoint'      => 'admin_products',
				'menu_label'    => __('Admin: Products', 'user-manager'),
				'enabled_key'   => 'my_account_admin_product_viewer_enabled',
				'usernames_key' => 'my_account_admin_product_viewer_usernames',
				'show_meta_key' => 'my_account_admin_product_viewer_show_meta',
			],
			'coupons' => [
				'endpoint'      => 'admin_coupons',
				'menu_label'    => __('Admin: Coupons', 'user-manager'),
				'enabled_key'   => 'my_account_admin_coupon_viewer_enabled',
				'usernames_key' => 'my_account_admin_coupon_viewer_usernames',
				'show_meta_key' => 'my_account_admin_coupon_viewer_show_meta',
			],
			'users' => [
				'endpoint'      => 'admin_users',
				'menu_label'    => __('Admin: Users', 'user-manager'),
				'enabled_key'   => 'my_account_admin_user_viewer_enabled',
				'usernames_key' => 'my_account_admin_user_viewer_usernames',
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
		echo '<form class="um-my-account-admin-search-form" method="get" action="' . esc_url($url) . '">';
		echo '<input type="search" name="um_search" value="' . esc_attr($search) . '" placeholder="' . esc_attr($placeholder) . '" />';
		echo '<button type="submit" class="button">' . esc_html__('Search', 'user-manager') . '</button>';
		if ($search !== '') {
			echo ' <a class="button" href="' . esc_url($url) . '">' . esc_html__('Clear', 'user-manager') . '</a>';
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

