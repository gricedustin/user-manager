<?php
/**
 * Plugin Tags & Notes helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Plugin_Tags_Notes_Trait {

	/**
	 * Storage option key (kept compatible with prior snippet naming).
	 */
	private static string $plugin_tags_notes_option_key = 'code_snippet_studio_plugin_tags_notes';

	/**
	 * Register Plugin Tags & Notes hooks when enabled.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_plugin_tags_notes(array $settings): void {
		if (empty($settings['plugin_tags_notes_enabled'])) {
			return;
		}

		add_action('after_plugin_row', [__CLASS__, 'render_plugin_tags_notes_editor_row'], 10, 3);
		add_filter('plugin_row_meta', [__CLASS__, 'filter_plugin_row_meta_with_tags_notes'], 12, 4);
		add_action('admin_init', [__CLASS__, 'handle_plugin_tags_notes_save_request']);
		add_action('admin_notices', [__CLASS__, 'maybe_render_plugin_tags_notes_saved_notice']);
		add_action('pre_current_active_plugins', [__CLASS__, 'render_plugin_tags_notes_toolbar']);
		add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_plugin_tags_notes_assets']);
		add_action('admin_head', [__CLASS__, 'print_plugin_tags_notes_styles']);
	}

	/**
	 * Render inline editor row after each plugin row.
	 *
	 * @param string $plugin_file Plugin basename.
	 * @param array<string,mixed> $plugin_data Plugin data.
	 * @param string $status Plugin status.
	 */
	public static function render_plugin_tags_notes_editor_row(string $plugin_file, array $plugin_data, string $status): void {
		unset($plugin_data, $status);
		if (!current_user_can('activate_plugins') || !self::is_plugins_php_screen()) {
			return;
		}

		$item = self::get_plugin_tags_notes_item($plugin_file);
		$tags_csv = esc_attr(implode(', ', $item['tags']));
		$note_val = esc_textarea($item['note']);
		$form_id = 'um-plugin-tags-notes-form';

		echo '<tr class="um-ptn-editor" data-plugin="' . esc_attr($plugin_file) . '" style="display:none;">';
		echo '<td colspan="3" class="plugin-update colspanchange">';
		echo '<div class="um-ptn-editor-wrap">';
		echo '<p style="margin:0 0 10px 0;"><strong>' . esc_html__('Plugin Tags & Notes', 'user-manager') . '</strong></p>';
		echo '<div style="display:flex; gap:12px; align-items:flex-start; flex-wrap:wrap;">';

		echo '<label style="min-width:260px; max-width:420px; flex:1 1 260px;">';
		echo '<span style="display:block; font-weight:600; margin-bottom:4px;">' . esc_html__('Tags (comma-separated)', 'user-manager') . '</span>';
		echo '<input type="text" class="regular-text" name="um_ptn_tags[' . esc_attr($plugin_file) . ']" value="' . $tags_csv . '" form="' . esc_attr($form_id) . '" />';
		$suggested_tags = self::get_plugin_tags_notes_all_tags();
		if (!empty($suggested_tags)) {
			sort($suggested_tags, SORT_NATURAL | SORT_FLAG_CASE);
			echo '<div class="um-ptn-suggested-tags" style="margin-top:6px;">';
			echo '<span style="font-size:11px;color:#666;margin-right:6px;">' . esc_html__('Suggested:', 'user-manager') . '</span>';
			foreach ($suggested_tags as $tag) {
				echo '<button type="button" class="button button-small um-ptn-tag-chip" data-tag="' . esc_attr($tag) . '" style="margin:0 6px 6px 0;">' . esc_html($tag) . '</button>';
			}
			echo '</div>';
		}
		echo '</label>';

		echo '<label style="min-width:260px; max-width:620px; flex:2 1 260px;">';
		echo '<span style="display:block; font-weight:600; margin-bottom:4px;">' . esc_html__('Note', 'user-manager') . '</span>';
		echo '<textarea name="um_ptn_note[' . esc_attr($plugin_file) . ']" rows="3" style="width:100%;" form="' . esc_attr($form_id) . '">' . $note_val . '</textarea>';
		echo '</label>';
		echo '</div>';

		echo '<p style="margin-top:10px;">';
		echo '<button type="button" class="button um-ptn-cancel">' . esc_html__('Close', 'user-manager') . '</button> ';
		echo '<span class="um-ptn-light-note" style="margin-left:6px; color:#666;">' . esc_html__('Use “Save All Tags & Notes” above the table to save changes.', 'user-manager') . '</span>';
		echo '</p>';
		echo '</div>';
		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Append tags and note indicator to plugin row meta.
	 *
	 * @param array<int,string> $meta Existing row meta.
	 * @param string $plugin_file Plugin basename.
	 * @param array<string,mixed> $plugin_data Plugin data.
	 * @param string $status Plugin status.
	 * @return array<int,string>
	 */
	public static function filter_plugin_row_meta_with_tags_notes(array $meta, string $plugin_file, array $plugin_data, string $status): array {
		unset($plugin_data, $status);
		if (!current_user_can('activate_plugins')) {
			return $meta;
		}

		$item = self::get_plugin_tags_notes_item($plugin_file);
		$badges = '';
		if (!empty($item['tags'])) {
			foreach ($item['tags'] as $tag) {
				$badges .= '<span class="um-ptn-badge" style="display:inline-block; margin-right:6px; padding:2px 6px; border-radius:12px; background:#eef3ff; border:1px solid #c7d2fe; color:#1e40af; font-size:11px; line-height:16px;">' . esc_html($tag) . '</span>';
			}
		}

		$note_html = '';
		if ($item['note'] !== '') {
			$title_note = trim(wp_strip_all_tags($item['note']));
			$note_value = nl2br(esc_html($item['note']));
			$note_html = '<span class="um-ptn-note-icon" title="' . esc_attr($title_note) . '" style="margin-left:6px;"><span class="dashicons dashicons-edit"></span></span>'
				. '<span class="um-ptn-note-text" title="' . esc_attr($title_note) . '">' . $note_value . '</span>';
		}

		if ($badges !== '' || $note_html !== '') {
			$meta[] = '<span class="um-ptn-meta">' . $badges . $note_html . '</span>';
		}

		return $meta;
	}

	/**
	 * Handle bulk save request from Plugins screen.
	 */
	public static function handle_plugin_tags_notes_save_request(): void {
		if (!is_admin() || !self::is_plugins_php_screen() || !current_user_can('activate_plugins')) {
			return;
		}
		if (!isset($_POST['um_ptn_action'])) {
			return;
		}
		$action = sanitize_text_field(wp_unslash($_POST['um_ptn_action']));
		if ($action !== 'save_all') {
			return;
		}
		if (empty($_POST['um_ptn_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['um_ptn_nonce'])), 'um_ptn_save_all')) {
			return;
		}

		$tags_map = isset($_POST['um_ptn_tags']) && is_array($_POST['um_ptn_tags']) ? wp_unslash($_POST['um_ptn_tags']) : [];
		$note_map = isset($_POST['um_ptn_note']) && is_array($_POST['um_ptn_note']) ? wp_unslash($_POST['um_ptn_note']) : [];
		$all_keys = array_unique(array_merge(array_keys($tags_map), array_keys($note_map)));

		foreach ($all_keys as $plugin_key) {
			$plugin_key = (string) $plugin_key;
			$plugin_file = sanitize_text_field($plugin_key);
			$tags_csv = isset($tags_map[$plugin_key]) ? (string) $tags_map[$plugin_key] : '';
			$note = isset($note_map[$plugin_key]) ? (string) $note_map[$plugin_key] : '';
			$tags_array = array_filter(array_map('trim', explode(',', $tags_csv)), static function ($tag): bool {
				return $tag !== '';
			});
			self::set_plugin_tags_notes_item($plugin_file, $tags_array, $note);
		}

		wp_safe_redirect(add_query_arg(['um_ptn_saved' => '1'], admin_url('plugins.php')));
		exit;
	}

	/**
	 * Render success notice after save.
	 */
	public static function maybe_render_plugin_tags_notes_saved_notice(): void {
		if (!self::is_plugins_php_screen() || !current_user_can('activate_plugins')) {
			return;
		}
		$saved = isset($_GET['um_ptn_saved']) ? sanitize_text_field(wp_unslash($_GET['um_ptn_saved'])) : '';
		if ($saved !== '1') {
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Plugin tags & notes saved.', 'user-manager') . '</p></div>';
	}

	/**
	 * Render toolbar above Plugins table.
	 */
	public static function render_plugin_tags_notes_toolbar(): void {
		if (!self::is_plugins_php_screen() || !current_user_can('activate_plugins')) {
			return;
		}

		$all_tags = self::get_plugin_tags_notes_all_tags();
		if (!empty($all_tags)) {
			sort($all_tags, SORT_NATURAL | SORT_FLAG_CASE);
		}

		echo '<div class="um-ptn-toolbar" style="margin:8px 0 12px; background:#fff;border:1px solid #d0d7de;border-radius:8px;padding:10px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">';
		echo '<div class="um-ptn-filter-wrap" style="display:flex; align-items:center; gap:8px;">';
		echo '<label for="um-ptn-filter-select" style="font-weight:600; margin:0; white-space:nowrap;">' . esc_html__('Plugin Tags', 'user-manager') . '</label>';
		echo '<select id="um-ptn-filter-select" style="min-width:180px; margin:0;">';
		echo '<option value="">' . esc_html__('All Tags', 'user-manager') . '</option>';
		foreach ($all_tags as $tag) {
			echo '<option value="' . esc_attr($tag) . '">' . esc_html($tag) . '</option>';
		}
		echo '</select>';
		echo '</div>';
		echo '<button type="button" class="button" id="um-ptn-edit-all">' . esc_html__('Edit All', 'user-manager') . '</button>';
		echo '<button type="button" class="button" id="um-ptn-close-all">' . esc_html__('Close All', 'user-manager') . '</button>';
		echo '<form id="um-plugin-tags-notes-form" method="post" action="' . esc_url(admin_url('plugins.php')) . '" style="display:flex; align-items:center; margin:0;">';
		wp_nonce_field('um_ptn_save_all', 'um_ptn_nonce');
		echo '<input type="hidden" name="um_ptn_action" value="save_all" />';
		echo '<button type="submit" class="button button-primary">' . esc_html__('Save All Tags & Notes', 'user-manager') . '</button>';
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Enqueue inline JS behavior for Plugins screen.
	 */
	public static function enqueue_plugin_tags_notes_assets(string $hook_suffix): void {
		if ($hook_suffix !== 'plugins.php' || !current_user_can('activate_plugins')) {
			return;
		}

		wp_register_script('um-plugin-tags-notes-inline', '', [], false, true);
		wp_enqueue_script('um-plugin-tags-notes-inline');

		wp_add_inline_script(
			'um-plugin-tags-notes-inline',
			'window.UMPluginTagsNotes = ' . wp_json_encode([
				'store' => self::get_plugin_tags_notes_store(),
				'allTags' => self::get_plugin_tags_notes_all_tags(),
				'linkLabel' => __('Tags & Notes', 'user-manager'),
			]) . ';'
		);

		$script = <<<'JS'
(function() {
	function q(sel, ctx) { return (ctx || document).querySelector(sel); }
	function qa(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
	function editorRow(plugin) { return q('tr.um-ptn-editor[data-plugin="' + plugin + '"]'); }

	function toggleEditor(plugin, show) {
		var row = editorRow(plugin);
		if (!row) return;
		row.style.display = show ? '' : 'none';
	}

	function closeAllEditors() {
		qa('tr.um-ptn-editor').forEach(function(row) { row.style.display = 'none'; });
	}

	function openAllEditorsVisible() {
		qa('#the-list tr[data-plugin]:not(.um-ptn-editor)').forEach(function(row) {
			if (row.style.display === 'none') return;
			var plugin = row.getAttribute('data-plugin');
			if (plugin) toggleEditor(plugin, true);
		});
	}

	function applyTagFilter(tag) {
		var store = (window.UMPluginTagsNotes && window.UMPluginTagsNotes.store) || {};
		qa('#the-list tr[data-plugin]:not(.um-ptn-editor)').forEach(function(row) {
			var plugin = row.getAttribute('data-plugin');
			if (!plugin) return;
			var item = store[plugin] || {};
			var tags = Array.isArray(item.tags) ? item.tags : [];
			var show = !tag || tags.indexOf(tag) !== -1;
			row.style.display = show ? '' : 'none';
			if (!show) toggleEditor(plugin, false);
		});
	}

	function ensureRowActionLinks() {
		var label = (window.UMPluginTagsNotes && window.UMPluginTagsNotes.linkLabel) || 'Tags & Notes';
		qa('#the-list tr[data-plugin]:not(.um-ptn-editor)').forEach(function(row) {
			var plugin = row.getAttribute('data-plugin');
			if (!plugin) return;
			var rowActions = q('.row-actions', row);
			if (!rowActions || q('.um-ptn-row-action', rowActions)) return;
			if (rowActions.children.length) rowActions.appendChild(document.createTextNode(' | '));
			var span = document.createElement('span');
			span.className = 'um-ptn-row-action';
			var link = document.createElement('a');
			link.href = '#';
			link.className = 'um-ptn-toggle';
			link.setAttribute('data-plugin', plugin);
			link.textContent = label;
			span.appendChild(link);
			rowActions.appendChild(span);
		});
	}

	document.addEventListener('click', function(e) {
		var toggle = e.target.closest('.um-ptn-toggle');
		if (toggle) {
			e.preventDefault();
			var plugin = toggle.getAttribute('data-plugin') || '';
			if (!plugin) return;
			var row = editorRow(plugin);
			if (!row) return;
			toggleEditor(plugin, row.style.display === 'none');
			return;
		}

		var cancel = e.target.closest('.um-ptn-cancel');
		if (cancel) {
			e.preventDefault();
			var editor = cancel.closest('tr.um-ptn-editor');
			if (editor) editor.style.display = 'none';
			return;
		}

		var chip = e.target.closest('.um-ptn-tag-chip');
		if (chip) {
			e.preventDefault();
			var tag = (chip.getAttribute('data-tag') || '').trim();
			if (!tag) return;
			var editorRow = chip.closest('tr.um-ptn-editor');
			if (!editorRow) return;
			var input = q('input[name^="um_ptn_tags["]', editorRow);
			if (!input) return;
			var parts = (input.value || '').split(',').map(function(part) { return part.trim(); }).filter(Boolean);
			var lower = parts.map(function(part) { return part.toLowerCase(); });
			if (lower.indexOf(tag.toLowerCase()) === -1) {
				parts.push(tag);
				input.value = parts.join(', ');
			}
		}
	});

	document.addEventListener('DOMContentLoaded', function() {
		ensureRowActionLinks();
		var filterSelect = q('#um-ptn-filter-select');
		if (filterSelect) {
			filterSelect.addEventListener('change', function() {
				closeAllEditors();
				applyTagFilter(this.value);
			});
		}
		var editAll = q('#um-ptn-edit-all');
		var closeAll = q('#um-ptn-close-all');
		if (editAll) editAll.addEventListener('click', function(e) { e.preventDefault(); openAllEditorsVisible(); });
		if (closeAll) closeAll.addEventListener('click', function(e) { e.preventDefault(); closeAllEditors(); });
	});
})();
JS;
		wp_add_inline_script('um-plugin-tags-notes-inline', $script);
	}

	/**
	 * Print minimal styles for editor row and note display.
	 */
	public static function print_plugin_tags_notes_styles(): void {
		if (!self::is_plugins_php_screen()) {
			return;
		}
		?>
		<style>
		.um-ptn-editor .plugin-update {
			border-left: 4px solid #2271b1;
		}
		.um-ptn-note-icon {
			cursor: help;
			display: inline-flex;
			vertical-align: middle;
		}
		.um-ptn-note-icon .dashicons {
			font-size: 14px;
			width: 14px;
			height: 14px;
			line-height: 14px;
		}
		.um-ptn-note-text {
			margin-left: 4px;
			color: #444;
			font-style: italic;
		}
		</style>
		<?php
	}

	/**
	 * Determine if request is wp-admin/plugins.php.
	 */
	private static function is_plugins_php_screen(): bool {
		global $pagenow;
		return isset($pagenow) && $pagenow === 'plugins.php';
	}

	/**
	 * Get stored plugin tags/notes.
	 *
	 * @return array<string,array{tags:array<int,string>,note:string}>
	 */
	private static function get_plugin_tags_notes_store(): array {
		$store = get_option(self::$plugin_tags_notes_option_key, []);
		return is_array($store) ? $store : [];
	}

	/**
	 * Persist full plugin tags/notes store.
	 *
	 * @param array<string,array{tags:array<int,string>,note:string}> $store
	 */
	private static function set_plugin_tags_notes_store(array $store): void {
		update_option(self::$plugin_tags_notes_option_key, $store);
	}

	/**
	 * Get tags/notes for one plugin.
	 *
	 * @return array{tags:array<int,string>,note:string}
	 */
	private static function get_plugin_tags_notes_item(string $plugin_file): array {
		$store = self::get_plugin_tags_notes_store();
		$item = isset($store[$plugin_file]) && is_array($store[$plugin_file]) ? $store[$plugin_file] : [];
		$tags = [];
		if (!empty($item['tags']) && is_array($item['tags'])) {
			foreach ($item['tags'] as $tag) {
				$tag = sanitize_text_field((string) $tag);
				if ($tag !== '') {
					$tags[] = $tag;
				}
			}
		}
		$tags = array_values(array_unique($tags));
		$note = isset($item['note']) ? wp_kses_post((string) $item['note']) : '';
		return ['tags' => $tags, 'note' => $note];
	}

	/**
	 * Save tags/notes for one plugin.
	 *
	 * @param array<int,string> $tags_array
	 */
	private static function set_plugin_tags_notes_item(string $plugin_file, array $tags_array, string $note): void {
		$plugin_file = sanitize_text_field($plugin_file);
		if ($plugin_file === '') {
			return;
		}

		$tags = [];
		foreach ($tags_array as $tag) {
			$tag = sanitize_text_field(trim((string) $tag));
			if ($tag !== '') {
				$tags[] = $tag;
			}
		}
		$tags = array_values(array_unique($tags));

		$store = self::get_plugin_tags_notes_store();
		$store[$plugin_file] = [
			'tags' => $tags,
			'note' => wp_kses_post($note),
		];
		self::set_plugin_tags_notes_store($store);
	}

	/**
	 * Collect all known tags.
	 *
	 * @return array<int,string>
	 */
	private static function get_plugin_tags_notes_all_tags(): array {
		$store = self::get_plugin_tags_notes_store();
		$tags_map = [];
		foreach ($store as $item) {
			if (!is_array($item) || empty($item['tags']) || !is_array($item['tags'])) {
				continue;
			}
			foreach ($item['tags'] as $tag) {
				$tag = sanitize_text_field((string) $tag);
				if ($tag !== '') {
					$tags_map[$tag] = true;
				}
			}
		}
		return array_keys($tags_map);
	}
}

