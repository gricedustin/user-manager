<?php
/**
 * Add-on card: Security Hardening.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Security_Hardening {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['security_hardening_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-security-hardening" data-um-active-selectors="#um-security-hardening-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-shield"></span>
				<h2><?php esc_html_e('Security Hardening', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-security-hardening-enabled" name="security_hardening_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Enable optional hardening controls that can reduce common exposure points. Review each option carefully before enabling on production.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-security-hardening-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="security_hardening_block_rest_user_enumeration" value="1" <?php checked(!empty($settings['security_hardening_block_rest_user_enumeration'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Block REST API User Enumeration', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('Helps prevent public discovery of user accounts through default /wp/v2/users endpoints. Risk: third-party tools or integrations that rely on public user endpoints may stop working.', 'user-manager'); ?>
						</p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="security_hardening_disallow_file_edit" value="1" <?php checked(!empty($settings['security_hardening_disallow_file_edit'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Disable Theme/Plugin File Editing in WP-Admin (DISALLOW_FILE_EDIT)', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('Reduces risk of malicious code edits from compromised admin accounts by disabling the built-in editor. Risk: administrators lose dashboard-based file editing and must edit files via SFTP/Git/CLI.', 'user-manager'); ?>
						</p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="security_hardening_disallow_file_mods" value="1" <?php checked(!empty($settings['security_hardening_disallow_file_mods'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Disable Plugin/Theme Install & Update in WP-Admin (DISALLOW_FILE_MODS)', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('Prevents dashboard plugin/theme installs, updates, and deletions to tighten change control. Risk: routine update workflows in wp-admin are blocked; updates must be handled by deployment tooling.', 'user-manager'); ?>
						</p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="security_hardening_force_ssl_admin" value="1" <?php checked(!empty($settings['security_hardening_force_ssl_admin'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Force SSL for WP-Admin and Login (FORCE_SSL_ADMIN)', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('Helps protect login credentials and session cookies by requiring HTTPS in admin/login contexts. Risk: sites without valid SSL or with misconfigured reverse proxies can experience login or redirect issues.', 'user-manager'); ?>
						</p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="security_hardening_hide_wp_version" value="1" <?php checked(!empty($settings['security_hardening_hide_wp_version'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Hide WordPress Version Generator Output', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('Removes version generator output from front-end head and feeds to reduce passive fingerprinting. Risk: some diagnostics, scanners, or tooling that expect this value may no longer detect version automatically.', 'user-manager'); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

