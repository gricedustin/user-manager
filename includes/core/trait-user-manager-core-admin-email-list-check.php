<?php
/**
 * Admin Email List Check: fetches a remote TXT file listing the WP
 * Administrator emails that should exist on this site, caches the result
 * on a daily basis, and renders admin notices for any missing admins or
 * any local administrators that are not present in the remote list.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Admin_Email_List_Check_Trait {

	/**
	 * Settings key storing the remote TXT file URL.
	 */
	public static function admin_email_list_check_url_setting_key(): string {
		return 'admin_email_list_check_remote_url';
	}

	/**
	 * Transient used to cache the parsed list of remote admin emails.
	 */
	private static function admin_email_list_check_cache_key(): string {
		return 'user_manager_admin_email_list_check_cache';
	}

	/**
	 * Transient used to cache the last fetch error (empty on success).
	 */
	private static function admin_email_list_check_error_key(): string {
		return 'user_manager_admin_email_list_check_error';
	}

	/**
	 * Cache lifetime (daily).
	 */
	private static function admin_email_list_check_cache_ttl(): int {
		return DAY_IN_SECONDS;
	}

	/**
	 * Register admin_notices hook for the admin email list check.
	 */
	public static function maybe_boot_admin_email_list_check(): void {
		add_action('admin_notices', [__CLASS__, 'render_admin_email_list_check_notices'], 8);
	}

	/**
	 * Read the configured remote TXT file URL from plugin settings.
	 */
	public static function get_admin_email_list_check_url(): string {
		$settings = self::get_settings();
		$setting_key = self::admin_email_list_check_url_setting_key();
		$url = isset($settings[$setting_key])
			? (string) $settings[$setting_key]
			: '';
		$url = trim($url);
		return $url;
	}

	/**
	 * Return the cached list of remote admin emails, fetching and caching
	 * it when the cache is empty or expired.
	 *
	 * @return array{emails:array<int,string>,error:string,url:string}
	 */
	public static function get_admin_email_list_check_remote_emails(): array {
		$url = self::get_admin_email_list_check_url();
		if ($url === '') {
			return [
				'emails' => [],
				'error'  => '',
				'url'    => '',
			];
		}

		$cached = get_transient(self::admin_email_list_check_cache_key());
		$cached_error = get_transient(self::admin_email_list_check_error_key());
		if (is_array($cached) && isset($cached['url']) && $cached['url'] === $url && isset($cached['emails']) && is_array($cached['emails'])) {
			return [
				'emails' => array_values(array_map('strval', $cached['emails'])),
				'error'  => is_string($cached_error) ? $cached_error : '',
				'url'    => $url,
			];
		}

		return self::refresh_admin_email_list_check_remote_emails($url);
	}

	/**
	 * Fetch the remote TXT file, parse it, and store it in the daily cache.
	 *
	 * @return array{emails:array<int,string>,error:string,url:string}
	 */
	public static function refresh_admin_email_list_check_remote_emails(string $url = ''): array {
		if ($url === '') {
			$url = self::get_admin_email_list_check_url();
		}
		if ($url === '') {
			delete_transient(self::admin_email_list_check_cache_key());
			delete_transient(self::admin_email_list_check_error_key());
			return [
				'emails' => [],
				'error'  => '',
				'url'    => '',
			];
		}

		$response = wp_remote_get($url, [
			'timeout'     => 15,
			'redirection' => 5,
			'user-agent'  => 'UserExperienceManager/AdminEmailListCheck',
		]);

		if (is_wp_error($response)) {
			$error_message = (string) $response->get_error_message();
			self::persist_admin_email_list_check_error($url, $error_message);
			return [
				'emails' => [],
				'error'  => $error_message,
				'url'    => $url,
			];
		}

		$code = (int) wp_remote_retrieve_response_code($response);
		if ($code < 200 || $code >= 300) {
			$error_message = sprintf(
				/* translators: %d: HTTP response code */
				__('Remote TXT file request returned HTTP %d.', 'user-manager'),
				$code
			);
			self::persist_admin_email_list_check_error($url, $error_message);
			return [
				'emails' => [],
				'error'  => $error_message,
				'url'    => $url,
			];
		}

		$body = (string) wp_remote_retrieve_body($response);
		$emails = self::parse_admin_email_list_check_body($body);

		set_transient(
			self::admin_email_list_check_cache_key(),
			[
				'url'          => $url,
				'emails'       => $emails,
				'fetched_at'   => time(),
			],
			self::admin_email_list_check_cache_ttl()
		);
		delete_transient(self::admin_email_list_check_error_key());

		return [
			'emails' => $emails,
			'error'  => '',
			'url'    => $url,
		];
	}

	/**
	 * Clear the cached remote admin email list so the next check performs
	 * a fresh fetch.
	 */
	public static function clear_admin_email_list_check_cache(): void {
		delete_transient(self::admin_email_list_check_cache_key());
		delete_transient(self::admin_email_list_check_error_key());
	}

	/**
	 * Parse an email list from the TXT body. Emails can be separated by
	 * newlines and/or commas and the file is tolerant of blank lines and
	 * surrounding whitespace.
	 *
	 * @return array<int,string>
	 */
	private static function parse_admin_email_list_check_body(string $body): array {
		$normalized = str_replace(["\r\n", "\r"], "\n", $body);
		$tokens = preg_split('/[\n,]+/', $normalized);
		if (!is_array($tokens)) {
			return [];
		}

		$seen = [];
		foreach ($tokens as $token) {
			$token = trim((string) $token);
			if ($token === '') {
				continue;
			}
			$clean = sanitize_email($token);
			if ($clean === '' || !is_email($clean)) {
				continue;
			}
			$key = strtolower($clean);
			if (isset($seen[$key])) {
				continue;
			}
			$seen[$key] = $clean;
		}

		return array_values($seen);
	}

	/**
	 * Persist a fetch-error marker so admins get a useful message without
	 * re-fetching on every page load. Cached for a short time so retries
	 * happen soon but not on every request.
	 */
	private static function persist_admin_email_list_check_error(string $url, string $error_message): void {
		set_transient(
			self::admin_email_list_check_cache_key(),
			[
				'url'        => $url,
				'emails'     => [],
				'fetched_at' => time(),
			],
			MINUTE_IN_SECONDS * 15
		);
		set_transient(
			self::admin_email_list_check_error_key(),
			$error_message,
			MINUTE_IN_SECONDS * 15
		);
	}

	/**
	 * Return the email addresses of all currently registered WordPress
	 * administrators on this site.
	 *
	 * @return array<int,array{id:int,email:string}>
	 */
	public static function get_local_administrator_emails(): array {
		$users = get_users([
			'role'   => 'administrator',
			'fields' => ['ID', 'user_email'],
		]);

		$out = [];
		if (!is_array($users)) {
			return $out;
		}
		foreach ($users as $user) {
			$email = isset($user->user_email) ? (string) $user->user_email : '';
			$email = trim($email);
			if ($email === '') {
				continue;
			}
			$out[] = [
				'id'    => isset($user->ID) ? (int) $user->ID : 0,
				'email' => $email,
			];
		}
		return $out;
	}

	/**
	 * Render the admin notices for missing remote admins and for local
	 * administrators that are not in the remote list.
	 */
	public static function render_admin_email_list_check_notices(): void {
		if (!is_admin() || !current_user_can('manage_options')) {
			return;
		}

		if (!self::is_user_manager_admin_screen_for_email_list_check()) {
			return;
		}

		// Render the new "Also Display Notification with All Users with X
		// Role" notices first (independent of whether the Remote TXT URL
		// is configured), then fall through to the existing remote-list
		// notices when a URL is present.
		self::render_admin_email_list_check_role_notices();

		$result = self::get_admin_email_list_check_remote_emails();
		$url = $result['url'];
		if ($url === '') {
			return;
		}

		if ($result['error'] !== '') {
			echo '<div class="notice notice-error"><p><strong>'
				. esc_html__('User Manager: Remote Admin Email List', 'user-manager')
				. '</strong> — '
				. esc_html(
					sprintf(
						/* translators: %s: error message from the remote TXT fetch */
						__('Unable to read the remote TXT file of WP Administrator emails: %s', 'user-manager'),
						$result['error']
					)
				)
				. '</p><p>'
				. esc_html__('Check the "Remote TXT File URL List of WP Administrator Emails for This Site" setting and try clearing the cache.', 'user-manager')
				. '</p></div>';
			return;
		}

		$remote_emails = $result['emails'];
		if (empty($remote_emails)) {
			echo '<div class="notice notice-warning"><p><strong>'
				. esc_html__('User Manager: Remote Admin Email List', 'user-manager')
				. '</strong> — '
				. esc_html__('The remote TXT file was fetched but contained no valid email addresses.', 'user-manager')
				. '</p></div>';
			return;
		}

		$local_admins = self::get_local_administrator_emails();

		$local_email_map = [];
		foreach ($local_admins as $entry) {
			$local_email_map[strtolower($entry['email'])] = $entry;
		}

		$remote_email_map = [];
		foreach ($remote_emails as $remote_email) {
			$remote_email_map[strtolower($remote_email)] = $remote_email;
		}

		$missing_locally = [];
		foreach ($remote_email_map as $key => $display_email) {
			if (!isset($local_email_map[$key])) {
				$missing_locally[] = $display_email;
			}
		}

		$extra_locally = [];
		foreach ($local_email_map as $key => $entry) {
			if (!isset($remote_email_map[$key])) {
				$extra_locally[] = $entry;
			}
		}

		// Render "Not in Remote Admin List" FIRST. These are local WP
		// Administrators that should probably be demoted or removed — a
		// higher-severity finding — so admins see it before the
		// "Missing from Remote Admin List" add-missing-user prompt.
		if (!empty($extra_locally)) {
			echo '<div class="notice notice-error"><p><strong>'
				. esc_html__('User Manager: WP Administrators Not in Remote Admin List', 'user-manager')
				. '</strong></p>';
			echo '<p>' . esc_html__('The following WP Administrator email(s) exist on this site but are NOT in the remote TXT list. Click a link to remove each administrator, or use Change Role to demote them instead:', 'user-manager') . '</p>';
			echo '<ul style="list-style:disc;margin-left:20px;">';
			foreach ($extra_locally as $entry) {
				$remove_url = add_query_arg(
					[
						'page'                 => self::SETTINGS_PAGE_SLUG,
						'tab'                  => self::TAB_LOGIN_TOOLS,
						'login_tools_section'  => self::TAB_REMOVE_USER,
						'um_prefill_user'      => (int) $entry['id'],
						'um_prefill_user_email' => rawurlencode($entry['email']),
					],
					admin_url('admin.php')
				);
				$change_role_url = add_query_arg(
					[
						'page'                  => self::SETTINGS_PAGE_SLUG,
						'tab'                   => self::TAB_LOGIN_TOOLS,
						'login_tools_section'   => self::TAB_CHANGE_ROLE,
						'um_prefill_user_email' => rawurlencode($entry['email']),
						'um_prefill_role'       => 'customer',
					],
					admin_url('admin.php')
				);
				echo '<li>'
					. '<code>' . esc_html($entry['email']) . '</code> (user ID ' . esc_html((string) $entry['id']) . ') — '
					. '<a href="' . esc_url($remove_url) . '">' . esc_html__('Remove this administrator', 'user-manager') . '</a>'
					. ' | '
					. '<a href="' . esc_url($change_role_url) . '">' . esc_html__('Change Role', 'user-manager') . '</a>'
					. '</li>';
			}
			echo '</ul>';

			// Bulk action buttons — prefill the full email list (comma-
			// separated because it is shorter in the URL than newline
			// encoding, and both the Remove User and Change Role tabs
			// accept comma or newline separators when hydrating prefills).
			$all_emails = array_values(array_filter(array_map(static function ($entry) {
				return isset($entry['email']) ? (string) $entry['email'] : '';
			}, $extra_locally)));
			if (!empty($all_emails)) {
				$bulk_list = rawurlencode(implode(',', $all_emails));
				$bulk_remove_url = add_query_arg(
					[
						'page'                  => self::SETTINGS_PAGE_SLUG,
						'tab'                   => self::TAB_LOGIN_TOOLS,
						'login_tools_section'   => self::TAB_REMOVE_USER,
						'um_prefill_user_email' => $bulk_list,
					],
					admin_url('admin.php')
				);
				$bulk_change_role_url = add_query_arg(
					[
						'page'                  => self::SETTINGS_PAGE_SLUG,
						'tab'                   => self::TAB_LOGIN_TOOLS,
						'login_tools_section'   => self::TAB_CHANGE_ROLE,
						'um_prefill_user_email' => $bulk_list,
						'um_prefill_role'       => 'customer',
					],
					admin_url('admin.php')
				);
				echo '<p style="margin-top:12px;">'
					. '<a href="' . esc_url($bulk_remove_url) . '" class="button button-secondary" style="margin-right:8px;">'
					. esc_html(sprintf(
						/* translators: %d: number of administrators */
						_n('Remove all %d administrator', 'Remove all %d administrators', count($all_emails), 'user-manager'),
						count($all_emails)
					))
					. '</a>'
					. '<a href="' . esc_url($bulk_change_role_url) . '" class="button button-primary">'
					. esc_html(sprintf(
						/* translators: %d: number of administrators */
						_n('Change %d role', 'Change all %d roles', count($all_emails), 'user-manager'),
						count($all_emails)
					))
					. '</a>'
					. '</p>';
			}

			echo '</div>';
		}

		if (!empty($missing_locally)) {
			echo '<div class="notice notice-warning"><p><strong>'
				. esc_html__('User Manager: Missing WP Administrators from Remote Admin List', 'user-manager')
				. '</strong></p>';
			echo '<p>' . esc_html__('The following administrator email(s) are in the remote TXT list but do not exist as WP Administrators on this site. Click a link to create each user:', 'user-manager') . '</p>';
			echo '<ul style="list-style:disc;margin-left:20px;">';
			foreach ($missing_locally as $missing_email) {
				$create_url = add_query_arg(
					[
						'page'                => self::SETTINGS_PAGE_SLUG,
						'tab'                 => self::TAB_LOGIN_TOOLS,
						'login_tools_section' => self::TAB_CREATE_USER,
						'um_prefill_email'    => rawurlencode($missing_email),
						'um_prefill_role'     => 'administrator',
					],
					admin_url('admin.php')
				);
				echo '<li>'
					. '<code>' . esc_html($missing_email) . '</code> — '
					. '<a href="' . esc_url($create_url) . '">' . esc_html__('Create this administrator', 'user-manager') . '</a>'
					. '</li>';
			}
			echo '</ul>';

			// Bulk Create button — prefills the Paste from Spreadsheet
			// textarea on the Bulk Create tab with one missing email per
			// line and preselects the Default Role dropdown to
			// Administrator, so the admin can hit "Import" in one click
			// after glancing at the list.
			$missing_emails_clean = array_values(array_filter(array_map(static function ($email) {
				$email = trim((string) $email);
				return $email !== '' && is_email($email) ? sanitize_email($email) : '';
			}, $missing_locally)));
			if (!empty($missing_emails_clean)) {
				$bulk_create_url = add_query_arg(
					[
						'page'                   => self::SETTINGS_PAGE_SLUG,
						'tab'                    => self::TAB_LOGIN_TOOLS,
						'login_tools_section'    => self::TAB_BULK_CREATE,
						'um_prefill_paste_data'  => rawurlencode(implode("\n", $missing_emails_clean)),
						'um_prefill_role'        => 'administrator',
					],
					admin_url('admin.php')
				);
				echo '<p style="margin-top:12px;">'
					. '<a href="' . esc_url($bulk_create_url) . '" class="button button-primary">'
					. esc_html(sprintf(
						/* translators: %d: number of administrators */
						_n(
							'Bulk Create %d administrator',
							'Bulk Create all %d administrators',
							count($missing_emails_clean),
							'user-manager'
						),
						count($missing_emails_clean)
					))
					. '</a>'
					. ' <span class="description" style="margin-left:8px;">'
					. esc_html__('Opens the Bulk Create tool with every missing email prefilled in "Paste from Spreadsheet" and the Default Role preset to Administrator.', 'user-manager')
					. '</span>'
					. '</p>';
			}

			echo '</div>';
		}
	}

	/**
	 * Render one admin notice per role selected in the
	 * "Also Display Notification with All Users with X Role" setting,
	 * each listing every user assigned that role with per-row "Remove
	 * this user" and "Change role for this user" links. Change Role links
	 * preselect the Customer role to match the Remote Admin notice UX.
	 */
	public static function render_admin_email_list_check_role_notices(): void {
		$settings = self::get_settings();
		$roles_to_notify = isset($settings['admin_email_list_check_role_notification_roles'])
			&& is_array($settings['admin_email_list_check_role_notification_roles'])
			? array_values(array_unique(array_map('sanitize_key', $settings['admin_email_list_check_role_notification_roles'])))
			: [];
		if (empty($roles_to_notify)) {
			return;
		}
		$hide_empty = !empty($settings['admin_email_list_check_role_notification_hide_empty']);

		$registered_roles = method_exists(__CLASS__, 'get_user_roles') ? self::get_user_roles() : [];
		if (!is_array($registered_roles)) {
			$registered_roles = [];
		}

		foreach ($roles_to_notify as $role_key) {
			$role_key = sanitize_key($role_key);
			if ($role_key === '' || !isset($registered_roles[$role_key])) {
				continue;
			}

			$role_label = (string) $registered_roles[$role_key];
			$users = get_users([
				'role'    => $role_key,
				'orderby' => 'user_email',
				'order'   => 'ASC',
				'fields'  => ['ID', 'user_email', 'display_name', 'user_login'],
			]);
			$users = is_array($users) ? $users : [];

			// Per the "Hide Notification for Each if No Users are Found"
			// setting, stay silent for roles with zero matching users
			// instead of showing an empty-state card.
			if ($hide_empty && empty($users)) {
				continue;
			}

			echo '<div class="notice notice-info"><p><strong>'
				. esc_html(sprintf(
					/* translators: 1: role label, 2: role key */
					__('User Manager: Users with role "%1$s" (%2$s)', 'user-manager'),
					$role_label,
					$role_key
				))
				. '</strong></p>';

			if (empty($users)) {
				echo '<p>'
					. esc_html(sprintf(
						/* translators: %s: role label */
						__('No users are currently assigned the %s role.', 'user-manager'),
						$role_label
					))
					. '</p></div>';
				continue;
			}

			echo '<p>'
				. esc_html(sprintf(
					/* translators: 1: count of users, 2: role label */
					_n(
						'%1$d user with the %2$s role. Use the per-row links to remove or change each user\'s role.',
						'%1$d users with the %2$s role. Use the per-row links to remove or change each user\'s role.',
						count($users),
						'user-manager'
					),
					count($users),
					$role_label
				))
				. '</p>';

			echo '<ul style="list-style:disc;margin-left:20px;">';
			$role_emails_for_bulk = [];
			foreach ($users as $user) {
				$email = isset($user->user_email) ? (string) $user->user_email : '';
				$email = trim($email);
				if ($email === '' || !is_email($email)) {
					continue;
				}
				$user_id = isset($user->ID) ? (int) $user->ID : 0;
				$display = isset($user->display_name) && (string) $user->display_name !== ''
					? (string) $user->display_name
					: (string) ($user->user_login ?? $email);
				$role_emails_for_bulk[] = $email;

				$remove_url = add_query_arg(
					[
						'page'                  => self::SETTINGS_PAGE_SLUG,
						'tab'                   => self::TAB_LOGIN_TOOLS,
						'login_tools_section'   => self::TAB_REMOVE_USER,
						'um_prefill_user'       => $user_id,
						'um_prefill_user_email' => rawurlencode($email),
					],
					admin_url('admin.php')
				);
				$change_role_url = add_query_arg(
					[
						'page'                  => self::SETTINGS_PAGE_SLUG,
						'tab'                   => self::TAB_LOGIN_TOOLS,
						'login_tools_section'   => self::TAB_CHANGE_ROLE,
						'um_prefill_user_email' => rawurlencode($email),
						'um_prefill_role'       => 'customer',
					],
					admin_url('admin.php')
				);

				echo '<li>'
					. '<code>' . esc_html($email) . '</code>'
					. ' (' . esc_html($display);
				if ($user_id > 0) {
					echo ', user ID ' . esc_html((string) $user_id);
				}
				echo ') — '
					. '<a href="' . esc_url($remove_url) . '">' . esc_html__('Remove this user', 'user-manager') . '</a>'
					. ' | '
					. '<a href="' . esc_url($change_role_url) . '">' . esc_html__('Change role for this user', 'user-manager') . '</a>'
					. '</li>';
			}
			echo '</ul>';

			if (!empty($role_emails_for_bulk)) {
				$bulk_list = rawurlencode(implode(',', $role_emails_for_bulk));
				$bulk_remove_url = add_query_arg(
					[
						'page'                  => self::SETTINGS_PAGE_SLUG,
						'tab'                   => self::TAB_LOGIN_TOOLS,
						'login_tools_section'   => self::TAB_REMOVE_USER,
						'um_prefill_user_email' => $bulk_list,
					],
					admin_url('admin.php')
				);
				$bulk_change_role_url = add_query_arg(
					[
						'page'                  => self::SETTINGS_PAGE_SLUG,
						'tab'                   => self::TAB_LOGIN_TOOLS,
						'login_tools_section'   => self::TAB_CHANGE_ROLE,
						'um_prefill_user_email' => $bulk_list,
						'um_prefill_role'       => 'customer',
					],
					admin_url('admin.php')
				);
				echo '<p style="margin-top:12px;">'
					. '<a href="' . esc_url($bulk_remove_url) . '" class="button button-secondary" style="margin-right:8px;">'
					. esc_html(sprintf(
						/* translators: 1: count of users, 2: role label */
						_n('Remove all %1$d %2$s user', 'Remove all %1$d %2$s users', count($role_emails_for_bulk), 'user-manager'),
						count($role_emails_for_bulk),
						$role_label
					))
					. '</a>'
					. '<a href="' . esc_url($bulk_change_role_url) . '" class="button button-primary">'
					. esc_html(sprintf(
						/* translators: 1: count of users, 2: role label */
						_n('Change %1$d %2$s user\'s role', 'Change all %1$d %2$s users\' roles', count($role_emails_for_bulk), 'user-manager'),
						count($role_emails_for_bulk),
						$role_label
					))
					. '</a>'
					. '</p>';
			}

			echo '</div>';
		}
	}

	/**
	 * Only show the admin email list check notice on User Manager admin
	 * pages and on the WordPress Users list screen, which are the places
	 * administrators will act on the notice.
	 */
	private static function is_user_manager_admin_screen_for_email_list_check(): bool {
		if (!function_exists('get_current_screen')) {
			return false;
		}

		$screen = get_current_screen();
		if (!$screen) {
			return false;
		}

		if (isset($_GET['page']) && (string) $_GET['page'] === self::SETTINGS_PAGE_SLUG) {
			return true;
		}

		$base = isset($screen->base) ? (string) $screen->base : '';
		if ($base === 'users' || $base === 'dashboard') {
			return true;
		}

		return false;
	}
}
