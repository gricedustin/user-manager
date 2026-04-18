<?php
/**
 * Add-on card: Product Notification.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Product_Notification {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['product_notification_enabled']);
		$bg_color = (string) ($settings['product_notification_bg_color'] ?? '');
		$text_color = (string) ($settings['product_notification_text_color'] ?? '');
		$icon_color = (string) ($settings['product_notification_icon_color'] ?? '');
		$button_bg_color = (string) ($settings['product_notification_button_bg_color'] ?? '');
		$button_text_color = (string) ($settings['product_notification_button_text_color'] ?? '');
		$button_hover_bg_color = (string) ($settings['product_notification_button_hover_bg_color'] ?? '');
		$button_hover_text_color = (string) ($settings['product_notification_button_hover_text_color'] ?? '');
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-product-notification" data-um-active-selectors="#um-product-notification-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-megaphone"></span>
				<h2><?php esc_html_e('Product Notification', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-product-notification-enabled" name="product_notification_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
				</div>
				<div id="um-product-notification-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description"><?php esc_html_e('Adds product-level fields in WooCommerce Product Data > General so you can always show a WooCommerce-style notification above the product page.', 'user-manager'); ?></p>
					<div class="um-form-field">
						<label for="um-product-notification-bg-color"><strong><?php esc_html_e('Notification Background Color Override', 'user-manager'); ?></strong></label>
						<input type="text" id="um-product-notification-bg-color" name="product_notification_bg_color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($bg_color !== '' ? $bg_color : '#1e73be'); ?>" data-default-color="#1e73be"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label for="um-product-notification-text-color"><strong><?php esc_html_e('Notification Text Color Override', 'user-manager'); ?></strong></label>
						<input type="text" id="um-product-notification-text-color" name="product_notification_text_color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($text_color !== '' ? $text_color : '#ffffff'); ?>" data-default-color="#ffffff"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label for="um-product-notification-icon-color"><strong><?php esc_html_e('Notification Checkbox/Icon Color Override', 'user-manager'); ?></strong></label>
						<input type="text" id="um-product-notification-icon-color" name="product_notification_icon_color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($icon_color !== '' ? $icon_color : '#ffffff'); ?>" data-default-color="#ffffff"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label for="um-product-notification-button-bg-color"><strong><?php esc_html_e('Notification Button Background Color Override', 'user-manager'); ?></strong></label>
						<input type="text" id="um-product-notification-button-bg-color" name="product_notification_button_bg_color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($button_bg_color !== '' ? $button_bg_color : '#ffffff'); ?>" data-default-color="#ffffff"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label for="um-product-notification-button-text-color"><strong><?php esc_html_e('Notification Button Text Color Override', 'user-manager'); ?></strong></label>
						<input type="text" id="um-product-notification-button-text-color" name="product_notification_button_text_color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($button_text_color !== '' ? $button_text_color : '#000000'); ?>" data-default-color="#000000"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label for="um-product-notification-button-hover-bg-color"><strong><?php esc_html_e('Notification Button Hover Background Color Override', 'user-manager'); ?></strong></label>
						<input type="text" id="um-product-notification-button-hover-bg-color" name="product_notification_button_hover_bg_color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($button_hover_bg_color !== '' ? $button_hover_bg_color : '#f1f1f1'); ?>" data-default-color="#f1f1f1"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label for="um-product-notification-button-hover-text-color"><strong><?php esc_html_e('Notification Button Hover Text Color Override', 'user-manager'); ?></strong></label>
						<input type="text" id="um-product-notification-button-hover-text-color" name="product_notification_button_hover_text_color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($button_hover_text_color !== '' ? $button_hover_text_color : '#000000'); ?>" data-default-color="#000000"<?php echo $form_attr; ?> />
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

