<?php
/**
 * Add to Cart Variation Table feature helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Add_To_Cart_Variation_Table_Trait {
	/**
	 * Whether the variation table actually rendered this request.
	 *
	 * @var bool
	 */
	private static bool $add_to_cart_variation_table_rendered = false;

	/**
	 * Runtime trace events for front-end debugging.
	 *
	 * @var array<int,string>
	 */
	private static array $add_to_cart_variation_table_trace_events = [];

	/**
	 * Register front-end render hooks for the variation table based on settings.
	 */
	public static function register_add_to_cart_variation_table_render_hooks(): void {
		$map = self::get_add_to_cart_variation_table_hook_map();
		$selected = self::get_add_to_cart_variation_table_selected_hook_key();
		$fallback_hooks = ['single_product_summary', 'after_single_product_summary'];

		if ($selected === 'auto') {
			foreach ($map as $hook_config) {
				add_action($hook_config['hook'], [__CLASS__, 'maybe_render_add_to_cart_variation_table'], (int) $hook_config['priority']);
				self::append_add_to_cart_variation_table_trace(
					sprintf('Registered render hook: %s @ %d', $hook_config['hook'], (int) $hook_config['priority'])
				);
			}
			return;
		}

		if (!isset($map[$selected])) {
			$selected = 'after_add_to_cart_form';
		}

		$hook_config = $map[$selected];
		add_action($hook_config['hook'], [__CLASS__, 'maybe_render_add_to_cart_variation_table'], (int) $hook_config['priority']);
		self::append_add_to_cart_variation_table_trace(
			sprintf('Registered selected render hook: %s @ %d', $hook_config['hook'], (int) $hook_config['priority'])
		);

		// Safety fallback: some themes override the selected hook location.
		foreach ($fallback_hooks as $fallback_key) {
			if (!isset($map[$fallback_key]) || $fallback_key === $selected) {
				continue;
			}
			$fallback = $map[$fallback_key];
			add_action($fallback['hook'], [__CLASS__, 'maybe_render_add_to_cart_variation_table'], (int) $fallback['priority']);
			self::append_add_to_cart_variation_table_trace(
				sprintf('Registered fallback render hook: %s @ %d', $fallback['hook'], (int) $fallback['priority'])
			);
		}
	}

	/**
	 * Hook map for render location options.
	 *
	 * @return array<string,array{hook:string,priority:int}>
	 */
	private static function get_add_to_cart_variation_table_hook_map(): array {
		return [
			'after_add_to_cart_form' => [
				'hook' => 'woocommerce_after_add_to_cart_form',
				'priority' => 20,
			],
			'single_product_summary' => [
				'hook' => 'woocommerce_single_product_summary',
				'priority' => 35,
			],
			'after_single_product_summary' => [
				'hook' => 'woocommerce_after_single_product_summary',
				'priority' => 5,
			],
			'before_add_to_cart_form' => [
				'hook' => 'woocommerce_before_add_to_cart_form',
				'priority' => 30,
			],
		];
	}

	/**
	 * Get selected render hook key from settings.
	 */
	private static function get_add_to_cart_variation_table_selected_hook_key(): string {
		$settings = User_Manager_Core::get_settings();
		$selected = isset($settings['add_to_cart_variation_table_hook']) ? sanitize_key((string) $settings['add_to_cart_variation_table_hook']) : 'auto';
		$allowed = ['auto', 'after_add_to_cart_form', 'single_product_summary', 'after_single_product_summary', 'before_add_to_cart_form'];

		return in_array($selected, $allowed, true) ? $selected : 'auto';
	}

	/**
	 * Render alternate variation quantity table under the default add-to-cart form.
	 */
	public static function maybe_render_add_to_cart_variation_table(): void {
		self::append_add_to_cart_variation_table_trace('Render callback entered.');
		if (!function_exists('is_product') || !is_product()) {
			self::append_add_to_cart_variation_table_trace('Skip: not a single product page.');
			return;
		}
		if (!function_exists('wc_get_product')) {
			self::append_add_to_cart_variation_table_trace('Skip: wc_get_product() unavailable.');
			return;
		}

		global $product;
		if (!$product instanceof WC_Product && function_exists('get_queried_object_id')) {
			$fallback_product_id = (int) get_queried_object_id();
			$fallback_product = $fallback_product_id > 0 ? wc_get_product($fallback_product_id) : null;
			if ($fallback_product instanceof WC_Product) {
				$product = $fallback_product;
				self::append_add_to_cart_variation_table_trace(sprintf('Global $product unavailable; using queried product #%d.', $fallback_product_id));
			}
		}
		if (!$product instanceof WC_Product || !$product->is_type('variable')) {
			self::append_add_to_cart_variation_table_trace('Skip: current product is not a variable product.');
			return;
		}
		if (!$product->is_purchasable()) {
			self::append_add_to_cart_variation_table_trace('Skip: variable product is not purchasable.');
			return;
		}
		$settings = User_Manager_Core::get_settings();
		$allowed_category_ids = self::get_add_to_cart_variation_table_allowed_category_ids($settings);
		if (!empty($allowed_category_ids)) {
			if (!function_exists('has_term')) {
				self::append_add_to_cart_variation_table_trace('Skip: has_term() unavailable for category filtering.');
				return;
			}
			$in_allowed_category = has_term($allowed_category_ids, 'product_cat', $product->get_id());
			if (is_wp_error($in_allowed_category) || !$in_allowed_category) {
				self::append_add_to_cart_variation_table_trace('Skip: product is not in selected variation-table categories.');
				return;
			}
		}
		static $rendered_product_ids = [];
		$product_id = (int) $product->get_id();
		if (isset($rendered_product_ids[$product_id])) {
			self::append_add_to_cart_variation_table_trace('Skip: variation table already rendered for this product in current request.');
			return;
		}
		$rendered_product_ids[$product_id] = true;

		$available_variations = $product->get_available_variations();
		if (empty($available_variations) || !is_array($available_variations)) {
			self::append_add_to_cart_variation_table_trace('Skip: no available variations found.');
			return;
		}
		$prefix_variation_labels = !empty($settings['add_to_cart_variation_table_prefix_labels']);

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
				'label'       => wc_get_formatted_variation($variation, true, $prefix_variation_labels, false),
				'unit_price'  => function_exists('wc_get_price_to_display') ? (float) wc_get_price_to_display($variation) : (float) $variation->get_price(),
				'status'      => $is_in_stock ? __('In stock', 'user-manager') : __('Out of stock', 'user-manager'),
				'disabled'    => $is_row_disabled,
				'max'         => $max_qty,
			];
		}

		if (empty($rows)) {
			self::append_add_to_cart_variation_table_trace('Skip: no renderable variation rows after filtering.');
			return;
		}
		self::$add_to_cart_variation_table_rendered = true;
		self::append_add_to_cart_variation_table_trace(sprintf('Rendering table for product #%d with %d variation rows.', $product_id, count($rows)));

		$show_price_column = !empty($settings['add_to_cart_variation_table_show_price_column']);
		$hide_header_row = !empty($settings['add_to_cart_variation_table_hide_header_row']);
		$header_variation_label = isset($settings['add_to_cart_variation_table_header_variation_label']) ? trim((string) $settings['add_to_cart_variation_table_header_variation_label']) : '';
		$header_qty_label = isset($settings['add_to_cart_variation_table_header_qty_label']) ? trim((string) $settings['add_to_cart_variation_table_header_qty_label']) : '';
		if ($header_variation_label === '') {
			$header_variation_label = __('Variation', 'user-manager');
		}
		if ($header_qty_label === '') {
			$header_qty_label = __('Qty', 'user-manager');
		}
		$table_text_above = isset($settings['add_to_cart_variation_table_text_above']) ? (string) $settings['add_to_cart_variation_table_text_above'] : '';
		$table_text_below = isset($settings['add_to_cart_variation_table_text_below']) ? (string) $settings['add_to_cart_variation_table_text_below'] : '';
		$button_text = isset($settings['add_to_cart_variation_table_button_text']) ? trim((string) $settings['add_to_cart_variation_table_button_text']) : '';
		if ($button_text === '') {
			$button_text = __('Add to Cart', 'user-manager');
		}
		$currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$';
		$currency_position = (string) get_option('woocommerce_currency_pos', 'left');
		$currency_decimals = function_exists('wc_get_price_decimals') ? (int) wc_get_price_decimals() : 2;
		$currency_decimal_sep = function_exists('wc_get_price_decimal_separator') ? wc_get_price_decimal_separator() : '.';
		$currency_thousand_sep = function_exists('wc_get_price_thousand_separator') ? wc_get_price_thousand_separator() : ',';
		$debug_enabled = self::is_add_to_cart_variation_table_debug_enabled();
		$debug_payload = self::consume_add_to_cart_variation_table_debug_payload();
		?>
		<div
			class="um-add-to-cart-variation-table"
			style="margin-top:16px;"
			data-currency-symbol="<?php echo esc_attr($currency_symbol); ?>"
			data-currency-position="<?php echo esc_attr($currency_position); ?>"
			data-currency-decimals="<?php echo esc_attr((string) $currency_decimals); ?>"
			data-currency-decimal-sep="<?php echo esc_attr($currency_decimal_sep); ?>"
			data-currency-thousand-sep="<?php echo esc_attr($currency_thousand_sep); ?>"
		>
			<?php if (trim($table_text_above) !== '') : ?>
				<div class="um-add-to-cart-variation-table-custom-text um-add-to-cart-variation-table-custom-text-above" style="margin:0 0 12px;">
					<?php echo wp_kses_post($table_text_above); ?>
				</div>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url($product->get_permalink()); ?>" class="um-add-to-cart-variation-table-form">
				<?php wp_nonce_field('um_add_to_cart_variation_table_submit', 'um_add_to_cart_variation_table_nonce'); ?>
				<input type="hidden" name="um_add_to_cart_variation_table_submit" value="1" />
				<input type="hidden" name="um_add_to_cart_variation_table_product_id" value="<?php echo esc_attr((string) $product_id); ?>" />
				<table class="shop_table shop_table_responsive um-add-to-cart-variation-vertical-table" style="margin-bottom:12px;">
					<?php if (!$hide_header_row) : ?>
						<thead>
							<tr>
								<th><?php echo esc_html($header_variation_label); ?></th>
								<th style="width:120px;"><?php echo esc_html($header_qty_label); ?></th>
								<?php if ($show_price_column) : ?>
									<th style="width:160px;"><?php esc_html_e('Price', 'user-manager'); ?></th>
								<?php endif; ?>
							</tr>
						</thead>
					<?php endif; ?>
					<tbody>
						<?php foreach ($rows as $row) : ?>
							<?php
							$display_label = $row['label'] !== '' ? $row['label'] : ('#' . (string) $row['id']);
							if ($row['disabled']) {
								$display_label .= ' (' . $row['status'] . ')';
							}
							?>
							<tr>
								<td><?php echo esc_html($display_label); ?></td>
								<td>
									<input
										type="number"
										min="0"
										step="1"
										name="um_add_to_cart_variation_qty[<?php echo esc_attr((string) $row['id']); ?>]"
										value="0"
										class="input-text qty text"
										style="max-width:90px;"
										data-unit-price="<?php echo esc_attr(number_format((float) $row['unit_price'], 6, '.', '')); ?>"
										<?php echo $row['disabled'] ? 'disabled' : ''; ?>
										<?php echo $row['max'] !== '' ? 'max="' . esc_attr($row['max']) . '"' : ''; ?>
									/>
								</td>
								<?php if ($show_price_column) : ?>
									<td><?php echo wp_kses_post(wc_price((float) $row['unit_price'])); ?></td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th><?php esc_html_e('Totals', 'user-manager'); ?></th>
							<th><span class="um-add-to-cart-variation-table-total">0</span></th>
							<?php if ($show_price_column) : ?>
								<th><span class="um-add-to-cart-variation-table-total-amount"><?php echo wp_kses_post(wc_price(0)); ?></span></th>
							<?php endif; ?>
						</tr>
					</tfoot>
				</table>
				<button type="submit" class="button alt"><?php echo esc_html($button_text); ?></button>
			</form>
			<?php if (trim($table_text_below) !== '') : ?>
				<div class="um-add-to-cart-variation-table-custom-text um-add-to-cart-variation-table-custom-text-below" style="margin:12px 0 0;">
					<?php echo wp_kses_post($table_text_below); ?>
				</div>
			<?php endif; ?>
			<script>
				(function() {
					var root = document.currentScript ? document.currentScript.closest('.um-add-to-cart-variation-table') : null;
					if (!root) return;
					var qtyInputs = root.querySelectorAll('input[name^="um_add_to_cart_variation_qty["]');
					var totalQtyNode = root.querySelector('.um-add-to-cart-variation-table-total');
					var totalAmountNode = root.querySelector('.um-add-to-cart-variation-table-total-amount');
					if (!totalQtyNode) return;
					var currencySymbol = root.getAttribute('data-currency-symbol') || '$';
					var currencyPosition = root.getAttribute('data-currency-position') || 'left';
					var currencyDecimals = parseInt(root.getAttribute('data-currency-decimals') || '2', 10);
					if (isNaN(currencyDecimals) || currencyDecimals < 0) {
						currencyDecimals = 2;
					}
					var currencyDecimalSep = root.getAttribute('data-currency-decimal-sep') || '.';
					var currencyThousandSep = root.getAttribute('data-currency-thousand-sep') || ',';
					var formatNumber = function(value, decimals, decimalSep, thousandSep) {
						var fixed = Number(value).toFixed(decimals);
						var parts = fixed.split('.');
						var integerPart = parts[0];
						var decimalPart = parts.length > 1 ? parts[1] : '';
						integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSep);
						return decimals > 0 ? integerPart + decimalSep + decimalPart : integerPart;
					};
					var formatMoney = function(value) {
						var amount = formatNumber(value, currencyDecimals, currencyDecimalSep, currencyThousandSep);
						switch (currencyPosition) {
							case 'right':
								return amount + currencySymbol;
							case 'left_space':
								return currencySymbol + ' ' + amount;
							case 'right_space':
								return amount + ' ' + currencySymbol;
							case 'left':
							default:
								return currencySymbol + amount;
						}
					};
					var recalc = function() {
						var totalQty = 0;
						var totalAmount = 0;
						qtyInputs.forEach(function(input) {
							if (input.disabled) return;
							var value = parseInt(input.value || '0', 10);
							if (!isNaN(value) && value > 0) {
								totalQty += value;
								var unitPrice = parseFloat(input.getAttribute('data-unit-price') || '0');
								if (!isNaN(unitPrice) && unitPrice > 0) {
									totalAmount += (value * unitPrice);
								}
							}
						});
						totalQtyNode.textContent = String(totalQty);
						if (totalAmountNode) {
							totalAmountNode.textContent = formatMoney(totalAmount);
						}
					};
					qtyInputs.forEach(function(input) {
						input.addEventListener('input', recalc);
						input.addEventListener('change', recalc);
					});
					recalc();
				})();
			</script>
			<?php if ($debug_enabled) : ?>
				<div class="woocommerce-info" style="margin-top:12px;">
					<strong><?php esc_html_e('Add to Cart Variation Table Debug', 'user-manager'); ?></strong>
					<?php if (!empty($debug_payload)) : ?>
						<pre style="white-space:pre-wrap; margin:8px 0 0;"><?php echo esc_html(wp_json_encode($debug_payload, JSON_PRETTY_PRINT)); ?></pre>
					<?php else : ?>
						<p style="margin:8px 0 0;"><?php esc_html_e('Submit Add to Cart to see debug details for each variation.', 'user-manager'); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render front-end runtime trace panel with URL parameter support.
	 */
	public static function maybe_render_add_to_cart_variation_table_trace_panel(): void {
		if (!self::is_add_to_cart_variation_table_trace_enabled()) {
			return;
		}
		$selected = self::get_add_to_cart_variation_table_selected_hook_key();
		$map = self::get_add_to_cart_variation_table_hook_map();
		$is_product = function_exists('is_product') && is_product();
		$product = null;
		$product_type = '';
		$product_id = 0;
		$variation_count = 0;
		if ($is_product && function_exists('wc_get_product')) {
			$product_id = (int) get_queried_object_id();
			$product = $product_id > 0 ? wc_get_product($product_id) : null;
			if ($product instanceof WC_Product) {
				$product_type = (string) $product->get_type();
				if ($product->is_type('variable')) {
					$available_variations = $product->get_available_variations();
					$variation_count = is_array($available_variations) ? count($available_variations) : 0;
				}
			}
		}
		?>
		<div style="position:fixed; right:12px; bottom:12px; z-index:999999; width:420px; max-width:calc(100vw - 24px); max-height:60vh; overflow:auto; background:#111; color:#fff; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.35); padding:12px;">
			<strong style="display:block; margin-bottom:8px;"><?php esc_html_e('Add to Cart Variation Table Trace', 'user-manager'); ?></strong>
			<div style="font-size:12px; line-height:1.45;">
				<div><strong><?php esc_html_e('Selected hook setting:', 'user-manager'); ?></strong> <?php echo esc_html($selected); ?></div>
				<div><strong><?php esc_html_e('is_product():', 'user-manager'); ?></strong> <?php echo esc_html($is_product ? 'yes' : 'no'); ?></div>
				<div><strong><?php esc_html_e('Product ID:', 'user-manager'); ?></strong> <?php echo esc_html((string) $product_id); ?></div>
				<div><strong><?php esc_html_e('Product type:', 'user-manager'); ?></strong> <?php echo esc_html($product_type !== '' ? $product_type : 'n/a'); ?></div>
				<div><strong><?php esc_html_e('Variation count:', 'user-manager'); ?></strong> <?php echo esc_html((string) $variation_count); ?></div>
				<div><strong><?php esc_html_e('Rendered this request:', 'user-manager'); ?></strong> <?php echo esc_html(self::$add_to_cart_variation_table_rendered ? 'yes' : 'no'); ?></div>
				<div style="margin-top:8px;">
					<strong><?php esc_html_e('Hook registration state:', 'user-manager'); ?></strong>
					<ul style="margin:4px 0 0 18px;">
						<?php foreach ($map as $key => $config) : ?>
							<?php $priority = has_action($config['hook'], [__CLASS__, 'maybe_render_add_to_cart_variation_table']); ?>
							<li>
								<?php echo esc_html($key . ' => ' . $config['hook']); ?>
								<?php echo esc_html($priority !== false ? (' (registered @ ' . (string) $priority . ')') : ' (not registered)'); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div style="margin-top:8px;">
					<strong><?php esc_html_e('Trace events:', 'user-manager'); ?></strong>
					<ul style="margin:4px 0 0 18px;">
						<?php if (!empty(self::$add_to_cart_variation_table_trace_events)) : ?>
							<?php foreach (self::$add_to_cart_variation_table_trace_events as $event_line) : ?>
								<li><?php echo esc_html($event_line); ?></li>
							<?php endforeach; ?>
						<?php else : ?>
							<li><?php esc_html_e('No events recorded yet. This indicates render callback did not execute on this request.', 'user-manager'); ?></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * URL/front-end trigger for variation-table trace output.
	 */
	private static function is_add_to_cart_variation_table_trace_enabled(): bool {
		if (!function_exists('is_user_logged_in') || !function_exists('current_user_can')) {
			return false;
		}
		if (!is_user_logged_in() || !current_user_can('manage_options')) {
			return false;
		}
		if (!isset($_GET['um_variation_table_trace'])) {
			return false;
		}
		$raw = sanitize_text_field(wp_unslash($_GET['um_variation_table_trace']));
		return in_array(strtolower($raw), ['1', 'true', 'yes', 'on'], true);
	}

	/**
	 * Append one trace line for runtime diagnostics.
	 */
	private static function append_add_to_cart_variation_table_trace(string $line): void {
		if (!self::is_add_to_cart_variation_table_trace_enabled()) {
			return;
		}
		self::$add_to_cart_variation_table_trace_events[] = $line;
	}

	/**
	 * Render "Empty Cart" action button on cart form actions.
	 */
	public static function maybe_render_empty_cart_button_on_cart_screen(): void {
		if (is_admin() || wp_doing_ajax()) {
			return;
		}
		if (!function_exists('is_cart') || !is_cart()) {
			return;
		}
		$settings = User_Manager_Core::get_settings();
		if (empty($settings['add_to_cart_variation_table_empty_cart_button_on_cart'])) {
			return;
		}
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}
		if (WC()->cart->is_empty()) {
			return;
		}
		wp_nonce_field('um_empty_cart_action', 'um_empty_cart_nonce');
		?>
		<button
			type="submit"
			class="button"
			name="um_empty_cart_submit"
			value="1"
			formnovalidate
			onclick="return window.confirm('<?php echo esc_js(__('Are you sure you want to empty your cart?', 'user-manager')); ?>');"
		>
			<?php esc_html_e('Empty cart', 'user-manager'); ?>
		</button>
		<?php
	}

	/**
	 * Handle empty cart button submission.
	 */
	public static function maybe_handle_empty_cart_button_submission(): void {
		if (empty($_POST['um_empty_cart_submit'])) {
			return;
		}
		if (is_admin() || wp_doing_ajax()) {
			return;
		}
		if (!function_exists('is_cart') || !is_cart()) {
			return;
		}
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}
		if (empty($_POST['um_empty_cart_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['um_empty_cart_nonce'])), 'um_empty_cart_action')) {
			wc_add_notice(__('Security check failed. Please refresh and try again.', 'user-manager'), 'error');
			wp_safe_redirect(wc_get_cart_url());
			exit;
		}
		WC()->cart->empty_cart();
		wc_add_notice(__('Cart emptied.', 'user-manager'), 'success');
		wp_safe_redirect(wc_get_cart_url());
		exit;
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
		$debug_enabled = self::is_add_to_cart_variation_table_debug_enabled();
		$debug_rows = [];
		if (empty($_POST['um_add_to_cart_variation_table_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['um_add_to_cart_variation_table_nonce'])), 'um_add_to_cart_variation_table_submit')) {
			wc_add_notice(__('Security check failed. Please try again.', 'user-manager'), 'error');
			self::redirect_add_to_cart_variation_table_request(0, self::build_add_to_cart_variation_table_debug_query_args($debug_enabled, [
				'error' => 'invalid_nonce',
			]));
		}

		$product_id = isset($_POST['um_add_to_cart_variation_table_product_id']) ? absint($_POST['um_add_to_cart_variation_table_product_id']) : 0;
		$product = $product_id > 0 ? wc_get_product($product_id) : null;
		if (!$product instanceof WC_Product || !$product->is_type('variable')) {
			wc_add_notice(__('Invalid variable product for bulk variation add to cart.', 'user-manager'), 'error');
			self::redirect_add_to_cart_variation_table_request($product_id, self::build_add_to_cart_variation_table_debug_query_args($debug_enabled, [
				'error' => 'invalid_product',
				'product_id' => $product_id,
			]));
		}

		if (!WC()->cart && function_exists('wc_load_cart')) {
			wc_load_cart();
		}
		if (!WC()->cart) {
			wc_add_notice(__('Cart is unavailable right now. Please try again.', 'user-manager'), 'error');
			self::redirect_add_to_cart_variation_table_request($product_id, self::build_add_to_cart_variation_table_debug_query_args($debug_enabled, [
				'error' => 'cart_unavailable',
				'product_id' => $product_id,
			]));
		}

		$qty_map = [];
		if (isset($_POST['um_add_to_cart_variation_qty']) && is_array($_POST['um_add_to_cart_variation_qty'])) {
			$qty_map = (array) wp_unslash($_POST['um_add_to_cart_variation_qty']);
		}

		$rows_selected = 0;
		$items_added = 0;
		$error_messages = [];
		$history_rows = [];
		$current_user = function_exists('wp_get_current_user') ? wp_get_current_user() : null;
		$current_user_id = ($current_user instanceof WP_User) ? (int) $current_user->ID : 0;
		$current_user_email = ($current_user instanceof WP_User && !empty($current_user->user_email)) ? (string) $current_user->user_email : '';
		$current_user_login = ($current_user instanceof WP_User && !empty($current_user->user_login)) ? (string) $current_user->user_login : '';

		foreach ($qty_map as $variation_id_raw => $qty_raw) {
			$variation_id = absint((string) $variation_id_raw);
			if ($variation_id <= 0) {
				continue;
			}

			$qty = is_numeric($qty_raw) ? (int) floor((float) $qty_raw) : 0;
			if ($qty <= 0) {
				$debug_rows[] = [
					'variation_id' => $variation_id,
					'status'       => 'skipped',
					'note'         => 'Quantity is zero/empty.',
				];
				continue;
			}

			$rows_selected++;

			$variation = wc_get_product($variation_id);
			if (!$variation instanceof WC_Product_Variation || $variation->get_parent_id() !== $product_id) {
				$error_messages[] = sprintf(__('Variation #%d is not valid for this product.', 'user-manager'), $variation_id);
				$history_rows[] = [
					'variation_id' => $variation_id,
					'label'        => '#' . (string) $variation_id,
					'qty'          => $qty,
					'status'       => 'error',
					'note'         => 'Variation does not belong to this product.',
				];
				$debug_rows[] = [
					'variation_id' => $variation_id,
					'qty'          => $qty,
					'status'       => 'error',
					'note'         => 'Variation does not belong to this product.',
				];
				continue;
			}

			if (!$variation->is_purchasable() || !$variation->is_in_stock()) {
				$error_messages[] = sprintf(__('Variation #%d is currently unavailable.', 'user-manager'), $variation_id);
				$history_rows[] = [
					'variation_id' => $variation_id,
					'label'        => wc_get_formatted_variation($variation, true, true, false),
					'qty'          => $qty,
					'status'       => 'error',
					'note'         => 'Variation unavailable.',
				];
				$debug_rows[] = [
					'variation_id' => $variation_id,
					'qty'          => $qty,
					'status'       => 'error',
					'note'         => 'Variation unavailable.',
				];
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
				$history_rows[] = [
					'variation_id' => $variation_id,
					'label'        => wc_get_formatted_variation($variation, true, true, false),
					'qty'          => 0,
					'status'       => 'error',
					'note'         => 'No stock after stock checks.',
				];
				$debug_rows[] = [
					'variation_id' => $variation_id,
					'status'       => 'error',
					'note'         => 'No stock after stock checks.',
				];
				continue;
			}

			$added = WC()->cart->add_to_cart(
				$product_id,
				$qty,
				$variation_id,
				$variation->get_variation_attributes()
			);

			$variation_label = wc_get_formatted_variation($variation, true, true, false);
			if ($added) {
				$items_added += $qty;
				$history_rows[] = [
					'variation_id' => $variation_id,
					'label'        => $variation_label,
					'qty'          => $qty,
					'status'       => 'added',
					'note'         => 'Added to cart.',
				];
				$debug_rows[] = [
					'variation_id' => $variation_id,
					'qty'          => $qty,
					'status'       => 'added',
					'note'         => 'Added to cart.',
				];
			} else {
				$error_messages[] = sprintf(__('Variation #%d could not be added to cart.', 'user-manager'), $variation_id);
				$history_rows[] = [
					'variation_id' => $variation_id,
					'label'        => $variation_label,
					'qty'          => $qty,
					'status'       => 'error',
					'note'         => 'WC()->cart->add_to_cart returned false.',
				];
				$debug_rows[] = [
					'variation_id' => $variation_id,
					'qty'          => $qty,
					'status'       => 'error',
					'note'         => 'WC()->cart->add_to_cart returned false.',
				];
			}
		}

		if ($rows_selected === 0) {
			wc_add_notice(__('Enter a quantity greater than 0 for at least one variation.', 'user-manager'), 'notice');
			self::redirect_add_to_cart_variation_table_request($product_id, self::build_add_to_cart_variation_table_debug_query_args($debug_enabled, [
				'product_id'    => $product_id,
				'rows_selected' => 0,
				'items_added'   => 0,
				'errors'        => $error_messages,
				'rows'          => $debug_rows,
			]));
		}

		if ($items_added > 0) {
			wc_add_notice(
				sprintf(
					/* translators: %d: number of items added */
					_n('%d variation item added to cart.', '%d variation items added to cart.', $items_added, 'user-manager'),
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

		if ($rows_selected > 0) {
			$who = $current_user_email !== '' ? $current_user_email : ($current_user_login !== '' ? $current_user_login : __('Guest', 'user-manager'));
			self::record_add_to_cart_variation_table_history([
				'timestamp'    => current_time('mysql'),
				'user_id'      => $current_user_id,
				'who'          => $who,
				'product_id'   => $product_id,
				'product_name' => $product->get_name(),
				'rows_selected'=> $rows_selected,
				'items_added'  => $items_added,
				'variations'   => $history_rows,
			]);
		}

		self::redirect_add_to_cart_variation_table_request($product_id, self::build_add_to_cart_variation_table_debug_query_args($debug_enabled, [
			'product_id'    => $product_id,
			'rows_selected' => $rows_selected,
			'items_added'   => $items_added,
			'errors'        => $error_messages,
			'rows'          => $debug_rows,
		]));
	}

	/**
	 * Redirect after processing to prevent duplicate submissions on refresh.
	 */
	private static function redirect_add_to_cart_variation_table_request(int $product_id, array $query_args = []): void {
		$redirect_url = wp_get_referer();
		if (!is_string($redirect_url) || $redirect_url === '') {
			$redirect_url = $product_id > 0 ? get_permalink($product_id) : home_url('/');
		}
		if (!is_string($redirect_url) || $redirect_url === '') {
			$redirect_url = home_url('/');
		}
		$redirect_url = remove_query_arg(
			[
				'um_add_to_cart_variation_table_submit',
				'um_add_to_cart_variation_table_nonce',
				'um_add_to_cart_variation_table_debug',
				'um_add_to_cart_variation_table_debug_token',
			],
			$redirect_url
		);
		if (!empty($query_args)) {
			$redirect_url = add_query_arg($query_args, $redirect_url);
		}
		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	 * Whether debug mode is enabled for this feature.
	 */
	private static function is_add_to_cart_variation_table_debug_enabled(): bool {
		$settings = User_Manager_Core::get_settings();
		if (!empty($settings['add_to_cart_variation_table_debug_mode'])) {
			return true;
		}
		if (!isset($_GET['um_add_to_cart_variation_table_debug'])) {
			return false;
		}
		$raw = sanitize_text_field(wp_unslash($_GET['um_add_to_cart_variation_table_debug']));
		return in_array(strtolower($raw), ['1', 'true', 'yes', 'on'], true);
	}

	/**
	 * Persist debug payload and return redirect query args.
	 *
	 * @param bool              $enabled Debug enabled.
	 * @param array<string,mixed> $payload Debug payload.
	 * @return array<string,string>
	 */
	private static function build_add_to_cart_variation_table_debug_query_args(bool $enabled, array $payload): array {
		if (!$enabled) {
			return [];
		}
		$token = wp_generate_password(20, false, false);
		set_transient('um_variation_table_debug_' . $token, $payload, MINUTE_IN_SECONDS * 5);

		return [
			'um_add_to_cart_variation_table_debug' => '1',
			'um_add_to_cart_variation_table_debug_token' => $token,
		];
	}

	/**
	 * Load debug payload from transient, then clear it.
	 *
	 * @return array<string,mixed>
	 */
	private static function consume_add_to_cart_variation_table_debug_payload(): array {
		if (!isset($_GET['um_add_to_cart_variation_table_debug_token'])) {
			return [];
		}
		$token = sanitize_text_field(wp_unslash($_GET['um_add_to_cart_variation_table_debug_token']));
		if ($token === '') {
			return [];
		}
		$key = 'um_variation_table_debug_' . $token;
		$data = get_transient($key);
		delete_transient($key);

		return is_array($data) ? $data : [];
	}

	/**
	 * Parse and sanitize selected product categories for the variation table filter.
	 *
	 * @param array<string,mixed> $settings Settings array.
	 * @return array<int,int>
	 */
	private static function get_add_to_cart_variation_table_allowed_category_ids(array $settings): array {
		$raw = isset($settings['add_to_cart_variation_table_category_ids']) && is_array($settings['add_to_cart_variation_table_category_ids'])
			? $settings['add_to_cart_variation_table_category_ids']
			: [];

		return array_values(array_unique(array_filter(array_map('absint', $raw))));
	}

	/**
	 * Persist one history row for Add to Cart Variation Table runs.
	 *
	 * @param array<string,mixed> $entry History entry payload.
	 */
	private static function record_add_to_cart_variation_table_history(array $entry): void {
		$history = get_option('add_to_cart_variation_table_history', []);
		if (!is_array($history)) {
			$history = [];
		}
		array_unshift($history, $entry);
		if (count($history) > 200) {
			$history = array_slice($history, 0, 200);
		}
		update_option('add_to_cart_variation_table_history', $history);
	}

	/**
	 * Optionally hide the native variable-product add-to-cart form.
	 */
	public static function maybe_hide_default_add_to_cart_variation_form(): void {
		if (is_admin() || wp_doing_ajax()) {
			return;
		}
		if (!function_exists('is_product') || !is_product() || !function_exists('wc_get_product')) {
			return;
		}

		$settings = User_Manager_Core::get_settings();
		if (empty($settings['add_to_cart_variation_table_enabled']) || empty($settings['add_to_cart_variation_table_hide_default_form'])) {
			return;
		}

		$product_id = get_queried_object_id();
		$product = $product_id > 0 ? wc_get_product($product_id) : null;
		if (!$product instanceof WC_Product || !$product->is_type('variable')) {
			return;
		}
		?>
		<style id="um-hide-default-variable-add-to-cart">
			.single-product form.variations_form.cart {
				display: none !important;
			}
		</style>
		<?php
	}
}

