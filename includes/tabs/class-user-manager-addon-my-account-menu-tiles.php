<?php
/**
 * Add-on card: My Account Menu Tiles.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_My_Account_Menu_Tiles {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['my_account_menu_tiles_enabled']);
		$tiles_per_row = isset($settings['my_account_menu_tiles_per_row']) ? absint($settings['my_account_menu_tiles_per_row']) : 4;
		if ($tiles_per_row < 1) {
			$tiles_per_row = 4;
		}
		$min_tile_height = isset($settings['my_account_menu_tiles_min_height']) ? absint($settings['my_account_menu_tiles_min_height']) : 80;
		if ($min_tile_height < 1) {
			$min_tile_height = 80;
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-my-account-menu-tiles" data-um-active-selectors="#um-my-account-menu-tiles-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-grid-view"></span>
				<h2><?php esc_html_e('My Account Menu Tiles', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-my-account-menu-tiles-enabled" name="my_account_menu_tiles_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Display WooCommerce My Account menu links as responsive tile buttons below dashboard intro text.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-my-account-menu-tiles-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-my-account-menu-tiles-per-row"><?php esc_html_e('Tiles Per Row', 'user-manager'); ?></label>
						<input type="number" min="1" step="1" class="small-text" id="um-my-account-menu-tiles-per-row" name="my_account_menu_tiles_per_row" value="<?php echo esc_attr((string) $tiles_per_row); ?>"<?php echo $form_attr; ?> />
						<p class="description">
							<?php esc_html_e('Number of tiles per row on desktop. Mobile displays 2 tiles per row.', 'user-manager'); ?>
						</p>
					</div>

					<div class="um-form-field">
						<label for="um-my-account-menu-tiles-min-height"><?php esc_html_e('Minimum Tile Height (px)', 'user-manager'); ?></label>
						<input type="number" min="1" step="1" class="small-text" id="um-my-account-menu-tiles-min-height" name="my_account_menu_tiles_min_height" value="<?php echo esc_attr((string) $min_tile_height); ?>"<?php echo $form_attr; ?> />
						<p class="description">
							<?php esc_html_e('Minimum tile button height in pixels.', 'user-manager'); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

