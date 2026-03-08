<?php
/**
 * Add-on card: WP-Admin Bar Menu Items.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_WP_Admin_Bar_Menu_Items {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$admin_bar_items = isset($settings['admin_bar_menu_items']) && is_array($settings['admin_bar_menu_items']) ? $settings['admin_bar_menu_items'] : [];
		$is_enabled = array_key_exists('admin_bar_menu_items_enabled', $settings)
			? !empty($settings['admin_bar_menu_items_enabled'])
			: false;
		if (!array_key_exists('admin_bar_menu_items_enabled', $settings)) {
			foreach ($admin_bar_items as $row) {
				if (!is_array($row)) {
					continue;
				}
				$title = trim((string) ($row['title'] ?? ''));
				$shortcuts = trim((string) ($row['shortcuts'] ?? ''));
				if ($title !== '' || $shortcuts !== '') {
					$is_enabled = true;
					break;
				}
			}
		}
		if (empty($admin_bar_items)) {
			$admin_bar_items = [['title' => '', 'icon' => '', 'shortcuts' => '', 'side' => 'right']];
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-admin-bar-menu" data-um-active-selectors="#um-admin-bar-menu-items-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-menu-alt"></span>
				<h2><?php esc_html_e('WP-Admin Bar Menu Items', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" name="admin_bar_menu_items_enabled" id="um-admin-bar-menu-items-enabled" value="1" <?php checked($is_enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate WP-Admin Bar Menu Items', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Add custom top-bar shortcut menus for quick navigation in WordPress admin and front-end.', 'user-manager'); ?></p>
				</div>
				<div id="um-admin-bar-menu-items-fields" style="<?php echo $is_enabled ? '' : 'display:none;'; ?>">
				<p class="description" style="margin-bottom: 16px;"><?php esc_html_e('Add custom shortcut menus to the WordPress admin bar (visible on both front-end and back-end). Each item becomes a dropdown; add links line by line as "Label|URL" or use "Group Title|divider" to create a section header.', 'user-manager'); ?></p>
				<div id="um-admin-bar-menu-list">
					<?php foreach ($admin_bar_items as $idx => $item) : ?>
						<div class="um-admin-bar-menu-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
							<h3 class="um-settings-subsection um-admin-bar-menu-number" style="margin-top: 0;"><?php echo esc_html(sprintf(__('Menu %d', 'user-manager'), $idx + 1)); ?></h3>
							<div class="um-form-field">
								<label><?php esc_html_e('Title of shortcuts menu item', 'user-manager'); ?></label>
								<input type="text" name="admin_bar_menu_item[<?php echo (int) $idx; ?>][title]" class="large-text" value="<?php echo esc_attr($item['title'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g. Admin Menu', 'user-manager'); ?>"<?php echo $form_attr; ?> />
							</div>
							<div class="um-form-field">
								<label><?php esc_html_e('Icon override', 'user-manager'); ?></label>
								<input type="text" name="admin_bar_menu_item[<?php echo (int) $idx; ?>][icon]" class="regular-text" value="<?php echo esc_attr($item['icon'] ?? ''); ?>" placeholder="dashicons-admin-links"<?php echo $form_attr; ?> />
								<p class="description"><?php esc_html_e('Dashicons class (e.g. dashicons-admin-links). See', 'user-manager'); ?> <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener">developer.wordpress.org/resource/dashicons</a></p>
							</div>
							<div class="um-form-field">
								<label for="um-admin-bar-menu-side-<?php echo (int) $idx; ?>"><?php esc_html_e('Top Bar Side', 'user-manager'); ?></label>
								<?php $side = isset($item['side']) && $item['side'] === 'left' ? 'left' : 'right'; ?>
								<select name="admin_bar_menu_item[<?php echo (int) $idx; ?>][side]" id="um-admin-bar-menu-side-<?php echo (int) $idx; ?>" class="regular-text"<?php echo $form_attr; ?>>
									<option value="right" <?php selected($side, 'right'); ?>><?php esc_html_e('Right side (default)', 'user-manager'); ?></option>
									<option value="left" <?php selected($side, 'left'); ?>><?php esc_html_e('Left side', 'user-manager'); ?></option>
								</select>
								<p class="description"><?php esc_html_e('Choose where this menu appears in the WP admin top bar.', 'user-manager'); ?></p>
							</div>
							<div class="um-form-field">
								<label><?php esc_html_e('Shortcuts', 'user-manager'); ?></label>
								<textarea name="admin_bar_menu_item[<?php echo (int) $idx; ?>][shortcuts]" rows="8" class="large-text" style="width:100%;" placeholder="Coupon Manager|https://example.com/wp-admin/edit.php?post_type=shop_coupon"<?php echo $form_attr; ?>><?php echo esc_textarea($item['shortcuts'] ?? ''); ?></textarea>
								<p class="description"><?php esc_html_e('One per line: Link Title|URL. Use "Group Title|divider" for a section header. All link titles must be unique.', 'user-manager'); ?></p>
							</div>
							<button type="button" class="button um-remove-admin-bar-menu"><?php esc_html_e('Remove this menu', 'user-manager'); ?></button>
						</div>
					<?php endforeach; ?>
				</div>
				<p>
					<button type="button" class="button" id="um-add-admin-bar-menu"><?php esc_html_e('Add menu', 'user-manager'); ?></button>
				</p>
				</div>
			</div>
		</div>
		<?php
	}

	public static function render_template(string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		?>
		<script type="text/template" id="um-admin-bar-menu-template">
			<div class="um-admin-bar-menu-block" style="margin-bottom: 24px; padding: 16px; border: 1px solid #c3c4c7; border-radius: 4px; background: #f6f7f7;">
				<h3 class="um-settings-subsection um-admin-bar-menu-number" style="margin-top: 0;"><?php esc_html_e('New menu', 'user-manager'); ?></h3>
				<div class="um-form-field">
					<label><?php esc_html_e('Title of shortcuts menu item', 'user-manager'); ?></label>
					<input type="text" name="admin_bar_menu_item[__INDEX__][title]" class="large-text" value="" placeholder="<?php esc_attr_e('e.g. Admin Menu', 'user-manager'); ?>"<?php echo $form_attr; ?> />
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('Icon override', 'user-manager'); ?></label>
					<input type="text" name="admin_bar_menu_item[__INDEX__][icon]" class="regular-text" value="" placeholder="dashicons-admin-links"<?php echo $form_attr; ?> />
					<p class="description"><?php esc_html_e('Dashicons class (e.g. dashicons-admin-links). See', 'user-manager'); ?> <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener">developer.wordpress.org/resource/dashicons</a></p>
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('Top Bar Side', 'user-manager'); ?></label>
					<select name="admin_bar_menu_item[__INDEX__][side]" class="regular-text"<?php echo $form_attr; ?>>
						<option value="right"><?php esc_html_e('Right side (default)', 'user-manager'); ?></option>
						<option value="left"><?php esc_html_e('Left side', 'user-manager'); ?></option>
					</select>
					<p class="description"><?php esc_html_e('Choose where this menu appears in the WP admin top bar.', 'user-manager'); ?></p>
				</div>
				<div class="um-form-field">
					<label><?php esc_html_e('Shortcuts', 'user-manager'); ?></label>
					<textarea name="admin_bar_menu_item[__INDEX__][shortcuts]" rows="8" class="large-text" style="width:100%;" placeholder="Coupon Manager|https://example.com/wp-admin/edit.php?post_type=shop_coupon"<?php echo $form_attr; ?>></textarea>
					<p class="description"><?php esc_html_e('One per line: Link Title|URL. Use "Group Title|divider" for a section header. All link titles must be unique.', 'user-manager'); ?></p>
				</div>
				<button type="button" class="button um-remove-admin-bar-menu"><?php esc_html_e('Remove this menu', 'user-manager'); ?></button>
			</div>
		</script>
		<?php
	}
}

