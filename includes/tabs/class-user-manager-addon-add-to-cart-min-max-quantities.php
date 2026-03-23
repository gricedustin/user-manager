<?php
/**
 * Add-on card: Add to Cart Min/Max Quantities.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Add_To_Cart_Min_Max_Quantities {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['add_to_cart_min_max_quantities_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-add-to-cart-min-max-quantities" data-um-active-selectors="#um-add-to-cart-min-max-quantities-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-editor-ol"></span>
				<h2><?php esc_html_e('Add to Cart Min/Max Quantities', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-add-to-cart-min-max-quantities-enabled" name="add_to_cart_min_max_quantities_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Adds product-level "Minimum quantity" and "Maximum quantity" fields in WooCommerce Inventory settings and enforces limits during add-to-cart and cart validation.', 'user-manager'); ?>
					</p>
				</div>
				<div id="um-add-to-cart-min-max-quantities-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description">
						<?php esc_html_e('After activation, edit any WooCommerce product and open Product data > Inventory to set Minimum quantity and Maximum quantity values.', 'user-manager'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}
