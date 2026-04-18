<?php
/**
 * Add-on card: Administrator Custom Dashboard Tiles.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Administrator_Custom_Dashboard_Tiles {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['admin_custom_dashboard_tiles_enabled']);
		$admin_bar_enabled = !empty($settings['admin_custom_dashboard_tiles_admin_bar_enabled']);
		$page_title = isset($settings['admin_custom_dashboard_tiles_page_title']) ? (string) $settings['admin_custom_dashboard_tiles_page_title'] : '';
		$menu_title = isset($settings['admin_custom_dashboard_tiles_menu_title']) ? (string) $settings['admin_custom_dashboard_tiles_menu_title'] : '';
		$menu_priority = isset($settings['admin_custom_dashboard_tiles_menu_priority']) ? (int) $settings['admin_custom_dashboard_tiles_menu_priority'] : 80;
		$page_url = admin_url('admin.php?page=' . (class_exists('User_Manager_Core') && method_exists('User_Manager_Core', 'admin_custom_dashboard_tiles_page_slug') ? User_Manager_Core::admin_custom_dashboard_tiles_page_slug() : 'user-manager-custom-dashboard-tiles'));
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-administrator-custom-dashboard-tiles" data-um-active-selectors="#um-admin-custom-dashboard-tiles-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-admin-links"></span>
				<h2><?php esc_html_e('Administrator Custom Dashboard Tiles', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-admin-custom-dashboard-tiles-enabled" name="admin_custom_dashboard_tiles_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Add a dedicated WP-Admin dashboard of drag-and-drop tiles for administrators, grouped by custom sections, with click tracking, per-user favorites, search, and JSON import/export.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-admin-custom-dashboard-tiles-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-admin-custom-dashboard-tiles-page-title"><strong><?php esc_html_e('Page Title Override', 'user-manager'); ?></strong></label>
						<input type="text" id="um-admin-custom-dashboard-tiles-page-title" name="admin_custom_dashboard_tiles_page_title" class="regular-text" value="<?php echo esc_attr($page_title); ?>" placeholder="<?php esc_attr_e('Custom Dashboard Tiles', 'user-manager'); ?>"<?php echo $form_attr; ?> />
						<p class="description"><?php esc_html_e('Shown as the H1 heading and browser tab title on the dashboard. Leave blank for the default.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field">
						<label for="um-admin-custom-dashboard-tiles-menu-title"><strong><?php esc_html_e('Menu Title Override', 'user-manager'); ?></strong></label>
						<input type="text" id="um-admin-custom-dashboard-tiles-menu-title" name="admin_custom_dashboard_tiles_menu_title" class="regular-text" value="<?php echo esc_attr($menu_title); ?>" placeholder="<?php esc_attr_e('Dashboard Tiles', 'user-manager'); ?>"<?php echo $form_attr; ?> />
						<p class="description"><?php esc_html_e('Shown in the WP-Admin sidebar menu and in the admin bar dropdown label. Leave blank for the default.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field">
						<label for="um-admin-custom-dashboard-tiles-menu-priority"><strong><?php esc_html_e('Menu Location Priority', 'user-manager'); ?></strong></label>
						<input type="number" min="1" max="200" step="1" id="um-admin-custom-dashboard-tiles-menu-priority" name="admin_custom_dashboard_tiles_menu_priority" value="<?php echo esc_attr((string) $menu_priority); ?>"<?php echo $form_attr; ?> />
						<p class="description"><?php esc_html_e('Position in the WP-Admin menu (1 = top, ~100 = Comments, 200 = bottom). Default is 80, which places the menu near Settings.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-admin-custom-dashboard-tiles-admin-bar-enabled" name="admin_custom_dashboard_tiles_admin_bar_enabled" value="1" <?php checked($admin_bar_enabled); ?><?php echo $form_attr; ?> />
							<strong><?php esc_html_e('WP-Admin Bar Dropdown', 'user-manager'); ?></strong>
						</label>
						<p class="description"><?php esc_html_e('Adds a dropdown shortcut to the WP-Admin bar (top-right) that links to the dashboard and lists the current user\'s favorite tiles. Renders as an icon on mobile viewports so the shortcut stays reachable.', 'user-manager'); ?></p>
					</div>
					<?php if ($enabled) : ?>
						<div class="um-form-field">
							<a class="button button-secondary" href="<?php echo esc_url($page_url); ?>">
								<?php esc_html_e('Open Dashboard Tiles', 'user-manager'); ?>
							</a>
							<p class="description"><?php esc_html_e('Jump straight to the dashboard page to add or edit tiles.', 'user-manager'); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
