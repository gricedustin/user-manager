<?php
/**
 * User Manager Email Handler
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Email {

	/**
	 * Build email headers with From and Reply-To based on settings.
	 *
	 * @param array $additional_headers Optional additional headers to include.
	 * @return array Array of email headers.
	 */
	public static function build_email_headers(array $additional_headers = []): array {
		$headers = ['Content-Type: text/html; charset=UTF-8'];
		
		$settings = User_Manager_Core::get_settings();
		
		// Set From header if configured
		$from_name = $settings['send_from_name'] ?? '';
		$from_email = $settings['send_from_email'] ?? '';
		
		if (!empty($from_email) && is_email($from_email)) {
			if (!empty($from_name)) {
				$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
			} else {
				$headers[] = 'From: ' . $from_email;
			}
		} elseif (!empty($from_name)) {
			// If only name is set, use default WordPress email with custom name
			$default_email = get_option('admin_email');
			if ($default_email && is_email($default_email)) {
				$headers[] = 'From: ' . $from_name . ' <' . $default_email . '>';
			}
		}
		
		// Set Reply-To header if configured
		$reply_to = $settings['reply_to_email'] ?? '';
		if (!empty($reply_to) && is_email($reply_to)) {
			$headers[] = 'Reply-To: ' . $reply_to;
		} elseif (!empty($from_email) && is_email($from_email)) {
			// If no Reply-To is set but From is set, use From as Reply-To
			$headers[] = 'Reply-To: ' . $from_email;
		}
		
		// Merge with additional headers
		return array_merge($headers, $additional_headers);
	}

	/**
	 * Send user email using WooCommerce template.
	 *
	 * @param int    $user_id    User ID.
	 * @param string $password   Password (or masked) for %PASSWORD%.
	 * @param string $login_url  Login URL for %LOGINURL%.
	 * @param string $template_id Template ID.
	 * @param string $coupon_code Optional. Used when template contains %COUPONCODE%.
	 */
	public static function send_user_email(int $user_id, string $password, string $login_url, string $template_id = '', string $coupon_code = ''): bool {
		$user = get_user_by('ID', $user_id);
		if (!$user) {
			return false;
		}

		// Get template
		$template = null;
		if (!empty($template_id)) {
			$templates = User_Manager_Core::get_email_templates();
			if (isset($templates[$template_id])) {
				$template = $templates[$template_id];
			}
		}

		// Use default template if none specified
		if (!$template) {
			$template = self::get_default_template();
		}

		// Generate password reset URL
		$reset_key = get_password_reset_key($user);
		if (is_wp_error($reset_key)) {
			$password_reset_url = home_url('/my-account/lost-password/');
		} else {
			$password_reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');
		}

		// Replace placeholders
		$replacements = [
			'%SITEURL%' => home_url(),
			'%LOGINURL%' => $login_url,
			'%USERNAME%' => $user->user_login,
			'%PASSWORD%' => $password,
			'%EMAIL%' => $user->user_email,
			'%FIRSTNAME%' => $user->first_name,
			'%LASTNAME%' => $user->last_name,
			'%PASSWORDRESETURL%' => $password_reset_url,
			'%COUPONCODE%' => $coupon_code,
		];

		$subject = str_replace(array_keys($replacements), array_values($replacements), $template['subject']);
		$heading = str_replace(array_keys($replacements), array_values($replacements), $template['heading']);
		$body = str_replace(array_keys($replacements), array_values($replacements), $template['body']);

		// Build email content using WooCommerce template if available
		$email_content = self::wrap_in_woocommerce_template($body, $heading);

		// Set up headers
		$additional_headers = [];
		if (!empty($template['bcc'])) {
			$additional_headers[] = 'Bcc: ' . $template['bcc'];
		}
		$headers = self::build_email_headers($additional_headers);

		// Send email
		return wp_mail($user->user_email, $subject, $email_content, $headers);
	}

	/**
	 * Send email to any email address (not necessarily a WordPress user).
	 *
	 * @param string $email       Email address to send to.
	 * @param string $login_url   Login URL for template placeholders.
	 * @param string $template_id Template ID to use.
	 * @param string $coupon_code Optional. Used when template contains %COUPONCODE%.
	 * @return bool True on success, false on failure.
	 */
	public static function send_email_to_address(string $email, string $login_url, string $template_id = '', string $coupon_code = ''): bool {
		if (!is_email($email) || empty($template_id)) {
			return false;
		}

		// Get template
		$templates = User_Manager_Core::get_email_templates();
		$template = $templates[$template_id] ?? null;
		if (!$template) {
			return false;
		}

		// Extract username from email (part before @)
		$username = strstr($email, '@', true) ?: $email;

		// Replace placeholders
		$replacements = [
			'%SITEURL%' => home_url(),
			'%LOGINURL%' => $login_url,
			'%USERNAME%' => $username,
			'%PASSWORD%' => '••••••••',
			'%EMAIL%' => $email,
			'%FIRSTNAME%' => '',
			'%LASTNAME%' => '',
			'%PASSWORDRESETURL%' => home_url('/my-account/lost-password/'),
			'%COUPONCODE%' => $coupon_code,
		];

		$subject = str_replace(array_keys($replacements), array_values($replacements), $template['subject'] ?? '');
		$heading = str_replace(array_keys($replacements), array_values($replacements), $template['heading'] ?? '');
		$body = str_replace(array_keys($replacements), array_values($replacements), $template['body'] ?? '');

		// Build email content using WooCommerce template if available
		$email_content = self::wrap_in_woocommerce_template($body, $heading);

		// Set up headers
		$additional_headers = [];
		if (!empty($template['bcc'])) {
			$additional_headers[] = 'Bcc: ' . $template['bcc'];
		}
		$headers = self::build_email_headers($additional_headers);

		// Send email
		return wp_mail($email, $subject, $email_content, $headers);
	}

	/**
	 * Get default email template.
	 */
	private static function get_default_template(): array {
		return [
			'title' => 'Default',
			'subject' => 'Your Login Information',
			'heading' => 'Your Username and Password',
			'body' => '<p><strong>Login Page:</strong><br>
<a href="%SITEURL%%LOGINURL%">%SITEURL%%LOGINURL%</a></p>

<p><strong>Username:</strong><br>
%USERNAME%</p>

<p><strong>Password:</strong><br>
%PASSWORD%</p>',
			'bcc' => '',
		];
	}

	/**
	 * Get preview HTML for the email.
	 */
	public static function get_preview_html(string $content, string $heading): string {
		return self::wrap_in_woocommerce_template($content, $heading);
	}

	/**
	 * Wrap email content in WooCommerce email template.
	 */
	private static function wrap_in_woocommerce_template(string $content, string $heading): string {
		// Check if WooCommerce is active
		if (!class_exists('WooCommerce') || !function_exists('WC')) {
			return self::get_basic_html_template($content, $heading);
		}

		// Try to use WooCommerce email template
		try {
			// Get woocommerce mailer from instance
			$mailer = WC()->mailer();
			if (!$mailer) {
				return self::get_basic_html_template($content, $heading);
			}
			
			// Wrap message using woocommerce html email template
			$wrapped_message = $mailer->wrap_message($heading, $content);
			
			// Create new WC_Email instance
			$wc_email = new WC_Email();
			
			// Style the wrapped message with woocommerce inline styles
			$html_message = $wc_email->style_inline($wrapped_message);

			return $html_message;
		} catch (\Exception $e) {
			return self::get_basic_html_template($content, $heading);
		} catch (\Error $e) {
			return self::get_basic_html_template($content, $heading);
		}
	}

	/**
	 * Get basic HTML email template (fallback).
	 */
	private static function get_basic_html_template(string $content, string $heading): string {
		$site_name = get_bloginfo('name');
		
		return '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>' . esc_html($heading) . '</title>
<style>
body {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
	font-size: 14px;
	line-height: 1.6;
	color: #333;
	background-color: #f7f7f7;
	margin: 0;
	padding: 0;
}
.email-wrapper {
	max-width: 600px;
	margin: 0 auto;
	padding: 20px;
}
.email-header {
	background-color: #2271b1;
	color: #fff;
	padding: 20px;
	text-align: center;
}
.email-header h1 {
	margin: 0;
	font-size: 24px;
}
.email-body {
	background-color: #fff;
	padding: 30px;
	border: 1px solid #ddd;
}
.email-footer {
	text-align: center;
	padding: 20px;
	font-size: 12px;
	color: #666;
}
a {
	color: #2271b1;
}
</style>
</head>
<body>
<div class="email-wrapper">
	<div class="email-header">
		<h1>' . esc_html($heading) . '</h1>
	</div>
	<div class="email-body">
		' . wp_kses_post($content) . '
	</div>
	<div class="email-footer">
		<p>' . esc_html($site_name) . '</p>
	</div>
</div>
</body>
</html>';
	}
}

