<?php
/**
 * Extracted methods from class-user-manager-my-account-site-admin.php.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_My_Account_Site_Admin_Renderers_Trait {
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
	
		

		private static function render_orders_list(): void {
			$endpoint     = 'admin_orders';
			$current_page = self::get_current_page();
			$search       = self::get_search_query();
			$status_filters = self::get_configured_order_status_filters();
			$selected_status_key = self::get_selected_order_status_filter_key($status_filters);
			if ($search !== '') {
				$all_orders = self::search_orders($search, $selected_status_key);
				$paged      = self::paginate_items($all_orders, $current_page, self::PER_PAGE);
				$orders     = $paged['items'];
				$pages      = $paged['total_pages'];
			} else {
				$query_args = [
					'limit'    => self::PER_PAGE,
					'page'     => $current_page,
					'paginate' => true,
					'orderby'  => 'date',
					'order'    => 'DESC',
				];
				if ($selected_status_key !== '') {
					$status_slug = preg_replace('/^wc-/', '', $selected_status_key);
					$status_slug = is_string($status_slug) ? sanitize_key($status_slug) : '';
					if ($status_slug !== '') {
						$query_args['status'] = [$status_slug];
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
			}
	
			$can_approve = self::current_user_can_approve_orders();
			$hide_order_status = self::should_hide_order_status();
	
			echo '<h3 class="swh_order_history_title">' . esc_html__('Admin: Orders', 'user-manager') . '</h3>';
			echo '<p class="swh_order_history_desc"></p>';
			self::render_search_form($endpoint, __('Search orders...', 'user-manager'));
			self::render_order_status_filter_links($endpoint, $status_filters, $selected_status_key, $search);
	
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
					if (!$hide_order_status) {
						echo '<br /><span class="um-my-account-admin-status">' . esc_html($status_label) . '</span>';
					}
					echo '</td>';
					echo '<td>' . wp_kses_post($address_html) . '</td>';
					echo '<td class="center">';
					echo '<a class="button breathing_room full_width" href="' . esc_url($view_url) . '">' . esc_html__('View Order', 'user-manager') . '</a> ';
					echo '<a class="button breathing_room full_width" href="' . esc_url($print_url) . '">' . esc_html__('Print Order', 'user-manager') . '</a>';
					if ($can_approve && !$order->has_status('completed')) {
						$approve_url = self::get_approve_order_url($order_id, self::get_list_context_query_args());
						$decline_url = self::get_decline_order_url($order_id, self::get_list_context_query_args());
						echo ' <a class="button breathing_room full_width" href="' . esc_url($approve_url) . '">' . esc_html__('Approve', 'user-manager') . '</a>';
						echo ' <a class="button breathing_room full_width" href="' . esc_url($decline_url) . '">' . esc_html__('Decline', 'user-manager') . '</a>';
					}
					echo '</td>';
					echo '</tr>';
				}
			}
	
			echo '</tbody></table>';
			self::render_pagination($endpoint, $current_page, $pages, $search);
		}
	
		

		private static function render_order_detail(int $order_id): void {
			$order = wc_get_order($order_id);
			if (!$order) {
				self::print_error_notice(__('Order not found.', 'user-manager'));
				return;
			}
	
			$back_url = self::get_list_url('admin_orders');
			$can_approve = self::current_user_can_approve_orders() && !$order->has_status('completed');
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
				$decline_url = self::get_decline_order_url((int) $order->get_id(), $approve_args);
				echo ' <a class="button" href="' . esc_url($approve_url) . '">' . esc_html__('Approve', 'user-manager') . '</a>';
				echo ' <a class="button" href="' . esc_url($decline_url) . '">' . esc_html__('Decline', 'user-manager') . '</a>';
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

			self::render_order_additional_meta_fields($order);
	
			if (self::should_show_meta_for_area('orders')) {
				self::render_meta_table_from_post((int) $order->get_id());
			}
	
			if (isset($_GET['print']) && $_GET['print'] === '1') {
				echo '<script>window.print();</script>';
			}
		}
	
		

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
	
		

}
