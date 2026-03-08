<?php
/**
 * Add-on card: My Account Site Admin.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_My_Account_Site_Admin {

	public static function render(array $settings): void {
		$is_enabled = array_key_exists('my_account_site_admin_enabled', $settings)
			? !empty($settings['my_account_site_admin_enabled'])
			: (
				!empty($settings['my_account_admin_order_viewer_enabled'])
				|| !empty($settings['my_account_admin_product_viewer_enabled'])
				|| !empty($settings['my_account_admin_coupon_viewer_enabled'])
				|| !empty($settings['my_account_admin_user_viewer_enabled'])
			);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-my-account" data-um-active-selectors="#um-my-account-site-admin-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-admin-site"></span>
				<h2><?php esc_html_e('My Account Site Admin', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="my_account_site_admin_enabled" id="um-my-account-site-admin-enabled" value="1" <?php checked($is_enabled); ?> />
						<?php esc_html_e('Activate My Account Site Admin', 'user-manager'); ?>
					</label>
				</div>
				<div id="um-my-account-site-admin-fields" style="<?php echo $is_enabled ? '' : 'display:none;'; ?>">
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
		</div>
		<?php
	}
}

