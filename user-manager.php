<?php
/**
 * Plugin Name: User Manager
 * Description: Comprehensive user + coupon automation for WooCommerce: create/reset users (single, CSV, paste, SFTP), per-login history, customizable email templates, login-triggered per-user coupon cloning with storefront notifications, custom user meta imports, activity logging, and optional rebranded “set password” UX.
 * Version: 2.2.3
 * Author: Grice AI
 * Author URI: 
 * 
 * Changelog:
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
