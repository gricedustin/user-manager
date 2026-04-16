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

		public static function render_admin_activity_endpoint(): void {
			self::render_shared_styles();
			if (!self::ensure_area_access('activity')) {
				return;
			}

			self::render_activity_list();
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
			$add_webtoffee_download_invoice_button = self::should_add_webtoffee_download_invoice_button();
			$add_webtoffee_print_invoice_button = self::should_add_webtoffee_print_invoice_button();
			$approve_label = self::get_order_approve_button_label();
			$decline_label = self::get_order_decline_button_label();
	
			echo '<h3 class="swh_order_history_title">' . esc_html__('Admin: Orders', 'user-manager') . '</h3>';
			echo '<p class="swh_order_history_desc"></p>';
			self::render_search_form($endpoint, __('Search orders...', 'user-manager'));
			self::render_order_status_filter_links($endpoint, $status_filters, $selected_status_key, $search);
	
			echo '<table class="express_checkout_order_approvals woocommerce_my_account_admin_tools woocommerce_my_account_admin_tools_orders">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__('Order', 'user-manager') . '</th>';
			echo '<th>' . esc_html__('Shipping Address', 'user-manager') . '</th>';
			echo '<th></th>';
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
					$status_label  = self::get_order_status_display_label((string) $order->get_status());
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
					$webtoffee_invoice_urls = self::get_webtoffee_invoice_action_urls($order);
	
					echo '<tr class="express_checkout_order_approvals_row">';
					echo '<td><strong>' . esc_html($order_number) . '</strong>';
					echo '<div class="um-my-account-admin-order-meta-block">';
					echo esc_html($date_display);
					if ($billing_email !== '') {
						echo '<br />' . esc_html($billing_email);
					}
					if (!$hide_order_status) {
						echo '<br /><span class="um-my-account-admin-status">' . esc_html($status_label) . '</span>';
					}
					echo '</div>';
					echo '</td>';
					echo '<td>';
					if ($address_html !== '') {
						echo '<div class="um-my-account-admin-order-address-block">' . wp_kses_post($address_html) . '</div>';
					}
					echo '</td>';
					echo '<td class="um-my-account-order-list-meta-column">';
					$order_list_additional_meta_html = self::get_order_additional_meta_fields_for_orders_list_html($order);
					if ($order_list_additional_meta_html !== '') {
						echo '<div class="um-my-account-order-list-meta-wrap">' . wp_kses_post($order_list_additional_meta_html) . '</div>';
					} else {
						echo '&nbsp;';
					}
					echo '</td>';
					echo '<td class="center">';
					echo '<a class="button breathing_room full_width um-my-account-admin-order-btn um-my-account-admin-order-btn-view" href="' . esc_url($view_url) . '">' . esc_html__('View Order', 'user-manager') . '</a> ';
					echo '<a class="button breathing_room full_width um-my-account-admin-order-btn um-my-account-admin-order-btn-print" href="' . esc_url($print_url) . '">' . esc_html__('Print Order', 'user-manager') . '</a>';
					if ($add_webtoffee_print_invoice_button && $webtoffee_invoice_urls['print'] !== '') {
						echo ' <a class="button breathing_room full_width um-my-account-admin-order-btn um-my-account-admin-order-btn-print-invoice" href="' . esc_url($webtoffee_invoice_urls['print']) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Print Invoice', 'user-manager') . '</a>';
					}
					if ($add_webtoffee_download_invoice_button && $webtoffee_invoice_urls['download'] !== '') {
						echo ' <a class="button breathing_room full_width um-my-account-admin-order-btn um-my-account-admin-order-btn-download-invoice" href="' . esc_url($webtoffee_invoice_urls['download']) . '">' . esc_html__('Download Invoice', 'user-manager') . '</a>';
					}
					if ($can_approve && !$order->has_status('completed')) {
						if (!$order->has_status('processing')) {
							$approve_url = self::get_approve_order_url($order_id, self::get_list_context_query_args());
							echo ' <a class="button breathing_room full_width um-my-account-admin-order-btn um-my-account-admin-order-btn-approve" href="' . esc_url($approve_url) . '">' . esc_html($approve_label) . '</a>';
						}
						$decline_url = self::get_decline_order_url($order_id, self::get_list_context_query_args());
						echo ' <a class="button breathing_room full_width um-my-account-admin-order-btn um-my-account-admin-order-btn-decline" href="' . esc_url($decline_url) . '">' . esc_html($decline_label) . '</a>';
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
			$approve_label = self::get_order_approve_button_label();
			$decline_label = self::get_order_decline_button_label();
			echo '<h3 class="swh_order_history_title">' . esc_html__('Admin: Orders', 'user-manager') . '</h3>';
			echo '<p class="swh_order_history_desc"></p>';
			echo '<a class="button" href="' . esc_url($back_url) . '">' . esc_html__('Back', 'user-manager') . '</a>';
			if ($can_approve) {
				$approve_args = self::get_list_context_query_args();
				$approve_args['order_id'] = (int) $order->get_id();
				if (isset($_GET['print']) && $_GET['print'] === '1') {
					$approve_args['print'] = '1';
				}
				if (!$order->has_status('processing')) {
					$approve_url = self::get_approve_order_url((int) $order->get_id(), $approve_args);
					echo ' <a class="button" href="' . esc_url($approve_url) . '">' . esc_html($approve_label) . '</a>';
				}
				$decline_url = self::get_decline_order_url((int) $order->get_id(), $approve_args);
				echo ' <a class="button" href="' . esc_url($decline_url) . '">' . esc_html($decline_label) . '</a>';
			}
			echo '<br><br>';
	
			$date_created = $order->get_date_created();
			$date_display = $date_created ? $date_created->date_i18n('D M d, Y g:ia') : '';
			$status_label = self::get_order_status_display_label((string) $order->get_status());
	
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

		private static function render_activity_list(): void {
			global $wpdb;
			if (!$wpdb instanceof wpdb) {
				self::print_error_notice(__('Activity data is unavailable right now.', 'user-manager'));
				return;
			}

			$endpoint             = 'admin_activity';
			$per_page             = self::PER_PAGE;
			$current_page         = self::get_current_page();
			$offset               = ($current_page - 1) * $per_page;
			$search               = self::get_search_query();
			$action_filter        = trim(self::get_activity_action_filter_query_arg());
			$allowed_actions      = self::get_activity_allowed_actions_from_settings();
			$hidden_email_filters = self::get_activity_hidden_email_partials();
			$role_review_enabled  = self::is_activity_role_review_enabled();

			if (!empty($allowed_actions) && !in_array($action_filter, $allowed_actions, true)) {
				$action_filter = '';
			}

			$table       = $wpdb->prefix . 'um_user_activity';
			$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
			$entries     = [];
			$total       = 0;
			$all_actions = [];
			$role_review_user_ids = [];

			if ($table_exists) {
				if ($role_review_enabled) {
					$role_review_user_ids = self::get_activity_role_review_user_ids_map($table);
				}

				$where_parts = ['1=1'];
				$params = [];

				if (!empty($allowed_actions)) {
					$allowed_placeholders = implode(',', array_fill(0, count($allowed_actions), '%s'));
					$where_parts[] = "h.action IN ({$allowed_placeholders})";
					foreach ($allowed_actions as $allowed_action) {
						$params[] = $allowed_action;
					}
				}

				if ($action_filter !== '') {
					$where_parts[] = 'TRIM(h.action) = %s';
					$params[] = $action_filter;
				}

				if ($search !== '') {
					$like = '%' . $wpdb->esc_like($search) . '%';
					$where_parts[] = '(h.action LIKE %s OR h.url LIKE %s OR h.ip_address LIKE %s OR h.user_agent LIKE %s OR h.roles LIKE %s OR u.user_login LIKE %s OR u.user_email LIKE %s OR u.display_name LIKE %s OR CAST(h.user_id AS CHAR) LIKE %s)';
					for ($i = 0; $i < 9; $i++) {
						$params[] = $like;
					}
				}

				$where_sql = implode(' AND ', $where_parts);
				$count_sql = "SELECT COUNT(*) FROM {$table} h LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID WHERE {$where_sql}";
				if (!empty($params)) {
					$total = (int) $wpdb->get_var($wpdb->prepare($count_sql, ...$params));
				} else {
					$total = (int) $wpdb->get_var($count_sql);
				}

				$query_sql = "
					SELECT h.id, h.user_id, h.action, h.url, h.ip_address, h.user_agent, h.roles, h.created_at,
					       u.user_login, u.user_email, u.display_name
					  FROM {$table} h
					  LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
					 WHERE {$where_sql}
				  ORDER BY h.created_at DESC
				  LIMIT %d OFFSET %d
				";
				$query_params = $params;
				$query_params[] = $per_page;
				$query_params[] = $offset;
				$query = $wpdb->prepare($query_sql, ...$query_params);
				$rows = $wpdb->get_results($query);
				if (is_array($rows)) {
					foreach ($rows as $row) {
						$timestamp = $row->created_at ? strtotime((string) $row->created_at) : 0;
						$user_id = isset($row->user_id) ? (int) $row->user_id : 0;
						$user_email = isset($row->user_email) ? (string) $row->user_email : '';
						$email_hidden = self::should_hide_activity_email($user_email, $hidden_email_filters);
						$display_email = $email_hidden ? '' : $user_email;

						$display_name = isset($row->display_name) ? (string) $row->display_name : '';
						if ($display_name === '') {
							$display_name = $display_email !== '' ? $display_email : ((string) ($row->user_login ?? ''));
						}

						$entries[] = [
							'user_id'       => $user_id,
							'user_login'    => isset($row->user_login) ? (string) $row->user_login : '',
							'user_email'    => $display_email,
							'display_name'  => $display_name,
							'action'        => isset($row->action) ? (string) $row->action : '',
							'url'           => isset($row->url) ? (string) $row->url : '',
							'ip_address'    => isset($row->ip_address) ? (string) $row->ip_address : '',
							'user_agent'    => isset($row->user_agent) ? (string) $row->user_agent : '',
							'roles'         => isset($row->roles) ? (string) $row->roles : '',
							'timestamp'     => $timestamp > 0 ? $timestamp : 0,
							'role_review'   => $role_review_enabled && $user_id > 0 && isset($role_review_user_ids[$user_id]),
						];
					}
				}

				if (!empty($allowed_actions)) {
					$all_actions = $allowed_actions;
				} else {
					$raw_actions = $wpdb->get_col("SELECT DISTINCT action FROM {$table} WHERE action <> '' ORDER BY action ASC");
					if (is_array($raw_actions)) {
						foreach ($raw_actions as $raw_action) {
							$normalized_action = sanitize_text_field((string) $raw_action);
							if ($normalized_action === '') {
								continue;
							}
							$all_actions[] = $normalized_action;
						}
					}
					$all_actions = array_values(array_unique($all_actions));
				}
			}

			$total_pages = max(1, (int) ceil($total / $per_page));
			echo '<h3 class="swh_users_title">' . esc_html__('Admin: Activity', 'user-manager') . '</h3>';
			echo '<p class="swh_users_desc"></p>';
			self::render_search_form($endpoint, __('Search activity...', 'user-manager'));
			self::render_activity_action_filter_form($endpoint, $all_actions, $action_filter, $search);

			if (!$table_exists) {
				self::print_error_notice(__('User activity table was not found yet. Activity appears after log entries are created.', 'user-manager'));
				return;
			}

			if (empty($entries)) {
				echo '<p class="woocommerce-info">' . esc_html__('No user activity found for the selected filters.', 'user-manager') . '</p>';
				return;
			}

			echo '<table class="express_checkout_order_approvals woocommerce_my_account_admin_tools woocommerce_my_account_admin_tools_users">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__('User', 'user-manager') . '</th>';
			echo '<th>' . esc_html__('Email', 'user-manager') . '</th>';
			echo '<th>' . esc_html__('Roles', 'user-manager') . '</th>';
			echo '<th>' . esc_html__('Timestamp', 'user-manager') . '</th>';
			echo '<th>' . esc_html__('Action', 'user-manager') . '</th>';
			echo '</tr></thead><tbody>';

			foreach ($entries as $entry) {
				$ts = isset($entry['timestamp']) ? (int) $entry['timestamp'] : 0;
				$roles_text = isset($entry['roles']) ? (string) $entry['roles'] : '';
				$has_role_review = !empty($entry['role_review']);
				$user_id = isset($entry['user_id']) ? (int) $entry['user_id'] : 0;
				echo '<tr class="express_checkout_order_approvals_row">';
				echo '<td class="middle">';
				$display_user = (string) ($entry['display_name'] ?? '');
				if ($display_user === '') {
					$display_user = (string) ($entry['user_login'] ?? '');
				}
				if ($display_user === '') {
					$display_user = '&mdash;';
				}
				if ($user_id > 0) {
					$edit_user_url = get_edit_user_link($user_id);
					if (is_string($edit_user_url) && $edit_user_url !== '') {
						echo '<a href="' . esc_url($edit_user_url) . '">' . esc_html($display_user) . '</a>';
					} else {
						echo esc_html($display_user);
					}
				} else {
					echo esc_html($display_user);
				}
				echo '</td>';
				$email = (string) ($entry['user_email'] ?? '');
				echo '<td class="middle">' . ($email !== '' ? esc_html($email) : '&mdash;') . '</td>';
				echo '<td class="middle">';
				echo $roles_text !== '' ? esc_html($roles_text) : '&mdash;';
				if ($has_role_review) {
					echo ' <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#f0f6ff;color:#0a4b78;font-size:11px;font-weight:600;line-height:1.4;">' . esc_html__('User role change found in past', 'user-manager') . '</span>';
				}
				echo '</td>';
				echo '<td class="middle">';
				echo $ts > 0 ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ts)) : '&mdash;';
				echo '</td>';
				echo '<td class="middle">' . esc_html((string) ($entry['action'] ?? '')) . '</td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
			self::render_pagination($endpoint, $current_page, $total_pages, $search);
		}

		private static function render_activity_action_filter_form(string $endpoint, array $all_actions, string $action_filter, string $search): void {
			$base_url = self::get_endpoint_url($endpoint);
			echo '<div style="margin-bottom: 16px;">';
			echo '<form method="get" action="' . esc_url($base_url) . '" style="display:inline-block;">';
			if ($search !== '') {
				echo '<input type="hidden" name="um_search" value="' . esc_attr($search) . '" />';
			}
			echo '<label for="um-my-account-activity-action-filter" style="margin-right:8px;"><strong>' . esc_html__('Filter by Action:', 'user-manager') . '</strong></label>';
			echo '<select name="ua_action_filter" id="um-my-account-activity-action-filter" style="min-width:220px;">';
			echo '<option value="">' . esc_html__('All Actions', 'user-manager') . '</option>';
			foreach ($all_actions as $action_option) {
				$action_option = trim(sanitize_text_field((string) $action_option));
				if ($action_option === '') {
					continue;
				}
				echo '<option value="' . esc_attr($action_option) . '" ' . selected($action_filter, $action_option, false) . '>' . esc_html($action_option) . '</option>';
			}
			echo '</select>';
			echo ' <button type="submit" class="button">' . esc_html__('Apply Filter', 'user-manager') . '</button>';
			if ($action_filter !== '') {
				$clear_args = [];
				if ($search !== '') {
					$clear_args['um_search'] = $search;
				}
				$clear_url = self::get_endpoint_url($endpoint, $clear_args);
				echo ' <a class="button" href="' . esc_url($clear_url) . '">' . esc_html__('Clear Filter', 'user-manager') . '</a>';
			}
			echo '</form>';
			echo '</div>';
		}

		/**
		 * @return array<int,string>
		 */
		private static function get_activity_allowed_actions_from_settings(): array {
			$settings = User_Manager_Core::get_settings();
			$raw_actions = $settings['my_account_admin_activity_viewer_actions'] ?? [];
			if (!is_array($raw_actions)) {
				return [];
			}

			$actions = [];
			foreach ($raw_actions as $raw_action) {
				$action = trim(sanitize_text_field((string) $raw_action));
				if ($action === '') {
					continue;
				}
				$actions[] = $action;
			}

			return array_values(array_unique($actions));
		}

		/**
		 * @return array<int,string>
		 */
		private static function get_activity_hidden_email_partials(): array {
			$settings = User_Manager_Core::get_settings();
			$raw = isset($settings['my_account_admin_activity_viewer_hidden_email_partials'])
				? (string) $settings['my_account_admin_activity_viewer_hidden_email_partials']
				: '';
			$raw = trim($raw);
			if ($raw === '') {
				return [];
			}

			$parts = preg_split('/[\r\n,]+/', $raw);
			if (!is_array($parts)) {
				return [];
			}

			$values = [];
			foreach ($parts as $part) {
				$part = sanitize_text_field((string) $part);
				$part = strtolower(trim($part));
				if ($part === '') {
					continue;
				}
				$values[] = $part;
			}

			return array_values(array_unique($values));
		}

		private static function should_hide_activity_email(string $email, array $hidden_email_partials): bool {
			$email = strtolower(trim($email));
			if ($email === '' || empty($hidden_email_partials)) {
				return false;
			}

			foreach ($hidden_email_partials as $partial) {
				$partial = strtolower(trim((string) $partial));
				if ($partial === '') {
					continue;
				}
				if (strpos($email, $partial) !== false) {
					return true;
				}
			}

			return false;
		}

		private static function is_activity_role_review_enabled(): bool {
			$settings = User_Manager_Core::get_settings();
			return !empty($settings['my_account_admin_activity_viewer_role_review_enabled']);
		}

		/**
		 * @param string $table Fully-qualified user activity table name.
		 * @return array<int,bool>
		 */
		private static function get_activity_role_review_user_ids_map(string $table): array {
			global $wpdb;
			if (!$wpdb instanceof wpdb) {
				return [];
			}

			$ids = $wpdb->get_col(
				"SELECT user_id
				   FROM {$table}
				  WHERE user_id > 0
				    AND roles <> ''
			   GROUP BY user_id
				 HAVING COUNT(DISTINCT roles) > 1"
			);
			if (!is_array($ids)) {
				return [];
			}

			$map = [];
			foreach ($ids as $id) {
				$user_id = (int) $id;
				if ($user_id <= 0) {
					continue;
				}
				$map[$user_id] = true;
			}

			return $map;
		}
	
		

}
