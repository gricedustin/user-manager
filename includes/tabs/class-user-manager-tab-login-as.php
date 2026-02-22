<?php
/**
 * Login As tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Login_As {

	/**
	 * Option key for storing active login-as sessions.
	 */
	private const OPTION_KEY = 'user_manager_login_as_sessions';

	public static function render(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to access this page.', 'user-manager'));
		}

		$current_admin_id = get_current_user_id();
		$sessions         = get_option(self::OPTION_KEY, []);
		if (!is_array($sessions)) {
			$sessions = [];
		}

		$active_session = $sessions[$current_admin_id] ?? null;
		$notice_code    = isset($_GET['um_login_as_notice']) ? sanitize_key(wp_unslash($_GET['um_login_as_notice'])) : '';

		$notice_messages = [
			'start_success'         => ['success', __('Temporary password generated successfully. Use the credentials below in a private/incognito window.', 'user-manager')],
			'restored_success'      => ['success', __('Original password restored successfully.', 'user-manager')],
			'select_user_required'  => ['error', __('Please select a valid user email before generating a temporary password.', 'user-manager')],
			'user_not_found'        => ['error', __('The selected user could not be found.', 'user-manager')],
			'target_admin_blocked'  => ['error', __('For safety, Login As does not allow impersonating another administrator account.', 'user-manager')],
			'password_hash_missing' => ['error', __('Could not read the selected user password hash. No changes were made.', 'user-manager')],
			'password_set_failed'   => ['error', __('Could not set the temporary password. Please try again.', 'user-manager')],
			'restore_failed'        => ['error', __('Could not restore the original password for this session.', 'user-manager')],
		];

		// Handle form submissions.
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['um_login_as_action'])) {
			check_admin_referer('um_login_as_action');

			$action = sanitize_text_field(wp_unslash($_POST['um_login_as_action']));

			if ($action === 'start') {
				$target_id = isset($_POST['um_login_as_user']) ? absint($_POST['um_login_as_user']) : 0;
				$target_email = isset($_POST['um_login_as_user_email']) ? sanitize_email(wp_unslash($_POST['um_login_as_user_email'])) : '';
				if ($target_id <= 0 && $target_email !== '') {
					$target_user = get_user_by('email', $target_email);
					if ($target_user && isset($target_user->ID)) {
						$target_id = (int) $target_user->ID;
					}
				}
				$result_code = self::handle_start_session($current_admin_id, $target_id);
				// Reload to show updated session state and avoid resubmission.
				wp_safe_redirect(add_query_arg('um_login_as_notice', $result_code, User_Manager_Core::get_page_url(User_Manager_Core::TAB_LOGIN_AS)));
				exit;
			} elseif ($action === 'restore' && $active_session) {
				$result_code = self::handle_restore_session($current_admin_id, $active_session);
				wp_safe_redirect(add_query_arg('um_login_as_notice', $result_code, User_Manager_Core::get_page_url(User_Manager_Core::TAB_LOGIN_AS)));
				exit;
			}
		}

		// Refresh sessions after any potential modification.
		$sessions       = get_option(self::OPTION_KEY, []);
		$active_session = $sessions[$current_admin_id] ?? null;

		?>
		<div class="um-admin-grid">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-admin-users"></span>
					<h2><?php esc_html_e('Login As', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p class="description" style="margin-top:0;">
						<?php esc_html_e('Use this tool to temporarily set a random password for a user so you can log in as them in an incognito/private window. When finished, restore their original password.', 'user-manager'); ?>
					</p>

					<?php if ($notice_code !== '' && isset($notice_messages[$notice_code]) && is_array($notice_messages[$notice_code])) : ?>
						<?php
						$notice_type = $notice_messages[$notice_code][0] === 'success' ? 'notice-success' : 'notice-error';
						$notice_text = (string) $notice_messages[$notice_code][1];
						?>
						<div class="notice <?php echo esc_attr($notice_type); ?> is-dismissible" style="margin: 12px 0 16px;">
							<p><?php echo esc_html($notice_text); ?></p>
						</div>
					<?php endif; ?>

					<h3><?php esc_html_e('Step 1: Choose a user and generate a temporary password', 'user-manager'); ?></h3>
					<form method="post" action="" id="um-login-as-start-form">
						<?php wp_nonce_field('um_login_as_action'); ?>
						<input type="hidden" name="um_login_as_action" value="start" />

						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="um-login-as-user-email"><?php esc_html_e('User Email', 'user-manager'); ?></label>
								</th>
								<td>
									<input type="hidden" name="um_login_as_user" id="um-login-as-user-id" value="" />
									<input
										type="text"
										name="um_login_as_user_email"
										id="um-login-as-user-email"
										class="regular-text"
										list="um-login-as-user-datalist"
										data-um-lazy-datalist-source="user_emails"
										placeholder="<?php esc_attr_e('Type to search by email', 'user-manager'); ?>"
										value=""
										required
										autocomplete="off"
									/>
									<datalist id="um-login-as-user-datalist"></datalist>
									<p class="description">
										<?php esc_html_e('Start typing an email address, then choose a suggestion. This list loads on first focus to keep the page fast.', 'user-manager'); ?>
									</p>
								</td>
							</tr>
						</table>

						<?php submit_button(esc_html__('Generate Temporary Password', 'user-manager'), 'primary', 'submit', false); ?>
					</form>

					<?php if ($active_session) : ?>
						<?php
						$target_id   = (int) $active_session['user_id'];
						$target_user     = get_userdata($target_id);
						$settings        = User_Manager_Core::get_settings();
						$login_path      = !empty($settings['default_login_url']) ? $settings['default_login_url'] : '/my-account/';
						$login_url       = home_url($login_path);
						$sso_bypass_url  = home_url('/wp-login.php?saml_sso=false');
						?>
						<?php if ($target_user) : ?>
							<div class="notice notice-info" style="padding:12px 15px;margin:15px 0;">
								<p>
									<strong><?php esc_html_e('Active Login As Session', 'user-manager'); ?></strong><br />
									<?php
									printf(
										/* translators: 1: display name, 2: username, 3: email */
										esc_html__('You are temporarily impersonating %1$s (%2$s, %3$s).', 'user-manager'),
										esc_html($target_user->display_name),
										esc_html($target_user->user_login),
										esc_html($target_user->user_email)
									);
									?>
								</p>
								<p>
									<?php esc_html_e('Use the credentials below in a private/incognito browser window to log in as this user.', 'user-manager'); ?>
								</p>
								<div style="display:flex; flex-direction:column; gap:10px; max-width:480px;">
									<div>
										<div style="font-weight:600; margin-bottom:4px;">
											<?php esc_html_e('Username:', 'user-manager'); ?>
										</div>
										<input
											type="text"
											class="regular-text um-login-as-copy"
											data-label="<?php esc_attr_e('Username', 'user-manager'); ?>"
											value="<?php echo esc_attr($target_user->user_login); ?>"
											readonly
										/>
									</div>
									<div>
										<div style="font-weight:600; margin-bottom:4px;">
											<?php esc_html_e('Temporary Password:', 'user-manager'); ?>
										</div>
										<input
											type="text"
											class="regular-text um-login-as-copy"
											data-label="<?php esc_attr_e('Temporary Password', 'user-manager'); ?>"
											value="<?php echo esc_attr($active_session['temp_password']); ?>"
											readonly
										/>
									</div>
									<div>
										<div style="font-weight:600; margin-bottom:4px;">
											<?php esc_html_e('Login URL:', 'user-manager'); ?>
										</div>
										<input
											type="text"
											class="regular-text um-login-as-copy"
											data-label="<?php esc_attr_e('Login URL', 'user-manager'); ?>"
											value="<?php echo esc_attr($login_url); ?>"
											readonly
										/>
									</div>
									<div>
										<div style="font-weight:600; margin-bottom:4px;">
											<?php esc_html_e('SSO Bypass Login URL:', 'user-manager'); ?>
										</div>
										<input
											type="text"
											class="regular-text um-login-as-copy"
											data-label="<?php esc_attr_e('SSO Bypass Login URL', 'user-manager'); ?>"
											value="<?php echo esc_attr($sso_bypass_url); ?>"
											readonly
										/>
									</div>
								</div>
								<p class="description">
									<?php esc_html_e('Important: Do not share this password with the end user. It is only for temporary admin testing and will be replaced with the original password when you click "Restore Original Password".', 'user-manager'); ?>
								</p>
								<p>
									<form method="post" action="" onsubmit="return confirm('<?php echo esc_js(__('Restore the original password for this user?', 'user-manager')); ?>');" style="margin-top:10px;">
										<?php wp_nonce_field('um_login_as_action'); ?>
										<input type="hidden" name="um_login_as_action" value="restore" />
										<?php submit_button(esc_html__('Restore Original Password', 'user-manager'), 'secondary', 'submit', false); ?>
									</form>
								</p>
							</div>
						<?php else : ?>
							<p><?php esc_html_e('An active Login As session exists, but the user could not be found. You can safely ignore this or start a new session.', 'user-manager'); ?></p>
						<?php endif; ?>
					<?php endif; ?>

					<p class="description" style="margin-top:20px;">
						<?php esc_html_e('Security note: The original password hash is stored only for the duration of this session and is removed immediately after restoration.', 'user-manager'); ?>
					</p>
				</div>
			</div>

			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-backup"></span>
					<h2><?php esc_html_e('Recent Login As History', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<?php
					global $wpdb;
					$table        = $wpdb->prefix . 'um_admin_activity';
					$history_rows = $wpdb->get_results(
						"SELECT * FROM {$table} WHERE action IN ('login_as_start','login_as_restore') ORDER BY created_at DESC LIMIT 20",
						ARRAY_A
					);
					?>
					<?php if (empty($history_rows)) : ?>
						<p class="description">
							<?php esc_html_e('No recent Login As activity found for this admin.', 'user-manager'); ?>
						</p>
					<?php else : ?>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e('When', 'user-manager'); ?></th>
									<th><?php esc_html_e('Action', 'user-manager'); ?></th>
									<th><?php esc_html_e('Admin', 'user-manager'); ?></th>
									<th><?php esc_html_e('Target User', 'user-manager'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($history_rows as $row) : ?>
									<?php
									$extra = !empty($row['extra']) ? json_decode($row['extra'], true) : [];
									$target_id = isset($extra['target_user_id']) ? (int) $extra['target_user_id'] : 0;
									$target = $target_id ? get_userdata($target_id) : null;
									$when   = !empty($row['created_at']) ? User_Manager_Core::nice_time($row['created_at']) : '';
									$admin  = !empty($row['created_by']) ? get_userdata((int) $row['created_by']) : null;

									if ($row['action'] === 'login_as_start') {
										$label = esc_html__('Temporary Password Set', 'user-manager');
									} elseif ($row['action'] === 'login_as_restore') {
										$label = esc_html__('Password Restored', 'user-manager');
									} else {
										$label = esc_html(ucwords(str_replace('_', ' ', $row['action'])));
									}
									?>
									<tr>
										<td><?php echo esc_html($when); ?></td>
										<td><?php echo esc_html($label); ?></td>
										<td>
											<?php if ($admin) : ?>
												<a href="<?php echo esc_url(get_edit_user_link($admin->ID)); ?>">
													<?php echo esc_html($admin->display_name . ' (' . $admin->user_login . ')'); ?>
												</a>
											<?php else : ?>
												<em style="color:#646970;"><?php esc_html_e('Unknown', 'user-manager'); ?></em>
											<?php endif; ?>
										</td>
										<td>
											<?php if ($target) : ?>
												<a href="<?php echo esc_url(get_edit_user_link($target->ID)); ?>">
													<?php echo esc_html($target->display_name . ' (' . $target->user_login . ')'); ?>
												</a>
											<?php elseif (!empty($extra['target_user_login']) || !empty($extra['target_user_email'])) : ?>
												<?php
												$parts = [];
												if (!empty($extra['target_user_login'])) {
													$parts[] = $extra['target_user_login'];
												}
												if (!empty($extra['target_user_email'])) {
													$parts[] = $extra['target_user_email'];
												}
												echo esc_html(implode(' – ', $parts));
												?>
											<?php else : ?>
												<em style="color:#646970;"><?php esc_html_e('Unknown', 'user-manager'); ?></em>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<script>
		(function() {
			function updateLoginAsHiddenUserId() {
				var emailInput = document.getElementById('um-login-as-user-email');
				var hiddenInput = document.getElementById('um-login-as-user-id');
				if (!emailInput || !hiddenInput) {
					return;
				}
				var listId = emailInput.getAttribute('list');
				var datalist = listId ? document.getElementById(listId) : null;
				var typed = (emailInput.value || '').trim().toLowerCase();
				hiddenInput.value = '';
				if (!typed || !datalist || !datalist.options) {
					return;
				}
				for (var i = 0; i < datalist.options.length; i++) {
					var option = datalist.options[i];
					if (!option) {
						continue;
					}
					var optionValue = (option.value || '').trim().toLowerCase();
					if (optionValue !== typed) {
						continue;
					}
					var userId = option.getAttribute('data-um-user-id');
					if (userId) {
						hiddenInput.value = userId;
					}
					return;
				}
			}

			var startForm = document.getElementById('um-login-as-start-form');
			if (startForm) {
				startForm.addEventListener('submit', function() {
					updateLoginAsHiddenUserId();
				});
			}

			document.addEventListener('click', function(e) {
				var target = e.target;
				if (target.classList && target.classList.contains('um-login-as-copy')) {
					target.focus();
					target.select();
					try {
						document.execCommand('copy');
					} catch (err) {
						// Clipboard API may not be available; selection is still useful.
					}
				}
			});

			document.addEventListener('input', function(e) {
				if (e.target && e.target.id === 'um-login-as-user-email') {
					updateLoginAsHiddenUserId();
				}
			});
			document.addEventListener('change', function(e) {
				if (e.target && e.target.id === 'um-login-as-user-email') {
					updateLoginAsHiddenUserId();
				}
			});
		})();
		</script>
		<?php
	}

	/**
	 * Start a Login As session for a selected user.
	 *
	 * @param int $admin_id  Admin user ID.
	 * @param int $target_id Target user ID.
	 * @return string Result code for admin notice.
	 */
	private static function handle_start_session(int $admin_id, int $target_id): string {
		if ($target_id <= 0 || $admin_id <= 0) {
			return 'select_user_required';
		}

		$target = get_userdata($target_id);
		if (!$target) {
			return 'user_not_found';
		}

		// Do not allow logging in as another administrator unless explicitly desired.
		if (in_array('administrator', (array) $target->roles, true) && $admin_id !== $target_id) {
			return 'target_admin_blocked';
		}

		// Generate a secure random password.
		$temp_password = wp_generate_password(20, true, true);

		// Capture current password hash directly from DB.
		global $wpdb;
		$table      = $wpdb->users;
		$user_login = $target->user_login;
		$hash       = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT user_pass FROM {$table} WHERE ID = %d",
				$target_id
			)
		);

		if (empty($hash)) {
			return 'password_hash_missing';
		}

		// Update the user's password to the temporary value using wp_set_password.
		wp_set_password($temp_password, $target_id);

		$updated_hash = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT user_pass FROM {$table} WHERE ID = %d",
				$target_id
			)
		);
		if (empty($updated_hash) || !wp_check_password($temp_password, (string) $updated_hash, $target_id)) {
			return 'password_set_failed';
		}

		// Store session metadata keyed by current admin.
		$sessions = get_option(self::OPTION_KEY, []);
		if (!is_array($sessions)) {
			$sessions = [];
		}

		$sessions[$admin_id] = [
			'user_id'       => $target_id,
			'original_hash' => $hash,
			'temp_password' => $temp_password,
			'started_at'    => current_time('mysql'),
		];

		update_option(self::OPTION_KEY, $sessions);

		// Log to Admin Activity Log.
		User_Manager_Core::add_activity_log(
			'login_as_start',
			$admin_id,
			'Login As',
			[
				'target_user_id'    => $target_id,
				'target_user_login' => $target->user_login,
				'target_user_email' => $target->user_email,
			]
		);

		return 'start_success';
	}

	/**
	 * Restore the original password for an active session.
	 *
	 * @param int   $admin_id Admin user ID.
	 * @param array $session  Session data.
	 * @return string Result code for admin notice.
	 */
	private static function handle_restore_session(int $admin_id, array $session): string {
		if (empty($session['user_id']) || empty($session['original_hash'])) {
			return 'restore_failed';
		}

		$user_id       = (int) $session['user_id'];
		$original_hash = (string) $session['original_hash'];

		global $wpdb;
		$table = $wpdb->users;

		// Restore the original password hash directly.
		$updated = $wpdb->update(
			$table,
			[
				'user_pass' => $original_hash,
			],
			[
				'ID' => $user_id,
			],
			[
				'%s',
			],
			[
				'%d',
			]
		);
		if ($updated === false) {
			return 'restore_failed';
		}

		// Clear the session for this admin.
		$sessions = get_option(self::OPTION_KEY, []);
		if (is_array($sessions) && isset($sessions[$admin_id])) {
			unset($sessions[$admin_id]);
			update_option(self::OPTION_KEY, $sessions);
		}

		// Log restoration to Admin Activity Log.
		User_Manager_Core::add_activity_log(
			'login_as_restore',
			$admin_id,
			'Login As',
			[
				'target_user_id' => $user_id,
			]
		);

		return 'restored_success';
	}
}


