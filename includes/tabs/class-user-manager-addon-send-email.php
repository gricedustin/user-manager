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

