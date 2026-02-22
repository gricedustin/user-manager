<?php
/**
 * Reset Password tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Reset_Password {

	public static function render(): void {
		$templates = User_Manager_Core::get_email_templates();
		$activity  = User_Manager_Core::get_activity_log();

		// Normalize activity log structure.
		$entries = $activity['entries'] ?? $activity;
		if (!is_array($entries)) {
			$entries = [];
		}

		// Filter to only entries from Reset Password tool.
		$recent_resets = array_filter($entries, static function ($entry) {
			return is_array($entry)
				&& isset($entry['tool'])
				&& $entry['tool'] === 'Reset Password';
		});
		$recent_resets = array_slice($recent_resets, 0, 15);
		?>
		<div class="um-create-user-layout">
			<div class="um-create-user-form">
				<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-lock"></span>
					<h2><?php esc_html_e('Reset Password', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-reset-password-form">
						<input type="hidden" name="action" value="user_manager_reset_password" />
						<?php wp_nonce_field('user_manager_reset_password'); ?>
						
						<?php
						$prefill_emails = isset($_GET['um_email']) ? sanitize_textarea_field(wp_unslash($_GET['um_email'])) : '';
						?>
						<div class="um-form-field">
							<label for="um-reset-emails"><?php esc_html_e('Email Addresses', 'user-manager'); ?> <span style="color:red;">*</span></label>
							<textarea name="emails" id="um-reset-emails" class="large-text" rows="4" required placeholder="<?php esc_attr_e("user1@example.com\nuser2@example.com", 'user-manager'); ?>"><?php echo esc_textarea($prefill_emails); ?></textarea>
							<p class="description"><?php esc_html_e('Enter one email address per line. Multiple emails will use random passwords.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field" id="um-password-field-wrapper">
							<label for="um-new-password"><?php esc_html_e('New Password', 'user-manager'); ?></label>
							<div class="um-password-input-row">
								<input type="text" name="password" id="um-new-password" class="regular-text" />
								<button type="button" class="button" id="um-insert-random-password"><?php esc_html_e('Insert Random Password', 'user-manager'); ?></button>
							</div>
							<p class="description"><?php esc_html_e('Optional. Leave blank to auto-generate a secure random password.', 'user-manager'); ?></p>
							<p class="description um-bulk-password-notice" style="display:none; color: #d63638;"><strong><?php esc_html_e('Disabled: Multiple emails detected. Random passwords will be generated for each user.', 'user-manager'); ?></strong></p>
						</div>
						
						<div class="um-info-box">
							<span class="dashicons dashicons-info"></span>
							<div>
								<strong><?php esc_html_e('Tip: Force user to reset their own password', 'user-manager'); ?></strong>
								<p><?php esc_html_e('Leave the password field empty (a random password will be set), then send an email using a template with the %PASSWORDRESETURL% placeholder. This will send the user a secure link to reset their password themselves.', 'user-manager'); ?></p>
							</div>
						</div>
						
						<div class="um-send-email-option">
							<div class="um-form-field-inline">
								<input type="checkbox" name="send_email" id="um-reset-send-email" value="1" />
								<label for="um-reset-send-email"><strong><?php esc_html_e('Send Password Reset Email', 'user-manager'); ?></strong></label>
							</div>
							<p class="description" style="margin-top:6px;">
								<?php esc_html_e('Select a password-reset-focused email template. Coupon-related templates may not behave as expected here because the Reset Password tool does not create any coupons.', 'user-manager'); ?>
							</p>
							
							<div id="um-reset-template-select" style="margin-top:12px;display:none;">
								<label for="um-reset-template"><?php esc_html_e('Select Email Template:', 'user-manager'); ?></label>
								<select name="email_template" id="um-reset-template" class="regular-text" style="margin-top:6px;">
									<option value=""><?php esc_html_e('— Default Template —', 'user-manager'); ?></option>
									<?php foreach ($templates as $id => $template) : ?>
										<option value="<?php echo esc_attr($id); ?>" data-subject="<?php echo esc_attr($template['subject']); ?>" data-body="<?php echo esc_attr($template['body']); ?>" data-description="<?php echo esc_attr($template['description'] ?? ''); ?>"><?php echo esc_html($template['title']); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description um-template-description-note" style="margin-top:6px;"></p>
								<p style="margin-top:8px;">
									<button type="button" class="button" id="um-preview-reset-email-btn"><?php esc_html_e('Preview Email', 'user-manager'); ?></button>
								</p>
							</div>
						</div>
						
						<p style="margin-top:20px;">
							<?php submit_button(__('Reset Password', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</form>
				</div>
			</div>
			</div>
			
			<div class="um-create-user-recent">
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-clock"></span>
						<h2><?php esc_html_e('Recent Resets', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<?php if (empty($recent_resets)) : ?>
							<p class="um-empty-message"><?php esc_html_e('No password resets yet.', 'user-manager'); ?></p>
						<?php else : ?>
							<ul class="um-recent-users-list">
								<?php foreach ($recent_resets as $entry) : ?>
									<?php 
									$user = get_user_by('ID', $entry['user_id']);
									if (!$user) continue;
									?>
									<li>
										<a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>" title="<?php esc_attr_e('Edit user', 'user-manager'); ?>">
											<?php echo esc_html($user->user_email); ?>
										</a>
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
		.um-create-user-recent .um-admin-card-body {
			max-height: 500px;
			overflow-y: auto;
		}
		</style>
		
		<!-- Email Preview Modal -->
		<?php User_Manager_Tab_Shared::render_email_preview_modal($templates); ?>
		
		<script>
		jQuery(document).ready(function($) {
			$('#um-reset-send-email').on('change', function() {
				$('#um-reset-template-select').toggle(this.checked);
			});
			
			function umUpdateResetTemplateDescription($select) {
				var val = $select.val();
				var desc = '';
				if (val && window.umTemplates && umTemplates[val] && umTemplates[val].description) {
					desc = umTemplates[val].description;
				} else {
					var $opt = $select.find('option:selected');
					desc = $opt.data('description') || '';
				}
				var $note = $('#um-reset-template-select').find('.um-template-description-note');
				if ($note.length) {
					$note.text(desc || '');
				}
			}
			$('#um-reset-template').on('change', function(){ umUpdateResetTemplateDescription($(this)); });
			umUpdateResetTemplateDescription($('#um-reset-template'));
			
			$('#um-preview-reset-email-btn').on('click', function() {
				umShowEmailPreview('reset');
			});
			
			$('#um-insert-random-password').on('click', function() {
				var len = 16;
				var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*';
				var pw = '';
				var rnd = new Uint32Array(len);
				if (window.crypto && window.crypto.getRandomValues) {
					window.crypto.getRandomValues(rnd);
					for (var i = 0; i < len; i++) {
						pw += chars[rnd[i] % chars.length];
					}
				} else {
					for (var j = 0; j < len; j++) {
						pw += chars[Math.floor(Math.random() * chars.length)];
					}
				}
				$('#um-new-password').val(pw);
			});

			// Handle multi-email detection
			$('#um-reset-emails').on('input', function() {
				var emails = $(this).val().split('\n').filter(function(e) { return e.trim() !== ''; });
				var $passwordField = $('#um-new-password');
				var $bulkNotice = $('.um-bulk-password-notice');
				
				if (emails.length > 1) {
					$passwordField.prop('disabled', true).val('').css('background-color', '#f0f0f1');
					$bulkNotice.show();
				} else {
					$passwordField.prop('disabled', false).css('background-color', '');
					$bulkNotice.hide();
				}
			});
		});
		</script>
		<?php
	}
}


