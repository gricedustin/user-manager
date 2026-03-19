<?php
/**
 * Add-on card: Send Email.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('User_Manager_Tab_Email_Users')) {
	require_once __DIR__ . '/class-user-manager-tab-email-users.php';
}
if (!class_exists('User_Manager_Tab_Email_Templates')) {
	require_once __DIR__ . '/class-user-manager-tab-email-templates.php';
}

class User_Manager_Addon_Send_Email {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['send_email_users_enabled']);

		$templates_open = isset($_GET['edit_template']) && sanitize_key(wp_unslash($_GET['edit_template'])) !== '';
		$templates_toggle_label = $templates_open ? __('Collapse', 'user-manager') : __('Expand', 'user-manager');
		$templates_base_url = add_query_arg(
			'addon_section',
			'send-email-users',
			User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS)
		);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-send-email" data-um-active-selectors="#um-send-email-users-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-email-alt"></span>
				<h2><?php esc_html_e('Send Email', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-send-email-users-enabled" name="send_email_users_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Send bulk emails to selected users/lists with templates, previews, batching, and saved custom lists.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-send-email-users-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-admin-card" id="um-send-email-templates-card" style="margin-bottom:20px;">
						<div class="um-admin-card-header">
							<span class="dashicons dashicons-email"></span>
							<h2><?php esc_html_e('Email Templates', 'user-manager'); ?></h2>
							<button type="button" class="button button-small" id="um-toggle-send-email-templates-card" aria-expanded="<?php echo $templates_open ? 'true' : 'false'; ?>">
								<?php echo esc_html($templates_toggle_label); ?>
							</button>
						</div>
						<div class="um-admin-card-body" id="um-send-email-templates-card-body" style="<?php echo $templates_open ? '' : 'display:none;'; ?>">
							<?php User_Manager_Tab_Email_Templates::render($templates_base_url, 'addon-send-email-users'); ?>
						</div>
					</div>

					<div class="um-admin-grid" style="margin-bottom:20px;">
						<div class="um-admin-card">
							<div class="um-admin-card-header">
								<span class="dashicons dashicons-download"></span>
								<h2><?php esc_html_e('Import Demo Email Templates', 'user-manager'); ?></h2>
							</div>
							<div class="um-admin-card-body">
								<p><?php esc_html_e('Import pre-configured email templates to get started quickly. This will add 4 commonly used templates:', 'user-manager'); ?></p>
								<ul style="list-style: disc; margin-left: 20px; margin-bottom: 16px;">
									<li><strong><?php esc_html_e('Send login information', 'user-manager'); ?></strong> — <?php esc_html_e('Send my account link, username and clear text password', 'user-manager'); ?></li>
									<li><strong><?php esc_html_e('Activate your new account', 'user-manager'); ?></strong> — <?php esc_html_e('Send new users a link to the website with a temporary password and a link to change their password in their account', 'user-manager'); ?></li>
									<li><strong><?php esc_html_e('Send new password', 'user-manager'); ?></strong> — <?php esc_html_e('Sends updated login credentials with clear text password after a password change', 'user-manager'); ?></li>
									<li><strong><?php esc_html_e('Force password reset', 'user-manager'); ?></strong> — <?php esc_html_e('Send a password reset link for users to reset their own password', 'user-manager'); ?></li>
								</ul>
								<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
									<input type="hidden" name="action" value="user_manager_import_demo_templates" />
									<input type="hidden" name="templates_context" value="addon-send-email-users" />
									<?php wp_nonce_field('user_manager_import_demo_templates'); ?>
									<?php submit_button(__('Import Demo Templates', 'user-manager'), 'primary', 'submit', false); ?>
								</form>
							</div>
						</div>

						<div class="um-admin-card um-admin-card-full">
							<div class="um-admin-card-header">
								<span class="dashicons dashicons-email"></span>
								<h2><?php esc_html_e('Import Automated Coupon Email', 'user-manager'); ?></h2>
							</div>
							<div class="um-admin-card-body">
								<p><?php esc_html_e('Import automated coupon email templates used by the New User Coupons and Bulk Coupons features.', 'user-manager'); ?></p>
								<ul style="list-style: disc; margin-left: 20px; margin-bottom: 16px;">
									<li><strong><?php esc_html_e('Send automated coupon', 'user-manager'); ?></strong> — <?php esc_html_e('Configured in Settings to trigger automated discounts & store credits for new users. Supports %COUPONCODE%.', 'user-manager'); ?></li>
									<li><strong><?php esc_html_e('Send $10 coupon apology', 'user-manager'); ?></strong> — <?php esc_html_e('Use when sending a one-time $10 apology coupon that includes the %COUPONCODE% placeholder.', 'user-manager'); ?></li>
								</ul>
								<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
									<input type="hidden" name="action" value="user_manager_import_coupon_template" />
									<input type="hidden" name="templates_context" value="addon-send-email-users" />
									<?php wp_nonce_field('user_manager_import_coupon_template'); ?>
									<?php submit_button(__('Import Automated Coupon Email', 'user-manager'), 'secondary', 'submit', false); ?>
								</form>
							</div>
						</div>
					</div>

					<?php User_Manager_Tab_Email_Users::render($templates_base_url); ?>
				</div>
			</div>
		</div>
		<script>
		jQuery(function($) {
			var $toggleBtn = $('#um-toggle-send-email-templates-card');
			var $body = $('#um-send-email-templates-card-body');
			if (!$toggleBtn.length || !$body.length) {
				return;
			}
			$toggleBtn.on('click', function() {
				var expanded = $toggleBtn.attr('aria-expanded') === 'true';
				var nextExpanded = !expanded;
				$toggleBtn.attr('aria-expanded', nextExpanded ? 'true' : 'false');
				$toggleBtn.text(nextExpanded ? '<?php echo esc_js(__('Collapse', 'user-manager')); ?>' : '<?php echo esc_js(__('Expand', 'user-manager')); ?>');
				$body.stop(true, true).slideToggle(150);
			});
		});
		</script>
		<?php
	}
}

