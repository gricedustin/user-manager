<?php
/**
 * Plugin Name: User Manager
 * Description: Comprehensive user + coupon automation for WooCommerce: create/reset users (single, CSV, paste, SFTP), per-login history, customizable email templates, login-triggered per-user coupon cloning with storefront notifications, custom user meta imports, activity logging, and optional rebranded “set password” UX.
 * Version: 2.2.45
 * Author: Grice AI
 * Author URI: 
 * 
 * Changelog:
 * 
 * 2.2.45 - March 8, 2026
 * - Add-ons save flow: persisted all Bulk Coupons form values when using "Save Add-ons" (template code, totals, emails, amount override, prefix/suffix, length, expiration options, send-email toggle, and selected template).
 * - Add-ons form cleanup: aligned Bulk Coupons setting keys so save behavior is consistent whether using "Save Add-ons" or coupon-create actions.
 *
 * 2.2.44 - March 8, 2026
 * - Add-ons: removed duplicate Post Idea Generator section rendering on the Add-ons page.
 * - Removed the "Bulk Coupons is currently disabled in Add-ons..." notice message.
 *
 * 2.2.43 - March 8, 2026
 * - Add-ons: ensured Post Content Generator and Post Idea Generator migrated sections are always mounted and toggle reliably from their activation checkboxes.
 * - Add-ons: Blog Post Importer and Post Idea Generator tool areas now show/hide live when toggles are changed, improving visibility and migration reliability from Tools.
 *
 * 2.2.42 - March 8, 2026
 * - Refactor: split large Core activity-log/detail methods into a dedicated module trait file for cleaner organization.
 * - Refactor: split large Reports tracking numbers/notes render+export methods into a dedicated reports module trait file.
 * - Refactor: split Content Generator / Blog Importer action handlers into a dedicated actions module trait file.
 * - Refactor: split My Account Site Admin endpoint renderer/list/detail methods into a dedicated renderer module trait file.
 *
 * 2.2.41 - March 8, 2026
 * - Admin Log "View Details" now includes an "All Logged Form Data" section that recursively displays all stored entry fields.
 * - Added nested key rendering for activity detail payloads so values from arrays/objects are visible with full key paths.
 * - Added sensitive-field masking in details output for keys containing password, API key, token, secret, nonce, authorization, or cookie.
 *
 * 2.2.40 - March 8, 2026
 * - Fixed Add-ons reindexing for WP-Admin Bar Menu Items so all field types (including the new side selector) keep correct names after removing rows.
 * - User Role Switching add-on: replaced full get_users() load with paginated WP_User_Query output in the "Users with Role Switching Access" table for better performance.
 * - Reports: replaced SQL_CALC_FOUND_ROWS/FOUND_ROWS() in Orders Tracking Notes with a separate COUNT(*) query for better compatibility and scaling.
 *
 * 2.2.39 - March 8, 2026
 * - Retitled add-on card "WP-Admin Quick Search Bar" to "WP-Admin Bar Quick Search" (including activation label text).
 * - Re-sorted Add-ons cards A–Z by the current displayed card titles.
 * - Updated Docs feature label to match the new quick search add-on name.
 *
 * 2.2.38 - March 8, 2026
 * - WP-Admin Bar Menu Items add-on: each custom menu now has a "Top Bar Side" option (Left or Right).
 * - Custom top bar menus now render on the selected side (`root-default` for left, `top-secondary` for right).
 * - Menu side selection is saved per menu item and defaults to Right for backward compatibility.
 *
 * 2.2.37 - March 8, 2026
 * - Docs tab documentation view now uses a single-column layout instead of two columns.
 * - Updated docs layout wrapper to use the same single-column grid style used by other admin areas.
 *
 * 2.2.36 - March 8, 2026
 * - User Role Switching add-on now includes the same top-level Activate checkbox pattern used by other add-ons.
 * - Role Switching settings/history area now hides when the add-on is deactivated and reappears when activated.
 * - Add-ons tab JS now toggles the Role Switching settings area based on activation state.
 *
 * 2.2.35 - March 8, 2026
 * - Added explicit Activate checkboxes to add-ons that previously relied on content presence: My Account Site Admin, WP-Admin Notifications, WP-Admin Bar Menu Items, and WP-Admin CSS.
 * - Add-ons save flow now stores these activation toggles and Add-ons cards show/hide their settings areas based on activation state.
 * - Added runtime gating so disabled add-ons do not output admin notifications, admin bar custom menus, WP-Admin CSS, or My Account Site Admin features.
 *
 * 2.2.34 - March 8, 2026
 * - Add-ons retitles: Quick Search Bar → WP-Admin Quick Search Bar, Coupons for New Users → Coupon Automatically Created for New User, and Blog Post Idea Generator → Post Idea Generator.
 * - Updated related add-on labels/messages to match the new naming.
 * - Re-sorted Add-ons cards A–Z by the current card titles.
 *
 * 2.2.33 - March 8, 2026
 * - Settings > General Settings: moved coupon-related options out of User Experience into a new "Coupons" card.
 * - Settings > General Settings: moved post-meta-related options into a new "Post Meta" card.
 * - User Experience card now focuses on UX/search behavior while coupon and post meta controls are grouped in dedicated cards.
 *
 * 2.2.32 - March 8, 2026
 * - Moved the "Display Quick Search Bar" control from Settings into Add-ons as a dedicated "Quick Search Bar" card.
 * - Add-ons now saves the Quick Search Bar toggle, and General Settings saves no longer overwrite this add-on setting.
 * - Quick Search Bar remains enabled by default unless explicitly disabled.
 *
 * 2.2.31 - March 8, 2026
 * - Settings filter now uses keyword search only (removed the "Filter by area" dropdown).
 * - Retained auto-expand behavior while searching so matching cards/fields open automatically.
 * - Clearing the keyword filter returns Settings cards to the default collapsed state.
 *
 * 2.2.30 - March 8, 2026
 * - Retitled Add-ons card names for clarity: Role Switching → User Role Switching, Custom WP-Admin Notifications → WP-Admin Notifications, Bulk Coupons → Coupon Bulk Creator, and ChatGPT Content Generator → Post Content Generator.
 * - Updated matching activation labels for renamed add-ons.
 * - Synced documentation labels to the new add-on names.
 *
 * 2.2.29 - March 8, 2026
 * - Settings tab cards now load collapsed by default for a cleaner first view.
 * - When a Settings filter is active (area or keyword), matching cards auto-expand to show results immediately.
 * - Clearing filters returns cards to the default collapsed state.
 *
 * 2.2.28 - March 8, 2026
 * - Fixed Login As user search results not appearing for valid email searches.
 * - Updated Login As AJAX user lookup to support WP_User_Query partial-field result objects as well as WP_User objects.
 * - Login As type-to-search now correctly returns username/email matches again.
 *
 * 2.2.27 - March 8, 2026
 * - Added a dedicated Add-ons card for "Blog Post Idea Generator" with its own Activate checkbox.
 * - Moved Blog Post Idea Generator out of the ChatGPT Content Generator area and into its own add-on section.
 * - Blog Post Importer remains under ChatGPT Content Generator, while idea generation now has separate add-on activation.
 *
 * 2.2.26 - March 8, 2026
 * - Add-ons > ChatGPT Content Generator: added an Activate checkbox to enable/disable this add-on.
 * - Moved Blog Post Importer and Blog Post Idea Generator UI from Tools into the ChatGPT Content Generator area on the Add-ons tab.
 * - Tools sub-section under Settings now shows only utility tools (template imports and log/reset actions); blog content generation tools now live with ChatGPT settings.
 *
 * 2.2.25 - March 8, 2026
 * - Settings tab layout changed to a single-column card layout.
 * - Added a new Settings Filter panel at the top of Settings with area + keyword filters.
 * - Settings filter now lets admins isolate specific settings by card title, labels, descriptions, and field values.
 *
 * 2.2.24 - March 8, 2026
 * - Moved "Coupon Lookup by Email" out of Tools and into Reports as its own sub menu link.
 * - Reports sub-links now include: General Reports, User Activity, Admin Log, and Coupon Lookup by Email.
 * - Legacy coupon lookup URLs using tab=tools now route to Reports > Coupon Lookup by Email when coupon_lookup_email is present.
 *
 * 2.2.23 - March 8, 2026
 * - Settings tab now includes WooCommerce-style sub-links: General Settings (default), Email Templates, and Tools.
 * - Moved the former top-level Email Templates and Tools views under Settings sub-links.
 * - Legacy ?tab=email-templates and ?tab=tools URLs now open Settings and land in the correct sub-section.
 *
 * 2.2.22 - March 8, 2026
 * - Docs tab now includes WooCommerce-style sub-links: Documentation (default) and Versions.
 * - Moved Versions content into the Docs tab as a sub-section while preserving the full changelog view.
 * - Legacy ?tab=versions URLs now open Docs and land on the Versions sub-section for backward compatibility.
 *
 * 2.2.21 - March 8, 2026
 * - Reports tab now includes WooCommerce-style text sub-links: General Reports, User Activity, and Admin Log.
 * - Moved the former top-level User Activity (tab=login-history) and Admin Log (tab=activity-log) views under Reports sub-links.
 * - Added backward-compatible routing so legacy tab URLs still open Reports and land in the correct sub-section.
 *
 * 2.2.20 - March 8, 2026
 * - Settings tab: added a new "API Keys" card and moved the ChatGPT / OpenAI API Key field there.
 * - Add-ons tab: renamed the API card to "ChatGPT Content Generator".
 * - Add-ons API card now focuses on prompt/meta-box options and references Settings > API Keys for key management.
 *
 * 2.2.19 - March 8, 2026
 * - Moved Bulk Coupons into Add-ons as a dedicated add-on card with an Activate checkbox.
 * - Bulk Coupons create/template actions now run from the Add-ons page and redirect back to Add-ons with notices.
 * - Removed the top-level Bulk Coupons nav tab and route legacy ?tab=bulk-coupons requests to Add-ons.
 *
 * 2.2.18 - March 8, 2026
 * - Moved Coupons settings into three dedicated Add-ons cards: Coupons for New Users, Coupon Notifications for Users with Coupons, and Coupon Remaining Balances (Simple Gift Card & Store Credit Functionality).
 * - Coupon settings are now saved through the shared Add-ons save flow.
 * - Removed the top-level Coupons nav tab and route legacy ?tab=coupons requests to the Add-ons tab.
 *
 * 2.2.17 - March 8, 2026
 * - Add-ons tab now keeps all add-on cards collapsed by default on page load, regardless of active state.
 * - Added a clear active-state indicator in each add-on card header (status pill with dot + Active/Inactive label).
 * - Active add-ons now remain visibly highlighted even while collapsed.
 *
 * 2.2.16 - March 8, 2026
 * - Add-ons: moved Role Switching into the main alphabetical card list so it appears inline with the other add-ons.
 * - Role Switching add-on no longer renders an extra inner collapsible wrapper (removed collapse-inside-collapse UI).
 * - Role Switching settings now save through the shared Add-ons save flow while preserving Role Switching settings history logging.
 *
 * 2.2.15 - March 8, 2026
 * - Login As user search: switched to a more reliable AJAX request path and added a clickable result list under the search field.
 * - Login As search now maps selections and typed values more robustly (case-insensitive username/email matching).
 * - Login As admin-account impersonation guard was removed (regression fix) so administrator targets can be impersonated again as previously requested.
 *
 * 2.2.14 - March 8, 2026
 * - Add-ons tab now explicitly uses the single-column grid class to prevent multi-column layouts on wide screens.
 * - Role Switching add-on wrapper now also uses the single-column grid class for consistent one-column rendering.
 *
 * 2.2.13 - March 8, 2026
 * - Add-ons tab layout updated to a single-column view.
 * - Add-ons cards reordered alphabetically (A–Z) for easier scanning.
 *
 * 2.2.12 - March 8, 2026
 * - Added a Cursor AI rule to enforce tab/add-on file organization: one file per top-level tab, and one file per add-on inside the Add-ons tab.
 * - Refactored the Add-ons tab so each add-on now renders from its own file/class, with the Add-ons tab file reduced to orchestration + shared UI behavior.
 * - Preserved existing Add-ons functionality (collapsible state, templates, and dynamic settings behavior) while splitting implementation across dedicated add-on files.
 *
 * 2.2.11 - March 8, 2026
 * - Moved Role Switching out of its top-level tab and into the Add-ons area.
 * - Legacy ?tab=role-switching URLs now open Add-ons so existing bookmarks continue to work.
 * - Add-ons cards now auto-collapse when inactive and can be expanded from the card header.
 *
 * 2.2.10 - March 8, 2026
 * - Login As: replaced the large Select User dropdown with an AJAX search field that searches by username or email address.
 * - Login As: user search now loads matches on demand for better performance on large sites (e.g., 10,000+ users) instead of preloading all users.
 * - Login As: start-session handler now accepts typed username/email values and resolves them server-side when no hidden user ID is selected.
 *
 * 2.2.9 - March 8, 2026
 * - Added a new top-level "Add-ons" tab in User Manager navigation next to Settings.
 * - Moved these cards out of Settings and into Add-ons: My Account Site Admin, Bulk Add to Cart, Checkout Pre-Defined Addresses, Custom WP-Admin Notifications, WP-Admin Bar Menu Items, WP-Admin CSS, and API.
 * - Updated settings save handling so Settings and Add-ons each save only their own fields and return to the correct tab after save.
 *
 * 2.2.8 - March 8, 2026
 * - Settings → Bulk Add to Cart: expanded this area with clear shortcode usage ([bulk_add_to_cart]) and front-end debug parameter instructions.
 * - Bulk Add to Cart: added front-end URL debug switch ?um_bulk_add_to_cart_debug=1 to force verbose CSV processing diagnostics.
 * - Bulk Add to Cart processing fixes: robust CSV header normalization (including UTF-8 BOM handling), safer quantity parsing, improved product lookup (ID/SKU/slug/title/meta for products and variations), and improved variation add-to-cart behavior using parent + variation attributes.
 *
 * 2.2.7 - March 8, 2026
 * - Fixed My Account Admin Orders approve flow: approving an order now keeps the user on the current order/list view and displays a WooCommerce success/error notice instead of attempting a late redirect that could result in a blank screen.
 * - Added URL cleanup after approve notices to remove action nonce/query args from the browser URL so page refresh does not re-run the approve action.
 *
 * 2.2.6 - March 8, 2026
 * - My Account Site Admin (Order Viewer): added checkbox "Default all new orders into a payment pending status" under order approval settings.
 * - When enabled, new WooCommerce orders are defaulted to Pending payment, and payment-complete status transitions remain Pending payment until manually approved.
 *
 * 2.2.5 - March 8, 2026
 * - My Account Site Admin settings: added "Show Meta Data area" checkbox for each viewer (Orders, Products, Coupons, Users). Off by default.
 * - My Account Site Admin settings: added "Order approval allowed usernames (comma-separated)" under Order Viewer settings.
 * - Admin: Orders endpoint now shows an "Approve" button for pending payment orders to allowed approver usernames; clicking Approve moves the order to Processing (with nonce + notices).
 * - Improved search behavior in all My Account Site Admin lists (Orders, Products, Coupons, Users) with broader field matching and better result filtering.
 *
 * 2.2.4 - March 8, 2026
 * - My Account Site Admin: fixed endpoint/menu initialization timing so custom My Account admin endpoints register reliably across plugin load orders.
 * - Added explicit rewrite endpoint + query var registration for admin_orders, admin_products, admin_coupons, and admin_users to improve endpoint detection.
 * - Added front-end debug URL parameter for administrators: ?um_my_account_admin_debug=1 on My Account now shows a diagnostic panel with endpoint/query/menu/access details.
 * - Improved allowed username handling for My Account Site Admin access lists to support normal WordPress logins (including email-style usernames) without over-stripping characters.
 *
 * 2.2.3 - March 8, 2026
 * - Settings: new "My Account Site Admin" card with four viewer toggles and per-viewer comma-separated username allow lists:
 *   - My Account Admin Order Viewer
 *   - My Account Admin Product Viewer
 *   - My Account Admin Coupon Viewer
 *   - My Account Admin User Viewer
 * - WooCommerce My Account: new custom endpoints and menu links for Admin: Orders, Admin: Products, Admin: Coupons, and Admin: Users.
 * - Each My Account admin area now supports pagination, search, list views, and per-item detail views with "View" buttons.
 * - Access control: only enabled viewers are shown, and visibility is restricted to usernames listed in settings (with admins retaining access).
 *
 * 2.2.2 - February 14, 2026
 * - Settings → API: setting renamed to "Enable Page & Post Meta Box for inserting ChatGPT generated content into pages & posts". Meta box now appears on both Page and Post edit screens (previously pages only).
 * - WP-Admin top bar: new "User Manager" link (right side) that goes to the plugin Settings tab.
 *
 * 2.2.1 - February 8, 2026
 * - Tools → Blog Post Importer: WYSIWYG/HTML editor (wp_editor) for post description; post category as side-by-side checkboxes; full-width title and description; compact layout with title, date, and categories in one row above body; create posts as Gutenberg paragraph and list blocks (not classic block); success message with tiles (thumbnail max 200px, title wrap, View/Edit buttons); default Visual tab for editor; when creating posts with no date set, start with the most recent published or scheduled post date, then set every new post X days forward (first +X, second +2X, third +3X, etc.); "days" value (default 25) saved on form submit; auto-check categories when name or slug appears in title or body (server and client).
 * - Settings: new API card at bottom for ChatGPT/OpenAI API Key (password field); key saved and used for Tools → AI Prompt Support.
 * - Tools → AI Prompt Support: per-row "Topic idea" input and "Auto write from ChatGPT" button; API returns body and 3–5 titles; body inserted into that row’s editor and radio list of titles to fill Post Title.
 * - ChatGPT request error handling: server returns debug payload (HTTP code, response preview, parse errors, etc.); client shows error message and debug block in a red box per row; when 403 with body "-1" (nonce/referer failure), friendly message for dev environment (use same URL and port).
 * - Tools → Recent posts: section always visible on Tools tab; "Spread to date" date field and "Evenly spread all scheduled posts out to this date" button; AJAX reschedules all scheduled posts evenly from today to the chosen date; displays number of scheduled posts and "Recommended date" (today + scheduled count × saved days); date field pre-filled with recommended date; visible "Spread to date:" label; button uses recommended date when field is empty.
 * - Settings → API: under Appended Information to AI Prompt, checkbox "Enable Page & Post Meta Box for inserting ChatGPT generated content into pages & posts". When enabled, page and post edit screens show a meta box with topic, paragraphs (default 5), sentences per paragraph (default 5), Generate (preview), and Insert to bottom of this post (block format); usage logged to Admin Log.
 *
 * 2.2 - February 7, 2026
 * - Reorganized Settings tab General Settings into multiple admin cards for clearer organization
 *   - User & Login: default role, default login URL
 *   - Email Settings: send from name/email, reply-to, throttling
 *   - Activity & Logging: activity log, view reports, debug, admin activity
 *   - User Experience: rebrand copy, quick search, coupon meta box/column toggles
 *   - Bulk Add to Cart: activation, redirect, CSV columns, identifier type, debug
 *   - User Creation & Import: update existing users, SFTP/directory paths
 * - Settings tab uses two-column layout for all cards
 * - User Experience: new setting "Allow WooCommerce front-end product search to include SKUs"
 *   - When ?s= exactly matches a product or variation SKU, redirects to that product page
 * - Login History (User Activity) tab: new Roles column
 *   - Saves and displays the user's roles at the time the record was added; old records show empty
 * - Fixed Cart Coupon Remaining Balances: optional copy of source coupon expiration to remainder coupon
 *   - New checkbox "Copy source coupon expiration date to remainder coupon" under Order Received notice
 * - Add New User page (user-new.php): large admin notice recommending User Manager for creating users
 *   - Link to Create User in User Manager; JavaScript hides both default forms (Add Existing User + Add User)
 *   - "No thanks, I want to use the default forms" link reveals the default WordPress forms
 * - Profile and user-edit pages: admin notice at top with Open User Manager and Reset Password buttons
 *   - Reset Password link opens Reset Password tab with that user's email pre-filled in the form
 * - User Experience: "Apply Coupon Code via URL Parameter" setting
 *   - When enabled, visiting any page with the chosen parameter (default ?coupon-code=CODE) applies the coupon to the cart
 *   - Optional custom URL parameter name (letters, numbers, hyphens, underscores)
 * - Reset Password tab: "Insert Random Password" button next to New Password field to auto-fill a secure random password
 *
 * 2.1.2 - January 23, 2026
 * - Changed coupon remainder source requirements from AND to OR logic
 *   - Coupons now match if they meet ANY requirement type (Prefix OR Contains OR Suffix)
 *   - Updated all matching functions and debug output to reflect OR logic
 * - Enhanced checkout page debug output accessibility
 *   - "Enable Checkout Page Debug Output" now available to all logged-in users (not just administrators)
 * - Added new public debugging URL parameter (?debug_coupons=1)
 *   - Shows detailed coupon matching information on any page
 *   - Displays all applied coupons with matching status
 *   - Shows remaining balance calculations and notice status
 *   - Includes comprehensive matching details (prefix/contains/suffix checks)
 * - Improved debug output with detailed matching information
 *   - Shows which specific requirements matched or failed
 *   - Displays all configured prefixes, contains, and suffixes
 *   - Enhanced failure reason messages
 * - Updated settings descriptions to clarify OR logic behavior
 *   - Makes it clear that coupons match if they meet any of the three requirement types
 * - Added user role selector to Email Users tab
 *   - Scrollable list of all user roles with email counts
 *   - Checkboxes to auto-fill email addresses by role
 *   - Supports multiple role selection
 *   - Tracks selected roles in activity log
 * - Added email throttling feature
 *   - New setting: "Throttle Sending Emails to X Emails Per Page Load"
 *   - Configurable batch size to avoid triggering spam filters
 *   - Pending batch display with progress tracking
 *   - "Send Next Batch" button to continue sending remaining emails
 *   - Batches persist for 30 days, allowing delayed sending
 * - Added Custom Email Lists feature
 *   - New "Custom Email Lists" card in Email Users tab for creating and managing email lists
 *   - Create lists with custom titles and email addresses (one per line)
 *   - Edit and delete existing lists
 *   - Lists sorted alphabetically by title
 *   - "Select by List" section with checkboxes to auto-fill email addresses (similar to role selection)
 *   - Supports multiple list selection and combines with role selections
 *   - Selected lists tracked in activity log for history
 * - Added Custom Email Lists section to user edit page
 *   - Shows which lists the user belongs to
 *   - Checkboxes to easily add/remove users from lists
 *   - Changes saved when user profile is updated
 * - Added admin activity logging for Custom Email Lists operations
 *   - List creation, updates, and deletions are now tracked in Admin Log
 *   - Logs include list ID, title, email count, and change details
 * - Added Delete List button when editing lists
 *   - Separate delete form for safe list deletion
 *   - Fixed nested form issue that was causing Update List to delete lists
 */

if (!defined('ABSPATH')) {
	exit;
}

// Include tab files
require_once plugin_dir_path(__FILE__) . 'includes/class-user-manager-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-user-manager-tabs.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-user-manager-actions.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-user-manager-email.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-user-manager-my-account-site-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/checkout-ship-to-predefined.php';

require_once plugin_dir_path(__FILE__) . 'includes/coupon-notifications.php';

// Initialize the plugin
User_Manager_Core::init();
