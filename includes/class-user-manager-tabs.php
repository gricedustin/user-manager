<?php
/**
 * User Manager Tabs dispatcher.
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/tabs/class-user-manager-tab-shared.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-create-user.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-login-history.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-reset-password.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-remove-user.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-deactivate-user.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-role-switching.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-login-as.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-bulk-create.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-email-users.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-activity-log.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-email-templates.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-sms-text-templates.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-coupons.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-bulk-coupons.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-tools.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-settings.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-addons.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-reports.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-documentation.php';
require_once __DIR__ . '/tabs/class-user-manager-tab-versions.php';

class User_Manager_Tabs {

	/**
	 * Render the appropriate tab content.
	 */
	public static function render_tab(string $tab): void {
		switch ($tab) {
			case User_Manager_Core::TAB_LOGIN_TOOLS:
				self::render_tab(User_Manager_Core::get_current_login_tools_tab());
				break;
			case User_Manager_Core::TAB_CREATE_USER:
				User_Manager_Tab_Create_User::render();
				break;
			case User_Manager_Core::TAB_LOGIN_HISTORY:
				User_Manager_Tab_Login_History::render();
				break;
			case User_Manager_Core::TAB_RESET_PASSWORD:
				User_Manager_Tab_Reset_Password::render();
				break;
			case User_Manager_Core::TAB_REMOVE_USER:
				User_Manager_Tab_Remove_User::render();
				break;
			case User_Manager_Core::TAB_DEACTIVATE_USER:
				User_Manager_Tab_Deactivate_User::render();
				break;
			case User_Manager_Core::TAB_ROLE_SWITCHING:
				User_Manager_Tab_Role_Switching::render();
				break;
			case User_Manager_Core::TAB_LOGIN_AS:
				User_Manager_Tab_Login_As::render();
				break;
			case User_Manager_Core::TAB_BULK_CREATE:
				User_Manager_Tab_Bulk_Create::render();
				break;
			case User_Manager_Core::TAB_EMAIL_USERS:
				$settings = User_Manager_Core::get_settings();
				if (!User_Manager_Core::is_send_email_addon_enabled($settings)) {
					$addons_url = add_query_arg('addon_section', 'send-email-users', User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS));
					?>
					<div class="um-admin-card um-admin-card-full" style="margin-top:20px;">
						<div class="um-admin-card-header">
							<span class="dashicons dashicons-email-alt"></span>
							<h2><?php esc_html_e('Send Email Add-on is Disabled', 'user-manager'); ?></h2>
						</div>
						<div class="um-admin-card-body">
							<p><?php esc_html_e('Enable the Send Email add-on in Add-ons to access this screen.', 'user-manager'); ?></p>
							<p>
								<a href="<?php echo esc_url($addons_url); ?>" class="button button-primary"><?php esc_html_e('Open Send Email Add-on', 'user-manager'); ?></a>
							</p>
						</div>
					</div>
					<?php
					break;
				}
				User_Manager_Tab_Email_Users::render();
				break;
			case User_Manager_Core::TAB_ACTIVITY_LOG:
				User_Manager_Tab_Activity_Log::render();
				break;
			case User_Manager_Core::TAB_EMAIL_TEMPLATES:
				User_Manager_Tab_Email_Templates::render();
				break;
			case User_Manager_Core::TAB_COUPONS:
				User_Manager_Tab_Coupons::render();
				break;
			case User_Manager_Core::TAB_BULK_COUPONS:
				User_Manager_Tab_Bulk_Coupons::render();
				break;
			case User_Manager_Core::TAB_TOOLS:
				User_Manager_Tab_Tools::render();
				break;
			case User_Manager_Core::TAB_SETTINGS:
				User_Manager_Tab_Settings::render();
				break;
			case User_Manager_Core::TAB_ADDONS:
				User_Manager_Tab_Addons::render();
				break;
			case User_Manager_Core::TAB_REPORTS:
				User_Manager_Tab_Reports::render();
				break;
			case User_Manager_Core::TAB_DOCUMENTATION:
				User_Manager_Tab_Documentation::render();
				break;
			case User_Manager_Core::TAB_VERSIONS:
				User_Manager_Tab_Versions::render();
				break;
		}
	}
}


