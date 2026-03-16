<?php
/**
 * Add-on card: Cart Price Per-Piece.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Cart_Price_Per_Piece {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['cart_price_per_piece_enabled']);
		$enable_cart_display = array_key_exists('cart_price_per_piece_enable_cart_display', $settings)
			? !empty($settings['cart_price_per_piece_enable_cart_display'])
			: true;
		$enable_order_display = array_key_exists('cart_price_per_piece_enable_order_display', $settings)
			? !empty($settings['cart_price_per_piece_enable_order_display'])
			: true;
		$suffix_text = isset($settings['cart_price_per_piece_suffix_text']) ? (string) $settings['cart_price_per_piece_suffix_text'] : '/ea';
		$font_size = isset($settings['cart_price_per_piece_font_size']) ? (string) $settings['cart_price_per_piece_font_size'] : '12px';
		$text_color = isset($settings['cart_price_per_piece_text_color']) ? (string) $settings['cart_price_per_piece_text_color'] : '#666666';
		$allowed_font_sizes = ['10px', '11px', '12px', '13px', '14px'];
		if (!in_array($font_size, $allowed_font_sizes, true)) {
			$font_size = '12px';
		}
		if (!preg_match('/^#[0-9a-fA-F]{6}$/', $text_color)) {
			$text_color = '#666666';
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-cart-price-per-piece" data-um-active-selectors="#um-cart-price-per-piece-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-cart"></span>
				<h2><?php esc_html_e('Cart Price Per-Piece', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-cart-price-per-piece-enabled" name="cart_price_per_piece_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Show per-piece unit pricing when line-item quantity is greater than 1, with controls for cart/order areas and display style.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-cart-price-per-piece-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="cart_price_per_piece_enable_cart_display" value="1" <?php checked($enable_cart_display); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Enable Cart Display', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Show per-piece pricing on cart line subtotals.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="cart_price_per_piece_enable_order_display" value="1" <?php checked($enable_order_display); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Enable Order Display', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Show per-piece pricing on checkout and customer order pages.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label for="um-cart-price-per-piece-suffix"><?php esc_html_e('Unit Price Suffix', 'user-manager'); ?></label>
						<input type="text" class="regular-text" id="um-cart-price-per-piece-suffix" name="cart_price_per_piece_suffix_text" value="<?php echo esc_attr($suffix_text); ?>"<?php echo $form_attr; ?> />
						<p class="description"><?php esc_html_e('Text displayed after the calculated unit price (default: /ea).', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label for="um-cart-price-per-piece-font-size"><?php esc_html_e('Unit Price Font Size', 'user-manager'); ?></label>
						<select id="um-cart-price-per-piece-font-size" name="cart_price_per_piece_font_size"<?php echo $form_attr; ?>>
							<?php foreach ($allowed_font_sizes as $size) : ?>
								<option value="<?php echo esc_attr($size); ?>" <?php selected($font_size, $size); ?>>
									<?php echo esc_html($size); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="um-form-field">
						<label for="um-cart-price-per-piece-text-color"><?php esc_html_e('Unit Price Text Color', 'user-manager'); ?></label>
						<input type="color" id="um-cart-price-per-piece-text-color" name="cart_price_per_piece_text_color" value="<?php echo esc_attr($text_color); ?>"<?php echo $form_attr; ?> />
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

