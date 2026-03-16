<?php
/**
 * Add-on card: Fatal Error Debugger.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Fatal_Error_Debugger {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['fatal_error_debugger_enabled']);
		$email = isset($settings['fatal_error_debugger_email']) ? sanitize_email((string) $settings['fatal_error_debugger_email']) : '';
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-fatal-error-debugger" data-um-active-selectors="#um-fatal-error-debugger-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-warning"></span>
				<h2><?php esc_html_e('Fatal Error Debugger', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-fatal-error-debugger-enabled" name="fatal_error_debugger_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Capture front-end PHP fatal errors and show an admin-only debug panel with the latest error details.', 'user-manager'); ?>
					</p>
				</div>
				<div id="um-fatal-error-debugger-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-fatal-error-debugger-email"><?php esc_html_e('Sent Email To Address upon Fatal Errors', 'user-manager'); ?></label>
						<input type="email" id="um-fatal-error-debugger-email" name="fatal_error_debugger_email" class="regular-text" value="<?php echo esc_attr($email); ?>" placeholder="admin@example.com"<?php echo $form_attr; ?> />
						<p class="description">
							<?php esc_html_e('If an email address is entered, an email alert is sent when a fatal error is captured. If left blank, no email is sent.', 'user-manager'); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

