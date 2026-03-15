<?php
/**
 * Add-on card: Plugin Tags & Notes.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Plugin_Tags_Notes {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['plugin_tags_notes_enabled']);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-plugin-tags-notes" data-um-active-selectors="#um-plugin-tags-notes-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-tag"></span>
				<h2><?php esc_html_e('Plugin Tags & Notes', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-plugin-tags-notes-enabled" name="plugin_tags_notes_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Add private plugin tags and notes on the WP-Admin Plugins screen, including tag badges, inline editors, and client-side filtering by tag.', 'user-manager'); ?>
					</p>
					<p class="description">
						<?php esc_html_e('Helpful for organizing large plugin stacks by owner, purpose, environment, or risk level.', 'user-manager'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}

