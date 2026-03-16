<?php
/**
 * Order Received page customizer helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Order_Received_Page_Customizer_Trait {

	/**
	 * Register front-end hooks for Order Received page customizer add-on.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_order_received_page_customizer(array $settings): void {
		if (empty($settings['order_received_page_customizer_enabled'])) {
			return;
		}

		add_filter('woocommerce_page_title', [__CLASS__, 'filter_order_received_page_title'], 20, 1);
		add_filter('woocommerce_thankyou_order_received_text', [__CLASS__, 'filter_order_received_page_notice_text'], 20, 2);
	}

	/**
	 * Override Order Received H1 title when enabled.
	 */
	public static function filter_order_received_page_title(string $title): string {
		if (!self::is_order_received_page_customizer_context()) {
			return $title;
		}

		$custom_title = self::get_order_received_page_customizer_heading_text();
		return $custom_title !== '' ? $custom_title : $title;
	}

	/**
	 * Override Order Received success paragraph text when enabled.
	 *
	 * @param string        $notice_text Existing message text.
	 * @param WC_Order|false $order       Order object when available.
	 */
	public static function filter_order_received_page_notice_text(string $notice_text, $order): string {
		unset($order);

		if (!self::is_order_received_page_customizer_context()) {
			return $notice_text;
		}

		$custom_notice = self::get_order_received_page_customizer_paragraph_text();
		return $custom_notice !== '' ? $custom_notice : $notice_text;
	}

	/**
	 * Confirm this request is a frontend order-received page.
	 */
	private static function is_order_received_page_customizer_context(): bool {
		if (is_admin()) {
			return false;
		}
		if (!function_exists('is_order_received_page')) {
			return false;
		}
		return is_order_received_page();
	}

	/**
	 * Read custom H1 text.
	 */
	private static function get_order_received_page_customizer_heading_text(): string {
		$settings = self::get_settings();
		$text = isset($settings['order_received_page_customizer_heading_text'])
			? sanitize_text_field((string) $settings['order_received_page_customizer_heading_text'])
			: '';

		if ($text === '') {
			$text = 'Order received';
		}
		return $text;
	}

	/**
	 * Read custom success paragraph text.
	 */
	private static function get_order_received_page_customizer_paragraph_text(): string {
		$settings = self::get_settings();
		$text = isset($settings['order_received_page_customizer_paragraph_text'])
			? sanitize_textarea_field((string) $settings['order_received_page_customizer_paragraph_text'])
			: '';

		if ($text === '') {
			$text = 'Thank you. Your order has been received.';
		}
		return $text;
	}
}

