<?php
/**
 * Add-on card: Subpages Grid block.
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
				<h2><?php esc_html_e('Subpages Grid', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-page-block-subpages-grid-enabled" name="page_block_subpages_grid_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Adds the Subpages Grid block and [subpages_grid] shortcode so you can display child pages as a clean tile grid.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-page-block-subpages-grid-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description"><?php esc_html_e('Use either block custom/subpages-grid in the editor or shortcode [subpages_grid] where shortcode output is preferred.', 'user-manager'); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}

