<?php
/**
 * Create User tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Create_User {

	public static function render(): void {
		$roles     = User_Manager_Core::get_user_roles();
		$templates = User_Manager_Core::get_email_templates();
		$activity_data = User_Manager_Core::get_activity_log();

		// Normalize activity log structure (supports newer ['entries' => []] format).
		$entries = $activity_data['entries'] ?? $activity_data;
		if (!is_array($entries)) {
			$entries = [];
		}
		
		// Filter to only entries from Create User tool, guarding against non-array values.
		$recent_users = array_filter($entries, static function($entry) {
			return is_array($entry) && isset($entry['tool']) && in_array($entry['tool'], ['Create User', 'Create User (Updated)'], true);
		});
		$recent_users = array_slice($recent_users, 0, 15);
		?>
		<div class="um-create-user-layout">
			<div class="um-create-user-form">
				<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-admin-users"></span>
					<h2><?php esc_html_e('Create New User', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-create-user-form">
						<input type="hidden" name="action" value="user_manager_create_user" />
						<?php wp_nonce_field('user_manager_create_user'); ?>
						
						<div class="um-form-field">
							<label for="um-email"><?php esc_html_e('Email Address', 'user-manager'); ?> <span style="color:red;">*</span></label>
							<input type="email" name="email" id="um-email" class="regular-text" required />
							<?php
							$current_user = wp_get_current_user();
							if ($current_user->user_email && strpos($current_user->user_email, '@') !== false) {
								$date_part = current_time('Ymd-His');
								$parts = explode('@', $current_user->user_email, 2);
								$quick_test_email = $parts[0] . '+' . $date_part . '@' . $parts[1];
								echo '<p class="description">' . esc_html__('Quick Test User:', 'user-manager') . ' <code>' . esc_html($quick_test_email) . '</code></p>';
							}
							?>
						</div>
						
						<div class="um-form-field">
							<label for="um-username"><?php esc_html_e('Username', 'user-manager'); ?></label>
							<input type="text" name="username" id="um-username" class="regular-text" />
							<p class="description"><?php esc_html_e('Optional. Defaults to email address if left blank.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="um-first-name"><?php esc_html_e('First Name', 'user-manager'); ?></label>
							<input type="text" name="first_name" id="um-first-name" class="regular-text" />
						</div>
						
						<div class="um-form-field">
							<label for="um-last-name"><?php esc_html_e('Last Name', 'user-manager'); ?></label>
							<input type="text" name="last_name" id="um-last-name" class="regular-text" />
						</div>
						
						<div class="um-form-field">
							<label for="um-password"><?php esc_html_e('Password', 'user-manager'); ?></label>
							<div class="um-password-input-row">
								<input type="text" name="password" id="um-password" class="regular-text" />
								<button type="button" class="button" id="um-insert-random-password-create"><?php esc_html_e('Insert Random Password', 'user-manager'); ?></button>
							</div>
							<p class="description"><?php esc_html_e('Optional. Leave blank to auto-generate a secure random password.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="um-role"><?php esc_html_e('Role', 'user-manager'); ?></label>
							<select name="role" id="um-role" class="regular-text">
								<?php foreach ($roles as $key => $name) : ?>
									<option value="<?php echo esc_attr($key); ?>" <?php selected($key, 'customer'); ?>><?php echo esc_html($name); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						
						<div class="um-form-field">
							<label for="um-login-url"><?php esc_html_e('Login URL', 'user-manager'); ?></label>
							<select name="login_url" id="um-login-url" class="regular-text">
								<option value="/my-account/"><?php esc_html_e('My Account', 'user-manager'); ?></option>
								<option value="/wp-admin/"><?php esc_html_e('WP Admin', 'user-manager'); ?></option>
								<option value="/wp-login.php?saml_sso=false"><?php esc_html_e('WP Admin SSO Bypass', 'user-manager'); ?></option>
							</select>
							<p class="description"><?php esc_html_e('Used in email template for login link.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="um-create-coupon-code"><?php esc_html_e('Coupon code (for %COUPONCODE% in template)', 'user-manager'); ?></label>
							<input type="text" name="coupon_code_for_template" id="um-create-coupon-code" class="regular-text" list="um-create-coupon-code-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or leave empty', 'user-manager'); ?>" value="" autocomplete="off" />
							<datalist id="um-create-coupon-code-datalist"></datalist>
							<p class="description"><?php esc_html_e('When the selected template contains %COUPONCODE%, this code is used in the email. Leave empty to omit.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="um-allow-email-coupon"><?php esc_html_e('Also Append This Email Address to "Allowed Emails" on Coupon', 'user-manager'); ?></label>
							<input type="text" name="allow_email_coupon_code" id="um-allow-email-coupon" class="regular-text" list="um-allow-email-coupon-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or leave empty for none', 'user-manager'); ?>" value="" autocomplete="off" />
							<datalist id="um-allow-email-coupon-datalist"></datalist>
							<p class="description">
								<?php esc_html_e('Optional. When set, this user’s email will also be added to the “Allowed emails” / email restrictions on the selected WooCommerce coupon.', 'user-manager'); ?>
							</p>
						</div>
						
						<div class="um-send-email-option">
							<div class="um-form-field-inline">
								<input type="checkbox" name="send_email" id="um-send-email" value="1" />
								<label for="um-send-email"><strong><?php esc_html_e('Send Welcome Email', 'user-manager'); ?></strong></label>
							</div>
							<p class="description" style="margin-top:6px;">
								<?php esc_html_e('Be sure to select an email template that matches this tool. Coupon-related templates may not behave as expected here because the Create User form does not create any coupons.', 'user-manager'); ?>
							</p>
							
							<div id="um-email-template-select" style="margin-top:12px;display:none;">
								<label for="um-template"><?php esc_html_e('Select Email Template:', 'user-manager'); ?></label>
								<select name="email_template" id="um-template" class="regular-text" style="margin-top:6px;">
									<option value=""><?php esc_html_e('— Default Template —', 'user-manager'); ?></option>
									<?php foreach ($templates as $id => $template) : ?>
										<option value="<?php echo esc_attr($id); ?>" data-subject="<?php echo esc_attr($template['subject']); ?>" data-body="<?php echo esc_attr($template['body']); ?>" data-description="<?php echo esc_attr($template['description'] ?? ''); ?>"><?php echo esc_html($template['title']); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description um-template-description-note" style="margin-top:6px;"></p>
								<p style="margin-top:8px;">
									<button type="button" class="button" id="um-preview-email-btn"><?php esc_html_e('Preview Email', 'user-manager'); ?></button>
								</p>
							</div>
						</div>
						
						<p style="margin-top:20px;">
							<?php submit_button(__('Create User', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</form>
				</div>
			</div>
			</div>
			
			<div class="um-create-user-recent">
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-clock"></span>
						<h2><?php esc_html_e('Recently Created', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<?php if (empty($recent_users)) : ?>
							<p class="um-empty-message"><?php esc_html_e('No users created yet.', 'user-manager'); ?></p>
						<?php else : ?>
							<ul class="um-recent-users-list">
								<?php foreach ($recent_users as $entry) : ?>
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
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			max-width: 200px;
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
		
		<!-- Email Preview Modal -->
		<?php User_Manager_Tab_Shared::render_email_preview_modal($templates); ?>
		
		<script>
		jQuery(document).ready(function($) {
			$('#um-send-email').on('change', function() {
				$('#um-email-template-select').toggle(this.checked);
			});

			function umGenerateRandomPassword(length) {
				var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
				var result = '';
				for (var i = 0; i < length; i++) {
					result += chars.charAt(Math.floor(Math.random() * chars.length));
				}
				return result;
			}

			$('#um-insert-random-password-create').on('click', function() {
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
				var $field = $('#um-password');
				$field.val(pw);
				$field.trigger('change');
				$field.focus().select();
			});
			
			function umUpdateTemplateDescription($select) {
				var val = $select.val();
				var desc = '';
				if (val && window.umTemplates && umTemplates[val] && umTemplates[val].description) {
					desc = umTemplates[val].description;
				} else {
					// fallback to data attribute if present
					var $opt = $select.find('option:selected');
					desc = $opt.data('description') || '';
				}
				var $note = $select.closest('#um-email-template-select').find('.um-template-description-note');
				if ($note.length) {
					$note.text(desc || '');
				}
			}
			$('#um-template').on('change', function(){ umUpdateTemplateDescription($(this)); });
			umUpdateTemplateDescription($('#um-template'));
			
			$('#um-preview-email-btn').on('click', function() {
				umShowEmailPreview('single');
			});
		});
		</script>
		<?php
	}
}




