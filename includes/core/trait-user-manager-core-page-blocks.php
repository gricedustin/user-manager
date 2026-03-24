<?php
/**
 * Page Blocks add-ons helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Page_Blocks_Trait {

	/**
	 * Register runtime hooks for Page Blocks add-ons.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_page_blocks(array $settings): void {
		if (!empty($settings['page_block_subpages_grid_enabled'])) {
			add_action('init', [__CLASS__, 'register_noop_old_shortcodes'], 99);
			add_action('init', [__CLASS__, 'register_page_block_subpages_grid_shortcode'], 20);
			add_action('init', [__CLASS__, 'register_page_block_subpages_grid'], 20);
			add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_page_block_subpages_grid_editor_assets']);
		}

		if (!empty($settings['page_block_tabbed_content_area_enabled'])) {
			add_action('init', [__CLASS__, 'register_page_block_tabbed_content_area'], 20);
			add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_page_block_tabbed_content_editor_assets']);
		}

		if (!empty($settings['page_block_simple_icons_enabled'])) {
			add_action('init', [__CLASS__, 'register_page_block_simple_icons'], 20);
			add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_page_block_simple_icons_editor_assets']);
			add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_page_block_simple_icons_frontend_assets'], 20);
		}

		if (!empty($settings['page_block_menu_tiles_enabled'])) {
			add_action('init', [__CLASS__, 'register_page_block_menu_tiles'], 20);
			add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_page_block_menu_tiles_editor_assets']);
		}
	}

	/**
	 * Register empty handlers for legacy/broken shortcodes configured in settings.
	 */
	public static function register_noop_old_shortcodes(): void {
		$settings = self::get_settings();
		$list = isset($settings['page_block_old_shortcodes_list'])
			? (string) $settings['page_block_old_shortcodes_list']
			: '';
		if ($list === '') {
			return;
		}

		$tags = array_map('trim', explode(',', $list));
		$tags = array_values(array_unique(array_filter($tags)));
		foreach ($tags as $tag) {
			$valid = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $tag);
			if (!is_string($valid) || $valid === '') {
				continue;
			}

			add_shortcode($valid, [__CLASS__, 'render_noop_shortcode']);
			$valid_lower = strtolower($valid);
			if ($valid_lower !== $valid) {
				add_shortcode($valid_lower, [__CLASS__, 'render_noop_shortcode']);
			}
		}
	}

	/**
	 * Empty shortcode callback for legacy shortcode compatibility.
	 */
	public static function render_noop_shortcode($atts = [], $content = '', $tag = ''): string {
		unset($atts, $content, $tag);
		return '';
	}

	/**
	 * Register [mybrand_subpages_grid] shortcode.
	 */
	public static function register_page_block_subpages_grid_shortcode(): void {
		add_shortcode('mybrand_subpages_grid', [__CLASS__, 'render_mybrand_subpages_grid_shortcode']);
	}

	/**
	 * Render [mybrand_subpages_grid] shortcode output.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 */
	public static function render_mybrand_subpages_grid_shortcode($atts = []): string {
		$atts = shortcode_atts(
			[
				'parent' => 0,
				'columns' => 3,
				'excerpt_chars' => 0,
			],
			(array) $atts,
			'mybrand_subpages_grid'
		);

		$parent_id = isset($atts['parent']) ? absint($atts['parent']) : 0;
		$columns = isset($atts['columns']) ? max(1, min(6, (int) $atts['columns'])) : 3;
		$excerpt_length = isset($atts['excerpt_chars']) ? max(0, (int) $atts['excerpt_chars']) : 0;

		return self::build_page_block_subpages_grid_markup($parent_id, $columns, $excerpt_length, '');
	}

	/**
	 * Register dynamic block: custom/mybrand-subpages-grid.
	 */
	public static function register_page_block_subpages_grid(): void {
		if (!function_exists('register_block_type')) {
			return;
		}

		register_block_type('custom/mybrand-subpages-grid', [
			'render_callback' => [__CLASS__, 'render_mybrand_subpages_grid_block'],
			'attributes' => [
				'parent' => ['type' => 'number', 'default' => 0],
				'columns' => ['type' => 'number', 'default' => 3],
				'excerptChars' => ['type' => 'number', 'default' => 0],
				'className' => ['type' => 'string'],
			],
			'editor_script' => 'um-mybrand-subpages-grid-editor',
		]);
	}

	/**
	 * Render callback for custom/mybrand-subpages-grid.
	 *
	 * @param array<string,mixed> $attrs Block attributes.
	 */
	public static function render_mybrand_subpages_grid_block(array $attrs = []): string {
		$parent_id = isset($attrs['parent']) ? absint($attrs['parent']) : 0;
		$columns = isset($attrs['columns']) ? max(1, min(6, (int) $attrs['columns'])) : 3;
		$excerpt_length = isset($attrs['excerptChars']) ? max(0, (int) $attrs['excerptChars']) : 0;
		$class_name = isset($attrs['className']) ? (string) $attrs['className'] : '';
		$class_name = trim((string) preg_replace('/[^A-Za-z0-9_\-\s]/', '', $class_name));

		return self::build_page_block_subpages_grid_markup($parent_id, $columns, $excerpt_length, $class_name);
	}

	/**
	 * Shared renderer for subpages grid shortcode/block.
	 */
	private static function build_page_block_subpages_grid_markup(int $parent_id, int $columns, int $excerpt_length, string $extra_classes = ''): string {
		if ($parent_id <= 0) {
			return '<p style="text-align:center;color:red;">' . esc_html__('Please specify a parent page ID.', 'user-manager') . '</p>';
		}

		$subpages = get_pages([
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
			'parent'      => $parent_id,
		]);
		if (empty($subpages)) {
			return '<p style="text-align:center;">' . esc_html__('No subpages found.', 'user-manager') . '</p>';
		}

		$columns = max(1, min(6, $columns));
		$excerpt_length = max(0, $excerpt_length);
		$class_suffix = $extra_classes !== '' ? ' ' . esc_attr($extra_classes) : '';

		ob_start();
		?>
		<div class="subpages-grid columns-<?php echo esc_attr((string) $columns); ?><?php echo $class_suffix; ?>">
			<?php foreach ($subpages as $page) : ?>
				<?php
				$title = get_the_title($page->ID);
				$url = get_permalink($page->ID);
				$thumb = get_the_post_thumbnail($page->ID, 'medium', ['class' => 'subpage-thumb']);
				$excerpt = '';
				if ($excerpt_length > 0) {
					$content = $page->post_excerpt ? (string) $page->post_excerpt : wp_strip_all_tags((string) $page->post_content);
					$excerpt = function_exists('mb_substr') ? mb_substr($content, 0, $excerpt_length) : substr($content, 0, $excerpt_length);
					$len_all = function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
					if ($len_all > $excerpt_length) {
						$excerpt .= '...';
					}
				}
				?>
				<div class="subpage-item">
					<a href="<?php echo esc_url($url); ?>" class="subpage-link">
						<?php if ($thumb !== '') : ?>
							<div class="subpage-thumb-wrapper"><?php echo wp_kses_post($thumb); ?></div>
						<?php endif; ?>
						<h3 class="subpage-title"><?php echo esc_html($title); ?></h3>
					</a>
					<?php if ($excerpt_length > 0 && $excerpt !== '') : ?>
						<p class="subpage-excerpt"><?php echo esc_html($excerpt); ?></p>
						<div class="subpage-button">
							<a href="<?php echo esc_url($url); ?>" class="subpage-learnmore"><?php echo esc_html__('Learn More', 'user-manager'); ?></a>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<style>
		.subpages-grid { display: grid; gap: 30px; }
		<?php for ($i = 1; $i <= 6; $i++) : ?>
		.subpages-grid.columns-<?php echo (int) $i; ?> { grid-template-columns: repeat(<?php echo (int) $i; ?>, 1fr); }
		<?php endfor; ?>
		.subpage-item { text-align: center; }
		.subpage-thumb-wrapper { position: relative; width: 100%; padding-top: 100%; overflow: hidden; border-radius: 8px; }
		.subpage-thumb-wrapper img.subpage-thumb { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 8px; transition: transform .3s ease; }
		.subpage-thumb-wrapper:hover img.subpage-thumb { transform: scale(1.05); }
		.subpage-title { margin-top: 20px; font-size: 1.1em; text-align: center; }
		.subpage-excerpt { margin: 10px auto 0; max-width: 80%; font-size: .95em; line-height: 1.5em; }
		.subpage-button { margin-top: 20px; }
		.subpage-learnmore { display: inline-block; padding: 8px 18px; text-decoration: none; transition: background .3s ease, color .3s ease; }
		@media (max-width: 768px) { .subpages-grid[class*="columns-"] { grid-template-columns: repeat(2, 1fr); } }
		@media (max-width: 600px) { .subpages-grid[class*="columns-"], .subpages-grid[class^="columns-"] { grid-template-columns: 1fr; } }
		</style>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Enqueue editor UI for custom/mybrand-subpages-grid.
	 */
	public static function enqueue_page_block_subpages_grid_editor_assets(): void {
		wp_register_script(
			'um-mybrand-subpages-grid-editor',
			false,
			['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
			self::VERSION,
			true
		);

		$script = <<<'JS'
(function(blocks, element, blockEditor) {
	var registerBlockType = blocks.registerBlockType;
	var TextControl = window.wp.components.TextControl;
	var PanelBody = window.wp.components.PanelBody;
	var InspectorControls = blockEditor.InspectorControls;
	registerBlockType('custom/mybrand-subpages-grid', {
		title: 'My Brand Subpages Grid',
		icon: 'screenoptions',
		category: 'widgets',
		attributes: {
			parent: { type: 'number', default: 0 },
			columns: { type: 'number', default: 3 },
			excerptChars: { type: 'number', default: 0 }
		},
		edit: function(props) {
			var a = props.attributes;
			var set = props.setAttributes;
			return element.createElement(
				'div',
				{},
				element.createElement(
					InspectorControls,
					{},
					element.createElement(
						PanelBody,
						{ title: 'Settings', initialOpen: true },
						element.createElement(TextControl, { label: 'Parent Page ID', type: 'number', value: a.parent, onChange: function(v){ set({ parent: parseInt(v, 10) || 0 }); } }),
						element.createElement(TextControl, { label: 'Columns (1-6)', type: 'number', value: a.columns, onChange: function(v){ set({ columns: parseInt(v, 10) || 3 }); } }),
						element.createElement(TextControl, { label: 'Excerpt (characters)', type: 'number', value: a.excerptChars, onChange: function(v){ set({ excerptChars: parseInt(v, 10) || 0 }); } })
					)
				),
				element.createElement('p', {}, 'Preview available on front-end.')
			);
		},
		save: function() { return null; }
	});
})(window.wp.blocks, window.wp.element, window.wp.blockEditor);
JS;
		wp_add_inline_script('um-mybrand-subpages-grid-editor', $script);
		wp_enqueue_script('um-mybrand-subpages-grid-editor');
	}

	/**
	 * Register dynamic blocks:
	 * - custom/mybrand-tabbed-content-area
	 * - custom/tabbed-content-area (legacy alias)
	 */
	public static function register_page_block_tabbed_content_area(): void {
		if (!function_exists('register_block_type')) {
			return;
		}

		register_block_type('custom/mybrand-tabbed-content-area', [
			'render_callback' => [__CLASS__, 'render_mybrand_tabbed_content_area_block'],
			'attributes' => [
				'tabs' => [
					'type' => 'array',
					'default' => [],
					'items' => [
						'type' => 'object',
						'properties' => [
							'label' => ['type' => 'string'],
							'postId' => ['type' => 'integer'],
						],
					],
				],
				'className' => ['type' => 'string'],
			],
			'editor_script' => 'um-mybrand-tabbed-content-editor',
		]);

		register_block_type('custom/tabbed-content-area', [
			'render_callback' => [__CLASS__, 'render_mybrand_tabbed_content_area_block'],
			'attributes' => [
				'tabs' => [
					'type' => 'array',
					'default' => [],
					'items' => [
						'type' => 'object',
						'properties' => [
							'label' => ['type' => 'string'],
							'postId' => ['type' => 'integer'],
						],
					],
				],
				'className' => ['type' => 'string'],
			],
		]);
	}

	/**
	 * Render callback for tabbed content blocks.
	 *
	 * @param array<string,mixed> $attrs Block attributes.
	 */
	public static function render_mybrand_tabbed_content_area_block(array $attrs = []): string {
		$tabs = isset($attrs['tabs']) && is_array($attrs['tabs']) ? $attrs['tabs'] : [];
		if (empty($tabs)) {
			return '';
		}

		$uid = function_exists('wp_unique_id') ? wp_unique_id('um-tabbed-block-') : uniqid('um-tabbed-block-');
		$class_name = isset($attrs['className']) ? (string) $attrs['className'] : '';
		$class_name = trim((string) preg_replace('/[^A-Za-z0-9_\-\s]/', '', $class_name));
		$extra_classes = $class_name !== '' ? ' ' . esc_attr($class_name) : '';
		$tab_count = max(1, count($tabs));

		ob_start();
		?>
		<div class="tabbed-content-area<?php echo $extra_classes; ?>" id="<?php echo esc_attr($uid); ?>">
			<ul class="tabs" role="tablist">
				<?php foreach ($tabs as $index => $tab) : ?>
					<?php $label = isset($tab['label']) ? (string) $tab['label'] : ''; ?>
					<li class="tab <?php echo $index === 0 ? 'active' : ''; ?>" data-tab="<?php echo esc_attr($uid . '-tab-' . (int) $index); ?>" style="width: <?php echo esc_attr((string) (95 / $tab_count)); ?>%;">
						<?php echo esc_html($label); ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="tab-panels">
				<?php
				$panel_count = 0;
				$active_panel_marked = false;
				foreach ($tabs as $index => $tab) :
					$post_id = isset($tab['postId']) ? absint($tab['postId']) : 0;
					$post_obj = $post_id > 0 ? get_post($post_id) : null;
					if (!$post_obj instanceof WP_Post) {
						continue;
					}
					$panel_count++;
					$panel_active_class = !$active_panel_marked ? 'active' : '';
					$active_panel_marked = true;
					?>
					<div class="tab-panel <?php echo esc_attr($panel_active_class); ?>" id="<?php echo esc_attr($uid . '-tab-' . (int) $index); ?>">
						<?php echo apply_filters('the_content', $post_obj->post_content); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endforeach; ?>
				<?php if ($panel_count === 0) : ?>
					<p><?php esc_html_e('No tab content found.', 'user-manager'); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<script>
		(function() {
			var wrapper = document.getElementById(<?php echo wp_json_encode($uid); ?>);
			if (!wrapper) { return; }
			var tabs = wrapper.querySelectorAll('.tab');
			var panels = wrapper.querySelectorAll('.tab-panel');
			tabs.forEach(function(tab) {
				tab.addEventListener('click', function() {
					tabs.forEach(function(tt) { tt.classList.remove('active'); });
					panels.forEach(function(pp) { pp.classList.remove('active'); });
					tab.classList.add('active');
					var id = tab.getAttribute('data-tab');
					var panel = id ? wrapper.querySelector('#' + id) : null;
					if (panel) {
						panel.classList.add('active');
					}
				});
			});
		})();
		</script>
		<style>
		.tabbed-content-area { width: 100%; }
		.tabs { display: flex; flex-wrap: wrap; list-style: none; margin: 0 0 10px 0; padding: 0; border-bottom: 0; }
		.tab { padding: 10px 20px; cursor: pointer; margin-right: 5px; text-align: center; font-size: .8rem; border-left: 2px solid #000; border-right: 2px solid #000; border-top: 2px solid #000; background-color: #000; }
		.tab.active { background-color: #222; text-decoration: underline; border-left: 2px solid #000; border-right: 2px solid #000; border-top: 2px solid #000; font-weight: bold; }
		.tab-panel { display: none; background-color: #222; padding: 5px; border: 0; }
		.tab-panel.active { display: block; }
		.tab-panel img { margin-bottom: 10px; }
		@media (max-width: 768px) {
			.tabbed-content-area .tabs .tab { width: 48%; margin-top: 5px; border-bottom: 2px solid #000; }
		}
		</style>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Enqueue editor UI for custom/mybrand-tabbed-content-area.
	 */
	public static function enqueue_page_block_tabbed_content_editor_assets(): void {
		wp_register_script(
			'um-mybrand-tabbed-content-editor',
			false,
			['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
			self::VERSION,
			true
		);

		$script = <<<'JS'
(function(blocks, element, blockEditor) {
	var registerBlockType = blocks.registerBlockType;
	var Fragment = element.Fragment;
	var TextControl = window.wp.components.TextControl;
	registerBlockType('custom/mybrand-tabbed-content-area', {
		title: 'My Brand Tabbed Content Area',
		icon: 'index-card',
		category: 'widgets',
		attributes: { tabs: { type: 'array', default: [] } },
		edit: function(props) {
			var a = props.attributes, set = props.setAttributes, tabs = a.tabs || [];
			function update(i, k, v) {
				var next = tabs.slice();
				next[i] = next[i] || {};
				next[i][k] = v;
				set({ tabs: next });
			}
			function add() { set({ tabs: tabs.concat([{ label: 'Tab ' + (tabs.length + 1), postId: 0 }]) }); }
			function remove(i) { var next = tabs.slice(); next.splice(i, 1); set({ tabs: next }); }
			function move(i, d) { var ni = i + d; if (ni < 0 || ni >= tabs.length) { return; } var next = tabs.slice(); var temp = next[i]; next[i] = next[ni]; next[ni] = temp; set({ tabs: next }); }
			return element.createElement(
				Fragment,
				{},
				element.createElement(
					'div',
					{},
					tabs.map(function(tab, i) {
						return element.createElement(
							'div',
							{ key: i, style: { border: '1px solid #ddd', padding: '8px', marginBottom: '8px' } },
							element.createElement(TextControl, { label: 'Tab Label', value: tab.label || '', onChange: function(v){ update(i, 'label', v); } }),
							element.createElement(TextControl, { label: 'Page/Post ID', type: 'number', value: tab.postId || 0, onChange: function(v){ update(i, 'postId', parseInt(v, 10) || 0); } }),
							element.createElement(
								'div',
								{},
								element.createElement('button', { type: 'button', className: 'button', onClick: function(){ move(i, -1); } }, 'Move Up'),
								element.createElement('button', { type: 'button', className: 'button', style: { marginLeft: '6px' }, onClick: function(){ move(i, 1); } }, 'Move Down'),
								element.createElement('button', { type: 'button', className: 'button button-link-delete', style: { marginLeft: '6px' }, onClick: function(){ remove(i); } }, 'Remove')
							)
						);
					})
				),
				element.createElement('button', { className: 'button button-primary', onClick: add }, 'Add Tab')
			);
		},
		save: function() { return null; }
	});
})(window.wp.blocks, window.wp.element, window.wp.blockEditor);
JS;
		wp_add_inline_script('um-mybrand-tabbed-content-editor', $script);
		wp_enqueue_script('um-mybrand-tabbed-content-editor');
	}

	/**
	 * Register dynamic block: custom/mybrand-icon.
	 */
	public static function register_page_block_simple_icons(): void {
		if (!function_exists('register_block_type')) {
			return;
		}

		register_block_type('custom/mybrand-icon', [
			'render_callback' => [__CLASS__, 'render_mybrand_simple_icon_block'],
			'attributes' => [
				'icon' => ['type' => 'string', 'default' => 'home'],
				'size' => ['type' => 'string', 'default' => 'medium'],
				'color' => ['type' => 'string', 'default' => ''],
				'alignment' => ['type' => 'string', 'default' => 'left'],
				'className' => ['type' => 'string'],
			],
			'editor_script' => 'um-mybrand-icon-editor',
		]);
	}

	/**
	 * Render callback for custom/mybrand-icon.
	 *
	 * @param array<string,mixed> $attrs Block attributes.
	 */
	public static function render_mybrand_simple_icon_block(array $attrs = []): string {
		$icon = isset($attrs['icon']) ? sanitize_text_field((string) $attrs['icon']) : 'home';
		$size = isset($attrs['size']) ? sanitize_text_field((string) $attrs['size']) : 'medium';
		$color = isset($attrs['color']) ? sanitize_hex_color((string) $attrs['color']) : '';
		$alignment = isset($attrs['alignment']) ? sanitize_key((string) $attrs['alignment']) : 'left';
		if (!in_array($alignment, ['left', 'center', 'right'], true)) {
			$alignment = 'left';
		}
		if ($color === '' || !is_string($color)) {
			$text_color = sanitize_hex_color((string) get_option('lbt_text_color'));
			$color = $text_color ?: '#111111';
		}

		$icon_class = trim($icon);
		if ($icon_class === '') {
			$icon_class = 'fa-solid fa-home';
		} elseif (strpos($icon_class, 'fa-') === false) {
			$icon_name = preg_replace('/[^a-z0-9\-]/i', '', $icon_class);
			$icon_class = 'fa-solid fa-' . $icon_name;
		} elseif (
			strpos($icon_class, 'fa-solid') === false
			&& strpos($icon_class, 'fa-regular') === false
			&& strpos($icon_class, 'fa-brands') === false
		) {
			$icon_class = 'fa-solid ' . $icon_class;
		}

		$tokens = preg_split('/\s+/', $icon_class);
		$tokens = is_array($tokens) ? $tokens : [];
		$tokens = array_map('sanitize_html_class', $tokens);
		$tokens = array_values(array_filter($tokens));
		$icon_class = implode(' ', $tokens);
		if ($icon_class === '') {
			$icon_class = 'fa-solid fa-home';
		}

		$size_map = [
			'small' => '1em',
			'medium' => '1.5em',
			'large' => '2em',
			'x-large' => '3em',
		];
		$icon_size = isset($size_map[$size]) ? $size_map[$size] : $size_map['medium'];
		$class_name = isset($attrs['className']) ? (string) $attrs['className'] : '';
		$class_name = trim((string) preg_replace('/[^A-Za-z0-9_\-\s]/', '', $class_name));
		$extra_classes = $class_name !== '' ? ' ' . esc_attr($class_name) : '';

		ob_start();
		?>
		<div class="lbt-icon-wrapper lbt-icon-align-<?php echo esc_attr($alignment); ?><?php echo $extra_classes; ?>" style="text-align:<?php echo esc_attr($alignment); ?>;">
			<i class="fa <?php echo esc_attr($icon_class); ?>" style="font-size:<?php echo esc_attr($icon_size); ?>;color:<?php echo esc_attr($color); ?>;"></i>
		</div>
		<style>
		.lbt-icon-wrapper { margin: 10px 0; }
		.lbt-icon-wrapper i.fa { display: inline-block; }
		</style>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Enqueue editor UI for custom/mybrand-icon.
	 */
	public static function enqueue_page_block_simple_icons_editor_assets(): void {
		wp_enqueue_style(
			'um-font-awesome-editor',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
			[],
			'6.4.0'
		);

		wp_register_script(
			'um-mybrand-icon-editor',
			false,
			['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n'],
			self::VERSION,
			true
		);

		$script = <<<'JS'
(function(blocks, element, blockEditor, i18n) {
	var registerBlockType = blocks.registerBlockType;
	var TextControl = window.wp.components.TextControl;
	var SelectControl = window.wp.components.SelectControl;
	var PanelBody = window.wp.components.PanelBody;
	var InspectorControls = blockEditor.InspectorControls;
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var commonIcons = [
		{ label: 'Home', value: 'home' }, { label: 'User', value: 'user' }, { label: 'Envelope', value: 'envelope' },
		{ label: 'Phone', value: 'phone' }, { label: 'Star', value: 'star' }, { label: 'Heart', value: 'heart' },
		{ label: 'Shopping Cart', value: 'shopping-cart' }, { label: 'Search', value: 'magnifying-glass' }, { label: 'Settings', value: 'gear' },
		{ label: 'Info', value: 'circle-info' }, { label: 'Check', value: 'check' }, { label: 'X/Close', value: 'xmark' },
		{ label: 'Arrow Right', value: 'arrow-right' }, { label: 'Arrow Left', value: 'arrow-left' }, { label: 'Arrow Up', value: 'arrow-up' }, { label: 'Arrow Down', value: 'arrow-down' }
	];

	registerBlockType('custom/mybrand-icon', {
		title: __('My Brand Quick Icon', 'user-manager'),
		icon: 'star-filled',
		category: 'widgets',
		attributes: {
			icon: { type: 'string', default: 'home' },
			size: { type: 'string', default: 'medium' },
			color: { type: 'string', default: '' },
			alignment: { type: 'string', default: 'left' }
		},
		edit: function(props) {
			var a = props.attributes;
			var set = props.setAttributes;
			var iconValue = a.icon || 'home';
			var previewIcon = iconValue.indexOf('fa-') !== -1 ? iconValue : 'fa-solid fa-' + iconValue;
			return element.createElement(
				Fragment,
				{},
				element.createElement(
					InspectorControls,
					{},
					element.createElement(
						PanelBody,
						{ title: __('Icon Settings', 'user-manager'), initialOpen: true },
						element.createElement(SelectControl, {
							label: __('Common Icons', 'user-manager'),
							value: iconValue,
							options: [{ label: __('Select an icon', 'user-manager'), value: '' }].concat(commonIcons),
							onChange: function(v) { set({ icon: v || 'home' }); }
						}),
						element.createElement(TextControl, {
							label: __('Custom Icon Class (e.g., fa-solid fa-home)', 'user-manager'),
							value: a.icon,
							onChange: function(v) { set({ icon: v || 'home' }); },
							help: __('Enter Font Awesome icon class. Use format: fa-solid fa-iconname or just iconname', 'user-manager')
						}),
						element.createElement(SelectControl, {
							label: __('Size', 'user-manager'),
							value: a.size,
							options: [
								{ label: __('Small', 'user-manager'), value: 'small' },
								{ label: __('Medium', 'user-manager'), value: 'medium' },
								{ label: __('Large', 'user-manager'), value: 'large' },
								{ label: __('X-Large', 'user-manager'), value: 'x-large' }
							],
							onChange: function(v) { set({ size: v }); }
						}),
						element.createElement('div', { style: { marginBottom: '10px' } },
							element.createElement('label', { style: { display: 'block', marginBottom: '5px' } }, __('Icon Color', 'user-manager')),
							element.createElement('input', {
								type: 'color',
								value: a.color || '',
								onChange: function(e) { set({ color: e.target.value }); },
								style: { width: '100%', height: '40px' }
							}),
							element.createElement('p', { style: { fontSize: '12px', color: '#666', marginTop: '5px' } }, __('Leave empty to use default text color', 'user-manager'))
						),
						element.createElement(SelectControl, {
							label: __('Alignment', 'user-manager'),
							value: a.alignment,
							options: [
								{ label: __('Left', 'user-manager'), value: 'left' },
								{ label: __('Center', 'user-manager'), value: 'center' },
								{ label: __('Right', 'user-manager'), value: 'right' }
							],
							onChange: function(v) { set({ alignment: v }); }
						})
					)
				),
				element.createElement(
					'div',
					{ style: { padding: '20px', textAlign: a.alignment || 'left', border: '1px dashed #ccc', borderRadius: '4px' } },
					element.createElement('i', {
						className: 'fa ' + previewIcon,
						style: {
							fontSize: a.size === 'small' ? '1em' : (a.size === 'large' ? '2em' : (a.size === 'x-large' ? '3em' : '1.5em')),
							color: a.color || '#111111'
						}
					})
				)
			);
		},
		save: function() { return null; }
	});
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.i18n);
JS;
		wp_add_inline_script('um-mybrand-icon-editor', $script);
		wp_enqueue_script('um-mybrand-icon-editor');
	}

	/**
	 * Enqueue Font Awesome on front-end for icon block.
	 */
	public static function enqueue_page_block_simple_icons_frontend_assets(): void {
		wp_enqueue_style(
			'um-font-awesome-frontend',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
			[],
			'6.4.0'
		);
	}

	/**
	 * Register dynamic block: custom/mybrand-menu-tiles.
	 */
	public static function register_page_block_menu_tiles(): void {
		if (!function_exists('register_block_type')) {
			return;
		}

		register_block_type('custom/mybrand-menu-tiles', [
			'render_callback' => [__CLASS__, 'render_mybrand_menu_tiles_block'],
			'attributes' => [
				'columns' => ['type' => 'number', 'default' => 3],
				'excludeTitles' => ['type' => 'string', 'default' => ''],
				'buttonBgColor' => ['type' => 'string', 'default' => ''],
				'buttonBgHoverColor' => ['type' => 'string', 'default' => ''],
				'buttonTextColor' => ['type' => 'string', 'default' => ''],
				'buttonTextHoverColor' => ['type' => 'string', 'default' => ''],
				'className' => ['type' => 'string'],
			],
			'editor_script' => 'um-mybrand-menu-tiles-editor',
		]);
	}

	/**
	 * Render callback for custom/mybrand-menu-tiles.
	 *
	 * @param array<string,mixed> $attrs Block attributes.
	 */
	public static function render_mybrand_menu_tiles_block(array $attrs = []): string {
		$columns = isset($attrs['columns']) ? max(1, min(6, (int) $attrs['columns'])) : 3;
		$exclude_titles = isset($attrs['excludeTitles']) ? sanitize_text_field((string) $attrs['excludeTitles']) : '';
		$button_bg = isset($attrs['buttonBgColor']) ? sanitize_hex_color((string) $attrs['buttonBgColor']) : '';
		$button_bg_hover = isset($attrs['buttonBgHoverColor']) ? sanitize_hex_color((string) $attrs['buttonBgHoverColor']) : '';
		$button_text = isset($attrs['buttonTextColor']) ? sanitize_hex_color((string) $attrs['buttonTextColor']) : '';
		$button_text_hover = isset($attrs['buttonTextHoverColor']) ? sanitize_hex_color((string) $attrs['buttonTextHoverColor']) : '';
		$class_name = isset($attrs['className']) ? (string) $attrs['className'] : '';
		$class_name = trim((string) preg_replace('/[^A-Za-z0-9_\-\s]/', '', $class_name));
		$extra_classes = $class_name !== '' ? ' ' . esc_attr($class_name) : '';

		if (!is_string($button_bg) || $button_bg === '') {
			$button_bg = sanitize_hex_color((string) get_option('lbt_button_bg_color')) ?: '#f5f5f5';
		}
		if (!is_string($button_bg_hover) || $button_bg_hover === '') {
			$button_bg_hover = sanitize_hex_color((string) get_option('lbt_button_bg_hover_color')) ?: '#e5e5e5';
		}
		if (!is_string($button_text) || $button_text === '') {
			$button_text = sanitize_hex_color((string) get_option('lbt_button_text_color')) ?: '#333333';
		}
		if (!is_string($button_text_hover) || $button_text_hover === '') {
			$button_text_hover = sanitize_hex_color((string) get_option('lbt_button_text_hover_color')) ?: '#000000';
		}

		$exclude_titles_array = [];
		if ($exclude_titles !== '') {
			$exclude_titles_array = array_values(array_filter(array_map('trim', array_map('strtolower', explode(',', $exclude_titles)))));
		}

		$menu_locations = get_nav_menu_locations();
		if (empty($menu_locations['primary'])) {
			return '<p>' . esc_html__('Main menu not found.', 'user-manager') . '</p>';
		}

		$menu = wp_get_nav_menu_object((int) $menu_locations['primary']);
		if (!$menu) {
			return '<p>' . esc_html__('Main menu not found.', 'user-manager') . '</p>';
		}

		$menu_items = wp_get_nav_menu_items((int) $menu->term_id);
		if (!is_array($menu_items) || empty($menu_items)) {
			return '<p>' . esc_html__('No menu items found.', 'user-manager') . '</p>';
		}

		$unique_id = function_exists('wp_unique_id') ? wp_unique_id('um-menu-tiles-') : uniqid('um-menu-tiles-');
		$rendered_count = 0;

		ob_start();
		?>
		<div class="menu-tiles-grid <?php echo esc_attr($unique_id); ?><?php echo $extra_classes; ?>" style="--tile-cols: <?php echo esc_attr((string) $columns); ?>;">
			<?php foreach ($menu_items as $item) : ?>
				<?php
				if ((int) $item->menu_item_parent !== 0) {
					continue;
				}
				$item_title_lower = strtolower(trim((string) $item->title));
				$should_exclude = false;
				if (!empty($exclude_titles_array)) {
					foreach ($exclude_titles_array as $exclude_title) {
						if ($exclude_title !== '' && strpos($item_title_lower, $exclude_title) !== false) {
							$should_exclude = true;
							break;
						}
					}
				}
				if ($should_exclude) {
					continue;
				}
				$rendered_count++;
				?>
				<a href="<?php echo esc_url((string) $item->url); ?>" class="menu-tile">
					<?php echo esc_html((string) $item->title); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php if ($rendered_count === 0) : ?>
			<p><?php esc_html_e('No menu items found after filters.', 'user-manager'); ?></p>
		<?php endif; ?>
		<style>
		.<?php echo esc_attr($unique_id); ?>.menu-tiles-grid {
			display: grid;
			grid-template-columns: repeat(var(--tile-cols), 1fr);
			gap: 20px;
			padding: 20px;
		}
		.<?php echo esc_attr($unique_id); ?> .menu-tile {
			display: flex;
			align-items: center;
			justify-content: center;
			background: <?php echo esc_attr($button_bg); ?>;
			border: 2px solid #ccc;
			padding: 40px 20px;
			text-align: center;
			font-size: 1.5em;
			font-weight: bold;
			color: <?php echo esc_attr($button_text); ?>;
			text-decoration: none;
			transition: all 0.3s ease;
			border-radius: 5px;
		}
		.<?php echo esc_attr($unique_id); ?> .menu-tile:hover {
			text-decoration: underline;
			background: <?php echo esc_attr($button_bg_hover); ?>;
			color: <?php echo esc_attr($button_text_hover); ?>;
			border-color: #000000;
		}
		@media (max-width: 768px) {
			.<?php echo esc_attr($unique_id); ?>.menu-tiles-grid {
				grid-template-columns: 1fr;
			}
		}
		</style>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Enqueue editor UI for custom/mybrand-menu-tiles.
	 */
	public static function enqueue_page_block_menu_tiles_editor_assets(): void {
		wp_register_script(
			'um-mybrand-menu-tiles-editor',
			false,
			['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
			self::VERSION,
			true
		);

		$script = <<<'JS'
(function(blocks, element, components, blockEditor) {
	var registerBlockType = blocks.registerBlockType;
	var Fragment = element.Fragment;
	var TextControl = components.TextControl;
	var PanelBody = components.PanelBody;
	var InspectorControls = blockEditor.InspectorControls;
	registerBlockType('custom/mybrand-menu-tiles', {
		title: 'Menu Tile Buttons',
		icon: 'screenoptions',
		category: 'widgets',
		attributes: {
			columns: { type: 'number', default: 3 },
			excludeTitles: { type: 'string', default: '' },
			buttonBgColor: { type: 'string', default: '' },
			buttonBgHoverColor: { type: 'string', default: '' },
			buttonTextColor: { type: 'string', default: '' },
			buttonTextHoverColor: { type: 'string', default: '' }
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
						{ title: 'Settings', initialOpen: true },
						element.createElement(TextControl, { label: 'Columns', type: 'number', value: a.columns, onChange: function(v){ set({ columns: parseInt(v, 10) || 3 }); } }),
						element.createElement(TextControl, { label: 'Exclude Menu Titles (comma-separated partial matches)', value: a.excludeTitles || '', onChange: function(v){ set({ excludeTitles: v || '' }); } }),
						element.createElement(TextControl, { label: 'Button Background Color (hex)', value: a.buttonBgColor || '', onChange: function(v){ set({ buttonBgColor: v || '' }); } }),
						element.createElement(TextControl, { label: 'Button Hover Background Color (hex)', value: a.buttonBgHoverColor || '', onChange: function(v){ set({ buttonBgHoverColor: v || '' }); } }),
						element.createElement(TextControl, { label: 'Button Text Color (hex)', value: a.buttonTextColor || '', onChange: function(v){ set({ buttonTextColor: v || '' }); } }),
						element.createElement(TextControl, { label: 'Button Hover Text Color (hex)', value: a.buttonTextHoverColor || '', onChange: function(v){ set({ buttonTextHoverColor: v || '' }); } })
					)
				),
				element.createElement('p', {}, 'Preview available on front-end.')
			);
		},
		save: function() { return null; }
	});
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor);
JS;
		wp_add_inline_script('um-mybrand-menu-tiles-editor', $script);
		wp_enqueue_script('um-mybrand-menu-tiles-editor');
	}
}

