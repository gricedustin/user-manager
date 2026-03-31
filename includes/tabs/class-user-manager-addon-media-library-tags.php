<?php
/**
 * Add-on card: Media Library Tags & Photo Gallery.
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
		$description_value = isset($settings['media_library_tag_gallery_description_value']) && is_string($settings['media_library_tag_gallery_description_value']) && $settings['media_library_tag_gallery_description_value'] !== ''
			? $settings['media_library_tag_gallery_description_value']
			: (string) $defaults['descriptionValue'];
		$show_tags_on_bulk_select = !empty($settings['media_library_tags_show_tags_on_thumbnails_bulk_select']);
		$sticky_bulk_toolbar_mobile = !empty($settings['media_library_tags_sticky_bulk_toolbar_mobile']);
		$hidden_frontend_tags = isset($settings['media_library_tag_gallery_hidden_frontend_tags']) && is_string($settings['media_library_tag_gallery_hidden_frontend_tags'])
			? $settings['media_library_tag_gallery_hidden_frontend_tags']
			: '';

		$image_sizes = User_Manager_Core::get_available_image_sizes_for_media_gallery();
		$description_display_options = User_Manager_Core::get_media_library_gallery_description_display_options();
		$description_value_options = User_Manager_Core::get_media_library_gallery_description_value_options();
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-media-library-tags" data-um-active-selectors="#um-media-library-tags-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-tag"></span>
				<h2><?php esc_html_e('Media Library Tags & Photo Gallery', 'user-manager'); ?></h2>
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
									<option value="standard" <?php selected($style, 'standard'); ?>><?php esc_html_e('Standard', 'user-manager'); ?></option>
									<option value="mosaic_grid" <?php selected($style, 'mosaic_grid'); ?>><?php esc_html_e('Mosaic Grid (Irregular Tiles)', 'user-manager'); ?></option>
									<option value="masonry_pinterest" <?php selected($style, 'masonry_pinterest'); ?>><?php esc_html_e('Masonry / Pinterest Layout', 'user-manager'); ?></option>
									<option value="uniform_grid" <?php selected($style, 'uniform_grid'); ?>><?php esc_html_e('Uniform Grid (Classic Gallery)', 'user-manager'); ?></option>
									<option value="justified_rows" <?php selected($style, 'justified_rows'); ?>><?php esc_html_e('Justified Row Layout', 'user-manager'); ?></option>
									<option value="square_crop" <?php selected($style, 'square_crop'); ?>><?php esc_html_e('Square CSS Crop', 'user-manager'); ?></option>
									<option value="wide_rectangle_crop" <?php selected($style, 'wide_rectangle_crop'); ?>><?php esc_html_e('Wide Rectangle CSS Crop', 'user-manager'); ?></option>
									<option value="tall_rectangle_crop" <?php selected($style, 'tall_rectangle_crop'); ?>><?php esc_html_e('Tall Rectangle CSS Crop', 'user-manager'); ?></option>
									<option value="circle_crop" <?php selected($style, 'circle_crop'); ?>><?php esc_html_e('Circle CSS Crop', 'user-manager'); ?></option>
									<option value="carousel_slider" <?php selected($style, 'carousel_slider'); ?>><?php esc_html_e('Carousel / Slider Gallery', 'user-manager'); ?></option>
									<option value="fullscreen_lightbox_grid" <?php selected($style, 'fullscreen_lightbox_grid'); ?>><?php esc_html_e('Fullscreen Lightbox Grid', 'user-manager'); ?></option>
									<option value="horizontal_scroll" <?php selected($style, 'horizontal_scroll'); ?>><?php esc_html_e('Horizontal Scroll Gallery', 'user-manager'); ?></option>
									<option value="polaroid_scrapbook" <?php selected($style, 'polaroid_scrapbook'); ?>><?php esc_html_e('Polaroid / Scrapbook Layout', 'user-manager'); ?></option>
									<option value="split_screen_feature" <?php selected($style, 'split_screen_feature'); ?>><?php esc_html_e('Split Screen Feature Gallery', 'user-manager'); ?></option>
									<option value="infinite_scroll" <?php selected($style, 'infinite_scroll'); ?>><?php esc_html_e('Infinite Scroll Gallery', 'user-manager'); ?></option>
									<option value="perspective_3d" <?php selected($style, 'perspective_3d'); ?>><?php esc_html_e('3D Perspective Gallery', 'user-manager'); ?></option>
									<option value="timeline_story" <?php selected($style, 'timeline_story'); ?>><?php esc_html_e('Timeline / Story Gallery', 'user-manager'); ?></option>
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
								<label for="um-media-library-tags-gallery-description-value"><?php esc_html_e('Description Value', 'user-manager'); ?></label>
								<select id="um-media-library-tags-gallery-description-value" name="media_library_tag_gallery_description_value"<?php echo $form_attr; ?>>
									<?php foreach ($description_value_options as $value_key => $value_label) : ?>
										<option value="<?php echo esc_attr($value_key); ?>" <?php selected($description_value, $value_key); ?>><?php echo esc_html($value_label); ?></option>
									<?php endforeach; ?>
								</select>
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

