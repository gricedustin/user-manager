<?php
/**
 * Add-on card: WP-Admin CSS.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_WP_Admin_CSS {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$is_enabled = array_key_exists('wp_admin_css_enabled', $settings)
			? !empty($settings['wp_admin_css_enabled'])
			: false;
		if (!array_key_exists('wp_admin_css_enabled', $settings)) {
			$roles_css = isset($settings['wp_admin_css_roles']) && is_array($settings['wp_admin_css_roles']) ? $settings['wp_admin_css_roles'] : [];
			$has_role_css = false;
			foreach ($roles_css as $css_value) {
				if (trim((string) $css_value) !== '') {
					$has_role_css = true;
					break;
				}
			}
			$is_enabled = trim((string) ($settings['wp_admin_css_all'] ?? '')) !== ''
				|| trim((string) ($settings['wp_admin_css_users_css'] ?? '')) !== ''
				|| $has_role_css;
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-admin-css" data-um-active-selectors="#um-wp-admin-css-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-art"></span>
				<h2><?php esc_html_e('WP-Admin CSS', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="wp_admin_css_enabled" id="um-wp-admin-css-enabled" value="1" <?php checked($is_enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Inject custom CSS into wp-admin globally, by role, or for specific users.', 'user-manager'); ?></p>
				</div>
				<div id="um-wp-admin-css-fields" style="<?php echo $is_enabled ? '' : 'display:none;'; ?>">
				<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Apply custom CSS only in the WordPress admin (wp-admin). You can target all roles, exclude specific roles from the global CSS, apply CSS to specific users (by login, email, or ID), and/or add per-role CSS.', 'user-manager'); ?></p>

				<div id="um-wp-admin-css-list">
					<div class="um-wp-admin-css-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
						<h3 class="um-settings-subsection" style="margin-top: 0;"><?php esc_html_e('All Roles CSS', 'user-manager'); ?></h3>
						<div class="um-form-field">
							<label for="um-wp-admin-css-all"><?php esc_html_e('CSS applied to all roles in wp-admin', 'user-manager'); ?></label>
							<textarea name="wp_admin_css_all" id="um-wp-admin-css-all" class="large-text code" rows="8" style="width:100%; font-family: Consolas, Monaco, monospace;" placeholder="body { padding: 0; ?>"<?php echo $form_attr; ?>><?php echo esc_textarea($settings['wp_admin_css_all'] ?? ''); ?></textarea>
							<p class="description"><?php esc_html_e('Example: body { padding: 0; }', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-wp-admin-css-exclude-roles"><?php esc_html_e('Roles to exclude from All Roles CSS above', 'user-manager'); ?></label>
							<input type="text" name="wp_admin_css_exclude_roles" id="um-wp-admin-css-exclude-roles" class="large-text" value="<?php echo esc_attr(is_array($settings['wp_admin_css_exclude_roles'] ?? null) ? implode(', ', $settings['wp_admin_css_exclude_roles']) : ($settings['wp_admin_css_exclude_roles'] ?? '')); ?>" placeholder="administrator, editor"<?php echo $form_attr; ?> />
							<p class="description"><?php esc_html_e('Comma-separated role slugs. Users with any of these roles will not receive the All Roles CSS.', 'user-manager'); ?></p>
						</div>
					</div>

					<div class="um-wp-admin-css-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
						<h3 class="um-settings-subsection" style="margin-top: 0;"><?php esc_html_e('User-based CSS', 'user-manager'); ?></h3>
						<div class="um-form-field">
							<label for="um-wp-admin-css-users-css"><?php esc_html_e('CSS applied only to specific users', 'user-manager'); ?></label>
							<textarea name="wp_admin_css_users_css" id="um-wp-admin-css-users-css" class="large-text code" rows="8" style="width:100%; font-family: Consolas, Monaco, monospace;" placeholder="#wpadminbar { display: none; }"<?php echo $form_attr; ?>><?php echo esc_textarea($settings['wp_admin_css_users_css'] ?? ''); ?></textarea>
							<p class="description"><?php esc_html_e('Example: #wpadminbar { display: none; }', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-wp-admin-css-users-include"><?php esc_html_e('Users who receive the CSS above', 'user-manager'); ?></label>
							<input type="text" name="wp_admin_css_users_include" id="um-wp-admin-css-users-include" class="large-text" value="<?php echo esc_attr(is_array($settings['wp_admin_css_users_include'] ?? null) ? implode(', ', $settings['wp_admin_css_users_include']) : ($settings['wp_admin_css_users_include'] ?? '')); ?>" placeholder="admin@example.com, jane, 123"<?php echo $form_attr; ?> />
							<p class="description"><?php esc_html_e('Comma-separated: usernames (login), email addresses, or user IDs.', 'user-manager'); ?></p>
						</div>
					</div>

					<?php
					$wp_admin_css_roles = isset($settings['wp_admin_css_roles']) && is_array($settings['wp_admin_css_roles']) ? $settings['wp_admin_css_roles'] : [];
					foreach (User_Manager_Core::get_user_roles() as $role_key => $role_name) :
						$role_css = $wp_admin_css_roles[$role_key] ?? '';
					?>
					<div class="um-wp-admin-css-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
						<h3 class="um-settings-subsection" style="margin-top: 0;"><?php echo esc_html(sprintf(__('Role: %s', 'user-manager'), $role_name)); ?></h3>
						<div class="um-form-field">
							<label for="um-wp-admin-css-role-<?php echo esc_attr($role_key); ?>"><?php echo esc_html(sprintf(__('WP-Admin CSS for role: %s', 'user-manager'), $role_name)); ?></label>
							<textarea name="wp_admin_css_role[<?php echo esc_attr($role_key); ?>]" id="um-wp-admin-css-role-<?php echo esc_attr($role_key); ?>" class="large-text code" rows="6" style="width:100%; font-family: Consolas, Monaco, monospace;"<?php echo $form_attr; ?>><?php echo esc_textarea($role_css); ?></textarea>
							<p class="description"><?php esc_html_e('Leave blank to skip this role.', 'user-manager'); ?></p>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				</div>
			</div>
		</div>
		<?php
	}
}

