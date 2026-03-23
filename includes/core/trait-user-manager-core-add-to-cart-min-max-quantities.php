<?php
/**
 * Add to Cart Min/Max Quantities helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Add_To_Cart_Min_Max_Quantities_Trait {
	/**
	 * Product meta key for minimum allowed quantity.
	 */
	private static string $um_min_qty_meta_key = '_um_minimum_allowed_quantity';

	/**
	 * Product meta key for maximum allowed quantity.
	 */
	private static string $um_max_qty_meta_key = '_um_maximum_allowed_quantity';

	/**
	 * Register hooks for Add to Cart Min/Max Quantities add-on.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_add_to_cart_min_max_quantities(array $settings): void {
		if (empty($settings['add_to_cart_min_max_quantities_enabled'])) {
			return;
		}

		// Product edit UI (Inventory tab).
		add_action('woocommerce_product_options_inventory_product_data', [__CLASS__, 'render_add_to_cart_min_max_quantity_fields']);
		add_action('woocommerce_process_product_meta', [__CLASS__, 'save_add_to_cart_min_max_quantity_fields'], 10, 1);

		// Front-end quantity UI + validation.
		add_filter('woocommerce_quantity_input_args', [__CLASS__, 'filter_add_to_cart_min_max_quantity_input_args'], 10, 2);
		add_filter('woocommerce_add_to_cart_validation', [__CLASS__, 'validate_add_to_cart_min_max_quantities'], 10, 5);
		add_action('woocommerce_check_cart_items', [__CLASS__, 'validate_cart_item_min_max_quantities']);
	}

	/**
	 * Render min/max quantity fields in product Inventory options.
	 */
	public static function render_add_to_cart_min_max_quantity_fields(): void {
		global $post;
		$product_id = isset($post->ID) ? absint($post->ID) : 0;
		if ($product_id <= 0) {
			return;
		}

		$minimum_allowed_quantity = self::get_quantity_limit_meta_value($product_id, self::$um_min_qty_meta_key);
		$maximum_allowed_quantity = self::get_quantity_limit_meta_value($product_id, self::$um_max_qty_meta_key);
		?>
		<p class="form-field minimum_allowed_quantity_field">
			<label for="um_minimum_allowed_quantity"><?php esc_html_e('Minimum quantity', 'user-manager'); ?></label>
			<span
				class="woocommerce-help-tip"
				tabindex="0"
				aria-label="<?php echo esc_attr(__('Enter a minimum required quantity for this product.', 'user-manager')); ?>"
			></span>
			<input
				type="number"
				class="short"
				name="um_minimum_allowed_quantity"
				id="um_minimum_allowed_quantity"
				value="<?php echo esc_attr($minimum_allowed_quantity > 0 ? (string) $minimum_allowed_quantity : ''); ?>"
				min="0"
				step="1"
			/>
		</p>

		<p class="form-field maximum_allowed_quantity_field">
			<label for="um_maximum_allowed_quantity"><?php esc_html_e('Maximum quantity', 'user-manager'); ?></label>
			<span
				class="woocommerce-help-tip"
				tabindex="0"
				aria-label="<?php echo esc_attr(__('Enter a maximum allowed quantity for this product.', 'user-manager')); ?>"
			></span>
			<input
				type="number"
				class="short"
				name="um_maximum_allowed_quantity"
				id="um_maximum_allowed_quantity"
				value="<?php echo esc_attr($maximum_allowed_quantity > 0 ? (string) $maximum_allowed_quantity : ''); ?>"
				min="0"
				step="1"
			/>
		</p>
		<?php
	}

	/**
	 * Save product-level min/max quantity settings.
	 */
	public static function save_add_to_cart_min_max_quantity_fields(int $product_id): void {
		if ($product_id <= 0 || !current_user_can('edit_post', $product_id)) {
			return;
		}

		$minimum_allowed_quantity = isset($_POST['um_minimum_allowed_quantity'])
			? max(0, absint(wp_unslash($_POST['um_minimum_allowed_quantity'])))
			: 0;
		$maximum_allowed_quantity = isset($_POST['um_maximum_allowed_quantity'])
			? max(0, absint(wp_unslash($_POST['um_maximum_allowed_quantity'])))
			: 0;

		if ($minimum_allowed_quantity > 0 && $maximum_allowed_quantity > 0 && $maximum_allowed_quantity < $minimum_allowed_quantity) {
			$maximum_allowed_quantity = $minimum_allowed_quantity;
		}

		if ($minimum_allowed_quantity > 0) {
			update_post_meta($product_id, self::$um_min_qty_meta_key, $minimum_allowed_quantity);
		} else {
			delete_post_meta($product_id, self::$um_min_qty_meta_key);
		}

		if ($maximum_allowed_quantity > 0) {
			update_post_meta($product_id, self::$um_max_qty_meta_key, $maximum_allowed_quantity);
		} else {
			delete_post_meta($product_id, self::$um_max_qty_meta_key);
		}
	}

	/**
	 * Filter quantity input min/max values for add-to-cart quantity fields.
	 *
	 * @param array<string,mixed> $args
	 * @param mixed               $product
	 * @return array<string,mixed>
	 */
	public static function filter_add_to_cart_min_max_quantity_input_args(array $args, $product): array {
		if (!$product instanceof WC_Product) {
			return $args;
		}
		if (function_exists('is_cart') && is_cart()) {
			return $args;
		}

		$limits = self::get_product_min_max_quantity_limits($product);
		$min = isset($limits['min']) ? max(0, (int) $limits['min']) : 0;
		$max = isset($limits['max']) ? max(0, (int) $limits['max']) : 0;
		if ($min <= 0 && $max <= 0) {
			return $args;
		}

		if ($min > 0) {
			$current_min = isset($args['min_value']) ? (int) $args['min_value'] : 0;
			$args['min_value'] = max($current_min, $min);
		}

		if ($max > 0) {
			$current_max = isset($args['max_value']) ? (int) $args['max_value'] : 0;
			$args['max_value'] = $current_max > 0 ? min($current_max, $max) : $max;
		}

		if (isset($args['min_value'], $args['max_value']) && (int) $args['max_value'] > 0 && (int) $args['max_value'] < (int) $args['min_value']) {
			// Preserve stricter upper bounds (for example stock limits) and clamp min down.
			$args['min_value'] = (int) $args['max_value'];
		}

		return $args;
	}

	/**
	 * Validate min/max when adding to cart.
	 *
	 * @param bool       $passed
	 * @param int|string $product_id
	 * @param int|float  $quantity
	 * @param int|string $variation_id
	 * @return bool
	 */
	public static function validate_add_to_cart_min_max_quantities($passed, $product_id, $quantity, $variation_id = 0, $variations = []): bool {
		$passed = (bool) $passed;
		if (!$passed) {
			return false;
		}

		$product_id = absint($product_id);
		$variation_id = absint($variation_id);
		$target_product_id = $variation_id > 0 ? $variation_id : $product_id;
		$product = $target_product_id > 0 && function_exists('wc_get_product') ? wc_get_product($target_product_id) : null;
		if (!$product instanceof WC_Product) {
			return $passed;
		}

		$limits = self::get_product_min_max_quantity_limits($product);
		$min = isset($limits['min']) ? max(0, (int) $limits['min']) : 0;
		$max = isset($limits['max']) ? max(0, (int) $limits['max']) : 0;
		if ($min <= 0 && $max <= 0) {
			return $passed;
		}

		$scope_product_id = isset($limits['scope_product_id']) ? absint($limits['scope_product_id']) : 0;
		$scope_variation_id = isset($limits['scope_variation_id']) ? absint($limits['scope_variation_id']) : 0;

		$existing_qty = self::get_cart_quantity_for_scope($scope_product_id, $scope_variation_id);
		$requested_qty = max(0, (int) floor((float) $quantity));
		$new_total_qty = $existing_qty + $requested_qty;

		$product_label = self::get_product_label_for_min_max_notice($product);

		if ($min > 0 && $new_total_qty < $min) {
			wc_add_notice(
				sprintf(
					/* translators: 1: product name, 2: minimum quantity, 3: current quantity */
					__('"%1$s" requires a minimum quantity of %2$d. Current quantity: %3$d.', 'user-manager'),
					$product_label,
					$min,
					$new_total_qty
				),
				'error'
			);
			return false;
		}

		if ($max > 0 && $new_total_qty > $max) {
			wc_add_notice(
				sprintf(
					/* translators: 1: product name, 2: maximum quantity, 3: current quantity */
					__('"%1$s" allows a maximum quantity of %2$d. Current quantity: %3$d.', 'user-manager'),
					$product_label,
					$max,
					$new_total_qty
				),
				'error'
			);
			return false;
		}

		return $passed;
	}

	/**
	 * Validate existing cart rows against min/max limits.
	 *
	 * Runs during cart/checkout validation so quantity edits are still enforced.
	 */
	public static function validate_cart_item_min_max_quantities(): void {
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}

		$grouped_limits = [];
		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$product = isset($cart_item['data']) && $cart_item['data'] instanceof WC_Product ? $cart_item['data'] : null;
			if (!$product instanceof WC_Product) {
				continue;
			}

			$limits = self::get_product_min_max_quantity_limits($product);
			$min = isset($limits['min']) ? max(0, (int) $limits['min']) : 0;
			$max = isset($limits['max']) ? max(0, (int) $limits['max']) : 0;
			if ($min <= 0 && $max <= 0) {
				continue;
			}

			$scope_product_id = isset($limits['scope_product_id']) ? absint($limits['scope_product_id']) : 0;
			$scope_variation_id = isset($limits['scope_variation_id']) ? absint($limits['scope_variation_id']) : 0;
			$scope_key = $scope_variation_id > 0
				? 'variation:' . (string) $scope_variation_id
				: 'product:' . (string) $scope_product_id;

			if (!isset($grouped_limits[$scope_key])) {
				$grouped_limits[$scope_key] = [
					'qty' => 0,
					'min' => $min,
					'max' => $max,
					'label' => self::get_product_label_for_min_max_notice($product),
				];
			}

			$grouped_limits[$scope_key]['qty'] += isset($cart_item['quantity']) ? max(0, (int) $cart_item['quantity']) : 0;
			$grouped_limits[$scope_key]['min'] = $min > 0 ? $min : $grouped_limits[$scope_key]['min'];
			$grouped_limits[$scope_key]['max'] = $max > 0 ? $max : $grouped_limits[$scope_key]['max'];
		}

		foreach ($grouped_limits as $group) {
			$qty = isset($group['qty']) ? max(0, (int) $group['qty']) : 0;
			$min = isset($group['min']) ? max(0, (int) $group['min']) : 0;
			$max = isset($group['max']) ? max(0, (int) $group['max']) : 0;
			$label = isset($group['label']) ? (string) $group['label'] : __('This product', 'user-manager');

			if ($min > 0 && $qty > 0 && $qty < $min) {
				wc_add_notice(
					sprintf(
						/* translators: 1: product name, 2: minimum quantity, 3: current quantity */
						__('"%1$s" requires a minimum quantity of %2$d. Current quantity: %3$d.', 'user-manager'),
						$label,
						$min,
						$qty
					),
					'error'
				);
			}

			if ($max > 0 && $qty > $max) {
				wc_add_notice(
					sprintf(
						/* translators: 1: product name, 2: maximum quantity, 3: current quantity */
						__('"%1$s" allows a maximum quantity of %2$d. Current quantity: %3$d.', 'user-manager'),
						$label,
						$max,
						$qty
					),
					'error'
				);
			}
		}
	}

	/**
	 * Resolve quantity limits for a product/variation.
	 *
	 * @return array{min:int,max:int,scope_product_id:int,scope_variation_id:int}
	 */
	private static function get_product_min_max_quantity_limits(WC_Product $product): array {
		$product_id = (int) $product->get_id();
		$parent_id = method_exists($product, 'get_parent_id') ? (int) $product->get_parent_id() : 0;

		$scope_product_id = $product_id;
		$scope_variation_id = 0;

		$min = self::get_quantity_limit_meta_value($product_id, self::$um_min_qty_meta_key);
		$max = self::get_quantity_limit_meta_value($product_id, self::$um_max_qty_meta_key);

		if ($product->is_type('variation') && $parent_id > 0) {
			$parent_min = self::get_quantity_limit_meta_value($parent_id, self::$um_min_qty_meta_key);
			$parent_max = self::get_quantity_limit_meta_value($parent_id, self::$um_max_qty_meta_key);

			// Variation-level values (if present) take precedence; otherwise inherit parent.
			if ($min <= 0) {
				$min = $parent_min;
			}
			if ($max <= 0) {
				$max = $parent_max;
			}

			// Parent-level limits apply across all variations of that parent.
			if (($parent_min > 0 || $parent_max > 0) && self::get_quantity_limit_meta_value($product_id, self::$um_min_qty_meta_key) <= 0 && self::get_quantity_limit_meta_value($product_id, self::$um_max_qty_meta_key) <= 0) {
				$scope_product_id = $parent_id;
				$scope_variation_id = 0;
			} else {
				$scope_product_id = $parent_id;
				$scope_variation_id = $product_id;
			}
		}

		if ($min > 0 && $max > 0 && $max < $min) {
			$max = $min;
		}

		return [
			'min' => $min,
			'max' => $max,
			'scope_product_id' => $scope_product_id,
			'scope_variation_id' => $scope_variation_id,
		];
	}

	/**
	 * Get normalized integer quantity limit from product meta.
	 */
	private static function get_quantity_limit_meta_value(int $product_id, string $meta_key): int {
		if ($product_id <= 0 || $meta_key === '') {
			return 0;
		}
		$raw = get_post_meta($product_id, $meta_key, true);
		return max(0, absint($raw));
	}

	/**
	 * Sum cart quantity for a scoped product/variation target.
	 */
	private static function get_cart_quantity_for_scope(int $scope_product_id, int $scope_variation_id = 0): int {
		if (!function_exists('WC') || !WC()->cart || $scope_product_id <= 0) {
			return 0;
		}

		$total = 0;
		foreach (WC()->cart->get_cart() as $cart_item) {
			$product_id = isset($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
			$variation_id = isset($cart_item['variation_id']) ? absint($cart_item['variation_id']) : 0;
			$qty = isset($cart_item['quantity']) ? max(0, (int) $cart_item['quantity']) : 0;

			if ($scope_variation_id > 0) {
				if ($variation_id === $scope_variation_id) {
					$total += $qty;
				}
				continue;
			}

			if ($product_id === $scope_product_id) {
				$total += $qty;
			}
		}

		return $total;
	}

	/**
	 * Get product label for notices.
	 */
	private static function get_product_label_for_min_max_notice(WC_Product $product): string {
		$name = trim((string) $product->get_name());
		if ($name !== '') {
			return $name;
		}
		return sprintf(__('Product #%d', 'user-manager'), (int) $product->get_id());
	}
}
