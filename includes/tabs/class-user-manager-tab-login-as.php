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

		// Handle form submissions.
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['um_login_as_action'])) {
			check_admin_referer('um_login_as_action');

			$action = sanitize_text_field(wp_unslash($_POST['um_login_as_action']));

			if ($action === 'start') {
				$target_id = isset($_POST['um_login_as_user']) ? absint($_POST['um_login_as_user']) : 0;
				self::handle_start_session($current_admin_id, $target_id);
				// Reload to show updated session state and avoid resubmission.
				wp_safe_redirect(User_Manager_Core::get_page_url(User_Manager_Core::TAB_LOGIN_AS));
				exit;
			} elseif ($action === 'restore' && $active_session) {
				self::handle_restore_session($current_admin_id, $active_session);
				wp_safe_redirect(User_Manager_Core::get_page_url(User_Manager_Core::TAB_LOGIN_AS));
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

					<h3><?php esc_html_e('Step 1: Choose a user and generate a temporary password', 'user-manager'); ?></h3>
					<form method="post" action="">
						<?php wp_nonce_field('um_login_as_action'); ?>
						<input type="hidden" name="um_login_as_action" value="start" />

						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="um-login-as-user"><?php esc_html_e('Select User', 'user-manager'); ?></label>
								</th>
								<td>
									<?php
									// Fetch all users and sort by email address (A–Z), then render a basic select.
									$users = get_users([
										'orderby' => 'user_email',
										'order'   => 'ASC',
										'fields'  => ['ID', 'user_email'],
									]);
									?>
									<select name="um_login_as_user" id="um-login-as-user" class="regular-text">
										<option value=""><?php esc_html_e('— Select a user —', 'user-manager'); ?></option>
										<?php foreach ($users as $user) : ?>
											<?php if (empty($user->user_email)) { continue; } ?>
											<option value="<?php echo (int) $user->ID; ?>">
												<?php echo esc_html(strtolower($user->user_email)); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description">
										<?php esc_html_e('We recommend testing with a user account that matches the role(s) you want to preview.', 'user-manager'); ?>
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
		})();
		</script>
		<?php
	}

	/**
	 * Start a Login As session for a selected user.
	 *
	 * @param int $admin_id  Admin user ID.
	 * @param int $target_id Target user ID.
	 */
	private static function handle_start_session(int $admin_id, int $target_id): void {
		if ($target_id <= 0 || $admin_id <= 0) {
			return;
		}

		$target = get_userdata($target_id);
		if (!$target) {
			return;
		}

		// Do not allow logging in as another administrator unless explicitly desired.
		if (in_array('administrator', (array) $target->roles, true) && $admin_id !== $target_id) {
			return;
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
			return;
		}

		// Update the user's password to the temporary value using wp_set_password.
		wp_set_password($temp_password, $target_id);

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
	}

	/**
	 * Restore the original password for an active session.
	 *
	 * @param int   $admin_id Admin user ID.
	 * @param array $session  Session data.
	 */
	private static function handle_restore_session(int $admin_id, array $session): void {
		if (empty($session['user_id']) || empty($session['original_hash'])) {
			return;
		}

		$user_id       = (int) $session['user_id'];
		$original_hash = (string) $session['original_hash'];

		global $wpdb;
		$table = $wpdb->users;

		// Restore the original password hash directly.
		$wpdb->update(
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
	}
}


