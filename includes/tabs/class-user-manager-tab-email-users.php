<?php
/**
 * Email Users tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Email_Users {

	public static function render(string $base_url = ''): void {
		$templates     = User_Manager_Core::get_email_templates();
		$activity_data = User_Manager_Core::get_activity_log();
		$page_url = $base_url !== '' ? $base_url : User_Manager_Core::get_page_url(User_Manager_Core::TAB_EMAIL_USERS);

		// Normalize activity log structure (supports older flat arrays and newer ['entries' => []] format).
		$entries = $activity_data['entries'] ?? $activity_data;
		if (!is_array($entries)) {
			$entries = [];
		}

		// Filter to only email_sent entries, guarding against non-array/legacy values.
		$recent_emails = array_filter($entries, static function($entry) {
			return is_array($entry) && isset($entry['action']) && $entry['action'] === 'email_sent';
		});
		$recent_emails = array_slice($recent_emails, 0, 15);
		
		// Check for pending email batch
		$transient_key = 'um_email_batch_' . get_current_user_id();
		$pending_batch = get_transient($transient_key);
		
		// Get custom email lists
		$custom_lists = get_option('um_custom_email_lists', []);
		if (!is_array($custom_lists)) {
			$custom_lists = [];
		}
		// Sort lists A-Z by title
		uasort($custom_lists, function($a, $b) {
			return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
		});
		$editing_list_id = isset($_GET['edit_list']) ? sanitize_text_field(wp_unslash($_GET['edit_list'])) : '';
		$editing_list = !empty($editing_list_id) && isset($custom_lists[$editing_list_id]) ? $custom_lists[$editing_list_id] : null;
		?>
		<div class="um-create-user-layout">
			<div class="um-create-user-form">
				<?php if (!empty($pending_batch) && is_array($pending_batch)) : ?>
					<div class="um-admin-card" style="margin-bottom: 20px; border-left: 4px solid #2271b1;">
						<div class="um-admin-card-header">
							<span class="dashicons dashicons-clock"></span>
							<h2><?php esc_html_e('Pending Email Batch', 'user-manager'); ?></h2>
						</div>
						<div class="um-admin-card-body">
							<p>
								<strong><?php esc_html_e('Batch Status:', 'user-manager'); ?></strong><br>
								<?php 
								$total_original = $pending_batch['total_original'] ?? 0;
								$total_sent = $pending_batch['total_sent_so_far'] ?? 0;
								$remaining = count($pending_batch['emails'] ?? []);
								printf(
									esc_html__('Total emails: %d | Sent so far: %d | Remaining: %d', 'user-manager'),
									$total_original,
									$total_sent,
									$remaining
								);
								?>
							</p>
							<?php if (!empty($pending_batch['created_at'])) : ?>
								<p style="color: #646970; font-size: 12px; margin-top: 8px;">
									<?php printf(
										esc_html__('Batch started: %s', 'user-manager'),
										esc_html(User_Manager_Core::nice_time($pending_batch['created_at']))
									); ?>
								</p>
							<?php endif; ?>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 15px;">
								<input type="hidden" name="action" value="user_manager_email_users_next_batch" />
								<?php wp_nonce_field('user_manager_email_users_next_batch'); ?>
								<?php submit_button(__('Send Next Batch', 'user-manager'), 'primary', 'submit', false); ?>
							</form>
						</div>
					</div>
				<?php endif; ?>
				
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-email-alt"></span>
						<h2><?php esc_html_e('Email Users', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-email-users-form">
							<input type="hidden" name="action" value="user_manager_email_users" />
							<?php wp_nonce_field('user_manager_email_users'); ?>
							
							<?php
							// Get all user roles and count emails for each
							global $wp_roles;
							$roles = $wp_roles->get_names();
							$role_counts = [];
							foreach ($roles as $role_key => $role_name) {
								$users = get_users(['role' => $role_key, 'fields' => ['user_email']]);
								$role_counts[$role_key] = [
									'name' => $role_name,
									'count' => count($users),
									'emails' => array_map(function($user) { return $user->user_email; }, $users)
								];
							}
							?>
							
							<div class="um-form-field">
								<label><?php esc_html_e('Select by User Role', 'user-manager'); ?></label>
								<div class="um-role-selector">
									<?php foreach ($role_counts as $role_key => $role_data) : ?>
										<label>
											<input type="checkbox" class="um-role-checkbox" value="<?php echo esc_attr($role_key); ?>" data-emails="<?php echo esc_attr(implode("\n", $role_data['emails'])); ?>" />
											<strong><?php echo esc_html($role_data['name']); ?></strong>
											<span style="color: #646970; margin-left: 8px;">(<?php echo esc_html(number_format($role_data['count'])); ?> <?php echo esc_html($role_data['count'] === 1 ? __('email', 'user-manager') : __('emails', 'user-manager')); ?>)</span>
										</label>
									<?php endforeach; ?>
								</div>
								<p class="description"><?php esc_html_e('Select one or more user roles to automatically fill the Email Addresses field below. You can select multiple roles and manually edit the list.', 'user-manager'); ?></p>
							</div>
							
							<?php if (!empty($custom_lists)) : ?>
							<div class="um-form-field">
								<label><?php esc_html_e('Select by List', 'user-manager'); ?></label>
								<div class="um-role-selector">
									<?php foreach ($custom_lists as $list_id => $list_data) : ?>
										<?php
										$list_emails = $list_data['emails'] ?? [];
										$email_count = count($list_emails);
										?>
										<label>
											<input type="checkbox" class="um-list-checkbox" value="<?php echo esc_attr($list_id); ?>" data-emails="<?php echo esc_attr(implode("\n", $list_emails)); ?>" />
											<strong><?php echo esc_html($list_data['title'] ?? ''); ?></strong>
											<span style="color: #646970; margin-left: 8px;">(<?php echo esc_html(number_format($email_count)); ?> <?php echo esc_html($email_count === 1 ? __('email', 'user-manager') : __('emails', 'user-manager')); ?>)</span>
										</label>
									<?php endforeach; ?>
								</div>
								<p class="description"><?php esc_html_e('Select one or more custom email lists to automatically fill the Email Addresses field below. You can select multiple lists and manually edit the list.', 'user-manager'); ?></p>
							</div>
							<?php endif; ?>
							
							<div class="um-form-field">
								<label for="um-email-users-list"><?php esc_html_e('Email Addresses', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<textarea name="emails" id="um-email-users-list" class="large-text" rows="8" required placeholder="<?php esc_attr_e("user1@example.com\nuser2@example.com\nuser3@example.com", 'user-manager'); ?>"></textarea>
								<p class="description"><?php esc_html_e('Enter one email address per line. Only existing users will receive emails.', 'user-manager'); ?></p>
							</div>
							
							<div class="um-form-field">
								<label for="um-email-users-template">
									<?php esc_html_e('Email Template', 'user-manager'); ?> <span style="color:red;">*</span>
									<?php User_Manager_Tab_Shared::render_template_settings_shortcut('email'); ?>
								</label>
								<select name="email_template" id="um-email-users-template" class="regular-text" required>
									<option value=""><?php esc_html_e('— Select Template —', 'user-manager'); ?></option>
									<?php foreach ($templates as $id => $template) : ?>
										<option value="<?php echo esc_attr($id); ?>" data-description="<?php echo esc_attr($template['description'] ?? ''); ?>"><?php echo esc_html($template['title']); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description um-template-description-note" style="margin-top:6px;"></p>
								<?php if (empty($templates)) : ?>
									<p class="description" style="color: #d63638;"><?php esc_html_e('No templates found. Create one in Add-ons → Send Email → Email Templates.', 'user-manager'); ?></p>
								<?php endif; ?>
							</div>
							
							<div class="um-form-field">
								<label for="um-email-users-login-url"><?php esc_html_e('Login URL (for template)', 'user-manager'); ?></label>
								<select name="login_url" id="um-email-users-login-url" class="regular-text">
									<option value="/my-account/"><?php esc_html_e('My Account', 'user-manager'); ?></option>
									<option value="/wp-admin/"><?php esc_html_e('WP Admin', 'user-manager'); ?></option>
									<option value="/wp-login.php?saml_sso=false"><?php esc_html_e('WP Admin SSO Bypass', 'user-manager'); ?></option>
								</select>
								<p class="description"><?php esc_html_e('Used for %LOGINURL% placeholder in email template.', 'user-manager'); ?></p>
							</div>
							
							<div class="um-form-field" id="um-email-users-coupon-code-row">
								<label for="um-email-users-coupon-code"><?php esc_html_e('Coupon code (for %COUPONCODE% in template)', 'user-manager'); ?></label>
								<input type="text" name="coupon_code_for_template" id="um-email-users-coupon-code" class="regular-text" list="um-email-users-coupon-code-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or leave empty', 'user-manager'); ?>" value="" autocomplete="off" />
								<datalist id="um-email-users-coupon-code-datalist"></datalist>
								<p class="description"><?php esc_html_e('When the selected template contains %COUPONCODE%, this code is used in the email (preview and send). Leave empty to omit or use a default in preview.', 'user-manager'); ?></p>
							</div>
							
							<div class="um-form-field">
								<label>
									<input type="checkbox" name="send_to_all_emails" id="um-send-to-all-emails" value="1" />
									<?php esc_html_e('Send to all email addresses even if they are not users', 'user-manager'); ?>
								</label>
								<p class="description"><?php esc_html_e('When checked, emails will be sent to all addresses in the list, regardless of whether they exist as WordPress users. When unchecked, only existing users will receive emails.', 'user-manager'); ?></p>
							</div>
							
							<div class="um-info-box">
								<span class="dashicons dashicons-info"></span>
								<div>
									<strong><?php esc_html_e('Note', 'user-manager'); ?></strong>
									<p><?php esc_html_e('By default, emails are sent to existing users only. Check the option above to send to all email addresses. The %PASSWORD% placeholder will show "••••••••" since actual passwords cannot be retrieved.', 'user-manager'); ?></p>
								</div>
							</div>
							
							<p style="margin-top:20px;">
								<button type="button" class="button" id="um-preview-email-users-btn" <?php echo empty($templates) ? 'disabled' : ''; ?>><?php esc_html_e('Preview Email', 'user-manager'); ?></button>
								<?php submit_button(__('Send Emails', 'user-manager'), 'primary', 'submit', false, empty($templates) ? ['disabled' => 'disabled'] : []); ?>
							</p>
						</form>
					</div>
				</div>
			</div>
			
			<div class="um-create-user-recent">
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-clock"></span>
						<h2><?php esc_html_e('Recently Emailed', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<?php if (empty($recent_emails)) : ?>
							<p class="um-empty-message"><?php esc_html_e('No emails sent yet.', 'user-manager'); ?></p>
						<?php else : ?>
							<ul class="um-recent-users-list">
								<?php foreach ($recent_emails as $entry) : ?>
									<?php 
									$user_id = $entry['user_id'] ?? 0;
									$user = $user_id > 0 ? get_user_by('ID', $user_id) : false;
									
									// Get email from user or from log extra data
									$email = '';
									$extra = $entry['extra'] ?? [];
									
									if ($user) {
										$email = $user->user_email;
									} elseif (isset($extra['user_email'])) {
										$email = $extra['user_email'];
									} elseif (isset($extra['attempted_email'])) {
										$email = $extra['attempted_email'];
									}
									
									if (empty($email)) continue;
									?>
									<li>
										<?php if ($user) : ?>
											<a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>" title="<?php esc_attr_e('Edit user', 'user-manager'); ?>">
												<?php echo esc_html($email); ?>
											</a>
										<?php else : ?>
											<span class="um-recent-email-no-link" title="<?php esc_attr_e('Non-user email address', 'user-manager'); ?>"><?php echo esc_html($email); ?></span>
										<?php endif; ?>
										<span class="um-recent-time"><?php echo esc_html(User_Manager_Core::nice_time($entry['created_at'])); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="um-admin-card" style="margin-top: 20px;">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-list-view"></span>
						<h2><?php esc_html_e('Custom Email Lists', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-email-list-form">
							<input type="hidden" name="action" value="user_manager_save_email_list" />
							<?php wp_nonce_field('user_manager_save_email_list'); ?>
							<?php if ($editing_list) : ?>
								<input type="hidden" name="list_id" value="<?php echo esc_attr($editing_list_id); ?>" />
							<?php endif; ?>
							
							<div class="um-form-field">
								<label for="um-list-title"><?php esc_html_e('List Title', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<input type="text" name="list_title" id="um-list-title" class="regular-text" value="<?php echo esc_attr($editing_list['title'] ?? ''); ?>" required />
							</div>
							
							<div class="um-form-field">
								<label for="um-list-emails"><?php esc_html_e('Email Addresses', 'user-manager'); ?></label>
								<textarea name="list_emails" id="um-list-emails" class="large-text" rows="6" placeholder="<?php esc_attr_e("user1@example.com\nuser2@example.com\nuser3@example.com", 'user-manager'); ?>"><?php echo esc_textarea(implode("\n", $editing_list['emails'] ?? [])); ?></textarea>
								<p class="description"><?php esc_html_e('Enter one email address per line.', 'user-manager'); ?></p>
							</div>
							
							<p>
								<?php submit_button($editing_list ? __('Update List', 'user-manager') : __('Create List', 'user-manager'), 'primary', 'submit', false); ?>
								<?php if ($editing_list) : ?>
									<a href="<?php echo esc_url($page_url); ?>" class="button" style="margin-left: 5px;"><?php esc_html_e('Cancel', 'user-manager'); ?></a>
								<?php endif; ?>
							</p>
						</form>
						
						<?php if ($editing_list) : ?>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 10px;" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to delete this list? This action cannot be undone.', 'user-manager')); ?>');">
								<input type="hidden" name="action" value="user_manager_delete_email_list" />
								<input type="hidden" name="list_id" value="<?php echo esc_attr($editing_list_id); ?>" />
								<?php wp_nonce_field('user_manager_delete_email_list'); ?>
								<?php submit_button(__('Delete List', 'user-manager'), 'button button-link-delete', 'submit', false); ?>
							</form>
						<?php endif; ?>
						
						<?php if (!empty($custom_lists)) : ?>
							<hr style="margin: 20px 0;" />
							<h3 style="margin-top: 0;"><?php esc_html_e('Saved Lists', 'user-manager'); ?></h3>
							<table class="widefat striped">
								<thead>
									<tr>
										<th><?php esc_html_e('Title', 'user-manager'); ?></th>
										<th><?php esc_html_e('Emails', 'user-manager'); ?></th>
										<th style="text-align: center;"><?php esc_html_e('Actions', 'user-manager'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($custom_lists as $list_id => $list_data) : ?>
										<tr>
											<td><strong><?php echo esc_html($list_data['title'] ?? ''); ?></strong></td>
											<td><?php echo esc_html(number_format(count($list_data['emails'] ?? []))); ?></td>
											<td style="text-align: center;">
												<a href="<?php echo esc_url(add_query_arg('edit_list', $list_id, $page_url)); ?>" class="button button-small"><?php esc_html_e('Edit', 'user-manager'); ?></a>
												<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
													<input type="hidden" name="action" value="user_manager_download_email_list_csv" />
													<input type="hidden" name="list_id" value="<?php echo esc_attr($list_id); ?>" />
													<?php wp_nonce_field('user_manager_download_email_list_csv_' . $list_id); ?>
													<?php submit_button(__('CSV', 'user-manager'), 'button-small', 'submit', false, ['style' => 'margin-left: 5px;']); ?>
												</form>
												<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to delete this list?', 'user-manager')); ?>');">
													<input type="hidden" name="action" value="user_manager_delete_email_list" />
													<input type="hidden" name="list_id" value="<?php echo esc_attr($list_id); ?>" />
													<?php wp_nonce_field('user_manager_delete_email_list'); ?>
													<?php submit_button(__('Delete', 'user-manager'), 'button-small', 'submit', false, ['style' => 'margin-left: 5px;']); ?>
												</form>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
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
		.um-role-selector {
			border: 1px solid #ddd;
			border-radius: 4px;
			padding: 10px;
			background: #f9f9f9;
			margin-bottom: 10px;
		}
		.um-role-selector label {
			display: block;
			padding: 6px 0;
			cursor: pointer;
		}
		.um-role-selector input[type="checkbox"] {
			margin-right: 8px;
		}
		.um-list-checkbox {
			margin-right: 8px;
		}
		.button-link-delete {
			color: #b32d2e !important;
		}
		.button-link-delete:hover {
			color: #dc3232 !important;
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
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			max-width: 200px;
		}
		.um-recent-users-list a:hover {
			text-decoration: underline;
		}
		.um-recent-email-no-link {
			color: #646970;
			font-size: 13px;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			max-width: 200px;
			display: inline-block;
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
		
		<!-- Email Preview Modal -->
		<?php User_Manager_Tab_Shared::render_email_preview_modal($templates); ?>
		
		<script>
		jQuery(document).ready(function($) {
			$('#um-preview-email-users-btn').on('click', function() {
				umShowEmailPreview('email-users');
			});
			
			function umUpdateEmailUsersTemplateDesc() {
				var $select = $('#um-email-users-template');
				var val = $select.val();
				var desc = '';
				if (val && window.umTemplates && umTemplates[val] && umTemplates[val].description) {
					desc = umTemplates[val].description;
				} else {
					var $opt = $select.find('option:selected');
					desc = $opt.data('description') || '';
				}
				$select.closest('.um-admin-card-body').find('.um-template-description-note').first().text(desc || '');
			}
			$('#um-email-users-template').on('change', umUpdateEmailUsersTemplateDesc);
			umUpdateEmailUsersTemplateDesc();
			
			// Role and list selector functionality
			function updateEmailListFromSelectors() {
				var allEmails = [];
				var selectedRoles = [];
				var selectedLists = [];
				
				// Get emails from selected roles
				$('.um-role-checkbox:checked').each(function() {
					var roleKey = $(this).val();
					var roleName = $(this).closest('label').find('strong').text();
					selectedRoles.push(roleName);
					
					var emails = $(this).data('emails');
					if (emails) {
						var emailArray = emails.split('\n').filter(function(email) {
							return email.trim() !== '';
						});
						allEmails = allEmails.concat(emailArray);
					}
				});
				
				// Get emails from selected lists
				$('.um-list-checkbox:checked').each(function() {
					var listId = $(this).val();
					var listName = $(this).closest('label').find('strong').text();
					selectedLists.push(listName);
					
					var emails = $(this).data('emails');
					if (emails) {
						var emailArray = emails.split('\n').filter(function(email) {
							return email.trim() !== '';
						});
						allEmails = allEmails.concat(emailArray);
					}
				});
				
				// Remove duplicates and sort
				allEmails = [...new Set(allEmails)].sort();
				
				// Update textarea
				$('#um-email-users-list').val(allEmails.join('\n'));
				
				// Store selected roles and lists in hidden fields for activity log
				$('#um-selected-roles').remove();
				$('#um-selected-lists').remove();
				if (selectedRoles.length > 0) {
					$('#um-email-users-form').append('<input type="hidden" id="um-selected-roles" name="selected_roles" value="' + selectedRoles.join(', ') + '" />');
				}
				if (selectedLists.length > 0) {
					$('#um-email-users-form').append('<input type="hidden" id="um-selected-lists" name="selected_lists" value="' + selectedLists.join(', ') + '" />');
				}
			}
			
			$('.um-role-checkbox, .um-list-checkbox').on('change', updateEmailListFromSelectors);
		});
		</script>
		<?php
	}
}




