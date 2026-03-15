<?php
/**
 * Bulk Page Creator action handlers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Actions_Bulk_Page_Creator_Trait {

	/**
	 * Handle Bulk Page Creator submit action.
	 */
	public static function handle_bulk_page_creator(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_bulk_page_creator', 'user_manager_bulk_page_creator_nonce');

		$settings = User_Manager_Core::get_settings();
		$settings = self::bulk_page_creator_resolve_runtime_settings($settings);
		if (empty($settings['bulk_page_creator_enabled'])) {
			wp_safe_redirect(self::bulk_page_creator_get_redirect_url(['um_msg' => 'bulk_page_creator_disabled']));
			exit;
		}

		$page_data_raw = isset($_POST['bulk_page_creator_page_data']) ? sanitize_textarea_field(wp_unslash($_POST['bulk_page_creator_page_data'])) : '';
		if (trim($page_data_raw) === '') {
			wp_safe_redirect(self::bulk_page_creator_get_redirect_url(['um_msg' => 'bulk_page_creator_no_data']));
			exit;
		}

		$api_key = isset($settings['openai_api_key']) ? trim((string) $settings['openai_api_key']) : '';
		if ($api_key === '') {
			wp_safe_redirect(self::bulk_page_creator_get_redirect_url(['um_msg' => 'bulk_page_creator_no_api_key']));
			exit;
		}

		$results = self::bulk_page_creator_process_pages($page_data_raw, $settings, $api_key);
		$current_user_id = get_current_user_id();
		set_transient('um_bulk_page_creator_results_' . $current_user_id, $results, 10 * MINUTE_IN_SECONDS);

		User_Manager_Core::add_activity_log(
			'bulk_page_creator_run',
			$current_user_id,
			'Bulk Page Creator',
			[
				'total' => $results['total'],
				'created' => $results['created'],
				'failed' => $results['failed'],
				'auto_publish' => !empty($settings['bulk_page_creator_auto_publish']),
				'download_images' => !empty($settings['bulk_page_creator_download_images']),
			]
		);

		wp_safe_redirect(self::bulk_page_creator_get_redirect_url(['bulk_page_creator_completed' => '1']));
		exit;
	}

	/**
	 * Build redirect URL back to Bulk Page Creator card.
	 *
	 * @param array<string,string> $extra_args
	 */
	private static function bulk_page_creator_get_redirect_url(array $extra_args = []): string {
		$args = array_merge(
			[
				'addon_section' => 'bulk-page-creator',
			],
			$extra_args
		);
		return add_query_arg($args, User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS));
	}

	/**
	 * Process all "Title|Prompt" rows.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<string,mixed>
	 */
	private static function bulk_page_creator_process_pages(string $page_data, array $settings, string $api_key): array {
		$lines = preg_split('/\r\n|\r|\n/', $page_data) ?: [];
		$results = [
			'total' => 0,
			'created' => 0,
			'failed' => 0,
			'details' => [],
		];

		foreach ($lines as $line) {
			$line = trim((string) $line);
			if ($line === '') {
				continue;
			}

			$results['total']++;
			$parts = explode('|', $line, 2);
			if (count($parts) !== 2) {
				$detail = [
					'title' => $line,
					'prompt' => '',
					'status' => 'failed',
					'error' => __('Invalid format. Use: Title|Prompt', 'user-manager'),
					'page_id' => 0,
					'images_downloaded' => 0,
				];
				$results['details'][] = $detail;
				$results['failed']++;
				self::bulk_page_creator_append_history([
					'title' => $detail['title'],
					'prompt' => '',
					'page_id' => 0,
					'user_id' => get_current_user_id(),
					'images_downloaded' => 0,
					'post_status' => '',
					'error' => (string) $detail['error'],
					'created_at' => current_time('mysql'),
				]);
				continue;
			}

			$title = sanitize_text_field(trim($parts[0]));
			$prompt = sanitize_textarea_field(trim($parts[1]));
			$detail = self::bulk_page_creator_create_single_page($title, $prompt, $settings, $api_key);
			$results['details'][] = $detail;
			if (($detail['status'] ?? '') === 'success') {
				$results['created']++;
			} else {
				$results['failed']++;
			}

			self::bulk_page_creator_append_history([
				'title' => (string) ($detail['title'] ?? $title),
				'prompt' => (string) ($detail['prompt'] ?? $prompt),
				'page_id' => (int) ($detail['page_id'] ?? 0),
				'user_id' => get_current_user_id(),
				'images_downloaded' => (int) ($detail['images_downloaded'] ?? 0),
				'post_status' => (string) ($detail['post_status'] ?? ''),
				'error' => (string) ($detail['error'] ?? ''),
				'created_at' => current_time('mysql'),
			]);
		}

		return $results;
	}

	/**
	 * Create one page with AI content and optional images.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<string,mixed>
	 */
	private static function bulk_page_creator_create_single_page(string $title, string $prompt, array $settings, string $api_key): array {
		$result = [
			'title' => $title,
			'prompt' => $prompt,
			'status' => 'failed',
			'error' => '',
			'page_id' => 0,
			'images_downloaded' => 0,
		];

		if ($title === '' || $prompt === '') {
			$result['error'] = __('Title and prompt are required.', 'user-manager');
			return $result;
		}

		$max_tokens = isset($settings['bulk_page_creator_max_tokens']) ? absint($settings['bulk_page_creator_max_tokens']) : 2000;
		if ($max_tokens < 100) {
			$max_tokens = 100;
		}
		if ($max_tokens > 8000) {
			$max_tokens = 8000;
		}

		$temperature = isset($settings['bulk_page_creator_temperature']) ? (float) $settings['bulk_page_creator_temperature'] : 0.7;
		if ($temperature < 0) {
			$temperature = 0;
		}
		if ($temperature > 1) {
			$temperature = 1;
		}

		$include_with_every_prompt = isset($settings['bulk_page_creator_include_with_every_prompt'])
			? trim((string) $settings['bulk_page_creator_include_with_every_prompt'])
			: '';
		$full_prompt = $include_with_every_prompt !== ''
			? ($include_with_every_prompt . "\n\n" . $prompt)
			: $prompt;

		$content = self::bulk_page_creator_generate_content($api_key, $full_prompt, $max_tokens, $temperature);
		if (is_wp_error($content)) {
			$result['error'] = $content->get_error_message();
			return $result;
		}

		$post_status = !empty($settings['bulk_page_creator_auto_publish']) ? 'publish' : 'draft';
		$result['post_status'] = $post_status;
		$page_id = wp_insert_post([
			'post_title' => $title,
			'post_content' => wp_kses_post($content),
			'post_status' => $post_status,
			'post_type' => 'page',
			'post_author' => get_current_user_id(),
		], true);
		if (is_wp_error($page_id)) {
			$result['error'] = $page_id->get_error_message();
			return $result;
		}

		$page_id = (int) $page_id;
		$result['page_id'] = $page_id;

		$image_ids = [];
		if (!empty($settings['bulk_page_creator_download_images'])) {
			$image_count = isset($settings['bulk_page_creator_image_search_count']) ? absint($settings['bulk_page_creator_image_search_count']) : 3;
			if ($image_count < 1) {
				$image_count = 1;
			}
			if ($image_count > 10) {
				$image_count = 10;
			}
			$image_ids = self::bulk_page_creator_download_images($title, $page_id, $image_count);
		}

		if (!empty($settings['bulk_page_creator_set_featured_image']) && !empty($image_ids)) {
			set_post_thumbnail($page_id, (int) $image_ids[0]);
		}

		$result['images_downloaded'] = count($image_ids);
		$result['status'] = 'success';

		return $result;
	}

	/**
	 * Call OpenAI and return generated HTML page content.
	 */
	private static function bulk_page_creator_generate_content(string $api_key, string $prompt, int $max_tokens, float $temperature) {
		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			[
				'timeout' => 60,
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type' => 'application/json',
				],
				'body' => wp_json_encode([
					'model' => 'gpt-4o-mini',
					'messages' => [
						[
							'role' => 'system',
							'content' => 'You are a professional content writer. Create engaging, informative website page content using HTML with headings, paragraphs, and lists when helpful. Respond with content only.',
						],
						[
							'role' => 'user',
							'content' => $prompt,
						],
					],
					'max_tokens' => $max_tokens,
					'temperature' => $temperature,
				]),
			]
		);

		if (is_wp_error($response)) {
			return new WP_Error('api_error', sprintf(__('API request failed: %s', 'user-manager'), $response->get_error_message()));
		}

		$code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if ($code !== 200) {
			$message = isset($data['error']['message']) ? (string) $data['error']['message'] : (string) $body;
			return new WP_Error('api_error', sprintf(__('API Error: %s', 'user-manager'), $message));
		}

		$content = isset($data['choices'][0]['message']['content']) ? trim((string) $data['choices'][0]['message']['content']) : '';
		$content = preg_replace('/^```[a-zA-Z]*\s*|\s*```$/', '', $content);
		if ($content === '') {
			return new WP_Error('api_error', __('Invalid API response format.', 'user-manager'));
		}

		return $content;
	}

	/**
	 * Download placeholder images and attach to the page.
	 *
	 * @return array<int,int> Attachment IDs.
	 */
	private static function bulk_page_creator_download_images(string $title, int $page_id, int $image_count): array {
		$image_ids = [];
		if (!function_exists('media_handle_sideload') || !function_exists('download_url')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		for ($i = 0; $i < $image_count; $i++) {
			$image_url = 'https://picsum.photos/1200/800?random=' . rawurlencode((string) wp_rand(1, 999999));
			$tmp_file = download_url($image_url, 45);
			if (is_wp_error($tmp_file)) {
				continue;
			}

			$file_array = [
				'name' => sanitize_file_name($title . '-' . ($i + 1) . '.jpg'),
				'type' => 'image/jpeg',
				'tmp_name' => $tmp_file,
				'error' => 0,
				'size' => filesize($tmp_file),
			];

			$attachment_id = media_handle_sideload($file_array, $page_id, $title);
			if (is_wp_error($attachment_id)) {
				@unlink($tmp_file);
				continue;
			}

			$image_ids[] = (int) $attachment_id;
		}

		return $image_ids;
	}

	/**
	 * Persist one history row.
	 *
	 * @param array<string,mixed> $row
	 */
	private static function bulk_page_creator_append_history(array $row): void {
		$history = get_option('user_manager_bulk_page_creator_history', []);
		if (!is_array($history)) {
			$history = [];
		}
		array_unshift($history, $row);
		if (count($history) > 200) {
			$history = array_slice($history, 0, 200);
		}
		update_option('user_manager_bulk_page_creator_history', $history);
	}

	/**
	 * Normalize posted add-on settings and persist them.
	 *
	 * @param array<string,mixed> $settings
	 * @return array<string,mixed>
	 */
	private static function bulk_page_creator_resolve_runtime_settings(array $settings): array {
		$settings['bulk_page_creator_enabled'] = isset($_POST['bulk_page_creator_enabled']) ? $_POST['bulk_page_creator_enabled'] === '1' : !empty($settings['bulk_page_creator_enabled']);
		$settings['bulk_page_creator_max_tokens'] = isset($_POST['bulk_page_creator_max_tokens']) ? max(100, min(8000, absint($_POST['bulk_page_creator_max_tokens']))) : (isset($settings['bulk_page_creator_max_tokens']) ? absint($settings['bulk_page_creator_max_tokens']) : 2000);
		$temperature = isset($_POST['bulk_page_creator_temperature']) ? (float) wp_unslash($_POST['bulk_page_creator_temperature']) : (isset($settings['bulk_page_creator_temperature']) ? (float) $settings['bulk_page_creator_temperature'] : 0.7);
		$settings['bulk_page_creator_temperature'] = max(0, min(1, $temperature));
		$settings['bulk_page_creator_image_search_count'] = isset($_POST['bulk_page_creator_image_search_count']) ? max(1, min(10, absint($_POST['bulk_page_creator_image_search_count']))) : (isset($settings['bulk_page_creator_image_search_count']) ? max(1, min(10, absint($settings['bulk_page_creator_image_search_count']))) : 3);
		$settings['bulk_page_creator_auto_publish'] = isset($_POST['bulk_page_creator_auto_publish']) ? $_POST['bulk_page_creator_auto_publish'] === '1' : !empty($settings['bulk_page_creator_auto_publish']);
		$settings['bulk_page_creator_download_images'] = isset($_POST['bulk_page_creator_download_images']) ? $_POST['bulk_page_creator_download_images'] === '1' : !empty($settings['bulk_page_creator_download_images']);
		$settings['bulk_page_creator_set_featured_image'] = isset($_POST['bulk_page_creator_set_featured_image']) ? $_POST['bulk_page_creator_set_featured_image'] === '1' : !empty($settings['bulk_page_creator_set_featured_image']);
		$settings['bulk_page_creator_include_with_every_prompt'] = isset($_POST['bulk_page_creator_include_with_every_prompt']) ? sanitize_textarea_field(wp_unslash($_POST['bulk_page_creator_include_with_every_prompt'])) : (string) ($settings['bulk_page_creator_include_with_every_prompt'] ?? '');
		$settings['bulk_page_creator_page_data'] = isset($_POST['bulk_page_creator_page_data']) ? sanitize_textarea_field(wp_unslash($_POST['bulk_page_creator_page_data'])) : (string) ($settings['bulk_page_creator_page_data'] ?? '');

		update_option(User_Manager_Core::OPTION_KEY, $settings);
		return $settings;
	}
}

