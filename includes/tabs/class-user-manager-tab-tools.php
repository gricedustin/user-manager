<?php
/**
 * Tools tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Tools {

	public static function render(
		bool $show_utility_cards = true,
		bool $show_blog_importer_card = true,
		bool $show_blog_idea_generator_card = true,
		bool $wrap_blog_idea_card = true,
		bool $wrap_blog_importer_card = true,
		bool $wrap_grid = true
	): void {
		$blog_categories = [];
		$um_blog_spread_first = current_time('Y-m-d');
		$um_blog_spread_last  = current_time('Y-m-d');
		$um_has_chatgpt_key = false;
		if ($show_blog_importer_card || $show_blog_idea_generator_card) {
			$blog_categories = get_categories(['taxonomy' => 'category', 'hide_empty' => false]);
			$last_published = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1, 'orderby' => 'date', 'order' => 'DESC']);
			$um_blog_spread_first = !empty($last_published) ? get_the_date('Y-m-d', $last_published[0]) : current_time('Y-m-d');
			$um_settings = User_Manager_Core::get_settings();
			$um_has_chatgpt_key = !empty(trim((string) ($um_settings['openai_api_key'] ?? '')));
		}
		?>
		<?php if ($wrap_grid) : ?>
		<div class="um-admin-grid um-admin-grid-single">
		<?php endif; ?>
			<?php if ($show_utility_cards) : ?>
			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-download"></span>
					<h2><?php esc_html_e('Import Demo Email Templates', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Import pre-configured email templates to get started quickly. This will add 4 commonly used templates:', 'user-manager'); ?></p>
					<ul style="list-style: disc; margin-left: 20px; margin-bottom: 16px;">
						<li><strong><?php esc_html_e('Send login information', 'user-manager'); ?></strong> — <?php esc_html_e('Send my account link, username and clear text password', 'user-manager'); ?></li>
						<li><strong><?php esc_html_e('Activate your new account', 'user-manager'); ?></strong> — <?php esc_html_e('Send new users a link to the website with a temporary password and a link to change their password in their account', 'user-manager'); ?></li>
						<li><strong><?php esc_html_e('Send new password', 'user-manager'); ?></strong> — <?php esc_html_e('Sends updated login credentials with clear text password after a password change', 'user-manager'); ?></li>
						<li><strong><?php esc_html_e('Force password reset', 'user-manager'); ?></strong> — <?php esc_html_e('Send a password reset link for users to reset their own password', 'user-manager'); ?></li>
					</ul>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="user_manager_import_demo_templates" />
						<?php wp_nonce_field('user_manager_import_demo_templates'); ?>
						<?php submit_button(__('Import Demo Templates', 'user-manager'), 'primary', 'submit', false); ?>
					</form>
				</div>
			</div>
			
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-email"></span>
					<h2><?php esc_html_e('Import Automated Coupon Email', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Import automated coupon email templates used by the New User Coupons and Bulk Coupons features.', 'user-manager'); ?></p>
					<ul style="list-style: disc; margin-left: 20px; margin-bottom: 16px;">
						<li><strong><?php esc_html_e('Send automated coupon', 'user-manager'); ?></strong> — <?php esc_html_e('Configured in Settings to trigger automated discounts & store credits for new users. Supports %COUPONCODE%.', 'user-manager'); ?></li>
						<li><strong><?php esc_html_e('Send $10 coupon apology', 'user-manager'); ?></strong> — <?php esc_html_e('Use when sending a one-time $10 apology coupon that includes the %COUPONCODE% placeholder.', 'user-manager'); ?></li>
					</ul>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="user_manager_import_coupon_template" />
						<?php wp_nonce_field('user_manager_import_coupon_template'); ?>
						<?php submit_button(__('Import Automated Coupon Email', 'user-manager'), 'secondary', 'submit', false); ?>
					</form>
				</div>
			</div>
			
			<div class="um-admin-card">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-trash"></span>
					<h2><?php esc_html_e('Clear Logs & Reset Views', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Remove all entries from the selected logs or reset view-related reports. These actions cannot be undone.', 'user-manager'); ?></p>
					<div style="display:flex; flex-direction:column; gap:12px; max-width:420px;">
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to clear the entire Admin Activity Log? This cannot be undone.', 'user-manager')); ?>');">
							<input type="hidden" name="action" value="user_manager_clear_activity_log" />
							<?php wp_nonce_field('user_manager_clear_activity_log'); ?>
							<?php submit_button(__('Clear Admin Activity Log', 'user-manager'), 'delete', 'submit', false); ?>
						</form>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to clear the entire User Activity Log? This cannot be undone.', 'user-manager')); ?>');">
							<input type="hidden" name="action" value="user_manager_clear_user_activity_log" />
							<?php wp_nonce_field('user_manager_clear_user_activity_log'); ?>
							<?php submit_button(__('Clear User Activity Log', 'user-manager'), 'delete', 'submit', false); ?>
						</form>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to reset all view-related reports (Page Views, Product Views, 404 Errors, Search Queries)? This cannot be undone.', 'user-manager')); ?>');">
							<input type="hidden" name="action" value="user_manager_reset_view_reports" />
							<?php wp_nonce_field('user_manager_reset_view_reports'); ?>
							<?php submit_button(__('Reset Total Views / Traffic Reports', 'user-manager'), 'delete', 'submit', false); ?>
						</form>
					</div>
				</div>
			</div>

			<?php endif; ?>
			<?php if ($show_blog_importer_card) : ?>
			<?php if ($wrap_blog_importer_card) : ?>
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-edit-page"></span>
					<h2><?php esc_html_e('Blog Post Importer', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
			<?php else : ?>
			<div id="um-blog-importer-card">
			<?php endif; ?>
					<p><?php esc_html_e('Create multiple blog posts at once. Add one or more posts below; optional settings when saving can apply a random featured image and spread post dates evenly between a first and last date.', 'user-manager'); ?></p>
					<?php
					$um_default_editor_tinymce = function () { return 'tinymce'; };
					add_filter('wp_default_editor', $um_default_editor_tinymce, 10, 0);
					?>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-blog-post-importer-form">
						<input type="hidden" name="action" value="user_manager_blog_post_importer" />
						<?php wp_nonce_field('user_manager_blog_post_importer'); ?>

						<?php
						$um_blog_importer_max_rows = 10;
						?>
						<style>
						#um-blog-importer-posts .um-blog-importer-row .wp-editor-wrap { width: 100% !important; }
						#um-blog-importer-posts .um-blog-importer-row .wp-editor-container { width: 100% !important; }
						#um-blog-importer-posts .um-blog-importer-row .wp-editor-area { width: 100% !important; box-sizing: border-box; }
						#um-blog-importer-posts .um-blog-importer-meta-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-start; margin-bottom: 12px; }
						#um-blog-importer-posts .um-blog-importer-meta-row .um-blog-importer-meta-col { flex: 0 1 auto; }
						#um-blog-importer-posts .um-blog-importer-meta-row .um-blog-importer-meta-col input[type="text"] { min-width: 540px; width: 100%; max-width: 960px; box-sizing: border-box; }
						#um-blog-importer-posts .um-blog-importer-meta-row .um-blog-importer-meta-col input[type="date"] { flex-shrink: 0; }
						#um-blog-importer-posts .um-blog-importer-meta-row .um-blog-importer-categories { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; min-width: 0; }
						#um-blog-importer-posts .um-blog-importer-meta-row .um-blog-importer-categories label { display: inline-block; margin: 0; white-space: nowrap; }
						</style>
						<div id="um-blog-importer-posts">
							<?php for ($um_bi = 0; $um_bi < $um_blog_importer_max_rows; $um_bi++) : ?>
							<div class="um-blog-importer-row um-form-field" data-index="<?php echo (int) $um_bi; ?>" style="border: 1px solid #c3c4c7; padding: 12px; margin-bottom: 12px; border-radius: 4px;<?php echo $um_bi > 0 ? ' display:none;' : ''; ?>">
								<h4 style="margin-top: 0;"><?php echo esc_html(sprintf(__('Post %d', 'user-manager'), $um_bi + 1)); ?></h4>
								<div class="um-blog-importer-topic-row" style="margin-bottom: 10px;">
									<label for="um-blog-topic-<?php echo (int) $um_bi; ?>" style="display:block; margin-bottom: 4px; font-weight: 600;"><?php esc_html_e('Topic idea', 'user-manager'); ?></label>
									<input type="text" id="um-blog-topic-<?php echo (int) $um_bi; ?>" class="large-text um-blog-topic-input" data-index="<?php echo (int) $um_bi; ?>" style="width: 100%; max-width: 960px; box-sizing: border-box; margin-bottom: 6px;" placeholder="<?php esc_attr_e('e.g. benefits of morning exercise', 'user-manager'); ?>" />
									<?php if ($um_has_chatgpt_key) : ?>
									<button type="button" class="button button-primary um-blog-chatgpt-row-btn" data-index="<?php echo (int) $um_bi; ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('user_manager_blog_chatgpt')); ?>"><?php esc_html_e('Auto write from ChatGPT', 'user-manager'); ?></button>
									<span class="spinner um-blog-chatgpt-row-spinner" data-index="<?php echo (int) $um_bi; ?>" style="float:none; margin-left:8px; display:none;"></span>
									<div class="um-blog-chatgpt-error-row" id="um-blog-chatgpt-error-<?php echo (int) $um_bi; ?>" style="display:none; margin-top:8px; padding:10px; border:1px solid #d63638; border-radius:4px; background:#fcf0f1; color:#3c434a;"></div>
									<div class="um-blog-chatgpt-titles-row" id="um-blog-chatgpt-titles-<?php echo (int) $um_bi; ?>" style="display:none; margin-top:8px; padding:8px; border:1px solid #c3c4c7; border-radius:4px; background:#f9f9f9;">
										<p style="margin:0 0 6px 0; font-weight:600;"><?php esc_html_e('Choose a title (auto-fills the title field below):', 'user-manager'); ?></p>
										<div class="um-blog-chatgpt-radios" data-index="<?php echo (int) $um_bi; ?>"></div>
									</div>
									<?php endif; ?>
								</div>
								<div class="um-blog-importer-meta-row">
									<div class="um-blog-importer-meta-col">
										<input type="text" name="blog_importer_posts[<?php echo (int) $um_bi; ?>][title]" id="um-blog-title-<?php echo (int) $um_bi; ?>" class="large-text" placeholder="<?php esc_attr_e('Post Title', 'user-manager'); ?>" />
									</div>
									<div class="um-blog-importer-meta-col">
										<input type="date" name="blog_importer_posts[<?php echo (int) $um_bi; ?>][date]" id="um-blog-date-<?php echo (int) $um_bi; ?>" />
									</div>
									<div class="um-blog-importer-meta-col">
										<input type="text" name="blog_importer_posts[<?php echo (int) $um_bi; ?>][tags]" id="um-blog-tags-<?php echo (int) $um_bi; ?>" class="regular-text" placeholder="<?php esc_attr_e('Tags (comma-separated)', 'user-manager'); ?>" style="min-width:360px;" />
									</div>
									<div class="um-blog-importer-meta-col">
										<?php if (!empty($blog_categories)) : ?>
											<div class="um-blog-importer-categories">
												<?php foreach ($blog_categories as $cat) : ?>
													<label><input type="checkbox" name="blog_importer_posts[<?php echo (int) $um_bi; ?>][categories][]" value="<?php echo (int) $cat->term_id; ?>" data-cat-name="<?php echo esc_attr($cat->name); ?>" data-cat-slug="<?php echo esc_attr($cat->slug); ?>" /> <?php echo esc_html($cat->name); ?></label>
												<?php endforeach; ?>
											</div>
										<?php else : ?>
											<em><?php esc_html_e('No categories found.', 'user-manager'); ?></em>
										<?php endif; ?>
									</div>
								</div>
								<input type="hidden" id="um_blog_raw_body_<?php echo (int) $um_bi; ?>" name="blog_importer_posts[<?php echo (int) $um_bi; ?>][raw_body]" value="" />
								<div class="um-blog-importer-editor-wrap" style="width: 100%; box-sizing: border-box;">
									<?php
									wp_editor('', 'um_blog_desc_' . $um_bi, [
										'textarea_name' => 'blog_importer_posts[' . $um_bi . '][description]',
										'textarea_rows' => 8,
										'media_buttons' => true,
										'teeny'         => false,
										'quicktags'     => true,
										'tinymce'       => ['toolbar1' => 'formatselect,bold,italic,underline,blockquote,link,unlink,bullist,numlist,outdent,indent,undo,redo'],
										'drag_drop_upload' => true,
									]);
									?>
								</div>
							</div>
							<?php endfor; ?>
						</div>
						<p>
							<button type="button" class="button" id="um-blog-importer-add-row"><?php esc_html_e('Add another post', 'user-manager'); ?></button>
							<span class="description" id="um-blog-importer-row-count" style="margin-left:8px;"><?php esc_html_e('Up to 10 posts. Add another reveals the next row.', 'user-manager'); ?></span>
						</p>

						<hr style="margin: 20px 0;" />
						<h4><?php esc_html_e('Settings when saving', 'user-manager'); ?></h4>
						<p>
							<label><input type="checkbox" name="blog_importer_apply_random_image" value="1" checked="checked" /> <?php esc_html_e('Apply random image from Media Library as featured image (only images not already used as a post featured image)', 'user-manager'); ?></label>
						</p>
						<p>
							<label><input type="checkbox" name="blog_importer_single_plus_25" value="1" checked="checked" /> <?php esc_html_e('When creating posts, if no date is set, start with the most recent published or scheduled post date, and then set every new post', 'user-manager'); ?></label>
							<input type="number" name="blog_importer_single_plus_days" id="um-blog-single-plus-days" value="<?php echo esc_attr((string) max(1, min(365, (int) get_option('um_blog_importer_plus_days', 25)))); ?>" min="1" max="365" style="width:60px;" />
							<label for="um-blog-single-plus-days"><?php esc_html_e('days forward from that date (e.g. 3 posts with 25 days = first +25, second +50, third +75).', 'user-manager'); ?></label>
						</p>
						<p>
							<label><input type="checkbox" name="blog_importer_apply_spread" value="1" /> <?php esc_html_e('Spread post dates: from', 'user-manager'); ?></label>
							<input type="date" name="blog_importer_spread_first" id="um-blog-spread-first" value="<?php echo esc_attr($um_blog_spread_first); ?>" /> <?php esc_html_e('to', 'user-manager'); ?> <input type="date" name="blog_importer_spread_last" id="um-blog-spread-last" value="<?php echo esc_attr($um_blog_spread_last); ?>" /><br />
							<span class="description"><?php esc_html_e('When checked and both dates are set, post dates are ignored and dates are spread evenly from first (post 1) to last (last post).', 'user-manager'); ?></span>
						</p>

						<p style="margin-top: 20px;">
							<?php submit_button(__('Create Posts', 'user-manager'), 'primary', 'submit', false); ?>
						</p>
						<?php
						$um_ai_prompt_categories = !empty($blog_categories) ? implode(', ', wp_list_pluck($blog_categories, 'name')) : '';
						$um_ai_prompt_text = sprintf(
							__("Topic idea:\n\nPlease write a blog post about 5 paragraphs long (4-8 sentences each), with 3-5 SEO-friendly titles.\n\nHere are our post categories: %s\n\nPlease focus on 1–2 of these categories and include the exact category name(s) spelled identically somewhere in the post body. Do not mention any of the other categories in the post.", 'user-manager'),
							$um_ai_prompt_categories !== '' ? $um_ai_prompt_categories : __('(none)', 'user-manager')
						);
						?>
						<p style="margin-top: 20px;">
							<label for="um-blog-ai-prompt" style="display:block; font-weight: 600; margin-bottom: 6px;"><?php esc_html_e('AI Prompt Support', 'user-manager'); ?></label>
							<textarea id="um-blog-ai-prompt" class="large-text code" rows="6" readonly style="width: 100%; box-sizing: border-box; cursor: text;" onclick="this.select();"><?php echo esc_textarea($um_ai_prompt_text); ?></textarea>
							<span class="description"><?php esc_html_e('Copy this prompt to use with an AI assistant; the category list is filled from your site. The topic idea and "Auto write from ChatGPT" button above each post use this prompt.', 'user-manager'); ?></span>
						</p>
					</form>
					<?php
					remove_filter('wp_default_editor', $um_default_editor_tinymce, 10);
					// Recent posts list (newest to oldest) — always on Tools tab so date field and spread button are visible
					$um_recent_posts = get_posts([
						'post_type'      => 'post',
						'post_status'    => ['publish', 'future'],
						'orderby'        => 'date',
						'order'          => 'DESC',
						'posts_per_page' => 20,
						'no_found_rows'  => true,
					]);
					$um_scheduled_posts = get_posts([
						'post_type'      => 'post',
						'post_status'    => 'future',
						'posts_per_page' => -1,
						'fields'         => 'ids',
					]);
					$um_scheduled_count = is_array($um_scheduled_posts) ? count($um_scheduled_posts) : 0;
					$um_plus_days = max(1, min(365, (int) get_option('um_blog_importer_plus_days', 25)));
					$um_recommended_ts = current_time('timestamp') + ($um_scheduled_count * $um_plus_days * DAY_IN_SECONDS);
					$um_recommended_date = wp_date('Y-m-d', $um_recommended_ts);
					$um_spread_nonce = wp_create_nonce('user_manager_spread_scheduled_posts');
					?>
					<div class="um-blog-importer-recent-posts" style="margin-top:24px; padding-top:16px; border-top:1px solid #c3c4c7;" data-spread-nonce="<?php echo esc_attr($um_spread_nonce); ?>" data-recommended-date="<?php echo esc_attr($um_recommended_date); ?>">
						<h4 style="margin:0 0 8px 0;"><?php esc_html_e('Recent posts (newest to oldest)', 'user-manager'); ?></h4>
						<p style="margin:0 0 8px 0;">
							<?php
							echo esc_html(sprintf(
								/* translators: %d: number of scheduled posts */
								_n('%d scheduled post', '%d scheduled posts', $um_scheduled_count, 'user-manager'),
								$um_scheduled_count
							));
							if ($um_scheduled_count > 0) {
								echo ' · ';
								echo esc_html(sprintf(
									/* translators: 1: recommended date, 2: number of scheduled posts, 3: days value */
									__('Recommended date: %1$s (today + %2$d × %3$d days)', 'user-manager'),
									wp_date(get_option('date_format'), $um_recommended_ts),
									$um_scheduled_count,
									$um_plus_days
								));
							}
							?>
						</p>
						<p style="margin:0 0 8px 0;">
							<label for="um-blog-spread-date" style="margin-right:8px; font-weight:600;"><?php esc_html_e('Spread to date:', 'user-manager'); ?></label>
							<input type="date" id="um-blog-spread-date" class="um-blog-importer-spread-date" style="margin-right:12px; min-width:160px;" value="<?php echo $um_scheduled_count > 0 ? esc_attr($um_recommended_date) : ''; ?>" />
							<button type="button" class="button um-blog-importer-spread-scheduled-btn"><?php esc_html_e('Evenly spread all scheduled posts out to this date', 'user-manager'); ?></button>
						</p>
						<?php
					if (!empty($um_recent_posts)) {
						?>
						<table class="widefat striped" style="margin-top:8px;"><thead><tr>
								<th><?php esc_html_e('Title', 'user-manager'); ?></th>
								<th><?php esc_html_e('Status', 'user-manager'); ?></th>
								<th><?php esc_html_e('Date', 'user-manager'); ?></th>
								<th><?php esc_html_e('Days since previous post', 'user-manager'); ?></th>
								<th><?php esc_html_e('New post suggested date', 'user-manager'); ?></th>
							</tr></thead><tbody>
								<?php
								$prev_ts = null;
								foreach ($um_recent_posts as $rp) {
									$rp_id = (int) $rp->ID;
									$rp_title = get_the_title($rp_id);
									$rp_date = get_the_date('', $rp_id);
									$rp_edit = get_edit_post_link($rp_id, 'raw');
									$ts = get_post_time('U', true, $rp_id);
									$days_cell = '—';
									$days_cell_style = '';
									$suggested_date_cell = '—';
									$suggested_date_style = '';
									if ($prev_ts !== null && $ts !== false) {
										$days = (int) round(($prev_ts - $ts) / DAY_IN_SECONDS);
										$days_cell = (string) $days;
										if ($days > 60) {
											$days_cell_style = ' background-color:#fcf0f1; color:#b32d2e;';
											$suggested_date_style = $days_cell_style;
										} elseif ($days > 30) {
											$days_cell_style = ' background-color:#fcf9e8; color:#94660c;';
											$suggested_date_style = $days_cell_style;
										}
										$midpoint_ts = $ts + (int) round(($days * DAY_IN_SECONDS) / 2);
										$suggested_date_cell = wp_date(get_option('date_format'), $midpoint_ts);
									}
									if ($ts !== false) {
										$prev_ts = $ts;
									}
									$rp_status = get_post_status($rp_id);
									$status_label = ($rp_status === 'future') ? __('Scheduled', 'user-manager') : __('Published', 'user-manager');
									?>
									<tr>
										<td><?php echo $rp_edit ? '<a href="' . esc_url($rp_edit) . '">' . esc_html($rp_title) . '</a>' : esc_html($rp_title); ?></td>
										<td><?php echo esc_html($status_label); ?></td>
										<td><?php echo esc_html($rp_date); ?></td>
										<td style="<?php echo esc_attr($days_cell_style); ?>"><?php echo esc_html($days_cell); ?></td>
										<td style="<?php echo esc_attr($suggested_date_style); ?>"><?php echo esc_html($suggested_date_cell); ?></td>
									</tr>
								<?php } ?>
							</tbody></table>
						<?php
					} else {
						echo '<p class="description" style="margin:8px 0 0 0;">' . esc_html__('No published or scheduled posts yet.', 'user-manager') . '</p>';
					}
					?>
					</div>
					<script>
					(function() {
						var container = document.getElementById('um-blog-importer-posts');
						var addBtn = document.getElementById('um-blog-importer-add-row');
						if (!container || !addBtn) return;
						var rows = container.querySelectorAll('.um-blog-importer-row');
						var maxRows = rows.length;
						addBtn.addEventListener('click', function() {
							for (var i = 0; i < maxRows; i++) {
								if (rows[i].style.display === 'none') {
									rows[i].style.display = '';
									if (i === maxRows - 1) addBtn.style.display = 'none';
									break;
								}
							}
						});
						function setVisualTab() {
							container.querySelectorAll('.wp-editor-wrap').forEach(function(wrap) {
								if (wrap.classList.contains('html-active') && !wrap.classList.contains('tmce-active')) {
									var visualBtn = wrap.querySelector('.wp-switch-editor:not(.html)');
									if (visualBtn) visualBtn.click();
								}
							});
						}
						if (document.readyState === 'loading') {
							document.addEventListener('DOMContentLoaded', function() { setTimeout(setVisualTab, 100); });
						} else {
							setTimeout(setVisualTab, 100);
						}
						function getTitleTextForRow(index) {
							var titleEl = document.getElementById('um-blog-title-' + index);
							return (titleEl && titleEl.value ? titleEl.value : '').replace(/\s+/g, ' ').toLowerCase();
						}
						function getBodyTextForRow(index) {
							var ed = typeof tinymce !== 'undefined' && tinymce.get('um_blog_desc_' + index);
							if (ed) {
								return (ed.getContent({ format: 'text' }) || '').replace(/\s+/g, ' ').toLowerCase();
							}
							var ta = document.getElementById('um_blog_desc_' + index);
							return (ta ? (ta.value || '') : '').replace(/\s+/g, ' ').toLowerCase();
						}
						function getCombinedTextForRow(index) {
							return (getTitleTextForRow(index) + ' ' + getBodyTextForRow(index)).trim();
						}
						function syncCategoriesFromBody(index) {
							var row = container.querySelector('.um-blog-importer-row[data-index="' + index + '"]');
							if (!row) return;
							var text = getCombinedTextForRow(index);
							var checkboxes = row.querySelectorAll('.um-blog-importer-categories input[type="checkbox"]');
							checkboxes.forEach(function(cb) {
								var name = (cb.getAttribute('data-cat-name') || '').toLowerCase();
								var slug = (cb.getAttribute('data-cat-slug') || '').toLowerCase();
								if ((name && name.length > 0 && text.indexOf(name) !== -1) || (slug && slug.length > 0 && text.indexOf(slug) !== -1)) {
									cb.checked = true;
								}
							});
						}
						function bindCategorySync() {
							for (var i = 0; i < maxRows; i++) {
								(function(idx) {
									var titleEl = document.getElementById('um-blog-title-' + idx);
									if (titleEl) {
										titleEl.addEventListener('input', function() { syncCategoriesFromBody(idx); });
										titleEl.addEventListener('change', function() { syncCategoriesFromBody(idx); });
									}
									var ta = document.getElementById('um_blog_desc_' + idx);
									if (ta) {
										ta.addEventListener('input', function() { syncCategoriesFromBody(idx); });
										ta.addEventListener('change', function() { syncCategoriesFromBody(idx); });
									}
									if (typeof tinymce !== 'undefined') {
										var attempt = 0;
										function tryBind() {
											var ed = tinymce.get('um_blog_desc_' + idx);
											if (ed) {
												ed.on('change keyup', function() { syncCategoriesFromBody(idx); });
											} else if (attempt < 80) {
												attempt++;
												setTimeout(tryBind, 100);
											}
										}
										setTimeout(tryBind, 300 + (idx * 50));
									}
								})(i);
							}
							setTimeout(function() {
								for (var j = 0; j < maxRows; j++) {
									var r = container.querySelector('.um-blog-importer-row[data-index="' + j + '"]');
									if (r && r.style.display !== 'none') syncCategoriesFromBody(j);
								}
							}, 1500);
						}
						setTimeout(bindCategorySync, 600);
					})();
					</script>
					<?php if ($um_has_chatgpt_key) : ?>
					<script>
					(function() {
						var ajaxurl = typeof ajaxurl !== 'undefined' ? ajaxurl : '<?php echo esc_url(admin_url("admin-ajax.php")); ?>';
						var promptEl = document.getElementById('um-blog-ai-prompt');
						function showChatGptError(idx, message, debug) {
							var errEl = document.getElementById('um-blog-chatgpt-error-' + idx);
							if (!errEl) return;
							var html = '<strong>' + (message || '<?php echo esc_js(__('Request failed.', 'user-manager')); ?>') + '</strong>';
							if (debug && typeof debug === 'object') {
								html += '<pre style="margin:8px 0 0 0; padding:8px; background:#fff; border:1px solid #c3c4c7; font-size:12px; overflow:auto; max-height:200px;">' + (JSON.stringify(debug, null, 2).replace(/</g, '&lt;').replace(/>/g, '&gt;')) + '</pre>';
							} else if (debug) {
								html += '<pre style="margin:8px 0 0 0; padding:8px; background:#fff; font-size:12px;">' + (String(debug).replace(/</g, '&lt;').replace(/>/g, '&gt;')) + '</pre>';
							}
							errEl.innerHTML = html;
							errEl.style.display = 'block';
						}
						function hideChatGptError(idx) {
							var errEl = document.getElementById('um-blog-chatgpt-error-' + idx);
							if (errEl) { errEl.style.display = 'none'; errEl.innerHTML = ''; }
						}
						// Topic idea: Enter key triggers "Auto write from ChatGPT" instead of submitting the form
						document.querySelectorAll('.um-blog-topic-input').forEach(function(topicInput) {
							topicInput.addEventListener('keydown', function(e) {
								if (e.key !== 'Enter') return;
								e.preventDefault();
								var idx = topicInput.getAttribute('data-index');
								if (idx === null) return;
								var chatBtn = document.querySelector('.um-blog-chatgpt-row-btn[data-index="' + idx + '"]');
								if (chatBtn) chatBtn.click();
							});
						});
						document.querySelectorAll('.um-blog-chatgpt-row-btn').forEach(function(btn) {
							var idx = parseInt(btn.getAttribute('data-index'), 10);
							var spinner = document.querySelector('.um-blog-chatgpt-row-spinner[data-index="' + idx + '"]');
							var titlesRow = document.getElementById('um-blog-chatgpt-titles-' + idx);
							var radiosContainer = titlesRow ? titlesRow.querySelector('.um-blog-chatgpt-radios') : null;
							btn.addEventListener('click', function() {
								var topicEl = document.getElementById('um-blog-topic-' + idx);
								var topic = (topicEl && topicEl.value) ? topicEl.value.trim() : '';
								var promptBase = (promptEl && promptEl.value) ? promptEl.value : '';
								var nonce = btn.getAttribute('data-nonce') || '';
								if (spinner) { spinner.style.display = 'inline-block'; spinner.classList.add('is-active'); }
								if (titlesRow) titlesRow.style.display = 'none';
								hideChatGptError(idx);
								var formData = new FormData();
								formData.append('action', 'user_manager_blog_chatgpt');
								formData.append('nonce', nonce);
								formData.append('topic_idea', topic);
								formData.append('prompt_base', promptBase);
								fetch(ajaxurl, { method: 'POST', body: formData, credentials: 'same-origin' })
									.then(function(r) {
										var contentType = r.headers.get('content-type');
										if (r.status !== 200 && !contentType.includes('json')) {
											return r.text().then(function(t) { throw { status: r.status, body: t }; });
										}
										return r.json();
									})
									.then(function(data) {
										if (spinner) { spinner.style.display = 'none'; spinner.classList.remove('is-active'); }
										if (!data.success || !data.data || !Array.isArray(data.data.titles) || typeof data.data.body !== 'string') {
											showChatGptError(idx, data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Request failed.', 'user-manager')); ?>', data.data && data.data.debug ? data.data.debug : null);
											return;
										}
										hideChatGptError(idx);
										var titles = data.data.titles;
										var body = data.data.body;
										var ed = typeof tinymce !== 'undefined' ? tinymce.get('um_blog_desc_' + idx) : null;
										var ta = document.getElementById('um_blog_desc_' + idx);
										if (ed) {
											ed.setContent(body);
											if (typeof ed.save === 'function') ed.save();
										}
										if (ta) ta.value = body;
										var rawInput = document.getElementById('um_blog_raw_body_' + idx);
										if (rawInput) rawInput.value = body;
										if (data.data.tags && typeof data.data.tags === 'string') {
											var tagsInput = document.getElementById('um-blog-tags-' + idx);
											if (tagsInput) tagsInput.value = data.data.tags;
										}
										setTimeout(function() { syncCategoriesFromBody(idx); }, 150);
										if (radiosContainer && titlesRow) {
											radiosContainer.innerHTML = '';
											titles.forEach(function(t, i) {
												var label = document.createElement('label');
												label.style.display = 'block';
												label.style.marginBottom = '4px';
												var radio = document.createElement('input');
												radio.type = 'radio';
												radio.name = 'um_blog_chatgpt_title_' + idx;
												radio.value = t;
												if (i === 0) { radio.checked = true; var titleInput = document.getElementById('um-blog-title-' + idx); if (titleInput) titleInput.value = t; }
												radio.addEventListener('change', function() { var ti = document.getElementById('um-blog-title-' + idx); if (ti) ti.value = this.value; });
												label.appendChild(radio);
												label.appendChild(document.createTextNode(' ' + t));
												radiosContainer.appendChild(label);
											});
											titlesRow.style.display = 'block';
										}
									})
									.catch(function(err) {
										if (spinner) { spinner.style.display = 'none'; spinner.classList.remove('is-active'); }
										var msg = '<?php echo esc_js(__('Request failed.', 'user-manager')); ?>';
										var debug = null;
										if (err && err.status) {
											debug = { status: err.status, body_preview: err.body ? String(err.body).substring(0, 500) : '' };
											if (err.status === 403 && err.body && String(err.body).trim() === '-1') {
												msg = 'Verification failed (403). WordPress rejected the request—often in dev when the page URL and request origin differ. Open the admin using the same URL (same domain and port, e.g. always https://yoursite.local/wp-admin) and try again.';
											} else {
												msg = 'HTTP ' + err.status;
											}
										} else if (err && err.message) {
											msg = err.message;
											debug = { name: err.name, stack: err.stack ? String(err.stack).split('\n').slice(0, 5) : [] };
										}
										showChatGptError(idx, msg, debug);
									});
							});
						});
						document.querySelectorAll('.um-blog-chatgpt-radios').forEach(function(radiosDiv) {
							var idx = parseInt(radiosDiv.getAttribute('data-index'), 10);
							radiosDiv.addEventListener('change', function(e) {
								if (e.target.type === 'radio' && e.target.name === 'um_blog_chatgpt_title_' + idx) {
									var titleInput = document.getElementById('um-blog-title-' + idx);
									if (titleInput) titleInput.value = e.target.value;
								}
							});
						});
					})();
					</script>
					<?php endif; ?>
			<?php if ($wrap_blog_importer_card) : ?>
				</div>
			</div>
			<?php else : ?>
			</div>
			<?php endif; ?>

			<?php endif; ?>

			<?php if ($show_blog_idea_generator_card) : ?>
			<?php
			$um_idea_posts = get_posts(['post_type' => 'post', 'post_status' => ['publish', 'future'], 'orderby' => 'date', 'order' => 'DESC', 'posts_per_page' => 500, 'no_found_rows' => true]);
			$um_idea_titles = array_map('get_the_title', $um_idea_posts);
			$um_idea_prompt = __('Here are all of our blog post titles, what are some other topics and/or headlines you might recommend?', 'user-manager') . "\n\n" . implode("\n", $um_idea_titles);
			$um_idea_nonce  = wp_create_nonce('user_manager_blog_ideas');
			?>
			<?php if ($wrap_blog_idea_card) : ?>
			<div class="um-admin-card um-admin-card-full" id="um-blog-idea-generator-card" data-ideas-nonce="<?php echo esc_attr($um_idea_nonce); ?>">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-lightbulb"></span>
					<h2><?php esc_html_e('Post Idea Generator', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
			<?php else : ?>
			<div id="um-blog-idea-generator-card" data-ideas-nonce="<?php echo esc_attr($um_idea_nonce); ?>">
			<?php endif; ?>
					<?php if ($um_has_chatgpt_key) : ?>
						<p style="margin-top: 0;">
							<label for="um-blog-idea-topic-focus" style="display:block; font-weight: 600; margin-bottom: 6px;"><?php esc_html_e('Optional topic focus', 'user-manager'); ?></label>
							<input type="text" id="um-blog-idea-topic-focus" class="large-text" style="width: 100%; max-width: 600px; box-sizing: border-box; margin-bottom: 12px;" placeholder="<?php esc_attr_e('e.g. wellness, productivity', 'user-manager'); ?>" />
						</p>
						<p style="margin-top: 0;">
							<button type="button" class="button button-primary" id="um-blog-idea-chatgpt-btn"><?php esc_html_e('Ask for ideas via ChatGPT', 'user-manager'); ?></button>
							<span class="spinner" id="um-blog-idea-spinner" style="float:none; margin-left:8px; display:none;"></span>
						</p>
						<div id="um-blog-idea-results" style="display:none; margin-top:12px; padding:12px; border:1px solid #c3c4c7; border-radius:4px; background:#f9f9f9; white-space:pre-wrap; font-family:inherit; font-size:13px; line-height:1.5;"></div>
					<?php endif; ?>
					<label for="um-blog-idea-prompt" style="display:block; font-weight: 600; margin-bottom: 6px; margin-top: 16px;"><?php esc_html_e('AI Prompt Support', 'user-manager'); ?></label>
					<textarea id="um-blog-idea-prompt" class="large-text code" rows="12" readonly style="width: 100%; box-sizing: border-box; cursor: text;" onclick="this.select();"><?php echo esc_textarea($um_idea_prompt); ?></textarea>
					<p class="description"><?php esc_html_e('Copy this prompt to use with an AI assistant. It includes all your blog post titles (newest to oldest) so the AI can suggest related topics or headlines.', 'user-manager'); ?></p>
			<?php if ($wrap_blog_idea_card) : ?>
				</div>
			</div>
			<?php else : ?>
			</div>
			<?php endif; ?>
			<?php if ($um_has_chatgpt_key) : ?>
			<script>
			(function(){
				var btn = document.getElementById('um-blog-idea-chatgpt-btn');
				var spinner = document.getElementById('um-blog-idea-spinner');
				var results = document.getElementById('um-blog-idea-results');
				var card = document.getElementById('um-blog-idea-generator-card');
				var importerContainer = document.getElementById('um-blog-importer-posts');
				if (!btn || !results || !card) return;
				var nonce = card.getAttribute('data-ideas-nonce');
				var ajaxurl = typeof ajaxurl !== 'undefined' ? ajaxurl : '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
				var nextRowIndex = 0;
				var maxRows = 10;
				function stripListPrefix(text) {
					return text.replace(/^[\s\d\.\)\-\*\•]+\s*/, '').trim();
				}
				function useIdeaForNextPost(ideaText) {
					if (nextRowIndex >= maxRows) return;
					var row = importerContainer && importerContainer.querySelector('.um-blog-importer-row[data-index="' + nextRowIndex + '"]');
					var topicInput = document.getElementById('um-blog-topic-' + nextRowIndex);
					var chatBtn = document.querySelector('.um-blog-chatgpt-row-btn[data-index="' + nextRowIndex + '"]');
					for (var i = 0; i <= nextRowIndex; i++) {
						var r = importerContainer && importerContainer.querySelector('.um-blog-importer-row[data-index="' + i + '"]');
						if (r) r.style.display = '';
					}
					if (topicInput) topicInput.value = ideaText;
					if (chatBtn) chatBtn.click();
					nextRowIndex++;
				}
				btn.addEventListener('click', function() {
					results.style.display = 'none';
					results.innerHTML = '';
					nextRowIndex = 0;
					spinner.style.display = 'inline-block';
					spinner.classList.add('is-active');
					var form = new FormData();
					form.append('action', 'user_manager_blog_ideas');
					form.append('nonce', nonce);
					var topicFocusEl = document.getElementById('um-blog-idea-topic-focus');
					if (topicFocusEl && topicFocusEl.value.trim()) form.append('topic_focus', topicFocusEl.value.trim());
					fetch(ajaxurl, { method: 'POST', body: form, credentials: 'same-origin' })
						.then(function(r) { return r.json(); })
						.then(function(data) {
							spinner.style.display = 'none';
							spinner.classList.remove('is-active');
							if (data.success && data.data && data.data.content) {
								results.style.color = '';
								var rawLines = data.data.content.split(/\r?\n/);
								var ideas = [];
								for (var i = 0; i < rawLines.length; i++) {
									var line = rawLines[i].trim();
									if (line.length < 2) continue;
									ideas.push(line);
								}
								if (ideas.length === 0) {
									results.textContent = data.data.content;
								} else {
									var insertLabel = '<?php echo esc_js(__('Insert into Blog Post Importer Topic Idea Above and Auto write from ChatGPT', 'user-manager')); ?>';
									ideas.forEach(function(idea) {
										var row = document.createElement('div');
										row.style.cssText = 'margin-bottom:10px; padding:8px; border:1px solid #c3c4c7; border-radius:4px; background:#fff;';
										var text = document.createElement('span');
										text.textContent = idea;
										text.style.display = 'block';
										text.style.marginBottom = '6px';
										var useBtn = document.createElement('button');
										useBtn.type = 'button';
										useBtn.className = 'button button-small';
										useBtn.textContent = insertLabel;
										useBtn.addEventListener('click', function() { useIdeaForNextPost(stripListPrefix(idea)); });
										row.appendChild(text);
										row.appendChild(useBtn);
										results.appendChild(row);
									});
								}
								results.style.display = 'block';
							} else {
								results.textContent = data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Request failed.', 'user-manager')); ?>';
								results.style.display = 'block';
								results.style.color = '#b32d2e';
							}
						})
						.catch(function() {
							spinner.style.display = 'none';
							spinner.classList.remove('is-active');
							results.textContent = '<?php echo esc_js(__('Request failed.', 'user-manager')); ?>';
							results.style.display = 'block';
							results.style.color = '#b32d2e';
						});
				});
			})();
			</script>
			<?php endif; ?>
			<?php endif; ?>
		<?php if ($wrap_grid) : ?>
		</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render Coupon Lookup by Email in any tab context.
	 *
	 * @param string              $tab                Tab slug for form submissions.
	 * @param array<string,mixed> $hidden_query_args  Extra hidden GET fields to preserve.
	 * @param string              $activity_tool      Tool label used in activity log.
	 */
	public static function render_coupon_lookup_by_email_card(
		string $tab = User_Manager_Core::TAB_REPORTS,
		array $hidden_query_args = [],
		string $activity_tool = 'Reports'
	): void {
		$lookup_email_raw = isset($_GET['coupon_lookup_email']) ? wp_unslash($_GET['coupon_lookup_email']) : '';
		$lookup_email     = $lookup_email_raw !== '' ? sanitize_email($lookup_email_raw) : '';
		$lookup_ran       = ($lookup_email !== '' && class_exists('WC_Coupon'));
		$lookup_results   = $lookup_ran ? self::get_coupon_lookup_results($lookup_email) : [];

		if ($lookup_ran && is_email($lookup_email)) {
			User_Manager_Core::add_activity_log('coupon_lookup', 0, $activity_tool, [
				'email'        => $lookup_email,
				'result_count' => count($lookup_results),
			]);
		}
		?>
		<div class="um-admin-grid">
			<div class="um-admin-card um-admin-card-full">
				<div class="um-admin-card-header">
					<span class="dashicons dashicons-search"></span>
					<h2><?php esc_html_e('Coupon Lookup by Email', 'user-manager'); ?></h2>
				</div>
				<div class="um-admin-card-body">
					<p><?php esc_html_e('Search for coupons where a specific email address appears in the Allowed Emails / email restrictions.', 'user-manager'); ?></p>
					<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="margin-bottom:15px; max-width:480px;">
						<input type="hidden" name="page" value="<?php echo esc_attr(User_Manager_Core::SETTINGS_PAGE_SLUG); ?>" />
						<input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>" />
						<?php foreach ($hidden_query_args as $field_name => $field_value) : ?>
							<?php if (is_scalar($field_value) && (string) $field_value !== '') : ?>
								<input type="hidden" name="<?php echo esc_attr((string) $field_name); ?>" value="<?php echo esc_attr((string) $field_value); ?>" />
							<?php endif; ?>
						<?php endforeach; ?>
						<label for="um-coupon-lookup-email" style="display:block;margin-bottom:6px;">
							<?php esc_html_e('Email Address', 'user-manager'); ?>
						</label>
						<input type="email" name="coupon_lookup_email" id="um-coupon-lookup-email" class="regular-text" style="max-width:320px;" value="<?php echo esc_attr($lookup_email_raw); ?>" />
						<?php submit_button(__('Search Coupons', 'user-manager'), 'secondary', 'submit', false, ['style' => 'margin-left:8px;']); ?>
						<p class="description" style="margin-top:8px;">
							<?php esc_html_e('Matches coupons where this exact email is present in WooCommerce email restrictions, the customer_email meta, or the User Manager coupon email meta.', 'user-manager'); ?>
						</p>
					</form>

					<?php if ($lookup_ran) : ?>
						<?php if ($lookup_email === '' || !is_email($lookup_email)) : ?>
							<p style="color:#d63638;"><?php esc_html_e('Please enter a valid email address.', 'user-manager'); ?></p>
						<?php elseif (empty($lookup_results)) : ?>
							<p><?php esc_html_e('No coupons found for this email address.', 'user-manager'); ?></p>
						<?php else : ?>
							<table class="widefat striped">
								<thead>
									<tr>
										<th><?php esc_html_e('Coupon Code', 'user-manager'); ?></th>
										<th><?php esc_html_e('Coupon ID', 'user-manager'); ?></th>
										<th><?php esc_html_e('Type', 'user-manager'); ?></th>
										<th><?php esc_html_e('Value', 'user-manager'); ?></th>
										<th><?php esc_html_e('Usage', 'user-manager'); ?></th>
										<th><?php esc_html_e('Created', 'user-manager'); ?></th>
										<th><?php esc_html_e('Expires', 'user-manager'); ?></th>
										<th><?php esc_html_e('Status', 'user-manager'); ?></th>
										<th><?php esc_html_e('Allowed Emails', 'user-manager'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($lookup_results as $row) : ?>
										<tr>
											<td>
												<?php if (!empty($row['edit'])) : ?>
													<a href="<?php echo esc_url($row['edit']); ?>"><?php echo esc_html(strtoupper((string) $row['code'])); ?></a>
												<?php else : ?>
													<?php echo esc_html(strtoupper((string) $row['code'])); ?>
												<?php endif; ?>
											</td>
											<td><?php echo (int) $row['id']; ?></td>
											<td><?php echo esc_html((string) $row['type_label']); ?></td>
											<td><?php echo is_string($row['amount']) ? wp_kses_post($row['amount']) : esc_html((string) $row['amount']); ?></td>
											<td><?php echo esc_html((string) $row['usage']); ?></td>
											<td><?php echo esc_html((string) $row['created']); ?></td>
											<td><?php echo esc_html((string) $row['expiry']); ?></td>
											<td><?php echo esc_html((string) $row['status']); ?></td>
											<td><?php echo esc_html(implode(', ', (array) ($row['emails'] ?? []))); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>

							<?php
							$summary_lines = [];
							foreach ($lookup_results as $row) {
								$post_status = get_post_status((int) ($row['id'] ?? 0));
								if ($post_status !== 'publish') {
									continue;
								}

								$code       = strtoupper((string) ($row['code'] ?? ''));
								$amount_raw = (float) ($row['amount_raw'] ?? 0);
								$status     = (string) ($row['status'] ?? '');
								$expiry_ts  = $row['expiry_ts'] ?? null;
								$created_ts = $row['created_ts'] ?? null;

								if (function_exists('wc_price')) {
									$amount_text = html_entity_decode(wp_strip_all_tags(wc_price($amount_raw)));
								} else {
									$amount_text = '$' . number_format($amount_raw, 2, '.', ',');
								}

								if (stripos($status, 'Fully Used') !== false) {
									if ($created_ts) {
										$created_text = date_i18n(get_option('date_format'), (int) $created_ts);
										$line         = sprintf(
											/* translators: 1: coupon code, 2: created date, 3: amount text */
											__('Coupon code %1$s (created on %2$s) had a value of %3$s, and was fully used.', 'user-manager'),
											$code,
											$created_text,
											$amount_text
										);
									} else {
										$line = sprintf(
											/* translators: 1: coupon code, 2: amount text */
											__('Coupon code %1$s had a value of %2$s, and was fully used.', 'user-manager'),
											$code,
											$amount_text
										);
									}
								} else {
									if ($expiry_ts) {
										$expiry_text = date_i18n(get_option('date_format'), (int) $expiry_ts);
										$line        = sprintf(
											/* translators: 1: coupon code, 2: amount text, 3: expiry date */
											__('Coupon code %1$s has a value of %2$s, can still be used, and expires on %3$s.', 'user-manager'),
											$code,
											$amount_text,
											$expiry_text
										);
									} else {
										$line = sprintf(
											/* translators: 1: coupon code, 2: amount text */
											__('Coupon code %1$s has a value of %2$s, can still be used, and does not expire.', 'user-manager'),
											$code,
											$amount_text
										);
									}
								}

								$summary_lines[] = $line;
							}

							if (!empty($summary_lines)) :
								?>
								<div style="margin-top:20px;">
									<label for="um-coupon-lookup-summary" style="display:block;margin-bottom:4px;font-weight:600;">
										<?php esc_html_e('Copyable coupon summary', 'user-manager'); ?>
									</label>
									<textarea id="um-coupon-lookup-summary" class="large-text code" rows="4" readonly><?php echo esc_textarea(implode("\n", $summary_lines)); ?></textarea>
									<p class="description" style="margin-top:4px;">
										<?php esc_html_e('Use this summary in emails or notes when explaining which coupons are available or have already been used for this email address.', 'user-manager'); ?>
									</p>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Build coupon lookup result rows for a single email.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private static function get_coupon_lookup_results(string $lookup_email): array {
		if ($lookup_email === '' || !class_exists('WC_Coupon')) {
			return [];
		}

		$results = [];
		$now_ts  = current_time('timestamp');
		$query   = new WP_Query([
			'post_type'      => 'shop_coupon',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => [
				'relation' => 'OR',
				[
					'key'     => 'customer_email',
					'value'   => $lookup_email,
					'compare' => 'LIKE',
				],
				[
					'key'     => '_um_user_coupon_user_email',
					'value'   => $lookup_email,
					'compare' => 'LIKE',
				],
			],
		]);
		$coupon_ids = $query instanceof WP_Query ? (array) $query->posts : [];

		foreach ($coupon_ids as $coupon_id) {
			$coupon_id = (int) $coupon_id;
			if ($coupon_id <= 0) {
				continue;
			}

			$coupon = new WC_Coupon($coupon_id);
			if (!$coupon || !$coupon->get_id()) {
				continue;
			}

			$emails = [];
			$customer_email = get_post_meta($coupon_id, 'customer_email', true);
			if (!empty($customer_email)) {
				if (is_array($customer_email)) {
					$emails = array_merge($emails, $customer_email);
				} elseif (is_string($customer_email)) {
					$emails = array_merge($emails, array_map('trim', preg_split('/[\r\n,]+/', $customer_email)));
				}
			}
			if (method_exists($coupon, 'get_email_restrictions')) {
				$restrictions = $coupon->get_email_restrictions('edit');
				if (!empty($restrictions) && is_array($restrictions)) {
					$emails = array_merge($emails, $restrictions);
				}
			}
			$um_email = get_post_meta($coupon_id, '_um_user_coupon_user_email', true);
			if (!empty($um_email)) {
				$emails[] = $um_email;
			}
			$emails = array_values(array_unique(array_filter(array_map('trim', $emails))));

			$discount_type = (string) $coupon->get_discount_type();
			$amount        = (float) $coupon->get_amount();
			$amount_label  = function_exists('wc_price') ? wp_kses_post(wc_price($amount)) : (string) $amount;
			$usage_count   = (int) $coupon->get_usage_count();
			$usage_limit   = $coupon->get_usage_limit();
			$usage_label   = $usage_limit
				? sprintf('%1$d / %2$d', $usage_count, (int) $usage_limit)
				: sprintf('%1$d / %2$s', $usage_count, __('Unlimited', 'user-manager'));

			$date_expires = $coupon->get_date_expires();
			$expiry_ts    = $date_expires ? $date_expires->getTimestamp() : null;
			$expiry_label = $expiry_ts ? date_i18n(get_option('date_format'), $expiry_ts) : __('No expiration', 'user-manager');

			$date_created = $coupon->get_date_created();
			$created_ts   = $date_created ? $date_created->getTimestamp() : null;
			$created_label = $created_ts ? date_i18n(get_option('date_format'), $created_ts) : '';

			$post_status = get_post_status($coupon_id);
			if ($post_status !== 'publish') {
				$status_label = sprintf(
					/* translators: %s: post status slug */
					__('Inactive (%s)', 'user-manager'),
					$post_status
				);
			} elseif ($expiry_ts && $expiry_ts < $now_ts) {
				$status_label = __('Expired', 'user-manager');
			} elseif ($usage_limit && $usage_count >= $usage_limit) {
				$status_label = __('Fully Used', 'user-manager');
			} elseif ($usage_count > 0) {
				$status_label = __('Active – Used', 'user-manager');
			} else {
				$status_label = __('Active – Unused', 'user-manager');
			}

			$results[] = [
				'id'          => $coupon_id,
				'code'        => $coupon->get_code(),
				'emails'      => $emails,
				'edit'        => get_edit_post_link($coupon_id, ''),
				'type'        => $discount_type,
				'type_label'  => ucwords(str_replace('_', ' ', $discount_type)),
				'amount'      => $amount_label,
				'amount_raw'  => $amount,
				'usage'       => $usage_label,
				'usage_count' => $usage_count,
				'usage_limit' => $usage_limit,
				'created'     => $created_label,
				'created_ts'  => $created_ts,
				'expiry'      => $expiry_label,
				'expiry_ts'   => $expiry_ts,
				'status'      => $status_label,
			];
		}

		return $results;
	}
}




