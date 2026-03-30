<?php
/**
 * Add-on card: Product Search by SKU.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Product_Search_By_SKU {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['search_redirect_by_sku']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-product-search-by-sku" data-um-active-selectors="#um-product-search-by-sku-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-search"></span>
				<h2><?php esc_html_e('Product Search by SKU', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-product-search-by-sku-enabled" name="search_redirect_by_sku" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('When a WooCommerce front-end search term (?s=) exactly matches a product or variation SKU, automatically redirect to that product page instead of showing search results.', 'user-manager'); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}

