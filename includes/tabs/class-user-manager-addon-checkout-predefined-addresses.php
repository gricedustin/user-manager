<?php
/**
 * Add-on card: Checkout Pre-Defined Addresses.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Checkout_Predefined_Addresses {

	public static function render(array $settings): void {
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-checkout-predefined" data-um-active-selectors="#um-checkout-ship-to-predefined">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-location-alt"></span>
				<h2><?php esc_html_e('Checkout Pre-Defined Addresses', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="checkout_ship_to_predefined_enabled" id="um-checkout-ship-to-predefined" value="1" <?php checked($settings['checkout_ship_to_predefined_enabled'] ?? false); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Pre-load a dropdown of Ship To addresses on checkout so users can select an office or location. The selected address overwrites the shipping (and optionally billing) address. The field appears where the chosen hook runs (e.g. after order notes on the classic checkout form).', 'user-manager'); ?></p>
				</div>
				<div id="um-checkout-ship-to-predefined-fields" style="<?php echo empty($settings['checkout_ship_to_predefined_enabled']) ? 'display:none;' : ''; ?>">
					<hr style="margin: 16px 0;" />
					<h3 class="um-settings-subsection"><?php esc_html_e('Address list', 'user-manager'); ?></h3>
					<div class="um-form-field">
						<label for="um-checkout-address-list"><?php esc_html_e('Address List (tab-separated from spreadsheet)', 'user-manager'); ?></label>
						<textarea name="checkout_ship_to_address_list" id="um-checkout-address-list" rows="10" class="large-text code" style="width:100%;"><?php echo esc_textarea($settings['checkout_ship_to_address_list'] ?? ''); ?></textarea>
						<p class="description"><?php esc_html_e('Paste from a spreadsheet with columns: Location Name, Address 1, Address 2, City, State, Zip, Country (tab-separated). Do not include a header row. One location per line.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field">
						<label for="um-checkout-please-select"><?php esc_html_e('Default option text (first option in dropdown)', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_please_select" id="um-checkout-please-select" class="regular-text" value="<?php echo esc_attr($settings['checkout_ship_to_please_select'] ?? 'Please select'); ?>" />
					</div>
					<div class="um-form-field">
						<label for="um-checkout-selection-required"><?php esc_html_e('Selection required?', 'user-manager'); ?></label>
						<select name="checkout_ship_to_selection_required" id="um-checkout-selection-required" class="regular-text">
							<option value="yes" <?php selected($settings['checkout_ship_to_selection_required'] ?? 'no', 'yes'); ?>><?php esc_html_e('Yes', 'user-manager'); ?></option>
							<option value="no" <?php selected($settings['checkout_ship_to_selection_required'] ?? 'no', 'no'); ?>><?php esc_html_e('No', 'user-manager'); ?></option>
						</select>
					</div>
					<div class="um-form-field">
						<label for="um-checkout-required-error"><?php esc_html_e('Error message when not selected', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_required_error" id="um-checkout-required-error" class="large-text" value="<?php echo esc_attr($settings['checkout_ship_to_required_error'] ?? __('Please make a selection.', 'user-manager')); ?>" />
					</div>
					<div class="um-form-field">
						<label for="um-checkout-field-hook"><?php esc_html_e('Checkout field location', 'user-manager'); ?></label>
						<select name="checkout_ship_to_field_hook" id="um-checkout-field-hook" class="regular-text">
							<option value="woocommerce_after_order_notes" <?php selected($settings['checkout_ship_to_field_hook'] ?? 'woocommerce_after_order_notes', 'woocommerce_after_order_notes'); ?>><?php esc_html_e('After order notes', 'user-manager'); ?></option>
							<option value="woocommerce_before_checkout_billing_form" <?php selected($settings['checkout_ship_to_field_hook'] ?? '', 'woocommerce_before_checkout_billing_form'); ?>><?php esc_html_e('Before billing form', 'user-manager'); ?></option>
							<option value="woocommerce_after_checkout_billing_form" <?php selected($settings['checkout_ship_to_field_hook'] ?? '', 'woocommerce_after_checkout_billing_form'); ?>><?php esc_html_e('After billing form', 'user-manager'); ?></option>
							<option value="woocommerce_before_checkout_shipping_form" <?php selected($settings['checkout_ship_to_field_hook'] ?? '', 'woocommerce_before_checkout_shipping_form'); ?>><?php esc_html_e('Before shipping form', 'user-manager'); ?></option>
							<option value="woocommerce_after_checkout_shipping_form" <?php selected($settings['checkout_ship_to_field_hook'] ?? '', 'woocommerce_after_checkout_shipping_form'); ?>><?php esc_html_e('After shipping form', 'user-manager'); ?></option>
						</select>
						<p class="description"><?php esc_html_e('Where the Shipping Location dropdown appears on checkout. Use this to move it below billing, below shipping, or after order notes.', 'user-manager'); ?></p>
					</div>
					<hr style="margin: 16px 0;" />
					<h3 class="um-settings-subsection"><?php esc_html_e('Default address (hidden fallback for order submit)', 'user-manager'); ?></h3>
					<p class="description"><?php esc_html_e('A default address is used so the order can submit; the selected predefined address then overwrites shipping (and optionally billing).', 'user-manager'); ?></p>
					<div class="um-form-field">
						<label for="um-checkout-default-address"><?php esc_html_e('Default address line 1', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_default_address" id="um-checkout-default-address" class="large-text" value="<?php echo esc_attr($settings['checkout_ship_to_default_address'] ?? '4001 Lake Breeze Ave N #40'); ?>" />
					</div>
					<div class="um-form-field">
						<label for="um-checkout-default-city"><?php esc_html_e('Default city', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_default_city" id="um-checkout-default-city" class="regular-text" value="<?php echo esc_attr($settings['checkout_ship_to_default_city'] ?? 'BROOKLYN CENTER'); ?>" />
					</div>
					<div class="um-form-field">
						<label for="um-checkout-default-state"><?php esc_html_e('Default state (2 characters)', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_default_state" id="um-checkout-default-state" class="small-text" value="<?php echo esc_attr($settings['checkout_ship_to_default_state'] ?? 'MN'); ?>" maxlength="2" />
					</div>
					<div class="um-form-field">
						<label for="um-checkout-default-zip"><?php esc_html_e('Default ZIP', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_default_zip" id="um-checkout-default-zip" class="small-text" value="<?php echo esc_attr($settings['checkout_ship_to_default_zip'] ?? '55429'); ?>" />
					</div>
					<div class="um-form-field">
						<label for="um-checkout-default-country"><?php esc_html_e('Default country (2 characters)', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_default_country" id="um-checkout-default-country" class="small-text" value="<?php echo esc_attr($settings['checkout_ship_to_default_country'] ?? 'US'); ?>" maxlength="2" />
					</div>
					<hr style="margin: 16px 0;" />
					<h3 class="um-settings-subsection"><?php esc_html_e('Overwrite behavior', 'user-manager'); ?></h3>
					<div class="um-form-field">
						<label for="um-checkout-overwrite-billing"><?php esc_html_e('Overwrite billing address with selected address', 'user-manager'); ?></label>
						<select name="checkout_ship_to_overwrite_billing" id="um-checkout-overwrite-billing" class="regular-text">
							<option value="yes" <?php selected($settings['checkout_ship_to_overwrite_billing'] ?? 'no', 'yes'); ?>><?php esc_html_e('Yes', 'user-manager'); ?></option>
							<option value="no" <?php selected($settings['checkout_ship_to_overwrite_billing'] ?? 'no', 'no'); ?>><?php esc_html_e('No', 'user-manager'); ?></option>
						</select>
						<p class="description"><?php esc_html_e('No = customer can still enter their own billing address.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field">
						<label for="um-checkout-overwrite-shipping"><?php esc_html_e('Overwrite shipping address with selected address', 'user-manager'); ?></label>
						<select name="checkout_ship_to_overwrite_shipping" id="um-checkout-overwrite-shipping" class="regular-text">
							<option value="yes" <?php selected($settings['checkout_ship_to_overwrite_shipping'] ?? 'yes', 'yes'); ?>><?php esc_html_e('Yes', 'user-manager'); ?></option>
							<option value="yes_if_same" <?php selected($settings['checkout_ship_to_overwrite_shipping'] ?? '', 'yes_if_same'); ?>><?php esc_html_e('Yes, but only if "Ship to a different address" is unchecked', 'user-manager'); ?></option>
							<option value="no" <?php selected($settings['checkout_ship_to_overwrite_shipping'] ?? '', 'no'); ?>><?php esc_html_e('No', 'user-manager'); ?></option>
						</select>
					</div>
					<div class="um-form-field">
						<label for="um-checkout-company-override"><?php esc_html_e('Company override', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_company_override" id="um-checkout-company-override" class="large-text" value="<?php echo esc_attr($settings['checkout_ship_to_company_override'] ?? ''); ?>" />
						<p class="description"><?php esc_html_e('Optional. Set a company name to use on the shipping (and billing if overwritten) address.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field">
						<label for="um-checkout-first-name-prefix"><?php esc_html_e('First name prefix', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_first_name_prefix" id="um-checkout-first-name-prefix" class="regular-text" value="<?php echo esc_attr($settings['checkout_ship_to_first_name_prefix'] ?? 'Attn: '); ?>" />
						<p class="description"><?php esc_html_e('e.g. Attn: — prepended to the first name on the shipping address.', 'user-manager'); ?></p>
					</div>
					<hr style="margin: 16px 0;" />
					<h3 class="um-settings-subsection"><?php esc_html_e('Labels & layout', 'user-manager'); ?></h3>
					<div class="um-form-field">
						<label for="um-checkout-field-label"><?php esc_html_e('Field label', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_field_label" id="um-checkout-field-label" class="regular-text" value="<?php echo esc_attr($settings['checkout_ship_to_field_label'] ?? __('Choose your office.', 'user-manager')); ?>" />
					</div>
					<div class="um-form-field">
						<label for="um-checkout-area-title"><?php esc_html_e('Section title above dropdown', 'user-manager'); ?></label>
						<input type="text" name="checkout_ship_to_area_title" id="um-checkout-area-title" class="regular-text" value="<?php echo esc_attr($settings['checkout_ship_to_area_title'] ?? __('Shipping Location', 'user-manager')); ?>" />
						<p class="description"><?php esc_html_e('Use "blank" to hide the title.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field">
						<label for="um-checkout-single-column"><?php esc_html_e('Single column checkout', 'user-manager'); ?></label>
						<select name="checkout_ship_to_single_column" id="um-checkout-single-column" class="regular-text">
							<option value="no" <?php selected($settings['checkout_ship_to_single_column'] ?? 'no', 'no'); ?>><?php esc_html_e('No', 'user-manager'); ?></option>
							<option value="yes" <?php selected($settings['checkout_ship_to_single_column'] ?? '', 'yes'); ?>><?php esc_html_e('Yes', 'user-manager'); ?></option>
						</select>
					</div>
					<hr style="margin: 16px 0;" />
					<h3 class="um-settings-subsection"><?php esc_html_e('Auto-hide checkout elements', 'user-manager'); ?></h3>
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="checkout_ship_to_auto_hide_shipping" value="1" <?php checked($settings['checkout_ship_to_auto_hide_shipping'] ?? false); ?> />
							<?php esc_html_e('Auto-hide all shipping address fields', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="checkout_ship_to_auto_hide_company" value="1" <?php checked($settings['checkout_ship_to_auto_hide_company'] ?? true); ?> />
							<?php esc_html_e('Auto-hide company field', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="checkout_ship_to_auto_hide_notes" value="1" <?php checked($settings['checkout_ship_to_auto_hide_notes'] ?? false); ?> />
							<?php esc_html_e('Auto-hide order notes field', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="checkout_ship_to_hide_coupon" value="1" <?php checked($settings['checkout_ship_to_hide_coupon'] ?? false); ?> />
							<?php esc_html_e('Auto-hide coupon field', 'user-manager'); ?>
						</label>
					</div>
					<hr style="margin: 16px 0;" />
					<h3 class="um-settings-subsection"><?php esc_html_e('Troubleshooting', 'user-manager'); ?></h3>
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="checkout_ship_to_show_debug" id="um-checkout-show-debug" value="1" <?php checked($settings['checkout_ship_to_show_debug'] ?? false); ?> />
							<?php esc_html_e('Show debugging info for admins', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('When checked, a detailed debug box is shown on the checkout page (only to users who can manage options). Use it to verify settings, parsed addresses, and hooks.', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

