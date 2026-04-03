<?php
/**
 * Emali Log helpers.
 */
if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Emali_Log_Trait {

	/**
	 * Pending email log IDs grouped by payload hash for this request.
	 *
	 * @var array<string,array<int,int>>
	 */
	private static array $emali_log_pending_ids_by_hash = [];

	/**
	 * Prevent repeated dbDelta calls in same request.
	 */
	private static bool $emali_log_table_checked = false;

	/**
	 * Boot hooks for Emali Log add-on.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_emali_log(array $settings): void {
		if (empty($settings['emali_log_enabled']) || self::is_addon_temporarily_disabled('emali-log')) {
			return;
		}

		self::ensure_emali_log_table();
		add_filter('wp_mail', [__CLASS__, 'emali_log_capture_wp_mail'], 5, 1);
		add_action('wp_mail_succeeded', [__CLASS__, 'emali_log_mark_wp_mail_succeeded'], 10, 1);
		add_action('wp_mail_failed', [__CLASS__, 'emali_log_mark_wp_mail_failed'], 10, 1);
	}

	/**
	 * Get database table name for the Emali Log add-on.
	 */
	public static function get_emali_log_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'um_email_log';
	}

	/**
	 * Ensure the Emali Log table exists.
	 */
	public static function ensure_emali_log_table(): void {
		if (self::$emali_log_table_checked) {
			return;
		}
		self::$emali_log_table_checked = true;

		global $wpdb;
		$table_name = self::get_emali_log_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			created_at DATETIME NOT NULL,
			sent_at DATETIME NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			to_recipients LONGTEXT NULL,
			subject TEXT NULL,
			message LONGTEXT NULL,
			headers LONGTEXT NULL,
			attachments LONGTEXT NULL,
			from_header TEXT NULL,
			reply_to_header TEXT NULL,
			cc_header TEXT NULL,
			bcc_header TEXT NULL,
			content_type VARCHAR(100) NULL,
			error_message LONGTEXT NULL,
			request_uri TEXT NULL,
			trigger_user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			payload_hash VARCHAR(64) NULL,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY created_at (created_at),
			KEY payload_hash (payload_hash)
		) {$charset_collate};";

		dbDelta($sql);
	}

	/**
	 * Capture outgoing wp_mail payload before send.
	 *
	 * @param array<string,mixed> $atts Email payload.
	 * @return array<string,mixed>
	 */
	public static function emali_log_capture_wp_mail($atts) {
		if (!is_array($atts)) {
			return $atts;
		}

		$normalized = self::emali_log_normalize_payload($atts);
		$log_id = self::emali_log_insert_pending_row($normalized);
		if ($log_id > 0 && $normalized['payload_hash'] !== '') {
			if (!isset(self::$emali_log_pending_ids_by_hash[$normalized['payload_hash']])) {
				self::$emali_log_pending_ids_by_hash[$normalized['payload_hash']] = [];
			}
			self::$emali_log_pending_ids_by_hash[$normalized['payload_hash']][] = $log_id;
		}

		return $atts;
	}

	/**
	 * Mark wp_mail payload as sent successfully.
	 *
	 * @param array<string,mixed>|object $mail_data Sent mail data.
	 */
	public static function emali_log_mark_wp_mail_succeeded($mail_data): void {
		$mail_array = is_array($mail_data) ? $mail_data : [];
		$normalized = self::emali_log_normalize_payload($mail_array);
		$log_id = self::emali_log_shift_pending_id_by_hash($normalized['payload_hash']);
		if ($log_id <= 0) {
			$log_id = self::emali_log_find_latest_pending_id($normalized['payload_hash']);
		}
		if ($log_id <= 0) {
			return;
		}

		global $wpdb;
		$wpdb->update(
			self::get_emali_log_table_name(),
			[
				'status'  => 'sent',
				'sent_at' => current_time('mysql'),
			],
			['id' => $log_id],
			['%s', '%s'],
			['%d']
		);
	}

	/**
	 * Mark wp_mail payload as failed.
	 *
	 * @param WP_Error $wp_error Error object.
	 */
	public static function emali_log_mark_wp_mail_failed($wp_error): void {
		$data = ($wp_error instanceof WP_Error) ? $wp_error->get_error_data() : [];
		$mail_array = is_array($data) ? $data : [];
		$normalized = self::emali_log_normalize_payload($mail_array);
		$log_id = self::emali_log_shift_pending_id_by_hash($normalized['payload_hash']);
		if ($log_id <= 0) {
			$log_id = self::emali_log_find_latest_pending_id($normalized['payload_hash']);
		}
		if ($log_id <= 0) {
			return;
		}

		$error_message = ($wp_error instanceof WP_Error) ? $wp_error->get_error_message() : __('Unknown email send failure', 'user-manager');
		global $wpdb;
		$wpdb->update(
			self::get_emali_log_table_name(),
			[
				'status'        => 'failed',
				'error_message' => (string) $error_message,
				'sent_at'       => current_time('mysql'),
			],
			['id' => $log_id],
			['%s', '%s', '%s'],
			['%d']
		);
	}

	/**
	 * Get latest Emali Log rows.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_emali_log_entries(int $limit = 50, int $offset = 0, string $status = '', string $search = ''): array {
		self::ensure_emali_log_table();
		global $wpdb;
		$table_name = self::get_emali_log_table_name();
		$limit = max(1, min(500, $limit));
		$offset = max(0, $offset);
		$status = sanitize_key($status);
		$search = trim((string) $search);

		$where_parts = ['1=1'];
		$params = [];

		if (in_array($status, ['pending', 'sent', 'failed'], true)) {
			$where_parts[] = 'status = %s';
			$params[] = $status;
		}
		if ($search !== '') {
			$like = '%' . $wpdb->esc_like($search) . '%';
			$where_parts[] = '(subject LIKE %s OR to_recipients LIKE %s OR from_header LIKE %s OR reply_to_header LIKE %s OR cc_header LIKE %s OR bcc_header LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$where_sql = implode(' AND ', $where_parts);
		$params[] = $limit;
		$params[] = $offset;
		$query = $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d",
			$params
		);

		$rows = $wpdb->get_results($query, ARRAY_A);
		return is_array($rows) ? $rows : [];
	}

	/**
	 * Get total Emali Log row count for current filters.
	 */
	public static function get_emali_log_total_count(string $status = '', string $search = ''): int {
		self::ensure_emali_log_table();
		global $wpdb;
		$table_name = self::get_emali_log_table_name();
		$status = sanitize_key($status);
		$search = trim((string) $search);

		$where_parts = ['1=1'];
		$params = [];
		if (in_array($status, ['pending', 'sent', 'failed'], true)) {
			$where_parts[] = 'status = %s';
			$params[] = $status;
		}
		if ($search !== '') {
			$like = '%' . $wpdb->esc_like($search) . '%';
			$where_parts[] = '(subject LIKE %s OR to_recipients LIKE %s OR from_header LIKE %s OR reply_to_header LIKE %s OR cc_header LIKE %s OR bcc_header LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$where_sql = implode(' AND ', $where_parts);
		if (!empty($params)) {
			$query = $wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}", $params);
		} else {
			$query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}";
		}
		return (int) $wpdb->get_var($query);
	}

	/**
	 * Get count stats for recent send windows.
	 *
	 * @return array{hour:int,day:int,week:int,month:int,total:int}
	 */
	public static function get_emali_log_stats(): array {
		self::ensure_emali_log_table();
		global $wpdb;
		$table_name = self::get_emali_log_table_name();
		$now = current_time('timestamp');
		$windows = [
			'hour'  => $now - HOUR_IN_SECONDS,
			'day'   => $now - DAY_IN_SECONDS,
			'week'  => $now - WEEK_IN_SECONDS,
			'month' => $now - MONTH_IN_SECONDS,
		];
		$stats = [
			'hour' => 0,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'total' => 0,
		];

		foreach ($windows as $key => $timestamp) {
			$threshold = wp_date('Y-m-d H:i:s', $timestamp, wp_timezone());
			$stats[$key] = (int) $wpdb->get_var(
				$wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE created_at >= %s", $threshold)
			);
		}
		$stats['total'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
		return $stats;
	}

	/**
	 * Fetch one Emali Log row by ID.
	 *
	 * @return array<string,mixed>|null
	 */
	public static function get_emali_log_entry(int $log_id): ?array {
		$log_id = absint($log_id);
		if ($log_id <= 0) {
			return null;
		}
		self::ensure_emali_log_table();
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM " . self::get_emali_log_table_name() . " WHERE id = %d LIMIT 1", $log_id),
			ARRAY_A
		);
		return is_array($row) ? $row : null;
	}

	/**
	 * Resend an email row using original payload.
	 */
	public static function resend_emali_log_entry(int $log_id): bool {
		$entry = self::get_emali_log_entry($log_id);
		if (!$entry) {
			return false;
		}
		$to = self::emali_log_decode_json_array($entry['to_recipients'] ?? '');
		$headers = self::emali_log_decode_json_array($entry['headers'] ?? '');
		$attachments = self::emali_log_decode_json_array($entry['attachments'] ?? '');
		$subject = isset($entry['subject']) ? (string) $entry['subject'] : '';
		$message = isset($entry['message']) ? (string) $entry['message'] : '';
		if (empty($to)) {
			return false;
		}
		return wp_mail($to, $subject, $message, $headers, $attachments);
	}

	/**
	 * Forward an email row to another address.
	 */
	public static function forward_emali_log_entry(int $log_id, string $forward_email): bool {
		$entry = self::get_emali_log_entry($log_id);
		$forward_email = sanitize_email($forward_email);
		if (!$entry || $forward_email === '' || !is_email($forward_email)) {
			return false;
		}
		$headers = self::emali_log_decode_json_array($entry['headers'] ?? '');
		$attachments = self::emali_log_decode_json_array($entry['attachments'] ?? '');
		$subject = isset($entry['subject']) ? (string) $entry['subject'] : '';
		$message = isset($entry['message']) ? (string) $entry['message'] : '';
		return wp_mail($forward_email, $subject, $message, $headers, $attachments);
	}

	/**
	 * Clear all Emali Log history rows.
	 */
	public static function clear_emali_log_history(): void {
		self::ensure_emali_log_table();
		global $wpdb;
		$table_name = self::get_emali_log_table_name();
		$wpdb->query("TRUNCATE TABLE {$table_name}");
	}

	/**
	 * @param array<string,mixed> $atts
	 * @return array{to:array<int,string>,subject:string,message:string,headers:array<int,string>,attachments:array<int,string>,from:string,reply_to:string,cc:string,bcc:string,content_type:string,payload_hash:string}
	 */
	private static function emali_log_normalize_payload(array $atts): array {
		$to = self::emali_log_normalize_to_list($atts['to'] ?? []);
		$subject = isset($atts['subject']) ? (string) $atts['subject'] : '';
		$message = isset($atts['message']) ? (string) $atts['message'] : '';
		$headers = self::emali_log_normalize_header_list($atts['headers'] ?? []);
		$attachments = self::emali_log_normalize_header_list($atts['attachments'] ?? []);
		$parsed = self::emali_log_parse_standard_headers($headers);

		return [
			'to'           => $to,
			'subject'      => $subject,
			'message'      => $message,
			'headers'      => $headers,
			'attachments'  => $attachments,
			'from'         => $parsed['from'],
			'reply_to'     => $parsed['reply_to'],
			'cc'           => $parsed['cc'],
			'bcc'          => $parsed['bcc'],
			'content_type' => $parsed['content_type'],
			'payload_hash' => md5(wp_json_encode([$to, $subject, $message, $headers, $attachments])),
		];
	}

	/**
	 * @param array{to:array<int,string>,subject:string,message:string,headers:array<int,string>,attachments:array<int,string>,from:string,reply_to:string,cc:string,bcc:string,content_type:string,payload_hash:string} $normalized
	 */
	private static function emali_log_insert_pending_row(array $normalized): int {
		self::ensure_emali_log_table();
		global $wpdb;
		$inserted = $wpdb->insert(
			self::get_emali_log_table_name(),
			[
				'created_at'      => current_time('mysql'),
				'status'          => 'pending',
				'to_recipients'   => wp_json_encode($normalized['to']),
				'subject'         => $normalized['subject'],
				'message'         => $normalized['message'],
				'headers'         => wp_json_encode($normalized['headers']),
				'attachments'     => wp_json_encode($normalized['attachments']),
				'from_header'     => $normalized['from'],
				'reply_to_header' => $normalized['reply_to'],
				'cc_header'       => $normalized['cc'],
				'bcc_header'      => $normalized['bcc'],
				'content_type'    => $normalized['content_type'],
				'request_uri'     => isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '',
				'trigger_user_id' => get_current_user_id(),
				'payload_hash'    => $normalized['payload_hash'],
			],
			['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
		);

		if (!$inserted) {
			return 0;
		}
		return (int) $wpdb->insert_id;
	}

	private static function emali_log_shift_pending_id_by_hash(string $hash): int {
		if ($hash === '' || empty(self::$emali_log_pending_ids_by_hash[$hash])) {
			return 0;
		}
		$id = array_shift(self::$emali_log_pending_ids_by_hash[$hash]);
		return (int) $id;
	}

	private static function emali_log_find_latest_pending_id(string $hash): int {
		self::ensure_emali_log_table();
		global $wpdb;
		if ($hash !== '') {
			$id = (int) $wpdb->get_var($wpdb->prepare(
				"SELECT id FROM " . self::get_emali_log_table_name() . " WHERE status = 'pending' AND payload_hash = %s ORDER BY id DESC LIMIT 1",
				$hash
			));
			if ($id > 0) {
				return $id;
			}
		}
		return (int) $wpdb->get_var("SELECT id FROM " . self::get_emali_log_table_name() . " WHERE status = 'pending' ORDER BY id DESC LIMIT 1");
	}

	/**
	 * @param mixed $value
	 * @return array<int,string>
	 */
	private static function emali_log_normalize_to_list($value): array {
		if (is_array($value)) {
			$list = $value;
		} else {
			$list = preg_split('/[\r\n,;]+/', (string) $value) ?: [];
		}
		$list = array_values(array_filter(array_map(static function ($entry): string {
			return trim((string) $entry);
		}, $list)));
		return $list;
	}

	/**
	 * @param mixed $value
	 * @return array<int,string>
	 */
	private static function emali_log_normalize_header_list($value): array {
		if (is_array($value)) {
			$list = $value;
		} else {
			$list = preg_split('/\r\n|\r|\n/', (string) $value) ?: [];
		}
		$list = array_values(array_filter(array_map(static function ($entry): string {
			return trim((string) $entry);
		}, $list), static function (string $entry): bool {
			return $entry !== '';
		}));
		return $list;
	}

	/**
	 * @param array<int,string> $headers
	 * @return array{from:string,reply_to:string,cc:string,bcc:string,content_type:string}
	 */
	private static function emali_log_parse_standard_headers(array $headers): array {
		$parsed = [
			'from' => '',
			'reply_to' => '',
			'cc' => '',
			'bcc' => '',
			'content_type' => '',
		];
		foreach ($headers as $header_line) {
			$parts = explode(':', $header_line, 2);
			if (count($parts) !== 2) {
				continue;
			}
			$key = strtolower(trim((string) $parts[0]));
			$value = trim((string) $parts[1]);
			if ($key === 'from') {
				$parsed['from'] = $value;
			} elseif ($key === 'reply-to') {
				$parsed['reply_to'] = $value;
			} elseif ($key === 'cc') {
				$parsed['cc'] = $value;
			} elseif ($key === 'bcc') {
				$parsed['bcc'] = $value;
			} elseif ($key === 'content-type') {
				$parsed['content_type'] = $value;
			}
		}
		return $parsed;
	}

	/**
	 * @param mixed $raw
	 * @return array<int,string>
	 */
	public static function emali_log_decode_json_array($raw): array {
		$decoded = json_decode((string) $raw, true);
		if (!is_array($decoded)) {
			return [];
		}
		$list = [];
		foreach ($decoded as $item) {
			$item = trim((string) $item);
			if ($item !== '') {
				$list[] = $item;
			}
		}
		return $list;
	}
}
