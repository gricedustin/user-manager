<?php
/**
 * Add-on card: Add to Cart Variation Table.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Add_To_Cart_Variation_Table {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['add_to_cart_variation_table_enabled']);
		$selected_hook = isset($settings['add_to_cart_variation_table_hook']) ? sanitize_key((string) $settings['add_to_cart_variation_table_hook']) : 'auto';
		$hook_options = [
			'auto'                         => __('Auto (try multiple WooCommerce hooks)', 'user-manager'),
			'after_add_to_cart_form'       => __('After Add to Cart form (woocommerce_after_add_to_cart_form)', 'user-manager'),
			'single_product_summary'       => __('Single product summary area (woocommerce_single_product_summary)', 'user-manager'),
			'after_single_product_summary' => __('After single product summary (woocommerce_after_single_product_summary)', 'user-manager'),
			'before_add_to_cart_form'      => __('Before Add to Cart form (woocommerce_before_add_to_cart_form)', 'user-manager'),
		];
		if (!isset($hook_options[$selected_hook])) {
			$selected_hook = 'auto';
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-add-to-cart-variation-table" data-um-active-selectors="#um-add-to-cart-variation-table-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-screenoptions"></span>
				<h2><?php esc_html_e('Add to Cart Variation Table', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-add-to-cart-variation-table-enabled" name="add_to_cart_variation_table_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('On variable product pages, add a separate variation table under the default Add to Cart area so customers can bulk-add multiple variations without changing native add-to-cart behavior.', 'user-manager'); ?>
					</p>
				</div>
				<div id="um-add-to-cart-variation-table-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-add-to-cart-variation-table-hook"><?php esc_html_e('Single Product Page Hook', 'user-manager'); ?></label>
						<select id="um-add-to-cart-variation-table-hook" name="add_to_cart_variation_table_hook"<?php echo $form_attr; ?>>
							<?php foreach ($hook_options as $option_key => $option_label) : ?>
								<option value="<?php echo esc_attr($option_key); ?>" <?php selected($selected_hook, $option_key); ?>>
									<?php echo esc_html($option_label); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e('Choose where the variation table renders on single product pages. If your theme does not show the table, use Auto or try a different hook.', 'user-manager'); ?>
						</p>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-add-to-cart-variation-table-hide-default-form" name="add_to_cart_variation_table_hide_default_form" value="1" <?php checked(!empty($settings['add_to_cart_variation_table_hide_default_form'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Hide default variable-product add to cart form when bulk table is shown', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('When enabled, the normal variation dropdown + single Add to Cart button are hidden and only this bulk table is shown.', 'user-manager'); ?>
						</p>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-add-to-cart-variation-table-show-price-column" name="add_to_cart_variation_table_show_price_column" value="1" <?php checked(!empty($settings['add_to_cart_variation_table_show_price_column'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Show Price column and line total amount in Totals row', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('Adds a third Price column and dynamically calculates the total amount for entered quantities in the bottom Totals row.', 'user-manager'); ?>
						</p>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-add-to-cart-variation-table-debug-mode" name="add_to_cart_variation_table_debug_mode" value="1" <?php checked(!empty($settings['add_to_cart_variation_table_debug_mode'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Enable debug mode for Add to Cart Variation Table', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('When enabled, a front-end debug panel displays per-variation processing details after submitting Add All Variations.', 'user-manager'); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

