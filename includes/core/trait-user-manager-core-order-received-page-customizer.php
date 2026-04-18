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
		add_filter('the_title', [__CLASS__, 'filter_order_received_page_theme_title'], 20, 2);
		add_filter('document_title_parts', [__CLASS__, 'filter_order_received_document_title_parts'], 20, 1);
		add_filter('pre_get_document_title', [__CLASS__, 'filter_order_received_document_title'], 20, 1);
		add_filter('woocommerce_thankyou_order_received_text', [__CLASS__, 'filter_order_received_page_notice_text'], 20, 2);
		add_action('wp_footer', [__CLASS__, 'print_order_received_heading_override_script'], 99);
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
	 * Override theme-provided checkout page titles on order-received context.
	 *
	 * Many themes output the H1 via the_title() instead of woocommerce_page_title().
	 *
	 * @param string $title   Existing title text.
	 * @param int    $post_id Post ID.
	 */
	public static function filter_order_received_page_theme_title(string $title, int $post_id): string {
		if (!self::is_order_received_page_customizer_context()) {
			return $title;
		}

		if (!function_exists('wc_get_page_id')) {
			return $title;
		}

		$checkout_page_id = (int) wc_get_page_id('checkout');
		if ($checkout_page_id <= 0 || $post_id !== $checkout_page_id) {
			return $title;
		}

		$custom_title = self::get_order_received_page_customizer_heading_text();
		return $custom_title !== '' ? $custom_title : $title;
	}

	/**
	 * Override browser/page title parts on order-received context.
	 *
	 * @param array<string,string> $parts Document title parts.
	 * @return array<string,string>
	 */
	public static function filter_order_received_document_title_parts(array $parts): array {
		if (!self::is_order_received_page_customizer_context()) {
			return $parts;
		}

		$custom_title = self::get_order_received_page_customizer_heading_text();
		if ($custom_title !== '') {
			$parts['title'] = $custom_title;
		}

		return $parts;
	}

	/**
	 * Override full document title on order-received context.
	 */
	public static function filter_order_received_document_title(string $title): string {
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
	 * Front-end fallback to update H1 in themes/templates that bypass filters.
	 */
	public static function print_order_received_heading_override_script(): void {
		if (!self::is_order_received_page_customizer_context()) {
			return;
		}

		$custom_title = self::get_order_received_page_customizer_heading_text();
		if ($custom_title === '') {
			return;
		}
		?>
		<script>
		(function() {
			var customTitle = <?php echo wp_json_encode($custom_title); ?>;
			if (!customTitle) {
				return;
			}

			function applyHeadingOverride() {
				var selectors = [
					'.woocommerce-order h1',
					'.woocommerce-order-received h1',
					'.entry-header .entry-title',
					'h1.entry-title',
					'.woocommerce h1.page-title'
				];
				for (var i = 0; i < selectors.length; i++) {
					var heading = document.querySelector(selectors[i]);
					if (heading) {
						heading.textContent = customTitle;
						return;
					}
				}
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', applyHeadingOverride);
			} else {
				applyHeadingOverride();
			}
		})();
		</script>
		<?php
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

