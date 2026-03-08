<?php
/**
 * Add-on card: Coupon Notifications for users with coupons.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Coupon_Notifications_For_Users_With_Coupons {

	public static function render(array $settings): void {
		$enabled = !empty($settings['user_coupon_notifications_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-coupon-notifications" data-um-active-selectors="#um-coupon-notifications-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-megaphone"></span>
				<h2><?php esc_html_e('Coupon Notifications for Users with Coupons', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="user_coupon_notifications_enabled" id="um-coupon-notifications-enabled" value="1" <?php checked($enabled); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
				</div>

				<div id="um-coupon-notifications-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description"><?php esc_html_e('Shows a dismissible banner on selected storefront pages listing every coupon tied to the logged-in user.', 'user-manager'); ?></p>

				<div class="um-settings-two-column">
					<div class="um-settings-column">
						<h3 class="um-checkbox-section-title"><?php esc_html_e('Show notification on', 'user-manager'); ?></h3>
						<div class="um-checkbox-grid">
							<label class="um-checkbox-chip">
								<input type="checkbox" name="coupon_notifications_show_on_cart" value="1" <?php checked(!empty($settings['coupon_notifications_show_on_cart'])); ?> />
								<span><?php esc_html_e('Cart', 'user-manager'); ?></span>
							</label>
							<label class="um-checkbox-chip">
								<input type="checkbox" name="coupon_notifications_show_on_checkout" value="1" <?php checked(!empty($settings['coupon_notifications_show_on_checkout'])); ?> />
								<span><?php esc_html_e('Checkout', 'user-manager'); ?></span>
							</label>
							<label class="um-checkbox-chip">
								<input type="checkbox" name="coupon_notifications_show_on_my_account" value="1" <?php checked(!empty($settings['coupon_notifications_show_on_my_account'])); ?> />
								<span><?php esc_html_e('My Account', 'user-manager'); ?></span>
							</label>
							<label class="um-checkbox-chip">
								<input type="checkbox" name="coupon_notifications_show_on_home" value="1" <?php checked(!empty($settings['coupon_notifications_show_on_home'])); ?> />
								<span><?php esc_html_e('Home page', 'user-manager'); ?></span>
							</label>
							<label class="um-checkbox-chip">
								<input type="checkbox" name="coupon_notifications_show_on_product" value="1" <?php checked(!empty($settings['coupon_notifications_show_on_product'])); ?> />
								<span><?php esc_html_e('Single product', 'user-manager'); ?></span>
							</label>
							<label class="um-checkbox-chip">
								<input type="checkbox" name="coupon_notifications_show_on_archives" value="1" <?php checked(!empty($settings['coupon_notifications_show_on_archives'])); ?> />
								<span><?php esc_html_e('Product archives (shop/category/tag)', 'user-manager'); ?></span>
							</label>
							<label class="um-checkbox-chip">
								<input type="checkbox" name="coupon_notifications_show_on_posts" value="1" <?php checked(!empty($settings['coupon_notifications_show_on_posts'])); ?> />
								<span><?php esc_html_e('Blog posts', 'user-manager'); ?></span>
							</label>
							<label class="um-checkbox-chip">
								<input type="checkbox" name="coupon_notifications_show_on_pages" value="1" <?php checked(!empty($settings['coupon_notifications_show_on_pages'])); ?> />
								<span><?php esc_html_e('Regular pages', 'user-manager'); ?></span>
							</label>
						</div>
					</div>
					<div class="um-settings-column">
						<div class="um-form-field">
							<label for="coupon_notifications_collapse_threshold"><?php esc_html_e('Collapse threshold', 'user-manager'); ?></label>
							<input type="number" min="0" class="small-text" name="coupon_notifications_collapse_threshold" id="coupon_notifications_collapse_threshold" value="<?php echo esc_attr($settings['coupon_notifications_collapse_threshold'] ?? 1); ?>" />
							<p class="description"><?php esc_html_e('Collapse into an accordion if more than this number of coupons are available. Use 0 to always collapse.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_notifications_clear_coupons_when_cart_empty" value="1" <?php checked(!empty($settings['coupon_notifications_clear_coupons_when_cart_empty'])); ?> />
								<?php esc_html_e('If Cart Quantity Changes from 1 to 0 and Cart is Now Empty, Remove All Coupon Codes from Cart', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When enabled, any time the WooCommerce cart becomes empty on the frontend, all applied coupon codes are automatically removed so new notifications and remainders can surface cleanly.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_notifications_block_support" value="1" <?php checked(!empty($settings['coupon_notifications_block_support'])); ?> />
								<?php esc_html_e('Enable Cart/Checkout block compatibility', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When using the WooCommerce Cart or Checkout blocks, prepend the coupon notice via render_block so it still appears. Leave unchecked to rely on classic template hooks only.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_notifications_hide_store_credit" value="1" <?php checked(!empty($settings['coupon_notifications_hide_store_credit'])); ?> />
								<?php esc_html_e('Hide WooCommerce store credit container', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Use when Store Credits plugin is active to suppress its default notice.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_notifications_debug" value="1" <?php checked(!empty($settings['coupon_notifications_debug'])); ?> />
								<?php esc_html_e('Enable debug output', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Shows detailed coupon debugging information on the frontend when logged in.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_notifications_sort_by_expiration" value="1" <?php checked(!empty($settings['coupon_notifications_sort_by_expiration'])); ?> />
								<?php esc_html_e('Sort by Expiration Date', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Sorts coupons by expiration date with soonest expiring first, and coupons with no expiration at the bottom. Secondary sort: by expiration date, then by highest value to lowest.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_notifications_block_checkout_shipping_notice" value="1" <?php checked(!empty($settings['coupon_notifications_block_checkout_shipping_notice'])); ?> />
								<?php esc_html_e('Display Coupon Shipping Notice for Block Checkout', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Shows a notice in block checkout when a coupon covers 100% of the cart subtotal, explaining that coupons apply to products only and do not cover shipping costs.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_notifications_classic_checkout_shipping_notice" value="1" <?php checked(!empty($settings['coupon_notifications_classic_checkout_shipping_notice'])); ?> />
								<?php esc_html_e('Display Coupon Shipping Notice for Classic Checkout', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Shows a notice in classic/legacy checkout when a coupon covers 100% of the cart subtotal, explaining that coupons apply to products only and do not cover shipping costs.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="coupon_notifications_shipping_notice_title"><?php esc_html_e('Notice Title', 'user-manager'); ?></label>
							<input type="text" name="coupon_notifications_shipping_notice_title" id="coupon_notifications_shipping_notice_title" class="regular-text" value="<?php echo esc_attr($settings['coupon_notifications_shipping_notice_title'] ?? 'Coupon Notice'); ?>" />
							<p class="description"><?php esc_html_e('Title displayed in the shipping notice.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="coupon_notifications_shipping_notice_description"><?php esc_html_e('Notice Description', 'user-manager'); ?></label>
							<textarea name="coupon_notifications_shipping_notice_description" id="coupon_notifications_shipping_notice_description" class="large-text" rows="3"><?php echo esc_textarea($settings['coupon_notifications_shipping_notice_description'] ?? 'Coupons and store credits apply to product prices only, and do not cover shipping costs. Shipping is calculated separately from our shipping carriers.'); ?></textarea>
							<p class="description"><?php esc_html_e('Description text displayed in the shipping notice.', 'user-manager'); ?></p>
						</div>
					</div>
				</div>
				</div>
			</div>
		</div>
		<?php
	}
}

