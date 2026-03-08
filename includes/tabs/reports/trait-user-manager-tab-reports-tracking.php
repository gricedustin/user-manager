<?php
/**
 * Extracted methods from class-user-manager-tab-reports.php.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Tab_Reports_Tracking_Trait {
		public static function export_orders_tracking_numbers_csv(): void {
			if (!class_exists('WooCommerce')) {
				return;
			}
	
			// Mirror the on-screen logic but without pagination, capped to a
			// reasonable recent window of completed orders.
			$max_orders   = 5000;
			$days_back    = 365;
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
				'date_completed'=> '>' . $cutoff_mysql,
			];
	
			$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
			$rows   = [];
	
			foreach ($orders as $order) {
				if (!$order instanceof WC_Order) {
					continue;
				}
	
				$order_id  = $order->get_id();
				$completed = $order->get_date_completed();
				$completed_at = $completed ? $completed->date_i18n('Y-m-d H:i:s') : '';
	
				$tracking_items = get_post_meta($order_id, '_wc_shipment_tracking_items', true);
				if (empty($tracking_items) || !is_array($tracking_items)) {
					continue;
				}
	
				foreach ($tracking_items as $tracking_item) {
					if (!is_array($tracking_item)) {
						continue;
					}
	
					$provider = isset($tracking_item['tracking_provider']) ? (string) $tracking_item['tracking_provider'] : '';
					$number   = isset($tracking_item['tracking_number']) ? (string) $tracking_item['tracking_number'] : '';
					$shipped  = isset($tracking_item['date_shipped']) ? (string) $tracking_item['date_shipped'] : '';
					$carrier  = isset($tracking_item['custom_provider']) ? (string) $tracking_item['custom_provider'] : $provider;
	
					if ($shipped !== '') {
						if (ctype_digit((string) $shipped)) {
							$shipped_ts      = (int) $shipped;
							$shipped_display = date_i18n('Y-m-d', $shipped_ts);
						} else {
							$shipped_display = date_i18n('Y-m-d', strtotime($shipped));
						}
					} else {
						$shipped_display = '';
					}
	
					$rows[] = [
						$order->get_order_number(),
						$order_id,
						$completed_at,
						$carrier,
						$provider,
						$number,
						$shipped_display,
					];
				}
			}
	
			// Ensure newest first by completion date.
			usort(
				$rows,
				static function (array $a, array $b): int {
					$at = strtotime($a[2] ?? '') ?: 0;
					$bt = strtotime($b[2] ?? '') ?: 0;
					return $bt <=> $at;
				}
			);
	
			self::export_csv(
				'orders-tracking-numbers',
				[
					'Order Number',
					'Order ID',
					'Completed At',
					'Carrier',
					'Provider',
					'Tracking Number',
					'Date Shipped',
				],
				$rows
			);
		}
	
		

		public static function export_orders_tracking_notes_csv(): void {
			if (!class_exists('WooCommerce')) {
				return;
			}
	
			global $wpdb;
	
			$comments_table = $wpdb->comments;
			$posts_table    = $wpdb->posts;
	
			$sql = $wpdb->prepare(
				"SELECT c.comment_ID, c.comment_post_ID, c.comment_content, c.comment_date
				 FROM {$comments_table} c
				 INNER JOIN {$posts_table} p ON c.comment_post_ID = p.ID
				WHERE c.comment_type = 'order_note'
				  AND c.comment_approved = 1
				  AND p.post_type = 'shop_order'
				  AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending', 'wc-refunded')
				  AND c.comment_content LIKE %s
				ORDER BY c.comment_date DESC",
				'%has been shipped%'
			);
	
			$rows   = $wpdb->get_results($sql);
			$export = [];
	
			foreach ($rows as $row) {
				$order      = wc_get_order($row->comment_post_ID);
				$order_num  = $order instanceof WC_Order ? $order->get_order_number() : $row->comment_post_ID;
				$note_ts    = strtotime($row->comment_date);
				$note_date  = $note_ts ? date_i18n('Y-m-d H:i:s', $note_ts) : $row->comment_date;
	
				$export[] = [
					$order_num,
					(int) $row->comment_post_ID,
					$note_date,
					(string) $row->comment_content,
				];
			}
	
			self::export_csv(
				'orders-tracking-notes',
				[
					'Order Number',
					'Order ID',
					'Note Date',
					'Note Content',
				],
				$export
			);
		}
	
		

		private static function render_orders_tracking_numbers_report(): void {
			if (!class_exists('WooCommerce')) {
				echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager'); ?></p>
				<?php
				return;
			}
	
			$per_page = 50;
			$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
			$offset   = ($paged - 1) * $per_page;
	
			// Limit to a recent window of completed orders to avoid timeouts on
			// large stores while still surfacing the most recent tracking data.
			$max_orders   = 2000;
			$days_back    = 365;
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
				'date_completed'=> '>' . $cutoff_mysql,
			];
	
			$orders = function_exists('wc_get_orders') ? wc_get_orders($args) : [];
			$rows   = [];
	
			foreach ($orders as $order) {
				if (!$order instanceof WC_Order) {
					continue;
				}
	
				$order_id   = $order->get_id();
				$completed  = $order->get_date_completed();
				$completed_display = $completed
					? $completed->date_i18n(get_option('date_format') . ' ' . get_option('time_format'))
					: '';
	
				$tracking_items = get_post_meta($order_id, '_wc_shipment_tracking_items', true);
				if (empty($tracking_items) || !is_array($tracking_items)) {
					continue;
				}
	
				foreach ($tracking_items as $tracking_item) {
					if (!is_array($tracking_item)) {
						continue;
					}
	
					$provider = isset($tracking_item['tracking_provider']) ? (string) $tracking_item['tracking_provider'] : '';
					$number   = isset($tracking_item['tracking_number']) ? (string) $tracking_item['tracking_number'] : '';
					$shipped  = isset($tracking_item['date_shipped']) ? (string) $tracking_item['date_shipped'] : '';
					$carrier  = isset($tracking_item['custom_provider']) ? (string) $tracking_item['custom_provider'] : $provider;
	
					if ($shipped !== '') {
						// Many setups store date_shipped as a timestamp; normalize for display.
						if (ctype_digit((string) $shipped)) {
							$shipped_ts      = (int) $shipped;
							$shipped_display = date_i18n(get_option('date_format'), $shipped_ts);
						} else {
							$shipped_display = date_i18n(get_option('date_format'), strtotime($shipped));
						}
					} else {
						$shipped_display = '';
					}
	
					$rows[] = [
						'order_id'          => $order_id,
						'order_number'      => $order->get_order_number(),
						'completed_at'      => $completed ? $completed->date_i18n('Y-m-d H:i:s') : '',
						'completed_display' => $completed_display,
						'provider'          => $provider,
						'carrier'           => $carrier,
						'tracking_number'   => $number,
						'date_shipped'      => $shipped_display,
					];
				}
			}
	
			// Already ordered by completion date in the query, but ensure newest first.
			usort(
				$rows,
				static function (array $a, array $b): int {
					$at = strtotime($a['completed_at'] ?? '') ?: 0;
					$bt = strtotime($b['completed_at'] ?? '') ?: 0;
					return $bt <=> $at;
				}
			);
	
			$total = count($rows);
			if ($total > $per_page) {
				$rows = array_slice($rows, $offset, $per_page);
			}
	
			$total_pages = max(1, (int) ceil($total / $per_page));
			$current_url = add_query_arg('report', 'orders-tracking-numbers', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));
	
			?>
			<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
				<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
					<?php esc_html_e('Export to CSV', 'user-manager'); ?>
				</a>
			</div>
	
			<?php if (empty($rows)) : ?>
				<p><?php esc_html_e('No tracking data found for completed orders.', 'user-manager'); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e('Order', 'user-manager'); ?></th>
							<th><?php esc_html_e('Completed', 'user-manager'); ?></th>
							<th><?php esc_html_e('Carrier', 'user-manager'); ?></th>
							<th><?php esc_html_e('Tracking Number', 'user-manager'); ?></th>
							<th><?php esc_html_e('Date Shipped', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($rows as $row) : ?>
							<tr>
								<td>
									<a href="<?php echo esc_url(admin_url('post.php?post=' . $row['order_id'] . '&action=edit')); ?>">
										<?php echo esc_html('#' . $row['order_number']); ?>
									</a>
								</td>
								<td><?php echo esc_html($row['completed_display'] ?: '—'); ?></td>
								<td><?php echo esc_html($row['carrier'] ?: $row['provider'] ?: '—'); ?></td>
								<td><?php echo esc_html($row['tracking_number'] ?: '—'); ?></td>
								<td><?php echo esc_html($row['date_shipped'] ?: '—'); ?></td>
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
	
		

		private static function render_orders_tracking_notes_report(): void {
			if (!class_exists('WooCommerce')) {
				echo '<p>' . esc_html__('WooCommerce is required for this report.', 'user-manager'); ?></p>
				<?php
				return;
			}
	
			global $wpdb;
	
			$per_page = 50;
			$paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
			$offset   = ($paged - 1) * $per_page;
	
			$comments_table = $wpdb->comments;
			$posts_table    = $wpdb->posts;
	
			$total_sql = $wpdb->prepare(
				"SELECT COUNT(*)
				 FROM {$comments_table} c
				 INNER JOIN {$posts_table} p ON c.comment_post_ID = p.ID
				WHERE c.comment_type = 'order_note'
				  AND c.comment_approved = 1
				  AND p.post_type = 'shop_order'
				  AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending', 'wc-refunded')
				  AND c.comment_content LIKE %s",
				'%has been shipped%'
			);
			$total_found = (int) $wpdb->get_var($total_sql);
	
			// Find order notes that contain "has been shipped" and belong to orders, newest first.
			$sql = $wpdb->prepare(
				"SELECT c.comment_ID, c.comment_post_ID, c.comment_content, c.comment_date
				 FROM {$comments_table} c
				 INNER JOIN {$posts_table} p ON c.comment_post_ID = p.ID
				WHERE c.comment_type = 'order_note'
				  AND c.comment_approved = 1
				  AND p.post_type = 'shop_order'
				  AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending', 'wc-refunded')
				  AND c.comment_content LIKE %s
				ORDER BY c.comment_date DESC
				LIMIT %d OFFSET %d",
				'%has been shipped%',
				$per_page,
				$offset
			);
	
			$rows        = $wpdb->get_results($sql);
			$total_pages = max(1, (int) ceil($total_found / $per_page));
	
			$current_url = add_query_arg('report', 'orders-tracking-notes', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS));
	
			?>
			<div style="margin-top:30px; margin-bottom:20px; clear:both; display:block;">
				<a href="<?php echo esc_url(add_query_arg('export', 'csv', $current_url)); ?>" class="button">
					<?php esc_html_e('Export to CSV', 'user-manager'); ?>
				</a>
			</div>
	
			<?php if (empty($rows)) : ?>
				<p><?php esc_html_e('No shipped order notes found.', 'user-manager'); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e('Order', 'user-manager'); ?></th>
							<th><?php esc_html_e('Note Date', 'user-manager'); ?></th>
							<th><?php esc_html_e('Note Content', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($rows as $row) : ?>
							<tr>
								<td>
									<?php
									$order      = wc_get_order($row->comment_post_ID);
									$order_num  = $order instanceof WC_Order ? $order->get_order_number() : $row->comment_post_ID;
									?>
									<a href="<?php echo esc_url(admin_url('post.php?post=' . (int) $row->comment_post_ID . '&action=edit')); ?>">
										<?php echo esc_html('#' . $order_num); ?>
									</a>
								</td>
								<td>
									<?php
									$note_ts = strtotime($row->comment_date);
									echo esc_html(
										$note_ts
											? date_i18n(
												get_option('date_format') . ' ' . get_option('time_format'),
												$note_ts
											)
											: $row->comment_date
									);
									?>
								</td>
								<td><?php echo esc_html($row->comment_content); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
	
				<?php if ($total_pages > 1) : ?>
					<?php self::render_pagination($paged, $total_pages, $total_found, $current_url); ?>
				<?php endif; ?>
			<?php endif; ?>
			<?php
		}
	
		

}
