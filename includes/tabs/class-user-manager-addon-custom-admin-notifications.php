<?php
/**
 * Add-on card: Custom WP-Admin Notifications.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Custom_Admin_Notifications {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$admin_notifications = isset($settings['custom_admin_notifications']) && is_array($settings['custom_admin_notifications']) ? $settings['custom_admin_notifications'] : [];
		$is_enabled = array_key_exists('custom_admin_notifications_enabled', $settings)
			? !empty($settings['custom_admin_notifications_enabled'])
			: false;
		if (!array_key_exists('custom_admin_notifications_enabled', $settings)) {
			foreach ($admin_notifications as $row) {
				if (!is_array($row)) {
					continue;
				}
				$title = trim((string) ($row['title'] ?? ''));
				$body  = trim((string) ($row['body'] ?? ''));
				if ($title !== '' || $body !== '') {
					$is_enabled = true;
					break;
				}
			}
		}
		if (empty($admin_notifications)) {
			$admin_notifications = [['title' => '', 'body' => '', 'background_color' => '', 'url_string_match' => '']];
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-custom-notifications" data-um-active-selectors="#um-custom-admin-notifications-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-megaphone"></span>
				<h2><?php esc_html_e('WP-Admin Notifications', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="custom_admin_notifications_enabled" id="um-custom-admin-notifications-enabled" value="1" <?php checked($is_enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate WP-Admin Notifications', 'user-manager'); ?>
					</label>
				</div>
				<div id="um-custom-admin-notifications-fields" style="<?php echo $is_enabled ? '' : 'display:none;'; ?>">
				<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Add custom admin notices at the top of WP-Admin screens. Each notification can be limited to URLs that contain a specific string (e.g. shop_coupon for coupon edit screens), or shown on all admin screens if URL match is blank.', 'user-manager'); ?></p>
				<div id="um-custom-admin-notifications-list">
					<?php foreach ($admin_notifications as $idx => $n) : ?>
						<div class="um-admin-notification-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
							<h3 class="um-settings-subsection um-admin-notification-number" style="margin-top: 0;"><?php echo esc_html(sprintf(__('Notification %d', 'user-manager'), $idx + 1)); ?></h3>
							<div class="um-form-field">
								<label><?php esc_html_e('Notification Headline', 'user-manager'); ?></label>
								<input type="text" name="custom_admin_notification[<?php echo (int) $idx; ?>][title]" class="large-text" value="<?php echo esc_attr($n['title'] ?? ''); ?>"<?php echo $form_attr; ?> />
							</div>
							<div class="um-form-field">
								<label><?php esc_html_e('Notification Body', 'user-manager'); ?></label>
								<textarea name="custom_admin_notification[<?php echo (int) $idx; ?>][body]" rows="5" class="large-text" style="width:100%;"<?php echo $form_attr; ?>><?php echo esc_textarea($n['body'] ?? ''); ?></textarea>
							</div>
							<div class="um-form-field">
								<label><?php esc_html_e('Background Color', 'user-manager'); ?></label>
								<input type="text" name="custom_admin_notification[<?php echo (int) $idx; ?>][background_color]" class="regular-text" value="<?php echo esc_attr($n['background_color'] ?? ''); ?>" placeholder="red or #202020"<?php echo $form_attr; ?> />
								<p class="description"><?php esc_html_e('CSS values only (e.g. #202020 or red).', 'user-manager'); ?></p>
							</div>
							<div class="um-form-field">
								<label><?php esc_html_e('URL String Match', 'user-manager'); ?></label>
								<input type="text" name="custom_admin_notification[<?php echo (int) $idx; ?>][url_string_match]" class="regular-text" value="<?php echo esc_attr($n['url_string_match'] ?? ''); ?>" placeholder="shop_coupon"<?php echo $form_attr; ?> />
								<p class="description"><?php esc_html_e('Show only when the current admin URL contains this string. Leave blank to show on all WP-Admin screens.', 'user-manager'); ?></p>
							</div>
							<button type="button" class="button um-remove-admin-notification"><?php esc_html_e('Remove this notification', 'user-manager'); ?></button>
						</div>
					<?php endforeach; ?>
				</div>
				<p>
					<button type="button" class="button" id="um-add-admin-notification"><?php esc_html_e('Add notification', 'user-manager'); ?></button>
				</p>
				</div>
			</div>
		</div>
		<?php
	}

	public static function render_template(string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		?>
		<script type="text/template" id="um-admin-notification-template">
			<div class="um-admin-notification-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
				<h3 class="um-settings-subsection um-admin-notification-number" style="margin-top: 0;"><?php esc_html_e('New notification', 'user-manager'); ?></h3>
				<div class="um-form-field">
					<label><?php esc_html_e('Notification Headline', 'user-manager'); ?></label>
					<input type="text" name="custom_admin_notification[__INDEX__][title]" class="large-text" value=""<?php echo $form_attr; ?> />
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('Notification Body', 'user-manager'); ?></label>
					<textarea name="custom_admin_notification[__INDEX__][body]" rows="5" class="large-text" style="width:100%;"<?php echo $form_attr; ?>></textarea>
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('Background Color', 'user-manager'); ?></label>
					<input type="text" name="custom_admin_notification[__INDEX__][background_color]" class="regular-text" value="" placeholder="red or #202020"<?php echo $form_attr; ?> />
					<p class="description"><?php esc_html_e('CSS values only (e.g. #202020 or red).', 'user-manager'); ?></p>
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('URL String Match', 'user-manager'); ?></label>
					<input type="text" name="custom_admin_notification[__INDEX__][url_string_match]" class="regular-text" value="" placeholder="shop_coupon"<?php echo $form_attr; ?> />
					<p class="description"><?php esc_html_e('Show only when the current admin URL contains this string. Leave blank to show on all WP-Admin screens.', 'user-manager'); ?></p>
				</div>
				<button type="button" class="button um-remove-admin-notification"><?php esc_html_e('Remove this notification', 'user-manager'); ?></button>
			</div>
		</script>
		<?php
	}
}

