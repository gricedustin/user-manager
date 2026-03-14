<?php
/**
 * Front-end URL parameter debugger helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Frontend_URL_Parameter_Debugger_Trait {

	/**
	 * Render a front-end debug panel when URL trigger is present.
	 */
	public static function maybe_render_frontend_url_parameter_debugger(): void {
		if (is_admin() || wp_doing_ajax()) {
			return;
		}
		if (!is_user_logged_in() || !current_user_can('manage_options')) {
			return;
		}

		$settings = self::get_settings();
		if (empty($settings['frontend_url_param_debugger_enabled'])) {
			return;
		}

		$param_name = self::get_frontend_url_parameter_debugger_param_name($settings);
		if (!isset($_GET[$param_name])) {
			return;
		}

		$trigger_value = isset($_GET[$param_name]) ? wp_unslash($_GET[$param_name]) : '';
		$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
		$current_url = esc_url(home_url($request_uri));

		$query_params = [];
		if (!empty($_GET) && is_array($_GET)) {
			foreach ($_GET as $raw_key => $raw_value) {
				$key = sanitize_text_field((string) $raw_key);
				if ($key === '') {
					continue;
				}
				$query_params[$key] = self::normalize_frontend_url_parameter_debug_value(wp_unslash($raw_value));
			}
		}
		ksort($query_params, SORT_NATURAL | SORT_FLAG_CASE);
		?>
		<div class="um-frontend-url-param-debugger" style="position:fixed;right:20px;bottom:20px;z-index:99999;max-width:720px;width:calc(100vw - 40px);max-height:75vh;overflow:auto;background:#fff;border:2px solid #2271b1;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,.25);padding:14px;">
			<h3 style="margin:0 0 8px;"><?php esc_html_e('Front-End URL Parameter Debugger', 'user-manager'); ?></h3>
			<p style="margin:0 0 8px;font-size:12px;">
				<strong><?php esc_html_e('Current URL:', 'user-manager'); ?></strong>
				<?php echo esc_html($current_url); ?>
			</p>
			<p style="margin:0 0 12px;font-size:12px;">
				<strong><?php esc_html_e('Trigger Parameter:', 'user-manager'); ?></strong>
				<?php echo esc_html($param_name); ?> = <?php echo esc_html(self::stringify_frontend_url_parameter_debug_value($trigger_value)); ?>
			</p>
			<table class="widefat striped" style="margin:0;">
				<thead>
					<tr>
						<th style="width:35%;"><?php esc_html_e('Parameter', 'user-manager'); ?></th>
						<th><?php esc_html_e('Value', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($query_params)) : ?>
						<?php foreach ($query_params as $key => $value) : ?>
							<tr>
								<td><code><?php echo esc_html($key); ?></code></td>
								<td><code><?php echo esc_html(self::stringify_frontend_url_parameter_debug_value($value)); ?></code></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="2"><?php esc_html_e('No URL query parameters found.', 'user-manager'); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Resolve the configured URL trigger parameter name.
	 *
	 * @param array<string,mixed> $settings
	 */
	private static function get_frontend_url_parameter_debugger_param_name(array $settings): string {
		$param_name = isset($settings['frontend_url_param_debugger_param'])
			? sanitize_key((string) $settings['frontend_url_param_debugger_param'])
			: 'um_url_debug';

		return $param_name !== '' ? $param_name : 'um_url_debug';
	}

	/**
	 * Normalize debug values (including nested arrays) for safe rendering.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	private static function normalize_frontend_url_parameter_debug_value($value) {
		if (is_array($value)) {
			$normalized = [];
			foreach ($value as $k => $v) {
				$key = is_string($k) ? sanitize_text_field($k) : (int) $k;
				$normalized[$key] = self::normalize_frontend_url_parameter_debug_value($v);
			}
			return $normalized;
		}
		if (is_object($value)) {
			return self::normalize_frontend_url_parameter_debug_value((array) $value);
		}
		return sanitize_text_field((string) $value);
	}

	/**
	 * Convert any debug value to a readable single-line string.
	 *
	 * @param mixed $value
	 */
	private static function stringify_frontend_url_parameter_debug_value($value): string {
		if (is_array($value) || is_object($value)) {
			$json = wp_json_encode($value);
			return is_string($json) ? $json : '';
		}
		return sanitize_text_field((string) $value);
	}
}

