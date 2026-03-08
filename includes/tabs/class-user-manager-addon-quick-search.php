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
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-quick-search" data-um-active-selectors="#um-quick-search-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-search"></span>
				<h2><?php esc_html_e('WP-Admin Bar Quick Search', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-quick-search-enabled" name="um_quick_search_enabled" value="1" <?php checked($settings['um_quick_search_enabled'] ?? true); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate WP-Admin Bar Quick Search', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Adds a "Search" icon to the WordPress admin bar that opens a quick search dropdown for posts, products, orders, users, product categories, and media.', 'user-manager'); ?>
					</p>
					<p class="description">
						<?php esc_html_e('This add-on is enabled by default for new installs unless you explicitly disable it.', 'user-manager'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}

