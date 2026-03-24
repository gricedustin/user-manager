<?php
/**
 * Add-on card: Page Block - Tabs with Content from Other Pages.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Page_Block_Tabbed_Content_Area {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['page_block_tabbed_content_area_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-page-block-tabbed-content-area" data-um-active-selectors="#um-page-block-tabbed-content-area-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-index-card"></span>
				<h2><?php esc_html_e('Page Block: Tabs with Content from Other Pages', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-page-block-tabbed-content-area-enabled" name="page_block_tabbed_content_area_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Registers dynamic blocks custom/tabbed-content-area (plus legacy alias custom/tabbed-content-area) so tab panels can render content from selected pages/posts.', 'user-manager'); ?>
					</p>
				</div>
				<div id="um-page-block-tabbed-content-area-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description">
						<?php esc_html_e('Use in the block editor: add tabs, set tab labels, and assign Page/Post IDs for each panel.', 'user-manager'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}

