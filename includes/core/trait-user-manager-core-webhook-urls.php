<?php
/**
 * Webhook URLs helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Webhook_URLs_Trait {

	private static bool $webhook_urls_debug_mode = false;

	/**
	 * @var array<int,array<string,mixed>>
	 */
	private static array $webhook_urls_debug_log = [];

	private static bool $webhook_urls_shutdown_registered = false;

	/**
	 * Register router + fatal handler for webhook URLs.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_webhook_urls(array $settings): void {
		if (empty($settings['webhook_urls_enabled'])) {
			return;
		}

		add_action('template_redirect', [__CLASS__, 'maybe_handle_webhook_urls_request'], 1);
		if (!self::$webhook_urls_shutdown_registered) {
			register_shutdown_function([__CLASS__, 'webhook_urls_shutdown_handler']);
			self::$webhook_urls_shutdown_registered = true;
		}
	}

	/**
	 * Front-end webhook router.
	 */
	public static function maybe_handle_webhook_urls_request(): void {
		if (is_admin()) {
			return;
		}

		$settings = self::get_settings();
		if (empty($settings['webhook_urls_enabled'])) {
			return;
		}

		self::$webhook_urls_debug_mode = !empty($settings['webhook_urls_debug_mode']);
		self::$webhook_urls_debug_log = [];
		$allow_get = !empty($settings['webhook_urls_allow_url_params']);

		self::webhook_urls_debug('Router started', [
			'request_method' => isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : '',
			'allow_url_params' => $allow_get,
		]);

		$webhook = '';
		if ($allow_get && isset($_GET['webhook'])) {
			$webhook = sanitize_key((string) wp_unslash($_GET['webhook']));
		}
		if ($webhook === '' && isset($_POST['webhook'])) {
			$webhook = sanitize_key((string) wp_unslash($_POST['webhook']));
		}
		if ($webhook === '') {
			return;
		}

		self::webhook_urls_debug('Webhook identified', ['webhook' => $webhook]);

		$type_order = !empty($settings['webhook_urls_activate_order_webhook']);
		$type_user = !empty($settings['webhook_urls_activate_user_webhook']);
		$type_post = !empty($settings['webhook_urls_activate_post_webhook']);
		$type_coupon = !empty($settings['webhook_urls_activate_coupon_webhook']);
		$type_product = !empty($settings['webhook_urls_activate_product_webhook']);
		$type_category = !empty($settings['webhook_urls_activate_product_cat_webhook']);
		$type_reset_password = !empty($settings['webhook_urls_activate_user_password_reset_webhook']);
		$type_send_email = !empty($settings['webhook_urls_activate_send_email_webhook']);

		try {
			switch ($webhook) {
				case 'create_order':
					if (!$type_order) {
						self::webhook_urls_json(['ok' => false, 'error' => 'Order webhook not active'], 403);
					}
					if (!$allow_get && !isset($_POST['webhook'])) {
						self::webhook_urls_json(['ok' => false, 'error' => 'URL parameters are disabled. Use POST or enable Allow URL Parameters.'], 403);
					}
					self::webhook_urls_handle_create_order($allow_get);
					break;
				case 'edit_order':
					if (!$type_order) {
						self::webhook_urls_json(['ok' => false, 'error' => 'Order webhook not active'], 403);
					}
					self::webhook_urls_handle_edit_order($allow_get);
					break;
				case 'create_coupon':
					if (!$type_coupon) {
						self::webhook_urls_json(['ok' => false, 'error' => 'Coupon webhook not active'], 403);
					}
					self::webhook_urls_handle_create_coupon($allow_get);
					break;
				case 'edit_coupon':
					if (!$type_coupon) {
						self::webhook_urls_json(['ok' => false, 'error' => 'Coupon webhook not active'], 403);
					}
					self::webhook_urls_handle_edit_coupon($allow_get);
					break;
				case 'reset_password':
					if (!$type_reset_password) {
						self::webhook_urls_json(['ok' => false, 'error' => 'Reset password webhook not active'], 403);
					}
					self::webhook_urls_handle_reset_password($allow_get);
					break;
				case 'send_email':
					if (!$type_send_email) {
						self::webhook_urls_json(['ok' => false, 'error' => 'Send email webhook not active'], 403);
					}
					self::webhook_urls_handle_send_email($allow_get);
					break;
				case 'create_user':
				case 'edit_user':
					if (!$type_user) {
						self::webhook_urls_json(['ok' => false, 'error' => 'User webhook not active'], 403);
					}
					self::webhook_urls_json(['ok' => true, 'message' => 'User create/edit placeholder. Extend this handler as needed.']);
					break;
				case 'create_post':
				case 'edit_post':
					if (!$type_post) {
						self::webhook_urls_json(['ok' => false, 'error' => 'Post webhook not active'], 403);
					}
					self::webhook_urls_json(['ok' => true, 'message' => 'Post create/edit placeholder. Extend this handler as needed.']);
					break;
				case 'create_product':
				case 'edit_product':
					if (!$type_product) {
						self::webhook_urls_json(['ok' => false, 'error' => 'Product webhook not active'], 403);
					}
					self::webhook_urls_json(['ok' => true, 'message' => 'Product create/edit placeholder. Extend this handler as needed.']);
					break;
				case 'create_product_cat':
				case 'edit_product_cat':
					if (!$type_category) {
						self::webhook_urls_json(['ok' => false, 'error' => 'Product category webhook not active'], 403);
					}
					self::webhook_urls_json(['ok' => true, 'message' => 'Product category create/edit placeholder. Extend this handler as needed.']);
					break;
				default:
					self::webhook_urls_json(['ok' => false, 'error' => 'Unknown webhook'], 400);
			}
		} catch (Throwable $e) {
			self::webhook_urls_debug('Unhandled router exception', [
				'message' => $e->getMessage(),
				'file' => basename($e->getFile()),
				'line' => $e->getLine(),
			]);
			self::webhook_urls_json([
				'ok' => false,
				'error' => 'Server error: ' . $e->getMessage(),
			], 500);
		}
	}

	/**
	 * Shutdown catcher for fatal webhook errors.
	 */
	public static function webhook_urls_shutdown_handler(): void {
		if (is_admin()) {
			return;
		}

		if (!isset($_GET['webhook']) && !isset($_POST['webhook'])) {
			return;
		}

		$error = error_get_last();
		if (!$error || !isset($error['type']) || !in_array((int) $error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR], true)) {
			return;
		}

		if (!self::$webhook_urls_debug_mode) {
			$settings = self::get_settings();
			self::$webhook_urls_debug_mode = !empty($settings['webhook_urls_debug_mode']);
		}

		if (headers_sent()) {
			return;
		}

		nocache_headers();
		status_header(500);
		header('Content-Type: application/json; charset=utf-8');
		$payload = [
			'ok' => false,
			'error' => 'Fatal error: ' . (isset($error['message']) ? (string) $error['message'] : 'Unknown'),
			'file' => isset($error['file']) ? basename((string) $error['file']) : '',
			'line' => isset($error['line']) ? (int) $error['line'] : 0,
		];
		if (self::$webhook_urls_debug_mode) {
			$payload['debug'] = self::$webhook_urls_debug_log;
		}
		echo wp_json_encode($payload, JSON_PRETTY_PRINT);
	}

	/**
	 * Handle create_order webhook.
	 */
	private static function webhook_urls_handle_create_order(bool $allow_get): void {
		if (!function_exists('wc_create_order') || !function_exists('wc_get_product')) {
			self::webhook_urls_json(['ok' => false, 'error' => 'WooCommerce required'], 500);
		}

		$first_name = sanitize_text_field((string) self::webhook_urls_get_input('first_name', $allow_get));
		$last_name = sanitize_text_field((string) self::webhook_urls_get_input('last_name', $allow_get));
		$company = sanitize_text_field((string) self::webhook_urls_get_input('company', $allow_get));
		$phone = sanitize_text_field((string) self::webhook_urls_get_input('phone', $allow_get));
		$address = sanitize_text_field((string) self::webhook_urls_get_input('address', $allow_get));
		$address_2 = sanitize_text_field((string) self::webhook_urls_get_input('address_2', $allow_get));
		$city = sanitize_text_field((string) self::webhook_urls_get_input('city', $allow_get));
		$state = sanitize_text_field((string) self::webhook_urls_get_input('state', $allow_get));
		$postal_code = sanitize_text_field((string) self::webhook_urls_get_input('postal_code', $allow_get));
		$country = sanitize_text_field((string) self::webhook_urls_get_input('country', $allow_get));
		$email = sanitize_email((string) self::webhook_urls_get_input('email_address', $allow_get));
		$product_ids_raw = sanitize_text_field((string) self::webhook_urls_get_input('product_ids', $allow_get));
		$order_note = wp_kses_post((string) self::webhook_urls_get_input('order_note', $allow_get));
		$order_status = sanitize_text_field((string) self::webhook_urls_get_input('order_status', $allow_get));

		$missing = [];
		if ($first_name === '') {
			$missing[] = 'first_name';
		}
		if ($last_name === '') {
			$missing[] = 'last_name';
		}
		if ($email === '') {
			$missing[] = 'email_address';
		}
		if ($product_ids_raw === '') {
			$missing[] = 'product_ids';
		}
		if (!empty($missing)) {
			self::webhook_urls_json(['ok' => false, 'error' => 'Missing required fields: ' . implode(', ', $missing)], 400);
		}

		$product_ids = self::webhook_urls_parse_csv_absints($product_ids_raw);
		if (empty($product_ids)) {
			self::webhook_urls_json(['ok' => false, 'error' => 'No valid product IDs provided.'], 400);
		}

		try {
			$order = wc_create_order();
			if (is_wp_error($order) || !$order || !is_a($order, 'WC_Order')) {
				self::webhook_urls_json(['ok' => false, 'error' => 'Failed to create order object'], 500);
			}

			$billing = [
				'first_name' => $first_name,
				'last_name' => $last_name,
				'company' => $company,
				'email' => $email,
				'phone' => $phone,
				'address_1' => $address,
				'address_2' => $address_2,
				'city' => $city,
				'state' => $state,
				'postcode' => $postal_code,
				'country' => $country,
			];
			foreach ($billing as $field_key => $value) {
				$billing_setter = 'set_billing_' . $field_key;
				if (method_exists($order, $billing_setter)) {
					$order->{$billing_setter}($value);
				}
				$shipping_setter = 'set_shipping_' . $field_key;
				if (method_exists($order, $shipping_setter)) {
					$order->{$shipping_setter}($value);
				}
			}

			$added_count = 0;
			$invalid_products = [];
			foreach ($product_ids as $product_id) {
				$product = wc_get_product($product_id);
				if (!$product || !$product->exists()) {
					$invalid_products[] = $product_id;
					continue;
				}

				if (method_exists($product, 'is_type') && $product->is_type('variable')) {
					$children = method_exists($product, 'get_children') ? $product->get_children() : [];
					if (!empty($children)) {
						$variation = wc_get_product((int) $children[0]);
						if ($variation && $variation->exists()) {
							$order->add_product($variation, 1);
							$added_count++;
							continue;
						}
					}
					$invalid_products[] = $product_id;
					continue;
				}

				$order->add_product($product, 1);
				$added_count++;
			}

			if ($added_count === 0) {
				self::webhook_urls_json([
					'ok' => false,
					'error' => 'No valid products could be added to the order.',
					'invalid_products' => $invalid_products,
				], 400);
			}

			if ($order_note !== '' && method_exists($order, 'set_customer_note')) {
				$order->set_customer_note($order_note);
			}

			$status = self::webhook_urls_normalize_order_status($order_status);
			if ($status === '') {
				$status = 'pending';
			}

			$order->calculate_totals();
			$order->set_status($status);

			$webhook_note = sprintf(
				'Order created via webhook. Source: %1$s | IP: %2$s | User: %3$s',
				self::webhook_urls_detect_source_url(),
				self::webhook_urls_detect_ip(),
				self::webhook_urls_current_user_label()
			);
			$order->add_order_note($webhook_note, false);
			$order->save();

			self::webhook_urls_json([
				'ok' => true,
				'order_id' => $order->get_id(),
				'order_number' => $order->get_order_number(),
				'invalid_products' => $invalid_products,
			]);
		} catch (Throwable $e) {
			self::webhook_urls_json([
				'ok' => false,
				'error' => 'Order creation failed: ' . $e->getMessage(),
			], 500);
		}
	}

	/**
	 * Handle edit_order webhook.
	 */
	private static function webhook_urls_handle_edit_order(bool $allow_get): void {
		if (!function_exists('wc_get_order')) {
			self::webhook_urls_json(['ok' => false, 'error' => 'WooCommerce required'], 500);
		}

		$order_id = absint((string) self::webhook_urls_get_input('order_id', $allow_get));
		if ($order_id <= 0) {
			self::webhook_urls_json(['ok' => false, 'error' => 'order_id required'], 400);
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			self::webhook_urls_json(['ok' => false, 'error' => 'Order not found'], 404);
		}

		$field_map = [
			'billing_first_name' => 'first_name',
			'billing_last_name' => 'last_name',
			'billing_company' => 'company',
			'billing_phone' => 'phone',
			'billing_address_1' => 'address',
			'billing_address_2' => 'address_2',
			'billing_city' => 'city',
			'billing_state' => 'state',
			'billing_postcode' => 'postal_code',
			'billing_country' => 'country',
			'billing_email' => 'email_address',
		];
		foreach ($field_map as $setter_key => $input_key) {
			$raw_value = self::webhook_urls_get_input($input_key, $allow_get);
			if ($raw_value === null || $raw_value === '') {
				continue;
			}

			$value = $setter_key === 'billing_email'
				? sanitize_email($raw_value)
				: sanitize_text_field($raw_value);
			$setter = 'set_' . $setter_key;
			if (method_exists($order, $setter)) {
				$order->{$setter}($value);
			}

			if (strpos($setter_key, 'billing_') === 0 && in_array($setter_key, ['billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_country'], true)) {
				$shipping_setter = 'set_' . str_replace('billing_', 'shipping_', $setter_key);
				if (method_exists($order, $shipping_setter)) {
					$order->{$shipping_setter}($value);
				}
			}
		}

		$product_ids_raw = self::webhook_urls_get_input('product_ids', $allow_get);
		$added_products = [];
		if ($product_ids_raw !== null && $product_ids_raw !== '') {
			$product_ids = self::webhook_urls_parse_csv_absints($product_ids_raw);
			foreach ($product_ids as $product_id) {
				$product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;
				if ($product && $product->exists()) {
					$order->add_product($product, 1);
					$added_products[] = $product_id;
				}
			}
		}

		$order_note = self::webhook_urls_get_input('order_note', $allow_get);
		if ($order_note !== null && $order_note !== '') {
			$order->add_order_note(wp_kses_post($order_note), false);
		}

		$order_status = sanitize_text_field((string) self::webhook_urls_get_input('order_status', $allow_get));
		$status = self::webhook_urls_normalize_order_status($order_status);
		if ($status !== '') {
			$order->set_status($status);
		}

		$order->calculate_totals();
		$order->save();
		self::webhook_urls_json([
			'ok' => true,
			'order_id' => $order->get_id(),
			'order_number' => $order->get_order_number(),
			'added_products' => $added_products,
		]);
	}

	/**
	 * Handle create_coupon webhook.
	 */
	private static function webhook_urls_handle_create_coupon(bool $allow_get): void {
		if (!class_exists('WC_Coupon')) {
			self::webhook_urls_json(['ok' => false, 'error' => 'WooCommerce required'], 500);
		}

		$code = sanitize_text_field((string) self::webhook_urls_get_input('code', $allow_get));
		if ($code === '') {
			self::webhook_urls_json(['ok' => false, 'error' => 'code required'], 400);
		}

		$coupon = new WC_Coupon();
		$coupon->set_code($code);
		self::webhook_urls_apply_coupon_fields_from_input($coupon, $allow_get, false);
		$coupon->save();

		self::webhook_urls_json([
			'ok' => true,
			'coupon_id' => $coupon->get_id(),
			'code' => $coupon->get_code(),
		]);
	}

	/**
	 * Handle edit_coupon webhook.
	 */
	private static function webhook_urls_handle_edit_coupon(bool $allow_get): void {
		if (!class_exists('WC_Coupon')) {
			self::webhook_urls_json(['ok' => false, 'error' => 'WooCommerce required'], 500);
		}

		$coupon_id = absint((string) self::webhook_urls_get_input('coupon_id', $allow_get));
		$code = sanitize_text_field((string) self::webhook_urls_get_input('code', $allow_get));
		$coupon = null;
		if ($coupon_id > 0) {
			$coupon = new WC_Coupon($coupon_id);
		} elseif ($code !== '') {
			$coupon = new WC_Coupon($code);
		}
		if (!$coupon || !$coupon->get_id()) {
			self::webhook_urls_json(['ok' => false, 'error' => 'Coupon not found'], 404);
		}

		self::webhook_urls_apply_coupon_fields_from_input($coupon, $allow_get, true);
		$coupon->save();
		self::webhook_urls_json([
			'ok' => true,
			'coupon_id' => $coupon->get_id(),
			'code' => $coupon->get_code(),
		]);
	}

	/**
	 * Apply coupon fields from webhook input.
	 */
	private static function webhook_urls_apply_coupon_fields_from_input($coupon, bool $allow_get, bool $is_edit): void {
		$discount_type = self::webhook_urls_get_input('discount_type', $allow_get);
		if ($discount_type !== null && $discount_type !== '') {
			$discount_type = sanitize_key($discount_type);
			if (!in_array($discount_type, ['percent', 'fixed_cart', 'fixed_product'], true)) {
				$discount_type = 'fixed_cart';
			}
			$coupon->set_discount_type($discount_type);
		} elseif (!$is_edit) {
			$coupon->set_discount_type('fixed_cart');
		}

		$amount = self::webhook_urls_get_input('amount', $allow_get);
		if ($amount !== null && $amount !== '') {
			$coupon->set_amount((float) $amount);
		}

		$expiry = self::webhook_urls_get_input('expiry_date', $allow_get);
		if ($expiry !== null) {
			$expiry = sanitize_text_field($expiry);
			if ($expiry === '') {
				$coupon->set_date_expires(null);
			} else {
				$timestamp = strtotime($expiry);
				if ($timestamp !== false) {
					$coupon->set_date_expires($timestamp);
				}
			}
		}

		$individual_use = self::webhook_urls_get_input('individual_use', $allow_get);
		if ($individual_use !== null && $individual_use !== '') {
			$coupon->set_individual_use((int) $individual_use === 1);
		}

		$usage_limit = self::webhook_urls_get_input('usage_limit', $allow_get);
		if ($usage_limit !== null && $usage_limit !== '') {
			$coupon->set_usage_limit(absint($usage_limit));
		}

		$usage_limit_per_user = self::webhook_urls_get_input('usage_limit_per_user', $allow_get);
		if ($usage_limit_per_user !== null && $usage_limit_per_user !== '') {
			$coupon->set_usage_limit_per_user(absint($usage_limit_per_user));
		}

		$minimum_amount = self::webhook_urls_get_input('minimum_amount', $allow_get);
		if ($minimum_amount !== null) {
			$coupon->set_minimum_amount(sanitize_text_field($minimum_amount));
		}

		$maximum_amount = self::webhook_urls_get_input('maximum_amount', $allow_get);
		if ($maximum_amount !== null) {
			$coupon->set_maximum_amount(sanitize_text_field($maximum_amount));
		}

		$free_shipping = self::webhook_urls_get_input('free_shipping', $allow_get);
		if ($free_shipping !== null && $free_shipping !== '') {
			$coupon->set_free_shipping((int) $free_shipping === 1);
		}

		$email_restrictions = self::webhook_urls_get_input('email_restrictions', $allow_get);
		if ($email_restrictions !== null) {
			$coupon->set_email_restrictions(self::webhook_urls_parse_csv_emails((string) $email_restrictions));
		}

		$product_ids = self::webhook_urls_get_input('product_ids', $allow_get);
		if ($product_ids !== null) {
			$coupon->set_product_ids(self::webhook_urls_parse_csv_absints((string) $product_ids));
		}

		$exclude_product_ids = self::webhook_urls_get_input('exclude_product_ids', $allow_get);
		if ($exclude_product_ids !== null) {
			$coupon->set_excluded_product_ids(self::webhook_urls_parse_csv_absints((string) $exclude_product_ids));
		}

		$product_categories = self::webhook_urls_get_input('product_categories', $allow_get);
		if ($product_categories !== null) {
			$coupon->set_product_categories(self::webhook_urls_parse_csv_absints((string) $product_categories));
		}

		$exclude_product_categories = self::webhook_urls_get_input('exclude_product_categories', $allow_get);
		if ($exclude_product_categories !== null) {
			$coupon->set_excluded_product_categories(self::webhook_urls_parse_csv_absints((string) $exclude_product_categories));
		}

		$description = self::webhook_urls_get_input('description', $allow_get);
		if ($description !== null) {
			$coupon->set_description(sanitize_text_field($description));
		}
	}

	/**
	 * Handle reset_password webhook.
	 */
	private static function webhook_urls_handle_reset_password(bool $allow_get): void {
		$user_id = absint((string) self::webhook_urls_get_input('user_id', $allow_get));
		$email = sanitize_email((string) self::webhook_urls_get_input('email', $allow_get));
		$new_password = (string) self::webhook_urls_get_input('new_password', $allow_get);

		if ($new_password === '') {
			self::webhook_urls_json(['ok' => false, 'error' => 'new_password required'], 400);
		}
		if ($user_id <= 0 && $email === '') {
			self::webhook_urls_json(['ok' => false, 'error' => 'user_id or email required'], 400);
		}

		if ($user_id <= 0) {
			$user = get_user_by('email', $email);
			if (!$user) {
				self::webhook_urls_json(['ok' => false, 'error' => 'User not found'], 404);
			}
			$user_id = (int) $user->ID;
		}

		wp_set_password($new_password, $user_id);
		self::webhook_urls_json(['ok' => true, 'user_id' => $user_id]);
	}

	/**
	 * Handle send_email webhook.
	 */
	private static function webhook_urls_handle_send_email(bool $allow_get): void {
		$to_raw = self::webhook_urls_get_input('to', $allow_get);
		$subject = self::webhook_urls_get_input('subject', $allow_get);
		$message = self::webhook_urls_get_input('message', $allow_get);

		if ($to_raw === null || $to_raw === '' || $subject === null || $subject === '' || $message === null || $message === '') {
			self::webhook_urls_json(['ok' => false, 'error' => 'to, subject, message required'], 400);
		}

		$recipients = self::webhook_urls_parse_csv_emails($to_raw);
		if (empty($recipients)) {
			self::webhook_urls_json(['ok' => false, 'error' => 'No valid recipients found in "to"'], 400);
		}

		$from_email = sanitize_email((string) self::webhook_urls_get_input('from_email', $allow_get));
		$from_name = sanitize_text_field((string) self::webhook_urls_get_input('from_name', $allow_get));
		$bcc_emails = self::webhook_urls_parse_csv_emails((string) self::webhook_urls_get_input('bcc', $allow_get));
		$html = self::webhook_urls_get_input('html', $allow_get);
		$is_html = $html === null ? true : ((int) $html !== 0);

		$headers = [];
		if ($is_html) {
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
		}
		if ($from_email !== '') {
			$from_header = $from_name !== '' ? ($from_name . ' <' . $from_email . '>') : $from_email;
			$headers[] = 'From: ' . $from_header;
		}
		foreach ($bcc_emails as $bcc_email) {
			$headers[] = 'Bcc: ' . $bcc_email;
		}

		$failed = [];
		foreach ($recipients as $recipient) {
			if (!wp_mail($recipient, sanitize_text_field($subject), $message, $headers)) {
				$failed[] = $recipient;
			}
		}

		if (!empty($failed)) {
			self::webhook_urls_json([
				'ok' => false,
				'recipients' => $recipients,
				'failed_recipients' => $failed,
			], 500);
		}

		self::webhook_urls_json(['ok' => true, 'recipients' => $recipients], 200);
	}

	/**
	 * Normalize incoming order status values.
	 */
	private static function webhook_urls_normalize_order_status(string $status): string {
		$status = strtolower(trim($status));
		$status = str_replace('wc-', '', $status);
		$valid = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'];
		return in_array($status, $valid, true) ? $status : '';
	}

	/**
	 * Parse CSV list into absint IDs.
	 *
	 * @return array<int,int>
	 */
	private static function webhook_urls_parse_csv_absints(string $csv): array {
		$parts = array_map('trim', explode(',', $csv));
		$ids = array_filter(array_map('absint', $parts));
		return array_values(array_unique($ids));
	}

	/**
	 * Parse CSV list into valid emails.
	 *
	 * @return array<int,string>
	 */
	private static function webhook_urls_parse_csv_emails(string $csv): array {
		$parts = array_map('trim', explode(',', $csv));
		$emails = [];
		foreach ($parts as $part) {
			$email = sanitize_email($part);
			if ($email !== '') {
				$emails[] = $email;
			}
		}
		return array_values(array_unique($emails));
	}

	/**
	 * Get request input from POST, then optional GET fallback.
	 */
	private static function webhook_urls_get_input(string $key, bool $allow_get): ?string {
		if (isset($_POST[$key]) && !is_array($_POST[$key])) {
			return (string) wp_unslash($_POST[$key]);
		}
		if ($allow_get && isset($_GET[$key]) && !is_array($_GET[$key])) {
			return (string) wp_unslash($_GET[$key]);
		}
		return null;
	}

	/**
	 * Emit JSON response and stop execution.
	 *
	 * @param array<string,mixed> $data
	 */
	private static function webhook_urls_json(array $data, int $status = 200): void {
		nocache_headers();
		status_header($status);
		header('Content-Type: application/json; charset=utf-8');
		if (self::$webhook_urls_debug_mode) {
			$data['debug'] = self::$webhook_urls_debug_log;
		}
		echo wp_json_encode($data, JSON_PRETTY_PRINT);
		exit;
	}

	/**
	 * Record a debug entry when debug mode is enabled.
	 *
	 * @param array<string,mixed>|null $data
	 */
	private static function webhook_urls_debug(string $message, ?array $data = null): void {
		if (!self::$webhook_urls_debug_mode) {
			return;
		}
		$entry = [
			'time' => microtime(true),
			'message' => $message,
		];
		if ($data !== null) {
			$entry['data'] = $data;
		}
		self::$webhook_urls_debug_log[] = $entry;
	}

	/**
	 * Detect source URL from request.
	 */
	private static function webhook_urls_detect_source_url(): string {
		if (!empty($_SERVER['HTTP_REFERER'])) {
			return esc_url_raw((string) wp_unslash($_SERVER['HTTP_REFERER']));
		}
		if (!empty($_SERVER['REQUEST_URI'])) {
			return esc_url_raw(home_url((string) wp_unslash($_SERVER['REQUEST_URI'])));
		}
		return 'Direct';
	}

	/**
	 * Detect request IP, respecting proxy headers.
	 */
	private static function webhook_urls_detect_ip(): string {
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$parts = explode(',', (string) wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
			return sanitize_text_field(trim((string) $parts[0]));
		}
		if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
			return sanitize_text_field((string) wp_unslash($_SERVER['HTTP_X_REAL_IP']));
		}
		if (!empty($_SERVER['REMOTE_ADDR'])) {
			return sanitize_text_field((string) wp_unslash($_SERVER['REMOTE_ADDR']));
		}
		return 'Unknown';
	}

	/**
	 * Return current user label for notes/logging.
	 */
	private static function webhook_urls_current_user_label(): string {
		$user = wp_get_current_user();
		if ($user && $user->exists()) {
			return (string) $user->user_login;
		}
		return 'Not logged in';
	}
}

