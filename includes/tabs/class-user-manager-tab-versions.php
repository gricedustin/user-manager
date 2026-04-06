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
						<h4>2.5.131 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed an early translation-loading timing issue by deferring Media Library Tag Video Library legacy migration to run on init instead of during plugin bootstrap.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added init guards to the migration path to prevent pre-init taxonomy registration from triggering WordPress 6.7+ _load_textdomain_just_in_time notices for the user-manager textdomain.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.130 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag Gallery placeholder resolution now supports URL `title` override so `[tag-name]` can use `?title=...` when present.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When URL title override is set, `[tag-name]` uses the sanitized `title` query value while keeping tag-description lookup from the existing URL tag expression.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.129 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new Media Library Tag Gallery setting: "Featured Image Max Width (px)" so the front-end featured image width is configurable from add-on settings instead of hardcoded CSS.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Front-end featured-image wrap CSS now uses the configured max width value (with viewport-aware cap) for Media Library Tag description featured images.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.128 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new optional "Activate Video Library" setting under the Media Library Tags add-on settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, Media now includes a new "Video Library" page with a full add/edit form for YouTube link, title, description, date, and clickable Library Tag assignment.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a saved-videos table on the new Video Library page with per-row Edit actions that repopulate the same form for updates.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Library Tags Bulk Editor now replaces per-tag YouTube textareas with a Video Library summary column and manage links.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Front-end gallery YouTube embeds now source from the centralized Video Library records assigned to active Library Tags.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added one-time migration to import existing legacy per-tag YouTube link meta into the new centralized Video Library records.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.127 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Updated Media Library Tag description layout so text wraps around the tag Featured Image (including below the image) for a true flowing article-style presentation.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tag Featured Image now opens in the same lightbox collection as gallery images, including prev/next and deep-link index continuity.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a new default-enabled setting to hide duplicate featured-image tiles when that same attachment already exists in the tagged gallery image set.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.126 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Changed Media Library Tag Gallery lightbox deep-link query parameter from um_lightbox_image_id to image for cleaner share URLs.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Lightbox deep-link auto-open now reads both image (new) and um_lightbox_image_id (legacy) so old shared links continue to open the correct image.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.125 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added per-tag Featured Image support for Media Library Tags in both the taxonomy edit screen and the Library Tags Bulk Editor page.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Front-end gallery tag descriptions now render the tag Featured Image left-aligned beside the description content when an image is assigned.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.124 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Restricted Access full-screen overlay coverage on mobile by enforcing viewport-height overlays with fixed inset positioning and dynamic viewport units.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Background overlay mode now uses mobile-safe viewport sizing (100dvh with 100vh fallback) so no page content peeks behind the lock screen on small devices.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.123 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new gallery style option: "Mosaic Grid (Taller Tiles)" that reuses the existing irregular mosaic pattern with 50% taller base tile rows.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Mosaic (Taller) shares the same large/tall/wide tile placement logic as Mosaic Grid while increasing visual tile height for a taller collage look.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.122 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag description paragraphs now preserve author-entered line breaks by converting newline characters to <br> in front-end output.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library Tag description output now explicitly allows basic inline formatting tags such as <b>, <strong>, <i>, and <em> while retaining safe sanitization.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.121 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new Media Library Tag Gallery setting: "Allow Tap or Click on Left or Right side of image to go to Previous or Next Photo" directly under the existing swipe setting.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Lightbox now supports left/right side image click (or tap) navigation in both the primary and resilient fallback runtimes when enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.120 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added 25px bottom spacing to Media Library Tag Gallery wrapper output so gallery blocks have consistent space below the gallery container.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.119 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Updated Media Library admin sort labels to use neutral "Views" wording instead of "Lightbox Views" in list/grid sort dropdowns.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fixed grid-view Media Library sort behavior so selecting a sort option now applies immediately to the wp.media attachment query and refreshes results in-place.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.118 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added lightbox deep-link URLs for Media Library Tag Gallery images using the um_lightbox_image_id query parameter so opening an image writes its attachment ID to the URL.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Pages loaded with a matching um_lightbox_image_id now auto-open the corresponding image in lightbox (including resilient fallback runtime), enabling shareable links to specific photos.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.117 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Extended Media Library Tag Gallery lightbox tracking to include current-period counters for Year, Month, Week, and Day alongside total Lightbox Views.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Attachment edit/tagging UI now shows Lightbox Views (Year/Month/Week/Day) under the existing Lightbox Views metric for each media item.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.116 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed WP_Term to int conversion warnings in Media Library menu-tag matching by extracting menu term IDs from wp_get_nav_menus() term objects before normalization.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Hardened term-ID handling in quick-search single-term redirects and category/tag report edit links so term objects are never passed directly into integer casts/absint paths.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.115 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Hardened Restricted Access shared-password session persistence by validating across all duplicate um_restricted_access cookie values present in the raw Cookie header, not only the single parsed $_COOKIE value.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Restricted Access now writes signed access cookies across all relevant path/domain variants (including host-only and common www/non-www host variants) to prevent homepage prompts caused by cookie scope collisions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.114 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added per-image Media Library Tag Gallery lightbox view tracking. Each time a lightbox image opens, the attachment now increments a stored "Lightbox Views" meta count.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library attachment edit/tagging UI now displays the current Lightbox Views value near Library Tags for quick backend visibility.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Media Library sort controls (list and grid) for Lightbox Views so admins can sort by highest/lowest viewed media.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.113 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new Restricted Access setting under "No access message": "Password Submit Button Text".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Custom password submit button text now renders in both Restricted Access overlay modes (full-page overlay and background HTML overlay).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.112 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Restricted Access shared-password persistence by setting the access cookie across all relevant cookie paths (including site root) and host domain resolution.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Prevents repeated password prompts when navigating back to the homepage or other URLs that differ by cookie path scope.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.111 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed pre-open hover styling from Media Library Tag Gallery images when Link To is set to Lightbox, so thumbnails no longer animate/change on hover before opening the modal.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Kept hover styling behavior for non-lightbox link modes (Open Image / New Window) to avoid changing non-lightbox gallery interactions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.110 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new Media Library Tags setting: "Allow Swipe to Left or Right to go to Previous or Next Photo".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Swipe-to-navigate now works in the primary lightbox runtime and in resilient fallback runtimes when that setting is enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.109 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Applied slideshow transition classes in the resilient/fallback lightbox runtime during step changes so "Slideshow Transition" now visibly affects fallback slideshow playback.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fallback slideshow step changes now trigger the same image transition CSS class flow used by the main runtime (crossfade / slide-left) instead of hard cuts.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.108 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reordered lightbox controls to display as Previous arrow, Play Slideshow button, Next arrow so Play appears centered between navigation arrows.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.107 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed fallback lightbox slideshow control wiring by defining the inline slideshow onclick callback used by the modal Play/Pause button.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Prevents Play Slideshow button no-op behavior in degraded front-end JavaScript conditions where fallback runtime is active.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.106 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Updated Media Library Tag Gallery lightbox Previous/Next buttons to arrow-style controls (&lsaquo; / &rsaquo;) in modal output.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Extended resilient inline lightbox fallback runtime to support Play/Pause slideshow, slideshow seconds-per-photo timing, and transition mode classes (none, crossfade, slide-left).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Simplified add-on settings label text from "Slideshow Transition (None, Crossfade, Slide to Left)" to "Slideshow Transition" to remove duplicated verbose wording.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.105 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Media Library Tag Gallery inline fallback bootstrap registration by hooking the helper script to wp_head early, ensuring window.umMltgInline is available before gallery click handlers run.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Kept a tiny direct click-open fallback inside trigger onclick handlers so lightbox can still open if global helper registration is blocked by severe third-party script failures.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.104 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved the inline fallback lightbox helper into a dedicated PHP method and ensured it prints in the front-end head before gallery output, preventing parser breakage in block editor script payloads.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Kept emergency click-open resilience while removing the accidental editor-script contamination that could cause JavaScript syntax failures on front-end pages.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.103 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed the Lightbox "Duplicate" quick link from Media Library Tag Gallery modal output.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Lightbox now closes when clicking outside the image (overlay backdrop), including in the inline fail-safe fallback path.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Restored Previous/Next controls and ArrowLeft/ArrowRight keyboard navigation in both primary and inline-fallback lightbox paths when the "Add Previous & Next Links..." setting is enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.102 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added inline lightbox-open fallback directly on Media Library Tag Gallery image trigger elements so clicks can still open images even when external scripts throw page-level JS errors.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added inline close fallback on the lightbox close button to keep modal exit behavior available during degraded front-end script conditions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.101 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Restored fallback lightbox support for "Add Previous & Next Links in Lightbox Window and Allow Keyboard Arrows Shortcut" so those controls continue to work even when the primary lightbox runtime is unavailable.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fallback modal runtime now reads the same Lightbox Previous/Next setting and supports Previous/Next button navigation plus ArrowLeft/ArrowRight keyboard shortcuts when enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.100 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a lightweight fallback modal runtime for Media Library Tag Gallery so image clicks can still open when the primary lightbox script is interrupted by unrelated front-end JavaScript errors.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated runtime trigger selector matching to avoid brittle quoted-attribute dependency and support class/data-attribute trigger detection paths.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.99 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Rebuilt Media Library Tag Gallery lightbox triggers to use dedicated modal data attributes and normalized runtime selector resolution, making "Link To: Open Image in Lightbox" far more resilient across gallery styles.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a hardened click-open path with gallery-level and document-level capture fallback so third-party click handlers are less likely to bypass modal opening behavior.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.98 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Pipe-separated Media Library Tag Gallery sections now suppress the built-in album description block inside each rendered sub-gallery so tag descriptions are not shown twice.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Each pipe section still keeps the centered heading/description block above the gallery and preserves the 50px bottom spacing between sequential pipe galleries.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.96 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag Gallery URL override now supports pipe-separated groups (example: ?tag=gallery1|gallery2|gallery3) to render multiple separate gallery sections in sequence instead of merging all photos into one result set.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When pipe-separated mode is used, each section now prints a centered H2 with the active tag name and a centered paragraph with that tag description (when available) above its corresponding gallery.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.95 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('SEO Basics now resolves Media Library placeholder values inside Page Title Override before printing head meta tags, so [tag-name] and [site-title] work in og:title and twitter:title output.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Aligned SEO Basics title override resolution paths so document title filters and social title meta tags consistently use the same dynamic placeholder replacement behavior.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.94 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Replaced the Media Library Tag Gallery "Modal Window" setting with a restored "Link To" selector in the add-on settings, with options: None, Open Image, Open Image in New Window, and Open Image in Lightbox.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added matching block-level "Link To" override controls (plus "Use add-on default"), and updated front-end rendering so each option now outputs the requested click behavior per gallery/block.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.93 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new Media Library Tag Gallery setting: "Allow Simple Lightbox when clicking on a thumbnail" with full save/load support in add-on defaults and block-level "Use add-on default" controls.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When Simple Lightbox thumbnail-click mode is enabled, gallery thumbnail clicks now open an image-only lightbox view (close button + image) and hide advanced modal controls for a simpler experience.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.92 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media > Library Tags > Bulk Editor now groups terms into two sections: tags detected as "Live in Menu Navigation" first, followed by all remaining tags.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added section header rows in the Bulk Editor table so navigation-live tags are separated from non-navigation tags for faster editing workflow.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.91 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags placeholder replacement now supports [site-title] alongside [tag-name] and [tag-description], including document title replacement paths.', 'user-manager'); ?></li>
							<li><?php esc_html_e('[site-title] now resolves to your WordPress site title (blogname), allowing combined title templates such as "[tag-name] | [site-title]".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.90 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Restricted Access now includes a new checkbox under "Full Screen Overlay Image": "Display as normal Image Above No access message instead of Background Image".', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, the overlay image is rendered as normal content above the no-access message (instead of as a full-screen background image) in both standard overlay mode and background-HTML overlay mode.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.89 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media > Library Tags > Bulk Editor now lists only individual tag terms and skips combined comma-style tag names (example: "Pets, Dating").', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Editor table rows now focus on single-token Library Tags only, so terms like "Pets" and "Dating" appear as separate editable rows when those individual tags exist.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.88 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags filter dropdowns in Media Library list/grid now append a trailing star (*) to tag options that are also detected in active navigation menu URLs.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Reused the existing menu-slug matching logic so the starred dropdown indicators stay aligned with the "Live in Menu Navigation" badge behavior used in the Bulk Editor.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.87 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed front-end inline per-photo admin tag controls from Media Library Tags gallery output (tag list + Hide + Duplicate links).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Cleaned related inline-control CSS/JS hooks so those admin inline controls no longer render or bind click handlers in gallery runtime.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.86 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Restricted Access now includes a new setting: "Still Allow Full Page HTML to be Rendered in Background behind Overlay for Social Media Share Link Meta Data".', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled in Full Screen Overlay mode, full page HTML (including social meta tags) is still rendered while a fixed overlay is injected on top for visitors.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.85 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reverted admin per-photo tag controls (tag list + Hide/Duplicate) back to the non-overlay inline placement under each image for clearer visibility and interaction.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added extra bottom spacing for Mosaic Grid image wrappers so inline admin controls have more room and are easier to read/click in mosaic layout.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.84 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Administrator-only per-photo tag actions (tag list + Hide/Duplicate) are now rendered as an overlay pinned to the bottom of each gallery image instead of below the photo flow.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Overlay styling includes a subtle dark backdrop and per-style spacing adjustments so controls remain readable while staying visually attached to each photo tile.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.83 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags front-end gallery now shows administrator-only per-photo tag tools under every image (all gallery styles), including the current tag list and quick-action links for Hide and Duplicate.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Hide/Duplicate quick actions now add tags via AJAX without page refresh and immediately update the per-photo admin tag tool display state for that image.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.82 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed full-size image href usage from Media Library Tags modal-window triggers and switched lightbox click targets to non-link modal trigger elements so browser navigation to image files cannot occur.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Lightbox runtime now resolves image sources from dedicated modal trigger data attributes, ensuring Mosaic Grid and other gallery styles open the modal window directly instead of following file URLs.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.81 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Mosaic Grid modal-window click behavior by hardening gallery link hit areas so image clicks consistently reach the lightbox trigger across irregular mosaic tiles.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added explicit full-tile link coverage and click-layer priority for gallery links to ensure front-end modal opening remains reliable in mosaic style.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.80 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Media Library Tags modal window opening regression by forcing gallery image click behavior to always render lightbox/modal links, including legacy blocks that still carried old Link To values.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Block defaults and server-side rendering now normalize old link settings to modal window mode so images reliably open the modal on click.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.79 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags block/editor UI now removes the old "Link To" setting and presents the new Modal Window behavior as the standard image interaction mode.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Gallery rendering now defaults to modal window opening for images, while legacy saved link-to values are still safely normalized for backward compatibility.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.78 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Restricted Access add-on now includes a new "Access History" card that displays a log table with timestamp, IP, IP location, browser, URL accessed from, password used, and failed password values.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added persistent restricted access history storage and automatic logging for shared-password submissions, including both successful and failed attempts.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.77 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Restricted Access add-on now includes a new "Full Screen Overlay Image Max Width" setting directly under the overlay image URL field.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When an overlay image is used, it now renders at width: 100% and applies the optional max-width value when provided.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.76 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Mosaic Grid mobile behavior now preserves mosaic tile sizing when Number of Columns (Mobile) is 3 or more, instead of forcing all tiles to uniform size on mobile.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When mobile columns are 1–2, mosaic mobile reset behavior remains active for readability; 3+ columns now keep mosaic large/tall/wide tile spans.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.75 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media > Library Tags > Bulk Editor slug column now flags terms that appear in active menu navigation URLs with a "Live in Menu Navigation" badge beneath the slug input.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Menu-match detection scans active nav menu item URLs and performs boundary-aware slug token matching so manually linked tag slugs are easier to identify before editing.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.74 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Stabilized Media Library Tag Gallery lightbox modal open behavior by replacing brittle pointer/capture interception with a simpler delegated click-to-open modal flow per gallery instance.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added lightbox modal color controls (background + text) to add-on defaults, settings save handling, and block-level override controls with "Use add-on default" toggles.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Lightbox modal captions, Previous/Next controls, keyboard navigation, and slideshow controls are now rendered on top of the new modal color variables for consistent behavior and appearance.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.73 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed the extra front-end CSS typography/color rule on .um-media-library-tag-description-paragraph so description paragraphs inherit theme/plugin defaults.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.72 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added plugin version display directly under the main "User Experience Manager" admin page title.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Version line now mirrors the page-header style pattern and automatically reflects the current plugin version constant.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.71 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Library Tags term edit/add screens now include a new "YouTube Video Links" textarea (one URL per line) with sanitized storage in term meta.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media > Library Tags > Bulk Editor now includes a matching "YouTube Video Links" column so links can be edited and saved for all tags from one screen.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library Tag Gallery front-end now embeds tag-level YouTube videos above the gallery and below album descriptions, using one full-width video for a single link or a two-column layout for multiple links.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.70 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Dynamic Photo Gallery with Media Library Tags now includes a new "Display Album Tag Description(s)" default setting (none / above gallery / below gallery).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library Tag Gallery block now includes a matching per-block override with "Use add-on default", and renders single/multi-tag descriptions (with admin edit links) above or below the gallery using the same URL tag expression logic as [tag-description].', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.69 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Library Tags Bulk Editor post-save redirect URL to load through upload.php, preventing the "Cannot load um-media-library-tags-bulk-editor" error after Save All.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Editor now returns to the correct admin page route with the success count notice after saving.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.68 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a protective admin-bar color reset stylesheet so plugin/user WP-Admin CSS rules no longer override default WordPress top-bar link colors.', 'user-manager'); ?></li>
							<li><?php esc_html_e('WP-Admin bar links and labels now stay on WordPress default white/light-blue states instead of inheriting blue link styles from broad custom CSS selectors.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.67 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Lightbox debug activation now has a server-side fallback path so ?um_mltg_debug=1 reliably enables diagnostics even when front-end URL parsing is altered by caching/redirect layers.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a visible in-gallery debug badge and runtime debug data attributes so debug state can be verified at a glance without opening browser developer tools.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.66 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Hardened front-end lightbox opening by intercepting gallery link activation earlier in the interaction lifecycle (capture-phase down events) before third-party click handlers can force file navigation.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added duplicate-open suppression for click-after-pointer flows so links cannot navigate away while still avoiding double-opening the overlay.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.65 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Media Library Tags Bulk Editor admin menu registration so the screen reliably appears under Media in wp-admin.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Editor submenu registration now safely ensures the Library Tags taxonomy is registered before adding the menu item to avoid hook-order/timing misses.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.64 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added URL-based front-end lightbox diagnostics for Media Library Tags gallery: use ?um_mltg_debug=1 to enable detailed click/open/control logs for each gallery instance.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Debug mode now includes an on-page floating panel and optional ?um_mltg_debug_open=1 auto-open behavior to quickly isolate whether failures are in click interception or overlay rendering.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.62 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Refactored front-end lightbox runtime to a single source of truth by removing the global fallback overlay runtime that could conflict with per-gallery control wiring.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Lightbox control buttons now strictly honor settings for visibility/hidden/disabled state, preventing slideshow button display when disabled and restoring reliable control interactions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.61 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed lightbox Previous/Next/Play controls so click handlers no longer no-op when feature toggles are off but controls are visible.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Lightbox navigation now consistently advances/reverses images and slideshow playback whenever a lightbox image is active.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.60 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Library Tags taxonomy screen now includes a new "Bulk Editor" submenu under Media > Library Tags.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Editor provides an all-tags table with editable title, slug, and description fields plus a single Save All action.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.59 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('When a single URL tag value is used (example: ?cruise), [tag-description] now resolves to that one URL-selected tag only instead of expanded query-match aliases.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tag-query expansion for filtering remains active for gallery/media results, while placeholder description output now stays aligned to explicit URL tag input.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.58 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Lightbox click handling was simplified and made fully self-contained so gallery image links reliably open the overlay instead of navigating to the raw image file URL.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed fragile event-default checks in the lightbox intercept path and now force-intercept direct image-link clicks in both capture and bubble phases.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.57 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Lightbox open behavior hardened with direct per-link listeners plus delegated capture/bubble fallbacks, restoring reliable overlay opening instead of direct file navigation.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Expanded tag-filter validation/matching now resolves tokenized filter values in Media Library grid/list and URL override parsing, so filter queries consistently match if the selected tag token exists in a combined tag string.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.56 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library tag filter dropdown now displays only unique individual tags, excluding combined/comma-composed term labels from the filter list.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Applied unique individual-tag options consistently in both list-view and grid-view media filter controls while preserving full term choices for bulk apply.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.55 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Library Tag filter matching for comma-separated tag names so matches are token-order independent (for example, both "Cruise, Honeymoon" and "Honeymoon, Cruise" now match either base filter token).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated compound tag matching to check each normalized token with boundary-aware matching instead of strict token equality, improving consistency between admin media filters and front-end gallery output.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.54 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Newly inserted Media Library Tag Gallery blocks now default all per-setting "Use add-on default" toggles to ON, so new blocks inherit add-on defaults unless manually untoggled.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Applied this default-on behavior in both block attribute registration and editor attribute defaults for consistent behavior on fresh block insertion.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.53 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library tag filtering now includes compound/synonym tag slugs when matching a selected base tag (for example: selecting "cruise" now also matches combined tags like "honeymoon-cruise" and tag names containing "Cruise").', 'user-manager'); ?></li>
							<li><?php esc_html_e('Applied the same expanded tag matching logic to front-end gallery output and Media Library list/grid filters so counts/results stay consistent across admin and front-end.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Gallery query handling now explicitly sets nopaging when Page Limit is 0 (unlimited), preventing environment-level paging fallbacks from truncating results.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.52 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed front-end "Link To: lightbox" click handling so gallery images reliably open the lightbox again after recent admin lightbox tooling updates.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Hardened delegated click target resolution to safely handle non-element event targets while preserving existing lightbox open behavior.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.51 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Front-end gallery lightbox now includes a new admin-only "Duplicate" link that quickly adds a "duplicate" Library Tag to the active image.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added admin-only inline tag tools in the lightbox so tags can be added directly from the image overlay without opening the Media Library edit screen.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Lightbox tag actions use secure AJAX requests with the existing Media Library Tags nonce/capability checks and support comma-separated quick tag entry.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.50 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag placeholder replacement now resolves all URL override tags (single, AND, or OR expressions) in URL order for [tag-description].', 'user-manager'); ?></li>
							<li><?php esc_html_e('When [tag-description] is rendered in content, each matched tag description now outputs in its own paragraph with class "um-media-library-tag-description-paragraph".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Administrator edit links are now appended per tag description paragraph so each active tag description includes its own direct edit shortcut.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.49 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags gallery defaults now include a new Slideshow Transition setting with options: None, Crossfade, and Slide to Left.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library Tag Gallery block now includes a per-block Slideshow Transition control with a "Use add-on default" toggle.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Front-end slideshow playback now applies the configured transition style while advancing images in the lightbox.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.47 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Add-ons section save redirect sticking to the wrong add-on after save in nested-form contexts.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email Log filter form now uses the prebuilt add-on base URL directly and no longer injects duplicate addon_section fields that can leak into unrelated Add-ons settings saves.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed redundant addon_section hidden inputs from Email Log action forms (resend/forward/clear) to prevent Add-ons save routing collisions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.46 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed My Account Admin search form button clickability on frontend endpoint pages by strengthening stacking and pointer-event styles for the search form and submit button.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.45 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Email Log add-on UI wording was corrected from "Emali" to "Email" for user-facing labels/buttons, including "Clear Email Log History".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a new modal HTML preview action in Email Log rows so rendered email HTML can be opened in a popup window without leaving the table.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.44 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Updated Documentation tab content to reflect the current tab architecture and new sub-page routing patterns.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Docs now explicitly describe Login Tools sub-pages (tab=login-tools with login_tools_section), Settings-related sub-pages (tab=settings/tab=email-templates/tab=tools), Add-ons block-specific deep links (addon_section/block_section), and Documentation docs_section sub-pages.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Documentation catalogs were refreshed to include current Add-ons/Blocks naming and recent modules.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.43 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Email Log now includes a configurable retention setting: "Auto-delete log entries after X days" (0 keeps logs forever).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added automatic Email Log cleanup of old rows using daily WP-Cron scheduling plus a once-per-day fallback on normal requests.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Clear Email Log History now also resets cleanup timing metadata so future retention cleanup restarts cleanly.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.42 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new Add-on: Email Log.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When activated, Email Log records outgoing wp_mail events with full email header columns (To, From, Reply-To, CC, BCC, Content-Type), subject, status, and message body data.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Email Log tools for HTML email preview + raw source, resend email, forward email to another address, status/search filters, pagination, and clear-history controls.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Email Log stats cards for sent volume in the past hour, day, week, month, plus total logged emails.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.41 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons filter tags now include two new categories: Email and SMS.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email/SMS tag matching is keyword-driven so messaging-related add-ons appear under those new filters.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.40 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Add-ons/Blocks save regression where normal section saves could be incorrectly treated as temporary-disable-only saves, preventing per add-on/block deactivation.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Temporary-disable-only flow now starts from persisted raw settings only for that dedicated save action, while regular saves continue to process activation checkbox changes normally.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.39 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed Add-ons temporary-disable toggle save flow so unchecking "Temporarily disable all add-ons runtime functionality" no longer clears active add-on checkboxes.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Settings saves now start from persisted raw settings (without runtime disable overrides), preventing runtime-disabled values from being written back as false.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.38 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Invoice Approval invoice page now shows a summary directly under "Products & Services" with total line item count and total quantity (for example: "Total Line Items: 3 (Total Quantity: 14)").', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.37 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed fatal error on Add-ons/Blocks screens by adding missing User_Manager_Core::get_raw_settings() compatibility method.', 'user-manager'); ?></li>
							<li><?php esc_html_e('get_raw_settings() now returns persisted plugin options without runtime temporary-disable overrides, restoring admin screen rendering.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.36 <span>(April 3, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Updated AI Notes/rules for Documentation > Versions updates so new changelog entries must use the real current date at edit time.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Prevents stale hardcoded changelog dates from being reused for future docs_section=versions updates.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.35 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Temporarily Disable controls are now split and independent: Add-ons has its own temporary-disable toggle, and Blocks has its own temporary-disable toggle.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons/Blocks screens now read saved (raw) activation states so cards remain checked/highlighted while temporary-disable is active; runtime behavior is disabled without changing active checkbox values.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added temporary-disabled visual badges/icons on Add-ons and Blocks index views while their respective temporary-disable toggle is enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.34 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed "Temporarily Disable All" save behavior so using that card no longer unchecks/deactivates individual add-ons or blocks in saved settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Temporary disable now applies at runtime only and now also affects User Role Switching and Send Email while enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.33 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Blocks tab: moved the "Temporarily Disable All" card to the main tab=blocks index view (where all blocks are listed).', 'user-manager'); ?></li>
							<li><?php esc_html_e('When viewing/editing an individual block via block_section, the temporary-disable card is now hidden.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.32 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('WP-Admin Bar Quick Search add-on now includes a new checkbox list setting: "Priority Post Types to Display Before All Remaining Post Types".', 'user-manager'); ?></li>
							<li><?php esc_html_e('When set, selected post types are shown first in the Quick Search dropdown, while remaining post types continue to display alphabetically after the prioritized group.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.31 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab: the "Temporarily Disable All" card now appears only on the main tab=addons view.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When editing/viewing a specific add-on via addon_section, the temporary-disable card is now hidden to keep section screens focused.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.30 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved the default "User Experience Manager" WP-Admin top-bar shortcut behind a new Settings > User Experience checkbox.', 'user-manager'); ?></li>
							<li><?php esc_html_e('The admin-bar shortcut is now OFF by default and only appears when "Show User Experience Manager link in WP-Admin top bar" is enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.29 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons + Blocks tabs now include a bottom "Temporarily Disable All" card with checkbox and Save button to temporarily override and disable all add-ons/blocks runtime functionality.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Runtime disable-all now supports both URL-based override and saved settings-based override, and applies to all add-ons/blocks (including Send Email) until unchecked and saved.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.26 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new Add-ons card: Product Notification, including global style controls for notification and button colors (normal + hover states).', 'user-manager'); ?></li>
							<li><?php esc_html_e('When Product Notification is active, WooCommerce Product Data > General now includes a per-product field: "Display a Woocommerce Notification Above Product at All Times?" plus optional button title and URL.', 'user-manager'); ?></li>
							<li><?php esc_html_e('If a product notification message is set, a WooCommerce-style message now renders above the single product page at all times, with support for %PRODUCT_TITLE% replacement and optional custom button link.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.25 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Restricted Access logged-out redirect modes are now authoritative: "Redirect to My Account" and "Redirect to WP-Admin" always redirect blocked visitors and never render the full-screen overlay for those modes.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Shared-password gating remains tied to Full Screen Overlay mode so redirect behavior is consistent when redirect modes are selected.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.23 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Post Meta Viewer edit mode no longer posts every existing meta field on save; unchanged rows are now excluded from form submission to prevent oversized product-edit requests.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fixes WooCommerce product fields (including price and related settings) being dropped when "Allow editing of post meta values" is enabled and forms exceed PHP input limits.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.21 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new SEO Basics add-on with activation toggle in Add-ons tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When active, adds an "SEO Basics" meta box to Posts and Pages with Page Title Override and Page Description Override fields.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Front-end meta image now resolves automatically using Featured Image first, then falls back to the first image found in the page/post content.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.20 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Gallery placeholder fix: [tag-name]/[tag-description] replacement in HTML document titles now resolves the current singular post context directly from the queried object when get_queried_object_id() is unavailable during title-generation timing.', 'user-manager'); ?></li>
							<li><?php esc_html_e('This restores reliable <title> replacement for browser/SEO title output while keeping existing page title/body placeholder behavior unchanged.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.19 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-on runtime gating hardening: My Account Site Admin hooks are now only initialized when that add-on is active (including URL temporary disable support).', 'user-manager'); ?></li>
							<li><?php esc_html_e('New User Coupons debug panel hook now registers only when the New User Coupons add-on is active, preventing debug UI from running while the add-on is off.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.18 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Remaining Balance notices (checkout + order received) now suppress currency labels when the configured currency symbol/name is longer than 1 character (e.g. "Points"), showing numeric-only values like 90.00.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Single-character currency symbols (such as $) continue to display as before.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.17 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings > Email Settings now defaults empty "Send From Name" to the website title, and defaults empty "Send From Email Address" / "Reply To Email Address" to noreply@{site-domain}.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Email header generation now applies the same fallbacks at runtime so outbound emails remain consistent even when those fields are left blank.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.16 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Updated "Send automated remaining balance coupon" demo email body to include both remaining balance amount and coupon code using %COUPONCODEVALUE% and %COUPONCODE%.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added %COUPONCODEVALUE% placeholder support across remaining-balance email send and preview flows, including automatic value resolution from the coupon amount.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.15 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons > User Coupon Remaining Balances: fixed Preview Email button reliability by binding the click handler with delegated events, so it works consistently even when the add-on card is conditionally rendered/toggled after page load.', 'user-manager'); ?></li>
							<li><?php esc_html_e('This now matches the proven preview-modal flow used by other template preview buttons and avoids missed direct-bind timing issues.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.14 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Core hook registration hardening: New User Coupons, Checkout Address Selector (including debug footer hook), Quick Search, and Staging & Development Environment Overrides hooks now only register when their associated add-on is active.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Prevents add-on behavior from running when the add-on is disabled, reducing lingering side effects from inactive modules.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.13 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Staging & Development Environment Overrides: made the WooCommerce webhook-delivery filter callback signature backward-compatible with both 3-argument and 4-argument WooCommerce hook calls to prevent fatal argument-count exceptions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Prevents uncaught exceptions like "Too few arguments to function maybe_block_staging_dev_woocommerce_webhook()" from interrupting checkout/account flows when webhook delivery checks run.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.12 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('User Coupon Remaining Balances generation is now deferred to a scheduled background event from order status hooks, preventing checkout/order-status transition errors from bubbling into customer-facing order-processing failures.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added per-order processing lock for remaining-balance generation to avoid duplicate/concurrent processing and to harden reliability under multiple status/thank-you triggers.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.11 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('User Coupon Remaining Balances runtime now fully respects add-on activation state before registering checkout/thank-you hooks, so no remainder notices or processing run when the add-on is turned off.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added defensive error handling around remainder coupon generation to prevent checkout/order completion from failing if coupon processing encounters an exception.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.10 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons > User Coupon Remaining Balances: fixed Preview Email button by rendering the shared email preview modal on the Add-ons page context.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Preview now correctly opens for the selected template in "Send Email to User when New Remaining Balance Code is Created", including %COUPONCODE% and [coupon_code] placeholder substitution.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.9 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Import Demo Email Templates now includes individual "Recreate" links for each demo template to upsert one template at a time without running the full import.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added secure admin-post handler for recreating single demo templates and a success notice message when a template is recreated.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.8 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Import Demo Email Templates now includes a new "Send automated remaining balance coupon" template with %COUPONCODE% support.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added default subject/body for the remaining-balance template: "You have a remaining balance" and coupon-code output in the message body.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.7 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new Restricted Access add-on with logged-out behavior controls (redirect to My Account, redirect to WP-Admin, or full-screen overlay).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Restricted Access now supports shared password gate, appended URL string access, configurable re-auth time limit, role exclusions, custom no-access message, and centered full-screen overlay styling with background/text colors and image.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.6 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Order Received Page Customizer: improved H1 override reliability by adding fallback output hooks and title filters for thank-you page contexts where theme/Woo templates bypass woocommerce_page_title.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Custom heading now applies to page title, browser title parts, and template-rendered order-received heading blocks with stronger compatibility across themes.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.5 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Edit Email Templates shortcut links now pass an auto-expand flag so Add-ons > Send Email opens with the Email Templates panel expanded.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.4 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Send Email > Custom Email Lists table: action buttons (Edit, CSV, Delete) now render one-per-line for clearer row layout and easier click targets.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.3 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Hardened Send Email always-on behavior: URL runtime disable overrides can no longer disable send-email-users.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons UI now forces Send Email fields visible in send-email-users section, even without an Activate checkbox state.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.2 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Send Email add-on is now always active and cannot be deactivated from Add-ons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login Tools and other email-template consumers now always have Send Email templates/features available without activation dependencies.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.5.1 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Coupon edit screen: fixed Email List Converter field handling to avoid conflicting with WooCommerce\'s native Allowed emails input/select2 behavior.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed runtime replacement of the core customer_email field and now update the native field value directly, preventing editor UI spinner/locking issues.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.99 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reports tab: date-based reports now show a Start Date / End Date filter directly under the Query summary when selected.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Applied date-range filtering to date-enabled report datasets and pagination URLs, including tracking reports in the extracted tracking trait.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.98 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reports label update: renamed "Coupon Audit" to "Coupons Audit" in report naming and documentation references for consistent wording.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.97 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reports tab: added a contextual "Query summary" description area under the Select report dropdown that appears when a report is selected.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added human-readable query explanations for all report options so admins can understand what each report is querying at a glance.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.96 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Login Tools > Login As now includes a security note clarifying that temporary passwords are automatically restored after 15 minutes if not manually restored.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added Login As auto-restore expiry handling on normal page loads so original password hashes are reinstated after the 15-minute temporary session window.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.95 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Documentation tab content was refreshed to match the current top-level tab architecture, including Login Tools, Add-ons, Blocks, and Documentation sections.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated docs now include a dedicated Blocks Reference (with Dynamic Photo Gallery with Media Library Tags) and revised About/Installation copy for the current module structure.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.94 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Renamed "Media Library Tags & Photo Gallery" to "Dynamic Photo Gallery with Media Library Tags" across add-on/runtime labels.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved this gallery add-on into the Blocks tab (tab=blocks) and removed it from Add-ons, including block-section routing/save handling.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.93 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new top-level "Blocks" tab (page=user-manager&tab=blocks) next to Add-ons, with dedicated filtering and section navigation for content blocks.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Moved the four Page Block add-ons (Subpages Grid, Tabbed Content Area, Simple Icons, Menu Tiles) out of Add-ons into the new Blocks tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Refined block tags and copy to be content-focused (for example Icons, Grids, Tabs, Content, Navigation, Shortcodes) and updated block card descriptions/titles accordingly.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.92 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('When [tag-description] resolves from URL-based Library Tag context, WordPress administrators now see an inline "Edit Tag Description" link appended to that placeholder output for faster term editing.', 'user-manager'); ?></li>
							<li><?php esc_html_e('The new edit link is intentionally limited to front-end content replacement only; title and document-title placeholder replacement remain plain text.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.91 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag URL placeholders now also apply to the page document title (<title>) so [tag-name]/[tag-description] can resolve in browser tab titles and SEO title output.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Placeholder replacement remains scoped to singular content that contains a Media Library Tag Gallery block with URL override behavior enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.90 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags add-on "Style" setting is now sorted A–Z for easier scanning in addon_section=media-library-tags.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added new "Accent Color (frames/backgrounds)" setting so white UI surfaces in gallery styles (such as Polaroid/Split/Carousel controls) can be recolored for dark mode websites.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.89 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag Gallery defaults now include: "Do Not CSS Crop Any Images if Gallery Photos Total is Less Than..." (0 keeps CSS crop behavior always enabled).', 'user-manager'); ?></li>
							<li><?php esc_html_e('When gallery image total is below this threshold, CSS crop styles are disabled so images render using natural proportions (no CSS crop/aspect-ratio forcing).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.88 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Mosaic Grid layout now uses a deterministic repeating pattern with dedicated large, tall, and wide tiles for more predictable balance.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added consistent grid auto-row sizing plus dense packing behavior so smaller items backfill gaps more reliably and reduce dead space.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.87 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag Gallery URL override now supports multiple-tag expressions in the standard ?tag= parameter.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Use ?tag=tag+tag2 to require BOTH tags (AND), and ?tag=tag_tag2 to match EITHER tag (OR).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.86 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Front-end gallery lightbox now shows an "Edit image" link for WordPress administrators.', 'user-manager'); ?></li>
							<li><?php esc_html_e('The admin-only lightbox shortcut opens the selected image directly in Media Library edit mode for faster metadata/content updates.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.85 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Mosaic Grid layout now uses column-aware tile span patterns to reduce stranded gaps and improve backfilling of smaller images.', 'user-manager'); ?></li>
							<li><?php esc_html_e('On mobile widths, Mosaic Grid now disables span overrides so tiles flow naturally and avoid empty-looking pockets.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.84 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag Gallery pagination is now rendered as basic links (unstyled link list) instead of button-styled pagination controls.', 'user-manager'); ?></li>
							<li><?php esc_html_e('The current gallery page number now displays in bold text for clearer active-page visibility.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.83 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a front-end WP-Admin top bar shortcut: "Edit Library Tag" when a Media Library Tag Gallery URL tag is actively being viewed.', 'user-manager'); ?></li>
							<li><?php esc_html_e('The shortcut only appears for users who can edit the resolved Library Tag and links directly to that term\'s edit screen in wp-admin.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.82 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags add-on defaults: added desktop column thresholds for album size — "Number of Columns (Desktop) if less than 50 photos", "if less than 25 photos", and "if less than 10 photos".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library Tag Gallery now auto-selects desktop column count by image count (<10, <25, <50), while the existing Desktop Columns value remains the default for 50+ images.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.81 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added [tag-description] placeholder support alongside [tag-name] for Media Library Tag Gallery URL override-driven text replacement in post titles/content.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When a matching URL tag is found, [tag-description] now resolves to that Library Tag description; if not found, it resolves to an empty string.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.80 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed [tag-name] replacement timing for post titles/content by making placeholder config cache post-specific, preventing early false cache hits.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When "Allow Any URL Parameter..." is enabled, [tag-name] replacement now activates if either URL override toggle is enabled on the gallery block.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.79 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag Gallery block URL override setting note now documents [tag-name] placeholder replacement in post titles/content when URL tag override is active.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When no valid URL tag is found, [tag-name] now resolves to an empty string, as documented in the setting note.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.78 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag Gallery block: added "Allow Any URL Parameter to Be Used as a Tag Identifier such as ?tag-name for Shorter URLs" under URL override settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, URL query keys that match a Library Tag slug can act as the gallery tag override (in addition to standard ?tag=tag-slug).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.77 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag Gallery block: added "Do Not Allow Empty Tag / Do Not Load without Tag Value" setting under URL tag override options.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, the gallery block returns no output unless a tag is selected (via block setting or URL override), preventing all-images loads on empty tag values.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.76 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library "No tags" filter: fixed query handling to remove conflicting Library Tag clauses before applying the no-tags condition.', 'user-manager'); ?></li>
							<li><?php esc_html_e('No-tags filtering now reliably returns attachments with no Library Tags assigned in both list and grid Media Library views.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.75 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags & Photo Gallery: added "Random" to Sort Order options in add-on defaults and block-level controls.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Gallery rendering now supports random image ordering when Sort Order is set to Random.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.74 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library tag filter label updated from "No Tags" to "No tags" in list and grid views.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Fixed "No tags" filtering to reliably return attachments with no Library Tags assigned by excluding all existing Library Tag term IDs via taxonomy NOT IN.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.73 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags add-on: added "Keep Media Library bulk tools header visible on mobile while scrolling" setting.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, the Media Library toolbar/header becomes sticky on mobile viewport widths so Bulk Select tools remain accessible without scrolling back to the top.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.72 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library tag filter: added a new "No Tags" option at the top so admins can quickly show only images with no Library Tags assigned.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Both list and grid Media Library views now apply a taxonomy "NOT EXISTS" query when "No Tags" is selected.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.71 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Edit User/Profile top notice is now hidden by default.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Settings > User Experience: added "Show notice at top of Edit User/Profile pages" checkbox so admins can enable that notice when desired.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.70 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags add-on: added "Show Tags on Thumbnails when Bulk Selecting" setting to display each selected image\'s Library Tags directly on media thumbnails in bulk-select mode.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library Tags & Photo Gallery: added "Tags to hide from front end gallery" (comma-separated) setting to permanently exclude matching Library Tags from front-end gallery output.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Example supported: create/use a Library Tag named "hide", then add hide in this setting to ensure those tagged images never render in front-end galleries.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.69 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags & Photo Gallery: added new default settings "Description Display" (none, centered under photo, lightbox under photo, both) and "Description Value" (caption, title, description, alt text, filename, slug, date).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library Tag Gallery block now includes matching per-block override controls for Description Display and Description Value, including "Use add-on default" toggles for each.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Gallery rendering now outputs description text from the selected value source under photos and/or inside the lightbox (with dynamic lightbox caption support), based on the selected display mode.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.68 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags copy tweak: updated "enter tag" to "or enter tag" for bulk tag input placeholders.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Adjusted Media Library bulk-select toolbar alignment so Apply Tag controls sit lower and align better with Delete/Cancel controls.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.67 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags copy updates: changed "All Library Tags" to "All tags", "Bulk apply: choose Library Tag" to "Apply Tag", "or enter new Library Tag" to "enter tag", and "Apply Library Tag" to "Apply Tag(s)".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library bulk tag controls now stay hidden until Bulk Select mode is active in both list and grid views, reducing toolbar clutter.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.66 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tag Gallery block: added per-setting "Use add-on default" toggles for Columns (Desktop/Mobile), Sort Order, File Size, Style, Page Limit, and Link To.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled per setting, the block inspector disables that field and uses the corresponding default configured in the Media Library Tags & Photo Gallery add-on settings.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Front-end render now honors these per-setting default toggles while preserving existing block behavior when toggles are not enabled.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.65 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library grid toolbar: ensured Library Tag bulk controls remain visible in bulk-select mode by using dedicated control classes instead of default attachment-filters visibility toggles.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk apply dropdown, new-tag text field, and Apply button now stay accessible in the media grid bulk-apply toolbar.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.64 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library attachment editor: added quick tag links above the Library Tags help text so clicking a tag auto-inserts it into the current item\'s Library Tags field.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Quick links append tags without duplicates and target the correct attachment field in the current media row/modal context.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.62 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags filter now reliably renders on Media Library "All items" views by allowing empty post_type values in the upload.php filter-control hook.', 'user-manager'); ?></li>
							<li><?php esc_html_e('This restores Library Tag filtering visibility in list/grid contexts where WordPress does not pass "attachment" explicitly.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.61 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags & Photo Gallery: restored legacy style options "Standard", "Square CSS Crop", "Wide Rectangle CSS Crop", "Tall Rectangle CSS Crop", and "Circle CSS Crop" alongside the newer style layouts.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Re-added these legacy styles across add-on settings, block editor Style selector, save validation, and front-end rendering classes so existing selections remain compatible.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.60 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Bulk Add to Cart shortcode: fixed undefined variable warning for $product_id_column_header by initializing the display header before the How-To instructions render.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Bulk Add to Cart shortcode: fixed undefined variable warnings for $show_sample_csv and $show_sample_with_data by initializing the sample-download visibility flags before rendering links.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.58 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Media Library Tags & Photo Gallery: updated Style choices to 12 layout modes (Mosaic Grid, Masonry/Pinterest, Uniform Grid, Justified Rows, Carousel/Slider, Fullscreen Lightbox Grid, Horizontal Scroll, Polaroid/Scrapbook, Split Screen Feature, Infinite Scroll, 3D Perspective, Timeline/Story).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated Style rendering behavior and front-end scripts/CSS to support all 12 modes with responsive behavior and style-specific interactions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Normalized gallery style keys across add-on settings, block editor controls, and render logic to ensure selected styles save/apply correctly.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.56 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new Add-on: "Media Library Tags" with Activate toggle and description in Add-ons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Adds a new "Library Tags" submenu item under Media using an attachment taxonomy for tag management.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Media Library supports filtering by Library Tag, bulk applying tags (list and grid views), and per-item add/remove tags in attachment details.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.55 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Product Search by SKU add-on default changed to OFF for new installs (must be explicitly activated).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated Product Search by SKU add-on active-state checks so unset settings are treated as disabled by default.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.54 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Staging/Dev front-end notice bar now always renders at the top of the page instead of being output as footer content.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a dedicated footer fallback that injects the notice at the top of <body> for themes that do not call wp_body_open.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.53 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Data Anonymizer > Exceptions to Above: added "Exclude All WP Administrators" and "Exclude User if Email Address Matches Administration Email Address" checkboxes.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Both new exception checkboxes default to checked when first introduced.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Users anonymization now skips matching users for those exceptions and logs skip counts in run notes/history.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.52 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Hotfix: prevented duplicate Gutenberg block registration notices for custom/tabbed-content-area by guarding block registration with WP_Block_Type_Registry checks.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Tabbed Content Area add-on now safely skips registering custom/tabbed-content-area and custom/legacy-tabbed-content-area when they already exist.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.51 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Hotfix: removed a duplicate User_Manager_Core static property declaration (`$staging_dev_notice_rendered`) that caused a fatal "Cannot redeclare" error.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Staging/Dev notice rendering now uses a single property declaration, restoring normal plugin loading.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.50 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tag filter: added a new "Security" tag and positioned it directly after "Users" in the Add-ons sub-navigation.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated security-related add-on descriptions so they are matched by the new Security filter (for example: Security Hardening, Webhook URLs, Post Meta Viewer access controls, and related safety/security tools).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.49 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Edit User/Profile notice now includes a "Login As This User" button that links directly to Login Tools > Login As.', 'user-manager'); ?></li>
							<li><?php esc_html_e('The new button pre-fills the selected user email and can auto-run "Generate Temporary Password" to reduce clicks for admins.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login As screen now supports URL-driven prefill and auto-generate behavior for faster user impersonation workflows.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.48 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new Add-on: "Staging & Development Environment Overrides" with Activate toggle and default-on safety settings for disabling emails, payment gateways, webhooks, and API/JSON requests in non-production.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added non-production notices for both front-end top bar and WP-Admin, each enabled by default when the add-on is active.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added optional "Data Anonymized" timestamp note in non-production notices, sourced from the latest Data Anonymizer history run.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.47 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new Add-on: "Data Anonymizer" with Activate toggle and grouped settings for Order Data, User Data, Form Data, and exception domains.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added dedicated run actions/buttons for Orders, Users, and Forms plus a persistent Data Anonymizer History table showing runner, checked settings, counts, notes, and timestamp.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Forms anonymization now supports common storage sources including CFDB7, WPForms, Fluent Forms, Gravity Forms, Ninja Forms, Formidable Forms, and Flamingo (CF7 storage).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.46 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Coupon Remaining Balances add-on: added a new option to send an email whenever a new remaining balance code is created.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a template selector with a default-template option, an Email Templates shortcut link, and a Preview Email button directly in the add-on.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Remaining-balance emails now support both %COUPONCODE% and [coupon_code] placeholders and use Email Settings sender values for From/Reply-To headers.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.45 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Tabbed Content Area block editor now supports both a Page/Post selection dropdown and a manual Page/Post ID field for each tab.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When both values are saved, manual Page/Post ID now overrides the dropdown selection, as requested.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.44 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings > User Experience: retitled the field label to "Legacy/Broken Shortcodes (comma-separated)".', 'user-manager'); ?></li>
							<li><?php esc_html_e('When set, the plugin now registers empty shortcode handlers for listed tags (and lowercase variants) so removed shortcode sources do not break legacy content.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Page Block: Tile Grid for Subpages no longer owns this setting; it is now managed centrally in User Experience settings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.43 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings > User Experience: added "Legacy/Broken Shortcodes to No-op (comma-separated)" as a standard setting.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When set, the plugin now registers empty shortcode handlers for listed tags (and lowercase variants) so removed shortcode sources do not break legacy content.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Page Block: Tile Grid for Subpages no longer owns this setting; it is now managed centrally in User Experience settings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.42 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed the "Legacy/Broken Shortcodes to No-op (comma-separated)" setting from Page Block: Tile Grid for Subpages.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed related legacy no-op shortcode registration hooks and setting persistence for that option.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.40 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added four new Page Block add-ons with Activate toggles in Add-ons: Tile Grid for Subpages, Tabs with Content from Other Pages, Simple Icons, and Tile Grid for Menu.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, these add-ons now register their related Gutenberg blocks and editor UI scripts: custom/subpages-grid, custom/tabbed-content-area, custom/simple-icon, and custom/menu-tiles.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Page Block: Tile Grid for Subpages includes [subpages_grid] support with front-end rendering.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.39 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Site Admin > Orders: added two new settings under "Hide Order Status" to optionally add WebToffee invoice action buttons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added "Add WebToffee WooCommerce PDF Invoices Download Invoice Button" and "Add WebToffee WooCommerce PDF Invoices Print Invoice Button" toggles.', 'user-manager'); ?></li>
							<li><?php esc_html_e('When enabled, Admin: Orders now pulls WebToffee invoice action URLs from WooCommerce account-order actions and appends matching Print Invoice / Download Invoice buttons to each order row when available.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.38 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('My Account Admin Orders search now supports direct order IDs, order numbers with/without "#" prefixes, and partial order-number matching.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added sequential-order-number meta search support (including common "_order_number" style meta keys) for "Sequential Order Numbers Pro" style values.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Expanded Admin Orders search matching to include order-number meta fields and normalized search variants for better partial matching.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.37 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed early translation loading notice by hardening add-on runtime label translation to never run before the init hook.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added explicit plugin textdomain loading on init for the user-manager domain.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.36 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added new Add-on: "Add to Cart Min/Max Quantities" with an Activate toggle in Add-ons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Adds WooCommerce product Inventory fields for "Minimum quantity" and "Maximum quantity" (per product).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Enforces min/max quantity rules during add-to-cart validation and cart/checkout quantity validation notices.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.35 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Post Meta Viewer add-on: added role-based access controls ("Allowed Roles") to decide which roles can view the meta box.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Post Meta Viewer add-on: added username/email allow-list ("Allowed Usernames/Emails"), one per line.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Access matching now supports role OR username/email logic, and defaults to allow all post editors when both lists are empty.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.34 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Post Meta Viewer add-on: added a post type checkbox list so admins can limit the meta box to selected post types.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Default behavior remains enabled for all post types when no specific selections are saved.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.33 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reports > Admin Log: removed the "Add-ons Connected to Admin Log" card.', 'user-manager'); ?></li>
							<li><?php esc_html_e('The Activity Log table and filters remain available; only the add-ons summary panel was removed.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.32 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Deactivate User(s): added a new "Deactivated Users History" card under the deactivated users list.', 'user-manager'); ?></li>
							<li><?php esc_html_e('The new history keeps a persistent running log of deactivation and reactivation events (with date, action, user, identifier, before/after values, and actor).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Deactivate and Reactivate actions now append entries to this history so previous status changes remain visible even after reactivation.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.31 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Deactivate User(s): input now accepts usernames in addition to email addresses.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Deactivate User(s): added a per-user Reactivate button in the "Deactivated Users" table.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Reactivation now clears deactivation flags and restores login/email values (with uniqueness safeguards) so accounts can sign in again.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.30 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a sub-navigation spacing override so cards/layout wrappers directly under `.subsubsub` no longer add extra top gap.', 'user-manager'); ?></li>
							<li><?php esc_html_e('This includes wrappers like `.um-admin-grid`, `.um-admin-card`, `.um-create-user-layout`, and `.um-email-templates-layout` when they appear immediately below sub-navigation.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.29 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tab: removed extra top spacing beneath the add-on tag sub-navigation by clearing top margin on the top-level add-on grids/cards.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.28 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Retitled the throttle count label from "Texts Per Batch" to "Emails/Texts Per Batch" in Settings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.27 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed the standalone "Import Automated Coupon Email" card from Send Email.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Merged coupon template imports into "Import Demo Email Templates" so one import now includes both coupon templates.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated the demo email import list to show all 6 templates, including coupon-focused entries with %COUPONCODE% support.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.26 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved "Import Demo SMS Text Templates" into the SMS Text Templates manager and placed it at the bottom of that panel.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the duplicate "Import Demo SMS Text Templates" card from the surrounding Send SMS Text add-on wrapper.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.25 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved "Import Demo Email Templates" into the Email Templates manager and placed it at the bottom of that panel.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed the duplicate "Import Demo Email Templates" card from the surrounding Send Email add-on wrapper.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.24 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed the legacy top-level "Send Email" navigation tab (`tab=email-users`) now that Send Email is managed as an add-on.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Send Email remains available from Add-ons and optional add-on main-navigation shortcuts.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.23 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved "Import Demo SMS Text Templates" from Tools into the Send SMS Text add-on area.', 'user-manager'); ?></li>
							<li><?php esc_html_e('SMS demo template imports now include automated coupon + $10 apology coupon SMS templates (with %COUPONCODE% support).', 'user-manager'); ?></li>
							<li><?php esc_html_e('SMS import actions submitted from the Send SMS Text add-on now redirect back to that same add-on context with success notices.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.22 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Moved "Import Demo Email Templates" and "Import Automated Coupon Email" into the Send Email add-on area.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Import actions posted from the Send Email add-on now redirect back to that same add-on context with success notices.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Removed those two email import cards from Tools to avoid duplicate management locations.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.21 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Removed "Email Templates" and "SMS Text Templates" sub-links from Settings after moving both template managers into their add-ons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Legacy Settings template URLs now redirect to the relevant add-on cards (Send Email or Send SMS Text).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Template shortcut links now open the add-on template managers directly from Email/SMS template selector fields.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.20 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added a new "Send Email" add-on card with Activate toggle and description, so Send Email is no longer shown by default unless enabled.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Main navigation now shows the Send Email tab only when the Send Email add-on is active.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added an "Email Templates" manager card at the top of the Send Email add-on, collapsed by default and auto-expanded when editing a specific template.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added an "SMS Text Templates" manager card at the top of the Send SMS Text add-on, collapsed by default and auto-expanded when editing a specific SMS template.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Template manager forms now preserve add-on context so save/edit/delete/reorder actions return to the corresponding add-on card.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.19 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Retitled add-on shortcut checkbox label from "Add as Man Navigation Tab" to "Add to Main Navigation".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.18 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Added shortcut edit links next to Email Template selectors so admins can jump directly to Settings → Email Templates.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added shortcut edit links next to SMS Text Template selectors so admins can jump directly to Settings → SMS Text Templates.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Applied template-editor shortcut links across Create, Bulk Create, Reset Password, Email Users, coupon-email template selectors, and SMS texting template selectors.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.17 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons: each add-on now shows an "Add to Main Navigation" checkbox next to Activate when the add-on is enabled.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Main navigation: selected + active add-ons now appear as shortcut tabs to the right of Docs, linking directly to each add-on settings screen.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-on shortcut choices are now saved in plugin settings and automatically hidden when an add-on is not active.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.16 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Order Invoice & Approval: on front-end invoice pages, logged-in WordPress administrators now see an "Edit this order in WP Admin" link at the bottom.', 'user-manager'); ?></li>
							<li><?php esc_html_e('The edit-order link opens in a new browser tab/window and is hidden for non-administrator viewers.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.15 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Send SMS Text: removed "skip on no user match" behavior so valid phone numbers are still sent even when no user is found.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Send SMS Text: improved user lookup by phone using flexible format matching (e.g. 952-200-7732, 9522007732, +19522007732).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated SMS send notices to report "Sent without user match" rather than "Skipped (no user match)".', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.14 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Tools: added a new "Import Demo SMS Text Templates" card next to "Import Demo Email Templates".', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added backend import handler and action for demo SMS templates with nonce and capability checks.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added success notice feedback after importing demo SMS text templates.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.13 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Login Tools: added a new "Deactivate User(s)" sub-menu next to Remove User(s) with bulk email-based deactivation workflow.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Deactivate User(s): preserves account/history data while blocking future logins via a deactivated-user authentication guard.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Deactivate User(s): added optional quiet password reset + optional [YYYYMMDD]-deactivated- login/email prefix behavior (both configurable in Settings).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Deactivate User(s): added a new "Deactivated Users" card with a paginated table of all deactivated accounts.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.12 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Login Tools sub-navigation labels were updated for clarity: Create Single User, Create Multiple Users, Reset Password(s), Remove User(s), and Login As a User.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.11 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Fixed an early translation-loading notice by preventing add-on runtime labels from being translated during pre-init settings bootstrap.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-on runtime toggle labels are now translated only when needed in UI contexts, avoiding _load_textdomain_just_in_time warnings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.10 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Settings > API Keys: added a new "Simple Texting API Token" setting for SMS sending.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Settings sub-navigation: added "SMS Text Templates" next to Email Templates, including full SMS template management.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Add-ons: added a new "Send SMS Text" add-on with Activate toggle and a texting workflow modeled after Email Users (phone numbers, template selection, login URL, coupon code, preview, recent texts, and shared custom lists).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added SMS send + next-batch handlers with support for "Send to all phone numbers even if they are not users."', 'user-manager'); ?></li>
							<li><?php esc_html_e('Updated throttling labels to include texting and enabled throttle/batch behavior for SMS sends using the same throttle settings.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.9 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Email Users > Saved Lists: added a CSV button in each list row to download that entire saved list as a CSV file.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added a secure admin-post export handler for Saved Lists CSV downloads (capability check + nonce validation).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.8 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Navigation: added a new top-level "Login Tools" tab and moved Create, Bulk Create, Reset Pass, Remove, and Login As into a sub-navigation under it.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Login Tools now defaults to the Create screen when opening the plugin (Login Tools -> Create).', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added two Login Tools sub-links at the end: "Recent Logins" and "More Reports", both linking to Reports > User Logins.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.7 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Reports: added a new "Orders Still Processing but have a Tracking Number" report in tab=reports.', 'user-manager'); ?></li>
							<li><?php esc_html_e('New report filters order notes to only processing orders whose notes contain "with tracking number", helping surface potentially stuck orders that already have tracking details.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added CSV export support for the new processing-with-tracking-number report.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.6 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('View Website by Role Permission: changed "Default Roles" to a single-selection "Default Role" dropdown on user profile permissions.', 'user-manager'); ?></li>
							<li><?php esc_html_e('View Website by Role Permission: added a new per-user "Roles to Hide" checkbox list so selected roles are hidden from that user\'s front-end role switcher.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Role Switching enforcement: hidden roles are now blocked in both switcher display and POST handling (including reset-to-default behavior).', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.5 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Documentation: added a new Troubleshooting sub-link with practical isolation steps and URL-parameter guidance.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added temporary URL overrides to disable add-ons per request: ?um_disable_all_addons=1 for all add-ons, or ?um_disable_addons=slug1,slug2 for specific add-ons.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Documentation > Troubleshooting now includes a checkbox URL builder to generate disable-all and comma-separated add-on-disable test URLs.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.4 <span>(March 16, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: added minimum total quantity validation with customizable JavaScript alert messaging and optional success alert before continuing.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added new "Cart Total Items" add-on with Activate toggle, customizable copy, cart/checkout visibility controls, and above/below placement settings for each area.', 'user-manager'); ?></li>
							<li><?php esc_html_e('Added new "Order Received Page Customizer" add-on with Activate toggle and settings to override the Order Received heading and success paragraph text.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.3 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tile list: removed green active-state text, border, and shadow styling in the "Choose an Add-on" area.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.2 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add to Cart Variation Table: changed front-end totals label to "Total" and added a new "Hide Totals Row" setting.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.1 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Hotfix: removed duplicate bulk_add_to_cart_get_product_id_column_header() declaration to prevent fatal redeclare error.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.4.0 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Release 2.4.0: includes recent admin tab ordering and add-ons tag navigation updates.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.54 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Add-ons tag filter: added a new "Orders" tag in the sub-navigation for order-related add-ons.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.53 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Main tab order updated so Settings appears before Reports.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.52 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Documentation sub-menu order updated so About appears before Versions.', 'user-manager'); ?></li>
						</ul>
					</div>
					<div class="um-changelog-item">
						<h4>2.3.51 <span>(March 15, 2026)</span></h4>
						<ul>
							<li><?php esc_html_e('Documentation > Support: updated support request link to https://simplewebhelp.com/inquiries/?ref=uxm.', 'user-manager'); ?></li>
						</ul>
					</div>
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

