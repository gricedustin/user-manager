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

		$image_sizes = User_Manager_Core::get_available_image_sizes_for_media_gallery();
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
									<option value="mosaic_grid" <?php selected($style, 'mosaic_grid'); ?>><?php esc_html_e('Mosaic Grid (Irregular Tiles)', 'user-manager'); ?></option>
									<option value="masonry_pinterest" <?php selected($style, 'masonry_pinterest'); ?>><?php esc_html_e('Masonry / Pinterest Layout', 'user-manager'); ?></option>
									<option value="uniform_grid" <?php selected($style, 'uniform_grid'); ?>><?php esc_html_e('Uniform Grid (Classic Gallery)', 'user-manager'); ?></option>
									<option value="justified_row" <?php selected($style, 'justified_row'); ?>><?php esc_html_e('Justified Row Layout', 'user-manager'); ?></option>
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

