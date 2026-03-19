<?php
/**
 * Settings tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

	class User_Manager_Tab_Settings {

	public static function render(): void {
		$base_url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_SETTINGS);
		$requested_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : User_Manager_Core::TAB_SETTINGS;
		$settings_section = isset($_GET['settings_section']) ? sanitize_key(wp_unslash($_GET['settings_section'])) : '';
		$valid_sections = ['general', 'tools'];

		// Email/SMS template managers were moved into their add-ons.
		// Preserve old URLs by redirecting to the relevant add-on card.
		if ($settings_section === 'email-templates' || $requested_tab === User_Manager_Core::TAB_EMAIL_TEMPLATES) {
			wp_safe_redirect(add_query_arg('addon_section', 'send-email-users', User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS)));
			exit;
		}
		if ($settings_section === 'sms-text-templates') {
			wp_safe_redirect(add_query_arg('addon_section', 'send-sms-text', User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS)));
			exit;
		}

		// Backward compatibility: legacy tabs now live under Settings sub links.
		if ($settings_section === '') {
			if ($requested_tab === User_Manager_Core::TAB_TOOLS) {
				$settings_section = 'tools';
			}
		}
		if (!in_array($settings_section, $valid_sections, true)) {
			$settings_section = 'general';
		}

		$general_url = add_query_arg('settings_section', 'general', $base_url);
		$tools_url = add_query_arg('settings_section', 'tools', $base_url);

		?>
		<ul class="subsubsub" style="margin: 12px 0 14px;">
			<li>
				<a href="<?php echo esc_url($general_url); ?>" class="<?php echo $settings_section === 'general' ? 'current' : ''; ?>">
					<?php esc_html_e('General Settings', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($tools_url); ?>" class="<?php echo $settings_section === 'tools' ? 'current' : ''; ?>">
					<?php esc_html_e('Tools', 'user-manager'); ?>
				</a>
			</li>
		</ul>
		<br class="clear" />
		<?php

		if ($settings_section === 'tools') {
			User_Manager_Tab_Tools::render(true, false, false);
			return;
		}

		$settings = User_Manager_Core::get_settings();
		?>
		<div class="um-admin-card um-admin-card-full" style="margin-top: 20px;">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-filter"></span>
				<h2><?php esc_html_e('Settings Filter', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
					<div style="min-width:280px; flex:1;">
						<label for="um-settings-filter-text"><strong><?php esc_html_e('Keyword filter', 'user-manager'); ?></strong></label>
						<input type="text" id="um-settings-filter-text" class="regular-text" style="width:100%; max-width:560px;" placeholder="<?php esc_attr_e('Type to filter settings by label, description, or value...', 'user-manager'); ?>" />
					</div>
					<div>
						<button type="button" class="button" id="um-settings-filter-clear"><?php esc_html_e('Clear Filter', 'user-manager'); ?></button>
					</div>
				</div>
				<p class="description" id="um-settings-filter-empty" style="display:none; margin-top: 10px;">
					<?php esc_html_e('No settings match the current filter.', 'user-manager'); ?>
				</p>
			</div>
		</div>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="user_manager_save_settings" />
			<input type="hidden" name="settings_section" value="general" />
			<?php wp_nonce_field('user_manager_save_settings'); ?>
			<div class="um-admin-grid um-admin-grid-single">
				<!-- Email Settings -->
				<div class="um-admin-card um-settings-filter-card um-settings-collapsible-card" data-card="email-settings" data-title="<?php echo esc_attr__('Email Settings', 'user-manager'); ?>">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-email"></span>
						<h2><?php esc_html_e('Email Settings', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label for="um-send-from-name"><?php esc_html_e('Send From Name', 'user-manager'); ?></label>
							<input type="text" name="send_from_name" id="um-send-from-name" class="regular-text" value="<?php echo esc_attr($settings['send_from_name'] ?? ''); ?>" />
							<p class="description"><?php esc_html_e('The name that appears in the "From" field of emails sent by User Manager. Leave empty to use WordPress default.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-send-from-email"><?php esc_html_e('Send From Email Address', 'user-manager'); ?></label>
							<input type="email" name="send_from_email" id="um-send-from-email" class="regular-text" value="<?php echo esc_attr($settings['send_from_email'] ?? ''); ?>" />
							<p class="description"><?php esc_html_e('The email address that appears in the "From" field of emails sent by User Manager. Leave empty to use WordPress default.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-reply-to-email"><?php esc_html_e('Reply To Email Address', 'user-manager'); ?></label>
							<input type="email" name="reply_to_email" id="um-reply-to-email" class="regular-text" value="<?php echo esc_attr($settings['reply_to_email'] ?? ''); ?>" />
							<p class="description"><?php esc_html_e('The email address that appears in the "Reply-To" field of emails sent by User Manager. Leave empty to use the From address.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="throttle_emails_enabled" id="um-throttle-emails-enabled" value="1" <?php checked($settings['throttle_emails_enabled'] ?? false); ?> />
								<?php esc_html_e('Throttle Sending Emails to X Emails/Texts Per Page Load', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When enabled, only the specified number of emails/texts will be sent per page load to avoid provider limits and spam filters. Remaining items are queued for the next batch.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field" id="um-throttle-emails-count-field" style="<?php echo empty($settings['throttle_emails_enabled']) ? 'display:none;' : ''; ?>">
							<label for="um-throttle-emails-count"><?php esc_html_e('Texts Per Batch', 'user-manager'); ?></label>
							<input type="number" name="throttle_emails_count" id="um-throttle-emails-count" class="small-text" min="1" value="<?php echo esc_attr($settings['throttle_emails_count'] ?? 50); ?>" />
							<p class="description"><?php esc_html_e('Number used per batch for both Email Users and Send SMS Texts. After sending a batch, a button appears to send the next batch.', 'user-manager'); ?></p>
						</div>
					</div>
				</div>

				<!-- Activity & Logging -->
				<div class="um-admin-card um-settings-filter-card um-settings-collapsible-card" data-card="activity-logging" data-title="<?php echo esc_attr__('Activity & Logging', 'user-manager'); ?>">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-list-view"></span>
						<h2><?php esc_html_e('Activity & Logging', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="log_activity" value="1" <?php checked($settings['log_activity'] ?? true); ?> />
								<?php esc_html_e('Enable Activity Logging', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Log all user creation and password reset actions.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="enable_view_reports" value="1" <?php checked($settings['enable_view_reports'] ?? true); ?> />
								<?php esc_html_e('Enable View/404/Search Reports', 'user-manager'); ?>
							</label>
							<p class="description">
								<?php esc_html_e('Controls logging for Page Views, Product Views, Page Not Found 404 Errors, and Search Queries reports. Disable to stop collecting new entries while keeping existing data.', 'user-manager'); ?>
							</p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="log_activity_debug" value="1" <?php checked($settings['log_activity_debug'] ?? false); ?> />
								<?php esc_html_e('Log activity debug messages to error_log', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Helps troubleshoot why log entries are missing. Disable on production systems.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="log_admin_activity" value="1" <?php checked($settings['log_admin_activity'] ?? true); ?> />
								<?php esc_html_e('Log WP-Admin Activity', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Log wp-admin logins and post type creation/editing activities in the Admin Log tab.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="role_change_alert_enabled" id="um-role-change-alert-enabled" value="1" <?php checked($settings['role_change_alert_enabled'] ?? false); ?> />
								<?php esc_html_e('Admin alert if any user with these roles changes to another role', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When enabled, an email is sent to the address below whenever a user whose previous role (from User Activity / Login History) was one of the selected roles has a different role after login (e.g. after SSO updates their role).', 'user-manager'); ?></p>
						</div>
						<div id="um-role-change-alert-fields" style="<?php echo empty($settings['role_change_alert_enabled']) ? 'display:none;' : ''; ?>">
							<div class="um-form-field">
								<label class="um-label-block"><?php esc_html_e('Monitor these roles (alert when a user with one of these roles changes to any other role)', 'user-manager'); ?></label>
								<div class="um-checkbox-list" style="border: 1px solid #c3c4c7; padding: 10px 12px; background: #fff; border-radius: 4px;">
									<?php
									$alert_roles = isset($settings['role_change_alert_roles']) && is_array($settings['role_change_alert_roles']) ? $settings['role_change_alert_roles'] : [];
									foreach (User_Manager_Core::get_user_roles() as $role_key => $role_name) :
										?>
										<label style="display: block; margin-bottom: 6px;">
											<input type="checkbox" name="role_change_alert_roles[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $alert_roles, true)); ?> />
											<?php echo esc_html($role_name); ?> (<code><?php echo esc_html($role_key); ?></code>)
										</label>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="um-form-field">
								<label for="um-role-change-alert-email"><?php esc_html_e('Admin email address for role change alerts', 'user-manager'); ?></label>
								<input type="email" name="role_change_alert_email" id="um-role-change-alert-email" class="regular-text" value="<?php echo esc_attr($settings['role_change_alert_email'] ?? ''); ?>" placeholder="admin@example.com" />
								<p class="description"><?php esc_html_e('Alerts are sent once per role change when the user\'s previous role (from the last User Activity record) was one of the monitored roles.', 'user-manager'); ?></p>
							</div>
						</div>
					</div>
				</div>

				<!-- User Experience -->
				<div class="um-admin-card um-settings-filter-card um-settings-collapsible-card" data-card="user-experience" data-title="<?php echo esc_attr__('User Experience', 'user-manager'); ?>">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-admin-appearance"></span>
						<h2><?php esc_html_e('User Experience', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="rebrand_reset_password_copy" value="1" <?php checked($settings['rebrand_reset_password_copy'] ?? false); ?> />
								<?php esc_html_e('Rebrand Reset Password Copy to Set Password Copy', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Change the reset password copy to set password intended for new users who should set a password when first logging into the site so it doesn\'t look like they are "resetting" for the first time. Also removes the username from the password changed email.', 'user-manager'); ?></p>
						</div>
					</div>
				</div>

				<!-- Coupons -->
				<div class="um-admin-card um-settings-filter-card um-settings-collapsible-card" data-card="coupons" data-title="<?php echo esc_attr__('Coupons', 'user-manager'); ?>">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-tickets-alt"></span>
						<h2><?php esc_html_e('Coupons', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_email_converter" value="1" <?php checked($settings['coupon_email_converter'] ?? false); ?> />
								<?php esc_html_e('Enable Coupon Email List Converter meta box', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Adds a meta box on coupon edit screens to convert a one-per-line email list into a comma-separated list for Allowed emails.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_show_email_column" value="1" <?php checked($settings['coupon_show_email_column'] ?? false); ?> />
								<?php esc_html_e('Show Emails Assigned To Column in Coupons Admin Screen', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Adds an "Assigned Emails" column to WooCommerce coupons so you can see restricted email addresses without opening each coupon.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="coupon_code_url_param_enabled" value="1" <?php checked($settings['coupon_code_url_param_enabled'] ?? false); ?> />
								<?php esc_html_e('Apply Coupon Code via URL Parameter', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When enabled, visiting any page with the URL parameter below (e.g. ?coupon-code=SAVE10) will apply that coupon to the cart.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-coupon-code-url-param"><?php esc_html_e('URL parameter name', 'user-manager'); ?></label>
							<input type="text" name="coupon_code_url_param_name" id="um-coupon-code-url-param" class="regular-text" value="<?php echo esc_attr($settings['coupon_code_url_param_name'] ?? 'coupon-code'); ?>" placeholder="coupon-code" />
							<p class="description"><?php esc_html_e('Default is coupon-code. Use only letters, numbers, hyphens, and underscores. Example: ?coupon-code=SAVE10', 'user-manager'); ?></p>
						</div>
					</div>
				</div>

				<!-- User Creation & Import -->
				<div class="um-admin-card um-settings-filter-card um-settings-collapsible-card" data-card="user-import" data-title="<?php echo esc_attr__('User Creation & Import', 'user-manager'); ?>">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-upload"></span>
						<h2><?php esc_html_e('User Creation & Import', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label for="um-default-role"><?php esc_html_e('Default User Role', 'user-manager'); ?></label>
							<select name="default_role" id="um-default-role" class="regular-text">
								<?php foreach (User_Manager_Core::get_user_roles() as $key => $name) : ?>
									<option value="<?php echo esc_attr($key); ?>" <?php selected($settings['default_role'] ?? 'customer', $key); ?>><?php echo esc_html($name); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="um-form-field">
							<label for="um-default-login-url"><?php esc_html_e('Default Login URL', 'user-manager'); ?></label>
							<select name="default_login_url" id="um-default-login-url" class="regular-text">
								<option value="/my-account/" <?php selected($settings['default_login_url'] ?? '/my-account/', '/my-account/'); ?>><?php esc_html_e('My Account', 'user-manager'); ?></option>
								<option value="/wp-admin/" <?php selected($settings['default_login_url'] ?? '', '/wp-admin/'); ?>><?php esc_html_e('WP Admin', 'user-manager'); ?></option>
								<option value="/wp-login.php?saml_sso=false" <?php selected($settings['default_login_url'] ?? '', '/wp-login.php?saml_sso=false'); ?>><?php esc_html_e('WP Admin SSO Bypass', 'user-manager'); ?></option>
							</select>
						</div>
						<div class="um-form-field">
							<label for="um-paste-default-columns"><?php esc_html_e('Paste from Spreadsheet: default column order', 'user-manager'); ?></label>
							<input type="text" name="paste_default_columns" id="um-paste-default-columns" class="large-text code" value="<?php echo esc_attr($settings['paste_default_columns'] ?? 'email,first_name,last_name,role,username,password'); ?>" placeholder="email,first_name,last_name,role,username,password" />
							<p class="description"><?php esc_html_e('When pasted data has no header row, columns are interpreted in this order (comma-separated). The first column should be email. You can add or remove columns (e.g. coupon_email_append). Used by the Paste from Spreadsheet tool on the Bulk Create tab.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="update_existing_users" value="1" <?php checked($settings['update_existing_users'] ?? false); ?> />
								<?php esc_html_e('When creating a new user, if the user already exists, update all details', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Instead of skipping existing users, update their first name, last name, role, and password.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<?php
							$add_existing_network_user_to_subsite_enabled = array_key_exists('add_existing_network_user_to_subsite', $settings)
								? !empty($settings['add_existing_network_user_to_subsite'])
								: true;
							?>
							<label>
								<input type="checkbox" name="add_existing_network_user_to_subsite" value="1" <?php checked($add_existing_network_user_to_subsite_enabled); ?> />
								<?php esc_html_e('If a user already exists in a multisite network, but does not already exist in this sub-site, add them to the sub-site', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('Applies to Create User, Bulk Create, and SFTP import. Default is enabled.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<?php
							$deactivate_users_reset_password_enabled = array_key_exists('deactivate_users_reset_password', $settings)
								? !empty($settings['deactivate_users_reset_password'])
								: true;
							?>
							<label>
								<input type="checkbox" name="deactivate_users_reset_password" value="1" <?php checked($deactivate_users_reset_password_enabled); ?> />
								<?php esc_html_e('Deactivate User(s): quiet-reset passwords to random strings', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When enabled, deactivating users will silently reset their passwords in the background without sending emails.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<?php
							$deactivate_users_prefix_identity_enabled = array_key_exists('deactivate_users_prefix_identity', $settings)
								? !empty($settings['deactivate_users_prefix_identity'])
								: true;
							?>
							<label>
								<input type="checkbox" name="deactivate_users_prefix_identity" value="1" <?php checked($deactivate_users_prefix_identity_enabled); ?> />
								<?php esc_html_e('Deactivate User(s): prefix login/email with [YYYYMMDD]-deactivated-', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('When enabled, deactivating users renames both login and email to a date-prefixed deactivated format to prevent normal credential reuse.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-sftp-directories"><?php esc_html_e('SFTP/Directory Paths for CSV Import', 'user-manager'); ?></label>
							<textarea name="sftp_directories" id="um-sftp-directories" rows="5" class="large-text code" placeholder="<?php echo esc_attr("/home/username/imports/\n/var/www/html/wp-content/uploads/user-imports/\n" . WP_CONTENT_DIR . "/uploads/user-imports/"); ?>"><?php echo esc_textarea($settings['sftp_directories'] ?? ''); ?></textarea>
							<p class="description"><?php esc_html_e('Enter one directory path per line. These directories will be monitored for CSV files in the Bulk Create tool.', 'user-manager'); ?></p>
							<p class="description"><strong><?php esc_html_e('Example paths:', 'user-manager'); ?></strong></p>
							<ul class="description" style="margin: 5px 0 0 20px; list-style: disc;">
								<li><code><?php echo esc_html(WP_CONTENT_DIR . '/uploads/user-imports/'); ?></code> — <?php esc_html_e('WordPress uploads folder', 'user-manager'); ?></li>
								<li><code>/home/username/imports/</code> — <?php esc_html_e('Custom SFTP directory', 'user-manager'); ?></li>
								<li><code><?php echo esc_html(ABSPATH . 'imports/'); ?></code> — <?php esc_html_e('Site root folder', 'user-manager'); ?></li>
							</ul>
						</div>
					</div>
				</div>

				<!-- API Keys -->
				<div class="um-admin-card um-settings-filter-card um-settings-collapsible-card" data-card="api-keys" data-title="<?php echo esc_attr__('API Keys', 'user-manager'); ?>">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-admin-network"></span>
						<h2><?php esc_html_e('API Keys', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<div class="um-form-field">
							<label for="um-openai-api-key"><?php esc_html_e('ChatGPT / OpenAI API Key', 'user-manager'); ?></label>
							<input type="password" name="openai_api_key" id="um-openai-api-key" class="regular-text" value="<?php echo esc_attr($settings['openai_api_key'] ?? ''); ?>" autocomplete="off" />
							<p class="description"><?php esc_html_e('Used for ChatGPT-powered content tools. Leave empty to disable ChatGPT requests. Get an API key from platform.openai.com.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-simple-texting-api-token"><?php esc_html_e('Simple Texting API Token', 'user-manager'); ?></label>
							<input type="password" name="simple_texting_api_token" id="um-simple-texting-api-token" class="regular-text" value="<?php echo esc_attr($settings['simple_texting_api_token'] ?? ''); ?>" autocomplete="off" />
							<p class="description"><?php esc_html_e('Used by the Send SMS Text add-on to send SMS messages through SimpleTexting. Store your bearer API token here.', 'user-manager'); ?></p>
						</div>
					</div>
				</div>

				<!-- Save button -->
				<div class="um-admin-card um-admin-card-full um-settings-save-card">
					<div class="um-admin-card-body">
						<p style="margin:0;">
							<?php submit_button(__('Save Settings', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</div>
				</div>
			</div>
		</form>
		<style>
		.um-settings-collapsible-card .um-admin-card-header {
			cursor: pointer;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.um-settings-collapse-indicator {
			margin-left: auto;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 20px;
			height: 20px;
			border: 1px solid #c3c4c7;
			border-radius: 999px;
			background: #fff;
			font-weight: 600;
			line-height: 1;
		}
		</style>
		<script>
		jQuery(document).ready(function($) {
			function sortSettingsCards() {
				var $grid = $('.um-admin-grid.um-admin-grid-single').first();
				if (!$grid.length) {
					return;
				}
				var $saveCard = $grid.children('.um-settings-save-card').first();
				if (!$saveCard.length) {
					return;
				}

				var $apiCard = $grid.children('.um-settings-filter-card[data-card="api-keys"]').first();
				var sortableCards = $grid.children('.um-settings-filter-card').not($apiCard).get();

				sortableCards.sort(function(a, b) {
					var aTitle = (($(a).data('title') || '') + '').toLowerCase();
					var bTitle = (($(b).data('title') || '') + '').toLowerCase();
					return aTitle.localeCompare(bTitle);
				});

				$(sortableCards).each(function() {
					$(this).insertBefore($saveCard);
				});

				if ($apiCard.length) {
					$apiCard.insertBefore($saveCard);
				}
			}
			sortSettingsCards();

			// Toggle throttle emails count field
			function toggleThrottleEmailsCount() {
				if ($('#um-throttle-emails-enabled').is(':checked')) {
					$('#um-throttle-emails-count-field').show();
				} else {
					$('#um-throttle-emails-count-field').hide();
				}
			}
			$('#um-throttle-emails-enabled').on('change', toggleThrottleEmailsCount);
			toggleThrottleEmailsCount();

			function toggleRoleChangeAlertFields() {
				if ($('#um-role-change-alert-enabled').is(':checked')) {
					$('#um-role-change-alert-fields').show();
				} else {
					$('#um-role-change-alert-fields').hide();
				}
			}
			$('#um-role-change-alert-enabled').on('change', toggleRoleChangeAlertFields);
			toggleRoleChangeAlertFields();

			function normalizeFilterText(str) {
				return (str || '').toString().toLowerCase().trim();
			}

			function settingFieldText($field) {
				var text = [];
				text.push($field.text());
				$field.find('input, select, textarea').each(function() {
					var $input = $(this);
					text.push($input.val());
					text.push($input.attr('placeholder'));
					text.push($input.attr('name'));
				});
				return normalizeFilterText(text.join(' '));
			}

			function isSettingsFilterActive() {
				var keyword = normalizeFilterText($('#um-settings-filter-text').val());
				return keyword !== '';
			}

			function setSettingsCardCollapsed($card, collapsed, skipAnimation) {
				var $body = $card.children('.um-admin-card-body').first();
				if (!$body.length) {
					return;
				}
				$card.attr('data-um-collapsed', collapsed ? '1' : '0');
				$card.find('.um-settings-collapse-indicator').first().text(collapsed ? '+' : '−');
				if (skipAnimation) {
					$body.toggle(!collapsed);
				} else if (collapsed) {
					$body.stop(true, true).slideUp(120);
				} else {
					$body.stop(true, true).slideDown(120);
				}
			}

			function initSettingsCardCollapse() {
				$('.um-settings-collapsible-card .um-admin-card-header').each(function() {
					var $header = $(this);
					$header.attr('role', 'button').attr('tabindex', '0');
					if (!$header.find('.um-settings-collapse-indicator').length) {
						$header.append('<span class="um-settings-collapse-indicator" aria-hidden="true">+</span>');
					}
				});

				$('.um-settings-collapsible-card').each(function() {
					setSettingsCardCollapsed($(this), true, true);
				});

				$('.um-settings-collapsible-card .um-admin-card-header').on('click keydown', function(e) {
					if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') {
						return;
					}
					if (isSettingsFilterActive()) {
						return;
					}
					e.preventDefault();
					var $card = $(this).closest('.um-settings-collapsible-card');
					var collapsed = $card.attr('data-um-collapsed') === '1';
					setSettingsCardCollapsed($card, !collapsed, false);
				});
			}

			function applySettingsFilter() {
				var keyword = normalizeFilterText($('#um-settings-filter-text').val());
				var filterActive = isSettingsFilterActive();
				var anyVisible = false;

				$('.um-settings-filter-card').each(function() {
					var $card = $(this);
					var cardTitle = normalizeFilterText($card.data('title'));
					var hasKeywordMatch = false;

					if (keyword === '') {
						$card.find('.um-form-field').show();
						hasKeywordMatch = true;
					} else {
						$card.find('.um-form-field').each(function() {
							var $field = $(this);
							var matched = settingFieldText($field).indexOf(keyword) !== -1;
							$field.toggle(matched);
							if (matched) {
								hasKeywordMatch = true;
							}
						});
					}

					// If keyword only matches the card title, show all fields in that card.
					if (keyword !== '' && !hasKeywordMatch && cardTitle.indexOf(keyword) !== -1) {
						$card.find('.um-form-field').show();
						hasKeywordMatch = true;
					}

					var showCard = (keyword === '' || hasKeywordMatch);
					$card.toggle(showCard);
					if (showCard) {
						setSettingsCardCollapsed($card, !filterActive, true);
					}
					if (showCard) {
						anyVisible = true;
					}
				});

				$('#um-settings-filter-empty').toggle(!anyVisible);
			}

			$('#um-settings-filter-text').on('input', applySettingsFilter);
			$('#um-settings-filter-clear').on('click', function() {
				$('#um-settings-filter-text').val('');
				applySettingsFilter();
			});
			initSettingsCardCollapse();
			applySettingsFilter();
		});
		</script>
		<?php
	}
}
