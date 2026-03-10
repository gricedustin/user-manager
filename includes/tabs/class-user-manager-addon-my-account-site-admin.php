<?php
/**
 * Add-on card: My Account Site Admin.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_My_Account_Site_Admin {

	public static function render(array $settings): void {
		$available_roles = self::get_available_roles();
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
					</div>
					<div class="um-form-field" id="um-my-account-admin-order-approver-users-field" style="<?php echo empty($settings['my_account_admin_order_viewer_enabled']) ? 'display:none;' : ''; ?>">
						<label for="um-my-account-admin-order-approval-usernames"><?php esc_html_e('Order approval allowed usernames (comma-separated)', 'user-manager'); ?></label>
						<input type="text" name="my_account_admin_order_approval_usernames" id="um-my-account-admin-order-approval-usernames" class="large-text" value="<?php echo esc_attr($settings['my_account_admin_order_approval_usernames'] ?? ''); ?>" placeholder="approver1, approver2" />
						<p class="description"><?php esc_html_e('These users can see an "Approve" button for pending payment orders, which moves the order to Processing.', 'user-manager'); ?></p>
						<?php self::render_role_checkboxes('my_account_admin_order_approval_roles', $settings['my_account_admin_order_approval_roles'] ?? [], $available_roles, __('Order approval allowed roles', 'user-manager')); ?>
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
}

