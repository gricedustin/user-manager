<?php
/**
 * Add-on card: Bulk Page Creator.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Bulk_Page_Creator {

	public static function render(array $settings): void {
		$enabled = !empty($settings['bulk_page_creator_enabled']);
		$max_tokens = isset($settings['bulk_page_creator_max_tokens']) ? absint($settings['bulk_page_creator_max_tokens']) : 2000;
		if ($max_tokens < 100) {
			$max_tokens = 2000;
		}
		$temperature = isset($settings['bulk_page_creator_temperature']) ? (float) $settings['bulk_page_creator_temperature'] : 0.7;
		if ($temperature < 0 || $temperature > 1) {
			$temperature = 0.7;
		}
		$image_search_count = isset($settings['bulk_page_creator_image_search_count']) ? absint($settings['bulk_page_creator_image_search_count']) : 3;
		if ($image_search_count < 1) {
			$image_search_count = 3;
		}
		$include_with_every_prompt = isset($settings['bulk_page_creator_include_with_every_prompt']) ? (string) $settings['bulk_page_creator_include_with_every_prompt'] : '';
		$page_data = isset($settings['bulk_page_creator_page_data']) ? (string) $settings['bulk_page_creator_page_data'] : '';
		$has_api_key = !empty($settings['openai_api_key']);
		$history = get_option('user_manager_bulk_page_creator_history', []);
		if (!is_array($history)) {
			$history = [];
		}
		$history = array_slice($history, 0, 50);

		$current_user_id = get_current_user_id();
		$results = get_transient('um_bulk_page_creator_results_' . $current_user_id);
		if (is_array($results)) {
			delete_transient('um_bulk_page_creator_results_' . $current_user_id);
		} else {
			$results = null;
		}
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-bulk-page-creator" data-um-active-selectors="#um-bulk-page-creator-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-admin-page"></span>
				<h2><?php esc_html_e('Bulk Page Creator', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-bulk-page-creator-enabled" name="bulk_page_creator_enabled" value="1" <?php checked($enabled); ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Create multiple pages from AI prompts using your existing OpenAI API key, with optional image downloads and featured image assignment.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-bulk-page-creator-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<p class="description" style="margin-top:0;">
						<?php esc_html_e('Uses the API key from Settings > API Keys.', 'user-manager'); ?>
					</p>
					<?php if (!$has_api_key) : ?>
						<div class="notice notice-warning inline">
							<p><?php esc_html_e('No OpenAI API key detected. Add your key in Settings > API Keys before running page creation.', 'user-manager'); ?></p>
						</div>
					<?php endif; ?>

					<?php if (!empty($_GET['um_msg']) && $_GET['um_msg'] === 'bulk_page_creator_no_api_key') : ?>
						<div class="notice notice-error inline">
							<p><?php esc_html_e('OpenAI API key is missing. Add your key in Settings > API Keys before running Bulk Page Creator.', 'user-manager'); ?></p>
						</div>
					<?php endif; ?>

					<?php if (!empty($_GET['um_msg']) && $_GET['um_msg'] === 'bulk_page_creator_no_data') : ?>
						<div class="notice notice-error inline">
							<p><?php esc_html_e('No page data was submitted. Add at least one "Title|Prompt" line and try again.', 'user-manager'); ?></p>
						</div>
					<?php endif; ?>

					<?php if ($results !== null) : ?>
						<div class="notice notice-success inline">
							<p>
								<?php
								printf(
									/* translators: 1: total rows, 2: created count, 3: failed count */
									esc_html__('Bulk run complete. Total: %1$d, Created: %2$d, Failed: %3$d.', 'user-manager'),
									(int) ($results['total'] ?? 0),
									(int) ($results['created'] ?? 0),
									(int) ($results['failed'] ?? 0)
								);
								?>
							</p>
						</div>
					<?php endif; ?>

					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-bulk-page-creator-max-tokens"><?php esc_html_e('Max Tokens per Request', 'user-manager'); ?></label>
							<input type="number" min="100" max="8000" step="1" class="small-text" id="um-bulk-page-creator-max-tokens" name="bulk_page_creator_max_tokens" value="<?php echo esc_attr((string) $max_tokens); ?>" />
							<p class="description"><?php esc_html_e('Recommended range: 1000-3000. Higher values can increase cost.', 'user-manager'); ?></p>
						</div>
						<div class="um-form-field">
							<label for="um-bulk-page-creator-temperature"><?php esc_html_e('AI Temperature', 'user-manager'); ?></label>
							<input type="number" min="0" max="1" step="0.1" class="small-text" id="um-bulk-page-creator-temperature" name="bulk_page_creator_temperature" value="<?php echo esc_attr((string) $temperature); ?>" />
							<p class="description"><?php esc_html_e('0.0 = more consistent, 1.0 = more creative.', 'user-manager'); ?></p>
						</div>
					</div>

					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-bulk-page-creator-image-count"><?php esc_html_e('Images to Download', 'user-manager'); ?></label>
							<input type="number" min="1" max="10" step="1" class="small-text" id="um-bulk-page-creator-image-count" name="bulk_page_creator_image_search_count" value="<?php echo esc_attr((string) $image_search_count); ?>" />
						</div>
						<div class="um-form-field">
							<label for="um-bulk-page-creator-include-every"><?php esc_html_e('Include with Every Page Prompt', 'user-manager'); ?></label>
							<textarea id="um-bulk-page-creator-include-every" name="bulk_page_creator_include_with_every_prompt" rows="3" class="large-text"><?php echo esc_textarea($include_with_every_prompt); ?></textarea>
						</div>
					</div>

					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr 1fr;">
						<div class="um-form-field">
							<label>
								<input type="checkbox" name="bulk_page_creator_auto_publish" value="1" <?php checked(!empty($settings['bulk_page_creator_auto_publish'])); ?> />
								<?php esc_html_e('Auto Publish Pages', 'user-manager'); ?>
							</label>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" id="um-bulk-page-creator-download-images" name="bulk_page_creator_download_images" value="1" <?php checked(!array_key_exists('bulk_page_creator_download_images', $settings) || !empty($settings['bulk_page_creator_download_images'])); ?> />
								<?php esc_html_e('Download Images', 'user-manager'); ?>
							</label>
						</div>
						<div class="um-form-field">
							<label>
								<input type="checkbox" id="um-bulk-page-creator-featured-image" name="bulk_page_creator_set_featured_image" value="1" <?php checked(!array_key_exists('bulk_page_creator_set_featured_image', $settings) || !empty($settings['bulk_page_creator_set_featured_image'])); ?> />
								<?php esc_html_e('Set Featured Image', 'user-manager'); ?>
							</label>
						</div>
					</div>

					<?php wp_nonce_field('user_manager_bulk_page_creator', 'user_manager_bulk_page_creator_nonce', false); ?>

					<div class="um-form-field">
						<label for="um-bulk-page-creator-page-data"><?php esc_html_e('Page Data', 'user-manager'); ?></label>
						<textarea id="um-bulk-page-creator-page-data" name="bulk_page_creator_page_data" rows="8" class="large-text code" placeholder="<?php esc_attr_e("Home|Create a welcoming homepage with clear value propositions\nAbout Us|Write our company history, mission, and team overview\nServices|Explain our key services and how clients benefit", 'user-manager'); ?>"><?php echo esc_textarea($page_data); ?></textarea>
						<p class="description">
							<?php esc_html_e('One line per page in this format: Title|Prompt', 'user-manager'); ?>
						</p>
					</div>

					<p style="margin-top:12px;">
						<button type="submit" class="button button-primary um-addon-action-submit" data-um-target-action="user_manager_bulk_page_creator" <?php disabled(!$has_api_key); ?>>
							<?php esc_html_e('Create Pages', 'user-manager'); ?>
						</button>
					</p>

					<?php if ($results !== null && !empty($results['details']) && is_array($results['details'])) : ?>
						<hr style="margin:20px 0;" />
						<h3 style="margin:0 0 8px;"><?php esc_html_e('Latest Run Details', 'user-manager'); ?></h3>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e('Title', 'user-manager'); ?></th>
									<th><?php esc_html_e('Status', 'user-manager'); ?></th>
									<th><?php esc_html_e('Page', 'user-manager'); ?></th>
									<th><?php esc_html_e('Images', 'user-manager'); ?></th>
									<th><?php esc_html_e('Error', 'user-manager'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($results['details'] as $detail) : ?>
									<?php
									$detail_title = isset($detail['title']) ? (string) $detail['title'] : '';
									$detail_status = isset($detail['status']) ? (string) $detail['status'] : 'failed';
									$detail_page_id = isset($detail['page_id']) ? absint($detail['page_id']) : 0;
									$detail_images = isset($detail['images_downloaded']) ? absint($detail['images_downloaded']) : 0;
									$detail_error = isset($detail['error']) ? (string) $detail['error'] : '';
									?>
									<tr>
										<td><?php echo esc_html($detail_title); ?></td>
										<td><?php echo esc_html(ucfirst($detail_status)); ?></td>
										<td>
											<?php if ($detail_page_id > 0) : ?>
												<a href="<?php echo esc_url(get_edit_post_link($detail_page_id)); ?>" target="_blank" rel="noopener noreferrer">
													<?php echo esc_html((string) $detail_page_id); ?>
												</a>
											<?php else : ?>
												—
											<?php endif; ?>
										</td>
										<td><?php echo esc_html((string) $detail_images); ?></td>
										<td><?php echo esc_html($detail_error); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="um-admin-card">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-backup"></span>
				<h2><?php esc_html_e('Bulk Page Creator History', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<?php if (empty($history)) : ?>
					<p class="description"><?php esc_html_e('No page creation history yet.', 'user-manager'); ?></p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e('Timestamp', 'user-manager'); ?></th>
								<th><?php esc_html_e('Title', 'user-manager'); ?></th>
								<th><?php esc_html_e('Status', 'user-manager'); ?></th>
								<th><?php esc_html_e('User', 'user-manager'); ?></th>
								<th><?php esc_html_e('Page', 'user-manager'); ?></th>
								<th><?php esc_html_e('Images', 'user-manager'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($history as $row) : ?>
								<?php
								$created_at = isset($row['created_at']) ? (string) $row['created_at'] : '';
								$title = isset($row['title']) ? (string) $row['title'] : '';
								$user_id = isset($row['user_id']) ? absint($row['user_id']) : 0;
								$page_id = isset($row['page_id']) ? absint($row['page_id']) : 0;
								$images_downloaded = isset($row['images_downloaded']) ? absint($row['images_downloaded']) : 0;
								$error = isset($row['error']) ? (string) $row['error'] : '';
								$status = $error === '' ? __('Success', 'user-manager') : __('Failed', 'user-manager');
								$user_display = '—';
								if ($user_id > 0) {
									$user = get_userdata($user_id);
									if ($user && !empty($user->user_email)) {
										$user_display = (string) $user->user_email;
									}
								}
								?>
								<tr>
									<td><?php echo esc_html($created_at !== '' ? $created_at : '—'); ?></td>
									<td><?php echo esc_html($title); ?></td>
									<td><?php echo esc_html($status); ?></td>
									<td><?php echo esc_html($user_display); ?></td>
									<td>
										<?php if ($page_id > 0) : ?>
											<a href="<?php echo esc_url(get_edit_post_link($page_id)); ?>" target="_blank" rel="noopener noreferrer">
												<?php echo esc_html((string) $page_id); ?>
											</a>
										<?php else : ?>
											—
										<?php endif; ?>
									</td>
									<td><?php echo esc_html((string) $images_downloaded); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

