<?php
/**
 * My Account Admin: Additional Meta Fields / Comparison Flags repeater UI.
 *
 * Renders the "Add Meta Field" / "Add Comparison Flag" repeater UI used by
 * the My Account Admin addon card (`class-user-manager-addon-my-account-site-admin.php`).
 *
 * The repeater stores its output in a hidden input so the existing
 * raw-string parser on the backend (see
 * `parse_order_additional_meta_field_definitions()` and
 * `parse_order_list_additional_meta_compare_flags()` in
 * `class-user-manager-my-account-site-admin.php`) continues to work unchanged.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Addon_My_Account_Admin_Meta_Fields_Repeater_Trait {

	/**
	 * Render the "Add Meta Field" repeater UI for the additional meta fields
	 * settings. The raw meta format used by the parsers is preserved in a
	 * hidden input so existing save/parse logic stays unchanged.
	 *
	 * @param string $name     The settings field name (stores the combined raw value).
	 * @param string $field_id DOM id used for the hidden input.
	 * @param string $raw      Current stored raw value.
	 */
	private static function render_additional_meta_fields_repeater(string $name, string $field_id, string $raw): void {
		$rows = self::parse_additional_meta_fields_raw($raw);
		$container_id = $field_id . '-repeater';
		?>
		<input type="hidden" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($raw); ?>" data-um-meta-fields-raw-input="1" />
		<div class="um-meta-fields-repeater" id="<?php echo esc_attr($container_id); ?>" data-um-meta-fields-repeater data-um-meta-fields-target="<?php echo esc_attr($field_id); ?>">
			<div class="um-meta-fields-repeater-rows" data-um-meta-fields-rows>
				<?php if (empty($rows)) : ?>
					<?php self::render_additional_meta_fields_repeater_row([]); ?>
				<?php else : ?>
					<?php foreach ($rows as $row) : ?>
						<?php self::render_additional_meta_fields_repeater_row($row); ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="um-meta-fields-repeater-actions" style="margin-top:8px;">
				<button type="button" class="button button-secondary" data-um-meta-fields-add>
					<span class="dashicons dashicons-plus-alt" style="line-height:1.2;"></span>
					<?php esc_html_e('Add Meta Field', 'user-manager'); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single meta-field repeater row.
	 *
	 * @param array{key?:string,label?:string,prefix?:string,flags?:array<int,string>} $row
	 */
	private static function render_additional_meta_fields_repeater_row(array $row): void {
		$key    = isset($row['key']) ? (string) $row['key'] : '';
		$label  = isset($row['label']) ? (string) $row['label'] : '';
		$prefix = isset($row['prefix']) ? (string) $row['prefix'] : '';
		$flags  = isset($row['flags']) && is_array($row['flags']) ? array_map('strval', $row['flags']) : [];
		?>
		<div class="um-meta-fields-repeater-row" data-um-meta-fields-row>
			<div class="um-meta-fields-repeater-row-grid">
				<label class="um-meta-fields-repeater-cell">
					<span><?php esc_html_e('Meta Field', 'user-manager'); ?></span>
					<input type="text" class="regular-text" data-um-meta-fields-key placeholder="_tracking_number" value="<?php echo esc_attr($key); ?>" />
				</label>
				<label class="um-meta-fields-repeater-cell">
					<span><?php esc_html_e('Custom Label (optional)', 'user-manager'); ?></span>
					<input type="text" class="regular-text" data-um-meta-fields-label placeholder="<?php esc_attr_e('Tracking Number', 'user-manager'); ?>" value="<?php echo esc_attr($label); ?>" />
				</label>
				<label class="um-meta-fields-repeater-cell">
					<span><?php esc_html_e('Prefix Before Value (optional)', 'user-manager'); ?></span>
					<input type="text" class="regular-text" data-um-meta-fields-prefix placeholder="https://example.com/uploads/" value="<?php echo esc_attr($prefix); ?>" />
				</label>
			</div>
			<div class="um-meta-fields-repeater-flags">
				<span class="um-meta-fields-repeater-flags-label"><?php esc_html_e('Flags:', 'user-manager'); ?></span>
				<label><input type="checkbox" data-um-meta-fields-flag="text_line_count" <?php checked(in_array('text_line_count', $flags, true)); ?> /> <?php esc_html_e('Count file lines', 'user-manager'); ?></label>
				<label><input type="checkbox" data-um-meta-fields-flag="preview" <?php checked(in_array('preview', $flags, true)); ?> /> <?php esc_html_e('Preview in modal', 'user-manager'); ?></label>
				<label><input type="checkbox" data-um-meta-fields-flag="display_when_empty" <?php checked(in_array('display_when_empty', $flags, true)); ?> /> <?php esc_html_e('Show row when empty', 'user-manager'); ?></label>
			</div>
			<div class="um-meta-fields-repeater-row-actions">
				<button type="button" class="button-link button-link-delete" data-um-meta-fields-remove>
					<span class="dashicons dashicons-trash" style="line-height:1.4;"></span>
					<?php esc_html_e('Remove', 'user-manager'); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the repeater UI for the "Additional Flag to Display Below
	 * Additional Fields" setting. Stores the raw newline-joined format in a
	 * hidden input for the existing parser.
	 *
	 * @param string $name     Settings field name.
	 * @param string $field_id DOM id for the hidden input.
	 * @param string $raw      Current stored raw value.
	 */
	private static function render_additional_meta_compare_flags_repeater(string $name, string $field_id, string $raw): void {
		$rows = self::parse_additional_meta_compare_flags_raw($raw);
		$container_id = $field_id . '-repeater';
		?>
		<input type="hidden" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($raw); ?>" data-um-meta-compare-flags-raw-input="1" />
		<div class="um-meta-fields-repeater" id="<?php echo esc_attr($container_id); ?>" data-um-meta-compare-flags-repeater data-um-meta-compare-flags-target="<?php echo esc_attr($field_id); ?>">
			<div class="um-meta-fields-repeater-rows" data-um-meta-compare-flags-rows>
				<?php if (empty($rows)) : ?>
					<?php self::render_additional_meta_compare_flags_repeater_row([]); ?>
				<?php else : ?>
					<?php foreach ($rows as $row) : ?>
						<?php self::render_additional_meta_compare_flags_repeater_row($row); ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="um-meta-fields-repeater-actions" style="margin-top:8px;">
				<button type="button" class="button button-secondary" data-um-meta-compare-flags-add>
					<span class="dashicons dashicons-plus-alt" style="line-height:1.2;"></span>
					<?php esc_html_e('Add Comparison Flag', 'user-manager'); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single compare-flag row.
	 *
	 * @param array{meta_a?:string,meta_b?:string,operator?:string,grace?:string,title?:string,bg?:string,text?:string} $row
	 */
	private static function render_additional_meta_compare_flags_repeater_row(array $row): void {
		$meta_a   = isset($row['meta_a']) ? (string) $row['meta_a'] : '';
		$meta_b   = isset($row['meta_b']) ? (string) $row['meta_b'] : '';
		$grace    = isset($row['grace']) ? (string) $row['grace'] : '';
		$title    = isset($row['title']) ? (string) $row['title'] : '';
		$bg       = isset($row['bg']) ? (string) $row['bg'] : '';
		$text     = isset($row['text']) ? (string) $row['text'] : '';
		$operator = isset($row['operator']) ? (string) $row['operator'] : 'are_they_equal';
		if ($operator !== 'are_they_not_equal') {
			$operator = 'are_they_equal';
		}
		?>
		<div class="um-meta-fields-repeater-row um-meta-fields-repeater-row-compare" data-um-meta-compare-flags-row>
			<div class="um-meta-fields-repeater-row-grid um-meta-fields-repeater-row-grid-compare">
				<label class="um-meta-fields-repeater-cell">
					<span><?php esc_html_e('Meta Field A', 'user-manager'); ?></span>
					<input type="text" class="regular-text" data-um-meta-compare-flags-a placeholder="_meta_field_a" value="<?php echo esc_attr($meta_a); ?>" />
				</label>
				<label class="um-meta-fields-repeater-cell">
					<span><?php esc_html_e('Meta Field B', 'user-manager'); ?></span>
					<input type="text" class="regular-text" data-um-meta-compare-flags-b placeholder="_meta_field_b" value="<?php echo esc_attr($meta_b); ?>" />
				</label>
				<label class="um-meta-fields-repeater-cell">
					<span><?php esc_html_e('Show flag when', 'user-manager'); ?></span>
					<select class="regular-text" data-um-meta-compare-flags-operator>
						<option value="are_they_equal" <?php selected($operator, 'are_they_equal'); ?>><?php esc_html_e('Values are equal', 'user-manager'); ?></option>
						<option value="are_they_not_equal" <?php selected($operator, 'are_they_not_equal'); ?>><?php esc_html_e('Values are NOT equal', 'user-manager'); ?></option>
					</select>
				</label>
				<label class="um-meta-fields-repeater-cell">
					<span><?php esc_html_e('Grace Value (optional, numeric)', 'user-manager'); ?></span>
					<input type="text" class="regular-text" data-um-meta-compare-flags-grace placeholder="3" value="<?php echo esc_attr($grace); ?>" />
				</label>
				<label class="um-meta-fields-repeater-cell um-meta-fields-repeater-cell-wide">
					<span><?php esc_html_e('Flag Title', 'user-manager'); ?></span>
					<input type="text" class="regular-text" data-um-meta-compare-flags-title placeholder="FLAG TITLE" value="<?php echo esc_attr($title); ?>" />
				</label>
				<label class="um-meta-fields-repeater-cell">
					<span><?php esc_html_e('Background Color', 'user-manager'); ?></span>
					<input type="text" class="regular-text" data-um-meta-compare-flags-bg placeholder="#000000" value="<?php echo esc_attr($bg); ?>" />
				</label>
				<label class="um-meta-fields-repeater-cell">
					<span><?php esc_html_e('Text Color', 'user-manager'); ?></span>
					<input type="text" class="regular-text" data-um-meta-compare-flags-text placeholder="#ffffff" value="<?php echo esc_attr($text); ?>" />
				</label>
			</div>
			<p class="description" style="margin:6px 0 0;">
				<?php esc_html_e('Without a grace value: "Values are equal" flags on exact match; "Values are NOT equal" flags when they differ. With a grace value (both values must be numeric): "Values are equal" flags when ABS(A − B) > grace, "Values are NOT equal" flags when ABS(A − B) ≤ grace.', 'user-manager'); ?>
			</p>
			<div class="um-meta-fields-repeater-row-actions">
				<button type="button" class="button-link button-link-delete" data-um-meta-compare-flags-remove>
					<span class="dashicons dashicons-trash" style="line-height:1.4;"></span>
					<?php esc_html_e('Remove', 'user-manager'); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Parse the stored "additional meta fields" raw value back into UI rows
	 * for hydration on page load.
	 *
	 * @return array<int,array{key:string,label:string,prefix:string,flags:array<int,string>}>
	 */
	private static function parse_additional_meta_fields_raw(string $raw): array {
		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}
		$parts = preg_split('/[\r\n,]+/', $raw);
		if (!is_array($parts)) {
			return [];
		}

		$rows = [];
		foreach ($parts as $part) {
			$part = trim((string) $part);
			if ($part === '') {
				continue;
			}

			$meta_key = $part;
			$label = '';
			$prefix = '';
			$flags = [];
			if (strpos($part, ':') !== false) {
				$pair = explode(':', $part, 3);
				$meta_key = isset($pair[0]) ? trim((string) $pair[0]) : '';
				$label    = isset($pair[1]) ? trim((string) $pair[1]) : '';
				$prefix_and_flags = isset($pair[2]) ? (string) $pair[2] : '';

				$prefix_raw = $prefix_and_flags;
				$flags_raw  = '';
				$double_colon_pos = strrpos($prefix_and_flags, '::');
				if ($double_colon_pos !== false) {
					$prefix_raw = trim(substr($prefix_and_flags, 0, $double_colon_pos));
					$flags_raw  = trim(substr($prefix_and_flags, $double_colon_pos + 2));
				} else {
					$last_colon_pos = strrpos($prefix_and_flags, ':');
					if ($last_colon_pos !== false) {
						$maybe_prefix = trim(substr($prefix_and_flags, 0, $last_colon_pos));
						$maybe_flags  = trim(substr($prefix_and_flags, $last_colon_pos + 1));
						if ($maybe_flags !== '' && preg_match('/^[a-z0-9_\-\|,\s]+$/i', $maybe_flags)) {
							$prefix_raw = $maybe_prefix;
							$flags_raw  = $maybe_flags;
						}
					}
				}

				$prefix = trim($prefix_raw);
				$flag_tokens = preg_split('/[\s,\|]+/', strtolower(trim($flags_raw)));
				if (is_array($flag_tokens)) {
					foreach ($flag_tokens as $token) {
						$token = trim($token);
						if ($token === '') {
							continue;
						}
						$canonical = self::canonicalize_meta_field_flag($token);
						if ($canonical !== '' && !in_array($canonical, $flags, true)) {
							$flags[] = $canonical;
						}
					}
				}
			}

			$meta_key = trim($meta_key);
			if ($meta_key === '') {
				continue;
			}

			$rows[] = [
				'key'    => $meta_key,
				'label'  => $label,
				'prefix' => $prefix,
				'flags'  => $flags,
			];
		}

		return $rows;
	}

	/**
	 * Normalize any supported flag synonym back to the canonical form used
	 * by the UI checkboxes.
	 */
	private static function canonicalize_meta_field_flag(string $token): string {
		$token = strtolower(trim($token));
		$line_count_synonyms = ['text_line_count', 'text-file-line-count', 'line_count', 'count_lines'];
		$preview_synonyms    = ['preview', 'preview_file', 'file_preview', 'preview-modal', 'preview_modal'];
		$display_synonyms    = ['display_when_empty', 'display-empty', 'show_empty', 'show_if_empty', 'render_if_empty'];
		if (in_array($token, $line_count_synonyms, true)) {
			return 'text_line_count';
		}
		if (in_array($token, $preview_synonyms, true)) {
			return 'preview';
		}
		if (in_array($token, $display_synonyms, true)) {
			return 'display_when_empty';
		}
		return '';
	}

	/**
	 * Parse the stored "additional flag to display" raw value back into
	 * UI rows. Grammar reminder (one per line, operator is one of
	 * `are_they_equal` or `are_they_not_equal`):
	 *   meta_a:meta_b:<operator>:TITLE:bg:text
	 *   meta_a:meta_b:<operator>:grace:TITLE:bg:text
	 *
	 * @return array<int,array{meta_a:string,meta_b:string,operator:string,grace:string,title:string,bg:string,text:string}>
	 */
	private static function parse_additional_meta_compare_flags_raw(string $raw): array {
		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}
		$lines = preg_split('/[\r\n]+/', $raw);
		if (!is_array($lines)) {
			return [];
		}

		$rows = [];
		foreach ($lines as $line) {
			$line = trim((string) $line);
			if ($line === '') {
				continue;
			}
			$segments = explode(':', $line);
			if (count($segments) < 4) {
				continue;
			}
			$meta_a = trim((string) $segments[0]);
			$meta_b = trim((string) $segments[1]);
			$operator = strtolower(trim((string) $segments[2]));
			if (!in_array($operator, ['are_they_equal', 'are_they_not_equal'], true)) {
				continue;
			}

			$remaining = array_map('trim', array_slice($segments, 3));
			$grace = '';
			if (count($remaining) >= 2 && is_numeric($remaining[0])) {
				$grace = (string) $remaining[0];
				array_shift($remaining);
			}

			$bg = '';
			$text = '';
			$remaining_count = count($remaining);
			if ($remaining_count >= 3 && preg_match('/^#[0-9a-fA-F]{3,8}$/', $remaining[$remaining_count - 2]) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $remaining[$remaining_count - 1])) {
				$bg = $remaining[$remaining_count - 2];
				$text = $remaining[$remaining_count - 1];
				array_pop($remaining);
				array_pop($remaining);
			} elseif ($remaining_count >= 2 && preg_match('/^#[0-9a-fA-F]{3,8}$/', $remaining[$remaining_count - 1])) {
				$bg = $remaining[$remaining_count - 1];
				array_pop($remaining);
			}
			$title = trim(implode(':', $remaining));

			if ($meta_a === '' || $meta_b === '' || $title === '') {
				continue;
			}

			$rows[] = [
				'meta_a'   => $meta_a,
				'meta_b'   => $meta_b,
				'operator' => $operator,
				'grace'    => $grace,
				'title'    => $title,
				'bg'       => $bg,
				'text'     => $text,
			];
		}

		return $rows;
	}

	/**
	 * Render the shared CSS + JS that powers every repeater on the current
	 * addon card. Only output once per request so re-renders or multiple
	 * addon cards don't duplicate styles/handlers.
	 */
	private static function render_additional_meta_fields_repeater_assets(): void {
		static $rendered = false;
		if ($rendered) {
			return;
		}
		$rendered = true;
		?>
		<style>
		.um-meta-fields-repeater-row {
			padding: 10px;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			margin-bottom: 8px;
			background: #fbfbfc;
		}
		.um-meta-fields-repeater-row-grid {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 10px;
		}
		.um-meta-fields-repeater-row-grid-compare {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}
		.um-meta-fields-repeater-cell-wide {
			grid-column: span 3;
		}
		@media (max-width: 900px) {
			.um-meta-fields-repeater-row-grid,
			.um-meta-fields-repeater-row-grid-compare {
				grid-template-columns: 1fr;
			}
			.um-meta-fields-repeater-cell-wide {
				grid-column: auto;
			}
		}
		.um-meta-fields-repeater-cell {
			display: flex;
			flex-direction: column;
			gap: 4px;
			font-size: 12px;
		}
		.um-meta-fields-repeater-cell > span {
			color: #3c434a;
			font-weight: 600;
		}
		.um-meta-fields-repeater-cell input[type="text"] {
			width: 100%;
		}
		.um-meta-fields-repeater-flags {
			margin-top: 8px;
			display: flex;
			flex-wrap: wrap;
			gap: 10px 14px;
			align-items: center;
			font-size: 12px;
		}
		.um-meta-fields-repeater-flags-label {
			font-weight: 600;
			color: #3c434a;
		}
		.um-meta-fields-repeater-row-actions {
			margin-top: 8px;
			text-align: right;
		}
		.um-meta-fields-repeater-actions .button {
			display: inline-flex;
			align-items: center;
			gap: 4px;
		}
		</style>
		<script>
		(function(){
			if (window.umMetaFieldsRepeaterBound) {
				return;
			}
			window.umMetaFieldsRepeaterBound = true;

			function syncMetaFieldsRepeater(container) {
				var targetId = container.getAttribute('data-um-meta-fields-target');
				if (!targetId) { return; }
				var target = document.getElementById(targetId);
				if (!target) { return; }
				var rows = container.querySelectorAll('[data-um-meta-fields-row]');
				var out = [];
				for (var i = 0; i < rows.length; i++) {
					var row = rows[i];
					var keyInput = row.querySelector('[data-um-meta-fields-key]');
					var labelInput = row.querySelector('[data-um-meta-fields-label]');
					var prefixInput = row.querySelector('[data-um-meta-fields-prefix]');
					var flagInputs = row.querySelectorAll('[data-um-meta-fields-flag]');
					var key = (keyInput && keyInput.value ? keyInput.value : '').trim();
					if (!key) { continue; }
					var label = (labelInput && labelInput.value ? labelInput.value : '').trim();
					var prefix = (prefixInput && prefixInput.value ? prefixInput.value : '').trim();
					var flags = [];
					for (var f = 0; f < flagInputs.length; f++) {
						if (flagInputs[f].checked) {
							flags.push(flagInputs[f].getAttribute('data-um-meta-fields-flag'));
						}
					}
					var piece = key + ':' + label;
					if (prefix !== '' || flags.length > 0) {
						piece += ':' + prefix;
						if (flags.length > 0) {
							piece += '::' + flags.join('|');
						}
					}
					out.push(piece);
				}
				target.value = out.join(', ');
			}

			function syncCompareFlagsRepeater(container) {
				var targetId = container.getAttribute('data-um-meta-compare-flags-target');
				if (!targetId) { return; }
				var target = document.getElementById(targetId);
				if (!target) { return; }
				var rows = container.querySelectorAll('[data-um-meta-compare-flags-row]');
				var lines = [];
				for (var i = 0; i < rows.length; i++) {
					var row = rows[i];
					var a = (row.querySelector('[data-um-meta-compare-flags-a]') || {value:''}).value.trim();
					var b = (row.querySelector('[data-um-meta-compare-flags-b]') || {value:''}).value.trim();
					var opInput = row.querySelector('[data-um-meta-compare-flags-operator]');
					var op = (opInput && opInput.value ? opInput.value : 'are_they_equal').trim();
					if (op !== 'are_they_not_equal') { op = 'are_they_equal'; }
					var grace = (row.querySelector('[data-um-meta-compare-flags-grace]') || {value:''}).value.trim();
					var title = (row.querySelector('[data-um-meta-compare-flags-title]') || {value:''}).value.trim();
					var bg = (row.querySelector('[data-um-meta-compare-flags-bg]') || {value:''}).value.trim();
					var text = (row.querySelector('[data-um-meta-compare-flags-text]') || {value:''}).value.trim();
					if (!a || !b || !title) { continue; }
					var parts = [a, b, op];
					if (grace !== '' && !isNaN(parseFloat(grace))) {
						parts.push(grace);
					}
					parts.push(title);
					if (bg !== '') { parts.push(bg); }
					if (text !== '') { parts.push(text); }
					lines.push(parts.join(':'));
				}
				target.value = lines.join('\n');
			}

			function cloneBlankRow(row) {
				var clone = row.cloneNode(true);
				var inputs = clone.querySelectorAll('input');
				for (var i = 0; i < inputs.length; i++) {
					if (inputs[i].type === 'checkbox') {
						inputs[i].checked = false;
					} else {
						inputs[i].value = '';
					}
				}
				var selects = clone.querySelectorAll('select');
				for (var s = 0; s < selects.length; s++) {
					if (selects[s].options && selects[s].options.length > 0) {
						selects[s].selectedIndex = 0;
					}
				}
				return clone;
			}

			document.addEventListener('click', function(ev) {
				var addBtn = ev.target.closest && ev.target.closest('[data-um-meta-fields-add]');
				if (addBtn) {
					ev.preventDefault();
					var container = addBtn.closest('[data-um-meta-fields-repeater]');
					if (!container) { return; }
					var rowsWrap = container.querySelector('[data-um-meta-fields-rows]');
					var existing = rowsWrap.querySelector('[data-um-meta-fields-row]');
					if (!existing) { return; }
					var newRow = cloneBlankRow(existing);
					rowsWrap.appendChild(newRow);
					syncMetaFieldsRepeater(container);
					return;
				}
				var removeBtn = ev.target.closest && ev.target.closest('[data-um-meta-fields-remove]');
				if (removeBtn) {
					ev.preventDefault();
					var row = removeBtn.closest('[data-um-meta-fields-row]');
					var container2 = removeBtn.closest('[data-um-meta-fields-repeater]');
					if (row && container2) {
						var allRows = container2.querySelectorAll('[data-um-meta-fields-row]');
						if (allRows.length <= 1) {
							var inputs = row.querySelectorAll('input');
							for (var i = 0; i < inputs.length; i++) {
								if (inputs[i].type === 'checkbox') { inputs[i].checked = false; } else { inputs[i].value = ''; }
							}
						} else {
							row.parentNode.removeChild(row);
						}
						syncMetaFieldsRepeater(container2);
					}
					return;
				}
				var addCmp = ev.target.closest && ev.target.closest('[data-um-meta-compare-flags-add]');
				if (addCmp) {
					ev.preventDefault();
					var container3 = addCmp.closest('[data-um-meta-compare-flags-repeater]');
					if (!container3) { return; }
					var rowsWrap3 = container3.querySelector('[data-um-meta-compare-flags-rows]');
					var existing3 = rowsWrap3.querySelector('[data-um-meta-compare-flags-row]');
					if (!existing3) { return; }
					var clone3 = cloneBlankRow(existing3);
					rowsWrap3.appendChild(clone3);
					syncCompareFlagsRepeater(container3);
					return;
				}
				var rmCmp = ev.target.closest && ev.target.closest('[data-um-meta-compare-flags-remove]');
				if (rmCmp) {
					ev.preventDefault();
					var row4 = rmCmp.closest('[data-um-meta-compare-flags-row]');
					var container4 = rmCmp.closest('[data-um-meta-compare-flags-repeater]');
					if (row4 && container4) {
						var allRows4 = container4.querySelectorAll('[data-um-meta-compare-flags-row]');
						if (allRows4.length <= 1) {
							var inputs4 = row4.querySelectorAll('input');
							for (var j = 0; j < inputs4.length; j++) {
								if (inputs4[j].type === 'checkbox') { inputs4[j].checked = false; } else { inputs4[j].value = ''; }
							}
						} else {
							row4.parentNode.removeChild(row4);
						}
						syncCompareFlagsRepeater(container4);
					}
					return;
				}
			});

			document.addEventListener('input', function(ev) {
				var container = ev.target.closest && ev.target.closest('[data-um-meta-fields-repeater]');
				if (container) {
					syncMetaFieldsRepeater(container);
					return;
				}
				var container2 = ev.target.closest && ev.target.closest('[data-um-meta-compare-flags-repeater]');
				if (container2) {
					syncCompareFlagsRepeater(container2);
				}
			});
			document.addEventListener('change', function(ev) {
				var container = ev.target.closest && ev.target.closest('[data-um-meta-fields-repeater]');
				if (container) {
					syncMetaFieldsRepeater(container);
					return;
				}
				var container2 = ev.target.closest && ev.target.closest('[data-um-meta-compare-flags-repeater]');
				if (container2) {
					syncCompareFlagsRepeater(container2);
				}
			});

			var initForms = function() {
				var containers = document.querySelectorAll('[data-um-meta-fields-repeater]');
				for (var i = 0; i < containers.length; i++) {
					syncMetaFieldsRepeater(containers[i]);
				}
				var ccontainers = document.querySelectorAll('[data-um-meta-compare-flags-repeater]');
				for (var j = 0; j < ccontainers.length; j++) {
					syncCompareFlagsRepeater(ccontainers[j]);
				}
			};
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', initForms);
			} else {
				initForms();
			}
		})();
		</script>
		<?php
	}
}
