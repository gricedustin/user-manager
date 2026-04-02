<?php
/**
 * Add-on card: SEO Basics.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_SEO_Basics {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['seo_basics_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-seo-basics" data-um-active-selectors="#um-seo-basics-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-chart-area"></span>
				<h2><?php esc_html_e('SEO Basics', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-seo-basics-enabled" name="seo_basics_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
				</div>
				<div id="um-seo-basics-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description"><?php esc_html_e('Adds an "SEO Basics" meta box on pages/posts for Page Title Override and Page Description Override.', 'user-manager'); ?></p>
					<p class="description"><?php esc_html_e('Meta image defaults to the Featured Image. If no featured image exists, it falls back to the first image displayed in page/post content.', 'user-manager'); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}

