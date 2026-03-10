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
		$current_addon_section = isset($_GET['addon_section']) ? sanitize_key(wp_unslash($_GET['addon_section'])) : 'all';
		if ($current_addon_section !== 'all' && !isset($addon_sections[$current_addon_section])) {
			$current_addon_section = 'all';
		}
		$addons_base_url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS);
		?>
		<ul class="subsubsub" style="margin: 12px 0 14px;">
			<li>
				<a href="<?php echo esc_url(add_query_arg('addon_section', 'all', $addons_base_url)); ?>" class="<?php echo $current_addon_section === 'all' ? 'current' : ''; ?>">
					<?php esc_html_e('All Add-ons', 'user-manager'); ?>
				</a>
				<?php if (!empty($addon_sections)) : ?> |<?php endif; ?>
			</li>
			<?php $addon_total = count($addon_sections); $addon_index = 0; ?>
			<?php foreach ($addon_sections as $section_key => $section_meta) : $addon_index++; ?>
				<li>
					<a href="<?php echo esc_url(add_query_arg('addon_section', $section_key, $addons_base_url)); ?>" class="<?php echo $current_addon_section === $section_key ? 'current' : ''; ?>">
						<?php if (!empty($section_meta['active'])) : ?><strong><?php endif; ?>
						<?php echo esc_html((string) $section_meta['label']); ?>
						<?php if (!empty($section_meta['active'])) : ?></strong><?php endif; ?>
					</a><?php echo $addon_index < $addon_total ? ' |' : ''; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="<?php echo esc_attr($settings_form_id); ?>">
			<input type="hidden" name="action" id="um-addons-form-action" value="user_manager_save_settings" />
			<input type="hidden" name="settings_section" value="addons" />
			<input type="hidden" name="addon_section" value="<?php echo esc_attr($current_addon_section); ?>" />
			<?php wp_nonce_field('user_manager_save_settings'); ?>
			<div class="um-admin-grid um-admin-grid-single">
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
		<div class="um-admin-grid um-admin-grid-single">
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
			<div class="um-admin-card um-admin-card-full">
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
			background: #edfaef;
			border-color: #7ad07f;
			color: #137333;
		}
		.um-addon-collapsible.um-addon-active .um-addon-active-dot {
			background: #2ea043;
			box-shadow: 0 0 0 2px rgba(46, 160, 67, 0.25);
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
				if (!currentAddonSection || currentAddonSection === 'all') {
					$('.um-addon-section').show();
					return;
				}
				$('.um-addon-section').hide();
				$('.um-addon-section[data-addon-section="' + currentAddonSection + '"]').show();
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

					// Keep cards collapsed by default in "all" view,
					// but auto-expand when user is focused on a single add-on section.
					setAddonCardCollapsed($card, currentAddonSection === 'all', true);
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
	 * @return array<string,array{label:string,active:bool}>
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
				'active' => !empty($settings['bulk_add_to_cart_enabled']),
			],
			'checkout-pre-defined-addresses' => [
				'label'  => __('Checkout Pre-Defined Addresses', 'user-manager'),
				'active' => !empty($settings['checkout_ship_to_predefined_enabled']),
			],
			'coupon-creator' => [
				'label'  => __('Coupon Creator', 'user-manager'),
				'active' => !empty($settings['bulk_coupons_enabled']),
			],
			'coupon-for-new-user' => [
				'label'  => __('Coupon for New User', 'user-manager'),
				'active' => !empty($settings['nuc_enabled']),
			],
			'coupon-notifications-for-users-with-coupons' => [
				'label'  => __('Coupon Notifications for Users with Coupons', 'user-manager'),
				'active' => !empty($settings['user_coupon_notifications_enabled']),
			],
			'coupon-remaining-balances' => [
				'label'  => __('Coupon Remaining Balances (Simple Gift Card & Store Credit Functionality)', 'user-manager'),
				'active' => !empty($settings['coupon_remainder_enabled']),
			],
			'my-account-coupon-screen' => [
				'label'  => __('My Account Coupon Screen', 'user-manager'),
				'active' => !empty($settings['my_account_coupon_screen_enabled']),
			],
			'my-account-site-admin' => [
				'label'  => __('My Account Site Admin', 'user-manager'),
				'active' => $my_account_site_admin_enabled,
			],
			'post-content-generator' => [
				'label'  => __('Post Content Generator', 'user-manager'),
				'active' => !empty($settings['openai_content_generator_enabled']),
			],
			'post-idea-generator' => [
				'label'  => __('Post Idea Generator', 'user-manager'),
				'active' => !empty($settings['openai_blog_post_idea_generator_enabled']),
			],
			'user-role-switching' => [
				'label'  => __('User Role Switching', 'user-manager'),
				'active' => !empty($role_switch_settings['enabled']),
			],
			'wp-admin-bar-menu-items' => [
				'label'  => __('WP-Admin Bar Menu Items', 'user-manager'),
				'active' => array_key_exists('admin_bar_menu_items_enabled', $settings) ? !empty($settings['admin_bar_menu_items_enabled']) : true,
			],
			'wp-admin-bar-quick-search' => [
				'label'  => __('WP-Admin Bar Quick Search', 'user-manager'),
				'active' => array_key_exists('um_quick_search_enabled', $settings) ? !empty($settings['um_quick_search_enabled']) : true,
			],
			'wp-admin-css' => [
				'label'  => __('WP-Admin CSS', 'user-manager'),
				'active' => array_key_exists('wp_admin_css_enabled', $settings) ? !empty($settings['wp_admin_css_enabled']) : true,
			],
			'wp-admin-notifications' => [
				'label'  => __('WP-Admin Notifications', 'user-manager'),
				'active' => array_key_exists('custom_admin_notifications_enabled', $settings) ? !empty($settings['custom_admin_notifications_enabled']) : true,
			],
		];
	}
}

