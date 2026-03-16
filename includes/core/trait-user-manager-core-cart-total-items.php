<?php
/**
 * Cart Total Items helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Cart_Total_Items_Trait {

	/**
	 * Register frontend hooks for Cart Total Items add-on.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_cart_total_items(array $settings): void {
		if (empty($settings['cart_total_items_enabled'])) {
			return;
		}

		add_action('woocommerce_before_cart_table', [__CLASS__, 'render_cart_total_items_above_cart_table'], 10);
		add_action('woocommerce_after_cart_table', [__CLASS__, 'render_cart_total_items_below_cart_table'], 10);
		add_action('woocommerce_checkout_before_order_review', [__CLASS__, 'render_cart_total_items_above_checkout_review'], 10);
		add_action('woocommerce_review_order_before_payment', [__CLASS__, 'render_cart_total_items_below_checkout_review'], 10);
	}

	/**
	 * Render total items above the cart table.
	 */
	public static function render_cart_total_items_above_cart_table(): void {
		if (!function_exists('is_cart') || !is_cart()) {
			return;
		}
		self::maybe_render_cart_total_items_block('cart', 'above');
	}

	/**
	 * Render total items below the cart table.
	 */
	public static function render_cart_total_items_below_cart_table(): void {
		if (!function_exists('is_cart') || !is_cart()) {
			return;
		}
		self::maybe_render_cart_total_items_block('cart', 'below');
	}

	/**
	 * Render total items above checkout review area.
	 */
	public static function render_cart_total_items_above_checkout_review(): void {
		if (!function_exists('is_checkout') || !is_checkout()) {
			return;
		}
		if (function_exists('is_order_received_page') && is_order_received_page()) {
			return;
		}
		self::maybe_render_cart_total_items_block('checkout', 'above');
	}

	/**
	 * Render total items below checkout review table.
	 */
	public static function render_cart_total_items_below_checkout_review(): void {
		if (!function_exists('is_checkout') || !is_checkout()) {
			return;
		}
		if (function_exists('is_order_received_page') && is_order_received_page()) {
			return;
		}
		self::maybe_render_cart_total_items_block('checkout', 'below');
	}

	/**
	 * Conditionally output the cart total items text block.
	 *
	 * @param string $context  cart|checkout
	 * @param string $position above|below
	 */
	private static function maybe_render_cart_total_items_block(string $context, string $position): void {
		if (is_admin() && !wp_doing_ajax()) {
			return;
		}
		if (!function_exists('WC') || !WC() || !WC()->cart) {
			return;
		}

		$settings = self::get_cart_total_items_settings();
		if (!$settings['enabled']) {
			return;
		}
		if (!self::cart_total_items_should_render_position($settings, $context, $position)) {
			return;
		}

		$total_items = max(0, (int) WC()->cart->get_cart_contents_count());
		$copy = trim((string) $settings['copy']);
		if ($copy === '') {
			$copy = 'Total Items:';
		}
		?>
		<div
			class="um-cart-total-items um-cart-total-items-<?php echo esc_attr($context); ?>-<?php echo esc_attr($position); ?>"
			style="text-align:center; margin:10px 0;"
		>
			<?php echo esc_html($copy); ?> <?php echo esc_html((string) $total_items); ?>
		</div>
		<?php
	}

	/**
	 * Determine whether to display a context/position block.
	 *
	 * @param array<string,mixed> $settings Normalized settings.
	 */
	private static function cart_total_items_should_render_position(array $settings, string $context, string $position): bool {
		if ($context === 'cart') {
			if (!$settings['show_on_cart']) {
				return false;
			}
			return $position === 'above' ? $settings['cart_above'] : $settings['cart_below'];
		}

		if ($context === 'checkout') {
			if (!$settings['show_on_checkout']) {
				return false;
			}
			return $position === 'above' ? $settings['checkout_above'] : $settings['checkout_below'];
		}

		return false;
	}

	/**
	 * Build normalized settings array for Cart Total Items.
	 *
	 * @return array{enabled:bool,copy:string,show_on_cart:bool,show_on_checkout:bool,cart_above:bool,cart_below:bool,checkout_above:bool,checkout_below:bool}
	 */
	private static function get_cart_total_items_settings(): array {
		$settings = self::get_settings();

		return [
			'enabled' => !empty($settings['cart_total_items_enabled']),
			'copy' => isset($settings['cart_total_items_copy'])
				? sanitize_text_field((string) $settings['cart_total_items_copy'])
				: 'Total Items:',
			'show_on_cart' => array_key_exists('cart_total_items_show_on_cart', $settings)
				? !empty($settings['cart_total_items_show_on_cart'])
				: true,
			'show_on_checkout' => array_key_exists('cart_total_items_show_on_checkout', $settings)
				? !empty($settings['cart_total_items_show_on_checkout'])
				: true,
			'cart_above' => !empty($settings['cart_total_items_cart_above']),
			'cart_below' => array_key_exists('cart_total_items_cart_below', $settings)
				? !empty($settings['cart_total_items_cart_below'])
				: true,
			'checkout_above' => !empty($settings['cart_total_items_checkout_above']),
			'checkout_below' => array_key_exists('cart_total_items_checkout_below', $settings)
				? !empty($settings['cart_total_items_checkout_below'])
				: true,
		];
	}
}

