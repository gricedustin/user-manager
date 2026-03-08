<?php
/**
 * Add-on card: Bulk Add to Cart.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Bulk_Add_To_Cart {

	public static function render(array $settings, array $bulk_settings): void {
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-bulk-add-to-cart" data-um-active-selectors="input[name='bulk_add_to_cart_enabled']">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-cart"></span>
				<h2><?php esc_html_e('Bulk Add to Cart', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="bulk_add_to_cart_enabled" value="1" <?php checked($settings['bulk_add_to_cart_enabled'] ?? false); ?> />
						<?php esc_html_e('Activate Bulk Add to Cart Functionality', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Enables the [bulk_add_to_cart] CSV upload form on the front-end and processes uploaded files to add multiple products to the WooCommerce cart.', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field">
					<label class="um-label-block"><?php esc_html_e('Shortcode usage', 'user-manager'); ?></label>
					<input type="text" readonly class="regular-text code" value="[bulk_add_to_cart]" onclick="this.select();" />
					<p class="description">
						<?php esc_html_e('Place this shortcode on a page to show the CSV upload form for logged-in users.', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field">
					<label class="um-label-block"><?php esc_html_e('Front-end debug URL parameter', 'user-manager'); ?></label>
					<input type="text" readonly class="large-text code" value="?um_bulk_add_to_cart_debug=1" onclick="this.select();" />
					<p class="description">
						<?php esc_html_e('Append this parameter to the page with [bulk_add_to_cart] to force verbose debug notices for that request, even if debug mode is unchecked.', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="bulk_add_to_cart_redirect_to_cart" value="1" <?php checked(($bulk_settings['redirect_to_cart'] ?? '1') === '1'); ?> />
						<?php esc_html_e('Redirect to cart page after processing CSV file', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('When enabled, users will be automatically redirected to the cart page after the CSV has been processed.', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field">
					<label for="um-bulk-identifier-column"><?php esc_html_e('Product Identifier Column', 'user-manager'); ?></label>
					<input type="text" name="bulk_add_to_cart_identifier_column" id="um-bulk-identifier-column" class="regular-text" value="<?php echo esc_attr($bulk_settings['identifier_column'] ?? 'product_id'); ?>" />
					<p class="description">
						<?php esc_html_e('Exact column header in your CSV that contains the product identifier (ID, SKU, slug, title, or meta field value).', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field">
					<label for="um-bulk-identifier-type"><?php esc_html_e('Identifier Type', 'user-manager'); ?></label>
					<select name="bulk_add_to_cart_identifier_type" id="um-bulk-identifier-type" class="regular-text">
						<?php $identifier_type = $bulk_settings['identifier_type'] ?? 'product_id'; ?>
						<option value="product_id" <?php selected($identifier_type, 'product_id'); ?>><?php esc_html_e('Product ID', 'user-manager'); ?></option>
						<option value="product_sku" <?php selected($identifier_type, 'product_sku'); ?>><?php esc_html_e('Product SKU', 'user-manager'); ?></option>
						<option value="product_slug" <?php selected($identifier_type, 'product_slug'); ?>><?php esc_html_e('Product Slug', 'user-manager'); ?></option>
						<option value="product_title" <?php selected($identifier_type, 'product_title'); ?>><?php esc_html_e('Product Title', 'user-manager'); ?></option>
						<option value="meta_field" <?php selected($identifier_type, 'meta_field'); ?>><?php esc_html_e('Custom Meta Field Value', 'user-manager'); ?></option>
					</select>
					<p class="description">
						<?php esc_html_e('Choose how products should be looked up from the CSV identifier column.', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field" id="um-bulk-meta-field-name-row">
					<label for="um-bulk-meta-field-name"><?php esc_html_e('Meta Field Name (when using Custom Meta Field Value)', 'user-manager'); ?></label>
					<input type="text" name="bulk_add_to_cart_meta_field_name" id="um-bulk-meta-field-name" class="regular-text" value="<?php echo esc_attr($bulk_settings['meta_field_name'] ?? ''); ?>" />
					<p class="description">
						<?php esc_html_e('Meta key that contains the unique identifier used in your CSV when Identifier Type is "Custom Meta Field Value".', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field">
					<label for="um-bulk-quantity-column"><?php esc_html_e('Quantity Column', 'user-manager'); ?></label>
					<input type="text" name="bulk_add_to_cart_quantity_column" id="um-bulk-quantity-column" class="regular-text" value="<?php echo esc_attr($bulk_settings['quantity_column'] ?? 'quantity'); ?>" />
					<p class="description">
						<?php esc_html_e('Exact column header in your CSV that contains the quantity for each product row.', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="bulk_add_to_cart_debug_mode" value="1" <?php checked(($bulk_settings['debug_mode'] ?? '0') === '1'); ?> />
						<?php esc_html_e('Enable debug mode for Bulk Add to Cart', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('When enabled, extra WooCommerce notices will describe how the CSV was parsed and how each row was handled.', 'user-manager'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}

