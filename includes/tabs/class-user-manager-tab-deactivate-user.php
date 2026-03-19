<?php
/**
 * Deactivate User tab renderer.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Tab_Deactivate_User {

	public static function render(): void {
		$settings = User_Manager_Core::get_settings();
		$reset_password_enabled = array_key_exists('deactivate_users_reset_password', $settings)
			? !empty($settings['deactivate_users_reset_password'])
			: true;
		$prefix_identity_enabled = array_key_exists('deactivate_users_prefix_identity', $settings)
			? !empty($settings['deactivate_users_prefix_identity'])
			: true;

		$paged = isset($_GET['deactivate_users_paged']) ? max(1, absint($_GET['deactivate_users_paged'])) : 1;
		$per_page = 20;

		$user_query = new WP_User_Query([
			'number' => $per_page,
			'paged' => $paged,
			'orderby' => 'ID',
			'order' => 'DESC',
			'meta_key' => User_Manager_Core::USER_DEACTIVATED_META_KEY,
			'meta_value' => '1',
		]);

		$deactivated_users = $user_query->get_results();
		$total_users = (int) $user_query->get_total();
		$total_pages = max(1, (int) ceil($total_users / $per_page));

		$pagination_base = add_query_arg(
			[
				'page' => User_Manager_Core::SETTINGS_PAGE_SLUG,
				'tab' => User_Manager_Core::TAB_LOGIN_TOOLS,
				'login_tools_section' => User_Manager_Core::TAB_DEACTIVATE_USER,
				'deactivate_users_paged' => '%#%',
			],
			admin_url('admin.php')
		);

		$pagination_links = paginate_links([
			'base' => $pagination_base,
			'format' => '',
			'current' => $paged,
			'total' => $total_pages,
			'type' => 'array',
			'prev_text' => __('&laquo; Previous', 'user-manager'),
			'next_text' => __('Next &raquo;', 'user-manager'),
		]);
		?>
		<div class="um-create-user-layout">
			<div class="um-create-user-form">
				<div class="um-admin-card">
					<div class="um-admin-card-header">
						<span class="dashicons dashicons-lock"></span>
						<h2><?php esc_html_e('Deactivate User(s)', 'user-manager'); ?></h2>
					</div>
					<div class="um-admin-card-body">
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="um-deactivate-user-form">
							<input type="hidden" name="action" value="user_manager_deactivate_user" />
							<?php wp_nonce_field('user_manager_deactivate_user'); ?>

							<div class="um-form-field">
								<label for="um-deactivate-identifiers"><?php esc_html_e('Email Addresses or Usernames', 'user-manager'); ?> <span style="color:red;">*</span></label>
								<textarea name="identifiers" id="um-deactivate-identifiers" class="large-text" rows="5" required placeholder="<?php esc_attr_e("user1@example.com\nusername2", 'user-manager'); ?>"></textarea>
								<p class="description"><?php esc_html_e('Enter one email address or username per line. Matching users will be deactivated (not deleted).', 'user-manager'); ?></p>
							</div>

							<div class="um-info-box">
								<span class="dashicons dashicons-info"></span>
								<div>
									<strong><?php esc_html_e('What deactivation does:', 'user-manager'); ?></strong>
									<ul style="margin: 6px 0 0 18px; list-style: disc;">
										<li><?php esc_html_e('Preserves user account data for historical reporting.', 'user-manager'); ?></li>
										<li><?php esc_html_e('Blocks future login using a deactivated-user authentication guard.', 'user-manager'); ?></li>
										<li>
											<?php
											echo $reset_password_enabled
												? esc_html__('Quietly resets password to a random string (enabled in Settings).', 'user-manager')
												: esc_html__('Quiet password reset is currently disabled in Settings.', 'user-manager');
											?>
										</li>
										<li>
											<?php
											echo $prefix_identity_enabled
												? esc_html__('Prefixes login and email with [YYYYMMDD]-deactivated- (enabled in Settings).', 'user-manager')
												: esc_html__('Login/email prefixing is currently disabled in Settings.', 'user-manager');
											?>
										</li>
									</ul>
								</div>
							</div>

							<p style="margin-top:20px;">
								<?php submit_button(__('Deactivate User(s)', 'user-manager'), 'primary', 'submit', false); ?>
							</p>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="um-admin-card um-admin-card-full" style="margin-top:20px;">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-groups"></span>
				<h2><?php esc_html_e('Deactivated Users', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<?php if (empty($deactivated_users)) : ?>
					<p class="um-empty-message"><?php esc_html_e('No deactivated users found.', 'user-manager'); ?></p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e('User', 'user-manager'); ?></th>
								<th><?php esc_html_e('Current Login', 'user-manager'); ?></th>
								<th><?php esc_html_e('Current Email', 'user-manager'); ?></th>
								<th><?php esc_html_e('Original Login', 'user-manager'); ?></th>
								<th><?php esc_html_e('Original Email', 'user-manager'); ?></th>
								<th><?php esc_html_e('Deactivated At', 'user-manager'); ?></th>
								<th><?php esc_html_e('Deactivated By', 'user-manager'); ?></th>
								<th><?php esc_html_e('Actions', 'user-manager'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($deactivated_users as $user) : ?>
								<?php
								$user_id = (int) $user->ID;
								$original_login = (string) get_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_LOGIN_META_KEY, true);
								$original_email = (string) get_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_ORIGINAL_EMAIL_META_KEY, true);
								$deactivated_at = (string) get_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_AT_META_KEY, true);
								$deactivated_by_id = absint(get_user_meta($user_id, User_Manager_Core::USER_DEACTIVATED_BY_META_KEY, true));
								$deactivated_by_user = $deactivated_by_id > 0 ? get_user_by('ID', $deactivated_by_id) : null;
								$deactivated_by = $deactivated_by_user instanceof WP_User ? $deactivated_by_user->display_name : __('Unknown', 'user-manager');
								?>
								<tr>
									<td>
										<a href="<?php echo esc_url(get_edit_user_link($user_id)); ?>">
											<?php echo esc_html($user->display_name !== '' ? $user->display_name : $user->user_login); ?>
										</a>
									</td>
									<td><code><?php echo esc_html((string) $user->user_login); ?></code></td>
									<td><code><?php echo esc_html((string) $user->user_email); ?></code></td>
									<td><code><?php echo esc_html($original_login !== '' ? $original_login : (string) $user->user_login); ?></code></td>
									<td><code><?php echo esc_html($original_email !== '' ? $original_email : (string) $user->user_email); ?></code></td>
									<td>
										<?php
										if ($deactivated_at !== '') {
											$ts = strtotime($deactivated_at);
											echo esc_html($ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ts) : $deactivated_at);
										} else {
											echo '-';
										}
										?>
									</td>
									<td><?php echo esc_html($deactivated_by); ?></td>
									<td>
										<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Reactivate this user account?', 'user-manager')); ?>');">
											<input type="hidden" name="action" value="user_manager_reactivate_user" />
											<input type="hidden" name="user_id" value="<?php echo esc_attr((string) $user_id); ?>" />
											<input type="hidden" name="deactivate_users_paged" value="<?php echo esc_attr((string) $paged); ?>" />
											<?php wp_nonce_field('user_manager_reactivate_user_' . $user_id, 'user_manager_reactivate_user_nonce'); ?>
											<button type="submit" class="button button-small"><?php esc_html_e('Reactivate', 'user-manager'); ?></button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php if (!empty($pagination_links)) : ?>
						<div class="tablenav" style="margin-top: 12px;">
							<div class="tablenav-pages">
								<span class="displaying-num">
									<?php
									printf(
										esc_html(_n('%d user', '%d users', $total_users, 'user-manager')),
										(int) $total_users
									);
									?>
								</span>
								<span class="pagination-links" style="margin-left:10px;">
									<?php
									foreach ($pagination_links as $link) {
										echo wp_kses_post($link . ' ');
									}
									?>
								</span>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<style>
		.um-create-user-layout {
			display: grid;
			grid-template-columns: 1fr;
			gap: 20px;
			margin-top: 20px;
		}
		.um-empty-message {
			color: #646970;
			font-style: italic;
			margin: 0;
		}
		</style>
		<?php
	}
}

