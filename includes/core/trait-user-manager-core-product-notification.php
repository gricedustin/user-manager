<?php
/**
 * Product Notification helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Product_Notification_Trait {

	/**
	 * Product meta key: persistent notification message.
	 */
	private static string $product_notification_message_meta_key = '_um_product_notification_message';

	/**
	 * Product meta key: optional button title.
	 */
	private static string $product_notification_button_title_meta_key = '_um_product_notification_button_title';

	/**
	 * Product meta key: optional button URL.
	 */
	private static string $product_notification_button_url_meta_key = '_um_product_notification_button_url';

	/**
	 * Register hooks for Product Notification add-on.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_product_notification(array $settings): void {
		if (empty($settings['product_notification_enabled']) || self::is_addon_temporarily_disabled('product-notification')) {
			return;
		}

		add_action('woocommerce_product_options_general_product_data', [__CLASS__, 'render_product_notification_general_fields'], 999);
		add_action('woocommerce_process_product_meta', [__CLASS__, 'save_product_notification_general_fields'], 10, 1);
		add_action('woocommerce_before_single_product', [__CLASS__, 'render_product_notification_above_product'], 5);
	}

	/**
	 * Render product-level fields in Product Data > General.
	 */
	public static function render_product_notification_general_fields(): void {
		global $post;
		$product_id = isset($post->ID) ? absint($post->ID) : 0;
		if ($product_id <= 0) {
			return;
		}

		$message = (string) get_post_meta($product_id, self::$product_notification_message_meta_key, true);
		$button_title = (string) get_post_meta($product_id, self::$product_notification_button_title_meta_key, true);
		$button_url = (string) get_post_meta($product_id, self::$product_notification_button_url_meta_key, true);

		echo '<div class="options_group">';

		woocommerce_wp_textarea_input([
			'id' => 'um_product_notification_message',
			'label' => __('Display a Woocommerce Notification Above Product at All Times?', 'user-manager'),
			'desc_tip' => true,
			'description' => __('If this field has text, a WooCommerce-style message displays above this product at all times.', 'user-manager'),
			'placeholder' => __('"%PRODUCT_TITLE%" has been added to your cart.', 'user-manager'),
			'value' => $message,
		]);

		woocommerce_wp_text_input([
			'id' => 'um_product_notification_button_title',
			'label' => __('Optional Button Title', 'user-manager'),
			'desc_tip' => true,
			'description' => __('Leave empty to use "View cart".', 'user-manager'),
			'value' => $button_title,
		]);

		woocommerce_wp_text_input([
			'id' => 'um_product_notification_button_url',
			'label' => __('Optional Button URL', 'user-manager'),
			'desc_tip' => true,
			'description' => __('Leave empty to use the WooCommerce cart URL.', 'user-manager'),
			'value' => $button_url,
		]);

		echo '</div>';
	}

	/**
	 * Save product-level Product Notification fields.
	 */
	public static function save_product_notification_general_fields(int $product_id): void {
		if ($product_id <= 0 || !current_user_can('edit_post', $product_id)) {
			return;
		}

		$message = isset($_POST['um_product_notification_message'])
			? sanitize_textarea_field(wp_unslash($_POST['um_product_notification_message']))
			: '';
		$button_title = isset($_POST['um_product_notification_button_title'])
			? sanitize_text_field(wp_unslash($_POST['um_product_notification_button_title']))
			: '';
		$button_url = isset($_POST['um_product_notification_button_url'])
			? esc_url_raw(wp_unslash($_POST['um_product_notification_button_url']))
			: '';

		if ($message !== '') {
			update_post_meta($product_id, self::$product_notification_message_meta_key, $message);
		} else {
			delete_post_meta($product_id, self::$product_notification_message_meta_key);
		}

		if ($button_title !== '') {
			update_post_meta($product_id, self::$product_notification_button_title_meta_key, $button_title);
		} else {
			delete_post_meta($product_id, self::$product_notification_button_title_meta_key);
		}

		if ($button_url !== '') {
			update_post_meta($product_id, self::$product_notification_button_url_meta_key, $button_url);
		} else {
			delete_post_meta($product_id, self::$product_notification_button_url_meta_key);
		}
	}

	/**
	 * Render a persistent WooCommerce-style notice above the product page.
	 */
	public static function render_product_notification_above_product(): void {
		if (!function_exists('is_product') || !is_product()) {
			return;
		}

		$product_id = get_queried_object_id();
		if ($product_id <= 0) {
			return;
		}

		$message = trim((string) get_post_meta($product_id, self::$product_notification_message_meta_key, true));
		if ($message === '') {
			return;
		}

		$product_title = get_the_title($product_id);
		$resolved_message = str_replace('%PRODUCT_TITLE%', (string) $product_title, $message);
		$resolved_message = wp_kses_post($resolved_message);

		$button_title = trim((string) get_post_meta($product_id, self::$product_notification_button_title_meta_key, true));
		if ($button_title === '') {
			$button_title = __('View cart', 'user-manager');
		}

		$button_url = trim((string) get_post_meta($product_id, self::$product_notification_button_url_meta_key, true));
		if ($button_url === '' && function_exists('wc_get_cart_url')) {
			$button_url = wc_get_cart_url();
		}

		$settings = self::get_settings();
		$colors = self::get_product_notification_color_settings($settings);

		$notice_style = sprintf(
			'background-color: %1$s !important; color: %2$s !important;',
			esc_attr($colors['background']),
			esc_attr($colors['text'])
		);
		$button_style = sprintf(
			'background-color: %1$s !important; border-color: %1$s !important; color: %2$s !important;',
			esc_attr($colors['button_background']),
			esc_attr($colors['button_text'])
		);
		?>
		<style id="um-product-notification-hover-colors">
			.woocommerce-message.um-product-notification-message .um-product-notification-button:hover,
			.woocommerce-message.um-product-notification-message .um-product-notification-button:focus {
				background-color: <?php echo esc_html($colors['button_hover_background']); ?> !important;
				border-color: <?php echo esc_html($colors['button_hover_background']); ?> !important;
				color: <?php echo esc_html($colors['button_hover_text']); ?> !important;
			}
		</style>
		<div class="woocommerce-message um-product-notification-message" role="alert" style="<?php echo $notice_style; ?>" tabindex="-1">
			<?php echo wp_kses_post($resolved_message); ?>
			<?php if ($button_url !== '') : ?>
				<a href="<?php echo esc_url($button_url); ?>" class="button wc-forward um-product-notification-button" style="<?php echo $button_style; ?>"><?php echo esc_html($button_title); ?></a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Resolve Product Notification colors from settings (with defaults).
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array{background:string,text:string,button_background:string,button_text:string,button_hover_background:string,button_hover_text:string}
	 */
	private static function get_product_notification_color_settings(array $settings): array {
		$defaults = [
			'background' => '#1e73be',
			'text' => '#ffffff',
			'button_background' => '#ffffff',
			'button_text' => '#000000',
			'button_hover_background' => '#f1f1f1',
			'button_hover_text' => '#000000',
		];

		$background = sanitize_hex_color((string) ($settings['product_notification_bg_color'] ?? ''));
		$text = sanitize_hex_color((string) ($settings['product_notification_text_color'] ?? ''));
		$button_background = sanitize_hex_color((string) ($settings['product_notification_button_bg_color'] ?? ''));
		$button_text = sanitize_hex_color((string) ($settings['product_notification_button_text_color'] ?? ''));
		$button_hover_background = sanitize_hex_color((string) ($settings['product_notification_button_hover_bg_color'] ?? ''));
		$button_hover_text = sanitize_hex_color((string) ($settings['product_notification_button_hover_text_color'] ?? ''));

		return [
			'background' => $background ?: $defaults['background'],
			'text' => $text ?: $defaults['text'],
			'button_background' => $button_background ?: $defaults['button_background'],
			'button_text' => $button_text ?: $defaults['button_text'],
			'button_hover_background' => $button_hover_background ?: $defaults['button_hover_background'],
			'button_hover_text' => $button_hover_text ?: $defaults['button_hover_text'],
		];
	}
}

