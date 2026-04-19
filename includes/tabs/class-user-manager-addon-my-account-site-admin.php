<?php
/**
 * Add-on card: My Account Site Admin.
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/addons/trait-my-account-admin-meta-fields-repeater.php';

class User_Manager_Addon_My_Account_Site_Admin {

	use User_Manager_Addon_My_Account_Admin_Meta_Fields_Repeater_Trait;

	public static function render(array $settings, string $settings_form_id = ''): void {
		$available_roles = self::get_available_roles();
		$available_activity_actions = self::get_available_activity_actions();
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$order_status_note = self::get_order_statuses_note();
		$order_statuses = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [];
		$order_statuses = is_array($order_statuses) ? $order_statuses : [];
		$order_status_title_overrides = self::normalize_order_status_title_overrides($settings['my_account_admin_order_status_titles'] ?? []);
		$selected_activity_actions = self::normalize_activity_actions($settings['my_account_admin_activity_viewer_actions'] ?? []);
		$is_enabled = array_key_exists('my_account_site_admin_enabled', $settings)
			? !empty($settings['my_account_site_admin_enabled'])
			: (
				!empty($settings['my_account_admin_order_viewer_enabled'])
				|| !empty($settings['my_account_admin_product_viewer_enabled'])
				|| !empty($settings['my_account_admin_coupon_viewer_enabled'])
				|| !empty($settings['my_account_admin_user_viewer_enabled'])
				|| !empty($settings['my_account_admin_activity_viewer_enabled'])
			);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-my-account" data-um-active-selectors="#um-my-account-site-admin-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-admin-site"></span>
				<h2><?php esc_html_e('My Account Admin', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="my_account_site_admin_enabled" id="um-my-account-site-admin-enabled" value="1" <?php checked($is_enabled); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Add admin-style Orders, Products, Coupons, and Users tools inside WooCommerce My Account.', 'user-manager'); ?></p>
				</div>
				<div id="um-my-account-site-admin-fields" style="<?php echo $is_enabled ? '' : 'display:none;'; ?>">
				<div class="um-form-field">
					<label for="um-my-account-admin-items-per-page"><?php esc_html_e('Rows per page in My Account Admin lists', 'user-manager'); ?></label>
					<input type="number" name="my_account_admin_items_per_page" id="um-my-account-admin-items-per-page" class="small-text" min="1" max="200" value="<?php echo esc_attr(isset($settings['my_account_admin_items_per_page']) ? (int) $settings['my_account_admin_items_per_page'] : 20); ?>" />
					<p class="description"><?php esc_html_e('Controls pagination size for My Account Admin Orders, Products, Coupons, Users, and Activity tables.', 'user-manager'); ?></p>
				</div>
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="my_account_admin_enable_csv_export_button" id="um-my-account-admin-enable-csv-export-button" value="1" <?php checked($settings['my_account_admin_enable_csv_export_button'] ?? false); ?> />
						<?php esc_html_e('Add Download to CSV Button below Paging Buttons', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Adds an Export to CSV button to My Account Admin Orders, Products, Coupons, Users, and Activity list screens. Downloads all rows across all pages for the active filters/search.', 'user-manager'); ?></p>
				</div>
				<div class="um-form-field">
					<label for="um-my-account-admin-wp-admin-redirect-list"><?php esc_html_e('WP Administrators to Redirect to My Account if Accessing WP-Admin and Remove WP-Admin Top Bar on Front End?', 'user-manager'); ?></label>
					<input type="text" name="my_account_admin_wp_admin_redirect_list" id="um-my-account-admin-wp-admin-redirect-list" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_wp_admin_redirect_list'] ?? ($settings['my_account_admin_activity_viewer_wp_admin_redirect_list'] ?? '')); ?>" placeholder="adminuser, admin@example.com, 123" />
					<p class="description"><?php esc_html_e('Comma-separated list of WP Administrator usernames, emails, or user IDs. Matching users are redirected away from wp-admin to My Account and the WP-Admin top bar is hidden for them on the front end. Leaves roles unchanged. If empty, no users are affected.', 'user-manager'); ?></p>
				</div>
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="my_account_admin_order_viewer_enabled" id="um-my-account-admin-order-viewer-enabled" value="1" <?php checked($settings['my_account_admin_order_viewer_enabled'] ?? false); ?> />
						<?php esc_html_e('My Account Admin Order Viewer', 'user-manager'); ?>
					</label>
				</div>
				<div class="um-my-account-order-viewer-sub-settings" style="margin-left: 24px; padding-left: 16px; border-left: 2px solid #dcdcde;">
					<div class="um-form-field" id="um-my-account-admin-order-viewer-users-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-order-viewer-usernames"><?php esc_html_e('Allowed usernames (comma-separated)', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_order_viewer_usernames" id="um-my-account-admin-order-viewer-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_order_viewer_usernames'] ?? ''); ?>" placeholder="username1, username2" />
						<p class="description"><?php esc_html_e('Usernames allowed to view the Admin: Orders My Account area.', 'user-manager'); ?></p>
						<?php self::render_role_checkboxes('my_account_admin_order_viewer_roles', $settings['my_account_admin_order_viewer_roles'] ?? [], $available_roles, __('Allowed roles for Admin: Orders', 'user-manager')); ?>
						<label for="um-my-account-admin-order-status-filters"><?php esc_html_e('Order Status Filters (Comma, Separated)', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_order_status_filters" id="um-my-account-admin-order-status-filters" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_order_status_filters'] ?? ''); ?>" placeholder="wc_completed:Complete,wc_failed:Failed" />
						<p class="description"><?php esc_html_e('Use wc_status keys, separated by commas. If a colon is included, the value after the colon is used as the filter title/label.', 'user-manager'); ?></p>
						<p class="description"><?php echo esc_html($order_status_note); ?></p>
						<?php if (!empty($order_statuses)) : ?>
							<div style="margin-top:12px;">
								<label class="um-label-block"><strong><?php esc_html_e('Order Status Title Overrides (Front End)', 'user-manager'); ?></strong></label>
								<p class="description" style="margin-top:0;"><?php esc_html_e('Optional. Override how each WooCommerce order status label appears in My Account Admin Orders.', 'user-manager'); ?></p>
								<div class="um-checkbox-grid">
									<?php foreach ($order_statuses as $status_key => $status_label) : ?>
										<?php
										$normalized_key = self::normalize_order_status_key((string) $status_key);
										if ($normalized_key === '') {
											continue;
										}
										$override_value = isset($order_status_title_overrides[$normalized_key]) ? $order_status_title_overrides[$normalized_key] : '';
										?>
										<div class="um-form-field">
											<label for="um-my-account-admin-order-status-title-<?php echo esc_attr($normalized_key); ?>"><?php echo esc_html(wp_strip_all_tags((string) $status_label)); ?> <code><?php echo esc_html($normalized_key); ?></code></label>
											<input type="text" name="my_account_admin_order_status_titles[<?php echo esc_attr($normalized_key); ?>]" id="um-my-account-admin-order-status-title-<?php echo esc_attr($normalized_key); ?>" class="regular-text" value="<?php echo esc_attr($override_value); ?>" placeholder="<?php echo esc_attr(wp_strip_all_tags((string) $status_label)); ?>" />
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<label>
							<input type="checkbox" name="my_account_admin_order_hide_status" id="um-my-account-admin-order-hide-status" value="1" <?php checked($settings['my_account_admin_order_hide_status'] ?? false); ?> />
							<?php esc_html_e('Hide Order Status', 'user-manager'); ?>
						</label>
						<label style="display:block; margin-top:8px;">
							<input type="checkbox" name="my_account_admin_order_add_webtoffee_download_invoice_button" id="um-my-account-admin-order-add-webtoffee-download-invoice-button" value="1" <?php checked($settings['my_account_admin_order_add_webtoffee_download_invoice_button'] ?? false); ?> />
							<?php esc_html_e('Add WebToffee WooCommerce PDF Invoices Download Invoice Button', 'user-manager'); ?>
						</label>
						<label style="display:block; margin-top:6px;">
							<input type="checkbox" name="my_account_admin_order_add_webtoffee_print_invoice_button" id="um-my-account-admin-order-add-webtoffee-print-invoice-button" value="1" <?php checked($settings['my_account_admin_order_add_webtoffee_print_invoice_button'] ?? false); ?> />
							<?php esc_html_e('Add WebToffee WooCommerce PDF Invoices Print Invoice Button', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field" id="um-my-account-admin-order-approver-users-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-order-approval-usernames"><?php esc_html_e('Order approval allowed usernames (comma-separated)', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_order_approval_usernames" id="um-my-account-admin-order-approval-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_order_approval_usernames'] ?? ''); ?>" placeholder="approver1, approver2" />
						<p class="description"><?php esc_html_e('These users can see action buttons for any order that is not Completed. Approve is hidden when an order is already Processing, and Decline is hidden when an order is already Canceled.', 'user-manager'); ?></p>
						<?php self::render_role_checkboxes('my_account_admin_order_approval_roles', $settings['my_account_admin_order_approval_roles'] ?? [], $available_roles, __('Order approval allowed roles', 'user-manager')); ?>
						<label for="um-my-account-admin-order-approve-button-label"><?php esc_html_e('Approve Button Label', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_order_approve_button_label" id="um-my-account-admin-order-approve-button-label" class="regular-text" value="<?php echo esc_attr($settings['my_account_admin_order_approve_button_label'] ?? 'Move to Processing'); ?>" />
						<label for="um-my-account-admin-order-approve-button-background-color" style="margin-top:8px;"><?php esc_html_e('Approve Button Background Color', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_order_approve_button_background_color" id="um-my-account-admin-order-approve-button-background-color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($settings['my_account_admin_order_approve_button_background_color'] ?? ''); ?>" data-default-color="#2271b1" placeholder="#2271b1" />
						<label for="um-my-account-admin-order-decline-button-label" style="margin-top:8px;"><?php esc_html_e('Decline Button Label', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_order_decline_button_label" id="um-my-account-admin-order-decline-button-label" class="regular-text" value="<?php echo esc_attr($settings['my_account_admin_order_decline_button_label'] ?? 'Move to Canceled'); ?>" />
						<label for="um-my-account-admin-order-decline-button-background-color" style="margin-top:8px;"><?php esc_html_e('Decline Button Background Color', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_order_decline_button_background_color" id="um-my-account-admin-order-decline-button-background-color" class="regular-text um-color-picker-field" value="<?php echo esc_attr($settings['my_account_admin_order_decline_button_background_color'] ?? ''); ?>" data-default-color="#b32d2e" placeholder="#b32d2e" />
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
					<div class="um-form-field" id="um-my-account-admin-order-additional-meta-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label><?php esc_html_e('Additional Meta Fields to Display Under Order', 'user-manager'); ?></label>
						<p class="description"><?php esc_html_e('Add one row per meta field you want rendered under each Order detail view. Each row below becomes a meta_field:Label:prefix_before_value[:flags] entry, joined by commas when saved.', 'user-manager'); ?></p>
						<?php self::render_additional_meta_fields_repeater(
							'my_account_admin_order_additional_meta_fields',
							'um-my-account-admin-order-additional-meta-fields',
							(string) ($settings['my_account_admin_order_additional_meta_fields'] ?? '')
						); ?>
						<p class="description" style="margin-top:8px;"><?php esc_html_e('Example: Meta Field = _volunteer_file, Label = Volunteer File, Prefix = https://example.com/uploads/, flags = Count file lines + Preview in modal + Show row when empty.', 'user-manager'); ?></p>
						<p class="description"><strong><?php esc_html_e('Flexible Checkout Fields PRO File Upload support:', 'user-manager'); ?></strong> <?php esc_html_e('add the fcf_file flag to a row to treat the stored meta value as a Flexible Checkout Fields PRO upload hash. The plugin resolves the file from wp-content/uploads/woocommerce_uploads/flexible-checkout-fields/<hash>/ before linking, previewing, and counting lines.', 'user-manager'); ?></p>
						<p class="description"><?php esc_html_e('Debug: append ?um_text_file_line_count_debug=1 to the Admin: Orders URL to output URL-build/fetch/cache diagnostics for flagged file fields.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field" id="um-my-account-admin-order-additional-meta-list-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label><?php esc_html_e('Additional Meta Fields to Display Under Order in All Orders Screen', 'user-manager'); ?></label>
						<p class="description"><?php esc_html_e('Add one row per meta field you want rendered inline under the action buttons of each row in the Admin: Orders list.', 'user-manager'); ?></p>
						<?php self::render_additional_meta_fields_repeater(
							'my_account_admin_order_list_additional_meta_fields',
							'um-my-account-admin-order-additional-meta-fields-list',
							(string) ($settings['my_account_admin_order_list_additional_meta_fields'] ?? '')
						); ?>
						<p class="description" style="margin-top:8px;"><?php esc_html_e('Example: Meta Field = _volunteer_file, Label = Volunteer File, Prefix = https://example.com/uploads/, flags = Count file lines + Preview in modal + Show row when empty.', 'user-manager'); ?></p>
						<p class="description"><strong><?php esc_html_e('Flexible Checkout Fields PRO File Upload support:', 'user-manager'); ?></strong> <?php esc_html_e('add the fcf_file flag to a row to treat the stored meta value as a Flexible Checkout Fields PRO upload hash. The plugin resolves the file from wp-content/uploads/woocommerce_uploads/flexible-checkout-fields/<hash>/ before linking, previewing, and counting lines.', 'user-manager'); ?></p>
						<p class="description"><?php esc_html_e('Debug: append ?um_text_file_line_count_debug=1 to the Admin: Orders URL to output URL-build/fetch/cache diagnostics for flagged file fields.', 'user-manager'); ?></p>
						<div style="margin-top:8px;">
							<button
								type="submit"
								class="button button-secondary um-addon-action-submit"
								data-um-target-action="user_manager_reset_text_file_line_count_cache"
								<?php echo $form_attr; ?>
								onclick="return window.confirm('<?php echo esc_js(__('Reset all cached line-count values now? The next view will re-fetch and re-count rows for flagged files.', 'user-manager')); ?>');"
							>
								<?php esc_html_e('Reset Cached Line Counts', 'user-manager'); ?>
							</button>
						</div>
					</div>
					<div class="um-form-field" id="um-my-account-admin-order-additional-flag-list-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label><?php esc_html_e('Additional Flag to Display Below Additional Fields in All Orders Screen', 'user-manager'); ?></label>
						<p class="description"><?php esc_html_e('Compare two meta values per row and pick whether the flag should show when the values ARE equal or when they are NOT equal. With a grace value, both values must be numeric. Use the Grace Value Operator to explicitly pick "Only flag when diff EXCEEDS grace (>)" or "Only flag when diff is WITHIN grace (≤)" — or leave it on Auto to keep the legacy behavior where ARE equal pairs with EXCEEDS and NOT equal pairs with WITHIN. Default colors are black background and white text.', 'user-manager'); ?></p>
						<?php self::render_additional_meta_compare_flags_repeater(
							'my_account_admin_order_list_additional_flag_fields',
							'um-my-account-admin-order-additional-flag-fields-list',
							(string) ($settings['my_account_admin_order_list_additional_flag_fields'] ?? '')
						); ?>
						<?php self::render_additional_meta_compare_flags_preview(); ?>
					</div>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="my_account_admin_product_viewer_enabled" id="um-my-account-admin-product-viewer-enabled" value="1" <?php checked($settings['my_account_admin_product_viewer_enabled'] ?? false); ?> />
						<?php esc_html_e('My Account Admin Product Viewer', 'user-manager'); ?>
					</label>
				</div>
				<div class="um-my-account-product-viewer-sub-settings" style="margin-left: 24px; padding-left: 16px; border-left: 2px solid #dcdcde;">
					<div class="um-form-field" id="um-my-account-admin-product-viewer-users-field" style="<?php echo empty($settings['my_account_admin_product_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-product-viewer-usernames"><?php esc_html_e('Allowed usernames (comma-separated)', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_product_viewer_usernames" id="um-my-account-admin-product-viewer-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_product_viewer_usernames'] ?? ''); ?>" placeholder="username1, username2" />
						<?php self::render_role_checkboxes('my_account_admin_product_viewer_roles', $settings['my_account_admin_product_viewer_roles'] ?? [], $available_roles, __('Allowed roles for Admin: Products', 'user-manager')); ?>
					</div>
					<div class="um-form-field" id="um-my-account-admin-product-meta-field" style="<?php echo empty($settings['my_account_admin_product_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label>
							<input type="checkbox" name="my_account_admin_product_viewer_show_meta" id="um-my-account-admin-product-viewer-show-meta" value="1" <?php checked($settings['my_account_admin_product_viewer_show_meta'] ?? false); ?> />
							<?php esc_html_e('Show Meta Data area for Product details', 'user-manager'); ?>
						</label>
					</div>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="my_account_admin_coupon_viewer_enabled" id="um-my-account-admin-coupon-viewer-enabled" value="1" <?php checked($settings['my_account_admin_coupon_viewer_enabled'] ?? false); ?> />
						<?php esc_html_e('My Account Admin Coupon Viewer', 'user-manager'); ?>
					</label>
				</div>
				<div class="um-my-account-coupon-viewer-sub-settings" style="margin-left: 24px; padding-left: 16px; border-left: 2px solid #dcdcde;">
					<div class="um-form-field" id="um-my-account-admin-coupon-viewer-users-field" style="<?php echo empty($settings['my_account_admin_coupon_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-coupon-viewer-usernames"><?php esc_html_e('Allowed usernames (comma-separated)', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_coupon_viewer_usernames" id="um-my-account-admin-coupon-viewer-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_coupon_viewer_usernames'] ?? ''); ?>" placeholder="username1, username2" />
						<?php self::render_role_checkboxes('my_account_admin_coupon_viewer_roles', $settings['my_account_admin_coupon_viewer_roles'] ?? [], $available_roles, __('Allowed roles for Admin: Coupons', 'user-manager')); ?>
					</div>
					<div class="um-form-field" id="um-my-account-admin-coupon-meta-field" style="<?php echo empty($settings['my_account_admin_coupon_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label>
							<input type="checkbox" name="my_account_admin_coupon_viewer_show_meta" id="um-my-account-admin-coupon-viewer-show-meta" value="1" <?php checked($settings['my_account_admin_coupon_viewer_show_meta'] ?? false); ?> />
							<?php esc_html_e('Show Meta Data area for Coupon details', 'user-manager'); ?>
						</label>
					</div>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="my_account_admin_user_viewer_enabled" id="um-my-account-admin-user-viewer-enabled" value="1" <?php checked($settings['my_account_admin_user_viewer_enabled'] ?? false); ?> />
						<?php esc_html_e('My Account Admin User Viewer', 'user-manager'); ?>
					</label>
				</div>
				<div class="um-my-account-user-viewer-sub-settings" style="margin-left: 24px; padding-left: 16px; border-left: 2px solid #dcdcde;">
					<div class="um-form-field" id="um-my-account-admin-user-viewer-users-field" style="<?php echo empty($settings['my_account_admin_user_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-user-viewer-usernames"><?php esc_html_e('Allowed usernames (comma-separated)', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_user_viewer_usernames" id="um-my-account-admin-user-viewer-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_user_viewer_usernames'] ?? ''); ?>" placeholder="username1, username2" />
						<?php self::render_role_checkboxes('my_account_admin_user_viewer_roles', $settings['my_account_admin_user_viewer_roles'] ?? [], $available_roles, __('Allowed roles for Admin: Users', 'user-manager')); ?>
					</div>
					<div class="um-form-field" id="um-my-account-admin-user-meta-field" style="<?php echo empty($settings['my_account_admin_user_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label>
							<input type="checkbox" name="my_account_admin_user_viewer_show_meta" id="um-my-account-admin-user-viewer-show-meta" value="1" <?php checked($settings['my_account_admin_user_viewer_show_meta'] ?? false); ?> />
							<?php esc_html_e('Show Meta Data area for User details', 'user-manager'); ?>
						</label>
					</div>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="my_account_admin_activity_viewer_enabled" id="um-my-account-admin-activity-viewer-enabled" value="1" <?php checked($settings['my_account_admin_activity_viewer_enabled'] ?? false); ?> />
						<?php esc_html_e('My Account Admin Activity Viewer', 'user-manager'); ?>
					</label>
				</div>
				<div class="um-my-account-activity-viewer-sub-settings" style="margin-left: 24px; padding-left: 16px; border-left: 2px solid #dcdcde;">
					<div class="um-form-field" id="um-my-account-admin-activity-viewer-users-field" style="<?php echo empty($settings['my_account_admin_activity_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-activity-viewer-usernames"><?php esc_html_e('Allowed usernames (comma-separated)', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_activity_viewer_usernames" id="um-my-account-admin-activity-viewer-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_activity_viewer_usernames'] ?? ''); ?>" placeholder="username1, username2" />
						<?php self::render_role_checkboxes('my_account_admin_activity_viewer_roles', $settings['my_account_admin_activity_viewer_roles'] ?? [], $available_roles, __('Allowed roles for Admin: Activity', 'user-manager')); ?>
						<label for="um-my-account-admin-activity-viewer-hidden-emails"><?php esc_html_e('Partial Match Emails to Hide on Front End (comma-separated)', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_activity_viewer_hidden_email_partials" id="um-my-account-admin-activity-viewer-hidden-emails" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_activity_viewer_hidden_email_partials'] ?? ''); ?>" placeholder="internal@, @mycompany.com" />
						<p class="description"><?php esc_html_e('If an email contains any value from this list, that user activity record is excluded from front-end Activity results.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field" id="um-my-account-admin-activity-viewer-actions-field" style="<?php echo empty($settings['my_account_admin_activity_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label class="um-label-block"><strong><?php esc_html_e('Only Display Actions', 'user-manager'); ?></strong></label>
						<p class="description" style="margin-top:0;"><?php esc_html_e('If none are checked, all actions are shown.', 'user-manager'); ?></p>
						<?php if (!empty($available_activity_actions)) : ?>
							<div class="um-checkbox-grid">
								<?php foreach ($available_activity_actions as $activity_action) : ?>
									<label class="um-checkbox-chip">
										<input type="checkbox" name="my_account_admin_activity_viewer_actions[]" value="<?php echo esc_attr($activity_action); ?>" <?php checked(in_array($activity_action, $selected_activity_actions, true)); ?> />
										<span><?php echo esc_html($activity_action); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<p class="description"><?php esc_html_e('No activity actions found yet. Actions appear after user activity is logged.', 'user-manager'); ?></p>
						<?php endif; ?>
					</div>
					<div class="um-form-field" id="um-my-account-admin-activity-viewer-role-review-field" style="<?php echo empty($settings['my_account_admin_activity_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label>
							<input type="checkbox" name="my_account_admin_activity_viewer_role_review_enabled" id="um-my-account-admin-activity-viewer-role-review-enabled" value="1" <?php checked($settings['my_account_admin_activity_viewer_role_review_enabled'] ?? false); ?> />
							<?php esc_html_e('Display "User role change found in past" Flag on Users where they have another record in the log with a different Role', 'user-manager'); ?>
						</label>
					</div>
				</div>
				</div>
			</div>
		</div>
		<?php self::render_additional_meta_fields_repeater_assets(); ?>
		<?php
	}

	/**
	 * Render a read-only preview under the Additional Flag repeater showing
	 * the latest orders, the meta values that would be compared, and
	 * whether each configured compare flag would render for each order.
	 *
	 * Rendered only when the My Account Admin Order Viewer is enabled and
	 * at least one compare-flag row is configured.
	 */
	private static function render_additional_meta_compare_flags_preview(): void {
		if (!class_exists('User_Manager_My_Account_Site_Admin')) {
			return;
		}
		if (!method_exists('User_Manager_My_Account_Site_Admin', 'get_order_list_additional_meta_compare_flags_preview')) {
			return;
		}

		$preview_rows = User_Manager_My_Account_Site_Admin::get_order_list_additional_meta_compare_flags_preview(5);
		?>
		<div class="um-meta-compare-flags-preview" style="margin-top:12px;">
			<div class="um-admin-card" style="background:#fff;border:1px solid #dcdcde;border-radius:4px;padding:12px;">
				<h4 style="margin:0 0 6px;">
					<span class="dashicons dashicons-visibility" style="line-height:1.2;"></span>
					<?php esc_html_e('Preview: latest 5 orders', 'user-manager'); ?>
				</h4>
				<p class="description" style="margin:0 0 8px;">
					<?php esc_html_e('Re-saves settings to refresh this preview. Each row shows the compared meta values, the calculation, and whether the flag would render on the Admin: Orders list.', 'user-manager'); ?>
				</p>
				<?php if (empty($preview_rows)) : ?>
					<p class="description" style="margin:0;">
						<?php esc_html_e('No preview available yet. Save at least one compare-flag row with Meta Field A / Meta Field B / Flag Title set, and make sure this site has at least one WooCommerce order.', 'user-manager'); ?>
					</p>
				<?php else : ?>
					<?php foreach ($preview_rows as $row) : ?>
						<div class="um-meta-compare-flags-preview-order" style="padding:8px;border-top:1px solid #f0f0f1;">
							<strong><?php
								/* translators: 1: order number, 2: order id */
								echo esc_html(sprintf(__('Order #%1$s (ID %2$d)', 'user-manager'), (string) $row['order_number'], (int) $row['order_id']));
							?></strong>
							<?php if (empty($row['flags'])) : ?>
								<p class="description" style="margin:4px 0 0;">
									<?php esc_html_e('No compare-flag rows configured.', 'user-manager'); ?>
								</p>
							<?php else : ?>
								<ul style="list-style:disc;margin:6px 0 0 20px;padding:0;">
									<?php foreach ($row['flags'] as $f) :
										$operator_label = $f['operator'] === 'are_they_not_equal'
											? __('Values are NOT equal', 'user-manager')
											: __('Values are equal', 'user-manager');
										$would = !empty($f['would_display']);
										$badge_style = sprintf(
											'display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:%1$s;color:%2$s;margin-left:6px;vertical-align:middle;',
											esc_attr($f['background_color'] !== '' ? $f['background_color'] : '#000000'),
											esc_attr($f['text_color'] !== '' ? $f['text_color'] : '#ffffff')
										);
										?>
										<li style="margin-bottom:4px;">
											<code><?php echo esc_html($f['meta_key_a']); ?></code>
											= <?php echo $f['value_a'] === '' ? '<em>' . esc_html__('(empty)', 'user-manager') . '</em>' : ('"' . esc_html($f['value_a']) . '"'); ?>
											&nbsp;|&nbsp;
											<code><?php echo esc_html($f['meta_key_b']); ?></code>
											= <?php echo $f['value_b'] === '' ? '<em>' . esc_html__('(empty)', 'user-manager') . '</em>' : ('"' . esc_html($f['value_b']) . '"'); ?>
											<br />
											<span class="description"><?php echo esc_html($operator_label); ?><?php
												if (isset($f['grace_value']) && $f['grace_value'] !== null) {
													$grace_operator_raw = isset($f['grace_operator']) ? (string) $f['grace_operator'] : '';
													if ($grace_operator_raw === '') {
														$grace_operator_raw = $f['operator'] === 'are_they_equal' ? 'exceeds' : 'within';
													}
													$grace_operator_label = $grace_operator_raw === 'within'
														? __('within', 'user-manager')
														: __('exceeds', 'user-manager');
													echo esc_html(sprintf(
														' — ' . __('grace %1$s (%2$s)', 'user-manager'),
														(string) $f['grace_value'],
														$grace_operator_label
													));
												}
											?></span>
											<br />
											<span class="description"><?php echo esc_html((string) $f['calculation']); ?></span>
											<br />
											<?php if ($would) : ?>
												<strong style="color:#008a20;">
													<?php esc_html_e('Flag would display:', 'user-manager'); ?>
												</strong>
												<span style="<?php echo $badge_style; ?>"><?php echo esc_html((string) $f['title']); ?></span>
											<?php else : ?>
												<strong style="color:#8c8f94;">
													<?php esc_html_e('Flag would NOT display for this order.', 'user-manager'); ?>
												</strong>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get all registered WordPress roles.
	 *
	 * @return array<string,string>
	 */
	private static function get_available_roles(): array {
		if (!function_exists('wp_roles')) {
			return [];
		}
		$wp_roles = wp_roles();
		if (!$wp_roles || !method_exists($wp_roles, 'get_names')) {
			return [];
		}
		$names = $wp_roles->get_names();
		return is_array($names) ? $names : [];
	}

	/**
	 * Normalize selected role values from settings.
	 *
	 * @param mixed $raw Raw stored value.
	 * @return array<int,string>
	 */
	private static function normalize_selected_roles($raw): array {
		$parts = [];
		if (is_array($raw)) {
			$parts = $raw;
		} elseif (is_string($raw) && trim($raw) !== '') {
			$split = preg_split('/[\s,]+/', $raw);
			$parts = is_array($split) ? $split : [];
		}

		$roles = [];
		foreach ($parts as $part) {
			$key = sanitize_key((string) $part);
			if ($key === '') {
				continue;
			}
			$roles[] = $key;
		}
		$roles = array_values(array_unique($roles));

		$valid_roles = array_keys(self::get_available_roles());
		if (!empty($valid_roles)) {
			$roles = array_values(array_intersect($roles, $valid_roles));
		}

		return $roles;
	}

	/**
	 * Normalize selected activity actions from settings.
	 *
	 * @param mixed $raw Raw stored value.
	 * @return array<int,string>
	 */
	private static function normalize_activity_actions($raw): array {
		if (!is_array($raw)) {
			return [];
		}

		$actions = [];
		foreach ($raw as $action) {
			$action = sanitize_text_field((string) $action);
			if ($action === '') {
				continue;
			}
			$actions[] = $action;
		}

		return array_values(array_unique($actions));
	}

	/**
	 * Return distinct action values from user activity table.
	 *
	 * @return array<int,string>
	 */
	private static function get_available_activity_actions(): array {
		global $wpdb;

		if (!$wpdb instanceof wpdb) {
			return [];
		}

		$table = $wpdb->prefix . 'um_user_activity';
		$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
		if ($table_exists !== $table) {
			return [];
		}

		$rows = $wpdb->get_col("SELECT DISTINCT action FROM {$table} WHERE action <> '' ORDER BY action ASC");
		if (!is_array($rows)) {
			return [];
		}

		$actions = [];
		foreach ($rows as $row) {
			$action = sanitize_text_field((string) $row);
			if ($action === '') {
				continue;
			}
			$actions[] = $action;
		}

		return array_values(array_unique($actions));
	}

	/**
	 * Render role checkbox chips for an access scope.
	 *
	 * @param string              $name Field name.
	 * @param mixed               $selected_raw Selected values.
	 * @param array<string,string> $available_roles Available roles.
	 * @param string              $label Label.
	 */
	private static function render_role_checkboxes(string $name, $selected_raw, array $available_roles, string $label): void {
		if (empty($available_roles)) {
			return;
		}
		$selected = self::normalize_selected_roles($selected_raw);
		?>
		<div style="margin-top:10px;">
			<label class="um-label-block"><?php echo esc_html($label); ?></label>
			<div class="um-checkbox-grid">
				<?php foreach ($available_roles as $role_key => $role_label) : ?>
					<label class="um-checkbox-chip">
						<input type="checkbox" name="<?php echo esc_attr($name); ?>[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $selected, true)); ?> />
						<span><?php echo esc_html($role_label); ?> <code><?php echo esc_html($role_key); ?></code></span>
					</label>
				<?php endforeach; ?>
			</div>
			<p class="description"><?php esc_html_e('If any selected role matches the current user, access is granted even if the username is not listed above.', 'user-manager'); ?></p>
		</div>
		<?php
	}

	/**
	 * Build a human-readable note of current order statuses.
	 */
	private static function get_order_statuses_note(): string {
		if (!function_exists('wc_get_order_statuses')) {
			return __('Available statuses could not be loaded because WooCommerce is unavailable.', 'user-manager');
		}

		$statuses = wc_get_order_statuses();
		if (!is_array($statuses) || empty($statuses)) {
			return __('Available statuses could not be loaded.', 'user-manager');
		}

		$chunks = [];
		foreach ($statuses as $status_key => $status_label) {
			$key = strtolower((string) $status_key);
			$key = str_replace('-', '_', $key);
			$chunks[] = $key . ' (' . wp_strip_all_tags((string) $status_label) . ')';
		}

		return sprintf(
			/* translators: %s: comma-separated status key list */
			__('Available statuses in this store: %s', 'user-manager'),
			implode(', ', $chunks)
		);
	}

	/**
	 * @param mixed $raw Raw settings value.
	 * @return array<string,string>
	 */
	private static function normalize_order_status_title_overrides($raw): array {
		if (!is_array($raw)) {
			return [];
		}

		$out = [];
		foreach ($raw as $status_key => $label) {
			$normalized = self::normalize_order_status_key((string) $status_key);
			if ($normalized === '') {
				continue;
			}
			$label = sanitize_text_field((string) $label);
			if ($label === '') {
				continue;
			}
			$out[$normalized] = $label;
		}

		return $out;
	}

	/**
	 * Normalize status keys like `pending`, `wc_pending`, or `wc-pending` to `wc-pending`.
	 */
	private static function normalize_order_status_key(string $raw): string {
		$raw = trim(strtolower($raw));
		if ($raw === '') {
			return '';
		}
		$raw = str_replace('_', '-', $raw);
		$raw = sanitize_key($raw);
		if ($raw === '') {
			return '';
		}
		if (strpos($raw, 'wc-') !== 0) {
			$raw = 'wc-' . ltrim($raw, '-');
		}
		return sanitize_key($raw);
	}
}

