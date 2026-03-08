<?php
/**
 * Add-on card: Role Switching.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Role_Switching {

	public static function render(): void {
		$role_switch_settings = get_option('view_website_by_role_settings', []);
		$role_switch_enabled  = !empty($role_switch_settings['enabled']);
		$hidden_roles         = $role_switch_settings['hidden_roles'] ?? [];
		$allow_reset          = !empty($role_switch_settings['allow_reset']);
		$roles                = wp_roles()->get_names();
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-role-switching" data-um-active-selectors="#um-role-switching-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-visibility"></span>
				<h2><?php esc_html_e('User Role Switching', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="um-role-switching-enabled"><?php esc_html_e('Activate User Role Switching', 'user-manager'); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="role_switching_enabled" id="um-role-switching-enabled" value="1" <?php checked($role_switch_enabled); ?> />
								<?php esc_html_e('Enable front-end role switcher and user profile permissions.', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('When enabled, eligible users see a "View as role" bar at the bottom of the site and can temporarily switch roles for preview and QA.', 'user-manager'); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label><?php esc_html_e('Hide Roles from Front-end Switcher', 'user-manager'); ?></label>
						</th>
						<td>
							<?php foreach ($roles as $role_key => $role_name) : ?>
								<label style="display:block;margin-bottom:5px;">
									<input type="checkbox" name="hidden_roles[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $hidden_roles, true)); ?> />
									<?php echo esc_html($role_name); ?>
								</label>
							<?php endforeach; ?>
							<p class="description">
								<?php esc_html_e('Select roles that should be hidden from the front-end role switcher for all users.', 'user-manager'); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="um-role-switching-allow-reset"><?php esc_html_e('Allow Reset to Default Roles', 'user-manager'); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="allow_reset" id="um-role-switching-allow-reset" value="1" <?php checked($allow_reset); ?> />
								<?php esc_html_e('Enable the "Reset to Default Roles" button in the front-end switcher.', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('When enabled, users with default roles configured in their profile can quickly restore their original role set.', 'user-manager'); ?>
							</p>
						</td>
					</tr>
				</table>

				<hr style="margin:20px 0;" />
				<h3 style="margin:0 0 12px;"><?php esc_html_e('Users with Role Switching Access', 'user-manager'); ?></h3>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e('Username', 'user-manager'); ?></th>
							<th><?php esc_html_e('Name', 'user-manager'); ?></th>
							<th><?php esc_html_e('Email', 'user-manager'); ?></th>
							<th><?php esc_html_e('Non-Admin Roles', 'user-manager'); ?></th>
							<th><?php esc_html_e('Admin Role', 'user-manager'); ?></th>
							<th><?php esc_html_e('Actions', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$users     = get_users();
						$has_users = false;
						foreach ($users as $user) {
							$user_meta = get_user_meta($user->ID, 'view_website_by_role_permissions', true);
							if (!is_array($user_meta) || empty($user_meta)) {
								continue;
							}
							$has_users = true;
							?>
							<tr>
								<td><?php echo esc_html($user->user_login); ?></td>
								<td><?php echo esc_html($user->display_name); ?></td>
								<td><?php echo esc_html($user->user_email); ?></td>
								<td>
									<span class="dashicons <?php echo isset($user_meta['active']) ? 'dashicons-yes' : 'dashicons-no'; ?>" style="color: <?php echo isset($user_meta['active']) ? '#46b450' : '#dc3232'; ?>;"></span>
								</td>
								<td>
									<span class="dashicons <?php echo isset($user_meta['admin']) ? 'dashicons-yes' : 'dashicons-no'; ?>" style="color: <?php echo isset($user_meta['admin']) ? '#46b450' : '#dc3232'; ?>;"></span>
								</td>
								<td>
									<a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>" class="button button-small">
										<?php esc_html_e('Edit User', 'user-manager'); ?>
									</a>
								</td>
							</tr>
							<?php
						}
						if (!$has_users) :
							?>
							<tr>
								<td colspan="6"><?php esc_html_e('No users have been granted role switching access yet.', 'user-manager'); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<hr style="margin:20px 0;" />
				<h3 style="margin:0 0 12px;"><?php esc_html_e('Users Who Have Changed Their Role', 'user-manager'); ?></h3>
				<?php
				global $wpdb;
				$table            = $wpdb->prefix . 'um_admin_activity';
				$role_change_rows = $wpdb->get_results(
					"SELECT * FROM {$table} WHERE action = 'role_switch_change' ORDER BY created_at DESC LIMIT 50",
					ARRAY_A
				);
				?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e('Date/Time', 'user-manager'); ?></th>
							<th><?php esc_html_e('User', 'user-manager'); ?></th>
							<th><?php esc_html_e('Action', 'user-manager'); ?></th>
							<th><?php esc_html_e('Existing Role(s)', 'user-manager'); ?></th>
							<th><?php esc_html_e('New Role(s)', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($role_change_rows)) : ?>
							<?php foreach ($role_change_rows as $row) : ?>
								<?php
								$extra     = !empty($row['extra']) ? json_decode($row['extra'], true) : [];
								$user_id   = (int) $row['user_id'];
								$user      = $user_id ? get_userdata($user_id) : null;
								$when      = !empty($row['created_at']) ? gmdate('Y-m-d H:i:s', strtotime($row['created_at'])) : '';
								$action    = isset($extra['change_type']) ? (string) $extra['change_type'] : '';
								$existing  = isset($extra['existing_roles']) ? (string) $extra['existing_roles'] : '';
								$new_roles = isset($extra['new_roles']) ? (string) $extra['new_roles'] : '';
								?>
								<tr>
									<td><?php echo esc_html($when); ?></td>
									<td>
										<?php if ($user) : ?>
											<a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>">
												<?php echo esc_html($user->user_login); ?>
											</a>
										<?php else : ?>
											<em style="color:#646970;"><?php esc_html_e('Unknown', 'user-manager'); ?></em>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html($action !== '' ? $action : $row['action']); ?></td>
									<td><?php echo esc_html($existing); ?></td>
									<td><?php echo esc_html($new_roles); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="5"><?php esc_html_e('No role changes have been recorded yet.', 'user-manager'); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<hr style="margin:20px 0;" />
				<h3 style="margin:0 0 12px;"><?php esc_html_e('Role Switching Settings Change History', 'user-manager'); ?></h3>
				<?php
				$settings_rows = $wpdb->get_results(
					"SELECT * FROM {$table} WHERE action = 'role_switch_settings' ORDER BY created_at DESC LIMIT 50",
					ARRAY_A
				);
				?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e('Date/Time', 'user-manager'); ?></th>
							<th><?php esc_html_e('User', 'user-manager'); ?></th>
							<th><?php esc_html_e('Action', 'user-manager'); ?></th>
							<th><?php esc_html_e('Details', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($settings_rows)) : ?>
							<?php foreach ($settings_rows as $row) : ?>
								<?php
								$extra   = !empty($row['extra']) ? json_decode($row['extra'], true) : [];
								$admin   = !empty($row['created_by']) ? get_userdata((int) $row['created_by']) : null;
								$when    = !empty($row['created_at']) ? gmdate('Y-m-d H:i:s', strtotime($row['created_at'])) : '';
								$action  = isset($extra['history_action']) ? (string) $extra['history_action'] : '';
								$details = isset($extra['details']) ? (string) $extra['details'] : '';
								?>
								<tr>
									<td><?php echo esc_html($when); ?></td>
									<td>
										<?php if ($admin) : ?>
											<a href="<?php echo esc_url(get_edit_user_link($admin->ID)); ?>">
												<?php echo esc_html($admin->user_login); ?>
											</a>
										<?php else : ?>
											<em style="color:#646970;"><?php esc_html_e('Unknown', 'user-manager'); ?></em>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html($action !== '' ? $action : $row['action']); ?></td>
									<td><?php echo wp_kses_post(User_Manager_Core::process_role_switch_history_details($details)); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="4"><?php esc_html_e('No history entries found.', 'user-manager'); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
				<p class="description" style="margin-top:10px;">
					<?php esc_html_e('Tip: Edit a user profile to grant or remove role switching permissions for that user.', 'user-manager'); ?>
				</p>
			</div>
		</div>
		<?php
	}
}

