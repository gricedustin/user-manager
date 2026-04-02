<?php
/**
 * Plugin Name: User Experience Manager
 * Description: User Experience Manager for B2B/B2C WooCommerce sites, built to improve admin and front-end user experience across welcome emails, bulk user management, dynamic coupon management, and workflow tools via tabs (Create User, Bulk Create, Reset Password, Remove User, Login As, Email Users, Settings, Reports, Add-ons, Documentation).
 * Version: 2.5.14
 * Author: Grice Projects
 * Author URI: https://griceprojects.com
 * 
 * Changelog:
 * 
 * 2.5.14 - March 16, 2026
 * - Hardened add-on runtime gating so key hook registrations only run when their add-on is active (including URL-based temporary disable support): Checkout Address Selector, New User Coupons, Quick Search, User Coupon Notifications cart-empty coupon cleanup, and Staging/Development Overrides.
 * - Prevents inactive add-ons from registering related runtime hooks and eliminates lingering side effects when an add-on is turned off.
 *
 * 2.5.13 - March 16, 2026
 * - Fixed staging/dev webhook-delivery filter callback signature to accept WooCommerce versions that pass 3 arguments, preventing fatal "Too few arguments" exceptions during webhook-triggering order flows.
 *
 * 2.5.12 - March 16, 2026
 * - User Coupon Remaining Balances: moved remainder generation off synchronous checkout status transitions and onto a short deferred WP-Cron event to avoid checkout/order-processing interruptions.
 * - Added per-order processing lock for remainder generation to prevent duplicate processing/race conditions when both thank-you/status hooks can fire close together.
 *
 * 2.5.11 - March 16, 2026
 * - User Coupon Remaining Balances now fully respects add-on activation state before registering front-end/checkout hooks, so notice/debug/runtime output no longer runs when the add-on is turned off.
 * - Added defensive error handling around remaining-balance coupon generation to prevent checkout/order flow failures from surfacing as generic WooCommerce order-processing errors.
 *
 * 2.5.10 - March 16, 2026
 * - Add-ons > User Coupon Remaining Balances: fixed Preview Email button by rendering the shared email preview modal on the Add-ons page context.
 * - Preview now correctly opens for the selected template in "Send Email to User when New Remaining Balance Code is Created", including %COUPONCODE% and [coupon_code] placeholder substitution.
 *
 * 2.5.9 - March 16, 2026
 * - Email Templates: added per-template "Recreate" links under Import Demo Email Templates so each demo template can be re-seeded individually.
 * - Added secure individual template import actions for all demo email templates, including automated coupon and remaining-balance coupon templates.
 *
 * 2.5.8 - March 16, 2026
 * - Import Demo Email Templates now includes a new "Send automated remaining balance coupon" template.
 * - Added default subject/body for the new template with %COUPONCODE% support ("You have a remaining balance").
 *
 * 2.5.7 - March 16, 2026
 * - Added new Restricted Access add-on with controls for logged-out behavior (My Account redirect, WP-Admin redirect, or full-screen overlay), shared password access, appended URL-string access, timed re-authentication, and role-based blocking for logged-in users.
 * - Added configurable restricted-access overlay options for custom no-access message, background color, text color, and optional background image with centered layout.
 *
 * 2.5.6 - March 16, 2026
 * - Order Received Page Customizer: improved H1 override reliability by adding multiple fallback hooks (page title, checkout thank-you title, template title, and thank-you heading output) to support different WooCommerce/theme rendering paths.
 * - Existing custom paragraph override remains active via woocommerce_thankyou_order_received_text.
 *
 * 2.5.5 - March 16, 2026
 * - "Edit Email Templates" shortcut links now open Add-ons > Send Email with the Email Templates panel auto-expanded.
 *
 * 2.5.4 - March 16, 2026
 * - Send Email > Saved Lists table: action buttons (Edit, CSV, Delete) now render one per line for clearer vertical alignment.
 *
 * 2.5.3 - March 16, 2026
 * - Send Email add-on is now hard-pinned on: Add-ons save logic, runtime checks, and add-on URL-disable overrides no longer disable send-email-users.
 * - Add-ons UI now keeps Send Email fields visible even without an activate checkbox so template tools remain reliably available.
 *
 * 2.5.1 - March 16, 2026
 * - Coupon edit screen: fixed Email List Converter JavaScript to avoid mutating/replacing WooCommerce's native Allowed Emails field, preventing edit-screen loading/spinner conflicts.
 * - Converter now safely reads/writes both native and select2 customer_email field variants without renaming core inputs.
 *
 * 2.4.99 - March 16, 2026
 * - Reports tab: added a reusable Start Date / End Date filter under the Query summary for reports that include date-based fields.
 * - Date filters now further narrow matching report results and preserve filter values through pagination and CSV export links.
 *
 * 2.4.98 - March 16, 2026
 * - Reports label update: retitled "Coupon Audit" to "Coupons Audit" in report naming/documentation copy.
 *
 * 2.4.97 - March 16, 2026
 * - Reports tab: added a dynamic "Query summary" description panel under the Select report dropdown.
 * - Every General Report now has a human-readable query description shown when selected, clarifying what records/conditions each report uses.
 *
 * 2.4.96 - March 16, 2026
 * - Login Tools > Login As now includes a note that temporary passwords are automatically restored after 15 minutes if not manually restored.
 * - Added automatic Login As session expiry restoration on normal page loads, so original user password hashes are reinstated after the 15-minute window.
 *
 * 2.4.95 - March 16, 2026
 * - Documentation tab was refreshed to match current product structure, including the Login Tools grouping, dedicated Blocks tab, and updated add-on/block references.
 * - Added separate Blocks Reference documentation cards and updated installation/about feature lists to reflect active tabs, add-ons, and blocks.
 *
 * 2.4.94 - March 16, 2026
 * - Renamed "Media Library Tags & Photo Gallery" to "Dynamic Photo Gallery with Media Library Tags".
 * - Moved this gallery/tag add-on from Add-ons into the dedicated Blocks tab.
 *
 * 2.4.93 - March 16, 2026
 * - Added a new top-level Blocks tab (page=user-manager&tab=blocks) next to Add-ons and moved the four Page Block add-ons into this dedicated content-block area.
 * - Blocks now use content-focused labels/tags/descriptions (for example Icons, Grids, Tabs, Navigation, Content) to improve discoverability.
 *
 * 2.4.92 - March 16, 2026
 * - [tag-description] placeholder output now appends an "Edit Tag Description" link for WordPress administrators, linking directly to the active Library Tag edit screen.
 * - The admin edit link is added only to front-end content placeholder output; title and document title placeholder replacements remain plain text.
 *
 * 2.4.91 - March 16, 2026
 * - [tag-name]/[tag-description] placeholder replacement now also runs for the HTML document title (<title>) via WordPress title filters.
 * - URL-resolved Library Tag placeholders now stay consistent across page headers, page body content, and browser/page title output.
 *
 * 2.4.90 - March 16, 2026
 * - Media Library Tag Gallery "Style" options are now sorted A-Z in the Add-ons settings section.
 * - Added gallery "Accent Color (frames/backgrounds)" setting to control white frame/background surfaces (for example Polaroid layout and split-screen panels) for dark mode compatibility.
 *
 * 2.4.88 - March 16, 2026
 * - Rebuilt Mosaic Grid into a deterministic repeat pattern (large, tall, wide, standard) with consistent auto-row sizing for cleaner alignment.
 * - Mosaic now uses dense packing with controlled spans and full-height image fill to reduce dead space and improve vertical rhythm.
 *
 * 2.4.87 - March 16, 2026
 * - URL tag override now supports multi-tag logic in the gallery query: ?tag=tag+tag2 requires BOTH tags (AND), while ?tag=tag_tag2 matches EITHER tag (OR).
 * - Multi-tag expressions are also supported for "Allow Any URL Parameter..." mode via query keys (for example: ?tag+tag2 or ?tag_tag2).
 * - [tag-name] and [tag-description] placeholders continue to resolve from the first matched tag in multi-tag URL expressions.
 *
 * 2.4.86 - March 16, 2026
 * - Lightbox (front end): administrators now see an "Edit image" shortcut when opening a gallery image in lightbox mode.
 * - The shortcut links directly to that attachment's edit screen in Media Library for faster image data updates.
 *
 * 2.4.85 - March 16, 2026
 * - Refined Mosaic Grid tile-span pattern to reduce stranded gaps where smaller square items should backfill open spaces.
 * - Mosaic span behavior is now column-aware and avoids complex row/column spanning on mobile for more reliable packing.
 *
 * 2.4.84 - March 16, 2026
 * - Simplified Media Library Tag Gallery pagination markup to basic links without button-like styling.
 * - Current gallery page number now renders as bold text.
 *
 * 2.4.83 - March 16, 2026
 * - Added a front-end wp-admin top bar shortcut ("Edit Library Tag") when a Media Library Tag Gallery URL tag is active.
 * - Shortcut links directly to the matching Library Tag edit screen and is shown only to users allowed to edit that term.
 *
 * 2.4.82 - March 16, 2026
 * - Media Library Tag Gallery defaults: added desktop column threshold settings for albums with fewer than 50, 25, and 10 photos.
 * - Gallery rendering now auto-adjusts desktop columns by attachment count thresholds (<50, <25, <10), while existing Number of Columns (Desktop) remains the default for 50+ photos.
 *
 * 2.4.81 - March 16, 2026
 * - Added [tag-description] placeholder support for post title/content replacement when Media Library gallery URL tag override is active.
 * - [tag-description] resolves from the matched Library Tag description and falls back to empty text when no valid URL tag is present.
 * - Updated the "Allow Any URL Parameter..." setting note to include both [tag-name] and [tag-description] behavior.
 *
 * 2.4.80 - March 16, 2026
 * - Fixed [tag-name] replacement in post title/content by ensuring block config detection does not cache too early before singular query context is available.
 * - Treat the "Allow Any URL Parameter..." toggle as enabling URL tag placeholder replacement behavior (including when ?tag= is not explicitly enabled).
 *
 * 2.4.79 - March 16, 2026
 * - Media Library Tag Gallery URL override: [tag-name] placeholder now supported in post title/content when URL override is enabled on the page's gallery block.
 * - [tag-name] resolves to the active URL tag name and safely falls back to empty text when no valid URL tag is present.
 * - Added this behavior note under the "Allow Any URL Parameter..." block setting help text.
 *
 * 2.4.78 - March 16, 2026
 * - Media Library Tag Gallery block: added "Allow Any URL Parameter to Be Used as a Tag Identifier such as ?tag-name for Shorter URLs" under URL override controls.
 * - When enabled, URL query keys that match Library Tag slugs (e.g. ?my-tag) can act as tag overrides, in addition to the standard ?tag=... parameter.
 *
 * 2.4.77 - March 16, 2026
 * - Media Library Tag Gallery block: added "Do Not Allow Empty Tag / Do Not Load without Tag Value" toggle under URL tag override settings.
 * - When enabled, the block returns no gallery output unless a tag is provided (from block setting or URL override).
 *
 * 2.4.76 - March 16, 2026
 * - Media Library "No tags" filter fix: remove conflicting Library Tags tax clauses/query vars before applying the no-tags filter.
 * - This ensures "No tags" returns untagged media items instead of "No media items found." in list and grid views.
 *
 * 2.4.75 - March 16, 2026
 * - Media Library Tags gallery Sort Order: added "Random" option in add-on defaults and block-level controls.
 * - Gallery rendering now supports random ordering when Sort Order is set to Random.
 *
 * 2.4.74 - March 16, 2026
 * - Media Library filter copy update: changed "No Tags" label to "No tags".
 * - Fixed the "No tags" filter query so it reliably returns only attachments without any Library Tags (instead of empty results).
 *
 * 2.4.73 - March 16, 2026
 * - Media Library Tags add-on: added setting to keep the Media Library bulk-tools header visible on mobile while scrolling.
 * - When enabled, Media Library mobile toolbar/router stays sticky so Bulk Select controls remain accessible without scrolling back to the top.
 *
 * 2.4.72 - March 16, 2026
 * - Media Library tag filter: added a new "No Tags" option to show only attachments that do not have any Library Tags.
 * - "No Tags" filter now works in both list view and media grid/ajax view using a taxonomy NOT EXISTS query.
 *
 * 2.4.71 - March 16, 2026
 * - Settings tab: added "Show profile/edit user helper notice" checkbox under User Experience.
 * - Profile/Edit User notice is now hidden by default unless this new setting is enabled.
 *
 * 2.4.70 - March 16, 2026
 * - Media Library Tags add-on: added "Show Tags on Thumbnails when Bulk Selecting" setting to display existing tag labels on media thumbnails while bulk-select mode is active.
 * - Media Library Tags add-on: added "Tags to hide from front end gallery" (comma-separated) to exclude matching Library Tag slugs from all front-end gallery output.
 * - Added help example for using a tag slug like "hide" to keep tagged images from appearing in front-end galleries.
 *
 * 2.4.69 - March 16, 2026
 * - Media Library Tags & Photo Gallery: added gallery defaults for "Description Display" (none, centered under photo, lightbox under photo, both) and "Description Value" (caption, filename, title, description, alt text, slug, date).
 * - Media Library Tag Gallery block: added matching block-level controls with per-setting "Use add-on default" toggles for Description Display and Description Value.
 * - Gallery rendering now supports descriptions under photos and/or in lightbox captions using the selected description source value.
 *
 * 2.4.68 - March 16, 2026
 * - Media Library bulk tag input placeholder updated from "enter tag" to "or enter tag" in list and grid bulk-apply controls.
 * - Adjusted Media Library bulk-select toolbar alignment so Apply Tag select/input/button sit slightly lower and align better with Delete/Cancel controls.
 *
 * 2.4.67 - March 16, 2026
 * - Media Library Tags copy updates: changed "All Library Tags" to "All tags", "Bulk apply: choose Library Tag" to "Apply Tag", "or enter new Library Tag" to "enter tag", and "Apply Library Tag" to "Apply Tag(s)".
 * - Media Library bulk-apply tag controls are now hidden until Bulk Select mode is active (list and grid views), then shown while selecting media.
 *
 * 2.4.66 - March 16, 2026
 * - Media Library Tag Gallery block editor: added per-setting "Use add-on default" toggles for columns (desktop/mobile), sort order, file size, style, page limit, and link target.
 * - When enabled for a setting, that block option now inherits the configured add-on default value and disables local override in the inspector.
 * - Front-end rendering now honors each per-setting default toggle, so defaults can be centrally updated without editing every block.
 *
 * 2.4.65 - March 16, 2026
 * - Media Library Tags (grid/select mode): kept custom bulk controls visible in the media toolbar by using dedicated classes/styles instead of hidden default attachment-filters styling.
 * - Bulk apply dropdown and "or enter new Library Tag" text input now stay visible and usable while selecting media items in grid view.
 *
 * 2.4.64 - March 16, 2026
 * - Media Library Tags: in the media-item Library Tags editor, added clickable quick-link tags above the description text.
 * - Clicking a tag link auto-inserts it into that media item's Library Tags field (without duplicating existing tags).
 *
 * 2.4.63 - March 16, 2026
 * - Media Library Tags: fixed bulk-assign controls visibility on Media Library "All items" views where post_type can be empty.
 * - Bulk apply dropdown/button now render in list view when browsing upload.php with no explicit post_type, matching the tag filter behavior.
 *
 * 2.4.62 - March 16, 2026
 * - Media Library Tags: fixed Library Tag filter visibility on Media Library "All items" views by allowing empty post_type contexts on upload.php.
 * - Tag filter dropdown and bulk controls now reliably render on upload.php list views even when WordPress does not pass post_type=attachment.
 *
 * 2.4.61 - March 16, 2026
 * - Media Library Tags & Photo Gallery: added legacy style options back into Style dropdown: Standard, Square CSS Crop, Wide Rectangle CSS Crop, Tall Rectangle CSS Crop, and Circle CSS Crop.
 * - Restored save validation and render support for these legacy style keys so previously configured galleries continue to work as expected.
 *
 * 2.4.60 - March 16, 2026
 * - Fixed Bulk Add to Cart shortcode warnings by initializing missing variables before output/render use:
 *   - $product_id_column_header
 *   - $show_sample_csv
 *   - $show_sample_with_data
 * - Prevents undefined variable warnings on frontend shortcode rendering.
 *
 * 2.4.59 - March 16, 2026
 * - Media Library Tag Gallery block: added a block-level setting to allow URL parameter override via ?tag=[tag_name].
 * - When enabled on a block instance, the URL tag parameter overrides the block-selected Library Tag (if the tag exists); when disabled, block tag selection remains unchanged.
 *
 * 2.4.58 - March 16, 2026
 * - Media Library Tags & Photo Gallery: updated Style options to 12 layouts: Mosaic Grid (Irregular Tiles), Masonry / Pinterest, Uniform Grid, Justified Row, Carousel / Slider, Fullscreen Lightbox Grid, Horizontal Scroll, Polaroid / Scrapbook, Split Screen Feature, Infinite Scroll, 3D Perspective, and Timeline / Story.
 * - Added matching front-end rendering/CSS/JS behaviors for each of these gallery styles, including carousel navigation, split-screen thumbnail switching, infinite reveal-on-scroll, timeline metadata layout, and fullscreen/lightbox interactions.
 *
 * 2.4.57 - March 16, 2026
 * - Renamed add-on to "Media Library Tags & Photo Gallery".
 * - Added new setting: "Activate Media Library Tag Gallery Block".
 * - Added gallery default settings for all gallery blocks: desktop/mobile columns, sort order, file size, style, page limit, and link target.
 * - Added new block for posts/pages to display images filtered by Library Tag (or All by default) using these defaults.
 *
 * 2.4.56 - March 16, 2026
 * - Added new Add-on: "Media Library Tags" with Activate checkbox and description.
 * - Adds a "Library Tags" taxonomy menu under Media, media-library filter dropdown (list/grid), bulk tag assignment, and per-item tag edit controls in attachment details.
 *
 * 2.4.55 - March 16, 2026
 * - Product Search by SKU add-on is now OFF by default on fresh installs (requires explicit activation).
 *
 * 2.4.54 - March 16, 2026
 * - Staging & Development non-production front-end notice now renders at the top of the page (via wp_body_open) and no longer appears as a footer bar.
 * - Added a footer fallback injector that inserts the same notice at the top of <body> for themes that do not call wp_body_open.
 *
 * 2.4.53 - March 16, 2026
 * - Data Anonymizer > Exceptions to Above: added "Exclude All WP Administrators" checkbox (checked by default).
 * - Data Anonymizer > Exceptions to Above: added "Exclude User if Email Address Matches Administration Email Address" checkbox (checked by default).
 * - Users anonymization now skips matching users when these new exception options are enabled and logs skip counts in run notes/history.
 *
 * 2.4.52 - March 16, 2026
 * - Hotfix: prevented duplicate WordPress block registration for custom/tabbed-content-area by guarding block registration with WP_Block_Type_Registry checks.
 * - Added the same duplicate-registration guard for the legacy alias custom/legacy-tabbed-content-area.
 *
 * 2.4.51 - March 16, 2026
 * - Hotfix: removed duplicate declaration of User_Manager_Core::$staging_dev_notice_rendered that caused a fatal redeclare error.
 *
 * 2.4.50 - March 16, 2026
 * - Add-ons tag filter: added a new "Security" tag in the sub-navigation and positioned it directly after "Users".
 * - Reviewed and updated security-related add-on descriptions so they are discoverable via the Security filter.
 *
 * 2.4.49 - March 16, 2026
 * - Edit User/Profile notice: added a new "Login As This User" button next to Reset Password.
 * - The new button opens Login Tools > Login As, auto-fills the current user's email, and auto-triggers "Generate Temporary Password".
 *
 * 2.4.48 - March 16, 2026
 * - Added new Add-on: "Staging & Development Environment Overrides" with Activate toggle and default-on safety settings.
 * - Added non-production overrides to disable emails, payment gateways, webhooks, and external API/JSON requests (configurable by checkbox).
 * - Added configurable non-production notices for front-end and WP-Admin, with optional Data Anonymized timestamp suffix based on Data Anonymizer history.
 *
 * 2.4.47 - March 16, 2026
 * - Added new Add-on: "Data Anonymizer" with Activate toggle and configurable anonymization settings for Orders, Users, and Form Submissions.
 * - Added "Run Data Anonymizer" card with three run actions (Orders, Users, Forms) that execute using the currently checked settings.
 * - Added "Data Anonymizer History" card that logs runner, run type, settings snapshot, affected counts, notes, and supported form-plugin table mappings.
 *
 * 2.4.46 - March 16, 2026
 * - Add-ons > User Coupon Remaining Balances: added new "Send Email to User when New Remaining Balance Code is Created" setting.
 * - Added template controls under that setting: Select Email Template (+ shortcut), a default-template option, and Preview Email.
 * - New remaining-balance emails now support both %COUPONCODE% and [coupon_code] variables and use Email Settings sender headers (From/Reply-To).
 *
 * 2.4.45 - March 16, 2026
 * - Tabbed Content Area editor: each tab now supports both a Page/Post selection dropdown and a manual Page/Post ID field.
 * - Rendering now prioritizes manual Page/Post ID when both manual ID and dropdown selection are saved.
 * - Added editor-side option loading for pages/posts so tab content can be selected without manually typing IDs.
 *
 * 2.4.44 - March 16, 2026
 * - Settings > User Experience: retitled "Legacy/Broken Shortcodes to No-op (comma-separated)" to "Legacy/Broken Shortcodes (comma-separated)".
 *
 * 2.4.43 - March 16, 2026
 * - Settings > User Experience: added "Legacy/Broken Shortcodes to No-op (comma-separated)" as a normal setting with help text.
 * - Reintroduced runtime registration of empty handlers for configured legacy shortcode tags so removed shortcode sources do not break old content.
 *
 * 2.4.42 - March 16, 2026
 * - Removed the "Legacy/Broken Shortcodes to No-op (comma-separated)" setting from Page Block: Tile Grid for Subpages.
 * - Removed legacy no-op shortcode registration code and related saved-setting handling.
 *
 * 2.4.41 - March 16, 2026
 * - Removed all "mybrand" references from the new Page Block add-ons and updated related block/shortcode identifiers accordingly.
 * - Updated identifiers to neutral names (for example: custom/subpages-grid, custom/tabbed-content-area, custom/simple-icon, custom/menu-tiles, and [subpages_grid]).
 * - Kept legacy tabbed-content block compatibility while avoiding duplicate registrations.
 *
 * 2.4.40 - March 16, 2026
 * - Added four new add-ons with Activate toggles for Page Blocks: Tile Grid for Subpages, Tabs with Content from Other Pages, Simple Icons, and Tile Grid for Menu.
 * - Added runtime-gated block registrations for custom/subpages-grid, custom/tabbed-content-area (+ legacy custom/tabbed-content-area), custom/simple-icon, and custom/menu-tiles.
 * - Added the [subpages_grid] shortcode and optional legacy shortcode no-op setting, plus block-editor UI script hooks for each new page-block add-on.
 *
 * 2.4.39 - March 16, 2026
 * - My Account Admin Orders: added two new settings under "Hide Order Status" to optionally add WebToffee invoice action buttons.
 * - Added "Add WebToffee WooCommerce PDF Invoices Print Invoice Button" and "Add WebToffee WooCommerce PDF Invoices Download Invoice Button" toggles.
 * - When enabled, Admin Orders rows now pull WebToffee invoice action URLs from WooCommerce account-order actions and render Print Invoice / Download Invoice buttons in the custom button area.
 *
 * 2.4.38 - March 16, 2026
 * - My Account Admin Orders search now supports direct order IDs, order numbers with/without "#" prefixes, and partial order-number matching.
 * - Added sequential-order-number meta search support (including common "_order_number" style meta keys) for "Sequential Order Numbers Pro" style values.
 * - Expanded Admin Orders search matching to include order-number meta fields and normalized search variants for better partial matching.
 *
 * 2.4.37 - March 16, 2026
 * - Fixed early translation loading notice by hardening add-on runtime label translation to never run before the init hook.
 * - Added explicit plugin textdomain loading on init for the user-manager domain.
 *
 * 2.4.36 - March 16, 2026
 * - Added new Add-on: "Add to Cart Min/Max Quantities" with an Activate toggle in Add-ons.
 * - Adds WooCommerce product Inventory fields for "Minimum quantity" and "Maximum quantity" (per product).
 * - Enforces min/max quantity rules during add-to-cart validation and cart/checkout quantity validation notices.
 *
 * 2.4.35 - March 16, 2026
 * - Post Meta Viewer add-on: added role-based access controls ("Allowed Roles") to decide which roles can view the meta box.
 * - Post Meta Viewer add-on: added username/email allow-list ("Allowed Usernames/Emails"), one per line.
 * - Access matching now supports role OR username/email logic, and defaults to allow all post editors when both lists are empty.
 *
 * 2.4.34 - March 16, 2026
 * - Post Meta Viewer add-on: added a post type checkbox list so admins can limit the meta box to selected post types.
 * - Default behavior remains enabled for all post types when no specific selections are saved.
 *
 * 2.4.33 - March 16, 2026
 * - Reports > Admin Log: removed the "Add-ons Connected to Admin Log" card.
 * - The Activity Log table and filters remain available; only the add-ons summary panel was removed.
 *
 * 2.4.32 - March 16, 2026
 * - Deactivate User(s): added a new "Deactivated Users History" card under the deactivated users list.
 * - The new history keeps a persistent running log of deactivation and reactivation events (with date, action, user, identifier, before/after values, and actor).
 * - Deactivate and Reactivate actions now append entries to this history so previous status changes remain visible even after reactivation.
 *
 * 2.4.31 - March 16, 2026
 * - Deactivate User(s): input now accepts usernames in addition to email addresses.
 * - Deactivate User(s): added a per-user Reactivate button in the "Deactivated Users" table.
 * - Reactivation now clears deactivation flags and restores login/email values (with uniqueness safeguards) so accounts can sign in again.
 *
 * 2.4.30 - March 16, 2026
 * - Added a sub-navigation spacing override so cards/layout wrappers directly under `.subsubsub` no longer add extra top gap.
 * - This includes wrappers like `.um-admin-grid`, `.um-admin-card`, `.um-create-user-layout`, and `.um-email-templates-layout` when they appear immediately below sub-navigation.
 *
 * 2.4.29 - March 16, 2026
 * - Add-ons tab: removed extra top spacing beneath the add-on tag sub-navigation by clearing top margin on the top-level add-on grids/cards.
 *
 * 2.4.28 - March 16, 2026
 * - Retitled the throttle count label from "Texts Per Batch" to "Emails/Texts Per Batch" in Settings.
 *
 * 2.4.27 - March 16, 2026
 * - Removed the standalone "Import Automated Coupon Email" card from Send Email.
 * - Merged coupon template imports into "Import Demo Email Templates" so one import now includes both coupon templates.
 * - Updated the demo email import list to show all 6 templates, including coupon-focused entries with %COUPONCODE% support.
 *
 * 2.4.26 - March 16, 2026
 * - Moved "Import Demo SMS Text Templates" into the SMS Text Templates manager and placed it at the bottom of that panel.
 * - Removed the duplicate "Import Demo SMS Text Templates" card from the surrounding Send SMS Text add-on wrapper.
 *
 * 2.4.25 - March 16, 2026
 * - Moved "Import Demo Email Templates" into the Email Templates manager and placed it at the bottom of that panel.
 * - Removed the duplicate "Import Demo Email Templates" card from the surrounding Send Email add-on wrapper.
 *
 * 2.4.24 - March 16, 2026
 * - Removed the legacy top-level "Send Email" navigation tab (`tab=email-users`) now that Send Email is managed as an add-on.
 * - Send Email remains available from Add-ons and optional add-on main-navigation shortcuts.
 *
 * 2.4.23 - March 16, 2026
 * - Moved "Import Demo SMS Text Templates" from Tools into the Send SMS Text add-on area.
 * - SMS demo template imports now include automated coupon + $10 apology coupon SMS templates (with %COUPONCODE% support).
 * - SMS import actions submitted from the Send SMS Text add-on now redirect back to that same add-on context with success notices.
 *
 * 2.4.22 - March 16, 2026
 * - Moved "Import Demo Email Templates" and "Import Automated Coupon Email" into the Send Email add-on area.
 * - Import actions posted from the Send Email add-on now redirect back to that same add-on context with success notices.
 * - Removed those two email import cards from Tools to avoid duplicate management locations.
 *
 * 2.4.21 - March 16, 2026
 * - Removed "Email Templates" and "SMS Text Templates" sub-links from Settings after moving both template managers into their add-ons.
 * - Legacy Settings template URLs now redirect to the relevant add-on cards (Send Email or Send SMS Text).
 * - Template shortcut links now open the add-on template managers directly from Email/SMS template selector fields.
 *
 * 2.4.20 - March 16, 2026
 * - Added a new "Send Email" add-on card with Activate toggle and description, so Send Email is no longer shown by default unless enabled.
 * - Main navigation now shows the Send Email tab only when the Send Email add-on is active.
 * - Added an "Email Templates" manager card at the top of the Send Email add-on, collapsed by default and auto-expanded when editing a specific template.
 * - Added an "SMS Text Templates" manager card at the top of the Send SMS Text add-on, collapsed by default and auto-expanded when editing a specific SMS template.
 * - Template manager forms now preserve add-on context so save/edit/delete/reorder actions return to the corresponding add-on card.
 *
 * 2.4.19 - March 16, 2026
 * - Retitled add-on shortcut checkbox label from "Add as Man Navigation Tab" to "Add to Main Navigation".
 *
 * 2.4.18 - March 16, 2026
 * - Added shortcut edit links next to Email Template selectors so admins can jump directly to Settings → Email Templates.
 * - Added shortcut edit links next to SMS Text Template selectors so admins can jump directly to Settings → SMS Text Templates.
 * - Applied template-editor shortcut links across Create, Bulk Create, Reset Password, Email Users, coupon-email template selectors, and SMS texting template selectors.
 *
 * 2.4.17 - March 16, 2026
 * - Add-ons: each add-on now shows an "Add to Main Navigation" checkbox next to Activate when the add-on is enabled.
 * - Main navigation: selected + active add-ons now appear as shortcut tabs to the right of Docs, linking directly to each add-on settings screen.
 * - Add-on shortcut choices are now saved in plugin settings and automatically hidden when an add-on is not active.
 *
 * 2.4.16 - March 16, 2026
 * - Order Invoice & Approval: on front-end invoice pages, logged-in WordPress administrators now see an "Edit this order in WP Admin" link at the bottom.
 * - The edit-order link opens in a new browser tab/window and is hidden for non-administrator viewers.
 *
 * 2.4.15 - March 16, 2026
 * - Send SMS Text: removed "skip on no user match" behavior so valid phone numbers are still sent even when no user is found.
 * - Send SMS Text: improved user lookup by phone using flexible format matching (e.g. 952-200-7732, 9522007732, +19522007732).
 * - Updated SMS send notices to report "Sent without user match" rather than "Skipped (no user match)".
 *
 * 2.4.14 - March 16, 2026
 * - Tools: added a new "Import Demo SMS Text Templates" card next to "Import Demo Email Templates".
 * - Added backend import handler and action for demo SMS templates with nonce and capability checks.
 * - Added success notice feedback after importing demo SMS text templates.
 *
 * 2.4.13 - March 16, 2026
 * - Login Tools: added a new "Deactivate User(s)" sub-menu next to Remove User(s) with bulk email-based deactivation workflow.
 * - Deactivate User(s): preserves account/history data while blocking future logins via a deactivated-user authentication guard.
 * - Deactivate User(s): added optional quiet password reset + optional [YYYYMMDD]-deactivated- login/email prefix behavior (both configurable in Settings).
 * - Deactivate User(s): added a new "Deactivated Users" card with a paginated table of all deactivated accounts.
 *
 * 2.4.12 - March 16, 2026
 * - Login Tools sub-navigation labels were updated for clarity: Create Single User, Create Multiple Users, Reset Password(s), Remove User(s), and Login As a User.
 *
 * 2.4.11 - March 16, 2026
 * - Fixed an early translation-loading notice by preventing add-on runtime labels from being translated during pre-init settings bootstrap.
 * - Add-on runtime toggle labels are now translated only when needed in UI contexts, avoiding _load_textdomain_just_in_time warnings.
 *
 * 2.4.10 - March 16, 2026
 * - Settings > API Keys: added a new "Simple Texting API Token" setting for SMS sending.
 * - Settings sub-navigation: added "SMS Text Templates" next to Email Templates, including full SMS template management.
 * - Add-ons: added a new "Send SMS Text" add-on with Activate toggle and a texting workflow modeled after Email Users (phone numbers, template selection, login URL, coupon code, preview, recent texts, and shared custom lists).
 * - Added SMS send + next-batch handlers with support for "Send to all phone numbers even if they are not users."
 * - Updated throttling labels to include texting and enabled throttle/batch behavior for SMS sends using the same throttle settings.
 *
 * 2.4.9 - March 16, 2026
 * - Email Users > Saved Lists: added a CSV button in each list row to download that entire saved list as a CSV file.
 * - Added a secure admin-post export handler for Saved Lists CSV downloads (capability check + nonce validation).
 *
 * 2.4.8 - March 16, 2026
 * - Navigation: added a new top-level "Login Tools" tab and moved Create, Bulk Create, Reset Pass, Remove, and Login As into a sub-navigation under it.
 * - Login Tools now defaults to the Create screen when opening the plugin (Login Tools -> Create).
 * - Added two Login Tools sub-links at the end: "Recent Logins" and "More Reports", both linking to Reports > User Logins.
 *
 * 2.4.7 - March 16, 2026
 * - Reports: added a new "Orders Still Processing but have a Tracking Number" report in tab=reports.
 * - New report filters order notes to only processing orders whose notes contain "with tracking number", helping surface potentially stuck orders that already have tracking details.
 * - Added CSV export support for the new processing-with-tracking-number report.
 *
 * 2.4.6 - March 16, 2026
 * - View Website by Role Permission: changed "Default Roles" to a single-selection "Default Role" dropdown on user profile permissions.
 * - View Website by Role Permission: added a new per-user "Roles to Hide" checkbox list so selected roles are hidden from that user's front-end role switcher.
 * - Role Switching enforcement: hidden roles are now blocked in both switcher display and POST handling (including reset-to-default behavior).
 *
 * 2.4.5 - March 16, 2026
 * - Documentation: added a new Troubleshooting sub-link with practical isolation steps and URL-parameter guidance.
 * - Added temporary URL overrides to disable add-ons per request: ?um_disable_all_addons=1 for all add-ons, or ?um_disable_addons=slug1,slug2 for specific add-ons.
 * - Documentation > Troubleshooting now includes a checkbox URL builder to generate disable-all and comma-separated add-on-disable test URLs.
 *
 * 2.4.4 - March 16, 2026
 * - Add to Cart Variation Table: added minimum total quantity validation with customizable JavaScript alert messaging and optional success alert before continuing.
 * - Added new "Cart Total Items" add-on with Activate toggle, customizable copy, cart/checkout visibility controls, and above/below placement settings for each area.
 * - Added new "Order Received Page Customizer" add-on with Activate toggle and settings to override the Order Received heading and success paragraph text.
 *
 * 2.4.3 - March 15, 2026
 * - Add-ons tile list: removed green active-state text, border, and shadow styling in the "Choose an Add-on" area.
 *
 * 2.4.2 - March 15, 2026
 * - Add to Cart Variation Table: changed front-end totals label to "Total" and added a new "Hide Totals Row" setting.
 *
 * 2.4.1 - March 15, 2026
 * - Hotfix: removed duplicate bulk_add_to_cart_get_product_id_column_header() declaration to prevent fatal redeclare error.
 *
 * 2.4.0 - March 15, 2026
 * - Release 2.4.0: includes recent admin tab ordering and add-ons tag navigation updates.
 *
 * 2.3.54 - March 15, 2026
 * - Add-ons tag filter: added a new "Orders" tag in the sub-navigation for order-related add-ons.
 *
 * 2.3.53 - March 15, 2026
 * - Main tab order updated so Settings appears before Reports.
 *
 * 2.3.52 - March 15, 2026
 * - Documentation sub-menu order updated so About appears before Versions.
 *
 * 2.3.51 - March 15, 2026
 * - Documentation > Support: updated support request link to https://simplewebhelp.com/inquiries/?ref=uxm.
 *
 * 2.3.50 - March 15, 2026
 * - Documentation > About: expanded Long Description and Feature List to be significantly more detailed, including exhaustive core feature coverage and a complete add-on inventory.
 *
 * 2.3.49 - March 15, 2026
 * - Create User tab: updated the "Create New User" card form to a two-column field layout to reduce vertical height while preserving all existing fields and behavior.
 *
 * 2.3.48 - March 15, 2026
 * - Documentation sub-menu order updated so Versions appears before About.
 *
 * 2.3.47 - March 15, 2026
 * - Moved "Allow WooCommerce front-end product search to include SKUs" from Settings into a new Add-ons card: "Product Search by SKU" with standard Activate toggle and description.
 *
 * 2.3.46 - March 15, 2026
 * - Reports > Admin Log: added an "Add-ons Connected to Admin Log" panel that lists every add-on with status, quick links, and per-tool match counts, plus an add-on tool filter in the log table.
 * - Documentation tab: added new subsections before Versions (Installation, About, Support), including auto-loaded screenshots from /assets/documentation-screenshots when image files exist.
 *
 * 2.3.45 - March 15, 2026
 * - Add-ons tag navigation: added a new "Pages" tag (A-Z sorted) and mapped Page Creator into this filter.
 *
 * 2.3.44 - March 15, 2026
 * - Retitled the "Bulk Page Creator" add-on to "Page Creator" across Add-ons labels, card headings, notices, documentation references, and related activity log naming.
 *
 * 2.3.43 - March 15, 2026
 * - Order Invoice & Approval: added a live "currently allowed emails" list under approval-email settings, combining global list entries and user-profile checkbox-enabled emails with Edit User links when available.
 *
 * 2.3.42 - March 15, 2026
 * - Retitled the "Invoice Approval" add-on to "Order Invoice & Approval" across Add-ons labels, documentation references, and related settings/profile headings.
 *
 * 2.3.41 - March 15, 2026
 * - Webhook URLs add-on: expanded all Webhook Types notes with detailed field-level guidance and full sample URLs that include every currently supported field for each webhook type.
 *
 * 2.3.40 - March 15, 2026
 * - Plugin Tags & Notes: fixed the "Tags & Notes" row action to reliably open the inline tags/notes text box editor on Plugins screen rows.
 *
 * 2.3.39 - March 15, 2026
 * - Page Creator: moved the Page Data + Create Pages action into its own card above History and removed the "Latest Run Details" section.
 *
 * 2.3.38 - March 15, 2026
 * - Page Creator: added top margin/breathing room above the "Page Creator History" card.
 *
 * 2.3.37 - March 15, 2026
 * - My Account Admin > Orders: ensured the Decline / "Move to Canceled" action button is shown next to other status action buttons on order views.
 *
 * 2.3.36 - March 15, 2026
 * - Added new "Order Invoice & Approval" add-on with invoice branding/settings controls, invoice approval form settings, and WooCommerce order invoice links.
 * - Order Invoice & Approval now supports per-user invoice approval access via Edit User checkbox (email-match based), in addition to the global approval email list setting.
 *
 * 2.3.35 - March 15, 2026
 * - Added new "Webhook URLs" add-on with Activate toggle plus full settings for debug mode, URL parameter handling, and individual webhook type activation.
 * - Added front-end webhook router/handlers for create/edit orders, create/edit coupons, reset password, send email, and placeholders for user/post/product/category hooks.
 *
 * 2.3.34 - March 15, 2026
 * - Added new "Database Table Browser" add-on with Activate toggle and records-per-page setting.
 * - Database Table Browser now lists all tables, supports secure table drill-down with nonce checks, and renders paginated table rows directly in Add-ons.
 *
 * 2.3.33 - March 15, 2026
 * - Added new "Page Creator" add-on with Activate toggle, OpenAI generation controls, bulk Title|Prompt input, and create action in the Add-ons tab.
 * - Page Creator now reuses the existing API key from Settings > API Keys, supports optional image downloads/featured image assignment, and stores run history.
 *
 * 2.3.32 - March 15, 2026
 * - Added new "Cart Price Per-Piece" add-on with Activate toggle and settings for cart/order display, suffix text, font size, and text color.
 * - When active, unit pricing now appears under WooCommerce line subtotals for multi-quantity items on cart, checkout, and customer order views.
 *
 * 2.3.31 - March 15, 2026
 * - Added new "My Account Menu Tiles" add-on with an Activate toggle and settings for desktop tiles per row plus minimum tile height.
 * - When active, My Account dashboard now renders menu endpoints as responsive tile buttons below the default dashboard text.
 *
 * 2.3.30 - March 15, 2026
 * - Added new "Plugin Tags & Notes" add-on with an Activate toggle in the Add-ons tab.
 * - When active, wp-admin/plugins.php now includes per-plugin tags/notes badges, inline editors, a bulk Save All form, and client-side tag filtering tools.
 *
 * 2.3.29 - March 15, 2026
 * - Documentation tab: added a new "All Reports (Reports Tab Reference)" card listing every Reports section and all General Reports currently available.
 *
 * 2.3.28 - March 15, 2026
 * - Add-ons UI: Add-ons Filter now has extra spacing below it and only displays on the all add-on tiles view (not inside individual add-on settings screens).
 *
 * 2.3.27 - March 15, 2026
 * - Added new "Security Hardening" add-on with an Activate toggle and granular hardening checkboxes for REST user endpoint blocking, file-edit/file-mod restrictions, forced SSL admin, and WordPress version hiding.
 * - Added Security Hardening coverage to Add-ons metadata, settings persistence, and documentation/use-case references.
 *
 * 2.3.26 - March 15, 2026
 * - Simplified plugin description by removing the long inline add-ons list from the header metadata.
 *
 * 2.3.25 - March 15, 2026
 * - Added a Settings shortcut link to the plugin row actions on the WordPress Plugins screen.
 * - Added quick shortcut links for each main tab in the plugin row meta area next to version/author details.
 *
 * 2.3.24 - March 15, 2026
 * - Removed the Front-End URL Parameter Debugger add-on and its related add-on settings/UI references.
 *
 * 2.3.23 - March 15, 2026
 * - Updated plugin author metadata from "Dustin Grice" to "Grice Projects".
 *
 * 2.3.22 - March 15, 2026
 * - Added a new Add-ons Filter card on tab=addons with keyword filtering and clear/reset behavior for add-on tiles and add-on settings screens.
 * - Added a new Documentation Filter card on tab=documentation with keyword filtering across documentation cards and use cases.
 * - Added a new Versions Filter card on docs_section=versions with keyword filtering across changelog items.
 *
 * 2.3.21 - March 15, 2026
 * - Documentation tab: replaced legacy cards with a fully refreshed Tabs Reference, Add-ons Reference, and updated platform overview.
 * - Documentation tab: added a new "Use Cases" card with practical B2B/B2C scenarios (including welcome-coupon onboarding) to highlight how tools can be combined.
 *
 * 2.3.20 - March 15, 2026
 * - WP-Admin top bar shortcut: changed the User Experience Manager link target to open the Add-ons tab by default instead of Settings.
 *
 * 2.3.19 - March 15, 2026
 * - Add to Cart Variation Table: added a JavaScript confirmation alert before submitting the cart-screen "Empty cart" button.
 *
 * 2.3.18 - March 15, 2026
 * - Add to Cart Variation Table: changed the default "Add to Cart Variation Table Button Text" fallback from "Add All Variations" to "Add to Cart".
 * - Add to Cart Variation Table: updated related settings/help text so blank button text now clearly defaults to "Add to Cart".
 *
 * 2.3.17 - March 15, 2026
 * - Rebranded plugin title/author metadata to "User Experience Manager" by Dustin Grice (griceprojects.com) and refreshed the plugin description to emphasize B2B/B2C user experience outcomes.
 * - Add-ons activation defaults: removed default-on behavior so add-ons are not auto-activated on first install unless explicitly enabled.
 *
 * 2.3.16 - March 15, 2026
 * - Add to Cart Variation Table: added two new settings to override the header labels for the Variation and Qty columns.
 * - Add to Cart Variation Table header row now uses custom Variation/Qty labels when provided, and falls back to defaults when blank.
 *
 * 2.3.15 - March 15, 2026
 * - Add-ons UI: added top margin above the "Add to Cart Variation Table History" card for clearer visual spacing.
 *
 * 2.3.14 - March 15, 2026
 * - Add to Cart Variation Table: added a new checkbox setting "Add an Empty Cart button on Cart Screen".
 * - When enabled, an Empty Cart button now renders in WooCommerce cart actions and empties the cart via nonce-protected submission.
 *
 * 2.3.13 - March 15, 2026
 * - Add to Cart Variation Table: added a category filter setting "Only display variation table for products in these categories" with a scrollable checkbox list of product categories.
 * - Add to Cart Variation Table front-end rendering now respects selected product categories; when no categories are selected, it remains available to all variable products.
 *
 * 2.3.12 - March 15, 2026
 * - Add to Cart Variation Table: added a new checkbox setting to remove the table header row (Variation / Qty) on the front end.
 * - Add to Cart Variation Table now conditionally renders the header row based on the new hide-header setting.
 *
 * 2.3.11 - March 15, 2026
 * - Add to Cart Variation Table: added a new text setting to override the front-end "Add All Variations" button label.
 * - Add to Cart Variation Table button now uses the custom label when set, and falls back to "Add All Variations" when blank.
 *
 * 2.3.10 - March 15, 2026
 * - Add to Cart Variation Table: added a new setting "Prefix all variations with the variation label" (default off) to control label formatting like "Size: Small" versus "Small".
 * - Add to Cart Variation Table front-end row rendering now respects the prefix-label setting for the Variation column.
 *
 * 2.3.9 - March 15, 2026
 * - Add to Cart Variation Table: removed the default "Add Multiple Variations" heading/description copy from the front-end table output.
 * - Add to Cart Variation Table: added "Add Text Above Variation Table" and "Add Text Below Variation Table" textarea settings (HTML supported) and render those blocks on the front end.
 * - Add-ons: added a new "Add to Cart Variation Table History" card showing timestamp, who submitted, total items added, and variation/option details for each bulk add run.
 *
 * 2.3.8 - March 15, 2026
 * - Added a new Fatal Error Debugger add-on with Activate toggle and an admin-only front-end fatal error panel.
 * - Fatal Error Debugger now captures fatal shutdown errors, stores the latest payload, and can send email alerts only when "Sent Email To Address upon Fatal Errors" is filled.
 *
 * 2.3.7 - March 15, 2026
 * - Add to Cart Variation Table trace stability: added defensive function-exists guards for auth checks to prevent fatal errors during early plugin bootstrap.
 *
 * 2.3.6 - March 15, 2026
 * - Add to Cart Variation Table: removed strict WooCommerce class-load gating so render/submission hooks are always registered when the add-on is active.
 * - Add to Cart Variation Table: added safety fallback render hooks so the table still appears when a theme override skips the selected hook.
 * - Add to Cart Variation Table: added front-end trace diagnostics via URL parameter ?um_variation_table_trace=1 (admin-only), including hook registration state and render skip reasons.
 *
 * 2.3.5 - March 15, 2026
 * - Add to Cart Variation Table: added a backend "Single Product Page Hook" selector so the render location can be chosen per site/theme.
 * - Add to Cart Variation Table: added Auto hook mode that tries multiple WooCommerce product hooks for better front-end compatibility when a single hook does not fire.
 *
 * 2.3.4 - March 15, 2026
 * - Add to Cart Variation Table: added a new settings checkbox to optionally show a third Price column in the variation table.
 * - Add to Cart Variation Table: Totals row now dynamically updates both total quantity and total amount when the Price column option is enabled.
 *
 * 2.3.3 - March 15, 2026
 * - Add to Cart Variation Table: switched to a vertical two-column layout (Variation + Qty), removed price output, added a live Total row, and kept it as a separate alternative form under the native add-to-cart area.
 * - Add to Cart Variation Table: added setting to hide/show the native variable-product dropdown add-to-cart form when the bulk table is present.
 * - Add to Cart Variation Table: added debug mode setting and front-end debug output for Add All Variations processing details.
 *
 * 2.3.2 - March 15, 2026
 * - My Account Admin Orders: added configurable button labels for Approve/Decline (default labels now "Move to Processing" and "Move to Canceled"), hid Approve when already Processing, and hid Decline when already Canceled.
 * - My Account Admin Orders action handling now blocks redundant approve/decline actions for already-Processing and already-Canceled orders, with corresponding notices.
 *
 * 2.3.1 - March 15, 2026
 * - Add to Cart Variation Table: refactored as an alternative form under the default Add to Cart area with an "Add All Variations" submit flow and optional front-end debug mode.
 * - My Account Admin Orders: added status filter configuration, inline status filter links, optional hide-order-status toggle, approve/decline actions for all non-completed statuses, and internal order notes that record who performed each action and when.
 * - My Account Admin Order additional meta field format now supports "meta_field:Label:prefix_before_value", with URL auto-linking and "Open File" link text for prefixed file URLs.
 *
 * 2.3.0 - March 8, 2026
 * - Release 2.3.0: bundles recent Add-ons, Bulk Add to Cart, My Account Admin, and WP-Admin CSS improvements from the 2.2.9x series.
 *
 * 2.2.96 - March 8, 2026
 * - Bulk Add to Cart shortcode: switched "Download Sample CSV" to a direct CSV download endpoint so line breaks are preserved reliably.
 * - Bulk Add to Cart sample CSV now includes a blank line after the header row and keeps the same headers as the product-data sample.
 *
 * 2.2.95 - March 8, 2026
 * - Bulk Add to Cart shortcode: "Download Sample CSV" now uses the same header columns as "Download Sample CSV with Product Data" (identifier, quantity, product_title, product_variation).
 *
 * 2.2.94 - March 8, 2026
 * - Bulk Add to Cart shortcode UI: changed sample download controls to text links and moved them above the "Select CSV File" upload field.
 *
 * 2.2.93 - March 8, 2026
 * - My Account Admin add-on UI: indented sub-settings for Product Viewer, Coupon Viewer, and User Viewer to match the Order Viewer layout.
 *
 * 2.2.92 - March 8, 2026
 * - WP-Admin CSS hide preset dropdown fix: allowed top-bar submenu wrappers are now hidden by default and shown only on hover/focus.
 * - WP-Admin CSS JS fallback now preserves collapsed submenu state and toggles visibility only during interaction.
 *
 * 2.2.91 - March 8, 2026
 * - WP-Admin CSS hide preset fix: CSS output now renders as raw CSS (not HTML-escaped), so advanced selectors apply correctly.
 * - WP-Admin CSS hide preset fallback: added JS-based hide pass after DOM load for admin screens that render/refresh layout after initial head CSS.
 *
 * 2.2.90 - March 8, 2026
 * - WP-Admin CSS preset hardening: increased selector coverage/specificity and reinforced !important rules so the admin sidebar/top-bar hide preset applies more consistently across wp-admin screens.
 *
 * 2.2.89 - March 8, 2026
 * - WP-Admin CSS add-on: added a new preset card to hide wp-admin sidebar/top-bar chrome (while preserving profile/logout and custom top-bar menu items).
 * - WP-Admin CSS preset targeting: added usernames/emails field and role checkbox targeting (OR logic) for applying the preset to specific users.
 *
 * 2.2.88 - March 8, 2026
 * - Add to Cart Bulk Import: added a dedicated history report card in Add-ons with Timestamp, User Email, Media Library file link, Total Items Added, Number of Errors, and View More details.
 * - Add to Cart Bulk Import history now stores confirmation notification messages and per-line detail messages for exact View More reporting.
 *
 * 2.2.87 - March 8, 2026
 * - My Account Coupons Page add-on: added a reminder and direct link to resave Permalinks after activation so the endpoint is registered.
 *
 * 2.2.86 - March 8, 2026
 * - Add-ons UI: retitled "My Account Coupon Screen" to "My Account Coupons Page".
 *
 * 2.2.85 - March 8, 2026
 * - Add-ons UI: retitled "Checkout Pre-Defined Addresses" to "Checkout Address Selector".
 *
 * 2.2.84 - March 8, 2026
 * - Add-ons UI: retitled "Coupon for New User" to "New User Coupons", "Coupon Notifications for Users with Coupons" to "User Coupon Notifications", and "Coupon Remaining Balances" to "User Coupon Remaining Balances".
 *
 * 2.2.83 - March 8, 2026
 * - Add-ons UI: retitled "My Account Site Admin" to "My Account Admin".
 *
 * 2.2.82 - March 8, 2026
 * - Add-ons UI: retitled "Coupon Remaining Balances (Simple Gift Card & Store Credit Functionality)" to "Coupon Remaining Balances".
 *
 * 2.2.81 - March 8, 2026
 * - Add-ons UI: standardized all primary add-on toggle labels to a simple "Activate" checkbox while preserving each card description text beneath it.
 *
 * 2.2.80 - March 8, 2026
 * - Add-ons navigation: removed the "All Add-ons" shortcut from the new sub-navigation list.
 * - Add-ons layout fix: added a clear break after the floated subsubsub list to prevent add-on cards from rendering in an off-screen side column.
 * - Add-ons section default: when no addon_section is provided, the first add-on section now opens by default.
 *
 * 2.2.79 - March 8, 2026
 * - Add-ons focused section safety fix: all add-on cards now remain in the form markup while non-selected sections are visually hidden, preventing unrelated add-on settings from being cleared on save.
 *
 * 2.2.78 - March 8, 2026
 * - Add-ons tab: added a subsubsub-style add-on navigation list to quickly jump to a specific add-on section.
 * - Add-ons tab: added per-section filtering so selecting an add-on shows only that add-on card while keeping Save Add-ons available.
 * - Add-ons tab: active add-on links in the new navigation are bold, and saving now keeps users on the same selected add-on section.
 *
 * 2.2.77 - March 8, 2026
 * - Add-ons: added a new "My Account Coupon Screen" add-on with Activate toggle plus settings for Menu Title Name, Page Title, and Page Description.
 * - My Account: added a dedicated Coupons endpoint/tab that reuses the User Coupon Notifications coupon query and displays all matching coupons as WooCommerce-style notices on that page.
 *
 * 2.2.76 - March 8, 2026
 * - Bulk Add to Cart notice copy: changed "Line-by-line product processing" heading to "Details" and shortened each row line.
 * - Bulk Add to Cart details line format now uses "ID: ... — Added/Error (qty)" and only includes "Note: ..." when the row has an error.
 *
 * 2.2.75 - March 8, 2026
 * - Email Templates layout: default view now shows Saved Templates + Add New Template (empty form) in two columns.
 * - Email Templates layout: edit view now shows Live Preview + Edit Template in two columns and hides Saved Templates while editing.
 *
 * 2.2.74 - March 8, 2026
 * - Bulk Add to Cart: debug notice routing now preserves only the two primary user-facing Woo notices (total items + line-by-line summary) while redirecting all other processing/debug notices into the Debug Information panel.
 *
 * 2.2.73 - March 8, 2026
 * - Bulk Add to Cart: added a new WooCommerce success notification showing total items added and a View Cart button.
 * - Bulk Add to Cart: added a new line-by-line WooCommerce notification listing CSV line, product ID, product title, variation, qty added, status, and error reason when applicable.
 *
 * 2.2.72 - March 8, 2026
 * - Email Templates UI: moved Live Preview (Demo Data) above Saved Templates in editing mode so both are visible side by side.
 * - Email Templates layout: preview now appears at the top of the form column while editing.
 *
 * 2.2.71 - March 8, 2026
 * - Bulk Add to Cart debug UI: moved upload/processing notice messages into the Debug Information panel when debug mode is active.
 * - Bulk Add to Cart debug UI: added formatted notice flattening so multi-line details (like per-product result rows) display cleanly in Debug Information.
 *
 * 2.2.70 - March 8, 2026
 * - Bulk Add to Cart upload trigger fix: processing now runs when the submit field is present (even if browser posts an empty submit value), and the submit button now posts value="1" explicitly.
 * - Bulk Add to Cart shortcode UI: fixed "Download Sample CSV" button URL rendering by allowing data: protocol output.
 *
 * 2.2.69 - March 8, 2026
 * - Bulk Add to Cart debug panel now includes a line-by-line CSV processing trace showing what happened for each file row.
 * - Bulk Add to Cart uploads are now copied into Media Library with metadata describing who uploaded the file, when, and the source URL.
 * - User Activity file links for Bulk Add to Cart uploads now prefer the Media Library attachment URL when available.
 *
 * 2.2.68 - March 8, 2026
 * - Add-ons UI: retitled "Coupon Automatically Created for New User" to "Coupon for New User".
 * - Add-ons tab: re-sorted cards A→Z after the title update.
 *
 * 2.2.67 - March 8, 2026
 * - Bulk Add to Cart uploads now add a User Activity log entry with a direct URL link to the uploaded CSV file (visible under Reports → User Activity).
 *
 * 2.2.66 - March 8, 2026
 * - Bulk Add to Cart upload processing: rows with blank/zero quantity are now skipped (not treated as errors), which better supports product-data sample CSV workflows.
 * - Bulk Add to Cart debug improvements: added richer upload/request diagnostics and processing summary details for faster troubleshooting.
 * - Bulk Add to Cart shortcode now prints WooCommerce notices in-place so upload results are visible even on non-WooCommerce pages.
 *
 * 2.2.65 - March 8, 2026
 * - Bulk Add to Cart CSV parser: now ignores blank rows anywhere in the file, including leading blank rows before the header row.
 *
 * 2.2.64 - March 8, 2026
 * - Bulk Add to Cart shortcode UI: added "Download Sample CSV with Product Data" to export all product + variation IDs with quantity defaulted to 0, plus informational product title and variation columns.
 * - Bulk Add to Cart uploader compatibility: product title / variation columns remain informational and are ignored during upload because only identifier and quantity columns are parsed.
 *
 * 2.2.63 - March 8, 2026
 * - Bulk Add to Cart shortcode UI: removed the optional debug URL bullet from the "How to Use" list.
 * - Bulk Add to Cart shortcode UI: added a "Download Sample CSV" button that generates a sample file using the configured identifier and quantity column names.
 *
 * 2.2.62 - March 8, 2026
 * - Bulk Add to Cart: fixed front-end shortcode registration so [bulk_add_to_cart] still registers when WooCommerce loads after User Manager.
 * - Bulk Add to Cart shortcode now shows a clear WooCommerce-required message if WooCommerce is unavailable.
 *
 * 2.2.61 - March 8, 2026
 * - Settings tab: cards are now sorted alphabetically (A–Z) on load, while keeping API Keys pinned as the final card.
 *
 * 2.2.60 - March 8, 2026
 * - Admin card layout: removed max-height constraint from .um-admin-card so fields remain contained and cards grow with content.
 *
 * 2.2.59 - March 8, 2026
 * - Admin card styling: removed global overflow-y:auto from .um-admin-card-body so card content no longer forces internal vertical scrolling.
 *
 * 2.2.58 - March 8, 2026
 * - My Account Site Admin: removed redundant helper description line under activation area.
 * - My Account Site Admin: indented all "My Account Admin Order Viewer" sub-settings for clearer parent/child hierarchy.
 *
 * 2.2.57 - March 8, 2026
 * - Admin UI cleanup: removed remaining in-card vertical scroll wrappers so card content expands naturally.
 *
 * 2.2.56 - March 8, 2026
 * - Add-ons UI: retitled "Coupon Bulk Creator" to "Coupon Creator" and "Bulk Add to Cart" to "Add to Cart Bulk Import".
 *
 * 2.2.55 - March 8, 2026
 * - Add-ons UI: added short always-visible descriptions under activation checkboxes where the add-on purpose was only shown inside hidden settings.
 *
 * 2.2.54 - March 8, 2026
 * - Add-ons tab: re-sorted add-on cards A→Z.
 * - Add-ons save wiring: cards moved in order now submit settings via form attributes while preserving dynamic template row saves.
 *
 * 2.2.53 - March 8, 2026
 * - Add-ons UI: removed remaining nested-card wrappers for embedded Blog Post Importer and Post Idea Generator sections in Add-ons.
 *
 * 2.2.52 - March 8, 2026
 * - Add-ons UI: Post Idea Generator tool now renders embedded content inside its add-on card (no nested inner card).
 *
 * 2.2.51 - March 8, 2026
 * - My Account Site Admin: added role-based access checkboxes under each username allow-list (Orders, Products, Coupons, Users, and Order Approval).
 * - My Account Site Admin access control now supports username OR role matching for viewer access and order approvals.
 *
 * 2.2.50 - March 8, 2026
 * - Add-ons UI: Coupon Automatically Created for New User, Bulk Add to Cart, Coupon Notifications for Users with Coupons, and Coupon Remaining Balances now hide their settings blocks unless the add-on is active.
 * - Add-ons live toggle behavior: those four cards now show/hide settings immediately when activation checkboxes are changed.
 *
 * 2.2.49 - March 8, 2026
 * - Add-ons UI: moved Blog Post Importer and Post Idea Generator functionality into their respective cards (Post Content Generator / Post Idea Generator) instead of separate bottom panels.
 * - Add-ons save flow: API and Post Idea activation/settings fields now remain savable from their in-card placement.
 *
 * 2.2.48 - March 8, 2026
 * - Updated coupon remainder notice setting labels from "if Coupon is Applied" wording to "Code Used".
 *
 * 2.2.47 - March 8, 2026
 * - Add-ons migration: restored both migrated tool sections from the old Tools tab for Post Content Generator (Blog Post Importer) and Post Idea Generator.
 * - Add-ons: linked migrated tool sections now toggle reliably from their corresponding activation checkboxes so they consistently display when enabled.
 *
 * 2.2.46 - March 8, 2026
 * - Add-ons save reliability: fixed submit-action reset logic so "Save Add-ons" consistently posts to user_manager_save_settings.
 * - Add-ons form now safely defaults to settings save on Enter-key submits unless an explicit action button (e.g., bulk coupon create) initiated the submit.
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
