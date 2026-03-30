<?php
/**
 * Add-on card: Data Anonymizer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Data_Anonymizer {

	public static function render(array $settings): void {
		$enabled = !empty($settings['data_anonymizer_enabled']);
		$history = get_option('user_manager_data_anonymizer_history', []);
		if (!is_array($history)) {
			$history = [];
		}
		$history = array_slice($history, 0, 200);

		$current_user_id = get_current_user_id();
		$last_run = get_transient('um_data_anonymizer_last_run_' . $current_user_id);
		if (is_array($last_run)) {
			delete_transient('um_data_anonymizer_last_run_' . $current_user_id);
		} else {
			$last_run = null;
		}

		$excluded_domains = isset($settings['data_anonymizer_excluded_email_domains'])
			? (string) $settings['data_anonymizer_excluded_email_domains']
			: '';
		$exclude_wp_administrators = array_key_exists('data_anonymizer_exclude_wp_administrators', $settings)
			? !empty($settings['data_anonymizer_exclude_wp_administrators'])
			: true;
		$exclude_admin_email_match = array_key_exists('data_anonymizer_exclude_if_matches_admin_email', $settings)
			? !empty($settings['data_anonymizer_exclude_if_matches_admin_email'])
			: true;
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-data-anonymizer" data-um-active-selectors="#um-data-anonymizer-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-shield-alt"></span>
				<h2><?php esc_html_e('Data Anonymizer', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-data-anonymizer-enabled" name="data_anonymizer_enabled" value="1" <?php checked($enabled); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Anonymize order, user, and supported form-submission data using configurable replacement rules.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-data-anonymizer-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<?php if ($last_run !== null) : ?>
						<div class="notice notice-<?php echo !empty($last_run['status']) && $last_run['status'] === 'error' ? 'error' : 'success'; ?> inline">
							<p><?php echo esc_html((string) ($last_run['message'] ?? __('Data Anonymizer run completed.', 'user-manager'))); ?></p>
						</div>
					<?php endif; ?>

					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr; gap:20px;">
						<div class="um-admin-card" style="margin:0;">
							<div class="um-admin-card-header">
								<span class="dashicons dashicons-cart"></span>
								<h3 style="margin:0;"><?php esc_html_e('Order Data', 'user-manager'); ?></h3>
							</div>
							<div class="um-admin-card-body">
								<label style="display:block;margin:0 0 10px;">
									<input type="checkbox" name="data_anonymizer_order_address_random" value="1" <?php checked(!empty($settings['data_anonymizer_order_address_random'])); ?> />
									<?php esc_html_e('Replace All Order Address Data with Random Values', 'user-manager'); ?>
								</label>
								<label style="display:block;margin:0 0 10px;">
									<input type="checkbox" name="data_anonymizer_order_phone_fixed" value="1" <?php checked(!empty($settings['data_anonymizer_order_phone_fixed'])); ?> />
									<?php esc_html_e('Replace All Order Phone Number Data with 555-555-5555', 'user-manager'); ?>
								</label>
								<label style="display:block;margin:0 0 10px;">
									<input type="checkbox" name="data_anonymizer_order_email_random" value="1" <?php checked(!empty($settings['data_anonymizer_order_email_random'])); ?> />
									<?php esc_html_e('Replace All Order Email Address Data with Random Email Addresses', 'user-manager'); ?>
								</label>
								<label style="display:block;margin:0;">
									<input type="checkbox" name="data_anonymizer_order_notes_random" value="1" <?php checked(!empty($settings['data_anonymizer_order_notes_random'])); ?> />
									<?php esc_html_e('Replace All Order Note Data with Random Notes', 'user-manager'); ?>
								</label>
							</div>
						</div>

						<div class="um-admin-card" style="margin:0;">
							<div class="um-admin-card-header">
								<span class="dashicons dashicons-admin-users"></span>
								<h3 style="margin:0;"><?php esc_html_e('User Data', 'user-manager'); ?></h3>
							</div>
							<div class="um-admin-card-body">
								<label style="display:block;margin:0 0 10px;">
									<input type="checkbox" name="data_anonymizer_user_meta_address_random" value="1" <?php checked(!empty($settings['data_anonymizer_user_meta_address_random'])); ?> />
									<?php esc_html_e('Replace All User Meta Address Data with Random Values', 'user-manager'); ?>
								</label>
								<label style="display:block;margin:0 0 10px;">
									<input type="checkbox" name="data_anonymizer_user_meta_phone_fixed" value="1" <?php checked(!empty($settings['data_anonymizer_user_meta_phone_fixed'])); ?> />
									<?php esc_html_e('Replace All User Meta Phone Number Data with 555-555-5555', 'user-manager'); ?>
								</label>
								<label style="display:block;margin:0 0 10px;">
									<input type="checkbox" name="data_anonymizer_user_email_random" value="1" <?php checked(!empty($settings['data_anonymizer_user_email_random'])); ?> />
									<?php esc_html_e('Replace All User Email Address Data with Random Email Addresses', 'user-manager'); ?>
								</label>
								<label style="display:block;margin:0;">
									<input type="checkbox" name="data_anonymizer_user_login_random" value="1" <?php checked(!empty($settings['data_anonymizer_user_login_random'])); ?> />
									<?php esc_html_e('Replace All User Logins with Random Logins', 'user-manager'); ?>
								</label>
							</div>
						</div>
					</div>

					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr; gap:20px; margin-top:14px;">
						<div class="um-admin-card" style="margin:0;">
							<div class="um-admin-card-header">
								<span class="dashicons dashicons-feedback"></span>
								<h3 style="margin:0;"><?php esc_html_e('Form Data', 'user-manager'); ?></h3>
							</div>
							<div class="um-admin-card-body">
								<label style="display:block;margin:0 0 8px;">
									<input type="checkbox" name="data_anonymizer_forms_random" value="1" <?php checked(!empty($settings['data_anonymizer_forms_random'])); ?> />
									<?php esc_html_e('Replace All Form Submissions with Random Data', 'user-manager'); ?>
								</label>
								<p class="description" style="margin:0;">
									<?php esc_html_e('Looks for common storage tables used by major form plugins (details also listed in run history results).', 'user-manager'); ?>
								</p>
							</div>
						</div>

						<div class="um-admin-card" style="margin:0;">
							<div class="um-admin-card-header">
								<span class="dashicons dashicons-filter"></span>
								<h3 style="margin:0;"><?php esc_html_e('Exceptions to Above', 'user-manager'); ?></h3>
							</div>
							<div class="um-admin-card-body">
								<label style="display:block;margin:0 0 10px;">
									<input type="checkbox" name="data_anonymizer_exclude_wp_administrators" value="1" <?php checked($exclude_wp_administrators); ?> />
									<?php esc_html_e('Exclude All WP Administrators', 'user-manager'); ?>
								</label>
								<label style="display:block;margin:0 0 12px;">
									<input type="checkbox" name="data_anonymizer_exclude_if_matches_admin_email" value="1" <?php checked($exclude_admin_email_match); ?> />
									<?php esc_html_e('Exclude User if Email Address Matches Administration Email Address', 'user-manager'); ?>
								</label>
								<label for="um-data-anonymizer-excluded-email-domains" style="display:block;">
									<?php esc_html_e('Email Domains to Exclude from Email Address Changes and Login Changes (comma separated)', 'user-manager'); ?>
								</label>
								<input
									type="text"
									class="regular-text"
									style="width:100%;max-width:none;"
									id="um-data-anonymizer-excluded-email-domains"
									name="data_anonymizer_excluded_email_domains"
									value="<?php echo esc_attr($excluded_domains); ?>"
									placeholder="example.com, internal.local"
								/>
								<p class="description" style="margin:8px 0 0;">
									<?php esc_html_e('If a user/order email contains any listed domain, that record will keep its current email and login.', 'user-manager'); ?>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="um-admin-card" id="um-data-anonymizer-run-card" style="margin-top:18px;<?php echo $enabled ? '' : 'display:none;'; ?>">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-controls-play"></span>
				<h2><?php esc_html_e('Run Data Anonymizer', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<?php wp_nonce_field('user_manager_data_anonymizer_run', 'user_manager_data_anonymizer_nonce', false); ?>
				<p class="description" style="margin-top:0;">
					<?php esc_html_e('These actions use the checked settings above at run time and save a history record with counts and selected options.', 'user-manager'); ?>
				</p>
				<p style="display:flex; gap:10px; flex-wrap:wrap; margin:12px 0 0;">
					<button type="submit" class="button button-primary um-addon-action-submit" data-um-target-action="user_manager_run_data_anonymizer_orders">
						<?php esc_html_e('Run Data Anonymizer for Orders', 'user-manager'); ?>
					</button>
					<button type="submit" class="button button-primary um-addon-action-submit" data-um-target-action="user_manager_run_data_anonymizer_users">
						<?php esc_html_e('Run Data Anonymizer for Users', 'user-manager'); ?>
					</button>
					<button type="submit" class="button button-primary um-addon-action-submit" data-um-target-action="user_manager_run_data_anonymizer_forms">
						<?php esc_html_e('Run Data Anonymizer for Forms', 'user-manager'); ?>
					</button>
				</p>
			</div>
		</div>

		<div class="um-admin-card" style="margin-top:18px;">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-backup"></span>
				<h2><?php esc_html_e('Data Anonymizer History', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<?php if (empty($history)) : ?>
					<p class="description"><?php esc_html_e('No Data Anonymizer history yet.', 'user-manager'); ?></p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e('Timestamp', 'user-manager'); ?></th>
								<th><?php esc_html_e('Run Type', 'user-manager'); ?></th>
								<th><?php esc_html_e('Run By', 'user-manager'); ?></th>
								<th><?php esc_html_e('Settings Checked', 'user-manager'); ?></th>
								<th><?php esc_html_e('Records Affected', 'user-manager'); ?></th>
								<th><?php esc_html_e('Counts / Notes', 'user-manager'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($history as $row) : ?>
								<?php
								$created_at = isset($row['created_at']) ? (string) $row['created_at'] : '';
								$run_type = isset($row['run_type']) ? (string) $row['run_type'] : '—';
								$run_by = isset($row['run_by']) ? (int) $row['run_by'] : 0;
								$records_affected = isset($row['records_affected']) ? (int) $row['records_affected'] : 0;
								$settings_checked = isset($row['settings_checked']) && is_array($row['settings_checked']) ? $row['settings_checked'] : [];
								$counts = isset($row['counts']) && is_array($row['counts']) ? $row['counts'] : [];
								$notes = isset($row['notes']) && is_array($row['notes']) ? $row['notes'] : [];
								$supported_form_plugins = isset($row['supported_form_plugins']) && is_array($row['supported_form_plugins']) ? $row['supported_form_plugins'] : [];

								$run_user_display = '—';
								if ($run_by > 0) {
									$run_user = get_userdata($run_by);
									if ($run_user && !empty($run_user->user_email)) {
										$run_user_display = (string) $run_user->user_email;
									}
								}
								?>
								<tr>
									<td><?php echo esc_html($created_at !== '' ? $created_at : '—'); ?></td>
									<td><?php echo esc_html(ucfirst($run_type)); ?></td>
									<td><?php echo esc_html($run_user_display); ?></td>
									<td>
										<?php if (empty($settings_checked)) : ?>
											—
										<?php else : ?>
											<ul style="margin:0;padding-left:18px;">
												<?php foreach ($settings_checked as $item) : ?>
													<li><?php echo esc_html((string) $item); ?></li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html((string) $records_affected); ?></td>
									<td>
										<?php if (!empty($counts)) : ?>
											<div style="margin-bottom:8px;">
												<strong><?php esc_html_e('Counts', 'user-manager'); ?>:</strong>
												<ul style="margin:4px 0 0;padding-left:18px;">
													<?php foreach ($counts as $count_key => $count_value) : ?>
														<li><?php echo esc_html((string) $count_key . ': ' . (string) $count_value); ?></li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>
										<?php if (!empty($notes)) : ?>
											<div style="margin-bottom:8px;">
												<strong><?php esc_html_e('Notes', 'user-manager'); ?>:</strong>
												<ul style="margin:4px 0 0;padding-left:18px;">
													<?php foreach ($notes as $note_item) : ?>
														<li><?php echo esc_html((string) $note_item); ?></li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>
										<?php if (!empty($supported_form_plugins)) : ?>
											<div>
												<strong><?php esc_html_e('Supported Form Plugins', 'user-manager'); ?>:</strong>
												<ul style="margin:4px 0 0;padding-left:18px;">
													<?php foreach ($supported_form_plugins as $plugin_item) : ?>
														<li><?php echo esc_html((string) $plugin_item); ?></li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

