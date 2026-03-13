<?php
/**
 * Add-ons tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/class-user-manager-addon-my-account-site-admin.php';
require_once __DIR__ . '/class-user-manager-addon-bulk-add-to-cart.php';
require_once __DIR__ . '/class-user-manager-addon-bulk-coupons.php';
require_once __DIR__ . '/class-user-manager-addon-blog-post-idea-generator.php';
require_once __DIR__ . '/class-user-manager-addon-checkout-predefined-addresses.php';
require_once __DIR__ . '/class-user-manager-addon-coupon-notifications-for-users-with-coupons.php';
require_once __DIR__ . '/class-user-manager-addon-coupon-remaining-balances.php';
require_once __DIR__ . '/class-user-manager-addon-coupons-for-new-users.php';
require_once __DIR__ . '/class-user-manager-addon-custom-admin-notifications.php';
require_once __DIR__ . '/class-user-manager-addon-my-account-coupon-screen.php';
require_once __DIR__ . '/class-user-manager-addon-post-meta.php';
require_once __DIR__ . '/class-user-manager-addon-quick-search.php';
require_once __DIR__ . '/class-user-manager-addon-wp-admin-bar-menu-items.php';
require_once __DIR__ . '/class-user-manager-addon-wp-admin-css.php';
require_once __DIR__ . '/class-user-manager-addon-api.php';
require_once __DIR__ . '/class-user-manager-addon-role-switching.php';

class User_Manager_Tab_Addons {

	public static function render(): void {
		$settings      = User_Manager_Core::get_settings();
		$bulk_settings = get_option('bulk_add_to_cart_settings', []);
		$settings_form_id = 'um-addons-settings-form';
		$addon_sections = self::get_addon_sections($settings);
		$sorted_addon_sections = $addon_sections;
		uasort($sorted_addon_sections, static function (array $a, array $b): int {
			$a_label = isset($a['label']) ? (string) $a['label'] : '';
			$b_label = isset($b['label']) ? (string) $b['label'] : '';
			return strcasecmp($a_label, $b_label);
		});
		$addon_tags = self::get_addon_tags($addon_sections);
		$current_addon_tag = isset($_GET['addon_tag']) ? sanitize_title(wp_unslash($_GET['addon_tag'])) : '';
		if ($current_addon_tag !== '' && !isset($addon_tags[$current_addon_tag])) {
			$current_addon_tag = '';
		}
		$current_addon_section = isset($_GET['addon_section']) ? sanitize_key(wp_unslash($_GET['addon_section'])) : '';
		if ($current_addon_section !== '' && !isset($addon_sections[$current_addon_section])) {
			$current_addon_section = '';
		}
		$addons_base_url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS);
		?>
		<ul class="subsubsub" style="margin: 12px 0 14px;">
			<?php $tag_total = count($addon_tags); $tag_index = 0; ?>
			<li>
				<a href="<?php echo esc_url($addons_base_url); ?>" class="<?php echo $current_addon_tag === '' ? 'current' : ''; ?>">
					<?php esc_html_e('All Add-ons', 'user-manager'); ?>
				</a> |
			</li>
			<?php foreach ($addon_tags as $tag_key => $tag_label) : $tag_index++; ?>
				<li>
					<a href="<?php echo esc_url(add_query_arg('addon_tag', $tag_key, $addons_base_url)); ?>" class="<?php echo $current_addon_tag === $tag_key ? 'current' : ''; ?>">
						<?php echo esc_html($tag_label); ?>
					</a><?php echo $tag_index < $tag_total ? ' |' : ''; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<br class="clear" />

		<div class="um-addons-empty-state" style="<?php echo $current_addon_section === '' ? '' : 'display:none;'; ?>">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-screenoptions"></span>
					<h2><?php esc_html_e('Choose an Add-on', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-addon-tile-grid">
						<?php
						$visible_tiles = 0;
						foreach ($sorted_addon_sections as $section_key => $section_meta) :
							$section_tags = self::get_addon_section_tags($section_meta);
							if ($current_addon_tag !== '' && !isset($section_tags[$current_addon_tag])) {
								continue;
							}
							$visible_tiles++;
							?>
							<?php $is_active = !empty($section_meta['active']); ?>
							<a
								class="um-addon-tile<?php echo $is_active ? ' um-addon-tile-active' : ''; ?>"
								href="<?php echo esc_url(add_query_arg(['addon_section' => $section_key, 'addon_tag' => $current_addon_tag], $addons_base_url)); ?>"
							>
								<span class="um-addon-tile-title"><?php echo esc_html((string) $section_meta['label']); ?></span>
								<span class="um-addon-tile-status"><?php echo $is_active ? esc_html__('Active', 'user-manager') : esc_html__('Inactive', 'user-manager'); ?></span>
								<?php if (!empty($section_meta['description'])) : ?>
									<span class="um-addon-tile-description"><?php echo esc_html((string) $section_meta['description']); ?></span>
								<?php endif; ?>
								<?php if (!empty($section_tags)) : ?>
									<span class="um-addon-tile-tags">
										<?php foreach ($section_tags as $section_tag_key => $section_tag_label) : ?>
											<span class="um-addon-tile-tag um-addon-tile-tag-<?php echo esc_attr($section_tag_key); ?>"><?php echo esc_html($section_tag_label); ?></span>
										<?php endforeach; ?>
									</span>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
						<?php if ($visible_tiles === 0) : ?>
							<p class="description"><?php esc_html_e('No add-ons match this tag.', 'user-manager'); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="<?php echo esc_attr($settings_form_id); ?>">
			<input type="hidden" name="action" id="um-addons-form-action" value="user_manager_save_settings" />
			<input type="hidden" name="settings_section" value="addons" />
			<input type="hidden" name="addon_section" value="<?php echo esc_attr($current_addon_section); ?>" />
			<input type="hidden" name="addon_tag" value="<?php echo esc_attr($current_addon_tag); ?>" />
			<?php wp_nonce_field('user_manager_save_settings'); ?>
			<div class="um-admin-grid um-admin-grid-single" style="<?php echo $current_addon_section === '' ? 'display:none;' : ''; ?>">
				<div class="um-addon-section" data-addon-section="add-to-cart-bulk-import">
					<?php User_Manager_Addon_Bulk_Add_To_Cart::render($settings, $bulk_settings); ?>
				</div>
				<div class="um-addon-section" data-addon-section="checkout-pre-defined-addresses">
					<?php User_Manager_Addon_Checkout_Predefined_Addresses::render($settings); ?>
				</div>
				<div class="um-addon-section" data-addon-section="coupon-creator">
					<?php User_Manager_Addon_Bulk_Coupons::render($settings); ?>
				</div>
				<div class="um-addon-section" data-addon-section="coupon-for-new-user">
					<?php User_Manager_Addon_Coupons_For_New_Users::render($settings); ?>
				</div>
				<div class="um-addon-section" data-addon-section="coupon-notifications-for-users-with-coupons">
					<?php User_Manager_Addon_Coupon_Notifications_For_Users_With_Coupons::render($settings); ?>
				</div>
				<div class="um-addon-section" data-addon-section="coupon-remaining-balances">
					<?php User_Manager_Addon_Coupon_Remaining_Balances::render($settings); ?>
				</div>
				<div class="um-addon-section" data-addon-section="my-account-coupon-screen">
					<?php User_Manager_Addon_My_Account_Coupon_Screen::render($settings); ?>
				</div>
				<div class="um-addon-section" data-addon-section="my-account-site-admin">
					<?php User_Manager_Addon_My_Account_Site_Admin::render($settings); ?>
				</div>
			</div>
		</form>
		<div class="um-admin-grid um-admin-grid-single" style="<?php echo $current_addon_section === '' ? 'display:none;' : ''; ?>">
			<div class="um-addon-section" data-addon-section="post-meta">
				<?php User_Manager_Addon_Post_Meta::render($settings, $settings_form_id); ?>
			</div>
			<div class="um-addon-section" data-addon-section="post-content-generator">
				<?php User_Manager_Addon_API::render($settings, $settings_form_id); ?>
			</div>
			<div class="um-addon-section" data-addon-section="post-idea-generator">
				<?php User_Manager_Addon_Blog_Post_Idea_Generator::render($settings, $settings_form_id); ?>
			</div>
			<div class="um-addon-section" data-addon-section="user-role-switching">
				<?php User_Manager_Addon_Role_Switching::render($settings_form_id); ?>
			</div>
			<div class="um-addon-section" data-addon-section="wp-admin-bar-menu-items">
				<?php User_Manager_Addon_WP_Admin_Bar_Menu_Items::render($settings, $settings_form_id); ?>
			</div>
			<div class="um-addon-section" data-addon-section="wp-admin-bar-quick-search">
				<?php User_Manager_Addon_Quick_Search::render($settings, $settings_form_id); ?>
			</div>
			<div class="um-addon-section" data-addon-section="wp-admin-css">
				<?php User_Manager_Addon_WP_Admin_CSS::render($settings, $settings_form_id); ?>
			</div>
			<div class="um-addon-section" data-addon-section="wp-admin-notifications">
				<?php User_Manager_Addon_Custom_Admin_Notifications::render($settings, $settings_form_id); ?>
			</div>
			<div class="um-admin-card um-admin-card-full um-addon-save-card">
				<div class="um-admin-card-body">
					<p style="margin:0;">
						<?php submit_button(__('Save Add-ons', 'user-manager'), 'primary', 'submit', false, ['form' => $settings_form_id]); ?>
					</p>
				</div>
			</div>
		</div>

		<?php User_Manager_Addon_Custom_Admin_Notifications::render_template($settings_form_id); ?>
		<?php User_Manager_Addon_WP_Admin_Bar_Menu_Items::render_template($settings_form_id); ?>

		<style>
		.um-addon-tile-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(240px, 240px));
			gap: 12px;
			justify-content: start;
		}
		.um-addon-tile {
			display: block;
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
			margin-bottom: 6px;
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
			.um-addon-tile-grid {
				grid-template-columns: minmax(220px, 1fr);
			}
		}
		.um-addon-collapsible .um-admin-card-header {
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.um-addon-active-indicator {
			margin-left: auto;
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 2px 8px;
			border-radius: 999px;
			font-size: 11px;
			font-weight: 600;
			line-height: 1.4;
			border: 1px solid #dcdcde;
			background: #f6f7f7;
			color: #50575e;
		}
		.um-addon-active-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: #8c8f94;
			box-shadow: 0 0 0 2px rgba(140, 143, 148, 0.2);
		}
		.um-addon-collapsible.um-addon-active .um-addon-active-indicator {
			background: #e7f1ff;
			border-color: #72aee6;
			color: #0a4b78;
		}
		.um-addon-collapsible.um-addon-active .um-addon-active-dot {
			background: #2271b1;
			box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.25);
		}
		.um-addon-collapse-indicator {
			margin-left: 8px;
			font-weight: 700;
			font-size: 18px;
			line-height: 1;
		}
		.um-checkbox-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
			gap: 8px;
			margin-top: 8px;
		}
		.um-checkbox-chip {
			display: flex;
			align-items: flex-start;
			gap: 8px;
			padding: 8px 10px;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			background: #f6f7f7;
		}
		.um-checkbox-chip input {
			margin-top: 2px;
		}
		.um-checkbox-chip span {
			display: inline-block;
			font-size: 13px;
			line-height: 1.4;
			font-weight: 400;
		}
		.um-checkbox-section-title {
			margin: 0 0 6px;
			font-size: 14px;
			font-weight: 600;
		}
		.um-settings-two-column {
			display: flex;
			flex-wrap: wrap;
			gap: 24px;
			margin-top: 12px;
		}
		.um-settings-two-column .um-settings-column {
			flex: 1 1 280px;
		}
		.um-settings-two-column label {
			display: inline-block;
			margin-bottom: 6px;
		}
		@media (max-width: 600px) {
			.um-checkbox-grid {
				grid-template-columns: 1fr;
			}
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			var addonActiveText = '<?php echo esc_js(__('Active', 'user-manager')); ?>';
			var addonInactiveText = '<?php echo esc_js(__('Inactive', 'user-manager')); ?>';
			var currentAddonSection = '<?php echo esc_js($current_addon_section); ?>';

			function applyAddonSectionFilter() {
				if (!currentAddonSection) {
					$('.um-addons-empty-state').show();
					$('.um-addon-section').hide();
					$('.um-addon-save-card').hide();
					return;
				}
				$('.um-addons-empty-state').hide();
				$('.um-addon-section').hide();
				$('.um-addon-section[data-addon-section="' + currentAddonSection + '"]').show();
				$('.um-addon-save-card').show();
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

				var cardId = $card.attr('id') || '';
				if (cardId === 'um-addon-card-custom-notifications') {
					var hasNotification = false;
					$('#um-custom-admin-notifications-list .um-admin-notification-block').each(function() {
						var $block = $(this);
						var title = $.trim($block.find('input[name*="[title]"]').val() || '');
						var body = $.trim($block.find('textarea[name*="[body]"]').val() || '');
						if (title !== '' || body !== '') {
							hasNotification = true;
							return false;
						}
					});
					return hasNotification;
				}
				if (cardId === 'um-addon-card-admin-bar-menu') {
					var hasMenu = false;
					$('#um-admin-bar-menu-list .um-admin-bar-menu-block').each(function() {
						var $block = $(this);
						var title = $.trim($block.find('input[name*="[title]"]').val() || '');
						var shortcuts = $.trim($block.find('textarea[name*="[shortcuts]"]').val() || '');
						if (title !== '' || shortcuts !== '') {
							hasMenu = true;
							return false;
						}
					});
					return hasMenu;
				}
				if (cardId === 'um-addon-card-admin-css') {
					var allCss = $.trim($('#um-wp-admin-css-all').val() || '');
					var usersCss = $.trim($('#um-wp-admin-css-users-css').val() || '');
					return allCss !== '' || usersCss !== '';
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
				$activeIndicator.find('.um-addon-active-label').text(isActive ? addonActiveText : addonInactiveText);
			}

			function refreshAddonCardAutoState($card) {
				var isActive = isAddonCardActive($card);
				setAddonCardActiveState($card, isActive);
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

					// Keep cards collapsed by default in choose/add-on index view,
					// but auto-expand when user is focused on a single add-on section.
					setAddonCardCollapsed($card, currentAddonSection === '', true);
					refreshAddonCardAutoState($card);
				});
			}

			applyAddonSectionFilter();
			initAddonCollapsibleCards();

			function umToggleBulkMetaFieldRow() {
				var type = $('#um-bulk-identifier-type').val();
				if (type === 'meta_field') {
					$('#um-bulk-meta-field-name-row').show();
				} else {
					$('#um-bulk-meta-field-name-row').hide();
				}
			}
			umToggleBulkMetaFieldRow();
			$('#um-bulk-identifier-type').on('change', umToggleBulkMetaFieldRow);

			function toggleCheckoutShipToFields() {
				if ($('#um-checkout-ship-to-predefined').is(':checked')) {
					$('#um-checkout-ship-to-predefined-fields').show();
				} else {
					$('#um-checkout-ship-to-predefined-fields').hide();
				}
			}
			$('#um-checkout-ship-to-predefined').on('change', toggleCheckoutShipToFields);
			toggleCheckoutShipToFields();

			function toggleNucEmailTemplateField() {
				$('#nuc-email-template-select').toggle($('#nuc_send_email').is(':checked'));
			}
			$('#nuc_send_email').on('change', toggleNucEmailTemplateField);
			toggleNucEmailTemplateField();
			$('#um-preview-nuc-email-btn').on('click', function() {
				if (typeof window.umShowEmailPreview === 'function') {
					window.umShowEmailPreview('nuc');
				}
			});
			$('#um-bulk-coupons-send-email').on('change', function() {
				$('#um-bulk-coupons-template-select').toggle(this.checked);
			});
			$('#um-bulk-coupons-template-select').toggle($('#um-bulk-coupons-send-email').is(':checked'));
			$('.um-bulk-coupons-preview-email-btn').on('click', function() {
				if (typeof window.umShowEmailPreview === 'function') {
					window.umShowEmailPreview('bulk-coupons');
				}
			});
			function toggleBulkCouponsFields() {
				$('#um-bulk-coupons-fields').toggle($('#um-bulk-coupons-enabled').is(':checked'));
			}
			function toggleBulkAddToCartAddonFields() {
				var enabled = $("input[name='bulk_add_to_cart_enabled']").is(':checked');
				$('#um-bulk-add-to-cart-fields').toggle(enabled);
				if (enabled) {
					umToggleBulkMetaFieldRow();
				}
			}
			function togglePostMetaAddonFields() {
				$('#um-post-meta-edit-fields').toggle($('#um-post-meta-enabled').is(':checked'));
			}
			function toggleNewUserCouponAddonFields() {
				var enabled = $('#um-nuc-enabled').is(':checked');
				$('#um-nuc-fields').toggle(enabled);
				if (enabled) {
					toggleNucEmailTemplateField();
				}
			}
			function toggleCouponNotificationsAddonFields() {
				$('#um-coupon-notifications-fields').toggle($('#um-coupon-notifications-enabled').is(':checked'));
			}
			function toggleCouponRemainderAddonFields() {
				$('#um-coupon-remainder-fields').toggle($('#um-coupon-remainder-enabled').is(':checked'));
			}
			function toggleMyAccountCouponScreenFields() {
				$('#um-my-account-coupon-screen-fields').toggle($('#um-my-account-coupon-screen-enabled').is(':checked'));
			}
			$('#um-bulk-coupons-enabled').on('change', toggleBulkCouponsFields);
			toggleBulkCouponsFields();
			toggleBulkAddToCartAddonFields();
			togglePostMetaAddonFields();
			toggleNewUserCouponAddonFields();
			toggleCouponNotificationsAddonFields();
			toggleCouponRemainderAddonFields();
			toggleMyAccountCouponScreenFields();
			$('.um-addon-action-submit').on('click', function() {
				var targetAction = $(this).attr('data-um-target-action') || 'user_manager_save_settings';
				$('#um-addons-form-action').val(targetAction);
			});
			$('button[name="submit"], input[name="submit"]').on('click', function() {
				$('#um-addons-form-action').val('user_manager_save_settings');
			});
			// Fallback for Enter-key submits: default to settings save unless
			// an explicit add-on action button triggered submission.
			$('#um-addons-form-action').closest('form').on('submit', function() {
				var $active = $(document.activeElement);
				if (!$active.hasClass('um-addon-action-submit')) {
					$('#um-addons-form-action').val('user_manager_save_settings');
				}
			});

			function toggleMyAccountAdminViewerField(checkboxSelector, fieldSelector) {
				if ($(checkboxSelector).is(':checked')) {
					$(fieldSelector).show();
				} else {
					$(fieldSelector).hide();
				}
			}

			function toggleMyAccountAdminViewerFields() {
				var addonEnabled = $('#um-my-account-site-admin-enabled').is(':checked');
				$('#um-my-account-site-admin-fields').toggle(addonEnabled);
				if (!addonEnabled) {
					return;
				}
				toggleMyAccountAdminViewerField('#um-my-account-admin-order-viewer-enabled', '#um-my-account-admin-order-viewer-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-order-viewer-enabled', '#um-my-account-admin-order-approver-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-order-viewer-enabled', '#um-my-account-admin-order-default-pending-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-order-viewer-enabled', '#um-my-account-admin-order-meta-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-product-viewer-enabled', '#um-my-account-admin-product-viewer-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-product-viewer-enabled', '#um-my-account-admin-product-meta-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-coupon-viewer-enabled', '#um-my-account-admin-coupon-viewer-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-coupon-viewer-enabled', '#um-my-account-admin-coupon-meta-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-user-viewer-enabled', '#um-my-account-admin-user-viewer-users-field');
				toggleMyAccountAdminViewerField('#um-my-account-admin-user-viewer-enabled', '#um-my-account-admin-user-meta-field');
			}

			function toggleCustomAdminNotificationsFields() {
				$('#um-custom-admin-notifications-fields').toggle($('#um-custom-admin-notifications-enabled').is(':checked'));
			}

			function toggleAdminBarMenuItemsFields() {
				$('#um-admin-bar-menu-items-fields').toggle($('#um-admin-bar-menu-items-enabled').is(':checked'));
			}

			function toggleWpAdminCssFields() {
				$('#um-wp-admin-css-fields').toggle($('#um-wp-admin-css-enabled').is(':checked'));
			}

			function toggleRoleSwitchingFields() {
				$('#um-role-switching-fields').toggle($('#um-role-switching-enabled').is(':checked'));
			}

			$('#um-my-account-site-admin-enabled, #um-my-account-admin-order-viewer-enabled, #um-my-account-admin-product-viewer-enabled, #um-my-account-admin-coupon-viewer-enabled, #um-my-account-admin-user-viewer-enabled').on('change', toggleMyAccountAdminViewerFields);
			toggleMyAccountAdminViewerFields();
			$('#um-my-account-site-admin-enabled, #um-my-account-admin-order-viewer-enabled, #um-my-account-admin-product-viewer-enabled, #um-my-account-admin-coupon-viewer-enabled, #um-my-account-admin-user-viewer-enabled').on('change', function() {
				refreshAddonCardAutoState($('#um-addon-card-my-account'));
			});
			$("input[name='bulk_add_to_cart_enabled']").on('change', function() {
				toggleBulkAddToCartAddonFields();
				refreshAddonCardAutoState($('#um-addon-card-bulk-add-to-cart'));
			});
			$('#um-post-meta-enabled').on('change', function() {
				togglePostMetaAddonFields();
				refreshAddonCardAutoState($('#um-addon-card-post-meta'));
			});
			$('#um-bulk-coupons-enabled').on('change', function() {
				refreshAddonCardAutoState($('#um-addon-card-bulk-coupons'));
			});
			$('#um-checkout-ship-to-predefined').on('change', function() {
				refreshAddonCardAutoState($('#um-addon-card-checkout-predefined'));
			});
			function toggleOpenAiAddonFields() {
				if ($('#um-openai-content-generator-enabled').is(':checked')) {
					$('#um-openai-content-generator-fields').show();
				} else {
					$('#um-openai-content-generator-fields').hide();
				}
			}
			$('#um-openai-content-generator-enabled').on('change', function() {
				toggleOpenAiAddonFields();
				refreshAddonCardAutoState($('#um-addon-card-api'));
			});
			$('#um-openai-page-meta-box').on('change', function() {
				refreshAddonCardAutoState($('#um-addon-card-api'));
			});
			function toggleOpenAiIdeaAddonFields() {
				$('#um-openai-blog-post-idea-generator-fields').toggle($('#um-openai-blog-post-idea-generator-enabled').is(':checked'));
			}
			$('#um-openai-blog-post-idea-generator-enabled').on('change', function() {
				toggleOpenAiIdeaAddonFields();
				refreshAddonCardAutoState($('#um-addon-card-blog-post-idea-generator'));
			});
			toggleOpenAiAddonFields();
			toggleOpenAiIdeaAddonFields();
			$('#um-role-switching-enabled').on('change', function() {
				toggleRoleSwitchingFields();
				refreshAddonCardAutoState($('#um-addon-card-role-switching'));
			});
			$('#um-quick-search-enabled').on('change', function() {
				refreshAddonCardAutoState($('#um-addon-card-quick-search'));
			});
			$('#um-nuc-enabled').on('change', function() {
				toggleNewUserCouponAddonFields();
				refreshAddonCardAutoState($('#um-addon-card-coupons-new-users'));
			});
			$('#um-coupon-notifications-enabled').on('change', function() {
				toggleCouponNotificationsAddonFields();
				refreshAddonCardAutoState($('#um-addon-card-coupon-notifications'));
			});
			$('#um-coupon-remainder-enabled').on('change', function() {
				toggleCouponRemainderAddonFields();
				refreshAddonCardAutoState($('#um-addon-card-coupon-remainder'));
			});
			$('#um-my-account-coupon-screen-enabled').on('change', function() {
				toggleMyAccountCouponScreenFields();
				refreshAddonCardAutoState($('#um-addon-card-my-account-coupon-screen'));
			});
			$('#um-custom-admin-notifications-enabled').on('change', function() {
				toggleCustomAdminNotificationsFields();
				refreshAddonCardAutoState($('#um-addon-card-custom-notifications'));
			});
			$('#um-admin-bar-menu-items-enabled').on('change', function() {
				toggleAdminBarMenuItemsFields();
				refreshAddonCardAutoState($('#um-addon-card-admin-bar-menu'));
			});
			$('#um-wp-admin-css-enabled').on('change', function() {
				toggleWpAdminCssFields();
				refreshAddonCardAutoState($('#um-addon-card-admin-css'));
			});
			toggleCustomAdminNotificationsFields();
			toggleAdminBarMenuItemsFields();
			toggleWpAdminCssFields();
			toggleRoleSwitchingFields();

			$('#um-add-admin-notification').on('click', function() {
				var count = $('#um-custom-admin-notifications-list .um-admin-notification-block').length;
				var tpl = $('#um-admin-notification-template').html().replace(/__INDEX__/g, count);
				$('#um-custom-admin-notifications-list').append(tpl);
				$('#um-custom-admin-notifications-list .um-admin-notification-block').last().find('.um-admin-notification-number').text('<?php echo esc_js(__('Notification', 'user-manager')); ?> ' + (count + 1));
				refreshAddonCardAutoState($('#um-addon-card-custom-notifications'));
			});
			$('#um-custom-admin-notifications-list').on('click', '.um-remove-admin-notification', function() {
				$(this).closest('.um-admin-notification-block').remove();
				$('#um-custom-admin-notifications-list .um-admin-notification-block').each(function(i) {
					var $block = $(this);
					var idx = i;
					$block.find('.um-admin-notification-number').text('<?php echo esc_js(__('Notification', 'user-manager')); ?> ' + (idx + 1));
					$block.find('input, textarea').each(function() {
						var name = $(this).attr('name');
						if (name && name.indexOf('custom_admin_notification[') === 0) {
							$(this).attr('name', name.replace(/custom_admin_notification\[\d+\]/, 'custom_admin_notification[' + idx + ']'));
						}
					});
				});
				refreshAddonCardAutoState($('#um-addon-card-custom-notifications'));
			});

			$('#um-add-admin-bar-menu').on('click', function() {
				var count = $('#um-admin-bar-menu-list .um-admin-bar-menu-block').length;
				var tpl = $('#um-admin-bar-menu-template').html().replace(/__INDEX__/g, count);
				$('#um-admin-bar-menu-list').append(tpl);
				$('#um-admin-bar-menu-list .um-admin-bar-menu-block').last().find('.um-admin-bar-menu-number').text('<?php echo esc_js(__('Menu', 'user-manager')); ?> ' + (count + 1));
				refreshAddonCardAutoState($('#um-addon-card-admin-bar-menu'));
			});
			$('#um-admin-bar-menu-list').on('click', '.um-remove-admin-bar-menu', function() {
				$(this).closest('.um-admin-bar-menu-block').remove();
				$('#um-admin-bar-menu-list .um-admin-bar-menu-block').each(function(i) {
					var $block = $(this);
					var idx = i;
					$block.find('.um-admin-bar-menu-number').text('<?php echo esc_js(__('Menu', 'user-manager')); ?> ' + (idx + 1));
					$block.find('input, textarea, select').each(function() {
						var name = $(this).attr('name');
						if (name && name.indexOf('admin_bar_menu_item[') === 0) {
							$(this).attr('name', name.replace(/admin_bar_menu_item\[\d+\]/, 'admin_bar_menu_item[' + idx + ']'));
						}
					});
				});
				refreshAddonCardAutoState($('#um-addon-card-admin-bar-menu'));
			});
			$('#um-custom-admin-notifications-list').on('input change', 'input, textarea', function() {
				refreshAddonCardAutoState($('#um-addon-card-custom-notifications'));
			});
			$('#um-admin-bar-menu-list').on('input change', 'input, textarea, select', function() {
				refreshAddonCardAutoState($('#um-addon-card-admin-bar-menu'));
			});
			$('#um-wp-admin-css-all, #um-wp-admin-css-users-css').on('input change', function() {
				refreshAddonCardAutoState($('#um-addon-card-admin-css'));
			});
		});
		</script>
		<?php
	}

	/**
	 * Build Add-ons sub-navigation metadata.
	 *
	 * @param array $settings Plugin settings.
	 * @return array<string,array{label:string,description:string,active:bool}>
	 */
	private static function get_addon_sections(array $settings): array {
		$role_switch_settings = get_option('view_website_by_role_settings', []);
		if (!is_array($role_switch_settings)) {
			$role_switch_settings = [];
		}

		$my_account_site_admin_enabled = array_key_exists('my_account_site_admin_enabled', $settings)
			? !empty($settings['my_account_site_admin_enabled'])
			: (
				!empty($settings['my_account_admin_order_viewer_enabled'])
				|| !empty($settings['my_account_admin_product_viewer_enabled'])
				|| !empty($settings['my_account_admin_coupon_viewer_enabled'])
				|| !empty($settings['my_account_admin_user_viewer_enabled'])
			);

		return [
			'add-to-cart-bulk-import' => [
				'label'  => __('Add to Cart Bulk Import', 'user-manager'),
				'description' => __('Upload a product CSV so users can add many items before checkout.', 'user-manager'),
				'active' => !empty($settings['bulk_add_to_cart_enabled']),
			],
			'checkout-pre-defined-addresses' => [
				'label'  => __('Checkout Address Selector', 'user-manager'),
				'description' => __('Offer a checkout selector that pre-fills and controls address details.', 'user-manager'),
				'active' => !empty($settings['checkout_ship_to_predefined_enabled']),
			],
			'coupon-creator' => [
				'label'  => __('Coupon Creator', 'user-manager'),
				'description' => __('Create many coupons at once with templates, email, and usage options.', 'user-manager'),
				'active' => !empty($settings['bulk_coupons_enabled']),
			],
			'coupon-for-new-user' => [
				'label'  => __('New User Coupons', 'user-manager'),
				'description' => __('Automatically issue template coupons to new users and optionally email them.', 'user-manager'),
				'active' => !empty($settings['nuc_enabled']),
			],
			'coupon-notifications-for-users-with-coupons' => [
				'label'  => __('User Coupon Notifications', 'user-manager'),
				'description' => __('Display reminder notices for each eligible user coupon on selected pages.', 'user-manager'),
				'active' => !empty($settings['user_coupon_notifications_enabled']),
			],
			'coupon-remaining-balances' => [
				'label'  => __('User Coupon Remaining Balances', 'user-manager'),
				'description' => __('Create a replacement coupon when a qualifying balance remains after checkout.', 'user-manager'),
				'active' => !empty($settings['coupon_remainder_enabled']),
			],
			'my-account-coupon-screen' => [
				'label'  => __('My Account Coupons Page', 'user-manager'),
				'description' => __('Add a My Account Coupons page that lists eligible coupon notices.', 'user-manager'),
				'active' => !empty($settings['my_account_coupon_screen_enabled']),
			],
			'my-account-site-admin' => [
				'label'  => __('My Account Admin', 'user-manager'),
				'description' => __('Add admin-style Orders, Products, Coupons, and Users tools in My Account.', 'user-manager'),
				'active' => $my_account_site_admin_enabled,
			],
			'post-meta' => [
				'label'  => __('Post Meta Viewer', 'user-manager'),
				'description' => __('Show all post meta keys and values in a dedicated editor box.', 'user-manager'),
				'active' => !empty($settings['display_post_meta_meta_box']),
			],
			'post-content-generator' => [
				'label'  => __('Post Content Generator', 'user-manager'),
				'description' => __('Generate AI post content and import drafts with configurable prompt options.', 'user-manager'),
				'active' => !empty($settings['openai_content_generator_enabled']),
			],
			'post-idea-generator' => [
				'label'  => __('Post Idea Generator', 'user-manager'),
				'description' => __('Generate AI-assisted post ideas based on your existing site content.', 'user-manager'),
				'active' => !empty($settings['openai_blog_post_idea_generator_enabled']),
			],
			'user-role-switching' => [
				'label'  => __('User Role Switching', 'user-manager'),
				'description' => __('Enable front-end role switching controls with profile permission support.', 'user-manager'),
				'active' => !empty($role_switch_settings['enabled']),
			],
			'wp-admin-bar-menu-items' => [
				'label'  => __('WP-Admin Bar Menu Items', 'user-manager'),
				'description' => __('Create custom WP-Admin bar shortcut menus for faster admin navigation.', 'user-manager'),
				'active' => array_key_exists('admin_bar_menu_items_enabled', $settings) ? !empty($settings['admin_bar_menu_items_enabled']) : true,
			],
			'wp-admin-bar-quick-search' => [
				'label'  => __('WP-Admin Bar Quick Search', 'user-manager'),
				'description' => __('Add a WP-Admin quick search panel for posts, orders, and users.', 'user-manager'),
				'active' => array_key_exists('um_quick_search_enabled', $settings) ? !empty($settings['um_quick_search_enabled']) : true,
			],
			'wp-admin-css' => [
				'label'  => __('WP-Admin CSS', 'user-manager'),
				'description' => __('Apply custom CSS in WP-Admin globally, by role, or by user.', 'user-manager'),
				'active' => array_key_exists('wp_admin_css_enabled', $settings) ? !empty($settings['wp_admin_css_enabled']) : true,
			],
			'wp-admin-notifications' => [
				'label'  => __('WP-Admin Notifications', 'user-manager'),
				'description' => __('Display WP-Admin notification banners with optional URL-based targeting rules.', 'user-manager'),
				'active' => array_key_exists('custom_admin_notifications_enabled', $settings) ? !empty($settings['custom_admin_notifications_enabled']) : true,
			],
		];
	}

	/**
	 * Build a sorted map of add-on tags.
	 *
	 * @param array<string,array<string,mixed>> $addon_sections
	 * @return array<string,string>
	 */
	private static function get_addon_tags(array $addon_sections): array {
		$tags = [];
		foreach ($addon_sections as $section_meta) {
			foreach (self::get_addon_section_tags($section_meta) as $tag_key => $tag_label) {
				$tags[$tag_key] = $tag_label;
			}
		}

		asort($tags, SORT_NATURAL | SORT_FLAG_CASE);
		return $tags;
	}

	/**
	 * Master add-on tags and keyword triggers.
	 *
	 * @return array<string,array{label:string,keywords:array<int,string>}>
	 */
	private static function get_master_addon_tags(): array {
		return [
			'cart'       => [
				'label'    => __('Cart', 'user-manager'),
				'keywords' => ['cart'],
			],
			'checkout'   => [
				'label'    => __('Checkout', 'user-manager'),
				'keywords' => ['checkout'],
			],
			'coupon'     => [
				'label'    => __('Coupons', 'user-manager'),
				'keywords' => ['coupon'],
			],
			'my-account' => [
				'label'    => __('My Account', 'user-manager'),
				'keywords' => ['my account', 'my-account'],
			],
			'post'       => [
				'label'    => __('Posts', 'user-manager'),
				'keywords' => ['post'],
			],
			'user'       => [
				'label'    => __('Users', 'user-manager'),
				'keywords' => ['user'],
			],
			'wp-admin'   => [
				'label'    => __('WP-Admin', 'user-manager'),
				'keywords' => ['wp-admin', 'wp admin'],
			],
		];
	}

	/**
	 * Derive add-on tags by checking label/description against master keywords.
	 *
	 * @param array<string,mixed> $section_meta
	 * @return array<string,string>
	 */
	private static function get_addon_section_tags(array $section_meta): array {
		$label = isset($section_meta['label']) ? trim((string) $section_meta['label']) : '';
		$description = isset($section_meta['description']) ? trim((string) $section_meta['description']) : '';
		$search_text = trim($label . ' ' . $description);
		if ($search_text === '') {
			return [];
		}

		$matches = [];
		foreach (self::get_master_addon_tags() as $tag_key => $tag_meta) {
			$tag_label = isset($tag_meta['label']) ? trim((string) $tag_meta['label']) : '';
			$keywords = isset($tag_meta['keywords']) && is_array($tag_meta['keywords']) ? $tag_meta['keywords'] : [];
			if ($tag_label === '' || empty($keywords)) {
				continue;
			}

			foreach ($keywords as $keyword) {
				$keyword = trim((string) $keyword);
				if ($keyword !== '' && stripos($search_text, $keyword) !== false) {
					$matches[$tag_key] = $tag_label;
					break;
				}
			}
		}

		asort($matches, SORT_NATURAL | SORT_FLAG_CASE);
		return $matches;
	}
}

