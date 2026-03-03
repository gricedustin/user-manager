<?php
/**
 * WooCommerce My Account Site Admin viewers.
 */

if (!defined('ABSPATH')) {
	exit;
}

final class User_Manager_My_Account_Site_Admin {
	private const PER_PAGE = 20;

	/**
	 * Prevent duplicate style output when multiple endpoints render.
	 *
	 * @var bool
	 */
	private static $styles_rendered = false;

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		add_filter('woocommerce_get_query_vars', [__CLASS__, 'filter_my_account_query_vars'], 20, 1);
		add_filter('woocommerce_account_menu_items', [__CLASS__, 'filter_my_account_menu_items'], 40, 1);
		add_action('woocommerce_account_admin_orders_endpoint', [__CLASS__, 'render_admin_orders_endpoint']);
		add_action('woocommerce_account_admin_products_endpoint', [__CLASS__, 'render_admin_products_endpoint']);
		add_action('woocommerce_account_admin_coupons_endpoint', [__CLASS__, 'render_admin_coupons_endpoint']);
		add_action('woocommerce_account_admin_users_endpoint', [__CLASS__, 'render_admin_users_endpoint']);
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

		$query_args = [
			'limit'    => self::PER_PAGE,
			'page'     => $current_page,
			'paginate' => true,
			'orderby'  => 'date',
			'order'    => 'DESC',
		];

		if ($search !== '') {
			if (is_numeric($search)) {
				$query_args['include'] = [absint($search)];
			} else {
				$query_args['search'] = '*' . $search . '*';
			}
		}

		$result = wc_get_orders($query_args);
		$orders = [];
		$pages  = 1;

		if (is_object($result) && isset($result->orders)) {
			$orders = is_array($result->orders) ? $result->orders : [];
			$pages  = isset($result->max_num_pages) ? max(1, (int) $result->max_num_pages) : 1;
		} elseif (is_array($result)) {
			$orders = $result;
			$pages  = 1;
		}

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
		echo '<h3 class="swh_order_history_title">' . esc_html__('Admin: Orders', 'user-manager') . '</h3>';
		echo '<p class="swh_order_history_desc"></p>';
		echo '<a class="button" href="' . esc_url($back_url) . '">' . esc_html__('Back', 'user-manager') . '</a><br><br>';

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

		$args = [
			'post_type'      => ['product', 'product_variation'],
			'post_status'    => ['publish', 'private'],
			'posts_per_page' => self::PER_PAGE,
			'paged'          => $current_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		];
		if ($search !== '') {
			$args['s'] = $search;
		}

		$query = new WP_Query($args);

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

		if (empty($query->posts)) {
			echo '<tr><td colspan="7" class="express_checkout_order_approvals_empty">' . esc_html__('No products found.', 'user-manager') . '</td></tr>';
		} else {
			foreach ($query->posts as $post_id) {
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
		self::render_pagination($endpoint, $current_page, max(1, (int) $query->max_num_pages), $search);
		wp_reset_postdata();
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
		self::render_meta_table_from_post($product_id);
	}

	/**
	 * Render coupons list with pagination.
	 */
	private static function render_coupons_list(): void {
		$endpoint     = 'admin_coupons';
		$current_page = self::get_current_page();
		$search       = self::get_search_query();

		$args = [
			'post_type'      => 'shop_coupon',
			'post_status'    => ['publish', 'private', 'draft'],
			'posts_per_page' => self::PER_PAGE,
			'paged'          => $current_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		];
		if ($search !== '') {
			$args['s'] = $search;
		}

		$query = new WP_Query($args);

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

		if (empty($query->posts)) {
			echo '<tr><td colspan="9" class="express_checkout_order_approvals_empty">' . esc_html__('No coupons found.', 'user-manager') . '</td></tr>';
		} else {
			foreach ($query->posts as $coupon_id) {
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
		self::render_pagination($endpoint, $current_page, max(1, (int) $query->max_num_pages), $search);
		wp_reset_postdata();
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

		self::render_meta_table_from_post($coupon_id);
	}

	/**
	 * Render users list with pagination.
	 */
	private static function render_users_list(): void {
		$endpoint     = 'admin_users';
		$current_page = self::get_current_page();
		$search       = self::get_search_query();
		$offset       = ($current_page - 1) * self::PER_PAGE;

		$args = [
			'number'      => self::PER_PAGE,
			'offset'      => $offset,
			'orderby'     => 'registered',
			'order'       => 'DESC',
			'count_total' => true,
		];

		if ($search !== '') {
			$args['search']         = '*' . $search . '*';
			$args['search_columns'] = ['user_login', 'user_email', 'display_name'];
		}

		$query = new WP_User_Query($args);
		$users = $query->get_results();
		$total = (int) $query->get_total();
		$pages = (int) ceil($total / self::PER_PAGE);
		if ($pages < 1) {
			$pages = 1;
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

		self::render_meta_table_from_user($user->ID);
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
			$sanitized = sanitize_user($part, true);
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
			],
			'products' => [
				'endpoint'      => 'admin_products',
				'menu_label'    => __('Admin: Products', 'user-manager'),
				'enabled_key'   => 'my_account_admin_product_viewer_enabled',
				'usernames_key' => 'my_account_admin_product_viewer_usernames',
			],
			'coupons' => [
				'endpoint'      => 'admin_coupons',
				'menu_label'    => __('Admin: Coupons', 'user-manager'),
				'enabled_key'   => 'my_account_admin_coupon_viewer_enabled',
				'usernames_key' => 'my_account_admin_coupon_viewer_usernames',
			],
			'users' => [
				'endpoint'      => 'admin_users',
				'menu_label'    => __('Admin: Users', 'user-manager'),
				'enabled_key'   => 'my_account_admin_user_viewer_enabled',
				'usernames_key' => 'my_account_admin_user_viewer_usernames',
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
}

