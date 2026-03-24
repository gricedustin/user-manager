<?php
/**
 * Add-on card: Page Block: Tile Grid for Subpages.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Page_Block_Subpages_Grid {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['page_block_subpages_grid_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-page-block-subpages-grid" data-um-active-selectors="#um-page-block-subpages-grid-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-screenoptions"></span>
				<h2><?php esc_html_e('Page Block: Tile Grid for Subpages', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-page-block-subpages-grid-enabled" name="page_block_subpages_grid_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Registers the Subpages Grid tools: shortcode [subpages_grid] and dynamic block custom/subpages-grid (with block editor controls).', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-page-block-subpages-grid-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description"><?php esc_html_e('Use shortcode [subpages_grid] or block custom/subpages-grid after activation.', 'user-manager'); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}

