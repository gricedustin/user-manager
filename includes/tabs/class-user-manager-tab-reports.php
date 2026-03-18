<?php
/**
 * Reports tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}


require_once __DIR__ . '/reports/trait-user-manager-tab-reports-tracking.php';
class User_Manager_Tab_Reports {
	use User_Manager_Tab_Reports_Tracking_Trait;

	public static function render(): void {
		$base_url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS);
		$requested_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : User_Manager_Core::TAB_REPORTS;
		$reports_section = isset($_GET['reports_section']) ? sanitize_key(wp_unslash($_GET['reports_section'])) : '';
		$valid_sections = ['general', 'user-activity', 'admin-log', 'coupon-lookup-email'];

		// Backward compatibility: legacy top-level tabs now live under Reports sub links.
		if ($reports_section === '') {
			if ($requested_tab === User_Manager_Core::TAB_LOGIN_HISTORY) {
				$reports_section = 'user-activity';
			} elseif ($requested_tab === User_Manager_Core::TAB_ACTIVITY_LOG) {
				$reports_section = 'admin-log';
			} elseif ($requested_tab === User_Manager_Core::TAB_TOOLS && isset($_GET['coupon_lookup_email'])) {
				$reports_section = 'coupon-lookup-email';
			}
		}
		if (!in_array($reports_section, $valid_sections, true)) {
			$reports_section = 'general';
		}

		$general_url = add_query_arg('reports_section', 'general', $base_url);
		$user_activity_url = add_query_arg('reports_section', 'user-activity', $base_url);
		$admin_log_url = add_query_arg('reports_section', 'admin-log', $base_url);
		$coupon_lookup_url = add_query_arg('reports_section', 'coupon-lookup-email', $base_url);

		?>
		<ul class="subsubsub" style="margin: 12px 0 14px;">
			<li>
				<a href="<?php echo esc_url($general_url); ?>" class="<?php echo $reports_section === 'general' ? 'current' : ''; ?>">
					<?php esc_html_e('General Reports', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($user_activity_url); ?>" class="<?php echo $reports_section === 'user-activity' ? 'current' : ''; ?>">
					<?php esc_html_e('User Activity', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($admin_log_url); ?>" class="<?php echo $reports_section === 'admin-log' ? 'current' : ''; ?>">
					<?php esc_html_e('Admin Log', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($coupon_lookup_url); ?>" class="<?php echo $reports_section === 'coupon-lookup-email' ? 'current' : ''; ?>">
					<?php esc_html_e('Coupon Lookup by Email', 'user-manager'); ?>
				</a>
			</li>
		</ul>
		<br class="clear" />
		<?php

		if ($reports_section === 'user-activity') {
			User_Manager_Tab_Login_History::render();
			return;
		}

		if ($reports_section === 'admin-log') {
			User_Manager_Tab_Activity_Log::render();
			return;
		}
		if ($reports_section === 'coupon-lookup-email') {
			User_Manager_Tab_Tools::render_coupon_lookup_by_email_card(
				User_Manager_Core::TAB_REPORTS,
				['reports_section' => 'coupon-lookup-email'],
				'Reports'
			);
			return;
		}

		$report = isset($_GET['report']) ? sanitize_key(wp_unslash($_GET['report'])) : '';
		$valid_reports = [
			'user-logins',
			'coupons-used',
			'coupons-assigned',
			'password-reset',
			'user-password-changes',
			'sales-vs-coupons',
			'coupons-no-expiration',
			'coupons-free-shipping',
			'coupons-remaining-balances',
			'coupons-unused',
			'coupon-audit',
			'orders-with-refunds',
			'orders-zero-total',
			'orders-free-shipping',
			'order-payment-methods',
			'order-notes',
			'orders-processing-days',
			'orders-shipments-by-day',
			'orders-shipments-by-week',
			'orders-shipments-by-month',
			'orders-tracking-numbers',
			'orders-tracking-notes',
			'orders-still-processing-with-tracking-number',
			'user-data',
			'product-purchases',
			'product-category-purchases',
			'product-tag-purchases',
			'user-total-sales',
			'users-who-used-coupons',
			'page-not-found-404-errors',
			'search-queries',
			'page-views',
			'page-category-views',
			'post-views',
			'post-category-views',
			'post-tag-views',
			'product-views',
			'product-category-views',
			'product-tag-views',
			'post-meta-field-names',
		];
		
		if (!in_array($report, $valid_reports, true)) {
			$report = '';
		}

		// Human-readable labels for each report (without "Recent"/"All").
		$report_labels = [
			'user-logins'                 => __('User Logins', 'user-manager'),
			'coupons-used'                => __('Coupons Used', 'user-manager'),
			'coupons-assigned'            => __('Coupons with Email Addresses', 'user-manager'),
			'coupons-no-expiration'       => __('Coupons with No Expiration', 'user-manager'),
			'coupons-free-shipping'       => __('Coupons with Free Shipping', 'user-manager'),
			'coupons-remaining-balances'  => __('Coupons with Remaining Balances', 'user-manager'),
			'coupons-unused'              => __('Coupons Unused', 'user-manager'),
			'coupon-audit'                => __('Coupon Audit', 'user-manager'),
			'orders-with-refunds'         => __('Order Refunds', 'user-manager'),
			'orders-zero-total'           => __('Orders with $0 Total', 'user-manager'),
			'orders-free-shipping'        => __('Orders with Free Shipping', 'user-manager'),
			'order-payment-methods'       => __('Order Payment Methods', 'user-manager'),
			'order-notes'                 => __('Order Notes', 'user-manager'),
			'sales-vs-coupons'            => __('Order Sales vs Coupon Usage', 'user-manager'),
			'password-reset'              => __('User Password Resets', 'user-manager'),
			'user-password-changes'       => __('User Password Changes', 'user-manager'),
			'orders-processing-days'      => __('Orders Processing by Number of Days', 'user-manager'),
			'orders-shipments-by-day'     => __('Order Total Shipments by Day', 'user-manager'),
			'orders-shipments-by-week'    => __('Order Total Shipments by Week', 'user-manager'),
			'orders-shipments-by-month'   => __('Order Total Shipments by Month', 'user-manager'),
			'orders-tracking-numbers'     => __('Order Tracking Numbers', 'user-manager'),
			'orders-tracking-notes'       => __('Order Tracking Number Notes', 'user-manager'),
			'orders-still-processing-with-tracking-number' => __('Orders Still Processing but have a Tracking Number', 'user-manager'),
			'user-data'                   => __('User Data', 'user-manager'),
			'product-purchases'           => __('Product Purchases', 'user-manager'),
			'product-category-purchases'  => __('Product Category Purchases', 'user-manager'),
			'product-tag-purchases'       => __('Product Tag Purchases', 'user-manager'),
			'user-total-sales'            => __('User Total Sales', 'user-manager'),
			'users-who-used-coupons'      => __('User Coupon Usage', 'user-manager'),
			'page-not-found-404-errors'   => __('Page Not Found 404 Errors', 'user-manager'),
			'search-queries'              => __('Search Queries', 'user-manager'),
			'page-views'                  => __('Page Views', 'user-manager'),
			'page-category-views'         => __('Page Category Archives Views', 'user-manager'),
			'post-views'                  => __('Post Views', 'user-manager'),
			'post-category-views'         => __('Post Category Archives Views', 'user-manager'),
			'post-tag-views'              => __('Post Tag Archives Views', 'user-manager'),
			'product-views'               => __('Product Views', 'user-manager'),
			'product-category-views'      => __('Product Category Archives Views', 'user-manager'),
			'product-tag-views'           => __('Product Tag Archives Views', 'user-manager'),
			'post-meta-field-names'       => __('Post Meta Field Names (Unique List)', 'user-manager'),
		];

		// Build sorted (A–Z) list of available reports for the dropdown.
		$report_options = [];
		foreach ($valid_reports as $key) {
			if (isset($report_labels[$key])) {
				$report_options[$key] = $report_labels[$key];
			}
		}
		asort($report_options, SORT_NATURAL | SORT_FLAG_CASE);
		?>
		<div class="um-admin-grid">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-chart-bar"></span>
					<h2><?php esc_html_e('Reports', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div style="margin:0 0 30px 0; padding:0 0 15px 0; border-bottom:1px solid #ccd0d4; display:block;">
						<form method="get" action="<?php echo esc_url($base_url); ?>" style="display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap;">
							<input type="hidden" name="page" value="<?php echo esc_attr(User_Manager_Core::SETTINGS_PAGE_SLUG); ?>" />
							<input type="hidden" name="tab" value="<?php echo esc_attr(User_Manager_Core::TAB_REPORTS); ?>" />
							<label for="um-report-select">
								<strong><?php esc_html_e('Select report:', 'user-manager'); ?></strong>
							</label>
							<select id="um-report-select" name="report" onchange="this.form.submit();" style="min-width:260px;">
								<option value=""><?php esc_html_e('— Choose a report —', 'user-manager'); ?></option>
								<?php foreach ($report_options as $key => $label) : ?>
									<option value="<?php echo esc_attr($key); ?>" <?php selected($report, $key); ?>>
										<?php echo esc_html($label); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</form>
					</div>
					
					<?php if ($report === 'user-logins') : ?>
						<?php self::render_user_logins_report(); ?>
					<?php elseif ($report === 'coupons-used') : ?>
						<?php self::render_coupons_used_report(); ?>
					<?php elseif ($report === 'coupons-assigned') : ?>
						<?php self::render_coupons_assigned_report(); ?>
					<?php elseif ($report === 'coupons-no-expiration') : ?>
						<?php self::render_coupons_no_expiration_report(); ?>
					<?php elseif ($report === 'coupons-free-shipping') : ?>
						<?php self::render_coupons_free_shipping_report(); ?>
					<?php elseif ($report === 'coupons-remaining-balances') : ?>
						<?php self::render_coupons_remaining_balances_report(); ?>
					<?php elseif ($report === 'coupons-unused') : ?>
						<?php self::render_coupons_unused_report(); ?>
					<?php elseif ($report === 'coupon-audit') : ?>
						<?php self::render_coupon_audit_report(); ?>
					<?php elseif ($report === 'orders-with-refunds') : ?>
						<?php self::render_orders_with_refunds_report(); ?>
					<?php elseif ($report === 'orders-zero-total') : ?>
						<?php self::render_orders_zero_total_report(); ?>
					<?php elseif ($report === 'orders-free-shipping') : ?>
						<?php self::render_orders_free_shipping_report(); ?>
					<?php elseif ($report === 'order-payment-methods') : ?>
						<?php self::render_order_payment_methods_report(); ?>
					<?php elseif ($report === 'order-notes') : ?>
						<?php self::render_order_notes_report(); ?>
					<?php elseif ($report === 'orders-processing-days') : ?>
						<?php self::render_orders_processing_days_report(); ?>
					<?php elseif ($report === 'orders-shipments-by-day') : ?>
						<?php self::render_orders_shipments_by_day_report(); ?>
					<?php elseif ($report === 'orders-shipments-by-week') : ?>
						<?php self::render_orders_shipments_by_week_report(); ?>
					<?php elseif ($report === 'orders-shipments-by-month') : ?>
						<?php self::render_orders_shipments_by_month_report(); ?>
					<?php elseif ($report === 'orders-tracking-numbers') : ?>
						<?php self::render_orders_tracking_numbers_report(); ?>
					<?php elseif ($report === 'orders-tracking-notes') : ?>
						<?php self::render_orders_tracking_notes_report(); ?>
					<?php elseif ($report === 'orders-still-processing-with-tracking-number') : ?>
						<?php self::render_orders_still_processing_with_tracking_number_report(); ?>
					<?php elseif ($report === 'sales-vs-coupons') : ?>
						<?php self::render_sales_vs_coupons_report(); ?>
					<?php elseif ($report === 'password-reset') : ?>
						<?php self::render_password_reset_report(); ?>
					<?php elseif ($report === 'user-password-changes') : ?>
						<?php self::render_user_password_changes_report(); ?>
					<?php elseif ($report === 'user-data') : ?>
						<?php self::render_user_data_report(); ?>
					<?php elseif ($report === 'user-total-sales') : ?>
						<?php self::render_user_total_sales_report(); ?>
					<?php elseif ($report === 'users-who-used-coupons') : ?>
						<?php self::render_users_who_used_coupons_report(); ?>
					<?php elseif ($report === 'product-purchases') : ?>
						<?php self::render_product_purchases_report(); ?>
					<?php elseif ($report === 'product-category-purchases') : ?>
						<?php self::render_product_category_purchases_report(); ?>
					<?php elseif ($report === 'product-tag-purchases') : ?>
						<?php self::render_product_tag_purchases_report(); ?>
					<?php elseif ($report === 'page-not-found-404-errors') : ?>
						<?php self::render_page_not_found_404_errors_report(); ?>
					<?php elseif ($report === 'search-queries') : ?>
						<?php self::render_search_queries_report(); ?>
					<?php elseif ($report === 'page-views') : ?>
						<?php self::render_page_views_report(); ?>
					<?php elseif ($report === 'page-category-views') : ?>
						<?php self::render_page_category_views_report(); ?>
					<?php elseif ($report === 'post-views') : ?>
						<?php self::render_post_views_report(); ?>
					<?php elseif ($report === 'post-category-views') : ?>
						<?php self::render_post_category_views_report(); ?>
					<?php elseif ($report === 'post-tag-views') : ?>
						<?php self::render_post_tag_views_report(); ?>
					<?php elseif ($report === 'product-views') : ?>
						<?php self::render_product_views_report(); ?>
					<?php elseif ($report === 'product-category-views') : ?>
						<?php self::render_product_category_views_report(); ?>
					<?php elseif ($report === 'product-tag-views') : ?>
						<?php self::render_product_tag_views_report(); ?>
					<?php elseif ($report === 'post-meta-field-names') : ?>
						<?php self::render_post_meta_field_names_report(); ?>
					<?php else : ?>
						<div style="margin-top:30px; clear:both;">
							<p><?php esc_html_e('Please select a report from the menu above.', 'user-manager'); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	public static function export_user_logins_csv(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'um_login_history';
		$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
		
		$entries = [];
		
		if ($table_exists) {
			$query = "SELECT h.id, h.user_id, h.username, h.email, h.ip_address, h.user_agent, h.created_at,
			                 u.user_login, u.user_email, u.display_name
			            FROM {$table} h
			            LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
			           ORDER BY h.created_at DESC";
			
			$rows = $wpdb->get_results($query);
			foreach ($rows as $row) {
				$entries[] = [
					$row->user_id ? (int) $row->user_id : '',
					$row->user_login ?: $row->username ?: '',
					$row->user_email ?: $row->email ?: '',
					$row->display_name ?: ($row->user_email ?: $row->username ?: ''),
					$row->ip_address ?: '',
					$row->user_agent ?: '',
					$row->created_at,
				];
			}
		}
		
		self::export_csv('recent-user-logins', [
			'User ID', 'Username', 'Email', 'Display Name', 'IP Address', 'User Agent', 'Login Date'
		], $entries);
	}

	private static function render_user_logins_report(): void {
		global $wpdb;
		$per_page = 50;
		$paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset = ($paged - 1) * $per_page;
		
		$table = $wpdb->prefix . 'um_login_history';
		$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
		
		$entries = [];
		$total = 0;
		
		if ($table_exists) {
			$total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
			
			$query = $wpdb->prepare(
				"SELECT h.id, h.user_id, h.username, h.email, h.ip_address, h.user_agent, h.created_at,
				        u.user_login, u.user_email, u.display_name
				   FROM {$table} h
				   LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
				  ORDER BY h.created_at DESC
				  LIMIT %d OFFSET %d",
				$per_page,
				$offset
			);
			
			$rows = $wpdb->get_results($query);
			foreach ($rows as $row) {
				$entries[] = [
					'user_id' => (int) $row->user_id,
					'username' => $row->user_login ?: $row->username ?: '',
					'email' => $row->user_email ?: $row->email ?: '',
					'display_name' => $row->display_name ?: ($row->user_email ?: $row->username ?: ''),
					'ip_address' => $row->ip_address ?: '',
					'user_agent' => $row->user_agent ?: '',
					'created_at' => $row->created_at,
				];
			}
		}
		
		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'user-logins', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));
		
		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>
		
		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No login records found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('User', 'user-manager'); ?></th>
						<th><?php esc_html_e('Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Username', 'user-manager'); ?></th>
						<th><?php esc_html_e('IP Address', 'user-manager'); ?></th>
						<th><?php esc_html_e('User Agent', 'user-manager'); ?></th>
						<th><?php esc_html_e('Login Date', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $e) : ?>
						<tr>
							<td>
								<?php if (!empty($e['user_id'])) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($e['user_id'])); ?>">
										<?php echo esc_html($e['display_name'] ?: $e['email'] ?: $e['username']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($e['display_name'] ?: $e['email'] ?: $e['username'] ?: '—'); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($e['email'] ?: '—'); ?></td>
							<td><?php echo esc_html($e['username'] ?: '—'); ?></td>
							<td><code><?php echo esc_html($e['ip_address'] ?: '—'); ?></code></td>
							<td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo esc_attr($e['user_agent']); ?>">
								<?php echo esc_html($e['user_agent'] ?: '—'); ?>
							</td>
							<td><?php echo esc_html($e['created_at']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	public static function export_coupons_used_csv(): void {
		global $wpdb;
		
		if (!class_exists('WooCommerce')) {
			return;
		}
		
		$orders_table = $wpdb->prefix . 'posts';
		$order_items_table = $wpdb->prefix . 'woocommerce_order_items';
		$order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
		
		$query = "SELECT DISTINCT oi.order_id, oi.order_item_id, oi.order_item_name as coupon_code, 
		                 p.post_date, pm1.meta_value as order_total, pm2.meta_value as billing_email,
		                 oim.meta_value as discount_amount
		            FROM {$order_items_table} oi
		            INNER JOIN {$orders_table} p ON oi.order_id = p.ID
		            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_order_total'
		            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_billing_email'
		            LEFT JOIN {$order_itemmeta_table} oim ON oi.order_item_id = oim.order_item_id AND oim.meta_key = 'discount_amount'
		           WHERE oi.order_item_type = 'coupon'
		             AND p.post_type = 'shop_order'
		             AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
		           ORDER BY p.post_date DESC";
		
		$rows = $wpdb->get_results($query);
		$entries = [];
		foreach ($rows as $row) {
			$entries[] = [
				(int) $row->order_id,
				$row->coupon_code ?: '',
				$row->post_date ?: '',
				$row->order_total ?: '0',
				$row->discount_amount ?: '0',
				$row->billing_email ?: '',
			];
		}
		
		self::export_csv('recent-coupons-used', [
			'Order ID', 'Coupon Code', 'Order Date', 'Order Total', 'Discount Amount', 'Billing Email'
		], $entries);
	}

	/**
	 * Export password reset activity to CSV.
	 */
	public static function export_password_reset_csv(): void {
		$result = User_Manager_Core::get_activity_log(0, 0, null);
		$entries = $result['entries'] ?? $result;
		if (!is_array($entries)) {
			$entries = [];
		}

		$rows = [];
		foreach ($entries as $entry) {
			if (empty($entry['action']) || !in_array($entry['action'], ['password_reset', 'password_reset_failed', 'bulk_password_reset', 'bulk_password_reset_email_sent', 'password_reset_email_sent'], true)) {
				continue;
			}
			$extra = isset($entry['extra']) && is_array($entry['extra']) ? $entry['extra'] : [];

			$target_email = '';
			if (!empty($extra['user_email'])) {
				$target_email = (string) $extra['user_email'];
			} elseif (!empty($extra['attempted_email'])) {
				$target_email = (string) $extra['attempted_email'];
			}

			$rows[] = [
				(int) ($entry['user_id'] ?? 0),
				$entry['action'],
				$entry['tool'] ?? '',
				$target_email,
				!empty($extra['bulk']) ? 'yes' : 'no',
				!empty($extra['email_sent']) ? 'yes' : 'no',
				!empty($extra['error']) ? (string) $extra['error'] : '',
				$entry['created_at'] ?? '',
			];
		}

		self::export_csv('password-reset-activity', [
			'User ID',
			'Action',
			'Tool',
			'Target Email',
			'Bulk',
			'Email Sent',
			'Error',
			'Date',
		], $rows);
	}

	private static function render_coupons_used_report(): void {
		global $wpdb;
		$per_page = 50;
		$paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset = ($paged - 1) * $per_page;
		
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}
		
		$entries = [];
		$total = 0;
		
		// Get orders with coupons
		$orders_table = $wpdb->prefix . 'posts';
		$order_items_table = $wpdb->prefix . 'woocommerce_order_items';
		
		$total_query = "SELECT COUNT(DISTINCT oi.order_id)
		                  FROM {$order_items_table} oi
		                  INNER JOIN {$orders_table} p ON oi.order_id = p.ID
		                 WHERE oi.order_item_type = 'coupon'
		                   AND p.post_type = 'shop_order'
		                   AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')";
		$total = (int) $wpdb->get_var($total_query);
		
		$order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
		
		$query = $wpdb->prepare(
			"SELECT DISTINCT oi.order_id, oi.order_item_id, oi.order_item_name as coupon_code, 
			                 p.post_date, pm1.meta_value as order_total, pm2.meta_value as billing_email,
			                 oim.meta_value as discount_amount
			            FROM {$order_items_table} oi
			            INNER JOIN {$orders_table} p ON oi.order_id = p.ID
			            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_order_total'
			            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_billing_email'
			            LEFT JOIN {$order_itemmeta_table} oim ON oi.order_item_id = oim.order_item_id AND oim.meta_key = 'discount_amount'
			           WHERE oi.order_item_type = 'coupon'
			             AND p.post_type = 'shop_order'
			             AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
			           ORDER BY p.post_date DESC
			           LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);
		
		$rows = $wpdb->get_results($query);
		foreach ($rows as $row) {
			$entries[] = [
				'order_id' => (int) $row->order_id,
				'coupon_code' => $row->coupon_code ?: '',
				'order_date' => $row->post_date ?: '',
				'order_total' => $row->order_total ?: '0',
				'billing_email' => $row->billing_email ?: '',
				'discount_amount' => $row->discount_amount ?: '0',
			];
		}
		
		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'coupons-used', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));
		
		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>
		
		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No coupon usage found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Order ID', 'user-manager'); ?></th>
						<th><?php esc_html_e('Coupon Code', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Total', 'user-manager'); ?></th>
						<th><?php esc_html_e('Discount Amount', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Email', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $e) : ?>
						<tr>
							<td>
								<?php if (function_exists('wc_get_order')) : ?>
									<?php $order = wc_get_order($e['order_id']); ?>
									<?php if ($order) : ?>
										<a href="<?php echo esc_url(admin_url('post.php?post=' . $e['order_id'] . '&action=edit')); ?>">
											#<?php echo esc_html($e['order_id']); ?>
										</a>
									<?php else : ?>
										#<?php echo esc_html($e['order_id']); ?>
									<?php endif; ?>
								<?php else : ?>
									#<?php echo esc_html($e['order_id']); ?>
								<?php endif; ?>
							</td>
							<td><strong><?php echo esc_html(strtoupper($e['coupon_code'])); ?></strong></td>
							<td><?php echo esc_html($e['order_date']); ?></td>
							<td><?php echo function_exists('wc_price') ? wc_price($e['order_total']) : esc_html($e['order_total']); ?></td>
							<td><?php echo function_exists('wc_price') ? wc_price($e['discount_amount']) : esc_html($e['discount_amount']); ?></td>
							<td><?php echo esc_html($e['billing_email'] ?: '—'); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render Password Reset report.
	 */
	private static function render_password_reset_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$result = User_Manager_Core::get_activity_log();
		$all_entries = $result['entries'] ?? $result;
		if (!is_array($all_entries)) {
			$all_entries = [];
		}

		// Filter to password reset–related actions.
		$filtered = array_values(array_filter($all_entries, static function ($entry) {
			if (!is_array($entry) || empty($entry['action'])) {
				return false;
			}
			return in_array($entry['action'], ['password_reset', 'password_reset_failed', 'bulk_password_reset', 'bulk_password_reset_email_sent', 'password_reset_email_sent'], true);
		}));

		$total   = count($filtered);
		$entries = array_slice($filtered, $offset, $per_page);

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'password-reset', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No password reset activity found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('User', 'user-manager'); ?></th>
						<th><?php esc_html_e('Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Action', 'user-manager'); ?></th>
						<th><?php esc_html_e('Bulk', 'user-manager'); ?></th>
						<th><?php esc_html_e('Email Sent', 'user-manager'); ?></th>
						<th><?php esc_html_e('Error', 'user-manager'); ?></th>
						<th><?php esc_html_e('Date', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $entry) : ?>
						<?php
						$user_id = isset($entry['user_id']) ? (int) $entry['user_id'] : 0;
						$user    = $user_id ? get_user_by('ID', $user_id) : null;
						$extra   = isset($entry['extra']) && is_array($entry['extra']) ? $entry['extra'] : [];

						$target_email = '';
						if (!empty($extra['user_email'])) {
							$target_email = (string) $extra['user_email'];
						} elseif (!empty($extra['attempted_email'])) {
							$target_email = (string) $extra['attempted_email'];
						} elseif ($user && $user->user_email) {
							$target_email = $user->user_email;
						}
						?>
						<tr>
							<td>
								<?php if ($user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>">
										<?php echo esc_html($user->display_name ?: $user->user_email ?: $user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('N/A', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($target_email ?: '—'); ?></td>
							<td><?php echo esc_html($entry['action']); ?></td>
							<td><?php echo !empty($extra['bulk']) ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager'); ?></td>
							<td><?php echo !empty($extra['email_sent']) ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager'); ?></td>
							<td><?php echo !empty($extra['error']) ? esc_html($extra['error']) : '—'; ?></td>
							<td><?php echo esc_html($entry['created_at'] ?? ''); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	public static function export_coupons_assigned_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}
		
		$coupons_query = new WP_Query([
			'post_type' => 'shop_coupon',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'DESC',
			'fields' => 'ids',
		]);
		
		$csv_data = [];
		
		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}
			
			$emails = [];
			
			// Get emails from customer_email meta
			$customer_email = get_post_meta($coupon_id, 'customer_email', true);
			if (!empty($customer_email)) {
				if (is_array($customer_email)) {
					$emails = array_merge($emails, $customer_email);
				} else {
					$emails = array_merge($emails, array_map('trim', explode(',', $customer_email)));
				}
			}
			
			// Get emails from WooCommerce coupon email restrictions
			if (method_exists($coupon, 'get_email_restrictions')) {
				$restrictions = $coupon->get_email_restrictions('edit');
				if (!empty($restrictions) && is_array($restrictions)) {
					$emails = array_merge($emails, $restrictions);
				}
			}
			
			// Get email from User Manager meta
			$um_email = get_post_meta($coupon_id, '_um_user_coupon_user_email', true);
			if (!empty($um_email)) {
				$emails[] = $um_email;
			}
			
			// Remove duplicates and empty values
			$emails = array_unique(array_filter(array_map('trim', $emails)));
			
			if (!empty($emails)) {
				$expiry_date = $coupon->get_date_expires() ? $coupon->get_date_expires()->date('Y-m-d H:i:s') : '';
				foreach ($emails as $email) {
					$csv_data[] = [
						$coupon_id,
						$coupon->get_code(),
						$coupon->get_amount(),
						$coupon->get_discount_type(),
						$coupon->get_usage_count(),
						$coupon->get_usage_limit() ?: 'Unlimited',
						$expiry_date ?: 'No expiration',
						$email,
					];
				}
			}
		}
		
		self::export_csv('coupons-assigned-to-emails', [
			'Coupon ID', 'Coupon Code', 'Amount', 'Discount Type', 'Usage Count', 'Usage Limit', 'Expiry Date', 'Email'
		], $csv_data);
	}

	/**
	 * Export coupons with no expiration date to CSV.
	 */
	public static function export_coupons_no_expiration_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$coupons_query = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'any', // include all statuses
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		]);

		$rows = [];

		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			if ($coupon->get_date_expires()) {
				continue; // Only coupons with no expiration.
			}

			$rows[] = [
				$coupon_id,
				$coupon->get_code(),
				$coupon->get_amount(),
				$coupon->get_discount_type(),
				$coupon->get_usage_count(),
				$coupon->get_usage_limit() ?: 'Unlimited',
			];
		}

		self::export_csv('coupons-no-expiration', [
			'Coupon ID',
			'Coupon Code',
			'Amount',
			'Discount Type',
			'Usage Count',
			'Usage Limit',
		], $rows);
	}

	/**
	 * Export coupons with free shipping to CSV.
	 */
	public static function export_coupons_free_shipping_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$coupons_query = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		]);

		$rows = [];

		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			if (!$coupon->get_free_shipping('edit')) {
				continue;
			}

			$expiry_date = $coupon->get_date_expires() ? $coupon->get_date_expires()->date('Y-m-d H:i:s') : '';

			$rows[] = [
				$coupon_id,
				$coupon->get_code(),
				$coupon->get_amount(),
				$coupon->get_discount_type(),
				$coupon->get_usage_count(),
				$coupon->get_usage_limit() ?: 'Unlimited',
				$expiry_date ?: 'No expiration',
			];
		}

		self::export_csv('coupons-with-free-shipping', [
			'Coupon ID',
			'Coupon Code',
			'Amount',
			'Discount Type',
			'Usage Count',
			'Usage Limit',
			'Expiry Date',
		], $rows);
	}

	/**
	 * Export coupons that can still be used (unused / not fully consumed) to CSV.
	 *
	 * Criteria:
	 * - Published coupons.
	 * - Expiration date has not been reached or has no expiration.
	 * - Global usage limit has not been reached or has no limit.
	 */
	public static function export_coupons_unused_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$coupons_query = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		]);

		$rows   = [];
		$now_ts = current_time('timestamp');

		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			// Skip expired coupons.
			$date_expires = $coupon->get_date_expires();
			if ($date_expires && $date_expires->getTimestamp() < $now_ts) {
				continue;
			}

			$usage_count          = (int) $coupon->get_usage_count();
			$usage_limit          = $coupon->get_usage_limit();
			$usage_limit_per_user = $coupon->get_usage_limit_per_user();

			// Skip coupons where the global usage limit has been reached.
			if ($usage_limit && $usage_count >= $usage_limit) {
				continue;
			}

			// Collect all allowed emails (customer_email meta, email restrictions, and UM meta).
			$emails = [];
			$customer_email = get_post_meta($coupon_id, 'customer_email', true);
			if (!empty($customer_email)) {
				if (is_array($customer_email)) {
					$emails = array_merge($emails, $customer_email);
				} else {
					$emails = array_merge($emails, array_map('trim', explode(',', $customer_email)));
				}
			}
			if (method_exists($coupon, 'get_email_restrictions')) {
				$restrictions = $coupon->get_email_restrictions('edit');
				if (!empty($restrictions) && is_array($restrictions)) {
					$emails = array_merge($emails, $restrictions);
				}
			}
			$um_email = get_post_meta($coupon_id, '_um_user_coupon_user_email', true);
			if (!empty($um_email)) {
				$emails[] = $um_email;
			}
			$emails = array_unique(array_filter(array_map('trim', $emails)));
			$email_list = implode(', ', $emails);

			$rows[] = [
				$coupon_id,
				$coupon->get_code(),
				$coupon->get_amount(),
				$coupon->get_discount_type(),
				$usage_count,
				$usage_limit ?: 'Unlimited',
				$usage_limit_per_user ?: 'Unlimited',
				$coupon->get_date_created() ? $coupon->get_date_created()->date('Y-m-d H:i:s') : '',
				$date_expires ? $date_expires->date('Y-m-d H:i:s') : 'No expiration',
				$coupon->get_minimum_amount() ?: '',
				$coupon->get_maximum_amount() ?: '',
				$coupon->get_individual_use('edit') ? 'yes' : 'no',
				$coupon->get_free_shipping('edit') ? 'yes' : 'no',
				$email_list,
			];
		}

		self::export_csv('coupons-unused', [
			'Coupon ID',
			'Coupon Code',
			'Amount',
			'Discount Type',
			'Usage Count',
			'Usage Limit',
			'Usage Limit Per User',
			'Date Created',
			'Expiry Date',
			'Minimum Spend',
			'Maximum Spend',
			'Individual Use Only',
			'Free Shipping',
			'Allowed Emails',
		], $rows);
	}

	/**
	 * Export "Coupon Audit" report to CSV.
	 */
	public static function export_coupon_audit_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$rows = self::get_coupon_audit_data(true);

		self::export_csv(
			'coupon-audit',
			[
				'Coupon Code',
				'Amount',
				'Discount Type',
				'Status',
				'Usage',
				'Usage Limit Per User',
				'Date Created',
				'Expiry Date',
				'Minimum Spend',
				'Maximum Spend',
				'Individual Use Only',
				'Free Shipping',
				'Allowed Email(s)',
				'Total Coupons with Same Allowed Email',
				'Line by Line List of All Coupon Codes with Same Allowed Email',
				'Duplicate Codes with Similar Prefixes',
			],
			$rows
		);
	}

	/**
	 * Export coupons with remaining balances to CSV.
	 *
	 * This report approximates remainder coupons using the configured generated prefix and
	 * source-code string matching from the Fixed Cart Coupon Remaining Balances feature.
	 */
	public static function export_coupons_remaining_balances_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$settings = User_Manager_Core::get_settings();
		$generated_prefix = isset($settings['coupon_remainder_generated_prefix']) && $settings['coupon_remainder_generated_prefix'] !== ''
			? trim((string) $settings['coupon_remainder_generated_prefix'])
			: 'remaining-balance-';

		$coupons_query = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		]);

		$rows = [];

		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			if ($coupon->get_discount_type() !== 'fixed_cart') {
				continue;
			}

			$code = $coupon->get_code();

			// Heuristic: remainder coupons are generated with the configured prefix.
			if ($generated_prefix && stripos($code, $generated_prefix) !== 0) {
				continue;
			}

			// Only include remainder coupons that have never been used.
			if ($coupon->get_usage_count() > 0) {
				continue;
			}

			$remaining_amount = $coupon->get_amount();
			$expiry_date      = $coupon->get_date_expires() ? $coupon->get_date_expires()->date('Y-m-d H:i:s') : '';
			$created_at       = get_post_field('post_date', $coupon_id);

			// Collect any associated email addresses for this coupon, similar to the
			// "Coupons with Email Addresses" report.
			$emails = [];

			// Emails from customer_email meta.
			$customer_email = get_post_meta($coupon_id, 'customer_email', true);
			if (!empty($customer_email)) {
				if (is_array($customer_email)) {
					$emails = array_merge($emails, $customer_email);
				} else {
					$emails = array_merge($emails, array_map('trim', explode(',', $customer_email)));
				}
			}

			// Emails from WooCommerce coupon email restrictions.
			if (method_exists($coupon, 'get_email_restrictions')) {
				$restrictions = $coupon->get_email_restrictions('edit');
				if (!empty($restrictions) && is_array($restrictions)) {
					$emails = array_merge($emails, $restrictions);
				}
			}

			// Email from User Manager meta.
			$um_email = get_post_meta($coupon_id, '_um_user_coupon_user_email', true);
			if (!empty($um_email)) {
				$emails[] = $um_email;
			}

			// Normalize, dedupe, and flatten emails to a comma-separated list.
			$emails = array_unique(array_filter(array_map('trim', $emails)));
			$email_list = implode(', ', $emails);

			$rows[] = [
				$coupon_id,
				$code,
				$remaining_amount,
				$coupon->get_usage_count(),
				$coupon->get_usage_limit() ?: 'Unlimited',
				$expiry_date ?: 'No expiration',
				$created_at ?: '',
				$email_list,
			];
		}

		self::export_csv('coupons-remaining-balances', [
			'Coupon ID',
			'Coupon Code',
			'Remaining Amount',
			'Usage Count',
			'Usage Limit',
			'Expiry Date',
			'Created Date',
			'Email',
		], $rows);
	}

	/**
	 * Export "User Password Changes" report to CSV.
	 */
	public static function export_user_password_changes_csv(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'um_user_activity';
		$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;

		$rows = [];

		if ($table_exists) {
			$query = $wpdb->prepare(
				"SELECT h.id, h.user_id, h.action, h.url, h.ip_address, h.user_agent, h.created_at,
				        u.user_login, u.user_email, u.display_name
				   FROM {$table} h
				   LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
				  WHERE h.action = %s
				  ORDER BY h.created_at DESC",
				'Changed Password'
			);

			$results = $wpdb->get_results($query);
			foreach ($results as $row) {
				$rows[] = [
					$row->user_id ? (int) $row->user_id : '',
					$row->user_login ?: '',
					$row->user_email ?: '',
					$row->display_name ?: ($row->user_email ?: ''),
					$row->url ?: '',
					$row->ip_address ?: '',
					$row->user_agent ?: '',
					$row->created_at ?: '',
				];
			}
		}

		self::export_csv(
			'user-password-changes',
			[
				'User ID',
				'Username',
				'Email',
				'Display Name',
				'URL',
				'IP Address',
				'User Agent',
				'Date',
			],
			$rows
		);
	}

	/**
	 * Export "Order Notes" report to CSV.
	 */
	public static function export_order_notes_csv(): void {
		global $wpdb;

		$orders_table   = $wpdb->prefix . 'posts';
		$comments_table = $wpdb->prefix . 'comments';
		$meta_table     = $wpdb->prefix . 'commentmeta';

		$rows = [];

		// Fetch all order notes for shop_order posts.
		$query = "
			SELECT c.comment_ID, c.comment_post_ID, c.comment_author, c.comment_content, c.comment_date_gmt,
			       p.post_date, p.post_status,
			       MAX(CASE WHEN cm.meta_key = '_wc_order_note_type' THEN cm.meta_value END) as note_type
			  FROM {$comments_table} c
			  INNER JOIN {$orders_table} p ON c.comment_post_ID = p.ID
			  LEFT JOIN {$meta_table} cm ON c.comment_ID = cm.comment_id
			 WHERE c.comment_type = 'order_note'
			   AND p.post_type = 'shop_order'
			 GROUP BY c.comment_ID
			 ORDER BY c.comment_date_gmt DESC
		";

		$results = $wpdb->get_results($query);

		foreach ($results as $row) {
			$note_type = $row->note_type ?: 'internal';

			$rows[] = [
				(int) $row->comment_post_ID,
				(string) $row->comment_author,
				$note_type,
				wp_strip_all_tags((string) $row->comment_content),
				(string) $row->comment_date_gmt,
				(string) $row->post_status,
			];
		}

		self::export_csv(
			'order-notes',
			[
				'Order ID',
				'Author',
				'Note Type',
				'Content',
				'Date (GMT)',
				'Order Status',
			],
			$rows
		);
	}

	/**
	 * Export "Orders Processing by Number of Days" report to CSV.
	 */
	public static function export_orders_processing_days_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$data = self::get_orders_processing_days_data(true);

		self::export_csv(
			'orders-processing-by-days',
			[
				'Order ID',
				'Order Date',
				'Billing Email',
				'Status',
				'Days in Processing',
			],
			$data
		);
	}

	/**
	 * Export "Order Total Shipments by Day" report to CSV.
	 */
	public static function export_orders_shipments_by_day_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$data = self::get_orders_shipments_aggregated_data('day', true);

		self::export_csv(
			'orders-shipments-by-day',
			[
				'Date',
				'Completed Orders',
				'Total Order Amount',
			],
			$data
		);
	}

	/**
	 * Export "Order Total Shipments by Week" report to CSV.
	 */
	public static function export_orders_shipments_by_week_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$data = self::get_orders_shipments_aggregated_data('week', true);

		self::export_csv(
			'orders-shipments-by-week',
			[
				'Week',
				'Completed Orders',
				'Total Order Amount',
			],
			$data
		);
	}

	/**
	 * Export "Order Total Shipments by Month" report to CSV.
	 */
	public static function export_orders_shipments_by_month_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$data = self::get_orders_shipments_aggregated_data('month', true);

		self::export_csv(
			'orders-shipments-by-month',
			[
				'Month',
				'Completed Orders',
				'Total Order Amount',
			],
			$data
		);
	}

	/**
	 * Export "User Data" report to CSV.
	 */
	public static function export_user_data_csv(): void {
		$rows = self::get_user_data_rows(true);

		self::export_csv(
			'user-data',
			[
				'User ID',
				'Username',
				'Email',
				'First Name',
				'Last Name',
				'Display Name',
				'Roles',
				'Billing First Name',
				'Billing Last Name',
				'Billing Company',
				'Billing Address 1',
				'Billing Address 2',
				'Billing City',
				'Billing State',
				'Billing Postcode',
				'Billing Country',
				'Billing Phone',
				'Billing Email',
				'Shipping First Name',
				'Shipping Last Name',
				'Shipping Company',
				'Shipping Address 1',
				'Shipping Address 2',
				'Shipping City',
				'Shipping State',
				'Shipping Postcode',
				'Shipping Country',
			],
			$rows
		);
	}

	/**
	 * Export "Order Refunds" report to CSV.
	 */
	public static function export_orders_with_refunds_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$data = self::get_orders_with_refunds_data(true);

		self::export_csv(
			'orders-with-refunds',
			[
				'Order ID',
				'Order Date',
				'Billing Email',
				'Order Total',
				'Refunded Amount',
				'Refund Type',
				'Status',
			],
			$data
		);
	}

	/**
	 * Export Sales vs Coupon Usage report to CSV.
	 */
	public static function export_sales_vs_coupons_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}
		
		$date_from = isset($_GET['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_from'])
			? sanitize_text_field(wp_unslash($_GET['date_from']))
			: null;

		$date_to = isset($_GET['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_to'])
			? sanitize_text_field(wp_unslash($_GET['date_to']))
			: null;

		$data = self::get_sales_vs_coupons_data(true, $date_from, $date_to);

		self::export_csv(
			'sales-vs-coupon-usage',
			[
				'Order ID',
				'Order Date',
				'Ship To Name',
				'Ship To State',
				'Ship To Country',
				'Total Payment Collected',
				'Total Coupon(s) Value Collected',
				'Coupon Code(s) Used',
			],
			$data
		);
	}

	/**
	 * Export "Orders with $0 Total" report to CSV.
	 */
	public static function export_orders_zero_total_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$rows = self::get_orders_zero_total_data(true);

		self::export_csv(
			'orders-with-zero-total',
			[
				'Order ID',
				'Order Date',
				'Billing Email',
				'Status',
				'Coupons Applied',
				'Total Coupons Value',
			],
			$rows
		);
	}

	/**
	 * Export "Orders with Free Shipping" report to CSV.
	 */
	public static function export_orders_free_shipping_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$rows = self::get_orders_free_shipping_data(true);

		self::export_csv(
			'orders-with-free-shipping',
			[
				'Order ID',
				'Order Date',
				'Billing Email',
				'Status',
				'Shipping Method',
				'Shipping Total',
				'Shipping Tax',
			],
			$rows
		);
	}

	/**
	 * Export "User Total Sales" report to CSV.
	 */
	public static function export_user_total_sales_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$rows = self::get_user_total_sales_data(true);

		self::export_csv(
			'user-total-sales',
			[
				'User ID',
				'Username',
				'Email',
				'First Name',
				'Last Name',
				'Total Sales',
				'Total Orders',
				'Total Lines Purchased',
			],
			$rows
		);
	}

	/**
	 * Export "Users Who Used Coupons" report to CSV.
	 */
	public static function export_users_who_used_coupons_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$rows = self::get_users_who_used_coupons_data(true);

		self::export_csv(
			'users-who-used-coupons',
			[
				'Email',
				'Total Coupons Used',
				'Total Value of Coupons Used',
				'List of Coupons Used',
				'Total Paid After Coupons Applied',
			],
			$rows
		);
	}

	/**
	 * Export "Product Purchases" report to CSV.
	 */
	public static function export_product_purchases_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$rows = self::get_product_purchases_data(true);

		self::export_csv(
			'product-purchases',
			[
				'Product ID',
				'Product Name',
				'SKU',
				'Current Price',
				'Total Sales',
				'Total Qty Sold',
				'Total Orders',
			],
			$rows
		);
	}

	/**
	 * Export "Product Category Purchases" report to CSV.
	 */
	public static function export_product_category_purchases_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$rows = self::get_product_category_purchases_data(true);

		self::export_csv(
			'product-category-purchases',
			[
				'Category ID',
				'Category Name',
				'Slug',
				'Total Sales',
				'Total Qty Sold',
				'Total Orders',
			],
			$rows
		);
	}

	/**
	 * Export "Product Tag Purchases" report to CSV.
	 */
	public static function export_product_tag_purchases_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$rows = self::get_product_tag_purchases_data(true);

		self::export_csv(
			'product-tag-purchases',
			[
				'Tag ID',
				'Tag Name',
				'Slug',
				'Total Sales',
				'Total Qty Sold',
				'Total Orders',
			],
			$rows
		);
	}

	/**
	 * Export "Page Not Found 404 Errors" report to CSV.
	 */
	public static function export_404_errors_csv(): void {
		$rows = self::get_404_errors_data(true);

		self::export_csv(
			'page-not-found-404-errors',
			[
				'URL',
				'Last Seen',
				'Hits',
				'Last User ID',
			],
			$rows
		);
	}

	/**
	 * Export "Search Queries" report to CSV.
	 */
	public static function export_search_queries_csv(): void {
		$rows = self::get_search_queries_data(true);

		self::export_csv(
			'search-queries',
			[
				'Search Term',
				'Post Type',
				'Total Searches',
				'Last Searched',
				'Last User ID',
				'Example URL',
			],
			$rows
		);
	}

	/**
	 * Export "Page Views" report to CSV.
	 */
	public static function export_page_views_csv(): void {
		$rows = self::get_page_views_data(true);

		self::export_csv(
			'page-views',
			[
				'Page ID',
				'Title',
				'Slug',
				'Permalink',
				'Total Views',
				'Last Viewed',
				'Last User ID',
			],
			$rows
		);
	}

	/**
	 * Export "Page Category Archives Views" report to CSV.
	 */
	public static function export_page_category_views_csv(): void {
		$rows = self::get_page_category_views_data(true);

		self::export_csv(
			'page-category-views',
			[
				'Term ID',
				'Name',
				'Slug',
				'Permalink',
				'Total Views',
				'Last Viewed',
				'Last User ID',
			],
			$rows
		);
	}

	/**
	 * Export "Post Views" report to CSV.
	 */
	public static function export_post_views_csv(): void {
		$rows = self::get_post_views_data(true);

		self::export_csv(
			'post-views',
			[
				'Post ID',
				'Title',
				'Slug',
				'Permalink',
				'Total Views',
				'Last Viewed',
				'Last User ID',
			],
			$rows
		);
	}

	/**
	 * Export "Post Category Archives Views" report to CSV.
	 */
	public static function export_post_category_views_csv(): void {
		$rows = self::get_post_category_views_data(true);

		self::export_csv(
			'post-category-views',
			[
				'Term ID',
				'Name',
				'Slug',
				'Permalink',
				'Total Views',
				'Last Viewed',
				'Last User ID',
			],
			$rows
		);
	}

	/**
	 * Export "Post Tag Archives Views" report to CSV.
	 */
	public static function export_post_tag_views_csv(): void {
		$rows = self::get_post_tag_views_data(true);

		self::export_csv(
			'post-tag-views',
			[
				'Term ID',
				'Name',
				'Slug',
				'Permalink',
				'Total Views',
				'Last Viewed',
				'Last User ID',
			],
			$rows
		);
	}

	/**
	 * Export "Product Views" report to CSV.
	 */
	public static function export_product_views_csv(): void {
		$rows = self::get_product_views_data(true);

		self::export_csv(
			'product-views',
			[
				'Product ID',
				'Title',
				'Slug',
				'Permalink',
				'Total Views',
				'Last Viewed',
				'Last User ID',
			],
			$rows
		);
	}

	/**
	 * Export "Product Category Archives Views" report to CSV.
	 */
	public static function export_product_category_views_csv(): void {
		$rows = self::get_product_category_views_data(true);

		self::export_csv(
			'product-category-views',
			[
				'Term ID',
				'Name',
				'Slug',
				'Permalink',
				'Total Views',
				'Last Viewed',
				'Last User ID',
			],
			$rows
		);
	}

	/**
	 * Export "Product Tag Archives Views" report to CSV.
	 */
	public static function export_product_tag_views_csv(): void {
		$rows = self::get_product_tag_views_data(true);

		self::export_csv(
			'product-tag-views',
			[
				'Term ID',
				'Name',
				'Slug',
				'Permalink',
				'Total Views',
				'Last Viewed',
				'Last User ID',
			],
			$rows
		);
	}

	/**
	 * Export "Post Meta Field Names (Unique List)" report to CSV.
	 */
	public static function export_post_meta_field_names_csv(): void {
		global $wpdb;
		$meta_table = $wpdb->postmeta;
		$keys       = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$meta_table} ORDER BY meta_key ASC");
		$keys       = is_array($keys) ? $keys : [];
		$rows       = array_map(function ($meta_key) {
			return [ $meta_key ];
		}, $keys);
		self::export_csv('post-meta-field-names', [ __('Meta Key', 'user-manager') ], $rows);
	}

	/**
	 * Export "Order Payment Methods" report to CSV.
	 */
	public static function export_order_payment_methods_csv(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}

		$rows = self::get_order_payment_methods_data(true);

		self::export_csv(
			'order-payment-methods',
			[
				'Order ID',
				'Order Date',
				'Billing Email',
				'Status',
				'Payment Method',
				'Payment Source',
				'Order Total',
			],
			$rows
		);
	}

	private static function render_coupons_assigned_report(): void {
		global $wpdb;
		$per_page = 50;
		$paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset = ($paged - 1) * $per_page;
		
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}
		
		$entries = [];
		
		// Get all coupons first
		$coupons_query = new WP_Query([
			'post_type' => 'shop_coupon',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'DESC',
			'fields' => 'ids',
		]);
		
		// Filter coupons that have email restrictions
		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}
			
			$emails = [];
			
			// Get emails from customer_email meta
			$customer_email = get_post_meta($coupon_id, 'customer_email', true);
			if (!empty($customer_email)) {
				if (is_array($customer_email)) {
					$emails = array_merge($emails, $customer_email);
				} else {
					$emails = array_merge($emails, array_map('trim', explode(',', $customer_email)));
				}
			}
			
			// Get emails from WooCommerce coupon email restrictions
			if (method_exists($coupon, 'get_email_restrictions')) {
				$restrictions = $coupon->get_email_restrictions('edit');
				if (!empty($restrictions) && is_array($restrictions)) {
					$emails = array_merge($emails, $restrictions);
				}
			}
			
			// Get email from User Manager meta
			$um_email = get_post_meta($coupon_id, '_um_user_coupon_user_email', true);
			if (!empty($um_email)) {
				$emails[] = $um_email;
			}
			
			// Remove duplicates and empty values
			$emails = array_unique(array_filter(array_map('trim', $emails)));
			
			if (!empty($emails)) {
				$entries[] = [
					'coupon_id' => $coupon_id,
					'coupon_code' => $coupon->get_code(),
					'amount' => $coupon->get_amount(),
					'discount_type' => $coupon->get_discount_type(),
					'usage_count' => $coupon->get_usage_count(),
					'usage_limit' => $coupon->get_usage_limit(),
					'expiry_date' => $coupon->get_date_expires() ? $coupon->get_date_expires()->date('Y-m-d H:i:s') : '',
					'emails' => $emails,
				];
			}
		}
		
		$total = count($entries);
		
		// Apply pagination
		if ($total > $per_page) {
			$entries = array_slice($entries, $offset, $per_page);
		}
		
		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'coupons-assigned', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));
		
		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>
		
		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No coupons with email restrictions found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Coupon Code', 'user-manager'); ?></th>
						<th><?php esc_html_e('Amount', 'user-manager'); ?></th>
						<th><?php esc_html_e('Discount Type', 'user-manager'); ?></th>
						<th><?php esc_html_e('Usage', 'user-manager'); ?></th>
						<th><?php esc_html_e('Expiry Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Assigned Emails', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $e) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . $e['coupon_id'] . '&action=edit')); ?>">
									<strong><?php echo esc_html(strtoupper($e['coupon_code'])); ?></strong>
								</a>
							</td>
							<td><?php echo function_exists('wc_price') ? wc_price($e['amount']) : esc_html($e['amount']); ?></td>
							<td><?php echo esc_html(ucwords(str_replace('_', ' ', $e['discount_type']))); ?></td>
							<td>
								<?php echo esc_html($e['usage_count']); ?>
								<?php if ($e['usage_limit']) : ?>
									/ <?php echo esc_html($e['usage_limit']); ?>
								<?php else : ?>
									/ <?php esc_html_e('Unlimited', 'user-manager'); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($e['expiry_date'] ? date_i18n(get_option('date_format'), strtotime($e['expiry_date'])) : '—'); ?></td>
							<td><?php echo esc_html(implode(', ', $e['emails'])); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Coupons with No Expiration" report.
	 */
	private static function render_coupons_no_expiration_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$coupons_query = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		]);

		$entries = [];
		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			if ($coupon->get_date_expires()) {
				continue;
			}

			$entries[] = [
				'coupon_id'     => $coupon_id,
				'coupon_code'   => $coupon->get_code(),
				'amount'        => $coupon->get_amount(),
				'discount_type' => $coupon->get_discount_type(),
				'usage_count'   => $coupon->get_usage_count(),
				'usage_limit'   => $coupon->get_usage_limit(),
			];
		}

		$total = count($entries);
		if ($total > $per_page) {
			$entries = array_slice($entries, $offset, $per_page);
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'coupons-no-expiration', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No coupons without expiration found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Coupon Code', 'user-manager'); ?></th>
						<th><?php esc_html_e('Amount', 'user-manager'); ?></th>
						<th><?php esc_html_e('Discount Type', 'user-manager'); ?></th>
						<th><?php esc_html_e('Usage', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $e) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . $e['coupon_id'] . '&action=edit')); ?>">
									<strong><?php echo esc_html(strtoupper($e['coupon_code'])); ?></strong>
								</a>
							</td>
							<td><?php echo function_exists('wc_price') ? wc_price($e['amount']) : esc_html($e['amount']); ?></td>
							<td><?php echo esc_html(ucwords(str_replace('_', ' ', $e['discount_type']))); ?></td>
							<td>
								<?php echo esc_html($e['usage_count']); ?>
								<?php if ($e['usage_limit']) : ?>
									/ <?php echo esc_html($e['usage_limit']); ?>
								<?php else : ?>
									/ <?php esc_html_e('Unlimited', 'user-manager'); ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Coupons with Free Shipping" report.
	 */
	private static function render_coupons_free_shipping_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$coupons_query = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		]);

		$entries = [];
		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			if (!$coupon->get_free_shipping('edit')) {
				continue;
			}

			$entries[] = [
				'coupon_id'     => $coupon_id,
				'coupon_code'   => $coupon->get_code(),
				'amount'        => $coupon->get_amount(),
				'discount_type' => $coupon->get_discount_type(),
				'usage_count'   => $coupon->get_usage_count(),
				'usage_limit'   => $coupon->get_usage_limit(),
				'expiry_date'   => $coupon->get_date_expires() ? $coupon->get_date_expires()->date('Y-m-d H:i:s') : '',
			];
		}

		$total = count($entries);
		if ($total > $per_page) {
			$entries = array_slice($entries, $offset, $per_page);
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'coupons-free-shipping', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No coupons with free shipping found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Coupon Code', 'user-manager'); ?></th>
						<th><?php esc_html_e('Amount', 'user-manager'); ?></th>
						<th><?php esc_html_e('Discount Type', 'user-manager'); ?></th>
						<th><?php esc_html_e('Usage', 'user-manager'); ?></th>
						<th><?php esc_html_e('Expiry Date', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $e) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . $e['coupon_id'] . '&action=edit')); ?>">
									<strong><?php echo esc_html(strtoupper($e['coupon_code'])); ?></strong>
								</a>
							</td>
							<td><?php echo function_exists('wc_price') ? wc_price($e['amount']) : esc_html($e['amount']); ?></td>
							<td><?php echo esc_html(ucwords(str_replace('_', ' ', $e['discount_type']))); ?></td>
							<td>
								<?php echo esc_html($e['usage_count']); ?>
								<?php if ($e['usage_limit']) : ?>
									/ <?php echo esc_html($e['usage_limit']); ?>
								<?php else : ?>
									/ <?php esc_html_e('Unlimited', 'user-manager'); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($e['expiry_date'] ? date_i18n(get_option('date_format'), strtotime($e['expiry_date'])) : '—'); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Coupons Unused" report.
	 *
	 * Shows all published coupons that:
	 * - Have not expired (or have no expiration), and
	 * - Have not reached their global usage limit (or have no limit).
	 */
	private static function render_coupons_unused_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$coupons_query = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		]);

		$entries = [];
		$now_ts  = current_time('timestamp');

		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			// Expiration criteria.
			$date_expires = $coupon->get_date_expires();
			if ($date_expires && $date_expires->getTimestamp() < $now_ts) {
				continue;
			}

			$usage_count          = (int) $coupon->get_usage_count();
			$usage_limit          = $coupon->get_usage_limit();
			$usage_limit_per_user = $coupon->get_usage_limit_per_user();

			// Global usage limit criteria.
			if ($usage_limit && $usage_count >= $usage_limit) {
				continue;
			}

			// Collect all allowed emails (customer_email meta, email restrictions, and UM meta).
			$emails = [];
			$customer_email = get_post_meta($coupon_id, 'customer_email', true);
			if (!empty($customer_email)) {
				if (is_array($customer_email)) {
					$emails = array_merge($emails, $customer_email);
				} else {
					$emails = array_merge($emails, array_map('trim', explode(',', $customer_email)));
				}
			}
			if (method_exists($coupon, 'get_email_restrictions')) {
				$restrictions = $coupon->get_email_restrictions('edit');
				if (!empty($restrictions) && is_array($restrictions)) {
					$emails = array_merge($emails, $restrictions);
				}
			}
			$um_email = get_post_meta($coupon_id, '_um_user_coupon_user_email', true);
			if (!empty($um_email)) {
				$emails[] = $um_email;
			}
			$emails = array_unique(array_filter(array_map('trim', $emails)));
			$email_list = implode(', ', $emails);

			$entries[] = [
				'coupon_id'           => $coupon_id,
				'coupon_code'         => $coupon->get_code(),
				'amount'              => $coupon->get_amount(),
				'discount_type'       => $coupon->get_discount_type(),
				'usage_count'         => $usage_count,
				'usage_limit'         => $usage_limit,
				'usage_limit_per_user'=> $usage_limit_per_user,
				'created_at'          => $coupon->get_date_created() ? $coupon->get_date_created()->date('Y-m-d H:i:s') : '',
				'expiry_date'         => $date_expires ? $date_expires->date('Y-m-d H:i:s') : '',
				'min_spend'           => $coupon->get_minimum_amount() ?: '',
				'max_spend'           => $coupon->get_maximum_amount() ?: '',
				'individual_use'      => $coupon->get_individual_use('edit'),
				'free_shipping'       => $coupon->get_free_shipping('edit'),
				'emails'              => $email_list,
			];
		}

		$total = count($entries);
		if ($total > $per_page) {
			$entries = array_slice($entries, $offset, $per_page);
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'coupons-unused', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No unused coupons found that meet the criteria.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Coupon Code', 'user-manager'); ?></th>
						<th><?php esc_html_e('Amount', 'user-manager'); ?></th>
						<th><?php esc_html_e('Discount Type', 'user-manager'); ?></th>
						<th><?php esc_html_e('Status', 'user-manager'); ?></th>
						<th><?php esc_html_e('Usage', 'user-manager'); ?></th>
						<th><?php esc_html_e('Usage Limit Per User', 'user-manager'); ?></th>
						<th><?php esc_html_e('Date Created', 'user-manager'); ?></th>
						<th><?php esc_html_e('Expiry Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Minimum Spend', 'user-manager'); ?></th>
						<th><?php esc_html_e('Maximum Spend', 'user-manager'); ?></th>
						<th><?php esc_html_e('Individual Use Only', 'user-manager'); ?></th>
						<th><?php esc_html_e('Free Shipping', 'user-manager'); ?></th>
						<th><?php esc_html_e('Allowed Emails', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $e) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . $e['coupon_id'] . '&action=edit')); ?>">
									<strong><?php echo esc_html(strtoupper($e['coupon_code'])); ?></strong>
								</a>
							</td>
							<td><?php echo function_exists('wc_price') ? wc_price($e['amount']) : esc_html($e['amount']); ?></td>
							<td><?php echo esc_html(ucwords(str_replace('_', ' ', $e['discount_type']))); ?></td>
							<td>
								<?php
								$limit_label = $e['usage_limit'] ? (string) $e['usage_limit'] : __('Unlimited', 'user-manager');
								echo esc_html($e['usage_count'] . ' / ' . $limit_label);
								?>
							</td>
							<td>
								<?php
								echo esc_html(
									$e['usage_limit_per_user']
										? (string) $e['usage_limit_per_user']
										: __('Unlimited', 'user-manager')
								);
								?>
							</td>
							<td>
								<?php
								if (!empty($e['created_at'])) {
									$created_ts = strtotime($e['created_at']);
									echo esc_html(
										$created_ts
											? date_i18n(
												get_option('date_format') . ' ' . get_option('time_format'),
												$created_ts
											)
											: $e['created_at']
									);
								} else {
									echo '—';
								}
								?>
							</td>
							<td>
								<?php
								if (!empty($e['expiry_date'])) {
									$exp_ts = strtotime($e['expiry_date']);
									echo esc_html(
										$exp_ts
											? date_i18n(get_option('date_format'), $exp_ts)
											: $e['expiry_date']
									);
								} else {
									esc_html_e('No expiration', 'user-manager');
								}
								?>
							</td>
							<td><?php echo $e['min_spend'] !== '' ? esc_html($e['min_spend']) : '—'; ?></td>
							<td><?php echo $e['max_spend'] !== '' ? esc_html($e['max_spend']) : '—'; ?></td>
							<td><?php echo $e['individual_use'] ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager'); ?></td>
							<td><?php echo $e['free_shipping'] ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager'); ?></td>
							<td><?php echo $e['emails'] !== '' ? esc_html($e['emails']) : '—'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Coupon Audit" report.
	 */
	private static function render_coupon_audit_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$rows  = self::get_coupon_audit_data();
		$total = count($rows);

		if ($total > $per_page) {
			$rows = array_slice($rows, $offset, $per_page);
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'coupon-audit', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No coupons found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Coupon Code', 'user-manager'); ?></th>
						<th><?php esc_html_e('Amount', 'user-manager'); ?></th>
						<th><?php esc_html_e('Discount Type', 'user-manager'); ?></th>
						<th><?php esc_html_e('Status', 'user-manager'); ?></th>
						<th><?php esc_html_e('Usage', 'user-manager'); ?></th>
						<th><?php esc_html_e('Usage Limit Per User', 'user-manager'); ?></th>
						<th><?php esc_html_e('Date Created', 'user-manager'); ?></th>
						<th><?php esc_html_e('Expiry Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Minimum Spend', 'user-manager'); ?></th>
						<th><?php esc_html_e('Maximum Spend', 'user-manager'); ?></th>
						<th><?php esc_html_e('Individual Use Only', 'user-manager'); ?></th>
						<th><?php esc_html_e('Free Shipping', 'user-manager'); ?></th>
						<th><?php esc_html_e('Allowed Email(s)', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Coupons with Same Allowed Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Line by Line List of All Coupon Codes with Same Allowed Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Duplicate Codes with Similar Prefixes', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$usage_label = $row['usage_limit']
							? sprintf('%d / %d', $row['usage_count'], $row['usage_limit'])
							: sprintf('%d / %s', $row['usage_count'], esc_html__('Unlimited', 'user-manager'));

						$usage_limit_per_user = $row['usage_limit_per_user']
							? $row['usage_limit_per_user']
							: esc_html__('Unlimited', 'user-manager');

						$allowed_emails = !empty($row['allowed_emails']) ? implode(', ', $row['allowed_emails']) : '';
						$related_codes  = !empty($row['related_codes']) ? implode("\n", $row['related_codes']) : '';
						$duplicate_prefixes = !empty($row['related_prefixes']) ? implode("\n", $row['related_prefixes']) : '';
						?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . absint($row['coupon_id']) . '&action=edit')); ?>">
									<strong><?php echo esc_html(strtoupper($row['coupon_code'])); ?></strong>
								</a>
							</td>
							<td><?php echo function_exists('wc_price') ? wc_price($row['amount']) : esc_html($row['amount']); ?></td>
							<td><?php echo esc_html(ucwords(str_replace('_', ' ', $row['discount_type']))); ?></td>
							<td><?php echo esc_html($row['status']); ?></td>
							<td><?php echo esc_html($usage_label); ?></td>
							<td><?php echo esc_html($usage_limit_per_user); ?></td>
							<td>
								<?php
								if (!empty($row['created_at'])) {
									$created_ts = strtotime($row['created_at']);
									echo esc_html(
										$created_ts
											? date_i18n(get_option('date_format'), $created_ts)
											: '—'
									);
								} else {
									echo '—';
								}
								?>
							</td>
							<td>
								<?php
								if (!empty($row['expiry_date'])) {
									$exp_ts = strtotime($row['expiry_date']);
									echo esc_html(
										$exp_ts
											? date_i18n(get_option('date_format'), $exp_ts)
											: $row['expiry_date']
									);
								} else {
									esc_html_e('No expiration', 'user-manager');
								}
								?>
							</td>
							<td><?php echo $row['min_spend'] !== '' ? esc_html($row['min_spend']) : '—'; ?></td>
							<td><?php echo $row['max_spend'] !== '' ? esc_html($row['max_spend']) : '—'; ?></td>
							<td><?php echo $row['individual_use'] ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager'); ?></td>
							<td><?php echo $row['free_shipping'] ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager'); ?></td>
							<td><?php echo $allowed_emails !== '' ? esc_html($allowed_emails) : '—'; ?></td>
							<td><?php echo esc_html(number_format_i18n($row['related_count'])); ?></td>
							<td><?php echo $related_codes !== '' ? nl2br(esc_html($related_codes)) : '—'; ?></td>
							<td><?php echo $duplicate_prefixes !== '' ? nl2br(esc_html($duplicate_prefixes)) : '—'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Collect coupon audit rows for display or export.
	 *
	 * @param bool $for_csv Whether to shape results for CSV output.
	 *
	 * @return array
	 */
	private static function get_coupon_audit_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		global $wpdb;

		$coupons_query = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'any', // include all coupon statuses
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		]);

		$coupons       = [];
		$email_to_code = [];
		$coupon_map    = [];
		$coupon_raw_map = [];

		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			$allowed_emails = [];

			$customer_email = get_post_meta($coupon_id, 'customer_email', true);
			if (!empty($customer_email)) {
				if (is_array($customer_email)) {
					$allowed_emails = array_merge($allowed_emails, $customer_email);
				} else {
					$allowed_emails = array_merge($allowed_emails, array_map('trim', explode(',', $customer_email)));
				}
			}

			if (method_exists($coupon, 'get_email_restrictions')) {
				$restrictions = $coupon->get_email_restrictions('edit');
				if (!empty($restrictions) && is_array($restrictions)) {
					$allowed_emails = array_merge($allowed_emails, $restrictions);
				}
			}

			$um_email = get_post_meta($coupon_id, '_um_user_coupon_user_email', true);
			if (!empty($um_email)) {
				$allowed_emails[] = $um_email;
			}

			$allowed_emails = array_unique(array_filter(array_map('trim', $allowed_emails)));

			$code         = strtoupper($coupon->get_code());
			$date_created = $coupon->get_date_created();
			$date_expires = $coupon->get_date_expires();

			$coupon_entry = [
				'coupon_id'           => $coupon_id,
				'coupon_code'         => $code,
				'amount'              => $coupon->get_amount(),
				'discount_type'       => $coupon->get_discount_type(),
				'status'              => get_post_status($coupon_id) ?: '',
				'usage_count'         => (int) $coupon->get_usage_count(),
				'usage_limit'         => $coupon->get_usage_limit(),
				'usage_limit_per_user' => $coupon->get_usage_limit_per_user(),
				'created_at'          => $date_created ? $date_created->date('Y-m-d H:i:s') : '',
				'expiry_date'         => $date_expires ? $date_expires->date('Y-m-d H:i:s') : '',
				'min_spend'           => $coupon->get_minimum_amount() ?: '',
				'max_spend'           => $coupon->get_maximum_amount() ?: '',
				'individual_use'      => (bool) $coupon->get_individual_use('edit'),
				'free_shipping'       => (bool) $coupon->get_free_shipping('edit'),
				'allowed_emails'      => $allowed_emails,
			];

			$coupons[] = $coupon_entry;
			$coupon_map[$code] = &$coupons[array_key_last($coupons)];
			$coupon_raw_map[$code] = [
				'code'        => $code,
				'usage_count' => (int) $coupon->get_usage_count(),
			];

			foreach ($allowed_emails as $email) {
				if (!isset($email_to_code[$email])) {
					$email_to_code[$email] = [];
				}
				$email_to_code[$email][] = $code;
			}
		}

		// Find most recent order usage per coupon code (latest order wins).
		$last_use = [];
		if (!empty($coupons)) {
			$orders_table        = $wpdb->prefix . 'posts';
			$order_items_table   = $wpdb->prefix . 'woocommerce_order_items';
			$valid_order_status  = "'wc-completed','wc-processing','wc-on-hold','wc-pending','wc-refunded','wc-cancelled','wc-failed'";
			$order_usage_rows = $wpdb->get_results(
				"SELECT oi.order_item_name AS coupon_code, oi.order_id, p.post_date
				   FROM {$order_items_table} oi
				   INNER JOIN {$orders_table} p ON oi.order_id = p.ID
				  WHERE oi.order_item_type = 'coupon'
				    AND p.post_type = 'shop_order'
				    AND p.post_status IN ({$valid_order_status})
				  ORDER BY p.post_date DESC"
			);

			foreach ($order_usage_rows as $row) {
				$code = strtoupper($row->coupon_code);
				if (!isset($last_use[$code])) {
					$last_use[$code] = [
						'order_id'   => (int) $row->order_id,
						'order_date' => $row->post_date ?: '',
					];
				}
			}
		}

		foreach ($coupons as &$coupon_entry) {
			$related_codes = [];

			foreach ($coupon_entry['allowed_emails'] as $email) {
				if (isset($email_to_code[$email])) {
					foreach ($email_to_code[$email] as $code) {
						$related_codes[$code] = true;
					}
				}
			}

			$related_list = array_keys($related_codes);
			sort($related_list, SORT_NATURAL | SORT_FLAG_CASE);

			$formatted_related = [];
			foreach ($related_list as $code) {
				$details = $coupon_map[$code] ?? null;
				if (!$details) {
					$formatted_related[] = $code;
					continue;
				}

				$amount_str = is_numeric($details['amount'])
					? number_format((float) $details['amount'], 2, '.', '')
					: (string) $details['amount'];

				$created_text = $details['created_at'] ?: __('unknown date', 'user-manager');
				$expiry_text  = $details['expiry_date'] ?: __('No expiration', 'user-manager');

				$is_used = (int) $details['usage_count'] > 0;
				$status_label = $details['status'] !== '' ? $details['status'] : __('unknown status', 'user-manager');
				$last_order = $last_use[$code] ?? null;
				if ($is_used) {
					$order_bits = '';
					if ($last_order && $last_order['order_id']) {
						$order_bits = sprintf(
							/* translators: 1: order id, 2: order date */
							__('and was used on Order ID #%1$d on %2$s', 'user-manager'),
							(int) $last_order['order_id'],
							$last_order['order_date'] ?: __('unknown date', 'user-manager')
						);
					} else {
						$order_bits = __('and was used', 'user-manager');
					}

					$formatted_related[] = sprintf(
						/* translators: 1: code, 2: created date, 3: amount, 4: order info */
						__('%1$s (this used %2$s code was created on %3$s and had a value of %4$s %5$s)', 'user-manager'),
						$code,
						$status_label,
						$created_text,
						$amount_str,
						$order_bits
					);
				} else {
					$formatted_related[] = sprintf(
						/* translators: 1: code, 2: status, 3: created date, 4: amount, 5: expiry */
						__('%1$s (this unused %2$s code was created on %3$s and has a value of %4$s and expires on %5$s)', 'user-manager'),
						$code,
						$status_label,
						$created_text,
						$amount_str,
						$expiry_text
					);
				}
			}

			// Build prefix summary from raw related codes.
			$prefix_counts = [];
			foreach ($related_list as $code) {
				$prefix = $code;
				if (strpos($code, '-') !== false) {
					$prefix = substr($code, 0, strpos($code, '-') + 1); // include dash
				}
				if (!isset($prefix_counts[$prefix])) {
					$prefix_counts[$prefix] = ['total' => 0, 'used' => 0];
				}
				$prefix_counts[$prefix]['total']++;
				// Determine used based on usage_count > 0
				$code_details = $coupon_map[$code] ?? $coupon_raw_map[$code] ?? null;
				$usage_count  = $code_details ? (int) ($code_details['usage_count'] ?? 0) : 0;
				if ($usage_count > 0) {
					$prefix_counts[$prefix]['used']++;
				}
			}

			// Sort prefixes by total desc, then name.
			uksort($prefix_counts, static function ($a, $b) use ($prefix_counts) {
				$diff = $prefix_counts[$b]['total'] <=> $prefix_counts[$a]['total'];
				return $diff !== 0 ? $diff : strcasecmp($a, $b);
			});

			$prefix_lines = [];
			foreach ($prefix_counts as $prefix => $counts) {
				$prefix_lines[] = sprintf(
					/* translators: 1: prefix, 2: total, 3: used */
					__('%1$s prefix was found in %2$d codes (%3$d of %2$d have been used)', 'user-manager'),
					$prefix,
					(int) $counts['total'],
					(int) $counts['used']
				);
			}

			$coupon_entry['related_count']    = count($related_list);
			$coupon_entry['related_codes']    = $formatted_related;
			$coupon_entry['related_prefixes'] = $prefix_lines;
		}
		unset($coupon_entry);

		if ($for_csv) {
			$rows = [];
			foreach ($coupons as $coupon_entry) {
				$usage_label = $coupon_entry['usage_limit']
					? sprintf('%d / %d', $coupon_entry['usage_count'], $coupon_entry['usage_limit'])
					: sprintf('%d / %s', $coupon_entry['usage_count'], 'Unlimited');

				$rows[] = [
					$coupon_entry['coupon_code'],
					$coupon_entry['amount'],
					$coupon_entry['discount_type'],
					$coupon_entry['status'],
					$usage_label,
					$coupon_entry['usage_limit_per_user'] ?: 'Unlimited',
					$coupon_entry['created_at'] ?: '',
					$coupon_entry['expiry_date'] ?: 'No expiration',
					$coupon_entry['min_spend'],
					$coupon_entry['max_spend'],
					$coupon_entry['individual_use'] ? 'yes' : 'no',
					$coupon_entry['free_shipping'] ? 'yes' : 'no',
					!empty($coupon_entry['allowed_emails']) ? implode(', ', $coupon_entry['allowed_emails']) : '',
					$coupon_entry['related_count'],
					!empty($coupon_entry['related_codes']) ? implode("\n", $coupon_entry['related_codes']) : '',
					!empty($coupon_entry['related_prefixes']) ? implode("\n", $coupon_entry['related_prefixes']) : '',
				];
			}

			return $rows;
		}

		return $coupons;
	}

	/**
	 * Render "Coupons with Remaining Balances" report.
	 */
	private static function render_coupons_remaining_balances_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$settings = User_Manager_Core::get_settings();
		$generated_prefix = isset($settings['coupon_remainder_generated_prefix']) && $settings['coupon_remainder_generated_prefix'] !== ''
			? trim((string) $settings['coupon_remainder_generated_prefix'])
			: 'remaining-balance-';

		$coupons_query = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		]);

		$entries         = [];
		$total_remaining = 0.0;

		foreach ($coupons_query->posts as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			if ($coupon->get_discount_type() !== 'fixed_cart') {
				continue;
			}

			$code = $coupon->get_code();

			// Heuristic: remainder coupons are generated with the configured prefix.
			if ($generated_prefix && stripos($code, $generated_prefix) !== 0) {
				continue;
			}

			// Only include remainder coupons that have never been used.
			if ($coupon->get_usage_count() > 0) {
				continue;
			}

			$remaining_amount = (float) $coupon->get_amount();
			$total_remaining += $remaining_amount;

			$created_at = get_post_field('post_date', $coupon_id);

			// Collect any associated email addresses for this coupon, similar to the
			// "Coupons with Email Addresses" report.
			$emails = [];

			// Emails from customer_email meta.
			$customer_email = get_post_meta($coupon_id, 'customer_email', true);
			if (!empty($customer_email)) {
				if (is_array($customer_email)) {
					$emails = array_merge($emails, $customer_email);
				} else {
					$emails = array_merge($emails, array_map('trim', explode(',', $customer_email)));
				}
			}

			// Emails from WooCommerce coupon email restrictions.
			if (method_exists($coupon, 'get_email_restrictions')) {
				$restrictions = $coupon->get_email_restrictions('edit');
				if (!empty($restrictions) && is_array($restrictions)) {
					$emails = array_merge($emails, $restrictions);
				}
			}

			// Email from User Manager meta.
			$um_email = get_post_meta($coupon_id, '_um_user_coupon_user_email', true);
			if (!empty($um_email)) {
				$emails[] = $um_email;
			}

			// Normalize, dedupe, and flatten emails to a comma-separated list.
			$emails = array_unique(array_filter(array_map('trim', $emails)));
			$email_list = implode(', ', $emails);

			$entries[] = [
				'coupon_id'   => $coupon_id,
				'coupon_code' => $code,
				'amount'      => $remaining_amount,
				'usage_count' => $coupon->get_usage_count(),
				'usage_limit' => $coupon->get_usage_limit(),
				'expiry_date' => $coupon->get_date_expires() ? $coupon->get_date_expires()->date('Y-m-d H:i:s') : '',
				'created_at'  => $created_at ?: '',
				'emails'      => $email_list,
			];
		}

		// Ensure newest coupons are shown first by created date, just in case
		// another filter modifies the query ordering.
		usort(
			$entries,
			static function (array $a, array $b): int {
				$at = strtotime($a['created_at'] ?? '') ?: 0;
				$bt = strtotime($b['created_at'] ?? '') ?: 0;
				// Descending (newest first).
				return $bt <=> $at;
			}
		);

		$total = count($entries);
		if ($total > $per_page) {
			$entries = array_slice($entries, $offset, $per_page);
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'coupons-remaining-balances', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<p>
				<strong><?php esc_html_e('Total Remaining Balance Across All Remainder Coupons:', 'user-manager'); ?></strong>
				<?php
				if (function_exists('wc_price')) {
					echo wp_kses_post(wc_price($total_remaining));
				} else {
					echo esc_html(number_format_i18n($total_remaining, 2));
				}
				?>
			</p>
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No remainder coupons found matching the configured prefix.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Coupon Code', 'user-manager'); ?></th>
						<th><?php esc_html_e('Remaining Amount', 'user-manager'); ?></th>
						<th><?php esc_html_e('Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Created', 'user-manager'); ?></th>
						<th><?php esc_html_e('Usage', 'user-manager'); ?></th>
						<th><?php esc_html_e('Expiry Date', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $e) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . $e['coupon_id'] . '&action=edit')); ?>">
									<strong><?php echo esc_html(strtoupper($e['coupon_code'])); ?></strong>
								</a>
							</td>
							<td><?php echo function_exists('wc_price') ? wc_price($e['amount']) : esc_html($e['amount']); ?></td>
							<td><?php echo !empty($e['emails']) ? esc_html($e['emails']) : '—'; ?></td>
							<td>
								<?php
								if (!empty($e['created_at'])) {
									$created_ts = strtotime($e['created_at']);
									echo esc_html(
										$created_ts
											? date_i18n(
												get_option('date_format') . ' ' . get_option('time_format'),
												$created_ts
											)
											: '—'
									);
								} else {
									echo '—';
								}
								?>
							</td>
							<td>
								<?php echo esc_html($e['usage_count']); ?>
								<?php if ($e['usage_limit']) : ?>
									/ <?php echo esc_html($e['usage_limit']); ?>
								<?php else : ?>
									/ <?php esc_html_e('Unlimited', 'user-manager'); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($e['expiry_date'] ? date_i18n(get_option('date_format'), strtotime($e['expiry_date'])) : '—'); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Order Refunds" report.
	 */
	private static function render_orders_with_refunds_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_orders_with_refunds_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'orders-with-refunds', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No refunded orders found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Order ID', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Total', 'user-manager'); ?></th>
						<th><?php esc_html_e('Refunded Amount', 'user-manager'); ?></th>
						<th><?php esc_html_e('Refund Type', 'user-manager'); ?></th>
						<th><?php esc_html_e('Status', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . absint($row['order_id']) . '&action=edit')); ?>">
									#<?php echo esc_html($row['order_id']); ?>
								</a>
							</td>
							<td><?php echo esc_html($row['order_date']); ?></td>
							<td><?php echo esc_html($row['billing_email'] ?: '—'); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['order_total']));
								} else {
									echo esc_html($row['order_total']);
								}
								?>
							</td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['refunded_amount']));
								} else {
									echo esc_html($row['refunded_amount']);
								}
								?>
							</td>
							<td>
								<?php
								echo esc_html(
									$row['refund_type'] === 'full'
										? __('Full', 'user-manager')
										: __('Partial', 'user-manager')
								);
								?>
							</td>
							<td><?php echo esc_html($row['status']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "User Password Changes" report, based on user activity table.
	 */
	private static function render_user_password_changes_report(): void {
		global $wpdb;

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$table = $wpdb->prefix . 'um_user_activity';
		$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;

		$entries = [];
		$total   = 0;

		if ($table_exists) {
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE action = %s",
					'Changed Password'
				)
			);

			$query = $wpdb->prepare(
				"SELECT h.id, h.user_id, h.action, h.url, h.ip_address, h.user_agent, h.created_at,
				        u.user_login, u.user_email, u.display_name
				   FROM {$table} h
				   LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
				  WHERE h.action = %s
				  ORDER BY h.created_at DESC
				  LIMIT %d OFFSET %d",
				'Changed Password',
				$per_page,
				$offset
			);

			$rows = $wpdb->get_results($query);
			foreach ($rows as $row) {
				$entries[] = [
					'user_id'      => (int) $row->user_id,
					'username'     => $row->user_login ?: '',
					'email'        => $row->user_email ?: '',
					'display_name' => $row->display_name ?: ($row->user_email ?: ''),
					'url'          => $row->url ?: '',
					'ip_address'   => $row->ip_address ?: '',
					'user_agent'   => $row->user_agent ?: '',
					'created_at'   => $row->created_at ?: '',
				];
			}
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'user-password-changes', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No password changes found in user activity.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('User', 'user-manager'); ?></th>
						<th><?php esc_html_e('Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Username', 'user-manager'); ?></th>
						<th><?php esc_html_e('URL', 'user-manager'); ?></th>
						<th><?php esc_html_e('IP Address', 'user-manager'); ?></th>
						<th><?php esc_html_e('User Agent', 'user-manager'); ?></th>
						<th><?php esc_html_e('Date', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $e) : ?>
						<tr>
							<td>
								<?php if (!empty($e['user_id'])) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($e['user_id'])); ?>">
										<?php echo esc_html($e['display_name'] ?: $e['email'] ?: $e['username']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($e['display_name'] ?: $e['email'] ?: $e['username'] ?: '—'); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($e['email'] ?: '—'); ?></td>
							<td><?php echo esc_html($e['username'] ?: '—'); ?></td>
							<td style="max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
								<?php if (!empty($e['url'])) : ?>
									<a href="<?php echo esc_url($e['url']); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($e['url']); ?>
									</a>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
							<td><code><?php echo esc_html($e['ip_address'] ?: '—'); ?></code></td>
							<td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo esc_attr($e['user_agent']); ?>">
								<?php echo esc_html($e['user_agent'] ?: '—'); ?>
							</td>
							<td><?php echo esc_html($e['created_at']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Order Notes" report: internal/customer/system notes left on orders.
	 */
	private static function render_order_notes_report(): void {
		global $wpdb;

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$orders_table   = $wpdb->prefix . 'posts';
		$comments_table = $wpdb->prefix . 'comments';
		$meta_table     = $wpdb->prefix . 'commentmeta';

		// Count total notes.
		$total = (int) $wpdb->get_var(
			"
			SELECT COUNT(*)
			  FROM {$comments_table} c
			  INNER JOIN {$orders_table} p ON c.comment_post_ID = p.ID
			 WHERE c.comment_type = 'order_note'
			   AND p.post_type = 'shop_order'
			"
		);

		// Fetch paged notes with note_type meta.
		$query = $wpdb->prepare(
			"
			SELECT c.comment_ID, c.comment_post_ID, c.comment_author, c.comment_content, c.comment_date_gmt,
			       p.post_date, p.post_status,
			       MAX(CASE WHEN cm.meta_key = '_wc_order_note_type' THEN cm.meta_value END) as note_type
			  FROM {$comments_table} c
			  INNER JOIN {$orders_table} p ON c.comment_post_ID = p.ID
			  LEFT JOIN {$meta_table} cm ON c.comment_ID = cm.comment_id
			 WHERE c.comment_type = 'order_note'
			   AND p.post_type = 'shop_order'
			 GROUP BY c.comment_ID
			 ORDER BY c.comment_date_gmt DESC
			 LIMIT %d OFFSET %d
			",
			$per_page,
			$offset
		);

		$results = $wpdb->get_results($query);

		$entries = [];
		foreach ($results as $row) {
			$note_type = $row->note_type ?: 'internal';

			$entries[] = [
				'order_id'      => (int) $row->comment_post_ID,
				'author'        => (string) $row->comment_author,
				'note_type'     => $note_type,
				'content'       => (string) $row->comment_content,
				'date_gmt'      => (string) $row->comment_date_gmt,
				'order_status'  => (string) $row->post_status,
			];
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'order-notes', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($entries)) : ?>
			<p><?php esc_html_e('No order notes found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Order', 'user-manager'); ?></th>
						<th><?php esc_html_e('Author', 'user-manager'); ?></th>
						<th><?php esc_html_e('Note Type', 'user-manager'); ?></th>
						<th><?php esc_html_e('Content', 'user-manager'); ?></th>
						<th><?php esc_html_e('Date (GMT)', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Status', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $e) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . $e['order_id'] . '&action=edit')); ?>">
									#<?php echo esc_html($e['order_id']); ?>
								</a>
							</td>
							<td><?php echo esc_html($e['author'] ?: '—'); ?></td>
							<td><?php echo esc_html($e['note_type']); ?></td>
							<td style="max-width:400px; white-space:normal;">
								<?php echo wp_kses_post(wpautop($e['content'])); ?>
							</td>
							<td><?php echo esc_html($e['date_gmt']); ?></td>
							<td><?php echo esc_html($e['order_status']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Orders Processing by Number of Days" report.
	 *
	 * Shows orders currently in Processing status with the oldest (highest days) at the top.
	 */
	private static function render_orders_processing_days_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_orders_processing_days_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'orders-processing-days', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No processing orders found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Order', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Status', 'user-manager'); ?></th>
						<th><?php esc_html_e('Days in Processing', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . absint($row['order_id']) . '&action=edit')); ?>">
									#<?php echo esc_html($row['order_id']); ?>
								</a>
							</td>
							<td><?php echo esc_html($row['order_date']); ?></td>
							<td><?php echo esc_html($row['billing_email'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['status']); ?></td>
							<td><?php echo esc_html($row['days']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Order Total Shipments by Day" report.
	 */
	private static function render_orders_shipments_by_day_report(): void {
		self::render_orders_shipments_aggregated_report('day');
	}

	/**
	 * Render "Order Total Shipments by Week" report.
	 */
	private static function render_orders_shipments_by_week_report(): void {
		self::render_orders_shipments_aggregated_report('week');
	}

	/**
	 * Render "Order Total Shipments by Month" report.
	 */
	private static function render_orders_shipments_by_month_report(): void {
		self::render_orders_shipments_aggregated_report('month');
	}

	/**
	 * Render Sales vs Coupon Usage report.
	 */
	private static function render_sales_vs_coupons_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager'); ?></p>
			<?php
			return;
		}
		
		$per_page = 50;
		$paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$current_url = add_query_arg('report', 'sales-vs-coupons', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		$today = current_time('Y-m-d');
		$default_from = date('Y-m-d', strtotime('-3 months', strtotime($today)));
		$default_to   = $today;

		$date_from = isset($_GET['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_from'])
			? sanitize_text_field(wp_unslash($_GET['date_from']))
			: $default_from;

		$date_to = isset($_GET['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_to'])
			? sanitize_text_field(wp_unslash($_GET['date_to']))
			: $default_to;

		$all_rows = self::get_sales_vs_coupons_data(false, $date_from, $date_to);
		$total = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, ($paged - 1) * $per_page, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<form method="get" style="margin-bottom:15px;">
				<?php
				foreach (['page', 'tab', 'report'] as $key) {
					if (isset($_GET[$key])) {
						?>
						<input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_GET[$key]))); ?>" />
						<?php
					}
				}
				?>
				<label for="um_date_from" style="margin-right:8px;">
					<?php esc_html_e('From', 'user-manager'); ?>:
					<input type="date" id="um_date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>" />
				</label>
				<label for="um_date_to" style="margin:0 8px;">
					<?php esc_html_e('To', 'user-manager'); ?>:
					<input type="date" id="um_date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>" />
				</label>
				<?php submit_button(__('Filter', 'user-manager'), 'secondary', '', false); ?>
			</form>

			<a href="<?php echo esc_url(add_query_arg(['export' => 'csv', 'date_from' => $date_from, 'date_to' => $date_to], $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No orders found for the selected date range.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Order ID', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Ship To Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Ship To State', 'user-manager'); ?></th>
						<th><?php esc_html_e('Ship To Country', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Payment Collected', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Coupon(s) Value Collected', 'user-manager'); ?></th>
						<th><?php esc_html_e('Coupon Code(s) Used', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url(admin_url('post.php?post=' . absint($row['order_id']) . '&action=edit')); ?>">
									#<?php echo esc_html($row['order_id']); ?>
								</a>
							</td>
							<td><?php echo esc_html($row['order_date']); ?></td>
							<td><?php echo esc_html($row['ship_to_name']); ?></td>
							<td><?php echo esc_html($row['ship_to_state']); ?></td>
							<td><?php echo esc_html($row['ship_to_country']); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['order_total']));
								} else {
									echo esc_html($row['order_total']);
								}
								?>
							</td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['coupon_total']));
								} else {
									echo esc_html($row['coupon_total']);
								}
								?>
							</td>
							<td><?php echo esc_html($row['coupon_codes']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php
				self::render_pagination(
					$paged,
					$total_pages,
					$total,
					add_query_arg(
						[
							'date_from' => $date_from,
							'date_to'   => $date_to,
						],
						$current_url
					)
				);
				?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Get Sales vs Coupon Usage data.
	 *
	 * @param bool        $for_csv   Whether data is for CSV (numeric values formatted).
	 * @param string|null $date_from From date (Y-m-d).
	 * @param string|null $date_to   To date (Y-m-d).
	 *
	 * @return array
	 */
	private static function get_sales_vs_coupons_data(bool $for_csv = false, ?string $date_from = null, ?string $date_to = null): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$today = current_time('Y-m-d');
		if (empty($date_from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
			$date_from = date('Y-m-d', strtotime('-3 months', strtotime($today)));
		}
		if (empty($date_to) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
			$date_to = $today;
		}

		$statuses = ['wc-completed', 'wc-processing', 'wc-on-hold'];

		$args = [
			'limit'        => -1,
			'status'       => $statuses,
			'type'         => 'shop_order',
			'date_created' => $date_from . '...' . $date_to,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'return'       => 'objects',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		$data = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$order_id   = $order->get_id();
			$order_date = $order->get_date_created() ? $order->get_date_created()->date_i18n('Y-m-d H:i:s') : '';

			$ship_name    = trim($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name());
			if ('' === $ship_name) {
				$ship_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
			}

			$ship_state   = $order->get_shipping_state() ?: $order->get_billing_state();
			$ship_country = $order->get_shipping_country() ?: $order->get_billing_country();

			$order_total = (float) $order->get_total();

			$coupon_codes = $order->get_coupon_codes();
			$coupon_items = $order->get_items('coupon');

			$coupon_total = 0.0;
			foreach ($coupon_items as $coupon_item) {
				if (method_exists($coupon_item, 'get_discount')) {
					$coupon_total += (float) $coupon_item->get_discount();
				} elseif (is_array($coupon_item) && isset($coupon_item['discount_amount'])) {
					$coupon_total += (float) $coupon_item['discount_amount'];
				}
			}

			$coupon_codes_str = !empty($coupon_codes) ? implode(', ', array_map('strtoupper', $coupon_codes)) : '';

			if ($for_csv) {
				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$order_total_formatted  = wc_format_decimal($order_total, wc_get_price_decimals());
					$coupon_total_formatted = wc_format_decimal($coupon_total, wc_get_price_decimals());
				} else {
					$order_total_formatted  = $order_total;
					$coupon_total_formatted = $coupon_total;
				}

				$data[] = [
					$order_id,
					$order_date,
					$ship_name,
					$ship_state,
					$ship_country,
					$order_total_formatted,
					$coupon_total_formatted,
					$coupon_codes_str,
				];
			} else {
				$data[] = [
					'order_id'        => $order_id,
					'ship_to_name'    => $ship_name,
					'ship_to_state'   => $ship_state,
					'ship_to_country' => $ship_country,
					'order_total'     => $order_total,
					'coupon_total'    => $coupon_total,
					'coupon_codes'    => $coupon_codes_str,
					'order_date'      => $order_date,
				];
			}
		}

		return $data;
	}

	/**
	 * Render "Orders with $0 Total" report.
	 */
	private static function render_orders_zero_total_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_orders_zero_total_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'orders-zero-total', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No orders with $0 total were found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Order', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Status', 'user-manager'); ?></th>
						<th><?php esc_html_e('Coupons Applied', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Coupons Value', 'user-manager'); ?></th>
						<th><?php esc_html_e('Line Items Preview', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$order_id       = (int) $row['order_id'];
						$order_edit_url = admin_url('post.php?post=' . $order_id . '&action=edit');
						?>
						<tr>
							<td>
								<a href="<?php echo esc_url($order_edit_url); ?>">
									#<?php echo esc_html($order_id); ?>
								</a>
							</td>
							<td><?php echo esc_html($row['order_date']); ?></td>
							<td><?php echo esc_html($row['billing_email'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['status']); ?></td>
							<td><?php echo esc_html($row['coupon_codes'] ?: '—'); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['coupon_total']));
								} else {
									echo esc_html($row['coupon_total']);
								}
								?>
							</td>
							<td>
								<?php
								$order = function_exists('wc_get_order') ? wc_get_order($order_id) : null;
								if ($order instanceof WC_Order) :
									$items        = $order->get_items('line_item');
									$preview_text = [];
									$count        = 0;
									foreach ($items as $item) {
										/* @var WC_Order_Item_Product $item */
										$product_name = $item->get_name();
										$qty          = $item->get_quantity();
										$preview_text[] = sprintf('%s × %s', $product_name, $qty);
										$count++;
										if ($count >= 3) {
											break;
										}
									}
									$more_count = max(0, count($items) - $count);
									?>
									<div>
										<?php if (!empty($preview_text)) : ?>
											<span><?php echo esc_html(implode(', ', $preview_text)); ?></span>
											<?php if ($more_count > 0) : ?>
												<span><?php printf(esc_html__(' …and %d more item(s)', 'user-manager'), $more_count); ?></span>
											<?php endif; ?>
										<?php else : ?>
											<span><?php esc_html_e('No line items', 'user-manager'); ?></span>
										<?php endif; ?>
									</div>
									<?php
								else :
									?>
									<span><?php esc_html_e('Order not found', 'user-manager'); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Orders with Free Shipping" report.
	 */
	private static function render_orders_free_shipping_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_orders_free_shipping_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'orders-free-shipping', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No orders with free shipping were found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Order', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Status', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping Method', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping Total', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping Tax', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$order_id       = (int) $row['order_id'];
						$order_edit_url = admin_url('post.php?post=' . $order_id . '&action=edit');
						?>
						<tr>
							<td>
								<a href="<?php echo esc_url($order_edit_url); ?>">
									#<?php echo esc_html($order_id); ?>
								</a>
							</td>
							<td><?php echo esc_html($row['order_date']); ?></td>
							<td><?php echo esc_html($row['billing_email'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['status']); ?></td>
							<td><?php echo esc_html($row['shipping_method'] ?: '—'); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['shipping_total']));
								} else {
									echo esc_html($row['shipping_total']);
								}
								?>
							</td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['shipping_tax']));
								} else {
									echo esc_html($row['shipping_tax']);
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "User Total Sales" report.
	 */
	private static function render_user_total_sales_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_user_total_sales_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'user-total-sales', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No user sales found for the selected criteria.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('User', 'user-manager'); ?></th>
						<th><?php esc_html_e('Username', 'user-manager'); ?></th>
						<th><?php esc_html_e('Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('First Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Sales', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Orders', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Lines Purchased', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php $user_id = (int) $row['user_id']; ?>
						<tr>
							<td>
								<?php if ($user_id > 0) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($user_id)); ?>">
										<?php echo esc_html(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: ($row['email'] ?? $row['username'])); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('N/A', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['username'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['email'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['first_name'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['last_name'] ?: '—'); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['total_sales']));
								} else {
									echo esc_html($row['total_sales']);
								}
								?>
							</td>
							<td><?php echo esc_html($row['total_orders']); ?></td>
							<td><?php echo esc_html($row['total_lines']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Users Who Used Coupons" report.
	 */
	private static function render_users_who_used_coupons_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_users_who_used_coupons_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'users-who-used-coupons', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No users with coupon usage were found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Coupons Used', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Value of Coupons Used', 'user-manager'); ?></th>
						<th><?php esc_html_e('List of Coupons Used', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Paid After Coupons Applied', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td><?php echo esc_html($row['email']); ?></td>
							<td><?php echo esc_html($row['total_coupons']); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['total_coupon_value']));
								} else {
									echo esc_html($row['total_coupon_value']);
								}
								?>
							</td>
							<td><?php echo esc_html(implode(', ', $row['coupon_codes'])); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['total_paid_after']));
								} else {
									echo esc_html($row['total_paid_after']);
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Product Purchases" report.
	 */
	private static function render_product_purchases_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_product_purchases_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'product-purchases', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No product purchases found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Product', 'user-manager'); ?></th>
						<th><?php esc_html_e('Product ID', 'user-manager'); ?></th>
						<th><?php esc_html_e('SKU', 'user-manager'); ?></th>
						<th><?php esc_html_e('Current Price', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Sales', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Qty Sold', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Orders', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td>
								<?php if (!empty($row['product_id'])) : ?>
									<a href="<?php echo esc_url(get_edit_post_link((int) $row['product_id'])); ?>">
										<?php echo esc_html($row['name'] ?: '—'); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['name'] ?: '—'); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['product_id'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['sku'] ?: '—'); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['price']));
								} else {
									echo esc_html($row['price']);
								}
								?>
							</td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['total_sales']));
								} else {
									echo esc_html($row['total_sales']);
								}
								?>
							</td>
							<td><?php echo esc_html($row['total_qty']); ?></td>
							<td><?php echo esc_html($row['total_orders']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Product Category Purchases" report.
	 */
	private static function render_product_category_purchases_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_product_category_purchases_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'product-category-purchases', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No product category purchases found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Category', 'user-manager'); ?></th>
						<th><?php esc_html_e('Category ID', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Sales', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Qty Sold', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Orders', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td>
								<?php if (!empty($row['term_id'])) : ?>
									<a href="<?php echo esc_url(get_edit_term_link((int) $row['term_id'], 'product_cat')); ?>">
										<?php echo esc_html($row['name'] ?: '—'); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['name'] ?: '—'); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['term_id'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['slug'] ?: '—'); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['total_sales']));
								} else {
									echo esc_html($row['total_sales']);
								}
								?>
							</td>
							<td><?php echo esc_html($row['total_qty']); ?></td>
							<td><?php echo esc_html($row['total_orders']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Product Tag Purchases" report.
	 */
	private static function render_product_tag_purchases_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_product_tag_purchases_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'product-tag-purchases', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No product tag purchases found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Tag', 'user-manager'); ?></th>
						<th><?php esc_html_e('Tag ID', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Sales', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Qty Sold', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Orders', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td>
								<?php if (!empty($row['term_id'])) : ?>
									<a href="<?php echo esc_url(get_edit_term_link((int) $row['term_id'], 'product_tag')); ?>">
										<?php echo esc_html($row['name'] ?: '—'); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['name'] ?: '—'); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['term_id'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['slug'] ?: '—'); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['total_sales']));
								} else {
									echo esc_html($row['total_sales']);
								}
								?>
							</td>
							<td><?php echo esc_html($row['total_qty']); ?></td>
							<td><?php echo esc_html($row['total_orders']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Page Not Found 404 Errors" report.
	 */
	private static function render_page_not_found_404_errors_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_404_errors_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'page-not-found-404-errors', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No 404 errors have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('URL', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Seen', 'user-manager'); ?></th>
						<th><?php esc_html_e('Hits', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						?>
						<tr>
							<td style="max-width:420px; word-break:break-all;">
								<a href="<?php echo esc_url($row['url']); ?>" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html($row['url']); ?>
								</a>
							</td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Search Queries" report.
	 */
	private static function render_search_queries_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_search_queries_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'search-queries', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No search queries have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Search Term', 'user-manager'); ?></th>
						<th><?php esc_html_e('Post Type', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Searches', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Searched', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
						<th><?php esc_html_e('Example URL', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						$example_url  = isset($row['last_url']) ? (string) $row['last_url'] : '';
						$post_type    = isset($row['post_type']) ? (string) $row['post_type'] : '';
						?>
						<tr>
							<td><?php echo esc_html($row['query']); ?></td>
							<td>
								<?php if ($post_type !== '') : ?>
									<?php echo esc_html($post_type); ?>
								<?php else : ?>
									<em><?php esc_html_e('Default', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
							<td style="max-width:420px; word-break:break-all;">
								<?php if ($example_url) : ?>
									<a href="<?php echo esc_url($example_url); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($example_url); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('N/A', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Page Views" report.
	 */
	private static function render_page_views_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_page_views_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'page-views', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No page views have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Title', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Permalink', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Views', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Viewed', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						$permalink    = isset($row['permalink']) ? (string) $row['permalink'] : '';
						?>
						<tr>
							<td>
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($row['title']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['title']); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['slug']); ?></td>
							<td style="max-width:420px; word-break:break-all;">
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($permalink); ?>
									</a>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Page Category Archives Views" report.
	 */
	private static function render_page_category_views_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_page_category_views_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'page-category-views', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No page category archive views have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Permalink', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Views', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Viewed', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						$permalink    = isset($row['permalink']) ? (string) $row['permalink'] : '';
						?>
						<tr>
							<td>
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($row['name']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['name']); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['slug']); ?></td>
							<td style="max-width:420px; word-break:break-all;">
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($permalink); ?>
									</a>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Post Views" report.
	 */
	private static function render_post_views_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_post_views_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'post-views', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No post views have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Title', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Permalink', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Views', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Viewed', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						$permalink    = isset($row['permalink']) ? (string) $row['permalink'] : '';
						?>
						<tr>
							<td>
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($row['title']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['title']); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['slug']); ?></td>
							<td style="max-width:420px; word-break:break-all;">
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($permalink); ?>
									</a>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Post Category Archives Views" report.
	 */
	private static function render_post_category_views_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_post_category_views_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'post-category-views', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No post category archive views have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Permalink', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Views', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Viewed', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						$permalink    = isset($row['permalink']) ? (string) $row['permalink'] : '';
						?>
						<tr>
							<td>
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($row['name']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['name']); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['slug']); ?></td>
							<td style="max-width:420px; word-break:break-all;">
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($permalink); ?>
									</a>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Post Tag Archives Views" report.
	 */
	private static function render_post_tag_views_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_post_tag_views_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'post-tag-views', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No post tag archive views have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Permalink', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Views', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Viewed', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						$permalink    = isset($row['permalink']) ? (string) $row['permalink'] : '';
						?>
						<tr>
							<td>
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($row['name']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['name']); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['slug']); ?></td>
							<td style="max-width:420px; word-break:break-all;">
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($permalink); ?>
									</a>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Product Views" report.
	 */
	private static function render_product_views_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_product_views_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'product-views', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No product views have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Title', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Permalink', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Views', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Viewed', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						$permalink    = isset($row['permalink']) ? (string) $row['permalink'] : '';
						?>
						<tr>
							<td>
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($row['title']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['title']); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['slug']); ?></td>
							<td style="max-width:420px; word-break:break-all;">
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($permalink); ?>
									</a>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Product Category Archives Views" report.
	 */
	private static function render_product_category_views_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_product_category_views_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'product-category-views', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No product category archive views have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Permalink', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Views', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Viewed', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						$permalink    = isset($row['permalink']) ? (string) $row['permalink'] : '';
						?>
						<tr>
							<td>
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($row['name']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['name']); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['slug']); ?></td>
							<td style="max-width:420px; word-break:break-all;">
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($permalink); ?>
									</a>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Product Tag Archives Views" report.
	 */
	private static function render_product_tag_views_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_product_tag_views_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'product-tag-views', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No product tag archive views have been recorded yet.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
						<th><?php esc_html_e('Permalink', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Views', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Viewed', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last User', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$last_user_id = isset($row['last_user_id']) ? (int) $row['last_user_id'] : 0;
						$last_user    = $last_user_id ? get_user_by('ID', $last_user_id) : null;
						$permalink    = isset($row['permalink']) ? (string) $row['permalink'] : '';
						?>
						<tr>
							<td>
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($row['name']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($row['name']); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['slug']); ?></td>
							<td style="max-width:420px; word-break:break-all%;">
								<?php if ($permalink !== '') : ?>
									<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html($permalink); ?>
									</a>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($row['hits']); ?></td>
							<td><?php echo esc_html($row['last_seen']); ?></td>
							<td>
								<?php if ($last_user) : ?>
									<a href="<?php echo esc_url(get_edit_user_link($last_user->ID)); ?>">
										<?php echo esc_html($last_user->user_email ?: $last_user->user_login); ?>
									</a>
								<?php else : ?>
									<em><?php esc_html_e('Guest / Unknown', 'user-manager'); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Post Meta Field Names (Unique List)" report.
	 */
	private static function render_post_meta_field_names_report(): void {
		global $wpdb;
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$meta_table = $wpdb->postmeta;
		$all_keys   = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$meta_table} ORDER BY meta_key ASC");
		$all_keys   = is_array($all_keys) ? $all_keys : [];
		$total      = count($all_keys);

		if ($total > $per_page) {
			$rows = array_slice($all_keys, $offset, $per_page);
		} else {
			$rows = $all_keys;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'post-meta-field-names', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No post meta fields found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Meta Key', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $meta_key) : ?>
						<tr>
							<td><code><?php echo esc_html($meta_key); ?></code></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Order Payment Methods" report.
	 */
	private static function render_order_payment_methods_report(): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_order_payment_methods_data(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'order-payment-methods', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No orders found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Order', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Date', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Status', 'user-manager'); ?></th>
						<th><?php esc_html_e('Payment Method', 'user-manager'); ?></th>
						<th><?php esc_html_e('Payment Source', 'user-manager'); ?></th>
						<th><?php esc_html_e('Order Total', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<?php
						$order_id       = (int) $row['order_id'];
						$order_edit_url = admin_url('post.php?post=' . $order_id . '&action=edit');
						?>
						<tr>
							<td>
								<a href="<?php echo esc_url($order_edit_url); ?>">
									#<?php echo esc_html($order_id); ?>
								</a>
							</td>
							<td><?php echo esc_html($row['order_date']); ?></td>
							<td><?php echo esc_html($row['billing_email'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['status']); ?></td>
							<td><?php echo esc_html($row['payment_method'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['payment_source'] ?: '—'); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['order_total']));
								} else {
									echo esc_html($row['order_total']);
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Get data for "Orders with $0 Total" report.
	 *
	 * @param bool $for_csv Whether to format for CSV (no currency formatting needed because total is 0).
	 * @return array
	 */
	private static function get_orders_zero_total_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		// Include all core order statuses.
		$statuses = array_keys(function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [
			'wc-pending'    => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold'    => 'On hold',
			'wc-completed'  => 'Completed',
			'wc-cancelled'  => 'Cancelled',
			'wc-refunded'   => 'Refunded',
			'wc-failed'     => 'Failed',
		]);

		$args = [
			'limit'  => -1,
			'status' => $statuses,
			'type'   => 'shop_order',
			'return' => 'objects',
			'orderby'=> 'date',
			'order'  => 'DESC',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		$data   = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			// We consider orders whose final total is exactly 0.
			$total = (float) $order->get_total();
			if (abs($total) > 0.0001) {
				continue;
			}

			$order_id     = $order->get_id();
			$order_date   = $order->get_date_created() ? $order->get_date_created()->date_i18n('Y-m-d H:i:s') : '';
			$billing_mail = $order->get_billing_email();
			$status       = $order->get_status();

			$coupon_codes = $order->get_coupon_codes();
			$coupon_items = $order->get_items('coupon');
			$coupon_total = 0.0;

			foreach ($coupon_items as $coupon_item) {
				if (method_exists($coupon_item, 'get_discount')) {
					$coupon_total += (float) $coupon_item->get_discount();
				} elseif (is_array($coupon_item) && isset($coupon_item['discount_amount'])) {
					$coupon_total += (float) $coupon_item['discount_amount'];
				}
			}

			$coupon_codes_str = !empty($coupon_codes) ? implode(', ', array_map('strtoupper', $coupon_codes)) : '';

			if ($for_csv) {
				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$coupon_total_formatted = wc_format_decimal($coupon_total, wc_get_price_decimals());
				} else {
					$coupon_total_formatted = $coupon_total;
				}

				$data[] = [
					$order_id,
					$order_date,
					$billing_mail,
					$status,
					$coupon_codes_str,
					$coupon_total_formatted,
				];
			} else {
				$data[] = [
					'order_id'      => $order_id,
					'order_date'    => $order_date,
					'billing_email' => $billing_mail,
					'status'        => $status,
					'coupon_codes'  => $coupon_codes_str,
					'coupon_total'  => $coupon_total,
				];
			}
		}

		return $data;
	}

	/**
	 * Get data for "Orders with Free Shipping" report.
	 *
	 * We treat an order as having free shipping if its shipping total + shipping tax is zero
	 * (within a tiny tolerance), but it has at least one shipping item/method recorded.
	 *
	 * @param bool $for_csv Whether to format for CSV output.
	 * @return array
	 */
	private static function get_orders_free_shipping_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$statuses = array_keys(function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [
			'wc-pending'    => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold'    => 'On hold',
			'wc-completed'  => 'Completed',
			'wc-cancelled'  => 'Cancelled',
			'wc-refunded'   => 'Refunded',
			'wc-failed'     => 'Failed',
		]);

		$args = [
			'limit'  => -1,
			'status' => $statuses,
			'type'   => 'shop_order',
			'return' => 'objects',
			'orderby'=> 'date',
			'order'  => 'DESC',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		$data   = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$shipping_total = (float) $order->get_shipping_total();
			$shipping_tax   = (float) $order->get_shipping_tax();
			$combined       = $shipping_total + $shipping_tax;

			// Require that shipping charges are effectively zero.
			if (abs($combined) > 0.0001) {
				continue;
			}

			$shipping_method = $order->get_shipping_method();
			$has_shipping    = !empty($shipping_method) || !empty($order->get_shipping_country()) || !empty($order->get_billing_country());

			if (!$has_shipping) {
				// Skip purely virtual/no-shipping orders; this report is about free shipping.
				continue;
			}

			$order_id     = $order->get_id();
			$order_date   = $order->get_date_created() ? $order->get_date_created()->date_i18n('Y-m-d H:i:s') : '';
			$billing_mail = $order->get_billing_email();
			$status       = $order->get_status();

			if ($for_csv) {
				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$shipping_total_fmt = wc_format_decimal($shipping_total, wc_get_price_decimals());
					$shipping_tax_fmt   = wc_format_decimal($shipping_tax, wc_get_price_decimals());
				} else {
					$shipping_total_fmt = $shipping_total;
					$shipping_tax_fmt   = $shipping_tax;
				}

				$data[] = [
					$order_id,
					$order_date,
					$billing_mail,
					$status,
					$shipping_method,
					$shipping_total_fmt,
					$shipping_tax_fmt,
				];
			} else {
				$data[] = [
					'order_id'        => $order_id,
					'order_date'      => $order_date,
					'billing_email'   => $billing_mail,
					'status'          => $status,
					'shipping_method' => $shipping_method,
					'shipping_total'  => $shipping_total,
					'shipping_tax'    => $shipping_tax,
				];
			}
		}

		return $data;
	}

	/**
	 * Get data for "User Total Sales" report.
	 *
	 * Aggregates WooCommerce orders by user to compute:
	 * - Total sales amount
	 * - Total orders
	 * - Total line items purchased (sum of line item quantities)
	 *
	 * Guests (no customer user_id) are excluded.
	 *
	 * @param bool $for_csv Whether to format numeric values for CSV output.
	 * @return array
	 */
	private static function get_user_total_sales_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$statuses = ['wc-completed', 'wc-processing', 'wc-on-hold'];

		$args = [
			'limit'  => -1,
			'status' => $statuses,
			'type'   => 'shop_order',
			'return' => 'objects',
			'orderby'=> 'date',
			'order'  => 'DESC',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		if (empty($orders)) {
			return [];
		}

		$totals = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$user_id = (int) $order->get_customer_id();
			if ($user_id <= 0) {
				// Skip guest orders; this report focuses on registered users.
				continue;
			}

			if (!isset($totals[$user_id])) {
				$user = get_user_by('ID', $user_id);

				$totals[$user_id] = [
					'user_id'       => $user_id,
					'username'      => $user ? $user->user_login : '',
					'email'         => $user ? $user->user_email : '',
					'first_name'    => $user ? get_user_meta($user_id, 'first_name', true) : '',
					'last_name'     => $user ? get_user_meta($user_id, 'last_name', true) : '',
					'total_sales'   => 0.0,
					'total_orders'  => 0,
					'total_lines'   => 0,
				];
			}

			$order_total = (float) $order->get_total();
			$totals[$user_id]['total_sales']  += $order_total;
			$totals[$user_id]['total_orders'] += 1;

			$line_items = $order->get_items('line_item');
			$lines_qty  = 0;
			foreach ($line_items as $item) {
				if (method_exists($item, 'get_quantity')) {
					$lines_qty += (int) $item->get_quantity();
				}
			}
			$totals[$user_id]['total_lines'] += $lines_qty;
		}

		// Convert map to indexed array and sort by total_sales descending.
		$rows = array_values($totals);
		usort($rows, static function ($a, $b) {
			return $b['total_sales'] <=> $a['total_sales'];
		});

		if ($for_csv) {
			$out = [];
			foreach ($rows as $row) {
				$total_sales = $row['total_sales'];
				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$total_sales = wc_format_decimal($total_sales, wc_get_price_decimals());
				}

				$out[] = [
					$row['user_id'],
					$row['username'],
					$row['email'],
					$row['first_name'],
					$row['last_name'],
					$total_sales,
					$row['total_orders'],
					$row['total_lines'],
				];
			}
			return $out;
		}

		return $rows;
	}

	/**
	 * Get data for "Users Who Used Coupons" report.
	 *
	 * Aggregates WooCommerce orders by customer email to compute:
	 * - Total number of coupons applied
	 * - Total coupon discount value
	 * - List of unique coupon codes used
	 * - Total amount paid after coupons across those orders
	 *
	 * @param bool $for_csv Whether to format numeric values for CSV output.
	 * @return array
	 */
	private static function get_users_who_used_coupons_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$statuses = ['wc-completed', 'wc-processing', 'wc-on-hold'];

		// To keep this report performant on large stores, cap the query to a
		// recent window of orders instead of loading the entire history.
		$max_orders   = 10000; // Hard cap on number of orders to consider.
		$days_back    = 365;   // Only look at roughly the last 12 months.
		$now_ts       = current_time('timestamp');
		$cutoff_ts    = strtotime('-' . $days_back . ' days', $now_ts);
		$cutoff_mysql = gmdate('Y-m-d H:i:s', $cutoff_ts);

		$args = [
			'limit'        => $max_orders,
			'status'       => $statuses,
			'type'         => 'shop_order',
			'return'       => 'objects',
			'orderby'      => 'date',
			'order'        => 'DESC',
			// Only consider orders created after the cutoff and up to "now".
			'date_created' => '>' . $cutoff_mysql,
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		if (empty($orders)) {
			return [];
		}

		$totals = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$coupon_codes = $order->get_coupon_codes();
			if (empty($coupon_codes)) {
				// Skip orders without coupons.
				continue;
			}

			$email = trim(strtolower((string) $order->get_billing_email()));

			// Fallback to user email if billing email is missing.
			if ($email === '') {
				$user_id = (int) $order->get_customer_id();
				if ($user_id > 0) {
					$user = get_user_by('ID', $user_id);
					if ($user && !empty($user->user_email)) {
						$email = trim(strtolower($user->user_email));
					}
				}
			}

			if ($email === '') {
				// If we still don't have an email, skip this order.
				continue;
			}

			if (!isset($totals[$email])) {
				$totals[$email] = [
					'email'             => $email,
					'total_coupons'     => 0,
					'total_coupon_value'=> 0.0,
					'total_paid_after'  => 0.0,
					'coupon_codes'      => [],
				];
			}

			// Count coupons on this order.
			$totals[$email]['total_coupons'] += count($coupon_codes);

			// Track unique coupon codes (uppercased for consistency).
			foreach ($coupon_codes as $code) {
				$code = strtoupper(trim((string) $code));
				if ('' !== $code) {
					$totals[$email]['coupon_codes'][$code] = true;
				}
			}

			// Sum coupon discount amounts from coupon line items.
			$coupon_items = $order->get_items('coupon');
			$coupon_total = 0.0;
			foreach ($coupon_items as $coupon_item) {
				if (method_exists($coupon_item, 'get_discount')) {
					$coupon_total += (float) $coupon_item->get_discount();
				} elseif (is_array($coupon_item) && isset($coupon_item['discount_amount'])) {
					$coupon_total += (float) $coupon_item['discount_amount'];
				}
			}

			$totals[$email]['total_coupon_value'] += $coupon_total;

			// Total paid after coupons is just the final order total.
			$totals[$email]['total_paid_after'] += (float) $order->get_total();
		}

		if (empty($totals)) {
			return [];
		}

		// Normalize coupon_codes to flat arrays.
		foreach ($totals as $email => $row) {
			$codes = array_keys($row['coupon_codes']);
			sort($codes, SORT_NATURAL | SORT_FLAG_CASE);
			$totals[$email]['coupon_codes'] = $codes;
		}

		// Convert map to indexed array and sort by total_coupon_value descending.
		$rows = array_values($totals);
		usort($rows, static function ($a, $b) {
			return $b['total_coupon_value'] <=> $a['total_coupon_value'];
		});

		if ($for_csv) {
			$out = [];
			foreach ($rows as $row) {
				$coupon_total = $row['total_coupon_value'];
				$total_after  = $row['total_paid_after'];

				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$coupon_total = wc_format_decimal($coupon_total, wc_get_price_decimals());
					$total_after  = wc_format_decimal($total_after, wc_get_price_decimals());
				}

				$out[] = [
					$row['email'],
					$row['total_coupons'],
					$coupon_total,
					implode(', ', $row['coupon_codes']),
					$total_after,
				];
			}
			return $out;
		}

		return $rows;
	}

	/**
	 * Get data for "Product Purchases" report.
	 *
	 * Aggregates WooCommerce orders by product to compute:
	 * - Total sales amount
	 * - Total quantity sold
	 * - Total orders containing the product
	 *
	 * @param bool $for_csv Whether to format numeric values for CSV output.
	 * @return array
	 */
	private static function get_product_purchases_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$statuses = ['wc-completed', 'wc-processing', 'wc-on-hold'];

		$args = [
			'limit'  => -1,
			'status' => $statuses,
			'type'   => 'shop_order',
			'return' => 'objects',
			'orderby'=> 'date',
			'order'  => 'DESC',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		if (empty($orders)) {
			return [];
		}

		$products = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$order_id   = $order->get_id();
			$line_items = $order->get_items('line_item');

			foreach ($line_items as $item) {
				if (!$item instanceof WC_Order_Item_Product) {
					continue;
				}

				$product   = $item->get_product();
				$product_id = $product ? $product->get_id() : 0;

				$key = $product_id > 0 ? (string) $product_id : 'item_' . md5($item->get_name());

				if (!isset($products[$key])) {
					$name = $product ? $product->get_name() : $item->get_name();
					$sku  = $product ? (string) $product->get_sku() : '';
					$price_current = $product ? (float) $product->get_price() : 0.0;

					$products[$key] = [
						'product_id'   => $product_id,
						'name'         => $name,
						'sku'          => $sku,
						'price'        => $price_current,
						'total_sales'  => 0.0,
						'total_qty'    => 0,
						'order_ids'    => [],
					];
				}

				$qty        = (int) $item->get_quantity();
				$line_total = (float) $item->get_total();

				$products[$key]['total_sales'] += $line_total;
				$products[$key]['total_qty']   += $qty;
				$products[$key]['order_ids'][$order_id] = true;
			}
		}

		$rows = [];
		foreach ($products as $prod) {
			$prod['total_orders'] = count($prod['order_ids']);
			unset($prod['order_ids']);
			$rows[] = $prod;
		}

		// Sort by total_sales descending.
		usort($rows, static function ($a, $b) {
			return $b['total_sales'] <=> $a['total_sales'];
		});

		if ($for_csv) {
			$out = [];
			foreach ($rows as $row) {
				$total_sales = $row['total_sales'];
				$price       = $row['price'];

				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$total_sales = wc_format_decimal($total_sales, wc_get_price_decimals());
					$price       = wc_format_decimal($price, wc_get_price_decimals());
				}

				$out[] = [
					$row['product_id'],
					$row['name'],
					$row['sku'],
					$price,
					$total_sales,
					$row['total_qty'],
					$row['total_orders'],
				];
			}
			return $out;
		}

		return $rows;
	}

	/**
	 * Get data for "Product Category Purchases" report.
	 *
	 * Aggregates WooCommerce orders by product category to compute:
	 * - Total sales amount
	 * - Total quantity sold
	 * - Total orders containing products in the category
	 *
	 * @param bool $for_csv Whether to format numeric values for CSV output.
	 * @return array
	 */
	private static function get_product_category_purchases_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$statuses = ['wc-completed', 'wc-processing', 'wc-on-hold'];

		$args = [
			'limit'  => -1,
			'status' => $statuses,
			'type'   => 'shop_order',
			'return' => 'objects',
			'orderby'=> 'date',
			'order'  => 'DESC',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		if (empty($orders)) {
			return [];
		}

		$cats = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$order_id   = $order->get_id();
			$line_items = $order->get_items('line_item');

			foreach ($line_items as $item) {
				if (!$item instanceof WC_Order_Item_Product) {
					continue;
				}

				$product = $item->get_product();
				$product_id = $product ? $product->get_id() : 0;
				if ($product_id <= 0) {
					continue;
				}

				$term_ids = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
				if (empty($term_ids) || !is_array($term_ids)) {
					continue;
				}

				$qty        = (int) $item->get_quantity();
				$line_total = (float) $item->get_total();

				foreach ($term_ids as $term_id) {
					$term_id = (int) $term_id;
					if (!isset($cats[$term_id])) {
						$term = get_term($term_id, 'product_cat');
						if (!$term || is_wp_error($term)) {
							continue;
						}

						$cats[$term_id] = [
							'term_id'      => $term_id,
							'name'         => $term->name,
							'slug'         => $term->slug,
							'total_sales'  => 0.0,
							'total_qty'    => 0,
							'order_ids'    => [],
						];
					}

					$cats[$term_id]['total_sales'] += $line_total;
					$cats[$term_id]['total_qty']   += $qty;
					$cats[$term_id]['order_ids'][$order_id] = true;
				}
			}
		}

		$rows = [];
		foreach ($cats as $cat) {
			$cat['total_orders'] = count($cat['order_ids']);
			unset($cat['order_ids']);
			$rows[] = $cat;
		}

		// Sort by total_sales descending.
		usort($rows, static function ($a, $b) {
			return $b['total_sales'] <=> $a['total_sales'];
		});

		if ($for_csv) {
			$out = [];
			foreach ($rows as $row) {
				$total_sales = $row['total_sales'];
				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$total_sales = wc_format_decimal($total_sales, wc_get_price_decimals());
				}

				$out[] = [
					$row['term_id'],
					$row['name'],
					$row['slug'],
					$total_sales,
					$row['total_qty'],
					$row['total_orders'],
				];
			}
			return $out;
		}

		return $rows;
	}

	/**
	 * Get data for "Product Tag Purchases" report.
	 *
	 * Aggregates WooCommerce orders by product tag to compute:
	 * - Total sales amount
	 * - Total quantity sold
	 * - Total orders containing products with the tag
	 *
	 * @param bool $for_csv Whether to format numeric values for CSV output.
	 * @return array
	 */
	private static function get_product_tag_purchases_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$statuses = ['wc-completed', 'wc-processing', 'wc-on-hold'];

		$args = [
			'limit'  => -1,
			'status' => $statuses,
			'type'   => 'shop_order',
			'return' => 'objects',
			'orderby'=> 'date',
			'order'  => 'DESC',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		if (empty($orders)) {
			return [];
		}

		$tags = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$order_id   = $order->get_id();
			$line_items = $order->get_items('line_item');

			foreach ($line_items as $item) {
				if (!$item instanceof WC_Order_Item_Product) {
					continue;
				}

				$product = $item->get_product();
				$product_id = $product ? $product->get_id() : 0;
				if ($product_id <= 0) {
					continue;
				}

				$term_ids = wp_get_post_terms($product_id, 'product_tag', ['fields' => 'ids']);
				if (empty($term_ids) || !is_array($term_ids)) {
					continue;
				}

				$qty        = (int) $item->get_quantity();
				$line_total = (float) $item->get_total();

				foreach ($term_ids as $term_id) {
					$term_id = (int) $term_id;
					if (!isset($tags[$term_id])) {
						$term = get_term($term_id, 'product_tag');
						if (!$term || is_wp_error($term)) {
							continue;
						}

						$tags[$term_id] = [
							'term_id'      => $term_id,
							'name'         => $term->name,
							'slug'         => $term->slug,
							'total_sales'  => 0.0,
							'total_qty'    => 0,
							'order_ids'    => [],
						];
					}

					$tags[$term_id]['total_sales'] += $line_total;
					$tags[$term_id]['total_qty']   += $qty;
					$tags[$term_id]['order_ids'][$order_id] = true;
				}
			}
		}

		$rows = [];
		foreach ($tags as $tag) {
			$tag['total_orders'] = count($tag['order_ids']);
			unset($tag['order_ids']);
			$rows[] = $tag;
		}

		// Sort by total_sales descending.
		usort($rows, static function ($a, $b) {
			return $b['total_sales'] <=> $a['total_sales'];
		});

		if ($for_csv) {
			$out = [];
			foreach ($rows as $row) {
				$total_sales = $row['total_sales'];
				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$total_sales = wc_format_decimal($total_sales, wc_get_price_decimals());
				}

				$out[] = [
					$row['term_id'],
					$row['name'],
					$row['slug'],
					$total_sales,
					$row['total_qty'],
					$row['total_orders'],
				];
			}
			return $out;
		}

		return $rows;
	}

	/**
	 * Get data for "Page Not Found 404 Errors" report.
	 *
	 * Aggregates unique 404 URLs from the admin activity log and tracks:
	 * - Last seen timestamp
	 * - Hit count
	 * - Last user ID (if any)
	 *
	 * @param bool $for_csv Whether to format for CSV output.
	 * @return array
	 */
	private static function get_404_errors_data(bool $for_csv = false): array {
		global $wpdb;

		$table = $wpdb->prefix . 'um_admin_activity';

		// Fetch up to 10,000 recent 404 entries for aggregation.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, user_id, extra, created_at
				 FROM {$table}
				WHERE action = %s
				ORDER BY created_at DESC
				LIMIT 10000",
				'404_hit'
			),
			ARRAY_A
		);

		if (empty($rows)) {
			return [];
		}

		$urls = [];

		foreach ($rows as $row) {
			$extra = !empty($row['extra']) ? json_decode($row['extra'], true) : [];
			if (!is_array($extra)) {
				$extra = [];
			}

			$url = isset($extra['url']) ? (string) $extra['url'] : '';
			if ($url === '') {
				continue;
			}

			$key       = $url;
			$created   = isset($row['created_at']) ? (string) $row['created_at'] : '';
			$user_id   = isset($row['user_id']) ? (int) $row['user_id'] : 0;

			if (!isset($urls[$key])) {
				$urls[$key] = [
					'url'          => $url,
					'last_seen'    => $created,
					'hits'         => 1,
					'last_user_id' => $user_id,
				];
			} else {
				$urls[$key]['hits']++;
				// Update last seen if this entry is newer.
				if ($created !== '' && $created > $urls[$key]['last_seen']) {
					$urls[$key]['last_seen']    = $created;
					$urls[$key]['last_user_id'] = $user_id;
				}
			}
		}

		$data = array_values($urls);

		// Sort by hits descending (most frequent at top), then last_seen descending.
		usort($data, static function ($a, $b) {
			if ($a['hits'] === $b['hits']) {
				return strcmp($b['last_seen'], $a['last_seen']);
			}
			return $b['hits'] <=> $a['hits'];
		});

		if ($for_csv) {
			$out = [];
			foreach ($data as $row) {
				$out[] = [
					$row['url'],
					$row['last_seen'],
					$row['hits'],
					$row['last_user_id'],
				];
			}
			return $out;
		}

		return $data;
	}

	/**
	 * Get data for "Search Queries" report.
	 *
	 * Aggregates search_query actions from admin activity log by search term:
	 * - Total searches
	 * - Last searched timestamp
	 * - Last user ID
	 * - Example URL (last seen)
	 *
	 * @param bool $for_csv Whether to format for CSV output.
	 * @return array
	 */
	private static function get_search_queries_data(bool $for_csv = false): array {
		global $wpdb;

		$table = $wpdb->prefix . 'um_admin_activity';

		// Fetch up to 10,000 recent searches for aggregation.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, user_id, extra, created_at
				 FROM {$table}
				WHERE action = %s
				ORDER BY created_at DESC
				LIMIT 10000",
				'search_query'
			),
			ARRAY_A
		);

		if (empty($rows)) {
			return [];
		}

		$terms = [];

		foreach ($rows as $row) {
			$extra = !empty($row['extra']) ? json_decode($row['extra'], true) : [];
			if (!is_array($extra)) {
				$extra = [];
			}

			$query = isset($extra['query']) ? (string) $extra['query'] : '';
			if ($query === '') {
				continue;
			}

			$post_type = isset($extra['post_type']) ? (string) $extra['post_type'] : '';

			$key       = mb_strtolower($query) . '|' . $post_type;
			$created   = isset($row['created_at']) ? (string) $row['created_at'] : '';
			$user_id   = isset($row['user_id']) ? (int) $row['user_id'] : 0;
			$url       = isset($extra['url']) ? (string) $extra['url'] : '';

			if (!isset($terms[$key])) {
				$terms[$key] = [
					'query'       => $query,
					'post_type'   => $post_type,
					'last_seen'   => $created,
					'hits'        => 1,
					'last_user_id'=> $user_id,
					'last_url'    => $url,
				];
			} else {
				$terms[$key]['hits']++;
				if ($created !== '' && $created > $terms[$key]['last_seen']) {
					$terms[$key]['last_seen']    = $created;
					$terms[$key]['last_user_id'] = $user_id;
					$terms[$key]['last_url']     = $url;
				}
			}
		}

		$data = array_values($terms);

		// Sort by hits descending, then last_seen descending.
		usort($data, static function ($a, $b) {
			if ($a['hits'] === $b['hits']) {
				return strcmp($b['last_seen'], $a['last_seen']);
			}
			return $b['hits'] <=> $a['hits'];
		});

		if ($for_csv) {
			$out = [];
			foreach ($data as $row) {
				$out[] = [
					$row['query'],
					$row['post_type'],
					$row['hits'],
					$row['last_seen'],
					$row['last_user_id'],
					$row['last_url'],
				];
			}
			return $out;
		}

		return $data;
	}

	/**
	 * Get data for "Page Views" report.
	 *
	 * @param bool $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_page_views_data(bool $for_csv = false): array {
		return self::get_post_view_aggregate_for_action('page_view', $for_csv);
	}

	/**
	 * Get data for "Post Views" report.
	 *
	 * @param bool $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_post_views_data(bool $for_csv = false): array {
		return self::get_post_view_aggregate_for_action('post_view', $for_csv);
	}

	/**
	 * Get data for "Product Views" report.
	 *
	 * @param bool $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_product_views_data(bool $for_csv = false): array {
		return self::get_post_view_aggregate_for_action('product_view', $for_csv);
	}

	/**
	 * Get data for "Page Category Archives Views" report.
	 *
	 * @param bool $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_page_category_views_data(bool $for_csv = false): array {
		return self::get_term_view_aggregate_for_action('page_category_view', $for_csv);
	}

	/**
	 * Get data for "Post Category Archives Views" report.
	 *
	 * @param bool $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_post_category_views_data(bool $for_csv = false): array {
		return self::get_term_view_aggregate_for_action('post_category_view', $for_csv);
	}

	/**
	 * Get data for "Post Tag Archives Views" report.
	 *
	 * @param bool $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_post_tag_views_data(bool $for_csv = false): array {
		return self::get_term_view_aggregate_for_action('post_tag_view', $for_csv);
	}

	/**
	 * Get data for "Product Category Archives Views" report.
	 *
	 * @param bool $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_product_category_views_data(bool $for_csv = false): array {
		return self::get_term_view_aggregate_for_action('product_category_view', $for_csv);
	}

	/**
	 * Get data for "Product Tag Archives Views" report.
	 *
	 * @param bool $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_product_tag_views_data(bool $for_csv = false): array {
		return self::get_term_view_aggregate_for_action('product_tag_view', $for_csv);
	}

	/**
	 * Aggregate view data for post-based view actions (page, post, product).
	 *
	 * @param string $action  Activity log action key.
	 * @param bool   $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_post_view_aggregate_for_action(string $action, bool $for_csv = false): array {
		global $wpdb;

		$table = $wpdb->prefix . 'um_admin_activity';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, extra, created_at
				 FROM {$table}
				WHERE action = %s
				ORDER BY created_at DESC
				LIMIT 10000",
				$action
			),
			ARRAY_A
		);

		if (empty($rows)) {
			return [];
		}

		$posts = [];

		foreach ($rows as $row) {
			$extra = !empty($row['extra']) ? json_decode($row['extra'], true) : [];
			if (!is_array($extra)) {
				$extra = [];
			}

			$post_id = isset($extra['post_id']) ? (int) $extra['post_id'] : 0;
			if ($post_id <= 0) {
				continue;
			}

			$title     = isset($extra['title']) ? (string) $extra['title'] : '';
			$slug      = isset($extra['slug']) ? (string) $extra['slug'] : '';
			$permalink = isset($extra['permalink']) ? (string) $extra['permalink'] : '';
			$created   = isset($row['created_at']) ? (string) $row['created_at'] : '';
			$user_id   = isset($row['user_id']) ? (int) $row['user_id'] : 0;

			if (!isset($posts[$post_id])) {
				$posts[$post_id] = [
					'post_id'      => $post_id,
					'title'        => $title,
					'slug'         => $slug,
					'permalink'    => $permalink,
					'last_seen'    => $created,
					'hits'         => 1,
					'last_user_id' => $user_id,
				];
			} else {
				$posts[$post_id]['hits']++;
				if ($created !== '' && $created > $posts[$post_id]['last_seen']) {
					$posts[$post_id]['last_seen']    = $created;
					$posts[$post_id]['last_user_id'] = $user_id;
				}
			}
		}

		// Enrich with current post data where possible.
		foreach ($posts as $post_id => &$data) {
			$post = get_post($post_id);
			if ($post instanceof WP_Post) {
				$data['title']     = get_the_title($post);
				$data['slug']      = $post->post_name;
				$permalink         = get_permalink($post);
				$data['permalink'] = $permalink ? (string) $permalink : $data['permalink'];
			}
		}
		unset($data);

		$data = array_values($posts);

		// Sort by hits descending, then last_seen descending.
		usort($data, static function ($a, $b) {
			if ($a['hits'] === $b['hits']) {
				return strcmp($b['last_seen'], $a['last_seen']);
			}
			return $b['hits'] <=> $a['hits'];
		});

		if ($for_csv) {
			$out = [];
			foreach ($data as $row) {
				$out[] = [
					$row['post_id'],
					$row['title'],
					$row['slug'],
					$row['permalink'],
					$row['hits'],
					$row['last_seen'],
					$row['last_user_id'],
				];
			}
			return $out;
		}

		return $data;
	}

	/**
	 * Aggregate view data for term-based view actions (categories/tags).
	 *
	 * @param string $action  Activity log action key.
	 * @param bool   $for_csv Whether to format for CSV.
	 * @return array
	 */
	private static function get_term_view_aggregate_for_action(string $action, bool $for_csv = false): array {
		global $wpdb;

		$table = $wpdb->prefix . 'um_admin_activity';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, extra, created_at
				 FROM {$table}
				WHERE action = %s
				ORDER BY created_at DESC
				LIMIT 10000",
				$action
			),
			ARRAY_A
		);

		if (empty($rows)) {
			return [];
		}

		$terms = [];

		foreach ($rows as $row) {
			$extra = !empty($row['extra']) ? json_decode($row['extra'], true) : [];
			if (!is_array($extra)) {
				$extra = [];
			}

			$term_id  = isset($extra['term_id']) ? (int) $extra['term_id'] : 0;
			$taxonomy = isset($extra['taxonomy']) ? (string) $extra['taxonomy'] : '';
			if ($term_id <= 0 || $taxonomy === '') {
				continue;
			}

			$name      = isset($extra['name']) ? (string) $extra['name'] : '';
			$slug      = isset($extra['slug']) ? (string) $extra['slug'] : '';
			$permalink = isset($extra['permalink']) ? (string) $extra['permalink'] : '';
			$created   = isset($row['created_at']) ? (string) $row['created_at'] : '';
			$user_id   = isset($row['user_id']) ? (int) $row['user_id'] : 0;

			$key = $taxonomy . '|' . $term_id;

			if (!isset($terms[$key])) {
				$terms[$key] = [
					'term_id'      => $term_id,
					'taxonomy'     => $taxonomy,
					'name'         => $name,
					'slug'         => $slug,
					'permalink'    => $permalink,
					'last_seen'    => $created,
					'hits'         => 1,
					'last_user_id' => $user_id,
				];
			} else {
				$terms[$key]['hits']++;
				if ($created !== '' && $created > $terms[$key]['last_seen']) {
					$terms[$key]['last_seen']    = $created;
					$terms[$key]['last_user_id'] = $user_id;
				}
			}
		}

		// Enrich with current term data where possible.
		foreach ($terms as $key => &$data) {
			$term = get_term_by('id', $data['term_id'], $data['taxonomy']);
			if ($term instanceof WP_Term) {
				$data['name'] = $term->name;
				$data['slug'] = $term->slug;
				$link         = get_term_link($term);
				if (!is_wp_error($link)) {
					$data['permalink'] = (string) $link;
				}
			}
		}
		unset($data);

		$data = array_values($terms);

		// Sort by hits descending, then last_seen descending.
		usort($data, static function ($a, $b) {
			if ($a['hits'] === $b['hits']) {
				return strcmp($b['last_seen'], $a['last_seen']);
			}
			return $b['hits'] <=> $a['hits'];
		});

		if ($for_csv) {
			$out = [];
			foreach ($data as $row) {
				$out[] = [
					$row['term_id'],
					$row['name'],
					$row['slug'],
					$row['permalink'],
					$row['hits'],
					$row['last_seen'],
					$row['last_user_id'],
				];
			}
			return $out;
		}

		return $data;
	}

	/**
	 * Get data for "Order Payment Methods" report.
	 *
	 * Attempts to surface how the customer actually paid, including Stripe UPE wallets
	 * (Apple Pay, Google Pay, PayPal via Stripe, etc.) when available.
	 *
	 * @param bool $for_csv Whether to format numeric values for CSV output.
	 * @return array
	 */
	private static function get_order_payment_methods_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$statuses = array_keys(function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [
			'wc-pending'    => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold'    => 'On hold',
			'wc-completed'  => 'Completed',
			'wc-cancelled'  => 'Cancelled',
			'wc-refunded'   => 'Refunded',
			'wc-failed'     => 'Failed',
		]);

		$args = [
			'limit'  => -1,
			'status' => $statuses,
			'type'   => 'shop_order',
			'return' => 'objects',
			'orderby'=> 'date',
			'order'  => 'DESC',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		$data   = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$order_total = (float) $order->get_total();

			// Only include orders where the collected total is greater than zero.
			if ($order_total <= 0.0) {
				continue;
			}

			$order_id      = $order->get_id();
			$order_date    = $order->get_date_created() ? $order->get_date_created()->date_i18n('Y-m-d H:i:s') : '';
			$billing_email = $order->get_billing_email();
			$status        = $order->get_status();

			$gateway_id    = (string) $order->get_payment_method();
			$gateway_title = (string) $order->get_payment_method_title();

			// Derive a "payment source" that explains how they paid.
			$payment_source = '';

			// Stripe UPE wallet detection (Apple Pay, Google Pay, etc.).
			$upe_type = (string) $order->get_meta('_stripe_upe_payment_type', true);
			if ($upe_type !== '') {
				$normalized = strtolower(trim($upe_type));
				if ($normalized === 'apple_pay') {
					$payment_source = __('Apple Pay (Stripe)', 'user-manager');
				} elseif ($normalized === 'google_pay') {
					$payment_source = __('Google Pay (Stripe)', 'user-manager');
				} elseif ($normalized === 'link') {
					$payment_source = __('Link (Stripe)', 'user-manager');
				} elseif ($normalized === 'paypal') {
					$payment_source = __('PayPal (via Stripe)', 'user-manager');
				} else {
					$label = ucwords(str_replace('_', ' ', $normalized));
					$payment_source = sprintf(
						/* translators: %s is Stripe UPE payment type label. */
						__('Stripe UPE: %s', 'user-manager'),
						$label
					);
				}
			}

			// Direct PayPal detection if no Stripe wallet was found.
			if ($payment_source === '') {
				$has_paypal_meta = $order->get_meta('_ppcp_paypal_order_id', true) !== ''
					|| $order->get_meta('_paypal_transaction_id', true) !== '';
				if (stripos($gateway_id, 'paypal') !== false || stripos($gateway_title, 'paypal') !== false || $has_paypal_meta) {
					$payment_source = __('PayPal', 'user-manager');
				}
			}

			// Fallback to the gateway title or ID if we still don't have a nice label.
			if ($payment_source === '') {
				if ($gateway_title !== '') {
					$payment_source = $gateway_title;
				} elseif ($gateway_id !== '') {
					$payment_source = $gateway_id;
				} else {
					$payment_source = __('Unknown', 'user-manager');
				}
			}

			if ($for_csv) {
				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$order_total_formatted = wc_format_decimal($order_total, wc_get_price_decimals());
				} else {
					$order_total_formatted = $order_total;
				}

				$data[] = [
					$order_id,
					$order_date,
					$billing_email,
					$status,
					$gateway_title ?: $gateway_id,
					$payment_source,
					$order_total_formatted,
				];
			} else {
				$data[] = [
					'order_id'        => $order_id,
					'order_date'      => $order_date,
					'billing_email'   => $billing_email,
					'status'          => $status,
					'payment_method'  => $gateway_title ?: $gateway_id,
					'payment_source'  => $payment_source,
					'order_total'     => $order_total,
				];
			}
		}

		return $data;
	}

	/**
	 * Get "Order Refunds" data.
	 *
	 * @param bool $for_csv Whether to format numeric values for CSV output.
	 * @return array
	 */
	private static function get_orders_with_refunds_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$statuses = array_keys(function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [
			'wc-pending'    => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold'    => 'On hold',
			'wc-completed'  => 'Completed',
			'wc-cancelled'  => 'Cancelled',
			'wc-refunded'   => 'Refunded',
			'wc-failed'     => 'Failed',
		]);

		$args = [
			'limit'  => -1,
			'status' => $statuses,
			'type'   => 'shop_order',
			'return' => 'objects',
			'orderby'=> 'date',
			'order'  => 'DESC',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		$data   = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$order_total     = (float) $order->get_total();
			$refunded_amount = (float) $order->get_total_refunded();

			if ($refunded_amount <= 0) {
				continue;
			}

			$billing_email = $order->get_billing_email();
			$status        = $order->get_status(); // e.g. 'refunded', 'completed'

			// Determine full vs partial refund with small tolerance.
			$refund_type = 'partial';
			if ($order_total > 0 && abs($refunded_amount - $order_total) < 0.01) {
				$refund_type = 'full';
			} elseif ($order_total == 0 && $refunded_amount > 0) {
				$refund_type = 'full';
			}

			$order_date = $order->get_date_created() ? $order->get_date_created()->date_i18n('Y-m-d H:i:s') : '';

			if ($for_csv) {
				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$order_total_formatted     = wc_format_decimal($order_total, wc_get_price_decimals());
					$refunded_amount_formatted = wc_format_decimal($refunded_amount, wc_get_price_decimals());
				} else {
					$order_total_formatted     = $order_total;
					$refunded_amount_formatted = $refunded_amount;
				}

				$data[] = [
					$order->get_id(),
					$order_date,
					$billing_email,
					$order_total_formatted,
					$refunded_amount_formatted,
					$refund_type,
					$status,
				];
			} else {
				$data[] = [
					'order_id'        => $order->get_id(),
					'order_date'      => $order_date,
					'billing_email'   => $billing_email,
					'order_total'     => $order_total,
					'refunded_amount' => $refunded_amount,
					'refund_type'     => $refund_type,
					'status'          => $status,
				];
			}
		}

		return $data;
	}

	/**
	 * Get data for "Orders Processing by Number of Days" report.
	 *
	 * @param bool $for_csv Whether to format for CSV (no formatting needed currently).
	 * @return array
	 */
	private static function get_orders_processing_days_data(bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		// Only processing orders.
		$args = [
			'limit'  => -1,
			'status' => ['wc-processing'],
			'type'   => 'shop_order',
			'return' => 'objects',
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		$data   = [];

		$now_ts = current_time('timestamp');

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$order_id   = $order->get_id();
			$created    = $order->get_date_created();
			$order_date = $created ? $created->date_i18n('Y-m-d H:i:s') : '';
			$created_ts = $created ? $created->getTimestamp() : 0;

			if (!$created_ts) {
				continue;
			}

			$diff_seconds = max(0, $now_ts - $created_ts);
			$days         = (int) floor($diff_seconds / DAY_IN_SECONDS);

			$row = [
				'order_id'      => $order_id,
				'order_date'    => $order_date,
				'billing_email' => $order->get_billing_email(),
				'status'        => $order->get_status(),
				'days'          => $days,
			];

			if ($for_csv) {
				$data[] = [
					$order_id,
					$order_date,
					$row['billing_email'],
					$row['status'],
					$days,
				];
			} else {
				$data[] = $row;
			}
		}

		// Sort by days descending so oldest (largest days) at top.
		usort($data, static function ($a, $b) {
			return $b['days'] <=> $a['days'];
		});

		return $data;
	}

	/**
	 * Get aggregated shipment counts and totals by period based on order completion date.
	 *
	 * @param string $period  'day', 'week', or 'month'.
	 * @param bool   $for_csv Whether to format numeric values for CSV output.
	 * @return array
	 */
	private static function get_orders_shipments_aggregated_data(string $period, bool $for_csv = false): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		$period = in_array($period, ['day', 'week', 'month'], true) ? $period : 'day';

		// To keep this report performant on large stores, cap the query to a
		// recent window of completed orders instead of loading the entire history.
		$max_orders   = 10000; // Hard cap on number of orders to consider.
		$days_back    = 365;   // Only look at the last 12 months of completions.
		$now_ts       = current_time('timestamp');
		$cutoff_ts    = strtotime('-' . $days_back . ' days', $now_ts);
		$cutoff_mysql = gmdate('Y-m-d H:i:s', $cutoff_ts);

		$args = [
			'limit'         => $max_orders,
			'status'        => ['wc-completed'],
			'type'          => 'shop_order',
			'return'        => 'objects',
			'orderby'       => 'date_completed',
			'order'         => 'DESC',
			// Only consider orders completed after the cutoff and up to "now".
			'date_completed'=> '>' . $cutoff_mysql,
		];

		$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
		$groups = [];

		foreach ($orders as $order) {
			if (!$order instanceof WC_Order) {
				continue;
			}

			$completed = $order->get_date_completed();
			if (!$completed) {
				continue;
			}

			// Use the site's local time when grouping.
			$completed_ts   = $completed->getOffsetTimestamp();
			$completed_date = date_i18n('Y-m-d H:i:s', $completed_ts);

			switch ($period) {
				case 'week':
					$key = date_i18n('o-\WW', $completed_ts); // e.g. 2026-W03
					break;
				case 'month':
					$key = date_i18n('Y-m', $completed_ts); // e.g. 2026-01
					break;
				case 'day':
				default:
					$key = date_i18n('Y-m-d', $completed_ts); // e.g. 2026-01-14
					break;
			}

			if (!isset($groups[$key])) {
				$groups[$key] = [
					'key'          => $key,
					'completed_at' => $completed_date,
					'count'        => 0,
					'total'        => 0.0,
				];
			}

			$groups[$key]['count']++;
			$groups[$key]['total'] += (float) $order->get_total();
		}

		// Sort keys by most recent completion date (newest first).
		uksort(
			$groups,
			static function (string $a, string $b): int {
				// Keys are already formatted in chronological-friendly order, so compare strings descending.
				return strcmp($b, $a);
			}
		);

		$data = [];
		foreach ($groups as $group) {
			if ($for_csv) {
				$total = $group['total'];
				if (function_exists('wc_format_decimal') && function_exists('wc_get_price_decimals')) {
					$total = wc_format_decimal($total, wc_get_price_decimals());
				}
				$data[] = [
					$group['key'],
					$group['count'],
					$total,
				];
			} else {
				$data[] = $group;
			}
		}

		return $data;
	}

	/**
	 * Shared renderer for "Order Total Shipments" aggregated reports.
	 *
	 * @param string $period 'day', 'week', or 'month'.
	 */
	private static function render_orders_shipments_aggregated_report(string $period): void {
		if (!class_exists('WooCommerce')) {
			echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager') . '</p>';
			return;
		}

		$period = in_array($period, ['day', 'week', 'month'], true) ? $period : 'day';

		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_orders_shipments_aggregated_data($period, false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));

		switch ($period) {
			case 'week':
				$report_slug = 'orders-shipments-by-week';
				$label_date  = __('Week', 'user-manager');
				break;
			case 'month':
				$report_slug = 'orders-shipments-by-month';
				$label_date  = __('Month', 'user-manager');
				break;
			case 'day':
			default:
				$report_slug = 'orders-shipments-by-day';
				$label_date  = __('Date', 'user-manager');
				break;
		}

		$current_url = add_query_arg('report', $report_slug, User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No completed orders found for this period.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo esc_html($label_date); ?></th>
						<th><?php esc_html_e('Completed Orders', 'user-manager'); ?></th>
						<th><?php esc_html_e('Total Order Amount', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td><?php echo esc_html($row['key']); ?></td>
							<td><?php echo esc_html($row['count']); ?></td>
							<td>
								<?php
								if (function_exists('wc_price')) {
									echo wp_kses_post(wc_price($row['total']));
								} else {
									echo esc_html(number_format_i18n($row['total'], 2));
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render "Order Tracking Numbers" report using WooCommerce Shipment Tracking data.
	 *
	 * Shows the most recently completed orders first.
	 */
/**
	 * Render "Order Tracking Number Notes" report based on order notes containing "has been shipped".
	 *
	 * Newest notes (by note date) appear at the top.
	 */
/**
	 * Render "User Data" report listing users and common profile/billing/shipping fields.
	 */
	private static function render_user_data_report(): void {
		$per_page = 50;
		$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset   = ($paged - 1) * $per_page;

		$all_rows = self::get_user_data_rows(false);
		$total    = count($all_rows);

		if ($total > $per_page) {
			$rows = array_slice($all_rows, $offset, $per_page);
		} else {
			$rows = $all_rows;
		}

		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = add_query_arg('report', 'user-data', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));

		?>
		<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
			<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
				<?php esc_html_e('Export to CSV', 'user-manager'); ?>
			</a>
		</div>

		<?php if (empty($rows)) : ?>
			<p><?php esc_html_e('No users found.', 'user-manager'); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('User ID', 'user-manager'); ?></th>
						<th><?php esc_html_e('Username', 'user-manager'); ?></th>
						<th><?php esc_html_e('Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('First Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Last Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Display Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Roles', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing First Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Last Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Company', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Address 1', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Address 2', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing City', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing State', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Postcode', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Country', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Phone', 'user-manager'); ?></th>
						<th><?php esc_html_e('Billing Email', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping First Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping Last Name', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping Company', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping Address 1', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping Address 2', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping City', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping State', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping Postcode', 'user-manager'); ?></th>
						<th><?php esc_html_e('Shipping Country', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td><?php echo esc_html($row['user_id']); ?></td>
							<td><?php echo esc_html($row['username'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['email'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['first_name'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['last_name'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['display_name'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['roles'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_first'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_last'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_company'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_address1'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_address2'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_city'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_state'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_postcode'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_country'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_phone'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['billing_email'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['shipping_first'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['shipping_last'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['shipping_company'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['shipping_address1'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['shipping_address2'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['shipping_city'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['shipping_state'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['shipping_postcode'] ?: '—'); ?></td>
							<td><?php echo esc_html($row['shipping_country'] ?: '—'); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<?php self::render_pagination($paged, $total_pages, $total, $current_url); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Build rows for User Data report.
	 *
	 * @param bool $for_csv If true, returns flat rows ready for CSV.
	 * @return array
	 */
	private static function get_user_data_rows(bool $for_csv = false): array {
		// Fetch all users; for very large sites this could be heavy, but matches other report patterns.
		$query = new WP_User_Query([
			'number' => -1,
			'fields' => ['ID', 'user_login', 'user_email', 'display_name'],
		]);

		$users = $query->get_results();
		if (empty($users)) {
			return [];
		}

		$rows = [];

		foreach ($users as $user) {
			$user_id = (int) $user->ID;

			$first_name = get_user_meta($user_id, 'first_name', true);
			$last_name  = get_user_meta($user_id, 'last_name', true);

			$billing_first_name = get_user_meta($user_id, 'billing_first_name', true);
			$billing_last_name  = get_user_meta($user_id, 'billing_last_name', true);
			$billing_company    = get_user_meta($user_id, 'billing_company', true);
			$billing_address_1  = get_user_meta($user_id, 'billing_address_1', true);
			$billing_address_2  = get_user_meta($user_id, 'billing_address_2', true);
			$billing_city       = get_user_meta($user_id, 'billing_city', true);
			$billing_state      = get_user_meta($user_id, 'billing_state', true);
			$billing_postcode   = get_user_meta($user_id, 'billing_postcode', true);
			$billing_country    = get_user_meta($user_id, 'billing_country', true);
			$billing_phone      = get_user_meta($user_id, 'billing_phone', true);
			$billing_email      = get_user_meta($user_id, 'billing_email', true);

			$shipping_first_name = get_user_meta($user_id, 'shipping_first_name', true);
			$shipping_last_name  = get_user_meta($user_id, 'shipping_last_name', true);
			$shipping_company    = get_user_meta($user_id, 'shipping_company', true);
			$shipping_address_1  = get_user_meta($user_id, 'shipping_address_1', true);
			$shipping_address_2  = get_user_meta($user_id, 'shipping_address_2', true);
			$shipping_city       = get_user_meta($user_id, 'shipping_city', true);
			$shipping_state      = get_user_meta($user_id, 'shipping_state', true);
			$shipping_postcode   = get_user_meta($user_id, 'shipping_postcode', true);
			$shipping_country    = get_user_meta($user_id, 'shipping_country', true);

			$roles = [];
			if (!empty($user->roles) && is_array($user->roles)) {
				$roles = $user->roles;
			}

			$row = [
				'user_id'          => $user_id,
				'username'         => $user->user_login,
				'email'            => $user->user_email,
				'first_name'       => $first_name,
				'last_name'        => $last_name,
				'display_name'     => $user->display_name,
				'roles'            => implode(', ', $roles),
				'billing_first'    => $billing_first_name,
				'billing_last'     => $billing_last_name,
				'billing_company'  => $billing_company,
				'billing_address1' => $billing_address_1,
				'billing_address2' => $billing_address_2,
				'billing_city'     => $billing_city,
				'billing_state'    => $billing_state,
				'billing_postcode' => $billing_postcode,
				'billing_country'  => $billing_country,
				'billing_phone'    => $billing_phone,
				'billing_email'    => $billing_email,
				'shipping_first'   => $shipping_first_name,
				'shipping_last'    => $shipping_last_name,
				'shipping_company' => $shipping_company,
				'shipping_address1'=> $shipping_address_1,
				'shipping_address2'=> $shipping_address_2,
				'shipping_city'    => $shipping_city,
				'shipping_state'   => $shipping_state,
				'shipping_postcode'=> $shipping_postcode,
				'shipping_country' => $shipping_country,
			];

			if ($for_csv) {
				$rows[] = [
					$row['user_id'],
					$row['username'],
					$row['email'],
					$row['first_name'],
					$row['last_name'],
					$row['display_name'],
					$row['roles'],
					$row['billing_first'],
					$row['billing_last'],
					$row['billing_company'],
					$row['billing_address1'],
					$row['billing_address2'],
					$row['billing_city'],
					$row['billing_state'],
					$row['billing_postcode'],
					$row['billing_country'],
					$row['billing_phone'],
					$row['billing_email'],
					$row['shipping_first'],
					$row['shipping_last'],
					$row['shipping_company'],
					$row['shipping_address1'],
					$row['shipping_address2'],
					$row['shipping_city'],
					$row['shipping_state'],
					$row['shipping_postcode'],
					$row['shipping_country'],
				];
			} else {
				// For on-screen table, return full associative rows; all fields are shown in the table.
				$rows[] = $row;
			}
		}

		return $rows;
	}

	private static function render_pagination(int $paged, int $total_pages, int $total, string $current_url): void {
		?>
		<div class="tablenav" style="margin-top:12px;">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo esc_html(number_format_i18n($total)); ?> <?php esc_html_e('items', 'user-manager'); ?></span>
				<span class="pagination-links">
					<?php
					$prev_url = add_query_arg('paged', max(1, $paged - 1), $current_url);
					$next_url = add_query_arg('paged', min($total_pages, $paged + 1), $current_url);
					?>
					<a class="first-page <?php echo $paged <= 1 ? 'disabled' : ''; ?>" href="<?php echo esc_url(add_query_arg('paged', 1, $current_url)); ?>">&laquo;</a>
					<a class="prev-page <?php echo $paged <= 1 ? 'disabled' : ''; ?>" href="<?php echo esc_url($prev_url); ?>">&lsaquo;</a>
					<span class="paging-input">
						<?php echo esc_html($paged); ?> <?php esc_html_e('of', 'user-manager'); ?> <span class="total-pages"><?php echo esc_html($total_pages); ?></span>
					</span>
					<a class="next-page <?php echo $paged >= $total_pages ? 'disabled' : ''; ?>" href="<?php echo esc_url($next_url); ?>">&rsaquo;</a>
					<a class="last-page <?php echo $paged >= $total_pages ? 'disabled' : ''; ?>" href="<?php echo esc_url(add_query_arg('paged', $total_pages, $current_url)); ?>">&raquo;</a>
				</span>
			</div>
		</div>
		<?php
	}

	private static function export_csv(string $filename, array $headers, array $data): void {
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . esc_attr($filename) . '-' . date('Y-m-d') . '.csv"');
		header('Pragma: no-cache');
		header('Expires: 0');
		
		$output = fopen('php://output', 'w');
		
		// Add BOM for Excel compatibility
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
		
		// Output headers
		fputcsv($output, $headers);
		
		// Output data
		foreach ($data as $row) {
			fputcsv($output, $row);
		}
		
		fclose($output);
		exit;
	}
}

