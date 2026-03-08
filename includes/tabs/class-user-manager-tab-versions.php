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
		<div class="um-admin-grid">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-backup"></span>
					<h2><?php esc_html_e('Changelog', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<div class="um-changelog-item">
						<h4>2.2.29 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings tab cards now load collapsed by default for a cleaner first view.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When a Settings filter is active (area or keyword), matching cards auto-expand to show results immediately.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Clearing filters returns Settings cards to the default collapsed state.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.28 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Login As user search results not appearing for valid email searches.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated Login As AJAX user lookup to support WP_User_Query partial-field result objects as well as WP_User objects.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As type-to-search now correctly returns username/email matches again.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.27 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a dedicated Add-ons card for "Blog Post Idea Generator" with its own Activate checkbox.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved Blog Post Idea Generator out of the ChatGPT Content Generator area and into its own add-on section.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Blog Post Importer remains under ChatGPT Content Generator, while idea generation now uses separate add-on activation.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.26 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons > ChatGPT Content Generator now includes an Activate checkbox to enable/disable the add-on.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved Blog Post Importer and Blog Post Idea Generator UI from Tools into the ChatGPT Content Generator area on the Add-ons tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tools under Settings now focuses on utility tools (template imports and log/reset actions), while blog content generation tools live with ChatGPT settings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.25 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings tab layout changed to a single-column card layout.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a new Settings Filter panel at the top of Settings with area + keyword filters.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Settings filter now lets admins isolate specific settings by card title, labels, descriptions, and field values.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.24 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved "Coupon Lookup by Email" out of Tools and into Reports as its own sub menu link.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Reports sub-links now include General Reports, User Activity, Admin Log, and Coupon Lookup by Email.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Legacy coupon lookup URLs using tab=tools now resolve to Reports > Coupon Lookup by Email when coupon_lookup_email is present.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.23 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings tab now includes WooCommerce-style sub-links: General Settings (default), Email Templates, and Tools.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved the former top-level Email Templates and Tools views under Settings sub-links.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Legacy ?tab=email-templates and ?tab=tools URLs now resolve to Settings and open the correct sub-section.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.22 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Docs tab now includes WooCommerce-style sub-links: Documentation (default) and Versions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved Versions content into the Docs tab as a sub-section while preserving the full changelog view.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Legacy ?tab=versions URLs now resolve to Docs and open the Versions sub-section for backward compatibility.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.21 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reports tab now includes WooCommerce-style text sub-links: General Reports, User Activity, and Admin Log.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved the former top-level User Activity (tab=login-history) and Admin Log (tab=activity-log) views under Reports sub-links.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added backward-compatible routing so legacy tab URLs still resolve to Reports and open the correct sub-section.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.20 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings tab: added a new "API Keys" card and moved the ChatGPT / OpenAI API Key field there.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons tab: renamed the API card to "ChatGPT Content Generator".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons API card now focuses on prompt/meta-box options and references Settings > API Keys for key management.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.19 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved Bulk Coupons into Add-ons as a dedicated add-on card with an Activate checkbox.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Coupons create/template actions now submit from Add-ons and return to Add-ons with success/error notices.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the top-level Bulk Coupons navigation tab and route legacy ?tab=bulk-coupons requests to Add-ons.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.18 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved Coupons settings into three dedicated Add-ons cards: Coupons for New Users, Coupon Notifications for Users with Coupons, and Coupon Remaining Balances (Simple Gift Card & Store Credit Functionality).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Coupon settings now save via the shared Add-ons save flow, keeping all add-on configuration in one place.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the top-level Coupons navigation tab and route legacy ?tab=coupons requests to Add-ons for backward compatibility.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.17 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab now keeps all add-on cards collapsed by default on page load, regardless of active state.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a clear active-state indicator in each add-on card header (status pill with dot + Active/Inactive label).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Active add-ons now remain visibly highlighted even while collapsed.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.16 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons: moved Role Switching into the main alphabetical card list so it appears inline with the other add-ons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Role Switching add-on no longer renders an extra inner collapsible wrapper (removed collapse-inside-collapse UI).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Role Switching settings now save through the shared Add-ons save flow while keeping Role Switching settings history entries.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.15 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Login As search now uses a more reliable AJAX request flow and displays a clickable result list under the user search field.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As selection mapping was hardened with case-insensitive username/email matching for typed values and picked suggestions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the Login As administrator-target restriction (regression fix), restoring ability to impersonate administrator accounts.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.14 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab now explicitly uses the single-column grid class to force one-column rendering on wide screens.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Role Switching add-on wrapper now also uses the single-column grid class for consistent one-column layout.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.13 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab layout updated to a single-column view.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons cards reordered alphabetically (A–Z) for easier scanning.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.12 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a Cursor AI rule that enforces file organization: one file per top-level tab, and one dedicated file per add-on in the Add-ons tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Refactored Add-ons rendering so each add-on card now lives in its own file/class, while class-user-manager-tab-addons.php now acts as the orchestrator.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Preserved Add-ons behavior (collapsible cards, dynamic templates, and toggle logic) while splitting implementation across dedicated files.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.11 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved Role Switching out of its top-level tab and into the Add-ons area.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Legacy ?tab=role-switching links now resolve to the Add-ons tab for backward compatibility.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons cards now auto-collapse when inactive and can be expanded from each card header.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.10 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Login As: replaced the large "Select User" dropdown with an AJAX search field that searches by username or email address.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As: user search now loads matches on demand (instead of preloading all users), improving performance on large sites.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As: start-session handling now resolves typed username/email values server-side when no hidden user ID is selected.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.9 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new top-level "Add-ons" tab in the User Manager navigation next to Settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved these cards out of Settings and into Add-ons: My Account Site Admin, Bulk Add to Cart, Checkout Pre-Defined Addresses, Custom WP-Admin Notifications, WP-Admin Bar Menu Items, WP-Admin CSS, and API.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated settings save handling so Settings and Add-ons each save only their own fields and return to the correct tab after save.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.8 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings → Bulk Add to Cart: expanded this area with clear shortcode usage ([bulk_add_to_cart]) and front-end debug parameter instructions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart: added front-end URL debug switch ?um_bulk_add_to_cart_debug=1 to force verbose diagnostics for CSV processing.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart processing fixes: robust CSV header normalization (including UTF-8 BOM handling), safer quantity parsing, improved product lookup (ID/SKU/slug/title/meta for products and variations), and improved variation add-to-cart behavior using parent + variation attributes.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.7 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Admin Orders: fixed approve flow to handle status updates inline and display WooCommerce success/error notices without redirecting after output begins.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added URL cleanup after approve notices to remove nonce/action query args so page refresh does not re-trigger approval actions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.6 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Site Admin (Order Viewer): added checkbox "Default all new orders into a payment pending status" under order approval settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, new WooCommerce orders are defaulted to Pending payment, and payment-complete transitions remain Pending payment until manually approved.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.5 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Site Admin settings: added a "Show Meta Data area" checkbox for each viewer (Orders, Products, Coupons, Users). These are off by default.', 'user-manager'); ?></li>
							<li><?php esc_html_e('My Account Site Admin settings: added "Order approval allowed usernames (comma-separated)" under the Order Viewer settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Admin: Orders endpoint now shows an "Approve" button for pending payment orders for allowed approvers; approving changes order status from Pending payment to Processing with nonce checks and notices.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Improved search behavior across all My Account Site Admin lists (Orders, Products, Coupons, Users) with broader matching and filtered results.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.4 <span>(February 22, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Site Admin: fixed endpoint/menu initialization timing so custom My Account admin endpoints register reliably across plugin load orders.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added explicit rewrite endpoint + query var registration for admin_orders, admin_products, admin_coupons, and admin_users to improve endpoint detection.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added front-end debug URL parameter for administrators: append ?um_my_account_admin_debug=1 to a My Account URL to view a diagnostic panel with endpoint/query/menu/access details.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Improved allowed username parsing for My Account Site Admin access lists so standard WordPress logins, including email-style usernames, are handled correctly.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.2.3 <span>(February 22, 2026)</span></h4>
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
		<?php
	}
}

