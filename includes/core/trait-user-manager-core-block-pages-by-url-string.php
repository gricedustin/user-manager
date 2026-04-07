<?php
/**
 * Block Pages by URL String helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Block_Pages_By_URL_String_Trait {

	/**
	 * Boot URL-string page blocking when enabled.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_block_pages_by_url_string(array $settings): void {
		if (empty($settings['block_pages_by_url_string_enabled'])) {
			return;
		}
		add_action('template_redirect', [__CLASS__, 'maybe_block_pages_by_url_string_request'], 0);
	}

	/**
	 * Match current URL against configured rules and block/redirect when matched.
	 */
	public static function maybe_block_pages_by_url_string_request(): void {
		if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
			return;
		}
		if (defined('REST_REQUEST') && REST_REQUEST) {
			return;
		}

		$settings = self::get_settings();
		if (empty($settings['block_pages_by_url_string_enabled'])) {
			return;
		}

		$match_rules = self::block_pages_by_url_string_parse_lines(
			isset($settings['block_pages_by_url_string_match_urls']) ? (string) $settings['block_pages_by_url_string_match_urls'] : ''
		);
		if (empty($match_rules)) {
			return;
		}

		$current_request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
		$current_full_url = home_url($current_request_uri);
		if (!self::block_pages_by_url_string_request_matches_rules($current_request_uri, $current_full_url, $match_rules)) {
			return;
		}

		$exception_rules = self::block_pages_by_url_string_parse_lines(
			isset($settings['block_pages_by_url_string_exception_urls']) ? (string) $settings['block_pages_by_url_string_exception_urls'] : ''
		);
		if (!empty($exception_rules) && self::block_pages_by_url_string_request_matches_rules($current_request_uri, $current_full_url, $exception_rules)) {
			return;
		}

		$redirect_url = isset($settings['block_pages_by_url_string_redirect_url']) ? (string) $settings['block_pages_by_url_string_redirect_url'] : '';
		$redirect_url = trim($redirect_url);
		if ($redirect_url !== '' && wp_http_validate_url($redirect_url) && !self::block_pages_by_url_string_current_request_matches_url($redirect_url)) {
			wp_safe_redirect($redirect_url);
			exit;
		}

		self::render_block_pages_by_url_string_overlay_and_exit($settings);
	}

	/**
	 * @return array<int,string>
	 */
	private static function block_pages_by_url_string_parse_lines(string $raw_text): array {
		$lines = preg_split('/\r\n|\r|\n/', $raw_text);
		if (!is_array($lines)) {
			return [];
		}
		$clean = [];
		foreach ($lines as $line) {
			$line = trim((string) $line);
			if ($line === '') {
				continue;
			}
			$clean[] = $line;
		}
		return array_values(array_unique($clean));
	}

	/**
	 * @param array<int,string> $rules
	 */
	private static function block_pages_by_url_string_request_matches_rules(string $request_uri, string $full_url, array $rules): bool {
		$request_uri_lc = strtolower($request_uri);
		$full_url_lc = strtolower($full_url);
		foreach ($rules as $rule) {
			$rule = trim((string) $rule);
			if ($rule === '') {
				continue;
			}
			if ($rule === '/') {
				return true;
			}
			$rule_lc = strtolower($rule);
			if (strpos($request_uri_lc, $rule_lc) !== false || strpos($full_url_lc, $rule_lc) !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Compare current request path/query to target URL to prevent redirect loops.
	 */
	private static function block_pages_by_url_string_current_request_matches_url(string $target_url): bool {
		$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
		$current_url = home_url($request_uri);
		$current = wp_parse_url($current_url);
		$target = wp_parse_url($target_url);
		if (!is_array($current) || !is_array($target)) {
			return false;
		}
		$current_path = isset($current['path']) ? (string) $current['path'] : '';
		$target_path = isset($target['path']) ? (string) $target['path'] : '';
		if ($current_path !== $target_path) {
			return false;
		}
		$current_query = isset($current['query']) ? (string) $current['query'] : '';
		$target_query = isset($target['query']) ? (string) $target['query'] : '';
		return $current_query === $target_query;
	}

	/**
	 * @param array<string,mixed> $settings
	 */
	private static function render_block_pages_by_url_string_overlay_and_exit(array $settings): void {
		nocache_headers();
		status_header(403);

		$background_color = self::block_pages_by_url_string_safe_css_value(
			isset($settings['block_pages_by_url_string_background_color']) ? (string) $settings['block_pages_by_url_string_background_color'] : '#000000',
			'#000000'
		);
		$background_url = isset($settings['block_pages_by_url_string_background_url']) ? (string) $settings['block_pages_by_url_string_background_url'] : '';
		$logo_url = isset($settings['block_pages_by_url_string_logo_url']) ? (string) $settings['block_pages_by_url_string_logo_url'] : '';
		$logo_width = self::block_pages_by_url_string_safe_css_value(
			isset($settings['block_pages_by_url_string_logo_width']) ? (string) $settings['block_pages_by_url_string_logo_width'] : '',
			''
		);
		$text_color = self::block_pages_by_url_string_safe_css_value(
			isset($settings['block_pages_by_url_string_text_color']) ? (string) $settings['block_pages_by_url_string_text_color'] : '',
			'#ffffff'
		);
		$message = isset($settings['block_pages_by_url_string_message']) ? trim((string) $settings['block_pages_by_url_string_message']) : '';

		$body_style = 'margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;box-sizing:border-box;text-align:center;background-color:' . $background_color . ';color:' . $text_color . ';';
		if ($background_url !== '') {
			$body_style .= 'background-image:url(' . esc_url($background_url) . ');background-size:cover;background-position:center center;background-repeat:no-repeat;';
		}

		$logo_style = 'display:block;margin:0 auto 18px;max-width:min(92vw, 100%);height:auto;';
		if ($logo_width !== '') {
			$logo_style .= 'width:' . $logo_width . ';';
		}

		?>
		<!doctype html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo('charset'); ?>" />
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			<title><?php echo esc_html(get_bloginfo('name')); ?></title>
		</head>
		<body style="<?php echo esc_attr($body_style); ?>">
			<div style="width:100%;max-width:920px;">
				<?php if ($logo_url !== '') : ?>
					<img src="<?php echo esc_url($logo_url); ?>" alt="" style="<?php echo esc_attr($logo_style); ?>" />
				<?php endif; ?>
				<?php if ($message !== '') : ?>
					<div style="font-size:clamp(18px,3.2vw,34px);font-weight:700;line-height:1.25;"><?php echo esc_html($message); ?></div>
				<?php endif; ?>
			</div>
		</body>
		</html>
		<?php
		exit;
	}

	/**
	 * Keep style values to a safe subset for inline CSS output.
	 */
	private static function block_pages_by_url_string_safe_css_value(string $raw_value, string $fallback = ''): string {
		$value = trim((string) $raw_value);
		if ($value === '') {
			return $fallback;
		}
		if (!preg_match('/^[#a-zA-Z0-9\(\),.%\s\-]+$/', $value)) {
			return $fallback;
		}
		return $value;
	}
}

