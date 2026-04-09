<?php
/**
 * Plugin Name: User Experience Manager
 * Description: User Experience Manager for B2B/B2C WooCommerce sites, built to improve admin and front-end user experience across welcome emails, bulk user management, dynamic coupon management, and workflow tools via tabs (Create User, Bulk Create, Reset Password, Remove User, Login As, Email Users, Settings, Reports, Add-ons, Documentation).
 * Version: 2.5.165
 * Author: Grice Projects
 * Author URI: https://griceprojects.com
 * 
 * Changelog:
 * 
 * 2.5.165 - March 16, 2026
 * - Add-ons tab heading updated so the non-active tile card is now labeled "Inactive Addons" to match the new Active Add-ons card.
 * - Blocks tab heading updated so the non-active tile card is now labeled "Inactive Blocks" to match the new Active Blocks card.
 *
 * 2.5.164 - March 16, 2026
 * - Plugin Title Override now also updates the plugin submenu label under Users in WP-Admin.
 * - Added a new Settings checkbox to optionally show this plugin as a top-level WP-Admin parent menu item below Users.
 * - New top-level WP-Admin parent menu item uses the Plugin Title Override value when provided.
 *
 * 2.5.163 - March 16, 2026
 * - Extended "Additional Meta Fields to Display Under Order in All Orders Screen" with optional flags support so specific fields can be marked as text-file URLs that should also show fetched line counts.
 * - Added text-file line count flag handling for additional order meta definitions (`text_line_count`, `text-file-line-count`, `line_count`, `count_lines`) and appended `(X lines)` to linked file values when enabled.
 * - Updated All Orders Screen additional-meta setting helper text with flag usage examples for text-based file line counting.
 *
 * 2.5.162 - March 16, 2026
 * - My Account Admin now includes a new setting: "Additional Meta Fields to Display Under Order in All Orders Screen" for rendering configured order meta directly in the Admin: Orders table view.
 * - Admin: Orders list now renders configured row-level additional meta fields in a dedicated full-width row below each order, improving visibility for long values/links without squeezing the main columns.
 *
 * 2.5.161 - March 16, 2026
 * - Product Notification add-on now includes a new "Notification Icon/Checkbox Color Override" setting (WordPress color picker) with a default of white (`#ffffff`).
 * - Product Notification front-end output now applies the configured icon color to `.woocommerce-message.um-product-notification-message::before` so the WooCommerce notice icon/checkmark color can be controlled independently.
 *
 * 2.5.160 - March 16, 2026
 * - Converted all plugin admin settings fields that store color values to use WordPress color picker controls across Add-ons and Blocks screens for easier color selection.
 * - Updated color-setting sanitization for My Account Admin button colors, Invoice & Approval colors, and WP-Admin Notification background colors to consistently enforce hex color values.
 * - Added shared Add-ons/Blocks color-picker initialization so dynamically added fields (for example WP-Admin Notifications rows) also receive picker UI automatically.
 *
 * 2.5.159 - March 16, 2026
 * - My Account Admin access checks were hardened so area endpoints only render for explicitly allowed usernames/roles (plus admins), preventing accidental broad access when allow-lists are empty.
 * - Added per-order-status front-end title overrides in My Account Admin Orders so each WooCommerce status label can be customized individually.
 * - Block Pages by URL String now supports multiple scoped rule sets with per-rule match URLs, exception URLs, optional username list, optional role checkboxes, and per-rule look/feel + redirect settings.
 * - Updated Block Pages by URL String color inputs to use WP color pickers for background/text colors.
 * - Added Settings > User Experience > "Plugin Title Override" to retitle both the plugin page heading and the WP-Admin bar shortcut label.
 *
 * 2.5.158 - March 16, 2026
 * - Add-ons tab now includes a new "Active Add-ons" card directly below Add-ons Filter that shows only active add-ons using the same tile layout and tag filtering.
 * - Blocks tab now includes a new "Active Blocks" card directly below Blocks Filter that shows only active blocks using the same tile layout and tag filtering.
 * - Renamed the Users submenu label from "User Experience Manager" to "UX Manager" while keeping the existing page slug and screen behavior.
 *
 * 2.5.157 - March 16, 2026
 * - Updated Tabbed Content Area block front-end mobile CSS to enforce two-column tab layout at <=768px (`.tabs .tab { width: 47% !important; }`) while keeping wrapped tab rows.
 * - Added explicit mobile `.tabs` flex-wrap rule in the Tabbed Content Area block output style for consistent two-column tab rendering on smaller screens.
 *
 * 2.5.152 - March 16, 2026
 * - Added per-selected-tag "Tag Title Override" inputs on Media > Tag Groups to customize front-end tag labels while preserving original slugs/URLs.
 * - Tag Group breadcrumb/group link rendering now uses saved tag title overrides for group members only (parent label and link targets remain unchanged).
 *
 * 2.5.151 - March 16, 2026
 * - Updated Tag Group link targets to use the current page as the base URL and append key-only tag query format (`?[tag-slug]`) instead of always linking from site root.
 * - Tag Group breadcrumb links now keep users on the same gallery/page context while switching tag expressions via key-only query keys.
 *
 * 2.5.144 - March 16, 2026
 * - Added new Media > Tag Groups admin page to create/manage tag groups with a Parent Tag and multiple related member tags.
 * - Added front-end related group links above gallery description area: when current tag belongs to a group, show links to other group tags plus a Parent Tag link.
 * - Added Bulk Editor-adjacent Media submenu entry for Tag Groups and secure save/delete handlers for group records.
 *
 * 2.5.142 - March 16, 2026
 * - Added shared CSS injection for `[um_media_library_tag_videos]` shortcode output so video grid classes always render with correct multi-column layout even when no gallery block is present on the page.
 * - Shortcode video wraps now consistently apply `.um-media-library-tag-videos-wrap-*` column rules (including `*-cols-2/3/4`) and mobile collapse behavior in standalone shortcode contexts.
 *
 * 2.5.141 - March 16, 2026
 * - Fixed Media Library Tag featured description-image lightbox trigger output by preserving generated button markup attributes in album description wrappers (including the inline open handler), so featured images open in the same lightbox flow as gallery items.
 * - Removed over-sanitization on rendered description wrapper output that was stripping required lightbox trigger attributes from generated internal markup.
 *
 * 2.5.140 - March 16, 2026
 * - Added new shortcode `[um_media_library_tag_videos]` to render centralized Video Library embeds with the same front-end video layout used in gallery blocks.
 * - Shortcode supports tag expressions (`tag1`, `tag1+tag2`, `tag1_tag2`, `tag1|tag2`) and optional desktop column override (`desktop_columns` / `columns`, 1-4).
 * - Added shortcode usage guidance to Media > Video Library admin page, including supported tag expression formats and behavior notes.
 *
 * 2.5.139 - March 16, 2026
 * - Replaced long inline lightbox trigger onclick payload with a short delegated call to the resilient `window.umMltgInline.open(...)` API to avoid browser parser edge cases from breaking click-to-open behavior.
 * - Kept event-prevent/propagation guards in the inline handler while moving state updates/rendering logic fully into the shared lightbox runtime.
 *
 * 2.5.138 - March 16, 2026
 * - Improved Media Library Tag video embed layout so video title/description metadata is rendered below a dedicated responsive frame wrapper, preventing metadata from being clipped by the iframe container.
 * - Added dynamic desktop video-grid columns for multi-video displays, automatically using up to 4 columns (2/3/4) based on the number of videos shown.
 *
 * 2.5.137 - March 16, 2026
 * - Hardened front-end lightbox/deep-link URL parsing by replacing inline regex escape snippets with a dedicated escape helper in each gallery runtime block to prevent parser edge-case breakage.
 * - Updated slideshow seconds JavaScript injection to use JSON-safe numeric output, reducing risk of malformed inline script output that can stop lightbox initialization.
 *
 * 2.5.136 - March 16, 2026
 * - Fixed tag description Featured Image lightbox trigger behavior so the featured image still opens in lightbox when Link To is set to "Open Image in Lightbox", even when duplicate featured image tiles are hidden from the gallery grid.
 * - Preserved duplicate-hiding behavior for gallery tiles while keeping the description-area featured image in the lightbox collection for direct click-to-open.
 *
 * 2.5.135 - March 16, 2026
 * - Added two new Video Library display settings under Media Library Tags: "Display Video Title under each video" and "Display Video Description under each video."
 * - Front-end Media Library Tag Gallery video embeds now optionally render each video's saved title and/or description beneath the iframe when those settings are enabled.
 *
 * 2.5.134 - March 16, 2026
 * - Added a front-end deep-link auto-open retry helper for Media Library Tag Gallery that re-attempts opening `?image=<attachment_id>` via the resilient inline lightbox API when other scripts fail later on the page.
 * - Deep-link auto-open now includes delayed retries after DOM ready/load so shared `&image=` links can still open reliably even when unrelated JavaScript errors interrupt gallery runtime initialization.
 *
 * 2.5.133 - March 16, 2026
 * - Edit Video form on Media > Video Library now shows an embedded YouTube preview directly under the YouTube Link field when the current URL is valid.
 * - Preview uses the canonical saved video URL and renders responsive iframe output so admins can verify the selected video before saving updates.
 *
 * 2.5.132 - March 16, 2026
 * - Added a Delete button in the Video Library table row action column on Media > Video Library.
 * - Added secure delete handling (nonce + capability check) so saved video records can be removed directly from the Video Library list.
 *
 * 2.5.131 - March 16, 2026
 * - Fixed an early-boot translation timing issue by deferring Media Library Video legacy migration to run on init instead of plugin bootstrap.
 * - Added an init guard around Video Library legacy migration so taxonomy registration and i18n label calls cannot execute before init.
 *
 * 2.5.130 - March 16, 2026
 * - [tag-name] placeholder replacement now supports URL title override via ?title=... so custom heading text can drive placeholder output.
 * - When a valid Media Library Tag URL override is active, title query param is sanitized and used as [tag-name] value while tag descriptions continue resolving from selected tags.
 *
 * 2.5.129 - March 16, 2026
 * - Added a new Media Library Tag Gallery setting: "Featured Image Max Width (px)" so the tag description featured image width is configurable from add-on settings instead of hardcoded CSS.
 * - Featured image description CSS now uses that setting value in the `max-width: min(42vw, Xpx)` rule.
 *
 * 2.5.128 - March 16, 2026
 * - Added new "Activate Video Library" setting under the Media Library Tag Gallery options.
 * - Added a new Media > Video Library admin page for centralized YouTube video management with Title, Description, Date, Library Tag assignment, save, and edit flows.
 * - Replaced per-tag YouTube textarea usage in Library Tags Bulk Editor and front-end gallery video embeds with centralized Video Library records (including one-time legacy term-meta migration).
 *
 * 2.5.127 - March 16, 2026
 * - Updated tag description featured-image layout to true text wrapping so description copy flows beside and beneath the image.
 * - Featured image shown next to tag description can now open in the same lightbox collection as gallery images (with shared prev/next flow).
 * - Added default-enabled setting to hide duplicate featured image tiles from tagged gallery results when that featured image is already in the tag's image collection.
 *
 * 2.5.126 - March 16, 2026
 * - Updated Media Library Tag Gallery lightbox deep-link query parameter from um_lightbox_image_id to image.
 * - Auto-open lightbox now resolves from image=<attachment_id> while remaining backward compatible with legacy um_lightbox_image_id links.
 * - Opening and closing lightbox now writes/clears the image parameter and removes legacy um_lightbox_image_id from the URL.
 *
 * 2.5.125 - March 16, 2026
 * - Added per-Library-Tag featured image selection in both the Library Tags Bulk Editor and individual Edit Tag screen.
 * - Front-end gallery tag description area now displays the selected tag featured image aligned left beside description content (blog-post featured-image style layout).
 * - Added media picker controls for tag featured image select/replace/remove with preview in bulk and edit-tag admin UIs.
 *
 * 2.5.124 - March 16, 2026
 * - Fixed Restricted Access overlay viewport coverage on mobile by enforcing full fixed inset coverage with dynamic viewport height fallbacks.
 * - Updated both immediate full-page overlay mode and background HTML overlay mode to use 100dvh/min-height fallback rules so no page background peeks through on mobile.
 *
 * 2.5.123 - March 16, 2026
 * - Added new Media Library Tag Gallery style option: "Mosaic Grid (Taller Tiles)" that reuses the mosaic layout with 50% taller base tiles.
 * - Added mosaic taller style CSS variants for desktop/mobile tile auto-row sizing while preserving existing large/tall/wide tile patterns.
 *
 * 2.5.122 - March 16, 2026
 * - Media Library Tag gallery descriptions now convert line breaks to <br> so multi-paragraph/plain-text newlines render on the front end.
 * - Allowed inline <b> and <i> formatting tags (and strong/em aliases) in rendered gallery tag descriptions while preserving safe sanitization.
 *
 * 2.5.121 - March 16, 2026
 * - Added a new Media Library Tag Gallery setting: "Allow Tap or Click on Left or Right side of image to go to Previous or Next Photo."
 * - Wired tap/click left-right image-side navigation through add-on defaults, block overrides, and both primary + resilient fallback lightbox runtimes.
 *
 * 2.5.120 - March 16, 2026
 * - Added 25px bottom margin to Media Library Tag Gallery wrapper output so each gallery block has clearer spacing below it.
 *
 * 2.5.119 - March 16, 2026
 * - Renamed Media Library sort option labels from "Lightbox Views" to "Views" in the admin dropdown UI.
 * - Fixed Media Library grid sort behavior so changing the sort dropdown applies the selected order to attachment queries in-place.
 * - Grid sort now persists sort query state in the URL without requiring a full page reload when supported.
 *
 * 2.5.118 - March 16, 2026
 * - Added lightbox deep-link support with URL parameter um_lightbox_image_id=<attachment_id> when opening Media Library Tag Gallery images.
 * - Shared links with um_lightbox_image_id now auto-open the matching image in lightbox on page load (primary and resilient fallback runtimes).
 * - Lightbox URL deep-link parameter is cleared automatically when closing the modal.
 *
 * 2.5.117 - March 16, 2026
 * - Added rolling Lightbox Views counters for current Year, Month, Week, and Day on each media item.
 * - Lightbox open tracking now increments total + current period counters in attachment metadata.
 * - Media Library attachment edit helper now displays Lightbox Views (Year/Month/Week/Day) alongside total Lightbox Views.
 *
 * 2.5.116 - March 16, 2026
 * - Fixed WP_Term-to-integer conversion warnings by hardening menu and term ID handling in Media Library Tags and Quick Search term redirects.
 * - Updated term edit-link rendering paths to normalize term IDs through absint() before building edit URLs.
 *
 * 2.5.115 - March 16, 2026
 * - Deepened Restricted Access cookie persistence by validating all matching cookie candidates from raw request headers (not only the single parsed PHP cookie value).
 * - Restricted Access now writes signed access cookies across host-only + host-domain variants and common www/non-www host forms to reduce cross-route cookie mismatch.
 * - Valid Restricted Access cookie value is now normalized back into runtime state so homepage and other paths consistently honor unlocked access.
 *
 * 2.5.114 - March 16, 2026
 * - Added Lightbox view tracking for Media Library Tag Gallery images and now stores per-attachment counts in media metadata.
 * - Added "Sort media by" controls in Media Library list/grid views with Lightbox Views (highest/lowest) options.
 * - Added "Lightbox Views" display next to Library Tags in attachment edit fields.
 *
 * 2.5.113 - March 16, 2026
 * - Added a new Restricted Access setting under No access message: "Password Submit Button Text".
 * - Shared-password overlays now use the configured custom submit button label in both full-page and background-overlay render modes.
 *
 * 2.5.112 - March 16, 2026
 * - Fixed Restricted Access shared-password session persistence across different site paths (including homepage) by writing the access cookie on all relevant cookie paths.
 * - Restricted Access cookie now uses a resolved host domain when appropriate so access state remains consistent between protected pages and home navigation.
 *
 * 2.5.111 - March 16, 2026
 * - Removed pre-click hover styling on Media Library Tag Gallery images when using Link To: Open Image in Lightbox.
 * - Lightbox trigger buttons now keep neutral visual state on hover/focus/active so images do not visually shift before opening.
 * - Style-specific hover transforms for Polaroid Scrapbook and Perspective 3D now apply only when link mode is not lightbox.
 *
 * 2.5.110 - March 16, 2026
 * - Added a new Media Library Tag Gallery setting: "Allow Swipe to Left or Right to go to Previous or Next Photo."
 * - Wired swipe navigation through add-on defaults, block-level override controls, and gallery runtime data attributes.
 * - Swipe gestures now navigate previous/next images in both primary and resilient fallback lightbox runtimes when enabled.
 *
 * 2.5.109 - March 16, 2026
 * - Applied CSS transition animation handling to the resilient inline slideshow fallback path so "Slideshow Transition" visibly affects fallback slide changes (none, crossfade, slide-left).
 * - Fallback slideshow now toggles image transition state classes during interval-driven next-slide rendering to match transition behavior expected in the main runtime.
 *
 * 2.5.108 - March 16, 2026
 * - Reordered lightbox controls to place the Play Slideshow button between the Previous and Next arrow buttons.
 *
 * 2.5.107 - March 16, 2026
 * - Fixed fallback lightbox slideshow button runtime wiring by defining the missing inline slideshow onclick handler variable used in gallery modal markup.
 * - Preserved the new arrow-based Previous/Next controls and resilient slideshow behavior paths introduced in 2.5.106.
 *
 * 2.5.106 - March 16, 2026
 * - Lightbox Previous/Next controls now render as arrow buttons instead of text labels.
 * - Resilient inline fallback lightbox now supports Play/Pause slideshow, slideshow seconds timing, and slideshow transition class handling from existing gallery settings.
 * - Simplified the Add-ons label text for Slideshow Transition to avoid duplicate verbose wording.
 *
 * 2.5.105 - March 16, 2026
 * - Hooked the resilient Media Library lightbox helper into wp_head so it initializes before gallery markup and remains available even when later in-body scripts fail.
 * - Added a tiny direct open fallback path inside gallery trigger onclick handlers to force modal open when the helper object is unavailable due to unrelated JavaScript errors.
 *
 * 2.5.104 - March 16, 2026
 * - Fixed a regression where front-end Media Library Tag Gallery helper code was accidentally injected into block-editor JavaScript, which could break script parsing and prevent lightbox opening.
 * - Added a dedicated front-end inline lightbox helper method and hook-based bootstrap so fallback lightbox behavior initializes from a safe standalone path.
 * - Kept Duplicate link removed while preserving backdrop-close and Previous/Next + keyboard navigation behavior in fallback flow.
 *
 * 2.5.103 - March 16, 2026
 * - Removed the lightbox Duplicate link from Media Library Tag Gallery modal output.
 * - Enabled click-on-backdrop-to-close behavior in the inline emergency lightbox fallback path.
 * - Restored Previous/Next buttons and ArrowLeft/ArrowRight keyboard support in the same emergency fallback path when the related lightbox setting is enabled.
 *
 * 2.5.102 - March 16, 2026
 * - Added inline on-click lightbox open/close fallback handlers on Media Library Tag Gallery lightbox triggers so modal opening still works even when unrelated JavaScript errors stop later runtime scripts.
 * - Fallback handlers read existing modal data attributes (src/alt/caption/edit URL) directly from the clicked trigger and open the same overlay without depending on jQuery/global runtime availability.
 *
 * 2.5.101 - March 16, 2026
 * - Restored Previous/Next lightbox controls and ArrowLeft/ArrowRight keyboard navigation inside the fail-safe Media Library Tag Gallery lightbox runtime.
 * - Fallback lightbox now respects the existing "Add Previous & Next Links in Lightbox Window and Allow Keyboard Arrows Shortcut" setting.
 *
 * 2.5.100 - March 16, 2026
 * - Added a fail-safe lightbox script for Media Library Tag Gallery that activates only if the primary lightbox runtime fails to initialize, preventing total lightbox loss from unrelated front-end JavaScript errors.
 * - Lightbox trigger selector matching now avoids quoted-attribute dependency and includes class/data-based fallbacks for stronger compatibility with aggressive script minification/rewriters.
 *
 * 2.5.99 - March 16, 2026
 * - Reworked Media Library Tag Gallery "Open Image in Lightbox" triggers to use a dedicated modal-trigger data path for stronger click reliability.
 * - Added hardened capture-phase fallback handling so gallery image clicks open the modal even when other scripts/theme handlers are competing for click events.
 * - Kept backward compatibility for legacy lightbox trigger attributes while prioritizing the new modal trigger attributes.
 *
 * 2.5.98 - March 16, 2026
 * - Fixed duplicate tag-description output in pipe-separated gallery mode by suppressing the inner gallery album-description block while keeping the pipe section heading/description output.
 * - Pipe-separated gallery sections continue to render centered H2 + description once and keep the 50px bottom spacing between sections.
 *
 * 2.5.96 - March 16, 2026
 * - Media Library Tag Gallery URL override now supports pipe-separated groups (example: ?tag=gallery1|gallery2|gallery3) to render separate gallery sections sequentially instead of combining all photos into one query.
 * - Each pipe-separated gallery section now renders a centered H2 tag title and a centered paragraph with the tag description above that gallery when description text exists.
 * - Existing plus (+) AND and underscore (_) OR behavior remains intact within each individual pipe-separated section expression.
 *
 * 2.5.95 - March 16, 2026
 * - Fixed SEO Basics dynamic placeholder replacement for Page Title Override meta outputs so [tag-name] and [site-title] resolve in frontend social title tags.
 * - og:title and twitter:title now use the same resolved Page Title Override text path as document title filters, keeping title placeholder behavior consistent.
 *
 * 2.5.94 - March 16, 2026
 * - Replaced the Media Library Tag Gallery "Modal Window" default setting with a restored "Link To" selector.
 * - Added Link To options: None, Open Image, Open Image in New Window, and Open Image in Lightbox.
 * - Added block-level "Link To" override support (with Use add-on default) so each gallery block can override the add-on default behavior.
 *
 * 2.5.93 - March 16, 2026
 * - Added a new Media Library Tags add-on setting: "Allow Simple Lightbox when clicking on a thumbnail".
 * - Added block-level support (with "Use add-on default") for the same simple thumbnail lightbox behavior in Media Library Tag Gallery blocks.
 * - When enabled, clicking thumbnails opens a simplified image-only lightbox (close + image only) while hiding advanced controls and admin tag tools.
 *
 * 2.5.92 - March 16, 2026
 * - Library Tags Bulk Editor now splits rows into two sections: tags that are live in menu navigation first, then all remaining tags below.
 * - Added section header rows in the Bulk Editor table to clearly separate "Live in Menu Navigation" tags from other tags.
 *
 * 2.5.91 - March 16, 2026
 * - Added support for [site-title] placeholder replacement alongside [tag-name] and [tag-description].
 * - [site-title] now resolves to the WordPress Site Title and is supported in post title/content placeholder paths and HTML <title> replacement flows.
 *
 * 2.5.90 - March 16, 2026
 * - Restricted Access now includes a new checkbox under "Full Screen Overlay Image": "Display as normal Image Above No access message instead of Background Image".
 * - When enabled, the overlay image is rendered as normal content above the No access message instead of as a full-screen background layer.
 *
 * 2.5.89 - March 16, 2026
 * - Library Tags Bulk Editor now lists only individual tag names and omits combined comma-separated tag rows (example: "Pets, Dating").
 * - Combined tags are filtered out of the Bulk Editor table so split tags like "Pets" and "Dating" remain as separate rows.
 *
 * 2.5.88 - March 16, 2026
 * - Media Library tag filter dropdown options now append a trailing "*" for tags detected in active menu navigation URLs.
 * - Applied the same menu-navigation star marker to both list-view and grid-view Media Library tag filter dropdowns for consistency.
 *
 * 2.5.87 - March 16, 2026
 * - Removed front-end Media Library Tag Gallery inline administrator tag controls that printed tag lists with Hide/Duplicate quick-action links under each image.
 * - Cleaned up related gallery-side inline-control CSS/JS runtime paths tied to the removed inline admin tag block markup.
 *
 * 2.5.86 - March 16, 2026
 * - Added a new Restricted Access setting: "Still Allow Full Page HTML to be Rendered in Background behind Overlay for Social Media Share Link Meta Data".
 * - When enabled, Restricted Access now lets full page HTML render for crawlers/share scrapers while displaying the access overlay on top for visitors.
 *
 * 2.5.85 - March 16, 2026
 * - Reverted admin per-photo tag controls from bottom-image overlay back to below-image placement for clearer readability and less visual obstruction.
 * - Added extra bottom margin under admin per-photo tag controls specifically in Mosaic Grid style so Hide/Duplicate links are easier to see and click.
 *
 * 2.5.84 - March 16, 2026
 * - Moved admin-only per-image tag controls (tag list + Hide + Duplicate actions) into an overlay positioned at the bottom of each gallery photo.
 * - Overlay controls now sit on top of image thumbnails with a semi-transparent backdrop while preserving AJAX no-refresh tag actions.
 *
 * 2.5.83 - March 16, 2026
 * - Media Library Tags front-end gallery now shows admin-only per-image tag controls below every photo across all styles, including a list of current tags plus "Hide" and "Duplicate" quick actions.
 * - New per-image "Hide" and "Duplicate" controls add Library Tags asynchronously (AJAX) without page reload, and refresh the displayed tag list inline after successful updates.
 *
 * 2.5.82 - March 16, 2026
 * - Removed lightbox click anchors to full-size image files for Media Library Tags gallery modal behavior and replaced them with modal trigger buttons using data attributes.
 * - Mosaic Grid and other gallery styles now open the modal window directly on click without browser navigation to image URLs.
 *
 * 2.5.81 - March 16, 2026
 * - Fixed mosaic-grid click handling so modal/lightbox opens reliably by making gallery links explicit block-level click targets above image layers.
 * - Added front-end CSS hardening for gallery link wrappers (display:block, width/height:100%, pointer-events:auto) to prevent style-specific tile layouts from swallowing click events.
 *
 * 2.5.80 - March 16, 2026
 * - Fixed Media Library Tags gallery modal-window opening regression by forcing front-end gallery image links to lightbox/modal output even when legacy block attributes or older saved link settings were set to "none".
 * - Updated block registration/default handling so Link To legacy values no longer prevent lightbox anchor markup from rendering on existing pages.
 *
 * 2.5.79 - March 16, 2026
 * - Removed the "Link To" control from Media Library Tags gallery settings and block inspector, and replaced it with always-on modal window behavior for image clicks.
 * - Gallery images now open in the modal window by default (except style-specific forced behavior such as fullscreen lightbox grid), keeping existing lightbox controls/settings as the primary interaction model.
 *
 * 2.5.78 - March 16, 2026
 * - Restricted Access now logs shared-password access attempts and displays them in a new "Access History" card under addon_section=restricted-access.
 * - New Access History table columns: timestamp, IP, IP location, browser, URL accessed from, password if used, and failed password if failed.
 *
 * 2.5.77 - March 16, 2026
 * - Restricted Access add-on now includes a new "Full Screen Overlay Image Max Width" field directly under "Full Screen Overlay Image".
 * - Full Screen Overlay image now renders as width: 100% with an optional max-width constraint when provided, improving responsive control.
 *
 * 2.5.76 - March 16, 2026
 * - Mosaic Grid mobile behavior now preserves irregular mosaic tile spans when Number of Columns (Mobile) is set to 3 or higher, instead of forcing all tiles back to equal-size blocks.
 * - When mobile columns are 1-2, the layout still flattens to uniform tiles for readability; 3+ columns now keeps the mosaic styling active on mobile.
 *
 * 2.5.75 - March 16, 2026
 * - Library Tags Bulk Editor now checks active theme menu item URLs for each tag slug and displays a "Live in Menu Navigation" badge under matching slug rows.
 * - Helps identify which Library Tag slugs are already manually referenced in menu navigation links.
 *
 * 2.5.74 - March 16, 2026
 * - Rebuilt Media Library Tag Gallery lightbox modal opening flow to use a simpler delegated click handler for more reliable, consistent opening behavior.
 * - Added Lightbox Modal Background Color and Lightbox Modal Text Color settings (with block-level "Use add-on default" overrides) and applied them to modal UI elements.
 * - Kept caption, previous/next controls, keyboard arrows, slideshow button, slideshow timing, and transition behavior in the new modal runtime.
 *
 * 2.5.73 - March 16, 2026
 * - Removed the custom .um-media-library-tag-description-paragraph typography/color style override from the gallery output CSS so default/theme styling applies.
 *
 * 2.5.72 - March 16, 2026
 * - Added plugin version text directly under the main User Experience Manager admin page title so the active version is always visible at the top of the screen.
 *
 * 2.5.71 - March 16, 2026
 * - Added new "YouTube Video Links" textarea to Library Tag add/edit screens (one URL per line) with sanitized per-term storage.
 * - Extended Library Tags Bulk Editor to include a "YouTube Video Links" column so links can be managed for all tags from one table.
 * - Front-end Media Library Tag Gallery now embeds saved YouTube videos above the gallery (below album descriptions): 1 video = full width, multiple videos = 2-column layout.
 *
 * 2.5.70 - March 16, 2026
 * - Added new Media Library Tag Gallery setting: "Display Album Tag Description(s)" with options none / above gallery / below gallery.
 * - Added matching per-block override controls (including "Use add-on default") and reused [tag-description]-style multi-tag paragraph + edit-link rendering for above/below gallery output.
 *
 * 2.5.69 - March 16, 2026
 * - Fixed Library Tags Bulk Editor save redirect target to use upload.php?page=um-media-library-tags-bulk-editor, preventing the "Cannot load um-media-library-tags-bulk-editor." error after save.
 * - Bulk Editor now returns to the correct Media submenu page with success/error notices intact.
 *
 * 2.5.68 - March 16, 2026
 * - Added a wp-admin bar color safeguard so broad custom CSS rules from WP-Admin CSS settings no longer force admin-bar links to blue.
 * - Admin bar link/label colors now stay aligned with default WordPress top-bar colors.
 *
 * 2.5.67 - March 16, 2026
 * - Added server-side lightbox debug activation detection and front-end data attributes so debug mode can be confirmed from rendered HTML even when query parsing is altered by caching or redirects.
 * - Added an always-visible in-gallery debug badge plus global JS fallback flags to make debug mode presence obvious and resilient.
 *
 * 2.5.66 - March 16, 2026
 * - Hardened front-end lightbox opening by intercepting gallery link interactions at pointer-down capture phase, preventing third-party handlers from navigating to image URLs before lightbox opens.
 * - Added duplicate-click suppression after pointer-triggered open to avoid double-open races while keeping link navigation fully blocked.
 *
 * 2.5.65 - March 16, 2026
 * - Fixed Media Library Tags Bulk Editor admin menu registration so Bulk Editor reliably appears under Media in wp-admin.
 * - Added a taxonomy-registration fallback during submenu registration to avoid hook-order/timing issues preventing menu visibility.
 *
 * 2.5.64 - March 16, 2026
 * - Added front-end lightbox debug mode URL flag (?um_mltg_debug=1) with detailed console tracing and a small on-page debug log panel to diagnose click interception/runtime flow.
 * - Added optional debug auto-open URL flag (?um_mltg_debug_open=1) to force-open the first lightbox image and isolate rendering/control issues from click handler issues.
 *
 * 2.5.63 - March 16, 2026
 * - Fixed gallery click interception so lightbox opening cannot be bypassed by other front-end handlers; added a stricter capture-phase document fallback and immediate propagation stop for lightbox links.
 * - Restored reliable image-click-to-lightbox behavior after the prior runtime consolidation.
 *
 * 2.5.62 - March 16, 2026
 * - Removed the separate global front-end lightbox fallback runtime so only the per-gallery lightbox runtime handles interactions, preventing control-state conflicts.
 * - Tightened lightbox control visibility state (display/hidden/disabled/aria) so "Add a Play Slideshow Button" and navigation controls strictly follow settings.
 * - Control button clicks now stop propagation and reliably execute Previous/Next/Slideshow actions within the active lightbox session.
 *
 * 2.5.60 - March 16, 2026
 * - Added new Library Tags submenu screen: "Bulk Editor" under Media > Library Tags.
 * - Bulk Editor displays all tags in one table with editable title, slug, and description fields plus a Save All action.
 * - Added secure bulk-save handler (capability + nonce) for updating all Library Tags in one submission with success/error notices.
 *
 * 2.5.59 - March 16, 2026
 * - Fixed URL tag-expression parsing so single-token URLs (example: /album/?cruise) no longer expand to multiple matched tags for placeholder output.
 * - [tag-description] now correctly outputs a single description paragraph for single-token URL filters while multi-token expressions still output multiple descriptions.
 *
 * 2.5.58 - March 16, 2026
 * - Fixed front-end gallery lightbox open handler regression caused by an invalid event reference inside the helper function.
 * - Clicking gallery images with Link To = lightbox now consistently opens the overlay instead of navigating to the file URL.
 *
 * 2.5.57 - March 16, 2026
 * - Reworked front-end lightbox link interception with direct per-link handlers plus delegated capture/bubble fallback so gallery links consistently open in lightbox instead of opening files directly.
 * - Updated Media Library tag filter validation/parsing paths to use the expanded token-aware filter slug resolver (including URL override parsing) so filters match when the searched token exists anywhere in tag strings.
 *
 * 2.5.56 - March 16, 2026
 * - Media Library grid/list tag filter dropdown now shows only unique individual tag tokens (for example, combined names like "Travel, Italy, Venice" no longer appear as a single filter option).
 * - Kept bulk-apply tag dropdown behavior unchanged so full stored tag names/slugs are still available for assignment.
 *
 * 2.5.55 - March 16, 2026
 * - Fixed compound/comma-separated Library Tag matching so token order no longer matters (example: both "Cruise, Honeymoon" and "Honeymoon, Cruise" now match cruise/honeymoon filtering consistently).
 * - Expanded token matching logic now checks each normalized token with boundary-aware matching instead of exact-token equality.
 *
 * 2.5.54 - March 16, 2026
 * - New Media Library Tag Gallery blocks now default all "Use add-on default" toggles to enabled, so block-level settings inherit the main add-on defaults unless manually untoggled.
 * - Applied this default-on behavior consistently in both block attribute registration and editor attribute bootstrapping.
 *
 * 2.5.53 - March 16, 2026
 * - Media Library Tag filtering now matches related compound tags when filtering by a base tag slug (example: selecting "cruise" now also includes tags like "honeymoon-cruise" and "Honeymoon, Cruise").
 * - Applied the same expanded matching behavior to both front-end gallery output and WP Media Library list/grid filtering for consistent results.
 * - Added explicit nopaging handling for gallery queries when Page Limit is 0 to better preserve unlimited output behavior.
 *
 * 2.5.52 - March 16, 2026
 * - Fixed front-end gallery "Link To: lightbox" click handling regression by hardening delegated click-target resolution before calling .closest().
 * - Restored reliable lightbox opening from gallery image links while keeping the new admin quick-tag tools active.
 *
 * 2.5.51 - March 16, 2026
 * - Front-end gallery lightbox now includes a new admin-only "Duplicate" quick action that adds the "duplicate" Library Tag to the active image.
 * - Added admin-only inline tag tools in the lightbox to quickly add one or more Library Tags (comma-separated) directly to the active image without leaving the screen.
 * - Lightbox admin tag actions use secure AJAX nonce checks and attachment-targeted updates through existing Library Tag apply handlers.
 *
 * 2.5.50 - March 16, 2026
 * - [tag-description] now renders all URL-selected Media Library tag descriptions (single, AND, OR) in the same order as the tag expression in the URL.
 * - Each rendered tag description now outputs as its own paragraph with class "um-media-library-tag-description-paragraph".
 * - When available for admins, each tag description paragraph now includes its own "Edit Tag Description" link for that specific tag.
 *
 * 2.5.49 - March 16, 2026
 * - Added new Dynamic Photo Gallery lightbox setting: "Slideshow Transition (None, Crossfade, Slide to Left)" as an add-on default.
 * - Added matching per-block Slideshow Transition control with a "Use add-on default" toggle in the Media Library Tag Gallery block inspector.
 * - Front-end lightbox slideshow now supports transition modes: none, crossfade, and slide-left.
 *
 * 2.5.48 - March 16, 2026
 * - Dynamic Photo Gallery with Media Library Tags: added new default lightbox settings for Previous/Next navigation + keyboard arrows, Play Slideshow button, and Slideshow Seconds Per Photo.
 * - Added matching per-block controls in Media Library Tag Gallery block settings, including "Use add-on default" toggles for all three new lightbox options.
 * - Front-end lightbox now supports optional Previous/Next controls, ArrowLeft/ArrowRight navigation, and a configurable slideshow play/pause timer using the configured seconds-per-photo interval.
 *
 * 2.5.47 - March 16, 2026
 * - Fixed Add-ons settings save redirect context so saving a non-Email Log add-on no longer jumps to addon_section=emali-log.
 * - Email Log nested forms now avoid emitting conflicting addon_section fields that could override the main Add-ons form section on save.
 * - Email Log filter form now targets its section URL directly, preserving current section context without introducing duplicate hidden routing fields.
 *
 * 2.5.46 - April 3, 2026
 * - My Account Admin Orders search form button clickability fix: raised search form stacking context and ensured pointer events are enabled on the submit button/input so Search is always clickable.
 *
 * 2.5.45 - April 3, 2026
 * - Email Log add-on UI wording updated from "Emali" to "Email" for user-facing labels/buttons (including "Clear Email Log History").
 * - Added a new modal HTML preview action in Email Log list rows so the rendered email body can be opened in a popup window without leaving the table.
 *
 * 2.5.44 - April 3, 2026
 * - Updated Documentation tab content to reflect the current top-level tabs and newer sub-page navigation patterns (login_tools_section, addon_section, block_section, docs_section).
 * - Refreshed tab reference cards and About > Feature List entries so current navigation routes and section naming are documented in one place.
 *
 * 2.5.43 - April 3, 2026
 * - Email Log now includes an "Auto-delete log entries after X days" setting (0 = keep forever).
 * - Added automatic Email Log retention cleanup (daily scheduled event + request fallback) to purge rows older than the configured number of days.
 * - Preserved and clarified the manual "Clear Email Log History" action so all log entries can be wiped immediately.
 *
 * 2.5.42 - April 3, 2026
 * - Added new Add-on: Email Log.
 * - When activated, Email Log captures outgoing wp_mail payloads into a dedicated database table and tracks sent/failed status updates.
 * - Added Email Log UI in tab=addons with status/search filters, email header columns, HTML/source preview, resend + forward actions, clear-history action, and hour/day/week/month stats cards.
 *
 * 2.5.41 - April 3, 2026
 * - Add-ons filter tags now include two new top-level options: Email and SMS.
 * - Added keyword matching so Email/SMS-related add-ons are grouped under those new filter tags in tab=addons.
 *
 * 2.5.40 - April 3, 2026
 * - Fixed Add-ons/Blocks save routing so normal "Save" actions can deactivate individual add-ons/blocks again.
 * - Tightened temporary-disable-only detection to run only from main index context, preventing false matches while editing a specific add-on/block section.
 *
 * 2.5.39 - April 3, 2026
 * - Fixed Add-ons temporary-disable toggle save flow: turning off "Temporarily disable all add-ons runtime functionality" now preserves existing add-on active checkbox states.
 * - Settings save now reads persisted raw settings (not runtime-overridden settings) to prevent accidental add-on deactivation when temporary runtime disable had been enabled.
 *
 * 2.5.38 - April 3, 2026
 * - Invoice Approval invoice view now shows an item summary directly under "Products & Services": "Total Line Items: X (Total Quantity: Y)".
 * - Added order quantity aggregation from invoice line items so both line-count and quantity totals are visible before the items table.
 *
 * 2.5.37 - April 3, 2026
 * - Fixed fatal error on Add-ons/Blocks screens by adding missing User_Manager_Core::get_raw_settings() compatibility method.
 * - get_raw_settings() now returns persisted plugin options without runtime temporary-disable overrides, restoring admin screen rendering.
 *
 * 2.5.36 - April 3, 2026
 * - Updated AI Notes/rules for Documentation > Versions updates so new changelog entries must use the real current date at edit time.
 * - Prevents stale hardcoded changelog dates from being reused for future docs_section=versions updates.
 *
 * 2.5.35 - March 16, 2026
 * - Split temporary runtime overrides into independent controls: Add-ons has its own "Temporarily Disable All" toggle and Blocks has its own separate toggle.
 * - Add-ons/Blocks screens now read persisted (raw) settings so active modules stay checked/highlighted while temporary runtime disable is enabled.
 * - Added temporary-disable status badges/icons in Add-ons and Blocks index views to clearly indicate runtime-disabled mode.
 *
 * 2.5.33 - March 16, 2026
 * - Blocks tab: the "Temporarily Disable All" card now appears only on the main Blocks index view and is hidden when viewing a specific block section.
 *
 * 2.5.32 - March 16, 2026
 * - WP-Admin Bar Quick Search add-on now includes "Priority Post Types to Display Before All Remaining Post Types" with a checkbox per available post type.
 * - Checked priority post types are shown first in the quick-search dropdown, while all remaining post types still appear after them.
 *
 * 2.5.31 - March 16, 2026
 * - Add-ons tab: the "Temporarily Disable All" card now appears only on the main Add-ons index view and is hidden when viewing a specific add-on section.
 *
 * 2.5.30 - March 16, 2026
 * - Added a new Settings > User Experience checkbox to control the "User Experience Manager" WP-Admin top-bar shortcut.
 * - The top-bar shortcut is now disabled by default and only appears when that setting is enabled.
 *
 * 2.5.29 - March 16, 2026
 * - Add-ons tab and Blocks tab now include a new bottom card: "Temporarily Disable All" with a checkbox and Save button.
 * - The new temporary override disables all add-ons and blocks runtime behavior individually when enabled, and restores normal behavior when unchecked and saved.
 *
 * 2.5.27 - March 16, 2026
 * - Product Notification: updated the default placeholder text for "Display a Woocommerce Notification Above Product at All Times?" to "Customers have reported that this item runs small".
 *
 * 2.5.26 - March 16, 2026
 * - Added new Add-on: Product Notification.
 * - When activated, WooCommerce products get new Product Data > General fields: "Display a Woocommerce Notification Above Product at All Times?", "Optional Button Title", and "Optional Button URL".
 * - If the product notification field has content, a persistent WooCommerce-style message now renders above the product page at all times.
 * - Added Product Notification add-on color override settings for notification background/text and button default/hover colors.
 *
 * 2.5.25 - March 16, 2026
 * - Restricted Access redirect modes now enforce hard redirects for logged-out users across all front-end pages: "Redirect to My Account" and "Redirect to WP-Admin" no longer render the full-screen overlay fallback.
 * - Prevents redirect targets from showing the overlay page when already on the destination URL; requests now remain on the intended login destination.
 *
 * 2.5.24 - March 16, 2026
 * - Restricted Access add-on description now explicitly includes "Security" terminology so it appears in the Add-ons Security filter/tag view.
 *
 * 2.5.23 - March 16, 2026
 * - Fixed Post Meta Viewer edit mode on high-meta screens (like WooCommerce products): existing meta values now submit only when changed, preventing oversized form submissions that can block core product fields (such as price) from saving.
 * - Existing meta fields in the Post Meta Viewer now use data-meta-key + lightweight JS mirrors so unchanged rows do not inflate POST payload size.
 *
 * 2.5.22 - March 16, 2026
 * - SEO Basics meta box now shows recommended character targets (Title: 60, Description: 160).
 * - Added live character countdown indicators for title/description override fields to show how many characters are left.
 *
 * 2.5.21 - March 16, 2026
 * - Added new Add-ons card: "SEO Basics" with activation toggle.
 * - When active, adds an "SEO Basics" meta box on every page and post with Page Title Override and Page Description Override fields.
 * - Front-end SEO image now resolves by default to Featured Image first; if no featured image exists, it falls back to the first image found in page/post content.
 * - Applies title override to browser document titles and outputs description/image meta tags in wp_head.
 *
 * 2.5.20 - March 16, 2026
 * - Fixed Media Library Tag placeholder replacement in the HTML document <title> path by adding fallback context detection when queried object IDs are unavailable at title-filter time.
 * - [tag-name]/[tag-description] now resolve in browser title tags more reliably, matching page title/body behavior for URL tag override-enabled gallery blocks.
 *
 * 2.5.19 - March 16, 2026
 * - Add-on runtime-gating hardening follow-up: My Account Site Admin hooks now only initialize when its add-on is enabled (including URL temporary-disable support), preventing endpoint/query-var hooks from registering while inactive.
 * - New User Coupons debug panel hook now only registers when the New User Coupons add-on is active, eliminating debug panel runtime when the add-on is disabled.
 *
 * 2.5.18 - March 16, 2026
 * - Remaining balance checkout + order-received notices now hide currency text when the currency symbol/label is longer than 1 character (example: "Points"), showing numeric-only amounts for cleaner display.
 * - Applies to classic checkout notice, block checkout notice, and order received remaining-balance confirmation amounts/calculations.
 *
 * 2.5.17 - March 16, 2026
 * - Email Settings defaults: when sender fields are left empty, User Manager now defaults to Site Title for "Send From Name" and noreply@{site-domain} for both "Send From Email Address" and "Reply To Email Address".
 * - Updated Settings tab field rendering and runtime header generation so defaults are consistent across UI and outgoing email behavior.
 *
 * 2.5.16 - March 16, 2026
 * - Updated "Send automated remaining balance coupon" demo template body to include both Remaining Balance (%COUPONCODEVALUE%) and Coupon Code (%COUPONCODE%).
 * - Added %COUPONCODEVALUE% placeholder support to coupon email sends and preview rendering, including remainder-coupon workflows.
 * - Existing default remaining-balance template entries are auto-updated to the new body when unchanged/customization-safe.
 *
 * 2.5.15 - March 16, 2026
 * - Add-ons > User Coupon Remaining Balances: fixed Preview Email button to use delegated click binding so it works reliably when Add-ons cards/toggles are re-rendered or initially hidden.
 * - Preview now uses the same shared modal invocation pattern as other working preview buttons, restoring consistent behavior for coupon remainder template previews.
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
