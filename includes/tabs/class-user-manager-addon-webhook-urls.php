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
							<?php esc_html_e('Supports create_order and edit_order webhooks with billing/shipping fields, product IDs, customer note, and optional order status updates.', 'user-manager'); ?>
						</p>
						<p class="description">
							<?php
							printf(
								/* translators: %s: sample URL */
								esc_html__('Sample: %s?webhook=create_order&first_name=Jane&last_name=Doe&email_address=jane@example.com&product_ids=123,456', 'user-manager'),
								esc_html($site)
							);
							?>
						</p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_user_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_user_webhook'])); ?> />
							<?php esc_html_e('Activate New User/Edit User Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Placeholder handler for create_user and edit_user webhooks.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_post_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_post_webhook'])); ?> />
							<?php esc_html_e('Activate New Post/Edit Post Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Placeholder handler for create_post and edit_post webhooks.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_coupon_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_coupon_webhook'])); ?> />
							<?php esc_html_e('Activate New Coupon/Edit Coupon Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Supports create_coupon and edit_coupon with WooCommerce coupon fields such as amount, usage limits, restrictions, categories, and expiry.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_product_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_product_webhook'])); ?> />
							<?php esc_html_e('Activate New Product/Edit Product Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Placeholder handler for create_product and edit_product webhooks.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_product_cat_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_product_cat_webhook'])); ?> />
							<?php esc_html_e('Activate New Product Category/Edit Product Category Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Placeholder handler for create_product_cat and edit_product_cat webhooks.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_user_password_reset_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_user_password_reset_webhook'])); ?> />
							<?php esc_html_e('Activate User Password Reset Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Supports reset_password with user_id or email plus new_password.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="webhook_urls_activate_send_email_webhook" value="1" <?php checked(!empty($settings['webhook_urls_activate_send_email_webhook'])); ?> />
							<?php esc_html_e('Activate Send Email Webhook', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Supports send_email with to, subject, message, optional from/bcc headers, and HTML mode.', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

