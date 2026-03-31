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
		add_action('admin_init', [__CLASS__, 'handle_media_library_bulk_apply_tag']);
		add_action('admin_notices', [__CLASS__, 'maybe_render_media_library_tags_admin_notice']);
		add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_media_library_tags_admin_assets']);
		add_action('wp_ajax_user_manager_bulk_apply_media_library_tag', [__CLASS__, 'ajax_bulk_apply_media_library_tag']);
		add_filter('attachment_fields_to_edit', [__CLASS__, 'add_media_library_tags_attachment_field'], 10, 2);
		add_filter('attachment_fields_to_save', [__CLASS__, 'save_media_library_tags_attachment_field'], 10, 2);
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
					'all_items' => __('All Library Tags', 'user-manager'),
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
	 * Render list-view media filter dropdown and bulk apply controls.
	 *
	 * @param string $post_type Current post type.
	 * @param string $which     Top or bottom controls.
	 */
	public static function render_media_library_tag_filter_controls(string $post_type = '', string $which = 'top'): void {
		global $pagenow;
		if ($pagenow !== 'upload.php' || $post_type !== 'attachment') {
			return;
		}

		$taxonomy = self::media_library_tags_taxonomy();
		if (!taxonomy_exists($taxonomy)) {
			return;
		}

		$selected_filter = self::get_requested_media_library_tag_slug();
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
			<option value=""><?php esc_html_e('All Library Tags', 'user-manager'); ?></option>
			<?php foreach ($terms as $term) : ?>
				<?php if (!$term instanceof WP_Term) { continue; } ?>
				<option value="<?php echo esc_attr($term->slug); ?>" <?php selected($selected_filter, (string) $term->slug); ?>>
					<?php echo esc_html($term->name); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php

		if ($which !== 'top') {
			return;
		}
		?>
		<label for="um-media-library-bulk-tag" class="screen-reader-text"><?php esc_html_e('Choose Library Tag for bulk apply', 'user-manager'); ?></label>
		<select id="um-media-library-bulk-tag" name="um_media_library_bulk_tag">
			<option value=""><?php esc_html_e('Bulk apply: choose Library Tag', 'user-manager'); ?></option>
			<?php foreach ($terms as $term) : ?>
				<?php if (!$term instanceof WP_Term) { continue; } ?>
				<option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
			<?php endforeach; ?>
		</select>
		<?php submit_button(__('Apply Library Tag', 'user-manager'), 'secondary', 'um_media_library_bulk_apply', false); ?>
		<?php wp_nonce_field('um_media_library_bulk_apply_action', 'um_media_library_bulk_apply_nonce', false); ?>
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

		$tag_slug = self::get_requested_media_library_tag_slug();
		if ($tag_slug === '') {
			return;
		}

		$tax_query = $query->get('tax_query');
		if (!is_array($tax_query)) {
			$tax_query = [];
		}
		$tax_query[] = [
			'taxonomy' => self::media_library_tags_taxonomy(),
			'field' => 'slug',
			'terms' => [$tag_slug],
		];
		$query->set('tax_query', $tax_query);
	}

	/**
	 * Apply grid/ajax query filtering by selected Library Tag.
	 *
	 * @param array<string,mixed> $query Query args.
	 * @return array<string,mixed>
	 */
	public static function filter_media_library_ajax_query_by_tag(array $query): array {
		$tag_slug = '';

		if (isset($query['um_media_library_tag'])) {
			$tag_slug = sanitize_title((string) $query['um_media_library_tag']);
		}
		if ($tag_slug === '' && isset($_REQUEST['query']) && is_array($_REQUEST['query']) && isset($_REQUEST['query']['um_media_library_tag'])) {
			$tag_slug = sanitize_title((string) wp_unslash($_REQUEST['query']['um_media_library_tag']));
		}
		if ($tag_slug === '') {
			$tag_slug = self::get_requested_media_library_tag_slug();
		}
		if ($tag_slug === '') {
			return $query;
		}

		$tax_query = isset($query['tax_query']) && is_array($query['tax_query']) ? $query['tax_query'] : [];
		$tax_query[] = [
			'taxonomy' => self::media_library_tags_taxonomy(),
			'field' => 'slug',
			'terms' => [$tag_slug],
		];
		$query['tax_query'] = $tax_query;
		return $query;
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

		$tag_slug = isset($_POST['um_media_library_bulk_tag']) ? sanitize_title(wp_unslash($_POST['um_media_library_bulk_tag'])) : '';
		$media_ids = isset($_POST['media']) && is_array($_POST['media']) ? array_map('absint', wp_unslash($_POST['media'])) : [];
		$redirect_url = wp_get_referer() ?: admin_url('upload.php');
		$redirect_url = remove_query_arg(['um_media_library_tags_notice', 'um_media_library_tags_count'], $redirect_url);

		if (empty($media_ids)) {
			wp_safe_redirect(add_query_arg('um_media_library_tags_notice', 'no_selection', $redirect_url));
			exit;
		}
		if ($tag_slug === '' || !term_exists($tag_slug, self::media_library_tags_taxonomy())) {
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
	 * Handle grid-view bulk apply via AJAX.
	 */
	public static function ajax_bulk_apply_media_library_tag(): void {
		if (!current_user_can('upload_files')) {
			wp_send_json_error(['message' => __('You do not have permission to update media tags.', 'user-manager')], 403);
		}
		check_ajax_referer('um_media_library_tags_ajax', 'nonce');

		$tag_slug = isset($_POST['tag']) ? sanitize_title(wp_unslash($_POST['tag'])) : '';
		$raw_ids = isset($_POST['ids']) && is_array($_POST['ids']) ? wp_unslash($_POST['ids']) : [];
		$ids = array_map('absint', $raw_ids);

		if ($tag_slug === '' || !term_exists($tag_slug, self::media_library_tags_taxonomy())) {
			wp_send_json_error(['message' => __('Please choose a valid Library Tag.', 'user-manager')], 400);
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
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('Choose a Library Tag before applying to selected media.', 'user-manager') . '</p></div>';
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

		$form_fields['um_media_library_tags'] = [
			'label' => __('Library Tags', 'user-manager'),
			'input' => 'html',
			'html'  => '<input type="text" class="text" name="attachments[' . (int) $post->ID . '][um_media_library_tags]" value="' . esc_attr($value) . '" />',
			'helps' => __('Comma-separated tags. Add new tags or remove existing tags for this media item.', 'user-manager'),
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

		$config = [
			'selectedTag' => self::get_requested_media_library_tag_slug(),
			'terms' => $term_options,
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('um_media_library_tags_ajax'),
			'labels' => [
				'filterAll' => __('All Library Tags', 'user-manager'),
				'bulkChoose' => __('Bulk apply: choose Library Tag', 'user-manager'),
				'bulkButton' => __('Apply Library Tag', 'user-manager'),
				'bulkNoSelection' => __('Select one or more media items first.', 'user-manager'),
				'bulkNoTag' => __('Choose a Library Tag first.', 'user-manager'),
			],
		];

		wp_register_script('um-media-library-tags-admin', false, ['jquery'], self::VERSION, true);
		wp_add_inline_script('um-media-library-tags-admin', 'window.umMediaLibraryTagsConfig = ' . wp_json_encode($config) . ';', 'before');
		$script = <<<'JS'
(function($) {
	var cfg = window.umMediaLibraryTagsConfig || {};
	if (!cfg || !Array.isArray(cfg.terms)) {
		return;
	}

	function buildOptions(defaultLabel, selected) {
		var html = '<option value="">' + String(defaultLabel || '') + '</option>';
		cfg.terms.forEach(function(term) {
			if (!term || !term.slug) {
				return;
			}
			var isSelected = selected && selected === term.slug ? ' selected' : '';
			html += '<option value="' + String(term.slug).replace(/"/g, '&quot;') + '"' + isSelected + '>'
				+ String(term.name || term.slug).replace(/</g, '&lt;').replace(/>/g, '&gt;')
				+ '</option>';
		});
		return html;
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
		var filterHtml = buildOptions(cfg.labels && cfg.labels.filterAll, selectedTag);
		var bulkHtml = buildOptions(cfg.labels && cfg.labels.bulkChoose, '');
		var $filterLabel = $('<label class="screen-reader-text" for="um-media-library-tag-filter-grid">Library Tag filter</label>');
		var $filter = $('<select id="um-media-library-tag-filter-grid" class="attachment-filters"></select>').html(filterHtml);
		var $bulkLabel = $('<label class="screen-reader-text" for="um-media-library-tag-bulk-grid">Bulk apply Library Tag</label>');
		var $bulk = $('<select id="um-media-library-tag-bulk-grid" class="attachment-filters"></select>').html(bulkHtml);
		var $button = $('<button type="button" class="button media-button"></button>').text((cfg.labels && cfg.labels.bulkButton) || 'Apply Library Tag');

		$toolbar.append($filterLabel).append($filter).append($bulkLabel).append($bulk).append($button);

		$filter.on('change', function() {
			updateUrlParam('um_media_library_tag', $(this).val());
		});

		$button.on('click', function() {
			var tag = String($bulk.val() || '');
			var ids = getSelectedMediaIdsFromGrid();
			if (!ids.length) {
				window.alert((cfg.labels && cfg.labels.bulkNoSelection) || 'Select one or more media items first.');
				return;
			}
			if (!tag) {
				window.alert((cfg.labels && cfg.labels.bulkNoTag) || 'Choose a Library Tag first.');
				return;
			}

			$button.prop('disabled', true);
			$.post(cfg.ajaxUrl, {
				action: 'user_manager_bulk_apply_media_library_tag',
				nonce: cfg.nonce,
				tag: tag,
				ids: ids
			}).always(function() {
				window.location.reload();
			});
		});
	}

	$(function() {
		var isGridMode = $('body.upload-php.mode-grid').length > 0 || /[?&]mode=grid(?:&|$)/.test(window.location.search);
		if (isGridMode) {
			ensureGridControls();
			var tries = 0;
			var timer = window.setInterval(function() {
				ensureGridControls();
				tries++;
				if (tries > 20 || $('#um-media-library-tag-filter-grid').length) {
					window.clearInterval(timer);
				}
			}, 300);
		}

	});
})(jQuery);
JS;
		wp_add_inline_script('um-media-library-tags-admin', $script);
		wp_enqueue_script('um-media-library-tags-admin');
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
				'columnsDesktop' => ['type' => 'integer', 'default' => 4],
				'columnsMobile' => ['type' => 'integer', 'default' => 2],
				'sortOrder' => ['type' => 'string', 'default' => 'date_desc'],
				'fileSize' => ['type' => 'string', 'default' => 'thumbnail'],
				'style' => ['type' => 'string', 'default' => 'standard'],
				'pageLimit' => ['type' => 'integer', 'default' => 0],
				'linkTo' => ['type' => 'string', 'default' => 'none'],
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
			['label' => __('All Library Tags', 'user-manager'), 'value' => ''],
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

	var cfg = window.umMediaLibraryTagGalleryConfig || {};
	var defaults = cfg.defaults || {};
	var terms = Array.isArray(cfg.terms) ? cfg.terms : [{ label: 'All Library Tags', value: '' }];
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

	registerBlockType('custom/media-library-tag-gallery', {
		title: 'Media Library Tag Gallery',
		icon: 'format-gallery',
		category: 'widgets',
		attributes: {
			tagSlug: { type: 'string', default: '' },
			columnsDesktop: { type: 'integer', default: parseInt(defaults.columnsDesktop, 10) || 4 },
			columnsMobile: { type: 'integer', default: parseInt(defaults.columnsMobile, 10) || 2 },
			sortOrder: { type: 'string', default: defaults.sortOrder || 'date_desc' },
			fileSize: { type: 'string', default: defaults.fileSize || 'thumbnail' },
			style: { type: 'string', default: defaults.style || 'standard' },
			pageLimit: { type: 'integer', default: parseInt(defaults.pageLimit, 10) || 0 },
			linkTo: { type: 'string', default: defaults.linkTo || 'none' }
		},
		edit: function(props) {
			var a = props.attributes;
			var set = props.setAttributes;
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
						element.createElement(TextControl, {
							label: 'Number of Columns (Desktop)',
							type: 'number',
							min: 1,
							max: 8,
							value: a.columnsDesktop || 4,
							onChange: function(v){ set({ columnsDesktop: Math.max(1, parseInt(v, 10) || 1) }); }
						}),
						element.createElement(TextControl, {
							label: 'Number of Columns (Mobile)',
							type: 'number',
							min: 1,
							max: 4,
							value: a.columnsMobile || 2,
							onChange: function(v){ set({ columnsMobile: Math.max(1, parseInt(v, 10) || 1) }); }
						}),
						element.createElement(SelectControl, {
							label: 'Sort Order',
							value: a.sortOrder || 'date_desc',
							options: [
								{ label: 'Date ASC', value: 'date_asc' },
								{ label: 'Date DESC', value: 'date_desc' },
								{ label: 'ID ASC', value: 'id_asc' },
								{ label: 'ID DESC', value: 'id_desc' },
								{ label: 'Filename ASC', value: 'filename_asc' },
								{ label: 'Filename DESC', value: 'filename_desc' },
								{ label: 'Caption ASC', value: 'caption_asc' },
								{ label: 'Caption DESC', value: 'caption_desc' }
							],
							onChange: function(v){ set({ sortOrder: String(v || 'date_desc') }); }
						}),
						element.createElement(SelectControl, {
							label: 'File Size',
							value: a.fileSize || 'thumbnail',
							options: imageSizeOptions,
							onChange: function(v){ set({ fileSize: String(v || 'thumbnail') }); }
						}),
						element.createElement(SelectControl, {
							label: 'Style',
							value: a.style || 'standard',
							options: [
								{ label: 'Standard', value: 'standard' },
								{ label: 'Mosaic', value: 'mosaic' },
				{ label: 'Square CSS Crop', value: 'square_crop' },
				{ label: 'Wide Rectangle CSS Crop', value: 'wide_rectangle_crop' },
				{ label: 'Tall Rectangle CSS Crop', value: 'tall_rectangle_crop' },
				{ label: 'Circle CSS Crop', value: 'circle_crop' }
							],
							onChange: function(v){ set({ style: String(v || 'standard') }); }
						}),
						element.createElement(TextControl, {
							label: 'Page Limit (0 = unlimited)',
							type: 'number',
							min: 0,
							value: a.pageLimit || 0,
							onChange: function(v){ set({ pageLimit: Math.max(0, parseInt(v, 10) || 0) }); }
						}),
						element.createElement(SelectControl, {
							label: 'Link To',
							value: a.linkTo || 'none',
							options: [
								{ label: 'Nothing', value: 'none' },
								{ label: 'Lightbox', value: 'lightbox' },
								{ label: 'Open Media Library Permalink', value: 'media_permalink' }
							],
							onChange: function(v){ set({ linkTo: String(v || 'none') }); }
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
	 * Render callback for Media Library Tag Gallery block.
	 *
	 * @param array<string,mixed> $attrs Block attributes.
	 */
	public static function render_media_library_tags_gallery_block(array $attrs = []): string {
		$settings = User_Manager_Core::get_settings();
		$defaults = self::get_media_library_tag_gallery_defaults($settings);

		$tag_slug = isset($attrs['tagSlug']) ? sanitize_title((string) $attrs['tagSlug']) : '';
		$columns_desktop = max(1, min(8, absint($attrs['columnsDesktop'] ?? $defaults['columnsDesktop'])));
		$columns_mobile = max(1, min(4, absint($attrs['columnsMobile'] ?? $defaults['columnsMobile'])));
		$sort_order = isset($attrs['sortOrder']) ? sanitize_key((string) $attrs['sortOrder']) : (string) $defaults['sortOrder'];
		$file_size = isset($attrs['fileSize']) ? sanitize_key((string) $attrs['fileSize']) : (string) $defaults['fileSize'];
		$style = isset($attrs['style']) ? sanitize_key((string) $attrs['style']) : (string) $defaults['style'];
		$page_limit = max(0, absint($attrs['pageLimit'] ?? $defaults['pageLimit']));
		$link_to = isset($attrs['linkTo']) ? sanitize_key((string) $attrs['linkTo']) : (string) $defaults['linkTo'];

		$allowed_sort_orders = ['date_asc', 'date_desc', 'id_asc', 'id_desc', 'filename_asc', 'filename_desc', 'caption_asc', 'caption_desc'];
		$allowed_file_sizes = array_keys(self::get_available_image_sizes_for_media_gallery());
		if (empty($allowed_file_sizes)) {
			$allowed_file_sizes = ['thumbnail', 'medium', 'large', 'full'];
		}
		$allowed_styles = ['standard', 'mosaic', 'square_crop', 'wide_rectangle_crop', 'tall_rectangle_crop', 'circle_crop'];
		$allowed_links = ['none', 'lightbox', 'media_permalink'];
		if (!in_array($sort_order, $allowed_sort_orders, true)) {
			$sort_order = 'date_desc';
		}
		if (!in_array($file_size, $allowed_file_sizes, true)) {
			$file_size = 'thumbnail';
		}
		if (!in_array($style, $allowed_styles, true)) {
			$style = 'standard';
		}
		if (!in_array($link_to, $allowed_links, true)) {
			$link_to = 'none';
		}

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
			'offset' => $offset,
			'no_found_rows' => $page_limit <= 0,
		];

		if ($tag_slug !== '' && term_exists($tag_slug, self::media_library_tags_taxonomy())) {
			$query_args['tax_query'] = [
				[
					'taxonomy' => self::media_library_tags_taxonomy(),
					'field' => 'slug',
					'terms' => [$tag_slug],
				],
			];
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
		}

		$query = new WP_Query($query_args);
		$attachments = $query->posts;
		if (!is_array($attachments)) {
			$attachments = [];
		}

		$uid = function_exists('wp_unique_id') ? wp_unique_id('um-media-gallery-') : uniqid('um-media-gallery-');
		$style_class = 'um-media-gallery-style-' . $style;
		$total_pages = ($page_limit > 0 && isset($query->max_num_pages)) ? max(1, (int) $query->max_num_pages) : 1;

		ob_start();
		?>
		<div id="<?php echo esc_attr($uid); ?>" class="um-media-library-tag-gallery <?php echo esc_attr($style_class); ?>">
			<div class="um-media-library-tag-gallery-grid" style="--um-mltg-cols-desktop:<?php echo esc_attr((string) $columns_desktop); ?>;--um-mltg-cols-mobile:<?php echo esc_attr((string) $columns_mobile); ?>;">
				<?php foreach ($attachments as $attachment) : ?>
					<?php
					if (!($attachment instanceof WP_Post)) {
						continue;
					}
					$attachment_id = (int) $attachment->ID;
					$image_html = wp_get_attachment_image($attachment_id, $file_size, false, ['loading' => 'lazy']);
					if ($image_html === '') {
						continue;
					}
					$image_src = wp_get_attachment_image_url($attachment_id, 'full');
					$permalink = get_attachment_link($attachment_id);
					$caption = wp_get_attachment_caption($attachment_id);
					?>
					<figure class="um-media-library-tag-gallery-item">
					<?php if ($link_to === 'media_permalink' && $permalink) : ?>
							<a href="<?php echo esc_url($permalink); ?>" class="um-media-library-tag-gallery-link"><?php echo $image_html; ?></a>
						<?php elseif ($link_to === 'lightbox' && $image_src) : ?>
							<a href="<?php echo esc_url($image_src); ?>" class="um-media-library-tag-gallery-link" data-um-lightbox="1"><?php echo $image_html; ?></a>
						<?php else : ?>
							<?php echo $image_html; ?>
						<?php endif; ?>
						<?php if ($caption !== '') : ?>
							<figcaption class="um-media-library-tag-gallery-caption"><?php echo esc_html($caption); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
			<?php if ($page_limit > 0 && $total_pages > 1) : ?>
				<div class="um-media-library-tag-gallery-pagination">
					<?php for ($i = 1; $i <= $total_pages; $i++) : ?>
						<?php $page_url = add_query_arg('um_media_gallery_page', (string) $i); ?>
						<a href="<?php echo esc_url($page_url); ?>" class="<?php echo $i === $page_num ? 'current' : ''; ?>"><?php echo esc_html((string) $i); ?></a>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
		<style>
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
		.um-media-library-tag-gallery-item { margin: 0; }
		.um-media-library-tag-gallery-item img { width: 100%; height: auto; display: block; }
		.um-media-gallery-style-square_crop .um-media-library-tag-gallery-item img { aspect-ratio: 1 / 1; object-fit: cover; }
		.um-media-gallery-style-wide_rectangle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 16 / 9; object-fit: cover; }
		.um-media-gallery-style-tall_rectangle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 3 / 4; object-fit: cover; }
		.um-media-gallery-style-circle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 1 / 1; object-fit: cover; border-radius: 999px; }
		.um-media-gallery-style-mosaic .um-media-library-tag-gallery-item:nth-child(3n+1) { grid-column: span 2; }
		.um-media-library-tag-gallery-caption { margin-top: 6px; font-size: 12px; color: #50575e; }
		.um-media-library-tag-gallery-pagination { margin-top: 14px; display:flex; gap:8px; flex-wrap:wrap; }
		.um-media-library-tag-gallery-pagination a { text-decoration:none; padding:4px 8px; border:1px solid #dcdcde; border-radius:4px; }
		.um-media-library-tag-gallery-pagination a.current { background:#2271b1; border-color:#2271b1; color:#fff; }
		.um-mltg-lightbox-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.86); z-index: 999999; display: none; align-items: center; justify-content: center; padding: 30px; }
		.um-mltg-lightbox-overlay img { max-width: min(95vw, 1600px); max-height: 88vh; width: auto; height: auto; display: block; box-shadow: 0 4px 24px rgba(0,0,0,0.4); }
		.um-mltg-lightbox-close { position: absolute; top: 14px; right: 16px; border: 0; background: transparent; color: #fff; font-size: 36px; line-height: 1; cursor: pointer; }
		</style>
		<div class="um-mltg-lightbox-overlay" id="<?php echo esc_attr($uid); ?>-lightbox" aria-hidden="true">
			<button type="button" class="um-mltg-lightbox-close" aria-label="<?php esc_attr_e('Close image', 'user-manager'); ?>">&times;</button>
			<img src="" alt="" />
		</div>
		<script>
		(function() {
			var root = document.getElementById('<?php echo esc_js($uid); ?>');
			if (!root) { return; }
			var overlay = document.getElementById('<?php echo esc_js($uid); ?>-lightbox');
			if (!overlay) { return; }
			var closeBtn = overlay.querySelector('.um-mltg-lightbox-close');
			var image = overlay.querySelector('img');
			function closeOverlay() {
				overlay.style.display = 'none';
				overlay.setAttribute('aria-hidden', 'true');
				if (image) {
					image.setAttribute('src', '');
				}
			}
			root.addEventListener('click', function(event) {
				var link = event.target.closest('a[data-um-lightbox="1"]');
				if (!link) { return; }
				event.preventDefault();
				var src = link.getAttribute('href') || '';
				if (!src || !image) { return; }
				image.setAttribute('src', src);
				overlay.style.display = 'flex';
				overlay.setAttribute('aria-hidden', 'false');
			});
			if (closeBtn) {
				closeBtn.addEventListener('click', closeOverlay);
			}
			overlay.addEventListener('click', function(event) {
				if (event.target === overlay) {
					closeOverlay();
				}
			});
			document.addEventListener('keydown', function(event) {
				if (event.key === 'Escape' && overlay.getAttribute('aria-hidden') === 'false') {
					closeOverlay();
				}
			});
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
			'columnsMobile' => 2,
			'sortOrder' => 'date_desc',
			'fileSize' => 'thumbnail',
			'style' => 'standard',
			'pageLimit' => 0,
			'linkTo' => 'none',
		];

		if (isset($settings['media_library_tag_gallery_columns_desktop'])) {
			$defaults['columnsDesktop'] = max(1, min(8, absint($settings['media_library_tag_gallery_columns_desktop'])));
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
	 * Resolve selected Library Tag from request/query/referer.
	 */
	private static function get_requested_media_library_tag_slug(): string {
		$slug = '';

		if (isset($_REQUEST['query']) && is_array($_REQUEST['query']) && isset($_REQUEST['query']['um_media_library_tag'])) {
			$slug = sanitize_title((string) wp_unslash($_REQUEST['query']['um_media_library_tag']));
		}
		if ($slug === '' && isset($_REQUEST['um_media_library_tag'])) {
			$slug = sanitize_title((string) wp_unslash($_REQUEST['um_media_library_tag']));
		}
		if ($slug === '') {
			$slug = self::get_media_library_tag_slug_from_referer();
		}
		if ($slug === '') {
			return '';
		}

		return term_exists($slug, self::media_library_tags_taxonomy()) ? $slug : '';
	}

	/**
	 * Read Library Tag from referer query string.
	 */
	private static function get_media_library_tag_slug_from_referer(): string {
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

		return sanitize_title((string) $query_args['um_media_library_tag']);
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
	 * Internal taxonomy name.
	 */
	private static function media_library_tags_taxonomy(): string {
		return 'um_media_library_tag';
	}
}
