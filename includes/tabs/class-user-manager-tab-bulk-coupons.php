<?php
/**
 * Bulk Coupons tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Bulk_Coupons {

	public static function render(): void {
		$settings  = User_Manager_Core::get_settings();
		$templates = User_Manager_Core::get_email_templates();

		// Last-used values
		$template_code    = $settings['bulk_coupons_template_code'] ?? '';
		$total_to_create  = isset($settings['bulk_coupons_total']) ? (int) $settings['bulk_coupons_total'] : 0;
		$emails_last      = $settings['bulk_coupons_emails'] ?? '';
		$amount_override  = $settings['bulk_coupons_amount'] ?? '';
		$code_prefix      = $settings['bulk_coupons_prefix'] ?? '';
		$code_suffix      = $settings['bulk_coupons_suffix'] ?? '';
		$code_length      = isset($settings['bulk_coupons_length']) ? (int) $settings['bulk_coupons_length'] : 8;
		$expiration_date  = $settings['bulk_coupons_expiration_date'] ?? '';
		$expiration_days  = isset($settings['bulk_coupons_expiration_days']) ? (int) $settings['bulk_coupons_expiration_days'] : 0;
		if ($code_length <= 0) {
			$code_length = 8;
		}
		
		// Detect if our quick-start template coupon already exists.
		$basic_template_code = 'basic_coupon_template_10_off_one_time_use_no_expiration';
		$basic_template_exists = false;
		if (function_exists('wc_get_coupon_id_by_code')) {
			$basic_template_exists = (bool) wc_get_coupon_id_by_code($basic_template_code);
		} else {
			$existing_template_ids = get_posts([
				'post_type'   => 'shop_coupon',
				'post_status' => 'publish',
				'numberposts' => 1,
				'fields'      => 'ids',
				'name'        => $basic_template_code,
			]);
			$basic_template_exists = !empty($existing_template_ids);
		}

		// Recent Bulk Coupons activity (from admin log)
		$activity_data = User_Manager_Core::get_activity_log();
		$entries = $activity_data['entries'] ?? $activity_data;
		if (!is_array($entries)) {
			$entries = [];
		}
		$recent_bulk = array_filter($entries, static function ($entry) {
			return isset($entry['tool'], $entry['action']) && $entry['tool'] === 'Bulk Coupons' && $entry['action'] === 'coupon_created';
		});
		$recent_bulk = array_slice($recent_bulk, 0, 25);
		?>
		<div class="um-admin-grid" style="grid-template-columns: 2fr 1fr;">
			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-tickets-alt"></span>
					<h2><?php esc_html_e('Create Bulk Coupons', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p class="description" style="margin-top:0;">
						<?php esc_html_e('Within your existing coupons, create one initial coupon to use as a template, then use this generator to clone it into many unique codes with optional prefixes, suffixes, and amount overrides.', 'user-manager'); ?>
					</p>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="user_manager_bulk_coupons" />
						<?php wp_nonce_field('user_manager_bulk_coupons'); ?>

						<div class="um-form-field">
							<label for="um-bulk-coupons-template">
								<?php esc_html_e('Copy all data from this existing Coupon as a starting template for each new code', 'user-manager'); ?>
							</label>
							<input type="text" name="bulk_coupons_template_code" id="um-bulk-coupons-template" class="regular-text" list="um-bulk-coupons-template-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or enter coupon code', 'user-manager'); ?>" value="<?php echo esc_attr($template_code); ?>" autocomplete="off" />
							<datalist id="um-bulk-coupons-template-datalist"></datalist>
							<p class="description">
								<?php esc_html_e('All coupon settings (type, restrictions, usage limits, etc.) will be cloned from this coupon unless overridden below.', 'user-manager'); ?>
							</p>
						</div>
						<?php if (!$basic_template_exists) : ?>
							<div class="um-form-field" style="margin-top: 10px; padding: 10px 12px; border: 1px solid #ccd0d4; border-radius: 4px; background: #f6f7f7;">
								<h3 style="margin-top:0;"><?php esc_html_e('Need a Quick Coupon Template?', 'user-manager'); ?></h3>
								<p class="description" style="margin-bottom:8px;">
									<?php esc_html_e('Click this button to create a starter coupon named "basic_coupon_template_10_off_one_time_use_no_expiration" as a fixed cart coupon worth 10.00, one-time use only, with no expiration.', 'user-manager'); ?>
									<br />
									<?php esc_html_e('After it is created, the page will refresh so you can select it above and override any values below as needed.', 'user-manager'); ?>
								</p>
								<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:8px;">
									<input type="hidden" name="action" value="user_manager_create_basic_coupon_template" />
									<?php wp_nonce_field('user_manager_create_basic_coupon_template'); ?>
									<?php submit_button(__('Create Quick Coupon Template', 'user-manager'), 'secondary', 'submit', false); ?>
								</form>
							</div>
						<?php endif; ?>

						<div class="um-admin-grid" style="grid-template-columns: 1fr 1fr;">
							<div>
								<div class="um-form-field">
									<label for="um-bulk-coupons-total">
										<?php esc_html_e('Total Number of Coupons to Generate', 'user-manager'); ?>
									</label>
									<input type="number" min="1" step="1" name="bulk_coupons_total" id="um-bulk-coupons-total" class="regular-text" value="<?php echo esc_attr($total_to_create > 0 ? $total_to_create : ''); ?>" />
									<p class="description">
										<?php esc_html_e('Optional if you provide an email list. When an email list is provided, one coupon will be created per email instead.', 'user-manager'); ?>
									</p>
								</div>
							</div>
							<div>
								<div class="um-form-field">
									<label for="um-bulk-coupons-emails">
										<?php esc_html_e('List of Email Addresses to Create Codes for and auto assign each email to each new code', 'user-manager'); ?>
									</label>
									<textarea name="bulk_coupons_emails" id="um-bulk-coupons-emails" rows="4" class="large-text code" placeholder="<?php esc_attr_e("one@example.com\nanother@example.com\nthird@example.com", 'user-manager'); ?>"><?php echo esc_textarea($emails_last); ?></textarea>
									<p class="description">
										<?php esc_html_e('Optional. One email per line. If supplied, the generator will create one coupon per email and assign that email as a restriction on the coupon.', 'user-manager'); ?>
									</p>
								</div>
							</div>
						</div>

						<div class="um-admin-grid" style="grid-template-columns: 1fr 1fr;">
							<div>
								<div class="um-form-field">
									<label for="um-bulk-coupons-amount">
										<?php esc_html_e('Override Total Value of these New Coupons', 'user-manager'); ?>
									</label>
									<input type="text" name="bulk_coupons_amount" id="um-bulk-coupons-amount" class="regular-text" value="<?php echo esc_attr($amount_override); ?>" placeholder="100" />
									<p class="description">
										<?php esc_html_e('Optional. If set, overrides the template coupon amount for each new code.', 'user-manager'); ?>
									</p>
								</div>
							</div>
							<div>
								<div class="um-form-field">
									<label for="um-bulk-coupons-length">
										<?php esc_html_e('Total Length/Number of Random Characters & Numbers as the Coupon Code Name', 'user-manager'); ?>
									</label>
									<input type="number" min="4" max="64" name="bulk_coupons_length" id="um-bulk-coupons-length" class="regular-text" value="<?php echo esc_attr($code_length); ?>" />
									<p class="description">
										<?php esc_html_e('Length of the random portion of the coupon code. Default 8.', 'user-manager'); ?>
									</p>
								</div>
							</div>
						</div>

						<div class="um-admin-grid" style="grid-template-columns: 1fr 1fr;">
							<div>
								<div class="um-form-field">
									<label for="um-bulk-coupons-expiration-date">
										<?php esc_html_e('Override Expiration Date', 'user-manager'); ?>
									</label>
									<input type="date" name="bulk_coupons_expiration_date" id="um-bulk-coupons-expiration-date" class="regular-text" value="<?php echo esc_attr($expiration_date); ?>" />
									<p class="description">
										<?php esc_html_e('Optional. Sets a specific expiration date for all generated coupons (YYYY-MM-DD).', 'user-manager'); ?>
									</p>
								</div>
							</div>
							<div>
								<div class="um-form-field">
									<label for="um-bulk-coupons-expiration-days">
										<?php esc_html_e('Override Expiration Date based on Number of Days from Today', 'user-manager'); ?>
									</label>
									<input type="number" min="0" step="1" name="bulk_coupons_expiration_days" id="um-bulk-coupons-expiration-days" class="regular-text" value="<?php echo esc_attr($expiration_days > 0 ? $expiration_days : ''); ?>" />
									<p class="description">
										<?php esc_html_e('Optional. If set to a number greater than 0, expiration will be today plus this many days. This takes priority over a specific date above.', 'user-manager'); ?>
									</p>
								</div>
							</div>
						</div>

						<div class="um-admin-grid" style="grid-template-columns: 1fr 1fr;">
							<div>
								<div class="um-form-field">
									<label for="um-bulk-coupons-prefix">
										<?php esc_html_e('Coupon Code Prefix (added before each new coupon code)', 'user-manager'); ?>
									</label>
									<input type="text" name="bulk_coupons_prefix" id="um-bulk-coupons-prefix" class="regular-text" value="<?php echo esc_attr($code_prefix); ?>" placeholder="WELCOME-" />
									<p class="description">
										<?php esc_html_e('Optional. Supports %YEAR% placeholder which will be replaced with the current year.', 'user-manager'); ?>
									</p>
								</div>
							</div>
							<div>
								<div class="um-form-field">
									<label for="um-bulk-coupons-suffix">
										<?php esc_html_e('Coupon Code Suffix (added after each new coupon code)', 'user-manager'); ?>
									</label>
									<input type="text" name="bulk_coupons_suffix" id="um-bulk-coupons-suffix" class="regular-text" value="<?php echo esc_attr($code_suffix); ?>" placeholder="-2025" />
									<p class="description">
										<?php esc_html_e('Optional. Supports %YEAR% placeholder which will be replaced with the current year.', 'user-manager'); ?>
									</p>
								</div>
							</div>
						</div>

						<div class="um-send-email-option" style="margin-top: 12px;">
							<div class="um-form-field-inline">
								<input type="checkbox" name="send_email" id="um-bulk-coupons-send-email" value="1" />
								<label for="um-bulk-coupons-send-email"><strong><?php esc_html_e('Send Coupon Emails to Listed Addresses', 'user-manager'); ?></strong></label>
							</div>

							<div id="um-bulk-coupons-template-select" style="margin-top:12px;display:none;">
								<label for="um-bulk-coupons-template-email"><?php esc_html_e('Select Email Template:', 'user-manager'); ?></label>
								<select name="email_template" id="um-bulk-coupons-template-email" class="regular-text" style="margin-top:6px;">
									<option value=""><?php esc_html_e('— Select Template —', 'user-manager'); ?></option>
									<?php foreach ($templates as $id => $template) : ?>
										<option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($template['title'] ?? $id); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description" style="margin-top:6px;">
									<?php esc_html_e('Emails are only sent when a list of email addresses is provided above. Templates support %COUPONCODE% for the generated code.', 'user-manager'); ?>
								</p>
								<p style="margin-top:8px;">
									<button type="button" class="button um-bulk-coupons-preview-email-btn"><?php esc_html_e('Preview Email (First Address)', 'user-manager'); ?></button>
								</p>
							</div>
						</div>

						<p style="margin-top:20px;">
							<?php submit_button(__('Create All New Codes', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</form>
				</div>
			</div>

			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-clock"></span>
					<h2><?php esc_html_e('Recent Bulk Creates', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body" style="max-height: 320px; overflow-y: auto;">
					<?php if (empty($recent_bulk)) : ?>
						<p class="um-empty-message"><?php esc_html_e('No bulk coupon runs recorded yet.', 'user-manager'); ?></p>
					<?php else : ?>
						<ul class="um-recent-users-list">
							<?php foreach ($recent_bulk as $entry) : ?>
								<?php
								$extra = isset($entry['extra']) && is_array($entry['extra']) ? $entry['extra'] : [];
								$code  = $extra['coupon_code'] ?? '';
								$link  = $extra['coupon_link'] ?? '';
								$email = $extra['assigned_email'] ?? '';
								if (!$code) {
									continue;
								}
								?>
								<li>
									<?php if ($link) : ?>
										<a href="<?php echo esc_url($link); ?>" title="<?php esc_attr_e('Edit coupon', 'user-manager'); ?>">
											<?php echo esc_html($code); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html($code); ?>
									<?php endif; ?>
									<?php if ($email) : ?>
										<span class="description"><?php echo esc_html($email); ?></span>
									<?php endif; ?>
									<span class="um-recent-time">
										<?php echo esc_html(User_Manager_Core::nice_time($entry['created_at'])); ?>
									</span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<script>
		jQuery(function($){
			$('#um-bulk-coupons-send-email').on('change', function() {
				$('#um-bulk-coupons-template-select').toggle(this.checked);
			});
			$('.um-bulk-coupons-preview-email-btn').on('click', function() {
				umShowEmailPreview('bulk-coupons');
			});
		});
		</script>
		<?php User_Manager_Tab_Shared::render_email_preview_modal($templates); ?>
		<?php
	}
}


