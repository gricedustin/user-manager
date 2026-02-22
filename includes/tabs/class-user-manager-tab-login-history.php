<?php
/**
 * Login history/User activity tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Login_History {

	public static function render(): void {
		global $wpdb;
		$per_page = 50;
		$paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset = ($paged - 1) * $per_page;
		$action_filter = isset($_GET['ua_action_filter']) ? sanitize_text_field(wp_unslash($_GET['ua_action_filter'])) : '';
		$entries = [];
		$total = 0;
		// Read from user activity table
		$table = $wpdb->prefix . 'um_user_activity';
		$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
		
		if ($table_exists) {
			// Build WHERE clause for optional action filter.
			$where = '1=1';
			$params = [];
			if ($action_filter !== '') {
				$where .= ' AND h.action = %s';
				$params[] = $action_filter;
			}

			// Total count with optional filter.
			$count_sql = "SELECT COUNT(*) FROM {$table} h WHERE {$where}";
			if (!empty($params)) {
				$total = (int) $wpdb->get_var($wpdb->prepare($count_sql, ...$params));
			} else {
				$total = (int) $wpdb->get_var($count_sql);
			}

			// Main query with filter + pagination. Include h.roles (saved at record time; empty for old records).
			$query_sql = "
				SELECT h.id, h.user_id, h.action, h.url, h.ip_address, h.user_agent, h.roles, h.created_at,
				       u.user_login, u.user_email, u.display_name
				  FROM {$table} h
				  LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
				 WHERE {$where}
			  ORDER BY h.created_at DESC
			  LIMIT %d OFFSET %d
			";
			$params[] = $per_page;
			$params[] = $offset;
			$query = $wpdb->prepare($query_sql, ...$params);
			$rows = $wpdb->get_results($query);
			foreach ($rows as $row) {
				$timestamp = $row->created_at ? strtotime($row->created_at) : 0;
				$entries[] = [
					'user_id' => (int) $row->user_id,
					'user_login' => $row->user_login ?: '',
					'user_email' => $row->user_email ?: '',
					'display_name' => $row->display_name ?: ($row->user_email ?: ''),
					'action' => $row->action ?: '',
					'url' => $row->url ?: '',
					'ip_address' => $row->ip_address,
					'user_agent' => $row->user_agent,
					'roles' => isset($row->roles) ? (string) $row->roles : '',
					'timestamp' => $timestamp ?: 0,
					'created_at' => $row->created_at,
				];
			}
		} else {
			// No activity table found
			$total = 0;
		}
		
		$total_pages = max(1, (int) ceil($total / $per_page));
		$current_url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_LOGIN_HISTORY);

		// Get distinct actions for filter dropdown.
		$all_actions = [];
		if ($table_exists) {
			$all_actions = $wpdb->get_col("SELECT DISTINCT action FROM {$table} ORDER BY action ASC");
		}
		
		?>
		<div class="um-admin-grid">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-clock"></span>
					<h2><?php esc_html_e('User Activity', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<?php if (empty($entries) && empty($action_filter)) : ?>
						<p class="um-empty-message"><?php esc_html_e('No user activity found.', 'user-manager'); ?></p>
					<?php else : ?>
						<!-- Action Filter -->
						<div style="margin-bottom: 20px;">
							<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:inline-block;">
								<input type="hidden" name="page" value="<?php echo esc_attr(User_Manager_Core::SETTINGS_PAGE_SLUG); ?>" />
								<input type="hidden" name="tab" value="<?php echo esc_attr(User_Manager_Core::TAB_LOGIN_HISTORY); ?>" />
								<label for="um-ua-action-filter" style="margin-right: 8px;">
									<strong><?php esc_html_e('Filter by Action:', 'user-manager'); ?></strong>
								</label>
								<select name="ua_action_filter" id="um-ua-action-filter" onchange="this.form.submit();" style="min-width: 200px;">
									<option value=""><?php esc_html_e('All Actions', 'user-manager'); ?></option>
									<?php foreach ($all_actions as $action) : ?>
										<option value="<?php echo esc_attr($action); ?>" <?php selected($action_filter, $action); ?>>
											<?php echo esc_html($action); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<?php if ($action_filter) : ?>
									<a href="<?php echo esc_url($current_url); ?>" class="button" style="margin-left: 8px;">
										<?php esc_html_e('Clear Filter', 'user-manager'); ?>
									</a>
								<?php endif; ?>
							</form>
						</div>
						
						<?php if (empty($entries)) : ?>
							<p class="um-empty-message"><?php esc_html_e('No user activity found for the selected filter.', 'user-manager'); ?></p>
						<?php else : ?>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e('Action', 'user-manager'); ?></th>
									<th><?php esc_html_e('User', 'user-manager'); ?></th>
									<th><?php esc_html_e('Email', 'user-manager'); ?></th>
									<th><?php esc_html_e('Username', 'user-manager'); ?></th>
									<th><?php esc_html_e('Roles', 'user-manager'); ?></th>
									<th><?php esc_html_e('Login Time', 'user-manager'); ?></th>
									<th><?php esc_html_e('Time Ago', 'user-manager'); ?></th>
									<th><?php esc_html_e('URL', 'user-manager'); ?></th>
									<th><?php esc_html_e('IP', 'user-manager'); ?></th>
									<th><?php esc_html_e('User Agent', 'user-manager'); ?></th>
									<th style="width:140px;"><?php esc_html_e('Actions', 'user-manager'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($entries as $e) : ?>
									<tr>
										<td><?php echo esc_html($e['action']); ?></td>
										<td>
											<?php if (!empty($e['user_id'])) : ?>
												<a href="<?php echo esc_url(get_edit_user_link($e['user_id'])); ?>">
													<?php echo esc_html($e['display_name'] ?: $e['user_email'] ?: $e['user_login']); ?>
												</a>
											<?php else : ?>
												<?php echo esc_html($e['display_name'] ?: $e['user_email'] ?: ($e['user_login'] ?: '—')); ?>
											<?php endif; ?>
										</td>
										<td><?php echo esc_html($e['user_email'] ?: '—'); ?></td>
										<td><?php echo esc_html($e['user_login'] ?: '—'); ?></td>
										<td><?php echo esc_html(!empty($e['roles']) ? $e['roles'] : '—'); ?></td>
										<td>
											<?php 
											$ts = (int) $e['timestamp'];
											echo $ts 
												? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ts))
												: '—';
											?>
										</td>
										<td>
											<?php echo $ts ? esc_html(User_Manager_Core::nice_time($ts)) : '—'; ?>
										</td>
										<td style="max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
											<?php if (!empty($e['url'])) : ?>
												<a href="<?php echo esc_url($e['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($e['url']); ?></a>
											<?php else : ?>
												—
											<?php endif; ?>
										</td>
										<td><code><?php echo esc_html($e['ip_address'] ?: ''); ?></code></td>
										<td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo esc_attr($e['user_agent'] ?: ''); ?>">
											<?php echo esc_html($e['user_agent'] ?: ''); ?>
										</td>
										<td>
											<?php if (!empty($e['user_id'])) : ?>
												<a class="button button-small" href="<?php echo esc_url(get_edit_user_link($e['user_id'])); ?>">
													<?php esc_html_e('Edit User', 'user-manager'); ?>
												</a>
											<?php else : ?>
												<span style="color:#646970;">—</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						
						<?php if ($total_pages > 1) : ?>
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
						<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
			
		</div>
		<?php
	}
}




