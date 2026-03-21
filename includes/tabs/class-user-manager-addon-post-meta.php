<?php
/**
 * Add-on card: Post Meta Viewer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Post_Meta {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['display_post_meta_meta_box']);
		$post_type_objects = get_post_types(['show_ui' => true], 'objects');
		$post_type_slugs = is_array($post_type_objects) ? array_keys($post_type_objects) : [];
		$selected_post_types = [];
		if (!empty($settings['display_post_meta_post_types']) && is_array($settings['display_post_meta_post_types'])) {
			$selected_post_types = array_values(array_map('sanitize_key', $settings['display_post_meta_post_types']));
		}
		if (empty($selected_post_types)) {
			$selected_post_types = $post_type_slugs; // Default behavior: all post types enabled.
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-post-meta" data-um-active-selectors="#um-post-meta-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-editor-code"></span>
				<h2><?php esc_html_e('Post Meta Viewer', 'user-manager'); ?></h2>
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
						<label><strong><?php esc_html_e('Enabled Post Types', 'user-manager'); ?></strong></label>
						<?php if (empty($post_type_objects)) : ?>
							<p class="description"><?php esc_html_e('No editable post types found.', 'user-manager'); ?></p>
						<?php else : ?>
							<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:8px; margin-top:8px;">
								<?php foreach ($post_type_objects as $post_type_slug => $post_type_obj) : ?>
									<?php
									$post_type_slug = sanitize_key((string) $post_type_slug);
									$post_type_label = isset($post_type_obj->labels->singular_name) && $post_type_obj->labels->singular_name !== ''
										? (string) $post_type_obj->labels->singular_name
										: (isset($post_type_obj->labels->name) ? (string) $post_type_obj->labels->name : $post_type_slug);
									?>
									<label>
										<input
											type="checkbox"
											name="display_post_meta_post_types[]"
											value="<?php echo esc_attr($post_type_slug); ?>"
											<?php checked(in_array($post_type_slug, $selected_post_types, true)); ?>
											<?php echo $form_attr; ?>
										/>
										<?php echo esc_html($post_type_label); ?> <code><?php echo esc_html($post_type_slug); ?></code>
									</label>
								<?php endforeach; ?>
							</div>
							<p class="description"><?php esc_html_e('By default, all post types are enabled. Uncheck post types you do not want to include.', 'user-manager'); ?></p>
						<?php endif; ?>
					</div>
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

