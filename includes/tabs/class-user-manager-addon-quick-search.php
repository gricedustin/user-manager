<?php
/**
 * Add-on card: Quick Search Bar.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Quick_Search {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$post_types = get_post_types(['show_ui' => true], 'objects');
		$post_types = is_array($post_types) ? $post_types : [];
		unset($post_types['attachment']);

		$post_type_choices = [];
		foreach ($post_types as $post_type) {
			if (!is_object($post_type) || empty($post_type->name)) {
				continue;
			}
			$post_type_slug = sanitize_key((string) $post_type->name);
			if ($post_type_slug === '') {
				continue;
			}
			$label = isset($post_type->labels->name) ? (string) $post_type->labels->name : $post_type_slug;
			if ($post_type_slug === 'shop_order') {
				$label = __('Orders', 'user-manager');
			} elseif ($post_type_slug === 'product') {
				$label = __('Products', 'user-manager');
			}
			$post_type_choices[$post_type_slug] = $label;
		}
		natcasesort($post_type_choices);

		$selected_priority_post_types = [];
		if (!empty($settings['um_quick_search_priority_post_types']) && is_array($settings['um_quick_search_priority_post_types'])) {
			$selected_priority_post_types = array_values(array_map('sanitize_key', $settings['um_quick_search_priority_post_types']));
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-quick-search" data-um-active-selectors="#um-quick-search-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-search"></span>
				<h2><?php esc_html_e('WP-Admin Bar Quick Search', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-quick-search-enabled" name="um_quick_search_enabled" value="1" <?php checked($settings['um_quick_search_enabled'] ?? false); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Adds a "Search" icon to the WordPress admin bar that opens a quick search dropdown for posts, products, orders, users, product categories, and media.', 'user-manager'); ?>
					</p>
				</div>
				<div class="um-form-field">
					<label class="um-label-block"><?php esc_html_e('Priority Post Types to Display Before All Remaining Post Types', 'user-manager'); ?></label>
					<p class="description" style="margin-top: 0;">
						<?php esc_html_e('Checked post types appear first in the Quick Search dropdown. Unchecked post types will still appear after priority post types.', 'user-manager'); ?>
					</p>
					<?php if (!empty($post_type_choices)) : ?>
						<div class="um-checkbox-list" style="border: 1px solid #c3c4c7; padding: 10px 12px; background: #fff; border-radius: 4px; max-height: 220px; overflow:auto;">
							<?php foreach ($post_type_choices as $post_type_slug => $post_type_label) : ?>
								<label style="display: block; margin-bottom: 6px;">
									<input type="checkbox" name="um_quick_search_priority_post_types[]" value="<?php echo esc_attr($post_type_slug); ?>" <?php checked(in_array($post_type_slug, $selected_priority_post_types, true)); ?><?php echo $form_attr; ?> />
									<?php echo esc_html($post_type_label); ?> (<code><?php echo esc_html($post_type_slug); ?></code>)
								</label>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p class="description"><?php esc_html_e('No UI post types found.', 'user-manager'); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

