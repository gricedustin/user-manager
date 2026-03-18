<?php
/**
 * Activity Log tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Activity_Log {

	public static function render(): void {
		global $wpdb;
		$per_page = 50;
		$paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset = ($paged - 1) * $per_page;
		$action_filter = isset($_GET['action_filter']) ? sanitize_text_field(wp_unslash($_GET['action_filter'])) : '';
		$tool_filter = isset($_GET['tool_filter']) ? sanitize_text_field(wp_unslash($_GET['tool_filter'])) : '';

		// Get activity log with pagination and filtering
		$result = User_Manager_Core::get_activity_log($per_page, $offset, $action_filter ?: null, $tool_filter ?: null);
		$log = $result['entries'];
		$total = $result['total'];
		$total_pages = max(1, (int) ceil($total / $per_page));
		$is_reports_context = (isset($_GET['tab']) && sanitize_key(wp_unslash($_GET['tab'])) === User_Manager_Core::TAB_REPORTS)
			|| (isset($_GET['reports_section']) && sanitize_key(wp_unslash($_GET['reports_section'])) === 'admin-log');
		$current_url = $is_reports_context
			? add_query_arg('reports_section', 'admin-log', User_Manager_Core::get_page_url(User_Manager_Core::TAB_REPORTS))
			: User_Manager_Core::get_page_url(User_Manager_Core::TAB_ACTIVITY_LOG);
		$settings = User_Manager_Core::get_settings();
		$addon_sections = class_exists('User_Manager_Tab_Addons')
			? User_Manager_Tab_Addons::get_addon_sections_for_reports($settings)
			: [];
		uasort($addon_sections, static function (array $a, array $b): int {
			$a_label = isset($a['label']) ? (string) $a['label'] : '';
			$b_label = isset($b['label']) ? (string) $b['label'] : '';
			return strcasecmp($a_label, $b_label);
		});
		
		// Get all unique actions for filter dropdown
		$table = $wpdb->prefix . 'um_admin_activity';
		$all_actions = $wpdb->get_col("SELECT DISTINCT action FROM {$table} ORDER BY action ASC");
		$tool_counts = $wpdb->get_results("SELECT tool, COUNT(*) AS total FROM {$table} GROUP BY tool", ARRAY_A);
		$tool_count_map = [];
		if (is_array($tool_counts)) {
			foreach ($tool_counts as $tool_count_row) {
				$tool_name = isset($tool_count_row['tool']) ? trim((string) $tool_count_row['tool']) : '';
				if ($tool_name === '') {
					continue;
				}
				$tool_count_map[strtolower($tool_name)] = isset($tool_count_row['total']) ? (int) $tool_count_row['total'] : 0;
			}
		}
		
		?>
		<div class="um-admin-grid">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-admin-plugins"></span>
					<h2><?php esc_html_e('Add-ons Connected to Admin Log', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Every add-on is listed here with its active status and quick links to both Add-ons settings and filtered Admin Log results.', 'user-manager'); ?></p>
					<?php if (empty($addon_sections)) : ?>
						<p><?php esc_html_e('No add-ons metadata found.', 'user-manager'); ?></p>
					<?php else : ?>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e('Add-on', 'user-manager'); ?></th>
									<th><?php esc_html_e('Status', 'user-manager'); ?></th>
									<th><?php esc_html_e('Tool Matches', 'user-manager'); ?></th>
									<th><?php esc_html_e('Links', 'user-manager'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($addon_sections as $addon_key => $addon_meta) : ?>
									<?php
									$addon_label = isset($addon_meta['label']) ? (string) $addon_meta['label'] : '';
									$addon_active = !empty($addon_meta['active']);
									$addon_settings_url = add_query_arg('addon_section', sanitize_key((string) $addon_key), User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS));
									$addon_log_url = add_query_arg(
										[
											'tool_filter' => $addon_label,
											'action_filter' => '',
											'paged' => 1,
										],
										$current_url
									);
									$tool_match_total = isset($tool_count_map[strtolower($addon_label)]) ? (int) $tool_count_map[strtolower($addon_label)] : 0;
									?>
									<tr>
										<td><strong><?php echo esc_html($addon_label); ?></strong></td>
										<td>
											<?php if ($addon_active) : ?>
												<span class="um-status-badge um-status-success"><?php esc_html_e('Active', 'user-manager'); ?></span>
											<?php else : ?>
												<span class="um-status-badge um-status-secondary"><?php esc_html_e('Inactive', 'user-manager'); ?></span>
											<?php endif; ?>
										</td>
										<td><?php echo esc_html(number_format_i18n($tool_match_total)); ?></td>
										<td>
											<a class="button button-small" href="<?php echo esc_url($addon_settings_url); ?>"><?php esc_html_e('Open Add-on', 'user-manager'); ?></a>
											<a class="button button-small" href="<?php echo esc_url($addon_log_url); ?>"><?php esc_html_e('View in Admin Log', 'user-manager'); ?></a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-list-view"></span>
					<h2><?php esc_html_e('Activity Log', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<?php if (empty($log) && empty($action_filter) && empty($tool_filter)) : ?>
						<p><?php esc_html_e('No activity logged yet.', 'user-manager'); ?></p>
					<?php else : ?>
						<!-- Action Filter -->
						<div style="margin-bottom: 20px;">
							<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display: inline-block;">
								<input type="hidden" name="page" value="<?php echo esc_attr(User_Manager_Core::SETTINGS_PAGE_SLUG); ?>" />
								<input type="hidden" name="tab" value="<?php echo esc_attr($is_reports_context ? User_Manager_Core::TAB_REPORTS : User_Manager_Core::TAB_ACTIVITY_LOG); ?>" />
								<?php if ($is_reports_context) : ?>
									<input type="hidden" name="reports_section" value="admin-log" />
								<?php endif; ?>
								<label for="um-action-filter" style="margin-right: 8px;">
									<strong><?php esc_html_e('Filter by Action:', 'user-manager'); ?></strong>
								</label>
								<select name="action_filter" id="um-action-filter" onchange="this.form.submit();" style="min-width: 200px;">
									<option value=""><?php esc_html_e('All Actions', 'user-manager'); ?></option>
									<?php foreach ($all_actions as $action) : 
										$action_label = self::get_action_label($action);
									?>
										<option value="<?php echo esc_attr($action); ?>" <?php selected($action_filter, $action); ?>>
											<?php echo esc_html($action_label); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<label for="um-tool-filter" style="margin: 0 8px 0 12px;">
									<strong><?php esc_html_e('Filter by Add-on Tool:', 'user-manager'); ?></strong>
								</label>
								<select name="tool_filter" id="um-tool-filter" onchange="this.form.submit();" style="min-width: 220px;">
									<option value=""><?php esc_html_e('All Add-on Tools', 'user-manager'); ?></option>
									<?php foreach ($addon_sections as $addon_meta) :
										$addon_label = isset($addon_meta['label']) ? (string) $addon_meta['label'] : '';
										if ($addon_label === '') {
											continue;
										}
										?>
										<option value="<?php echo esc_attr($addon_label); ?>" <?php selected($tool_filter, $addon_label); ?>>
											<?php echo esc_html($addon_label); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<?php if ($action_filter || $tool_filter) : ?>
									<a href="<?php echo esc_url($current_url); ?>" class="button" style="margin-left: 8px;">
										<?php esc_html_e('Clear Filter', 'user-manager'); ?>
									</a>
								<?php endif; ?>
							</form>
						</div>
						
					<?php if (empty($log)) : ?>
							<p><?php esc_html_e('No activity found for the selected filter.', 'user-manager'); ?></p>
						<?php else : ?>
						<table class="um-activity-table">
							<thead>
								<tr>
									<th><?php esc_html_e('Action', 'user-manager'); ?></th>
									<th><?php esc_html_e('User', 'user-manager'); ?></th>
									<th><?php esc_html_e('Tool', 'user-manager'); ?></th>
									<th><?php esc_html_e('Triggered By', 'user-manager'); ?></th>
									<th><?php esc_html_e('Date', 'user-manager'); ?></th>
									<th><?php esc_html_e('Details', 'user-manager'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($log as $entry) : ?>
									<?php 
									$user = get_user_by('ID', $entry['user_id']);
									$created_by = get_user_by('ID', $entry['created_by']);
									$import_id = $entry['extra']['import_id'] ?? null;
									?>
									<tr>
										<td>
											<?php 
											switch ($entry['action']) {
												case 'user_created':
													echo '<span class="um-status-badge um-status-success">' . esc_html__('New User Created', 'user-manager') . '</span>';
													break;
												case 'user_updated':
													echo '<span class="um-status-badge um-status-warning">' . esc_html__('Updated', 'user-manager') . '</span>';
													break;
												case 'password_reset':
													echo '<span class="um-status-badge um-status-info">' . esc_html__('Password Reset', 'user-manager') . '</span>';
													break;
												case 'user_create_failed':
													echo '<span class="um-status-badge um-status-error">' . esc_html__('Failed', 'user-manager') . '</span>';
													break;
												case 'password_reset_failed':
													echo '<span class="um-status-badge um-status-error">' . esc_html__('Reset Failed', 'user-manager') . '</span>';
													break;
												case 'user_removed':
													echo '<span class="um-status-badge um-status-warning">' . esc_html__('User Removed', 'user-manager') . '</span>';
													break;
												case 'user_deleted':
													echo '<span class="um-status-badge um-status-error">' . esc_html__('User Deleted', 'user-manager') . '</span>';
													break;
												case 'user_remove_failed':
													echo '<span class="um-status-badge um-status-error">' . esc_html__('Remove Failed', 'user-manager') . '</span>';
													break;
												case 'user_deactivated':
													echo '<span class="um-status-badge um-status-warning">' . esc_html__('User Deactivated', 'user-manager') . '</span>';
													break;
												case 'user_deactivate_failed':
													echo '<span class="um-status-badge um-status-error">' . esc_html__('Deactivate Failed', 'user-manager') . '</span>';
													break;
												case 'user_deactivated_already':
													echo '<span class="um-status-badge um-status-secondary">' . esc_html__('Already Deactivated', 'user-manager') . '</span>';
													break;
												case 'user_skipped':
													echo '<span class="um-status-badge um-status-info">' . esc_html__('Skipped', 'user-manager') . '</span>';
													break;
												case 'email_sent':
													echo '<span class="um-status-badge um-status-success">' . esc_html__('Email Sent', 'user-manager') . '</span>';
													break;
												case 'email_failed':
													echo '<span class="um-status-badge um-status-error">' . esc_html__('Email Failed', 'user-manager') . '</span>';
													break;
												case 'bulk_import_summary':
													echo '<span class="um-status-badge um-status-secondary">' . esc_html__('Bulk Summary', 'user-manager') . '</span>';
													break;
												case 'coupon_created':
												case 'nuc_coupon_created':
													echo '<span class="um-status-badge um-status-success">' . esc_html__('Coupon Created', 'user-manager') . '</span>';
													break;
												case 'admin_login':
													echo '<span class="um-status-badge um-status-info">' . esc_html__('WP Admin Login', 'user-manager') . '</span>';
													break;
												case 'post_created':
													echo '<span class="um-status-badge um-status-success">' . esc_html__('Post Created', 'user-manager') . '</span>';
													break;
												case 'post_updated':
													echo '<span class="um-status-badge um-status-warning">' . esc_html__('Post Updated', 'user-manager') . '</span>';
													break;
												case 'post_status_changed':
													echo '<span class="um-status-badge um-status-info">' . esc_html__('Status Changed', 'user-manager') . '</span>';
													break;
												case 'plugin_activated':
													echo '<span class="um-status-badge um-status-success">' . esc_html__('Plugin Activated', 'user-manager') . '</span>';
													break;
												case 'plugin_deactivated':
													echo '<span class="um-status-badge um-status-warning">' . esc_html__('Plugin Deactivated', 'user-manager') . '</span>';
													break;
												default:
													$label = ucwords(str_replace('_', ' ', $entry['action']));
													echo '<span class="um-status-badge um-status-secondary">' . esc_html($label) . '</span>';
											}
											?>
										</td>
										<td>
											<?php 
											$extra = isset($entry['extra']) ? $entry['extra'] : [];
											$attempted_email = isset($extra['attempted_email']) ? $extra['attempted_email'] : '';
											$user_email = isset($extra['user_email']) ? $extra['user_email'] : '';
											$error_msg = isset($extra['error']) ? $extra['error'] : '';
											
											// Handle post type entries
											if (in_array($entry['action'], ['post_created', 'post_updated', 'post_status_changed'], true)) {
												$post_title = isset($extra['post_title']) ? $extra['post_title'] : '';
												$post_type_label = isset($extra['post_type_label']) ? $extra['post_type_label'] : (isset($extra['post_type']) ? $extra['post_type'] : '');
												$edit_link = isset($extra['edit_link']) ? $extra['edit_link'] : '';
												if ($post_title && $edit_link) {
													echo '<a href="' . esc_url($edit_link) . '">' . esc_html($post_title) . '</a>';
													if ($post_type_label) {
														echo '<br><small style="color: #646970;">' . esc_html($post_type_label) . '</small>';
													}
												} elseif ($post_title) {
													echo esc_html($post_title);
												} else {
													echo '<em style="color: #646970;">' . esc_html__('N/A', 'user-manager') . '</em>';
												}
											} elseif (in_array($entry['action'], ['plugin_activated', 'plugin_deactivated'], true)) {
												$plugin_name = isset($extra['plugin_name']) ? $extra['plugin_name'] : (isset($extra['plugin_file']) ? $extra['plugin_file'] : '');
												$plugin_url = isset($extra['plugin_url']) ? $extra['plugin_url'] : '';
												if ($plugin_name && $plugin_url) {
													echo '<a href="' . esc_url($plugin_url) . '">' . esc_html($plugin_name) . '</a>';
													if (isset($extra['plugin_version'])) {
														echo '<br><small style="color: #646970;">' . esc_html__('Version', 'user-manager') . ' ' . esc_html($extra['plugin_version']) . '</small>';
													}
												} elseif ($plugin_name) {
													echo esc_html($plugin_name);
												} else {
													echo '<em style="color: #646970;">' . esc_html__('N/A', 'user-manager') . '</em>';
												}
											}
											?>
											<?php if (!in_array($entry['action'], ['post_created', 'post_updated', 'post_status_changed', 'plugin_activated', 'plugin_deactivated'], true)) : ?>
												<?php if ($user) : ?>
												<a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>"><?php echo esc_html($user->user_email); ?></a>
											<?php elseif (!empty($user_email)) : ?>
												<span><?php echo esc_html($user_email); ?></span>
												<?php if (in_array($entry['action'], ['user_removed', 'user_deleted', 'user_deactivated'], true)) : ?>
													<br><small style="color: #646970;"><?php esc_html_e('User removed/deactivated', 'user-manager'); ?></small>
												<?php endif; ?>
											<?php elseif (!empty($attempted_email)) : ?>
												<span style="color: #d63638;"><?php echo esc_html($attempted_email); ?></span>
												<?php if (!empty($error_msg)) : ?>
													<br><small style="color: #646970;"><?php echo esc_html($error_msg); ?></small>
												<?php endif; ?>
											<?php elseif ($entry['user_id'] === 0 || empty($entry['user_id'])) : ?>
												<em style="color: #646970;"><?php esc_html_e('N/A', 'user-manager'); ?></em>
											<?php else : ?>
												<em><?php esc_html_e('User deleted', 'user-manager'); ?></em>
											<?php endif; ?>
											<?php endif; ?>
										</td>
										<td>
											<?php echo esc_html($entry['tool']); ?>
											<?php if (!empty($entry['extra']['source_file'])) : ?>
												<br><small style="color: #646970;"><?php echo esc_html($entry['extra']['source_file']); ?></small>
											<?php elseif ($entry['action'] === 'bulk_import_summary') : ?>
												<?php
												$created_count = isset($entry['extra']['created_count']) ? (int) $entry['extra']['created_count'] : 0;
												$method = isset($entry['extra']['method']) ? ucfirst(str_replace('_', ' ', $entry['extra']['method'])) : '';
												$emails = !empty($entry['extra']['sent_emails']) ? __('Emails sent', 'user-manager') : __('No emails sent', 'user-manager');
												?>
												<br><small style="color: #646970;">
													<?php
													printf(
														esc_html__('%1$d created via %2$s — %3$s', 'user-manager'),
														$created_count,
														esc_html($method ?: __('Bulk Import', 'user-manager')),
														esc_html($emails)
													);
													?>
												</small>
											<?php endif; ?>
										</td>
										<td>
											<?php if ($created_by) : ?>
												<?php echo esc_html($created_by->display_name); ?>
											<?php else : ?>
												<em><?php esc_html_e('Unknown', 'user-manager'); ?></em>
											<?php endif; ?>
										</td>
										<?php $created_ts = !empty($entry['created_at']) ? strtotime($entry['created_at']) : 0; ?>
										<?php if (!$created_ts) { $created_ts = current_time('timestamp'); } ?>
										<td>
											<span class="um-nicetime" title="<?php echo esc_attr(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $created_ts)); ?>">
												<?php echo esc_html(User_Manager_Core::nice_time($created_ts)); ?>
											</span>
										</td>
										<td>
											<button type="button" class="button button-small um-view-activity-details" 
												data-entry-id="<?php echo esc_attr($entry['id']); ?>">
												<?php esc_html_e('View', 'user-manager'); ?>
											</button>
											<?php if ($import_id) : ?>
												<button type="button" class="button button-small um-view-import-details" data-import-id="<?php echo esc_attr($import_id); ?>">
													<?php esc_html_e('Import Log', 'user-manager'); ?>
												</button>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						
						<?php if ($total_pages > 1) : ?>
							<?php self::render_pagination($paged, $total_pages, $total, $current_url, $action_filter, $tool_filter); ?>
						<?php endif; ?>
					<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		
		<!-- Activity Details Modal -->
		<div id="um-activity-details-modal" class="um-modal" style="display:none;">
			<div class="um-modal-content" style="max-width: 600px;">
				<div class="um-modal-header">
					<h3><span class="dashicons dashicons-info-outline"></span> <?php esc_html_e('Activity Details', 'user-manager'); ?></h3>
					<button type="button" class="um-modal-close">&times;</button>
				</div>
				<div class="um-modal-body" id="um-activity-details-content">
				</div>
			</div>
		</div>
		
		<!-- Import Details Modal -->
		<div id="um-import-details-modal" class="um-modal" style="display:none;">
			<div class="um-modal-content" style="max-width: 800px; max-height: 90vh;">
				<div class="um-modal-header">
					<h3><span class="dashicons dashicons-list-view"></span> <?php esc_html_e('Import Details', 'user-manager'); ?></h3>
					<button type="button" class="um-modal-close">&times;</button>
				</div>
				<div class="um-modal-body" id="um-import-details-content">
					<p><?php esc_html_e('Loading...', 'user-manager'); ?></p>
				</div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			// Activity details modal
			$('.um-view-activity-details').on('click', function() {
				var entryId = $(this).data('entry-id');
				$('#um-activity-details-content').html('<p><?php echo esc_js(__('Loading...', 'user-manager')); ?></p>');
				$('#um-activity-details-modal').show();
				
				$.ajax({
					url: ajaxurl,
					type: 'GET',
					data: {
						action: 'user_manager_get_activity_details',
						entry_id: entryId,
						_wpnonce: '<?php echo wp_create_nonce('user_manager_get_activity_details'); ?>'
					},
					success: function(response) {
						if (response.success) {
							$('#um-activity-details-content').html(response.data.html);
						} else {
							$('#um-activity-details-content').html('<p class="um-error">' + (response.data.message || '<?php echo esc_js(__('Failed to load activity details.', 'user-manager')); ?>') + '</p>');
						}
					},
					error: function() {
						$('#um-activity-details-content').html('<p class="um-error"><?php echo esc_js(__('Failed to load activity details.', 'user-manager')); ?></p>');
					}
				});
			});
			
			// Import details modal
			$('.um-view-import-details').on('click', function() {
				var importId = $(this).data('import-id');
				$('#um-import-details-content').html('<p><?php echo esc_js(__('Loading...', 'user-manager')); ?></p>');
				$('#um-import-details-modal').show();
				
				$.ajax({
					url: ajaxurl,
					type: 'GET',
					data: {
						action: 'user_manager_get_import_log',
						import_id: importId,
						_wpnonce: '<?php echo wp_create_nonce('user_manager_get_import_log'); ?>'
					},
					success: function(response) {
						if (response.success) {
							$('#um-import-details-content').html(response.data.html);
						} else {
							$('#um-import-details-content').html('<p class="um-error">' + (response.data.message || '<?php echo esc_js(__('Failed to load import details.', 'user-manager')); ?>') + '</p>');
						}
					},
					error: function() {
						$('#um-import-details-content').html('<p class="um-error"><?php echo esc_js(__('Failed to load import details.', 'user-manager')); ?></p>');
					}
				});
			});
			
			$('.um-modal-close, .um-modal').on('click', function(e) {
				if (e.target === this) {
					$('.um-modal').hide();
				}
			});
			
			$('.um-modal-content').on('click', function(e) {
				e.stopPropagation();
			});
		});
		</script>
		<?php
	}
	
	/**
	 * Get human-readable label for action type.
	 */
	private static function get_action_label(string $action): string {
		$labels = [
			'user_created' => __('New User Created', 'user-manager'),
			'user_updated' => __('User Updated', 'user-manager'),
			'password_reset' => __('Password Reset', 'user-manager'),
			'user_create_failed' => __('User Creation Failed', 'user-manager'),
			'password_reset_failed' => __('Password Reset Failed', 'user-manager'),
			'user_removed' => __('User Removed', 'user-manager'),
			'user_deleted' => __('User Deleted', 'user-manager'),
			'user_remove_failed' => __('User Remove Failed', 'user-manager'),
			'user_deactivated' => __('User Deactivated', 'user-manager'),
			'user_deactivate_failed' => __('User Deactivate Failed', 'user-manager'),
			'user_deactivated_already' => __('User Already Deactivated', 'user-manager'),
			'user_skipped' => __('User Skipped', 'user-manager'),
			'email_sent' => __('Email Sent', 'user-manager'),
			'email_failed' => __('Email Failed', 'user-manager'),
			'bulk_import_summary' => __('Bulk Import Summary', 'user-manager'),
			'coupon_created' => __('Coupon Created', 'user-manager'),
			'nuc_coupon_created' => __('New User Coupon Created', 'user-manager'),
			'coupon_remainder_created' => __('Coupon Remainder Created', 'user-manager'),
			'admin_login' => __('WP Admin Login', 'user-manager'),
			'post_created' => __('Post Created', 'user-manager'),
			'post_updated' => __('Post Updated', 'user-manager'),
			'post_status_changed' => __('Post Status Changed', 'user-manager'),
			'plugin_activated' => __('Plugin Activated', 'user-manager'),
			'plugin_deactivated' => __('Plugin Deactivated', 'user-manager'),
		];
		
		return $labels[$action] ?? ucwords(str_replace('_', ' ', $action));
	}
	
	/**
	 * Render pagination controls.
	 */
	private static function render_pagination(int $paged, int $total_pages, int $total, string $current_url, string $action_filter = '', string $tool_filter = ''): void {
		// Preserve action filter in pagination URLs
		$base_url = $current_url;
		if ($action_filter) {
			$base_url = add_query_arg('action_filter', $action_filter, $base_url);
		}
		if ($tool_filter) {
			$base_url = add_query_arg('tool_filter', $tool_filter, $base_url);
		}
		?>
		<div class="tablenav" style="margin-top:12px;">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo esc_html(number_format_i18n($total)); ?> <?php esc_html_e('items', 'user-manager'); ?></span>
				<span class="pagination-links">
					<?php
					$prev_url = add_query_arg('paged', max(1, $paged - 1), $base_url);
					$next_url = add_query_arg('paged', min($total_pages, $paged + 1), $base_url);
					?>
					<a class="first-page <?php echo $paged <= 1 ? 'disabled' : ''; ?>" href="<?php echo esc_url(add_query_arg('paged', 1, $base_url)); ?>">&laquo;</a>
					<a class="prev-page <?php echo $paged <= 1 ? 'disabled' : ''; ?>" href="<?php echo esc_url($prev_url); ?>">&lsaquo;</a>
					<span class="paging-input">
						<?php echo esc_html($paged); ?> <?php esc_html_e('of', 'user-manager'); ?> <span class="total-pages"><?php echo esc_html($total_pages); ?></span>
					</span>
					<a class="next-page <?php echo $paged >= $total_pages ? 'disabled' : ''; ?>" href="<?php echo esc_url($next_url); ?>">&rsaquo;</a>
					<a class="last-page <?php echo $paged >= $total_pages ? 'disabled' : ''; ?>" href="<?php echo esc_url(add_query_arg('paged', $total_pages, $base_url)); ?>">&raquo;</a>
				</span>
			</div>
		</div>
		<?php
	}
}

