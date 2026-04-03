<?php
/**
 * Add-on card: Email Log.
 */
if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Emali_Log {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['emali_log_enabled']);
		$auto_delete_days = isset($settings['emali_log_auto_delete_days']) ? max(0, absint($settings['emali_log_auto_delete_days'])) : 0;
		$modal_payload = [];

		$current_status = isset($_GET['emali_log_status']) ? sanitize_key(wp_unslash($_GET['emali_log_status'])) : '';
		if (!in_array($current_status, ['sent', 'failed', 'pending'], true)) {
			$current_status = '';
		}
		$current_search = isset($_GET['emali_log_search']) ? sanitize_text_field(wp_unslash($_GET['emali_log_search'])) : '';
		$current_page = isset($_GET['emali_log_page']) ? max(1, absint($_GET['emali_log_page'])) : 1;
		$preview_id = isset($_GET['emali_log_preview']) ? absint($_GET['emali_log_preview']) : 0;

		$per_page = 25;
		$offset = ($current_page - 1) * $per_page;
		$entries = User_Manager_Core::get_emali_log_entries($per_page, $offset, $current_status, $current_search);
		$total_count = User_Manager_Core::get_emali_log_total_count($current_status, $current_search);
		$total_pages = (int) ceil($total_count / $per_page);
		$stats = User_Manager_Core::get_emali_log_stats();
		$preview_entry = $preview_id > 0 ? User_Manager_Core::get_emali_log_entry($preview_id) : null;

		$base_addons_url = User_Manager_Core::get_page_url(User_Manager_Core::TAB_ADDONS);
		$emali_log_base_url = add_query_arg('addon_section', 'emali-log', $base_addons_url);
		$current_addon_tag = isset($_GET['addon_tag']) ? sanitize_title(wp_unslash($_GET['addon_tag'])) : '';
		if ($current_addon_tag !== '') {
			$emali_log_base_url = add_query_arg('addon_tag', $current_addon_tag, $emali_log_base_url);
		}

		$filter_status_options = [
			'' => __('All statuses', 'user-manager'),
			'sent' => __('Sent', 'user-manager'),
			'failed' => __('Failed', 'user-manager'),
			'pending' => __('Pending', 'user-manager'),
		];
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-emali-log" data-um-active-selectors="#um-emali-log-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-email-alt2"></span>
				<h2><?php esc_html_e('Email Log', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-emali-log-enabled" name="emali_log_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description">
						<?php esc_html_e('Logs all outgoing wp_mail emails with headers, preview, resend, and forwarding tools.', 'user-manager'); ?>
					</p>
				</div>

				<div id="um-emali-log-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<div class="um-admin-card" style="margin-bottom:12px;">
						<div class="um-admin-card-body">
							<div class="um-form-field">
								<label for="um-emali-log-auto-delete-days"><strong><?php esc_html_e('Auto-delete log entries after X days', 'user-manager'); ?></strong></label><br />
								<input type="number" id="um-emali-log-auto-delete-days" min="0" step="1" name="emali_log_auto_delete_days" value="<?php echo esc_attr((string) $auto_delete_days); ?>" <?php echo $form_attr; ?> />
								<p class="description">
									<?php esc_html_e('Set to 0 to keep all entries forever. Any value greater than 0 automatically removes rows older than that many days.', 'user-manager'); ?>
								</p>
							</div>
						</div>
					</div>

					<div class="um-admin-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; margin-bottom:14px;">
						<?php self::render_stat_card(__('Past hour', 'user-manager'), (int) ($stats['hour'] ?? 0)); ?>
						<?php self::render_stat_card(__('Past day', 'user-manager'), (int) ($stats['day'] ?? 0)); ?>
						<?php self::render_stat_card(__('Past week', 'user-manager'), (int) ($stats['week'] ?? 0)); ?>
						<?php self::render_stat_card(__('Past month', 'user-manager'), (int) ($stats['month'] ?? 0)); ?>
						<?php self::render_stat_card(__('Total logged', 'user-manager'), (int) ($stats['total'] ?? 0)); ?>
					</div>

					<form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="margin-bottom:12px;">
						<input type="hidden" name="page" value="<?php echo esc_attr(User_Manager_Core::SETTINGS_PAGE_SLUG); ?>" />
						<input type="hidden" name="tab" value="<?php echo esc_attr(User_Manager_Core::TAB_ADDONS); ?>" />
						<input type="hidden" name="addon_section" value="emali-log" />
						<?php if ($current_addon_tag !== '') : ?>
							<input type="hidden" name="addon_tag" value="<?php echo esc_attr($current_addon_tag); ?>" />
						<?php endif; ?>
						<div style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">
							<div>
								<label for="um-emali-log-status"><strong><?php esc_html_e('Status', 'user-manager'); ?></strong></label><br />
								<select id="um-emali-log-status" name="emali_log_status">
									<?php foreach ($filter_status_options as $status_key => $status_label) : ?>
										<option value="<?php echo esc_attr($status_key); ?>" <?php selected($current_status, $status_key); ?>><?php echo esc_html($status_label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div style="min-width:280px;">
								<label for="um-emali-log-search"><strong><?php esc_html_e('Search', 'user-manager'); ?></strong></label><br />
								<input type="text" class="regular-text" id="um-emali-log-search" name="emali_log_search" value="<?php echo esc_attr($current_search); ?>" placeholder="<?php esc_attr_e('Subject, To, From, headers...', 'user-manager'); ?>" />
							</div>
							<div>
								<?php submit_button(__('Filter', 'user-manager'), 'secondary', 'submit', false); ?>
							</div>
							<div>
								<a class="button" href="<?php echo esc_url($emali_log_base_url); ?>"><?php esc_html_e('Clear', 'user-manager'); ?></a>
							</div>
						</div>
					</form>

					<div style="overflow:auto; border:1px solid #dcdcde; border-radius:6px;">
						<table class="widefat striped" style="min-width:1200px;">
							<thead>
								<tr>
									<th><?php esc_html_e('Date', 'user-manager'); ?></th>
									<th><?php esc_html_e('Status', 'user-manager'); ?></th>
									<th><?php esc_html_e('To', 'user-manager'); ?></th>
									<th><?php esc_html_e('Subject', 'user-manager'); ?></th>
									<th><?php esc_html_e('From', 'user-manager'); ?></th>
									<th><?php esc_html_e('Reply-To', 'user-manager'); ?></th>
									<th><?php esc_html_e('CC', 'user-manager'); ?></th>
									<th><?php esc_html_e('BCC', 'user-manager'); ?></th>
									<th><?php esc_html_e('Content-Type', 'user-manager'); ?></th>
									<th><?php esc_html_e('Actions', 'user-manager'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($entries)) : ?>
									<tr>
										<td colspan="10"><?php esc_html_e('No emails found for current filters.', 'user-manager'); ?></td>
									</tr>
								<?php else : ?>
									<?php foreach ($entries as $entry) : ?>
										<?php
										$entry_id = isset($entry['id']) ? absint($entry['id']) : 0;
										$created_at = isset($entry['created_at']) ? (string) $entry['created_at'] : '';
										$status = isset($entry['status']) ? (string) $entry['status'] : 'pending';
										$to = self::decode_json_array($entry['to_recipients'] ?? '');
										$subject = isset($entry['subject']) ? (string) $entry['subject'] : '';
										$from_header = isset($entry['from_header']) ? (string) $entry['from_header'] : '';
										$reply_to_header = isset($entry['reply_to_header']) ? (string) $entry['reply_to_header'] : '';
										$cc_header = isset($entry['cc_header']) ? (string) $entry['cc_header'] : '';
										$bcc_header = isset($entry['bcc_header']) ? (string) $entry['bcc_header'] : '';
										$content_type = isset($entry['content_type']) ? (string) $entry['content_type'] : '';
										$raw_message = isset($entry['message']) ? (string) $entry['message'] : '';
										if ($entry_id > 0) {
											$modal_payload[(string) $entry_id] = [
												'subject' => $subject,
												'html'    => $raw_message,
											];
										}
										$preview_url = add_query_arg([
											'emali_log_preview' => $entry_id,
											'emali_log_page' => $current_page,
											'emali_log_status' => $current_status,
											'emali_log_search' => $current_search,
										], $emali_log_base_url);
										?>
										<tr>
											<td><?php echo esc_html($created_at); ?></td>
											<td>
												<strong><?php echo esc_html(ucfirst($status)); ?></strong>
												<?php if ($status === 'failed' && !empty($entry['error_message'])) : ?>
													<div class="description" style="margin-top:4px;"><?php echo esc_html((string) $entry['error_message']); ?></div>
												<?php endif; ?>
											</td>
											<td><?php echo esc_html(implode(', ', $to)); ?></td>
											<td><?php echo esc_html($subject); ?></td>
											<td><?php echo esc_html($from_header); ?></td>
											<td><?php echo esc_html($reply_to_header); ?></td>
											<td><?php echo esc_html($cc_header); ?></td>
											<td><?php echo esc_html($bcc_header); ?></td>
											<td><?php echo esc_html($content_type); ?></td>
											<td>
												<div style="display:flex; flex-direction:column; gap:6px; min-width:220px;">
													<a class="button button-small" href="<?php echo esc_url($preview_url); ?>"><?php esc_html_e('View Email', 'user-manager'); ?></a>
													<button type="button" class="button button-small um-email-log-open-html-modal" data-log-id="<?php echo esc_attr((string) $entry_id); ?>"><?php esc_html_e('Open HTML Modal', 'user-manager'); ?></button>
													<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex; gap:6px; flex-wrap:wrap;">
														<input type="hidden" name="action" value="user_manager_emali_log_resend" />
														<input type="hidden" name="log_id" value="<?php echo esc_attr((string) $entry_id); ?>" />
														<input type="hidden" name="addon_section" value="emali-log" />
														<input type="hidden" name="addon_tag" value="<?php echo esc_attr($current_addon_tag); ?>" />
														<input type="hidden" name="emali_log_status" value="<?php echo esc_attr($current_status); ?>" />
														<input type="hidden" name="emali_log_search" value="<?php echo esc_attr($current_search); ?>" />
														<input type="hidden" name="emali_log_page" value="<?php echo esc_attr((string) $current_page); ?>" />
														<?php wp_nonce_field('user_manager_emali_log_resend_' . $entry_id); ?>
														<button type="submit" class="button button-small"><?php esc_html_e('Resend', 'user-manager'); ?></button>
													</form>
													<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex; gap:6px; flex-wrap:wrap;">
														<input type="hidden" name="action" value="user_manager_emali_log_forward" />
														<input type="hidden" name="log_id" value="<?php echo esc_attr((string) $entry_id); ?>" />
														<input type="hidden" name="addon_section" value="emali-log" />
														<input type="hidden" name="addon_tag" value="<?php echo esc_attr($current_addon_tag); ?>" />
														<input type="hidden" name="emali_log_status" value="<?php echo esc_attr($current_status); ?>" />
														<input type="hidden" name="emali_log_search" value="<?php echo esc_attr($current_search); ?>" />
														<input type="hidden" name="emali_log_page" value="<?php echo esc_attr((string) $current_page); ?>" />
														<?php wp_nonce_field('user_manager_emali_log_forward_' . $entry_id); ?>
														<input type="email" class="regular-text" name="forward_email" placeholder="<?php esc_attr_e('Forward to email', 'user-manager'); ?>" style="max-width:170px;" required />
														<button type="submit" class="button button-small"><?php esc_html_e('Forward', 'user-manager'); ?></button>
													</form>
												</div>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					<?php if ($total_pages > 1) : ?>
						<p style="margin-top:10px;">
							<?php for ($page_num = 1; $page_num <= $total_pages; $page_num++) : ?>
								<?php
								$page_url = add_query_arg([
									'emali_log_page' => $page_num,
									'emali_log_status' => $current_status,
									'emali_log_search' => $current_search,
								], $emali_log_base_url);
								?>
								<?php if ($page_num === $current_page) : ?>
									<strong><?php echo esc_html((string) $page_num); ?></strong>
								<?php else : ?>
									<a href="<?php echo esc_url($page_url); ?>"><?php echo esc_html((string) $page_num); ?></a>
								<?php endif; ?>
								<?php echo $page_num < $total_pages ? ' | ' : ''; ?>
							<?php endfor; ?>
						</p>
					<?php endif; ?>

					<?php if (!empty($preview_entry)) : ?>
						<?php
						$preview_subject = isset($preview_entry['subject']) ? (string) $preview_entry['subject'] : '';
						$preview_message = isset($preview_entry['message']) ? (string) $preview_entry['message'] : '';
						$preview_headers = self::decode_json_array($preview_entry['headers'] ?? '');
						$preview_attachments = self::decode_json_array($preview_entry['attachments'] ?? '');
						?>
						<div class="um-admin-card" style="margin-top:16px;">
							<div class="um-admin-card-header">
								<span class="dashicons dashicons-visibility"></span>
								<h2><?php esc_html_e('Email Preview', 'user-manager'); ?> #<?php echo esc_html((string) absint($preview_entry['id'] ?? 0)); ?></h2>
							</div>
							<div class="um-admin-card-body">
								<p><strong><?php esc_html_e('Subject:', 'user-manager'); ?></strong> <?php echo esc_html($preview_subject); ?></p>
								<p style="margin-top:8px;">
									<button type="button" class="button um-email-log-open-html-modal" data-log-id="<?php echo esc_attr((string) absint($preview_entry['id'] ?? 0)); ?>"><?php esc_html_e('Open HTML in Modal Window', 'user-manager'); ?></button>
								</p>
								<div style="padding:12px; border:1px solid #dcdcde; border-radius:4px; background:#fff; max-height:420px; overflow:auto;">
									<?php echo wp_kses_post($preview_message); ?>
								</div>
								<p style="margin-top:10px;"><strong><?php esc_html_e('HTML Source', 'user-manager'); ?></strong></p>
								<textarea readonly class="large-text code" rows="8"><?php echo esc_textarea($preview_message); ?></textarea>

								<?php if (!empty($preview_headers)) : ?>
									<p style="margin-top:10px;"><strong><?php esc_html_e('Headers', 'user-manager'); ?></strong></p>
									<textarea readonly class="large-text code" rows="5"><?php echo esc_textarea(implode("\n", $preview_headers)); ?></textarea>
								<?php endif; ?>
								<?php if (!empty($preview_attachments)) : ?>
									<p style="margin-top:10px;"><strong><?php esc_html_e('Attachments', 'user-manager'); ?></strong></p>
									<textarea readonly class="large-text code" rows="3"><?php echo esc_textarea(implode("\n", $preview_attachments)); ?></textarea>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:14px;" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to clear all logged emails?', 'user-manager')); ?>');">
						<input type="hidden" name="action" value="user_manager_emali_log_clear" />
						<input type="hidden" name="addon_section" value="emali-log" />
						<input type="hidden" name="addon_tag" value="<?php echo esc_attr($current_addon_tag); ?>" />
						<?php wp_nonce_field('user_manager_emali_log_clear'); ?>
						<?php submit_button(__('Clear Email Log History', 'user-manager'), 'delete', 'submit', false); ?>
					</form>
				</div>
			</div>
		</div>
		<div id="um-email-log-html-modal" style="display:none; position:fixed; inset:0; z-index:100000;">
			<div class="um-email-log-html-modal-backdrop" data-close-modal="1" style="position:absolute; inset:0; background:rgba(0,0,0,0.6);"></div>
			<div style="position:relative; width:min(1100px, calc(100vw - 40px)); margin:24px auto; background:#fff; border-radius:8px; box-shadow:0 20px 50px rgba(0,0,0,0.35); overflow:hidden;">
				<div style="display:flex; align-items:center; justify-content:space-between; padding:12px 14px; border-bottom:1px solid #dcdcde;">
					<h2 id="um-email-log-html-modal-title" style="margin:0; font-size:16px;"><?php esc_html_e('Email HTML Preview', 'user-manager'); ?></h2>
					<button type="button" class="button" data-close-modal="1"><?php esc_html_e('Close', 'user-manager'); ?></button>
				</div>
				<div style="padding:0;">
					<iframe id="um-email-log-html-modal-iframe" sandbox="" style="width:100%; height:72vh; border:0; display:block;"></iframe>
				</div>
			</div>
		</div>
		<script>
		jQuery(function($) {
			var emailModalData = <?php echo wp_json_encode($modal_payload); ?>;
			var $htmlModal = $('#um-email-log-html-modal');
			var $htmlModalTitle = $('#um-email-log-html-modal-title');
			var htmlModalIframe = document.getElementById('um-email-log-html-modal-iframe');

			function toggleEmailLogFields() {
				$('#um-emali-log-fields').toggle($('#um-emali-log-enabled').is(':checked'));
			}
			function openHtmlModal(logId) {
				var payload = emailModalData[String(logId)] || emailModalData[logId];
				if (!payload || !htmlModalIframe) {
					return;
				}
				var subject = payload.subject ? (' — ' + payload.subject) : '';
				$htmlModalTitle.text('<?php echo esc_js(__('Email HTML Preview', 'user-manager')); ?> #' + logId + subject);
				htmlModalIframe.srcdoc = payload.html || '<p style="font-family:Arial,sans-serif;padding:20px;"><?php echo esc_js(__('No HTML body found for this email.', 'user-manager')); ?></p>';
				$htmlModal.show();
			}
			function closeHtmlModal() {
				$htmlModal.hide();
				if (htmlModalIframe) {
					htmlModalIframe.srcdoc = '';
				}
			}

			$('#um-emali-log-enabled').on('change', toggleEmailLogFields);
			$(document).on('click', '.um-email-log-open-html-modal', function() {
				var logId = parseInt($(this).data('log-id'), 10);
				if (!logId) {
					return;
				}
				openHtmlModal(logId);
			});
			$htmlModal.on('click', '[data-close-modal="1"]', function() {
				closeHtmlModal();
			});
			$(document).on('keydown', function(event) {
				if (event.key === 'Escape' && $htmlModal.is(':visible')) {
					closeHtmlModal();
				}
			});
			toggleEmailLogFields();
		});
		</script>
		<?php
	}

	private static function render_stat_card(string $label, int $count): void {
		?>
		<div style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:12px;">
			<div style="font-size:11px;color:#646970;text-transform:uppercase;letter-spacing:0.04em;"><?php echo esc_html($label); ?></div>
			<div style="font-size:22px;font-weight:700;line-height:1.2;margin-top:4px;"><?php echo esc_html(number_format($count)); ?></div>
		</div>
		<?php
	}

	/**
	 * @param mixed $raw
	 * @return array<int,string>
	 */
	private static function decode_json_array($raw): array {
		$decoded = json_decode((string) $raw, true);
		if (!is_array($decoded)) {
			return [];
		}
		$list = [];
		foreach ($decoded as $item) {
			$item = trim((string) $item);
			if ($item !== '') {
				$list[] = $item;
			}
		}
		return $list;
	}
}
