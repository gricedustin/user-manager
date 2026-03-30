<?php
/**
 * Data Anonymizer action handlers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Actions_Data_Anonymizer_Trait {

	/**
	 * Handle "Run Data Anonymizer for Orders".
	 */
	public static function handle_run_data_anonymizer_orders(): void {
		self::handle_data_anonymizer_run('orders');
	}

	/**
	 * Handle "Run Data Anonymizer for Users".
	 */
	public static function handle_run_data_anonymizer_users(): void {
		self::handle_data_anonymizer_run('users');
	}

	/**
	 * Handle "Run Data Anonymizer for Forms".
	 */
	public static function handle_run_data_anonymizer_forms(): void {
		self::handle_data_anonymizer_run('forms');
	}

	/**
	 * Shared run handler for Data Anonymizer actions.
	 */
	private static function handle_data_anonymizer_run(string $run_type): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_data_anonymizer_run', 'user_manager_data_anonymizer_nonce');

		$settings = User_Manager_Core::get_settings();
		$settings = self::data_anonymizer_resolve_runtime_settings($settings);
		if (empty($settings['data_anonymizer_enabled'])) {
			self::data_anonymizer_store_last_run_result([
				'status' => 'error',
				'message' => __('Data Anonymizer is not active. Activate the add-on first.', 'user-manager'),
			]);
			wp_safe_redirect(self::data_anonymizer_get_redirect_url());
			exit;
		}

		$result = [
			'records_affected' => 0,
			'counts' => [],
			'notes' => [],
			'supported_form_plugins' => [],
		];

		if ($run_type === 'orders') {
			$result = self::data_anonymizer_run_orders($settings);
		} elseif ($run_type === 'users') {
			$result = self::data_anonymizer_run_users($settings);
		} else {
			$result = self::data_anonymizer_run_forms($settings);
		}

		$history_entry = self::data_anonymizer_append_history($run_type, $settings, $result);
		self::data_anonymizer_store_last_run_result([
			'status' => 'success',
			'message' => __('Data Anonymizer run completed.', 'user-manager'),
			'history_entry' => $history_entry,
		]);

		User_Manager_Core::add_activity_log(
			'data_anonymizer_run_' . $run_type,
			get_current_user_id(),
			'Data Anonymizer',
			[
				'run_type' => $run_type,
				'records_affected' => (int) ($result['records_affected'] ?? 0),
				'counts' => isset($result['counts']) && is_array($result['counts']) ? $result['counts'] : [],
			]
		);

		wp_safe_redirect(self::data_anonymizer_get_redirect_url());
		exit;
	}

	/**
	 * Build redirect URL back to Data Anonymizer add-on.
	 *
	 * @param array<string,string> $extra_args
	 */
	private static function data_anonymizer_get_redirect_url(array $extra_args = []): string {
		$args = array_merge(
			[
				'addon_section' => 'data-anonymizer',
			],
			$extra_args
		);
		return add_query_arg($args, User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS));
	}

	/**
	 * Persist latest per-user run result as transient for UI notice display.
	 *
	 * @param array<string,mixed> $payload
	 */
	private static function data_anonymizer_store_last_run_result(array $payload): void {
		$current_user_id = get_current_user_id();
		if ($current_user_id <= 0) {
			return;
		}
		set_transient('um_data_anonymizer_last_run_' . $current_user_id, $payload, 10 * MINUTE_IN_SECONDS);
	}

	/**
	 * Resolve posted Data Anonymizer settings and persist them.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<string,mixed>
	 */
	private static function data_anonymizer_resolve_runtime_settings(array $settings): array {
		$bool_fields = [
			'data_anonymizer_enabled',
			'data_anonymizer_order_address_random',
			'data_anonymizer_order_phone_fixed',
			'data_anonymizer_order_email_random',
			'data_anonymizer_order_notes_random',
			'data_anonymizer_user_meta_address_random',
			'data_anonymizer_user_meta_phone_fixed',
			'data_anonymizer_user_email_random',
			'data_anonymizer_user_login_random',
			'data_anonymizer_forms_random',
			'data_anonymizer_exclude_wp_administrators',
			'data_anonymizer_exclude_if_matches_admin_email',
		];

		foreach ($bool_fields as $field) {
			if (isset($_POST[$field])) {
				$settings[$field] = wp_unslash($_POST[$field]) === '1';
			}
		}

		if (isset($_POST['data_anonymizer_excluded_email_domains'])) {
			$settings['data_anonymizer_excluded_email_domains'] = sanitize_text_field(wp_unslash($_POST['data_anonymizer_excluded_email_domains']));
		}

		update_option(User_Manager_Core::OPTION_KEY, $settings);
		return $settings;
	}

	/**
	 * Run Orders anonymization.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<string,mixed>
	 */
	private static function data_anonymizer_run_orders(array $settings): array {
		$counts = [
			'orders_scanned' => 0,
			'orders_updated' => 0,
			'order_emails_skipped_excluded' => 0,
			'order_notes_updated' => 0,
		];
		$notes = [];
		$records_affected = 0;

		$do_address = !empty($settings['data_anonymizer_order_address_random']);
		$do_phone = !empty($settings['data_anonymizer_order_phone_fixed']);
		$do_email = !empty($settings['data_anonymizer_order_email_random']);
		$do_notes = !empty($settings['data_anonymizer_order_notes_random']);
		$excluded_domains = self::data_anonymizer_parse_excluded_domains((string) ($settings['data_anonymizer_excluded_email_domains'] ?? ''));

		if (!$do_address && !$do_phone && !$do_email && !$do_notes) {
			return [
				'records_affected' => 0,
				'counts' => $counts,
				'notes' => [__('No order settings were selected for this run.', 'user-manager')],
				'supported_form_plugins' => [],
			];
		}

		if (!function_exists('wc_get_orders') || !function_exists('wc_get_order')) {
			return [
				'records_affected' => 0,
				'counts' => $counts,
				'notes' => [__('WooCommerce is not available. Orders anonymization was skipped.', 'user-manager')],
				'supported_form_plugins' => [],
			];
		}

		$page = 1;
		$max_pages = 1;
		do {
			$query = wc_get_orders([
				'return' => 'ids',
				'limit' => 200,
				'page' => $page,
				'paginate' => true,
			]);
			$order_ids = [];
			if (is_object($query) && isset($query->orders) && is_array($query->orders)) {
				$order_ids = $query->orders;
				$max_pages = isset($query->max_num_pages) ? max(1, (int) $query->max_num_pages) : 1;
			} elseif (is_array($query)) {
				$order_ids = $query;
				$max_pages = 1;
			}

			foreach ($order_ids as $order_id) {
				$order = wc_get_order((int) $order_id);
				if (!$order) {
					continue;
				}
				$counts['orders_scanned']++;

				$changed = false;
				$profile = self::data_anonymizer_generate_profile((int) $order_id);

				if ($do_address) {
					$order->set_billing_first_name($profile['first_name']);
					$order->set_billing_last_name($profile['last_name']);
					$order->set_billing_company($profile['company']);
					$order->set_billing_address_1($profile['address_1']);
					$order->set_billing_address_2($profile['address_2']);
					$order->set_billing_city($profile['city']);
					$order->set_billing_state($profile['state']);
					$order->set_billing_postcode($profile['postcode']);
					$order->set_billing_country($profile['country']);
					$order->set_shipping_first_name($profile['first_name']);
					$order->set_shipping_last_name($profile['last_name']);
					$order->set_shipping_company($profile['company']);
					$order->set_shipping_address_1($profile['address_1']);
					$order->set_shipping_address_2($profile['address_2']);
					$order->set_shipping_city($profile['city']);
					$order->set_shipping_state($profile['state']);
					$order->set_shipping_postcode($profile['postcode']);
					$order->set_shipping_country($profile['country']);
					$changed = true;
				}

				if ($do_phone) {
					$order->set_billing_phone('555-555-5555');
					if (method_exists($order, 'set_shipping_phone')) {
						$order->set_shipping_phone('555-555-5555');
					}
					$changed = true;
				}

				if ($do_email) {
					$current_email = (string) $order->get_billing_email();
					if (self::data_anonymizer_email_matches_excluded_domains($current_email, $excluded_domains)) {
						$counts['order_emails_skipped_excluded']++;
					} else {
						$order->set_billing_email(self::data_anonymizer_generate_email('order', (int) $order_id));
						$changed = true;
					}
				}

				if ($changed) {
					$order->save();
					$counts['orders_updated']++;
				}
			}

			$page++;
		} while ($page <= $max_pages);

		if ($do_notes) {
			global $wpdb;
			$comments_table = $wpdb->comments;
			$updated_notes = $wpdb->query(
				"UPDATE {$comments_table}
				 SET comment_content = CONCAT('Anonymized order note #', comment_ID, ' - ', SUBSTRING(MD5(CONCAT(RAND(), comment_ID)), 1, 12))
				 WHERE comment_type = 'order_note'"
			);
			$counts['order_notes_updated'] = is_numeric($updated_notes) ? (int) $updated_notes : 0;
		}

		$records_affected = (int) $counts['orders_updated'] + (int) $counts['order_notes_updated'];
		$notes[] = sprintf(
			/* translators: 1: scanned orders count, 2: updated orders count */
			__('Orders scanned: %1$d. Orders updated: %2$d.', 'user-manager'),
			(int) $counts['orders_scanned'],
			(int) $counts['orders_updated']
		);

		if ($do_email && !empty($counts['order_emails_skipped_excluded'])) {
			$notes[] = sprintf(
				/* translators: %d: skipped count */
				__('Skipped order email anonymization due to excluded domains: %d', 'user-manager'),
				(int) $counts['order_emails_skipped_excluded']
			);
		}

		if ($do_notes) {
			$notes[] = sprintf(
				/* translators: %d: updated notes count */
				__('Order notes anonymized: %d', 'user-manager'),
				(int) $counts['order_notes_updated']
			);
		}

		return [
			'records_affected' => $records_affected,
			'counts' => $counts,
			'notes' => $notes,
			'supported_form_plugins' => [],
		];
	}

	/**
	 * Run Users anonymization.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<string,mixed>
	 */
	private static function data_anonymizer_run_users(array $settings): array {
		$counts = [
			'users_scanned' => 0,
			'users_touched' => 0,
			'user_meta_address_rows_updated' => 0,
			'user_meta_phone_rows_updated' => 0,
			'user_emails_updated' => 0,
			'user_logins_updated' => 0,
			'user_identity_skipped_excluded' => 0,
			'users_skipped_wp_administrators' => 0,
			'users_skipped_admin_email_match' => 0,
		];
		$notes = [];

		$do_address_meta = !empty($settings['data_anonymizer_user_meta_address_random']);
		$do_phone_meta = !empty($settings['data_anonymizer_user_meta_phone_fixed']);
		$do_user_email = !empty($settings['data_anonymizer_user_email_random']);
		$do_user_login = !empty($settings['data_anonymizer_user_login_random']);
		$exclude_wp_administrators = array_key_exists('data_anonymizer_exclude_wp_administrators', $settings)
			? !empty($settings['data_anonymizer_exclude_wp_administrators'])
			: true;
		$exclude_if_matches_admin_email = array_key_exists('data_anonymizer_exclude_if_matches_admin_email', $settings)
			? !empty($settings['data_anonymizer_exclude_if_matches_admin_email'])
			: true;
		$excluded_domains = self::data_anonymizer_parse_excluded_domains((string) ($settings['data_anonymizer_excluded_email_domains'] ?? ''));
		$site_admin_email = strtolower(trim((string) get_option('admin_email')));

		if (!$do_address_meta && !$do_phone_meta && !$do_user_email && !$do_user_login) {
			return [
				'records_affected' => 0,
				'counts' => $counts,
				'notes' => [__('No user settings were selected for this run.', 'user-manager')],
				'supported_form_plugins' => [],
			];
		}

		$user_query = new WP_User_Query([
			'fields' => 'ID',
			'number' => -1,
		]);
		$users = $user_query->get_results();
		if (!is_array($users)) {
			$users = [];
		}

		global $wpdb;
		foreach ($users as $user_id_value) {
			$user_id = absint($user_id_value);
			if ($user_id <= 0) {
				continue;
			}
			$user_obj = get_userdata($user_id);
			if (!($user_obj instanceof WP_User)) {
				continue;
			}
			$original_email = (string) $user_obj->user_email;
			$counts['users_scanned']++;

			if ($exclude_wp_administrators && in_array('administrator', (array) $user_obj->roles, true)) {
				$counts['users_skipped_wp_administrators']++;
				continue;
			}

			if ($exclude_if_matches_admin_email && $site_admin_email !== '' && strtolower(trim($original_email)) === $site_admin_email) {
				$counts['users_skipped_admin_email_match']++;
				continue;
			}

			$touched_this_user = false;

			if ($do_address_meta) {
				$updated_rows = self::data_anonymizer_update_user_address_meta($user_id);
				$counts['user_meta_address_rows_updated'] += $updated_rows;
				if ($updated_rows > 0) {
					$touched_this_user = true;
				}
			}

			if ($do_phone_meta) {
				$updated_rows = self::data_anonymizer_update_user_phone_meta($user_id);
				$counts['user_meta_phone_rows_updated'] += $updated_rows;
				if ($updated_rows > 0) {
					$touched_this_user = true;
				}
			}

			$excluded_identity = self::data_anonymizer_email_matches_excluded_domains($original_email, $excluded_domains);
			if ($excluded_identity && ($do_user_email || $do_user_login)) {
				$counts['user_identity_skipped_excluded']++;
			}

			if ($do_user_email && !$excluded_identity) {
				$new_email = self::data_anonymizer_generate_unique_user_email($user_id);
				$updated = wp_update_user([
					'ID' => $user_id,
					'user_email' => $new_email,
				]);
				if (!is_wp_error($updated)) {
					$counts['user_emails_updated']++;
					$touched_this_user = true;
				}
			}

			if ($do_user_login && !$excluded_identity) {
				$new_login = self::data_anonymizer_generate_unique_user_login($user_id);
				$updated_rows = $wpdb->update(
					$wpdb->users,
					['user_login' => $new_login],
					['ID' => $user_id],
					['%s'],
					['%d']
				);
				if (is_numeric($updated_rows) && (int) $updated_rows > 0) {
					clean_user_cache($user_id);
					$counts['user_logins_updated']++;
					$touched_this_user = true;
				}
			}

			if ($touched_this_user) {
				$counts['users_touched']++;
			}
		}

		$records_affected =
			(int) $counts['user_meta_address_rows_updated'] +
			(int) $counts['user_meta_phone_rows_updated'] +
			(int) $counts['user_emails_updated'] +
			(int) $counts['user_logins_updated'];

		$notes[] = sprintf(
			/* translators: 1: scanned users, 2: touched users */
			__('Users scanned: %1$d. Users touched: %2$d.', 'user-manager'),
			(int) $counts['users_scanned'],
			(int) $counts['users_touched']
		);
		if (!empty($counts['user_identity_skipped_excluded'])) {
			$notes[] = sprintf(
				/* translators: %d: skipped count */
				__('Skipped user email/login anonymization due to excluded domains: %d', 'user-manager'),
				(int) $counts['user_identity_skipped_excluded']
			);
		}
		if (!empty($counts['users_skipped_wp_administrators'])) {
			$notes[] = sprintf(
				/* translators: %d: skipped administrators */
				__('Skipped users because they are WP Administrators: %d', 'user-manager'),
				(int) $counts['users_skipped_wp_administrators']
			);
		}
		if (!empty($counts['users_skipped_admin_email_match'])) {
			$notes[] = sprintf(
				/* translators: %d: skipped admin-email matches */
				__('Skipped users because email matches Administration Email Address: %d', 'user-manager'),
				(int) $counts['users_skipped_admin_email_match']
			);
		}

		return [
			'records_affected' => $records_affected,
			'counts' => $counts,
			'notes' => $notes,
			'supported_form_plugins' => [],
		];
	}

	/**
	 * Run Forms anonymization.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<string,mixed>
	 */
	private static function data_anonymizer_run_forms(array $settings): array {
		$counts = [
			'tables_detected' => 0,
			'tables_updated' => 0,
			'rows_updated' => 0,
		];
		$notes = [];
		$supported_plugins = [];

		if (empty($settings['data_anonymizer_forms_random'])) {
			return [
				'records_affected' => 0,
				'counts' => $counts,
				'notes' => [__('Form anonymization setting is not enabled.', 'user-manager')],
				'supported_form_plugins' => self::data_anonymizer_get_supported_form_plugins(),
			];
		}

		global $wpdb;
		$prefix = $wpdb->prefix;

		$table_specs = [
			[
				'plugin' => 'Contact Form 7 Database (CFDB7)',
				'table' => $prefix . 'db7_forms',
				'id_column' => 'form_id',
				'columns' => [
					'form_value' => 'text',
				],
			],
			[
				'plugin' => 'WPForms',
				'table' => $prefix . 'wpforms_entries',
				'id_column' => 'entry_id',
				'columns' => [
					'fields' => 'text',
					'ip_address' => 'ip',
					'user_agent' => 'ua',
				],
			],
			[
				'plugin' => 'Fluent Forms',
				'table' => $prefix . 'fluentform_submissions',
				'id_column' => 'id',
				'columns' => [
					'response' => 'text',
					'user_inputs' => 'text',
					'ip' => 'ip',
					'browser' => 'ua',
				],
			],
			[
				'plugin' => 'Gravity Forms',
				'table' => $prefix . 'gf_entry',
				'id_column' => 'id',
				'columns' => [
					'ip' => 'ip',
					'source_url' => 'url',
					'user_agent' => 'ua',
				],
			],
			[
				'plugin' => 'Gravity Forms',
				'table' => $prefix . 'gf_entry_meta',
				'id_column' => 'id',
				'columns' => [
					'meta_value' => 'text',
				],
			],
			[
				'plugin' => 'Ninja Forms',
				'table' => $prefix . 'nf3_submission_meta',
				'id_column' => 'id',
				'columns' => [
					'meta_value' => 'text',
				],
			],
			[
				'plugin' => 'Formidable Forms',
				'table' => $prefix . 'frm_item_metas',
				'id_column' => 'id',
				'columns' => [
					'meta_value' => 'text',
				],
			],
		];

		foreach ($table_specs as $spec) {
			$table_name = (string) $spec['table'];
			if (!self::data_anonymizer_table_exists($table_name)) {
				continue;
			}

			$counts['tables_detected']++;
			$supported_plugins[] = (string) $spec['plugin'];
			$updated_rows = self::data_anonymizer_update_table_rows(
				$table_name,
				(string) $spec['id_column'],
				is_array($spec['columns']) ? $spec['columns'] : []
			);
			if ($updated_rows > 0) {
				$counts['tables_updated']++;
				$counts['rows_updated'] += $updated_rows;
			}
		}

		// Flamingo stores CF7 submissions as posts/postmeta.
		$flamingo_rows = self::data_anonymizer_update_flamingo_submissions();
		if ($flamingo_rows > 0) {
			$counts['tables_detected'] += 2;
			$counts['tables_updated'] += 2;
			$counts['rows_updated'] += $flamingo_rows;
			$supported_plugins[] = 'Flamingo (Contact Form 7 storage)';
		}

		$supported_plugins = array_values(array_unique($supported_plugins));
		if (empty($supported_plugins)) {
			$notes[] = __('No supported form-data tables were detected in this database.', 'user-manager');
		} else {
			$notes[] = sprintf(
				/* translators: %s: plugin list */
				__('Detected form data sources: %s', 'user-manager'),
				implode(', ', $supported_plugins)
			);
		}

		return [
			'records_affected' => (int) $counts['rows_updated'],
			'counts' => $counts,
			'notes' => $notes,
			'supported_form_plugins' => self::data_anonymizer_get_supported_form_plugins(),
		];
	}

	/**
	 * Update rows in a form-submission table.
	 *
	 * @param array<string,string> $columns
	 */
	private static function data_anonymizer_update_table_rows(string $table_name, string $id_column, array $columns): int {
		global $wpdb;

		if (!self::data_anonymizer_table_has_column($table_name, $id_column)) {
			return 0;
		}

		$id_column_escaped = str_replace('`', '', $id_column);
		$set_clauses = [];
		foreach ($columns as $column_name => $mode) {
			$column_name = (string) $column_name;
			$mode = (string) $mode;
			if ($column_name === '' || !self::data_anonymizer_table_has_column($table_name, $column_name)) {
				continue;
			}

			if ($mode === 'ip') {
				$set_clauses[] = "`{$column_name}` = '127.0.0.1'";
			} elseif ($mode === 'ua') {
				$set_clauses[] = "`{$column_name}` = 'Data Anonymized'";
			} elseif ($mode === 'url') {
				$set_clauses[] = "`{$column_name}` = 'https://example.com/anonymized'";
			} else {
				$set_clauses[] = "`{$column_name}` = CONCAT('Anonymized-', SUBSTRING(MD5(CONCAT(RAND(), `{$id_column_escaped}`)), 1, 16))";
			}
		}

		if (empty($set_clauses)) {
			return 0;
		}

		$sql = "UPDATE `{$table_name}` SET " . implode(', ', $set_clauses);
		$updated_rows = $wpdb->query($sql);
		return is_numeric($updated_rows) ? (int) $updated_rows : 0;
	}

	/**
	 * Update Flamingo submissions data (CF7 storage plugin).
	 */
	private static function data_anonymizer_update_flamingo_submissions(): int {
		global $wpdb;
		$posts_table = $wpdb->posts;
		$postmeta_table = $wpdb->postmeta;

		$post_ids = $wpdb->get_col(
			"SELECT ID FROM {$posts_table} WHERE post_type = 'flamingo_inbound'"
		);
		if (!is_array($post_ids) || empty($post_ids)) {
			return 0;
		}

		$ids = array_map('intval', $post_ids);
		$ids_sql = implode(',', $ids);
		if ($ids_sql === '') {
			return 0;
		}

		$updated_posts = $wpdb->query(
			"UPDATE {$posts_table}
			 SET post_title = CONCAT('Anonymized Submission #', ID),
			     post_excerpt = CONCAT('Anonymized-', SUBSTRING(MD5(CONCAT(RAND(), ID)), 1, 16)),
			     post_content = CONCAT('Anonymized-', SUBSTRING(MD5(CONCAT(RAND(), ID)), 1, 24))
			 WHERE ID IN ({$ids_sql})"
		);
		$updated_meta = $wpdb->query(
			"UPDATE {$postmeta_table}
			 SET meta_value = CONCAT('Anonymized-', SUBSTRING(MD5(CONCAT(RAND(), meta_id)), 1, 16))
			 WHERE post_id IN ({$ids_sql})"
		);

		$total = 0;
		if (is_numeric($updated_posts)) {
			$total += (int) $updated_posts;
		}
		if (is_numeric($updated_meta)) {
			$total += (int) $updated_meta;
		}
		return $total;
	}

	/**
	 * Check whether a database table exists.
	 */
	private static function data_anonymizer_table_exists(string $table_name): bool {
		global $wpdb;
		$found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name));
		return is_string($found) && $found === $table_name;
	}

	/**
	 * Check whether a table has a specific column.
	 */
	private static function data_anonymizer_table_has_column(string $table_name, string $column_name): bool {
		global $wpdb;
		$found = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table_name}` LIKE %s", $column_name));
		return is_string($found) && $found === $column_name;
	}

	/**
	 * Update user address-related meta rows with randomized values.
	 */
	private static function data_anonymizer_update_user_address_meta(int $user_id): int {
		global $wpdb;
		$usermeta_table = $wpdb->usermeta;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT umeta_id, meta_key
				 FROM {$usermeta_table}
				 WHERE user_id = %d
				   AND (
				     LOWER(meta_key) LIKE 'billing_%%'
				     OR LOWER(meta_key) LIKE 'shipping_%%'
				     OR LOWER(meta_key) LIKE '%%address%%'
				     OR LOWER(meta_key) LIKE '%%city%%'
				     OR LOWER(meta_key) LIKE '%%state%%'
				     OR LOWER(meta_key) LIKE '%%postcode%%'
				     OR LOWER(meta_key) LIKE '%%zip%%'
				     OR LOWER(meta_key) LIKE '%%country%%'
				   )",
				$user_id
			),
			ARRAY_A
		);
		if (!is_array($rows) || empty($rows)) {
			return 0;
		}

		$profile = self::data_anonymizer_generate_profile($user_id);
		$updated = 0;
		foreach ($rows as $row) {
			$umeta_id = isset($row['umeta_id']) ? (int) $row['umeta_id'] : 0;
			$meta_key = isset($row['meta_key']) ? (string) $row['meta_key'] : '';
			if ($umeta_id <= 0 || $meta_key === '') {
				continue;
			}
			$new_value = self::data_anonymizer_get_address_value_for_meta_key($meta_key, $profile);
			$changed = $wpdb->update(
				$usermeta_table,
				['meta_value' => $new_value],
				['umeta_id' => $umeta_id],
				['%s'],
				['%d']
			);
			if (is_numeric($changed) && (int) $changed > 0) {
				$updated++;
			}
		}

		return $updated;
	}

	/**
	 * Update user phone-related meta rows with fixed placeholder number.
	 */
	private static function data_anonymizer_update_user_phone_meta(int $user_id): int {
		global $wpdb;
		$usermeta_table = $wpdb->usermeta;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT umeta_id
				 FROM {$usermeta_table}
				 WHERE user_id = %d
				   AND (
				     LOWER(meta_key) LIKE '%%phone%%'
				     OR LOWER(meta_key) LIKE '%%mobile%%'
				     OR LOWER(meta_key) LIKE '%%tel%%'
				   )",
				$user_id
			),
			ARRAY_A
		);
		if (!is_array($rows) || empty($rows)) {
			return 0;
		}

		$updated = 0;
		foreach ($rows as $row) {
			$umeta_id = isset($row['umeta_id']) ? (int) $row['umeta_id'] : 0;
			if ($umeta_id <= 0) {
				continue;
			}
			$changed = $wpdb->update(
				$usermeta_table,
				['meta_value' => '555-555-5555'],
				['umeta_id' => $umeta_id],
				['%s'],
				['%d']
			);
			if (is_numeric($changed) && (int) $changed > 0) {
				$updated++;
			}
		}

		return $updated;
	}

	/**
	 * Build one history row and persist it.
	 *
	 * @param array<string,mixed> $settings
	 * @param array<string,mixed> $result
	 * @return array<string,mixed>
	 */
	private static function data_anonymizer_append_history(string $run_type, array $settings, array $result): array {
		$history = get_option('user_manager_data_anonymizer_history', []);
		if (!is_array($history)) {
			$history = [];
		}

		$current_user_id = get_current_user_id();
		$history_entry = [
			'id' => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('um-data-anonymizer-', true),
			'run_type' => $run_type,
			'run_by' => $current_user_id,
			'created_at' => current_time('mysql'),
			'settings_checked' => self::data_anonymizer_get_checked_setting_labels($settings),
			'records_affected' => (int) ($result['records_affected'] ?? 0),
			'counts' => isset($result['counts']) && is_array($result['counts']) ? $result['counts'] : [],
			'notes' => isset($result['notes']) && is_array($result['notes']) ? $result['notes'] : [],
			'supported_form_plugins' => isset($result['supported_form_plugins']) && is_array($result['supported_form_plugins']) ? $result['supported_form_plugins'] : [],
		];

		array_unshift($history, $history_entry);
		if (count($history) > 300) {
			$history = array_slice($history, 0, 300);
		}
		update_option('user_manager_data_anonymizer_history', $history);

		return $history_entry;
	}

	/**
	 * @param array<string,mixed> $settings
	 * @return array<int,string>
	 */
	private static function data_anonymizer_get_checked_setting_labels(array $settings): array {
		$label_map = [
			'data_anonymizer_order_address_random' => __('Order: Replace address data with random values', 'user-manager'),
			'data_anonymizer_order_phone_fixed' => __('Order: Replace phone numbers with 555-555-5555', 'user-manager'),
			'data_anonymizer_order_email_random' => __('Order: Replace email addresses with random emails', 'user-manager'),
			'data_anonymizer_order_notes_random' => __('Order: Replace note data with random notes', 'user-manager'),
			'data_anonymizer_user_meta_address_random' => __('User: Replace address meta with random values', 'user-manager'),
			'data_anonymizer_user_meta_phone_fixed' => __('User: Replace phone meta with 555-555-5555', 'user-manager'),
			'data_anonymizer_user_email_random' => __('User: Replace email addresses with random emails', 'user-manager'),
			'data_anonymizer_user_login_random' => __('User: Replace logins with random logins', 'user-manager'),
			'data_anonymizer_forms_random' => __('Forms: Replace submissions with random data', 'user-manager'),
			'data_anonymizer_exclude_wp_administrators' => __('Exceptions: Exclude All WP Administrators', 'user-manager'),
			'data_anonymizer_exclude_if_matches_admin_email' => __('Exceptions: Exclude User if Email Address Matches Administration Email Address', 'user-manager'),
		];

		$checked = [];
		foreach ($label_map as $settings_key => $label) {
			if (!empty($settings[$settings_key])) {
				$checked[] = $label;
			}
		}

		$excluded_domains = trim((string) ($settings['data_anonymizer_excluded_email_domains'] ?? ''));
		if ($excluded_domains !== '') {
			$checked[] = sprintf(
				/* translators: %s: comma-separated domains */
				__('Exceptions: excluded domains = %s', 'user-manager'),
				$excluded_domains
			);
		}

		return $checked;
	}

	/**
	 * Parse comma-separated excluded email domains.
	 *
	 * @return array<int,string>
	 */
	private static function data_anonymizer_parse_excluded_domains(string $domains): array {
		$parts = array_filter(array_map('trim', explode(',', strtolower($domains))));
		$normalized = [];
		foreach ($parts as $domain) {
			$domain = ltrim((string) $domain, '@.');
			if ($domain !== '') {
				$normalized[] = $domain;
			}
		}
		return array_values(array_unique($normalized));
	}

	/**
	 * Whether an email matches any excluded domain.
	 *
	 * @param array<int,string> $excluded_domains
	 */
	private static function data_anonymizer_email_matches_excluded_domains(string $email, array $excluded_domains): bool {
		$email = strtolower(trim($email));
		if ($email === '' || empty($excluded_domains)) {
			return false;
		}
		foreach ($excluded_domains as $domain) {
			if ($domain !== '' && strpos($email, $domain) !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Generate randomized profile data for address/name fields.
	 *
	 * @return array<string,string>
	 */
	private static function data_anonymizer_generate_profile(int $seed = 0): array {
		$first_names = ['Alex', 'Jordan', 'Taylor', 'Morgan', 'Riley', 'Casey', 'Jamie', 'Avery'];
		$last_names = ['Smith', 'Johnson', 'Brown', 'Miller', 'Davis', 'Wilson', 'Anderson', 'Clark'];
		$streets = ['Maple St', 'Oak Ave', 'Cedar Rd', 'Pine Ln', 'Lakeview Dr', 'Sunset Blvd', 'River Rd', 'Elm St'];
		$cities = ['Springfield', 'Riverton', 'Lakeside', 'Fairview', 'Franklin', 'Georgetown', 'Madison', 'Milton'];
		$states = ['CA', 'TX', 'FL', 'NY', 'MN', 'AZ', 'WA', 'CO'];

		$seed = absint($seed);
		$first_name = $first_names[$seed % count($first_names)];
		$last_name = $last_names[$seed % count($last_names)];
		$street = $streets[$seed % count($streets)];
		$city = $cities[$seed % count($cities)];
		$state = $states[$seed % count($states)];
		$house_number = (string) wp_rand(100, 9999);
		$postcode = str_pad((string) wp_rand(10000, 99999), 5, '0', STR_PAD_LEFT);

		return [
			'first_name' => $first_name,
			'last_name' => $last_name,
			'company' => 'Anonymized Co',
			'address_1' => $house_number . ' ' . $street,
			'address_2' => 'Suite ' . (string) wp_rand(100, 999),
			'city' => $city,
			'state' => $state,
			'postcode' => $postcode,
			'country' => 'US',
		];
	}

	/**
	 * Resolve randomized address value by meta key.
	 *
	 * @param array<string,string> $profile
	 */
	private static function data_anonymizer_get_address_value_for_meta_key(string $meta_key, array $profile): string {
		$key = strtolower($meta_key);
		if (strpos($key, 'first_name') !== false) {
			return $profile['first_name'];
		}
		if (strpos($key, 'last_name') !== false) {
			return $profile['last_name'];
		}
		if (strpos($key, 'company') !== false) {
			return $profile['company'];
		}
		if (strpos($key, 'address_1') !== false || strpos($key, 'address1') !== false) {
			return $profile['address_1'];
		}
		if (strpos($key, 'address_2') !== false || strpos($key, 'address2') !== false) {
			return $profile['address_2'];
		}
		if (strpos($key, 'city') !== false) {
			return $profile['city'];
		}
		if (strpos($key, 'state') !== false) {
			return $profile['state'];
		}
		if (strpos($key, 'postcode') !== false || strpos($key, 'zip') !== false) {
			return $profile['postcode'];
		}
		if (strpos($key, 'country') !== false) {
			return $profile['country'];
		}
		return 'Anonymized';
	}

	/**
	 * Generate a random email string.
	 */
	private static function data_anonymizer_generate_email(string $prefix, int $seed): string {
		$rand = substr(md5((string) wp_rand(1000, 999999) . '-' . $seed . '-' . microtime(true)), 0, 10);
		return sanitize_email($prefix . '-' . $seed . '-' . $rand . '@example.test');
	}

	/**
	 * Generate a unique user email.
	 */
	private static function data_anonymizer_generate_unique_user_email(int $user_id): string {
		for ($i = 0; $i < 50; $i++) {
			$email = self::data_anonymizer_generate_email('user', $user_id + $i);
			if ($email !== '' && !email_exists($email)) {
				return $email;
			}
		}
		return self::data_anonymizer_generate_email('user', $user_id + wp_rand(1000, 9999));
	}

	/**
	 * Generate a unique user_login value.
	 */
	private static function data_anonymizer_generate_unique_user_login(int $user_id): string {
		for ($i = 0; $i < 50; $i++) {
			$candidate = 'anon_user_' . $user_id . '_' . substr(md5((string) wp_rand(1000, 999999) . '-' . $i), 0, 6);
			$candidate = sanitize_user($candidate, true);
			if ($candidate !== '' && !username_exists($candidate)) {
				return $candidate;
			}
		}
		return sanitize_user('anon_user_' . $user_id . '_' . wp_rand(10000, 99999), true);
	}

	/**
	 * Full list of form plugins/tables this anonymizer supports.
	 *
	 * @return array<int,string>
	 */
	private static function data_anonymizer_get_supported_form_plugins(): array {
		global $wpdb;
		$prefix = isset($wpdb->prefix) ? (string) $wpdb->prefix : 'wp_';
		return [
			'Contact Form 7 Database (CFDB7) - table: ' . $prefix . 'db7_forms',
			'WPForms - table: ' . $prefix . 'wpforms_entries',
			'Fluent Forms - table: ' . $prefix . 'fluentform_submissions',
			'Gravity Forms - tables: ' . $prefix . 'gf_entry, ' . $prefix . 'gf_entry_meta',
			'Ninja Forms - table: ' . $prefix . 'nf3_submission_meta',
			'Formidable Forms - table: ' . $prefix . 'frm_item_metas',
			'Flamingo (CF7 storage) - wp_posts/wp_postmeta where post_type = flamingo_inbound',
		];
	}
}

