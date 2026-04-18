<?php
/**
 * User Manager SMS helper/service.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_SMS {
	/**
	 * Normalize a phone number into a compact E.164-like format.
	 *
	 * @param string $phone Raw phone number input.
	 * @return string Normalized phone number (e.g. +15551234567) or empty string.
	 */
	public static function normalize_phone_number(string $phone): string {
		$phone = trim($phone);
		if ($phone === '') {
			return '';
		}

		$has_plus = strpos($phone, '+') === 0;
		$digits = preg_replace('/\D+/', '', $phone);
		$digits = is_string($digits) ? $digits : '';

		if ($digits === '') {
			return '';
		}

		// Common US normalization when users enter 10 or 11 digit local numbers.
		if (!$has_plus && strlen($digits) === 10) {
			return '+1' . $digits;
		}
		if (!$has_plus && strlen($digits) === 11 && strpos($digits, '1') === 0) {
			return '+' . $digits;
		}

		return '+' . $digits;
	}

	/**
	 * Validate a phone number for SMS send usage.
	 */
	public static function is_valid_phone_number(string $phone): bool {
		$normalized = self::normalize_phone_number($phone);
		return (bool) preg_match('/^\+\d{10,15}$/', $normalized);
	}

	/**
	 * Parse a textarea block (one phone number per line) into normalized values.
	 *
	 * @param string $phone_numbers_raw Raw line-delimited phone numbers.
	 * @return array<int,string>
	 */
	public static function parse_phone_numbers(string $phone_numbers_raw): array {
		$phone_numbers_raw = str_replace(["\r\n", "\r"], "\n", $phone_numbers_raw);
		$rows = explode("\n", $phone_numbers_raw);
		$phones = [];

		foreach ($rows as $row) {
			$phone = self::normalize_phone_number($row);
			if ($phone === '' || !self::is_valid_phone_number($phone)) {
				continue;
			}
			$phones[] = $phone;
		}

		return array_values(array_unique($phones));
	}

	/**
	 * Get all known phone numbers for a user from common meta keys.
	 *
	 * @param int $user_id User ID.
	 * @return array<int,string>
	 */
	public static function get_user_phone_numbers(int $user_id): array {
		if ($user_id <= 0) {
			return [];
		}

		$meta_keys = [
			'billing_phone',
			'phone',
			'shipping_phone',
			'telephone',
			'mobile',
			'mobile_phone',
			'cell_phone',
		];

		$phones = [];
		foreach ($meta_keys as $meta_key) {
			$value = get_user_meta($user_id, $meta_key, true);
			if (is_array($value)) {
				foreach ($value as $sub_value) {
					$normalized = self::normalize_phone_number((string) $sub_value);
					if ($normalized !== '' && self::is_valid_phone_number($normalized)) {
						$phones[] = $normalized;
					}
				}
				continue;
			}

			$normalized = self::normalize_phone_number((string) $value);
			if ($normalized !== '' && self::is_valid_phone_number($normalized)) {
				$phones[] = $normalized;
			}
		}

		return array_values(array_unique($phones));
	}

	/**
	 * Get the preferred user phone number (first valid value from known keys).
	 */
	public static function get_user_primary_phone(int $user_id): string {
		$phones = self::get_user_phone_numbers($user_id);
		return isset($phones[0]) ? (string) $phones[0] : '';
	}

	/**
	 * Build placeholder replacements for SMS template text.
	 *
	 * @param string $phone       Destination phone number.
	 * @param string $login_url   Login URL placeholder value.
	 * @param string $coupon_code Coupon code placeholder value.
	 * @param array  $context     Optional user context.
	 * @return array<string,string>
	 */
	private static function get_replacements(string $phone, string $login_url, string $coupon_code, array $context = []): array {
		$email = isset($context['email']) ? sanitize_email((string) $context['email']) : '';
		$username = isset($context['username']) ? sanitize_text_field((string) $context['username']) : '';
		$first_name = isset($context['first_name']) ? sanitize_text_field((string) $context['first_name']) : '';
		$last_name = isset($context['last_name']) ? sanitize_text_field((string) $context['last_name']) : '';

		if ($username === '' && $email !== '') {
			$username = strstr($email, '@', true) ?: $email;
		}
		if ($username === '') {
			$username = ltrim($phone, '+');
		}

		return [
			'%SITEURL%'          => home_url(),
			'%LOGINURL%'         => $login_url,
			'%USERNAME%'         => $username,
			'%PASSWORD%'         => '••••••••',
			'%EMAIL%'            => $email,
			'%FIRSTNAME%'        => $first_name,
			'%LASTNAME%'         => $last_name,
			'%PASSWORDRESETURL%' => home_url('/my-account/lost-password/'),
			'%COUPONCODE%'       => $coupon_code,
			'%PHONENUMBER%'      => $phone,
		];
	}

	/**
	 * Build SMS message text from a template and context.
	 */
	public static function build_sms_message(string $template_id, string $phone, string $login_url, string $coupon_code = '', array $context = []): string {
		$templates = User_Manager_Core::get_sms_text_templates();
		$template = $templates[$template_id] ?? null;
		if (!is_array($template) || empty($template['body'])) {
			$template = [
				'body' => 'Hi %FIRSTNAME%, login here: %SITEURL%%LOGINURL% (%USERNAME%).',
			];
		}

		$message = (string) ($template['body'] ?? '');
		$message = str_replace(
			array_keys(self::get_replacements($phone, $login_url, $coupon_code, $context)),
			array_values(self::get_replacements($phone, $login_url, $coupon_code, $context)),
			$message
		);

		// SMS should be plain text.
		$message = wp_strip_all_tags($message);
		$message = trim(preg_replace('/\s+/', ' ', $message) ?? '');
		return $message;
	}

	/**
	 * Send SMS using SimpleTexting API token.
	 *
	 * This method tries a few payload variants/endpoints to maximize compatibility
	 * with different SimpleTexting API revisions.
	 */
	public static function send_sms_to_phone(string $phone, string $template_id, string $login_url, string $coupon_code = '', array $context = []): bool {
		$phone = self::normalize_phone_number($phone);
		if (!self::is_valid_phone_number($phone)) {
			return false;
		}

		$settings = User_Manager_Core::get_settings();
		$api_token = isset($settings['simple_texting_api_token']) ? trim((string) $settings['simple_texting_api_token']) : '';
		if ($api_token === '') {
			return false;
		}

		$message = self::build_sms_message($template_id, $phone, $login_url, $coupon_code, $context);
		if ($message === '') {
			return false;
		}

		$endpoints = apply_filters(
			'user_manager_simple_texting_endpoints',
			[
				'https://api.simpletexting.com/v2/send-sms',
				'https://app2.simpletexting.com/v1/send',
			]
		);
		if (!is_array($endpoints)) {
			return false;
		}

		$attempts = [
			['format' => 'json', 'body' => ['to' => $phone, 'text' => $message]],
			['format' => 'json', 'body' => ['to' => $phone, 'message' => $message]],
			['format' => 'form', 'body' => ['to' => $phone, 'text' => $message]],
			['format' => 'form', 'body' => ['phone' => $phone, 'message' => $message]],
		];

		foreach ($endpoints as $endpoint_raw) {
			$endpoint = esc_url_raw((string) $endpoint_raw);
			if ($endpoint === '') {
				continue;
			}

			foreach ($attempts as $attempt) {
				$headers = [
					'Authorization' => 'Bearer ' . $api_token,
					'Accept'        => 'application/json',
				];
				$args = [
					'timeout' => 20,
					'headers' => $headers,
				];

				if (($attempt['format'] ?? '') === 'json') {
					$args['headers']['Content-Type'] = 'application/json';
					$args['body'] = wp_json_encode($attempt['body']);
				} else {
					$args['body'] = $attempt['body'];
				}

				$response = wp_remote_post($endpoint, $args);
				if (is_wp_error($response)) {
					continue;
				}

				$code = (int) wp_remote_retrieve_response_code($response);
				if ($code >= 200 && $code < 300) {
					return true;
				}
			}
		}

		return false;
	}
}

