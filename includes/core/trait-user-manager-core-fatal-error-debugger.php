<?php
/**
 * Fatal Error Debugger helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Fatal_Error_Debugger_Trait {
	/**
	 * Ensure shutdown callback is only registered once.
	 *
	 * @var bool
	 */
	private static bool $fatal_error_debugger_shutdown_registered = false;

	/**
	 * Register shutdown capture for fatal errors.
	 */
	public static function register_fatal_error_debugger_shutdown_handler(): void {
		if (self::$fatal_error_debugger_shutdown_registered) {
			return;
		}
		self::$fatal_error_debugger_shutdown_registered = true;
		register_shutdown_function([__CLASS__, 'capture_fatal_error_debugger_shutdown']);
	}

	/**
	 * Capture fatal errors at shutdown and store for admin front-end display.
	 */
	public static function capture_fatal_error_debugger_shutdown(): void {
		if (function_exists('is_admin') && is_admin()) {
			return;
		}
		if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
			return;
		}

		$last_error = error_get_last();
		if (!is_array($last_error) || empty($last_error['type'])) {
			return;
		}
		if (!self::is_fatal_error_debugger_type((int) $last_error['type'])) {
			return;
		}

		$settings = self::get_settings();
		if (empty($settings['fatal_error_debugger_enabled'])) {
			return;
		}

		$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
		$current_url = function_exists('home_url') ? home_url($request_uri) : $request_uri;
		$payload = [
			'timestamp'   => function_exists('current_time') ? current_time('mysql') : gmdate('Y-m-d H:i:s'),
			'error_type'  => (int) $last_error['type'],
			'message'     => isset($last_error['message']) ? sanitize_text_field((string) $last_error['message']) : '',
			'file'        => isset($last_error['file']) ? sanitize_text_field((string) $last_error['file']) : '',
			'line'        => isset($last_error['line']) ? absint($last_error['line']) : 0,
			'request_uri' => sanitize_text_field($request_uri),
			'url'         => esc_url_raw((string) $current_url),
		];
		set_transient('um_fatal_error_debugger_last_error', $payload, DAY_IN_SECONDS * 7);

		$email = isset($settings['fatal_error_debugger_email']) ? sanitize_email((string) $settings['fatal_error_debugger_email']) : '';
		if ($email !== '' && function_exists('wp_mail')) {
			$hash_basis = $payload['message'] . '|' . $payload['file'] . '|' . (string) $payload['line'];
			$error_hash = md5($hash_basis);
			$last_sent_hash = get_transient('um_fatal_error_debugger_last_mail_hash');
			if (!is_string($last_sent_hash) || $last_sent_hash !== $error_hash) {
				$subject = sprintf(
					/* translators: %s: site name */
					__('Fatal Error Captured on %s', 'user-manager'),
					wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)
				);
				$lines = [
					'User Manager Fatal Error Debugger captured a fatal error.',
					'',
					'Time: ' . (string) $payload['timestamp'],
					'Type: ' . (string) $payload['error_type'],
					'Message: ' . (string) $payload['message'],
					'File: ' . (string) $payload['file'],
					'Line: ' . (string) $payload['line'],
					'URL: ' . (string) $payload['url'],
				];
				wp_mail($email, $subject, implode("\n", $lines));
				set_transient('um_fatal_error_debugger_last_mail_hash', $error_hash, MINUTE_IN_SECONDS * 10);
			}
		}
	}

	/**
	 * Render latest fatal error details on front-end for administrators.
	 */
	public static function maybe_render_fatal_error_debugger_panel(): void {
		if (is_admin() || wp_doing_ajax()) {
			return;
		}
		if (!is_user_logged_in() || !current_user_can('manage_options')) {
			return;
		}

		$settings = self::get_settings();
		if (empty($settings['fatal_error_debugger_enabled'])) {
			return;
		}

		$payload = get_transient('um_fatal_error_debugger_last_error');
		if (!is_array($payload) || empty($payload['message'])) {
			return;
		}

		$time = isset($payload['timestamp']) ? (string) $payload['timestamp'] : '';
		$message = isset($payload['message']) ? (string) $payload['message'] : '';
		$file = isset($payload['file']) ? (string) $payload['file'] : '';
		$line = isset($payload['line']) ? (int) $payload['line'] : 0;
		$url = isset($payload['url']) ? (string) $payload['url'] : '';
		$type = isset($payload['error_type']) ? (int) $payload['error_type'] : 0;
		?>
		<div class="um-fatal-error-debugger-panel" style="position:fixed;left:20px;bottom:20px;z-index:99999;max-width:760px;width:calc(100vw - 40px);max-height:75vh;overflow:auto;background:#fff;border:2px solid #d63638;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,.25);padding:14px;">
			<h3 style="margin:0 0 10px;color:#d63638;"><?php esc_html_e('Fatal Error Debugger', 'user-manager'); ?></h3>
			<p style="margin:0 0 8px;font-size:12px;">
				<strong><?php esc_html_e('Captured:', 'user-manager'); ?></strong> <?php echo esc_html($time !== '' ? $time : __('Unknown', 'user-manager')); ?>
			</p>
			<p style="margin:0 0 8px;font-size:12px;">
				<strong><?php esc_html_e('Type:', 'user-manager'); ?></strong> <?php echo esc_html((string) $type); ?>
			</p>
			<p style="margin:0 0 8px;font-size:12px;">
				<strong><?php esc_html_e('Message:', 'user-manager'); ?></strong> <code><?php echo esc_html($message); ?></code>
			</p>
			<p style="margin:0 0 8px;font-size:12px;">
				<strong><?php esc_html_e('File:', 'user-manager'); ?></strong> <code><?php echo esc_html($file); ?></code>
			</p>
			<p style="margin:0 0 8px;font-size:12px;">
				<strong><?php esc_html_e('Line:', 'user-manager'); ?></strong> <?php echo esc_html((string) $line); ?>
			</p>
			<?php if ($url !== '') : ?>
				<p style="margin:0;font-size:12px;">
					<strong><?php esc_html_e('URL:', 'user-manager'); ?></strong> <code><?php echo esc_html($url); ?></code>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Check whether an error type is fatal-level.
	 */
	private static function is_fatal_error_debugger_type(int $type): bool {
		return in_array(
			$type,
			[
				E_ERROR,
				E_PARSE,
				E_CORE_ERROR,
				E_COMPILE_ERROR,
				E_USER_ERROR,
				E_RECOVERABLE_ERROR,
			],
			true
		);
	}
}

