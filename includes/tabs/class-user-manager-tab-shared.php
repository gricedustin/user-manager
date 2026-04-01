<?php
/**
 * Shared tab helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Shared {

	/**
	 * Render email preview modal.
	 */
	public static function render_email_preview_modal(array $templates): void {
		$default_subject = 'Your Login Information';
		$default_body = '<p><strong>Login Page:</strong><br>
<a href="%SITEURL%%LOGINURL%">%SITEURL%%LOGINURL%</a></p>

<p><strong>Username:</strong><br>
%USERNAME%</p>

<p><strong>Password:</strong><br>
%PASSWORD%</p>';
		?>
		<!-- Email Preview Modal -->
		<div id="um-email-preview-modal" class="um-modal" style="display:none;">
			<div class="um-modal-overlay"></div>
			<div class="um-modal-content">
				<div class="um-modal-header">
					<h3><span class="dashicons dashicons-email"></span> <?php esc_html_e('Email Preview', 'user-manager'); ?></h3>
					<button type="button" class="um-modal-close">&times;</button>
				</div>
				<div class="um-modal-body">
					<div class="um-preview-warning">
						<span class="dashicons dashicons-warning"></span>
						<strong><?php esc_html_e('An email will be sent upon creating this user.', 'user-manager'); ?></strong>
					</div>
					
					<div class="um-preview-section">
						<label><?php esc_html_e('To:', 'user-manager'); ?></label>
						<div id="um-preview-to" class="um-preview-value"></div>
					</div>
					
					<div class="um-preview-section">
						<label><?php esc_html_e('Subject:', 'user-manager'); ?></label>
						<div id="um-preview-subject" class="um-preview-value"></div>
					</div>
					
					<div class="um-preview-section um-preview-section-full">
						<label><?php esc_html_e('Email Preview:', 'user-manager'); ?></label>
						<div class="um-preview-email-frame-wrapper">
							<iframe id="um-preview-iframe" class="um-preview-iframe"></iframe>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<style>
		.um-modal {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			z-index: 100000;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.um-modal-overlay {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(0,0,0,0.6);
		}
		.um-modal-content {
			position: relative;
			background: #fff;
			border-radius: 8px;
			max-width: 700px;
			width: 95%;
			max-height: 90vh;
			overflow: hidden;
			display: flex;
			flex-direction: column;
			box-shadow: 0 4px 20px rgba(0,0,0,0.3);
		}
		.um-modal-header {
			padding: 16px 20px;
			border-bottom: 1px solid #dcdcde;
			display: flex;
			justify-content: space-between;
			align-items: center;
			background: #f6f7f7;
		}
		.um-modal-header h3 {
			margin: 0;
			font-size: 16px;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.um-modal-header h3 .dashicons {
			color: #2271b1;
		}
		.um-modal-close {
			background: none;
			border: none;
			font-size: 24px;
			cursor: pointer;
			color: #666;
			padding: 0;
			line-height: 1;
		}
		.um-modal-close:hover {
			color: #d63638;
		}
		.um-modal-body {
			padding: 20px;
			overflow-y: auto;
			flex: 1;
		}
		.um-preview-warning {
			background: #fff8e5;
			border: 1px solid #ffcc00;
			border-radius: 4px;
			padding: 12px 16px;
			margin-bottom: 20px;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.um-preview-warning .dashicons {
			color: #d63638;
		}
		.um-preview-section {
			margin-bottom: 16px;
		}
		.um-preview-section label {
			display: block;
			font-weight: 600;
			margin-bottom: 6px;
			color: #1d2327;
		}
		.um-preview-value {
			background: #f6f7f7;
			padding: 10px 12px;
			border-radius: 4px;
			border: 1px solid #dcdcde;
		}
		.um-preview-section-full {
			margin-bottom: 0;
		}
		.um-preview-email-frame-wrapper {
			border: 1px solid #dcdcde;
			border-radius: 4px;
			overflow: hidden;
			background: #f7f7f7;
		}
		.um-preview-iframe {
			width: 100%;
			height: 350px;
			border: none;
			background: #fff;
		}
		</style>
		
		<script>
		var umDefaultSubject = <?php echo json_encode($default_subject); ?>;
		var umSiteUrl = <?php echo json_encode(home_url()); ?>;
		var umAjaxUrl = <?php echo json_encode(admin_url('admin-ajax.php')); ?>;
		var umPreviewNonce = <?php echo json_encode(wp_create_nonce('user_manager_email_preview')); ?>;
		var umTemplates = <?php echo json_encode($templates); ?>;
		var umCurrentFormType = '';
		var umIsConfirmation = false;
		
		function umShowEmailPreview(formType, isConfirmation) {
			umCurrentFormType = formType;
			umIsConfirmation = isConfirmation || false;
			
			var email = '', username = '', firstName = '', lastName = '', loginUrl = '/my-account/', templateId = '', couponCode = '';
			var subject = umDefaultSubject;
			
			if (formType === 'single') {
				email = jQuery('#um-email').val() || 'user@example.com';
				username = jQuery('#um-username').val() || email;
				firstName = jQuery('#um-first-name').val() || '';
				lastName = jQuery('#um-last-name').val() || '';
				loginUrl = jQuery('#um-login-url').val() || '/my-account/';
				templateId = jQuery('#um-template').val();
				couponCode = jQuery('#um-create-coupon-code').val() || 'SAMPLECOUPON123';
			} else if (formType === 'reset') {
				email = jQuery('#um-reset-email').val() || 'user@example.com';
				username = email;
				templateId = jQuery('#um-reset-template').val();
			} else if (formType === 'bulk-paste') {
				// Parse first line of paste data
				var pasteData = jQuery('#um-paste-data').val();
				if (pasteData) {
					var lines = pasteData.trim().split(/\r?\n/);
					var firstLine = lines[0];
					// Check if first line is header
					if (firstLine.toLowerCase().indexOf('email') === 0) {
						firstLine = lines[1] || '';
					}
					var cells = firstLine.split('\t');
					email = cells[0] || 'user@example.com';
					firstName = cells[1] || '';
					lastName = cells[2] || '';
					username = email;
				} else {
					email = 'user@example.com';
				}
				loginUrl = jQuery('#um-paste-login-url').val() || '/my-account/';
				templateId = jQuery('#um-paste-template').val();
				couponCode = jQuery('#um-paste-coupon-code').val() || 'SAMPLECOUPON123';
			} else if (formType === 'bulk-file') {
				email = 'first-user@example.com';
				firstName = 'First';
				lastName = 'User';
				username = email;
				loginUrl = jQuery('#um-bulk-login-url').val() || '/my-account/';
				templateId = jQuery('#um-bulk-template').val();
				couponCode = jQuery('#um-bulk-coupon-code').val() || 'SAMPLECOUPON123';
			} else if (formType === 'bulk-coupons') {
				var emailsRaw = jQuery('#um-bulk-coupons-emails').val();
				if (emailsRaw) {
					var linesEmails = emailsRaw.trim().split(/\r?\n/);
					email = linesEmails[0] || 'user@example.com';
				} else {
					email = 'user@example.com';
				}
				username   = email;
				firstName  = '';
				lastName   = '';
				loginUrl   = '/my-account/';
				templateId = jQuery('#um-bulk-coupons-template-email').val();
				couponCode = 'SAMPLECOUPON123';
			} else if (formType === 'nuc') {
				// New User Coupons: preview email with a single sample coupon.
				email      = 'newuser@example.com';
				username   = email;
				firstName  = 'New';
				lastName   = 'User';
				loginUrl   = '/my-account/';
				templateId = jQuery('#nuc_email_template').val();
				couponCode = 'NEWUSER-123456';
			} else if (formType === 'coupon-remainder') {
				// Coupon Remaining Balance: preview the selected/default email template.
				email      = 'user@example.com';
				username   = email.split('@')[0] || 'user';
				firstName  = '';
				lastName   = '';
				loginUrl   = '/my-account/';
				templateId = jQuery('#um-coupon-remainder-email-template').val();
				couponCode = 'REMAINING-BALANCE-123456';
			} else if (formType === 'email-users') {
				// Email Users: get values from email-users form
				var emailsRaw = jQuery('#um-email-users-list').val();
				if (emailsRaw) {
					var linesEmails = emailsRaw.trim().split(/\r?\n/);
					email = linesEmails[0].trim() || 'user@example.com';
				} else {
					email = 'user@example.com';
				}
				username = email.split('@')[0] || 'user';
				loginUrl = jQuery('#um-email-users-login-url').val() || '/my-account/';
				templateId = jQuery('#um-email-users-template').val();
				couponCode = jQuery('#um-email-users-coupon-code').val() || 'SAMPLECOUPON123';
			}
			
			// Get subject from template
			if (templateId && umTemplates[templateId]) {
				subject = umTemplates[templateId].subject;
			} else if (templateId === '__um_default__') {
				subject = 'Your Remaining Balance Coupon Code';
			}
			
			// Replace placeholders in subject
			var password = '••••••••••••';
			subject = subject.replace(/%SITEURL%/g, umSiteUrl)
				.replace(/%LOGINURL%/g, loginUrl)
				.replace(/%USERNAME%/g, username)
				.replace(/%PASSWORD%/g, password)
				.replace(/%EMAIL%/g, email)
				.replace(/%FIRSTNAME%/g, firstName)
				.replace(/%LASTNAME%/g, lastName)
				.replace(/%COUPONCODE%/g, couponCode || 'SAMPLECOUPON123')
				.replace(/\[coupon_code\]/g, couponCode || 'SAMPLECOUPON123');
			
			// Update modal content
			jQuery('#um-preview-to').text(email);
			jQuery('#um-preview-subject').text(subject);
			
			// Build preview URL with parameters
			var previewUrl = umAjaxUrl + '?action=user_manager_email_preview' +
				'&nonce=' + encodeURIComponent(umPreviewNonce) +
				'&template_id=' + encodeURIComponent(templateId) +
				'&email=' + encodeURIComponent(email) +
				'&username=' + encodeURIComponent(username) +
				'&first_name=' + encodeURIComponent(firstName) +
				'&last_name=' + encodeURIComponent(lastName) +
				'&login_url=' + encodeURIComponent(loginUrl) +
				'&coupon_code=' + encodeURIComponent(couponCode || 'SAMPLECOUPON123');
			
			// Load preview in iframe
			document.getElementById('um-preview-iframe').src = previewUrl;
			
			// Show modal
			jQuery('#um-email-preview-modal').show();
		}
		
		jQuery(document).ready(function($) {
			// Close modal handlers
			$('.um-modal-close, .um-modal-overlay').on('click', function() {
				$('#um-email-preview-modal').hide();
			});
			
			// ESC key to close
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' && $('#um-email-preview-modal').is(':visible')) {
					$('#um-email-preview-modal').hide();
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Build settings URL for editing email/sms templates.
	 *
	 * @param string $template_type Either "email" or "sms".
	 */
	public static function get_template_settings_url(string $template_type = 'email'): string {
		$addon_section = $template_type === 'sms' ? 'send-sms-text' : 'send-email-users';
		$query_args = [
			'addon_section' => $addon_section,
		];
		if ($template_type !== 'sms') {
			$query_args['open_email_templates'] = '1';
		}
		return add_query_arg($query_args, User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS));
	}

	/**
	 * Render inline shortcut link to the template settings editor screen.
	 *
	 * @param string $template_type Either "email" or "sms".
	 */
	public static function render_template_settings_shortcut(string $template_type = 'email'): void {
		$template_type = $template_type === 'sms' ? 'sms' : 'email';
		$url = self::get_template_settings_url($template_type);
		$link_label = $template_type === 'sms'
			? __('Edit SMS Text Templates', 'user-manager')
			: __('Edit Email Templates', 'user-manager');
		?>
		<span class="description" style="margin-left:8px;">
			<a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($link_label); ?></a>
		</span>
		<?php
	}
}




