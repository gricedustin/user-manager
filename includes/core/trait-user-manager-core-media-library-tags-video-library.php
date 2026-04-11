<?php
/**
 * Media Library Tags Video Library helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Media_Library_Tags_Video_Library_Trait {

	/**
	 * Option key for centralized Video Library items.
	 */
	private static function media_library_tag_video_library_option_key(): string {
		return 'um_media_library_tag_video_library_items';
	}

	/**
	 * Option key for one-time legacy YouTube term-meta migration.
	 */
	private static function media_library_tag_video_library_legacy_migration_flag_option_key(): string {
		return 'um_media_library_tag_video_library_migrated_legacy_meta_v1';
	}

	/**
	 * Init-safe wrapper for one-time migration.
	 *
	 * Prevents translation-triggering taxonomy registration from running too
	 * early during plugin bootstrap on WP 6.7+.
	 */
	public static function maybe_migrate_legacy_term_youtube_links_to_video_library_on_init(): void {
		if (!did_action('init')) {
			return;
		}
		self::maybe_migrate_legacy_term_youtube_links_to_video_library();
	}

	/**
	 * Register "Video Library" submenu under Media.
	 */
	public static function register_media_library_tag_video_library_submenu(): void {
		add_submenu_page(
			'upload.php',
			__('Video Library', 'user-manager'),
			__('Video Library', 'user-manager'),
			'upload_files',
			'um-media-library-tag-video-library',
			[__CLASS__, 'render_media_library_tag_video_library_page']
		);
	}

	/**
	 * Render centralized Video Library admin page.
	 */
	public static function render_media_library_tag_video_library_page(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage the Video Library.', 'user-manager'));
		}
		self::maybe_migrate_legacy_term_youtube_links_to_video_library();

		$taxonomy = self::media_library_tags_taxonomy();
		if (!taxonomy_exists($taxonomy)) {
			self::register_media_library_tags_taxonomy();
		}

		$all_terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]);
		if (!is_array($all_terms) || is_wp_error($all_terms)) {
			$all_terms = [];
		}

		$tag_options = [];
		foreach ($all_terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$term_slug = sanitize_title((string) $term->slug);
			if ($term_slug === '') {
				continue;
			}
			$tag_options[$term_slug] = (string) $term->name;
		}

		$items = self::get_media_library_tag_video_library_items();
		$items_by_id = [];
		foreach ($items as $item) {
			if (!is_array($item) || empty($item['id'])) {
				continue;
			}
			$items_by_id[(string) $item['id']] = $item;
		}

		$notice = isset($_GET['um_video_library_notice']) ? sanitize_key((string) wp_unslash($_GET['um_video_library_notice'])) : '';
		$editing_id = isset($_GET['video_id']) ? sanitize_text_field((string) wp_unslash($_GET['video_id'])) : '';
		$editing_item = ($editing_id !== '' && isset($items_by_id[$editing_id]) && is_array($items_by_id[$editing_id]))
			? $items_by_id[$editing_id]
			: [];

		$form_id = isset($editing_item['id']) ? (string) $editing_item['id'] : '';
		$form_url = isset($editing_item['youtubeUrl']) ? (string) $editing_item['youtubeUrl'] : '';
		$form_title = isset($editing_item['title']) ? (string) $editing_item['title'] : '';
		$form_description = isset($editing_item['description']) ? (string) $editing_item['description'] : '';
		$form_date = isset($editing_item['videoDate']) ? (string) $editing_item['videoDate'] : '';
		$form_time = isset($editing_item['videoTime']) ? (string) $editing_item['videoTime'] : '';
		$form_is_vertical = !empty($editing_item['isVertical']);
		$form_tag_slugs = isset($editing_item['tagSlugs']) && is_array($editing_item['tagSlugs'])
			? array_values(array_filter(array_map('sanitize_title', array_map('strval', $editing_item['tagSlugs']))))
			: [];
		$form_tag_slugs_csv = implode(',', $form_tag_slugs);
		$form_preview_video_id = '';
		if ($form_id !== '' && $form_url !== '') {
			$form_preview_video_id = self::get_media_library_tag_youtube_video_id_from_url($form_url);
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e('Video Library', 'user-manager'); ?></h1>
			<p><?php esc_html_e('Add and manage YouTube videos in one place, then assign Library Tags to control where they appear in your front-end media gallery blocks.', 'user-manager'); ?></p>
			<div class="notice notice-info" style="margin:12px 0 16px; padding:10px 12px;">
				<p style="margin:0 0 8px;">
					<strong><?php esc_html_e('Shortcode:', 'user-manager'); ?></strong>
					<code>[um_media_library_tag_videos tags="tag-expression" desktop_columns="3"]</code>
				</p>
				<p style="margin:0 0 6px;"><?php esc_html_e('Tag expression examples:', 'user-manager'); ?></p>
				<ul style="margin:0 0 8px 20px; list-style:disc;">
					<li><code>tag1</code> &mdash; <?php esc_html_e('single tag', 'user-manager'); ?></li>
					<li><code>tag1+tag2</code> &mdash; <?php esc_html_e('must have both tags (AND)', 'user-manager'); ?></li>
					<li><code>tag1_tag2</code> &mdash; <?php esc_html_e('must have either tag (OR)', 'user-manager'); ?></li>
					<li><code>tag1|tag2</code> &mdash; <?php esc_html_e('loop each tag independently on separate rows with tag title/description above each row', 'user-manager'); ?></li>
				</ul>
				<p style="margin:0; color:#50575e;"><?php esc_html_e('desktop_columns is optional (1-4). If omitted, columns auto-size based on number of videos.', 'user-manager'); ?></p>
			</div>
			<?php if ($notice === 'saved') : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Video saved successfully.', 'user-manager'); ?></p></div>
			<?php elseif ($notice === 'bulk_saved') : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Videos saved successfully.', 'user-manager'); ?></p></div>
			<?php elseif ($notice === 'deleted') : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Video deleted successfully.', 'user-manager'); ?></p></div>
			<?php elseif ($notice === 'invalid_youtube') : ?>
				<div class="notice notice-error is-dismissible"><p><?php esc_html_e('Please enter a valid YouTube URL.', 'user-manager'); ?></p></div>
			<?php elseif ($notice === 'bulk_invalid_youtube') : ?>
				<div class="notice notice-error is-dismissible"><p><?php esc_html_e('One or more rows had invalid YouTube URLs and were skipped.', 'user-manager'); ?></p></div>
			<?php endif; ?>

			<div class="um-video-library-form-wrap">
				<h2 style="margin-top:0;"><?php echo $form_id !== '' ? esc_html__('Edit Video', 'user-manager') : esc_html__('Add Video', 'user-manager'); ?></h2>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<input type="hidden" name="action" value="user_manager_media_library_tag_video_library_save" />
					<input type="hidden" name="um_video_library_video_id" value="<?php echo esc_attr($form_id); ?>" />
					<?php wp_nonce_field('user_manager_media_library_tag_video_library_save', 'user_manager_media_library_tag_video_library_nonce'); ?>
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><label for="um-video-library-youtube-url"><?php esc_html_e('YouTube Link', 'user-manager'); ?></label></th>
								<td>
									<input type="url" class="regular-text" style="width:100%;max-width:680px;" id="um-video-library-youtube-url" name="um_video_library_youtube_url" value="<?php echo esc_attr($form_url); ?>" placeholder="https://www.youtube.com/watch?v=..." required />
									<?php if ($form_preview_video_id !== '') : ?>
										<div style="margin-top:10px; max-width:680px;">
											<p class="description" style="margin:0 0 8px;"><?php esc_html_e('Current video preview:', 'user-manager'); ?></p>
											<div style="position:relative; width:100%; padding-top:56.25%; background:#000; border-radius:4px; overflow:hidden;">
												<iframe
													src="<?php echo esc_url('https://www.youtube.com/embed/' . rawurlencode($form_preview_video_id)); ?>"
													title="<?php esc_attr_e('YouTube video preview', 'user-manager'); ?>"
													style="position:absolute; inset:0; width:100%; height:100%; border:0;"
													allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
													allowfullscreen
												></iframe>
											</div>
										</div>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="um-video-library-title"><?php esc_html_e('Title', 'user-manager'); ?></label></th>
								<td>
									<input type="text" class="regular-text" style="width:100%;max-width:680px;" id="um-video-library-title" name="um_video_library_title" value="<?php echo esc_attr($form_title); ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="um-video-library-description"><?php esc_html_e('Description', 'user-manager'); ?></label></th>
								<td>
									<textarea id="um-video-library-description" name="um_video_library_description" rows="4" style="width:100%;max-width:680px;"><?php echo esc_textarea($form_description); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="um-video-library-date"><?php esc_html_e('Date', 'user-manager'); ?></label></th>
								<td>
									<input type="date" id="um-video-library-date" name="um_video_library_date" value="<?php echo esc_attr($form_date); ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="um-video-library-time"><?php esc_html_e('Time', 'user-manager'); ?></label></th>
								<td>
									<input type="time" id="um-video-library-time" name="um_video_library_time" value="<?php echo esc_attr($form_time); ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e('Display Mode', 'user-manager'); ?></th>
								<td>
									<label for="um-video-library-is-vertical">
										<input type="checkbox" id="um-video-library-is-vertical" name="um_video_library_is_vertical" value="1" <?php checked($form_is_vertical); ?> />
										<?php esc_html_e('Vertical (Short / 9:16)', 'user-manager'); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e('Library Tags', 'user-manager'); ?></th>
								<td>
									<input type="hidden" id="um-video-library-tag-slugs" name="um_video_library_tag_slugs" value="<?php echo esc_attr($form_tag_slugs_csv); ?>" />
									<div id="um-video-library-tag-picker" class="um-video-library-tag-picker" data-selected="<?php echo esc_attr($form_tag_slugs_csv); ?>">
										<?php if (!empty($tag_options)) : ?>
											<?php foreach ($tag_options as $tag_slug => $tag_name) : ?>
												<button type="button" class="button um-video-library-tag-pill" data-tag-slug="<?php echo esc_attr((string) $tag_slug); ?>"><?php echo esc_html((string) $tag_name); ?></button>
											<?php endforeach; ?>
										<?php else : ?>
											<p class="description" style="margin:0;"><?php esc_html_e('No Library Tags found yet. Create Library Tags first under Media > Library Tags.', 'user-manager'); ?></p>
										<?php endif; ?>
									</div>
									<p class="description" style="margin-top:8px;"><?php esc_html_e('Click tags to attach this video to one or more Library Tags.', 'user-manager'); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					<?php submit_button($form_id !== '' ? __('Update Video', 'user-manager') : __('Save Video', 'user-manager')); ?>
				</form>
			</div>

			<h2 style="margin-top:28px;"><?php esc_html_e('Saved Videos', 'user-manager'); ?></h2>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="user_manager_media_library_tag_video_library_bulk_save" />
				<?php wp_nonce_field('user_manager_media_library_tag_video_library_bulk_save', 'user_manager_media_library_tag_video_library_bulk_save_nonce'); ?>
				<table class="widefat fixed striped">
					<thead>
						<tr>
							<th style="width:20%;"><?php esc_html_e('Title', 'user-manager'); ?></th>
							<th style="width:26%;"><?php esc_html_e('YouTube Link', 'user-manager'); ?></th>
							<th style="width:11%;"><?php esc_html_e('Date', 'user-manager'); ?></th>
							<th style="width:8%;"><?php esc_html_e('Time', 'user-manager'); ?></th>
							<th style="width:8%;"><?php esc_html_e('Vertical', 'user-manager'); ?></th>
							<th style="width:22%;"><?php esc_html_e('Tags', 'user-manager'); ?></th>
							<th style="width:15%;"><?php esc_html_e('Description', 'user-manager'); ?></th>
							<th style="width:6%;"><?php esc_html_e('Actions', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($items)) : ?>
							<tr>
								<td colspan="8"><?php esc_html_e('No videos saved yet.', 'user-manager'); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ($items as $item) : ?>
								<?php if (!is_array($item) || empty($item['id']) || empty($item['youtubeUrl'])) { continue; } ?>
								<?php
								$row_title = isset($item['title']) ? (string) $item['title'] : '';
								$row_description = isset($item['description']) ? (string) $item['description'] : '';
								$row_date = isset($item['videoDate']) ? (string) $item['videoDate'] : '';
								$row_time = isset($item['videoTime']) ? (string) $item['videoTime'] : '';
								$row_is_vertical = !empty($item['isVertical']);
								$row_tag_slugs = isset($item['tagSlugs']) && is_array($item['tagSlugs']) ? $item['tagSlugs'] : [];
								$row_tag_slugs_csv = implode(', ', array_values(array_filter(array_map('sanitize_title', array_map('strval', $row_tag_slugs)))));
								$delete_url = wp_nonce_url(
									add_query_arg(
										[
											'action' => 'user_manager_media_library_tag_video_library_delete',
											'video_id' => (string) $item['id'],
										],
										admin_url('admin-post.php')
									),
									'user_manager_media_library_tag_video_library_delete_' . (string) $item['id']
								);
								?>
								<tr>
									<td>
										<input type="hidden" name="um_video_library_rows[<?php echo esc_attr((string) $item['id']); ?>][id]" value="<?php echo esc_attr((string) $item['id']); ?>" />
										<input type="text" class="regular-text" style="width:100%;" name="um_video_library_rows[<?php echo esc_attr((string) $item['id']); ?>][title]" value="<?php echo esc_attr($row_title); ?>" />
									</td>
									<td>
										<input type="url" class="regular-text code" style="width:100%;" name="um_video_library_rows[<?php echo esc_attr((string) $item['id']); ?>][youtubeUrl]" value="<?php echo esc_attr((string) $item['youtubeUrl']); ?>" placeholder="https://www.youtube.com/watch?v=..." />
									</td>
									<td>
										<input type="date" style="width:100%;" name="um_video_library_rows[<?php echo esc_attr((string) $item['id']); ?>][videoDate]" value="<?php echo esc_attr($row_date); ?>" />
									</td>
									<td>
										<input type="time" style="width:100%;" name="um_video_library_rows[<?php echo esc_attr((string) $item['id']); ?>][videoTime]" value="<?php echo esc_attr($row_time); ?>" />
									</td>
									<td style="text-align:center;">
										<input type="checkbox" name="um_video_library_rows[<?php echo esc_attr((string) $item['id']); ?>][isVertical]" value="1" <?php checked($row_is_vertical); ?> />
									</td>
									<td>
										<input type="text" class="regular-text" style="width:100%;" name="um_video_library_rows[<?php echo esc_attr((string) $item['id']); ?>][tagSlugs]" value="<?php echo esc_attr($row_tag_slugs_csv); ?>" placeholder="tag-one, tag-two" />
									</td>
									<td>
										<textarea rows="2" style="width:100%;" name="um_video_library_rows[<?php echo esc_attr((string) $item['id']); ?>][description]"><?php echo esc_textarea($row_description); ?></textarea>
									</td>
									<td>
										<a class="button button-small" style="margin-top:6px;" href="<?php echo esc_url($delete_url); ?>" onclick="return window.confirm(<?php echo wp_json_encode(__('Delete this video?', 'user-manager')); ?>);"><?php esc_html_e('Delete', 'user-manager'); ?></a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
				<?php if (!empty($items)) : ?>
					<p style="margin-top:12px;">
						<button type="submit" class="button button-primary"><?php esc_html_e('Save All Videos', 'user-manager'); ?></button>
					</p>
				<?php endif; ?>
			</form>
		</div>
		<style>
		.um-video-library-form-wrap {
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 6px;
			padding: 18px;
			max-width: 980px;
		}
		.um-video-library-tag-picker {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			max-width: 980px;
		}
		.um-video-library-tag-pill.is-selected {
			background: #135e96;
			border-color: #135e96;
			color: #fff;
		}
		</style>
		<script>
		(function(){
			var picker = document.getElementById('um-video-library-tag-picker');
			var hiddenInput = document.getElementById('um-video-library-tag-slugs');
			if (!picker || !hiddenInput) {
				return;
			}
			var selected = {};
			function normalizeSlug(value) {
				return String(value || '').toLowerCase().replace(/[^a-z0-9\-_]/g, '');
			}
			function readInitial() {
				var raw = String(picker.getAttribute('data-selected') || hiddenInput.value || '');
				raw.split(',').forEach(function(slug){
					var normalized = normalizeSlug(slug.trim());
					if (normalized) {
						selected[normalized] = true;
					}
				});
			}
			function syncHiddenField() {
				hiddenInput.value = Object.keys(selected).join(',');
			}
			function syncPills() {
				var pills = picker.querySelectorAll('.um-video-library-tag-pill');
				for (var i = 0; i < pills.length; i++) {
					var pill = pills[i];
					var slug = normalizeSlug(pill.getAttribute('data-tag-slug'));
					if (slug && selected[slug]) {
						pill.classList.add('is-selected');
						pill.setAttribute('aria-pressed', 'true');
					} else {
						pill.classList.remove('is-selected');
						pill.setAttribute('aria-pressed', 'false');
					}
				}
			}
			readInitial();
			syncPills();
			syncHiddenField();
			picker.addEventListener('click', function(event){
				var target = event.target;
				if (!target || !target.classList || !target.classList.contains('um-video-library-tag-pill')) {
					return;
				}
				event.preventDefault();
				var slug = normalizeSlug(target.getAttribute('data-tag-slug'));
				if (!slug) {
					return;
				}
				if (selected[slug]) {
					delete selected[slug];
				} else {
					selected[slug] = true;
				}
				syncPills();
				syncHiddenField();
			});
		})();
		</script>
		<?php
	}

	/**
	 * Handle Video Library save (create/update).
	 */
	public static function handle_media_library_tag_video_library_save(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage the Video Library.', 'user-manager'));
		}
		check_admin_referer('user_manager_media_library_tag_video_library_save', 'user_manager_media_library_tag_video_library_nonce');

		$video_id = isset($_POST['um_video_library_video_id']) ? sanitize_text_field((string) wp_unslash($_POST['um_video_library_video_id'])) : '';
		$raw_url = isset($_POST['um_video_library_youtube_url']) ? (string) wp_unslash($_POST['um_video_library_youtube_url']) : '';
		$raw_title = isset($_POST['um_video_library_title']) ? (string) wp_unslash($_POST['um_video_library_title']) : '';
		$raw_description = isset($_POST['um_video_library_description']) ? (string) wp_unslash($_POST['um_video_library_description']) : '';
		$raw_date = isset($_POST['um_video_library_date']) ? (string) wp_unslash($_POST['um_video_library_date']) : '';
		$raw_time = isset($_POST['um_video_library_time']) ? (string) wp_unslash($_POST['um_video_library_time']) : '';
		$raw_tag_slugs_csv = isset($_POST['um_video_library_tag_slugs']) ? (string) wp_unslash($_POST['um_video_library_tag_slugs']) : '';
		$is_vertical = isset($_POST['um_video_library_is_vertical']) && (string) wp_unslash($_POST['um_video_library_is_vertical']) === '1';

		$video_url = self::sanitize_media_library_tag_video_library_youtube_url($raw_url);
		if ($video_url === '') {
			$redirect_url = add_query_arg(
				[
					'page' => 'um-media-library-tag-video-library',
					'um_video_library_notice' => 'invalid_youtube',
				],
				admin_url('upload.php')
			);
			wp_safe_redirect($redirect_url);
			exit;
		}

		$sanitized_title = sanitize_text_field($raw_title);
		$sanitized_description = sanitize_textarea_field($raw_description);
		$sanitized_date = self::sanitize_media_library_tag_video_library_date($raw_date);
		$sanitized_time = self::sanitize_media_library_tag_video_library_time($raw_time);
		$sanitized_tag_slugs = self::sanitize_media_library_tag_video_library_tag_slugs($raw_tag_slugs_csv);

		$items = self::get_media_library_tag_video_library_items();
		$normalized_video_id = $video_id !== '' ? sanitize_text_field($video_id) : '';
		$upserted_item = [
			'id' => $normalized_video_id !== '' ? $normalized_video_id : self::build_media_library_tag_video_library_id(),
			'youtubeUrl' => $video_url,
			'title' => $sanitized_title,
			'description' => $sanitized_description,
			'videoDate' => $sanitized_date,
			'videoTime' => $sanitized_time,
			'isVertical' => $is_vertical,
			'tagSlugs' => $sanitized_tag_slugs,
		];

		$updated = false;
		foreach ($items as $index => $item) {
			if (!is_array($item)) {
				continue;
			}
			$item_id = isset($item['id']) ? sanitize_key((string) $item['id']) : '';
			if ($item_id === '' || $item_id !== $upserted_item['id']) {
				continue;
			}
			$items[$index] = $upserted_item;
			$updated = true;
			break;
		}
		if (!$updated) {
			$items[] = $upserted_item;
		}

		self::update_media_library_tag_video_library_items($items);

		$redirect_url = add_query_arg(
			[
				'page' => 'um-media-library-tag-video-library',
				'um_video_library_notice' => 'saved',
			],
			admin_url('upload.php')
		);
		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	 * Handle Video Library bulk save from table rows.
	 */
	public static function handle_media_library_tag_video_library_bulk_save(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage the Video Library.', 'user-manager'));
		}
		check_admin_referer('user_manager_media_library_tag_video_library_bulk_save', 'user_manager_media_library_tag_video_library_bulk_save_nonce');

		$rows = isset($_POST['um_video_library_rows']) ? wp_unslash($_POST['um_video_library_rows']) : [];
		if (!is_array($rows) || empty($rows)) {
			wp_safe_redirect(
				add_query_arg(
					[
						'page' => 'um-media-library-tag-video-library',
					],
					admin_url('upload.php')
				)
			);
			exit;
		}

		$existing_items = self::get_media_library_tag_video_library_items();
		$existing_by_id = [];
		foreach ($existing_items as $item) {
			if (!is_array($item)) {
				continue;
			}
			$item_id = isset($item['id']) ? sanitize_key((string) $item['id']) : '';
			if ($item_id === '') {
				continue;
			}
			$existing_by_id[$item_id] = $item;
		}

		$sanitized_items = [];
		$has_invalid_url = false;
		foreach ($rows as $row) {
			if (!is_array($row)) {
				continue;
			}
			$row_id = isset($row['id']) ? sanitize_key((string) $row['id']) : '';
			if ($row_id === '' || !isset($existing_by_id[$row_id])) {
				continue;
			}

			$raw_url = isset($row['youtubeUrl']) ? (string) $row['youtubeUrl'] : '';
			$video_url = self::sanitize_media_library_tag_video_library_youtube_url($raw_url);
			if ($video_url === '') {
				$has_invalid_url = true;
				$sanitized_items[] = $existing_by_id[$row_id];
				continue;
			}

			$raw_title = isset($row['title']) ? (string) $row['title'] : '';
			$raw_description = isset($row['description']) ? (string) $row['description'] : '';
			$raw_date = isset($row['videoDate']) ? (string) $row['videoDate'] : '';
			$raw_time = isset($row['videoTime']) ? (string) $row['videoTime'] : '';
			$raw_tag_slugs_csv = isset($row['tagSlugs']) ? (string) $row['tagSlugs'] : '';
			$row_is_vertical = isset($row['isVertical']) && (string) $row['isVertical'] === '1';
			$sanitized_items[] = [
				'id' => $row_id,
				'youtubeUrl' => $video_url,
				'title' => sanitize_text_field($raw_title),
				'description' => sanitize_textarea_field($raw_description),
				'videoDate' => self::sanitize_media_library_tag_video_library_date($raw_date),
				'videoTime' => self::sanitize_media_library_tag_video_library_time($raw_time),
				'isVertical' => $row_is_vertical,
				'tagSlugs' => self::sanitize_media_library_tag_video_library_tag_slugs($raw_tag_slugs_csv),
			];
		}

		self::update_media_library_tag_video_library_items($sanitized_items);

		$redirect_args = [
			'page' => 'um-media-library-tag-video-library',
			'um_video_library_notice' => $has_invalid_url ? 'bulk_invalid_youtube' : 'bulk_saved',
		];
		wp_safe_redirect(add_query_arg($redirect_args, admin_url('upload.php')));
		exit;
	}

	/**
	 * Handle Video Library delete.
	 */
	public static function handle_media_library_tag_video_library_delete(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage the Video Library.', 'user-manager'));
		}
		$video_id = isset($_GET['video_id']) ? sanitize_key((string) wp_unslash($_GET['video_id'])) : '';
		if ($video_id === '') {
			wp_safe_redirect(
				add_query_arg(
					[
						'page' => 'um-media-library-tag-video-library',
					],
					admin_url('upload.php')
				)
			);
			exit;
		}
		check_admin_referer('user_manager_media_library_tag_video_library_delete_' . $video_id);

		$items = self::get_media_library_tag_video_library_items();
		$kept_items = [];
		foreach ($items as $item) {
			if (!is_array($item)) {
				continue;
			}
			$item_id = isset($item['id']) ? sanitize_key((string) $item['id']) : '';
			if ($item_id !== '' && hash_equals($video_id, $item_id)) {
				continue;
			}
			$kept_items[] = $item;
		}
		self::update_media_library_tag_video_library_items($kept_items);

		wp_safe_redirect(
			add_query_arg(
				[
					'page' => 'um-media-library-tag-video-library',
					'um_video_library_notice' => 'deleted',
				],
				admin_url('upload.php')
			)
		);
		exit;
	}

	/**
	 * Render one Video Library summary cell for Bulk Editor.
	 */
	public static function render_media_library_tag_video_library_summary_cell_html(string $term_slug): string {
		$term_slug = sanitize_title($term_slug);
		if ($term_slug === '') {
			return '—';
		}
		self::maybe_migrate_legacy_term_youtube_links_to_video_library();
		$settings = User_Manager_Core::get_settings();
		if (empty($settings['media_library_tag_video_library_enabled'])) {
			return '<span style="color:#50575e;">' . esc_html__('Video Library is disabled. Enable "Activate Video Library" in plugin settings.', 'user-manager') . '</span>';
		}

		$records = self::get_media_library_tag_video_library_records_for_slugs([$term_slug]);
		$manage_url = add_query_arg(
			[
				'page' => 'um-media-library-tag-video-library',
			],
			admin_url('upload.php')
		);

		if (empty($records)) {
			return '<span style="display:block;margin-bottom:6px;color:#50575e;">' . esc_html__('No videos assigned.', 'user-manager') . '</span>'
				. '<a href="' . esc_url($manage_url) . '">' . esc_html__('Manage in Video Library', 'user-manager') . '</a>';
		}

		$list_items = [];
		$max_items = 3;
		$count = 0;
		foreach ($records as $record) {
			if (!is_array($record) || empty($record['youtubeUrl'])) {
				continue;
			}
			$row_title = isset($record['title']) && trim((string) $record['title']) !== ''
				? (string) $record['title']
				: (string) $record['youtubeUrl'];
			$list_items[] = '<li><a href="' . esc_url((string) $record['youtubeUrl']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($row_title) . '</a></li>';
			$count++;
			if ($count >= $max_items) {
				break;
			}
		}
		$more_count = max(0, count($records) - $count);

		$html = '<ul style="margin:0 0 8px 18px;list-style:disc;">' . implode('', $list_items) . '</ul>';
		if ($more_count > 0) {
			$html .= '<span style="display:block;margin-bottom:6px;color:#50575e;">'
				. esc_html(
					sprintf(
						/* translators: %d: number of hidden videos */
						__('and %d more...', 'user-manager'),
						$more_count
					)
				)
				. '</span>';
		}
		$html .= '<a href="' . esc_url($manage_url) . '">' . esc_html__('Manage in Video Library', 'user-manager') . '</a>';

		return $html;
	}

	/**
	 * Return Video Library records matching one or more tag slugs.
	 *
	 * @param array<int,string> $tag_slugs
	 * @return array<int,array<string,mixed>>
	 */
	private static function get_media_library_tag_video_library_records_for_slugs(array $tag_slugs): array {
		if (empty($tag_slugs)) {
			return [];
		}
		$requested_slugs = [];
		foreach ($tag_slugs as $tag_slug) {
			$tag_slug = sanitize_title((string) $tag_slug);
			if ($tag_slug === '') {
				continue;
			}
			$requested_slugs[$tag_slug] = true;
		}
		if (empty($requested_slugs)) {
			return [];
		}

		$items = self::get_media_library_tag_video_library_items();
		$matches = [];
		$seen_urls = [];
		foreach ($items as $item) {
			if (!is_array($item) || empty($item['youtubeUrl'])) {
				continue;
			}
			$item_tag_slugs = isset($item['tagSlugs']) && is_array($item['tagSlugs'])
				? $item['tagSlugs']
				: [];
			$item_has_requested_tag = false;
			foreach ($item_tag_slugs as $item_tag_slug) {
				$item_tag_slug = sanitize_title((string) $item_tag_slug);
				if ($item_tag_slug !== '' && isset($requested_slugs[$item_tag_slug])) {
					$item_has_requested_tag = true;
					break;
				}
			}
			if (!$item_has_requested_tag) {
				continue;
			}
			$youtube_url = (string) $item['youtubeUrl'];
			if ($youtube_url === '' || isset($seen_urls[$youtube_url])) {
				continue;
			}
			$seen_urls[$youtube_url] = true;
			$matches[] = $item;
		}

		return $matches;
	}

	/**
	 * Read and sanitize all saved Video Library items.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private static function get_media_library_tag_video_library_items(): array {
		$raw_items = get_option(self::media_library_tag_video_library_option_key(), []);
		if (!is_array($raw_items)) {
			return [];
		}

		$sanitized = [];
		foreach ($raw_items as $raw_item) {
			if (!is_array($raw_item)) {
				continue;
			}
			$item = self::sanitize_media_library_tag_video_library_item($raw_item);
			if ($item === null) {
				continue;
			}
			$sanitized[] = $item;
		}

		usort($sanitized, static function (array $a, array $b): int {
			$date_a = isset($a['videoDate']) ? (string) $a['videoDate'] : '';
			$date_b = isset($b['videoDate']) ? (string) $b['videoDate'] : '';
			$time_a = isset($a['videoTime']) ? (string) $a['videoTime'] : '';
			$time_b = isset($b['videoTime']) ? (string) $b['videoTime'] : '';
			if ($date_a === '' && $date_b === '') {
				$title_a = strtolower((string) ($a['title'] ?? ''));
				$title_b = strtolower((string) ($b['title'] ?? ''));
				return $title_a <=> $title_b;
			}
			if ($date_a === '') {
				return 1;
			}
			if ($date_b === '') {
				return -1;
			}
			if ($date_a !== $date_b) {
				return strcmp($date_b, $date_a);
			}
			if ($time_a === $time_b) {
				$title_a = strtolower((string) ($a['title'] ?? ''));
				$title_b = strtolower((string) ($b['title'] ?? ''));
				return $title_a <=> $title_b;
			}
			if ($time_a === '') {
				return 1;
			}
			if ($time_b === '') {
				return -1;
			}
			return strcmp($time_b, $time_a);
		});

		return $sanitized;
	}

	/**
	 * Persist sanitized Video Library items.
	 *
	 * @param array<int,mixed> $items
	 */
	private static function update_media_library_tag_video_library_items(array $items): void {
		$sanitized_items = [];
		foreach ($items as $item) {
			if (!is_array($item)) {
				continue;
			}
			$sanitized_item = self::sanitize_media_library_tag_video_library_item($item);
			if ($sanitized_item === null) {
				continue;
			}
			$sanitized_items[] = $sanitized_item;
		}

		update_option(self::media_library_tag_video_library_option_key(), $sanitized_items);
	}

	/**
	 * @param array<string,mixed> $item
	 * @return array<string,mixed>|null
	 */
	private static function sanitize_media_library_tag_video_library_item(array $item): ?array {
		$id = isset($item['id']) ? sanitize_key((string) $item['id']) : '';
		if ($id === '') {
			$id = self::build_media_library_tag_video_library_id();
		}
		$youtube_url = self::sanitize_media_library_tag_video_library_youtube_url((string) ($item['youtubeUrl'] ?? ''));
		if ($youtube_url === '') {
			return null;
		}

		$title = sanitize_text_field((string) ($item['title'] ?? ''));
		$description = sanitize_textarea_field((string) ($item['description'] ?? ''));
		$video_date = self::sanitize_media_library_tag_video_library_date((string) ($item['videoDate'] ?? ''));
		$video_time = self::sanitize_media_library_tag_video_library_time((string) ($item['videoTime'] ?? ''));
		$is_vertical = !empty($item['isVertical']);
		$tag_slugs = isset($item['tagSlugs']) && is_array($item['tagSlugs']) ? $item['tagSlugs'] : [];
		$tag_slugs_csv = implode(',', array_map('strval', $tag_slugs));
		$tag_slugs = self::sanitize_media_library_tag_video_library_tag_slugs($tag_slugs_csv);

		return [
			'id' => $id,
			'youtubeUrl' => $youtube_url,
			'title' => $title,
			'description' => $description,
			'videoDate' => $video_date,
			'videoTime' => $video_time,
			'isVertical' => $is_vertical,
			'tagSlugs' => $tag_slugs,
		];
	}

	/**
	 * Canonicalize one YouTube URL.
	 */
	private static function sanitize_media_library_tag_video_library_youtube_url(string $raw_url): string {
		$video_id = self::get_media_library_tag_youtube_video_id_from_url($raw_url);
		if ($video_id === '') {
			return '';
		}
		return 'https://www.youtube.com/watch?v=' . $video_id;
	}

	/**
	 * Sanitize one YYYY-MM-DD value.
	 */
	private static function sanitize_media_library_tag_video_library_date(string $raw_date): string {
		$raw_date = trim($raw_date);
		if ($raw_date === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_date) !== 1) {
			return '';
		}
		$parts = explode('-', $raw_date);
		if (count($parts) !== 3) {
			return '';
		}
		$year = (int) $parts[0];
		$month = (int) $parts[1];
		$day = (int) $parts[2];
		if (!checkdate($month, $day, $year)) {
			return '';
		}
		return sprintf('%04d-%02d-%02d', $year, $month, $day);
	}

	/**
	 * Sanitize one HH:MM (24-hour) time value.
	 */
	private static function sanitize_media_library_tag_video_library_time(string $raw_time): string {
		$raw_time = trim($raw_time);
		if ($raw_time === '' || preg_match('/^\d{2}:\d{2}$/', $raw_time) !== 1) {
			return '';
		}
		$parts = explode(':', $raw_time);
		if (count($parts) !== 2) {
			return '';
		}
		$hour = (int) $parts[0];
		$minute = (int) $parts[1];
		if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
			return '';
		}
		return sprintf('%02d:%02d', $hour, $minute);
	}

	/**
	 * Parse and sanitize CSV tag slugs against existing taxonomy terms.
	 *
	 * @return array<int,string>
	 */
	private static function sanitize_media_library_tag_video_library_tag_slugs(string $raw_csv): array {
		$requested_slugs = array_filter(array_map('sanitize_title', array_map('trim', explode(',', $raw_csv))));
		if (empty($requested_slugs)) {
			return [];
		}
		$requested_map = [];
		foreach ($requested_slugs as $requested_slug) {
			if ($requested_slug === '') {
				continue;
			}
			$requested_map[$requested_slug] = true;
		}
		if (empty($requested_map)) {
			return [];
		}

		$valid_slugs = [];
		$terms = get_terms([
			'taxonomy' => self::media_library_tags_taxonomy(),
			'hide_empty' => false,
			'fields' => 'slugs',
		]);
		if (is_array($terms) && !is_wp_error($terms)) {
			foreach ($terms as $term_slug) {
				$term_slug = sanitize_title((string) $term_slug);
				if ($term_slug !== '' && isset($requested_map[$term_slug])) {
					$valid_slugs[$term_slug] = $term_slug;
				}
			}
		}

		return array_values($valid_slugs);
	}

	/**
	 * Build a stable-ish unique item ID for new video records.
	 */
	private static function build_media_library_tag_video_library_id(): string {
		return sanitize_key('umv_' . str_replace('.', '', uniqid('', true)));
	}

	/**
	 * One-time migration from legacy per-term YouTube links meta.
	 */
	private static function maybe_migrate_legacy_term_youtube_links_to_video_library(): void {
		if (!did_action('init')) {
			return;
		}

		$already_migrated = get_option(self::media_library_tag_video_library_legacy_migration_flag_option_key(), false);
		if ($already_migrated) {
			return;
		}

		$taxonomy = self::media_library_tags_taxonomy();
		if (!taxonomy_exists($taxonomy)) {
			self::register_media_library_tags_taxonomy();
		}
		if (!taxonomy_exists($taxonomy)) {
			return;
		}
		$terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
		]);
		if (is_wp_error($terms)) {
			return;
		}
		if (!is_array($terms)) {
			return;
		}

		$items = self::get_media_library_tag_video_library_items();
		$items_by_url = [];
		foreach ($items as $index => $item) {
			if (!is_array($item) || empty($item['youtubeUrl'])) {
				continue;
			}
			$url = (string) $item['youtubeUrl'];
			$items_by_url[$url] = $index;
		}

		$updated = false;
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$term_slug = sanitize_title((string) $term->slug);
			if ($term_slug === '') {
				continue;
			}

			$legacy_urls = self::get_legacy_media_library_tag_youtube_urls_for_term((int) $term->term_id);
			foreach ($legacy_urls as $legacy_url) {
				$legacy_url = (string) $legacy_url;
				if ($legacy_url === '') {
					continue;
				}

				if (isset($items_by_url[$legacy_url]) && isset($items[$items_by_url[$legacy_url]]) && is_array($items[$items_by_url[$legacy_url]])) {
					$item_index = (int) $items_by_url[$legacy_url];
					$existing_tags = isset($items[$item_index]['tagSlugs']) && is_array($items[$item_index]['tagSlugs'])
						? array_values(array_filter(array_map('sanitize_title', array_map('strval', $items[$item_index]['tagSlugs']))))
						: [];
					if (!in_array($term_slug, $existing_tags, true)) {
						$existing_tags[] = $term_slug;
						$items[$item_index]['tagSlugs'] = array_values(array_unique($existing_tags));
						$updated = true;
					}
					continue;
				}

				$items[] = [
					'id' => self::build_media_library_tag_video_library_id(),
					'youtubeUrl' => $legacy_url,
					'title' => '',
					'description' => '',
					'videoDate' => '',
					'videoTime' => '',
					'isVertical' => false,
					'tagSlugs' => [$term_slug],
				];
				$items_by_url[$legacy_url] = count($items) - 1;
				$updated = true;
			}
		}

		if ($updated) {
			self::update_media_library_tag_video_library_items($items);
		}
		update_option(self::media_library_tag_video_library_legacy_migration_flag_option_key(), true);
	}

	/**
	 * Parse legacy per-term YouTube links meta into canonical URLs.
	 *
	 * @return array<int,string>
	 */
	private static function get_legacy_media_library_tag_youtube_urls_for_term(int $term_id): array {
		if ($term_id <= 0) {
			return [];
		}
		$raw_meta = get_term_meta($term_id, 'um_media_library_tag_youtube_links', true);
		$lines = preg_split('/\r\n|\r|\n/', (string) $raw_meta);
		if (!is_array($lines)) {
			return [];
		}

		$urls = [];
		foreach ($lines as $line) {
			$video_id = self::get_media_library_tag_youtube_video_id_from_url((string) $line);
			if ($video_id === '') {
				continue;
			}
			$url = 'https://www.youtube.com/watch?v=' . $video_id;
			$urls[$url] = $url;
		}
		return array_values($urls);
	}
}

