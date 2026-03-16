<?php
/**
 * Cart Price Per-Piece helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Cart_Price_Per_Piece_Trait {

	/**
	 * Register frontend hooks for Cart Price Per-Piece add-on.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_cart_price_per_piece(array $settings): void {
		if (empty($settings['cart_price_per_piece_enabled'])) {
			return;
		}

		add_filter('woocommerce_cart_item_subtotal', [__CLASS__, 'filter_cart_price_per_piece_subtotal'], 20, 3);
		add_filter('woocommerce_order_formatted_line_subtotal', [__CLASS__, 'filter_order_price_per_piece_subtotal'], 20, 3);
	}

	/**
	 * Add per-piece amount under cart/checkout line subtotal when qty > 1.
	 *
	 * @param string              $subtotal_html Existing formatted subtotal HTML.
	 * @param array<string,mixed> $cart_item Cart item data.
	 * @param string              $cart_item_key Cart item key.
	 */
	public static function filter_cart_price_per_piece_subtotal(string $subtotal_html, array $cart_item, string $cart_item_key): string {
		unset($cart_item_key);

		$settings = self::get_cart_price_per_piece_settings();
		if (!$settings['enabled']) {
			return $subtotal_html;
		}

		$is_cart_context = function_exists('is_cart') && is_cart();
		$is_checkout_context = function_exists('is_checkout') && is_checkout();
		if ($is_cart_context && !$settings['enable_cart_display']) {
			return $subtotal_html;
		}
		if ($is_checkout_context && !$settings['enable_order_display']) {
			return $subtotal_html;
		}
		if (!$is_cart_context && !$is_checkout_context) {
			return $subtotal_html;
		}

		$quantity = isset($cart_item['quantity']) ? absint($cart_item['quantity']) : 0;
		$product = isset($cart_item['data']) && is_object($cart_item['data']) ? $cart_item['data'] : null;
		if ($quantity <= 1 || !$product || !method_exists($product, 'get_price')) {
			return $subtotal_html;
		}

		if (!function_exists('WC') || !WC() || !WC()->cart || !method_exists(WC()->cart, 'get_product_subtotal')) {
			return $subtotal_html;
		}

		$unit_price_html = WC()->cart->get_product_subtotal($product, 1);
		if ($unit_price_html === '') {
			return $subtotal_html;
		}

		return $subtotal_html . self::render_cart_price_per_piece_html($unit_price_html, $settings);
	}

	/**
	 * Add per-piece amount under order line subtotal when qty > 1.
	 *
	 * @param string   $subtotal_html Existing formatted subtotal HTML.
	 * @param WC_Order_Item_Product $item Order item.
	 * @param WC_Order $order Order object.
	 */
	public static function filter_order_price_per_piece_subtotal(string $subtotal_html, $item, $order): string {
		$settings = self::get_cart_price_per_piece_settings();
		if (!$settings['enabled'] || !$settings['enable_order_display']) {
			return $subtotal_html;
		}

		if (self::is_cart_price_per_piece_email_context()) {
			return $subtotal_html;
		}

		$has_order = $order && is_object($order) && method_exists($order, 'get_id');
		$has_item = $item && is_object($item) && method_exists($item, 'get_quantity') && method_exists($item, 'get_total');
		if (!$has_order || !$has_item) {
			return $subtotal_html;
		}

		$quantity = absint($item->get_quantity());
		if ($quantity <= 1) {
			return $subtotal_html;
		}

		$line_total = (float) $item->get_total();
		$line_tax = (float) $item->get_total_tax();
		$line_total_with_tax = $line_total + $line_tax;
		if ($line_total_with_tax <= 0) {
			return $subtotal_html;
		}

		$unit_price = $line_total_with_tax / $quantity;
		if (!function_exists('wc_price')) {
			return $subtotal_html;
		}

		$unit_price_html = wc_price($unit_price, ['currency' => method_exists($order, 'get_currency') ? $order->get_currency() : '']);
		return $subtotal_html . self::render_cart_price_per_piece_html($unit_price_html, $settings);
	}

	/**
	 * Determine whether rendering is currently inside an email template.
	 */
	private static function is_cart_price_per_piece_email_context(): bool {
		return did_action('woocommerce_email_header') > did_action('woocommerce_email_footer');
	}

	/**
	 * Build per-piece HTML block.
	 *
	 * @param string $unit_price_html Formatted unit price HTML.
	 * @param array<string,mixed> $settings Normalized settings.
	 */
	private static function render_cart_price_per_piece_html(string $unit_price_html, array $settings): string {
		$font_size = isset($settings['font_size']) ? (string) $settings['font_size'] : '12px';
		$text_color = isset($settings['text_color']) ? (string) $settings['text_color'] : '#666666';
		$suffix = isset($settings['suffix_text']) ? (string) $settings['suffix_text'] : '/ea';
		return sprintf(
			'<small class="um-cart-price-per-piece" style="display:block;margin-top:2px;font-size:%1$s;color:%2$s;">%3$s %4$s</small>',
			esc_attr($font_size),
			esc_attr($text_color),
			wp_kses_post($unit_price_html),
			esc_html($suffix)
		);
	}

	/**
	 * Resolve validated settings for Cart Price Per-Piece.
	 *
	 * @return array{enabled:bool,enable_cart_display:bool,enable_order_display:bool,suffix_text:string,font_size:string,text_color:string}
	 */
	private static function get_cart_price_per_piece_settings(): array {
		$settings = self::get_settings();
		$allowed_font_sizes = ['10px', '11px', '12px', '13px', '14px'];
		$font_size = isset($settings['cart_price_per_piece_font_size']) ? (string) $settings['cart_price_per_piece_font_size'] : '12px';
		if (!in_array($font_size, $allowed_font_sizes, true)) {
			$font_size = '12px';
		}
		$text_color = isset($settings['cart_price_per_piece_text_color']) ? sanitize_hex_color((string) $settings['cart_price_per_piece_text_color']) : '#666666';
		if (empty($text_color)) {
			$text_color = '#666666';
		}
		$suffix_text = isset($settings['cart_price_per_piece_suffix_text']) ? sanitize_text_field((string) $settings['cart_price_per_piece_suffix_text']) : '/ea';
		if ($suffix_text === '') {
			$suffix_text = '/ea';
		}

		return [
			'enabled' => !empty($settings['cart_price_per_piece_enabled']),
			'enable_cart_display' => array_key_exists('cart_price_per_piece_enable_cart_display', $settings)
				? !empty($settings['cart_price_per_piece_enable_cart_display'])
				: true,
			'enable_order_display' => array_key_exists('cart_price_per_piece_enable_order_display', $settings)
				? !empty($settings['cart_price_per_piece_enable_order_display'])
				: true,
			'suffix_text' => $suffix_text,
			'font_size' => $font_size,
			'text_color' => $text_color,
		];
	}
}

