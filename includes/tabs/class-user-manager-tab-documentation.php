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
		$valid_sections = ['documentation', 'installation', 'about', 'troubleshooting', 'support', 'versions'];

		// Backward compatibility: legacy Versions tab now lives under Docs sub links.
		if ($docs_section === '' && $requested_tab === User_Manager_Core::TAB_VERSIONS) {
			$docs_section = 'versions';
		}
		if (!in_array($docs_section, $valid_sections, true)) {
			$docs_section = 'documentation';
		}

		$documentation_url = add_query_arg('docs_section', 'documentation', $base_url);
		$installation_url = add_query_arg('docs_section', 'installation', $base_url);
		$about_url = add_query_arg('docs_section', 'about', $base_url);
		$troubleshooting_url = add_query_arg('docs_section', 'troubleshooting', $base_url);
		$support_url = add_query_arg('docs_section', 'support', $base_url);
		$versions_url = add_query_arg('docs_section', 'versions', $base_url);

		?>
		<ul class="subsubsub" style="margin: 12px 0 14px;">
			<li>
				<a href="<?php echo esc_url($documentation_url); ?>" class="<?php echo $docs_section === 'documentation' ? 'current' : ''; ?>">
					<?php esc_html_e('Documentation', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($installation_url); ?>" class="<?php echo $docs_section === 'installation' ? 'current' : ''; ?>">
					<?php esc_html_e('Installation', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($about_url); ?>" class="<?php echo $docs_section === 'about' ? 'current' : ''; ?>">
					<?php esc_html_e('About', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($troubleshooting_url); ?>" class="<?php echo $docs_section === 'troubleshooting' ? 'current' : ''; ?>">
					<?php esc_html_e('Troubleshooting', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($versions_url); ?>" class="<?php echo $docs_section === 'versions' ? 'current' : ''; ?>">
					<?php esc_html_e('Versions', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($support_url); ?>" class="<?php echo $docs_section === 'support' ? 'current' : ''; ?>">
					<?php esc_html_e('Support', 'user-manager'); ?>
				</a>
			</li>
		</ul>
		<br class="clear" />
		<?php

		if ($docs_section === 'versions') {
			User_Manager_Tab_Versions::render();
			return;
		}
		if ($docs_section === 'installation') {
			self::render_installation_section();
			return;
		}
		if ($docs_section === 'about') {
			self::render_about_section();
			return;
		}
		if ($docs_section === 'troubleshooting') {
			self::render_troubleshooting_section();
			return;
		}
		if ($docs_section === 'support') {
			self::render_support_section();
			return;
		}

		$tab_cards = [
			[
				'icon'    => 'dashicons-admin-users',
				'title'   => __('Login Tools Tab', 'user-manager'),
				'summary' => __('Run day-to-day user lifecycle workflows from one area, including Create User, Bulk Create, Reset Password, Remove User, Deactivate User(s), and Login As.', 'user-manager'),
				'details' => [
					__('Designed for support, onboarding, and account-maintenance teams managing many users quickly.', 'user-manager'),
					__('Keeps user operations grouped under one top-level tab with section-based navigation.', 'user-manager'),
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
				'icon'    => 'dashicons-layout',
				'title'   => __('Blocks Tab', 'user-manager'),
				'summary' => __('Manage content-focused block modules and gallery block defaults from a dedicated area separate from operational add-ons.', 'user-manager'),
				'details' => [
					__('Includes block features such as Subpages Grid, Tabbed Content Area, Simple Icons, Menu Tiles, and Dynamic Photo Gallery with Media Library Tags.', 'user-manager'),
					__('Useful for content teams building page layouts, visual navigation, and media-rich sections.', 'user-manager'),
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
			[
				'icon'    => 'dashicons-admin-tools',
				'title'   => __('Troubleshooting Section', 'user-manager'),
				'summary' => __('Generate temporary URL overrides to disable all or selected add-ons while diagnosing conflicts or regressions.', 'user-manager'),
				'details' => [
					__('Includes direct parameter examples and a checkbox URL builder for single/multi add-on isolation tests.', 'user-manager'),
					__('Overrides are temporary and only apply while the URL parameters are present.', 'user-manager'),
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
				'icon'    => 'dashicons-cart',
				'title'   => __('Add to Cart Min/Max Quantities', 'user-manager'),
				'summary' => __('Set product-level minimum/maximum quantity rules and enforce them during add-to-cart and cart updates.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-cart',
				'title'   => __('Cart Price Per-Piece', 'user-manager'),
				'summary' => __('Display per-piece unit pricing under line subtotals for multi-quantity items across cart, checkout, and order views.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-cart',
				'title'   => __('Cart Total Items', 'user-manager'),
				'summary' => __('Display a centered total item count above and/or below cart and checkout review tables with configurable copy.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-admin-page',
				'title'   => __('Page Creator', 'user-manager'),
				'summary' => __('Generate multiple AI-written pages from Title|Prompt rows using your saved OpenAI API key, with optional image downloads and history tracking.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-database',
				'title'   => __('Database Table Browser', 'user-manager'),
				'summary' => __('Browse database tables, inspect column structure, and view paginated row data directly in WP-Admin for debugging and support workflows.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-rss',
				'title'   => __('Webhook URLs', 'user-manager'),
				'summary' => __('Expose configurable webhook endpoints for order/coupon workflows, password resets, and email sending with optional URL param support and debug output.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-media-spreadsheet',
				'title'   => __('Order Invoice & Approval', 'user-manager'),
				'summary' => __('Render customer-facing invoice links/pages for WooCommerce orders with approval forms, PDF output, payment links, and approval access by email or user-profile checkbox.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-format-status',
				'title'   => __('Order Received Page Customizer', 'user-manager'),
				'summary' => __('Override the Order Received page heading and success paragraph message shown after checkout.', 'user-manager'),
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
				'icon'    => 'dashicons-shield',
				'title'   => __('Data Anonymizer', 'user-manager'),
				'summary' => __('Apply configurable anonymization rules to user/order/form data for privacy workflows, testing environments, and compliance support.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-admin-site',
				'title'   => __('Staging & Development Environment Overrides', 'user-manager'),
				'summary' => __('Apply non-production safety controls such as email/payment/webhook/API blocking and visible environment warning notices.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-shield',
				'title'   => __('Security Hardening', 'user-manager'),
				'summary' => __('Enable optional hardening controls for REST user endpoints, admin file modification restrictions, SSL admin enforcement, and version output reduction.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-email',
				'title'   => __('Send Email', 'user-manager'),
				'summary' => __('Activate email-sending workflows used by Email Users and related campaigns with reusable template integration.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-format-chat',
				'title'   => __('Send SMS Text', 'user-manager'),
				'summary' => __('Activate SMS messaging workflows for role/list-based communication from WP-Admin.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-tickets',
				'title'   => __('My Account Coupons Page', 'user-manager'),
				'summary' => __('Add a dedicated My Account coupons endpoint so customers can view available coupon notices in one place.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-grid-view',
				'title'   => __('My Account Menu Tiles', 'user-manager'),
				'summary' => __('Display My Account menu items as responsive dashboard tile buttons with configurable columns and tile height.', 'user-manager'),
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
				'icon'    => 'dashicons-search',
				'title'   => __('Product Search by SKU', 'user-manager'),
				'summary' => __('When active, front-end WooCommerce search terms that exactly match a product or variation SKU redirect directly to that product page.', 'user-manager'),
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
				'icon'    => 'dashicons-tag',
				'title'   => __('Plugin Tags & Notes', 'user-manager'),
				'summary' => __('Add private per-plugin tags and notes on the Plugins screen, with inline editing and tag-based filtering for faster plugin stack organization.', 'user-manager'),
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

		$block_cards = [
			[
				'icon'    => 'dashicons-screenoptions',
				'title'   => __('Subpages Grid', 'user-manager'),
				'summary' => __('Display child pages as a visual tile grid using block or shortcode output for directory-style navigation.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-index-card',
				'title'   => __('Tabbed Content Area', 'user-manager'),
				'summary' => __('Build tabbed content layouts by sourcing each panel from selected pages/posts for reusable content architecture.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-star-filled',
				'title'   => __('Simple Icons', 'user-manager'),
				'summary' => __('Insert configurable icon callouts for features, links, and quick visual messaging in block-based content.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-grid-view',
				'title'   => __('Menu Tiles', 'user-manager'),
				'summary' => __('Render WordPress menu items as tile buttons for dashboard-style or portal-style content navigation.', 'user-manager'),
			],
			[
				'icon'    => 'dashicons-format-gallery',
				'title'   => __('Dynamic Photo Gallery with Media Library Tags', 'user-manager'),
				'summary' => __('Manage Library Tags taxonomy + media tagging tools and power dynamic front-end galleries with block defaults, styles, URL overrides, and filtering options.', 'user-manager'),
			],
		];

		$addon_card_count = count($addon_cards);
		$block_card_count = count($block_cards);

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
				'title'       => __('Harden Common WordPress Exposure Points', 'user-manager'),
				'description' => __('Use the Security Hardening add-on to reduce public endpoint exposure and tighten WP-Admin file-change controls while documenting any operational tradeoffs.', 'user-manager'),
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

		$general_reports = [
			__('Coupons Audit', 'user-manager'),
			__('Coupons Unused', 'user-manager'),
			__('Coupons Used', 'user-manager'),
			__('Coupons with Email Addresses', 'user-manager'),
			__('Coupons with Free Shipping', 'user-manager'),
			__('Coupons with No Expiration', 'user-manager'),
			__('Coupons with Remaining Balances', 'user-manager'),
			__('Order Notes', 'user-manager'),
			__('Order Payment Methods', 'user-manager'),
			__('Order Refunds', 'user-manager'),
			__('Order Sales vs Coupon Usage', 'user-manager'),
			__('Order Total Shipments by Day', 'user-manager'),
			__('Order Total Shipments by Month', 'user-manager'),
			__('Order Total Shipments by Week', 'user-manager'),
			__('Order Tracking Number Notes', 'user-manager'),
			__('Order Tracking Numbers', 'user-manager'),
			__('Orders Processing by Number of Days', 'user-manager'),
			__('Orders Still Processing but have a Tracking Number', 'user-manager'),
			__('Orders with $0 Total', 'user-manager'),
			__('Orders with Free Shipping', 'user-manager'),
			__('Page Category Archives Views', 'user-manager'),
			__('Page Not Found 404 Errors', 'user-manager'),
			__('Page Views', 'user-manager'),
			__('Post Category Archives Views', 'user-manager'),
			__('Post Meta Field Names (Unique List)', 'user-manager'),
			__('Post Tag Archives Views', 'user-manager'),
			__('Post Views', 'user-manager'),
			__('Product Category Archives Views', 'user-manager'),
			__('Product Category Purchases', 'user-manager'),
			__('Product Purchases', 'user-manager'),
			__('Product Tag Archives Views', 'user-manager'),
			__('Product Tag Purchases', 'user-manager'),
			__('Product Views', 'user-manager'),
			__('Search Queries', 'user-manager'),
			__('User Coupon Usage', 'user-manager'),
			__('User Data', 'user-manager'),
			__('User Logins', 'user-manager'),
			__('User Password Changes', 'user-manager'),
			__('User Password Resets', 'user-manager'),
			__('User Total Sales', 'user-manager'),
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
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d is the number of add-on cards listed below. */
								__('Add-ons are optional operational feature modules you can activate as needed. This section currently documents %d add-ons.', 'user-manager'),
								(int) $addon_card_count
							)
						);
						?>
					</p>
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
					<span class="dashicons dashicons-layout"></span>
					<h2><?php esc_html_e('Blocks Reference', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d is the number of block cards listed below. */
								__('Blocks are content-focused modules managed from the Blocks tab. This section currently documents %d block modules.', 'user-manager'),
								(int) $block_card_count
							)
						);
						?>
					</p>
				</div>
			</div>

			<?php foreach ($block_cards as $block_card) : ?>
				<div class="um-admin-card um-docs-filter-card">
					<div class="um-admin-card-header">
						<span class="dashicons <?php echo esc_attr($block_card['icon']); ?>"></span>
						<h2><?php echo esc_html($block_card['title']); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p><?php echo esc_html($block_card['summary']); ?></p>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="um-admin-card um-docs-filter-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-chart-bar"></span>
					<h2><?php esc_html_e('All Reports (Reports Tab Reference)', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('This is the complete report inventory currently available from the Reports tab.', 'user-manager'); ?></p>
					<div class="um-doc-section">
						<h3><?php esc_html_e('Reports Sections', 'user-manager'); ?></h3>
						<ul>
							<li><?php esc_html_e('General Reports', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Activity', 'user-manager'); ?></li>
							<li><?php esc_html_e('Admin Log', 'user-manager'); ?></li>
							<li><?php esc_html_e('Coupon Lookup by Email', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-doc-section">
						<h3><?php esc_html_e('General Reports List', 'user-manager'); ?></h3>
						<ul>
							<?php foreach ($general_reports as $report_label) : ?>
								<li><?php echo esc_html($report_label); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

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

	/**
	 * Render Installation subsection.
	 */
	private static function render_installation_section(): void {
		?>
		<div class="um-admin-grid um-admin-grid-single">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-admin-tools"></span>
					<h2><?php esc_html_e('Installation', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Activate User Experience Manager from the WordPress Plugins screen, then open User Manager in wp-admin to begin configuring users, emails, settings, add-ons, and blocks.', 'user-manager'); ?></p>
					<ol>
						<li><?php esc_html_e('Activate the plugin under Plugins > Installed Plugins.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Open User Manager and configure core user workflows (Create User, Bulk Create, Reset Password, Remove User).', 'user-manager'); ?></li>
						<li><?php esc_html_e('Set up email templates and email-sending behavior for each user workflow.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Review Settings for global defaults and operational preferences.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Explore Add-ons for operational modules and Blocks for content modules; activate only what your site needs.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Use Reports > Admin Log to verify setup actions and ongoing activity.', 'user-manager'); ?></li>
					</ol>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render About subsection.
	 */
	private static function render_about_section(): void {
		$screenshot_urls = self::get_about_screenshot_urls();
		$tags = [
			'woocommerce',
			'user management',
			'user experience',
			'b2b ecommerce',
			'b2c ecommerce',
			'customer accounts',
			'admin workflow',
			'email automation',
			'coupon automation',
			'my account tools',
			'order approvals',
			'activity logging',
			'wordpress plugin',
			'store operations',
			'support tooling',
		];
		?>
		<div class="um-admin-grid um-admin-grid-single">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-info-outline"></span>
					<h2><?php esc_html_e('About User Experience Manager', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<h3><?php esc_html_e('Item Name', 'user-manager'); ?></h3>
					<p><strong><?php esc_html_e('User Experience Manager', 'user-manager'); ?></strong></p>

					<h3><?php esc_html_e('Category', 'user-manager'); ?></h3>
					<p><?php esc_html_e('WordPress → eCommerce → WooCommerce', 'user-manager'); ?></p>

					<h3><?php esc_html_e('Tags', 'user-manager'); ?></h3>
					<p><?php echo esc_html(implode(', ', $tags)); ?></p>

					<h3><?php esc_html_e('Short Description', 'user-manager'); ?></h3>
					<p><?php esc_html_e('User Experience Manager is an all-in-one operational toolkit for improving both admin workflows and customer account experiences in WooCommerce-powered stores. It centralizes user management, email workflows, reporting, modular add-ons, and content-focused block modules in one scalable interface.', 'user-manager'); ?></p>

					<h3><?php esc_html_e('Long Description (HTML Supported)', 'user-manager'); ?></h3>
					<div>
						<p><?php esc_html_e('User Experience Manager was built to replace fragmented, plugin-by-plugin admin workflows with one centralized operations layer for WordPress and WooCommerce teams. It combines user lifecycle tooling, communication templates, reporting visibility, modular UX add-ons, and dedicated content blocks so support, operations, and growth teams can work from one interface.', 'user-manager'); ?></p>
						<p><?php esc_html_e('At the core level, the plugin includes account creation and import workflows, password operations, role and access controls, email communication utilities, and reporting surfaces designed for both day-to-day execution and long-term operational visibility. Teams can onboard users faster, reduce repeated manual admin tasks, and standardize customer/account experiences across storefronts.', 'user-manager'); ?></p>
						<p><?php esc_html_e('On top of core tooling, User Experience Manager includes an extensive add-on catalog and a dedicated Blocks catalog that can be activated feature-by-feature. This allows you to turn on only what your store needs—from cart/checkout enhancements and coupon automation to My Account admin experiences, content-generation workflows, dynamic photo galleries, and WP-Admin UX customization—without forcing unnecessary complexity into every environment.', 'user-manager'); ?></p>
						<p><?php esc_html_e('The result is a scalable in-house operations platform for agencies, internal ecommerce teams, and multi-store organizations that need flexible UX controls, faster support resolution, and cleaner admin execution across thousands of products, users, orders, and customer journeys.', 'user-manager'); ?></p>
					</div>

					<h3><?php esc_html_e('Feature List', 'user-manager'); ?></h3>
					<h4><?php esc_html_e('Core Tabs and Platform Features', 'user-manager'); ?></h4>
					<ul>
						<li><?php esc_html_e('Login Tools: includes Create User, Bulk Create, Reset Password, Remove User, Deactivate User(s), and Login As in one sectioned workspace.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Email Users: send targeted admin-generated communication using reusable templates.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Reports: includes General Reports, User Activity, Admin Log, and Coupon Lookup by Email.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Settings: global defaults, workflow controls, API keys, template utilities, and system behavior configuration.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Blocks: dedicated content module management for Subpages Grid, Tabbed Content Area, Simple Icons, Menu Tiles, and Dynamic Photo Gallery with Media Library Tags.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Documentation: internal reference, onboarding, installation, support, marketing/about content, and versions/changelog access.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Admin logging and diagnostics: operational event logging with detailed row-level drill-down and filter controls.', 'user-manager'); ?></li>
					</ul>
					<h4><?php esc_html_e('Add-on Catalog (All Current Add-ons)', 'user-manager'); ?></h4>
					<ul>
						<li><?php esc_html_e('Add to Cart Bulk Import', 'user-manager'); ?></li>
						<li><?php esc_html_e('Add to Cart Variation Table', 'user-manager'); ?></li>
						<li><?php esc_html_e('Cart Price Per-Piece', 'user-manager'); ?></li>
						<li><?php esc_html_e('Cart Total Items', 'user-manager'); ?></li>
						<li><?php esc_html_e('Page Creator', 'user-manager'); ?></li>
						<li><?php esc_html_e('Database Table Browser', 'user-manager'); ?></li>
						<li><?php esc_html_e('Webhook URLs', 'user-manager'); ?></li>
						<li><?php esc_html_e('Order Invoice & Approval', 'user-manager'); ?></li>
						<li><?php esc_html_e('Order Received Page Customizer', 'user-manager'); ?></li>
						<li><?php esc_html_e('Checkout Address Selector', 'user-manager'); ?></li>
						<li><?php esc_html_e('Coupon Creator', 'user-manager'); ?></li>
						<li><?php esc_html_e('New User Coupons', 'user-manager'); ?></li>
						<li><?php esc_html_e('User Coupon Notifications', 'user-manager'); ?></li>
						<li><?php esc_html_e('User Coupon Remaining Balances', 'user-manager'); ?></li>
						<li><?php esc_html_e('Fatal Error Debugger', 'user-manager'); ?></li>
						<li><?php esc_html_e('Security Hardening', 'user-manager'); ?></li>
						<li><?php esc_html_e('My Account Coupons Page', 'user-manager'); ?></li>
						<li><?php esc_html_e('My Account Menu Tiles', 'user-manager'); ?></li>
						<li><?php esc_html_e('My Account Admin', 'user-manager'); ?></li>
						<li><?php esc_html_e('Post Meta Viewer', 'user-manager'); ?></li>
						<li><?php esc_html_e('Product Search by SKU', 'user-manager'); ?></li>
						<li><?php esc_html_e('Post Content Generator', 'user-manager'); ?></li>
						<li><?php esc_html_e('Post Idea Generator', 'user-manager'); ?></li>
						<li><?php esc_html_e('Plugin Tags & Notes', 'user-manager'); ?></li>
						<li><?php esc_html_e('User Role Switching', 'user-manager'); ?></li>
						<li><?php esc_html_e('WP-Admin Bar Menu Items', 'user-manager'); ?></li>
						<li><?php esc_html_e('WP-Admin Bar Quick Search', 'user-manager'); ?></li>
						<li><?php esc_html_e('WP-Admin CSS', 'user-manager'); ?></li>
						<li><?php esc_html_e('WP-Admin Notifications', 'user-manager'); ?></li>
					</ul>
					<h4><?php esc_html_e('Blocks Catalog (All Current Blocks)', 'user-manager'); ?></h4>
					<ul>
						<li><?php esc_html_e('Subpages Grid', 'user-manager'); ?></li>
						<li><?php esc_html_e('Tabbed Content Area', 'user-manager'); ?></li>
						<li><?php esc_html_e('Simple Icons', 'user-manager'); ?></li>
						<li><?php esc_html_e('Menu Tiles', 'user-manager'); ?></li>
						<li><?php esc_html_e('Dynamic Photo Gallery with Media Library Tags', 'user-manager'); ?></li>
					</ul>

					<h3><?php esc_html_e('Screenshots', 'user-manager'); ?></h3>
					<p><?php esc_html_e('Screenshots automatically load from: /assets/documentation-screenshots/', 'user-manager'); ?></p>
					<?php if (empty($screenshot_urls)) : ?>
						<p><em><?php esc_html_e('No screenshots found yet. Add image files to assets/documentation-screenshots and they will appear automatically here.', 'user-manager'); ?></em></p>
					<?php else : ?>
						<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:12px;">
							<?php foreach ($screenshot_urls as $screenshot_url) : ?>
								<div style="border:1px solid #dcdcde; border-radius:6px; padding:8px; background:#fff;">
									<img src="<?php echo esc_url($screenshot_url); ?>" alt="<?php esc_attr_e('User Experience Manager screenshot', 'user-manager'); ?>" style="max-width:100%; height:auto; display:block;" />
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h3><?php esc_html_e('Use Cases', 'user-manager'); ?></h3>
					<ul>
						<li><?php esc_html_e('B2B account onboarding and role-based user provisioning at scale.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Customer account support workflows with controlled Login As troubleshooting.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Coupon and retention campaign management with reminder/remaining-balance workflows.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Operational visibility for teams managing multiple stores and admin users.', 'user-manager'); ?></li>
					</ul>

					<h3><?php esc_html_e('Requirements', 'user-manager'); ?></h3>
					<p><?php esc_html_e('WordPress is required. WooCommerce is strongly recommended if you are selling online and want to use ecommerce-focused add-ons.', 'user-manager'); ?></p>

					<h3><?php esc_html_e('Support Information', 'user-manager'); ?></h3>
					<p><a href="https://www.griceprojects.com" target="_blank" rel="noopener noreferrer">www.griceprojects.com</a></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Troubleshooting subsection.
	 */
	private static function render_troubleshooting_section(): void {
		$addon_map = User_Manager_Core::get_addon_runtime_toggle_map();
		uasort($addon_map, static function (array $a, array $b): int {
			$a_label = isset($a['label']) ? (string) $a['label'] : '';
			$b_label = isset($b['label']) ? (string) $b['label'] : '';
			return strcasecmp($a_label, $b_label);
		});

		$disable_all_param = User_Manager_Core::URL_PARAM_DISABLE_ALL_ADDONS;
		$disable_addons_param = User_Manager_Core::URL_PARAM_DISABLE_ADDONS;
		$disabled_slugs = User_Manager_Core::get_temporarily_disabled_addons_from_url();
		$raw_disable_all = isset($_GET[$disable_all_param]) ? strtolower(trim(sanitize_text_field(wp_unslash($_GET[$disable_all_param])))) : '';
		$raw_disable_addons = isset($_GET[$disable_addons_param]) ? strtolower(trim(sanitize_text_field(wp_unslash($_GET[$disable_addons_param])))) : '';
		$disable_all_requested = in_array($raw_disable_all, ['1', 'true', 'yes', 'on', 'all'], true) || $raw_disable_addons === 'all';

		$disabled_labels = [];
		foreach ($disabled_slugs as $disabled_slug) {
			if (!isset($addon_map[$disabled_slug]['label'])) {
				continue;
			}
			$disabled_labels[] = (string) $addon_map[$disabled_slug]['label'];
		}

		$default_target_url = home_url('/');
		?>
		<div class="um-admin-grid um-admin-grid-single">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-admin-tools"></span>
					<h2><?php esc_html_e('Troubleshooting', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Use this section to quickly isolate add-on conflicts by temporarily disabling all add-ons (or specific add-ons) using URL parameters.', 'user-manager'); ?></p>
					<ol>
						<li><?php esc_html_e('Test with all add-ons disabled to confirm whether an add-on is involved.', 'user-manager'); ?></li>
						<li><?php esc_html_e('If the issue clears, re-enable add-ons one-by-one (or in small groups) until the issue returns.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Use the Versions section to confirm recent changes and release notes.', 'user-manager'); ?></li>
						<li><?php esc_html_e('Use Reports > Admin Log and other debug add-ons as needed for deeper diagnostics.', 'user-manager'); ?></li>
					</ol>

					<h3><?php esc_html_e('URL Parameters', 'user-manager'); ?></h3>
					<ul>
						<li>
							<code><?php echo esc_html($disable_all_param); ?>=1</code>
							— <?php esc_html_e('Temporarily disable all add-ons for the current request URL.', 'user-manager'); ?>
						</li>
						<li>
							<code><?php echo esc_html($disable_addons_param); ?>=add-to-cart-variation-table</code>
							— <?php esc_html_e('Temporarily disable one specific add-on for the current request URL.', 'user-manager'); ?>
						</li>
						<li>
							<code><?php echo esc_html($disable_addons_param); ?>=add-to-cart-variation-table,cart-total-items</code>
							— <?php esc_html_e('Temporarily disable multiple add-ons using a comma-separated list.', 'user-manager'); ?>
						</li>
					</ul>
					<p class="description">
						<?php esc_html_e('These URL overrides are temporary and only apply while those parameters are present in the URL.', 'user-manager'); ?>
					</p>

					<?php if (!empty($disabled_labels)) : ?>
						<div class="notice notice-warning inline" style="margin:12px 0 0; padding:8px 12px;">
							<p style="margin:0;">
								<strong><?php esc_html_e('Active URL override detected:', 'user-manager'); ?></strong>
								<?php
								if ($disable_all_requested) {
									esc_html_e('All add-ons are currently disabled for this URL.', 'user-manager');
								} else {
									echo esc_html(implode(', ', $disabled_labels));
								}
								?>
							</p>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-admin-links"></span>
					<h2><?php esc_html_e('Troubleshooting URL Builder', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-form-field">
						<label for="um-troubleshooting-target-url"><strong><?php esc_html_e('Target URL', 'user-manager'); ?></strong></label>
						<input type="text" id="um-troubleshooting-target-url" class="large-text" value="<?php echo esc_attr($default_target_url); ?>" placeholder="<?php echo esc_attr($default_target_url); ?>" />
						<p class="description"><?php esc_html_e('Enter the front-end/admin URL you want to test, then choose disable options below.', 'user-manager'); ?></p>
					</div>

					<div class="um-form-field">
						<label>
							<input type="checkbox" id="um-troubleshooting-disable-all" <?php checked($disable_all_requested); ?> />
							<?php esc_html_e('Disable all add-ons for this URL', 'user-manager'); ?>
						</label>
					</div>

					<div class="um-form-field">
						<label><strong><?php esc_html_e('Disable specific add-ons', 'user-manager'); ?></strong></label>
						<div id="um-troubleshooting-addon-list" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:6px; margin-top:8px;">
							<?php foreach ($addon_map as $addon_slug => $addon_meta) : ?>
								<?php
								$addon_label = isset($addon_meta['label']) ? (string) $addon_meta['label'] : $addon_slug;
								$is_checked = in_array($addon_slug, $disabled_slugs, true);
								?>
								<label style="display:flex; gap:8px; align-items:flex-start; padding:6px 8px; border:1px solid #dcdcde; border-radius:4px; background:#fff;">
									<input
										type="checkbox"
										class="um-troubleshooting-addon-checkbox"
										value="<?php echo esc_attr($addon_slug); ?>"
										<?php checked($is_checked); ?>
									/>
									<span>
										<strong><?php echo esc_html($addon_label); ?></strong><br />
										<code style="font-size:11px;"><?php echo esc_html($addon_slug); ?></code>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="um-form-field" style="margin-top:12px;">
						<label for="um-troubleshooting-generated-url"><strong><?php esc_html_e('Generated URL', 'user-manager'); ?></strong></label>
						<input type="text" id="um-troubleshooting-generated-url" class="large-text code" readonly value="" />
						<p style="margin-top:8px;">
							<button type="button" class="button" id="um-troubleshooting-copy-url"><?php esc_html_e('Copy URL', 'user-manager'); ?></button>
						</p>
					</div>
				</div>
			</div>
		</div>
		<script>
		jQuery(document).ready(function($) {
			var disableAllParam = '<?php echo esc_js($disable_all_param); ?>';
			var disableAddonsParam = '<?php echo esc_js($disable_addons_param); ?>';
			var fallbackTarget = '<?php echo esc_js($default_target_url); ?>';

			function buildTroubleshootingUrl() {
				var rawTarget = ($.trim($('#um-troubleshooting-target-url').val()) || fallbackTarget);
				var parsed;
				try {
					parsed = new URL(rawTarget);
				} catch (e) {
					parsed = new URL(rawTarget, window.location.origin);
				}

				parsed.searchParams.delete(disableAllParam);
				parsed.searchParams.delete(disableAddonsParam);

				var disableAll = $('#um-troubleshooting-disable-all').is(':checked');
				if (disableAll) {
					parsed.searchParams.set(disableAllParam, '1');
				} else {
					var selected = [];
					$('.um-troubleshooting-addon-checkbox:checked').each(function() {
						selected.push($(this).val());
					});
					if (selected.length > 0) {
						parsed.searchParams.set(disableAddonsParam, selected.join(','));
					}
				}

				$('#um-troubleshooting-generated-url').val(parsed.toString());
			}

			function toggleAddonCheckboxState() {
				var disableAll = $('#um-troubleshooting-disable-all').is(':checked');
				$('.um-troubleshooting-addon-checkbox').prop('disabled', disableAll);
			}

			$('#um-troubleshooting-target-url').on('input change', buildTroubleshootingUrl);
			$('#um-troubleshooting-disable-all').on('change', function() {
				toggleAddonCheckboxState();
				buildTroubleshootingUrl();
			});
			$('.um-troubleshooting-addon-checkbox').on('change', buildTroubleshootingUrl);
			$('#um-troubleshooting-copy-url').on('click', function() {
				var value = $('#um-troubleshooting-generated-url').val();
				if (!value) {
					return;
				}
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(value);
				} else {
					$('#um-troubleshooting-generated-url').trigger('focus').trigger('select');
					document.execCommand('copy');
				}
			});

			toggleAddonCheckboxState();
			buildTroubleshootingUrl();
		});
		</script>
		<?php
	}

	/**
	 * Render Support subsection.
	 */
	private static function render_support_section(): void {
		?>
		<div class="um-admin-grid um-admin-grid-single">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-sos"></span>
					<h2><?php esc_html_e('Support', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('We are happy to support your implementation and feature requests. User Experience Manager was developed in-house based on requests from hundreds of clients to manage thousands of ecommerce stores and customer/admin experiences in one place, and we would love to support your requests as well.', 'user-manager'); ?></p>
					<p>
						<a class="button button-primary" href="https://simplewebhelp.com/inquiries/?ref=uxm" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e('Contact Support', 'user-manager'); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Auto-load screenshot image URLs from plugin folder.
	 *
	 * @return array<int,string>
	 */
	private static function get_about_screenshot_urls(): array {
		$plugin_root = dirname(__DIR__, 2);
		$screenshots_dir = $plugin_root . '/assets/documentation-screenshots';
		if (!is_dir($screenshots_dir)) {
			return [];
		}

		$patterns = ['*.png', '*.jpg', '*.jpeg', '*.gif', '*.webp', '*.svg'];
		$files = [];
		foreach ($patterns as $pattern) {
			$matches = glob($screenshots_dir . '/' . $pattern);
			if (!empty($matches) && is_array($matches)) {
				$files = array_merge($files, $matches);
			}
		}
		if (empty($files)) {
			return [];
		}

		natcasesort($files);
		$files = array_values(array_unique($files));
		$urls = [];
		foreach ($files as $file_path) {
			if (!is_file($file_path)) {
				continue;
			}
			$basename = basename((string) $file_path);
			$urls[] = plugins_url('assets/documentation-screenshots/' . rawurlencode($basename), $plugin_root . '/user-manager.php');
		}
		return $urls;
	}
}


