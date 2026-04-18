<?php
/**
 * Remove User tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Remove_User {

	public static function render(): void {
		$activity = User_Manager_Core::get_activity_log();

		// Normalize activity log structure.
		$entries = $activity['entries'] ?? $activity;
		if (!is_array($entries)) {
			$entries = [];
		}

		$prefill_emails_raw = [];
		if (isset($_GET['um_prefill_user_email'])) {
			$raw_value = (string) wp_unslash($_GET['um_prefill_user_email']);
			$raw_value = rawurldecode($raw_value);
			$tokens = preg_split('/[\r\n,]+/', $raw_value);
			if (is_array($tokens)) {
				foreach ($tokens as $token) {
					$token = trim((string) $token);
					if ($token === '') {
						continue;
					}
					$sanitized = sanitize_email($token);
					if ($sanitized !== '' && is_email($sanitized)) {
						$prefill_emails_raw[] = $sanitized;
					}
				}
			}
		}
		if (empty($prefill_emails_raw) && isset($_GET['um_prefill_user'])) {
			$prefill_user_id = absint(wp_unslash($_GET['um_prefill_user']));
			if ($prefill_user_id > 0) {
				$prefill_user = get_user_by('id', $prefill_user_id);
				if ($prefill_user instanceof WP_User && !empty($prefill_user->user_email) && is_email($prefill_user->user_email)) {
					$prefill_emails_raw[] = $prefill_user->user_email;
				}
			}
		}
		$prefill_emails_text = implode("\n", array_values(array_unique($prefill_emails_raw)));

		// Filter to only entries from Remove User tool.
		$recent_deletions = array_filter($entries, static function ($entry) {
			return is_array($entry)
				&& isset($entry['tool'], $entry['action'])
				&& $entry['tool'] === 'Remove User'
				&& in_array($entry['action'], ['user_removed', 'user_deleted'], true);
		});
		$recent_deletions = array_slice($recent_deletions, 0, 15);
		?>
		<div class="um-create-user-layout">
			<div class="um-create-user-form">
				<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-trash"></span>
					<h2><?php esc_html_e('Remove User', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-remove-user-form">
						<input type="hidden" name="action" value="user_manager_remove_user" />
						<?php wp_nonce_field('user_manager_remove_user'); ?>
						
						<div class="um-form-field">
							<label for="um-remove-emails"><?php esc_html_e('Email Addresses', 'user-manager'); ?> <span style="color:red;">*</span></label>
							<textarea name="emails" id="um-remove-emails" class="large-text" rows="4" required placeholder="<?php esc_attr_e("user1@example.com\nuser2@example.com", 'user-manager'); ?>"><?php echo esc_textarea($prefill_emails_text); ?></textarea>
							<p class="description"><?php esc_html_e('Enter one email address per line. Multiple emails will be removed from the environment.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-info-box">
							<span class="dashicons dashicons-info"></span>
							<div>
								<strong><?php esc_html_e('Important:', 'user-manager'); ?></strong>
								<p><?php 
								if (is_multisite()) {
									esc_html_e('On multisite networks, users will be removed from this site only, not from the entire network.', 'user-manager');
								} else {
									esc_html_e('Users will be permanently deleted from the environment. This action cannot be undone.', 'user-manager');
								}
								?></p>
							</div>
						</div>
						
						<?php if (is_multisite()) : ?>
						<div class="um-form-field" style="margin-top:15px;">
							<label>
								<input type="checkbox" name="delete_from_network_if_no_other_sites" id="um-delete-from-network" value="1" />
								<?php esc_html_e('Delete from network if user does not exist in any other sub sites', 'user-manager'); ?>
							</label>
							<p class="description"><?php esc_html_e('If checked, after removing the user from this site, the system will check if the user exists on any other sites in the network. If not found on any other sites, the user will be permanently deleted from the entire network.', 'user-manager'); ?></p>
						</div>
						<?php endif; ?>
						
						<p style="margin-top:20px;">
							<?php submit_button(__('Remove User(s)', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</form>
				</div>
			</div>
			</div>
			
			<div class="um-create-user-recent">
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-clock"></span>
						<h2><?php esc_html_e('Recently Deleted', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<?php if (empty($recent_deletions)) : ?>
							<p class="um-empty-message"><?php esc_html_e('No users removed yet.', 'user-manager'); ?></p>
						<?php else : ?>
							<ul class="um-recent-users-list">
								<?php foreach ($recent_deletions as $entry) : ?>
									<?php 
									$user_email = '';
									if (!empty($entry['user_id']) && $entry['user_id'] > 0) {
										$user = get_user_by('ID', $entry['user_id']);
										if ($user) {
											$user_email = $user->user_email;
										}
									}
									if (empty($user_email) && !empty($entry['extra']['attempted_email'])) {
										$user_email = $entry['extra']['attempted_email'];
									}
									if (empty($user_email)) {
										$user_email = __('User deleted', 'user-manager');
									}
									?>
									<li>
										<span><?php echo esc_html($user_email); ?></span>
										<span class="um-recent-time"><?php echo esc_html(User_Manager_Core::nice_time($entry['created_at'])); ?></span>
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
		.um-recent-users-list span {
			color: #646970;
			font-size: 13px;
			white-space: normal;
			word-break: break-word;
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
}

