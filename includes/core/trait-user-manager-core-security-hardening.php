<?php
/**
 * Security Hardening helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Security_Hardening_Trait {

	/**
	 * Apply enabled hardening controls for current request.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_apply_security_hardening(array $settings): void {
		if (empty($settings['security_hardening_enabled'])) {
			return;
		}

		if (!empty($settings['security_hardening_disallow_file_edit']) && !defined('DISALLOW_FILE_EDIT')) {
			define('DISALLOW_FILE_EDIT', true);
		}

		if (!empty($settings['security_hardening_disallow_file_mods']) && !defined('DISALLOW_FILE_MODS')) {
			define('DISALLOW_FILE_MODS', true);
		}

		if (!empty($settings['security_hardening_force_ssl_admin'])) {
			if (function_exists('force_ssl_admin')) {
				force_ssl_admin(true);
			}
			if (!defined('FORCE_SSL_ADMIN')) {
				define('FORCE_SSL_ADMIN', true);
			}
		}

		if (!empty($settings['security_hardening_block_rest_user_enumeration'])) {
			add_filter('rest_endpoints', [__CLASS__, 'security_hardening_filter_rest_endpoints'], 99, 1);
		}

		if (!empty($settings['security_hardening_hide_wp_version'])) {
			remove_action('wp_head', 'wp_generator');
			add_filter('the_generator', '__return_empty_string');
		}
	}

	/**
	 * Remove default WP user endpoints from public REST index.
	 *
	 * @param mixed $endpoints REST endpoints map.
	 * @return mixed
	 */
	public static function security_hardening_filter_rest_endpoints($endpoints) {
		if (!is_array($endpoints)) {
			return $endpoints;
		}

		unset($endpoints['/wp/v2/users']);
		unset($endpoints['/wp/v2/users/(?P<id>[\\d]+)']);

		return $endpoints;
	}
}

