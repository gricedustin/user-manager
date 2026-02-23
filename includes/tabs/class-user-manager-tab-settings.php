<?php
/**
 * Settings tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

	class User_Manager_Tab_Settings {

	public static function render(): void {
		$settings      = User_Manager_Core::get_settings();
		$bulk_settings = get_option('bulk_add_to_cart_settings', []);
		?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="user_manager_save_settings" />
			<input type="hidden" name="settings_section" value="general" />
			<?php wp_nonce_field('user_manager_save_settings'); ?>
			<div class="um-admin-grid um-admin-grid-2col">
				<!-- User & Login -->
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-admin-users"></span>
						<h2><?php esc_html_e('User & Login', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label for="um-default-role"><?php esc_html_e('Default User Role', 'user-manager'); ?></label>
							<select name="default_role" id="um-default-role" class="regular-text">
								<?php foreach (User_Manager_Core::get_user_roles() as $key => $name) : ?>
									<option value="<?php echo esc_attr($key); ?>" <?php selected($settings['default_role'] ?? 'customer', $key); ?>><?php echo esc_html($name); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="um-form-field">
							<label for="um-default-login-url"><?php esc_html_e('Default Login URL', 'user-manager'); ?></label>
							<select name="default_login_url" id="um-default-login-url" class="regular-text">
								<option value="/my-account/" <?php selected($settings['default_login_url'] ?? '/my-account/', '/my-account/'); ?>><?php esc_html_e('My Account', 'user-manager'); ?></option>
								<option value="/wp-admin/" <?php selected($settings['default_login_url'] ?? '', '/wp-admin/'); ?>><?php esc_html_e('WP Admin', 'user-manager'); ?></option>
								<option value="/wp-login.php?saml_sso=false" <?php selected($settings['default_login_url'] ?? '', '/wp-login.php?saml_sso=false'); ?>><?php esc_html_e('WP Admin SSO Bypass', 'user-manager'); ?></option>
							</select>
						</div>
						<div class="um-form-field">
							<label for="um-paste-default-columns"><?php esc_html_e('Paste from Spreadsheet: default column order', 'user-manager'); ?></label>
							<input type="text" name="paste_default_columns" id="um-paste-default-columns" class="large-text code" value="<?php echo esc_attr($settings['paste_default_columns'] ?? 'email,first_name,last_name,role,username,password'); ?>" placeholder="email,first_name,last_name,role,username,password" />
							<p class="description"><?php esc_html_e('When pasted data has no header row, columns are interpreted in this order (comma-separated). The first column should be email. You can add or remove columns (e.g. coupon_email_append). Used by the Paste from Spreadsheet tool on the Bulk Create tab.', 'user-manager'); ?></p>
						</div>
					</div>
				</div>

				<!-- Email Settings -->
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-email"></span>
						<h2><?php esc_html_e('Email Settings', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label for="um-send-from-name"><?php esc_html_e('Send From Name', 'user-manager'); ?></label>
							<input type="text" name="send_from_name" id="um-send-from-name" class="regular-text" value="<?php echo esc_attr($settings['send_from_name'] ?? ''); ?>" />
							<p class="description"><?php esc_html_e('The name that appears in the "From" field of emails sent by User Manager. Leave empty to use WordPress default.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-send-from-email"><?php esc_html_e('Send From Email Address', 'user-manager'); ?></label>
							<input type="email" name="send_from_email" id="um-send-from-email" class="regular-text" value="<?php echo esc_attr($settings['send_from_email'] ?? ''); ?>" />
							<p class="description"><?php esc_html_e('The email address that appears in the "From" field of emails sent by User Manager. Leave empty to use WordPress default.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-reply-to-email"><?php esc_html_e('Reply To Email Address', 'user-manager'); ?></label>
							<input type="email" name="reply_to_email" id="um-reply-to-email" class="regular-text" value="<?php echo esc_attr($settings['reply_to_email'] ?? ''); ?>" />
							<p class="description"><?php esc_html_e('The email address that appears in the "Reply-To" field of emails sent by User Manager. Leave empty to use the From address.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="throttle_emails_enabled" id="um-throttle-emails-enabled" value="1" <?php checked($settings['throttle_emails_enabled'] ?? false); ?> />
								<?php esc_html_e('Throttle Sending Emails to X Emails Per Page Load', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When enabled, only the specified number of emails will be sent per page load to avoid triggering spam filters. Remaining emails will be queued for the next batch.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field" id="um-throttle-emails-count-field" style="<?php echo empty($settings['throttle_emails_enabled']) ? 'display:none;' : ''; ?>">
							<label for="um-throttle-emails-count"><?php esc_html_e('Emails Per Batch', 'user-manager'); ?></label>
							<input type="number" name="throttle_emails_count" id="um-throttle-emails-count" class="small-text" min="1" value="<?php echo esc_attr($settings['throttle_emails_count'] ?? 50); ?>" />
							<p class="description"><?php esc_html_e('Number of emails to send per batch. After sending a batch, a button will appear to send the next batch.', 'user-manager'); ?></p>
						</div>
					</div>
				</div>

				<!-- Activity & Logging -->
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-list-view"></span>
						<h2><?php esc_html_e('Activity & Logging', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="log_activity" value="1" <?php checked($settings['log_activity'] ?? true); ?> />
								<?php esc_html_e('Enable Activity Logging', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Log all user creation and password reset actions.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="enable_view_reports" value="1" <?php checked($settings['enable_view_reports'] ?? true); ?> />
								<?php esc_html_e('Enable View/404/Search Reports', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('Controls logging for Page Views, Product Views, Page Not Found 404 Errors, and Search Queries reports. Disable to stop collecting new entries while keeping existing data.', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="log_activity_debug" value="1" <?php checked($settings['log_activity_debug'] ?? false); ?> />
								<?php esc_html_e('Log activity debug messages to error_log', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Helps troubleshoot why log entries are missing. Disable on production systems.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="log_admin_activity" value="1" <?php checked($settings['log_admin_activity'] ?? true); ?> />
								<?php esc_html_e('Log WP-Admin Activity', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Log wp-admin logins and post type creation/editing activities in the Admin Log tab.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="role_change_alert_enabled" id="um-role-change-alert-enabled" value="1" <?php checked($settings['role_change_alert_enabled'] ?? false); ?> />
								<?php esc_html_e('Admin alert if any user with these roles changes to another role', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When enabled, an email is sent to the address below whenever a user whose previous role (from User Activity / Login History) was one of the selected roles has a different role after login (e.g. after SSO updates their role).', 'user-manager'); ?></p>
						</div>
						<div id="um-role-change-alert-fields" style="<?php echo empty($settings['role_change_alert_enabled']) ? 'display:none;' : ''; ?>">
							<div class="um-form-field">
								<label class="um-label-block"><?php esc_html_e('Monitor these roles (alert when a user with one of these roles changes to any other role)', 'user-manager'); ?></label>
								<div class="um-checkbox-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #c3c4c7; padding: 10px 12px; background: #fff; border-radius: 4px;">
									<?php
									$alert_roles = isset($settings['role_change_alert_roles']) && is_array($settings['role_change_alert_roles']) ? $settings['role_change_alert_roles'] : [];
									foreach (User_Manager_Core::get_user_roles() as $role_key => $role_name) :
										?>
										<label style="display: block; margin-bottom: 6px;">
											<input type="checkbox" name="role_change_alert_roles[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $alert_roles, true)); ?> />
											<?php echo esc_html($role_name); ?> (<code><?php echo esc_html($role_key); ?></code>)
										</label>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="um-form-field">
								<label for="um-role-change-alert-email"><?php esc_html_e('Admin email address for role change alerts', 'user-manager'); ?></label>
								<input type="email" name="role_change_alert_email" id="um-role-change-alert-email" class="regular-text" value="<?php echo esc_attr($settings['role_change_alert_email'] ?? ''); ?>" placeholder="admin@example.com" />
								<p class="description"><?php esc_html_e('Alerts are sent once per role change when the user\'s previous role (from the last User Activity record) was one of the monitored roles.', 'user-manager'); ?></p>
							</div>
						</div>
					</div>
				</div>

				<!-- User Experience -->
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-admin-appearance"></span>
						<h2><?php esc_html_e('User Experience', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="rebrand_reset_password_copy" value="1" <?php checked($settings['rebrand_reset_password_copy'] ?? false); ?> />
								<?php esc_html_e('Rebrand Reset Password Copy to Set Password Copy', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Change the reset password copy to set password intended for new users who should set a password when first logging into the site so it doesn\'t look like they are "resetting" for the first time. Also removes the username from the password changed email.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="um_quick_search_enabled" value="1" <?php checked($settings['um_quick_search_enabled'] ?? true); ?> />
								<?php esc_html_e('Display Quick Search Bar', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('Adds a "Search" icon to the WordPress admin bar that opens a quick search dropdown for posts, products, orders, users, product categories, and media.', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_email_converter" value="1" <?php checked($settings['coupon_email_converter'] ?? false); ?> />
								<?php esc_html_e('Enable Coupon Email List Converter meta box', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Adds a meta box on coupon edit screens to convert a one-per-line email list into a comma-separated list for Allowed emails.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_show_email_column" value="1" <?php checked($settings['coupon_show_email_column'] ?? false); ?> />
								<?php esc_html_e('Show Emails Assigned To Column in Coupons Admin Screen', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Adds an "Assigned Emails" column to WooCommerce coupons so you can see restricted email addresses without opening each coupon.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="search_redirect_by_sku" value="1" <?php checked(!isset($settings['search_redirect_by_sku']) || !empty($settings['search_redirect_by_sku'])); ?> />
								<?php esc_html_e('Allow WooCommerce front-end product search to include SKUs', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When a search term (?s=) exactly matches a product or variation SKU, redirect directly to that product page instead of showing search results.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_code_url_param_enabled" value="1" <?php checked($settings['coupon_code_url_param_enabled'] ?? false); ?> />
								<?php esc_html_e('Apply Coupon Code via URL Parameter', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When enabled, visiting any page with the URL parameter below (e.g. ?coupon-code=SAVE10) will apply that coupon to the cart.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-coupon-code-url-param"><?php esc_html_e('URL parameter name', 'user-manager'); ?></label>
							<input type="text" name="coupon_code_url_param_name" id="um-coupon-code-url-param" class="regular-text" value="<?php echo esc_attr($settings['coupon_code_url_param_name'] ?? 'coupon-code'); ?>" placeholder="coupon-code" />
							<p class="description"><?php esc_html_e('Default is coupon-code. Use only letters, numbers, hyphens, and underscores. Example: ?coupon-code=SAVE10', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="display_post_meta_meta_box" value="1" <?php checked($settings['display_post_meta_meta_box'] ?? false); ?> />
								<?php esc_html_e('Display all post meta fields & values in a meta box when editing posts', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Shows a meta box on the edit screen for all post types listing every post meta key and its value(s).', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="allow_edit_post_meta" value="1" <?php checked($settings['allow_edit_post_meta'] ?? false); ?> />
								<?php esc_html_e('Allow editing of post meta values', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When the post meta meta box is enabled, allow changing meta values directly from the meta box. Save the post to apply changes.', 'user-manager'); ?></p>
						</div>
					</div>
				</div>

				<!-- Bulk Add to Cart -->
				<div class="um-admin-card">
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

				<!-- User Creation & Import -->
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-upload"></span>
						<h2><?php esc_html_e('User Creation & Import', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="update_existing_users" value="1" <?php checked($settings['update_existing_users'] ?? false); ?> />
								<?php esc_html_e('When creating a new user, if the user already exists, update all details', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Instead of skipping existing users, update their first name, last name, role, and password.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-sftp-directories"><?php esc_html_e('SFTP/Directory Paths for CSV Import', 'user-manager'); ?></label>
							<textarea name="sftp_directories" id="um-sftp-directories" rows="5" class="large-text code" placeholder="<?php echo esc_attr("/home/username/imports/\n/var/www/html/wp-content/uploads/user-imports/\n" . WP_CONTENT_DIR . "/uploads/user-imports/"); ?>"><?php echo esc_textarea($settings['sftp_directories'] ?? ''); ?></textarea>
							<p class="description"><?php esc_html_e('Enter one directory path per line. These directories will be monitored for CSV files in the Bulk Create tool.', 'user-manager'); ?></p>
							<p class="description"><strong><?php esc_html_e('Example paths:', 'user-manager'); ?></strong></p>
							<ul class="description" style="margin: 5px 0 0 20px; list-style: disc;">
								<li><code><?php echo esc_html(WP_CONTENT_DIR . '/uploads/user-imports/'); ?></code> — <?php esc_html_e('WordPress uploads folder', 'user-manager'); ?></li>
								<li><code>/home/username/imports/</code> — <?php esc_html_e('Custom SFTP directory', 'user-manager'); ?></li>
								<li><code><?php echo esc_html(ABSPATH . 'imports/'); ?></code> — <?php esc_html_e('Site root folder', 'user-manager'); ?></li>
							</ul>
						</div>
					</div>
				</div>

				<!-- Checkout: Ship To Pre-Defined Addresses -->
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-location-alt"></span>
						<h2><?php esc_html_e('Checkout', 'user-manager'); ?></h2>
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
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-megaphone"></span>
						<h2><?php esc_html_e('Custom WP-Admin Notifications', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Add custom admin notices at the top of WP-Admin screens. Each notification can be limited to URLs that contain a specific string (e.g. shop_coupon for coupon edit screens), or shown on all admin screens if URL match is blank.', 'user-manager'); ?></p>
						<?php
						$admin_notifications = isset($settings['custom_admin_notifications']) && is_array($settings['custom_admin_notifications']) ? $settings['custom_admin_notifications'] : [];
						if (empty($admin_notifications)) {
							$admin_notifications = [ ['title' => '', 'body' => '', 'background_color' => '', 'url_string_match' => ''] ];
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

				<!-- Custom WP-Admin Top Bar Menus & Links -->
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-menu-alt"></span>
						<h2><?php esc_html_e('Custom WP-Admin Top Bar Menus & Links', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<?php
						$admin_bar_enabled = !isset($settings['admin_bar_menu_items_enabled']) || !empty($settings['admin_bar_menu_items_enabled']);
						$admin_bar_visibility = isset($settings['admin_bar_menu_visibility']) ? (string) $settings['admin_bar_menu_visibility'] : 'all_toolbar_users';
						if (!in_array($admin_bar_visibility, ['all_toolbar_users', 'manage_options_only'], true)) {
							$admin_bar_visibility = 'all_toolbar_users';
						}
						$admin_bar_parent = isset($settings['admin_bar_menu_parent']) ? (string) $settings['admin_bar_menu_parent'] : 'top-secondary';
						if (!in_array($admin_bar_parent, ['root-default', 'top-secondary'], true)) {
							$admin_bar_parent = 'top-secondary';
						}
						$admin_bar_force_first_left = !empty($settings['admin_bar_menu_force_first_left']);
						$default_shortcuts_example = "Coupon Manager|admin:edit.php?post_type=shop_coupon\nOrder Manager|admin:edit.php?post_type=shop_order\nOrder Exporter|admin:admin.php?page=wc-order-export";
						$admin_bar_items = isset($settings['admin_bar_menu_items']) && is_array($settings['admin_bar_menu_items']) ? $settings['admin_bar_menu_items'] : [];
						if (empty($admin_bar_items)) {
							$admin_bar_items = [[
								'title' => 'Admin Menu',
								'icon' => 'dashicons-editor-ul',
								'shortcuts' => $default_shortcuts_example,
							]];
						}
						?>
						<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Create custom top bar dropdown menus (front-end and wp-admin). Define one menu title, then add links line by line. For URLs, you can use full links or admin paths for this site (example: admin:edit.php?post_type=shop_coupon).', 'user-manager'); ?></p>
						<p class="description" style="margin-top:-8px;margin-bottom:16px;"><?php esc_html_e('When you save, absolute links that point to /wp-admin/ are automatically normalized to admin: paths (domain removed).', 'user-manager'); ?></p>

						<div class="um-form-field">
							<label>
								<input type="checkbox" name="admin_bar_menu_items_enabled" id="um-admin-bar-menu-items-enabled" value="1" <?php checked($admin_bar_enabled); ?> />
								<?php esc_html_e('Enable custom top bar menus', 'user-manager'); ?>
							</label>
						</div>
						<div class="um-form-field">
							<label for="um-admin-bar-menu-visibility"><?php esc_html_e('Who should see these menus?', 'user-manager'); ?></label>
							<select name="admin_bar_menu_visibility" id="um-admin-bar-menu-visibility" class="regular-text">
								<option value="all_toolbar_users" <?php selected($admin_bar_visibility, 'all_toolbar_users'); ?>><?php esc_html_e('Anyone who can see the top admin bar', 'user-manager'); ?></option>
								<option value="manage_options_only" <?php selected($admin_bar_visibility, 'manage_options_only'); ?>><?php esc_html_e('Administrators only (manage_options)', 'user-manager'); ?></option>
							</select>
						</div>
						<div class="um-form-field">
							<label for="um-admin-bar-menu-parent"><?php esc_html_e('Menu location in top bar', 'user-manager'); ?></label>
							<select name="admin_bar_menu_parent" id="um-admin-bar-menu-parent" class="regular-text">
								<option value="root-default" <?php selected($admin_bar_parent, 'root-default'); ?>><?php esc_html_e('Left side', 'user-manager'); ?></option>
								<option value="top-secondary" <?php selected($admin_bar_parent, 'top-secondary'); ?>><?php esc_html_e('Right side', 'user-manager'); ?></option>
							</select>
						</div>
						<div class="um-form-field" id="um-admin-bar-force-first-left-wrap" style="<?php echo $admin_bar_parent === 'root-default' ? '' : 'display:none;'; ?>">
							<label>
								<input type="checkbox" name="admin_bar_menu_force_first_left" id="um-admin-bar-force-first-left" value="1" <?php checked($admin_bar_force_first_left); ?> />
								<?php esc_html_e('When on the left side, force custom menu(s) to appear first', 'user-manager'); ?>
							</label>
						</div>

						<div id="um-admin-bar-menu-items-wrapper" style="<?php echo $admin_bar_enabled ? '' : 'display:none;'; ?>">
						<div id="um-admin-bar-menu-list">
							<?php foreach ($admin_bar_items as $idx => $item) : ?>
								<?php $item_shortcuts = User_Manager_Core::normalize_admin_bar_shortcuts_for_storage((string) ($item['shortcuts'] ?? '')); ?>
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
										<textarea name="admin_bar_menu_item[<?php echo (int) $idx; ?>][shortcuts]" rows="8" class="large-text" style="width:100%;" placeholder="<?php echo esc_attr($default_shortcuts_example); ?>"><?php echo esc_textarea($item_shortcuts); ?></textarea>
										<p class="description"><?php esc_html_e('One per line: Link Title|URL. Use "Group Title|divider" for a section header. Example links: Coupon Manager|admin:edit.php?post_type=shop_coupon', 'user-manager'); ?></p>
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
				</div>

				<!-- WP-Admin CSS -->
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-art"></span>
						<h2><?php esc_html_e('WP-Admin CSS', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Apply custom CSS only in the WordPress admin (wp-admin). You can target all roles, exclude specific roles from the global CSS, apply CSS to specific users (by login, email, or ID), and/or add per-role CSS.', 'user-manager'); ?></p>

						<div id="um-wp-admin-css-list">
							<!-- All Roles CSS -->
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

							<!-- User-based CSS -->
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
							<!-- Role: <?php echo esc_attr($role_key); ?> -->
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
				<div class="um-admin-card">
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
							<label><input type="checkbox" name="openai_page_meta_box" value="1" <?php checked(!empty($settings['openai_page_meta_box'])); ?> /> <?php esc_html_e('Enable Page & Post Meta Box for inserting ChatGPT generated content into pages & posts', 'user-manager'); ?></label>
							<p class="description"><?php esc_html_e('When enabled, page edit screens show a meta box to generate content via ChatGPT and insert it at the bottom of the page (block format).', 'user-manager'); ?></p>
						</div>
					</div>
				</div>

				<!-- Save button -->
				<div class="um-admin-card um-admin-card-full">
					<div class="um-admin-card-body">
						<p style="margin:0;">
							<?php submit_button(__('Save Settings', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</div>
				</div>
			</div>
		</form>
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
					<textarea name="admin_bar_menu_item[__INDEX__][shortcuts]" rows="8" class="large-text" style="width:100%;" placeholder="Coupon Manager|admin:edit.php?post_type=shop_coupon
Order Manager|admin:edit.php?post_type=shop_order
Order Exporter|admin:admin.php?page=wc-order-export"></textarea>
					<p class="description"><?php esc_html_e('One per line: Link Title|URL. Use "Group Title|divider" for a section header. Example links: Coupon Manager|admin:edit.php?post_type=shop_coupon', 'user-manager'); ?></p>
				</div>
				<button type="button" class="button um-remove-admin-bar-menu"><?php esc_html_e('Remove this menu', 'user-manager'); ?></button>
			</div>
		</script>
		<script>
		jQuery(document).ready(function($) {
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

			// Toggle throttle emails count field
			function toggleThrottleEmailsCount() {
				if ($('#um-throttle-emails-enabled').is(':checked')) {
					$('#um-throttle-emails-count-field').show();
				} else {
					$('#um-throttle-emails-count-field').hide();
				}
			}
			$('#um-throttle-emails-enabled').on('change', toggleThrottleEmailsCount);
			toggleThrottleEmailsCount();

			function toggleCheckoutShipToFields() {
				if ($('#um-checkout-ship-to-predefined').is(':checked')) {
					$('#um-checkout-ship-to-predefined-fields').show();
				} else {
					$('#um-checkout-ship-to-predefined-fields').hide();
				}
			}
			$('#um-checkout-ship-to-predefined').on('change', toggleCheckoutShipToFields);
			toggleCheckoutShipToFields();

			function toggleRoleChangeAlertFields() {
				if ($('#um-role-change-alert-enabled').is(':checked')) {
					$('#um-role-change-alert-fields').show();
				} else {
					$('#um-role-change-alert-fields').hide();
				}
			}
			$('#um-role-change-alert-enabled').on('change', toggleRoleChangeAlertFields);
			toggleRoleChangeAlertFields();

			function toggleAdminBarMenuItemsEnabled() {
				if ($('#um-admin-bar-menu-items-enabled').is(':checked')) {
					$('#um-admin-bar-menu-items-wrapper').show();
				} else {
					$('#um-admin-bar-menu-items-wrapper').hide();
				}
			}
			$('#um-admin-bar-menu-items-enabled').on('change', toggleAdminBarMenuItemsEnabled);
			toggleAdminBarMenuItemsEnabled();

			function toggleAdminBarForceFirstLeftField() {
				if ($('#um-admin-bar-menu-parent').val() === 'root-default') {
					$('#um-admin-bar-force-first-left-wrap').show();
				} else {
					$('#um-admin-bar-force-first-left-wrap').hide();
				}
			}
			$('#um-admin-bar-menu-parent').on('change', toggleAdminBarForceFirstLeftField);
			toggleAdminBarForceFirstLeftField();

			$('#um-add-admin-notification').on('click', function() {
				var count = $('#um-custom-admin-notifications-list .um-admin-notification-block').length;
				var tpl = $('#um-admin-notification-template').html().replace(/__INDEX__/g, count);
				$('#um-custom-admin-notifications-list').append(tpl);
				$('#um-custom-admin-notifications-list .um-admin-notification-block').last().find('.um-admin-notification-number').text('<?php echo esc_js(__('Notification', 'user-manager')); ?> ' + (count + 1));
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
			});

			$('#um-add-admin-bar-menu').on('click', function() {
				var count = $('#um-admin-bar-menu-list .um-admin-bar-menu-block').length;
				var tpl = $('#um-admin-bar-menu-template').html().replace(/__INDEX__/g, count);
				$('#um-admin-bar-menu-list').append(tpl);
				$('#um-admin-bar-menu-list .um-admin-bar-menu-block').last().find('.um-admin-bar-menu-number').text('<?php echo esc_js(__('Menu', 'user-manager')); ?> ' + (count + 1));
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
			});
		});
		</script>
		<?php
	}
}
