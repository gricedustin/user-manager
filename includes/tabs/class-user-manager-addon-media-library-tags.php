<?php
/**
 * Add-on card: Media Library Tags.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Media_Library_Tags {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['media_library_tags_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-media-library-tags" data-um-active-selectors="#um-media-library-tags-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-tag"></span>
				<h2><?php esc_html_e('Media Library Tags', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-media-library-tags-enabled" name="media_library_tags_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Adds a Library Tags taxonomy for Media, including a "Library Tags" submenu under Media, media list/grid filters, bulk tag assignment, and per-item add/remove controls.', 'user-manager'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}

