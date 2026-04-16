<?php
/**
 * Add-on card: My Account Site Admin.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_My_Account_Site_Admin {

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
					<div class="um-form-field" id="um-my-account-admin-order-additional-meta-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-order-additional-meta-fields"><?php esc_html_e('Additional Meta Fields to Display Under Order', 'user-manager'); ?></label>
						<textarea name="my_account_admin_order_additional_meta_fields" id="um-my-account-admin-order-additional-meta-fields" class="large-text" rows="4" placeholder="_tracking_number:Tracking Number, _invoice_url:Invoice URL"><?php echo esc_textarea($settings['my_account_admin_order_additional_meta_fields'] ?? ''); ?></textarea>
						<p class="description"><?php esc_html_e('Format: meta_field:Label:prefix_before_value[:flags]. Renders under each Order detail view. Optional flags: text_line_count, text-file-line-count, line_count, count_lines, preview, preview_file, file_preview, preview-modal, preview_modal, display_when_empty, display-empty, show_empty, show_if_empty, render_if_empty. Separate values using commas and/or line breaks.', 'user-manager'); ?></p>
						<p class="description"><?php esc_html_e('Example with line count, preview modal, and always-show row: _volunteer_file:Volunteer File:https://example.com/uploads/::text_line_count|preview|display_when_empty', 'user-manager'); ?></p>
						<p class="description"><?php esc_html_e('Debug: append ?um_text_file_line_count_debug=1 to the Admin: Orders URL to output URL-build/fetch/cache diagnostics for flagged file fields.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field" id="um-my-account-admin-order-additional-meta-list-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-order-additional-meta-fields-list"><?php esc_html_e('Additional Meta Fields to Display Under Order in All Orders Screen', 'user-manager'); ?></label>
						<textarea name="my_account_admin_order_list_additional_meta_fields" id="um-my-account-admin-order-additional-meta-fields-list" class="large-text" rows="5" placeholder="_tracking_number:Tracking Number, _invoice_url:Invoice URL"><?php echo esc_textarea($settings['my_account_admin_order_list_additional_meta_fields'] ?? ''); ?></textarea>
						<p class="description"><?php esc_html_e('Format: meta_field:Label:prefix_before_value[:flags]. Renders in Admin: Orders list (inline under action buttons). Optional flags: text_line_count, text-file-line-count, line_count, count_lines, preview, preview_file, file_preview, preview-modal, preview_modal, display_when_empty, display-empty, show_empty, show_if_empty, render_if_empty. Separate values using commas and/or line breaks.', 'user-manager'); ?></p>
						<p class="description"><?php esc_html_e('Example with line count, preview modal, and always-show row: _volunteer_file:Volunteer File:https://example.com/uploads/::text_line_count|preview|display_when_empty', 'user-manager'); ?></p>
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
						<label for="um-my-account-admin-order-additional-flag-fields-list"><?php esc_html_e('Additional Flag to Display Below Additional Fields in All Orders Screen', 'user-manager'); ?></label>
						<textarea name="my_account_admin_order_list_additional_flag_fields" id="um-my-account-admin-order-additional-flag-fields-list" class="large-text" rows="5" placeholder="_meta_field_a:_meta_field_b:are_they_equal:3:FLAG TITLE:#000000:#ffffff"><?php echo esc_textarea($settings['my_account_admin_order_list_additional_flag_fields'] ?? ''); ?></textarea>
						<p class="description"><?php esc_html_e('Format (one per line): meta_field_a:meta_field_b:are_they_equal:FLAG TITLE:bgcolor:textcolor OR meta_field_a:meta_field_b:are_they_equal:grace_value:FLAG TITLE:bgcolor:textcolor', 'user-manager'); ?></p>
						<p class="description"><?php esc_html_e('Without grace_value, the flag renders when the compared values are equal (case-insensitive). With grace_value, both values must be numeric and the flag renders only when ABS(meta_a - meta_b) is greater than grace_value. Default colors: black background and white text.', 'user-manager'); ?></p>
					</div>
					<div class="um-form-field" id="um-my-account-admin-order-meta-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label>
							<input type="checkbox" name="my_account_admin_order_viewer_show_meta" id="um-my-account-admin-order-viewer-show-meta" value="1" <?php checked($settings['my_account_admin_order_viewer_show_meta'] ?? false); ?> />
							<?php esc_html_e('Show Meta Data area for Order details', 'user-manager'); ?>
						</label>
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
					<div class="um-form-field" id="um-my-account-admin-activity-viewer-wp-admin-redirect-field" style="<?php echo empty($settings['my_account_admin_activity_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-activity-viewer-wp-admin-redirect-list"><?php esc_html_e('WP Administrators to Redirect to My Account if Accessing WP-Admin', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_activity_viewer_wp_admin_redirect_list" id="um-my-account-admin-activity-viewer-wp-admin-redirect-list" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_activity_viewer_wp_admin_redirect_list'] ?? ''); ?>" placeholder="adminuser, admin@example.com, 123" />
						<p class="description"><?php esc_html_e('Comma-separated list of WP Administrator usernames, emails, or user IDs that should be redirected to My Account whenever they attempt to access wp-admin. Leaves their role unchanged. If empty, no users are redirected.', 'user-manager'); ?></p>
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

