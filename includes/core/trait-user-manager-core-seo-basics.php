<?php
/**
 * SEO Basics add-on helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_SEO_Basics_Trait {

	/**
	 * Register runtime hooks for SEO Basics add-on.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_seo_basics(array $settings): void {
		if (empty($settings['seo_basics_enabled']) || self::is_addon_temporarily_disabled('seo-basics')) {
			return;
		}

		add_action('add_meta_boxes', [__CLASS__, 'add_seo_basics_meta_box']);
		add_action('save_post', [__CLASS__, 'save_seo_basics_meta_box'], 10, 2);
		add_filter('document_title_parts', [__CLASS__, 'filter_seo_basics_document_title_parts'], 30);
		add_filter('pre_get_document_title', [__CLASS__, 'filter_seo_basics_document_title'], 30);
		add_action('wp_head', [__CLASS__, 'render_seo_basics_meta_tags'], 3);
	}

	/**
	 * Register SEO Basics meta boxes on pages/posts.
	 */
	public static function add_seo_basics_meta_box(): void {
		foreach (['page', 'post'] as $post_type) {
			add_meta_box(
				'um_seo_basics',
				__('SEO Basics', 'user-manager'),
				[__CLASS__, 'render_seo_basics_meta_box'],
				$post_type,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Render SEO Basics post meta box fields.
	 */
	public static function render_seo_basics_meta_box($post): void {
		if (!($post instanceof WP_Post)) {
			return;
		}
		$title_override = (string) get_post_meta($post->ID, '_um_seo_basics_title_override', true);
		$description_override = (string) get_post_meta($post->ID, '_um_seo_basics_description_override', true);
		$image_url = self::get_seo_basics_meta_image_url((int) $post->ID);

		wp_nonce_field('um_save_seo_basics_meta', 'um_seo_basics_meta_nonce');
		?>
		<div class="um-seo-basics-meta-box">
			<p>
				<label for="um-seo-basics-title-override"><strong><?php esc_html_e('Page Title Override', 'user-manager'); ?></strong></label><br />
				<input
					type="text"
					id="um-seo-basics-title-override"
					name="um_seo_basics_title_override"
					class="widefat"
					value="<?php echo esc_attr($title_override); ?>"
					placeholder="<?php esc_attr_e('Optional custom <title> text', 'user-manager'); ?>"
				/>
			</p>
			<p>
				<label for="um-seo-basics-description-override"><strong><?php esc_html_e('Page Description Override', 'user-manager'); ?></strong></label><br />
				<textarea
					id="um-seo-basics-description-override"
					name="um_seo_basics_description_override"
					class="widefat"
					rows="4"
					placeholder="<?php esc_attr_e('Optional meta description text', 'user-manager'); ?>"
				><?php echo esc_textarea($description_override); ?></textarea>
			</p>
			<p class="description" style="margin-bottom:0;">
				<?php esc_html_e('Meta Image: uses Featured Image first. If none is set, falls back to the first image found in page/post content.', 'user-manager'); ?>
			</p>
			<?php if ($image_url !== '') : ?>
				<p class="description" style="margin-top:6px;">
					<?php
					printf(
						/* translators: %s: resolved image URL */
						esc_html__('Current resolved image: %s', 'user-manager'),
						esc_url($image_url)
					);
					?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Save SEO Basics post meta box fields.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save_seo_basics_meta_box(int $post_id, $post): void {
		if (!($post instanceof WP_Post)) {
			return;
		}
		if (!in_array($post->post_type, ['post', 'page'], true)) {
			return;
		}
		if (!isset($_POST['um_seo_basics_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['um_seo_basics_meta_nonce'])), 'um_save_seo_basics_meta')) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
			return;
		}

		$title_override = isset($_POST['um_seo_basics_title_override'])
			? sanitize_text_field(wp_unslash($_POST['um_seo_basics_title_override']))
			: '';
		$description_override = isset($_POST['um_seo_basics_description_override'])
			? sanitize_textarea_field(wp_unslash($_POST['um_seo_basics_description_override']))
			: '';

		if ($title_override === '') {
			delete_post_meta($post_id, '_um_seo_basics_title_override');
		} else {
			update_post_meta($post_id, '_um_seo_basics_title_override', $title_override);
		}

		if ($description_override === '') {
			delete_post_meta($post_id, '_um_seo_basics_description_override');
		} else {
			update_post_meta($post_id, '_um_seo_basics_description_override', $description_override);
		}
	}

	/**
	 * Apply title override to document title parts.
	 *
	 * @param array<string,string> $parts Title parts.
	 * @return array<string,string>
	 */
	public static function filter_seo_basics_document_title_parts(array $parts): array {
		$title_override = self::get_current_seo_basics_title_override();
		if ($title_override === '') {
			return $parts;
		}
		$parts['title'] = $title_override;
		return $parts;
	}

	/**
	 * Apply title override to pre-built document title string.
	 *
	 * @param mixed $title Existing title.
	 */
	public static function filter_seo_basics_document_title($title): string {
		$resolved_title = is_string($title) ? $title : '';
		$title_override = self::get_current_seo_basics_title_override();
		return $title_override !== '' ? $title_override : $resolved_title;
	}

	/**
	 * Print frontend SEO meta tags.
	 */
	public static function render_seo_basics_meta_tags(): void {
		if (is_admin() || !is_singular(['post', 'page'])) {
			return;
		}
		$post_id = self::get_current_seo_basics_post_id();
		if ($post_id <= 0) {
			return;
		}

		$title_override = trim((string) get_post_meta($post_id, '_um_seo_basics_title_override', true));
		$description_override = trim((string) get_post_meta($post_id, '_um_seo_basics_description_override', true));
		$image_url = self::get_seo_basics_meta_image_url($post_id);

		if ($title_override === '' && $description_override === '' && $image_url === '') {
			return;
		}

		if ($title_override !== '') {
			echo '<meta property="og:title" content="' . esc_attr($title_override) . '" />' . "\n";
			echo '<meta name="twitter:title" content="' . esc_attr($title_override) . '" />' . "\n";
		}
		if ($description_override !== '') {
			echo '<meta name="description" content="' . esc_attr($description_override) . '" />' . "\n";
			echo '<meta property="og:description" content="' . esc_attr($description_override) . '" />' . "\n";
			echo '<meta name="twitter:description" content="' . esc_attr($description_override) . '" />' . "\n";
		}
		if ($image_url !== '') {
			echo '<meta property="og:image" content="' . esc_url($image_url) . '" />' . "\n";
			echo '<meta name="twitter:image" content="' . esc_url($image_url) . '" />' . "\n";
		}
	}

	/**
	 * Resolve current singular post ID for SEO Basics.
	 */
	private static function get_current_seo_basics_post_id(): int {
		$post_id = get_queried_object_id();
		if ($post_id > 0) {
			return (int) $post_id;
		}
		$post = get_post();
		return $post instanceof WP_Post ? (int) $post->ID : 0;
	}

	/**
	 * Resolve current title override value for singular post/page.
	 */
	private static function get_current_seo_basics_title_override(): string {
		if (is_admin() || !is_singular(['post', 'page'])) {
			return '';
		}
		$post_id = self::get_current_seo_basics_post_id();
		if ($post_id <= 0) {
			return '';
		}
		return trim((string) get_post_meta($post_id, '_um_seo_basics_title_override', true));
	}

	/**
	 * Resolve meta image URL: featured image first, first content image second.
	 */
	private static function get_seo_basics_meta_image_url(int $post_id): string {
		if ($post_id <= 0) {
			return '';
		}

		if (has_post_thumbnail($post_id)) {
			$thumbnail_url = wp_get_attachment_image_url((int) get_post_thumbnail_id($post_id), 'full');
			if (is_string($thumbnail_url) && $thumbnail_url !== '') {
				return $thumbnail_url;
			}
		}

		$post = get_post($post_id);
		if (!($post instanceof WP_Post)) {
			return '';
		}
		$content = (string) $post->post_content;
		if ($content === '') {
			return '';
		}

		if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $content, $matches) && !empty($matches[1])) {
			$image_url = esc_url_raw((string) $matches[1]);
			if ($image_url !== '') {
				return $image_url;
			}
		}

		if (preg_match('/wp-image-([0-9]+)/', $content, $id_matches) && !empty($id_matches[1])) {
			$image_url = wp_get_attachment_image_url((int) $id_matches[1], 'full');
			if (is_string($image_url) && $image_url !== '') {
				return $image_url;
			}
		}

		return '';
	}
}

