<?php
/**
 * Add-on card: Order Invoice & Approval.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Invoice_Approval {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['invoice_approval_enabled']);
		$site = trailingslashit(site_url('/'));

		$primary_color = isset($settings['invoice_primary_color']) ? (string) $settings['invoice_primary_color'] : '#4B2E83';
		$button_color = isset($settings['invoice_button_color']) ? (string) $settings['invoice_button_color'] : '#4B2E83';
		$button_text_color = isset($settings['invoice_button_text_color']) ? (string) $settings['invoice_button_text_color'] : '#ffffff';
		$font_family = isset($settings['invoice_font_family']) ? (string) $settings['invoice_font_family'] : 'Poppins, sans-serif';
		$logo_url = isset($settings['invoice_logo_url']) ? (string) $settings['invoice_logo_url'] : '';
		$logo_max_width = isset($settings['invoice_logo_max_width']) ? (string) $settings['invoice_logo_max_width'] : '160px';
		$company_name = isset($settings['invoice_company_name']) ? (string) $settings['invoice_company_name'] : get_bloginfo('name');
		$company_address = isset($settings['invoice_company_address']) ? (string) $settings['invoice_company_address'] : '';
		$company_email = isset($settings['invoice_company_email']) ? (string) $settings['invoice_company_email'] : get_bloginfo('admin_email');
		$company_phone = isset($settings['invoice_company_phone']) ? (string) $settings['invoice_company_phone'] : '';
		$header_note = isset($settings['invoice_header_note']) ? (string) $settings['invoice_header_note'] : '';
		$footer_note = isset($settings['invoice_footer_note']) ? (string) $settings['invoice_footer_note'] : '';
		$footer_note_below_buttons = isset($settings['invoice_footer_note_below_buttons']) ? (string) $settings['invoice_footer_note_below_buttons'] : '';
		$order_label = isset($settings['invoice_order_label']) ? (string) $settings['invoice_order_label'] : 'Order';
		$scrollable_threshold = isset($settings['invoice_scrollable_items_threshold']) ? (string) $settings['invoice_scrollable_items_threshold'] : '';
		$approval_emails = isset($settings['invoice_approval_emails']) ? (string) $settings['invoice_approval_emails'] : '';
		$approval_title = isset($settings['invoice_approval_title']) ? (string) $settings['invoice_approval_title'] : 'Approve & Pay Later';
		$approval_checkbox_text = isset($settings['invoice_approval_checkbox_text']) ? (string) $settings['invoice_approval_checkbox_text'] : 'I hereby approve of placing this order and authorize payment for this order. I understand that by checking this box and submitting this form, I am providing my electronic signature and consent to proceed with the order and payment processing.';
		$approval_button_text = isset($settings['invoice_approval_button_text']) ? (string) $settings['invoice_approval_button_text'] : 'Send to Production';

		$allowed_invoice_approval_emails = [];
		$global_email_entries = preg_split('/\r\n|\r|\n|,/', $approval_emails) ?: [];
		foreach ($global_email_entries as $entry) {
			$email = sanitize_email(trim((string) $entry));
			if ($email === '') {
				continue;
			}
			$key = strtolower($email);
			if (!isset($allowed_invoice_approval_emails[$key])) {
				$allowed_invoice_approval_emails[$key] = [
					'email' => $email,
					'sources' => [],
					'user_id' => 0,
				];
			}
			$allowed_invoice_approval_emails[$key]['sources']['global'] = __('Global list', 'user-manager');
			$user_by_email = function_exists('get_user_by') ? get_user_by('email', $email) : false;
			if ($user_by_email && isset($user_by_email->ID)) {
				$allowed_invoice_approval_emails[$key]['user_id'] = absint($user_by_email->ID);
			}
		}

		if (function_exists('get_users')) {
			$enabled_users = get_users([
				'meta_key' => '_um_invoice_approval_enabled',
				'meta_value' => '1',
				'fields' => ['ID', 'user_email'],
				'number' => -1,
			]);
			foreach ($enabled_users as $enabled_user) {
				$email = isset($enabled_user->user_email) ? sanitize_email((string) $enabled_user->user_email) : '';
				if ($email === '') {
					continue;
				}
				$key = strtolower($email);
				if (!isset($allowed_invoice_approval_emails[$key])) {
					$allowed_invoice_approval_emails[$key] = [
						'email' => $email,
						'sources' => [],
						'user_id' => 0,
					];
				}
				$allowed_invoice_approval_emails[$key]['sources']['profile'] = __('User checkbox enabled', 'user-manager');
				$allowed_invoice_approval_emails[$key]['user_id'] = isset($enabled_user->ID) ? absint($enabled_user->ID) : 0;
			}
		}
		ksort($allowed_invoice_approval_emails, SORT_NATURAL | SORT_FLAG_CASE);
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-invoice-approval" data-um-active-selectors="#um-invoice-approval-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-media-spreadsheet"></span>
				<h2><?php esc_html_e('Order Invoice & Approval', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-invoice-approval-enabled" name="invoice_approval_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Add invoice links to WooCommerce orders, render invoice pages, and allow invoice approval + payment workflows.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-invoice-approval-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-form-field">
						<label for="um-invoice-primary-color"><?php esc_html_e('Primary Color', 'user-manager'); ?></label>
						<input type="text" id="um-invoice-primary-color" name="invoice_primary_color" class="regular-text" value="<?php echo esc_attr($primary_color); ?>"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="invoice_hide_logo_in_pdf" value="1" <?php checked(!empty($settings['invoice_hide_logo_in_pdf'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Hide Logo in PDF', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="invoice_hide_buttons_in_pdf" value="1" <?php checked(!empty($settings['invoice_hide_buttons_in_pdf'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Hide Buttons in PDF', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-invoice-button-color"><?php esc_html_e('Button Color', 'user-manager'); ?></label>
							<input type="text" id="um-invoice-button-color" name="invoice_button_color" class="regular-text" value="<?php echo esc_attr($button_color); ?>"<?php echo $form_attr; ?> />
						</div>
						<div class="um-form-field">
							<label for="um-invoice-button-text-color"><?php esc_html_e('Button Text Color', 'user-manager'); ?></label>
							<input type="text" id="um-invoice-button-text-color" name="invoice_button_text_color" class="regular-text" value="<?php echo esc_attr($button_text_color); ?>"<?php echo $form_attr; ?> />
						</div>
					</div>
					<div class="um-form-field">
						<label for="um-invoice-font-family"><?php esc_html_e('Font Family', 'user-manager'); ?></label>
						<input type="text" id="um-invoice-font-family" name="invoice_font_family" class="regular-text" value="<?php echo esc_attr($font_family); ?>"<?php echo $form_attr; ?> />
					</div>
					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-invoice-logo-url"><?php esc_html_e('Logo URL', 'user-manager'); ?></label>
							<input type="text" id="um-invoice-logo-url" name="invoice_logo_url" class="large-text" value="<?php echo esc_attr($logo_url); ?>"<?php echo $form_attr; ?> />
						</div>
						<div class="um-form-field">
							<label for="um-invoice-logo-max-width"><?php esc_html_e('Logo Max Width', 'user-manager'); ?></label>
							<input type="text" id="um-invoice-logo-max-width" name="invoice_logo_max_width" class="regular-text" value="<?php echo esc_attr($logo_max_width); ?>"<?php echo $form_attr; ?> />
						</div>
					</div>
					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-invoice-company-name"><?php esc_html_e('Company Name', 'user-manager'); ?></label>
							<input type="text" id="um-invoice-company-name" name="invoice_company_name" class="regular-text" value="<?php echo esc_attr($company_name); ?>"<?php echo $form_attr; ?> />
						</div>
						<div class="um-form-field">
							<label for="um-invoice-company-phone"><?php esc_html_e('Company Phone', 'user-manager'); ?></label>
							<input type="text" id="um-invoice-company-phone" name="invoice_company_phone" class="regular-text" value="<?php echo esc_attr($company_phone); ?>"<?php echo $form_attr; ?> />
						</div>
					</div>
					<div class="um-form-field">
						<label for="um-invoice-company-email"><?php esc_html_e('Company Email', 'user-manager'); ?></label>
						<input type="text" id="um-invoice-company-email" name="invoice_company_email" class="regular-text" value="<?php echo esc_attr($company_email); ?>"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label for="um-invoice-company-address"><?php esc_html_e('Company Address (multiline)', 'user-manager'); ?></label>
						<textarea id="um-invoice-company-address" name="invoice_company_address" rows="3" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea($company_address); ?></textarea>
					</div>
					<div class="um-form-field">
						<label for="um-invoice-header-note"><?php esc_html_e('Header Note', 'user-manager'); ?></label>
						<textarea id="um-invoice-header-note" name="invoice_header_note" rows="3" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea($header_note); ?></textarea>
					</div>
					<div class="um-form-field">
						<label for="um-invoice-footer-note"><?php esc_html_e('Footer Note', 'user-manager'); ?></label>
						<textarea id="um-invoice-footer-note" name="invoice_footer_note" rows="3" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea($footer_note); ?></textarea>
					</div>
					<div class="um-form-field">
						<label for="um-invoice-footer-note-below"><?php esc_html_e('Footer Note (Below Payment Buttons)', 'user-manager'); ?></label>
						<textarea id="um-invoice-footer-note-below" name="invoice_footer_note_below_buttons" rows="3" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea($footer_note_below_buttons); ?></textarea>
					</div>
					<div class="um-admin-grid" style="grid-template-columns:1fr 1fr;">
						<div class="um-form-field">
							<label for="um-invoice-order-label"><?php esc_html_e('Order Label', 'user-manager'); ?></label>
							<input type="text" id="um-invoice-order-label" name="invoice_order_label" class="regular-text" value="<?php echo esc_attr($order_label); ?>"<?php echo $form_attr; ?> />
						</div>
						<div class="um-form-field">
							<label for="um-invoice-scrollable-threshold"><?php esc_html_e('Scrollable Items Threshold', 'user-manager'); ?></label>
							<input type="text" id="um-invoice-scrollable-threshold" name="invoice_scrollable_items_threshold" class="small-text" value="<?php echo esc_attr($scrollable_threshold); ?>"<?php echo $form_attr; ?> />
						</div>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="invoice_show_hidden_meta_fields" value="1" <?php checked(!empty($settings['invoice_show_hidden_meta_fields'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Show Hidden Meta Fields', 'user-manager'); ?>
						</label>
					</div>
					<div class="um-form-field">
						<label>
							<input type="checkbox" name="invoice_enable_enhancements" value="1" <?php checked(!empty($settings['invoice_enable_enhancements'])); ?><?php echo $form_attr; ?> />
							<?php esc_html_e('Enable Invoice Enhancements', 'user-manager'); ?>
						</label>
						<p class="description"><?php esc_html_e('Enable guest-payable invoices and inline billing/shipping address edit form on invoice pages.', 'user-manager'); ?></p>
					</div>

					<hr style="margin:16px 0;" />
					<h3 style="margin:0 0 8px;"><?php esc_html_e('Order Invoice & Approval Settings', 'user-manager'); ?></h3>
					<div class="um-form-field">
						<label for="um-invoice-approval-emails"><?php esc_html_e('Email Addresses to Enable Order Invoice & Approval', 'user-manager'); ?></label>
						<textarea id="um-invoice-approval-emails" name="invoice_approval_emails" rows="4" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea($approval_emails); ?></textarea>
						<p class="description"><?php esc_html_e('One email per line. If invoice billing email matches, approval form appears. Alternative access can be granted per user via Edit User screen checkbox.', 'user-manager'); ?></p>
						<?php if (!empty($allowed_invoice_approval_emails)) : ?>
							<p class="description" style="margin-top:8px;"><strong><?php esc_html_e('Currently allowed emails (global list + enabled user checkboxes):', 'user-manager'); ?></strong></p>
							<ul style="margin:4px 0 0 18px; list-style:disc;">
								<?php foreach ($allowed_invoice_approval_emails as $email_row) : ?>
									<?php
									$row_email = isset($email_row['email']) ? (string) $email_row['email'] : '';
									$row_sources = isset($email_row['sources']) && is_array($email_row['sources']) ? array_values($email_row['sources']) : [];
									$row_user_id = isset($email_row['user_id']) ? absint($email_row['user_id']) : 0;
									$edit_url = $row_user_id > 0 ? get_edit_user_link($row_user_id) : '';
									?>
									<li style="margin:2px 0;">
										<code><?php echo esc_html($row_email); ?></code>
										<?php if (!empty($row_sources)) : ?>
											<em style="color:#555;">(<?php echo esc_html(implode(' + ', $row_sources)); ?>)</em>
										<?php endif; ?>
										<?php if (!empty($edit_url)) : ?>
											- <a href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit User', 'user-manager'); ?></a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p class="description" style="margin-top:8px;"><?php esc_html_e('No emails are currently allowed. Add emails above or enable user-level access via the Edit User checkbox.', 'user-manager'); ?></p>
						<?php endif; ?>
					</div>
					<div class="um-form-field">
						<label for="um-invoice-approval-title"><?php esc_html_e('Order Invoice & Approval Form Title', 'user-manager'); ?></label>
						<input type="text" id="um-invoice-approval-title" name="invoice_approval_title" class="regular-text" value="<?php echo esc_attr($approval_title); ?>"<?php echo $form_attr; ?> />
					</div>
					<div class="um-form-field">
						<label for="um-invoice-approval-checkbox-text"><?php esc_html_e('Order Invoice & Approval Checkbox Text', 'user-manager'); ?></label>
						<textarea id="um-invoice-approval-checkbox-text" name="invoice_approval_checkbox_text" rows="4" class="large-text"<?php echo $form_attr; ?>><?php echo esc_textarea($approval_checkbox_text); ?></textarea>
					</div>
					<div class="um-form-field">
						<label for="um-invoice-approval-button-text"><?php esc_html_e('Order Invoice & Approval Button Text', 'user-manager'); ?></label>
						<input type="text" id="um-invoice-approval-button-text" name="invoice_approval_button_text" class="regular-text" value="<?php echo esc_attr($approval_button_text); ?>"<?php echo $form_attr; ?> />
					</div>

					<div class="um-form-field">
						<label class="um-label-block"><?php esc_html_e('Invoice URL pattern', 'user-manager'); ?></label>
						<input type="text" readonly class="large-text code" value="<?php echo esc_attr($site . '?invoice=[trimmed_order_key]'); ?>" onclick="this.select();" />
						<p class="description"><?php esc_html_e('Order list actions and order editor tools will provide working invoice links automatically.', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

