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
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-add-to-cart-variation-table" data-um-active-selectors="#um-add-to-cart-variation-table-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-screenoptions"></span>
				<h2><?php esc_html_e('Add to Cart Variation Table', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-add-to-cart-variation-table-enabled" name="add_to_cart_variation_table_enabled" value="1" <?php checked(!empty($settings['add_to_cart_variation_table_enabled'])); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('On variable product pages, display a variation table with quantity inputs so customers can add multiple variations to cart in one submission.', 'user-manager'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}

