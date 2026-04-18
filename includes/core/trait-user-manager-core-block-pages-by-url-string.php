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

		$rulesets = self::get_block_pages_by_url_string_rulesets($settings);
		if (empty($rulesets)) {
			return;
		}

		$current_request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
		$current_full_url = home_url($current_request_uri);
		foreach ($rulesets as $ruleset) {
			$match_rules = isset($ruleset['match_rules']) && is_array($ruleset['match_rules']) ? $ruleset['match_rules'] : [];
			if (empty($match_rules)) {
				continue;
			}
			if (!self::block_pages_by_url_string_request_matches_rules($current_request_uri, $current_full_url, $match_rules)) {
				continue;
			}

			$exception_rules = isset($ruleset['exception_rules']) && is_array($ruleset['exception_rules']) ? $ruleset['exception_rules'] : [];
			if (!empty($exception_rules) && self::block_pages_by_url_string_request_matches_rules($current_request_uri, $current_full_url, $exception_rules)) {
				continue;
			}

			$redirect_url = isset($ruleset['redirect_url']) ? trim((string) $ruleset['redirect_url']) : '';
			if ($redirect_url !== '' && wp_http_validate_url($redirect_url) && !self::block_pages_by_url_string_current_request_matches_url($redirect_url)) {
				wp_safe_redirect($redirect_url);
				exit;
			}

			self::render_block_pages_by_url_string_overlay_and_exit($ruleset);
		}
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

		$background_color_raw = isset($settings['background_color'])
			? (string) $settings['background_color']
			: (isset($settings['block_pages_by_url_string_background_color']) ? (string) $settings['block_pages_by_url_string_background_color'] : '#000000');
		$background_url = isset($settings['background_url'])
			? (string) $settings['background_url']
			: (isset($settings['block_pages_by_url_string_background_url']) ? (string) $settings['block_pages_by_url_string_background_url'] : '');
		$logo_url = isset($settings['logo_url'])
			? (string) $settings['logo_url']
			: (isset($settings['block_pages_by_url_string_logo_url']) ? (string) $settings['block_pages_by_url_string_logo_url'] : '');
		$logo_width_raw = isset($settings['logo_width'])
			? (string) $settings['logo_width']
			: (isset($settings['block_pages_by_url_string_logo_width']) ? (string) $settings['block_pages_by_url_string_logo_width'] : '');
		$text_color_raw = isset($settings['text_color'])
			? (string) $settings['text_color']
			: (isset($settings['block_pages_by_url_string_text_color']) ? (string) $settings['block_pages_by_url_string_text_color'] : '');
		$message = isset($settings['message'])
			? trim((string) $settings['message'])
			: (isset($settings['block_pages_by_url_string_message']) ? trim((string) $settings['block_pages_by_url_string_message']) : '');

		$background_color = self::block_pages_by_url_string_safe_css_value(
			$background_color_raw,
			'#000000'
		);
		$logo_width = self::block_pages_by_url_string_safe_css_value(
			$logo_width_raw,
			''
		);
		$text_color = self::block_pages_by_url_string_safe_css_value(
			$text_color_raw,
			'#ffffff'
		);

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

	/**
	 * Build normalized, access-scoped rule sets.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<int,array<string,mixed>>
	 */
	private static function get_block_pages_by_url_string_rulesets(array $settings): array {
		$raw_sets = isset($settings['block_pages_by_url_string_rules']) && is_array($settings['block_pages_by_url_string_rules'])
			? $settings['block_pages_by_url_string_rules']
			: [];

		$sets = [];
		foreach ($raw_sets as $raw_set) {
			if (!is_array($raw_set)) {
				continue;
			}
			$match_rules = self::block_pages_by_url_string_parse_lines(isset($raw_set['match_urls']) ? (string) $raw_set['match_urls'] : '');
			if (empty($match_rules)) {
				continue;
			}
			$sets[] = [
				'match_rules' => $match_rules,
				'exception_rules' => self::block_pages_by_url_string_parse_lines(isset($raw_set['exception_urls']) ? (string) $raw_set['exception_urls'] : ''),
				'usernames' => self::block_pages_by_url_string_parse_usernames(isset($raw_set['usernames']) ? (string) $raw_set['usernames'] : ''),
				'roles' => self::block_pages_by_url_string_parse_roles(isset($raw_set['roles']) ? $raw_set['roles'] : []),
				'background_color' => isset($raw_set['background_color']) ? (string) $raw_set['background_color'] : '#000000',
				'background_url' => isset($raw_set['background_url']) ? (string) $raw_set['background_url'] : '',
				'logo_url' => isset($raw_set['logo_url']) ? (string) $raw_set['logo_url'] : '',
				'logo_width' => isset($raw_set['logo_width']) ? (string) $raw_set['logo_width'] : '',
				'message' => isset($raw_set['message']) ? (string) $raw_set['message'] : '',
				'text_color' => isset($raw_set['text_color']) ? (string) $raw_set['text_color'] : '',
				'redirect_url' => isset($raw_set['redirect_url']) ? (string) $raw_set['redirect_url'] : '',
			];
		}

		// Backward compatibility: if no rule sets saved, use legacy single-rule fields.
		if (empty($sets)) {
			$legacy_match_rules = self::block_pages_by_url_string_parse_lines(
				isset($settings['block_pages_by_url_string_match_urls']) ? (string) $settings['block_pages_by_url_string_match_urls'] : ''
			);
			if (!empty($legacy_match_rules)) {
				$sets[] = [
					'match_rules' => $legacy_match_rules,
					'exception_rules' => self::block_pages_by_url_string_parse_lines(
						isset($settings['block_pages_by_url_string_exception_urls']) ? (string) $settings['block_pages_by_url_string_exception_urls'] : ''
					),
					'usernames' => [],
					'roles' => [],
					'background_color' => isset($settings['block_pages_by_url_string_background_color']) ? (string) $settings['block_pages_by_url_string_background_color'] : '#000000',
					'background_url' => isset($settings['block_pages_by_url_string_background_url']) ? (string) $settings['block_pages_by_url_string_background_url'] : '',
					'logo_url' => isset($settings['block_pages_by_url_string_logo_url']) ? (string) $settings['block_pages_by_url_string_logo_url'] : '',
					'logo_width' => isset($settings['block_pages_by_url_string_logo_width']) ? (string) $settings['block_pages_by_url_string_logo_width'] : '',
					'message' => isset($settings['block_pages_by_url_string_message']) ? (string) $settings['block_pages_by_url_string_message'] : '',
					'text_color' => isset($settings['block_pages_by_url_string_text_color']) ? (string) $settings['block_pages_by_url_string_text_color'] : '',
					'redirect_url' => isset($settings['block_pages_by_url_string_redirect_url']) ? (string) $settings['block_pages_by_url_string_redirect_url'] : '',
				];
			}
		}

		$scoped_sets = [];
		foreach ($sets as $set) {
			if (self::block_pages_by_url_string_current_user_matches_scope(
				isset($set['usernames']) ? $set['usernames'] : [],
				isset($set['roles']) ? $set['roles'] : []
			)) {
				$scoped_sets[] = $set;
			}
		}

		return $scoped_sets;
	}

	/**
	 * @return array<int,string>
	 */
	private static function block_pages_by_url_string_parse_usernames(string $raw): array {
		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}
		$parts = preg_split('/[\s,]+/', $raw);
		if (!is_array($parts)) {
			return [];
		}
		$usernames = [];
		foreach ($parts as $part) {
			$sanitized = sanitize_user((string) $part, false);
			if ($sanitized === '') {
				continue;
			}
			$usernames[] = strtolower($sanitized);
		}
		return array_values(array_unique($usernames));
	}

	/**
	 * @param mixed $raw
	 * @return array<int,string>
	 */
	private static function block_pages_by_url_string_parse_roles($raw): array {
		$parts = is_array($raw) ? $raw : [];
		$roles = [];
		foreach ($parts as $part) {
			$role = sanitize_key((string) $part);
			if ($role === '') {
				continue;
			}
			$roles[] = $role;
		}
		$roles = array_values(array_unique($roles));
		if (function_exists('wp_roles')) {
			$wp_roles = wp_roles();
			if ($wp_roles && isset($wp_roles->roles) && is_array($wp_roles->roles)) {
				$roles = array_values(array_intersect($roles, array_keys($wp_roles->roles)));
			}
		}
		return $roles;
	}

	/**
	 * @param mixed $usernames
	 * @param mixed $roles
	 */
	private static function block_pages_by_url_string_current_user_matches_scope($usernames, $roles): bool {
		$usernames = is_array($usernames) ? $usernames : [];
		$roles = is_array($roles) ? $roles : [];
		if (empty($usernames) && empty($roles)) {
			return true;
		}
		if (!is_user_logged_in()) {
			return false;
		}
		$user = wp_get_current_user();
		$login = strtolower((string) ($user->user_login ?? ''));
		if ($login !== '' && in_array($login, $usernames, true)) {
			return true;
		}
		if (empty($roles)) {
			return false;
		}
		$current_roles = is_array($user->roles) ? array_map('sanitize_key', $user->roles) : [];
		return !empty(array_intersect($roles, $current_roles));
	}
}

