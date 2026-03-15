<?php
/**
 * Documentation tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Documentation {

	public static function render(): void {
		$base_url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_DOCUMENTATION);
		$requested_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : User_Manager_Core::TAB_DOCUMENTATION;
		$docs_section = isset($_GET['docs_section']) ? sanitize_key(wp_unslash($_GET['docs_section'])) : '';
		$valid_sections = ['documentation', 'versions'];

		// Backward compatibility: legacy Versions tab now lives under Docs sub links.
		if ($docs_section === '' && $requested_tab === User_Manager_Core::TAB_VERSIONS) {
			$docs_section = 'versions';
		}
		if (!in_array($docs_section, $valid_sections, true)) {
			$docs_section = 'documentation';
		}

		$documentation_url = add_query_arg('docs_section', 'documentation', $base_url);
		$versions_url = add_query_arg('docs_section', 'versions', $base_url);

		?>
		<ul class="subsubsub" style="margin: 12px 0 14px;">
			<li>
				<a href="<?php echo esc_url($documentation_url); ?>" class="<?php echo $docs_section === 'documentation' ? 'current' : ''; ?>">
					<?php esc_html_e('Documentation', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($versions_url); ?>" class="<?php echo $docs_section === 'versions' ? 'current' : ''; ?>">
					<?php esc_html_e('Versions', 'user-manager'); ?>
				</a>
			</li>
		</ul>
		<br class="clear" />
		<?php

		if ($docs_section === 'versions') {
			User_Manager_Tab_Versions::render();
			return;
		}

		$tab_cards = [
			[
				'icon'    => 'dashicons-admin-users',
				'title'   => __('Create User Tab', 'user-manager'),
				'summary' => __('Create one user at a time with full control over role, credentials, login URL, and optional welcome email behavior.', 'user-manager'),
				'details' => [
					__('Great for manual onboarding, support-assisted account setup, and VIP account creation.', 'user-manager'),
					__('Can pair with coupon and email template settings to provide a guided first-login experience.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-upload',
				'title'   => __('Bulk Create Tab', 'user-manager'),
				'summary' => __('Import users in bulk via CSV, paste-from-spreadsheet, or directory-based workflows for high-volume admin operations.', 'user-manager'),
				'details' => [
					__('Useful for B2B launches, distributor account imports, and migration projects.', 'user-manager'),
					__('Supports default column mapping and optional update-existing-user behavior.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-lock',
				'title'   => __('Reset Password Tab', 'user-manager'),
				'summary' => __('Reset passwords in bulk or individually and optionally send secure reset flows with email templates.', 'user-manager'),
				'details' => [
					__('Helpful for account recovery campaigns and post-migration security resets.', 'user-manager'),
					__('Can reduce support time when multiple users need immediate access restoration.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-trash',
				'title'   => __('Remove User Tab', 'user-manager'),
				'summary' => __('Remove user accounts safely when offboarding users, cleaning invalid accounts, or processing privacy requests.', 'user-manager'),
				'details' => [
					__('Useful for lifecycle management and admin cleanup tasks.', 'user-manager'),
					__('Designed to help teams process account removals consistently.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-admin-network',
				'title'   => __('Login As Tab', 'user-manager'),
				'summary' => __('Generate controlled temporary login credentials and links so admins can troubleshoot account-specific issues quickly.', 'user-manager'),
				'details' => [
					__('Ideal for support teams resolving checkout, order history, and permission-related issues.', 'user-manager'),
					__('Works well with admin activity logging for accountability.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-email-alt',
				'title'   => __('Email Users Tab', 'user-manager'),
				'summary' => __('Send targeted user emails with reusable templates for onboarding, reminders, campaigns, and service communications.', 'user-manager'),
				'details' => [
					__('Useful for lifecycle messaging such as welcome, activation, and account-update notices.', 'user-manager'),
					__('Supports consistent branding via WooCommerce-style email presentation.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-chart-bar',
				'title'   => __('Reports Tab', 'user-manager'),
				'summary' => __('Review operational and behavior reports including user activity, admin logs, coupon usage context, and other diagnostics.', 'user-manager'),
				'details' => [
					__('Helpful for troubleshooting, compliance review, and conversion optimization analysis.', 'user-manager'),
					__('Lets teams validate what changed, who changed it, and when.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-admin-settings',
				'title'   => __('Settings Tab', 'user-manager'),
				'summary' => __('Configure global behavior across General Settings, Email Templates, and Tools to control defaults, branding, and operational workflows.', 'user-manager'),
				'details' => [
					__('Central place for environment-level options used by multiple tabs and add-ons.', 'user-manager'),
					__('Includes template management and utility actions that support daily admin operations.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-screenoptions',
				'title'   => __('Add-ons Tab', 'user-manager'),
				'summary' => __('Activate feature modules only when needed so your site stays focused, modular, and easier to maintain.', 'user-manager'),
				'details' => [
					__('Each add-on has its own settings card and activation toggle.', 'user-manager'),
					__('Best for gradually rolling out capabilities by business priority.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-media-document',
				'title'   => __('Documentation Tab', 'user-manager'),
				'summary' => __('Read implementation guidance, current feature references, and practical examples for team onboarding.', 'user-manager'),
				'details' => [
					__('Designed to help admins understand what each tab and add-on does in plain language.', 'user-manager'),
					__('Updated alongside feature growth to reduce training and handoff friction.', 'user-manager'),
				],
			],
			[
				'icon'    => 'dashicons-backup',
				'title'   => __('Versions Section', 'user-manager'),
				'summary' => __('Track release history and feature changes using the built-in changelog under Documentation > Versions.', 'user-manager'),
				'details' => [
					__('Useful for QA checks, deployment validation, and historical reference.', 'user-manager'),
					__('Helps teams quickly confirm when a behavior was added or updated.', 'user-manager'),
				],
			],
		];

		$addon_cards = [
			[
				'icon'    => 'dashicons-cart',
				'title'   => __('Add to Cart Bulk Import', 'user-manager'),
				'summary' => __('Allow bulk cart loading from CSV with mapping, diagnostics, and import history tracking for large order entry workflows.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-screenoptions',
				'title'   => __('Add to Cart Variation Table', 'user-manager'),
				'summary' => __('Add a variable-product quantity table so shoppers can add multiple variations in one action with configurable display controls.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-location-alt',
				'title'   => __('Checkout Address Selector', 'user-manager'),
				'summary' => __('Streamline checkout with predefined address selections and conditional field behavior for faster, cleaner order submission.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-tickets-alt',
				'title'   => __('Coupon Creator', 'user-manager'),
				'summary' => __('Generate large batches of WooCommerce coupons from templates with flexible code options and reusable campaign workflows.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-awards',
				'title'   => __('New User Coupons', 'user-manager'),
				'summary' => __('Automatically issue one-time or rule-based coupon incentives to newly eligible users for stronger onboarding conversion.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-megaphone',
				'title'   => __('User Coupon Notifications', 'user-manager'),
				'summary' => __('Show coupon reminders to logged-in users on selected storefront pages so available offers are easy to discover.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-money-alt',
				'title'   => __('User Coupon Remaining Balances', 'user-manager'),
				'summary' => __('Convert leftover fixed-cart value into new customer-specific balance coupons to preserve value and increase return visits.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-warning',
				'title'   => __('Fatal Error Debugger', 'user-manager'),
				'summary' => __('Capture front-end fatal errors for administrators and optionally send alert emails to speed up production issue response.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-tickets',
				'title'   => __('My Account Coupons Page', 'user-manager'),
				'summary' => __('Add a dedicated My Account coupons endpoint so customers can view available coupon notices in one place.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-admin-site-alt3',
				'title'   => __('My Account Admin', 'user-manager'),
				'summary' => __('Add admin-style Orders, Products, Coupons, and Users viewers inside My Account with configurable access controls.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-editor-code',
				'title'   => __('Post Meta Viewer', 'user-manager'),
				'summary' => __('Display and optionally edit post meta values from the editor to simplify debugging and content data operations.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-edit-page',
				'title'   => __('Post Content Generator', 'user-manager'),
				'summary' => __('Generate and import AI-assisted post content drafts with template-oriented prompts for faster publishing workflows.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-lightbulb',
				'title'   => __('Post Idea Generator', 'user-manager'),
				'summary' => __('Generate AI-assisted topic ideas based on site context to accelerate editorial planning and campaign ideation.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-randomize',
				'title'   => __('User Role Switching', 'user-manager'),
				'summary' => __('Allow approved users to preview the site as alternate roles for UX validation, QA, and permission testing.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-menu-alt',
				'title'   => __('WP-Admin Bar Menu Items', 'user-manager'),
				'summary' => __('Create custom top-bar shortcut menus so admins can jump to key areas faster across wp-admin and front end.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-search',
				'title'   => __('WP-Admin Bar Quick Search', 'user-manager'),
				'summary' => __('Add a top-bar quick search workflow for posts, users, orders, and more to reduce navigation time.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-art',
				'title'   => __('WP-Admin CSS', 'user-manager'),
				'summary' => __('Apply targeted wp-admin CSS globally, by role, or by user to tailor UI visibility and simplify focused workflows.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-bell',
				'title'   => __('WP-Admin Notifications', 'user-manager'),
				'summary' => __('Display configurable admin notices with optional URL targeting to communicate internal instructions and process updates.', 'user-manager'),
			],
		];

		$use_cases = [
			[
				'title'       => __('Welcome New Users with a One-Time Coupon', 'user-manager'),
				'description' => __('Use Create User or Bulk Create with Email Templates, then activate New User Coupons + User Coupon Notifications to automatically reward first-time users and surface offers where they shop.', 'user-manager'),
			],
			[
				'title'       => __('Launch a B2B Portal with Bulk Onboarding', 'user-manager'),
				'description' => __('Use Bulk Create to import account lists, Login As for support verification, and My Account Admin to give designated internal users controlled back-office visibility.', 'user-manager'),
			],
			[
				'title'       => __('Reduce Friction for High-Volume Cart Building', 'user-manager'),
				'description' => __('Use Add to Cart Bulk Import for CSV-driven ordering and Add to Cart Variation Table for matrix-style variable product ordering on single product pages.', 'user-manager'),
			],
			[
				'title'       => __('Run Structured Coupon Campaigns', 'user-manager'),
				'description' => __('Use Coupon Creator for batch generation, User Coupon Notifications for visibility, and User Coupon Remaining Balances to preserve unused value and encourage repeat purchases.', 'user-manager'),
			],
			[
				'title'       => __('Speed Up Support and Issue Resolution', 'user-manager'),
				'description' => __('Use Login As, Reports > Admin Log, and Fatal Error Debugger to reproduce and diagnose account or page issues faster.', 'user-manager'),
			],
			[
				'title'       => __('Create Role-Specific Admin Experiences', 'user-manager'),
				'description' => __('Use WP-Admin CSS, WP-Admin Notifications, and WP-Admin Bar Menu Items to simplify interfaces and highlight the exact tools each team needs.', 'user-manager'),
			],
			[
				'title'       => __('Improve Checkout Consistency', 'user-manager'),
				'description' => __('Use Checkout Address Selector to standardize shipping/address behavior and reduce form mistakes in repeated-order environments.', 'user-manager'),
			],
			[
				'title'       => __('Operate a Content Pipeline Faster', 'user-manager'),
				'description' => __('Use Post Idea Generator to plan topics, Post Content Generator to draft posts, and Post Meta Viewer to inspect or adjust underlying content metadata.', 'user-manager'),
			],
			[
				'title'       => __('Audit Changes and Maintain Accountability', 'user-manager'),
				'description' => __('Use Reports and admin activity logging to see what changed, when it changed, and who made the change across critical workflows.', 'user-manager'),
			],
			[
				'title'       => __('Train New Team Members Quickly', 'user-manager'),
				'description' => __('Use this Documentation tab plus the Versions section to teach feature purpose, rollout order, and change history without external docs.', 'user-manager'),
			],
		];

		?>
		<div class="um-admin-card um-admin-card-full" style="margin-top: 20px;">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-filter"></span>
				<h2><?php esc_html_e('Documentation Filter', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
					<div style="min-width:280px; flex:1;">
						<label for="um-docs-filter-text"><strong><?php esc_html_e('Keyword filter', 'user-manager'); ?></strong></label>
						<input type="text" id="um-docs-filter-text" class="regular-text" style="width:100%; max-width:560px;" placeholder="<?php esc_attr_e('Type to filter documentation by title, details, or use case text...', 'user-manager'); ?>" />
					</div>
					<div>
						<button type="button" class="button" id="um-docs-filter-clear"><?php esc_html_e('Clear Filter', 'user-manager'); ?></button>
					</div>
				</div>
				<p class="description" id="um-docs-filter-empty" style="display:none; margin-top: 10px;">
					<?php esc_html_e('No documentation cards match the current filter.', 'user-manager'); ?>
				</p>
			</div>
		</div>
		<div class="um-admin-grid um-admin-grid-single">
			<div class="um-admin-card um-docs-filter-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-info-outline"></span>
					<h2><?php esc_html_e('Platform Overview', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('User Experience Manager is built to improve both administrator workflows and customer-facing experiences for B2B and B2C ecommerce sites. The documentation below is organized by Tabs, Add-ons, and practical Use Cases so teams can quickly find the right tool for each scenario.', 'user-manager'); ?></p>
				</div>
			</div>

			<div class="um-admin-card um-docs-filter-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-index-card"></span>
					<h2><?php esc_html_e('Tabs Reference', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Each card below maps to a primary tab or documentation section in this plugin.', 'user-manager'); ?></p>
				</div>
			</div>

			<?php foreach ($tab_cards as $tab_card) : ?>
				<div class="um-admin-card um-docs-filter-card">
					<div class="um-admin-card-header">
						<span class="dashicons <?php echo esc_attr($tab_card['icon']); ?>"></span>
						<h2><?php echo esc_html($tab_card['title']); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p><?php echo esc_html($tab_card['summary']); ?></p>
						<?php if (!empty($tab_card['details']) && is_array($tab_card['details'])) : ?>
							<ul>
								<?php foreach ($tab_card['details'] as $detail_line) : ?>
									<li><?php echo esc_html((string) $detail_line); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="um-admin-card um-docs-filter-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-screenoptions"></span>
					<h2><?php esc_html_e('Add-ons Reference', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Add-ons are optional feature modules you can activate as needed. These cards explain what each add-on is designed to do.', 'user-manager'); ?></p>
				</div>
			</div>

			<?php foreach ($addon_cards as $addon_card) : ?>
				<div class="um-admin-card um-docs-filter-card">
					<div class="um-admin-card-header">
						<span class="dashicons <?php echo esc_attr($addon_card['icon']); ?>"></span>
						<h2><?php echo esc_html($addon_card['title']); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p><?php echo esc_html($addon_card['summary']); ?></p>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="um-admin-card um-docs-filter-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-lightbulb"></span>
					<h2><?php esc_html_e('Use Cases', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Real-world examples of how different tabs and add-ons can be combined to improve user experience and admin efficiency.', 'user-manager'); ?></p>
					<div class="um-doc-section">
						<ul>
							<?php foreach ($use_cases as $use_case) : ?>
								<li>
									<strong><?php echo esc_html($use_case['title']); ?></strong> — <?php echo esc_html($use_case['description']); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<script>
		jQuery(document).ready(function($) {
			function normalizeDocsFilterText(str) {
				return (str || '').toString().toLowerCase().trim();
			}

			function docsCardHaystack($card) {
				var text = [];
				text.push($card.text());
				$card.find('input, select, textarea').each(function() {
					var $input = $(this);
					text.push($input.val());
					text.push($input.attr('placeholder'));
					text.push($input.attr('name'));
				});
				return normalizeDocsFilterText(text.join(' '));
			}

			function applyDocumentationFilter() {
				var keyword = normalizeDocsFilterText($('#um-docs-filter-text').val());
				var anyVisible = false;
				$('.um-docs-filter-card').each(function() {
					var $card = $(this);
					var matched = keyword === '' || docsCardHaystack($card).indexOf(keyword) !== -1;
					$card.toggle(matched);
					if (matched) {
						anyVisible = true;
					}
				});
				$('#um-docs-filter-empty').toggle(keyword !== '' && !anyVisible);
			}

			$('#um-docs-filter-text').on('input', applyDocumentationFilter);
			$('#um-docs-filter-clear').on('click', function() {
				$('#um-docs-filter-text').val('');
				applyDocumentationFilter();
			});
			applyDocumentationFilter();
		});
		</script>
		<?php
	}
}


