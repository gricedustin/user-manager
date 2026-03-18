<?php
/**
 * Add-on card: Send SMS Text.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('User_Manager_SMS')) {
	require_once dirname(__DIR__) . '/class-user-manager-sms.php';
}

class User_Manager_Addon_Send_SMS_Text {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['send_sms_text_enabled']);
		$templates = User_Manager_Core::get_sms_text_templates();

		$activity_data = User_Manager_Core::get_activity_log();
		$entries = $activity_data['entries'] ?? $activity_data;
		if (!is_array($entries)) {
			$entries = [];
		}
		$recent_texts = array_filter($entries, static function ($entry) {
			return is_array($entry) && isset($entry['action']) && $entry['action'] === 'sms_sent';
		});
		$recent_texts = array_slice($recent_texts, 0, 15);

		$pending_batch = get_transient('um_sms_batch_' . get_current_user_id());

		$custom_lists = get_option('um_custom_email_lists', []);
		if (!is_array($custom_lists)) {
			$custom_lists = [];
		}
		uasort($custom_lists, static function ($a, $b) {
			return strcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
		});

		$role_phone_data = self::get_role_phone_data();
		$list_phone_data = self::get_list_phone_data($custom_lists);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-send-sms-text" data-um-active-selectors="#um-send-sms-text-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-smartphone"></span>
				<h2><?php esc_html_e('Send SMS Text', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-send-sms-text-enabled" name="send_sms_text_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Send SMS text messages to users (or phone numbers directly) using SMS Text Templates and your Simple Texting API token.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-send-sms-text-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<?php if (!empty($pending_batch) && is_array($pending_batch)) : ?>
						<div class="um-admin-card" style="margin-bottom: 20px; border-left: 4px solid #2271b1;">
							<div class="um-admin-card-header">
								<span class="dashicons dashicons-clock"></span>
								<h2><?php esc_html_e('Pending Text Batch', 'user-manager'); ?></h2>
							</div>
							<div class="um-admin-card-body">
								<p>
									<strong><?php esc_html_e('Batch Status:', 'user-manager'); ?></strong><br>
									<?php
									$total_original = isset($pending_batch['total_original']) ? absint($pending_batch['total_original']) : 0;
									$total_sent = isset($pending_batch['total_sent_so_far']) ? absint($pending_batch['total_sent_so_far']) : 0;
									$remaining = count($pending_batch['phone_numbers'] ?? []);
									printf(
										esc_html__('Total texts: %1$d | Sent so far: %2$d | Remaining: %3$d', 'user-manager'),
										$total_original,
										$total_sent,
										$remaining
									);
									?>
								</p>
								<?php if (!empty($pending_batch['created_at'])) : ?>
									<p style="color:#646970; font-size:12px; margin-top:8px;">
										<?php
										printf(
											esc_html__('Batch started: %s', 'user-manager'),
											esc_html(User_Manager_Core::nice_time($pending_batch['created_at']))
										);
										?>
									</p>
								<?php endif; ?>
								<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 15px;">
									<input type="hidden" name="action" value="user_manager_send_sms_texts_next_batch" />
									<?php wp_nonce_field('user_manager_send_sms_texts_next_batch'); ?>
									<?php submit_button(__('Send Next Text Batch', 'user-manager'), 'primary', 'submit', false); ?>
								</form>
							</div>
						</div>
					<?php endif; ?>

					<div class="um-create-user-layout">
						<div class="um-create-user-form">
							<div class="um-admin-card">
								<div class="um-admin-card-header">
									<span class="dashicons dashicons-smartphone"></span>
									<h2><?php esc_html_e('Send SMS Texts', 'user-manager'); ?></h2>
								</div>
								<div class="um-admin-card-body">
									<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-send-sms-text-form">
										<input type="hidden" name="action" value="user_manager_send_sms_texts" />
										<?php wp_nonce_field('user_manager_send_sms_texts'); ?>

										<div class="um-form-field">
											<label><?php esc_html_e('Select by User Role', 'user-manager'); ?></label>
											<div class="um-role-selector">
												<?php foreach ($role_phone_data as $role_key => $role_data) : ?>
													<label>
														<input
															type="checkbox"
															class="um-sms-role-checkbox"
															value="<?php echo esc_attr($role_key); ?>"
															data-phones="<?php echo esc_attr(implode("\n", $role_data['phones'] ?? [])); ?>"
														/>
														<strong><?php echo esc_html($role_data['name'] ?? ''); ?></strong>
														<span style="color:#646970; margin-left:8px;">
															(<?php echo esc_html(number_format((int) ($role_data['count'] ?? 0))); ?> <?php echo esc_html(((int) ($role_data['count'] ?? 0)) === 1 ? __('phone', 'user-manager') : __('phones', 'user-manager')); ?>)
														</span>
													</label>
												<?php endforeach; ?>
											</div>
											<p class="description"><?php esc_html_e('Select one or more user roles to fill the Phone Numbers field with user phone numbers.', 'user-manager'); ?></p>
										</div>

										<?php if (!empty($custom_lists)) : ?>
											<div class="um-form-field">
												<label><?php esc_html_e('Select by List', 'user-manager'); ?></label>
												<div class="um-role-selector">
													<?php foreach ($custom_lists as $list_id => $list_data) : ?>
														<?php
														$list_phones = $list_phone_data[$list_id] ?? [];
														$phone_count = count($list_phones);
														?>
														<label>
															<input
																type="checkbox"
																class="um-sms-list-checkbox"
																value="<?php echo esc_attr($list_id); ?>"
																data-phones="<?php echo esc_attr(implode("\n", $list_phones)); ?>"
															/>
															<strong><?php echo esc_html($list_data['title'] ?? ''); ?></strong>
															<span style="color:#646970; margin-left:8px;">
																(<?php echo esc_html(number_format($phone_count)); ?> <?php echo esc_html($phone_count === 1 ? __('phone', 'user-manager') : __('phones', 'user-manager')); ?>)
															</span>
														</label>
													<?php endforeach; ?>
												</div>
												<p class="description"><?php esc_html_e('Lists are shared with Email Users. For SMS, list emails are mapped to user phone numbers when available.', 'user-manager'); ?></p>
											</div>
										<?php endif; ?>

										<div class="um-form-field">
											<label for="um-send-sms-phone-numbers"><?php esc_html_e('Phone Numbers', 'user-manager'); ?> <span style="color:red;">*</span></label>
											<textarea name="phone_numbers" id="um-send-sms-phone-numbers" class="large-text" rows="8" required placeholder="<?php esc_attr_e("+15551234567\n+15557654321\n+15559871234", 'user-manager'); ?>"></textarea>
											<p class="description"><?php esc_html_e('Enter one phone number per line. Use international format when possible (e.g. +15551234567).', 'user-manager'); ?></p>
										</div>

										<div class="um-form-field">
											<label for="um-send-sms-template"><?php esc_html_e('SMS Text Template', 'user-manager'); ?> <span style="color:red;">*</span></label>
											<select name="sms_template" id="um-send-sms-template" class="regular-text" required>
												<option value=""><?php esc_html_e('— Select Template —', 'user-manager'); ?></option>
												<?php foreach ($templates as $id => $template) : ?>
													<option value="<?php echo esc_attr($id); ?>" data-description="<?php echo esc_attr($template['description'] ?? ''); ?>" data-body="<?php echo esc_attr($template['body'] ?? ''); ?>">
														<?php echo esc_html($template['title'] ?? ''); ?>
													</option>
												<?php endforeach; ?>
											</select>
											<p class="description um-sms-template-description-note" style="margin-top:6px;"></p>
											<?php if (empty($templates)) : ?>
												<p class="description" style="color:#d63638;">
													<?php esc_html_e('No SMS text templates found. Create one in Settings → SMS Text Templates first.', 'user-manager'); ?>
												</p>
											<?php endif; ?>
										</div>

										<div class="um-form-field">
											<label for="um-send-sms-login-url"><?php esc_html_e('Login URL (for template)', 'user-manager'); ?></label>
											<select name="login_url" id="um-send-sms-login-url" class="regular-text">
												<option value="/my-account/"><?php esc_html_e('My Account', 'user-manager'); ?></option>
												<option value="/wp-admin/"><?php esc_html_e('WP Admin', 'user-manager'); ?></option>
												<option value="/wp-login.php?saml_sso=false"><?php esc_html_e('WP Admin SSO Bypass', 'user-manager'); ?></option>
											</select>
											<p class="description"><?php esc_html_e('Used for %LOGINURL% placeholder in SMS text templates.', 'user-manager'); ?></p>
										</div>

										<div class="um-form-field">
											<label for="um-send-sms-coupon-code"><?php esc_html_e('Coupon code (for %COUPONCODE% in template)', 'user-manager'); ?></label>
											<input type="text" name="coupon_code_for_template" id="um-send-sms-coupon-code" class="regular-text" list="um-send-sms-coupon-code-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or leave empty', 'user-manager'); ?>" value="" autocomplete="off" />
											<datalist id="um-send-sms-coupon-code-datalist"></datalist>
											<p class="description"><?php esc_html_e('When the selected SMS template contains %COUPONCODE%, this code is used in preview and sends.', 'user-manager'); ?></p>
										</div>

										<div class="um-form-field">
											<label>
												<input type="checkbox" name="send_to_all_phone_numbers" id="um-send-to-all-phone-numbers" value="1" />
												<?php esc_html_e('Send to all phone numbers even if they are not users', 'user-manager'); ?>
											</label>
											<p class="description"><?php esc_html_e('When checked, texts will be sent to all phone numbers in the list. When unchecked, only phone numbers that map to existing users are sent.', 'user-manager'); ?></p>
										</div>

										<div class="um-info-box">
											<span class="dashicons dashicons-info"></span>
											<div>
												<strong><?php esc_html_e('Note', 'user-manager'); ?></strong>
												<p><?php esc_html_e('The add-on uses your Simple Texting API Token from Settings → API Keys. If no token is configured, sends will fail.', 'user-manager'); ?></p>
											</div>
										</div>

										<p style="margin-top:20px;">
											<button type="button" class="button" id="um-preview-sms-text-btn" <?php echo empty($templates) ? 'disabled' : ''; ?>><?php esc_html_e('Preview SMS Text', 'user-manager'); ?></button>
											<?php submit_button(__('Send SMS Texts', 'user-manager'), 'primary', 'submit', false, empty($templates) ? ['disabled' => 'disabled'] : []); ?>
										</p>
									</form>
								</div>
							</div>
						</div>

						<div class="um-create-user-recent">
							<div class="um-admin-card">
								<div class="um-admin-card-header">
									<span class="dashicons dashicons-clock"></span>
									<h2><?php esc_html_e('Recent Texts', 'user-manager'); ?></h2>
								</div>
								<div class="um-admin-card-body">
									<?php if (empty($recent_texts)) : ?>
										<p class="um-empty-message"><?php esc_html_e('No texts sent yet.', 'user-manager'); ?></p>
									<?php else : ?>
										<ul class="um-recent-users-list">
											<?php foreach ($recent_texts as $entry) : ?>
												<?php
												$user_id = isset($entry['user_id']) ? (int) $entry['user_id'] : 0;
												$user = $user_id > 0 ? get_user_by('ID', $user_id) : false;
												$extra = isset($entry['extra']) && is_array($entry['extra']) ? $entry['extra'] : [];
												$phone = '';
												if (isset($extra['phone_number'])) {
													$phone = (string) $extra['phone_number'];
												} elseif (isset($extra['attempted_phone'])) {
													$phone = (string) $extra['attempted_phone'];
												} elseif ($user) {
													$phone = User_Manager_SMS::get_user_primary_phone((int) $user->ID);
												}
												$phone = User_Manager_SMS::normalize_phone_number($phone);
												if ($phone === '') {
													continue;
												}
												?>
												<li>
													<?php if ($user) : ?>
														<a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>" title="<?php esc_attr_e('Edit user', 'user-manager'); ?>">
															<?php echo esc_html($phone); ?>
														</a>
													<?php else : ?>
														<span class="um-recent-email-no-link" title="<?php esc_attr_e('Non-user phone number', 'user-manager'); ?>"><?php echo esc_html($phone); ?></span>
													<?php endif; ?>
													<span class="um-recent-time"><?php echo esc_html(User_Manager_Core::nice_time($entry['created_at'] ?? '')); ?></span>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							</div>

							<div class="um-admin-card" style="margin-top:20px;">
								<div class="um-admin-card-header">
									<span class="dashicons dashicons-list-view"></span>
									<h2><?php esc_html_e('Custom Email Lists', 'user-manager'); ?></h2>
								</div>
								<div class="um-admin-card-body">
									<p class="description" style="margin-top:0;">
										<?php esc_html_e('This add-on uses the same shared custom lists from Email Users (no separate SMS lists).', 'user-manager'); ?>
									</p>
									<p>
										<a href="<?php echo esc_url(User_Manager_Core::get_page_url(User_Manager_Core::TAB_EMAIL_USERS)); ?>" class="button">
											<?php esc_html_e('Manage Shared Lists in Email Users', 'user-manager'); ?>
										</a>
									</p>
									<?php if (empty($custom_lists)) : ?>
										<p class="um-empty-message"><?php esc_html_e('No shared lists found yet.', 'user-manager'); ?></p>
									<?php else : ?>
										<table class="widefat striped">
											<thead>
												<tr>
													<th><?php esc_html_e('Title', 'user-manager'); ?></th>
													<th><?php esc_html_e('Emails in List', 'user-manager'); ?></th>
													<th><?php esc_html_e('Phones Resolved', 'user-manager'); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($custom_lists as $list_id => $list_data) : ?>
													<tr>
														<td><strong><?php echo esc_html($list_data['title'] ?? ''); ?></strong></td>
														<td><?php echo esc_html(number_format(count($list_data['emails'] ?? []))); ?></td>
														<td><?php echo esc_html(number_format(count($list_phone_data[$list_id] ?? []))); ?></td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>

					<div id="um-sms-preview-modal" class="um-modal" style="display:none;">
						<div class="um-modal-overlay"></div>
						<div class="um-modal-content">
							<div class="um-modal-header">
								<h3><span class="dashicons dashicons-smartphone"></span> <?php esc_html_e('SMS Text Preview', 'user-manager'); ?></h3>
								<button type="button" class="um-modal-close">&times;</button>
							</div>
							<div class="um-modal-body">
								<div class="um-preview-section">
									<label><?php esc_html_e('To:', 'user-manager'); ?></label>
									<div id="um-sms-preview-to" class="um-preview-value"></div>
								</div>
								<div class="um-preview-section um-preview-section-full">
									<label><?php esc_html_e('SMS Preview:', 'user-manager'); ?></label>
									<div id="um-sms-preview-text" class="um-preview-value" style="white-space:pre-wrap;"></div>
								</div>
							</div>
						</div>
					</div>

					<style>
					#um-addon-card-send-sms-text .um-create-user-layout {
						display: grid;
						grid-template-columns: 1fr 350px;
						gap: 20px;
						margin-top: 20px;
					}
					@media (max-width: 1100px) {
						#um-addon-card-send-sms-text .um-create-user-layout {
							grid-template-columns: 1fr;
						}
					}
					#um-addon-card-send-sms-text .um-role-selector {
						border: 1px solid #ddd;
						border-radius: 4px;
						padding: 10px;
						background: #f9f9f9;
						margin-bottom: 10px;
						max-height: 220px;
						overflow: auto;
					}
					#um-addon-card-send-sms-text .um-role-selector label {
						display: block;
						padding: 6px 0;
						cursor: pointer;
					}
					#um-addon-card-send-sms-text .um-info-box {
						display: flex;
						gap: 10px;
						align-items: flex-start;
						padding: 10px 12px;
						border-left: 3px solid #2271b1;
						background: #f0f6fc;
						border-radius: 3px;
						margin-top: 8px;
					}
					#um-addon-card-send-sms-text .um-info-box .dashicons {
						color: #2271b1;
					}
					#um-addon-card-send-sms-text .um-info-box p {
						margin: 4px 0 0;
					}
					#um-addon-card-send-sms-text .um-recent-users-list {
						list-style: none;
						margin: 0;
						padding: 0;
					}
					#um-addon-card-send-sms-text .um-recent-users-list li {
						display: flex;
						justify-content: space-between;
						align-items: center;
						padding: 8px 0;
						border-bottom: 1px solid #f0f0f1;
					}
					#um-addon-card-send-sms-text .um-recent-users-list li:last-child {
						border-bottom: none;
					}
					#um-addon-card-send-sms-text .um-recent-users-list a,
					#um-addon-card-send-sms-text .um-recent-email-no-link {
						overflow: hidden;
						text-overflow: ellipsis;
						white-space: nowrap;
						max-width: 200px;
						font-size: 13px;
					}
					#um-addon-card-send-sms-text .um-recent-time {
						color: #646970;
						font-size: 12px;
						flex-shrink: 0;
					}
					#um-addon-card-send-sms-text .um-empty-message {
						color: #646970;
						font-style: italic;
						margin: 0;
					}
					#um-sms-preview-modal {
						position: fixed;
						top: 0;
						left: 0;
						right: 0;
						bottom: 0;
						z-index: 100000;
						display: flex;
						align-items: center;
						justify-content: center;
					}
					#um-sms-preview-modal .um-modal-overlay {
						position: absolute;
						top: 0;
						left: 0;
						right: 0;
						bottom: 0;
						background: rgba(0, 0, 0, 0.6);
					}
					#um-sms-preview-modal .um-modal-content {
						position: relative;
						background: #fff;
						border-radius: 8px;
						max-width: 640px;
						width: 95%;
						max-height: 90vh;
						overflow: hidden;
						display: flex;
						flex-direction: column;
						box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
					}
					#um-sms-preview-modal .um-modal-header {
						padding: 16px 20px;
						border-bottom: 1px solid #dcdcde;
						display: flex;
						justify-content: space-between;
						align-items: center;
						background: #f6f7f7;
					}
					#um-sms-preview-modal .um-modal-header h3 {
						margin: 0;
						font-size: 16px;
						display: flex;
						align-items: center;
						gap: 8px;
					}
					#um-sms-preview-modal .um-modal-close {
						background: none;
						border: none;
						font-size: 24px;
						cursor: pointer;
						color: #666;
						padding: 0;
						line-height: 1;
					}
					#um-sms-preview-modal .um-modal-close:hover {
						color: #d63638;
					}
					#um-sms-preview-modal .um-modal-body {
						padding: 20px;
						overflow-y: auto;
					}
					#um-sms-preview-modal .um-preview-section {
						margin-bottom: 16px;
					}
					#um-sms-preview-modal .um-preview-section label {
						display: block;
						font-weight: 600;
						margin-bottom: 6px;
					}
					#um-sms-preview-modal .um-preview-value {
						background: #f6f7f7;
						padding: 10px 12px;
						border-radius: 4px;
						border: 1px solid #dcdcde;
					}
					</style>

					<script>
					jQuery(function($) {
						var smsTemplates = <?php echo wp_json_encode($templates); ?>;
						var siteUrl = <?php echo wp_json_encode(home_url()); ?>;

						function updateSmsTemplateDescription() {
							var $select = $('#um-send-sms-template');
							var desc = $select.find('option:selected').data('description') || '';
							$('.um-sms-template-description-note').text(desc);
						}

						function updatePhoneListFromSelectors() {
							var allPhones = [];
							var selectedRoles = [];
							var selectedLists = [];

							$('.um-sms-role-checkbox:checked').each(function() {
								var roleName = $(this).closest('label').find('strong').text();
								selectedRoles.push(roleName);
								var phones = ($(this).data('phones') || '').toString();
								if (phones) {
									allPhones = allPhones.concat(phones.split('\n'));
								}
							});

							$('.um-sms-list-checkbox:checked').each(function() {
								var listName = $(this).closest('label').find('strong').text();
								selectedLists.push(listName);
								var phones = ($(this).data('phones') || '').toString();
								if (phones) {
									allPhones = allPhones.concat(phones.split('\n'));
								}
							});

							var seen = {};
							var uniquePhones = [];
							$.each(allPhones, function(_, phone) {
								phone = $.trim(phone || '');
								if (!phone || seen[phone]) {
									return;
								}
								seen[phone] = true;
								uniquePhones.push(phone);
							});

							$('#um-send-sms-phone-numbers').val(uniquePhones.join('\n'));

							$('#um-send-sms-selected-roles').remove();
							$('#um-send-sms-selected-lists').remove();

							if (selectedRoles.length) {
								$('#um-send-sms-text-form').append('<input type="hidden" id="um-send-sms-selected-roles" name="selected_roles" value="' + $('<div>').text(selectedRoles.join(', ')).html() + '" />');
							}
							if (selectedLists.length) {
								$('#um-send-sms-text-form').append('<input type="hidden" id="um-send-sms-selected-lists" name="selected_lists" value="' + $('<div>').text(selectedLists.join(', ')).html() + '" />');
							}
						}

						function buildSmsPreviewMessage() {
							var templateId = $('#um-send-sms-template').val() || '';
							var template = smsTemplates[templateId] || {};
							var body = (template.body || 'Hi %FIRSTNAME%, login here: %SITEURL%%LOGINURL% (%USERNAME%).').toString();

							var phoneRaw = ($('#um-send-sms-phone-numbers').val() || '').split(/\r?\n/);
							var phone = $.trim(phoneRaw[0] || '+15551234567');
							var username = (phone || '').replace(/\D+/g, '') || 'user';
							var loginUrl = $('#um-send-sms-login-url').val() || '/my-account/';
							var couponCode = $('#um-send-sms-coupon-code').val() || 'SAMPLECOUPON123';

							var replacements = {
								'%SITEURL%': siteUrl,
								'%LOGINURL%': loginUrl,
								'%USERNAME%': username,
								'%PASSWORD%': '••••••••',
								'%EMAIL%': '',
								'%FIRSTNAME%': '',
								'%LASTNAME%': '',
								'%PASSWORDRESETURL%': siteUrl + '/my-account/lost-password/',
								'%COUPONCODE%': couponCode,
								'%PHONENUMBER%': phone
							};

							Object.keys(replacements).forEach(function(key) {
								body = body.split(key).join(replacements[key]);
							});

							return {
								phone: phone || '+15551234567',
								message: $.trim(body)
							};
						}

						$('#um-send-sms-template').on('change', updateSmsTemplateDescription);
						$('.um-sms-role-checkbox, .um-sms-list-checkbox').on('change', updatePhoneListFromSelectors);
						updateSmsTemplateDescription();

						$('#um-preview-sms-text-btn').on('click', function() {
							var preview = buildSmsPreviewMessage();
							$('#um-sms-preview-to').text(preview.phone);
							$('#um-sms-preview-text').text(preview.message);
							$('#um-sms-preview-modal').show();
						});

						$('#um-sms-preview-modal .um-modal-close, #um-sms-preview-modal .um-modal-overlay').on('click', function() {
							$('#um-sms-preview-modal').hide();
						});
					});
					</script>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Build phone lists by role.
	 *
	 * @return array<string,array{name:string,count:int,phones:array<int,string>}>
	 */
	private static function get_role_phone_data(): array {
		global $wp_roles;
		$role_data = [];
		$roles = is_object($wp_roles) ? $wp_roles->get_names() : [];

		foreach ($roles as $role_key => $role_name) {
			$users = get_users([
				'role'   => $role_key,
				'fields' => ['ID'],
			]);
			$phones = [];
			foreach ($users as $user_obj) {
				$user_id = isset($user_obj->ID) ? (int) $user_obj->ID : 0;
				if ($user_id <= 0) {
					continue;
				}
				$primary_phone = User_Manager_SMS::get_user_primary_phone($user_id);
				if ($primary_phone !== '' && User_Manager_SMS::is_valid_phone_number($primary_phone)) {
					$phones[] = $primary_phone;
				}
			}

			$phones = array_values(array_unique($phones));
			$role_data[$role_key] = [
				'name'  => (string) $role_name,
				'count' => count($phones),
				'phones' => $phones,
			];
		}

		return $role_data;
	}

	/**
	 * Convert shared email lists into phone lists by mapping each list email to a user phone.
	 *
	 * @param array<string,array<string,mixed>> $custom_lists
	 * @return array<string,array<int,string>>
	 */
	private static function get_list_phone_data(array $custom_lists): array {
		$data = [];
		$email_phone_cache = [];

		foreach ($custom_lists as $list_id => $list_data) {
			$emails = isset($list_data['emails']) && is_array($list_data['emails']) ? $list_data['emails'] : [];
			$list_phones = [];

			foreach ($emails as $email_raw) {
				$email = sanitize_email((string) $email_raw);
				if ($email === '') {
					continue;
				}
				if (!array_key_exists($email, $email_phone_cache)) {
					$user = get_user_by('email', $email);
					$email_phone_cache[$email] = ($user && isset($user->ID)) ? User_Manager_SMS::get_user_primary_phone((int) $user->ID) : '';
				}
				$phone = (string) $email_phone_cache[$email];
				if ($phone !== '' && User_Manager_SMS::is_valid_phone_number($phone)) {
					$list_phones[] = $phone;
				}
			}

			$data[(string) $list_id] = array_values(array_unique($list_phones));
		}

		return $data;
	}
}

