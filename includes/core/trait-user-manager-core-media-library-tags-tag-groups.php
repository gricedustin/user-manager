<?php
/**
 * Media Library Tags Tag Groups helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Media_Library_Tags_Tag_Groups_Trait {

	/**
	 * Option key for saved tag groups.
	 */
	private static function media_library_tag_groups_option_key(): string {
		return 'um_media_library_tag_groups';
	}

	/**
	 * Build front-end tag URL in key-only format: /?[tag-slug]
	 */
	private static function build_media_library_tag_group_frontend_url(string $tag_slug): string {
		$tag_slug = sanitize_title($tag_slug);
		if ($tag_slug === '') {
			return self::get_media_library_tag_group_current_page_base_url();
		}
		return rtrim(self::get_media_library_tag_group_current_page_base_url(), '?&') . '?' . rawurlencode($tag_slug);
	}

	/**
	 * Resolve current front-end page URL without query args for Tag Group links.
	 */
	private static function get_media_library_tag_group_current_page_base_url(): string {
		$base_url = '';
		if (!is_admin()) {
			$queried_id = get_queried_object_id();
			if ($queried_id > 0) {
				$permalink = get_permalink($queried_id);
				if (is_string($permalink) && $permalink !== '') {
					$base_url = $permalink;
				}
			}
			if ($base_url === '' && !empty($_SERVER['REQUEST_URI'])) {
				$request_uri = (string) wp_unslash($_SERVER['REQUEST_URI']);
				$path = strtok($request_uri, '?');
				$path = is_string($path) ? $path : '';
				$base_url = home_url($path !== '' ? $path : '/');
			}
		}
		if ($base_url === '') {
			$base_url = home_url('/');
		}
		return remove_query_arg([], $base_url);
	}

	/**
	 * Register "Tag Groups" submenu under Media.
	 */
	public static function register_media_library_tag_groups_submenu(): void {
		add_submenu_page(
			'upload.php',
			__('Tag Groups', 'user-manager'),
			__('Tag Groups', 'user-manager'),
			'upload_files',
			'um-media-library-tag-groups',
			[__CLASS__, 'render_media_library_tag_groups_page']
		);
	}

	/**
	 * Render Tag Groups admin page.
	 */
	public static function render_media_library_tag_groups_page(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage Tag Groups.', 'user-manager'));
		}

		$slug_name_map = self::get_media_library_tag_slug_name_map();
		$groups = self::get_media_library_tag_groups();
		$groups_by_id = [];
		foreach ($groups as $group) {
			if (!is_array($group) || empty($group['id'])) {
				continue;
			}
			$groups_by_id[(string) $group['id']] = $group;
		}

		$notice = isset($_GET['um_tag_groups_notice']) ? sanitize_key((string) wp_unslash($_GET['um_tag_groups_notice'])) : '';
		$editing_id = isset($_GET['group_id']) ? sanitize_key((string) wp_unslash($_GET['group_id'])) : '';
		$editing_group = ($editing_id !== '' && isset($groups_by_id[$editing_id]) && is_array($groups_by_id[$editing_id]))
			? $groups_by_id[$editing_id]
			: [];

		$form_id = isset($editing_group['id']) ? (string) $editing_group['id'] : '';
		$form_parent_slug = isset($editing_group['parentSlug']) ? sanitize_title((string) $editing_group['parentSlug']) : '';
		$form_member_slugs = isset($editing_group['memberSlugs']) && is_array($editing_group['memberSlugs'])
			? array_values(array_filter(array_map('sanitize_title', array_map('strval', $editing_group['memberSlugs']))))
			: [];
		$form_member_slugs_csv = implode(',', $form_member_slugs);

		?>
		<div class="wrap">
			<h1><?php esc_html_e('Tag Groups', 'user-manager'); ?></h1>
			<p><?php esc_html_e('Create groups by selecting a Parent Tag and assigning related tags to that group. On the front-end gallery description area, related links are displayed when the current tag belongs to a group.', 'user-manager'); ?></p>
			<?php if ($notice === 'saved') : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Tag group saved successfully.', 'user-manager'); ?></p></div>
			<?php elseif ($notice === 'deleted') : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Tag group deleted successfully.', 'user-manager'); ?></p></div>
			<?php elseif ($notice === 'invalid_parent') : ?>
				<div class="notice notice-error is-dismissible"><p><?php esc_html_e('Please choose a valid Parent Tag.', 'user-manager'); ?></p></div>
			<?php endif; ?>

			<div class="um-tag-groups-form-wrap">
				<h2 style="margin-top:0;"><?php echo $form_id !== '' ? esc_html__('Edit Tag Group', 'user-manager') : esc_html__('Add Tag Group', 'user-manager'); ?></h2>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<input type="hidden" name="action" value="user_manager_media_library_tag_groups_save" />
					<input type="hidden" name="um_tag_group_id" value="<?php echo esc_attr($form_id); ?>" />
					<?php wp_nonce_field('user_manager_media_library_tag_groups_save', 'user_manager_media_library_tag_groups_nonce'); ?>
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><label for="um-tag-group-parent-slug"><?php esc_html_e('Parent Tag', 'user-manager'); ?></label></th>
								<td>
									<select id="um-tag-group-parent-slug" name="um_tag_group_parent_slug" required>
										<option value=""><?php esc_html_e('Select a Parent Tag', 'user-manager'); ?></option>
										<?php foreach ($slug_name_map as $slug => $name) : ?>
											<option value="<?php echo esc_attr((string) $slug); ?>" <?php selected($form_parent_slug, (string) $slug); ?>><?php echo esc_html((string) $name); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description" style="margin-top:8px;"><?php esc_html_e('This is the parent tag shown as the main group link.', 'user-manager'); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e('Group Tags', 'user-manager'); ?></th>
								<td>
									<input type="hidden" id="um-tag-group-member-slugs" name="um_tag_group_member_slugs" value="<?php echo esc_attr($form_member_slugs_csv); ?>" />
									<div id="um-tag-group-picker" class="um-tag-group-picker" data-selected="<?php echo esc_attr($form_member_slugs_csv); ?>">
										<?php if (!empty($slug_name_map)) : ?>
											<?php foreach ($slug_name_map as $slug => $name) : ?>
												<button type="button" class="button um-tag-group-pill" data-tag-slug="<?php echo esc_attr((string) $slug); ?>"><?php echo esc_html((string) $name); ?></button>
											<?php endforeach; ?>
										<?php else : ?>
											<p class="description" style="margin:0;"><?php esc_html_e('No Library Tags found yet. Create Library Tags first under Media > Library Tags.', 'user-manager'); ?></p>
										<?php endif; ?>
									</div>
									<p class="description" style="margin-top:8px;"><?php esc_html_e('Select the related tags that belong to this group. The parent tag is automatically excluded from this list.', 'user-manager'); ?></p>
									<div id="um-tag-group-order-wrap" class="um-tag-group-order-wrap">
										<strong><?php esc_html_e('Display Order', 'user-manager'); ?></strong>
										<p class="description" style="margin:6px 0 8px;"><?php esc_html_e('Drag selected tags to control the order shown on the front end.', 'user-manager'); ?></p>
										<ul id="um-tag-group-order-list" class="um-tag-group-order-list"></ul>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
					<?php submit_button($form_id !== '' ? __('Update Tag Group', 'user-manager') : __('Save Tag Group', 'user-manager')); ?>
				</form>
			</div>

			<h2 style="margin-top:28px;"><?php esc_html_e('Saved Tag Groups', 'user-manager'); ?></h2>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th style="width:24%;"><?php esc_html_e('Parent Tag', 'user-manager'); ?></th>
						<th style="width:56%;"><?php esc_html_e('Group Tags', 'user-manager'); ?></th>
						<th style="width:20%;"><?php esc_html_e('Actions', 'user-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($groups)) : ?>
						<tr>
							<td colspan="3"><?php esc_html_e('No tag groups saved yet.', 'user-manager'); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ($groups as $group) : ?>
							<?php if (!is_array($group) || empty($group['id'])) { continue; } ?>
							<?php
							$group_id = (string) $group['id'];
							$parent_slug = isset($group['parentSlug']) ? sanitize_title((string) $group['parentSlug']) : '';
							$member_slugs = isset($group['memberSlugs']) && is_array($group['memberSlugs'])
								? array_values(array_filter(array_map('sanitize_title', array_map('strval', $group['memberSlugs']))))
								: [];
							$parent_name = isset($slug_name_map[$parent_slug]) ? (string) $slug_name_map[$parent_slug] : $parent_slug;
							$member_names = [];
							foreach ($member_slugs as $member_slug) {
								$member_names[] = isset($slug_name_map[$member_slug]) ? (string) $slug_name_map[$member_slug] : $member_slug;
							}
							$edit_url = add_query_arg(
								[
									'page' => 'um-media-library-tag-groups',
									'group_id' => $group_id,
								],
								admin_url('upload.php')
							);
							$delete_url = wp_nonce_url(
								add_query_arg(
									[
										'action' => 'user_manager_media_library_tag_groups_delete',
										'group_id' => $group_id,
									],
									admin_url('admin-post.php')
								),
								'user_manager_media_library_tag_groups_delete_' . $group_id
							);
							?>
							<tr>
								<td><strong><?php echo esc_html($parent_name); ?></strong></td>
								<td><?php echo !empty($member_names) ? esc_html(implode(', ', $member_names)) : '—'; ?></td>
								<td>
									<a class="button button-small" href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit', 'user-manager'); ?></a>
									<a class="button button-small" style="margin-top:6px;" href="<?php echo esc_url($delete_url); ?>" onclick="return window.confirm(<?php echo wp_json_encode(__('Delete this tag group?', 'user-manager')); ?>);"><?php esc_html_e('Delete', 'user-manager'); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<style>
		.um-tag-groups-form-wrap {
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 6px;
			padding: 18px;
			max-width: 980px;
		}
		.um-tag-group-picker {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			max-width: 980px;
		}
		.um-tag-group-pill.is-selected {
			background: #135e96;
			border-color: #135e96;
			color: #fff;
		}
		.um-tag-group-pill.is-parent-tag {
			background: #f0f0f1;
			border-color: #dcdcde;
			color: #50575e;
		}
		.um-tag-group-order-wrap {
			margin-top: 14px;
			max-width: 620px;
		}
		.um-tag-group-order-list {
			margin: 0;
			padding: 0;
			list-style: none;
			display: flex;
			flex-direction: column;
			gap: 6px;
		}
		.um-tag-group-order-item {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 6px 10px;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			background: #fff;
			cursor: move;
			user-select: none;
		}
		.um-tag-group-order-item .dashicons {
			color: #8c8f94;
			font-size: 16px;
			width: 16px;
			height: 16px;
		}
		.um-tag-group-order-item.is-dragging {
			opacity: 0.55;
		}
		</style>
		<script>
		(function(){
			var picker = document.getElementById('um-tag-group-picker');
			var hiddenInput = document.getElementById('um-tag-group-member-slugs');
			var parentSelect = document.getElementById('um-tag-group-parent-slug');
			var orderList = document.getElementById('um-tag-group-order-list');
			var orderWrap = document.getElementById('um-tag-group-order-wrap');
			if (!picker || !hiddenInput || !parentSelect || !orderList || !orderWrap) {
				return;
			}
			var selected = {};
			var selectedOrder = [];
			function normalizeSlug(value) {
				return String(value || '').toLowerCase().replace(/[^a-z0-9\-_]/g, '');
			}
			function readInitial() {
				var raw = String(picker.getAttribute('data-selected') || hiddenInput.value || '');
				raw.split(',').forEach(function(slug){
					var normalized = normalizeSlug(slug.trim());
					if (normalized) {
						selected[normalized] = true;
						selectedOrder.push(normalized);
					}
				});
				selectedOrder = selectedOrder.filter(function(slug, idx, list){
					return list.indexOf(slug) === idx;
				});
			}
			function syncHiddenField() {
				hiddenInput.value = selectedOrder.join(',');
			}
			function ensureSelectionOrderSync() {
				selectedOrder = selectedOrder.filter(function(slug) {
					return !!selected[slug];
				});
				Object.keys(selected).forEach(function(slug) {
					if (selectedOrder.indexOf(slug) === -1) {
						selectedOrder.push(slug);
					}
				});
			}
			function renderOrderList() {
				ensureSelectionOrderSync();
				orderList.innerHTML = '';
				if (!selectedOrder.length) {
					orderWrap.style.display = 'none';
					return;
				}
				orderWrap.style.display = 'block';
				var pills = picker.querySelectorAll('.um-tag-group-pill');
				var namesBySlug = {};
				for (var i = 0; i < pills.length; i++) {
					var pill = pills[i];
					var slug = normalizeSlug(pill.getAttribute('data-tag-slug'));
					if (!slug) {
						continue;
					}
					namesBySlug[slug] = String(pill.textContent || slug).trim();
				}
				selectedOrder.forEach(function(slug) {
					var li = document.createElement('li');
					li.className = 'um-tag-group-order-item';
					li.setAttribute('draggable', 'true');
					li.setAttribute('data-tag-slug', slug);
					li.innerHTML = '<span class="dashicons dashicons-menu"></span><span>' + String(namesBySlug[slug] || slug) + '</span>';
					orderList.appendChild(li);
				});
			}
			function syncPills() {
				var parentSlug = normalizeSlug(parentSelect.value);
				var pills = picker.querySelectorAll('.um-tag-group-pill');
				for (var i = 0; i < pills.length; i++) {
					var pill = pills[i];
					var slug = normalizeSlug(pill.getAttribute('data-tag-slug'));
					var isParentTag = !!parentSlug && slug === parentSlug;
					if (isParentTag && selected[slug]) {
						delete selected[slug];
					}
					if (isParentTag) {
						pill.classList.add('is-parent-tag');
						pill.classList.remove('is-selected');
						pill.setAttribute('aria-pressed', 'false');
						continue;
					}
					pill.classList.remove('is-parent-tag');
					if (slug && selected[slug]) {
						pill.classList.add('is-selected');
						pill.setAttribute('aria-pressed', 'true');
					} else {
						pill.classList.remove('is-selected');
						pill.setAttribute('aria-pressed', 'false');
					}
				}
				renderOrderList();
			}
			readInitial();
			syncPills();
			syncHiddenField();
			parentSelect.addEventListener('change', function() {
				syncPills();
				syncHiddenField();
			});
			picker.addEventListener('click', function(event){
				var target = event.target;
				if (!target || !target.classList || !target.classList.contains('um-tag-group-pill')) {
					return;
				}
				event.preventDefault();
				var slug = normalizeSlug(target.getAttribute('data-tag-slug'));
				if (!slug || slug === normalizeSlug(parentSelect.value)) {
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
			orderList.addEventListener('dragstart', function(event) {
				var item = event.target && event.target.closest ? event.target.closest('.um-tag-group-order-item') : null;
				if (!item) {
					return;
				}
				item.classList.add('is-dragging');
				event.dataTransfer.effectAllowed = 'move';
				event.dataTransfer.setData('text/plain', String(item.getAttribute('data-tag-slug') || ''));
			});
			orderList.addEventListener('dragend', function(event) {
				var item = event.target && event.target.closest ? event.target.closest('.um-tag-group-order-item') : null;
				if (item) {
					item.classList.remove('is-dragging');
				}
			});
			orderList.addEventListener('dragover', function(event) {
				event.preventDefault();
				var dragging = orderList.querySelector('.um-tag-group-order-item.is-dragging');
				var target = event.target && event.target.closest ? event.target.closest('.um-tag-group-order-item') : null;
				if (!dragging || !target || dragging === target) {
					return;
				}
				var rect = target.getBoundingClientRect();
				var placeBefore = event.clientY < (rect.top + rect.height / 2);
				if (placeBefore) {
					orderList.insertBefore(dragging, target);
				} else {
					orderList.insertBefore(dragging, target.nextSibling);
				}
			});
			orderList.addEventListener('drop', function(event) {
				event.preventDefault();
				var nodes = orderList.querySelectorAll('.um-tag-group-order-item');
				selectedOrder = [];
				for (var i = 0; i < nodes.length; i++) {
					var slug = normalizeSlug(nodes[i].getAttribute('data-tag-slug'));
					if (slug && selectedOrder.indexOf(slug) === -1) {
						selectedOrder.push(slug);
					}
				}
				syncHiddenField();
			});
		})();
		</script>
		<?php
	}

	/**
	 * Save Tag Group create/update.
	 */
	public static function handle_media_library_tag_groups_save(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage Tag Groups.', 'user-manager'));
		}
		check_admin_referer('user_manager_media_library_tag_groups_save', 'user_manager_media_library_tag_groups_nonce');

		$group_id = isset($_POST['um_tag_group_id']) ? sanitize_key((string) wp_unslash($_POST['um_tag_group_id'])) : '';
		$parent_slug = isset($_POST['um_tag_group_parent_slug']) ? sanitize_title((string) wp_unslash($_POST['um_tag_group_parent_slug'])) : '';
		$member_slugs_csv = isset($_POST['um_tag_group_member_slugs']) ? (string) wp_unslash($_POST['um_tag_group_member_slugs']) : '';

		if ($parent_slug === '' || !term_exists($parent_slug, self::media_library_tags_taxonomy())) {
			wp_safe_redirect(
				add_query_arg(
					[
						'page' => 'um-media-library-tag-groups',
						'um_tag_groups_notice' => 'invalid_parent',
					],
					admin_url('upload.php')
				)
			);
			exit;
		}

		$member_slugs = self::sanitize_media_library_tag_group_member_slugs_csv($member_slugs_csv, $parent_slug);
		$groups = self::get_media_library_tag_groups();
		$upsert_group = [
			'id' => $group_id !== '' ? $group_id : self::build_media_library_tag_group_id(),
			'parentSlug' => $parent_slug,
			'memberSlugs' => $member_slugs,
		];

		$updated = false;
		foreach ($groups as $index => $group) {
			if (!is_array($group) || empty($group['id'])) {
				continue;
			}
			if ((string) $group['id'] !== (string) $upsert_group['id']) {
				continue;
			}
			$groups[$index] = $upsert_group;
			$updated = true;
			break;
		}
		if (!$updated) {
			$groups[] = $upsert_group;
		}

		self::update_media_library_tag_groups($groups);
		wp_safe_redirect(
			add_query_arg(
				[
					'page' => 'um-media-library-tag-groups',
					'um_tag_groups_notice' => 'saved',
				],
				admin_url('upload.php')
			)
		);
		exit;
	}

	/**
	 * Delete one Tag Group.
	 */
	public static function handle_media_library_tag_groups_delete(): void {
		if (!current_user_can('upload_files')) {
			wp_die(esc_html__('You do not have permission to manage Tag Groups.', 'user-manager'));
		}
		$group_id = isset($_GET['group_id']) ? sanitize_key((string) wp_unslash($_GET['group_id'])) : '';
		if ($group_id === '') {
			wp_safe_redirect(add_query_arg(['page' => 'um-media-library-tag-groups'], admin_url('upload.php')));
			exit;
		}
		check_admin_referer('user_manager_media_library_tag_groups_delete_' . $group_id);

		$groups = self::get_media_library_tag_groups();
		$kept_groups = [];
		foreach ($groups as $group) {
			if (!is_array($group) || empty($group['id'])) {
				continue;
			}
			if ((string) $group['id'] === $group_id) {
				continue;
			}
			$kept_groups[] = $group;
		}
		self::update_media_library_tag_groups($kept_groups);

		wp_safe_redirect(
			add_query_arg(
				[
					'page' => 'um-media-library-tag-groups',
					'um_tag_groups_notice' => 'deleted',
				],
				admin_url('upload.php')
			)
		);
		exit;
	}

	/**
	 * Render related links for the current tag when it belongs to a group.
	 */
	public static function render_media_library_tag_group_links_html(string $current_tag_slug): string {
		$current_tag_slug = sanitize_title($current_tag_slug);
		if ($current_tag_slug === '') {
			return '';
		}

		$groups = self::get_media_library_tag_groups();
		if (empty($groups)) {
			return '';
		}

		$matched_group = null;
		foreach ($groups as $group) {
			if (!is_array($group)) {
				continue;
			}
			$parent_slug = isset($group['parentSlug']) ? sanitize_title((string) $group['parentSlug']) : '';
			$member_slugs = isset($group['memberSlugs']) && is_array($group['memberSlugs'])
				? array_values(array_filter(array_map('sanitize_title', array_map('strval', $group['memberSlugs']))))
				: [];
			$all_group_slugs = array_values(array_unique(array_filter(array_merge([$parent_slug], $member_slugs))));
			if (in_array($current_tag_slug, $all_group_slugs, true)) {
				$matched_group = [
					'parentSlug' => $parent_slug,
					'memberSlugs' => $member_slugs,
				];
				break;
			}
		}
		if (!is_array($matched_group) || empty($matched_group['parentSlug'])) {
			return '';
		}

		$parent_slug = sanitize_title((string) $matched_group['parentSlug']);
		$member_slugs = isset($matched_group['memberSlugs']) && is_array($matched_group['memberSlugs'])
			? array_values(array_filter(array_map('sanitize_title', array_map('strval', $matched_group['memberSlugs']))))
			: [];
		$slug_name_map = self::get_media_library_tag_slug_name_map();

		$related_slugs = [];
		foreach ($member_slugs as $member_slug) {
			if ($member_slug === '' || $member_slug === $parent_slug) {
				continue;
			}
			$related_slugs[] = $member_slug;
		}
		$related_slugs = array_values(array_unique($related_slugs));

		$related_links = [];
		foreach ($related_slugs as $related_slug) {
			$related_name = isset($slug_name_map[$related_slug]) ? (string) $slug_name_map[$related_slug] : $related_slug;
			if ($related_slug === $current_tag_slug) {
				$related_links[] = sprintf(
					'<span class="um-media-library-tag-group-links-current"><strong>%1$s</strong></span>',
					esc_html($related_name)
				);
			} else {
				$related_links[] = sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url(self::build_media_library_tag_group_frontend_url($related_slug)),
					esc_html($related_name)
				);
			}
		}

		$parent_link_html = '';
		if ($parent_slug !== '') {
			$parent_name = isset($slug_name_map[$parent_slug]) ? (string) $slug_name_map[$parent_slug] : $parent_slug;
			if ($parent_slug === $current_tag_slug) {
				$parent_link_html = sprintf(
					'<span class="um-media-library-tag-group-links-current"><strong>%1$s</strong></span>',
					esc_html($parent_name)
				);
			} else {
				$parent_link_html = sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url(self::build_media_library_tag_group_frontend_url($parent_slug)),
					esc_html($parent_name)
				);
			}
		}

		if (empty($related_links) && $parent_link_html === '') {
			return '';
		}

		$line_segments = [];
		if ($parent_link_html !== '') {
			$line_segments[] = $parent_link_html;
		}
		if (!empty($related_links)) {
			$line_segments[] = implode(' &middot; ', $related_links);
		}
		if (empty($line_segments)) {
			return '';
		}

		return '<div class="um-media-library-tag-group-links" style="margin:0 0 12px; white-space:normal;">' . implode(' &gt; ', $line_segments) . '</div>';
	}

	/**
	 * Render related links for a parsed tag-expression context.
	 *
	 * @param array{mode?:string,slugs?:array<int,string>,primarySlug?:string} $tag_override
	 */
	public static function render_media_library_tag_group_links_html_for_expression(array $tag_override): string {
		$group_rows = self::media_library_tag_group_records_for_current_expression($tag_override);
		if (empty($group_rows)) {
			return '';
		}

		$chunks = [];
		foreach ($group_rows as $row) {
			if (!is_array($row)) {
				continue;
			}
			$current_slug = isset($row['currentSlug']) ? sanitize_title((string) $row['currentSlug']) : '';
			if ($current_slug === '') {
				continue;
			}
			$row_html = self::render_media_library_tag_group_links_html($current_slug);
			if ($row_html === '') {
				continue;
			}
			$chunks[] = $row_html;
		}

		if (empty($chunks)) {
			return '';
		}
		return '<div class="um-media-library-tag-group-links-wrap">' . implode('', $chunks) . '</div>';
	}

	/**
	 * Resolve active/current slugs for group-link rendering.
	 *
	 * @param array{mode?:string,slugs?:array<int,string>,primarySlug?:string} $tag_override
	 * @return array<int,array{currentSlug:string}>
	 */
	private static function media_library_tag_group_records_for_current_expression(array $tag_override): array {
		$current_slugs = isset($tag_override['slugs']) && is_array($tag_override['slugs'])
			? array_values(array_filter(array_map('sanitize_title', array_map('strval', $tag_override['slugs']))))
			: [];
		$fallback_slug = isset($tag_override['primarySlug']) ? sanitize_title((string) $tag_override['primarySlug']) : '';
		if (empty($current_slugs) && $fallback_slug !== '') {
			$current_slugs[] = $fallback_slug;
		}
		$current_slugs = array_values(array_unique($current_slugs));
		if (empty($current_slugs)) {
			return [];
		}

		$rows = [];
		foreach ($current_slugs as $slug) {
			if ($slug === '') {
				continue;
			}
			$rows[] = ['currentSlug' => $slug];
		}
		return $rows;
	}

	/**
	 * Return tag slug=>name map.
	 *
	 * @return array<string,string>
	 */
	private static function get_media_library_tag_slug_name_map(): array {
		$taxonomy = self::media_library_tags_taxonomy();
		if (!taxonomy_exists($taxonomy)) {
			self::register_media_library_tags_taxonomy();
		}
		$terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]);
		if (is_wp_error($terms) || !is_array($terms)) {
			return [];
		}

		$map = [];
		foreach ($terms as $term) {
			if (!($term instanceof WP_Term)) {
				continue;
			}
			$slug = sanitize_title((string) $term->slug);
			if ($slug === '') {
				continue;
			}
			$map[$slug] = (string) $term->name;
		}
		return $map;
	}

	/**
	 * Read and sanitize Tag Group rows.
	 *
	 * @return array<int,array{id:string,parentSlug:string,memberSlugs:array<int,string>}>
	 */
	private static function get_media_library_tag_groups(): array {
		$raw_groups = get_option(self::media_library_tag_groups_option_key(), []);
		if (!is_array($raw_groups)) {
			return [];
		}

		$groups = [];
		foreach ($raw_groups as $raw_group) {
			if (!is_array($raw_group)) {
				continue;
			}
			$group = self::sanitize_media_library_tag_group_record($raw_group);
			if ($group === null) {
				continue;
			}
			$groups[] = $group;
		}

		usort($groups, static function (array $a, array $b): int {
			return strnatcasecmp((string) ($a['parentSlug'] ?? ''), (string) ($b['parentSlug'] ?? ''));
		});
		return $groups;
	}

	/**
	 * Persist Tag Group rows.
	 *
	 * @param array<int,mixed> $groups
	 */
	private static function update_media_library_tag_groups(array $groups): void {
		$sanitized = [];
		foreach ($groups as $group) {
			if (!is_array($group)) {
				continue;
			}
			$sanitized_group = self::sanitize_media_library_tag_group_record($group);
			if ($sanitized_group === null) {
				continue;
			}
			$sanitized[] = $sanitized_group;
		}
		update_option(self::media_library_tag_groups_option_key(), $sanitized);
	}

	/**
	 * @param array<string,mixed> $group
	 * @return array{id:string,parentSlug:string,memberSlugs:array<int,string>}|null
	 */
	private static function sanitize_media_library_tag_group_record(array $group): ?array {
		$group_id = isset($group['id']) ? sanitize_key((string) $group['id']) : '';
		if ($group_id === '') {
			$group_id = self::build_media_library_tag_group_id();
		}

		$parent_slug = isset($group['parentSlug']) ? sanitize_title((string) $group['parentSlug']) : '';
		if ($parent_slug === '' || !term_exists($parent_slug, self::media_library_tags_taxonomy())) {
			return null;
		}

		$raw_member_slugs = [];
		if (isset($group['memberSlugs']) && is_array($group['memberSlugs'])) {
			$raw_member_slugs = array_map('strval', $group['memberSlugs']);
		}
		$member_slugs = self::sanitize_media_library_tag_group_member_slugs_csv(implode(',', $raw_member_slugs), $parent_slug);

		return [
			'id' => $group_id,
			'parentSlug' => $parent_slug,
			'memberSlugs' => $member_slugs,
		];
	}

	/**
	 * @return array<int,string>
	 */
	private static function sanitize_media_library_tag_group_member_slugs_csv(string $raw_csv, string $parent_slug = ''): array {
		$parent_slug = sanitize_title($parent_slug);
		$requested = array_filter(array_map('sanitize_title', array_map('trim', explode(',', $raw_csv))));
		if (empty($requested)) {
			return [];
		}
		$taxonomy = self::media_library_tags_taxonomy();
		$clean = [];
		foreach ($requested as $slug) {
			if ($slug === '' || $slug === $parent_slug) {
				continue;
			}
			if (!term_exists($slug, $taxonomy)) {
				continue;
			}
			$clean[] = $slug;
		}
		return array_values(array_unique($clean));
	}

	/**
	 * Build stable random Tag Group ID.
	 */
	private static function build_media_library_tag_group_id(): string {
		return 'tg_' . substr(md5(uniqid('tg', true)), 0, 12);
	}
}

