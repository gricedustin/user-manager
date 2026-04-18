<?php
/**
 * Add-on card: Order Received Page Customizer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Order_Received_Page_Customizer {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['order_received_page_customizer_enabled']);
		$heading_text = isset($settings['order_received_page_customizer_heading_text'])
			? (string) $settings['order_received_page_customizer_heading_text']
			: 'Order received';
		$paragraph_text = isset($settings['order_received_page_customizer_paragraph_text'])
			? (string) $settings['order_received_page_customizer_paragraph_text']
			: 'Thank you. Your order has been received.';
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-order-received-page-customizer" data-um-active-selectors="#um-order-received-page-customizer-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-format-status"></span>
				<h2><?php esc_html_e('Order Received Page Customizer', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-order-received-page-customizer-enabled" name="order_received_page_customizer_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Override the Order Received page heading (H1) and success paragraph text shown after checkout.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-order-received-page-customizer-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-order-received-page-customizer-heading-text"><?php esc_html_e('Order Received H1 Text', 'user-manager'); ?></label>
						<input type="text" id="um-order-received-page-customizer-heading-text" name="order_received_page_customizer_heading_text" class="regular-text" value="<?php echo esc_attr($heading_text); ?>" placeholder="<?php esc_attr_e('Order received', 'user-manager'); ?>"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label for="um-order-received-page-customizer-paragraph-text"><?php esc_html_e('Order Received Paragraph Text', 'user-manager'); ?></label>
						<textarea id="um-order-received-page-customizer-paragraph-text" name="order_received_page_customizer_paragraph_text" rows="3" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea($paragraph_text); ?></textarea>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

