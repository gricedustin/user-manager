<?php
/**
 * Administrator Custom Dashboard Tiles — WP-Admin page renderer.
 *
 * Renders the two tabs (Dashboard + Settings), handles notices, and emits the
 * self-contained JS that drives favorites, click tracking, sortable layout,
 * and the Fluent Form quick-link helper.
 */

if (!defined('ABSPATH')) {
	exit;
}

final class User_Manager_Admin_Custom_Dashboard_Tiles_Page {

	/**
	 * Render the full admin page with tab switching.
	 */
	public static function render(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to view this page.', 'user-manager'), '', ['response' => 403]);
		}

		$settings   = User_Manager_Core::get_settings();
		$page_title = User_Manager_Core::get_admin_custom_dashboard_tiles_page_title($settings);
		$data       = User_Manager_Core::get_admin_custom_dashboard_tiles_data();
		$clicks     = User_Manager_Core::get_admin_custom_dashboard_tiles_clicks();
		$favorites  = User_Manager_Core::get_admin_custom_dashboard_tiles_favorites_for_current_user();
		$page_slug  = User_Manager_Core::admin_custom_dashboard_tiles_page_slug();

		$active_tab = isset($_GET['tab']) ? sanitize_key((string) wp_unslash($_GET['tab'])) : 'dashboard';
		if (!in_array($active_tab, ['dashboard', 'settings'], true)) {
			$active_tab = 'dashboard';
		}

		$dashboard_url = add_query_arg(['page' => $page_slug, 'tab' => 'dashboard'], admin_url('admin.php'));
		$settings_url  = add_query_arg(['page' => $page_slug, 'tab' => 'settings'], admin_url('admin.php'));

		self::render_notice();

		echo '<div class="wrap um-admin-custom-dashboard-tiles-page">';
		echo '<h1>' . esc_html($page_title) . '</h1>';
		echo '<nav class="nav-tab-wrapper" style="margin-top: 20px;">';
		echo '<a href="' . esc_url($dashboard_url) . '" class="nav-tab ' . ($active_tab === 'dashboard' ? 'nav-tab-active' : '') . '">' . esc_html__('Dashboard', 'user-manager') . '</a>';
		echo '<a href="' . esc_url($settings_url) . '" class="nav-tab ' . ($active_tab === 'settings' ? 'nav-tab-active' : '') . '">' . esc_html__('Settings', 'user-manager') . '</a>';
		echo '</nav>';

		if ($active_tab === 'settings') {
			self::render_settings_tab($data);
		} else {
			self::render_dashboard_tab($data, $clicks, $favorites);
		}

		echo '</div>';

		self::render_inline_styles();
		self::render_inline_scripts($data, $active_tab === 'settings');
	}

	/**
	 * Translate the `um_ctiles` status into an admin notice.
	 */
	private static function render_notice(): void {
		if (!isset($_GET['um_ctiles'])) {
			return;
		}
		$status = sanitize_key((string) wp_unslash($_GET['um_ctiles']));
		if ($status === '') {
			return;
		}
		$map = [
			'section_saved'          => ['type' => 'success', 'message' => __('Section saved.', 'user-manager')],
			'section_deleted'        => ['type' => 'success', 'message' => __('Section removed. Its tiles moved to Uncategorized.', 'user-manager')],
			'section_missing'        => ['type' => 'error',   'message' => __('Section not found.', 'user-manager')],
			'section_title_required' => ['type' => 'error',   'message' => __('Section title is required.', 'user-manager')],
			'tile_saved'             => ['type' => 'success', 'message' => __('Tile saved.', 'user-manager')],
			'tile_deleted'           => ['type' => 'success', 'message' => __('Tile removed.', 'user-manager')],
			'tile_missing'           => ['type' => 'error',   'message' => __('Tile not found.', 'user-manager')],
			'tile_fields_required'   => ['type' => 'error',   'message' => __('Both Link Title and Link URL are required.', 'user-manager')],
			'import_ok'              => ['type' => 'success', 'message' => __('Import complete. Duplicates were skipped.', 'user-manager')],
			'import_nothing_new'     => ['type' => 'info',    'message' => __('Import processed, but everything in the payload already existed locally.', 'user-manager')],
			'import_invalid_json'    => ['type' => 'error',   'message' => __('Import failed: the payload was not valid JSON.', 'user-manager')],
		];
		if (!isset($map[$status])) {
			return;
		}
		$type = $map[$status]['type'];
		$class = 'notice notice-' . $type . ' is-dismissible';
		echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($map[$status]['message']) . '</p></div>';
	}

	/**
	 * Settings tab: section form, tile form, sortable layout, import/export.
	 *
	 * @param array{sections:array<int,array<string,mixed>>,tiles:array<int,array<string,mixed>>} $data
	 */
	private static function render_settings_tab(array $data): void {
		$sections = $data['sections'];
		usort($sections, static function (array $a, array $b): int {
			$oa = isset($a['order']) ? (int) $a['order'] : 0;
			$ob = isset($b['order']) ? (int) $b['order'] : 0;
			return $oa === $ob ? 0 : ($oa < $ob ? -1 : 1);
		});
		$tiles_by_section = ['' => []];
		foreach ($sections as $section) {
			$tiles_by_section[$section['id']] = [];
		}
		foreach ($data['tiles'] as $tile) {
			$section_id = isset($tile['section_id']) ? (string) $tile['section_id'] : '';
			if (!isset($tiles_by_section[$section_id])) {
				$tiles_by_section[''][] = $tile;
				continue;
			}
			$tiles_by_section[$section_id][] = $tile;
		}
		foreach ($tiles_by_section as $section_id => $tiles) {
			usort($tiles, static function (array $a, array $b): int {
				$oa = isset($a['order']) ? (int) $a['order'] : 0;
				$ob = isset($b['order']) ? (int) $b['order'] : 0;
				return $oa === $ob ? 0 : ($oa < $ob ? -1 : 1);
			});
			$tiles_by_section[$section_id] = $tiles;
		}

		$prefill_tile_url = isset($_GET['prefill_tile_url']) ? (string) wp_unslash($_GET['prefill_tile_url']) : '';
		$prefill_tile_url = User_Manager_Core::sanitize_admin_custom_dashboard_tiles_url($prefill_tile_url);
		$admin_post = admin_url('admin-post.php');

		echo '<div style="margin-top: 20px;">';

		echo '<div class="postbox" style="margin-bottom: 20px;">';
		echo '<div class="postbox-header"><h2 class="hndle">' . esc_html__('Section Settings', 'user-manager') . '</h2></div>';
		echo '<div class="inside" style="padding: 20px;">';
		echo '<form method="post" action="' . esc_url($admin_post) . '" id="um-admin-custom-dashboard-tiles-section-form">';
		echo '<input type="hidden" name="action" value="um_admin_dashboard_tiles_save_section" />';
		wp_nonce_field('um_admin_dashboard_tiles_save_section');
		echo '<input type="hidden" name="section_id" id="um-admin-custom-dashboard-tiles-section-id" value="" />';
		echo '<table class="form-table"><tbody>';
		echo '<tr><th scope="row"><label for="um-admin-custom-dashboard-tiles-section-title">' . esc_html__('Section Title', 'user-manager') . '</label></th>';
		echo '<td><input type="text" id="um-admin-custom-dashboard-tiles-section-title" name="section_title" class="regular-text" required />';
		echo '<p class="description">' . esc_html__('Enter a name for this section (e.g., "Quick Links", "Resources")', 'user-manager') . '</p></td></tr>';
		echo '<tr><th scope="row"><label for="um-admin-custom-dashboard-tiles-section-description">' . esc_html__('Section Description', 'user-manager') . '</label></th>';
		echo '<td><textarea id="um-admin-custom-dashboard-tiles-section-description" name="section_description" class="large-text" rows="3"></textarea>';
		echo '<p class="description">' . esc_html__('Optional description text that will appear below the section title.', 'user-manager') . '</p></td></tr>';
		echo '</tbody></table>';
		echo '<p class="submit"><button type="submit" class="button button-primary" id="um-admin-custom-dashboard-tiles-section-submit">' . esc_html__('Add Section', 'user-manager') . '</button> ';
		echo '<button type="button" class="button" id="um-admin-custom-dashboard-tiles-section-cancel" style="display:none;">' . esc_html__('Cancel', 'user-manager') . '</button></p>';
		echo '</form>';
		echo '</div></div>';

		echo '<div class="postbox" style="margin-bottom: 20px;">';
		echo '<div class="postbox-header"><h2 class="hndle">' . esc_html__('Tile Settings', 'user-manager') . '</h2></div>';
		echo '<div class="inside" style="padding: 20px;">';
		echo '<form method="post" action="' . esc_url($admin_post) . '" id="um-admin-custom-dashboard-tiles-tile-form">';
		echo '<input type="hidden" name="action" value="um_admin_dashboard_tiles_save_tile" />';
		wp_nonce_field('um_admin_dashboard_tiles_save_tile');
		echo '<input type="hidden" name="tile_id" id="um-admin-custom-dashboard-tiles-tile-id" value="" />';
		echo '<table class="form-table"><tbody>';
		echo '<tr><th scope="row"><label for="um-admin-custom-dashboard-tiles-tile-title">' . esc_html__('Link Title', 'user-manager') . '</label></th>';
		echo '<td><input type="text" id="um-admin-custom-dashboard-tiles-tile-title" name="tile_title" class="regular-text" required />';
		echo '<p class="description">' . esc_html__('The text that will be displayed on the tile.', 'user-manager') . '</p></td></tr>';
		echo '<tr><th scope="row"><label for="um-admin-custom-dashboard-tiles-tile-url">' . esc_html__('Link URL', 'user-manager') . '</label></th>';
		echo '<td><div style="display:flex; gap:8px; align-items:flex-start;">';
		echo '<input type="text" id="um-admin-custom-dashboard-tiles-tile-url" name="tile_url" class="regular-text" required value="' . esc_attr($prefill_tile_url) . '" placeholder="' . esc_attr__('/wp-admin/admin.php?page=fluent-form-display&id= or https://', 'user-manager') . '" />';
		echo '<button type="button" class="button" id="um-admin-custom-dashboard-tiles-fluent-form-toggle" title="' . esc_attr__('Quick add Fluent Form link', 'user-manager') . '">';
		echo '<span class="dashicons dashicons-admin-links" style="margin-top: 3px;"></span> ' . esc_html__('Fluent Form', 'user-manager') . '</button></div>';
		echo '<p class="description">' . esc_html__('The URL the tile will link to (supports relative URLs starting with /).', 'user-manager') . '</p>';
		echo '<div id="um-admin-custom-dashboard-tiles-fluent-form-tool" style="display:none; margin-top:10px; padding:10px; background:#f0f0f1; border:1px solid #ddd; border-radius:4px;">';
		echo '<label for="um-admin-custom-dashboard-tiles-fluent-form-id" style="display:block; margin-bottom:5px; font-weight:600;">' . esc_html__('Fluent Form ID:', 'user-manager') . '</label>';
		echo '<div style="display:flex; gap:8px;">';
		echo '<input type="number" id="um-admin-custom-dashboard-tiles-fluent-form-id" min="1" style="width:150px;" placeholder="' . esc_attr__('Enter form ID', 'user-manager') . '" />';
		echo '<button type="button" class="button button-primary" id="um-admin-custom-dashboard-tiles-fluent-form-apply">' . esc_html__('Apply', 'user-manager') . '</button>';
		echo '<button type="button" class="button" id="um-admin-custom-dashboard-tiles-fluent-form-cancel">' . esc_html__('Cancel', 'user-manager') . '</button>';
		echo '</div></div></td></tr>';
		echo '<tr><th scope="row"><label for="um-admin-custom-dashboard-tiles-tile-section">' . esc_html__('Section', 'user-manager') . '</label></th>';
		echo '<td><select id="um-admin-custom-dashboard-tiles-tile-section" name="tile_section" class="regular-text">';
		echo '<option value="">' . esc_html__('Uncategorized', 'user-manager') . '</option>';
		foreach ($sections as $section) {
			echo '<option value="' . esc_attr($section['id']) . '">' . esc_html($section['title']) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__('Select which section this tile belongs to.', 'user-manager') . '</p></td></tr>';
		echo '<tr><th scope="row"><label for="um-admin-custom-dashboard-tiles-tile-notes">' . esc_html__('Notes', 'user-manager') . '</label></th>';
		echo '<td><textarea id="um-admin-custom-dashboard-tiles-tile-notes" name="tile_notes" class="large-text" rows="3" placeholder="' . esc_attr__('Optional notes or description for this tile', 'user-manager') . '"></textarea>';
		echo '<p class="description">' . esc_html__('These notes will appear on the tile in the Dashboard.', 'user-manager') . '</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__('New Window', 'user-manager') . '</th>';
		echo '<td><label><input type="checkbox" id="um-admin-custom-dashboard-tiles-tile-new-window" name="tile_new_window" value="1" /> ' . esc_html__('Open link in a new window/tab', 'user-manager') . '</label></td></tr>';
		echo '</tbody></table>';
		echo '<p class="submit"><button type="submit" class="button button-primary" id="um-admin-custom-dashboard-tiles-tile-submit">' . esc_html__('Save Tile', 'user-manager') . '</button> ';
		echo '<button type="button" class="button" id="um-admin-custom-dashboard-tiles-tile-cancel" style="display:none;">' . esc_html__('Cancel', 'user-manager') . '</button></p>';
		echo '</form>';
		echo '</div></div>';

		echo '<div class="postbox"><div class="postbox-header"><h2 class="hndle">' . esc_html__('Sections &amp; Tiles', 'user-manager') . '</h2></div>';
		echo '<div class="inside" style="padding: 20px;">';
		echo '<p class="description" style="margin-bottom: 20px;">' . esc_html__('Drag sections and tiles to reorder them. Click on a tile to edit it.', 'user-manager') . '</p>';
		echo '<div id="um-admin-custom-dashboard-tiles-sections-container">';
		foreach ($sections as $section) {
			echo '<div class="um-admin-custom-dashboard-tiles-section-wrapper" data-section-id="' . esc_attr($section['id']) . '" data-section-title="' . esc_attr($section['title']) . '" data-section-description="' . esc_attr($section['description']) . '">';
			echo '<div class="um-admin-custom-dashboard-tiles-section-header">';
			echo '<span class="dashicons dashicons-move um-admin-custom-dashboard-tiles-handle" style="cursor: move; color: #666;"></span>';
			echo '<h3 style="display:inline-block; margin:0 10px;">' . esc_html($section['title']) . '</h3>';
			echo '<button type="button" class="button button-small um-admin-custom-dashboard-tiles-edit-section">' . esc_html__('Edit Section', 'user-manager') . '</button> ';
			echo '<button type="button" class="button button-small um-admin-custom-dashboard-tiles-delete-section">' . esc_html__('Delete Section', 'user-manager') . '</button>';
			echo '</div>';
			echo '<ul class="um-admin-custom-dashboard-tiles-tiles-list" data-section-id="' . esc_attr($section['id']) . '">';
			foreach ($tiles_by_section[$section['id']] as $tile) {
				self::render_settings_tile_row($tile);
			}
			echo '</ul>';
			echo '</div>';
		}

		if (!empty($tiles_by_section[''])) {
			echo '<div class="um-admin-custom-dashboard-tiles-section-wrapper um-admin-custom-dashboard-tiles-uncategorized" data-section-id="">';
			echo '<div class="um-admin-custom-dashboard-tiles-section-header">';
			echo '<h3 style="display:inline-block; margin:0 10px;">' . esc_html__('Uncategorized', 'user-manager') . '</h3>';
			echo '</div>';
			echo '<ul class="um-admin-custom-dashboard-tiles-tiles-list" data-section-id="">';
			foreach ($tiles_by_section[''] as $tile) {
				self::render_settings_tile_row($tile);
			}
			echo '</ul>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div></div>';

		self::render_settings_delete_forms();
		self::render_settings_import_export($data);

		echo '</div>';
	}

	/**
	 * Render one tile row on the Settings screen.
	 *
	 * @param array<string,mixed> $tile Tile data.
	 */
	private static function render_settings_tile_row(array $tile): void {
		$tile = User_Manager_Core::normalize_admin_custom_dashboard_tiles_tile($tile);
		echo '<li class="um-admin-custom-dashboard-tiles-tile-item" ';
		echo 'data-tile-id="' . esc_attr($tile['id']) . '" ';
		echo 'data-tile-title="' . esc_attr($tile['title']) . '" ';
		echo 'data-tile-url="' . esc_attr($tile['url']) . '" ';
		echo 'data-tile-section="' . esc_attr($tile['section_id']) . '" ';
		echo 'data-tile-notes="' . esc_attr($tile['notes']) . '" ';
		echo 'data-tile-new-window="' . ($tile['new_window'] ? '1' : '0') . '">';
		echo '<span class="dashicons dashicons-move um-admin-custom-dashboard-tiles-handle" style="cursor: move; color: #666;"></span>';
		echo '<span class="um-admin-custom-dashboard-tiles-tile-title">' . esc_html($tile['title']) . '</span>';
		echo '<span class="um-admin-custom-dashboard-tiles-tile-url">' . esc_html($tile['url']) . '</span>';
		if ($tile['new_window']) {
			echo ' <span class="dashicons dashicons-external" style="color:#666;" title="' . esc_attr__('Opens in new window', 'user-manager') . '"></span>';
		}
		echo ' <button type="button" class="button button-small um-admin-custom-dashboard-tiles-edit-tile">' . esc_html__('Edit', 'user-manager') . '</button>';
		echo ' <button type="button" class="button button-small um-admin-custom-dashboard-tiles-delete-tile">' . esc_html__('Delete', 'user-manager') . '</button>';
		echo '</li>';
	}

	/**
	 * Hidden inline forms used by the JS delete confirmations.
	 */
	private static function render_settings_delete_forms(): void {
		$admin_post = admin_url('admin-post.php');
		echo '<form method="post" action="' . esc_url($admin_post) . '" id="um-admin-custom-dashboard-tiles-delete-section-form" style="display:none;">';
		echo '<input type="hidden" name="action" value="um_admin_dashboard_tiles_delete_section" />';
		wp_nonce_field('um_admin_dashboard_tiles_delete_section');
		echo '<input type="hidden" name="section_id" value="" />';
		echo '</form>';

		echo '<form method="post" action="' . esc_url($admin_post) . '" id="um-admin-custom-dashboard-tiles-delete-tile-form" style="display:none;">';
		echo '<input type="hidden" name="action" value="um_admin_dashboard_tiles_delete_tile" />';
		wp_nonce_field('um_admin_dashboard_tiles_delete_tile');
		echo '<input type="hidden" name="tile_id" value="" />';
		echo '</form>';
	}

	/**
	 * Render the Export/Import JSON postbox.
	 *
	 * @param array{sections:array<int,array<string,mixed>>,tiles:array<int,array<string,mixed>>} $data
	 */
	private static function render_settings_import_export(array $data): void {
		$admin_post = admin_url('admin-post.php');
		$json = wp_json_encode([
			'sections' => array_values($data['sections']),
			'tiles'    => array_values($data['tiles']),
		], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		if (!is_string($json)) {
			$json = '{"sections":[],"tiles":[]}';
		}
		echo '<div class="postbox" style="margin-top: 20px;">';
		echo '<div class="postbox-header"><h2 class="hndle">' . esc_html__('Export / Import (JSON)', 'user-manager') . '</h2></div>';
		echo '<div class="inside" style="padding: 20px;">';
		echo '<p style="margin-top:0; color:#50575e;">' . wp_kses_post(__('Copy the JSON from <strong>Export</strong> on one site and paste it into <strong>Import</strong> on another site to bring sections and tiles across. Importing the same payload twice is a no-op — anything that already exists locally (matched by ID, or by section title / tile title + URL) is skipped instead of duplicated.', 'user-manager')) . '</p>';
		echo '<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 12px;">';
		echo '<div><h3 style="margin-top:0;">' . esc_html__('Export', 'user-manager') . '</h3>';
		echo '<p class="description">' . esc_html__('Read-only snapshot of the current sections + tiles.', 'user-manager') . '</p>';
		echo '<textarea id="um-admin-custom-dashboard-tiles-export-json" readonly rows="14" style="width:100%; font-family: Menlo, Consolas, monospace; font-size: 12px;">' . esc_textarea($json) . '</textarea>';
		echo '<div style="margin-top:8px; display:flex; gap:8px; align-items:center;">';
		echo '<button type="button" class="button" id="um-admin-custom-dashboard-tiles-export-copy">' . esc_html__('Copy to Clipboard', 'user-manager') . '</button>';
		echo '<span id="um-admin-custom-dashboard-tiles-export-copied" style="display:none; color:#00a32a; font-size:12px;">' . esc_html__('Copied!', 'user-manager') . '</span>';
		echo '</div></div>';
		echo '<div><h3 style="margin-top:0;">' . esc_html__('Import', 'user-manager') . '</h3>';
		echo '<p class="description">' . esc_html__('Paste a JSON payload exported from another site. Existing sections / tiles are kept; only new entries are added.', 'user-manager') . '</p>';
		echo '<form method="post" action="' . esc_url($admin_post) . '">';
		echo '<input type="hidden" name="action" value="um_admin_dashboard_tiles_import" />';
		wp_nonce_field('um_admin_dashboard_tiles_import');
		echo '<textarea name="um_admin_dashboard_tiles_import_payload" rows="14" style="width:100%; font-family: Menlo, Consolas, monospace; font-size: 12px;" placeholder="{&quot;sections&quot;:[...],&quot;tiles&quot;:[...]}"></textarea>';
		echo '<div style="margin-top:8px; display:flex; gap:8px; align-items:center;">';
		echo '<button type="submit" class="button button-primary">' . esc_html__('Import JSON', 'user-manager') . '</button>';
		echo '<span class="description" style="font-size:12px;">' . esc_html__('Duplicates are not added.', 'user-manager') . '</span>';
		echo '</div></form></div>';
		echo '</div></div></div>';
	}

	/**
	 * Dashboard tab: search, favorites, regular sections, recent clicks.
	 *
	 * @param array{sections:array<int,array<string,mixed>>,tiles:array<int,array<string,mixed>>} $data
	 * @param array<string,array<string,mixed>> $clicks
	 * @param array<int,string> $favorites
	 */
	private static function render_dashboard_tab(array $data, array $clicks, array $favorites): void {
		$sections = $data['sections'];
		usort($sections, static function (array $a, array $b): int {
			$oa = isset($a['order']) ? (int) $a['order'] : 0;
			$ob = isset($b['order']) ? (int) $b['order'] : 0;
			return $oa === $ob ? 0 : ($oa < $ob ? -1 : 1);
		});
		$tiles_by_section = ['' => []];
		foreach ($sections as $section) {
			$tiles_by_section[$section['id']] = [];
		}
		$tiles_by_id = [];
		foreach ($data['tiles'] as $tile) {
			$tiles_by_id[$tile['id']] = $tile;
			$section_id = isset($tile['section_id']) ? (string) $tile['section_id'] : '';
			if (!isset($tiles_by_section[$section_id])) {
				$tiles_by_section[''][] = $tile;
				continue;
			}
			$tiles_by_section[$section_id][] = $tile;
		}
		foreach ($tiles_by_section as $section_id => $tiles) {
			usort($tiles, static function (array $a, array $b): int {
				$oa = isset($a['order']) ? (int) $a['order'] : 0;
				$ob = isset($b['order']) ? (int) $b['order'] : 0;
				return $oa === $ob ? 0 : ($oa < $ob ? -1 : 1);
			});
			$tiles_by_section[$section_id] = $tiles;
		}

		echo '<div class="um-admin-custom-dashboard-tiles-search-container" style="margin-top:20px; margin-bottom:15px;">';
		echo '<label for="um-admin-custom-dashboard-tiles-search" style="display:block; font-weight:600; margin-bottom:5px;">' . esc_html__('Search Links', 'user-manager') . '</label>';
		echo '<input type="text" id="um-admin-custom-dashboard-tiles-search" placeholder="' . esc_attr__('Type to filter links by title, notes, or section...', 'user-manager') . '" style="width:100%; max-width:600px; padding:8px 10px; font-size:13px; border-radius:4px; border:1px solid #ccd0d4; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04);" />';
		echo '<p style="margin-top:4px; font-size:12px; color:#666;">' . esc_html__('Results update as you type and apply to Favorites, sections, and other links.', 'user-manager') . '</p>';
		echo '</div>';

		echo '<div style="margin-top:10px; display:flex; gap:20px;"><div style="flex:1;">';

		$favorite_tiles = [];
		foreach ($favorites as $fav_id) {
			if (isset($tiles_by_id[$fav_id])) {
				$favorite_tiles[] = $tiles_by_id[$fav_id];
			}
		}
		if (!empty($favorite_tiles)) {
			echo '<div class="um-admin-custom-dashboard-tiles-section-card" data-section-name="' . esc_attr__('Favorites', 'user-manager') . '" style="margin-bottom:20px;">';
			echo '<div class="um-admin-custom-dashboard-tiles-section-card-header"><h2 style="margin:0; font-size:18px; font-weight:600;">' . esc_html__('Favorites', 'user-manager') . '</h2></div>';
			echo '<div class="um-admin-custom-dashboard-tiles-section-card-body"><div class="um-admin-custom-dashboard-tiles-tiles-grid">';
			foreach ($favorite_tiles as $tile) {
				self::render_dashboard_tile_card($tile, $clicks, $favorites, true);
			}
			echo '</div></div></div>';
		}

		foreach ($sections as $section) {
			$section_tiles = isset($tiles_by_section[$section['id']]) ? $tiles_by_section[$section['id']] : [];
			if (empty($section_tiles)) {
				continue;
			}
			echo '<div class="um-admin-custom-dashboard-tiles-section-card" data-section-name="' . esc_attr($section['title']) . '" style="margin-bottom:20px;">';
			echo '<div class="um-admin-custom-dashboard-tiles-section-card-header">';
			echo '<h2 style="margin:0; font-size:18px; font-weight:600;">' . esc_html($section['title']) . '</h2>';
			if (!empty($section['description'])) {
				echo '<p style="margin:6px 0 0; color:#555;">' . esc_html($section['description']) . '</p>';
			}
			echo '</div>';
			echo '<div class="um-admin-custom-dashboard-tiles-section-card-body"><div class="um-admin-custom-dashboard-tiles-tiles-grid">';
			foreach ($section_tiles as $tile) {
				$is_favorite = in_array($tile['id'], $favorites, true);
				self::render_dashboard_tile_card($tile, $clicks, $favorites, $is_favorite);
			}
			echo '</div></div></div>';
		}

		if (!empty($tiles_by_section[''])) {
			echo '<div class="um-admin-custom-dashboard-tiles-section-card" data-section-name="' . esc_attr__('Uncategorized', 'user-manager') . '" style="margin-bottom:20px;">';
			echo '<div class="um-admin-custom-dashboard-tiles-section-card-header"><h2 style="margin:0; font-size:18px; font-weight:600;">' . esc_html__('Uncategorized', 'user-manager') . '</h2></div>';
			echo '<div class="um-admin-custom-dashboard-tiles-section-card-body"><div class="um-admin-custom-dashboard-tiles-tiles-grid">';
			foreach ($tiles_by_section[''] as $tile) {
				$is_favorite = in_array($tile['id'], $favorites, true);
				self::render_dashboard_tile_card($tile, $clicks, $favorites, $is_favorite);
			}
			echo '</div></div></div>';
		}

		echo '</div></div>';
	}

	/**
	 * Render one card on the Dashboard.
	 *
	 * @param array<string,mixed> $tile Tile data.
	 * @param array<string,array<string,mixed>> $clicks Click stats map.
	 * @param array<int,string> $favorites User's favorite ids.
	 * @param bool $is_favorite Whether this tile is currently a favorite.
	 */
	private static function render_dashboard_tile_card(array $tile, array $clicks, array $favorites, bool $is_favorite): void {
		$tile = User_Manager_Core::normalize_admin_custom_dashboard_tiles_tile($tile);
		$click = isset($clicks[$tile['id']]) ? $clicks[$tile['id']] : null;
		$count = (int) ($click['count'] ?? 0);
		$click_class = '';
		if ($count >= 10) {
			$click_class = 'um-admin-custom-dashboard-tiles-click-high';
		} elseif ($count >= 3) {
			$click_class = 'um-admin-custom-dashboard-tiles-click-medium';
		} elseif ($count >= 1) {
			$click_class = 'um-admin-custom-dashboard-tiles-click-low';
		}
		$target_attrs = $tile['new_window'] ? ' target="_blank" rel="noopener noreferrer"' : '';
		$search_needle = strtolower(trim($tile['title'] . ' ' . $tile['notes']));

		echo '<div class="um-admin-custom-dashboard-tiles-tile-card ' . esc_attr($click_class) . '" data-tile-id="' . esc_attr($tile['id']) . '" data-search="' . esc_attr($search_needle) . '">';
		echo '<a href="' . esc_url($tile['url']) . '"' . $target_attrs . ' class="um-admin-custom-dashboard-tiles-tile-card-link" data-tile-click-link="' . esc_attr($tile['id']) . '">';
		echo '<div class="um-admin-custom-dashboard-tiles-tile-card-content">';
		echo '<div class="um-admin-custom-dashboard-tiles-tile-card-title">' . esc_html($tile['title']) . '</div>';
		if ($tile['notes'] !== '') {
			echo '<div class="um-admin-custom-dashboard-tiles-tile-card-notes">' . esc_html($tile['notes']) . '</div>';
		}
		echo '<div class="um-admin-custom-dashboard-tiles-tile-card-stats">';
		if ($count > 0) {
			echo '<div class="um-admin-custom-dashboard-tiles-click-count"><span class="dashicons dashicons-chart-line"></span> ';
			echo esc_html(sprintf(_n('%d click', '%d clicks', $count, 'user-manager'), $count));
			echo '</div>';
		}
		if (!empty($click['last_user_email'])) {
			echo '<div class="um-admin-custom-dashboard-tiles-click-last"><span class="dashicons dashicons-admin-users"></span> ' . esc_html((string) $click['last_user_email']) . '</div>';
		}
		if (!empty($click['last_timestamp'])) {
			$relative = human_time_diff((int) $click['last_timestamp'], current_time('timestamp')) . ' ' . __('ago', 'user-manager');
			echo '<div class="um-admin-custom-dashboard-tiles-click-time"><span class="dashicons dashicons-clock"></span> ' . esc_html($relative) . '</div>';
		}
		echo '</div></div></a>';
		$fav_class = $is_favorite ? 'um-admin-custom-dashboard-tiles-favorite-active' : '';
		$fav_icon = $is_favorite ? 'dashicons-star-filled' : 'dashicons-star-empty';
		$fav_title = $is_favorite ? __('Remove from favorites', 'user-manager') : __('Add to favorites', 'user-manager');
		echo '<button type="button" class="um-admin-custom-dashboard-tiles-favorite-btn ' . esc_attr($fav_class) . '" data-tile-id="' . esc_attr($tile['id']) . '" title="' . esc_attr($fav_title) . '">';
		echo '<span class="dashicons ' . esc_attr($fav_icon) . '"></span></button>';
		echo '</div>';
	}

	/**
	 * Shared styles for both tabs.
	 */
	private static function render_inline_styles(): void {
		?>
		<style id="um-admin-custom-dashboard-tiles-css">
			.um-admin-custom-dashboard-tiles-section-wrapper { margin-bottom:24px; padding:16px 18px; border:1px solid #dcdcde; border-radius:6px; background:#fcfcfc; box-shadow:0 1px 1px rgba(0,0,0,0.04); }
			.um-admin-custom-dashboard-tiles-section-header { margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid #ddd; }
			.um-admin-custom-dashboard-tiles-tiles-list { list-style:none; margin:0; padding:0; min-height:50px; }
			.um-admin-custom-dashboard-tiles-tile-item { display:flex; align-items:center; gap:10px; padding:10px; margin-bottom:5px; background:#fff; border:1px solid #ddd; border-radius:4px; cursor:move; }
			.um-admin-custom-dashboard-tiles-tile-item:hover { background:#f0f0f0; }
			.um-admin-custom-dashboard-tiles-tile-item.ui-sortable-helper { background:#e7f3ff; border-color:#2271b1; }
			.um-admin-custom-dashboard-tiles-tile-title { flex:1; font-weight:600; }
			.um-admin-custom-dashboard-tiles-tile-url { flex:2; color:#666; font-size:13px; word-break: break-all; }
			.um-admin-custom-dashboard-tiles-section-wrapper.ui-sortable-helper { background:#e7f3ff; border-color:#2271b1; }
			.um-admin-custom-dashboard-tiles-section-card { background:#fff; border:1px solid #ddd; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.08); overflow:hidden; }
			.um-admin-custom-dashboard-tiles-section-card-header { padding:20px 20px 15px 20px; border-bottom:1px solid #eee; background:#f9f9f9; }
			.um-admin-custom-dashboard-tiles-section-card-body { padding:20px; }
			.um-admin-custom-dashboard-tiles-tiles-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap:15px; }
			.um-admin-custom-dashboard-tiles-tile-card { position:relative; background:#fff; border:1px solid #ddd; border-radius:8px; overflow:hidden; transition:all 0.3s ease; box-shadow:0 2px 4px rgba(0,0,0,0.08); }
			.um-admin-custom-dashboard-tiles-tile-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.15); transform: translateY(-2px); border-color:#2271b1; }
			.um-admin-custom-dashboard-tiles-tile-card.um-admin-custom-dashboard-tiles-click-high { background:#f0f9f4; border-left:4px solid #46b450; }
			.um-admin-custom-dashboard-tiles-tile-card.um-admin-custom-dashboard-tiles-click-medium { background:#fef9e7; border-left:4px solid #ffb900; }
			.um-admin-custom-dashboard-tiles-tile-card.um-admin-custom-dashboard-tiles-click-low { background:#fef2f2; border-left:4px solid #dc3232; }
			.um-admin-custom-dashboard-tiles-tile-card-link { display:block; text-decoration:none; color:inherit; }
			.um-admin-custom-dashboard-tiles-tile-card-content { padding:20px; }
			.um-admin-custom-dashboard-tiles-tile-card-title { font-size:15px; font-weight:600; color:#2271b1; margin-bottom:6px; line-height:1.4; }
			.um-admin-custom-dashboard-tiles-tile-card-notes { font-size:13px; color:#444; margin-bottom:10px; line-height:1.5; }
			.um-admin-custom-dashboard-tiles-tile-card-link:hover .um-admin-custom-dashboard-tiles-tile-card-title { color:#135e96; }
			.um-admin-custom-dashboard-tiles-tile-card-stats { display:flex; flex-direction:column; gap:8px; }
			.um-admin-custom-dashboard-tiles-click-count { font-size:13px; font-weight:600; color:#2271b1; display:flex; align-items:center; gap:6px; }
			.um-admin-custom-dashboard-tiles-click-last, .um-admin-custom-dashboard-tiles-click-time { font-size:12px; color:#666; display:flex; align-items:center; gap:6px; line-height:1.4; }
			.um-admin-custom-dashboard-tiles-click-last .dashicons, .um-admin-custom-dashboard-tiles-click-time .dashicons { color:#999; font-size:12px; width:12px; height:12px; }
			.um-admin-custom-dashboard-tiles-favorite-btn { position:absolute; top:10px; right:10px; background:rgba(255,255,255,0.9); border:1px solid #ddd; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; padding:0; transition:all 0.2s ease; }
			.um-admin-custom-dashboard-tiles-favorite-btn:hover { background:#fff; border-color:#2271b1; }
			.um-admin-custom-dashboard-tiles-favorite-btn .dashicons { font-size:18px; width:18px; height:18px; color:#666; }
			.um-admin-custom-dashboard-tiles-favorite-btn.um-admin-custom-dashboard-tiles-favorite-active .dashicons { color:#f0b429; }
			@media (max-width: 768px) { .um-admin-custom-dashboard-tiles-tiles-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap:15px; } }
			@media (max-width: 480px) { .um-admin-custom-dashboard-tiles-tiles-grid { grid-template-columns: 1fr; } }
		</style>
		<?php
	}

	/**
	 * Inline JS: handles favorites AJAX, click tracking, sortable layout,
	 * tile/section editing inline, search filter, and Fluent Form helper.
	 *
	 * @param array{sections:array<int,array<string,mixed>>,tiles:array<int,array<string,mixed>>} $data
	 */
	private static function render_inline_scripts(array $data, bool $is_settings_tab): void {
		if ($is_settings_tab) {
			wp_enqueue_script('jquery-ui-sortable');
		}
		$click_action   = User_Manager_Core::admin_custom_dashboard_tiles_click_action();
		$favorite_action = User_Manager_Core::admin_custom_dashboard_tiles_favorite_action();
		$reorder_action = User_Manager_Core::admin_custom_dashboard_tiles_reorder_action();
		$click_nonce    = wp_create_nonce($click_action);
		$favorite_nonce = wp_create_nonce($favorite_action);
		$reorder_nonce  = wp_create_nonce($reorder_action);
		$config = [
			'ajaxUrl'          => admin_url('admin-ajax.php'),
			'clickAction'      => $click_action,
			'clickNonce'       => $click_nonce,
			'favoriteAction'   => $favorite_action,
			'favoriteNonce'    => $favorite_nonce,
			'reorderAction'    => $reorder_action,
			'reorderNonce'     => $reorder_nonce,
			'isSettingsTab'    => $is_settings_tab,
			'strings'          => [
				'confirmDeleteSection' => __('Delete this section? Its tiles will be moved to Uncategorized.', 'user-manager'),
				'confirmDeleteTile'    => __('Delete this tile?', 'user-manager'),
				'addSection'           => __('Add Section', 'user-manager'),
				'updateSection'        => __('Update Section', 'user-manager'),
				'saveTile'             => __('Save Tile', 'user-manager'),
				'updateTile'           => __('Update Tile', 'user-manager'),
			],
		];
		?>
		<script id="um-admin-custom-dashboard-tiles-js">
		(function(){
			var config = <?php echo wp_json_encode($config); ?>;
			function $(sel, root){return (root || document).querySelector(sel);}
			function $all(sel, root){return Array.prototype.slice.call((root || document).querySelectorAll(sel));}
			function postForm(data){
				var body = new URLSearchParams();
				Object.keys(data).forEach(function(k){ body.set(k, data[k]); });
				return fetch(config.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest'
					},
					body: body.toString(),
					cache: 'no-store'
				}).then(function(res){ return res.json().catch(function(){ return null; }); });
			}

			// --- Favorites + click tracking (both tabs) ---
			$all('.um-admin-custom-dashboard-tiles-favorite-btn').forEach(function(btn){
				btn.addEventListener('click', function(e){
					e.preventDefault();
					e.stopPropagation();
					var tileId = btn.getAttribute('data-tile-id');
					if (!tileId) return;
					postForm({
						action: config.favoriteAction,
						nonce:  config.favoriteNonce,
						tile_id: tileId
					}).then(function(resp){
						if (resp && resp.success) {
							var isFav = !!(resp.data && resp.data.is_favorite);
							btn.classList.toggle('um-admin-custom-dashboard-tiles-favorite-active', isFav);
							var icon = btn.querySelector('.dashicons');
							if (icon) {
								icon.classList.toggle('dashicons-star-filled', isFav);
								icon.classList.toggle('dashicons-star-empty', !isFav);
							}
							btn.setAttribute('title', isFav ? '<?php echo esc_js(__('Remove from favorites', 'user-manager')); ?>' : '<?php echo esc_js(__('Add to favorites', 'user-manager')); ?>');
						}
					});
				});
			});
			$all('[data-tile-click-link]').forEach(function(link){
				link.addEventListener('click', function(){
					var tileId = link.getAttribute('data-tile-click-link');
					if (!tileId) return;
					try {
						postForm({
							action: config.clickAction,
							nonce:  config.clickNonce,
							tile_id: tileId
						});
					} catch(err) {}
				}, false);
			});

			// --- Dashboard search ---
			var searchInput = $('#um-admin-custom-dashboard-tiles-search');
			if (searchInput) {
				searchInput.addEventListener('input', function(){
					var needle = searchInput.value.toLowerCase().trim();
					$all('.um-admin-custom-dashboard-tiles-section-card').forEach(function(card){
						var sectionName = (card.getAttribute('data-section-name') || '').toLowerCase();
						var matches = 0;
						$all('.um-admin-custom-dashboard-tiles-tile-card', card).forEach(function(tile){
							var hay = (tile.getAttribute('data-search') || '');
							var visible = needle === '' || hay.indexOf(needle) !== -1 || sectionName.indexOf(needle) !== -1;
							tile.style.display = visible ? '' : 'none';
							if (visible) matches++;
						});
						card.style.display = (needle === '' || matches > 0 || sectionName.indexOf(needle) !== -1) ? '' : 'none';
					});
				});
			}

			// --- Settings tab handlers ---
			if (!config.isSettingsTab) return;

			var sectionForm = $('#um-admin-custom-dashboard-tiles-section-form');
			var sectionIdField = $('#um-admin-custom-dashboard-tiles-section-id');
			var sectionTitleField = $('#um-admin-custom-dashboard-tiles-section-title');
			var sectionDescField = $('#um-admin-custom-dashboard-tiles-section-description');
			var sectionSubmitBtn = $('#um-admin-custom-dashboard-tiles-section-submit');
			var sectionCancelBtn = $('#um-admin-custom-dashboard-tiles-section-cancel');
			function resetSectionForm(){
				if (sectionIdField) sectionIdField.value = '';
				if (sectionTitleField) sectionTitleField.value = '';
				if (sectionDescField) sectionDescField.value = '';
				if (sectionSubmitBtn) sectionSubmitBtn.textContent = config.strings.addSection;
				if (sectionCancelBtn) sectionCancelBtn.style.display = 'none';
			}
			if (sectionCancelBtn) {
				sectionCancelBtn.addEventListener('click', function(){ resetSectionForm(); });
			}
			$all('.um-admin-custom-dashboard-tiles-edit-section').forEach(function(btn){
				btn.addEventListener('click', function(){
					var wrapper = btn.closest('.um-admin-custom-dashboard-tiles-section-wrapper');
					if (!wrapper || !sectionIdField || !sectionTitleField || !sectionDescField) return;
					sectionIdField.value = wrapper.getAttribute('data-section-id') || '';
					sectionTitleField.value = wrapper.getAttribute('data-section-title') || '';
					sectionDescField.value = wrapper.getAttribute('data-section-description') || '';
					if (sectionSubmitBtn) sectionSubmitBtn.textContent = config.strings.updateSection;
					if (sectionCancelBtn) sectionCancelBtn.style.display = '';
					if (sectionForm && sectionForm.scrollIntoView) { sectionForm.scrollIntoView({behavior: 'smooth', block: 'start'}); }
				});
			});
			var deleteSectionForm = $('#um-admin-custom-dashboard-tiles-delete-section-form');
			$all('.um-admin-custom-dashboard-tiles-delete-section').forEach(function(btn){
				btn.addEventListener('click', function(){
					if (!deleteSectionForm) return;
					if (!window.confirm(config.strings.confirmDeleteSection)) return;
					var wrapper = btn.closest('.um-admin-custom-dashboard-tiles-section-wrapper');
					var id = wrapper ? wrapper.getAttribute('data-section-id') : '';
					if (!id) return;
					var idField = deleteSectionForm.querySelector('input[name="section_id"]');
					if (idField) idField.value = id;
					deleteSectionForm.submit();
				});
			});

			var tileForm = $('#um-admin-custom-dashboard-tiles-tile-form');
			var tileIdField = $('#um-admin-custom-dashboard-tiles-tile-id');
			var tileTitleField = $('#um-admin-custom-dashboard-tiles-tile-title');
			var tileUrlField = $('#um-admin-custom-dashboard-tiles-tile-url');
			var tileSectionField = $('#um-admin-custom-dashboard-tiles-tile-section');
			var tileNotesField = $('#um-admin-custom-dashboard-tiles-tile-notes');
			var tileNewWindowField = $('#um-admin-custom-dashboard-tiles-tile-new-window');
			var tileSubmitBtn = $('#um-admin-custom-dashboard-tiles-tile-submit');
			var tileCancelBtn = $('#um-admin-custom-dashboard-tiles-tile-cancel');
			function resetTileForm(){
				if (tileIdField) tileIdField.value = '';
				if (tileTitleField) tileTitleField.value = '';
				if (tileUrlField) tileUrlField.value = '';
				if (tileSectionField) tileSectionField.value = '';
				if (tileNotesField) tileNotesField.value = '';
				if (tileNewWindowField) tileNewWindowField.checked = false;
				if (tileSubmitBtn) tileSubmitBtn.textContent = config.strings.saveTile;
				if (tileCancelBtn) tileCancelBtn.style.display = 'none';
			}
			if (tileCancelBtn) { tileCancelBtn.addEventListener('click', function(){ resetTileForm(); }); }
			$all('.um-admin-custom-dashboard-tiles-edit-tile').forEach(function(btn){
				btn.addEventListener('click', function(){
					var item = btn.closest('.um-admin-custom-dashboard-tiles-tile-item');
					if (!item) return;
					if (tileIdField) tileIdField.value = item.getAttribute('data-tile-id') || '';
					if (tileTitleField) tileTitleField.value = item.getAttribute('data-tile-title') || '';
					if (tileUrlField) tileUrlField.value = item.getAttribute('data-tile-url') || '';
					if (tileSectionField) tileSectionField.value = item.getAttribute('data-tile-section') || '';
					if (tileNotesField) tileNotesField.value = item.getAttribute('data-tile-notes') || '';
					if (tileNewWindowField) tileNewWindowField.checked = item.getAttribute('data-tile-new-window') === '1';
					if (tileSubmitBtn) tileSubmitBtn.textContent = config.strings.updateTile;
					if (tileCancelBtn) tileCancelBtn.style.display = '';
					if (tileForm && tileForm.scrollIntoView) { tileForm.scrollIntoView({behavior: 'smooth', block: 'start'}); }
				});
			});
			var deleteTileForm = $('#um-admin-custom-dashboard-tiles-delete-tile-form');
			$all('.um-admin-custom-dashboard-tiles-delete-tile').forEach(function(btn){
				btn.addEventListener('click', function(){
					if (!deleteTileForm) return;
					if (!window.confirm(config.strings.confirmDeleteTile)) return;
					var item = btn.closest('.um-admin-custom-dashboard-tiles-tile-item');
					var id = item ? item.getAttribute('data-tile-id') : '';
					if (!id) return;
					var idField = deleteTileForm.querySelector('input[name="tile_id"]');
					if (idField) idField.value = id;
					deleteTileForm.submit();
				});
			});

			// Fluent Form quick helper
			var fluentToggle = $('#um-admin-custom-dashboard-tiles-fluent-form-toggle');
			var fluentPanel = $('#um-admin-custom-dashboard-tiles-fluent-form-tool');
			var fluentIdInput = $('#um-admin-custom-dashboard-tiles-fluent-form-id');
			var fluentApply = $('#um-admin-custom-dashboard-tiles-fluent-form-apply');
			var fluentCancel = $('#um-admin-custom-dashboard-tiles-fluent-form-cancel');
			if (fluentToggle && fluentPanel) {
				fluentToggle.addEventListener('click', function(){ fluentPanel.style.display = (fluentPanel.style.display === 'none' || !fluentPanel.style.display) ? '' : 'none'; });
			}
			if (fluentCancel && fluentPanel) {
				fluentCancel.addEventListener('click', function(){ fluentPanel.style.display = 'none'; });
			}
			if (fluentApply && fluentIdInput && tileUrlField) {
				fluentApply.addEventListener('click', function(){
					var id = parseInt(fluentIdInput.value, 10);
					if (id && id > 0) {
						tileUrlField.value = '/wp-admin/admin.php?page=fluent_forms&form_id=' + id + '&route=entries#/?sort_by=DESC&type=&page=1';
						if (fluentPanel) fluentPanel.style.display = 'none';
					}
				});
			}

			// Export: copy to clipboard
			var exportTa = $('#um-admin-custom-dashboard-tiles-export-json');
			var exportBtn = $('#um-admin-custom-dashboard-tiles-export-copy');
			var exportCopied = $('#um-admin-custom-dashboard-tiles-export-copied');
			if (exportBtn && exportTa) {
				exportBtn.addEventListener('click', function(){
					exportTa.focus(); exportTa.select();
					var copied = false;
					try {
						if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(exportTa.value); copied = true; }
						else { copied = document.execCommand('copy'); }
					} catch (err) { copied = false; }
					if (exportCopied) {
						exportCopied.textContent = copied ? '<?php echo esc_js(__('Copied!', 'user-manager')); ?>' : '<?php echo esc_js(__('Press Ctrl/Cmd+C to copy.', 'user-manager')); ?>';
						exportCopied.style.display = 'inline';
						setTimeout(function(){ exportCopied.style.display = 'none'; }, 2000);
					}
				});
			}

			// Sortable sections + tiles (requires jQuery UI)
			if (window.jQuery && window.jQuery.fn && window.jQuery.fn.sortable) {
				var $ = window.jQuery;
				function persistOrder(){
					var sectionOrder = $('#um-admin-custom-dashboard-tiles-sections-container > .um-admin-custom-dashboard-tiles-section-wrapper').map(function(){
						return $(this).attr('data-section-id') || '';
					}).get();
					var tileLayout = {};
					$('#um-admin-custom-dashboard-tiles-sections-container .um-admin-custom-dashboard-tiles-tiles-list').each(function(){
						var sid = $(this).attr('data-section-id') || '';
						tileLayout[sid] = $(this).children('.um-admin-custom-dashboard-tiles-tile-item').map(function(){ return $(this).attr('data-tile-id'); }).get();
					});
					var body = new URLSearchParams();
					body.set('action', config.reorderAction);
					body.set('nonce', config.reorderNonce);
					sectionOrder.forEach(function(id){ body.append('section_order[]', id); });
					Object.keys(tileLayout).forEach(function(sid){
						tileLayout[sid].forEach(function(tid){ body.append('tile_layout[' + sid + '][]', tid); });
					});
					fetch(config.ajaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
						body: body.toString(),
						cache: 'no-store'
					});
				}
				$('#um-admin-custom-dashboard-tiles-sections-container').sortable({
					handle: '.um-admin-custom-dashboard-tiles-section-header .um-admin-custom-dashboard-tiles-handle',
					items: '> .um-admin-custom-dashboard-tiles-section-wrapper',
					update: persistOrder
				});
				$('.um-admin-custom-dashboard-tiles-tiles-list').sortable({
					connectWith: '.um-admin-custom-dashboard-tiles-tiles-list',
					handle: '.um-admin-custom-dashboard-tiles-handle',
					update: persistOrder
				});
			}
		})();
		</script>
		<?php
	}
}
