<?php
/**
 * Administrator Custom Dashboard Tiles — core trait.
 *
 * Boots the admin menu page, admin bar dropdown, click-tracking AJAX
 * endpoint, favorite-toggle AJAX endpoint, and all data helpers for the
 * Administrator Custom Dashboard Tiles add-on.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Admin_Custom_Dashboard_Tiles_Trait {

	// NOTE: PHP 8.1 and earlier do not allow `const` declarations inside a
	// trait (that syntax is PHP 8.2+), so the slugs/keys below are exposed
	// as public static accessor methods instead. Callers that used to do
	// `User_Manager_Core::ADMIN_CUSTOM_DASHBOARD_TILES_PAGE_SLUG` should
	// now call `User_Manager_Core::admin_custom_dashboard_tiles_page_slug()`
	// (and the same pattern for the other names).

	/**
	 * Option key that stores sections + tiles definitions.
	 */
	public static function admin_custom_dashboard_tiles_data_option(): string {
		return 'user_manager_admin_custom_dashboard_tiles_data';
	}

	/**
	 * Option key that stores per-tile click statistics.
	 */
	public static function admin_custom_dashboard_tiles_clicks_option(): string {
		return 'user_manager_admin_custom_dashboard_tiles_clicks';
	}

	/**
	 * User meta key that stores per-user favorite tile ids.
	 */
	public static function admin_custom_dashboard_tiles_favorites_meta(): string {
		return '_um_admin_custom_dashboard_tiles_favorites';
	}

	/**
	 * WP-admin page slug.
	 */
	public static function admin_custom_dashboard_tiles_page_slug(): string {
		return 'user-manager-custom-dashboard-tiles';
	}

	/**
	 * AJAX action names.
	 */
	public static function admin_custom_dashboard_tiles_click_action(): string {
		return 'um_admin_dashboard_tile_click';
	}
	public static function admin_custom_dashboard_tiles_favorite_action(): string {
		return 'um_admin_dashboard_tile_toggle_favorite';
	}
	public static function admin_custom_dashboard_tiles_reorder_action(): string {
		return 'um_admin_dashboard_tiles_reorder';
	}

	/**
	 * Boot runtime hooks when the add-on is enabled.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_admin_custom_dashboard_tiles(array $settings): void {
		if (empty($settings['admin_custom_dashboard_tiles_enabled'])) {
			return;
		}
		if (self::is_addon_temporarily_disabled('administrator-custom-dashboard-tiles')) {
			return;
		}

		require_once __DIR__ . '/../admin/class-user-manager-admin-custom-dashboard-tiles-page.php';

		add_action('admin_menu', [__CLASS__, 'register_admin_custom_dashboard_tiles_menu'], 20, 0);
		add_action('admin_post_um_admin_dashboard_tiles_save_section', [__CLASS__, 'handle_admin_custom_dashboard_tiles_save_section']);
		add_action('admin_post_um_admin_dashboard_tiles_delete_section', [__CLASS__, 'handle_admin_custom_dashboard_tiles_delete_section']);
		add_action('admin_post_um_admin_dashboard_tiles_save_tile', [__CLASS__, 'handle_admin_custom_dashboard_tiles_save_tile']);
		add_action('admin_post_um_admin_dashboard_tiles_delete_tile', [__CLASS__, 'handle_admin_custom_dashboard_tiles_delete_tile']);
		add_action('admin_post_um_admin_dashboard_tiles_import', [__CLASS__, 'handle_admin_custom_dashboard_tiles_import']);

		add_action('wp_ajax_' . self::admin_custom_dashboard_tiles_click_action(), [__CLASS__, 'handle_admin_custom_dashboard_tiles_click_ajax']);
		add_action('wp_ajax_' . self::admin_custom_dashboard_tiles_favorite_action(), [__CLASS__, 'handle_admin_custom_dashboard_tiles_favorite_ajax']);
		add_action('wp_ajax_' . self::admin_custom_dashboard_tiles_reorder_action(), [__CLASS__, 'handle_admin_custom_dashboard_tiles_reorder_ajax']);

		if (!empty($settings['admin_custom_dashboard_tiles_admin_bar_enabled'])) {
			add_action('admin_bar_menu', [__CLASS__, 'add_admin_custom_dashboard_tiles_admin_bar_node'], 101);
			add_action('wp_head', [__CLASS__, 'render_admin_custom_dashboard_tiles_admin_bar_styles']);
			add_action('admin_head', [__CLASS__, 'render_admin_custom_dashboard_tiles_admin_bar_styles']);
		}
	}

	/**
	 * Register the WP-admin menu entry for the tiles dashboard.
	 */
	public static function register_admin_custom_dashboard_tiles_menu(): void {
		if (!current_user_can('manage_options')) {
			return;
		}

		$settings = self::get_settings();
		$menu_title = self::get_admin_custom_dashboard_tiles_menu_title($settings);
		$page_title = self::get_admin_custom_dashboard_tiles_page_title($settings);
		$priority = self::get_admin_custom_dashboard_tiles_menu_priority($settings);

		add_menu_page(
			$page_title,
			$menu_title,
			'manage_options',
			self::admin_custom_dashboard_tiles_page_slug(),
			['User_Manager_Admin_Custom_Dashboard_Tiles_Page', 'render'],
			'dashicons-admin-links',
			$priority
		);
	}

	/**
	 * Resolve the page title with settings override.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function get_admin_custom_dashboard_tiles_page_title(array $settings): string {
		$override = isset($settings['admin_custom_dashboard_tiles_page_title'])
			? trim((string) $settings['admin_custom_dashboard_tiles_page_title'])
			: '';
		return $override !== '' ? $override : __('Custom Dashboard Tiles', 'user-manager');
	}

	/**
	 * Resolve the menu title with settings override.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function get_admin_custom_dashboard_tiles_menu_title(array $settings): string {
		$override = isset($settings['admin_custom_dashboard_tiles_menu_title'])
			? trim((string) $settings['admin_custom_dashboard_tiles_menu_title'])
			: '';
		return $override !== '' ? $override : __('Dashboard Tiles', 'user-manager');
	}

	/**
	 * Resolve the menu location priority with a safe default.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function get_admin_custom_dashboard_tiles_menu_priority(array $settings): int {
		$raw = isset($settings['admin_custom_dashboard_tiles_menu_priority'])
			? (int) $settings['admin_custom_dashboard_tiles_menu_priority']
			: 0;
		if ($raw <= 0) {
			return 80;
		}
		return min(200, max(1, $raw));
	}

	/**
	 * Fetch sections + tiles data (normalized).
	 *
	 * @return array{sections:array<int,array<string,mixed>>,tiles:array<int,array<string,mixed>>}
	 */
	public static function get_admin_custom_dashboard_tiles_data(): array {
		$raw = get_option(self::admin_custom_dashboard_tiles_data_option(), []);
		if (!is_array($raw)) {
			$raw = [];
		}
		$sections = isset($raw['sections']) && is_array($raw['sections']) ? array_values($raw['sections']) : [];
		$tiles    = isset($raw['tiles']) && is_array($raw['tiles']) ? array_values($raw['tiles']) : [];
		return [
			'sections' => array_map([__CLASS__, 'normalize_admin_custom_dashboard_tiles_section'], $sections),
			'tiles'    => array_map([__CLASS__, 'normalize_admin_custom_dashboard_tiles_tile'], $tiles),
		];
	}

	/**
	 * Persist sections + tiles data.
	 *
	 * @param array{sections:array<int,array<string,mixed>>,tiles:array<int,array<string,mixed>>} $data
	 */
	public static function save_admin_custom_dashboard_tiles_data(array $data): void {
		$sections = isset($data['sections']) && is_array($data['sections']) ? array_values($data['sections']) : [];
		$tiles    = isset($data['tiles']) && is_array($data['tiles']) ? array_values($data['tiles']) : [];
		update_option(self::admin_custom_dashboard_tiles_data_option(), [
			'sections' => array_map([__CLASS__, 'normalize_admin_custom_dashboard_tiles_section'], $sections),
			'tiles'    => array_map([__CLASS__, 'normalize_admin_custom_dashboard_tiles_tile'], $tiles),
		], false);
	}

	/**
	 * Normalize a section definition.
	 *
	 * @param mixed $section Raw section.
	 * @return array{id:string,title:string,description:string,order:int}
	 */
	public static function normalize_admin_custom_dashboard_tiles_section($section): array {
		$section = is_array($section) ? $section : [];
		$id = isset($section['id']) ? sanitize_key((string) $section['id']) : '';
		if ($id === '') {
			$id = self::generate_admin_custom_dashboard_tiles_id('section');
		}
		return [
			'id'          => $id,
			'title'       => isset($section['title']) ? sanitize_text_field((string) $section['title']) : '',
			'description' => isset($section['description']) ? sanitize_textarea_field((string) $section['description']) : '',
			'order'       => isset($section['order']) ? (int) $section['order'] : 0,
		];
	}

	/**
	 * Normalize a tile definition.
	 *
	 * @param mixed $tile Raw tile.
	 * @return array{id:string,title:string,url:string,section_id:string,notes:string,new_window:bool,order:int}
	 */
	public static function normalize_admin_custom_dashboard_tiles_tile($tile): array {
		$tile = is_array($tile) ? $tile : [];
		$id = isset($tile['id']) ? sanitize_key((string) $tile['id']) : '';
		if ($id === '') {
			$id = self::generate_admin_custom_dashboard_tiles_id('tile');
		}
		$url = isset($tile['url']) ? (string) $tile['url'] : '';
		$url = self::sanitize_admin_custom_dashboard_tiles_url($url);
		return [
			'id'         => $id,
			'title'      => isset($tile['title']) ? sanitize_text_field((string) $tile['title']) : '',
			'url'        => $url,
			'section_id' => isset($tile['section_id']) ? sanitize_key((string) $tile['section_id']) : '',
			'notes'      => isset($tile['notes']) ? sanitize_textarea_field((string) $tile['notes']) : '',
			'new_window' => !empty($tile['new_window']),
			'order'      => isset($tile['order']) ? (int) $tile['order'] : 0,
		];
	}

	/**
	 * Generate a prefixed unique identifier.
	 */
	public static function generate_admin_custom_dashboard_tiles_id(string $prefix): string {
		$prefix = sanitize_key($prefix);
		$time = (string) time();
		$suffix = function_exists('wp_generate_password') ? wp_generate_password(6, false, false) : substr(md5(uniqid('', true)), 0, 6);
		return $prefix . '_' . $time . '_' . $suffix;
	}

	/**
	 * Accept absolute http(s) URLs and root-relative paths starting with "/".
	 */
	public static function sanitize_admin_custom_dashboard_tiles_url(string $url): string {
		$url = trim($url);
		if ($url === '') {
			return '';
		}
		if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
			// Root-relative path; preserve ampersands and the "?" / "#" segments.
			return esc_url_raw($url);
		}
		return esc_url_raw($url);
	}

	/**
	 * Read per-tile click stats.
	 *
	 * @return array<string,array{count:int,last_user_id:int,last_user_login:string,last_user_email:string,last_timestamp:int}>
	 */
	public static function get_admin_custom_dashboard_tiles_clicks(): array {
		$raw = get_option(self::admin_custom_dashboard_tiles_clicks_option(), []);
		if (!is_array($raw)) {
			return [];
		}
		$clicks = [];
		foreach ($raw as $tile_id => $entry) {
			$tile_id = sanitize_key((string) $tile_id);
			if ($tile_id === '' || !is_array($entry)) {
				continue;
			}
			$clicks[$tile_id] = [
				'count'           => isset($entry['count']) ? max(0, (int) $entry['count']) : 0,
				'last_user_id'    => isset($entry['last_user_id']) ? (int) $entry['last_user_id'] : 0,
				'last_user_login' => isset($entry['last_user_login']) ? sanitize_text_field((string) $entry['last_user_login']) : '',
				'last_user_email' => isset($entry['last_user_email']) ? sanitize_text_field((string) $entry['last_user_email']) : '',
				'last_timestamp'  => isset($entry['last_timestamp']) ? (int) $entry['last_timestamp'] : 0,
			];
		}
		return $clicks;
	}

	/**
	 * Record a click for the given tile id by the current user.
	 */
	public static function record_admin_custom_dashboard_tile_click(string $tile_id): void {
		$tile_id = sanitize_key($tile_id);
		if ($tile_id === '') {
			return;
		}
		$user = wp_get_current_user();
		if (!$user || !$user->ID) {
			return;
		}
		$clicks = self::get_admin_custom_dashboard_tiles_clicks();
		$prior = isset($clicks[$tile_id]) ? $clicks[$tile_id] : [
			'count' => 0,
			'last_user_id' => 0,
			'last_user_login' => '',
			'last_user_email' => '',
			'last_timestamp' => 0,
		];
		$clicks[$tile_id] = [
			'count'           => (int) $prior['count'] + 1,
			'last_user_id'    => (int) $user->ID,
			'last_user_login' => (string) $user->user_login,
			'last_user_email' => (string) $user->user_email,
			'last_timestamp'  => time(),
		];
		update_option(self::admin_custom_dashboard_tiles_clicks_option(), $clicks, false);
	}

	/**
	 * Get the current user's favorite tile ids.
	 *
	 * @return array<int,string>
	 */
	public static function get_admin_custom_dashboard_tiles_favorites_for_current_user(): array {
		$user_id = get_current_user_id();
		if ($user_id <= 0) {
			return [];
		}
		$raw = get_user_meta($user_id, self::admin_custom_dashboard_tiles_favorites_meta(), true);
		if (!is_array($raw)) {
			return [];
		}
		$favorites = [];
		foreach ($raw as $tile_id) {
			$tile_id = sanitize_key((string) $tile_id);
			if ($tile_id !== '') {
				$favorites[] = $tile_id;
			}
		}
		return array_values(array_unique($favorites));
	}

	/**
	 * Toggle a favorite tile for the current user.
	 */
	public static function toggle_admin_custom_dashboard_tiles_favorite(string $tile_id): bool {
		$tile_id = sanitize_key($tile_id);
		$user_id = get_current_user_id();
		if ($tile_id === '' || $user_id <= 0) {
			return false;
		}
		$favorites = self::get_admin_custom_dashboard_tiles_favorites_for_current_user();
		if (in_array($tile_id, $favorites, true)) {
			$favorites = array_values(array_diff($favorites, [$tile_id]));
			$is_favorite = false;
		} else {
			$favorites[] = $tile_id;
			$favorites = array_values(array_unique($favorites));
			$is_favorite = true;
		}
		update_user_meta($user_id, self::admin_custom_dashboard_tiles_favorites_meta(), $favorites);
		return $is_favorite;
	}

	/**
	 * AJAX: record a tile click from the portal page.
	 */
	public static function handle_admin_custom_dashboard_tiles_click_ajax(): void {
		check_ajax_referer(self::admin_custom_dashboard_tiles_click_action(), 'nonce');
		if (!current_user_can('read')) {
			wp_send_json_error(['code' => 'forbidden'], 403);
		}
		$tile_id = isset($_POST['tile_id']) ? sanitize_key((string) wp_unslash($_POST['tile_id'])) : '';
		if ($tile_id === '') {
			wp_send_json_error(['code' => 'missing_tile_id'], 400);
		}
		self::record_admin_custom_dashboard_tile_click($tile_id);
		wp_send_json_success(['tile_id' => $tile_id]);
	}

	/**
	 * AJAX: toggle favorite for the current user.
	 */
	public static function handle_admin_custom_dashboard_tiles_favorite_ajax(): void {
		check_ajax_referer(self::admin_custom_dashboard_tiles_favorite_action(), 'nonce');
		if (!current_user_can('read')) {
			wp_send_json_error(['code' => 'forbidden'], 403);
		}
		$tile_id = isset($_POST['tile_id']) ? sanitize_key((string) wp_unslash($_POST['tile_id'])) : '';
		if ($tile_id === '') {
			wp_send_json_error(['code' => 'missing_tile_id'], 400);
		}
		$is_favorite = self::toggle_admin_custom_dashboard_tiles_favorite($tile_id);
		wp_send_json_success([
			'tile_id'     => $tile_id,
			'is_favorite' => $is_favorite,
		]);
	}

	/**
	 * AJAX: persist drag-drop ordering of sections and tiles.
	 */
	public static function handle_admin_custom_dashboard_tiles_reorder_ajax(): void {
		check_ajax_referer(self::admin_custom_dashboard_tiles_reorder_action(), 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(['code' => 'forbidden'], 403);
		}
		$section_order_raw = isset($_POST['section_order']) ? (array) wp_unslash($_POST['section_order']) : [];
		$tile_layout_raw   = isset($_POST['tile_layout']) ? (array) wp_unslash($_POST['tile_layout']) : [];

		$data = self::get_admin_custom_dashboard_tiles_data();

		// Apply section order.
		$ordered_section_ids = array_values(array_filter(array_map('sanitize_key', array_map('strval', $section_order_raw))));
		if (!empty($ordered_section_ids)) {
			$sections_by_id = [];
			foreach ($data['sections'] as $section) {
				$sections_by_id[$section['id']] = $section;
			}
			$reordered = [];
			$position = 0;
			foreach ($ordered_section_ids as $section_id) {
				if (isset($sections_by_id[$section_id])) {
					$section = $sections_by_id[$section_id];
					$section['order'] = $position++;
					$reordered[] = $section;
					unset($sections_by_id[$section_id]);
				}
			}
			foreach ($sections_by_id as $section) {
				$section['order'] = $position++;
				$reordered[] = $section;
			}
			$data['sections'] = $reordered;
		}

		// Apply tile layout: { section_id => [tile_id, tile_id, ...], ... }.
		if (!empty($tile_layout_raw)) {
			$tiles_by_id = [];
			foreach ($data['tiles'] as $tile) {
				$tiles_by_id[$tile['id']] = $tile;
			}
			$reordered_tiles = [];
			foreach ($tile_layout_raw as $section_id => $tile_ids) {
				$section_id = sanitize_key((string) $section_id);
				if (!is_array($tile_ids)) {
					continue;
				}
				$position = 0;
				foreach ($tile_ids as $tile_id) {
					$tile_id = sanitize_key((string) $tile_id);
					if ($tile_id === '' || !isset($tiles_by_id[$tile_id])) {
						continue;
					}
					$tile = $tiles_by_id[$tile_id];
					$tile['section_id'] = $section_id;
					$tile['order']      = $position++;
					$reordered_tiles[]  = $tile;
					unset($tiles_by_id[$tile_id]);
				}
			}
			foreach ($tiles_by_id as $tile) {
				$reordered_tiles[] = $tile;
			}
			$data['tiles'] = $reordered_tiles;
		}

		self::save_admin_custom_dashboard_tiles_data($data);
		wp_send_json_success();
	}

	/**
	 * Handle section add/update via admin-post.
	 */
	public static function handle_admin_custom_dashboard_tiles_save_section(): void {
		self::verify_admin_custom_dashboard_tiles_manage_request('um_admin_dashboard_tiles_save_section');
		$data = self::get_admin_custom_dashboard_tiles_data();

		$section_id = isset($_POST['section_id']) ? sanitize_key((string) wp_unslash($_POST['section_id'])) : '';
		$title = isset($_POST['section_title']) ? sanitize_text_field(wp_unslash($_POST['section_title'])) : '';
		$description = isset($_POST['section_description']) ? sanitize_textarea_field(wp_unslash($_POST['section_description'])) : '';

		if ($title === '') {
			self::redirect_to_admin_custom_dashboard_tiles_settings('section_title_required');
		}

		if ($section_id !== '') {
			$updated = false;
			foreach ($data['sections'] as $index => $section) {
				if ($section['id'] === $section_id) {
					$data['sections'][$index]['title'] = $title;
					$data['sections'][$index]['description'] = $description;
					$updated = true;
					break;
				}
			}
			if (!$updated) {
				$section_id = '';
			}
		}
		if ($section_id === '') {
			$data['sections'][] = [
				'id'          => self::generate_admin_custom_dashboard_tiles_id('section'),
				'title'       => $title,
				'description' => $description,
				'order'       => count($data['sections']),
			];
		}

		self::save_admin_custom_dashboard_tiles_data($data);
		self::redirect_to_admin_custom_dashboard_tiles_settings('section_saved');
	}

	/**
	 * Handle section delete via admin-post.
	 */
	public static function handle_admin_custom_dashboard_tiles_delete_section(): void {
		self::verify_admin_custom_dashboard_tiles_manage_request('um_admin_dashboard_tiles_delete_section');
		$section_id = isset($_POST['section_id']) ? sanitize_key((string) wp_unslash($_POST['section_id'])) : '';
		if ($section_id === '') {
			$section_id = isset($_GET['section_id']) ? sanitize_key((string) wp_unslash($_GET['section_id'])) : '';
		}
		if ($section_id === '') {
			self::redirect_to_admin_custom_dashboard_tiles_settings('section_missing');
		}

		$data = self::get_admin_custom_dashboard_tiles_data();
		$data['sections'] = array_values(array_filter($data['sections'], static function ($section) use ($section_id): bool {
			return isset($section['id']) && $section['id'] !== $section_id;
		}));
		// Move this section's tiles to Uncategorized.
		foreach ($data['tiles'] as $index => $tile) {
			if (isset($tile['section_id']) && $tile['section_id'] === $section_id) {
				$data['tiles'][$index]['section_id'] = '';
			}
		}
		self::save_admin_custom_dashboard_tiles_data($data);
		self::redirect_to_admin_custom_dashboard_tiles_settings('section_deleted');
	}

	/**
	 * Handle tile add/update via admin-post.
	 */
	public static function handle_admin_custom_dashboard_tiles_save_tile(): void {
		self::verify_admin_custom_dashboard_tiles_manage_request('um_admin_dashboard_tiles_save_tile');

		$tile_id = isset($_POST['tile_id']) ? sanitize_key((string) wp_unslash($_POST['tile_id'])) : '';
		$title = isset($_POST['tile_title']) ? sanitize_text_field(wp_unslash($_POST['tile_title'])) : '';
		$url = isset($_POST['tile_url']) ? (string) wp_unslash($_POST['tile_url']) : '';
		$section_id = isset($_POST['tile_section']) ? sanitize_key((string) wp_unslash($_POST['tile_section'])) : '';
		$notes = isset($_POST['tile_notes']) ? sanitize_textarea_field(wp_unslash($_POST['tile_notes'])) : '';
		$new_window = isset($_POST['tile_new_window']) && (string) $_POST['tile_new_window'] === '1';

		$url = self::sanitize_admin_custom_dashboard_tiles_url($url);
		if ($title === '' || $url === '') {
			self::redirect_to_admin_custom_dashboard_tiles_settings('tile_fields_required');
		}

		$data = self::get_admin_custom_dashboard_tiles_data();

		if ($tile_id !== '') {
			$updated = false;
			foreach ($data['tiles'] as $index => $tile) {
				if ($tile['id'] === $tile_id) {
					$data['tiles'][$index]['title'] = $title;
					$data['tiles'][$index]['url'] = $url;
					$data['tiles'][$index]['section_id'] = $section_id;
					$data['tiles'][$index]['notes'] = $notes;
					$data['tiles'][$index]['new_window'] = $new_window;
					$updated = true;
					break;
				}
			}
			if (!$updated) {
				$tile_id = '';
			}
		}
		if ($tile_id === '') {
			$data['tiles'][] = [
				'id'         => self::generate_admin_custom_dashboard_tiles_id('tile'),
				'title'      => $title,
				'url'        => $url,
				'section_id' => $section_id,
				'notes'      => $notes,
				'new_window' => $new_window,
				'order'      => count($data['tiles']),
			];
		}

		self::save_admin_custom_dashboard_tiles_data($data);
		self::redirect_to_admin_custom_dashboard_tiles_settings('tile_saved');
	}

	/**
	 * Handle tile delete via admin-post.
	 */
	public static function handle_admin_custom_dashboard_tiles_delete_tile(): void {
		self::verify_admin_custom_dashboard_tiles_manage_request('um_admin_dashboard_tiles_delete_tile');
		$tile_id = isset($_POST['tile_id']) ? sanitize_key((string) wp_unslash($_POST['tile_id'])) : '';
		if ($tile_id === '') {
			$tile_id = isset($_GET['tile_id']) ? sanitize_key((string) wp_unslash($_GET['tile_id'])) : '';
		}
		if ($tile_id === '') {
			self::redirect_to_admin_custom_dashboard_tiles_settings('tile_missing');
		}

		$data = self::get_admin_custom_dashboard_tiles_data();
		$data['tiles'] = array_values(array_filter($data['tiles'], static function ($tile) use ($tile_id): bool {
			return isset($tile['id']) && $tile['id'] !== $tile_id;
		}));
		self::save_admin_custom_dashboard_tiles_data($data);
		self::redirect_to_admin_custom_dashboard_tiles_settings('tile_deleted');
	}

	/**
	 * Handle JSON import; duplicates (by id or by label+url) are skipped.
	 */
	public static function handle_admin_custom_dashboard_tiles_import(): void {
		self::verify_admin_custom_dashboard_tiles_manage_request('um_admin_dashboard_tiles_import');
		$payload_raw = isset($_POST['um_admin_dashboard_tiles_import_payload'])
			? (string) wp_unslash($_POST['um_admin_dashboard_tiles_import_payload'])
			: '';
		$payload = json_decode($payload_raw, true);
		if (!is_array($payload)) {
			self::redirect_to_admin_custom_dashboard_tiles_settings('import_invalid_json');
		}

		$data = self::get_admin_custom_dashboard_tiles_data();
		$incoming_sections = isset($payload['sections']) && is_array($payload['sections']) ? $payload['sections'] : [];
		$incoming_tiles    = isset($payload['tiles']) && is_array($payload['tiles']) ? $payload['tiles'] : [];

		$section_ids_by_title = [];
		foreach ($data['sections'] as $section) {
			$section_ids_by_title[strtolower($section['title'])] = $section['id'];
		}
		$tile_fingerprints = [];
		foreach ($data['tiles'] as $tile) {
			$tile_fingerprints[strtolower($tile['title'] . '|' . $tile['url'])] = true;
		}

		$sections_added = 0;
		foreach ($incoming_sections as $section) {
			$section = self::normalize_admin_custom_dashboard_tiles_section($section);
			if ($section['title'] === '') {
				continue;
			}
			$existing_id_by_id = false;
			foreach ($data['sections'] as $existing) {
				if ($existing['id'] === $section['id']) {
					$existing_id_by_id = true;
					break;
				}
			}
			$existing_id_by_title = isset($section_ids_by_title[strtolower($section['title'])]);
			if ($existing_id_by_id || $existing_id_by_title) {
				continue;
			}
			$data['sections'][] = $section;
			$section_ids_by_title[strtolower($section['title'])] = $section['id'];
			$sections_added++;
		}

		$tiles_added = 0;
		foreach ($incoming_tiles as $tile) {
			$tile = self::normalize_admin_custom_dashboard_tiles_tile($tile);
			if ($tile['title'] === '' || $tile['url'] === '') {
				continue;
			}
			$fingerprint = strtolower($tile['title'] . '|' . $tile['url']);
			if (isset($tile_fingerprints[$fingerprint])) {
				continue;
			}
			$id_collision = false;
			foreach ($data['tiles'] as $existing) {
				if ($existing['id'] === $tile['id']) {
					$id_collision = true;
					break;
				}
			}
			if ($id_collision) {
				continue;
			}
			$data['tiles'][] = $tile;
			$tile_fingerprints[$fingerprint] = true;
			$tiles_added++;
		}

		self::save_admin_custom_dashboard_tiles_data($data);
		$message = 'import_ok';
		if ($sections_added === 0 && $tiles_added === 0) {
			$message = 'import_nothing_new';
		}
		self::redirect_to_admin_custom_dashboard_tiles_settings($message);
	}

	/**
	 * Shared nonce + capability guard for manage-side endpoints.
	 */
	private static function verify_admin_custom_dashboard_tiles_manage_request(string $action): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to manage Administrator Custom Dashboard Tiles.', 'user-manager'), '', ['response' => 403]);
		}
		check_admin_referer($action);
	}

	/**
	 * Redirect back to the Settings tab with a status code.
	 */
	private static function redirect_to_admin_custom_dashboard_tiles_settings(string $status): void {
		$url = add_query_arg(
			[
				'page'       => self::admin_custom_dashboard_tiles_page_slug(),
				'tab'        => 'settings',
				'um_ctiles'  => sanitize_key($status),
			],
			admin_url('admin.php')
		);
		wp_safe_redirect($url);
		exit;
	}

	/**
	 * Admin bar: add the Custom Dashboard Tiles dropdown with favorites.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function add_admin_custom_dashboard_tiles_admin_bar_node($wp_admin_bar): void {
		if (!($wp_admin_bar instanceof WP_Admin_Bar)) {
			return;
		}
		if (!current_user_can('read') || !is_user_logged_in()) {
			return;
		}

		$settings    = self::get_settings();
		$menu_title  = self::get_admin_custom_dashboard_tiles_menu_title($settings);
		$favorites   = self::get_admin_custom_dashboard_tiles_favorites_for_current_user();
		$data        = self::get_admin_custom_dashboard_tiles_data();
		$tiles_by_id = [];
		foreach ($data['tiles'] as $tile) {
			$tiles_by_id[$tile['id']] = $tile;
		}

		$favorite_tiles = [];
		foreach ($favorites as $tile_id) {
			if (isset($tiles_by_id[$tile_id])) {
				$favorite_tiles[] = $tiles_by_id[$tile_id];
			}
		}
		$favorite_count = count($favorite_tiles);

		$page_url = admin_url('admin.php?page=' . self::admin_custom_dashboard_tiles_page_slug());
		$label = $favorite_count > 0
			? sprintf('%s (%d)', $menu_title, $favorite_count)
			: $menu_title;

		$wp_admin_bar->add_node([
			'id'     => 'um-admin-custom-dashboard-tiles',
			'title'  => '<span class="ab-icon dashicons dashicons-admin-links"></span><span class="ab-label">' . esc_html($label) . '</span>',
			'href'   => $page_url,
			'parent' => 'top-secondary',
			'meta'   => [
				'title' => $menu_title,
				'class' => 'um-admin-custom-dashboard-tiles-admin-bar menupop',
			],
		]);

		foreach ($favorite_tiles as $tile) {
			$wp_admin_bar->add_node([
				'id'     => 'um-admin-custom-dashboard-tiles-fav-' . sanitize_html_class($tile['id']),
				'title'  => esc_html($tile['title']),
				'href'   => esc_url($tile['url']),
				'parent' => 'um-admin-custom-dashboard-tiles',
				'meta'   => [
					'title'  => $tile['title'],
					'target' => $tile['new_window'] ? '_blank' : '',
					'rel'    => $tile['new_window'] ? 'noopener noreferrer' : '',
				],
			]);
		}

		$current_path = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
		$prefill_url = add_query_arg(
			[
				'page'             => self::admin_custom_dashboard_tiles_page_slug(),
				'tab'              => 'settings',
				'prefill_tile_url' => rawurlencode($current_path),
			],
			admin_url('admin.php')
		);
		$wp_admin_bar->add_node([
			'id'     => 'um-admin-custom-dashboard-tiles-add-current-page',
			'title'  => esc_html__('Add Current Page to Dashboard Tiles', 'user-manager'),
			'href'   => esc_url($prefill_url),
			'parent' => 'um-admin-custom-dashboard-tiles',
			'meta'   => [
				'title' => __('Open Dashboard Tiles settings with the current URL pre-filled', 'user-manager'),
			],
		]);
	}

	/**
	 * Render a tiny style snippet so the admin bar icon always shows on mobile
	 * (WP default stylesheet hides the label below the mobile breakpoint).
	 */
	public static function render_admin_custom_dashboard_tiles_admin_bar_styles(): void {
		if (!is_admin_bar_showing()) {
			return;
		}
		?>
		<style id="um-admin-custom-dashboard-tiles-admin-bar-css">
			#wpadminbar #wp-admin-bar-um-admin-custom-dashboard-tiles > .ab-item .ab-icon { display: inline-block; }
			#wpadminbar #wp-admin-bar-um-admin-custom-dashboard-tiles > .ab-item .ab-icon::before { content: "\f103"; font-family: dashicons; top: 2px; }
			@media screen and (max-width: 782px) {
				#wpadminbar #wp-admin-bar-um-admin-custom-dashboard-tiles { display: block !important; }
				#wpadminbar #wp-admin-bar-um-admin-custom-dashboard-tiles > .ab-item { padding: 0 8px; position: relative; text-indent: 100%; overflow: hidden; white-space: nowrap; }
				#wpadminbar #wp-admin-bar-um-admin-custom-dashboard-tiles > .ab-item .ab-icon { position: absolute; left: 8px; top: 0; text-indent: 0; }
				#wpadminbar #wp-admin-bar-um-admin-custom-dashboard-tiles > .ab-item .ab-icon::before { color: #a7aaad; }
			}
		</style>
		<?php
	}
}
