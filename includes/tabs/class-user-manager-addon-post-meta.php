<?php
/**
 * Add-on card: Post Meta.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Post_Meta {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['display_post_meta_meta_box']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-post-meta" data-um-active-selectors="#um-post-meta-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-editor-code"></span>
				<h2><?php esc_html_e('Post Meta', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-post-meta-enabled" name="display_post_meta_meta_box" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Shows a Post Meta box on post edit screens listing post meta fields and values.', 'user-manager'); ?></p>
				</div>
				<div id="um-post-meta-edit-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="allow_edit_post_meta" value="1" <?php checked($settings['allow_edit_post_meta'] ?? false); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Allow editing of post meta values', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('When the post meta meta box is enabled, allow changing meta values directly from the meta box. Save the post to apply changes.', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

