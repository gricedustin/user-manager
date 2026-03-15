<?php
/**
 * Order Invoice & Approval helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_Invoice_Approval_Trait {

	/**
	 * Register invoice approval hooks when add-on is enabled.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_invoice_approval(array $settings): void {
		if (empty($settings['invoice_approval_enabled'])) {
			return;
		}

		add_action('admin_notices', [__CLASS__, 'render_invoice_approval_admin_notice']);
		add_action('add_meta_boxes', [__CLASS__, 'register_invoice_meta_box']);
		add_action('save_post_shop_order', [__CLASS__, 'save_invoice_meta_box']);
		add_filter('woocommerce_admin_order_actions', [__CLASS__, 'add_invoice_order_action'], 10, 2);
		add_action('admin_footer', [__CLASS__, 'render_invoice_inline_admin_link']);
		add_action('template_redirect', [__CLASS__, 'maybe_render_invoice_page']);

		add_action('show_user_profile', [__CLASS__, 'render_invoice_approval_profile_fields']);
		add_action('edit_user_profile', [__CLASS__, 'render_invoice_approval_profile_fields']);
		add_action('personal_options_update', [__CLASS__, 'save_invoice_approval_profile_fields']);
		add_action('edit_user_profile_update', [__CLASS__, 'save_invoice_approval_profile_fields']);
	}

	/**
	 * Render admin notice on order edit when invoice is approved.
	 */
	public static function render_invoice_approval_admin_notice(): void {
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (!$screen || $screen->id !== 'shop_order' || $screen->base !== 'post') {
			return;
		}

		$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
		if ($post_id <= 0 || !function_exists('wc_get_order')) {
			return;
		}

		$order = wc_get_order($post_id);
		if (!$order) {
			return;
		}

		$approval_data = $order->get_meta('_um_invoice_approval');
		if (!is_array($approval_data) || empty($approval_data)) {
			return;
		}

		$name = isset($approval_data['name']) ? (string) $approval_data['name'] : '';
		$email = isset($approval_data['email']) ? (string) $approval_data['email'] : '';
		$date_display = isset($approval_data['date_formatted']) ? (string) $approval_data['date_formatted'] : '';
		if ($date_display === '' && !empty($approval_data['timestamp'])) {
			$date_display = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime((string) $approval_data['timestamp']));
		}
		if ($date_display === '') {
			$date_display = __('Unknown date', 'user-manager');
		}

		if ($name === '' && $email === '') {
			return;
		}

		$message = sprintf(
			/* translators: 1: approver name, 2: approver email, 3: date/time */
			__('Approved: This invoice was approved by %1$s (%2$s) on %3$s.', 'user-manager'),
			esc_html($name),
			esc_html($email),
			esc_html($date_display)
		);
		echo '<div class="notice notice-success is-dismissible"><p>✓ ' . $message . '</p></div>';
	}

	/**
	 * Register order-side invoice meta box.
	 */
	public static function register_invoice_meta_box(): void {
		add_meta_box(
			'um_invoice_metabox',
			__('Invoice', 'user-manager'),
			[__CLASS__, 'render_invoice_meta_box'],
			'shop_order',
			'side',
			'default'
		);
	}

	/**
	 * Render order-side invoice meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function render_invoice_meta_box($post): void {
		if (!$post || $post->post_type !== 'shop_order') {
			return;
		}

		$title = (string) get_post_meta($post->ID, '_um_invoice_title', true);
		$order_header_note = (string) get_post_meta($post->ID, '_um_invoice_header_note', true);
		$order_footer_note = (string) get_post_meta($post->ID, '_um_invoice_footer_note', true);

		echo '<p><label for="um_invoice_title"><strong>' . esc_html__('Invoice Title', 'user-manager') . '</strong></label>';
		echo '<input type="text" style="width:100%;" id="um_invoice_title" name="um_invoice_title" value="' . esc_attr($title) . '" /></p>';
		echo '<p class="description">' . esc_html__('Optional title shown on the invoice page.', 'user-manager') . '</p>';

		echo '<p><label for="um_invoice_header_note"><strong>' . esc_html__('Order Header Note', 'user-manager') . '</strong></label><br />';
		echo '<textarea id="um_invoice_header_note" name="um_invoice_header_note" rows="3" style="width:100%;">' . esc_textarea($order_header_note) . '</textarea></p>';
		echo '<p class="description">' . esc_html__('Shown below global header note for this order only.', 'user-manager') . '</p>';

		echo '<p><label for="um_invoice_footer_note"><strong>' . esc_html__('Order Footer Note', 'user-manager') . '</strong></label><br />';
		echo '<textarea id="um_invoice_footer_note" name="um_invoice_footer_note" rows="3" style="width:100%;">' . esc_textarea($order_footer_note) . '</textarea></p>';
		echo '<p class="description">' . esc_html__('Shown below global footer note for this order only.', 'user-manager') . '</p>';

		if (function_exists('wc_get_order')) {
			$order = wc_get_order($post->ID);
			if ($order) {
				$key = self::invoice_get_trimmed_order_key($order);
				$invoice_url = add_query_arg(['invoice' => rawurlencode($key)], home_url('/'));
				echo '<p><a href="' . esc_url($invoice_url) . '" class="button" target="_blank" rel="noopener">' . esc_html__('View Invoice', 'user-manager') . '</a></p>';
				$logged_out_views = (int) get_post_meta($post->ID, '_um_invoice_logged_out_views', true);
				echo '<p><strong>' . esc_html__('Logged Out View Count:', 'user-manager') . '</strong> ' . esc_html((string) $logged_out_views) . '</p>';
			}
		}

		wp_nonce_field('um_invoice_meta_save', 'um_invoice_meta_nonce');
	}

	/**
	 * Save order invoice meta box values.
	 */
	public static function save_invoice_meta_box(int $post_id): void {
		if (!isset($_POST['um_invoice_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['um_invoice_meta_nonce'])), 'um_invoice_meta_save')) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$title = isset($_POST['um_invoice_title']) ? sanitize_text_field(wp_unslash($_POST['um_invoice_title'])) : '';
		$header_note = isset($_POST['um_invoice_header_note']) ? wp_kses_post(wp_unslash($_POST['um_invoice_header_note'])) : '';
		$footer_note = isset($_POST['um_invoice_footer_note']) ? wp_kses_post(wp_unslash($_POST['um_invoice_footer_note'])) : '';

		update_post_meta($post_id, '_um_invoice_title', $title);
		update_post_meta($post_id, '_um_invoice_header_note', $header_note);
		update_post_meta($post_id, '_um_invoice_footer_note', $footer_note);
	}

	/**
	 * Add "Invoice" action to WooCommerce admin order actions.
	 *
	 * @param array<string,mixed> $actions
	 * @param WC_Order $order
	 * @return array<string,mixed>
	 */
	public static function add_invoice_order_action(array $actions, $order): array {
		if (!$order || !is_a($order, 'WC_Order')) {
			return $actions;
		}

		$key = self::invoice_get_trimmed_order_key($order);
		$url = add_query_arg(['invoice' => rawurlencode($key)], home_url('/'));
		$actions['um_invoice'] = [
			'url' => esc_url($url),
			'name' => __('Invoice', 'user-manager'),
			'action' => 'view invoice',
			'target' => '_blank',
		];
		return $actions;
	}

	/**
	 * Add inline "Invoice →" link near customer payment page link on order editor.
	 */
	public static function render_invoice_inline_admin_link(): void {
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (!$screen || $screen->id !== 'shop_order' || $screen->base !== 'post') {
			return;
		}
		$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
		if ($post_id <= 0 || !function_exists('wc_get_order')) {
			return;
		}
		$order = wc_get_order($post_id);
		if (!$order) {
			return;
		}
		$key = self::invoice_get_trimmed_order_key($order);
		$url = add_query_arg(['invoice' => rawurlencode($key)], home_url('/'));
		?>
		<script>
		(function() {
			function addInlineInvoiceLink() {
				var containers = document.querySelectorAll('#order_data, .panel-wrap, .order_data_column, .wc-order-data-row, .order_data_column_container');
				var target = null;
				containers.forEach(function(container) {
					if (target) {
						return;
					}
					var links = container.querySelectorAll('a');
					links.forEach(function(link) {
						var text = (link.textContent || '').trim().toLowerCase();
						if (text.indexOf('customer payment page') === 0) {
							target = link;
						}
					});
				});
				if (!target || document.querySelector('.um-view-invoice-inline')) {
					return;
				}
				var invoiceLink = document.createElement('a');
				invoiceLink.href = <?php echo wp_json_encode(esc_url($url)); ?>;
				invoiceLink.target = '_blank';
				invoiceLink.rel = 'noopener';
				invoiceLink.textContent = 'Invoice →';
				invoiceLink.className = 'um-view-invoice-inline';
				invoiceLink.style.marginLeft = '6px';
				target.insertAdjacentElement('afterend', invoiceLink);
			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', addInlineInvoiceLink);
			} else {
				addInlineInvoiceLink();
			}
		})();
		</script>
		<?php
	}

	/**
	 * Invoice front-end renderer.
	 */
	public static function maybe_render_invoice_page(): void {
		if (is_admin() || empty($_GET['invoice']) || !function_exists('wc_get_order') || !class_exists('WC_Order')) {
			return;
		}

		$settings = self::get_settings();
		if (empty($settings['invoice_approval_enabled'])) {
			return;
		}

		$short_key = sanitize_text_field(wp_unslash($_GET['invoice']));
		$full_key = strpos($short_key, 'wc_order_') === 0 ? $short_key : ('wc_order_' . $short_key);
		$order_id = 0;
		if (function_exists('wc_get_order_id_by_order_key')) {
			$order_id = (int) wc_get_order_id_by_order_key($full_key);
		}
		if ($order_id <= 0) {
			global $wpdb;
			$order_id = (int) $wpdb->get_var($wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_order_key' AND meta_value = %s LIMIT 1",
				$full_key
			));
		}
		if ($order_id <= 0) {
			return;
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}

		$enable_enhancements = !empty($settings['invoice_enable_enhancements']);
		if ($enable_enhancements) {
			$needs_save = false;
			if ($order->get_meta('_cart_to_invoice', true) !== 'true') {
				$order->update_meta_data('_cart_to_invoice', 'true');
				$needs_save = true;
			}
			if ((int) $order->get_customer_id() > 0) {
				$order->set_customer_id(0);
				$needs_save = true;
			}
			if ($needs_save) {
				$order->save();
			}
		}

		// Handle inline address updates.
		if ($enable_enhancements && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['um_invoice_address_update'])) {
			$nonce = isset($_POST['um_invoice_address_nonce']) ? sanitize_text_field(wp_unslash($_POST['um_invoice_address_nonce'])) : '';
			if ($nonce !== '' && wp_verify_nonce($nonce, 'um_invoice_address_update_' . $order_id)) {
				$billing_fields = [
					'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country',
				];
				foreach ($billing_fields as $field) {
					$raw = isset($_POST['billing_' . $field]) ? wp_unslash($_POST['billing_' . $field]) : '';
					$val = sanitize_text_field($raw);
					$billing_setter = 'set_billing_' . $field;
					if (method_exists($order, $billing_setter)) {
						$order->{$billing_setter}($val);
					}
				}

				$shipping_same = !empty($_POST['shipping_same_as_billing']);
				if ($shipping_same) {
					foreach ($billing_fields as $field) {
						$getter = 'get_billing_' . $field;
						$setter = 'set_shipping_' . $field;
						if (method_exists($order, $getter) && method_exists($order, $setter)) {
							$order->{$setter}($order->{$getter}());
						}
					}
				} else {
					foreach ($billing_fields as $field) {
						$raw = isset($_POST['shipping_' . $field]) ? wp_unslash($_POST['shipping_' . $field]) : '';
						$val = sanitize_text_field($raw);
						$shipping_setter = 'set_shipping_' . $field;
						if (method_exists($order, $shipping_setter)) {
							$order->{$shipping_setter}($val);
						}
					}
				}

				$order->save();
				wp_safe_redirect(esc_url_raw(add_query_arg(['invoice' => rawurlencode($short_key)], home_url('/'))));
				exit;
			}
		}

		// Handle invoice approval submission.
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['um_invoice_approval'])) {
			$nonce = isset($_POST['um_invoice_approval_nonce']) ? sanitize_text_field(wp_unslash($_POST['um_invoice_approval_nonce'])) : '';
			if ($nonce !== '' && wp_verify_nonce($nonce, 'um_invoice_approval_' . $order_id)) {
				$approver_name = isset($_POST['approver_name']) ? sanitize_text_field(wp_unslash($_POST['approver_name'])) : '';
				$approver_email = isset($_POST['approver_email']) ? sanitize_email(wp_unslash($_POST['approver_email'])) : '';
				$approval_checked = isset($_POST['approval_checkbox']) && $_POST['approval_checkbox'] === '1';
				if ($approver_name !== '' && $approver_email !== '' && is_email($approver_email) && $approval_checked) {
					$approval_data = [
						'name' => $approver_name,
						'email' => $approver_email,
						'timestamp' => current_time('mysql'),
						'date_formatted' => current_time(get_option('date_format') . ' ' . get_option('time_format')),
					];
					$order->update_meta_data('_um_invoice_approval', $approval_data);
					$order->set_status('processing', __('Invoice approved by customer.', 'user-manager'));
					$order->save();
					wp_safe_redirect(esc_url_raw(add_query_arg(['invoice' => rawurlencode($short_key)], home_url('/'))));
					exit;
				}
			}
		}

		if (!current_user_can('manage_woocommerce')) {
			$expected_key = self::invoice_get_trimmed_order_key($order);
			$is_valid = function_exists('hash_equals') ? hash_equals($expected_key, $short_key) : ($expected_key === $short_key);
			if (!$is_valid) {
				wp_die(esc_html__('Unauthorized invoice access.', 'user-manager'));
			}
		}

		$pdf_mode = isset($_GET['pdf']) && $_GET['pdf'] === '1';
		$html = self::build_invoice_html($order, $settings, $pdf_mode);

		if ($pdf_mode) {
			$filename = 'invoice-' . $order->get_order_number() . '.pdf';
			$generated = false;
			if (class_exists('\Dompdf\Dompdf')) {
				try {
					$dompdf = new \Dompdf\Dompdf();
					$dompdf->loadHtml($html);
					$dompdf->setPaper('A4', 'portrait');
					$dompdf->render();
					header('Content-Type: application/pdf');
					header('Content-Disposition: attachment; filename="' . $filename . '"');
					echo $dompdf->output();
					$generated = true;
				} catch (\Throwable $e) {
					$generated = false;
				}
			}
			if ($generated) {
				exit;
			}

			echo str_replace('</body>', '<script>window.addEventListener("load",function(){window.print();});</script></body>', $html);
			exit;
		}

		if (!is_user_logged_in()) {
			$count = (int) get_post_meta($order->get_id(), '_um_invoice_logged_out_views', true);
			update_post_meta($order->get_id(), '_um_invoice_logged_out_views', $count + 1);
		}

		echo $html;
		exit;
	}

	/**
	 * Render checkbox on edit user/profile to allow invoice approvals.
	 *
	 * @param WP_User $user User object.
	 */
	public static function render_invoice_approval_profile_fields($user): void {
		if (!current_user_can('manage_options')) {
			return;
		}

		$enabled = get_user_meta($user->ID, '_um_invoice_approval_enabled', true) === '1';
		?>
		<div class="card" style="max-width:100%;margin-bottom:20px;padding:20px;background:#fff;border:1px solid #ccd0d4;box-shadow:0 1px 1px rgba(0,0,0,.04);">
			<h2 style="margin-top:0;padding-bottom:10px;border-bottom:1px solid #eee;">
				<span class="dashicons dashicons-media-spreadsheet" style="margin-right:5px;"></span>
				<?php esc_html_e('Order Invoice & Approval Access', 'user-manager'); ?>
			</h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e('Allow Order Invoice & Approval by Email', 'user-manager'); ?></th>
					<td>
						<label>
							<input type="checkbox" name="um_invoice_approval_enabled" value="1" <?php checked($enabled); ?> />
							<?php esc_html_e('Enable order invoice & approval access for this user email', 'user-manager'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('If checked, invoices whose billing email matches this user email will show the invoice approval form, even if not listed in the global "Email Addresses to Enable Order Invoice & Approval" setting.', 'user-manager'); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Save user profile checkbox for invoice approval access.
	 */
	public static function save_invoice_approval_profile_fields(int $user_id): void {
		if (!current_user_can('manage_options')) {
			return;
		}
		$enabled = !empty($_POST['um_invoice_approval_enabled']) ? '1' : '0';
		update_user_meta($user_id, '_um_invoice_approval_enabled', $enabled);
	}

	/**
	 * Build invoice HTML output.
	 *
	 * @param array<string,mixed> $settings
	 */
	private static function build_invoice_html($order, array $settings, bool $pdf_mode): string {
		$primary = self::invoice_setting($settings, 'invoice_primary_color', '#4B2E83');
		$btn_bg = self::invoice_setting($settings, 'invoice_button_color', $primary);
		$btn_fg = self::invoice_setting($settings, 'invoice_button_text_color', '#ffffff');
		$font = self::invoice_setting($settings, 'invoice_font_family', 'Poppins, sans-serif');
		$logo = self::invoice_setting($settings, 'invoice_logo_url', '');
		$logo_width = self::invoice_setting($settings, 'invoice_logo_max_width', '160px');
		$company_name = self::invoice_setting($settings, 'invoice_company_name', get_bloginfo('name'));
		$company_address = self::invoice_setting($settings, 'invoice_company_address', '');
		$company_email = self::invoice_setting($settings, 'invoice_company_email', get_bloginfo('admin_email'));
		$company_phone = self::invoice_setting($settings, 'invoice_company_phone', '');
		$header_note = self::invoice_setting($settings, 'invoice_header_note', '');
		$footer_note = self::invoice_setting($settings, 'invoice_footer_note', '');
		$footer_below = self::invoice_setting($settings, 'invoice_footer_note_below_buttons', '');
		$order_label = self::invoice_setting($settings, 'invoice_order_label', 'Order');
		$hide_logo_pdf = !empty($settings['invoice_hide_logo_in_pdf']);
		$hide_buttons_pdf = !empty($settings['invoice_hide_buttons_in_pdf']);
		$show_hidden_meta = !empty($settings['invoice_show_hidden_meta_fields']);
		$enable_enhancements = !empty($settings['invoice_enable_enhancements']);
		$scroll_threshold = absint(self::invoice_setting($settings, 'invoice_scrollable_items_threshold', '0'));
		$approval_title = self::invoice_setting($settings, 'invoice_approval_title', 'Approve & Pay Later');
		$approval_checkbox_text = self::invoice_setting($settings, 'invoice_approval_checkbox_text', 'I hereby approve of placing this order and authorize payment for this order.');
		$approval_button_text = self::invoice_setting($settings, 'invoice_approval_button_text', 'Send to Production');

		$invoice_title = (string) get_post_meta($order->get_id(), '_um_invoice_title', true);
		$order_header_note = (string) get_post_meta($order->get_id(), '_um_invoice_header_note', true);
		$order_footer_note = (string) get_post_meta($order->get_id(), '_um_invoice_footer_note', true);
		$approval_data = $order->get_meta('_um_invoice_approval');
		$has_approval = is_array($approval_data) && !empty($approval_data);

		$billing_email = $order->get_billing_email();
		$approval_enabled = self::invoice_is_approval_enabled_for_email((string) $billing_email, $settings);

		$order_number = $order->get_order_number();
		$issued_date = wc_format_datetime($order->get_date_created(), get_option('date_format'));
		$items = $order->get_items();
		$item_count = count($items);
		$currency = $order->get_currency();

		$checkout_base = remove_query_arg(['store', 'brand'], wc_get_checkout_url());
		$pay_url = wc_get_endpoint_url('order-pay', $order->get_id(), $checkout_base);
		$pay_url = add_query_arg([
			'pay_for_order' => 'true',
			'key' => $order->get_order_key(),
		], $pay_url);
		$pay_url = remove_query_arg(['store', 'brand'], $pay_url);

		$pdf_url = add_query_arg([
			'invoice' => rawurlencode(self::invoice_get_trimmed_order_key($order)),
			'pdf' => '1',
		], home_url('/'));

		$billing_name = $order->get_formatted_billing_full_name();
		$billing_phone = $order->get_billing_phone();
		$billing_address_lines = [];
		foreach ([$order->get_billing_address_1(), $order->get_billing_address_2()] as $line) {
			if ((string) $line !== '') {
				$billing_address_lines[] = (string) $line;
			}
		}
		$city_state_zip = trim(implode(' ', array_filter([
			trim((string) $order->get_billing_city() . (((string) $order->get_billing_state() !== '') ? ', ' . (string) $order->get_billing_state() : '')),
			(string) $order->get_billing_postcode(),
		])));
		if ($city_state_zip !== '') {
			$billing_address_lines[] = $city_state_zip;
		}
		$billing_address_html = implode('<br>', array_map('esc_html', $billing_address_lines));

		ob_start();
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo esc_html($invoice_title !== '' ? $invoice_title : ($order_label . ' #' . $order_number)); ?></title>
		<style>
		body{font-family:<?php echo esc_html($font); ?>;margin:0;padding:0;color:#1a1a1a;background:#fff;}
		.invoice{max-width:1000px;margin:0 auto;overflow-x:auto;}
		.header{background:<?php echo esc_html($primary); ?>;color:#fff;padding:30px;}
		.header .logo img{max-width:<?php echo esc_html($logo_width); ?>;height:auto;}
		.content{padding:30px;}
		.info-grid{display:flex;gap:24px;flex-wrap:wrap;}
		.info-block{flex:1 1 320px;}
		table{width:100%;border-collapse:collapse;margin-bottom:20px;}
		th,td{padding:10px 8px;border-bottom:1px solid #ddd;vertical-align:top;text-align:left;}
		th:nth-child(3),td:nth-child(3){white-space:nowrap;}
		.text-right{text-align:right;}
		.total{font-weight:700;}
		.button{background:<?php echo esc_html($btn_bg); ?>;color:<?php echo esc_html($btn_fg); ?>;text-decoration:none;display:inline-block;padding:10px 20px;border-radius:6px;margin:10px 8px 10px 0;font-weight:600;}
		.invoice-items-scrollable{max-height:600px;overflow:auto;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:0;}
		.invoice-items-scrollable table{margin-bottom:0;min-width:600px;}
		.invoice-items-scrollable thead{position:sticky;top:0;background:#fff;z-index:5;}
		.invoice-approval-form{margin:24px 0;padding:18px;border:2px solid #ddd;border-radius:6px;background:#f9f9f9;}
		.invoice-approval-form input[type="text"], .invoice-approval-form input[type="email"]{width:100%;max-width:420px;padding:8px;border:1px solid #ccc;border-radius:4px;}
		.invoice-approval-notification{background:#d4edda;border:1px solid #c3e6cb;border-radius:6px;padding:14px;margin:16px 0;color:#155724;}
		.invoice-address-edit{margin:16px 0 20px;}
		.invoice-address-edit__panel{display:none;margin-top:8px;padding:12px;border:1px solid #ddd;border-radius:6px;background:#fcfcfc;}
		.invoice-address-edit__grid{display:flex;gap:16px;flex-wrap:wrap;}
		.invoice-address-edit__column{flex:1 1 320px;}
		.invoice-address-edit__field{margin-bottom:8px;}
		.invoice-address-edit__field input{width:100%;padding:7px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;}
		@media (max-width:700px){.content,.header{padding:18px;}.info-grid{display:block;}}
		</style>
		</head>
		<body>
		<div class="invoice">
			<div class="header">
				<div class="logo">
					<?php if ($logo !== '' && !($pdf_mode && $hide_logo_pdf)) : ?>
						<img src="<?php echo esc_url($logo); ?>" alt="">
					<?php else : ?>
						<strong><?php echo esc_html(get_bloginfo('name')); ?></strong>
					<?php endif; ?>
				</div>
			</div>
			<div class="content">
				<?php if ($has_approval && !$pdf_mode) : ?>
					<div class="invoice-approval-notification">
						<strong>✓ <?php esc_html_e('Approved:', 'user-manager'); ?></strong>
						<?php
						$name = isset($approval_data['name']) ? (string) $approval_data['name'] : '';
						$email = isset($approval_data['email']) ? (string) $approval_data['email'] : '';
						$date = isset($approval_data['date_formatted']) ? (string) $approval_data['date_formatted'] : '';
						echo esc_html(sprintf(__('This invoice was approved by %1$s (%2$s) on %3$s.', 'user-manager'), $name, $email, $date));
						?>
					</div>
				<?php endif; ?>

				<?php if ($invoice_title !== '') : ?>
					<h2 style="margin:0 0 6px;"><?php echo esc_html($invoice_title); ?></h2>
				<?php endif; ?>
				<div><strong><?php echo esc_html($order_label); ?> #<?php echo esc_html($order_number); ?></strong></div>
				<div style="margin-bottom:12px;"><?php echo esc_html($issued_date); ?></div>

				<div class="info-grid">
					<div class="info-block">
						<strong><?php echo esc_html($billing_name); ?></strong><br>
						<?php echo wp_kses_post($billing_address_html); ?>
						<?php if ($billing_phone !== '') : ?><br><?php echo esc_html($billing_phone); ?><?php endif; ?>
						<?php if ($billing_email !== '') : ?><br><a href="mailto:<?php echo esc_attr($billing_email); ?>"><?php echo esc_html($billing_email); ?></a><?php endif; ?>
					</div>
					<div class="info-block">
						<?php if ($company_name !== '') : ?><strong><?php echo esc_html($company_name); ?></strong><br><?php endif; ?>
						<?php if ($company_address !== '') : ?><?php echo nl2br(esc_html($company_address)); ?><br><?php endif; ?>
						<?php if ($company_phone !== '') : ?><?php echo esc_html($company_phone); ?><br><?php endif; ?>
						<?php if ($company_email !== '') : ?><a href="mailto:<?php echo esc_attr($company_email); ?>"><?php echo esc_html($company_email); ?></a><?php endif; ?>
					</div>
				</div>

				<?php if ($header_note !== '' || $order_header_note !== '') : ?>
					<div style="margin:14px 0;">
						<?php if ($header_note !== '') : ?><p><?php echo nl2br(esc_html($header_note)); ?></p><?php endif; ?>
						<?php if ($order_header_note !== '') : ?><p><?php echo nl2br(esc_html($order_header_note)); ?></p><?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ($enable_enhancements && !$pdf_mode) : ?>
					<?php self::render_invoice_address_editor($order); ?>
				<?php endif; ?>

				<h3><?php esc_html_e('Products & Services', 'user-manager'); ?></h3>
				<?php if ($scroll_threshold > 0 && $item_count > $scroll_threshold) : ?><div class="invoice-items-scrollable"><?php endif; ?>
				<table>
					<thead>
						<tr>
							<th><?php esc_html_e('Item & Description', 'user-manager'); ?></th>
							<th><?php esc_html_e('Quantity', 'user-manager'); ?></th>
							<th><?php esc_html_e('Unit Price', 'user-manager'); ?></th>
							<th class="text-right"><?php esc_html_e('Total', 'user-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($items as $item_id => $item) : ?>
						<?php
						$product = $item->get_product();
						$name = $item->get_name();
						$qty = (int) $item->get_quantity();
						$line_subtotal = (float) $item->get_subtotal();
						$unit_price = $qty > 0 ? $line_subtotal / $qty : 0;
						$formatted_meta = method_exists($item, 'get_formatted_meta_data') ? $item->get_formatted_meta_data('', true) : [];
						if (!$show_hidden_meta && is_array($formatted_meta)) {
							$formatted_meta = array_values(array_filter($formatted_meta, static function ($meta) {
								$key = is_object($meta) && isset($meta->key) ? (string) $meta->key : '';
								return $key === '' || strpos($key, '_') !== 0;
							}));
						}
						$thumb = $product ? wp_get_attachment_image_src($product->get_image_id(), 'thumbnail') : [];
						$thumb_url = !empty($thumb[0]) ? $thumb[0] : '';
						?>
						<tr>
							<td>
								<?php if ($thumb_url !== '') : ?><img src="<?php echo esc_url($thumb_url); ?>" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #ddd;float:left;margin-right:10px;" alt=""><?php endif; ?>
								<div style="overflow:hidden;"><?php echo esc_html($name); ?></div>
								<?php if (!empty($formatted_meta)) : ?>
									<div style="font-size:0.86em;color:#555;margin-top:4px;">
										<?php foreach ($formatted_meta as $meta) : ?>
											<?php
											$display_key = isset($meta->display_key) ? (string) $meta->display_key : '';
											$display_val = isset($meta->display_value) ? (string) $meta->display_value : '';
											?>
											<p style="margin:2px 0;"><strong><?php echo esc_html($display_key); ?>:</strong> <?php echo wp_kses_post($display_val); ?></p>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html((string) $qty); ?></td>
							<td><?php echo wp_kses_post(wc_price($unit_price, ['currency' => $currency])); ?></td>
							<td class="text-right"><?php echo wp_kses_post(wc_price($line_subtotal, ['currency' => $currency])); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php if ($scroll_threshold > 0 && $item_count > $scroll_threshold) : ?></div><?php endif; ?>

				<table>
					<tbody>
						<tr><td colspan="3" class="text-right"><strong><?php esc_html_e('Subtotal', 'user-manager'); ?></strong></td><td class="text-right"><?php echo wp_kses_post(wc_price($order->get_subtotal(), ['currency' => $currency])); ?></td></tr>
						<?php $discount_total = (float) $order->get_discount_total(); ?>
						<?php if ($discount_total > 0) : ?><tr><td colspan="3" class="text-right"><strong><?php esc_html_e('Discounts', 'user-manager'); ?></strong></td><td class="text-right">-<?php echo wp_kses_post(wc_price($discount_total, ['currency' => $currency])); ?></td></tr><?php endif; ?>
						<?php $tax_total = (float) $order->get_total_tax(); ?>
						<?php if ($tax_total > 0) : ?><tr><td colspan="3" class="text-right"><?php esc_html_e('Tax', 'user-manager'); ?></td><td class="text-right"><?php echo wp_kses_post(wc_price($tax_total, ['currency' => $currency])); ?></td></tr><?php endif; ?>
						<?php $shipping_total = (float) $order->get_shipping_total(); ?>
						<?php if ($shipping_total > 0) : ?><tr><td colspan="3" class="text-right"><?php esc_html_e('Shipping', 'user-manager'); ?></td><td class="text-right"><?php echo wp_kses_post(wc_price($shipping_total, ['currency' => $currency])); ?></td></tr><?php endif; ?>
						<tr><td colspan="3" class="text-right total"><?php esc_html_e('Total', 'user-manager'); ?></td><td class="text-right total"><?php echo wp_kses_post(wc_price($order->get_total(), ['currency' => $currency])); ?></td></tr>
					</tbody>
				</table>

				<?php if ($approval_enabled && !$has_approval && !$pdf_mode) : ?>
					<div class="invoice-approval-form">
						<h3><?php echo esc_html($approval_title); ?></h3>
						<form method="post">
							<input type="hidden" name="um_invoice_approval" value="1">
							<input type="hidden" name="um_invoice_approval_nonce" value="<?php echo esc_attr(wp_create_nonce('um_invoice_approval_' . $order->get_id())); ?>">
							<p>
								<label for="approver_name"><strong><?php esc_html_e('Name', 'user-manager'); ?></strong></label><br>
								<input type="text" id="approver_name" name="approver_name" required>
							</p>
							<p>
								<label for="approver_email"><strong><?php esc_html_e('Email Address', 'user-manager'); ?></strong></label><br>
								<input type="email" id="approver_email" name="approver_email" required>
							</p>
							<p>
								<label><input type="checkbox" name="approval_checkbox" value="1" required> <?php echo esc_html($approval_checkbox_text); ?></label>
							</p>
							<p><button type="submit" class="button" style="border:none;cursor:pointer;"><?php echo esc_html($approval_button_text); ?></button></p>
						</form>
					</div>
				<?php endif; ?>

				<div>
					<?php if ($footer_note !== '') : ?><p><?php echo nl2br(esc_html($footer_note)); ?></p><?php endif; ?>
					<?php if ($order_footer_note !== '') : ?><p><?php echo nl2br(esc_html($order_footer_note)); ?></p><?php endif; ?>
					<?php if (!($pdf_mode && $hide_buttons_pdf)) : ?>
						<a href="<?php echo esc_url($pay_url); ?>" class="button" target="_blank" rel="noopener"><?php esc_html_e('Pay Online', 'user-manager'); ?></a>
						<a href="<?php echo esc_url($pdf_url); ?>" class="button"><?php esc_html_e('Download Invoice as PDF', 'user-manager'); ?></a>
					<?php endif; ?>
					<?php if ($footer_below !== '') : ?><p><?php echo nl2br(esc_html($footer_below)); ?></p><?php endif; ?>
				</div>
			</div>
		</div>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var toggle = document.getElementById('um-invoice-edit-toggle');
			var panel = document.getElementById('um-invoice-edit-panel');
			var shipSame = document.querySelector('input[name="shipping_same_as_billing"]');
			var shippingFields = document.getElementById('um-shipping-fields');
			if (toggle && panel) {
				toggle.addEventListener('click', function(e) {
					e.preventDefault();
					panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
				});
			}
			if (shipSame && shippingFields) {
				shipSame.addEventListener('change', function() {
					shippingFields.style.display = this.checked ? 'none' : 'block';
				});
			}
		});
		</script>
		</body>
		</html>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render inline billing/shipping editor block.
	 */
	private static function render_invoice_address_editor($order): void {
		$b_first = $order->get_billing_first_name();
		$b_last = $order->get_billing_last_name();
		$b_company = $order->get_billing_company();
		$b_addr1 = $order->get_billing_address_1();
		$b_addr2 = $order->get_billing_address_2();
		$b_city = $order->get_billing_city();
		$b_state = $order->get_billing_state();
		$b_postcode = $order->get_billing_postcode();
		$b_country = $order->get_billing_country();

		$s_first = $order->get_shipping_first_name();
		$s_last = $order->get_shipping_last_name();
		$s_company = $order->get_shipping_company();
		$s_addr1 = $order->get_shipping_address_1();
		$s_addr2 = $order->get_shipping_address_2();
		$s_city = $order->get_shipping_city();
		$s_state = $order->get_shipping_state();
		$s_postcode = $order->get_shipping_postcode();
		$s_country = $order->get_shipping_country();

		$shipping_same = (
			$s_first === $b_first &&
			$s_last === $b_last &&
			$s_company === $b_company &&
			$s_addr1 === $b_addr1 &&
			$s_addr2 === $b_addr2 &&
			$s_city === $b_city &&
			$s_state === $b_state &&
			$s_postcode === $b_postcode &&
			$s_country === $b_country
		);
		?>
		<div class="invoice-address-edit">
			<a href="#" id="um-invoice-edit-toggle"><?php esc_html_e('Edit Billing / Shipping Address', 'user-manager'); ?></a>
			<div id="um-invoice-edit-panel" class="invoice-address-edit__panel">
				<form method="post">
					<input type="hidden" name="um_invoice_address_update" value="1">
					<input type="hidden" name="um_invoice_address_nonce" value="<?php echo esc_attr(wp_create_nonce('um_invoice_address_update_' . $order->get_id())); ?>">
					<div class="invoice-address-edit__grid">
						<div class="invoice-address-edit__column">
							<strong><?php esc_html_e('Billing Address', 'user-manager'); ?></strong>
							<?php self::render_invoice_address_fields('billing', [
								'first_name' => $b_first,
								'last_name' => $b_last,
								'company' => $b_company,
								'address_1' => $b_addr1,
								'address_2' => $b_addr2,
								'city' => $b_city,
								'state' => $b_state,
								'postcode' => $b_postcode,
								'country' => $b_country,
							]); ?>
						</div>
						<div class="invoice-address-edit__column">
							<p><label><input type="checkbox" name="shipping_same_as_billing" value="1" <?php checked($shipping_same); ?>> <?php esc_html_e('Shipping address is the same as billing', 'user-manager'); ?></label></p>
							<div id="um-shipping-fields" style="<?php echo $shipping_same ? 'display:none;' : 'display:block;'; ?>">
								<strong><?php esc_html_e('Shipping Address', 'user-manager'); ?></strong>
								<?php self::render_invoice_address_fields('shipping', [
									'first_name' => $s_first,
									'last_name' => $s_last,
									'company' => $s_company,
									'address_1' => $s_addr1,
									'address_2' => $s_addr2,
									'city' => $s_city,
									'state' => $s_state,
									'postcode' => $s_postcode,
									'country' => $s_country,
								]); ?>
							</div>
						</div>
					</div>
					<p><button type="submit" class="button"><?php esc_html_e('Save Address', 'user-manager'); ?></button></p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render grouped address inputs.
	 *
	 * @param array<string,string> $values
	 */
	private static function render_invoice_address_fields(string $prefix, array $values): void {
		$labels = [
			'first_name' => __('First Name', 'user-manager'),
			'last_name' => __('Last Name', 'user-manager'),
			'company' => __('Company', 'user-manager'),
			'address_1' => __('Address 1', 'user-manager'),
			'address_2' => __('Address 2', 'user-manager'),
			'city' => __('City', 'user-manager'),
			'state' => __('State', 'user-manager'),
			'postcode' => __('Postal Code', 'user-manager'),
			'country' => __('Country', 'user-manager'),
		];
		foreach ($labels as $field => $label) {
			$id = $prefix . '_' . $field;
			$value = isset($values[$field]) ? $values[$field] : '';
			echo '<div class="invoice-address-edit__field">';
			echo '<label for="' . esc_attr($id) . '">' . esc_html($label) . '</label>';
			echo '<input type="text" id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" value="' . esc_attr($value) . '">';
			echo '</div>';
		}
	}

	/**
	 * Check whether invoice approval is enabled for a billing email.
	 *
	 * @param array<string,mixed> $settings
	 */
	private static function invoice_is_approval_enabled_for_email(string $billing_email, array $settings): bool {
		$billing_email = strtolower(trim($billing_email));
		if ($billing_email === '') {
			return false;
		}

		$list = isset($settings['invoice_approval_emails']) ? (string) $settings['invoice_approval_emails'] : '';
		if ($list !== '') {
			$emails = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $list) ?: []));
			$emails = array_map('strtolower', $emails);
			if (in_array($billing_email, $emails, true)) {
				return true;
			}
		}

		$user = get_user_by('email', $billing_email);
		if ($user && (string) get_user_meta((int) $user->ID, '_um_invoice_approval_enabled', true) === '1') {
			return true;
		}

		return false;
	}

	/**
	 * Resolve invoice setting with fallback.
	 *
	 * @param array<string,mixed> $settings
	 */
	private static function invoice_setting(array $settings, string $key, string $default = ''): string {
		$value = isset($settings[$key]) ? (string) $settings[$key] : '';
		return $value === '' ? $default : $value;
	}

	/**
	 * Convert order key to trimmed key without wc_order_ prefix.
	 */
	private static function invoice_get_trimmed_order_key($order): string {
		$order_key = method_exists($order, 'get_order_key') ? (string) $order->get_order_key() : '';
		return preg_replace('/^wc_order_/', '', $order_key) ?: $order_key;
	}
}

