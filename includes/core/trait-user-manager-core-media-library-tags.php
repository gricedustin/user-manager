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
		add_filter('the_content', [__CLASS__, 'replace_media_library_tag_placeholders_in_content'], 20);
		add_filter('the_title', [__CLASS__, 'replace_media_library_tag_placeholders_in_title'], 20, 2);
		add_filter('document_title_parts', [__CLASS__, 'replace_media_library_tag_placeholders_in_document_title_parts'], 20);
		add_filter('pre_get_document_title', [__CLASS__, 'replace_media_library_tag_placeholders_in_document_title'], 20);
		add_action('admin_bar_menu', [__CLASS__, 'add_media_library_tag_admin_bar_shortcut'], 101);
		add_action('admin_menu', [__CLASS__, 'register_media_library_tags_bulk_editor_submenu']);
		add_action('admin_post_user_manager_media_library_tags_bulk_editor_save', [__CLASS__, 'handle_media_library_tags_bulk_editor_save']);
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
	 * Render Library Tags Bulk Editor screen.
	 */
	public static function render_media_library_tags_bulk_editor_page(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage Library Tags.', 'user-manager'));
		}

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
			<p><?php esc_html_e('Edit all Library Tag titles, slugs, and descriptions in one screen, then save all changes at once.', 'user-manager'); ?></p>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="user_manager_media_library_tags_bulk_editor_save" />
				<?php wp_nonce_field('user_manager_media_library_tags_bulk_editor_save', 'user_manager_media_library_tags_bulk_editor_nonce'); ?>
				<table class="widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 24%;"><?php esc_html_e('Tag Title', 'user-manager'); ?></th>
							<th style="width: 20%;"><?php esc_html_e('Slug', 'user-manager'); ?></th>
							<th><?php esc_html_e('Description', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($terms)) : ?>
							<tr><td colspan="3"><?php esc_html_e('No Library Tags found.', 'user-manager'); ?></td></tr>
						<?php else : ?>
							<?php foreach ($terms as $term) : ?>
								<?php if (!($term instanceof WP_Term)) { continue; } ?>
								<tr>
									<td>
										<input type="text" class="regular-text" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][name]" value="<?php echo esc_attr((string) $term->name); ?>" />
									</td>
									<td>
										<input type="text" class="regular-text" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][slug]" value="<?php echo esc_attr((string) $term->slug); ?>" />
									</td>
									<td>
										<textarea rows="3" style="width:100%;" name="um_bulk_terms[<?php echo esc_attr((string) $term->term_id); ?>][description]"><?php echo esc_textarea((string) $term->description); ?></textarea>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
				<?php submit_button(__('Save All', 'user-manager')); ?>
			</form>
		</div>
		<?php
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
			$description = isset($raw_term_data['description']) ? sanitize_textarea_field((string) $raw_term_data['description']) : (string) $current_term->description;

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
				<option value="<?php echo esc_attr((string) $filter_term['slug']); ?>" <?php selected($selected_filter, (string) $filter_term['slug']); ?>>
					<?php echo esc_html((string) ($filter_term['name'] ?? $filter_term['slug'])); ?>
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
		$filter_term_options = self::get_media_library_unique_filter_terms();

		$settings = User_Manager_Core::get_settings();
		$show_thumbnail_tags = !empty($settings['media_library_tags_show_tags_on_thumbnails_bulk_select']);
		$sticky_bulk_toolbar_mobile = !empty($settings['media_library_tags_sticky_bulk_toolbar_mobile']);
		$config = [
			'selectedTag' => self::get_requested_media_library_tag_filter_value(),
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
		var filterHtml = buildOptions(cfg.labels && cfg.labels.filterAll, selectedTag, true, filterTerms);
		var bulkHtml = buildOptions(cfg.labels && cfg.labels.bulkChoose, '', false, bulkTerms);
		var $filterLabel = $('<label class="screen-reader-text" for="um-media-library-tag-filter-grid">Library Tag filter</label>');
		var $filter = $('<select id="um-media-library-tag-filter-grid" class="um-media-library-tag-control"></select>').html(filterHtml);
		var $bulkLabel = $('<label class="screen-reader-text" for="um-media-library-tag-bulk-grid">Bulk apply Library Tag</label>');
		var $bulk = $('<select id="um-media-library-tag-bulk-grid" class="um-media-library-tag-control"></select>').html(bulkHtml);
		var $newTag = $('<input type="text" id="um-media-library-tag-bulk-grid-new" class="um-media-library-tag-control" />').attr('placeholder', (cfg.labels && cfg.labels.bulkNewTagPlaceholder) || 'or enter tag');
		var $button = $('<button type="button" class="button media-button"></button>').text((cfg.labels && cfg.labels.bulkButton) || 'Apply Tag(s)');
		var $bulkWrap = $('<span class="um-media-library-tag-bulk-controls" style="display:none;"></span>');
		$filter.css({ display: 'inline-block', minWidth: '160px' });
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
				'linkTo' => ['type' => 'string', 'default' => 'none'],
				'useDefaultAlbumDescriptionPosition' => ['type' => 'boolean', 'default' => true],
				'albumDescriptionPosition' => ['type' => 'string', 'default' => 'none'],
				'useDefaultDescriptionDisplay' => ['type' => 'boolean', 'default' => true],
				'descriptionDisplay' => ['type' => 'string', 'default' => 'none'],
				'useDefaultDescriptionValue' => ['type' => 'boolean', 'default' => true],
				'descriptionValue' => ['type' => 'string', 'default' => 'caption'],
				'useDefaultLightboxPrevNextKeyboard' => ['type' => 'boolean', 'default' => true],
				'lightboxPrevNextKeyboard' => ['type' => 'boolean', 'default' => true],
				'useDefaultLightboxSlideshowButton' => ['type' => 'boolean', 'default' => true],
				'lightboxSlideshowButton' => ['type' => 'boolean', 'default' => false],
				'useDefaultLightboxSlideshowSeconds' => ['type' => 'boolean', 'default' => true],
				'lightboxSlideshowSeconds' => ['type' => 'number', 'default' => 3],
				'useDefaultLightboxSlideshowTransition' => ['type' => 'boolean', 'default' => true],
				'lightboxSlideshowTransition' => ['type' => 'string', 'default' => 'none'],
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
		{ label: 'Polaroid / Scrapbook Layout', value: 'polaroid_scrapbook' },
		{ label: 'Split Screen Feature Gallery', value: 'split_screen_feature' },
		{ label: 'Square CSS Crop', value: 'square_crop' },
		{ label: 'Standard', value: 'standard' },
		{ label: 'Tall Rectangle CSS Crop', value: 'tall_rectangle_crop' },
		{ label: 'Timeline / Story Gallery', value: 'timeline_story' },
		{ label: 'Uniform Grid (Classic Gallery)', value: 'uniform_grid' },
		{ label: 'Wide Rectangle CSS Crop', value: 'wide_rectangle_crop' }
	];
	var linkToOptions = [
		{ label: 'Nothing', value: 'none' },
		{ label: 'Lightbox', value: 'lightbox' },
		{ label: 'Open Media Library Permalink', value: 'media_permalink' }
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
		linkTo: defaults.linkTo || 'none',
		albumDescriptionPosition: defaults.albumDescriptionPosition || 'none',
		descriptionDisplay: defaults.descriptionDisplay || 'none',
		descriptionValue: defaults.descriptionValue || 'caption',
		lightboxPrevNextKeyboard: defaults.lightboxPrevNextKeyboard !== false,
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
		})()
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
			linkTo: { type: 'string', default: defaults.linkTo || 'none' },
			useDefaultAlbumDescriptionPosition: { type: 'boolean', default: true },
			albumDescriptionPosition: { type: 'string', default: defaults.albumDescriptionPosition || 'none' },
			useDefaultDescriptionDisplay: { type: 'boolean', default: true },
			descriptionDisplay: { type: 'string', default: defaults.descriptionDisplay || 'none' },
			useDefaultDescriptionValue: { type: 'boolean', default: true },
			descriptionValue: { type: 'string', default: defaults.descriptionValue || 'caption' },
			useDefaultLightboxPrevNextKeyboard: { type: 'boolean', default: true },
			lightboxPrevNextKeyboard: { type: 'boolean', default: defaults.lightboxPrevNextKeyboard !== false },
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
			})() }
		},
		edit: function(props) {
			var a = props.attributes;
			var set = props.setAttributes;
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
			var useDefaultLightboxSlideshowButton = !!a.useDefaultLightboxSlideshowButton;
			var useDefaultLightboxSlideshowSeconds = !!a.useDefaultLightboxSlideshowSeconds;
			var useDefaultLightboxSlideshowTransition = !!a.useDefaultLightboxSlideshowTransition;
			var effectiveColumnsDesktop = useDefaultColumnsDesktop ? fallbackDefaults.columnsDesktop : (a.columnsDesktop || fallbackDefaults.columnsDesktop);
			var effectiveColumnsDesktopLt50 = useDefaultColumnsDesktopLt50 ? fallbackDefaults.columnsDesktopLt50 : (a.columnsDesktopLt50 || fallbackDefaults.columnsDesktopLt50);
			var effectiveColumnsDesktopLt25 = useDefaultColumnsDesktopLt25 ? fallbackDefaults.columnsDesktopLt25 : (a.columnsDesktopLt25 || fallbackDefaults.columnsDesktopLt25);
			var effectiveColumnsDesktopLt10 = useDefaultColumnsDesktopLt10 ? fallbackDefaults.columnsDesktopLt10 : (a.columnsDesktopLt10 || fallbackDefaults.columnsDesktopLt10);
			var effectiveColumnsMobile = useDefaultColumnsMobile ? fallbackDefaults.columnsMobile : (a.columnsMobile || fallbackDefaults.columnsMobile);
			var effectiveSortOrder = useDefaultSortOrder ? fallbackDefaults.sortOrder : (a.sortOrder || fallbackDefaults.sortOrder);
			var effectiveFileSize = useDefaultFileSize ? fallbackDefaults.fileSize : (a.fileSize || fallbackDefaults.fileSize);
			var effectiveStyle = useDefaultStyle ? fallbackDefaults.style : (a.style || fallbackDefaults.style);
			var effectivePageLimit = useDefaultPageLimit ? fallbackDefaults.pageLimit : (typeof a.pageLimit === 'number' ? a.pageLimit : fallbackDefaults.pageLimit);
			var effectiveLinkTo = useDefaultLinkTo ? fallbackDefaults.linkTo : (a.linkTo || fallbackDefaults.linkTo);
			var effectiveAlbumDescriptionPosition = useDefaultAlbumDescriptionPosition ? fallbackDefaults.albumDescriptionPosition : (a.albumDescriptionPosition || fallbackDefaults.albumDescriptionPosition);
			var effectiveDescriptionDisplay = useDefaultDescriptionDisplay ? fallbackDefaults.descriptionDisplay : (a.descriptionDisplay || fallbackDefaults.descriptionDisplay);
			var effectiveDescriptionValue = useDefaultDescriptionValue ? fallbackDefaults.descriptionValue : (a.descriptionValue || fallbackDefaults.descriptionValue);
			var effectiveLightboxPrevNextKeyboard = useDefaultLightboxPrevNextKeyboard ? !!fallbackDefaults.lightboxPrevNextKeyboard : !!a.lightboxPrevNextKeyboard;
			var effectiveLightboxSlideshowButton = useDefaultLightboxSlideshowButton ? !!fallbackDefaults.lightboxSlideshowButton : !!a.lightboxSlideshowButton;
			var effectiveLightboxSlideshowSecondsRaw = useDefaultLightboxSlideshowSeconds ? fallbackDefaults.lightboxSlideshowSeconds : parseFloat(a.lightboxSlideshowSeconds);
			var effectiveLightboxSlideshowSeconds = isFinite(effectiveLightboxSlideshowSecondsRaw) ? Math.max(1, Math.min(60, effectiveLightboxSlideshowSecondsRaw)) : fallbackDefaults.lightboxSlideshowSeconds;
			var effectiveLightboxSlideshowTransition = useDefaultLightboxSlideshowTransition ? String(fallbackDefaults.lightboxSlideshowTransition || 'none') : String(a.lightboxSlideshowTransition || 'none');
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
							onChange: function(v){ set({ linkTo: String(v || 'none') }); }
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
		// Front-end fallback lightbox runtime removed:
		// a single source of truth (the per-gallery runtime below) avoids
		// control-state conflicts for prev/next/slideshow behavior.

		$settings = User_Manager_Core::get_settings();
		$defaults = self::get_media_library_tag_gallery_defaults($settings);

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
		$use_default_lightbox_slideshow_button = !empty($attrs['useDefaultLightboxSlideshowButton']);
		$use_default_lightbox_slideshow_seconds = !empty($attrs['useDefaultLightboxSlideshowSeconds']);
		$use_default_lightbox_slideshow_transition = !empty($attrs['useDefaultLightboxSlideshowTransition']);
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
		$link_to = $use_default_link_to
			? sanitize_key((string) $defaults['linkTo'])
			: (isset($attrs['linkTo']) ? sanitize_key((string) $attrs['linkTo']) : (string) $defaults['linkTo']);
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

		$allowed_sort_orders = ['date_asc', 'date_desc', 'id_asc', 'id_desc', 'filename_asc', 'filename_desc', 'caption_asc', 'caption_desc', 'random'];
		$allowed_file_sizes = array_keys(self::get_available_image_sizes_for_media_gallery());
		if (empty($allowed_file_sizes)) {
			$allowed_file_sizes = ['thumbnail', 'medium', 'large', 'full'];
		}
		$allowed_styles = array_keys(self::get_media_library_gallery_style_options());
		$allowed_links = ['none', 'lightbox', 'media_permalink'];
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
		if (!in_array($link_to, $allowed_links, true)) {
			$link_to = 'none';
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
		$url_tag_override = [
			'mode' => 'none',
			'slugs' => [],
		];
		if ($allow_url_tag_override) {
			$url_tag_override = self::resolve_media_library_gallery_url_tag_override($allow_any_url_param_tag_identifier);
			if ($url_tag_override['mode'] === 'all') {
				$tag_slug = '';
			} elseif (!empty($url_tag_override['slugs'])) {
				$tag_slug = (string) $url_tag_override['slugs'][0];
			}
		}
		$album_tag_description_html = '';
		if ($album_description_position !== 'none') {
			$album_tag_description_html = self::render_media_library_tag_description_paragraphs_html(
				self::get_media_library_tag_description_data_for_tag_expression($url_tag_override)
			);
		}
		$has_effective_tag_value = ($tag_slug !== '' || !empty($url_tag_override['slugs']));
		if ($require_tag_value && !$has_effective_tag_value) {
			return '';
		}

		$effective_link_to = $link_to;
		if ($style === 'fullscreen_lightbox_grid') {
			$effective_link_to = 'lightbox';
		}
		if ($style === 'infinite_scroll') {
			$page_limit = 0;
		}
		$show_description_under_photo = in_array($description_display, ['grid', 'both'], true);
		$show_description_in_lightbox = in_array($description_display, ['lightbox', 'both'], true);
		$hidden_frontend_tag_slugs = self::get_hidden_frontend_tag_slugs($settings);
		$css_crop_styles = ['mosaic_grid', 'square_crop', 'wide_rectangle_crop', 'tall_rectangle_crop', 'circle_crop', 'fullscreen_lightbox_grid'];

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
		$total_pages = ($page_limit > 0 && isset($query->max_num_pages)) ? max(1, (int) $query->max_num_pages) : 1;
		$show_lightbox_admin_edit_link = current_user_can('manage_options');
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
		$timeline_date_format = get_option('date_format');
		if (!is_string($timeline_date_format) || $timeline_date_format === '') {
			$timeline_date_format = 'F j, Y';
		}

		ob_start();
		?>
		<div
			id="<?php echo esc_attr($uid); ?>"
			class="um-media-library-tag-gallery <?php echo esc_attr($style_class); ?>"
			style="--um-mltg-accent-color:<?php echo esc_attr($accent_color); ?>;"
			<?php echo $lightbox_prev_next_keyboard ? ' data-um-lightbox-prev-next="1"' : ' data-um-lightbox-prev-next="0"'; ?>
			<?php echo $lightbox_slideshow_button ? ' data-um-lightbox-slideshow="1"' : ' data-um-lightbox-slideshow="0"'; ?>
			data-um-lightbox-seconds="<?php echo esc_attr((string) $lightbox_slideshow_seconds); ?>"
			<?php echo $lightbox_debug_enabled ? ' data-um-lightbox-debug="1"' : ' data-um-lightbox-debug="0"'; ?>
			<?php echo $lightbox_debug_auto_open ? ' data-um-lightbox-debug-open="1"' : ' data-um-lightbox-debug-open="0"'; ?>
			data-um-fallback-allow-controls="0"
		>
			<?php if ($album_description_position === 'above' && $album_tag_description_html !== '') : ?>
				<div class="um-media-library-tag-description-wrap um-media-library-tag-description-wrap-above">
					<?php echo wp_kses_post($album_tag_description_html); ?>
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
									<?php if ($effective_link_to === 'media_permalink' && $permalink) : ?>
										<a href="<?php echo esc_url($permalink); ?>" class="um-media-library-tag-gallery-link"><?php echo $image_html; ?></a>
									<?php elseif ($effective_link_to === 'lightbox' && $image_src) : ?>
										<a href="<?php echo esc_url($image_src); ?>" class="um-media-library-tag-gallery-link" data-um-lightbox="1" data-um-lightbox-alt="<?php echo esc_attr($description_attr); ?>" data-um-lightbox-index="<?php echo esc_attr((string) $index); ?>"<?php echo ($show_description_in_lightbox && $description_text !== '') ? ' data-um-lightbox-caption="' . esc_attr($description_text) . '"' : ''; ?><?php echo $show_lightbox_admin_edit_link ? ' data-um-lightbox-edit-url="' . esc_attr((string) get_edit_post_link($attachment_id, '')) . '"' : ''; ?><?php echo $show_lightbox_admin_edit_link ? ' data-um-lightbox-attachment-id="' . esc_attr((string) $attachment_id) . '"' : ''; ?>><?php echo $image_html; ?></a>
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
				<div class="um-media-library-tag-gallery-grid" data-um-cols-desktop="<?php echo esc_attr((string) $effective_columns_desktop); ?>" style="--um-mltg-cols-desktop:<?php echo esc_attr((string) $effective_columns_desktop); ?>;--um-mltg-cols-mobile:<?php echo esc_attr((string) $columns_mobile); ?>;">
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
						if ($style === 'mosaic_grid' && !$disable_css_crop_for_small_galleries) {
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
							<?php if ($effective_link_to === 'media_permalink' && $permalink) : ?>
								<a href="<?php echo esc_url($permalink); ?>" class="um-media-library-tag-gallery-link"><?php echo $image_html; ?></a>
									<?php elseif ($effective_link_to === 'lightbox' && $image_src) : ?>
										<a href="<?php echo esc_url($image_src); ?>" class="um-media-library-tag-gallery-link" data-um-lightbox="1" data-um-lightbox-alt="<?php echo esc_attr($description_attr); ?>" data-um-lightbox-index="<?php echo esc_attr((string) $index); ?>"<?php echo ($show_description_in_lightbox && $description_text !== '') ? ' data-um-lightbox-caption="' . esc_attr($description_text) . '"' : ''; ?><?php echo $show_lightbox_admin_edit_link ? ' data-um-lightbox-edit-url="' . esc_attr((string) get_edit_post_link($attachment_id, '')) . '"' : ''; ?><?php echo $show_lightbox_admin_edit_link ? ' data-um-lightbox-attachment-id="' . esc_attr((string) $attachment_id) . '"' : ''; ?>><?php echo $image_html; ?></a>
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
					<?php echo wp_kses_post($album_tag_description_html); ?>
				</div>
			<?php endif; ?>
		</div>
		<style>
.um-media-library-tag-description-wrap {
	margin: 0 0 12px;
}
.um-media-library-tag-description-wrap.um-media-library-tag-description-wrap-below {
	margin-top: 12px;
	margin-bottom: 0;
}
.um-media-library-tag-description-wrap .um-media-library-tag-description-paragraph {
	margin: 0 0 8px;
}
.um-media-library-tag-description-wrap .um-media-library-tag-description-paragraph:last-child {
	margin-bottom: 0;
}
.um-media-library-tag-description-wrap .um-media-library-tag-edit-description-link {
	margin-left: 4px;
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
		.um-media-library-tag-gallery-item img { width: 100%; height: auto; display: block; }
		<?php if (!$disable_css_crop_for_small_galleries) : ?>
		.um-media-gallery-style-uniform_grid .um-media-library-tag-gallery-item img,
		.um-media-gallery-style-square_crop .um-media-library-tag-gallery-item img,
		.um-media-gallery-style-fullscreen_lightbox_grid .um-media-library-tag-gallery-item img { aspect-ratio: 1 / 1; object-fit: cover; }
		.um-media-gallery-style-wide_rectangle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 16 / 9; object-fit: cover; }
		.um-media-gallery-style-tall_rectangle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 3 / 4; object-fit: cover; }
		.um-media-gallery-style-circle_crop .um-media-library-tag-gallery-item img { aspect-ratio: 1 / 1; object-fit: cover; border-radius: 999px; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-grid {
			grid-auto-flow: dense;
			grid-auto-rows: clamp(110px, 14vw, 200px);
		}
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item img { width: 100%; height: 100%; object-fit: cover; aspect-ratio: auto; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item.um-mltg-mosaic-large { grid-column: span 2; grid-row: span 2; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item.um-mltg-mosaic-tall { grid-row: span 2; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item.um-mltg-mosaic-wide { grid-column: span 2; }
		.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-grid[data-um-cols-desktop="2"] .um-media-library-tag-gallery-item.um-mltg-mosaic-tall { grid-row: auto; }
		@media (max-width: 782px) {
			.um-media-gallery-style-mosaic_grid .um-media-library-tag-gallery-item { grid-column: auto !important; grid-row: auto !important; }
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
		.um-media-gallery-style-polaroid_scrapbook .um-media-library-tag-gallery-item:hover { transform: rotate(0deg) scale(1.02); z-index:2; }
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
		.um-media-gallery-style-perspective_3d .um-media-library-tag-gallery-item:hover { transform:rotateY(0deg) scale(1); }
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
		.um-media-library-tag-description-paragraph { margin: 0 0 8px; font-size: 14px; line-height: 1.5; color: #1d2327; }
		.um-media-library-tag-edit-description-link { margin-left: 6px; font-size: 12px; }
		.um-media-library-tag-gallery-pagination { margin-top: 14px; }
		.um-media-library-tag-gallery-pagination a,
		.um-media-library-tag-gallery-pagination strong { margin-right: 8px; }
		.um-mltg-lightbox-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.86); z-index: 999999; display: none; align-items: center; justify-content: center; flex-direction: column; padding: 30px; }
		.um-mltg-lightbox-overlay img { max-width: min(95vw, 1600px); max-height: 88vh; width: auto; height: auto; display: block; box-shadow: 0 4px 24px rgba(0,0,0,0.4); opacity: 1; transform: translateX(0); transition: opacity .28s ease, transform .32s ease; will-change: opacity, transform; }
		.um-mltg-lightbox-caption { margin-top: 10px; color: #fff; font-size: 14px; line-height: 1.45; text-align: center; max-width: min(95vw, 1600px); display: none; }
		.um-mltg-lightbox-edit-link { margin-top: 8px; color: #fff; text-decoration: underline; display: none; font-size: 13px; }
		.um-mltg-lightbox-edit-link:hover { color: #cfe7ff; }
		.um-mltg-lightbox-duplicate-link { margin-top: 6px; color: #fff; text-decoration: underline; display: none; font-size: 13px; cursor: pointer; }
		.um-mltg-lightbox-duplicate-link:hover { color: #cfe7ff; }
		.um-mltg-lightbox-tag-tools { margin-top: 8px; display: none; width: min(95vw, 560px); }
		.um-mltg-lightbox-tag-tools-row { display: flex; gap: 8px; justify-content: center; align-items: center; flex-wrap: wrap; }
		.um-mltg-lightbox-tag-input {
			width: min(95vw, 360px);
			padding: 7px 10px;
			border-radius: 4px;
			border: 1px solid rgba(255,255,255,0.65);
			background: rgba(255,255,255,0.12);
			color: #fff;
			font-size: 13px;
		}
		.um-mltg-lightbox-tag-input::placeholder { color: rgba(255,255,255,0.8); }
		.um-mltg-lightbox-tag-add-button {
			border: 1px solid rgba(255,255,255,0.6);
			background: rgba(0,0,0,0.28);
			color: #fff;
			padding: 6px 10px;
			border-radius: 4px;
			cursor: pointer;
			font-size: 13px;
		}
		.um-mltg-lightbox-tag-add-button:hover { background: rgba(255,255,255,0.12); }
		.um-mltg-lightbox-tag-add-button[disabled] { opacity: 0.6; cursor: wait; }
		.um-mltg-lightbox-tag-feedback { margin-top: 6px; color: #fff; font-size: 12px; line-height: 1.35; text-align: center; min-height: 1.35em; }
		.um-mltg-lightbox-controls { margin-top: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap; }
		.um-mltg-lightbox-prev,
		.um-mltg-lightbox-next,
		.um-mltg-lightbox-slideshow-toggle {
			border: 1px solid rgba(255,255,255,0.6);
			background: rgba(0,0,0,0.28);
			color: #fff;
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
		.um-mltg-lightbox-close { position: absolute; top: 14px; right: 16px; border: 0; background: transparent; color: #fff; font-size: 36px; line-height: 1; cursor: pointer; }
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
		<div class="um-mltg-lightbox-overlay" id="<?php echo esc_attr($uid); ?>-lightbox" aria-hidden="true">
			<button type="button" class="um-mltg-lightbox-close" aria-label="<?php esc_attr_e('Close image', 'user-manager'); ?>">&times;</button>
			<img src="" alt="" />
			<p class="um-mltg-lightbox-caption"></p>
			<a href="#" class="um-mltg-lightbox-edit-link" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Edit image', 'user-manager'); ?></a>
			<a href="#" class="um-mltg-lightbox-duplicate-link"><?php esc_html_e('Duplicate', 'user-manager'); ?></a>
			<div class="um-mltg-lightbox-tag-tools">
				<div class="um-mltg-lightbox-tag-tools-row">
					<input type="text" class="um-mltg-lightbox-tag-input" placeholder="<?php echo esc_attr__('Add tag(s), comma separated', 'user-manager'); ?>" />
					<button type="button" class="um-mltg-lightbox-tag-add-button"><?php esc_html_e('Add Tag(s)', 'user-manager'); ?></button>
				</div>
				<p class="um-mltg-lightbox-tag-feedback" aria-live="polite"></p>
			</div>
			<div class="um-mltg-lightbox-controls">
				<button type="button" class="um-mltg-lightbox-prev"><?php esc_html_e('Previous', 'user-manager'); ?></button>
				<button type="button" class="um-mltg-lightbox-next"><?php esc_html_e('Next', 'user-manager'); ?></button>
				<button type="button" class="um-mltg-lightbox-slideshow-toggle"><?php esc_html_e('Play Slideshow', 'user-manager'); ?></button>
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
					var regex = new RegExp('(?:\\?|&)' + String(paramName).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(?:=([^&]*))?(?:&|$)', 'i');
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
			var duplicateLinkEl = overlay.querySelector('.um-mltg-lightbox-duplicate-link');
			var tagToolsEl = overlay.querySelector('.um-mltg-lightbox-tag-tools');
			var tagInputEl = overlay.querySelector('.um-mltg-lightbox-tag-input');
			var tagAddBtnEl = overlay.querySelector('.um-mltg-lightbox-tag-add-button');
			var tagFeedbackEl = overlay.querySelector('.um-mltg-lightbox-tag-feedback');
			var prevBtn = overlay.querySelector('.um-mltg-lightbox-prev');
			var nextBtn = overlay.querySelector('.um-mltg-lightbox-next');
			var slideshowBtn = overlay.querySelector('.um-mltg-lightbox-slideshow-toggle');
			var canManageLightboxTags = <?php echo $show_lightbox_admin_edit_link ? 'true' : 'false'; ?>;
			var lightboxTagAjaxUrl = <?php echo wp_json_encode((string) $lightbox_tag_ajax_url); ?> || '';
			var lightboxTagNonce = <?php echo wp_json_encode((string) $lightbox_tag_nonce); ?> || '';
			var enablePrevNextKeyboard = <?php echo $lightbox_prev_next_keyboard ? 'true' : 'false'; ?>;
			var enableSlideshowButton = <?php echo $lightbox_slideshow_button ? 'true' : 'false'; ?>;
			var slideshowSecondsPerPhoto = <?php echo esc_js((string) $lightbox_slideshow_seconds); ?>;
			var slideshowTransition = <?php echo wp_json_encode((string) $lightbox_slideshow_transition); ?> || 'none';
			var lightboxLinks = Array.prototype.slice.call(root.querySelectorAll('a[data-um-lightbox="1"]'));
			var activeLightboxIndex = -1;
			var activeAttachmentId = 0;
			var slideshowTimer = null;
			var slideshowPlaying = false;
			var bodyPrevOverflow = '';
			var transitionTimer = null;
			lightboxDebugLog('Runtime initialized', {
				lightboxLinks: lightboxLinks.length,
				enablePrevNextKeyboard: enablePrevNextKeyboard,
				enableSlideshowButton: enableSlideshowButton,
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
				var raw = link.getAttribute('data-um-lightbox-index');
				var parsed = parseInt(raw || '', 10);
				if (!isNaN(parsed) && parsed >= 0) {
					return parsed;
				}
				lightboxDebugLog('Missing/invalid data-um-lightbox-index; using link array index fallback', {
					rawIndex: raw || '',
					href: link.getAttribute('href') || ''
				});
				return lightboxLinks.indexOf(link);
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
				var src = link.getAttribute('href') || '';
				if (!src) {
					lightboxDebugLog('Render aborted: link has no href/src', {
						index: parseLightboxIndex(link)
					});
					return false;
				}
				var caption = link.getAttribute('data-um-lightbox-caption') || '';
				var altText = link.getAttribute('data-um-lightbox-alt') || '';
				var editUrl = link.getAttribute('data-um-lightbox-edit-url') || '';
				var attachmentIdRaw = link.getAttribute('data-um-lightbox-attachment-id') || '';
				var attachmentId = parseInt(String(attachmentIdRaw || '0'), 10);
				if (isNaN(attachmentId) || attachmentId < 1) {
					attachmentId = 0;
				}
				var shouldAnimate = !!animate && slideshowTransition !== 'none';
				var applyPayload = function() {
					image.setAttribute('src', src);
					image.setAttribute('alt', altText);
					activeAttachmentId = attachmentId;
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
					if (duplicateLinkEl) {
						duplicateLinkEl.style.display = (canManageLightboxTags && attachmentId > 0) ? 'inline-block' : 'none';
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
				if (captionEl) {
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
				lightboxDebugLog('Active lightbox index set', {
					activeLightboxIndex: activeLightboxIndex
				});
			}
			function closeOverlay() {
				stopSlideshow();
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
				if (duplicateLinkEl) {
					duplicateLinkEl.style.display = 'none';
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
			if (overlay && overlay.style.display !== 'none') {
				overlay.style.display = 'none';
				overlay.setAttribute('aria-hidden', 'true');
			}
			if (duplicateLinkEl) {
				duplicateLinkEl.addEventListener('click', function(event) {
					event.preventDefault();
					addTagListToActiveAttachment(['duplicate']);
				});
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
			function openLightboxFromLink(link) {
				if (!link) { return; }
				var initialIndex = parseLightboxIndex(link);
				if (initialIndex < 0) {
					initialIndex = lightboxLinks.indexOf(link);
				}
				lightboxDebugLog('Opening lightbox from link', {
					initialIndex: initialIndex,
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
			function findLightboxLinkFromTarget(target) {
				if (!target) { return null; }
				var node = target.nodeType === 1 ? target : target.parentElement;
				if (!node || !node.closest) { return null; }
				return node.closest('a[data-um-lightbox="1"]');
			}
			var suppressClickOpenUntil = 0;
			function interceptLightboxEvent(event, shouldOpen) {
				var evt = event || window.event;
				if (evt && evt.__umMltgHandled) {
					lightboxDebugLog('Event ignored: already handled', {
						type: evt && evt.type ? String(evt.type) : ''
					});
					return true;
				}
				var link = findLightboxLinkFromTarget(evt && evt.target ? evt.target : null);
				if (!link && this && this.matches && this.matches('a[data-um-lightbox="1"]')) {
					link = this;
				}
				if (!link || !root.contains(link)) {
					return false;
				}
				lightboxDebugLog('Lightbox event intercepted', {
					type: evt && evt.type ? String(evt.type) : '',
					href: link.getAttribute('href') || '',
					defaultPreventedBefore: !!(evt && evt.defaultPrevented),
					targetTag: evt && evt.target && evt.target.tagName ? String(evt.target.tagName) : '',
					shouldOpen: !!shouldOpen
				});
				if (evt) {
					if (shouldOpen) {
						evt.__umMltgHandled = true;
					}
					if (typeof evt.preventDefault === 'function') {
						evt.preventDefault();
					}
					if (typeof evt.stopImmediatePropagation === 'function') {
						evt.stopImmediatePropagation();
					} else if (typeof evt.stopPropagation === 'function') {
						evt.stopPropagation();
					}
				}
				if (shouldOpen) {
					openLightboxFromLink(link);
				}
				return true;
			}
			function shouldSkipDownEvent(event) {
				if (!event) { return false; }
				if (event.type === 'mousedown' && typeof event.button === 'number' && event.button !== 0) {
					return true;
				}
				return false;
			}
			function handleLightboxPointerDown(event) {
				var evt = event || window.event;
				if (shouldSkipDownEvent(evt)) {
					return;
				}
				var openedFromPointer = interceptLightboxEvent.call(this, evt, true);
				if (openedFromPointer) {
					suppressClickOpenUntil = Date.now() + 500;
				}
			}
			function handleLightboxLinkClick(event) {
				var evt = event || window.event;
				if (Date.now() < suppressClickOpenUntil) {
					var link = findLightboxLinkFromTarget(evt && evt.target ? evt.target : null);
					if (!link && this && this.matches && this.matches('a[data-um-lightbox="1"]')) {
						link = this;
					}
					if (link && root.contains(link)) {
						lightboxDebugLog('Suppressing duplicate click after pointer-open', {
							href: link.getAttribute('href') || ''
						});
						if (evt) {
							evt.__umMltgHandled = true;
							if (typeof evt.preventDefault === 'function') {
								evt.preventDefault();
							}
							if (typeof evt.stopImmediatePropagation === 'function') {
								evt.stopImmediatePropagation();
							} else if (typeof evt.stopPropagation === 'function') {
								evt.stopPropagation();
							}
						}
						return false;
					}
				}
				return interceptLightboxEvent.call(this, evt, true);
			}
			function bindDownCaptureListeners(target, handler) {
				if (!target || !target.addEventListener) { return; }
				var downEvents = [];
				if (window && window.PointerEvent) {
					downEvents = ['pointerdown'];
				} else {
					downEvents = ['touchstart', 'mousedown'];
				}
				downEvents.forEach(function(eventName) {
					target.addEventListener(eventName, handler, true);
				});
			}
			lightboxLinks.forEach(function(lightboxLink) {
				bindDownCaptureListeners(lightboxLink, handleLightboxPointerDown);
				lightboxLink.addEventListener('click', handleLightboxLinkClick, true);
				lightboxLink.onclick = handleLightboxLinkClick;
			});
			bindDownCaptureListeners(root, handleLightboxPointerDown);
			root.addEventListener('click', handleLightboxLinkClick, true);
			if (window && window.addEventListener) {
				bindDownCaptureListeners(window, handleLightboxPointerDown);
				window.addEventListener('click', handleLightboxLinkClick, true);
			}
			bindDownCaptureListeners(document, handleLightboxPointerDown);
			document.addEventListener('click', handleLightboxLinkClick, true);
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
			if (lightboxDebugAutoOpen && lightboxLinks.length) {
				lightboxDebugLog('Auto-open enabled for first lightbox item');
				window.setTimeout(function() {
					openLightboxFromLink(lightboxLinks[0]);
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
			'linkTo' => 'none',
			'albumDescriptionPosition' => 'none',
			'descriptionDisplay' => 'none',
			'descriptionValue' => 'caption',
			'lightboxPrevNextKeyboard' => true,
			'lightboxSlideshowButton' => false,
			'lightboxSlideshowSeconds' => 3.0,
			'lightboxSlideshowTransition' => 'none',
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
		if (!empty($settings['media_library_tag_gallery_album_description_position'])) {
			$defaults['albumDescriptionPosition'] = sanitize_key((string) $settings['media_library_tag_gallery_album_description_position']);
		}
		if (!empty($settings['media_library_tag_gallery_description_display'])) {
			$defaults['descriptionDisplay'] = sanitize_key((string) $settings['media_library_tag_gallery_description_display']);
		}
		if (!empty($settings['media_library_tag_gallery_description_value'])) {
			$defaults['descriptionValue'] = sanitize_key((string) $settings['media_library_tag_gallery_description_value']);
		}
		$defaults['lightboxPrevNextKeyboard'] = isset($settings['media_library_tag_gallery_lightbox_prev_next_keyboard'])
			? $settings['media_library_tag_gallery_lightbox_prev_next_keyboard'] === true || $settings['media_library_tag_gallery_lightbox_prev_next_keyboard'] === '1'
			: (bool) $defaults['lightboxPrevNextKeyboard'];
		$defaults['lightboxSlideshowButton'] = isset($settings['media_library_tag_gallery_lightbox_slideshow_button'])
			? $settings['media_library_tag_gallery_lightbox_slideshow_button'] === true || $settings['media_library_tag_gallery_lightbox_slideshow_button'] === '1'
			: (bool) $defaults['lightboxSlideshowButton'];
		if (isset($settings['media_library_tag_gallery_lightbox_slideshow_seconds_per_photo'])) {
			$defaults['lightboxSlideshowSeconds'] = max(1.0, min(60.0, (float) $settings['media_library_tag_gallery_lightbox_slideshow_seconds_per_photo']));
		}
		if (isset($settings['media_library_tag_gallery_lightbox_slideshow_transition'])) {
			$defaults['lightboxSlideshowTransition'] = sanitize_key((string) $settings['media_library_tag_gallery_lightbox_slideshow_transition']);
		}
		$valid_lightbox_slideshow_transitions = ['none', 'crossfade', 'slide_left'];
		if (!in_array((string) $defaults['lightboxSlideshowTransition'], $valid_lightbox_slideshow_transitions, true)) {
			$defaults['lightboxSlideshowTransition'] = 'none';
		}
		$valid_styles = array_keys(self::get_media_library_gallery_style_options());
		if (!in_array((string) $defaults['style'], $valid_styles, true)) {
			$defaults['style'] = 'uniform_grid';
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
	 * Replace [tag-name]/[tag-description] with active URL tag values when enabled by block settings.
	 */
	private static function replace_media_library_tag_placeholders_in_text(string $text, bool $allow_html = false): string {
		if (strpos($text, '[tag-name]') === false && strpos($text, '[tag-description]') === false) {
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

		return strtr($text, [
			'[tag-name]' => (string) ($placeholder_values['name'] ?? ''),
			'[tag-description]' => $description,
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
	 *   editDescriptionUrls:array<int,string>
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
			];
		}

		$allow_any = !empty($config['allowAny']);
		$tag_override = self::resolve_media_library_gallery_url_tag_override($allow_any);
		return self::get_media_library_tag_description_data_for_tag_expression($tag_override);
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
	 *   editDescriptionUrls:array<int,string>
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
		];
	}

	/**
	 * Render description paragraphs (with optional edit links) for one or many tags.
	 *
	 * @param array{
	 *   description?:string,
	 *   editDescriptionUrl?:string,
	 *   descriptions?:array<int,string>,
	 *   editDescriptionUrls?:array<int,string>
	 * } $tag_description_data
	 */
	private static function render_media_library_tag_description_paragraphs_html(array $tag_description_data): string {
		$description = trim((string) ($tag_description_data['description'] ?? ''));
		$edit_description_url = (string) ($tag_description_data['editDescriptionUrl'] ?? '');
		$descriptions = isset($tag_description_data['descriptions']) && is_array($tag_description_data['descriptions'])
			? $tag_description_data['descriptions']
			: [];
		$edit_description_urls = isset($tag_description_data['editDescriptionUrls']) && is_array($tag_description_data['editDescriptionUrls'])
			? $tag_description_data['editDescriptionUrls']
			: [];
		$description_paragraphs = [];
		foreach ($descriptions as $index => $tag_description) {
			$tag_description = trim((string) $tag_description);
			$tag_edit_url = isset($edit_description_urls[$index]) ? (string) $edit_description_urls[$index] : '';
			if ($tag_description === '' && $tag_edit_url === '') {
				continue;
			}
			$edit_link = '';
			if ($tag_edit_url !== '') {
				$edit_link = sprintf(
					' <a class="um-media-library-tag-edit-description-link" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
					esc_url($tag_edit_url),
					esc_html__('Edit Tag Description', 'user-manager')
				);
			}
			$description_paragraphs[] = sprintf(
				'<p class="um-media-library-tag-description-paragraph">%1$s%2$s</p>',
				wp_kses_post($tag_description),
				$edit_link
			);
		}
		if (!empty($description_paragraphs)) {
			return implode('', $description_paragraphs);
		}
		if ($edit_description_url !== '' && $description !== '') {
			return $description . sprintf(
				' <a class="um-media-library-tag-edit-description-link" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
				esc_url($edit_description_url),
				esc_html__('Edit Tag Description', 'user-manager')
			);
		}
		return $description;
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
	 * @return array<int,array{slug:string,name:string}>
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

		$options = array_values($options_by_slug);
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
