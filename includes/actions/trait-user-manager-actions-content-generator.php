<?php
/**
 * Extracted methods from class-user-manager-actions.php.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Actions_Content_Generator_Trait {
		public static function handle_blog_post_importer(): void {
			if (!current_user_can('edit_posts')) {
				wp_die(__('You do not have permission to access this page.', 'user-manager'));
			}
			$settings = User_Manager_Core::get_settings();
			if (empty($settings['openai_content_generator_enabled'])) {
				wp_safe_redirect(add_query_arg(['tab' => User_Manager_Core::TAB_ADDONS, 'um_msg' => 'error'], admin_url('admin.php?page=' . User_Manager_Core::SETTINGS_PAGE_SLUG)));
				exit;
			}
	
			check_admin_referer('user_manager_blog_post_importer');
	
			$raw_posts = isset($_POST['blog_importer_posts']) && is_array($_POST['blog_importer_posts']) ? $_POST['blog_importer_posts'] : [];
			$posts = [];
			foreach ($raw_posts as $row) {
				$title = isset($row['title']) ? sanitize_text_field(wp_unslash($row['title'])) : '';
				if ($title === '') {
					continue;
				}
				$raw_body = isset($row['raw_body']) ? trim((string) wp_unslash($row['raw_body'])) : '';
				$description = $raw_body !== '' ? wp_kses_post($raw_body) : (isset($row['description']) ? wp_kses_post(wp_unslash($row['description'])) : '');
				$categories = [];
				if (!empty($row['categories']) && is_array($row['categories'])) {
					$categories = array_map('absint', $row['categories']);
					$categories = array_filter($categories);
				}
				$date = isset($row['date']) ? sanitize_text_field(wp_unslash($row['date'])) : '';
				$tags_raw = isset($row['tags']) ? sanitize_text_field(wp_unslash($row['tags'])) : '';
				$tags = array_filter(array_map('trim', explode(',', $tags_raw)));
				$posts[] = [
					'title'       => $title,
					'description' => $description,
					'categories'  => $categories,
					'date'        => $date,
					'tags'        => $tags,
				];
			}
	
			if (empty($posts)) {
				wp_safe_redirect(add_query_arg(['tab' => User_Manager_Core::TAB_ADDONS, 'um_msg' => 'blog_importer_no_posts'], admin_url('admin.php?page=' . User_Manager_Core::SETTINGS_PAGE_SLUG)));
				exit;
			}
	
			$apply_random_image = !empty($_POST['blog_importer_apply_random_image']) && $_POST['blog_importer_apply_random_image'] === '1';
			$single_plus_25 = !empty($_POST['blog_importer_single_plus_25']) && $_POST['blog_importer_single_plus_25'] === '1';
			$single_plus_days = $single_plus_25 && isset($_POST['blog_importer_single_plus_days']) ? max(1, min(365, (int) $_POST['blog_importer_single_plus_days'])) : 25;
			update_option('um_blog_importer_plus_days', $single_plus_days);
			$apply_spread = !empty($_POST['blog_importer_apply_spread']) && $_POST['blog_importer_apply_spread'] === '1';
			$spread_first = isset($_POST['blog_importer_spread_first']) ? sanitize_text_field(wp_unslash($_POST['blog_importer_spread_first'])) : '';
			$spread_last = isset($_POST['blog_importer_spread_last']) ? sanitize_text_field(wp_unslash($_POST['blog_importer_spread_last'])) : '';
			$use_spread = $apply_spread && $spread_first !== '' && $spread_last !== '';
			$spread_dates = [];
			if ($use_spread && count($posts) > 0) {
				$t1 = strtotime($spread_first);
				$t2 = strtotime($spread_last);
				if ($t1 !== false && $t2 !== false) {
					$n = count($posts);
					$step = ($n > 1) ? ($t2 - $t1) / ($n - 1) : 0;
					for ($i = 0; $i < $n; $i++) {
						$ts = $n > 1 ? (int) round($t1 + $i * $step) : $t1;
						$spread_dates[] = gmdate('Y-m-d H:i:s', $ts);
					}
				}
			}
	
			$last_post_ts_for_plus_days = null;
			if ($single_plus_25) {
				$last_published = get_posts(['post_type' => 'post', 'post_status' => ['publish', 'future'], 'numberposts' => 1, 'orderby' => 'date', 'order' => 'DESC']);
				if (!empty($last_published)) {
					$last_ts = get_post_time('U', true, $last_published[0]);
					if ($last_ts !== false) {
						$last_post_ts_for_plus_days = $last_ts;
					}
				}
			}
			$undated_offset = 0;
	
			$pool_attachment_ids = [];
			if ($apply_random_image) {
				global $wpdb;
				$used = $wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value != ''");
				$used = array_map('absint', array_filter($used));
				$args = [
					'post_type'      => 'attachment',
					'post_status'   => 'inherit',
					'posts_per_page'=> -1,
					'fields'        => 'ids',
					'meta_query'    => [['key' => '_wp_attachment_metadata', 'compare' => 'EXISTS']],
				];
				$all = get_posts($args);
				foreach ($all as $aid) {
					$mime = get_post_mime_type($aid);
					if ($mime && strpos($mime, 'image/') === 0 && !in_array($aid, $used, true)) {
						$pool_attachment_ids[] = $aid;
					}
				}
				shuffle($pool_attachment_ids);
			}
	
			$blog_categories_for_auto = get_categories(['taxonomy' => 'category', 'hide_empty' => false]);
			$created = [];
			$current_user_id = get_current_user_id();
			foreach ($posts as $i => $p) {
				$post_date = '';
				if ($use_spread && isset($spread_dates[$i])) {
					$post_date = $spread_dates[$i];
				} elseif ($p['date'] !== '') {
					$post_date = $p['date'] . ' 12:00:00';
				} elseif ($last_post_ts_for_plus_days !== null && $single_plus_25) {
					$undated_offset++;
					$post_date = gmdate('Y-m-d H:i:s', $last_post_ts_for_plus_days + ($single_plus_days * DAY_IN_SECONDS * $undated_offset));
				}
				if ($post_date === '') {
					$post_date = current_time('mysql');
				}
	
				$categories = array_values(array_unique(array_map('absint', $p['categories'])));
				$body_text = wp_strip_all_tags($p['description']);
				$title_and_body = $p['title'] . ' ' . $body_text;
				foreach ($blog_categories_for_auto as $cat) {
					if (in_array($cat->term_id, $categories, true)) {
						continue;
					}
					if ($cat->name !== '' && stripos($title_and_body, $cat->name) !== false) {
						$categories[] = $cat->term_id;
					} elseif ($cat->slug !== '' && stripos($title_and_body, $cat->slug) !== false) {
						$categories[] = $cat->term_id;
					}
				}
				$categories = array_values(array_unique($categories));
	
				$post_content = self::html_to_paragraph_blocks($p['description']);
				$post_data = [
					'post_title'   => $p['title'],
					'post_content' => $post_content,
					'post_status'  => 'publish',
					'post_author'  => $current_user_id ?: 1,
					'post_type'    => 'post',
					'post_date'    => $post_date,
				];
				$post_id = wp_insert_post($post_data);
				if (is_wp_error($post_id) || !$post_id) {
					continue;
				}
				if (!empty($categories)) {
					wp_set_post_terms($post_id, $categories, 'category');
				}
				if (!empty($p['tags'])) {
					wp_set_post_terms($post_id, $p['tags'], 'post_tag');
				}
				if ($apply_random_image && !empty($pool_attachment_ids)) {
					$attach_id = array_shift($pool_attachment_ids);
					set_post_thumbnail($post_id, $attach_id);
				}
				$created[] = ['id' => $post_id, 'title' => $p['title']];
			}
	
			User_Manager_Core::add_activity_log('blog_post_import', $current_user_id, 'ChatGPT Content Generator', [
				'created_count'       => count($created),
				'apply_random_image'  => $apply_random_image,
				'spread_dates_used'   => $use_spread,
				'post_titles'         => array_column($created, 'title'),
				'post_ids'            => array_column($created, 'id'),
			]);
	
			set_transient('um_blog_importer_created_' . $current_user_id, $created, 60);
	
			wp_safe_redirect(add_query_arg([
				'tab'    => User_Manager_Core::TAB_ADDONS,
				'um_msg' => 'blog_importer_ok',
				'count'  => count($created),
			], admin_url('admin.php?page=' . User_Manager_Core::SETTINGS_PAGE_SLUG)));
			exit;
		}
	
		

		public static function ajax_blog_chatgpt(): void {
			$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
			if (!wp_verify_nonce($nonce, 'user_manager_blog_chatgpt')) {
				wp_send_json_error([
					'message' => __('Verification failed. Please refresh the page and try again.', 'user-manager'),
					'debug'   => ['code' => 'invalid_nonce'],
				]);
			}
			if (!current_user_can('edit_posts')) {
				wp_send_json_error(['message' => __('You do not have permission to do this.', 'user-manager'), 'debug' => ['code' => 'permission_denied']]);
			}
			$settings = User_Manager_Core::get_settings();
			if (empty($settings['openai_content_generator_enabled'])) {
				wp_send_json_error(['message' => __('ChatGPT Content Generator add-on is disabled.', 'user-manager'), 'debug' => ['code' => 'addon_disabled']]);
			}
			$api_key = isset($settings['openai_api_key']) ? trim((string) $settings['openai_api_key']) : '';
			if ($api_key === '') {
				wp_send_json_error(['message' => __('OpenAI API key is not configured in Settings.', 'user-manager'), 'debug' => ['code' => 'no_api_key']]);
			}
			$topic_idea = isset($_POST['topic_idea']) ? sanitize_textarea_field(wp_unslash($_POST['topic_idea'])) : '';
			$prompt_base = isset($_POST['prompt_base']) ? wp_kses_post(wp_unslash($_POST['prompt_base'])) : '';
			$user_message = '';
			if ($topic_idea !== '') {
				$user_message .= "Topic idea: " . $topic_idea . "\n\n";
			}
			$user_message .= $prompt_base !== '' ? $prompt_base : __('Please write a blog post about 5 paragraphs long (4-8 sentences each), with 3-5 SEO-friendly titles.', 'user-manager');
			$prompt_append = isset($settings['openai_prompt_append']) ? trim((string) $settings['openai_prompt_append']) : '';
			if ($prompt_append !== '') {
				$user_message .= "\n\n" . $prompt_append;
			}
			$system_message = 'You are a blog post writer. You must respond with valid JSON only, no other text or markdown. Use this exact format: {"titles":["Title 1","Title 2","Title 3"],"body":"<p>First paragraph HTML</p><p>Second paragraph</p>...","tags":"tag1, tag2, tag3"}. Generate 3 to 5 SEO-friendly titles in the "titles" array. Generate the post body in the "body" string as HTML using <p> tags for paragraphs. The body should be about 5 paragraphs. Also include a "tags" string with 3 to 5 relevant blog tags, comma-separated (e.g. "health, fitness, wellness, tips, lifestyle").';
			$response = wp_remote_post(
				'https://api.openai.com/v1/chat/completions',
				[
					'timeout' => 60,
					'headers' => [
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					],
					'body' => wp_json_encode([
						'model' => 'gpt-4o-mini',
						'messages' => [
							['role' => 'system', 'content' => $system_message],
							['role' => 'user', 'content' => $user_message],
						],
						'temperature' => 0.7,
					]),
				]
			);
			if (is_wp_error($response)) {
				wp_send_json_error([
					'message' => $response->get_error_message(),
					'debug'   => ['error_code' => $response->get_error_code()],
				]);
			}
			$code = wp_remote_retrieve_response_code($response);
			$body = wp_remote_retrieve_body($response);
			if ($code !== 200) {
				$decoded = json_decode($body, true);
				$err = isset($decoded['error']['message']) ? $decoded['error']['message'] : $body;
				$debug = [
					'http_code' => $code,
					'response_preview' => mb_substr($body, 0, 500),
				];
				if (isset($decoded['error']['code'])) {
					$debug['api_error_code'] = $decoded['error']['code'];
				}
				wp_send_json_error(['message' => $err, 'debug' => $debug]);
			}
			$data = json_decode($body, true);
			$content = isset($data['choices'][0]['message']['content']) ? trim($data['choices'][0]['message']['content']) : '';
			if ($content === '') {
				wp_send_json_error([
					'message' => __('No content in API response.', 'user-manager'),
					'debug'   => ['response_keys' => $data ? array_keys($data) : [], 'body_preview' => mb_substr($body, 0, 300)],
				]);
			}
			$content = preg_replace('/^```\w*\s*|\s*```$/','', $content);
			$parsed = json_decode($content, true);
			if (!is_array($parsed) || !isset($parsed['titles']) || !isset($parsed['body'])) {
				wp_send_json_error([
					'message' => __('Could not parse API response. Expected JSON with titles and body.', 'user-manager'),
					'debug'   => ['content_preview' => mb_substr($content, 0, 400), 'json_error' => json_last_error_msg()],
				]);
			}
			$titles = is_array($parsed['titles']) ? array_values($parsed['titles']) : [];
			$body_html = is_string($parsed['body']) ? $parsed['body'] : '';
			$titles = array_slice(array_filter(array_map('sanitize_text_field', $titles)), 0, 10);
			$tags_string = '';
			if (isset($parsed['tags'])) {
				if (is_string($parsed['tags'])) {
					$tags_string = sanitize_text_field($parsed['tags']);
				} elseif (is_array($parsed['tags'])) {
					$tags_string = implode(', ', array_filter(array_map('sanitize_text_field', $parsed['tags'])));
				}
			}
			wp_send_json_success(['titles' => $titles, 'body' => $body_html, 'tags' => $tags_string]);
		}
	
		

		public static function ajax_set_post_thumbnail(): void {
			$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
			if (!wp_verify_nonce($nonce, 'user_manager_set_post_thumbnail')) {
				wp_send_json_error(['message' => __('Verification failed.', 'user-manager')]);
			}
			if (!current_user_can('edit_posts')) {
				wp_send_json_error(['message' => __('You do not have permission to edit this post.', 'user-manager')]);
			}
			$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
			$attachment_id = isset($_POST['attachment_id']) ? absint($_POST['attachment_id']) : 0;
			if (!$post_id || get_post_type($post_id) !== 'post') {
				wp_send_json_error(['message' => __('Invalid post.', 'user-manager')]);
			}
			if (!$attachment_id) {
				delete_post_thumbnail($post_id);
				$thumb_url = '';
			} else {
				if (get_post_type($attachment_id) !== 'attachment' || !wp_attachment_is_image($attachment_id)) {
					wp_send_json_error(['message' => __('Invalid image.', 'user-manager')]);
				}
				set_post_thumbnail($post_id, $attachment_id);
				$thumb_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
			}
			$view_url = get_permalink($post_id);
			wp_send_json_success(['thumb_url' => $thumb_url ?: '', 'view_url' => $view_url ?: '']);
		}
	
		

		public static function ajax_set_post_date(): void {
			$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
			if (!wp_verify_nonce($nonce, 'user_manager_set_post_date')) {
				wp_send_json_error(['message' => __('Verification failed.', 'user-manager')]);
			}
			if (!current_user_can('edit_posts')) {
				wp_send_json_error(['message' => __('You do not have permission to edit this post.', 'user-manager')]);
			}
			$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
			$date = isset($_POST['date']) ? sanitize_text_field(wp_unslash($_POST['date'])) : '';
			if (!$post_id || get_post_type($post_id) !== 'post') {
				wp_send_json_error(['message' => __('Invalid post.', 'user-manager')]);
			}
			if ($date === '' || strtotime($date) === false) {
				wp_send_json_error(['message' => __('Invalid date.', 'user-manager')]);
			}
			$datetime = $date . ' 12:00:00';
			wp_update_post([
				'ID'            => $post_id,
				'post_date'     => $datetime,
				'post_date_gmt' => get_gmt_from_date($datetime),
			]);
			$formatted = get_the_date('', $post_id);
			wp_send_json_success(['formatted_date' => $formatted]);
		}
	
		

		public static function ajax_spread_scheduled_posts(): void {
			$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
			if (!wp_verify_nonce($nonce, 'user_manager_spread_scheduled_posts')) {
				wp_send_json_error(['message' => __('Verification failed.', 'user-manager')]);
			}
			if (!current_user_can('edit_posts')) {
				wp_send_json_error(['message' => __('You do not have permission to edit posts.', 'user-manager')]);
			}
			$target_date = isset($_POST['target_date']) ? sanitize_text_field(wp_unslash($_POST['target_date'])) : '';
			if ($target_date === '' || strtotime($target_date) === false) {
				wp_send_json_error(['message' => __('Please choose a date.', 'user-manager')]);
			}
			$scheduled = get_posts([
				'post_type'      => 'post',
				'post_status'    => 'future',
				'orderby'        => 'date',
				'order'          => 'ASC',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
			]);
			if (empty($scheduled)) {
				wp_send_json_success(['message' => __('No scheduled posts to spread.', 'user-manager'), 'updated' => 0]);
			}
			$start_ts = (int) current_time('timestamp');
			$end_ts = strtotime($target_date . ' 12:00:00');
			if ($end_ts === false || $end_ts < $start_ts) {
				$end_ts = $start_ts;
			}
			$n = count($scheduled);
			$step = $n > 1 ? ($end_ts - $start_ts) / ($n - 1) : 0;
			$updated = 0;
			foreach ($scheduled as $i => $post) {
				$post_ts = (int) round($start_ts + $step * $i);
				$post_date = date('Y-m-d H:i:s', $post_ts);
				$post_date_gmt = gmdate('Y-m-d H:i:s', $post_ts);
				wp_update_post([
					'ID'            => (int) $post->ID,
					'post_date'     => $post_date,
					'post_date_gmt' => $post_date_gmt,
				]);
				$updated++;
			}
			wp_send_json_success(['message' => sprintf(__('%d scheduled post(s) spread evenly to %s.', 'user-manager'), $updated, wp_date(get_option('date_format'), $end_ts)), 'updated' => $updated]);
		}
	
		

		public static function ajax_blog_ideas(): void {
			$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
			if (!wp_verify_nonce($nonce, 'user_manager_blog_ideas')) {
				wp_send_json_error(['message' => __('Verification failed.', 'user-manager')]);
			}
			if (!current_user_can('edit_posts')) {
				wp_send_json_error(['message' => __('You do not have permission.', 'user-manager')]);
			}
			$settings = User_Manager_Core::get_settings();
			if (empty($settings['openai_blog_post_idea_generator_enabled'])) {
				wp_send_json_error(['message' => __('Post Idea Generator add-on is disabled.', 'user-manager')]);
			}
			$api_key = isset($settings['openai_api_key']) ? trim((string) $settings['openai_api_key']) : '';
			if ($api_key === '') {
				wp_send_json_error(['message' => __('OpenAI API key is not configured in Settings.', 'user-manager')]);
			}
			$idea_posts = get_posts(['post_type' => 'post', 'post_status' => ['publish', 'future'], 'orderby' => 'date', 'order' => 'DESC', 'posts_per_page' => 500, 'no_found_rows' => true]);
			$titles = array_map('get_the_title', $idea_posts);
			$topic_focus = isset($_POST['topic_focus']) ? sanitize_text_field(wp_unslash($_POST['topic_focus'])) : '';
			$user_message = __('Here are all of our blog post titles, what are some other topics and/or headlines you might recommend?', 'user-manager');
			if ($topic_focus !== '') {
				$user_message .= "\n\n" . __('Optional topic focus:', 'user-manager') . ' ' . $topic_focus;
			}
			$user_message .= "\n\n" . implode("\n", $titles);
			$prompt_append = isset($settings['openai_prompt_append']) ? trim((string) $settings['openai_prompt_append']) : '';
			if ($prompt_append !== '') {
				$user_message .= "\n\n" . $prompt_append;
			}
			$system_message = __('You are a blog content strategist. Based on the list of existing blog post titles below, suggest additional topics and headline ideas that would fit well. Respond in clear, readable format (e.g. bullet points or numbered list). Do not include JSON or code blocks.', 'user-manager');
			$response = wp_remote_post(
				'https://api.openai.com/v1/chat/completions',
				[
					'timeout' => 60,
					'headers' => [
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					],
					'body' => wp_json_encode([
						'model' => 'gpt-4o-mini',
						'messages' => [
							['role' => 'system', 'content' => $system_message],
							['role' => 'user', 'content' => $user_message],
						],
						'temperature' => 0.7,
					]),
				]
			);
			if (is_wp_error($response)) {
				wp_send_json_error(['message' => $response->get_error_message()]);
			}
			$code = wp_remote_retrieve_response_code($response);
			$body = wp_remote_retrieve_body($response);
			if ($code !== 200) {
				$decoded = json_decode($body, true);
				$err = isset($decoded['error']['message']) ? $decoded['error']['message'] : $body;
				wp_send_json_error(['message' => $err]);
			}
			$data = json_decode($body, true);
			$content = isset($data['choices'][0]['message']['content']) ? trim($data['choices'][0]['message']['content']) : '';
			if ($content === '') {
				wp_send_json_error(['message' => __('No content in API response.', 'user-manager')]);
			}
			wp_send_json_success(['content' => $content]);
		}
	
		

		public static function ajax_page_chatgpt_generate(): void {
			$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
			if (!wp_verify_nonce($nonce, 'user_manager_page_chatgpt_generate')) {
				wp_send_json_error(['message' => __('Verification failed.', 'user-manager')]);
			}
			if (!current_user_can('edit_pages')) {
				wp_send_json_error(['message' => __('You do not have permission to edit pages.', 'user-manager')]);
			}
			$settings = User_Manager_Core::get_settings();
			if (empty($settings['openai_content_generator_enabled'])) {
				wp_send_json_error(['message' => __('ChatGPT Content Generator add-on is disabled.', 'user-manager')]);
			}
			$api_key = isset($settings['openai_api_key']) ? trim((string) $settings['openai_api_key']) : '';
			if ($api_key === '') {
				wp_send_json_error(['message' => __('OpenAI API key is not configured in Settings.', 'user-manager')]);
			}
			$topic = isset($_POST['topic']) ? sanitize_textarea_field(wp_unslash($_POST['topic'])) : '';
			if ($topic === '') {
				wp_send_json_error(['message' => __('Please enter what should be written about.', 'user-manager')]);
			}
			$num_paragraphs = isset($_POST['num_paragraphs']) ? max(1, min(20, (int) $_POST['num_paragraphs'])) : 5;
			$num_sentences = isset($_POST['num_sentences']) ? max(1, min(20, (int) $_POST['num_sentences'])) : 5;
			$include_existing = !empty($_POST['include_existing_content']) && $_POST['include_existing_content'] === '1';
			$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
			$prompt_append = isset($settings['openai_prompt_append']) ? trim((string) $settings['openai_prompt_append']) : '';
			$user_message = sprintf(
				__('Write about the following topic in exactly %1$d paragraph(s), each with approximately %2$d sentence(s). Use clear, professional language. Return only the content as HTML with <p> tags for each paragraph. No titles or headings.', 'user-manager'),
				$num_paragraphs,
				$num_sentences
			) . "\n\nTopic: " . $topic;
			if ($include_existing && $post_id && get_post_type($post_id) === 'page') {
				$page = get_post($post_id);
				if ($page && $page->post_content !== '') {
					$raw_text = wp_strip_all_tags($page->post_content);
					$raw_text = preg_replace('/\s+/', ' ', trim($raw_text));
					if ($raw_text !== '') {
						$user_message .= "\n\n" . __('Existing page content (for context):', 'user-manager') . "\n" . $raw_text;
					}
				}
			}
			if ($prompt_append !== '') {
				$user_message .= "\n\n" . $prompt_append;
			}
			$system_message = __('You are a professional content writer. Respond with only the requested HTML content (paragraphs in <p> tags), no other text or explanation.', 'user-manager');
			$response = wp_remote_post(
				'https://api.openai.com/v1/chat/completions',
				[
					'timeout' => 60,
					'headers' => [
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					],
					'body' => wp_json_encode([
						'model' => 'gpt-4o-mini',
						'messages' => [
							['role' => 'system', 'content' => $system_message],
							['role' => 'user', 'content' => $user_message],
						],
						'temperature' => 0.7,
					]),
				]
			);
			if (is_wp_error($response)) {
				wp_send_json_error(['message' => $response->get_error_message()]);
			}
			$code = wp_remote_retrieve_response_code($response);
			$body = wp_remote_retrieve_body($response);
			if ($code !== 200) {
				$decoded = json_decode($body, true);
				$err = isset($decoded['error']['message']) ? $decoded['error']['message'] : $body;
				wp_send_json_error(['message' => $err]);
			}
			$data = json_decode($body, true);
			$content = isset($data['choices'][0]['message']['content']) ? trim($data['choices'][0]['message']['content']) : '';
			$content = preg_replace('/^```\w*\s*|\s*```$/','', $content);
			if ($content === '') {
				wp_send_json_error(['message' => __('No content in API response.', 'user-manager')]);
			}
			$preview = wp_kses_post($content);
			wp_send_json_success(['content' => $content, 'preview' => $preview]);
		}
	
		

		public static function ajax_page_chatgpt_insert(): void {
			$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
			if (!wp_verify_nonce($nonce, 'user_manager_page_chatgpt_insert')) {
				wp_send_json_error(['message' => __('Verification failed.', 'user-manager')]);
			}
			$settings = User_Manager_Core::get_settings();
			if (empty($settings['openai_content_generator_enabled'])) {
				wp_send_json_error(['message' => __('ChatGPT Content Generator add-on is disabled.', 'user-manager')]);
			}
			$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
			$content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';
			if (!$post_id) {
				wp_send_json_error(['message' => __('Invalid post.', 'user-manager')]);
			}
			$post = get_post($post_id);
			if (!$post) {
				wp_send_json_error(['message' => __('Post not found.', 'user-manager')]);
			}
			$post_type = get_post_type($post_id);
			if (!in_array($post_type, ['page', 'post'], true)) {
				wp_send_json_error(['message' => __('Invalid post.', 'user-manager')]);
			}
			if (!current_user_can('edit_post', $post_id)) {
				wp_send_json_error(['message' => __('You do not have permission to edit this post.', 'user-manager')]);
			}
			if ($content === '') {
				wp_send_json_error(['message' => __('No content to insert.', 'user-manager')]);
			}
			$position = isset($_POST['insert_position']) ? sanitize_key($_POST['insert_position']) : 'bottom';
			if (!in_array($position, ['bottom', 'top', 'replace'], true)) {
				$position = 'bottom';
			}
			$existing = $post->post_content ?: '';
			$new_blocks = self::html_to_paragraph_blocks($content);
			$sep = (strlen($existing) > 0 && substr($existing, -1) !== "\n" ? "\n\n" : '');
			$entity = ($post_type === 'page') ? __('page', 'user-manager') : __('post', 'user-manager');
			if ($position === 'bottom') {
				$new_content = $existing . $sep . $new_blocks;
				$message = sprintf(__('Content inserted at the bottom of the %s. You can save to keep changes.', 'user-manager'), $entity);
			} elseif ($position === 'top') {
				$new_content = $new_blocks . $sep . $existing;
				$message = sprintf(__('Content inserted at the top of the %s. You can save to keep changes.', 'user-manager'), $entity);
			} else {
				$new_content = $new_blocks;
				$message = sprintf(__('%s content replaced. You can save to keep changes.', 'user-manager'), ucfirst($entity));
			}
			wp_update_post([
				'ID'           => $post_id,
				'post_content' => $new_content,
			]);
			$log_admin_activity = isset($settings['log_admin_activity']) ? !empty($settings['log_admin_activity']) : true;
			if ($log_admin_activity) {
				User_Manager_Core::add_activity_log('page_chatgpt_content_inserted', get_current_user_id(), 'ChatGPT Page Meta Box', [
					'page_id'       => $post_id,
					'page_title'    => get_the_title($post_id),
					'topic_preview' => mb_substr(wp_strip_all_tags($content), 0, 100),
					'insert_position' => $position,
				]);
			}
			wp_send_json_success(['message' => $message, 'reload' => true]);
		}
	
		

}
