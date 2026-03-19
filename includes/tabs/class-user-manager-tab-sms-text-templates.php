<?php
/**
 * SMS Text Templates section renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_SMS_Text_Templates {

	public static function render(string $base_url = '', string $templates_context = ''): void {
		$templates = User_Manager_Core::get_sms_text_templates();
		$section_url = $base_url !== ''
			? $base_url
			: add_query_arg(
				'settings_section',
				'sms-text-templates',
				User_Manager_Core::get_page_url(User_Manager_Core::TAB_SETTINGS)
			);
		$templates_context = sanitize_key($templates_context);
		if ($templates_context !== '') {
			$section_url = add_query_arg('templates_context', $templates_context, $section_url);
		}

		$edit_id = isset($_GET['edit_sms_template']) ? sanitize_key(wp_unslash($_GET['edit_sms_template'])) : '';
		$editing = ($edit_id !== '' && isset($templates[$edit_id])) ? $templates[$edit_id] : null;
		?>
		<div class="um-email-templates-layout<?php echo $editing ? ' um-email-templates-layout-editing' : ''; ?>">
			<div class="um-email-templates-list">
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-format-chat"></span>
						<h2><?php esc_html_e('Saved SMS Text Templates', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<?php if (empty($templates)) : ?>
							<div class="um-empty-state">
								<span class="dashicons dashicons-email-alt"></span>
								<p><?php esc_html_e('No SMS text templates yet. Create your first SMS text template using the form.', 'user-manager'); ?></p>
							</div>
						<?php else : ?>
							<?php foreach ($templates as $id => $template) : ?>
								<div class="um-template-card <?php echo $edit_id === $id ? 'um-template-card-active' : ''; ?>">
									<div class="um-template-card-header">
										<div>
											<h4 class="um-template-card-title"><?php echo esc_html($template['title'] ?? ''); ?></h4>
											<?php if (!empty($template['description'])) : ?>
												<p class="um-template-card-desc"><?php echo esc_html($template['description']); ?></p>
											<?php endif; ?>
										</div>
										<div class="um-template-actions">
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
												<input type="hidden" name="action" value="user_manager_move_sms_text_template" />
												<input type="hidden" name="template_id" value="<?php echo esc_attr($id); ?>" />
												<input type="hidden" name="direction" value="up" />
												<?php if ($templates_context !== '') : ?>
													<input type="hidden" name="templates_context" value="<?php echo esc_attr($templates_context); ?>" />
												<?php endif; ?>
												<?php wp_nonce_field('user_manager_move_sms_text_template'); ?>
												<button type="submit" class="button button-small" title="<?php esc_attr_e('Move Up', 'user-manager'); ?>">&#9650;</button>
											</form>
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
												<input type="hidden" name="action" value="user_manager_move_sms_text_template" />
												<input type="hidden" name="template_id" value="<?php echo esc_attr($id); ?>" />
												<input type="hidden" name="direction" value="down" />
												<?php if ($templates_context !== '') : ?>
													<input type="hidden" name="templates_context" value="<?php echo esc_attr($templates_context); ?>" />
												<?php endif; ?>
												<?php wp_nonce_field('user_manager_move_sms_text_template'); ?>
												<button type="submit" class="button button-small" title="<?php esc_attr_e('Move Down', 'user-manager'); ?>">&#9660;</button>
											</form>
											<a href="<?php echo esc_url(add_query_arg('edit_sms_template', $id, $section_url)); ?>" class="button button-small"><?php esc_html_e('Edit', 'user-manager'); ?></a>
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
												<input type="hidden" name="action" value="user_manager_duplicate_sms_text_template" />
												<input type="hidden" name="template_id" value="<?php echo esc_attr($id); ?>" />
												<?php if ($templates_context !== '') : ?>
													<input type="hidden" name="templates_context" value="<?php echo esc_attr($templates_context); ?>" />
												<?php endif; ?>
												<?php wp_nonce_field('user_manager_duplicate_sms_text_template'); ?>
												<button type="submit" class="button button-small"><?php esc_html_e('Duplicate', 'user-manager'); ?></button>
											</form>
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js(__('Delete this SMS text template?', 'user-manager')); ?>');">
												<input type="hidden" name="action" value="user_manager_delete_sms_text_template" />
												<input type="hidden" name="template_id" value="<?php echo esc_attr($id); ?>" />
												<?php if ($templates_context !== '') : ?>
													<input type="hidden" name="templates_context" value="<?php echo esc_attr($templates_context); ?>" />
												<?php endif; ?>
												<?php wp_nonce_field('user_manager_delete_sms_text_template'); ?>
												<button type="submit" class="button button-small button-link-delete"><?php esc_html_e('Delete', 'user-manager'); ?></button>
											</form>
										</div>
									</div>
									<div class="um-template-details">
										<div class="um-template-body-preview">
											<strong><?php esc_html_e('SMS Text:', 'user-manager'); ?></strong>
											<div class="um-template-body-content">
												<?php echo esc_html($template['body'] ?? ''); ?>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="um-email-templates-form">
				<div class="um-admin-card <?php echo $editing ? 'um-admin-card-editing' : ''; ?>">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-<?php echo $editing ? 'edit' : 'plus-alt2'; ?>"></span>
						<h2><?php echo $editing ? esc_html__('Edit SMS Text Template', 'user-manager') : esc_html__('Add New SMS Text Template', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
							<input type="hidden" name="action" value="user_manager_save_sms_text_template" />
							<?php if ($templates_context !== '') : ?>
								<input type="hidden" name="templates_context" value="<?php echo esc_attr($templates_context); ?>" />
							<?php endif; ?>
							<?php if ($edit_id !== '') : ?>
								<input type="hidden" name="template_id" value="<?php echo esc_attr($edit_id); ?>" />
							<?php endif; ?>
							<?php wp_nonce_field('user_manager_save_sms_text_template'); ?>

							<div class="um-form-field">
								<label for="um-sms-tpl-title"><?php esc_html_e('Template Title', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<input type="text" name="title" id="um-sms-tpl-title" class="regular-text" value="<?php echo esc_attr($editing['title'] ?? ''); ?>" required />
							</div>

							<div class="um-form-field">
								<label for="um-sms-tpl-description"><?php esc_html_e('Description/Notes', 'user-manager'); ?></label>
								<textarea name="description" id="um-sms-tpl-description" rows="2" class="large-text"><?php echo esc_textarea($editing['description'] ?? ''); ?></textarea>
							</div>

							<div class="um-form-field">
								<label for="um-sms-tpl-body"><?php esc_html_e('SMS Text Message', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<textarea name="body" id="um-sms-tpl-body" rows="8" class="large-text code" required><?php echo esc_textarea($editing['body'] ?? self::get_default_sms_body()); ?></textarea>
								<p class="description">
									<?php esc_html_e('Placeholders:', 'user-manager'); ?>
									<code>%SITEURL%</code>
									<code>%LOGINURL%</code>
									<code>%USERNAME%</code>
									<code>%PASSWORD%</code>
									<code>%EMAIL%</code>
									<code>%FIRSTNAME%</code>
									<code>%LASTNAME%</code>
									<code>%PASSWORDRESETURL%</code>
									<code>%COUPONCODE%</code>
									<code>%PHONENUMBER%</code>
								</p>
							</div>

							<p style="margin-top:20px;">
								<?php submit_button($editing ? __('Update SMS Text Template', 'user-manager') : __('Save SMS Text Template', 'user-manager'), 'primary', 'submit', false); ?>
								<?php if ($editing) : ?>
									<a href="<?php echo esc_url($section_url); ?>" class="button" style="margin-left:8px;"><?php esc_html_e('Cancel', 'user-manager'); ?></a>
								<?php endif; ?>
							</p>
						</form>
					</div>
				</div>

				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-info-outline"></span>
						<h2><?php esc_html_e('Template Tips', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p><?php esc_html_e('Keep SMS templates concise and direct. If your provider supports long messages, messages may still be split into multiple SMS segments by carriers.', 'user-manager'); ?></p>
						<p><?php esc_html_e('For best compatibility, avoid HTML and use plain text punctuation.', 'user-manager'); ?></p>
					</div>
				</div>
			</div>
		</div>

		<style>
		.um-email-templates-layout {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 20px;
			margin-top: 20px;
		}
		.um-email-templates-form {
			display: flex;
			flex-direction: column;
			gap: 20px;
		}
		.um-template-card {
			background: #f9f9f9;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			padding: 16px;
			margin-bottom: 16px;
		}
		.um-template-card:last-child {
			margin-bottom: 0;
		}
		.um-template-card-active {
			border-color: #2271b1;
			background: #f0f6fc;
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
			flex-shrink: 0;
			flex-wrap: wrap;
			justify-content: flex-end;
		}
		.um-template-details {
			border-top: 1px solid #dcdcde;
			padding-top: 12px;
		}
		.um-template-body-preview {
			margin-top: 8px;
		}
		.um-template-body-preview strong {
			display: block;
			margin-bottom: 8px;
			font-size: 13px;
			color: #1d2327;
		}
		.um-template-body-content {
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			padding: 12px;
			font-size: 13px;
			line-height: 1.5;
			white-space: pre-wrap;
		}
		.um-admin-card-editing {
			border-color: #2271b1;
		}
		.um-admin-card-editing .um-admin-card-header {
			background: #2271b1;
			border-color: #2271b1;
		}
		.um-admin-card-editing .um-admin-card-header h2,
		.um-admin-card-editing .um-admin-card-header .dashicons {
			color: #fff;
		}
		.um-empty-state {
			text-align: center;
			padding: 40px 20px;
			color: #646970;
		}
		.um-empty-state .dashicons {
			font-size: 48px;
			width: 48px;
			height: 48px;
			color: #c3c4c7;
			margin-bottom: 12px;
		}
		.um-empty-state p {
			margin: 0;
		}
		@media (max-width: 1100px) {
			.um-email-templates-layout {
				grid-template-columns: 1fr;
			}
		}
		</style>
		<?php
	}

	private static function get_default_sms_body(): string {
		return 'Hi %FIRSTNAME%, your login is %SITEURL%%LOGINURL% (username: %USERNAME%).';
	}
}

