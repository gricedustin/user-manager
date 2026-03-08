<?php
/**
 * Add-on card: Blog Post Idea Generator.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Blog_Post_Idea_Generator {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-blog-post-idea-generator" data-um-active-selectors="#um-openai-blog-post-idea-generator-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-lightbulb"></span>
				<h2><?php esc_html_e('Post Idea Generator', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-openai-blog-post-idea-generator-enabled" name="openai_blog_post_idea_generator_enabled" value="1" <?php checked(!empty($settings['openai_blog_post_idea_generator_enabled'])); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate Post Idea Generator', 'user-manager'); ?>
					</label>
				</div>
				<div id="um-openai-blog-post-idea-generator-fields" style="<?php echo empty($settings['openai_blog_post_idea_generator_enabled']) ? 'display:none;' : ''; ?>">
					<p class="description" style="margin-top:0;">
						<?php esc_html_e('Enable or disable Post Idea Generator tools in Add-ons.', 'user-manager'); ?>
					</p>
					<?php User_Manager_Tab_Tools::render(false, false, true, false, true, false); ?>
				</div>
			</div>
		</div>
		<?php
	}
}

