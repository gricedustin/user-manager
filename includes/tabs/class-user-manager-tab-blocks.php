<?php
/**
 * Blocks tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/class-user-manager-addon-page-block-menu-tiles.php';
require_once __DIR__ . '/class-user-manager-addon-page-block-simple-icons.php';
require_once __DIR__ . '/class-user-manager-addon-page-block-subpages-grid.php';
require_once __DIR__ . '/class-user-manager-addon-page-block-tabbed-content-area.php';

class User_Manager_Tab_Blocks {

	public static function render(): void {
		$settings = User_Manager_Core::get_settings();
		$settings_form_id = 'um-blocks-settings-form';
		$block_sections = self::get_block_sections($settings);
		$sorted_block_sections = $block_sections;
		uasort($sorted_block_sections, static function (array $a, array $b): int {
			$a_label = isset($a['label']) ? (string) $a['label'] : '';
			$b_label = isset($b['label']) ? (string) $b['label'] : '';
			return strcasecmp($a_label, $b_label);
		});

		$block_tags = self::get_block_tags($block_sections);
		$current_block_tag = isset($_GET['block_tag']) ? sanitize_title(wp_unslash($_GET['block_tag'])) : '';
		if ($current_block_tag !== '' && !isset($block_tags[$current_block_tag])) {
			$current_block_tag = '';
		}
		$current_block_section = isset($_GET['block_section']) ? sanitize_key(wp_unslash($_GET['block_section'])) : '';
		if ($current_block_section !== '' && !isset($block_sections[$current_block_section])) {
			$current_block_section = '';
		}

		$blocks_base_url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_BLOCKS);
		?>
		<ul class="subsubsub" style="margin: 12px 0 14px;">
			<?php $tag_total = count($block_tags); $tag_index = 0; ?>
			<li>
				<a href="<?php echo esc_url($blocks_base_url); ?>" class="<?php echo $current_block_tag === '' ? 'current' : ''; ?>">
					<?php esc_html_e('All Blocks', 'user-manager'); ?>
				</a> |
			</li>
			<?php foreach ($block_tags as $tag_key => $tag_label) : $tag_index++; ?>
				<li>
					<a href="<?php echo esc_url(add_query_arg('block_tag', $tag_key, $blocks_base_url)); ?>" class="<?php echo $current_block_tag === $tag_key ? 'current' : ''; ?>">
						<?php echo esc_html($tag_label); ?>
					</a><?php echo $tag_index < $tag_total ? ' |' : ''; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<br class="clear" />

		<?php if ($current_block_section === '') : ?>
			<div class="um-admin-card um-admin-card-full" style="margin-bottom: 16px;">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-filter"></span>
					<h2><?php esc_html_e('Blocks Filter', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
						<div style="min-width:280px; flex:1;">
							<label for="um-blocks-filter-text"><strong><?php esc_html_e('Keyword filter', 'user-manager'); ?></strong></label>
							<input type="text" id="um-blocks-filter-text" class="regular-text" style="width:100%; max-width:560px;" placeholder="<?php esc_attr_e('Type to filter blocks by title, description, or tag...', 'user-manager'); ?>" />
						</div>
						<div>
							<button type="button" class="button" id="um-blocks-filter-clear"><?php esc_html_e('Clear Filter', 'user-manager'); ?></button>
						</div>
					</div>
					<p class="description" id="um-blocks-filter-empty" style="display:none; margin-top: 10px;">
						<?php esc_html_e('No blocks match the current filter.', 'user-manager'); ?>
					</p>
				</div>
			</div>
		<?php endif; ?>

		<div class="um-blocks-empty-state" style="<?php echo $current_block_section === '' ? '' : 'display:none;'; ?>">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-screenoptions"></span>
					<h2><?php esc_html_e('Choose a Block', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-block-tile-grid">
						<?php
						$visible_tiles = 0;
						foreach ($sorted_block_sections as $section_key => $section_meta) :
							$section_tags = isset($section_meta['tags']) && is_array($section_meta['tags']) ? $section_meta['tags'] : [];
							if ($current_block_tag !== '' && !isset($section_tags[$current_block_tag])) {
								continue;
							}
							$visible_tiles++;
							$is_active = !empty($section_meta['active']);
							?>
							<a
								class="um-addon-tile<?php echo $is_active ? ' um-addon-tile-active' : ''; ?>"
								href="<?php echo esc_url(add_query_arg(['block_section' => $section_key, 'block_tag' => $current_block_tag], $blocks_base_url)); ?>"
							>
								<span class="um-addon-tile-title"><?php echo esc_html((string) $section_meta['label']); ?></span>
								<span class="um-addon-tile-status"><?php echo $is_active ? esc_html__('Active', 'user-manager') : esc_html__('Inactive', 'user-manager'); ?></span>
								<?php if (!empty($section_meta['description'])) : ?>
									<span class="um-addon-tile-description"><?php echo esc_html((string) $section_meta['description']); ?></span>
								<?php endif; ?>
								<?php if (!empty($section_tags)) : ?>
									<span class="um-addon-tile-tags">
										<?php foreach ($section_tags as $section_tag_key => $section_tag_label) : ?>
											<span class="um-addon-tile-tag um-addon-tile-tag-<?php echo esc_attr($section_tag_key); ?>"><?php echo esc_html((string) $section_tag_label); ?></span>
										<?php endforeach; ?>
									</span>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
						<?php if ($visible_tiles === 0) : ?>
							<p class="description"><?php esc_html_e('No blocks match this tag.', 'user-manager'); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="<?php echo esc_attr($settings_form_id); ?>">
			<input type="hidden" name="action" value="user_manager_save_settings" />
			<input type="hidden" name="settings_section" value="blocks" />
			<input type="hidden" name="block_section" value="<?php echo esc_attr($current_block_section); ?>" />
			<input type="hidden" name="block_tag" value="<?php echo esc_attr($current_block_tag); ?>" />
			<?php wp_nonce_field('user_manager_save_settings'); ?>
			<div class="um-admin-grid um-admin-grid-single um-blocks-main-grid" style="<?php echo $current_block_section === '' ? 'display:none;' : ''; ?>">
				<div class="um-block-section" data-block-section="page-block-subpages-grid">
					<?php User_Manager_Addon_Page_Block_Subpages_Grid::render($settings, $settings_form_id); ?>
				</div>
				<div class="um-block-section" data-block-section="page-block-tabbed-content-area">
					<?php User_Manager_Addon_Page_Block_Tabbed_Content_Area::render($settings, $settings_form_id); ?>
				</div>
				<div class="um-block-section" data-block-section="page-block-simple-icons">
					<?php User_Manager_Addon_Page_Block_Simple_Icons::render($settings, $settings_form_id); ?>
				</div>
				<div class="um-block-section" data-block-section="page-block-menu-tiles">
					<?php User_Manager_Addon_Page_Block_Menu_Tiles::render($settings, $settings_form_id); ?>
				</div>
				<div class="um-admin-card um-admin-card-full um-block-save-card">
					<div class="um-admin-card-body">
						<p style="margin:0;">
							<?php submit_button(__('Save', 'user-manager'), 'primary', 'submit', false, ['form' => $settings_form_id]); ?>
						</p>
					</div>
				</div>
			</div>
		</form>

		<style>
		.um-admin-grid.um-blocks-main-grid {
			margin-top: 0;
		}
		.um-block-tile-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(240px, 240px));
			grid-auto-rows: 1fr;
			gap: 12px;
			justify-content: start;
			align-items: stretch;
		}
		.um-addon-tile {
			display: flex;
			flex-direction: column;
			height: 100%;
			box-sizing: border-box;
			min-height: 180px;
			padding: 12px;
			border: 1px solid #dcdcde;
			border-radius: 6px;
			background: #fff;
			text-decoration: none;
			color: #1d2327;
			transition: border-color 120ms ease, box-shadow 120ms ease, background-color 120ms ease;
		}
		.um-addon-tile:hover,
		.um-addon-tile:focus {
			border-color: #72aee6;
			box-shadow: 0 0 0 1px rgba(34, 113, 177, 0.18);
			outline: none;
		}
		.um-addon-tile.um-addon-tile-active {
			background: #e7f1ff;
			border-color: #72aee6;
		}
		.um-addon-tile-title {
			display: block;
			font-weight: 600;
			margin-bottom: 4px;
		}
		.um-addon-tile-description {
			display: block;
			font-size: 12px;
			line-height: 1.4;
			color: #50575e;
			margin-bottom: 6px;
		}
		.um-addon-tile-tags {
			display: flex;
			flex-wrap: wrap;
			gap: 4px;
			padding-top: 6px;
			margin-top: auto;
		}
		.um-addon-tile-tag {
			display: inline-block;
			padding: 1px 7px;
			border-radius: 999px;
			background: #f0f6fc;
			border: 1px solid #c5d9ed;
			color: #0a4b78;
			font-size: 11px;
			line-height: 1.5;
			font-weight: 500;
		}
		.um-addon-tile-status {
			display: block;
			font-size: 12px;
			color: #50575e;
			margin-bottom: 6px;
		}
		@media (max-width: 600px) {
			.um-block-tile-grid {
				grid-template-columns: minmax(220px, 1fr);
			}
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			var currentBlockSection = '<?php echo esc_js($current_block_section); ?>';

			function normalizeFilterText(str) {
				return (str || '').toString().toLowerCase().trim();
			}

			function filterHaystack($root) {
				var text = [];
				text.push($root.text());
				text.push($root.attr('data-block-section'));
				text.push($root.attr('href'));
				return normalizeFilterText(text.join(' '));
			}

			function applyBlocksFilter() {
				var keyword = normalizeFilterText($('#um-blocks-filter-text').val());
				var anyVisible = false;

				if (!currentBlockSection) {
					$('.um-blocks-empty-state .um-addon-tile').each(function() {
						var $tile = $(this);
						var matched = keyword === '' || filterHaystack($tile).indexOf(keyword) !== -1;
						$tile.toggle(matched);
						if (matched) {
							anyVisible = true;
						}
					});
				} else {
					$('.um-block-section[data-block-section="' + currentBlockSection + '"] .um-admin-card').each(function() {
						var $card = $(this);
						var matched = keyword === '' || filterHaystack($card).indexOf(keyword) !== -1;
						$card.toggle(matched);
						if (matched) {
							anyVisible = true;
						}
					});
					$('.um-block-save-card').show();
				}

				$('#um-blocks-filter-empty').toggle(keyword !== '' && !anyVisible);
			}

			function isAddonCardActive($card) {
				var selectorsRaw = ($card.attr('data-um-active-selectors') || '').trim();
				if (selectorsRaw !== '') {
					var selectors = selectorsRaw.split(',');
					for (var i = 0; i < selectors.length; i++) {
						var selector = $.trim(selectors[i]);
						if (!selector) {
							continue;
						}
						var $inputs = $(selector);
						if ($inputs.length && $inputs.filter(':checked').length > 0) {
							return true;
						}
					}
					return false;
				}
				return false;
			}

			function setAddonCardCollapsed($card, collapsed, skipAnimation) {
				var $body = $card.children('.um-admin-card-body').first();
				if (!$body.length) {
					return;
				}
				var $indicator = $card.children('.um-admin-card-header').find('.um-addon-collapse-indicator');
				if (collapsed) {
					$card.addClass('um-addon-collapsed');
					if (skipAnimation) {
						$body.hide();
					} else {
						$body.stop(true, true).slideUp(150);
					}
					$indicator.text('+');
				} else {
					$card.removeClass('um-addon-collapsed');
					if (skipAnimation) {
						$body.show();
					} else {
						$body.stop(true, true).slideDown(150);
					}
					$indicator.text('−');
				}
			}

			function setAddonCardActiveState($card, isActive) {
				var $header = $card.children('.um-admin-card-header').first();
				var $activeIndicator = $header.find('.um-addon-active-indicator');
				if (!$activeIndicator.length) {
					return;
				}
				$card.toggleClass('um-addon-active', isActive);
				$activeIndicator.find('.um-addon-active-label').text(isActive ? '<?php echo esc_js(__('Active', 'user-manager')); ?>' : '<?php echo esc_js(__('Inactive', 'user-manager')); ?>');
			}

			function refreshAddonCardAutoState($card) {
				setAddonCardActiveState($card, isAddonCardActive($card));
			}

			function initAddonCollapsibleCards() {
				$('.um-addon-collapsible').each(function() {
					var $card = $(this);
					var $header = $card.children('.um-admin-card-header').first();
					if (!$header.length) {
						return;
					}
					if (!$header.find('.um-addon-active-indicator').length) {
						$header.append('<span class="um-addon-active-indicator"><span class="um-addon-active-dot"></span><span class="um-addon-active-label"><?php echo esc_js(__('Inactive', 'user-manager')); ?></span></span>');
					}
					if (!$header.find('.um-addon-collapse-indicator').length) {
						$header.append('<span class="um-addon-collapse-indicator">+</span>');
					}
					$header.css('cursor', 'pointer');
					$header.on('click', function(e) {
						if ($(e.target).closest('a,button,input,select,textarea,label').length) {
							return;
						}
						setAddonCardCollapsed($card, !$card.hasClass('um-addon-collapsed'));
					});

					setAddonCardCollapsed($card, currentBlockSection === '', true);
					refreshAddonCardAutoState($card);
				});
			}

			function applyBlockSectionFilter() {
				if (!currentBlockSection) {
					$('.um-blocks-empty-state').show();
					$('.um-block-section').hide();
					$('.um-block-save-card').hide();
					return;
				}
				$('.um-blocks-empty-state').hide();
				$('.um-block-section').hide();
				$('.um-block-section[data-block-section="' + currentBlockSection + '"]').show();
				$('.um-block-save-card').show();
			}

			function togglePageBlockSubpagesGridFields() {
				$('#um-page-block-subpages-grid-fields').toggle($('#um-page-block-subpages-grid-enabled').is(':checked'));
			}

			function togglePageBlockTabbedContentAreaFields() {
				$('#um-page-block-tabbed-content-area-fields').toggle($('#um-page-block-tabbed-content-area-enabled').is(':checked'));
			}

			function togglePageBlockSimpleIconsFields() {
				$('#um-page-block-simple-icons-fields').toggle($('#um-page-block-simple-icons-enabled').is(':checked'));
			}

			function togglePageBlockMenuTilesFields() {
				$('#um-page-block-menu-tiles-fields').toggle($('#um-page-block-menu-tiles-enabled').is(':checked'));
			}

			applyBlockSectionFilter();
			$('#um-blocks-filter-text').on('input', applyBlocksFilter);
			$('#um-blocks-filter-clear').on('click', function() {
				$('#um-blocks-filter-text').val('');
				applyBlocksFilter();
			});
			initAddonCollapsibleCards();
			togglePageBlockSubpagesGridFields();
			togglePageBlockTabbedContentAreaFields();
			togglePageBlockSimpleIconsFields();
			togglePageBlockMenuTilesFields();

			$('#um-page-block-subpages-grid-enabled').on('change', function() {
				togglePageBlockSubpagesGridFields();
				refreshAddonCardAutoState($('#um-addon-card-page-block-subpages-grid'));
			});
			$('#um-page-block-tabbed-content-area-enabled').on('change', function() {
				togglePageBlockTabbedContentAreaFields();
				refreshAddonCardAutoState($('#um-addon-card-page-block-tabbed-content-area'));
			});
			$('#um-page-block-simple-icons-enabled').on('change', function() {
				togglePageBlockSimpleIconsFields();
				refreshAddonCardAutoState($('#um-addon-card-page-block-simple-icons'));
			});
			$('#um-page-block-menu-tiles-enabled').on('change', function() {
				togglePageBlockMenuTilesFields();
				refreshAddonCardAutoState($('#um-addon-card-page-block-menu-tiles'));
			});

			applyBlocksFilter();
		});
		</script>
		<?php
	}

	/**
	 * Blocks tab sections.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<string,array{label:string,description:string,active:bool,tags:array<string,string>}>
	 */
	private static function get_block_sections(array $settings): array {
		return [
			'page-block-subpages-grid' => [
				'label' => __('Subpages Grid', 'user-manager'),
				'description' => __('Build visual tile grids for child pages using either a block or shortcode.', 'user-manager'),
				'active' => !empty($settings['page_block_subpages_grid_enabled']),
				'tags' => [
					'grids' => __('Grids', 'user-manager'),
					'shortcodes' => __('Shortcodes', 'user-manager'),
				],
			],
			'page-block-tabbed-content-area' => [
				'label' => __('Tabbed Content Area', 'user-manager'),
				'description' => __('Create tabbed layouts that pull content from selected pages or posts.', 'user-manager'),
				'active' => !empty($settings['page_block_tabbed_content_area_enabled']),
				'tags' => [
					'tabs' => __('Tabs', 'user-manager'),
					'content' => __('Content', 'user-manager'),
				],
			],
			'page-block-simple-icons' => [
				'label' => __('Simple Icons', 'user-manager'),
				'description' => __('Drop in quick icon blocks for feature highlights, links, and callouts.', 'user-manager'),
				'active' => !empty($settings['page_block_simple_icons_enabled']),
				'tags' => [
					'icons' => __('Icons', 'user-manager'),
					'content' => __('Content', 'user-manager'),
				],
			],
			'page-block-menu-tiles' => [
				'label' => __('Menu Tiles', 'user-manager'),
				'description' => __('Turn your navigation menu into a tile-based content grid.', 'user-manager'),
				'active' => !empty($settings['page_block_menu_tiles_enabled']),
				'tags' => [
					'grids' => __('Grids', 'user-manager'),
					'navigation' => __('Navigation', 'user-manager'),
				],
			],
		];
	}

	/**
	 * Flatten and sort block tags from section metadata.
	 *
	 * @param array<string,array<string,mixed>> $sections
	 * @return array<string,string>
	 */
	private static function get_block_tags(array $sections): array {
		$tags = [];
		foreach ($sections as $section_meta) {
			$section_tags = isset($section_meta['tags']) && is_array($section_meta['tags']) ? $section_meta['tags'] : [];
			foreach ($section_tags as $tag_key => $tag_label) {
				$tags[sanitize_title((string) $tag_key)] = (string) $tag_label;
			}
		}

		asort($tags, SORT_NATURAL | SORT_FLAG_CASE);
		return $tags;
	}
}

