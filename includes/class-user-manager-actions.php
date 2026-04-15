<?php
/**
 * User Manager Action Handlers
 */

if (!defined('ABSPATH')) {
	exit;
}


require_once __DIR__ . '/actions/trait-user-manager-actions-content-generator.php';
require_once __DIR__ . '/actions/trait-user-manager-actions-bulk-page-creator.php';
require_once __DIR__ . '/actions/trait-user-manager-actions-data-anonymizer.php';
require_once __DIR__ . '/class-user-manager-sms.php';
class User_Manager_Actions {
	use User_Manager_Actions_Content_Generator_Trait;
	use User_Manager_Actions_Bulk_Page_Creator_Trait;
	use User_Manager_Actions_Data_Anonymizer_Trait;

	/**
	 * Initialize action hooks.
	 */
	public static function init(): void {
		add_action('admin_post_user_manager_create_user', [__CLASS__, 'handle_create_user']);
		add_action('admin_post_user_manager_reset_password', [__CLASS__, 'handle_reset_password']);
		add_action('admin_post_user_manager_remove_user', [__CLASS__, 'handle_remove_user']);
		add_action('admin_post_user_manager_deactivate_user', [__CLASS__, 'handle_deactivate_user']);
		add_action('admin_post_user_manager_reactivate_user', [__CLASS__, 'handle_reactivate_user']);
		add_action('admin_post_user_manager_bulk_create', [__CLASS__, 'handle_bulk_create']);
		add_action('admin_post_user_manager_bulk_coupons', [__CLASS__, 'handle_bulk_coupons']);
		add_action('admin_post_user_manager_save_template', [__CLASS__, 'handle_save_template']);
		add_action('admin_post_user_manager_delete_template', [__CLASS__, 'handle_delete_template']);
		add_action('admin_post_user_manager_duplicate_template', [__CLASS__, 'handle_duplicate_template']);
		add_action('admin_post_user_manager_save_sms_text_template', [__CLASS__, 'handle_save_sms_text_template']);
		add_action('admin_post_user_manager_delete_sms_text_template', [__CLASS__, 'handle_delete_sms_text_template']);
		add_action('admin_post_user_manager_duplicate_sms_text_template', [__CLASS__, 'handle_duplicate_sms_text_template']);
		add_action('admin_post_user_manager_move_sms_text_template', [__CLASS__, 'handle_move_sms_text_template']);
		add_action('admin_post_user_manager_save_settings', [__CLASS__, 'handle_save_settings']);
		add_action('admin_post_user_manager_import_sftp_file', [__CLASS__, 'handle_import_sftp_file']);
		add_action('admin_post_user_manager_email_users', [__CLASS__, 'handle_email_users']);
		add_action('admin_post_user_manager_email_users_next_batch', [__CLASS__, 'handle_email_users_next_batch']);
		add_action('admin_post_user_manager_send_sms_texts', [__CLASS__, 'handle_send_sms_texts']);
		add_action('admin_post_user_manager_send_sms_texts_next_batch', [__CLASS__, 'handle_send_sms_texts_next_batch']);
		add_action('admin_post_user_manager_save_email_list', [__CLASS__, 'handle_save_email_list']);
		add_action('admin_post_user_manager_delete_email_list', [__CLASS__, 'handle_delete_email_list']);
		add_action('admin_post_user_manager_download_email_list_csv', [__CLASS__, 'handle_download_email_list_csv']);
		add_action('admin_post_user_manager_create_directory', [__CLASS__, 'handle_create_directory']);
		add_action('admin_post_user_manager_download_sample_csv', [__CLASS__, 'handle_download_sample_csv']);
		add_action('admin_post_user_manager_import_demo_templates', [__CLASS__, 'handle_import_demo_templates']);
		add_action('admin_post_user_manager_recreate_demo_template', [__CLASS__, 'handle_recreate_demo_template']);
		add_action('admin_post_user_manager_import_demo_sms_text_templates', [__CLASS__, 'handle_import_demo_sms_text_templates']);
		add_action('admin_post_user_manager_clear_activity_log', [__CLASS__, 'handle_clear_activity_log']);
		add_action('admin_post_user_manager_clear_user_activity_log', [__CLASS__, 'handle_clear_user_activity_log']);
		add_action('admin_post_user_manager_emali_log_resend', [__CLASS__, 'handle_emali_log_resend']);
		add_action('admin_post_user_manager_emali_log_forward', [__CLASS__, 'handle_emali_log_forward']);
		add_action('admin_post_user_manager_emali_log_clear', [__CLASS__, 'handle_emali_log_clear']);
		add_action('admin_post_user_manager_move_template', [__CLASS__, 'handle_move_template']);
		add_action('admin_post_user_manager_import_coupon_template', [__CLASS__, 'handle_import_coupon_template']);
		add_action('admin_post_user_manager_migrate_store_credit_coupons', [__CLASS__, 'handle_migrate_store_credit_coupons']);
		add_action('admin_post_user_manager_create_basic_coupon_template', [__CLASS__, 'handle_create_basic_coupon_template']);
		add_action('admin_post_user_manager_reset_view_reports', [__CLASS__, 'handle_reset_view_reports']);
		add_action('admin_post_user_manager_reset_text_file_line_count_cache', [__CLASS__, 'handle_reset_text_file_line_count_cache']);
		add_action('admin_post_user_manager_blog_post_importer', [__CLASS__, 'handle_blog_post_importer']);
		add_action('admin_post_user_manager_bulk_page_creator', [__CLASS__, 'handle_bulk_page_creator']);
		add_action('admin_post_user_manager_run_data_anonymizer_orders', [__CLASS__, 'handle_run_data_anonymizer_orders']);
		add_action('admin_post_user_manager_run_data_anonymizer_users', [__CLASS__, 'handle_run_data_anonymizer_users']);
		add_action('admin_post_user_manager_run_data_anonymizer_forms', [__CLASS__, 'handle_run_data_anonymizer_forms']);
		add_action('wp_ajax_user_manager_blog_chatgpt', [__CLASS__, 'ajax_blog_chatgpt']);
		add_action('wp_ajax_user_manager_set_post_thumbnail', [__CLASS__, 'ajax_set_post_thumbnail']);
		add_action('wp_ajax_user_manager_set_post_date', [__CLASS__, 'ajax_set_post_date']);
		add_action('wp_ajax_user_manager_spread_scheduled_posts', [__CLASS__, 'ajax_spread_scheduled_posts']);
		add_action('wp_ajax_user_manager_blog_ideas', [__CLASS__, 'ajax_blog_ideas']);
		add_action('add_meta_boxes', [__CLASS__, 'register_page_chatgpt_meta_box']);
		add_action('wp_ajax_user_manager_page_chatgpt_generate', [__CLASS__, 'ajax_page_chatgpt_generate']);
		add_action('wp_ajax_user_manager_page_chatgpt_insert', [__CLASS__, 'ajax_page_chatgpt_insert']);
	}

	/**
	 * Reset all view-related / traffic report entries.
	 */
	public static function handle_reset_view_reports(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_reset_view_reports');

		global $wpdb;
		$table = $wpdb->prefix . 'um_admin_activity';

		// Actions used by view/traffic reports (404s, search, and future view reports).
		$actions = [
			'404_hit',
			'search_query',
			'page_view',
			'page_category_view',
			'post_view',
			'post_category_view',
			'post_tag_view',
			'product_view',
			'product_category_view',
			'product_tag_view',
		];

		$placeholders = implode(',', array_fill(0, count($actions), '%s'));
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE action IN ($placeholders)",
				$actions
			)
		);

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_TOOLS, 'view_reports_reset'));
		exit;
	}

	/**
	 * Reset cached per-order text-file line counts used by My Account Admin meta fields.
	 */
	public static function handle_reset_text_file_line_count_cache(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_save_settings');

		$deleted_count = 0;
		$message_key = 'text_file_line_count_cache_reset';
		if (!class_exists('User_Manager_My_Account_Site_Admin') || !method_exists('User_Manager_My_Account_Site_Admin', 'reset_all_cached_text_file_line_counts')) {
			$message_key = 'text_file_line_count_cache_reset_error';
		} else {
			$deleted_count = max(0, (int) User_Manager_My_Account_Site_Admin::reset_all_cached_text_file_line_counts());
		}

		$redirect_extra = [
			'addon_section' => 'my-account-site-admin',
			'cache_reset_count' => $deleted_count,
		];
		if (isset($_POST['addon_tag'])) {
			$addon_tag = sanitize_title(wp_unslash($_POST['addon_tag']));
			if ($addon_tag !== '') {
				$redirect_extra['addon_tag'] = $addon_tag;
			}
		}

		wp_safe_redirect(
			User_Manager_Core::get_redirect_with_message(
				User_Manager_Core::TAB_ADDONS,
				$message_key,
				$redirect_extra
			)
		);
		exit;
	}

	/**
	 * Handle bulk coupon generation.
	 */
	public static function handle_bulk_coupons(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_bulk_coupons', 'user_manager_bulk_coupons_nonce');
		$redirect_tab = User_Manager_Core::TAB_ADDONS;

		$settings = User_Manager_Core::get_settings();
		if (empty($settings['bulk_coupons_enabled'])) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'bulk_coupons_disabled'));
			exit;
		}

		// Ensure WooCommerce coupon API is available.
		if (!class_exists('WC_Coupon')) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'error'));
			exit;
		}

		$template_code        = isset($_POST['bulk_coupons_template_code']) ? sanitize_text_field(wp_unslash($_POST['bulk_coupons_template_code'])) : '';
		$total_raw            = isset($_POST['bulk_coupons_total']) ? wp_unslash($_POST['bulk_coupons_total']) : '';
		$emails_raw           = isset($_POST['bulk_coupons_emails']) ? sanitize_textarea_field(wp_unslash($_POST['bulk_coupons_emails'])) : '';
		$amount_raw           = isset($_POST['bulk_coupons_amount']) ? wp_unslash($_POST['bulk_coupons_amount']) : '';
		$prefix               = isset($_POST['bulk_coupons_prefix']) ? sanitize_text_field(wp_unslash($_POST['bulk_coupons_prefix'])) : '';
		$suffix               = isset($_POST['bulk_coupons_suffix']) ? sanitize_text_field(wp_unslash($_POST['bulk_coupons_suffix'])) : '';
		$length_raw           = isset($_POST['bulk_coupons_length']) ? wp_unslash($_POST['bulk_coupons_length']) : '';
		$expiration_date_raw  = isset($_POST['bulk_coupons_expiration_date']) ? sanitize_text_field(wp_unslash($_POST['bulk_coupons_expiration_date'])) : '';
		$expiration_days_raw  = isset($_POST['bulk_coupons_expiration_days']) ? wp_unslash($_POST['bulk_coupons_expiration_days']) : '';
		$send_email_raw       = isset($_POST['send_email']) && $_POST['send_email'] === '1';
		$template_id_raw      = isset($_POST['email_template']) ? sanitize_key($_POST['email_template']) : '';

		$total_to_create   = is_numeric($total_raw) ? max(0, (int) $total_raw) : 0;
		$code_length       = is_numeric($length_raw) ? (int) $length_raw : 8;
		$expiration_days   = is_numeric($expiration_days_raw) ? (int) $expiration_days_raw : 0;
		if ($expiration_days < 0) {
			$expiration_days = 0;
		}
		if ($code_length <= 0) {
			$code_length = 8;
		}

		// Normalize amount override via WooCommerce helper if available.
		$amount_override = '';
		if ($amount_raw !== '') {
			if (function_exists('wc_format_decimal')) {
				$amount_override = (string) wc_format_decimal($amount_raw);
			} else {
				$amount_override = sanitize_text_field($amount_raw);
			}
		}

		// Persist last-used form values into settings.
		$settings['bulk_coupons_template_code']      = $template_code;
		$settings['bulk_coupons_total']              = $total_to_create;
		$settings['bulk_coupons_emails']             = $emails_raw;
		$settings['bulk_coupons_amount']             = $amount_override;
		$settings['bulk_coupons_prefix']             = $prefix;
		$settings['bulk_coupons_suffix']             = $suffix;
		$settings['bulk_coupons_length']             = $code_length;
		$settings['bulk_coupons_expiration_date']    = $expiration_date_raw;
		$settings['bulk_coupons_expiration_days']    = $expiration_days;
		$settings['bulk_coupons_send_email']         = (bool) $send_email_raw;
		$settings['bulk_coupons_email_template']     = $template_id_raw;
		update_option(User_Manager_Core::OPTION_KEY, $settings);

		if (empty($template_code)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'error'));
			exit;
		}

		$template_coupon = new WC_Coupon($template_code);
		if (!$template_coupon || !$template_coupon->get_id()) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'error'));
			exit;
		}

		// Parse email list (one per line, also tolerate commas/semicolons).
		$emails = [];
		if (!empty($emails_raw)) {
			$normalized = str_replace(["\r\n", "\r", ';', ','], "\n", $emails_raw);
			$lines = array_filter(array_map('trim', explode("\n", $normalized)));
			foreach ($lines as $line) {
				$email = sanitize_email($line);
				if ($email && is_email($email)) {
					$emails[] = $email;
				}
			}
			$emails = array_values(array_unique($emails));
		}

		$use_email_mode = !empty($emails);
		if ($use_email_mode) {
			$total_to_create = count($emails);
		}

		if ($total_to_create <= 0) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'error'));
			exit;
		}

		$created          = 0;
		$created_coupons  = [];
		$log_settings     = User_Manager_Core::get_settings();
		$log_enabled      = $log_settings['log_activity'] ?? true;
		$send_email       = (bool) $send_email_raw && $template_id_raw !== '' && $use_email_mode;
		$template_id      = $template_id_raw;

		for ($i = 0; $i < $total_to_create; $i++) {
			$assigned_email = $use_email_mode && isset($emails[$i]) ? $emails[$i] : '';

			// Generate unique coupon code.
			$attempts = 0;
			do {
				$code = User_Manager_Core::build_random_code($code_length, $prefix, $suffix);
				$existing_id = function_exists('wc_get_coupon_id_by_code') ? wc_get_coupon_id_by_code($code) : 0;
				$attempts++;
			} while ($existing_id && $attempts < 5);

			if (!empty($existing_id)) {
				continue;
			}

			$new_coupon_id = wp_insert_post([
				'post_title'   => $code,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id() ?: 1,
				'post_type'    => 'shop_coupon',
			]);

			if (is_wp_error($new_coupon_id) || !$new_coupon_id) {
				continue;
			}

			$new_coupon = new WC_Coupon($new_coupon_id);

			// Clone core settings from template.
			$new_coupon->set_discount_type($template_coupon->get_discount_type());
			$new_coupon->set_amount($template_coupon->get_amount());
			$new_coupon->set_individual_use($template_coupon->get_individual_use('edit'));
			$new_coupon->set_product_ids($template_coupon->get_product_ids('edit'));
			$new_coupon->set_excluded_product_ids($template_coupon->get_excluded_product_ids('edit'));
			$new_coupon->set_usage_limit($template_coupon->get_usage_limit('edit'));
			$new_coupon->set_usage_limit_per_user($template_coupon->get_usage_limit_per_user('edit'));
			$new_coupon->set_limit_usage_to_x_items($template_coupon->get_limit_usage_to_x_items('edit'));
			$new_coupon->set_free_shipping($template_coupon->get_free_shipping('edit'));
			$new_coupon->set_date_expires($template_coupon->get_date_expires('edit'));
			$new_coupon->set_minimum_amount($template_coupon->get_minimum_amount('edit'));
			$new_coupon->set_maximum_amount($template_coupon->get_maximum_amount('edit'));
			$new_coupon->set_product_categories($template_coupon->get_product_categories('edit'));
			$new_coupon->set_excluded_product_categories($template_coupon->get_excluded_product_categories('edit'));
			$new_coupon->set_virtual($template_coupon->get_virtual('edit'));

			// Apply expiration overrides, with "days from today" taking precedence.
			if ($expiration_days > 0 || $expiration_date_raw !== '') {
				$expires_timestamp = null;

				if ($expiration_days > 0) {
					$expires_timestamp = current_time('timestamp') + (DAY_IN_SECONDS * $expiration_days);
				} else {
					$parsed = strtotime($expiration_date_raw . ' 23:59:59');
					if ($parsed) {
						$expires_timestamp = $parsed;
					}
				}

				if ($expires_timestamp) {
					$new_coupon->set_date_expires($expires_timestamp);
				}
			}

			if ($amount_override !== '') {
				$new_coupon->set_amount($amount_override);
			}

			// Assign email restriction if provided.
			if ($assigned_email) {
				if (method_exists($new_coupon, 'set_email_restrictions')) {
					$new_coupon->set_email_restrictions([$assigned_email]);
				} else {
					update_post_meta($new_coupon_id, 'customer_email', [$assigned_email]);
				}
				update_post_meta($new_coupon_id, '_um_user_coupon_user_email', $assigned_email);
			}

			$new_coupon->save();

			$email_sent = false;

			// Optionally send coupon email when email list mode is used.
			if ($send_email && $assigned_email) {
				$user_for_email = get_user_by('email', $assigned_email);

				if ($user_for_email instanceof WP_User) {
					User_Manager_Core::send_coupon_email_to_user($user_for_email, $code, $template_id);
				} else {
					User_Manager_Core::send_coupon_email_to_address($assigned_email, $code, $template_id);
				}

				$email_sent = true;

				if ($log_enabled) {
					User_Manager_Core::add_activity_log('coupon_email_sent', $user_for_email instanceof WP_User ? $user_for_email->ID : 0, 'Bulk Coupons', [
						'coupon_id'     => $new_coupon_id,
						'coupon_code'   => $code,
						'email'         => $assigned_email,
						'template_id'   => $template_id,
					]);
				}
			}

			$created++;
			$created_coupons[] = [
				'code'  => $code,
				'link'  => get_edit_post_link($new_coupon_id, ''),
				'email' => $assigned_email,
			];

			// Log each coupon into the admin activity log.
			if ($log_enabled) {
				$user_id = 0;
				if ($assigned_email) {
					$user = get_user_by('email', $assigned_email);
					if ($user) {
						$user_id = $user->ID;
					}
				}

				User_Manager_Core::add_activity_log('coupon_created', $user_id, 'Bulk Coupons', [
					'coupon_id'      => $new_coupon_id,
					'coupon_code'    => $code,
					'coupon_link'    => get_edit_post_link($new_coupon_id, ''),
					'template_code'  => $template_code,
					'amount'         => $new_coupon->get_amount(),
					'assigned_email' => $assigned_email,
					'email_sent'     => $email_sent,
					'email_template' => $template_id,
				]);
			}
		}

		// Log summary entry.
		if ($log_enabled) {
			User_Manager_Core::add_activity_log('coupon_bulk_summary', 0, 'Bulk Coupons Summary', [
				'created_count'   => $created,
				'template_code'   => $template_code,
				'emails_mode'     => $use_email_mode,
				'emails_count'    => count($emails),
				'amount_override' => $amount_override,
				'expiration_date' => $expiration_date_raw,
				'expiration_days' => $expiration_days,
			]);
		}

		if ($created <= 0) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'error'));
			exit;
		}

		$redirect = add_query_arg(
			'count',
			$created,
			User_Manager_Core::get_redirect_with_message($redirect_tab, 'bulk_coupons_created')
		);

		if (!empty($created_coupons)) {
			$token = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('bulk_coupons_', true);
			set_transient(
				'um_bulk_coupons_' . $token,
				[
					'coupons' => $created_coupons,
				],
				HOUR_IN_SECONDS
			);
			$redirect = add_query_arg('bulk_ref', rawurlencode($token), $redirect);
		}

		wp_safe_redirect($redirect);
		exit;
	}

	/**
	 * Handle single user creation.
	 */
	public static function handle_create_user(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_create_user');

		$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
		$username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
		$first_name = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
		$last_name = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
		$password = isset($_POST['password']) ? wp_unslash($_POST['password']) : '';
		$role = isset($_POST['role']) ? sanitize_key($_POST['role']) : 'customer';
		$login_url = isset($_POST['login_url']) ? sanitize_text_field(wp_unslash($_POST['login_url'])) : '/my-account/';
		$coupon_code = isset($_POST['coupon_code_for_template']) ? sanitize_text_field(wp_unslash($_POST['coupon_code_for_template'])) : '';
		$send_email = isset($_POST['send_email']) && $_POST['send_email'] === '1';
		$template_id = isset($_POST['email_template']) ? sanitize_key($_POST['email_template']) : '';
		$allow_email_coupon_code = isset($_POST['allow_email_coupon_code']) ? sanitize_text_field(wp_unslash($_POST['allow_email_coupon_code'])) : '';

		if (empty($email) || !is_email($email)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_CREATE_USER, 'error'));
			exit;
		}

		// Check if user exists
		$existing_user = get_user_by('email', $email);
		$settings = User_Manager_Core::get_settings();
		
		if ($existing_user) {
			$added_to_subsite = self::maybe_add_existing_network_user_to_current_site((int) $existing_user->ID, $role, $settings);

			if (!empty($settings['update_existing_users'])) {
				// Capture old values before update
				$old_values = [
					'first_name' => $existing_user->first_name,
					'last_name' => $existing_user->last_name,
					'role' => implode(', ', $existing_user->roles),
				];
				
				// Update existing user
				$update_data = [
					'ID' => $existing_user->ID,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'role' => $role,
				];
				
				if (!empty($password)) {
					$update_data['user_pass'] = $password;
				} else {
					$password = wp_generate_password(12, true, false);
					$update_data['user_pass'] = $password;
				}
				
				wp_update_user($update_data);
				
				$log_debug = User_Manager_Core::add_activity_log('user_updated', $existing_user->ID, 'Create User (Updated)', [
					'email_sent' => $send_email,
					'template_id' => $template_id,
					'login_url' => $login_url,
					'password_changed' => true,
					'added_to_subsite' => $added_to_subsite,
					'old_values' => $old_values,
					'new_values' => [
						'first_name' => $first_name,
						'last_name' => $last_name,
						'role' => $role,
					],
				]);
				
				// Send email if requested
				if ($send_email) {
					User_Manager_Email::send_user_email($existing_user->ID, $password, $login_url, $template_id, $coupon_code);
					$url = User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_CREATE_USER, 'user_updated_email_sent');
					$url = User_Manager_Core::maybe_add_log_debug_to_url($url, $log_debug);
					wp_safe_redirect($url);
				} else {
					$url = User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_CREATE_USER, 'user_updated');
					$url = User_Manager_Core::maybe_add_log_debug_to_url($url, $log_debug);
					wp_safe_redirect($url);
				}
				exit;
			} else {
				if ($added_to_subsite) {
					$log_debug = User_Manager_Core::add_activity_log('user_added_to_subsite', $existing_user->ID, 'Create User (Added to Site)', [
						'email' => $email,
						'role' => $role,
						'blog_id' => get_current_blog_id(),
						'multisite' => is_multisite(),
						'update_existing_users' => false,
					]);
					$url = User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_CREATE_USER, 'user_added_to_subsite');
					$url = User_Manager_Core::maybe_add_log_debug_to_url($url, $log_debug);
					wp_safe_redirect($url);
					exit;
				}
				wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_CREATE_USER, 'user_exists'));
				exit;
			}
		}

		// Generate username from email if not provided
		if (empty($username)) {
			$username = $email;
		}

		// Ensure unique username
		if (username_exists($username)) {
			$username = $email;
			if (username_exists($username)) {
				$username = $email . '_' . wp_rand(100, 999);
			}
		}

		// Generate password if not provided
		$generated_password = empty($password);
		if ($generated_password) {
			$password = wp_generate_password(12, true, false);
		}

		// Create user
		$user_id = wp_insert_user([
			'user_login' => $username,
			'user_email' => $email,
			'user_pass' => $password,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'role' => $role,
		]);

		if (is_wp_error($user_id)) {
			$log_debug = User_Manager_Core::add_activity_log('user_create_failed', 0, 'Create User', [
				'error' => $user_id->get_error_message(),
				'attempted_email' => $email,
				'attempted_username' => $username,
			]);
			$url = User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_CREATE_USER, 'error');
			$url = User_Manager_Core::maybe_add_log_debug_to_url($url, $log_debug);
			wp_safe_redirect($url);
			exit;
		}

		// Optionally append this user email to a coupon's Allowed Emails.
		if ($allow_email_coupon_code !== '' && class_exists('WC_Coupon')) {
			$coupon_id = function_exists('wc_get_coupon_id_by_code') ? wc_get_coupon_id_by_code($allow_email_coupon_code) : 0;
			if ($coupon_id) {
				$coupon = new WC_Coupon($coupon_id);
				if ($coupon && $coupon->get_id()) {
					if (method_exists($coupon, 'get_email_restrictions') && method_exists($coupon, 'set_email_restrictions')) {
						$existing = $coupon->get_email_restrictions('edit');
						if (!is_array($existing)) {
							$existing = [];
						}
						$existing[] = $email;
						$existing = array_values(array_unique(array_filter(array_map('trim', $existing))));
						$coupon->set_email_restrictions($existing);
					} else {
						$existing = get_post_meta($coupon_id, 'customer_email', true);
						$list     = [];
						if (is_array($existing)) {
							$list = $existing;
						} elseif (is_string($existing) && $existing !== '') {
							$list = array_map('trim', explode(',', $existing));
						}
						$list[] = $email;
						$list   = array_values(array_unique(array_filter($list)));
						update_post_meta($coupon_id, 'customer_email', $list);
					}
					$coupon->save();
				}
			}
		}

		// Log activity
		$log_debug = User_Manager_Core::add_activity_log('user_created', $user_id, 'Create User', [
			'email_sent' => $send_email,
			'template_id' => $template_id,
			'login_url' => $login_url,
			'new_values' => [
				'email' => $email,
				'username' => $username,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'role' => $role,
				'password_generated' => $generated_password,
			],
		]);

		// Send email if requested
		if ($send_email) {
			User_Manager_Email::send_user_email($user_id, $password, $login_url, $template_id, $coupon_code);
			$url = User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_CREATE_USER, 'user_created_email_sent');
			$url = User_Manager_Core::maybe_add_log_debug_to_url($url, $log_debug);
			wp_safe_redirect($url);
		} else {
			$url = User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_CREATE_USER, 'user_created');
			$url = User_Manager_Core::maybe_add_log_debug_to_url($url, $log_debug);
			wp_safe_redirect($url);
		}
		exit;
	}

	/**
	 * Handle password reset.
	 */
	public static function handle_reset_password(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_reset_password');

		$emails_raw = isset($_POST['emails']) ? sanitize_textarea_field(wp_unslash($_POST['emails'])) : '';
		$password = isset($_POST['password']) ? wp_unslash($_POST['password']) : '';
		$send_email = isset($_POST['send_email']) && $_POST['send_email'] === '1';
		$template_id = isset($_POST['email_template']) ? sanitize_key($_POST['email_template']) : '';

		// Parse emails (one per line) - handle both \r\n and \n line endings
		$emails_raw = str_replace("\r\n", "\n", $emails_raw);
		$emails_raw = str_replace("\r", "\n", $emails_raw);
		$emails = array_filter(array_map('trim', explode("\n", $emails_raw)));
		$emails = array_map('sanitize_email', $emails);
		$emails = array_filter($emails, function($email) {
			return !empty($email) && is_email($email);
		});

		if (empty($emails)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_RESET_PASSWORD, 'error'));
			exit;
		}

		$settings = User_Manager_Core::get_settings();
		$login_url = $settings['default_login_url'] ?? '/my-account/';
		$is_bulk = count($emails) > 1;
		
		$reset_count = 0;
		$not_found_count = 0;
		$emails_sent = 0;

		foreach ($emails as $email) {
			$user = get_user_by('email', $email);
			if (!$user) {
				$not_found_count++;
				// Log not found
				if ($settings['log_activity'] ?? true) {
					User_Manager_Core::add_activity_log('password_reset_failed', 0, 'Reset Password', [
						'error' => __('User not found', 'user-manager'),
						'attempted_email' => $email,
						'bulk' => $is_bulk,
					]);
				}
				continue;
			}

			// For bulk resets, always generate random password
			// For single reset, use provided password or generate random
			$user_password = ($is_bulk || empty($password)) ? wp_generate_password(12, true, false) : $password;

			// Reset password
			wp_set_password($user_password, $user->ID);
			$reset_count++;

			// Log activity
			if ($settings['log_activity'] ?? true) {
				User_Manager_Core::add_activity_log('password_reset', $user->ID, 'Reset Password', [
					'email_sent' => $send_email,
					'template_id' => $template_id,
					'login_url' => $login_url,
					'bulk' => $is_bulk,
					'password_auto_generated' => ($is_bulk || empty($_POST['password'])),
				]);
			}

			// Send email if requested
			if ($send_email) {
				User_Manager_Email::send_user_email($user->ID, $user_password, $login_url, $template_id);
				$emails_sent++;
			}
		}

		// Redirect with appropriate message
		if ($is_bulk) {
			$message = $send_email ? 'bulk_password_reset_email_sent' : 'bulk_password_reset';
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_RESET_PASSWORD, $message, [
				'reset' => $reset_count,
				'not_found' => $not_found_count,
				'emails' => $emails_sent,
			]));
		} else {
			if ($reset_count === 0) {
				wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_RESET_PASSWORD, 'user_not_found'));
			} elseif ($send_email) {
				wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_RESET_PASSWORD, 'password_reset_email_sent'));
			} else {
				wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_RESET_PASSWORD, 'password_reset'));
			}
		}
		exit;
	}

	/**
	 * Handle user removal.
	 */
	public static function handle_remove_user(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_remove_user');

		$emails_raw = isset($_POST['emails']) ? sanitize_textarea_field(wp_unslash($_POST['emails'])) : '';

		// Parse emails (one per line) - handle both \r\n and \n line endings
		$emails_raw = str_replace("\r\n", "\n", $emails_raw);
		$emails_raw = str_replace("\r", "\n", $emails_raw);
		$emails = array_filter(array_map('trim', explode("\n", $emails_raw)));
		$emails = array_map('sanitize_email', $emails);
		$emails = array_filter($emails, function($email) {
			return !empty($email) && is_email($email);
		});

		if (empty($emails)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_REMOVE_USER, 'error'));
			exit;
		}

		$settings = User_Manager_Core::get_settings();
		$is_multisite = is_multisite();
		$delete_from_network = $is_multisite && isset($_POST['delete_from_network_if_no_other_sites']) && $_POST['delete_from_network_if_no_other_sites'] === '1';
		$removed_count = 0;
		$not_found_count = 0;

		foreach ($emails as $email) {
			$user = get_user_by('email', $email);
			if (!$user) {
				$not_found_count++;
				// Log not found
				if ($settings['log_activity'] ?? true) {
					User_Manager_Core::add_activity_log('user_remove_failed', 0, 'Remove User', [
						'error' => __('User not found', 'user-manager'),
						'attempted_email' => $email,
					]);
				}
				continue;
			}

			// Store user info before deletion for logging
			$user_email = $user->user_email;
			$user_id = $user->ID;

			if ($is_multisite) {
				// On multisite, remove user from current site only
				if (is_user_member_of_blog($user_id, get_current_blog_id())) {
					remove_user_from_blog($user_id, get_current_blog_id());
					$removed_count++;
					
					// Log activity
					if ($settings['log_activity'] ?? true) {
						User_Manager_Core::add_activity_log('user_removed', $user_id, 'Remove User', [
							'user_email' => $user_email,
							'multisite' => true,
							'blog_id' => get_current_blog_id(),
						]);
					}
					
					// If checkbox is checked, check if user exists on other sites and delete from network if not
					if ($delete_from_network) {
						$user_exists_on_other_sites = false;
						$sites = get_sites(['number' => 9999]);
						
						foreach ($sites as $site) {
							if ((int) $site->blog_id === get_current_blog_id()) {
								continue; // Skip current site
							}
							if (is_user_member_of_blog($user_id, $site->blog_id)) {
								$user_exists_on_other_sites = true;
								break;
							}
						}
						
						// If user doesn't exist on any other sites, delete from network
						if (!$user_exists_on_other_sites) {
							// Check if user is the current user (can't delete yourself)
							if ($user_id === get_current_user_id()) {
								if ($settings['log_activity'] ?? true) {
									User_Manager_Core::add_activity_log('user_remove_failed', $user_id, 'Remove User', [
										'error' => __('Cannot delete your own account from network', 'user-manager'),
										'attempted_email' => $email,
										'multisite' => true,
									]);
								}
							} else {
								// Require files for wp_delete_user
								require_once(ABSPATH . 'wp-admin/includes/user.php');
								
								// Delete user from network (reassigns posts to current user if needed)
								$deleted = wp_delete_user($user_id, get_current_user_id());
								
								if ($deleted) {
									// Update log entry to reflect network deletion
									if ($settings['log_activity'] ?? true) {
										User_Manager_Core::add_activity_log('user_deleted', $user_id, 'Remove User', [
											'user_email' => $user_email,
											'multisite' => true,
											'deleted_from_network' => true,
											'removed_from_blog_id' => get_current_blog_id(),
										]);
									}
								}
							}
						}
					}
				} else {
					$not_found_count++;
					// Log not found on this site
					if ($settings['log_activity'] ?? true) {
						User_Manager_Core::add_activity_log('user_remove_failed', $user_id, 'Remove User', [
							'error' => __('User not a member of this site', 'user-manager'),
							'attempted_email' => $email,
							'multisite' => true,
						]);
					}
				}
			} else {
				// On single site, delete user permanently
				// Check if user is the current user (can't delete yourself)
				if ($user_id === get_current_user_id()) {
					$not_found_count++;
					if ($settings['log_activity'] ?? true) {
						User_Manager_Core::add_activity_log('user_remove_failed', $user_id, 'Remove User', [
							'error' => __('Cannot delete your own account', 'user-manager'),
							'attempted_email' => $email,
						]);
					}
					continue;
				}

				// Require files for wp_delete_user
				require_once(ABSPATH . 'wp-admin/includes/user.php');
				
				// Delete user (reassigns posts to current user if needed)
				$deleted = wp_delete_user($user_id, get_current_user_id());
				
				if ($deleted) {
					$removed_count++;
					
					// Log activity
					if ($settings['log_activity'] ?? true) {
						User_Manager_Core::add_activity_log('user_deleted', $user_id, 'Remove User', [
							'user_email' => $user_email,
							'multisite' => false,
						]);
					}
				} else {
					$not_found_count++;
					// Log deletion failure
					if ($settings['log_activity'] ?? true) {
						User_Manager_Core::add_activity_log('user_remove_failed', $user_id, 'Remove User', [
							'error' => __('Failed to delete user', 'user-manager'),
							'attempted_email' => $email,
						]);
					}
				}
			}
		}

		// Redirect with appropriate message
		if (count($emails) > 1) {
			$message = 'bulk_user_removed';
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_REMOVE_USER, $message, [
				'removed' => $removed_count,
				'not_found' => $not_found_count,
			]));
		} else {
			if ($removed_count === 0) {
				wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_REMOVE_USER, 'user_not_found'));
			} else {
				wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_REMOVE_USER, 'user_removed'));
			}
		}
		exit;
	}

	/**
	 * Handle user deactivation (preserve account/history, block future login).
	 */
	public static function handle_deactivate_user(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_deactivate_user');

		$identifiers_raw = isset($_POST['identifiers'])
			? sanitize_textarea_field(wp_unslash($_POST['identifiers']))
			: (isset($_POST['emails']) ? sanitize_textarea_field(wp_unslash($_POST['emails'])) : '');
		$identifiers_raw = str_replace(["\r\n", "\r", ',', ';'], "\n", $identifiers_raw);
		$identifiers = array_values(array_filter(array_map('trim', explode("\n", $identifiers_raw))));

		if (empty($identifiers)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'error'));
			exit;
		}

		$settings = User_Manager_Core::get_settings();
		$log_enabled = $settings['log_activity'] ?? true;
		$reset_password = array_key_exists('deactivate_users_reset_password', $settings)
			? !empty($settings['deactivate_users_reset_password'])
			: true;
		$prefix_identity = array_key_exists('deactivate_users_prefix_identity', $settings)
			? !empty($settings['deactivate_users_prefix_identity'])
			: true;

		$deactivated_count = 0;
		$already_count = 0;
		$not_found_count = 0;
		$failed_count = 0;
		$prefix = '[' . wp_date('Ymd', current_time('timestamp')) . ']-deactivated-';

		foreach ($identifiers as $identifier) {
			$user = self::get_user_from_deactivation_identifier($identifier);
			if (!$user instanceof WP_User) {
				$not_found_count++;
				if ($log_enabled) {
					User_Manager_Core::add_activity_log('user_deactivate_failed', 0, 'Deactivate User', [
						'error' => __('User not found', 'user-manager'),
						'attempted_identifier' => $identifier,
					]);
				}
				continue;
			}

			$user_id = (int) $user->ID;
			if ($user_id === get_current_user_id()) {
				$failed_count++;
				if ($log_enabled) {
					User_Manager_Core::add_activity_log('user_deactivate_failed', $user_id, 'Deactivate User', [
						'error' => __('Cannot deactivate your own account', 'user-manager'),
						'attempted_identifier' => $identifier,
					]);
				}
				continue;
			}

			if (User_Manager_Core::is_user_deactivated($user_id)) {
				$already_count++;
				if ($log_enabled) {
					User_Manager_Core::add_activity_log('user_deactivated_already', $user_id, 'Deactivate User', [
						'user_email' => (string) $user->user_email,
						'attempted_identifier' => $identifier,
					]);
				}
				continue;
			}

			$original_login = (string) $user->user_login;
			$original_email = (string) $user->user_email;
			if (get_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_LOGIN_META_KEY, true) === '') {
				update_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_LOGIN_META_KEY, $original_login);
			}
			if (get_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_EMAIL_META_KEY, true) === '') {
				update_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_EMAIL_META_KEY, $original_email);
			}

			$new_login = $original_login;
			$new_email = $original_email;
			if ($prefix_identity) {
				$new_login = self::build_deactivated_login($original_login, $prefix, $user_id);
				$new_email = self::build_deactivated_email($original_email, $prefix, $user_id);

				global $wpdb;
				$updated = $wpdb->update(
					$wpdb->users,
					[
						'user_login' => $new_login,
						'user_email' => $new_email,
						'user_status' => 1,
					],
					['ID' => $user_id],
					['%s', '%s', '%d'],
					['%d']
				);
				if ($updated === false) {
					$failed_count++;
					if ($log_enabled) {
						User_Manager_Core::add_activity_log('user_deactivate_failed', $user_id, 'Deactivate User', [
							'error' => __('Failed to update deactivated login/email values', 'user-manager'),
							'attempted_identifier' => $identifier,
						]);
					}
					continue;
				}
			} else {
				global $wpdb;
				$wpdb->update(
					$wpdb->users,
					['user_status' => 1],
					['ID' => $user_id],
					['%d'],
					['%d']
				);
			}

			if ($reset_password) {
				$random_password = wp_generate_password(28, true, true);
				wp_set_password($random_password, $user_id);
			}

			update_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_META_KEY, 1);
			update_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_AT_META_KEY, current_time('mysql'));
			update_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_BY_META_KEY, (int) get_current_user_id());
			clean_user_cache($user_id);

			$deactivated_count++;
			User_Manager_Core::add_deactivated_users_history_entry([
				'action' => 'deactivated',
				'user_id' => $user_id,
				'user_login' => $original_login,
				'user_email' => $original_email,
				'result_login' => $new_login,
				'result_email' => $new_email,
				'attempted_identifier' => $identifier,
				'performed_by' => (int) get_current_user_id(),
				'performed_at' => current_time('mysql'),
			]);
			if ($log_enabled) {
				User_Manager_Core::add_activity_log('user_deactivated', $user_id, 'Deactivate User', [
					'attempted_identifier' => $identifier,
					'user_email' => $original_email,
					'original_login' => $original_login,
					'new_login' => $new_login,
					'new_email' => $new_email,
					'password_reset' => $reset_password,
					'identity_prefixed' => $prefix_identity,
				]);
			}
		}

		if (count($identifiers) > 1) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'bulk_user_deactivated', [
				'deactivated' => $deactivated_count,
				'already' => $already_count,
				'not_found' => $not_found_count,
				'failed' => $failed_count,
			]));
			exit;
		}

		if ($deactivated_count > 0) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'user_deactivated'));
			exit;
		}
		if ($already_count > 0) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'user_already_deactivated'));
			exit;
		}
		if ($not_found_count > 0) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'user_not_found'));
			exit;
		}

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'user_deactivate_failed'));
		exit;
	}

	/**
	 * Handle user reactivation.
	 */
	public static function handle_reactivate_user(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		$user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
		if ($user_id <= 0) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'user_reactivate_failed'));
			exit;
		}

		check_admin_referer('user_manager_reactivate_user_' . $user_id, 'user_manager_reactivate_user_nonce');

		$paged = isset($_POST['deactivate_users_paged']) ? max(1, absint($_POST['deactivate_users_paged'])) : 1;
		$redirect_extra = ['deactivate_users_paged' => $paged];
		$settings = User_Manager_Core::get_settings();
		$log_enabled = $settings['log_activity'] ?? true;

		$user = get_user_by('ID', $user_id);
		if (!$user instanceof WP_User) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'user_not_found', $redirect_extra));
			exit;
		}

		if (!User_Manager_Core::is_user_deactivated($user_id)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'user_not_deactivated', $redirect_extra));
			exit;
		}

		$original_login = (string) get_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_LOGIN_META_KEY, true);
		$original_email = (string) get_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_EMAIL_META_KEY, true);

		$restored_login = self::build_deactivated_login($original_login !== '' ? $original_login : (string) $user->user_login, '', $user_id);
		$restored_email = self::build_deactivated_email($original_email !== '' ? $original_email : (string) $user->user_email, '', $user_id);

		global $wpdb;
		$updated = $wpdb->update(
			$wpdb->users,
			[
				'user_login' => $restored_login,
				'user_email' => $restored_email,
				'user_status' => 0,
			],
			['ID' => $user_id],
			['%s', '%s', '%d'],
			['%d']
		);

		if ($updated === false) {
			if ($log_enabled) {
				User_Manager_Core::add_activity_log('user_reactivate_failed', $user_id, 'Deactivate User', [
					'error' => __('Failed to restore login/email values during reactivation', 'user-manager'),
					'restored_login' => $restored_login,
					'restored_email' => $restored_email,
				]);
			}
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'user_reactivate_failed', $redirect_extra));
			exit;
		}

		delete_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_META_KEY);
		delete_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_AT_META_KEY);
		delete_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_BY_META_KEY);
		delete_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_LOGIN_META_KEY);
		delete_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_EMAIL_META_KEY);
		clean_user_cache($user_id);

		if ($log_enabled) {
			User_Manager_Core::add_activity_log('user_reactivated', $user_id, 'Deactivate User', [
				'restored_login' => $restored_login,
				'restored_email' => $restored_email,
			]);
		}
		User_Manager_Core::add_deactivated_users_history_entry([
			'action' => 'reactivated',
			'user_id' => $user_id,
			'user_login' => (string) $user->user_login,
			'user_email' => (string) $user->user_email,
			'result_login' => $restored_login,
			'result_email' => $restored_email,
			'performed_by' => (int) get_current_user_id(),
			'performed_at' => current_time('mysql'),
		]);

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_DEACTIVATE_USER, 'user_reactivated', $redirect_extra));
		exit;
	}

	/**
	 * Resolve a deactivation identifier (email or username) to a user object.
	 */
	private static function get_user_from_deactivation_identifier(string $identifier): ?WP_User {
		$identifier = trim($identifier);
		if ($identifier === '') {
			return null;
		}

		if (is_email($identifier)) {
			$email = sanitize_email($identifier);
			$user_by_email = $email !== '' ? get_user_by('email', $email) : false;
			if ($user_by_email instanceof WP_User) {
				return $user_by_email;
			}
		}

		$login = sanitize_user($identifier, true);
		if ($login === '') {
			return null;
		}

		$user_by_login = get_user_by('login', $login);
		return $user_by_login instanceof WP_User ? $user_by_login : null;
	}

	/**
	 * Build a unique deactivated username value.
	 */
	private static function build_deactivated_login(string $existing_login, string $prefix, int $user_id): string {
		global $wpdb;
		$max_len = 60;
		$base = substr($prefix . $existing_login, 0, $max_len);
		if ($base === '') {
			$base = substr($prefix . 'user-' . $user_id, 0, $max_len);
		}

		$candidate = $base;
		$counter = 2;
		while (true) {
			$existing_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->users} WHERE user_login = %s LIMIT 1",
					$candidate
				)
			);
			if ($existing_id === 0 || $existing_id === $user_id) {
				return $candidate;
			}
			$suffix = '-' . $counter;
			$candidate = substr($base, 0, max(1, $max_len - strlen($suffix))) . $suffix;
			$counter++;
		}
	}

	/**
	 * Build a unique deactivated user_email value.
	 */
	private static function build_deactivated_email(string $existing_email, string $prefix, int $user_id): string {
		global $wpdb;
		$max_len = 100;
		$base = substr($prefix . $existing_email, 0, $max_len);
		if ($base === '') {
			$base = substr($prefix . 'user-' . $user_id . '@example.invalid', 0, $max_len);
		}

		$candidate = $base;
		$counter = 2;
		while (true) {
			$existing_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->users} WHERE user_email = %s LIMIT 1",
					$candidate
				)
			);
			if ($existing_id === 0 || $existing_id === $user_id) {
				return $candidate;
			}
			$suffix = '-' . $counter;
			$candidate = substr($base, 0, max(1, $max_len - strlen($suffix))) . $suffix;
			$counter++;
		}
	}

	/**
	 * Handle bulk user creation.
	 */
	public static function handle_bulk_create(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_bulk_create');

		$method = isset($_POST['bulk_method']) ? sanitize_key($_POST['bulk_method']) : '';
		$default_role = isset($_POST['default_role']) ? sanitize_key($_POST['default_role']) : 'customer';
		$send_email = isset($_POST['send_email']) && $_POST['send_email'] === '1';
		$template_id = isset($_POST['email_template']) ? sanitize_key($_POST['email_template']) : '';
		$settings = User_Manager_Core::get_settings();
		$login_url = isset($_POST['login_url']) ? sanitize_text_field(wp_unslash($_POST['login_url'])) : ($settings['default_login_url'] ?? '/my-account/');
		$coupon_code = isset($_POST['coupon_code_for_template']) ? sanitize_text_field(wp_unslash($_POST['coupon_code_for_template'])) : '';
		$allow_email_coupon_code = isset($_POST['allow_email_coupon_code']) ? sanitize_text_field(wp_unslash($_POST['allow_email_coupon_code'])) : '';

		$rows = [];

		if ($method === 'file' && !empty($_FILES['csv_file']['tmp_name'])) {
			$rows = self::parse_csv_file($_FILES['csv_file']['tmp_name']);
		} elseif ($method === 'paste' && !empty($_POST['paste_data'])) {
			$rows = self::parse_paste_data(wp_unslash($_POST['paste_data']));
		}

		if (empty($rows)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'error'));
			exit;
		}

		$created = 0;
		$created_users = [];
		$updated_users = [];

		$update_existing = !empty($settings['update_existing_users']);

		$coupon_for_allow = null;
		$coupon_for_allow_id = 0;
		if ($allow_email_coupon_code !== '' && class_exists('WC_Coupon')) {
			$coupon_for_allow_id = function_exists('wc_get_coupon_id_by_code') ? wc_get_coupon_id_by_code($allow_email_coupon_code) : 0;
			if ($coupon_for_allow_id) {
				$tmp_coupon = new WC_Coupon($coupon_for_allow_id);
				if ($tmp_coupon && $tmp_coupon->get_id()) {
					$coupon_for_allow = $tmp_coupon;
				}
			}
		}

		foreach ($rows as $row) {
			$raw_email = isset($row['email']) ? $row['email'] : '';
			$email = sanitize_email($raw_email);
			
			if (empty($email) || !is_email($email)) {
				// Log invalid email error
				if ($settings['log_activity'] ?? true) {
					User_Manager_Core::add_activity_log('user_create_failed', 0, 'Bulk Create', [
						'error' => __('Invalid email address', 'user-manager'),
						'attempted_email' => $raw_email,
						'first_name' => isset($row['first_name']) ? $row['first_name'] : '',
						'last_name' => isset($row['last_name']) ? $row['last_name'] : '',
					]);
				}
				continue;
			}

			$existing_user = get_user_by('email', $email);
			$password = !empty($row['password']) ? $row['password'] : wp_generate_password(12, true, false);
			$role = !empty($row['role']) ? sanitize_key($row['role']) : $default_role;
			$first_name = isset($row['first_name']) ? sanitize_text_field($row['first_name']) : '';
			$last_name = isset($row['last_name']) ? sanitize_text_field($row['last_name']) : '';
			// Collect custom meta from any additional columns
			$reserved = ['email','username','first_name','last_name','role','password','coupon_email_append'];
			$custom_meta = [];
			foreach ($row as $k => $v) {
				if (in_array($k, $reserved, true)) {
					continue;
				}
				if ($v === '' || $v === null) {
					continue;
				}
				$custom_meta[$k] = sanitize_text_field($v);
			}

			if ($existing_user) {
				$added_to_subsite = self::maybe_add_existing_network_user_to_current_site((int) $existing_user->ID, $role, $settings);
				if ($update_existing) {
					// Capture old values
					$old_values = [
						'first_name' => $existing_user->first_name,
						'last_name' => $existing_user->last_name,
						'role' => implode(', ', $existing_user->roles),
					];
					
					// Update existing user
					wp_update_user([
						'ID' => $existing_user->ID,
						'first_name' => $first_name,
						'last_name' => $last_name,
						'role' => $role,
						'user_pass' => $password,
					]);
					// Apply custom meta
					if (!empty($custom_meta)) {
						foreach ($custom_meta as $meta_key => $meta_value) {
							update_user_meta($existing_user->ID, $meta_key, $meta_value);
						}
					}
					
					$created++;
					
					if ($settings['log_activity'] ?? true) {
						User_Manager_Core::add_activity_log('user_updated', $existing_user->ID, 'Bulk Create (Updated)', [
							'email_sent' => $send_email,
							'template_id' => $template_id,
							'login_url' => $login_url,
							'password_changed' => true,
							'added_to_subsite' => $added_to_subsite,
							'old_values' => $old_values,
							'new_values' => [
								'first_name' => $first_name,
								'last_name' => $last_name,
								'role' => $role,
							],
						]);
					}
					
					if ($send_email) {
						User_Manager_Email::send_user_email($existing_user->ID, $password, $login_url, $template_id, $coupon_code);
					}
					
					$updated_users[] = [
						'id' => $existing_user->ID,
						'email' => $email,
						'name' => trim($first_name . ' ' . $last_name),
					];

					// Append email to coupon restrictions for updated users when configured.
					if ($coupon_for_allow) {
						self::append_email_to_coupon_restrictions($coupon_for_allow, $coupon_for_allow_id, $email);
					}
					// Per-row: append this user's email to the coupon specified in coupon_email_append column.
					$row_coupon_code = isset($row['coupon_email_append']) ? trim((string) $row['coupon_email_append']) : '';
					if ($row_coupon_code !== '' && class_exists('WC_Coupon')) {
						self::append_email_to_coupon_by_code($row_coupon_code, $email);
					}
				} elseif ($added_to_subsite) {
					$created++;
					$display_name = trim($existing_user->first_name . ' ' . $existing_user->last_name);
					if ($display_name === '') {
						$display_name = (string) $existing_user->display_name;
					}
					$created_users[] = [
						'id' => $existing_user->ID,
						'email' => $email,
						'name' => $display_name,
					];

					if ($settings['log_activity'] ?? true) {
						User_Manager_Core::add_activity_log('user_added_to_subsite', $existing_user->ID, 'Bulk Create (Added to Site)', [
							'email' => $email,
							'role' => $role,
							'blog_id' => get_current_blog_id(),
							'multisite' => is_multisite(),
							'update_existing_users' => false,
						]);
					}
				}
				continue;
			}

			$username = !empty($row['username']) ? sanitize_user($row['username']) : $email;
			if (username_exists($username)) {
				$username = $email;
				if (username_exists($username)) {
					$username = $email . '_' . wp_rand(100, 999);
				}
			}

			$user_id = wp_insert_user([
				'user_login' => $username,
				'user_email' => $email,
				'user_pass' => $password,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'role' => $role,
			]);

			if (!is_wp_error($user_id)) {
				// Apply custom meta
				if (!empty($custom_meta)) {
					foreach ($custom_meta as $meta_key => $meta_value) {
						update_user_meta($user_id, $meta_key, $meta_value);
					}
				}
				$created++;

				// Log activity
				if ($settings['log_activity'] ?? true) {
					User_Manager_Core::add_activity_log('user_created', $user_id, 'Bulk Create', [
						'email_sent' => $send_email,
						'template_id' => $template_id,
						'login_url' => $login_url,
						'new_values' => [
							'email' => $email,
							'username' => $username,
							'first_name' => $first_name,
							'last_name' => $last_name,
							'role' => $role,
							'password_generated' => empty($row['password']),
						],
					]);
				}

				// Send email if requested
				if ($send_email) {
					User_Manager_Email::send_user_email($user_id, $password, $login_url, $template_id, $coupon_code);
				}

				$created_users[] = [
					'id' => $user_id,
					'email' => $email,
					'name' => trim($first_name . ' ' . $last_name),
				];

				// Append email to coupon restrictions for newly created users when configured.
				if ($coupon_for_allow) {
					self::append_email_to_coupon_restrictions($coupon_for_allow, $coupon_for_allow_id, $email);
				}
				// Per-row: append this user's email to the coupon specified in coupon_email_append column.
				$row_coupon_code = isset($row['coupon_email_append']) ? trim((string) $row['coupon_email_append']) : '';
				if ($row_coupon_code !== '' && class_exists('WC_Coupon')) {
					self::append_email_to_coupon_by_code($row_coupon_code, $email);
				}
			} else {
				// Log error
				if ($settings['log_activity'] ?? true) {
					User_Manager_Core::add_activity_log('user_create_failed', 0, 'Bulk Create', [
						'error' => $user_id->get_error_message(),
						'attempted_email' => $email,
						'attempted_username' => $username,
					]);
				}
			}
		}

		$redirect = add_query_arg('count', $created, User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'bulk_created'));
		if (!empty($created_users) || !empty($updated_users)) {
			$token = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('bulk_', true);
			set_transient('um_bulk_notice_' . $token, [
				'created' => $created_users,
				'updated' => $updated_users,
			], HOUR_IN_SECONDS);
			$redirect = add_query_arg('bulk_ref', rawurlencode($token), $redirect);
		}
		if ($settings['log_activity'] ?? true) {
			User_Manager_Core::add_activity_log('bulk_import_summary', 0, 'Bulk Create Summary', [
				'created_count' => $created,
				'method' => $method,
				'sent_emails' => $send_email,
			]);
		}
		User_Manager_Core::maybe_debug_log('Bulk create redirect', [
			'redirect' => $redirect,
			'created' => $created,
			'updated_users' => count($updated_users),
			'created_users' => count($created_users),
		]);
		wp_safe_redirect($redirect);
		exit;
	}

	/**
	 * Parse CSV file.
	 */
	private static function parse_csv_file(string $file_path): array {
		$rows = [];
		$handle = fopen($file_path, 'r');
		
		if (!$handle) {
			return $rows;
		}

		$headers = null;
		$line_num = 0;

		while (($data = fgetcsv($handle)) !== false) {
			$line_num++;
			
			// First row - check if it's a header
			if ($line_num === 1) {
				$first_cell = strtolower(trim($data[0] ?? ''));
				if (in_array($first_cell, ['email', 'email_address', 'e-mail', 'user_email'])) {
					$headers = array_map('strtolower', array_map('trim', $data));
					continue;
				}
				// No header row, use default column order
				$headers = ['email', 'first_name', 'last_name', 'role', 'username', 'password'];
			}

			$row = [];
			foreach ($headers as $i => $header) {
				$header = self::normalize_header($header);
				if (isset($data[$i])) {
					$row[$header] = trim($data[$i]);
				}
			}

			if (!empty($row['email'])) {
				$rows[] = $row;
			}
		}

		fclose($handle);
		return $rows;
	}

	/**
	 * Parse pasted data.
	 */
	private static function parse_paste_data(string $data): array {
		$rows = [];
		$lines = preg_split('/\r\n|\r|\n/', $data);
		
		$headers = null;
		$line_num = 0;

		foreach ($lines as $line) {
			$line = trim($line);
			if (empty($line)) {
				continue;
			}

			$line_num++;
			$cells = preg_split('/\t/', $line);

			// First row - check if it's a header
			if ($line_num === 1) {
				$first_cell = strtolower(trim($cells[0] ?? ''));
				if (in_array($first_cell, ['email', 'email_address', 'e-mail', 'user_email'])) {
					$headers = array_map('strtolower', array_map('trim', $cells));
					continue;
				}
				// No header row, use default column order from settings
				$settings = User_Manager_Core::get_settings();
				$default_columns = isset($settings['paste_default_columns']) && $settings['paste_default_columns'] !== ''
					? $settings['paste_default_columns']
					: 'email,first_name,last_name,role,username,password';
				$headers = array_map('strtolower', array_map('trim', array_filter(explode(',', $default_columns))));
				if (empty($headers)) {
					$headers = ['email', 'first_name', 'last_name', 'role', 'username'];
				}
			}

			$row = [];
			foreach ($headers as $i => $header) {
				$header = self::normalize_header($header);
				if (isset($cells[$i])) {
					$row[$header] = trim($cells[$i]);
				}
			}

			if (!empty($row['email'])) {
				$rows[] = $row;
			}
		}

		return $rows;
	}

	/**
	 * Normalize header names.
	 */
	private static function normalize_header(string $header): string {
		$header = strtolower(trim($header));
		
		$map = [
			'email_address' => 'email',
			'e-mail' => 'email',
			'user_email' => 'email',
			'firstname' => 'first_name',
			'first' => 'first_name',
			'lastname' => 'last_name',
			'last' => 'last_name',
			'user_role' => 'role',
			'user_login' => 'username',
			'login' => 'username',
			'user_pass' => 'password',
			'pass' => 'password',
		];

		return $map[$header] ?? $header;
	}

	/**
	 * Helper: append an email to a coupon's Allowed Emails by coupon code.
	 *
	 * @param string $coupon_code WooCommerce coupon code.
	 * @param string $email       Email to append.
	 */
	private static function append_email_to_coupon_by_code(string $coupon_code, string $email): void {
		$coupon_code = trim($coupon_code);
		if ($coupon_code === '' || !function_exists('wc_get_coupon_id_by_code')) {
			return;
		}
		$coupon_id = wc_get_coupon_id_by_code($coupon_code);
		if (!$coupon_id) {
			return;
		}
		$coupon = new WC_Coupon($coupon_id);
		if (!$coupon || !$coupon->get_id()) {
			return;
		}
		self::append_email_to_coupon_restrictions($coupon, $coupon_id, $email);
	}

	/**
	 * Helper: append an email address to a coupon's Allowed Emails / restrictions.
	 *
	 * @param WC_Coupon $coupon
	 * @param int       $coupon_id
	 * @param string    $email
	 */
	private static function append_email_to_coupon_restrictions($coupon, int $coupon_id, string $email): void {
		$email = trim($email);
		if ($email === '') {
			return;
		}

		if ($coupon && method_exists($coupon, 'get_email_restrictions') && method_exists($coupon, 'set_email_restrictions')) {
			$existing = $coupon->get_email_restrictions('edit');
			if (!is_array($existing)) {
				$existing = [];
			}
			$existing[] = $email;
			$existing = array_values(array_unique(array_filter(array_map('trim', $existing))));
			$coupon->set_email_restrictions($existing);
			$coupon->save();
		} else {
			$existing = get_post_meta($coupon_id, 'customer_email', true);
			$list     = [];
			if (is_array($existing)) {
				$list = $existing;
			} elseif (is_string($existing) && $existing !== '') {
				$list = array_map('trim', explode(',', $existing));
			}
			$list[] = $email;
			$list   = array_values(array_unique(array_filter($list)));
			update_post_meta($coupon_id, 'customer_email', $list);
		}
	}

	/**
	 * Handle saving email template.
	 */
	public static function handle_save_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_save_template');

		$template_id = isset($_POST['template_id']) ? sanitize_key($_POST['template_id']) : '';
		$title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
		$description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
		$subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
		$heading = isset($_POST['heading']) ? sanitize_text_field(wp_unslash($_POST['heading'])) : '';
		$body = isset($_POST['body']) ? wp_kses_post(wp_unslash($_POST['body'])) : '';
		$bcc = isset($_POST['bcc']) ? sanitize_email(wp_unslash($_POST['bcc'])) : '';

		if (empty($title) || empty($subject) || empty($body)) {
			wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'error']));
			exit;
		}

		$templates = User_Manager_Core::get_email_templates();

		$template_data = [
			'title' => $title,
			'description' => $description,
			'subject' => $subject,
			'heading' => $heading,
			'body' => $body,
			'bcc' => $bcc,
		];

		$saved_id = null;
		if ($template_id && isset($templates[$template_id])) {
			// Preserve order if present
			if (isset($templates[$template_id]['order'])) {
				$template_data['order'] = (int) $templates[$template_id]['order'];
			}
			$templates[$template_id] = $template_data;
			$saved_id = $template_id;
		} else {
			$new_id = 'tpl_' . uniqid();
			// Determine next order
			$max_order = 0;
			foreach ($templates as $t) {
				$max_order = max($max_order, isset($t['order']) ? (int) $t['order'] : 0);
			}
			$template_data['order'] = $max_order + 1;
			$templates[$new_id] = $template_data;
			$saved_id = $new_id;
		}

		update_option(User_Manager_Core::EMAIL_TEMPLATES_KEY, $templates);

		$redirect_url = self::get_email_templates_redirect_url(['um_msg' => 'template_saved']);
		if ($saved_id) {
			$redirect_url = add_query_arg('edit_template', $saved_id, $redirect_url);
		}
		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	 * Handle moving template up/down (reordering).
	 */
	public static function handle_move_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}
		check_admin_referer('user_manager_move_template');
		
		$template_id = isset($_POST['template_id']) ? sanitize_key($_POST['template_id']) : '';
		$direction = isset($_POST['direction']) ? sanitize_key($_POST['direction']) : '';
		
		if (empty($template_id) || !in_array($direction, ['up', 'down'], true)) {
			wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'error']));
			exit;
		}
		
		$templates = get_option(User_Manager_Core::EMAIL_TEMPLATES_KEY, []);
		if (!is_array($templates) || !isset($templates[$template_id])) {
			wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'error']));
			exit;
		}
		
		// Ensure orders exist
		$order = 1;
		foreach ($templates as $id => &$tpl) {
			if (!isset($tpl['order']) || !is_numeric($tpl['order'])) {
				$tpl['order'] = $order;
			}
			$order++;
		}
		unset($tpl);
		
		// Build an array of ids sorted by order
		uasort($templates, function($a, $b) {
			$oa = isset($a['order']) ? (int) $a['order'] : 0;
			$ob = isset($b['order']) ? (int) $b['order'] : 0;
			if ($oa === $ob) return 0;
			return ($oa < $ob) ? -1 : 1;
		});
		$ids = array_keys($templates);
		$index = array_search($template_id, $ids, true);
		if ($index === false) {
			wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'error']));
			exit;
		}
		
		if ($direction === 'up' && $index > 0) {
			$swapWith = $index - 1;
		} elseif ($direction === 'down' && $index < count($ids) - 1) {
			$swapWith = $index + 1;
		} else {
			// nothing to do
			wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'template_saved']));
			exit;
		}
		
		// Swap their orders
		$idA = $ids[$index];
		$idB = $ids[$swapWith];
		$tmp = $templates[$idA]['order'];
		$templates[$idA]['order'] = $templates[$idB]['order'];
		$templates[$idB]['order'] = $tmp;
		
		update_option(User_Manager_Core::EMAIL_TEMPLATES_KEY, $templates);
		wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'template_saved']));
		exit;
	}

	/**
	 * Handle deleting email template.
	 */
	public static function handle_delete_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_delete_template');

		$template_id = isset($_POST['template_id']) ? sanitize_key($_POST['template_id']) : '';

		if (empty($template_id)) {
			wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'error']));
			exit;
		}

		$templates = User_Manager_Core::get_email_templates();

		if (isset($templates[$template_id])) {
			unset($templates[$template_id]);
			update_option(User_Manager_Core::EMAIL_TEMPLATES_KEY, $templates);
		}

		wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'template_deleted']));
		exit;
	}

	/**
	 * Handle duplicating an email template.
	 */
	public static function handle_duplicate_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_duplicate_template');

		$template_id = isset($_POST['template_id']) ? sanitize_key($_POST['template_id']) : '';

		if (empty($template_id)) {
			wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'error']));
			exit;
		}

		$templates = User_Manager_Core::get_email_templates();

		if (!isset($templates[$template_id])) {
			wp_safe_redirect(self::get_email_templates_redirect_url(['um_msg' => 'error']));
			exit;
		}

		$source = $templates[$template_id];

		// Determine next order.
		$max_order = 0;
		foreach ($templates as $tpl) {
			$max_order = max($max_order, isset($tpl['order']) ? (int) $tpl['order'] : 0);
		}

		$new_id = 'tpl_' . uniqid();
		$copy   = $source;
		$copy['title'] = ($copy['title'] ?? '') . ' (Copy)';
		$copy['order'] = $max_order + 1;

		$templates[$new_id] = $copy;

		update_option(User_Manager_Core::EMAIL_TEMPLATES_KEY, $templates);

		wp_safe_redirect(self::get_email_templates_redirect_url([
			'edit_template' => $new_id,
			'message'       => 'template_duplicated',
		]));
		exit;
	}

	/**
	 * Build URL for Email Templates screen, honoring add-on embedding context.
	 *
	 * @param array<string,mixed> $extra Additional query args.
	 */
	private static function get_email_templates_redirect_url(array $extra = []): string {
		$context = isset($_REQUEST['templates_context']) ? sanitize_key(wp_unslash($_REQUEST['templates_context'])) : '';
		if ($context === 'addon-send-email-users') {
			$url = add_query_arg(
				[
					'addon_section'      => 'send-email-users',
					'templates_context'  => $context,
				],
				User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS)
			);
		} else {
			$url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_EMAIL_TEMPLATES);
		}
		if (!empty($extra)) {
			$url = add_query_arg($extra, $url);
		}
		return $url;
	}

	/**
	 * Build URL for Settings > SMS Text Templates section.
	 */
	private static function get_sms_text_templates_section_url(array $extra = []): string {
		$context = isset($_REQUEST['templates_context']) ? sanitize_key(wp_unslash($_REQUEST['templates_context'])) : '';
		if ($context === 'addon-send-sms-text') {
			$url = add_query_arg(
				[
					'addon_section'      => 'send-sms-text',
					'templates_context'  => $context,
				],
				User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS)
			);
		} else {
			$url = add_query_arg(
				'settings_section',
				'sms-text-templates',
				User_Manager_Core::get_page_url(User_Manager_Core::TAB_SETTINGS)
			);
		}
		if (!empty($extra)) {
			$url = add_query_arg($extra, $url);
		}
		return $url;
	}

	/**
	 * Handle save SMS text template action.
	 */
	public static function handle_save_sms_text_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_save_sms_text_template');

		$template_id = isset($_POST['template_id']) ? sanitize_key(wp_unslash($_POST['template_id'])) : '';
		$title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
		$description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
		$body = isset($_POST['body']) ? sanitize_textarea_field(wp_unslash($_POST['body'])) : '';

		if ($title === '' || $body === '') {
			wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_error']));
			exit;
		}

		$templates = User_Manager_Core::get_sms_text_templates();
		$template_data = [
			'title'       => $title,
			'description' => $description,
			'body'        => $body,
		];

		$saved_id = null;
		if ($template_id !== '' && isset($templates[$template_id])) {
			if (isset($templates[$template_id]['order'])) {
				$template_data['order'] = (int) $templates[$template_id]['order'];
			}
			$templates[$template_id] = $template_data;
			$saved_id = $template_id;
		} else {
			$new_id = 'sms_tpl_' . uniqid();
			$max_order = 0;
			foreach ($templates as $tpl) {
				$max_order = max($max_order, isset($tpl['order']) ? (int) $tpl['order'] : 0);
			}
			$template_data['order'] = $max_order + 1;
			$templates[$new_id] = $template_data;
			$saved_id = $new_id;
		}

		update_option(User_Manager_Core::SMS_TEXT_TEMPLATES_KEY, $templates);
		$redirect_url = self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_saved']);
		if ($saved_id) {
			$redirect_url = add_query_arg('edit_sms_template', $saved_id, $redirect_url);
		}

		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	 * Handle move SMS text template action.
	 */
	public static function handle_move_sms_text_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}
		check_admin_referer('user_manager_move_sms_text_template');

		$template_id = isset($_POST['template_id']) ? sanitize_key(wp_unslash($_POST['template_id'])) : '';
		$direction = isset($_POST['direction']) ? sanitize_key(wp_unslash($_POST['direction'])) : '';
		if ($template_id === '' || !in_array($direction, ['up', 'down'], true)) {
			wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_error']));
			exit;
		}

		$templates = get_option(User_Manager_Core::SMS_TEXT_TEMPLATES_KEY, []);
		if (!is_array($templates) || !isset($templates[$template_id])) {
			wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_error']));
			exit;
		}

		$order = 1;
		foreach ($templates as &$tpl) {
			if (!is_array($tpl)) {
				$tpl = [];
			}
			if (!isset($tpl['order']) || !is_numeric($tpl['order'])) {
				$tpl['order'] = $order;
			}
			$order++;
		}
		unset($tpl);

		uasort($templates, static function ($a, $b) {
			$oa = isset($a['order']) ? (int) $a['order'] : 0;
			$ob = isset($b['order']) ? (int) $b['order'] : 0;
			if ($oa === $ob) {
				return 0;
			}
			return $oa < $ob ? -1 : 1;
		});
		$ids = array_keys($templates);
		$index = array_search($template_id, $ids, true);
		if ($index === false) {
			wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_error']));
			exit;
		}

		if ($direction === 'up' && $index > 0) {
			$swap_with = $index - 1;
		} elseif ($direction === 'down' && $index < count($ids) - 1) {
			$swap_with = $index + 1;
		} else {
			wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_saved']));
			exit;
		}

		$id_a = $ids[$index];
		$id_b = $ids[$swap_with];
		$tmp = $templates[$id_a]['order'];
		$templates[$id_a]['order'] = $templates[$id_b]['order'];
		$templates[$id_b]['order'] = $tmp;

		update_option(User_Manager_Core::SMS_TEXT_TEMPLATES_KEY, $templates);
		wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_saved']));
		exit;
	}

	/**
	 * Handle delete SMS text template action.
	 */
	public static function handle_delete_sms_text_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}
		check_admin_referer('user_manager_delete_sms_text_template');

		$template_id = isset($_POST['template_id']) ? sanitize_key(wp_unslash($_POST['template_id'])) : '';
		if ($template_id === '') {
			wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_error']));
			exit;
		}

		$templates = User_Manager_Core::get_sms_text_templates();
		if (isset($templates[$template_id])) {
			unset($templates[$template_id]);
			update_option(User_Manager_Core::SMS_TEXT_TEMPLATES_KEY, $templates);
		}

		wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_deleted']));
		exit;
	}

	/**
	 * Handle duplicate SMS text template action.
	 */
	public static function handle_duplicate_sms_text_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}
		check_admin_referer('user_manager_duplicate_sms_text_template');

		$template_id = isset($_POST['template_id']) ? sanitize_key(wp_unslash($_POST['template_id'])) : '';
		if ($template_id === '') {
			wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_error']));
			exit;
		}

		$templates = User_Manager_Core::get_sms_text_templates();
		if (!isset($templates[$template_id])) {
			wp_safe_redirect(self::get_sms_text_templates_section_url(['um_msg' => 'sms_template_error']));
			exit;
		}

		$source = $templates[$template_id];
		$max_order = 0;
		foreach ($templates as $tpl) {
			$max_order = max($max_order, isset($tpl['order']) ? (int) $tpl['order'] : 0);
		}

		$new_id = 'sms_tpl_' . uniqid();
		$copy = is_array($source) ? $source : [];
		$copy['title'] = ((string) ($copy['title'] ?? 'Template')) . ' (Copy)';
		$copy['order'] = $max_order + 1;
		$templates[$new_id] = $copy;
		update_option(User_Manager_Core::SMS_TEXT_TEMPLATES_KEY, $templates);

		wp_safe_redirect(self::get_sms_text_templates_section_url([
			'edit_sms_template' => $new_id,
			'um_msg'            => 'sms_template_saved',
		]));
		exit;
	}

	/**
	 * Handle saving settings.
	 */
	public static function handle_save_settings(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_save_settings');

		$section = isset($_POST['settings_section']) ? sanitize_key($_POST['settings_section']) : 'general';
		// Normal settings saves should use runtime-aware values (matches legacy behavior).
		// For the dedicated "temporary disable only" save action, we switch to raw
		// settings later inside the addons/blocks cases to preserve saved activation
		// states while toggling temporary runtime disable.
		$settings = User_Manager_Core::get_settings();
		if (!is_array($settings)) {
			$settings = [];
		}
		$settings_before = $settings;
		$bulk_settings_before = get_option('bulk_add_to_cart_settings', []);
		if (!is_array($bulk_settings_before)) {
			$bulk_settings_before = [];
		}
		$role_switch_settings_before = get_option('view_website_by_role_settings', []);
		if (!is_array($role_switch_settings_before)) {
			$role_switch_settings_before = [];
		}
		$bulk_settings_after = $bulk_settings_before;
		$role_switch_settings_after = $role_switch_settings_before;
		$should_reset_media_library_tag_reports = false;
		$media_library_tag_reports_reset_counts = [
			'attachments' => 0,
			'tags' => 0,
		];

		$redirect_tab = User_Manager_Core::TAB_SETTINGS;

		switch ($section) {
			case 'new_user_coupons':
				$redirect_tab = User_Manager_Core::TAB_ADDONS;
				$settings['nuc_enabled'] = isset($_POST['nuc_enabled']) && $_POST['nuc_enabled'] === '1';
				$settings['nuc_when'] = isset($_POST['nuc_when']) ? sanitize_text_field(wp_unslash($_POST['nuc_when'])) : 'after_registration';
				$settings['nuc_template_code'] = isset($_POST['nuc_template_code']) ? sanitize_text_field(wp_unslash($_POST['nuc_template_code'])) : '';
				$settings['nuc_amount_override'] = isset($_POST['nuc_amount_override']) ? sanitize_text_field(wp_unslash($_POST['nuc_amount_override'])) : '';
				$settings['nuc_code_length'] = isset($_POST['nuc_code_length']) ? absint($_POST['nuc_code_length']) : 8;
				$settings['nuc_prefix'] = isset($_POST['nuc_prefix']) ? sanitize_text_field(wp_unslash($_POST['nuc_prefix'])) : '';
				$settings['nuc_postfix'] = isset($_POST['nuc_postfix']) ? sanitize_text_field(wp_unslash($_POST['nuc_postfix'])) : '';
				$settings['nuc_after_date'] = isset($_POST['nuc_after_date']) ? sanitize_text_field(wp_unslash($_POST['nuc_after_date'])) : '';
				$settings['nuc_email_contains'] = isset($_POST['nuc_email_contains']) ? sanitize_text_field(wp_unslash($_POST['nuc_email_contains'])) : '';
				$settings['nuc_email_exclude'] = isset($_POST['nuc_email_exclude']) ? sanitize_text_field(wp_unslash($_POST['nuc_email_exclude'])) : '';
				$settings['nuc_exp_days'] = isset($_POST['nuc_exp_days']) ? absint($_POST['nuc_exp_days']) : 0;
				$settings['nuc_send_email'] = isset($_POST['nuc_send_email']) && $_POST['nuc_send_email'] === '1';
				$settings['nuc_email_template'] = isset($_POST['nuc_email_template']) ? sanitize_key($_POST['nuc_email_template']) : '';
				$settings['nuc_auto_draft_duplicates'] = isset($_POST['nuc_auto_draft_duplicates']) && $_POST['nuc_auto_draft_duplicates'] === '1';
				$settings['nuc_debug_mode'] = isset($_POST['nuc_debug_mode']) && $_POST['nuc_debug_mode'] === '1';
				$settings['nuc_run_everywhere'] = isset($_POST['nuc_run_everywhere']) && $_POST['nuc_run_everywhere'] === '1';
				$settings['nuc_run_my_account'] = isset($_POST['nuc_run_my_account']) && $_POST['nuc_run_my_account'] === '1';
				$settings['nuc_run_cart'] = isset($_POST['nuc_run_cart']) && $_POST['nuc_run_cart'] === '1';
				$settings['nuc_run_checkout'] = isset($_POST['nuc_run_checkout']) && $_POST['nuc_run_checkout'] === '1';
				$settings['nuc_run_product'] = isset($_POST['nuc_run_product']) && $_POST['nuc_run_product'] === '1';
				$settings['nuc_run_shop'] = isset($_POST['nuc_run_shop']) && $_POST['nuc_run_shop'] === '1';
				$settings['nuc_run_home'] = isset($_POST['nuc_run_home']) && $_POST['nuc_run_home'] === '1';
				break;
			
			case 'coupon_notifications':
				$redirect_tab = User_Manager_Core::TAB_ADDONS;
				$settings['user_coupon_notifications_enabled'] = isset($_POST['user_coupon_notifications_enabled']) && $_POST['user_coupon_notifications_enabled'] === '1';
				$settings['coupon_notifications_show_on_cart'] = isset($_POST['coupon_notifications_show_on_cart']) && $_POST['coupon_notifications_show_on_cart'] === '1';
				$settings['coupon_notifications_show_on_checkout'] = isset($_POST['coupon_notifications_show_on_checkout']) && $_POST['coupon_notifications_show_on_checkout'] === '1';
				$settings['coupon_notifications_show_on_my_account'] = isset($_POST['coupon_notifications_show_on_my_account']) && $_POST['coupon_notifications_show_on_my_account'] === '1';
				$settings['coupon_notifications_show_on_home'] = isset($_POST['coupon_notifications_show_on_home']) && $_POST['coupon_notifications_show_on_home'] === '1';
				$settings['coupon_notifications_show_on_product'] = isset($_POST['coupon_notifications_show_on_product']) && $_POST['coupon_notifications_show_on_product'] === '1';
				$settings['coupon_notifications_show_on_archives'] = isset($_POST['coupon_notifications_show_on_archives']) && $_POST['coupon_notifications_show_on_archives'] === '1';
				$settings['coupon_notifications_show_on_posts'] = isset($_POST['coupon_notifications_show_on_posts']) && $_POST['coupon_notifications_show_on_posts'] === '1';
				$settings['coupon_notifications_show_on_pages'] = isset($_POST['coupon_notifications_show_on_pages']) && $_POST['coupon_notifications_show_on_pages'] === '1';
				$settings['coupon_notifications_collapse_threshold'] = isset($_POST['coupon_notifications_collapse_threshold'])
					? max(0, absint($_POST['coupon_notifications_collapse_threshold']))
					: 1;
				$settings['coupon_notifications_clear_coupons_when_cart_empty'] = isset($_POST['coupon_notifications_clear_coupons_when_cart_empty']) && $_POST['coupon_notifications_clear_coupons_when_cart_empty'] === '1';
				$settings['coupon_notifications_debug'] = isset($_POST['coupon_notifications_debug']) && $_POST['coupon_notifications_debug'] === '1';
				$settings['coupon_notifications_hide_store_credit'] = isset($_POST['coupon_notifications_hide_store_credit']) && $_POST['coupon_notifications_hide_store_credit'] === '1';
				$settings['coupon_notifications_block_support'] = isset($_POST['coupon_notifications_block_support']) && $_POST['coupon_notifications_block_support'] === '1';
				$settings['coupon_notifications_sort_by_expiration'] = isset($_POST['coupon_notifications_sort_by_expiration']) && $_POST['coupon_notifications_sort_by_expiration'] === '1';
				$settings['coupon_notifications_block_checkout_shipping_notice'] = isset($_POST['coupon_notifications_block_checkout_shipping_notice']) && $_POST['coupon_notifications_block_checkout_shipping_notice'] === '1';
				$settings['coupon_notifications_classic_checkout_shipping_notice'] = isset($_POST['coupon_notifications_classic_checkout_shipping_notice']) && $_POST['coupon_notifications_classic_checkout_shipping_notice'] === '1';
				$settings['coupon_notifications_shipping_notice_title'] = isset($_POST['coupon_notifications_shipping_notice_title']) ? sanitize_text_field(wp_unslash($_POST['coupon_notifications_shipping_notice_title'])) : 'Coupon Notice';
				$settings['coupon_notifications_shipping_notice_description'] = isset($_POST['coupon_notifications_shipping_notice_description']) ? sanitize_textarea_field(wp_unslash($_POST['coupon_notifications_shipping_notice_description'])) : 'Coupons and store credits apply to product prices only, and do not cover shipping costs. Shipping is calculated separately from our shipping carriers.';
				break;

			case 'coupon_remainder':
				$redirect_tab = User_Manager_Core::TAB_ADDONS;
				$settings['coupon_remainder_enabled'] = isset($_POST['coupon_remainder_enabled']) && $_POST['coupon_remainder_enabled'] === '1';
				$settings['coupon_remainder_min_amount'] = isset($_POST['coupon_remainder_min_amount']) ? (float) $_POST['coupon_remainder_min_amount'] : 0;
				$settings['coupon_remainder_source_prefixes'] = isset($_POST['coupon_remainder_source_prefixes']) ? sanitize_textarea_field(wp_unslash($_POST['coupon_remainder_source_prefixes'])) : '';
				$settings['coupon_remainder_source_contains'] = isset($_POST['coupon_remainder_source_contains']) ? sanitize_textarea_field(wp_unslash($_POST['coupon_remainder_source_contains'])) : '';
				$settings['coupon_remainder_source_suffixes'] = isset($_POST['coupon_remainder_source_suffixes']) ? sanitize_textarea_field(wp_unslash($_POST['coupon_remainder_source_suffixes'])) : '';
				$settings['coupon_remainder_generated_prefix'] = isset($_POST['coupon_remainder_generated_prefix']) ? sanitize_text_field(wp_unslash($_POST['coupon_remainder_generated_prefix'])) : '';
				$settings['coupon_remainder_debug'] = isset($_POST['coupon_remainder_debug']) && $_POST['coupon_remainder_debug'] === '1';
				$settings['coupon_remainder_checkout_debug'] = isset($_POST['coupon_remainder_checkout_debug']) && $_POST['coupon_remainder_checkout_debug'] === '1';
				$settings['coupon_remainder_checkout_notice'] = isset($_POST['coupon_remainder_checkout_notice']) && $_POST['coupon_remainder_checkout_notice'] === '1';
				$settings['coupon_remainder_checkout_notice_block'] = isset($_POST['coupon_remainder_checkout_notice_block']) && $_POST['coupon_remainder_checkout_notice_block'] === '1';
				$settings['coupon_remainder_order_received_notice'] = isset($_POST['coupon_remainder_order_received_notice']) && $_POST['coupon_remainder_order_received_notice'] === '1';
				$settings['coupon_remainder_copy_expiration'] = isset($_POST['coupon_remainder_copy_expiration']) && $_POST['coupon_remainder_copy_expiration'] === '1';
				$settings['coupon_remainder_free_shipping'] = isset($_POST['coupon_remainder_free_shipping']) && $_POST['coupon_remainder_free_shipping'] === '1';
				$settings['coupon_remainder_send_email'] = isset($_POST['coupon_remainder_send_email']) && $_POST['coupon_remainder_send_email'] === '1';
				$settings['coupon_remainder_email_template'] = isset($_POST['coupon_remainder_email_template']) ? sanitize_key($_POST['coupon_remainder_email_template']) : '__um_default__';
				break;
			
			case 'general':
			default:
				$settings['default_role'] = isset($_POST['default_role']) ? sanitize_key($_POST['default_role']) : 'customer';
				$settings['default_login_url'] = isset($_POST['default_login_url']) ? sanitize_text_field(wp_unslash($_POST['default_login_url'])) : '/my-account/';
				$settings['paste_default_columns'] = isset($_POST['paste_default_columns']) ? sanitize_text_field(wp_unslash($_POST['paste_default_columns'])) : 'email,first_name,last_name,role,username,password';
				$settings['role_change_alert_enabled'] = isset($_POST['role_change_alert_enabled']) && $_POST['role_change_alert_enabled'] === '1';
				$settings['role_change_alert_roles'] = [];
				if (!empty($_POST['role_change_alert_roles']) && is_array($_POST['role_change_alert_roles'])) {
					$settings['role_change_alert_roles'] = array_map('sanitize_key', wp_unslash($_POST['role_change_alert_roles']));
				}
				$settings['role_change_alert_email'] = isset($_POST['role_change_alert_email']) ? sanitize_email(wp_unslash($_POST['role_change_alert_email'])) : '';
				$settings['log_activity'] = isset($_POST['log_activity']) && $_POST['log_activity'] === '1';
				$settings['log_activity_debug'] = isset($_POST['log_activity_debug']) && $_POST['log_activity_debug'] === '1';
				$settings['log_admin_activity'] = isset($_POST['log_admin_activity']) && $_POST['log_admin_activity'] === '1';
				$settings['enable_view_reports'] = isset($_POST['enable_view_reports']) && $_POST['enable_view_reports'] === '1';
				$settings['update_existing_users'] = isset($_POST['update_existing_users']) && $_POST['update_existing_users'] === '1';
				$settings['add_existing_network_user_to_subsite'] = isset($_POST['add_existing_network_user_to_subsite']) && $_POST['add_existing_network_user_to_subsite'] === '1';
				$settings['deactivate_users_reset_password'] = isset($_POST['deactivate_users_reset_password']) && $_POST['deactivate_users_reset_password'] === '1';
				$settings['deactivate_users_prefix_identity'] = isset($_POST['deactivate_users_prefix_identity']) && $_POST['deactivate_users_prefix_identity'] === '1';
				$settings['rebrand_reset_password_copy'] = isset($_POST['rebrand_reset_password_copy']) && $_POST['rebrand_reset_password_copy'] === '1';
				$settings['show_profile_user_manager_notice'] = isset($_POST['show_profile_user_manager_notice']) && $_POST['show_profile_user_manager_notice'] === '1';
				$settings['show_user_manager_admin_bar_link'] = isset($_POST['show_user_manager_admin_bar_link']) && $_POST['show_user_manager_admin_bar_link'] === '1';
				$settings['show_top_level_admin_menu_item'] = isset($_POST['show_top_level_admin_menu_item']) && $_POST['show_top_level_admin_menu_item'] === '1';
				$settings['plugin_title_override'] = self::sanitize_plugin_title_override(
					isset($_POST['plugin_title_override']) ? wp_unslash($_POST['plugin_title_override']) : ''
				);
				$settings['legacy_broken_shortcodes_noop_list'] = isset($_POST['legacy_broken_shortcodes_noop_list']) ? sanitize_text_field(wp_unslash($_POST['legacy_broken_shortcodes_noop_list'])) : '';
				$settings['coupon_email_converter'] = isset($_POST['coupon_email_converter']) && $_POST['coupon_email_converter'] === '1';
				$settings['coupon_show_email_column'] = isset($_POST['coupon_show_email_column']) && $_POST['coupon_show_email_column'] === '1';
				$settings['coupon_code_url_param_enabled'] = isset($_POST['coupon_code_url_param_enabled']) && $_POST['coupon_code_url_param_enabled'] === '1';
				$param_name = isset($_POST['coupon_code_url_param_name']) ? sanitize_key(str_replace(' ', '-', wp_unslash($_POST['coupon_code_url_param_name']))) : 'coupon-code';
				$settings['coupon_code_url_param_name'] = $param_name !== '' ? $param_name : 'coupon-code';
				$settings['sftp_directories'] = isset($_POST['sftp_directories']) ? sanitize_textarea_field(wp_unslash($_POST['sftp_directories'])) : '';
				$settings['openai_api_key'] = isset($_POST['openai_api_key']) ? sanitize_text_field(wp_unslash($_POST['openai_api_key'])) : '';
				$settings['simple_texting_api_token'] = isset($_POST['simple_texting_api_token']) ? sanitize_text_field(wp_unslash($_POST['simple_texting_api_token'])) : '';
				$settings['send_from_name'] = isset($_POST['send_from_name']) ? sanitize_text_field(wp_unslash($_POST['send_from_name'])) : '';
				$settings['send_from_email'] = isset($_POST['send_from_email']) ? sanitize_email(wp_unslash($_POST['send_from_email'])) : '';
				$settings['reply_to_email'] = isset($_POST['reply_to_email']) ? sanitize_email(wp_unslash($_POST['reply_to_email'])) : '';
				$settings['throttle_emails_enabled'] = isset($_POST['throttle_emails_enabled']) && $_POST['throttle_emails_enabled'] === '1';
				$settings['throttle_emails_count'] = isset($_POST['throttle_emails_count']) ? max(1, absint($_POST['throttle_emails_count'])) : 50;
				break;

			case 'blocks':
				$redirect_tab = User_Manager_Core::TAB_BLOCKS;
				$is_blocks_temp_disable_only = isset($_POST['um_save_temporary_disable_only'])
					&& $_POST['um_save_temporary_disable_only'] !== ''
					&& (
						!isset($_POST['submit'])
						|| $_POST['submit'] === ''
						|| $_POST['submit'] === '0'
					)
					&& empty($_POST['block_section']);
				if ($is_blocks_temp_disable_only) {
					$settings = User_Manager_Core::get_raw_settings();
					if (!is_array($settings)) {
						$settings = [];
					}
				}
				$settings['temporarily_disable_blocks'] = isset($_POST['temporarily_disable_blocks']) && $_POST['temporarily_disable_blocks'] === '1';
				if ($is_blocks_temp_disable_only) {
					break;
				}
				$settings['page_block_subpages_grid_enabled'] = isset($_POST['page_block_subpages_grid_enabled']) && $_POST['page_block_subpages_grid_enabled'] === '1';
				$settings['page_block_tabbed_content_area_enabled'] = isset($_POST['page_block_tabbed_content_area_enabled']) && $_POST['page_block_tabbed_content_area_enabled'] === '1';
				$settings['page_block_simple_icons_enabled'] = isset($_POST['page_block_simple_icons_enabled']) && $_POST['page_block_simple_icons_enabled'] === '1';
				$settings['page_block_menu_tiles_enabled'] = isset($_POST['page_block_menu_tiles_enabled']) && $_POST['page_block_menu_tiles_enabled'] === '1';
				$settings['media_library_tags_enabled'] = isset($_POST['media_library_tags_enabled']) && $_POST['media_library_tags_enabled'] === '1';
				$settings['media_library_tag_gallery_block_enabled'] = isset($_POST['media_library_tag_gallery_block_enabled']) && $_POST['media_library_tag_gallery_block_enabled'] === '1';
				$settings['media_library_tag_video_library_enabled'] = isset($_POST['media_library_tag_video_library_enabled']) && $_POST['media_library_tag_video_library_enabled'] === '1';
				$settings['media_library_tag_video_library_display_title'] = isset($_POST['media_library_tag_video_library_display_title']) && $_POST['media_library_tag_video_library_display_title'] === '1';
				$settings['media_library_tag_video_library_display_description'] = isset($_POST['media_library_tag_video_library_display_description']) && $_POST['media_library_tag_video_library_display_description'] === '1';
				$settings['media_library_tag_gallery_columns_desktop'] = isset($_POST['media_library_tag_gallery_columns_desktop']) ? max(1, min(8, absint($_POST['media_library_tag_gallery_columns_desktop']))) : 4;
				$settings['media_library_tag_gallery_columns_desktop_lt_50'] = isset($_POST['media_library_tag_gallery_columns_desktop_lt_50']) ? max(1, min(8, absint($_POST['media_library_tag_gallery_columns_desktop_lt_50']))) : $settings['media_library_tag_gallery_columns_desktop'];
				$settings['media_library_tag_gallery_columns_desktop_lt_25'] = isset($_POST['media_library_tag_gallery_columns_desktop_lt_25']) ? max(1, min(8, absint($_POST['media_library_tag_gallery_columns_desktop_lt_25']))) : $settings['media_library_tag_gallery_columns_desktop_lt_50'];
				$settings['media_library_tag_gallery_columns_desktop_lt_10'] = isset($_POST['media_library_tag_gallery_columns_desktop_lt_10']) ? max(1, min(8, absint($_POST['media_library_tag_gallery_columns_desktop_lt_10']))) : $settings['media_library_tag_gallery_columns_desktop_lt_25'];
				$settings['media_library_tag_gallery_disable_css_crop_under_total'] = isset($_POST['media_library_tag_gallery_disable_css_crop_under_total'])
					? max(0, absint($_POST['media_library_tag_gallery_disable_css_crop_under_total']))
					: 0;
				$settings['media_library_tag_gallery_columns_mobile'] = isset($_POST['media_library_tag_gallery_columns_mobile']) ? max(1, min(4, absint($_POST['media_library_tag_gallery_columns_mobile']))) : 2;
				$gallery_sort = isset($_POST['media_library_tag_gallery_sort_order']) ? sanitize_key(wp_unslash($_POST['media_library_tag_gallery_sort_order'])) : 'date_desc';
				$allowed_gallery_sort = ['date_asc', 'date_desc', 'id_asc', 'id_desc', 'filename_asc', 'filename_desc', 'caption_asc', 'caption_desc', 'random'];
				$settings['media_library_tag_gallery_sort_order'] = in_array($gallery_sort, $allowed_gallery_sort, true) ? $gallery_sort : 'date_desc';
				$gallery_size = isset($_POST['media_library_tag_gallery_file_size']) ? sanitize_key(wp_unslash($_POST['media_library_tag_gallery_file_size'])) : 'thumbnail';
				$allowed_gallery_sizes = ['thumbnail', 'medium', 'large', 'full'];
				if (function_exists('get_intermediate_image_sizes')) {
					$registered_sizes = get_intermediate_image_sizes();
					if (is_array($registered_sizes)) {
						foreach ($registered_sizes as $registered_size) {
							$registered_size = sanitize_key((string) $registered_size);
							if ($registered_size !== '' && !in_array($registered_size, $allowed_gallery_sizes, true)) {
								$allowed_gallery_sizes[] = $registered_size;
							}
						}
					}
				}
				$settings['media_library_tag_gallery_file_size'] = in_array($gallery_size, $allowed_gallery_sizes, true) ? $gallery_size : 'thumbnail';
				$gallery_style = isset($_POST['media_library_tag_gallery_style']) ? sanitize_key(wp_unslash($_POST['media_library_tag_gallery_style'])) : 'uniform_grid';
				$allowed_gallery_styles = array_keys(User_Manager_Core::get_media_library_gallery_style_options());
				$settings['media_library_tag_gallery_style'] = in_array($gallery_style, $allowed_gallery_styles, true) ? $gallery_style : 'uniform_grid';
				$settings['media_library_tag_gallery_accent_color'] = isset($_POST['media_library_tag_gallery_accent_color'])
					? sanitize_hex_color(wp_unslash($_POST['media_library_tag_gallery_accent_color']))
					: '';
				$settings['media_library_tag_gallery_page_limit'] = isset($_POST['media_library_tag_gallery_page_limit']) ? max(0, absint($_POST['media_library_tag_gallery_page_limit'])) : 0;
				$gallery_link_to = isset($_POST['media_library_tag_gallery_link_to']) ? sanitize_key(wp_unslash($_POST['media_library_tag_gallery_link_to'])) : 'lightbox';
				$allowed_gallery_link_to = array_keys(User_Manager_Core::get_media_library_gallery_link_to_options());
				$settings['media_library_tag_gallery_link_to'] = in_array($gallery_link_to, $allowed_gallery_link_to, true) ? $gallery_link_to : 'lightbox';
				$gallery_album_description_position = isset($_POST['media_library_tag_gallery_album_description_position']) ? sanitize_key(wp_unslash($_POST['media_library_tag_gallery_album_description_position'])) : 'none';
				$allowed_album_description_positions = array_keys(User_Manager_Core::get_media_library_gallery_album_description_position_options());
				if (!in_array($gallery_album_description_position, $allowed_album_description_positions, true)) {
					$gallery_album_description_position = 'none';
				}
				$settings['media_library_tag_gallery_album_description_position'] = $gallery_album_description_position;
				$settings['media_library_tag_gallery_featured_image_separate_column'] = isset($_POST['media_library_tag_gallery_featured_image_separate_column']) && $_POST['media_library_tag_gallery_featured_image_separate_column'] === '1';
				$settings['media_library_tag_gallery_hide_featured_image_if_no_description_or_bullets'] = isset($_POST['media_library_tag_gallery_hide_featured_image_if_no_description_or_bullets']) && $_POST['media_library_tag_gallery_hide_featured_image_if_no_description_or_bullets'] === '1';
				$single_video_three_column_compact_enabled = false;
				if (isset($_POST['media_library_tag_gallery_single_video_three_column_combined_row'])) {
					$single_video_three_column_compact_enabled = $_POST['media_library_tag_gallery_single_video_three_column_combined_row'] === '1';
				} elseif (isset($_POST['media_library_tag_gallery_single_video_three_column_alternative_layout'])) {
					$single_video_three_column_compact_enabled = $_POST['media_library_tag_gallery_single_video_three_column_alternative_layout'] === '1';
				}
				$settings['media_library_tag_gallery_single_video_three_column_combined_row'] = $single_video_three_column_compact_enabled;
				// Keep legacy key in sync for backward compatibility with older UI reads.
				$settings['media_library_tag_gallery_single_video_three_column_alternative_layout'] = $single_video_three_column_compact_enabled;
				$settings['media_library_tag_gallery_10plus_bullets_li_inline_styles'] = isset($_POST['media_library_tag_gallery_10plus_bullets_li_inline_styles'])
					? sanitize_text_field(wp_unslash($_POST['media_library_tag_gallery_10plus_bullets_li_inline_styles']))
					: '';
				$gallery_description_display = isset($_POST['media_library_tag_gallery_description_display']) ? sanitize_key(wp_unslash($_POST['media_library_tag_gallery_description_display'])) : 'none';
				$allowed_description_display = array_keys(User_Manager_Core::get_media_library_gallery_description_display_options());
				if (!in_array($gallery_description_display, $allowed_description_display, true)) {
					$gallery_description_display = 'none';
				}
				$settings['media_library_tag_gallery_description_display'] = $gallery_description_display;
				$gallery_description_value = isset($_POST['media_library_tag_gallery_description_value']) ? sanitize_key(wp_unslash($_POST['media_library_tag_gallery_description_value'])) : 'caption';
				$allowed_description_values = array_keys(User_Manager_Core::get_media_library_gallery_description_value_options());
				if (!in_array($gallery_description_value, $allowed_description_values, true)) {
					$gallery_description_value = 'caption';
				}
				$settings['media_library_tag_gallery_description_value'] = $gallery_description_value;
				$settings['media_library_tag_gallery_lightbox_prev_next_keyboard'] = isset($_POST['media_library_tag_gallery_lightbox_prev_next_keyboard']) && $_POST['media_library_tag_gallery_lightbox_prev_next_keyboard'] === '1';
				$settings['media_library_tag_gallery_lightbox_swipe_navigation'] = isset($_POST['media_library_tag_gallery_lightbox_swipe_navigation']) && $_POST['media_library_tag_gallery_lightbox_swipe_navigation'] === '1';
				$settings['media_library_tag_gallery_lightbox_tap_side_navigation'] = isset($_POST['media_library_tag_gallery_lightbox_tap_side_navigation']) && $_POST['media_library_tag_gallery_lightbox_tap_side_navigation'] === '1';
				$settings['media_library_tag_gallery_lightbox_slideshow_button'] = isset($_POST['media_library_tag_gallery_lightbox_slideshow_button']) && $_POST['media_library_tag_gallery_lightbox_slideshow_button'] === '1';
				$settings['media_library_tag_gallery_lightbox_slideshow_seconds_per_photo'] = isset($_POST['media_library_tag_gallery_lightbox_slideshow_seconds_per_photo'])
					? max(1, min(60, absint($_POST['media_library_tag_gallery_lightbox_slideshow_seconds_per_photo'])))
					: 3;
				$lightbox_transition = isset($_POST['media_library_tag_gallery_lightbox_slideshow_transition'])
					? sanitize_key(wp_unslash($_POST['media_library_tag_gallery_lightbox_slideshow_transition']))
					: 'none';
				$allowed_lightbox_transitions = ['none', 'crossfade', 'slide_left'];
				$settings['media_library_tag_gallery_lightbox_slideshow_transition'] = in_array($lightbox_transition, $allowed_lightbox_transitions, true)
					? $lightbox_transition
					: 'none';
				$settings['media_library_tag_gallery_lightbox_modal_background_color'] = isset($_POST['media_library_tag_gallery_lightbox_modal_background_color'])
					? sanitize_hex_color(wp_unslash($_POST['media_library_tag_gallery_lightbox_modal_background_color']))
					: '';
				$settings['media_library_tag_gallery_lightbox_modal_text_color'] = isset($_POST['media_library_tag_gallery_lightbox_modal_text_color'])
					? sanitize_hex_color(wp_unslash($_POST['media_library_tag_gallery_lightbox_modal_text_color']))
					: '';
				$settings['media_library_tag_gallery_simple_lightbox_thumbnail_click'] = isset($_POST['media_library_tag_gallery_simple_lightbox_thumbnail_click'])
					&& $_POST['media_library_tag_gallery_simple_lightbox_thumbnail_click'] === '1';
				$settings['media_library_tag_gallery_hide_featured_image_duplicate_in_tagged_images'] = isset($_POST['media_library_tag_gallery_hide_featured_image_duplicate_in_tagged_images'])
					&& $_POST['media_library_tag_gallery_hide_featured_image_duplicate_in_tagged_images'] === '1';
				$settings['media_library_tag_gallery_exclude_logged_in_users_from_tracking'] = isset($_POST['media_library_tag_gallery_exclude_logged_in_users_from_tracking'])
					&& $_POST['media_library_tag_gallery_exclude_logged_in_users_from_tracking'] === '1';
				$settings['media_library_tag_gallery_exclude_wp_administrator_users_from_tracking'] = isset($_POST['media_library_tag_gallery_exclude_wp_administrator_users_from_tracking'])
					&& $_POST['media_library_tag_gallery_exclude_wp_administrator_users_from_tracking'] === '1';
				$settings['media_library_tag_gallery_featured_image_max_width_px'] = isset($_POST['media_library_tag_gallery_featured_image_max_width_px'])
					? max(0, min(2000, absint($_POST['media_library_tag_gallery_featured_image_max_width_px'])))
					: 360;
				$settings['media_library_tags_show_tags_on_thumbnails_bulk_select'] = isset($_POST['media_library_tags_show_tags_on_thumbnails_bulk_select']) && $_POST['media_library_tags_show_tags_on_thumbnails_bulk_select'] === '1';
				$settings['media_library_tags_sticky_bulk_toolbar_mobile'] = isset($_POST['media_library_tags_sticky_bulk_toolbar_mobile']) && $_POST['media_library_tags_sticky_bulk_toolbar_mobile'] === '1';
				$settings['media_library_tag_gallery_hidden_frontend_tags'] = isset($_POST['media_library_tag_gallery_hidden_frontend_tags'])
					? sanitize_text_field(wp_unslash($_POST['media_library_tag_gallery_hidden_frontend_tags']))
					: '';
				$should_reset_media_library_tag_reports = isset($_POST['media_library_tag_reports_reset']) && $_POST['media_library_tag_reports_reset'] === '1';
				if ($should_reset_media_library_tag_reports) {
					$media_library_tag_reports_reset_counts = User_Manager_Core::clear_media_library_tag_reports_data();
				}
				break;

			case 'addons':
				$redirect_tab = User_Manager_Core::TAB_ADDONS;
				$is_addons_temp_disable_only = isset($_POST['um_save_temporary_disable_only'])
					&& $_POST['um_save_temporary_disable_only'] !== ''
					&& (
						!isset($_POST['submit'])
						|| $_POST['submit'] === ''
						|| $_POST['submit'] === '0'
					)
					&& empty($_POST['addon_section']);
				if ($is_addons_temp_disable_only) {
					$settings = User_Manager_Core::get_raw_settings();
					if (!is_array($settings)) {
						$settings = [];
					}
				}
				$settings['temporarily_disable_addons'] = isset($_POST['temporarily_disable_addons']) && $_POST['temporarily_disable_addons'] === '1';
				if ($is_addons_temp_disable_only) {
					break;
				}
				$settings['openai_content_generator_enabled'] = isset($_POST['openai_content_generator_enabled']) && $_POST['openai_content_generator_enabled'] === '1';
				$settings['openai_blog_post_idea_generator_enabled'] = isset($_POST['openai_blog_post_idea_generator_enabled']) && $_POST['openai_blog_post_idea_generator_enabled'] === '1';
				$settings['product_notification_enabled'] = isset($_POST['product_notification_enabled']) && $_POST['product_notification_enabled'] === '1';
				$settings['product_notification_bg_color'] = isset($_POST['product_notification_bg_color'])
					? sanitize_hex_color(wp_unslash($_POST['product_notification_bg_color']))
					: '';
				$settings['product_notification_text_color'] = isset($_POST['product_notification_text_color'])
					? sanitize_hex_color(wp_unslash($_POST['product_notification_text_color']))
					: '';
				$settings['product_notification_button_bg_color'] = isset($_POST['product_notification_button_bg_color'])
					? sanitize_hex_color(wp_unslash($_POST['product_notification_button_bg_color']))
					: '';
				$settings['product_notification_button_text_color'] = isset($_POST['product_notification_button_text_color'])
					? sanitize_hex_color(wp_unslash($_POST['product_notification_button_text_color']))
					: '';
				$settings['product_notification_button_hover_bg_color'] = isset($_POST['product_notification_button_hover_bg_color'])
					? sanitize_hex_color(wp_unslash($_POST['product_notification_button_hover_bg_color']))
					: '';
				$settings['product_notification_button_hover_text_color'] = isset($_POST['product_notification_button_hover_text_color'])
					? sanitize_hex_color(wp_unslash($_POST['product_notification_button_hover_text_color']))
					: '';
				$settings['product_notification_icon_color'] = isset($_POST['product_notification_icon_color'])
					? sanitize_hex_color(wp_unslash($_POST['product_notification_icon_color']))
					: '';
				$settings['emali_log_enabled'] = isset($_POST['emali_log_enabled']) && $_POST['emali_log_enabled'] === '1';
				$settings['emali_log_auto_delete_days'] = isset($_POST['emali_log_auto_delete_days'])
					? max(0, absint($_POST['emali_log_auto_delete_days']))
					: 0;
				$settings['search_redirect_by_sku'] = isset($_POST['search_redirect_by_sku']) && $_POST['search_redirect_by_sku'] === '1';
				$settings['plugin_tags_notes_enabled'] = isset($_POST['plugin_tags_notes_enabled']) && $_POST['plugin_tags_notes_enabled'] === '1';
				$settings['seo_basics_enabled'] = isset($_POST['seo_basics_enabled']) && $_POST['seo_basics_enabled'] === '1';
				$settings['um_quick_search_enabled'] = isset($_POST['um_quick_search_enabled']) && $_POST['um_quick_search_enabled'] === '1';
				$settings['um_quick_search_priority_post_types'] = [];
				if (isset($_POST['um_quick_search_priority_post_types']) && is_array($_POST['um_quick_search_priority_post_types'])) {
					$allowed_post_types = get_post_types(['show_ui' => true], 'names');
					$allowed_post_types = is_array($allowed_post_types) ? array_map('sanitize_key', $allowed_post_types) : [];
					$selected_priority_post_types = array_map(
						'sanitize_key',
						array_map('wp_unslash', $_POST['um_quick_search_priority_post_types'])
					);
					$settings['um_quick_search_priority_post_types'] = array_values(array_unique(array_intersect($selected_priority_post_types, $allowed_post_types)));
				}
				$settings['security_hardening_enabled'] = isset($_POST['security_hardening_enabled']) && $_POST['security_hardening_enabled'] === '1';
				$settings['security_hardening_block_rest_user_enumeration'] = isset($_POST['security_hardening_block_rest_user_enumeration']) && $_POST['security_hardening_block_rest_user_enumeration'] === '1';
				$settings['security_hardening_disallow_file_edit'] = isset($_POST['security_hardening_disallow_file_edit']) && $_POST['security_hardening_disallow_file_edit'] === '1';
				$settings['security_hardening_disallow_file_mods'] = isset($_POST['security_hardening_disallow_file_mods']) && $_POST['security_hardening_disallow_file_mods'] === '1';
				$settings['security_hardening_force_ssl_admin'] = isset($_POST['security_hardening_force_ssl_admin']) && $_POST['security_hardening_force_ssl_admin'] === '1';
				$settings['security_hardening_hide_wp_version'] = isset($_POST['security_hardening_hide_wp_version']) && $_POST['security_hardening_hide_wp_version'] === '1';
				$settings['fatal_error_debugger_enabled'] = isset($_POST['fatal_error_debugger_enabled']) && $_POST['fatal_error_debugger_enabled'] === '1';
				$settings['fatal_error_debugger_email'] = isset($_POST['fatal_error_debugger_email']) ? sanitize_email(wp_unslash($_POST['fatal_error_debugger_email'])) : '';
				$settings['openai_prompt_append'] = isset($_POST['openai_prompt_append']) ? sanitize_textarea_field(wp_unslash($_POST['openai_prompt_append'])) : '';
				$settings['openai_page_meta_box'] = isset($_POST['openai_page_meta_box']) && $_POST['openai_page_meta_box'] === '1';
				$settings['display_post_meta_meta_box'] = isset($_POST['display_post_meta_meta_box']) && $_POST['display_post_meta_meta_box'] === '1';
				$settings['display_post_meta_post_types'] = [];
				if (isset($_POST['display_post_meta_post_types']) && is_array($_POST['display_post_meta_post_types'])) {
					$allowed_post_types = get_post_types(['show_ui' => true], 'names');
					$allowed_post_types = is_array($allowed_post_types) ? array_map('sanitize_key', $allowed_post_types) : [];
					$selected_post_types = array_map(
						'sanitize_key',
						array_map('wp_unslash', $_POST['display_post_meta_post_types'])
					);
					$settings['display_post_meta_post_types'] = array_values(array_unique(array_intersect($selected_post_types, $allowed_post_types)));
				}
				$settings['display_post_meta_allowed_roles'] = [];
				if (isset($_POST['display_post_meta_allowed_roles']) && is_array($_POST['display_post_meta_allowed_roles'])) {
					$allowed_roles = array_keys(User_Manager_Core::get_user_roles());
					$allowed_roles = array_map('sanitize_key', $allowed_roles);
					$selected_roles = array_map(
						'sanitize_key',
						array_map('wp_unslash', $_POST['display_post_meta_allowed_roles'])
					);
					$settings['display_post_meta_allowed_roles'] = array_values(array_unique(array_intersect($selected_roles, $allowed_roles)));
				}
				$settings['display_post_meta_allowed_users'] = [];
				if (isset($_POST['display_post_meta_allowed_users'])) {
					$raw_identifiers = sanitize_textarea_field(wp_unslash($_POST['display_post_meta_allowed_users']));
					$parts = preg_split('/[\r\n,;]+/', $raw_identifiers);
					$identifiers = [];
					if (is_array($parts)) {
						foreach ($parts as $part) {
							$part = trim((string) $part);
							if ($part === '') {
								continue;
							}
							if (is_email($part)) {
								$email = strtolower(sanitize_email($part));
								if ($email !== '') {
									$identifiers[] = $email;
								}
								continue;
							}
							$username = strtolower(sanitize_user($part, false));
							if ($username !== '') {
								$identifiers[] = $username;
							}
						}
					}
					$settings['display_post_meta_allowed_users'] = array_values(array_unique($identifiers));
				}
				$settings['allow_edit_post_meta'] = isset($_POST['allow_edit_post_meta']) && $_POST['allow_edit_post_meta'] === '1';

				// Coupons for New Users.
				$settings['nuc_enabled'] = isset($_POST['nuc_enabled']) && $_POST['nuc_enabled'] === '1';
				$settings['nuc_when'] = isset($_POST['nuc_when']) ? sanitize_text_field(wp_unslash($_POST['nuc_when'])) : 'after_registration';
				$settings['nuc_template_code'] = isset($_POST['nuc_template_code']) ? sanitize_text_field(wp_unslash($_POST['nuc_template_code'])) : '';
				$settings['nuc_amount_override'] = isset($_POST['nuc_amount_override']) ? sanitize_text_field(wp_unslash($_POST['nuc_amount_override'])) : '';
				$settings['nuc_code_length'] = isset($_POST['nuc_code_length']) ? absint($_POST['nuc_code_length']) : 8;
				$settings['nuc_prefix'] = isset($_POST['nuc_prefix']) ? sanitize_text_field(wp_unslash($_POST['nuc_prefix'])) : '';
				$settings['nuc_postfix'] = isset($_POST['nuc_postfix']) ? sanitize_text_field(wp_unslash($_POST['nuc_postfix'])) : '';
				$settings['nuc_after_date'] = isset($_POST['nuc_after_date']) ? sanitize_text_field(wp_unslash($_POST['nuc_after_date'])) : '';
				$settings['nuc_email_contains'] = isset($_POST['nuc_email_contains']) ? sanitize_text_field(wp_unslash($_POST['nuc_email_contains'])) : '';
				$settings['nuc_email_exclude'] = isset($_POST['nuc_email_exclude']) ? sanitize_text_field(wp_unslash($_POST['nuc_email_exclude'])) : '';
				$settings['nuc_exp_days'] = isset($_POST['nuc_exp_days']) ? absint($_POST['nuc_exp_days']) : 0;
				$settings['nuc_send_email'] = isset($_POST['nuc_send_email']) && $_POST['nuc_send_email'] === '1';
				$settings['nuc_email_template'] = isset($_POST['nuc_email_template']) ? sanitize_key($_POST['nuc_email_template']) : '';
				$settings['nuc_auto_draft_duplicates'] = isset($_POST['nuc_auto_draft_duplicates']) && $_POST['nuc_auto_draft_duplicates'] === '1';
				$settings['nuc_debug_mode'] = isset($_POST['nuc_debug_mode']) && $_POST['nuc_debug_mode'] === '1';
				$settings['nuc_run_everywhere'] = isset($_POST['nuc_run_everywhere']) && $_POST['nuc_run_everywhere'] === '1';
				$settings['nuc_run_my_account'] = isset($_POST['nuc_run_my_account']) && $_POST['nuc_run_my_account'] === '1';
				$settings['nuc_run_cart'] = isset($_POST['nuc_run_cart']) && $_POST['nuc_run_cart'] === '1';
				$settings['nuc_run_checkout'] = isset($_POST['nuc_run_checkout']) && $_POST['nuc_run_checkout'] === '1';
				$settings['nuc_run_product'] = isset($_POST['nuc_run_product']) && $_POST['nuc_run_product'] === '1';
				$settings['nuc_run_shop'] = isset($_POST['nuc_run_shop']) && $_POST['nuc_run_shop'] === '1';
				$settings['nuc_run_home'] = isset($_POST['nuc_run_home']) && $_POST['nuc_run_home'] === '1';

				// Coupon Notifications for Users with Coupons.
				$settings['user_coupon_notifications_enabled'] = isset($_POST['user_coupon_notifications_enabled']) && $_POST['user_coupon_notifications_enabled'] === '1';
				$settings['coupon_notifications_show_on_cart'] = isset($_POST['coupon_notifications_show_on_cart']) && $_POST['coupon_notifications_show_on_cart'] === '1';
				$settings['coupon_notifications_show_on_checkout'] = isset($_POST['coupon_notifications_show_on_checkout']) && $_POST['coupon_notifications_show_on_checkout'] === '1';
				$settings['coupon_notifications_show_on_my_account'] = isset($_POST['coupon_notifications_show_on_my_account']) && $_POST['coupon_notifications_show_on_my_account'] === '1';
				$settings['coupon_notifications_show_on_home'] = isset($_POST['coupon_notifications_show_on_home']) && $_POST['coupon_notifications_show_on_home'] === '1';
				$settings['coupon_notifications_show_on_product'] = isset($_POST['coupon_notifications_show_on_product']) && $_POST['coupon_notifications_show_on_product'] === '1';
				$settings['coupon_notifications_show_on_archives'] = isset($_POST['coupon_notifications_show_on_archives']) && $_POST['coupon_notifications_show_on_archives'] === '1';
				$settings['coupon_notifications_show_on_posts'] = isset($_POST['coupon_notifications_show_on_posts']) && $_POST['coupon_notifications_show_on_posts'] === '1';
				$settings['coupon_notifications_show_on_pages'] = isset($_POST['coupon_notifications_show_on_pages']) && $_POST['coupon_notifications_show_on_pages'] === '1';
				$settings['coupon_notifications_collapse_threshold'] = isset($_POST['coupon_notifications_collapse_threshold'])
					? max(0, absint($_POST['coupon_notifications_collapse_threshold']))
					: 1;
				$settings['coupon_notifications_clear_coupons_when_cart_empty'] = isset($_POST['coupon_notifications_clear_coupons_when_cart_empty']) && $_POST['coupon_notifications_clear_coupons_when_cart_empty'] === '1';
				$settings['coupon_notifications_debug'] = isset($_POST['coupon_notifications_debug']) && $_POST['coupon_notifications_debug'] === '1';
				$settings['coupon_notifications_hide_store_credit'] = isset($_POST['coupon_notifications_hide_store_credit']) && $_POST['coupon_notifications_hide_store_credit'] === '1';
				$settings['coupon_notifications_block_support'] = isset($_POST['coupon_notifications_block_support']) && $_POST['coupon_notifications_block_support'] === '1';
				$settings['coupon_notifications_sort_by_expiration'] = isset($_POST['coupon_notifications_sort_by_expiration']) && $_POST['coupon_notifications_sort_by_expiration'] === '1';
				$settings['coupon_notifications_block_checkout_shipping_notice'] = isset($_POST['coupon_notifications_block_checkout_shipping_notice']) && $_POST['coupon_notifications_block_checkout_shipping_notice'] === '1';
				$settings['coupon_notifications_classic_checkout_shipping_notice'] = isset($_POST['coupon_notifications_classic_checkout_shipping_notice']) && $_POST['coupon_notifications_classic_checkout_shipping_notice'] === '1';
				$settings['coupon_notifications_shipping_notice_title'] = isset($_POST['coupon_notifications_shipping_notice_title']) ? sanitize_text_field(wp_unslash($_POST['coupon_notifications_shipping_notice_title'])) : 'Coupon Notice';
				$settings['coupon_notifications_shipping_notice_description'] = isset($_POST['coupon_notifications_shipping_notice_description']) ? sanitize_textarea_field(wp_unslash($_POST['coupon_notifications_shipping_notice_description'])) : 'Coupons and store credits apply to product prices only, and do not cover shipping costs. Shipping is calculated separately from our shipping carriers.';

				// Coupon Remaining Balances.
				$settings['coupon_remainder_enabled'] = isset($_POST['coupon_remainder_enabled']) && $_POST['coupon_remainder_enabled'] === '1';
				$settings['coupon_remainder_min_amount'] = isset($_POST['coupon_remainder_min_amount']) ? (float) $_POST['coupon_remainder_min_amount'] : 0;
				$settings['coupon_remainder_source_prefixes'] = isset($_POST['coupon_remainder_source_prefixes']) ? sanitize_textarea_field(wp_unslash($_POST['coupon_remainder_source_prefixes'])) : '';
				$settings['coupon_remainder_source_contains'] = isset($_POST['coupon_remainder_source_contains']) ? sanitize_textarea_field(wp_unslash($_POST['coupon_remainder_source_contains'])) : '';
				$settings['coupon_remainder_source_suffixes'] = isset($_POST['coupon_remainder_source_suffixes']) ? sanitize_textarea_field(wp_unslash($_POST['coupon_remainder_source_suffixes'])) : '';
				$settings['coupon_remainder_generated_prefix'] = isset($_POST['coupon_remainder_generated_prefix']) ? sanitize_text_field(wp_unslash($_POST['coupon_remainder_generated_prefix'])) : '';
				$settings['coupon_remainder_debug'] = isset($_POST['coupon_remainder_debug']) && $_POST['coupon_remainder_debug'] === '1';
				$settings['coupon_remainder_checkout_debug'] = isset($_POST['coupon_remainder_checkout_debug']) && $_POST['coupon_remainder_checkout_debug'] === '1';
				$settings['coupon_remainder_checkout_notice'] = isset($_POST['coupon_remainder_checkout_notice']) && $_POST['coupon_remainder_checkout_notice'] === '1';
				$settings['coupon_remainder_checkout_notice_block'] = isset($_POST['coupon_remainder_checkout_notice_block']) && $_POST['coupon_remainder_checkout_notice_block'] === '1';
				$settings['coupon_remainder_order_received_notice'] = isset($_POST['coupon_remainder_order_received_notice']) && $_POST['coupon_remainder_order_received_notice'] === '1';
				$settings['coupon_remainder_copy_expiration'] = isset($_POST['coupon_remainder_copy_expiration']) && $_POST['coupon_remainder_copy_expiration'] === '1';
				$settings['coupon_remainder_free_shipping'] = isset($_POST['coupon_remainder_free_shipping']) && $_POST['coupon_remainder_free_shipping'] === '1';
				$settings['coupon_remainder_send_email'] = isset($_POST['coupon_remainder_send_email']) && $_POST['coupon_remainder_send_email'] === '1';
				$settings['coupon_remainder_email_template'] = isset($_POST['coupon_remainder_email_template']) ? sanitize_key($_POST['coupon_remainder_email_template']) : '__um_default__';

				// Checkout: Ship To Pre-Defined Addresses
				$settings['checkout_ship_to_predefined_enabled'] = isset($_POST['checkout_ship_to_predefined_enabled']) && $_POST['checkout_ship_to_predefined_enabled'] === '1';
				$settings['checkout_ship_to_address_list'] = isset($_POST['checkout_ship_to_address_list']) ? sanitize_textarea_field(wp_unslash($_POST['checkout_ship_to_address_list'])) : '';
				$settings['checkout_ship_to_please_select'] = isset($_POST['checkout_ship_to_please_select']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_please_select'])) : 'Please select';
				$settings['checkout_ship_to_selection_required'] = isset($_POST['checkout_ship_to_selection_required']) && $_POST['checkout_ship_to_selection_required'] === 'yes' ? 'yes' : 'no';
				$settings['checkout_ship_to_required_error'] = isset($_POST['checkout_ship_to_required_error']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_required_error'])) : '';
				$settings['checkout_ship_to_field_hook'] = isset($_POST['checkout_ship_to_field_hook']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_field_hook'])) : 'woocommerce_after_order_notes';
				$settings['checkout_ship_to_default_address'] = isset($_POST['checkout_ship_to_default_address']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_default_address'])) : '';
				$settings['checkout_ship_to_default_city'] = isset($_POST['checkout_ship_to_default_city']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_default_city'])) : '';
				$settings['checkout_ship_to_default_state'] = isset($_POST['checkout_ship_to_default_state']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_default_state'])) : '';
				$settings['checkout_ship_to_default_zip'] = isset($_POST['checkout_ship_to_default_zip']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_default_zip'])) : '';
				$settings['checkout_ship_to_default_country'] = isset($_POST['checkout_ship_to_default_country']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_default_country'])) : '';
				$settings['checkout_ship_to_overwrite_billing'] = isset($_POST['checkout_ship_to_overwrite_billing']) && $_POST['checkout_ship_to_overwrite_billing'] === 'yes' ? 'yes' : 'no';
				$settings['checkout_ship_to_overwrite_shipping'] = isset($_POST['checkout_ship_to_overwrite_shipping']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_overwrite_shipping'])) : 'yes';
				$settings['checkout_ship_to_company_override'] = isset($_POST['checkout_ship_to_company_override']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_company_override'])) : '';
				$settings['checkout_ship_to_first_name_prefix'] = isset($_POST['checkout_ship_to_first_name_prefix']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_first_name_prefix'])) : '';
				$settings['checkout_ship_to_field_label'] = isset($_POST['checkout_ship_to_field_label']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_field_label'])) : '';
				$settings['checkout_ship_to_area_title'] = isset($_POST['checkout_ship_to_area_title']) ? sanitize_text_field(wp_unslash($_POST['checkout_ship_to_area_title'])) : '';
				$settings['checkout_ship_to_single_column'] = isset($_POST['checkout_ship_to_single_column']) && $_POST['checkout_ship_to_single_column'] === 'yes' ? 'yes' : 'no';
				$settings['checkout_ship_to_auto_hide_shipping'] = isset($_POST['checkout_ship_to_auto_hide_shipping']) && $_POST['checkout_ship_to_auto_hide_shipping'] === '1';
				$settings['checkout_ship_to_auto_hide_company'] = isset($_POST['checkout_ship_to_auto_hide_company']) && $_POST['checkout_ship_to_auto_hide_company'] === '1';
				$settings['checkout_ship_to_auto_hide_notes'] = isset($_POST['checkout_ship_to_auto_hide_notes']) && $_POST['checkout_ship_to_auto_hide_notes'] === '1';
				$settings['checkout_ship_to_hide_coupon'] = isset($_POST['checkout_ship_to_hide_coupon']) && $_POST['checkout_ship_to_hide_coupon'] === '1';
				$settings['checkout_ship_to_show_debug'] = isset($_POST['checkout_ship_to_show_debug']) && $_POST['checkout_ship_to_show_debug'] === '1';

				// My Account Site Admin viewer controls.
				$settings['my_account_admin_order_viewer_enabled'] = isset($_POST['my_account_admin_order_viewer_enabled']) && $_POST['my_account_admin_order_viewer_enabled'] === '1';
				$settings['my_account_admin_order_viewer_usernames'] = self::sanitize_username_csv(
					isset($_POST['my_account_admin_order_viewer_usernames']) ? wp_unslash($_POST['my_account_admin_order_viewer_usernames']) : ''
				);
				$settings['my_account_admin_order_viewer_roles'] = self::sanitize_role_keys_array(
					isset($_POST['my_account_admin_order_viewer_roles']) ? wp_unslash($_POST['my_account_admin_order_viewer_roles']) : []
				);
				$settings['my_account_admin_order_status_filters'] = isset($_POST['my_account_admin_order_status_filters'])
					? sanitize_textarea_field(wp_unslash($_POST['my_account_admin_order_status_filters']))
					: '';
				$settings['my_account_admin_order_status_titles'] = self::sanitize_my_account_order_status_title_overrides(
					isset($_POST['my_account_admin_order_status_titles']) ? wp_unslash($_POST['my_account_admin_order_status_titles']) : []
				);
				$settings['my_account_admin_order_hide_status'] = isset($_POST['my_account_admin_order_hide_status']) && $_POST['my_account_admin_order_hide_status'] === '1';
				$settings['my_account_admin_order_add_webtoffee_download_invoice_button'] = isset($_POST['my_account_admin_order_add_webtoffee_download_invoice_button']) && $_POST['my_account_admin_order_add_webtoffee_download_invoice_button'] === '1';
				$settings['my_account_admin_order_add_webtoffee_print_invoice_button'] = isset($_POST['my_account_admin_order_add_webtoffee_print_invoice_button']) && $_POST['my_account_admin_order_add_webtoffee_print_invoice_button'] === '1';
				$settings['my_account_admin_order_approval_usernames'] = self::sanitize_username_csv(
					isset($_POST['my_account_admin_order_approval_usernames']) ? wp_unslash($_POST['my_account_admin_order_approval_usernames']) : ''
				);
				$settings['my_account_admin_order_approval_roles'] = self::sanitize_role_keys_array(
					isset($_POST['my_account_admin_order_approval_roles']) ? wp_unslash($_POST['my_account_admin_order_approval_roles']) : []
				);
				$settings['my_account_admin_order_approve_button_label'] = isset($_POST['my_account_admin_order_approve_button_label'])
					? sanitize_text_field(wp_unslash($_POST['my_account_admin_order_approve_button_label']))
					: 'Move to Processing';
				$approve_button_bg = isset($_POST['my_account_admin_order_approve_button_background_color'])
					? sanitize_hex_color(wp_unslash($_POST['my_account_admin_order_approve_button_background_color']))
					: '';
				$settings['my_account_admin_order_approve_button_background_color'] = $approve_button_bg ? $approve_button_bg : '';
				$settings['my_account_admin_order_decline_button_label'] = isset($_POST['my_account_admin_order_decline_button_label'])
					? sanitize_text_field(wp_unslash($_POST['my_account_admin_order_decline_button_label']))
					: 'Move to Canceled';
				$decline_button_bg = isset($_POST['my_account_admin_order_decline_button_background_color'])
					? sanitize_hex_color(wp_unslash($_POST['my_account_admin_order_decline_button_background_color']))
					: '';
				$settings['my_account_admin_order_decline_button_background_color'] = $decline_button_bg ? $decline_button_bg : '';
				$settings['my_account_admin_order_default_pending_enabled'] = isset($_POST['my_account_admin_order_default_pending_enabled']) && $_POST['my_account_admin_order_default_pending_enabled'] === '1';
				$settings['my_account_admin_order_additional_meta_fields'] = isset($_POST['my_account_admin_order_additional_meta_fields'])
					? sanitize_textarea_field(wp_unslash($_POST['my_account_admin_order_additional_meta_fields']))
					: '';
				$settings['my_account_admin_order_list_additional_meta_fields'] = isset($_POST['my_account_admin_order_list_additional_meta_fields'])
					? sanitize_textarea_field(wp_unslash($_POST['my_account_admin_order_list_additional_meta_fields']))
					: '';
				$settings['my_account_admin_order_list_additional_flag_fields'] = isset($_POST['my_account_admin_order_list_additional_flag_fields'])
					? sanitize_textarea_field(wp_unslash($_POST['my_account_admin_order_list_additional_flag_fields']))
					: '';
				$settings['my_account_admin_order_viewer_show_meta'] = isset($_POST['my_account_admin_order_viewer_show_meta']) && $_POST['my_account_admin_order_viewer_show_meta'] === '1';
				$settings['my_account_admin_product_viewer_enabled'] = isset($_POST['my_account_admin_product_viewer_enabled']) && $_POST['my_account_admin_product_viewer_enabled'] === '1';
				$settings['my_account_admin_product_viewer_usernames'] = self::sanitize_username_csv(
					isset($_POST['my_account_admin_product_viewer_usernames']) ? wp_unslash($_POST['my_account_admin_product_viewer_usernames']) : ''
				);
				$settings['my_account_admin_product_viewer_roles'] = self::sanitize_role_keys_array(
					isset($_POST['my_account_admin_product_viewer_roles']) ? wp_unslash($_POST['my_account_admin_product_viewer_roles']) : []
				);
				$settings['my_account_admin_product_viewer_show_meta'] = isset($_POST['my_account_admin_product_viewer_show_meta']) && $_POST['my_account_admin_product_viewer_show_meta'] === '1';
				$settings['my_account_admin_coupon_viewer_enabled'] = isset($_POST['my_account_admin_coupon_viewer_enabled']) && $_POST['my_account_admin_coupon_viewer_enabled'] === '1';
				$settings['my_account_admin_coupon_viewer_usernames'] = self::sanitize_username_csv(
					isset($_POST['my_account_admin_coupon_viewer_usernames']) ? wp_unslash($_POST['my_account_admin_coupon_viewer_usernames']) : ''
				);
				$settings['my_account_admin_coupon_viewer_roles'] = self::sanitize_role_keys_array(
					isset($_POST['my_account_admin_coupon_viewer_roles']) ? wp_unslash($_POST['my_account_admin_coupon_viewer_roles']) : []
				);
				$settings['my_account_admin_coupon_viewer_show_meta'] = isset($_POST['my_account_admin_coupon_viewer_show_meta']) && $_POST['my_account_admin_coupon_viewer_show_meta'] === '1';
				$settings['my_account_admin_user_viewer_enabled'] = isset($_POST['my_account_admin_user_viewer_enabled']) && $_POST['my_account_admin_user_viewer_enabled'] === '1';
				$settings['my_account_admin_user_viewer_usernames'] = self::sanitize_username_csv(
					isset($_POST['my_account_admin_user_viewer_usernames']) ? wp_unslash($_POST['my_account_admin_user_viewer_usernames']) : ''
				);
				$settings['my_account_admin_user_viewer_roles'] = self::sanitize_role_keys_array(
					isset($_POST['my_account_admin_user_viewer_roles']) ? wp_unslash($_POST['my_account_admin_user_viewer_roles']) : []
				);
				$settings['my_account_admin_user_viewer_show_meta'] = isset($_POST['my_account_admin_user_viewer_show_meta']) && $_POST['my_account_admin_user_viewer_show_meta'] === '1';
				$settings['my_account_site_admin_enabled'] = isset($_POST['my_account_site_admin_enabled']) && $_POST['my_account_site_admin_enabled'] === '1';
				$settings['my_account_coupon_screen_enabled'] = isset($_POST['my_account_coupon_screen_enabled']) && $_POST['my_account_coupon_screen_enabled'] === '1';
				$menu_title = isset($_POST['my_account_coupon_screen_menu_title']) ? sanitize_text_field(wp_unslash($_POST['my_account_coupon_screen_menu_title'])) : 'Coupons';
				$page_title = isset($_POST['my_account_coupon_screen_page_title']) ? sanitize_text_field(wp_unslash($_POST['my_account_coupon_screen_page_title'])) : 'Coupons';
				$settings['my_account_coupon_screen_menu_title'] = $menu_title !== '' ? $menu_title : 'Coupons';
				$settings['my_account_coupon_screen_page_title'] = $page_title !== '' ? $page_title : 'Coupons';
				$settings['my_account_coupon_screen_page_description'] = isset($_POST['my_account_coupon_screen_page_description']) ? sanitize_textarea_field(wp_unslash($_POST['my_account_coupon_screen_page_description'])) : '';
				$settings['my_account_menu_tiles_enabled'] = isset($_POST['my_account_menu_tiles_enabled']) && $_POST['my_account_menu_tiles_enabled'] === '1';
				$settings['my_account_menu_tiles_per_row'] = isset($_POST['my_account_menu_tiles_per_row']) ? max(1, absint($_POST['my_account_menu_tiles_per_row'])) : 4;
				$settings['my_account_menu_tiles_min_height'] = isset($_POST['my_account_menu_tiles_min_height']) ? max(1, absint($_POST['my_account_menu_tiles_min_height'])) : 80;
				$settings['cart_price_per_piece_enabled'] = isset($_POST['cart_price_per_piece_enabled']) && $_POST['cart_price_per_piece_enabled'] === '1';
				$settings['cart_price_per_piece_enable_cart_display'] = isset($_POST['cart_price_per_piece_enable_cart_display']) && $_POST['cart_price_per_piece_enable_cart_display'] === '1';
				$settings['cart_price_per_piece_enable_order_display'] = isset($_POST['cart_price_per_piece_enable_order_display']) && $_POST['cart_price_per_piece_enable_order_display'] === '1';
				$settings['cart_price_per_piece_suffix_text'] = isset($_POST['cart_price_per_piece_suffix_text']) ? sanitize_text_field(wp_unslash($_POST['cart_price_per_piece_suffix_text'])) : '/ea';
				$cart_price_per_piece_font_size = isset($_POST['cart_price_per_piece_font_size']) ? sanitize_text_field(wp_unslash($_POST['cart_price_per_piece_font_size'])) : '12px';
				$allowed_cart_price_per_piece_font_sizes = ['10px', '11px', '12px', '13px', '14px'];
				$settings['cart_price_per_piece_font_size'] = in_array($cart_price_per_piece_font_size, $allowed_cart_price_per_piece_font_sizes, true) ? $cart_price_per_piece_font_size : '12px';
				$cart_price_per_piece_color = isset($_POST['cart_price_per_piece_text_color']) ? sanitize_hex_color(wp_unslash($_POST['cart_price_per_piece_text_color'])) : '#666666';
				$settings['cart_price_per_piece_text_color'] = $cart_price_per_piece_color ? $cart_price_per_piece_color : '#666666';
				$settings['cart_total_items_enabled'] = isset($_POST['cart_total_items_enabled']) && $_POST['cart_total_items_enabled'] === '1';
				$settings['cart_total_items_copy'] = isset($_POST['cart_total_items_copy']) ? sanitize_text_field(wp_unslash($_POST['cart_total_items_copy'])) : 'Total Items:';
				$settings['cart_total_items_show_on_cart'] = isset($_POST['cart_total_items_show_on_cart']) && $_POST['cart_total_items_show_on_cart'] === '1';
				$settings['cart_total_items_show_on_checkout'] = isset($_POST['cart_total_items_show_on_checkout']) && $_POST['cart_total_items_show_on_checkout'] === '1';
				$settings['cart_total_items_cart_above'] = isset($_POST['cart_total_items_cart_above']) && $_POST['cart_total_items_cart_above'] === '1';
				$settings['cart_total_items_cart_below'] = isset($_POST['cart_total_items_cart_below']) && $_POST['cart_total_items_cart_below'] === '1';
				$settings['cart_total_items_checkout_above'] = isset($_POST['cart_total_items_checkout_above']) && $_POST['cart_total_items_checkout_above'] === '1';
				$settings['cart_total_items_checkout_below'] = isset($_POST['cart_total_items_checkout_below']) && $_POST['cart_total_items_checkout_below'] === '1';
				$settings['bulk_page_creator_enabled'] = isset($_POST['bulk_page_creator_enabled']) && $_POST['bulk_page_creator_enabled'] === '1';
				$settings['bulk_page_creator_max_tokens'] = isset($_POST['bulk_page_creator_max_tokens']) ? max(100, min(8000, absint($_POST['bulk_page_creator_max_tokens']))) : 2000;
				$bulk_page_creator_temperature = isset($_POST['bulk_page_creator_temperature']) ? (float) wp_unslash($_POST['bulk_page_creator_temperature']) : 0.7;
				if ($bulk_page_creator_temperature < 0) {
					$bulk_page_creator_temperature = 0;
				}
				if ($bulk_page_creator_temperature > 1) {
					$bulk_page_creator_temperature = 1;
				}
				$settings['bulk_page_creator_temperature'] = $bulk_page_creator_temperature;
				$settings['bulk_page_creator_image_search_count'] = isset($_POST['bulk_page_creator_image_search_count']) ? max(1, min(10, absint($_POST['bulk_page_creator_image_search_count']))) : 3;
				$settings['bulk_page_creator_auto_publish'] = isset($_POST['bulk_page_creator_auto_publish']) && $_POST['bulk_page_creator_auto_publish'] === '1';
				$settings['bulk_page_creator_download_images'] = isset($_POST['bulk_page_creator_download_images']) && $_POST['bulk_page_creator_download_images'] === '1';
				$settings['bulk_page_creator_set_featured_image'] = isset($_POST['bulk_page_creator_set_featured_image']) && $_POST['bulk_page_creator_set_featured_image'] === '1';
				$settings['bulk_page_creator_include_with_every_prompt'] = isset($_POST['bulk_page_creator_include_with_every_prompt']) ? sanitize_textarea_field(wp_unslash($_POST['bulk_page_creator_include_with_every_prompt'])) : '';
				$settings['bulk_page_creator_page_data'] = isset($_POST['bulk_page_creator_page_data']) ? sanitize_textarea_field(wp_unslash($_POST['bulk_page_creator_page_data'])) : '';
				$settings['database_table_browser_enabled'] = isset($_POST['database_table_browser_enabled']) && $_POST['database_table_browser_enabled'] === '1';
				$settings['database_table_browser_per_page_limit'] = isset($_POST['database_table_browser_per_page_limit']) ? max(1, min(1000, absint($_POST['database_table_browser_per_page_limit']))) : 100;
				$settings['webhook_urls_enabled'] = isset($_POST['webhook_urls_enabled']) && $_POST['webhook_urls_enabled'] === '1';
				$settings['webhook_urls_debug_mode'] = isset($_POST['webhook_urls_debug_mode']) && $_POST['webhook_urls_debug_mode'] === '1';
				$settings['webhook_urls_allow_url_params'] = isset($_POST['webhook_urls_allow_url_params']) && $_POST['webhook_urls_allow_url_params'] === '1';
				$settings['webhook_urls_activate_order_webhook'] = isset($_POST['webhook_urls_activate_order_webhook']) && $_POST['webhook_urls_activate_order_webhook'] === '1';
				$settings['webhook_urls_activate_user_webhook'] = isset($_POST['webhook_urls_activate_user_webhook']) && $_POST['webhook_urls_activate_user_webhook'] === '1';
				$settings['webhook_urls_activate_post_webhook'] = isset($_POST['webhook_urls_activate_post_webhook']) && $_POST['webhook_urls_activate_post_webhook'] === '1';
				$settings['webhook_urls_activate_coupon_webhook'] = isset($_POST['webhook_urls_activate_coupon_webhook']) && $_POST['webhook_urls_activate_coupon_webhook'] === '1';
				$settings['webhook_urls_activate_product_webhook'] = isset($_POST['webhook_urls_activate_product_webhook']) && $_POST['webhook_urls_activate_product_webhook'] === '1';
				$settings['webhook_urls_activate_product_cat_webhook'] = isset($_POST['webhook_urls_activate_product_cat_webhook']) && $_POST['webhook_urls_activate_product_cat_webhook'] === '1';
				$settings['webhook_urls_activate_user_password_reset_webhook'] = isset($_POST['webhook_urls_activate_user_password_reset_webhook']) && $_POST['webhook_urls_activate_user_password_reset_webhook'] === '1';
				$settings['webhook_urls_activate_send_email_webhook'] = isset($_POST['webhook_urls_activate_send_email_webhook']) && $_POST['webhook_urls_activate_send_email_webhook'] === '1';
				$settings['invoice_approval_enabled'] = isset($_POST['invoice_approval_enabled']) && $_POST['invoice_approval_enabled'] === '1';
				$invoice_primary_color = isset($_POST['invoice_primary_color']) ? sanitize_hex_color(wp_unslash($_POST['invoice_primary_color'])) : '#4B2E83';
				$settings['invoice_primary_color'] = $invoice_primary_color ? $invoice_primary_color : '#4B2E83';
				$settings['invoice_hide_logo_in_pdf'] = isset($_POST['invoice_hide_logo_in_pdf']) && $_POST['invoice_hide_logo_in_pdf'] === '1';
				$settings['invoice_hide_buttons_in_pdf'] = isset($_POST['invoice_hide_buttons_in_pdf']) && $_POST['invoice_hide_buttons_in_pdf'] === '1';
				$invoice_button_color = isset($_POST['invoice_button_color']) ? sanitize_hex_color(wp_unslash($_POST['invoice_button_color'])) : '#4B2E83';
				$settings['invoice_button_color'] = $invoice_button_color ? $invoice_button_color : '#4B2E83';
				$invoice_button_text_color = isset($_POST['invoice_button_text_color']) ? sanitize_hex_color(wp_unslash($_POST['invoice_button_text_color'])) : '#ffffff';
				$settings['invoice_button_text_color'] = $invoice_button_text_color ? $invoice_button_text_color : '#ffffff';
				$settings['invoice_font_family'] = isset($_POST['invoice_font_family']) ? sanitize_text_field(wp_unslash($_POST['invoice_font_family'])) : 'Poppins, sans-serif';
				$settings['invoice_logo_url'] = isset($_POST['invoice_logo_url']) ? esc_url_raw(wp_unslash($_POST['invoice_logo_url'])) : '';
				$settings['invoice_logo_max_width'] = isset($_POST['invoice_logo_max_width']) ? sanitize_text_field(wp_unslash($_POST['invoice_logo_max_width'])) : '160px';
				$settings['invoice_company_name'] = isset($_POST['invoice_company_name']) ? sanitize_text_field(wp_unslash($_POST['invoice_company_name'])) : get_bloginfo('name');
				$settings['invoice_company_address'] = isset($_POST['invoice_company_address']) ? sanitize_textarea_field(wp_unslash($_POST['invoice_company_address'])) : '';
				$settings['invoice_company_email'] = isset($_POST['invoice_company_email']) ? sanitize_email(wp_unslash($_POST['invoice_company_email'])) : get_bloginfo('admin_email');
				$settings['invoice_company_phone'] = isset($_POST['invoice_company_phone']) ? sanitize_text_field(wp_unslash($_POST['invoice_company_phone'])) : '';
				$settings['invoice_header_note'] = isset($_POST['invoice_header_note']) ? sanitize_textarea_field(wp_unslash($_POST['invoice_header_note'])) : '';
				$settings['invoice_footer_note'] = isset($_POST['invoice_footer_note']) ? sanitize_textarea_field(wp_unslash($_POST['invoice_footer_note'])) : '';
				$settings['invoice_footer_note_below_buttons'] = isset($_POST['invoice_footer_note_below_buttons']) ? sanitize_textarea_field(wp_unslash($_POST['invoice_footer_note_below_buttons'])) : '';
				$settings['invoice_order_label'] = isset($_POST['invoice_order_label']) ? sanitize_text_field(wp_unslash($_POST['invoice_order_label'])) : 'Order';
				$settings['invoice_show_hidden_meta_fields'] = isset($_POST['invoice_show_hidden_meta_fields']) && $_POST['invoice_show_hidden_meta_fields'] === '1';
				$settings['invoice_enable_enhancements'] = isset($_POST['invoice_enable_enhancements']) && $_POST['invoice_enable_enhancements'] === '1';
				$settings['invoice_scrollable_items_threshold'] = isset($_POST['invoice_scrollable_items_threshold']) ? sanitize_text_field(wp_unslash($_POST['invoice_scrollable_items_threshold'])) : '';
				$settings['invoice_approval_emails'] = isset($_POST['invoice_approval_emails']) ? sanitize_textarea_field(wp_unslash($_POST['invoice_approval_emails'])) : '';
				$settings['invoice_approval_title'] = isset($_POST['invoice_approval_title']) ? sanitize_text_field(wp_unslash($_POST['invoice_approval_title'])) : 'Approve & Pay Later';
				$settings['invoice_approval_checkbox_text'] = isset($_POST['invoice_approval_checkbox_text']) ? sanitize_textarea_field(wp_unslash($_POST['invoice_approval_checkbox_text'])) : '';
				$settings['invoice_approval_button_text'] = isset($_POST['invoice_approval_button_text']) ? sanitize_text_field(wp_unslash($_POST['invoice_approval_button_text'])) : 'Send to Production';
				$settings['order_received_page_customizer_enabled'] = isset($_POST['order_received_page_customizer_enabled']) && $_POST['order_received_page_customizer_enabled'] === '1';
				$settings['order_received_page_customizer_heading_text'] = isset($_POST['order_received_page_customizer_heading_text']) ? sanitize_text_field(wp_unslash($_POST['order_received_page_customizer_heading_text'])) : 'Order received';
				$settings['order_received_page_customizer_paragraph_text'] = isset($_POST['order_received_page_customizer_paragraph_text']) ? sanitize_textarea_field(wp_unslash($_POST['order_received_page_customizer_paragraph_text'])) : 'Thank you. Your order has been received.';
				$settings['restricted_access_enabled'] = isset($_POST['restricted_access_enabled']) && $_POST['restricted_access_enabled'] === '1';
				$restricted_logged_out_behavior = isset($_POST['restricted_access_logged_out_behavior']) ? sanitize_key(wp_unslash($_POST['restricted_access_logged_out_behavior'])) : 'overlay';
				if ($restricted_logged_out_behavior === 'my-account') {
					$restricted_logged_out_behavior = 'redirect_my_account';
				} elseif ($restricted_logged_out_behavior === 'wp-admin') {
					$restricted_logged_out_behavior = 'redirect_wp_admin';
				}
				$allowed_restricted_logged_out_behaviors = ['redirect_my_account', 'redirect_wp_admin', 'overlay'];
				$settings['restricted_access_logged_out_behavior'] = in_array($restricted_logged_out_behavior, $allowed_restricted_logged_out_behaviors, true) ? $restricted_logged_out_behavior : 'overlay';
				$settings['restricted_access_shared_password'] = isset($_POST['restricted_access_shared_password']) ? sanitize_text_field(wp_unslash($_POST['restricted_access_shared_password'])) : '';
				$settings['restricted_access_remember_ip_for_30_days'] = isset($_POST['restricted_access_remember_ip_for_30_days']) && $_POST['restricted_access_remember_ip_for_30_days'] === '1';
				$settings['restricted_access_url_string'] = isset($_POST['restricted_access_url_string']) ? sanitize_text_field(wp_unslash($_POST['restricted_access_url_string'])) : '';
				$settings['restricted_access_time_limit_minutes'] = isset($_POST['restricted_access_time_limit_minutes']) ? max(1, absint($_POST['restricted_access_time_limit_minutes'])) : 30;
				$settings['restricted_access_excluded_roles'] = self::sanitize_role_keys_array(
					isset($_POST['restricted_access_excluded_roles']) ? wp_unslash($_POST['restricted_access_excluded_roles']) : []
				);
				$settings['restricted_access_no_access_message'] = isset($_POST['restricted_access_no_access_message'])
					? sanitize_textarea_field(wp_unslash($_POST['restricted_access_no_access_message']))
					: '';
				$restricted_access_password_button_text = isset($_POST['restricted_access_password_submit_button_text']) ? sanitize_text_field(wp_unslash($_POST['restricted_access_password_submit_button_text'])) : '';
				$settings['restricted_access_password_submit_button_text'] = $restricted_access_password_button_text !== '' ? $restricted_access_password_button_text : 'Access Website';
				$restricted_overlay_bg = isset($_POST['restricted_access_overlay_background_color']) ? sanitize_hex_color(wp_unslash($_POST['restricted_access_overlay_background_color'])) : '#ffffff';
				$settings['restricted_access_overlay_background_color'] = $restricted_overlay_bg ? $restricted_overlay_bg : '#ffffff';
				$restricted_overlay_text = isset($_POST['restricted_access_overlay_text_color']) ? sanitize_hex_color(wp_unslash($_POST['restricted_access_overlay_text_color'])) : '#000000';
				$settings['restricted_access_overlay_text_color'] = $restricted_overlay_text ? $restricted_overlay_text : '#000000';
				$settings['restricted_access_overlay_image_url'] = isset($_POST['restricted_access_overlay_image_url']) ? esc_url_raw(wp_unslash($_POST['restricted_access_overlay_image_url'])) : '';
				$settings['restricted_access_overlay_image_max_width'] = isset($_POST['restricted_access_overlay_image_max_width']) ? sanitize_text_field(wp_unslash($_POST['restricted_access_overlay_image_max_width'])) : '';
				$settings['restricted_access_overlay_image_display_as_normal_above_message'] = isset($_POST['restricted_access_overlay_image_display_as_normal_above_message']) && $_POST['restricted_access_overlay_image_display_as_normal_above_message'] === '1';
				$settings['restricted_access_render_background_html_for_social_meta'] = isset($_POST['restricted_access_render_background_html_for_social_meta']) && $_POST['restricted_access_render_background_html_for_social_meta'] === '1';
				$settings['block_pages_by_url_string_enabled'] = isset($_POST['block_pages_by_url_string_enabled']) && $_POST['block_pages_by_url_string_enabled'] === '1';
				$block_pages_rules = [];
				if (isset($_POST['block_pages_by_url_string_rules_json'])) {
					$decoded_rules = json_decode((string) wp_unslash($_POST['block_pages_by_url_string_rules_json']), true);
					$block_pages_rules = self::sanitize_block_pages_by_url_string_rules($decoded_rules);
				}
				if (empty($block_pages_rules)) {
					$block_pages_rules = self::sanitize_block_pages_by_url_string_rules(
						isset($_POST['block_pages_by_url_string_rules']) ? wp_unslash($_POST['block_pages_by_url_string_rules']) : []
					);
				}
				$settings['block_pages_by_url_string_rules'] = $block_pages_rules;
				$primary_block_pages_rule = isset($block_pages_rules[0]) && is_array($block_pages_rules[0]) ? $block_pages_rules[0] : [];
				$settings['block_pages_by_url_string_match_urls'] = isset($primary_block_pages_rule['match_urls']) ? (string) $primary_block_pages_rule['match_urls'] : '';
				$settings['block_pages_by_url_string_exception_urls'] = isset($primary_block_pages_rule['exception_urls']) ? (string) $primary_block_pages_rule['exception_urls'] : '';
				$block_pages_bg_color = isset($_POST['block_pages_by_url_string_background_color']) ? sanitize_hex_color(wp_unslash($_POST['block_pages_by_url_string_background_color'])) : '#000000';
				$settings['block_pages_by_url_string_background_color'] = $block_pages_bg_color !== '' ? $block_pages_bg_color : '#000000';
				$settings['block_pages_by_url_string_background_url'] = isset($_POST['block_pages_by_url_string_background_url']) ? esc_url_raw(wp_unslash($_POST['block_pages_by_url_string_background_url'])) : '';
				$settings['block_pages_by_url_string_logo_url'] = isset($_POST['block_pages_by_url_string_logo_url']) ? esc_url_raw(wp_unslash($_POST['block_pages_by_url_string_logo_url'])) : '';
				$settings['block_pages_by_url_string_logo_width'] = isset($_POST['block_pages_by_url_string_logo_width']) ? sanitize_text_field(wp_unslash($_POST['block_pages_by_url_string_logo_width'])) : '';
				$settings['block_pages_by_url_string_message'] = isset($_POST['block_pages_by_url_string_message']) ? sanitize_text_field(wp_unslash($_POST['block_pages_by_url_string_message'])) : '';
				$block_pages_text_color = isset($_POST['block_pages_by_url_string_text_color']) ? sanitize_hex_color(wp_unslash($_POST['block_pages_by_url_string_text_color'])) : '';
				$settings['block_pages_by_url_string_text_color'] = $block_pages_text_color !== '' ? $block_pages_text_color : '';
				$settings['block_pages_by_url_string_redirect_url'] = isset($_POST['block_pages_by_url_string_redirect_url']) ? esc_url_raw(wp_unslash($_POST['block_pages_by_url_string_redirect_url'])) : '';
				// Keep Send Email permanently enabled because Login Tools depend on
				// shared email templates from this add-on context.
				$settings['send_email_users_enabled'] = true;
				$settings['send_sms_text_enabled'] = isset($_POST['send_sms_text_enabled']) && $_POST['send_sms_text_enabled'] === '1';
				$settings['staging_dev_overrides_enabled'] = isset($_POST['staging_dev_overrides_enabled']) && $_POST['staging_dev_overrides_enabled'] === '1';
				$settings['staging_dev_disable_all_emails'] = isset($_POST['staging_dev_disable_all_emails']) && $_POST['staging_dev_disable_all_emails'] === '1';
				$settings['staging_dev_disable_all_payment_gateways'] = isset($_POST['staging_dev_disable_all_payment_gateways']) && $_POST['staging_dev_disable_all_payment_gateways'] === '1';
				$settings['staging_dev_disable_all_webhooks'] = isset($_POST['staging_dev_disable_all_webhooks']) && $_POST['staging_dev_disable_all_webhooks'] === '1';
				$settings['staging_dev_disable_all_api_json_requests'] = isset($_POST['staging_dev_disable_all_api_json_requests']) && $_POST['staging_dev_disable_all_api_json_requests'] === '1';
				$settings['staging_dev_notice_frontend_top_bar'] = isset($_POST['staging_dev_notice_frontend_top_bar']) && $_POST['staging_dev_notice_frontend_top_bar'] === '1';
				$settings['staging_dev_notice_wp_admin'] = isset($_POST['staging_dev_notice_wp_admin']) && $_POST['staging_dev_notice_wp_admin'] === '1';
				$settings['staging_dev_notice_include_data_anonymized'] = isset($_POST['staging_dev_notice_include_data_anonymized']) && $_POST['staging_dev_notice_include_data_anonymized'] === '1';
				$settings['data_anonymizer_enabled'] = isset($_POST['data_anonymizer_enabled']) && $_POST['data_anonymizer_enabled'] === '1';
				$settings['data_anonymizer_order_address_random'] = isset($_POST['data_anonymizer_order_address_random']) && $_POST['data_anonymizer_order_address_random'] === '1';
				$settings['data_anonymizer_order_phone_fixed'] = isset($_POST['data_anonymizer_order_phone_fixed']) && $_POST['data_anonymizer_order_phone_fixed'] === '1';
				$settings['data_anonymizer_order_email_random'] = isset($_POST['data_anonymizer_order_email_random']) && $_POST['data_anonymizer_order_email_random'] === '1';
				$settings['data_anonymizer_order_notes_random'] = isset($_POST['data_anonymizer_order_notes_random']) && $_POST['data_anonymizer_order_notes_random'] === '1';
				$settings['data_anonymizer_user_meta_address_random'] = isset($_POST['data_anonymizer_user_meta_address_random']) && $_POST['data_anonymizer_user_meta_address_random'] === '1';
				$settings['data_anonymizer_user_meta_phone_fixed'] = isset($_POST['data_anonymizer_user_meta_phone_fixed']) && $_POST['data_anonymizer_user_meta_phone_fixed'] === '1';
				$settings['data_anonymizer_user_email_random'] = isset($_POST['data_anonymizer_user_email_random']) && $_POST['data_anonymizer_user_email_random'] === '1';
				$settings['data_anonymizer_user_login_random'] = isset($_POST['data_anonymizer_user_login_random']) && $_POST['data_anonymizer_user_login_random'] === '1';
				$settings['data_anonymizer_forms_random'] = isset($_POST['data_anonymizer_forms_random']) && $_POST['data_anonymizer_forms_random'] === '1';
				$settings['data_anonymizer_exclude_wp_administrators'] = isset($_POST['data_anonymizer_exclude_wp_administrators']) && $_POST['data_anonymizer_exclude_wp_administrators'] === '1';
				$settings['data_anonymizer_exclude_if_matches_admin_email'] = isset($_POST['data_anonymizer_exclude_if_matches_admin_email']) && $_POST['data_anonymizer_exclude_if_matches_admin_email'] === '1';
				$settings['data_anonymizer_excluded_email_domains'] = isset($_POST['data_anonymizer_excluded_email_domains']) ? sanitize_text_field(wp_unslash($_POST['data_anonymizer_excluded_email_domains'])) : '';
				$settings['addon_main_navigation_tabs'] = [];
				if (isset($_POST['addon_main_navigation_tabs']) && is_array($_POST['addon_main_navigation_tabs'])) {
					$allowed_addon_slugs = array_keys(User_Manager_Core::get_addon_runtime_toggle_map(false));
					$selected_addon_slugs = array_map(
						'sanitize_key',
						wp_unslash($_POST['addon_main_navigation_tabs'])
					);
					foreach ($selected_addon_slugs as $selected_addon_slug) {
						if ($selected_addon_slug === '' || !in_array($selected_addon_slug, $allowed_addon_slugs, true)) {
							continue;
						}
						$settings['addon_main_navigation_tabs'][] = $selected_addon_slug;
					}
					$settings['addon_main_navigation_tabs'] = array_values(array_unique($settings['addon_main_navigation_tabs']));
				}

				// Bulk Add to Cart settings (migrated from standalone plugin UI).
				$settings['bulk_add_to_cart_enabled'] = isset($_POST['bulk_add_to_cart_enabled']) && $_POST['bulk_add_to_cart_enabled'] === '1';
				$settings['add_to_cart_variation_table_enabled'] = isset($_POST['add_to_cart_variation_table_enabled']) && $_POST['add_to_cart_variation_table_enabled'] === '1';
				$settings['add_to_cart_min_max_quantities_enabled'] = isset($_POST['add_to_cart_min_max_quantities_enabled']) && $_POST['add_to_cart_min_max_quantities_enabled'] === '1';
				$variation_table_hook = isset($_POST['add_to_cart_variation_table_hook']) ? sanitize_key(wp_unslash($_POST['add_to_cart_variation_table_hook'])) : 'auto';
				$allowed_variation_table_hooks = ['auto', 'after_add_to_cart_form', 'single_product_summary', 'after_single_product_summary', 'before_add_to_cart_form'];
				$settings['add_to_cart_variation_table_hook'] = in_array($variation_table_hook, $allowed_variation_table_hooks, true) ? $variation_table_hook : 'auto';
				$settings['add_to_cart_variation_table_hide_default_form'] = isset($_POST['add_to_cart_variation_table_hide_default_form']) && $_POST['add_to_cart_variation_table_hide_default_form'] === '1';
				$settings['add_to_cart_variation_table_show_price_column'] = isset($_POST['add_to_cart_variation_table_show_price_column']) && $_POST['add_to_cart_variation_table_show_price_column'] === '1';
				$settings['add_to_cart_variation_table_prefix_labels'] = isset($_POST['add_to_cart_variation_table_prefix_labels']) && $_POST['add_to_cart_variation_table_prefix_labels'] === '1';
				$settings['add_to_cart_variation_table_hide_header_row'] = isset($_POST['add_to_cart_variation_table_hide_header_row']) && $_POST['add_to_cart_variation_table_hide_header_row'] === '1';
				$settings['add_to_cart_variation_table_hide_totals_row'] = isset($_POST['add_to_cart_variation_table_hide_totals_row']) && $_POST['add_to_cart_variation_table_hide_totals_row'] === '1';
				$settings['add_to_cart_variation_table_header_variation_label'] = isset($_POST['add_to_cart_variation_table_header_variation_label']) ? sanitize_text_field(wp_unslash($_POST['add_to_cart_variation_table_header_variation_label'])) : '';
				$settings['add_to_cart_variation_table_header_qty_label'] = isset($_POST['add_to_cart_variation_table_header_qty_label']) ? sanitize_text_field(wp_unslash($_POST['add_to_cart_variation_table_header_qty_label'])) : '';
				$variation_table_category_ids = isset($_POST['add_to_cart_variation_table_category_ids']) && is_array($_POST['add_to_cart_variation_table_category_ids'])
					? array_values(array_unique(array_filter(array_map('absint', wp_unslash($_POST['add_to_cart_variation_table_category_ids'])))))
					: [];
				$settings['add_to_cart_variation_table_category_ids'] = $variation_table_category_ids;
				$settings['add_to_cart_variation_table_empty_cart_button_on_cart'] = isset($_POST['add_to_cart_variation_table_empty_cart_button_on_cart']) && $_POST['add_to_cart_variation_table_empty_cart_button_on_cart'] === '1';
				$settings['add_to_cart_variation_table_min_total_qty'] = isset($_POST['add_to_cart_variation_table_min_total_qty']) ? max(0, absint($_POST['add_to_cart_variation_table_min_total_qty'])) : 0;
				$settings['add_to_cart_variation_table_min_total_qty_alert_message'] = isset($_POST['add_to_cart_variation_table_min_total_qty_alert_message']) ? sanitize_text_field(wp_unslash($_POST['add_to_cart_variation_table_min_total_qty_alert_message'])) : '';
				$settings['add_to_cart_variation_table_success_alert_message'] = isset($_POST['add_to_cart_variation_table_success_alert_message']) ? sanitize_text_field(wp_unslash($_POST['add_to_cart_variation_table_success_alert_message'])) : '';
				$settings['add_to_cart_variation_table_button_text'] = isset($_POST['add_to_cart_variation_table_button_text']) ? sanitize_text_field(wp_unslash($_POST['add_to_cart_variation_table_button_text'])) : '';
				$settings['add_to_cart_variation_table_text_above'] = isset($_POST['add_to_cart_variation_table_text_above']) ? wp_kses_post(wp_unslash($_POST['add_to_cart_variation_table_text_above'])) : '';
				$settings['add_to_cart_variation_table_text_below'] = isset($_POST['add_to_cart_variation_table_text_below']) ? wp_kses_post(wp_unslash($_POST['add_to_cart_variation_table_text_below'])) : '';
				$settings['add_to_cart_variation_table_debug_mode'] = isset($_POST['add_to_cart_variation_table_debug_mode']) && $_POST['add_to_cart_variation_table_debug_mode'] === '1';
				$settings['bulk_coupons_enabled'] = isset($_POST['bulk_coupons_enabled']) && $_POST['bulk_coupons_enabled'] === '1';
				$settings['bulk_coupons_template_code'] = isset($_POST['bulk_coupons_template_code']) ? sanitize_text_field(wp_unslash($_POST['bulk_coupons_template_code'])) : '';
				$settings['bulk_coupons_total'] = isset($_POST['bulk_coupons_total']) ? max(0, absint($_POST['bulk_coupons_total'])) : 0;
				$settings['bulk_coupons_emails'] = isset($_POST['bulk_coupons_emails']) ? sanitize_textarea_field(wp_unslash($_POST['bulk_coupons_emails'])) : '';
				$bulk_coupons_amount_raw = isset($_POST['bulk_coupons_amount']) ? wp_unslash($_POST['bulk_coupons_amount']) : '';
				if ($bulk_coupons_amount_raw !== '' && function_exists('wc_format_decimal')) {
					$settings['bulk_coupons_amount'] = (string) wc_format_decimal($bulk_coupons_amount_raw);
				} else {
					$settings['bulk_coupons_amount'] = sanitize_text_field($bulk_coupons_amount_raw);
				}
				$settings['bulk_coupons_prefix'] = isset($_POST['bulk_coupons_prefix']) ? sanitize_text_field(wp_unslash($_POST['bulk_coupons_prefix'])) : '';
				$settings['bulk_coupons_suffix'] = isset($_POST['bulk_coupons_suffix']) ? sanitize_text_field(wp_unslash($_POST['bulk_coupons_suffix'])) : '';
				$settings['bulk_coupons_length'] = isset($_POST['bulk_coupons_length']) ? max(4, min(64, absint($_POST['bulk_coupons_length']))) : 8;
				$settings['bulk_coupons_expiration_date'] = isset($_POST['bulk_coupons_expiration_date']) ? sanitize_text_field(wp_unslash($_POST['bulk_coupons_expiration_date'])) : '';
				$settings['bulk_coupons_expiration_days'] = isset($_POST['bulk_coupons_expiration_days']) ? max(0, absint($_POST['bulk_coupons_expiration_days'])) : 0;
				$settings['bulk_coupons_send_email'] = isset($_POST['send_email']) && $_POST['send_email'] === '1';
				$settings['bulk_coupons_email_template'] = isset($_POST['email_template']) ? sanitize_key($_POST['email_template']) : '';

				$bulk_settings = [
					'redirect_to_cart'   => isset($_POST['bulk_add_to_cart_redirect_to_cart']) && $_POST['bulk_add_to_cart_redirect_to_cart'] === '1' ? '1' : '0',
					'identifier_column'  => isset($_POST['bulk_add_to_cart_identifier_column']) ? sanitize_text_field(wp_unslash($_POST['bulk_add_to_cart_identifier_column'])) : 'product_id',
					'identifier_type'    => isset($_POST['bulk_add_to_cart_identifier_type']) ? sanitize_text_field(wp_unslash($_POST['bulk_add_to_cart_identifier_type'])) : 'product_id',
					'meta_field_name'    => isset($_POST['bulk_add_to_cart_meta_field_name']) ? sanitize_text_field(wp_unslash($_POST['bulk_add_to_cart_meta_field_name'])) : '',
					'product_id_custom_column_header' => isset($_POST['bulk_add_to_cart_product_id_custom_column_header']) ? sanitize_text_field(wp_unslash($_POST['bulk_add_to_cart_product_id_custom_column_header'])) : 'product_id',
					'sku_custom_column_header' => isset($_POST['bulk_add_to_cart_sku_custom_column_header']) ? sanitize_text_field(wp_unslash($_POST['bulk_add_to_cart_sku_custom_column_header'])) : '_sku',
					'hide_product_id_column' => isset($_POST['bulk_add_to_cart_hide_product_id_column']) && $_POST['bulk_add_to_cart_hide_product_id_column'] === '1' ? '1' : '0',
					'hide_sku_column' => isset($_POST['bulk_add_to_cart_hide_sku_column']) && $_POST['bulk_add_to_cart_hide_sku_column'] === '1' ? '1' : '0',
					'quantity_column'    => isset($_POST['bulk_add_to_cart_quantity_column']) ? sanitize_text_field(wp_unslash($_POST['bulk_add_to_cart_quantity_column'])) : 'quantity',
					'debug_mode'         => isset($_POST['bulk_add_to_cart_debug_mode']) && $_POST['bulk_add_to_cart_debug_mode'] === '1' ? '1' : '0',
					'show_sample_csv'    => isset($_POST['bulk_add_to_cart_show_sample_csv']) && $_POST['bulk_add_to_cart_show_sample_csv'] === '1' ? '1' : '0',
					'show_sample_with_product_data' => isset($_POST['bulk_add_to_cart_show_sample_with_product_data']) && $_POST['bulk_add_to_cart_show_sample_with_product_data'] === '1' ? '1' : '0',
					'sample_with_data_include_private_products' => isset($_POST['bulk_add_to_cart_sample_with_data_include_private_products']) && $_POST['bulk_add_to_cart_sample_with_data_include_private_products'] === '1' ? '1' : '0',
					'sample_with_data_include_draft_products' => isset($_POST['bulk_add_to_cart_sample_with_data_include_draft_products']) && $_POST['bulk_add_to_cart_sample_with_data_include_draft_products'] === '1' ? '1' : '0',
				];
				if (trim((string) $bulk_settings['product_id_custom_column_header']) === '') {
					$bulk_settings['product_id_custom_column_header'] = 'product_id';
				}
				if (trim((string) $bulk_settings['sku_custom_column_header']) === '') {
					$bulk_settings['sku_custom_column_header'] = '_sku';
				}
				$bulk_settings_after = $bulk_settings;
				update_option('bulk_add_to_cart_settings', $bulk_settings);

				// Role Switching settings.
				$role_switch_settings    = $role_switch_settings_before;
				$old_role_switch_enabled = !empty($role_switch_settings['enabled']);
				$old_hidden_roles        = isset($role_switch_settings['hidden_roles']) && is_array($role_switch_settings['hidden_roles']) ? $role_switch_settings['hidden_roles'] : [];
				$old_allow_reset         = !empty($role_switch_settings['allow_reset']);

				$is_addons_temp_disable_only = isset($is_addons_temp_disable_only) && $is_addons_temp_disable_only;
				$new_role_switch_enabled = $is_addons_temp_disable_only
					? $old_role_switch_enabled
					: (isset($_POST['role_switching_enabled']) && $_POST['role_switching_enabled'] === '1');
				$new_hidden_roles = $is_addons_temp_disable_only
					? $old_hidden_roles
					: (isset($_POST['hidden_roles']) ? array_map('sanitize_text_field', (array) wp_unslash($_POST['hidden_roles'])) : []);
				$new_allow_reset = $is_addons_temp_disable_only
					? $old_allow_reset
					: (isset($_POST['allow_reset']) && $_POST['allow_reset'] === '1');

				$role_switch_settings_after = [
					'enabled'      => $new_role_switch_enabled,
					'hidden_roles' => $new_hidden_roles,
					'allow_reset'  => $new_allow_reset,
				];
				update_option('view_website_by_role_settings', $role_switch_settings_after);

				$role_switch_changes = [];
				if ($old_role_switch_enabled !== $new_role_switch_enabled) {
					$role_switch_changes[] = $new_role_switch_enabled ? 'Enabled Role Switching' : 'Disabled Role Switching';
				}
				$added_roles   = array_diff($new_hidden_roles, $old_hidden_roles);
				$removed_roles = array_diff($old_hidden_roles, $new_hidden_roles);
				if (!empty($added_roles)) {
					$role_switch_changes[] = 'Hidden roles: ' . implode(', ', $added_roles);
				}
				if (!empty($removed_roles)) {
					$role_switch_changes[] = 'Unhidden roles: ' . implode(', ', $removed_roles);
				}
				if ($old_allow_reset !== $new_allow_reset) {
					$role_switch_changes[] = $new_allow_reset ? 'Enabled Reset Default Roles' : 'Disabled Reset Default Roles';
				}
				if (!empty($role_switch_changes)) {
					User_Manager_Core::add_role_switch_history('Settings Update', implode(' | ', $role_switch_changes));
				}

				// Custom WP-Admin Notifications (indices may be 0,1,2 or 0,2 after a remove; we normalize to sequential)
				$settings['custom_admin_notifications_enabled'] = isset($_POST['custom_admin_notifications_enabled']) && $_POST['custom_admin_notifications_enabled'] === '1';
				$settings['custom_admin_notifications'] = [];
				if (!empty($_POST['custom_admin_notification']) && is_array($_POST['custom_admin_notification'])) {
					$keys = array_keys($_POST['custom_admin_notification']);
					sort($keys, SORT_NUMERIC);
					foreach ($keys as $k) {
						$row = $_POST['custom_admin_notification'][$k];
						if (!is_array($row)) {
							continue;
						}
						$title = isset($row['title']) ? sanitize_text_field(wp_unslash($row['title'])) : '';
						$body  = isset($row['body']) ? sanitize_textarea_field(wp_unslash($row['body'])) : '';
						$color = isset($row['background_color']) ? sanitize_hex_color(wp_unslash($row['background_color'])) : '';
						$url   = isset($row['url_string_match']) ? sanitize_text_field(wp_unslash($row['url_string_match'])) : '';
						$settings['custom_admin_notifications'][] = [
							'title'             => $title,
							'body'              => $body,
							'background_color'  => $color,
							'url_string_match'  => $url,
						];
					}
				}

				// WP-Admin Bar Menu Items (custom shortcut menus in the admin bar)
				$settings['admin_bar_menu_items_enabled'] = isset($_POST['admin_bar_menu_items_enabled']) && $_POST['admin_bar_menu_items_enabled'] === '1';
				$settings['admin_bar_menu_items'] = [];
				if (!empty($_POST['admin_bar_menu_item']) && is_array($_POST['admin_bar_menu_item'])) {
					$keys = array_keys($_POST['admin_bar_menu_item']);
					sort($keys, SORT_NUMERIC);
					foreach ($keys as $k) {
						$row = $_POST['admin_bar_menu_item'][$k];
						if (!is_array($row)) {
							continue;
						}
						$menu_title = isset($row['title']) ? sanitize_text_field(wp_unslash($row['title'])) : '';
						$shortcuts  = isset($row['shortcuts']) ? sanitize_textarea_field(wp_unslash($row['shortcuts'])) : '';
						$icon       = isset($row['icon']) ? sanitize_text_field(wp_unslash($row['icon'])) : '';
						$side       = isset($row['side']) ? sanitize_key(wp_unslash($row['side'])) : 'right';
						$side       = $side === 'left' ? 'left' : 'right';
						$settings['admin_bar_menu_items'][] = [
							'title'     => $menu_title,
							'icon'      => $icon,
							'side'      => $side,
							'shortcuts' => $shortcuts,
						];
					}
				}

				// WP-Admin CSS
				$settings['wp_admin_css_enabled'] = isset($_POST['wp_admin_css_enabled']) && $_POST['wp_admin_css_enabled'] === '1';
				$settings['wp_admin_css_all'] = isset($_POST['wp_admin_css_all']) ? sanitize_textarea_field(wp_unslash($_POST['wp_admin_css_all'])) : '';
				$exclude_roles = isset($_POST['wp_admin_css_exclude_roles']) ? sanitize_text_field(wp_unslash($_POST['wp_admin_css_exclude_roles'])) : '';
				$settings['wp_admin_css_exclude_roles'] = array_filter(array_map('trim', explode(',', $exclude_roles)));
				$settings['wp_admin_css_users_css'] = isset($_POST['wp_admin_css_users_css']) ? sanitize_textarea_field(wp_unslash($_POST['wp_admin_css_users_css'])) : '';
				$users_include = isset($_POST['wp_admin_css_users_include']) ? sanitize_text_field(wp_unslash($_POST['wp_admin_css_users_include'])) : '';
				$settings['wp_admin_css_users_include'] = array_filter(array_map('trim', explode(',', $users_include)));
				$settings['wp_admin_css_roles'] = [];
				if (!empty($_POST['wp_admin_css_role']) && is_array($_POST['wp_admin_css_role'])) {
					$roles = User_Manager_Core::get_user_roles();
					foreach ($_POST['wp_admin_css_role'] as $role_key => $css) {
						if (isset($roles[$role_key])) {
							$settings['wp_admin_css_roles'][$role_key] = sanitize_textarea_field(wp_unslash($css));
						}
					}
				}
				$settings['wp_admin_css_hide_admin_chrome_enabled'] = isset($_POST['wp_admin_css_hide_admin_chrome_enabled']) && $_POST['wp_admin_css_hide_admin_chrome_enabled'] === '1';
				$hide_admin_chrome_users = isset($_POST['wp_admin_css_hide_admin_chrome_users_include']) ? sanitize_text_field(wp_unslash($_POST['wp_admin_css_hide_admin_chrome_users_include'])) : '';
				$settings['wp_admin_css_hide_admin_chrome_users_include'] = array_filter(array_map('trim', explode(',', $hide_admin_chrome_users)));
				$settings['wp_admin_css_hide_admin_chrome_roles'] = [];
				if (!empty($_POST['wp_admin_css_hide_admin_chrome_roles']) && is_array($_POST['wp_admin_css_hide_admin_chrome_roles'])) {
					$roles = User_Manager_Core::get_user_roles();
					foreach ((array) wp_unslash($_POST['wp_admin_css_hide_admin_chrome_roles']) as $role_key) {
						$role_key = sanitize_key((string) $role_key);
						if (isset($roles[$role_key])) {
							$settings['wp_admin_css_hide_admin_chrome_roles'][] = $role_key;
						}
					}
					$settings['wp_admin_css_hide_admin_chrome_roles'] = array_values(array_unique($settings['wp_admin_css_hide_admin_chrome_roles']));
				}
				break;
		}

		update_option(User_Manager_Core::OPTION_KEY, $settings);
		User_Manager_Core::sync_coupon_notification_settings($settings);

		$changed_fields = self::collect_changed_values($settings_before, $settings, 'settings');
		if ($section === 'addons') {
			$changed_fields = array_merge(
				$changed_fields,
				self::collect_changed_values($bulk_settings_before, $bulk_settings_after, 'bulk_add_to_cart_settings'),
				self::collect_changed_values($role_switch_settings_before, $role_switch_settings_after, 'role_switch_settings')
			);
		}
		if (!empty($changed_fields)) {
			$log_extra = [
				'settings_section' => $section,
				'changed_count'    => count($changed_fields),
				'changed_fields'   => $changed_fields,
			];
			if (isset($_POST['addon_section'])) {
				$addon_section = sanitize_key(wp_unslash($_POST['addon_section']));
				if ($addon_section !== '') {
					$log_extra['addon_section'] = $addon_section;
				}
			}
			User_Manager_Core::add_activity_log('settings_updated', get_current_user_id(), 'Settings', $log_extra);
		}

		$redirect_message = 'settings_saved';
		if ($should_reset_media_library_tag_reports) {
			$redirect_message = 'media_library_tag_reports_cleared';
		}
		$redirect_url = User_Manager_Core::get_redirect_with_message($redirect_tab, $redirect_message);
		if ($should_reset_media_library_tag_reports) {
			$redirect_url = add_query_arg(
				[
					'media_library_tag_reports_attachments_cleared' => (string) max(0, (int) ($media_library_tag_reports_reset_counts['attachments'] ?? 0)),
					'media_library_tag_reports_tags_cleared' => (string) max(0, (int) ($media_library_tag_reports_reset_counts['tags'] ?? 0)),
				],
				$redirect_url
			);
		}
		if ($redirect_tab === User_Manager_Core::TAB_ADDONS && isset($_POST['addon_section'])) {
			$addon_section = sanitize_key(wp_unslash($_POST['addon_section']));
			if ($addon_section !== '') {
				$redirect_url = add_query_arg('addon_section', $addon_section, $redirect_url);
			}
			if (isset($_POST['addon_tag'])) {
				$addon_tag = sanitize_title(wp_unslash($_POST['addon_tag']));
				if ($addon_tag !== '') {
					$redirect_url = add_query_arg('addon_tag', $addon_tag, $redirect_url);
				}
			}
		}
		if ($redirect_tab === User_Manager_Core::TAB_BLOCKS && isset($_POST['block_section'])) {
			$block_section = sanitize_key(wp_unslash($_POST['block_section']));
			if ($block_section !== '') {
				$redirect_url = add_query_arg('block_section', $block_section, $redirect_url);
			}
			if (isset($_POST['block_tag'])) {
				$block_tag = sanitize_title(wp_unslash($_POST['block_tag']));
				if ($block_tag !== '') {
					$redirect_url = add_query_arg('block_tag', $block_tag, $redirect_url);
				}
			}
		}

		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	 * Handle resend action for an Emali Log row.
	 */
	public static function handle_emali_log_resend(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}
		$log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
		check_admin_referer('user_manager_emali_log_resend_' . $log_id);
		$ok = User_Manager_Core::resend_emali_log_entry($log_id);
		$msg = $ok ? 'emali_log_resent' : 'emali_log_resend_failed';
		wp_safe_redirect(
			User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_ADDONS, $msg, [
				'addon_section' => 'emali-log',
				'addon_tag'     => isset($_POST['addon_tag']) ? sanitize_title(wp_unslash($_POST['addon_tag'])) : '',
				'emali_log_status' => isset($_POST['emali_log_status']) ? sanitize_key(wp_unslash($_POST['emali_log_status'])) : '',
				'emali_log_search' => isset($_POST['emali_log_search']) ? sanitize_text_field(wp_unslash($_POST['emali_log_search'])) : '',
				'emali_log_page'   => isset($_POST['emali_log_page']) ? max(1, absint($_POST['emali_log_page'])) : 1,
			])
		);
		exit;
	}

	/**
	 * Handle forward action for an Emali Log row.
	 */
	public static function handle_emali_log_forward(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}
		$log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
		check_admin_referer('user_manager_emali_log_forward_' . $log_id);
		$forward_email = isset($_POST['forward_email']) ? sanitize_email(wp_unslash($_POST['forward_email'])) : '';
		$ok = User_Manager_Core::forward_emali_log_entry($log_id, $forward_email);
		$msg = $ok ? 'emali_log_forwarded' : 'emali_log_forward_failed';
		wp_safe_redirect(
			User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_ADDONS, $msg, [
				'addon_section' => 'emali-log',
				'addon_tag'     => isset($_POST['addon_tag']) ? sanitize_title(wp_unslash($_POST['addon_tag'])) : '',
				'emali_log_status' => isset($_POST['emali_log_status']) ? sanitize_key(wp_unslash($_POST['emali_log_status'])) : '',
				'emali_log_search' => isset($_POST['emali_log_search']) ? sanitize_text_field(wp_unslash($_POST['emali_log_search'])) : '',
				'emali_log_page'   => isset($_POST['emali_log_page']) ? max(1, absint($_POST['emali_log_page'])) : 1,
			])
		);
		exit;
	}

	/**
	 * Handle clear-all action for Emali Log history.
	 */
	public static function handle_emali_log_clear(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}
		check_admin_referer('user_manager_emali_log_clear');
		User_Manager_Core::clear_emali_log_history();
		wp_safe_redirect(
			User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_ADDONS, 'emali_log_cleared', [
				'addon_section' => 'emali-log',
				'addon_tag'     => isset($_POST['addon_tag']) ? sanitize_title(wp_unslash($_POST['addon_tag'])) : '',
				'emali_log_status' => isset($_POST['emali_log_status']) ? sanitize_key(wp_unslash($_POST['emali_log_status'])) : '',
				'emali_log_search' => isset($_POST['emali_log_search']) ? sanitize_text_field(wp_unslash($_POST['emali_log_search'])) : '',
				'emali_log_page'   => isset($_POST['emali_log_page']) ? max(1, absint($_POST['emali_log_page'])) : 1,
			])
		);
		exit;
	}

	/**
	 * Build changed field rows between old/new values.
	 *
	 * @param mixed  $before Previous value.
	 * @param mixed  $after  New value.
	 * @param string $path   Current field path.
	 * @return array<int,array{field:string,old:mixed,new:mixed}>
	 */
	private static function collect_changed_values($before, $after, string $path = ''): array {
		if (is_object($before)) {
			$before = (array) $before;
		}
		if (is_object($after)) {
			$after = (array) $after;
		}

		if (is_array($before) && is_array($after)) {
			$keys = array_values(array_unique(array_merge(array_keys($before), array_keys($after))));
			sort($keys, SORT_STRING);
			$changes = [];
			foreach ($keys as $key) {
				$key_string = (string) $key;
				$child_path = $path === '' ? $key_string : $path . '[' . $key_string . ']';
				$child_before = array_key_exists($key, $before) ? $before[$key] : null;
				$child_after  = array_key_exists($key, $after) ? $after[$key] : null;
				$changes = array_merge($changes, self::collect_changed_values($child_before, $child_after, $child_path));
			}
			return $changes;
		}

		if (self::normalize_value_for_change_compare($before) === self::normalize_value_for_change_compare($after)) {
			return [];
		}

		$field = $path !== '' ? $path : 'value';
		return [[
			'field' => $field,
			'old'   => self::format_changed_value_for_log($field, $before),
			'new'   => self::format_changed_value_for_log($field, $after),
		]];
	}

	/**
	 * Normalize values for stable change comparison.
	 *
	 * @param mixed $value
	 */
	private static function normalize_value_for_change_compare($value): string {
		if (is_object($value)) {
			$value = (array) $value;
		}
		if (is_array($value)) {
			$normalized = [];
			foreach ($value as $key => $child) {
				$normalized[(string) $key] = self::normalize_value_for_change_compare($child);
			}
			if (self::is_assoc_array($normalized)) {
				ksort($normalized, SORT_STRING);
			}
			$encoded = wp_json_encode($normalized);
			return is_string($encoded) ? $encoded : '';
		}
		if (is_bool($value)) {
			return $value ? 'true' : 'false';
		}
		if ($value === null) {
			return 'null';
		}
		return (string) $value;
	}

	/**
	 * Convert changed values into safe log payload values.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	private static function format_changed_value_for_log(string $field, $value) {
		if (self::is_sensitive_change_field($field)) {
			return '[redacted]';
		}
		if (is_object($value)) {
			$value = (array) $value;
		}
		if (is_array($value)) {
			$encoded = wp_json_encode($value);
			return is_string($encoded) ? $encoded : '';
		}
		if (is_bool($value)) {
			return $value;
		}
		if ($value === null) {
			return null;
		}
		return (string) $value;
	}

	/**
	 * Check if an array is associative.
	 *
	 * @param array<mixed> $array
	 */
	private static function is_assoc_array(array $array): bool {
		if ($array === []) {
			return false;
		}
		return array_keys($array) !== range(0, count($array) - 1);
	}

	/**
	 * Determine whether field path contains sensitive data.
	 */
	private static function is_sensitive_change_field(string $field): bool {
		$needle = strtolower($field);
		$sensitive_tokens = [
			'password',
			'user_pass',
			'api_key',
			'token',
			'secret',
			'nonce',
			'authorization',
			'cookie',
		];
		foreach ($sensitive_tokens as $token) {
			if ($token !== '' && strpos($needle, $token) !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Handle SFTP file import.
	 */
	public static function handle_import_sftp_file(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_import_sftp_file');

		$filepath = isset($_POST['filepath']) ? sanitize_text_field(wp_unslash($_POST['filepath'])) : '';
		$default_role = isset($_POST['default_role']) ? sanitize_key($_POST['default_role']) : 'customer';
		$send_email = isset($_POST['send_email']) && $_POST['send_email'] === '1';
		$template_id = isset($_POST['email_template']) ? sanitize_key($_POST['email_template']) : '';
		$login_url = '/my-account/';

		if (empty($filepath) || !file_exists($filepath) || !is_readable($filepath)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'file_not_found'));
			exit;
		}

		// Validate that the file is in one of the configured directories
		$allowed_dirs = User_Manager_Core::get_sftp_directories();
		$file_dir = dirname($filepath);
		$is_allowed = false;
		foreach ($allowed_dirs as $dir) {
			if (strpos(realpath($filepath), realpath($dir)) === 0) {
				$is_allowed = true;
				break;
			}
		}

		if (!$is_allowed) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'file_not_allowed'));
			exit;
		}

		// Read and parse CSV
		$handle = fopen($filepath, 'r');
		if (!$handle) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'file_read_error'));
			exit;
		}

		$rows = [];
		$header = null;
		while (($row = fgetcsv($handle)) !== false) {
			if ($header === null) {
				// Check if first row is a header
				$first_val = strtolower(trim($row[0] ?? ''));
				if (in_array($first_val, ['email', 'e-mail', 'email address'])) {
					$header = array_map('strtolower', array_map('trim', $row));
					continue;
				}
				$header = ['email', 'first_name', 'last_name', 'role', 'password'];
			}
			$rows[] = $row;
		}
		fclose($handle);

		if (empty($rows)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'no_data'));
			exit;
		}

		$settings = User_Manager_Core::get_settings();
		$created = 0;
		$updated = 0;
		$skipped = 0;
		$failed = 0;
		$emails_sent = 0;
		$detailed_log = [];

		// Generate a unique import ID for tracking
		$import_id = uniqid('import_');

		foreach ($rows as $index => $row) {
			$row_num = $index + 1;
			$email_idx = array_search('email', $header) !== false ? array_search('email', $header) : 0;
			$email = isset($row[$email_idx]) ? sanitize_email(trim($row[$email_idx])) : '';

			if (empty($email) || !is_email($email)) {
				$detailed_log[] = [
					'row' => $row_num,
					'status' => 'failed',
					'message' => sprintf(__('Invalid email: %s', 'user-manager'), $row[$email_idx] ?? '(empty)'),
				];
				$failed++;
				// Log error
				User_Manager_Core::add_activity_log('user_create_failed', 0, 'SFTP Import', [
					'error' => sprintf(__('Invalid email: %s', 'user-manager'), $row[$email_idx] ?? '(empty)'),
					'import_id' => $import_id,
					'source_file' => basename($filepath),
					'row' => $row_num,
				]);
				continue;
			}

			$first_name_idx = array_search('first_name', $header) !== false ? array_search('first_name', $header) : 1;
			$last_name_idx = array_search('last_name', $header) !== false ? array_search('last_name', $header) : 2;
			$role_idx = array_search('role', $header) !== false ? array_search('role', $header) : 3;
			$password_idx = array_search('password', $header) !== false ? array_search('password', $header) : 4;

			$first_name = isset($row[$first_name_idx]) ? sanitize_text_field(trim($row[$first_name_idx])) : '';
			$last_name = isset($row[$last_name_idx]) ? sanitize_text_field(trim($row[$last_name_idx])) : '';
			$role = isset($row[$role_idx]) && !empty(trim($row[$role_idx])) ? sanitize_key(trim($row[$role_idx])) : $default_role;
			$password = isset($row[$password_idx]) && !empty(trim($row[$password_idx])) ? trim($row[$password_idx]) : wp_generate_password(16, true, false);
			// Collect custom meta from additional headers/columns
			$reserved = ['email','first_name','last_name','role','password','username'];
			$custom_meta = [];
			foreach ($header as $i => $col) {
				$col = trim(strtolower($col));
				if (in_array($col, $reserved, true)) {
					continue;
				}
				if (!isset($row[$i])) {
					continue;
				}
				$val = trim((string) $row[$i]);
				if ($val === '') {
					continue;
				}
				$custom_meta[$col] = sanitize_text_field($val);
			}

			$existing_user = get_user_by('email', $email);

			if ($existing_user) {
				$added_to_subsite = self::maybe_add_existing_network_user_to_current_site((int) $existing_user->ID, $role, $settings);
				if (!empty($settings['update_existing_users'])) {
					// Capture old values
					$old_values = [
						'first_name' => $existing_user->first_name,
						'last_name' => $existing_user->last_name,
						'role' => implode(', ', $existing_user->roles),
					];
					
					$update_data = [
						'ID' => $existing_user->ID,
						'first_name' => $first_name,
						'last_name' => $last_name,
						'role' => $role,
					];

					$password_changed = !empty($row[$password_idx]);
					if ($password_changed) {
						wp_set_password($password, $existing_user->ID);
					}

					wp_update_user($update_data);
					// Apply custom meta
					if (!empty($custom_meta)) {
						foreach ($custom_meta as $meta_key => $meta_value) {
							update_user_meta($existing_user->ID, $meta_key, $meta_value);
						}
					}
					$updated++;

					$detailed_log[] = [
						'row' => $row_num,
						'email' => $email,
						'user_id' => $existing_user->ID,
						'status' => 'updated',
						'message' => __('User updated', 'user-manager'),
					];

					User_Manager_Core::add_activity_log('user_updated', $existing_user->ID, 'SFTP Import (Updated)', [
						'email_sent' => $send_email,
						'template_id' => $template_id,
						'login_url' => $login_url,
						'import_id' => $import_id,
						'source_file' => basename($filepath),
						'password_changed' => $password_changed,
						'added_to_subsite' => $added_to_subsite,
						'old_values' => $old_values,
						'new_values' => [
							'first_name' => $first_name,
							'last_name' => $last_name,
							'role' => $role,
						],
					]);

					if ($send_email) {
						User_Manager_Email::send_user_email($existing_user->ID, $password, $login_url, $template_id);
						$emails_sent++;
					}
				} elseif ($added_to_subsite) {
					$created++;
					$detailed_log[] = [
						'row' => $row_num,
						'email' => $email,
						'user_id' => $existing_user->ID,
						'status' => 'added',
						'message' => __('Existing network user added to this site', 'user-manager'),
					];
					User_Manager_Core::add_activity_log('user_added_to_subsite', $existing_user->ID, 'SFTP Import (Added to Site)', [
						'import_id' => $import_id,
						'source_file' => basename($filepath),
						'row' => $row_num,
						'email' => $email,
						'role' => $role,
						'blog_id' => get_current_blog_id(),
						'multisite' => is_multisite(),
						'update_existing_users' => false,
					]);
				} else {
					$skipped++;
					$detailed_log[] = [
						'row' => $row_num,
						'email' => $email,
						'status' => 'skipped',
						'message' => __('User already exists', 'user-manager'),
					];
					// Log skipped
					User_Manager_Core::add_activity_log('user_skipped', $existing_user->ID, 'SFTP Import', [
						'reason' => __('User already exists and update setting is disabled', 'user-manager'),
						'import_id' => $import_id,
						'source_file' => basename($filepath),
						'row' => $row_num,
					]);
				}
				continue;
			}

			// Create new user
			$username = sanitize_user(current(explode('@', $email)), true);
			if (username_exists($username)) {
				$username = $username . '_' . wp_rand(100, 999);
			}

			$user_data = [
				'user_login' => $username,
				'user_email' => $email,
				'user_pass' => $password,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'role' => $role,
			];

			$user_id = wp_insert_user($user_data);

			if (is_wp_error($user_id)) {
				$failed++;
				$detailed_log[] = [
					'row' => $row_num,
					'email' => $email,
					'status' => 'failed',
					'message' => $user_id->get_error_message(),
				];
				// Log error
				User_Manager_Core::add_activity_log('user_create_failed', 0, 'SFTP Import', [
					'error' => $user_id->get_error_message(),
					'attempted_email' => $email,
					'attempted_username' => $username,
					'import_id' => $import_id,
					'source_file' => basename($filepath),
					'row' => $row_num,
				]);
				continue;
			}

			// Apply custom meta
			if (!empty($custom_meta)) {
				foreach ($custom_meta as $meta_key => $meta_value) {
					update_user_meta($user_id, $meta_key, $meta_value);
				}
			}
			$created++;
			$detailed_log[] = [
				'row' => $row_num,
				'email' => $email,
				'user_id' => $user_id,
				'status' => 'created',
				'message' => __('User created successfully', 'user-manager'),
			];

			User_Manager_Core::add_activity_log('user_created', $user_id, 'SFTP Import', [
				'email_sent' => $send_email,
				'template_id' => $template_id,
				'login_url' => $login_url,
				'import_id' => $import_id,
				'source_file' => basename($filepath),
				'new_values' => [
					'email' => $email,
					'username' => $username,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'role' => $role,
					'password_generated' => empty($row[$password_idx]),
				],
			]);

			if ($send_email) {
				User_Manager_Email::send_user_email($user_id, $password, $login_url, $template_id);
				$emails_sent++;
			}
		}

		// Store the detailed log in a transient for later viewing
		set_transient('um_import_log_' . $import_id, [
			'filepath' => $filepath,
			'filename' => basename($filepath),
			'created' => $created,
			'updated' => $updated,
			'skipped' => $skipped,
			'failed' => $failed,
			'emails_sent' => $emails_sent,
			'total' => count($rows),
			'log' => $detailed_log,
			'imported_at' => current_time('timestamp'),
			'imported_by' => get_current_user_id(),
		], WEEK_IN_SECONDS);

		// Mark file as imported
		User_Manager_Core::mark_file_imported($filepath, $import_id);

		// Redirect with success message
		$message = 'sftp_imported';
		if ($send_email && $emails_sent > 0) {
			$message = 'sftp_imported_email_sent';
		}

		if ($settings['log_activity'] ?? true) {
			User_Manager_Core::add_activity_log('sftp_import_summary', 0, 'SFTP Import Summary', [
				'created' => $created,
				'updated' => $updated,
				'skipped' => $skipped,
				'failed' => $failed,
				'emails_sent' => $emails_sent,
				'import_id' => $import_id,
				'source_file' => basename($filepath),
			]);
		}

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, $message, [
			'created' => $created,
			'updated' => $updated,
			'skipped' => $skipped,
			'failed' => $failed,
			'emails' => $emails_sent,
		]));
		exit;
	}

	/**
	 * Whether auto-adding existing network users to current sub-site is enabled.
	 *
	 * Defaults to true when the setting does not yet exist.
	 */
	private static function should_auto_add_existing_network_user_to_subsite(array $settings): bool {
		if (!is_multisite()) {
			return false;
		}
		if (!array_key_exists('add_existing_network_user_to_subsite', $settings)) {
			return true;
		}
		return !empty($settings['add_existing_network_user_to_subsite']);
	}

	/**
	 * Add an existing network user to the current site when enabled.
	 */
	private static function maybe_add_existing_network_user_to_current_site(int $user_id, string $requested_role, array $settings): bool {
		if ($user_id <= 0 || !self::should_auto_add_existing_network_user_to_subsite($settings)) {
			return false;
		}

		$blog_id = (int) get_current_blog_id();
		if ($blog_id <= 0 || is_user_member_of_blog($user_id, $blog_id)) {
			return false;
		}

		$role = self::resolve_multisite_membership_role($requested_role);
		$result = add_user_to_blog($blog_id, $user_id, $role);
		return !is_wp_error($result);
	}

	/**
	 * Ensure the role exists before assigning membership on multisite.
	 */
	private static function resolve_multisite_membership_role(string $requested_role): string {
		$role = sanitize_key($requested_role);
		if ($role === '') {
			$role = sanitize_key((string) get_option('default_role', 'subscriber'));
		}
		if ($role === '') {
			$role = 'subscriber';
		}

		if (function_exists('wp_roles')) {
			$wp_roles = wp_roles();
			$available_roles = ($wp_roles && method_exists($wp_roles, 'get_names')) ? array_keys((array) $wp_roles->get_names()) : [];
			if (!empty($available_roles) && !in_array($role, $available_roles, true)) {
				$default_role = sanitize_key((string) get_option('default_role', 'subscriber'));
				$role = in_array($default_role, $available_roles, true) ? $default_role : 'subscriber';
			}
		}

		return $role;
	}

	/**
	 * Handle email users action.
	 */
	public static function handle_email_users(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_email_users');

		$emails_raw = isset($_POST['emails']) ? sanitize_textarea_field(wp_unslash($_POST['emails'])) : '';
		$template_id = isset($_POST['email_template']) ? sanitize_key($_POST['email_template']) : '';
		$login_url = isset($_POST['login_url']) ? sanitize_text_field(wp_unslash($_POST['login_url'])) : '/my-account/';
		$coupon_code = isset($_POST['coupon_code_for_template']) ? sanitize_text_field(wp_unslash($_POST['coupon_code_for_template'])) : '';
		$send_to_all = isset($_POST['send_to_all_emails']) && $_POST['send_to_all_emails'] === '1';
		$selected_roles = isset($_POST['selected_roles']) ? sanitize_text_field(wp_unslash($_POST['selected_roles'])) : '';
		$selected_lists = isset($_POST['selected_lists']) ? sanitize_text_field(wp_unslash($_POST['selected_lists'])) : '';

		// Parse emails (one per line) - handle both \r\n and \n line endings
		$emails_raw = str_replace("\r\n", "\n", $emails_raw);
		$emails_raw = str_replace("\r", "\n", $emails_raw);
		$emails = array_filter(array_map('trim', explode("\n", $emails_raw)));
		$emails = array_map('sanitize_email', $emails);
		$emails = array_filter($emails, function($email) {
			return !empty($email) && is_email($email);
		});
		$emails = array_unique($emails);

		if (empty($emails)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'no_emails'));
			exit;
		}

		if (empty($template_id)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'no_template'));
			exit;
		}

		$settings = User_Manager_Core::get_settings();
		$sent_count = 0;
		$not_found_count = 0;
		
		// Check if throttling is enabled
		$throttle_enabled = !empty($settings['throttle_emails_enabled']);
		$throttle_count = !empty($settings['throttle_emails_count']) ? (int) $settings['throttle_emails_count'] : 50;
		$emails_processed = 0;
		$remaining_emails = [];

		foreach ($emails as $email) {
			// If throttling is enabled and we've reached the limit, store remaining emails
			if ($throttle_enabled && $emails_processed >= $throttle_count) {
				$remaining_emails[] = $email;
				continue;
			}
			$user = get_user_by('email', $email);
			
			if ($send_to_all) {
				// Send to all emails regardless of user status
				if ($user) {
					// User exists, use user email method
					User_Manager_Email::send_user_email($user->ID, '••••••••', $login_url, $template_id, $coupon_code);
					$sent_count++;
					
					// Log activity
					if ($settings['log_activity'] ?? true) {
						$log_data = [
							'template_id' => $template_id,
							'login_url' => $login_url,
							'user_email' => $user->user_email,
						];
						if (!empty($selected_roles)) {
							$log_data['selected_roles'] = $selected_roles;
						}
						if (!empty($selected_lists)) {
							$log_data['selected_lists'] = $selected_lists;
						}
						User_Manager_Core::add_activity_log('email_sent', $user->ID, 'Email Users', $log_data);
					}
				} else {
					// Not a user, send to address directly
					$sent = User_Manager_Email::send_email_to_address($email, $login_url, $template_id, $coupon_code);
					if ($sent) {
						$sent_count++;
						
						// Log activity
						if ($settings['log_activity'] ?? true) {
							$log_data = [
								'template_id' => $template_id,
								'login_url' => $login_url,
								'user_email' => $email,
								'note' => __('Sent to non-user email address', 'user-manager'),
							];
							if (!empty($selected_roles)) {
								$log_data['selected_roles'] = $selected_roles;
							}
							if (!empty($selected_lists)) {
								$log_data['selected_lists'] = $selected_lists;
							}
							User_Manager_Core::add_activity_log('email_sent', 0, 'Email Users', $log_data);
						}
					}
				}
			} else {
				// Original behavior: only send to existing users
				if (!$user) {
					$not_found_count++;
					// Log not found
					if ($settings['log_activity'] ?? true) {
						User_Manager_Core::add_activity_log('email_failed', 0, 'Email Users', [
							'error' => __('User not found', 'user-manager'),
							'attempted_email' => $email,
							'template_id' => $template_id,
						]);
					}
					continue;
				}

				// Send email (password placeholder will show masked value)
				User_Manager_Email::send_user_email($user->ID, '••••••••', $login_url, $template_id, $coupon_code);
				$sent_count++;

				// Log activity
				if ($settings['log_activity'] ?? true) {
					$log_data = [
						'template_id' => $template_id,
						'login_url' => $login_url,
						'user_email' => $user->user_email,
					];
					if (!empty($selected_roles)) {
						$log_data['selected_roles'] = $selected_roles;
					}
					if (!empty($selected_lists)) {
						$log_data['selected_lists'] = $selected_lists;
					}
					User_Manager_Core::add_activity_log('email_sent', $user->ID, 'Email Users', $log_data);
				}
			}
			
			// Increment processed count after attempting to send
			$emails_processed++;
		}
		
		// If there are remaining emails and throttling is enabled, store them for next batch
		if ($throttle_enabled && !empty($remaining_emails)) {
			$batch_data = [
				'emails' => $remaining_emails,
				'template_id' => $template_id,
				'login_url' => $login_url,
				'coupon_code' => $coupon_code,
				'send_to_all' => $send_to_all,
				'selected_roles' => $selected_roles,
				'selected_lists' => $selected_lists,
				'created_at' => current_time('mysql'),
				'total_original' => count($emails),
				'total_sent_so_far' => $sent_count,
			];
			
			// Store in transient (expires in 30 days to allow for delayed sending)
			$transient_key = 'um_email_batch_' . get_current_user_id();
			set_transient($transient_key, $batch_data, 30 * DAY_IN_SECONDS);
			
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'emails_sent_batch', [
				'sent' => $sent_count,
				'not_found' => $not_found_count,
				'remaining' => count($remaining_emails),
				'total' => count($emails),
			]));
			exit;
		}

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'emails_sent', [
			'sent' => $sent_count,
			'not_found' => $not_found_count,
		]));
		exit;
	}

	/**
	 * Handle email users next batch action.
	 */
	public static function handle_email_users_next_batch(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_email_users_next_batch');

		$transient_key = 'um_email_batch_' . get_current_user_id();
		$batch_data = get_transient($transient_key);
		
		if (empty($batch_data) || !is_array($batch_data)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'no_batch'));
			exit;
		}
		
		$emails = $batch_data['emails'] ?? [];
		$template_id = $batch_data['template_id'] ?? '';
		$login_url = $batch_data['login_url'] ?? '/my-account/';
		$coupon_code = $batch_data['coupon_code'] ?? '';
		$send_to_all = !empty($batch_data['send_to_all']);
		$selected_roles = $batch_data['selected_roles'] ?? '';
		$selected_lists = $batch_data['selected_lists'] ?? '';
		
		if (empty($emails) || empty($template_id)) {
			delete_transient($transient_key);
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'batch_complete'));
			exit;
		}
		
		$settings = User_Manager_Core::get_settings();
		$throttle_enabled = !empty($settings['throttle_emails_enabled']);
		$throttle_count = !empty($settings['throttle_emails_count']) ? (int) $settings['throttle_emails_count'] : 50;
		
		$sent_count = 0;
		$not_found_count = 0;
		$emails_processed = 0;
		$remaining_emails = [];
		
		foreach ($emails as $email) {
			// If throttling is enabled and we've reached the limit, store remaining emails
			if ($throttle_enabled && $emails_processed >= $throttle_count) {
				$remaining_emails[] = $email;
				continue;
			}
			
			$user = get_user_by('email', $email);
			
			if ($send_to_all) {
				// Send to all emails regardless of user status
				if ($user) {
					// User exists, use user email method
					User_Manager_Email::send_user_email($user->ID, '••••••••', $login_url, $template_id, $coupon_code);
					$sent_count++;
					
					// Log activity
					if ($settings['log_activity'] ?? true) {
						$log_data = [
							'template_id' => $template_id,
							'login_url' => $login_url,
							'user_email' => $user->user_email,
							'batch' => true,
						];
						if (!empty($selected_roles)) {
							$log_data['selected_roles'] = $selected_roles;
						}
						if (!empty($selected_lists)) {
							$log_data['selected_lists'] = $selected_lists;
						}
						User_Manager_Core::add_activity_log('email_sent', $user->ID, 'Email Users', $log_data);
					}
				} else {
					// Not a user, send to address directly
					$sent = User_Manager_Email::send_email_to_address($email, $login_url, $template_id, $coupon_code);
					if ($sent) {
						$sent_count++;
						
						// Log activity
						if ($settings['log_activity'] ?? true) {
							$log_data = [
								'template_id' => $template_id,
								'login_url' => $login_url,
								'user_email' => $email,
								'note' => __('Sent to non-user email address', 'user-manager'),
								'batch' => true,
							];
							if (!empty($selected_roles)) {
								$log_data['selected_roles'] = $selected_roles;
							}
							if (!empty($selected_lists)) {
								$log_data['selected_lists'] = $selected_lists;
							}
							User_Manager_Core::add_activity_log('email_sent', 0, 'Email Users', $log_data);
						}
					}
				}
			} else {
				// Original behavior: only send to existing users
				if (!$user) {
					$not_found_count++;
					// Log not found
					if ($settings['log_activity'] ?? true) {
						User_Manager_Core::add_activity_log('email_failed', 0, 'Email Users', [
							'error' => __('User not found', 'user-manager'),
							'attempted_email' => $email,
							'template_id' => $template_id,
							'batch' => true,
						]);
					}
					continue;
				}

				// Send email (password placeholder will show masked value)
				User_Manager_Email::send_user_email($user->ID, '••••••••', $login_url, $template_id, $coupon_code);
				$sent_count++;

				// Log activity
				if ($settings['log_activity'] ?? true) {
					$log_data = [
						'template_id' => $template_id,
						'login_url' => $login_url,
						'user_email' => $user->user_email,
						'batch' => true,
					];
					if (!empty($selected_roles)) {
						$log_data['selected_roles'] = $selected_roles;
					}
					if (!empty($selected_lists)) {
						$log_data['selected_lists'] = $selected_lists;
					}
					User_Manager_Core::add_activity_log('email_sent', $user->ID, 'Email Users', $log_data);
				}
			}
			
			// Increment processed count after attempting to send
			$emails_processed++;
		}
		
		// Update batch data with remaining emails
		if (!empty($remaining_emails)) {
			$batch_data['emails'] = $remaining_emails;
			$batch_data['total_sent_so_far'] = ($batch_data['total_sent_so_far'] ?? 0) + $sent_count;
			set_transient($transient_key, $batch_data, 30 * DAY_IN_SECONDS);
			
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'emails_sent_batch', [
				'sent' => $sent_count,
				'not_found' => $not_found_count,
				'remaining' => count($remaining_emails),
				'total' => $batch_data['total_original'] ?? 0,
				'total_sent' => $batch_data['total_sent_so_far'],
			]));
		} else {
			// All emails sent, delete transient
			delete_transient($transient_key);
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'batch_complete', [
				'sent' => $sent_count,
				'total' => $batch_data['total_original'] ?? 0,
			]));
		}
		exit;
	}

	/**
	 * Build redirect URL back to Add-ons > Send SMS Text section.
	 */
	private static function get_send_sms_text_redirect_url(string $message, array $extra = []): string {
		$extra['addon_section'] = 'send-sms-text';
		return User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_ADDONS, $message, $extra);
	}

	/**
	 * Build a map of normalized phone => user context.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function get_user_phone_directory(): array {
		$directory = [];
		$users = get_users(['fields' => 'all']);
		foreach ($users as $user) {
			if (!$user instanceof WP_User) {
				continue;
			}
			$user_id = (int) $user->ID;
			$phones = User_Manager_SMS::get_user_phone_numbers($user_id);
			if (empty($phones)) {
				continue;
			}
			$context = [
				'user_id'    => $user_id,
				'email'      => (string) $user->user_email,
				'username'   => (string) $user->user_login,
				'first_name' => (string) $user->first_name,
				'last_name'  => (string) $user->last_name,
			];
			foreach ($phones as $phone) {
				foreach (self::get_phone_lookup_keys($phone) as $lookup_key) {
					if (!isset($directory[$lookup_key])) {
						$directory[$lookup_key] = $context;
					}
				}
			}
		}

		return $directory;
	}

	/**
	 * Build phone lookup keys so equivalent formats map to the same user.
	 *
	 * @return array<int,string>
	 */
	private static function get_phone_lookup_keys(string $phone): array {
		$keys = [];
		$normalized = User_Manager_SMS::normalize_phone_number($phone);
		if ($normalized !== '') {
			$keys[] = $normalized;
		}

		$digits = preg_replace('/\D+/', '', $phone);
		$digits = is_string($digits) ? $digits : '';
		if ($digits !== '') {
			$keys[] = $digits;
			$keys[] = '+' . $digits;
			if (strlen($digits) === 10) {
				$keys[] = '+1' . $digits;
				$keys[] = '1' . $digits;
			} elseif (strlen($digits) === 11 && strpos($digits, '1') === 0) {
				$last_ten = substr($digits, 1);
				if ($last_ten !== '') {
					$keys[] = $last_ten;
					$keys[] = '+1' . $last_ten;
				}
			}
		}

		return array_values(array_unique(array_filter($keys)));
	}

	/**
	 * Resolve user context from phone directory using flexible phone key matching.
	 *
	 * @param array<string,array<string,mixed>> $phone_directory
	 * @return array<string,mixed>|null
	 */
	private static function get_user_context_for_phone(string $phone_number, array $phone_directory): ?array {
		foreach (self::get_phone_lookup_keys($phone_number) as $lookup_key) {
			if (isset($phone_directory[$lookup_key]) && is_array($phone_directory[$lookup_key])) {
				return $phone_directory[$lookup_key];
			}
		}
		return null;
	}

	/**
	 * Handle Send SMS Text action.
	 */
	public static function handle_send_sms_texts(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_send_sms_texts');

		$settings = User_Manager_Core::get_settings();
		if (empty($settings['send_sms_text_enabled'])) {
			wp_safe_redirect(self::get_send_sms_text_redirect_url('send_sms_disabled'));
			exit;
		}

		$api_token = isset($settings['simple_texting_api_token']) ? trim((string) $settings['simple_texting_api_token']) : '';
		if ($api_token === '') {
			wp_safe_redirect(self::get_send_sms_text_redirect_url('sms_token_missing'));
			exit;
		}

		$phone_numbers_raw = isset($_POST['phone_numbers']) ? sanitize_textarea_field(wp_unslash($_POST['phone_numbers'])) : '';
		$template_id = isset($_POST['sms_template']) ? sanitize_key(wp_unslash($_POST['sms_template'])) : '';
		$login_url = isset($_POST['login_url']) ? sanitize_text_field(wp_unslash($_POST['login_url'])) : '/my-account/';
		$coupon_code = isset($_POST['coupon_code_for_template']) ? sanitize_text_field(wp_unslash($_POST['coupon_code_for_template'])) : '';
		$send_to_all_phone_numbers = isset($_POST['send_to_all_phone_numbers']) && $_POST['send_to_all_phone_numbers'] === '1';
		$selected_roles = isset($_POST['selected_roles']) ? sanitize_text_field(wp_unslash($_POST['selected_roles'])) : '';
		$selected_lists = isset($_POST['selected_lists']) ? sanitize_text_field(wp_unslash($_POST['selected_lists'])) : '';

		$phone_numbers = User_Manager_SMS::parse_phone_numbers($phone_numbers_raw);
		if (empty($phone_numbers)) {
			wp_safe_redirect(self::get_send_sms_text_redirect_url('no_phone_numbers'));
			exit;
		}

		if ($template_id === '') {
			wp_safe_redirect(self::get_send_sms_text_redirect_url('no_sms_template'));
			exit;
		}

		$throttle_enabled = !empty($settings['throttle_emails_enabled']);
		$throttle_count = !empty($settings['throttle_emails_count']) ? (int) $settings['throttle_emails_count'] : 50;
		$throttle_count = max(1, $throttle_count);

		$total_original = count($phone_numbers);
		$batch_phone_numbers = $phone_numbers;
		$remaining_phone_numbers = [];
		if ($throttle_enabled && $total_original > $throttle_count) {
			$batch_phone_numbers = array_slice($phone_numbers, 0, $throttle_count);
			$remaining_phone_numbers = array_slice($phone_numbers, $throttle_count);
		}

		$phone_directory = self::get_user_phone_directory();
		$sent_count = 0;
		$not_found_count = 0;
		$failed_count = 0;

		foreach ($batch_phone_numbers as $phone_number) {
			$phone_number = User_Manager_SMS::normalize_phone_number((string) $phone_number);
			if (!User_Manager_SMS::is_valid_phone_number($phone_number)) {
				$failed_count++;
				continue;
			}

			$user_context = self::get_user_context_for_phone($phone_number, $phone_directory);
			if (!is_array($user_context)) {
				$not_found_count++;
			}

			if (!is_array($user_context)) {
				$user_context = [
					'user_id'    => 0,
					'email'      => '',
					'username'   => ltrim($phone_number, '+'),
					'first_name' => '',
					'last_name'  => '',
				];
			}

			$sent = User_Manager_SMS::send_sms_to_phone($phone_number, $template_id, $login_url, $coupon_code, $user_context);
			if (!$sent) {
				$failed_count++;
				continue;
			}

			$sent_count++;
			$user_id = isset($user_context['user_id']) ? (int) $user_context['user_id'] : 0;
			$log_data = [
				'sms_template' => $template_id,
				'login_url'    => $login_url,
				'phone_number' => $phone_number,
				'is_non_user'  => $user_id <= 0,
			];
			if ($coupon_code !== '') {
				$log_data['coupon_code'] = $coupon_code;
			}
			if ($selected_roles !== '') {
				$log_data['selected_roles'] = $selected_roles;
			}
			if ($selected_lists !== '') {
				$log_data['selected_lists'] = $selected_lists;
			}
			User_Manager_Core::add_activity_log('sms_sent', $user_id, 'Send SMS Text', $log_data);
		}

		$transient_key = 'um_sms_batch_' . get_current_user_id();
		if (!empty($remaining_phone_numbers)) {
			$batch_data = [
				'phone_numbers'               => $remaining_phone_numbers,
				'total_original'              => $total_original,
				'total_sent_so_far'           => $sent_count,
				'template_id'                 => $template_id,
				'login_url'                   => $login_url,
				'coupon_code'                 => $coupon_code,
				'send_to_all_phone_numbers'   => $send_to_all_phone_numbers,
				'selected_roles'              => $selected_roles,
				'selected_lists'              => $selected_lists,
				'created_at'                  => current_time('mysql'),
			];
			set_transient($transient_key, $batch_data, 30 * DAY_IN_SECONDS);

			wp_safe_redirect(self::get_send_sms_text_redirect_url('texts_sent_batch', [
				'sent'       => $sent_count,
				'not_found'  => $not_found_count,
				'failed'     => $failed_count,
				'remaining'  => count($remaining_phone_numbers),
				'total'      => $total_original,
				'total_sent' => $sent_count,
			]));
			exit;
		}

		delete_transient($transient_key);
		wp_safe_redirect(self::get_send_sms_text_redirect_url('texts_sent', [
			'sent'      => $sent_count,
			'not_found' => $not_found_count,
			'failed'    => $failed_count,
		]));
		exit;
	}

	/**
	 * Handle Send SMS Text next-batch action.
	 */
	public static function handle_send_sms_texts_next_batch(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_send_sms_texts_next_batch');

		$settings = User_Manager_Core::get_settings();
		if (empty($settings['send_sms_text_enabled'])) {
			wp_safe_redirect(self::get_send_sms_text_redirect_url('send_sms_disabled'));
			exit;
		}

		$api_token = isset($settings['simple_texting_api_token']) ? trim((string) $settings['simple_texting_api_token']) : '';
		if ($api_token === '') {
			wp_safe_redirect(self::get_send_sms_text_redirect_url('sms_token_missing'));
			exit;
		}

		$transient_key = 'um_sms_batch_' . get_current_user_id();
		$batch_data = get_transient($transient_key);
		if (empty($batch_data) || !is_array($batch_data)) {
			wp_safe_redirect(self::get_send_sms_text_redirect_url('no_text_batch'));
			exit;
		}

		$phone_numbers = isset($batch_data['phone_numbers']) && is_array($batch_data['phone_numbers']) ? $batch_data['phone_numbers'] : [];
		if (empty($phone_numbers)) {
			delete_transient($transient_key);
			wp_safe_redirect(self::get_send_sms_text_redirect_url('no_text_batch'));
			exit;
		}

		$template_id = isset($batch_data['template_id']) ? sanitize_key((string) $batch_data['template_id']) : '';
		$login_url = isset($batch_data['login_url']) ? sanitize_text_field((string) $batch_data['login_url']) : '/my-account/';
		$coupon_code = isset($batch_data['coupon_code']) ? sanitize_text_field((string) $batch_data['coupon_code']) : '';
		$selected_roles = isset($batch_data['selected_roles']) ? sanitize_text_field((string) $batch_data['selected_roles']) : '';
		$selected_lists = isset($batch_data['selected_lists']) ? sanitize_text_field((string) $batch_data['selected_lists']) : '';

		if ($template_id === '') {
			delete_transient($transient_key);
			wp_safe_redirect(self::get_send_sms_text_redirect_url('no_sms_template'));
			exit;
		}

		$throttle_enabled = !empty($settings['throttle_emails_enabled']);
		$throttle_count = !empty($settings['throttle_emails_count']) ? (int) $settings['throttle_emails_count'] : 50;
		$throttle_count = max(1, $throttle_count);

		$batch_phone_numbers = $phone_numbers;
		$remaining_phone_numbers = [];
		if ($throttle_enabled && count($phone_numbers) > $throttle_count) {
			$batch_phone_numbers = array_slice($phone_numbers, 0, $throttle_count);
			$remaining_phone_numbers = array_slice($phone_numbers, $throttle_count);
		}

		$phone_directory = self::get_user_phone_directory();
		$sent_count = 0;
		$not_found_count = 0;
		$failed_count = 0;

		foreach ($batch_phone_numbers as $phone_number) {
			$phone_number = User_Manager_SMS::normalize_phone_number((string) $phone_number);
			if (!User_Manager_SMS::is_valid_phone_number($phone_number)) {
				$failed_count++;
				continue;
			}

			$user_context = self::get_user_context_for_phone($phone_number, $phone_directory);
			if (!is_array($user_context)) {
				$not_found_count++;
			}

			if (!is_array($user_context)) {
				$user_context = [
					'user_id'    => 0,
					'email'      => '',
					'username'   => ltrim($phone_number, '+'),
					'first_name' => '',
					'last_name'  => '',
				];
			}

			$sent = User_Manager_SMS::send_sms_to_phone($phone_number, $template_id, $login_url, $coupon_code, $user_context);
			if (!$sent) {
				$failed_count++;
				continue;
			}

			$sent_count++;
			$user_id = isset($user_context['user_id']) ? (int) $user_context['user_id'] : 0;
			$log_data = [
				'sms_template' => $template_id,
				'login_url'    => $login_url,
				'phone_number' => $phone_number,
				'is_non_user'  => $user_id <= 0,
				'batch'        => true,
			];
			if ($coupon_code !== '') {
				$log_data['coupon_code'] = $coupon_code;
			}
			if ($selected_roles !== '') {
				$log_data['selected_roles'] = $selected_roles;
			}
			if ($selected_lists !== '') {
				$log_data['selected_lists'] = $selected_lists;
			}
			User_Manager_Core::add_activity_log('sms_sent', $user_id, 'Send SMS Text', $log_data);
		}

		$total_sent_so_far = isset($batch_data['total_sent_so_far']) ? absint($batch_data['total_sent_so_far']) : 0;
		$total_sent_so_far += $sent_count;
		$total_original = isset($batch_data['total_original']) ? absint($batch_data['total_original']) : count($batch_phone_numbers);

		if (!empty($remaining_phone_numbers)) {
			$batch_data['phone_numbers'] = $remaining_phone_numbers;
			$batch_data['total_sent_so_far'] = $total_sent_so_far;
			set_transient($transient_key, $batch_data, 30 * DAY_IN_SECONDS);

			wp_safe_redirect(self::get_send_sms_text_redirect_url('texts_sent_batch', [
				'sent'       => $sent_count,
				'not_found'  => $not_found_count,
				'failed'     => $failed_count,
				'remaining'  => count($remaining_phone_numbers),
				'total'      => $total_original,
				'total_sent' => $total_sent_so_far,
			]));
			exit;
		}

		delete_transient($transient_key);
		wp_safe_redirect(self::get_send_sms_text_redirect_url('text_batch_complete', [
			'sent'      => $sent_count,
			'total'     => $total_sent_so_far,
			'not_found' => $not_found_count,
			'failed'    => $failed_count,
		]));
		exit;
	}

	/**
	 * Handle save email list action.
	 */
	public static function handle_save_email_list(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_save_email_list');

		$list_id = isset($_POST['list_id']) ? sanitize_text_field(wp_unslash($_POST['list_id'])) : '';
		$title = isset($_POST['list_title']) ? sanitize_text_field(wp_unslash($_POST['list_title'])) : '';
		$emails_raw = isset($_POST['list_emails']) ? sanitize_textarea_field(wp_unslash($_POST['list_emails'])) : '';

		if (empty($title)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'list_title_required'));
			exit;
		}

		// Parse emails
		$emails_raw = str_replace("\r\n", "\n", $emails_raw);
		$emails_raw = str_replace("\r", "\n", $emails_raw);
		$emails = array_filter(array_map('trim', explode("\n", $emails_raw)));
		$emails = array_map('sanitize_email', $emails);
		$emails = array_filter($emails, function($email) {
			return !empty($email) && is_email($email);
		});
		$emails = array_unique($emails);

		// Get existing lists
		$lists = get_option('um_custom_email_lists', []);
		if (!is_array($lists)) {
			$lists = [];
		}

		// Create or update list
		$is_new = empty($list_id);
		$old_title = '';
		$old_email_count = 0;
		
		if ($is_new) {
			// New list - generate ID
			$list_id = 'list_' . time() . '_' . wp_generate_password(8, false);
		} else {
			// Existing list - get old data for logging
			$old_list = $lists[$list_id] ?? [];
			$old_title = $old_list['title'] ?? '';
			$old_email_count = count($old_list['emails'] ?? []);
		}

		$lists[$list_id] = [
			'title' => $title,
			'emails' => $emails,
			'created_at' => isset($lists[$list_id]['created_at']) ? $lists[$list_id]['created_at'] : current_time('mysql'),
			'updated_at' => current_time('mysql'),
		];

		update_option('um_custom_email_lists', $lists);

		// Log admin activity
		$settings = User_Manager_Core::get_settings();
		$log_admin_activity = isset($settings['log_admin_activity']) ? !empty($settings['log_admin_activity']) : true;
		
		if ($log_admin_activity) {
			$user_id = get_current_user_id();
			$action = $is_new ? 'email_list_created' : 'email_list_updated';
			$extra = [
				'list_id' => $list_id,
				'list_title' => $title,
				'email_count' => count($emails),
			];
			
			if (!$is_new) {
				$extra['old_title'] = $old_title;
				$extra['old_email_count'] = $old_email_count;
			}
			
			User_Manager_Core::add_activity_log($action, $user_id, 'Email Users', $extra);
		}

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'list_saved'));
		exit;
	}

	/**
	 * Handle delete email list action.
	 */
	public static function handle_delete_email_list(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_delete_email_list');

		$list_id = isset($_POST['list_id']) ? sanitize_text_field(wp_unslash($_POST['list_id'])) : '';

		if (empty($list_id)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'list_not_found'));
			exit;
		}

		// Get existing lists
		$lists = get_option('um_custom_email_lists', []);
		if (!is_array($lists)) {
			$lists = [];
		}

		if (isset($lists[$list_id])) {
			$deleted_list = $lists[$list_id];
			$list_title = $deleted_list['title'] ?? '';
			$email_count = count($deleted_list['emails'] ?? []);
			
			unset($lists[$list_id]);
			update_option('um_custom_email_lists', $lists);
			
			// Log admin activity
			$settings = User_Manager_Core::get_settings();
			$log_admin_activity = isset($settings['log_admin_activity']) ? !empty($settings['log_admin_activity']) : true;
			
			if ($log_admin_activity) {
				$user_id = get_current_user_id();
				User_Manager_Core::add_activity_log('email_list_deleted', $user_id, 'Email Users', [
					'list_id' => $list_id,
					'list_title' => $list_title,
					'email_count' => $email_count,
				]);
			}
			
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'list_deleted'));
		} else {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'list_not_found'));
		}
		exit;
	}

	/**
	 * Download a saved custom email list as CSV.
	 */
	public static function handle_download_email_list_csv(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		$list_id = isset($_POST['list_id']) ? sanitize_text_field(wp_unslash($_POST['list_id'])) : '';
		if ($list_id === '') {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'list_not_found'));
			exit;
		}

		check_admin_referer('user_manager_download_email_list_csv_' . $list_id);

		$lists = get_option('um_custom_email_lists', []);
		if (!is_array($lists) || !isset($lists[$list_id]) || !is_array($lists[$list_id])) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_EMAIL_USERS, 'list_not_found'));
			exit;
		}

		$list_data = $lists[$list_id];
		$title     = isset($list_data['title']) ? sanitize_text_field((string) $list_data['title']) : '';
		$filename_base = sanitize_file_name($title !== '' ? $title : 'email-list');
		if ($filename_base === '') {
			$filename_base = 'email-list';
		}

		$emails = isset($list_data['emails']) && is_array($list_data['emails']) ? $list_data['emails'] : [];

		nocache_headers();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename_base . '-emails.csv"');
		header('Pragma: no-cache');
		header('Expires: 0');

		$output = fopen('php://output', 'w');
		if ($output === false) {
			exit;
		}

		fputcsv($output, ['email']);
		foreach ($emails as $email_raw) {
			$email = sanitize_email((string) $email_raw);
			if ($email === '' || !is_email($email)) {
				continue;
			}
			fputcsv($output, [$email]);
		}

		fclose($output);
		exit;
	}

	/**
	 * Handle create directory action.
	 */
	public static function handle_create_directory(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_create_directory');

		$directory = isset($_POST['directory']) ? sanitize_text_field(wp_unslash($_POST['directory'])) : '';

		if (empty($directory)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'directory_error'));
			exit;
		}

		// Validate that the directory is in the configured list
		$allowed_dirs = User_Manager_Core::get_sftp_directories();
		if (!in_array($directory, $allowed_dirs)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'directory_not_allowed'));
			exit;
		}

		// Try to create the directory
		if (!wp_mkdir_p($directory)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'directory_create_failed'));
			exit;
		}

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_BULK_CREATE, 'directory_created'));
		exit;
	}

	/**
	 * Handle download sample CSV.
	 */
	public static function handle_download_sample_csv(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_download_sample_csv');

		$csv_content = "email,first_name,last_name,role,username,password,coupon_email_append\n";
		$csv_content .= "john.doe@example.com,John,Doe,customer,john.doe,,\n";
		$csv_content .= "jane.smith@example.com,Jane,Smith,subscriber,jane.smith,,SUMMER20\n";
		$csv_content .= "bob.wilson@example.com,Bob,Wilson,customer,bob.wilson,custompass123,WELCOME10\n";
		$csv_content .= "alice.jones@example.com,Alice,Jones,editor,alice.jones,,\n";

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="sample-user-import.csv"');
		header('Pragma: no-cache');
		header('Expires: 0');

		echo $csv_content;
		exit;
	}

	/**
	 * Convert HTML content to Gutenberg blocks (paragraphs and lists, no classic block).
	 *
	 * @param string $html Post content HTML from editor.
	 * @return string Block markup.
	 */
	private static function html_to_paragraph_blocks(string $html): string {
		$html = trim($html);
		if ($html === '') {
			return '';
		}
		// Already block markup — leave as-is.
		if (strpos($html, '<!-- wp:') !== false) {
			return $html;
		}
		// Normalize line endings so DOMDocument parses consistently.
		$html = str_replace([ "\r\n", "\r" ], "\n", $html);

		$out = '';
		$wrapper = '<div id="um-html-root">' . $html . '</div>';
		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML(
			'<?xml encoding="UTF-8">' . $wrapper,
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		libxml_clear_errors();
		$root = $doc->getElementById('um-html-root');
		if (!$root) {
			$root = $doc->getElementsByTagName('body')->item(0);
		}
		if (!$root) {
			return "<!-- wp:paragraph -->\n<p>" . $html . "</p>\n<!-- /wp:paragraph -->\n\n";
		}
		// If the only child is a single div/body-like wrapper (e.g. TinyMCE), parse its inner HTML instead.
		$child_nodes = [];
		foreach ($root->childNodes as $n) {
			$child_nodes[] = $n;
		}
		if (count($child_nodes) === 1 && $child_nodes[0]->nodeType === XML_ELEMENT_NODE) {
			$tag = strtolower($child_nodes[0]->nodeName);
			if ($tag === 'div' || $tag === 'body' || $tag === 'span') {
				$inner = self::um_get_inner_html($child_nodes[0]);
				if (trim($inner) !== '' && (strpos($inner, '<p') !== false || strpos($inner, '<ul') !== false || strpos($inner, '<ol') !== false)) {
					return self::html_to_paragraph_blocks($inner);
				}
			}
		}

		foreach ($root->childNodes as $node) {
			if ($node->nodeType !== XML_ELEMENT_NODE) {
				if ($node->nodeType === XML_TEXT_NODE && trim($node->textContent) !== '') {
					$out .= "<!-- wp:paragraph -->\n<p>" . self::um_escape_block_inner(trim($node->textContent)) . "</p>\n<!-- /wp:paragraph -->\n\n";
				}
				continue;
			}
			$tag = strtolower($node->nodeName);
			if ($tag === 'p') {
				$inner = self::um_get_inner_html($node);
				$inner = trim($inner);
				if ($inner !== '') {
					$out .= "<!-- wp:paragraph -->\n<p>" . $inner . "</p>\n<!-- /wp:paragraph -->\n\n";
				}
			} elseif ($tag === 'ul' || $tag === 'ol') {
				$ordered = ($tag === 'ol');
				$out .= self::um_list_to_blocks($node, $ordered);
			} else {
				$inner = self::um_get_inner_html($node);
				$inner = trim($inner);
				if ($inner !== '') {
					$out .= "<!-- wp:paragraph -->\n<p>" . $inner . "</p>\n<!-- /wp:paragraph -->\n\n";
				}
			}
		}

		return trim($out);
	}

	/**
	 * Get inner HTML of a DOMNode (children only, not the node itself).
	 *
	 * @param DOMNode $node
	 * @return string
	 */
	private static function um_get_inner_html(DOMNode $node): string {
		$html = '';
		foreach ($node->childNodes as $child) {
			$html .= $node->ownerDocument->saveHTML($child);
		}
		return $html;
	}

	/**
	 * Escape content for use inside a block (basic).
	 *
	 * @param string $s
	 * @return string
	 */
	private static function um_escape_block_inner(string $s): string {
		return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Convert a DOMElement ul/ol into Gutenberg list block markup.
	 *
	 * @param DOMElement $listEl
	 * @param bool       $ordered
	 * @return string
	 */
	private static function um_list_to_blocks(DOMElement $listEl, bool $ordered): string {
		$tag = $ordered ? 'ol' : 'ul';
		$attrs = $ordered ? ' {"ordered":true}' : '';
		$out = "<!-- wp:list" . $attrs . " -->\n";
		$out .= "<" . $tag . " class=\"wp-block-list\">\n";
		foreach ($listEl->childNodes as $child) {
			if ($child->nodeType !== XML_ELEMENT_NODE || strtolower($child->nodeName) !== 'li') {
				continue;
			}
			$li_inner = self::um_get_inner_html($child);
			$li_inner = trim($li_inner);
			$out .= "<!-- wp:list-item -->\n<li>" . $li_inner . "</li>\n<!-- /wp:list-item -->\n";
		}
		$out .= "</" . $tag . ">\n";
		$out .= "<!-- /wp:list -->\n\n";
		return $out;
	}

	/**
	 * Register the Page & Post ChatGPT meta box when the setting is enabled.
	 */
	public static function register_page_chatgpt_meta_box(): void {
		$settings = User_Manager_Core::get_settings();
		if (empty($settings['openai_content_generator_enabled'])) {
			return;
		}
		if (empty($settings['openai_page_meta_box'])) {
			return;
		}
		if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
			return;
		}
		foreach (['page', 'post'] as $post_type) {
			add_meta_box(
				'um_page_chatgpt',
				__('Insert Content via ChatGPT', 'user-manager'),
				[__CLASS__, 'render_page_chatgpt_meta_box'],
				$post_type,
				'normal'
			);
		}
	}

	/**
	 * Render the Page ChatGPT meta box (topic, paragraphs, sentences, Generate, preview, Insert).
	 *
	 * @param WP_Post $post Current page post.
	 */
	public static function render_page_chatgpt_meta_box(WP_Post $post): void {
		$generate_nonce = wp_create_nonce('user_manager_page_chatgpt_generate');
		$insert_nonce = wp_create_nonce('user_manager_page_chatgpt_insert');
		?>
		<div class="um-page-chatgpt-meta-box">
			<style>.um-page-chatgpt-idea-tag{display:inline-block;font-size:12px;padding:4px 10px;margin:2px 4px 2px 0;background:#f0f0f1;color:#2271b1;border-radius:3px;text-decoration:none;}.um-page-chatgpt-idea-tag:hover{background:#dcdcde;color:#135e96;}</style>
			<p>
				<label for="um-page-chatgpt-topic" style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e('What should be written about', 'user-manager'); ?></label>
				<input type="text" id="um-page-chatgpt-topic" class="large-text" style="width:100%;" placeholder="<?php esc_attr_e('e.g. benefits of our product', 'user-manager'); ?>" />
			</p>
			<p class="um-page-chatgpt-idea-tags" style="margin-top:6px; margin-bottom:12px;">
				<span style="font-size:12px; color:#50575e; margin-right:8px;"><?php esc_html_e('Pre-written ideas:', 'user-manager'); ?></span>
				<a href="#" class="um-page-chatgpt-idea-tag" data-text="<?php echo esc_attr(__('Add more to this page', 'user-manager')); ?>"><?php esc_html_e('Add more to this page', 'user-manager'); ?></a>
				<a href="#" class="um-page-chatgpt-idea-tag" data-text="<?php echo esc_attr(__('Write a better intro for this page', 'user-manager')); ?>"><?php esc_html_e('Write a better intro for this page', 'user-manager'); ?></a>
				<a href="#" class="um-page-chatgpt-idea-tag" data-text="<?php echo esc_attr(__('Re-write this entire page', 'user-manager')); ?>"><?php esc_html_e('Re-write this entire page', 'user-manager'); ?></a>
			</p>
			<p>
				<label><input type="checkbox" id="um-page-chatgpt-include-existing" value="1" checked="checked" /> <?php esc_html_e('Include raw text from existing post when sending to ChatGPT as additional context about the page', 'user-manager'); ?></label>
			</p>
			<p>
				<label for="um-page-chatgpt-paragraphs" style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Number of paragraphs', 'user-manager'); ?></label>
				<input type="number" id="um-page-chatgpt-paragraphs" min="1" max="20" value="5" style="width:80px;" />
			</p>
			<p>
				<label for="um-page-chatgpt-sentences" style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e('Number of sentences per paragraph', 'user-manager'); ?></label>
				<input type="number" id="um-page-chatgpt-sentences" min="1" max="20" value="5" style="width:80px;" />
			</p>
			<p>
				<button type="button" class="button button-primary" id="um-page-chatgpt-generate"><?php esc_html_e('Generate', 'user-manager'); ?></button>
				<span class="spinner" id="um-page-chatgpt-spinner" style="float:none; margin-left:8px; display:none;"></span>
			</p>
			<div id="um-page-chatgpt-preview" style="margin-top:12px; padding:12px; border:1px solid #c3c4c7; border-radius:4px; background:#f6f7f7; max-height:300px; overflow-y:auto; display:none;"></div>
			<input type="hidden" id="um-page-chatgpt-content" value="" />
			<p style="margin-top:12px;">
				<button type="button" class="button um-page-chatgpt-insert-btn" data-insert-position="bottom" disabled><?php esc_html_e('Insert to bottom of this post', 'user-manager'); ?></button>
				<button type="button" class="button um-page-chatgpt-insert-btn" data-insert-position="top" disabled><?php esc_html_e('Insert to top of this post', 'user-manager'); ?></button>
				<button type="button" class="button um-page-chatgpt-insert-btn" data-insert-position="replace" disabled><?php esc_html_e('Replace this entire post', 'user-manager'); ?></button>
			</p>
		</div>
		<script>
		(function(){
			var postId = <?php echo (int) $post->ID; ?>;
			var ajaxurl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
			var generateNonce = <?php echo wp_json_encode($generate_nonce); ?>;
			var insertNonce = <?php echo wp_json_encode($insert_nonce); ?>;
			var topicEl = document.getElementById('um-page-chatgpt-topic');
			var includeExistingEl = document.getElementById('um-page-chatgpt-include-existing');
			var paragraphsEl = document.getElementById('um-page-chatgpt-paragraphs');
			var sentencesEl = document.getElementById('um-page-chatgpt-sentences');
			var generateBtn = document.getElementById('um-page-chatgpt-generate');
			var spinner = document.getElementById('um-page-chatgpt-spinner');
			var preview = document.getElementById('um-page-chatgpt-preview');
			var contentEl = document.getElementById('um-page-chatgpt-content');
			var insertBtns = document.querySelectorAll('.um-page-chatgpt-insert-btn');
			var box = document.querySelector('.um-page-chatgpt-meta-box');
			if (!generateBtn || !insertBtns.length) return;
			function setInsertButtonsEnabled(enabled) { insertBtns.forEach(function(b){ b.disabled = !enabled; }); }
			if (box) {
				box.addEventListener('click', function(e) {
					var tag = e.target.closest('.um-page-chatgpt-idea-tag');
					if (!tag) return;
					e.preventDefault();
					var text = tag.getAttribute('data-text') || tag.textContent || '';
					if (topicEl) topicEl.value = text;
				});
			}
			generateBtn.addEventListener('click', function(){
				var topic = topicEl ? topicEl.value.trim() : '';
				if (!topic) { alert('<?php echo esc_js(__('Please enter what should be written about.', 'user-manager')); ?>'); return; }
				var numPara = paragraphsEl ? Math.max(1, Math.min(20, parseInt(paragraphsEl.value, 10) || 5)) : 5;
				var numSent = sentencesEl ? Math.max(1, Math.min(20, parseInt(sentencesEl.value, 10) || 5)) : 5;
				generateBtn.disabled = true;
				spinner.style.display = 'inline-block';
				preview.style.display = 'none';
				var form = new FormData();
				form.append('action', 'user_manager_page_chatgpt_generate');
				form.append('nonce', generateNonce);
				form.append('post_id', postId);
				form.append('topic', topic);
				form.append('num_paragraphs', numPara);
				form.append('num_sentences', numSent);
				if (includeExistingEl && includeExistingEl.checked) form.append('include_existing_content', '1');
				fetch(ajaxurl, { method: 'POST', body: form, credentials: 'same-origin' }).then(function(r){ return r.json(); }).then(function(data){
					generateBtn.disabled = false;
					spinner.style.display = 'none';
					if (data.success && data.data && data.data.content) {
						contentEl.value = data.data.content;
						preview.innerHTML = data.data.preview || data.data.content.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
						preview.style.display = 'block';
						setInsertButtonsEnabled(true);
					} else {
						alert(data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Generation failed.', 'user-manager')); ?>');
					}
				}).catch(function(){ generateBtn.disabled = false; spinner.style.display = 'none'; alert('<?php echo esc_js(__('Request failed.', 'user-manager')); ?>'); });
			});
			box.addEventListener('click', function(e) {
				var btn = e.target.closest('.um-page-chatgpt-insert-btn');
				if (!btn || btn.disabled) return;
				var content = contentEl ? contentEl.value : '';
				if (!content) { alert('<?php echo esc_js(__('Generate content first.', 'user-manager')); ?>'); return; }
				var position = btn.getAttribute('data-insert-position') || 'bottom';
				insertBtns.forEach(function(b){ b.disabled = true; });
				var form = new FormData();
				form.append('action', 'user_manager_page_chatgpt_insert');
				form.append('nonce', insertNonce);
				form.append('post_id', postId);
				form.append('content', content);
				form.append('insert_position', position);
				fetch(ajaxurl, { method: 'POST', body: form, credentials: 'same-origin' }).then(function(r){ return r.json(); }).then(function(data){
					setInsertButtonsEnabled(true);
					if (data.success) {
						alert(data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Done. You can save the page to keep changes.', 'user-manager')); ?>');
						if (data.data && data.data.reload) window.location.reload();
					} else {
						alert(data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Insert failed.', 'user-manager')); ?>');
					}
				}).catch(function(){ setInsertButtonsEnabled(true); alert('<?php echo esc_js(__('Request failed.', 'user-manager')); ?>'); });
			});
		})();
		</script>
		<?php
	}

	/**
	 * AJAX: Generate page content via ChatGPT (topic, num paragraphs, num sentences).
	 */
/**
	 * AJAX: Insert generated content at the bottom of the page (block format) and log admin activity.
	 */
	/**
	 * Get all built-in demo email template presets.
	 *
	 * @return array<string,array<string,string>>
	 */
	private static function get_demo_email_template_presets(): array {
		return [
			'tpl_demo_login_info' => [
				'title' => __('Send login information', 'user-manager'),
				'description' => __('Send my account link, username and clear text password', 'user-manager'),
			'subject' => __('Your Login Information', 'user-manager'),
				'heading' => __('Your username and password', 'user-manager'),
			'body' => '<p><strong>Login Page:</strong><br>
<a href="%SITEURL%%LOGINURL%">%SITEURL%%LOGINURL%</a></p>

<p><strong>Email:</strong><br>
%EMAIL%</p>

<p><strong>Password:</strong><br>
%PASSWORD%</p>',
			'bcc' => '',
			],
			'tpl_demo_activate_account' => [
				'title' => __('Activate your new account', 'user-manager'),
				'description' => __('Send new users a link to the website with a temporary password and a link to change their password in their account', 'user-manager'),
				'subject' => __('Activate your new account', 'user-manager'),
				'heading' => __('Set your password', 'user-manager'),
			'body' => '<p><strong>Login Page:</strong><br>
<a href="%SITEURL%%LOGINURL%">%SITEURL%%LOGINURL%</a></p>

<p><strong>Username:</strong><br>
%EMAIL%</p>

<p><strong>Temporary Password:</strong><br>
%PASSWORD%</p>

<p><strong>Set Password Page:</strong><br>
%SITEURL%/my-account/edit-account/</p>',
			'bcc' => '',
			],
			'tpl_demo_new_password' => [
				'title' => __('Send new password', 'user-manager'),
				'description' => __('Sends updated login credentials with clear text password after a password change', 'user-manager'),
			'subject' => __('Your New Password', 'user-manager'),
				'heading' => __('Your password has been updated', 'user-manager'),
				'body' => '<p><strong>Login Page:</strong><br>
<a href="%SITEURL%%LOGINURL%">%SITEURL%%LOGINURL%</a></p>

<p><strong>Email:</strong><br>
%EMAIL%</p>

<p><strong>New Password:</strong><br>
%PASSWORD%</p>',
				'bcc' => '',
			],
			'tpl_demo_password_reset' => [
				'title' => __('Force password reset', 'user-manager'),
				'description' => __('Send a password reset link for users to reset their own password', 'user-manager'),
				'subject' => __('Reset Your Password', 'user-manager'),
				'heading' => __('Reset your password', 'user-manager'),
				'body' => '<p><strong>Email:</strong><br>
%EMAIL%</p>

<p><strong>Reset Password Page:</strong><br>
<a href="%SITEURL%/my-account/lost-password/">Click here to set a new password</a></p>',
			'bcc' => '',
			],
			'tpl_auto_coupon' => [
				'title'       => __('Send automated coupon', 'user-manager'),
				'description' => __('Configured in settings tab to trigger automated discounts & store credits for new users', 'user-manager'),
				'subject'     => __('You have a new coupon!', 'user-manager'),
				'heading'     => __('Login to use your new coupon', 'user-manager'),
				'body'        => '<p><strong>Coupon Code:</strong><br>
%COUPONCODE%</p>

<p><strong>Login Page:</strong><br>
<a href="%SITEURL%%LOGINURL%">%SITEURL%%LOGINURL%</a></p>',
			],
			'tpl_auto_coupon_apology_10' => [
				'title'       => __('Send $10 coupon apology', 'user-manager'),
				'description' => __('Use when sending a one-time $10 apology coupon that includes the %COUPONCODE% placeholder.', 'user-manager'),
				'subject'     => __('You have a new $10 gift code!', 'user-manager'),
				'heading'     => __('We are so sorry!', 'user-manager'),
				'body'        => '<p>Here is a $10 one-time use gift code to go towards your next purchase...</p>

<p><strong>Coupon Code:</strong><br>
%COUPONCODE%</p>',
			],
			'tpl_auto_coupon_remaining_balance' => [
				'title'       => __('Send automated remaining balance coupon', 'user-manager'),
				'description' => __('Configured in Settings to trigger automated remaining balance coupon for new users. Supports %COUPONCODEVALUE% and %COUPONCODE%.', 'user-manager'),
				'subject'     => __('You have a remaining balance', 'user-manager'),
				'heading'     => __('You have a remaining balance', 'user-manager'),
				'body'        => '<p>Remaining Balance:<br>
%COUPONCODEVALUE%</p>

<p>Coupon Code:<br>
%COUPONCODE%</p>',
			],
		];
	}

	/**
	 * Import demo templates (core logic, can be called directly).
	 */
	public static function import_demo_templates(): void {
		// Start with existing templates map (unsorted raw)
		$existing = get_option(User_Manager_Core::EMAIL_TEMPLATES_KEY, []);
		if (!is_array($existing)) {
			$existing = [];
		}
		
		$all_presets = self::get_demo_email_template_presets();
		$desired_ids = ['tpl_demo_login_info', 'tpl_demo_activate_account', 'tpl_demo_new_password', 'tpl_demo_password_reset'];
		$desired = [];
		foreach ($desired_ids as $desired_id) {
			if (isset($all_presets[$desired_id])) {
				$desired[$desired_id] = $all_presets[$desired_id];
			}
		}
		
		// Merge/overwrite existing with desired content
		foreach ($desired as $key => $data) {
			$existing[$key] = array_merge($existing[$key] ?? [], $data);
		}
		
		// Build a new ordered list: desired keys first (1..4), then any remaining existing templates
		// Collect remaining keys not in desired, maintaining their current relative order by 'order' or insertion
		$remaining = [];
		// Ensure each has an order to allow stable sort
		$orderSeed = 1000;
		foreach ($existing as $id => &$tpl) {
			if (!isset($tpl['order']) || !is_numeric($tpl['order'])) {
				$tpl['order'] = $orderSeed++;
			}
		}
		unset($tpl);
		// Sort current existing by order
		$sortedExisting = $existing;
		uasort($sortedExisting, function($a, $b) {
			$oa = isset($a['order']) ? (int) $a['order'] : 0;
			$ob = isset($b['order']) ? (int) $b['order'] : 0;
			if ($oa === $ob) return 0;
			return ($oa < $ob) ? -1 : 1;
		});
		foreach ($sortedExisting as $id => $tpl) {
			if (!isset($desired[$id])) {
				$remaining[$id] = $tpl;
			}
		}
		
		// Reassign sequential order: desired first
		$new = [];
		$ord = 1;
		foreach (array_keys($desired) as $id) {
			$new[$id] = $existing[$id];
			$new[$id]['order'] = $ord++;
		}
		foreach ($remaining as $id => $tpl) {
			$new[$id] = $tpl;
			$new[$id]['order'] = $ord++;
		}
		
		update_option(User_Manager_Core::EMAIL_TEMPLATES_KEY, $new);
	}

	/**
	 * Recreate a single built-in demo email template by ID.
	 */
	public static function recreate_demo_email_template(string $template_id): bool {
		$template_id = sanitize_key($template_id);
		$presets = self::get_demo_email_template_presets();
		if ($template_id === '' || !isset($presets[$template_id])) {
			return false;
		}

		$templates = User_Manager_Core::get_email_templates();
		$existing_order = isset($templates[$template_id]['order']) ? absint($templates[$template_id]['order']) : 0;
		$templates[$template_id] = array_merge($templates[$template_id] ?? [], $presets[$template_id]);

		if ($existing_order > 0) {
			$templates[$template_id]['order'] = $existing_order;
		} else {
			$max_order = 0;
			foreach ($templates as $id => $tpl) {
				if ($id === $template_id) {
					continue;
				}
				$max_order = max($max_order, isset($tpl['order']) ? (int) $tpl['order'] : 0);
			}
			$templates[$template_id]['order'] = $max_order + 1;
		}

		update_option(User_Manager_Core::EMAIL_TEMPLATES_KEY, $templates);
		return true;
	}

	/**
	 * Handle import demo templates.
	 */
	public static function handle_import_demo_templates(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_import_demo_templates');

		self::import_demo_templates();
		self::import_coupon_template();

		wp_safe_redirect(self::get_email_template_import_redirect_url('demo_templates_imported'));
		exit;
	}

	/**
	 * Handle recreating a single built-in demo email template.
	 */
	public static function handle_recreate_demo_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_recreate_demo_template');

		$template_id = isset($_REQUEST['template_id']) ? sanitize_key(wp_unslash($_REQUEST['template_id'])) : '';
		$presets = self::get_demo_email_template_presets();
		$template_title = isset($presets[$template_id]['title']) ? sanitize_text_field((string) $presets[$template_id]['title']) : '';
		$recreated = self::recreate_demo_email_template($template_id);
		$message = $recreated ? 'demo_template_recreated' : 'error';
		$redirect = self::get_email_template_import_redirect_url($message);
		if ($template_id !== '') {
			$redirect = add_query_arg('template_id', $template_id, $redirect);
		}
		if ($template_title !== '') {
			$redirect = add_query_arg('template_title', $template_title, $redirect);
		}

		wp_safe_redirect($redirect);
		exit;
	}

	/**
	 * Build redirect URL after email-template import actions.
	 */
	private static function get_email_template_import_redirect_url(string $message): string {
		$context = isset($_REQUEST['templates_context']) ? sanitize_key(wp_unslash($_REQUEST['templates_context'])) : '';
		if ($context === 'addon-send-email-users') {
			return add_query_arg(
				[
					'addon_section'     => 'send-email-users',
					'templates_context' => $context,
					'um_msg'            => $message,
				],
				User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS)
			);
		}

		return User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_TOOLS, $message);
	}

	/**
	 * Import demo SMS text templates (core logic, can be called directly).
	 */
	public static function import_demo_sms_text_templates(): void {
		$existing = get_option(User_Manager_Core::SMS_TEXT_TEMPLATES_KEY, []);
		if (!is_array($existing)) {
			$existing = [];
		}

		$desired = [
			'sms_tpl_demo_login_info' => [
				'title' => __('Send login information', 'user-manager'),
				'description' => __('Send website login link plus username and clear text password by SMS', 'user-manager'),
				'body' => "Login: %SITEURL%%LOGINURL%\nEmail: %EMAIL%\nPassword: %PASSWORD%",
			],
			'sms_tpl_demo_activate_account' => [
				'title' => __('Activate your new account', 'user-manager'),
				'description' => __('Send temporary login details and a direct set-password path by SMS', 'user-manager'),
				'body' => "Welcome! Login: %SITEURL%%LOGINURL%\nUsername: %EMAIL%\nTemp password: %PASSWORD%\nSet password: %SITEURL%/my-account/edit-account/",
			],
			'sms_tpl_demo_new_password' => [
				'title' => __('Send new password', 'user-manager'),
				'description' => __('Send updated login credentials after password changes by SMS', 'user-manager'),
				'body' => "Your password was updated.\nLogin: %SITEURL%%LOGINURL%\nEmail: %EMAIL%\nNew password: %PASSWORD%",
			],
			'sms_tpl_demo_password_reset' => [
				'title' => __('Force password reset', 'user-manager'),
				'description' => __('Send a password reset link by SMS', 'user-manager'),
				'body' => "Reset your password here: %PASSWORDRESETURL%\nAccount email: %EMAIL%",
			],
			'sms_tpl_auto_coupon' => [
				'title' => __('Send automated coupon', 'user-manager'),
				'description' => __('Configured in Settings to trigger automated discounts & store credits for new users. Supports %COUPONCODE%.', 'user-manager'),
				'body' => "You have a new coupon.\nCoupon code: %COUPONCODE%\nLogin here: %SITEURL%%LOGINURL%",
			],
			'sms_tpl_auto_coupon_apology_10' => [
				'title' => __('Send $10 coupon apology', 'user-manager'),
				'description' => __('Use when sending a one-time $10 apology coupon that includes the %COUPONCODE% placeholder.', 'user-manager'),
				'body' => "We're so sorry! Here is your one-time $10 gift code: %COUPONCODE%",
			],
		];

		foreach ($desired as $key => $data) {
			$existing[$key] = array_merge($existing[$key] ?? [], $data);
		}

		$remaining = [];
		$order_seed = 1000;
		foreach ($existing as $id => &$tpl) {
			if (!isset($tpl['order']) || !is_numeric($tpl['order'])) {
				$tpl['order'] = $order_seed++;
			}
		}
		unset($tpl);

		$sorted_existing = $existing;
		uasort($sorted_existing, static function ($a, $b) {
			$oa = isset($a['order']) ? (int) $a['order'] : 0;
			$ob = isset($b['order']) ? (int) $b['order'] : 0;
			if ($oa === $ob) {
				return 0;
			}
			return $oa < $ob ? -1 : 1;
		});
		foreach ($sorted_existing as $id => $tpl) {
			if (!isset($desired[$id])) {
				$remaining[$id] = $tpl;
			}
		}

		$new = [];
		$ord = 1;
		foreach (array_keys($desired) as $id) {
			$new[$id] = $existing[$id];
			$new[$id]['order'] = $ord++;
		}
		foreach ($remaining as $id => $tpl) {
			$new[$id] = $tpl;
			$new[$id]['order'] = $ord++;
		}

		update_option(User_Manager_Core::SMS_TEXT_TEMPLATES_KEY, $new);
	}

	/**
	 * Handle import demo SMS text templates.
	 */
	public static function handle_import_demo_sms_text_templates(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_import_demo_sms_text_templates');

		self::import_demo_sms_text_templates();

		wp_safe_redirect(self::get_sms_template_import_redirect_url('demo_sms_templates_imported'));
		exit;
	}

	/**
	 * Build redirect URL after SMS-template import actions.
	 */
	private static function get_sms_template_import_redirect_url(string $message): string {
		$context = isset($_REQUEST['templates_context']) ? sanitize_key(wp_unslash($_REQUEST['templates_context'])) : '';
		if ($context === 'addon-send-sms-text') {
			return add_query_arg(
				[
					'addon_section'     => 'send-sms-text',
					'templates_context' => $context,
					'um_msg'            => $message,
				],
				User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS)
			);
		}

		return User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_TOOLS, $message);
	}
	
	/**
	 * Import automated coupon email templates (core logic, can be called directly).
	 */
	public static function import_coupon_template(): void {
		$templates = User_Manager_Core::get_email_templates();
		$presets = self::get_demo_email_template_presets();

		// Automated coupon template.
		$auto_id = 'tpl_auto_coupon';
		if (isset($presets[$auto_id])) {
			$templates[$auto_id] = array_merge($templates[$auto_id] ?? [], $presets[$auto_id]);
		}

		// $10 apology coupon template.
		$apology_id = 'tpl_auto_coupon_apology_10';
		if (isset($presets[$apology_id])) {
			$templates[$apology_id] = array_merge($templates[$apology_id] ?? [], $presets[$apology_id]);
		}

		// Automated remaining balance coupon template.
		$remaining_balance_id = 'tpl_auto_coupon_remaining_balance';
		if (isset($presets[$remaining_balance_id])) {
			$templates[$remaining_balance_id] = array_merge($templates[$remaining_balance_id] ?? [], $presets[$remaining_balance_id]);
		}

		// Assign order after existing ones: auto coupon, apology coupon, then remaining balance coupon.
		$max_order = 0;
		foreach ($templates as $tpl) {
			$max_order = max($max_order, isset($tpl['order']) ? (int) $tpl['order'] : 0);
		}
		$templates[$auto_id]['order']              = $max_order + 1;
		$templates[$apology_id]['order']           = $max_order + 2;
		$templates[$remaining_balance_id]['order'] = $max_order + 3;

		update_option(User_Manager_Core::EMAIL_TEMPLATES_KEY, $templates);
	}

	/**
	 * Handle import of the automated coupon email template.
	 */
	public static function handle_import_coupon_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}
		check_admin_referer('user_manager_import_coupon_template');

		self::import_coupon_template();

		wp_safe_redirect(self::get_email_template_import_redirect_url('demo_templates_imported'));
		exit;
	}

	/**
	 * Handle creation of a basic coupon template for Bulk Coupons.
	 */
	public static function handle_create_basic_coupon_template(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_create_basic_coupon_template', 'user_manager_create_basic_coupon_template_nonce');
		$redirect_tab = User_Manager_Core::TAB_ADDONS;
		$settings = User_Manager_Core::get_settings();
		if (empty($settings['bulk_coupons_enabled'])) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'bulk_coupons_disabled'));
			exit;
		}

		if (!class_exists('WC_Coupon')) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'error'));
			exit;
		}

		$code = 'basic_coupon_template_10_off_one_time_use_no_expiration';

		// If coupon already exists, just redirect back so user can select it.
		$existing_id = function_exists('wc_get_coupon_id_by_code') ? wc_get_coupon_id_by_code($code) : 0;

		if ($existing_id) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'success'));
			exit;
		}

		$coupon_id = wp_insert_post([
			'post_title'   => $code,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id() ?: 1,
			'post_type'    => 'shop_coupon',
		]);

		if (is_wp_error($coupon_id) || !$coupon_id) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'error'));
			exit;
		}

		$coupon = new WC_Coupon($coupon_id);

		// Configure as fixed cart coupon worth 10, one-time use, no expiration.
		$coupon->set_discount_type('fixed_cart');

		if (function_exists('wc_format_decimal')) {
			$coupon->set_amount(wc_format_decimal(10));
		} else {
			$coupon->set_amount('10');
		}

		$coupon->set_usage_limit(1);
		$coupon->set_date_expires(null); // No expiration.

		$coupon->save();

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message($redirect_tab, 'success'));
		exit;
	}

	/**
	 * Handle clear activity log.
	 */
	public static function handle_clear_activity_log(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_clear_activity_log');

		global $wpdb;
		$table = $wpdb->prefix . 'um_admin_activity';
		$wpdb->query("TRUNCATE TABLE {$table}");
		delete_option(User_Manager_Core::ACTIVITY_LOG_KEY);

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_TOOLS, 'activity_log_cleared'));
		exit;
	}

	/**
	 * Handle clear user activity log.
	 */
	public static function handle_clear_user_activity_log(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_clear_user_activity_log');

		global $wpdb;
		$table = $wpdb->prefix . 'um_user_activity';
		$wpdb->query("TRUNCATE TABLE {$table}");

		wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_TOOLS, 'user_activity_log_cleared'));
		exit;
	}

	/**
	 * Handle migration of store_credit coupons to fixed_cart coupons.
	 */
	public static function handle_migrate_store_credit_coupons(): void {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to access this page.', 'user-manager'));
		}

		check_admin_referer('user_manager_migrate_store_credit_coupons');

		if (!class_exists('WooCommerce')) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_ADDONS, 'migration_failed_no_wc'));
			exit;
		}

		$coupon_ids = isset($_POST['coupon_ids']) && is_array($_POST['coupon_ids']) 
			? array_map('absint', $_POST['coupon_ids']) 
			: [];

		if (empty($coupon_ids)) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_ADDONS, 'migration_no_selection'));
			exit;
		}

		$migrated_count = 0;
		$failed_count = 0;

		foreach ($coupon_ids as $coupon_id) {
			$coupon = new WC_Coupon($coupon_id);
			
			if (!$coupon || !$coupon->get_id()) {
				$failed_count++;
				continue;
			}

			// Check if already migrated
			$migrated_flag = get_post_meta($coupon_id, '_um_store_credit_migrated', true);
			
			// If already migrated, only allow re-migration if coupon type is not fixed_cart
			if (!empty($migrated_flag)) {
				if ($coupon->get_discount_type() === 'fixed_cart') {
					// Already properly migrated, skip
					continue;
				}
				// Has migrated flag but not fixed_cart - allow re-migration
			} else {
				// Not migrated yet - verify it's a store_credit coupon
				if ($coupon->get_discount_type() !== 'store_credit') {
					$failed_count++;
					continue;
				}
			}

			// Preserve email restrictions before migration
			$email_restrictions = $coupon->get_email_restrictions();
			$customer_email_meta = get_post_meta($coupon_id, 'customer_email', true);
			$um_user_email = get_post_meta($coupon_id, '_um_user_coupon_user_email', true);
			$um_user_id = get_post_meta($coupon_id, '_um_user_coupon_user_id', true);

			// Convert to fixed_cart
			$coupon->set_discount_type('fixed_cart');
			// Set usage limit to 1 (single-use)
			$coupon->set_usage_limit(1);
			// Reset usage count to 0
			$coupon->set_usage_count(0);
			
			// Preserve email restrictions
			if (!empty($email_restrictions) && is_array($email_restrictions)) {
				$coupon->set_email_restrictions($email_restrictions);
			}
			
			$coupon->save();

			// Preserve email meta fields
			if (!empty($customer_email_meta)) {
				update_post_meta($coupon_id, 'customer_email', $customer_email_meta);
			}
			if (!empty($um_user_email)) {
				update_post_meta($coupon_id, '_um_user_coupon_user_email', $um_user_email);
			}
			if (!empty($um_user_id)) {
				update_post_meta($coupon_id, '_um_user_coupon_user_id', $um_user_id);
			}

			// Mark as migrated
			update_post_meta($coupon_id, '_um_store_credit_migrated', true);
			update_post_meta($coupon_id, '_um_store_credit_migrated_date', current_time('mysql'));

			$migrated_count++;

			// Log activity
			User_Manager_Core::add_activity_log('store_credit_migrated', get_current_user_id(), 'Coupons', [
				'coupon_id' => $coupon_id,
				'coupon_code' => $coupon->get_code(),
			]);
		}

		// Preserve sort parameters
		$sort = isset($_POST['sort']) ? sanitize_key($_POST['sort']) : 'id';
		$order = isset($_POST['order']) && strtolower($_POST['order']) === 'desc' ? 'desc' : 'asc';
		$extra_params = ['count' => $migrated_count];
		if ($sort !== 'id' || $order !== 'asc') {
			$extra_params['sort'] = $sort;
			$extra_params['order'] = $order;
		}

		if ($migrated_count > 0) {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(
				User_Manager_Core::TAB_ADDONS,
				'migration_success',
				$extra_params
			));
		} else {
			wp_safe_redirect(User_Manager_Core::get_redirect_with_message(User_Manager_Core::TAB_ADDONS, 'migration_failed'));
		}
		exit;
	}

	/**
	 * Normalize and sanitize a comma-separated username list.
	 */
	private static function sanitize_username_csv($raw): string {
		$raw = is_string($raw) ? $raw : '';
		if ($raw === '') {
			return '';
		}

		$parts = preg_split('/[\s,]+/', $raw);
		if (!is_array($parts)) {
			return '';
		}

		$usernames = [];
		foreach ($parts as $part) {
			$part = trim((string) $part);
			if ($part === '') {
				continue;
			}

			$username = sanitize_user($part, false);
			if ($username === '') {
				continue;
			}

			$usernames[] = strtolower($username);
		}

		$usernames = array_values(array_unique($usernames));
		return implode(', ', $usernames);
	}

	/**
	 * Sanitize posted role keys and keep only real WP roles.
	 *
	 * @param mixed $raw Raw posted roles.
	 * @return array<int,string>
	 */
	private static function sanitize_role_keys_array($raw): array {
		$parts = is_array($raw) ? $raw : [];
		$roles = [];
		foreach ($parts as $part) {
			$key = sanitize_key((string) $part);
			if ($key === '') {
				continue;
			}
			$roles[] = $key;
		}
		$roles = array_values(array_unique($roles));

		if (function_exists('wp_roles')) {
			$wp_roles = wp_roles();
			if ($wp_roles && isset($wp_roles->roles) && is_array($wp_roles->roles)) {
				$roles = array_values(array_intersect($roles, array_keys($wp_roles->roles)));
			}
		}

		return $roles;
	}

	/**
	 * Sanitize posted My Account order status title overrides.
	 *
	 * @param mixed $raw Raw posted value.
	 * @return array<string,string>
	 */
	private static function sanitize_my_account_order_status_title_overrides($raw): array {
		if (!is_array($raw)) {
			return [];
		}

		$clean = [];
		foreach ($raw as $status_key => $label) {
			$normalized_key = self::normalize_wc_order_status_key((string) $status_key);
			if ($normalized_key === '') {
				continue;
			}
			$label = sanitize_text_field((string) $label);
			if ($label === '') {
				continue;
			}
			$clean[$normalized_key] = $label;
		}

		return $clean;
	}

	/**
	 * Normalize WooCommerce status keys like pending / wc_pending / wc-pending.
	 */
	private static function normalize_wc_order_status_key(string $raw): string {
		$raw = trim(strtolower($raw));
		if ($raw === '') {
			return '';
		}
		$raw = str_replace('_', '-', $raw);
		$raw = sanitize_key($raw);
		if ($raw === '') {
			return '';
		}
		if (strpos($raw, 'wc-') !== 0) {
			$raw = 'wc-' . ltrim($raw, '-');
		}
		return sanitize_key($raw);
	}

	/**
	 * Sanitize plugin title override text.
	 */
	private static function sanitize_plugin_title_override($raw): string {
		$title = sanitize_text_field((string) $raw);
		return trim($title);
	}

	/**
	 * Normalize and sanitize Block Pages by URL String rule sets.
	 *
	 * @param mixed $raw_sets Raw posted value.
	 * @return array<int,array<string,mixed>>
	 */
	private static function sanitize_block_pages_by_url_string_rules($raw_sets): array {
		if (!is_array($raw_sets)) {
			return [];
		}

		$available_roles = [];
		if (function_exists('wp_roles')) {
			$wp_roles = wp_roles();
			if ($wp_roles && isset($wp_roles->roles) && is_array($wp_roles->roles)) {
				$available_roles = array_keys($wp_roles->roles);
			}
		}

		$sanitized_sets = [];
		foreach ($raw_sets as $raw_set) {
			if (!is_array($raw_set)) {
				continue;
			}

			$match_urls = isset($raw_set['match_urls']) ? sanitize_textarea_field((string) $raw_set['match_urls']) : '';
			$exception_urls = isset($raw_set['exception_urls']) ? sanitize_textarea_field((string) $raw_set['exception_urls']) : '';
			if (trim($match_urls) === '') {
				continue;
			}

			$usernames = isset($raw_set['usernames']) ? self::sanitize_username_csv((string) $raw_set['usernames']) : '';
			$roles = isset($raw_set['roles']) ? self::sanitize_role_keys_array($raw_set['roles']) : [];
			if (!empty($available_roles)) {
				$roles = array_values(array_intersect($roles, $available_roles));
			}

			$redirect_url = isset($raw_set['redirect_url']) ? esc_url_raw((string) $raw_set['redirect_url']) : '';
			$background_color = isset($raw_set['background_color']) ? sanitize_hex_color((string) $raw_set['background_color']) : '';
			$text_color = isset($raw_set['text_color']) ? sanitize_hex_color((string) $raw_set['text_color']) : '';

			$sanitized_sets[] = [
				'match_urls' => $match_urls,
				'exception_urls' => $exception_urls,
				'usernames' => $usernames,
				'roles' => $roles,
				'background_color' => $background_color ? $background_color : '#000000',
				'background_url' => isset($raw_set['background_url']) ? esc_url_raw((string) $raw_set['background_url']) : '',
				'logo_url' => isset($raw_set['logo_url']) ? esc_url_raw((string) $raw_set['logo_url']) : '',
				'logo_width' => isset($raw_set['logo_width']) ? sanitize_text_field((string) $raw_set['logo_width']) : '',
				'message' => isset($raw_set['message']) ? sanitize_text_field((string) $raw_set['message']) : '',
				'text_color' => $text_color ? $text_color : '',
				'redirect_url' => $redirect_url,
			];
		}

		return $sanitized_sets;
	}
}

