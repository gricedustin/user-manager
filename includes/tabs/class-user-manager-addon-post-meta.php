<?php
/**
 * Add-on card: Post Meta Field Viewer & Editor.
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
		$available_roles = User_Manager_Core::get_user_roles();
		$selected_roles = [];
		if (!empty($settings['display_post_meta_allowed_roles']) && is_array($settings['display_post_meta_allowed_roles'])) {
			$selected_roles = array_values(array_map('sanitize_key', $settings['display_post_meta_allowed_roles']));
		}
		$selected_user_identifiers = [];
		if (!empty($settings['display_post_meta_allowed_users'])) {
			if (is_array($settings['display_post_meta_allowed_users'])) {
				$selected_user_identifiers = array_values(array_filter(array_map('trim', $settings['display_post_meta_allowed_users'])));
			} else {
				$selected_user_identifiers = array_values(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', (string) $settings['display_post_meta_allowed_users']))));
			}
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-post-meta" data-um-active-selectors="#um-post-meta-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-editor-code"></span>
				<h2><?php esc_html_e('Post Meta Field Viewer & Editor', 'user-manager'); ?></h2>
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
					<div class="um-form-field">
						<label><strong><?php esc_html_e('Allowed Roles (who can see Post Meta Field Viewer & Editor)', 'user-manager'); ?></strong></label>
						<?php if (empty($available_roles)) : ?>
							<p class="description"><?php esc_html_e('No roles found.', 'user-manager'); ?></p>
						<?php else : ?>
							<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:8px; margin-top:8px;">
								<?php foreach ($available_roles as $role_key => $role_name) : ?>
									<?php $role_key = sanitize_key((string) $role_key); ?>
									<label>
										<input
											type="checkbox"
											name="display_post_meta_allowed_roles[]"
											value="<?php echo esc_attr($role_key); ?>"
											<?php checked(in_array($role_key, $selected_roles, true)); ?>
											<?php echo $form_attr; ?>
										/>
										<?php echo esc_html((string) $role_name); ?> <code><?php echo esc_html($role_key); ?></code>
									</label>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="um-form-field">
						<label for="um-post-meta-allowed-users"><strong><?php esc_html_e('Allowed Usernames/Emails', 'user-manager'); ?></strong></label>
						<textarea id="um-post-meta-allowed-users" name="display_post_meta_allowed_users" rows="5" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea(implode("\n", $selected_user_identifiers)); ?></textarea>
						<p class="description"><?php esc_html_e('Enter one username or email per line. This list works in addition to, or instead of, role selection.', 'user-manager'); ?></p>
						<p class="description"><?php esc_html_e('Access logic: if both role and user lists are empty, all users with post-edit capability can access. If either list has values, matching by role OR username/email grants access.', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

