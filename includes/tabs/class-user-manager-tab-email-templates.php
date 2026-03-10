<?php
/**
 * Email Templates tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Email_Templates {

	public static function render(): void {
		// Auto-import templates if none exist and this hasn't been done before
		$auto_import_done = get_option('user_manager_auto_import_templates_done', false);
		$templates = User_Manager_Core::get_email_templates();
		
		if (empty($templates) && !$auto_import_done && current_user_can('manage_options')) {
			// Import demo templates
			User_Manager_Actions::import_demo_templates();
			// Import coupon template
			User_Manager_Actions::import_coupon_template();
			// Mark as done so it only happens once
			update_option('user_manager_auto_import_templates_done', true);
			// Refresh templates after import
			$templates = User_Manager_Core::get_email_templates();
		}
		
		$edit_id = isset($_GET['edit_template']) ? sanitize_key($_GET['edit_template']) : null;
		$editing = ($edit_id && isset($templates[$edit_id])) ? $templates[$edit_id] : null;
		?>
		<div class="um-email-templates-layout<?php echo $editing ? ' um-email-templates-layout-editing' : ''; ?>">
			<!-- Left Column: Saved Templates -->
			<div class="um-email-templates-list">
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-email"></span>
						<h2><?php esc_html_e('Saved Templates', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<?php if (empty($templates)) : ?>
							<div class="um-empty-state">
								<span class="dashicons dashicons-email-alt"></span>
								<p><?php esc_html_e('No templates yet. Create your first template using the form.', 'user-manager'); ?></p>
							</div>
						<?php else : ?>
							<?php foreach ($templates as $id => $template) : ?>
								<div class="um-template-card <?php echo $edit_id === $id ? 'um-template-card-active' : ''; ?>">
									<div class="um-template-card-header">
										<div>
											<h4 class="um-template-card-title"><?php echo esc_html($template['title']); ?></h4>
											<?php if (!empty($template['description'])) : ?>
												<p class="um-template-card-desc"><?php echo esc_html($template['description']); ?></p>
											<?php endif; ?>
										</div>
										<div class="um-template-actions">
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
												<input type="hidden" name="action" value="user_manager_move_template" />
												<input type="hidden" name="template_id" value="<?php echo esc_attr($id); ?>" />
												<input type="hidden" name="direction" value="up" />
												<?php wp_nonce_field('user_manager_move_template'); ?>
												<button type="submit" class="button button-small" title="<?php esc_attr_e('Move Up', 'user-manager'); ?>">&#9650;</button>
											</form>
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
												<input type="hidden" name="action" value="user_manager_move_template" />
												<input type="hidden" name="template_id" value="<?php echo esc_attr($id); ?>" />
												<input type="hidden" name="direction" value="down" />
												<?php wp_nonce_field('user_manager_move_template'); ?>
												<button type="submit" class="button button-small" title="<?php esc_attr_e('Move Down', 'user-manager'); ?>">&#9660;</button>
											</form>
											<a href="<?php echo esc_url(add_query_arg('edit_template', $id, User_Manager_Core::get_page_url(User_Manager_Core::TAB_EMAIL_TEMPLATES))); ?>" class="button button-small"><?php esc_html_e('Edit', 'user-manager'); ?></a>
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
												<input type="hidden" name="action" value="user_manager_duplicate_template" />
												<input type="hidden" name="template_id" value="<?php echo esc_attr($id); ?>" />
												<?php wp_nonce_field('user_manager_duplicate_template'); ?>
												<button type="submit" class="button button-small" title="<?php esc_attr_e('Duplicate', 'user-manager'); ?>">
													<?php esc_html_e('Duplicate', 'user-manager'); ?>
												</button>
											</form>
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js(__('Delete this template?', 'user-manager')); ?>');">
												<input type="hidden" name="action" value="user_manager_delete_template" />
												<input type="hidden" name="template_id" value="<?php echo esc_attr($id); ?>" />
												<?php wp_nonce_field('user_manager_delete_template'); ?>
												<button type="submit" class="button button-small button-link-delete"><?php esc_html_e('Delete', 'user-manager'); ?></button>
											</form>
										</div>
									</div>
									<div class="um-template-details">
										<div class="um-template-detail-row">
											<strong><?php esc_html_e('Subject:', 'user-manager'); ?></strong>
											<span><?php echo esc_html($template['subject']); ?></span>
										</div>
										<div class="um-template-detail-row">
											<strong><?php esc_html_e('Heading:', 'user-manager'); ?></strong>
											<span><?php echo esc_html($template['heading']); ?></span>
										</div>
										<?php if (!empty($template['bcc'])) : ?>
										<div class="um-template-detail-row">
											<strong><?php esc_html_e('BCC:', 'user-manager'); ?></strong>
											<span><?php echo esc_html($template['bcc']); ?></span>
										</div>
										<?php endif; ?>
										<div class="um-template-body-preview">
											<strong><?php esc_html_e('Body:', 'user-manager'); ?></strong>
											<div class="um-template-body-content"><?php echo wp_kses_post($template['body']); ?></div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			
			<!-- Right Column: Add/Edit Form -->
			<div class="um-email-templates-form">
				<div class="um-admin-card <?php echo $editing ? 'um-admin-card-editing' : ''; ?>">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-<?php echo $editing ? 'edit' : 'plus-alt2'; ?>"></span>
						<h2><?php echo $editing ? esc_html__('Edit Email Template', 'user-manager') : esc_html__('Add New Email Template', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
							<input type="hidden" name="action" value="user_manager_save_template" />
							<?php if ($edit_id) : ?>
								<input type="hidden" name="template_id" value="<?php echo esc_attr($edit_id); ?>" />
							<?php endif; ?>
							<?php wp_nonce_field('user_manager_save_template'); ?>
							
							<div class="um-form-field">
								<label for="um-tpl-title"><?php esc_html_e('Template Title', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<input type="text" name="title" id="um-tpl-title" class="regular-text" value="<?php echo esc_attr($editing['title'] ?? ''); ?>" required />
							</div>
							
							<div class="um-form-field">
								<label for="um-tpl-description"><?php esc_html_e('Description/Notes', 'user-manager'); ?></label>
								<textarea name="description" id="um-tpl-description" rows="2" class="large-text"><?php echo esc_textarea($editing['description'] ?? ''); ?></textarea>
							</div>
							
							<div class="um-form-field">
								<label for="um-tpl-subject"><?php esc_html_e('Email Subject', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<input type="text" name="subject" id="um-tpl-subject" class="regular-text" value="<?php echo esc_attr($editing['subject'] ?? ''); ?>" required />
							</div>
							
							<div class="um-form-field">
								<label for="um-tpl-heading"><?php esc_html_e('Email Heading', 'user-manager'); ?></label>
								<input type="text" name="heading" id="um-tpl-heading" class="regular-text" value="<?php echo esc_attr($editing['heading'] ?? ''); ?>" />
							</div>
							
							<div class="um-form-field">
								<label for="um-tpl-body"><?php esc_html_e('Email Body', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<textarea name="body" id="um-tpl-body" rows="12" class="large-text code" required><?php echo esc_textarea($editing['body'] ?? self::get_default_email_body()); ?></textarea>
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
								</p>
							</div>
							
							<div class="um-form-field">
								<label for="um-tpl-bcc"><?php esc_html_e('BCC Email', 'user-manager'); ?></label>
								<input type="email" name="bcc" id="um-tpl-bcc" class="regular-text" value="<?php echo esc_attr($editing['bcc'] ?? ''); ?>" />
							</div>
							
							<p style="margin-top:20px;">
								<?php submit_button($editing ? __('Update Template', 'user-manager') : __('Save Template', 'user-manager'), 'primary', 'submit', false); ?>
								<?php if ($editing) : ?>
									<a href="<?php echo esc_url(User_Manager_Core::get_page_url(User_Manager_Core::TAB_EMAIL_TEMPLATES)); ?>" class="button" style="margin-left:8px;"><?php esc_html_e('Cancel', 'user-manager'); ?></a>
								<?php endif; ?>
							</p>
						</form>
					</div>
				</div>
				
				<?php if ($editing) : ?>
				<?php
				// Build demo preview using current (editing) template values and demo data
				$demo_email = 'demo.user@example.com';
				$demo_username = 'demo.user';
				$demo_first = 'Demo';
				$demo_last = 'User';
				$demo_login_url = '/my-account/';
				$demo_password = '••••••••••••';
				$demo_password_reset_url = home_url('/my-account/lost-password/');
				
				$replacements = [
					'%SITEURL%' => home_url(),
					'%LOGINURL%' => $demo_login_url,
					'%USERNAME%' => $demo_username,
					'%PASSWORD%' => $demo_password,
					'%EMAIL%' => $demo_email,
					'%FIRSTNAME%' => $demo_first,
					'%LASTNAME%' => $demo_last,
					'%PASSWORDRESETURL%' => $demo_password_reset_url,
				];
				
				$preview_heading = isset($editing['heading']) ? str_replace(array_keys($replacements), array_values($replacements), (string) $editing['heading']) : 'Preview';
				$preview_body = isset($editing['body']) ? str_replace(array_keys($replacements), array_values($replacements), (string) $editing['body']) : self::get_default_email_body();
				
				$preview_html = User_Manager_Email::get_preview_html($preview_body, $preview_heading);
				?>
				<div class="um-admin-card um-live-preview-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-visibility"></span>
						<h2><?php esc_html_e('Live Preview (Demo Data)', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<p class="description" style="margin-top:0;"><?php esc_html_e('Preview uses demo data and the current template fields. Save the template to persist changes.', 'user-manager'); ?></p>
						<div style="border:1px solid #dcdcde; border-radius:4px; overflow:hidden; background:#f7f7f7;">
							<iframe style="width:100%; height:520px; border:0; display:block;" srcdoc="<?php echo esc_attr($preview_html); ?>"></iframe>
						</div>
					</div>
				</div>
				<?php endif; ?>
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
		.um-email-templates-layout.um-email-templates-layout-editing .um-email-templates-form {
			order: -1;
		}
		.um-email-templates-form .um-live-preview-card {
			order: -1;
			margin-top: 0;
		}
		@media (max-width: 1200px) {
			.um-email-templates-layout {
				grid-template-columns: 1fr;
			}
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
		}
		.um-template-details {
			border-top: 1px solid #dcdcde;
			padding-top: 12px;
		}
		.um-template-detail-row {
			display: flex;
			gap: 8px;
			margin-bottom: 6px;
			font-size: 13px;
		}
		.um-template-detail-row strong {
			flex-shrink: 0;
			width: 60px;
			color: #1d2327;
		}
		.um-template-detail-row span {
			color: #50575e;
		}
		.um-template-body-preview {
			margin-top: 12px;
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
		}
		.um-template-body-content p {
			margin: 0 0 4px;
		}
		.um-template-body-content p:last-child {
			margin-bottom: 0;
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
		</style>
		<?php
	}
	
	/**
	 * Default email body text used for new templates.
	 */
	private static function get_default_email_body(): string {
		return '<p><strong>Login Page:</strong><br>
<a href="%SITEURL%%LOGINURL%">%SITEURL%%LOGINURL%</a></p>

<p><strong>Email:</strong><br>
%EMAIL%</p>

<p><strong>Password:</strong><br>
%PASSWORD%</p>';
	}
}




