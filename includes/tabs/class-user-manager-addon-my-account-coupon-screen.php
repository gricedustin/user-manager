<?php
/**
 * Add-on card: My Account Coupons Page.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_My_Account_Coupon_Screen {

	public static function render(array $settings): void {
		$enabled          = !empty($settings['my_account_coupon_screen_enabled']);
		$menu_title       = isset($settings['my_account_coupon_screen_menu_title']) ? (string) $settings['my_account_coupon_screen_menu_title'] : 'Coupons';
		$page_title       = isset($settings['my_account_coupon_screen_page_title']) ? (string) $settings['my_account_coupon_screen_page_title'] : 'Coupons';
		$page_description = isset($settings['my_account_coupon_screen_page_description']) ? (string) $settings['my_account_coupon_screen_page_description'] : '';
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-my-account-coupon-screen" data-um-active-selectors="#um-my-account-coupon-screen-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-tickets-alt"></span>
				<h2><?php esc_html_e('My Account Coupons Page', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="my_account_coupon_screen_enabled" id="um-my-account-coupon-screen-enabled" value="1" <?php checked($enabled); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Add a dedicated Coupons tab to My Account that displays eligible user coupons using WooCommerce-style notices.', 'user-manager'); ?></p>
				</div>

				<div id="um-my-account-coupon-screen-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-my-account-coupon-screen-menu-title"><?php esc_html_e('Menu Title Name', 'user-manager'); ?></label>
						<input type="text" class="regular-text" name="my_account_coupon_screen_menu_title" id="um-my-account-coupon-screen-menu-title" value="<?php echo esc_attr($menu_title); ?>" />
						<p class="description"><?php esc_html_e('Default: Coupons', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label for="um-my-account-coupon-screen-page-title"><?php esc_html_e('Page Title', 'user-manager'); ?></label>
						<input type="text" class="regular-text" name="my_account_coupon_screen_page_title" id="um-my-account-coupon-screen-page-title" value="<?php echo esc_attr($page_title); ?>" />
						<p class="description"><?php esc_html_e('Default: Coupons', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label for="um-my-account-coupon-screen-page-description"><?php esc_html_e('Page Description under Title', 'user-manager'); ?></label>
						<textarea class="large-text" rows="3" name="my_account_coupon_screen_page_description" id="um-my-account-coupon-screen-page-description"><?php echo esc_textarea($page_description); ?></textarea>
						<p class="description"><?php esc_html_e('Default: none', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

