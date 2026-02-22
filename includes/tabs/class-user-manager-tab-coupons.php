<?php
/**
 * Coupons tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Coupons {

	public static function render(): void {
		$settings = User_Manager_Core::get_settings();
		?>
		<div class="um-admin-grid">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-tickets-alt"></span>
					<h2><?php esc_html_e('New User Coupons', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="user_manager_save_settings" />
						<input type="hidden" name="settings_section" value="new_user_coupons" />
						<?php wp_nonce_field('user_manager_save_settings'); ?>
						
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="nuc_enabled" value="1" <?php checked($settings['nuc_enabled'] ?? false); ?> />
								<?php esc_html_e('Enable New User Coupons', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Create a user-specific coupon cloned from a template and optionally email it to the user.', 'user-manager'); ?></p>
							<p class="description"><?php esc_html_e('Coupons are not bulk generated; each user is evaluated when they log in and visit a page you enabled below.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="nuc_when"><?php esc_html_e('When to Create', 'user-manager'); ?></label>
							<select name="nuc_when" id="nuc_when" class="regular-text">
								<option value="after_registration" <?php selected(($settings['nuc_when'] ?? 'after_registration'), 'after_registration'); ?>><?php esc_html_e('After Registration', 'user-manager'); ?></option>
								<option value="after_first_order" <?php selected(($settings['nuc_when'] ?? ''), 'after_first_order'); ?>><?php esc_html_e('After First Order', 'user-manager'); ?></option>
							</select>
							<p class="description">
								<?php esc_html_e('After Registration: coupon attempt runs the first time the user logs in and hits an enabled page.', 'user-manager'); ?><br>
								<?php esc_html_e('After First Order: coupon attempt runs once the user has a completed order and visits an enabled page.', 'user-manager'); ?>
							</p>
						</div>
						
						<div class="um-form-field">
							<label for="nuc_after_date"><?php esc_html_e('Only Apply to Users Registered After Date', 'user-manager'); ?></label>
							<input type="text" name="nuc_after_date" id="nuc_after_date" class="regular-text" value="<?php echo esc_attr($settings['nuc_after_date'] ?? ''); ?>" placeholder="YYYY-MM-DD" />
							<p class="description"><?php esc_html_e('Optional. Example: 2025-01-01. Users created before this date will NOT receive a coupon.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="nuc_email_contains"><?php esc_html_e('Limit to Users with Email Address(s) Containing', 'user-manager'); ?></label>
							<input type="text" name="nuc_email_contains" id="nuc_email_contains" class="regular-text" value="<?php echo esc_attr($settings['nuc_email_contains'] ?? ''); ?>" placeholder="example.com, @company.org, +promo" />
							<p class="description"><?php esc_html_e('Optional. Comma-separated list of substrings. Only users whose email contains at least one of these will receive a coupon. Case-insensitive match.', 'user-manager'); ?></p>
						</div>

						<div class="um-form-field">
							<label for="nuc_email_exclude"><?php esc_html_e('Exclude Users with Email Address(s) Containing', 'user-manager'); ?></label>
							<input type="text" name="nuc_email_exclude" id="nuc_email_exclude" class="regular-text" value="<?php echo esc_attr($settings['nuc_email_exclude'] ?? ''); ?>" placeholder="test@example.com, @internal, +blocked" />
							<p class="description"><?php esc_html_e('Optional. Comma-separated list of substrings. Users whose email contains any of these will never receive a coupon. Case-insensitive match.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-admin-grid" style="grid-template-columns: 1fr 1fr;">
							<div>
								<div class="um-form-field">
									<?php 
									$um_all_coupons = get_posts([
										'post_type'   => 'shop_coupon',
										'post_status' => 'publish',
										'numberposts' => -1,
										'orderby'     => 'title',
										'order'       => 'ASC',
										'fields'      => 'ids',
									]);
									?>
									<label for="nuc_template_code"><?php esc_html_e('Coupon Template Code', 'user-manager'); ?></label>
									<select name="nuc_template_code" id="nuc_template_code" class="regular-text">
										<option value=""><?php esc_html_e('— Select Coupon —', 'user-manager'); ?></option>
										<?php foreach ($um_all_coupons as $cid) : ?>
											<?php
											$code = get_the_title($cid);
											if (stripos($code, 'template') === false) {
												continue;
											}
											?>
											<option value="<?php echo esc_attr($code); ?>" <?php selected(($settings['nuc_template_code'] ?? ''), $code); ?>><?php echo esc_html($code); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description">
										<?php esc_html_e('Existing coupon code to copy settings from. All values will be cloned unless overridden below. Only coupons whose codes contain the word "template" are listed here.', 'user-manager'); ?>
									</p>
								</div>
								<div class="um-form-field">
									<label for="nuc_amount_override"><?php esc_html_e('Amount Override', 'user-manager'); ?></label>
									<input type="text" name="nuc_amount_override" id="nuc_amount_override" class="regular-text" value="<?php echo esc_attr($settings['nuc_amount_override'] ?? ''); ?>" placeholder="100" />
									<p class="description"><?php esc_html_e('Optional. If set, overrides the template coupon amount.', 'user-manager'); ?></p>
								</div>
								<div class="um-form-field">
									<label for="nuc_exp_days"><?php esc_html_e('Expiration (Days)', 'user-manager'); ?></label>
									<input type="number" name="nuc_exp_days" id="nuc_exp_days" class="regular-text" value="<?php echo esc_attr($settings['nuc_exp_days'] ?? 0); ?>" min="0" />
									<p class="description"><?php esc_html_e('Optional. If > 0, sets expiration to current date + this many days. Overrides any expiration in the template.', 'user-manager'); ?></p>
								</div>
							</div>
							<div>
								<div class="um-form-field">
									<label for="nuc_code_length"><?php esc_html_e('Coupon Code Length', 'user-manager'); ?></label>
									<input type="number" name="nuc_code_length" id="nuc_code_length" class="regular-text" value="<?php echo esc_attr($settings['nuc_code_length'] ?? 8); ?>" min="4" max="32" />
									<p class="description"><?php esc_html_e('Default: 8', 'user-manager'); ?></p>
								</div>
								<div class="um-form-field">
									<label for="nuc_prefix"><?php esc_html_e('Optional Prefix', 'user-manager'); ?></label>
									<input type="text" name="nuc_prefix" id="nuc_prefix" class="regular-text" value="<?php echo esc_attr($settings['nuc_prefix'] ?? ''); ?>" placeholder="WELCOME-" />
									<p class="description"><?php esc_html_e('Supports %YEAR% which will be replaced with the current year.', 'user-manager'); ?></p>
								</div>
								<div class="um-form-field">
									<label for="nuc_postfix"><?php esc_html_e('Optional Suffix', 'user-manager'); ?></label>
									<input type="text" name="nuc_postfix" id="nuc_postfix" class="regular-text" value="<?php echo esc_attr($settings['nuc_postfix'] ?? ''); ?>" placeholder="-2025" />
									<p class="description"><?php esc_html_e('Supports %YEAR% which will be replaced with the current year.', 'user-manager'); ?></p>
								</div>
							</div>
						</div>
						
						<div class="um-send-email-option">
							<div class="um-form-field-inline">
								<input type="checkbox" name="nuc_send_email" id="nuc_send_email" value="1" <?php checked($settings['nuc_send_email'] ?? false); ?> />
								<label for="nuc_send_email"><strong><?php esc_html_e('Send Email with Coupon', 'user-manager'); ?></strong></label>
							</div>
							
							<div id="nuc-email-template-select" style="margin-top:12px;<?php echo !empty($settings['nuc_send_email']) ? '' : 'display:none;'; ?>">
								<label for="nuc_email_template"><?php esc_html_e('Email Template', 'user-manager'); ?></label>
								<select name="nuc_email_template" id="nuc_email_template" class="regular-text" style="margin-top:6px;">
									<option value=""><?php esc_html_e('— Select Template —', 'user-manager'); ?></option>
									<?php foreach (User_Manager_Core::get_email_templates() as $id => $template) : ?>
										<option value="<?php echo esc_attr($id); ?>" <?php selected(($settings['nuc_email_template'] ?? ''), $id); ?>><?php echo esc_html($template['title'] ?? $id); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e('Template supports %COUPONCODE% placeholder.', 'user-manager'); ?></p>
								<p style="margin-top:8px;">
									<button type="button" class="button" id="um-preview-nuc-email-btn">
										<?php esc_html_e('Preview Email with Sample Coupon', 'user-manager'); ?>
									</button>
								</p>
							</div>
						</div>

						<style>
						.um-checkbox-grid {
							display: grid;
							grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
							gap: 8px;
							margin-top: 8px;
						}
						.um-checkbox-chip {
							display: flex;
							align-items: flex-start;
							gap: 8px;
							padding: 8px 10px;
							border: 1px solid #dcdcde;
							border-radius: 4px;
							background: #f6f7f7;
						}
						.um-checkbox-chip input {
							margin-top: 2px;
						}
						.um-checkbox-chip span {
							display: inline-block;
							font-size: 13px;
							line-height: 1.4;
							font-weight: 400;
						}
						.um-checkbox-section-title {
							margin: 0 0 6px;
							font-size: 14px;
							font-weight: 600;
						}
						@media (max-width: 600px) {
							.um-checkbox-grid {
								grid-template-columns: 1fr;
							}
						}
						</style>
						<div class="um-form-field" style="margin-top:12px;">
							<h3 class="um-checkbox-section-title"><?php esc_html_e('Run coupon checks on', 'user-manager'); ?></h3>
							<div class="um-checkbox-grid">
								<label class="um-checkbox-chip">
									<input type="checkbox" name="nuc_run_everywhere" value="1" <?php checked($settings['nuc_run_everywhere'] ?? false); ?> />
									<span><?php esc_html_e('Entire front-end (all pages)', 'user-manager'); ?></span>
								</label>
								<label class="um-checkbox-chip">
									<input type="checkbox" name="nuc_run_my_account" value="1" <?php checked($settings['nuc_run_my_account'] ?? false); ?> />
									<span><?php esc_html_e('My Account screens', 'user-manager'); ?></span>
								</label>
								<label class="um-checkbox-chip">
									<input type="checkbox" name="nuc_run_cart" value="1" <?php checked($settings['nuc_run_cart'] ?? false); ?> />
									<span><?php esc_html_e('Cart page', 'user-manager'); ?></span>
								</label>
								<label class="um-checkbox-chip">
									<input type="checkbox" name="nuc_run_checkout" value="1" <?php checked($settings['nuc_run_checkout'] ?? false); ?> />
									<span><?php esc_html_e('Checkout page', 'user-manager'); ?></span>
								</label>
								<label class="um-checkbox-chip">
									<input type="checkbox" name="nuc_run_product" value="1" <?php checked($settings['nuc_run_product'] ?? false); ?> />
									<span><?php esc_html_e('Single product pages', 'user-manager'); ?></span>
								</label>
								<label class="um-checkbox-chip">
									<input type="checkbox" name="nuc_run_shop" value="1" <?php checked($settings['nuc_run_shop'] ?? false); ?> />
									<span><?php esc_html_e('Shop / product archive pages', 'user-manager'); ?></span>
								</label>
								<label class="um-checkbox-chip">
									<input type="checkbox" name="nuc_run_home" value="1" <?php checked($settings['nuc_run_home'] ?? false); ?> />
									<span><?php esc_html_e('Home page', 'user-manager'); ?></span>
								</label>
							</div>
							<p class="description"><?php esc_html_e('These checks supplement the automatic run during registration or first order, ensuring coupons are still created if a user meets the criteria later.', 'user-manager'); ?></p>
						</div>

						<div class="um-form-field" style="margin-top:12px;">
							<label>
								<input type="checkbox" name="nuc_auto_draft_duplicates" value="1" <?php checked($settings['nuc_auto_draft_duplicates'] ?? false); ?> />
								<?php esc_html_e('Automatically Draft Duplicated Codes', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('When New User Coupons runs on the locations above, also scan for older coupons for this user whose codes match the configured prefix/suffix pattern and move any accidental duplicates into Draft status.', 'user-manager'); ?>
							</p>
						</div>

						<div class="um-form-field" style="margin-top:12px;">
							<label>
								<input type="checkbox" name="nuc_debug_mode" value="1" <?php checked($settings['nuc_debug_mode'] ?? false); ?> />
								<?php esc_html_e('Enable Debug Mode (front-end overlay)', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Displays live troubleshooting data for administrators on the front-end so you can see why coupons were or were not created.', 'user-manager'); ?></p>
						</div>
						
						<p style="margin-top:20px;">
							<?php submit_button(__('Save Settings', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</form>
				</div>
			</div>
			
			<?php
			$templates = User_Manager_Core::get_email_templates();
			User_Manager_Tab_Shared::render_email_preview_modal($templates);
			?>
			<script>
			jQuery(function($){
				$('#nuc_send_email').on('change', function() {
					$('#nuc-email-template-select').toggle(this.checked);
				});
				$('#um-preview-nuc-email-btn').on('click', function() {
					umShowEmailPreview('nuc');
				});
			});
			</script>
			
			<?php
			$notify_enabled = !empty($settings['user_coupon_notifications_enabled']);
			?>
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-megaphone"></span>
					<h2><?php esc_html_e('User Coupon Notifications', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p class="description"><?php esc_html_e('Shows a dismissible banner on selected storefront pages listing every coupon tied to the logged-in user.', 'user-manager'); ?></p>
					<style>
					.um-settings-two-column {
						display: flex;
						flex-wrap: wrap;
						gap: 24px;
						margin-top: 12px;
					}
					.um-settings-two-column .um-settings-column {
						flex: 1 1 280px;
					}
					.um-settings-two-column label {
						display: inline-block;
						margin-bottom: 6px;
					}
					</style>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="user_manager_save_settings" />
						<input type="hidden" name="settings_section" value="coupon_notifications" />
						<?php wp_nonce_field('user_manager_save_settings'); ?>
						
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="user_coupon_notifications_enabled" value="1" <?php checked($notify_enabled); ?> />
								<?php esc_html_e('Activate', 'user-manager'); ?>
							</label>
						</div>
						
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
						
						<p style="margin-top:20px;">
							<?php submit_button(__('Save Settings', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</form>
				</div>
			</div>

			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-admin-network"></span>
					<h2><?php esc_html_e('Fixed Cart Coupon Remaining Balances (Simple Gift Card & Store Credit Functionality)', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p class="description">
						<?php esc_html_e('Behaves like a lightweight gift card/store credit: after checkout, unused funds from qualifying fixed cart coupons are rolled into a brand-new coupon tied to the shopper’s email, with a single-use limit.', 'user-manager'); ?>
					</p>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="user_manager_save_settings" />
						<input type="hidden" name="settings_section" value="coupon_remainder" />
						<?php wp_nonce_field('user_manager_save_settings'); ?>

						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_remainder_enabled" value="1" <?php checked(!empty($settings['coupon_remainder_enabled'])); ?> />
								<?php esc_html_e('Active', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('If parameters are met after checkout, the system creates a fresh fixed cart coupon covering the remaining balance, sets usage limit to 1, and restricts it to the shopper’s email automatically.', 'user-manager'); ?>
							</p>
						</div>

						<div class="um-form-field">
							<label for="coupon_remainder_min_amount"><?php esc_html_e('Only Create if Remaining Value is Above', 'user-manager'); ?></label>
							<input type="number" step="0.01" min="0" name="coupon_remainder_min_amount" id="coupon_remainder_min_amount" class="regular-text" value="<?php echo esc_attr($settings['coupon_remainder_min_amount'] ?? '0'); ?>" placeholder="10.00" />
							<p class="description"><?php esc_html_e('Prevents tiny balances from generating new coupons. Enter 0 to always create when a remainder exists.', 'user-manager'); ?></p>
						</div>

						<div class="um-form-field">
							<label for="coupon_remainder_source_prefixes"><?php esc_html_e('Only Create if Coupon Code is Prefixed With…', 'user-manager'); ?></label>
							<textarea name="coupon_remainder_source_prefixes" id="coupon_remainder_source_prefixes" class="regular-text" rows="4" placeholder="gift-
credit-
promo-"><?php echo esc_textarea($settings['coupon_remainder_source_prefixes'] ?? ''); ?></textarea>
							<p class="description"><?php esc_html_e('Optional. Enter one prefix per line. Leave blank to skip this check. Matching is case-insensitive and checks if the code begins with any line. A coupon will match if it meets ANY of the three requirement types (Prefix OR Contains OR Suffix). Depending on your settings here, you may want to even add "remaining-balance-" as a match here as well to give out remaining balances for remaining balances', 'user-manager'); ?></p>
						</div>

						<div class="um-form-field">
							<label for="coupon_remainder_source_contains"><?php esc_html_e('Only Create if Coupon Code Contains…', 'user-manager'); ?></label>
							<textarea name="coupon_remainder_source_contains" id="coupon_remainder_source_contains" class="regular-text" rows="4" placeholder="gift
credit
promo"><?php echo esc_textarea($settings['coupon_remainder_source_contains'] ?? ''); ?></textarea>
							<p class="description"><?php esc_html_e('Optional. Enter one string per line. Leave blank to skip this check. Matching is case-insensitive and checks if the code contains any of these strings anywhere in the code. A coupon will match if it meets ANY of the three requirement types (Prefix OR Contains OR Suffix).', 'user-manager'); ?></p>
						</div>

						<div class="um-form-field">
							<label for="coupon_remainder_source_suffixes"><?php esc_html_e('Only Create if Coupon Code Ends With…', 'user-manager'); ?></label>
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
								<?php esc_html_e('Enable Checkout Page Remaining Balance Notice if Coupon is Applied', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Displays a notice above the Place Order button informing customers that they will receive a remaining balance coupon code after placing their order. (Classic Checkout)', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_remainder_checkout_notice_block" value="1" <?php checked(!empty($settings['coupon_remainder_checkout_notice_block'])); ?> />
								<?php esc_html_e('Enable Block Checkout Page Remaining Balance Notice if Coupon is Applied', 'user-manager'); ?>
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

						<p class="description">
							<?php esc_html_e('This offers a gift card-like workflow using native WooCommerce coupons—no additional coupon types or heavy plugins required. Technically, you can use 1 shared coupon code that 500 people might use, limited to 1 per person each, and each person will still get their remaining balance.', 'user-manager'); ?>
						</p>

						<p style="margin-top:20px;">
							<?php submit_button(__('Save Settings', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</form>
				</div>
			</div>

			<?php
			// Migration card for store_credit coupons
			$show_migration_table = isset($_GET['show_migration_table']) && $_GET['show_migration_table'] === '1';
			$sort_by = isset($_GET['sort']) ? sanitize_key($_GET['sort']) : 'id';
			$sort_order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'desc' : 'asc';
			
			$store_credit_coupons = [];
			$total_found = 0;
			$migrated_count = 0;
			$remaining_count = 0;
			
			if ($show_migration_table) {
				$store_credit_coupons = self::get_store_credit_coupons($sort_by, $sort_order);
				$total_found = count($store_credit_coupons);
				foreach ($store_credit_coupons as $coupon) {
					if (!empty($coupon['migrated'])) {
						$migrated_count++;
					} else {
						$remaining_count++;
					}
				}
			}
			?>
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-update"></span>
					<h2><?php esc_html_e('Migrate from Store Coupons Plugin', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div style="margin-bottom:20px;">
						<label>
							<input type="checkbox" id="show-migration-table" onchange="this.checked ? window.location.href = '<?php echo esc_url(add_query_arg('show_migration_table', '1', User_Manager_Core::get_page_url(User_Manager_Core::TAB_COUPONS))); ?>' : window.location.href = '<?php echo esc_url(remove_query_arg('show_migration_table', User_Manager_Core::get_page_url(User_Manager_Core::TAB_COUPONS))); ?>'" <?php checked($show_migration_table); ?> />
							<strong><?php esc_html_e('Display Store Credits to be Migrated', 'user-manager'); ?></strong>
						</label>
						<p class="description" style="margin-top:5px;">
							<?php esc_html_e('Check this box to load and display the migration table. This may take a moment if you have many store credit coupons.', 'user-manager'); ?>
						</p>
					</div>
					
					<?php if ($show_migration_table) : ?>
					<div style="background:#fff3cd;border:1px solid #ffc107;border-left:4px solid #ffc107;border-radius:4px;padding:15px;margin-bottom:20px;">
						<h3 style="margin-top:0;"><?php esc_html_e('What This Migration Does', 'user-manager'); ?></h3>
						<p style="margin:5px 0;">
							<?php esc_html_e('This tool converts store_credit type coupons (from Store Coupons Plugin) to fixed_cart type coupons. When you migrate coupons:', 'user-manager'); ?>
						</p>
						<ul style="margin:10px 0 5px 20px;padding:0;">
							<li><?php echo wp_kses(__('Coupon type changes from <strong>store_credit</strong> to <strong>fixed_cart</strong>', 'user-manager'), ['strong' => []]); ?></li>
							<li><?php echo wp_kses(__('Usage limit is set to <strong>1</strong> (single-use)', 'user-manager'), ['strong' => []]); ?></li>
							<li><?php esc_html_e('Usage count is reset to 0 (starts fresh)', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email restrictions and assigned emails are preserved', 'user-manager'); ?></li>
							<li><?php esc_html_e('Coupon is marked as migrated to prevent duplicate migrations', 'user-manager'); ?></li>
							<li><?php esc_html_e('All other coupon settings (amount, expiration, etc.) remain unchanged', 'user-manager'); ?></li>
						</ul>
						<p style="margin:15px 0 5px 0;">
							<strong><?php esc_html_e('Why Migrate?', 'user-manager'); ?></strong>
						</p>
						<p style="margin:5px 0;">
							<?php echo wp_kses(__('The <strong>Fixed Cart Coupon Remaining Balances</strong> feature (shown above) only works with <strong>fixed_cart</strong> type coupons. If you have existing <strong>store_credit</strong> coupons from the Store Coupons Plugin, they will not automatically generate remaining balance coupons when partially used. By migrating them to <strong>fixed_cart</strong> type, these coupons will become compatible with the remaining balance system, allowing unused funds to automatically roll into new single-use coupons tied to the customer\'s email after checkout.', 'user-manager'), ['strong' => []]); ?>
						</p>
						<p style="margin:10px 0 5px 0;">
							<?php esc_html_e('This enables a seamless gift card-like experience where customers can use partial amounts and automatically receive new coupons for any remaining balance.', 'user-manager'); ?>
						</p>
					</div>
					<?php endif; ?>
					
					<?php if ($show_migration_table && $total_found > 0) : ?>
						<div style="background:#f0f6fc;border:1px solid #2271b1;border-left:4px solid #2271b1;border-radius:4px;padding:15px;margin-bottom:20px;">
							<h3 style="margin-top:0;"><?php esc_html_e('Migration Statistics', 'user-manager'); ?></h3>
							<p style="margin:5px 0;">
								<strong><?php esc_html_e('Total Store Credit Coupons Found:', 'user-manager'); ?></strong> <?php echo esc_html($total_found); ?>
							</p>
							<p style="margin:5px 0;">
								<strong><?php esc_html_e('Already Migrated:', 'user-manager'); ?></strong> <?php echo esc_html($migrated_count); ?>
							</p>
							<p style="margin:5px 0;">
								<strong><?php esc_html_e('Remaining to Migrate:', 'user-manager'); ?></strong> <?php echo esc_html($remaining_count); ?>
							</p>
						</div>

						<?php if ($remaining_count > 0) : ?>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="store-credit-migration-form" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to migrate the selected coupons? This will convert them from store_credit type to fixed_cart type.', 'user-manager')); ?>');">
								<input type="hidden" name="action" value="user_manager_migrate_store_credit_coupons" />
								<input type="hidden" name="sort" value="<?php echo esc_attr($sort_by); ?>" />
								<input type="hidden" name="order" value="<?php echo esc_attr($sort_order); ?>" />
								<?php wp_nonce_field('user_manager_migrate_store_credit_coupons'); ?>
								
								<div style="margin-bottom:15px;">
									<label>
										<input type="checkbox" id="select-all-store-credits" />
										<strong><?php esc_html_e('Select All', 'user-manager'); ?></strong>
									</label>
								</div>

								<?php
								$current_url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_COUPONS);
								$get_sort_link = function($column, $label) use ($current_url, $sort_by, $sort_order) {
									$new_order = ($sort_by === $column && $sort_order === 'asc') ? 'desc' : 'asc';
									$url = add_query_arg([
										'sort' => $column,
										'order' => $new_order,
									], $current_url);
									$arrow = '';
									if ($sort_by === $column) {
										$arrow = $sort_order === 'asc' ? ' ↑' : ' ↓';
									}
									return '<a href="' . esc_url($url) . '" style="text-decoration:none;color:inherit;">' . esc_html($label) . $arrow . '</a>';
								};
								?>
								<table class="widefat striped">
									<thead>
										<tr>
											<th style="width:40px;"><?php esc_html_e('Select', 'user-manager'); ?></th>
											<th><?php echo $get_sort_link('code', __('Coupon Code', 'user-manager')); ?></th>
											<th><?php esc_html_e('Coupon Type', 'user-manager'); ?></th>
											<th><?php echo $get_sort_link('amount', __('Amount', 'user-manager')); ?></th>
											<th><?php echo $get_sort_link('usage_count', __('Usage', 'user-manager')); ?></th>
											<th><?php esc_html_e('Assigned Email', 'user-manager'); ?></th>
											<th><?php echo $get_sort_link('migrated', __('Status', 'user-manager')); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($store_credit_coupons as $coupon) : ?>
											<?php
											$is_store_credit = $coupon['discount_type'] === 'store_credit';
											$can_migrate = $is_store_credit || (!empty($coupon['migrated']) && $coupon['discount_type'] !== 'fixed_cart');
											?>
											<?php if (!empty($coupon['migrated']) && $coupon['discount_type'] === 'fixed_cart') : ?>
												<tr style="opacity:0.6;">
													<td></td>
													<td>
														<a href="<?php echo esc_url(admin_url('post.php?post=' . $coupon['id'] . '&action=edit')); ?>">
															<strong><?php echo esc_html(strtoupper($coupon['code'])); ?></strong>
														</a>
													</td>
													<td><?php echo esc_html(ucwords(str_replace('_', ' ', $coupon['discount_type']))); ?></td>
													<td><?php echo function_exists('wc_price') ? wc_price($coupon['amount']) : esc_html($coupon['amount']); ?></td>
													<td><?php echo esc_html($coupon['usage_count']); ?> / <?php echo esc_html($coupon['usage_limit'] ?: __('Unlimited', 'user-manager')); ?></td>
													<td><?php echo esc_html($coupon['emails'] ?? '—'); ?></td>
													<td><span style="color:#155724;"><?php esc_html_e('Migrated', 'user-manager'); ?></span></td>
												</tr>
											<?php else : ?>
												<tr>
													<td>
														<?php if ($can_migrate) : ?>
															<input type="checkbox" name="coupon_ids[]" value="<?php echo esc_attr($coupon['id']); ?>" class="store-credit-checkbox" />
														<?php else : ?>
															<input type="checkbox" disabled title="<?php esc_attr_e('Cannot migrate: coupon type is not store_credit', 'user-manager'); ?>" />
														<?php endif; ?>
													</td>
													<td>
														<a href="<?php echo esc_url(admin_url('post.php?post=' . $coupon['id'] . '&action=edit')); ?>">
															<strong><?php echo esc_html(strtoupper($coupon['code'])); ?></strong>
														</a>
													</td>
													<td><?php echo esc_html(ucwords(str_replace('_', ' ', $coupon['discount_type']))); ?></td>
													<td><?php echo function_exists('wc_price') ? wc_price($coupon['amount']) : esc_html($coupon['amount']); ?></td>
													<td><?php echo esc_html($coupon['usage_count']); ?> / <?php echo esc_html($coupon['usage_limit'] ?: __('Unlimited', 'user-manager')); ?></td>
													<td><?php echo esc_html($coupon['emails'] ?? '—'); ?></td>
													<td>
														<?php if ($is_store_credit) : ?>
															<span style="color:#d63638;"><?php esc_html_e('Pending', 'user-manager'); ?></span>
														<?php else : ?>
															<span style="color:#646970;"><?php esc_html_e('Already Migrated', 'user-manager'); ?></span>
														<?php endif; ?>
													</td>
												</tr>
											<?php endif; ?>
										<?php endforeach; ?>
									</tbody>
								</table>

								<p style="margin-top:20px;">
									<?php submit_button(__('Migrate Selected Coupons', 'user-manager'), 'primary', 'submit', false); ?>
								</p>
							</form>
						<?php else : ?>
							<p><?php esc_html_e('All store credit coupons have been migrated.', 'user-manager'); ?></p>
						<?php endif; ?>
					<?php elseif ($show_migration_table) : ?>
						<p><?php esc_html_e('No store credit coupons found.', 'user-manager'); ?></p>
					<?php else : ?>
						<p style="color:#646970;font-style:italic;"><?php esc_html_e('Check the box above to load the migration table.', 'user-manager'); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($){
			$('#nuc_send_email').on('change', function(){
				$('#nuc-email-template-select').toggle(this.checked);
			});
			
			// Select all functionality for store credit migration (only enabled checkboxes)
			$('#select-all-store-credits').on('change', function(){
				$('.store-credit-checkbox:not(:disabled)').prop('checked', this.checked);
			});
			
			// Update select all when individual checkboxes change (only count enabled checkboxes)
			$('.store-credit-checkbox').on('change', function(){
				var total = $('.store-credit-checkbox:not(:disabled)').length;
				var checked = $('.store-credit-checkbox:not(:disabled):checked').length;
				$('#select-all-store-credits').prop('checked', total > 0 && total === checked);
			});
		});
		</script>
		<?php
	}

	/**
	 * Get all store_credit type coupons that haven't been migrated yet.
	 * 
	 * @param string $sort_by Column to sort by (id, code, amount, usage_count, migrated)
	 * @param string $sort_order Sort order (asc or desc)
	 * @return array
	 */
	private static function get_store_credit_coupons(string $sort_by = 'id', string $sort_order = 'asc'): array {
		if (!class_exists('WooCommerce')) {
			return [];
		}

		// Get coupons that are either:
		// 1. Still store_credit type (not migrated)
		// 2. Have been migrated (have the migration flag, even if now fixed_cart)
		$args = [
			'post_type' => 'shop_coupon',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'meta_query' => [
				'relation' => 'OR',
				[
					'key' => 'discount_type',
					'value' => 'store_credit',
					'compare' => '=',
				],
				[
					'key' => '_um_store_credit_migrated',
					'compare' => 'EXISTS',
				],
			],
		];

		$coupons = get_posts($args);
		$results = [];

		foreach ($coupons as $post) {
			$coupon = new WC_Coupon($post->ID);
			if (!$coupon) {
				continue;
			}
			
			// Check if already migrated
			$migrated = get_post_meta($post->ID, '_um_store_credit_migrated', true);
			
			// If not migrated, it must still be store_credit type
			if (empty($migrated) && $coupon->get_discount_type() !== 'store_credit') {
				continue;
			}

			// Check if already migrated
			$migrated = get_post_meta($post->ID, '_um_store_credit_migrated', true);

			// Get assigned emails from various sources
			$emails = [];
			
			// Check customer_email meta
			$customer_email = get_post_meta($post->ID, 'customer_email', true);
			if (!empty($customer_email)) {
				if (is_array($customer_email)) {
					$emails = array_merge($emails, $customer_email);
				} else {
					$emails[] = $customer_email;
				}
			}
			
			// Check WooCommerce email restrictions
			$email_restrictions = $coupon->get_email_restrictions();
			if (!empty($email_restrictions) && is_array($email_restrictions)) {
				$emails = array_merge($emails, $email_restrictions);
			}
			
			// Check User Manager custom meta
			$um_email = get_post_meta($post->ID, '_um_user_coupon_user_email', true);
			if (!empty($um_email)) {
				$emails[] = $um_email;
			}
			
			// Remove duplicates and empty values
			$emails = array_unique(array_filter($emails));
			$emails_display = !empty($emails) ? implode(', ', $emails) : '';

			$results[] = [
				'id' => $post->ID,
				'code' => $coupon->get_code(),
				'discount_type' => $coupon->get_discount_type(),
				'amount' => (float) $coupon->get_amount(),
				'usage_count' => (int) $coupon->get_usage_count(),
				'usage_limit' => $coupon->get_usage_limit() ? (int) $coupon->get_usage_limit() : null,
				'migrated' => !empty($migrated),
				'emails' => $emails_display,
			];
		}

		// Validate sort column
		$valid_sort_columns = ['id', 'code', 'amount', 'usage_count', 'migrated'];
		if (!in_array($sort_by, $valid_sort_columns, true)) {
			$sort_by = 'id';
		}

		// Sort the results
		usort($results, function($a, $b) use ($sort_by, $sort_order) {
			// Always prioritize non-migrated over migrated (unless sorting by migrated status)
			if ($sort_by !== 'migrated') {
				if ($a['migrated'] !== $b['migrated']) {
					return $a['migrated'] ? 1 : -1;
				}
			}

			$value_a = $a[$sort_by] ?? '';
			$value_b = $b[$sort_by] ?? '';

			// Handle null values for usage_limit
			if ($sort_by === 'usage_limit') {
				if ($value_a === null && $value_b === null) {
					$result = 0;
				} elseif ($value_a === null) {
					$result = 1; // null values go to end
				} elseif ($value_b === null) {
					$result = -1;
				} else {
					$result = $value_a <=> $value_b;
				}
			} else {
				$result = $value_a <=> $value_b;
			}

			return $sort_order === 'desc' ? -$result : $result;
		});

		return $results;
	}
}


