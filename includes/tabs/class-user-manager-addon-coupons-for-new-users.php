<?php
/**
 * Add-on card: Coupons for New Users.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Coupons_For_New_Users {

	public static function render(array $settings): void {
		$templates = User_Manager_Core::get_email_templates();
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-coupons-new-users" data-um-active-selectors="#um-nuc-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-tickets-alt"></span>
				<h2><?php esc_html_e('Coupon for New User', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="nuc_enabled" id="um-nuc-enabled" value="1" <?php checked($settings['nuc_enabled'] ?? false); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Create a user-specific coupon cloned from a template and optionally email it to the user.', 'user-manager'); ?></p>
					<p class="description"><?php esc_html_e('Coupons are not bulk generated; each user is evaluated when they log in and visit a page you enabled below.', 'user-manager'); ?></p>
				</div>

				<div id="um-nuc-fields" style="<?php echo !empty($settings['nuc_enabled']) ? '' : 'display:none;'; ?>">
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
							$all_coupons = get_posts([
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
								<option value=""><?php esc_html_e('- Select Coupon -', 'user-manager'); ?></option>
								<?php foreach ($all_coupons as $coupon_id) : ?>
									<?php
									$code = get_the_title($coupon_id);
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
							<option value=""><?php esc_html_e('- Select Template -', 'user-manager'); ?></option>
							<?php foreach ($templates as $id => $template) : ?>
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
				</div>
			</div>
		</div>
		<?php
		User_Manager_Tab_Shared::render_email_preview_modal($templates);
	}
}

