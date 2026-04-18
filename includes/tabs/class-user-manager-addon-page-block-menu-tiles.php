<?php
/**
 * Block card: Menu Tiles.
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
				<h2><?php esc_html_e('Menu Tiles', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-page-block-menu-tiles-enabled" name="page_block_menu_tiles_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Activate the Menu Tiles block to turn a WordPress menu into a visual grid of tile buttons.', 'user-manager'); ?>
					</p>
				</div>
				<div id="um-page-block-menu-tiles-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description"><?php esc_html_e('Best for quick-link sections, portal pages, and dashboard-style navigation layouts.', 'user-manager'); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}

