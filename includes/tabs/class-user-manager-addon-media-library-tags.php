<?php
/**
 * Add-on card: Dynamic Photo Gallery with Media Library Tags.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Media_Library_Tags {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['media_library_tags_enabled']);

		$defaults = User_Manager_Core::get_media_library_tag_gallery_defaults();
		$gallery_block_enabled = !empty($settings['media_library_tag_gallery_block_enabled']);
		$columns_desktop = isset($settings['media_library_tag_gallery_columns_desktop'])
			? max(1, min(8, (int) $settings['media_library_tag_gallery_columns_desktop']))
			: (int) $defaults['columnsDesktop'];
		$columns_desktop_lt_50 = isset($settings['media_library_tag_gallery_columns_desktop_lt_50'])
			? max(1, min(8, (int) $settings['media_library_tag_gallery_columns_desktop_lt_50']))
			: (int) ($defaults['columnsDesktopLt50'] ?? $columns_desktop);
		$columns_desktop_lt_25 = isset($settings['media_library_tag_gallery_columns_desktop_lt_25'])
			? max(1, min(8, (int) $settings['media_library_tag_gallery_columns_desktop_lt_25']))
			: (int) ($defaults['columnsDesktopLt25'] ?? $columns_desktop);
		$columns_desktop_lt_10 = isset($settings['media_library_tag_gallery_columns_desktop_lt_10'])
			? max(1, min(8, (int) $settings['media_library_tag_gallery_columns_desktop_lt_10']))
			: (int) ($defaults['columnsDesktopLt10'] ?? $columns_desktop);
		$columns_mobile = isset($settings['media_library_tag_gallery_columns_mobile'])
			? max(1, min(4, (int) $settings['media_library_tag_gallery_columns_mobile']))
			: (int) $defaults['columnsMobile'];
		$sort_order = isset($settings['media_library_tag_gallery_sort_order']) && is_string($settings['media_library_tag_gallery_sort_order']) && $settings['media_library_tag_gallery_sort_order'] !== ''
			? $settings['media_library_tag_gallery_sort_order']
			: (string) $defaults['sortOrder'];
		$file_size = isset($settings['media_library_tag_gallery_file_size']) && is_string($settings['media_library_tag_gallery_file_size']) && $settings['media_library_tag_gallery_file_size'] !== ''
			? $settings['media_library_tag_gallery_file_size']
			: (string) $defaults['fileSize'];
		$style = isset($settings['media_library_tag_gallery_style']) && is_string($settings['media_library_tag_gallery_style']) && $settings['media_library_tag_gallery_style'] !== ''
			? $settings['media_library_tag_gallery_style']
			: (string) $defaults['style'];
		$page_limit = isset($settings['media_library_tag_gallery_page_limit'])
			? max(0, (int) $settings['media_library_tag_gallery_page_limit'])
			: (int) $defaults['pageLimit'];
		$link_to = isset($settings['media_library_tag_gallery_link_to']) && is_string($settings['media_library_tag_gallery_link_to']) && $settings['media_library_tag_gallery_link_to'] !== ''
			? $settings['media_library_tag_gallery_link_to']
			: (string) $defaults['linkTo'];
		$description_display = isset($settings['media_library_tag_gallery_description_display']) && is_string($settings['media_library_tag_gallery_description_display']) && $settings['media_library_tag_gallery_description_display'] !== ''
			? $settings['media_library_tag_gallery_description_display']
			: (string) $defaults['descriptionDisplay'];
		$album_description_position = isset($settings['media_library_tag_gallery_album_description_position']) && is_string($settings['media_library_tag_gallery_album_description_position']) && $settings['media_library_tag_gallery_album_description_position'] !== ''
			? sanitize_key((string) $settings['media_library_tag_gallery_album_description_position'])
			: (string) ($defaults['albumDescriptionPosition'] ?? 'none');
		$album_description_position_options = User_Manager_Core::get_media_library_gallery_album_description_position_options();
		if (!isset($album_description_position_options[$album_description_position])) {
			$album_description_position = 'none';
		}
		$description_value = isset($settings['media_library_tag_gallery_description_value']) && is_string($settings['media_library_tag_gallery_description_value']) && $settings['media_library_tag_gallery_description_value'] !== ''
			? $settings['media_library_tag_gallery_description_value']
			: (string) $defaults['descriptionValue'];
		$lightbox_prev_next_keyboard = isset($settings['media_library_tag_gallery_lightbox_prev_next_keyboard'])
			? !empty($settings['media_library_tag_gallery_lightbox_prev_next_keyboard'])
			: !empty($defaults['lightboxPrevNextKeyboard']);
		$lightbox_slideshow_button = isset($settings['media_library_tag_gallery_lightbox_slideshow_button'])
			? !empty($settings['media_library_tag_gallery_lightbox_slideshow_button'])
			: !empty($defaults['lightboxSlideshowButton']);
		$lightbox_slideshow_seconds = isset($settings['media_library_tag_gallery_lightbox_slideshow_seconds_per_photo'])
			? max(1, min(60, (int) $settings['media_library_tag_gallery_lightbox_slideshow_seconds_per_photo']))
			: max(1, min(60, (int) ($defaults['lightboxSlideshowSeconds'] ?? 3)));
		$lightbox_slideshow_transition_options = User_Manager_Core::get_media_library_gallery_lightbox_transition_options();
		$lightbox_slideshow_transition = isset($settings['media_library_tag_gallery_lightbox_slideshow_transition'])
			? sanitize_key((string) $settings['media_library_tag_gallery_lightbox_slideshow_transition'])
			: sanitize_key((string) ($defaults['lightboxSlideshowTransition'] ?? 'none'));
		if (!isset($lightbox_slideshow_transition_options[$lightbox_slideshow_transition])) {
			$lightbox_slideshow_transition = 'none';
		}
		$lightbox_modal_background_color = isset($settings['media_library_tag_gallery_lightbox_modal_background_color']) && is_string($settings['media_library_tag_gallery_lightbox_modal_background_color']) && $settings['media_library_tag_gallery_lightbox_modal_background_color'] !== ''
			? sanitize_hex_color((string) $settings['media_library_tag_gallery_lightbox_modal_background_color'])
			: sanitize_hex_color((string) ($defaults['lightboxModalBackgroundColor'] ?? '#000000'));
		if (!is_string($lightbox_modal_background_color) || $lightbox_modal_background_color === '') {
			$lightbox_modal_background_color = '#000000';
		}
		$lightbox_modal_text_color = isset($settings['media_library_tag_gallery_lightbox_modal_text_color']) && is_string($settings['media_library_tag_gallery_lightbox_modal_text_color']) && $settings['media_library_tag_gallery_lightbox_modal_text_color'] !== ''
			? sanitize_hex_color((string) $settings['media_library_tag_gallery_lightbox_modal_text_color'])
			: sanitize_hex_color((string) ($defaults['lightboxModalTextColor'] ?? '#ffffff'));
		if (!is_string($lightbox_modal_text_color) || $lightbox_modal_text_color === '') {
			$lightbox_modal_text_color = '#ffffff';
		}
		$disable_css_crop_threshold = isset($settings['media_library_tag_gallery_disable_css_crop_under_total'])
			? max(0, (int) $settings['media_library_tag_gallery_disable_css_crop_under_total'])
			: (int) ($defaults['disableCssCropUnderTotal'] ?? 0);
		$show_tags_on_bulk_select = !empty($settings['media_library_tags_show_tags_on_thumbnails_bulk_select']);
		$sticky_bulk_toolbar_mobile = !empty($settings['media_library_tags_sticky_bulk_toolbar_mobile']);
		$hidden_frontend_tags = isset($settings['media_library_tag_gallery_hidden_frontend_tags']) && is_string($settings['media_library_tag_gallery_hidden_frontend_tags'])
			? $settings['media_library_tag_gallery_hidden_frontend_tags']
			: '';

		$image_sizes = User_Manager_Core::get_available_image_sizes_for_media_gallery();
		$description_display_options = User_Manager_Core::get_media_library_gallery_description_display_options();
		$description_value_options = User_Manager_Core::get_media_library_gallery_description_value_options();
		$style_options = User_Manager_Core::get_media_library_gallery_style_options();
		$accent_color = isset($settings['media_library_tag_gallery_accent_color']) && is_string($settings['media_library_tag_gallery_accent_color']) && $settings['media_library_tag_gallery_accent_color'] !== ''
			? sanitize_hex_color($settings['media_library_tag_gallery_accent_color'])
			: (string) ($defaults['accentColor'] ?? '#ffffff');
		if (!is_string($accent_color) || $accent_color === '') {
			$accent_color = '#ffffff';
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-media-library-tags" data-um-active-selectors="#um-media-library-tags-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-tag"></span>
				<h2><?php esc_html_e('Dynamic Photo Gallery with Media Library Tags', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-media-library-tags-enabled" name="media_library_tags_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Adds a Library Tags taxonomy for Media, including a "Library Tags" submenu under Media, media list/grid filters, bulk tag assignment, per-item add/remove controls, and an optional tag-based photo gallery block for posts/pages.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-media-library-tags-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-media-library-tags-gallery-block-enabled" name="media_library_tag_gallery_block_enabled" value="1" <?php checked($gallery_block_enabled); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Activate Media Library Tag Gallery Block', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('Registers a gallery block for posts/pages that can show images from a selected Library Tag (or All tags by default).', 'user-manager'); ?>
						</p>
					</div>

					<div id="um-media-library-tags-gallery-settings" style="<?php echo $gallery_block_enabled ? '' : 'display:none;'; ?>">
						<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;gap:18px;margin-top:12px;">
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-columns-desktop"><?php esc_html_e('Number of Columns (Desktop)', 'user-manager'); ?></label>
								<input type="number" min="1" max="8" class="small-text" id="um-media-library-tags-gallery-columns-desktop" name="media_library_tag_gallery_columns_desktop" value="<?php echo esc_attr((string) $columns_desktop); ?>"<?php echo $form_attr; ?> />
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-columns-desktop-lt-50"><?php esc_html_e('Number of Columns (Desktop) if less than 50 photos', 'user-manager'); ?></label>
								<input type="number" min="1" max="8" class="small-text" id="um-media-library-tags-gallery-columns-desktop-lt-50" name="media_library_tag_gallery_columns_desktop_lt_50" value="<?php echo esc_attr((string) $columns_desktop_lt_50); ?>"<?php echo $form_attr; ?> />
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-columns-desktop-lt-25"><?php esc_html_e('Number of Columns (Desktop) if less than 25 photos', 'user-manager'); ?></label>
								<input type="number" min="1" max="8" class="small-text" id="um-media-library-tags-gallery-columns-desktop-lt-25" name="media_library_tag_gallery_columns_desktop_lt_25" value="<?php echo esc_attr((string) $columns_desktop_lt_25); ?>"<?php echo $form_attr; ?> />
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-columns-desktop-lt-10"><?php esc_html_e('Number of Columns (Desktop) if less than 10 photos', 'user-manager'); ?></label>
								<input type="number" min="1" max="8" class="small-text" id="um-media-library-tags-gallery-columns-desktop-lt-10" name="media_library_tag_gallery_columns_desktop_lt_10" value="<?php echo esc_attr((string) $columns_desktop_lt_10); ?>"<?php echo $form_attr; ?> />
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-columns-mobile"><?php esc_html_e('Number of Columns (Mobile)', 'user-manager'); ?></label>
								<input type="number" min="1" max="4" class="small-text" id="um-media-library-tags-gallery-columns-mobile" name="media_library_tag_gallery_columns_mobile" value="<?php echo esc_attr((string) $columns_mobile); ?>"<?php echo $form_attr; ?> />
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-sort-order"><?php esc_html_e('Sort Order', 'user-manager'); ?></label>
								<select id="um-media-library-tags-gallery-sort-order" name="media_library_tag_gallery_sort_order"<?php echo $form_attr; ?>>
									<option value="date_asc" <?php selected($sort_order, 'date_asc'); ?>><?php esc_html_e('Date ASC', 'user-manager'); ?></option>
									<option value="date_desc" <?php selected($sort_order, 'date_desc'); ?>><?php esc_html_e('Date DESC', 'user-manager'); ?></option>
									<option value="id_asc" <?php selected($sort_order, 'id_asc'); ?>><?php esc_html_e('ID ASC', 'user-manager'); ?></option>
									<option value="id_desc" <?php selected($sort_order, 'id_desc'); ?>><?php esc_html_e('ID DESC', 'user-manager'); ?></option>
									<option value="filename_asc" <?php selected($sort_order, 'filename_asc'); ?>><?php esc_html_e('Filename ASC', 'user-manager'); ?></option>
									<option value="filename_desc" <?php selected($sort_order, 'filename_desc'); ?>><?php esc_html_e('Filename DESC', 'user-manager'); ?></option>
									<option value="caption_asc" <?php selected($sort_order, 'caption_asc'); ?>><?php esc_html_e('Caption ASC', 'user-manager'); ?></option>
									<option value="caption_desc" <?php selected($sort_order, 'caption_desc'); ?>><?php esc_html_e('Caption DESC', 'user-manager'); ?></option>
									<option value="random" <?php selected($sort_order, 'random'); ?>><?php esc_html_e('Random', 'user-manager'); ?></option>
								</select>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-file-size"><?php esc_html_e('File Size', 'user-manager'); ?></label>
								<select id="um-media-library-tags-gallery-file-size" name="media_library_tag_gallery_file_size"<?php echo $form_attr; ?>>
									<?php foreach ($image_sizes as $size_key => $size_label) : ?>
										<option value="<?php echo esc_attr($size_key); ?>" <?php selected($file_size, $size_key); ?>><?php echo esc_html($size_label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-style"><?php esc_html_e('Style', 'user-manager'); ?></label>
								<select id="um-media-library-tags-gallery-style" name="media_library_tag_gallery_style"<?php echo $form_attr; ?>>
									<?php foreach ($style_options as $style_key => $style_label) : ?>
										<option value="<?php echo esc_attr((string) $style_key); ?>" <?php selected($style, (string) $style_key); ?>><?php echo esc_html((string) $style_label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-page-limit"><?php esc_html_e('Page Limit', 'user-manager'); ?></label>
								<input type="number" min="0" class="small-text" id="um-media-library-tags-gallery-page-limit" name="media_library_tag_gallery_page_limit" value="<?php echo esc_attr((string) $page_limit); ?>"<?php echo $form_attr; ?> />
								<p class="description" style="margin:6px 0 0;"><?php esc_html_e('Set to 0 for unlimited. If greater than 0, pagination is added when results exceed this limit.', 'user-manager'); ?></p>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-link-to"><?php esc_html_e('Link To', 'user-manager'); ?></label>
								<select id="um-media-library-tags-gallery-link-to" name="media_library_tag_gallery_link_to"<?php echo $form_attr; ?>>
									<option value="none" <?php selected($link_to, 'none'); ?>><?php esc_html_e('nothing', 'user-manager'); ?></option>
									<option value="lightbox" <?php selected($link_to, 'lightbox'); ?>><?php esc_html_e('lightbox', 'user-manager'); ?></option>
									<option value="media_permalink" <?php selected($link_to, 'media_permalink'); ?>><?php esc_html_e('open media library permalink', 'user-manager'); ?></option>
								</select>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-description-display"><?php esc_html_e('Description Display', 'user-manager'); ?></label>
								<select id="um-media-library-tags-gallery-description-display" name="media_library_tag_gallery_description_display"<?php echo $form_attr; ?>>
									<?php foreach ($description_display_options as $display_key => $display_label) : ?>
										<option value="<?php echo esc_attr($display_key); ?>" <?php selected($description_display, $display_key); ?>><?php echo esc_html($display_label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-album-description-position"><?php esc_html_e('Display Album Tag Description(s)', 'user-manager'); ?></label>
								<select id="um-media-library-tags-gallery-album-description-position" name="media_library_tag_gallery_album_description_position"<?php echo $form_attr; ?>>
									<?php foreach ($album_description_position_options as $position_key => $position_label) : ?>
										<option value="<?php echo esc_attr($position_key); ?>" <?php selected($album_description_position, $position_key); ?>><?php echo esc_html($position_label); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description" style="margin:6px 0 0;"><?php esc_html_e('Shows URL-selected Library Tag description paragraph(s) above or below the gallery, including per-tag Edit Tag Description links for admins. Multiple URL tags render multiple description paragraphs in URL order.', 'user-manager'); ?></p>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-description-value"><?php esc_html_e('Description Value', 'user-manager'); ?></label>
								<select id="um-media-library-tags-gallery-description-value" name="media_library_tag_gallery_description_value"<?php echo $form_attr; ?>>
									<?php foreach ($description_value_options as $value_key => $value_label) : ?>
										<option value="<?php echo esc_attr($value_key); ?>" <?php selected($description_value, $value_key); ?>><?php echo esc_html($value_label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="um-form-field">
								<label>
									<input type="checkbox" name="media_library_tag_gallery_lightbox_prev_next_keyboard" value="1" <?php checked($lightbox_prev_next_keyboard); ?><?php echo $form_attr; ?> />
									<?php esc_html_e('Add Previous & Next Links in Lightbox Window and Allow Keyboard Arrows Shortcut', 'user-manager'); ?>
								</label>
							</div>
							<div class="um-form-field">
								<label>
									<input type="checkbox" name="media_library_tag_gallery_lightbox_slideshow_button" value="1" <?php checked($lightbox_slideshow_button); ?><?php echo $form_attr; ?> />
									<?php esc_html_e('Add a Play Slideshow Button in Lightbox Window', 'user-manager'); ?>
								</label>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-lightbox-slideshow-seconds"><?php esc_html_e('Slideshow Seconds Per Photo', 'user-manager'); ?></label>
								<input type="number" min="1" max="60" class="small-text" id="um-media-library-tags-gallery-lightbox-slideshow-seconds" name="media_library_tag_gallery_lightbox_slideshow_seconds_per_photo" value="<?php echo esc_attr((string) $lightbox_slideshow_seconds); ?>"<?php echo $form_attr; ?> />
								<p class="description" style="margin:6px 0 0;"><?php esc_html_e('How many seconds each photo stays visible during slideshow playback.', 'user-manager'); ?></p>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-lightbox-slideshow-transition"><?php esc_html_e('Slideshow Transition (None, Crossfade, Slide to Left)', 'user-manager'); ?></label>
								<select id="um-media-library-tags-gallery-lightbox-slideshow-transition" name="media_library_tag_gallery_lightbox_slideshow_transition"<?php echo $form_attr; ?>>
									<?php foreach ($lightbox_slideshow_transition_options as $transition_key => $transition_label) : ?>
										<option value="<?php echo esc_attr((string) $transition_key); ?>" <?php selected($lightbox_slideshow_transition, (string) $transition_key); ?>><?php echo esc_html((string) $transition_label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-lightbox-modal-bg-color"><?php esc_html_e('Lightbox Modal Background Color', 'user-manager'); ?></label>
								<input type="color" id="um-media-library-tags-gallery-lightbox-modal-bg-color" name="media_library_tag_gallery_lightbox_modal_background_color" value="<?php echo esc_attr((string) $lightbox_modal_background_color); ?>"<?php echo $form_attr; ?> />
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-lightbox-modal-text-color"><?php esc_html_e('Lightbox Modal Text Color', 'user-manager'); ?></label>
								<input type="color" id="um-media-library-tags-gallery-lightbox-modal-text-color" name="media_library_tag_gallery_lightbox_modal_text_color" value="<?php echo esc_attr((string) $lightbox_modal_text_color); ?>"<?php echo $form_attr; ?> />
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-disable-css-crop-below-total"><?php esc_html_e('Do Not CSS Crop Any Images if Gallery Photos Total is Less Than...', 'user-manager'); ?></label>
								<input type="number" min="0" class="small-text" id="um-media-library-tags-gallery-disable-css-crop-below-total" name="media_library_tag_gallery_disable_css_crop_under_total" value="<?php echo esc_attr((string) $disable_css_crop_threshold); ?>"<?php echo $form_attr; ?> />
								<p class="description" style="margin:6px 0 0;"><?php esc_html_e('Set to 0 to keep CSS crop styles always active. If the gallery total is below this number, CSS crop styles are disabled and images use natural proportions.', 'user-manager'); ?></p>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-gallery-accent-color"><?php esc_html_e('Accent Color (frames/backgrounds)', 'user-manager'); ?></label>
								<input type="color" id="um-media-library-tags-gallery-accent-color" name="media_library_tag_gallery_accent_color" value="<?php echo esc_attr((string) $accent_color); ?>"<?php echo $form_attr; ?> />
								<p class="description" style="margin:6px 0 0;"><?php esc_html_e('Used by styles with frame/background surfaces (for example Polaroid cards, split-screen panels, and carousel/split controls) so white backgrounds can be replaced for dark-mode sites.', 'user-manager'); ?></p>
							</div>
							<div class="um-form-field">
								<label>
									<input type="checkbox" name="media_library_tags_show_tags_on_thumbnails_bulk_select" value="1" <?php checked($show_tags_on_bulk_select); ?><?php echo $form_attr; ?> />
									<?php esc_html_e('Show Tags on Thumbnails when Bulk Selecting', 'user-manager'); ?>
								</label>
								<p class="description" style="margin:6px 0 0;"><?php esc_html_e('Displays each selected media item\'s Library Tags on its thumbnail while in Bulk Select mode.', 'user-manager'); ?></p>
							</div>
							<div class="um-form-field">
								<label>
									<input type="checkbox" name="media_library_tags_sticky_bulk_toolbar_mobile" value="1" <?php checked($sticky_bulk_toolbar_mobile); ?><?php echo $form_attr; ?> />
									<?php esc_html_e('Keep Media Library bulk tools header visible on mobile while scrolling', 'user-manager'); ?>
								</label>
								<p class="description" style="margin:6px 0 0;"><?php esc_html_e('Pins the Media Library header/toolbar on small screens so Bulk Select controls stay accessible without scrolling back to the top.', 'user-manager'); ?></p>
							</div>
							<div class="um-form-field">
								<label for="um-media-library-tags-hidden-frontend-tags"><?php esc_html_e('Tags to hide from front end gallery', 'user-manager'); ?></label>
								<input type="text" class="regular-text" id="um-media-library-tags-hidden-frontend-tags" name="media_library_tag_gallery_hidden_frontend_tags" value="<?php echo esc_attr($hidden_frontend_tags); ?>" placeholder="hide, internal-only"<?php echo $form_attr; ?> />
								<p class="description" style="margin:6px 0 0;"><?php esc_html_e('Comma-separated tag slugs/names that should never display in front-end galleries. Example: create a tag named "hide" and add hide here to always exclude those images from front-end gallery output.', 'user-manager'); ?></p>
							</div>
						</div>
						<p class="description" style="margin-top:10px;">
							<?php esc_html_e('These settings are used as defaults for every Media Library Tag Gallery block.', 'user-manager'); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

