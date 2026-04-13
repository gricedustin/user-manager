<?php
/**
 * Media Library Tags add-on helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Media_Library_Tags_Trait {

	/**
	 * Register runtime hooks for Media Library Tags add-on.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_media_library_tags(array $settings): void {
		if (empty($settings['media_library_tags_enabled'])) {
			return;
		}

		$taxonomy = self::media_library_tags_taxonomy();
		add_action('init', [__CLASS__, 'register_media_library_tags_taxonomy'], 20);
		if (!empty($settings['media_library_tag_gallery_block_enabled'])) {
			add_action('init', [__CLASS__, 'register_media_library_tags_gallery_block'], 20);
			add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_media_library_tags_gallery_block_editor_assets']);
		}
		add_action('restrict_manage_posts', [__CLASS__, 'render_media_library_tag_filter_controls'], 20, 2);
		add_filter('manage_upload_columns', [__CLASS__, 'add_media_library_tags_list_table_column']);
		add_action('manage_media_custom_column', [__CLASS__, 'render_media_library_tags_list_table_column'], 10, 2);
		add_action('pre_get_posts', [__CLASS__, 'filter_media_library_queries_by_tag']);
		add_filter('ajax_query_attachments_args', [__CLASS__, 'filter_media_library_ajax_query_by_tag']);
		add_filter('posts_clauses', [__CLASS__, 'maybe_apply_media_library_lightbox_view_sort_clauses'], 20, 2);
		add_action('admin_init', [__CLASS__, 'handle_media_library_bulk_apply_tag']);
		add_action('admin_notices', [__CLASS__, 'maybe_render_media_library_tags_admin_notice']);
		add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_media_library_tags_admin_assets']);
		add_action('wp_ajax_user_manager_bulk_apply_media_library_tag', [__CLASS__, 'ajax_bulk_apply_media_library_tag']);
		add_action('wp_ajax_user_manager_track_media_lightbox_view', [__CLASS__, 'ajax_track_media_library_lightbox_view']);
		add_action('wp_ajax_nopriv_user_manager_track_media_lightbox_view', [__CLASS__, 'ajax_track_media_library_lightbox_view']);
		add_filter('attachment_fields_to_edit', [__CLASS__, 'add_media_library_tags_attachment_field'], 10, 2);
		add_filter('attachment_fields_to_save', [__CLASS__, 'save_media_library_tags_attachment_field'], 10, 2);
		add_filter('the_content', [__CLASS__, 'replace_media_library_tag_placeholders_in_content'], 20);
		add_filter('the_title', [__CLASS__, 'replace_media_library_tag_placeholders_in_title'], 20, 2);
		add_filter('document_title_parts', [__CLASS__, 'replace_media_library_tag_placeholders_in_document_title_parts'], 20);
		add_filter('pre_get_document_title', [__CLASS__, 'replace_media_library_tag_placeholders_in_document_title'], 20);
		add_filter('body_class', [__CLASS__, 'add_media_library_tag_body_classes_for_bullets'], 20, 1);
		add_action('admin_bar_menu', [__CLASS__, 'add_media_library_tag_admin_bar_shortcut'], 101);
		add_action('admin_menu', [__CLASS__, 'register_media_library_tags_bulk_editor_submenu']);
		add_action('admin_menu', [__CLASS__, 'register_media_library_tag_reports_submenu']);
		add_action('admin_menu', [__CLASS__, 'register_media_library_tag_groups_submenu']);
		add_action('admin_post_user_manager_media_library_tag_groups_save', [__CLASS__, 'handle_media_library_tag_groups_save']);
		add_action('admin_post_user_manager_media_library_tag_groups_delete', [__CLASS__, 'handle_media_library_tag_groups_delete']);
		add_action('init', [__CLASS__, 'maybe_migrate_legacy_term_youtube_links_to_video_library_on_init'], 30);
		if (!empty($settings['media_library_tag_video_library_enabled'])) {
			add_action('admin_menu', [__CLASS__, 'register_media_library_tag_video_library_submenu']);
			add_action('admin_post_user_manager_media_library_tag_video_library_save', [__CLASS__, 'handle_media_library_tag_video_library_save']);
			add_action('admin_post_user_manager_media_library_tag_video_library_bulk_save', [__CLASS__, 'handle_media_library_tag_video_library_bulk_save']);
			add_action('admin_post_user_manager_media_library_tag_video_library_delete', [__CLASS__, 'handle_media_library_tag_video_library_delete']);
		}
		add_action('admin_post_user_manager_media_library_tags_bulk_editor_save', [__CLASS__, 'handle_media_library_tags_bulk_editor_save']);
		add_action($taxonomy . '_add_form_fields', [__CLASS__, 'render_media_library_tag_featured_image_add_form_fields']);
		add_action($taxonomy . '_edit_form_fields', [__CLASS__, 'render_media_library_tag_featured_image_edit_form_fields']);
		add_action('created_' . $taxonomy, [__CLASS__, 'save_media_library_tag_featured_image_term_meta']);
		add_action('edited_' . $taxonomy, [__CLASS__, 'save_media_library_tag_featured_image_term_meta']);
		add_action('edit_terms', [__CLASS__, 'capture_media_library_tag_slug_before_update'], 10, 3);
		add_action('edited_terms', [__CLASS__, 'maybe_sync_media_library_tag_slug_after_update'], 10, 3);
		add_action('wp_head', [__CLASS__, 'print_media_library_tags_inline_lightbox_bootstrap'], 1);
		add_shortcode('um_media_library_tag_videos', [__CLASS__, 'render_media_library_tag_videos_shortcode']);
	}

	/**
	 * Add Library Tags column to Media list table.
	 *
	 * @param array<string,string> $columns Existing columns.
	 * @return array<string,string>
	 */
	public static function add_media_library_tags_list_table_column(array $columns): array {
		$new_columns = [];
		foreach ($columns as $key => $label) {
			$new_columns[$key] = $label;
			if ($key === 'author') {
				$new_columns['um_media_library_tags'] = __('Library Tags', 'user-manager');
			}
		}
		if (!isset($new_columns['um_media_library_tags'])) {
			$new_columns['um_media_library_tags'] = __('Library Tags', 'user-manager');
		}
		return $new_columns;
	}

	/**
	 * Render Library Tags list-table column value.
	 */
	public static function render_media_library_tags_list_table_column(string $column_name, int $post_id): void {
		if ($column_name !== 'um_media_library_tags') {
			return;
		}
		$terms = wp_get_object_terms($post_id, self::media_library_tags_taxonomy(), ['fields' => 'names']);
		if (!is_array($terms) || empty($terms)) {
			echo '—';
			return;
		}
		echo esc_html(implode(', ', array_map('strval', $terms)));
	}

	/**
	 * Register non-hierarchical taxonomy for attachment tags.
	 */
	public static function register_media_library_tags_taxonomy(): void {
		$taxonomy = self::media_library_tags_taxonomy();
		if (taxonomy_exists($taxonomy)) {
			return;
		}

		register_taxonomy(
			$taxonomy,
			['attachment'],
			[
				'labels' => [
					'name' => __('Library Tags', 'user-manager'),
					'singular_name' => __('Library Tag', 'user-manager'),
					'search_items' => __('Search Library Tags', 'user-manager'),
					'all_items' => __('All tags', 'user-manager'),
					'edit_item' => __('Edit Library Tag', 'user-manager'),
					'update_item' => __('Update Library Tag', 'user-manager'),
					'add_new_item' => __('Add New Library Tag', 'user-manager'),
					'new_item_name' => __('New Library Tag Name', 'user-manager'),
					'menu_name' => __('Library Tags', 'user-manager'),
				],
				'public' => false,
				'show_ui' => true,
				'show_admin_column' => true,
				'show_in_menu' => 'upload.php',
				'show_in_nav_menus' => false,
				'show_tagcloud' => false,
				'show_in_rest' => true,
				'hierarchical' => false,
				'rewrite' => false,
				'query_var' => 'um_media_library_tag',
				'update_count_callback' => '_update_post_term_count',
				'capabilities' => [
					'manage_terms' => 'upload_files',
					'edit_terms' => 'upload_files',
					'delete_terms' => 'upload_files',
					'assign_terms' => 'upload_files',
				],
			]
		);
	}

	/**
	 * Register "Bulk Editor" submenu under Library Tags taxonomy screen.
	 */
	public static function register_media_library_tags_bulk_editor_submenu(): void {
		$taxonomy = self::media_library_tags_taxonomy();
		if (!taxonomy_exists($taxonomy)) {
			// Ensure taxonomy is available even if hook timing/order changes.
			self::register_media_library_tags_taxonomy();
		}

		add_submenu_page(
			'upload.php',
			__('Library Tags Bulk Editor', 'user-manager'),
			__('Bulk Editor', 'user-manager'),
			'upload_files',
			'um-media-library-tags-bulk-editor',
			[__CLASS__, 'render_media_library_tags_bulk_editor_page']
		);
	}

	/**
	 * Register "Tag Reports" submenu under Media.
	 */
	public static function register_media_library_tag_reports_submenu(): void {
		add_submenu_page(
			'upload.php',
			__('Tag Reports', 'user-manager'),
			__('Tag Reports', 'user-manager'),
			'upload_files',
			'um-media-library-tag-reports',
			[__CLASS__, 'render_media_library_tag_reports_page']
		);
	}

	/**
	 * Render Media > Tag Reports screen.
	 */
	public static function render_media_library_tag_reports_page(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to view Tag Reports.', 'user-manager'));
		}
		$active_tab = isset($_GET['report_tab']) ? sanitize_key((string) wp_unslash($_GET['report_tab'])) : 'images';
		if (!in_array($active_tab, ['images', 'tags'], true)) {
			$active_tab = 'images';
		}
		$base_url = add_query_arg(['page' => 'um-media-library-tag-reports'], admin_url('upload.php'));
		$image_rows = self::get_media_library_most_viewed_images_for_report(200);
		$tag_rows = self::get_media_library_most_viewed_album_tags_for_report(200);
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Tag Reports', 'user-manager'); ?></h1>
			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_url(add_query_arg(['report_tab' => 'images'], $base_url)); ?>" class="nav-tab <?php echo $active_tab === 'images' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Most Viewed Images', 'user-manager'); ?></a>
				<a href="<?php echo esc_url(add_query_arg(['report_tab' => 'tags'], $base_url)); ?>" class="nav-tab <?php echo $active_tab === 'tags' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Most Viewed Album Tags', 'user-manager'); ?></a>
			</h2>

			<?php if ($active_tab === 'images') : ?>
				<table class="widefat striped" style="margin-top:14px;">
					<thead>
						<tr>
							<th style="width:72px;"><?php esc_html_e('Image', 'user-manager'); ?></th>
							<th><?php esc_html_e('Title', 'user-manager'); ?></th>
							<th><?php esc_html_e('Lightbox Views', 'user-manager'); ?></th>
							<th><?php esc_html_e('Year', 'user-manager'); ?></th>
							<th><?php esc_html_e('Month', 'user-manager'); ?></th>
							<th><?php esc_html_e('Week', 'user-manager'); ?></th>
							<th><?php esc_html_e('Day', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($image_rows)) : ?>
							<tr><td colspan="7"><?php esc_html_e('No image view data yet.', 'user-manager'); ?></td></tr>
						<?php else : ?>
							<?php foreach ($image_rows as $row) : ?>
								<tr>
									<td><?php echo wp_kses_post((string) ($row['thumbHtml'] ?? '')); ?></td>
									<td>
										<strong><?php echo esc_html((string) ($row['title'] ?? '')); ?></strong>
										<?php if (!empty($row['editUrl'])) : ?>
											<div><a href="<?php echo esc_url((string) $row['editUrl']); ?>"><?php esc_html_e('Edit', 'user-manager'); ?></a></div>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['total'] ?? 0))); ?></td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['year'] ?? 0))); ?></td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['month'] ?? 0))); ?></td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['week'] ?? 0))); ?></td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['day'] ?? 0))); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			<?php else : ?>
				<table class="widefat striped" style="margin-top:14px;">
					<thead>
						<tr>
							<th><?php esc_html_e('Album Tag', 'user-manager'); ?></th>
							<th><?php esc_html_e('Slug', 'user-manager'); ?></th>
							<th><?php esc_html_e('Album Tag Views', 'user-manager'); ?></th>
							<th><?php esc_html_e('Year', 'user-manager'); ?></th>
							<th><?php esc_html_e('Month', 'user-manager'); ?></th>
							<th><?php esc_html_e('Week', 'user-manager'); ?></th>
							<th><?php esc_html_e('Day', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($tag_rows)) : ?>
							<tr><td colspan="7"><?php esc_html_e('No album tag view data yet.', 'user-manager'); ?></td></tr>
						<?php else : ?>
							<?php foreach ($tag_rows as $row) : ?>
								<tr>
									<td>
										<strong><?php echo esc_html((string) ($row['name'] ?? '')); ?></strong>
										<?php if (!empty($row['editUrl'])) : ?>
											<div><a href="<?php echo esc_url((string) $row['editUrl']); ?>"><?php esc_html_e('Edit', 'user-manager'); ?></a></div>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html((string) ($row['slug'] ?? '')); ?></td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['total'] ?? 0))); ?></td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['year'] ?? 0))); ?></td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['month'] ?? 0))); ?></td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['week'] ?? 0))); ?></td>
									<td><?php echo esc_html(number_format_i18n((int) ($row['day'] ?? 0))); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render Library Tags Bulk Editor screen.
	 */
	public static function render_media_library_tags_bulk_editor_page(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage Library Tags.', 'user-manager'));
		}
		self::enqueue_media_library_tags_bulk_editor_featured_image_assets();

		$taxonomy = self::media_library_tags_taxonomy();
		$terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]);
		if (is_wp_error($terms) || !is_array($terms)) {
			$terms = [];
		}
		$terms = array_values(array_filter($terms, static function ($term): bool {
			if (!($term instanceof WP_Term)) {
				return false;
			}
			$term_name = trim((string) $term->name);
			if ($term_name === '') {
				return false;
			}
			// Bulk Editor should show only individual tags, not comma-combined tag names.
			return strpos($term_name, ',') === false;
		}));
		$menu_slug_matches = self::get_media_library_bulk_editor_menu_slug_matches($terms);
		$live_in_nav_terms = [];
		$remaining_terms = [];
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$term_slug = sanitize_title((string) $term->slug);
			if ($term_slug !== '' && !empty($menu_slug_matches[$term_slug])) {
				$live_in_nav_terms[] = $term;
				continue;
			}
			$remaining_terms[] = $term;
		}

		$updated_count = isset($_GET['um_bulk_updated']) ? absint(wp_unslash($_GET['um_bulk_updated'])) : 0;
		if ($updated_count > 0) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: %d: number of updated tags */
						__('Updated %d Library Tag(s).', 'user-manager'),
						$updated_count
					)
				)
			);
		}
		$user_id = get_current_user_id();
		$errors = get_transient('um_media_library_tags_bulk_editor_errors_' . $user_id);
		if (is_array($errors) && !empty($errors)) {
			delete_transient('um_media_library_tags_bulk_editor_errors_' . $user_id);
			echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__('Some tags could not be updated:', 'user-manager') . '</strong></p><ul style="margin-left: 18px; list-style: disc;">';
			foreach ($errors as $error_message) {
				echo '<li>' . esc_html((string) $error_message) . '</li>';
			}
			echo '</ul></div>';
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Library Tags Bulk Editor', 'user-manager'); ?></h1>
			<p><?php esc_html_e('Edit all Library Tag titles, slugs, descriptions, bullets, and featured images in one screen, then save all changes at once. Video links are now managed in Media > Video Library.', 'user-manager'); ?></p>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="user_manager_media_library_tags_bulk_editor_save" />
				<?php wp_nonce_field('user_manager_media_library_tags_bulk_editor_save', 'user_manager_media_library_tags_bulk_editor_nonce'); ?>
				<table class="widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 34%;"><?php esc_html_e('Tag Details', 'user-manager'); ?></th>
							<th style="width: 33%;"><?php esc_html_e('Description', 'user-manager'); ?></th>
							<th style="width: 33%;"><?php esc_html_e('Bullets', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($terms)) : ?>
							<tr><td colspan="3"><?php esc_html_e('No Library Tags found.', 'user-manager'); ?></td></tr>
						<?php else : ?>
							<?php if (!empty($live_in_nav_terms)) : ?>
								<tr class="um-bulk-editor-section-row">
									<td colspan="3"><strong><?php esc_html_e('Live in Menu Navigation', 'user-manager'); ?></strong></td>
								</tr>
								<?php foreach ($live_in_nav_terms as $term) : ?>
									<?php if (!($term instanceof WP_Term)) { continue; } ?>
									<?php
									$featured_image_id = self::get_media_library_tag_featured_image_id((int) $term->term_id);
									$term_bullets = implode("\n", self::get_media_library_tag_bullets_lines((int) $term->term_id));
									?>
									<tr>
										<td>
											<div class="um-bulk-editor-details-stack">
												<div class="um-bulk-editor-details-field">
													<label class="um-bulk-editor-details-label"><?php esc_html_e('Tag Title', 'user-manager'); ?></label>
													<input type="text" class="regular-text" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][name]" value="<?php echo esc_attr((string) $term->name); ?>" />
												</div>
												<div class="um-bulk-editor-details-field">
													<?php $term_slug = sanitize_title((string) $term->slug); ?>
													<label class="um-bulk-editor-details-label"><?php esc_html_e('Slug', 'user-manager'); ?></label>
													<input type="text" class="regular-text" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][slug]" value="<?php echo esc_attr((string) $term->slug); ?>" />
													<?php if ($term_slug !== '' && !empty($menu_slug_matches[$term_slug])) : ?>
														<div style="margin-top:6px;">
															<span class="um-menu-live-navigation-badge"><?php esc_html_e('Live in Menu Navigation', 'user-manager'); ?></span>
														</div>
													<?php endif; ?>
												</div>
												<div class="um-bulk-editor-details-field">
													<label class="um-bulk-editor-details-label"><?php esc_html_e('Featured Image', 'user-manager'); ?></label>
													<?php echo self::render_media_library_tag_featured_image_bulk_editor_control_html((int) $term->term_id, $featured_image_id); ?>
												</div>
												<div class="um-bulk-editor-details-field">
													<label class="um-bulk-editor-details-label"><?php esc_html_e('Video Library', 'user-manager'); ?></label>
													<?php echo self::render_media_library_tag_video_library_summary_cell_html((string) $term->slug); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</div>
											</div>
										</td>
										<td class="um-bulk-editor-description-cell">
											<textarea rows="10" class="um-bulk-editor-description-textarea" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][description]"><?php echo esc_textarea((string) $term->description); ?></textarea>
										</td>
										<td class="um-bulk-editor-description-cell">
											<textarea rows="10" class="um-bulk-editor-description-textarea" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][bullets]" placeholder="<?php esc_attr_e('One bullet per line', 'user-manager'); ?>"><?php echo esc_textarea((string) $term_bullets); ?></textarea>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php if (!empty($remaining_terms)) : ?>
								<tr class="um-bulk-editor-section-row">
									<td colspan="3"><strong><?php esc_html_e('All Other Tags', 'user-manager'); ?></strong></td>
								</tr>
								<?php foreach ($remaining_terms as $term) : ?>
									<?php if (!($term instanceof WP_Term)) { continue; } ?>
									<?php
									$featured_image_id = self::get_media_library_tag_featured_image_id((int) $term->term_id);
									$term_bullets = implode("\n", self::get_media_library_tag_bullets_lines((int) $term->term_id));
									?>
									<tr>
										<td>
											<div class="um-bulk-editor-details-stack">
												<div class="um-bulk-editor-details-field">
													<label class="um-bulk-editor-details-label"><?php esc_html_e('Tag Title', 'user-manager'); ?></label>
													<input type="text" class="regular-text" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][name]" value="<?php echo esc_attr((string) $term->name); ?>" />
												</div>
												<div class="um-bulk-editor-details-field">
													<?php $term_slug = sanitize_title((string) $term->slug); ?>
													<label class="um-bulk-editor-details-label"><?php esc_html_e('Slug', 'user-manager'); ?></label>
													<input type="text" class="regular-text" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][slug]" value="<?php echo esc_attr((string) $term->slug); ?>" />
													<?php if ($term_slug !== '' && !empty($menu_slug_matches[$term_slug])) : ?>
														<div style="margin-top:6px;">
															<span class="um-menu-live-navigation-badge"><?php esc_html_e('Live in Menu Navigation', 'user-manager'); ?></span>
														</div>
													<?php endif; ?>
												</div>
												<div class="um-bulk-editor-details-field">
													<label class="um-bulk-editor-details-label"><?php esc_html_e('Featured Image', 'user-manager'); ?></label>
													<?php echo self::render_media_library_tag_featured_image_bulk_editor_control_html((int) $term->term_id, $featured_image_id); ?>
												</div>
												<div class="um-bulk-editor-details-field">
													<label class="um-bulk-editor-details-label"><?php esc_html_e('Video Library', 'user-manager'); ?></label>
													<?php echo self::render_media_library_tag_video_library_summary_cell_html((string) $term->slug); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</div>
											</div>
										</td>
										<td class="um-bulk-editor-description-cell">
											<textarea rows="10" class="um-bulk-editor-description-textarea" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][description]"><?php echo esc_textarea((string) $term->description); ?></textarea>
										</td>
										<td class="um-bulk-editor-description-cell">
											<textarea rows="10" class="um-bulk-editor-description-textarea" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][bullets]" placeholder="<?php esc_attr_e('One bullet per line', 'user-manager'); ?>"><?php echo esc_textarea((string) $term_bullets); ?></textarea>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						<?php endif; ?>
					</tbody>
				</table>
				<?php submit_button(__('Save All', 'user-manager')); ?>
			</form>
		</div>
		<style>
		.um-menu-live-navigation-badge {
			display: inline-block;
			font-size: 11px;
			line-height: 1.2;
			padding: 2px 7px;
			border-radius: 999px;
			background: #edf7ed;
			color: #1f6f43;
			border: 1px solid #c8e6ce;
			font-weight: 600;
		}
		.um-bulk-editor-section-row td {
			background: #f6f7f7;
			border-top: 1px solid #dcdcde;
			font-size: 12px;
			letter-spacing: 0.02em;
			text-transform: uppercase;
			color: #50575e;
			padding-top: 8px;
			padding-bottom: 8px;
		}
		.um-bulk-editor-details-stack {
			display: grid;
			gap: 10px;
		}
		.um-bulk-editor-details-field {
			margin: 0;
		}
		.um-bulk-editor-details-label {
			display: block;
			font-weight: 600;
			margin-bottom: 4px;
		}
		.um-bulk-editor-description-cell {
			vertical-align: top;
		}
		.um-bulk-editor-description-textarea {
			min-height: 220px;
			resize: vertical;
			line-height: 1.45;
		}
		</style>
		<?php
	}

	/**
	 * Build slug=>menu-match map for Bulk Editor "Live in Menu Navigation" badges.
	 *
	 * @param array<int,mixed> $terms
	 * @return array<string,bool>
	 */
	private static function get_media_library_bulk_editor_menu_slug_matches(array $terms): array {
		$slugs = [];
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$slug = sanitize_title((string) $term->slug);
			if ($slug === '') {
				continue;
			}
			$slugs[] = $slug;
		}
		return self::get_media_library_menu_slug_matches($slugs);
	}

	/**
	 * Build slug=>menu-match map by scanning active menu item URLs.
	 *
	 * @param array<int,string> $slugs
	 * @return array<string,bool>
	 */
	private static function get_media_library_menu_slug_matches(array $slugs): array {
		$slug_map = [];
		foreach ($slugs as $slug_value) {
			$slug = sanitize_title((string) $slug_value);
			if ($slug === '') {
				continue;
			}
			$slug_map[$slug] = false;
		}
		if (empty($slug_map)) {
			return [];
		}

		$menu_locations = get_nav_menu_locations();
		if (!is_array($menu_locations) || empty($menu_locations)) {
			return $slug_map;
		}

		$menu_ids = array_values(array_unique(array_filter(array_map('absint', $menu_locations))));
		$all_menus = wp_get_nav_menus(['hide_empty' => false]);
		if (is_array($all_menus) && !empty($all_menus)) {
			$all_menu_ids = [];
			foreach ($all_menus as $menu_term) {
				if (!($menu_term instanceof WP_Term)) {
					continue;
				}
				$menu_term_id = absint($menu_term->term_id);
				if ($menu_term_id > 0) {
					$all_menu_ids[] = $menu_term_id;
				}
			}
			$menu_ids = array_values(array_unique(array_merge($menu_ids, $all_menu_ids)));
		}
		if (empty($menu_ids)) {
			return $slug_map;
		}

		foreach ($menu_ids as $menu_id) {
			$items = wp_get_nav_menu_items($menu_id, ['post_status' => 'publish']);
			if (!is_array($items) || empty($items)) {
				continue;
			}
			foreach ($items as $item) {
				if (!($item instanceof WP_Post)) {
					continue;
				}
				$url = isset($item->url) ? (string) $item->url : '';
				if ($url === '') {
					$url = (string) get_post_meta((int) $item->ID, '_menu_item_url', true);
				}
				if ($url === '') {
					continue;
				}
				$decoded_url = rawurldecode((string) $url);
				$lower_url = strtolower($decoded_url);
				foreach ($slug_map as $slug => $matched) {
					if ($matched) {
						continue;
					}
					$slug_pattern = '/(^|[\/\?\&\=\-\_\.\,\+\s])' . preg_quote(strtolower($slug), '/') . '($|[\/\?\&\=\-\_\.\,\+\s])/';
					if (preg_match($slug_pattern, $lower_url) === 1) {
						$slug_map[$slug] = true;
					}
				}
			}
		}

		return $slug_map;
	}

	/**
	 * Save Library Tags bulk editor rows.
	 */
	public static function handle_media_library_tags_bulk_editor_save(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage Library Tags.', 'user-manager'));
		}
		check_admin_referer('user_manager_media_library_tags_bulk_editor_save', 'user_manager_media_library_tags_bulk_editor_nonce');

		$taxonomy = self::media_library_tags_taxonomy();
		$raw_terms = isset($_POST['um_bulk_terms']) && is_array($_POST['um_bulk_terms']) ? wp_unslash($_POST['um_bulk_terms']) : [];
		$updated = 0;
		$errors = [];

		foreach ($raw_terms as $raw_term_id => $raw_term_data) {
			$term_id = absint($raw_term_id);
			if ($term_id <= 0 || !is_array($raw_term_data)) {
				continue;
			}

			$current_term = get_term($term_id, $taxonomy);
			if (!($current_term instanceof WP_Term)) {
				continue;
			}

			$name = isset($raw_term_data['name']) ? trim(sanitize_text_field((string) $raw_term_data['name'])) : trim((string) $current_term->name);
			$slug = isset($raw_term_data['slug']) ? sanitize_title((string) $raw_term_data['slug']) : sanitize_title((string) $current_term->slug);
			$description = isset($raw_term_data['description'])
				? self::sanitize_media_library_tag_description_input((string) $raw_term_data['description'])
				: (string) $current_term->description;
			$bullets = isset($raw_term_data['bullets'])
				? self::sanitize_media_library_tag_bullets_input((string) $raw_term_data['bullets'])
				: implode("\n", self::get_media_library_tag_bullets_lines($term_id));
			$featured_image_id = isset($raw_term_data['featured_image_id'])
				? self::sanitize_media_library_tag_featured_image_id($raw_term_data['featured_image_id'])
				: self::get_media_library_tag_featured_image_id($term_id);

			if ($name === '') {
				$name = trim((string) $current_term->name);
			}
			if ($slug === '') {
				$slug = sanitize_title($name);
			}
			if ($slug === '') {
				$slug = sanitize_title((string) $current_term->slug);
			}

			$result = wp_update_term($term_id, $taxonomy, [
				'name' => $name,
				'slug' => $slug,
				'description' => $description,
			]);
			if (is_wp_error($result)) {
				$errors[] = sprintf(
					/* translators: 1: original term name, 2: error message */
					__('"%1$s": %2$s', 'user-manager'),
					(string) $current_term->name,
					$result->get_error_message()
				);
				continue;
			}
			$old_slug = sanitize_title((string) $current_term->slug);
			$new_slug = isset($result['slug']) ? sanitize_title((string) $result['slug']) : $slug;
			if ($new_slug === '') {
				$new_slug = $old_slug;
			}
			self::maybe_replace_media_library_video_tag_slug($old_slug, $new_slug);
			if ($featured_image_id <= 0) {
				delete_term_meta($term_id, self::media_library_tag_featured_image_meta_key());
			} else {
				update_term_meta($term_id, self::media_library_tag_featured_image_meta_key(), $featured_image_id);
			}
			if ($bullets === '') {
				delete_term_meta($term_id, self::media_library_tag_bullets_meta_key());
			} else {
				update_term_meta($term_id, self::media_library_tag_bullets_meta_key(), $bullets);
			}
			$updated++;
		}

		if (!empty($errors)) {
			set_transient('um_media_library_tags_bulk_editor_errors_' . get_current_user_id(), $errors, 120);
		}

		$redirect_url = add_query_arg(
			[
				'page' => 'um-media-library-tags-bulk-editor',
				'um_bulk_updated' => (string) $updated,
			],
			admin_url('upload.php')
		);
		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	 * Keep Media Library Tag description formatting while allowing only safe HTML.
	 */
	private static function sanitize_media_library_tag_description_input(string $raw_description): string {
		$normalized = str_replace(["\r\n", "\r"], "\n", $raw_description);

		return (string) wp_kses($normalized, self::get_media_library_tag_description_allowed_html());
	}

	/**
	 * Sanitize Library Tag bullet textarea input.
	 */
	private static function sanitize_media_library_tag_bullets_input(string $raw_bullets): string {
		$lines = self::parse_media_library_tag_bullets_lines($raw_bullets);
		if (empty($lines)) {
			return '';
		}
		return implode("\n", $lines);
	}

	/**
	 * Parse a newline-separated bullets value into sanitized lines.
	 *
	 * @return array<int,string>
	 */
	private static function parse_media_library_tag_bullets_lines(string $raw_bullets): array {
		$normalized = str_replace(["\r\n", "\r"], "\n", $raw_bullets);
		$parts = preg_split('/\n+/', $normalized);
		if (!is_array($parts) || empty($parts)) {
			return [];
		}

		$lines = [];
		foreach ($parts as $part) {
			$line = trim(sanitize_text_field((string) $part));
			if ($line === '') {
				continue;
			}
			$lines[] = $line;
		}

		return $lines;
	}

	/**
	 * Allowed HTML tags for front-end tag descriptions.
	 *
	 * @return array<string,array<string,bool>>
	 */
	private static function get_media_library_tag_description_allowed_html(): array {
		$allowed_tags = wp_kses_allowed_html('post');

		if (!isset($allowed_tags['b']) || !is_array($allowed_tags['b'])) {
			$allowed_tags['b'] = [];
		}
		if (!isset($allowed_tags['i']) || !is_array($allowed_tags['i'])) {
			$allowed_tags['i'] = [];
		}
		if (!isset($allowed_tags['br']) || !is_array($allowed_tags['br'])) {
			$allowed_tags['br'] = [];
		}

		return is_array($allowed_tags) ? $allowed_tags : [];
	}

	/**
	 * Format tag description HTML for output.
	 */
	private static function format_media_library_tag_description_html(string $description): string {
		$sanitized_description = (string) wp_kses($description, self::get_media_library_tag_description_allowed_html());
		$normalized_description = str_replace(["\r\n", "\r"], "\n", $sanitized_description);

		return nl2br($normalized_description);
	}

	/**
	 * Meta key for Library Tag featured image.
	 */
	private static function media_library_tag_featured_image_meta_key(): string {
		return 'um_media_library_tag_featured_image_id';
	}

	/**
	 * Meta key for Library Tag front-end bullet list.
	 */
	private static function media_library_tag_bullets_meta_key(): string {
		return 'um_media_library_tag_bullets';
	}

	/**
	 * Resolve one Library Tag featured image attachment ID.
	 */
	private static function get_media_library_tag_featured_image_id(int $term_id): int {
		if ($term_id <= 0) {
			return 0;
		}
		return absint(get_term_meta($term_id, self::media_library_tag_featured_image_meta_key(), true));
	}

	/**
	 * Resolve one Library Tag bullet list as an array of line items.
	 *
	 * @return array<int,string>
	 */
	private static function get_media_library_tag_bullets_lines(int $term_id): array {
		if ($term_id <= 0) {
			return [];
		}
		$raw = (string) get_term_meta($term_id, self::media_library_tag_bullets_meta_key(), true);
		if ($raw === '') {
			return [];
		}
		return self::parse_media_library_tag_bullets_lines($raw);
	}

	/**
	 * Sanitize a featured image attachment ID from request input.
	 *
	 * @param mixed $raw_value
	 */
	private static function sanitize_media_library_tag_featured_image_id($raw_value): int {
		$attachment_id = absint($raw_value);
		if ($attachment_id <= 0) {
			return 0;
		}
		$attachment = get_post($attachment_id);
		if (!($attachment instanceof WP_Post) || $attachment->post_type !== 'attachment') {
			return 0;
		}
		$mime = get_post_mime_type($attachment_id);
		if (!is_string($mime) || strpos($mime, 'image/') !== 0) {
			return 0;
		}
		return $attachment_id;
	}

	/**
	 * Render featured image field on "Add Library Tag" form.
	 */
	public static function render_media_library_tag_featured_image_add_form_fields(string $taxonomy = ''): void {
		self::enqueue_media_library_tags_featured_image_picker_assets();
		?>
		<div class="form-field term-um-media-library-tag-featured-image-wrap">
			<label for="um-media-library-tag-featured-image-id"><?php esc_html_e('Featured Image', 'user-manager'); ?></label>
			<input type="hidden" id="um-media-library-tag-featured-image-id" name="um_media_library_tag_featured_image_id" class="um-media-library-tag-featured-image-id" value="" />
			<div class="um-media-library-tag-featured-image-preview"></div>
			<p style="margin-top:8px;">
				<button type="button" class="button um-media-library-tag-featured-image-select"><?php esc_html_e('Select Featured Image', 'user-manager'); ?></button>
				<button type="button" class="button um-media-library-tag-featured-image-remove" style="display:none;"><?php esc_html_e('Remove Featured Image', 'user-manager'); ?></button>
			</p>
			<p><?php esc_html_e('Shows on the front-end next to the tag description when this tag is active in the gallery.', 'user-manager'); ?></p>
		</div>
		<div class="form-field term-um-media-library-tag-bullets-wrap">
			<label for="um-media-library-tag-bullets"><?php esc_html_e('Bullets', 'user-manager'); ?></label>
			<textarea id="um-media-library-tag-bullets" name="um_media_library_tag_bullets" rows="5" class="large-text"></textarea>
			<p><?php esc_html_e('Optional. Add one bullet per line to show below this tag description on the front end.', 'user-manager'); ?></p>
		</div>
		<?php
	}

	/**
	 * Render featured image field on "Edit Library Tag" form.
	 */
	public static function render_media_library_tag_featured_image_edit_form_fields(WP_Term $term, string $taxonomy = ''): void {
		self::enqueue_media_library_tags_featured_image_picker_assets();
		$attachment_id = self::get_media_library_tag_featured_image_id((int) $term->term_id);
		$bullet_lines = self::get_media_library_tag_bullets_lines((int) $term->term_id);
		$album_tag_views = self::get_media_library_album_tag_view_count((int) $term->term_id);
		$album_tag_period_views = self::get_media_library_album_tag_period_view_counts((int) $term->term_id);
		$preview_html = $attachment_id > 0
			? wp_get_attachment_image($attachment_id, 'medium', false, ['class' => 'um-media-library-tag-featured-image-preview-image'])
			: '';
		?>
		<tr class="form-field term-um-media-library-tag-featured-image-wrap">
			<th scope="row">
				<label for="um-media-library-tag-featured-image-id"><?php esc_html_e('Featured Image', 'user-manager'); ?></label>
			</th>
			<td>
				<input type="hidden" id="um-media-library-tag-featured-image-id" name="um_media_library_tag_featured_image_id" class="um-media-library-tag-featured-image-id" value="<?php echo esc_attr((string) $attachment_id); ?>" />
				<div class="um-media-library-tag-featured-image-preview"><?php echo $preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<p style="margin-top:8px;">
					<button type="button" class="button um-media-library-tag-featured-image-select"><?php echo $attachment_id > 0 ? esc_html__('Replace Featured Image', 'user-manager') : esc_html__('Select Featured Image', 'user-manager'); ?></button>
					<button type="button" class="button um-media-library-tag-featured-image-remove" <?php echo $attachment_id > 0 ? '' : 'style="display:none;"'; ?>><?php esc_html_e('Remove Featured Image', 'user-manager'); ?></button>
				</p>
				<p class="description"><?php esc_html_e('Shows on the front-end next to the tag description when this tag is active in the gallery.', 'user-manager'); ?></p>
			</td>
		</tr>
		<tr class="form-field term-um-media-library-tag-bullets-wrap">
			<th scope="row">
				<label for="um-media-library-tag-bullets"><?php esc_html_e('Bullets', 'user-manager'); ?></label>
			</th>
			<td>
				<textarea id="um-media-library-tag-bullets" name="um_media_library_tag_bullets" rows="6" class="large-text"><?php echo esc_textarea(implode("\n", $bullet_lines)); ?></textarea>
				<p class="description"><?php esc_html_e('Optional. Add one bullet per line to show below this tag description on the front end.', 'user-manager'); ?></p>
			</td>
		</tr>
		<tr class="form-field term-um-media-library-tag-views-wrap">
			<th scope="row">
				<label><?php esc_html_e('Album Tag View Reports', 'user-manager'); ?></label>
			</th>
			<td>
				<div style="margin-bottom:4px;"><strong><?php esc_html_e('Album Tag Views:', 'user-manager'); ?></strong> <?php echo esc_html(number_format_i18n($album_tag_views)); ?></div>
				<div style="margin-bottom:4px;"><strong><?php esc_html_e('Album Tag Views (Year):', 'user-manager'); ?></strong> <?php echo esc_html(number_format_i18n((int) ($album_tag_period_views['year'] ?? 0))); ?></div>
				<div style="margin-bottom:4px;"><strong><?php esc_html_e('Album Tag Views (Month):', 'user-manager'); ?></strong> <?php echo esc_html(number_format_i18n((int) ($album_tag_period_views['month'] ?? 0))); ?></div>
				<div style="margin-bottom:4px;"><strong><?php esc_html_e('Album Tag Views (Week):', 'user-manager'); ?></strong> <?php echo esc_html(number_format_i18n((int) ($album_tag_period_views['week'] ?? 0))); ?></div>
				<div style="margin-bottom:0;"><strong><?php esc_html_e('Album Tag Views (Day):', 'user-manager'); ?></strong> <?php echo esc_html(number_format_i18n((int) ($album_tag_period_views['day'] ?? 0))); ?></div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save featured image term meta for Library Tags.
	 */
	public static function save_media_library_tag_featured_image_term_meta(int $term_id): void {
		if ($term_id <= 0 || !current_user_can('upload_files')) {
			return;
		}
		if (array_key_exists('um_media_library_tag_featured_image_id', $_POST)) {
			$raw_value = wp_unslash($_POST['um_media_library_tag_featured_image_id']);
			$attachment_id = self::sanitize_media_library_tag_featured_image_id($raw_value);
			if ($attachment_id <= 0) {
				delete_term_meta($term_id, self::media_library_tag_featured_image_meta_key());
			} else {
				update_term_meta($term_id, self::media_library_tag_featured_image_meta_key(), $attachment_id);
			}
		}
		if (array_key_exists('um_media_library_tag_bullets', $_POST)) {
			$raw_bullets = wp_unslash($_POST['um_media_library_tag_bullets']);
			$bullets = self::sanitize_media_library_tag_bullets_input((string) $raw_bullets);
			if ($bullets === '') {
				delete_term_meta($term_id, self::media_library_tag_bullets_meta_key());
			} else {
				update_term_meta($term_id, self::media_library_tag_bullets_meta_key(), $bullets);
			}
		}
	}

	/**
	 * Render featured image selector controls for one bulk editor row.
	 */
	private static function render_media_library_tag_featured_image_bulk_editor_control_html(int $term_id, int $attachment_id): string {
		$term_id = absint($term_id);
		$attachment_id = absint($attachment_id);
		$preview_html = '';
		if ($attachment_id > 0) {
			$preview_html = wp_get_attachment_image(
				$attachment_id,
				'medium',
				false,
				[
					'class' => 'um-media-library-tag-featured-image-preview-image',
				]
			);
		}
		$remove_style = $attachment_id > 0 ? '' : ' style="display:none;"';
		$select_label = $attachment_id > 0
			? esc_html__('Replace Featured Image', 'user-manager')
			: esc_html__('Select Featured Image', 'user-manager');
		ob_start();
		?>
		<div class="um-bulk-featured-image-control" data-um-term-id="<?php echo esc_attr((string) $term_id); ?>">
			<input
				type="hidden"
				class="um-bulk-featured-image-id"
				name="um_bulk_terms[<?php echo esc_attr((string) $term_id); ?>][featured_image_id]"
				value="<?php echo esc_attr((string) $attachment_id); ?>"
			/>
			<div class="um-bulk-featured-image-preview"><?php echo $preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<p style="margin:8px 0 0;">
				<button type="button" class="button button-small um-bulk-featured-image-select"><?php echo esc_html($select_label); ?></button>
				<button type="button" class="button button-small um-bulk-featured-image-remove"<?php echo $remove_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php esc_html_e('Remove', 'user-manager'); ?></button>
			</p>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Enqueue media picker assets for the Library Tags Bulk Editor page.
	 */
	private static function enqueue_media_library_tags_bulk_editor_featured_image_assets(): void {
		self::enqueue_media_library_tags_featured_image_picker_assets();
	}

	/**
	 * Enqueue shared featured image picker assets for Library Tag admin forms.
	 */
	private static function enqueue_media_library_tags_featured_image_picker_assets(): void {
		static $enqueued = false;
		if ($enqueued) {
			return;
		}
		$enqueued = true;

		wp_enqueue_media();
		wp_register_script('um-media-library-tag-featured-image-picker', false, ['jquery'], self::VERSION, true);
		$script = <<<'JS'
(function($){
	var frame = null;
	function ensureFrame(){
		if (frame) {
			return frame;
		}
		frame = wp.media({
			title: 'Select Featured Image',
			button: { text: 'Use Featured Image' },
			multiple: false
		});
		return frame;
	}
	function getImageHtml(attachment){
		if (!attachment || !attachment.id) {
			return '';
		}
		var src = '';
		if (attachment.sizes && attachment.sizes.medium && attachment.sizes.medium.url) {
			src = attachment.sizes.medium.url;
		} else if (attachment.sizes && attachment.sizes.full && attachment.sizes.full.url) {
			src = attachment.sizes.full.url;
		} else if (attachment.url) {
			src = attachment.url;
		}
		if (!src) {
			return '';
		}
		return '<img src="' + String(src).replace(/"/g, '&quot;') + '" class="um-media-library-tag-featured-image-preview-image attachment-medium size-medium" alt="" />';
	}
	function setControlValue($scope, id, imageHtml){
		var hasImage = parseInt(id, 10) > 0;
		$scope.find('.um-media-library-tag-featured-image-id, .um-bulk-featured-image-id').first().val(hasImage ? String(id) : '');
		$scope.find('.um-media-library-tag-featured-image-preview, .um-bulk-featured-image-preview').first().html(hasImage ? imageHtml : '');
		$scope.find('.um-media-library-tag-featured-image-remove, .um-bulk-featured-image-remove').first().toggle(hasImage);
		var label = hasImage ? 'Replace Featured Image' : 'Select Featured Image';
		$scope.find('.um-media-library-tag-featured-image-select, .um-bulk-featured-image-select').first().text(label);
	}
	$(document).on('click', '.um-media-library-tag-featured-image-select, .um-bulk-featured-image-select', function(e){
		e.preventDefault();
		var $scope = $(this).closest('.term-um-media-library-tag-featured-image-wrap, .um-bulk-featured-image-control');
		if (!$scope.length) {
			return;
		}
		var picker = ensureFrame();
		picker.off('select');
		picker.on('select', function(){
			var selection = picker.state().get('selection');
			var attachment = selection && selection.first ? selection.first().toJSON() : null;
			if (!attachment || !attachment.id) {
				return;
			}
			var imageHtml = getImageHtml(attachment);
			setControlValue($scope, attachment.id, imageHtml);
		});
		picker.open();
	});
	$(document).on('click', '.um-media-library-tag-featured-image-remove, .um-bulk-featured-image-remove', function(e){
		e.preventDefault();
		var $scope = $(this).closest('.term-um-media-library-tag-featured-image-wrap, .um-bulk-featured-image-control');
		if (!$scope.length) {
			return;
		}
		setControlValue($scope, 0, '');
	});
})(jQuery);
JS;
		wp_add_inline_script('um-media-library-tag-featured-image-picker', $script, 'after');
		wp_enqueue_script('um-media-library-tag-featured-image-picker');

		$styles = <<<'CSS'
.um-media-library-tag-featured-image-preview,
.um-bulk-featured-image-preview {
	margin-top: 8px;
}
.um-media-library-tag-featured-image-preview-image,
.um-bulk-featured-image-preview img {
	display: block;
	max-width: 200px;
	height: auto;
	border: 1px solid #dcdcde;
	border-radius: 4px;
}
.um-bulk-featured-image-control .button + .button {
	margin-left: 6px;
}
CSS;
		wp_register_style('um-media-library-tag-featured-image-picker', false, [], self::VERSION);
		wp_enqueue_style('um-media-library-tag-featured-image-picker');
		wp_add_inline_style('um-media-library-tag-featured-image-picker', $styles);
	}

	/**
	 * Extract a YouTube video ID from a YouTube URL.
	 */
	private static function get_media_library_tag_youtube_video_id_from_url(string $raw_url): string {
		$raw_url = trim($raw_url);
		if ($raw_url === '') {
			return '';
		}
		if (stripos($raw_url, 'http://') !== 0 && stripos($raw_url, 'https://') !== 0) {
			$raw_url = 'https://' . ltrim($raw_url, '/');
		}

		$parts = wp_parse_url($raw_url);
		if (!is_array($parts)) {
			return '';
		}

		$host = isset($parts['host']) ? strtolower((string) $parts['host']) : '';
		if ($host === '') {
			return '';
		}
		if (strpos($host, 'www.') === 0) {
			$host = (string) substr($host, 4);
		}
		if (strpos($host, 'm.') === 0) {
			$host = (string) substr($host, 2);
		}

		$path = isset($parts['path']) ? trim((string) $parts['path'], '/') : '';
		$segments = $path !== '' ? explode('/', $path) : [];
		$video_id = '';

		if ($host === 'youtu.be') {
			$video_id = isset($segments[0]) ? (string) $segments[0] : '';
		} elseif (in_array($host, ['youtube.com', 'youtube-nocookie.com', 'music.youtube.com'], true)) {
			$first_segment = isset($segments[0]) ? strtolower((string) $segments[0]) : '';
			if ($first_segment === 'watch') {
				$query_args = [];
				if (!empty($parts['query'])) {
					parse_str((string) $parts['query'], $query_args);
				}
				$video_id = isset($query_args['v']) ? (string) $query_args['v'] : '';
			} elseif (in_array($first_segment, ['embed', 'shorts', 'live'], true)) {
				$video_id = isset($segments[1]) ? (string) $segments[1] : '';
			}
		}

		$video_id = trim($video_id);
		if ($video_id === '' || preg_match('/^[A-Za-z0-9_-]{6,32}$/', $video_id) !== 1) {
			return '';
		}

		return $video_id;
	}

	/**
	 * Render YouTube video embeds for active URL tags (or the block tag fallback).
	 *
	 * @param array{mode?:string,slugs?:array<int,string>,primarySlug?:string} $tag_override
	 * @param array{desktopColumns?:int,sortDirection?:string}                 $render_options
	 */
	private static function render_media_library_tag_youtube_videos_html(array $tag_override, string $fallback_tag_slug = '', array $render_options = []): string {
		$tag_slugs = isset($tag_override['slugs']) && is_array($tag_override['slugs'])
			? array_values(array_filter(array_map('sanitize_title', array_map('strval', $tag_override['slugs']))))
			: [];
		$fallback_tag_slug = sanitize_title($fallback_tag_slug);
		if (empty($tag_slugs) && $fallback_tag_slug !== '') {
			$tag_slugs[] = $fallback_tag_slug;
		}
		if (empty($tag_slugs)) {
			return '';
		}
		// When multiple tags are active, only use videos from the LAST tag.
		if (count($tag_slugs) > 1) {
			$last_tag_slug = (string) ($tag_slugs[array_key_last($tag_slugs)] ?? '');
			$tag_slugs = $last_tag_slug !== '' ? [$last_tag_slug] : [];
		}
		if (empty($tag_slugs)) {
			return '';
		}

		$video_records = self::get_media_library_tag_video_library_records_for_slugs($tag_slugs);
		if (empty($video_records)) {
			return '';
		}
		$sort_direction = self::normalize_media_library_tag_video_sort_direction($render_options['sortDirection'] ?? '');
		if ($sort_direction === '') {
			$sort_direction = 'asc';
		}
		self::sort_media_library_tag_video_records_by_datetime($video_records, $sort_direction);
		$settings = User_Manager_Core::get_settings();
		$display_video_title = !empty($settings['media_library_tag_video_library_display_title']);
		$display_video_description = !empty($settings['media_library_tag_video_library_display_description']);
		$can_edit_video_library = current_user_can('administrator');

		$video_items = [];
		foreach ($video_records as $video_record) {
			if (!is_array($video_record) || empty($video_record['youtubeUrl'])) {
				continue;
			}
			$video_url = (string) $video_record['youtubeUrl'];
			$video_id = self::get_media_library_tag_youtube_video_id_from_url((string) $video_url);
			if ($video_id === '') {
				continue;
			}
			$embed_url = 'https://www.youtube.com/embed/' . rawurlencode($video_id);
			$video_title = isset($video_record['title']) ? trim((string) $video_record['title']) : '';
			$video_description = isset($video_record['description']) ? trim((string) $video_record['description']) : '';
			$is_vertical = !empty($video_record['isVertical']);
			$meta_html = '';
			if ($display_video_title && $video_title !== '') {
				$meta_html .= '<h4 class="um-media-library-tag-video-title">' . esc_html($video_title) . '</h4>';
			}
			if ($display_video_description && $video_description !== '') {
				$meta_html .= '<p class="um-media-library-tag-video-description">' . esc_html($video_description) . '</p>';
			}
			if ($can_edit_video_library && !empty($video_record['id'])) {
				$edit_video_url = add_query_arg(
					[
						'page' => 'um-media-library-tag-video-library',
						'video_id' => sanitize_text_field((string) $video_record['id']),
					],
					admin_url('upload.php')
				);
				$meta_html .= '<p class="um-media-library-tag-video-edit-link-wrap"><a class="um-media-library-tag-video-edit-link" href="' . esc_url($edit_video_url) . '">' . esc_html__('Edit Video', 'user-manager') . '</a></p>';
			}
			$video_items[] = sprintf(
				'<div class="um-media-library-tag-video-item%4$s"><div class="um-media-library-tag-video-frame"><iframe src="%1$s" title="%2$s" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>%3$s</div>',
				esc_url($embed_url),
				esc_attr__('YouTube video', 'user-manager'),
				$meta_html,
				$is_vertical ? ' um-media-library-tag-video-item-vertical' : ''
			);
		}
		if (empty($video_items)) {
			return '';
		}

		$video_count = count($video_items);
		$desktop_columns_override = isset($render_options['desktopColumns']) ? absint((int) $render_options['desktopColumns']) : 0;
		if ($desktop_columns_override > 0) {
			$desktop_video_columns = max(1, min(4, $desktop_columns_override));
		} else {
			$desktop_video_columns = max(1, min(4, $video_count));
		}
		$wrap_classes = ['um-media-library-tag-videos-wrap'];
		$wrap_classes[] = 'um-media-library-tag-videos-wrap-multi';
		$wrap_classes[] = 'um-media-library-tag-videos-wrap-cols-' . $desktop_video_columns;

		return sprintf(
			'<div class="%1$s">%2$s</div>',
			esc_attr(implode(' ', $wrap_classes)),
			implode('', $video_items)
		);
	}

	/**
	 * Normalize supported shortcode sort directions.
	 */
	private static function normalize_media_library_tag_video_sort_direction($raw): string {
		$raw = strtolower(trim((string) $raw));
		if ($raw === '') {
			return '';
		}
		if (in_array($raw, ['asc', 'oldest', 'oldest_first', 'date_asc', 'time_asc'], true)) {
			return 'asc';
		}
		if (in_array($raw, ['desc', 'newest', 'newest_first', 'date_desc', 'time_desc'], true)) {
			return 'desc';
		}
		return '';
	}

	/**
	 * Sort video records by date+time, with title fallback.
	 *
	 * @param array<int,array<string,mixed>> $records
	 */
	private static function sort_media_library_tag_video_records_by_datetime(array &$records, string $direction = 'desc'): void {
		if (count($records) < 2) {
			return;
		}
		$direction = strtolower(trim($direction)) === 'asc' ? 'asc' : 'desc';
		usort($records, static function (array $a, array $b) use ($direction): int {
			$date_a = isset($a['videoDate']) ? (string) $a['videoDate'] : '';
			$date_b = isset($b['videoDate']) ? (string) $b['videoDate'] : '';
			$time_a = isset($a['videoTime']) ? (string) $a['videoTime'] : '';
			$time_b = isset($b['videoTime']) ? (string) $b['videoTime'] : '';
			$title_a = strtolower((string) ($a['title'] ?? ''));
			$title_b = strtolower((string) ($b['title'] ?? ''));

			// Keep unknown dates at the end regardless of direction.
			if ($date_a === '' && $date_b !== '') {
				return 1;
			}
			if ($date_b === '' && $date_a !== '') {
				return -1;
			}
			if ($date_a !== $date_b) {
				return $direction === 'asc'
					? strcmp($date_a, $date_b)
					: strcmp($date_b, $date_a);
			}

			// Same date: keep unknown times at the end.
			if ($time_a === '' && $time_b !== '') {
				return 1;
			}
			if ($time_b === '' && $time_a !== '') {
				return -1;
			}
			if ($time_a !== $time_b) {
				return $direction === 'asc'
					? strcmp($time_a, $time_b)
					: strcmp($time_b, $time_a);
			}

			return $title_a <=> $title_b;
		});
	}

	/**
	 * Shortcode renderer for Video Library rows.
	 *
	 * Supported tag expression formats:
	 * - tag1        (single tag)
	 * - tag1+tag2   (AND)
	 * - tag1_tag2   (OR)
	 * - tag1|tag2   (pipe split into independent sections)
	 *
	 * @param array<string,mixed> $atts
	 */
	public static function render_media_library_tag_videos_shortcode($atts = []): string {
		$atts = shortcode_atts(
			[
				'tags' => '',
				'tag' => '',
				'desktop_columns' => '',
				'columns' => '',
				'sort' => '',
				'order' => '',
			],
			is_array($atts) ? $atts : [],
			'um_media_library_tag_videos'
		);

		$raw_expression = trim((string) ($atts['tags'] !== '' ? $atts['tags'] : $atts['tag']));
		if ($raw_expression === '') {
			return '';
		}

		$desktop_columns_raw = $atts['desktop_columns'] !== '' ? $atts['desktop_columns'] : $atts['columns'];
		$desktop_columns = max(0, min(4, absint((int) $desktop_columns_raw)));
		$render_options = $desktop_columns > 0 ? ['desktopColumns' => $desktop_columns] : [];
		$sort_raw = $atts['sort'] !== '' ? $atts['sort'] : $atts['order'];
		$sort_direction = self::normalize_media_library_tag_video_sort_direction($sort_raw);
		if ($sort_direction !== '') {
			$render_options['sortDirection'] = $sort_direction;
		}

		$pipe_expressions = self::split_media_library_gallery_pipe_expressions($raw_expression);
		if (!empty($pipe_expressions)) {
			$sections = [];
			foreach ($pipe_expressions as $pipe_expression) {
				$parsed_expression = self::parse_media_library_gallery_tag_expression($pipe_expression);
				if (empty($parsed_expression['mode']) || $parsed_expression['mode'] === 'none') {
					continue;
				}
				$videos_html = self::render_media_library_tag_youtube_videos_html($parsed_expression, '', $render_options);
				if ($videos_html === '') {
					continue;
				}
				$tag_data = self::get_media_library_tag_description_data_for_tag_expression($parsed_expression);
				$title = trim((string) ($tag_data['name'] ?? ''));
				$description = trim((string) ($tag_data['description'] ?? ''));
				$header_html = '';
				if ($title !== '') {
					$header_html .= '<h2 class="um-media-library-tag-pipe-title" style="text-align:center;margin:0 0 8px;">' . esc_html($title) . '</h2>';
				}
				if ($description !== '') {
					$header_html .= '<p class="um-media-library-tag-pipe-description" style="text-align:center;margin:0 0 14px;">' . esc_html($description) . '</p>';
				}
				$sections[] = '<div class="um-media-library-tag-pipe-section" style="margin-bottom:50px;">' . $header_html . $videos_html . '</div>';
			}

			if (empty($sections)) {
				return '';
			}
			return self::get_media_library_tag_videos_shortcode_style_html() . implode('', $sections);
		}

		$tag_override = self::parse_media_library_gallery_tag_expression($raw_expression);
		if (empty($tag_override['mode']) || $tag_override['mode'] === 'none') {
			return '';
		}

		$videos_html = self::render_media_library_tag_youtube_videos_html($tag_override, '', $render_options);
		if ($videos_html === '') {
			return '';
		}
		return self::get_media_library_tag_videos_shortcode_style_html() . $videos_html;
	}

	/**
	 * Print shared Video Library shortcode CSS once per request.
	 */
	private static function get_media_library_tag_videos_shortcode_style_html(): string {
		static $printed = false;
		if ($printed) {
			return '';
		}
		$printed = true;
		return '<style>'
			. '.um-media-library-tag-videos-wrap{margin:0 0 22px;display:grid;grid-template-columns:1fr;gap:20px;}'
			. '.um-media-library-tag-videos-wrap-multi{grid-template-columns:repeat(2,minmax(0,1fr));}'
			. '.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-1{grid-template-columns:minmax(0,1fr) minmax(0,2fr) minmax(0,1fr);}'
			. '.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-1 .um-media-library-tag-video-item{grid-column:2;}'
			. '.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-3{grid-template-columns:repeat(3,minmax(0,1fr));}'
			. '.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-4{grid-template-columns:repeat(4,minmax(0,1fr));}'
			. '.um-media-library-tag-video-item{display:flex;flex-direction:column;min-width:0;}'
			. '.um-media-library-tag-video-frame{position:relative;padding-top:56.25%;background:#000;border-radius:6px;overflow:hidden;}'
			. '.um-media-library-tag-video-item-vertical .um-media-library-tag-video-frame{padding-top:177.7778%;}'
			. '.um-media-library-tag-video-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0;}'
			. '.um-media-library-tag-video-title{margin:14px 0 8px;font-size:17px;line-height:1.35;}'
			. '.um-media-library-tag-video-description{margin:0;font-size:14px;line-height:1.55;overflow-wrap:anywhere;}'
			. '.um-media-library-tag-video-edit-link-wrap{margin:12px 0 0;text-align:center;}'
			. '.um-media-library-tag-video-edit-link{display:inline-block;font-size:13px;line-height:1.4;}'
			. '@media (max-width:782px){.um-media-library-tag-videos-wrap-multi,.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-3,.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-4{grid-template-columns:repeat(2,minmax(0,1fr));}.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-1{grid-template-columns:1fr;}}'
			. '</style>';
	}

	/**
	 * Render list-view media filter dropdown and bulk apply controls.
	 *
	 * @param string $post_type Current post type.
	 * @param string $which     Top or bottom controls.
	 */
	public static function render_media_library_tag_filter_controls(string $post_type = '', string $which = 'top'): void {
		global $pagenow;
		if ($pagenow !== 'upload.php') {
			return;
		}

		// On Media Library "All items" views WordPress can pass an empty post_type.
		if ($post_type !== '' && $post_type !== 'attachment') {
			return;
		}

		$taxonomy = self::media_library_tags_taxonomy();
		if (!taxonomy_exists($taxonomy)) {
			return;
		}

		$selected_filter = self::get_requested_media_library_tag_filter_value();
		$selected_sort = self::get_requested_media_library_sort_value();
		$sort_options = self::get_media_library_media_admin_sort_options();
		$no_tags_filter_value = self::get_media_library_no_tags_filter_value();
		$filter_terms = self::get_media_library_unique_filter_terms();
		$terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]);
		if (is_wp_error($terms)) {
			$terms = [];
		}
		?>
		<label for="um-media-library-tag-filter" class="screen-reader-text"><?php esc_html_e('Filter by Library Tag', 'user-manager'); ?></label>
		<select id="um-media-library-tag-filter" name="um_media_library_tag" class="attachment-filters">
			<option value=""><?php esc_html_e('All tags', 'user-manager'); ?></option>
			<option value="<?php echo esc_attr($no_tags_filter_value); ?>" <?php selected($selected_filter, $no_tags_filter_value); ?>>
				<?php esc_html_e('No tags', 'user-manager'); ?>
			</option>
			<?php foreach ($filter_terms as $filter_term) : ?>
				<?php if (!is_array($filter_term) || empty($filter_term['slug'])) { continue; } ?>
				<?php
				$option_label = (string) ($filter_term['name'] ?? $filter_term['slug']);
				if (!empty($filter_term['in_menu_nav'])) {
					$option_label .= ' *';
				}
				?>
				<option value="<?php echo esc_attr((string) $filter_term['slug']); ?>" <?php selected($selected_filter, (string) $filter_term['slug']); ?>>
					<?php echo esc_html($option_label); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<label for="um-media-library-sort-filter" class="screen-reader-text"><?php esc_html_e('Sort media by', 'user-manager'); ?></label>
		<select id="um-media-library-sort-filter" name="um_media_library_sort" class="attachment-filters">
			<?php foreach ($sort_options as $sort_value => $sort_label) : ?>
				<option value="<?php echo esc_attr((string) $sort_value); ?>" <?php selected($selected_sort, (string) $sort_value); ?>>
					<?php echo esc_html((string) $sort_label); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php

		if ($which !== 'top' || ($post_type !== '' && $post_type !== 'attachment')) {
			return;
		}
		?>
		<span id="um-media-library-bulk-tag-wrap" style="display:none;">
			<label for="um-media-library-bulk-tag" class="screen-reader-text"><?php esc_html_e('Apply tag to selected media', 'user-manager'); ?></label>
			<select id="um-media-library-bulk-tag" name="um_media_library_bulk_tag">
				<option value=""><?php esc_html_e('Apply Tag', 'user-manager'); ?></option>
				<?php foreach ($terms as $term) : ?>
					<?php if (!$term instanceof WP_Term) { continue; } ?>
					<option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
				<?php endforeach; ?>
			</select>
			<label for="um-media-library-bulk-tag-new" class="screen-reader-text"><?php esc_html_e('Or enter tag', 'user-manager'); ?></label>
			<input
				type="text"
				id="um-media-library-bulk-tag-new"
				name="um_media_library_bulk_tag_new"
				placeholder="<?php echo esc_attr__('or enter tag', 'user-manager'); ?>"
				style="width: 190px;"
			/>
			<?php submit_button(__('Apply Tag(s)', 'user-manager'), 'secondary', 'um_media_library_bulk_apply', false); ?>
			<?php wp_nonce_field('um_media_library_bulk_apply_action', 'um_media_library_bulk_apply_nonce', false); ?>
		</span>
		<script>
			(function($) {
				function syncListBulkTagVisibility() {
					var $toggle = $('.wp-filter .select-mode-toggle-button, .media-toolbar .select-mode-toggle-button').first();
					var isActive = false;
					if ($toggle.length) {
						var txt = String($.trim($toggle.text() || '')).toLowerCase();
						isActive = txt.indexOf('cancel') !== -1 || String($toggle.attr('aria-pressed') || '').toLowerCase() === 'true';
					}
					$('#um-media-library-bulk-tag-wrap').toggle(isActive);
				}
				$(document).ready(function() {
					syncListBulkTagVisibility();
					$(document).on('click', '.select-mode-toggle-button, .delete-selected-button, .attachments-browser input[type="checkbox"]', function() {
						window.setTimeout(syncListBulkTagVisibility, 60);
					});
				});
				$('#um-media-library-sort-filter').on('change', function() {
					var value = String($(this).val() || '');
					var url = new URL(window.location.href);
					if (!value) {
						url.searchParams.delete('um_media_library_sort');
					} else {
						url.searchParams.set('um_media_library_sort', value);
					}
					window.location.href = url.toString();
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Apply list-view query filtering by selected Library Tag.
	 *
	 * @param WP_Query $query Query object.
	 */
	public static function filter_media_library_queries_by_tag($query): void {
		if (!is_admin() || !($query instanceof WP_Query) || !$query->is_main_query()) {
			return;
		}

		global $pagenow;
		if ($pagenow !== 'upload.php') {
			return;
		}

		$post_type = $query->get('post_type');
		if ($post_type === '' || $post_type === null) {
			$post_type = 'attachment';
		}
		if ($post_type !== 'attachment' && !(is_array($post_type) && in_array('attachment', $post_type, true))) {
			return;
		}
		$requested_sort = self::get_requested_media_library_sort_value();
		if ($requested_sort !== '') {
			$query->set('um_media_library_sort', $requested_sort);
		}

		$requested_filter = self::get_requested_media_library_tag_filter_value();
		if ($requested_filter === '') {
			return;
		}

		$query->set('um_media_library_tag', '');
		$tax_query = self::remove_media_library_tag_tax_query_clauses($query->get('tax_query'));
		if (self::is_media_library_no_tags_filter_value($requested_filter)) {
			$all_term_ids = self::get_media_library_all_tag_term_ids();
			if (!empty($all_term_ids)) {
				$tax_query[] = [
					'taxonomy' => self::media_library_tags_taxonomy(),
					'field' => 'term_id',
					'terms' => $all_term_ids,
					'operator' => 'NOT IN',
				];
			}
		} else {
			$filter_slugs = self::get_media_library_filter_slugs_for_requested_slug($requested_filter);
			if (empty($filter_slugs)) {
				$filter_slugs = [$requested_filter];
			}
			$tax_query[] = [
				'taxonomy' => self::media_library_tags_taxonomy(),
				'field' => 'slug',
				'terms' => $filter_slugs,
			];
		}
		if (count($tax_query) >= 1) {
			$tax_query['relation'] = 'AND';
		}
		$query->set('tax_query', $tax_query);
	}

	/**
	 * Apply grid/ajax query filtering by selected Library Tag.
	 *
	 * @param array<string,mixed> $query Query args.
	 * @return array<string,mixed>
	 */
	public static function filter_media_library_ajax_query_by_tag(array $query): array {
		$requested_filter = '';
		$requested_sort = isset($query['um_media_library_sort']) ? sanitize_key((string) $query['um_media_library_sort']) : '';
		if ($requested_sort === '') {
			$requested_sort = self::get_requested_media_library_sort_value();
		}
		$sort_options = self::get_media_library_media_admin_sort_options();
		if ($requested_sort !== '' && !isset($sort_options[$requested_sort])) {
			$requested_sort = '';
		}
		if ($requested_sort !== '') {
			$query['um_media_library_sort'] = $requested_sort;
		}

		if (isset($query['um_media_library_tag'])) {
			$requested_filter = sanitize_text_field((string) $query['um_media_library_tag']);
		}
		if ($requested_filter === '' && isset($_REQUEST['query']) && is_array($_REQUEST['query']) && isset($_REQUEST['query']['um_media_library_tag'])) {
			$requested_filter = sanitize_text_field((string) wp_unslash($_REQUEST['query']['um_media_library_tag']));
		}
		if ($requested_filter === '') {
			$requested_filter = self::get_requested_media_library_tag_filter_value();
		}
		if ($requested_filter === '') {
			return $query;
		}
		if (!self::is_media_library_no_tags_filter_value($requested_filter)) {
			$requested_filter = sanitize_title($requested_filter);
			if ($requested_filter === '' || !self::is_valid_media_library_tag_filter_slug($requested_filter)) {
				return $query;
			}
		}

		unset($query['um_media_library_tag']);
		$tax_query = self::remove_media_library_tag_tax_query_clauses($query['tax_query'] ?? []);
		if (self::is_media_library_no_tags_filter_value($requested_filter)) {
			$all_term_ids = self::get_media_library_all_tag_term_ids();
			if (!empty($all_term_ids)) {
				$tax_query[] = [
					'taxonomy' => self::media_library_tags_taxonomy(),
					'field' => 'term_id',
					'terms' => $all_term_ids,
					'operator' => 'NOT IN',
				];
			}
		} else {
			$filter_slugs = self::get_media_library_filter_slugs_for_requested_slug($requested_filter);
			if (empty($filter_slugs)) {
				$filter_slugs = [$requested_filter];
			}
			$tax_query[] = [
				'taxonomy' => self::media_library_tags_taxonomy(),
				'field' => 'slug',
				'terms' => $filter_slugs,
			];
		}
		if (count($tax_query) >= 1) {
			$tax_query['relation'] = 'AND';
		}
		$query['tax_query'] = $tax_query;
		return $query;
	}

	/**
	 * Apply Lightbox Views sorting to media queries using a LEFT JOIN
	 * so attachments without a stored count are still included.
	 *
	 * @param array<string,string> $clauses SQL clauses.
	 * @param WP_Query             $query   Query object.
	 * @return array<string,string>
	 */
	public static function maybe_apply_media_library_lightbox_view_sort_clauses(array $clauses, $query): array {
		if (!($query instanceof WP_Query)) {
			return $clauses;
		}
		if (!is_admin()) {
			return $clauses;
		}
		$requested_sort = self::get_requested_media_library_sort_value_from_query($query);
		if (!in_array($requested_sort, ['lightbox_views_desc', 'lightbox_views_asc'], true)) {
			return $clauses;
		}
		$post_type = $query->get('post_type');
		if ($post_type === '' || $post_type === null) {
			$post_type = 'attachment';
		}
		if ($post_type !== 'attachment' && !(is_array($post_type) && in_array('attachment', $post_type, true))) {
			return $clauses;
		}
		global $wpdb;
		if (!($wpdb instanceof wpdb)) {
			return $clauses;
		}
		$join_alias = 'um_mltg_lightbox_views_meta';
		$meta_key = self::media_library_lightbox_views_meta_key();
		if (strpos((string) ($clauses['join'] ?? ''), $join_alias) === false) {
			$clauses['join'] = (string) ($clauses['join'] ?? '') . $wpdb->prepare(
				" LEFT JOIN {$wpdb->postmeta} AS {$join_alias} ON ({$wpdb->posts}.ID = {$join_alias}.post_id AND {$join_alias}.meta_key = %s) ",
				$meta_key
			);
		}
		$direction = $requested_sort === 'lightbox_views_asc' ? 'ASC' : 'DESC';
		$secondary_direction = $direction;
		$clauses['orderby'] = "CAST(COALESCE({$join_alias}.meta_value, '0') AS UNSIGNED) {$direction}, {$wpdb->posts}.post_date {$secondary_direction}, {$wpdb->posts}.ID {$secondary_direction}";
		return $clauses;
	}

	/**
	 * Handle list-view bulk apply submit.
	 */
	public static function handle_media_library_bulk_apply_tag(): void {
		if (!is_admin() || !current_user_can('upload_files')) {
			return;
		}

		global $pagenow;
		if ($pagenow !== 'upload.php' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
			return;
		}

		if (!isset($_POST['um_media_library_bulk_apply'])) {
			return;
		}

		$nonce = isset($_POST['um_media_library_bulk_apply_nonce']) ? sanitize_text_field(wp_unslash($_POST['um_media_library_bulk_apply_nonce'])) : '';
		if ($nonce === '' || !wp_verify_nonce($nonce, 'um_media_library_bulk_apply_action')) {
			wp_die(esc_html__('Security check failed.', 'user-manager'));
		}

		$selected_slug = isset($_POST['um_media_library_bulk_tag']) ? sanitize_title(wp_unslash($_POST['um_media_library_bulk_tag'])) : '';
		$new_tag_raw = isset($_POST['um_media_library_bulk_tag_new']) ? sanitize_text_field(wp_unslash($_POST['um_media_library_bulk_tag_new'])) : '';
		$tag_slug = self::resolve_media_library_bulk_tag_slug($selected_slug, $new_tag_raw);
		$media_ids = isset($_POST['media']) && is_array($_POST['media']) ? array_map('absint', wp_unslash($_POST['media'])) : [];
		$redirect_url = wp_get_referer() ?: admin_url('upload.php');
		$redirect_url = remove_query_arg(['um_media_library_tags_notice', 'um_media_library_tags_count'], $redirect_url);

		if (empty($media_ids)) {
			wp_safe_redirect(add_query_arg('um_media_library_tags_notice', 'no_selection', $redirect_url));
			exit;
		}
		if ($tag_slug === '') {
			wp_safe_redirect(add_query_arg('um_media_library_tags_notice', 'no_tag', $redirect_url));
			exit;
		}

		$updated_count = self::bulk_apply_media_library_tag_to_attachments($media_ids, $tag_slug);
		wp_safe_redirect(
			add_query_arg(
				[
					'um_media_library_tags_notice' => 'applied',
					'um_media_library_tags_count' => (string) $updated_count,
				],
				$redirect_url
			)
		);
		exit;
	}

	/**
	 * Build list of frontend-hidden tag slugs from settings.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<int,string>
	 */
	private static function get_hidden_frontend_tag_slugs(array $settings): array {
		if (empty($settings['media_library_tag_gallery_hidden_frontend_tags'])) {
			return [];
		}
		$raw_hidden = sanitize_text_field((string) $settings['media_library_tag_gallery_hidden_frontend_tags']);
		$parts = preg_split('/[\r\n,;]+/', $raw_hidden);
		if (!is_array($parts)) {
			return [];
		}
		return array_values(array_unique(array_filter(array_map('sanitize_title', array_map('trim', $parts)))));
	}

	/**
	 * Handle grid-view bulk apply via AJAX.
	 */
	public static function ajax_bulk_apply_media_library_tag(): void {
		if (!current_user_can('upload_files')) {
			wp_send_json_error(['message' => __('You do not have permission to update media tags.', 'user-manager')], 403);
		}
		check_ajax_referer('um_media_library_tags_ajax', 'nonce');

		$selected_slug = isset($_POST['tag']) ? sanitize_title(wp_unslash($_POST['tag'])) : '';
		$new_tag_raw = isset($_POST['tag_new']) ? sanitize_text_field(wp_unslash($_POST['tag_new'])) : '';
		$tag_slug = self::resolve_media_library_bulk_tag_slug($selected_slug, $new_tag_raw);
		$raw_ids = isset($_POST['ids']) && is_array($_POST['ids']) ? wp_unslash($_POST['ids']) : [];
		$ids = array_map('absint', $raw_ids);

		if ($tag_slug === '') {
			wp_send_json_error(['message' => __('Please choose a Library Tag or enter a new one.', 'user-manager')], 400);
		}
		if (empty($ids)) {
			wp_send_json_error(['message' => __('Please select one or more media items first.', 'user-manager')], 400);
		}

		$updated_count = self::bulk_apply_media_library_tag_to_attachments($ids, $tag_slug);
		wp_send_json_success([
			'updated' => $updated_count,
			'message' => sprintf(
				/* translators: %d: updated media count */
				__('Library Tag applied to %d media item(s).', 'user-manager'),
				$updated_count
			),
		]);
	}

	/**
	 * Track a lightbox view for a specific attachment.
	 */
	public static function ajax_track_media_library_lightbox_view(): void {
		$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
		if ($nonce === '' || !wp_verify_nonce($nonce, 'um_media_library_lightbox_view')) {
			wp_send_json_error(['message' => __('Invalid request.', 'user-manager')], 403);
		}
		$attachment_id = isset($_POST['attachment_id']) ? absint($_POST['attachment_id']) : 0;
		if ($attachment_id <= 0 || get_post_type($attachment_id) !== 'attachment') {
			wp_send_json_error(['message' => __('Invalid media item.', 'user-manager')], 400);
		}
		$counts = self::increment_media_library_lightbox_view_counts($attachment_id);
		wp_send_json_success([
			'attachment_id' => $attachment_id,
			'lightbox_views' => (int) ($counts['total'] ?? 0),
			'lightbox_views_year' => (int) ($counts['year'] ?? 0),
			'lightbox_views_month' => (int) ($counts['month'] ?? 0),
			'lightbox_views_week' => (int) ($counts['week'] ?? 0),
			'lightbox_views_day' => (int) ($counts['day'] ?? 0),
		]);
	}

	/**
	 * Show success/error notices after bulk apply actions.
	 */
	public static function maybe_render_media_library_tags_admin_notice(): void {
		if (!is_admin()) {
			return;
		}

		global $pagenow;
		if ($pagenow !== 'upload.php') {
			return;
		}

		$notice = isset($_GET['um_media_library_tags_notice']) ? sanitize_key(wp_unslash($_GET['um_media_library_tags_notice'])) : '';
		if ($notice === '') {
			return;
		}

		if ($notice === 'applied') {
			$count = isset($_GET['um_media_library_tags_count']) ? absint($_GET['um_media_library_tags_count']) : 0;
			$message = sprintf(
				/* translators: %d: updated media count */
				__('Library Tag applied to %d media item(s).', 'user-manager'),
				$count
			);
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
			return;
		}

		if ($notice === 'no_selection') {
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('Select at least one media item before applying a Library Tag.', 'user-manager') . '</p></div>';
			return;
		}

		if ($notice === 'no_tag') {
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('Choose a Library Tag or enter a new one before applying to selected media.', 'user-manager') . '</p></div>';
		}
	}

	/**
	 * Add Library Tags field to media item detail modal/edit fields.
	 *
	 * @param array<string,mixed> $form_fields Fields.
	 * @param WP_Post             $post        Attachment post.
	 * @return array<string,mixed>
	 */
	public static function add_media_library_tags_attachment_field(array $form_fields, $post): array {
		if (!($post instanceof WP_Post) || $post->post_type !== 'attachment') {
			return $form_fields;
		}

		$terms = wp_get_object_terms($post->ID, self::media_library_tags_taxonomy(), ['fields' => 'names']);
		$value = is_array($terms) ? implode(', ', array_map('strval', $terms)) : '';
		$lightbox_views = self::get_media_library_lightbox_view_count((int) $post->ID);
		$lightbox_period_views = self::get_media_library_lightbox_period_view_counts((int) $post->ID);
		$all_terms = get_terms([
			'taxonomy' => self::media_library_tags_taxonomy(),
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]);
		if (is_wp_error($all_terms)) {
			$all_terms = [];
		}
		$quick_links_html = '';
		if (!empty($all_terms)) {
			$quick_links_html .= '<div style="margin: 8px 0 6px;">';
			$quick_links_html .= '<strong style="display:block; margin-bottom:4px;">' . esc_html__('Quick add tags:', 'user-manager') . '</strong>';
			foreach ($all_terms as $term) {
				if (!($term instanceof WP_Term)) {
					continue;
				}
				$quick_links_html .= '<a href="#" class="um-media-library-tag-insert" data-um-tag="' . esc_attr((string) $term->name) . '" style="display:inline-block; margin:0 8px 6px 0;">' . esc_html((string) $term->name) . '</a>';
			}
			$quick_links_html .= '</div>';
		}
		$quick_links_html .= '<script>(function(){ if(window.umMediaLibraryTagInsertInit){return;} window.umMediaLibraryTagInsertInit=true; document.addEventListener("click",function(e){ var link=e.target&&e.target.closest?e.target.closest(".um-media-library-tag-insert"):null; if(!link){return;} e.preventDefault(); var tag=(link.getAttribute("data-um-tag")||"").trim(); if(!tag){return;} var wrap=link.closest?link.closest("tr,div") : null; var input=wrap?wrap.querySelector("input[name*=\'[um_media_library_tags]\']"):null; if(!input){input=document.querySelector("input[name*=\'[um_media_library_tags]\']");} if(!input){return;} var parts=(input.value||"").split(",").map(function(v){return v.trim();}).filter(Boolean); if(parts.indexOf(tag)===-1){parts.push(tag);} input.value=parts.join(", "); input.dispatchEvent(new Event("change",{bubbles:true})); }); })();</script>';

		$form_fields['um_media_library_tags'] = [
			'label' => __('Library Tags', 'user-manager'),
			'input' => 'html',
			'html'  => '<input type="text" class="text" name="attachments[' . (int) $post->ID . '][um_media_library_tags]" value="' . esc_attr($value) . '" />',
			'helps' => ''
				. '<div style="margin-bottom:4px;"><strong>' . esc_html__('Lightbox Views:', 'user-manager') . '</strong> ' . esc_html(number_format_i18n($lightbox_views)) . '</div>'
				. '<div style="margin-bottom:4px;"><strong>' . esc_html__('Lightbox Views (Year):', 'user-manager') . '</strong> ' . esc_html(number_format_i18n((int) ($lightbox_period_views['year'] ?? 0))) . '</div>'
				. '<div style="margin-bottom:4px;"><strong>' . esc_html__('Lightbox Views (Month):', 'user-manager') . '</strong> ' . esc_html(number_format_i18n((int) ($lightbox_period_views['month'] ?? 0))) . '</div>'
				. '<div style="margin-bottom:4px;"><strong>' . esc_html__('Lightbox Views (Week):', 'user-manager') . '</strong> ' . esc_html(number_format_i18n((int) ($lightbox_period_views['week'] ?? 0))) . '</div>'
				. '<div style="margin-bottom:8px;"><strong>' . esc_html__('Lightbox Views (Day):', 'user-manager') . '</strong> ' . esc_html(number_format_i18n((int) ($lightbox_period_views['day'] ?? 0))) . '</div>'
				. $quick_links_html
				. __('Comma-separated tags. Add new tags or remove existing tags for this media item.', 'user-manager'),
		];

		return $form_fields;
	}

	/**
	 * Save Library Tags field from media item detail modal/edit fields.
	 *
	 * @param array<string,mixed> $post       Attachment post data.
	 * @param array<string,mixed> $attachment Attachment form data.
	 * @return array<string,mixed>
	 */
	public static function save_media_library_tags_attachment_field(array $post, array $attachment): array {
		$post_id = isset($post['ID']) ? absint($post['ID']) : 0;
		if ($post_id <= 0 || !current_user_can('upload_files')) {
			return $post;
		}
		if (!array_key_exists('um_media_library_tags', $attachment)) {
			return $post;
		}

		$raw = sanitize_text_field((string) $attachment['um_media_library_tags']);
		$parts = array_filter(array_map('trim', explode(',', $raw)));
		$parts = array_values(array_unique($parts));
		wp_set_object_terms($post_id, $parts, self::media_library_tags_taxonomy(), false);

		return $post;
	}

	/**
	 * Enqueue Media Library grid controls for filtering and bulk tag apply.
	 */
	public static function enqueue_media_library_tags_admin_assets(string $hook_suffix): void {
		if ($hook_suffix !== 'upload.php') {
			return;
		}

		$terms = get_terms([
			'taxonomy' => self::media_library_tags_taxonomy(),
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]);
		if (is_wp_error($terms)) {
			$terms = [];
		}

		$term_options = [];
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$term_options[] = [
				'slug' => (string) $term->slug,
				'name' => (string) $term->name,
			];
		}
		$filter_term_options = self::get_media_library_unique_filter_terms();

		$settings = User_Manager_Core::get_settings();
		$show_thumbnail_tags = !empty($settings['media_library_tags_show_tags_on_thumbnails_bulk_select']);
		$sticky_bulk_toolbar_mobile = !empty($settings['media_library_tags_sticky_bulk_toolbar_mobile']);
		$config = [
			'selectedTag' => self::get_requested_media_library_tag_filter_value(),
			'selectedSort' => self::get_requested_media_library_sort_value(),
			'sortOptions' => self::get_media_library_media_admin_sort_options_for_js(),
			'noTagsValue' => self::get_media_library_no_tags_filter_value(),
			'terms' => $term_options,
			'bulkTerms' => $term_options,
			'filterTerms' => $filter_term_options,
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('um_media_library_tags_ajax'),
			'showTagsOnThumbnailsWhenBulkSelecting' => $show_thumbnail_tags,
			'stickyBulkToolbarMobile' => $sticky_bulk_toolbar_mobile,
			'attachmentTagsById' => $show_thumbnail_tags ? self::get_media_library_attachment_tags_map() : [],
			'labels' => [
				'filterAll' => __('All tags', 'user-manager'),
				'filterNoTags' => __('No tags', 'user-manager'),
				'bulkChoose' => __('Apply Tag', 'user-manager'),
				'bulkNewTagPlaceholder' => __('or enter tag', 'user-manager'),
				'bulkButton' => __('Apply Tag(s)', 'user-manager'),
				'bulkNoSelection' => __('Select one or more media items first.', 'user-manager'),
				'bulkNoTag' => __('Choose a Library Tag or enter a new one first.', 'user-manager'),
				'tagsLabel' => __('Tags', 'user-manager'),
			],
		];

		wp_register_script('um-media-library-tags-admin', false, ['jquery'], self::VERSION, true);
		wp_add_inline_script('um-media-library-tags-admin', 'window.umMediaLibraryTagsConfig = ' . wp_json_encode($config) . ';', 'before');
		$script = <<<'JS'
(function($) {
	var cfg = window.umMediaLibraryTagsConfig || {};
	if (!cfg) {
		return;
	}
	var filterTerms = Array.isArray(cfg.filterTerms) && cfg.filterTerms.length ? cfg.filterTerms : (Array.isArray(cfg.terms) ? cfg.terms : []);
	var bulkTerms = Array.isArray(cfg.bulkTerms) && cfg.bulkTerms.length ? cfg.bulkTerms : (Array.isArray(cfg.terms) ? cfg.terms : []);
	var sortOptions = Array.isArray(cfg.sortOptions) ? cfg.sortOptions : [];

	function buildOptions(defaultLabel, selected, includeNoTags, termsList) {
		var html = '<option value="">' + String(defaultLabel || '') + '</option>';
		if (includeNoTags) {
			var noTagsValue = String((cfg && cfg.noTagsValue) || '__um_no_tags__');
			var noTagsSelected = selected && selected === noTagsValue ? ' selected' : '';
			html += '<option value="' + noTagsValue.replace(/"/g, '&quot;') + '"' + noTagsSelected + '>'
				+ String((cfg.labels && cfg.labels.filterNoTags) || 'No tags').replace(/</g, '&lt;').replace(/>/g, '&gt;')
				+ '</option>';
		}
		(termsList || []).forEach(function(term) {
			if (!term || !term.slug) {
				return;
			}
			var isSelected = selected && selected === term.slug ? ' selected' : '';
			var termLabel = String(term.name || term.slug);
			if (term.inMenuNav || term.in_menu_nav) {
				termLabel += ' *';
			}
			html += '<option value="' + String(term.slug).replace(/"/g, '&quot;') + '"' + isSelected + '>'
				+ termLabel.replace(/</g, '&lt;').replace(/>/g, '&gt;')
				+ '</option>';
		});
		return html;
	}
	function buildSortOptions(selected, optionsList) {
		var html = '';
		(optionsList || []).forEach(function(option) {
			if (!option || typeof option !== 'object') {
				return;
			}
			var optionValue = String(option.value || '');
			var optionLabel = String(option.label || optionValue || '');
			var isSelected = selected === optionValue ? ' selected' : '';
			html += '<option value="' + optionValue.replace(/"/g, '&quot;') + '"' + isSelected + '>'
				+ optionLabel.replace(/</g, '&lt;').replace(/>/g, '&gt;')
				+ '</option>';
		});
		return html;
	}
	function parseSortBy(value) {
		var raw = String(value || '');
		var allowed = {};
		sortOptions.forEach(function(option) {
			if (option && typeof option === 'object') {
				allowed[String(option.value || '')] = true;
			}
		});
		return Object.prototype.hasOwnProperty.call(allowed, raw) ? raw : '';
	}

	function updateUrlParam(param, value) {
		var url = new URL(window.location.href);
		if (!value) {
			url.searchParams.delete(param);
		} else {
			url.searchParams.set(param, value);
		}
		window.location.href = url.toString();
	}
	function setUrlParamWithoutReload(param, value) {
		try {
			var url = new URL(window.location.href);
			if (!value) {
				url.searchParams.delete(param);
			} else {
				url.searchParams.set(param, value);
			}
			if (window.history && typeof window.history.replaceState === 'function') {
				window.history.replaceState(window.history.state, '', url.toString());
				return true;
			}
		} catch (err) {
		}
		return false;
	}
	function applySortToMediaFrame(sortValue, forceRefresh) {
		var selectedSort = parseSortBy(sortValue);
		try {
			if (!window.wp || !wp.media || !wp.media.frame || !wp.media.frame.state) {
				return false;
			}
			var state = wp.media.frame.state();
			if (!state) {
				return false;
			}
			if (state.props && state.props.set) {
				state.props.set({ um_media_library_sort: selectedSort });
			}
			var library = state.get ? state.get('library') : null;
			if (!library || !library.props || !library.props.set) {
				return false;
			}
			library.props.set({ um_media_library_sort: selectedSort });
			if (forceRefresh && typeof library._requery === 'function') {
				library._requery(true);
			}
			return true;
		} catch (err) {
			return false;
		}
	}

	function getSelectedMediaIdsFromGrid() {
		var ids = [];
		try {
			if (!window.wp || !wp.media || !wp.media.frame || !wp.media.frame.state) {
				return ids;
			}
			var state = wp.media.frame.state();
			var selection = state && state.get ? state.get('selection') : null;
			if (!selection || !selection.each) {
				return ids;
			}
			selection.each(function(model) {
				var id = parseInt(model.get('id'), 10);
				if (id > 0) {
					ids.push(id);
				}
			});
		} catch (err) {
			return ids;
		}
		return ids;
	}

	function ensureGridControls() {
		var $toolbar = $('.media-toolbar-secondary');
		if (!$toolbar.length || $('#um-media-library-tag-filter-grid').length) {
			return;
		}

		var selectedTag = String(cfg.selectedTag || '');
		var selectedSort = parseSortBy(cfg.selectedSort);
		var filterHtml = buildOptions(cfg.labels && cfg.labels.filterAll, selectedTag, true, filterTerms);
		var sortHtml = buildSortOptions(selectedSort, sortOptions);
		var bulkHtml = buildOptions(cfg.labels && cfg.labels.bulkChoose, '', false, bulkTerms);
		var $filterLabel = $('<label class="screen-reader-text" for="um-media-library-tag-filter-grid">Library Tag filter</label>');
		var $filter = $('<select id="um-media-library-tag-filter-grid" class="um-media-library-tag-control"></select>').html(filterHtml);
		var $sortLabel = $('<label class="screen-reader-text" for="um-media-library-sort-filter-grid">Media sort</label>');
		var $sort = $('<select id="um-media-library-sort-filter-grid" class="um-media-library-tag-control"></select>').html(sortHtml);
		var $bulkLabel = $('<label class="screen-reader-text" for="um-media-library-tag-bulk-grid">Bulk apply Library Tag</label>');
		var $bulk = $('<select id="um-media-library-tag-bulk-grid" class="um-media-library-tag-control"></select>').html(bulkHtml);
		var $newTag = $('<input type="text" id="um-media-library-tag-bulk-grid-new" class="um-media-library-tag-control" />').attr('placeholder', (cfg.labels && cfg.labels.bulkNewTagPlaceholder) || 'or enter tag');
		var $button = $('<button type="button" class="button media-button"></button>').text((cfg.labels && cfg.labels.bulkButton) || 'Apply Tag(s)');
		var $bulkWrap = $('<span class="um-media-library-tag-bulk-controls" style="display:none;"></span>');
		$filter.css({ display: 'inline-block', minWidth: '160px' });
		$sort.css({ display: 'inline-block', minWidth: '220px' });
		$bulk.css({ display: 'inline-block', minWidth: '190px' });
		$newTag.css({ display: 'inline-block', minWidth: '190px' });
		$button.css({ marginTop: '0' });
		$bulkWrap.css({
			display: 'none',
			alignItems: 'center',
			gap: '6px',
			marginTop: '4px',
			verticalAlign: 'middle'
		});
		$bulkWrap.append($bulkLabel).append($bulk).append($newTag).append($button);

		$toolbar.append($filterLabel).append($filter).append($sortLabel).append($sort).append($bulkWrap);

		function isBulkSelectModeActive() {
			var $toggle = $('.media-toolbar-secondary .select-mode-toggle-button').first();
			if (!$toggle.length) {
				return false;
			}
			var toggleText = String($.trim($toggle.text() || '')).toLowerCase();
			var pressed = String($toggle.attr('aria-pressed') || '').toLowerCase() === 'true';
			var expanded = String($toggle.attr('aria-expanded') || '').toLowerCase() === 'true';
			var activeClass = $toggle.hasClass('active');
			var cancelText = toggleText.indexOf('cancel') !== -1;
			var deleteVisible = $('.media-toolbar-secondary .delete-selected-button').first().is(':visible') && !$('.media-toolbar-secondary .delete-selected-button').first().hasClass('hidden');
			return pressed || expanded || activeClass || cancelText || deleteVisible;
		}

		function syncBulkControlsVisibility() {
			var isActive = isBulkSelectModeActive();
			$bulkWrap.toggle(!!isActive);
			if (!isActive) {
				$bulk.val('');
				$newTag.val('');
			}
			applyThumbnailTagBadges();
		}

		function applyThumbnailTagBadges() {
			if (!(cfg && cfg.showTagsOnThumbnailsWhenBulkSelecting)) {
				$('.attachments .attachment .um-media-library-tag-thumb-label').remove();
				return;
			}
			var showBadges = isBulkSelectModeActive();
			var tagsMap = cfg.attachmentTagsById || {};
			$('.attachments .attachment').each(function() {
				var $attachment = $(this);
				var modelId = String($attachment.data('id') || $attachment.attr('data-id') || '');
				$attachment.find('.um-media-library-tag-thumb-label').remove();
				if (!showBadges || !modelId || !Object.prototype.hasOwnProperty.call(tagsMap, modelId)) {
					return;
				}
				var tags = tagsMap[modelId];
				if (!Array.isArray(tags) || !tags.length) {
					return;
				}
				var $label = $('<div class="um-media-library-tag-thumb-label"></div>').text(((cfg.labels && cfg.labels.tagsLabel) || 'Tags') + ': ' + tags.join(', '));
				$attachment.css('position', 'relative');
				$attachment.append($label);
			});
		}

		syncBulkControlsVisibility();
		$(document).on('click.umMediaLibraryTagBulkSync', '.media-toolbar-secondary .select-mode-toggle-button', function() {
			window.setTimeout(syncBulkControlsVisibility, 60);
		});
		$(document).on('change.umMediaLibraryTagBulkSync', '.attachments-browser input[type="checkbox"]', function() {
			window.setTimeout(syncBulkControlsVisibility, 60);
		});
		$(document).on('click.umMediaLibraryTagBulkSync', '.media-toolbar-secondary .delete-selected-button', function() {
			window.setTimeout(syncBulkControlsVisibility, 60);
		});
		$(document).on('click.umMediaLibraryTagBulkSync', '.attachments .attachment', function() {
			window.setTimeout(syncBulkControlsVisibility, 60);
		});
		$(document).on('mouseenter.umMediaLibraryTagBulkSync', '.attachments .attachment', function() {
			window.setTimeout(applyThumbnailTagBadges, 0);
		});

		$filter.on('change', function() {
			updateUrlParam('um_media_library_tag', $(this).val());
		});
		$sort.on('change', function() {
			var nextSort = parseSortBy($(this).val());
			cfg.selectedSort = nextSort;
			var applied = applySortToMediaFrame(nextSort, true);
			var updatedUrl = setUrlParamWithoutReload('um_media_library_sort', nextSort);
			if (!applied || !updatedUrl) {
				updateUrlParam('um_media_library_sort', nextSort);
			}
		});
		if (selectedSort) {
			window.setTimeout(function() {
				applySortToMediaFrame(selectedSort, true);
			}, 60);
		}

		$button.on('click', function() {
			var tag = String($bulk.val() || '');
			var newTag = String($newTag.val() || '');
			var ids = getSelectedMediaIdsFromGrid();
			if (!ids.length) {
				window.alert((cfg.labels && cfg.labels.bulkNoSelection) || 'Select one or more media items first.');
				return;
			}
			if (!tag && !newTag) {
				window.alert((cfg.labels && cfg.labels.bulkNoTag) || 'Choose a Library Tag first.');
				return;
			}

			$button.prop('disabled', true);
			$.post(cfg.ajaxUrl, {
				action: 'user_manager_bulk_apply_media_library_tag',
				nonce: cfg.nonce,
				tag: tag,
				tag_new: newTag,
				ids: ids
			}).always(function() {
				window.location.reload();
			});
		});
	}

	$(function() {
		// Do not rely solely on URL/body classes for grid detection; some "All items"
		// views omit expected mode flags. Inject controls whenever the media toolbar exists.
		ensureGridControls();
		window.setTimeout(function() {
			$('.attachments-browser').on('DOMNodeInserted.umMediaLibraryTagBulkSync', function() {
				window.setTimeout(function() {
					applyThumbnailTagBadges();
				}, 40);
			});
		}, 100);
		var tries = 0;
		var timer = window.setInterval(function() {
			ensureGridControls();
			tries++;
			if (tries > 20 || $('#um-media-library-tag-filter-grid').length) {
				window.clearInterval(timer);
			}
		}, 300);
	});
})(jQuery);
JS;
		wp_add_inline_script('um-media-library-tags-admin', $script);
		$styles = <<<'CSS'
.attachments .attachment .um-media-library-tag-thumb-label {
	position: absolute;
	left: 6px;
	right: 6px;
	bottom: 6px;
	padding: 4px 6px;
	border-radius: 3px;
	background: rgba(34, 113, 177, 0.88);
	color: #fff;
	font-size: 11px;
	line-height: 1.3;
	z-index: 3;
	pointer-events: none;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
@media (max-width: 782px) {
	body.um-media-library-sticky-toolbar-mobile .media-frame .media-frame-toolbar,
	body.um-media-library-sticky-toolbar-mobile .media-frame .media-frame-router {
		position: sticky;
		top: var(--wp-admin--admin-bar--height, 46px);
		z-index: 1002;
		background: #fff;
	}
	body.um-media-library-sticky-toolbar-mobile .media-frame .media-frame-toolbar {
		border-bottom: 1px solid #dcdcde;
	}
}
CSS;
		wp_add_inline_style('media-views', $styles);
		if ($sticky_bulk_toolbar_mobile) {
			$sticky_script = <<<'JS'
(function($){
	$(function(){
		if (!(window.matchMedia)) {
			$('body').addClass('um-media-library-sticky-toolbar-mobile');
			return;
		}
		var mq = window.matchMedia('(max-width: 782px)');
		function syncStickyToolbarClass() {
			$('body').toggleClass('um-media-library-sticky-toolbar-mobile', !!mq.matches);
		}
		syncStickyToolbarClass();
		if (typeof mq.addEventListener === 'function') {
			mq.addEventListener('change', syncStickyToolbarClass);
		} else if (typeof mq.addListener === 'function') {
			mq.addListener(syncStickyToolbarClass);
		}
	});
})(jQuery);
JS;
			wp_add_inline_script('um-media-library-tags-admin', $sticky_script);
		}
		wp_enqueue_script('um-media-library-tags-admin');
	}

	/**
	 * Build attachment => tag names map for media toolbar overlays.
	 *
	 * @return array<string,array<int,string>>
	 */
	private static function get_media_library_attachment_tags_map(): array {
		global $wpdb;
		if (!($wpdb instanceof wpdb)) {
			return [];
		}

		$post_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
				'attachment',
				'inherit'
			)
		);
		if (!is_array($post_ids) || empty($post_ids)) {
			return [];
		}

		$ids = array_values(array_unique(array_filter(array_map('absint', $post_ids))));
		if (empty($ids)) {
			return [];
		}

		$map = [];
		$taxonomy = self::media_library_tags_taxonomy();
		foreach ($ids as $attachment_id) {
			$names = wp_get_object_terms($attachment_id, $taxonomy, ['fields' => 'names']);
			if (!is_array($names) || empty($names)) {
				continue;
			}
			$clean_names = array_values(array_filter(array_map('strval', $names), static function (string $name): bool {
				return trim($name) !== '';
			}));
			if (!empty($clean_names)) {
				$map[(string) $attachment_id] = $clean_names;
			}
		}

		return $map;
	}

	/**
	 * Register Media Library Tag Gallery block.
	 */
	public static function register_media_library_tags_gallery_block(): void {
		if (!function_exists('register_block_type')) {
			return;
		}

		$block_name = 'custom/media-library-tag-gallery';
		if (class_exists('WP_Block_Type_Registry')) {
			$registry = WP_Block_Type_Registry::get_instance();
			if ($registry instanceof WP_Block_Type_Registry && method_exists($registry, 'is_registered') && $registry->is_registered($block_name)) {
				return;
			}
		}

		register_block_type($block_name, [
			'render_callback' => [__CLASS__, 'render_media_library_tags_gallery_block'],
			'attributes' => [
				'tagSlug' => ['type' => 'string', 'default' => ''],
				'allowUrlTagOverride' => ['type' => 'boolean', 'default' => false],
				'allowAnyUrlParamTagIdentifier' => ['type' => 'boolean', 'default' => false],
				'requireTagValue' => ['type' => 'boolean', 'default' => false],
				'useDefaultColumnsDesktop' => ['type' => 'boolean', 'default' => true],
				'columnsDesktop' => ['type' => 'integer', 'default' => 4],
				'useDefaultColumnsDesktopLt50' => ['type' => 'boolean', 'default' => true],
				'columnsDesktopLt50' => ['type' => 'integer', 'default' => 4],
				'useDefaultColumnsDesktopLt25' => ['type' => 'boolean', 'default' => true],
				'columnsDesktopLt25' => ['type' => 'integer', 'default' => 4],
				'useDefaultColumnsDesktopLt10' => ['type' => 'boolean', 'default' => true],
				'columnsDesktopLt10' => ['type' => 'integer', 'default' => 4],
				'useDefaultColumnsMobile' => ['type' => 'boolean', 'default' => true],
				'columnsMobile' => ['type' => 'integer', 'default' => 2],
				'useDefaultSortOrder' => ['type' => 'boolean', 'default' => true],
				'sortOrder' => ['type' => 'string', 'default' => 'date_desc'],
				'useDefaultFileSize' => ['type' => 'boolean', 'default' => true],
				'fileSize' => ['type' => 'string', 'default' => 'thumbnail'],
				'useDefaultStyle' => ['type' => 'boolean', 'default' => true],
				'style' => ['type' => 'string', 'default' => 'uniform_grid'],
				'useDefaultPageLimit' => ['type' => 'boolean', 'default' => true],
				'pageLimit' => ['type' => 'integer', 'default' => 0],
				'useDefaultLinkTo' => ['type' => 'boolean', 'default' => true],
				'linkTo' => ['type' => 'string', 'default' => 'lightbox'],
				'useDefaultAlbumDescriptionPosition' => ['type' => 'boolean', 'default' => true],
				'albumDescriptionPosition' => ['type' => 'string', 'default' => 'none'],
				'useDefaultDescriptionDisplay' => ['type' => 'boolean', 'default' => true],
				'descriptionDisplay' => ['type' => 'string', 'default' => 'none'],
				'useDefaultDescriptionValue' => ['type' => 'boolean', 'default' => true],
				'descriptionValue' => ['type' => 'string', 'default' => 'caption'],
				'useDefaultLightboxPrevNextKeyboard' => ['type' => 'boolean', 'default' => true],
				'lightboxPrevNextKeyboard' => ['type' => 'boolean', 'default' => true],
				'useDefaultLightboxSwipeNavigation' => ['type' => 'boolean', 'default' => true],
				'lightboxSwipeNavigation' => ['type' => 'boolean', 'default' => false],
				'useDefaultLightboxTapSideNavigation' => ['type' => 'boolean', 'default' => true],
				'lightboxTapSideNavigation' => ['type' => 'boolean', 'default' => false],
				'useDefaultLightboxSlideshowButton' => ['type' => 'boolean', 'default' => true],
				'lightboxSlideshowButton' => ['type' => 'boolean', 'default' => false],
				'useDefaultLightboxSlideshowSeconds' => ['type' => 'boolean', 'default' => true],
				'lightboxSlideshowSeconds' => ['type' => 'number', 'default' => 3],
				'useDefaultLightboxSlideshowTransition' => ['type' => 'boolean', 'default' => true],
				'lightboxSlideshowTransition' => ['type' => 'string', 'default' => 'none'],
				'useDefaultLightboxModalBackgroundColor' => ['type' => 'boolean', 'default' => true],
				'lightboxModalBackgroundColor' => ['type' => 'string', 'default' => '#000000'],
				'useDefaultLightboxModalTextColor' => ['type' => 'boolean', 'default' => true],
				'lightboxModalTextColor' => ['type' => 'string', 'default' => '#ffffff'],
				'useDefaultSimpleLightboxThumbnailClick' => ['type' => 'boolean', 'default' => true],
				'simpleLightboxThumbnailClick' => ['type' => 'boolean', 'default' => true],
			],
			'editor_script' => 'um-media-library-tag-gallery-editor',
		]);
	}

	/**
	 * Enqueue editor assets for Media Library Tag Gallery block.
	 */
	public static function enqueue_media_library_tags_gallery_block_editor_assets(): void {
		$terms = get_terms([
			'taxonomy' => self::media_library_tags_taxonomy(),
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]);
		if (is_wp_error($terms)) {
			$terms = [];
		}

		$term_options = [
			['label' => __('All tags', 'user-manager'), 'value' => ''],
		];
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$term_options[] = [
				'label' => (string) $term->name,
				'value' => (string) $term->slug,
			];
		}

		$settings = User_Manager_Core::get_settings();
		$defaults = self::get_media_library_tag_gallery_defaults($settings);

		wp_register_script(
			'um-media-library-tag-gallery-editor',
			false,
			['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
			User_Manager_Core::VERSION,
			true
		);
		wp_add_inline_script(
			'um-media-library-tag-gallery-editor',
			'window.umMediaLibraryTagGalleryConfig = ' . wp_json_encode([
				'terms' => $term_options,
				'defaults' => $defaults,
				'imageSizes' => array_map(
					static function (string $size_value, string $size_label): array {
						return [
							'value' => $size_value,
							'label' => $size_label,
						];
					},
					array_keys(self::get_available_image_sizes_for_media_gallery()),
					array_values(self::get_available_image_sizes_for_media_gallery())
				),
			]) . ';',
			'before'
		);

		$script = <<<'JS'
(function(blocks, element, blockEditor, components) {
	var registerBlockType = blocks.registerBlockType;
	var Fragment = element.Fragment;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var ToggleControl = components.ToggleControl;

	var cfg = window.umMediaLibraryTagGalleryConfig || {};
	var defaults = cfg.defaults || {};
	var terms = Array.isArray(cfg.terms) ? cfg.terms : [{ label: 'All tags', value: '' }];
	var imageSizeOptions = Array.isArray(cfg.imageSizes) ? cfg.imageSizes.map(function(size) {
		return { label: String(size.label || size.value || ''), value: String(size.value || '') };
	}).filter(function(size) {
		return size.value !== '';
	}) : [];
	if (!imageSizeOptions.length) {
		imageSizeOptions = [
			{ label: 'Thumbnail', value: 'thumbnail' },
			{ label: 'Medium', value: 'medium' },
			{ label: 'Large', value: 'large' },
			{ label: 'Full Size', value: 'full' }
		];
	}
	var sortOrderOptions = [
		{ label: 'Date ASC', value: 'date_asc' },
		{ label: 'Date DESC', value: 'date_desc' },
		{ label: 'ID ASC', value: 'id_asc' },
		{ label: 'ID DESC', value: 'id_desc' },
		{ label: 'Filename ASC', value: 'filename_asc' },
		{ label: 'Filename DESC', value: 'filename_desc' },
		{ label: 'Caption ASC', value: 'caption_asc' },
		{ label: 'Caption DESC', value: 'caption_desc' },
		{ label: 'Random', value: 'random' }
	];
	var styleOptions = [
		{ label: '3D Perspective Gallery', value: 'perspective_3d' },
		{ label: 'Carousel / Slider Gallery', value: 'carousel_slider' },
		{ label: 'Circle CSS Crop', value: 'circle_crop' },
		{ label: 'Fullscreen Lightbox Grid', value: 'fullscreen_lightbox_grid' },
		{ label: 'Horizontal Scroll Gallery', value: 'horizontal_scroll' },
		{ label: 'Infinite Scroll Gallery', value: 'infinite_scroll' },
		{ label: 'Justified Row Layout', value: 'justified_rows' },
		{ label: 'Masonry / Pinterest Layout', value: 'masonry_pinterest' },
		{ label: 'Mosaic Grid (Irregular Tiles)', value: 'mosaic_grid' },
		{ label: 'Mosaic Grid (Taller Tiles)', value: 'mosaic_grid_taller' },
		{ label: 'Polaroid / Scrapbook Layout', value: 'polaroid_scrapbook' },
		{ label: 'Split Screen Feature Gallery', value: 'split_screen_feature' },
		{ label: 'Square CSS Crop', value: 'square_crop' },
		{ label: 'Standard', value: 'standard' },
		{ label: 'Tall Rectangle CSS Crop', value: 'tall_rectangle_crop' },
		{ label: 'Timeline / Story Gallery', value: 'timeline_story' },
		{ label: 'Uniform Grid (Classic Gallery)', value: 'uniform_grid' },
		{ label: 'Wide Rectangle CSS Crop', value: 'wide_rectangle_crop' }
	];
	var albumDescriptionPositionOptions = [
		{ label: 'none', value: 'none' },
		{ label: 'above gallery', value: 'above' },
		{ label: 'below gallery', value: 'below' }
	];
	var descriptionDisplayOptions = [
		{ label: 'none', value: 'none' },
		{ label: 'centered under photo', value: 'grid' },
		{ label: 'lightbox under photo', value: 'lightbox' },
		{ label: 'both', value: 'both' }
	];
	var descriptionValueOptions = [
		{ label: 'Caption', value: 'caption' },
		{ label: 'Filename', value: 'filename' },
		{ label: 'Title', value: 'title' },
		{ label: 'Description', value: 'description' },
		{ label: 'Alt text', value: 'alt' },
		{ label: 'Slug', value: 'slug' },
		{ label: 'Date', value: 'date' }
	];
	var slideshowTransitionOptions = [
		{ label: 'None', value: 'none' },
		{ label: 'Crossfade', value: 'crossfade' },
		{ label: 'Slide to Left', value: 'slide_left' }
	];
	var linkToOptions = [
		{ label: 'None', value: 'none' },
		{ label: 'Open Image', value: 'image' },
		{ label: 'Open Image in New Window', value: 'image_new_window' },
		{ label: 'Open Image in Lightbox', value: 'lightbox' }
	];
	var fallbackDefaults = {
		columnsDesktop: parseInt(defaults.columnsDesktop, 10) || 4,
		columnsDesktopLt50: parseInt(defaults.columnsDesktopLt50, 10) || 4,
		columnsDesktopLt25: parseInt(defaults.columnsDesktopLt25, 10) || 4,
		columnsDesktopLt10: parseInt(defaults.columnsDesktopLt10, 10) || 4,
		columnsMobile: parseInt(defaults.columnsMobile, 10) || 2,
		sortOrder: defaults.sortOrder || 'date_desc',
		fileSize: defaults.fileSize || 'thumbnail',
		style: defaults.style || 'uniform_grid',
		pageLimit: parseInt(defaults.pageLimit, 10) || 0,
		linkTo: (function() {
			var raw = String(defaults.linkTo || 'lightbox').toLowerCase();
			if (raw === 'media_permalink' || raw === 'media_file' || raw === 'file' || raw === 'open_image') {
				raw = 'image';
			}
			if (raw === 'new_window' || raw === 'open_image_new_window') {
				raw = 'image_new_window';
			}
			if (['none', 'image', 'image_new_window', 'lightbox'].indexOf(raw) === -1) {
				raw = 'lightbox';
			}
			return raw;
		})(),
		albumDescriptionPosition: defaults.albumDescriptionPosition || 'none',
		descriptionDisplay: defaults.descriptionDisplay || 'none',
		descriptionValue: defaults.descriptionValue || 'caption',
		lightboxPrevNextKeyboard: defaults.lightboxPrevNextKeyboard !== false,
		lightboxSwipeNavigation: !!defaults.lightboxSwipeNavigation,
		lightboxTapSideNavigation: !!defaults.lightboxTapSideNavigation,
		lightboxSlideshowButton: !!defaults.lightboxSlideshowButton,
		lightboxSlideshowSeconds: (function() {
			var seconds = parseFloat(defaults.lightboxSlideshowSeconds);
			if (!isFinite(seconds)) {
				seconds = 3;
			}
			return Math.max(1, Math.min(60, seconds));
		})(),
		lightboxSlideshowTransition: (function() {
			var raw = String(defaults.lightboxSlideshowTransition || 'none').toLowerCase();
			if (['none', 'crossfade', 'slide_left'].indexOf(raw) === -1) {
				raw = 'none';
			}
			return raw;
		})(),
		lightboxModalBackgroundColor: (function() {
			var raw = String(defaults.lightboxModalBackgroundColor || '#000000').trim();
			return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(raw) ? raw : '#000000';
		})(),
		lightboxModalTextColor: (function() {
			var raw = String(defaults.lightboxModalTextColor || '#ffffff').trim();
			return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(raw) ? raw : '#ffffff';
		})(),
		simpleLightboxThumbnailClick: defaults.simpleLightboxThumbnailClick !== false
	};

	registerBlockType('custom/media-library-tag-gallery', {
		title: 'Media Library Tag Gallery',
		icon: 'format-gallery',
		category: 'widgets',
		attributes: {
			tagSlug: { type: 'string', default: '' },
			allowUrlTagOverride: { type: 'boolean', default: false },
			allowAnyUrlParamTagIdentifier: { type: 'boolean', default: false },
			requireTagValue: { type: 'boolean', default: false },
			useDefaultColumnsDesktop: { type: 'boolean', default: true },
			columnsDesktop: { type: 'integer', default: parseInt(defaults.columnsDesktop, 10) || 4 },
			useDefaultColumnsDesktopLt50: { type: 'boolean', default: true },
			columnsDesktopLt50: { type: 'integer', default: parseInt(defaults.columnsDesktopLt50, 10) || 4 },
			useDefaultColumnsDesktopLt25: { type: 'boolean', default: true },
			columnsDesktopLt25: { type: 'integer', default: parseInt(defaults.columnsDesktopLt25, 10) || 4 },
			useDefaultColumnsDesktopLt10: { type: 'boolean', default: true },
			columnsDesktopLt10: { type: 'integer', default: parseInt(defaults.columnsDesktopLt10, 10) || 4 },
			useDefaultColumnsMobile: { type: 'boolean', default: true },
			columnsMobile: { type: 'integer', default: parseInt(defaults.columnsMobile, 10) || 2 },
			useDefaultSortOrder: { type: 'boolean', default: true },
			sortOrder: { type: 'string', default: defaults.sortOrder || 'date_desc' },
			useDefaultFileSize: { type: 'boolean', default: true },
			fileSize: { type: 'string', default: defaults.fileSize || 'thumbnail' },
			useDefaultStyle: { type: 'boolean', default: true },
			style: { type: 'string', default: defaults.style || 'uniform_grid' },
			useDefaultPageLimit: { type: 'boolean', default: true },
			pageLimit: { type: 'integer', default: parseInt(defaults.pageLimit, 10) || 0 },
			useDefaultLinkTo: { type: 'boolean', default: true },
			linkTo: { type: 'string', default: fallbackDefaults.linkTo },
			useDefaultAlbumDescriptionPosition: { type: 'boolean', default: true },
			albumDescriptionPosition: { type: 'string', default: defaults.albumDescriptionPosition || 'none' },
			useDefaultDescriptionDisplay: { type: 'boolean', default: true },
			descriptionDisplay: { type: 'string', default: defaults.descriptionDisplay || 'none' },
			useDefaultDescriptionValue: { type: 'boolean', default: true },
			descriptionValue: { type: 'string', default: defaults.descriptionValue || 'caption' },
			useDefaultLightboxPrevNextKeyboard: { type: 'boolean', default: true },
			lightboxPrevNextKeyboard: { type: 'boolean', default: defaults.lightboxPrevNextKeyboard !== false },
			useDefaultLightboxSwipeNavigation: { type: 'boolean', default: true },
			lightboxSwipeNavigation: { type: 'boolean', default: !!defaults.lightboxSwipeNavigation },
			useDefaultLightboxTapSideNavigation: { type: 'boolean', default: true },
			lightboxTapSideNavigation: { type: 'boolean', default: !!defaults.lightboxTapSideNavigation },
			useDefaultLightboxSlideshowButton: { type: 'boolean', default: true },
			lightboxSlideshowButton: { type: 'boolean', default: !!defaults.lightboxSlideshowButton },
			useDefaultLightboxSlideshowSeconds: { type: 'boolean', default: true },
			lightboxSlideshowSeconds: { type: 'number', default: (function() {
				var seconds = parseFloat(defaults.lightboxSlideshowSeconds);
				if (!isFinite(seconds)) {
					seconds = 3;
				}
				return Math.max(1, Math.min(60, seconds));
			})() },
			useDefaultLightboxSlideshowTransition: { type: 'boolean', default: true },
			lightboxSlideshowTransition: { type: 'string', default: (function() {
				var raw = String(defaults.lightboxSlideshowTransition || 'none').toLowerCase();
				if (['none', 'crossfade', 'slide_left'].indexOf(raw) === -1) {
					raw = 'none';
				}
				return raw;
			})() },
			useDefaultLightboxModalBackgroundColor: { type: 'boolean', default: true },
			lightboxModalBackgroundColor: { type: 'string', default: (function() {
				var raw = String(defaults.lightboxModalBackgroundColor || '#000000').trim();
				return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(raw) ? raw : '#000000';
			})() },
			useDefaultLightboxModalTextColor: { type: 'boolean', default: true },
			lightboxModalTextColor: { type: 'string', default: (function() {
				var raw = String(defaults.lightboxModalTextColor || '#ffffff').trim();
				return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(raw) ? raw : '#ffffff';
			})() },
			useDefaultSimpleLightboxThumbnailClick: { type: 'boolean', default: true },
			simpleLightboxThumbnailClick: { type: 'boolean', default: defaults.simpleLightboxThumbnailClick !== false }
		},
		edit: function(props) {
			var a = props.attributes;
			var set = props.setAttributes;
			function sanitizeHex(value, fallback) {
				var raw = String(value || '').trim();
				return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(raw) ? raw : fallback;
			}
			var useDefaultColumnsDesktop = !!a.useDefaultColumnsDesktop;
			var useDefaultColumnsDesktopLt50 = !!a.useDefaultColumnsDesktopLt50;
			var useDefaultColumnsDesktopLt25 = !!a.useDefaultColumnsDesktopLt25;
			var useDefaultColumnsDesktopLt10 = !!a.useDefaultColumnsDesktopLt10;
			var useDefaultColumnsMobile = !!a.useDefaultColumnsMobile;
			var useDefaultSortOrder = !!a.useDefaultSortOrder;
			var useDefaultFileSize = !!a.useDefaultFileSize;
			var useDefaultStyle = !!a.useDefaultStyle;
			var useDefaultPageLimit = !!a.useDefaultPageLimit;
			var useDefaultLinkTo = !!a.useDefaultLinkTo;
			var useDefaultAlbumDescriptionPosition = !!a.useDefaultAlbumDescriptionPosition;
			var useDefaultDescriptionDisplay = !!a.useDefaultDescriptionDisplay;
			var useDefaultDescriptionValue = !!a.useDefaultDescriptionValue;
			var useDefaultLightboxPrevNextKeyboard = !!a.useDefaultLightboxPrevNextKeyboard;
			var useDefaultLightboxSwipeNavigation = !!a.useDefaultLightboxSwipeNavigation;
			var useDefaultLightboxTapSideNavigation = !!a.useDefaultLightboxTapSideNavigation;
			var useDefaultLightboxSlideshowButton = !!a.useDefaultLightboxSlideshowButton;
			var useDefaultLightboxSlideshowSeconds = !!a.useDefaultLightboxSlideshowSeconds;
			var useDefaultLightboxSlideshowTransition = !!a.useDefaultLightboxSlideshowTransition;
			var useDefaultLightboxModalBackgroundColor = !!a.useDefaultLightboxModalBackgroundColor;
			var useDefaultLightboxModalTextColor = !!a.useDefaultLightboxModalTextColor;
			var useDefaultSimpleLightboxThumbnailClick = !!a.useDefaultSimpleLightboxThumbnailClick;
			var effectiveColumnsDesktop = useDefaultColumnsDesktop ? fallbackDefaults.columnsDesktop : (a.columnsDesktop || fallbackDefaults.columnsDesktop);
			var effectiveColumnsDesktopLt50 = useDefaultColumnsDesktopLt50 ? fallbackDefaults.columnsDesktopLt50 : (a.columnsDesktopLt50 || fallbackDefaults.columnsDesktopLt50);
			var effectiveColumnsDesktopLt25 = useDefaultColumnsDesktopLt25 ? fallbackDefaults.columnsDesktopLt25 : (a.columnsDesktopLt25 || fallbackDefaults.columnsDesktopLt25);
			var effectiveColumnsDesktopLt10 = useDefaultColumnsDesktopLt10 ? fallbackDefaults.columnsDesktopLt10 : (a.columnsDesktopLt10 || fallbackDefaults.columnsDesktopLt10);
			var effectiveColumnsMobile = useDefaultColumnsMobile ? fallbackDefaults.columnsMobile : (a.columnsMobile || fallbackDefaults.columnsMobile);
			var effectiveSortOrder = useDefaultSortOrder ? fallbackDefaults.sortOrder : (a.sortOrder || fallbackDefaults.sortOrder);
			var effectiveFileSize = useDefaultFileSize ? fallbackDefaults.fileSize : (a.fileSize || fallbackDefaults.fileSize);
			var effectiveStyle = useDefaultStyle ? fallbackDefaults.style : (a.style || fallbackDefaults.style);
			var effectivePageLimit = useDefaultPageLimit ? fallbackDefaults.pageLimit : (typeof a.pageLimit === 'number' ? a.pageLimit : fallbackDefaults.pageLimit);
			var effectiveLinkTo = useDefaultLinkTo ? fallbackDefaults.linkTo : String(a.linkTo || fallbackDefaults.linkTo);
			if (effectiveLinkTo === 'media_permalink' || effectiveLinkTo === 'media_file' || effectiveLinkTo === 'file' || effectiveLinkTo === 'open_image') {
				effectiveLinkTo = 'image';
			}
			if (effectiveLinkTo === 'new_window' || effectiveLinkTo === 'open_image_new_window') {
				effectiveLinkTo = 'image_new_window';
			}
			if (['none', 'image', 'image_new_window', 'lightbox'].indexOf(effectiveLinkTo) === -1) {
				effectiveLinkTo = 'lightbox';
			}
			var effectiveAlbumDescriptionPosition = useDefaultAlbumDescriptionPosition ? fallbackDefaults.albumDescriptionPosition : (a.albumDescriptionPosition || fallbackDefaults.albumDescriptionPosition);
			var effectiveDescriptionDisplay = useDefaultDescriptionDisplay ? fallbackDefaults.descriptionDisplay : (a.descriptionDisplay || fallbackDefaults.descriptionDisplay);
			var effectiveDescriptionValue = useDefaultDescriptionValue ? fallbackDefaults.descriptionValue : (a.descriptionValue || fallbackDefaults.descriptionValue);
			var effectiveLightboxPrevNextKeyboard = useDefaultLightboxPrevNextKeyboard ? !!fallbackDefaults.lightboxPrevNextKeyboard : !!a.lightboxPrevNextKeyboard;
			var effectiveLightboxSwipeNavigation = useDefaultLightboxSwipeNavigation ? !!fallbackDefaults.lightboxSwipeNavigation : !!a.lightboxSwipeNavigation;
			var effectiveLightboxTapSideNavigation = useDefaultLightboxTapSideNavigation ? !!fallbackDefaults.lightboxTapSideNavigation : !!a.lightboxTapSideNavigation;
			var effectiveLightboxSlideshowButton = useDefaultLightboxSlideshowButton ? !!fallbackDefaults.lightboxSlideshowButton : !!a.lightboxSlideshowButton;
			var effectiveLightboxSlideshowSecondsRaw = useDefaultLightboxSlideshowSeconds ? fallbackDefaults.lightboxSlideshowSeconds : parseFloat(a.lightboxSlideshowSeconds);
			var effectiveLightboxSlideshowSeconds = isFinite(effectiveLightboxSlideshowSecondsRaw) ? Math.max(1, Math.min(60, effectiveLightboxSlideshowSecondsRaw)) : fallbackDefaults.lightboxSlideshowSeconds;
			var effectiveLightboxSlideshowTransition = useDefaultLightboxSlideshowTransition ? String(fallbackDefaults.lightboxSlideshowTransition || 'none') : String(a.lightboxSlideshowTransition || 'none');
			var effectiveLightboxModalBackgroundColor = useDefaultLightboxModalBackgroundColor
				? fallbackDefaults.lightboxModalBackgroundColor
				: sanitizeHex(a.lightboxModalBackgroundColor, fallbackDefaults.lightboxModalBackgroundColor);
			var effectiveLightboxModalTextColor = useDefaultLightboxModalTextColor
				? fallbackDefaults.lightboxModalTextColor
				: sanitizeHex(a.lightboxModalTextColor, fallbackDefaults.lightboxModalTextColor);
			var effectiveSimpleLightboxThumbnailClick = useDefaultSimpleLightboxThumbnailClick
				? !!fallbackDefaults.simpleLightboxThumbnailClick
				: !!a.simpleLightboxThumbnailClick;
			return element.createElement(
				Fragment,
				{},
				element.createElement(
					InspectorControls,
					{},
					element.createElement(
						PanelBody,
						{ title: 'Gallery Settings', initialOpen: true },
						element.createElement(SelectControl, {
							label: 'Library Tag',
							value: a.tagSlug || '',
							options: terms,
							onChange: function(v){ set({ tagSlug: String(v || '') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Allow URL tag override (?tag=tag-slug, ?tag=tag+tag2 for AND, ?tag=tag_tag2 for OR)',
							checked: !!a.allowUrlTagOverride,
							onChange: function(v){ set({ allowUrlTagOverride: !!v }); },
							help: a.allowUrlTagOverride ? 'URL ?tag=... can override selected tag for this block. Use ?tag=tag+tag2 for AND logic and ?tag=tag_tag2 for OR logic.' : 'Use selected tag only.'
						}),
						element.createElement(ToggleControl, {
							label: 'Do Not Allow Empty Tag/Do Not Load without Tag Value',
							checked: !!a.requireTagValue,
							onChange: function(v){ set({ requireTagValue: !!v }); },
							help: !!a.requireTagValue ? 'Block will not load when effective tag is empty.' : 'Block can load when no tag value is set (shows all tags).'
						}),
						element.createElement(ToggleControl, {
							label: 'Allow Any URL Parameter to Be Used as a Tag Identifier such as ?tag-name for Shorter URLs',
							checked: !!a.allowAnyUrlParamTagIdentifier,
							onChange: function(v){ set({ allowAnyUrlParamTagIdentifier: !!v }); },
							help: !!a.allowAnyUrlParamTagIdentifier ? 'Any URL query key matching a tag slug expression can override the block tag (examples: ?tag-name, ?tag+tag2 for AND, ?tag_tag2 for OR). Also replaces [tag-name] and [tag-description] in post titles/content using the first resolved tag; if no valid URL tag is found, placeholders become empty.' : 'Only ?tag=tag-slug URL override is used.'
						}),
						element.createElement(TextControl, {
							label: 'Number of Columns (Desktop)',
							type: 'number',
							min: 1,
							max: 8,
							disabled: useDefaultColumnsDesktop,
							value: effectiveColumnsDesktop,
							help: useDefaultColumnsDesktop ? ('Using add-on default: ' + String(fallbackDefaults.columnsDesktop)) : '',
							onChange: function(v){ set({ columnsDesktop: Math.max(1, parseInt(v, 10) || 1) }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Number of Columns (Desktop)',
							checked: useDefaultColumnsDesktop,
							onChange: function(v){ set({ useDefaultColumnsDesktop: !!v }); }
						}),
						element.createElement(TextControl, {
							label: 'Number of Columns (Desktop) if less than 50 photos',
							type: 'number',
							min: 1,
							max: 8,
							disabled: useDefaultColumnsDesktopLt50,
							value: effectiveColumnsDesktopLt50,
							help: useDefaultColumnsDesktopLt50 ? ('Using add-on default: ' + String(fallbackDefaults.columnsDesktopLt50)) : '',
							onChange: function(v){ set({ columnsDesktopLt50: Math.max(1, parseInt(v, 10) || 1) }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Number of Columns (Desktop) if less than 50 photos',
							checked: useDefaultColumnsDesktopLt50,
							onChange: function(v){ set({ useDefaultColumnsDesktopLt50: !!v }); }
						}),
						element.createElement(TextControl, {
							label: 'Number of Columns (Desktop) if less than 25 photos',
							type: 'number',
							min: 1,
							max: 8,
							disabled: useDefaultColumnsDesktopLt25,
							value: effectiveColumnsDesktopLt25,
							help: useDefaultColumnsDesktopLt25 ? ('Using add-on default: ' + String(fallbackDefaults.columnsDesktopLt25)) : '',
							onChange: function(v){ set({ columnsDesktopLt25: Math.max(1, parseInt(v, 10) || 1) }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Number of Columns (Desktop) if less than 25 photos',
							checked: useDefaultColumnsDesktopLt25,
							onChange: function(v){ set({ useDefaultColumnsDesktopLt25: !!v }); }
						}),
						element.createElement(TextControl, {
							label: 'Number of Columns (Desktop) if less than 10 photos',
							type: 'number',
							min: 1,
							max: 8,
							disabled: useDefaultColumnsDesktopLt10,
							value: effectiveColumnsDesktopLt10,
							help: useDefaultColumnsDesktopLt10 ? ('Using add-on default: ' + String(fallbackDefaults.columnsDesktopLt10)) : '',
							onChange: function(v){ set({ columnsDesktopLt10: Math.max(1, parseInt(v, 10) || 1) }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Number of Columns (Desktop) if less than 10 photos',
							checked: useDefaultColumnsDesktopLt10,
							onChange: function(v){ set({ useDefaultColumnsDesktopLt10: !!v }); }
						}),
						element.createElement(TextControl, {
							label: 'Number of Columns (Mobile)',
							type: 'number',
							min: 1,
							max: 4,
							disabled: useDefaultColumnsMobile,
							value: effectiveColumnsMobile,
							help: useDefaultColumnsMobile ? ('Using add-on default: ' + String(fallbackDefaults.columnsMobile)) : '',
							onChange: function(v){ set({ columnsMobile: Math.max(1, parseInt(v, 10) || 1) }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Number of Columns (Mobile)',
							checked: useDefaultColumnsMobile,
							onChange: function(v){ set({ useDefaultColumnsMobile: !!v }); }
						}),
						element.createElement(SelectControl, {
							label: 'Sort Order',
							disabled: useDefaultSortOrder,
							value: effectiveSortOrder,
							help: useDefaultSortOrder ? ('Using add-on default: ' + String(fallbackDefaults.sortOrder)) : '',
							options: sortOrderOptions,
							onChange: function(v){ set({ sortOrder: String(v || 'date_desc') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Sort Order',
							checked: useDefaultSortOrder,
							onChange: function(v){ set({ useDefaultSortOrder: !!v }); }
						}),
						element.createElement(SelectControl, {
							label: 'File Size',
							disabled: useDefaultFileSize,
							value: effectiveFileSize,
							help: useDefaultFileSize ? ('Using add-on default: ' + String(fallbackDefaults.fileSize)) : '',
							options: imageSizeOptions,
							onChange: function(v){ set({ fileSize: String(v || 'thumbnail') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for File Size',
							checked: useDefaultFileSize,
							onChange: function(v){ set({ useDefaultFileSize: !!v }); }
						}),
						element.createElement(SelectControl, {
							label: 'Style',
							disabled: useDefaultStyle,
							value: effectiveStyle,
							help: useDefaultStyle ? ('Using add-on default: ' + String(fallbackDefaults.style)) : '',
							options: styleOptions,
							onChange: function(v){ set({ style: String(v || 'uniform_grid') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Style',
							checked: useDefaultStyle,
							onChange: function(v){ set({ useDefaultStyle: !!v }); }
						}),
						element.createElement(TextControl, {
							label: 'Page Limit (0 = unlimited)',
							type: 'number',
							min: 0,
							disabled: useDefaultPageLimit,
							value: effectivePageLimit,
							help: useDefaultPageLimit ? ('Using add-on default: ' + String(fallbackDefaults.pageLimit)) : '',
							onChange: function(v){ set({ pageLimit: Math.max(0, parseInt(v, 10) || 0) }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Page Limit',
							checked: useDefaultPageLimit,
							onChange: function(v){ set({ useDefaultPageLimit: !!v }); }
						}),
						element.createElement(SelectControl, {
							label: 'Link To',
							disabled: useDefaultLinkTo,
							value: effectiveLinkTo,
							help: useDefaultLinkTo ? ('Using add-on default: ' + String(fallbackDefaults.linkTo)) : '',
							options: linkToOptions,
							onChange: function(v){ set({ linkTo: String(v || 'lightbox') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Link To',
							checked: useDefaultLinkTo,
							onChange: function(v){ set({ useDefaultLinkTo: !!v }); }
						}),
						element.createElement(SelectControl, {
							label: 'Display Album Tag Description(s)',
							disabled: useDefaultAlbumDescriptionPosition,
							value: effectiveAlbumDescriptionPosition,
							help: useDefaultAlbumDescriptionPosition ? ('Using add-on default: ' + String(fallbackDefaults.albumDescriptionPosition)) : '',
							options: albumDescriptionPositionOptions,
							onChange: function(v){ set({ albumDescriptionPosition: String(v || 'none') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Album Tag Description(s) Position',
							checked: useDefaultAlbumDescriptionPosition,
							onChange: function(v){ set({ useDefaultAlbumDescriptionPosition: !!v }); }
						}),
						element.createElement(SelectControl, {
							label: 'Description Display',
							disabled: useDefaultDescriptionDisplay,
							value: effectiveDescriptionDisplay,
							help: useDefaultDescriptionDisplay ? ('Using add-on default: ' + String(fallbackDefaults.descriptionDisplay)) : '',
							options: descriptionDisplayOptions,
							onChange: function(v){ set({ descriptionDisplay: String(v || 'none') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Description Display',
							checked: useDefaultDescriptionDisplay,
							onChange: function(v){ set({ useDefaultDescriptionDisplay: !!v }); }
						}),
						element.createElement(SelectControl, {
							label: 'Description Value',
							disabled: useDefaultDescriptionValue,
							value: effectiveDescriptionValue,
							help: useDefaultDescriptionValue ? ('Using add-on default: ' + String(fallbackDefaults.descriptionValue)) : '',
							options: descriptionValueOptions,
							onChange: function(v){ set({ descriptionValue: String(v || 'caption') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Description Value',
							checked: useDefaultDescriptionValue,
							onChange: function(v){ set({ useDefaultDescriptionValue: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Add Previous & Next Links in Lightbox Window and Allow Keyboard Arrows Shortcut',
							checked: effectiveLightboxPrevNextKeyboard,
							disabled: useDefaultLightboxPrevNextKeyboard,
							help: useDefaultLightboxPrevNextKeyboard ? ('Using add-on default: ' + (fallbackDefaults.lightboxPrevNextKeyboard ? 'enabled' : 'disabled')) : '',
							onChange: function(v){ set({ lightboxPrevNextKeyboard: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Lightbox Previous/Next + Keyboard Arrows',
							checked: useDefaultLightboxPrevNextKeyboard,
							onChange: function(v){ set({ useDefaultLightboxPrevNextKeyboard: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Allow Swipe to Left or Right to go to Previous or Next Photo',
							checked: effectiveLightboxSwipeNavigation,
							disabled: useDefaultLightboxSwipeNavigation,
							help: useDefaultLightboxSwipeNavigation ? ('Using add-on default: ' + (fallbackDefaults.lightboxSwipeNavigation ? 'enabled' : 'disabled')) : '',
							onChange: function(v){ set({ lightboxSwipeNavigation: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Lightbox Swipe Navigation',
							checked: useDefaultLightboxSwipeNavigation,
							onChange: function(v){ set({ useDefaultLightboxSwipeNavigation: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Allow Tap or Click on Left or Right side of image to go to Previous or Next Photo',
							checked: effectiveLightboxTapSideNavigation,
							disabled: useDefaultLightboxTapSideNavigation,
							help: useDefaultLightboxTapSideNavigation ? ('Using add-on default: ' + (fallbackDefaults.lightboxTapSideNavigation ? 'enabled' : 'disabled')) : '',
							onChange: function(v){ set({ lightboxTapSideNavigation: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Lightbox Left/Right Tap Navigation',
							checked: useDefaultLightboxTapSideNavigation,
							onChange: function(v){ set({ useDefaultLightboxTapSideNavigation: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Add a Play Slideshow Button in Lightbox Window',
							checked: effectiveLightboxSlideshowButton,
							disabled: useDefaultLightboxSlideshowButton,
							help: useDefaultLightboxSlideshowButton ? ('Using add-on default: ' + (fallbackDefaults.lightboxSlideshowButton ? 'enabled' : 'disabled')) : '',
							onChange: function(v){ set({ lightboxSlideshowButton: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Lightbox Play Slideshow Button',
							checked: useDefaultLightboxSlideshowButton,
							onChange: function(v){ set({ useDefaultLightboxSlideshowButton: !!v }); }
						}),
						element.createElement(TextControl, {
							label: 'Slideshow Seconds Per Photo',
							type: 'number',
							min: 1,
							max: 60,
							step: 0.1,
							disabled: useDefaultLightboxSlideshowSeconds,
							value: String(effectiveLightboxSlideshowSeconds),
							help: useDefaultLightboxSlideshowSeconds ? ('Using add-on default: ' + String(fallbackDefaults.lightboxSlideshowSeconds)) : '',
							onChange: function(v){
								var parsed = parseFloat(v);
								if (!isFinite(parsed)) {
									parsed = fallbackDefaults.lightboxSlideshowSeconds;
								}
								set({ lightboxSlideshowSeconds: Math.max(1, Math.min(60, parsed)) });
							}
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Slideshow Seconds Per Photo',
							checked: useDefaultLightboxSlideshowSeconds,
							onChange: function(v){ set({ useDefaultLightboxSlideshowSeconds: !!v }); }
						}),
						element.createElement(SelectControl, {
							label: 'Slideshow Transition',
							disabled: useDefaultLightboxSlideshowTransition,
							value: effectiveLightboxSlideshowTransition,
							help: useDefaultLightboxSlideshowTransition ? ('Using add-on default: ' + String(fallbackDefaults.lightboxSlideshowTransition)) : '',
							options: slideshowTransitionOptions,
							onChange: function(v){ set({ lightboxSlideshowTransition: String(v || 'none') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Slideshow Transition',
							checked: useDefaultLightboxSlideshowTransition,
							onChange: function(v){ set({ useDefaultLightboxSlideshowTransition: !!v }); }
						}),
						element.createElement(TextControl, {
							label: 'Lightbox Modal Background Color (hex)',
							disabled: useDefaultLightboxModalBackgroundColor,
							value: String(effectiveLightboxModalBackgroundColor || '#000000'),
							help: useDefaultLightboxModalBackgroundColor ? ('Using add-on default: ' + String(fallbackDefaults.lightboxModalBackgroundColor)) : 'Example: #000000',
							onChange: function(v){ set({ lightboxModalBackgroundColor: sanitizeHex(v, '#000000') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Lightbox Modal Background Color',
							checked: useDefaultLightboxModalBackgroundColor,
							onChange: function(v){ set({ useDefaultLightboxModalBackgroundColor: !!v }); }
						}),
						element.createElement(TextControl, {
							label: 'Lightbox Modal Text Color (hex)',
							disabled: useDefaultLightboxModalTextColor,
							value: String(effectiveLightboxModalTextColor || '#ffffff'),
							help: useDefaultLightboxModalTextColor ? ('Using add-on default: ' + String(fallbackDefaults.lightboxModalTextColor)) : 'Example: #ffffff',
							onChange: function(v){ set({ lightboxModalTextColor: sanitizeHex(v, '#ffffff') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Lightbox Modal Text Color',
							checked: useDefaultLightboxModalTextColor,
							onChange: function(v){ set({ useDefaultLightboxModalTextColor: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Allow Simple Lightbox when clicking on a thumbnail',
							checked: effectiveSimpleLightboxThumbnailClick,
							disabled: useDefaultSimpleLightboxThumbnailClick,
							help: useDefaultSimpleLightboxThumbnailClick ? ('Using add-on default: ' + (fallbackDefaults.simpleLightboxThumbnailClick ? 'enabled' : 'disabled')) : 'Shows a simplified image-only lightbox (close + image only).',
							onChange: function(v){ set({ simpleLightboxThumbnailClick: !!v }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Simple Lightbox thumbnail click',
							checked: useDefaultSimpleLightboxThumbnailClick,
							onChange: function(v){ set({ useDefaultSimpleLightboxThumbnailClick: !!v }); }
						})
					)
				),
				element.createElement('p', {}, 'Media Library Tag Gallery preview is shown on the front-end.')
			);
		},
		save: function() { return null; }
	});
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
JS;
		wp_add_inline_script('um-media-library-tag-gallery-editor', $script);
		wp_enqueue_script('um-media-library-tag-gallery-editor');
	}

	/**
	 * Print resilient front-end lightbox bootstrap script early in <head>.
	 * This script avoids jQuery and can survive later in-body script errors.
	 */
	public static function print_media_library_tags_inline_lightbox_bootstrap(): void {
		static $printed = false;
		if ($printed) {
			return;
		}
		if (is_admin()) {
			return;
		}
		$printed = true;
		?>
		<script>
		(function() {
			if (!window.umMediaLibraryLightboxViewTracker) {
				var trackedByOverlay = {};
				window.umMediaLibraryLightboxViewTracker = {
					track: function(trigger) {
						if (!trigger || !trigger.getAttribute) {
							return;
						}
						var attachmentIdRaw = trigger.getAttribute('data-um-modal-attachment-id') || trigger.getAttribute('data-um-lightbox-attachment-id') || '';
						var attachmentId = parseInt(String(attachmentIdRaw || '0'), 10);
						if (isNaN(attachmentId) || attachmentId <= 0) {
							return;
						}
						var root = trigger.closest ? trigger.closest('.um-media-library-tag-gallery') : null;
						var overlayId = '';
						if (root && root.id) {
							overlayId = String(root.id) + '-lightbox';
						}
						if (overlayId !== '' && trackedByOverlay[overlayId] === attachmentId) {
							return;
						}
						if (overlayId !== '') {
							trackedByOverlay[overlayId] = attachmentId;
						}
						var trackUrl = root && root.getAttribute ? String(root.getAttribute('data-um-lightbox-track-url') || '') : '';
						var trackNonce = root && root.getAttribute ? String(root.getAttribute('data-um-lightbox-track-nonce') || '') : '';
						if (!trackUrl || !trackNonce) {
							return;
						}
						var payload = 'action=user_manager_track_media_lightbox_view'
							+ '&nonce=' + encodeURIComponent(trackNonce)
							+ '&attachment_id=' + encodeURIComponent(String(attachmentId));
						try {
							if (window.fetch) {
								window.fetch(trackUrl, {
									method: 'POST',
									credentials: 'same-origin',
									headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
									body: payload
								}).catch(function() {});
								return;
							}
						} catch (err) {
						}
						try {
							var xhr = new XMLHttpRequest();
							xhr.open('POST', trackUrl, true);
							xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
							xhr.send(payload);
						} catch (err) {
						}
					},
					reset: function(overlayId) {
						if (!overlayId) {
							return;
						}
						delete trackedByOverlay[String(overlayId)];
					}
				};
			}
			if (window.umMltgInline) {
				return;
			}
			var triggerSelector = '.um-media-library-tag-gallery-lightbox-trigger,[data-um-modal-trigger],[data-um-lightbox]';
			var overlayTapNavigateThresholdRatio = 0.12;
			var deepLinkParam = 'image';
			var legacyDeepLinkParam = 'um_lightbox_image_id';
			var api = {};
			var tracker = window.umMediaLibraryLightboxViewTracker || null;
			function escapeRegexParam(value) {
				return String(value || '').replace(/[\\^$.*+?()[\]{}|]/g, '\\$&');
			}
			function parseAttachmentIdValue(rawValue) {
				var parsed = parseInt(String(rawValue || '0'), 10);
				if (isNaN(parsed) || parsed < 1) {
					return 0;
				}
				return parsed;
			}
			function getAttachmentIdFromTrigger(trigger) {
				if (!trigger || !trigger.getAttribute) {
					return 0;
				}
				return parseAttachmentIdValue(
					trigger.getAttribute('data-um-modal-attachment-id')
					|| trigger.getAttribute('data-um-lightbox-attachment-id')
					|| ''
				);
			}
			function readAttachmentIdFromUrl() {
				var raw = '';
				try {
					var urlObj = new URL(window.location.href);
					raw = String(urlObj.searchParams.get(deepLinkParam) || urlObj.searchParams.get(legacyDeepLinkParam) || '');
				} catch (err) {
					var query = String(window.location.search || '');
					var regex = new RegExp('(?:\\?|&)' + escapeRegexParam(deepLinkParam) + '=([^&#]*)', 'i');
					var matches = query.match(regex);
					if ((!matches || typeof matches[1] === 'undefined') && legacyDeepLinkParam) {
						var legacyRegex = new RegExp('(?:\\?|&)' + escapeRegexParam(legacyDeepLinkParam) + '=([^&#]*)', 'i');
						matches = query.match(legacyRegex);
					}
					if (matches && typeof matches[1] !== 'undefined') {
						raw = decodeURIComponent(String(matches[1] || '').replace(/\+/g, ' '));
					}
				}
				return parseAttachmentIdValue(raw);
			}
			function updateAttachmentIdInUrl(attachmentId) {
				if (!window.history || typeof window.history.replaceState !== 'function') {
					return;
				}
				try {
					var urlObj = new URL(window.location.href);
					if (attachmentId > 0) {
						urlObj.searchParams.set(deepLinkParam, String(attachmentId));
						if (legacyDeepLinkParam) {
							urlObj.searchParams.delete(legacyDeepLinkParam);
						}
					} else {
						urlObj.searchParams.delete(deepLinkParam);
						if (legacyDeepLinkParam) {
							urlObj.searchParams.delete(legacyDeepLinkParam);
						}
					}
					window.history.replaceState(window.history.state, '', urlObj.toString());
				} catch (err) {
				}
			}
			function trackLightboxView(trigger) {
				if (!tracker || typeof tracker.track !== 'function' || !trigger) {
					return;
				}
				tracker.track(trigger);
			}
			function getItems(root, trigger) {
				if (root && root.querySelectorAll) {
					var fromRoot = Array.prototype.slice.call(root.querySelectorAll(triggerSelector));
					if (fromRoot.length) {
						return fromRoot;
					}
				}
				return trigger ? [trigger] : [];
			}
			function updateControls(overlay, allowPrevNext, allowSlideshow) {
				var controlsWrap = overlay.querySelector('.um-mltg-lightbox-controls');
				var prevBtn = overlay.querySelector('.um-mltg-lightbox-prev');
				var nextBtn = overlay.querySelector('.um-mltg-lightbox-next');
				var slideshowBtn = overlay.querySelector('.um-mltg-lightbox-slideshow-toggle');
				if (controlsWrap) {
					controlsWrap.style.display = (allowPrevNext || allowSlideshow) ? 'flex' : 'none';
				}
				if (prevBtn) {
					prevBtn.style.display = allowPrevNext ? 'inline-block' : 'none';
				}
				if (nextBtn) {
					nextBtn.style.display = allowPrevNext ? 'inline-block' : 'none';
				}
				if (slideshowBtn) {
					slideshowBtn.style.display = allowSlideshow ? 'inline-block' : 'none';
					slideshowBtn.textContent = 'Play Slideshow';
				}
			}
			function bindSwipeHandlers(overlay) {
				if (!overlay || overlay.__umInlineSwipeBound) {
					return;
				}
				overlay.__umInlineSwipeBound = true;
				var swipeStartX = 0;
				var swipeStartY = 0;
				var swipeTracking = false;
				var swipeThresholdPx = 44;
				overlay.addEventListener('touchstart', function(event) {
					var allowSwipe = overlay.getAttribute('data-um-inline-swipe') === '1';
					if (!allowSwipe || overlay.getAttribute('aria-hidden') !== 'false') {
						swipeTracking = false;
						return;
					}
					var touch = event.touches && event.touches.length ? event.touches[0] : null;
					if (!touch) {
						swipeTracking = false;
						return;
					}
					swipeStartX = touch.clientX;
					swipeStartY = touch.clientY;
					swipeTracking = true;
				});
				overlay.addEventListener('touchend', function(event) {
					if (!swipeTracking) {
						return;
					}
					swipeTracking = false;
					var touch = event.changedTouches && event.changedTouches.length ? event.changedTouches[0] : null;
					if (!touch) {
						return;
					}
					var deltaX = touch.clientX - swipeStartX;
					var deltaY = touch.clientY - swipeStartY;
					var absX = Math.abs(deltaX);
					var absY = Math.abs(deltaY);
					if (absX < swipeThresholdPx || absX < (absY * 1.2)) {
						return;
					}
					var direction = deltaX < 0 ? 1 : -1;
					var navBtn = direction > 0
						? overlay.querySelector('.um-mltg-lightbox-next')
						: overlay.querySelector('.um-mltg-lightbox-prev');
					if (navBtn) {
						api.step(navBtn, direction, null);
					}
				});
				overlay.addEventListener('touchcancel', function() {
					swipeTracking = false;
				});
			}
			function bindTapNavigationHandlers(overlay) {
				if (!overlay || overlay.__umInlineTapNavBound) {
					return;
				}
				overlay.__umInlineTapNavBound = true;
				overlay.addEventListener('click', function(event) {
					if (!event || event.target !== overlay) {
						return;
					}
					var allowTapNavigate = overlay.getAttribute('data-um-inline-tap-nav') === '1';
					if (!allowTapNavigate || overlay.getAttribute('aria-hidden') !== 'false') {
						return;
					}
					var allowPrevNext = overlay.getAttribute('data-um-inline-prevnext') === '1';
					var allowSwipe = overlay.getAttribute('data-um-inline-swipe') === '1';
					if (!allowPrevNext && !allowSwipe) {
						return;
					}
					var rect = overlay.getBoundingClientRect ? overlay.getBoundingClientRect() : null;
					if (!rect || rect.width <= 0) {
						return;
					}
					var edgeWidth = Math.max(48, Math.round(rect.width * overlayTapNavigateThresholdRatio));
					var x = typeof event.clientX === 'number' ? event.clientX : 0;
					var direction = 0;
					if (x <= rect.left + edgeWidth) {
						direction = -1;
					} else if (x >= rect.right - edgeWidth) {
						direction = 1;
					}
					if (!direction) {
						return;
					}
					event.preventDefault();
					if (typeof event.stopImmediatePropagation === 'function') {
						event.stopImmediatePropagation();
					} else if (typeof event.stopPropagation === 'function') {
						event.stopPropagation();
					}
					var navBtn = direction > 0
						? overlay.querySelector('.um-mltg-lightbox-next')
						: overlay.querySelector('.um-mltg-lightbox-prev');
					if (navBtn) {
						api.step(navBtn, direction, null);
					}
				}, true);
			}
			function renderOverlay(overlay, trigger, root, items, index, animate) {
				var src = trigger.getAttribute('data-um-modal-src') || trigger.getAttribute('data-um-lightbox-src') || trigger.getAttribute('href') || '';
				if (!src) {
					return false;
				}
				var caption = trigger.getAttribute('data-um-modal-caption') || trigger.getAttribute('data-um-lightbox-caption') || '';
				var altText = trigger.getAttribute('data-um-modal-alt') || trigger.getAttribute('data-um-lightbox-alt') || '';
				var editUrl = trigger.getAttribute('data-um-modal-edit-url') || trigger.getAttribute('data-um-lightbox-edit-url') || '';
				var attachmentId = getAttachmentIdFromTrigger(trigger);
				var image = overlay.querySelector('img');
				var captionEl = overlay.querySelector('.um-mltg-lightbox-caption');
				var editLinkEl = overlay.querySelector('.um-mltg-lightbox-edit-link');
				var tagToolsEl = overlay.querySelector('.um-mltg-lightbox-tag-tools');
				if (image) {
					image.setAttribute('src', src);
					image.setAttribute('alt', altText);
				}
				if (captionEl) {
					captionEl.textContent = caption;
					captionEl.style.display = caption ? 'block' : 'none';
				}
				if (editLinkEl) {
					if (editUrl) {
						editLinkEl.setAttribute('href', editUrl);
						editLinkEl.style.display = 'inline-block';
					} else {
						editLinkEl.setAttribute('href', '#');
						editLinkEl.style.display = 'none';
					}
				}
				if (tagToolsEl) {
					tagToolsEl.style.display = 'none';
				}
				var allowPrevNext = !!(root && root.getAttribute && root.getAttribute('data-um-lightbox-prev-next') === '1');
				var allowSwipe = !!(root && root.getAttribute && root.getAttribute('data-um-lightbox-swipe') === '1');
				var allowTapNavigation = !!(root && root.getAttribute && root.getAttribute('data-um-lightbox-tap-nav') === '1');
				var allowSlideshow = !!(root && root.getAttribute && root.getAttribute('data-um-lightbox-slideshow') === '1');
				var slideshowSecondsRaw = root && root.getAttribute ? parseFloat(String(root.getAttribute('data-um-lightbox-seconds') || '3')) : 3;
				var slideshowSeconds = isFinite(slideshowSecondsRaw) ? Math.max(1, Math.min(60, slideshowSecondsRaw)) : 3;
				var slideshowTransition = root && root.getAttribute ? String(root.getAttribute('data-um-lightbox-transition') || 'none') : 'none';
				var normalizedTransition = (slideshowTransition === 'crossfade' || slideshowTransition === 'slide_left') ? slideshowTransition : 'none';
				updateControls(overlay, allowPrevNext, allowSlideshow);
				overlay.classList.remove('um-mltg-transition-crossfade', 'um-mltg-transition-slide-left');
				if (normalizedTransition === 'crossfade') {
					overlay.classList.add('um-mltg-transition-crossfade');
				} else if (normalizedTransition === 'slide_left') {
					overlay.classList.add('um-mltg-transition-slide-left');
				}
				var shouldAnimate = !!animate && normalizedTransition !== 'none';
				if (image) {
					image.classList.remove('is-transitioning');
				}
				if (shouldAnimate && image) {
					image.classList.add('is-transitioning');
				}
				if (root && root.id) {
					overlay.setAttribute('data-um-inline-root-id', root.id);
				} else {
					overlay.setAttribute('data-um-inline-root-id', '');
				}
				overlay.setAttribute('data-um-inline-prevnext', allowPrevNext ? '1' : '0');
				overlay.setAttribute('data-um-inline-swipe', allowSwipe ? '1' : '0');
				overlay.setAttribute('data-um-inline-tap-nav', allowTapNavigation ? '1' : '0');
				overlay.setAttribute('data-um-inline-index', String(Math.max(0, index)));
				overlay.setAttribute('data-um-inline-attachment-id', String(attachmentId));
				overlay.setAttribute('data-um-inline-slideshow', allowSlideshow ? '1' : '0');
				overlay.setAttribute('data-um-inline-slideshow-seconds', String(slideshowSeconds));
				overlay.setAttribute('data-um-inline-transition', normalizedTransition);
				updateAttachmentIdInUrl(attachmentId);
				trackLightboxView(trigger);
				bindSwipeHandlers(overlay);
				bindTapNavigationHandlers(overlay);
				overlay.style.display = 'flex';
				overlay.setAttribute('aria-hidden', 'false');
				overlay.setAttribute('tabindex', '-1');
				if (overlay.focus) {
					overlay.focus();
				}
				if (document && document.body) {
					document.body.style.overflow = 'hidden';
				}
				if (shouldAnimate && image) {
					window.setTimeout(function() {
						image.classList.remove('is-transitioning');
					}, 160);
				}
				var slideshowTimer = overlay.__umInlineSlideshowTimer || null;
				if (slideshowTimer) {
					window.clearInterval(slideshowTimer);
					overlay.__umInlineSlideshowTimer = null;
				}
				var slideshowPlaying = !!overlay.__umInlineSlideshowPlaying;
				overlay.__umInlineSlideshowPlaying = false;
				var slideshowBtn = overlay.querySelector('.um-mltg-lightbox-slideshow-toggle');
				if (slideshowBtn && slideshowPlaying && allowSlideshow) {
					var intervalMs = Math.max(1000, Math.round(slideshowSeconds * 1000));
					overlay.__umInlineSlideshowPlaying = true;
					slideshowBtn.textContent = 'Pause Slideshow';
					overlay.__umInlineSlideshowTimer = window.setInterval(function() {
						if (overlay.getAttribute('aria-hidden') !== 'false' || overlay.getAttribute('data-um-inline-slideshow') !== '1') {
							if (overlay.__umInlineSlideshowTimer) {
								window.clearInterval(overlay.__umInlineSlideshowTimer);
								overlay.__umInlineSlideshowTimer = null;
							}
							overlay.__umInlineSlideshowPlaying = false;
							if (slideshowBtn) {
								slideshowBtn.textContent = 'Play Slideshow';
							}
							return;
						}
						var nextBtn = overlay.querySelector('.um-mltg-lightbox-next');
						if (nextBtn) {
							api.step(nextBtn, 1, null);
						}
					}, intervalMs);
				}
				return true;
			}
			api.open = function(trigger, overlayId, event) {
				if (event) {
					event.preventDefault();
					if (typeof event.stopImmediatePropagation === 'function') {
						event.stopImmediatePropagation();
					} else if (typeof event.stopPropagation === 'function') {
						event.stopPropagation();
					}
				}
				var overlay = document.getElementById(String(overlayId || ''));
				if (!overlay || !trigger) {
					return false;
				}
				var root = trigger.closest ? trigger.closest('.um-media-library-tag-gallery') : null;
				var items = getItems(root, trigger);
				var rawIndex = trigger.getAttribute('data-um-modal-index') || trigger.getAttribute('data-um-lightbox-index') || '';
				var index = parseInt(String(rawIndex || ''), 10);
				if (isNaN(index) || index < 0 || index >= items.length) {
					index = items.indexOf(trigger);
				}
				if (index < 0) {
					index = 0;
				}
				var shouldAnimate = overlay.getAttribute('aria-hidden') === 'false';
				return renderOverlay(overlay, trigger, root, items, index, shouldAnimate);
			};
			api.closeFromOverlay = function(overlay, event) {
				if (event) {
					event.preventDefault();
					if (typeof event.stopImmediatePropagation === 'function') {
						event.stopImmediatePropagation();
					} else if (typeof event.stopPropagation === 'function') {
						event.stopPropagation();
					}
				}
				if (!overlay) {
					return false;
				}
				if (tracker && typeof tracker.reset === 'function') {
					var inlineRootId = String(overlay.getAttribute('data-um-inline-root-id') || '');
					if (inlineRootId) {
						tracker.reset(inlineRootId + '-lightbox');
					}
				}
				overlay.style.display = 'none';
				overlay.setAttribute('aria-hidden', 'true');
				overlay.classList.remove('um-mltg-transition-crossfade', 'um-mltg-transition-slide-left');
				var image = overlay.querySelector('img');
				if (image) {
					image.setAttribute('src', '');
					image.classList.remove('is-transitioning');
				}
				var slideshowBtn = overlay.querySelector('.um-mltg-lightbox-slideshow-toggle');
				if (slideshowBtn) {
					slideshowBtn.textContent = 'Play Slideshow';
				}
				if (overlay.__umInlineSlideshowTimer) {
					window.clearInterval(overlay.__umInlineSlideshowTimer);
					overlay.__umInlineSlideshowTimer = null;
				}
				overlay.__umInlineSlideshowPlaying = false;
				if (document && document.body) {
					document.body.style.overflow = '';
				}
				overlay.setAttribute('data-um-inline-attachment-id', '0');
				updateAttachmentIdInUrl(0);
				return false;
			};
			api.closeFromButton = function(button, event) {
				var overlay = button && button.closest ? button.closest('.um-mltg-lightbox-overlay') : null;
				return api.closeFromOverlay(overlay, event);
			};
			api.backdrop = function(overlay, event) {
				if (!overlay || !event) {
					return false;
				}
				if (event.target === overlay) {
					return api.closeFromOverlay(overlay, event);
				}
				return true;
			};
			api.step = function(button, direction, event) {
				if (event) {
					event.preventDefault();
					if (typeof event.stopImmediatePropagation === 'function') {
						event.stopImmediatePropagation();
					} else if (typeof event.stopPropagation === 'function') {
						event.stopPropagation();
					}
				}
				var overlay = button && button.closest ? button.closest('.um-mltg-lightbox-overlay') : null;
				if (!overlay) {
					return false;
				}
				var allowPrevNext = overlay.getAttribute('data-um-inline-prevnext') === '1';
				var allowSwipe = overlay.getAttribute('data-um-inline-swipe') === '1';
				if (!allowPrevNext && !allowSwipe) {
					return false;
				}
				var rootId = overlay.getAttribute('data-um-inline-root-id') || '';
				var root = rootId ? document.getElementById(rootId) : null;
				if (!root) {
					return false;
				}
				var items = getItems(root, null);
				if (!items.length) {
					return false;
				}
				var currentIndex = parseInt(String(overlay.getAttribute('data-um-inline-index') || '0'), 10);
				if (isNaN(currentIndex) || currentIndex < 0 || currentIndex >= items.length) {
					currentIndex = 0;
				}
				var nextIndex = direction < 0 ? (currentIndex - 1 + items.length) % items.length : (currentIndex + 1) % items.length;
				var trigger = items[nextIndex];
				if (!trigger) {
					return false;
				}
				var shouldAnimate = overlay.getAttribute('aria-hidden') === 'false';
				return renderOverlay(overlay, trigger, root, items, nextIndex, shouldAnimate);
			};
			api.toggleSlideshow = function(button, event) {
				if (event) {
					event.preventDefault();
					if (typeof event.stopImmediatePropagation === 'function') {
						event.stopImmediatePropagation();
					} else if (typeof event.stopPropagation === 'function') {
						event.stopPropagation();
					}
				}
				var overlay = button && button.closest ? button.closest('.um-mltg-lightbox-overlay') : null;
				if (!overlay || overlay.getAttribute('data-um-inline-slideshow') !== '1') {
					return false;
				}
				if (overlay.__umInlineSlideshowTimer) {
					window.clearInterval(overlay.__umInlineSlideshowTimer);
					overlay.__umInlineSlideshowTimer = null;
					overlay.__umInlineSlideshowPlaying = false;
					if (button) {
						button.textContent = 'Play Slideshow';
					}
					return false;
				}
				var secondsRaw = parseFloat(String(overlay.getAttribute('data-um-inline-slideshow-seconds') || '3'));
				var intervalMs = Math.max(1000, Math.round((isFinite(secondsRaw) ? secondsRaw : 3) * 1000));
				overlay.__umInlineSlideshowPlaying = true;
				if (button) {
					button.textContent = 'Pause Slideshow';
				}
				overlay.__umInlineSlideshowTimer = window.setInterval(function() {
					if (overlay.getAttribute('aria-hidden') !== 'false' || overlay.getAttribute('data-um-inline-slideshow') !== '1') {
						if (overlay.__umInlineSlideshowTimer) {
							window.clearInterval(overlay.__umInlineSlideshowTimer);
							overlay.__umInlineSlideshowTimer = null;
						}
						overlay.__umInlineSlideshowPlaying = false;
						if (button) {
							button.textContent = 'Play Slideshow';
						}
						return;
					}
					var nextBtn = overlay.querySelector('.um-mltg-lightbox-next');
					if (nextBtn) {
						api.step(nextBtn, 1, null);
					}
				}, intervalMs);
				return false;
			};
			api.keydown = function(overlay, event) {
				if (!overlay || !event) {
					return true;
				}
				if (event.key === 'Escape' && overlay.getAttribute('aria-hidden') === 'false') {
					return api.closeFromOverlay(overlay, event);
				}
				if (overlay.getAttribute('aria-hidden') !== 'false') {
					return true;
				}
				var allowPrevNext = overlay.getAttribute('data-um-inline-prevnext') === '1';
				if (allowPrevNext && event.key === 'ArrowLeft') {
					var prevBtn = overlay.querySelector('.um-mltg-lightbox-prev');
					if (prevBtn) {
						return api.step(prevBtn, -1, event);
					}
				} else if (allowPrevNext && event.key === 'ArrowRight') {
					var nextBtn = overlay.querySelector('.um-mltg-lightbox-next');
					if (nextBtn) {
						return api.step(nextBtn, 1, event);
					}
				} else if (event.key === ' ') {
					var allowSlideshow = overlay.getAttribute('data-um-inline-slideshow') === '1';
					if (allowSlideshow) {
						var slideshowBtn = overlay.querySelector('.um-mltg-lightbox-slideshow-toggle');
						if (slideshowBtn) {
							return api.toggleSlideshow(slideshowBtn, event);
						}
					}
				}
				return true;
			};
			window.umMltgInline = api;
			function findDeepLinkTriggerByAttachmentId(attachmentId) {
				if (!attachmentId || !document || !document.querySelector) {
					return null;
				}
				var idString = String(attachmentId);
				var selectorModal = '[data-um-modal-attachment-id="' + idString + '"]';
				var selectorLegacy = '[data-um-lightbox-attachment-id="' + idString + '"]';
				var scopedSelector = '.um-media-library-tag-gallery ' + selectorModal + ', .um-media-library-tag-gallery ' + selectorLegacy;
				var trigger = document.querySelector(scopedSelector);
				if (trigger) {
					return trigger;
				}
				return document.querySelector(selectorModal + ', ' + selectorLegacy);
			}
			function tryOpenDeepLinkTrigger(trigger) {
				if (!trigger) {
					return false;
				}
				var root = trigger.closest ? trigger.closest('.um-media-library-tag-gallery') : null;
				if (root && root.id) {
					var overlay = document.getElementById(root.id + '-lightbox');
					if (overlay && window.umMltgInline && typeof window.umMltgInline.open === 'function') {
						return !!window.umMltgInline.open(trigger, overlay.id, null);
					}
				}
				try {
					if (typeof trigger.click === 'function') {
						trigger.click();
						return true;
					}
				} catch (err) {
				}
				return false;
			}
			function scheduleDeepLinkAutoOpenFallback() {
				var attachmentIdFromUrl = readAttachmentIdFromUrl();
				if (!attachmentIdFromUrl || window.__umMltgDeepLinkConsumed === attachmentIdFromUrl) {
					return;
				}
				var attempts = 0;
				var maxAttempts = 40;
				var step = function() {
					attempts += 1;
					if (window.__umMltgDeepLinkConsumed === attachmentIdFromUrl) {
						return true;
					}
					var trigger = findDeepLinkTriggerByAttachmentId(attachmentIdFromUrl);
					if (!trigger) {
						return attempts >= maxAttempts;
					}
					if (tryOpenDeepLinkTrigger(trigger)) {
						window.__umMltgDeepLinkConsumed = attachmentIdFromUrl;
						return true;
					}
					return attempts >= maxAttempts;
				};
				if (step()) {
					return;
				}
				var retryTimer = window.setInterval(function() {
					if (step()) {
						window.clearInterval(retryTimer);
					}
				}, 120);
			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', scheduleDeepLinkAutoOpenFallback);
			} else {
				scheduleDeepLinkAutoOpenFallback();
			}
			window.addEventListener('load', scheduleDeepLinkAutoOpenFallback);
		})();
		</script>
		<?php
	}

	/**
	 * Render callback for Media Library Tag Gallery block.
	 *
	 * @param array<string,mixed> $attrs Block attributes.
	 */
	public static function render_media_library_tags_gallery_block(array $attrs = []): string {
		// Front-end fallback lightbox runtime removed:
		// a single source of truth (the per-gallery runtime below) avoids
		// control-state conflicts for prev/next/slideshow behavior.

		$settings = User_Manager_Core::get_settings();
		$defaults = self::get_media_library_tag_gallery_defaults($settings);
		$forced_url_tag_expression = isset($attrs['forceUrlTagExpression'])
			? trim((string) wp_unslash((string) $attrs['forceUrlTagExpression']))
			: '';

		$tag_slug = isset($attrs['tagSlug']) ? sanitize_title((string) $attrs['tagSlug']) : '';
		$allow_url_tag_override = !empty($attrs['allowUrlTagOverride']);
		$allow_any_url_param_tag_identifier = !empty($attrs['allowAnyUrlParamTagIdentifier']);
		$require_tag_value = !empty($attrs['requireTagValue']);
		$use_default_columns_desktop = !empty($attrs['useDefaultColumnsDesktop']);
		$use_default_columns_desktop_lt_50 = !empty($attrs['useDefaultColumnsDesktopLt50']);
		$use_default_columns_desktop_lt_25 = !empty($attrs['useDefaultColumnsDesktopLt25']);
		$use_default_columns_desktop_lt_10 = !empty($attrs['useDefaultColumnsDesktopLt10']);
		$use_default_columns_mobile = !empty($attrs['useDefaultColumnsMobile']);
		$use_default_sort_order = !empty($attrs['useDefaultSortOrder']);
		$use_default_file_size = !empty($attrs['useDefaultFileSize']);
		$use_default_style = !empty($attrs['useDefaultStyle']);
		$use_default_page_limit = !empty($attrs['useDefaultPageLimit']);
		$use_default_link_to = !empty($attrs['useDefaultLinkTo']);
		$use_default_album_description_position = !empty($attrs['useDefaultAlbumDescriptionPosition']);
		$use_default_description_display = !empty($attrs['useDefaultDescriptionDisplay']);
		$use_default_description_value = !empty($attrs['useDefaultDescriptionValue']);
		$use_default_lightbox_prev_next_keyboard = !empty($attrs['useDefaultLightboxPrevNextKeyboard']);
		$use_default_lightbox_swipe_navigation = !empty($attrs['useDefaultLightboxSwipeNavigation']);
		$use_default_lightbox_tap_side_navigation = !empty($attrs['useDefaultLightboxTapSideNavigation']);
		$use_default_lightbox_slideshow_button = !empty($attrs['useDefaultLightboxSlideshowButton']);
		$use_default_lightbox_slideshow_seconds = !empty($attrs['useDefaultLightboxSlideshowSeconds']);
		$use_default_lightbox_slideshow_transition = !empty($attrs['useDefaultLightboxSlideshowTransition']);
		$use_default_lightbox_modal_background_color = !empty($attrs['useDefaultLightboxModalBackgroundColor']);
		$use_default_lightbox_modal_text_color = !empty($attrs['useDefaultLightboxModalTextColor']);
		$use_default_simple_lightbox_thumbnail_click = !empty($attrs['useDefaultSimpleLightboxThumbnailClick']);
		$columns_desktop = $use_default_columns_desktop
			? max(1, min(8, absint($defaults['columnsDesktop'])))
			: max(1, min(8, absint($attrs['columnsDesktop'] ?? $defaults['columnsDesktop'])));
		$columns_desktop_lt_50 = $use_default_columns_desktop_lt_50
			? max(1, min(8, absint($defaults['columnsDesktopLt50'] ?? $defaults['columnsDesktop'])))
			: max(1, min(8, absint($attrs['columnsDesktopLt50'] ?? ($defaults['columnsDesktopLt50'] ?? $defaults['columnsDesktop']))));
		$columns_desktop_lt_25 = $use_default_columns_desktop_lt_25
			? max(1, min(8, absint($defaults['columnsDesktopLt25'] ?? $defaults['columnsDesktop'])))
			: max(1, min(8, absint($attrs['columnsDesktopLt25'] ?? ($defaults['columnsDesktopLt25'] ?? $defaults['columnsDesktop']))));
		$columns_desktop_lt_10 = $use_default_columns_desktop_lt_10
			? max(1, min(8, absint($defaults['columnsDesktopLt10'] ?? $defaults['columnsDesktop'])))
			: max(1, min(8, absint($attrs['columnsDesktopLt10'] ?? ($defaults['columnsDesktopLt10'] ?? $defaults['columnsDesktop']))));
		$columns_mobile = $use_default_columns_mobile
			? max(1, min(4, absint($defaults['columnsMobile'])))
			: max(1, min(4, absint($attrs['columnsMobile'] ?? $defaults['columnsMobile'])));
		$sort_order = $use_default_sort_order
			? sanitize_key((string) $defaults['sortOrder'])
			: (isset($attrs['sortOrder']) ? sanitize_key((string) $attrs['sortOrder']) : (string) $defaults['sortOrder']);
		$accent_color = sanitize_hex_color((string) ($defaults['accentColor'] ?? '#ffffff'));
		if (!is_string($accent_color) || $accent_color === '') {
			$accent_color = '#ffffff';
		}
		$file_size = $use_default_file_size
			? sanitize_key((string) $defaults['fileSize'])
			: (isset($attrs['fileSize']) ? sanitize_key((string) $attrs['fileSize']) : (string) $defaults['fileSize']);
		$style = $use_default_style
			? sanitize_key((string) $defaults['style'])
			: (isset($attrs['style']) ? sanitize_key((string) $attrs['style']) : (string) $defaults['style']);
		$page_limit = $use_default_page_limit
			? max(0, absint($defaults['pageLimit']))
			: max(0, absint($attrs['pageLimit'] ?? $defaults['pageLimit']));
		$link_to_raw = $use_default_link_to
			? (string) ($defaults['linkTo'] ?? 'lightbox')
			: (isset($attrs['linkTo']) ? (string) $attrs['linkTo'] : (string) ($defaults['linkTo'] ?? 'lightbox'));
		$link_to = sanitize_key($link_to_raw);
		if (in_array($link_to, ['media_permalink', 'media_file', 'file', 'open_image'], true)) {
			$link_to = 'image';
		} elseif (in_array($link_to, ['new_window', 'open_image_new_window'], true)) {
			$link_to = 'image_new_window';
		}
		$album_description_position = $use_default_album_description_position
			? sanitize_key((string) ($defaults['albumDescriptionPosition'] ?? 'none'))
			: (isset($attrs['albumDescriptionPosition']) ? sanitize_key((string) $attrs['albumDescriptionPosition']) : sanitize_key((string) ($defaults['albumDescriptionPosition'] ?? 'none')));
		$description_display = $use_default_description_display
			? sanitize_key((string) $defaults['descriptionDisplay'])
			: (isset($attrs['descriptionDisplay']) ? sanitize_key((string) $attrs['descriptionDisplay']) : (string) $defaults['descriptionDisplay']);
		$description_value = $use_default_description_value
			? sanitize_key((string) $defaults['descriptionValue'])
			: (isset($attrs['descriptionValue']) ? sanitize_key((string) $attrs['descriptionValue']) : (string) $defaults['descriptionValue']);
		$lightbox_prev_next_keyboard = $use_default_lightbox_prev_next_keyboard
			? !empty($defaults['lightboxPrevNextKeyboard'])
			: !empty($attrs['lightboxPrevNextKeyboard']);
		$lightbox_swipe_navigation = $use_default_lightbox_swipe_navigation
			? !empty($defaults['lightboxSwipeNavigation'])
			: !empty($attrs['lightboxSwipeNavigation']);
		$lightbox_tap_side_navigation = $use_default_lightbox_tap_side_navigation
			? !empty($defaults['lightboxTapSideNavigation'])
			: !empty($attrs['lightboxTapSideNavigation']);
		$lightbox_slideshow_button = $use_default_lightbox_slideshow_button
			? !empty($defaults['lightboxSlideshowButton'])
			: !empty($attrs['lightboxSlideshowButton']);
		$lightbox_slideshow_seconds_raw = $use_default_lightbox_slideshow_seconds
			? (isset($defaults['lightboxSlideshowSeconds']) ? (float) $defaults['lightboxSlideshowSeconds'] : 3.0)
			: (isset($attrs['lightboxSlideshowSeconds']) ? (float) $attrs['lightboxSlideshowSeconds'] : (isset($defaults['lightboxSlideshowSeconds']) ? (float) $defaults['lightboxSlideshowSeconds'] : 3.0));
		$lightbox_slideshow_seconds = max(1.0, min(60.0, $lightbox_slideshow_seconds_raw));
		$lightbox_slideshow_transition = $use_default_lightbox_slideshow_transition
			? sanitize_key((string) ($defaults['lightboxSlideshowTransition'] ?? 'none'))
			: (isset($attrs['lightboxSlideshowTransition']) ? sanitize_key((string) $attrs['lightboxSlideshowTransition']) : sanitize_key((string) ($defaults['lightboxSlideshowTransition'] ?? 'none')));
		$lightbox_modal_background_color = $use_default_lightbox_modal_background_color
			? sanitize_hex_color((string) ($defaults['lightboxModalBackgroundColor'] ?? '#000000'))
			: sanitize_hex_color((string) ($attrs['lightboxModalBackgroundColor'] ?? ($defaults['lightboxModalBackgroundColor'] ?? '#000000')));
		$lightbox_modal_text_color = $use_default_lightbox_modal_text_color
			? sanitize_hex_color((string) ($defaults['lightboxModalTextColor'] ?? '#ffffff'))
			: sanitize_hex_color((string) ($attrs['lightboxModalTextColor'] ?? ($defaults['lightboxModalTextColor'] ?? '#ffffff')));
		$simple_lightbox_thumbnail_click = $use_default_simple_lightbox_thumbnail_click
			? !empty($defaults['simpleLightboxThumbnailClick'])
			: !empty($attrs['simpleLightboxThumbnailClick']);
		if (!is_string($lightbox_modal_background_color) || $lightbox_modal_background_color === '') {
			$lightbox_modal_background_color = '#000000';
		}
		if (!is_string($lightbox_modal_text_color) || $lightbox_modal_text_color === '') {
			$lightbox_modal_text_color = '#ffffff';
		}
		$allowed_sort_orders = ['date_asc', 'date_desc', 'id_asc', 'id_desc', 'filename_asc', 'filename_desc', 'caption_asc', 'caption_desc', 'random'];
		$allowed_file_sizes = array_keys(self::get_available_image_sizes_for_media_gallery());
		if (empty($allowed_file_sizes)) {
			$allowed_file_sizes = ['thumbnail', 'medium', 'large', 'full'];
		}
		$allowed_styles = array_keys(self::get_media_library_gallery_style_options());
		$allowed_link_to = array_keys(self::get_media_library_gallery_link_to_options());
		$allowed_album_description_positions = array_keys(self::get_media_library_gallery_album_description_position_options());
		$allowed_description_display = array_keys(self::get_media_library_gallery_description_display_options());
		$allowed_description_values = array_keys(self::get_media_library_gallery_description_value_options());
		$allowed_lightbox_slideshow_transitions = ['none', 'crossfade', 'slide_left'];
		if (!in_array($sort_order, $allowed_sort_orders, true)) {
			$sort_order = 'date_desc';
		}
		if (!in_array($file_size, $allowed_file_sizes, true)) {
			$file_size = 'thumbnail';
		}
		if (!in_array($style, $allowed_styles, true)) {
			$style = 'uniform_grid';
		}
		if (!in_array($link_to, $allowed_link_to, true)) {
			$link_to = 'lightbox';
		}
		if (!in_array($album_description_position, $allowed_album_description_positions, true)) {
			$album_description_position = 'none';
		}
		if (!in_array($description_display, $allowed_description_display, true)) {
			$description_display = 'none';
		}
		if (!in_array($description_value, $allowed_description_values, true)) {
			$description_value = 'caption';
		}
		if (!in_array($lightbox_slideshow_transition, $allowed_lightbox_slideshow_transitions, true)) {
			$lightbox_slideshow_transition = 'none';
		}

		$pipe_tag_groups = [];
		if ($forced_url_tag_expression === '' && $allow_url_tag_override) {
			$pipe_tag_groups = self::resolve_media_library_gallery_pipe_tag_override_groups($allow_any_url_param_tag_identifier);
		}
		if (!empty($pipe_tag_groups)) {
			$pipe_sections = [];
			foreach ($pipe_tag_groups as $pipe_group) {
				$pipe_expression = isset($pipe_group['expression']) ? trim((string) $pipe_group['expression']) : '';
				if ($pipe_expression === '') {
					continue;
				}

				$pipe_section_attrs = $attrs;
				$pipe_section_attrs['forceUrlTagExpression'] = $pipe_expression;
				$pipe_section_attrs['allowUrlTagOverride'] = false;
				$pipe_section_attrs['useDefaultAlbumDescriptionPosition'] = false;
				$pipe_section_attrs['albumDescriptionPosition'] = 'none';
				$pipe_section_html = self::render_media_library_tags_gallery_block($pipe_section_attrs);
				if ($pipe_section_html === '') {
					continue;
				}

				$pipe_title = isset($pipe_group['title']) ? trim((string) $pipe_group['title']) : '';
				$pipe_description = isset($pipe_group['description']) ? trim((string) $pipe_group['description']) : '';
				$pipe_header_html = '';
				if ($pipe_title !== '') {
					$pipe_header_html .= '<h2 class="um-media-library-tag-pipe-title" style="text-align:center;margin:0 0 8px;">' . esc_html($pipe_title) . '</h2>';
				}
				if ($pipe_description !== '') {
					$pipe_header_html .= '<p class="um-media-library-tag-pipe-description" style="text-align:center;margin:0 0 14px;">' . esc_html($pipe_description) . '</p>';
				}

				$pipe_sections[] = '<div class="um-media-library-tag-pipe-section" style="margin-bottom:50px;">' . $pipe_header_html . $pipe_section_html . '</div>';
			}

			return implode('', $pipe_sections);
		}

		$url_tag_override = [
			'mode' => 'none',
			'slugs' => [],
		];
		if ($forced_url_tag_expression !== '') {
			$url_tag_override = self::parse_media_library_gallery_tag_expression($forced_url_tag_expression);
			if ($url_tag_override['mode'] === 'all') {
				$tag_slug = '';
			} elseif (!empty($url_tag_override['slugs'])) {
				$tag_slug = (string) $url_tag_override['slugs'][0];
			}
		} elseif ($allow_url_tag_override) {
			$url_tag_override = self::resolve_media_library_gallery_url_tag_override($allow_any_url_param_tag_identifier);
			if ($url_tag_override['mode'] === 'all') {
				$tag_slug = '';
			} elseif (!empty($url_tag_override['slugs'])) {
				$tag_slug = (string) $url_tag_override['slugs'][0];
			}
		}
		$tag_description_data = self::get_media_library_tag_description_data_for_tag_expression($url_tag_override);
		$album_tag_description_html = '';
		$album_tag_youtube_videos_html = self::render_media_library_tag_youtube_videos_html($url_tag_override, $tag_slug);
		$has_effective_tag_value = ($tag_slug !== '' || !empty($url_tag_override['slugs']));
		if ($require_tag_value && !$has_effective_tag_value) {
			return '';
		}

		$effective_link_to = $link_to;
		$allow_lightbox_click_open = $effective_link_to === 'lightbox';
		if ($style === 'infinite_scroll') {
			$page_limit = 0;
		}
		$show_description_under_photo = in_array($description_display, ['grid', 'both'], true);
		$show_description_in_lightbox = in_array($description_display, ['lightbox', 'both'], true);
		$hidden_frontend_tag_slugs = self::get_hidden_frontend_tag_slugs($settings);
		$hide_featured_image_duplicate_in_tagged_images = !empty($defaults['hideFeaturedImageDuplicateInTaggedImages']);
		$featured_image_max_width_px = isset($defaults['featuredImageMaxWidthPx'])
			? max(0, min(1600, absint((int) $defaults['featuredImageMaxWidthPx'])))
			: 360;
		$featured_description_attachment_id = 0;
		$last_tag_index_for_description = isset($tag_description_data['lastIndex']) ? (int) $tag_description_data['lastIndex'] : -1;
		if (
			$last_tag_index_for_description >= 0
			&& !empty($tag_description_data['featuredImageIds'])
			&& is_array($tag_description_data['featuredImageIds'])
			&& isset($tag_description_data['featuredImageIds'][$last_tag_index_for_description])
		) {
			$featured_description_attachment_id = absint((int) $tag_description_data['featuredImageIds'][$last_tag_index_for_description]);
		}
		if ($featured_description_attachment_id <= 0) {
			$featured_description_attachment_id = isset($tag_description_data['featuredImageId']) ? absint($tag_description_data['featuredImageId']) : 0;
		}
		if ($featured_description_attachment_id <= 0 && !empty($tag_description_data['featuredImageIds']) && is_array($tag_description_data['featuredImageIds'])) {
			$featured_description_attachment_id = absint((int) ($tag_description_data['featuredImageIds'][0] ?? 0));
		}
		$css_crop_styles = ['mosaic_grid', 'mosaic_grid_taller', 'square_crop', 'wide_rectangle_crop', 'tall_rectangle_crop', 'circle_crop', 'fullscreen_lightbox_grid'];

		$page_num = isset($_GET['um_media_gallery_page']) ? max(1, absint(wp_unslash($_GET['um_media_gallery_page']))) : 1;
		$offset = 0;
		if ($page_limit > 0) {
			$offset = ($page_num - 1) * $page_limit;
		}

		$query_args = [
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => $page_limit > 0 ? $page_limit : -1,
			'nopaging' => $page_limit <= 0,
			'offset' => $offset,
			'no_found_rows' => $page_limit <= 0,
		];

		$tax_query = [];
		$active_view_tag_slugs = [];
		if (!empty($url_tag_override['slugs']) && in_array($url_tag_override['mode'], ['and', 'or', 'single'], true)) {
			$tag_override_tax_query = self::build_media_library_gallery_multi_tag_tax_query(
				array_values(array_map('strval', $url_tag_override['slugs'])),
				(string) $url_tag_override['mode']
			);
			if (!empty($tag_override_tax_query)) {
				foreach ($tag_override_tax_query as $tag_clause) {
					$tax_query[] = $tag_clause;
				}
			}
			$active_view_tag_slugs = array_values(array_unique(array_filter(array_map(
				'sanitize_title',
				array_map('strval', $url_tag_override['slugs'])
			))));
		} elseif ($tag_slug !== '' && self::is_valid_media_library_tag_filter_slug($tag_slug)) {
			$filter_slugs = self::get_media_library_filter_slugs_for_requested_slug($tag_slug);
			if (empty($filter_slugs)) {
				$filter_slugs = [$tag_slug];
			}
			$tax_query[] = [
				'taxonomy' => self::media_library_tags_taxonomy(),
				'field' => 'slug',
				'terms' => $filter_slugs,
			];
			$active_view_tag_slugs = array_values(array_unique(array_filter(array_map('sanitize_title', $filter_slugs))));
		}
		if (!empty($hidden_frontend_tag_slugs)) {
			$tax_query[] = [
				'taxonomy' => self::media_library_tags_taxonomy(),
				'field' => 'slug',
				'terms' => $hidden_frontend_tag_slugs,
				'operator' => 'NOT IN',
			];
		}
		if (!empty($tax_query)) {
			if (count($tax_query) > 1) {
				$tax_query['relation'] = 'AND';
			}
			$query_args['tax_query'] = $tax_query;
		}
		if (!empty($active_view_tag_slugs)) {
			self::increment_media_library_album_tag_view_counts_for_slugs($active_view_tag_slugs);
		}

		switch ($sort_order) {
			case 'date_asc':
				$query_args['orderby'] = 'date';
				$query_args['order'] = 'ASC';
				break;
			case 'date_desc':
				$query_args['orderby'] = 'date';
				$query_args['order'] = 'DESC';
				break;
			case 'id_asc':
				$query_args['orderby'] = 'ID';
				$query_args['order'] = 'ASC';
				break;
			case 'id_desc':
				$query_args['orderby'] = 'ID';
				$query_args['order'] = 'DESC';
				break;
			case 'filename_asc':
				$query_args['meta_key'] = '_wp_attached_file';
				$query_args['orderby'] = 'meta_value';
				$query_args['order'] = 'ASC';
				break;
			case 'filename_desc':
				$query_args['meta_key'] = '_wp_attached_file';
				$query_args['orderby'] = 'meta_value';
				$query_args['order'] = 'DESC';
				break;
			case 'caption_asc':
				$query_args['orderby'] = 'post_excerpt';
				$query_args['order'] = 'ASC';
				break;
			case 'caption_desc':
				$query_args['orderby'] = 'post_excerpt';
				$query_args['order'] = 'DESC';
				break;
			case 'random':
				$query_args['orderby'] = 'rand';
				break;
		}

		$query = new WP_Query($query_args);
		$attachments = $query->posts;
		if (!is_array($attachments)) {
			$attachments = [];
		}
		$featured_image_exists_in_tagged_images = false;
		foreach ($attachments as $attachment_candidate) {
			if (!($attachment_candidate instanceof WP_Post)) {
				continue;
			}
			if ((int) $attachment_candidate->ID === $featured_description_attachment_id) {
				$featured_image_exists_in_tagged_images = true;
				break;
			}
		}
		if ($hide_featured_image_duplicate_in_tagged_images && $featured_description_attachment_id > 0) {
			$attachments = array_values(array_filter($attachments, static function ($attachment) use ($featured_description_attachment_id): bool {
				return !($attachment instanceof WP_Post) || (int) $attachment->ID !== $featured_description_attachment_id;
			}));
		}
		$attachment_count = count($attachments);
		$effective_columns_desktop = $columns_desktop;
		if ($attachment_count < 10) {
			$effective_columns_desktop = $columns_desktop_lt_10;
		} elseif ($attachment_count < 25) {
			$effective_columns_desktop = $columns_desktop_lt_25;
		} elseif ($attachment_count < 50) {
			$effective_columns_desktop = $columns_desktop_lt_50;
		}
		$disable_css_crop_under_total = max(0, absint($defaults['disableCssCropUnderTotal'] ?? 0));
		$disable_css_crop_for_small_galleries = $disable_css_crop_under_total > 0 && $attachment_count < $disable_css_crop_under_total;

		$uid = function_exists('wp_unique_id') ? wp_unique_id('um-media-gallery-') : uniqid('um-media-gallery-');
		$style_class = 'um-media-gallery-style-' . $style;
		if ($disable_css_crop_for_small_galleries && in_array($style, $css_crop_styles, true)) {
			$style = 'standard';
			$style_class = 'um-media-gallery-style-standard';
		}
		$lightbox_modal_background_color_css = sanitize_hex_color((string) $lightbox_modal_background_color);
		if (!is_string($lightbox_modal_background_color_css) || $lightbox_modal_background_color_css === '') {
			$lightbox_modal_background_color_css = '#000000';
		}
		$lightbox_modal_text_color_css = sanitize_hex_color((string) $lightbox_modal_text_color);
		if (!is_string($lightbox_modal_text_color_css) || $lightbox_modal_text_color_css === '') {
			$lightbox_modal_text_color_css = '#ffffff';
		}
		$inline_lightbox_overlay_id = $uid . '-lightbox';
		$lightbox_view_track_url = admin_url('admin-ajax.php');
		$lightbox_view_track_nonce = wp_create_nonce('um_media_library_lightbox_view');
		$inline_lightbox_open_onclick = "if(window.umMltgInline&&window.umMltgInline.open){return !!window.umMltgInline.open(this," . wp_json_encode((string) $inline_lightbox_overlay_id) . ",event);}event.preventDefault();if(typeof event.stopImmediatePropagation==='function'){event.stopImmediatePropagation();}else if(typeof event.stopPropagation==='function'){event.stopPropagation();}return false;";
		$inline_lightbox_close_onclick = "if(window.umMltgInline&&window.umMltgInline.closeFromButton){return !!window.umMltgInline.closeFromButton(this,event);}return false;";
		$inline_lightbox_overlay_onclick = "if(window.umMltgInline&&window.umMltgInline.backdrop){return !!window.umMltgInline.backdrop(this,event);}return true;";
		$inline_lightbox_overlay_onkeydown = "if(window.umMltgInline&&window.umMltgInline.keydown){return !!window.umMltgInline.keydown(this,event);}return true;";
		$inline_lightbox_prev_onclick = "if(window.umMltgInline&&window.umMltgInline.step){return !!window.umMltgInline.step(this,-1,event);}event.preventDefault();return false;";
		$inline_lightbox_next_onclick = "if(window.umMltgInline&&window.umMltgInline.step){return !!window.umMltgInline.step(this,1,event);}event.preventDefault();return false;";
		$inline_lightbox_slideshow_onclick = "if(window.umMltgInline&&window.umMltgInline.toggleSlideshow){return !!window.umMltgInline.toggleSlideshow(this,event);}event.preventDefault();return false;";
		$total_pages = ($page_limit > 0 && isset($query->max_num_pages)) ? max(1, (int) $query->max_num_pages) : 1;
		$show_lightbox_admin_edit_link = current_user_can('manage_options');
		// Featured image should remain a lightbox trigger whenever Link To is
		// lightbox, even if duplicate featured-image tiles are hidden from the
		// main gallery grid.
		$show_featured_description_image_in_lightbox_collection = $allow_lightbox_click_open
			&& $featured_description_attachment_id > 0;
		$featured_lightbox_index_offset = $show_featured_description_image_in_lightbox_collection ? 1 : 0;
		$use_separate_featured_image_column = !empty($defaults['featuredImageSeparateColumn']);
		$hide_featured_image_if_no_description_or_bullets = !empty($defaults['hideFeaturedImageIfNoDescriptionOrBullets']);
		if ($album_description_position !== 'none') {
			$album_tag_description_html = self::render_media_library_tag_description_paragraphs_html(
				$tag_description_data,
				[
					'linkTo' => $effective_link_to,
					'descriptionValue' => $description_value,
					'showDescriptionInLightbox' => $show_description_in_lightbox,
					'showLightboxAdminEditLink' => $show_lightbox_admin_edit_link,
					'inlineLightboxOpenOnclick' => $inline_lightbox_open_onclick,
					'featuredLightboxEnabled' => $show_featured_description_image_in_lightbox_collection,
					'featuredLightboxIndex' => $show_featured_description_image_in_lightbox_collection ? 0 : -1,
					'useSeparateFeaturedImageColumn' => $use_separate_featured_image_column,
					'hideFeaturedImageIfNoDescriptionOrBullets' => $hide_featured_image_if_no_description_or_bullets,
				]
			);
			$group_links_html = self::render_media_library_tag_group_links_html_for_expression($url_tag_override);
			if ($group_links_html !== '') {
				$album_tag_description_html = $group_links_html . $album_tag_description_html;
			}
		}
		$lightbox_tag_ajax_url = $show_lightbox_admin_edit_link ? admin_url('admin-ajax.php') : '';
		$lightbox_tag_nonce = $show_lightbox_admin_edit_link ? wp_create_nonce('um_media_library_tags_ajax') : '';
		$lightbox_debug_enabled = false;
		if (isset($_GET['um_mltg_debug'])) {
			$raw_debug_flag = strtolower(trim((string) sanitize_text_field((string) wp_unslash($_GET['um_mltg_debug']))));
			$lightbox_debug_enabled = in_array($raw_debug_flag, ['1', 'true', 'yes', 'on'], true);
		}
		$lightbox_debug_auto_open = false;
		if (isset($_GET['um_mltg_debug_open'])) {
			$raw_debug_open_flag = strtolower(trim((string) sanitize_text_field((string) wp_unslash($_GET['um_mltg_debug_open']))));
			$lightbox_debug_auto_open = in_array($raw_debug_open_flag, ['1', 'true', 'yes', 'on'], true);
		}
		if ($lightbox_debug_auto_open) {
			$lightbox_debug_enabled = true;
		}
		$enable_simple_lightbox_for_this_gallery = $allow_lightbox_click_open && $simple_lightbox_thumbnail_click;
		$timeline_date_format = get_option('date_format');
		if (!is_string($timeline_date_format) || $timeline_date_format === '') {
			$timeline_date_format = 'F j, Y';
		}
		ob_start();
		?>
		<div
			id="<?php echo esc_attr($uid); ?>"
			class="um-media-library-tag-gallery <?php echo esc_attr($style_class); ?>"
			style="--um-mltg-accent-color:<?php echo esc_attr($accent_color); ?>;--um-mltg-lightbox-modal-bg:<?php echo esc_attr($lightbox_modal_background_color_css); ?>;--um-mltg-lightbox-modal-text:<?php echo esc_attr($lightbox_modal_text_color_css); ?>;"
			<?php echo $lightbox_prev_next_keyboard ? ' data-um-lightbox-prev-next="1"' : ' data-um-lightbox-prev-next="0"'; ?>
			<?php echo $lightbox_swipe_navigation ? ' data-um-lightbox-swipe="1"' : ' data-um-lightbox-swipe="0"'; ?>
			<?php echo $lightbox_tap_side_navigation ? ' data-um-lightbox-tap-nav="1"' : ' data-um-lightbox-tap-nav="0"'; ?>
			<?php echo $lightbox_slideshow_button ? ' data-um-lightbox-slideshow="1"' : ' data-um-lightbox-slideshow="0"'; ?>
			data-um-lightbox-seconds="<?php echo esc_attr((string) $lightbox_slideshow_seconds); ?>"
			data-um-lightbox-transition="<?php echo esc_attr((string) $lightbox_slideshow_transition); ?>"
			<?php echo $lightbox_debug_enabled ? ' data-um-lightbox-debug="1"' : ' data-um-lightbox-debug="0"'; ?>
			<?php echo $lightbox_debug_auto_open ? ' data-um-lightbox-debug-open="1"' : ' data-um-lightbox-debug-open="0"'; ?>
			data-um-lightbox-track-url="<?php echo esc_attr((string) $lightbox_view_track_url); ?>"
			data-um-lightbox-track-nonce="<?php echo esc_attr((string) $lightbox_view_track_nonce); ?>"
			data-um-fallback-allow-controls="0"
			<?php echo $enable_simple_lightbox_for_this_gallery ? ' data-um-lightbox-simple-thumbnail-click="1"' : ' data-um-lightbox-simple-thumbnail-click="0"'; ?>
			data-um-lightbox-link-mode="<?php echo esc_attr((string) $effective_link_to); ?>"
		>
			<?php if ($album_description_position === 'above' && $album_tag_description_html !== '') : ?>
				<div class="um-media-library-tag-description-wrap um-media-library-tag-description-wrap-above">
					<?php echo $album_tag_description_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
			<?php if ($lightbox_debug_enabled) : ?>
				<div class="um-mltg-debug-badge">
					<?php
					printf(
						/* translators: %s: gallery runtime ID */
						esc_html__('UM Lightbox Debug Active (%s)', 'user-manager'),
						esc_html((string) $uid)
					);
					?>
				</div>
			<?php endif; ?>
			<?php if ($album_tag_youtube_videos_html !== '') : ?>
				<?php echo $album_tag_youtube_videos_html; ?>
			<?php endif; ?>
			<?php if (empty($attachments)) : ?>
				<p class="um-media-library-tag-gallery-empty"><?php esc_html_e('No images found for this gallery.', 'user-manager'); ?></p>
			<?php elseif ($style === 'carousel_slider') : ?>
				<div class="um-mltg-carousel">
					<button type="button" class="um-mltg-carousel-nav um-mltg-carousel-prev" aria-label="<?php esc_attr_e('Previous slide', 'user-manager'); ?>">&lsaquo;</button>
					<div class="um-mltg-carousel-viewport">
						<div class="um-mltg-carousel-track">
							<?php foreach ($attachments as $index => $attachment) : ?>
								<?php
								if (!($attachment instanceof WP_Post)) {
									continue;
								}
								$attachment_id = (int) $attachment->ID;
								$image_html = wp_get_attachment_image($attachment_id, $file_size, false, ['loading' => $index < 2 ? 'eager' : 'lazy']);
								if ($image_html === '') {
									continue;
								}
								$image_src = wp_get_attachment_image_url($attachment_id, 'full');
								$permalink = get_attachment_link($attachment_id);
								$description_text = self::get_media_library_gallery_description_text($attachment_id, $description_value);
								$image_alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
								$description_attr = $image_alt !== '' ? $image_alt : $description_text;
								?>
								<figure class="um-media-library-tag-gallery-item um-mltg-carousel-slide" data-slide-index="<?php echo esc_attr((string) $index); ?>">
									<?php if ($effective_link_to === 'image_new_window' && $image_src) : ?>
										<a href="<?php echo esc_url((string) $image_src); ?>" target="_blank" rel="noopener noreferrer" class="um-media-library-tag-gallery-link"><?php echo $image_html; ?></a>
								<?php elseif ($effective_link_to === 'image' && $image_src) : ?>
										<a href="<?php echo esc_url((string) $image_src); ?>" class="um-media-library-tag-gallery-link"><?php echo $image_html; ?></a>
								<?php elseif ($effective_link_to === 'lightbox' && $image_src) : ?>
									<button
										type="button"
										class="um-media-library-tag-gallery-link um-media-library-tag-gallery-lightbox-trigger"
										data-um-modal-trigger="1"
										data-um-modal-src="<?php echo esc_attr((string) $image_src); ?>"
										data-um-modal-alt="<?php echo esc_attr($description_attr); ?>"
										data-um-modal-index="<?php echo esc_attr((string) ($index + $featured_lightbox_index_offset)); ?>"
										<?php echo ($show_description_in_lightbox && $description_text !== '') ? ' data-um-modal-caption="' . esc_attr($description_text) . '"' : ''; ?>
										<?php echo $show_lightbox_admin_edit_link ? ' data-um-modal-edit-url="' . esc_attr((string) get_edit_post_link($attachment_id, '')) . '"' : ''; ?>
										data-um-modal-attachment-id="<?php echo esc_attr((string) $attachment_id); ?>"
										onclick="<?php echo esc_attr($inline_lightbox_open_onclick); ?>"
										aria-label="<?php echo esc_attr($description_attr); ?>"
									><?php echo $image_html; ?></button>
									<?php else : ?>
										<?php echo $image_html; ?>
									<?php endif; ?>
									<?php if ($show_description_under_photo && $description_text !== '') : ?>
										<figcaption class="um-media-library-tag-gallery-caption"><?php echo esc_html($description_text); ?></figcaption>
									<?php endif; ?>
								</figure>
							<?php endforeach; ?>
						</div>
					</div>
					<button type="button" class="um-mltg-carousel-nav um-mltg-carousel-next" aria-label="<?php esc_attr_e('Next slide', 'user-manager'); ?>">&rsaquo;</button>
				</div>
				<div class="um-mltg-carousel-dots"></div>
			<?php elseif ($style === 'split_screen_feature') : ?>
				<?php $first_attachment = $attachments[0] instanceof WP_Post ? $attachments[0] : null; ?>
				<div class="um-mltg-split-screen">
					<div class="um-mltg-split-main">
						<?php if ($first_attachment instanceof WP_Post) : ?>
							<?php
							$first_id = (int) $first_attachment->ID;
							$first_main_src = wp_get_attachment_image_url($first_id, 'full');
							$first_main_description = self::get_media_library_gallery_description_text($first_id, $description_value);
							$first_main_alt = trim((string) get_post_meta($first_id, '_wp_attachment_image_alt', true));
							$first_main_thumb = wp_get_attachment_image_url($first_id, $file_size);
							?>
							<img src="<?php echo esc_url((string) ($first_main_thumb ?: $first_main_src)); ?>" alt="<?php echo esc_attr($first_main_alt !== '' ? $first_main_alt : $first_main_description); ?>" class="um-mltg-split-main-image" />
							<?php if ($show_description_under_photo && $first_main_description !== '') : ?>
								<p class="um-mltg-split-main-caption"><?php echo esc_html($first_main_description); ?></p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<div class="um-mltg-split-thumbs">
						<?php foreach ($attachments as $index => $attachment) : ?>
							<?php
							if (!($attachment instanceof WP_Post)) {
								continue;
							}
							$attachment_id = (int) $attachment->ID;
							$thumb_src = wp_get_attachment_image_url($attachment_id, $file_size);
							$full_src = wp_get_attachment_image_url($attachment_id, 'full');
							$description_text = self::get_media_library_gallery_description_text($attachment_id, $description_value);
							$image_alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
							$description_attr = $image_alt !== '' ? $image_alt : $description_text;
							if (!$thumb_src && !$full_src) {
								continue;
							}
							?>
							<button
								type="button"
								class="um-mltg-split-thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
								data-main-src="<?php echo esc_attr((string) ($thumb_src ?: $full_src)); ?>"
								data-caption="<?php echo esc_attr((string) $description_text); ?>"
								aria-label="<?php echo esc_attr($description_attr); ?>"
							>
								<img src="<?php echo esc_url((string) ($thumb_src ?: $full_src)); ?>" alt="<?php echo esc_attr($description_attr); ?>" />
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="um-media-library-tag-gallery-grid" data-um-cols-desktop="<?php echo esc_attr((string) $effective_columns_desktop); ?>" data-um-cols-mobile="<?php echo esc_attr((string) $columns_mobile); ?>" style="--um-mltg-cols-desktop:<?php echo esc_attr((string) $effective_columns_desktop); ?>;--um-mltg-cols-mobile:<?php echo esc_attr((string) $columns_mobile); ?>;">
					<?php foreach ($attachments as $index => $attachment) : ?>
						<?php
						if (!($attachment instanceof WP_Post)) {
							continue;
						}
						$attachment_id = (int) $attachment->ID;
						$image_html = wp_get_attachment_image($attachment_id, $file_size, false, ['loading' => $index < 12 ? 'eager' : 'lazy']);
						if ($image_html === '') {
							continue;
						}
						$image_src = wp_get_attachment_image_url($attachment_id, 'full');
						$permalink = get_attachment_link($attachment_id);
						$description_text = self::get_media_library_gallery_description_text($attachment_id, $description_value);
						$image_alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
						$description_attr = $image_alt !== '' ? $image_alt : $description_text;
						$date_label = get_the_date($timeline_date_format, $attachment_id);
						$is_infinite_hidden = $style === 'infinite_scroll' && $index >= max(12, $effective_columns_desktop * 3);
						$item_classes = ['um-media-library-tag-gallery-item'];
						if (in_array($style, ['mosaic_grid', 'mosaic_grid_taller'], true) && !$disable_css_crop_for_small_galleries) {
							$pattern_slot = $index % 8;
							if ($pattern_slot === 0) {
								$item_classes[] = 'um-mltg-mosaic-large';
							} elseif ($pattern_slot === 3) {
								$item_classes[] = 'um-mltg-mosaic-tall';
							} elseif ($pattern_slot === 5) {
								$item_classes[] = 'um-mltg-mosaic-wide';
							}
						}
						if ($is_infinite_hidden) {
							$item_classes[] = 'um-mltg-infinite-hidden';
						}
						?>
						<figure class="<?php echo esc_attr(implode(' ', $item_classes)); ?>"<?php echo $is_infinite_hidden ? ' data-um-infinite-hidden="1"' : ''; ?>>
							<?php if ($effective_link_to === 'image_new_window' && $image_src) : ?>
								<a href="<?php echo esc_url((string) $image_src); ?>" target="_blank" rel="noopener noreferrer" class="um-media-library-tag-gallery-link"><?php echo $image_html; ?></a>
							<?php elseif ($effective_link_to === 'image' && $image_src) : ?>
								<a href="<?php echo esc_url((string) $image_src); ?>" class="um-media-library-tag-gallery-link"><?php echo $image_html; ?></a>
							<?php elseif ($effective_link_to === 'lightbox' && $image_src) : ?>
								<button
									type="button"
									class="um-media-library-tag-gallery-link um-media-library-tag-gallery-lightbox-trigger"
									data-um-modal-trigger="1"
									data-um-modal-src="<?php echo esc_attr((string) $image_src); ?>"
									data-um-modal-alt="<?php echo esc_attr($description_attr); ?>"
									data-um-modal-index="<?php echo esc_attr((string) ($index + $featured_lightbox_index_offset)); ?>"
									<?php echo ($show_description_in_lightbox && $description_text !== '') ? ' data-um-modal-caption="' . esc_attr($description_text) . '"' : ''; ?>
									<?php echo $show_lightbox_admin_edit_link ? ' data-um-modal-edit-url="' . esc_attr((string) get_edit_post_link($attachment_id, '')) . '"' : ''; ?>
									data-um-modal-attachment-id="<?php echo esc_attr((string) $attachment_id); ?>"
									onclick="<?php echo esc_attr($inline_lightbox_open_onclick); ?>"
									aria-label="<?php echo esc_attr($description_attr); ?>"
								><?php echo $image_html; ?></button>
							<?php else : ?>
								<?php echo $image_html; ?>
							<?php endif; ?>
							<?php if ($style === 'timeline_story') : ?>
								<div class="um-mltg-timeline-meta"><?php echo esc_html((string) $date_label); ?></div>
							<?php endif; ?>
							<?php if ($show_description_under_photo && $description_text !== '') : ?>
								<figcaption class="um-media-library-tag-gallery-caption"><?php echo esc_html($description_text); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ($page_limit > 0 && $total_pages > 1 && $style !== 'infinite_scroll') : ?>
				<div class="um-media-library-tag-gallery-pagination">
					<?php for ($i = 1; $i <= $total_pages; $i++) : ?>
						<?php $page_url = add_query_arg('um_media_gallery_page', (string) $i); ?>
						<?php if ($i === $page_num) : ?>
							<strong><?php echo esc_html((string) $i); ?></strong>
						<?php else : ?>
							<a href="<?php echo esc_url($page_url); ?>"><?php echo esc_html((string) $i); ?></a>
						<?php endif; ?>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
			<?php if ($album_description_position === 'below' && $album_tag_description_html !== '') : ?>
				<div class="um-media-library-tag-description-wrap um-media-library-tag-description-wrap-below">
					<?php echo $album_tag_description_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>
		<style>
.um-media-library-tag-gallery {
	margin-bottom: 25px;
}
.um-media-library-tag-description-wrap {
	margin: 0 0 50px;
}
.um-media-library-tag-description-wrap.um-media-library-tag-description-wrap-below {
	margin-top: 12px;
	margin-bottom: 0;
}
.um-media-library-tag-description-wrap .um-media-library-tag-description-paragraph {
	margin: 0 0 50px;
}
.um-media-library-tag-description-wrap .um-media-library-tag-edit-description-link {
	margin-left: 4px;
}
.um-media-library-tag-description-wrap .um-media-library-tag-description-block .um-media-library-tag-description-paragraph {
	margin: 0 0 12px;
}
.um-media-library-tag-description-wrap .um-media-library-tag-description-bullets {
	margin: 0 0 0 22px;
	padding: 0;
	list-style: disc;
}
.um-media-library-tag-description-wrap .um-media-library-tag-description-bullet {
	margin: 0 0 8px;
}
.um-media-library-tag-description-layout {
	display: block;
}
.um-media-library-tag-description-featured {
	margin: 0 18px 10px 0;
	float: left;
}
.um-media-library-tag-description-featured .um-media-library-tag-description-featured-image {
	display: block;
	max-width: min(42vw, <?php echo esc_html((string) $featured_image_max_width_px); ?>px);
	height: auto;
}
.um-media-library-tag-description-text {
	min-width: 0;
}
.um-media-library-tag-description-layout-with-floating-image::after {
	content: "";
	display: table;
	clear: both;
}
.um-media-library-tag-description-layout-split-columns {
	display: grid;
	grid-template-columns: max-content minmax(0,1fr);
	column-gap: 18px;
	align-items: center;
}
.um-media-library-tag-description-layout-split-columns .um-media-library-tag-description-column-image .um-media-library-tag-description-featured {
	float: none;
	margin: 0;
}
.um-media-library-tag-description-layout-split-columns .um-media-library-tag-description-column-content {
	min-width: 0;
}
.um-media-library-tag-description-layout-split-columns .um-media-library-tag-description-column-image {
	width: fit-content;
	max-width: min(42vw, <?php echo esc_html((string) $featured_image_max_width_px); ?>px);
}
.um-media-library-tag-description-layout-split-columns .um-media-library-tag-description-column-image .um-media-library-tag-description-featured-image {
	width: auto;
	max-width: min(42vw, <?php echo esc_html((string) $featured_image_max_width_px); ?>px);
}
@media (max-width: 782px) {
	.um-media-library-tag-description-layout {
		display: block;
	}
	.um-media-library-tag-description-layout-split-columns {
		display: block;
	}
	.um-media-library-tag-description-layout-split-columns .um-media-library-tag-description-column-image {
		width: 100%;
		max-width: 100%;
	}
	.um-media-library-tag-description-layout-split-columns .um-media-library-tag-description-column-image .um-media-library-tag-description-featured-image {
		width: 100%;
		max-width: 100%;
	}
	.um-media-library-tag-description-featured {
		float: none;
		margin: 0 0 12px;
	}
	.um-media-library-tag-description-featured .um-media-library-tag-description-featured-image {
		max-width: 100%;
	}
}
		.um-media-library-tag-videos-wrap {
			margin: 0 0 22px;
			display: grid;
			grid-template-columns: 1fr;
			gap: 20px;
		}
		.um-media-library-tag-videos-wrap-multi {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
		.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-1 {
			grid-template-columns: minmax(0, 1fr) minmax(0, 2fr) minmax(0, 1fr);
		}
		.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-1 > .um-media-library-tag-video-item {
			grid-column: 2;
		}
		.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-3 {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}
		.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-4 {
			grid-template-columns: repeat(4, minmax(0, 1fr));
		}
		.um-media-library-tag-video-item {
			display: flex;
			flex-direction: column;
			min-width: 0;
		}
		.um-media-library-tag-video-frame {
			position: relative;
			padding-top: 56.25%;
			background: #000;
			border-radius: 6px;
			overflow: hidden;
		}
		.um-media-library-tag-video-item-vertical .um-media-library-tag-video-frame {
			padding-top: 177.7778%;
		}
		.um-media-library-tag-video-frame iframe {
			position: absolute;
			inset: 0;
			width: 100%;
			height: 100%;
			border: 0;
		}
		.um-media-library-tag-video-title {
			margin: 14px 0 8px;
			font-size: 17px;
			line-height: 1.35;
		}
		.um-media-library-tag-video-description {
			margin: 0;
			font-size: 14px;
			line-height: 1.55;
			overflow-wrap: anywhere;
		}
		@media (max-width: 782px) {
			.um-media-library-tag-videos-wrap-multi {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
			.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-3,
			.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-4 {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
			.um-media-library-tag-videos-wrap-multi.um-media-library-tag-videos-wrap-cols-1 {
				grid-template-columns: 1fr;
			}
		}
		.um-media-library-tag-gallery-grid {
			display: grid;
			grid-template-columns: repeat(var(--um-mltg-cols-desktop), minmax(0, 1fr));
			gap: 14px;
		}
		@media (max-width: 782px) {
			.um-media-library-tag-gallery-grid {
				grid-template-columns: repeat(var(--um-mltg-cols-mobile), minmax(0, 1fr));
			}
		}
		.um-media-library-tag-gallery-item { margin: 0; position: relative; }
		.um-media-library-tag-gallery-link { display: block; width: 100%; height: 100%; }
		.um-media-library-tag-gallery-lightbox-trigger {
			display: block;
			width: 100%;
			height: 100%;
			border: 0;
			background: transparent;
			padding: 0;
			cursor: pointer;
			text-align: inherit;
			font: inherit;
			color: inherit;
		}
		.um-media-library-tag-gallery-lightbox-trigger:hover,
		.um-media-library-tag-gallery-lightbox-trigger:focus,
		.um-media-library-tag-gallery-lightbox-trigger:focus-visible,
		.um-media-library-tag-gallery-lightbox-trigger:active {
			background: transparent;
			color: inherit;
			box-shadow: none;
			filter: none;
			opacity: 1;
			text-decoration: none;
		}
		.um-media-library-tag-gallery-lightbox-trigger img {
			filter: none;
			opacity: 1;
			transform: none;
		}
		.um-media-library-tag-gallery-item img { width: 100%; height: auto; display: block; }
		<?php if (!$disable_css_crop_for_small_galleries) : ?>
		.um-media-gallery-style-uniform_grid .um-media-library-tag-gallery-item img,
		.um-media-gallery-style-square_crop .um-media-library-tag-gallery-item img,
		.um-media-gallery-style-fullscreen_lightbox_grid .um-media-library-tag-gallery-item img { aspect-ratio: 1 / 1; object-fit: cover; }
		.um-media-gallery-style-wide_rectangle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 16 / 9; object-fit: cover; }
		.um-media-gallery-style-tall_rectangle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 3 / 4; object-fit: cover; }
		.um-media-gallery-style-circle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 1 / 1; object-fit: cover; border-radius: 999px; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-grid,
		.um-media-gallery-style-mosaic_grid_taller .um-media-library-tag-gallery-grid {
			grid-auto-flow: dense;
		}
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-grid {
			grid-auto-rows: clamp(110px, 14vw, 200px);
		}
		.um-media-gallery-style-mosaic_grid_taller .um-media-library-tag-gallery-grid {
			grid-auto-rows: clamp(165px, 21vw, 300px);
		}
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item img,
		.um-media-gallery-style-mosaic_grid_taller .um-media-library-tag-gallery-item img { width: 100%; height: 100%; object-fit: cover; aspect-ratio: auto; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item.um-mltg-mosaic-large,
		.um-media-gallery-style-mosaic_grid_taller .um-media-library-tag-gallery-item.um-mltg-mosaic-large { grid-column: span 2; grid-row: span 2; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item.um-mltg-mosaic-tall,
		.um-media-gallery-style-mosaic_grid_taller .um-media-library-tag-gallery-item.um-mltg-mosaic-tall { grid-row: span 2; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item.um-mltg-mosaic-wide,
		.um-media-gallery-style-mosaic_grid_taller .um-media-library-tag-gallery-item.um-mltg-mosaic-wide { grid-column: span 2; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-grid[data-um-cols-desktop="2"] .um-media-library-tag-gallery-item.um-mltg-mosaic-tall,
		.um-media-gallery-style-mosaic_grid_taller .um-media-library-tag-gallery-grid[data-um-cols-desktop="2"] .um-media-library-tag-gallery-item.um-mltg-mosaic-tall { grid-row: auto; }
		@media (max-width: 782px) {
			.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-grid[data-um-cols-mobile="1"] .um-media-library-tag-gallery-item,
			.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-grid[data-um-cols-mobile="2"] .um-media-library-tag-gallery-item,
			.um-media-gallery-style-mosaic_grid_taller .um-media-library-tag-gallery-grid[data-um-cols-mobile="1"] .um-media-library-tag-gallery-item,
			.um-media-gallery-style-mosaic_grid_taller .um-media-library-tag-gallery-grid[data-um-cols-mobile="2"] .um-media-library-tag-gallery-item { grid-column: auto !important; grid-row: auto !important; }
		}
		<?php endif; ?>
		.um-media-gallery-style-masonry_pinterest .um-media-library-tag-gallery-grid { display: block; column-count: var(--um-mltg-cols-desktop); column-gap: 14px; }
		.um-media-gallery-style-masonry_pinterest .um-media-library-tag-gallery-item { break-inside: avoid; margin: 0 0 14px; }
		.um-media-gallery-style-masonry_pinterest .um-media-library-tag-gallery-item img { width: 100%; height: auto; object-fit: cover; }
		@media (max-width: 782px) { .um-media-gallery-style-masonry_pinterest .um-media-library-tag-gallery-grid { column-count: var(--um-mltg-cols-mobile); } }
		.um-media-gallery-style-justified_rows .um-media-library-tag-gallery-grid { display:flex; flex-wrap:wrap; gap:10px; }
		.um-media-gallery-style-justified_rows .um-media-library-tag-gallery-item { flex: 1 0 180px; }
		.um-media-gallery-style-justified_rows .um-media-library-tag-gallery-item img { height: 200px; width: 100%; object-fit: cover; }
		.um-mltg-carousel { display:flex; align-items:center; gap:8px; }
		.um-mltg-carousel-viewport { overflow:hidden; width:100%; }
		.um-mltg-carousel-track { display:flex; transition: transform .35s ease; }
		.um-mltg-carousel-slide { min-width:100%; }
		.um-mltg-carousel-slide img { width:100%; max-height:70vh; object-fit:contain; background:#f6f7f7; }
		.um-mltg-carousel-nav { border:1px solid #c3c4c7; background:var(--um-mltg-accent-color, #fff); border-radius:4px; width:34px; height:34px; line-height:1; font-size:24px; cursor:pointer; }
		.um-mltg-carousel-dots { display:flex; gap:6px; justify-content:center; margin-top:10px; }
		.um-mltg-carousel-dots button { width:9px; height:9px; border-radius:50%; border:0; background:#c3c4c7; cursor:pointer; }
		.um-mltg-carousel-dots button.is-active { background:#2271b1; }
		.um-media-gallery-style-horizontal_scroll .um-media-library-tag-gallery-grid { display:flex; overflow-x:auto; gap:12px; scroll-snap-type:x mandatory; padding-bottom:6px; }
		.um-media-gallery-style-horizontal_scroll .um-media-library-tag-gallery-item { min-width:min(320px, 85vw); flex:0 0 auto; scroll-snap-align:start; }
		.um-media-gallery-style-horizontal_scroll .um-media-library-tag-gallery-item img { height:260px; object-fit:cover; }
		.um-media-gallery-style-polaroid_scrapbook .um-media-library-tag-gallery-item { background:var(--um-mltg-accent-color, #fff); padding:10px 10px 18px; box-shadow:0 8px 18px rgba(0,0,0,0.12); border:1px solid #e5e5e5; }
		.um-media-gallery-style-polaroid_scrapbook .um-media-library-tag-gallery-item:nth-child(odd) { transform: rotate(-2.3deg); }
		.um-media-gallery-style-polaroid_scrapbook .um-media-library-tag-gallery-item:nth-child(even) { transform: rotate(2.1deg); }
		.um-media-gallery-style-polaroid_scrapbook:not([data-um-lightbox-link-mode="lightbox"]) .um-media-library-tag-gallery-item:hover { transform: rotate(0deg) scale(1.02); z-index:2; }
		.um-mltg-split-screen { display:grid; grid-template-columns:minmax(0, 2fr) minmax(180px, 1fr); gap:14px; }
		.um-mltg-split-main { border:1px solid #dcdcde; border-radius:6px; padding:8px; background:var(--um-mltg-accent-color, #fff); }
		.um-mltg-split-main-image { width:100%; height:auto; max-height:70vh; object-fit:contain; display:block; }
		.um-mltg-split-main-caption { margin:8px 0 2px; font-size:13px; color:#50575e; }
		.um-mltg-split-thumbs { display:grid; gap:8px; max-height:70vh; overflow:auto; }
		.um-mltg-split-thumb { border:1px solid #c3c4c7; background:var(--um-mltg-accent-color, #fff); padding:3px; cursor:pointer; border-radius:4px; }
		.um-mltg-split-thumb.is-active { border-color:#2271b1; box-shadow:0 0 0 1px #2271b1 inset; }
		.um-mltg-split-thumb img { width:100%; height:84px; object-fit:cover; display:block; }
		@media (max-width: 782px) { .um-mltg-split-screen { grid-template-columns:1fr; } }
		.um-media-gallery-style-infinite_scroll .um-mltg-infinite-hidden { display:none; }
		.um-mltg-infinite-sentinel { width:100%; height:1px; }
		.um-media-gallery-style-perspective_3d .um-media-library-tag-gallery-grid { display:flex; gap:16px; overflow-x:auto; perspective:1000px; padding:8px 4px 14px; }
		.um-media-gallery-style-perspective_3d .um-media-library-tag-gallery-item { flex:0 0 min(340px, 82vw); transform:rotateY(-18deg) scale(.94); transform-origin:center; transition:transform .2s ease; }
		.um-media-gallery-style-perspective_3d:not([data-um-lightbox-link-mode="lightbox"]) .um-media-library-tag-gallery-item:hover { transform:rotateY(0deg) scale(1); }
		.um-media-gallery-style-perspective_3d .um-media-library-tag-gallery-item img { height:230px; object-fit:cover; border-radius:8px; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-grid { grid-template-columns:1fr; gap:20px; position:relative; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-grid::before { content:''; position:absolute; left:14px; top:0; bottom:0; width:2px; background:#dcdcde; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-item { padding-left:34px; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-item::before { content:''; position:absolute; left:7px; top:14px; width:14px; height:14px; border-radius:50%; background:#2271b1; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-item img { max-height:340px; object-fit:cover; border-radius:6px; }
		.um-mltg-timeline-meta { margin:6px 0 4px; font-size:12px; color:#2271b1; font-weight:600; }
		.um-media-library-tag-gallery-caption { margin-top: 6px; font-size: 12px; color: #50575e; }
		.um-media-library-tag-description-wrap { margin: 0 0 14px; }
		.um-media-library-tag-description-wrap-below { margin: 14px 0 0; }
		.um-media-library-tag-edit-description-link { margin-left: 6px; font-size: 12px; }
		.um-media-library-tag-gallery-pagination { margin-top: 14px; }
		.um-media-library-tag-gallery-pagination a,
		.um-media-library-tag-gallery-pagination strong { margin-right: 8px; }
		.um-mltg-lightbox-overlay { position: fixed; inset: 0; background: var(--um-mltg-lightbox-modal-bg, rgba(0, 0, 0, 0.86)); z-index: 999999; display: none; align-items: center; justify-content: center; flex-direction: column; padding: 30px; }
		.um-mltg-lightbox-overlay img { max-width: min(95vw, 1600px); max-height: 88vh; width: auto; height: auto; display: block; box-shadow: 0 4px 24px rgba(0,0,0,0.4); opacity: 1; transform: translateX(0); transition: opacity .28s ease, transform .32s ease; will-change: opacity, transform; }
		.um-mltg-lightbox-caption { margin-top: 10px; color: var(--um-mltg-lightbox-modal-text, #fff); font-size: 14px; line-height: 1.45; text-align: center; max-width: min(95vw, 1600px); display: none; }
		.um-mltg-lightbox-edit-link { margin-top: 8px; color: var(--um-mltg-lightbox-modal-text, #fff); text-decoration: underline; display: none; font-size: 13px; }
		.um-mltg-lightbox-edit-link:hover { opacity: 0.8; }
		.um-mltg-lightbox-tag-tools { margin-top: 8px; display: none; width: min(95vw, 560px); }
		.um-mltg-lightbox-tag-tools-row { display: flex; gap: 8px; justify-content: center; align-items: center; flex-wrap: wrap; }
		.um-mltg-lightbox-tag-input {
			width: min(95vw, 360px);
			padding: 7px 10px;
			border-radius: 4px;
			border: 1px solid rgba(255,255,255,0.65);
			background: rgba(255,255,255,0.12);
			color: var(--um-mltg-lightbox-modal-text, #fff);
			font-size: 13px;
		}
		.um-mltg-lightbox-tag-input::placeholder { color: rgba(255,255,255,0.8); }
		.um-mltg-lightbox-tag-add-button {
			border: 1px solid rgba(255,255,255,0.6);
			background: rgba(0,0,0,0.28);
			color: var(--um-mltg-lightbox-modal-text, #fff);
			padding: 6px 10px;
			border-radius: 4px;
			cursor: pointer;
			font-size: 13px;
		}
		.um-mltg-lightbox-tag-add-button:hover { background: rgba(255,255,255,0.12); }
		.um-mltg-lightbox-tag-add-button[disabled] { opacity: 0.6; cursor: wait; }
		.um-mltg-lightbox-tag-feedback { margin-top: 6px; color: var(--um-mltg-lightbox-modal-text, #fff); font-size: 12px; line-height: 1.35; text-align: center; min-height: 1.35em; }
		.um-mltg-lightbox-controls { margin-top: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap; }
		.um-mltg-lightbox-prev,
		.um-mltg-lightbox-next,
		.um-mltg-lightbox-slideshow-toggle {
			border: 1px solid rgba(255,255,255,0.6);
			background: rgba(0,0,0,0.28);
			color: var(--um-mltg-lightbox-modal-text, #fff);
			padding: 6px 10px;
			border-radius: 4px;
			cursor: pointer;
			font-size: 13px;
		}
		.um-mltg-lightbox-prev:hover,
		.um-mltg-lightbox-next:hover,
		.um-mltg-lightbox-slideshow-toggle:hover {
			background: rgba(255,255,255,0.12);
		}
		.um-mltg-lightbox-overlay.um-mltg-transition-crossfade img.is-transitioning { opacity: 0; }
		.um-mltg-lightbox-overlay.um-mltg-transition-slide-left img.is-transitioning { transform: translateX(-28px); opacity: 0.42; }
		.um-mltg-lightbox-close { position: absolute; top: 14px; right: 16px; border: 0; background: transparent; color: var(--um-mltg-lightbox-modal-text, #fff); font-size: 36px; line-height: 1; cursor: pointer; }
		.um-mltg-debug-badge {
			position: sticky;
			top: 0;
			z-index: 20;
			display: inline-block;
			margin: 0 0 10px;
			padding: 6px 10px;
			border-radius: 4px;
			background: #111;
			color: #ffeb3b;
			font: 600 12px/1.2 monospace;
		}
		</style>
		<script>
		window.__UM_MLTG_FORCE_DEBUG = <?php echo $lightbox_debug_enabled ? 'true' : 'false'; ?> || !!window.__UM_MLTG_FORCE_DEBUG;
		window.__UM_MLTG_FORCE_DEBUG_OPEN = <?php echo $lightbox_debug_auto_open ? 'true' : 'false'; ?> || !!window.__UM_MLTG_FORCE_DEBUG_OPEN;
		</script>
		<div class="um-mltg-lightbox-overlay" id="<?php echo esc_attr($uid); ?>-lightbox" aria-hidden="true" onclick="<?php echo esc_attr($inline_lightbox_overlay_onclick); ?>" onkeydown="<?php echo esc_attr($inline_lightbox_overlay_onkeydown); ?>">
			<button type="button" class="um-mltg-lightbox-close" onclick="<?php echo esc_attr($inline_lightbox_close_onclick); ?>" aria-label="<?php esc_attr_e('Close image', 'user-manager'); ?>">&times;</button>
			<img src="" alt="" />
			<p class="um-mltg-lightbox-caption"></p>
			<a href="#" class="um-mltg-lightbox-edit-link" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Edit image', 'user-manager'); ?></a>
			<div class="um-mltg-lightbox-tag-tools">
				<div class="um-mltg-lightbox-tag-tools-row">
					<input type="text" class="um-mltg-lightbox-tag-input" placeholder="<?php echo esc_attr__('Add tag(s), comma separated', 'user-manager'); ?>" />
					<button type="button" class="um-mltg-lightbox-tag-add-button"><?php esc_html_e('Add Tag(s)', 'user-manager'); ?></button>
				</div>
				<p class="um-mltg-lightbox-tag-feedback" aria-live="polite"></p>
			</div>
			<div class="um-mltg-lightbox-controls">
				<button type="button" class="um-mltg-lightbox-prev" onclick="<?php echo esc_attr($inline_lightbox_prev_onclick); ?>" aria-label="<?php esc_attr_e('Previous image', 'user-manager'); ?>">&lsaquo;</button>
				<button type="button" class="um-mltg-lightbox-slideshow-toggle" onclick="<?php echo esc_attr($inline_lightbox_slideshow_onclick); ?>"><?php esc_html_e('Play Slideshow', 'user-manager'); ?></button>
				<button type="button" class="um-mltg-lightbox-next" onclick="<?php echo esc_attr($inline_lightbox_next_onclick); ?>" aria-label="<?php esc_attr_e('Next image', 'user-manager'); ?>">&rsaquo;</button>
			</div>
		</div>
		<script>
		(function() {
			var root = document.getElementById('<?php echo esc_js($uid); ?>');
			if (!root) { return; }
			var overlay = document.getElementById('<?php echo esc_js($uid); ?>-lightbox');
			if (!overlay) { return; }
			overlay.setAttribute('data-um-lightbox-bound', '1');
			var debugInstanceLabel = <?php echo wp_json_encode((string) $uid); ?> || 'um-mltg';
			function readDebugQueryFlag(paramName) {
				var value = '';
				try {
					var urlObj = new URL(window.location.href);
					value = String(urlObj.searchParams.get(paramName) || '');
				} catch (err) {
					var escapeRegexParam = function(rawValue) {
						return String(rawValue || '').replace(/[\\^$.*+?()[\]{}|]/g, '\\$&');
					};
					var regex = new RegExp('(?:\\?|&)' + escapeRegexParam(paramName) + '(?:=([^&]*))?(?:&|$)', 'i');
					var matches = String(window.location.search || '').match(regex);
					if (matches && typeof matches[1] !== 'undefined') {
						value = decodeURIComponent(String(matches[1] || '').replace(/\+/g, ' '));
					}
				}
				if (!value) { return false; }
				return /^(1|true|yes|on)$/i.test(String(value));
			}
			var lightboxDebugEnabled = root.getAttribute('data-um-lightbox-debug') === '1' || readDebugQueryFlag('um_mltg_debug') || !!window.__UM_MLTG_FORCE_DEBUG;
			var lightboxDebugAutoOpen = root.getAttribute('data-um-lightbox-debug-open') === '1' || readDebugQueryFlag('um_mltg_debug_open') || !!window.__UM_MLTG_FORCE_DEBUG_OPEN;
			if (lightboxDebugAutoOpen) {
				lightboxDebugEnabled = true;
			}
			function serializeDebugDetails(details) {
				if (typeof details === 'undefined' || details === null) {
					return '';
				}
				if (typeof details === 'string') {
					return details;
				}
				try {
					return JSON.stringify(details);
				} catch (err) {
					return String(details);
				}
			}
			function ensureLightboxDebugPanel() {
				if (!lightboxDebugEnabled || !document || !document.body) {
					return null;
				}
				if (!window.__umMltgDebugPanelState) {
					var panel = document.createElement('div');
					panel.id = 'um-mltg-debug-panel';
					panel.style.position = 'fixed';
					panel.style.right = '12px';
					panel.style.bottom = '12px';
					panel.style.width = '380px';
					panel.style.maxWidth = 'calc(100vw - 24px)';
					panel.style.maxHeight = '40vh';
					panel.style.overflow = 'hidden';
					panel.style.zIndex = '1000001';
					panel.style.background = 'rgba(0,0,0,0.92)';
					panel.style.border = '1px solid rgba(255,255,255,0.28)';
					panel.style.borderRadius = '6px';
					panel.style.color = '#fff';
					panel.style.font = '12px/1.35 monospace';
					var title = document.createElement('div');
					title.textContent = 'UM Lightbox Debug Enabled (?um_mltg_debug=1)';
					title.style.padding = '8px 10px';
					title.style.borderBottom = '1px solid rgba(255,255,255,0.2)';
					title.style.fontWeight = '700';
					var logWrap = document.createElement('div');
					logWrap.style.padding = '8px 10px';
					logWrap.style.maxHeight = 'calc(40vh - 42px)';
					logWrap.style.overflow = 'auto';
					panel.appendChild(title);
					panel.appendChild(logWrap);
					document.body.appendChild(panel);
					window.__umMltgDebugPanelState = {
						logWrap: logWrap
					};
				}
				return window.__umMltgDebugPanelState;
			}
			function lightboxDebugLog(message, details) {
				if (!lightboxDebugEnabled) { return; }
				var suffix = serializeDebugDetails(details);
				var text = '[UM MLTG][' + String(debugInstanceLabel) + '] ' + String(message) + (suffix ? (' ' + suffix) : '');
				if (window.console && typeof window.console.log === 'function') {
					window.console.log(text);
				}
				var panelState = ensureLightboxDebugPanel();
				if (!panelState || !panelState.logWrap) {
					return;
				}
				var line = document.createElement('div');
				line.textContent = new Date().toISOString().slice(11, 19) + ' ' + text;
				line.style.paddingBottom = '4px';
				panelState.logWrap.insertBefore(line, panelState.logWrap.firstChild);
				while (panelState.logWrap.children.length > 45) {
					panelState.logWrap.removeChild(panelState.logWrap.lastChild);
				}
			}
			var closeBtn = overlay.querySelector('.um-mltg-lightbox-close');
			var image = overlay.querySelector('img');
			var captionEl = overlay.querySelector('.um-mltg-lightbox-caption');
			var editLinkEl = overlay.querySelector('.um-mltg-lightbox-edit-link');
			var duplicateLinkEl = null;
			var tagToolsEl = overlay.querySelector('.um-mltg-lightbox-tag-tools');
			var tagInputEl = overlay.querySelector('.um-mltg-lightbox-tag-input');
			var tagAddBtnEl = overlay.querySelector('.um-mltg-lightbox-tag-add-button');
			var tagFeedbackEl = overlay.querySelector('.um-mltg-lightbox-tag-feedback');
			var prevBtn = overlay.querySelector('.um-mltg-lightbox-prev');
			var nextBtn = overlay.querySelector('.um-mltg-lightbox-next');
			var slideshowBtn = overlay.querySelector('.um-mltg-lightbox-slideshow-toggle');
			var controlsWrap = overlay.querySelector('.um-mltg-lightbox-controls');
			var canManageLightboxTags = <?php echo $show_lightbox_admin_edit_link ? 'true' : 'false'; ?>;
			var lightboxTagAjaxUrl = <?php echo wp_json_encode((string) $lightbox_tag_ajax_url); ?> || '';
			var lightboxTagNonce = <?php echo wp_json_encode((string) $lightbox_tag_nonce); ?> || '';
			var lightboxViewTracker = window.umMediaLibraryLightboxViewTracker || null;
			var enablePrevNextKeyboard = <?php echo $lightbox_prev_next_keyboard ? 'true' : 'false'; ?>;
			var enableSwipeNavigation = root.getAttribute('data-um-lightbox-swipe') === '1';
			var enableTapSideNavigation = root.getAttribute('data-um-lightbox-tap-nav') === '1';
			var enableSlideshowButton = <?php echo $lightbox_slideshow_button ? 'true' : 'false'; ?>;
			var enableSimpleLightboxThumbnailClick = root.getAttribute('data-um-lightbox-simple-thumbnail-click') === '1';
			if (enableSimpleLightboxThumbnailClick) {
				enablePrevNextKeyboard = false;
				enableSwipeNavigation = false;
				enableTapSideNavigation = false;
				enableSlideshowButton = false;
				canManageLightboxTags = false;
			}
			var slideshowSecondsPerPhoto = <?php echo wp_json_encode((float) $lightbox_slideshow_seconds); ?>;
			var slideshowTransition = <?php echo wp_json_encode((string) $lightbox_slideshow_transition); ?> || 'none';
			var lightboxTriggerSelector = '.um-media-library-tag-gallery-lightbox-trigger,[data-um-modal-trigger],[data-um-lightbox]';
			var lightboxDeepLinkParam = 'image';
			var lightboxLegacyDeepLinkParam = 'um_lightbox_image_id';
			var lightboxLinks = [];
			var activeLightboxIndex = -1;
			var activeAttachmentId = 0;
			var slideshowTimer = null;
			var slideshowPlaying = false;
			var bodyPrevOverflow = '';
			var transitionTimer = null;
			var suppressClickOpenUntil = 0;
			var swipeStartX = 0;
			var swipeStartY = 0;
			var swipeTracking = false;
			var swipeThresholdPx = 44;
			function escapeRegexParam(value) {
				return String(value || '').replace(/[\\^$.*+?()[\]{}|]/g, '\\$&');
			}
			function refreshLightboxLinks() {
				lightboxLinks = Array.prototype.slice.call(root.querySelectorAll(lightboxTriggerSelector));
				return lightboxLinks;
			}
			refreshLightboxLinks();
			lightboxDebugLog('Runtime initialized', {
				lightboxLinks: lightboxLinks.length,
				enablePrevNextKeyboard: enablePrevNextKeyboard,
				enableSwipeNavigation: enableSwipeNavigation,
				enableTapSideNavigation: enableTapSideNavigation,
				enableSlideshowButton: enableSlideshowButton,
				enableSimpleLightboxThumbnailClick: enableSimpleLightboxThumbnailClick,
				slideshowSecondsPerPhoto: slideshowSecondsPerPhoto,
				slideshowTransition: slideshowTransition
			});
			function normalizeTransition(value) {
				var normalized = String(value || 'none');
				if (normalized === 'crossfade' || normalized === 'slide_left') {
					return normalized;
				}
				return 'none';
			}
			slideshowTransition = normalizeTransition(slideshowTransition);
			if (overlay) {
				overlay.classList.remove('um-mltg-transition-crossfade', 'um-mltg-transition-slide-left');
				if (slideshowTransition === 'crossfade') {
					overlay.classList.add('um-mltg-transition-crossfade');
				} else if (slideshowTransition === 'slide_left') {
					overlay.classList.add('um-mltg-transition-slide-left');
				}
			}
			function parseLightboxIndex(link) {
				if (!link) { return -1; }
				var raw = link.getAttribute('data-um-modal-index') || link.getAttribute('data-um-lightbox-index');
				var parsed = parseInt(raw || '', 10);
				if (!isNaN(parsed) && parsed >= 0) {
					return parsed;
				}
				lightboxDebugLog('Missing/invalid modal index; using link array index fallback', {
					rawIndex: raw || '',
					href: link.getAttribute('href') || ''
				});
				return lightboxLinks.indexOf(link);
			}
			function parseAttachmentIdValue(rawValue) {
				var parsed = parseInt(String(rawValue || '0'), 10);
				if (isNaN(parsed) || parsed < 1) {
					return 0;
				}
				return parsed;
			}
			function getAttachmentIdFromLink(link) {
				if (!link || !link.getAttribute) {
					return 0;
				}
				return parseAttachmentIdValue(
					link.getAttribute('data-um-modal-attachment-id')
					|| link.getAttribute('data-um-lightbox-attachment-id')
					|| ''
				);
			}
			function readLightboxAttachmentIdFromUrl() {
				var raw = '';
				try {
					var urlObj = new URL(window.location.href);
					raw = String(urlObj.searchParams.get(lightboxDeepLinkParam) || urlObj.searchParams.get(lightboxLegacyDeepLinkParam) || '');
				} catch (err) {
					var query = String(window.location.search || '');
					var regex = new RegExp('(?:\\?|&)' + escapeRegexParam(lightboxDeepLinkParam) + '=([^&#]*)', 'i');
					var matches = query.match(regex);
					if ((!matches || typeof matches[1] === 'undefined') && lightboxLegacyDeepLinkParam) {
						var legacyRegex = new RegExp('(?:\\?|&)' + escapeRegexParam(lightboxLegacyDeepLinkParam) + '=([^&#]*)', 'i');
						matches = query.match(legacyRegex);
					}
					if (matches && typeof matches[1] !== 'undefined') {
						raw = decodeURIComponent(String(matches[1] || '').replace(/\+/g, ' '));
					}
				}
				return parseAttachmentIdValue(raw);
			}
			function updateLightboxAttachmentIdInUrl(attachmentId) {
				if (!window.history || typeof window.history.replaceState !== 'function') {
					return;
				}
				try {
					var urlObj = new URL(window.location.href);
					if (attachmentId > 0) {
						urlObj.searchParams.set(lightboxDeepLinkParam, String(attachmentId));
						if (lightboxLegacyDeepLinkParam) {
							urlObj.searchParams.delete(lightboxLegacyDeepLinkParam);
						}
					} else {
						urlObj.searchParams.delete(lightboxDeepLinkParam);
						if (lightboxLegacyDeepLinkParam) {
							urlObj.searchParams.delete(lightboxLegacyDeepLinkParam);
						}
					}
					window.history.replaceState(window.history.state, '', urlObj.toString());
				} catch (err) {
				}
			}
			function findLightboxIndexByAttachmentId(attachmentId) {
				if (!attachmentId) {
					return -1;
				}
				for (var i = 0; i < lightboxLinks.length; i += 1) {
					if (getAttachmentIdFromLink(lightboxLinks[i]) === attachmentId) {
						return i;
					}
				}
				return -1;
			}
			function trackLightboxView(link) {
				if (!lightboxViewTracker || typeof lightboxViewTracker.track !== 'function' || !link) {
					return;
				}
				lightboxViewTracker.track(link);
			}
			function stopSlideshow() {
				if (slideshowTimer) {
					window.clearInterval(slideshowTimer);
					slideshowTimer = null;
				}
				slideshowPlaying = false;
				if (slideshowBtn) {
					slideshowBtn.textContent = '<?php echo esc_js(__('Play Slideshow', 'user-manager')); ?>';
				}
			}
			function setTagFeedback(message, isError) {
				if (!tagFeedbackEl) { return; }
				tagFeedbackEl.textContent = String(message || '');
				tagFeedbackEl.style.color = isError ? '#ffb3b3' : '#fff';
			}
			function setTagControlsBusy(isBusy) {
				var busy = !!isBusy;
				if (tagAddBtnEl) {
					tagAddBtnEl.disabled = busy;
				}
				if (tagInputEl) {
					tagInputEl.disabled = busy;
				}
				if (duplicateLinkEl) {
					duplicateLinkEl.style.pointerEvents = busy ? 'none' : 'auto';
					duplicateLinkEl.style.opacity = busy ? '0.65' : '1';
				}
			}
			function requestAddTagToAttachment(attachmentId, tagValue, callback) {
				var id = parseInt(String(attachmentId || '0'), 10);
				var tagText = String(tagValue || '').trim();
				if (!canManageLightboxTags || !lightboxTagAjaxUrl || !lightboxTagNonce || !id || !tagText) {
					if (callback) {
						callback(false, '<?php echo esc_js(__('Unable to add tag right now.', 'user-manager')); ?>');
					}
					return;
				}
				var payload = 'action=user_manager_bulk_apply_media_library_tag'
					+ '&nonce=' + encodeURIComponent(lightboxTagNonce)
					+ '&tag_new=' + encodeURIComponent(tagText)
					+ '&ids[]=' + encodeURIComponent(String(id));
				var xhr = new XMLHttpRequest();
				xhr.open('POST', lightboxTagAjaxUrl, true);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
				xhr.onreadystatechange = function() {
					if (xhr.readyState !== 4) { return; }
					var response = null;
					try {
						response = JSON.parse(xhr.responseText || '{}');
					} catch (err) {
						response = null;
					}
					if (xhr.status >= 200 && xhr.status < 300 && response && response.success) {
						if (callback) {
							callback(true, (response.data && response.data.message) ? String(response.data.message) : '');
						}
						return;
					}
					var message = '<?php echo esc_js(__('Could not add Library Tag.', 'user-manager')); ?>';
					if (response && response.data && response.data.message) {
						message = String(response.data.message);
					}
					if (callback) {
						callback(false, message);
					}
				};
				xhr.send(payload);
			}
			function addTagListToActiveAttachment(tags) {
				var queue = Array.isArray(tags) ? tags.slice() : [];
				queue = queue.map(function(tag) {
					return String(tag || '').trim();
				}).filter(function(tag) {
					return !!tag;
				});
				queue = queue.filter(function(tag, idx, list) {
					return list.indexOf(tag) === idx;
				});
				if (!queue.length) {
					setTagFeedback('<?php echo esc_js(__('Enter at least one tag name.', 'user-manager')); ?>', true);
					return;
				}
				if (!activeAttachmentId) {
					setTagFeedback('<?php echo esc_js(__('No active image selected.', 'user-manager')); ?>', true);
					return;
				}
				var completed = 0;
				setTagControlsBusy(true);
				setTagFeedback('<?php echo esc_js(__('Saving tags...', 'user-manager')); ?>', false);
				var processNext = function() {
					if (!queue.length) {
						setTagControlsBusy(false);
						setTagFeedback(
							completed > 1
								? '<?php echo esc_js(__('Tags added successfully.', 'user-manager')); ?>'
								: '<?php echo esc_js(__('Tag added successfully.', 'user-manager')); ?>',
							false
						);
						if (tagInputEl) {
							tagInputEl.value = '';
						}
						return;
					}
					var nextTag = queue.shift();
					requestAddTagToAttachment(activeAttachmentId, nextTag, function(success, message) {
						if (!success) {
							setTagControlsBusy(false);
							setTagFeedback(message || '<?php echo esc_js(__('Could not add Library Tag.', 'user-manager')); ?>', true);
							return;
						}
						completed += 1;
						processNext();
					});
				};
				processNext();
			}
			function renderLightboxFromLink(link, animate) {
				if (!link || !image) { return false; }
				var src = link.getAttribute('data-um-modal-src') || link.getAttribute('data-um-lightbox-src') || link.getAttribute('href') || '';
				if (!src) {
					lightboxDebugLog('Render aborted: trigger has no src/href', {
						index: parseLightboxIndex(link)
					});
					return false;
				}
				var caption = link.getAttribute('data-um-modal-caption') || link.getAttribute('data-um-lightbox-caption') || '';
				var altText = link.getAttribute('data-um-modal-alt') || link.getAttribute('data-um-lightbox-alt') || '';
				var editUrl = link.getAttribute('data-um-modal-edit-url') || link.getAttribute('data-um-lightbox-edit-url') || '';
				var attachmentIdRaw = link.getAttribute('data-um-modal-attachment-id') || link.getAttribute('data-um-lightbox-attachment-id') || '';
				var attachmentId = parseInt(String(attachmentIdRaw || '0'), 10);
				if (isNaN(attachmentId) || attachmentId < 1) {
					attachmentId = 0;
				}
				trackLightboxView(link);
				var shouldAnimate = !!animate && slideshowTransition !== 'none';
				var applyPayload = function() {
					image.setAttribute('src', src);
					image.setAttribute('alt', altText);
					activeAttachmentId = attachmentId;
					if (enableSimpleLightboxThumbnailClick) {
						activeAttachmentId = 0;
						if (captionEl) {
							captionEl.textContent = '';
							captionEl.style.display = 'none';
						}
						if (editLinkEl) {
							editLinkEl.setAttribute('href', '#');
							editLinkEl.style.display = 'none';
						}
						if (duplicateLinkEl) {
							duplicateLinkEl.style.display = 'none';
						}
						if (tagToolsEl) {
							tagToolsEl.style.display = 'none';
						}
						setTagFeedback('', false);
						setTagControlsBusy(false);
						return;
					}
					if (captionEl) {
						captionEl.textContent = caption;
						captionEl.style.display = caption ? 'block' : 'none';
					}
					if (editLinkEl) {
						if (editUrl) {
							editLinkEl.setAttribute('href', editUrl);
							editLinkEl.style.display = 'inline-block';
						} else {
							editLinkEl.setAttribute('href', '#');
							editLinkEl.style.display = 'none';
						}
					}
					if (tagToolsEl) {
						tagToolsEl.style.display = (canManageLightboxTags && attachmentId > 0) ? 'block' : 'none';
					}
					setTagFeedback('', false);
					setTagControlsBusy(false);
				};
				lightboxDebugLog('Rendering lightbox payload', {
					index: parseLightboxIndex(link),
					src: src,
					animate: shouldAnimate
				});
				if (transitionTimer) {
					window.clearTimeout(transitionTimer);
					transitionTimer = null;
				}
				if (shouldAnimate) {
					image.classList.add('is-transitioning');
					transitionTimer = window.setTimeout(function() {
						applyPayload();
						image.classList.remove('is-transitioning');
						transitionTimer = null;
					}, 160);
					return true;
				}
				image.classList.remove('is-transitioning');
				applyPayload();
				if (!enableSimpleLightboxThumbnailClick && captionEl) {
					captionEl.style.display = caption ? 'block' : 'none';
				}
				return true;
			}
			function showLightboxByIndex(nextIndex) {
				if (!lightboxLinks.length) { return; }
				var total = lightboxLinks.length;
				var normalizedIndex = nextIndex;
				if (normalizedIndex < 0) {
					normalizedIndex = total - 1;
				} else if (normalizedIndex >= total) {
					normalizedIndex = 0;
				}
				var link = lightboxLinks[normalizedIndex];
				var shouldAnimate = overlay.getAttribute('aria-hidden') === 'false' && activeLightboxIndex >= 0;
				if (!renderLightboxFromLink(link, shouldAnimate)) {
					lightboxDebugLog('showLightboxByIndex aborted: render returned false', {
						requestedIndex: nextIndex,
						normalizedIndex: normalizedIndex
					});
					return;
				}
				activeLightboxIndex = normalizedIndex;
				updateLightboxAttachmentIdInUrl(getAttachmentIdFromLink(link));
				lightboxDebugLog('Active lightbox index set', {
					activeLightboxIndex: activeLightboxIndex
				});
			}
			function handleSwipeNavigation(deltaX, deltaY) {
				if (!enableSwipeNavigation || overlay.getAttribute('aria-hidden') !== 'false' || activeLightboxIndex < 0) {
					return;
				}
				var absX = Math.abs(deltaX);
				var absY = Math.abs(deltaY);
				if (absX < swipeThresholdPx || absX < (absY * 1.2)) {
					return;
				}
				if (deltaX < 0) {
					lightboxDebugLog('Touch swipe left -> next');
					showLightboxByIndex(activeLightboxIndex + 1);
				} else {
					lightboxDebugLog('Touch swipe right -> previous');
					showLightboxByIndex(activeLightboxIndex - 1);
				}
			}
			function closeOverlay() {
				stopSlideshow();
				if (lightboxViewTracker && typeof lightboxViewTracker.reset === 'function' && root && root.id) {
					lightboxViewTracker.reset(String(root.id) + '-lightbox');
				}
				overlay.style.display = 'none';
				overlay.setAttribute('aria-hidden', 'true');
				lightboxDebugLog('Overlay closed');
				if (transitionTimer) {
					window.clearTimeout(transitionTimer);
					transitionTimer = null;
				}
				if (image) {
					image.setAttribute('src', '');
					image.classList.remove('is-transitioning');
				}
				if (captionEl) {
					captionEl.textContent = '';
					captionEl.style.display = 'none';
				}
				if (editLinkEl) {
					editLinkEl.setAttribute('href', '#');
					editLinkEl.style.display = 'none';
				}
				if (tagToolsEl) {
					tagToolsEl.style.display = 'none';
				}
				if (tagInputEl) {
					tagInputEl.value = '';
				}
				setTagFeedback('', false);
				setTagControlsBusy(false);
				activeAttachmentId = 0;
				updateLightboxAttachmentIdInUrl(0);
				if (document && document.body) {
					document.body.style.overflow = bodyPrevOverflow;
				}
			}
			if (prevBtn) {
				prevBtn.style.display = enablePrevNextKeyboard ? 'inline-block' : 'none';
				prevBtn.disabled = !enablePrevNextKeyboard;
				prevBtn.setAttribute('aria-hidden', enablePrevNextKeyboard ? 'false' : 'true');
				if (enablePrevNextKeyboard) {
					prevBtn.removeAttribute('hidden');
				} else {
					prevBtn.setAttribute('hidden', 'hidden');
				}
			}
			if (nextBtn) {
				nextBtn.style.display = enablePrevNextKeyboard ? 'inline-block' : 'none';
				nextBtn.disabled = !enablePrevNextKeyboard;
				nextBtn.setAttribute('aria-hidden', enablePrevNextKeyboard ? 'false' : 'true');
				if (enablePrevNextKeyboard) {
					nextBtn.removeAttribute('hidden');
				} else {
					nextBtn.setAttribute('hidden', 'hidden');
				}
			}
			if (slideshowBtn) {
				slideshowBtn.style.display = enableSlideshowButton ? 'inline-block' : 'none';
				slideshowBtn.disabled = !enableSlideshowButton;
				slideshowBtn.setAttribute('aria-hidden', enableSlideshowButton ? 'false' : 'true');
				if (enableSlideshowButton) {
					slideshowBtn.removeAttribute('hidden');
				} else {
					slideshowBtn.setAttribute('hidden', 'hidden');
				}
			}
			if (controlsWrap) {
				controlsWrap.style.display = (enableSimpleLightboxThumbnailClick || (!enablePrevNextKeyboard && !enableSlideshowButton)) ? 'none' : 'flex';
			}
			if (overlay && overlay.style.display !== 'none') {
				overlay.style.display = 'none';
				overlay.setAttribute('aria-hidden', 'true');
			}
			if (tagAddBtnEl) {
				tagAddBtnEl.addEventListener('click', function() {
					var raw = tagInputEl ? String(tagInputEl.value || '') : '';
					var tagParts = raw.split(/[\n,;]+/).map(function(part) {
						return String(part || '').trim();
					}).filter(function(part) {
						return !!part;
					});
					addTagListToActiveAttachment(tagParts);
				});
			}
			if (tagInputEl) {
				tagInputEl.addEventListener('keydown', function(event) {
					if (event.key !== 'Enter') { return; }
					event.preventDefault();
					if (tagAddBtnEl) {
						tagAddBtnEl.click();
					}
				});
			}
			function openLightboxFromLink(link, sourceLabel) {
				if (!link) { return; }
				refreshLightboxLinks();
				var initialIndex = parseLightboxIndex(link);
				if (initialIndex < 0) {
					initialIndex = lightboxLinks.indexOf(link);
				}
				if (initialIndex < 0 && lightboxLinks.length) {
					initialIndex = 0;
				}
				if (initialIndex < 0) {
					lightboxDebugLog('Open aborted: no lightbox links found for trigger', {
						source: sourceLabel || 'unknown'
					});
					return;
				}
				lightboxDebugLog('Opening lightbox from link', {
					initialIndex: initialIndex,
					source: sourceLabel || 'unknown',
					src: link.getAttribute('data-um-modal-src') || link.getAttribute('data-um-lightbox-src') || '',
					href: link.getAttribute('href') || '',
					defaultPrevented: false
				});
				showLightboxByIndex(initialIndex);
				if (document && document.body) {
					bodyPrevOverflow = document.body.style.overflow || '';
					document.body.style.overflow = 'hidden';
				}
				overlay.style.display = 'flex';
				overlay.setAttribute('aria-hidden', 'false');
				lightboxDebugLog('Overlay opened', {
					display: overlay.style.display,
					ariaHidden: overlay.getAttribute('aria-hidden')
				});
			}
			function resolveLightboxTriggerFromEventNode(node) {
				if (!node || !node.closest) {
					return null;
				}
				var link = node.closest(lightboxTriggerSelector);
				if (link && root.contains(link)) {
					return link;
				}
				var tile = node.closest('.um-media-library-tag-gallery-item');
				if (tile && root.contains(tile)) {
					var tileTrigger = tile.querySelector(lightboxTriggerSelector);
					if (tileTrigger) {
						return tileTrigger;
					}
				}
				return null;
			}
			function openLightboxFromEvent(event, sourceLabel) {
				var evt = event || window.event;
				var node = evt && evt.target ? evt.target : null;
				var link = resolveLightboxTriggerFromEventNode(node);
				if (!link) {
					return false;
				}
				if (evt) {
					evt.preventDefault();
					if (typeof evt.stopImmediatePropagation === 'function') {
						evt.stopImmediatePropagation();
					} else {
						evt.stopPropagation();
					}
				}
				var now = Date.now();
				if (now < suppressClickOpenUntil) {
					return true;
				}
				suppressClickOpenUntil = now + 220;
				openLightboxFromLink(link, sourceLabel || 'event');
				return true;
			}
			function autoOpenLightboxFromUrlAttachmentId() {
				var attachmentIdFromUrl = readLightboxAttachmentIdFromUrl();
				if (!attachmentIdFromUrl) {
					return false;
				}
				if (window.__umMltgDeepLinkConsumed === attachmentIdFromUrl) {
					return false;
				}
				refreshLightboxLinks();
				var matchedIndex = findLightboxIndexByAttachmentId(attachmentIdFromUrl);
				if (matchedIndex < 0) {
					return false;
				}
				lightboxDebugLog('Auto-open from URL attachment id', {
					attachmentId: attachmentIdFromUrl,
					index: matchedIndex
				});
				showLightboxByIndex(matchedIndex);
				if (document && document.body) {
					bodyPrevOverflow = document.body.style.overflow || '';
					document.body.style.overflow = 'hidden';
				}
				overlay.style.display = 'flex';
				overlay.setAttribute('aria-hidden', 'false');
				window.__umMltgDeepLinkConsumed = attachmentIdFromUrl;
				return true;
			}
			root.addEventListener('click', function(event) {
				openLightboxFromEvent(event, 'root-capture');
			}, true);
			document.addEventListener('click', function(event) {
				var node = event && event.target ? event.target : null;
				if (!node || !root.contains(node)) {
					return;
				}
				openLightboxFromEvent(event, 'document-capture');
			}, true);
			if (closeBtn) {
				closeBtn.addEventListener('click', closeOverlay);
			}
			if (prevBtn) {
				prevBtn.addEventListener('click', function(event) {
					event.preventDefault();
					event.stopPropagation();
					lightboxDebugLog('Previous button click', {
						enablePrevNextKeyboard: enablePrevNextKeyboard,
						activeLightboxIndex: activeLightboxIndex
					});
					if (!enablePrevNextKeyboard || activeLightboxIndex < 0) { return; }
					showLightboxByIndex(activeLightboxIndex - 1);
				});
			}
			if (nextBtn) {
				nextBtn.addEventListener('click', function(event) {
					event.preventDefault();
					event.stopPropagation();
					lightboxDebugLog('Next button click', {
						enablePrevNextKeyboard: enablePrevNextKeyboard,
						activeLightboxIndex: activeLightboxIndex
					});
					if (!enablePrevNextKeyboard || activeLightboxIndex < 0) { return; }
					showLightboxByIndex(activeLightboxIndex + 1);
				});
			}
			if (slideshowBtn) {
				slideshowBtn.addEventListener('click', function(event) {
					event.preventDefault();
					event.stopPropagation();
					lightboxDebugLog('Slideshow button click', {
						enableSlideshowButton: enableSlideshowButton,
						activeLightboxIndex: activeLightboxIndex,
						slideshowPlaying: slideshowPlaying
					});
					if (!enableSlideshowButton || !lightboxLinks.length || activeLightboxIndex < 0) { return; }
					if (slideshowPlaying) {
						stopSlideshow();
						return;
					}
					var intervalMs = Math.max(1000, Math.round(parseFloat(String(slideshowSecondsPerPhoto || 3)) * 1000));
					slideshowPlaying = true;
					slideshowBtn.textContent = '<?php echo esc_js(__('Pause Slideshow', 'user-manager')); ?>';
					slideshowTimer = window.setInterval(function() {
						if (overlay.getAttribute('aria-hidden') !== 'false') {
							stopSlideshow();
							return;
						}
						showLightboxByIndex(activeLightboxIndex + 1);
					}, intervalMs);
				});
			}
			overlay.addEventListener('click', function(event) {
				if (event.target === overlay) {
					lightboxDebugLog('Overlay backdrop click -> close');
					closeOverlay();
				}
			});
			overlay.addEventListener('touchstart', function(event) {
				if (!enableSwipeNavigation || overlay.getAttribute('aria-hidden') !== 'false') {
					swipeTracking = false;
					return;
				}
				var touch = event.touches && event.touches.length ? event.touches[0] : null;
				if (!touch) {
					swipeTracking = false;
					return;
				}
				swipeStartX = touch.clientX;
				swipeStartY = touch.clientY;
				swipeTracking = true;
			});
			overlay.addEventListener('touchend', function(event) {
				if (!swipeTracking) {
					return;
				}
				swipeTracking = false;
				var touch = event.changedTouches && event.changedTouches.length ? event.changedTouches[0] : null;
				if (!touch) {
					return;
				}
				handleSwipeNavigation(touch.clientX - swipeStartX, touch.clientY - swipeStartY);
			});
			overlay.addEventListener('touchcancel', function() {
				swipeTracking = false;
			});
			overlay.addEventListener('click', function(event) {
				if (!enableTapSideNavigation || overlay.getAttribute('aria-hidden') !== 'false' || activeLightboxIndex < 0) {
					return;
				}
				if (event.target === overlay) {
					return;
				}
				if (event.target && event.target.closest && event.target.closest('.um-mltg-lightbox-close,.um-mltg-lightbox-prev,.um-mltg-lightbox-next,.um-mltg-lightbox-slideshow-toggle,.um-mltg-lightbox-edit-link,.um-mltg-lightbox-tag-tools')) {
					return;
				}
				// Tap-side navigation should only react to clicks on the actual image,
				// not arbitrary background/container clicks.
				if (!image || event.target !== image) {
					return;
				}
				var bounds = image && image.getBoundingClientRect ? image.getBoundingClientRect() : null;
				if (!bounds || !isFinite(bounds.left) || !isFinite(bounds.width) || bounds.width <= 0) {
					return;
				}
				var clientX = event.clientX;
				if (!isFinite(clientX) || clientX < bounds.left || clientX > bounds.right) {
					return;
				}
				event.preventDefault();
				event.stopPropagation();
				if (clientX < (bounds.left + (bounds.width / 2))) {
					showLightboxByIndex(activeLightboxIndex - 1);
				} else {
					showLightboxByIndex(activeLightboxIndex + 1);
				}
			}, true);
			document.addEventListener('keydown', function(event) {
				if (event.key === 'Escape' && overlay.getAttribute('aria-hidden') === 'false') {
					closeOverlay();
					return;
				}
				if (!enablePrevNextKeyboard || overlay.getAttribute('aria-hidden') !== 'false' || activeLightboxIndex < 0) {
					return;
				}
				if (event.key === 'ArrowLeft') {
					event.preventDefault();
					lightboxDebugLog('Keyboard ArrowLeft navigation');
					showLightboxByIndex(activeLightboxIndex - 1);
				} else if (event.key === 'ArrowRight') {
					event.preventDefault();
					lightboxDebugLog('Keyboard ArrowRight navigation');
					showLightboxByIndex(activeLightboxIndex + 1);
				}
			});
			if (lightboxDebugAutoOpen) {
				refreshLightboxLinks();
			}
			var openedByUrlDeepLink = autoOpenLightboxFromUrlAttachmentId();
			if (!openedByUrlDeepLink && lightboxDebugAutoOpen && lightboxLinks.length) {
				lightboxDebugLog('Auto-open enabled for first lightbox item');
				window.setTimeout(function() {
					openLightboxFromLink(lightboxLinks[0], 'debug-auto-open');
				}, 80);
			}

			var carouselRoot = root.querySelector('.um-mltg-carousel');
			if (carouselRoot) {
				var track = carouselRoot.querySelector('.um-mltg-carousel-track');
				var slides = track ? track.querySelectorAll('.um-mltg-carousel-slide') : [];
				var dotsWrap = root.querySelector('.um-mltg-carousel-dots');
				var prevBtn = carouselRoot.querySelector('.um-mltg-carousel-prev');
				var nextBtn = carouselRoot.querySelector('.um-mltg-carousel-next');
				var slideIndex = 0;
				function renderCarousel() {
					if (!track || !slides.length) { return; }
					if (slideIndex < 0) { slideIndex = slides.length - 1; }
					if (slideIndex >= slides.length) { slideIndex = 0; }
					track.style.transform = 'translateX(' + String(-slideIndex * 100) + '%)';
					if (dotsWrap) {
						var dots = dotsWrap.querySelectorAll('button');
						dots.forEach(function(dot, idx) {
							if (idx === slideIndex) {
								dot.classList.add('is-active');
							} else {
								dot.classList.remove('is-active');
							}
						});
					}
				}
				if (dotsWrap && slides.length > 1) {
					slides.forEach(function(_, idx) {
						var dot = document.createElement('button');
						dot.type = 'button';
						dot.addEventListener('click', function() {
							slideIndex = idx;
							renderCarousel();
						});
						dotsWrap.appendChild(dot);
					});
				}
				if (prevBtn) {
					prevBtn.addEventListener('click', function() {
						slideIndex -= 1;
						renderCarousel();
					});
				}
				if (nextBtn) {
					nextBtn.addEventListener('click', function() {
						slideIndex += 1;
						renderCarousel();
					});
				}
				renderCarousel();
			}

			var splitRoot = root.querySelector('.um-mltg-split-screen');
			if (splitRoot) {
				var mainImage = splitRoot.querySelector('.um-mltg-split-main-image');
				var mainCaption = splitRoot.querySelector('.um-mltg-split-main-caption');
				var thumbs = splitRoot.querySelectorAll('.um-mltg-split-thumb');
				thumbs.forEach(function(thumb) {
					thumb.addEventListener('click', function() {
						var nextSrc = thumb.getAttribute('data-main-src') || '';
						var nextCaption = thumb.getAttribute('data-caption') || '';
						if (mainImage && nextSrc) {
							mainImage.setAttribute('src', nextSrc);
						}
						if (mainCaption) {
							mainCaption.textContent = nextCaption;
						}
						thumbs.forEach(function(item) { item.classList.remove('is-active'); });
						thumb.classList.add('is-active');
					});
				});
			}

			if (root.classList.contains('um-media-gallery-style-infinite_scroll')) {
				var hidden = root.querySelectorAll('[data-um-infinite-hidden="1"]');
				if (hidden.length) {
					var revealBatch = Math.max(6, parseInt(root.style.getPropertyValue('--um-mltg-cols-desktop'), 10) * 2 || 8);
					var sentinel = document.createElement('div');
					sentinel.className = 'um-mltg-infinite-sentinel';
					root.appendChild(sentinel);
					var revealMore = function() {
						var count = 0;
						hidden.forEach(function(node) {
							if (count >= revealBatch) { return; }
							if (node.style.display === 'none' || node.classList.contains('um-mltg-infinite-hidden')) {
								node.classList.remove('um-mltg-infinite-hidden');
								node.style.display = '';
								node.removeAttribute('data-um-infinite-hidden');
								count += 1;
							}
						});
					};
					if ('IntersectionObserver' in window) {
						var io = new IntersectionObserver(function(entries) {
							entries.forEach(function(entry) {
								if (entry.isIntersecting) {
									revealMore();
								}
							});
						}, { rootMargin: '120px 0px' });
						io.observe(sentinel);
					} else {
						revealMore();
					}
				}
			}
			if (overlay) {
				overlay.setAttribute('data-um-lightbox-ready', '1');
			}
		})();
		</script>
		<script>
		(function() {
			var root = document.getElementById('<?php echo esc_js($uid); ?>');
			if (!root) { return; }
			var overlay = document.getElementById('<?php echo esc_js($uid); ?>-lightbox');
			if (!overlay) { return; }
			if (overlay.getAttribute('data-um-lightbox-ready') === '1') { return; }
			if (overlay.getAttribute('data-um-lightbox-fallback-bound') === '1') { return; }
			overlay.setAttribute('data-um-lightbox-fallback-bound', '1');

			var closeBtn = overlay.querySelector('.um-mltg-lightbox-close');
			var image = overlay.querySelector('img');
			var captionEl = overlay.querySelector('.um-mltg-lightbox-caption');
			var editLinkEl = overlay.querySelector('.um-mltg-lightbox-edit-link');
			var tagToolsEl = overlay.querySelector('.um-mltg-lightbox-tag-tools');
			var controlsWrap = overlay.querySelector('.um-mltg-lightbox-controls');
			var bodyPrevOverflow = '';
			var lightboxViewTracker = window.umMediaLibraryLightboxViewTracker || null;
			var enablePrevNextKeyboard = root.getAttribute('data-um-lightbox-prev-next') === '1';
			var enableSwipeNavigation = root.getAttribute('data-um-lightbox-swipe') === '1';
			var enableTapSideNavigation = root.getAttribute('data-um-lightbox-tap-nav') === '1';
			var enableSimpleLightboxThumbnailClick = root.getAttribute('data-um-lightbox-simple-thumbnail-click') === '1';
			if (enableSimpleLightboxThumbnailClick) {
				enablePrevNextKeyboard = false;
				enableSwipeNavigation = false;
				enableTapSideNavigation = false;
			}
			var lightboxItems = [];
			var activeIndex = -1;
			var triggerSelector = '.um-media-library-tag-gallery-lightbox-trigger,[data-um-modal-trigger],[data-um-lightbox]';
			var lightboxDeepLinkParam = 'image';
			var lightboxLegacyDeepLinkParam = 'um_lightbox_image_id';
			var swipeStartX = 0;
			var swipeStartY = 0;
			var swipeTracking = false;
			var swipeThresholdPx = 44;
			function escapeRegexParam(value) {
				return String(value || '').replace(/[\\^$.*+?()[\]{}|]/g, '\\$&');
			}
			function parseAttachmentIdValue(rawValue) {
				var parsed = parseInt(String(rawValue || '0'), 10);
				if (isNaN(parsed) || parsed < 1) {
					return 0;
				}
				return parsed;
			}
			function getAttachmentIdFromTrigger(trigger) {
				if (!trigger || !trigger.getAttribute) {
					return 0;
				}
				return parseAttachmentIdValue(
					trigger.getAttribute('data-um-modal-attachment-id')
					|| trigger.getAttribute('data-um-lightbox-attachment-id')
					|| ''
				);
			}
			function readLightboxAttachmentIdFromUrl() {
				var raw = '';
				try {
					var urlObj = new URL(window.location.href);
					raw = String(urlObj.searchParams.get(lightboxDeepLinkParam) || urlObj.searchParams.get(lightboxLegacyDeepLinkParam) || '');
				} catch (err) {
					var query = String(window.location.search || '');
					var regex = new RegExp('(?:\\?|&)' + escapeRegexParam(lightboxDeepLinkParam) + '=([^&#]*)', 'i');
					var matches = query.match(regex);
					if ((!matches || typeof matches[1] === 'undefined') && lightboxLegacyDeepLinkParam) {
						var legacyRegex = new RegExp('(?:\\?|&)' + escapeRegexParam(lightboxLegacyDeepLinkParam) + '=([^&#]*)', 'i');
						matches = query.match(legacyRegex);
					}
					if (matches && typeof matches[1] !== 'undefined') {
						raw = decodeURIComponent(String(matches[1] || '').replace(/\+/g, ' '));
					}
				}
				return parseAttachmentIdValue(raw);
			}
			function updateLightboxAttachmentIdInUrl(attachmentId) {
				if (!window.history || typeof window.history.replaceState !== 'function') {
					return;
				}
				try {
					var urlObj = new URL(window.location.href);
					if (attachmentId > 0) {
						urlObj.searchParams.set(lightboxDeepLinkParam, String(attachmentId));
						if (lightboxLegacyDeepLinkParam) {
							urlObj.searchParams.delete(lightboxLegacyDeepLinkParam);
						}
					} else {
						urlObj.searchParams.delete(lightboxDeepLinkParam);
						if (lightboxLegacyDeepLinkParam) {
							urlObj.searchParams.delete(lightboxLegacyDeepLinkParam);
						}
					}
					window.history.replaceState(window.history.state, '', urlObj.toString());
				} catch (err) {
				}
			}
			function findTriggerByAttachmentId(attachmentId) {
				if (!attachmentId) {
					return null;
				}
				for (var i = 0; i < lightboxItems.length; i += 1) {
					if (getAttachmentIdFromTrigger(lightboxItems[i]) === attachmentId) {
						return lightboxItems[i];
					}
				}
				return null;
			}

			function refreshItems() {
				lightboxItems = Array.prototype.slice.call(root.querySelectorAll(triggerSelector));
				return lightboxItems;
			}
			refreshItems();

			var prevBtn = overlay.querySelector('.um-mltg-lightbox-prev');
			var nextBtn = overlay.querySelector('.um-mltg-lightbox-next');
			var slideshowBtn = overlay.querySelector('.um-mltg-lightbox-slideshow-toggle');
			if (controlsWrap) {
				controlsWrap.style.display = enablePrevNextKeyboard ? 'flex' : 'none';
			}
			if (prevBtn) { prevBtn.style.display = enablePrevNextKeyboard ? 'inline-block' : 'none'; }
			if (nextBtn) { nextBtn.style.display = enablePrevNextKeyboard ? 'inline-block' : 'none'; }
			if (slideshowBtn) { slideshowBtn.style.display = 'none'; }
			if (tagToolsEl) {
				tagToolsEl.style.display = 'none';
			}

			function resolveTrigger(node) {
				if (!node || !node.closest) { return null; }
				var trigger = node.closest(triggerSelector);
				if (trigger && root.contains(trigger)) {
					return trigger;
				}
				var tile = node.closest('.um-media-library-tag-gallery-item');
				if (tile && root.contains(tile)) {
					return tile.querySelector(triggerSelector);
				}
				return null;
			}

			function findItemIndex(trigger) {
				if (!trigger) { return -1; }
				var indexRaw = trigger.getAttribute('data-um-modal-index') || trigger.getAttribute('data-um-lightbox-index');
				var parsed = parseInt(String(indexRaw || ''), 10);
				if (!isNaN(parsed) && parsed >= 0) {
					return parsed;
				}
				return lightboxItems.indexOf(trigger);
			}

			function openFromTrigger(trigger) {
				if (!trigger || !image) { return; }
				refreshItems();
				var src = trigger.getAttribute('data-um-modal-src') || trigger.getAttribute('data-um-lightbox-src') || trigger.getAttribute('href') || '';
				if (!src) { return; }
				var caption = trigger.getAttribute('data-um-modal-caption') || trigger.getAttribute('data-um-lightbox-caption') || '';
				var altText = trigger.getAttribute('data-um-modal-alt') || trigger.getAttribute('data-um-lightbox-alt') || '';
				var editUrl = trigger.getAttribute('data-um-modal-edit-url') || trigger.getAttribute('data-um-lightbox-edit-url') || '';
				if (lightboxViewTracker && typeof lightboxViewTracker.track === 'function') {
					lightboxViewTracker.track(trigger);
				}
				activeIndex = findItemIndex(trigger);
				if (activeIndex < 0 && lightboxItems.length) {
					activeIndex = 0;
				}
				updateLightboxAttachmentIdInUrl(getAttachmentIdFromTrigger(trigger));

				image.setAttribute('src', src);
				image.setAttribute('alt', altText);
				if (captionEl) {
					captionEl.textContent = caption;
					captionEl.style.display = caption ? 'block' : 'none';
				}
				if (editLinkEl) {
					if (editUrl) {
						editLinkEl.setAttribute('href', editUrl);
						editLinkEl.style.display = 'inline-block';
					} else {
						editLinkEl.setAttribute('href', '#');
						editLinkEl.style.display = 'none';
					}
				}
				if (document && document.body) {
					bodyPrevOverflow = document.body.style.overflow || '';
					document.body.style.overflow = 'hidden';
				}
				overlay.style.display = 'flex';
				overlay.setAttribute('aria-hidden', 'false');
			}

			function openByIndex(nextIndex) {
				if (!enablePrevNextKeyboard && !enableSwipeNavigation && !enableTapSideNavigation) { return; }
				if (!lightboxItems.length) { return; }
				var idx = nextIndex;
				if (idx < 0) {
					idx = lightboxItems.length - 1;
				} else if (idx >= lightboxItems.length) {
					idx = 0;
				}
				var nextTrigger = lightboxItems[idx];
				if (!nextTrigger) { return; }
				openFromTrigger(nextTrigger);
			}
			function handleSwipeNavigation(deltaX, deltaY) {
				if (!enableSwipeNavigation || overlay.getAttribute('aria-hidden') !== 'false' || activeIndex < 0) {
					return;
				}
				var absX = Math.abs(deltaX);
				var absY = Math.abs(deltaY);
				if (absX < swipeThresholdPx || absX < (absY * 1.2)) {
					return;
				}
				if (deltaX < 0) {
					openByIndex(activeIndex + 1);
				} else {
					openByIndex(activeIndex - 1);
				}
			}

			function closeOverlay() {
				if (lightboxViewTracker && typeof lightboxViewTracker.reset === 'function') {
					lightboxViewTracker.reset('<?php echo esc_js($uid); ?>-lightbox');
				}
				overlay.style.display = 'none';
				overlay.setAttribute('aria-hidden', 'true');
				if (image) {
					image.setAttribute('src', '');
				}
				if (captionEl) {
					captionEl.textContent = '';
					captionEl.style.display = 'none';
				}
				if (editLinkEl) {
					editLinkEl.setAttribute('href', '#');
					editLinkEl.style.display = 'none';
				}
				if (document && document.body) {
					document.body.style.overflow = bodyPrevOverflow;
				}
				updateLightboxAttachmentIdInUrl(0);
				activeIndex = -1;
			}

			root.addEventListener('click', function(event) {
				var trigger = resolveTrigger(event && event.target ? event.target : null);
				if (!trigger) { return; }
				event.preventDefault();
				if (typeof event.stopImmediatePropagation === 'function') {
					event.stopImmediatePropagation();
				} else {
					event.stopPropagation();
				}
				openFromTrigger(trigger);
			}, true);

			if (closeBtn) {
				closeBtn.addEventListener('click', function(event) {
					event.preventDefault();
					closeOverlay();
				});
			}
			if (prevBtn) {
				prevBtn.addEventListener('click', function(event) {
					event.preventDefault();
					event.stopPropagation();
					if (!enablePrevNextKeyboard || activeIndex < 0) { return; }
					openByIndex(activeIndex - 1);
				});
			}
			if (nextBtn) {
				nextBtn.addEventListener('click', function(event) {
					event.preventDefault();
					event.stopPropagation();
					if (!enablePrevNextKeyboard || activeIndex < 0) { return; }
					openByIndex(activeIndex + 1);
				});
			}
			overlay.addEventListener('click', function(event) {
				if (event.target === overlay) {
					closeOverlay();
				}
			});
			overlay.addEventListener('click', function(event) {
				if (!enableTapSideNavigation || overlay.getAttribute('aria-hidden') !== 'false' || activeIndex < 0) {
					return;
				}
				if (event.target === overlay) {
					return;
				}
				if (event.target && event.target.closest && event.target.closest('.um-mltg-lightbox-close,.um-mltg-lightbox-prev,.um-mltg-lightbox-next,.um-mltg-lightbox-slideshow-toggle,.um-mltg-lightbox-edit-link,.um-mltg-lightbox-tag-tools')) {
					return;
				}
				// Tap-side navigation should only react to clicks on the actual image,
				// not arbitrary background/container clicks.
				if (!image || event.target !== image) {
					return;
				}
				var bounds = image && image.getBoundingClientRect ? image.getBoundingClientRect() : null;
				if (!bounds || !isFinite(bounds.left) || !isFinite(bounds.width) || bounds.width <= 0) {
					return;
				}
				var clientX = event.clientX;
				if (!isFinite(clientX) || clientX < bounds.left || clientX > bounds.right) {
					return;
				}
				event.preventDefault();
				event.stopPropagation();
				if (clientX < (bounds.left + (bounds.width / 2))) {
					openByIndex(activeIndex - 1);
				} else {
					openByIndex(activeIndex + 1);
				}
			}, true);
			overlay.addEventListener('touchstart', function(event) {
				if (!enableSwipeNavigation || overlay.getAttribute('aria-hidden') !== 'false') {
					swipeTracking = false;
					return;
				}
				var touch = event.touches && event.touches.length ? event.touches[0] : null;
				if (!touch) {
					swipeTracking = false;
					return;
				}
				swipeStartX = touch.clientX;
				swipeStartY = touch.clientY;
				swipeTracking = true;
			});
			overlay.addEventListener('touchend', function(event) {
				if (!swipeTracking) {
					return;
				}
				swipeTracking = false;
				var touch = event.changedTouches && event.changedTouches.length ? event.changedTouches[0] : null;
				if (!touch) {
					return;
				}
				handleSwipeNavigation(touch.clientX - swipeStartX, touch.clientY - swipeStartY);
			});
			overlay.addEventListener('touchcancel', function() {
				swipeTracking = false;
			});
			document.addEventListener('keydown', function(event) {
				if (event.key === 'Escape' && overlay.getAttribute('aria-hidden') === 'false') {
					closeOverlay();
					return;
				}
				if (!enablePrevNextKeyboard || overlay.getAttribute('aria-hidden') !== 'false' || activeIndex < 0) {
					return;
				}
				if (event.key === 'ArrowLeft') {
					event.preventDefault();
					openByIndex(activeIndex - 1);
				} else if (event.key === 'ArrowRight') {
					event.preventDefault();
					openByIndex(activeIndex + 1);
				}
			});
			var attachmentIdFromUrl = readLightboxAttachmentIdFromUrl();
			if (attachmentIdFromUrl > 0 && window.__umMltgDeepLinkConsumed !== attachmentIdFromUrl) {
				var deepLinkTrigger = findTriggerByAttachmentId(attachmentIdFromUrl);
				if (deepLinkTrigger) {
					openFromTrigger(deepLinkTrigger);
					window.__umMltgDeepLinkConsumed = attachmentIdFromUrl;
				}
			}
		})();
		</script>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Resolve default gallery settings from add-on options.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<string,mixed>
	 */
	public static function get_media_library_tag_gallery_defaults(array $settings = []): array {
		if (empty($settings)) {
			$settings = User_Manager_Core::get_settings();
		}

		$defaults = [
			'columnsDesktop' => 4,
			'columnsDesktopLt50' => 4,
			'columnsDesktopLt25' => 4,
			'columnsDesktopLt10' => 4,
			'disableCssCropUnderTotal' => 0,
			'accentColor' => '#ffffff',
			'columnsMobile' => 2,
			'sortOrder' => 'date_desc',
			'fileSize' => 'thumbnail',
			'style' => 'uniform_grid',
			'pageLimit' => 0,
			'linkTo' => 'lightbox',
			'albumDescriptionPosition' => 'none',
			'featuredImageSeparateColumn' => false,
			'hideFeaturedImageIfNoDescriptionOrBullets' => false,
			'descriptionDisplay' => 'none',
			'descriptionValue' => 'caption',
			'lightboxPrevNextKeyboard' => true,
			'lightboxSwipeNavigation' => false,
			'lightboxSlideshowButton' => false,
			'lightboxSlideshowSeconds' => 3.0,
			'lightboxSlideshowTransition' => 'none',
			'lightboxModalBackgroundColor' => '#000000',
			'lightboxModalTextColor' => '#ffffff',
			'simpleLightboxThumbnailClick' => true,
			'hideFeaturedImageDuplicateInTaggedImages' => true,
			'featuredImageMaxWidthPx' => 360,
			'inlineStylesForLiTagsIf10PlusBulletsBeingDisplayed' => '',
		];

		if (isset($settings['media_library_tag_gallery_columns_desktop'])) {
			$defaults['columnsDesktop'] = max(1, min(8, absint($settings['media_library_tag_gallery_columns_desktop'])));
		}
		if (isset($settings['media_library_tag_gallery_columns_desktop_lt_50'])) {
			$defaults['columnsDesktopLt50'] = max(1, min(8, absint($settings['media_library_tag_gallery_columns_desktop_lt_50'])));
		}
		if (isset($settings['media_library_tag_gallery_columns_desktop_lt_25'])) {
			$defaults['columnsDesktopLt25'] = max(1, min(8, absint($settings['media_library_tag_gallery_columns_desktop_lt_25'])));
		}
		if (isset($settings['media_library_tag_gallery_columns_desktop_lt_10'])) {
			$defaults['columnsDesktopLt10'] = max(1, min(8, absint($settings['media_library_tag_gallery_columns_desktop_lt_10'])));
		}
		if (isset($settings['media_library_tag_gallery_disable_css_crop_under_total'])) {
			$defaults['disableCssCropUnderTotal'] = max(0, absint($settings['media_library_tag_gallery_disable_css_crop_under_total']));
		}
		if (!empty($settings['media_library_tag_gallery_accent_color']) && is_string($settings['media_library_tag_gallery_accent_color'])) {
			$accent_color = sanitize_hex_color($settings['media_library_tag_gallery_accent_color']);
			if (is_string($accent_color) && $accent_color !== '') {
				$defaults['accentColor'] = $accent_color;
			}
		}
		$defaults['columnsDesktopLt50'] = max(1, min(8, (int) $defaults['columnsDesktopLt50']));
		$defaults['columnsDesktopLt25'] = max(1, min(8, (int) $defaults['columnsDesktopLt25']));
		$defaults['columnsDesktopLt10'] = max(1, min(8, (int) $defaults['columnsDesktopLt10']));
		if ($defaults['columnsDesktopLt50'] <= 0) {
			$defaults['columnsDesktopLt50'] = (int) $defaults['columnsDesktop'];
		}
		if ($defaults['columnsDesktopLt25'] <= 0) {
			$defaults['columnsDesktopLt25'] = (int) $defaults['columnsDesktopLt50'];
		}
		if ($defaults['columnsDesktopLt10'] <= 0) {
			$defaults['columnsDesktopLt10'] = (int) $defaults['columnsDesktopLt25'];
		}
		if (isset($settings['media_library_tag_gallery_columns_mobile'])) {
			$defaults['columnsMobile'] = max(1, min(4, absint($settings['media_library_tag_gallery_columns_mobile'])));
		}
		if (!empty($settings['media_library_tag_gallery_sort_order'])) {
			$defaults['sortOrder'] = sanitize_key((string) $settings['media_library_tag_gallery_sort_order']);
		}
		if (!empty($settings['media_library_tag_gallery_file_size'])) {
			$defaults['fileSize'] = sanitize_key((string) $settings['media_library_tag_gallery_file_size']);
		}
		if (!empty($settings['media_library_tag_gallery_style'])) {
			$defaults['style'] = sanitize_key((string) $settings['media_library_tag_gallery_style']);
		}
		if (isset($settings['media_library_tag_gallery_page_limit'])) {
			$defaults['pageLimit'] = max(0, absint($settings['media_library_tag_gallery_page_limit']));
		}
		if (!empty($settings['media_library_tag_gallery_link_to'])) {
			$defaults['linkTo'] = sanitize_key((string) $settings['media_library_tag_gallery_link_to']);
		}
		if (in_array((string) $defaults['linkTo'], ['media_permalink', 'media_file', 'file', 'open_image'], true)) {
			$defaults['linkTo'] = 'image';
		} elseif (in_array((string) $defaults['linkTo'], ['new_window', 'open_image_new_window'], true)) {
			$defaults['linkTo'] = 'image_new_window';
		}
		if (!empty($settings['media_library_tag_gallery_album_description_position'])) {
			$defaults['albumDescriptionPosition'] = sanitize_key((string) $settings['media_library_tag_gallery_album_description_position']);
		}
		$defaults['featuredImageSeparateColumn'] = isset($settings['media_library_tag_gallery_featured_image_separate_column'])
			? ($settings['media_library_tag_gallery_featured_image_separate_column'] === true || $settings['media_library_tag_gallery_featured_image_separate_column'] === '1')
			: (bool) ($defaults['featuredImageSeparateColumn'] ?? false);
		$defaults['hideFeaturedImageIfNoDescriptionOrBullets'] = isset($settings['media_library_tag_gallery_hide_featured_image_if_no_description_or_bullets'])
			? ($settings['media_library_tag_gallery_hide_featured_image_if_no_description_or_bullets'] === true || $settings['media_library_tag_gallery_hide_featured_image_if_no_description_or_bullets'] === '1')
			: (bool) ($defaults['hideFeaturedImageIfNoDescriptionOrBullets'] ?? false);
		if (!empty($settings['media_library_tag_gallery_description_display'])) {
			$defaults['descriptionDisplay'] = sanitize_key((string) $settings['media_library_tag_gallery_description_display']);
		}
		if (!empty($settings['media_library_tag_gallery_description_value'])) {
			$defaults['descriptionValue'] = sanitize_key((string) $settings['media_library_tag_gallery_description_value']);
		}
		$defaults['lightboxPrevNextKeyboard'] = isset($settings['media_library_tag_gallery_lightbox_prev_next_keyboard'])
			? $settings['media_library_tag_gallery_lightbox_prev_next_keyboard'] === true || $settings['media_library_tag_gallery_lightbox_prev_next_keyboard'] === '1'
			: (bool) $defaults['lightboxPrevNextKeyboard'];
		$defaults['lightboxSwipeNavigation'] = isset($settings['media_library_tag_gallery_lightbox_swipe_navigation'])
			? $settings['media_library_tag_gallery_lightbox_swipe_navigation'] === true || $settings['media_library_tag_gallery_lightbox_swipe_navigation'] === '1'
			: (bool) $defaults['lightboxSwipeNavigation'];
		$defaults['lightboxTapSideNavigation'] = isset($settings['media_library_tag_gallery_lightbox_tap_side_navigation'])
			? $settings['media_library_tag_gallery_lightbox_tap_side_navigation'] === true || $settings['media_library_tag_gallery_lightbox_tap_side_navigation'] === '1'
			: (bool) ($defaults['lightboxTapSideNavigation'] ?? false);
		$defaults['lightboxSlideshowButton'] = isset($settings['media_library_tag_gallery_lightbox_slideshow_button'])
			? $settings['media_library_tag_gallery_lightbox_slideshow_button'] === true || $settings['media_library_tag_gallery_lightbox_slideshow_button'] === '1'
			: (bool) $defaults['lightboxSlideshowButton'];
		if (isset($settings['media_library_tag_gallery_lightbox_slideshow_seconds_per_photo'])) {
			$defaults['lightboxSlideshowSeconds'] = max(1.0, min(60.0, (float) $settings['media_library_tag_gallery_lightbox_slideshow_seconds_per_photo']));
		}
		if (isset($settings['media_library_tag_gallery_lightbox_slideshow_transition'])) {
			$defaults['lightboxSlideshowTransition'] = sanitize_key((string) $settings['media_library_tag_gallery_lightbox_slideshow_transition']);
		}
		if (!empty($settings['media_library_tag_gallery_lightbox_modal_background_color']) && is_string($settings['media_library_tag_gallery_lightbox_modal_background_color'])) {
			$modal_background_color = sanitize_hex_color($settings['media_library_tag_gallery_lightbox_modal_background_color']);
			if (is_string($modal_background_color) && $modal_background_color !== '') {
				$defaults['lightboxModalBackgroundColor'] = $modal_background_color;
			}
		}
		if (!empty($settings['media_library_tag_gallery_lightbox_modal_text_color']) && is_string($settings['media_library_tag_gallery_lightbox_modal_text_color'])) {
			$modal_text_color = sanitize_hex_color($settings['media_library_tag_gallery_lightbox_modal_text_color']);
			if (is_string($modal_text_color) && $modal_text_color !== '') {
				$defaults['lightboxModalTextColor'] = $modal_text_color;
			}
		}
		$defaults['simpleLightboxThumbnailClick'] = isset($settings['media_library_tag_gallery_simple_lightbox_thumbnail_click'])
			? $settings['media_library_tag_gallery_simple_lightbox_thumbnail_click'] === true || $settings['media_library_tag_gallery_simple_lightbox_thumbnail_click'] === '1'
			: (bool) $defaults['simpleLightboxThumbnailClick'];
		$defaults['hideFeaturedImageDuplicateInTaggedImages'] = isset($settings['media_library_tag_gallery_hide_featured_image_duplicate_in_tagged_images'])
			? $settings['media_library_tag_gallery_hide_featured_image_duplicate_in_tagged_images'] === true || $settings['media_library_tag_gallery_hide_featured_image_duplicate_in_tagged_images'] === '1'
			: (bool) ($defaults['hideFeaturedImageDuplicateInTaggedImages'] ?? true);
		if (isset($settings['media_library_tag_gallery_10plus_bullets_li_inline_styles'])) {
			$defaults['inlineStylesForLiTagsIf10PlusBulletsBeingDisplayed'] = sanitize_text_field((string) $settings['media_library_tag_gallery_10plus_bullets_li_inline_styles']);
		}
		if (isset($settings['media_library_tag_gallery_featured_image_max_width_px'])) {
			$defaults['featuredImageMaxWidthPx'] = max(120, min(1600, absint($settings['media_library_tag_gallery_featured_image_max_width_px'])));
		}
		$defaults['lightboxModalBackgroundColor'] = sanitize_hex_color((string) ($defaults['lightboxModalBackgroundColor'] ?? '#000000')) ?: '#000000';
		$defaults['lightboxModalTextColor'] = sanitize_hex_color((string) ($defaults['lightboxModalTextColor'] ?? '#ffffff')) ?: '#ffffff';
		$valid_lightbox_slideshow_transitions = ['none', 'crossfade', 'slide_left'];
		if (!in_array((string) $defaults['lightboxSlideshowTransition'], $valid_lightbox_slideshow_transitions, true)) {
			$defaults['lightboxSlideshowTransition'] = 'none';
		}
		$valid_styles = array_keys(self::get_media_library_gallery_style_options());
		if (!in_array((string) $defaults['style'], $valid_styles, true)) {
			$defaults['style'] = 'uniform_grid';
		}
		$valid_link_to = array_keys(self::get_media_library_gallery_link_to_options());
		if (!in_array((string) $defaults['linkTo'], $valid_link_to, true)) {
			$defaults['linkTo'] = 'lightbox';
		}
		$valid_album_description_positions = array_keys(self::get_media_library_gallery_album_description_position_options());
		if (!in_array((string) $defaults['albumDescriptionPosition'], $valid_album_description_positions, true)) {
			$defaults['albumDescriptionPosition'] = 'none';
		}
		$valid_description_display = array_keys(self::get_media_library_gallery_description_display_options());
		if (!in_array((string) $defaults['descriptionDisplay'], $valid_description_display, true)) {
			$defaults['descriptionDisplay'] = 'none';
		}
		$valid_description_values = array_keys(self::get_media_library_gallery_description_value_options());
		if (!in_array((string) $defaults['descriptionValue'], $valid_description_values, true)) {
			$defaults['descriptionValue'] = 'caption';
		}

		return $defaults;
	}

	/**
	 * @return array<string,string>
	 */
	public static function get_available_image_sizes_for_media_gallery(): array {
		$sizes = [
			'thumbnail' => __('Thumbnail', 'user-manager'),
			'medium' => __('Medium', 'user-manager'),
			'large' => __('Large', 'user-manager'),
			'full' => __('Full Size', 'user-manager'),
		];

		if (function_exists('get_intermediate_image_sizes')) {
			$registered = get_intermediate_image_sizes();
			if (is_array($registered)) {
				foreach ($registered as $size_name) {
					$size_name = sanitize_key((string) $size_name);
					if ($size_name === '' || isset($sizes[$size_name])) {
						continue;
					}
					$sizes[$size_name] = ucwords(str_replace(['-', '_'], ' ', $size_name));
				}
			}
		}

		return $sizes;
	}

	/**
	 * @return array<string,string>
	 */
	public static function get_media_library_gallery_description_display_options(): array {
		return [
			'none' => __('none', 'user-manager'),
			'grid' => __('centered under photo', 'user-manager'),
			'lightbox' => __('lightbox under photo', 'user-manager'),
			'both' => __('both', 'user-manager'),
		];
	}

	/**
	 * @return array<string,string>
	 */
	public static function get_media_library_gallery_album_description_position_options(): array {
		return [
			'none' => __('none', 'user-manager'),
			'above' => __('above gallery', 'user-manager'),
			'below' => __('below gallery', 'user-manager'),
		];
	}

	/**
	 * @return array<string,string>
	 */
	public static function get_media_library_gallery_description_value_options(): array {
		return [
			'caption' => __('Caption', 'user-manager'),
			'title' => __('Title', 'user-manager'),
			'description' => __('Description', 'user-manager'),
			'alt' => __('Alt text', 'user-manager'),
			'filename' => __('Filename', 'user-manager'),
			'slug' => __('Slug', 'user-manager'),
			'date' => __('Date', 'user-manager'),
		];
	}

	/**
	 * @return array<string,string>
	 */
	public static function get_media_library_gallery_lightbox_transition_options(): array {
		return [
			'none' => __('None', 'user-manager'),
			'crossfade' => __('Crossfade', 'user-manager'),
			'slide_left' => __('Slide to Left', 'user-manager'),
		];
	}

	/**
	 * @return array<string,string>
	 */
	public static function get_media_library_gallery_link_to_options(): array {
		return [
			'none' => __('None', 'user-manager'),
			'image' => __('Open Image', 'user-manager'),
			'image_new_window' => __('Open Image in New Window', 'user-manager'),
			'lightbox' => __('Open Image in Lightbox', 'user-manager'),
		];
	}

	/**
	 * Resolve description text value for a specific attachment.
	 */
	private static function get_media_library_gallery_description_text(int $attachment_id, string $description_value): string {
		if ($attachment_id <= 0) {
			return '';
		}

		switch ($description_value) {
			case 'title':
				return trim((string) get_the_title($attachment_id));
			case 'description':
				$attachment = get_post($attachment_id);
				return $attachment instanceof WP_Post ? trim((string) $attachment->post_content) : '';
			case 'alt':
				return trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
			case 'filename':
				$file = get_post_meta($attachment_id, '_wp_attached_file', true);
				if (!is_string($file) || $file === '') {
					return '';
				}
				return trim((string) wp_basename($file));
			case 'slug':
				$attachment = get_post($attachment_id);
				return $attachment instanceof WP_Post ? trim((string) $attachment->post_name) : '';
			case 'date':
				$date_format = get_option('date_format');
				if (!is_string($date_format) || $date_format === '') {
					$date_format = 'F j, Y';
				}
				return trim((string) get_the_date($date_format, $attachment_id));
			case 'caption':
			default:
				return trim((string) wp_get_attachment_caption($attachment_id));
		}
	}

	/**
	 * @return array<string,string>
	 */
	public static function get_media_library_gallery_style_options(): array {
		$options = [
			'carousel_slider' => __('Carousel / Slider Gallery', 'user-manager'),
			'fullscreen_lightbox_grid' => __('Fullscreen Lightbox Grid', 'user-manager'),
			'horizontal_scroll' => __('Horizontal Scroll Gallery', 'user-manager'),
			'infinite_scroll' => __('Infinite Scroll Gallery', 'user-manager'),
			'justified_rows' => __('Justified Row Layout', 'user-manager'),
			'masonry_pinterest' => __('Masonry / Pinterest Layout', 'user-manager'),
			'mosaic_grid' => __('Mosaic Grid (Irregular Tiles)', 'user-manager'),
			'mosaic_grid_taller' => __('Mosaic Grid (Taller Tiles)', 'user-manager'),
			'perspective_3d' => __('3D Perspective Gallery', 'user-manager'),
			'polaroid_scrapbook' => __('Polaroid / Scrapbook Layout', 'user-manager'),
			'split_screen_feature' => __('Split Screen Feature Gallery', 'user-manager'),
			'timeline_story' => __('Timeline / Story Gallery', 'user-manager'),
			'uniform_grid' => __('Uniform Grid (Classic Gallery)', 'user-manager'),
		];
		asort($options, SORT_NATURAL | SORT_FLAG_CASE);
		return $options;
	}

	/**
	 * Resolve selected Media Library sort value from request.
	 */
	private static function get_requested_media_library_sort_value(): string {
		$value = '';
		if (isset($_REQUEST['query']) && is_array($_REQUEST['query']) && isset($_REQUEST['query']['um_media_library_sort'])) {
			$value = sanitize_key((string) wp_unslash($_REQUEST['query']['um_media_library_sort']));
		}
		if ($value === '' && isset($_REQUEST['um_media_library_sort'])) {
			$value = sanitize_key((string) wp_unslash($_REQUEST['um_media_library_sort']));
		}
		if ($value === '') {
			$referer = wp_get_referer();
			if (!$referer && isset($_SERVER['HTTP_REFERER'])) {
				$referer = esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER']));
			}
			if ($referer) {
				$parts = wp_parse_url($referer);
				if (is_array($parts) && !empty($parts['query'])) {
					parse_str((string) $parts['query'], $query_args);
					if (is_array($query_args) && isset($query_args['um_media_library_sort'])) {
						$value = sanitize_key((string) $query_args['um_media_library_sort']);
					}
				}
			}
		}
		$options = self::get_media_library_media_admin_sort_options();
		return isset($options[$value]) ? $value : '';
	}

	/**
	 * Resolve selected Media Library sort value from a query.
	 */
	private static function get_requested_media_library_sort_value_from_query(WP_Query $query): string {
		$raw = $query->get('um_media_library_sort');
		if (!is_string($raw) || $raw === '') {
			$raw = self::get_requested_media_library_sort_value();
		}
		$value = sanitize_key((string) $raw);
		$options = self::get_media_library_media_admin_sort_options();
		return isset($options[$value]) ? $value : '';
	}

	/**
	 * @return array<string,string>
	 */
	private static function get_media_library_media_admin_sort_options(): array {
		return [
			'' => __('Sort by Upload Date (Newest first)', 'user-manager'),
			'lightbox_views_desc' => __('Sort by Views (Highest first)', 'user-manager'),
			'lightbox_views_asc' => __('Sort by Views (Lowest first)', 'user-manager'),
		];
	}

	/**
	 * @return array<int,array{value:string,label:string}>
	 */
	private static function get_media_library_media_admin_sort_options_for_js(): array {
		$options = self::get_media_library_media_admin_sort_options();
		$rows = [];
		foreach ($options as $value => $label) {
			$rows[] = [
				'value' => (string) $value,
				'label' => (string) $label,
			];
		}
		return $rows;
	}

	/**
	 * Term meta key used to store album tag view totals.
	 */
	private static function media_library_album_tag_views_meta_key(): string {
		return 'um_media_album_tag_views';
	}

	/**
	 * Resolve base term-meta key for one album-tag period counter.
	 */
	private static function media_library_album_tag_views_period_meta_key(string $period): string {
		$period = sanitize_key($period);
		if (!in_array($period, ['year', 'month', 'week', 'day'], true)) {
			return '';
		}
		return 'um_media_album_tag_views_' . $period;
	}

	/**
	 * Resolve storage key for the current album-tag period bucket.
	 */
	private static function media_library_album_tag_period_current_key(string $period): string {
		switch ($period) {
			case 'year':
				return gmdate('Y');
			case 'month':
				return gmdate('Y-m');
			case 'week':
				$iso_year = (string) gmdate('o');
				$iso_week = str_pad((string) gmdate('W'), 2, '0', STR_PAD_LEFT);
				return $iso_year . '-W' . $iso_week;
			case 'day':
				return gmdate('Y-m-d');
			default:
				return '';
		}
	}

	/**
	 * Read normalized album-tag view count for one term.
	 */
	private static function get_media_library_album_tag_view_count(int $term_id): int {
		if ($term_id <= 0) {
			return 0;
		}
		$value = get_term_meta($term_id, self::media_library_album_tag_views_meta_key(), true);
		return max(0, absint($value));
	}

	/**
	 * Read current period counters for album tag views (year/month/week/day).
	 *
	 * @return array{year:int,month:int,week:int,day:int}
	 */
	private static function get_media_library_album_tag_period_view_counts(int $term_id): array {
		$counts = [];
		foreach (['year', 'month', 'week', 'day'] as $period) {
			$current_key = self::media_library_album_tag_period_current_key($period);
			$meta_key = self::media_library_album_tag_views_period_meta_key($period);
			if ($current_key === '' || $meta_key === '') {
				continue;
			}
			$stored_key = (string) get_term_meta($term_id, $meta_key . '_key', true);
			$stored_count = max(0, absint(get_term_meta($term_id, $meta_key . '_count', true)));
			$counts[$period] = ($stored_key === $current_key) ? $stored_count : 0;
		}
		return [
			'year' => (int) ($counts['year'] ?? 0),
			'month' => (int) ($counts['month'] ?? 0),
			'week' => (int) ($counts['week'] ?? 0),
			'day' => (int) ($counts['day'] ?? 0),
		];
	}

	/**
	 * Increment album tag views for one or more slugs.
	 *
	 * @param array<int,string> $tag_slugs
	 */
	private static function increment_media_library_album_tag_view_counts_for_slugs(array $tag_slugs): void {
		$tag_slugs = array_values(array_unique(array_filter(array_map('sanitize_title', $tag_slugs))));
		if (empty($tag_slugs)) {
			return;
		}
		$terms = get_terms([
			'taxonomy' => self::media_library_tags_taxonomy(),
			'hide_empty' => false,
			'slug' => $tag_slugs,
		]);
		if (!is_array($terms) || is_wp_error($terms)) {
			return;
		}
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			self::increment_media_library_album_tag_view_counts((int) $term->term_id);
		}
	}

	/**
	 * Increment total + period album-tag view counters for one term.
	 *
	 * @return array{total:int,year:int,month:int,week:int,day:int}
	 */
	private static function increment_media_library_album_tag_view_counts(int $term_id): array {
		$total = self::get_media_library_album_tag_view_count($term_id) + 1;
		update_term_meta($term_id, self::media_library_album_tag_views_meta_key(), $total);
		$period_counts = self::increment_media_library_album_tag_view_period_counts($term_id);
		return [
			'total' => $total,
			'year' => (int) ($period_counts['year'] ?? 0),
			'month' => (int) ($period_counts['month'] ?? 0),
			'week' => (int) ($period_counts['week'] ?? 0),
			'day' => (int) ($period_counts['day'] ?? 0),
		];
	}

	/**
	 * Increment period counters for album-tag views (year/month/week/day).
	 *
	 * @return array{year:int,month:int,week:int,day:int}
	 */
	private static function increment_media_library_album_tag_view_period_counts(int $term_id): array {
		$counts = [];
		foreach (['year', 'month', 'week', 'day'] as $period) {
			$current_key = self::media_library_album_tag_period_current_key($period);
			$meta_key = self::media_library_album_tag_views_period_meta_key($period);
			if ($current_key === '' || $meta_key === '') {
				continue;
			}
			$stored_key = (string) get_term_meta($term_id, $meta_key . '_key', true);
			$stored_count = max(0, absint(get_term_meta($term_id, $meta_key . '_count', true)));
			$next_count = ($stored_key === $current_key) ? ($stored_count + 1) : 1;
			update_term_meta($term_id, $meta_key . '_key', $current_key);
			update_term_meta($term_id, $meta_key . '_count', $next_count);
			$counts[$period] = $next_count;
		}
		return [
			'year' => (int) ($counts['year'] ?? 0),
			'month' => (int) ($counts['month'] ?? 0),
			'week' => (int) ($counts['week'] ?? 0),
			'day' => (int) ($counts['day'] ?? 0),
		];
	}

	/**
	 * Build rows for Tag Reports > Most Viewed Images.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private static function get_media_library_most_viewed_images_for_report(int $limit = 200): array {
		$limit = max(1, min(1000, $limit));
		$attachments = get_posts([
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => $limit,
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'meta_key' => self::media_library_lightbox_views_meta_key(),
			'meta_query' => [
				[
					'key' => self::media_library_lightbox_views_meta_key(),
					'value' => 0,
					'compare' => '>',
					'type' => 'NUMERIC',
				],
			],
			'no_found_rows' => true,
		]);
		if (!is_array($attachments) || empty($attachments)) {
			return [];
		}
		$rows = [];
		foreach ($attachments as $attachment) {
			if (!($attachment instanceof WP_Post)) {
				continue;
			}
			$attachment_id = (int) $attachment->ID;
			$total = self::get_media_library_lightbox_view_count($attachment_id);
			if ($total <= 0) {
				continue;
			}
			$period_counts = self::get_media_library_lightbox_period_view_counts($attachment_id);
			$rows[] = [
				'thumbHtml' => (string) wp_get_attachment_image($attachment_id, [60, 60], true),
				'title' => (string) get_the_title($attachment_id),
				'editUrl' => (string) get_edit_post_link($attachment_id, ''),
				'total' => $total,
				'year' => (int) ($period_counts['year'] ?? 0),
				'month' => (int) ($period_counts['month'] ?? 0),
				'week' => (int) ($period_counts['week'] ?? 0),
				'day' => (int) ($period_counts['day'] ?? 0),
			];
		}
		return $rows;
	}

	/**
	 * Build rows for Tag Reports > Most Viewed Album Tags.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private static function get_media_library_most_viewed_album_tags_for_report(int $limit = 200): array {
		$limit = max(1, min(1000, $limit));
		$terms = get_terms([
			'taxonomy' => self::media_library_tags_taxonomy(),
			'hide_empty' => false,
		]);
		if (!is_array($terms) || is_wp_error($terms)) {
			return [];
		}
		$rows = [];
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$term_id = (int) $term->term_id;
			$total = self::get_media_library_album_tag_view_count($term_id);
			if ($total <= 0) {
				continue;
			}
			$period_counts = self::get_media_library_album_tag_period_view_counts($term_id);
			$rows[] = [
				'name' => (string) $term->name,
				'slug' => (string) $term->slug,
				'editUrl' => (string) add_query_arg(
					[
						'taxonomy' => self::media_library_tags_taxonomy(),
						'tag_ID' => $term_id,
						'post_type' => 'attachment',
					],
					admin_url('term.php')
				),
				'total' => $total,
				'year' => (int) ($period_counts['year'] ?? 0),
				'month' => (int) ($period_counts['month'] ?? 0),
				'week' => (int) ($period_counts['week'] ?? 0),
				'day' => (int) ($period_counts['day'] ?? 0),
			];
		}
		usort($rows, static function (array $a, array $b): int {
			$total_compare = ((int) ($b['total'] ?? 0)) <=> ((int) ($a['total'] ?? 0));
			if ($total_compare !== 0) {
				return $total_compare;
			}
			$year_compare = ((int) ($b['year'] ?? 0)) <=> ((int) ($a['year'] ?? 0));
			if ($year_compare !== 0) {
				return $year_compare;
			}
			$month_compare = ((int) ($b['month'] ?? 0)) <=> ((int) ($a['month'] ?? 0));
			if ($month_compare !== 0) {
				return $month_compare;
			}
			$week_compare = ((int) ($b['week'] ?? 0)) <=> ((int) ($a['week'] ?? 0));
			if ($week_compare !== 0) {
				return $week_compare;
			}
			$day_compare = ((int) ($b['day'] ?? 0)) <=> ((int) ($a['day'] ?? 0));
			if ($day_compare !== 0) {
				return $day_compare;
			}
			return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
		});
		return array_slice($rows, 0, $limit);
	}

	/**
	 * Post meta key used to store lightbox view totals.
	 */
	private static function media_library_lightbox_views_meta_key(): string {
		return 'um_media_lightbox_views';
	}

	/**
	 * Read a normalized lightbox view count for an attachment.
	 */
	private static function get_media_library_lightbox_view_count(int $attachment_id): int {
		if ($attachment_id <= 0) {
			return 0;
		}
		$value = get_post_meta($attachment_id, self::media_library_lightbox_views_meta_key(), true);
		return max(0, absint($value));
	}

	/**
	 * Increment total + current year/month/week/day lightbox counters.
	 *
	 * @return array{total:int,year:int,month:int,week:int,day:int}
	 */
	private static function increment_media_library_lightbox_view_counts(int $attachment_id): array {
		$total = self::get_media_library_lightbox_view_count($attachment_id) + 1;
		update_post_meta($attachment_id, self::media_library_lightbox_views_meta_key(), $total);
		$period_counts = self::increment_media_library_lightbox_view_period_counts($attachment_id);
		return [
			'total' => $total,
			'year' => (int) ($period_counts['year'] ?? 0),
			'month' => (int) ($period_counts['month'] ?? 0),
			'week' => (int) ($period_counts['week'] ?? 0),
			'day' => (int) ($period_counts['day'] ?? 0),
		];
	}

	/**
	 * Read current period counters for year/month/week/day.
	 *
	 * @return array{year:int,month:int,week:int,day:int}
	 */
	private static function get_media_library_lightbox_period_view_counts(int $attachment_id): array {
		$counts = [];
		foreach (['year', 'month', 'week', 'day'] as $period) {
			$current_key = self::media_library_lightbox_period_current_key($period);
			$meta_key = self::media_library_lightbox_views_period_meta_key($period);
			if ($current_key === '' || $meta_key === '') {
				continue;
			}
			$stored_key = (string) get_post_meta($attachment_id, $meta_key . '_key', true);
			$stored_count = max(0, absint(get_post_meta($attachment_id, $meta_key . '_count', true)));
			$counts[$period] = ($stored_key === $current_key) ? $stored_count : 0;
		}
		return [
			'year' => (int) ($counts['year'] ?? 0),
			'month' => (int) ($counts['month'] ?? 0),
			'week' => (int) ($counts['week'] ?? 0),
			'day' => (int) ($counts['day'] ?? 0),
		];
	}

	/**
	 * Increment period counters for current year/month/week/day.
	 *
	 * @return array{year:int,month:int,week:int,day:int}
	 */
	private static function increment_media_library_lightbox_view_period_counts(int $attachment_id): array {
		$counts = [];
		foreach (['year', 'month', 'week', 'day'] as $period) {
			$current_key = self::media_library_lightbox_period_current_key($period);
			$meta_key = self::media_library_lightbox_views_period_meta_key($period);
			if ($current_key === '' || $meta_key === '') {
				continue;
			}
			$stored_key = (string) get_post_meta($attachment_id, $meta_key . '_key', true);
			$stored_count = max(0, absint(get_post_meta($attachment_id, $meta_key . '_count', true)));
			$next_count = ($stored_key === $current_key) ? ($stored_count + 1) : 1;
			update_post_meta($attachment_id, $meta_key . '_key', $current_key);
			update_post_meta($attachment_id, $meta_key . '_count', $next_count);
			$counts[$period] = $next_count;
		}
		return [
			'year' => (int) ($counts['year'] ?? 0),
			'month' => (int) ($counts['month'] ?? 0),
			'week' => (int) ($counts['week'] ?? 0),
			'day' => (int) ($counts['day'] ?? 0),
		];
	}

	/**
	 * Resolve storage key for the current period bucket.
	 */
	private static function media_library_lightbox_period_current_key(string $period): string {
		switch ($period) {
			case 'year':
				return gmdate('Y');
			case 'month':
				return gmdate('Y-m');
			case 'week':
				$iso_year = (string) gmdate('o');
				$iso_week = str_pad((string) gmdate('W'), 2, '0', STR_PAD_LEFT);
				return $iso_year . '-W' . $iso_week;
			case 'day':
				return gmdate('Y-m-d');
			default:
				return '';
		}
	}

	/**
	 * Resolve base post-meta key for a period counter.
	 */
	private static function media_library_lightbox_views_period_meta_key(string $period): string {
		$period = sanitize_key($period);
		if (!in_array($period, ['year', 'month', 'week', 'day'], true)) {
			return '';
		}
		return 'um_media_lightbox_views_' . $period;
	}

	/**
	 * Resolve selected Library Tag from request/query/referer.
	 */
	private static function get_requested_media_library_tag_filter_value(): string {
		$value = '';

		if (isset($_REQUEST['query']) && is_array($_REQUEST['query']) && isset($_REQUEST['query']['um_media_library_tag'])) {
			$value = sanitize_text_field((string) wp_unslash($_REQUEST['query']['um_media_library_tag']));
		}
		if ($value === '' && isset($_REQUEST['um_media_library_tag'])) {
			$value = sanitize_text_field((string) wp_unslash($_REQUEST['um_media_library_tag']));
		}
		if ($value === '') {
			$value = self::get_media_library_tag_filter_value_from_referer();
		}
		if ($value === '') {
			return '';
		}

		if (self::is_media_library_no_tags_filter_value($value)) {
			return self::get_media_library_no_tags_filter_value();
		}

		$slug = sanitize_title($value);
		return ($slug !== '' && self::is_valid_media_library_tag_filter_slug($slug)) ? $slug : '';
	}

	/**
	 * Read Library Tag from referer query string.
	 */
	private static function get_media_library_tag_filter_value_from_referer(): string {
		$referer = wp_get_referer();
		if (!$referer && isset($_SERVER['HTTP_REFERER'])) {
			$referer = esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER']));
		}
		if (!$referer) {
			return '';
		}

		$parts = wp_parse_url($referer);
		if (!is_array($parts) || empty($parts['query'])) {
			return '';
		}

		parse_str((string) $parts['query'], $query_args);
		if (!is_array($query_args) || empty($query_args['um_media_library_tag'])) {
			return '';
		}

		return sanitize_text_field((string) $query_args['um_media_library_tag']);
	}

	/**
	 * Special request value used to filter only attachments with no Library Tags.
	 */
	private static function get_media_library_no_tags_filter_value(): string {
		return '__um_no_tags__';
	}

	/**
	 * Resolve URL-based tag override for Media Library Tag Gallery block.
	 *
	 * Supported combinations:
	 * - single tag: ?tag=tag-one
	 * - AND logic: ?tag=tag-one+tag-two or ?tag-one+tag-two (allowAny mode)
	 * - OR logic:  ?tag=tag-one_tag-two or ?tag-one_tag-two (allowAny mode)
	 *
	 * @param bool $allow_any_parameter Whether any URL parameter key can map to tag slugs.
	 * @return array{mode:'none'|'all'|'single'|'and'|'or',slugs:array<int,string>,primarySlug:string}
	 */
	private static function resolve_media_library_gallery_url_tag_override(bool $allow_any_parameter = false): array {
		$none = [
			'mode' => 'none',
			'slugs' => [],
			'primarySlug' => '',
		];
		if (isset($_GET['tag'])) {
			$raw_tag_value = self::get_raw_query_string_value('tag');
			if ($raw_tag_value === null) {
				$raw_tag_value = (string) wp_unslash($_GET['tag']);
			}
			$parsed = self::parse_media_library_gallery_tag_expression($raw_tag_value);
			if ($parsed['mode'] !== 'none') {
				return $parsed;
			}
		}
		if (!$allow_any_parameter) {
			return $none;
		}

		$raw_keys = self::get_raw_query_string_keys();
		if (empty($raw_keys)) {
			return $none;
		}
		foreach ($raw_keys as $raw_key) {
			if (!is_string($raw_key) || $raw_key === '' || $raw_key === 'tag') {
				continue;
			}
			$parsed = self::parse_media_library_gallery_tag_expression($raw_key);
			if ($parsed['mode'] !== 'none') {
				return $parsed;
			}
		}

		return $none;
	}

	/**
	 * Resolve pipe-separated URL tag override groups for sequential gallery rendering.
	 *
	 * Example:
	 * - ?tag=gallery-one|gallery-two|gallery-three
	 *
	 * @param bool $allow_any_parameter Whether any URL parameter key can map to tag slugs.
	 * @return array<int,array{expression:string,title:string,description:string}>
	 */
	private static function resolve_media_library_gallery_pipe_tag_override_groups(bool $allow_any_parameter = false): array {
		$raw_expressions = [];
		if (isset($_GET['tag'])) {
			$raw_tag_value = self::get_raw_query_string_value('tag');
			if ($raw_tag_value === null) {
				$raw_tag_value = (string) wp_unslash($_GET['tag']);
			}
			$raw_expressions = self::split_media_library_gallery_pipe_expressions($raw_tag_value);
		}
		if (empty($raw_expressions) && $allow_any_parameter) {
			$raw_keys = self::get_raw_query_string_keys();
			foreach ($raw_keys as $raw_key) {
				if (!is_string($raw_key) || $raw_key === '' || $raw_key === 'tag') {
					continue;
				}
				$raw_expressions = self::split_media_library_gallery_pipe_expressions($raw_key);
				if (!empty($raw_expressions)) {
					break;
				}
			}
		}
		if (empty($raw_expressions)) {
			return [];
		}

		$groups = [];
		foreach ($raw_expressions as $raw_expression) {
			$parsed_expression = self::parse_media_library_gallery_tag_expression($raw_expression);
			if (empty($parsed_expression['mode']) || $parsed_expression['mode'] === 'none') {
				continue;
			}
			$tag_data = self::get_media_library_tag_description_data_for_tag_expression($parsed_expression);
			$title = trim((string) ($tag_data['name'] ?? ''));
			if ($title === '' && !empty($parsed_expression['slugs']) && is_array($parsed_expression['slugs'])) {
				$title = (string) $parsed_expression['slugs'][0];
			}
			$description = trim((string) ($tag_data['description'] ?? ''));
			$groups[] = [
				'expression' => (string) $raw_expression,
				'title' => $title,
				'description' => $description,
			];
		}
		return $groups;
	}

	/**
	 * Split a raw expression by pipe separators into sub-expressions.
	 *
	 * @return array<int,string>
	 */
	private static function split_media_library_gallery_pipe_expressions(string $raw_expression): array {
		$raw_expression = trim((string) $raw_expression);
		if ($raw_expression === '' || strpos($raw_expression, '|') === false) {
			return [];
		}
		$parts = preg_split('/\|+/', $raw_expression) ?: [];
		$parts = array_values(array_filter(array_map(
			static function ($part): string {
				return trim((string) $part);
			},
			$parts
		)));
		return $parts;
	}

	/**
	 * Parse a URL tag expression into single/AND/OR mode with validated slugs.
	 *
	 * @param bool $for_queries When true, allow token-based expansion for query matching.
	 * @return array{mode:'none'|'all'|'single'|'and'|'or',slugs:array<int,string>,primarySlug:string}
	 */
	private static function parse_media_library_gallery_tag_expression(string $raw_expression, bool $for_queries = false): array {
		$none = [
			'mode' => 'none',
			'slugs' => [],
			'primarySlug' => '',
		];
		$raw_expression = trim((string) $raw_expression);
		if ($raw_expression === '') {
			return $none;
		}

		$mode = 'single';
		$parts = [$raw_expression];
		if (strpos($raw_expression, '_') !== false) {
			$mode = 'or';
			$parts = preg_split('/_+/', $raw_expression) ?: [];
		} elseif (strpos($raw_expression, '+') !== false || preg_match('/\s+/', $raw_expression)) {
			$mode = 'and';
			$parts = preg_split('/(?:\+|\s)+/', $raw_expression) ?: [];
		}

		$normalized_parts = [];
		foreach ($parts as $part) {
			$slug = sanitize_title((string) $part);
			if ($slug !== '') {
				$normalized_parts[] = $slug;
			}
		}
		$normalized_parts = array_values(array_unique($normalized_parts));
		if (empty($normalized_parts)) {
			return $none;
		}
		if (count($normalized_parts) === 1 && $normalized_parts[0] === 'all') {
			return [
				'mode' => 'all',
				'slugs' => [],
				'primarySlug' => '',
			];
		}

		$valid_slugs = [];
		foreach ($normalized_parts as $candidate_slug) {
			if ($candidate_slug === 'all') {
				continue;
			}
			if (term_exists($candidate_slug, self::media_library_tags_taxonomy())) {
				$valid_slugs[] = $candidate_slug;
				continue;
			}
			if (!$for_queries) {
				continue;
			}
			$matched_slugs = self::get_media_library_filter_slugs_for_requested_slug($candidate_slug);
			if (empty($matched_slugs)) {
				continue;
			}
			foreach ($matched_slugs as $matched_slug) {
				$matched_slug = sanitize_title((string) $matched_slug);
				if ($matched_slug !== '') {
					$valid_slugs[] = $matched_slug;
				}
			}
		}
		$valid_slugs = array_values(array_unique($valid_slugs));
		if (empty($valid_slugs)) {
			return $none;
		}
		if (count($valid_slugs) === 1) {
			$mode = 'single';
		}

		return [
			'mode' => $mode,
			'slugs' => $valid_slugs,
			'primarySlug' => (string) $valid_slugs[0],
		];
	}

	/**
	 * Read raw query-string value for a key while preserving "+" characters.
	 */
	private static function get_raw_query_string_value(string $target_key): ?string {
		$query_string = isset($_SERVER['QUERY_STRING']) ? (string) wp_unslash($_SERVER['QUERY_STRING']) : '';
		if ($query_string === '') {
			return null;
		}

		foreach (explode('&', $query_string) as $pair) {
			if ($pair === '') {
				continue;
			}
			$segments = explode('=', $pair, 2);
			$raw_key = rawurldecode((string) ($segments[0] ?? ''));
			if ($raw_key !== $target_key) {
				continue;
			}
			$raw_value = isset($segments[1]) ? rawurldecode((string) $segments[1]) : '';
			return (string) $raw_value;
		}

		return null;
	}

	/**
	 * Read raw query-string keys while preserving "+" characters.
	 *
	 * @return array<int,string>
	 */
	private static function get_raw_query_string_keys(): array {
		$query_string = isset($_SERVER['QUERY_STRING']) ? (string) wp_unslash($_SERVER['QUERY_STRING']) : '';
		$keys = [];
		if ($query_string !== '') {
			foreach (explode('&', $query_string) as $pair) {
				if ($pair === '') {
					continue;
				}
				$segments = explode('=', $pair, 2);
				$raw_key = rawurldecode((string) ($segments[0] ?? ''));
				if ($raw_key !== '') {
					$keys[] = $raw_key;
				}
			}
		}
		if (empty($keys) && !empty($_GET) && is_array($_GET)) {
			$raw_query = wp_unslash($_GET);
			if (is_array($raw_query)) {
				foreach (array_keys($raw_query) as $raw_key) {
					if (is_string($raw_key) && $raw_key !== '') {
						$keys[] = $raw_key;
					}
				}
			}
		}
		return array_values(array_unique(array_map('strval', $keys)));
	}

	/**
	 * Build tax_query clauses for single/AND/OR multi-tag URL override logic.
	 *
	 * @param array<int,string> $slugs Valid tag slugs.
	 * @param string            $mode  one of single|and|or.
	 * @return array<int,array<string,mixed>>
	 */
	private static function build_media_library_gallery_multi_tag_tax_query(array $slugs, string $mode): array {
		$slugs = array_values(array_unique(array_filter(array_map('sanitize_title', $slugs))));
		if (empty($slugs)) {
			return [];
		}
		$taxonomy = self::media_library_tags_taxonomy();
		if ($mode === 'and') {
			$clauses = [];
			foreach ($slugs as $slug) {
				if ($slug === '') {
					continue;
				}
				$clauses[] = [
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => [$slug],
				];
			}
			return $clauses;
		}
		return [
			[
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $slugs,
			],
		];
	}

	/**
	 * Replace tag placeholders in post content.
	 */
	public static function replace_media_library_tag_placeholders_in_content(string $content): string {
		return self::replace_media_library_tag_placeholders_in_text($content, true);
	}

	/**
	 * Replace tag placeholders in post titles.
	 *
	 * @param string $title   The title text.
	 * @param int    $post_id Post ID provided by filter.
	 */
	public static function replace_media_library_tag_placeholders_in_title(string $title, int $post_id = 0): string {
		unset($post_id);
		return self::replace_media_library_tag_placeholders_in_text($title, false);
	}

	/**
	 * Replace placeholders in document title parts (<title> support).
	 *
	 * @param array<string,string> $parts Document title parts.
	 * @return array<string,string>
	 */
	public static function replace_media_library_tag_placeholders_in_document_title_parts(array $parts): array {
		// Ensure config is initialized before themes/SEO plugins build title parts.
		self::prime_media_library_tag_placeholder_config();
		foreach ($parts as $key => $value) {
			if (!is_string($value)) {
				continue;
			}
			$parts[$key] = self::replace_media_library_tag_placeholders_in_text($value, false);
		}
		return $parts;
	}

	/**
	 * Replace placeholders in pre-generated document title fallback.
	 */
	public static function replace_media_library_tag_placeholders_in_document_title($title): string {
		if (!is_string($title) || $title === '') {
			return is_string($title) ? $title : '';
		}
		// Ensure config is initialized before themes/SEO plugins build raw title string.
		self::prime_media_library_tag_placeholder_config();
		return self::replace_media_library_tag_placeholders_in_text($title, false);
	}

	/**
	 * Add "Edit Library Tag" shortcut to front-end admin bar when URL tag override is active.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function add_media_library_tag_admin_bar_shortcut($wp_admin_bar): void {
		if (!($wp_admin_bar instanceof WP_Admin_Bar) || is_admin() || !is_admin_bar_showing()) {
			return;
		}

		$config = self::get_current_post_media_library_tag_placeholder_config();
		if (empty($config['enabled'])) {
			return;
		}

		$tag_override = self::resolve_media_library_gallery_url_tag_override(!empty($config['allowAny']));
		$tag_slug = isset($tag_override['primarySlug']) ? (string) $tag_override['primarySlug'] : '';
		if ($tag_slug === '') {
			return;
		}

		$taxonomy = self::media_library_tags_taxonomy();
		$term = get_term_by('slug', $tag_slug, $taxonomy);
		if (!$term instanceof WP_Term) {
			return;
		}
		if (!current_user_can('edit_term', (int) $term->term_id)) {
			return;
		}

		$edit_link = add_query_arg(
			[
				'taxonomy' => $taxonomy,
				'tag_ID' => (int) $term->term_id,
				'post_type' => 'attachment',
			],
			admin_url('term.php')
		);
		$wp_admin_bar->add_node([
			'id' => 'um-edit-library-tag',
			'title' => __('Edit Library Tag', 'user-manager'),
			'href' => esc_url($edit_link),
			'parent' => 'top-secondary',
			'meta' => [
				'title' => sprintf(__('Edit Library Tag: %s', 'user-manager'), (string) $term->name),
			],
		]);
	}

	/**
	 * Replace [tag-name]/[tag-description]/[site-title] placeholders when enabled by block settings.
	 */
	private static function replace_media_library_tag_placeholders_in_text(string $text, bool $allow_html = false): string {
		if (strpos($text, '[tag-name]') === false && strpos($text, '[tag-description]') === false && strpos($text, '[site-title]') === false) {
			return $text;
		}
		if (!self::is_media_library_tag_name_placeholder_enabled_on_current_post()) {
			return $text;
		}

		$placeholder_values = self::get_media_library_tag_placeholder_values();
		$description = (string) ($placeholder_values['description'] ?? '');
		if ($allow_html) {
			$description = self::render_media_library_tag_description_paragraphs_html($placeholder_values);
		}

		$site_title = sanitize_text_field((string) get_bloginfo('name'));
		return strtr($text, [
			'[tag-name]' => (string) ($placeholder_values['name'] ?? ''),
			'[tag-description]' => $description,
			'[site-title]' => $site_title,
		]);
	}

	/**
	 * Determine if current singular post has a gallery block that enables URL tag override.
	 */
	private static function is_media_library_tag_name_placeholder_enabled_on_current_post(): bool {
		$config = self::get_current_post_media_library_tag_placeholder_config();
		return !empty($config['enabled']);
	}

	/**
	 * Resolve active URL tag placeholder values.
	 *
	 * @return array{
	 *   name:string,
	 *   description:string,
	 *   editDescriptionUrl:string,
	 *   names:array<int,string>,
	 *   descriptions:array<int,string>,
	 *   editDescriptionUrls:array<int,string>,
	 *   featuredImageId:int,
	 *   featuredImageIds:array<int,int>
	 * }
	 */
	private static function get_media_library_tag_placeholder_values(): array {
		$config = self::get_current_post_media_library_tag_placeholder_config();
		if (empty($config['enabled'])) {
			return [
				'name' => '',
				'description' => '',
				'editDescriptionUrl' => '',
				'names' => [],
				'descriptions' => [],
				'editDescriptionUrls' => [],
				'featuredImageId' => 0,
				'featuredImageIds' => [],
			];
		}

		$allow_any = !empty($config['allowAny']);
		$tag_override = self::resolve_media_library_gallery_url_tag_override($allow_any);
		$placeholder_values = self::get_media_library_tag_description_data_for_tag_expression($tag_override);
		$title_override = self::get_media_library_gallery_title_query_override();
		if ($title_override !== '') {
			$placeholder_values['name'] = $title_override;
		}
		return $placeholder_values;
	}

	/**
	 * Resolve optional URL title override for [tag-name] placeholder replacement.
	 */
	private static function get_media_library_gallery_title_query_override(): string {
		$raw_value = self::get_raw_query_string_value('title');
		if ($raw_value === null && isset($_GET['title'])) {
			$raw_value = (string) wp_unslash($_GET['title']);
		}
		if ($raw_value === null) {
			return '';
		}
		return trim(sanitize_text_field((string) $raw_value));
	}

	/**
	 * Build tag description/name/edit-link data for a parsed URL tag expression.
	 *
	 * @param array{mode?:string,slugs?:array<int,string>,primarySlug?:string} $tag_override
	 * @return array{
	 *   name:string,
	 *   description:string,
	 *   editDescriptionUrl:string,
	 *   names:array<int,string>,
	 *   descriptions:array<int,string>,
	 *   editDescriptionUrls:array<int,string>,
	 *   bulletLines:array<int,string>,
	 *   bulletsLines:array<int,array<int,string>>
	 * }
	 */
	private static function get_media_library_tag_description_data_for_tag_expression(array $tag_override): array {
		$empty = [
			'name' => '',
			'description' => '',
			'editDescriptionUrl' => '',
			'names' => [],
			'descriptions' => [],
			'editDescriptionUrls' => [],
			'bulletLines' => [],
			'bulletsLines' => [],
			'lastIndex' => -1,
		];
		$slugs = isset($tag_override['slugs']) && is_array($tag_override['slugs'])
			? array_values(array_filter(array_map('sanitize_title', array_map('strval', $tag_override['slugs']))))
			: [];
		$fallback_slug = isset($tag_override['primarySlug']) ? sanitize_title((string) $tag_override['primarySlug']) : '';
		if (empty($slugs) && $fallback_slug !== '') {
			$slugs = [$fallback_slug];
		}
		if (empty($slugs)) {
			return $empty;
		}

		$taxonomy = self::media_library_tags_taxonomy();
		$names = [];
		$descriptions = [];
		$edit_urls = [];
		$featured_image_ids = [];
		$bullets_lines = [];
		foreach ($slugs as $slug) {
			$term = get_term_by('slug', $slug, $taxonomy);
			if (!$term instanceof WP_Term) {
				continue;
			}
			$names[] = trim((string) $term->name);
			$descriptions[] = trim((string) $term->description);
			$edit_url = '';
			if (current_user_can('manage_options') && current_user_can('edit_term', (int) $term->term_id)) {
				$edit_url = add_query_arg(
					[
						'taxonomy' => $taxonomy,
						'tag_ID' => (int) $term->term_id,
						'post_type' => 'attachment',
					],
					admin_url('term.php')
				);
			}
			$edit_urls[] = $edit_url;
			$featured_image_ids[] = self::get_media_library_tag_featured_image_id((int) $term->term_id);
			$bullets_lines[] = self::get_media_library_tag_bullets_lines((int) $term->term_id);
		}
		if (empty($names)) {
			return $empty;
		}

		return [
			'name' => (string) $names[0],
			'description' => (string) ($descriptions[0] ?? ''),
			'editDescriptionUrl' => (string) ($edit_urls[0] ?? ''),
			'names' => $names,
			'descriptions' => $descriptions,
			'editDescriptionUrls' => $edit_urls,
			'featuredImageId' => (int) ($featured_image_ids[0] ?? 0),
			'featuredImageIds' => $featured_image_ids,
			'bulletLines' => (isset($bullets_lines[0]) && is_array($bullets_lines[0])) ? $bullets_lines[0] : [],
			'bulletsLines' => $bullets_lines,
			'lastIndex' => !empty($names) ? (int) array_key_last($names) : -1,
		];
	}

	/**
	 * Render description paragraphs (with optional edit links) for one or many tags.
	 *
	 * @param array{
	 *   description?:string,
	 *   editDescriptionUrl?:string,
	 *   descriptions?:array<int,string>,
	 *   editDescriptionUrls?:array<int,string>,
	 *   bulletLines?:array<int,string>,
	 *   bulletsLines?:array<int,array<int,string>>,
	 *   featuredImageId?:int,
	 *   featuredImageIds?:array<int,int>
	 * } $tag_description_data
	 * @param array{
	 *   linkTo?:string,
	 *   descriptionValue?:string,
	 *   showDescriptionInLightbox?:bool,
	 *   showLightboxAdminEditLink?:bool,
	 *   inlineLightboxOpenOnclick?:string,
	 *   featuredLightboxEnabled?:bool,
	 *   featuredLightboxIndex?:int,
	 *   hideFeaturedImageIfNoDescriptionOrBullets?:bool
	 * } $layout_options
	 */
	private static function render_media_library_tag_description_paragraphs_html(array $tag_description_data, array $layout_options = []): string {
		$description = trim((string) ($tag_description_data['description'] ?? ''));
		$edit_description_url = (string) ($tag_description_data['editDescriptionUrl'] ?? '');
		$descriptions = isset($tag_description_data['descriptions']) && is_array($tag_description_data['descriptions'])
			? $tag_description_data['descriptions']
			: [];
		$edit_description_urls = isset($tag_description_data['editDescriptionUrls']) && is_array($tag_description_data['editDescriptionUrls'])
			? $tag_description_data['editDescriptionUrls']
			: [];
		$single_bullet_lines = isset($tag_description_data['bulletLines']) && is_array($tag_description_data['bulletLines'])
			? array_values(array_filter(array_map(static function ($line): string {
				return trim(sanitize_text_field((string) $line));
			}, $tag_description_data['bulletLines'])))
			: [];
		$bullets_lines = isset($tag_description_data['bulletsLines']) && is_array($tag_description_data['bulletsLines'])
			? $tag_description_data['bulletsLines']
			: [];
		$description_paragraphs = [];
		$last_index = !empty($descriptions) ? array_key_last($descriptions) : null;
		if ($last_index !== null) {
			$tag_description = trim((string) ($descriptions[$last_index] ?? ''));
			$tag_edit_url = isset($edit_description_urls[$last_index]) ? (string) $edit_description_urls[$last_index] : '';
			$tag_bullet_lines = [];
			if (isset($bullets_lines[$last_index]) && is_array($bullets_lines[$last_index])) {
				$tag_bullet_lines = array_values(array_filter(array_map(static function ($line): string {
					return trim(sanitize_text_field((string) $line));
				}, $bullets_lines[$last_index])));
			}
			if (empty($tag_bullet_lines) && !empty($single_bullet_lines)) {
				$tag_bullet_lines = $single_bullet_lines;
			}
			if ($tag_description !== '' || $tag_edit_url !== '' || !empty($tag_bullet_lines)) {
				$tag_description_html = self::format_media_library_tag_description_html($tag_description);
				$edit_link = '';
				if ($tag_edit_url !== '') {
					$edit_link = sprintf(
						' <a class="um-media-library-tag-edit-description-link" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
						esc_url($tag_edit_url),
						esc_html__('Edit Tag Description', 'user-manager')
					);
				}
				$paragraph_html = sprintf(
					'<p class="um-media-library-tag-description-paragraph">%1$s%2$s</p>',
					$tag_description_html,
					$edit_link
				);
				$bullets_html = self::render_media_library_tag_description_bullets_html($tag_bullet_lines);
				if ($bullets_html !== '') {
					$description_paragraphs[] = '<div class="um-media-library-tag-description-block">' . $paragraph_html . $bullets_html . '</div>';
				} else {
					$description_paragraphs[] = $paragraph_html;
				}
			}
		}
		$description_html = '';
		if (!empty($description_paragraphs)) {
			$description_html = implode('', $description_paragraphs);
		} elseif ($edit_description_url !== '' && $description !== '') {
			$description_html = self::format_media_library_tag_description_html($description) . sprintf(
				' <a class="um-media-library-tag-edit-description-link" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
				esc_url($edit_description_url),
				esc_html__('Edit Tag Description', 'user-manager')
			);
		} else {
			$description_html = self::format_media_library_tag_description_html($description);
		}
		if (empty($description_paragraphs)) {
			$fallback_bullets_html = self::render_media_library_tag_description_bullets_html($single_bullet_lines);
			if ($fallback_bullets_html !== '') {
				if ($description_html !== '') {
					$description_html = '<div class="um-media-library-tag-description-block">' . $description_html . $fallback_bullets_html . '</div>';
				} else {
					$description_html = $fallback_bullets_html;
				}
			}
		}
		$hide_featured_image_if_no_content = !empty($layout_options['hideFeaturedImageIfNoDescriptionOrBullets']);
		if (
			$hide_featured_image_if_no_content
			&& !self::media_library_tag_description_data_has_non_empty_description_or_bullets($tag_description_data)
		) {
			return $description_html;
		}
		$featured_image_html = self::render_media_library_tag_featured_image_html($tag_description_data, $layout_options);
		if ($featured_image_html === '') {
			return $description_html;
		}
		if ($description_html === '') {
			return '<div class="um-media-library-tag-description-layout um-media-library-tag-description-layout-with-floating-image">' . $featured_image_html . '</div>';
		}
		$use_separate_column = !empty($layout_options['useSeparateFeaturedImageColumn']);
		if ($use_separate_column) {
			return '<div class="um-media-library-tag-description-layout um-media-library-tag-description-layout-split-columns"><div class="um-media-library-tag-description-column um-media-library-tag-description-column-image">' . $featured_image_html . '</div><div class="um-media-library-tag-description-column um-media-library-tag-description-column-content">' . $description_html . '</div></div>';
		}
		return '<div class="um-media-library-tag-description-layout um-media-library-tag-description-layout-with-floating-image">' . $featured_image_html . $description_html . '</div>';
	}

	/**
	 * Determine whether tag description payload includes any non-empty description or bullets.
	 *
	 * @param array<string,mixed> $tag_description_data
	 */
	private static function media_library_tag_description_data_has_non_empty_description_or_bullets(array $tag_description_data): bool {
		$description = trim((string) ($tag_description_data['description'] ?? ''));
		if ($description !== '') {
			return true;
		}
		$descriptions = isset($tag_description_data['descriptions']) && is_array($tag_description_data['descriptions'])
			? $tag_description_data['descriptions']
			: [];
		foreach ($descriptions as $description_value) {
			if (trim((string) $description_value) !== '') {
				return true;
			}
		}
		$single_bullet_lines = isset($tag_description_data['bulletLines']) && is_array($tag_description_data['bulletLines'])
			? $tag_description_data['bulletLines']
			: [];
		foreach ($single_bullet_lines as $bullet_line) {
			if (trim(sanitize_text_field((string) $bullet_line)) !== '') {
				return true;
			}
		}
		$bullets_lines = isset($tag_description_data['bulletsLines']) && is_array($tag_description_data['bulletsLines'])
			? $tag_description_data['bulletsLines']
			: [];
		foreach ($bullets_lines as $bullet_group) {
			if (!is_array($bullet_group)) {
				continue;
			}
			foreach ($bullet_group as $bullet_line) {
				if (trim(sanitize_text_field((string) $bullet_line)) !== '') {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Render a UL list for Library Tag description bullets.
	 *
	 * @param array<int,string> $bullet_lines
	 */
	private static function render_media_library_tag_description_bullets_html(array $bullet_lines): string {
		$settings = User_Manager_Core::get_settings();
		$defaults = self::get_media_library_tag_gallery_defaults($settings);
		$sanitized_lines = [];
		foreach ($bullet_lines as $line) {
			$line = trim(sanitize_text_field((string) $line));
			if ($line === '') {
				continue;
			}
			$sanitized_lines[] = $line;
		}
		if (empty($sanitized_lines)) {
			return '';
		}
		$items = [];
		$has_10_plus_bullets = count($sanitized_lines) > 10;
		$bullet_classes = ['um-media-library-tag-description-bullet'];
		if ($has_10_plus_bullets) {
			$bullet_classes[] = '10plusbullets';
		}
		$li_inline_style = '';
		if ($has_10_plus_bullets) {
			$li_inline_style = isset($defaults['inlineStylesForLiTagsIf10PlusBulletsBeingDisplayed'])
				? trim(sanitize_text_field((string) $defaults['inlineStylesForLiTagsIf10PlusBulletsBeingDisplayed']))
				: '';
		}
		$li_inline_style_attr = $li_inline_style !== '' ? ' style="' . esc_attr($li_inline_style) . '"' : '';
		foreach ($sanitized_lines as $sanitized_line) {
			$items[] = '<li class="' . esc_attr(implode(' ', $bullet_classes)) . '"' . $li_inline_style_attr . '>' . esc_html($sanitized_line) . '</li>';
		}
		$bullets_classes = ['um-media-library-tag-description-bullets'];
		if ($has_10_plus_bullets) {
			$bullets_classes[] = '10plusbullets';
		}
		return '<ul class="' . esc_attr(implode(' ', $bullets_classes)) . '">' . implode('', $items) . '</ul>';
	}

	/**
	 * Render tag featured image HTML for description layout.
	 *
	 * @param array{
	 *   featuredImageId?:int,
	 *   featuredImageIds?:array<int,int>
	 * } $tag_description_data
	 * @param array{
	 *   linkTo?:string,
	 *   descriptionValue?:string,
	 *   showDescriptionInLightbox?:bool,
	 *   showLightboxAdminEditLink?:bool,
	 *   inlineLightboxOpenOnclick?:string,
	 *   featuredLightboxEnabled?:bool,
	 *   featuredLightboxIndex?:int
	 * } $layout_options
	 */
	private static function render_media_library_tag_featured_image_html(array $tag_description_data, array $layout_options = []): string {
		$featured_image_ids = isset($tag_description_data['featuredImageIds']) && is_array($tag_description_data['featuredImageIds'])
			? array_values(array_filter(array_map('absint', $tag_description_data['featuredImageIds'])))
			: [];
		$last_index = isset($tag_description_data['lastIndex']) ? (int) $tag_description_data['lastIndex'] : -1;
		if ($last_index >= 0 && isset($featured_image_ids[$last_index])) {
			$last_featured_image_id = absint((int) $featured_image_ids[$last_index]);
			if ($last_featured_image_id > 0) {
				$featured_image_ids = [$last_featured_image_id];
			}
		}
		if (empty($featured_image_ids)) {
			$single_featured_image_id = isset($tag_description_data['featuredImageId']) ? absint($tag_description_data['featuredImageId']) : 0;
			if ($single_featured_image_id > 0) {
				$featured_image_ids = [$single_featured_image_id];
			}
		}
		if (empty($featured_image_ids)) {
			return '';
		}

		$attachment_id = absint($featured_image_ids[0]);
		if ($attachment_id <= 0) {
			return '';
		}

		$image_html = wp_get_attachment_image(
			$attachment_id,
			'large',
			false,
			[
				'class' => 'um-media-library-tag-description-featured-image wp-post-image',
				'loading' => 'lazy',
				'decoding' => 'async',
			]
		);
		if (!is_string($image_html) || $image_html === '') {
			return '';
		}

		$effective_link_to = isset($layout_options['linkTo']) ? sanitize_key((string) $layout_options['linkTo']) : '';
		$show_description_in_lightbox = !empty($layout_options['showDescriptionInLightbox']);
		$show_lightbox_admin_edit_link = !empty($layout_options['showLightboxAdminEditLink']);
		$inline_lightbox_open_onclick = isset($layout_options['inlineLightboxOpenOnclick']) ? (string) $layout_options['inlineLightboxOpenOnclick'] : '';
		$featured_lightbox_enabled = !empty($layout_options['featuredLightboxEnabled']);
		$featured_lightbox_index = isset($layout_options['featuredLightboxIndex']) ? (int) $layout_options['featuredLightboxIndex'] : -1;

		$image_alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
		$description_value = isset($layout_options['descriptionValue']) ? sanitize_key((string) $layout_options['descriptionValue']) : 'caption';
		$description_text = self::get_media_library_gallery_description_text($attachment_id, $description_value);
		$description_attr = $image_alt !== '' ? $image_alt : $description_text;
		$image_src = wp_get_attachment_image_url($attachment_id, 'full');

		if ($featured_lightbox_enabled && $effective_link_to === 'lightbox' && $image_src) {
			$link_html = sprintf(
				'<button type="button" class="um-media-library-tag-description-featured-trigger um-media-library-tag-gallery-link um-media-library-tag-gallery-lightbox-trigger" data-um-modal-trigger="1" data-um-modal-src="%1$s" data-um-modal-alt="%2$s" data-um-modal-index="%3$s"%4$s%5$s data-um-modal-attachment-id="%6$d" onclick="%7$s" aria-label="%2$s">%8$s</button>',
				esc_attr((string) $image_src),
				esc_attr($description_attr),
				esc_attr((string) max(0, $featured_lightbox_index)),
				($show_description_in_lightbox && $description_text !== '') ? ' data-um-modal-caption="' . esc_attr($description_text) . '"' : '',
				$show_lightbox_admin_edit_link ? ' data-um-modal-edit-url="' . esc_attr((string) get_edit_post_link($attachment_id, '')) . '"' : '',
				$attachment_id,
				esc_attr($inline_lightbox_open_onclick),
				$image_html
			);
			return '<figure class="um-media-library-tag-description-featured">' . $link_html . '</figure>';
		}

		return '<figure class="um-media-library-tag-description-featured">' . $image_html . '</figure>';
	}

	/**
	 * Collect whether current post enables [tag-name] replacement and any-param mode.
	 *
	 * @return array{enabled:bool,allowAny:bool}
	 */
	private static function get_current_post_media_library_tag_placeholder_config(): array {
		static $cache = [];
		$post_id = get_queried_object_id();
		if ($post_id <= 0 && is_singular()) {
			$global_post = get_post();
			if ($global_post instanceof WP_Post) {
				$post_id = (int) $global_post->ID;
			}
		}
		if (isset($cache[$post_id]) && is_array($cache[$post_id])) {
			return $cache[$post_id];
		}

		$config = ['enabled' => false, 'allowAny' => false];
		if (is_admin() || !is_singular()) {
			return $config;
		}

		$post = get_post($post_id);
		if (!$post instanceof WP_Post) {
			return $config;
		}
		$content = (string) $post->post_content;
		if ($content === '' || !has_blocks($content)) {
			return $config;
		}

		$blocks = parse_blocks($content);
		if (!is_array($blocks) || empty($blocks)) {
			return $config;
		}

		$config = self::scan_media_library_tag_placeholder_config_from_blocks($blocks);
		$cache[$post_id] = $config;
		return $config;
	}

	/**
	 * Prime placeholder config cache early so title filters can resolve [tag-name].
	 */
	private static function prime_media_library_tag_placeholder_config(): void {
		static $primed = false;
		if ($primed) {
			return;
		}
		$primed = true;
		self::get_current_post_media_library_tag_placeholder_config();
	}

	/**
	 * Add front-end body class when active tag-description context has >10 bullets.
	 *
	 * @param array<int,string> $classes
	 * @return array<int,string>
	 */
	public static function add_media_library_tag_body_classes_for_bullets(array $classes): array {
		if (is_admin()) {
			return $classes;
		}
		if (!self::media_library_tag_description_context_has_more_than_ten_bullets()) {
			return $classes;
		}
		$classes[] = '10plusbullets';
		return array_values(array_unique(array_filter(array_map('sanitize_html_class', $classes))));
	}

	/**
	 * Determine whether current active tag-description context has >10 bullets.
	 */
	private static function media_library_tag_description_context_has_more_than_ten_bullets(): bool {
		if (!is_singular()) {
			return false;
		}
		$config = self::get_current_post_media_library_tag_placeholder_config();
		if (empty($config['enabled'])) {
			return false;
		}
		$allow_any = !empty($config['allowAny']);
		$tag_override = self::resolve_media_library_gallery_url_tag_override($allow_any);
		$tag_description_data = self::get_media_library_tag_description_data_for_tag_expression($tag_override);
		$descriptions = isset($tag_description_data['descriptions']) && is_array($tag_description_data['descriptions'])
			? $tag_description_data['descriptions']
			: [];
		$last_index = !empty($descriptions) ? array_key_last($descriptions) : null;
		$bullets_lines = isset($tag_description_data['bulletsLines']) && is_array($tag_description_data['bulletsLines'])
			? $tag_description_data['bulletsLines']
			: [];
		$single_bullet_lines = isset($tag_description_data['bulletLines']) && is_array($tag_description_data['bulletLines'])
			? $tag_description_data['bulletLines']
			: [];
		$effective_lines = [];
		if ($last_index !== null && isset($bullets_lines[$last_index]) && is_array($bullets_lines[$last_index])) {
			$effective_lines = $bullets_lines[$last_index];
		}
		if (empty($effective_lines)) {
			$effective_lines = $single_bullet_lines;
		}
		$normalized_lines = array_values(array_filter(array_map(static function ($line): string {
			return trim(sanitize_text_field((string) $line));
		}, $effective_lines)));
		return count($normalized_lines) > 10;
	}

	/**
	 * Recursively scan parsed blocks for Media Library Tag Gallery URL override settings.
	 *
	 * @param array<int,array<string,mixed>> $blocks
	 * @return array{enabled:bool,allowAny:bool}
	 */
	private static function scan_media_library_tag_placeholder_config_from_blocks(array $blocks): array {
		$config = ['enabled' => false, 'allowAny' => false];
		foreach ($blocks as $block) {
			if (!is_array($block)) {
				continue;
			}

			$block_name = isset($block['blockName']) ? (string) $block['blockName'] : '';
			if ($block_name === 'custom/media-library-tag-gallery') {
				$attrs = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : [];
				if (!empty($attrs['allowUrlTagOverride']) || !empty($attrs['allowAnyUrlParamTagIdentifier'])) {
					$config['enabled'] = true;
					if (!empty($attrs['allowAnyUrlParamTagIdentifier'])) {
						$config['allowAny'] = true;
						return $config;
					}
				}
			}

			if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
				$inner = self::scan_media_library_tag_placeholder_config_from_blocks($block['innerBlocks']);
				if (!empty($inner['enabled'])) {
					$config['enabled'] = true;
				}
				if (!empty($inner['allowAny'])) {
					$config['allowAny'] = true;
					return $config;
				}
			}
		}

		return $config;
	}

	/**
	 * Get all term IDs for the Library Tags taxonomy.
	 *
	 * @return array<int,int>
	 */
	private static function get_media_library_all_tag_term_ids(): array {
		$ids = get_terms([
			'taxonomy' => self::media_library_tags_taxonomy(),
			'hide_empty' => false,
			'fields' => 'ids',
		]);
		if (is_wp_error($ids) || !is_array($ids)) {
			return [];
		}
		return array_values(array_filter(array_map('absint', $ids)));
	}

	/**
	 * Resolve matching tag slugs for a selected filter slug.
	 *
	 * Supports selecting a base term (e.g. "cruise") while still including
	 * compound terms whose slug/name token contains that term
	 * (e.g. "honeymoon-cruise" / "Honeymoon, Cruise").
	 *
	 * @return array<int,string>
	 */
	private static function get_media_library_filter_slugs_for_requested_slug(string $requested_slug): array {
		$requested_slug = sanitize_title($requested_slug);
		if ($requested_slug === '') {
			return [];
		}

		static $cache = [];
		if (isset($cache[$requested_slug]) && is_array($cache[$requested_slug])) {
			return $cache[$requested_slug];
		}

		$matched = [$requested_slug];
		$requested_tokens = array_values(array_filter(explode('-', $requested_slug)));
		sort($requested_tokens);
		$requested_token_signature = implode('|', $requested_tokens);
		$taxonomy = self::media_library_tags_taxonomy();
		$terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
		]);
		if (is_wp_error($terms) || !is_array($terms)) {
			$cache[$requested_slug] = array_values(array_unique(array_filter($matched)));
			return $cache[$requested_slug];
		}

		$slug_pattern = '/(^|-)' . preg_quote($requested_slug, '/') . '($|-)/';
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$candidate_slug = sanitize_title((string) $term->slug);
			if ($candidate_slug === '') {
				continue;
			}
			if ($candidate_slug === $requested_slug) {
				$matched[] = $candidate_slug;
				continue;
			}

			$is_match = preg_match($slug_pattern, $candidate_slug) === 1;
			if (
				!$is_match
				&& count($requested_tokens) > 1
			) {
				$candidate_tokens = array_values(array_filter(explode('-', $candidate_slug)));
				sort($candidate_tokens);
				$is_match = !empty($candidate_tokens) && implode('|', $candidate_tokens) === $requested_token_signature;
			}
			if (!$is_match) {
				$name_tokens = preg_split('/[\s,;\/\|\+&]+/', (string) $term->name);
				if (is_array($name_tokens)) {
					$token_pattern = '/(^|-)' . preg_quote($requested_slug, '/') . '($|-)/';
					foreach ($name_tokens as $token) {
						$token_slug = sanitize_title((string) $token);
						if ($token_slug === '') {
							continue;
						}
						if (preg_match($token_pattern, $token_slug) === 1) {
							$is_match = true;
							break;
						}
					}
				}
			}

			if ($is_match) {
				$matched[] = $candidate_slug;
			}
		}

		$cache[$requested_slug] = array_values(array_unique(array_filter($matched)));
		return $cache[$requested_slug];
	}

	/**
	 * Build unique individual filter options from Library Tags terms.
	 *
	 * - A plain term name remains as one option.
	 * - A comma-separated term name contributes one option per token.
	 *
	 * @return array<int,array{slug:string,name:string,in_menu_nav:bool}>
	 */
	private static function get_media_library_unique_filter_terms(): array {
		static $cache = null;
		if (is_array($cache)) {
			return $cache;
		}

		$terms = get_terms([
			'taxonomy' => self::media_library_tags_taxonomy(),
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]);
		if (is_wp_error($terms) || !is_array($terms)) {
			$cache = [];
			return $cache;
		}

		$options_by_slug = [];
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$term_name = trim((string) $term->name);
			if ($term_name === '') {
				continue;
			}
			$segments = strpos($term_name, ',') !== false
				? preg_split('/\s*,\s*/', $term_name)
				: [$term_name];
			if (!is_array($segments) || empty($segments)) {
				$segments = [$term_name];
			}
			foreach ($segments as $segment) {
				$segment = trim((string) $segment);
				if ($segment === '') {
					continue;
				}
				$segment_slug = sanitize_title($segment);
				if ($segment_slug === '' || isset($options_by_slug[$segment_slug])) {
					continue;
				}
				$options_by_slug[$segment_slug] = [
					'slug' => $segment_slug,
					'name' => $segment,
				];
			}
		}

		$menu_slug_matches = self::get_media_library_menu_slug_matches(array_keys($options_by_slug));
		$options = array_values(array_map(static function (array $option) use ($menu_slug_matches): array {
			$slug = isset($option['slug']) ? sanitize_title((string) $option['slug']) : '';
			$option['in_menu_nav'] = $slug !== '' && !empty($menu_slug_matches[$slug]);
			return $option;
		}, $options_by_slug));
		usort($options, static function (array $left, array $right): int {
			return strnatcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
		});

		$cache = $options;
		return $cache;
	}

	/**
	 * Validate filter slug against real terms or generated individual filter tokens.
	 */
	private static function is_valid_media_library_tag_filter_slug(string $slug): bool {
		$slug = sanitize_title($slug);
		if ($slug === '') {
			return false;
		}
		if (term_exists($slug, self::media_library_tags_taxonomy())) {
			return true;
		}
		$filter_terms = self::get_media_library_unique_filter_terms();
		foreach ($filter_terms as $filter_term) {
			if (!is_array($filter_term) || empty($filter_term['slug'])) {
				continue;
			}
			if ((string) $filter_term['slug'] === $slug) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Remove existing Library Tags taxonomy clauses from a tax_query array.
	 *
	 * @param mixed $tax_query Existing tax_query.
	 * @return array<int|string,mixed>
	 */
	private static function remove_media_library_tag_tax_query_clauses($tax_query): array {
		if (!is_array($tax_query)) {
			return [];
		}
		$filtered = [];
		foreach ($tax_query as $key => $clause) {
			if ($key === 'relation') {
				continue;
			}
			if (is_array($clause) && !empty($clause['taxonomy']) && (string) $clause['taxonomy'] === self::media_library_tags_taxonomy()) {
				continue;
			}
			$filtered[] = $clause;
		}
		return $filtered;
	}

	/**
	 * Whether a requested filter value indicates "No tags".
	 */
	private static function is_media_library_no_tags_filter_value(string $value): bool {
		return $value === self::get_media_library_no_tags_filter_value();
	}

	/**
	 * Apply selected Library Tag to attachment IDs.
	 *
	 * @param array<int,int> $attachment_ids Attachment IDs.
	 */
	private static function bulk_apply_media_library_tag_to_attachments(array $attachment_ids, string $tag_slug): int {
		$taxonomy = self::media_library_tags_taxonomy();
		$term = get_term_by('slug', $tag_slug, $taxonomy);
		if (!$term instanceof WP_Term) {
			return 0;
		}

		$updated_count = 0;
		foreach (array_values(array_unique(array_filter(array_map('absint', $attachment_ids)))) as $attachment_id) {
			if ($attachment_id <= 0 || get_post_type($attachment_id) !== 'attachment') {
				continue;
			}
			$result = wp_set_object_terms($attachment_id, [(int) $term->term_id], $taxonomy, true);
			if (!is_wp_error($result)) {
				$updated_count++;
			}
		}

		return $updated_count;
	}

	/**
	 * Resolve selected/new tag input to an assignable Library Tag slug.
	 * Creates a new term when only a new tag name is provided.
	 */
	private static function resolve_media_library_bulk_tag_slug(string $selected_slug, string $new_tag_raw = ''): string {
		$taxonomy = self::media_library_tags_taxonomy();

		$new_tag_name = trim(sanitize_text_field($new_tag_raw));
		if ($new_tag_name !== '') {
			$existing_by_name = get_term_by('name', $new_tag_name, $taxonomy);
			if ($existing_by_name instanceof WP_Term) {
				return (string) $existing_by_name->slug;
			}

			$new_tag_slug = sanitize_title($new_tag_name);
			if ($new_tag_slug !== '') {
				$existing_by_slug = get_term_by('slug', $new_tag_slug, $taxonomy);
				if ($existing_by_slug instanceof WP_Term) {
					return (string) $existing_by_slug->slug;
				}
			}

			$created = wp_insert_term($new_tag_name, $taxonomy);
			if (!is_wp_error($created) && isset($created['term_id'])) {
				$created_term = get_term((int) $created['term_id'], $taxonomy);
				if ($created_term instanceof WP_Term) {
					return (string) $created_term->slug;
				}
			}

			return '';
		}

		$selected_slug = sanitize_title($selected_slug);
		if ($selected_slug !== '' && term_exists($selected_slug, $taxonomy)) {
			return $selected_slug;
		}

		return '';
	}

	/**
	 * Internal taxonomy name.
	 */
	private static function media_library_tags_taxonomy(): string {
		return 'um_media_library_tag';
	}
}
