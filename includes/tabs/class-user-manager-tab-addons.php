<?php
/**
 * Add-ons tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Addons {

	public static function render(): void {
		$settings      = User_Manager_Core::get_settings();
		$bulk_settings = get_option('bulk_add_to_cart_settings', []);
		?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="user_manager_save_settings" />
			<input type="hidden" name="settings_section" value="addons" />
			<?php wp_nonce_field('user_manager_save_settings'); ?>
			<div class="um-admin-grid um-admin-grid-2col">
				<!-- My Account Site Admin -->
				<div class="um-admin-card um-addon-collapsible" id="um-addon-card-my-account" data-um-active-selectors="#um-my-account-admin-order-viewer-enabled,#um-my-account-admin-product-viewer-enabled,#um-my-account-admin-coupon-viewer-enabled,#um-my-account-admin-user-viewer-enabled">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-admin-site"></span>
						<h2><?php esc_html_e('My Account Site Admin', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Add admin-style viewer pages inside WooCommerce My Account for selected users. Enter comma-separated usernames (user_login values) allowed to access each area.', 'user-manager'); ?></p>

						<div class="um-form-field">
							<label>
								<input type="checkbox" name="my_account_admin_order_viewer_enabled" id="um-my-account-admin-order-viewer-enabled" value="1" <?php checked($settings['my_account_admin_order_viewer_enabled'] ?? false); ?> />
								<?php esc_html_e('My Account Admin Order Viewer', 'user-manager'); ?>
							</label>
						</div>
						<div class="um-form-field" id="um-my-account-admin-order-viewer-users-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label for="um-my-account-admin-order-viewer-usernames"><?php esc_html_e('Allowed usernames (comma-separated)', 'user-manager'); ?></label>
							<input type="text" name="my_account_admin_order_viewer_usernames" id="um-my-account-admin-order-viewer-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_order_viewer_usernames'] ?? ''); ?>" placeholder="username1, username2" />
							<p class="description"><?php esc_html_e('Usernames allowed to view the Admin: Orders My Account area.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field" id="um-my-account-admin-order-approver-users-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label for="um-my-account-admin-order-approval-usernames"><?php esc_html_e('Order approval allowed usernames (comma-separated)', 'user-manager'); ?></label>
							<input type="text" name="my_account_admin_order_approval_usernames" id="um-my-account-admin-order-approval-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_order_approval_usernames'] ?? ''); ?>" placeholder="approver1, approver2" />
							<p class="description"><?php esc_html_e('These users can see an "Approve" button for pending payment orders, which moves the order to Processing.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field" id="um-my-account-admin-order-default-pending-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label>
								<input type="checkbox" name="my_account_admin_order_default_pending_enabled" id="um-my-account-admin-order-default-pending-enabled" value="1" <?php checked($settings['my_account_admin_order_default_pending_enabled'] ?? false); ?> />
								<?php esc_html_e('Default all new orders into a payment pending status', 'user-manager'); ?>
							</label>
						</div>
						<div class="um-form-field" id="um-my-account-admin-order-meta-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label>
								<input type="checkbox" name="my_account_admin_order_viewer_show_meta" id="um-my-account-admin-order-viewer-show-meta" value="1" <?php checked($settings['my_account_admin_order_viewer_show_meta'] ?? false); ?> />
								<?php esc_html_e('Show Meta Data area for Order details', 'user-manager'); ?>
							</label>
						</div>

						<div class="um-form-field">
							<label>
								<input type="checkbox" name="my_account_admin_product_viewer_enabled" id="um-my-account-admin-product-viewer-enabled" value="1" <?php checked($settings['my_account_admin_product_viewer_enabled'] ?? false); ?> />
								<?php esc_html_e('My Account Admin Product Viewer', 'user-manager'); ?>
							</label>
						</div>
						<div class="um-form-field" id="um-my-account-admin-product-viewer-users-field" style="<?php echo empty($settings['my_account_admin_product_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label for="um-my-account-admin-product-viewer-usernames"><?php esc_html_e('Allowed usernames (comma-separated)', 'user-manager'); ?></label>
							<input type="text" name="my_account_admin_product_viewer_usernames" id="um-my-account-admin-product-viewer-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_product_viewer_usernames'] ?? ''); ?>" placeholder="username1, username2" />
						</div>
						<div class="um-form-field" id="um-my-account-admin-product-meta-field" style="<?php echo empty($settings['my_account_admin_product_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label>
								<input type="checkbox" name="my_account_admin_product_viewer_show_meta" id="um-my-account-admin-product-viewer-show-meta" value="1" <?php checked($settings['my_account_admin_product_viewer_show_meta'] ?? false); ?> />
								<?php esc_html_e('Show Meta Data area for Product details', 'user-manager'); ?>
							</label>
						</div>

						<div class="um-form-field">
							<label>
								<input type="checkbox" name="my_account_admin_coupon_viewer_enabled" id="um-my-account-admin-coupon-viewer-enabled" value="1" <?php checked($settings['my_account_admin_coupon_viewer_enabled'] ?? false); ?> />
								<?php esc_html_e('My Account Admin Coupon Viewer', 'user-manager'); ?>
							</label>
						</div>
						<div class="um-form-field" id="um-my-account-admin-coupon-viewer-users-field" style="<?php echo empty($settings['my_account_admin_coupon_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label for="um-my-account-admin-coupon-viewer-usernames"><?php esc_html_e('Allowed usernames (comma-separated)', 'user-manager'); ?></label>
							<input type="text" name="my_account_admin_coupon_viewer_usernames" id="um-my-account-admin-coupon-viewer-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_coupon_viewer_usernames'] ?? ''); ?>" placeholder="username1, username2" />
						</div>
						<div class="um-form-field" id="um-my-account-admin-coupon-meta-field" style="<?php echo empty($settings['my_account_admin_coupon_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label>
								<input type="checkbox" name="my_account_admin_coupon_viewer_show_meta" id="um-my-account-admin-coupon-viewer-show-meta" value="1" <?php checked($settings['my_account_admin_coupon_viewer_show_meta'] ?? false); ?> />
								<?php esc_html_e('Show Meta Data area for Coupon details', 'user-manager'); ?>
							</label>
						</div>

						<div class="um-form-field">
							<label>
								<input type="checkbox" name="my_account_admin_user_viewer_enabled" id="um-my-account-admin-user-viewer-enabled" value="1" <?php checked($settings['my_account_admin_user_viewer_enabled'] ?? false); ?> />
								<?php esc_html_e('My Account Admin User Viewer', 'user-manager'); ?>
							</label>
						</div>
						<div class="um-form-field" id="um-my-account-admin-user-viewer-users-field" style="<?php echo empty($settings['my_account_admin_user_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label for="um-my-account-admin-user-viewer-usernames"><?php esc_html_e('Allowed usernames (comma-separated)', 'user-manager'); ?></label>
							<input type="text" name="my_account_admin_user_viewer_usernames" id="um-my-account-admin-user-viewer-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_user_viewer_usernames'] ?? ''); ?>" placeholder="username1, username2" />
						</div>
						<div class="um-form-field" id="um-my-account-admin-user-meta-field" style="<?php echo empty($settings['my_account_admin_user_viewer_enabled']) ? 'display:none;' : ''; ?>">
							<label>
								<input type="checkbox" name="my_account_admin_user_viewer_show_meta" id="um-my-account-admin-user-viewer-show-meta" value="1" <?php checked($settings['my_account_admin_user_viewer_show_meta'] ?? false); ?> />
								<?php esc_html_e('Show Meta Data area for User details', 'user-manager'); ?>
							</label>
						</div>
					</div>
				</div>

				<!-- Bulk Add to Cart -->
				<div class="um-admin-card um-addon-collapsible" id="um-addon-card-bulk-add-to-cart" data-um-active-selectors="input[name='bulk_add_to_cart_enabled']">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-cart"></span>
						<h2><?php esc_html_e('Bulk Add to Cart', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="bulk_add_to_cart_enabled" value="1" <?php checked($settings['bulk_add_to_cart_enabled'] ?? false); ?> />
								<?php esc_html_e('Activate Bulk Add to Cart Functionality', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('Enables the [bulk_add_to_cart] CSV upload form on the front-end and processes uploaded files to add multiple products to the WooCommerce cart.', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label class="um-label-block"><?php esc_html_e('Shortcode usage', 'user-manager'); ?></label>
							<input type="text" readonly class="regular-text code" value="[bulk_add_to_cart]" onclick="this.select();" />
							<p class="description">
								<?php esc_html_e('Place this shortcode on a page to show the CSV upload form for logged-in users.', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label class="um-label-block"><?php esc_html_e('Front-end debug URL parameter', 'user-manager'); ?></label>
							<input type="text" readonly class="large-text code" value="?um_bulk_add_to_cart_debug=1" onclick="this.select();" />
							<p class="description">
								<?php esc_html_e('Append this parameter to the page with [bulk_add_to_cart] to force verbose debug notices for that request, even if debug mode is unchecked.', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="bulk_add_to_cart_redirect_to_cart" value="1" <?php checked(($bulk_settings['redirect_to_cart'] ?? '1') === '1'); ?> />
								<?php esc_html_e('Redirect to cart page after processing CSV file', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('When enabled, users will be automatically redirected to the cart page after the CSV has been processed.', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label for="um-bulk-identifier-column"><?php esc_html_e('Product Identifier Column', 'user-manager'); ?></label>
							<input type="text" name="bulk_add_to_cart_identifier_column" id="um-bulk-identifier-column" class="regular-text" value="<?php echo esc_attr($bulk_settings['identifier_column'] ?? 'product_id'); ?>" />
							<p class="description">
								<?php esc_html_e('Exact column header in your CSV that contains the product identifier (ID, SKU, slug, title, or meta field value).', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label for="um-bulk-identifier-type"><?php esc_html_e('Identifier Type', 'user-manager'); ?></label>
							<select name="bulk_add_to_cart_identifier_type" id="um-bulk-identifier-type" class="regular-text">
								<?php
								$identifier_type = $bulk_settings['identifier_type'] ?? 'product_id';
								?>
								<option value="product_id" <?php selected($identifier_type, 'product_id'); ?>><?php esc_html_e('Product ID', 'user-manager'); ?></option>
								<option value="product_sku" <?php selected($identifier_type, 'product_sku'); ?>><?php esc_html_e('Product SKU', 'user-manager'); ?></option>
								<option value="product_slug" <?php selected($identifier_type, 'product_slug'); ?>><?php esc_html_e('Product Slug', 'user-manager'); ?></option>
								<option value="product_title" <?php selected($identifier_type, 'product_title'); ?>><?php esc_html_e('Product Title', 'user-manager'); ?></option>
								<option value="meta_field" <?php selected($identifier_type, 'meta_field'); ?>><?php esc_html_e('Custom Meta Field Value', 'user-manager'); ?></option>
							</select>
							<p class="description">
								<?php esc_html_e('Choose how products should be looked up from the CSV identifier column.', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field" id="um-bulk-meta-field-name-row">
							<label for="um-bulk-meta-field-name"><?php esc_html_e('Meta Field Name (when using Custom Meta Field Value)', 'user-manager'); ?></label>
							<input type="text" name="bulk_add_to_cart_meta_field_name" id="um-bulk-meta-field-name" class="regular-text" value="<?php echo esc_attr($bulk_settings['meta_field_name'] ?? ''); ?>" />
							<p class="description">
								<?php esc_html_e('Meta key that contains the unique identifier used in your CSV when Identifier Type is "Custom Meta Field Value".', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label for="um-bulk-quantity-column"><?php esc_html_e('Quantity Column', 'user-manager'); ?></label>
							<input type="text" name="bulk_add_to_cart_quantity_column" id="um-bulk-quantity-column" class="regular-text" value="<?php echo esc_attr($bulk_settings['quantity_column'] ?? 'quantity'); ?>" />
							<p class="description">
								<?php esc_html_e('Exact column header in your CSV that contains the quantity for each product row.', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="bulk_add_to_cart_debug_mode" value="1" <?php checked(($bulk_settings['debug_mode'] ?? '0') === '1'); ?> />
								<?php esc_html_e('Enable debug mode for Bulk Add to Cart', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('When enabled, extra WooCommerce notices will describe how the CSV was parsed and how each row was handled.', 'user-manager'); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Checkout: Ship To Pre-Defined Addresses -->
				<div class="um-admin-card um-addon-collapsible" id="um-addon-card-checkout-predefined" data-um-active-selectors="#um-checkout-ship-to-predefined">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-location-alt"></span>
						<h2><?php esc_html_e('Checkout Pre-Defined Addresses', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="checkout_ship_to_predefined_enabled" id="um-checkout-ship-to-predefined" value="1" <?php checked($settings['checkout_ship_to_predefined_enabled'] ?? false); ?> />
								<?php esc_html_e('Activate Ship To Pre-Defined Addresses', 'user-manager'); ?>
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

				<!-- Custom WP-Admin Notifications -->
				<div class="um-admin-card um-addon-collapsible" id="um-addon-card-custom-notifications">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-megaphone"></span>
						<h2><?php esc_html_e('Custom WP-Admin Notifications', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Add custom admin notices at the top of WP-Admin screens. Each notification can be limited to URLs that contain a specific string (e.g. shop_coupon for coupon edit screens), or shown on all admin screens if URL match is blank.', 'user-manager'); ?></p>
						<?php
						$admin_notifications = isset($settings['custom_admin_notifications']) && is_array($settings['custom_admin_notifications']) ? $settings['custom_admin_notifications'] : [];
						if (empty($admin_notifications)) {
							$admin_notifications = [['title' => '', 'body' => '', 'background_color' => '', 'url_string_match' => '']];
						}
						?>
						<div id="um-custom-admin-notifications-list">
							<?php foreach ($admin_notifications as $idx => $n) : ?>
								<div class="um-admin-notification-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
									<h3 class="um-settings-subsection um-admin-notification-number" style="margin-top: 0;"><?php echo esc_html(sprintf(__('Notification %d', 'user-manager'), $idx + 1)); ?></h3>
									<div class="um-form-field">
										<label><?php esc_html_e('Notification Headline', 'user-manager'); ?></label>
										<input type="text" name="custom_admin_notification[<?php echo (int) $idx; ?>][title]" class="large-text" value="<?php echo esc_attr($n['title'] ?? ''); ?>" />
									</div>
									<div class="um-form-field">
										<label><?php esc_html_e('Notification Body', 'user-manager'); ?></label>
										<textarea name="custom_admin_notification[<?php echo (int) $idx; ?>][body]" rows="5" class="large-text" style="width:100%;"><?php echo esc_textarea($n['body'] ?? ''); ?></textarea>
									</div>
									<div class="um-form-field">
										<label><?php esc_html_e('Background Color', 'user-manager'); ?></label>
										<input type="text" name="custom_admin_notification[<?php echo (int) $idx; ?>][background_color]" class="regular-text" value="<?php echo esc_attr($n['background_color'] ?? ''); ?>" placeholder="red or #202020" />
										<p class="description"><?php esc_html_e('CSS values only (e.g. #202020 or red).', 'user-manager'); ?></p>
									</div>
									<div class="um-form-field">
										<label><?php esc_html_e('URL String Match', 'user-manager'); ?></label>
										<input type="text" name="custom_admin_notification[<?php echo (int) $idx; ?>][url_string_match]" class="regular-text" value="<?php echo esc_attr($n['url_string_match'] ?? ''); ?>" placeholder="shop_coupon" />
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

				<!-- WP-Admin Bar Menu Items -->
				<div class="um-admin-card um-addon-collapsible" id="um-addon-card-admin-bar-menu">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-menu-alt"></span>
						<h2><?php esc_html_e('WP-Admin Bar Menu Items', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Add custom shortcut menus to the WordPress admin bar (visible on both front-end and back-end). Each item becomes a dropdown; add links line by line as "Label|URL" or use "Group Title|divider" to create a section header.', 'user-manager'); ?></p>
						<?php
						$admin_bar_items = isset($settings['admin_bar_menu_items']) && is_array($settings['admin_bar_menu_items']) ? $settings['admin_bar_menu_items'] : [];
						if (empty($admin_bar_items)) {
							$admin_bar_items = [['title' => '', 'icon' => '', 'shortcuts' => '']];
						}
						?>
						<div id="um-admin-bar-menu-list">
							<?php foreach ($admin_bar_items as $idx => $item) : ?>
								<div class="um-admin-bar-menu-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
									<h3 class="um-settings-subsection um-admin-bar-menu-number" style="margin-top: 0;"><?php echo esc_html(sprintf(__('Menu %d', 'user-manager'), $idx + 1)); ?></h3>
									<div class="um-form-field">
										<label><?php esc_html_e('Title of shortcuts menu item', 'user-manager'); ?></label>
										<input type="text" name="admin_bar_menu_item[<?php echo (int) $idx; ?>][title]" class="large-text" value="<?php echo esc_attr($item['title'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g. Admin Menu', 'user-manager'); ?>" />
									</div>
									<div class="um-form-field">
										<label><?php esc_html_e('Icon override', 'user-manager'); ?></label>
										<input type="text" name="admin_bar_menu_item[<?php echo (int) $idx; ?>][icon]" class="regular-text" value="<?php echo esc_attr($item['icon'] ?? ''); ?>" placeholder="dashicons-admin-links" />
										<p class="description"><?php esc_html_e('Dashicons class (e.g. dashicons-admin-links). See', 'user-manager'); ?> <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener">developer.wordpress.org/resource/dashicons</a></p>
									</div>
									<div class="um-form-field">
										<label><?php esc_html_e('Shortcuts', 'user-manager'); ?></label>
										<textarea name="admin_bar_menu_item[<?php echo (int) $idx; ?>][shortcuts]" rows="8" class="large-text" style="width:100%;" placeholder="Coupon Manager|https://example.com/wp-admin/edit.php?post_type=shop_coupon"><?php echo esc_textarea($item['shortcuts'] ?? ''); ?></textarea>
										<p class="description"><?php esc_html_e('One per line: Link Title|URL. Use "Group Title|divider" for a section header. All link titles must be unique.', 'user-manager'); ?></p>
									</div>
									<button type="button" class="button um-remove-admin-bar-menu"><?php esc_html_e('Remove this menu', 'user-manager'); ?></button>
								</div>
							<?php endforeach; ?>
						</div>
						<p>
							<button type="button" class="button" id="um-add-admin-bar-menu"><?php esc_html_e('Add menu', 'user-manager'); ?></button>
						</p>
					</div>
				</div>

				<!-- WP-Admin CSS -->
				<div class="um-admin-card um-addon-collapsible" id="um-addon-card-admin-css">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-art"></span>
						<h2><?php esc_html_e('WP-Admin CSS', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Apply custom CSS only in the WordPress admin (wp-admin). You can target all roles, exclude specific roles from the global CSS, apply CSS to specific users (by login, email, or ID), and/or add per-role CSS.', 'user-manager'); ?></p>

						<div id="um-wp-admin-css-list">
							<div class="um-wp-admin-css-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
								<h3 class="um-settings-subsection" style="margin-top: 0;"><?php esc_html_e('All Roles CSS', 'user-manager'); ?></h3>
								<div class="um-form-field">
									<label for="um-wp-admin-css-all"><?php esc_html_e('CSS applied to all roles in wp-admin', 'user-manager'); ?></label>
									<textarea name="wp_admin_css_all" id="um-wp-admin-css-all" class="large-text code" rows="8" style="width:100%; font-family: Consolas, Monaco, monospace;" placeholder="body { padding: 0; }"><?php echo esc_textarea($settings['wp_admin_css_all'] ?? ''); ?></textarea>
									<p class="description"><?php esc_html_e('Example: body { padding: 0; }', 'user-manager'); ?></p>
								</div>
								<div class="um-form-field">
									<label for="um-wp-admin-css-exclude-roles"><?php esc_html_e('Roles to exclude from All Roles CSS above', 'user-manager'); ?></label>
									<input type="text" name="wp_admin_css_exclude_roles" id="um-wp-admin-css-exclude-roles" class="large-text" value="<?php echo esc_attr(is_array($settings['wp_admin_css_exclude_roles'] ?? null) ? implode(', ', $settings['wp_admin_css_exclude_roles']) : ($settings['wp_admin_css_exclude_roles'] ?? '')); ?>" placeholder="administrator, editor" />
									<p class="description"><?php esc_html_e('Comma-separated role slugs. Users with any of these roles will not receive the All Roles CSS.', 'user-manager'); ?></p>
								</div>
							</div>

							<div class="um-wp-admin-css-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
								<h3 class="um-settings-subsection" style="margin-top: 0;"><?php esc_html_e('User-based CSS', 'user-manager'); ?></h3>
								<div class="um-form-field">
									<label for="um-wp-admin-css-users-css"><?php esc_html_e('CSS applied only to specific users', 'user-manager'); ?></label>
									<textarea name="wp_admin_css_users_css" id="um-wp-admin-css-users-css" class="large-text code" rows="8" style="width:100%; font-family: Consolas, Monaco, monospace;" placeholder="#wpadminbar { display: none; }"><?php echo esc_textarea($settings['wp_admin_css_users_css'] ?? ''); ?></textarea>
									<p class="description"><?php esc_html_e('Example: #wpadminbar { display: none; }', 'user-manager'); ?></p>
								</div>
								<div class="um-form-field">
									<label for="um-wp-admin-css-users-include"><?php esc_html_e('Users who receive the CSS above', 'user-manager'); ?></label>
									<input type="text" name="wp_admin_css_users_include" id="um-wp-admin-css-users-include" class="large-text" value="<?php echo esc_attr(is_array($settings['wp_admin_css_users_include'] ?? null) ? implode(', ', $settings['wp_admin_css_users_include']) : ($settings['wp_admin_css_users_include'] ?? '')); ?>" placeholder="admin@example.com, jane, 123" />
									<p class="description"><?php esc_html_e('Comma-separated: usernames (login), email addresses, or user IDs.', 'user-manager'); ?></p>
								</div>
							</div>

							<?php
							$wp_admin_css_roles = isset($settings['wp_admin_css_roles']) && is_array($settings['wp_admin_css_roles']) ? $settings['wp_admin_css_roles'] : [];
							foreach (User_Manager_Core::get_user_roles() as $role_key => $role_name) :
								$role_css = $wp_admin_css_roles[$role_key] ?? '';
							?>
							<div class="um-wp-admin-css-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
								<h3 class="um-settings-subsection" style="margin-top: 0;"><?php echo esc_html(sprintf(__('Role: %s', 'user-manager'), $role_name)); ?></h3>
								<div class="um-form-field">
									<label for="um-wp-admin-css-role-<?php echo esc_attr($role_key); ?>"><?php echo esc_html(sprintf(__('WP-Admin CSS for role: %s', 'user-manager'), $role_name)); ?></label>
									<textarea name="wp_admin_css_role[<?php echo esc_attr($role_key); ?>]" id="um-wp-admin-css-role-<?php echo esc_attr($role_key); ?>" class="large-text code" rows="6" style="width:100%; font-family: Consolas, Monaco, monospace;"><?php echo esc_textarea($role_css); ?></textarea>
									<p class="description"><?php esc_html_e('Leave blank to skip this role.', 'user-manager'); ?></p>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- API -->
				<div class="um-admin-card um-addon-collapsible" id="um-addon-card-api" data-um-active-selectors="#um-openai-page-meta-box">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-admin-generic"></span>
						<h2><?php esc_html_e('API', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label for="um-openai-api-key"><?php esc_html_e('ChatGPT / OpenAI API Key', 'user-manager'); ?></label>
							<input type="password" name="openai_api_key" id="um-openai-api-key" class="regular-text" value="<?php echo esc_attr($settings['openai_api_key'] ?? ''); ?>" autocomplete="off" />
							<p class="description"><?php esc_html_e('Used for the Blog Post Importer "Auto write from ChatGPT" on the Tools tab. Leave empty to hide that feature. Get an API key from platform.openai.com.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-openai-prompt-append"><?php esc_html_e('Appended Information to AI Prompt', 'user-manager'); ?></label>
							<textarea name="openai_prompt_append" id="um-openai-prompt-append" class="large-text" rows="4" style="width:100%;"><?php echo esc_textarea($settings['openai_prompt_append'] ?? ''); ?></textarea>
							<p class="description"><?php esc_html_e('This text is added to the end of the prompt on every "Auto write from ChatGPT" request to provide extra context (e.g. tone, audience, keywords). Leave blank to skip.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label><input type="checkbox" id="um-openai-page-meta-box" name="openai_page_meta_box" value="1" <?php checked(!empty($settings['openai_page_meta_box'])); ?> /> <?php esc_html_e('Enable Page & Post Meta Box for inserting ChatGPT generated content into pages & posts', 'user-manager'); ?></label>
							<p class="description"><?php esc_html_e('When enabled, page edit screens show a meta box to generate content via ChatGPT and insert it at the bottom of the page (block format).', 'user-manager'); ?></p>
						</div>
					</div>
				</div>

				<div class="um-admin-card um-admin-card-full">
					<div class="um-admin-card-body">
						<p style="margin:0;">
							<?php submit_button(__('Save Add-ons', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</div>
				</div>
			</div>
		</form>
		<?php
		$role_switch_settings = get_option('view_website_by_role_settings', []);
		$role_switch_enabled  = !empty($role_switch_settings['enabled']);
		?>
		<div class="um-admin-grid" style="margin-top:18px;">
			<div class="um-admin-card um-admin-card-full um-addon-collapsible" id="um-addon-card-role-switching" data-um-active-selectors="#um-role-switching-enabled">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-visibility"></span>
					<h2><?php esc_html_e('Role Switching', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p class="description" style="margin-top:0;">
						<?php esc_html_e('Role Switching has been moved into Add-ons. Expand this section to configure role switching settings and review role-switch history.', 'user-manager'); ?>
					</p>
					<details id="um-role-switching-embedded" <?php echo $role_switch_enabled ? 'open' : ''; ?>>
						<summary style="cursor:pointer;font-weight:600;">
							<?php echo $role_switch_enabled ? esc_html__('Role Switching is active — click to expand/collapse', 'user-manager') : esc_html__('Role Switching is inactive — click to expand/collapse', 'user-manager'); ?>
						</summary>
						<div style="margin-top:12px;">
							<?php
							if (class_exists('User_Manager_Tab_Role_Switching')) {
								User_Manager_Tab_Role_Switching::render();
							}
							?>
						</div>
					</details>
				</div>
			</div>
		</div>
		<script type="text/template" id="um-admin-notification-template">
			<div class="um-admin-notification-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
				<h3 class="um-settings-subsection um-admin-notification-number" style="margin-top: 0;"><?php esc_html_e('New notification', 'user-manager'); ?></h3>
				<div class="um-form-field">
					<label><?php esc_html_e('Notification Headline', 'user-manager'); ?></label>
					<input type="text" name="custom_admin_notification[__INDEX__][title]" class="large-text" value="" />
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('Notification Body', 'user-manager'); ?></label>
					<textarea name="custom_admin_notification[__INDEX__][body]" rows="5" class="large-text" style="width:100%;"></textarea>
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('Background Color', 'user-manager'); ?></label>
					<input type="text" name="custom_admin_notification[__INDEX__][background_color]" class="regular-text" value="" placeholder="red or #202020" />
					<p class="description"><?php esc_html_e('CSS values only (e.g. #202020 or red).', 'user-manager'); ?></p>
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('URL String Match', 'user-manager'); ?></label>
					<input type="text" name="custom_admin_notification[__INDEX__][url_string_match]" class="regular-text" value="" placeholder="shop_coupon" />
					<p class="description"><?php esc_html_e('Show only when the current admin URL contains this string. Leave blank to show on all WP-Admin screens.', 'user-manager'); ?></p>
				</div>
				<button type="button" class="button um-remove-admin-notification"><?php esc_html_e('Remove this notification', 'user-manager'); ?></button>
			</div>
		</script>
		<script type="text/template" id="um-admin-bar-menu-template">
			<div class="um-admin-bar-menu-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
				<h3 class="um-settings-subsection um-admin-bar-menu-number" style="margin-top: 0;"><?php esc_html_e('New menu', 'user-manager'); ?></h3>
				<div class="um-form-field">
					<label><?php esc_html_e('Title of shortcuts menu item', 'user-manager'); ?></label>
					<input type="text" name="admin_bar_menu_item[__INDEX__][title]" class="large-text" value="" placeholder="<?php esc_attr_e('e.g. Admin Menu', 'user-manager'); ?>" />
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('Icon override', 'user-manager'); ?></label>
					<input type="text" name="admin_bar_menu_item[__INDEX__][icon]" class="regular-text" value="" placeholder="dashicons-admin-links" />
					<p class="description"><?php esc_html_e('Dashicons class (e.g. dashicons-admin-links). See', 'user-manager'); ?> <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener">developer.wordpress.org/resource/dashicons</a></p>
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('Shortcuts', 'user-manager'); ?></label>
					<textarea name="admin_bar_menu_item[__INDEX__][shortcuts]" rows="8" class="large-text" style="width:100%;" placeholder="Coupon Manager|https://example.com/wp-admin/edit.php?post_type=shop_coupon"></textarea>
					<p class="description"><?php esc_html_e('One per line: Link Title|URL. Use "Group Title|divider" for a section header. All link titles must be unique.', 'user-manager'); ?></p>
				</div>
				<button type="button" class="button um-remove-admin-bar-menu"><?php esc_html_e('Remove this menu', 'user-manager'); ?></button>
			</div>
		</script>
		<script>
		jQuery(document).ready(function($) {
			function isAddonCardActive($card) {
				var selectorsRaw = ($card.attr('data-um-active-selectors') || '').trim();
				if (selectorsRaw !== '') {
					var selectors = selectorsRaw.split(',');
					for (var i = 0; i < selectors.length; i++) {
						var selector = $.trim(selectors[i]);
						if (!selector) {
							continue;
						}
						var $inputs = $(selector);
						if ($inputs.length && $inputs.filter(':checked').length > 0) {
							return true;
						}
					}
					return false;
				}

				var cardId = $card.attr('id') || '';
				if (cardId === 'um-addon-card-custom-notifications') {
					var hasNotification = false;
					$('#um-custom-admin-notifications-list .um-admin-notification-block').each(function() {
						var $block = $(this);
						var title = $.trim($block.find('input[name*="[title]"]').val() || '');
						var body = $.trim($block.find('textarea[name*="[body]"]').val() || '');
						if (title !== '' || body !== '') {
							hasNotification = true;
							return false;
						}
					});
					return hasNotification;
				}
				if (cardId === 'um-addon-card-admin-bar-menu') {
					var hasMenu = false;
					$('#um-admin-bar-menu-list .um-admin-bar-menu-block').each(function() {
						var $block = $(this);
						var title = $.trim($block.find('input[name*="[title]"]').val() || '');
						var shortcuts = $.trim($block.find('textarea[name*="[shortcuts]"]').val() || '');
						if (title !== '' || shortcuts !== '') {
							hasMenu = true;
							return false;
						}
					});
					return hasMenu;
				}
				if (cardId === 'um-addon-card-admin-css') {
					var allCss = $.trim($('#um-wp-admin-css-all').val() || '');
					var usersCss = $.trim($('#um-wp-admin-css-users-css').val() || '');
					return allCss !== '' || usersCss !== '';
				}
				return false;
			}

			function setAddonCardCollapsed($card, collapsed, skipAnimation) {
				var $body = $card.children('.um-admin-card-body').first();
				if (!$body.length) {
					return;
				}

				var $indicator = $card.children('.um-admin-card-header').find('.um-addon-collapse-indicator');
				if (collapsed) {
					$card.addClass('um-addon-collapsed');
					if (skipAnimation) {
						$body.hide();
					} else {
						$body.stop(true, true).slideUp(150);
					}
					$indicator.text('+');
				} else {
					$card.removeClass('um-addon-collapsed');
					if (skipAnimation) {
						$body.show();
					} else {
						$body.stop(true, true).slideDown(150);
					}
					$indicator.text('−');
				}
			}

			function refreshAddonCardAutoState($card, skipAnimation) {
				var isActive = isAddonCardActive($card);
				setAddonCardCollapsed($card, !isActive, skipAnimation);
			}

			function initAddonCollapsibleCards() {
				$('.um-addon-collapsible').each(function() {
					var $card = $(this);
					var $header = $card.children('.um-admin-card-header').first();
					if (!$header.length) {
						return;
					}

					if (!$header.find('.um-addon-collapse-indicator').length) {
						$header.append('<span class="um-addon-collapse-indicator" style="margin-left:auto;font-weight:700;font-size:18px;line-height:1;">−</span>');
					}
					$header.css('cursor', 'pointer');
					$header.on('click', function(e) {
						if ($(e.target).closest('a,button,input,select,textarea,label').length) {
							return;
						}
						setAddonCardCollapsed($card, !$card.hasClass('um-addon-collapsed'));
					});

					refreshAddonCardAutoState($card, true);
				});
			}

			initAddonCollapsibleCards();

			function umToggleBulkMetaFieldRow() {
				var type = $('#um-bulk-identifier-type').val();
				if (type === 'meta_field') {
					$('#um-bulk-meta-field-name-row').show();
				} else {
					$('#um-bulk-meta-field-name-row').hide();
				}
			}
			umToggleBulkMetaFieldRow();
			$('#um-bulk-identifier-type').on('change', umToggleBulkMetaFieldRow);

			function toggleCheckoutShipToFields() {
				if ($('#um-checkout-ship-to-predefined').is(':checked')) {
					$('#um-checkout-ship-to-predefined-fields').show();
				} else {
					$('#um-checkout-ship-to-predefined-fields').hide();
				}
			}
			$('#um-checkout-ship-to-predefined').on('change', toggleCheckoutShipToFields);
			toggleCheckoutShipToFields();

			function toggleMyAccountAdminViewerField(checkboxSelector, fieldSelector) {
				if ($(checkboxSelector).is(':checked')) {
					$(fieldSelector).show();
				} else {
					$(fieldSelector).hide();
				}
			}

			function toggleMyAccountAdminViewerFields() {
				toggleMyAccountAdminViewerField('#um-my-account-admin-order-viewer-enabled', '#um-my-account-admin-order-viewer-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-order-viewer-enabled', '#um-my-account-admin-order-approver-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-order-viewer-enabled', '#um-my-account-admin-order-default-pending-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-order-viewer-enabled', '#um-my-account-admin-order-meta-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-product-viewer-enabled', '#um-my-account-admin-product-viewer-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-product-viewer-enabled', '#um-my-account-admin-product-meta-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-coupon-viewer-enabled', '#um-my-account-admin-coupon-viewer-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-coupon-viewer-enabled', '#um-my-account-admin-coupon-meta-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-user-viewer-enabled', '#um-my-account-admin-user-viewer-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-user-viewer-enabled', '#um-my-account-admin-user-meta-field');
			}

			$('#um-my-account-admin-order-viewer-enabled, #um-my-account-admin-product-viewer-enabled, #um-my-account-admin-coupon-viewer-enabled, #um-my-account-admin-user-viewer-enabled').on('change', toggleMyAccountAdminViewerFields);
			toggleMyAccountAdminViewerFields();
			$('#um-my-account-admin-order-viewer-enabled, #um-my-account-admin-product-viewer-enabled, #um-my-account-admin-coupon-viewer-enabled, #um-my-account-admin-user-viewer-enabled').on('change', function() {
				refreshAddonCardAutoState($('#um-addon-card-my-account'));
			});
			$("input[name='bulk_add_to_cart_enabled']").on('change', function() {
				refreshAddonCardAutoState($('#um-addon-card-bulk-add-to-cart'));
			});
			$('#um-checkout-ship-to-predefined').on('change', function() {
				refreshAddonCardAutoState($('#um-addon-card-checkout-predefined'));
			});
			$('#um-openai-page-meta-box').on('change', function() {
				refreshAddonCardAutoState($('#um-addon-card-api'));
			});

			$('#um-add-admin-notification').on('click', function() {
				var count = $('#um-custom-admin-notifications-list .um-admin-notification-block').length;
				var tpl = $('#um-admin-notification-template').html().replace(/__INDEX__/g, count);
				$('#um-custom-admin-notifications-list').append(tpl);
				$('#um-custom-admin-notifications-list .um-admin-notification-block').last().find('.um-admin-notification-number').text('<?php echo esc_js(__('Notification', 'user-manager')); ?> ' + (count + 1));
				refreshAddonCardAutoState($('#um-addon-card-custom-notifications'));
			});
			$('#um-custom-admin-notifications-list').on('click', '.um-remove-admin-notification', function() {
				$(this).closest('.um-admin-notification-block').remove();
				$('#um-custom-admin-notifications-list .um-admin-notification-block').each(function(i) {
					var $block = $(this);
					var idx = i;
					$block.find('.um-admin-notification-number').text('<?php echo esc_js(__('Notification', 'user-manager')); ?> ' + (idx + 1));
					$block.find('input, textarea').each(function() {
						var name = $(this).attr('name');
						if (name && name.indexOf('custom_admin_notification[') === 0) {
							$(this).attr('name', name.replace(/custom_admin_notification\[\d+\]/, 'custom_admin_notification[' + idx + ']'));
						}
					});
				});
				refreshAddonCardAutoState($('#um-addon-card-custom-notifications'));
			});

			$('#um-add-admin-bar-menu').on('click', function() {
				var count = $('#um-admin-bar-menu-list .um-admin-bar-menu-block').length;
				var tpl = $('#um-admin-bar-menu-template').html().replace(/__INDEX__/g, count);
				$('#um-admin-bar-menu-list').append(tpl);
				$('#um-admin-bar-menu-list .um-admin-bar-menu-block').last().find('.um-admin-bar-menu-number').text('<?php echo esc_js(__('Menu', 'user-manager')); ?> ' + (count + 1));
				refreshAddonCardAutoState($('#um-addon-card-admin-bar-menu'));
			});
			$('#um-admin-bar-menu-list').on('click', '.um-remove-admin-bar-menu', function() {
				$(this).closest('.um-admin-bar-menu-block').remove();
				$('#um-admin-bar-menu-list .um-admin-bar-menu-block').each(function(i) {
					var $block = $(this);
					var idx = i;
					$block.find('.um-admin-bar-menu-number').text('<?php echo esc_js(__('Menu', 'user-manager')); ?> ' + (idx + 1));
					$block.find('input, textarea').each(function() {
						var name = $(this).attr('name');
						if (name && name.indexOf('admin_bar_menu_item[') === 0) {
							$(this).attr('name', name.replace(/admin_bar_menu_item\[\d+\]/, 'admin_bar_menu_item[' + idx + ']'));
						}
					});
				});
				refreshAddonCardAutoState($('#um-addon-card-admin-bar-menu'));
			});
			$('#um-custom-admin-notifications-list').on('input change', 'input, textarea', function() {
				refreshAddonCardAutoState($('#um-addon-card-custom-notifications'));
			});
			$('#um-admin-bar-menu-list').on('input change', 'input, textarea', function() {
				refreshAddonCardAutoState($('#um-addon-card-admin-bar-menu'));
			});
			$('#um-wp-admin-css-all, #um-wp-admin-css-users-css').on('input change', function() {
				refreshAddonCardAutoState($('#um-addon-card-admin-css'));
			});
		});
		</script>
		<?php
	}
}

