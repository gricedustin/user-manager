<?php
/**
 * Add-on card: Staging & Development Environment Overrides.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Staging_Development_Environment_Overrides {

	public static function render(array $settings): void {
		$enabled = !empty($settings['staging_dev_overrides_enabled']);

		$is_checked_default_true = static function (string $key) use ($settings): bool {
			if (!array_key_exists($key, $settings)) {
				return true;
			}
			return !empty($settings[$key]);
		};
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-staging-dev-overrides" data-um-active-selectors="#um-staging-dev-overrides-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-admin-tools"></span>
				<h2><?php esc_html_e('Staging & Development Environment Overrides', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-staging-dev-overrides-enabled" name="staging_dev_overrides_enabled" value="1" <?php checked($enabled); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Apply safety overrides for non-production environments, including email/payment/webhook/API blocking and visible non-production notices.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-staging-dev-overrides-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-admin-card" style="margin:0;">
						<div class="um-admin-card-header">
							<span class="dashicons dashicons-admin-generic"></span>
							<h3 style="margin:0;"><?php esc_html_e('Environment Overrides', 'user-manager'); ?></h3>
						</div>
						<div class="um-admin-card-body">
							<label style="display:block;margin:0 0 10px;">
								<input type="checkbox" name="staging_dev_disable_all_emails" value="1" <?php checked($is_checked_default_true('staging_dev_disable_all_emails')); ?> />
								<?php esc_html_e('Disable All Emails from Sending', 'user-manager'); ?>
							</label>
							<label style="display:block;margin:0 0 10px;">
								<input type="checkbox" name="staging_dev_disable_all_payment_gateways" value="1" <?php checked($is_checked_default_true('staging_dev_disable_all_payment_gateways')); ?> />
								<?php esc_html_e('Disable All Payment Gateways', 'user-manager'); ?>
							</label>
							<label style="display:block;margin:0 0 10px;">
								<input type="checkbox" name="staging_dev_disable_all_webhooks" value="1" <?php checked($is_checked_default_true('staging_dev_disable_all_webhooks')); ?> />
								<?php esc_html_e('Disable All Webhooks', 'user-manager'); ?>
							</label>
							<label style="display:block;margin:0;">
								<input type="checkbox" name="staging_dev_disable_all_api_json_requests" value="1" <?php checked($is_checked_default_true('staging_dev_disable_all_api_json_requests')); ?> />
								<?php esc_html_e('Disable All API & JSON Requests', 'user-manager'); ?>
							</label>
						</div>
					</div>

					<div class="um-admin-card" style="margin-top:14px;">
						<div class="um-admin-card-header">
							<span class="dashicons dashicons-warning"></span>
							<h3 style="margin:0;"><?php esc_html_e('Notices', 'user-manager'); ?></h3>
						</div>
						<div class="um-admin-card-body">
							<label style="display:block;margin:0 0 10px;">
								<input type="checkbox" name="staging_dev_notice_frontend_top_bar" value="1" <?php checked($is_checked_default_true('staging_dev_notice_frontend_top_bar')); ?> />
								<?php esc_html_e('Show Non-Production Notice in Front End Top Bar', 'user-manager'); ?>
							</label>
							<label style="display:block;margin:0 0 10px;">
								<input type="checkbox" name="staging_dev_notice_wp_admin" value="1" <?php checked($is_checked_default_true('staging_dev_notice_wp_admin')); ?> />
								<?php esc_html_e('Show Non-Production Notice in WP-Admin', 'user-manager'); ?>
							</label>
							<label style="display:block;margin:0;">
								<input type="checkbox" name="staging_dev_notice_include_data_anonymized" value="1" <?php checked($is_checked_default_true('staging_dev_notice_include_data_anonymized')); ?> />
								<?php esc_html_e('Include Data Anonymized Note at end of Notices above with Timestamp', 'user-manager'); ?>
							</label>
							<p class="description" style="margin:8px 0 0;">
								<?php esc_html_e('The data anonymized note is based on the latest run in the Data Anonymizer History.', 'user-manager'); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

