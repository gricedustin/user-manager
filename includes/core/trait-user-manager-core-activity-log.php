<?php
/**
 * Extracted methods from class-user-manager-core.php.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Activity_Log_Trait {
		public static function get_activity_log(int $per_page = 0, int $offset = 0, ?string $action_filter = null, ?string $tool_filter = null): array {
			global $wpdb;
			$table = $wpdb->prefix . 'um_admin_activity';

			$has_action_filter = !empty($action_filter);
			$has_tool_filter = !empty($tool_filter);
			$where = '';
			if ($has_action_filter && $has_tool_filter) {
				$where = $wpdb->prepare(
					' WHERE action = %s AND tool LIKE %s',
					$action_filter,
					'%' . $wpdb->esc_like((string) $tool_filter) . '%'
				);
			} elseif ($has_action_filter) {
				$where = $wpdb->prepare(' WHERE action = %s', $action_filter);
			} elseif ($has_tool_filter) {
				$where = $wpdb->prepare(
					' WHERE tool LIKE %s',
					'%' . $wpdb->esc_like((string) $tool_filter) . '%'
				);
			}

			$total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}{$where}");

			// Build query with pagination
			$query = "SELECT id, action, user_id, tool, extra, created_by, created_at FROM {$table}{$where} ORDER BY created_at DESC";
			if ($per_page > 0) {
				$query .= $wpdb->prepare(' LIMIT %d OFFSET %d', $per_page, $offset);
			} else {
				// Legacy behavior: limit to 500 if no pagination specified
				$query .= ' LIMIT 500';
			}
			
			$rows = $wpdb->get_results($query, ARRAY_A);
			if (empty($rows)) {
				return ['entries' => [], 'total' => $total];
			}
			
			foreach ($rows as &$row) {
				$row['extra'] = !empty($row['extra']) ? json_decode($row['extra'], true) : [];
			}
			unset($row);
			
			return ['entries' => $rows, 'total' => $total];
		}
	
		

		public static function add_activity_log(string $action, int $user_id, string $tool, array $extra = []): array {
			$settings = self::get_settings();
			$log_enabled = array_key_exists('log_activity', $settings) ? !empty($settings['log_activity']) : true;
			$debug_enabled = !empty($settings['log_activity_debug']);
			
			$debug = [
				'action' => $action,
				'user_id' => $user_id,
				'tool' => $tool,
				'extra' => $extra,
				'log_enabled' => $log_enabled,
				'debug_enabled' => $debug_enabled,
				'result' => 'skipped',
			];
			
			if (!$log_enabled && !$debug_enabled) {
				$debug['reason'] = 'logging_disabled';
				return $debug;
			}
			
			global $wpdb;
			$table = $wpdb->prefix . 'um_admin_activity';
			$data = [
				'action' => sanitize_text_field($action),
				'user_id' => (int) $user_id,
				'tool' => sanitize_text_field($tool),
				'extra' => wp_json_encode($extra),
				'created_by' => get_current_user_id(),
				'created_at' => current_time('mysql'),
			];
			
			$result = $wpdb->insert(
				$table,
				$data,
				['%s','%d','%s','%s','%d','%s']
			);
			
			if (false === $result) {
				$debug['result'] = 'db_error';
				$debug['error'] = $wpdb->last_error;
				self::maybe_debug_log('Failed to insert activity log', [
					'error' => $wpdb->last_error,
					'action' => $action,
					'user_id' => $user_id,
					'tool' => $tool,
				]);
			} else {
				$debug['result'] = 'success';
				$debug['insert_id'] = (int) $wpdb->insert_id;
			}
			
			return $debug;
		}
	
		

		public static function ajax_get_activity_details(): void {
			if (!current_user_can('manage_options')) {
				wp_send_json_error(['message' => __('Unauthorized', 'user-manager')]);
			}
	
			check_ajax_referer('user_manager_get_activity_details', '_wpnonce');
	
			$entry_id = isset($_GET['entry_id']) ? absint($_GET['entry_id']) : 0;
			
			if (empty($entry_id)) {
				wp_send_json_error(['message' => __('Invalid entry ID', 'user-manager')]);
			}
	
			global $wpdb;
			$table = $wpdb->prefix . 'um_admin_activity';
			$entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $entry_id), ARRAY_A);
			
			if (!$entry) {
				wp_send_json_error(['message' => __('Activity entry not found.', 'user-manager')]);
			}
	
			$entry['extra'] = !empty($entry['extra']) ? json_decode($entry['extra'], true) : [];
	
			$user = get_user_by('ID', $entry['user_id']);
			$created_by = get_user_by('ID', $entry['created_by']);
			$extra = $entry['extra'] ?? [];
			$created_ts = !empty($entry['created_at']) ? strtotime($entry['created_at']) : current_time('timestamp');
			
			// For post actions, user_id is the editor, not the post author
			$is_post_action = in_array($entry['action'], ['post_created', 'post_updated', 'post_status_changed'], true);
			$is_plugin_action = in_array($entry['action'], ['plugin_activated', 'plugin_deactivated'], true);
	
			// Determine action label and badge
			$action_label = $entry['action'];
			$badge_class = 'um-status-info';
			$notification_msg = '';
			$email_sent = !empty($extra['email_sent']);
			
			switch ($entry['action']) {
				case 'user_created':
					$action_label = __('User Created', 'user-manager');
					$badge_class = 'um-status-success';
					$notification_msg = $email_sent 
						? __('User created successfully and email sent.', 'user-manager')
						: __('User created successfully.', 'user-manager');
					break;
				case 'user_updated':
					$action_label = __('User Updated', 'user-manager');
					$badge_class = 'um-status-warning';
					$notification_msg = $email_sent 
						? __('Existing user updated successfully and email sent.', 'user-manager')
						: __('Existing user updated successfully.', 'user-manager');
					break;
				case 'password_reset':
					$action_label = __('Password Reset', 'user-manager');
					$badge_class = 'um-status-info';
					$notification_msg = $email_sent 
						? __('Password reset successfully and email sent.', 'user-manager')
						: __('Password reset successfully.', 'user-manager');
					break;
				case 'email_sent':
					$action_label = __('Email Sent', 'user-manager');
					$badge_class = 'um-status-success';
					$notification_msg = __('Email sent successfully.', 'user-manager');
					break;
				case 'admin_login':
					$action_label = __('WP Admin Login', 'user-manager');
					$badge_class = 'um-status-info';
					$notification_msg = __('Admin user logged into wp-admin.', 'user-manager');
					break;
				case 'login_as_start':
					$action_label = __('Login As – Temporary Password Set', 'user-manager');
					$badge_class = 'um-status-warning';
					$target_login = isset($extra['target_user_login']) ? $extra['target_user_login'] : '';
					$target_email = isset($extra['target_user_email']) ? $extra['target_user_email'] : '';
					if ($target_login || $target_email) {
						$notification_msg = sprintf(
							/* translators: 1: username, 2: email */
							__('Temporary password set for user %1$s (%2$s).', 'user-manager'),
							$target_login ?: __('(unknown)', 'user-manager'),
							$target_email ?: __('(unknown)', 'user-manager')
						);
					} else {
						$notification_msg = __('Temporary password set via Login As.', 'user-manager');
					}
					break;
				case 'login_as_restore':
					$action_label = __('Login As – Password Restored', 'user-manager');
					$badge_class = 'um-status-success';
					$notification_msg = __('Original password restored for Login As session.', 'user-manager');
					break;
				case 'post_created':
					$action_label = __('Post Created', 'user-manager');
					$badge_class = 'um-status-success';
					$notification_msg = __('Post created successfully.', 'user-manager');
					break;
				case 'post_updated':
					$action_label = __('Post Updated', 'user-manager');
					$badge_class = 'um-status-warning';
					$notification_msg = __('Post updated successfully.', 'user-manager');
					break;
				case 'post_status_changed':
					$action_label = __('Post Status Changed', 'user-manager');
					$badge_class = 'um-status-info';
					$old_status = isset($extra['old_status']) ? $extra['old_status'] : '';
					$new_status = isset($extra['new_status']) ? $extra['new_status'] : '';
					$notification_msg = sprintf(__('Post status changed from %s to %s.', 'user-manager'), $old_status, $new_status);
					break;
				case 'plugin_activated':
					$action_label = __('Plugin Activated', 'user-manager');
					$badge_class = 'um-status-success';
					$plugin_name = isset($extra['plugin_name']) ? $extra['plugin_name'] : '';
					$notification_msg = $plugin_name 
						? sprintf(__('Plugin "%s" activated successfully.', 'user-manager'), $plugin_name)
						: __('Plugin activated successfully.', 'user-manager');
					break;
				case 'plugin_deactivated':
					$action_label = __('Plugin Deactivated', 'user-manager');
					$badge_class = 'um-status-warning';
					$plugin_name = isset($extra['plugin_name']) ? $extra['plugin_name'] : '';
					$notification_msg = $plugin_name 
						? sprintf(__('Plugin "%s" deactivated.', 'user-manager'), $plugin_name)
						: __('Plugin deactivated.', 'user-manager');
					break;
				case 'settings_updated':
					$action_label = __('Settings Updated', 'user-manager');
					$badge_class = 'um-status-warning';
					$changed_count = isset($extra['changed_count']) ? (int) $extra['changed_count'] : 0;
					$section_label = isset($extra['settings_section']) ? (string) $extra['settings_section'] : __('general', 'user-manager');
					$notification_msg = sprintf(
						/* translators: 1: section key, 2: number of changed settings */
						__('Updated "%1$s" settings (%2$d change(s)).', 'user-manager'),
						$section_label,
						$changed_count
					);
					break;
				case 'coupon_lookup':
					$action_label = __('Coupon Lookup by Email', 'user-manager');
					$badge_class = 'um-status-info';
					$email = isset($extra['email']) ? $extra['email'] : '';
					$result_count = isset($extra['result_count']) ? (int) $extra['result_count'] : 0;
					$notification_msg = $email
						? sprintf(__('Searched for coupons with email %1$s; %2$d result(s).', 'user-manager'), $email, $result_count)
						: __('Coupon lookup was performed.', 'user-manager');
					break;
				case 'blog_post_import':
					$action_label = __('Blog Post Importer', 'user-manager');
					$badge_class = 'um-status-success';
					$created_count = isset($extra['created_count']) ? (int) $extra['created_count'] : 0;
					$notification_msg = sprintf(__('Created %d blog post(s).', 'user-manager'), $created_count);
					if (!empty($extra['apply_random_image'])) {
						$notification_msg .= ' ' . __('Random featured images applied.', 'user-manager');
					}
					if (!empty($extra['spread_dates_used'])) {
						$notification_msg .= ' ' . __('Dates spread between first and last.', 'user-manager');
					}
					break;
			}
	
			// Build HTML output
			ob_start();
			?>
			<table class="widefat" style="margin-bottom: 16px;">
				<tr>
					<td style="width: 140px;"><strong><?php esc_html_e('Action', 'user-manager'); ?></strong></td>
					<td><span class="um-status-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($action_label); ?></span></td>
				</tr>
				<?php if ($is_post_action) : ?>
				<tr>
					<td><strong><?php esc_html_e('Post', 'user-manager'); ?></strong></td>
					<td>
						<?php if (!empty($extra['post_title'])) : ?>
							<strong><?php echo esc_html($extra['post_title']); ?></strong>
							<?php if (!empty($extra['edit_link'])) : ?>
								<br><a href="<?php echo esc_url($extra['edit_link']); ?>" target="_blank"><?php esc_html_e('Edit Post', 'user-manager'); ?></a>
							<?php endif; ?>
							<?php if (!empty($extra['view_link'])) : ?>
								| <a href="<?php echo esc_url($extra['view_link']); ?>" target="_blank"><?php esc_html_e('View Post', 'user-manager'); ?></a>
							<?php endif; ?>
						<?php else : ?>
							<em><?php esc_html_e('N/A', 'user-manager'); ?></em>
						<?php endif; ?>
					</td>
				</tr>
				<?php if (!empty($extra['post_type_label'])) : ?>
				<tr>
					<td><strong><?php esc_html_e('Post Type', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($extra['post_type_label']); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ($entry['action'] === 'post_status_changed') : ?>
				<tr>
					<td><strong><?php esc_html_e('Status Change', 'user-manager'); ?></strong></td>
					<td>
						<?php 
						$old_status = isset($extra['old_status']) ? $extra['old_status'] : '';
						$new_status = isset($extra['new_status']) ? $extra['new_status'] : '';
						?>
						<span style="text-decoration: line-through; color: #646970;"><?php echo esc_html(ucfirst($old_status)); ?></span>
						→
						<strong style="color: #0073aa;"><?php echo esc_html(ucfirst($new_status)); ?></strong>
					</td>
				</tr>
				<?php endif; ?>
				<?php elseif ($is_plugin_action) : ?>
				<tr>
					<td><strong><?php esc_html_e('Plugin', 'user-manager'); ?></strong></td>
					<td>
						<?php if (!empty($extra['plugin_name'])) : ?>
							<strong><?php echo esc_html($extra['plugin_name']); ?></strong>
							<?php if (!empty($extra['plugin_url'])) : ?>
								<br><a href="<?php echo esc_url($extra['plugin_url']); ?>" target="_blank"><?php esc_html_e('Manage Plugins', 'user-manager'); ?></a>
							<?php endif; ?>
						<?php else : ?>
							<em><?php esc_html_e('N/A', 'user-manager'); ?></em>
						<?php endif; ?>
					</td>
				</tr>
				<?php if (!empty($extra['plugin_version'])) : ?>
				<tr>
					<td><strong><?php esc_html_e('Version', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($extra['plugin_version']); ?></td>
				</tr>
				<?php endif; ?>
				<?php if (!empty($extra['plugin_author'])) : ?>
				<tr>
					<td><strong><?php esc_html_e('Author', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($extra['plugin_author']); ?></td>
				</tr>
				<?php endif; ?>
				<?php if (isset($extra['network_wide']) && $extra['network_wide']) : ?>
				<tr>
					<td><strong><?php esc_html_e('Network Wide', 'user-manager'); ?></strong></td>
					<td><span class="um-status-badge um-status-info"><?php esc_html_e('Yes', 'user-manager'); ?></span></td>
				</tr>
				<?php endif; ?>
				<?php else : ?>
				<tr>
					<td><strong><?php esc_html_e('User Email', 'user-manager'); ?></strong></td>
					<td>
						<?php if ($user) : ?>
							<a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>"><?php echo esc_html($user->user_email); ?></a>
						<?php else : ?>
							<em><?php esc_html_e('User deleted', 'user-manager'); ?></em>
						<?php endif; ?>
					</td>
				</tr>
				<?php if ($entry['action'] === 'coupon_lookup' && !empty($extra['email'])) : ?>
				<tr>
					<td><strong><?php esc_html_e('Email searched', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($extra['email']); ?> (<?php echo (int) ($extra['result_count'] ?? 0); ?> <?php esc_html_e('result(s)', 'user-manager'); ?>)</td>
				</tr>
				<?php endif; ?>
				<?php if ($entry['action'] === 'blog_post_import' && !empty($extra['post_titles'])) : ?>
				<tr>
					<td><strong><?php esc_html_e('Posts created', 'user-manager'); ?></strong></td>
					<td><ul style="margin:0; padding-left:20px;"><?php foreach ((array) $extra['post_titles'] as $pt) : ?><li><?php echo esc_html($pt); ?></li><?php endforeach; ?></ul></td>
				</tr>
				<?php endif; ?>
				<?php endif; ?>
				<tr>
					<td><strong><?php esc_html_e('Tool/Source', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($entry['tool']); ?></td>
				</tr>
				<?php if (!empty($extra['source_file'])) : ?>
				<tr>
					<td><strong><?php esc_html_e('Source File', 'user-manager'); ?></strong></td>
					<td><code><?php echo esc_html($extra['source_file']); ?></code></td>
				</tr>
				<?php endif; ?>
				<tr>
					<td><strong><?php esc_html_e('Email Sent', 'user-manager'); ?></strong></td>
					<td>
						<?php if ($email_sent) : ?>
							<span class="um-status-badge um-status-success"><?php esc_html_e('Yes', 'user-manager'); ?></span>
						<?php else : ?>
							<span class="um-status-badge um-status-info"><?php esc_html_e('No', 'user-manager'); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<?php if (!empty($extra['template_id'])) : ?>
				<tr>
					<td><strong><?php esc_html_e('Email Template', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($extra['template_id']); ?></td>
				</tr>
				<?php endif; ?>
				<?php if (!empty($extra['login_url'])) : ?>
				<tr>
					<td><strong><?php esc_html_e('Login URL', 'user-manager'); ?></strong></td>
					<td><code><?php echo esc_html($extra['login_url']); ?></code></td>
				</tr>
				<?php endif; ?>
				<tr>
					<td><strong><?php esc_html_e('Performed By', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($created_by ? $created_by->display_name : __('Unknown', 'user-manager')); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e('Date/Time', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $created_ts)); ?></td>
				</tr>
			</table>
			
			<?php if (!empty($extra['new_values']) && $entry['action'] === 'user_created') : ?>
			<h4 style="margin: 16px 0 8px;"><?php esc_html_e('User Details Created', 'user-manager'); ?></h4>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Field', 'user-manager'); ?></th>
						<th><?php esc_html_e('Value', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($extra['new_values'] as $field => $value) : ?>
					<tr>
						<td><strong><?php echo esc_html(ucwords(str_replace('_', ' ', $field))); ?></strong></td>
						<td>
							<?php 
							if (is_bool($value)) {
								echo $value ? '<span class="um-status-badge um-status-success">' . esc_html__('Yes', 'user-manager') . '</span>' : '<span class="um-status-badge um-status-info">' . esc_html__('No', 'user-manager') . '</span>';
							} else {
								echo esc_html($value ?: '—');
							}
							?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
			
			<?php if (!empty($extra['old_values']) && !empty($extra['new_values']) && $entry['action'] === 'user_updated') : ?>
			<h4 style="margin: 16px 0 8px;"><?php esc_html_e('Changes Made', 'user-manager'); ?></h4>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Field', 'user-manager'); ?></th>
						<th><?php esc_html_e('Old Value', 'user-manager'); ?></th>
						<th><?php esc_html_e('New Value', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($extra['new_values'] as $field => $new_value) : 
						$old_value = $extra['old_values'][$field] ?? '';
						$changed = $old_value !== $new_value;
					?>
					<tr style="<?php echo $changed ? 'background: #fff8e5;' : ''; ?>">
						<td><strong><?php echo esc_html(ucwords(str_replace('_', ' ', $field))); ?></strong></td>
						<td><?php echo esc_html($old_value ?: '—'); ?></td>
						<td>
							<?php if ($changed) : ?>
								<strong style="color: #0073aa;"><?php echo esc_html($new_value ?: '—'); ?></strong>
							<?php else : ?>
								<?php echo esc_html($new_value ?: '—'); ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
					<?php if (!empty($extra['password_changed'])) : ?>
					<tr style="background: #fff8e5;">
						<td><strong><?php esc_html_e('Password', 'user-manager'); ?></strong></td>
						<td>••••••••</td>
						<td><strong style="color: #0073aa;"><?php esc_html_e('Changed', 'user-manager'); ?></strong></td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			<?php endif; ?>
			
			<?php if ($is_post_action && !empty($extra['changes']) && $entry['action'] === 'post_updated') : ?>
			<h4 style="margin: 16px 0 8px;"><?php esc_html_e('Changes Made', 'user-manager'); ?></h4>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Field', 'user-manager'); ?></th>
						<th><?php esc_html_e('Old Value', 'user-manager'); ?></th>
						<th><?php esc_html_e('New Value', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($extra['changes'] as $field => $change) : 
						$old_value = $change['old'] ?? '';
						$new_value = $change['new'] ?? '';
						$field_label = ucwords(str_replace('_', ' ', str_replace('post_', '', $field)));
					?>
					<tr style="background: #fff8e5;">
						<td><strong><?php echo esc_html($field_label); ?></strong></td>
						<td style="max-width: 300px; word-wrap: break-word;">
							<?php 
							if ($field === 'post_content') {
								echo esc_html(wp_trim_words($old_value, 20));
							} else {
								echo esc_html($old_value ?: '—');
							}
							?>
						</td>
						<td style="max-width: 300px; word-wrap: break-word;">
							<strong style="color: #0073aa;">
							<?php 
							if ($field === 'post_content') {
								echo esc_html(wp_trim_words($new_value, 20));
							} else {
								echo esc_html($new_value ?: '—');
							}
							?>
							</strong>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>

			<?php if ($entry['action'] === 'settings_updated' && !empty($extra['changed_fields']) && is_array($extra['changed_fields'])) : ?>
			<h4 style="margin: 16px 0 8px;"><?php esc_html_e('Settings Changes', 'user-manager'); ?></h4>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Field', 'user-manager'); ?></th>
						<th><?php esc_html_e('Old Value', 'user-manager'); ?></th>
						<th><?php esc_html_e('New Value', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($extra['changed_fields'] as $change_row) : ?>
						<?php
						$field = isset($change_row['field']) ? (string) $change_row['field'] : '';
						$old_value = $change_row['old'] ?? '';
						$new_value = $change_row['new'] ?? '';
						?>
						<tr style="background: #fff8e5;">
							<td><code><?php echo esc_html($field); ?></code></td>
							<td><?php echo esc_html(self::format_activity_detail_scalar($old_value)); ?></td>
							<td><strong style="color: #0073aa;"><?php echo esc_html(self::format_activity_detail_scalar($new_value)); ?></strong></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
	
			<?php
			$all_logged_rows = self::flatten_activity_detail_rows($extra);
			?>
			<?php if (!empty($all_logged_rows)) : ?>
			<h4 style="margin: 16px 0 8px;"><?php esc_html_e('All Logged Form Data', 'user-manager'); ?></h4>
			<table class="widefat striped">
				<thead>
					<tr>
						<th style="width: 36%;"><?php esc_html_e('Field', 'user-manager'); ?></th>
						<th><?php esc_html_e('Value', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($all_logged_rows as $row) : ?>
						<?php
						$field_path = isset($row['path']) ? (string) $row['path'] : '';
						$raw_value  = $row['value'] ?? '';
						$is_masked  = self::is_sensitive_activity_detail_path($field_path);
						$formatted  = $is_masked ? __('[redacted]', 'user-manager') : self::format_activity_detail_scalar($raw_value);
						?>
						<tr>
							<td><code><?php echo esc_html($field_path); ?></code></td>
							<td style="white-space: pre-wrap; word-break: break-word;"><?php echo esc_html($formatted); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
			
			<!-- Notification Message -->
			<div style="margin-top: 16px; padding: 12px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
				<strong style="display: block; margin-bottom: 4px;">
					<span class="dashicons dashicons-yes-alt" style="color: #155724;"></span>
					<?php esc_html_e('Admin Notification Message:', 'user-manager'); ?>
				</strong>
				<span style="color: #155724;"><?php echo esc_html($notification_msg); ?></span>
			</div>
			<?php
			$html = ob_get_clean();
	
			wp_send_json_success(['html' => $html]);
		}
	
		

		private static function flatten_activity_detail_rows($value, string $path = ''): array {
			$rows = [];
	
			if (is_object($value)) {
				$value = (array) $value;
			}
	
			if (is_array($value)) {
				if (empty($value) && $path !== '') {
					$rows[] = ['path' => $path, 'value' => []];
				} else {
					foreach ($value as $key => $child_value) {
						$key       = (string) $key;
						$child_key = ($path === '') ? $key : $path . '[' . $key . ']';
						$rows      = array_merge($rows, self::flatten_activity_detail_rows($child_value, $child_key));
					}
				}
				return $rows;
			}
	
			$rows[] = [
				'path'  => $path !== '' ? $path : 'value',
				'value' => $value,
			];
	
			return $rows;
		}
	
		

		private static function format_activity_detail_scalar($value): string {
			if (is_bool($value)) {
				return $value ? 'true' : 'false';
			}
			if ($value === null) {
				return 'null';
			}
			if (is_scalar($value)) {
				return (string) $value;
			}
	
			$encoded = wp_json_encode($value);
			return is_string($encoded) ? $encoded : '';
		}
	
		

		private static function is_sensitive_activity_detail_path(string $path): bool {
			$needle = strtolower($path);
			$sensitive_tokens = [
				'password',
				'user_pass',
				'api_key',
				'token',
				'secret',
				'nonce',
				'authorization',
				'cookie',
			];
	
			foreach ($sensitive_tokens as $token) {
				if ($token !== '' && strpos($needle, $token) !== false) {
					return true;
				}
			}
	
			return false;
		}
	
		
		

}
