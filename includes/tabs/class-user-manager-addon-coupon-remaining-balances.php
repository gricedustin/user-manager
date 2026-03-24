<?php
/**
 * Add-on card: Coupon Remaining Balances.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Coupon_Remaining_Balances {

	public static function render(array $settings): void {
		$templates = User_Manager_Core::get_email_templates();
		$selected_template = isset($settings['coupon_remainder_email_template']) ? (string) $settings['coupon_remainder_email_template'] : '';
		if ($selected_template === '') {
			$selected_template = '__um_default__';
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-coupon-remainder" data-um-active-selectors="#um-coupon-remainder-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-admin-network"></span>
				<h2><?php esc_html_e('User Coupon Remaining Balances', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="coupon_remainder_enabled" id="um-coupon-remainder-enabled" value="1" <?php checked(!empty($settings['coupon_remainder_enabled'])); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('If parameters are met after checkout, the system creates a fresh fixed cart coupon covering the remaining balance, sets usage limit to 1, and restricts it to the shopper\'s email automatically.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-coupon-remainder-fields" style="<?php echo !empty($settings['coupon_remainder_enabled']) ? '' : 'display:none;'; ?>">
				<p class="description">
					<?php esc_html_e('Behaves like a lightweight gift card/store credit: after checkout, unused funds from qualifying fixed cart coupons are rolled into a brand-new coupon tied to the shopper\'s email, with a single-use limit.', 'user-manager'); ?>
				</p>

				<div class="um-form-field">
					<label for="coupon_remainder_min_amount"><?php esc_html_e('Only Create if Remaining Value is Above', 'user-manager'); ?></label>
					<input type="number" step="0.01" min="0" name="coupon_remainder_min_amount" id="coupon_remainder_min_amount" class="regular-text" value="<?php echo esc_attr($settings['coupon_remainder_min_amount'] ?? '0'); ?>" placeholder="10.00" />
					<p class="description"><?php esc_html_e('Prevents tiny balances from generating new coupons. Enter 0 to always create when a remainder exists.', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label for="coupon_remainder_source_prefixes"><?php esc_html_e('Only Create if Coupon Code is Prefixed With...', 'user-manager'); ?></label>
					<textarea name="coupon_remainder_source_prefixes" id="coupon_remainder_source_prefixes" class="regular-text" rows="4" placeholder="gift-
credit-
promo-"><?php echo esc_textarea($settings['coupon_remainder_source_prefixes'] ?? ''); ?></textarea>
					<p class="description"><?php esc_html_e('Optional. Enter one prefix per line. Leave blank to skip this check. Matching is case-insensitive and checks if the code begins with any line. A coupon will match if it meets ANY of the three requirement types (Prefix OR Contains OR Suffix). Depending on your settings here, you may want to even add "remaining-balance-" as a match here as well to give out remaining balances for remaining balances', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label for="coupon_remainder_source_contains"><?php esc_html_e('Only Create if Coupon Code Contains...', 'user-manager'); ?></label>
					<textarea name="coupon_remainder_source_contains" id="coupon_remainder_source_contains" class="regular-text" rows="4" placeholder="gift
credit
promo"><?php echo esc_textarea($settings['coupon_remainder_source_contains'] ?? ''); ?></textarea>
					<p class="description"><?php esc_html_e('Optional. Enter one string per line. Leave blank to skip this check. Matching is case-insensitive and checks if the code contains any of these strings anywhere in the code. A coupon will match if it meets ANY of the three requirement types (Prefix OR Contains OR Suffix).', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label for="coupon_remainder_source_suffixes"><?php esc_html_e('Only Create if Coupon Code Ends With...', 'user-manager'); ?></label>
					<textarea name="coupon_remainder_source_suffixes" id="coupon_remainder_source_suffixes" class="regular-text" rows="4" placeholder="-2025
-2026
-PROMO"><?php echo esc_textarea($settings['coupon_remainder_source_suffixes'] ?? ''); ?></textarea>
					<p class="description"><?php esc_html_e('Optional. Enter one suffix per line. Leave blank to skip this check. Matching is case-insensitive and checks if the code ends with any of these strings. A coupon will match if it meets ANY of the three requirement types (Prefix OR Contains OR Suffix).', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label for="coupon_remainder_generated_prefix"><?php esc_html_e('Generated Code Prefix (defaults to remaining-balance-)', 'user-manager'); ?></label>
					<input type="text" name="coupon_remainder_generated_prefix" id="coupon_remainder_generated_prefix" class="regular-text" value="<?php echo esc_attr($settings['coupon_remainder_generated_prefix'] ?? ''); ?>" placeholder="remaining-balance-" />
					<p class="description">
						<?php esc_html_e('New codes follow the format prefix + [OLD CODE] + [POST_ID]. Example: remaining-balance-SUMMER25-123456.', 'user-manager'); ?>
					</p>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="coupon_remainder_debug" value="1" <?php checked(!empty($settings['coupon_remainder_debug'])); ?> />
						<?php esc_html_e('Enable Thank You Page Debug Output', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Shows remainder calculation status and diagnostics on the order received page for administrators.', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="coupon_remainder_checkout_debug" value="1" <?php checked(!empty($settings['coupon_remainder_checkout_debug'])); ?> />
						<?php esc_html_e('Enable Checkout Page Debug Output', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Shows a preview of current coupons in cart and remaining balance calculations under the Place Order button on the checkout page for logged-in users.', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="coupon_remainder_checkout_notice" value="1" <?php checked(!empty($settings['coupon_remainder_checkout_notice'])); ?> />
						<?php esc_html_e('Enable Checkout Page Remaining Balance Notice - Code Used', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Displays a notice above the Place Order button informing customers that they will receive a remaining balance coupon code after placing their order. (Classic Checkout)', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="coupon_remainder_checkout_notice_block" value="1" <?php checked(!empty($settings['coupon_remainder_checkout_notice_block'])); ?> />
						<?php esc_html_e('Enable Block Checkout Page Remaining Balance Notice - Code Used', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Displays a notice above the Place Order button informing customers that they will receive a remaining balance coupon code after placing their order. (Block Checkout)', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="coupon_remainder_order_received_notice" value="1" <?php checked(!empty($settings['coupon_remainder_order_received_notice'])); ?> />
						<?php esc_html_e('Enable Order Received Page Remaining Balance Created Notice', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Displays a notice on the order received/thank you page when a remaining balance coupon has been created, letting customers know they can apply it on their next checkout.', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="coupon_remainder_copy_expiration" value="1" <?php checked(!empty($settings['coupon_remainder_copy_expiration'])); ?> />
						<?php esc_html_e('Copy source coupon expiration date to remainder coupon', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('When a remaining balance coupon is generated, copy the expiration date from the original coupon to the new code. If unchecked, the new coupon has no expiration.', 'user-manager'); ?></p>
				</div>

				<div class="um-form-field">
					<label>
						<input type="checkbox" name="coupon_remainder_free_shipping" value="1" <?php checked(!empty($settings['coupon_remainder_free_shipping'])); ?> />
						<?php esc_html_e('Apply Free Shipping to New Remaining Balance Codes', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('When enabled, each newly created remaining balance coupon will also grant free shipping (WooCommerce free shipping on the coupon).', 'user-manager'); ?></p>
				</div>

				<div class="um-send-email-option" style="margin-top:12px;">
					<div class="um-form-field-inline">
						<input type="checkbox" name="coupon_remainder_send_email" id="um-coupon-remainder-send-email" value="1" <?php checked(!empty($settings['coupon_remainder_send_email'])); ?> />
						<label for="um-coupon-remainder-send-email">
							<strong><?php esc_html_e('Send Email to User when New Remaining Balance Code is Created', 'user-manager'); ?></strong>
						</label>
					</div>
					<div id="um-coupon-remainder-email-template-wrap" style="margin-top:12px;<?php echo !empty($settings['coupon_remainder_send_email']) ? '' : 'display:none;'; ?>">
						<label for="um-coupon-remainder-email-template">
							<?php esc_html_e('Select Email Template:', 'user-manager'); ?>
							<?php User_Manager_Tab_Shared::render_template_settings_shortcut('email'); ?>
						</label>
						<select name="coupon_remainder_email_template" id="um-coupon-remainder-email-template" class="regular-text" style="margin-top:6px;">
							<option value="__um_default__" <?php selected($selected_template, '__um_default__'); ?>><?php esc_html_e('— Default Template —', 'user-manager'); ?></option>
							<?php foreach ($templates as $id => $template) : ?>
								<option value="<?php echo esc_attr($id); ?>" <?php selected($selected_template, $id); ?>><?php echo esc_html($template['title'] ?? $id); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e('Supports both %COUPONCODE% and [coupon_code] variables.', 'user-manager'); ?></p>
						<p style="margin-top:8px;">
							<button type="button" class="button" id="um-preview-coupon-remainder-email-btn"><?php esc_html_e('Preview Email', 'user-manager'); ?></button>
						</p>
					</div>
				</div>

				<p class="description">
					<?php esc_html_e('This offers a gift card-like workflow using native WooCommerce coupons - no additional coupon types or heavy plugins required. Technically, you can use 1 shared coupon code that 500 people might use, limited to 1 per person each, and each person will still get their remaining balance.', 'user-manager'); ?>
				</p>
				</div>
			</div>
		</div>
		<?php
	}
}

