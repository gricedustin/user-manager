<?php
/**
 * Change Role tab renderer.
 *
 * Admin workflow for reassigning one or more existing users to a new role
 * in bulk. Modeled after the Reset Password sub-page so admins have a
 * consistent Login Tools experience: one textarea of email addresses, a
 * role picker, an optional "Send Role Change Email" checkbox, and a
 * recent-activity sidebar sourced from the Admin Activity Log.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Change_Role {

	/**
	 * Render the Change Role(s) screen.
	 */
	public static function render(): void {
		$activity = User_Manager_Core::get_activity_log();

		// Normalize activity log structure.
		$entries = $activity['entries'] ?? $activity;
		if (!is_array($entries)) {
			$entries = [];
		}

		// Filter to only entries from the Change Role tool so the "Recent
		// Changes" sidebar does not mix in Reset Password / Remove User
		// history.
		$recent_changes = array_filter($entries, static function ($entry) {
			return is_array($entry)
				&& isset($entry['tool'])
				&& $entry['tool'] === 'Change Role';
		});
		$recent_changes = array_slice($recent_changes, 0, 15);

		$roles = User_Manager_Core::get_user_roles();
		$prefill_emails = isset($_GET['um_email']) ? sanitize_textarea_field(wp_unslash($_GET['um_email'])) : '';
		?>
		<div class="um-create-user-layout">
			<div class="um-create-user-form">
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-admin-users"></span>
						<h2><?php esc_html_e('Change Role(s)', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-change-role-form">
							<input type="hidden" name="action" value="user_manager_change_role" />
							<?php wp_nonce_field('user_manager_change_role'); ?>

							<div class="um-form-field">
								<label for="um-change-role-emails"><?php esc_html_e('Email Addresses', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<textarea name="emails" id="um-change-role-emails" class="large-text" rows="4" required placeholder="<?php esc_attr_e("user1@example.com\nuser2@example.com", 'user-manager'); ?>"><?php echo esc_textarea($prefill_emails); ?></textarea>
								<p class="description"><?php esc_html_e('Enter one email address per line. All matched users will be switched to the selected role.', 'user-manager'); ?></p>
							</div>

							<div class="um-form-field">
								<label for="um-change-role-role"><?php esc_html_e('Select a Role to assign all the users to', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<select name="new_role" id="um-change-role-role" class="regular-text" required>
									<option value=""><?php esc_html_e('— Select Role —', 'user-manager'); ?></option>
									<?php foreach ($roles as $role_key => $role_name) : ?>
										<option value="<?php echo esc_attr($role_key); ?>"><?php echo esc_html($role_name); ?> (<code><?php echo esc_html($role_key); ?></code>)</option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e('The new role replaces the user\'s existing role(s) entirely. WordPress will fire its standard set_user_role action so other plugins that listen for role changes still run.', 'user-manager'); ?></p>
							</div>

							<div class="um-send-email-option">
								<div class="um-form-field-inline">
									<input type="checkbox" name="send_email" id="um-change-role-send-email" value="1" />
									<label for="um-change-role-send-email"><strong><?php esc_html_e('Send Role Change Email', 'user-manager'); ?></strong></label>
								</div>
								<p class="description" style="margin-top:6px;">
									<strong style="color:#d63638;"><?php esc_html_e('Not recommended in most cases.', 'user-manager'); ?></strong>
									<?php esc_html_e('Role changes are normally best done silently. Many users are confused by an unexpected "Your role has changed" email, and the message can reveal internal role labels or account-admin actions that the end user does not need to see. Only enable this when you have a specific customer-facing reason to notify them.', 'user-manager'); ?>
								</p>
							</div>

							<div class="um-info-box">
								<span class="dashicons dashicons-info"></span>
								<div>
									<strong><?php esc_html_e('Audit trail', 'user-manager'); ?></strong>
									<p><?php esc_html_e('Every successful and failed role change is written to the Admin Activity Log with the old role(s), the new role, the triggering admin, and whether a notification email was sent. Open Reports > Admin Log to review the history.', 'user-manager'); ?></p>
								</div>
							</div>

							<p style="margin-top:20px;">
								<?php submit_button(__('Change Role', 'user-manager'), 'primary', 'submit', false); ?>
							</p>
						</form>
					</div>
				</div>
			</div>

			<div class="um-create-user-recent">
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-clock"></span>
						<h2><?php esc_html_e('Recent Role Changes', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<?php if (empty($recent_changes)) : ?>
							<p class="um-empty-message"><?php esc_html_e('No role changes yet.', 'user-manager'); ?></p>
						<?php else : ?>
							<ul class="um-recent-users-list">
								<?php foreach ($recent_changes as $entry) :
									$user = isset($entry['user_id']) ? get_user_by('ID', (int) $entry['user_id']) : false;
									if (!$user) {
										continue;
									}
									$extra = self::decode_log_extra($entry['extra'] ?? []);
									$new_role = isset($extra['new_role']) ? (string) $extra['new_role'] : '';
									$summary = $new_role !== ''
										? sprintf('%s → %s', $user->user_email, $new_role)
										: (string) $user->user_email;
									?>
									<li>
										<a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>" title="<?php esc_attr_e('Edit user', 'user-manager'); ?>">
											<?php echo esc_html($summary); ?>
										</a>
										<span class="um-recent-time"><?php echo esc_html(User_Manager_Core::nice_time($entry['created_at'] ?? 0)); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<style>
		.um-create-user-layout {
			display: grid;
			grid-template-columns: 1fr 350px;
			gap: 20px;
			margin-top: 20px;
		}
		@media (max-width: 1100px) {
			.um-create-user-layout {
				grid-template-columns: 1fr;
			}
		}
		.um-recent-users-list {
			list-style: none;
			margin: 0;
			padding: 0;
		}
		.um-recent-users-list li {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 8px 0;
			border-bottom: 1px solid #f0f0f1;
		}
		.um-recent-users-list li:last-child {
			border-bottom: none;
		}
		.um-recent-users-list a {
			color: #2271b1;
			text-decoration: none;
			font-size: 13px;
			white-space: normal;
			word-break: break-word;
		}
		.um-recent-users-list a:hover {
			text-decoration: underline;
		}
		.um-recent-time {
			color: #646970;
			font-size: 12px;
			flex-shrink: 0;
		}
		.um-empty-message {
			color: #646970;
			font-style: italic;
			margin: 0;
		}
		</style>
		<?php
	}

	/**
	 * Normalize `extra` payloads coming back from the Admin Activity Log,
	 * which may be stored as a JSON string or pre-decoded array depending
	 * on the source.
	 *
	 * @param mixed $raw
	 * @return array<string,mixed>
	 */
	private static function decode_log_extra($raw): array {
		if (is_array($raw)) {
			return $raw;
		}
		if (is_string($raw) && $raw !== '') {
			$decoded = json_decode($raw, true);
			if (is_array($decoded)) {
				return $decoded;
			}
		}
		return [];
	}
}
