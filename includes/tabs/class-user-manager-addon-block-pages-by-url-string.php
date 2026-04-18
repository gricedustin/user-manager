<?php
/**
 * Add-on card: Block Pages by URL String.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Addon_Block_Pages_By_URL_String {

	public static function render(array $settings, string $settings_form_id = ''): void {
		$form_attr = $settings_form_id !== '' ? ' form="' . esc_attr($settings_form_id) . '"' : '';
		$enabled = !empty($settings['block_pages_by_url_string_enabled']);
		$available_roles = self::get_available_roles();
		$raw_rules = isset($settings['block_pages_by_url_string_rules']) && is_array($settings['block_pages_by_url_string_rules'])
			? $settings['block_pages_by_url_string_rules']
			: [];
		$rules = self::normalize_rules($raw_rules, $settings);
		if (function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		?>
		<div class="um-admin-card um-addon-collapsible" id="um-addon-card-block-pages-by-url-string" data-um-active-selectors="#um-block-pages-by-url-string-enabled">
			<div class="um-admin-card-header">
				<span class="dashicons dashicons-hidden"></span>
				<h2><?php esc_html_e('Block Pages by URL String', 'user-manager'); ?></h2>
			</div>
			<div class="um-admin-card-body">
				<div class="um-form-field">
					<label>
						<input type="checkbox" id="um-block-pages-by-url-string-enabled" name="block_pages_by_url_string_enabled" value="1" <?php checked($enabled); ?><?php echo $form_attr; ?> />
						<?php esc_html_e('Activate', 'user-manager'); ?>
					</label>
					<p class="description"><?php esc_html_e('Hide page content by matching URL strings. Supports line-by-line match rules, exceptions, redirect, and branded blocked screen styling.', 'user-manager'); ?></p>
				</div>

				<div id="um-block-pages-by-url-string-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
					<input type="hidden" id="um-block-pages-rules-json" name="block_pages_by_url_string_rules_json" value=""<?php echo $form_attr; ?> />
					<div id="um-block-pages-rules-wrap">
						<?php foreach ($rules as $index => $rule) : ?>
							<div class="um-block-pages-rule-card" data-rule-index="<?php echo esc_attr((string) $index); ?>">
								<div class="um-block-pages-rule-card-header">
									<strong><?php echo esc_html(sprintf(__('Rule Set %d', 'user-manager'), $index + 1)); ?></strong>
									<button type="button" class="button-link-delete um-block-pages-remove-rule"><?php esc_html_e('Remove', 'user-manager'); ?></button>
								</div>
								<div class="um-form-field">
									<label><strong><?php esc_html_e("Page URL Strings to Match & Hide All Content", 'user-manager'); ?></strong></label>
									<textarea class="large-text code um-block-pages-rule-match-urls" rows="6"<?php echo $form_attr; ?>><?php echo esc_textarea((string) $rule['match_urls']); ?></textarea>
									<p class="description"><?php esc_html_e('One per line. A slash (/) will match and block the entire front end.', 'user-manager'); ?></p>
								</div>
								<div class="um-form-field">
									<label><strong><?php esc_html_e("Exception Page URL Strings to Still Allow", 'user-manager'); ?></strong></label>
									<textarea class="large-text code um-block-pages-rule-exception-urls" rows="6"<?php echo $form_attr; ?>><?php echo esc_textarea((string) $rule['exception_urls']); ?></textarea>
									<p class="description"><?php esc_html_e('One per line. If a blocked URL also matches an exception string, it will stay accessible.', 'user-manager'); ?></p>
								</div>
								<div class="um-form-field">
									<label><strong><?php esc_html_e('Limit this rule to usernames (comma-separated, optional)', 'user-manager'); ?></strong></label>
									<input type="text" class="large-text um-block-pages-rule-usernames" value="<?php echo esc_attr((string) $rule['usernames']); ?>" placeholder="user1, user2"<?php echo $form_attr; ?> />
									<p class="description"><?php esc_html_e('Leave empty to apply by role-only or to everyone (if no roles selected).', 'user-manager'); ?></p>
								</div>
								<?php self::render_role_checkboxes($available_roles, isset($rule['roles']) && is_array($rule['roles']) ? $rule['roles'] : []); ?>
								<div class="um-form-field">
									<label><strong><?php esc_html_e('Optional Background Color', 'user-manager'); ?></strong></label>
									<input type="text" class="um-block-pages-color-field um-block-pages-rule-background-color" value="<?php echo esc_attr((string) $rule['background_color']); ?>" data-default-color="#000000"<?php echo $form_attr; ?> />
								</div>
								<div class="um-form-field">
									<label><strong><?php esc_html_e('Optional Background Image URL', 'user-manager'); ?></strong></label>
									<input type="url" class="regular-text um-block-pages-rule-background-url" value="<?php echo esc_attr((string) $rule['background_url']); ?>" placeholder="https://"<?php echo $form_attr; ?> />
									<p><button type="button" class="button um-block-pages-background-upload"><?php esc_html_e('Select Image', 'user-manager'); ?></button></p>
								</div>
								<div class="um-form-field">
									<label><strong><?php esc_html_e('Optional Centered Logo Image URL', 'user-manager'); ?></strong></label>
									<input type="url" class="regular-text um-block-pages-rule-logo-url" value="<?php echo esc_attr((string) $rule['logo_url']); ?>" placeholder="https://"<?php echo $form_attr; ?> />
									<p><button type="button" class="button um-block-pages-logo-upload"><?php esc_html_e('Select Image', 'user-manager'); ?></button></p>
								</div>
								<div class="um-form-field">
									<label><strong><?php esc_html_e('Optional Centered Logo Image Width', 'user-manager'); ?></strong></label>
									<input type="text" class="regular-text um-block-pages-rule-logo-width" value="<?php echo esc_attr((string) $rule['logo_width']); ?>" placeholder="100px"<?php echo $form_attr; ?> />
								</div>
								<div class="um-form-field">
									<label><strong><?php esc_html_e('Optional Message at Top of Page', 'user-manager'); ?></strong></label>
									<input type="text" class="regular-text um-block-pages-rule-message" value="<?php echo esc_attr((string) $rule['message']); ?>" placeholder="<?php esc_attr_e('Page not found.', 'user-manager'); ?>"<?php echo $form_attr; ?> />
								</div>
								<div class="um-form-field">
									<label><strong><?php esc_html_e('Optional Text Color', 'user-manager'); ?></strong></label>
									<input type="text" class="um-block-pages-color-field um-block-pages-rule-text-color" value="<?php echo esc_attr((string) $rule['text_color']); ?>" data-default-color="#ffffff"<?php echo $form_attr; ?> />
								</div>
								<div class="um-form-field">
									<label><strong><?php esc_html_e('Optional Redirect URL', 'user-manager'); ?></strong></label>
									<input type="url" class="regular-text um-block-pages-rule-redirect-url" value="<?php echo esc_attr((string) $rule['redirect_url']); ?>" placeholder="https://"<?php echo $form_attr; ?> />
									<p class="description"><?php esc_html_e('If set, matching blocked URLs redirect here instead of showing the blocked screen.', 'user-manager'); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<p style="margin-top:8px;">
						<button type="button" class="button button-secondary" id="um-block-pages-add-rule"><?php esc_html_e('Add Rule Set', 'user-manager'); ?></button>
					</p>
				</div>
			</div>
		</div>
		<style>
		.um-block-pages-rule-card {
			border: 1px solid #dcdcde;
			border-radius: 6px;
			padding: 12px;
			margin: 0 0 14px;
			background: #fff;
		}
		.um-block-pages-rule-card-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 8px;
		}
		</style>
		<script>
		jQuery(function($) {
			var $rulesWrap = $('#um-block-pages-rules-wrap');
			var $rulesJson = $('#um-block-pages-rules-json');
			var availableRoles = <?php echo wp_json_encode($available_roles); ?> || {};
			var baseRuleTemplate = {
				match_urls: '',
				exception_urls: '',
				usernames: '',
				roles: [],
				background_color: '#000000',
				background_url: '',
				logo_url: '',
				logo_width: '',
				message: '',
				text_color: '',
				redirect_url: ''
			};

			function escHtml(str) {
				return $('<div/>').text(str || '').html();
			}

			function renderRoleCheckboxes(selectedRoles) {
				var html = '';
				html += '<div class="um-form-field"><label class="um-label-block"><strong><?php echo esc_js(__('Limit this rule to roles (optional)', 'user-manager')); ?></strong></label><div class="um-checkbox-grid um-block-pages-rule-roles-wrap">';
				$.each(availableRoles, function(roleKey, roleLabel) {
					var checked = $.inArray(String(roleKey), selectedRoles) !== -1 ? ' checked="checked"' : '';
					html += '<label class="um-checkbox-chip">';
					html += '<input type="checkbox" class="um-block-pages-rule-role" value="' + escHtml(roleKey) + '"' + checked + ' />';
					html += '<span>' + escHtml(roleLabel) + ' <code>' + escHtml(roleKey) + '</code></span>';
					html += '</label>';
				});
				html += '</div><p class="description"><?php echo esc_js(__('If usernames and roles are both empty, this rule applies to everyone. If either is set, users must match at least one.', 'user-manager')); ?></p></div>';
				return html;
			}

			function createRuleCard(rule, index) {
				rule = $.extend({}, baseRuleTemplate, rule || {});
				var selectedRoles = Array.isArray(rule.roles) ? rule.roles.map(String) : [];
				var html = '';
				html += '<div class="um-block-pages-rule-card" data-rule-index="' + index + '">';
				html += '  <div class="um-block-pages-rule-card-header"><strong><?php echo esc_js(__('Rule Set', 'user-manager')); ?> ' + (index + 1) + '</strong><button type="button" class="button-link-delete um-block-pages-remove-rule"><?php echo esc_js(__('Remove', 'user-manager')); ?></button></div>';
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__("Page URL Strings to Match & Hide All Content", 'user-manager')); ?></strong></label><textarea class="large-text code um-block-pages-rule-match-urls" rows="6">' + escHtml(rule.match_urls) + '</textarea><p class="description"><?php echo esc_js(__('One per line. A slash (/) will match and block the entire front end.', 'user-manager')); ?></p></div>';
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__("Exception Page URL Strings to Still Allow", 'user-manager')); ?></strong></label><textarea class="large-text code um-block-pages-rule-exception-urls" rows="6">' + escHtml(rule.exception_urls) + '</textarea><p class="description"><?php echo esc_js(__('One per line. If a blocked URL also matches an exception string, it will stay accessible.', 'user-manager')); ?></p></div>';
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__('Limit this rule to usernames (comma-separated, optional)', 'user-manager')); ?></strong></label><input type="text" class="large-text um-block-pages-rule-usernames" value="' + escHtml(rule.usernames) + '" placeholder="user1, user2" /><p class="description"><?php echo esc_js(__('Leave empty to apply by role-only or to everyone (if no roles selected).', 'user-manager')); ?></p></div>';
				html += renderRoleCheckboxes(selectedRoles);
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__('Optional Background Color', 'user-manager')); ?></strong></label><input type="text" class="um-block-pages-color-field um-block-pages-rule-background-color" value="' + escHtml(rule.background_color || '#000000') + '" data-default-color="#000000" /></div>';
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__('Optional Background Image URL', 'user-manager')); ?></strong></label><input type="url" class="regular-text um-block-pages-rule-background-url" value="' + escHtml(rule.background_url) + '" placeholder="https://" /><p><button type="button" class="button um-block-pages-background-upload"><?php echo esc_js(__('Select Image', 'user-manager')); ?></button></p></div>';
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__('Optional Centered Logo Image URL', 'user-manager')); ?></strong></label><input type="url" class="regular-text um-block-pages-rule-logo-url" value="' + escHtml(rule.logo_url) + '" placeholder="https://" /><p><button type="button" class="button um-block-pages-logo-upload"><?php echo esc_js(__('Select Image', 'user-manager')); ?></button></p></div>';
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__('Optional Centered Logo Image Width', 'user-manager')); ?></strong></label><input type="text" class="regular-text um-block-pages-rule-logo-width" value="' + escHtml(rule.logo_width) + '" placeholder="100px" /></div>';
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__('Optional Message at Top of Page', 'user-manager')); ?></strong></label><input type="text" class="regular-text um-block-pages-rule-message" value="' + escHtml(rule.message) + '" placeholder="<?php echo esc_js(__('Page not found.', 'user-manager')); ?>" /></div>';
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__('Optional Text Color', 'user-manager')); ?></strong></label><input type="text" class="um-block-pages-color-field um-block-pages-rule-text-color" value="' + escHtml(rule.text_color || '') + '" data-default-color="#ffffff" /></div>';
				html += '  <div class="um-form-field"><label><strong><?php echo esc_js(__('Optional Redirect URL', 'user-manager')); ?></strong></label><input type="url" class="regular-text um-block-pages-rule-redirect-url" value="' + escHtml(rule.redirect_url) + '" placeholder="https://" /><p class="description"><?php echo esc_js(__('If set, matching blocked URLs redirect here instead of showing the blocked screen.', 'user-manager')); ?></p></div>';
				html += '</div>';
				return $(html);
			}

			function refreshRuleTitles() {
				$rulesWrap.find('.um-block-pages-rule-card').each(function(i) {
					$(this).attr('data-rule-index', i);
					$(this).find('.um-block-pages-rule-card-header strong').text('<?php echo esc_js(__('Rule Set', 'user-manager')); ?> ' + (i + 1));
				});
			}

			function syncRulesJson() {
				var payload = [];
				$rulesWrap.find('.um-block-pages-rule-card').each(function() {
					var $card = $(this);
					var roles = [];
					$card.find('.um-block-pages-rule-role:checked').each(function() {
						roles.push(String($(this).val() || ''));
					});
					payload.push({
						match_urls: $card.find('.um-block-pages-rule-match-urls').val() || '',
						exception_urls: $card.find('.um-block-pages-rule-exception-urls').val() || '',
						usernames: $card.find('.um-block-pages-rule-usernames').val() || '',
						roles: roles,
						background_color: $card.find('.um-block-pages-rule-background-color').val() || '#000000',
						background_url: $card.find('.um-block-pages-rule-background-url').val() || '',
						logo_url: $card.find('.um-block-pages-rule-logo-url').val() || '',
						logo_width: $card.find('.um-block-pages-rule-logo-width').val() || '',
						message: $card.find('.um-block-pages-rule-message').val() || '',
						text_color: $card.find('.um-block-pages-rule-text-color').val() || '',
						redirect_url: $card.find('.um-block-pages-rule-redirect-url').val() || ''
					});
				});
				$rulesJson.val(JSON.stringify(payload));
			}

			function initColorPickers($scope) {
				if (!$.fn.wpColorPicker) {
					return;
				}
				$scope.find('.um-block-pages-color-field').each(function() {
					var $field = $(this);
					if ($field.data('wpColorPicker')) {
						return;
					}
					var defaultColor = $field.attr('data-default-color') || false;
					$field.wpColorPicker({
						defaultColor: defaultColor,
						change: syncRulesJson,
						clear: syncRulesJson
					});
				});
			}

			function openMediaPicker(onSelect) {
				if (typeof wp === 'undefined' || !wp.media) {
					return;
				}
				var frame = wp.media({
					title: <?php echo wp_json_encode(__('Select Image', 'user-manager')); ?>,
					button: { text: <?php echo wp_json_encode(__('Use Image', 'user-manager')); ?> },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function() {
					var attachment = frame.state().get('selection').first();
					if (!attachment) {
						return;
					}
					var url = attachment.get('url') || '';
					if (url && typeof onSelect === 'function') {
						onSelect(url);
					}
				});
				frame.open();
			}

			$('#um-block-pages-add-rule').on('click', function(e) {
				e.preventDefault();
				var $newCard = createRuleCard(baseRuleTemplate, $rulesWrap.find('.um-block-pages-rule-card').length);
				$rulesWrap.append($newCard);
				initColorPickers($newCard);
				refreshRuleTitles();
				syncRulesJson();
			});

			$rulesWrap.on('click', '.um-block-pages-remove-rule', function(e) {
				e.preventDefault();
				$(this).closest('.um-block-pages-rule-card').remove();
				refreshRuleTitles();
				syncRulesJson();
			});

			$rulesWrap.on('click', '.um-block-pages-background-upload', function(e) {
				e.preventDefault();
				var $input = $(this).closest('.um-form-field').find('.um-block-pages-rule-background-url');
				openMediaPicker(function(url) {
					$input.val(url).trigger('change');
					syncRulesJson();
				});
			});
			$rulesWrap.on('click', '.um-block-pages-logo-upload', function(e) {
				e.preventDefault();
				var $input = $(this).closest('.um-form-field').find('.um-block-pages-rule-logo-url');
				openMediaPicker(function(url) {
					$input.val(url).trigger('change');
					syncRulesJson();
				});
			});

			$rulesWrap.on('input change', 'input, textarea, select', syncRulesJson);

			if (!$rulesWrap.find('.um-block-pages-rule-card').length) {
				$rulesWrap.append(createRuleCard(baseRuleTemplate, 0));
			}
			initColorPickers($rulesWrap);
			refreshRuleTitles();
			syncRulesJson();
		});
		</script>
		<?php
	}

	/**
	 * @return array<string,string>
	 */
	private static function get_available_roles(): array {
		if (!function_exists('wp_roles')) {
			return [];
		}
		$wp_roles = wp_roles();
		if (!$wp_roles || !method_exists($wp_roles, 'get_names')) {
			return [];
		}
		$names = $wp_roles->get_names();
		return is_array($names) ? $names : [];
	}

	/**
	 * @param array<string,string> $available_roles
	 * @param mixed                $selected_raw
	 */
	private static function render_role_checkboxes(array $available_roles, $selected_raw): void {
		$selected = [];
		if (is_array($selected_raw)) {
			foreach ($selected_raw as $raw_role) {
				$role = sanitize_key((string) $raw_role);
				if ($role === '') {
					continue;
				}
				$selected[] = $role;
			}
			$selected = array_values(array_unique($selected));
		}
		?>
		<div class="um-form-field">
			<label class="um-label-block"><strong><?php esc_html_e('Limit this rule to roles (optional)', 'user-manager'); ?></strong></label>
			<div class="um-checkbox-grid um-block-pages-rule-roles-wrap">
				<?php foreach ($available_roles as $role_key => $role_label) : ?>
					<label class="um-checkbox-chip">
						<input type="checkbox" class="um-block-pages-rule-role" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $selected, true)); ?> />
						<span><?php echo esc_html($role_label); ?> <code><?php echo esc_html($role_key); ?></code></span>
					</label>
				<?php endforeach; ?>
			</div>
			<p class="description"><?php esc_html_e('If usernames and roles are both empty, this rule applies to everyone. If either is set, users must match at least one.', 'user-manager'); ?></p>
		</div>
		<?php
	}

	/**
	 * @param array<int,mixed>       $raw_rules
	 * @param array<string,mixed>    $settings
	 * @return array<int,array<string,mixed>>
	 */
	private static function normalize_rules(array $raw_rules, array $settings): array {
		$rules = [];
		foreach ($raw_rules as $raw_rule) {
			if (!is_array($raw_rule)) {
				continue;
			}
			$rules[] = [
				'match_urls' => isset($raw_rule['match_urls']) ? (string) $raw_rule['match_urls'] : '',
				'exception_urls' => isset($raw_rule['exception_urls']) ? (string) $raw_rule['exception_urls'] : '',
				'usernames' => isset($raw_rule['usernames']) ? (string) $raw_rule['usernames'] : '',
				'roles' => isset($raw_rule['roles']) && is_array($raw_rule['roles']) ? $raw_rule['roles'] : [],
				'background_color' => isset($raw_rule['background_color']) ? (string) $raw_rule['background_color'] : '#000000',
				'background_url' => isset($raw_rule['background_url']) ? (string) $raw_rule['background_url'] : '',
				'logo_url' => isset($raw_rule['logo_url']) ? (string) $raw_rule['logo_url'] : '',
				'logo_width' => isset($raw_rule['logo_width']) ? (string) $raw_rule['logo_width'] : '',
				'message' => isset($raw_rule['message']) ? (string) $raw_rule['message'] : '',
				'text_color' => isset($raw_rule['text_color']) ? (string) $raw_rule['text_color'] : '',
				'redirect_url' => isset($raw_rule['redirect_url']) ? (string) $raw_rule['redirect_url'] : '',
			];
		}

		if (empty($rules)) {
			$rules[] = [
				'match_urls' => isset($settings['block_pages_by_url_string_match_urls']) ? (string) $settings['block_pages_by_url_string_match_urls'] : '',
				'exception_urls' => isset($settings['block_pages_by_url_string_exception_urls']) ? (string) $settings['block_pages_by_url_string_exception_urls'] : '',
				'usernames' => '',
				'roles' => [],
				'background_color' => isset($settings['block_pages_by_url_string_background_color']) ? (string) $settings['block_pages_by_url_string_background_color'] : '#000000',
				'background_url' => isset($settings['block_pages_by_url_string_background_url']) ? (string) $settings['block_pages_by_url_string_background_url'] : '',
				'logo_url' => isset($settings['block_pages_by_url_string_logo_url']) ? (string) $settings['block_pages_by_url_string_logo_url'] : '',
				'logo_width' => isset($settings['block_pages_by_url_string_logo_width']) ? (string) $settings['block_pages_by_url_string_logo_width'] : '',
				'message' => isset($settings['block_pages_by_url_string_message']) ? (string) $settings['block_pages_by_url_string_message'] : '',
				'text_color' => isset($settings['block_pages_by_url_string_text_color']) ? (string) $settings['block_pages_by_url_string_text_color'] : '',
				'redirect_url' => isset($settings['block_pages_by_url_string_redirect_url']) ? (string) $settings['block_pages_by_url_string_redirect_url'] : '',
			];
		}

		return $rules;
	}
}

