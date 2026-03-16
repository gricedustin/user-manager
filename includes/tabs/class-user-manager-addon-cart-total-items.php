<?php
/**
 * Add-on card: Cart Total Items.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Cart_Total_Items {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['cart_total_items_enabled']);
		$copy = isset($settings['cart_total_items_copy']) ? (string) $settings['cart_total_items_copy'] : 'Total Items:';
		$show_on_cart = array_key_exists('cart_total_items_show_on_cart', $settings)
			? !empty($settings['cart_total_items_show_on_cart'])
			: true;
		$show_on_checkout = array_key_exists('cart_total_items_show_on_checkout', $settings)
			? !empty($settings['cart_total_items_show_on_checkout'])
			: true;
		$cart_above = !empty($settings['cart_total_items_cart_above']);
		$cart_below = array_key_exists('cart_total_items_cart_below', $settings)
			? !empty($settings['cart_total_items_cart_below'])
			: true;
		$checkout_above = !empty($settings['cart_total_items_checkout_above']);
		$checkout_below = array_key_exists('cart_total_items_checkout_below', $settings)
			? !empty($settings['cart_total_items_checkout_below'])
			: true;
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-cart-total-items" data-um-active-selectors="#um-cart-total-items-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-cart"></span>
				<h2><?php esc_html_e('Cart Total Items', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-cart-total-items-enabled" name="cart_total_items_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Display a centered "Total Items" summary on cart and checkout review areas with separate placement controls for above and below each table.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-cart-total-items-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-cart-total-items-copy"><?php esc_html_e('Total Items Copy', 'user-manager'); ?></label>
						<input type="text" id="um-cart-total-items-copy" name="cart_total_items_copy" class="regular-text" value="<?php echo esc_attr($copy); ?>" placeholder="<?php esc_attr_e('Total Items:', 'user-manager'); ?>"<?php echo $form_attr; ?> />
						<p class="description"><?php esc_html_e('Text shown before the quantity value. Example output: "Total Items: 325".', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="cart_total_items_show_on_cart" value="1" <?php checked($show_on_cart); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Display on Cart', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field" style="margin-left:18px;">
						<label>
							<input type="checkbox" name="cart_total_items_cart_above" value="1" <?php checked($cart_above); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Display above cart table', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field" style="margin-left:18px;">
						<label>
							<input type="checkbox" name="cart_total_items_cart_below" value="1" <?php checked($cart_below); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Display below cart table', 'user-manager'); ?>
						</label>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" name="cart_total_items_show_on_checkout" value="1" <?php checked($show_on_checkout); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Display on Checkout', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field" style="margin-left:18px;">
						<label>
							<input type="checkbox" name="cart_total_items_checkout_above" value="1" <?php checked($checkout_above); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Display above checkout review table', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field" style="margin-left:18px;">
						<label>
							<input type="checkbox" name="cart_total_items_checkout_below" value="1" <?php checked($checkout_below); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Display below checkout review table', 'user-manager'); ?>
						</label>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

