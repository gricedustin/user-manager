<?php
/**
 * Add-on card: Webhook URLs.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Webhook_URLs {

	public static function render(array $settings): void {
		$enabled = !empty($settings['webhook_urls_enabled']);
		$site = trailingslashit(site_url('/'));
		$sample_create_order_url = $site . '?webhook=create_order&first_name=Jane&last_name=Doe&company=Acme%20Inc&phone=15551234567&address=123%20Main%20St&address_2=Suite%20200&city=Minneapolis&state=MN&postal_code=55401&country=US&email_address=jane@example.com&product_ids=123,456&order_note=Please%20call%20on%20arrival&order_status=pending';
		$sample_edit_order_url = $site . '?webhook=edit_order&order_id=1234&first_name=Janet&last_name=Doe&company=Acme%20Updated&phone=15557654321&address=500%20Broadway%20Ave&address_2=Floor%203&city=Saint%20Paul&state=MN&postal_code=55101&country=US&email_address=janet@example.com&product_ids=789,456&order_note=Updated%20by%20webhook&order_status=processing';
		$sample_create_user_url = $site . '?webhook=create_user';
		$sample_edit_user_url = $site . '?webhook=edit_user';
		$sample_create_post_url = $site . '?webhook=create_post';
		$sample_edit_post_url = $site . '?webhook=edit_post';
		$sample_create_coupon_url = $site . '?webhook=create_coupon&code=WELCOME25&discount_type=percent&amount=25&expiry_date=2026-12-31&individual_use=1&usage_limit=500&usage_limit_per_user=1&minimum_amount=50&maximum_amount=500&free_shipping=0&email_restrictions=jane@example.com,john@example.com&product_ids=123,456&exclude_product_ids=789,790&product_categories=10,11&exclude_product_categories=15,16&description=Welcome%20campaign%20coupon';
		$sample_edit_coupon_url = $site . '?webhook=edit_coupon&coupon_id=321&code=WELCOME25&discount_type=fixed_cart&amount=30&expiry_date=2027-01-31&individual_use=0&usage_limit=800&usage_limit_per_user=2&minimum_amount=75&maximum_amount=750&free_shipping=1&email_restrictions=jane@example.com,john@example.com&product_ids=123,456,777&exclude_product_ids=789,790&product_categories=10,11,12&exclude_product_categories=15,16&description=Updated%20coupon%20rules';
		$sample_create_product_url = $site . '?webhook=create_product';
		$sample_edit_product_url = $site . '?webhook=edit_product';
		$sample_create_product_cat_url = $site . '?webhook=create_product_cat';
		$sample_edit_product_cat_url = $site . '?webhook=edit_product_cat';
		$sample_reset_password_url = $site . '?webhook=reset_password&user_id=123&email=jane@example.com&new_password=TempPass!234';
		$sample_send_email_url = $site . '?webhook=send_email&to=jane@example.com,john@example.com&subject=Webhook%20Test%20Subject&message=%3Cp%3EThis%20is%20a%20test%20message.%3C%2Fp%3E&from_email=notifications@example.com&from_name=Store%20Notifications&bcc=manager@example.com,audit@example.com&html=1';
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-webhook-urls" data-um-active-selectors="#um-webhook-urls-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-rss"></span>
				<h2><?php esc_html_e('Webhook URLs', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-webhook-urls-enabled" name="webhook_urls_enabled" value="1" <?php checked($enabled); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Expose lightweight webhook endpoints for create/edit workflows (orders, coupons, password resets, and email sending) using POST data and optional URL params.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-webhook-urls-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label class="um-label-block"><?php esc_html_e('Webhook Base URL', 'user-manager'); ?></label>
						<input type="text" readonly class="large-text code" value="<?php echo esc_attr($site); ?>" onclick="this.select();" />
						<p class="description"><?php esc_html_e('Use this base URL with webhook query args, e.g. ?webhook=create_order', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_debug_mode" value="1" <?php checked(!empty($settings['webhook_urls_debug_mode'])); ?> />
							<?php esc_html_e('Debug Mode', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Include execution/debug trace data in webhook JSON responses to help troubleshoot payload and handler issues.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_allow_url_params" value="1" <?php checked(!empty($settings['webhook_urls_allow_url_params'])); ?> />
							<?php esc_html_e('Allow URL Parameters', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Allow GET query values as input fallback for testing. POST remains the recommended production method.', 'user-manager'); ?></p>
					</div>

					<hr style="margin:16px 0;" />
					<h3 style="margin:0 0 8px;"><?php esc_html_e('Webhook Types', 'user-manager'); ?></h3>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_order_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_order_webhook'])); ?> />
							<?php esc_html_e('Activate New Order/Edit Order Webhook', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('Supports create_order and edit_order. Required create fields: first_name, last_name, email_address, product_ids. Optional fields for create/edit: company, phone, address, address_2, city, state, postal_code, country, order_note, order_status. edit_order also requires order_id. Product IDs should be comma-separated.', 'user-manager'); ?>
						</p>
						<p class="description"><strong><?php esc_html_e('Create sample (all fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_create_order_url); ?></code></p>
						<p class="description"><strong><?php esc_html_e('Edit sample (all fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_edit_order_url); ?></code></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_user_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_user_webhook'])); ?> />
							<?php esc_html_e('Activate New User/Edit User Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Current status: placeholder handler for create_user and edit_user. At this time no additional payload fields are parsed beyond webhook.', 'user-manager'); ?></p>
						<p class="description"><strong><?php esc_html_e('Create sample (all currently supported fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_create_user_url); ?></code></p>
						<p class="description"><strong><?php esc_html_e('Edit sample (all currently supported fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_edit_user_url); ?></code></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_post_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_post_webhook'])); ?> />
							<?php esc_html_e('Activate New Post/Edit Post Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Current status: placeholder handler for create_post and edit_post. At this time no additional payload fields are parsed beyond webhook.', 'user-manager'); ?></p>
						<p class="description"><strong><?php esc_html_e('Create sample (all currently supported fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_create_post_url); ?></code></p>
						<p class="description"><strong><?php esc_html_e('Edit sample (all currently supported fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_edit_post_url); ?></code></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_coupon_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_coupon_webhook'])); ?> />
							<?php esc_html_e('Activate New Coupon/Edit Coupon Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Supports create_coupon and edit_coupon. create_coupon requires code. edit_coupon requires coupon_id or code to locate the coupon. Optional fields for create/edit: discount_type (percent|fixed_cart|fixed_product), amount, expiry_date, individual_use, usage_limit, usage_limit_per_user, minimum_amount, maximum_amount, free_shipping, email_restrictions, product_ids, exclude_product_ids, product_categories, exclude_product_categories, description.', 'user-manager'); ?></p>
						<p class="description"><strong><?php esc_html_e('Create sample (all fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_create_coupon_url); ?></code></p>
						<p class="description"><strong><?php esc_html_e('Edit sample (all fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_edit_coupon_url); ?></code></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_product_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_product_webhook'])); ?> />
							<?php esc_html_e('Activate New Product/Edit Product Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Current status: placeholder handler for create_product and edit_product. At this time no additional payload fields are parsed beyond webhook.', 'user-manager'); ?></p>
						<p class="description"><strong><?php esc_html_e('Create sample (all currently supported fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_create_product_url); ?></code></p>
						<p class="description"><strong><?php esc_html_e('Edit sample (all currently supported fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_edit_product_url); ?></code></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_product_cat_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_product_cat_webhook'])); ?> />
							<?php esc_html_e('Activate New Product Category/Edit Product Category Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Current status: placeholder handler for create_product_cat and edit_product_cat. At this time no additional payload fields are parsed beyond webhook.', 'user-manager'); ?></p>
						<p class="description"><strong><?php esc_html_e('Create sample (all currently supported fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_create_product_cat_url); ?></code></p>
						<p class="description"><strong><?php esc_html_e('Edit sample (all currently supported fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_edit_product_cat_url); ?></code></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_user_password_reset_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_user_password_reset_webhook'])); ?> />
							<?php esc_html_e('Activate User Password Reset Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Supports reset_password. Required field: new_password. Identification fields: user_id or email (either works; if both are present user_id is used first).', 'user-manager'); ?></p>
						<p class="description"><strong><?php esc_html_e('Reset sample (all fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_reset_password_url); ?></code></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_send_email_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_send_email_webhook'])); ?> />
							<?php esc_html_e('Activate Send Email Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Supports send_email. Required fields: to, subject, message. Optional fields: from_email, from_name, bcc, html (1=HTML, 0=plain text). Recipients in to/bcc should be comma-separated.', 'user-manager'); ?></p>
						<p class="description"><strong><?php esc_html_e('Send sample (all fields):', 'user-manager'); ?></strong><br><code style="word-break:break-all;"><?php echo esc_html($sample_send_email_url); ?></code></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

