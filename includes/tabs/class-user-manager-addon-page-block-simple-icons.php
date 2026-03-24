<?php
/**
 * Add-on card: Page Block: Simple Icons.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Page_Block_Simple_Icons {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['page_block_simple_icons_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-page-block-simple-icons" data-um-active-selectors="#um-page-block-simple-icons-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-star-filled"></span>
				<h2><?php esc_html_e('Page Block: Simple Icons', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-page-block-simple-icons-enabled" name="page_block_simple_icons_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Registers block custom/simple-icon (Quick Icon) with editor controls for icon, size, color, and alignment.', 'user-manager'); ?>
					</p>
				</div>
				<div id="um-page-block-simple-icons-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description">
						<?php esc_html_e('Also enqueues Font Awesome in the block editor and front-end when this add-on is active.', 'user-manager'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}

