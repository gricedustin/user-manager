<?php
/**
 * Plugin Name: User Manager
 * Description: Comprehensive user + coupon automation for WooCommerce: create/reset users (single, CSV, paste, SFTP), per-login history, customizable email templates, login-triggered per-user coupon cloning with storefront notifications, custom user meta imports, activity logging, and optional rebranded “set password” UX.
 * Version: 2.2.21
 * Author: Grice AI
 * Author URI: 
 * 
 * Changelog:
 * 
 * 2.2.21 - February 22, 2026
 * - Reports tab now includes WooCommerce-style text sub-links: General Reports, User Activity, and Admin Log.
 * - Moved the former top-level User Activity (tab=login-history) and Admin Log (tab=activity-log) views under Reports sub-links.
 * - Added backward-compatible routing so legacy tab URLs still open Reports and land in the correct sub-section.
 *
 * 2.2.20 - February 22, 2026
 * - Settings tab: added a new "API Keys" card and moved the ChatGPT / OpenAI API Key field there.
 * - Add-ons tab: renamed the API card to "ChatGPT Content Generator".
 * - Add-ons API card now focuses on prompt/meta-box options and references Settings > API Keys for key management.
 *
 * 2.2.19 - February 22, 2026
 * - Moved Bulk Coupons into Add-ons as a dedicated add-on card with an Activate checkbox.
 * - Bulk Coupons create/template actions now run from the Add-ons page and redirect back to Add-ons with notices.
 * - Removed the top-level Bulk Coupons nav tab and route legacy ?tab=bulk-coupons requests to Add-ons.
 *
 * 2.2.18 - February 22, 2026
 * - Moved Coupons settings into three dedicated Add-ons cards: Coupons for New Users, Coupon Notifications for Users with Coupons, and Coupon Remaining Balances (Simple Gift Card & Store Credit Functionality).
 * - Coupon settings are now saved through the shared Add-ons save flow.
 * - Removed the top-level Coupons nav tab and route legacy ?tab=coupons requests to the Add-ons tab.
 *
 * 2.2.17 - February 22, 2026
 * - Add-ons tab now keeps all add-on cards collapsed by default on page load, regardless of active state.
 * - Added a clear active-state indicator in each add-on card header (status pill with dot + Active/Inactive label).
 * - Active add-ons now remain visibly highlighted even while collapsed.
 *
 * 2.2.16 - February 22, 2026
 * - Add-ons: moved Role Switching into the main alphabetical card list so it appears inline with the other add-ons.
 * - Role Switching add-on no longer renders an extra inner collapsible wrapper (removed collapse-inside-collapse UI).
 * - Role Switching settings now save through the shared Add-ons save flow while preserving Role Switching settings history logging.
 *
 * 2.2.15 - February 22, 2026
 * - Login As user search: switched to a more reliable AJAX request path and added a clickable result list under the search field.
 * - Login As search now maps selections and typed values more robustly (case-insensitive username/email matching).
 * - Login As admin-account impersonation guard was removed (regression fix) so administrator targets can be impersonated again as previously requested.
 *
 * 2.2.14 - February 22, 2026
 * - Add-ons tab now explicitly uses the single-column grid class to prevent multi-column layouts on wide screens.
 * - Role Switching add-on wrapper now also uses the single-column grid class for consistent one-column rendering.
 *
 * 2.2.13 - February 22, 2026
 * - Add-ons tab layout updated to a single-column view.
 * - Add-ons cards reordered alphabetically (A–Z) for easier scanning.
 *
 * 2.2.12 - February 22, 2026
 * - Added a Cursor AI rule to enforce tab/add-on file organization: one file per top-level tab, and one file per add-on inside the Add-ons tab.
 * - Refactored the Add-ons tab so each add-on now renders from its own file/class, with the Add-ons tab file reduced to orchestration + shared UI behavior.
 * - Preserved existing Add-ons functionality (collapsible state, templates, and dynamic settings behavior) while splitting implementation across dedicated add-on files.
 *
 * 2.2.11 - February 22, 2026
 * - Moved Role Switching out of its top-level tab and into the Add-ons area.
 * - Legacy ?tab=role-switching URLs now open Add-ons so existing bookmarks continue to work.
 * - Add-ons cards now auto-collapse when inactive and can be expanded from the card header.
 *
 * 2.2.10 - February 22, 2026
 * - Login As: replaced the large Select User dropdown with an AJAX search field that searches by username or email address.
 * - Login As: user search now loads matches on demand for better performance on large sites (e.g., 10,000+ users) instead of preloading all users.
 * - Login As: start-session handler now accepts typed username/email values and resolves them server-side when no hidden user ID is selected.
 *
 * 2.2.9 - February 22, 2026
 * - Added a new top-level "Add-ons" tab in User Manager navigation next to Settings.
 * - Moved these cards out of Settings and into Add-ons: My Account Site Admin, Bulk Add to Cart, Checkout Pre-Defined Addresses, Custom WP-Admin Notifications, WP-Admin Bar Menu Items, WP-Admin CSS, and API.
 * - Updated settings save handling so Settings and Add-ons each save only their own fields and return to the correct tab after save.
 *
 * 2.2.8 - February 22, 2026
 * - Settings → Bulk Add to Cart: expanded this area with clear shortcode usage ([bulk_add_to_cart]) and front-end debug parameter instructions.
 * - Bulk Add to Cart: added front-end URL debug switch ?um_bulk_add_to_cart_debug=1 to force verbose CSV processing diagnostics.
 * - Bulk Add to Cart processing fixes: robust CSV header normalization (including UTF-8 BOM handling), safer quantity parsing, improved product lookup (ID/SKU/slug/title/meta for products and variations), and improved variation add-to-cart behavior using parent + variation attributes.
 *
 * 2.2.7 - February 22, 2026
 * - Fixed My Account Admin Orders approve flow: approving an order now keeps the user on the current order/list view and displays a WooCommerce success/error notice instead of attempting a late redirect that could result in a blank screen.
 * - Added URL cleanup after approve notices to remove action nonce/query args from the browser URL so page refresh does not re-run the approve action.
 *
 * 2.2.6 - February 22, 2026
 * - My Account Site Admin (Order Viewer): added checkbox "Default all new orders into a payment pending status" under order approval settings.
 * - When enabled, new WooCommerce orders are defaulted to Pending payment, and payment-complete status transitions remain Pending payment until manually approved.
 *
 * 2.2.5 - February 22, 2026
 * - My Account Site Admin settings: added "Show Meta Data area" checkbox for each viewer (Orders, Products, Coupons, Users). Off by default.
 * - My Account Site Admin settings: added "Order approval allowed usernames (comma-separated)" under Order Viewer settings.
 * - Admin: Orders endpoint now shows an "Approve" button for pending payment orders to allowed approver usernames; clicking Approve moves the order to Processing (with nonce + notices).
 * - Improved search behavior in all My Account Site Admin lists (Orders, Products, Coupons, Users) with broader field matching and better result filtering.
 *
 * 2.2.4 - February 22, 2026
 * - My Account Site Admin: fixed endpoint/menu initialization timing so custom My Account admin endpoints register reliably across plugin load orders.
 * - Added explicit rewrite endpoint + query var registration for admin_orders, admin_products, admin_coupons, and admin_users to improve endpoint detection.
 * - Added front-end debug URL parameter for administrators: ?um_my_account_admin_debug=1 on My Account now shows a diagnostic panel with endpoint/query/menu/access details.
 * - Improved allowed username handling for My Account Site Admin access lists to support normal WordPress logins (including email-style usernames) without over-stripping characters.
 *
 * 2.2.3 - February 22, 2026
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
