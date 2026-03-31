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
			<option value=""><?php esc_html_e('All tags', 'user-manager'); ?></option>
			<?php foreach ($terms as $term) : ?>
				<?php if (!$term instanceof WP_Term) { continue; } ?>
				<option value="<?php echo esc_attr($term->slug); ?>" <?php selected($selected_filter, (string) $term->slug); ?>>
					<?php echo esc_html($term->name); ?>
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
			<label for="um-media-library-bulk-tag-new" class="screen-reader-text"><?php esc_html_e('Enter tag', 'user-manager'); ?></label>
			<input
				type="text"
				id="um-media-library-bulk-tag-new"
				name="um_media_library_bulk_tag_new"
				placeholder="<?php echo esc_attr__('enter tag', 'user-manager'); ?>"
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
			'helps' => $quick_links_html . __('Comma-separated tags. Add new tags or remove existing tags for this media item.', 'user-manager'),
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
				'filterAll' => __('All tags', 'user-manager'),
				'bulkChoose' => __('Apply Tag', 'user-manager'),
				'bulkNewTagPlaceholder' => __('enter tag', 'user-manager'),
				'bulkButton' => __('Apply Tag(s)', 'user-manager'),
				'bulkNoSelection' => __('Select one or more media items first.', 'user-manager'),
				'bulkNoTag' => __('Choose a Library Tag or enter a new one first.', 'user-manager'),
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
		var $filter = $('<select id="um-media-library-tag-filter-grid" class="um-media-library-tag-control"></select>').html(filterHtml);
		var $bulkLabel = $('<label class="screen-reader-text" for="um-media-library-tag-bulk-grid">Bulk apply Library Tag</label>');
		var $bulk = $('<select id="um-media-library-tag-bulk-grid" class="um-media-library-tag-control"></select>').html(bulkHtml);
		var $newTag = $('<input type="text" id="um-media-library-tag-bulk-grid-new" class="um-media-library-tag-control" />').attr('placeholder', (cfg.labels && cfg.labels.bulkNewTagPlaceholder) || 'enter tag');
		var $button = $('<button type="button" class="button media-button"></button>').text((cfg.labels && cfg.labels.bulkButton) || 'Apply Tag(s)');
		var $bulkWrap = $('<span class="um-media-library-tag-bulk-controls" style="display:none;"></span>');
		$filter.css({ display: 'inline-block', minWidth: '160px' });
		$bulk.css({ display: 'inline-block', minWidth: '190px' });
		$newTag.css({ display: 'inline-block', minWidth: '190px' });
		$bulkWrap.append($bulkLabel).append($bulk).append($newTag).append($button);

		$toolbar.append($filterLabel).append($filter).append($bulkWrap);

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

		$filter.on('change', function() {
			updateUrlParam('um_media_library_tag', $(this).val());
		});

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
				'allowUrlTagOverride' => ['type' => 'boolean', 'default' => false],
				'useDefaultColumnsDesktop' => ['type' => 'boolean'],
				'columnsDesktop' => ['type' => 'integer', 'default' => 4],
				'useDefaultColumnsMobile' => ['type' => 'boolean'],
				'columnsMobile' => ['type' => 'integer', 'default' => 2],
				'useDefaultSortOrder' => ['type' => 'boolean'],
				'sortOrder' => ['type' => 'string', 'default' => 'date_desc'],
				'useDefaultFileSize' => ['type' => 'boolean'],
				'fileSize' => ['type' => 'string', 'default' => 'thumbnail'],
				'useDefaultStyle' => ['type' => 'boolean'],
				'style' => ['type' => 'string', 'default' => 'uniform_grid'],
				'useDefaultPageLimit' => ['type' => 'boolean'],
				'pageLimit' => ['type' => 'integer', 'default' => 0],
				'useDefaultLinkTo' => ['type' => 'boolean'],
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
		{ label: 'Caption DESC', value: 'caption_desc' }
	];
	var styleOptions = [
		{ label: 'Mosaic Grid (Irregular Tiles)', value: 'mosaic_grid' },
		{ label: 'Masonry / Pinterest Layout', value: 'masonry_pinterest' },
		{ label: 'Uniform Grid (Classic Gallery)', value: 'uniform_grid' },
		{ label: 'Justified Row Layout', value: 'justified_rows' },
		{ label: 'Carousel / Slider Gallery', value: 'carousel_slider' },
		{ label: 'Fullscreen Lightbox Grid', value: 'fullscreen_lightbox_grid' },
		{ label: 'Horizontal Scroll Gallery', value: 'horizontal_scroll' },
		{ label: 'Polaroid / Scrapbook Layout', value: 'polaroid_scrapbook' },
		{ label: 'Split Screen Feature Gallery', value: 'split_screen_feature' },
		{ label: 'Infinite Scroll Gallery', value: 'infinite_scroll' },
		{ label: '3D Perspective Gallery', value: 'perspective_3d' },
		{ label: 'Timeline / Story Gallery', value: 'timeline_story' },
		{ label: 'Standard', value: 'standard' },
		{ label: 'Square CSS Crop', value: 'square_crop' },
		{ label: 'Wide Rectangle CSS Crop', value: 'wide_rectangle_crop' },
		{ label: 'Tall Rectangle CSS Crop', value: 'tall_rectangle_crop' },
		{ label: 'Circle CSS Crop', value: 'circle_crop' }
	];
	var linkToOptions = [
		{ label: 'Nothing', value: 'none' },
		{ label: 'Lightbox', value: 'lightbox' },
		{ label: 'Open Media Library Permalink', value: 'media_permalink' }
	];
	var fallbackDefaults = {
		columnsDesktop: parseInt(defaults.columnsDesktop, 10) || 4,
		columnsMobile: parseInt(defaults.columnsMobile, 10) || 2,
		sortOrder: defaults.sortOrder || 'date_desc',
		fileSize: defaults.fileSize || 'thumbnail',
		style: defaults.style || 'uniform_grid',
		pageLimit: parseInt(defaults.pageLimit, 10) || 0,
		linkTo: defaults.linkTo || 'none'
	};

	registerBlockType('custom/media-library-tag-gallery', {
		title: 'Media Library Tag Gallery',
		icon: 'format-gallery',
		category: 'widgets',
		attributes: {
			tagSlug: { type: 'string', default: '' },
			allowUrlTagOverride: { type: 'boolean', default: false },
			useDefaultColumnsDesktop: { type: 'boolean', default: false },
			columnsDesktop: { type: 'integer', default: parseInt(defaults.columnsDesktop, 10) || 4 },
			useDefaultColumnsMobile: { type: 'boolean', default: false },
			columnsMobile: { type: 'integer', default: parseInt(defaults.columnsMobile, 10) || 2 },
			useDefaultSortOrder: { type: 'boolean', default: false },
			sortOrder: { type: 'string', default: defaults.sortOrder || 'date_desc' },
			useDefaultFileSize: { type: 'boolean', default: false },
			fileSize: { type: 'string', default: defaults.fileSize || 'thumbnail' },
			useDefaultStyle: { type: 'boolean', default: false },
			style: { type: 'string', default: defaults.style || 'uniform_grid' },
			useDefaultPageLimit: { type: 'boolean', default: false },
			pageLimit: { type: 'integer', default: parseInt(defaults.pageLimit, 10) || 0 },
			useDefaultLinkTo: { type: 'boolean', default: false },
			linkTo: { type: 'string', default: defaults.linkTo || 'none' }
		},
		edit: function(props) {
			var a = props.attributes;
			var set = props.setAttributes;
			var useDefaultColumnsDesktop = !!a.useDefaultColumnsDesktop;
			var useDefaultColumnsMobile = !!a.useDefaultColumnsMobile;
			var useDefaultSortOrder = !!a.useDefaultSortOrder;
			var useDefaultFileSize = !!a.useDefaultFileSize;
			var useDefaultStyle = !!a.useDefaultStyle;
			var useDefaultPageLimit = !!a.useDefaultPageLimit;
			var useDefaultLinkTo = !!a.useDefaultLinkTo;
			var effectiveColumnsDesktop = useDefaultColumnsDesktop ? fallbackDefaults.columnsDesktop : (a.columnsDesktop || fallbackDefaults.columnsDesktop);
			var effectiveColumnsMobile = useDefaultColumnsMobile ? fallbackDefaults.columnsMobile : (a.columnsMobile || fallbackDefaults.columnsMobile);
			var effectiveSortOrder = useDefaultSortOrder ? fallbackDefaults.sortOrder : (a.sortOrder || fallbackDefaults.sortOrder);
			var effectiveFileSize = useDefaultFileSize ? fallbackDefaults.fileSize : (a.fileSize || fallbackDefaults.fileSize);
			var effectiveStyle = useDefaultStyle ? fallbackDefaults.style : (a.style || fallbackDefaults.style);
			var effectivePageLimit = useDefaultPageLimit ? fallbackDefaults.pageLimit : (typeof a.pageLimit === 'number' ? a.pageLimit : fallbackDefaults.pageLimit);
			var effectiveLinkTo = useDefaultLinkTo ? fallbackDefaults.linkTo : (a.linkTo || fallbackDefaults.linkTo);
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
							label: 'Allow URL tag override (?tag=tag-slug)',
							checked: !!a.allowUrlTagOverride,
							onChange: function(v){ set({ allowUrlTagOverride: !!v }); },
							help: a.allowUrlTagOverride ? 'URL ?tag=... can override selected tag for this block.' : 'Use selected tag only.'
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
							onChange: function(v){ set({ linkTo: String(v || 'none') }); }
						}),
						element.createElement(ToggleControl, {
							label: 'Use add-on default for Link To',
							checked: useDefaultLinkTo,
							onChange: function(v){ set({ useDefaultLinkTo: !!v }); }
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
		$allow_url_tag_override = !empty($attrs['allowUrlTagOverride']);
		$use_default_columns_desktop = !empty($attrs['useDefaultColumnsDesktop']);
		$use_default_columns_mobile = !empty($attrs['useDefaultColumnsMobile']);
		$use_default_sort_order = !empty($attrs['useDefaultSortOrder']);
		$use_default_file_size = !empty($attrs['useDefaultFileSize']);
		$use_default_style = !empty($attrs['useDefaultStyle']);
		$use_default_page_limit = !empty($attrs['useDefaultPageLimit']);
		$use_default_link_to = !empty($attrs['useDefaultLinkTo']);
		$columns_desktop = $use_default_columns_desktop
			? max(1, min(8, absint($defaults['columnsDesktop'])))
			: max(1, min(8, absint($attrs['columnsDesktop'] ?? $defaults['columnsDesktop'])));
		$columns_mobile = $use_default_columns_mobile
			? max(1, min(4, absint($defaults['columnsMobile'])))
			: max(1, min(4, absint($attrs['columnsMobile'] ?? $defaults['columnsMobile'])));
		$sort_order = $use_default_sort_order
			? sanitize_key((string) $defaults['sortOrder'])
			: (isset($attrs['sortOrder']) ? sanitize_key((string) $attrs['sortOrder']) : (string) $defaults['sortOrder']);
		$file_size = $use_default_file_size
			? sanitize_key((string) $defaults['fileSize'])
			: (isset($attrs['fileSize']) ? sanitize_key((string) $attrs['fileSize']) : (string) $defaults['fileSize']);
		$style = $use_default_style
			? sanitize_key((string) $defaults['style'])
			: (isset($attrs['style']) ? sanitize_key((string) $attrs['style']) : (string) $defaults['style']);
		$page_limit = $use_default_page_limit
			? max(0, absint($defaults['pageLimit']))
			: max(0, absint($attrs['pageLimit'] ?? $defaults['pageLimit']));
		$link_to = $use_default_link_to
			? sanitize_key((string) $defaults['linkTo'])
			: (isset($attrs['linkTo']) ? sanitize_key((string) $attrs['linkTo']) : (string) $defaults['linkTo']);

		$allowed_sort_orders = ['date_asc', 'date_desc', 'id_asc', 'id_desc', 'filename_asc', 'filename_desc', 'caption_asc', 'caption_desc'];
		$allowed_file_sizes = array_keys(self::get_available_image_sizes_for_media_gallery());
		if (empty($allowed_file_sizes)) {
			$allowed_file_sizes = ['thumbnail', 'medium', 'large', 'full'];
		}
		$allowed_styles = array_keys(self::get_media_library_gallery_style_options());
		$allowed_links = ['none', 'lightbox', 'media_permalink'];
		if (!in_array($sort_order, $allowed_sort_orders, true)) {
			$sort_order = 'date_desc';
		}
		if (!in_array($file_size, $allowed_file_sizes, true)) {
			$file_size = 'thumbnail';
		}
		if (!in_array($style, $allowed_styles, true)) {
			$style = 'uniform_grid';
		}
		if (!in_array($link_to, $allowed_links, true)) {
			$link_to = 'none';
		}
		if ($allow_url_tag_override && isset($_GET['tag'])) {
			$url_tag = sanitize_title((string) wp_unslash($_GET['tag']));
			if ($url_tag === 'all') {
				$tag_slug = '';
			} elseif ($url_tag !== '' && term_exists($url_tag, self::media_library_tags_taxonomy())) {
				$tag_slug = $url_tag;
			}
		}

		$effective_link_to = $link_to;
		if ($style === 'fullscreen_lightbox_grid') {
			$effective_link_to = 'lightbox';
		}
		if ($style === 'infinite_scroll') {
			$page_limit = 0;
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
		$timeline_date_format = get_option('date_format');
		if (!is_string($timeline_date_format) || $timeline_date_format === '') {
			$timeline_date_format = 'F j, Y';
		}

		ob_start();
		?>
		<div id="<?php echo esc_attr($uid); ?>" class="um-media-library-tag-gallery <?php echo esc_attr($style_class); ?>">
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
								$caption = wp_get_attachment_caption($attachment_id);
								?>
								<figure class="um-media-library-tag-gallery-item um-mltg-carousel-slide" data-slide-index="<?php echo esc_attr((string) $index); ?>">
									<?php if ($effective_link_to === 'media_permalink' && $permalink) : ?>
										<a href="<?php echo esc_url($permalink); ?>" class="um-media-library-tag-gallery-link"><?php echo $image_html; ?></a>
									<?php elseif ($effective_link_to === 'lightbox' && $image_src) : ?>
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
							$first_main_caption = wp_get_attachment_caption($first_id);
							$first_main_thumb = wp_get_attachment_image_url($first_id, $file_size);
							?>
							<img src="<?php echo esc_url((string) ($first_main_thumb ?: $first_main_src)); ?>" alt="" class="um-mltg-split-main-image" />
							<?php if ($first_main_caption !== '') : ?>
								<p class="um-mltg-split-main-caption"><?php echo esc_html($first_main_caption); ?></p>
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
							$caption = wp_get_attachment_caption($attachment_id);
							if (!$thumb_src && !$full_src) {
								continue;
							}
							?>
							<button
								type="button"
								class="um-mltg-split-thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
								data-main-src="<?php echo esc_attr((string) ($thumb_src ?: $full_src)); ?>"
								data-caption="<?php echo esc_attr((string) $caption); ?>"
							>
								<img src="<?php echo esc_url((string) ($thumb_src ?: $full_src)); ?>" alt="" />
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="um-media-library-tag-gallery-grid" style="--um-mltg-cols-desktop:<?php echo esc_attr((string) $columns_desktop); ?>;--um-mltg-cols-mobile:<?php echo esc_attr((string) $columns_mobile); ?>;">
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
						$caption = wp_get_attachment_caption($attachment_id);
						$date_label = get_the_date($timeline_date_format, $attachment_id);
						$is_infinite_hidden = $style === 'infinite_scroll' && $index >= max(12, $columns_desktop * 3);
						?>
						<figure class="um-media-library-tag-gallery-item<?php echo $is_infinite_hidden ? ' um-mltg-infinite-hidden' : ''; ?>"<?php echo $is_infinite_hidden ? ' data-um-infinite-hidden="1"' : ''; ?>>
							<?php if ($effective_link_to === 'media_permalink' && $permalink) : ?>
								<a href="<?php echo esc_url($permalink); ?>" class="um-media-library-tag-gallery-link"><?php echo $image_html; ?></a>
							<?php elseif ($effective_link_to === 'lightbox' && $image_src) : ?>
								<a href="<?php echo esc_url($image_src); ?>" class="um-media-library-tag-gallery-link" data-um-lightbox="1"><?php echo $image_html; ?></a>
							<?php else : ?>
								<?php echo $image_html; ?>
							<?php endif; ?>
							<?php if ($style === 'timeline_story') : ?>
								<div class="um-mltg-timeline-meta"><?php echo esc_html((string) $date_label); ?></div>
							<?php endif; ?>
							<?php if ($caption !== '') : ?>
								<figcaption class="um-media-library-tag-gallery-caption"><?php echo esc_html($caption); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ($page_limit > 0 && $total_pages > 1 && $style !== 'infinite_scroll') : ?>
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
		.um-media-library-tag-gallery-item { margin: 0; position: relative; }
		.um-media-library-tag-gallery-item img { width: 100%; height: auto; display: block; }
		.um-media-gallery-style-uniform_grid .um-media-library-tag-gallery-item img,
		.um-media-gallery-style-square_crop .um-media-library-tag-gallery-item img,
		.um-media-gallery-style-fullscreen_lightbox_grid .um-media-library-tag-gallery-item img { aspect-ratio: 1 / 1; object-fit: cover; }
		.um-media-gallery-style-wide_rectangle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 16 / 9; object-fit: cover; }
		.um-media-gallery-style-tall_rectangle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 3 / 4; object-fit: cover; }
		.um-media-gallery-style-circle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 1 / 1; object-fit: cover; border-radius: 999px; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-grid { grid-auto-flow: dense; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item img { aspect-ratio: 1 / 1; object-fit: cover; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item:nth-child(7n+1) { grid-column: span 2; grid-row: span 2; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item:nth-child(5n+3) { grid-column: span 2; }
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
		.um-mltg-carousel-nav { border:1px solid #c3c4c7; background:#fff; border-radius:4px; width:34px; height:34px; line-height:1; font-size:24px; cursor:pointer; }
		.um-mltg-carousel-dots { display:flex; gap:6px; justify-content:center; margin-top:10px; }
		.um-mltg-carousel-dots button { width:9px; height:9px; border-radius:50%; border:0; background:#c3c4c7; cursor:pointer; }
		.um-mltg-carousel-dots button.is-active { background:#2271b1; }
		.um-media-gallery-style-horizontal_scroll .um-media-library-tag-gallery-grid { display:flex; overflow-x:auto; gap:12px; scroll-snap-type:x mandatory; padding-bottom:6px; }
		.um-media-gallery-style-horizontal_scroll .um-media-library-tag-gallery-item { min-width:min(320px, 85vw); flex:0 0 auto; scroll-snap-align:start; }
		.um-media-gallery-style-horizontal_scroll .um-media-library-tag-gallery-item img { height:260px; object-fit:cover; }
		.um-media-gallery-style-polaroid_scrapbook .um-media-library-tag-gallery-item { background:#fff; padding:10px 10px 18px; box-shadow:0 8px 18px rgba(0,0,0,0.12); border:1px solid #e5e5e5; }
		.um-media-gallery-style-polaroid_scrapbook .um-media-library-tag-gallery-item:nth-child(odd) { transform: rotate(-2.3deg); }
		.um-media-gallery-style-polaroid_scrapbook .um-media-library-tag-gallery-item:nth-child(even) { transform: rotate(2.1deg); }
		.um-media-gallery-style-polaroid_scrapbook .um-media-library-tag-gallery-item:hover { transform: rotate(0deg) scale(1.02); z-index:2; }
		.um-mltg-split-screen { display:grid; grid-template-columns:minmax(0, 2fr) minmax(180px, 1fr); gap:14px; }
		.um-mltg-split-main { border:1px solid #dcdcde; border-radius:6px; padding:8px; background:#fff; }
		.um-mltg-split-main-image { width:100%; height:auto; max-height:70vh; object-fit:contain; display:block; }
		.um-mltg-split-main-caption { margin:8px 0 2px; font-size:13px; color:#50575e; }
		.um-mltg-split-thumbs { display:grid; gap:8px; max-height:70vh; overflow:auto; }
		.um-mltg-split-thumb { border:1px solid #c3c4c7; background:#fff; padding:3px; cursor:pointer; border-radius:4px; }
		.um-mltg-split-thumb.is-active { border-color:#2271b1; box-shadow:0 0 0 1px #2271b1 inset; }
		.um-mltg-split-thumb img { width:100%; height:84px; object-fit:cover; display:block; }
		@media (max-width: 782px) { .um-mltg-split-screen { grid-template-columns:1fr; } }
		.um-media-gallery-style-infinite_scroll .um-mltg-infinite-hidden { display:none; }
		.um-mltg-infinite-sentinel { width:100%; height:1px; }
		.um-media-gallery-style-perspective_3d .um-media-library-tag-gallery-grid { display:flex; gap:16px; overflow-x:auto; perspective:1000px; padding:8px 4px 14px; }
		.um-media-gallery-style-perspective_3d .um-media-library-tag-gallery-item { flex:0 0 min(340px, 82vw); transform:rotateY(-18deg) scale(.94); transform-origin:center; transition:transform .2s ease; }
		.um-media-gallery-style-perspective_3d .um-media-library-tag-gallery-item:hover { transform:rotateY(0deg) scale(1); }
		.um-media-gallery-style-perspective_3d .um-media-library-tag-gallery-item img { height:230px; object-fit:cover; border-radius:8px; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-grid { grid-template-columns:1fr; gap:20px; position:relative; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-grid::before { content:''; position:absolute; left:14px; top:0; bottom:0; width:2px; background:#dcdcde; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-item { padding-left:34px; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-item::before { content:''; position:absolute; left:7px; top:14px; width:14px; height:14px; border-radius:50%; background:#2271b1; }
		.um-media-gallery-style-timeline_story .um-media-library-tag-gallery-item img { max-height:340px; object-fit:cover; border-radius:6px; }
		.um-mltg-timeline-meta { margin:6px 0 4px; font-size:12px; color:#2271b1; font-weight:600; }
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
			var bodyPrevOverflow = '';
			function closeOverlay() {
				overlay.style.display = 'none';
				overlay.setAttribute('aria-hidden', 'true');
				if (image) {
					image.setAttribute('src', '');
				}
				if (document && document.body) {
					document.body.style.overflow = bodyPrevOverflow;
				}
			}
			root.addEventListener('click', function(event) {
				var link = event.target.closest('a[data-um-lightbox="1"]');
				if (!link) { return; }
				event.preventDefault();
				var src = link.getAttribute('href') || '';
				if (!src || !image) { return; }
				image.setAttribute('src', src);
				if (document && document.body) {
					bodyPrevOverflow = document.body.style.overflow || '';
					document.body.style.overflow = 'hidden';
				}
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
			'style' => 'uniform_grid',
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
		$valid_styles = array_keys(self::get_media_library_gallery_style_options());
		if (!in_array((string) $defaults['style'], $valid_styles, true)) {
			$defaults['style'] = 'uniform_grid';
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
	public static function get_media_library_gallery_style_options(): array {
		return [
			'mosaic_grid' => __('Mosaic Grid (Irregular Tiles)', 'user-manager'),
			'masonry_pinterest' => __('Masonry / Pinterest Layout', 'user-manager'),
			'uniform_grid' => __('Uniform Grid (Classic Gallery)', 'user-manager'),
			'justified_rows' => __('Justified Row Layout', 'user-manager'),
			'carousel_slider' => __('Carousel / Slider Gallery', 'user-manager'),
			'fullscreen_lightbox_grid' => __('Fullscreen Lightbox Grid', 'user-manager'),
			'horizontal_scroll' => __('Horizontal Scroll Gallery', 'user-manager'),
			'polaroid_scrapbook' => __('Polaroid / Scrapbook Layout', 'user-manager'),
			'split_screen_feature' => __('Split Screen Feature Gallery', 'user-manager'),
			'infinite_scroll' => __('Infinite Scroll Gallery', 'user-manager'),
			'perspective_3d' => __('3D Perspective Gallery', 'user-manager'),
			'timeline_story' => __('Timeline / Story Gallery', 'user-manager'),
		];
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
