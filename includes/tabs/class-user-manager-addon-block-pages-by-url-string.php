<?php
/**
 * Add-on card: Block Pages by URL String.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Block_Pages_By_URL_String {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['block_pages_by_url_string_enabled']);
		$match_urls = isset($settings['block_pages_by_url_string_match_urls']) ? (string) $settings['block_pages_by_url_string_match_urls'] : '';
		$exception_urls = isset($settings['block_pages_by_url_string_exception_urls']) ? (string) $settings['block_pages_by_url_string_exception_urls'] : '';
		$background_color = isset($settings['block_pages_by_url_string_background_color']) ? (string) $settings['block_pages_by_url_string_background_color'] : '#000000';
		$background_url = isset($settings['block_pages_by_url_string_background_url']) ? (string) $settings['block_pages_by_url_string_background_url'] : '';
		$logo_url = isset($settings['block_pages_by_url_string_logo_url']) ? (string) $settings['block_pages_by_url_string_logo_url'] : '';
		$logo_width = isset($settings['block_pages_by_url_string_logo_width']) ? (string) $settings['block_pages_by_url_string_logo_width'] : '';
		$message = isset($settings['block_pages_by_url_string_message']) ? (string) $settings['block_pages_by_url_string_message'] : '';
		$text_color = isset($settings['block_pages_by_url_string_text_color']) ? (string) $settings['block_pages_by_url_string_text_color'] : '';
		$redirect_url = isset($settings['block_pages_by_url_string_redirect_url']) ? (string) $settings['block_pages_by_url_string_redirect_url'] : '';
		if (function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-block-pages-by-url-string" data-um-active-selectors="#um-block-pages-by-url-string-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-hidden"></span>
				<h2><?php esc_html_e('Block Pages by URL String', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-block-pages-by-url-string-enabled" name="block_pages_by_url_string_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Hide page content by matching URL strings. Supports line-by-line match rules, exceptions, redirect, and branded blocked screen styling.', 'user-manager'); ?></p>
				</div>

				<div id="um-block-pages-by-url-string-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-block-pages-by-url-string-match-urls"><strong><?php esc_html_e("Page URL Strings to Match & Hide All Content", 'user-manager'); ?></strong></label>
						<textarea id="um-block-pages-by-url-string-match-urls" name="block_pages_by_url_string_match_urls" class="large-text code" rows="8"<?php echo $form_attr; ?>><?php echo esc_textarea($match_urls); ?></textarea>
						<p class="description"><?php esc_html_e('One per line. A slash (/) will match and block the entire front end.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label for="um-block-pages-by-url-string-exception-urls"><strong><?php esc_html_e("Exception Page URL Strings to Still Allow", 'user-manager'); ?></strong></label>
						<textarea id="um-block-pages-by-url-string-exception-urls" name="block_pages_by_url_string_exception_urls" class="large-text code" rows="8"<?php echo $form_attr; ?>><?php echo esc_textarea($exception_urls); ?></textarea>
						<p class="description"><?php esc_html_e('One per line. If a blocked URL also matches an exception string, it will stay accessible.', 'user-manager'); ?></p>
					</div>

					<hr />

					<div class="um-form-field">
						<label for="um-block-pages-by-url-string-background-color"><strong><?php esc_html_e('Optional Background Color', 'user-manager'); ?></strong></label>
						<input type="text" id="um-block-pages-by-url-string-background-color" name="block_pages_by_url_string_background_color" class="regular-text" value="<?php echo esc_attr($background_color); ?>" placeholder="#000000"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label for="um-block-pages-by-url-string-background-url"><strong><?php esc_html_e('Optional Background Image URL', 'user-manager'); ?></strong></label>
						<input type="url" id="um-block-pages-by-url-string-background-url" name="block_pages_by_url_string_background_url" class="regular-text" value="<?php echo esc_attr($background_url); ?>" placeholder="https://"<?php echo $form_attr; ?> />
						<p><button type="button" class="button" id="um-block-pages-by-url-string-background-url-upload"><?php esc_html_e('Select Image', 'user-manager'); ?></button></p>
					</div>

					<div class="um-form-field">
						<label for="um-block-pages-by-url-string-logo-url"><strong><?php esc_html_e('Optional Centered Logo Image URL', 'user-manager'); ?></strong></label>
						<input type="url" id="um-block-pages-by-url-string-logo-url" name="block_pages_by_url_string_logo_url" class="regular-text" value="<?php echo esc_attr($logo_url); ?>" placeholder="https://"<?php echo $form_attr; ?> />
						<p><button type="button" class="button" id="um-block-pages-by-url-string-logo-url-upload"><?php esc_html_e('Select Image', 'user-manager'); ?></button></p>
					</div>

					<div class="um-form-field">
						<label for="um-block-pages-by-url-string-logo-width"><strong><?php esc_html_e('Optional Centered Logo Image Width', 'user-manager'); ?></strong></label>
						<input type="text" id="um-block-pages-by-url-string-logo-width" name="block_pages_by_url_string_logo_width" class="regular-text" value="<?php echo esc_attr($logo_width); ?>" placeholder="100px"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label for="um-block-pages-by-url-string-message"><strong><?php esc_html_e('Optional Message at Top of Page', 'user-manager'); ?></strong></label>
						<input type="text" id="um-block-pages-by-url-string-message" name="block_pages_by_url_string_message" class="regular-text" value="<?php echo esc_attr($message); ?>" placeholder="<?php esc_attr_e('Page not found.', 'user-manager'); ?>"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label for="um-block-pages-by-url-string-text-color"><strong><?php esc_html_e('Optional Text Color', 'user-manager'); ?></strong></label>
						<input type="text" id="um-block-pages-by-url-string-text-color" name="block_pages_by_url_string_text_color" class="regular-text" value="<?php echo esc_attr($text_color); ?>" placeholder="#ffffff"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label for="um-block-pages-by-url-string-redirect-url"><strong><?php esc_html_e('Optional Redirect URL', 'user-manager'); ?></strong></label>
						<input type="url" id="um-block-pages-by-url-string-redirect-url" name="block_pages_by_url_string_redirect_url" class="regular-text" value="<?php echo esc_attr($redirect_url); ?>" placeholder="https://"<?php echo $form_attr; ?> />
						<p class="description"><?php esc_html_e('If set, matching blocked URLs redirect here instead of showing the blocked screen.', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<script>
		jQuery(function($) {
			if (typeof wp === 'undefined' || !wp.media) {
				return;
			}
			function bindImageSelector(buttonSelector, inputSelector) {
				var $button = $(buttonSelector);
				var $input = $(inputSelector);
				if (!$button.length || !$input.length) {
					return;
				}
				$button.on('click', function(e) {
					e.preventDefault();
					var frame = wp.media({
						title: <?php echo wp_json_encode(__('Select Image', 'user-manager')); ?>,
						button: { text: <?php echo wp_json_encode(__('Use Image', 'user-manager')); ?> },
						library: { type: 'image' },
						multiple: false
					});
					frame.on('select', function() {
						var attachment = frame.state().get('selection').first();
						if (!attachment) {
							return;
						}
						var url = attachment.get('url') || '';
						if (url) {
							$input.val(url).trigger('change');
						}
					});
					frame.open();
				});
			}
			bindImageSelector('#um-block-pages-by-url-string-background-url-upload', '#um-block-pages-by-url-string-background-url');
			bindImageSelector('#um-block-pages-by-url-string-logo-url-upload', '#um-block-pages-by-url-string-logo-url');
		});
		</script>
		<?php
	}
}

