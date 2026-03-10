<?php
/**
 * Add-on card: API.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_API {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-api" data-um-active-selectors="#um-openai-content-generator-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-admin-generic"></span>
				<h2><?php esc_html_e('Post Content Generator', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-openai-content-generator-enabled" name="openai_content_generator_enabled" value="1" <?php checked(!empty($settings['openai_content_generator_enabled'])); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Enable AI-powered blog content generation and import tools in this add-on card.', 'user-manager'); ?></p>
				</div>

				<div id="um-openai-content-generator-fields" style="<?php echo empty($settings['openai_content_generator_enabled']) ? 'display:none;' : ''; ?>">
				<p class="description" style="margin-top:0;">
					<?php esc_html_e('API key management is now in Settings > API Keys. Use this add-on to configure ChatGPT content behavior.', 'user-manager'); ?>
				</p>
				<p class="description" style="margin-top:0;">
					<?php esc_html_e('When activated, Blog Post Importer appears directly in this add-on card.', 'user-manager'); ?>
				</p>
				<div class="um-form-field">
					<label for="um-openai-prompt-append"><?php esc_html_e('Appended Information to AI Prompt', 'user-manager'); ?></label>
					<textarea name="openai_prompt_append" id="um-openai-prompt-append" class="large-text" rows="4" style="width:100%;"<?php echo $form_attr; ?>><?php echo esc_textarea($settings['openai_prompt_append'] ?? ''); ?></textarea>
					<p class="description"><?php esc_html_e('This text is added to the end of the prompt on every "Auto write from ChatGPT" request to provide extra context (e.g. tone, audience, keywords). Leave blank to skip.', 'user-manager'); ?></p>
				</div>
				<div class="um-form-field">
					<label><input type="checkbox" id="um-openai-page-meta-box" name="openai_page_meta_box" value="1" <?php checked(!empty($settings['openai_page_meta_box'])); ?><?php echo $form_attr; ?> /> <?php esc_html_e('Enable Page & Post Meta Box for inserting ChatGPT generated content into pages & posts', 'user-manager'); ?></label>
					<p class="description"><?php esc_html_e('When enabled, page edit screens show a meta box to generate content via ChatGPT and insert it at the bottom of the page (block format).', 'user-manager'); ?></p>
				</div>
				<?php User_Manager_Tab_Tools::render(false, true, false, true, false, false); ?>
				</div>
			</div>
		</div>
		<?php
	}
}

