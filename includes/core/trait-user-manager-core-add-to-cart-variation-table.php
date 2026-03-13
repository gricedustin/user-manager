<?php
/**
 * Add to Cart Variation Table feature helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Add_To_Cart_Variation_Table_Trait {

	/**
	 * Render variation quantity table on variable product pages.
	 */
	public static function maybe_render_add_to_cart_variation_table(): void {
		if (!function_exists('is_product') || !is_product()) {
			return;
		}
		if (!function_exists('wc_get_product')) {
			return;
		}

		global $product;
		if (!$product instanceof WC_Product || !$product->is_type('variable')) {
			return;
		}
		if (!$product->is_purchasable()) {
			return;
		}

		$available_variations = $product->get_available_variations();
		if (empty($available_variations) || !is_array($available_variations)) {
			return;
		}

		$rows = [];
		foreach ($available_variations as $variation_data) {
			$variation_id = isset($variation_data['variation_id']) ? absint($variation_data['variation_id']) : 0;
			if ($variation_id <= 0) {
				continue;
			}

			$variation = wc_get_product($variation_id);
			if (!$variation instanceof WC_Product_Variation) {
				continue;
			}

			$is_in_stock = $variation->is_in_stock();
			$is_row_disabled = !$variation->is_purchasable() || !$is_in_stock;
			$max_qty = '';
			if (!$variation->backorders_allowed()) {
				$stock_qty = $variation->get_stock_quantity();
				if ($stock_qty !== null) {
					$max_qty = (string) max(0, (int) $stock_qty);
				}
			}

			$rows[] = [
				'id'          => $variation_id,
				'label'       => wc_get_formatted_variation($variation, true, true, false),
				'sku'         => (string) $variation->get_sku(),
				'price_html'  => (string) $variation->get_price_html(),
				'status'      => $is_in_stock ? __('In stock', 'user-manager') : __('Out of stock', 'user-manager'),
				'disabled'    => $is_row_disabled,
				'max'         => $max_qty,
			];
		}

		if (empty($rows)) {
			return;
		}
		?>
		<div class="um-add-to-cart-variation-table" style="margin-top:16px;">
			<h3 style="margin:0 0 10px;"><?php esc_html_e('Add Multiple Variations', 'user-manager'); ?></h3>
			<p class="description" style="margin:0 0 12px;">
				<?php esc_html_e('Enter quantities for one or more variations, then add all selected rows to cart at once.', 'user-manager'); ?>
			</p>
			<form method="post" class="cart um-add-to-cart-variation-table-form">
				<?php wp_nonce_field('um_add_to_cart_variation_table_submit', 'um_add_to_cart_variation_table_nonce'); ?>
				<input type="hidden" name="um_add_to_cart_variation_table_submit" value="1" />
				<input type="hidden" name="um_add_to_cart_variation_table_product_id" value="<?php echo esc_attr((string) $product->get_id()); ?>" />
				<table class="shop_table shop_table_responsive" style="margin-bottom:12px;">
					<thead>
						<tr>
							<th><?php esc_html_e('Variation', 'user-manager'); ?></th>
							<th><?php esc_html_e('SKU', 'user-manager'); ?></th>
							<th><?php esc_html_e('Price', 'user-manager'); ?></th>
							<th><?php esc_html_e('Status', 'user-manager'); ?></th>
							<th style="width:120px;"><?php esc_html_e('Qty', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($rows as $row) : ?>
							<tr>
								<td><?php echo esc_html($row['label'] !== '' ? $row['label'] : ('#' . (string) $row['id'])); ?></td>
								<td><?php echo esc_html($row['sku'] !== '' ? $row['sku'] : '-'); ?></td>
								<td><?php echo wp_kses_post($row['price_html'] !== '' ? $row['price_html'] : '-'); ?></td>
								<td><?php echo esc_html($row['status']); ?></td>
								<td>
									<input
										type="number"
										min="0"
										step="1"
										name="um_add_to_cart_variation_qty[<?php echo esc_attr((string) $row['id']); ?>]"
										value="0"
										class="input-text qty text"
										style="max-width:90px;"
										<?php echo $row['disabled'] ? 'disabled' : ''; ?>
										<?php echo $row['max'] !== '' ? 'max="' . esc_attr($row['max']) . '"' : ''; ?>
									/>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<button type="submit" class="button alt"><?php esc_html_e('Add Selected Variations to Cart', 'user-manager'); ?></button>
			</form>
		</div>
		<?php
	}

	/**
	 * Process variation-table add to cart submissions.
	 */
	public static function handle_add_to_cart_variation_table_submission(): void {
		if (empty($_POST['um_add_to_cart_variation_table_submit'])) {
			return;
		}
		if (is_admin() || wp_doing_ajax()) {
			return;
		}
		if (!function_exists('WC') || !function_exists('wc_get_product')) {
			return;
		}
		if (empty($_POST['um_add_to_cart_variation_table_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['um_add_to_cart_variation_table_nonce'])), 'um_add_to_cart_variation_table_submit')) {
			wc_add_notice(__('Security check failed. Please try again.', 'user-manager'), 'error');
			self::redirect_add_to_cart_variation_table_request(0);
		}

		$product_id = isset($_POST['um_add_to_cart_variation_table_product_id']) ? absint($_POST['um_add_to_cart_variation_table_product_id']) : 0;
		$product = $product_id > 0 ? wc_get_product($product_id) : null;
		if (!$product instanceof WC_Product || !$product->is_type('variable')) {
			wc_add_notice(__('Invalid variable product for bulk variation add to cart.', 'user-manager'), 'error');
			self::redirect_add_to_cart_variation_table_request($product_id);
		}

		if (!WC()->cart && function_exists('wc_load_cart')) {
			wc_load_cart();
		}
		if (!WC()->cart) {
			wc_add_notice(__('Cart is unavailable right now. Please try again.', 'user-manager'), 'error');
			self::redirect_add_to_cart_variation_table_request($product_id);
		}

		$qty_map = [];
		if (isset($_POST['um_add_to_cart_variation_qty']) && is_array($_POST['um_add_to_cart_variation_qty'])) {
			$qty_map = (array) wp_unslash($_POST['um_add_to_cart_variation_qty']);
		}

		$rows_selected = 0;
		$items_added = 0;
		$error_messages = [];

		foreach ($qty_map as $variation_id_raw => $qty_raw) {
			$variation_id = absint((string) $variation_id_raw);
			if ($variation_id <= 0) {
				continue;
			}

			$qty = is_numeric($qty_raw) ? (int) floor((float) $qty_raw) : 0;
			if ($qty <= 0) {
				continue;
			}

			$rows_selected++;

			$variation = wc_get_product($variation_id);
			if (!$variation instanceof WC_Product_Variation || $variation->get_parent_id() !== $product_id) {
				$error_messages[] = sprintf(__('Variation #%d is not valid for this product.', 'user-manager'), $variation_id);
				continue;
			}

			if (!$variation->is_purchasable() || !$variation->is_in_stock()) {
				$error_messages[] = sprintf(__('Variation #%d is currently unavailable.', 'user-manager'), $variation_id);
				continue;
			}

			if (!$variation->backorders_allowed()) {
				$stock_qty = $variation->get_stock_quantity();
				if ($stock_qty !== null) {
					$qty = min($qty, max(0, (int) $stock_qty));
				}
			}
			if ($qty <= 0) {
				$error_messages[] = sprintf(__('Variation #%d has no available stock.', 'user-manager'), $variation_id);
				continue;
			}

			$added = WC()->cart->add_to_cart(
				$product_id,
				$qty,
				$variation_id,
				$variation->get_variation_attributes()
			);

			if ($added) {
				$items_added += $qty;
			} else {
				$error_messages[] = sprintf(__('Variation #%d could not be added to cart.', 'user-manager'), $variation_id);
			}
		}

		if ($rows_selected === 0) {
			wc_add_notice(__('Enter a quantity greater than 0 for at least one variation.', 'user-manager'), 'notice');
			self::redirect_add_to_cart_variation_table_request($product_id);
		}

		if ($items_added > 0) {
			wc_add_notice(
				sprintf(
					/* translators: %d: number of items added */
					_n('%d item added to cart from variation table.', '%d items added to cart from variation table.', $items_added, 'user-manager'),
					$items_added
				),
				'success'
			);
		}

		if (!empty($error_messages)) {
			$error_preview = implode(' ', array_slice($error_messages, 0, 4));
			if (count($error_messages) > 4) {
				$error_preview .= ' ' . __('Additional variation errors occurred.', 'user-manager');
			}
			wc_add_notice($error_preview, 'error');
		}

		self::redirect_add_to_cart_variation_table_request($product_id);
	}

	/**
	 * Redirect after processing to prevent duplicate submissions on refresh.
	 */
	private static function redirect_add_to_cart_variation_table_request(int $product_id): void {
		$redirect_url = wp_get_referer();
		if (!is_string($redirect_url) || $redirect_url === '') {
			$redirect_url = $product_id > 0 ? get_permalink($product_id) : home_url('/');
		}
		if (!is_string($redirect_url) || $redirect_url === '') {
			$redirect_url = home_url('/');
		}
		wp_safe_redirect($redirect_url);
		exit;
	}
}

