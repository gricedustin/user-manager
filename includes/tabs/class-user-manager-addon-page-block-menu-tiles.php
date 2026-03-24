<?php
/**
 * Add-on card: Page Block: Tile Grid for Menu.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Page_Block_Menu_Tiles {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['page_block_menu_tiles_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-page-block-menu-tiles" data-um-active-selectors="#um-page-block-menu-tiles-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-screenoptions"></span>
				<h2><?php esc_html_e('Page Block: Tile Grid for Menu', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-page-block-menu-tiles-enabled" name="page_block_menu_tiles_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Registers dynamic Gutenberg block "custom/mybrand-menu-tiles" (Menu Tile Buttons) and its editor UI.', 'user-manager'); ?>
					</p>
				</div>
				<div id="um-page-block-menu-tiles-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description"><?php esc_html_e('When active, add the Menu Tile Buttons block in the editor to render primary-menu items as tile buttons.', 'user-manager'); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}

