<?php
/**
 * User Manager Core Class
 */

if (!defined('ABSPATH')) {
	exit;
}


require_once __DIR__ . '/core/trait-user-manager-core-activity-log.php';
require_once __DIR__ . '/core/trait-user-manager-core-add-to-cart-min-max-quantities.php';
require_once __DIR__ . '/core/trait-user-manager-core-add-to-cart-variation-table.php';
require_once __DIR__ . '/core/trait-user-manager-core-cart-price-per-piece.php';
require_once __DIR__ . '/core/trait-user-manager-core-cart-total-items.php';
require_once __DIR__ . '/core/trait-user-manager-core-emali-log.php';
require_once __DIR__ . '/core/trait-user-manager-core-fatal-error-debugger.php';
require_once __DIR__ . '/core/trait-user-manager-core-invoice-approval.php';
require_once __DIR__ . '/core/trait-user-manager-core-media-library-tags.php';
require_once __DIR__ . '/core/trait-user-manager-core-media-library-tags-video-library.php';
require_once __DIR__ . '/core/trait-user-manager-core-my-account-menu-tiles.php';
require_once __DIR__ . '/core/trait-user-manager-core-order-received-page-customizer.php';
require_once __DIR__ . '/core/trait-user-manager-core-page-blocks.php';
require_once __DIR__ . '/core/trait-user-manager-core-plugin-tags-notes.php';
require_once __DIR__ . '/core/trait-user-manager-core-product-notification.php';
require_once __DIR__ . '/core/trait-user-manager-core-restricted-access.php';
require_once __DIR__ . '/core/trait-user-manager-core-security-hardening.php';
require_once __DIR__ . '/core/trait-user-manager-core-seo-basics.php';
require_once __DIR__ . '/core/trait-user-manager-core-webhook-urls.php';
final class User_Manager_Core {
	use User_Manager_Core_Activity_Log_Trait;
	use User_Manager_Core_Add_To_Cart_Min_Max_Quantities_Trait;
	use User_Manager_Core_Add_To_Cart_Variation_Table_Trait;
	use User_Manager_Core_Cart_Price_Per_Piece_Trait;
	use User_Manager_Core_Cart_Total_Items_Trait;
	use User_Manager_Core_Emali_Log_Trait;
	use User_Manager_Core_Fatal_Error_Debugger_Trait;
	use User_Manager_Core_Invoice_Approval_Trait;
	use User_Manager_Core_Media_Library_Tags_Trait;
	use User_Manager_Core_Media_Library_Tags_Video_Library_Trait;
	use User_Manager_Core_My_Account_Menu_Tiles_Trait;
	use User_Manager_Core_Order_Received_Page_Customizer_Trait;
	use User_Manager_Core_Page_Blocks_Trait;
	use User_Manager_Core_Plugin_Tags_Notes_Trait;
	use User_Manager_Core_Product_Notification_Trait;
	use User_Manager_Core_Restricted_Access_Trait;
	use User_Manager_Core_Security_Hardening_Trait;
	use User_Manager_Core_SEO_Basics_Trait;
	use User_Manager_Core_Webhook_URLs_Trait;
	const OPTION_KEY = 'user_manager_settings';
	const ACTIVITY_LOG_KEY = 'user_manager_activity_log';
	const EMAIL_TEMPLATES_KEY = 'user_manager_email_templates';
	const SMS_TEXT_TEMPLATES_KEY = 'user_manager_sms_text_templates';
	const IMPORTED_FILES_KEY = 'user_manager_imported_files';
	const SETTINGS_PAGE_SLUG = 'user-manager';
	const VERSION = '2.5.129';
	const URL_PARAM_DISABLE_ALL_ADDONS = 'um_disable_all_addons';
	const URL_PARAM_DISABLE_ADDONS = 'um_disable_addons';
	const USER_DEACTIVATED_META_KEY = 'um_user_deactivated';
	const USER_DEACTIVATED_AT_META_KEY = 'um_user_deactivated_at';
	const USER_DEACTIVATED_BY_META_KEY = 'um_user_deactivated_by';
	const USER_DEACTIVATED_ORIGINAL_LOGIN_META_KEY = 'um_user_deactivated_original_login';
	const USER_DEACTIVATED_ORIGINAL_EMAIL_META_KEY = 'um_user_deactivated_original_email';
	const USER_DEACTIVATION_HISTORY_KEY = 'user_manager_deactivated_users_history';

	/**
	 * Stores remainder debug messages keyed by order ID.
	 *
	 * @var array<int,array<int,string>>
	 */
	private static array $coupon_remainder_debug_messages = [];

	/**
	 * Prevent duplicate front-end non-production notice rendering.
	 */
	private static bool $staging_dev_notice_rendered = false;

	/**
	 * Per-request debug trace rows for bulk add to cart uploads.
	 *
	 * @var array<int,array{line:int,status:string,message:string}>
	 */
	private static array $bulk_add_to_cart_debug_trace = [];

	/**
	 * Per-request media upload info for the latest bulk add CSV upload.
	 *
	 * @var array<string,mixed>
	 */
	private static array $bulk_add_to_cart_media_upload_info = [];

	/**
	 * Cached runtime-disabled add-on slugs parsed from URL.
	 *
	 * @var array<int,string>|null
	 */
	private static ?array $runtime_disabled_addon_slugs = null;

	/**
	 * Cached runtime "disable all add-ons" URL flag.
	 */
	private static ?bool $runtime_disable_all_addons = null;


	// Tab constants
	const TAB_LOGIN_TOOLS     = 'login-tools';
	const TAB_CREATE_USER     = 'create-user';
	const TAB_RESET_PASSWORD  = 'reset-password';
	const TAB_REMOVE_USER     = 'remove-user';
	const TAB_DEACTIVATE_USER = 'deactivate-user';
	const TAB_ROLE_SWITCHING  = 'role-switching';
	const TAB_LOGIN_AS        = 'login-as';
	const TAB_BULK_CREATE     = 'bulk-create';
	const TAB_BULK_COUPONS    = 'bulk-coupons';
	const TAB_EMAIL_USERS     = 'email-users';
	const TAB_LOGIN_HISTORY   = 'login-history';
	const TAB_ACTIVITY_LOG    = 'activity-log';
	const TAB_EMAIL_TEMPLATES = 'email-templates';
	const TAB_COUPONS         = 'coupons';
	const TAB_TOOLS           = 'tools';
	const TAB_SETTINGS        = 'settings';
	const TAB_ADDONS         = 'addons';
	const TAB_BLOCKS         = 'blocks';
	const TAB_REPORTS         = 'reports';
	const TAB_DOCUMENTATION   = 'documentation';
	const TAB_VERSIONS        = 'versions';

	/**
	 * Boot plugin hooks.
	 */
	public static function init(): void {
		add_action('init', [__CLASS__, 'load_textdomain'], 1);
		add_action('admin_menu', [__CLASS__, 'register_settings_page']);
		$main_plugin_basename = self::get_main_plugin_basename();
		add_filter('plugin_action_links_' . $main_plugin_basename, [__CLASS__, 'add_plugin_action_links']);
		add_filter('plugin_row_meta', [__CLASS__, 'add_plugin_row_meta_links'], 10, 2);
		add_action('admin_notices', [__CLASS__, 'maybe_render_user_new_notice']);
		add_action('admin_notices', [__CLASS__, 'render_custom_admin_notifications'], 5);
		add_action('admin_init', [__CLASS__, 'register_settings']);
		add_action('admin_init', [__CLASS__, 'maybe_handle_csv_export']);
		add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
		add_action('admin_head', [__CLASS__, 'inject_wp_admin_css']);
		add_action('init', [__CLASS__, 'maybe_create_login_history_table']);
		add_action('init', [__CLASS__, 'maybe_create_user_activity_table']);
		add_action('init', [__CLASS__, 'maybe_create_admin_activity_table']);
		add_action('init', [__CLASS__, 'maybe_create_restricted_access_history_table']);
		// On multisite, the MU logger handles login writes to ensure reliability on central login hosts.
		if (!is_multisite()) {
			add_action('wp_login', [__CLASS__, 'handle_wp_login'], 10, 2);
		}
		
		// Admin activity logging: wp-admin logins and post type changes
		add_action('admin_init', [__CLASS__, 'maybe_log_admin_login'], 1);
		add_filter('wp_insert_post_data', [__CLASS__, 'capture_post_data_before_save'], 10, 2);
		add_action('save_post', [__CLASS__, 'log_post_save'], 10, 3);
		add_action('transition_post_status', [__CLASS__, 'log_post_status_change'], 10, 3);
		
		// Plugin activation/deactivation logging
		add_action('activated_plugin', [__CLASS__, 'log_plugin_activation'], 10, 2);
		add_action('deactivated_plugin', [__CLASS__, 'log_plugin_deactivation'], 10, 2);
		
		// Profile/Edit User: notice at top of page (admin_notices) with Open User Manager + Reset Password buttons
		add_action('admin_notices', [__CLASS__, 'render_profile_user_manager_notice']);
		// Admin: show login history on user edit/profile screens for debugging
		add_action('show_user_profile', [__CLASS__, 'render_user_login_history_profile']);
		add_action('edit_user_profile', [__CLASS__, 'render_user_login_history_profile']);
		
		// Custom Email Lists on user edit/profile screens
		add_action('show_user_profile', [__CLASS__, 'render_user_email_lists_profile']);
		add_action('edit_user_profile', [__CLASS__, 'render_user_email_lists_profile']);
		add_action('personal_options_update', [__CLASS__, 'save_user_email_lists_profile']);
		add_action('edit_user_profile_update', [__CLASS__, 'save_user_email_lists_profile']);
		
		// User Activity: My Account page views (all endpoints) and password changes
		add_action('template_redirect', [__CLASS__, 'maybe_log_myaccount_page']);
		add_action('woocommerce_save_account_details', [__CLASS__, 'log_password_change_on_save'], 10, 1);
		add_action('after_password_reset', [__CLASS__, 'log_password_change_after_reset'], 10, 2);
		add_filter('wp_authenticate_user', [__CLASS__, 'block_deactivated_user_authentication'], 20, 2);
		add_filter('allow_password_reset', [__CLASS__, 'maybe_block_deactivated_user_password_reset'], 10, 2);
		
		// Coupon Email Converter meta box toggle + other settings-based behavior.
		$settings = self::get_settings();
		self::maybe_boot_add_to_cart_min_max_quantities($settings);
		self::maybe_boot_cart_price_per_piece($settings);
		self::maybe_boot_cart_total_items($settings);
		self::maybe_boot_emali_log($settings);
		self::maybe_boot_invoice_approval($settings);
		if (!self::is_disable_blocks_requested()) {
			self::maybe_boot_media_library_tags($settings);
		}
		self::maybe_boot_my_account_menu_tiles($settings);
		self::maybe_boot_order_received_page_customizer($settings);
		self::maybe_boot_product_notification($settings);
		if (!self::is_disable_blocks_requested()) {
			self::maybe_boot_page_blocks($settings);
		}
		self::maybe_boot_plugin_tags_notes($settings);
		self::maybe_boot_restricted_access($settings);
		self::maybe_apply_security_hardening($settings);
		self::maybe_boot_seo_basics($settings);
		self::maybe_boot_webhook_urls($settings);
		add_action('user_manager_emali_log_daily_cleanup', [__CLASS__, 'run_emali_log_daily_cleanup']);
		if (!empty($settings['coupon_email_converter'])) {
			add_action('add_meta_boxes', [__CLASS__, 'add_coupon_email_converter_meta_box']);
		}
		if (!empty($settings['display_post_meta_meta_box'])) {
			add_action('add_meta_boxes', [__CLASS__, 'add_all_post_meta_meta_box'], 10, 2);
			add_action('save_post', [__CLASS__, 'save_all_post_meta_meta_box'], 10, 2);
		}

		// User Activity: log WooCommerce orders when available.
		// Keep this scoped to the New User Coupons add-on context.
		$new_user_coupons_enabled = self::is_new_user_coupons_enabled($settings);
		if (class_exists('WooCommerce') && $new_user_coupons_enabled) {
			// Fire after order is marked processing or completed.
			add_action('woocommerce_order_status_processing', [__CLASS__, 'log_user_order_activity'], 20, 1);
			add_action('woocommerce_order_status_completed', [__CLASS__, 'log_user_order_activity'], 20, 1);
		}
		$checkout_ship_to_enabled = !empty($settings['checkout_ship_to_predefined_enabled']) && !self::is_addon_temporarily_disabled('checkout-pre-defined-addresses');
		$checkout_ship_to_debug_enabled = !empty($settings['checkout_ship_to_show_debug']) && !self::is_addon_temporarily_disabled('checkout-pre-defined-addresses');
		if ($checkout_ship_to_enabled || $checkout_ship_to_debug_enabled) {
			// Checkout: Ship To Pre-Defined Addresses — init after WooCommerce is loaded so class_exists('WooCommerce') is true.
			add_action('woocommerce_loaded', [__CLASS__, 'init_checkout_ship_to'], 5, 0);
			// Debug box: show on checkout for admins when "Show debugging info" is on.
			add_action('wp_footer', [__CLASS__, 'maybe_render_checkout_ship_to_debug'], 5, 0);
		}
		if (is_admin() && !empty($settings['coupon_show_email_column'])) {
			// Use a late priority so we can adjust columns after WooCommerce or other plugins.
			add_filter('manage_edit-shop_coupon_columns', [__CLASS__, 'add_coupon_email_column'], 99);
			add_action('manage_shop_coupon_posts_custom_column', [__CLASS__, 'render_coupon_email_column'], 10, 2);
		}
		if (class_exists('User_Manager_My_Account_Site_Admin') && self::is_my_account_site_admin_enabled($settings)) {
			User_Manager_My_Account_Site_Admin::init();
		}

		add_action('admin_bar_menu', [__CLASS__, 'add_user_manager_admin_bar_link'], 98);
		add_action('admin_bar_menu', [__CLASS__, 'add_custom_admin_bar_menu_items'], 99);
		// Quick Search add-on runs only when explicitly activated.
		$quick_search_enabled = !empty($settings['um_quick_search_enabled']) && !self::is_addon_temporarily_disabled('quick-search');
		if ($quick_search_enabled) {
			add_action('admin_bar_menu', [__CLASS__, 'add_quick_search_admin_bar_item'], 100);
			add_action('admin_footer', [__CLASS__, 'render_quick_search_dropdown']);
			add_action('wp_footer', [__CLASS__, 'render_quick_search_dropdown']);
			add_action('load-edit.php', [__CLASS__, 'quick_search_maybe_redirect_single_post']);
			add_action('load-users.php', [__CLASS__, 'quick_search_maybe_redirect_single_user']);
			add_action('load-edit-tags.php', [__CLASS__, 'quick_search_maybe_redirect_single_term']);
		}
		
		// New User Coupons: defer creation to front-end visits.
		if ($new_user_coupons_enabled) {
			add_action('template_redirect', [__CLASS__, 'maybe_create_new_user_coupon_on_visit'], 9);
		}
		// View monitoring: log page/post/product and archive views for reporting.
		add_action('template_redirect', [__CLASS__, 'maybe_log_view_reports'], 19);
		// 404 monitoring: log front-end 404 hits for reporting.
		add_action('template_redirect', [__CLASS__, 'maybe_log_404_error'], 20);
		// Search query monitoring: log front-end search queries (?s=) for reporting.
		add_action('template_redirect', [__CLASS__, 'maybe_log_search_query'], 20);
		$coupon_remainder_enabled = self::is_coupon_remainder_feature_enabled($settings);
		if ($coupon_remainder_enabled) {
			add_action('woocommerce_order_status_completed', [__CLASS__, 'queue_coupon_remainder_generation'], 20, 1);
			add_action('user_manager_process_coupon_remainder_async', [__CLASS__, 'maybe_generate_fixed_cart_coupon_remainders'], 10, 1);
			add_action('woocommerce_thankyou', [__CLASS__, 'handle_coupon_remainder_thankyou'], 5, 1);
			add_action('woocommerce_thankyou', [__CLASS__, 'render_coupon_remainder_debug_notice'], 8, 1);
			add_action('woocommerce_thankyou', [__CLASS__, 'render_order_received_remaining_balance_notice'], 10, 1);
			add_action('woocommerce_review_order_after_submit', [__CLASS__, 'render_checkout_coupon_remainder_debug'], 10);
			add_action('woocommerce_review_order_before_submit', [__CLASS__, 'render_checkout_remaining_balance_notice'], 10);
			// Block checkout support
			add_filter('render_block', [__CLASS__, 'maybe_detect_checkout_block'], 10, 2);
			add_action('wp_footer', [__CLASS__, 'inject_checkout_remaining_balance_notice'], 5);
			add_action('wp_footer', [__CLASS__, 'render_public_coupon_debug_output'], 999);
		}
		if (self::is_staging_dev_overrides_enabled($settings)) {
			add_action('admin_notices', [__CLASS__, 'render_non_production_admin_notice'], 6);
			add_action('wp_footer', [__CLASS__, 'render_non_production_frontend_notice_bar_fallback'], 0);
			add_action('wp_body_open', [__CLASS__, 'render_non_production_frontend_notice_bar'], 1);
			add_filter('pre_wp_mail', [__CLASS__, 'maybe_block_staging_dev_wp_mail'], 10, 2);
			add_filter('woocommerce_available_payment_gateways', [__CLASS__, 'maybe_disable_staging_dev_payment_gateways'], 999);
			add_filter('pre_http_request', [__CLASS__, 'maybe_block_staging_dev_http_requests'], 10, 3);
			add_filter('rest_pre_dispatch', [__CLASS__, 'maybe_block_staging_dev_rest_requests'], 10, 3);
			add_filter('woocommerce_webhook_should_deliver', [__CLASS__, 'maybe_block_staging_dev_woocommerce_webhook'], 10, 4);
		}
		
		// Frontend/site-wide behavior toggles based on settings.
		$settings = self::get_settings();
		if (!empty($settings['rebrand_reset_password_copy'])) {
			add_filter('woocommerce_lost_password_message', [__CLASS__, 'filter_lost_password_message']);
			add_action('wp_footer', [__CLASS__, 'print_lost_password_rebrand_script']);
			add_filter('password_change_email', [__CLASS__, 'filter_password_change_email'], 10, 3);
		}
		if (!empty($settings['nuc_debug_mode']) && $new_user_coupons_enabled) {
			add_action('wp_footer', [__CLASS__, 'render_new_user_coupon_debug_panel'], 1000);
		}

		// When enabled, clear applied coupons automatically whenever the cart
		// becomes empty (e.g. quantity changed from 1 to 0 or last item removed).
		$coupon_notifications_enabled = !empty($settings['user_coupon_notifications_enabled']) && !self::is_addon_temporarily_disabled('coupon-notifications-for-users-with-coupons');
		if ($coupon_notifications_enabled && !empty($settings['coupon_notifications_clear_coupons_when_cart_empty']) && class_exists('WooCommerce')) {
			add_action('woocommerce_cart_updated', [__CLASS__, 'maybe_clear_coupons_when_cart_empty']);
		}

		// Front-end search: when ?s= exactly matches a product/variation SKU, redirect to product.
		if (!empty($settings['search_redirect_by_sku'])) {
			add_action('template_redirect', [__CLASS__, 'maybe_redirect_search_to_product_by_sku'], 5);
		}
		// Apply coupon code from URL parameter (e.g. ?coupon-code=SAVE10).
		if (!empty($settings['coupon_code_url_param_enabled']) && class_exists('WooCommerce')) {
			add_action('woocommerce_cart_loaded_from_session', [__CLASS__, 'maybe_apply_coupon_from_url_param'], 20, 1);
		}

		// Bulk Add to Cart integration (migrated from standalone plugin).
		// Register shortcode/hooks when enabled, regardless of immediate WC class
		// availability, because plugin load order can initialize this plugin before
		// WooCommerce classes are declared.
		if (!empty($settings['bulk_add_to_cart_enabled'])) {
			if (!shortcode_exists('bulk_add_to_cart')) {
				add_shortcode('bulk_add_to_cart', [__CLASS__, 'bulk_add_to_cart_shortcode']);
			}
			add_filter('woocommerce_get_notices', [__CLASS__, 'bulk_add_to_cart_reorder_notices'], 20, 1);
			add_action('template_redirect', [__CLASS__, 'bulk_add_to_cart_maybe_download_sample_csv']);
			add_action('template_redirect', [__CLASS__, 'bulk_add_to_cart_maybe_download_sample_with_product_data']);
			add_action('template_redirect', [__CLASS__, 'bulk_add_to_cart_process_upload']);
		}
		if (!empty($settings['add_to_cart_variation_table_enabled'])) {
			self::register_add_to_cart_variation_table_render_hooks();
			add_action('woocommerce_cart_actions', [__CLASS__, 'maybe_render_empty_cart_button_on_cart_screen'], 15);
			add_action('template_redirect', [__CLASS__, 'maybe_handle_empty_cart_button_submission'], 14);
			add_action('template_redirect', [__CLASS__, 'handle_add_to_cart_variation_table_submission'], 15);
			add_action('wp_head', [__CLASS__, 'maybe_hide_default_add_to_cart_variation_form'], 20);
			add_action('wp_footer', [__CLASS__, 'maybe_render_add_to_cart_variation_table_trace_panel'], 1003);
		}
		if (!empty($settings['fatal_error_debugger_enabled'])) {
			self::register_fatal_error_debugger_shutdown_handler();
			add_action('wp_footer', [__CLASS__, 'maybe_render_fatal_error_debugger_panel'], 1004);
		}

		// Role Switching feature (front-end role preview) – only when enabled and
		// the standalone plugin is not already providing the same functionality.
		$role_settings = get_option('view_website_by_role_settings', []);
		$role_enabled  = !empty($role_settings['enabled']) && !self::is_addon_temporarily_disabled('user-role-switching');
		if ($role_enabled) {
			if (!function_exists('view_website_by_role_add_user_profile_fields')) {
				add_action('show_user_profile', [__CLASS__, 'render_role_switching_profile_fields']);
				add_action('edit_user_profile', [__CLASS__, 'render_role_switching_profile_fields']);
				add_action('personal_options_update', [__CLASS__, 'save_role_switching_profile_fields']);
				add_action('edit_user_profile_update', [__CLASS__, 'save_role_switching_profile_fields']);
			}
			if (!function_exists('view_website_by_role_handle_switch')) {
				add_action('wp_footer', [__CLASS__, 'render_role_switcher_bar']);
				add_action('init', [__CLASS__, 'handle_role_switching']);
			}
		}
		
		// Email preview endpoint
		add_action('wp_ajax_user_manager_email_preview', [__CLASS__, 'ajax_email_preview']);
		
		// Import log endpoint
		add_action('wp_ajax_user_manager_get_import_log', [__CLASS__, 'ajax_get_import_log']);
		
		// Activity details endpoint
		add_action('wp_ajax_user_manager_get_activity_details', [__CLASS__, 'ajax_get_activity_details']);
		
		// Lazy datalist options endpoint (loads list options on first focus/click).
		add_action('wp_ajax_user_manager_get_datalist_options', [__CLASS__, 'ajax_get_datalist_options']);
		// Login As user search endpoint (username/email lookup).
		add_action('wp_ajax_user_manager_search_users_for_login_as', [__CLASS__, 'ajax_search_users_for_login_as']);
		if (class_exists('User_Manager_Tab_Login_As')) {
			User_Manager_Tab_Login_As::init();
		}
		
		// Register action handlers
		User_Manager_Actions::init();
	}

	/**
	 * When enabled via settings, automatically clear all applied coupon codes
	 * whenever the WooCommerce cart becomes empty on the frontend.
	 *
	 * This helps avoid situations where a coupon remains applied after the last
	 * item is removed, which can hide User Coupon Notifications or remainder
	 * coupons until the cart is "reset".
	 */
	public static function maybe_clear_coupons_when_cart_empty(): void {
		// Avoid interfering with wp-admin order screens or non-cart contexts.
		if (is_admin() && !wp_doing_ajax()) {
			return;
		}
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}

		$cart = WC()->cart;

		// Only act when the cart is empty.
		if (!$cart->is_empty()) {
			return;
		}

		$applied = $cart->get_applied_coupons();
		if (empty($applied)) {
			return;
		}

		foreach ($applied as $code) {
			$cart->remove_coupon($code);
		}

		$cart->calculate_totals();
	}

	/**
	 * Whether Staging & Development Environment Overrides add-on is active.
	 *
	 * @param array<string,mixed>|null $settings Optional settings cache.
	 */
	private static function is_staging_dev_overrides_enabled(?array $settings = null): bool {
		if ($settings === null) {
			$settings = self::get_settings();
		}
		return !empty($settings['staging_dev_overrides_enabled']) && !self::is_addon_temporarily_disabled('staging-development-environment-overrides');
	}

	/**
	 * Whether New User Coupons add-on is active.
	 *
	 * @param array<string,mixed>|null $settings Optional settings cache.
	 */
	private static function is_new_user_coupons_enabled(?array $settings = null): bool {
		if ($settings === null) {
			$settings = self::get_settings();
		}
		return !empty($settings['nuc_enabled']) && !self::is_addon_temporarily_disabled('coupon-for-new-user');
	}

	/**
	 * Whether My Account Site Admin add-on is active.
	 *
	 * @param array<string,mixed>|null $settings Optional settings cache.
	 */
	private static function is_my_account_site_admin_enabled(?array $settings = null): bool {
		if ($settings === null) {
			$settings = self::get_settings();
		}
		$enabled = false;
		if (array_key_exists('my_account_site_admin_enabled', $settings)) {
			$enabled = !empty($settings['my_account_site_admin_enabled']);
		} else {
			$enabled = !empty($settings['my_account_admin_order_viewer_enabled'])
				|| !empty($settings['my_account_admin_product_viewer_enabled'])
				|| !empty($settings['my_account_admin_coupon_viewer_enabled'])
				|| !empty($settings['my_account_admin_user_viewer_enabled']);
		}
		return $enabled && !self::is_addon_temporarily_disabled('my-account-site-admin');
	}

	/**
	 * Get a default-true staging/dev setting value.
	 *
	 * @param array<string,mixed> $settings
	 */
	private static function get_staging_dev_default_true_setting(array $settings, string $key): bool {
		if (!array_key_exists($key, $settings)) {
			return true;
		}
		return !empty($settings[$key]);
	}

	/**
	 * Build data anonymized suffix text based on latest Data Anonymizer history.
	 */
	private static function get_staging_dev_data_anonymized_notice_suffix(array $settings): string {
		if (!self::get_staging_dev_default_true_setting($settings, 'staging_dev_notice_include_data_anonymized')) {
			return '';
		}
		$history = get_option('user_manager_data_anonymizer_history', []);
		if (!is_array($history) || empty($history)) {
			return __(' | Data anonymized: no runs recorded yet', 'user-manager');
		}
		$latest = $history[0];
		if (!is_array($latest)) {
			return __(' | Data anonymized: no runs recorded yet', 'user-manager');
		}
		$created_at = isset($latest['created_at']) ? (string) $latest['created_at'] : '';
		if ($created_at === '') {
			return __(' | Data anonymized: timestamp unavailable', 'user-manager');
		}
		$timestamp = strtotime($created_at);
		if (!$timestamp) {
			return sprintf(
				/* translators: %s: timestamp text */
				__(' | Data anonymized: %s', 'user-manager'),
				$created_at
			);
		}
		return sprintf(
			/* translators: %s: timestamp text */
			__(' | Data anonymized: %s', 'user-manager'),
			date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp)
		);
	}

	/**
	 * Render non-production warning notice in WP-Admin.
	 */
	public static function render_non_production_admin_notice(): void {
		if (wp_doing_ajax()) {
			return;
		}
		$settings = self::get_settings();
		if (!self::is_staging_dev_overrides_enabled($settings)) {
			return;
		}
		if (!self::get_staging_dev_default_true_setting($settings, 'staging_dev_notice_wp_admin')) {
			return;
		}
		$suffix = self::get_staging_dev_data_anonymized_notice_suffix($settings);
		?>
		<div class="notice notice-warning" style="margin:12px 0 16px;">
			<p>
				<strong><?php esc_html_e('Non-Production Environment', 'user-manager'); ?></strong>
				<?php echo esc_html__(' - staging/development overrides are active.', 'user-manager'); ?>
				<?php echo esc_html($suffix); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render non-production warning notice bar on front-end.
	 */
	public static function render_non_production_frontend_notice_bar(): void {
		if (is_admin() || wp_doing_ajax()) {
			return;
		}
		if (self::$staging_dev_notice_rendered) {
			return;
		}
		$settings = self::get_settings();
		if (!self::is_staging_dev_overrides_enabled($settings)) {
			return;
		}
		if (!self::get_staging_dev_default_true_setting($settings, 'staging_dev_notice_frontend_top_bar')) {
			return;
		}
		$suffix = self::get_staging_dev_data_anonymized_notice_suffix($settings);
		$message = __('Non-Production Environment - staging/development overrides are active.', 'user-manager') . $suffix;

		if (current_action() === 'wp_footer') {
			?>
			<script>
			(function() {
				var barId = 'um-non-production-notice-bar';
				if (document.getElementById(barId)) {
					return;
				}
				var bar = document.createElement('div');
				bar.id = barId;
				bar.className = 'um-non-production-notice-bar';
				bar.setAttribute('style', 'position:sticky;top:0;left:0;right:0;z-index:99999;background:#d63638;color:#fff;padding:10px 14px;font-size:13px;font-weight:600;text-align:center;');
				bar.textContent = <?php echo wp_json_encode($message); ?>;
				if (document.body) {
					document.body.insertBefore(bar, document.body.firstChild);
				}
			})();
			</script>
			<?php
			self::$staging_dev_notice_rendered = true;
			return;
		}
		?>
		<div id="um-non-production-notice-bar" class="um-non-production-notice-bar" style="position:sticky;top:0;left:0;right:0;z-index:99999;background:#d63638;color:#fff;padding:10px 14px;font-size:13px;font-weight:600;text-align:center;">
			<?php echo esc_html($message); ?>
		</div>
		<?php
		self::$staging_dev_notice_rendered = true;
	}

	/**
	 * Footer fallback for themes that do not call wp_body_open.
	 * Injects the notice at the top of <body>, not at the visual footer.
	 */
	public static function render_non_production_frontend_notice_bar_fallback(): void {
		if (is_admin() || wp_doing_ajax()) {
			return;
		}
		if (self::$staging_dev_notice_rendered) {
			return;
		}
		$settings = self::get_settings();
		if (!self::is_staging_dev_overrides_enabled($settings)) {
			return;
		}
		if (!self::get_staging_dev_default_true_setting($settings, 'staging_dev_notice_frontend_top_bar')) {
			return;
		}
		$suffix = self::get_staging_dev_data_anonymized_notice_suffix($settings);
		?>
		<script>
		(function() {
			if (!document.body || document.querySelector('.um-non-production-notice-bar')) {
				return;
			}
			var bar = document.createElement('div');
			bar.className = 'um-non-production-notice-bar';
			bar.style.position = 'sticky';
			bar.style.top = '0';
			bar.style.left = '0';
			bar.style.right = '0';
			bar.style.zIndex = '99999';
			bar.style.background = '#d63638';
			bar.style.color = '#fff';
			bar.style.padding = '10px 14px';
			bar.style.fontSize = '13px';
			bar.style.fontWeight = '600';
			bar.style.textAlign = 'center';
			bar.textContent = <?php echo wp_json_encode(__('Non-Production Environment - staging/development overrides are active.', 'user-manager') . $suffix); ?>;
			document.body.insertBefore(bar, document.body.firstChild);
		})();
		</script>
		<?php
		self::$staging_dev_notice_rendered = true;
	}

	/**
	 * Block outgoing wp_mail calls when staging/dev email override is enabled.
	 *
	 * @param null|bool|WP_Error $return Short-circuit return.
	 * @param array<string,mixed> $atts Mail attributes.
	 * @return null|bool|WP_Error
	 */
	public static function maybe_block_staging_dev_wp_mail($return, array $atts = []) {
		$settings = self::get_settings();
		if (!self::is_staging_dev_overrides_enabled($settings)) {
			return $return;
		}
		if (!self::get_staging_dev_default_true_setting($settings, 'staging_dev_disable_all_emails')) {
			return $return;
		}
		return false;
	}

	/**
	 * Disable all WooCommerce payment gateways when override is active.
	 *
	 * @param array<string,mixed> $gateways
	 * @return array<string,mixed>
	 */
	public static function maybe_disable_staging_dev_payment_gateways(array $gateways): array {
		$settings = self::get_settings();
		if (!self::is_staging_dev_overrides_enabled($settings)) {
			return $gateways;
		}
		if (!self::get_staging_dev_default_true_setting($settings, 'staging_dev_disable_all_payment_gateways')) {
			return $gateways;
		}
		return [];
	}

	/**
	 * Disable outbound HTTP API requests (except same-host/admin internal requests).
	 *
	 * @param false|array|WP_Error $preempt
	 * @param array<string,mixed> $args
	 * @param string $url
	 * @return false|array|WP_Error
	 */
	public static function maybe_block_staging_dev_http_requests($preempt, array $args, string $url) {
		$settings = self::get_settings();
		if (!self::is_staging_dev_overrides_enabled($settings)) {
			return $preempt;
		}
		if (!self::get_staging_dev_default_true_setting($settings, 'staging_dev_disable_all_api_json_requests')) {
			return $preempt;
		}
		if (wp_doing_cron() || wp_doing_ajax()) {
			return $preempt;
		}

		$target_host = (string) wp_parse_url($url, PHP_URL_HOST);
		$home_host = (string) wp_parse_url(home_url(), PHP_URL_HOST);
		if ($target_host !== '' && $home_host !== '' && strtolower($target_host) === strtolower($home_host)) {
			return $preempt;
		}

		return new WP_Error(
			'um_staging_dev_http_blocked',
			__('Blocked by Staging & Development Environment Overrides: API/JSON requests are disabled.', 'user-manager')
		);
	}

	/**
	 * Disable REST API endpoint dispatch when override is active.
	 *
	 * @param mixed $result
	 * @param WP_REST_Server $server
	 * @param WP_REST_Request $request
	 * @return mixed
	 */
	public static function maybe_block_staging_dev_rest_requests($result, $server, $request) {
		if (defined('WP_CLI') && WP_CLI) {
			return $result;
		}
		if (wp_doing_ajax()) {
			return $result;
		}
		$settings = self::get_settings();
		if (!self::is_staging_dev_overrides_enabled($settings)) {
			return $result;
		}
		if (!self::get_staging_dev_default_true_setting($settings, 'staging_dev_disable_all_api_json_requests')) {
			return $result;
		}
		return new WP_Error(
			'um_staging_dev_rest_blocked',
			__('Blocked by Staging & Development Environment Overrides: REST/API requests are disabled.', 'user-manager'),
			['status' => 503]
		);
	}

	/**
	 * Disable WooCommerce webhook delivery when override is active.
	 *
	 * WooCommerce has used different callback signatures over time (3 or 4 args),
	 * so keep trailing params optional for compatibility.
	 */
	public static function maybe_block_staging_dev_woocommerce_webhook(bool $should_deliver, $webhook = null, $arg = null, $resource = null): bool {
		$settings = self::get_settings();
		if (!self::is_staging_dev_overrides_enabled($settings)) {
			return $should_deliver;
		}
		if (!self::get_staging_dev_default_true_setting($settings, 'staging_dev_disable_all_webhooks')) {
			return $should_deliver;
		}
		return false;
	}
	
	/**
	 * Render Role Switching permissions on user profile screen.
	 *
	 * Mirrors the "View Website by Role Permission" card.
	 *
	 * @param WP_User $user User object.
	 */
	public static function render_role_switching_profile_fields($user): void {
		if (!current_user_can('manage_options')) {
			return;
		}

		$user_meta = get_user_meta($user->ID, 'view_website_by_role_permissions', true);
		if (!is_array($user_meta)) {
			$user_meta = [];
		}

		$roles                = wp_roles()->get_names();
		$default_roles        = self::get_user_role_switch_default_roles($user_meta);
		$default_role         = isset($default_roles[0]) ? (string) $default_roles[0] : '';
		$user_hidden_roles    = self::normalize_role_list($user_meta['hidden_roles'] ?? []);
		?>
		<div class="card" style="max-width:100%;margin-bottom:20px;padding:20px;background:#fff;border:1px solid #ccd0d4;box-shadow:0 1px 1px rgba(0,0,0,.04);">
			<h2 style="margin-top:0;padding-bottom:10px;border-bottom:1px solid #eee;">
				<span class="dashicons dashicons-visibility" style="margin-right:5px;"></span>
				<?php esc_html_e('Role Switching Permission', 'user-manager'); ?>
			</h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="um_role_switch_active"><?php esc_html_e('Active Non Administrator Roles', 'user-manager'); ?></label>
					</th>
					<td>
						<input type="checkbox" name="um_role_switch_active" id="um_role_switch_active" value="1" <?php checked(!empty($user_meta['active'])); ?> />
						<p class="description">
							<?php esc_html_e('Allow this user to preview the website as different non-administrator roles.', 'user-manager'); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="um_role_switch_admin"><?php esc_html_e('Active Administrator Roles', 'user-manager'); ?></label>
					</th>
					<td>
						<input type="checkbox" name="um_role_switch_admin" id="um_role_switch_admin" value="1" <?php checked(!empty($user_meta['admin'])); ?> />
						<p class="description">
							<?php esc_html_e('Allow this user to preview the website as administrator roles.', 'user-manager'); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="um_role_switch_default_role"><?php esc_html_e('Default Role', 'user-manager'); ?></label>
					</th>
					<td>
						<select name="um_role_switch_default_role" id="um_role_switch_default_role">
							<option value=""><?php esc_html_e('— No default role —', 'user-manager'); ?></option>
							<?php foreach ($roles as $role_key => $role_name) : ?>
								<option value="<?php echo esc_attr($role_key); ?>" <?php selected($default_role, $role_key); ?>>
									<?php echo esc_html($role_name); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e('Select one default role to restore when using the "Reset to Default Roles" button.', 'user-manager'); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php esc_html_e('Roles to Hide', 'user-manager'); ?></label>
					</th>
					<td>
						<?php foreach ($roles as $role_key => $role_name) : ?>
							<label style="display:block;margin-bottom:5px;">
								<input type="checkbox" name="um_role_switch_hidden_roles[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $user_hidden_roles, true)); ?> />
								<?php echo esc_html($role_name); ?>
							</label>
						<?php endforeach; ?>
						<p class="description">
							<?php esc_html_e('Hidden roles are removed from this user\'s front-end role switcher so they cannot view or select them.', 'user-manager'); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Save Role Switching profile fields.
	 *
	 * @param int $user_id User ID.
	 */
	public static function save_role_switching_profile_fields(int $user_id): void {
		if (!current_user_can('manage_options')) {
			return;
		}
		$roles = wp_roles()->get_names();

		$old_meta = get_user_meta($user_id, 'view_website_by_role_permissions', true);
		if (!is_array($old_meta)) {
			$old_meta = [];
		}

		$user_meta = [];
		$changes   = [];

		// Active non-admin roles.
		if (!empty($_POST['um_role_switch_active'])) {
			$user_meta['active'] = true;
			if (empty($old_meta['active'])) {
				$changes[] = 'Enabled non-admin role preview';
			}
		} elseif (!empty($old_meta['active'])) {
			$changes[] = 'Disabled non-admin role preview';
		}

		// Active admin roles.
		if (!empty($_POST['um_role_switch_admin'])) {
			$user_meta['admin'] = true;
			if (empty($old_meta['admin'])) {
				$changes[] = 'Enabled admin role preview';
			}
		} elseif (!empty($old_meta['admin'])) {
			$changes[] = 'Disabled admin role preview';
		}

		// Default role (single selection).
		$old_default_roles = self::get_user_role_switch_default_roles($old_meta);
		$new_default_role = isset($_POST['um_role_switch_default_role']) ? sanitize_key(wp_unslash($_POST['um_role_switch_default_role'])) : '';
		if ($new_default_role !== '' && isset($roles[$new_default_role])) {
			$user_meta['default_roles'] = [$new_default_role];
		}
		$new_default_roles = self::get_user_role_switch_default_roles($user_meta);
		if ($old_default_roles !== $new_default_roles) {
			$changes[] = 'Updated default role';
		}

		// Roles to hide for this user.
		$old_hidden_roles = self::normalize_role_list($old_meta['hidden_roles'] ?? []);
		$new_hidden_roles = isset($_POST['um_role_switch_hidden_roles']) ? self::normalize_role_list(wp_unslash($_POST['um_role_switch_hidden_roles'])) : [];
		$new_hidden_roles = array_values(array_filter($new_hidden_roles, static function (string $role_key) use ($roles): bool {
			return isset($roles[$role_key]);
		}));
		if (!empty($new_hidden_roles)) {
			$user_meta['hidden_roles'] = $new_hidden_roles;
		}
		if ($old_hidden_roles !== $new_hidden_roles) {
			$changes[] = 'Updated per-user roles to hide';
		}

		if (!empty($changes)) {
			$user = get_user_by('id', $user_id);
			if ($user instanceof WP_User) {
				self::add_role_switch_history(
					'User Permissions Update',
					'User: ' . $user->user_login . ' | ' . implode(' | ', $changes)
				);
			}
		}

		update_user_meta($user_id, 'view_website_by_role_permissions', $user_meta);
	}

	/**
	 * Render the front-end role switcher bar.
	 */
	public static function render_role_switcher_bar(): void {
		if (!is_user_logged_in()) {
			return;
		}

		$role_settings = get_option('view_website_by_role_settings', []);
		if (empty($role_settings['enabled'])) {
			return;
		}

		$current_user = wp_get_current_user();
		$user_meta    = get_user_meta($current_user->ID, 'view_website_by_role_permissions', true);
		if (!is_array($user_meta) || empty($user_meta)) {
			return;
		}

		$hidden_roles = self::get_effective_role_switch_hidden_roles($role_settings, $user_meta);
		$allow_reset  = !empty($role_settings['allow_reset']);

		$roles        = wp_roles()->get_names();
		$current_roles = (array) $current_user->roles;

		// Filter roles by permissions and hidden list.
		foreach ($roles as $role_key => $role_name) {
			if (in_array($role_key, $hidden_roles, true)) {
				unset($roles[$role_key]);
				continue;
			}
			if ($role_key === 'administrator' && empty($user_meta['admin'])) {
				unset($roles[$role_key]);
				continue;
			}
			if ($role_key !== 'administrator' && empty($user_meta['active'])) {
				unset($roles[$role_key]);
				continue;
			}
		}

		if (empty($roles)) {
			return;
		}

		$current_role_key = $current_roles[0] ?? '';
		$current_label    = [];
		foreach ($current_roles as $role) {
			if (isset($roles[$role])) {
				$current_label[] = $roles[$role];
			}
		}
		$current_roles_text = implode(', ', $current_label);

		$default_roles     = self::get_user_role_switch_default_roles($user_meta);
		$reset_roles       = [];
		foreach ($default_roles as $default_role_key) {
			if (!isset($roles[$default_role_key])) {
				continue;
			}
			if (in_array($default_role_key, $hidden_roles, true)) {
				continue;
			}
			if (!self::role_switch_user_can_select_role($default_role_key, $user_meta)) {
				continue;
			}
			$reset_roles[] = $default_role_key;
		}
		$has_default_roles = !empty($reset_roles);
		?>
		<div id="um-role-switcher-bar" style="position:fixed;bottom:0;left:0;right:0;background:#1d2327;padding:10px 16px;box-shadow:0 -2px 4px rgba(0,0,0,0.2);z-index:999999;">
			<form method="post" action="" style="display:flex;align-items:center;gap:12px;flex-wrap:nowrap;max-width:1200px;margin:0 auto;">
				<?php wp_nonce_field('um_role_switch', 'um_role_switch_nonce'); ?>
				<label for="um_role_switch_select" style="margin:0;color:#fff;font-size:0.9em;"><?php esc_html_e('View as:', 'user-manager'); ?></label>
				<select name="um_role_switch_role" id="um_role_switch_select" style="min-width:220px;background:#2c3338;color:#fff;border:1px solid #3c434a;padding:5px 28px 5px 8px;border-radius:4px;font-size:0.9em;" onchange="this.form.submit();">
					<?php foreach ($roles as $role_key => $role_name) : ?>
						<option value="<?php echo esc_attr($role_key); ?>" <?php selected($current_role_key, $role_key); ?>>
							<?php echo esc_html($role_name); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php if ($allow_reset && $has_default_roles) : ?>
					<button type="submit" name="um_role_switch_reset" value="1" class="button button-secondary" style="margin-left:8px;font-size:0.85em;">
						<?php esc_html_e('Reset to Default Roles', 'user-manager'); ?>
					</button>
				<?php endif; ?>
				<span style="color:#fff;font-size:0.9em;white-space:nowrap;margin-left:auto;">
					<?php esc_html_e('Current Role:', 'user-manager'); ?> <?php echo esc_html($current_roles_text); ?>
				</span>
			</form>
		</div>
		<style>
		body { padding-bottom: 60px !important; }
		#um-role-switcher-bar select option {
			background:#2c3338 !important;
			color:#fff !important;
			font-size:0.9em !important;
		}
		#um-role-switcher-bar button:hover {
			opacity:0.9 !important;
		}
		@media screen and (max-width:782px) {
			#um-role-switcher-bar form {
				flex-wrap:wrap !important;
				justify-content:flex-start !important;
			}
		}
		</style>
		<?php
	}

	/**
	 * Handle role switching POST actions.
	 */
	public static function handle_role_switching(): void {
		if (!is_user_logged_in()) {
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			return;
		}
		if (empty($_POST['um_role_switch_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['um_role_switch_nonce'])), 'um_role_switch')) {
			return;
		}

		$role_settings = get_option('view_website_by_role_settings', []);
		if (empty($role_settings['enabled'])) {
			return;
		}

		$current_user = wp_get_current_user();
		$user_meta    = get_user_meta($current_user->ID, 'view_website_by_role_permissions', true);
		if (!is_array($user_meta) || empty($user_meta)) {
			return;
		}

		$roles       = wp_roles()->get_names();
		$old_roles   = (array) $current_user->roles;
		$old_label   = implode(', ', $old_roles);
		$hidden_roles = self::get_effective_role_switch_hidden_roles($role_settings, $user_meta);

		// Handle reset to default roles.
		$default_roles = self::get_user_role_switch_default_roles($user_meta);
		if (!empty($_POST['um_role_switch_reset']) && !empty($default_roles)) {
			$reset_roles = [];
			foreach ($default_roles as $role) {
				if (!isset($roles[$role])) {
					continue;
				}
				if (in_array($role, $hidden_roles, true)) {
					continue;
				}
				if (!self::role_switch_user_can_select_role($role, $user_meta)) {
					continue;
				}
				$reset_roles[] = $role;
			}
			if (empty($reset_roles)) {
				return;
			}
			// Remove all current roles.
			foreach ($current_user->roles as $role) {
				$current_user->remove_role($role);
			}
			// Add back default roles.
			foreach ($reset_roles as $role) {
				if (isset($roles[$role])) {
					$current_user->add_role($role);
				}
			}

			self::add_role_switch_change('Role Reset', $old_label, implode(', ', $reset_roles), $current_user);
			return;
		}

		// Handle direct role switch.
		if (empty($_POST['um_role_switch_role'])) {
			return;
		}

		$new_role = sanitize_text_field(wp_unslash($_POST['um_role_switch_role']));
		if (!isset($roles[$new_role])) {
			return;
		}
		if (in_array($new_role, $hidden_roles, true)) {
			return;
		}

		// Permission checks.
		if (!self::role_switch_user_can_select_role($new_role, $user_meta)) {
			return;
		}

		$current_user->set_role($new_role);
		self::add_role_switch_change('Role Change', $old_label, $new_role, $current_user);
	}

	/**
	 * Normalize and dedupe role keys from mixed input.
	 *
	 * @param mixed $roles Raw role list.
	 * @return array<int,string>
	 */
	private static function normalize_role_list($roles): array {
		$roles = is_array($roles) ? $roles : [$roles];
		$roles = array_map(static function ($role): string {
			return sanitize_key((string) $role);
		}, $roles);
		$roles = array_values(array_unique(array_filter($roles)));
		return $roles;
	}

	/**
	 * Read user default role(s) with backward compatibility support.
	 *
	 * @param array<string,mixed> $user_meta Role switch user meta.
	 * @return array<int,string>
	 */
	private static function get_user_role_switch_default_roles(array $user_meta): array {
		$default_roles = self::normalize_role_list($user_meta['default_roles'] ?? []);
		// Keep only first role because UI now supports a single default role.
		if (count($default_roles) > 1) {
			$default_roles = [reset($default_roles)];
		}
		return $default_roles;
	}

	/**
	 * Combine global hidden roles with per-user hidden roles.
	 *
	 * @param array<string,mixed> $role_settings Global role-switch settings.
	 * @param array<string,mixed> $user_meta User role-switch meta.
	 * @return array<int,string>
	 */
	private static function get_effective_role_switch_hidden_roles(array $role_settings, array $user_meta): array {
		$global_hidden_roles = self::normalize_role_list($role_settings['hidden_roles'] ?? []);
		$user_hidden_roles = self::normalize_role_list($user_meta['hidden_roles'] ?? []);
		return array_values(array_unique(array_merge($global_hidden_roles, $user_hidden_roles)));
	}

	/**
	 * Check whether user permissions allow selecting a role.
	 *
	 * @param array<string,mixed> $user_meta User role-switch meta.
	 */
	private static function role_switch_user_can_select_role(string $role_key, array $user_meta): bool {
		if ($role_key === 'administrator') {
			return !empty($user_meta['admin']);
		}
		return !empty($user_meta['active']);
	}

	/**
	 * Record a role switch / reset change.
	 *
	 * @param string   $action      Action label.
	 * @param string   $existing    Existing roles text.
	 * @param string   $new         New role(s) text.
	 * @param WP_User  $current_user User object.
	 */
	private static function add_role_switch_change(string $action, string $existing, string $new, WP_User $current_user): void {
		// Legacy option-based history (kept for backwards compatibility, not used for new UI).
		$changes   = get_option('view_website_by_role_changes', []);
		$changes[] = [
			'timestamp'     => current_time('mysql'),
			'user_id'       => $current_user->ID,
			'username'      => $current_user->user_login,
			'action'        => $action,
			'existing_role' => $existing,
			'new_role'      => $new,
		];

		if (count($changes) > 100) {
			$changes = array_slice($changes, -100);
		}
		update_option('view_website_by_role_changes', $changes);

		// New: log to Admin Activity Log so the Role Switching tab can pull recent
		// history from a single unified source.
		self::add_activity_log(
			'role_switch_change',
			$current_user->ID,
			'Role Switching',
			[
				'change_type'    => $action,   // e.g. "Role Change" / "Role Reset".
				'existing_roles' => $existing,
				'new_roles'      => $new,
			]
		);
	}

	/**
	 * Record settings / permission history for Role Switching.
	 *
	 * @param string $action  Action label.
	 * @param string $details Details string.
	 */
	public static function add_role_switch_history(string $action, string $details): void {
		$current_user = wp_get_current_user();
		$history      = get_option('view_website_by_role_history', []);

		$entry = [
			'timestamp' => current_time('mysql'),
			'user_id'   => $current_user->ID,
			'username'  => $current_user->user_login,
			'action'    => $action,
			'details'   => $details,
		];

		array_unshift($history, $entry);
		if (count($history) > 100) {
			$history = array_slice($history, 0, 100);
		}

		update_option('view_website_by_role_history', $history);

		// New: mirror Role Switching configuration changes into Admin Activity Log.
		self::add_activity_log(
			'role_switch_settings',
			$current_user->ID,
			'Role Switching',
			[
				'history_action' => $action,
				'details'        => $details,
			]
		);
	}

	/**
	 * Process history details, turning "User: username" into profile links.
	 *
	 * @param string $details Raw details.
	 * @return string
	 */
	public static function process_role_switch_history_details(string $details): string {
		$pattern = '/User:\s+([^\s|]+)/';
		return (string) preg_replace_callback(
			$pattern,
			static function (array $matches) {
				$username = $matches[1];
				$user     = get_user_by('login', $username);
				if ($user instanceof WP_User) {
					return sprintf(
						'User: <a href="%s">%s</a>',
						esc_url(get_edit_user_link($user->ID)),
						esc_html($username)
					);
				}
				return $matches[0];
			},
			$details
		);
	}
	
	/**
	 * Add coupon email converter meta box on coupon edit screen.
	 */
	public static function add_coupon_email_converter_meta_box(): void {
		add_meta_box(
			'um_coupon_email_converter',
			__('Email List Converter', 'user-manager'),
			[__CLASS__, 'render_coupon_email_converter_meta_box'],
			'shop_coupon',
			'side',
			'default'
		);
	}

	/**
	 * Add "All Post Meta" meta box to all post types (when setting is enabled).
	 *
	 * @param string   $post_type Post type.
	 * @param \WP_Post $post      Post object.
	 */
	public static function add_all_post_meta_meta_box(string $post_type, $post): void {
		$settings = self::get_settings();
		if (!self::can_current_user_access_post_meta_viewer($settings)) {
			return;
		}
		$enabled_post_types = self::get_enabled_post_meta_post_types($settings);
		if (!in_array($post_type, $enabled_post_types, true)) {
			return;
		}
		add_meta_box(
			'um_all_post_meta',
			__('Post Meta (All Fields & Values)', 'user-manager'),
			[__CLASS__, 'render_all_post_meta_meta_box'],
			$post_type,
			'normal',
			'low',
			['post' => $post]
		);
	}

	/**
	 * Render the all-post-meta meta box: list every meta key and value(s). Optionally allow editing.
	 *
	 * @param \WP_Post $post   Post object.
	 * @param array    $args   Meta box args (contains 'post').
	 */
	public static function render_all_post_meta_meta_box($post, array $args = []): void {
		if (!$post || !isset($post->ID)) {
			return;
		}
		$settings = get_option(self::OPTION_KEY, []);
		$allow_edit = !empty($settings['allow_edit_post_meta']);
		$meta       = get_post_meta($post->ID);
		if (!is_array($meta)) {
			$meta = [];
		}
		ksort($meta);
		$can_edit = $allow_edit && current_user_can('edit_post', $post->ID);
		?>
		<style>
			.um-all-post-meta-table { width: 100%; border-collapse: collapse; font-size: 12px; }
			.um-all-post-meta-table th, .um-all-post-meta-table td { padding: 6px 8px; border: 1px solid #c3c4c7; text-align: left; vertical-align: top; }
			.um-all-post-meta-table th { background: #f0f0f1; width: 200px; max-width: 200px; word-break: break-all; }
			.um-all-post-meta-table td { background: #fff; }
			.um-all-post-meta-table textarea { width: 100%; min-height: 40px; font-family: Consolas, Monaco, monospace; font-size: 12px; box-sizing: border-box; }
			.um-all-post-meta-table input[type="text"] { width: 100%; font-family: Consolas, Monaco, monospace; font-size: 12px; box-sizing: border-box; }
			.um-all-post-meta-table .meta-key { font-family: Consolas, Monaco, monospace; }
			.um-all-post-meta-table .um-post-meta-new-sep { background: #f6f7f7; font-weight: 600; }
			.um-all-post-meta-table .um-post-meta-new-sep td { border-top: 2px solid #c3c4c7; padding-top: 10px; }
			.um-all-post-meta-table .um-post-meta-add-row td { border-top: 1px solid #c3c4c7; padding-top: 8px; }
			.um-all-post-meta-table .um-post-meta-unchanged { opacity: 0.6; }
		</style>
		<table class="um-all-post-meta-table widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e('Meta Key', 'user-manager'); ?></th>
					<th><?php echo $can_edit ? esc_html__('Value (editable)', 'user-manager') : esc_html__('Value', 'user-manager'); ?></th>
				</tr>
			</thead>
			<tbody id="um-post-meta-tbody">
				<?php if (!empty($meta)) : ?>
					<?php foreach ($meta as $key => $values) :
						$values = is_array($values) ? $values : [$values];
						$single_val = count($values) === 1 ? $values[0] : implode("\n", $values);
						$display_val = is_string($single_val) ? $single_val : maybe_serialize($single_val);
						?>
						<tr>
							<td class="meta-key"><code><?php echo esc_html($key); ?></code></td>
							<td>
								<?php if ($can_edit) : ?>
									<label for="um_pm_<?php echo esc_attr(sanitize_html_class($key)); ?>" class="screen-reader-text"><?php echo esc_html($key); ?></label>
									<textarea
										name="um_post_meta_edit[<?php echo esc_attr($key); ?>]"
										id="um_pm_<?php echo esc_attr(sanitize_html_class($key)); ?>"
										rows="2"
										data-um-original-value="<?php echo esc_attr($display_val); ?>"
										class="um-post-meta-edit-field um-post-meta-unchanged"
									><?php echo esc_textarea($display_val); ?></textarea>
								<?php else : ?>
									<pre style="margin:0; white-space: pre-wrap; word-break: break-word; font-size: 12px;"><?php echo esc_html($display_val); ?></pre>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="2"><?php esc_html_e('No post meta for this item.', 'user-manager'); ?></td>
					</tr>
				<?php endif; ?>

				<?php if ($can_edit) : ?>
					<tr class="um-post-meta-new-sep">
						<td colspan="2"><?php esc_html_e('Add new meta field', 'user-manager'); ?></td>
					</tr>
					<tr class="um-post-meta-new-row" id="um-post-meta-new-template">
						<td class="meta-key">
							<input type="text" name="um_post_meta_new_key[]" value="" placeholder="<?php esc_attr_e('Meta key', 'user-manager'); ?>" />
						</td>
						<td>
							<textarea name="um_post_meta_new_value[]" rows="2" placeholder="<?php esc_attr_e('Value', 'user-manager'); ?>"></textarea>
						</td>
					</tr>
					<tr class="um-post-meta-add-row" id="um-post-meta-add-row-tr">
						<td colspan="2">
							<button type="button" class="button" id="um-post-meta-add-row"><?php esc_html_e('Add another', 'user-manager'); ?></button>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php if ($can_edit) : ?>
			<?php wp_nonce_field('um_save_post_meta_meta_box', 'um_post_meta_meta_box_nonce'); ?>
			<p class="description" style="margin-top: 8px;"><?php esc_html_e('Save the post to apply changes. Edit existing meta above or add new key/value pairs below.', 'user-manager'); ?></p>
			<p class="description" style="margin-top: 4px;"><?php esc_html_e('Performance tip: only changed meta values are submitted to avoid oversized save requests on Products.', 'user-manager'); ?></p>
			<script>
			(function() {
				var template = document.getElementById('um-post-meta-new-template');
				var addRowTr = document.getElementById('um-post-meta-add-row-tr');
				var btn = document.getElementById('um-post-meta-add-row');
				var form = document.getElementById('post');
				if (!template || !addRowTr || !btn) return;
				btn.addEventListener('click', function() {
					var clone = template.cloneNode(true);
					clone.removeAttribute('id');
					clone.querySelector('input[type="text"]').value = '';
					clone.querySelector('textarea').value = '';
					addRowTr.parentNode.insertBefore(clone, addRowTr);
				});
				function syncChangedState(field) {
					if (!field) { return; }
					var original = field.getAttribute('data-um-original-value') || '';
					if (field.value === original) {
						field.classList.add('um-post-meta-unchanged');
					} else {
						field.classList.remove('um-post-meta-unchanged');
					}
				}
				var fields = document.querySelectorAll('.um-post-meta-edit-field');
				fields.forEach(function(field) {
					syncChangedState(field);
					field.addEventListener('input', function() {
						syncChangedState(field);
					});
					field.addEventListener('change', function() {
						syncChangedState(field);
					});
				});
				if (form) {
					form.addEventListener('submit', function() {
						fields.forEach(function(field) {
							var original = field.getAttribute('data-um-original-value') || '';
							if (field.value === original) {
								field.disabled = true;
							}
						});
					});
				}
			})();
			</script>
		<?php endif;
	}

	/**
	 * Save post meta from the "All Post Meta" meta box when editing is allowed.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public static function save_all_post_meta_meta_box(int $post_id, $post): void {
		$settings = get_option(self::OPTION_KEY, []);
		if (empty($settings['allow_edit_post_meta']) || empty($settings['display_post_meta_meta_box'])) {
			return;
		}
		if (!self::can_current_user_access_post_meta_viewer(is_array($settings) ? $settings : [])) {
			return;
		}
		$post_type = is_object($post) && isset($post->post_type) ? sanitize_key((string) $post->post_type) : '';
		if ($post_type !== '') {
			$enabled_post_types = self::get_enabled_post_meta_post_types(is_array($settings) ? $settings : []);
			if (!in_array($post_type, $enabled_post_types, true)) {
				return;
			}
		}
		if (!isset($_POST['um_post_meta_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['um_post_meta_meta_box_nonce'])), 'um_save_post_meta_meta_box')) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		$existing_keys = array_keys(get_post_meta($post_id));
		$existing_keys = array_flip($existing_keys);
		if (isset($_POST['um_post_meta_edit']) && is_array($_POST['um_post_meta_edit'])) {
			foreach ($_POST['um_post_meta_edit'] as $meta_key => $meta_value) {
				$meta_key = sanitize_key($meta_key);
				if ($meta_key === '' || !isset($existing_keys[$meta_key])) {
					continue;
				}
				$meta_value = wp_unslash($meta_value);
				update_post_meta($post_id, $meta_key, $meta_value);
			}
		}
		// Add new meta fields (key/value pairs from the "Add new meta field" section).
		$new_keys   = isset($_POST['um_post_meta_new_key']) && is_array($_POST['um_post_meta_new_key']) ? $_POST['um_post_meta_new_key'] : [];
		$new_values = isset($_POST['um_post_meta_new_value']) && is_array($_POST['um_post_meta_new_value']) ? $_POST['um_post_meta_new_value'] : [];
		$new_values = array_pad($new_values, count($new_keys), '');
		foreach ($new_keys as $i => $raw_key) {
			$meta_key = sanitize_key($raw_key);
			if ($meta_key === '') {
				continue;
			}
			$meta_value = isset($new_values[$i]) ? wp_unslash($new_values[$i]) : '';
			add_post_meta($post_id, $meta_key, $meta_value);
		}
	}

	/**
	 * Get enabled post types for Post Meta Viewer.
	 *
	 * Default behavior: all UI post types are enabled when no explicit list is saved.
	 *
	 * @param array<string,mixed> $settings Settings array.
	 * @return array<int,string>
	 */
	private static function get_enabled_post_meta_post_types(array $settings): array {
		$post_types = get_post_types(['show_ui' => true], 'names');
		if (!is_array($post_types)) {
			return [];
		}
		$post_types = array_values(array_map('sanitize_key', $post_types));

		$selected = [];
		if (!empty($settings['display_post_meta_post_types']) && is_array($settings['display_post_meta_post_types'])) {
			$selected = array_values(array_map('sanitize_key', $settings['display_post_meta_post_types']));
			$selected = array_values(array_intersect($selected, $post_types));
		}

		return !empty($selected) ? $selected : $post_types;
	}

	/**
	 * Whether current user can access the Post Meta Viewer.
	 *
	 * Access logic:
	 * - No role/user restrictions configured => allow all users who can edit posts.
	 * - If restrictions are configured => grant access when role OR username/email matches.
	 *
	 * @param array<string,mixed> $settings Settings array.
	 */
	private static function can_current_user_access_post_meta_viewer(array $settings): bool {
		if (!is_user_logged_in()) {
			return false;
		}

		$user = wp_get_current_user();
		if (!$user instanceof WP_User || !$user->ID) {
			return false;
		}

		$valid_roles = array_keys(self::get_user_roles());
		$valid_roles = array_map('sanitize_key', $valid_roles);

		$target_roles = [];
		if (!empty($settings['display_post_meta_allowed_roles']) && is_array($settings['display_post_meta_allowed_roles'])) {
			$target_roles = array_values(array_map('sanitize_key', $settings['display_post_meta_allowed_roles']));
			$target_roles = array_values(array_intersect($target_roles, $valid_roles));
		}

		$target_users = [];
		if (!empty($settings['display_post_meta_allowed_users'])) {
			$raw_users = $settings['display_post_meta_allowed_users'];
			if (!is_array($raw_users)) {
				$raw_users = preg_split('/[\r\n,;]+/', (string) $raw_users);
			}
			if (is_array($raw_users)) {
				foreach ($raw_users as $identifier) {
					$identifier = trim(strtolower((string) $identifier));
					if ($identifier === '') {
						continue;
					}
					if (is_email($identifier)) {
						$email = sanitize_email($identifier);
						if ($email !== '') {
							$target_users[] = strtolower($email);
						}
						continue;
					}
					$username = sanitize_user($identifier, false);
					if ($username !== '') {
						$target_users[] = strtolower($username);
					}
				}
			}
			$target_users = array_values(array_unique($target_users));
		}

		// No specific role/user restrictions: allow everyone who can edit this screen/post.
		if (empty($target_roles) && empty($target_users)) {
			return true;
		}

		return self::wp_admin_css_user_matches_targets($user, (array) $user->roles, $target_users, $target_roles);
	}

	/**
	 * Render the email converter meta box content.
	 */
	public static function render_coupon_email_converter_meta_box($post): void {
		?>
		<style>
		.um-email-converter textarea { width: 100%; min-height: 120px; font-family: monospace; font-size: 12px; }
		.um-email-converter .button { margin-top: 10px; width: 100%; }
		.um-email-converter-result { margin-top: 10px; padding: 8px; background: #f0f0f1; border-radius: 4px; font-size: 11px; word-break: break-all; display: none; max-height: 140px; overflow-y: auto; }
		.um-email-converter-result.show { display: block; }
		.um-email-converter-count { margin-top: 8px; font-size: 11px; color: #666; }
		</style>
		<div class="um-email-converter">
			<label for="um_email_list"><strong><?php esc_html_e('Paste emails (one per line):', 'user-manager'); ?></strong></label>
			<textarea id="um_email_list" placeholder="email1@example.com&#10;email2@example.com&#10;email3@example.com"></textarea>
			<button type="button" class="button" id="um_convert_emails"><?php esc_html_e('Convert to Comma List', 'user-manager'); ?></button>
			<div class="um-email-converter-result" id="um_email_result"></div>
			<div class="um-email-converter-count" id="um_email_count"></div>
			<button type="button" class="button button-primary" id="um_prepend_emails" style="display:none;"><?php esc_html_e('Append to Start of Allowed Emails', 'user-manager'); ?></button>
			<button type="button" class="button button-primary" id="um_append_emails" style="display:none;"><?php esc_html_e('Append to End of Allowed Emails', 'user-manager'); ?></button>
			<button type="button" class="button button-primary" id="um_apply_emails" style="display:none;"><?php esc_html_e('Replace All with New List', 'user-manager'); ?></button>
		</div>
		<script>
		jQuery(document).ready(function($) {
			var convertedEmails = [];

			/**
			 * Helper: locate the "Allowed emails" field (textarea preferred).
			 */
			function getAllowedEmailField() {
				var $field = $('#customer_email');
				if ($field.length === 0) {
					$field = $('[name="customer_email"]');
				}
				if ($field.length > 1) {
					var $visible = $field.filter(':visible');
					if ($visible.length) {
						$field = $visible;
					}
				}
				return $field.first();
			}

			/**
			 * Helper: parse a string of emails into a unique, normalized array.
			 */
			function parseEmails(raw) {
				var chunks = [];
				if (Array.isArray(raw)) {
					raw.forEach(function(item) {
						chunks = chunks.concat(String(item || '').split(/[\n,;\s]+/));
					});
				} else {
					chunks = String(raw || '').split(/[\n,;\s]+/);
				}
				var valid = [];
				var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
				chunks.forEach(function(email) {
					email = (email || '').trim().toLowerCase();
					if (email && emailRegex.test(email) && valid.indexOf(email) === -1) {
						valid.push(email);
					}
				});
				return valid;
			}

			/**
			 * Helper: get the current emails from the Allowed Emails field.
			 */
			function getCurrentAllowedEmails() {
				var $field = getAllowedEmailField();
				if (!$field.length) {
					return [];
				}
				return parseEmails($field.val());
			}

			/**
			 * Update the stats line beneath the converter.
			 */
			function updateCounts(validEmails) {
				var current = getCurrentAllowedEmails();
				var toAdd = validEmails.filter(function(email) {
					return current.indexOf(email) === -1;
				});
				var totalAfter = current.length + toAdd.length;

				var msg = '';
				msg += '<strong>Valid in pasted list:</strong> ' + validEmails.length + ' email(s)<br>';
				msg += '<strong>Currently in Allowed Emails:</strong> ' + current.length + ' email(s)<br>';
				msg += '<strong>New unique to add:</strong> ' + toAdd.length + ' email(s)<br>';
				msg += '<strong>Total after apply:</strong> ' + totalAfter + ' email(s)';
				$('#um_email_count').html(msg);
			}

			$('#um_convert_emails').on('click', function() {
				var input = $('#um_email_list').val();
				var validEmails = parseEmails(input);

				if (validEmails.length > 0) {
					convertedEmails = validEmails;
					var commaList = validEmails.join(', ');
					$('#um_email_result').text(commaList).addClass('show');
					updateCounts(validEmails);
					$('#um_apply_emails, #um_prepend_emails, #um_append_emails').show();
				} else {
					convertedEmails = [];
					$('#um_email_result').text('No valid emails found').addClass('show');
					$('#um_email_count').text('');
					$('#um_apply_emails, #um_prepend_emails, #um_append_emails').hide();
				}
			});

			/**
			 * Apply emails to the Allowed Emails field.
			 *
			 * mode:
			 * - 'replace'  => replace existing value with converted list.
			 * - 'prepend'  => add new emails before existing ones.
			 * - 'append'   => add new emails after existing ones.
			 */
			function applyEmails(mode) {
				if (!convertedEmails || !convertedEmails.length) {
					alert('Please convert a list of emails first.');
					return;
				}

				var $field = getAllowedEmailField();
				var listString = convertedEmails.join(', ');

				if (!$field.length) {
					// Fallback: copy to clipboard if we can't find the field.
					if (navigator.clipboard && navigator.clipboard.writeText) {
						navigator.clipboard.writeText(listString).then(function() {
							alert('Copied to clipboard! Paste into the \"Allowed emails\" field in Usage restriction tab.');
						}).catch(function() {
							alert('Could not find email field. Please copy the converted list manually.');
						});
					} else {
						alert('Could not find email field. Please copy the converted list manually.');
					}
					return;
				}

				var current = parseEmails($field.val());
				var merged = [];

				if (mode === 'replace') {
					merged = convertedEmails.slice();
				} else if (mode === 'prepend') {
					// New emails first, then existing ones that aren't already in the new list.
					var tail = current.filter(function(email) {
						return convertedEmails.indexOf(email) === -1;
					});
					merged = convertedEmails.concat(tail);
				} else {
					// Append mode (default): keep existing, then add any new ones not already present.
					merged = current.slice();
					convertedEmails.forEach(function(email) {
						if (merged.indexOf(email) === -1) {
							merged.push(email);
						}
					});
				}

				// Respect select2 / wc-customer-search fields without replacing
				// WooCommerce's native field or search behavior.
				if ($field.hasClass('wc-customer-search') || $field.data('select2')) {
					merged.forEach(function(email) {
						if ($field.find('option[value="' + email.replace(/"/g, '\\"') + '"]').length === 0) {
							$field.append(new Option(email, email, false, false));
						}
					});
					$field.val(merged).trigger('change');
					alert('Emails applied! Make sure to save the coupon.');
					return;
				}

				$field.val(merged.join(', ')).trigger('change');
				alert('Emails applied! Make sure to save the coupon.');
			}

			$('#um_apply_emails').on('click', function() {
				if (window.confirm('This will replace all existing Allowed Emails with the new list. Continue?')) {
					applyEmails('replace');
				}
			});

			$('#um_prepend_emails').on('click', function() {
				applyEmails('prepend');
			});

			$('#um_append_emails').on('click', function() {
				applyEmails('append');
			});
		});
		</script>
		<?php
	}

	/**
	 * Add "Assigned Emails" column to coupon list.
	 */
	public static function add_coupon_email_column(array $columns): array {
		// If another plugin has already renamed the Date column to "Assigned Emails",
		// restore it back to "Date" so we can provide a dedicated Assigned Emails column.
		if (isset($columns['date']) && $columns['date'] === __('Assigned Emails', 'user-manager')) {
			$columns['date'] = __('Date', 'default');
		}

		// Avoid duplicating our column if it already exists.
		if (isset($columns['um_coupon_emails'])) {
			return $columns;
		}

		$new_columns = [];

		// Insert our column immediately after the Usage / Limit column when present,
		// otherwise append it to the end of the list.
		foreach ($columns as $key => $label) {
			$new_columns[$key] = $label;

			if ($key === 'usage' || $key === 'usage_limit') {
				$new_columns['um_coupon_emails'] = __('Assigned Emails', 'user-manager');
			}
		}

		if (!isset($new_columns['um_coupon_emails'])) {
			$new_columns['um_coupon_emails'] = __('Assigned Emails', 'user-manager');
		}

		return $new_columns;
	}

	/**
	 * Add Quick Search icon to the WordPress admin bar.
	 *
	 * Based on Life Brand AI's wp-admin-top-bar-search implementation.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function add_quick_search_admin_bar_item($wp_admin_bar): void {
		if (!current_user_can('manage_options')) {
			return;
		}

		$title = '<span class="ab-icon dashicons dashicons-search"></span><span class="ab-label">Search</span>';

		$wp_admin_bar->add_node([
			'id'     => 'um-admin-search',
			'title'  => $title,
			'href'   => '#',
			'parent' => 'top-secondary',
			'meta'   => [
				'title' => __('Quick Search', 'user-manager'),
				'class' => 'um-admin-search-menu menupop',
			],
		]);
	}

	/**
	 * Output WP-Admin CSS in admin head based on settings (all roles, excluded roles, user-based, role-based).
	 */
	public static function inject_wp_admin_css(): void {
		if (!is_user_logged_in()) {
			return;
		}
		$settings = get_option(self::OPTION_KEY, []);
		if (!self::is_wp_admin_css_addon_enabled($settings)) {
			return;
		}
		$user = wp_get_current_user();
		if (!$user->ID) {
			return;
		}
		$user_roles = (array) $user->roles;
		$exclude_roles = isset($settings['wp_admin_css_exclude_roles']) && is_array($settings['wp_admin_css_exclude_roles']) ? $settings['wp_admin_css_exclude_roles'] : [];
		$users_include = isset($settings['wp_admin_css_users_include']) && is_array($settings['wp_admin_css_users_include']) ? $settings['wp_admin_css_users_include'] : [];
		$roles_css = isset($settings['wp_admin_css_roles']) && is_array($settings['wp_admin_css_roles']) ? $settings['wp_admin_css_roles'] : [];

		$to_output = [];
		$hide_admin_chrome_applied = false;

		// All roles CSS (unless user's role is in exclude list)
		$css_all = isset($settings['wp_admin_css_all']) ? trim((string) $settings['wp_admin_css_all']) : '';
		if ($css_all !== '') {
			$excluded = false;
			foreach ($user_roles as $r) {
				if (in_array($r, $exclude_roles, true)) {
					$excluded = true;
					break;
				}
			}
			if (!$excluded) {
				$to_output[] = $css_all;
			}
		}

		// User-based CSS (only if current user is in the include list)
		$css_users = isset($settings['wp_admin_css_users_css']) ? trim((string) $settings['wp_admin_css_users_css']) : '';
		if ($css_users !== '' && !empty($users_include)) {
			$matched = false;
			foreach ($users_include as $identifier) {
				$identifier = trim($identifier);
				if ($identifier === '') {
					continue;
				}
				if (is_numeric($identifier) && (int) $identifier === (int) $user->ID) {
					$matched = true;
					break;
				}
				if (strtolower($identifier) === strtolower($user->user_login) || strtolower($identifier) === strtolower($user->user_email)) {
					$matched = true;
					break;
				}
			}
			if ($matched) {
				$to_output[] = $css_users;
			}
		}

		// Role-based CSS (for each role the user has, add that role's CSS once)
		foreach ($user_roles as $role_key) {
			if (isset($roles_css[$role_key]) && trim((string) $roles_css[$role_key]) !== '') {
				$to_output[] = trim((string) $roles_css[$role_key]);
			}
		}

		// Preset CSS: hide admin sidebar + most top-bar items for targeted users/roles.
		$hide_admin_chrome_enabled = !empty($settings['wp_admin_css_hide_admin_chrome_enabled']);
		if ($hide_admin_chrome_enabled) {
			$hide_users = [];
			if (isset($settings['wp_admin_css_hide_admin_chrome_users_include'])) {
				if (is_array($settings['wp_admin_css_hide_admin_chrome_users_include'])) {
					$hide_users = $settings['wp_admin_css_hide_admin_chrome_users_include'];
				} else {
					$hide_users = array_filter(array_map('trim', explode(',', (string) $settings['wp_admin_css_hide_admin_chrome_users_include'])));
				}
			}
			$hide_roles = isset($settings['wp_admin_css_hide_admin_chrome_roles']) && is_array($settings['wp_admin_css_hide_admin_chrome_roles'])
				? $settings['wp_admin_css_hide_admin_chrome_roles']
				: [];

			if (self::wp_admin_css_user_matches_targets($user, $user_roles, $hide_users, $hide_roles)) {
				$to_output[] = self::get_wp_admin_css_hide_admin_chrome_preset();
				$hide_admin_chrome_applied = true;
			}
		}

		if (empty($to_output)) {
			return;
		}

		$combined = implode("\n", $to_output);
		$combined = str_replace(['</style>', '<script'], '', $combined);
		echo '<style id="um-wp-admin-css">' . "\n" . $combined . "\n" . '</style>' . "\n";
		if (is_admin_bar_showing()) {
			echo '<style id="um-wp-admin-bar-color-safeguard">' . "\n" . self::get_wp_admin_bar_color_safeguard_css() . "\n" . '</style>' . "\n";
		}
		if ($hide_admin_chrome_applied) {
			self::render_wp_admin_css_hide_admin_chrome_script();
		}
	}

	/**
	 * Match current user by provided usernames/emails and roles (OR logic).
	 *
	 * @param WP_User $user Current user object.
	 * @param array   $user_roles Current user roles.
	 * @param array   $target_users Usernames/emails list.
	 * @param array   $target_roles Roles list.
	 * @return bool
	 */
	private static function wp_admin_css_user_matches_targets($user, array $user_roles, array $target_users, array $target_roles): bool {
		$target_users = array_values(array_filter(array_map('trim', $target_users)));
		$target_roles = array_values(array_filter(array_map('sanitize_key', $target_roles)));

		if (empty($target_users) && empty($target_roles)) {
			return false;
		}

		$user_login = strtolower((string) ($user->user_login ?? ''));
		$user_email = strtolower((string) ($user->user_email ?? ''));

		foreach ($target_users as $identifier) {
			$identifier = strtolower((string) $identifier);
			if ($identifier === '') {
				continue;
			}
			if ($identifier === $user_login || $identifier === $user_email) {
				return true;
			}
		}

		foreach ($user_roles as $role) {
			if (in_array(sanitize_key((string) $role), $target_roles, true)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Preset CSS that minimizes admin UI chrome while keeping profile/logout and custom menu items.
	 */
	private static function get_wp_admin_css_hide_admin_chrome_preset(): string {
		return '
html body #wpadminbar .ab-top-menu > li,
html body .woocommerce-layout__header,
html body #adminmenu,
html body #adminmenumain,
html body #adminmenuwrap,
html body #adminmenuback,
html body .updated,
html body .notice,
html body .noticex,
html body .error,
html body #dashboard-widgets-wrap,
html body #message,
html body #screen-meta-links,
html body #screen-meta,
html body #contextual-help-link-wrap,
html body #screen-options-link-wrap {
	display:none !important;
}

html body #wpadminbar li#wp-admin-bar-my-account,
html body #wpadminbar li[id^="wp-admin-bar-um-custom-bar-"] {
	display:list-item !important;
}

html body #wpadminbar li#wp-admin-bar-my-account .ab-item,
html body #wpadminbar li#wp-admin-bar-my-account .ab-label,
html body #wpadminbar li[id^="wp-admin-bar-um-custom-bar-"] .ab-item,
html body #wpadminbar li[id^="wp-admin-bar-um-custom-bar-"] .ab-label {
	display:block !important;
}

html body #wpadminbar li#wp-admin-bar-my-account > .ab-sub-wrapper,
html body #wpadminbar li[id^="wp-admin-bar-um-custom-bar-"] > .ab-sub-wrapper {
	display:none !important;
}

html body #wpadminbar li#wp-admin-bar-my-account:hover > .ab-sub-wrapper,
html body #wpadminbar li#wp-admin-bar-my-account.hover > .ab-sub-wrapper,
html body #wpadminbar li#wp-admin-bar-my-account:focus-within > .ab-sub-wrapper,
html body #wpadminbar li[id^="wp-admin-bar-um-custom-bar-"]:hover > .ab-sub-wrapper,
html body #wpadminbar li[id^="wp-admin-bar-um-custom-bar-"].hover > .ab-sub-wrapper,
html body #wpadminbar li[id^="wp-admin-bar-um-custom-bar-"]:focus-within > .ab-sub-wrapper {
	display:block !important;
}

html body #wpcontent,
html body #wpfooter,
html body #wpbody-content {
	margin-left: 0 !important;
}

html body .woocommerce-layout__header {
	width: 100% !important;
}
';
	}

	/**
	 * Keep admin bar link colors aligned with WordPress defaults.
	 *
	 * This prevents broad custom CSS rules (for example, global a{color:...})
	 * from unintentionally changing wp-admin bar link colors.
	 */
	private static function get_wp_admin_bar_color_safeguard_css(): string {
		return '
#wpadminbar a.ab-item,
#wpadminbar .ab-label,
#wpadminbar .ab-sub-wrapper a,
#wpadminbar .ab-sub-wrapper a .ab-label {
	color: #f0f0f1 !important;
}

#wpadminbar a.ab-item:hover,
#wpadminbar a.ab-item:focus,
#wpadminbar .ab-sub-wrapper a:hover,
#wpadminbar .ab-sub-wrapper a:focus,
#wpadminbar .quicklinks .menupop ul li:hover > a,
#wpadminbar .quicklinks .menupop ul li.hover > a {
	color: #72aee6 !important;
}
';
	}

	/**
	 * JavaScript fallback to hide admin chrome for dynamically rendered admin UI.
	 */
	private static function render_wp_admin_css_hide_admin_chrome_script(): void {
		$hide_selectors = [
			'#adminmenu',
			'#adminmenumain',
			'#adminmenuwrap',
			'#adminmenuback',
			'.woocommerce-layout__header',
			'.updated',
			'.notice',
			'.noticex',
			'.error',
			'#dashboard-widgets-wrap',
			'#message',
			'#screen-meta-links',
			'#screen-meta',
			'#contextual-help-link-wrap',
			'#screen-options-link-wrap',
		];
		$show_selectors = [
			'#wpadminbar li#wp-admin-bar-my-account',
			'#wpadminbar li[id^="wp-admin-bar-um-custom-bar-"]',
		];
		?>
		<script id="um-wp-admin-css-hide-admin-chrome-fallback">
		(function() {
			var hideSelectors = <?php echo wp_json_encode($hide_selectors); ?>;
			var showSelectors = <?php echo wp_json_encode($show_selectors); ?>;
			function getDirectSubWrapper(node) {
				if (!node || !node.children) {
					return null;
				}
				for (var i = 0; i < node.children.length; i++) {
					var child = node.children[i];
					if (child && child.classList && child.classList.contains('ab-sub-wrapper')) {
						return child;
					}
				}
				return null;
			}
			function applyHidePreset() {
				hideSelectors.forEach(function(selector) {
					document.querySelectorAll(selector).forEach(function(node) {
						node.style.setProperty('display', 'none', 'important');
					});
				});
				showSelectors.forEach(function(selector) {
					document.querySelectorAll(selector).forEach(function(node) {
						node.style.setProperty('display', 'list-item', 'important');
						var sub = getDirectSubWrapper(node);
						if (sub) {
							sub.style.setProperty('display', 'none', 'important');
						}
					});
				});
				document.querySelectorAll('#wpcontent, #wpfooter, #wpbody-content').forEach(function(node) {
					node.style.setProperty('margin-left', '0', 'important');
				});

				showSelectors.forEach(function(selector) {
					document.querySelectorAll(selector).forEach(function(node) {
						if (node.dataset.umHoverBind === '1') {
							return;
						}
						node.dataset.umHoverBind = '1';
						var sub = getDirectSubWrapper(node);
						if (!sub) {
							return;
						}
						node.addEventListener('mouseenter', function() {
							sub.style.setProperty('display', 'block', 'important');
						});
						node.addEventListener('mouseleave', function() {
							sub.style.setProperty('display', 'none', 'important');
						});
						node.addEventListener('focusin', function() {
							sub.style.setProperty('display', 'block', 'important');
						});
						node.addEventListener('focusout', function() {
							if (node.contains(document.activeElement)) {
								return;
							}
							sub.style.setProperty('display', 'none', 'important');
						});
					});
				});
			}
			document.addEventListener('DOMContentLoaded', function() {
				applyHidePreset();
				setTimeout(applyHidePreset, 150);
				setTimeout(applyHidePreset, 800);
			});
		})();
		</script>
		<?php
	}

	/**
	 * Determine whether WP-Admin CSS add-on is enabled.
	 * Falls back to legacy behavior when no explicit toggle is stored.
	 *
	 * @param array $settings Plugin settings.
	 * @return bool
	 */
	private static function is_wp_admin_css_addon_enabled(array $settings): bool {
		if (self::is_addon_temporarily_disabled('wp-admin-css')) {
			return false;
		}
		if (array_key_exists('wp_admin_css_enabled', $settings)) {
			return !empty($settings['wp_admin_css_enabled']);
		}

		$roles_css = isset($settings['wp_admin_css_roles']) && is_array($settings['wp_admin_css_roles']) ? $settings['wp_admin_css_roles'] : [];
		foreach ($roles_css as $css) {
			if (trim((string) $css) !== '') {
				return true;
			}
		}

		return trim((string) ($settings['wp_admin_css_all'] ?? '')) !== ''
			|| trim((string) ($settings['wp_admin_css_users_css'] ?? '')) !== ''
			|| !empty($settings['wp_admin_css_hide_admin_chrome_enabled']);
	}

	/**
	 * Add "User Experience Manager" link to the wp-admin top bar (links to plugin Add-ons tab).
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function add_user_manager_admin_bar_link($wp_admin_bar): void {
		if (!current_user_can('manage_options')) {
			return;
		}
		$settings = self::get_settings();
		if (empty($settings['show_user_manager_admin_bar_link'])) {
			return;
		}
		$wp_admin_bar->add_node([
			'id'     => 'user-manager-settings',
			'title'  => __('User Experience Manager', 'user-manager'),
			'href'   => self::get_page_url(self::TAB_ADDONS),
			'parent' => 'top-secondary',
			'meta'   => [
				'title' => __('User Experience Manager Add-ons', 'user-manager'),
			],
		]);
	}

	/**
	 * Add custom admin bar menu items from Settings > WP-Admin Bar Menu Items.
	 * Each saved item is a dropdown; shortcuts are parsed from "Label|URL" or "Label|divider" per line.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function add_custom_admin_bar_menu_items($wp_admin_bar): void {
		$settings   = get_option(self::OPTION_KEY, []);
		if (!self::is_admin_bar_menu_items_addon_enabled($settings)) {
			return;
		}
		$menu_items = isset($settings['admin_bar_menu_items']) && is_array($settings['admin_bar_menu_items']) ? $settings['admin_bar_menu_items'] : [];
		if (empty($menu_items)) {
			return;
		}

		foreach ($menu_items as $i => $item) {
			$menu_title = isset($item['title']) ? trim((string) $item['title']) : '';
			if ($menu_title === '') {
				continue;
			}
			$shortcuts = isset($item['shortcuts']) ? (string) $item['shortcuts'] : '';
			$icon      = isset($item['icon']) ? trim((string) $item['icon']) : '';
			$side      = isset($item['side']) && $item['side'] === 'left' ? 'left' : 'right';
			$parent    = $side === 'left' ? 'root-default' : 'top-secondary';

			$parent_id = 'um-custom-bar-' . $i;
			$title_markup = $menu_title;
			if ($icon !== '' && strpos($icon, 'dashicons-') === 0) {
				$title_markup = '<span class="ab-icon dashicons ' . esc_attr($icon) . '"></span><span class="ab-label">' . esc_html($menu_title) . '</span>';
			}

			$wp_admin_bar->add_node([
				'id'     => $parent_id,
				'title'  => $title_markup,
				'href'   => '#',
				'parent' => $parent,
				'meta'   => [
					'title' => $menu_title,
					'class' => 'um-custom-bar-menu menupop',
				],
			]);

			$lines = array_filter(array_map('trim', explode("\n", $shortcuts)));
			$child_index = 0;
			foreach ($lines as $line) {
				$pipe = strpos($line, '|');
				if ($pipe === false) {
					continue;
				}
				$label = trim(substr($line, 0, $pipe));
				$url   = trim(substr($line, $pipe + 1));
				if ($label === '') {
					continue;
				}
				$child_id = $parent_id . '-' . $child_index;
				$child_index++;

				if (strtolower($url) === 'divider') {
					$wp_admin_bar->add_node([
						'id'     => $child_id,
						'title'  => esc_html($label),
						'href'   => '#',
						'parent' => $parent_id,
						'meta'   => ['class' => 'um-ab-group-header'],
					]);
				} else {
					$wp_admin_bar->add_node([
						'id'     => $child_id,
						'title'  => esc_html($label),
						'href'   => esc_url($url),
						'parent' => $parent_id,
					]);
				}
			}
		}
	}

	/**
	 * Determine whether WP-Admin Bar Menu Items add-on is enabled.
	 * Falls back to legacy behavior when no explicit toggle is stored.
	 *
	 * @param array $settings Plugin settings.
	 * @return bool
	 */
	private static function is_admin_bar_menu_items_addon_enabled(array $settings): bool {
		if (self::is_addon_temporarily_disabled('wp-admin-bar-menu-items')) {
			return false;
		}
		if (array_key_exists('admin_bar_menu_items_enabled', $settings)) {
			return !empty($settings['admin_bar_menu_items_enabled']);
		}

		$menu_items = isset($settings['admin_bar_menu_items']) && is_array($settings['admin_bar_menu_items']) ? $settings['admin_bar_menu_items'] : [];
		foreach ($menu_items as $item) {
			if (!is_array($item)) {
				continue;
			}
			$title = trim((string) ($item['title'] ?? ''));
			$shortcuts = trim((string) ($item['shortcuts'] ?? ''));
			if ($title !== '' || $shortcuts !== '') {
				return true;
			}
		}

		return false;
	}

	/**
	 * Render the Quick Search dropdown markup, styles, and behavior.
	 */
	public static function render_quick_search_dropdown(): void {
		if (!current_user_can('manage_options')) {
			return;
		}

		$settings = self::get_settings();
		// Build one search field per active post type (show_ui => true) on this site.
		$post_types     = get_post_types(['show_ui' => true], 'objects');
		$post_types     = is_array($post_types) ? $post_types : [];
		$excluded_types = ['attachment']; // Media is handled via its own screen.
		$priority_post_types = [];
		if (!empty($settings['um_quick_search_priority_post_types']) && is_array($settings['um_quick_search_priority_post_types'])) {
			$priority_post_types = array_values(array_unique(array_map('sanitize_key', $settings['um_quick_search_priority_post_types'])));
		}
		$priority_rank = array_flip($priority_post_types);

		// Always include a Users search first.
		$users_item = [
			'label'       => __('Users', 'user-manager'),
			'type'        => 'users',
			'post_type'   => '',
			'placeholder' => __('Users', 'user-manager'),
		];
		$post_type_items = [];
		foreach ($post_types as $post_type) {
			if (!is_object($post_type) || empty($post_type->name)) {
				continue;
			}
			$post_type_slug = sanitize_key((string) $post_type->name);
			if ($post_type_slug === '' || in_array($post_type_slug, $excluded_types, true)) {
				continue;
			}

			$label = isset($post_type->labels->name) ? (string) $post_type->labels->name : $post_type_slug;

			// Provide friendlier labels for some common core / WooCommerce types.
			if ($post_type_slug === 'shop_order') {
				$label = __('Orders', 'user-manager');
			} elseif ($post_type_slug === 'product') {
				$label = __('Products', 'user-manager');
			}

			$item = [
				'label'       => $label,
				'type'        => 'post_type',
				'post_type'   => $post_type_slug,
				'placeholder' => $label,
				'_sort_group' => array_key_exists($post_type_slug, $priority_rank) ? 0 : 1,
				'_sort_rank'  => array_key_exists($post_type_slug, $priority_rank) ? (int) $priority_rank[$post_type_slug] : PHP_INT_MAX,
			];
			$post_type_items[] = $item;
		}
		usort($post_type_items, static function ($a, $b) use ($priority_rank) {
			$a_group = isset($a['_sort_group']) ? (int) $a['_sort_group'] : 1;
			$b_group = isset($b['_sort_group']) ? (int) $b['_sort_group'] : 1;
			if ($a_group !== $b_group) {
				return $a_group <=> $b_group;
			}
			if ($a_group === 0) {
				$a_rank = isset($a['_sort_rank']) ? (int) $a['_sort_rank'] : PHP_INT_MAX;
				$b_rank = isset($b['_sort_rank']) ? (int) $b['_sort_rank'] : PHP_INT_MAX;
				if ($a_rank !== $b_rank) {
					return $a_rank <=> $b_rank;
				}
			}
			return strcasecmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
		});

		foreach ($post_type_items as &$post_type_item) {
			unset($post_type_item['_sort_group'], $post_type_item['_sort_rank']);
		}
		unset($post_type_item);

		$search_items = array_merge([$users_item], $post_type_items);

		?>
		<div id="um-admin-search-dropdown" style="display: none; position: fixed; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 999999; min-width: 320px; max-width: 400px; max-height: 80vh; overflow-y: auto;">
			<div style="padding: 10px;">
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
					<?php foreach ($search_items as $item) : ?>
						<div>
							<input
								type="text"
								class="um-admin-search-input"
								data-type="<?php echo esc_attr($item['type']); ?>"
								<?php if (!empty($item['post_type'])) : ?>
									data-post-type="<?php echo esc_attr($item['post_type']); ?>"
								<?php endif; ?>
								data-label="<?php echo esc_attr($item['label']); ?>"
								placeholder="<?php echo esc_attr($item['placeholder']); ?>"
								style="width: 100%; padding: 5px 7px; font-size: 11px; border: 1px solid #ddd; border-radius: 3px;"
								autocomplete="off"
							/>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<style>
			#wpadminbar #wp-admin-bar-um-admin-search {
				cursor: pointer;
			}
			#wp-admin-bar-um-admin-search .ab-icon {
				display: inline-block;
				width: 26px;
				height: 30px;
				line-height: 30px;
				text-align: center;
				font-size: 20px;
				margin-right: 0;
			}
			#wp-admin-bar-um-admin-search .ab-icon:before {
				display: inline-block;
				vertical-align: middle;
			}
			@media screen and (min-width: 783px) {
				#wp-admin-bar-um-admin-search .ab-icon {
					margin-top: -5px;
				}
			}
			@media screen and (max-width: 782px) {
				#wp-admin-bar-um-admin-search {
					display: list-item !important;
				}
				#wp-admin-bar-um-admin-search > .ab-item {
					display: flex !important;
					align-items: center;
					padding: 0 10px;
				}
				#wp-admin-bar-um-admin-search .ab-icon {
					display: inline-block !important;
					width: 37px;
					height: 37px;
					line-height: 37px;
					font-size: 16px;
					margin-top: -15px;
				}
				#wp-admin-bar-um-admin-search .ab-label {
					display: none !important;
				}
				#wp-admin-bar-top-secondary #wp-admin-bar-um-admin-search {
					display: list-item !important;
				}
			}
			#um-admin-search-dropdown {
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
			.um-admin-search-input:focus {
				outline: none;
				border-color: #2271b1;
				box-shadow: 0 0 0 1px #2271b1;
			}
			.um-admin-search-input::placeholder {
				color: #1d2327;
				opacity: 1;
			}
			#um-admin-search-dropdown::-webkit-scrollbar {
				width: 6px;
			}
			#um-admin-search-dropdown::-webkit-scrollbar-track {
				background: #f1f1f1;
			}
			#um-admin-search-dropdown::-webkit-scrollbar-thumb {
				background: #888;
				border-radius: 3px;
			}
			#um-admin-search-dropdown::-webkit-scrollbar-thumb:hover {
				background: #555;
			}
		</style>

		<script>
		(function() {
			var searchMenu = null;
			var dropdown = null;
			var searchInputs = null;
			var isHovering = false;
			var hideTimeout = null;
			var hoverInitialized = false;

			function initHoverFunctionality() {
				if (hoverInitialized) {
					return true;
				}

				searchMenu = document.getElementById('wp-admin-bar-um-admin-search');
				dropdown = document.getElementById('um-admin-search-dropdown');
				searchInputs = document.querySelectorAll('.um-admin-search-input');

				if (!searchMenu || !dropdown) {
					return false;
				}

				hoverInitialized = true;

				function positionDropdown() {
					var menuRect = searchMenu.getBoundingClientRect();
					var dropdownWidth = dropdown.offsetWidth || 320;
					var rightPos = window.innerWidth - menuRect.right;

					if (rightPos + dropdownWidth > window.innerWidth) {
						rightPos = window.innerWidth - dropdownWidth - 10;
					}

					dropdown.style.right = rightPos + 'px';
					dropdown.style.top = (menuRect.bottom + 2) + 'px';
				}

				searchMenu.addEventListener('mouseenter', function() {
					clearTimeout(hideTimeout);
					isHovering = true;
					dropdown.style.display = 'block';
					setTimeout(positionDropdown, 10);
				});

				dropdown.addEventListener('mouseenter', function() {
					clearTimeout(hideTimeout);
					isHovering = true;
				});

				[searchMenu, dropdown].forEach(function(el) {
					el.addEventListener('mouseleave', function() {
						isHovering = false;
						hideTimeout = setTimeout(function() {
							if (!isHovering) {
								dropdown.style.display = 'none';
							}
						}, 150);
					});
				});

				window.addEventListener('resize', function() {
					if (dropdown && dropdown.style.display === 'block') {
						positionDropdown();
					}
				});

				searchInputs.forEach(function(input) {
					input.addEventListener('keypress', function(e) {
						if (e.key === 'Enter') {
							e.preventDefault();
							var searchTerm = this.value.trim();
							if (!searchTerm) {
								return;
							}

							var type = this.getAttribute('data-type');
							var postType = this.getAttribute('data-post-type');
							var url = '';

							if (type === 'post_type' && postType) {
								url = '<?php echo esc_js(admin_url('edit.php')); ?>?post_type=' + postType + '&s=' + encodeURIComponent(searchTerm);
							} else if (type === 'users') {
								url = '<?php echo esc_js(admin_url('users.php')); ?>?s=' + encodeURIComponent(searchTerm);
							} else if (type === 'product_cat') {
								url = '<?php echo esc_js(admin_url('edit-tags.php')); ?>?taxonomy=product_cat&s=' + encodeURIComponent(searchTerm);
							} else if (type === 'attachment') {
								url = '<?php echo esc_js(admin_url('upload.php')); ?>?s=' + encodeURIComponent(searchTerm);
							}

							if (url) {
								url += '&um_quick_search=1';
								window.open(url, '_blank');
							}
						}
					});
				});

				return true;
			}

			function ensureAdminSearchVisible() {
				var adminSearch = document.getElementById('wp-admin-bar-um-admin-search');
				if (adminSearch) {
					adminSearch.style.display = 'list-item';
					adminSearch.style.visibility = 'visible';

					var parent = adminSearch.parentElement;
					if (parent) {
						parent.style.display = '';
						parent.style.visibility = 'visible';
					}

					var link = adminSearch.querySelector('.ab-item');
					if (link) {
						link.style.display = 'block';
						link.style.visibility = 'visible';
					}
				}

				initHoverFunctionality();
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', function() {
					ensureAdminSearchVisible();
					setTimeout(ensureAdminSearchVisible, 100);
					setTimeout(ensureAdminSearchVisible, 500);
				});
			} else {
				ensureAdminSearchVisible();
				setTimeout(ensureAdminSearchVisible, 100);
				setTimeout(ensureAdminSearchVisible, 500);
			}

			window.addEventListener('resize', ensureAdminSearchVisible);

			var adminBarCheckInterval = setInterval(function() {
				if (initHoverFunctionality()) {
					clearInterval(adminBarCheckInterval);
				}
			}, 100);

			setTimeout(function() {
				clearInterval(adminBarCheckInterval);
			}, 5000);
		})();
		</script>
		<?php
	}

	/**
	 * When a quick search for posts is triggered and only one result is found,
	 * redirect directly to the edit screen.
	 */
	public static function quick_search_maybe_redirect_single_post(): void {
		if (empty($_GET['um_quick_search']) || empty($_GET['s'])) {
			return;
		}

		$search    = sanitize_text_field(wp_unslash($_GET['s']));
		$post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : 'post';

		$args = [
			'post_type'      => $post_type,
			's'              => $search,
			'posts_per_page' => 2,
			'fields'         => 'ids',
			'post_status'    => 'any',
		];

		$query = new WP_Query($args);
		if ($query->post_count === 1 && !empty($query->posts[0])) {
			$post_id = (int) $query->posts[0];
			wp_safe_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
			exit;
		}
	}

	/**
	 * When a quick search for users is triggered and only one result is found,
	 * redirect directly to the user edit screen.
	 */
	public static function quick_search_maybe_redirect_single_user(): void {
		if (empty($_GET['um_quick_search']) || empty($_GET['s'])) {
			return;
		}

		$search = sanitize_text_field(wp_unslash($_GET['s']));

		$users = get_users([
			'search'         => '*' . $search . '*',
			'search_columns' => ['user_login', 'user_email', 'display_name'],
			'number'         => 2,
			'fields'         => ['ID'],
		]);

		if (count($users) === 1 && !empty($users[0]->ID)) {
			$user_id = (int) $users[0]->ID;
			wp_safe_redirect(admin_url('user-edit.php?user_id=' . $user_id));
			exit;
		}
	}

	/**
	 * When a quick search for terms is triggered and only one result is found,
	 * redirect directly to the term edit screen.
	 */
	public static function quick_search_maybe_redirect_single_term(): void {
		if (empty($_GET['um_quick_search']) || empty($_GET['s'])) {
			return;
		}

		$search   = sanitize_text_field(wp_unslash($_GET['s']));
		$taxonomy = isset($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';
		if (!$taxonomy) {
			return;
		}

		$terms = get_terms([
			'taxonomy'   => $taxonomy,
			'search'     => $search,
			'number'     => 2,
			'hide_empty' => false,
			'fields'     => 'ids',
		]);

		if (!is_wp_error($terms) && count($terms) === 1) {
			$first_term = $terms[0];
			if ($first_term instanceof WP_Term) {
				$term_id = absint($first_term->term_id);
			} else {
				$term_id = absint($first_term);
			}
			if ($term_id <= 0) {
				return;
			}
			$url     = add_query_arg(
				[
					'action'   => 'edit',
					'taxonomy' => $taxonomy,
					'tag_ID'   => $term_id,
				],
				admin_url('edit-tags.php')
			);
			wp_safe_redirect($url);
			exit;
		}
	}

	/**
	 * Render Assigned Emails column content.
	 */
	public static function render_coupon_email_column(string $column, int $post_id): void {
		if ('um_coupon_emails' !== $column) {
			return;
		}
		$emails = [];
		
		// Get emails from customer_email meta (WooCommerce standard)
		$customer_email = get_post_meta($post_id, 'customer_email', true);
		if (!empty($customer_email)) {
			if (is_array($customer_email)) {
				$emails = array_merge($emails, $customer_email);
			} else {
				// Handle comma-separated string
				$emails = array_merge($emails, array_map('trim', explode(',', $customer_email)));
			}
		}
		
		// Get emails from WooCommerce coupon email restrictions
		if (class_exists('WC_Coupon')) {
			$coupon = new WC_Coupon($post_id);
			if (method_exists($coupon, 'get_email_restrictions')) {
				$restrictions = $coupon->get_email_restrictions('edit');
				if (!empty($restrictions) && is_array($restrictions)) {
					$emails = array_merge($emails, $restrictions);
				}
			}
		}
		
		// Get email from User Manager meta (_um_user_coupon_user_email)
		$um_email = get_post_meta($post_id, '_um_user_coupon_user_email', true);
		if (!empty($um_email)) {
			$emails[] = $um_email;
		}

		// Remove duplicates and empty values
		$emails = array_unique(array_filter(array_map('trim', $emails)));

		if (empty($emails)) {
			echo '<span style="color:#646970;">' . esc_html__('—', 'user-manager') . '</span>';
			return;
		}

		if (!is_array($emails)) {
			$emails = maybe_unserialize($emails);
		}
		if (!is_array($emails)) {
			$emails = array_filter(array_map('trim', explode(',', (string) $emails)));
		}
		$emails = array_unique(array_filter($emails));

		if (empty($emails)) {
			echo '<span style="color:#646970;">' . esc_html__('—', 'user-manager') . '</span>';
			return;
		}

		$display = array_slice($emails, 0, 3);
		echo esc_html(implode(', ', $display));
		$remaining = count($emails) - count($display);
		if ($remaining > 0) {
			printf(
				' <span class="description">+%d</span>',
				$remaining
			);
		}
	}

	/**
	 * Shortcode handler for [bulk_add_to_cart].
	 *
	 * Renders the CSV upload form and optional debug block.
	 */
	public static function bulk_add_to_cart_shortcode(): string {
		if (!class_exists('WooCommerce')) {
			return '<p>' . esc_html__('WooCommerce is required to use the bulk add to cart feature.', 'user-manager') . '</p>';
		}
		if (!is_user_logged_in()) {
			return '<p>' . esc_html__('Please log in to use the bulk add to cart feature.', 'user-manager') . '</p>';
		}

		$options           = get_option('bulk_add_to_cart_settings', []);
		$identifier_column = isset($options['identifier_column']) ? (string) $options['identifier_column'] : 'product_id';
		$identifier_type   = isset($options['identifier_type']) ? (string) $options['identifier_type'] : 'product_id';
		$product_id_column = self::bulk_add_to_cart_get_product_id_column_header($options);
		$product_id_column_header = $product_id_column;
		$sku_column        = self::bulk_add_to_cart_get_sku_column_header($options);
		$quantity_column   = isset($options['quantity_column']) ? (string) $options['quantity_column'] : 'quantity';
		$hide_product_id_column = isset($options['hide_product_id_column']) && (string) $options['hide_product_id_column'] === '1';
		$hide_sku_column = isset($options['hide_sku_column']) && (string) $options['hide_sku_column'] === '1';
		$show_sample_csv = !array_key_exists('show_sample_csv', $options) || (string) ($options['show_sample_csv'] ?? '1') === '1';
		$show_sample_with_data = !array_key_exists('show_sample_with_product_data', $options) || (string) ($options['show_sample_with_product_data'] ?? '1') === '1';
		$force_debug       = self::is_bulk_add_to_cart_debug_requested();
		$debug_enabled     = (isset($options['debug_mode']) && (string) $options['debug_mode'] === '1') || $force_debug;
		$sample_csv_url    = add_query_arg('um_bulk_add_to_cart_sample', '1', remove_query_arg(['um_bulk_add_to_cart_sample', 'um_bulk_add_to_cart_sample_data']));
		$sample_with_data_url = add_query_arg('um_bulk_add_to_cart_sample_data', '1', remove_query_arg(['um_bulk_add_to_cart_sample', 'um_bulk_add_to_cart_sample_data']));
		$form_action_url   = esc_url($_SERVER['REQUEST_URI'] ?? '');

		$output = '<div class="bulk-add-to-cart-form" style="max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
		$debug_notice_lines = [];
		if (function_exists('wc_get_notices') && function_exists('wc_print_notices')) {
			$pending_notices = wc_get_notices();
			$submitted = isset($_POST['bulk_add_to_cart_submit']);
			if (!empty($pending_notices)) {
				if ($submitted && $debug_enabled) {
					$notices_for_display = [];
					$notices_for_debug   = [];
					foreach ($pending_notices as $type => $bucket) {
						if (!is_array($bucket)) {
							continue;
						}
						foreach ($bucket as $notice) {
							if (!is_array($notice)) {
								continue;
							}
							if (self::bulk_add_to_cart_notice_should_remain_visible($notice)) {
								if (!isset($notices_for_display[$type]) || !is_array($notices_for_display[$type])) {
									$notices_for_display[$type] = [];
								}
								$notices_for_display[$type][] = $notice;
							} else {
								if (!isset($notices_for_debug[$type]) || !is_array($notices_for_debug[$type])) {
									$notices_for_debug[$type] = [];
								}
								$notices_for_debug[$type][] = $notice;
							}
						}
					}
					$debug_notice_lines = self::bulk_add_to_cart_flatten_notices_for_debug($notices_for_debug);
					if (function_exists('wc_clear_notices')) {
						wc_clear_notices();
					}
					foreach ($notices_for_display as $type => $bucket) {
						if (!is_array($bucket)) {
							continue;
						}
						foreach ($bucket as $notice) {
							$message = isset($notice['notice']) ? (string) $notice['notice'] : '';
							if ($message === '') {
								continue;
							}
							$data = isset($notice['data']) && is_array($notice['data']) ? $notice['data'] : [];
							wc_add_notice($message, (string) $type, $data);
						}
					}
					ob_start();
					wc_print_notices();
					$output .= (string) ob_get_clean();
				} else {
					ob_start();
					wc_print_notices();
					$output .= (string) ob_get_clean();
				}
			}
		}

		if (isset($_POST['bulk_add_to_cart_submit']) && $debug_enabled) {
			$output .= '<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #dc3545;">';
			$output .= '<h3 style="margin-top: 0; color: #dc3545;">' . esc_html__('Debug Information', 'user-manager') . '</h3>';

			$output .= '<p><strong>' . esc_html__('Form Submission:', 'user-manager') . '</strong> ';
			$output .= isset($_POST['bulk_add_to_cart_submit']) ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager');
			$output .= '</p>';

			$output .= '<p><strong>' . esc_html__('Nonce Verification:', 'user-manager') . '</strong> ';
			$nonce_ok = isset($_POST['bulk_add_to_cart_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bulk_add_to_cart_nonce'])), 'bulk_add_to_cart_upload');
			$output .= $nonce_ok ? esc_html__('Valid', 'user-manager') : esc_html__('Invalid', 'user-manager');
			$output .= '</p>';

			$output .= '<p><strong>' . esc_html__('File Upload:', 'user-manager') . '</strong> ';
			if (isset($_FILES['csv_file'])) {
				$file_name = isset($_FILES['csv_file']['name']) ? (string) $_FILES['csv_file']['name'] : '';
				$output   .= esc_html__('File received', 'user-manager') . ' (' . esc_html($file_name) . ')';
				if (isset($_FILES['csv_file']['error']) && $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
					$output .= ' - ' . esc_html__('Error: ', 'user-manager') . (int) $_FILES['csv_file']['error'];
				}
			} else {
				$output .= esc_html__('No file received', 'user-manager');
			}
			$output .= '</p>';

			$output .= '<p><strong>' . esc_html__('WooCommerce Cart:', 'user-manager') . '</strong> ';
			$output .= (function_exists('WC') && WC()->cart) ? esc_html__('Initialized', 'user-manager') : esc_html__('Not initialized', 'user-manager');
			$output .= '</p>';
			$output .= '<p><strong>' . esc_html__('Request Method:', 'user-manager') . '</strong> ' . esc_html(isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : '') . '</p>';
			$output .= '<p><strong>' . esc_html__('Form Action URL:', 'user-manager') . '</strong> <code>' . esc_html($form_action_url) . '</code></p>';
			$output .= '<p><strong>' . esc_html__('URL Debug Flag:', 'user-manager') . '</strong> ' . ($force_debug ? esc_html__('Enabled', 'user-manager') : esc_html__('Disabled', 'user-manager')) . '</p>';

			if (isset($_FILES['csv_file']) && is_array($_FILES['csv_file'])) {
				$file_size = isset($_FILES['csv_file']['size']) ? (int) $_FILES['csv_file']['size'] : 0;
				$file_type = isset($_FILES['csv_file']['type']) ? (string) $_FILES['csv_file']['type'] : '';
				$tmp_name  = isset($_FILES['csv_file']['tmp_name']) ? (string) $_FILES['csv_file']['tmp_name'] : '';
				$output .= '<p><strong>' . esc_html__('File Size (bytes):', 'user-manager') . '</strong> ' . esc_html((string) $file_size) . '</p>';
				$output .= '<p><strong>' . esc_html__('File MIME Type:', 'user-manager') . '</strong> ' . esc_html($file_type !== '' ? $file_type : '—') . '</p>';
				$output .= '<p><strong>' . esc_html__('Temp File Exists:', 'user-manager') . '</strong> ' . (is_file($tmp_name) ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager')) . '</p>';
			}

			$output .= '<p><strong>' . esc_html__('Current Settings:', 'user-manager') . '</strong></p>';
			$output .= '<ul>';
			$output .= '<li>' . esc_html__('Identifier Column:', 'user-manager') . ' ' . esc_html($identifier_column) . '</li>';
			$output .= '<li>' . esc_html__('Identifier Type:', 'user-manager') . ' ' . esc_html($identifier_type) . '</li>';
			$output .= '<li>' . esc_html__('Product ID Column Header:', 'user-manager') . ' ' . esc_html($product_id_column) . '</li>';
			$output .= '<li>' . esc_html__('SKU Column Header:', 'user-manager') . ' ' . esc_html($sku_column) . '</li>';
			$output .= '<li>' . esc_html__('Hide Product ID Column in Samples:', 'user-manager') . ' ' . ($hide_product_id_column ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager')) . '</li>';
			$output .= '<li>' . esc_html__('Hide SKU Column in Samples:', 'user-manager') . ' ' . ($hide_sku_column ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager')) . '</li>';
			$output .= '<li>' . esc_html__('Quantity Column:', 'user-manager') . ' ' . esc_html($quantity_column) . '</li>';
			$output .= '</ul>';

			if (!empty(self::$bulk_add_to_cart_media_upload_info)) {
				$media_info = self::$bulk_add_to_cart_media_upload_info;
				$output .= '<p><strong>' . esc_html__('Media Library Upload:', 'user-manager') . '</strong></p>';
				$output .= '<ul>';
				if (!empty($media_info['activity_url'])) {
					$output .= '<li>' . esc_html__('User Activity File URL:', 'user-manager') . ' <a href="' . esc_url((string) $media_info['activity_url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html((string) $media_info['activity_url']) . '</a></li>';
				}
				if (!empty($media_info['attachment_id'])) {
					$output .= '<li>' . esc_html__('Attachment ID:', 'user-manager') . ' ' . esc_html((string) $media_info['attachment_id']) . '</li>';
				}
				if (!empty($media_info['attachment_url'])) {
					$output .= '<li>' . esc_html__('Attachment URL:', 'user-manager') . ' <a href="' . esc_url((string) $media_info['attachment_url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html((string) $media_info['attachment_url']) . '</a></li>';
				}
				if (!empty($media_info['error'])) {
					$output .= '<li>' . esc_html__('Media Upload Error:', 'user-manager') . ' ' . esc_html((string) $media_info['error']) . '</li>';
				}
				$output .= '</ul>';
			}

			if (!empty($debug_notice_lines)) {
				$output .= '<p><strong>' . esc_html__('Upload/Processing Messages:', 'user-manager') . '</strong></p>';
				$output .= '<ul>';
				foreach ($debug_notice_lines as $line) {
					$output .= '<li>' . esc_html($line) . '</li>';
				}
				$output .= '</ul>';
			}

			if (!empty(self::$bulk_add_to_cart_debug_trace)) {
				$output .= '<p><strong>' . esc_html__('Line-by-line Processing Trace:', 'user-manager') . '</strong></p>';
				$output .= '<div style="max-height:320px; overflow:auto; background:#fff; border:1px solid #dcdcde; border-radius:4px;">';
				$output .= '<table class="widefat striped" style="margin:0;">';
				$output .= '<thead><tr><th style="width:90px;">' . esc_html__('CSV Line', 'user-manager') . '</th><th style="width:160px;">' . esc_html__('Status', 'user-manager') . '</th><th>' . esc_html__('Details', 'user-manager') . '</th></tr></thead>';
				$output .= '<tbody>';
				foreach (self::$bulk_add_to_cart_debug_trace as $trace_row) {
					$line_value = isset($trace_row['line']) ? (int) $trace_row['line'] : 0;
					$status_value = isset($trace_row['status']) ? (string) $trace_row['status'] : '';
					$message_value = isset($trace_row['message']) ? (string) $trace_row['message'] : '';
					$output .= '<tr>';
					$output .= '<td>' . ($line_value > 0 ? esc_html((string) $line_value) : '—') . '</td>';
					$output .= '<td><code>' . esc_html($status_value) . '</code></td>';
					$output .= '<td>' . esc_html($message_value) . '</td>';
					$output .= '</tr>';
				}
				$output .= '</tbody></table></div>';
			}

			$output .= '</div>';
		}

		if ($force_debug && !isset($_POST['bulk_add_to_cart_submit'])) {
			$output .= '<div style="margin-bottom: 20px; padding: 12px 15px; background: #fff8e5; border-left: 4px solid #dba617;">';
			$output .= '<strong>' . esc_html__('Bulk Add to Cart debug mode is active via URL parameter.', 'user-manager') . '</strong> ';
			$output .= esc_html__('Submit a CSV to see verbose processing notices.', 'user-manager');
			$output .= '</div>';
		}

		$output .= '<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007cba;">';
		$output .= '<h3 style="margin-top: 0;">' . esc_html__('How to Use', 'user-manager') . '</h3>';
		$output .= '<ol style="margin: 0; padding-left: 20px;">';
		$output .= '<li>' . esc_html__('Prepare a CSV file with these columns (in any order):', 'user-manager') . '</li>';
		$output .= '<ul style="margin: 10px 0;">';
		$output .= '<li>' . sprintf(
			/* translators: %s: product_id fallback column header */
			esc_html__('"%s" - Product ID fallback column (checked automatically if the Product Identifier Column does not match).', 'user-manager'),
			esc_html($product_id_column_header)
		) . '</li>';
		$output .= '<li>' . sprintf(
			/* translators: 1: column name, 2: identifier type */
			esc_html__('"%1$s" - Contains the product %2$s', 'user-manager'),
			esc_html($identifier_column),
			esc_html($identifier_type)
		) . '</li>';
		$output .= '<li>' . sprintf(
			/* translators: 1: column name */
			esc_html__('"%s" - Contains the quantity (required)', 'user-manager'),
			esc_html($quantity_column)
		) . '</li>';
		$output .= '</ul>';

		$label = '';
		switch ($identifier_type) {
			case 'product_sku':
				$label = esc_html__('SKU', 'user-manager');
				break;
			case 'product_slug':
				$label = esc_html__('slug', 'user-manager');
				break;
			case 'product_title':
				$label = esc_html__('title', 'user-manager');
				break;
			case 'meta_field':
				$label = esc_html__('meta field value', 'user-manager');
				break;
			case 'product_id':
			default:
				$label = esc_html__('ID', 'user-manager');
				break;
		}

		$output .= '<li>' . sprintf(
			esc_html__('For variations, use the variation %s', 'user-manager'),
			$label
		) . '</li>';
		$output .= '<li>' . esc_html__('Importer can also identify products using the Product ID and/or _sku columns when those columns are present.', 'user-manager') . '</li>';
		$output .= '<li>' . esc_html__('Rows with blank or 0 quantity are ignored.', 'user-manager') . '</li>';
		$output .= '<li>' . esc_html__('Upload your CSV file and click "Add to Cart"', 'user-manager') . '</li>';
		$output .= '</ol>';
		$output .= '</div>';

		$output .= '<form method="post" enctype="multipart/form-data" action="' . $form_action_url . '">';
		$output .= wp_nonce_field('bulk_add_to_cart_upload', 'bulk_add_to_cart_nonce', true, false);
		$sample_links = [];
		if ($show_sample_csv) {
			$sample_links[] = '<a href="' . esc_url($sample_csv_url) . '">' . esc_html__('Download Sample CSV', 'user-manager') . '</a>';
		}
		if ($show_sample_with_data) {
			$sample_links[] = '<a href="' . esc_url($sample_with_data_url) . '">' . esc_html__('Download Sample CSV with Product Data', 'user-manager') . '</a>';
		}
		if (!empty($sample_links)) {
			$output .= '<p style="margin: 0 0 12px 0;">' . implode(' | ', $sample_links) . '</p>';
		}
		$output .= '<div style="margin-bottom: 20px;">';
		$output .= '<label for="csv_file" style="display: block; margin-bottom: 10px; font-weight: bold;">' . esc_html__('Select CSV File:', 'user-manager') . '</label>';
		$output .= '<input type="file" name="csv_file" id="csv_file" accept=".csv" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">';
		$output .= '</div>';
		$output .= '<button type="submit" name="bulk_add_to_cart_submit" value="1" class="button button-primary" style="padding: 10px 20px;">' . esc_html__('Add to Cart', 'user-manager') . '</button>';
		$output .= '</form>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Reorder WooCommerce notices so successes appear before errors.
	 *
	 * @param array<string,array> $notices Notices.
	 * @return array<string,array>
	 */
	public static function bulk_add_to_cart_reorder_notices($notices) {
		if (empty($notices) || !is_array($notices)) {
			return $notices;
		}

		$success = [];
		$error   = [];
		$other   = [];

		foreach ($notices as $notice) {
			if (isset($notice['type'])) {
				if ($notice['type'] === 'success') {
					$success[] = $notice;
				} elseif ($notice['type'] === 'error') {
					$error[] = $notice;
				} else {
					$other[] = $notice;
				}
			} else {
				$other[] = $notice;
			}
		}

		return array_merge($success, $other, $error);
	}

	/**
	 * Return the upload directory for Bulk Add to Cart CSV files.
	 */
	private static function get_bulk_add_to_cart_upload_dir(): string {
		$dir = trailingslashit(WP_CONTENT_DIR) . 'bulk-add-to-cart-import-files/';
		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}
		return $dir;
	}

	/**
	 * Handle CSV upload and processing for Bulk Add to Cart.
	 *
	 * Mirrors the behavior of the standalone plugin but is controlled
	 * by the "Activate Bulk Add to Cart Functionality" setting.
	 */
	public static function bulk_add_to_cart_process_upload(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}
		if (is_admin()) {
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			return;
		}
		if (!isset($_POST['bulk_add_to_cart_submit'])) {
			return;
		}

		self::$bulk_add_to_cart_debug_trace = [];
		self::$bulk_add_to_cart_media_upload_info = [];

		$options          = get_option('bulk_add_to_cart_settings', []);
		$identifier_col   = isset($options['identifier_column']) ? trim((string) $options['identifier_column']) : 'product_id';
		$identifier_type  = isset($options['identifier_type']) ? (string) $options['identifier_type'] : 'product_id';
		$product_id_col   = self::bulk_add_to_cart_get_product_id_column_header($options);
		$sku_col          = self::bulk_add_to_cart_get_sku_column_header($options);
		$quantity_col     = isset($options['quantity_column']) ? (string) $options['quantity_column'] : 'quantity';
		$debug_mode       = isset($options['debug_mode']) ? (string) $options['debug_mode'] : '0';
		$debug_enabled    = $debug_mode === '1' || self::is_bulk_add_to_cart_debug_requested();
		$debug_trace      = [];

		self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'request_start', 'Bulk Add to Cart upload request received.');

		if (empty($_POST['bulk_add_to_cart_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bulk_add_to_cart_nonce'])), 'bulk_add_to_cart_upload')) {
			self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'error_nonce', 'Nonce verification failed.');
			if ($debug_enabled) {
				self::$bulk_add_to_cart_debug_trace = $debug_trace;
			}
			wc_add_notice(esc_html__('Security check failed. Please try again.', 'user-manager'), 'error');
			return;
		}

		if (empty($_FILES['csv_file']) || !isset($_FILES['csv_file']['error']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
			self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'error_upload', 'Upload payload missing or upload error code was returned.');
			if ($debug_enabled) {
				self::$bulk_add_to_cart_debug_trace = $debug_trace;
			}
			wc_add_notice(esc_html__('Error uploading file. Please try again.', 'user-manager'), 'error');
			return;
		}

		if (function_exists('wc_load_cart')) {
			wc_load_cart();
		}

		if (!function_exists('WC') || !WC()->cart) {
			if (function_exists('WC')) {
				WC()->cart = new WC_Cart();
			} else {
				self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'error_cart', 'WooCommerce cart could not be initialized.');
				if ($debug_enabled) {
					self::$bulk_add_to_cart_debug_trace = $debug_trace;
				}
				wc_add_notice(esc_html__('WooCommerce cart is not available.', 'user-manager'), 'error');
				return;
			}
		}

		$file       = $_FILES['csv_file'];
		$filename   = isset($file['name']) ? sanitize_file_name(wp_unslash($file['name'])) : 'upload.csv';
		$file_size  = isset($file['size']) ? (int) $file['size'] : 0;
		$file_type  = isset($file['type']) ? (string) $file['type'] : '';
		$tmp_name   = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
		$timestamp  = current_time('timestamp');
		$new_name   = $timestamp . '-' . $filename;
		$upload_dir = self::get_bulk_add_to_cart_upload_dir();
		$upload_path = $upload_dir . $new_name;

		self::bulk_add_to_cart_append_debug_trace(
			$debug_trace,
			0,
			'upload_meta',
			'File "' . $filename . '" (' . $file_size . ' bytes, MIME: ' . ($file_type !== '' ? $file_type : 'unknown') . ').'
		);

		if ($debug_enabled) {
			wc_add_notice(
				'Upload metadata - Name: ' . $filename .
				', Size(bytes): ' . $file_size .
				', MIME: ' . ($file_type !== '' ? $file_type : 'unknown') .
				', Temp exists: ' . (is_file($tmp_name) ? 'yes' : 'no'),
				'notice'
			);
		}

		if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
			self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'error_move_uploaded_file', 'move_uploaded_file() failed.');
			if ($debug_enabled) {
				self::$bulk_add_to_cart_debug_trace = $debug_trace;
			}
			wc_add_notice(esc_html__('Error saving file. Please try again.', 'user-manager'), 'error');
			return;
		}

		if ($debug_enabled) {
			wc_add_notice('Upload saved to: ' . $upload_path, 'notice');
		}
		self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'upload_saved', 'CSV file saved to plugin upload directory.');

		$uploaded_file_url = content_url('bulk-add-to-cart-import-files/' . rawurlencode($new_name));
		$request_url = self::bulk_add_to_cart_get_current_request_url();
		$media_info = self::bulk_add_to_cart_upload_file_to_media_library($upload_path, $new_name, $request_url);
		$activity_url = !empty($media_info['attachment_url']) ? (string) $media_info['attachment_url'] : $uploaded_file_url;
		$media_info['activity_url'] = $activity_url;
		$media_info['raw_upload_url'] = $uploaded_file_url;
		self::$bulk_add_to_cart_media_upload_info = $media_info;
		self::add_user_activity((int) get_current_user_id(), __('Bulk Add to Cart CSV Upload', 'user-manager'), $activity_url);

		if (!empty($media_info['attachment_id'])) {
			self::bulk_add_to_cart_append_debug_trace(
				$debug_trace,
				0,
				'media_uploaded',
				'Stored in Media Library as attachment #' . (int) $media_info['attachment_id'] . '.'
			);
		} elseif (!empty($media_info['error'])) {
			self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'media_upload_error', (string) $media_info['error']);
			if ($debug_enabled) {
				wc_add_notice('Media Library upload error: ' . (string) $media_info['error'], 'notice');
			}
		}

		$handle = fopen($upload_path, 'r');
		if (!$handle) {
			self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'error_fopen', 'Could not open saved CSV file for reading.');
			if ($debug_enabled) {
				self::$bulk_add_to_cart_debug_trace = $debug_trace;
			}
			wc_add_notice(esc_html__('Error reading file. Please try again.', 'user-manager'), 'error');
			return;
		}

		$headers = false;
		$leading_blank_rows = 0;
		$csv_line_number = 0;
		while (($header_row = fgetcsv($handle)) !== false) {
			$csv_line_number++;
			if (!self::bulk_add_to_cart_row_has_data($header_row)) {
				$leading_blank_rows++;
				self::bulk_add_to_cart_append_debug_trace($debug_trace, $csv_line_number, 'skip_blank_before_header', 'Blank row skipped before header detection.');
				continue;
			}
			$headers = $header_row;
			self::bulk_add_to_cart_append_debug_trace($debug_trace, $csv_line_number, 'header_detected', 'Header row detected: ' . implode(', ', array_map('trim', $header_row)));
			break;
		}
		if (!$headers) {
			fclose($handle);
			self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'error_header_missing', 'No non-empty header row found.');
			if ($debug_enabled) {
				self::$bulk_add_to_cart_debug_trace = $debug_trace;
			}
			wc_add_notice(esc_html__('Invalid CSV format. Please check the file structure.', 'user-manager'), 'error');
			return;
		}

		$normalized_headers = array_map([__CLASS__, 'bulk_add_to_cart_normalize_header'], $headers);
		$normalized_lookup  = array_map([__CLASS__, 'bulk_add_to_cart_normalize_header'], [$identifier_col, $product_id_col, $sku_col, $quantity_col]);
		$identifier_lookup  = $normalized_lookup[0] ?? 'product_id';
		$product_id_lookup  = $normalized_lookup[1] ?? 'product_id';
		$sku_lookup         = $normalized_lookup[2] ?? '_sku';
		$quantity_lookup    = $normalized_lookup[3] ?? 'quantity';

		if ($debug_enabled) {
			wc_add_notice('CSV Headers: ' . implode(', ', $headers), 'notice');
			wc_add_notice('Normalized Headers: ' . implode(', ', $normalized_headers), 'notice');
			wc_add_notice(
				'Using settings - Identifier Column: ' . $identifier_col .
				', Type: ' . $identifier_type .
				', Product ID Column: ' . $product_id_col .
				', SKU Column: ' . $sku_col .
				', Quantity Column: ' . $quantity_col,
				'notice'
			);
			wc_add_notice('Leading blank rows skipped before header: ' . $leading_blank_rows, 'notice');
		}

		$identifier_index = array_search($identifier_lookup, $normalized_headers, true);
		$product_id_index = array_search($product_id_lookup, $normalized_headers, true);
		$sku_index        = array_search($sku_lookup, $normalized_headers, true);
		$quantity_index   = array_search($quantity_lookup, $normalized_headers, true);
		$product_id_index = array_search($product_id_lookup, $normalized_headers, true);
		if ($product_id_index === false && $product_id_builtin_lookup !== $product_id_lookup) {
			$product_id_index = array_search($product_id_builtin_lookup, $normalized_headers, true);
		}

		self::bulk_add_to_cart_append_debug_trace(
			$debug_trace,
			0,
			'column_lookup',
			'Identifier column index: ' . ($identifier_index !== false ? (string) $identifier_index : 'not found') .
			'; Product ID column index: ' . ($product_id_index !== false ? (string) $product_id_index : 'not found') .
			'; SKU column index: ' . ($sku_index !== false ? (string) $sku_index : 'not found') .
			'; Quantity column index: ' . ($quantity_index !== false ? (string) $quantity_index : 'not found') . '.'
		);

		if ($debug_enabled) {
			wc_add_notice(
				'Column indices - Identifier: ' . ($identifier_index !== false ? $identifier_index : 'not found') .
				', Product ID: ' . ($product_id_index !== false ? $product_id_index : 'not found') .
				', SKU: ' . ($sku_index !== false ? $sku_index : 'not found') .
				', Quantity: ' . ($quantity_index !== false ? $quantity_index : 'not found'),
				'notice'
			);
		}

		$has_any_identifier_column = ($identifier_index !== false || $product_id_index !== false || $sku_index !== false);
		if (!$has_any_identifier_column || $quantity_index === false) {
			fclose($handle);
			self::bulk_add_to_cart_append_debug_trace($debug_trace, 0, 'error_missing_columns', 'Required columns were not found in header.');
			if ($debug_enabled) {
				self::$bulk_add_to_cart_debug_trace = $debug_trace;
			}
			wc_add_notice(
				sprintf(
					esc_html__('Required columns not found. Need quantity column "%1$s" plus at least one identifier column ("%2$s", "%3$s", or "%4$s").', 'user-manager'),
					$quantity_col,
					$identifier_col,
					$product_id_col,
					$sku_col
				),
				'error'
			);
			return;
		}

		$success_count        = 0;
		$error_count          = 0;
		$errors               = [];
		$successful_additions = [];
		$total_items_added    = 0;
		$line_item_results    = [];
		$row_number           = 1;
		$blank_rows_skipped   = 0;
		$zero_qty_skipped     = 0;
		$data_rows_seen       = 0;

		while (($row = fgetcsv($handle)) !== false) {
			$csv_line_number++;
			$data_rows_seen++;
			if (!self::bulk_add_to_cart_row_has_data($row)) {
				$blank_rows_skipped++;
				self::bulk_add_to_cart_append_debug_trace($debug_trace, $csv_line_number, 'skip_blank_row', 'Blank data row skipped.');
				continue;
			}
			$row_number++;
			$identifier_primary = ($identifier_index !== false && isset($row[$identifier_index])) ? trim((string) $row[$identifier_index]) : '';
			$product_id_fallback_identifier = ($product_id_index !== false && isset($row[$product_id_index])) ? trim((string) $row[$product_id_index]) : '';
			$sku_fallback_identifier = ($sku_index !== false && isset($row[$sku_index])) ? trim((string) $row[$sku_index]) : '';
			$identifier = $identifier_primary !== '' ? $identifier_primary : ($product_id_fallback_identifier !== '' ? $product_id_fallback_identifier : $sku_fallback_identifier);
			$quantity_raw = isset($row[$quantity_index]) ? (string) $row[$quantity_index] : '';
			$quantity   = self::bulk_add_to_cart_parse_quantity($quantity_raw);

			if ($quantity <= 0) {
				$zero_qty_skipped++;
				self::bulk_add_to_cart_append_debug_trace(
					$debug_trace,
					$csv_line_number,
					'skip_zero_or_blank_quantity',
					'Identifier "' . ($identifier_primary !== '' ? $identifier_primary : '(empty)') . '" skipped because quantity "' . $quantity_raw . '" parses to 0.'
				);
				continue;
			}
			$lookup_attempts = [];
			if ($identifier_primary !== '') {
				$lookup_attempts[] = [
					'value'  => $identifier_primary,
					'type'   => $identifier_type,
					'source' => $identifier_col,
				];
			}
			if ($product_id_fallback_identifier !== '') {
				$already_covered_by_identifier = ($identifier_type === 'product_id' && $identifier_primary !== '' && $identifier_primary === $product_id_fallback_identifier);
				if (!$already_covered_by_identifier) {
					$lookup_attempts[] = [
						'value'  => $product_id_fallback_identifier,
						'type'   => 'product_id',
						'source' => $product_id_col,
					];
				}
			}
			if ($sku_fallback_identifier !== '') {
				$already_covered_by_identifier = ($identifier_type === 'product_sku' && $identifier_primary !== '' && $identifier_primary === $sku_fallback_identifier);
				if (!$already_covered_by_identifier) {
					$lookup_attempts[] = [
						'value'  => $sku_fallback_identifier,
						'type'   => 'product_sku',
						'source' => $sku_col,
					];
				}
			}

			if (empty($lookup_attempts)) {
				$error_count++;
				$message = sprintf(
					esc_html__('Row %1$d: Missing identifier (checked "%2$s", "%3$s", and "%4$s"; Quantity: "%5$s")', 'user-manager'),
					$row_number,
					esc_html($identifier_col),
					esc_html($product_id_col),
					esc_html($sku_col),
					esc_html($quantity_raw)
				);
				$errors[] = $message;
				$line_item_results[] = [
					'line'          => $csv_line_number,
					'status'        => 'error',
					'product_id'    => '',
					'product_title' => '',
					'variation'     => '',
					'qty_added'     => 0,
					'note'          => (string) __('Missing identifier across all identifier columns; row not added.', 'user-manager'),
				];
				self::bulk_add_to_cart_append_debug_trace($debug_trace, $csv_line_number, 'error_missing_identifier', wp_strip_all_tags($message));
				continue;
			}

			$product = null;
			$attempted_lookup_text = [];
			foreach ($lookup_attempts as $attempt) {
				$attempted_lookup_text[] = (string) $attempt['source'] . '=' . (string) $attempt['value'] . ' (' . (string) $attempt['type'] . ')';
				$product = self::bulk_add_to_cart_find_product((string) $attempt['value'], (string) $attempt['type'], $options);
				if ($product) {
					break;
				}
			}
			if (!$product) {
				$error_count++;
				$attempted_label = !empty($attempted_descriptions) ? implode(' | ', $attempted_descriptions) : __('(none)', 'user-manager');
				$message = sprintf(
					esc_html__('Row %1$d: Product not found using identifiers: %2$s (Quantity: %3$s)', 'user-manager'),
					$row_number,
					esc_html(implode(' | ', $attempted_lookup_text)),
					esc_html((string) $quantity)
				);
				$errors[] = $message;
				$line_item_results[] = [
					'line'          => $csv_line_number,
					'status'        => 'error',
					'product_id'    => $identifier !== '' ? $identifier : '—',
					'product_title' => '',
					'variation'     => '',
					'qty_added'     => 0,
					'note'          => (string) __('Product not found from identifier/product_id/_sku values; row not added.', 'user-manager'),
				];
				self::bulk_add_to_cart_append_debug_trace($debug_trace, $csv_line_number, 'error_product_not_found', wp_strip_all_tags($message));
				continue;
			}
			[$product_title, $product_variation] = self::bulk_add_to_cart_get_product_title_and_variation($product);
			$product_id_for_line = (string) $product->get_id();

			if (!$product->is_purchasable()) {
				$error_count++;
				$message = sprintf(
					esc_html__('Row %1$d: Product not purchasable: %2$s (Quantity: %3$s)', 'user-manager'),
					$row_number,
					esc_html($identifier_used),
					esc_html((string) $quantity)
				);
				$errors[] = $message;
				$line_item_results[] = [
					'line'          => $csv_line_number,
					'status'        => 'error',
					'product_id'    => $product_id_for_line,
					'product_title' => $product_title,
					'variation'     => $product_variation,
					'qty_added'     => 0,
					'note'          => (string) __('Product is not purchasable; row not added.', 'user-manager'),
				];
				self::bulk_add_to_cart_append_debug_trace($debug_trace, $csv_line_number, 'error_not_purchasable', wp_strip_all_tags($message));
				continue;
			}

			if ($product->managing_stock() && !$product->has_enough_stock($quantity)) {
				$error_count++;
				$message = sprintf(
					esc_html__('Row %1$d: Insufficient stock for: %2$s (Requested: %3$s, Available: %4$s)', 'user-manager'),
					$row_number,
					esc_html($identifier_used),
					esc_html((string) $quantity),
					esc_html((string) $product->get_stock_quantity())
				);
				$errors[] = $message;
				$line_item_results[] = [
					'line'          => $csv_line_number,
					'status'        => 'error',
					'product_id'    => $product_id_for_line,
					'product_title' => $product_title,
					'variation'     => $product_variation,
					'qty_added'     => 0,
					'note'          => sprintf(
						/* translators: 1: requested qty, 2: available qty */
						__('Insufficient stock (requested %1$s, available %2$s); row not added.', 'user-manager'),
						(string) $quantity,
						(string) $product->get_stock_quantity()
					),
				];
				self::bulk_add_to_cart_append_debug_trace($debug_trace, $csv_line_number, 'error_insufficient_stock', wp_strip_all_tags($message));
				continue;
			}

			$cart_item_key = self::bulk_add_to_cart_add_product_to_cart($product, $quantity);
			if ($cart_item_key) {
				$success_count++;
				$total_items_added += $quantity;
				$name = $product->get_name();
				if (!isset($successful_additions[$name])) {
					$successful_additions[$name] = 0;
				}
				$successful_additions[$name] += $quantity;
				$line_item_results[] = [
					'line'          => $csv_line_number,
					'status'        => 'success',
					'product_id'    => $product_id_for_line,
					'product_title' => $product_title,
					'variation'     => $product_variation,
					'qty_added'     => $quantity,
					'note'          => (string) __('Added to cart.', 'user-manager'),
				];
				self::bulk_add_to_cart_append_debug_trace(
					$debug_trace,
					$csv_line_number,
					'added_to_cart',
					'Added "' . $identifier_used . '" from ' . ($identifier_source !== '' ? $identifier_source : 'identifier') . ' (qty ' . $quantity . ') to cart.'
				);
			} else {
				$error_count++;
				$message = sprintf(
					esc_html__('Row %1$d: Failed to add to cart: %2$s (Quantity: %3$s)', 'user-manager'),
					$row_number,
					esc_html($identifier_used),
					esc_html((string) $quantity)
				);
				$errors[] = $message;
				$line_item_results[] = [
					'line'          => $csv_line_number,
					'status'        => 'error',
					'product_id'    => $product_id_for_line,
					'product_title' => $product_title,
					'variation'     => $product_variation,
					'qty_added'     => 0,
					'note'          => (string) __('Could not add to cart (WooCommerce rejected this row).', 'user-manager'),
				];
				self::bulk_add_to_cart_append_debug_trace($debug_trace, $csv_line_number, 'error_add_to_cart_failed', wp_strip_all_tags($message));
			}
		}

		fclose($handle);

		$history_confirmation_messages = [];
		$line_notice_messages = [];

		if ($total_items_added > 0) {
			$summary_message_plain = sprintf(
				/* translators: %d: total quantity of items added */
				_n('%d item total was added to cart.', '%d items total were added to cart.', $total_items_added, 'user-manager'),
				$total_items_added
			);
			$summary_message = $summary_message_plain;
			$summary_message .= ' <a href="' . esc_url(wc_get_cart_url()) . '" class="button wc-forward">' . esc_html__('View cart', 'user-manager') . '</a>';
			wc_add_notice($summary_message, 'success', ['um_bulk_add_to_cart_keep_visible' => 1]);
			$history_confirmation_messages[] = $summary_message_plain;
		}

		if ($error_count > 0) {
			$error_summary_message = sprintf(
				/* translators: %d: number of products */
				_n('%d product could not be added.', '%d products could not be added.', $error_count, 'user-manager'),
				$error_count
			);
			wc_add_notice($error_summary_message, 'error');
			$history_confirmation_messages[] = $error_summary_message;
		}

		if (!empty($line_item_results)) {
			$line_notice = '<strong>' . esc_html__('Details:', 'user-manager') . '</strong>';
			$line_notice .= '<ul style="margin:8px 0 0 20px;">';
			foreach ($line_item_results as $entry) {
				$is_success = (($entry['status'] ?? '') === 'success');
				$line_text = sprintf(
					/* translators: 1: product ID, 2: product title, 3: variation label, 4: Added/Error, 5: qty added */
					__('ID: %1$s %2$s %3$s %4$s (%5$s)', 'user-manager'),
					(string) (!empty($entry['product_id']) ? $entry['product_id'] : '—'),
					(string) (!empty($entry['product_title']) ? $entry['product_title'] : '—'),
					(string) (!empty($entry['variation']) ? $entry['variation'] : '—'),
					(string) ($is_success ? __('Added', 'user-manager') : __('Error', 'user-manager')),
					(string) ($entry['qty_added'] ?? 0)
				);
				if (!$is_success && !empty($entry['note'])) {
					$line_text .= ' ' . sprintf(
						/* translators: %s: error note */
						__('Note: %s', 'user-manager'),
						(string) $entry['note']
					);
				}
				$line_notice_messages[] = $line_text;
				$line_notice .= '<li>' . esc_html($line_text) . '</li>';
			}
			$line_notice .= '</ul>';
			wc_add_notice($line_notice, 'notice', ['um_bulk_add_to_cart_keep_visible' => 1]);
		}
		self::bulk_add_to_cart_append_debug_trace(
			$debug_trace,
			0,
			'summary',
			'Rows seen: ' . $data_rows_seen . '; blank rows skipped: ' . $blank_rows_skipped . '; zero/blank quantity rows skipped: ' . $zero_qty_skipped . '; rows added: ' . $success_count . '; total items added: ' . $total_items_added . '; errors: ' . $error_count . '.'
		);
		if ($debug_enabled) {
			wc_add_notice(
				'Processing summary - Rows seen: ' . $data_rows_seen .
				', Blank rows skipped: ' . $blank_rows_skipped .
				', Zero/blank quantity rows skipped: ' . $zero_qty_skipped .
				', Rows added: ' . $success_count .
				', Total items added: ' . $total_items_added .
				', Errors: ' . $error_count,
				'notice'
			);
			self::$bulk_add_to_cart_debug_trace = $debug_trace;
		}

		$current_user = wp_get_current_user();
		$history      = get_option('bulk_add_to_cart_history', []);
		if (!is_array($history)) {
			$history = [];
		}
		$media_info            = self::$bulk_add_to_cart_media_upload_info;
		$media_attachment_id   = !empty($media_info['attachment_id']) ? (int) $media_info['attachment_id'] : 0;
		$media_attachment_url  = !empty($media_info['attachment_url']) ? (string) $media_info['attachment_url'] : '';
		$history_file_url      = $media_attachment_url;
		if ($history_file_url === '' && !empty($media_info['activity_url'])) {
			$history_file_url = (string) $media_info['activity_url'];
		}
		if ($history_file_url === '') {
			$history_file_url = content_url('bulk-add-to-cart-import-files/' . rawurlencode($new_name));
		}

		array_unshift(
			$history,
			[
				'timestamp'      => current_time('mysql'),
				'user_id'        => $current_user->ID,
				'username'       => $current_user->user_login,
				'user_email'     => (string) ($current_user->user_email ?? ''),
				'filename'       => $new_name,
				'success_count'  => $success_count,
				'total_items_added' => $total_items_added,
				'error_count'    => $error_count,
				'errors'         => $errors,
				'successes'      => $successful_additions,
				'line_item_results' => $line_item_results,
				'confirmation_messages' => $history_confirmation_messages,
				'detail_messages' => $line_notice_messages,
				'media_attachment_id' => $media_attachment_id,
				'media_attachment_url' => $media_attachment_url,
				'file_url' => $history_file_url,
			]
		);
		$history = array_slice($history, 0, 100);
		update_option('bulk_add_to_cart_history', $history);

		if (isset($options['redirect_to_cart']) && $options['redirect_to_cart'] === '1' && !$debug_enabled) {
			wp_safe_redirect(wc_get_cart_url());
			exit;
		}
	}

	/**
	 * Download a basic sample CSV for Bulk Add to Cart.
	 */
	public static function bulk_add_to_cart_maybe_download_sample_csv(): void {
		if (is_admin()) {
			return;
		}
		if (!isset($_GET['um_bulk_add_to_cart_sample'])) {
			return;
		}
		if (!class_exists('WooCommerce')) {
			wp_die(esc_html__('WooCommerce is required to download this CSV.', 'user-manager'));
		}
		if (!is_user_logged_in()) {
			wp_die(esc_html__('Please log in to download this CSV.', 'user-manager'));
		}

		$options           = get_option('bulk_add_to_cart_settings', []);
		$show_sample_csv   = !array_key_exists('show_sample_csv', $options) || (string) ($options['show_sample_csv'] ?? '1') === '1';
		if (!$show_sample_csv) {
			wp_die(esc_html__('Download Sample CSV is disabled in Add-on settings.', 'user-manager'));
		}
		$identifier_column = isset($options['identifier_column']) ? trim((string) $options['identifier_column']) : 'product_id';
		$product_id_column = self::bulk_add_to_cart_get_product_id_column_header($options);
		$sku_column        = self::bulk_add_to_cart_get_sku_column_header($options);
		$quantity_column   = isset($options['quantity_column']) ? trim((string) $options['quantity_column']) : 'quantity';
		$identifier_type   = isset($options['identifier_type']) ? (string) $options['identifier_type'] : 'product_id';
		if ($identifier_column === '') {
			$identifier_column = 'product_id';
		}
		if ($identifier_type === '') {
			$identifier_type = 'product_id';
		}
		if ($quantity_column === '') {
			$quantity_column = 'quantity';
		}
		$include_identifier_column = self::bulk_add_to_cart_should_include_identifier_column($identifier_column, $product_id_column_header);

		$headers = self::bulk_add_to_cart_build_sample_csv_headers($options, $identifier_column, $product_id_column, $sku_column, $quantity_column);

		nocache_headers();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=bulk-add-to-cart-sample.csv');

		$out = fopen('php://output', 'w');
		if (!$out) {
			exit;
		}

		fputcsv($out, $headers);
		fwrite($out, "\r\n");
		$row_1_map = [
			self::bulk_add_to_cart_normalize_header($product_id_column) => '123',
			self::bulk_add_to_cart_normalize_header($sku_column)        => 'sample-sku-123',
			self::bulk_add_to_cart_normalize_header($identifier_column) => self::bulk_add_to_cart_get_sample_identifier_value('123', 'sample-sku-123', 'sample-product-123', 'Sample Product', 'sample-meta-123', $identifier_type),
			self::bulk_add_to_cart_normalize_header($quantity_column)   => '1',
			self::bulk_add_to_cart_normalize_header('product_title')    => 'Sample Product',
			self::bulk_add_to_cart_normalize_header('product_variation') => '',
		];
		$row_2_map = [
			self::bulk_add_to_cart_normalize_header($product_id_column) => '456',
			self::bulk_add_to_cart_normalize_header($sku_column)        => 'sample-sku-456',
			self::bulk_add_to_cart_normalize_header($identifier_column) => self::bulk_add_to_cart_get_sample_identifier_value('456', 'sample-sku-456', 'sample-variation-product-456', 'Sample Variation Product', 'sample-meta-456', $identifier_type),
			self::bulk_add_to_cart_normalize_header($quantity_column)   => '2',
			self::bulk_add_to_cart_normalize_header('product_title')    => 'Sample Variation Product',
			self::bulk_add_to_cart_normalize_header('product_variation') => 'Size: M | Color: Blue',
		];
		fputcsv($out, self::bulk_add_to_cart_build_csv_row_from_headers($headers, $row_1_map));
		fputcsv($out, self::bulk_add_to_cart_build_csv_row_from_headers($headers, $row_2_map));
		fclose($out);
		exit;
	}

	/**
	 * Download a CSV prefilled with all products/variations and zero quantities.
	 *
	 * Extra columns (like product title / variation summary) are informational and
	 * ignored by the uploader, which reads quantity + identifier columns (configured identifier first, then product_id fallback).
	 */
	public static function bulk_add_to_cart_maybe_download_sample_with_product_data(): void {
		if (is_admin()) {
			return;
		}
		if (!isset($_GET['um_bulk_add_to_cart_sample_data'])) {
			return;
		}
		if (!class_exists('WooCommerce')) {
			wp_die(esc_html__('WooCommerce is required to download this CSV.', 'user-manager'));
		}
		if (!is_user_logged_in()) {
			wp_die(esc_html__('Please log in to download this CSV.', 'user-manager'));
		}

		$options           = get_option('bulk_add_to_cart_settings', []);
		$show_sample_with_data = !array_key_exists('show_sample_with_product_data', $options) || (string) ($options['show_sample_with_product_data'] ?? '1') === '1';
		if (!$show_sample_with_data) {
			wp_die(esc_html__('Download Sample CSV with Product Data is disabled in Add-on settings.', 'user-manager'));
		}
		$identifier_column = isset($options['identifier_column']) ? trim((string) $options['identifier_column']) : 'product_id';
		$product_id_column = self::bulk_add_to_cart_get_product_id_column_header($options);
		$sku_column        = self::bulk_add_to_cart_get_sku_column_header($options);
		$quantity_column   = isset($options['quantity_column']) ? trim((string) $options['quantity_column']) : 'quantity';
		$identifier_type   = isset($options['identifier_type']) ? (string) $options['identifier_type'] : 'product_id';
		if ($identifier_column === '') {
			$identifier_column = 'product_id';
		}
		if ($identifier_type === '') {
			$identifier_type = 'product_id';
		}
		if ($quantity_column === '') {
			$quantity_column = 'quantity';
		}
		$include_identifier_column = self::bulk_add_to_cart_should_include_identifier_column($identifier_column, $product_id_column_header);
		$post_statuses = ['publish'];
		if ($include_private_products) {
			$post_statuses[] = 'private';
		}
		if ($include_draft_products) {
			$post_statuses[] = 'draft';
		}

		$headers = self::bulk_add_to_cart_build_sample_csv_headers($options, $identifier_column, $product_id_column, $sku_column, $quantity_column);

		$ids = get_posts([
			'post_type'              => ['product', 'product_variation'],
			'post_status'            => $post_statuses,
			'posts_per_page'         => -1,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]);

		nocache_headers();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=bulk-add-to-cart-sample-with-product-data.csv');

		$out = fopen('php://output', 'w');
		if (!$out) {
			exit;
		}

		fputcsv($out, $headers);
		foreach ($ids as $product_id) {
			$product_id = absint($product_id);
			if ($product_id <= 0) {
				continue;
			}
			$product = wc_get_product($product_id);
			if (!$product) {
				continue;
			}
			[$title, $variation_summary] = self::bulk_add_to_cart_get_product_title_and_variation($product);
			$sku_value = '';
			if (method_exists($product, 'get_sku')) {
				$sku_value = trim((string) $product->get_sku());
			}
			if ($sku_value === '') {
				$sku_value = trim((string) get_post_meta($product_id, '_sku', true));
			}
			$identifier_value = self::bulk_add_to_cart_get_identifier_value_for_product($product_id, $identifier_type, $options, $title, $sku_value);

			$row_map = [
				self::bulk_add_to_cart_normalize_header($product_id_column) => (string) $product_id,
				self::bulk_add_to_cart_normalize_header($sku_column)        => $sku_value,
				self::bulk_add_to_cart_normalize_header($identifier_column) => $identifier_value,
				self::bulk_add_to_cart_normalize_header($quantity_column)   => '0',
				self::bulk_add_to_cart_normalize_header('product_title')    => $title,
				self::bulk_add_to_cart_normalize_header('product_variation') => $variation_summary,
			];
			fputcsv($out, self::bulk_add_to_cart_build_csv_row_from_headers($headers, $row_map));
		}

		fclose($out);
		exit;
	}

	/**
	 * Resolve Product ID CSV column header.
	 */
	private static function bulk_add_to_cart_get_product_id_column_header(array $options): string {
		$header = isset($options['product_id_custom_column_header']) ? trim((string) $options['product_id_custom_column_header']) : 'product_id';
		return $header !== '' ? $header : 'product_id';
	}

	/**
	 * Resolve SKU CSV column header.
	 */
	private static function bulk_add_to_cart_get_sku_column_header(array $options): string {
		$header = isset($options['sku_custom_column_header']) ? trim((string) $options['sku_custom_column_header']) : '_sku';
		return $header !== '' ? $header : '_sku';
	}

	/**
	 * Build sample CSV headers based on visibility + configured columns.
	 *
	 * @return array<int,string>
	 */
	private static function bulk_add_to_cart_build_sample_csv_headers(
		array $options,
		string $identifier_column,
		string $product_id_column,
		string $sku_column,
		string $quantity_column
	): array {
		$headers = [];
		$hide_product_id_column = isset($options['hide_product_id_column']) && (string) $options['hide_product_id_column'] === '1';
		$hide_sku_column = isset($options['hide_sku_column']) && (string) $options['hide_sku_column'] === '1';

		if (!$hide_product_id_column) {
			self::bulk_add_to_cart_add_header_if_missing($headers, $product_id_column);
		}
		if (!$hide_sku_column) {
			self::bulk_add_to_cart_add_header_if_missing($headers, $sku_column);
		}
		self::bulk_add_to_cart_add_header_if_missing($headers, $identifier_column);
		self::bulk_add_to_cart_add_header_if_missing($headers, $quantity_column);
		self::bulk_add_to_cart_add_header_if_missing($headers, 'product_title');
		self::bulk_add_to_cart_add_header_if_missing($headers, 'product_variation');

		return $headers;
	}

	/**
	 * Add a header to CSV header list if missing (case-insensitive).
	 *
	 * @param array<int,string> $headers
	 */
	private static function bulk_add_to_cart_add_header_if_missing(array &$headers, string $header): void {
		$header = trim($header);
		if ($header === '') {
			return;
		}
		$normalized = self::bulk_add_to_cart_normalize_header($header);
		foreach ($headers as $existing) {
			if (self::bulk_add_to_cart_normalize_header((string) $existing) === $normalized) {
				return;
			}
		}
		$headers[] = $header;
	}

	/**
	 * Build one CSV row using header order + normalized values map.
	 *
	 * @param array<int,string>        $headers
	 * @param array<string,string> $values_by_normalized_header
	 * @return array<int,string>
	 */
	private static function bulk_add_to_cart_build_csv_row_from_headers(array $headers, array $values_by_normalized_header): array {
		$row = [];
		foreach ($headers as $header) {
			$key = self::bulk_add_to_cart_normalize_header((string) $header);
			$row[] = isset($values_by_normalized_header[$key]) ? (string) $values_by_normalized_header[$key] : '';
		}
		return $row;
	}

	/**
	 * Build demo identifier value based on identifier type.
	 */
	private static function bulk_add_to_cart_get_sample_identifier_value(
		string $product_id,
		string $sku,
		string $slug,
		string $title,
		string $meta_value,
		string $identifier_type
	): string {
		switch ($identifier_type) {
			case 'product_sku':
				return $sku;
			case 'product_slug':
				return $slug;
			case 'product_title':
				return $title;
			case 'meta_field':
				return $meta_value;
			case 'product_id':
			default:
				return $product_id;
		}
	}

	/**
	 * Build identifier value for a real product row in sample-with-data CSV.
	 *
	 */
	private static function bulk_add_to_cart_get_identifier_value_for_product(
		int $product_id,
		string $identifier_type,
		array $options,
		string $title,
		string $sku_value
	): string {
		switch ($identifier_type) {
			case 'product_sku':
				return $sku_value;
			case 'product_slug':
				$post = get_post($product_id);
				return $post ? (string) $post->post_name : '';
			case 'product_title':
				return $title;
			case 'meta_field':
				$meta_field_name = isset($options['meta_field_name']) ? (string) $options['meta_field_name'] : '';
				if ($meta_field_name === '') {
					return '';
				}
				return (string) get_post_meta($product_id, $meta_field_name, true);
			case 'product_id':
			default:
				return (string) $product_id;
		}
	}

	/**
	 * URL debug flag for Bulk Add to Cart frontend.
	 */
	private static function is_bulk_add_to_cart_debug_requested(): bool {
		if (!isset($_GET['um_bulk_add_to_cart_debug'])) {
			return false;
		}

		$raw = sanitize_text_field(wp_unslash($_GET['um_bulk_add_to_cart_debug']));
		$raw = strtolower(trim($raw));

		return $raw !== '' && $raw !== '0' && $raw !== 'false' && $raw !== 'no';
	}

	/**
	 * Check whether a parsed CSV row contains any non-empty cell value.
	 *
	 * @param mixed $row CSV row from fgetcsv().
	 */
	private static function bulk_add_to_cart_row_has_data($row): bool {
		if (!is_array($row) || empty($row)) {
			return false;
		}
		foreach ($row as $cell) {
			if (trim((string) $cell) !== '') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Append one debug trace row for CSV upload troubleshooting.
	 *
	 * @param array<int,array{line:int,status:string,message:string}> $trace
	 */
	private static function bulk_add_to_cart_append_debug_trace(array &$trace, int $line, string $status, string $message): void {
		$trace[] = [
			'line'    => $line,
			'status'  => $status,
			'message' => $message,
		];
	}

	/**
	 * Build current request URL for logging/attachment descriptions.
	 */
	private static function bulk_add_to_cart_get_current_request_url(): string {
		$scheme = (is_ssl() || (isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) === 'on')) ? 'https://' : 'http://';
		$host   = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : (string) parse_url(home_url(), PHP_URL_HOST);
		$uri    = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
		return esc_url_raw($scheme . $host . $uri);
	}

	/**
	 * Copy uploaded CSV into Media Library with uploader/source metadata.
	 *
	 * @return array<string,mixed>
	 */
	private static function bulk_add_to_cart_upload_file_to_media_library(string $source_path, string $filename, string $source_url): array {
		$result = [
			'attachment_id'  => 0,
			'attachment_url' => '',
			'error'          => '',
		];

		if (!is_file($source_path)) {
			$result['error'] = 'Source CSV does not exist.';
			return $result;
		}

		$uploads = wp_upload_dir();
		if (!empty($uploads['error'])) {
			$result['error'] = 'wp_upload_dir error: ' . (string) $uploads['error'];
			return $result;
		}

		$target_dir = trailingslashit((string) ($uploads['path'] ?? ''));
		if ($target_dir === '') {
			$result['error'] = 'Uploads path is empty.';
			return $result;
		}
		wp_mkdir_p($target_dir);

		$target_name = wp_unique_filename($target_dir, $filename);
		$target_path = $target_dir . $target_name;
		if (!copy($source_path, $target_path)) {
			$result['error'] = 'Failed to copy CSV into uploads directory.';
			return $result;
		}

		$filetype = wp_check_filetype($target_name, null);
		$mime     = !empty($filetype['type']) ? (string) $filetype['type'] : 'text/csv';

		$user = wp_get_current_user();
		$user_login = (string) ($user->user_login ?? '');
		$user_email = (string) ($user->user_email ?? '');
		$user_id    = (int) ($user->ID ?? 0);
		$uploaded_at = current_time('mysql');
		$description = sprintf(
			/* translators: 1: username, 2: user ID, 3: user email, 4: datetime, 5: source URL */
			__('Bulk Add to Cart CSV uploaded by %1$s (ID: %2$d, email: %3$s) on %4$s from URL: %5$s', 'user-manager'),
			$user_login !== '' ? $user_login : 'unknown',
			$user_id,
			$user_email !== '' ? $user_email : 'unknown',
			$uploaded_at,
			$source_url
		);

		$attachment = [
			'post_mime_type' => $mime,
			'post_title'     => sanitize_text_field((string) pathinfo($target_name, PATHINFO_FILENAME)),
			'post_content'   => $description,
			'post_status'    => 'inherit',
		];

		$attachment_id = wp_insert_attachment($attachment, $target_path);
		if (is_wp_error($attachment_id) || !$attachment_id) {
			$result['error'] = is_wp_error($attachment_id) ? $attachment_id->get_error_message() : 'wp_insert_attachment failed.';
			return $result;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attachment_meta = wp_generate_attachment_metadata((int) $attachment_id, $target_path);
		if (is_array($attachment_meta)) {
			wp_update_attachment_metadata((int) $attachment_id, $attachment_meta);
		}

		update_post_meta((int) $attachment_id, '_um_bulk_add_to_cart_uploaded_by_user_id', $user_id);
		update_post_meta((int) $attachment_id, '_um_bulk_add_to_cart_uploaded_at', $uploaded_at);
		update_post_meta((int) $attachment_id, '_um_bulk_add_to_cart_source_url', esc_url_raw($source_url));
		update_post_meta((int) $attachment_id, '_um_bulk_add_to_cart_original_filename', $filename);

		$result['attachment_id'] = (int) $attachment_id;
		$result['attachment_url'] = (string) wp_get_attachment_url((int) $attachment_id);
		return $result;
	}

	/**
	 * Return display-ready product title + variation text.
	 *
	 * @param WC_Product $product Product object.
	 * @return array{0:string,1:string}
	 */
	private static function bulk_add_to_cart_get_product_title_and_variation($product): array {
		if (!$product || !is_object($product) || !method_exists($product, 'is_type')) {
			return ['', ''];
		}

		if ($product->is_type('variation')) {
			$parent_title = '';
			$parent_id = method_exists($product, 'get_parent_id') ? (int) $product->get_parent_id() : 0;
			if ($parent_id > 0) {
				$parent = wc_get_product($parent_id);
				if ($parent && is_object($parent) && method_exists($parent, 'get_name')) {
					$parent_title = (string) $parent->get_name();
				}
			}
			$title = $parent_title !== '' ? $parent_title : (method_exists($product, 'get_name') ? (string) $product->get_name() : '');
			$variation = '';
			if (function_exists('wc_get_formatted_variation')) {
				$variation = trim(wp_strip_all_tags((string) wc_get_formatted_variation($product, true, true, true)));
			}
			return [$title, $variation];
		}

		$title = method_exists($product, 'get_name') ? (string) $product->get_name() : '';
		return [$title, ''];
	}

	/**
	 * Keep only explicitly marked notices visible during debug mode.
	 *
	 * @param array<string,mixed> $notice
	 */
	private static function bulk_add_to_cart_notice_should_remain_visible(array $notice): bool {
		$data = isset($notice['data']) && is_array($notice['data']) ? $notice['data'] : [];
		return !empty($data['um_bulk_add_to_cart_keep_visible']);
	}

	/**
	 * Flatten WooCommerce notices into plain-text debug lines.
	 *
	 * @param array<string,array<int,array<string,mixed>>> $notices
	 * @return array<int,string>
	 */
	private static function bulk_add_to_cart_flatten_notices_for_debug(array $notices): array {
		$lines = [];
		foreach ($notices as $type => $bucket) {
			if (!is_array($bucket)) {
				continue;
			}
			foreach ($bucket as $notice) {
				$message = isset($notice['notice']) ? (string) $notice['notice'] : '';
				if ($message === '') {
					continue;
				}
				$message = str_replace(["\r\n", "\r"], "\n", $message);
				$message = preg_replace('/<\s*br\s*\/?>/i', "\n", $message);
				$message = str_ireplace('</li>', "\n", $message);
				$message = str_ireplace('<li>', '- ', $message);
				$plain = trim(wp_strip_all_tags((string) $message));
				if ($plain === '') {
					continue;
				}
				$parts = preg_split('/\n+/', $plain);
				if (!is_array($parts)) {
					$parts = [$plain];
				}
				foreach ($parts as $part) {
					$part = trim((string) $part);
					if ($part === '') {
						continue;
					}
					$lines[] = $part;
				}
			}
		}
		return $lines;
	}

	/**
	 * Whether a separate identifier column should be included in sample CSV files.
	 */
	private static function bulk_add_to_cart_should_include_identifier_column(string $identifier_column, string $product_id_column_header): bool {
		$identifier_column = trim($identifier_column);
		if ($identifier_column === '') {
			return false;
		}

		return self::bulk_add_to_cart_normalize_header($identifier_column) !== self::bulk_add_to_cart_normalize_header($product_id_column_header);
	}

	/**
	 * Normalize CSV/header keys for robust matching.
	 */
	private static function bulk_add_to_cart_normalize_header(string $header): string {
		$header = str_replace("\xEF\xBB\xBF", '', $header);
		$header = trim($header);
		$header = strtolower($header);
		$header = preg_replace('/\s+/', '_', $header);
		return (string) $header;
	}

	/**
	 * Parse CSV quantity values safely.
	 */
	private static function bulk_add_to_cart_parse_quantity(string $raw): int {
		$raw = trim($raw);
		if ($raw === '') {
			return 0;
		}

		$normalized = str_replace(',', '', $raw);
		$normalized = preg_replace('/[^0-9.\-]/', '', $normalized);
		if ($normalized === '' || !is_numeric($normalized)) {
			return 0;
		}

		$value = (float) $normalized;
		return $value > 0 ? (int) floor($value) : 0;
	}

	/**
	 * Resolve product by configured identifier.
	 *
	 * @param string $identifier Identifier value from CSV.
	 * @param string $identifier_type Identifier type setting.
	 * @param array  $options Bulk settings.
	 * @return WC_Product|null
	 */
	private static function bulk_add_to_cart_find_product(string $identifier, string $identifier_type, array $options) {
		switch ($identifier_type) {
			case 'product_id':
				return wc_get_product(absint($identifier));

			case 'product_sku':
				$product_id = wc_get_product_id_by_sku($identifier);
				return $product_id ? wc_get_product($product_id) : null;

			case 'product_slug':
				return self::bulk_add_to_cart_find_product_by_slug($identifier);

			case 'product_title':
				return self::bulk_add_to_cart_find_product_by_title($identifier);

			case 'meta_field':
				$meta_field_name = isset($options['meta_field_name']) ? (string) $options['meta_field_name'] : '';
				if ($meta_field_name === '') {
					return null;
				}
				return self::bulk_add_to_cart_find_product_by_meta($identifier, $meta_field_name);
		}

		return null;
	}

	/**
	 * Resolve product by slug (product or variation).
	 */
	private static function bulk_add_to_cart_find_product_by_slug(string $identifier) {
		$slug = sanitize_title($identifier);
		if ($slug === '') {
			return null;
		}

		global $wpdb;
		$product_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type IN ('product','product_variation') AND post_status IN ('publish','private') ORDER BY post_type = 'product_variation' DESC, ID DESC LIMIT 1",
				$slug
			)
		);

		return $product_id ? wc_get_product((int) $product_id) : null;
	}

	/**
	 * Resolve product by exact title (product or variation).
	 */
	private static function bulk_add_to_cart_find_product_by_title(string $identifier) {
		global $wpdb;
		$product_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type IN ('product','product_variation') AND post_status IN ('publish','private') ORDER BY post_type = 'product_variation' DESC, ID DESC LIMIT 1",
				$identifier
			)
		);

		return $product_id ? wc_get_product((int) $product_id) : null;
	}

	/**
	 * Resolve product by custom meta value (product or variation).
	 */
	private static function bulk_add_to_cart_find_product_by_meta(string $identifier, string $meta_key) {
		global $wpdb;
		$product_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT pm.post_id
				 FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE pm.meta_key = %s
				   AND pm.meta_value = %s
				   AND p.post_type IN ('product','product_variation')
				   AND p.post_status IN ('publish','private')
				 ORDER BY p.post_type = 'product_variation' DESC, pm.post_id DESC
				 LIMIT 1",
				$meta_key,
				$identifier
			)
		);

		return $product_id ? wc_get_product((int) $product_id) : null;
	}

	/**
	 * Add a product (including variations) to the cart.
	 *
	 * @param WC_Product $product Product object.
	 * @param int        $quantity Quantity.
	 * @return string|false
	 */
	private static function bulk_add_to_cart_add_product_to_cart($product, int $quantity) {
		if (!$product || !function_exists('WC') || !WC()->cart) {
			return false;
		}

		if ($product->is_type('variation')) {
			$variation_id = (int) $product->get_id();
			$parent_id    = (int) $product->get_parent_id();
			if ($variation_id <= 0 || $parent_id <= 0) {
				return false;
			}

			$attributes = method_exists($product, 'get_variation_attributes')
				? (array) $product->get_variation_attributes()
				: [];

			return WC()->cart->add_to_cart($parent_id, $quantity, $variation_id, $attributes);
		}

		return WC()->cart->add_to_cart((int) $product->get_id(), $quantity);
	}

	/**
	 * Generate a random uppercase alphanumeric code of length N.
	 */
	private static function generate_random_code(int $length): string {
		$length = max(4, min(64, $length));
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$max = strlen($chars) - 1;
		$code = '';
		for ($i = 0; $i < $length; $i++) {
			$code .= $chars[random_int(0, $max)];
		}
		return $code;
	}

	/**
	 * Build a random coupon/code string with optional prefix and suffix.
	 *
	 * Supports %YEAR% placeholder replacement in prefix and suffix.
	 */
	public static function build_random_code(int $length, string $prefix = '', string $postfix = ''): string {
		$length = max(4, min(64, $length));
		$year = date('Y');
		$prefix = str_replace('%YEAR%', $year, $prefix);
		$postfix = str_replace('%YEAR%', $year, $postfix);
		$rand = self::generate_random_code($length);
		return strtoupper($prefix . $rand . $postfix);
	}

	/**
	 * Collect debug data for New User Coupons decisions.
	 *
	 * @return array{
	 *     enabled:bool,
	 *     wc_available:bool,
	 *     user_obj:?WP_User,
	 *     user_email:string,
	 *     user_registered:string,
	 *     nuc_when:string,
	 *     after_date_rule:string,
	 *     after_date_passed:?bool,
	 *     email_rule:string,
	 *     email_contains_match:?bool,
	 *     email_exclude_rule:string,
	 *     email_exclude_match:?bool,
	 *     template_code:string,
	 *     template_coupon_id:?int,
	 *     template_coupon_summary:string,
	 *     template_coupon:?WC_Coupon,
	 *     has_existing_coupon:bool,
	 *     existing_coupon_id:?int,
	 *     existing_coupon_code:string,
	 *     existing_coupon_link:string,
	 *     eligible:bool,
	 *     blocked_reason:string,
	 *     messages:array<string>
	 * }
	 */
	private static function get_new_user_coupon_debug_data(int $user_id, array $settings, bool $include_objects = false): array {
		$data = [
			'enabled' => !empty($settings['nuc_enabled']),
			'wc_available' => class_exists('WC_Coupon') && function_exists('wc_get_coupon_id_by_code'),
			'user_obj' => null,
			'user_email' => '',
			'user_registered' => '',
			'nuc_when' => $settings['nuc_when'] ?? 'after_registration',
			'after_date_rule' => trim((string) ($settings['nuc_after_date'] ?? '')),
			'after_date_passed' => null,
			'email_rule' => trim((string) ($settings['nuc_email_contains'] ?? '')),
			'email_contains_match' => null,
			'email_exclude_rule' => trim((string) ($settings['nuc_email_exclude'] ?? '')),
			'email_exclude_match' => null,
			'template_code' => trim((string) ($settings['nuc_template_code'] ?? '')),
			'template_coupon_id' => null,
			'template_coupon_summary' => '',
			'template_coupon' => null,
			'has_existing_coupon' => false,
			'existing_coupon_id' => null,
			'existing_coupon_code' => '',
			'existing_coupon_link' => '',
			'eligible' => true,
			'blocked_reason' => '',
			'messages' => [],
		];

		if (!$data['enabled']) {
			$data['eligible'] = false;
			$data['blocked_reason'] = __('New User Coupons are disabled.', 'user-manager');
			$data['messages'][] = $data['blocked_reason'];
			return $data;
		}

		if (!$data['wc_available']) {
			$data['eligible'] = false;
			$data['blocked_reason'] = __('WooCommerce coupon functions are unavailable.', 'user-manager');
			$data['messages'][] = $data['blocked_reason'];
			return $data;
		}

		$user = get_user_by('id', $user_id);
		if (!$user) {
			$data['eligible'] = false;
			$data['blocked_reason'] = __('User not found.', 'user-manager');
			$data['messages'][] = $data['blocked_reason'];
			return $data;
		}
		$data['user_obj'] = $include_objects ? $user : null;
		$data['user_email'] = $user->user_email;
		$data['user_registered'] = $user->user_registered;
		$data['messages'][] = sprintf(__('Evaluating user #%d (%s)', 'user-manager'), $user_id, $user->user_email);

		if ($data['after_date_rule'] !== '') {
			$threshold_ts = strtotime($data['after_date_rule'] . ' 00:00:00');
			$user_ts = strtotime($user->user_registered);
			if ($threshold_ts && $user_ts) {
				$data['after_date_passed'] = $user_ts >= $threshold_ts;
				$data['messages'][] = $data['after_date_passed']
					? sprintf(__('User registered after %s (passes date filter).', 'user-manager'), $data['after_date_rule'])
					: sprintf(__('User registered before %s (fails date filter).', 'user-manager'), $data['after_date_rule']);
				if (!$data['after_date_passed'] && empty($data['blocked_reason'])) {
					$data['eligible'] = false;
					$data['blocked_reason'] = __('User registered before the required date.', 'user-manager');
				}
			}
		}

		if ($data['email_rule'] !== '') {
			$list = array_filter(array_map('trim', explode(',', $data['email_rule'])));
			$hay = strtolower($user->user_email);
			$match = false;
			foreach ($list as $needle) {
				if ($needle === '') {
					continue;
				}
				if (strpos($hay, strtolower($needle)) !== false) {
					$match = true;
					break;
				}
			}
			$data['email_contains_match'] = $match;
			$data['messages'][] = $match
				? __('User email matches the configured substrings.', 'user-manager')
				: __('User email does not match any configured substrings.', 'user-manager');
			if (!$match && empty($data['blocked_reason'])) {
				$data['eligible'] = false;
				$data['blocked_reason'] = __('User email does not match the allowed substrings.', 'user-manager');
			}
		}

		if ($data['email_exclude_rule'] !== '') {
			$list = array_filter(array_map('trim', explode(',', $data['email_exclude_rule'])));
			$hay = strtolower($user->user_email);
			$match = false;
			foreach ($list as $needle) {
				if ($needle === '') {
					continue;
				}
				if (strpos($hay, strtolower($needle)) !== false) {
					$match = true;
					break;
				}
			}
			$data['email_exclude_match'] = $match;
			$data['messages'][] = $match
				? __('User email matches an excluded substring.', 'user-manager')
				: __('User email does not match any excluded substrings.', 'user-manager');
			if ($match) {
				$data['eligible'] = false;
				$data['blocked_reason'] = __('User email matches an excluded substring.', 'user-manager');
			}
		}

		if ($data['template_code'] === '') {
			$data['eligible'] = false;
			$data['blocked_reason'] = __('Template coupon code is not configured.', 'user-manager');
			$data['messages'][] = $data['blocked_reason'];
			return $data;
		}

		$template_id = wc_get_coupon_id_by_code($data['template_code']);
		if ($template_id) {
			$data['template_coupon_id'] = $template_id;
			$template_coupon = new WC_Coupon($template_id);
			if ($template_coupon && $template_coupon->get_id()) {
				$summary = sprintf(
					'%s — %s %s',
					$template_coupon->get_code(),
					$template_coupon->get_discount_type(),
					$template_coupon->get_amount()
				);
				$date_expires = $template_coupon->get_date_expires();
				if ($date_expires) {
					$summary .= ' · ' . sprintf(__('Expires %s', 'user-manager'), $date_expires->date_i18n(get_option('date_format')));
				}
				$data['template_coupon_summary'] = $summary;
				if ($include_objects) {
					$data['template_coupon'] = $template_coupon;
				}
				$data['messages'][] = sprintf(__('Template coupon found (ID %d).', 'user-manager'), $template_id);
			} else {
				$data['eligible'] = false;
				$data['blocked_reason'] = __('Template coupon could not be loaded.', 'user-manager');
				$data['messages'][] = $data['blocked_reason'];
			}
		} else {
			$data['eligible'] = false;
			$data['blocked_reason'] = __('Template coupon code could not be located.', 'user-manager');
			$data['messages'][] = $data['blocked_reason'];
		}

		// Existing coupon check (modern meta-based).
		$existing = get_posts([
			'post_type'   => 'shop_coupon',
			'post_status' => 'publish',
			'meta_query'  => [
				'relation' => 'AND',
				[
					'key'     => '_um_user_coupon_user_id',
					'value'   => $user_id,
					'compare' => '=',
				],
				[
					'key'     => '_um_user_coupon_template',
					'value'   => $data['template_code'],
					'compare' => '=',
				],
			],
			'fields'      => 'ids',
			'numberposts' => 1,
		]);
		if (!empty($existing)) {
			$data['has_existing_coupon']   = true;
			$data['existing_coupon_id']    = $existing[0];
			$data['existing_coupon_code']  = get_the_title($existing[0]) ?: '';
			$link                          = get_edit_post_link($existing[0]);
			$data['existing_coupon_link']  = $link ? $link : '';
			$data['messages'][]            = sprintf(__('Existing coupon detected: %s (ID %d).', 'user-manager'), $data['existing_coupon_code'], $existing[0]);
		} else {
			$data['messages'][] = __('No existing coupons detected for this user/template pair.', 'user-manager');
		}

		// Legacy check: look for any coupon for this user whose code matches the
		// configured New User Coupons prefix/suffix pattern, even if it predates
		// the _um_user_coupon_* meta being added. When nuc_auto_draft_duplicates
		// is enabled, we will keep the OLDEST matching coupon published and
		// automatically move any newer matches into Draft status.
		if (true) {
			$raw_prefix  = (string) ($settings['nuc_prefix'] ?? '');
			$raw_postfix = (string) ($settings['nuc_postfix'] ?? '');

			if ($raw_prefix !== '' || $raw_postfix !== '') {
				$year        = date('Y');
				$prefix_eval = str_replace('%YEAR%', $year, $raw_prefix);
				$postfix_eval = str_replace('%YEAR%', $year, $raw_postfix);

				$meta_query = ['relation' => 'OR'];
				if ($data['user_email'] !== '') {
					$meta_query[] = [
						'key'     => 'customer_email',
						'value'   => $data['user_email'],
						'compare' => 'LIKE',
					];
					$meta_query[] = [
						'key'     => '_um_user_coupon_user_email',
						'value'   => $data['user_email'],
						'compare' => 'LIKE',
					];
				}
				$meta_query[] = [
					'key'     => '_um_user_coupon_user_id',
					'value'   => $user_id,
					'compare' => '=',
				];

				$legacy_candidates = get_posts([
					'post_type'   => 'shop_coupon',
					'post_status' => 'publish',
					'numberposts' => -1,
					'fields'      => 'ids',
					'meta_query'  => $meta_query,
				]);

				$auto_draft = !empty($settings['nuc_auto_draft_duplicates']);

				// Build a list of matching candidates with their creation date so
				// we can keep the oldest and optionally draft the newer ones.
				$matches = [];
				foreach ($legacy_candidates as $cid) {
					$cid  = (int) $cid;
					$code = strtoupper((string) get_the_title($cid));
					if ($code === '') {
						continue;
					}

					$matches_prefix = true;
					$matches_suffix = true;

					if ($prefix_eval !== '') {
						$matches_prefix = (stripos($code, strtoupper($prefix_eval)) === 0);
					}
					if ($postfix_eval !== '') {
						$suffix_len = strlen($postfix_eval);
						if ($suffix_len > 0 && strlen($code) >= $suffix_len) {
							$matches_suffix = (substr($code, -$suffix_len) === strtoupper($postfix_eval));
						} else {
							$matches_suffix = false;
						}
					}

					if ($matches_prefix && $matches_suffix) {
						$created = get_post_time('U', true, $cid);
						$matches[] = [
							'id'      => $cid,
							'code'    => $code,
							'created' => $created ?: 0,
						];
					}
				}

				if (!empty($matches)) {
					// Oldest first.
					usort($matches, static function ($a, $b) {
						return $a['created'] <=> $b['created'];
					});

					$canonical = array_shift($matches);

					// Oldest matching coupon becomes the canonical code.
					$data['has_existing_coupon']   = true;
					$data['existing_coupon_id']    = $canonical['id'];
					$data['existing_coupon_code']  = $canonical['code'];
					$link                          = get_edit_post_link($canonical['id']);
					$data['existing_coupon_link']  = $link ? $link : '';
					$data['messages'][]            = sprintf(__('Legacy coupon detected by prefix/suffix: %s (ID %d). Oldest coupon kept active.', 'user-manager'), $canonical['code'], $canonical['id']);

					// Any remaining matches are newer duplicates.
					if ($auto_draft) {
						foreach ($matches as $dup) {
							wp_update_post([
								'ID'          => $dup['id'],
								'post_status' => 'draft',
							]);
							$data['messages'][] = sprintf(__('Duplicate legacy coupon moved to Draft: %s (ID %d).', 'user-manager'), $dup['code'], $dup['id']);
						}
					}
				} else {
					if (empty($data['has_existing_coupon'])) {
						$data['messages'][] = __('No legacy prefix/suffix coupons detected for this user.', 'user-manager');
					}
				}
			}
		}

		return $data;
	}
	
	/**
	 * Create a per-user coupon cloned from a template coupon.
	 */
	private static function create_user_coupon_from_template(int $user_id): ?string {
		if (!class_exists('WC_Coupon') || !function_exists('wc_get_coupon_id_by_code')) {
			return null;
		}
		$settings = self::get_settings();
		if (empty($settings['nuc_enabled'])) {
			return null;
		}
		$context = self::get_new_user_coupon_debug_data($user_id, $settings, true);
		if (empty($context['enabled']) || empty($context['wc_available']) || empty($context['user_obj'])) {
			return null;
		}
		if (!empty($context['has_existing_coupon']) && !empty($context['existing_coupon_code'])) {
			return $context['existing_coupon_code'];
		}
		if (empty($context['eligible'])) {
			return null;
		}
		$template_coupon = $context['template_coupon'] ?? null;
		if (!$template_coupon instanceof WC_Coupon) {
			return null;
		}
		$user = $context['user_obj'];
		$template_code = $context['template_code'];
		
		// Build new code
		$prefix = (string) ($settings['nuc_prefix'] ?? '');
		$postfix = (string) ($settings['nuc_postfix'] ?? '');
		$len = (int) ($settings['nuc_code_length'] ?? 8);
		$year = date('Y');
		$prefix = str_replace('%YEAR%', $year, $prefix);
		$postfix = str_replace('%YEAR%', $year, $postfix);
		$rand = self::generate_random_code($len);
		$new_code = strtoupper($prefix . $rand . $postfix);
		
		$new_coupon_id = wp_insert_post([
			'post_title' => $new_code,
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'shop_coupon',
		]);
		if (is_wp_error($new_coupon_id) || !$new_coupon_id) {
			return null;
		}
		
		$new_coupon = new WC_Coupon($new_coupon_id);
		
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
		
		if (!empty($settings['nuc_amount_override'])) {
			$new_coupon->set_amount((string) $settings['nuc_amount_override']);
		}
		
		$exp_days = isset($settings['nuc_exp_days']) ? (int) $settings['nuc_exp_days'] : 0;
		if ($exp_days > 0) {
			$base_ts = (int) current_time('timestamp');
			$expires_ts = $base_ts + ($exp_days * DAY_IN_SECONDS);
			$new_coupon->set_date_expires($expires_ts);
		}
		
		if (method_exists($new_coupon, 'set_email_restrictions')) {
			$new_coupon->set_email_restrictions([$user->user_email]);
		} else {
			update_post_meta($new_coupon_id, 'customer_email', [$user->user_email]);
		}
		
		$new_coupon->save();
		
		update_post_meta($new_coupon_id, '_um_user_coupon_user_id', $user_id);
		update_post_meta($new_coupon_id, '_um_user_coupon_template', $template_code);
		
		if (!empty($settings['nuc_send_email']) && !empty($settings['nuc_email_template'])) {
			self::send_coupon_email_to_user($user, $new_code, (string) $settings['nuc_email_template']);
		}
		
		// Log coupon creation in activity log.
		self::add_activity_log('coupon_created', $user_id, 'New User Coupons', [
			'coupon_id' => $new_coupon_id,
			'coupon_code' => $new_code,
			'coupon_link' => get_edit_post_link($new_coupon_id, ''),
			'template_code' => $template_code,
			'amount' => $new_coupon->get_amount(),
			'user_email' => $user->user_email,
			'email_sent' => !empty($settings['nuc_send_email']) && !empty($settings['nuc_email_template']),
			'email_template' => $settings['nuc_email_template'] ?? '',
		]);
		
		return $new_code;
	}

	/**
	 * Render debug overlay for New User Coupons decisions.
	 */
	public static function render_new_user_coupon_debug_panel(): void {
		if (is_admin()) {
			return;
		}
		$settings = self::get_settings();
		if (empty($settings['nuc_debug_mode'])) {
			return;
		}
		if (!current_user_can('manage_options')) {
			return;
		}
		$user_id = get_current_user_id();
		?>
		<style>
		.um-nuc-debug-panel {
			position: fixed;
			bottom: 24px;
			left: 24px;
			z-index: 999999;
			background: rgba(8, 10, 15, 0.95);
			border: 1px solid rgba(255,255,255,0.15);
			border-radius: 8px;
			padding: 16px;
			max-width: 360px;
			color: #fff;
			font-size: 12px;
			line-height: 1.4;
			box-shadow: 0 8px 24px rgba(0,0,0,0.4);
		}
		.um-nuc-debug-panel h3 {
			margin: 0 0 10px 0;
			font-size: 14px;
			display: flex;
			align-items: center;
			gap: 6px;
		}
		.um-nuc-debug-panel ul {
			margin: 0 0 10px 16px;
			padding: 0;
		}
		.um-nuc-debug-panel li {
			margin-bottom: 4px;
		}
		.um-nuc-debug-panel .um-nuc-debug-messages {
			max-height: 160px;
			overflow-y: auto;
			background: rgba(255,255,255,0.05);
			padding: 8px;
			border-radius: 4px;
			margin-top: 8px;
		}
		</style>
		<div class="um-nuc-debug-panel">
			<h3>🧪 <?php esc_html_e('New User Coupons Debug', 'user-manager'); ?></h3>
			<?php if (!$user_id) : ?>
				<p><?php esc_html_e('Log in to view coupon debug information.', 'user-manager'); ?></p>
			<?php else : ?>
				<?php $data = self::get_new_user_coupon_debug_data($user_id, $settings, false); ?>
				<ul>
					<li><strong><?php esc_html_e('User:', 'user-manager'); ?></strong> <?php echo esc_html($data['user_email'] ?: sprintf('#%d', $user_id)); ?></li>
					<li><strong><?php esc_html_e('Mode:', 'user-manager'); ?></strong> <?php echo esc_html($data['nuc_when']); ?></li>
					<li><strong><?php esc_html_e('Template code:', 'user-manager'); ?></strong> <?php echo esc_html($data['template_code'] ?: '—'); ?></li>
					<li><strong><?php esc_html_e('Template summary:', 'user-manager'); ?></strong> <?php echo esc_html($data['template_coupon_summary'] ?: __('Not found', 'user-manager')); ?></li>
					<li><strong><?php esc_html_e('Existing coupon:', 'user-manager'); ?></strong>
						<?php if (!empty($data['has_existing_coupon']) && $data['existing_coupon_code']) : ?>
							<?php echo esc_html($data['existing_coupon_code']); ?>
							<?php if (!empty($data['existing_coupon_link'])) : ?>
								(<a href="<?php echo esc_url($data['existing_coupon_link']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('edit', 'user-manager'); ?></a>)
							<?php endif; ?>
						<?php else : ?>
							<?php esc_html_e('None', 'user-manager'); ?>
						<?php endif; ?>
					</li>
					<li><strong><?php esc_html_e('Registered date:', 'user-manager'); ?></strong> <?php echo esc_html($data['user_registered'] ?: '—'); ?></li>
					<li><strong><?php esc_html_e('After date rule:', 'user-manager'); ?></strong> <?php echo $data['after_date_rule'] !== '' ? esc_html($data['after_date_rule']) : __('Not set', 'user-manager'); ?><?php
						if ($data['after_date_passed'] !== null) {
							echo $data['after_date_passed'] ? ' ✅' : ' ❌';
						}
					?></li>
					<li><strong><?php esc_html_e('Email contains rule:', 'user-manager'); ?></strong> <?php echo $data['email_rule'] !== '' ? esc_html($data['email_rule']) : __('Not set', 'user-manager'); ?><?php
						if ($data['email_contains_match'] !== null) {
							echo $data['email_contains_match'] ? ' ✅' : ' ❌';
						}
					?></li>
					<li><strong><?php esc_html_e('Email exclude rule:', 'user-manager'); ?></strong> <?php echo $data['email_exclude_rule'] !== '' ? esc_html($data['email_exclude_rule']) : __('Not set', 'user-manager'); ?><?php
						if ($data['email_exclude_match'] !== null) {
							echo $data['email_exclude_match'] ? ' ❌' : ' ✅';
						}
					?></li>
					<li><strong><?php esc_html_e('Eligible now:', 'user-manager'); ?></strong>
						<?php
						if (!empty($data['has_existing_coupon'])) {
							esc_html_e('Already has coupon', 'user-manager');
						} else {
							echo !empty($data['eligible']) ? esc_html__('Yes', 'user-manager') : esc_html__('No', 'user-manager');
						}
						?>
					</li>
					<?php if (!empty($data['blocked_reason'])) : ?>
						<li><strong><?php esc_html_e('Blocked reason:', 'user-manager'); ?></strong> <?php echo esc_html($data['blocked_reason']); ?></li>
					<?php endif; ?>
				</ul>
				<?php if (!empty($data['messages'])) : ?>
					<div class="um-nuc-debug-messages">
						<strong><?php esc_html_e('Decision log', 'user-manager'); ?>:</strong>
						<ol style="margin:6px 0 0 18px;padding:0;">
							<?php foreach ($data['messages'] as $message) : ?>
								<li><?php echo esc_html($message); ?></li>
							<?php endforeach; ?>
						</ol>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}
	
	/**
	 * Send coupon email using a User Manager email template to a specific user.
	 */
	public static function send_coupon_email_to_user(WP_User $user, string $coupon_code, string $template_id): void {
		$template = self::resolve_coupon_email_template($template_id);
		if (!$template || empty($user->user_email) || !is_email($user->user_email)) {
			return;
		}
		$coupon_code_value = self::format_coupon_code_value_for_placeholder($coupon_code);
		$replacements = [
			'%SITEURL%' => home_url(),
			'%LOGINURL%' => '/my-account/',
			'%USERNAME%' => $user->user_login,
			'%PASSWORD%' => '••••••••••••',
			'%EMAIL%' => $user->user_email,
			'%FIRSTNAME%' => $user->first_name,
			'%LASTNAME%' => $user->last_name,
			'%PASSWORDRESETURL%' => home_url('/my-account/lost-password/'),
			'%COUPONCODE%' => $coupon_code,
			'%COUPONCODEVALUE%' => $coupon_code_value,
			'[coupon_code]' => $coupon_code,
		];
		$subject = str_replace(array_keys($replacements), array_values($replacements), $template['subject'] ?? 'Your Coupon');
		$heading = str_replace(array_keys($replacements), array_values($replacements), $template['heading'] ?? 'Your Coupon Code');
		$body = str_replace(array_keys($replacements), array_values($replacements), $template['body'] ?? '<p>%COUPONCODE%</p>');
		
		// Build Woo-styled email HTML
		$email_html = User_Manager_Email::get_preview_html($body, $heading);
		$headers = User_Manager_Email::build_email_headers();
		wp_mail($user->user_email, $subject, $email_html, $headers);
	}

	/**
	 * Send coupon email to an arbitrary email address (not necessarily a WP user).
	 *
	 * Supports %COUPONCODE% and the common placeholders used in templates.
	 */
	public static function send_coupon_email_to_address(string $email, string $coupon_code, string $template_id): void {
		if (!is_email($email)) {
			return;
		}

		$template  = self::resolve_coupon_email_template($template_id);
		if (!$template) {
			return;
		}

		$login_url = '/my-account/';
		$username  = strstr($email, '@', true) ?: $email;
		$coupon_code_value = self::format_coupon_code_value_for_placeholder($coupon_code);

		$replacements = [
			'%SITEURL%'         => home_url(),
			'%LOGINURL%'        => $login_url,
			'%USERNAME%'        => $username,
			'%PASSWORD%'        => '••••••••••••',
			'%EMAIL%'           => $email,
			'%FIRSTNAME%'       => '',
			'%LASTNAME%'        => '',
			'%PASSWORDRESETURL%' => home_url('/my-account/lost-password/'),
			'%COUPONCODE%'      => $coupon_code,
			'%COUPONCODEVALUE%' => $coupon_code_value,
			'[coupon_code]'     => $coupon_code,
		];

		$subject = str_replace(array_keys($replacements), array_values($replacements), $template['subject'] ?? __('Your Coupon', 'user-manager'));
		$heading = str_replace(array_keys($replacements), array_values($replacements), $template['heading'] ?? __('Your Coupon Code', 'user-manager'));
		$body    = str_replace(array_keys($replacements), array_values($replacements), $template['body'] ?? '<p>%COUPONCODE%</p>');

		$email_html = User_Manager_Email::get_preview_html($body, $heading);
		$headers = User_Manager_Email::build_email_headers();
		wp_mail($email, $subject, $email_html, $headers);
	}

	/**
	 * Resolve coupon email template, with support for default template fallback.
	 *
	 * @return array<string,mixed>|null
	 */
	private static function resolve_coupon_email_template(string $template_id): ?array {
		if ($template_id === '__um_default__') {
			return [
				'title' => __('Default Template', 'user-manager'),
				'subject' => __('Your Remaining Balance Coupon Code', 'user-manager'),
				'heading' => __('Your Remaining Balance Coupon Code', 'user-manager'),
				'body' => '<p>' . __('Your new remaining balance code is:', 'user-manager') . ' <strong>[coupon_code]</strong></p>',
			];
		}
		if ($template_id === '') {
			return null;
		}
		$templates = self::get_email_templates();
		$template = $templates[$template_id] ?? null;
		return is_array($template) ? $template : null;
	}

	/**
	 * Format coupon amount for %COUPONCODEVALUE% placeholder.
	 */
	private static function format_coupon_code_value_for_placeholder(string $coupon_code): string {
		$coupon_code = trim($coupon_code);
		if ($coupon_code === '' || !class_exists('WC_Coupon')) {
			return '';
		}
		try {
			$coupon = new WC_Coupon($coupon_code);
			if (!$coupon || !$coupon->get_id()) {
				return '';
			}
			$amount = (float) $coupon->get_amount();
			return function_exists('wc_price') ? (string) wc_price($amount) : (string) $amount;
		} catch (\Throwable $throwable) {
			return '';
		}
	}
	
	/**
	 * Optionally run coupon generation when a logged-in user visits selected front-end locations.
	 */
	public static function maybe_create_new_user_coupon_on_visit(): void {
		if (is_admin()) {
			return;
		}
		$settings = self::get_settings();
		if (empty($settings['nuc_enabled'])) {
			return;
		}
		if (!self::should_run_new_user_coupon_visit_check($settings)) {
			return;
		}
		if (!is_user_logged_in()) {
			return;
		}
		$user_id = get_current_user_id();
		if (!$user_id) {
			return;
		}
		$mode = $settings['nuc_when'] ?? 'after_registration';
		if ($mode === 'after_first_order' && !self::user_has_completed_first_order((int) $user_id)) {
			return;
		}
		self::create_user_coupon_from_template((int) $user_id);
	}

	/**
	 * Log page/post/product and archive views for reporting.
	 *
	 * Tracks:
	 * - Page Views
	 * - Page Category Archives Views (page tax archives)
	 * - Post Views
	 * - Post Category Archives Views
	 * - Post Tag Archives Views
	 * - Product Views
	 * - Product Category Archives Views
	 * - Product Tag Archives Views
	 */
	public static function maybe_log_view_reports(): void {
		$settings = self::get_settings();
		$enabled  = array_key_exists('enable_view_reports', $settings) ? !empty($settings['enable_view_reports']) : true;
		if (!$enabled) {
			return;
		}

		if (is_admin() || wp_doing_ajax()) {
			return;
		}

		if (defined('REST_REQUEST') && REST_REQUEST) {
			return;
		}

		$user_id = is_user_logged_in() ? (int) get_current_user_id() : 0;

		// 1. Singular content views (pages, posts, products).
		if (is_singular()) {
			$post_id = get_queried_object_id();
			if ($post_id) {
				$post = get_post($post_id);
				if ($post instanceof WP_Post) {
					$post_type = $post->post_type;
					$action    = '';

					if ($post_type === 'page') {
						$action = 'page_view';
					} elseif ($post_type === 'post') {
						$action = 'post_view';
					} elseif ($post_type === 'product') {
						$action = 'product_view';
					}

					if ($action !== '') {
						$permalink = get_permalink($post_id);
						self::add_activity_log($action, $user_id, 'View Monitor', [
							'post_id'   => (int) $post_id,
							'post_type' => $post_type,
							'title'     => get_the_title($post),
							'slug'      => $post->post_name,
							'permalink' => $permalink ? (string) $permalink : '',
						]);
						return;
					}
				}
			}
		}

		// 2. WooCommerce product category archives.
		if (function_exists('is_product_category') && is_product_category()) {
			$term = get_queried_object();
			if ($term instanceof WP_Term) {
				$link = get_term_link($term);
				if (!is_wp_error($link)) {
					self::add_activity_log('product_category_view', $user_id, 'View Monitor', [
						'term_id'   => (int) $term->term_id,
						'taxonomy'  => (string) $term->taxonomy,
						'name'      => (string) $term->name,
						'slug'      => (string) $term->slug,
						'permalink' => (string) $link,
					]);
				}
			}
			return;
		}

		// 3. WooCommerce product tag archives.
		if (function_exists('is_product_tag') && is_product_tag()) {
			$term = get_queried_object();
			if ($term instanceof WP_Term) {
				$link = get_term_link($term);
				if (!is_wp_error($link)) {
					self::add_activity_log('product_tag_view', $user_id, 'View Monitor', [
						'term_id'   => (int) $term->term_id,
						'taxonomy'  => (string) $term->taxonomy,
						'name'      => (string) $term->name,
						'slug'      => (string) $term->slug,
						'permalink' => (string) $link,
					]);
				}
			}
			return;
		}

		// 4. Post category archives.
		if (is_category()) {
			$term = get_queried_object();
			if ($term instanceof WP_Term) {
				$link = get_term_link($term);
				if (!is_wp_error($link)) {
					self::add_activity_log('post_category_view', $user_id, 'View Monitor', [
						'term_id'   => (int) $term->term_id,
						'taxonomy'  => (string) $term->taxonomy,
						'name'      => (string) $term->name,
						'slug'      => (string) $term->slug,
						'permalink' => (string) $link,
					]);
				}
			}
			return;
		}

		// 5. Post tag archives.
		if (is_tag()) {
			$term = get_queried_object();
			if ($term instanceof WP_Term) {
				$link = get_term_link($term);
				if (!is_wp_error($link)) {
					self::add_activity_log('post_tag_view', $user_id, 'View Monitor', [
						'term_id'   => (int) $term->term_id,
						'taxonomy'  => (string) $term->taxonomy,
						'name'      => (string) $term->name,
						'slug'      => (string) $term->slug,
						'permalink' => (string) $link,
					]);
				}
			}
			return;
		}

		// 6. Generic page tax archives (for sites that attach custom taxonomies to pages).
		if (is_tax()) {
			$term = get_queried_object();
			if ($term instanceof WP_Term) {
				$taxonomy = get_taxonomy($term->taxonomy);
				if ($taxonomy && in_array('page', (array) $taxonomy->object_type, true)) {
					$link = get_term_link($term);
					if (!is_wp_error($link)) {
						self::add_activity_log('page_category_view', $user_id, 'View Monitor', [
							'term_id'   => (int) $term->term_id,
							'taxonomy'  => (string) $term->taxonomy,
							'name'      => (string) $term->name,
							'slug'      => (string) $term->slug,
							'permalink' => (string) $link,
						]);
					}
				}
			}
		}
	}

	/**
	 * Log 404 page hits for reporting.
	 */
	public static function maybe_log_404_error(): void {
		$settings = self::get_settings();
		$enabled  = array_key_exists('enable_view_reports', $settings) ? !empty($settings['enable_view_reports']) : true;
		if (!$enabled) {
			return;
		}

		if (!is_404()) {
			return;
		}

		if (is_admin() || wp_doing_ajax()) {
			return;
		}

		if (defined('REST_REQUEST') && REST_REQUEST) {
			return;
		}

		$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
		if ($request_uri === '') {
			return;
		}

		// Skip common asset paths and WordPress internals.
		$path = parse_url($request_uri, PHP_URL_PATH) ?: $request_uri;
		$path = (string) $path;

		$lower_path = strtolower($path);
		if (
			str_starts_with($lower_path, '/wp-content/') ||
			str_starts_with($lower_path, '/wp-includes/') ||
			str_starts_with($lower_path, '/wp-admin/')
		) {
			return;
		}

		if (preg_match('/\.(css|js|png|jpe?g|gif|svg|ico|webp|ttf|otf|woff2?|eot|json|xml|txt|map)$/i', $lower_path)) {
			return;
		}

		// Build absolute URL for display.
		$url = home_url($request_uri);

		$user_id = is_user_logged_in() ? (int) get_current_user_id() : 0;

		self::add_activity_log('404_hit', $user_id, '404 Monitor', [
			'url'     => $url,
			'path'    => $path,
			'referer' => wp_get_referer() ?: '',
		]);
	}

	/**
	 * If search term (?s=) exactly matches a product or variation SKU, redirect to that product.
	 * Only runs when the "Product Search by SKU" add-on is enabled.
	 */
	public static function maybe_redirect_search_to_product_by_sku(): void {
		if (is_admin() || !is_search()) {
			return;
		}

		$raw = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
		$raw = trim($raw);
		if ($raw === '') {
			return;
		}

		if (!function_exists('wc_get_product_id_by_sku')) {
			return;
		}

		$product_id = wc_get_product_id_by_sku($raw);

		if (!$product_id) {
			return;
		}

		$parent_id  = wp_get_post_parent_id($product_id);
		$target_id  = $parent_id ? $parent_id : $product_id;
		$permalink  = get_permalink($target_id);

		if ($permalink && is_string($permalink)) {
			wp_safe_redirect($permalink);
			exit;
		}
	}

	/**
	 * Apply a coupon from the URL parameter (e.g. ?coupon-code=SAVE10) when the cart is loaded.
	 * Only runs when "Apply Coupon Code via URL Parameter" is enabled.
	 *
	 * @param WC_Cart $cart Cart instance (unused but required by hook signature).
	 */
	public static function maybe_apply_coupon_from_url_param($cart): void {
		if (is_admin() && !wp_doing_ajax()) {
			return;
		}
		$settings = self::get_settings();
		if (empty($settings['coupon_code_url_param_enabled'])) {
			return;
		}
		$param_name = isset($settings['coupon_code_url_param_name']) && $settings['coupon_code_url_param_name'] !== ''
			? $settings['coupon_code_url_param_name']
			: 'coupon-code';
		$code = isset($_GET[ $param_name ]) ? sanitize_text_field(wp_unslash($_GET[ $param_name ])) : '';
		$code = trim($code);
		if ($code === '') {
			return;
		}
		static $applied = false;
		if ($applied) {
			return;
		}
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}
		$applied = true;
		WC()->cart->apply_coupon($code);
	}

	/**
	 * Log front-end search queries (?s=...) for reporting.
	 */
	public static function maybe_log_search_query(): void {
		$settings = self::get_settings();
		$enabled  = array_key_exists('enable_view_reports', $settings) ? !empty($settings['enable_view_reports']) : true;
		if (!$enabled) {
			return;
		}

		if (is_admin() || wp_doing_ajax()) {
			return;
		}

		if (defined('REST_REQUEST') && REST_REQUEST) {
			return;
		}

		if (!is_search()) {
			return;
		}

		$search_query = get_search_query(false);
		if ($search_query === '') {
			return;
		}

		$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
		$url         = $request_uri !== '' ? home_url($request_uri) : home_url(add_query_arg(null, null));

		// Detect post type context for the search (e.g., product, post, page, etc.).
		$post_type = get_query_var('post_type');
		if (is_array($post_type)) {
			$post_type = implode(',', array_map('sanitize_key', $post_type));
		} elseif (is_string($post_type) && $post_type !== '') {
			$post_type = sanitize_key($post_type);
		} else {
			$post_type = '';
		}

		$user_id = is_user_logged_in() ? (int) get_current_user_id() : 0;

		self::add_activity_log('search_query', $user_id, 'Search Monitor', [
			'query'     => $search_query,
			'url'       => $url,
			'referer'   => wp_get_referer() ?: '',
			'post_type' => $post_type,
		]);
	}

	/**
	 * Determine whether the current request matches any configured front-end triggers.
	 */
	private static function should_run_new_user_coupon_visit_check(array $settings): bool {
		if (!empty($settings['nuc_run_everywhere'])) {
			return true;
		}
		if (!empty($settings['nuc_run_my_account']) && function_exists('is_account_page') && is_account_page()) {
			return true;
		}
		if (!empty($settings['nuc_run_cart']) && function_exists('is_cart') && is_cart()) {
			return true;
		}
		if (!empty($settings['nuc_run_checkout']) && function_exists('is_checkout') && is_checkout()) {
			return true;
		}
		if (!empty($settings['nuc_run_product']) && function_exists('is_product') && is_product()) {
			return true;
		}
		if (!empty($settings['nuc_run_shop']) && function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
			return true;
		}
		if (!empty($settings['nuc_run_home']) && is_front_page()) {
			return true;
		}
		return false;
	}

	/**
	 * Whether the Coupon Remaining Balances add-on is active and allowed to run.
	 */
	private static function is_coupon_remainder_feature_enabled(?array $settings = null): bool {
		if ($settings === null) {
			$settings = self::get_settings();
		}
		return !empty($settings['coupon_remainder_enabled']) && !self::is_addon_temporarily_disabled('coupon-remaining-balances');
	}

	/**
	 * Queue remainder generation off checkout requests to avoid
	 * interfering with payment gateway order-processing responses.
	 */
	public static function queue_coupon_remainder_generation($order_id): void {
		$settings = self::get_settings();
		if (!self::is_coupon_remainder_feature_enabled($settings)) {
			return;
		}
		$order_id = absint($order_id);
		if ($order_id <= 0) {
			return;
		}

		$is_checkout_request = function_exists('is_checkout') && is_checkout();
		$is_wc_checkout_ajax = wp_doing_ajax()
			&& isset($_REQUEST['wc-ajax'])
			&& in_array(sanitize_key((string) wp_unslash($_REQUEST['wc-ajax'])), ['checkout', 'update_order_review'], true);

		$hook = 'user_manager_process_coupon_remainder_async';
		if ($is_checkout_request || $is_wc_checkout_ajax) {
			if (!wp_next_scheduled($hook, [$order_id])) {
				wp_schedule_single_event(time() + 15, $hook, [$order_id]);
			}
			return;
		}

		self::maybe_generate_fixed_cart_coupon_remainders($order_id);
	}

	/**
	 * Determine if the user has completed at least one WooCommerce order.
	 */
	private static function user_has_completed_first_order(int $user_id): bool {
		if (!function_exists('wc_get_orders')) {
			return false;
		}
		$orders = wc_get_orders([
			'customer' => $user_id,
			'return' => 'ids',
			'limit' => 1,
		]);
		return !empty($orders);
	}

	/**
	 * Create fixed cart remainder coupons after checkout when enabled.
	 */
	public static function maybe_generate_fixed_cart_coupon_remainders($order_id): void {
		$order_id = absint($order_id);
		if ($order_id <= 0) {
			return;
		}
		$settings = self::get_settings();
		if (!self::is_coupon_remainder_feature_enabled($settings)) {
			return;
		}
		if (!function_exists('wc_get_order')) {
			return;
		}
		$lock_key = '_um_coupon_remainder_processing_lock';
		$lock_acquired = false;
		if (add_post_meta($order_id, $lock_key, time(), true)) {
			$lock_acquired = true;
		} else {
			$existing_lock = (int) get_post_meta($order_id, $lock_key, true);
			// Stale lock recovery (e.g., interrupted request/crash).
			if ($existing_lock > 0 && (time() - $existing_lock) < 300) {
				return;
			}
			update_post_meta($order_id, $lock_key, time());
			$lock_acquired = true;
		}
		try {
			$order = wc_get_order($order_id);
			if (!$order || !method_exists($order, 'get_coupon_codes')) {
				return;
			}
			$codes = $order->get_coupon_codes();
			if (empty($codes)) {
				return;
			}

			$processed = get_post_meta($order_id, '_um_coupon_remainder_processed', true);
			if (!is_array($processed)) {
				$processed = [];
			}

			$min_remaining = isset($settings['coupon_remainder_min_amount']) ? max(0, (float) $settings['coupon_remainder_min_amount']) : 0;
			$generated_prefix = isset($settings['coupon_remainder_generated_prefix']) && $settings['coupon_remainder_generated_prefix'] !== ''
				? trim((string) $settings['coupon_remainder_generated_prefix'])
				: 'remaining-balance-';

			// Always use billing email from the order, never fall back to logged-in user email
			$user_email = $order->get_billing_email();
			if (empty($user_email)) {
				// Only use billing email meta as a last resort if get_billing_email() returns empty
				$user_email = $order->get_meta('_billing_email');
			}

			$processed_updated = false;
			$should_debug = !empty($settings['coupon_remainder_debug']) && current_user_can('manage_options');
			$debug_messages = [];

			foreach ($codes as $code) {
				$code_key = strtolower($code);
				if (!empty($processed[$code_key])) {
					continue;
				}

				$coupon = new WC_Coupon($code);
				if (!$coupon || $coupon->get_discount_type() !== 'fixed_cart') {
					continue;
				}

				// Check if coupon code matches source requirements (prefix, contains, suffix)
				if (!self::coupon_code_matches_source_requirements($code, $settings)) {
					continue;
				}

				// Calculate remaining amount and get discount details
				$discount_details = self::get_coupon_discount_details($order, $code);
				$remaining = self::calculate_coupon_remaining_amount($order, $coupon, $code);
				if ($remaining <= 0 || $remaining < $min_remaining) {
					continue;
				}

				$new_code = self::create_remainder_coupon_from_source($coupon, $order, $remaining, $generated_prefix, $user_email);
				if (!$new_code) {
					continue;
				}

				if (!empty($settings['coupon_remainder_send_email']) && !empty($user_email) && is_email($user_email)) {
					$template_id = isset($settings['coupon_remainder_email_template']) ? (string) $settings['coupon_remainder_email_template'] : '__um_default__';
					self::send_coupon_email_to_address((string) $user_email, (string) $new_code, $template_id);
				}

				$processed[$code_key] = $new_code;
				$processed_updated = true;

				// Create detailed private order note
				$original_amount = (float) $coupon->get_amount();
				$discount_used = $discount_details['discount'];
				// Only use discount amount, not discount tax (matches how WooCommerce calculates cart totals)
				
				$note = sprintf(
					/* translators: 1: new coupon code, 2: remaining value */
					__("New Remaining Balance Coupon Code Created: %1\$s with value %2\$s\n\n", 'user-manager'),
					strtoupper($new_code),
					wc_price($remaining)
				);
				
				$note .= __('Here is how the remaining balance was calculated:', 'user-manager') . "\n";
				$note .= sprintf(
					/* translators: 1: original amount */
					__('Original Coupon Amount: %s', 'user-manager'),
					wc_price($original_amount)
				) . "\n";
				$note .= sprintf(
					/* translators: 1: discount amount */
					__('Discount Applied: %s', 'user-manager'),
					wc_price($discount_used)
				) . "\n";
				$note .= sprintf(
					/* translators: 1: calculation formula, 2: remaining amount */
					__('Calculation: %1$s - %2$s = %3$s', 'user-manager'),
					wc_price($original_amount),
					wc_price($discount_used),
					wc_price($remaining)
				);
				
				// Add as private note (second parameter false = private, true = customer-facing)
				$order->add_order_note($note, false);

				$user_id = $order->get_user_id() ? (int) $order->get_user_id() : 0;
				self::add_activity_log('coupon_remainder_created', $user_id, 'Coupons', [
					'order_id' => (int) $order->get_id(),
					'source_coupon' => $code,
					'new_coupon' => $new_code,
					'remaining' => $remaining,
				]);

				if ($should_debug) {
					$debug_messages[] = sprintf(
						/* translators: 1: old code, 2: new code, 3: amount */
						__('Converted %1$s to %2$s with %3$s remaining.', 'user-manager'),
						strtoupper($code),
						$new_code,
						wc_price($remaining)
					);
				}
			}

			if ($processed_updated) {
				update_post_meta($order_id, '_um_coupon_remainder_processed', $processed);
				// Record in User Activity (Login History tab) so the user has a visible record when a remaining balance code was created for them.
				$activity_user_id = $order->get_user_id() ? (int) $order->get_user_id() : 0;
				if ($activity_user_id <= 0 && !empty($user_email)) {
					$user_by_email = get_user_by('email', $user_email);
					if ($user_by_email) {
						$activity_user_id = (int) $user_by_email->ID;
					}
				}
				if ($activity_user_id > 0) {
					$order_received_url = '';
					if (function_exists('wc_get_checkout_url')) {
						$order_received_url = wc_get_endpoint_url('order-received', $order_id, wc_get_checkout_url());
						if (method_exists($order, 'get_order_key')) {
							$order_received_url = add_query_arg('key', $order->get_order_key(), $order_received_url);
						}
					}
					self::add_user_activity($activity_user_id, __('Remaining balance code created', 'user-manager'), $order_received_url);
				}
			} elseif ($should_debug) {
				$debug_messages[] = __('No remainder coupons were created for this order.', 'user-manager');
			}

			if ($should_debug) {
				self::$coupon_remainder_debug_messages[(int) $order_id] = $debug_messages;
			}
		} catch (\Throwable $throwable) {
			self::maybe_debug_log('Coupon remainder processing failed', [
				'order_id' => (int) $order_id,
				'message' => $throwable->getMessage(),
				'file' => $throwable->getFile(),
				'line' => $throwable->getLine(),
			]);
		} finally {
			if ($lock_acquired) {
				delete_post_meta($order_id, $lock_key);
			}
		}
	}

	/**
	 * Check if a coupon code matches the source prefix, contains, or suffix requirements.
	 * 
	 * @param string $code The coupon code to check
	 * @param array $settings The plugin settings array
	 * @return bool True if the coupon matches all specified conditions (or if no conditions are set)
	 */
	/**
	 * Get detailed matching information for a coupon code against source requirements.
	 * Returns an array with matching details for debugging.
	 * 
	 * @param string $code Coupon code to check
	 * @param array $settings Plugin settings
	 * @return array Matching details
	 */
	private static function get_coupon_matching_details(string $code, array $settings): array {
		// Get prefix requirements
		$prefix_lines = '';
		if (!empty($settings['coupon_remainder_source_prefixes'])) {
			$prefix_lines = (string) $settings['coupon_remainder_source_prefixes'];
		} elseif (!empty($settings['coupon_remainder_source_prefix'])) {
			$prefix_lines = (string) $settings['coupon_remainder_source_prefix'];
		}
		$source_prefixes = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $prefix_lines)));
		
		// Get contains requirements
		$contains_lines = !empty($settings['coupon_remainder_source_contains']) ? (string) $settings['coupon_remainder_source_contains'] : '';
		$source_contains = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $contains_lines)));
		
		// Get suffix requirements
		$suffix_lines = !empty($settings['coupon_remainder_source_suffixes']) ? (string) $settings['coupon_remainder_source_suffixes'] : '';
		$source_suffixes = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $suffix_lines)));
		
		$details = [
			'code' => $code,
			'prefixes' => $source_prefixes,
			'contains' => $source_contains,
			'suffixes' => $source_suffixes,
			'matches' => false,
			'prefix_match' => null,
			'contains_match' => null,
			'suffix_match' => null,
			'failure_reason' => '',
		];
		
		// If no requirements are set, allow all
		if (empty($source_prefixes) && empty($source_contains) && empty($source_suffixes)) {
			$details['matches'] = true;
			$details['failure_reason'] = 'No requirements set - all coupons allowed';
			return $details;
		}
		
		// Check prefix (OR logic - check all for debugging, but any match is sufficient)
		if (!empty($source_prefixes)) {
			$matches_prefix = false;
			$matched_prefix = null;
			foreach ($source_prefixes as $prefix) {
				if ($prefix !== '' && stripos($code, $prefix) === 0) {
					$matches_prefix = true;
					$matched_prefix = $prefix;
					break;
				}
			}
			$details['prefix_match'] = $matches_prefix ? $matched_prefix : false;
		}
		
		// Check contains (OR logic - check all for debugging, but any match is sufficient)
		if (!empty($source_contains)) {
			$matches_contains = false;
			$matched_contains = [];
			foreach ($source_contains as $contains) {
				if ($contains !== '' && stripos($code, $contains) !== false) {
					$matches_contains = true;
					$matched_contains[] = $contains;
					// Don't break - collect all matches for better debugging
				}
			}
			$details['contains_match'] = $matches_contains ? (count($matched_contains) === 1 ? $matched_contains[0] : $matched_contains) : false;
		}
		
		// Check suffix (OR logic - check all for debugging, but any match is sufficient)
		if (!empty($source_suffixes)) {
			$matches_suffix = false;
			$matched_suffix = null;
			$code_lower = strtolower($code);
			foreach ($source_suffixes as $suffix) {
				if ($suffix !== '') {
					$suffix_lower = strtolower($suffix);
					$suffix_length = strlen($suffix_lower);
					if ($suffix_length > 0 && substr($code_lower, -$suffix_length) === $suffix_lower) {
						$matches_suffix = true;
						$matched_suffix = $suffix;
						break;
					}
				}
			}
			$details['suffix_match'] = $matches_suffix ? $matched_suffix : false;
		}
		
		// Determine overall match status using OR logic (any requirement can match)
		$any_check_passed = false;
		$passed_checks = [];
		
		if (!empty($source_prefixes) && $details['prefix_match'] !== false && $details['prefix_match'] !== null) {
			$any_check_passed = true;
			$passed_checks[] = 'prefix';
		}
		if (!empty($source_contains) && $details['contains_match'] !== false && $details['contains_match'] !== null) {
			$any_check_passed = true;
			$passed_checks[] = 'contains';
		}
		if (!empty($source_suffixes) && $details['suffix_match'] !== false && $details['suffix_match'] !== null) {
			$any_check_passed = true;
			$passed_checks[] = 'suffix';
		}
		
		// If no requirements are configured, allow all
		if (empty($source_prefixes) && empty($source_contains) && empty($source_suffixes)) {
			$any_check_passed = true;
		}
		
		$details['matches'] = $any_check_passed;
		if ($any_check_passed) {
			if (count($passed_checks) > 0) {
				$details['failure_reason'] = 'Matches requirement(s): ' . implode(', ', $passed_checks);
			} else {
				$details['failure_reason'] = 'Matches (no requirements configured)';
			}
		} else {
			$failed_checks = [];
			if (!empty($source_prefixes)) $failed_checks[] = 'prefix';
			if (!empty($source_contains)) $failed_checks[] = 'contains';
			if (!empty($source_suffixes)) $failed_checks[] = 'suffix';
			$details['failure_reason'] = 'Does not match any requirement (' . implode(' OR ', $failed_checks) . ')';
		}
		return $details;
	}

	private static function coupon_code_matches_source_requirements(string $code, array $settings): bool {
		// Get prefix requirements
		$prefix_lines = '';
		if (!empty($settings['coupon_remainder_source_prefixes'])) {
			$prefix_lines = (string) $settings['coupon_remainder_source_prefixes'];
		} elseif (!empty($settings['coupon_remainder_source_prefix'])) {
			$prefix_lines = (string) $settings['coupon_remainder_source_prefix'];
		}
		$source_prefixes = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $prefix_lines)));
		
		// Get contains requirements
		$contains_lines = !empty($settings['coupon_remainder_source_contains']) ? (string) $settings['coupon_remainder_source_contains'] : '';
		$source_contains = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $contains_lines)));
		
		// Get suffix requirements
		$suffix_lines = !empty($settings['coupon_remainder_source_suffixes']) ? (string) $settings['coupon_remainder_source_suffixes'] : '';
		$source_suffixes = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $suffix_lines)));
		
		// If no requirements are set, allow all
		if (empty($source_prefixes) && empty($source_contains) && empty($source_suffixes)) {
			return true;
		}
		
		// Use OR logic: match if ANY requirement type matches
		// Check prefix (matches if prefixes are specified and code starts with any prefix)
		if (!empty($source_prefixes)) {
			foreach ($source_prefixes as $prefix) {
				if ($prefix !== '' && stripos($code, $prefix) === 0) {
					return true;
				}
			}
		}
		
		// Check contains (matches if contains are specified and code contains any string)
		if (!empty($source_contains)) {
			foreach ($source_contains as $contains) {
				if ($contains !== '' && stripos($code, $contains) !== false) {
					return true;
				}
			}
		}
		
		// Check suffix (matches if suffixes are specified and code ends with any suffix)
		if (!empty($source_suffixes)) {
			$code_lower = strtolower($code);
			foreach ($source_suffixes as $suffix) {
				if ($suffix !== '') {
					$suffix_lower = strtolower($suffix);
					$suffix_length = strlen($suffix_lower);
					if ($suffix_length > 0 && substr($code_lower, -$suffix_length) === $suffix_lower) {
						return true;
					}
				}
			}
		}
		
		// If we get here, none of the requirements matched
		return false;
	}

	private static function calculate_coupon_remaining_amount(WC_Order $order, WC_Coupon $coupon, string $code): float {
		$discount_details = self::get_coupon_discount_details($order, $code);
		// Only use discount amount, not discount tax (matches how WooCommerce calculates cart totals)
		$discount_used = $discount_details['discount'];

		$amount = (float) $coupon->get_amount();
		$remaining = $amount - $discount_used;
		$decimals = wc_get_price_decimals();
		return $remaining > 0 ? (float) wc_format_decimal($remaining, $decimals) : 0.0;
	}

	/**
	 * Get discount details for a coupon used in an order.
	 */
	private static function get_coupon_discount_details(WC_Order $order, string $code): array {
		$discount = 0.0;
		$discount_tax = 0.0;
		
		foreach ($order->get_items('coupon') as $item) {
			if (strcasecmp($item->get_code(), $code) === 0) {
				$discount += (float) $item->get_discount();
				$discount_tax += (float) $item->get_discount_tax();
			}
		}
		
		return [
			'discount' => $discount,
			'discount_tax' => $discount_tax,
		];
	}

	private static function create_remainder_coupon_from_source(WC_Coupon $source, WC_Order $order, float $amount, string $prefix, ?string $user_email): ?string {
		$base_code = self::sanitize_coupon_fragment($source->get_code());
		$new_code = self::generate_remainder_coupon_code($prefix, $base_code, (int) $order->get_id());
		$attempts = 0;
		while (wc_get_coupon_id_by_code($new_code) && $attempts < 5) {
			$attempts++;
			$new_code = self::generate_remainder_coupon_code($prefix, $base_code, (int) $order->get_id(), $attempts);
		}
		if (wc_get_coupon_id_by_code($new_code)) {
			return null;
		}

		$new_coupon_id = wp_insert_post([
			'post_title' => $new_code,
			'post_status' => 'publish',
			'post_author' => get_current_user_id() ?: 1,
			'post_type' => 'shop_coupon',
		]);

		if (is_wp_error($new_coupon_id) || !$new_coupon_id) {
			return null;
		}

		$new_coupon = new WC_Coupon($new_coupon_id);
		$new_coupon->set_discount_type('fixed_cart');
		$new_coupon->set_amount($amount);
		$new_coupon->set_usage_limit(1);
		$new_coupon->set_usage_limit_per_user(1);
		$source_total = method_exists($source, 'get_amount') ? (float) $source->get_amount() : 0;
		$used_amount = $source_total ? max(0, $source_total - $amount) : 0;
		
		// Format prices without HTML for description
		$currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$';
		$formatted_source_total = $currency_symbol . number_format($source_total, 2, '.', ',');
		$formatted_used_amount = $currency_symbol . number_format($used_amount, 2, '.', ',');
		$formatted_remaining = $currency_symbol . number_format($amount, 2, '.', ',');
		
		$description = sprintf(
			/* translators: 1: original code, 2: total amount, 3: used amount, 4: remaining amount */
			__('Remaining balance of %1$s. Original value %2$s, used %3$s, remaining %4$s.', 'user-manager'),
			$source->get_code(),
			$formatted_source_total,
			$formatted_used_amount,
			$formatted_remaining
		);
		$new_coupon->set_description($description);

		// Copy expiration date from source coupon if setting is enabled and source has an expiration.
		$settings = self::get_settings();
		if (!empty($settings['coupon_remainder_copy_expiration']) && method_exists($source, 'get_date_expires') && method_exists($new_coupon, 'set_date_expires')) {
			$source_expires = $source->get_date_expires();
			if ($source_expires) {
				$new_coupon->set_date_expires($source_expires);
			}
		}

		// Apply free shipping to remainder coupon if setting is enabled.
		if (!empty($settings['coupon_remainder_free_shipping']) && method_exists($new_coupon, 'set_free_shipping')) {
			$new_coupon->set_free_shipping(true);
		}

		if ($user_email) {
			$new_coupon->set_email_restrictions([$user_email]);
			update_post_meta($new_coupon_id, '_um_user_coupon_user_email', $user_email);
		}

		update_post_meta($new_coupon_id, '_um_user_coupon_user_id', $order->get_user_id());
		update_post_meta($new_coupon_id, '_um_user_coupon_source_code', $source->get_code());

		$new_coupon->save();

		$calc_note = sprintf(
			/* translators: 1: amount */
			__('Remaining balance: %s', 'user-manager'),
			wc_price($amount)
		);

		$note_details = sprintf(
			/* translators: 1: total, 2: used, 3: remaining */
			__('Original value %1$s, used %2$s, remaining %3$s.', 'user-manager'),
			wc_price($source_total),
			wc_price($used_amount),
			wc_price($amount)
		);

		self::add_coupon_note($source->get_id(), sprintf(
			/* translators: 1: new code, 2: calculation, 3: details */
			__('Generated remainder coupon %1$s. %2$s %3$s', 'user-manager'),
			$new_code,
			$calc_note,
			$note_details
		));

		self::add_coupon_note($new_coupon_id, sprintf(
			/* translators: 1: original code, 2: calculation, 3: details */
			__('Created from %1$s. %2$s %3$s', 'user-manager'),
			$source->get_code(),
			$calc_note,
			$note_details
		));

		return $new_code;
	}

	private static function generate_remainder_coupon_code(string $prefix, string $source_code, int $order_id, int $attempt = 0): string {
		$clean_source = self::sanitize_coupon_fragment($source_code);
		$clean_prefix = self::sanitize_coupon_fragment($prefix);
		$trimmed_prefix = rtrim($clean_prefix, '-');
		if ($trimmed_prefix !== '' && strpos($clean_source, $trimmed_prefix) === 0) {
			$clean_source = ltrim(substr($clean_source, strlen($trimmed_prefix)), '-');
		}
		$base = $clean_source !== '' ? $clean_source : 'COUPON';
		$generated = sprintf('%s%s-%d', $prefix, $base, $order_id);
		if ($attempt > 0) {
			$generated .= '-' . $attempt;
		}
		return strtoupper($generated);
	}

	private static function sanitize_coupon_fragment(string $value): string {
		$sanitized = preg_replace('/[^A-Za-z0-9\-]/', '', strtoupper($value));
		return $sanitized ?? '';
	}

	private static function add_coupon_note(int $coupon_id, string $message): void {
		wp_insert_comment([
			'comment_post_ID' => $coupon_id,
			'comment_content' => $message,
			'comment_type' => 'coupon_note',
			'user_id' => get_current_user_id(),
			'comment_author' => __('User Manager', 'user-manager'),
			'comment_approved' => 1,
		]);
	}

	public static function handle_coupon_remainder_thankyou($order_id): void {
		if (!self::is_coupon_remainder_feature_enabled()) {
			return;
		}
		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}
		$status = $order->get_status();
		if (!in_array($status, ['processing', 'completed'], true)) {
			return;
		}
		self::maybe_generate_fixed_cart_coupon_remainders($order_id);
	}

	public static function render_coupon_remainder_debug_notice($order_id): void {
		$order_id = (int) $order_id;
		if ($order_id <= 0) {
			return;
		}
		$settings = self::get_settings();
		if (!self::is_coupon_remainder_feature_enabled($settings)) {
			return;
		}
		if (empty($settings['coupon_remainder_debug']) || !current_user_can('manage_options')) {
			return;
		}
		if (!isset(self::$coupon_remainder_debug_messages[$order_id])) {
			self::maybe_generate_fixed_cart_coupon_remainders($order_id);
		}
		$messages = self::$coupon_remainder_debug_messages[$order_id] ?? [];
		if (empty($messages)) {
			return;
		}
		echo '<div class="notice notice-info" style="margin:15px 0;padding:12px 15px;border-left:4px solid #2271b1;background:#f0f6ff;">';
		echo '<p><strong>' . esc_html__('Remainder Coupon Debug:', 'user-manager') . '</strong></p><ul>';
		foreach ($messages as $message) {
			echo '<li>' . esc_html($message) . '</li>';
		}
		echo '</ul></div>';
	}

	/**
	 * Render checkout page debug output for coupon remainder calculations.
	 */
	public static function render_checkout_coupon_remainder_debug(): void {
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}
		
		$settings = self::get_settings();
		if (!self::is_coupon_remainder_feature_enabled($settings)) {
			return;
		}
		if (empty($settings['coupon_remainder_checkout_debug']) || !is_user_logged_in()) {
			return;
		}
		
		if (!is_checkout()) {
			return;
		}
		
		$cart = WC()->cart;
		$applied_coupons = $cart->get_applied_coupons();
		
		if (empty($applied_coupons)) {
			echo '<div class="um-coupon-remainder-checkout-debug" style="margin:20px 0;padding:15px;background:#f0f6ff;border:1px solid #2271b1;border-left:4px solid #2271b1;border-radius:4px;">';
			echo '<p><strong>' . esc_html__('Remaining Balance Coupon Debug:', 'user-manager') . '</strong></p>';
			echo '<p>' . esc_html__('No coupons applied to cart.', 'user-manager') . '</p>';
			echo '</div>';
			return;
		}
		
		$min_remaining = isset($settings['coupon_remainder_min_amount']) ? max(0, (float) $settings['coupon_remainder_min_amount']) : 0;
		
		echo '<div class="um-coupon-remainder-checkout-debug" style="margin:20px 0;padding:15px;background:#f0f6ff;border:1px solid #2271b1;border-left:4px solid #2271b1;border-radius:4px;">';
		echo '<p><strong>' . esc_html__('Remaining Balance Coupon Debug:', 'user-manager') . '</strong></p>';
		
		echo '<p><strong>' . esc_html__('Current Coupons in Cart:', 'user-manager') . '</strong></p>';
		echo '<ul style="margin:10px 0;padding-left:20px;">';
		foreach ($applied_coupons as $code) {
			$coupon = new WC_Coupon($code);
			if (!$coupon || !$coupon->get_id()) {
				echo '<li>' . esc_html(strtoupper($code)) . ' - ' . esc_html__('Invalid coupon', 'user-manager') . '</li>';
				continue;
			}
			
			$coupon_amount = $coupon->get_amount();
			$discount_type = $coupon->get_discount_type();
			$is_fixed_cart = $discount_type === 'fixed_cart';
			
			echo '<li><strong>' . esc_html(strtoupper($code)) . '</strong>';
			echo ' - ' . esc_html($discount_type === 'fixed_cart' ? __('Fixed Cart', 'user-manager') : ucwords(str_replace('_', ' ', $discount_type)));
			echo ' - ' . wc_price($coupon_amount);
			
			if (!$is_fixed_cart) {
				echo ' <em>(' . esc_html__('Not a fixed cart coupon, will be skipped', 'user-manager') . ')</em>';
			} elseif (!self::coupon_code_matches_source_requirements($code, $settings)) {
				echo ' <em>(' . esc_html__('Does not match source requirements, will be skipped', 'user-manager') . ')</em>';
			}
			echo '</li>';
		}
		echo '</ul>';
		
		// Calculate what would happen
		echo '<p><strong>' . esc_html__('Remaining Balance Calculations:', 'user-manager') . '</strong></p>';
		echo '<ul style="margin:10px 0;padding-left:20px;">';
		
		$has_eligible_coupons = false;
		foreach ($applied_coupons as $code) {
			$coupon = new WC_Coupon($code);
			if (!$coupon || !$coupon->get_id() || $coupon->get_discount_type() !== 'fixed_cart') {
				continue;
			}
			
			// Check if coupon code matches source requirements (prefix, contains, suffix)
			if (!self::coupon_code_matches_source_requirements($code, $settings)) {
				continue;
			}
			
			$has_eligible_coupons = true;
			$original_amount = (float) $coupon->get_amount();
			
			// Get discount from cart (only discount amount, not discount tax - matches cart totals display)
			$discount_used = 0.0;
			foreach ($cart->get_coupon_discount_totals() as $coupon_code => $discount) {
				if (strcasecmp($coupon_code, $code) === 0) {
					$discount_used = (float) $discount;
					break;
				}
			}
			
			// Only use discount amount, not discount tax (matches how WooCommerce calculates cart totals)
			$remaining = $original_amount - $discount_used;
			$decimals = wc_get_price_decimals();
			$remaining = $remaining > 0 ? (float) wc_format_decimal($remaining, $decimals) : 0.0;
			
			// Generate preview of new coupon code
			$generated_prefix = isset($settings['coupon_remainder_generated_prefix']) && $settings['coupon_remainder_generated_prefix'] !== ''
				? trim((string) $settings['coupon_remainder_generated_prefix'])
				: 'remaining-balance-';
			$base_code = self::sanitize_coupon_fragment($code);
			$clean_prefix = self::sanitize_coupon_fragment($generated_prefix);
			$trimmed_prefix = rtrim($clean_prefix, '-');
			if ($trimmed_prefix !== '' && strpos($base_code, $trimmed_prefix) === 0) {
				$base_code = ltrim(substr($base_code, strlen($trimmed_prefix)), '-');
			}
			$base = $base_code !== '' ? $base_code : 'COUPON';
			$preview_code = strtoupper($generated_prefix . $base . '-ORDER_ID');
			
			echo '<li><strong>' . esc_html(strtoupper($code)) . ':</strong><br>';
			echo '&nbsp;&nbsp;' . esc_html__('Original Amount:', 'user-manager') . ' ' . wc_price($original_amount) . '<br>';
			echo '&nbsp;&nbsp;' . esc_html__('Discount Applied:', 'user-manager') . ' ' . wc_price($discount_used) . '<br>';
			echo '&nbsp;&nbsp;' . esc_html__('Calculation:', 'user-manager') . ' ' . wc_price($original_amount) . ' - ' . wc_price($discount_used) . ' = <strong>' . wc_price($remaining) . '</strong><br>';
			
			if ($remaining <= 0) {
				echo '&nbsp;&nbsp;<em>' . esc_html__('No remaining balance - coupon will be fully used', 'user-manager') . '</em>';
			} elseif ($remaining < $min_remaining) {
				echo '&nbsp;&nbsp;<em>' . sprintf(
					/* translators: 1: remaining amount, 2: minimum amount */
					esc_html__('Remaining balance (%1$s) is below minimum threshold (%2$s) - no remainder coupon will be created', 'user-manager'),
					wc_price($remaining),
					wc_price($min_remaining)
				) . '</em>';
			} else {
				echo '&nbsp;&nbsp;<strong style="color:#155724;">' . sprintf(
					/* translators: 1: remaining amount */
					esc_html__('✓ Remaining balance coupon will be created with value: %s', 'user-manager'),
					wc_price($remaining)
				) . '</strong><br>';
				echo '&nbsp;&nbsp;' . esc_html__('New Coupon Code (preview):', 'user-manager') . ' <code style="background:#fff;padding:2px 6px;border:1px solid #ddd;border-radius:3px;">' . esc_html($preview_code) . '</code><br>';
				echo '&nbsp;&nbsp;<em style="font-size:11px;color:#666;">' . esc_html__('Note: ORDER_ID will be replaced with the actual order ID when the order is placed. The final code may differ based on when the order is created.', 'user-manager') . '</em>';
			}
			echo '</li>';
		}
		
		if (!$has_eligible_coupons) {
			echo '<li><em>' . esc_html__('No eligible fixed cart coupons found that match the source prefix requirements.', 'user-manager') . '</em></li>';
		}
		
		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Render public debug output for coupons via URL parameter (debug_coupons=1).
	 * Shows all applied coupons and remaining balance calculations.
	 */
	public static function render_public_coupon_debug_output(): void {
		$settings = self::get_settings();
		if (!self::is_coupon_remainder_feature_enabled($settings)) {
			return;
		}
		// Check for URL parameter
		if (!isset($_GET['debug_coupons']) || $_GET['debug_coupons'] !== '1') {
			return;
		}

		// Only show for logged-in users
		if (!is_user_logged_in()) {
			return;
		}

		// Check if WooCommerce is active and cart exists
		if (!function_exists('WC') || !WC()->cart) {
			echo '<div class="um-coupon-public-debug" style="position:fixed;top:20px;right:20px;max-width:600px;max-height:80vh;overflow-y:auto;z-index:99999;background:#fff;border:3px solid #2271b1;border-radius:8px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,0.3);font-family:monospace;font-size:13px;line-height:1.6;">';
			echo '<h3 style="margin-top:0;color:#2271b1;">' . esc_html__('Coupon Debug Output', 'user-manager') . '</h3>';
			echo '<p style="color:#d63638;"><strong>' . esc_html__('Error:', 'user-manager') . '</strong> ' . esc_html__('WooCommerce cart is not available.', 'user-manager') . '</p>';
			echo '</div>';
			return;
		}

		$cart = WC()->cart;
		$applied_coupons = $cart->get_applied_coupons();
		$min_remaining = isset($settings['coupon_remainder_min_amount']) ? max(0, (float) $settings['coupon_remainder_min_amount']) : 0;
		$generated_prefix = isset($settings['coupon_remainder_generated_prefix']) && $settings['coupon_remainder_generated_prefix'] !== ''
			? trim((string) $settings['coupon_remainder_generated_prefix'])
			: 'remaining-balance-';

		// Start output
		echo '<div class="um-coupon-public-debug" style="position:fixed;top:20px;right:20px;max-width:600px;max-height:80vh;overflow-y:auto;z-index:99999;background:#fff;border:3px solid #2271b1;border-radius:8px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,0.3);font-family:monospace;font-size:13px;line-height:1.6;">';
		
		// Header
		echo '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;padding-bottom:10px;border-bottom:2px solid #2271b1;">';
		echo '<h3 style="margin:0;color:#2271b1;font-size:18px;">' . esc_html__('🔍 Coupon Debug Output', 'user-manager') . '</h3>';
		echo '<button onclick="this.parentElement.parentElement.style.display=\'none\'" style="background:#d63638;color:#fff;border:none;border-radius:4px;padding:5px 10px;cursor:pointer;font-size:12px;">' . esc_html__('Close', 'user-manager') . '</button>';
		echo '</div>';

		// Cart summary
		echo '<div style="margin-bottom:15px;padding:10px;background:#f0f6ff;border-radius:4px;">';
		echo '<strong>' . esc_html__('Cart Summary:', 'user-manager') . '</strong><br>';
		echo esc_html__('Subtotal:', 'user-manager') . ' ' . wc_price($cart->get_subtotal()) . '<br>';
		echo esc_html__('Total Discount:', 'user-manager') . ' ' . wc_price($cart->get_total_discount()) . '<br>';
		echo esc_html__('Cart Total:', 'user-manager') . ' <strong>' . wc_price($cart->get_total()) . '</strong>';
		echo '</div>';

		// Source requirements configuration
		$prefix_lines = '';
		if (!empty($settings['coupon_remainder_source_prefixes'])) {
			$prefix_lines = (string) $settings['coupon_remainder_source_prefixes'];
		} elseif (!empty($settings['coupon_remainder_source_prefix'])) {
			$prefix_lines = (string) $settings['coupon_remainder_source_prefix'];
		}
		$source_prefixes = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $prefix_lines)));
		
		$contains_lines = !empty($settings['coupon_remainder_source_contains']) ? (string) $settings['coupon_remainder_source_contains'] : '';
		$source_contains = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $contains_lines)));
		
		$suffix_lines = !empty($settings['coupon_remainder_source_suffixes']) ? (string) $settings['coupon_remainder_source_suffixes'] : '';
		$source_suffixes = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $suffix_lines)));

		echo '<div style="margin-bottom:15px;padding:10px;background:#fff3cd;border-left:4px solid #ffc107;border-radius:4px;">';
		echo '<strong style="color:#856404;font-size:14px;">' . esc_html__('Source Requirements Configuration (OR Logic):', 'user-manager') . '</strong><br>';
		echo '<em style="color:#856404;font-size:11px;">' . esc_html__('A coupon matches if it meets ANY requirement type (Prefix OR Contains OR Suffix)', 'user-manager') . '</em><br>';
		if (empty($source_prefixes) && empty($source_contains) && empty($source_suffixes)) {
			echo '<div style="margin-top:8px;"><em style="color:#856404;">' . esc_html__('No requirements set - all fixed cart coupons are eligible', 'user-manager') . '</em></div>';
		} else {
			if (!empty($source_prefixes)) {
				echo '<div style="margin-top:8px;"><strong>' . esc_html__('Prefixes (OR):', 'user-manager') . '</strong><br>';
				echo '<code style="background:#fff;padding:2px 6px;border:1px solid #ddd;border-radius:3px;font-size:11px;display:inline-block;margin:2px;">' . esc_html(implode('</code> <code style="background:#fff;padding:2px 6px;border:1px solid #ddd;border-radius:3px;font-size:11px;display:inline-block;margin:2px;">', $source_prefixes)) . '</code></div>';
			}
			if (!empty($source_contains)) {
				echo '<div style="margin-top:8px;"><strong>' . esc_html__('Contains (OR):', 'user-manager') . '</strong><br>';
				echo '<code style="background:#fff;padding:2px 6px;border:1px solid #ddd;border-radius:3px;font-size:11px;display:inline-block;margin:2px;">' . esc_html(implode('</code> <code style="background:#fff;padding:2px 6px;border:1px solid #ddd;border-radius:3px;font-size:11px;display:inline-block;margin:2px;">', $source_contains)) . '</code></div>';
			}
			if (!empty($source_suffixes)) {
				echo '<div style="margin-top:8px;"><strong>' . esc_html__('Suffixes (OR):', 'user-manager') . '</strong><br>';
				echo '<code style="background:#fff;padding:2px 6px;border:1px solid #ddd;border-radius:3px;font-size:11px;display:inline-block;margin:2px;">' . esc_html(implode('</code> <code style="background:#fff;padding:2px 6px;border:1px solid #ddd;border-radius:3px;font-size:11px;display:inline-block;margin:2px;">', $source_suffixes)) . '</code></div>';
			}
		}
		echo '</div>';

		// Applied coupons section
		if (empty($applied_coupons)) {
			echo '<div style="margin-bottom:15px;padding:10px;background:#fff3cd;border-left:4px solid #ffc107;border-radius:4px;">';
			echo '<strong>' . esc_html__('Applied Coupons:', 'user-manager') . '</strong><br>';
			echo '<em>' . esc_html__('No coupons applied to cart.', 'user-manager') . '</em>';
			echo '</div>';
		} else {
			echo '<div style="margin-bottom:15px;">';
			echo '<strong style="color:#2271b1;font-size:14px;">' . esc_html__('Applied Coupons:', 'user-manager') . '</strong>';
			echo '<ul style="margin:10px 0;padding-left:20px;">';
			
			foreach ($applied_coupons as $code) {
				$coupon = new WC_Coupon($code);
				if (!$coupon || !$coupon->get_id()) {
					echo '<li style="margin-bottom:8px;"><strong>' . esc_html(strtoupper($code)) . '</strong> - <span style="color:#d63638;">' . esc_html__('Invalid coupon', 'user-manager') . '</span></li>';
					continue;
				}
				
				$coupon_amount = $coupon->get_amount();
				$discount_type = $coupon->get_discount_type();
				$is_fixed_cart = $discount_type === 'fixed_cart';
				
				// Get discount used from cart
				$discount_used = 0.0;
				foreach ($cart->get_coupon_discount_totals() as $coupon_code => $discount) {
					if (strcasecmp($coupon_code, $code) === 0) {
						$discount_used = (float) $discount;
						break;
					}
				}
				
				// Get detailed matching information
				$matching_details = self::get_coupon_matching_details($code, $settings);
				$matches_requirements = $matching_details['matches'];
				
				echo '<li style="margin-bottom:12px;padding:8px;background:#f9f9f9;border-left:3px solid ' . ($is_fixed_cart ? ($matches_requirements ? '#155724' : '#d63638') : '#646970') . ';border-radius:3px;">';
				echo '<strong>' . esc_html(strtoupper($code)) . '</strong><br>';
				echo '&nbsp;&nbsp;' . esc_html__('Type:', 'user-manager') . ' ' . esc_html($discount_type === 'fixed_cart' ? __('Fixed Cart', 'user-manager') : ucwords(str_replace('_', ' ', $discount_type))) . '<br>';
				echo '&nbsp;&nbsp;' . esc_html__('Amount:', 'user-manager') . ' ' . wc_price($coupon_amount) . '<br>';
				echo '&nbsp;&nbsp;' . esc_html__('Discount Applied:', 'user-manager') . ' ' . wc_price($discount_used) . '<br>';
				
				if (!$is_fixed_cart) {
					echo '&nbsp;&nbsp;<em style="color:#646970;">' . esc_html__('Not a fixed cart coupon - will not generate remainder', 'user-manager') . '</em><br>';
				} else {
					// Show detailed matching information
					echo '<div style="margin-top:8px;padding:8px;background:#fff;border:1px solid ' . ($matches_requirements ? '#d4edda' : '#f8d7da') . ';border-radius:3px;font-size:12px;">';
					echo '<strong style="color:' . ($matches_requirements ? '#155724' : '#d63638') . ';">' . esc_html__('Matching Status:', 'user-manager') . '</strong> ';
					if ($matches_requirements) {
						echo '<span style="color:#155724;">✓ ' . esc_html($matching_details['failure_reason']) . '</span><br>';
					} else {
						echo '<span style="color:#d63638;">✗ ' . esc_html($matching_details['failure_reason']) . '</span><br>';
					}
					
					// Show prefix matching
					if (!empty($matching_details['prefixes'])) {
						echo '<div style="margin-top:6px;">';
						echo '<strong>' . esc_html__('Prefix Check:', 'user-manager') . '</strong> ';
						if ($matching_details['prefix_match'] !== null) {
							if ($matching_details['prefix_match'] !== false) {
								echo '<span style="color:#155724;">✓ Matches: <code>' . esc_html($matching_details['prefix_match']) . '</code></span>';
							} else {
								echo '<span style="color:#d63638;">✗ No match. Required: <code>' . esc_html(implode('</code>, <code>', $matching_details['prefixes'])) . '</code></span>';
							}
						} else {
							echo '<span style="color:#646970;">— Not checked (no prefixes configured)</span>';
						}
						echo '</div>';
					}
					
					// Show contains matching
					if (!empty($matching_details['contains'])) {
						echo '<div style="margin-top:6px;">';
						echo '<strong>' . esc_html__('Contains Check:', 'user-manager') . '</strong> ';
						if ($matching_details['contains_match'] !== null) {
							if ($matching_details['contains_match'] !== false) {
								$matches = is_array($matching_details['contains_match']) ? $matching_details['contains_match'] : [$matching_details['contains_match']];
								if (count($matches) === 1) {
									echo '<span style="color:#155724;">✓ Matches: <code>' . esc_html($matches[0]) . '</code></span>';
								} else {
									echo '<span style="color:#155724;">✓ Matches: <code>' . esc_html(implode('</code>, <code>', $matches)) . '</code></span>';
								}
							} else {
								echo '<span style="color:#d63638;">✗ No match. Required: <code>' . esc_html(implode('</code>, <code>', $matching_details['contains'])) . '</code></span>';
							}
						} else {
							echo '<span style="color:#646970;">— Not checked (no contains configured)</span>';
						}
						echo '</div>';
					}
					
					// Show suffix matching
					if (!empty($matching_details['suffixes'])) {
						echo '<div style="margin-top:6px;">';
						echo '<strong>' . esc_html__('Suffix Check:', 'user-manager') . '</strong> ';
						if ($matching_details['suffix_match'] !== null) {
							if ($matching_details['suffix_match'] !== false) {
								echo '<span style="color:#155724;">✓ Matches: <code>' . esc_html($matching_details['suffix_match']) . '</code></span>';
							} else {
								echo '<span style="color:#d63638;">✗ No match. Required: <code>' . esc_html(implode('</code>, <code>', $matching_details['suffixes'])) . '</code></span>';
							}
						} else {
							echo '<span style="color:#646970;">— Not checked (no suffixes configured)</span>';
						}
						echo '</div>';
					}
					
					echo '</div>';
				}
				
				echo '</li>';
			}
			
			echo '</ul>';
			echo '</div>';
		}

		// Remaining balance calculations
		echo '<div style="margin-bottom:15px;padding:10px;background:#f0f6ff;border-radius:4px;">';
		echo '<strong style="color:#2271b1;font-size:14px;">' . esc_html__('Remaining Balance Calculations:', 'user-manager') . '</strong>';
		
		if (empty($applied_coupons)) {
			echo '<p style="margin:10px 0 0 0;"><em>' . esc_html__('No coupons to calculate.', 'user-manager') . '</em></p>';
		} else {
			$has_eligible_coupons = false;
			$total_remaining = 0.0;
			$eligible_coupons = [];
			
			foreach ($applied_coupons as $code) {
				$coupon = new WC_Coupon($code);
				if (!$coupon || !$coupon->get_id() || $coupon->get_discount_type() !== 'fixed_cart') {
					continue;
				}
				
				// Check if coupon code matches source requirements
				if (!self::coupon_code_matches_source_requirements($code, $settings)) {
					continue;
				}
				
				$has_eligible_coupons = true;
				$original_amount = (float) $coupon->get_amount();
				
				// Get discount from cart
				$discount_used = 0.0;
				foreach ($cart->get_coupon_discount_totals() as $coupon_code => $discount) {
					if (strcasecmp($coupon_code, $code) === 0) {
						$discount_used = (float) $discount;
						break;
					}
				}
				
				// Calculate remaining
				$remaining = $original_amount - $discount_used;
				$decimals = wc_get_price_decimals();
				$remaining = $remaining > 0 ? (float) wc_format_decimal($remaining, $decimals) : 0.0;
				
				// Generate preview of new coupon code
				$base_code = self::sanitize_coupon_fragment($code);
				$clean_prefix = self::sanitize_coupon_fragment($generated_prefix);
				$trimmed_prefix = rtrim($clean_prefix, '-');
				if ($trimmed_prefix !== '' && strpos($base_code, $trimmed_prefix) === 0) {
					$base_code = ltrim(substr($base_code, strlen($trimmed_prefix)), '-');
				}
				$base = $base_code !== '' ? $base_code : 'COUPON';
				$preview_code = strtoupper($generated_prefix . $base . '-ORDER_ID');
				
				$eligible_coupons[] = [
					'code' => $code,
					'original' => $original_amount,
					'discount' => $discount_used,
					'remaining' => $remaining,
					'preview_code' => $preview_code,
				];
				
				if ($remaining > 0 && $remaining >= $min_remaining) {
					$total_remaining += $remaining;
				}
			}
			
			if (!$has_eligible_coupons) {
				echo '<p style="margin:10px 0 0 0;"><em>' . esc_html__('No eligible fixed cart coupons found that match the source requirements.', 'user-manager') . '</em></p>';
				// Debug: Show why no coupons matched
				$debug_matched = [];
				$debug_not_matched = [];
				foreach ($applied_coupons as $code) {
					$coupon = new WC_Coupon($code);
					if ($coupon && $coupon->get_id() && $coupon->get_discount_type() === 'fixed_cart') {
						if (self::coupon_code_matches_source_requirements($code, $settings)) {
							$debug_matched[] = $code;
						} else {
							$debug_not_matched[] = $code;
						}
					}
				}
				if (!empty($debug_matched)) {
					echo '<p style="margin:10px 0 0 0;color:#856404;font-size:12px;"><em>Debug: Found ' . count($debug_matched) . ' matching coupon(s): ' . esc_html(implode(', ', $debug_matched)) . '</em></p>';
				}
			} else {
				echo '<ul style="margin:10px 0;padding-left:20px;">';
				foreach ($eligible_coupons as $coupon_data) {
					$code = $coupon_data['code'];
					$original_amount = $coupon_data['original'];
					$discount_used = $coupon_data['discount'];
					$remaining = $coupon_data['remaining'];
					$preview_code = $coupon_data['preview_code'];
					
					echo '<li style="margin-bottom:12px;padding:8px;background:#fff;border-left:3px solid ' . ($remaining > 0 && $remaining >= $min_remaining ? '#155724' : '#646970') . ';border-radius:3px;">';
					echo '<strong>' . esc_html(strtoupper($code)) . ':</strong><br>';
					echo '&nbsp;&nbsp;' . esc_html__('Original:', 'user-manager') . ' ' . wc_price($original_amount) . '<br>';
					echo '&nbsp;&nbsp;' . esc_html__('Used:', 'user-manager') . ' ' . wc_price($discount_used) . '<br>';
					echo '&nbsp;&nbsp;' . esc_html__('Remaining:', 'user-manager') . ' <strong>' . wc_price($remaining) . '</strong><br>';
					
					if ($remaining <= 0) {
						echo '&nbsp;&nbsp;<em style="color:#646970;">' . esc_html__('No remaining balance - coupon will be fully used', 'user-manager') . '</em>';
					} elseif ($remaining < $min_remaining) {
						echo '&nbsp;&nbsp;<em style="color:#d63638;">' . sprintf(
							/* translators: 1: remaining amount, 2: minimum amount */
							esc_html__('Remaining (%1$s) below minimum (%2$s) - no remainder coupon', 'user-manager'),
							wc_price($remaining),
							wc_price($min_remaining)
						) . '</em>';
					} else {
						echo '&nbsp;&nbsp;<strong style="color:#155724;">✓ ' . sprintf(
							/* translators: 1: remaining amount */
							esc_html__('Remainder coupon will be created: %s', 'user-manager'),
							wc_price($remaining)
						) . '</strong><br>';
						echo '&nbsp;&nbsp;' . esc_html__('Preview Code:', 'user-manager') . ' <code style="background:#f0f0f0;padding:2px 6px;border:1px solid #ddd;border-radius:3px;font-size:11px;">' . esc_html($preview_code) . '</code>';
					}
					echo '</li>';
				}
				echo '</ul>';
				
				if ($total_remaining > 0) {
					echo '<div style="margin-top:10px;padding:10px;background:#d4edda;border:2px solid #155724;border-radius:4px;">';
					echo '<strong style="color:#155724;">' . esc_html__('Total Remaining Balance:', 'user-manager') . ' ' . wc_price($total_remaining) . '</strong><br>';
					echo '<em style="font-size:11px;color:#155724;">' . esc_html__('This amount will be created as a new remainder coupon after checkout.', 'user-manager') . '</em>';
					echo '</div>';
				}
			}
		}
		
		echo '</div>';

		// Notice display status
		$notice_block_enabled = !empty($settings['coupon_remainder_checkout_notice_block']);
		$notice_classic_enabled = !empty($settings['coupon_remainder_checkout_notice']);
		$has_eligible_for_notice = false;
		$notice_total_remaining = 0.0;
		
		if ($notice_block_enabled || $notice_classic_enabled) {
			foreach ($applied_coupons as $code) {
				$coupon = new WC_Coupon($code);
				if (!$coupon || !$coupon->get_id() || $coupon->get_discount_type() !== 'fixed_cart') {
					continue;
				}
				
				if (!self::coupon_code_matches_source_requirements($code, $settings)) {
					continue;
				}
				
				$original_amount = (float) $coupon->get_amount();
				$discount_used = 0.0;
				foreach ($cart->get_coupon_discount_totals() as $coupon_code => $discount) {
					if (strcasecmp($coupon_code, $code) === 0) {
						$discount_used = (float) $discount;
						break;
					}
				}
				
				$remaining = $original_amount - $discount_used;
				$decimals = wc_get_price_decimals();
				$remaining = $remaining > 0 ? (float) wc_format_decimal($remaining, $decimals) : 0.0;
				
				if ($remaining > 0 && $remaining >= $min_remaining) {
					$has_eligible_for_notice = true;
					$notice_total_remaining += $remaining;
				}
			}
		}
		
		echo '<div style="margin-bottom:15px;padding:10px;background:' . ($has_eligible_for_notice ? '#d4edda' : '#f8d7da') . ';border-left:4px solid ' . ($has_eligible_for_notice ? '#155724' : '#d63638') . ';border-radius:4px;">';
		echo '<strong style="color:' . ($has_eligible_for_notice ? '#155724' : '#d63638') . ';font-size:14px;">' . esc_html__('Remaining Balance Notice Status:', 'user-manager') . '</strong><br>';
		
		if (!$notice_block_enabled && !$notice_classic_enabled) {
			echo '<em style="color:#856404;">' . esc_html__('Notice settings are disabled. Enable "Block Checkout Page Remaining Balance Notice" or "Classic Checkout Page Remaining Balance Notice" in settings.', 'user-manager') . '</em>';
		} elseif (!$has_eligible_for_notice) {
			echo '<em style="color:#d63638;">' . esc_html__('Notice will NOT display because:', 'user-manager') . '</em><br>';
			echo '<ul style="margin:8px 0 0 20px;padding:0;font-size:12px;">';
			if (empty($applied_coupons)) {
				echo '<li>' . esc_html__('No coupons applied to cart', 'user-manager') . '</li>';
			} else {
				$has_fixed_cart = false;
				$has_matching = false;
				$has_remaining = false;
				foreach ($applied_coupons as $code) {
					$coupon = new WC_Coupon($code);
					if ($coupon && $coupon->get_id() && $coupon->get_discount_type() === 'fixed_cart') {
						$has_fixed_cart = true;
						if (self::coupon_code_matches_source_requirements($code, $settings)) {
							$has_matching = true;
							$original_amount = (float) $coupon->get_amount();
							$discount_used = 0.0;
							foreach ($cart->get_coupon_discount_totals() as $coupon_code => $discount) {
								if (strcasecmp($coupon_code, $code) === 0) {
									$discount_used = (float) $discount;
									break;
								}
							}
							$remaining = $original_amount - $discount_used;
							$decimals = wc_get_price_decimals();
							$remaining = $remaining > 0 ? (float) wc_format_decimal($remaining, $decimals) : 0.0;
							if ($remaining > 0 && $remaining >= $min_remaining) {
								$has_remaining = true;
							}
						}
					}
				}
				if (!$has_fixed_cart) {
					echo '<li>' . esc_html__('No fixed cart coupons in cart', 'user-manager') . '</li>';
				}
				if ($has_fixed_cart && !$has_matching) {
					echo '<li>' . esc_html__('No coupons match the source requirements (prefix/contains/suffix)', 'user-manager') . '</li>';
				}
				if ($has_matching && !$has_remaining) {
					echo '<li>' . sprintf(
						/* translators: 1: minimum amount */
						esc_html__('No coupons have remaining balance >= %s (minimum threshold)', 'user-manager'),
						wc_price($min_remaining)
					) . '</li>';
				}
			}
			echo '</ul>';
		} else {
			echo '<span style="color:#155724;">✓ ' . esc_html__('Notice WILL display on checkout page', 'user-manager') . '</span><br>';
			echo '<div style="margin-top:8px;font-size:12px;">';
			if ($notice_block_enabled) {
				echo '<strong>' . esc_html__('Block Checkout Notice:', 'user-manager') . '</strong> ' . esc_html__('Enabled', 'user-manager') . '<br>';
			}
			if ($notice_classic_enabled) {
				echo '<strong>' . esc_html__('Classic Checkout Notice:', 'user-manager') . '</strong> ' . esc_html__('Enabled', 'user-manager') . '<br>';
			}
			echo '<strong>' . esc_html__('Total Remaining Balance:', 'user-manager') . '</strong> ' . wc_price($notice_total_remaining);
			echo '</div>';
		}
		echo '</div>';

		// Footer note
		echo '<div style="margin-top:15px;padding-top:10px;border-top:1px solid #ddd;font-size:11px;color:#646970;">';
		echo '<em>' . esc_html__('Add ?debug_coupons=1 to any page URL to see this debug output.', 'user-manager') . '</em>';
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Render checkout page remaining balance notice for customers.
	 */
	public static function render_checkout_remaining_balance_notice(): void {
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}
		
		$settings = self::get_settings();
		if (!self::is_coupon_remainder_feature_enabled($settings)) {
			return;
		}
		if (empty($settings['coupon_remainder_checkout_notice'])) {
			return;
		}
		
		if (!is_checkout()) {
			return;
		}
		
		$cart = WC()->cart;
		$applied_coupons = $cart->get_applied_coupons();
		
		if (empty($applied_coupons)) {
			return;
		}
		
		$min_remaining = isset($settings['coupon_remainder_min_amount']) ? max(0, (float) $settings['coupon_remainder_min_amount']) : 0;
		
		$total_remaining = 0.0;
		$has_remaining_balance = false;
		$calculations = [];
		$currency_symbol = get_woocommerce_currency_symbol();
		
		foreach ($applied_coupons as $code) {
			$coupon = new WC_Coupon($code);
			if (!$coupon || !$coupon->get_id() || $coupon->get_discount_type() !== 'fixed_cart') {
				continue;
			}
			
			// Check if coupon code matches source requirements (prefix, contains, suffix)
			if (!self::coupon_code_matches_source_requirements($code, $settings)) {
				continue;
			}
			
			$original_amount = (float) $coupon->get_amount();
			
			// Get discount from cart (only discount amount, not discount tax - matches cart totals display)
			$discount_used = 0.0;
			foreach ($cart->get_coupon_discount_totals() as $coupon_code => $discount) {
				if (strcasecmp($coupon_code, $code) === 0) {
					$discount_used = (float) $discount;
					break;
				}
			}
			
			// Only use discount amount, not discount tax (matches how WooCommerce calculates cart totals)
			$remaining = $original_amount - $discount_used;
			$decimals = wc_get_price_decimals();
			$remaining = $remaining > 0 ? (float) wc_format_decimal($remaining, $decimals) : 0.0;
			
			if ($remaining > 0 && $remaining >= $min_remaining) {
				$total_remaining += $remaining;
				$has_remaining_balance = true;
				
				// Store calculation details
				$calculations[] = [
					'code' => $code,
					'original' => $original_amount,
					'discount' => $discount_used,
					'remaining' => $remaining,
				];
			}
		}
		
		if (!$has_remaining_balance || $total_remaining <= 0) {
			return;
		}
		
		// Format amounts; hide long currency labels (e.g. "Points") in checkout notices.
		$formatted_remaining = self::format_checkout_remainder_amount($total_remaining, $currency_symbol);
		
		// Build calculation text
		$calculation_text = '';
		if (count($calculations) === 1) {
			// Single coupon
			$calc = $calculations[0];
			$formatted_original = self::format_checkout_remainder_amount((float) $calc['original'], $currency_symbol);
			$formatted_discount = self::format_checkout_remainder_amount((float) $calc['discount'], $currency_symbol);
			$formatted_remaining_single = self::format_checkout_remainder_amount((float) $calc['remaining'], $currency_symbol);
			$calculation_text = sprintf(
				/* translators: 1: original amount, 2: discount used, 3: remaining amount */
				esc_html__('%1$s (Original) - %2$s (Discount Applied) = %3$s (Remaining)', 'user-manager'),
				$formatted_original,
				$formatted_discount,
				$formatted_remaining_single
			);
		} else {
			// Multiple coupons - show total calculation
			$total_original = 0.0;
			$total_discount = 0.0;
			foreach ($calculations as $calc) {
				$total_original += $calc['original'];
				$total_discount += $calc['discount'];
			}
			$formatted_original = self::format_checkout_remainder_amount($total_original, $currency_symbol);
			$formatted_discount = self::format_checkout_remainder_amount($total_discount, $currency_symbol);
			$calculation_text = sprintf(
				/* translators: 1: total original amount, 2: total discount used, 3: total remaining amount */
				esc_html__('%1$s (Total Original) - %2$s (Total Discount Applied) = %3$s (Total Remaining)', 'user-manager'),
				$formatted_original,
				$formatted_discount,
				$formatted_remaining
			);
		}
		
		echo '<div class="um-remaining-balance-notice" style="margin:20px 0;padding:15px 20px;background:#f0f6fc;border:1px solid #2271b1;border-left:4px solid #2271b1;border-radius:4px;clear:both;">';
		echo '<p style="margin:0;font-size:14px;line-height:1.6;">';
		echo sprintf(
			/* translators: 1: remaining balance amount */
			esc_html__('You will be left with a remaining balance coupon code of %s after placing this order.', 'user-manager'),
			'<strong style="color:#155724;">' . esc_html($formatted_remaining) . '</strong>'
		);
		echo '</p>';
		echo '<p style="margin:8px 0 0 0;font-size:11px;line-height:1.4;color:#666;">';
		echo esc_html($calculation_text);
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Static flag to track if checkout block is present.
	 */
	private static bool $checkout_block_detected = false;

	/**
	 * Detect WooCommerce checkout block for remaining balance notice injection.
	 */
	public static function maybe_detect_checkout_block(string $content, array $block): string {
		if (!self::is_coupon_remainder_feature_enabled()) {
			return $content;
		}
		if (empty($block['blockName']) || is_admin()) {
			return $content;
		}
		if ('woocommerce/checkout' === $block['blockName']) {
			self::$checkout_block_detected = true;
		}
		return $content;
	}

	/**
	 * Inject remaining balance notice into block checkout.
	 */
	public static function inject_checkout_remaining_balance_notice(): void {
		$settings = self::get_settings();
		if (!self::is_coupon_remainder_feature_enabled($settings)) {
			return;
		}
		// Bail early if WooCommerce's checkout helpers aren't available
		if (!function_exists('is_checkout') || !is_checkout()) {
			return;
		}
		
		if (!function_exists('WC') || !WC()->cart) {
			return;
		}
		
		if (empty($settings['coupon_remainder_checkout_notice_block'])) {
			return;
		}
		
		$cart = WC()->cart;
		$applied_coupons = $cart->get_applied_coupons();
		
		if (empty($applied_coupons)) {
			return;
		}
		
		$min_remaining = isset($settings['coupon_remainder_min_amount']) ? max(0, (float) $settings['coupon_remainder_min_amount']) : 0;
		
		$total_remaining = 0.0;
		$has_remaining_balance = false;
		$calculations = [];
		$currency_symbol = get_woocommerce_currency_symbol();
		
		foreach ($applied_coupons as $code) {
			$coupon = new WC_Coupon($code);
			if (!$coupon || !$coupon->get_id() || $coupon->get_discount_type() !== 'fixed_cart') {
				continue;
			}
			
			// Check if coupon code matches source requirements (prefix, contains, suffix)
			if (!self::coupon_code_matches_source_requirements($code, $settings)) {
				continue;
			}
			
			$original_amount = (float) $coupon->get_amount();
			
			// Get discount from cart (only discount amount, not discount tax - matches cart totals display)
			$discount_used = 0.0;
			foreach ($cart->get_coupon_discount_totals() as $coupon_code => $discount) {
				if (strcasecmp($coupon_code, $code) === 0) {
					$discount_used = (float) $discount;
					break;
				}
			}
			
			// Only use discount amount, not discount tax (matches how WooCommerce calculates cart totals)
			$remaining = $original_amount - $discount_used;
			$decimals = wc_get_price_decimals();
			$remaining = $remaining > 0 ? (float) wc_format_decimal($remaining, $decimals) : 0.0;
			
			if ($remaining > 0 && $remaining >= $min_remaining) {
				$total_remaining += $remaining;
				$has_remaining_balance = true;
				
				// Store calculation details
				$calculations[] = [
					'code' => $code,
					'original' => $original_amount,
					'discount' => $discount_used,
					'remaining' => $remaining,
				];
			}
		}
		
		if (!$has_remaining_balance || $total_remaining <= 0) {
			return;
		}
		
		// Format amounts; hide long currency labels (e.g. "Points") in checkout notices.
		$formatted_remaining = self::format_checkout_remainder_amount($total_remaining, $currency_symbol);
		
		// Build calculation text
		$calculation_text = '';
		if (count($calculations) === 1) {
			// Single coupon
			$calc = $calculations[0];
			$formatted_original = self::format_checkout_remainder_amount((float) $calc['original'], $currency_symbol);
			$formatted_discount = self::format_checkout_remainder_amount((float) $calc['discount'], $currency_symbol);
			$formatted_remaining_single = self::format_checkout_remainder_amount((float) $calc['remaining'], $currency_symbol);
			$calculation_text = sprintf(
				/* translators: 1: original amount, 2: discount used, 3: remaining amount */
				esc_html__('%1$s (Original) - %2$s (Discount Applied) = %3$s (Remaining)', 'user-manager'),
				$formatted_original,
				$formatted_discount,
				$formatted_remaining_single
			);
		} else {
			// Multiple coupons - show total calculation
			$total_original = 0.0;
			$total_discount = 0.0;
			foreach ($calculations as $calc) {
				$total_original += $calc['original'];
				$total_discount += $calc['discount'];
			}
			$formatted_original = self::format_checkout_remainder_amount($total_original, $currency_symbol);
			$formatted_discount = self::format_checkout_remainder_amount($total_discount, $currency_symbol);
			$calculation_text = sprintf(
				/* translators: 1: total original amount, 2: total discount used, 3: total remaining amount */
				esc_html__('%1$s (Total Original) - %2$s (Total Discount Applied) = %3$s (Total Remaining)', 'user-manager'),
				$formatted_original,
				$formatted_discount,
				$formatted_remaining
			);
		}
		
		$notice_html = '<div class="um-remaining-balance-notice-block" style="margin:20px 0;padding:15px 20px;background:#f0f6fc;border:1px solid #2271b1;border-left:4px solid #2271b1;border-radius:4px;clear:both;">';
		$notice_html .= '<p style="margin:0;font-size:14px;line-height:1.6;">';
		$notice_html .= sprintf(
			/* translators: 1: remaining balance amount */
			esc_html__('You will be left with a remaining balance coupon code of %s after placing this order.', 'user-manager'),
			'<strong style="color:#155724;">' . esc_html($formatted_remaining) . '</strong>'
		);
		$notice_html .= '</p>';
		$notice_html .= '<p style="margin:8px 0 0 0;font-size:11px;line-height:1.4;color:#666;">';
		$notice_html .= esc_html($calculation_text);
		$notice_html .= '</p>';
		$notice_html .= '</div>';
		
		// Output hidden div that will be injected via JavaScript
		echo '<div id="um-remaining-balance-notice-block-data" style="display:none;">' . esc_html(wp_json_encode(['notice_html' => $notice_html, 'total_remaining' => $total_remaining])) . '</div>';
		?>
		<script>
		(function() {
			if (typeof jQuery === 'undefined') {
				return;
			}
			
			function calculateRemainingBalance() {
				// Try to get data from PHP
				var dataEl = jQuery('#um-remaining-balance-notice-block-data');
				if (dataEl.length) {
					try {
						var data = JSON.parse(dataEl.text());
						return data;
					} catch(e) {
						// Fallback to DOM calculation
					}
				}
				return null;
			}
			
			function injectNotice() {
				var checkoutBlock = jQuery('.wp-block-woocommerce-checkout, .wc-block-checkout');
				if (!checkoutBlock.length) {
					return false;
				}
				
				// Remove existing notice
				jQuery('.um-remaining-balance-notice-block').remove();
				
				var data = calculateRemainingBalance();
				if (!data || !data.notice_html) {
					return false;
				}
				
				// Try multiple injection points
				var selectors = [
					'.wc-block-components-checkout-place-order-button',
					'button[type="submit"][name="woocommerce_checkout_place_order"]',
					'.wc-block-checkout__actions',
					'.wp-block-woocommerce-checkout__actions',
					'.wc-block-components-checkout__actions',
					'.wc-block-components-totals-wrapper',
					'.wc-block-components-order-summary'
				];
				
				var target = null;
				for (var i = 0; i < selectors.length; i++) {
					target = checkoutBlock.find(selectors[i]).first();
					if (target.length) {
						break;
					}
				}
				
				if (target && target.length) {
					var notice = jQuery(data.notice_html);
					
					// Try to insert before the button or its container
					var button = target.is('button') ? target : target.find('button[type="submit"]').first();
					if (button.length) {
						var container = button.closest('.wc-block-checkout__actions, .wp-block-woocommerce-checkout__actions, .wc-block-components-checkout__actions');
						if (container.length) {
							container.before(notice);
						} else {
							button.before(notice);
						}
					} else {
						target.before(notice);
					}
					
					return true;
				}
				
				return false;
			}
			
			// Debounce function
			function debounce(func, wait) {
				var timeout;
				return function() {
					var context = this, args = arguments;
					clearTimeout(timeout);
					timeout = setTimeout(function() {
						func.apply(context, args);
					}, wait);
				};
			}
			
			var debouncedInject = debounce(injectNotice, 300);
			
			jQuery(document).ready(function($) {
				// Initial attempts with delays
				var attempts = [0, 500, 1000, 2000, 3000, 5000];
				attempts.forEach(function(delay) {
					setTimeout(function() {
						injectNotice();
					}, delay);
				});
				
				// Listen for checkout updates
				jQuery(document.body).on('updated_checkout', debouncedInject);
				jQuery(document.body).on('applied_coupon removed_coupon', debouncedInject);
				
				// MutationObserver for DOM changes
				if (typeof MutationObserver !== 'undefined') {
					var observer = new MutationObserver(debounce(function() {
						injectNotice();
					}, 1000));
					
					var checkoutBlock = jQuery('.wp-block-woocommerce-checkout, .wc-block-checkout').first()[0];
					if (checkoutBlock) {
						observer.observe(checkoutBlock, {
							childList: true,
							subtree: true
						});
					}
				}
			});
		})();
		</script>
		<?php
	}

	/**
	 * Render remaining balance coupon created notice on order received page.
	 */
	public static function render_order_received_remaining_balance_notice($order_id): void {
		if (!$order_id) {
			return;
		}
		
		$settings = self::get_settings();
		if (!self::is_coupon_remainder_feature_enabled($settings)) {
			return;
		}
		if (empty($settings['coupon_remainder_order_received_notice'])) {
			return;
		}
		
		if (!function_exists('wc_get_order')) {
			return;
		}
		
		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}
		
		// Check if any remaining balance coupons were created for this order
		$processed = get_post_meta($order_id, '_um_coupon_remainder_processed', true);
		if (empty($processed) || !is_array($processed)) {
			return;
		}
		
		// Get coupon details
		$coupon_codes = [];
		$total_remaining = 0.0;
		$currency_symbol = get_woocommerce_currency_symbol();
		
		foreach ($processed as $source_code => $new_code) {
			$coupon = new WC_Coupon($new_code);
			if ($coupon && $coupon->get_id()) {
				$amount = (float) $coupon->get_amount();
				$total_remaining += $amount;
				$coupon_codes[] = [
					'code' => $new_code,
					'amount' => $amount,
				];
			}
		}
		
		if (empty($coupon_codes)) {
			return;
		}
		
		$formatted_total = self::format_checkout_remainder_amount($total_remaining, $currency_symbol);
		
		?>
		<div class="woocommerce-message um-remainder-notice" role="alert" style="display:block;">
			<?php
			if (count($coupon_codes) === 1) {
				printf(
					/* translators: 1: amount */
					esc_html__('A new remaining balance coupon code with a value of %1$s has been created and can be applied on your next checkout.', 'user-manager'),
					'<strong>' . esc_html($formatted_total) . '</strong>'
				);
				echo '<div class="um-remainder-codes" style="margin-top:8px;font-size:0.95em;word-break:break-all;">' . esc_html(strtoupper($coupon_codes[0]['code'])) . '</div>';
			} else {
				printf(
					/* translators: 1: number of coupons, 2: total amount */
					esc_html__('%1$d new remaining balance coupon codes with a total value of %2$s have been created and can be applied on your next checkout.', 'user-manager'),
					count($coupon_codes),
					'<strong>' . esc_html($formatted_total) . '</strong>'
				);
				echo '<div class="um-remainder-codes" style="margin-top:8px;font-size:0.95em;">';
				echo '<span style="font-weight:600;">' . esc_html__('Coupon codes:', 'user-manager') . '</span>';
				echo '<ul style="margin:6px 0 0;padding-left:1.25em;list-style:disc;">';
				foreach ($coupon_codes as $coupon_info) {
					$formatted_amount = self::format_checkout_remainder_amount((float) $coupon_info['amount'], $currency_symbol);
					echo '<li style="margin-bottom:4px;word-break:break-all;">' . esc_html(strtoupper($coupon_info['code'])) . ' <strong>(' . esc_html($formatted_amount) . ')</strong></li>';
				}
				echo '</ul></div>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Format remainder notice amounts for checkout/order-received UI.
	 *
	 * If the currency symbol is longer than 1 character (e.g. "Points"),
	 * hide it and show numeric-only value in these notices.
	 */
	private static function format_checkout_remainder_amount(float $amount, string $currency_symbol): string {
		$symbol = trim((string) $currency_symbol);
		$formatted_number = number_format($amount, 2, '.', '');
		if (strlen($symbol) > 1) {
			return $formatted_number;
		}
		return $symbol . $formatted_number;
	}

	/**
	 * Register top-level admin menu.
	 */
	public static function register_settings_page(): void {
		add_submenu_page(
			'users.php',
			__('User Experience Manager', 'user-manager'),
			__('User Experience Manager', 'user-manager'),
			'manage_options',
			self::SETTINGS_PAGE_SLUG,
			[__CLASS__, 'render_settings_page']
		);
		
		// Keep submenu highlighted on all tabs
		add_filter('submenu_file', [__CLASS__, 'keep_submenu_current']);
	}

	/**
	 * Resolve plugin basename for plugin-list row hooks.
	 */
	private static function get_main_plugin_basename(): string {
		$main_plugin_file = dirname(__DIR__) . '/user-manager.php';
		if (function_exists('plugin_basename')) {
			return plugin_basename($main_plugin_file);
		}

		return basename(dirname($main_plugin_file)) . '/' . basename($main_plugin_file);
	}

	/**
	 * Add Settings shortcut on Plugins screen row actions.
	 *
	 * @param array<int|string,string> $links
	 * @return array<int|string,string>
	 */
	public static function add_plugin_action_links(array $links): array {
		if (!current_user_can('manage_options')) {
			return $links;
		}

		$settings_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(self::get_page_url(self::TAB_SETTINGS)),
			esc_html__('Settings', 'user-manager')
		);
		array_unshift($links, $settings_link);

		return $links;
	}

	/**
	 * Add quick links for each tab in plugin row meta.
	 *
	 * @param array<int,string> $links Existing plugin row meta links.
	 * @param string            $file  Plugin file basename.
	 * @return array<int,string>
	 */
	public static function add_plugin_row_meta_links(array $links, string $file): array {
		if ($file !== self::get_main_plugin_basename()) {
			return $links;
		}
		if (!current_user_can('manage_options')) {
			return $links;
		}

		$tabs = [
			self::TAB_LOGIN_TOOLS   => __('Login Tools', 'user-manager'),
			self::TAB_EMAIL_USERS   => __('Email Users', 'user-manager'),
			self::TAB_SETTINGS      => __('Settings', 'user-manager'),
			self::TAB_REPORTS       => __('Reports', 'user-manager'),
			self::TAB_ADDONS        => __('Add-ons', 'user-manager'),
			self::TAB_BLOCKS        => __('Blocks', 'user-manager'),
			self::TAB_DOCUMENTATION => __('Documentation', 'user-manager'),
		];

		foreach ($tabs as $tab => $label) {
			$links[] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url(self::get_page_url($tab)),
				esc_html($label)
			);
		}

		return $links;
	}
	
	/**
	 * Handle CSV exports before page rendering.
	 */
	public static function maybe_handle_csv_export(): void {
		// Only run on our admin page
		if (!isset($_GET['page']) || $_GET['page'] !== self::SETTINGS_PAGE_SLUG) {
			return;
		}
		
		if (!current_user_can('manage_options')) {
			return;
		}
		
		$active_tab = self::get_current_tab();
		
		// Handle CSV exports for Reports tab before any HTML output
		if ($active_tab === self::TAB_REPORTS && isset($_GET['export']) && $_GET['export'] === 'csv') {
			$report = isset($_GET['report']) ? sanitize_key($_GET['report']) : '';
			if (!empty($report) && class_exists('User_Manager_Tab_Reports')) {
				if ($report === 'user-logins') {
					User_Manager_Tab_Reports::export_user_logins_csv();
				} elseif ($report === 'coupons-used') {
					User_Manager_Tab_Reports::export_coupons_used_csv();
				} elseif ($report === 'coupons-assigned') {
					User_Manager_Tab_Reports::export_coupons_assigned_csv();
				} elseif ($report === 'password-reset') {
					User_Manager_Tab_Reports::export_password_reset_csv();
				} elseif ($report === 'sales-vs-coupons') {
					User_Manager_Tab_Reports::export_sales_vs_coupons_csv();
				} elseif ($report === 'coupons-no-expiration') {
					User_Manager_Tab_Reports::export_coupons_no_expiration_csv();
				} elseif ($report === 'coupons-free-shipping') {
					User_Manager_Tab_Reports::export_coupons_free_shipping_csv();
				} elseif ($report === 'coupons-remaining-balances') {
					User_Manager_Tab_Reports::export_coupons_remaining_balances_csv();
				} elseif ($report === 'coupons-unused') {
					User_Manager_Tab_Reports::export_coupons_unused_csv();
				} elseif ($report === 'orders-with-refunds') {
					User_Manager_Tab_Reports::export_orders_with_refunds_csv();
				} elseif ($report === 'orders-zero-total') {
					User_Manager_Tab_Reports::export_orders_zero_total_csv();
				} elseif ($report === 'orders-free-shipping') {
					User_Manager_Tab_Reports::export_orders_free_shipping_csv();
				} elseif ($report === 'order-payment-methods') {
					User_Manager_Tab_Reports::export_order_payment_methods_csv();
				} elseif ($report === 'user-password-changes') {
					User_Manager_Tab_Reports::export_user_password_changes_csv();
				} elseif ($report === 'order-notes') {
					User_Manager_Tab_Reports::export_order_notes_csv();
				} elseif ($report === 'orders-processing-days') {
					User_Manager_Tab_Reports::export_orders_processing_days_csv();
				} elseif ($report === 'orders-shipments-by-day') {
					User_Manager_Tab_Reports::export_orders_shipments_by_day_csv();
				} elseif ($report === 'orders-shipments-by-week') {
					User_Manager_Tab_Reports::export_orders_shipments_by_week_csv();
				} elseif ($report === 'orders-shipments-by-month') {
					User_Manager_Tab_Reports::export_orders_shipments_by_month_csv();
				} elseif ($report === 'orders-tracking-numbers') {
					User_Manager_Tab_Reports::export_orders_tracking_numbers_csv();
				} elseif ($report === 'orders-tracking-notes') {
					User_Manager_Tab_Reports::export_orders_tracking_notes_csv();
				} elseif ($report === 'orders-still-processing-with-tracking-number') {
					User_Manager_Tab_Reports::export_orders_still_processing_with_tracking_number_csv();
				} elseif ($report === 'user-data') {
					User_Manager_Tab_Reports::export_user_data_csv();
				} elseif ($report === 'user-total-sales') {
					User_Manager_Tab_Reports::export_user_total_sales_csv();
				} elseif ($report === 'users-who-used-coupons') {
					User_Manager_Tab_Reports::export_users_who_used_coupons_csv();
				} elseif ($report === 'product-purchases') {
					User_Manager_Tab_Reports::export_product_purchases_csv();
				} elseif ($report === 'product-category-purchases') {
					User_Manager_Tab_Reports::export_product_category_purchases_csv();
				} elseif ($report === 'product-tag-purchases') {
					User_Manager_Tab_Reports::export_product_tag_purchases_csv();
				} elseif ($report === 'page-not-found-404-errors') {
					User_Manager_Tab_Reports::export_404_errors_csv();
				} elseif ($report === 'search-queries') {
					User_Manager_Tab_Reports::export_search_queries_csv();
				} elseif ($report === 'page-views') {
					User_Manager_Tab_Reports::export_page_views_csv();
				} elseif ($report === 'page-category-views') {
					User_Manager_Tab_Reports::export_page_category_views_csv();
				} elseif ($report === 'post-views') {
					User_Manager_Tab_Reports::export_post_views_csv();
				} elseif ($report === 'post-category-views') {
					User_Manager_Tab_Reports::export_post_category_views_csv();
				} elseif ($report === 'post-tag-views') {
					User_Manager_Tab_Reports::export_post_tag_views_csv();
				} elseif ($report === 'product-views') {
					User_Manager_Tab_Reports::export_product_views_csv();
				} elseif ($report === 'product-category-views') {
					User_Manager_Tab_Reports::export_product_category_views_csv();
				} elseif ($report === 'product-tag-views') {
					User_Manager_Tab_Reports::export_product_tag_views_csv();
				} elseif ($report === 'post-meta-field-names') {
					User_Manager_Tab_Reports::export_post_meta_field_names_csv();
				}
			}
		}
	}

	/**
	 * Keep the User Manager submenu item highlighted on all tabs.
	 */
	public static function keep_submenu_current($submenu_file) {
		$screen = get_current_screen();
		if ($screen && $screen->id === 'users_page_' . self::SETTINGS_PAGE_SLUG) {
			return self::SETTINGS_PAGE_SLUG;
		}
		return $submenu_file;
	}

	/**
	 * Register option.
	 */
	public static function register_settings(): void {
		register_setting(self::SETTINGS_PAGE_SLUG, self::OPTION_KEY);
		register_setting(self::SETTINGS_PAGE_SLUG, self::ACTIVITY_LOG_KEY);
		register_setting(self::SETTINGS_PAGE_SLUG, self::EMAIL_TEMPLATES_KEY);
		register_setting(self::SETTINGS_PAGE_SLUG, self::SMS_TEXT_TEMPLATES_KEY);
	}

	/**
	 * Enqueue admin assets on plugin screen.
	 */
	public static function enqueue_admin_assets(string $hook): void {
		if ('users_page_' . self::SETTINGS_PAGE_SLUG !== $hook) {
			return;
		}

		wp_add_inline_style('wp-admin', self::get_admin_styles());

		// Media modal for blog importer "Change Thumbnail" on success tiles
		wp_enqueue_media();
		// Run our Change Thumbnail script after media-views (wp.media) and in footer so body exists
		wp_register_script('um-blog-importer-change-thumb', false, ['media-editor'], self::VERSION, true);
		wp_enqueue_script('um-blog-importer-change-thumb');
		wp_add_inline_script('um-blog-importer-change-thumb', self::get_blog_importer_change_thumb_script(), 'after');

		// Enqueue script for file upload
		wp_enqueue_script('jquery');
		
		// Shared lazy datalist loader for fields using list=.
		wp_register_script('um-lazy-datalist', false, ['jquery'], self::VERSION, true);
		wp_enqueue_script('um-lazy-datalist');
		wp_add_inline_script('um-lazy-datalist', 'window.umLazyDatalistConfig = ' . wp_json_encode([
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('user_manager_datalist_options'),
		]) . ';', 'before');
		wp_add_inline_script('um-lazy-datalist', self::get_lazy_datalist_script(), 'after');
	}

	/**
	 * Inline script for "Change Thumbnail" on blog importer success tiles.
	 *
	 * @return string
	 */
	private static function get_blog_importer_change_thumb_script(): string {
		$ajaxurl = esc_url(admin_url('admin-ajax.php'));
		// Event delegation on body; runs after media-views so wp.media is defined.
		$js = '(function(){ var ajaxurl = ' . wp_json_encode($ajaxurl) . ';
		function bindChangeThumb() {
			var body = document.body;
			if (!body) return;
			body.addEventListener("click", function handler(e) {
				var btn = e.target.closest(".um-blog-importer-change-thumb");
				if (!btn) return;
				e.preventDefault();
				e.stopPropagation();
				var grid = btn.closest(".um-blog-importer-created-tiles");
				if (!grid) return;
				var nonce = grid.getAttribute("data-thumb-nonce");
				if (!nonce) return;
				var postId = btn.getAttribute("data-post-id");
				if (!postId) return;
				var tile = btn.closest(".um-blog-importer-tile");
				var thumbEl = tile ? tile.querySelector(".um-blog-importer-tile-thumb") : null;
				function openFrame() {
					if (typeof wp === "undefined" || !wp.media) { setTimeout(openFrame, 80); return; }
					var frame = wp.media({ library: { type: "image" }, multiple: false });
				frame.on("select", function() {
					var att = frame.state().get("selection").first();
					if (!att) return;
					var form = new FormData();
					form.append("action", "user_manager_set_post_thumbnail");
					form.append("nonce", nonce);
					form.append("post_id", postId);
					form.append("attachment_id", att.get("id"));
					fetch(ajaxurl, { method: "POST", body: form, credentials: "same-origin" }).then(function(r) { return r.json(); }).then(function(data) {
						if (data.success && thumbEl && data.data && data.data.thumb_url !== undefined) {
							var url = data.data.thumb_url;
							var viewUrl = (data.data.view_url || "").replace(/"/g, "&quot;");
							if (url) thumbEl.innerHTML = "<a href=\"" + viewUrl + "\" style=\"display:block; width:100%; height:100%;\" target=\"_blank\" rel=\"noopener noreferrer\"><img src=\"" + url.replace(/"/g, "&quot;") + "\" alt=\"\" style=\"width:100%; height:100%; object-fit:cover; max-width:200px;\" /></a>";
							else thumbEl.innerHTML = "<div class=\"um-blog-importer-tile-no-img\" style=\"width:100%; height:100%; color:#787c82; font-size:12px; display:flex; align-items:center; justify-content:center;\">No image</div>";
						}
					});
				});
				frame.open();
				}
				openFrame();
			}, true);
			body.addEventListener("click", function dateClick(e) {
				var btn = e.target.closest(".um-blog-importer-change-date");
				if (!btn) return;
				e.preventDefault();
				e.stopPropagation();
				var grid = btn.closest(".um-blog-importer-created-tiles");
				if (!grid) return;
				var dateNonce = grid.getAttribute("data-date-nonce");
				if (!dateNonce) return;
				var postId = btn.getAttribute("data-post-id");
				var currentDate = btn.getAttribute("data-date") || "";
				var input = document.createElement("input");
				input.type = "date";
				input.value = currentDate;
				input.style.cssText = "font-size:12px; width:100%; max-width:180px; margin-bottom:8px;";
				btn.parentNode.replaceChild(input, btn);
				input.focus();
				function saveDate() {
					var newDate = input.value;
					if (!newDate) return;
					var form = new FormData();
					form.append("action", "user_manager_set_post_date");
					form.append("nonce", dateNonce);
					form.append("post_id", postId);
					form.append("date", newDate);
					fetch(ajaxurl, { method: "POST", body: form, credentials: "same-origin" }).then(function(r) { return r.json(); }).then(function(data) {
						var newBtn = document.createElement("button");
						newBtn.type = "button";
						newBtn.className = "um-blog-importer-change-date link-button";
						newBtn.style.cssText = "display:block; font-size:12px; color:#2271b1; margin-bottom:8px; padding:0; border:0; background:none; cursor:pointer; text-align:left;";
						newBtn.setAttribute("data-post-id", postId);
						newBtn.setAttribute("data-date", newDate);
						newBtn.setAttribute("title", "Click to change date");
						newBtn.textContent = data.success && data.data && data.data.formatted_date ? data.data.formatted_date : newDate;
						input.parentNode.replaceChild(newBtn, input);
					});
				}
				input.addEventListener("change", saveDate);
				var originalText = btn.textContent;
				input.addEventListener("blur", function blurDate() {
					if (input.parentNode && input.value === currentDate) {
						var newBtn = document.createElement("button");
						newBtn.type = "button";
						newBtn.className = "um-blog-importer-change-date link-button";
						newBtn.style.cssText = "display:block; font-size:12px; color:#2271b1; margin-bottom:8px; padding:0; border:0; background:none; cursor:pointer; text-align:left;";
						newBtn.setAttribute("data-post-id", postId);
						newBtn.setAttribute("data-date", currentDate);
						newBtn.setAttribute("title", "Click to change date");
						newBtn.textContent = originalText;
						input.parentNode.replaceChild(newBtn, input);
					}
				});
			}, true);
			body.addEventListener("click", function spreadClick(e) {
				var btn = e.target.closest(".um-blog-importer-spread-scheduled-btn");
				if (!btn) return;
				e.preventDefault();
				var block = btn.closest(".um-blog-importer-recent-posts");
				if (!block) return;
				var nonce = block.getAttribute("data-spread-nonce");
				var dateInput = block.querySelector(".um-blog-importer-spread-date");
				var targetDate = dateInput ? dateInput.value.trim() : "";
				if (!targetDate) {
					targetDate = (block.getAttribute("data-recommended-date") || "").trim();
				}
				if (!targetDate) {
					alert("Please choose a date.");
					return;
				}
				if (!nonce) return;
				btn.disabled = true;
				var form = new FormData();
				form.append("action", "user_manager_spread_scheduled_posts");
				form.append("nonce", nonce);
				form.append("target_date", targetDate);
				fetch(ajaxurl, { method: "POST", body: form, credentials: "same-origin" }).then(function(r) { return r.json(); }).then(function(data) {
					btn.disabled = false;
					if (data.success && data.data && data.data.message) {
						alert(data.data.message);
						if (data.data.updated && data.data.updated > 0) window.location.reload();
					} else {
						alert(data.data && data.data.message ? data.data.message : "Something went wrong.");
					}
				}).catch(function() {
					btn.disabled = false;
					alert("Request failed.");
				});
			}, true);
		}
		if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", bindChangeThumb);
		else bindChangeThumb();
		})();';
		return $js;
	}
	
	/**
	 * Shared lazy datalist loader for plugin admin forms.
	 */
	private static function get_lazy_datalist_script(): string {
		return '(function($) {
			if (!$ || !window.umLazyDatalistConfig || !umLazyDatalistConfig.ajaxUrl || !umLazyDatalistConfig.nonce) {
				return;
			}
			
			var sourceCache = {};
			var sourceRequests = {};
			
			function populateDatalist(datalistEl, options) {
				if (!datalistEl) {
					return;
				}
				
				while (datalistEl.firstChild) {
					datalistEl.removeChild(datalistEl.firstChild);
				}
				
				for (var i = 0; i < options.length; i++) {
					var value = options[i];
					if (typeof value !== "string" || value === "") {
						continue;
					}
					var optionEl = document.createElement("option");
					optionEl.value = value;
					datalistEl.appendChild(optionEl);
				}
				
				datalistEl.setAttribute("data-um-lazy-loaded", "1");
				datalistEl.removeAttribute("data-um-lazy-loading");
			}
			
			function getSourceOptions(source) {
				if (Array.isArray(sourceCache[source])) {
					return $.Deferred().resolve(sourceCache[source]).promise();
				}
				
				if (sourceRequests[source]) {
					return sourceRequests[source];
				}
				
				sourceRequests[source] = $.ajax({
					url: umLazyDatalistConfig.ajaxUrl,
					method: "GET",
					dataType: "json",
					data: {
						action: "user_manager_get_datalist_options",
						nonce: umLazyDatalistConfig.nonce,
						source: source
					}
				}).then(function(response) {
					var options = [];
					if (response && response.success && response.data && Array.isArray(response.data.options)) {
						options = response.data.options.filter(function(item) {
							return typeof item === "string" && item !== "";
						});
					}
					sourceCache[source] = options;
					return options;
				}).always(function() {
					delete sourceRequests[source];
				});
				
				return sourceRequests[source];
			}
			
			function maybeLoadDatalistForInput(inputEl) {
				if (!inputEl) {
					return;
				}
				
				var source = inputEl.getAttribute("data-um-lazy-datalist-source");
				if (!source) {
					return;
				}
				
				var listId = inputEl.getAttribute("list");
				if (!listId) {
					return;
				}
				
				var datalistEl = document.getElementById(listId);
				if (!datalistEl) {
					return;
				}
				
				if (datalistEl.getAttribute("data-um-lazy-loaded") === "1" || datalistEl.getAttribute("data-um-lazy-loading") === "1") {
					return;
				}
				
				datalistEl.setAttribute("data-um-lazy-loading", "1");
				
				getSourceOptions(source).done(function(options) {
					populateDatalist(datalistEl, options || []);
				}).fail(function() {
					datalistEl.removeAttribute("data-um-lazy-loading");
				});
			}
			
			$(document).on("focusin click", "input[list][data-um-lazy-datalist-source]", function() {
				maybeLoadDatalistForInput(this);
			});
		})(jQuery);';
	}

	/**
	 * Get admin card styles.
	 */
	public static function get_admin_styles(): string {
		return '
		/* Layout for cards */
		.um-admin-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
			gap: 20px;
			margin-top: 20px;
		}
		/* Remove extra first-gap under sub-sub navigation rows */
		.wrap .subsubsub + .clear + .um-admin-card,
		.wrap .subsubsub + .clear + .um-admin-grid,
		.wrap .subsubsub + .clear + .um-create-user-layout,
		.wrap .subsubsub + .clear + .um-email-templates-layout {
			margin-top: 0 !important;
		}
		/* Two-column variant for pages that want a strict 2-col layout */
		.um-admin-grid.um-admin-grid-2col {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
		/* Single-column variant (all cards 100% width) for pages like Tools */
		.um-admin-grid.um-admin-grid-single {
			grid-template-columns: 1fr;
		}
		@media (max-width: 782px) {
			.um-admin-grid.um-admin-grid-2col {
				grid-template-columns: 1fr;
			}
		}
		/* Make User Manager tabs wrap cleanly across lines */
		.wrap .nav-tab-wrapper {
			display: flex;
			flex-wrap: wrap;
			gap: 0 4px;
			margin-bottom: 0.5rem;
		}
		.wrap .nav-tab-wrapper .nav-tab {
			float: none;
			margin: 0;
		}
		/* Blog importer success tiles: side-by-side grid */
		.um-blog-importer-created-tiles {
			display: grid !important;
			grid-template-columns: repeat(auto-fill, minmax(200px, 200px));
			gap: 12px;
			margin-top: 12px;
		}
		.um-blog-importer-created-tiles .um-blog-importer-tile {
			max-width: 200px;
		}
		.um-admin-card {
			background: #fff;
			border: 1px solid #c3c4c7;
			border-radius: 4px;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
			display: flex;
			flex-direction: column;
		}
		.um-admin-card-header {
			padding: 12px 16px;
			border-bottom: 1px solid #c3c4c7;
			background: #f6f7f7;
			display: flex;
			align-items: center;
			gap: 10px;
			flex-shrink: 0;
		}
		.um-admin-card-header h2 {
			margin: 0;
			font-size: 14px;
			font-weight: 600;
			color: #1d2327;
		}
		.um-admin-card-header .dashicons {
			color: #2271b1;
			font-size: 18px;
			width: 18px;
			height: 18px;
		}
		.um-admin-card-body {
			padding: 16px;
			flex: 1;
			min-height: 0;
		}
		.um-admin-card-body p {
			margin-top: 0;
		}
		.notice .um-notice-content {
			padding: 16px 28px 16px 20px;
			display: block;
		}
		.um-admin-card-full {
			grid-column: 1 / -1;
		}
		.um-admin-card .form-table th {
			padding-left: 0;
			width: 180px;
		}
		.um-admin-card .form-table td {
			padding-left: 0;
		}
		.um-admin-card .form-table input.regular-text,
		.um-admin-card .form-table select.regular-text,
		.um-admin-card .form-table textarea.regular-text {
			width: 100%;
			max-width: 100%;
			box-sizing: border-box;
		}
		.um-password-input-row {
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.um-password-input-row input.regular-text {
			flex: 1;
			min-width: 0;
		}
		.um-form-field {
			margin-bottom: 16px;
		}
		.um-form-field label {
			display: block;
			font-weight: 600;
			margin-bottom: 6px;
		}
		.um-form-field input[type="text"],
		.um-form-field input[type="email"],
		.um-form-field input[type="password"],
		.um-form-field select,
		.um-form-field textarea {
			width: 100%;
			max-width: 400px;
		}
		.um-form-field .description {
			color: #646970;
			font-size: 12px;
			margin-top: 4px;
		}
		.um-form-field-inline {
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.um-doc-section {
			margin-bottom: 24px;
		}
		.um-doc-section:last-child {
			margin-bottom: 0;
		}
		.um-doc-section h3 {
			margin: 0 0 12px;
			font-size: 14px;
			color: #1d2327;
		}
		.um-doc-section ul {
			margin: 0;
			padding-left: 20px;
		}
		.um-doc-section li {
			margin-bottom: 6px;
		}
		.um-code-block {
			background: #2c3338;
			color: #f0f0f1;
			padding: 12px 16px;
			border-radius: 4px;
			font-family: Consolas, Monaco, monospace;
			font-size: 13px;
			overflow-x: auto;
		}
		.um-changelog-item {
			background: #f6f7f7;
			border-left: 4px solid #2271b1;
			padding: 12px 16px;
			margin-bottom: 12px;
		}
		.um-changelog-item:last-child {
			margin-bottom: 0;
		}
		.um-changelog-item h4 {
			margin: 0 0 8px;
			font-size: 14px;
		}
		.um-changelog-item h4 span {
			font-weight: normal;
			color: #50575e;
			font-size: 12px;
		}
		.um-changelog-item ul {
			margin: 0;
			padding-left: 20px;
		}
		.um-status-badge {
			display: inline-block;
			padding: 2px 8px;
			border-radius: 3px;
			font-size: 11px;
			font-weight: 600;
			text-transform: uppercase;
		}
		.um-status-success {
			background: #d4edda;
			color: #155724;
		}
		.um-status-error {
			background: #f8d7da;
			color: #721c24;
		}
		.um-status-warning {
			background: #fff3cd;
			color: #856404;
		}
		.um-status-secondary {
			background: #e2e3e5;
			color: #383d41;
		}
		.um-status-info {
			background: #cce5ff;
			color: #004085;
		}
		.um-activity-table {
			width: 100%;
			border-collapse: collapse;
		}
		.um-activity-table th,
		.um-activity-table td {
			padding: 10px 12px;
			text-align: left;
			border-bottom: 1px solid #dcdcde;
		}
		.um-activity-table th {
			background: #f6f7f7;
			font-weight: 600;
			font-size: 12px;
		}
		.um-activity-table tr:hover {
			background: #f9f9f9;
		}
		.um-nicetime {
			color: #646970;
			font-size: 12px;
		}
		.um-template-card {
			background: #f9f9f9;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			padding: 16px;
			margin-bottom: 16px;
		}
		.um-template-card-header {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			margin-bottom: 12px;
		}
		.um-template-card-title {
			font-weight: 600;
			font-size: 14px;
			margin: 0;
		}
		.um-template-card-desc {
			color: #646970;
			font-size: 12px;
			margin: 4px 0 0;
		}
		.um-template-actions {
			display: flex;
			gap: 8px;
		}
		.um-bulk-preview {
			background: #f6f7f7;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			padding: 12px;
			margin-top: 12px;
			max-height: 200px;
			overflow-y: auto;
		}
		.um-bulk-preview table {
			width: 100%;
			font-size: 12px;
		}
		.um-send-email-option {
			background: #fff8e5;
			border: 1px solid #ffcc00;
			border-radius: 4px;
			padding: 12px;
			margin-top: 16px;
		}
		.um-feature-list {
			display: grid;
			gap: 12px;
		}
		.um-feature-item {
			display: flex;
			align-items: flex-start;
			gap: 10px;
			padding: 12px;
			background: #f9f9f9;
			border-radius: 4px;
		}
		.um-feature-item .dashicons {
			color: #2271b1;
			margin-top: 2px;
		}
		.um-feature-item-content h4 {
			margin: 0 0 4px;
			font-size: 13px;
		}
		.um-feature-item-content p {
			margin: 0;
			font-size: 12px;
			color: #50575e;
		}
		.um-info-box {
			background: #e7f3ff;
			border: 1px solid #72aee6;
			border-radius: 4px;
			padding: 12px 16px;
			margin-top: 16px;
			display: flex;
			gap: 12px;
			align-items: flex-start;
		}
		.um-info-box .dashicons {
			color: #2271b1;
			margin-top: 2px;
		}
		.um-info-box strong {
			display: block;
			margin-bottom: 4px;
		}
		.um-info-box p {
			margin: 0;
			font-size: 13px;
			color: #1d2327;
		}
		.um-modal {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0,0,0,0.6);
			z-index: 100000;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.um-modal-content {
			background: #fff;
			border-radius: 4px;
			box-shadow: 0 4px 20px rgba(0,0,0,0.3);
			max-width: 90%;
			max-height: 90vh;
			overflow: hidden;
			display: flex;
			flex-direction: column;
		}
		.um-modal-header {
			padding: 16px 20px;
			border-bottom: 1px solid #dcdcde;
			background: #f6f7f7;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
		.um-modal-header h3 {
			margin: 0;
			font-size: 16px;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.um-modal-close {
			background: none;
			border: none;
			font-size: 24px;
			cursor: pointer;
			color: #50575e;
			padding: 0;
			line-height: 1;
		}
		.um-modal-close:hover {
			color: #d63638;
		}
		.um-modal-body {
			padding: 20px;
			overflow-y: auto;
		}
		';
	}

	/**
	 * Render main settings page with tabs.
	 */
	public static function render_settings_page(): void {
		if (!current_user_can('manage_options')) {
			return;
		}

		$active_tab = self::get_current_tab();
		$settings = self::get_settings();
		$addon_main_navigation_tabs = self::get_enabled_addon_main_navigation_tabs($settings);
		$current_addon_section = $active_tab === self::TAB_ADDONS && isset($_GET['addon_section'])
			? sanitize_key(wp_unslash($_GET['addon_section']))
			: '';
		$current_block_section = $active_tab === self::TAB_BLOCKS && isset($_GET['block_section'])
			? sanitize_key(wp_unslash($_GET['block_section']))
			: '';
		$current_shortcut_section = $current_addon_section !== '' ? $current_addon_section : $current_block_section;
		$active_addon_shortcut_slug = '';
		foreach ($addon_main_navigation_tabs as $addon_tab_meta) {
			if ($current_shortcut_section === (string) ($addon_tab_meta['slug'] ?? '')) {
				$active_addon_shortcut_slug = $current_shortcut_section;
				break;
			}
		}
		$message = isset($_GET['um_msg']) ? sanitize_key(wp_unslash($_GET['um_msg'])) : '';
		?>
		<div class="wrap">
			<h1><?php echo esc_html__('User Experience Manager', 'user-manager'); ?></h1>
			<p style="margin-top: -10px; color: #646970; font-size: 14px;"><?php echo esc_html(sprintf(__('Version %s', 'user-manager'), self::VERSION)); ?></p>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php echo $active_tab === self::TAB_LOGIN_TOOLS ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(self::get_page_url(self::TAB_LOGIN_TOOLS)); ?>">
					<span class="dashicons dashicons-admin-users" style="font-size:16px;line-height:1.4;"></span>
					<?php esc_html_e('Login Tools', 'user-manager'); ?>
				</a>
				<a class="nav-tab <?php echo $active_tab === self::TAB_SETTINGS ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(self::get_page_url(self::TAB_SETTINGS)); ?>">
					<span class="dashicons dashicons-admin-settings" style="font-size:16px;line-height:1.4;"></span>
					<?php esc_html_e('Settings', 'user-manager'); ?>
				</a>
				<a class="nav-tab <?php echo $active_tab === self::TAB_REPORTS ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(self::get_page_url(self::TAB_REPORTS)); ?>">
					<span class="dashicons dashicons-chart-bar" style="font-size:16px;line-height:1.4;"></span>
					<?php esc_html_e('Reports', 'user-manager'); ?>
				</a>
				<a class="nav-tab <?php echo $active_tab === self::TAB_ADDONS && $active_addon_shortcut_slug === '' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(self::get_page_url(self::TAB_ADDONS)); ?>">
					<span class="dashicons dashicons-admin-plugins" style="font-size:16px;line-height:1.4;"></span>
					<?php esc_html_e('Add-ons', 'user-manager'); ?>
				</a>
				<a class="nav-tab <?php echo $active_tab === self::TAB_BLOCKS ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(self::get_page_url(self::TAB_BLOCKS)); ?>">
					<span class="dashicons dashicons-screenoptions" style="font-size:16px;line-height:1.4;"></span>
					<?php esc_html_e('Blocks', 'user-manager'); ?>
				</a>
				<a class="nav-tab <?php echo $active_tab === self::TAB_DOCUMENTATION ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(self::get_page_url(self::TAB_DOCUMENTATION)); ?>">
					<span class="dashicons dashicons-book" style="font-size:16px;line-height:1.4;"></span>
					<?php esc_html_e('Docs', 'user-manager'); ?>
				</a>
				<?php foreach ($addon_main_navigation_tabs as $addon_tab_meta) : ?>
					<?php
					$addon_slug = isset($addon_tab_meta['slug']) ? sanitize_key((string) $addon_tab_meta['slug']) : '';
					$addon_label = isset($addon_tab_meta['label']) ? (string) $addon_tab_meta['label'] : '';
					$addon_url = isset($addon_tab_meta['url']) ? (string) $addon_tab_meta['url'] : '';
					if ($addon_slug === '' || $addon_label === '' || $addon_url === '') {
						continue;
					}
					?>
					<a class="nav-tab <?php echo $active_addon_shortcut_slug === $addon_slug ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url($addon_url); ?>">
						<?php echo esc_html($addon_label); ?>
					</a>
				<?php endforeach; ?>
			</h2>
			<?php if ($active_tab === self::TAB_LOGIN_TOOLS) : ?>
				<?php self::render_login_tools_sub_navigation(); ?>
			<?php endif; ?>
			<?php self::render_admin_notice($message); ?>
			<?php User_Manager_Tabs::render_tab($active_tab); ?>
		</div>
		<?php
	}

	/**
	 * Get current active tab.
	 */
	public static function get_current_tab(): string {
		$tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : self::TAB_LOGIN_TOOLS;
		if (in_array($tab, [self::TAB_CREATE_USER, self::TAB_BULK_CREATE, self::TAB_RESET_PASSWORD, self::TAB_REMOVE_USER, self::TAB_DEACTIVATE_USER, self::TAB_LOGIN_AS], true)) {
			return self::TAB_LOGIN_TOOLS;
		}
		if ($tab === self::TAB_ROLE_SWITCHING) {
			return self::TAB_ADDONS;
		}
		if ($tab === self::TAB_COUPONS) {
			return self::TAB_ADDONS;
		}
		if ($tab === self::TAB_BULK_COUPONS) {
			return self::TAB_ADDONS;
		}
		if ($tab === self::TAB_LOGIN_HISTORY || $tab === self::TAB_ACTIVITY_LOG) {
			return self::TAB_REPORTS;
		}
		if ($tab === self::TAB_TOOLS && isset($_GET['coupon_lookup_email'])) {
			return self::TAB_REPORTS;
		}
		if ($tab === self::TAB_EMAIL_TEMPLATES || $tab === self::TAB_TOOLS) {
			return self::TAB_SETTINGS;
		}
		if ($tab === self::TAB_EMAIL_USERS) {
			$settings = self::get_settings();
			if (!self::is_send_email_addon_enabled($settings)) {
				return self::TAB_ADDONS;
			}
		}
		if ($tab === self::TAB_VERSIONS) {
			return self::TAB_DOCUMENTATION;
		}
		$allowed = [
			self::TAB_LOGIN_TOOLS,
			self::TAB_CREATE_USER,
			self::TAB_RESET_PASSWORD,
			self::TAB_REMOVE_USER,
			self::TAB_DEACTIVATE_USER,
			self::TAB_LOGIN_AS,
			self::TAB_BULK_CREATE,
			self::TAB_EMAIL_USERS,
			self::TAB_LOGIN_HISTORY,
			self::TAB_ACTIVITY_LOG,
			self::TAB_EMAIL_TEMPLATES,
			self::TAB_BULK_COUPONS,
			self::TAB_COUPONS,
			self::TAB_TOOLS,
			self::TAB_SETTINGS,
			self::TAB_ADDONS,
			self::TAB_BLOCKS,
			self::TAB_REPORTS,
			self::TAB_DOCUMENTATION,
			self::TAB_VERSIONS,
		];

		return in_array($tab, $allowed, true) ? $tab : self::TAB_LOGIN_TOOLS;
	}

	/**
	 * Resolve the active child screen for Login Tools.
	 *
	 * Supports legacy ?tab=create-user style URLs and the new
	 * ?tab=login-tools&login_tools_section=create-user format.
	 */
	public static function get_current_login_tools_tab(): string {
		$valid_login_tool_tabs = [
			self::TAB_CREATE_USER,
			self::TAB_BULK_CREATE,
			self::TAB_RESET_PASSWORD,
			self::TAB_REMOVE_USER,
			self::TAB_DEACTIVATE_USER,
			self::TAB_LOGIN_AS,
		];

		$legacy_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';
		if (in_array($legacy_tab, $valid_login_tool_tabs, true)) {
			return $legacy_tab;
		}

		$section = isset($_GET['login_tools_section']) ? sanitize_key(wp_unslash($_GET['login_tools_section'])) : '';
		if (in_array($section, $valid_login_tool_tabs, true)) {
			return $section;
		}

		return self::TAB_CREATE_USER;
	}

	/**
	 * Render Login Tools sub navigation.
	 */
	private static function render_login_tools_sub_navigation(): void {
		$active_login_tool_tab = self::get_current_login_tools_tab();
		$base_login_tools_url  = self::get_page_url(self::TAB_LOGIN_TOOLS);

		$create_url = add_query_arg('login_tools_section', self::TAB_CREATE_USER, $base_login_tools_url);
		$bulk_create_url = add_query_arg('login_tools_section', self::TAB_BULK_CREATE, $base_login_tools_url);
		$reset_url = add_query_arg('login_tools_section', self::TAB_RESET_PASSWORD, $base_login_tools_url);
		$remove_url = add_query_arg('login_tools_section', self::TAB_REMOVE_USER, $base_login_tools_url);
		$deactivate_url = add_query_arg('login_tools_section', self::TAB_DEACTIVATE_USER, $base_login_tools_url);
		$login_as_url = add_query_arg('login_tools_section', self::TAB_LOGIN_AS, $base_login_tools_url);
		$recent_logins_url = add_query_arg(
			[
				'page'   => self::SETTINGS_PAGE_SLUG,
				'tab'    => self::TAB_REPORTS,
				'report' => 'user-logins',
			],
			admin_url('admin.php')
		);
		$more_reports_url = add_query_arg(
			[
				'page'   => self::SETTINGS_PAGE_SLUG,
				'tab'    => self::TAB_REPORTS,
				'report' => 'user-logins',
			],
			admin_url('admin.php')
		);
		?>
		<ul class="subsubsub" style="margin: 12px 0 14px;">
			<li>
				<a href="<?php echo esc_url($create_url); ?>" class="<?php echo $active_login_tool_tab === self::TAB_CREATE_USER ? 'current' : ''; ?>">
					<?php esc_html_e('Create Single User', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($bulk_create_url); ?>" class="<?php echo $active_login_tool_tab === self::TAB_BULK_CREATE ? 'current' : ''; ?>">
					<?php esc_html_e('Create Multiple Users', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($reset_url); ?>" class="<?php echo $active_login_tool_tab === self::TAB_RESET_PASSWORD ? 'current' : ''; ?>">
					<?php esc_html_e('Reset Password(s)', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($remove_url); ?>" class="<?php echo $active_login_tool_tab === self::TAB_REMOVE_USER ? 'current' : ''; ?>">
					<?php esc_html_e('Remove User(s)', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($deactivate_url); ?>" class="<?php echo $active_login_tool_tab === self::TAB_DEACTIVATE_USER ? 'current' : ''; ?>">
					<?php esc_html_e('Deactivate User(s)', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($login_as_url); ?>" class="<?php echo $active_login_tool_tab === self::TAB_LOGIN_AS ? 'current' : ''; ?>">
					<?php esc_html_e('Login As a User', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($recent_logins_url); ?>">
					<?php esc_html_e('Recent Logins', 'user-manager'); ?>
				</a> |
			</li>
			<li>
				<a href="<?php echo esc_url($more_reports_url); ?>">
					<?php esc_html_e('More Reports', 'user-manager'); ?>
				</a>
			</li>
		</ul>
		<br class="clear" />
		<?php
	}

	/**
	 * Render custom WP-Admin notifications from Settings > Custom WP-Admin Notifications.
	 * Shows at top of admin screens; optionally limited by URL string match.
	 */
	public static function render_custom_admin_notifications(): void {
		if (!current_user_can('manage_options')) {
			return;
		}
		$settings = get_option(self::OPTION_KEY, []);
		if (!self::is_custom_admin_notifications_addon_enabled($settings)) {
			return;
		}
		$notifications = isset($settings['custom_admin_notifications']) && is_array($settings['custom_admin_notifications']) ? $settings['custom_admin_notifications'] : [];
		if (empty($notifications)) {
			return;
		}
		$request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
		foreach ($notifications as $n) {
			$title = $n['title'] ?? '';
			$body  = $n['body'] ?? '';
			$color = $n['background_color'] ?? '';
			$match = isset($n['url_string_match']) ? trim((string) $n['url_string_match']) : '';
			if ($match !== '' && strpos($request_uri, $match) === false) {
				continue;
			}
			if ($title === '' && $body === '') {
				continue;
			}
			$style = 'margin: 15px 0 20px 0; padding: 12px 16px; border-left-width: 4px; font-size: 13px; line-height: 1.45;';
			if ($color !== '') {
				$style .= ' border-left-color: ' . esc_attr($color) . ';';
			}
			?>
			<div class="notice um-custom-admin-notification" style="<?php echo esc_attr($style); ?>">
				<?php if ($title !== '') : ?>
					<p style="margin: 0 0 6px 0; font-weight: 700; font-size: 1em;"><?php echo esc_html($title); ?></p>
				<?php endif; ?>
				<?php if ($body !== '') : ?>
					<div style="margin: 0; white-space: pre-wrap; font-size: 1em;"><?php echo esc_html($body); ?></div>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Determine whether WP-Admin Notifications add-on is enabled.
	 * Falls back to legacy behavior when no explicit toggle is stored.
	 *
	 * @param array $settings Plugin settings.
	 * @return bool
	 */
	private static function is_custom_admin_notifications_addon_enabled(array $settings): bool {
		if (self::is_addon_temporarily_disabled('wp-admin-notifications')) {
			return false;
		}
		if (array_key_exists('custom_admin_notifications_enabled', $settings)) {
			return !empty($settings['custom_admin_notifications_enabled']);
		}

		$notifications = isset($settings['custom_admin_notifications']) && is_array($settings['custom_admin_notifications']) ? $settings['custom_admin_notifications'] : [];
		foreach ($notifications as $notification) {
			if (!is_array($notification)) {
				continue;
			}
			$title = trim((string) ($notification['title'] ?? ''));
			$body  = trim((string) ($notification['body'] ?? ''));
			if ($title !== '' || $body !== '') {
				return true;
			}
		}

		return false;
	}

	/**
	 * On profile.php and user-edit.php, show a notice at the top with Open User Experience Manager and Reset Password (email pre-filled).
	 */
	public static function render_profile_user_manager_notice(): void {
		global $pagenow;
		if (!isset($pagenow) || ($pagenow !== 'profile.php' && $pagenow !== 'user-edit.php')) {
			return;
		}
		if (!current_user_can('manage_options')) {
			return;
		}
		$settings = self::get_settings();
		if (empty($settings['show_profile_user_manager_notice'])) {
			return;
		}
		$profile_user_email = '';
		if ($pagenow === 'profile.php') {
			$user = wp_get_current_user();
			$profile_user_email = $user->user_email ?? '';
		} elseif ($pagenow === 'user-edit.php' && isset($_GET['user_id'])) {
			$user = get_userdata((int) $_GET['user_id']);
			$profile_user_email = $user ? $user->user_email : '';
		}
		$url         = self::get_page_url();
		$reset_url   = self::get_page_url(self::TAB_RESET_PASSWORD);
		$login_as_url = add_query_arg(
			[
				'page' => self::SETTINGS_PAGE_SLUG,
				'tab' => self::TAB_LOGIN_TOOLS,
				'login_tools_section' => self::TAB_LOGIN_AS,
			],
			admin_url('admin.php')
		);
		if ($profile_user_email !== '') {
			$reset_url = add_query_arg('um_email', rawurlencode($profile_user_email), $reset_url);
			$login_as_url = add_query_arg(
				[
					'um_login_as_email' => $profile_user_email,
					'um_login_as_auto_generate' => '1',
				],
				$login_as_url
			);
		}
		?>
		<div class="notice notice-info um-profile-notice" style="margin: 15px 0 20px 0; padding: 20px 24px; border-left-width: 4px; font-size: 16px; line-height: 1.5;">
			<p style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700;">
				<?php esc_html_e('User Experience Manager plugin is active and recommended for user management.', 'user-manager'); ?>
			</p>
			<p style="margin: 0;">
				<a href="<?php echo esc_url($url); ?>" class="button button-primary button-large" style="font-weight: 600;">
					<?php esc_html_e('Open User Experience Manager', 'user-manager'); ?>
				</a>
				<a href="<?php echo esc_url($reset_url); ?>" class="button button-large" style="font-weight: 600; margin-left: 8px;">
					<?php esc_html_e('Reset Password', 'user-manager'); ?>
				</a>
				<?php if ($profile_user_email !== '') : ?>
					<a href="<?php echo esc_url($login_as_url); ?>" class="button button-large" style="font-weight: 600; margin-left: 8px;">
						<?php esc_html_e('Login As This User', 'user-manager'); ?>
					</a>
				<?php endif; ?>
			</p>
		</div>
		<?php
	}

	/**
	 * On Add New User page (user-new.php), show a large notice recommending User Experience Manager for creating users.
	 * JS hides the default add-user form until the user clicks "No thanks, I want to use the default forms".
	 */
	public static function maybe_render_user_new_notice(): void {
		global $pagenow;
		if (!isset($pagenow) || $pagenow !== 'user-new.php' || !current_user_can('create_users')) {
			return;
		}
		$url = self::get_page_url(self::TAB_CREATE_USER);
		?>
		<div id="um-user-new-notice" class="notice notice-info um-user-new-notice" style="margin: 15px 0 20px 0; padding: 20px 24px; border-left-width: 4px; font-size: 16px; line-height: 1.5;">
			<p style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700;">
				<?php esc_html_e('User Experience Manager plugin is active and recommended for creating all new users.', 'user-manager'); ?>
			</p>
			<p style="margin: 0 0 12px 0;">
				<a href="<?php echo esc_url($url); ?>" class="button button-primary button-large" style="font-weight: 600;">
					<?php esc_html_e('Create user with User Experience Manager', 'user-manager'); ?>
				</a>
			</p>
			<p style="margin: 0; font-size: 14px;">
				<a href="#" id="um-show-default-user-form" class="um-use-default-forms-link"><?php esc_html_e('No thanks, I want to use the default forms.', 'user-manager'); ?></a>
			</p>
		</div>
		<script>
		(function() {
			var hiddenElements = [];

			function hideDefaultForms() {
				hiddenElements = [];
				// Add Existing User: h2, description p, form#adduser
				var addExistingH2 = document.getElementById('add-existing-user');
				if (addExistingH2) {
					hiddenElements.push(addExistingH2);
					addExistingH2.style.display = 'none';
					if (addExistingH2.nextElementSibling && addExistingH2.nextElementSibling.tagName === 'P') {
						hiddenElements.push(addExistingH2.nextElementSibling);
						addExistingH2.nextElementSibling.style.display = 'none';
					}
				}
				var adduserForm = document.getElementById('adduser');
				if (adduserForm) {
					hiddenElements.push(adduserForm);
					adduserForm.setAttribute('data-um-hidden', '1');
					adduserForm.style.display = 'none';
				}
				// Add User (create new): h2, description p, form#createuser
				var createNewH2 = document.getElementById('create-new-user');
				if (createNewH2) {
					hiddenElements.push(createNewH2);
					createNewH2.style.display = 'none';
					if (createNewH2.nextElementSibling && createNewH2.nextElementSibling.tagName === 'P') {
						hiddenElements.push(createNewH2.nextElementSibling);
						createNewH2.nextElementSibling.style.display = 'none';
					}
				}
				var createuserForm = document.getElementById('createuser');
				if (createuserForm) {
					hiddenElements.push(createuserForm);
					createuserForm.setAttribute('data-um-hidden', '1');
					createuserForm.style.display = 'none';
				}
			}

			function showDefaultForms() {
				hiddenElements.forEach(function(el) {
					if (el && el.removeAttribute) {
						el.removeAttribute('data-um-hidden');
						el.style.display = '';
					}
				});
				hiddenElements = [];
				var notice = document.getElementById('um-user-new-notice');
				if (notice) {
					notice.style.display = 'none';
				}
			}

			function init() {
				hideDefaultForms();
				var link = document.getElementById('um-show-default-user-form');
				if (link) {
					link.addEventListener('click', function(e) {
						e.preventDefault();
						showDefaultForms();
					});
				}
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', init);
			} else {
				init();
			}
		})();
		</script>
		<?php
	}

	/**
	 * Build page URL for tab.
	 */
	public static function get_page_url(string $tab = ''): string {
		$tab = $tab ?: self::TAB_LOGIN_TOOLS;
		return add_query_arg(
			[
				'page' => self::SETTINGS_PAGE_SLUG,
				'tab' => $tab,
			],
			admin_url('admin.php')
		);
	}

	/**
	 * Whether Send Email add-on is enabled.
	 *
	 * Send Email is intentionally always enabled because Login Tools
	 * and other template-dependent flows rely on it.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function is_send_email_addon_enabled(array $settings): bool {
		return !self::is_addon_temporarily_disabled('send-email-users');
	}

	/**
	 * Selected add-on slugs configured as main-navigation shortcuts.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array<int,string>
	 */
	public static function get_selected_addon_main_navigation_tabs(array $settings): array {
		if (!isset($settings['addon_main_navigation_tabs']) || !is_array($settings['addon_main_navigation_tabs'])) {
			return [];
		}

		$map = self::get_addon_runtime_toggle_map(false);
		$selected = [];
		foreach ($settings['addon_main_navigation_tabs'] as $slug) {
			$slug = sanitize_key((string) $slug);
			if ($slug === '' || !isset($map[$slug])) {
				continue;
			}
			$selected[] = $slug;
		}

		return array_values(array_unique($selected));
	}

	/**
	 * Build enabled add-on shortcuts for the main tab navigation.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array<int,array{slug:string,label:string,url:string}>
	 */
	public static function get_enabled_addon_main_navigation_tabs(array $settings): array {
		$selected_slugs = self::get_selected_addon_main_navigation_tabs($settings);
		if (empty($selected_slugs)) {
			return [];
		}

		$map = self::get_addon_runtime_toggle_map();
		$tabs = [];
		foreach ($selected_slugs as $slug) {
			if (!isset($map[$slug])) {
				continue;
			}
			if (!self::is_addon_enabled_for_main_navigation($slug, $settings)) {
				continue;
			}
			$is_blocks_shortcut = in_array($slug, self::get_blocks_tab_section_slugs(), true);
			$shortcut_url = $is_blocks_shortcut
				? add_query_arg('block_section', $slug, self::get_page_url(self::TAB_BLOCKS))
				: add_query_arg('addon_section', $slug, self::get_page_url(self::TAB_ADDONS));
			$tabs[] = [
				'slug'  => $slug,
				'label' => isset($map[$slug]['label']) ? (string) $map[$slug]['label'] : $slug,
				'url'   => $shortcut_url,
			];
		}

		return $tabs;
	}

	/**
	 * Add-on slugs that now render inside the Blocks tab.
	 *
	 * @return array<int,string>
	 */
	private static function get_blocks_tab_section_slugs(): array {
		return [
			'page-block-subpages-grid',
			'page-block-tabbed-content-area',
			'page-block-simple-icons',
			'page-block-menu-tiles',
			'media-library-tags',
		];
	}

	/**
	 * Determine if an add-on is currently enabled for shortcut rendering.
	 *
	 * @param string $addon_slug Add-on slug.
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private static function is_addon_enabled_for_main_navigation(string $addon_slug, array $settings): bool {
		$addon_slug = sanitize_key($addon_slug);
		if ($addon_slug === '' || self::is_addon_temporarily_disabled($addon_slug)) {
			return false;
		}

		if ($addon_slug === 'user-role-switching') {
			$role_settings = get_option('view_website_by_role_settings', []);
			if (!is_array($role_settings)) {
				$role_settings = [];
			}
			return !empty($role_settings['enabled']);
		}

		if ($addon_slug === 'product-search-by-sku' && !array_key_exists('search_redirect_by_sku', $settings)) {
			return true;
		}

		$map = self::get_addon_runtime_toggle_map(false);
		if (!isset($map[$addon_slug]['settings_keys']) || !is_array($map[$addon_slug]['settings_keys'])) {
			return false;
		}

		foreach ($map[$addon_slug]['settings_keys'] as $settings_key) {
			$settings_key = (string) $settings_key;
			if ($settings_key === '' || $settings_key === '__role_switching_option_enabled') {
				continue;
			}
			if (!empty($settings[$settings_key])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build redirect URL with message.
	 */
	public static function get_redirect_with_message(string $tab, string $message, array $extra = []): string {
		$args = [
			'page' => self::SETTINGS_PAGE_SLUG,
			'tab' => $tab,
			'um_msg' => $message,
		];
		
		// Merge extra query args
		$args = array_merge($args, $extra);
		
		return add_query_arg($args, admin_url('admin.php'));
	}

	/**
	 * Render admin notices.
	 */
	public static function render_admin_notice(string $message): void {
		if (empty($message)) {
			return;
		}

		$content = '';
		$type = 'success';

		switch ($message) {
			case 'user_created':
				$content = __('User created successfully.', 'user-manager');
				break;
			case 'user_created_email_sent':
				$content = __('User created successfully and email sent.', 'user-manager');
				break;
			case 'user_updated':
				$content = __('Existing user updated successfully.', 'user-manager');
				break;
			case 'user_updated_email_sent':
				$content = __('Existing user updated successfully and email sent.', 'user-manager');
				break;
			case 'password_reset':
				$content = __('Password reset successfully.', 'user-manager');
				break;
			case 'password_reset_email_sent':
				$content = __('Password reset successfully and email sent.', 'user-manager');
				break;
			case 'bulk_created':
				$count = isset($_GET['count']) ? absint($_GET['count']) : 0;
				$content = sprintf(__('%d users created/updated successfully.', 'user-manager'), $count);
				$bulk_ref = isset($_GET['bulk_ref']) ? sanitize_text_field(wp_unslash($_GET['bulk_ref'])) : '';
				if ($bulk_ref) {
					$notice_data = get_transient('um_bulk_notice_' . $bulk_ref);
					if ($notice_data && ( !empty($notice_data['created']) || !empty($notice_data['updated']) )) {
						delete_transient('um_bulk_notice_' . $bulk_ref);
						$sections_html = '';
						if (!empty($notice_data['created'])) {
							$sections_html .= self::render_user_notice_section(__('Created users', 'user-manager'), $notice_data['created']);
						}
						if (!empty($notice_data['updated'])) {
							$sections_html .= self::render_user_notice_section(__('Updated users', 'user-manager'), $notice_data['updated']);
						}
						if ($sections_html) {
							$content .= '<div class="um-notice-user-sections">' . $sections_html . '</div>';
						}
					}
				}
				break;
			case 'bulk_coupons_created':
				$count = isset($_GET['count']) ? absint($_GET['count']) : 0;
				if ($count <= 1) {
					/* translators: %d: number of coupons created */
					$content = sprintf(__('%d coupon created successfully.', 'user-manager'), $count);
				} else {
					/* translators: %d: number of coupons created */
					$content = sprintf(__('%d coupons created successfully.', 'user-manager'), $count);
				}
				$bulk_ref = isset($_GET['bulk_ref']) ? sanitize_text_field(wp_unslash($_GET['bulk_ref'])) : '';
				if ($bulk_ref) {
					$notice_data = get_transient('um_bulk_coupons_' . $bulk_ref);
					if ($notice_data && !empty($notice_data['coupons']) && is_array($notice_data['coupons'])) {
						delete_transient('um_bulk_coupons_' . $bulk_ref);
						$codes      = [];
						$list_items = '';

						foreach ($notice_data['coupons'] as $coupon) {
							$code  = isset($coupon['code']) ? (string) $coupon['code'] : '';
							$link  = isset($coupon['link']) ? (string) $coupon['link'] : '';
							$email = isset($coupon['email']) ? (string) $coupon['email'] : '';
							if (!$code) {
								continue;
							}
							$codes[] = $code;

							$list_items .= '<li>';
							if ($link) {
								$list_items .= '<a href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">' . esc_html($code) . '</a>';
							} else {
								$list_items .= esc_html($code);
							}
							if ($email) {
								$list_items .= ' <span class="description">(' . esc_html($email) . ')</span>';
							}
							$list_items .= '</li>';
						}

						if (!empty($codes)) {
							$codes_text = implode("\n", $codes);
							$content   .= '<div class="um-notice-bulk-coupons">';
							$content   .= '<p><strong>' . esc_html__('New coupon codes (click to select and copy)', 'user-manager') . '</strong></p>';
							$content   .= '<textarea readonly rows="4" class="large-text code" style="font-family:monospace; resize:vertical;" onclick="this.focus();this.select();">' . esc_textarea($codes_text) . '</textarea>';
							$content   .= '<p style="margin-top:8px;"><strong>' . esc_html__('Links to new coupons', 'user-manager') . '</strong></p>';
							$content   .= '<ul class="um-notice-coupons-list">' . $list_items . '</ul>';
							$content   .= '</div>';
						}
					}
				}
				break;
			case 'template_saved':
				$content = __('Email template saved successfully.', 'user-manager');
				break;
			case 'template_deleted':
				$content = __('Email template deleted.', 'user-manager');
				break;
			case 'sms_template_saved':
				$content = __('SMS text template saved successfully.', 'user-manager');
				break;
			case 'sms_template_deleted':
				$content = __('SMS text template deleted.', 'user-manager');
				break;
			case 'sms_template_error':
				$content = __('SMS text template could not be saved. Please make sure required fields are filled.', 'user-manager');
				$type = 'error';
				break;
			case 'settings_saved':
				$content = __('Settings saved successfully.', 'user-manager');
				break;
			case 'emali_log_resent':
				$content = __('Email resent successfully from Email Log.', 'user-manager');
				break;
			case 'emali_log_resend_failed':
				$content = __('Unable to resend this Email Log email entry.', 'user-manager');
				$type = 'error';
				break;
			case 'emali_log_forwarded':
				$content = __('Email forwarded successfully from Email Log.', 'user-manager');
				break;
			case 'emali_log_forward_failed':
				$content = __('Unable to forward this Email Log email entry. Check the forward address and try again.', 'user-manager');
				$type = 'error';
				break;
			case 'emali_log_cleared':
				$content = __('Email Log history has been cleared.', 'user-manager');
				break;
			case 'emali_log_auto_cleanup_saved':
				$content = __('Email Log auto-delete retention was updated.', 'user-manager');
				break;
			case 'view_reports_reset':
				$content = __('All view-related reports (Page Views, Product Views, 404 Errors, Search Queries) have been reset.', 'user-manager');
				break;
			case 'user_exists':
				$content = __('A user with that email already exists. Enable "Update existing users" in Settings to update instead.', 'user-manager');
				$type = 'error';
				break;
			case 'user_added_to_subsite':
				$content = __('Existing network user was added to this sub-site successfully.', 'user-manager');
				break;
			case 'user_not_found':
				$content = __('No user found with that email address.', 'user-manager');
				$type = 'error';
				break;
			case 'blog_importer_ok':
				$count = isset($_GET['count']) ? absint($_GET['count']) : 0;
				$content = sprintf(__('%d blog post(s) created successfully.', 'user-manager'), $count);
				$created = get_transient('um_blog_importer_created_' . get_current_user_id());
				if (!empty($created) && is_array($created)) {
					delete_transient('um_blog_importer_created_' . get_current_user_id());
					$content .= '<div class="um-blog-importer-created-tiles" style="display:grid; grid-template-columns:repeat(auto-fill, 200px); gap:12px; margin-top:12px;" data-thumb-nonce="' . esc_attr(wp_create_nonce('user_manager_set_post_thumbnail')) . '" data-date-nonce="' . esc_attr(wp_create_nonce('user_manager_set_post_date')) . '">';
					foreach ($created as $item) {
						$id = (int) ($item['id'] ?? 0);
						$title = isset($item['title']) ? esc_html($item['title']) : __('(no title)', 'user-manager');
						$content .= '<div class="um-blog-importer-tile" data-post-id="' . esc_attr((string) $id) . '" style="max-width:200px; border:1px solid #c3c4c7; border-radius:6px; overflow:hidden; background:#fff;">';
						if ($id) {
							$thumb_id = get_post_thumbnail_id($id);
							$thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
							$content .= '<div class="um-blog-importer-tile-thumb" style="aspect-ratio:16/10; background:#f0f0f1; max-width:200px;">';
							if ($thumb_url) {
								$content .= '<a href="' . esc_url(get_permalink($id)) . '" target="_blank" rel="noopener noreferrer" style="display:block; width:100%; height:100%;">';
								$content .= '<img src="' . esc_url($thumb_url) . '" alt="" style="width:100%; height:100%; object-fit:cover; max-width:200px;" loading="lazy" />';
								$content .= '</a>';
							} else {
								$content .= '<div class="um-blog-importer-tile-no-img" style="width:100%; height:100%; color:#787c82; font-size:12px; display:flex; align-items:center; justify-content:center;">' . esc_html__('No image', 'user-manager') . '</div>';
							}
							$content .= '</div>';
							$content .= '<div style="padding:8px;">';
							$content .= '<strong style="display:block; margin-bottom:4px; font-size:13px; line-height:1.3; word-wrap:break-word;">' . $title . '</strong>';
							$post_date = $id ? get_the_date('', $id) : '';
							$post_date_ymd = $id ? get_the_date('Y-m-d', $id) : '';
							if ($post_date) {
								$content .= '<button type="button" class="um-blog-importer-change-date link-button" style="display:block; font-size:12px; color:#2271b1; margin-bottom:8px; padding:0; border:0; background:none; cursor:pointer; text-align:left;" data-post-id="' . esc_attr((string) $id) . '" data-date="' . esc_attr($post_date_ymd) . '" title="' . esc_attr__('Click to change date', 'user-manager') . '">' . esc_html($post_date) . '</button>';
							}
							$view_url = get_permalink($id);
							$edit_url = get_edit_post_link($id, 'raw');
							$content .= '<div class="um-blog-importer-links" style="display:block; margin-top:8px; border-top:1px solid #e0e0e0; padding-top:8px;">';
							$content .= '<span style="display:flex; flex-direction:row; flex-wrap:wrap; gap:4px; align-items:center;">';
							if ($view_url) {
								$content .= '<a href="' . esc_url($view_url) . '" target="_blank" rel="noopener noreferrer" class="button button-small" style="flex:1; min-width:0; text-align:center; text-decoration:none; padding:4px;" title="' . esc_attr__('View', 'user-manager') . '"><span class="dashicons dashicons-visibility" style="font-size:18px; width:18px; height:18px;"></span></a>';
							}
							if ($edit_url) {
								$content .= '<a href="' . esc_url($edit_url) . '" class="button button-small" style="flex:1; min-width:0; text-align:center; text-decoration:none; padding:4px;" title="' . esc_attr__('Edit', 'user-manager') . '"><span class="dashicons dashicons-edit" style="font-size:18px; width:18px; height:18px;"></span></a>';
							}
							$content .= '<button type="button" class="button button-small um-blog-importer-change-thumb" data-post-id="' . esc_attr((string) $id) . '" style="flex:1; min-width:0; padding:4px;" title="' . esc_attr__('Thumbnail', 'user-manager') . '"><span class="dashicons dashicons-format-image" style="font-size:18px; width:18px; height:18px;"></span></button>';
							$content .= '</span></div></div>';
						} else {
							$content .= '<div style="padding:10px;"><strong>' . $title . '</strong></div>';
						}
						$content .= '</div>';
					}
					$content .= '</div>';
					// Recent posts list (newest to oldest, including scheduled)
					$recent_posts = get_posts([
						'post_type'      => 'post',
						'post_status'    => ['publish', 'future'],
						'orderby'        => 'date',
						'order'          => 'DESC',
						'posts_per_page' => 20,
						'no_found_rows'  => true,
					]);
					if (!empty($recent_posts)) {
						$scheduled_for_spread = get_posts(['post_type' => 'post', 'post_status' => 'future', 'posts_per_page' => -1, 'fields' => 'ids']);
						$scheduled_count = is_array($scheduled_for_spread) ? count($scheduled_for_spread) : 0;
						$plus_days = max(1, min(365, (int) get_option('um_blog_importer_plus_days', 25)));
						$recommended_ts = (int) current_time('timestamp') + ($scheduled_count * $plus_days * DAY_IN_SECONDS);
						$recommended_date = wp_date('Y-m-d', $recommended_ts);
						$spread_nonce = wp_create_nonce('user_manager_spread_scheduled_posts');
						$content .= '<div class="um-blog-importer-recent-posts" style="margin-top:24px; padding-top:16px; border-top:1px solid #c3c4c7;" data-spread-nonce="' . esc_attr($spread_nonce) . '" data-recommended-date="' . esc_attr($recommended_date) . '">';
						$content .= '<h4 style="margin:0 0 8px 0;">' . esc_html__('Recent posts (newest to oldest)', 'user-manager') . '</h4>';
						$content .= '<p style="margin:0 0 8px 0;">';
						$content .= esc_html(sprintf(_n('%d scheduled post', '%d scheduled posts', $scheduled_count, 'user-manager'), $scheduled_count));
						if ($scheduled_count > 0) {
							$content .= ' · ' . esc_html(sprintf(__('Recommended date: %1$s (today + %2$d × %3$d days)', 'user-manager'), wp_date(get_option('date_format'), $recommended_ts), $scheduled_count, $plus_days));
						}
						$content .= '</p><p style="margin:0 0 8px 0;">';
						$content .= '<label for="um-blog-spread-date-msg" style="margin-right:8px; font-weight:600;">' . esc_html__('Spread to date:', 'user-manager') . '</label>';
						$content .= '<input type="date" id="um-blog-spread-date-msg" class="um-blog-importer-spread-date" style="margin-right:12px; min-width:160px;" value="' . ($scheduled_count > 0 ? esc_attr($recommended_date) : '') . '" /> ';
						$content .= '<button type="button" class="button um-blog-importer-spread-scheduled-btn">' . esc_html__('Evenly spread all scheduled posts out to this date', 'user-manager') . '</button>';
						$content .= '</p>';
						$content .= '<table class="widefat striped" style="margin-top:8px;"><thead><tr>';
						$content .= '<th>' . esc_html__('Title', 'user-manager') . '</th>';
						$content .= '<th>' . esc_html__('Status', 'user-manager') . '</th>';
						$content .= '<th>' . esc_html__('Date', 'user-manager') . '</th>';
						$content .= '<th>' . esc_html__('Days since previous post', 'user-manager') . '</th>';
						$content .= '<th>' . esc_html__('New post suggested date', 'user-manager') . '</th>';
						$content .= '</tr></thead><tbody>';
						$prev_ts = null;
						foreach ($recent_posts as $rp) {
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
							$content .= '<tr>';
							$content .= '<td>';
							if ($rp_edit) {
								$content .= '<a href="' . esc_url($rp_edit) . '">' . esc_html($rp_title) . '</a>';
							} else {
								$content .= esc_html($rp_title);
							}
							$rp_status = get_post_status($rp_id);
							$status_label = ($rp_status === 'future') ? __('Scheduled', 'user-manager') : __('Published', 'user-manager');
							$content .= '</td><td>' . esc_html($status_label) . '</td><td>' . esc_html($rp_date) . '</td><td style="' . esc_attr($days_cell_style) . '">' . esc_html($days_cell) . '</td><td style="' . esc_attr($suggested_date_style) . '">' . esc_html($suggested_date_cell) . '</td></tr>';
						}
						$content .= '</tbody></table></div>';
					}
				}
				break;
			case 'blog_importer_no_posts':
				$content = __('No posts to create. Please add at least one post with a title.', 'user-manager');
				$type = 'warning';
				break;
			case 'error':
				$content = __('Something went wrong. Please try again.', 'user-manager');
				$type = 'error';
				break;
			case 'sftp_imported':
				$created = isset($_GET['created']) ? absint($_GET['created']) : 0;
				$updated = isset($_GET['updated']) ? absint($_GET['updated']) : 0;
				$skipped = isset($_GET['skipped']) ? absint($_GET['skipped']) : 0;
				$failed = isset($_GET['failed']) ? absint($_GET['failed']) : 0;
				$content = sprintf(
					__('SFTP Import complete: %d created, %d updated, %d skipped, %d failed.', 'user-manager'),
					$created, $updated, $skipped, $failed
				);
				$type = $failed > 0 ? 'warning' : 'success';
				break;
			case 'sftp_imported_email_sent':
				$created = isset($_GET['created']) ? absint($_GET['created']) : 0;
				$updated = isset($_GET['updated']) ? absint($_GET['updated']) : 0;
				$skipped = isset($_GET['skipped']) ? absint($_GET['skipped']) : 0;
				$failed = isset($_GET['failed']) ? absint($_GET['failed']) : 0;
				$emails = isset($_GET['emails']) ? absint($_GET['emails']) : 0;
				$content = sprintf(
					__('SFTP Import complete: %d created, %d updated, %d skipped, %d failed. %d emails sent.', 'user-manager'),
					$created, $updated, $skipped, $failed, $emails
				);
				$type = $failed > 0 ? 'warning' : 'success';
				break;
			case 'file_not_found':
				$content = __('The specified file was not found or is not readable.', 'user-manager');
				$type = 'error';
				break;
			case 'file_not_allowed':
				$content = __('The file is not in an allowed directory. Check your SFTP settings.', 'user-manager');
				$type = 'error';
				break;
			case 'file_read_error':
				$content = __('Could not read the file. Please check file permissions.', 'user-manager');
				$type = 'error';
				break;
			case 'no_data':
				$content = __('The file contains no data to import.', 'user-manager');
				$type = 'error';
				break;
			case 'bulk_password_reset':
				$reset = isset($_GET['reset']) ? absint($_GET['reset']) : 0;
				$not_found = isset($_GET['not_found']) ? absint($_GET['not_found']) : 0;
				$content = sprintf(
					__('Passwords reset for %d users. %d emails not found.', 'user-manager'),
					$reset, $not_found
				);
				$type = $not_found > 0 ? 'warning' : 'success';
				break;
			case 'bulk_password_reset_email_sent':
				$reset = isset($_GET['reset']) ? absint($_GET['reset']) : 0;
				$not_found = isset($_GET['not_found']) ? absint($_GET['not_found']) : 0;
				$emails = isset($_GET['emails']) ? absint($_GET['emails']) : 0;
				$content = sprintf(
					__('Passwords reset for %d users. %d emails not found. %d notification emails sent.', 'user-manager'),
					$reset, $not_found, $emails
				);
				$type = $not_found > 0 ? 'warning' : 'success';
				break;
			case 'emails_sent':
				$sent = isset($_GET['sent']) ? absint($_GET['sent']) : 0;
				$not_found = isset($_GET['not_found']) ? absint($_GET['not_found']) : 0;
				$content = sprintf(
					__('Emails sent to %d users. %d email addresses not found.', 'user-manager'),
					$sent, $not_found
				);
				$type = $not_found > 0 ? 'warning' : 'success';
				break;
			case 'emails_sent_batch':
				$sent = isset($_GET['sent']) ? absint($_GET['sent']) : 0;
				$not_found = isset($_GET['not_found']) ? absint($_GET['not_found']) : 0;
				$remaining = isset($_GET['remaining']) ? absint($_GET['remaining']) : 0;
				$total = isset($_GET['total']) ? absint($_GET['total']) : 0;
				$total_sent = isset($_GET['total_sent']) ? absint($_GET['total_sent']) : $sent;
				$content = sprintf(
					__('Batch sent: %d emails in this batch. Total progress: %d of %d sent. %d remaining. Click "Send Next Batch" to continue.', 'user-manager'),
					$sent, $total_sent, $total, $remaining
				);
				$type = 'info';
				break;
			case 'batch_complete':
				$sent = isset($_GET['sent']) ? absint($_GET['sent']) : 0;
				$total = isset($_GET['total']) ? absint($_GET['total']) : 0;
				$content = sprintf(
					__('All emails sent! Final batch: %d emails. Total: %d emails sent.', 'user-manager'),
					$sent, $total
				);
				$type = 'success';
				break;
			case 'texts_sent':
				$sent = isset($_GET['sent']) ? absint($_GET['sent']) : 0;
				$not_found = isset($_GET['not_found']) ? absint($_GET['not_found']) : 0;
				$failed = isset($_GET['failed']) ? absint($_GET['failed']) : 0;
				$content = sprintf(
					__('SMS texts sent: %1$d. Sent without user match: %2$d. Failed to send: %3$d.', 'user-manager'),
					$sent,
					$not_found,
					$failed
				);
				$type = $failed > 0 ? 'warning' : 'success';
				break;
			case 'texts_sent_batch':
				$sent = isset($_GET['sent']) ? absint($_GET['sent']) : 0;
				$not_found = isset($_GET['not_found']) ? absint($_GET['not_found']) : 0;
				$failed = isset($_GET['failed']) ? absint($_GET['failed']) : 0;
				$remaining = isset($_GET['remaining']) ? absint($_GET['remaining']) : 0;
				$total = isset($_GET['total']) ? absint($_GET['total']) : 0;
				$total_sent = isset($_GET['total_sent']) ? absint($_GET['total_sent']) : $sent;
				$content = sprintf(
					__('Text batch sent: %1$d texts in this batch. Total progress: %2$d of %3$d sent. %4$d remaining. Sent without user match: %5$d. Failed to send: %6$d.', 'user-manager'),
					$sent,
					$total_sent,
					$total,
					$remaining,
					$not_found,
					$failed
				);
				$type = $failed > 0 ? 'warning' : 'info';
				break;
			case 'text_batch_complete':
				$sent = isset($_GET['sent']) ? absint($_GET['sent']) : 0;
				$total = isset($_GET['total']) ? absint($_GET['total']) : 0;
				$content = sprintf(
					__('All queued texts sent! Final batch: %1$d. Total texts sent: %2$d.', 'user-manager'),
					$sent,
					$total
				);
				$type = 'success';
				break;
			case 'no_text_batch':
				$content = __('No pending text batch found.', 'user-manager');
				$type = 'warning';
				break;
			case 'no_phone_numbers':
				$content = __('Please enter at least one phone number.', 'user-manager');
				$type = 'error';
				break;
			case 'no_sms_template':
				$content = __('Please select an SMS text template.', 'user-manager');
				$type = 'error';
				break;
			case 'send_sms_disabled':
				$content = __('Send SMS Text add-on is not active.', 'user-manager');
				$type = 'error';
				break;
			case 'sms_token_missing':
				$content = __('Simple Texting API Token is missing. Add your token in Settings → API Keys.', 'user-manager');
				$type = 'error';
				break;
			case 'no_batch':
				$content = __('No pending email batch found.', 'user-manager');
				$type = 'warning';
				break;
			case 'list_saved':
				$content = __('Email list saved successfully.', 'user-manager');
				$type = 'success';
				break;
			case 'list_deleted':
				$content = __('Email list deleted successfully.', 'user-manager');
				$type = 'success';
				break;
			case 'list_title_required':
				$content = __('Please enter a title for the email list.', 'user-manager');
				$type = 'error';
				break;
			case 'list_not_found':
				$content = __('Email list not found.', 'user-manager');
				$type = 'error';
				break;
			case 'no_emails':
				$content = __('Please enter at least one email address.', 'user-manager');
				$type = 'error';
				break;
			case 'no_template':
				$content = __('Please select an email template.', 'user-manager');
				$type = 'error';
				break;
			case 'directory_created':
				$content = __('Directory created successfully.', 'user-manager');
				break;
			case 'directory_error':
				$content = __('Invalid directory path.', 'user-manager');
				$type = 'error';
				break;
			case 'directory_not_allowed':
				$content = __('Directory is not in the configured list.', 'user-manager');
				$type = 'error';
				break;
			case 'directory_create_failed':
				$content = __('Failed to create directory. Check file permissions.', 'user-manager');
				$type = 'error';
				break;
			case 'demo_templates_imported':
				$content = __('Demo email templates imported successfully.', 'user-manager');
				break;
			case 'demo_template_recreated':
				$template_title = isset($_GET['template_title']) ? sanitize_text_field(wp_unslash($_GET['template_title'])) : '';
				if ($template_title !== '') {
					$content = sprintf(
						/* translators: %s: template title */
						__('Template recreated: %s', 'user-manager'),
						$template_title
					);
				} else {
					$content = __('Template recreated successfully.', 'user-manager');
				}
				break;
			case 'demo_sms_templates_imported':
				$content = __('Demo SMS text templates imported successfully.', 'user-manager');
				break;
			case 'migration_success':
				$count = isset($_GET['count']) ? absint($_GET['count']) : 0;
				$content = sprintf(
					_n(
						'Successfully migrated %d store credit coupon to fixed cart type.',
						'Successfully migrated %d store credit coupons to fixed cart type.',
						$count,
						'user-manager'
					),
					$count
				);
				break;
			case 'migration_failed':
				$content = __('Migration failed. Please try again.', 'user-manager');
				$type = 'error';
				break;
			case 'migration_failed_no_wc':
				$content = __('Migration failed: WooCommerce is not active.', 'user-manager');
				$type = 'error';
				break;
			case 'migration_no_selection':
				$content = __('Please select at least one coupon to migrate.', 'user-manager');
				$type = 'warning';
				break;
			case 'activity_log_cleared':
				$content = __('Activity log has been cleared.', 'user-manager');
				break;
			case 'user_removed':
				$content = __('User removed successfully.', 'user-manager');
				break;
			case 'bulk_user_removed':
				$removed = isset($_GET['removed']) ? absint($_GET['removed']) : 0;
				$not_found = isset($_GET['not_found']) ? absint($_GET['not_found']) : 0;
				$content = sprintf(
					__('Removed %d user(s). %d email(s) not found.', 'user-manager'),
					$removed, $not_found
				);
				$type = $not_found > 0 ? 'warning' : 'success';
				break;
			case 'user_deactivated':
				$content = __('User deactivated successfully.', 'user-manager');
				break;
			case 'user_reactivated':
				$content = __('User reactivated successfully.', 'user-manager');
				break;
			case 'user_already_deactivated':
				$content = __('This user is already deactivated.', 'user-manager');
				$type = 'warning';
				break;
			case 'user_not_deactivated':
				$content = __('This user is not currently deactivated.', 'user-manager');
				$type = 'warning';
				break;
			case 'user_deactivate_failed':
				$content = __('User could not be deactivated. Please try again.', 'user-manager');
				$type = 'error';
				break;
			case 'user_reactivate_failed':
				$content = __('User could not be reactivated. Please try again.', 'user-manager');
				$type = 'error';
				break;
			case 'bulk_user_deactivated':
				$deactivated = isset($_GET['deactivated']) ? absint($_GET['deactivated']) : 0;
				$already = isset($_GET['already']) ? absint($_GET['already']) : 0;
				$not_found = isset($_GET['not_found']) ? absint($_GET['not_found']) : 0;
				$failed = isset($_GET['failed']) ? absint($_GET['failed']) : 0;
				$content = sprintf(
					__('Deactivated %1$d user(s). %2$d already deactivated. %3$d identifier(s) not found. %4$d failed.', 'user-manager'),
					$deactivated,
					$already,
					$not_found,
					$failed
				);
				$type = ($not_found > 0 || $failed > 0) ? 'warning' : 'success';
				break;
		}

		if (empty($content)) {
			return;
		}
		
		$debug_append = '';
		if (isset($_GET['um_log_debug'])) {
			$token = sanitize_text_field(wp_unslash($_GET['um_log_debug']));
			$debug_data = self::maybe_get_log_debug_notice($token);
			if (!empty($debug_data)) {
				$debug_append = self::render_log_debug_html($debug_data);
			}
		}
		?>
		<div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
			<div class="um-notice-content"><?php echo wp_kses_post($content . $debug_append); ?></div>
		</div>
		<?php if (strpos($content, 'um-notice-user-sections') !== false) : ?>
			<style>
			.um-notice-user-sections {
				margin-top: 8px;
			}
			.um-notice-user-section {
				margin-bottom: 8px;
			}
			.um-notice-user-section ul {
				margin: 4px 0 0 18px;
				padding: 0;
				list-style: disc;
			}
			.um-notice-user-section li {
				margin-bottom: 2px;
			}
			.um-log-debug table.widefat td {
				padding: 4px 8px;
			}
			</style>
		<?php endif; ?>
		<?php
	}

	private static function render_log_debug_html(array $debug): string {
		$rows = '';
		foreach ($debug as $key => $value) {
			if (is_array($value)) {
				$value = wp_json_encode($value);
			}
			$rows .= '<tr><td><strong>' . esc_html($key) . '</strong></td><td><code>' . esc_html((string) $value) . '</code></td></tr>';
		}
		return '<div class="um-log-debug"><p><strong>' . esc_html__('Log Debug Output', 'user-manager') . '</strong></p><table class="widefat" style="margin:8px 0;">' . $rows . '</table></div>';
	}

	/**
	 * Debug helper: log directly to error log when the setting is enabled.
	 */
	public static function maybe_debug_log(string $message, array $context = []): void {
		$settings = self::get_settings();
		if (empty($settings['log_activity_debug'])) {
			return;
		}
		$entry = '[User Manager] ' . $message;
		if (!empty($context)) {
			$entry .= ' :: ' . wp_json_encode($context);
		}
		error_log($entry);
	}

	/**
	 * Store debug info for later display in admin notices.
	 */
	public static function maybe_store_log_debug_notice(array $debug): ?string {
		$settings = self::get_settings();
		if (empty($settings['log_activity_debug'])) {
			return null;
		}
		if (empty($debug)) {
			return null;
		}
		$token = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('log_', true);
		set_transient('um_log_debug_' . $token, $debug, MINUTE_IN_SECONDS * 5);
		return $token;
	}

	public static function maybe_get_log_debug_notice(string $token): ?array {
		if (empty($token)) {
			return null;
		}
		$data = get_transient('um_log_debug_' . $token);
		if ($data) {
			delete_transient('um_log_debug_' . $token);
		}
		return $data ?: null;
	}

	public static function maybe_add_log_debug_to_url(string $url, array $debug): string {
		$token = self::maybe_store_log_debug_notice($debug);
		if (empty($token)) {
			return $url;
		}
		return add_query_arg('um_log_debug', rawurlencode($token), $url);
	}

	/**
	 * Render a list of users with links inside an admin notice.
	 *
	 * @param string $heading
	 * @param array<int,array{id?:int,email?:string,name?:string}> $users
	 */
	private static function render_user_notice_section(string $heading, array $users): string {
		if (empty($users)) {
			return '';
		}
		$html = '<div class="um-notice-user-section"><strong>' . esc_html($heading) . '</strong><ul>';
		foreach ($users as $user) {
			$user_id = isset($user['id']) ? (int) $user['id'] : 0;
			$email = isset($user['email']) ? $user['email'] : '';
			$name = trim((string) ($user['name'] ?? ''));
			if ($name === '') {
				$name = $email;
			}
			$link = $user_id ? get_edit_user_link($user_id) : '';
			$item_html = '';
			if ($link) {
				$item_html .= '<a href="' . esc_url($link) . '">' . esc_html($name) . '</a>';
			} else {
				$item_html .= esc_html($name);
			}
			if ($email && $email !== $name) {
				$item_html .= ' <span class="description">(' . esc_html($email) . ')</span>';
			}
			$html .= '<li>' . $item_html . '</li>';
		}
		$html .= '</ul></div>';
		return $html;
	}

	/**
	 * Get settings.
	 */
	public static function get_settings(): array {
		$options = get_option(self::OPTION_KEY, []);
		$options = is_array($options) ? $options : [];
		$options = self::apply_runtime_addon_disable_overrides($options);
		// Send Email defaults on unless add-ons temporary disable is requested.
		if (!self::is_disable_addons_requested()) {
			$options['send_email_users_enabled'] = true;
		}
		return $options;
	}

	/**
	 * Get persisted settings without runtime temporary-disable overrides.
	 *
	 * This is used by admin UI screens that must reflect saved checkbox state
	 * even when runtime temporary-disable flags are currently active.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_raw_settings(): array {
		$options = get_option(self::OPTION_KEY, []);
		return is_array($options) ? $options : [];
	}

	/**
	 * Runtime add-on map used by docs and URL disable-override support.
	 *
	 * @param bool $translate_labels Whether to translate the label values.
	 * @return array<string,array{label:string,settings_keys:array<int,string>}>
	 */
	public static function get_addon_runtime_toggle_map(bool $translate_labels = true): array {
		$map = [
			'add-to-cart-bulk-import' => [
				'label' => 'Add to Cart Bulk Import',
				'settings_keys' => ['bulk_add_to_cart_enabled'],
			],
			'add-to-cart-variation-table' => [
				'label' => 'Add to Cart Variation Table',
				'settings_keys' => ['add_to_cart_variation_table_enabled'],
			],
			'add-to-cart-min-max-quantities' => [
				'label' => 'Add to Cart Min/Max Quantities',
				'settings_keys' => ['add_to_cart_min_max_quantities_enabled'],
			],
			'cart-price-per-piece' => [
				'label' => 'Cart Price Per-Piece',
				'settings_keys' => ['cart_price_per_piece_enabled'],
			],
			'cart-total-items' => [
				'label' => 'Cart Total Items',
				'settings_keys' => ['cart_total_items_enabled'],
			],
			'checkout-pre-defined-addresses' => [
				'label' => 'Checkout Address Selector',
				'settings_keys' => ['checkout_ship_to_predefined_enabled'],
			],
			'coupon-creator' => [
				'label' => 'Coupon Creator',
				'settings_keys' => ['bulk_coupons_enabled'],
			],
			'coupon-for-new-user' => [
				'label' => 'New User Coupons',
				'settings_keys' => ['nuc_enabled'],
			],
			'coupon-notifications-for-users-with-coupons' => [
				'label' => 'User Coupon Notifications',
				'settings_keys' => ['user_coupon_notifications_enabled'],
			],
			'coupon-remaining-balances' => [
				'label' => 'User Coupon Remaining Balances',
				'settings_keys' => ['coupon_remainder_enabled'],
			],
			'data-anonymizer' => [
				'label' => 'Data Anonymizer',
				'settings_keys' => ['data_anonymizer_enabled'],
			],
			'staging-development-environment-overrides' => [
				'label' => 'Staging & Development Environment Overrides',
				'settings_keys' => ['staging_dev_overrides_enabled'],
			],
			'bulk-page-creator' => [
				'label' => 'Page Creator',
				'settings_keys' => ['bulk_page_creator_enabled'],
			],
			'page-block-subpages-grid' => [
				'label' => 'Page Block: Tile Grid for Subpages',
				'settings_keys' => ['page_block_subpages_grid_enabled'],
			],
			'page-block-tabbed-content-area' => [
				'label' => 'Page Block: Tabs with Content from Other Pages',
				'settings_keys' => ['page_block_tabbed_content_area_enabled'],
			],
			'page-block-simple-icons' => [
				'label' => 'Page Block: Simple Icons',
				'settings_keys' => ['page_block_simple_icons_enabled'],
			],
			'page-block-menu-tiles' => [
				'label' => 'Page Block: Tile Grid for Menu',
				'settings_keys' => ['page_block_menu_tiles_enabled'],
			],
			'database-table-browser' => [
				'label' => 'Database Table Browser',
				'settings_keys' => ['database_table_browser_enabled'],
			],
			'emali-log' => [
				'label' => 'Email Log',
				'settings_keys' => ['emali_log_enabled'],
			],
			'security-hardening' => [
				'label' => 'Security Hardening',
				'settings_keys' => ['security_hardening_enabled'],
			],
			'seo-basics' => [
				'label' => 'SEO Basics',
				'settings_keys' => ['seo_basics_enabled'],
			],
			'restricted-access' => [
				'label' => 'Restricted Access',
				'settings_keys' => ['restricted_access_enabled'],
			],
			'fatal-error-debugger' => [
				'label' => 'Fatal Error Debugger',
				'settings_keys' => ['fatal_error_debugger_enabled'],
			],
			'my-account-coupon-screen' => [
				'label' => 'My Account Coupons Page',
				'settings_keys' => ['my_account_coupon_screen_enabled'],
			],
			'my-account-menu-tiles' => [
				'label' => 'My Account Menu Tiles',
				'settings_keys' => ['my_account_menu_tiles_enabled'],
			],
			'my-account-site-admin' => [
				'label' => 'My Account Admin',
				'settings_keys' => [
					'my_account_site_admin_enabled',
					'my_account_admin_order_viewer_enabled',
					'my_account_admin_product_viewer_enabled',
					'my_account_admin_coupon_viewer_enabled',
					'my_account_admin_user_viewer_enabled',
				],
			],
			'media-library-tags' => [
				'label' => 'Dynamic Photo Gallery with Media Library Tags',
				'settings_keys' => ['media_library_tags_enabled'],
			],
			'post-meta' => [
				'label' => 'Post Meta Viewer',
				'settings_keys' => ['display_post_meta_meta_box'],
			],
			'product-search-by-sku' => [
				'label' => 'Product Search by SKU',
				'settings_keys' => ['search_redirect_by_sku'],
			],
			'product-notification' => [
				'label' => 'Product Notification',
				'settings_keys' => ['product_notification_enabled'],
			],
			'post-content-generator' => [
				'label' => 'Post Content Generator',
				'settings_keys' => ['openai_content_generator_enabled'],
			],
			'post-idea-generator' => [
				'label' => 'Post Idea Generator',
				'settings_keys' => ['openai_blog_post_idea_generator_enabled'],
			],
			'plugin-tags-notes' => [
				'label' => 'Plugin Tags & Notes',
				'settings_keys' => ['plugin_tags_notes_enabled'],
			],
			'user-role-switching' => [
				'label' => 'User Role Switching',
				'settings_keys' => ['__role_switching_option_enabled'],
			],
			'wp-admin-bar-menu-items' => [
				'label' => 'WP-Admin Bar Menu Items',
				'settings_keys' => ['admin_bar_menu_items_enabled'],
			],
			'wp-admin-bar-quick-search' => [
				'label' => 'WP-Admin Bar Quick Search',
				'settings_keys' => ['um_quick_search_enabled'],
			],
			'wp-admin-css' => [
				'label' => 'WP-Admin CSS',
				'settings_keys' => ['wp_admin_css_enabled'],
			],
			'wp-admin-notifications' => [
				'label' => 'WP-Admin Notifications',
				'settings_keys' => ['custom_admin_notifications_enabled'],
			],
			'invoice-approval' => [
				'label' => 'Order Invoice & Approval',
				'settings_keys' => ['invoice_approval_enabled'],
			],
			'order-received-page-customizer' => [
				'label' => 'Order Received Page Customizer',
				'settings_keys' => ['order_received_page_customizer_enabled'],
			],
			'webhook-urls' => [
				'label' => 'Webhook URLs',
				'settings_keys' => ['webhook_urls_enabled'],
			],
			'send-sms-text' => [
				'label' => 'Send SMS Text',
				'settings_keys' => ['send_sms_text_enabled'],
			],
			'send-email-users' => [
				'label' => 'Send Email',
				'settings_keys' => ['send_email_users_enabled'],
			],
		];

		// Never trigger translations before init; this prevents WP 6.7+
		// "_load_textdomain_just_in_time called incorrectly" notices.
		if (!$translate_labels || !did_action('init')) {
			return $map;
		}

		foreach ($map as &$meta) {
			$meta['label'] = __((string) $meta['label'], 'user-manager');
		}
		unset($meta);

		return $map;
	}

	/**
	 * Load plugin translations.
	 */
	public static function load_textdomain(): void {
		load_plugin_textdomain(
			'user-manager',
			false,
			dirname(plugin_basename(dirname(__DIR__) . '/user-manager.php')) . '/languages'
		);
	}

	/**
	 * Add-on slugs currently disabled by URL/query or add-ons temporary-disable settings override.
	 *
	 * @return array<int,string>
	 */
	public static function get_temporarily_disabled_addons_from_url(): array {
		if (self::$runtime_disabled_addon_slugs !== null) {
			return self::$runtime_disabled_addon_slugs;
		}

		$map = self::get_addon_runtime_toggle_map(false);
		$disabled = [];
		$disable_all = self::is_disable_addons_requested();
		if ($disable_all) {
			$disabled = array_keys($map);
			self::$runtime_disabled_addon_slugs = $disabled;
			return $disabled;
		}

		$raw = isset($_GET[self::URL_PARAM_DISABLE_ADDONS]) ? sanitize_text_field(wp_unslash($_GET[self::URL_PARAM_DISABLE_ADDONS])) : '';
		if ($raw === '') {
			self::$runtime_disabled_addon_slugs = [];
			return [];
		}

		$parts = array_filter(array_map('trim', explode(',', $raw)));
		foreach ($parts as $slug) {
			$slug = sanitize_key(str_replace(' ', '-', $slug));
			if ($slug === '' || !isset($map[$slug])) {
				continue;
			}
			$disabled[] = $slug;
		}
		$disabled = array_values(array_unique($disabled));
		self::$runtime_disabled_addon_slugs = $disabled;
		return $disabled;
	}

	/**
	 * Determine if an add-on is temporarily disabled by URL/query or add-ons settings override.
	 */
	public static function is_addon_temporarily_disabled(string $addon_slug): bool {
		$addon_slug = sanitize_key($addon_slug);
		if ($addon_slug === '') {
			return false;
		}
		if ($addon_slug === 'send-email-users' && !self::is_disable_addons_requested()) {
			return false;
		}
		return in_array($addon_slug, self::get_temporarily_disabled_addons_from_url(), true);
	}

	/**
	 * Apply runtime add-on disable overrides to current settings.
	 *
	 * @param array<string,mixed> $settings Settings array from option.
	 * @return array<string,mixed>
	 */
	private static function apply_runtime_addon_disable_overrides(array $settings): array {
		$disabled = self::get_temporarily_disabled_addons_from_url();
		if (empty($disabled)) {
			return $settings;
		}

		$map = self::get_addon_runtime_toggle_map(false);
		$disable_all = self::is_disable_addons_requested();
		foreach ($disabled as $slug) {
			if (!isset($map[$slug]['settings_keys']) || !is_array($map[$slug]['settings_keys'])) {
				continue;
			}
			foreach ($map[$slug]['settings_keys'] as $settings_key) {
				$settings_key = (string) $settings_key;
				if ($settings_key === '') {
					continue;
				}
				if ($settings_key === '__role_switching_option_enabled') {
					// Role switching state lives in a dedicated option.
					// Runtime disable is enforced via is_addon_temporarily_disabled()
					// so we do not mutate persisted role-switch settings here.
					continue;
				}
				$settings[$settings_key] = false;
			}
		}

		return $settings;
	}

	/**
	 * Whether URL parameter requested "disable all add-ons" mode.
	 */
	private static function is_disable_all_addons_requested_from_url(): bool {
		if (self::$runtime_disable_all_addons !== null) {
			return self::$runtime_disable_all_addons;
		}

		$all_flag = isset($_GET[self::URL_PARAM_DISABLE_ALL_ADDONS]) ? sanitize_text_field(wp_unslash($_GET[self::URL_PARAM_DISABLE_ALL_ADDONS])) : '';
		$list_flag = isset($_GET[self::URL_PARAM_DISABLE_ADDONS]) ? sanitize_text_field(wp_unslash($_GET[self::URL_PARAM_DISABLE_ADDONS])) : '';
		$all_flag = strtolower(trim($all_flag));
		$list_flag = strtolower(trim($list_flag));
		$truthy = ['1', 'true', 'yes', 'on', 'all'];

		self::$runtime_disable_all_addons = in_array($all_flag, $truthy, true) || $list_flag === 'all';
		return self::$runtime_disable_all_addons;
	}

	/**
	 * Whether settings requested temporary disable for add-ons.
	 */
	private static function is_disable_addons_requested_from_settings(): bool {
		$options = get_option(self::OPTION_KEY, []);
		$options = is_array($options) ? $options : [];
		return !empty($options['temporarily_disable_addons']);
	}

	/**
	 * Whether settings requested temporary disable for blocks.
	 */
	public static function is_disable_blocks_requested_from_settings(): bool {
		$options = get_option(self::OPTION_KEY, []);
		$options = is_array($options) ? $options : [];
		return !empty($options['temporarily_disable_blocks']);
	}

	/**
	 * Whether add-ons should be temporarily disabled for this request.
	 */
	private static function is_disable_addons_requested(): bool {
		return self::is_disable_all_addons_requested_from_url() || self::is_disable_addons_requested_from_settings();
	}

	/**
	 * Whether blocks should be temporarily disabled for this request.
	 */
	private static function is_disable_blocks_requested(): bool {
		return self::is_disable_all_addons_requested_from_url() || self::is_disable_blocks_requested_from_settings();
	}

	/**
	 * Run Ship To Pre-Defined Addresses init after WooCommerce has loaded (so class_exists('WooCommerce') is true).
	 */
	public static function init_checkout_ship_to(): void {
		User_Manager_Checkout_Ship_To_Predefined::init();
	}

	/**
	 * Render Ship To Pre-Defined Addresses debug box on checkout when "Show debugging info for admins" is on.
	 * Called from wp_footer so it runs even when Ship To init() returned early (e.g. feature disabled or WC not loaded).
	 */
	public static function maybe_render_checkout_ship_to_debug(): void {
		if (!function_exists('is_checkout') || !is_checkout()) {
			return;
		}
		if (!current_user_can('manage_options')) {
			return;
		}
		if (self::is_addon_temporarily_disabled('checkout-pre-defined-addresses')) {
			return;
		}
		$settings = self::get_settings();
		if (empty($settings['checkout_ship_to_show_debug'])) {
			return;
		}
		if (!is_array($settings)) {
			$settings = [];
		}
		User_Manager_Checkout_Ship_To_Predefined::render_debug_box_standalone($settings);
	}

	/**
	 * Sync coupon notification settings into the external notification script.
	 */
	public static function sync_coupon_notification_settings(array $settings): void {
		// Settings are read directly from the database by the bundled notification script,
		// so no additional syncing is required.
	}

	/**
	 * Get email templates.
	 */
	public static function get_email_templates(): array {
		$templates = get_option(self::EMAIL_TEMPLATES_KEY, []);
		$auto_import_done = get_option('user_manager_auto_import_templates_done', false);

		// If no templates exist yet and auto-import hasn't run, seed them now.
		if ((empty($templates) || !is_array($templates)) && !$auto_import_done && current_user_can('manage_options') && class_exists('User_Manager_Actions')) {
			// Import demo templates and coupon template once.
			User_Manager_Actions::import_demo_templates();
			User_Manager_Actions::import_coupon_template();
			update_option('user_manager_auto_import_templates_done', true);

			// Reload after import.
			$templates = get_option(self::EMAIL_TEMPLATES_KEY, []);
		}

		if (!is_array($templates)) {
			return [];
		}
		
		// Ensure each template has an order; assign sequentially if missing.
		$needs_persist = false;
		$needs_template_migration = false;
		$order = 1;
		foreach ($templates as $id => &$tpl) {
			if (!is_array($tpl)) {
				$tpl = [];
			}
			if (self::normalize_legacy_remaining_balance_template($id, $tpl)) {
				$needs_template_migration = true;
			}
			if (!isset($tpl['order']) || !is_numeric($tpl['order'])) {
				$tpl['order'] = $order;
				$needs_persist = true;
			}
			$order++;
		}
		unset($tpl);
		
		// Sort by 'order' ascending while preserving keys
		uasort($templates, function($a, $b) {
			$oa = isset($a['order']) ? (int) $a['order'] : 0;
			$ob = isset($b['order']) ? (int) $b['order'] : 0;
			if ($oa === $ob) return 0;
			return ($oa < $ob) ? -1 : 1;
		});
		
		// Persist back if we had to assign order or migrate a legacy template preset body.
		if ($needs_persist || $needs_template_migration) {
			update_option(self::EMAIL_TEMPLATES_KEY, $templates);
		}
		
		return $templates;
	}

	/**
	 * Normalize legacy default remaining-balance email template content in-place.
	 *
	 * Only updates the template when it still matches the old shipped defaults,
	 * so customized templates are preserved.
	 *
	 * @param string              $template_id Template identifier.
	 * @param array<string,mixed> $template    Template payload (mutated by reference).
	 */
	private static function normalize_legacy_remaining_balance_template(string $template_id, array &$template): bool {
		if ($template_id !== 'tpl_auto_coupon_remaining_balance') {
			return false;
		}

		$updated = false;
		$legacy_description = 'Configured in Settings to trigger automated remaining balance coupon for new users. Supports %COUPONCODE%.';
		$new_description = 'Configured in Settings to trigger automated remaining balance coupon for new users. Supports %COUPONCODEVALUE% and %COUPONCODE%.';
		$legacy_body = '<p>Here is your remaining balance Coupon Code:<br>' . "\n" . '%COUPONCODE%</p>';
		$new_body = self::get_coupon_remainder_template_default_body();

		$normalize_newlines = static function (string $value): string {
			return str_replace(["\r\n", "\r"], "\n", trim($value));
		};

		$current_description = isset($template['description']) && is_string($template['description']) ? $template['description'] : '';
		if ($normalize_newlines($current_description) === $legacy_description) {
			$template['description'] = $new_description;
			$updated = true;
		}

		$current_body = isset($template['body']) && is_string($template['body']) ? $template['body'] : '';
		if ($normalize_newlines($current_body) === '' || $normalize_newlines($current_body) === $normalize_newlines($legacy_body)) {
			$template['body'] = $new_body;
			$updated = true;
		}

		return $updated;
	}

	/**
	 * Default body for the remaining-balance coupon email template.
	 */
	private static function get_coupon_remainder_template_default_body(): string {
		return '<p>Remaining Balance:<br>' . "\n" .
			'%COUPONCODEVALUE%</p>' . "\n\n" .
			'<p>Coupon Code:<br>' . "\n" .
			'%COUPONCODE%</p>';
	}

	/**
	 * Get SMS text templates.
	 */
	public static function get_sms_text_templates(): array {
		$templates = get_option(self::SMS_TEXT_TEMPLATES_KEY, []);
		if (!is_array($templates)) {
			return [];
		}

		$needs_persist = false;
		$order = 1;
		foreach ($templates as $id => &$tpl) {
			if (!is_array($tpl)) {
				$tpl = [];
			}
			if (!isset($tpl['order']) || !is_numeric($tpl['order'])) {
				$tpl['order'] = $order;
				$needs_persist = true;
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

		if ($needs_persist) {
			update_option(self::SMS_TEXT_TEMPLATES_KEY, $templates);
		}

		return $templates;
	}

	/**
	 * Convert timestamps to human readable relative strings.
	 *
	 * Accepts Unix timestamps, MySQL datetime strings, or DateTime instances.
	 */
	public static function nice_time(int|string|\DateTimeInterface $timestamp): string {
		if ($timestamp instanceof \DateTimeInterface) {
			$timestamp = $timestamp->getTimestamp();
		} elseif (is_string($timestamp)) {
			if (is_numeric($timestamp)) {
				$timestamp = (int) $timestamp;
			} else {
				try {
					$timezone = function_exists('wp_timezone') ? wp_timezone() : new \DateTimeZone('UTC');
					$timestamp = (new \DateTimeImmutable($timestamp, $timezone))->getTimestamp();
				} catch (\Exception $e) {
					$timestamp = strtotime($timestamp);
				}
			}
		}
		
		if (!is_int($timestamp)) {
			return (string) $timestamp;
		}
		
		$diff = current_time('timestamp') - $timestamp;
		
		if ($diff < 60) {
			return __('just now', 'user-manager');
		} elseif ($diff < 3600) {
			$mins = floor($diff / 60);
			return sprintf(_n('%d minute ago', '%d minutes ago', $mins, 'user-manager'), $mins);
		} elseif ($diff < 86400) {
			$hours = floor($diff / 3600);
			return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'user-manager'), $hours);
		} elseif ($diff < 604800) {
			$days = floor($diff / 86400);
			return sprintf(_n('%d day ago', '%d days ago', $days, 'user-manager'), $days);
		} elseif ($diff < 2592000) {
			$weeks = floor($diff / 604800);
			return sprintf(_n('%d week ago', '%d weeks ago', $weeks, 'user-manager'), $weeks);
		} else {
			return date_i18n(get_option('date_format'), $timestamp);
		}
	}

	/**
	 * Get available user roles.
	 */
	public static function get_user_roles(): array {
		global $wp_roles;
		
		$roles = [];
		foreach ($wp_roles->roles as $key => $role) {
			$roles[$key] = $role['name'];
		}
		
		return $roles;
	}

	/**
	 * Get imported files list.
	 */
	public static function get_imported_files(): array {
		$files = get_option(self::IMPORTED_FILES_KEY, []);
		return is_array($files) ? $files : [];
	}

	/**
	 * Mark a file as imported.
	 */
	public static function mark_file_imported(string $filepath, string $activity_log_id): void {
		$files = self::get_imported_files();
		$files[$filepath] = [
			'imported_at' => current_time('timestamp'),
			'imported_by' => get_current_user_id(),
			'activity_log_id' => $activity_log_id,
		];
		update_option(self::IMPORTED_FILES_KEY, $files);
	}

	/**
	 * Get SFTP directories from settings.
	 */
	public static function get_sftp_directories(): array {
		$settings = self::get_settings();
		$dirs_text = $settings['sftp_directories'] ?? '';
		if (empty($dirs_text)) {
			return [];
		}
		$dirs = array_filter(array_map('trim', explode("\n", $dirs_text)));
		return $dirs;
	}

	/**
	 * Get CSV files from configured directories.
	 */
	public static function get_sftp_csv_files(): array {
		$directories = self::get_sftp_directories();
		$imported_files = self::get_imported_files();
		$files = [];

		foreach ($directories as $dir) {
			if (!is_dir($dir) || !is_readable($dir)) {
				continue;
			}
			
			$csv_files = glob(rtrim($dir, '/') . '/*.csv');
			if (!$csv_files) {
				continue;
			}

			foreach ($csv_files as $filepath) {
				$import_info = $imported_files[$filepath] ?? null;
				$files[] = [
					'path' => $filepath,
					'name' => basename($filepath),
					'directory' => $dir,
					'size' => filesize($filepath),
					'modified' => filemtime($filepath),
					'imported' => $import_info !== null,
					'imported_at' => $import_info['imported_at'] ?? null,
					'imported_by' => $import_info['imported_by'] ?? null,
					'activity_log_id' => $import_info['activity_log_id'] ?? null,
				];
			}
		}

		// Sort by modified date, newest first
		usort($files, function($a, $b) {
			return $b['modified'] - $a['modified'];
		});

		return $files;
	}

	/**
	 * AJAX handler for lazy datalist options.
	 */
	public static function ajax_get_datalist_options(): void {
		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Unauthorized', 'user-manager')], 403);
		}
		
		check_ajax_referer('user_manager_datalist_options', 'nonce');
		
		$source = isset($_GET['source']) ? sanitize_key(wp_unslash($_GET['source'])) : '';
		if ($source === 'coupon_codes') {
			wp_send_json_success([
				'options' => self::get_coupon_codes_for_datalist(),
			]);
		}
		
		wp_send_json_error(['message' => __('Unsupported datalist source.', 'user-manager')], 400);
	}

	/**
	 * AJAX handler for Login As user search (username/email).
	 */
	public static function ajax_search_users_for_login_as(): void {
		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Unauthorized', 'user-manager')], 403);
		}

		check_ajax_referer('user_manager_login_as_search', 'nonce');

		$query = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
		$query = trim($query);
		if (strlen($query) < 2) {
			wp_send_json_success(['results' => []]);
		}

		$user_query = new WP_User_Query([
			'number'         => 20,
			'search'         => '*' . $query . '*',
			'search_columns' => ['user_login', 'user_email'],
			'orderby'        => 'user_login',
			'order'          => 'ASC',
			'fields'         => ['ID', 'user_login', 'user_email'],
		]);

		$results = [];
		$seen    = [];
		foreach ($user_query->get_results() as $user) {
			if (!is_object($user)) {
				continue;
			}

			// WP_User_Query may return WP_User objects or lightweight row objects
			// depending on the "fields" argument. Support both formats.
			$user_id = isset($user->ID) ? (int) $user->ID : 0;
			if ($user_id <= 0 || isset($seen[$user_id])) {
				continue;
			}
			$seen[$user_id] = true;

			$user_login = isset($user->user_login) ? (string) $user->user_login : '';
			$user_email = isset($user->user_email) ? (string) $user->user_email : '';
			if ($user_login === '' && $user_email === '') {
				continue;
			}

			$label = $user_login !== '' ? $user_login : $user_email;
			if ($user_email !== '') {
				$label .= ' (' . $user_email . ')';
			}

			$results[] = [
				'id'    => $user_id,
				'label' => $label,
				'login' => $user_login,
				'email' => $user_email,
			];
		}

		wp_send_json_success(['results' => $results]);
	}
	
	/**
	 * Get coupon codes for lazy datalist fields.
	 *
	 * @return array<int,string>
	 */
	private static function get_coupon_codes_for_datalist(): array {
		if (!post_type_exists('shop_coupon')) {
			return [];
		}
		
		$coupon_ids = get_posts([
			'post_type'   => 'shop_coupon',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
			'fields'      => 'ids',
		]);
		
		if (empty($coupon_ids) || !is_array($coupon_ids)) {
			return [];
		}
		
		$codes = [];
		foreach ($coupon_ids as $coupon_id) {
			$code = get_the_title($coupon_id);
			if (is_string($code) && $code !== '') {
				$codes[] = $code;
			}
		}
		
		return array_values(array_unique($codes));
	}
	
	/**
	 * AJAX handler for email preview.
	 */
	public static function ajax_email_preview(): void {
		if (!current_user_can('manage_options')) {
			wp_die('Unauthorized');
		}

		check_ajax_referer('user_manager_email_preview', 'nonce');

		$template_id = isset($_GET['template_id']) ? sanitize_key($_GET['template_id']) : '';
		$email       = isset($_GET['email']) ? sanitize_email($_GET['email']) : 'user@example.com';
		$username    = isset($_GET['username']) ? sanitize_text_field($_GET['username']) : $email;
		$first_name  = isset($_GET['first_name']) ? sanitize_text_field($_GET['first_name']) : '';
		$last_name   = isset($_GET['last_name']) ? sanitize_text_field($_GET['last_name']) : '';
		$login_url   = isset($_GET['login_url']) ? sanitize_text_field($_GET['login_url']) : '/my-account/';
		$coupon_code = isset($_GET['coupon_code']) ? sanitize_text_field($_GET['coupon_code']) : 'SAMPLECOUPON123';
		$coupon_code_value = isset($_GET['coupon_code_value']) ? sanitize_text_field($_GET['coupon_code_value']) : '';
		if ($coupon_code_value === '') {
			$coupon_code_value = self::format_coupon_code_value_for_placeholder($coupon_code);
		}

		// Get template (supports custom "__um_default__" coupon template token).
		$template = null;
		if ($template_id === '__um_default__') {
			$template = self::resolve_coupon_email_template('__um_default__');
		} elseif (!empty($template_id)) {
			$templates = self::get_email_templates();
			if (isset($templates[$template_id])) {
				$template = $templates[$template_id];
			}
		}

		// Use default template if none specified
		if (!$template) {
			$template = [
				'subject' => 'Your Login Information',
				'heading' => 'Your Username and Password',
				'body' => '<p><strong>Login Page:</strong><br>
<a href="%SITEURL%%LOGINURL%">%SITEURL%%LOGINURL%</a></p>

<p><strong>Username:</strong><br>
%USERNAME%</p>

<p><strong>Password:</strong><br>
%PASSWORD%</p>',
			];
		}

		// Replace placeholders
		$password = '••••••••••••';
		$password_reset_url = home_url('/my-account/lost-password/');
		$replacements = [
			'%SITEURL%'         => home_url(),
			'%LOGINURL%'        => $login_url,
			'%USERNAME%'        => $username,
			'%PASSWORD%'        => $password,
			'%EMAIL%'           => $email,
			'%FIRSTNAME%'       => $first_name,
			'%LASTNAME%'        => $last_name,
			'%PASSWORDRESETURL%' => $password_reset_url,
			'%COUPONCODE%'      => $coupon_code,
			'%COUPONCODEVALUE%' => $coupon_code_value,
			'[coupon_code]'     => $coupon_code,
		];

		$heading = str_replace(array_keys($replacements), array_values($replacements), $template['heading']);
		$body = str_replace(array_keys($replacements), array_values($replacements), $template['body']);

		// Generate email HTML using WooCommerce template
		$email_html = User_Manager_Email::get_preview_html($body, $heading);

		// Output the email HTML
		header('Content-Type: text/html; charset=UTF-8');
		echo $email_html;
		exit;
	}

	/**
	 * AJAX handler for getting import log details.
	 */
	public static function ajax_get_import_log(): void {
		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Unauthorized', 'user-manager')]);
		}

		check_ajax_referer('user_manager_get_import_log', '_wpnonce');

		$import_id = isset($_GET['import_id']) ? sanitize_key($_GET['import_id']) : '';
		
		if (empty($import_id)) {
			wp_send_json_error(['message' => __('Invalid import ID', 'user-manager')]);
		}

		$log_data = get_transient('um_import_log_' . $import_id);
		
		if (!$log_data) {
			wp_send_json_error(['message' => __('Import log not found or has expired.', 'user-manager')]);
		}

		// Build HTML output
		ob_start();
		?>
		<div class="um-import-summary">
			<h4><?php esc_html_e('Import Summary', 'user-manager'); ?></h4>
			<table class="widefat" style="margin-bottom: 20px;">
				<tr>
					<td><strong><?php esc_html_e('File:', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($log_data['filename']); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e('Imported:', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $log_data['imported_at'])); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e('Total Rows:', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($log_data['total']); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e('Created:', 'user-manager'); ?></strong></td>
					<td><span class="um-status-badge um-status-success"><?php echo esc_html($log_data['created']); ?></span></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e('Updated:', 'user-manager'); ?></strong></td>
					<td><span class="um-status-badge um-status-warning"><?php echo esc_html($log_data['updated']); ?></span></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e('Skipped:', 'user-manager'); ?></strong></td>
					<td><span class="um-status-badge um-status-info"><?php echo esc_html($log_data['skipped']); ?></span></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e('Failed:', 'user-manager'); ?></strong></td>
					<td><span class="um-status-badge um-status-error"><?php echo esc_html($log_data['failed']); ?></span></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e('Emails Sent:', 'user-manager'); ?></strong></td>
					<td><?php echo esc_html($log_data['emails_sent']); ?></td>
				</tr>
			</table>
			
			<h4><?php esc_html_e('Detailed Log', 'user-manager'); ?></h4>
			<div>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e('Row', 'user-manager'); ?></th>
							<th><?php esc_html_e('Email', 'user-manager'); ?></th>
							<th><?php esc_html_e('Status', 'user-manager'); ?></th>
							<th><?php esc_html_e('Message', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($log_data['log'] as $entry) : ?>
							<tr>
								<td><?php echo esc_html($entry['row']); ?></td>
								<td>
									<?php if (!empty($entry['user_id'])) : ?>
										<a href="<?php echo esc_url(get_edit_user_link($entry['user_id'])); ?>"><?php echo esc_html($entry['email'] ?? '—'); ?></a>
									<?php else : ?>
										<?php echo esc_html($entry['email'] ?? '—'); ?>
									<?php endif; ?>
								</td>
								<td>
									<?php
									$status_class = 'um-status-info';
									switch ($entry['status']) {
										case 'created':
											$status_class = 'um-status-success';
											break;
										case 'updated':
											$status_class = 'um-status-warning';
											break;
										case 'failed':
											$status_class = 'um-status-error';
											break;
										case 'skipped':
											$status_class = 'um-status-info';
											break;
									}
									?>
									<span class="um-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($entry['status'])); ?></span>
								</td>
								<td><?php echo esc_html($entry['message']); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		wp_send_json_success(['html' => $html]);
	}

	/**
	 * AJAX handler for getting activity details.
	 */
/**
	 * Flatten nested activity log details into key/value rows.
	 *
	 * @param mixed  $value Raw data value.
	 * @param string $path  Current field path.
	 * @return array<int, array{path: string, value: mixed}>
	 */
/**
	 * Normalize scalar values for display in Activity Details modal.
	 *
	 * @param mixed $value Scalar-ish value.
	 */
/**
	 * Detect sensitive detail keys that should be masked in UI.
	 */
/**
	 * Woo: Replace lost password message with friendlier "set new password" copy.
	 */
	public static function filter_lost_password_message($message) {
		return __('Please enter your username or email address. You will receive a link to create a new password via email.', 'user-manager');
	}
	
	/**
	 * Create user activity table if it doesn't exist.
	 */
	public static function maybe_create_user_activity_table(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'um_user_activity';
		$exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			action varchar(100) NOT NULL DEFAULT '',
			url text,
			ip_address varchar(100) NOT NULL DEFAULT '',
			user_agent text,
			roles varchar(500) NOT NULL DEFAULT '',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY action (action),
			KEY created_at (created_at)
		) {$charset_collate};";
		dbDelta($sql);
		// Add roles column to existing tables that were created before this column existed.
		if ($exists === $table) {
			$column = $wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s", 'roles'));
			if (empty($column)) {
				$wpdb->query("ALTER TABLE {$table} ADD COLUMN roles varchar(500) NOT NULL DEFAULT '' AFTER user_agent");
			}
		}
	}

	/**
	 * Create admin activity log table if it doesn't exist.
	 */
	public static function maybe_create_admin_activity_table(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'um_admin_activity';
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			action varchar(191) NOT NULL DEFAULT '',
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			tool varchar(191) NOT NULL DEFAULT '',
			extra longtext,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY action (action),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) {$charset_collate};";
		dbDelta($sql);
		
		$legacy = get_option(self::ACTIVITY_LOG_KEY, []);
		if (!empty($legacy) && is_array($legacy)) {
			foreach ($legacy as $entry) {
				$wpdb->insert(
					$table,
					[
						'action' => sanitize_text_field($entry['action'] ?? ''),
						'user_id' => isset($entry['user_id']) ? (int) $entry['user_id'] : 0,
						'tool' => sanitize_text_field($entry['tool'] ?? ''),
						'extra' => wp_json_encode($entry['extra'] ?? []),
						'created_by' => isset($entry['created_by']) ? (int) $entry['created_by'] : 0,
						'created_at' => !empty($entry['created_at']) ? gmdate('Y-m-d H:i:s', (int) $entry['created_at']) : current_time('mysql'),
					],
					['%s','%d','%s','%s','%d','%s']
				);
			}
			delete_option(self::ACTIVITY_LOG_KEY);
		}
	}
	
	/**
	 * Insert a user activity record.
	 */
	private static function add_user_activity(int $user_id, string $action, string $url = ''): void {
		if ($user_id <= 0 || empty($action)) {
			return;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'um_user_activity';
		$now_mysql = current_time('mysql');

		// Capture user's roles at the time this record is added (not when the page loads).
		$roles_snapshot = '';
		$user = get_userdata($user_id);
		if ($user && !empty($user->roles) && is_array($user->roles)) {
			$roles_snapshot = implode(', ', $user->roles);
		}
		
		// Resolve URL if not provided
		if (empty($url)) {
			$scheme = (is_ssl() || (isset($_SERVER['HTTPS']) && 'on' === strtolower((string) $_SERVER['HTTPS']))) ? 'https://' : 'http://';
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : parse_url(home_url(), PHP_URL_HOST);
			$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
			$url = $scheme . $host . $uri;
		}
		$url = esc_url_raw($url);
		
		$wpdb->insert(
			$table,
			[
				'user_id' => $user_id,
				'action' => $action,
				'url' => $url,
				'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
				'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
				'roles' => $roles_snapshot,
				'created_at' => $now_mysql,
			],
			['%d','%s','%s','%s','%s','%s','%s']
		);
	}
	
	/**
	 * Log My Account page views (all endpoints) as activity.
	 */
	public static function maybe_log_myaccount_page(): void {
		if (!is_user_logged_in()) {
			return;
		}
		if (!function_exists('is_account_page')) {
			return;
		}
		if (!is_account_page()) {
			return;
		}
		$endpoint_label = 'Dashboard';
		$current_url = '';
		
		// Detect WooCommerce endpoint when available
		if (function_exists('is_wc_endpoint_url')) {
			$known_endpoints = [
				'dashboard' => 'Dashboard',
				'orders' => 'Orders',
				'view-order' => 'View Order',
				'downloads' => 'Downloads',
				'edit-address' => 'Addresses',
				'edit-account' => 'Edit Account',
				'payment-methods' => 'Payment Methods',
				'add-payment-method' => 'Add Payment Method',
				'lost-password' => 'Lost Password',
				'customer-logout' => 'Logout',
				'subscriptions' => 'Subscriptions',
			];
			
			$matched = false;
			foreach ($known_endpoints as $ep => $label) {
				if (is_wc_endpoint_url($ep)) {
					$endpoint_label = $label;
					$matched = true;
					break;
				}
			}
			
			if (!$matched) {
				// Base /my-account/ with no endpoint is typically dashboard
				$endpoint_label = 'Dashboard';
			}
		}
		
		// Build current URL
		$scheme = (is_ssl() || (isset($_SERVER['HTTPS']) && 'on' === strtolower((string) $_SERVER['HTTPS']))) ? 'https://' : 'http://';
		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : parse_url(home_url(), PHP_URL_HOST);
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		$current_url = $scheme . $host . $uri;
		
		self::add_user_activity(get_current_user_id(), 'View My Account: ' . $endpoint_label, $current_url);
	}
	
	/**
	 * Log password change from WooCommerce "Edit Account" form.
	 */
	public static function log_password_change_on_save(int $user_id): void {
		// Woo form uses password_1 when changing password
		$pw1 = isset($_POST['password_1']) ? (string) wp_unslash($_POST['password_1']) : '';
		if (!empty($pw1)) {
			$scheme = (is_ssl() || (isset($_SERVER['HTTPS']) && 'on' === strtolower((string) $_SERVER['HTTPS']))) ? 'https://' : 'http://';
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : parse_url(home_url(), PHP_URL_HOST);
			$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
			$current_url = $scheme . $host . $uri;
			self::add_user_activity((int) $user_id, 'Changed Password', $current_url);
		}
	}
	
	/**
	 * Log password reset via core flow.
	 */
	public static function log_password_change_after_reset($user, $new_pass): void {
		if ($user instanceof WP_User) {
			$scheme = (is_ssl() || (isset($_SERVER['HTTPS']) && 'on' === strtolower((string) $_SERVER['HTTPS']))) ? 'https://' : 'http://';
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : parse_url(home_url(), PHP_URL_HOST);
			$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
			$current_url = $scheme . $host . $uri;
			self::add_user_activity((int) $user->ID, 'Changed Password', $current_url);
		}
	}

	/**
	 * Log WooCommerce order activity into user activity table.
	 *
	 * Fired when an order moves into processing or completed status.
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	public static function log_user_order_activity($order_id): void {
		$order_id = (int) $order_id;
		if ($order_id <= 0) {
			return;
		}
		if (!function_exists('wc_get_order')) {
			return;
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}

		$user_id = (int) $order->get_user_id();
		if ($user_id <= 0) {
			// Skip guest checkouts; User Activity focuses on registered users.
			return;
		}

		// Build a front-end URL if available; fall back to order key URL if needed.
		$url = '';
		if (function_exists('wc_get_endpoint_url')) {
			$myaccount_page_id = get_option('woocommerce_myaccount_page_id');
			$myaccount_url     = $myaccount_page_id ? get_permalink($myaccount_page_id) : wc_get_page_permalink('myaccount');
			if ($myaccount_url) {
				$url = wc_get_endpoint_url('view-order', $order_id, $myaccount_url);
			}
		}
		if (empty($url)) {
			$url = $order->get_view_order_url();
		}

		$status = $order->get_status();
		$label  = sprintf(
			/* translators: 1: order number, 2: status */
			__('Placed Order #%1$s (%2$s)', 'user-manager'),
			$order->get_order_number(),
			$status
		);

		self::add_user_activity($user_id, $label, $url);
	}
	/**
	 * Print inline JS on the lost password page to rebrand UI strings and layout.
	 * Uses client-side URL guard for reliability across themes.
	 */
	public static function print_lost_password_rebrand_script(): void {
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			if (!window.location.href.includes('/my-account/lost-password/')) {
				if (window.console && console.debug) console.debug('UM Rebrand: script loaded, not lost-password page');
				return;
			}
			if (window.console && console.debug) console.debug('UM Rebrand: rebranding lost-password UI…');
			var notice = document.querySelector('.woocommerce-info, .woocommerce-message');
			if (notice) {
				var text = (notice.textContent || '').trim();
				var remove = 'Lost your password?';
				if (text && text.indexOf(remove) === 0) {
					notice.textContent = text.replace(remove, '').trim();
					if (window.console && console.debug) console.debug('UM Rebrand: removed leading phrase');
				}
			}
			var btn = document.querySelector('form.lost_reset_password button[type="submit"]')
				|| document.querySelector('form.woocommerce-ResetPassword button[type="submit"]')
				|| document.querySelector('.woocommerce form.lost_reset_password button.woocommerce-Button');
			if (btn) {
				btn.textContent = 'Set new password';
				if (window.console && console.debug) console.debug('UM Rebrand: updated submit button text');
			}
			var wcWrap = document.querySelector('.woocommerce-account .woocommerce');
			if (wcWrap) {
				wcWrap.style.justifyContent = 'unset';
				if (window.console && console.debug) console.debug('UM Rebrand: unset justifyContent on wrapper');
			}
			window.umRebrandTest = { active: true, page: 'lost-password' };
		});
		</script>
		<?php
	}
	
	/**
	 * Remove greeting line ("Hi username,") from password changed email.
	 *
	 * @param array   $email
	 * @param WP_User $user
	 * @param array   $userdata
	 * @return array
	 */
	public static function filter_password_change_email($email, $user, $userdata) {
		if (empty($email['message'])) {
			return $email;
		}
		$lines = explode("\n", (string) $email['message']);
		if (isset($lines[0]) && stripos($lines[0], 'hi ') === 0) {
			array_shift($lines);
		}
		if (isset($lines[0]) && trim($lines[0]) === '') {
			array_shift($lines);
		}
		$email['message'] = implode("\n", $lines);
		return $email;
	}

	/**
	 * Determine whether a user has been deactivated.
	 */
	public static function is_user_deactivated(int $user_id): bool {
		if ($user_id <= 0) {
			return false;
		}
		return !empty(get_user_meta($user_id, self::USER_DEACTIVATED_META_KEY, true));
	}

	/**
	 * Get deactivation/reactivation history entries.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_deactivated_users_history(): array {
		$history = get_option(self::USER_DEACTIVATION_HISTORY_KEY, []);
		if (!is_array($history)) {
			return [];
		}

		$normalized = [];
		foreach ($history as $entry) {
			if (!is_array($entry)) {
				continue;
			}

			$action = isset($entry['action']) ? sanitize_key((string) $entry['action']) : '';
			if (!in_array($action, ['deactivated', 'reactivated'], true)) {
				$action = 'deactivated';
			}

			$normalized[] = [
				'id' => isset($entry['id']) ? sanitize_text_field((string) $entry['id']) : '',
				'action' => $action,
				'user_id' => isset($entry['user_id']) ? absint($entry['user_id']) : 0,
				'user_login' => isset($entry['user_login']) ? sanitize_user((string) $entry['user_login'], true) : '',
				'user_email' => isset($entry['user_email']) ? sanitize_text_field((string) $entry['user_email']) : '',
				'result_login' => isset($entry['result_login']) ? sanitize_user((string) $entry['result_login'], true) : '',
				'result_email' => isset($entry['result_email']) ? sanitize_text_field((string) $entry['result_email']) : '',
				'attempted_identifier' => isset($entry['attempted_identifier']) ? sanitize_text_field((string) $entry['attempted_identifier']) : '',
				'performed_by' => isset($entry['performed_by']) ? absint($entry['performed_by']) : 0,
				'performed_at' => isset($entry['performed_at']) ? sanitize_text_field((string) $entry['performed_at']) : '',
			];
		}

		return $normalized;
	}

	/**
	 * Add one deactivation/reactivation history entry.
	 *
	 * @param array<string,mixed> $entry History entry data.
	 */
	public static function add_deactivated_users_history_entry(array $entry): void {
		$history = self::get_deactivated_users_history();
		$action = isset($entry['action']) ? sanitize_key((string) $entry['action']) : '';
		if (!in_array($action, ['deactivated', 'reactivated'], true)) {
			$action = 'deactivated';
		}

		$history_entry = [
			'id' => isset($entry['id'])
				? sanitize_text_field((string) $entry['id'])
				: (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('um-deactivate-history-', true)),
			'action' => $action,
			'user_id' => isset($entry['user_id']) ? absint($entry['user_id']) : 0,
			'user_login' => isset($entry['user_login']) ? sanitize_user((string) $entry['user_login'], true) : '',
			'user_email' => isset($entry['user_email']) ? sanitize_text_field((string) $entry['user_email']) : '',
			'result_login' => isset($entry['result_login']) ? sanitize_user((string) $entry['result_login'], true) : '',
			'result_email' => isset($entry['result_email']) ? sanitize_text_field((string) $entry['result_email']) : '',
			'attempted_identifier' => isset($entry['attempted_identifier']) ? sanitize_text_field((string) $entry['attempted_identifier']) : '',
			'performed_by' => isset($entry['performed_by']) ? absint($entry['performed_by']) : 0,
			'performed_at' => isset($entry['performed_at']) && (string) $entry['performed_at'] !== ''
				? sanitize_text_field((string) $entry['performed_at'])
				: current_time('mysql'),
		];

		array_unshift($history, $history_entry);
		update_option(self::USER_DEACTIVATION_HISTORY_KEY, $history);
	}

	/**
	 * Block authentication for deactivated users.
	 *
	 * @param WP_User|WP_Error $user
	 * @param string           $password
	 * @return WP_User|WP_Error
	 */
	public static function block_deactivated_user_authentication($user, $password) {
		if (is_wp_error($user) || !($user instanceof WP_User)) {
			return $user;
		}
		if (!self::is_user_deactivated((int) $user->ID)) {
			return $user;
		}
		return new WP_Error(
			'um_user_deactivated',
			__('This account has been deactivated. Please contact the site administrator.', 'user-manager')
		);
	}

	/**
	 * Prevent deactivated users from requesting password resets.
	 *
	 * @param bool $allow
	 * @param int  $user_id
	 */
	public static function maybe_block_deactivated_user_password_reset($allow, $user_id): bool {
		$user_id = absint($user_id);
		if (!$allow || $user_id <= 0) {
			return (bool) $allow;
		}
		if (self::is_user_deactivated($user_id)) {
			return false;
		}
		return true;
	}

	/**
	 * Create login history table if it doesn't exist.
	 */
	public static function maybe_create_login_history_table(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'um_login_history';
		$exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
		if ($exists === $table) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				username varchar(60) NOT NULL DEFAULT '',
				email varchar(100) NOT NULL DEFAULT '',
				ip_address varchar(100) NOT NULL DEFAULT '',
				user_agent text,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY created_at (created_at)
			) {$charset_collate};";
		dbDelta($sql);
	}
	
	/**
	 * Handle wp_login to record login history and update last_login meta.
	 * Also checks for role changes (vs. last User Activity record) and sends admin alert when configured.
	 *
	 * @param string  $user_login
	 * @param WP_User $user
	 */
	public static function handle_wp_login($user_login, $user): void {
		if (!$user || !isset($user->ID)) {
			return;
		}
		global $wpdb;
		// Per-site table only
		$table = $wpdb->prefix . 'um_login_history';
		$now_mysql = current_time('mysql');
		
		// Insert login history row (safe even if table missing; insert will fail silently or log in debug)
		$wpdb->insert(
			$table,
			[
				'user_id' => (int) $user->ID,
				'username' => (string) $user->user_login,
				'email' => (string) $user->user_email,
				'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
				'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
				'created_at' => $now_mysql,
			],
			['%d','%s','%s','%s','%s','%s']
		);
		
		// Update last_login meta
		update_user_meta($user->ID, 'last_login', $now_mysql);
		update_user_meta($user->ID, 'last_login_unix', time());

		// Role change alert: compare current roles to last User Activity record; send email if a monitored role changed.
		self::maybe_send_role_change_alert_on_login($user);

		// Record this login in um_user_activity (so next login has a "previous" roles snapshot).
		$login_url = '';
		if (isset($_SERVER['REQUEST_URI'])) {
			$scheme = (is_ssl() || (isset($_SERVER['HTTPS']) && 'on' === strtolower((string) $_SERVER['HTTPS']))) ? 'https://' : 'http://';
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : parse_url(home_url(), PHP_URL_HOST);
			$login_url = $scheme . $host . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
		}
		if (empty($login_url)) {
			$login_url = home_url('/wp-login.php');
		}
		self::add_user_activity((int) $user->ID, 'Login', esc_url_raw($login_url));
	}

	/**
	 * If role change alert is enabled, get last roles from um_user_activity and compare to current.
	 * If the user's previous roles included a monitored role and roles have changed, send one email to the configured admin.
	 *
	 * @param WP_User $user User after login (current roles may already be updated by SSO etc.).
	 */
	private static function maybe_send_role_change_alert_on_login(WP_User $user): void {
		$settings = self::get_settings();
		if (empty($settings['role_change_alert_enabled']) || empty($settings['role_change_alert_email']) || !is_email($settings['role_change_alert_email'])) {
			return;
		}
		$monitored = isset($settings['role_change_alert_roles']) && is_array($settings['role_change_alert_roles']) ? $settings['role_change_alert_roles'] : [];
		if (empty($monitored)) {
			return;
		}
		global $wpdb;
		$activity_table = $wpdb->prefix . 'um_user_activity';
		$table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $activity_table)) === $activity_table;
		if (!$table_exists) {
			return;
		}
		$last_row = $wpdb->get_row($wpdb->prepare(
			"SELECT roles FROM {$activity_table} WHERE user_id = %d ORDER BY created_at DESC LIMIT 1",
			$user->ID
		), ARRAY_A);
		$old_roles_str = isset($last_row['roles']) ? trim((string) $last_row['roles']) : '';
		$old_roles = [];
		if ($old_roles_str !== '') {
			$old_roles = array_unique(array_map('trim', explode(',', $old_roles_str)));
			$old_roles = array_values(array_filter($old_roles));
		}
		$new_roles = !empty($user->roles) && is_array($user->roles) ? $user->roles : [];
		sort($old_roles);
		sort($new_roles);
		if ($old_roles === $new_roles) {
			return;
		}
		$had_monitored = false;
		foreach ($old_roles as $r) {
			if (in_array($r, $monitored, true)) {
				$had_monitored = true;
				break;
			}
		}
		if (!$had_monitored) {
			return;
		}
		$old_role_display = $old_roles_str !== '' ? $old_roles_str : __('(none)', 'user-manager');
		$new_role_display = !empty($new_roles) ? implode(', ', $new_roles) : __('(none)', 'user-manager');
		$subject = sprintf(
			/* translators: %s: user email address */
			__('User Role Change Alert: %s', 'user-manager'),
			$user->user_email
		);
		$message = sprintf(
			/* translators: 1: user email, 2: previous roles, 3: new roles */
			__('The following user %1$s role was just changed from %2$s to %3$s.', 'user-manager'),
			$user->user_email,
			$old_role_display,
			$new_role_display
		);
		wp_mail($settings['role_change_alert_email'], $subject, $message);
	}
	
	/**
	 * Log wp-admin login to activity log.
	 * Called on admin_init to detect when user accesses wp-admin after login.
	 */
	public static function maybe_log_admin_login(): void {
		// Check if admin activity logging is enabled (defaults to true)
		$settings = self::get_settings();
		$log_admin_activity = isset($settings['log_admin_activity']) ? !empty($settings['log_admin_activity']) : true;
		
		if (!$log_admin_activity) {
			return;
		}
		
		// Only log once per session
		static $logged = false;
		if ($logged) {
			return;
		}
		
		// Only log if user is logged in and accessing admin
		if (!is_user_logged_in() || !is_admin()) {
			return;
		}
		
		$user_id = get_current_user_id();
		if (!$user_id) {
			return;
		}
		
		// Check if we've already logged this session
		$session_key = 'um_admin_login_logged_' . $user_id;
		if (get_transient($session_key)) {
			return;
		}
		
		// Set transient for 1 hour to prevent duplicate logs
		set_transient($session_key, true, HOUR_IN_SECONDS);
		$logged = true;
		
		// Log to activity log
		self::add_activity_log('admin_login', $user_id, 'WP Admin', [
			'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
			'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
			'admin_url' => admin_url(),
		]);
	}
	
	/**
	 * Capture post data before save to track changes.
	 * Stores old post data in a transient keyed by post ID.
	 */
	public static function capture_post_data_before_save($data, $postarr): array {
		// Check if admin activity logging is enabled (defaults to true)
		$settings = self::get_settings();
		$log_admin_activity = isset($settings['log_admin_activity']) ? !empty($settings['log_admin_activity']) : true;
		
		if (!$log_admin_activity) {
			return $data;
		}
		
		// Only in admin area
		if (!is_admin()) {
			return $data;
		}
		
		// Skip if no post ID (new post)
		if (empty($postarr['ID'])) {
			return $data;
		}
		
		$post_id = (int) $postarr['ID'];
		
		// Get old post data
		$old_post = get_post($post_id);
		if (!$old_post) {
			return $data;
		}
		
		// Store old data in transient (expires in 5 minutes)
		$old_data = [
			'post_title' => $old_post->post_title,
			'post_content' => $old_post->post_content,
			'post_excerpt' => $old_post->post_excerpt,
			'post_status' => $old_post->post_status,
			'post_parent' => $old_post->post_parent,
			'menu_order' => $old_post->menu_order,
		];
		
		set_transient('um_old_post_data_' . $post_id, $old_data, 5 * MINUTE_IN_SECONDS);
		
		return $data;
	}
	
	/**
	 * Log post creation and edits to activity log.
	 * Tracks what fields were added, removed, or changed.
	 */
	public static function log_post_save($post_id, $post, $update): void {
		// Check if admin activity logging is enabled (defaults to true)
		$settings = self::get_settings();
		$log_admin_activity = isset($settings['log_admin_activity']) ? !empty($settings['log_admin_activity']) : true;
		
		if (!$log_admin_activity) {
			return;
		}
		
		// Skip autosaves and revisions
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (wp_is_post_revision($post_id)) {
			return;
		}
		
		// Only log in admin area
		if (!is_admin()) {
			return;
		}
		
		// Skip if user doesn't have permission
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		
		$post_type = get_post_type($post_id);
		if (!$post_type) {
			return;
		}
		
		$user_id = get_current_user_id();
		if (!$user_id) {
			return;
		}
		
		// Get post type object for label
		$post_type_obj = get_post_type_object($post_type);
		$post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post_type;
		
		// Get old post data if this is an update
		$changes = [];
		
		if ($update) {
			// Get old data from transient (set by capture_post_data_before_save)
			$old_data = get_transient('um_old_post_data_' . $post_id);
			
			if ($old_data && is_array($old_data)) {
				// Track post fields
				$post_fields = ['post_title', 'post_content', 'post_excerpt', 'post_status', 'post_parent', 'menu_order'];
				foreach ($post_fields as $field) {
					$old_value = isset($old_data[$field]) ? $old_data[$field] : '';
					$new_value = isset($post->$field) ? $post->$field : '';
					if ($old_value !== $new_value) {
						$changes[$field] = [
							'old' => $old_value,
							'new' => $new_value,
						];
					}
				}
			}
			
			// Clean up transient
			delete_transient('um_old_post_data_' . $post_id);
		}
		
		// Determine action
		$action = $update ? 'post_updated' : 'post_created';
		$tool = sprintf(__('%s Editor', 'user-manager'), $post_type_label);
		
		// Build extra data
		$extra = [
			'post_id' => $post_id,
			'post_type' => $post_type,
			'post_type_label' => $post_type_label,
			'post_title' => $post->post_title ?? '',
			'edit_link' => get_edit_post_link($post_id, 'raw'),
			'view_link' => get_permalink($post_id),
		];
		
		if ($update && !empty($changes)) {
			$extra['changes'] = $changes;
			$extra['fields_changed'] = array_keys($changes);
		}
		
		// Log the activity
		self::add_activity_log($action, $user_id, $tool, $extra);
	}
	
	/**
	 * Log post status transitions (draft to publish, etc.)
	 */
	public static function log_post_status_change($new_status, $old_status, $post): void {
		// Check if admin activity logging is enabled (defaults to true)
		$settings = self::get_settings();
		$log_admin_activity = isset($settings['log_admin_activity']) ? !empty($settings['log_admin_activity']) : true;
		
		if (!$log_admin_activity) {
			return;
		}
		
		// Skip if status hasn't changed
		if ($new_status === $old_status) {
			return;
		}
		
		// Only log in admin area
		if (!is_admin()) {
			return;
		}
		
		// Skip autosaves and revisions
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (wp_is_post_revision($post->ID)) {
			return;
		}
		
		$user_id = get_current_user_id();
		if (!$user_id) {
			return;
		}
		
		$post_type = get_post_type($post);
		if (!$post_type) {
			return;
		}
		
		$post_type_obj = get_post_type_object($post_type);
		$post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post_type;
		
		$tool = sprintf(__('%s Editor', 'user-manager'), $post_type_label);
		
		$extra = [
			'post_id' => $post->ID,
			'post_type' => $post_type,
			'post_type_label' => $post_type_label,
			'post_title' => $post->post_title ?? '',
			'old_status' => $old_status,
			'new_status' => $new_status,
			'edit_link' => get_edit_post_link($post->ID, 'raw'),
			'view_link' => get_permalink($post->ID),
		];
		
		self::add_activity_log('post_status_changed', $user_id, $tool, $extra);
	}
	
	/**
	 * Log plugin activation.
	 * 
	 * @param string $plugin Plugin file path relative to plugins directory
	 * @param bool $network_wide Whether the plugin was activated network-wide
	 */
	public static function log_plugin_activation(string $plugin, bool $network_wide = false): void {
		// Check if admin activity logging is enabled (defaults to true)
		$settings = self::get_settings();
		$log_admin_activity = isset($settings['log_admin_activity']) ? !empty($settings['log_admin_activity']) : true;
		
		if (!$log_admin_activity) {
			return;
		}
		
		$user_id = get_current_user_id();
		if (!$user_id) {
			return;
		}
		
		// Get plugin data
		$plugin_data = self::get_plugin_data($plugin);
		
		$tool = __('Plugin Manager', 'user-manager');
		
		$extra = [
			'plugin_file' => $plugin,
			'plugin_name' => $plugin_data['name'] ?? $plugin,
			'plugin_version' => $plugin_data['version'] ?? '',
			'plugin_author' => $plugin_data['author'] ?? '',
			'network_wide' => $network_wide,
			'plugin_url' => admin_url('plugins.php'),
		];
		
		self::add_activity_log('plugin_activated', $user_id, $tool, $extra);
	}
	
	/**
	 * Log plugin deactivation.
	 * 
	 * @param string $plugin Plugin file path relative to plugins directory
	 * @param bool $network_wide Whether the plugin was deactivated network-wide
	 */
	public static function log_plugin_deactivation(string $plugin, bool $network_wide = false): void {
		// Check if admin activity logging is enabled (defaults to true)
		$settings = self::get_settings();
		$log_admin_activity = isset($settings['log_admin_activity']) ? !empty($settings['log_admin_activity']) : true;
		
		if (!$log_admin_activity) {
			return;
		}
		
		$user_id = get_current_user_id();
		if (!$user_id) {
			return;
		}
		
		// Get plugin data
		$plugin_data = self::get_plugin_data($plugin);
		
		$tool = __('Plugin Manager', 'user-manager');
		
		$extra = [
			'plugin_file' => $plugin,
			'plugin_name' => $plugin_data['name'] ?? $plugin,
			'plugin_version' => $plugin_data['version'] ?? '',
			'plugin_author' => $plugin_data['author'] ?? '',
			'network_wide' => $network_wide,
			'plugin_url' => admin_url('plugins.php'),
		];
		
		self::add_activity_log('plugin_deactivated', $user_id, $tool, $extra);
	}
	
	/**
	 * Get plugin data from plugin file.
	 * 
	 * @param string $plugin_file Plugin file path relative to plugins directory
	 * @return array Plugin data (name, version, author, etc.)
	 */
	private static function get_plugin_data(string $plugin_file): array {
		if (!function_exists('get_plugin_data')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
		if (!file_exists($plugin_path)) {
			return [];
		}
		
		$plugin_data = get_plugin_data($plugin_path);
		
		return [
			'name' => $plugin_data['Name'] ?? '',
			'version' => $plugin_data['Version'] ?? '',
			'author' => $plugin_data['Author'] ?? '',
			'description' => $plugin_data['Description'] ?? '',
		];
	}
	
	/**
	 * Render a Login History box on the user profile/edit screen (admins only).
	 *
	 * @param WP_User $user
	 */
	public static function render_user_login_history_profile($user): void {
		if (!current_user_can('manage_options')) {
			return;
		}
		if (!$user || !isset($user->ID)) {
			return;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'um_login_history';
		$table_exists = ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table);
		$user_id = (int) $user->ID;
		$logins = [];
		$error = '';
		
		if ($table_exists) {
			$logins = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, ip_address, user_agent, created_at FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT 100",
					$user_id
				)
			);
		} else {
			$error = sprintf(__('Login table not found: %s', 'user-manager'), esc_html($table));
		}
		?>
		<h2><?php esc_html_e('Login History (Debug)', 'user-manager'); ?></h2>
		<table class="form-table">
			<tr>
				<th><label><?php esc_html_e('Site Info', 'user-manager'); ?></label></th>
				<td>
					<code><?php echo esc_html(network_home_url()); ?></code><br>
					<?php esc_html_e('Blog ID', 'user-manager'); ?>: <code><?php echo function_exists('get_current_blog_id') ? (int) get_current_blog_id() : 0; ?></code><br>
					<?php esc_html_e('DB Prefix', 'user-manager'); ?>: <code><?php echo esc_html($wpdb->prefix); ?></code><br>
					<?php esc_html_e('Login Table', 'user-manager'); ?>: <code><?php echo esc_html($table); ?></code><br>
					<?php esc_html_e('Table Exists', 'user-manager'); ?>: <code><?php echo $table_exists ? 'yes' : 'no'; ?></code>
				</td>
			</tr>
			<?php if (!empty($error)) : ?>
			<tr>
				<th><label><?php esc_html_e('Status', 'user-manager'); ?></label></th>
				<td><span style="color:#d63638;"><?php echo esc_html($error); ?></span></td>
			</tr>
			<?php endif; ?>
			<tr>
				<th><label><?php esc_html_e('Recent Logins', 'user-manager'); ?></label></th>
				<td>
					<?php if (empty($logins)) : ?>
						<em><?php esc_html_e('No login records found for this user on this site.', 'user-manager'); ?></em>
					<?php else : ?>
						<div style="border:1px solid #dcdcde; border-radius:4px; background:#fff;">
							<table class="widefat striped" style="margin:0;">
								<thead>
									<tr>
										<th><?php esc_html_e('Date/Time', 'user-manager'); ?></th>
										<th><?php esc_html_e('Time Ago', 'user-manager'); ?></th>
										<th><?php esc_html_e('IP', 'user-manager'); ?></th>
										<th><?php esc_html_e('User Agent', 'user-manager'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($logins as $row) : 
										$ts = $row->created_at ? strtotime($row->created_at) : 0;
									?>
									<tr>
										<td><?php echo esc_html($row->created_at); ?></td>
										<td><?php echo $ts ? esc_html(self::nice_time($ts)) : '—'; ?></td>
										<td><code><?php echo esc_html($row->ip_address ?: ''); ?></code></td>
										<td style="max-width:420px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo esc_attr($row->user_agent ?: ''); ?>">
											<?php echo esc_html($row->user_agent ?: ''); ?>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
						<p class="description" style="margin-top:8px;">
							<?php esc_html_e('Showing up to the 100 most recent logins for this user on this site.', 'user-manager'); ?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render Custom Email Lists section on user edit/profile page.
	 *
	 * @param WP_User $user User object.
	 */
	public static function render_user_email_lists_profile($user): void {
		if (!current_user_can('manage_options')) {
			return;
		}
		if (!$user || !isset($user->ID)) {
			return;
		}

		// Get all custom email lists
		$custom_lists = get_option('um_custom_email_lists', []);
		if (!is_array($custom_lists)) {
			$custom_lists = [];
		}
		// Sort lists A-Z by title
		uasort($custom_lists, function($a, $b) {
			return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
		});

		$user_email = $user->user_email;
		$user_lists = [];

		// Find which lists contain this user's email
		foreach ($custom_lists as $list_id => $list_data) {
			$emails = $list_data['emails'] ?? [];
			if (in_array(strtolower($user_email), array_map('strtolower', $emails), true)) {
				$user_lists[] = $list_id;
			}
		}
		?>
		<h2><?php esc_html_e('Custom Email Lists', 'user-manager'); ?></h2>
		<table class="form-table">
			<tr>
				<th><label><?php esc_html_e('User Email', 'user-manager'); ?></label></th>
				<td>
					<code><?php echo esc_html($user_email); ?></code>
				</td>
			</tr>
			<?php if (empty($custom_lists)) : ?>
			<tr>
				<th><label><?php esc_html_e('Lists', 'user-manager'); ?></label></th>
				<td>
					<p class="description"><?php esc_html_e('No custom email lists have been created yet. Create lists in the User Manager → Email Users tab.', 'user-manager'); ?></p>
				</td>
			</tr>
			<?php else : ?>
			<tr>
				<th><label><?php esc_html_e('Lists', 'user-manager'); ?></label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e('Custom Email Lists', 'user-manager'); ?></span></legend>
						<?php foreach ($custom_lists as $list_id => $list_data) : ?>
							<?php
							$list_title = $list_data['title'] ?? '';
							$emails = $list_data['emails'] ?? [];
							$is_in_list = in_array($list_id, $user_lists, true);
							$email_count = count($emails);
							?>
							<label style="display: block; margin-bottom: 10px; padding: 8px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
								<input 
									type="checkbox" 
									name="um_email_lists[]" 
									value="<?php echo esc_attr($list_id); ?>" 
									<?php checked($is_in_list); ?>
								/>
								<strong><?php echo esc_html($list_title); ?></strong>
								<span style="color: #646970; margin-left: 8px;">
									(<?php echo esc_html(number_format($email_count)); ?> <?php echo esc_html($email_count === 1 ? __('email', 'user-manager') : __('emails', 'user-manager')); ?>)
								</span>
							</label>
						<?php endforeach; ?>
					</fieldset>
					<p class="description" style="margin-top: 10px;">
						<?php esc_html_e('Select the custom email lists this user should be included in. Changes will be saved when you update the user profile.', 'user-manager'); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Save Custom Email Lists changes on user profile update.
	 *
	 * @param int $user_id User ID.
	 */
	public static function save_user_email_lists_profile(int $user_id): void {
		if (!current_user_can('manage_options')) {
			return;
		}

		$user = get_user_by('ID', $user_id);
		if (!$user) {
			return;
		}

		$user_email = strtolower($user->user_email);
		$selected_lists = isset($_POST['um_email_lists']) ? array_map('sanitize_text_field', (array) wp_unslash($_POST['um_email_lists'])) : [];

		// Get all custom email lists
		$custom_lists = get_option('um_custom_email_lists', []);
		if (!is_array($custom_lists)) {
			$custom_lists = [];
		}

		$lists_changed = false;

		// Update each list
		foreach ($custom_lists as $list_id => &$list_data) {
			$emails = $list_data['emails'] ?? [];
			$emails_lower = array_map('strtolower', $emails);
			$is_selected = in_array($list_id, $selected_lists, true);
			$is_in_list = in_array($user_email, $emails_lower, true);

			if ($is_selected && !$is_in_list) {
				// Add user email to list
				$emails[] = $user->user_email;
				$emails = array_unique($emails);
				$list_data['emails'] = array_values($emails);
				$list_data['updated_at'] = current_time('mysql');
				$lists_changed = true;
			} elseif (!$is_selected && $is_in_list) {
				// Remove user email from list
				$emails = array_filter($emails, function($email) use ($user_email) {
					return strtolower($email) !== $user_email;
				});
				$list_data['emails'] = array_values($emails);
				$list_data['updated_at'] = current_time('mysql');
				$lists_changed = true;
			}
		}
		unset($list_data);

		// Save if any changes were made
		if ($lists_changed) {
			update_option('um_custom_email_lists', $custom_lists);
		}
	}
}

