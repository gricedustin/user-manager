<?php
/**
 * Add-on card: Bulk Add to Cart.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Bulk_Add_To_Cart {

	public static function render(array $settings, array $bulk_settings): void {
		$history = get_option('bulk_add_to_cart_history', []);
		if (!is_array($history)) {
			$history = [];
		}
		$history = array_slice($history, 0, 50);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-bulk-add-to-cart" data-um-active-selectors="input[name='bulk_add_to_cart_enabled']">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-cart"></span>
				<h2><?php esc_html_e('Add to Cart Bulk Import', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="bulk_add_to_cart_enabled" value="1" <?php checked($settings['bulk_add_to_cart_enabled'] ?? false); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Enables the [bulk_add_to_cart] CSV upload form on the front-end and processes uploaded files to add multiple products to the WooCommerce cart.', 'user-manager'); ?>
					</p>
				</div>
				<div id="um-bulk-add-to-cart-fields" style="<?php echo !empty($settings['bulk_add_to_cart_enabled']) ? '' : 'display:none;'; ?>">
				<div class="um-form-field">
					<label class="um-label-block"><?php esc_html_e('Shortcode usage', 'user-manager'); ?></label>
					<input type="text" readonly class="regular-text code" value="[bulk_add_to_cart]" onclick="this.select();" />
					<p class="description">
						<?php esc_html_e('Place this shortcode on a page to show the CSV upload form for logged-in users.', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field">
					<?php
					$show_sample_csv = array_key_exists('show_sample_csv', $bulk_settings)
						? ($bulk_settings['show_sample_csv'] ?? '1') === '1'
						: true;
					?>
					<label>
						<input type="checkbox" name="bulk_add_to_cart_show_sample_csv" value="1" <?php checked($show_sample_csv); ?> />
						<?php esc_html_e('Show "Download Sample CSV" link', 'user-manager'); ?>
					</label>
				</div>
				<div class="um-form-field">
					<?php
					$show_sample_with_data = array_key_exists('show_sample_with_product_data', $bulk_settings)
						? ($bulk_settings['show_sample_with_product_data'] ?? '1') === '1'
						: true;
					?>
					<label>
						<input type="checkbox" name="bulk_add_to_cart_show_sample_with_product_data" value="1" <?php checked($show_sample_with_data); ?> />
						<?php esc_html_e('Show "Download Sample CSV with Product Data" link', 'user-manager'); ?>
					</label>
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
					<label for="um-bulk-product-id-custom-header"><?php esc_html_e('product_id Custom Column Header', 'user-manager'); ?></label>
					<input type="text" name="bulk_add_to_cart_product_id_custom_column_header" id="um-bulk-product-id-custom-header" class="regular-text" value="<?php echo esc_attr($bulk_settings['product_id_custom_column_header'] ?? 'product_id'); ?>" />
					<p class="description">
						<?php esc_html_e('Changes the forced product_id column header in sample CSV files. Processing still treats this column as product_id fallback.', 'user-manager'); ?>
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
				<div class="um-form-field">
					<label class="um-label-block"><?php esc_html_e('Front-end debug URL parameter', 'user-manager'); ?></label>
					<input type="text" readonly class="large-text code" value="?um_bulk_add_to_cart_debug=1" onclick="this.select();" />
					<p class="description">
						<?php esc_html_e('Append this parameter to the page with [bulk_add_to_cart] to force verbose debug notices for that request, even if debug mode is unchecked.', 'user-manager'); ?>
					</p>
				</div>
				</div>
			</div>
		</div>

		<div class="um-admin-card" style="margin-top: 18px;">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-list-view"></span>
				<h2><?php esc_html_e('Add to Cart Bulk Import History', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<?php if (empty($history)) : ?>
					<p class="description"><?php esc_html_e('No Bulk Import history yet.', 'user-manager'); ?></p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e('Timestamp', 'user-manager'); ?></th>
								<th><?php esc_html_e('User Email', 'user-manager'); ?></th>
								<th><?php esc_html_e('Link to File in Media Library', 'user-manager'); ?></th>
								<th><?php esc_html_e('Total Items Added', 'user-manager'); ?></th>
								<th><?php esc_html_e('Number of Errors', 'user-manager'); ?></th>
								<th><?php esc_html_e('View More', 'user-manager'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($history as $index => $row) : ?>
							<?php
							$row_id = 'um-bulk-import-history-details-' . (int) $index;
							$timestamp = isset($row['timestamp']) ? (string) $row['timestamp'] : '';
							if ($timestamp !== '' && function_exists('mysql2date')) {
								$timestamp = mysql2date('Y-m-d H:i:s', $timestamp, true);
							}
							$user_email = isset($row['user_email']) ? (string) $row['user_email'] : '';
							if ($user_email === '' && !empty($row['user_id'])) {
								$user = get_userdata((int) $row['user_id']);
								if ($user && !empty($user->user_email)) {
									$user_email = (string) $user->user_email;
								}
							}
							$file_url = isset($row['file_url']) ? (string) $row['file_url'] : '';
							$attachment_id = isset($row['media_attachment_id']) ? (int) $row['media_attachment_id'] : 0;
							$total_items_added = isset($row['total_items_added']) ? (int) $row['total_items_added'] : 0;
							if ($total_items_added <= 0 && !empty($row['successes']) && is_array($row['successes'])) {
								$total_items_added = (int) array_sum(array_map('intval', $row['successes']));
							}
							$error_count = isset($row['error_count']) ? (int) $row['error_count'] : 0;
							?>
							<tr>
								<td><?php echo esc_html($timestamp !== '' ? $timestamp : '—'); ?></td>
								<td><?php echo esc_html($user_email !== '' ? $user_email : '—'); ?></td>
								<td>
									<?php if ($attachment_id > 0) : ?>
										<?php $edit_link = get_edit_post_link($attachment_id); ?>
										<?php if (!empty($edit_link)) : ?>
											<a href="<?php echo esc_url($edit_link); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open Media Item', 'user-manager'); ?></a>
										<?php elseif ($file_url !== '') : ?>
											<a href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open File', 'user-manager'); ?></a>
										<?php else : ?>
											—
										<?php endif; ?>
									<?php elseif ($file_url !== '') : ?>
										<a href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open File', 'user-manager'); ?></a>
									<?php else : ?>
										—
									<?php endif; ?>
								</td>
								<td><?php echo esc_html((string) $total_items_added); ?></td>
								<td><?php echo esc_html((string) $error_count); ?></td>
								<td>
									<button type="button" class="button button-small um-bulk-history-toggle" data-target="<?php echo esc_attr($row_id); ?>">
										<?php esc_html_e('View More', 'user-manager'); ?>
									</button>
								</td>
							</tr>
							<tr id="<?php echo esc_attr($row_id); ?>" style="display:none;">
								<td colspan="6">
									<?php self::render_history_details($row); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.um-bulk-history-toggle').off('click.umBulkHistory').on('click.umBulkHistory', function() {
				var targetId = $(this).attr('data-target');
				if (!targetId) {
					return;
				}
				$('#' + targetId).toggle();
			});
		});
		</script>
		<?php
	}

	/**
	 * Render per-run detailed data and confirmation messages.
	 *
	 * @param array<string,mixed> $row History row.
	 */
	private static function render_history_details(array $row): void {
		$confirmation_messages = isset($row['confirmation_messages']) && is_array($row['confirmation_messages'])
			? $row['confirmation_messages']
			: [];
		$detail_messages = isset($row['detail_messages']) && is_array($row['detail_messages'])
			? $row['detail_messages']
			: [];
		$errors = isset($row['errors']) && is_array($row['errors']) ? $row['errors'] : [];

		echo '<div style="padding:10px 0;">';
		echo '<p><strong>' . esc_html__('Confirmation Notification Messages', 'user-manager') . '</strong></p>';
		if (!empty($confirmation_messages)) {
			echo '<ul>';
			foreach ($confirmation_messages as $message) {
				echo '<li>' . esc_html((string) $message) . '</li>';
			}
			echo '</ul>';
		} else {
			echo '<p class="description">' . esc_html__('No confirmation messages were stored for this run.', 'user-manager') . '</p>';
		}

		echo '<p><strong>' . esc_html__('Details', 'user-manager') . '</strong></p>';
		if (!empty($detail_messages)) {
			echo '<ul>';
			foreach ($detail_messages as $message) {
				echo '<li>' . esc_html((string) $message) . '</li>';
			}
			echo '</ul>';
		} else {
			echo '<p class="description">' . esc_html__('No detailed line items were stored for this run.', 'user-manager') . '</p>';
		}

		if (!empty($errors)) {
			echo '<p><strong>' . esc_html__('Error Rows', 'user-manager') . '</strong></p>';
			echo '<ul>';
			foreach ($errors as $error_row) {
				echo '<li>' . esc_html((string) $error_row) . '</li>';
			}
			echo '</ul>';
		}
		echo '</div>';
	}
}

