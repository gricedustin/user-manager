<?php
/**
 * Versions/Changelog tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Versions {

	public static function render(): void {
		?>
		<div class="um-admin-card um-admin-card-full" style="margin-top: 20px;">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-filter"></span>
				<h2><?php esc_html_e('Versions Filter', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
					<div style="min-width:280px; flex:1;">
						<label for="um-versions-filter-text"><strong><?php esc_html_e('Keyword filter', 'user-manager'); ?></strong></label>
						<input type="text" id="um-versions-filter-text" class="regular-text" style="width:100%; max-width:560px;" placeholder="<?php esc_attr_e('Type to filter changelog versions or notes...', 'user-manager'); ?>" />
					</div>
					<div>
						<button type="button" class="button" id="um-versions-filter-clear"><?php esc_html_e('Clear Filter', 'user-manager'); ?></button>
					</div>
				</div>
				<p class="description" id="um-versions-filter-empty" style="display:none; margin-top: 10px;">
					<?php esc_html_e('No changelog items match the current filter.', 'user-manager'); ?>
				</p>
			</div>
		</div>
		<div class="um-admin-grid">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-backup"></span>
					<h2><?php esc_html_e('Changelog', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-changelog-item">
						<h4>2.3.50 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Documentation > About: expanded Long Description and Feature List to be significantly more detailed, including exhaustive core feature coverage and a complete add-on inventory.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.49 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Create User tab: updated the "Create New User" card form to a two-column field layout to reduce vertical height while preserving all existing fields and behavior.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.48 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Documentation sub-menu order updated so Versions appears before About.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.47 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved "Allow WooCommerce front-end product search to include SKUs" from Settings into a new Add-ons card: "Product Search by SKU" with standard Activate toggle and description.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.46 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reports > Admin Log: added an "Add-ons Connected to Admin Log" panel that lists every add-on with status, quick links, and per-tool match counts, plus an add-on tool filter in the log table.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Documentation tab: added new subsections before Versions (Installation, About, Support), including auto-loaded screenshots from /assets/documentation-screenshots when image files exist.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.45 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tag navigation: added a new "Pages" tag (A-Z sorted) and mapped Page Creator into this filter.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.44 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Retitled the "Bulk Page Creator" add-on to "Page Creator" across Add-ons labels, card headings, notices, documentation references, and related activity log naming.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.43 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Order Invoice & Approval: added a live "currently allowed emails" list under approval-email settings, combining global list entries and user-profile checkbox-enabled emails with Edit User links when available.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.42 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Retitled the "Invoice Approval" add-on to "Order Invoice & Approval" across Add-ons labels, documentation references, and related settings/profile headings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.41 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Webhook URLs add-on: expanded all Webhook Types notes with detailed field-level guidance and full sample URLs that include every currently supported field for each webhook type.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.40 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Plugin Tags & Notes: fixed the "Tags & Notes" row action to reliably open the inline tags/notes text box editor on Plugins screen rows.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.39 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Page Creator: moved the Page Data + Create Pages action into its own card above History and removed the "Latest Run Details" section.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.38 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Page Creator: added top margin/breathing room above the "Page Creator History" card.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.37 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Admin > Orders: ensured the Decline / "Move to Canceled" action button is shown next to other status action buttons on order views.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.36 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new "Order Invoice & Approval" add-on with invoice branding/settings controls, invoice approval form settings, and WooCommerce order invoice links.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Order Invoice & Approval now supports per-user invoice approval access via Edit User checkbox (email-match based), in addition to the global approval email list setting.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.35 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new "Webhook URLs" add-on with Activate toggle plus full settings for debug mode, URL parameter handling, and individual webhook type activation.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added front-end webhook router/handlers for create/edit orders, create/edit coupons, reset password, send email, and placeholders for user/post/product/category hooks.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.34 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new "Database Table Browser" add-on with Activate toggle and records-per-page setting.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Database Table Browser now lists all tables, supports secure table drill-down with nonce checks, and renders paginated table rows directly in Add-ons.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.33 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new "Page Creator" add-on with Activate toggle, OpenAI generation controls, bulk Title|Prompt input, and create action in the Add-ons tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Page Creator now reuses the existing API key from Settings > API Keys, supports optional image downloads/featured image assignment, and stores run history.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.32 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new "Cart Price Per-Piece" add-on with Activate toggle and settings for cart/order display, suffix text, font size, and text color.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When active, unit pricing now appears under WooCommerce line subtotals for multi-quantity items on cart, checkout, and customer order views.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.31 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new "My Account Menu Tiles" add-on with an Activate toggle and settings for desktop tiles per row plus minimum tile height.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When active, My Account dashboard now renders menu endpoints as responsive tile buttons below the default dashboard text.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.30 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new "Plugin Tags & Notes" add-on with an Activate toggle in the Add-ons tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When active, wp-admin/plugins.php now includes per-plugin tags/notes badges, inline editors, a bulk Save All form, and client-side tag filtering tools.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.29 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Documentation tab: added a new "All Reports (Reports Tab Reference)" card listing every Reports section and all General Reports currently available.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.28 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: Add-ons Filter now has extra spacing below it and only displays on the all add-on tiles view (not inside individual add-on settings screens).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.27 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new "Security Hardening" add-on with an Activate toggle and granular hardening checkboxes for REST user endpoint blocking, file-edit/file-mod restrictions, forced SSL admin, and WordPress version hiding.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Security Hardening coverage to Add-ons metadata, settings persistence, and documentation/use-case references.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.26 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Simplified plugin description by removing the long inline add-ons list from the header metadata.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.25 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a Settings shortcut link to the plugin row actions on the WordPress Plugins screen.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added quick shortcut links for each main tab in the plugin row meta area next to version/author details.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.24 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed the Front-End URL Parameter Debugger add-on and its related add-on settings/UI references.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.23 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Updated plugin author metadata from "Dustin Grice" to "Grice Projects".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.22 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new Add-ons Filter card on tab=addons with keyword filtering and clear/reset behavior for add-on tiles and add-on settings screens.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a new Documentation Filter card on tab=documentation with keyword filtering across documentation cards and use cases.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a new Versions Filter card on docs_section=versions with keyword filtering across changelog items.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.21 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Documentation tab: replaced legacy cards with a fully refreshed Tabs Reference, Add-ons Reference, and updated platform overview.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Documentation tab: added a new "Use Cases" card with practical B2B/B2C scenarios (including welcome-coupon onboarding) to highlight how tools can be combined.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.20 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('WP-Admin top bar shortcut: changed the User Experience Manager link target to open the Add-ons tab by default instead of Settings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.19 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added a JavaScript confirmation alert before submitting the cart-screen "Empty cart" button.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.18 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: changed the default "Add to Cart Variation Table Button Text" fallback from "Add All Variations" to "Add to Cart".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table: updated related settings/help text so blank button text now clearly defaults to "Add to Cart".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.17 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Rebranded plugin title/author metadata to "User Experience Manager" by Dustin Grice (griceprojects.com) and refreshed the plugin description to emphasize B2B/B2C user experience outcomes.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons activation defaults: removed default-on behavior so add-ons are not auto-activated on first install unless explicitly enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.16 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added two new settings to override the header labels for the Variation and Qty columns.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table header row now uses custom Variation/Qty labels when provided, and falls back to defaults when blank.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.15 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: added top margin above the "Add to Cart Variation Table History" card for clearer visual spacing.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.14 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added a new checkbox setting "Add an Empty Cart button on Cart Screen".', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, an Empty Cart button now renders in WooCommerce cart actions and empties the cart via nonce-protected submission.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.13 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added a category filter setting "Only display variation table for products in these categories" with a scrollable checkbox list of product categories.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table front-end rendering now respects selected product categories; when no categories are selected, it remains available to all variable products.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.12 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added a new checkbox setting to remove the table header row (Variation / Qty) on the front end.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table now conditionally renders the header row based on the new hide-header setting.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.11 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added a new text setting to override the front-end "Add All Variations" button label.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table button now uses the custom label when set, and falls back to "Add All Variations" when blank.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.10 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added a new setting "Prefix all variations with the variation label" (default off) to control label formatting like "Size: Small" versus "Small".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table front-end row rendering now respects the prefix-label setting for the Variation column.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.9 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: removed the default "Add Multiple Variations" heading/description copy from the front-end table output.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table: added "Add Text Above Variation Table" and "Add Text Below Variation Table" textarea settings (HTML supported) and render those blocks on the front end.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons: added a new "Add to Cart Variation Table History" card showing timestamp, who submitted, total items added, and variation/option details for each bulk add run.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.8 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new Fatal Error Debugger add-on with Activate toggle and an admin-only front-end fatal error panel.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fatal Error Debugger now captures fatal shutdown errors, stores the latest payload, and can send email alerts only when "Sent Email To Address upon Fatal Errors" is filled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.7 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table trace stability: added defensive function-exists guards for auth checks to prevent fatal errors during early plugin bootstrap.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.6 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: removed strict WooCommerce class-load gating so render/submission hooks are always registered when the add-on is active.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table: added safety fallback render hooks so the table still appears when a theme override skips the selected hook.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table: added front-end trace diagnostics via URL parameter ?um_variation_table_trace=1 (admin-only), including hook registration state and render skip reasons.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.5 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added a backend "Single Product Page Hook" selector so the render location can be chosen per site/theme.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table: added Auto hook mode that tries multiple WooCommerce product hooks for better front-end compatibility when a single hook does not fire.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.4 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added a new settings checkbox to optionally show a third Price column in the variation table.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table: Totals row now dynamically updates both total quantity and total amount when the Price column option is enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.3 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: switched to a vertical two-column layout (Variation + Qty), removed price output, added a live Total row, and kept it as a separate alternative form under the native add-to-cart area.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table: added setting to hide/show the native variable-product dropdown add-to-cart form when the bulk table is present.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Variation Table: added debug mode setting and front-end debug output for Add All Variations processing details.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.2 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Admin Orders: added configurable button labels for Approve/Decline (default labels now "Move to Processing" and "Move to Canceled"), hid Approve when already Processing, and hid Decline when already Canceled.', 'user-manager'); ?></li>
							<li><?php esc_html_e('My Account Admin Orders action handling now blocks redundant approve/decline actions for already-Processing and already-Canceled orders, with corresponding notices.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.1 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: refactored as an alternative form under the default Add to Cart area with an "Add All Variations" submit flow and optional front-end debug mode.', 'user-manager'); ?></li>
							<li><?php esc_html_e('My Account Admin Orders: added status filter configuration, inline status filter links, optional hide-order-status toggle, approve/decline actions for all non-completed statuses, and internal order notes that record who performed each action and when.', 'user-manager'); ?></li>
							<li><?php esc_html_e('My Account Admin Order additional meta field format now supports "meta_field:Label:prefix_before_value", with URL auto-linking and "Open File" link text for prefixed file URLs.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.0 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Release 2.3.0: bundles recent Add-ons, Bulk Add to Cart, My Account Admin, and WP-Admin CSS improvements from the 2.2.9x series.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.96 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart shortcode: switched "Download Sample CSV" to a direct CSV download endpoint so line breaks are preserved reliably.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart sample CSV now includes a blank line after the header row and keeps the same headers as the product-data sample.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.95 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart shortcode: "Download Sample CSV" now uses the same header columns as "Download Sample CSV with Product Data" (identifier, quantity, product_title, product_variation).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.94 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart shortcode UI: changed sample download controls to text links and moved them above the "Select CSV File" upload field.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.93 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Admin add-on UI: indented sub-settings for Product Viewer, Coupon Viewer, and User Viewer to match the Order Viewer layout.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.92 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('WP-Admin CSS hide preset dropdown fix: allowed top-bar submenu wrappers are now hidden by default and shown only on hover/focus.', 'user-manager'); ?></li>
							<li><?php esc_html_e('WP-Admin CSS JS fallback now preserves collapsed submenu state and toggles visibility only during interaction.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.91 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('WP-Admin CSS hide preset fix: CSS output now renders as raw CSS (not HTML-escaped), so advanced selectors apply correctly.', 'user-manager'); ?></li>
							<li><?php esc_html_e('WP-Admin CSS hide preset fallback: added JS-based hide pass after DOM load for admin screens that render/refresh layout after initial head CSS.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.90 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('WP-Admin CSS preset hardening: increased selector coverage/specificity and reinforced !important rules so the admin sidebar/top-bar hide preset applies more consistently across wp-admin screens.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.89 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('WP-Admin CSS add-on: added a new preset card to hide wp-admin sidebar/top-bar chrome while preserving profile/logout and custom top-bar menu items.', 'user-manager'); ?></li>
							<li><?php esc_html_e('WP-Admin CSS preset targeting: added usernames/emails field and role checkbox targeting (OR logic) for applying the preset to specific users.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.88 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Bulk Import: added a dedicated history report card in Add-ons with Timestamp, User Email, Media Library file link, Total Items Added, Number of Errors, and View More details.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add to Cart Bulk Import history now stores confirmation notification messages and per-line detail messages for exact View More reporting.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.87 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Coupons Page add-on: added a reminder and direct link to resave Permalinks after activation so the endpoint is registered.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.86 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: retitled "My Account Coupon Screen" to "My Account Coupons Page".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.85 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: retitled "Checkout Pre-Defined Addresses" to "Checkout Address Selector".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.84 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: retitled "Coupon for New User" to "New User Coupons", "Coupon Notifications for Users with Coupons" to "User Coupon Notifications", and "Coupon Remaining Balances" to "User Coupon Remaining Balances".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.83 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: retitled "My Account Site Admin" to "My Account Admin".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.82 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: retitled "Coupon Remaining Balances (Simple Gift Card & Store Credit Functionality)" to "Coupon Remaining Balances".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.81 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: standardized all primary add-on toggle labels to a simple "Activate" checkbox while preserving each card description text beneath it.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.80 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons navigation: removed the "All Add-ons" shortcut from the new sub-navigation list.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons layout fix: added a clear break after the floated subsubsub list to prevent add-on cards from rendering in an off-screen side column.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons section default: when no addon_section is provided, the first add-on section now opens by default.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.79 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons focused section safety fix: all add-on cards now remain in the form markup while non-selected sections are visually hidden, preventing unrelated add-on settings from being cleared on save.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.78 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab: added a subsubsub-style add-on navigation list to quickly jump to a specific add-on section.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons tab: added per-section filtering so selecting an add-on shows only that add-on card while keeping Save Add-ons available.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons tab: active add-on links in the new navigation are bold, and saving now keeps users on the same selected add-on section.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.77 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons: added a new "My Account Coupon Screen" add-on with Activate toggle plus settings for Menu Title Name, Page Title, and Page Description.', 'user-manager'); ?></li>
							<li><?php esc_html_e('My Account: added a dedicated Coupons endpoint/tab that reuses the User Coupon Notifications coupon query and displays all matching coupons as WooCommerce-style notices on that page.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.76 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart notice copy: changed "Line-by-line product processing" heading to "Details" and shortened each row line.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart details line format now uses "ID: ... — Added/Error (qty)" and only includes "Note: ..." when the row has an error.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.75 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Email Templates layout: default view now shows Saved Templates + Add New Template (empty form) in two columns.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email Templates layout: edit view now shows Live Preview + Edit Template in two columns and hides Saved Templates while editing.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.74 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart: debug notice routing now preserves only the two primary user-facing Woo notices (total items + line-by-line summary) while redirecting all other processing/debug notices into the Debug Information panel.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.73 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart: added a new WooCommerce success notification showing total items added and a View Cart button.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart: added a new line-by-line WooCommerce notification listing CSV line, product ID, product title, variation, qty added, status, and error reason when applicable.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.72 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Email Templates UI: moved Live Preview (Demo Data) above Saved Templates in editing mode so both are visible side by side.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email Templates layout: preview now appears at the top of the form column while editing.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.71 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart debug UI: moved upload/processing notice messages into the Debug Information panel when debug mode is active.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart debug UI: added formatted notice flattening so multi-line details (like per-product result rows) display cleanly in Debug Information.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.70 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart upload trigger fix: processing now runs when the submit field is present (even if browser posts an empty submit value), and the submit button now posts value="1" explicitly.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart shortcode UI: fixed "Download Sample CSV" button URL rendering by allowing data: protocol output.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.69 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart debug panel now includes a line-by-line CSV processing trace showing what happened for each file row.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart uploads are now copied into Media Library with metadata for uploader, upload time, and source URL.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Activity links for Bulk Add to Cart uploads now prefer the Media Library attachment URL when available.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.68 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: retitled "Coupon Automatically Created for New User" to "Coupon for New User".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons tab: re-sorted cards A→Z after the title update.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.67 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart uploads now create a User Activity entry with a direct link to the uploaded CSV file (Reports → User Activity).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.66 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart upload processing: rows with blank/zero quantity are now skipped (not treated as errors), which better supports product-data sample CSV workflows.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart debug improvements: added richer upload/request diagnostics and processing summary details for faster troubleshooting.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart shortcode now prints WooCommerce notices in-place so upload results are visible even on non-WooCommerce pages.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.65 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart CSV parser now ignores blank rows anywhere in the file, including leading blank rows before the header row.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.64 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart shortcode UI: added "Download Sample CSV with Product Data" to export all product + variation IDs with quantity defaulted to 0, plus informational product title and variation columns.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart uploader compatibility: product title / variation columns remain informational and are ignored during upload because only identifier and quantity columns are parsed.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.63 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart shortcode UI: removed the optional debug URL bullet from the "How to Use" list.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart shortcode UI: added a "Download Sample CSV" button that generates a sample file using the configured identifier and quantity column names.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.62 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart: fixed front-end shortcode registration so [bulk_add_to_cart] registers reliably even when WooCommerce loads after User Manager.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart shortcode now displays a clear WooCommerce-required message when WooCommerce is unavailable.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.61 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings tab: cards now sort A–Z on load, with API Keys always kept as the final card.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.60 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Admin card layout: removed max-height from .um-admin-card so cards grow with their content instead of clipping field areas.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.59 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Admin card styling: removed global overflow-y:auto from .um-admin-card-body so cards no longer force internal vertical scrolling.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.58 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Site Admin: removed redundant helper description line under the activation area.', 'user-manager'); ?></li>
							<li><?php esc_html_e('My Account Site Admin: indented all "My Account Admin Order Viewer" sub-settings for a clearer parent/child hierarchy.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.57 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Admin UI cleanup: removed remaining in-card vertical scroll wrappers so card content expands naturally.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.56 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: retitled "Coupon Bulk Creator" to "Coupon Creator" and "Bulk Add to Cart" to "Add to Cart Bulk Import".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.55 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: added short always-visible descriptions under activation checkboxes where the add-on purpose was only shown inside hidden settings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.54 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab: re-sorted add-on cards A→Z.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons save wiring: cards moved in order now submit settings via form attributes while preserving dynamic template row saves.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.53 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: removed remaining nested-card wrappers for embedded Blog Post Importer and Post Idea Generator sections in Add-ons.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.52 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: Post Idea Generator tool now renders embedded content inside its add-on card (no nested inner card).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.51 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Site Admin: added role-based access checkboxes under each username allow-list (Orders, Products, Coupons, Users, and Order Approval).', 'user-manager'); ?></li>
							<li><?php esc_html_e('My Account Site Admin permissions now support username OR role matching for viewer access and order approvals.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.50 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: Coupon Automatically Created for New User, Bulk Add to Cart, Coupon Notifications for Users with Coupons, and Coupon Remaining Balances now hide their settings blocks unless the add-on is active.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons live toggle behavior: those four cards now show/hide settings immediately when activation checkboxes are changed.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.49 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons UI: moved Blog Post Importer and Post Idea Generator functionality into their respective cards (Post Content Generator / Post Idea Generator) instead of separate bottom panels.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons save flow: API and Post Idea activation/settings fields now persist correctly from their in-card placement.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.48 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Updated coupon remainder notice setting labels to use "Code Used" wording for clarity.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.47 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons migration: restored both migrated tool sections from the old Tools tab for Post Content Generator (Blog Post Importer) and Post Idea Generator.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons: linked migrated tool sections now toggle reliably from their corresponding activation checkboxes so they consistently display when enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.46 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons save reliability: fixed submit-action reset logic so "Save Add-ons" consistently posts to user_manager_save_settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons form now safely defaults to settings save on Enter-key submits unless an explicit action button (e.g., bulk coupon create) initiated the submit.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.45 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons save flow: persisted all Bulk Coupons form values when using "Save Add-ons" (template code, totals, emails, amount override, prefix/suffix, length, expiration options, send-email toggle, and selected template).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons form cleanup: aligned Bulk Coupons setting keys so save behavior is consistent whether using "Save Add-ons" or coupon-create actions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.44 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons: removed duplicate Post Idea Generator section rendering on the Add-ons page.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the "Bulk Coupons is currently disabled in Add-ons..." notice message.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.43 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons: ensured Post Content Generator and Post Idea Generator migrated sections are always mounted and toggle reliably from their activation checkboxes.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons: Blog Post Importer and Post Idea Generator tool areas now show/hide live when toggles are changed, improving visibility and migration reliability from Tools.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.42 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Refactor: split large Core activity-log/detail methods into a dedicated module trait file for cleaner organization.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Refactor: split large Reports tracking numbers/notes render+export methods into a dedicated reports module trait file.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Refactor: split Content Generator / Blog Importer action handlers into a dedicated actions module trait file.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Refactor: split My Account Site Admin endpoint renderer/list/detail methods into a dedicated renderer module trait file.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.41 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Admin Log "View Details" now includes an "All Logged Form Data" section that recursively displays all stored entry fields.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added nested key rendering for activity detail payloads so values from arrays/objects are visible with full key paths.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added sensitive-field masking in details output for keys containing password, API key, token, secret, nonce, authorization, or cookie.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.40 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Add-ons reindexing for WP-Admin Bar Menu Items so all field types (including the new side selector) keep correct names after removing rows.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Role Switching add-on: replaced full get_users() load with paginated WP_User_Query output in the "Users with Role Switching Access" table for better performance.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Reports: replaced SQL_CALC_FOUND_ROWS/FOUND_ROWS() in Orders Tracking Notes with a separate COUNT(*) query for better compatibility and scaling.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.39 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Retitled add-on card "WP-Admin Quick Search Bar" to "WP-Admin Bar Quick Search" (including activation label text).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Re-sorted Add-ons cards A–Z by the current displayed card titles.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated Docs feature label to match the new quick search add-on name.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.38 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('WP-Admin Bar Menu Items add-on: each custom menu now has a "Top Bar Side" option (Left or Right).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Custom top bar menus now render on the selected side (`root-default` for left, `top-secondary` for right).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Menu side selection is saved per menu item and defaults to Right for backward compatibility.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.37 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Docs tab documentation view now uses a single-column layout instead of two columns.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated docs layout wrapper to use the same single-column grid style used by other admin areas.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.36 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('User Role Switching add-on now includes the same top-level Activate checkbox pattern used by other add-ons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Role Switching settings/history area now hides when the add-on is deactivated and reappears when activated.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons tab JS now toggles the Role Switching settings area based on activation state.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.35 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added explicit Activate checkboxes to add-ons that previously relied on content presence: My Account Site Admin, WP-Admin Notifications, WP-Admin Bar Menu Items, and WP-Admin CSS.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons save flow now stores these activation toggles and Add-ons cards show/hide their settings areas based on activation state.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added runtime gating so disabled add-ons do not output admin notifications, admin bar custom menus, WP-Admin CSS, or My Account Site Admin features.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.34 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons retitles: Quick Search Bar → WP-Admin Quick Search Bar, Coupons for New Users → Coupon Automatically Created for New User, and Blog Post Idea Generator → Post Idea Generator.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated related add-on labels/messages to match the new naming.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Re-sorted Add-ons cards A–Z by the current card titles.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.33 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings > General Settings: moved coupon-related options out of User Experience into a new "Coupons" card.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Settings > General Settings: moved post-meta-related options into a new "Post Meta" card.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Experience card now focuses on UX/search behavior while coupon and post meta controls are grouped in dedicated cards.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.32 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved the "Display Quick Search Bar" control from Settings into Add-ons as a dedicated "Quick Search Bar" card.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons now saves the Quick Search Bar toggle, and General Settings saves no longer overwrite this add-on setting.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Quick Search Bar remains enabled by default unless explicitly disabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.31 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings filter now uses keyword search only (removed the "Filter by area" dropdown).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Retained auto-expand behavior while searching so matching cards/fields open automatically.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Clearing the keyword filter returns Settings cards to the default collapsed state.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.30 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Retitled Add-ons card names for clarity: Role Switching → User Role Switching, Custom WP-Admin Notifications → WP-Admin Notifications, Bulk Coupons → Coupon Bulk Creator, and ChatGPT Content Generator → Post Content Generator.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated matching activation labels for renamed add-ons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Synced documentation labels to the new add-on names.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.29 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings tab cards now load collapsed by default for a cleaner first view.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When a Settings filter is active (area or keyword), matching cards auto-expand to show results immediately.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Clearing filters returns Settings cards to the default collapsed state.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.28 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Login As user search results not appearing for valid email searches.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated Login As AJAX user lookup to support WP_User_Query partial-field result objects as well as WP_User objects.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As type-to-search now correctly returns username/email matches again.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.27 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a dedicated Add-ons card for "Blog Post Idea Generator" with its own Activate checkbox.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved Blog Post Idea Generator out of the ChatGPT Content Generator area and into its own add-on section.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Blog Post Importer remains under ChatGPT Content Generator, while idea generation now uses separate add-on activation.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.26 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons > ChatGPT Content Generator now includes an Activate checkbox to enable/disable the add-on.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved Blog Post Importer and Blog Post Idea Generator UI from Tools into the ChatGPT Content Generator area on the Add-ons tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tools under Settings now focuses on utility tools (template imports and log/reset actions), while blog content generation tools live with ChatGPT settings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.25 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings tab layout changed to a single-column card layout.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a new Settings Filter panel at the top of Settings with area + keyword filters.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Settings filter now lets admins isolate specific settings by card title, labels, descriptions, and field values.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.24 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved "Coupon Lookup by Email" out of Tools and into Reports as its own sub menu link.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Reports sub-links now include General Reports, User Activity, Admin Log, and Coupon Lookup by Email.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Legacy coupon lookup URLs using tab=tools now resolve to Reports > Coupon Lookup by Email when coupon_lookup_email is present.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.23 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings tab now includes WooCommerce-style sub-links: General Settings (default), Email Templates, and Tools.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved the former top-level Email Templates and Tools views under Settings sub-links.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Legacy ?tab=email-templates and ?tab=tools URLs now resolve to Settings and open the correct sub-section.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.22 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Docs tab now includes WooCommerce-style sub-links: Documentation (default) and Versions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved Versions content into the Docs tab as a sub-section while preserving the full changelog view.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Legacy ?tab=versions URLs now resolve to Docs and open the Versions sub-section for backward compatibility.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.21 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reports tab now includes WooCommerce-style text sub-links: General Reports, User Activity, and Admin Log.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved the former top-level User Activity (tab=login-history) and Admin Log (tab=activity-log) views under Reports sub-links.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added backward-compatible routing so legacy tab URLs still resolve to Reports and open the correct sub-section.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.20 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings tab: added a new "API Keys" card and moved the ChatGPT / OpenAI API Key field there.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons tab: renamed the API card to "ChatGPT Content Generator".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons API card now focuses on prompt/meta-box options and references Settings > API Keys for key management.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.19 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved Bulk Coupons into Add-ons as a dedicated add-on card with an Activate checkbox.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Coupons create/template actions now submit from Add-ons and return to Add-ons with success/error notices.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the top-level Bulk Coupons navigation tab and route legacy ?tab=bulk-coupons requests to Add-ons.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.18 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved Coupons settings into three dedicated Add-ons cards: Coupons for New Users, Coupon Notifications for Users with Coupons, and Coupon Remaining Balances (Simple Gift Card & Store Credit Functionality).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Coupon settings now save via the shared Add-ons save flow, keeping all add-on configuration in one place.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the top-level Coupons navigation tab and route legacy ?tab=coupons requests to Add-ons for backward compatibility.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.17 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab now keeps all add-on cards collapsed by default on page load, regardless of active state.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a clear active-state indicator in each add-on card header (status pill with dot + Active/Inactive label).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Active add-ons now remain visibly highlighted even while collapsed.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.16 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons: moved Role Switching into the main alphabetical card list so it appears inline with the other add-ons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Role Switching add-on no longer renders an extra inner collapsible wrapper (removed collapse-inside-collapse UI).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Role Switching settings now save through the shared Add-ons save flow while keeping Role Switching settings history entries.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.15 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Login As search now uses a more reliable AJAX request flow and displays a clickable result list under the user search field.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As selection mapping was hardened with case-insensitive username/email matching for typed values and picked suggestions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the Login As administrator-target restriction (regression fix), restoring ability to impersonate administrator accounts.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.14 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab now explicitly uses the single-column grid class to force one-column rendering on wide screens.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Role Switching add-on wrapper now also uses the single-column grid class for consistent one-column layout.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.13 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab layout updated to a single-column view.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons cards reordered alphabetically (A–Z) for easier scanning.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.12 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a Cursor AI rule that enforces file organization: one file per top-level tab, and one dedicated file per add-on in the Add-ons tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Refactored Add-ons rendering so each add-on card now lives in its own file/class, while class-user-manager-tab-addons.php now acts as the orchestrator.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Preserved Add-ons behavior (collapsible cards, dynamic templates, and toggle logic) while splitting implementation across dedicated files.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.11 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved Role Switching out of its top-level tab and into the Add-ons area.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Legacy ?tab=role-switching links now resolve to the Add-ons tab for backward compatibility.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons cards now auto-collapse when inactive and can be expanded from each card header.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.10 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Login As: replaced the large "Select User" dropdown with an AJAX search field that searches by username or email address.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As: user search now loads matches on demand (instead of preloading all users), improving performance on large sites.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As: start-session handling now resolves typed username/email values server-side when no hidden user ID is selected.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.9 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new top-level "Add-ons" tab in the User Manager navigation next to Settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved these cards out of Settings and into Add-ons: My Account Site Admin, Bulk Add to Cart, Checkout Pre-Defined Addresses, Custom WP-Admin Notifications, WP-Admin Bar Menu Items, WP-Admin CSS, and API.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated settings save handling so Settings and Add-ons each save only their own fields and return to the correct tab after save.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.8 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings → Bulk Add to Cart: expanded this area with clear shortcode usage ([bulk_add_to_cart]) and front-end debug parameter instructions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart: added front-end URL debug switch ?um_bulk_add_to_cart_debug=1 to force verbose diagnostics for CSV processing.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart processing fixes: robust CSV header normalization (including UTF-8 BOM handling), safer quantity parsing, improved product lookup (ID/SKU/slug/title/meta for products and variations), and improved variation add-to-cart behavior using parent + variation attributes.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.7 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Admin Orders: fixed approve flow to handle status updates inline and display WooCommerce success/error notices without redirecting after output begins.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added URL cleanup after approve notices to remove nonce/action query args so page refresh does not re-trigger approval actions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.6 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Site Admin (Order Viewer): added checkbox "Default all new orders into a payment pending status" under order approval settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, new WooCommerce orders are defaulted to Pending payment, and payment-complete transitions remain Pending payment until manually approved.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.5 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Site Admin settings: added a "Show Meta Data area" checkbox for each viewer (Orders, Products, Coupons, Users). These are off by default.', 'user-manager'); ?></li>
							<li><?php esc_html_e('My Account Site Admin settings: added "Order approval allowed usernames (comma-separated)" under the Order Viewer settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Admin: Orders endpoint now shows an "Approve" button for pending payment orders for allowed approvers; approving changes order status from Pending payment to Processing with nonce checks and notices.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Improved search behavior across all My Account Site Admin lists (Orders, Products, Coupons, Users) with broader matching and filtered results.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.4 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Site Admin: fixed endpoint/menu initialization timing so custom My Account admin endpoints register reliably across plugin load orders.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added explicit rewrite endpoint + query var registration for admin_orders, admin_products, admin_coupons, and admin_users to improve endpoint detection.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added front-end debug URL parameter for administrators: append ?um_my_account_admin_debug=1 to a My Account URL to view a diagnostic panel with endpoint/query/menu/access details.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Improved allowed username parsing for My Account Site Admin access lists so standard WordPress logins, including email-style usernames, are handled correctly.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.3 <span>(March 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings: added a new "My Account Site Admin" card with four viewer controls and per-viewer comma-separated username allow lists: My Account Admin Order Viewer, Product Viewer, Coupon Viewer, and User Viewer.', 'user-manager'); ?></li>
							<li><?php esc_html_e('WooCommerce My Account: added custom admin endpoints and menu links for Admin: Orders, Admin: Products, Admin: Coupons, and Admin: Users.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Each My Account admin area now supports pagination, search, list views, and per-item detail views with dedicated "View" buttons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Access control: each area is only available when enabled in settings and the logged-in username is in that area\'s allow list (administrators keep access).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.2 <span>(February 14, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings → API: setting renamed to "Enable Page & Post Meta Box for inserting ChatGPT generated content into pages & posts". Meta box now appears on both Page and Post edit screens (previously pages only).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Page & Post Meta Box: when generating content via ChatGPT, the text from "Appended Information to AI Prompt" (same API card) is included in the request sent to the API, so the model receives that context with every generate.', 'user-manager'); ?></li>
							<li><?php esc_html_e('WP-Admin top bar: new "User Manager" link (right side of the bar) that goes to the plugin Settings tab.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.1 <span>(February 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Tools → Blog Post Importer: WYSIWYG/HTML editor (wp_editor) for post description; post category as side-by-side checkboxes; full-width title and description; compact layout with title, date, and categories in one row above body; create posts as Gutenberg paragraph and list blocks (not classic block); success message with tiles (thumbnail max 200px, title wrap, View/Edit buttons); default Visual tab for editor; when creating posts with no date set, start with the most recent published or scheduled post date, then set every new post X days forward (first +X, second +2X, third +3X, etc.); “days” value (default 25) saved on form submit; auto-check categories when name or slug appears in title or body (server and client).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Settings: new API card at bottom for ChatGPT/OpenAI API Key (password field); key saved and used for Tools → AI Prompt Support.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tools → AI Prompt Support: per-row "Topic idea" input and "Auto write from ChatGPT" button; API returns body and 3–5 titles; body inserted into that row\'s editor and radio list of titles to fill Post Title.', 'user-manager'); ?></li>
							<li><?php esc_html_e('ChatGPT request error handling: server returns debug payload (HTTP code, response preview, parse errors, etc.); client shows error message and debug block in a red box per row; when 403 with body "-1" (nonce/referer failure), friendly message for dev environment (use same URL and port).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tools → Recent posts: section always visible on Tools tab (below Blog Post Importer form), not only in success notice; “Spread to date” date field and “Evenly spread all scheduled posts out to this date” button; AJAX reschedules all scheduled posts evenly from today to the chosen date.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tools → Recent posts: displays number of scheduled posts and “Recommended date” (today + scheduled count × saved days); date field pre-filled with recommended date; visible “Spread to date:” label; button uses recommended date when field is empty.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Settings → API: under Appended Information to AI Prompt, new checkbox "Enable Page & Post Meta Box for inserting ChatGPT generated content into pages & posts". When enabled, page and post edit screens show a "ChatGPT generated content" meta box with: what should be written about (text input), number of paragraphs (default 5), number of sentences per paragraph (default 5), Generate button (shows preview), and "Insert to bottom of this post" (inserts content as Gutenberg paragraph blocks, keeping formatting). Usage is logged to Admin Log.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2 <span>(February 7, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings → User Experience: new "WP-Admin CSS" card. Apply custom CSS only in wp-admin: All Roles CSS (with optional roles to exclude), User-based CSS (for specific users by login/email/ID), and per-role CSS. Roles are loaded dynamically from the site. CSS is output in admin_head based on current user.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Reports: new "Post Meta Field Names (Unique List)" report. Lists every distinct meta_key from wp_postmeta, sorted A–Z, with pagination and CSV export.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Experience: "Display all post meta fields & values in a meta box when editing posts". Adds a "Post Meta (All Fields & Values)" meta box on the edit screen for all post types (with UI), listing every meta key and value in a table.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Experience: "Allow editing of post meta values". When the post meta meta box is enabled, existing values are editable and a table section "Add new meta field" allows adding custom key/value pairs; "Add another" adds more rows. Save the post to apply. Nonce and capability checks protect saves.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Replaced the “Also Append to Allowed Emails on Coupon” select boxes with searchable text inputs (with datalist) on Create User and both Bulk Create sections (Upload CSV File and Paste from Spreadsheet), so users can type to filter or enter a coupon code directly.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fixed coupon search fields not returning any options: coupon lists now load via get_posts(shop_coupon) and WC_Coupon instead of the non-existent wc_get_coupons() function.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved the Login As tab to the left of the Role Switching tab in the main navigation.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added per-row coupon support for bulk import: new optional column coupon_email_append. When set in a CSV or pasted row, that row’s email is appended to the specified coupon’s “Allowed emails”. Sample CSV and CSV Format Guide updated with the column; works together with the form-level “Also Append Each Email to Allowed Emails on Coupon” option.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the “Sample Test Emails” block from Upload CSV File and the “Sample Test Data” block from Paste from Spreadsheet on the Bulk Create tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('New Settings → User & Login option: “Paste from Spreadsheet: default column order” (comma-separated). When pasted data has no header row, columns are interpreted in this order. Required column remains email.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Paste from Spreadsheet: under the Paste Data textarea, now displays the required column (email) and the current default column order from settings, with a note that the order can be changed in Settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('CSV Format Guide: added note that the default paste column order (when no header row is used) is configurable under Settings → User & Login.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Reorganized Settings tab General Settings into multiple admin cards for clearer organization.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User & Login card: default user role and default login URL.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email Settings card: send from name/email, reply-to, and email throttling options.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Activity & Logging card: activity logging, view/404/search reports, debug messages, and WP-Admin activity logging.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Experience card: rebrand reset password copy, quick search bar, coupon email converter meta box, and coupons email column.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart card: activation, redirect, CSV column mapping, identifier type, and debug mode.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Creation & Import card: update existing users and SFTP/directory paths for CSV import.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Settings tab uses a two-column layout for all cards.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Experience: new setting "Allow WooCommerce front-end product search to include SKUs"—when a search term (?s=) exactly matches a product or variation SKU, redirects directly to that product page.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login History (User Activity) tab: new Roles column that saves and displays the user\'s roles at the time the record was added; older records without roles show empty.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fixed Cart Coupon Remaining Balances: optional copy of source coupon expiration date to the new remainder coupon. New checkbox "Copy source coupon expiration date to remainder coupon" under Enable Order Received Page Remaining Balance Created Notice.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add New User page (user-new.php): large admin notice recommending User Manager for creating users, with link to Create User. JavaScript hides both default forms (Add Existing User and Add User). "No thanks, I want to use the default forms" link reveals the default WordPress forms.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Profile and user-edit pages: admin notice at top with Open User Manager and Reset Password buttons. Reset Password opens the Reset Password tab with that user\'s email pre-filled.', 'user-manager'); ?></li>
							<li><?php esc_html_e('User Experience: "Apply Coupon Code via URL Parameter" setting. When enabled, visiting any page with the chosen parameter (default ?coupon-code=CODE) applies the coupon to the cart. Optional custom URL parameter name.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Reset Password tab: "Insert Random Password" button next to New Password field to auto-fill a secure random password.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Checkout Ship To Pre-Defined Addresses: added "Show debugging info for admins" checkbox under Troubleshooting. When enabled, a detailed debug box appears on the checkout page (for users who can manage options) showing feature state, settings, parsed addresses, and hooks—rendered from Core wp_footer so it appears even if Ship To init returned early.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Checkout Ship To Pre-Defined Addresses: Ship To init is now deferred to woocommerce_loaded so hooks register after WooCommerce is available. Removed early fallback that forced the dropdown above billing; "Checkout field location" now controls where the Shipping Location dropdown appears (e.g. After billing form, After shipping form). Added CSS so the Shipping Location block stays visible on checkout.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fixed Cart Coupon Remaining Balances: new checkbox "Apply Free Shipping to New Remaining Balance Codes" under "Copy source coupon expiration date to remainder coupon". When enabled, each newly created remaining balance coupon also grants free shipping.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Create New User: under Email Address, added a "Quick Test User" note showing the current user\'s email with a plus and date-time string before the @ (e.g. user+20260206-143022@domain.com) for easy copy-paste of test addresses.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Create → Paste from Spreadsheet: under Default column order, added a "Quick Test Users" selectable textarea with four pre-filled dummy rows (email+date-1..4, Dummy, Name, customer, username) so admins can copy and paste into Paste Data to quickly create test users.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Download Sample CSV: added username column to the sample CSV header and sample rows so the file matches the default paste column order.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.1.2 <span>(January 23, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Changed coupon remainder source requirements from AND to OR logic. Coupons now match if they meet ANY requirement type (Prefix OR Contains OR Suffix), making it easier to match coupons with different naming patterns.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Enhanced checkout page debug output accessibility. "Enable Checkout Page Debug Output" is now available to all logged-in users, not just administrators.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added new public debugging URL parameter (?debug_coupons=1) that shows detailed coupon matching information on any page, including all applied coupons with matching status, remaining balance calculations, and comprehensive matching details (prefix/contains/suffix checks).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Improved debug output with detailed matching information showing which specific requirements matched or failed, displaying all configured prefixes, contains, and suffixes, and enhanced failure reason messages.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated settings descriptions to clarify OR logic behavior, making it clear that coupons match if they meet any of the three requirement types.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added user role selector to Email Users tab with a scrollable list of all user roles showing email counts. Users can select one or more roles via checkboxes to automatically fill the Email Addresses field, supporting multiple role selection. Selected roles are tracked in the activity log for history.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added email throttling feature with a new setting "Throttle Sending Emails to X Emails Per Page Load" to avoid triggering spam filters. When enabled, only the specified number of emails are sent per page load, with remaining emails stored for the next batch. A "Pending Email Batch" card displays progress and a "Send Next Batch" button allows continuing the send process even days later. Batches persist for 30 days.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Custom Email Lists feature with a new "Custom Email Lists" card in the Email Users tab. Users can create lists with custom titles and email addresses (one per line), edit and delete existing lists. Lists are sorted alphabetically by title. A "Select by List" section with checkboxes allows auto-filling email addresses similar to role selection, supporting multiple list selection and combining with role selections. Selected lists are tracked in the activity log for history.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Custom Email Lists section to user edit page showing which lists the user belongs to. Checkboxes allow easy addition or removal of users from lists, with changes saved when the user profile is updated.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added admin activity logging for Custom Email Lists operations. List creation, updates, and deletions are now tracked in Admin Log with details including list ID, title, email count, and change information.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Delete List button when editing lists with a separate delete form for safe list deletion. Fixed nested form issue that was causing Update List to delete lists instead of updating them.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.1.1 <span>(January 20, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed the Email Users tab preview button to correctly load the selected email template by adding support for the email-users form type in the preview modal function.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added email sender configuration options in General Settings: Send From Name, Send From Email Address, and Reply To Email Address, allowing all User Manager emails to use custom sender information instead of the default WordPress sender.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a checkbox option in the Email Users tab to send emails to all addresses in the list, even if they do not exist as WordPress users, enabling bulk email campaigns to any email address.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a Coupon Audit report under Reports that lists coupon details plus allowed email overlaps, including per-email coupon counts and a combined list of codes sharing the same allowed email.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.1.0 <span>(January 18, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a Duplicate button to the Email Templates tab so existing templates can be cloned into new drafts in one click, preserving ordering and fields.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tightened the Coupons with Remaining Balances report to only show remainder coupons that have never been used (usage count == 0), ensuring the list reflects unused remaining-balance codes.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Resolved a fatal error in the Life Brand AI error handler by safely checking for wp_mail() before sending exception emails and falling back to error_log when unavailable.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Enhanced the Coupons Unused report to include an Allowed Emails column on-screen and in CSV export, aggregating WooCommerce and User Manager email restrictions into a single list.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Introduced a new Role Switching tab that centralizes “View Website by Role” settings, user permissions, and history, while integrating role switch and settings changes into the Admin Activity Log.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a front-end Role Switching bar with an improved single-line layout and auto-switching on dropdown change, plus an Admin Log-backed history of who changed roles and when.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a Login As tab that lets admins temporarily set a random password for a user, log in via incognito using the generated credentials + login URL, and then restore the original password with a single click.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As activity is now logged into the Admin Activity Log (temporary password set and password restored events) and surfaced in a Recent Login As History card for quick auditing.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved the Bulk Add to Cart plugin functionality into User Manager settings under a new “Activate Bulk Add to Cart Functionality” toggle, preserving CSV identifier/quantity settings and the [bulk_add_to_cart] shortcode.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Extended Role Switching history cards to pull from the unified Admin Activity Log, so “Users Who Have Changed Their Role” and “Role Switching Settings Change History” now reflect recent role changes and settings updates driven by the front-end switcher.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a “Coupon Lookup by Email” tool under the Tools tab that searches Allowed Emails across WooCommerce restrictions, customer_email meta, and User Manager coupon meta, returning a detailed table of matching coupons with type, value, creation date, expiration, and validity status.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added an “Also Append This Email Address to Allowed Emails on Coupon” selector to the Create User and Bulk Create tabs so newly created or updated users can be automatically mapped into specific coupon email restrictions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a “Users Who Used Coupons” report under Reports that aggregates coupon usage by customer email, showing total coupons used, combined coupon discount value, coupon codes list, and the total paid after coupons across all matching orders.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Introduced an optional Quick Search Bar setting that adds a Search icon to the WordPress admin bar; when enabled, admins get a dynamic dropdown of search fields for all active post types on the site (plus users and media) with single-result auto-redirects.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Improved the “Assigned Emails” column toggle for WooCommerce coupons so the column is injected more robustly into the list table without disturbing existing columns, while still aggregating customer_email, email restrictions, and User Manager coupon meta.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Enhanced New User Coupons with an “Automatically Draft Duplicated Codes” setting that, when enabled, scans for older coupons matching the configured prefix/suffix per user and keeps the oldest coupon active while moving any newer accidental duplicates to Draft.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tightened New User Coupons and Bulk Coupons template selection so the template dropdowns only list coupons whose codes contain the word “template”, making it easier to choose true template coupons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Refined the Login As UI so the user selector dropdown is sorted A–Z by email and only shows email addresses, and the Active Login As Session panel now exposes Username, Temporary Password, Login URL, and SSO Bypass Login URL as copy-on-click text fields.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated the Tools tab layout so all admin cards (Import Demo Email Templates, Import Automated Coupon Email, Clear Logs & Reset Views, and Coupon Lookup by Email) render as full-width rows for easier scanning.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Optimized the User Coupon Usage report so it considers only a recent window of orders (last 12 months, capped at 10,000 orders), preventing timeouts on large stores while keeping aggregate coupon usage accurate.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a “If Cart Quantity Changes from 1 to 0 and Cart is Now Empty, Remove All Coupon Codes from Cart” option under User Coupon Notifications to automatically clear applied coupons whenever the cart becomes empty, ensuring new coupon notices and remainder codes can surface cleanly.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Enhanced User Coupon Notifications debug mode with per-coupon explanations that spell out why each coupon is shown or hidden (already applied, expired, zero amount, email restrictions, usage limits, etc.), making front-end behavior much easier to understand.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Extended the Coupon Lookup by Email tool with a copyable human-readable summary textarea that narrates each live coupon’s value, status, expiration, and, for fully used coupons, its original value and created date, while skipping drafted coupons from the summary.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.0.1 <span>(January 14, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Enhanced the Coupons with Remaining Balances report to include Email and Created columns on-screen and in CSV export, with remainder coupons now explicitly sorted by newest created first.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added three new shipment analytics reports under Reports: Order Total Shipments by Day, Week, and Month, each aggregating completed orders by completion date with total counts, summed order totals, pagination, and CSV export.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Introduced an Order Tracking Numbers report that surfaces WooCommerce Shipment Tracking data (carrier, tracking number, shipped date) for the most recently completed orders, with CSV export support.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added an Order Tracking Number Notes report that scans order notes containing “has been shipped” as a fallback when shipment tracking meta is not available, listing the newest shipped notes first with CSV export.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a Coupons Unused report that surfaces all still-usable coupons (not expired and under their global usage limit) with full coupon configuration details on-screen and in CSV export.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Optimized shipment and tracking reports to cap queries to a recent window of completed orders, preventing timeouts on large stores while keeping newest-to-oldest reporting.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Upgraded the Email List Converter meta box on coupon edit screens with prepend/append behaviors, live email counts, and an automatic upgrade of the Allowed Emails field into a larger textarea for easier editing.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.0.0 <span>(January 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Major clean release that consolidates all recent enhancements to reports, bulk coupons, email tooling, and activity logging into a single stable version.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Reports tab now includes advanced analytics such as User Total Sales, Product / Product Category / Product Tag Purchases, Page Not Found 404 Errors with hit counts, and Search Queries with post-type awareness (including products), all sortable and exportable to CSV.', 'user-manager'); ?></li>
							<li><?php esc_html_e('New view-based reports track Page Views, Post Views, Product Views, and their category/tag archives with total view counts, last viewed timestamps, and last user, plus a global toggle in Settings and a reset tool under Tools.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Coupons tab includes a one-click basic coupon template generator, improved success UI with copy-friendly coupon lists, and support for sending & previewing coupon emails using %COUPONCODE% to both users and raw email addresses.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email templates and Send Email flows now ship with a curated set of demo and automated coupon templates (including a $10 apology coupon) and inline guidance on choosing the right template when no coupons are being created.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Admin and User Activity logs have been hardened with dedicated tables, filters, pagination, and new actions for WooCommerce orders, log clearing, and 404/search/view tracking, providing a comprehensive audit trail across wp-admin and the front end.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.7.0 <span>(January 8, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new order reports to the Reports tab: Orders with $0 Total, Orders with Free Shipping, and Order Payment Methods, each with pagination and CSV export.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Orders with $0 Total now includes an inline line items preview column and exposes coupon codes and total coupon value instead of payment method.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Order Payment Methods report surfaces how each order was paid, including Stripe UPE wallet types such as Apple Pay, Google Pay, Link, and PayPal via Stripe when available.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Coupons success notices now include a copy-friendly textarea listing all new coupon codes (one per line) plus a separate linked list beneath for quick navigation.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a quick “basic_coupon_template_10_off_one_time_use_no_expiration” generator on the Bulk Coupons tab that creates a fixed cart coupon worth 10.00, one-time use, with no expiration, and hides itself once the template exists.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Coupons email mode can now send coupon emails to each address in the email list using any email template with %COUPONCODE% support, logging both coupon creation and email sends into the Admin Activity Log.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Coupons tab now supports Email Preview for address-based coupon emails using the shared WooCommerce-styled email preview modal, including a sample %COUPONCODE% in the preview.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a new “Send $10 coupon apology” template to the automated coupon imports section and documented it under Tools → Import Automated Coupon Email.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated Create User, Bulk Create, and Reset Password tabs with inline notes under Send Email options to clarify template selection and to warn that coupon-based templates may not behave as expected when no coupons are created.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a User Total Sales report that aggregates WooCommerce orders by user to show username, email, name, total sales, total orders, and total line items purchased, with CSV export support.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added three product analytics reports under Reports: Product Purchases, Product Category Purchases, and Product Tag Purchases, each aggregating total sales, quantities, and order counts sorted from highest sales to lowest, with CSV export support.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a Page Not Found 404 Errors report that logs unique 404 URLs, showing last-seen timestamps, hit counts, and the last associated user when available, sorted with the most recent 404s at the top and exportable to CSV.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a Search Queries report that aggregates site-wide search terms from ?s= queries, showing total searches, last searched timestamp, last user, and an example URL, sorted by most-searched at the top with CSV export support.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.6.9 <span>(January 7, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added Bulk Coupons tab next to Coupons with a two-column admin card layout mirroring the Bulk Create tab, featuring a Create Bulk Coupons card and a Recent Bulk Creates sidebar.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Coupons tool can clone a template WooCommerce coupon into many new codes with support for total count or per-email generation, optional amount overrides, and configurable code prefix/suffix and random length.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk coupon runs now log each generated coupon and a summary entry into the Admin Log, including template used, assigned email (when applicable), and amount override details.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Override Expiration Date and Override Expiration Date based on Number of Days from Today fields to Bulk Coupons; the days-from-today option takes precedence and both values are persisted between runs.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fixed a fatal error on sites without WooCommerce enabled by guarding checkout-only coupon remainder notice logic behind WooCommerce function existence checks.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Polished the main tab navigation with improved spacing and reordered items so Coupons, Bulk Coupons, Send Email, Tools, and Settings follow a more intuitive workflow.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated the Email Users tab to gracefully handle both legacy and database-backed Admin Log formats, eliminating PHP notices when viewing the Recently Emailed sidebar.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Enhanced the User Activity tab with an Action filter dropdown (similar to Admin Log) and extended logging to include WooCommerce order activity for logged-in customers.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Clear User Activity Log alongside Clear Admin Activity Log in the Tools tab so both wp-admin and front-end activity tables can be reset independently.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Expanded the Documentation tab to cover Bulk Coupons, the enhanced Admin Log, Reports, and fixed cart remainder coupon features so on-screen help matches the current feature set.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a User Password Resets report to the Reports tab with pagination and CSV export, listing all password reset actions (including bulk runs and failures) from the Admin Log.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a User Password Changes report that pulls from the user activity table to show when users change their passwords in My Account, including URLs, IP addresses, and user agents.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Restored the Order Sales vs Coupon Usage report under Reports with date filters, pagination, and CSV export, summarizing total payments collected versus coupon discounts per order.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added multiple new coupon reports: Coupons with Email Addresses, Coupons with No Expiration, Coupons with Free Shipping, and Coupons with Remaining Balances (including a summed remaining-balance total at the top).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Order Refunds and Order Notes reports, surfacing refunded orders and internal/customer/system order notes with full CSV export support.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added an Orders Processing by Number of Days report that lists all processing orders with the oldest (most days in processing) at the top.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated the Reports tab UI to use a single “Select report” dropdown sorted A–Z, simplifying navigation as more reports are added.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Auto-imported demo email templates and the automated coupon template were moved into a centralized getter so templates are seeded the first time they are needed from any tab, not just the Email Templates screen.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.6.8 <span>(January 6, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added admin activity logging to Admin Log tab. Now tracks wp-admin logins and all post type creation/editing activities.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Post edit logs include a "View" button that shows detailed information about what fields were added, removed, or changed during the edit.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Admin login tracking uses session-based detection to prevent duplicate log entries while ensuring all admin access is recorded.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added pagination support to Admin Log tab (50 entries per page) for better performance with large activity logs.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Action filter dropdown at the top of Admin Log tab to filter entries by action type (e.g., WP Admin Login, Post Created, User Created, etc.).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added "Log WP-Admin Activity" checkbox setting in General Settings to enable/disable wp-admin activity logging. Enabled by default.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added plugin activation and deactivation logging to Admin Log tab. Records plugin name, version, author, and whether activation/deactivation was network-wide.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.6.7 <span>(January 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added two new coupon code matching settings for Fixed Cart Coupon Remaining Balances: "Only Create if Coupon Code Contains…" and "Only Create if Coupon Code Ends With…". These settings work alongside the existing "Only Create if Coupon Code is Prefixed With…" setting, allowing for more flexible coupon code matching. All three conditions can be used together, and a coupon must match all specified conditions to be eligible for remainder coupon creation.', 'user-manager'); ?></li>
							<li><?php esc_html_e('All matching is case-insensitive. The "Contains" setting checks if the coupon code contains any of the specified strings anywhere in the code, while the "Ends With" setting checks if the code ends with any of the specified suffixes.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.6.6 <span>(December 31, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('Added automatic import of demo email templates and automated coupon email template when the Email Templates tab is opened for the first time with no existing templates. This ensures new installations have starter templates ready to use immediately.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Auto-import functionality uses a site option to ensure it only runs once per installation, preventing duplicate template imports on subsequent visits.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added "Enable Checkout Page Remaining Balance Notice if Coupon is Used" checkbox setting. When enabled, displays a customer-facing notice above the Place Order button on the checkout page informing customers that they will receive a remaining balance coupon code after placing their order. The notice shows the total remaining balance amount and only appears when eligible fixed cart coupons are used that would generate a remainder coupon.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.6.5 <span>(<?php echo esc_html(date('F j, Y')); ?>)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new "Remove User" tab after Reset Password with support for removing multiple users by email address (one per line).', 'user-manager'); ?></li>
							<li><?php esc_html_e('On multisite networks, users are removed from the current site only (not the entire network). On single sites, users are permanently deleted from the environment.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed users are now tracked in a "Recently Deleted" sidebar area on the Remove User tab and also appear in the Activity Log tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Activity log now records who triggered each user removal action, and the log table header has been updated from "Created By" to "Triggered By" for better clarity.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added "Sort by Expiration Date" checkbox setting to User Coupon Notifications. When enabled, coupons are sorted with soonest expiration first, coupons with no expiration at the bottom, and secondary sort by expiration date then highest value to lowest.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fixed Cart Coupon Remaining Balances now correctly ties new remainder coupons to the order billing email instead of the logged-in user email address.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added 25px bottom margin to the "Show More Coupons" button in User Coupon Notifications for better spacing.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Enhanced the "Assigned Emails" column in the WooCommerce coupons admin screen to display all email restrictions including customer_email meta, WooCommerce email restrictions, and User Manager coupon email meta fields.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added checkbox option in Remove User tab for multisite networks: "Delete from network if user does not exist in any other sub sites". When enabled, after removing a user from the current site, the system checks if the user exists on any other sites and permanently deletes them from the network if not found.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added new Reports tab with sub-navigation menu featuring three reports: Recent User Logins, Recent Coupons Used, and Coupons with Email Addresses. All reports include pagination (50 records per page) and CSV export functionality. Reports use WordPress standard table styles and only load when selected from the sub-menu.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.6.4 <span>(December 5, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved the entire New User Coupons + User Coupon Notifications configuration into a dedicated “Coupons” tab so the Settings tab can stay focused on global preferences.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bundled the coupon notification template directly inside the plugin so it no longer depends on files from the Life Brand AI plugin.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a Coupons nav item alongside Templates/Settings for quicker access.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Introduced Fixed Cart Coupon Remaining Balances, a gift card–style workflow that rolls unused funds into a single-use coupon tied to the customer\'s email with optional prefix/minimum controls.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Overhauled the Cart/Checkout block compatibility mode so coupon banners inject cleanly into WooCommerce Blocks without breaking their hydration logic.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.6.3 <span>(December 5, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('New User Coupons now run exclusively when users log in and hit approved front-end pages, preventing coupons from being generated during manual creation or imports; the “After First Order” mode now verifies a completed order before cloning anything.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added an “Exclude Users with Email Address(s) Containing” filter plus clearer notes explaining that coupons are evaluated per-login and exactly when each mode triggers.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Refreshed the Settings UI with chip-style checkbox grids, renamed “User Coupon Notifications,” and added descriptive callouts for store credit suppression, debug toggles, and the overall notification behavior.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Recent Password Resets list now shows full email addresses (no truncation) and the notification form uses the same condensed layout as the coupon checker.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated nice_time() to accept MySQL datetime strings and DateTime objects to avoid PHP 8 type errors when displaying activity log timestamps.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.6.1 <span>(December 3, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('New “New User Coupons” admin card in Settings lets you clone an existing WooCommerce coupon per user with controls for when to create (registration or first order), registration date cutoffs, email substring filtering, prefix/suffix (with %YEAR% tokens), code length, amount overrides, expiration (days), and whether to send a follow-up email using any template.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added advanced toggles for New User Coupons to run checks on specific screens (front-end, My Account, Cart, Checkout, Product, Shop, Home) plus a front-end debug overlay that surfaces which rule blocked or created a coupon.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Coupon notifications settings now centralize everything: Activate checkbox, page-by-page visibility toggles, collapse threshold, debug mode, option to hide the WooCommerce store credit container, and a “Show Emails Assigned To” admin-column toggle.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a “Cart/Checkout block compatibility” checkbox so coupon notices can hook into render_block for the WooCommerce Cart/Checkout blocks while defaulting to the classic template hooks when unchecked.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Block compatibility mode injects the same notice markup ahead of the cart/checkout blocks, preserving page-level settings, collapse thresholds, and Apply Now behavior even in full-site-editing layouts.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.6.0 <span>(December 2, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('Added Login History tab with per-login records stored in a dedicated database table, including pagination, edit-user links, and “time ago” formatting.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added setting “Rebrand Reset Password Copy to Set Password Copy” to adjust lost password UI copy and remove username greeting from password-changed emails.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email template selects now display the selected template’s description beneath the selector.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Edit Email Template now includes a live preview card rendered with demo data using the WooCommerce email wrapper.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email templates can be reordered (Move Up/Down); all selects reflect the saved order.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated “Import demo email templates” to install 4 templates in a defined order and updated the Tools copy accordingly.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed Okta-specific mention from Login History and standardized on native tracking.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Last login user meta is now updated on each login in addition to the per-login history record.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Import now supports custom user meta columns (CSV/Paste/SFTP). Any extra columns beyond the standard fields are saved as user meta using the column name as the meta key.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.5.0 <span>(November 26, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('Added Tools tab with demo email template import and activity log clearing.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fixed WooCommerce email template integration using wrap_message() method.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Improved Activity Details modal styling with admin card layout.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Activity log now stores comprehensive data including old/new values for user updates.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.4.0 <span>(November 26, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('Added Email Users tab to send bulk emails to existing users.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Password Reset now supports multiple emails (one per line).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added SFTP/Directory import feature for CSV files.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Import detailed logs with View Log button in Activity Log.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.3.0 <span>(November 26, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('Added recently created users sidebar on Create User tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Two-column layout for Email Templates tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Display full body content in saved templates list.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added %PASSWORDRESETURL% placeholder for password reset links.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email preview displays full WooCommerce-styled HTML in iframe.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added info box on Reset Password tab with usage instructions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.2.0 <span>(November 25, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('Added email preview popup before creating users.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added confirmation dialog when sending emails.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added "Update existing users" setting option.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk preview shows first record sample.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.1.0 <span>(November 25, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('Added Create User tab with full user creation form.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Reset Password tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Bulk Create tab with CSV upload and paste support.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Activity Log with nice time formatting.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Email Templates with multiple template support.', 'user-manager'); ?></li>
							<li><?php esc_html_e('WooCommerce email template integration.', 'user-manager'); ?></li>
							<li><?php esc_html_e('No emails sent by default - opt-in only.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>1.0.0 <span>(November 25, 2025)</span></h4>
						<ul>
							<li><?php esc_html_e('Initial release.', 'user-manager'); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<script>
		jQuery(document).ready(function($) {
			function normalizeVersionsFilterText(str) {
				return (str || '').toString().toLowerCase().trim();
			}

			function applyVersionsFilter() {
				var keyword = normalizeVersionsFilterText($('#um-versions-filter-text').val());
				var anyVisible = false;
				$('.um-changelog-item').each(function() {
					var $item = $(this);
					var itemText = normalizeVersionsFilterText($item.text());
					var matched = keyword === '' || itemText.indexOf(keyword) !== -1;
					$item.toggle(matched);
					if (matched) {
						anyVisible = true;
					}
				});
				$('#um-versions-filter-empty').toggle(keyword !== '' && !anyVisible);
			}

			$('#um-versions-filter-text').on('input', applyVersionsFilter);
			$('#um-versions-filter-clear').on('click', function() {
				$('#um-versions-filter-text').val('');
				applyVersionsFilter();
			});
			applyVersionsFilter();
		});
		</script>
		<?php
	}
}

