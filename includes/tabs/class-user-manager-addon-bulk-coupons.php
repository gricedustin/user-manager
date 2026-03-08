<?php
/**
 * Add-on card: Bulk Coupons.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Bulk_Coupons {

	public static function render(array $settings): void {
		$templates = User_Manager_Core::get_email_templates();

		$template_code   = $settings['bulk_coupons_template_code'] ?? '';
		$total_to_create = isset($settings['bulk_coupons_total']) ? (int) $settings['bulk_coupons_total'] : 0;
		$emails_last     = $settings['bulk_coupons_emails'] ?? '';
		$amount_override = $settings['bulk_coupons_amount'] ?? '';
		$code_prefix     = $settings['bulk_coupons_prefix'] ?? '';
		$code_suffix     = $settings['bulk_coupons_suffix'] ?? '';
		$code_length     = isset($settings['bulk_coupons_length']) ? (int) $settings['bulk_coupons_length'] : 8;
		$expiration_date = $settings['bulk_coupons_expiration_date'] ?? '';
		$expiration_days = isset($settings['bulk_coupons_expiration_days']) ? (int) $settings['bulk_coupons_expiration_days'] : 0;
		$is_enabled      = !empty($settings['bulk_coupons_enabled']);

		if ($code_length <= 0) {
			$code_length = 8;
		}

		$basic_template_code   = 'basic_coupon_template_10_off_one_time_use_no_expiration';
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

		$activity_data = User_Manager_Core::get_activity_log();
		$entries       = $activity_data['entries'] ?? $activity_data;
		if (!is_array($entries)) {
			$entries = [];
		}
		$recent_bulk = array_filter($entries, static function ($entry) {
			return isset($entry['tool'], $entry['action']) && $entry['tool'] === 'Bulk Coupons' && $entry['action'] === 'coupon_created';
		});
		$recent_bulk = array_slice($recent_bulk, 0, 25);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-bulk-coupons" data-um-active-selectors="#um-bulk-coupons-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-tickets-alt"></span>
				<h2><?php esc_html_e('Coupon Creator', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="bulk_coupons_enabled" id="um-bulk-coupons-enabled" value="1" <?php checked($is_enabled); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Enable bulk coupon generation inside Add-ons.', 'user-manager'); ?></p>
				</div>

				<div id="um-bulk-coupons-fields" style="<?php echo $is_enabled ? '' : 'display:none;'; ?>">
					<?php wp_nonce_field('user_manager_bulk_coupons', 'user_manager_bulk_coupons_nonce', false); ?>
					<?php wp_nonce_field('user_manager_create_basic_coupon_template', 'user_manager_create_basic_coupon_template_nonce', false); ?>

					<p class="description" style="margin-top:0;">
						<?php esc_html_e('Create one template coupon, then clone it into many unique codes with optional prefixes, suffixes, amount overrides, expiration rules, and optional email sending.', 'user-manager'); ?>
					</p>

					<div class="um-form-field">
						<label for="um-bulk-coupons-template">
							<?php esc_html_e('Copy all data from this existing Coupon as a starting template for each new code', 'user-manager'); ?>
						</label>
						<input type="text" name="bulk_coupons_template_code" id="um-bulk-coupons-template" class="regular-text" list="um-bulk-coupons-template-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or enter coupon code', 'user-manager'); ?>" value="<?php echo esc_attr($template_code); ?>" autocomplete="off" />
						<datalist id="um-bulk-coupons-template-datalist"></datalist>
						<p class="description">
							<?php esc_html_e('All coupon settings (type, restrictions, usage limits, etc.) are cloned from this coupon unless overridden below.', 'user-manager'); ?>
						</p>
					</div>

					<?php if (!$basic_template_exists) : ?>
						<div class="um-form-field" style="margin-top:10px;padding:10px 12px;border:1px solid #ccd0d4;border-radius:4px;background:#f6f7f7;">
							<h3 style="margin-top:0;"><?php esc_html_e('Need a Quick Coupon Template?', 'user-manager'); ?></h3>
							<p class="description" style="margin-bottom:8px;">
								<?php esc_html_e('Creates "basic_coupon_template_10_off_one_time_use_no_expiration" (fixed cart 10.00, one-time use, no expiration).', 'user-manager'); ?>
							</p>
							<p style="margin:8px 0 0;">
								<button type="submit" class="button um-addon-action-submit" data-um-target-action="user_manager_create_basic_coupon_template">
									<?php esc_html_e('Create Quick Coupon Template', 'user-manager'); ?>
								</button>
							</p>
						</div>
					<?php endif; ?>

					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-bulk-coupons-total"><?php esc_html_e('Total Number of Coupons to Generate', 'user-manager'); ?></label>
							<input type="number" min="1" step="1" name="bulk_coupons_total" id="um-bulk-coupons-total" class="regular-text" value="<?php echo esc_attr($total_to_create > 0 ? $total_to_create : ''); ?>" />
							<p class="description"><?php esc_html_e('Optional if you provide an email list. With emails provided, one coupon is created per email.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-bulk-coupons-emails"><?php esc_html_e('Email addresses (one per line) for one-per-user coupon assignment', 'user-manager'); ?></label>
							<textarea name="bulk_coupons_emails" id="um-bulk-coupons-emails" rows="4" class="large-text code" placeholder="<?php esc_attr_e("one@example.com\nanother@example.com", 'user-manager'); ?>"><?php echo esc_textarea($emails_last); ?></textarea>
							<p class="description"><?php esc_html_e('Optional. Creates one coupon per email and assigns email restriction on each coupon.', 'user-manager'); ?></p>
						</div>
					</div>

					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-bulk-coupons-amount"><?php esc_html_e('Override Total Value of these New Coupons', 'user-manager'); ?></label>
							<input type="text" name="bulk_coupons_amount" id="um-bulk-coupons-amount" class="regular-text" value="<?php echo esc_attr($amount_override); ?>" placeholder="100" />
							<p class="description"><?php esc_html_e('Optional amount override for each generated code.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-bulk-coupons-length"><?php esc_html_e('Random Code Length', 'user-manager'); ?></label>
							<input type="number" min="4" max="64" name="bulk_coupons_length" id="um-bulk-coupons-length" class="regular-text" value="<?php echo esc_attr($code_length); ?>" />
							<p class="description"><?php esc_html_e('Length of random portion. Default 8.', 'user-manager'); ?></p>
						</div>
					</div>

					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-bulk-coupons-expiration-date"><?php esc_html_e('Override Expiration Date', 'user-manager'); ?></label>
							<input type="date" name="bulk_coupons_expiration_date" id="um-bulk-coupons-expiration-date" class="regular-text" value="<?php echo esc_attr($expiration_date); ?>" />
						</div>
						<div class="um-form-field">
							<label for="um-bulk-coupons-expiration-days"><?php esc_html_e('Override Expiration by Days from Today', 'user-manager'); ?></label>
							<input type="number" min="0" step="1" name="bulk_coupons_expiration_days" id="um-bulk-coupons-expiration-days" class="regular-text" value="<?php echo esc_attr($expiration_days > 0 ? $expiration_days : ''); ?>" />
						</div>
					</div>

					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-bulk-coupons-prefix"><?php esc_html_e('Coupon Code Prefix', 'user-manager'); ?></label>
							<input type="text" name="bulk_coupons_prefix" id="um-bulk-coupons-prefix" class="regular-text" value="<?php echo esc_attr($code_prefix); ?>" placeholder="WELCOME-" />
							<p class="description"><?php esc_html_e('Optional. Supports %YEAR%.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-bulk-coupons-suffix"><?php esc_html_e('Coupon Code Suffix', 'user-manager'); ?></label>
							<input type="text" name="bulk_coupons_suffix" id="um-bulk-coupons-suffix" class="regular-text" value="<?php echo esc_attr($code_suffix); ?>" placeholder="-2025" />
							<p class="description"><?php esc_html_e('Optional. Supports %YEAR%.', 'user-manager'); ?></p>
						</div>
					</div>

					<div class="um-send-email-option" style="margin-top:12px;">
						<div class="um-form-field-inline">
							<input type="checkbox" name="send_email" id="um-bulk-coupons-send-email" value="1" <?php checked(!empty($settings['bulk_coupons_send_email'])); ?> />
							<label for="um-bulk-coupons-send-email"><strong><?php esc_html_e('Send coupon emails to listed addresses', 'user-manager'); ?></strong></label>
						</div>
						<div id="um-bulk-coupons-template-select" style="margin-top:12px;<?php echo !empty($settings['bulk_coupons_send_email']) ? '' : 'display:none;'; ?>">
							<label for="um-bulk-coupons-template-email"><?php esc_html_e('Select Email Template:', 'user-manager'); ?></label>
							<select name="email_template" id="um-bulk-coupons-template-email" class="regular-text" style="margin-top:6px;">
								<option value=""><?php esc_html_e('- Select Template -', 'user-manager'); ?></option>
								<?php foreach ($templates as $id => $template) : ?>
									<option value="<?php echo esc_attr($id); ?>" <?php selected(($settings['bulk_coupons_email_template'] ?? ''), $id); ?>><?php echo esc_html($template['title'] ?? $id); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description" style="margin-top:6px;"><?php esc_html_e('Templates support %COUPONCODE% for the generated code.', 'user-manager'); ?></p>
							<p style="margin-top:8px;">
								<button type="button" class="button um-bulk-coupons-preview-email-btn"><?php esc_html_e('Preview Email (First Address)', 'user-manager'); ?></button>
							</p>
						</div>
					</div>

					<p style="margin-top:20px;">
						<button type="submit" class="button button-primary um-addon-action-submit" id="um-bulk-coupons-create-btn" data-um-target-action="user_manager_bulk_coupons">
							<?php esc_html_e('Create All New Codes', 'user-manager'); ?>
						</button>
					</p>

					<hr style="margin:18px 0;" />
					<h3 style="margin:0 0 8px;"><?php esc_html_e('Recent Bulk Creates', 'user-manager'); ?></h3>
					<div style="max-height:320px;overflow-y:auto;">
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
											<a href="<?php echo esc_url($link); ?>" title="<?php esc_attr_e('Edit coupon', 'user-manager'); ?>"><?php echo esc_html($code); ?></a>
										<?php else : ?>
											<?php echo esc_html($code); ?>
										<?php endif; ?>
										<?php if ($email) : ?>
											<span class="description"><?php echo esc_html($email); ?></span>
										<?php endif; ?>
										<span class="um-recent-time"><?php echo esc_html(User_Manager_Core::nice_time($entry['created_at'])); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

