<?php
/**
 * Bulk Create/Import tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Bulk_Create {

	public static function render(): void {
		$roles     = User_Manager_Core::get_user_roles();
		$templates = User_Manager_Core::get_email_templates();
		$settings  = User_Manager_Core::get_settings();
		$paste_default_columns = isset($settings['paste_default_columns']) && $settings['paste_default_columns'] !== ''
			? $settings['paste_default_columns']
			: 'email,first_name,last_name,role,username,password';
		$paste_column_headers = array_map('trim', array_filter(explode(',', $paste_default_columns)));
		if (empty($paste_column_headers)) {
			$paste_column_headers = ['email', 'first_name', 'last_name', 'role', 'username', 'password'];
		}
		$activity  = User_Manager_Core::get_activity_log();

		// Normalize activity log structure (supports both flat arrays and ['entries' => []]).
		$entries = $activity['entries'] ?? $activity;
		if (!is_array($entries)) {
			$entries = [];
		}

		// Filter to only entries from Bulk Create tool.
		$recent_bulk = array_filter($entries, static function ($entry) {
			return is_array($entry)
				&& isset($entry['tool'])
				&& in_array($entry['tool'], ['Bulk Create', 'Bulk Create (Updated)'], true);
		});
		$recent_bulk = array_slice($recent_bulk, 0, 15);
		?>
		<div class="um-admin-grid" style="grid-template-columns: 1fr 1fr;">
			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-upload"></span>
					<h2><?php esc_html_e('Upload CSV File', 'user-manager'); ?></h2>
					<a href="<?php echo esc_url(admin_url('admin-post.php?action=user_manager_download_sample_csv&_wpnonce=' . wp_create_nonce('user_manager_download_sample_csv'))); ?>" class="button button-secondary" style="margin-left: auto;">
						<span class="dashicons dashicons-download" style="vertical-align: middle; margin-right: 4px;"></span>
						<?php esc_html_e('Download Sample CSV', 'user-manager'); ?>
					</a>
				</div>
				<div class="um-admin-card-body">
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
						<input type="hidden" name="action" value="user_manager_bulk_create" />
						<input type="hidden" name="bulk_method" value="file" />
						<?php wp_nonce_field('user_manager_bulk_create'); ?>
						
						<div class="um-form-field">
							<label for="um-csv-file"><?php esc_html_e('CSV File', 'user-manager'); ?></label>
							<input type="file" name="csv_file" id="um-csv-file" accept=".csv" />
						</div>
						
						<div class="um-form-field">
							<label for="um-bulk-role"><?php esc_html_e('Default Role', 'user-manager'); ?></label>
							<select name="default_role" id="um-bulk-role" class="regular-text">
								<?php foreach ($roles as $key => $name) : ?>
									<option value="<?php echo esc_attr($key); ?>" <?php selected($key, 'customer'); ?>><?php echo esc_html($name); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e('Used when role is not specified in CSV.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="um-bulk-login-url"><?php esc_html_e('Login URL (for template)', 'user-manager'); ?></label>
							<select name="login_url" id="um-bulk-login-url" class="regular-text">
								<option value="/my-account/"><?php esc_html_e('My Account', 'user-manager'); ?></option>
								<option value="/wp-admin/"><?php esc_html_e('WP Admin', 'user-manager'); ?></option>
								<option value="/wp-login.php?saml_sso=false"><?php esc_html_e('WP Admin SSO Bypass', 'user-manager'); ?></option>
							</select>
							<p class="description"><?php esc_html_e('Used for %LOGINURL% placeholder in email template.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="um-bulk-coupon-code"><?php esc_html_e('Coupon code (for %COUPONCODE% in template)', 'user-manager'); ?></label>
							<input type="text" name="coupon_code_for_template" id="um-bulk-coupon-code" class="regular-text" list="um-bulk-coupon-code-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or leave empty', 'user-manager'); ?>" value="" autocomplete="off" />
							<datalist id="um-bulk-coupon-code-datalist"></datalist>
							<p class="description"><?php esc_html_e('When the selected template contains %COUPONCODE%, this code is used in the email. Leave empty to omit.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="um-bulk-allow-email-coupon"><?php esc_html_e('Also Append Each Email to "Allowed Emails" on Coupon', 'user-manager'); ?></label>
							<input type="text" name="allow_email_coupon_code" id="um-bulk-allow-email-coupon" class="regular-text" list="um-bulk-allow-email-coupon-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or leave empty for none', 'user-manager'); ?>" value="" autocomplete="off" />
							<datalist id="um-bulk-allow-email-coupon-datalist"></datalist>
							<p class="description">
								<?php esc_html_e('Optional. When set, each valid user email created from this CSV will also be added to the “Allowed emails” / email restrictions on the selected WooCommerce coupon.', 'user-manager'); ?>
							</p>
						</div>
						
						<div class="um-send-email-option">
							<div class="um-form-field-inline">
								<input type="checkbox" name="send_email" id="um-bulk-send-email" value="1" />
								<label for="um-bulk-send-email"><strong><?php esc_html_e('Send Welcome Emails', 'user-manager'); ?></strong></label>
							</div>
							<p class="description" style="margin-top:6px;">
								<?php esc_html_e('Choose an email template that fits bulk user creation. Coupon-related templates may not behave as expected here because the Bulk Create tool does not create any coupons.', 'user-manager'); ?>
							</p>
							
							<div id="um-bulk-template-select" style="margin-top:12px;display:none;">
								<label for="um-bulk-template">
									<?php esc_html_e('Select Email Template:', 'user-manager'); ?>
									<?php User_Manager_Tab_Shared::render_template_settings_shortcut('email'); ?>
								</label>
								<select name="email_template" id="um-bulk-template" class="regular-text" style="margin-top:6px;">
									<option value=""><?php esc_html_e('— Default Template —', 'user-manager'); ?></option>
									<?php foreach ($templates as $id => $template) : ?>
										<option value="<?php echo esc_attr($id); ?>" data-subject="<?php echo esc_attr($template['subject']); ?>" data-body="<?php echo esc_attr($template['body']); ?>" data-description="<?php echo esc_attr($template['description'] ?? ''); ?>"><?php echo esc_html($template['title']); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description um-template-description-note" style="margin-top:6px;"></p>
								<p style="margin-top:8px;">
									<button type="button" class="button um-preview-bulk-email-btn"><?php esc_html_e('Preview Email (First Record)', 'user-manager'); ?></button>
								</p>
							</div>
						</div>
						
						<p style="margin-top:20px;">
							<?php submit_button(__('Upload & Create Users', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</form>
				</div>
			</div>
			
			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-editor-paste-text"></span>
					<h2><?php esc_html_e('Paste from Spreadsheet', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-paste-form">
						<input type="hidden" name="action" value="user_manager_bulk_create" />
						<input type="hidden" name="bulk_method" value="paste" />
						<?php wp_nonce_field('user_manager_bulk_create'); ?>
						
						<div class="um-form-field">
							<label for="um-paste-data"><?php esc_html_e('Paste Data', 'user-manager'); ?></label>
							<textarea name="paste_data" id="um-paste-data" rows="8" class="large-text code" placeholder="<?php esc_attr_e("email@example.com\tJohn\tDoe\tcustomer\tjohn.doe", 'user-manager'); ?>"></textarea>
							<p class="description"><?php esc_html_e('Copy and paste tab-separated data directly from Excel or Google Sheets.', 'user-manager'); ?></p>
							<p class="description"><?php esc_html_e('Required column: email. Optional columns and their default order (when no header row is used) can be changed under Settings → User & Login (“Paste from Spreadsheet: default column order”).', 'user-manager'); ?></p>
							<p class="description" style="margin-top: 8px;">
								<strong><?php esc_html_e('Required column:', 'user-manager'); ?></strong> <code>email</code><br />
								<strong><?php esc_html_e('Default column order (when no header row):', 'user-manager'); ?></strong><br />
								<code style="display: inline-block; margin-top: 4px; padding: 6px 8px; background: #f0f0f1;"><?php echo esc_html(implode(' &nbsp;&#124;&nbsp; ', $paste_column_headers)); ?></code>
							</p>
							<?php
							$current_user = wp_get_current_user();
							if ($current_user->user_email && strpos($current_user->user_email, '@') !== false) {
								$date_part = current_time('Ymd-His');
								$parts = explode('@', $current_user->user_email, 2);
								$local = $parts[0];
								$domain = $parts[1];
								$quick_test_lines = [];
								for ($i = 1; $i <= 4; $i++) {
									$suffix = $date_part . '-' . $i;
									$quick_email = $local . '+' . $suffix . '@' . $domain;
									$quick_username = $local . '+' . $date_part . '-' . $i;
									$quick_test_lines[] = $quick_email . "\tTest\tUser\tcustomer\t" . $quick_username;
								}
								$quick_test_text = implode("\n", $quick_test_lines);
								?>
								<div class="um-form-field" style="margin-top: 12px;">
									<label for="um-quick-test-users"><?php esc_html_e('Quick Test Users', 'user-manager'); ?></label>
									<textarea id="um-quick-test-users" rows="5" class="large-text code" readonly style="cursor: text; background: #f6f7f7;"><?php echo esc_textarea($quick_test_text); ?></textarea>
									<p class="description"><?php esc_html_e('Select all and copy, then paste into the Paste Data field above to quickly create a few dummy test users.', 'user-manager'); ?></p>
								</div>
							<?php } ?>
						</div>
						
						<div class="um-form-field">
							<label for="um-paste-role"><?php esc_html_e('Default Role', 'user-manager'); ?></label>
							<select name="default_role" id="um-paste-role" class="regular-text">
								<?php foreach ($roles as $key => $name) : ?>
									<option value="<?php echo esc_attr($key); ?>" <?php selected($key, 'customer'); ?>><?php echo esc_html($name); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						
						<div class="um-form-field">
							<label for="um-paste-login-url"><?php esc_html_e('Login URL (for template)', 'user-manager'); ?></label>
							<select name="login_url" id="um-paste-login-url" class="regular-text">
								<option value="/my-account/"><?php esc_html_e('My Account', 'user-manager'); ?></option>
								<option value="/wp-admin/"><?php esc_html_e('WP Admin', 'user-manager'); ?></option>
								<option value="/wp-login.php?saml_sso=false"><?php esc_html_e('WP Admin SSO Bypass', 'user-manager'); ?></option>
							</select>
							<p class="description"><?php esc_html_e('Used for %LOGINURL% placeholder in email template.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="um-paste-coupon-code"><?php esc_html_e('Coupon code (for %COUPONCODE% in template)', 'user-manager'); ?></label>
							<input type="text" name="coupon_code_for_template" id="um-paste-coupon-code" class="regular-text" list="um-paste-coupon-code-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or leave empty', 'user-manager'); ?>" value="" autocomplete="off" />
							<datalist id="um-paste-coupon-code-datalist"></datalist>
							<p class="description"><?php esc_html_e('When the selected template contains %COUPONCODE%, this code is used in the email. Leave empty to omit.', 'user-manager'); ?></p>
						</div>
						
						<div class="um-form-field">
							<label for="um-paste-allow-email-coupon"><?php esc_html_e('Also Append Each Email to "Allowed Emails" on Coupon', 'user-manager'); ?></label>
							<input type="text" name="allow_email_coupon_code" id="um-paste-allow-email-coupon" class="regular-text" list="um-paste-allow-email-coupon-datalist" data-um-lazy-datalist-source="coupon_codes" placeholder="<?php esc_attr_e('Type to search or leave empty for none', 'user-manager'); ?>" value="" autocomplete="off" />
							<datalist id="um-paste-allow-email-coupon-datalist"></datalist>
							<p class="description">
								<?php esc_html_e('Optional. When set, each valid user email created from this pasted data will also be added to the “Allowed emails” / email restrictions on the selected WooCommerce coupon.', 'user-manager'); ?>
							</p>
						</div>
						
						<div class="um-send-email-option">
							<div class="um-form-field-inline">
								<input type="checkbox" name="send_email" id="um-paste-send-email" value="1" />
								<label for="um-paste-send-email"><strong><?php esc_html_e('Send Welcome Emails', 'user-manager'); ?></strong></label>
							</div>
							<p class="description" style="margin-top:6px;">
								<?php esc_html_e('Choose an email template that fits bulk user creation. Coupon-related templates may not behave as expected here because the Bulk Create tool does not create any coupons.', 'user-manager'); ?>
							</p>
							
							<div id="um-paste-template-select" style="margin-top:12px;display:none;">
								<label for="um-paste-template">
									<?php esc_html_e('Select Email Template:', 'user-manager'); ?>
									<?php User_Manager_Tab_Shared::render_template_settings_shortcut('email'); ?>
								</label>
								<select name="email_template" id="um-paste-template" class="regular-text" style="margin-top:6px;">
									<option value=""><?php esc_html_e('— Default Template —', 'user-manager'); ?></option>
									<?php foreach ($templates as $id => $template) : ?>
										<option value="<?php echo esc_attr($id); ?>" data-subject="<?php echo esc_attr($template['subject']); ?>" data-body="<?php echo esc_attr($template['body']); ?>" data-description="<?php echo esc_attr($template['description'] ?? ''); ?>"><?php echo esc_html($template['title']); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description um-template-description-note" style="margin-top:6px;"></p>
								<p style="margin-top:8px;">
									<button type="button" class="button um-preview-paste-email-btn"><?php esc_html_e('Preview Email (First Record)', 'user-manager'); ?></button>
								</p>
							</div>
						</div>
						
						<p style="margin-top:20px;">
							<?php submit_button(__('Create Users', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
					</form>
				</div>
			</div>
		</div>
		
		<div style="margin-top: 20px;">
			<?php self::render_sftp_file_browser($roles, $templates); ?>
		</div>
		
		<div class="um-admin-grid" style="grid-template-columns: 1fr 1fr; margin-top: 20px;">
			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-info-outline"></span>
					<h2><?php esc_html_e('CSV Format Guide', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-doc-section">
						<h3><?php esc_html_e('Required & Optional Columns', 'user-manager'); ?></h3>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e('Column', 'user-manager'); ?></th>
									<th><?php esc_html_e('Required', 'user-manager'); ?></th>
									<th><?php esc_html_e('Description', 'user-manager'); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><code>email</code></td>
									<td><span class="um-status-badge um-status-error"><?php esc_html_e('Required', 'user-manager'); ?></span></td>
									<td><?php esc_html_e('User email address', 'user-manager'); ?></td>
								</tr>
								<tr>
									<td><code>username</code></td>
									<td><span class="um-status-badge um-status-info"><?php esc_html_e('Optional', 'user-manager'); ?></span></td>
									<td><?php esc_html_e('Login username (defaults to email)', 'user-manager'); ?></td>
								</tr>
								<tr>
									<td><code>first_name</code></td>
									<td><span class="um-status-badge um-status-info"><?php esc_html_e('Optional', 'user-manager'); ?></span></td>
									<td><?php esc_html_e('User first name', 'user-manager'); ?></td>
								</tr>
								<tr>
									<td><code>last_name</code></td>
									<td><span class="um-status-badge um-status-info"><?php esc_html_e('Optional', 'user-manager'); ?></span></td>
									<td><?php esc_html_e('User last name', 'user-manager'); ?></td>
								</tr>
								<tr>
									<td><code>password</code></td>
									<td><span class="um-status-badge um-status-info"><?php esc_html_e('Optional', 'user-manager'); ?></span></td>
									<td><?php esc_html_e('Custom password (auto-generated if empty)', 'user-manager'); ?></td>
								</tr>
								<tr>
									<td><code>role</code></td>
									<td><span class="um-status-badge um-status-info"><?php esc_html_e('Optional', 'user-manager'); ?></span></td>
									<td><?php esc_html_e('User role (uses default if empty)', 'user-manager'); ?></td>
								</tr>
								<tr>
									<td><code>coupon_email_append</code></td>
									<td><span class="um-status-badge um-status-info"><?php esc_html_e('Optional', 'user-manager'); ?></span></td>
									<td><?php esc_html_e('WooCommerce coupon code. When set, this row’s email will be added to that coupon’s “Allowed emails” / email restrictions. Works together with the form-level “Also Append Each Email to Allowed Emails on Coupon” option.', 'user-manager'); ?></td>
								</tr>
								<tr>
									<td><code>any_other_column</code></td>
									<td><span class="um-status-badge um-status-info"><?php esc_html_e('Optional', 'user-manager'); ?></span></td>
									<td><?php esc_html_e('Any additional column will be saved as user meta using the column name as the meta key (e.g., company, phone, department).', 'user-manager'); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
					
					<div class="um-doc-section">
						<h3><?php esc_html_e('Example CSV', 'user-manager'); ?></h3>
						<div class="um-code-block">email,first_name,last_name,role,username,company,phone,coupon_email_append
john@example.com,John,Doe,customer,john.doe,Acme Inc,555-0100,
jane@example.com,Jane,Smith,subscriber,jane.smith,Globex,555-0101,SUMMER20
admin@example.com,Admin,User,administrator,admin.user,Initech,555-0102,WELCOME10</div>
					</div>
					
					<div class="um-doc-section">
						<h3><?php esc_html_e('Paste Format (Tab-Separated)', 'user-manager'); ?></h3>
						<p><?php esc_html_e('When pasting from a spreadsheet, use a header row with column names (e.g. email, first_name, last_name, role, username, coupon_email_append). Any extra columns will be stored as user meta.', 'user-manager'); ?></p>
						<div class="um-code-block">email	first_name	last_name	role	username	company	phone	coupon_email_append</div>
						<p class="description"><?php esc_html_e('First row can be a header row (will be auto-detected and skipped).', 'user-manager'); ?></p>
						<p class="description"><?php esc_html_e('Use the coupon_email_append column to add each row’s email to a coupon’s “Allowed emails” by coupon code.', 'user-manager'); ?></p>
						<p class="description"><?php esc_html_e('When pasting without a header row, the default column order is set under Settings → User & Login (“Paste from Spreadsheet: default column order”).', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
			
			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-clock"></span>
					<h2><?php esc_html_e('Recent Bulk Creates', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<?php if (empty($recent_bulk)) : ?>
						<p class="um-empty-message"><?php esc_html_e('No bulk creates yet.', 'user-manager'); ?></p>
					<?php else : ?>
						<ul class="um-recent-users-list">
							<?php foreach ($recent_bulk as $entry) : ?>
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
		
		
		<!-- Email Preview Modal -->
		<?php User_Manager_Tab_Shared::render_email_preview_modal($templates); ?>
		
		<script>
		jQuery(document).ready(function($) {
			$('#um-bulk-send-email').on('change', function() {
				$('#um-bulk-template-select').toggle(this.checked);
			});
			$('#um-paste-send-email').on('change', function() {
				$('#um-paste-template-select').toggle(this.checked);
			});
			
			function umUpdateTemplateDescGeneric($wrapper, $select) {
				var val = $select.val();
				var desc = '';
				if (val && window.umTemplates && umTemplates[val] && umTemplates[val].description) {
					desc = umTemplates[val].description;
				} else {
					var $opt = $select.find('option:selected');
					desc = $opt.data('description') || '';
				}
				var $note = $wrapper.find('.um-template-description-note');
				if ($note.length) {
					$note.text(desc || '');
				}
			}
			$('#um-bulk-template').on('change', function(){ umUpdateTemplateDescGeneric($('#um-bulk-template-select'), $(this)); });
			umUpdateTemplateDescGeneric($('#um-bulk-template-select'), $('#um-bulk-template'));
			$('#um-paste-template').on('change', function(){ umUpdateTemplateDescGeneric($('#um-paste-template-select'), $(this)); });
			umUpdateTemplateDescGeneric($('#um-paste-template-select'), $('#um-paste-template'));
			
			$('.um-preview-bulk-email-btn').on('click', function() {
				umShowEmailPreview('bulk-file');
			});
			
			$('.um-preview-paste-email-btn').on('click', function() {
				umShowEmailPreview('bulk-paste');
			});
		});
		</script>
		<?php
	}
	
	/**
	 * Render SFTP file browser block.
	 */
	private static function render_sftp_file_browser(array $roles, array $templates): void {
		$directories = User_Manager_Core::get_sftp_directories();
		
		if (empty($directories)) {
			return; // Don't show if no directories configured
		}
		
		$csv_files = User_Manager_Core::get_sftp_csv_files();
		?>
		<div class="um-admin-card um-admin-card-full">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-networking"></span>
				<h2><?php esc_html_e('Import from Directory', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<!-- Directory Status -->
				<div class="um-directory-status" style="margin-bottom: 20px;">
					<p><strong><?php esc_html_e('Configured directories:', 'user-manager'); ?></strong></p>
					<table class="widefat striped" style="max-width: 800px;">
						<thead>
							<tr>
								<th><?php esc_html_e('Directory Path', 'user-manager'); ?></th>
								<th style="width: 100px;"><?php esc_html_e('Status', 'user-manager'); ?></th>
								<th style="width: 150px;"><?php esc_html_e('Action', 'user-manager'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($directories as $dir) : ?>
								<tr>
									<td><code style="font-size: 12px;"><?php echo esc_html($dir); ?></code></td>
									<td>
										<?php if (is_dir($dir)) : ?>
											<span class="um-status-badge um-status-success"><?php esc_html_e('Exists', 'user-manager'); ?></span>
										<?php else : ?>
											<span class="um-status-badge um-status-error"><?php esc_html_e('Not Found', 'user-manager'); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<?php if (!is_dir($dir)) : ?>
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
												<input type="hidden" name="action" value="user_manager_create_directory" />
												<input type="hidden" name="directory" value="<?php echo esc_attr($dir); ?>" />
												<?php wp_nonce_field('user_manager_create_directory'); ?>
												<button type="submit" class="button button-small"><?php esc_html_e('Create Directory', 'user-manager'); ?></button>
											</form>
										<?php else : ?>
											<span style="color: #646970;">—</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				
				<?php if (empty($csv_files)) : ?>
					<div class="um-info-box">
						<span class="dashicons dashicons-info"></span>
						<div>
							<strong><?php esc_html_e('No CSV files found', 'user-manager'); ?></strong>
							<p><?php esc_html_e('No CSV files were found in the configured directories. Upload CSV files to one of the directories above to import users.', 'user-manager'); ?></p>
						</div>
					</div>
				<?php else : ?>
					<table class="um-sftp-files-table widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e('File', 'user-manager'); ?></th>
								<th><?php esc_html_e('Status', 'user-manager'); ?></th>
								<th><?php esc_html_e('Import Options', 'user-manager'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($csv_files as $file) : ?>
								<tr class="<?php echo $file['imported'] ? 'um-file-imported' : ''; ?>">
									<td>
										<strong><?php echo esc_html($file['name']); ?></strong>
										<br><small style="color: #646970;"><?php echo esc_html(size_format($file['size'])); ?> · <?php echo esc_html(User_Manager_Core::nice_time($file['modified'])); ?></small>
									</td>
									<td>
										<?php if ($file['imported']) : ?>
											<span class="um-status-badge um-status-success"><?php esc_html_e('Imported', 'user-manager'); ?></span>
											<br><small style="color: #646970;">
												<?php echo esc_html(User_Manager_Core::nice_time($file['imported_at'])); ?>
												<?php if ($file['activity_log_id']) : ?>
													· <a href="#" class="um-view-import-log" data-log-id="<?php echo esc_attr($file['activity_log_id']); ?>"><?php esc_html_e('View Log', 'user-manager'); ?></a>
												<?php endif; ?>
											</small>
										<?php else : ?>
											<span class="um-status-badge um-status-info"><?php esc_html_e('Not Imported', 'user-manager'); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="um-sftp-import-form">
											<input type="hidden" name="action" value="user_manager_import_sftp_file" />
											<input type="hidden" name="filepath" value="<?php echo esc_attr($file['path']); ?>" />
											<?php wp_nonce_field('user_manager_import_sftp_file'); ?>
											
											<select name="default_role" class="um-sftp-role-select">
												<?php foreach ($roles as $key => $name) : ?>
													<option value="<?php echo esc_attr($key); ?>" <?php selected($key, 'customer'); ?>><?php echo esc_html($name); ?></option>
												<?php endforeach; ?>
											</select>
											
											<label class="um-sftp-email-label">
												<input type="checkbox" name="send_email" value="1" class="um-sftp-send-email" />
												<?php esc_html_e('Email', 'user-manager'); ?>
											</label>
											
											<select name="email_template" class="um-sftp-template-select">
												<option value=""><?php esc_html_e('Default', 'user-manager'); ?></option>
												<?php foreach ($templates as $id => $template) : ?>
													<option value="<?php echo esc_attr($id); ?>" data-description="<?php echo esc_attr($template['description'] ?? ''); ?>"><?php echo esc_html($template['title']); ?></option>
												<?php endforeach; ?>
											</select>
											<span class="um-sftp-template-settings-shortcut" style="display:none;">
												<?php User_Manager_Tab_Shared::render_template_settings_shortcut('email'); ?>
											</span>
											<p class="description um-template-description-note" style="margin-top:6px; display:none;"></p>
											
											<button type="submit" class="button button-primary">
												<?php echo $file['imported'] ? esc_html__('Re-import', 'user-manager') : esc_html__('Import', 'user-manager'); ?>
											</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
		
		<style>
		.um-sftp-files-table {
			margin-top: 0;
		}
		.um-sftp-files-table th,
		.um-sftp-files-table td {
			vertical-align: middle;
		}
		.um-file-imported {
			background: #f0f9f0 !important;
		}
		.um-view-import-log {
			font-size: 12px;
		}
		.um-sftp-import-form {
			display: flex;
			align-items: center;
			gap: 8px;
			flex-wrap: wrap;
		}
		.um-sftp-import-form select {
			width: auto;
			min-width: 100px;
		}
		.um-sftp-import-form .um-sftp-template-select {
			display: none;
		}
		.um-sftp-email-label {
			white-space: nowrap;
		}
		</style>
		
		<script>
		jQuery(document).ready(function($) {
			$('.um-sftp-send-email').on('change', function() {
				var $form = $(this).closest('form');
				$form.find('.um-sftp-template-select').toggle(this.checked);
				$form.find('.um-sftp-template-settings-shortcut').toggle(this.checked);
				$form.find('.um-template-description-note').toggle(this.checked);
				if (this.checked) {
					$form.find('.um-sftp-template-select').trigger('change');
				}
			});
			
			$('.um-sftp-template-select').on('change', function() {
				var $select = $(this);
				var val = $select.val();
				var desc = '';
				if (val && window.umTemplates && umTemplates[val] && umTemplates[val].description) {
					desc = umTemplates[val].description;
				} else {
					var $opt = $select.find('option:selected');
					desc = $opt.data('description') || '';
				}
				$select.closest('form').find('.um-template-description-note').text(desc || '');
			});
		});
		</script>
		<?php
	}
}




