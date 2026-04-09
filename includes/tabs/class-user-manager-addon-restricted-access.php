<?php
/**
 * Add-on card: Restricted Access.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Restricted_Access {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['restricted_access_enabled']);
		$behavior = isset($settings['restricted_access_logged_out_behavior']) ? (string) $settings['restricted_access_logged_out_behavior'] : 'overlay';
		$password = isset($settings['restricted_access_shared_password']) ? (string) $settings['restricted_access_shared_password'] : '';
		$appended_url_key = isset($settings['restricted_access_url_string']) ? (string) $settings['restricted_access_url_string'] : '';
		$session_minutes = isset($settings['restricted_access_time_limit_minutes']) ? absint($settings['restricted_access_time_limit_minutes']) : 30;
		if ($session_minutes < 1) {
			$session_minutes = 30;
		}
		$excluded_roles = isset($settings['restricted_access_excluded_roles']) && is_array($settings['restricted_access_excluded_roles'])
			? array_map('sanitize_key', $settings['restricted_access_excluded_roles'])
			: [];
		$no_access_message = isset($settings['restricted_access_no_access_message']) ? (string) $settings['restricted_access_no_access_message'] : 'This is a private page';
		$password_submit_button_text = isset($settings['restricted_access_password_submit_button_text']) ? (string) $settings['restricted_access_password_submit_button_text'] : 'Access Website';
		$overlay_bg = isset($settings['restricted_access_overlay_background_color']) ? (string) $settings['restricted_access_overlay_background_color'] : '#ffffff';
		$overlay_text_color = isset($settings['restricted_access_overlay_text_color']) ? (string) $settings['restricted_access_overlay_text_color'] : '#000000';
		$overlay_image = isset($settings['restricted_access_overlay_image_url']) ? (string) $settings['restricted_access_overlay_image_url'] : '';
		$overlay_image_max_width = isset($settings['restricted_access_overlay_image_max_width']) ? (string) $settings['restricted_access_overlay_image_max_width'] : '';
		$overlay_image_display_above_message = !empty($settings['restricted_access_overlay_image_display_as_normal_above_message']);
		$render_background_html_for_social_meta = !empty($settings['restricted_access_render_background_html_for_social_meta']);
		$access_logs = User_Manager_Core::get_restricted_access_history_entries(300);
		$roles = User_Manager_Core::get_user_roles();
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-restricted-access" data-um-active-selectors="#um-restricted-access-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-lock"></span>
				<h2><?php esc_html_e('Restricted Access', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-restricted-access-enabled" name="restricted_access_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Restrict front-end site access with role-based exclusions, shared-password access, URL-key bypass, and custom blocked-state behavior.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-restricted-access-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-restricted-access-logged-out-behavior"><strong><?php esc_html_e('Logged Out Behavior', 'user-manager'); ?></strong></label>
						<select id="um-restricted-access-logged-out-behavior" name="restricted_access_logged_out_behavior"<?php echo $form_attr; ?>>
							<option value="redirect_my_account" <?php selected($behavior, 'redirect_my_account'); ?>><?php esc_html_e('Redirect to My Account', 'user-manager'); ?></option>
							<option value="redirect_wp_admin" <?php selected($behavior, 'redirect_wp_admin'); ?>><?php esc_html_e('Redirect to WP-Admin', 'user-manager'); ?></option>
							<option value="overlay" <?php selected($behavior, 'overlay'); ?>><?php esc_html_e('Full Screen Overlay', 'user-manager'); ?></option>
						</select>
					</div>

					<div class="um-form-field">
						<label for="um-restricted-access-shared-password"><strong><?php esc_html_e('Add a Shared Password to Access Website', 'user-manager'); ?></strong></label>
						<input type="text" id="um-restricted-access-shared-password" name="restricted_access_shared_password" class="regular-text" value="<?php echo esc_attr($password); ?>"<?php echo $form_attr; ?> />
						<p class="description"><?php esc_html_e('When set, blocked visitors can submit this password to gain temporary access.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label for="um-restricted-access-appended-url-key"><strong><?php esc_html_e('Allow an appended URL string to grant access', 'user-manager'); ?></strong></label>
						<input type="text" id="um-restricted-access-appended-url-key" name="restricted_access_url_string" class="regular-text" value="<?php echo esc_attr($appended_url_key); ?>" placeholder="<?php esc_attr_e('preview-access', 'user-manager'); ?>"<?php echo $form_attr; ?> />
						<p style="margin-top:8px;">
							<button type="button" class="button" id="um-restricted-access-generate-url-param"><?php esc_html_e('Insert Random URL Parameter String', 'user-manager'); ?></button>
						</p>
						<p class="description"><?php esc_html_e('If set to "example-key", visiting any URL with ?example-key grants temporary access.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label for="um-restricted-access-session-minutes"><strong><?php esc_html_e('Shared password/appended URL access time limit (minutes)', 'user-manager'); ?></strong></label>
						<input type="number" min="1" step="1" id="um-restricted-access-session-minutes" name="restricted_access_time_limit_minutes" value="<?php echo esc_attr((string) $session_minutes); ?>"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label><strong><?php esc_html_e('Roles to exclude from viewing any pages', 'user-manager'); ?></strong></label>
						<div class="um-checkbox-grid">
							<?php foreach ($roles as $role_key => $role_label) : ?>
								<label class="um-checkbox-chip">
									<input type="checkbox" name="restricted_access_excluded_roles[]" value="<?php echo esc_attr((string) $role_key); ?>" <?php checked(in_array((string) $role_key, $excluded_roles, true)); ?><?php echo $form_attr; ?> />
									<span><?php echo esc_html((string) $role_label); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<p class="description"><?php esc_html_e('Logged-in users with selected roles are blocked as well.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label for="um-restricted-access-no-access-message"><strong><?php esc_html_e('No access message', 'user-manager'); ?></strong></label>
						<input type="text" id="um-restricted-access-no-access-message" name="restricted_access_no_access_message" class="regular-text" value="<?php echo esc_attr($no_access_message); ?>"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label for="um-restricted-access-password-submit-button-text"><strong><?php esc_html_e('Password Submit Button Text', 'user-manager'); ?></strong></label>
						<input type="text" id="um-restricted-access-password-submit-button-text" name="restricted_access_password_submit_button_text" class="regular-text" value="<?php echo esc_attr($password_submit_button_text); ?>"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label for="um-restricted-access-overlay-bg"><strong><?php esc_html_e('Full Screen Overlay Background', 'user-manager'); ?></strong></label>
						<input type="text" id="um-restricted-access-overlay-bg" name="restricted_access_overlay_background_color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($overlay_bg); ?>" data-default-color="#ffffff" placeholder="#ffffff"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label for="um-restricted-access-overlay-text-color"><strong><?php esc_html_e('Full Screen Overlay Text Color', 'user-manager'); ?></strong></label>
						<input type="text" id="um-restricted-access-overlay-text-color" name="restricted_access_overlay_text_color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($overlay_text_color); ?>" data-default-color="#000000" placeholder="#000000"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label for="um-restricted-access-overlay-image"><strong><?php esc_html_e('Full Screen Overlay Image', 'user-manager'); ?></strong></label>
						<input type="text" id="um-restricted-access-overlay-image" name="restricted_access_overlay_image_url" class="regular-text" value="<?php echo esc_attr($overlay_image); ?>" placeholder="<?php esc_attr_e('https://example.com/image.jpg', 'user-manager'); ?>"<?php echo $form_attr; ?> />
						<p>
							<button type="button" class="button" id="um-restricted-access-overlay-image-upload"><?php esc_html_e('Select Image', 'user-manager'); ?></button>
						</p>
					</div>

					<div class="um-form-field">
						<label for="um-restricted-access-overlay-image-max-width"><strong><?php esc_html_e('Full Screen Overlay Image Max Width', 'user-manager'); ?></strong></label>
						<input type="text" id="um-restricted-access-overlay-image-max-width" name="restricted_access_overlay_image_max_width" class="regular-text" value="<?php echo esc_attr($overlay_image_max_width); ?>" placeholder="<?php esc_attr_e('e.g. 1200px', 'user-manager'); ?>"<?php echo $form_attr; ?> />
						<p class="description"><?php esc_html_e('Optional. Example values: 1200px, 90vw, 60rem, none.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-restricted-access-overlay-image-display-above-message" name="restricted_access_overlay_image_display_as_normal_above_message" value="1" <?php checked($overlay_image_display_above_message); ?><?php echo $form_attr; ?> />
							<strong><?php esc_html_e('Display as normal Image Above No access message instead of Background Image', 'user-manager'); ?></strong>
						</label>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-restricted-access-render-background-html-for-social-meta" name="restricted_access_render_background_html_for_social_meta" value="1" <?php checked($render_background_html_for_social_meta); ?><?php echo $form_attr; ?> />
							<strong><?php esc_html_e('Still Allow Full Page HTML to be Rendered in Background behind Overlay for Social Media Share Link Meta Data', 'user-manager'); ?></strong>
						</label>
						<p class="description"><?php esc_html_e('When enabled, full page HTML (including social meta tags) still renders in the response while the restricted-access overlay remains visible to visitors.', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<div class="um-admin-card" id="um-addon-card-restricted-access-history">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-shield"></span>
				<h2><?php esc_html_e('Access History', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div style="overflow:auto;">
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e('Timestamp', 'user-manager'); ?></th>
								<th><?php esc_html_e('IP', 'user-manager'); ?></th>
								<th><?php esc_html_e('IP Location', 'user-manager'); ?></th>
								<th><?php esc_html_e('Browser', 'user-manager'); ?></th>
								<th><?php esc_html_e('URL Accessed From', 'user-manager'); ?></th>
								<th><?php esc_html_e('Password if Used', 'user-manager'); ?></th>
								<th><?php esc_html_e('Failed Password if Failed', 'user-manager'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($access_logs)) : ?>
								<?php foreach ($access_logs as $log) : ?>
									<tr>
										<td><?php echo esc_html((string) ($log['created_at'] ?? '')); ?></td>
										<td><?php echo esc_html((string) ($log['ip_address'] ?? '')); ?></td>
										<td><?php echo esc_html((string) ($log['ip_location'] ?? '')); ?></td>
										<td><?php echo esc_html((string) ($log['browser'] ?? '')); ?></td>
										<td style="max-width:420px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo esc_attr((string) ($log['url_accessed_from'] ?? '')); ?>">
											<?php echo esc_html((string) ($log['url_accessed_from'] ?? '')); ?>
										</td>
										<td><?php echo esc_html((string) ($log['password_used'] ?? '')); ?></td>
										<td><?php echo esc_html((string) ($log['failed_password'] ?? '')); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="7"><?php esc_html_e('No access logs yet.', 'user-manager'); ?></td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<script>
		jQuery(function($) {
			var $input = $('#um-restricted-access-overlay-image');
			var $button = $('#um-restricted-access-overlay-image-upload');
			var $urlParamInput = $('#um-restricted-access-appended-url-key');
			var $generateUrlParamButton = $('#um-restricted-access-generate-url-param');
			if ($urlParamInput.length && $generateUrlParamButton.length) {
				$generateUrlParamButton.on('click', function(e) {
					e.preventDefault();
					var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
					var token = '';
					if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
						var bytes = new Uint8Array(32);
						window.crypto.getRandomValues(bytes);
						for (var i = 0; i < 32; i++) {
							token += chars.charAt(bytes[i] % chars.length);
						}
					} else {
						for (var j = 0; j < 32; j++) {
							token += chars.charAt(Math.floor(Math.random() * chars.length));
						}
					}
					$urlParamInput.val('?' + token).trigger('change');
				});
			}

			if ($input.length && $button.length && typeof wp !== 'undefined' && wp.media) {
				$button.on('click', function(e) {
					e.preventDefault();
					var frame = wp.media({
						title: <?php echo wp_json_encode(__('Select Overlay Image', 'user-manager')); ?>,
						button: { text: <?php echo wp_json_encode(__('Use Image', 'user-manager')); ?> },
						library: { type: 'image' },
						multiple: false
					});
					frame.on('select', function() {
						var attachment = frame.state().get('selection').first();
						if (!attachment) {
							return;
						}
						var url = attachment.get('url') || '';
						if (url) {
							$input.val(url).trigger('change');
						}
					});
					frame.open();
				});
			}
		});
		</script>
		<?php
	}
}

