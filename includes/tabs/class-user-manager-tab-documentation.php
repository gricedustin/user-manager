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

		?>
		<div class="um-admin-grid">
			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-info-outline"></span>
					<h2><?php esc_html_e('Overview', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-doc-section">
						<p><?php esc_html_e('User Manager provides comprehensive tools for creating and managing WordPress users, including single user creation, bulk imports, password resets, per-login history, and customizable email templates with WooCommerce integration.', 'user-manager'); ?></p>
					</div>
					<div class="um-doc-section">
						<h3><?php esc_html_e('Key Features', 'user-manager'); ?></h3>
						<div class="um-feature-list">
							<div class="um-feature-item">
								<span class="dashicons dashicons-admin-users"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Create Users', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Create single users with custom roles, passwords, and optional welcome emails. View recently created users in a sidebar.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-upload"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Bulk Import', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Import multiple users via CSV upload, paste directly from Excel/Google Sheets, or SFTP directory. Supports custom user meta columns: any extra columns beyond standard fields are saved as user meta.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-lock"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Password Reset', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Reset user passwords or send password reset links for users to set their own.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-clock"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Login History', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Per-login tracking stored in a dedicated table with pagination, “time ago” display, and links to edit the user.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-email-alt"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Email Users', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Send bulk emails to existing users using customizable templates.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-email"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Email Templates', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Create multiple templates with WooCommerce styling, live preview, and reordering (Move Up/Down). Template descriptions are shown beneath selectors when chosen.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-tickets-alt"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('New User Coupons & Notifications', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Automatically clone coupon templates per user as they log in and surface assigned coupons in on-site notices, complete with include/exclude email filters, date gates, and a live debug overlay.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-list-view"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Activity Log', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Centralized admin activity log with pagination and filters that records user creation/updates, password resets, wp-admin logins, post creation/edits/status changes, and plugin activation/deactivation with detailed before/after snapshots.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-admin-site-alt3"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Role Switching', 'user-manager'); ?></h4>
									<p><?php esc_html_e('A dedicated Role Switching tab plus a front-end switcher bar let approved users preview the site as other roles, with permissions and history changes logged into the Admin Activity Log.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-id-alt"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Login As', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Securely generate temporary passwords, view copy-on-click login credentials and SSO bypass URLs, and restore original passwords later, with all Login As actions recorded in a Recent Login As History panel and the Admin Activity Log.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-tickets-alt"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Bulk Coupons', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Clone an existing WooCommerce coupon into many unique codes using either a fixed count or per-email mode, with support for amount and expiration overrides, custom code prefix/suffix, random-length codes, and full logging plus on-screen summaries.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-chart-bar"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Reports', 'user-manager'); ?></h4>
									<p><?php esc_html_e('A growing suite of reports for logins, coupon usage, unused coupons, users who used coupons, shipment summaries, tracking numbers, views, searches, post meta field names (unique list), and more, all with pagination and CSV export support.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-art"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('WP-Admin CSS', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Settings → User Experience: apply custom CSS only in the WordPress admin. Target all roles (with optional exclusions), specific users by login/email/ID, or individual roles. Roles are loaded from your site.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-editor-code"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Post Meta (View & Edit)', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Optional meta box on every post type edit screen that lists all post meta keys and values. When editing is enabled, you can change values and add new custom fields from the same table.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-update"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Update Existing Users', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Optionally update existing users instead of skipping them during import.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-admin-tools"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Tools', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Run utility tasks such as importing demo and automated coupon email templates, clearing logs and view data, and using the Coupon Lookup by Email tool to inspect coupons tied to a specific address.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-admin-settings"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Rebranded Lost Password UX', 'user-manager'); ?></h4>
									<p><?php esc_html_e('Optional setting to rebrand “reset password” to “set password” for new users and remove username greeting in password-changed emails.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-search"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Quick Search Bar', 'user-manager'); ?></h4>
									<p><?php esc_html_e('An optional admin bar search icon that opens a dropdown of search fields for active post types, users, and terms, with smart single-result redirects into the right edit screens.', 'user-manager'); ?></p>
								</div>
							</div>
							<div class="um-feature-item">
								<span class="dashicons dashicons-cart"></span>
								<div class="um-feature-item-content">
									<h4><?php esc_html_e('Bulk Add to Cart', 'user-manager'); ?></h4>
									<p><?php esc_html_e('WooCommerce-focused bulk add-to-cart tooling powered by a shortcode and CSV uploads, now managed from the Settings tab under “Activate Bulk Add to Cart Functionality”.', 'user-manager'); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-email-alt"></span>
					<h2><?php esc_html_e('Email System', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-doc-section">
						<h3><?php esc_html_e('No Emails by Default', 'user-manager'); ?></h3>
						<p><?php esc_html_e('By default, NO emails are sent when creating users or resetting passwords. You must explicitly check the "Send Email" option to send notifications.', 'user-manager'); ?></p>
					</div>
					<div class="um-doc-section">
						<h3><?php esc_html_e('WooCommerce Integration', 'user-manager'); ?></h3>
						<p><?php esc_html_e('Emails are sent using the WooCommerce email template system, including your store\'s header logo, colors, and footer. Preview emails before sending to see exactly how they will appear.', 'user-manager'); ?></p>
					</div>
					<div class="um-doc-section">
						<h3><?php esc_html_e('Template Placeholders', 'user-manager'); ?></h3>
						<ul>
							<li><code>%SITEURL%</code> - <?php esc_html_e('Your website URL', 'user-manager'); ?></li>
							<li><code>%LOGINURL%</code> - <?php esc_html_e('Selected login URL path', 'user-manager'); ?></li>
							<li><code>%USERNAME%</code> - <?php esc_html_e('User\'s login username', 'user-manager'); ?></li>
							<li><code>%PASSWORD%</code> - <?php esc_html_e('User\'s password (plain text)', 'user-manager'); ?></li>
							<li><code>%EMAIL%</code> - <?php esc_html_e('User\'s email address', 'user-manager'); ?></li>
							<li><code>%FIRSTNAME%</code> - <?php esc_html_e('User\'s first name', 'user-manager'); ?></li>
							<li><code>%LASTNAME%</code> - <?php esc_html_e('User\'s last name', 'user-manager'); ?></li>
							<li><code>%PASSWORDRESETURL%</code> - <?php esc_html_e('Unique password reset link for the user', 'user-manager'); ?></li>
							<li><code>%COUPONCODE%</code> - <?php esc_html_e('Coupon code generated for the user (New User Coupons feature)', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-doc-section">
						<h3><?php esc_html_e('Password Reset Links', 'user-manager'); ?></h3>
						<p><?php esc_html_e('Use %PASSWORDRESETURL% in your email template to send users a secure link to set their own password. This is more secure than sending passwords in plain text.', 'user-manager'); ?></p>
					</div>
				</div>
			</div>

			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-admin-settings"></span>
					<h2><?php esc_html_e('Settings Overview', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-doc-section">
						<p><?php esc_html_e('The Settings tab is organized into cards. Key areas:', 'user-manager'); ?></p>
						<ul>
							<li><strong><?php esc_html_e('User & Login', 'user-manager'); ?></strong> — <?php esc_html_e('Default role, login URL, paste-from-spreadsheet column order.', 'user-manager'); ?></li>
							<li><strong><?php esc_html_e('Email Settings', 'user-manager'); ?></strong> — <?php esc_html_e('Send-from name/email, reply-to, throttling.', 'user-manager'); ?></li>
							<li><strong><?php esc_html_e('Activity & Logging', 'user-manager'); ?></strong> — <?php esc_html_e('Activity log, view/404/search reports, debug messages, WP-Admin activity logging.', 'user-manager'); ?></li>
							<li><strong><?php esc_html_e('User Experience', 'user-manager'); ?></strong> — <?php esc_html_e('Rebrand reset password copy, Quick Search bar, Coupon Email List Converter meta box, coupons email column, WooCommerce search-by-SKU redirect, Apply Coupon via URL parameter, display all post meta meta box, and allow editing of post meta (including add new custom fields).', 'user-manager'); ?></li>
							<li><strong><?php esc_html_e('WP-Admin CSS', 'user-manager'); ?></strong> — <?php esc_html_e('Apply CSS in wp-admin by all roles (with exclusions), by user, or by role.', 'user-manager'); ?></li>
							<li><strong><?php esc_html_e('Bulk Add to Cart', 'user-manager'); ?></strong> — <?php esc_html_e('Activation, redirect, CSV column mapping, identifier type.', 'user-manager'); ?></li>
							<li><strong><?php esc_html_e('User Creation & Import', 'user-manager'); ?></strong> — <?php esc_html_e('Update existing users, SFTP/directory paths for CSV import.', 'user-manager'); ?></li>
							<li><strong><?php esc_html_e('Checkout', 'user-manager'); ?></strong> — <?php esc_html_e('Ship To Pre-Defined Addresses and related checkout options.', 'user-manager'); ?></li>
							<li><strong><?php esc_html_e('Custom WP-Admin Notifications', 'user-manager'); ?></strong> — <?php esc_html_e('Admin notices and dismissible messages in wp-admin.', 'user-manager'); ?></li>
							<li><strong><?php esc_html_e('WP-Admin Bar Menu Items', 'user-manager'); ?></strong> — <?php esc_html_e('Custom shortcut menus in the admin bar (label and links).', 'user-manager'); ?></li>
						</ul>
					</div>
				</div>
			</div>

			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-awards"></span>
					<h2><?php esc_html_e('Coupon Automation', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-doc-section">
						<h3><?php esc_html_e('New User Coupons (login-triggered)', 'user-manager'); ?></h3>
						<p><?php esc_html_e('Coupons are never bulk generated. Each user is evaluated when they log in and land on one of the enabled front-end locations (My Account, Cart, Checkout, Product, Shop, Home, or Everywhere).', 'user-manager'); ?></p>
						<ul>
							<li><?php esc_html_e('Choose whether eligibility checks begin after registration or only after the user completes their first WooCommerce order.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Filter recipients with registration date cutoffs plus include/exclude email substring lists.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Clone any template coupon with custom code length, prefix/suffix (with %YEAR%), amount overrides, expiration days, and optional follow-up emails.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Enable the debug overlay to view a per-user decision log explaining why a coupon was created, skipped, or blocked.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-doc-section">
						<h3><?php esc_html_e('User Coupon Notifications', 'user-manager'); ?></h3>
						<p><?php esc_html_e('Display a dismissible banner on selected storefront pages that lists every coupon tied to the logged-in user\'s email address.', 'user-manager'); ?></p>
						<ul>
							<li><?php esc_html_e('Toggle visibility per page type (Cart, Checkout, My Account, Home, Product, Archives, Blog, Pages) with the same chip-style grid used elsewhere in settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Optional collapse threshold, block compatibility mode for the WooCommerce Cart/Checkout blocks, and a shortcut to hide the Store Credits plugin\'s default notice.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Optional sorting by expiration date (soonest first, with non-expiring coupons at the bottom) and an “Assigned Emails” admin column in the WooCommerce coupons list that aggregates all email restriction sources.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-doc-section">
						<h3><?php esc_html_e('Fixed Cart Coupon Remaining Balances', 'user-manager'); ?></h3>
						<p><?php esc_html_e('Turn existing fixed cart coupons into gift card–style balances without adding another plugin. When enabled, unused funds become a brand-new coupon that is single-use and tied to the customer\'s email.', 'user-manager'); ?></p>
						<ul>
							<li><?php esc_html_e('Control when remainders are generated with a minimum leftover threshold plus optional source-code prefix, contains, and ends-with filters, all evaluated case-insensitively.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Customize the generated code prefix (defaults to remaining-balance-) before the system appends [OLD CODE]-[YYYYMMDD]-[TIME] for easy traceability.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Order notes and the activity log capture each remainder coupon that is created, simplifying audits.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Optional checkout-page notice can inform customers, at the time of purchase, how much remaining balance they will receive as a new coupon after placing the order, with support for both classic and block-based checkout layouts.', 'user-manager'); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}


