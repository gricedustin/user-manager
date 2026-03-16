<?php
/**
 * Add-on card: Database Table Browser.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Database_Table_Browser {

	public static function render(array $settings): void {
		$enabled = !empty($settings['database_table_browser_enabled']);
		$per_page_limit = isset($settings['database_table_browser_per_page_limit']) ? absint($settings['database_table_browser_per_page_limit']) : 100;
		if ($per_page_limit < 1) {
			$per_page_limit = 100;
		}
		if ($per_page_limit > 1000) {
			$per_page_limit = 1000;
		}

		$current_section = isset($_GET['addon_section']) ? sanitize_key(wp_unslash($_GET['addon_section'])) : '';
		$is_viewing_browser = $current_section === 'database-table-browser';
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-database-table-browser" data-um-active-selectors="#um-database-table-browser-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-database"></span>
				<h2><?php esc_html_e('Database Table Browser', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-database-table-browser-enabled" name="database_table_browser_enabled" value="1" <?php checked($enabled); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Browse WordPress database tables in WP-Admin, including table structure, row counts, and paginated record views.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-database-table-browser-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-database-table-browser-limit"><?php esc_html_e('Records Per Page', 'user-manager'); ?></label>
						<input type="number" min="1" max="1000" step="1" class="small-text" id="um-database-table-browser-limit" name="database_table_browser_per_page_limit" value="<?php echo esc_attr((string) $per_page_limit); ?>" />
						<p class="description"><?php esc_html_e('How many rows to show per page when browsing a table.', 'user-manager'); ?></p>
					</div>

					<?php if ($is_viewing_browser) : ?>
						<?php self::render_browser($per_page_limit); ?>
					<?php else : ?>
						<p class="description"><?php esc_html_e('Open this add-on to browse all tables and drill into specific table rows.', 'user-manager'); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render table list + selected table records.
	 */
	private static function render_browser(int $per_page_limit): void {
		global $wpdb;

		$all_tables = self::get_all_tables();
		$selected_table = isset($_GET['um_db_browser_table']) ? sanitize_text_field(wp_unslash($_GET['um_db_browser_table'])) : '';
		$current_page = isset($_GET['um_db_browser_paged']) ? max(1, absint($_GET['um_db_browser_paged'])) : 1;
		$offset = ($current_page - 1) * $per_page_limit;

		$addon_tag = isset($_GET['addon_tag']) ? sanitize_title(wp_unslash($_GET['addon_tag'])) : '';
		$base_args = ['addon_section' => 'database-table-browser'];
		if ($addon_tag !== '') {
			$base_args['addon_tag'] = $addon_tag;
		}
		$base_url = add_query_arg($base_args, User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS));

		echo '<hr style="margin:18px 0;" />';

		if ($selected_table !== '') {
			if (!in_array($selected_table, $all_tables, true)) {
				echo '<p><strong>' . esc_html__('Selected table is not allowed or no longer exists.', 'user-manager') . '</strong></p>';
				echo '<p><a href="' . esc_url($base_url) . '">&larr; ' . esc_html__('Back to all tables', 'user-manager') . '</a></p>';
				return;
			}

			$nonce_action = 'um_db_browser_view_' . md5($selected_table);
			$nonce_value = isset($_GET['um_db_browser_nonce']) ? sanitize_text_field(wp_unslash($_GET['um_db_browser_nonce'])) : '';
			if (!wp_verify_nonce($nonce_value, $nonce_action)) {
				echo '<p><strong>' . esc_html__('Security check failed for this table view.', 'user-manager') . '</strong></p>';
				echo '<p><a href="' . esc_url($base_url) . '">&larr; ' . esc_html__('Back to all tables', 'user-manager') . '</a></p>';
				return;
			}

			$table_identifier = str_replace('`', '``', $selected_table);
			$total_rows = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$table_identifier}`");
			$columns = $wpdb->get_col("SHOW COLUMNS FROM `{$table_identifier}`", 0);
			if (!is_array($columns)) {
				$columns = [];
			}
			$rows = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM `{$table_identifier}` LIMIT %d OFFSET %d", $per_page_limit, $offset),
				ARRAY_A
			);
			if (!is_array($rows)) {
				$rows = [];
			}

			echo '<p><a href="' . esc_url($base_url) . '">&larr; ' . esc_html__('Back to all tables', 'user-manager') . '</a></p>';
			echo '<h3 style="margin:10px 0;">' . esc_html__('Table:', 'user-manager') . ' ' . esc_html($selected_table) . '</h3>';

			if (empty($columns)) {
				echo '<p class="description">' . esc_html__('No columns found for this table.', 'user-manager') . '</p>';
				return;
			}

			echo '<div style="overflow:auto;">';
			echo '<table class="widefat striped">';
			echo '<thead><tr>';
			foreach ($columns as $column_name) {
				echo '<th>' . esc_html((string) $column_name) . '</th>';
			}
			echo '</tr></thead><tbody>';
			if (empty($rows)) {
				echo '<tr><td colspan="' . esc_attr((string) count($columns)) . '">' . esc_html__('No rows found for this page.', 'user-manager') . '</td></tr>';
			} else {
				foreach ($rows as $row) {
					echo '<tr>';
					foreach ($columns as $column_name) {
						$value = $row[$column_name] ?? '';
						if (!is_scalar($value)) {
							$value = wp_json_encode($value);
						}
						echo '<td>' . esc_html((string) $value) . '</td>';
					}
					echo '</tr>';
				}
			}
			echo '</tbody></table>';
			echo '</div>';

			$total_pages = (int) ceil($total_rows / $per_page_limit);
			if ($total_pages > 1 && function_exists('paginate_links')) {
				$page_links = paginate_links([
					'base' => add_query_arg(
						[
							'addon_section' => 'database-table-browser',
							'addon_tag' => $addon_tag,
							'um_db_browser_table' => $selected_table,
							'um_db_browser_nonce' => wp_create_nonce($nonce_action),
							'um_db_browser_paged' => '%#%',
						],
						User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS)
					),
					'format' => '',
					'current' => $current_page,
					'total' => $total_pages,
					'type' => 'plain',
				]);
				if (is_string($page_links) && $page_links !== '') {
					echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post($page_links) . '</div></div>';
				}
			}

			return;
		}

		echo '<h3 style="margin:8px 0;">' . esc_html__('All Tables', 'user-manager') . '</h3>';
		echo '<table class="widefat striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__('Table', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Columns', 'user-manager') . '</th>';
		echo '<th>' . esc_html__('Rows', 'user-manager') . '</th>';
		echo '</tr></thead><tbody>';
		foreach ($all_tables as $table_name) {
			$table_identifier = str_replace('`', '``', $table_name);
			$count = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$table_identifier}`");
			$column_names = $wpdb->get_col("SHOW COLUMNS FROM `{$table_identifier}`", 0);
			if (!is_array($column_names)) {
				$column_names = [];
			}
			$columns_preview = implode(', ', array_map('strval', $column_names));

			$table_nonce = wp_create_nonce('um_db_browser_view_' . md5($table_name));
			$table_url = add_query_arg(
				[
					'addon_section' => 'database-table-browser',
					'addon_tag' => $addon_tag,
					'um_db_browser_table' => $table_name,
					'um_db_browser_nonce' => $table_nonce,
				],
				User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS)
			);

			echo '<tr>';
			echo '<td><a href="' . esc_url($table_url) . '">' . esc_html($table_name) . '</a></td>';
			echo '<td>' . esc_html($columns_preview) . '</td>';
			echo '<td>' . esc_html((string) $count) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Return all table names in the current database.
	 *
	 * @return array<int,string>
	 */
	private static function get_all_tables(): array {
		global $wpdb;
		$tables = $wpdb->get_col('SHOW TABLES');
		if (!is_array($tables)) {
			return [];
		}

		return array_values(array_filter(array_map('strval', $tables)));
	}
}

