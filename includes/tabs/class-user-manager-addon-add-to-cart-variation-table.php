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
		$text_above = isset($settings['add_to_cart_variation_table_text_above']) ? (string) $settings['add_to_cart_variation_table_text_above'] : '';
		$text_below = isset($settings['add_to_cart_variation_table_text_below']) ? (string) $settings['add_to_cart_variation_table_text_below'] : '';
		$button_text = isset($settings['add_to_cart_variation_table_button_text']) ? (string) $settings['add_to_cart_variation_table_button_text'] : '';
		$selected_category_ids = isset($settings['add_to_cart_variation_table_category_ids']) && is_array($settings['add_to_cart_variation_table_category_ids'])
			? array_values(array_unique(array_filter(array_map('absint', $settings['add_to_cart_variation_table_category_ids']))))
			: [];
		$product_categories = [];
		if (taxonomy_exists('product_cat')) {
			$terms = get_terms([
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			]);
			if (!is_wp_error($terms) && is_array($terms)) {
				$product_categories = $terms;
			}
		}
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
							<input type="checkbox" id="um-add-to-cart-variation-table-prefix-labels" name="add_to_cart_variation_table_prefix_labels" value="1" <?php checked(!empty($settings['add_to_cart_variation_table_prefix_labels'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Prefix all variations with the variation label', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('When enabled, variation rows include attribute labels (for example "Size: Small"). When disabled, only values are shown (for example "Small").', 'user-manager'); ?>
						</p>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-add-to-cart-variation-table-hide-header-row" name="add_to_cart_variation_table_hide_header_row" value="1" <?php checked(!empty($settings['add_to_cart_variation_table_hide_header_row'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Remove the header row (Variation / Qty)', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('When enabled, the variation table header row is hidden on the front end.', 'user-manager'); ?>
						</p>
					</div>
					<div class="um-form-field">
						<label class="um-label-block"><?php esc_html_e('Only display variation table for products in these categories', 'user-manager'); ?></label>
						<div style="max-height:220px; overflow-y:auto; border:1px solid #dcdcde; background:#fff; padding:10px 12px;">
							<?php if (!empty($product_categories)) : ?>
								<?php foreach ($product_categories as $term) : ?>
									<?php $term_id = isset($term->term_id) ? (int) $term->term_id : 0; ?>
									<?php if ($term_id <= 0) { continue; } ?>
									<label style="display:block; margin:0 0 6px;">
										<input type="checkbox" name="add_to_cart_variation_table_category_ids[]" value="<?php echo esc_attr((string) $term_id); ?>" <?php checked(in_array($term_id, $selected_category_ids, true)); ?><?php echo $form_attr; ?> />
										<?php echo esc_html((string) $term->name); ?>
									</label>
								<?php endforeach; ?>
							<?php else : ?>
								<p class="description" style="margin:0;"><?php esc_html_e('No product categories found.', 'user-manager'); ?></p>
							<?php endif; ?>
						</div>
						<p class="description">
							<?php esc_html_e('If no categories are selected, the variation table can display for all variable products.', 'user-manager'); ?>
						</p>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-add-to-cart-variation-table-empty-cart-button" name="add_to_cart_variation_table_empty_cart_button_on_cart" value="1" <?php checked(!empty($settings['add_to_cart_variation_table_empty_cart_button_on_cart'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Add an Empty Cart button on Cart Screen', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('When enabled, an Empty Cart button is added near cart action buttons (next to Update cart where theme markup allows).', 'user-manager'); ?>
						</p>
					</div>
					<div class="um-form-field">
						<label for="um-add-to-cart-variation-table-button-text"><?php esc_html_e('Add to Cart Variation Table Button Text', 'user-manager'); ?></label>
						<input type="text" id="um-add-to-cart-variation-table-button-text" name="add_to_cart_variation_table_button_text" class="regular-text" value="<?php echo esc_attr($button_text); ?>" placeholder="<?php esc_attr_e('Add All Variations', 'user-manager'); ?>"<?php echo $form_attr; ?> />
						<p class="description">
							<?php esc_html_e('Optional override for the front-end submit button text. Leave blank to use "Add All Variations".', 'user-manager'); ?>
						</p>
					</div>
					<div class="um-form-field">
						<label for="um-add-to-cart-variation-table-text-above"><?php esc_html_e('Add Text Above Variation Table', 'user-manager'); ?></label>
						<textarea id="um-add-to-cart-variation-table-text-above" name="add_to_cart_variation_table_text_above" rows="4" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea($text_above); ?></textarea>
						<p class="description">
							<?php esc_html_e('Optional content shown above the front-end variation table. Supports HTML tags.', 'user-manager'); ?>
						</p>
					</div>
					<div class="um-form-field">
						<label for="um-add-to-cart-variation-table-text-below"><?php esc_html_e('Add Text Below Variation Table', 'user-manager'); ?></label>
						<textarea id="um-add-to-cart-variation-table-text-below" name="add_to_cart_variation_table_text_below" rows="4" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea($text_below); ?></textarea>
						<p class="description">
							<?php esc_html_e('Optional content shown below the front-end variation table. Supports HTML tags.', 'user-manager'); ?>
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
						<p class="description">
							<?php esc_html_e('Front-end trace URL (admin only): append ?um_variation_table_trace=1 to a variable product URL to see runtime hook/debug diagnostics even if the table does not render.', 'user-manager'); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php self::render_history_card(); ?>
		<?php
	}

	/**
	 * Render recent Add to Cart Variation Table history.
	 */
	private static function render_history_card(): void {
		$history = get_option('add_to_cart_variation_table_history', []);
		if (!is_array($history)) {
			$history = [];
		}
		$history = array_slice($history, 0, 50);
		?>
		<div class="um-admin-card" style="margin-top:16px;">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-list-view"></span>
				<h2><?php esc_html_e('Add to Cart Variation Table History', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<?php if (empty($history)) : ?>
					<p class="description"><?php esc_html_e('No Add to Cart Variation Table history yet.', 'user-manager'); ?></p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e('Timestamp', 'user-manager'); ?></th>
								<th><?php esc_html_e('Who', 'user-manager'); ?></th>
								<th><?php esc_html_e('Items Added in Bulk', 'user-manager'); ?></th>
								<th><?php esc_html_e('Variations / Options', 'user-manager'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($history as $row) : ?>
								<?php
								$timestamp = isset($row['timestamp']) ? (string) $row['timestamp'] : '';
								if ($timestamp !== '' && function_exists('mysql2date')) {
									$timestamp = mysql2date('Y-m-d H:i:s', $timestamp, true);
								}
								$who = isset($row['who']) ? (string) $row['who'] : '';
								$items_added = isset($row['items_added']) ? (int) $row['items_added'] : 0;
								$product_name = isset($row['product_name']) ? (string) $row['product_name'] : '';
								$variations = isset($row['variations']) && is_array($row['variations']) ? $row['variations'] : [];
								?>
								<tr>
									<td><?php echo esc_html($timestamp !== '' ? $timestamp : '—'); ?></td>
									<td><?php echo esc_html($who !== '' ? $who : '—'); ?></td>
									<td><?php echo esc_html((string) $items_added); ?></td>
									<td>
										<?php if ($product_name !== '') : ?>
											<div><strong><?php esc_html_e('Product:', 'user-manager'); ?></strong> <?php echo esc_html($product_name); ?></div>
										<?php endif; ?>
										<?php if (empty($variations)) : ?>
											—
										<?php else : ?>
											<ul style="margin:0; padding-left:18px;">
												<?php foreach ($variations as $variation_row) : ?>
													<li><?php echo esc_html(self::format_history_variation_row($variation_row)); ?></li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Build a readable history line for one variation row.
	 *
	 * @param mixed $variation_row History row payload.
	 */
	private static function format_history_variation_row($variation_row): string {
		if (!is_array($variation_row)) {
			return (string) $variation_row;
		}
		$label = isset($variation_row['label']) ? trim((string) $variation_row['label']) : '';
		$variation_id = isset($variation_row['variation_id']) ? (int) $variation_row['variation_id'] : 0;
		$qty = isset($variation_row['qty']) ? (int) $variation_row['qty'] : 0;
		$status = isset($variation_row['status']) ? trim((string) $variation_row['status']) : '';
		$note = isset($variation_row['note']) ? trim((string) $variation_row['note']) : '';
		$parts = [];
		$parts[] = $label !== '' ? $label : ('#' . (string) $variation_id);
		if ($qty > 0) {
			$parts[] = 'x' . (string) $qty;
		}
		if ($status !== '') {
			$parts[] = strtoupper($status);
		}
		if ($note !== '') {
			$parts[] = $note;
		}
		return implode(' | ', $parts);
	}
}

