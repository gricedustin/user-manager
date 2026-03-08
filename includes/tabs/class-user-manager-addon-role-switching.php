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
		?>
		<div class="um-admin-grid" style="margin-top:18px;">
			<div class="um-admin-card um-admin-card-full um-addon-collapsible" id="um-addon-card-role-switching" data-um-active-selectors="#um-role-switching-enabled">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-visibility"></span>
					<h2><?php esc_html_e('Role Switching', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p class="description" style="margin-top:0;">
						<?php esc_html_e('Role Switching has been moved into Add-ons. Expand this section to configure role switching settings and review role-switch history.', 'user-manager'); ?>
					</p>
					<details id="um-role-switching-embedded" <?php echo $role_switch_enabled ? 'open' : ''; ?>>
						<summary style="cursor:pointer;font-weight:600;">
							<?php echo $role_switch_enabled ? esc_html__('Role Switching is active — click to expand/collapse', 'user-manager') : esc_html__('Role Switching is inactive — click to expand/collapse', 'user-manager'); ?>
						</summary>
						<div style="margin-top:12px;">
							<?php
							if (class_exists('User_Manager_Tab_Role_Switching')) {
								User_Manager_Tab_Role_Switching::render();
							}
							?>
						</div>
					</details>
				</div>
			</div>
		</div>
		<?php
	}
}

