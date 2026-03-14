<?php
/**
 * Add-on card: Front-End URL Parameter Debugger.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Frontend_URL_Parameter_Debugger {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['frontend_url_param_debugger_enabled']);
		$param_name = isset($settings['frontend_url_param_debugger_param']) ? sanitize_key((string) $settings['frontend_url_param_debugger_param']) : 'um_url_debug';
		if ($param_name === '') {
			$param_name = 'um_url_debug';
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-frontend-url-parameter-debugger" data-um-active-selectors="#um-frontend-url-parameter-debugger-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-search"></span>
				<h2><?php esc_html_e('Front-End URL Parameter Debugger', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-frontend-url-parameter-debugger-enabled" name="frontend_url_param_debugger_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Help users debug front-end query parameters with an admin-only URL parameter panel.', 'user-manager'); ?>
					</p>
				</div>
				<div id="um-frontend-url-parameter-debugger-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-frontend-url-parameter-debugger-param"><?php esc_html_e('Debug trigger URL parameter', 'user-manager'); ?></label>
						<input type="text" id="um-frontend-url-parameter-debugger-param" name="frontend_url_param_debugger_param" class="regular-text" value="<?php echo esc_attr($param_name); ?>"<?php echo $form_attr; ?> />
						<p class="description">
							<?php esc_html_e('When this query parameter is present (for example value "1"), the debugger panel is shown to logged-in administrators on the front end.', 'user-manager'); ?>
						</p>
						<input type="text" readonly class="large-text code" value="<?php echo esc_attr('?' . $param_name . '=1'); ?>" onclick="this.select();" />
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

