<?php
/**
 * Checkout: Ship To Pre-Defined Addresses
 *
 * Renders a dropdown of predefined addresses on checkout and overwrites
 * shipping (and optionally billing) with the selected address.
 */

if (!defined('ABSPATH')) {
	exit;
}

class User_Manager_Checkout_Ship_To_Predefined {

	/**
	 * Parsed addresses (location_name, address_1, address_2, city, state, zip, country).
	 *
	 * @var array<int, array<string, string>>
	 */
	private static $addresses = [];

	/**
	 * Settings.
	 *
	 * @var array<string, mixed>
	 */
	private static $settings = [];

	/**
	 * Init: register hooks when enabled and WooCommerce active.
	 * Like the old SWH implementation: register hooks whenever the feature is enabled; parse address list when rendering.
	 */
	/** Set to true when init() passes WooCommerce + (feature or debug) and registers hooks. */
	private static $init_hooks_registered = false;

	public static function init(): void {
		if (!class_exists('WooCommerce')) {
			return;
		}
		$settings = User_Manager_Core::get_settings();
		$feature_on = !empty($settings['checkout_ship_to_predefined_enabled']);
		$debug_on   = !empty($settings['checkout_ship_to_show_debug']);
		if (!$feature_on && !$debug_on) {
			return;
		}
		self::$settings = $settings;
		self::$init_hooks_registered = true;
		// Parse once so we have addresses for validation/order meta; do NOT skip registering hooks if empty.
		self::parse_address_list();

		$hook = $settings['checkout_ship_to_field_hook'] ?? 'woocommerce_after_order_notes';
		// Dropdown on the user-chosen hook; one fallback at end of customer details if theme never fires the chosen hook.
		if ($feature_on) {
			add_action($hook, [__CLASS__, 'render_dropdown'], 10, 1);
			add_filter($hook, [__CLASS__, 'render_dropdown_filter'], 10, 1);
			// Fallback only after customer details (so "Checkout field location" controls position when theme fires that hook).
			add_action('woocommerce_checkout_after_customer_details', [__CLASS__, 'render_dropdown_fallback'], 10, 0);
		}
		if ($debug_on) {
			add_action('woocommerce_checkout_before_customer_details', [__CLASS__, 'render_debug_box'], 1, 0);
			add_action('woocommerce_before_checkout_form', [__CLASS__, 'render_debug_box'], 1, 0);
			add_action('wp_footer', [__CLASS__, 'render_debug_box_footer'], 5, 0);
		}
		if ($feature_on) {
			add_action('wp_head', [__CLASS__, 'css_ensure_ship_to_visible'], 99);
			add_action('wp_footer', [__CLASS__, 'maybe_inline_scripts'], 19);
			add_action('woocommerce_checkout_process', [__CLASS__, 'validate_selection'], 10);
			add_action('woocommerce_checkout_update_order_meta', [__CLASS__, 'apply_selected_address_to_order'], 10, 2);
			add_filter('woocommerce_checkout_posted_data', [__CLASS__, 'override_posted_data'], 10, 1);
		}
		if ($feature_on && !empty($settings['checkout_ship_to_auto_hide_shipping'])) {
			add_action('wp_head', [__CLASS__, 'css_auto_hide_shipping'], 99);
		}
		if ($feature_on && !empty($settings['checkout_ship_to_auto_hide_company'])) {
			add_action('wp_head', [__CLASS__, 'css_auto_hide_company'], 99);
		}
		if ($feature_on && !empty($settings['checkout_ship_to_auto_hide_notes'])) {
			add_action('wp_head', [__CLASS__, 'css_auto_hide_notes'], 99);
		}
		if ($feature_on && !empty($settings['checkout_ship_to_hide_coupon'])) {
			add_action('wp_head', [__CLASS__, 'css_hide_coupon'], 99);
		}
		if ($feature_on && !empty($settings['checkout_ship_to_single_column']) && $settings['checkout_ship_to_single_column'] === 'yes') {
			add_action('wp_head', [__CLASS__, 'css_single_column_checkout'], 99);
		}
	}

	/**
	 * Parse the address list (tab-separated: location_name, address_1, address_2, city, state, zip, country).
	 */
	private static function parse_address_list(): void {
		$raw = self::$settings['checkout_ship_to_address_list'] ?? '';
		$lines = preg_split('/\r\n|\r|\n/', $raw);
		self::$addresses = [];
		$index = 1;
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line === '') {
				continue;
			}
			$cells = preg_split('/\t/', $line, 7);
			$loc = isset($cells[0]) ? trim($cells[0]) : '';
			$addr1 = isset($cells[1]) ? trim($cells[1]) : '';
			$addr2 = isset($cells[2]) ? trim($cells[2]) : '';
			$city = isset($cells[3]) ? trim($cells[3]) : '';
			$state = isset($cells[4]) ? trim($cells[4]) : '';
			$zip = isset($cells[5]) ? trim($cells[5]) : '';
			$country = isset($cells[6]) ? trim($cells[6]) : 'US';
			if ($loc === '' && $addr1 === '') {
				continue;
			}
			self::$addresses[$index] = [
				'location_name' => $loc,
				'address_1'     => $addr1,
				'address_2'     => $addr2,
				'city'          => $city,
				'state'         => $state,
				'zip'           => $zip,
				'country'       => $country,
			];
			$index++;
		}
	}

	/** Whether the dropdown has already been output (avoid duplicate when both main hook and fallback run). */
	private static $dropdown_rendered = false;

	/** Whether the debug box has already been output (avoid duplicate when multiple hooks fire). */
	private static $debug_box_rendered = false;

	/**
	 * Filter wrapper so we run when theme uses apply_filters (e.g. old SWH implementation used add_filter).
	 *
	 * @param mixed $value First argument from the hook (often the checkout object).
	 * @return mixed Same value so the filter chain continues.
	 */
	public static function render_dropdown_filter($value) {
		self::render_dropdown($value);
		return $value;
	}

	/**
	 * Fallback hook: woocommerce_checkout_after_customer_details runs in form-checkout.php even when themes omit woocommerce_after_order_notes.
	 */
	public static function render_dropdown_fallback(): void {
		if (self::$dropdown_rendered) {
			return;
		}
		$checkout = function_exists('WC') && WC() && is_callable([WC(), 'checkout']) ? WC()->checkout() : null;
		self::render_dropdown($checkout);
	}

	/**
	 * Footer fallback: show debug box on checkout when theme does not fire before_customer_details / before_checkout_form.
	 */
	public static function render_debug_box_footer(): void {
		if (!function_exists('is_checkout') || !is_checkout()) {
			return;
		}
		self::render_debug_box();
	}

	/**
	 * Standalone debug box: called from Core wp_footer when "Show debugging info" is on.
	 * Does not depend on Ship To init() having run; reads $settings and parses addresses for display.
	 *
	 * @param array<string, mixed> $settings Full options array (e.g. get_option(User_Manager_Core::OPTION_KEY)).
	 */
	public static function render_debug_box_standalone(array $settings): void {
		if (self::$debug_box_rendered) {
			return;
		}
		if (!current_user_can('manage_options')) {
			return;
		}
		self::$debug_box_rendered = true;
		$option_key = class_exists('User_Manager_Core') ? User_Manager_Core::OPTION_KEY : 'user_manager_settings';
		$hook = $settings['checkout_ship_to_field_hook'] ?? 'woocommerce_after_order_notes';
		$raw_list = $settings['checkout_ship_to_address_list'] ?? '';
		// Parse addresses for display (same logic as parse_address_list).
		$addresses_parsed = [];
		$lines = preg_split('/\r\n|\r|\n/', $raw_list);
		$index = 1;
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line === '') {
				continue;
			}
			$cells = preg_split('/\t/', $line, 7);
			$loc   = isset($cells[0]) ? trim($cells[0]) : '';
			$addr1 = isset($cells[1]) ? trim($cells[1]) : '';
			$addr2 = isset($cells[2]) ? trim($cells[2]) : '';
			$city  = isset($cells[3]) ? trim($cells[3]) : '';
			$state = isset($cells[4]) ? trim($cells[4]) : '';
			$zip   = isset($cells[5]) ? trim($cells[5]) : '';
			if ($loc === '' && $addr1 === '') {
				continue;
			}
			$addresses_parsed[$index] = $loc . ' | ' . $addr1 . ' | ' . $city . ' ' . $state . ' ' . $zip;
			$index++;
		}
		$checkout_keys = array_filter(array_keys($settings), function ($k) {
			return strpos((string) $k, 'checkout_ship_to_') === 0;
		});
		$checkout_settings = [];
		foreach ($checkout_keys as $k) {
			$v = $settings[ $k ];
			if ($k === 'checkout_ship_to_address_list') {
				$checkout_settings[ $k ] = '(length: ' . strlen((string) $v) . ') "' . esc_html(substr((string) $v, 0, 200)) . (strlen((string) $v) > 200 ? '…' : '') . '"';
			} else {
				$checkout_settings[ $k ] = is_bool($v) ? ($v ? 'true' : 'false') : (string) $v;
			}
		}
		?>
		<div id="um-shipto-debug-box" class="um-shipto-debug" style="margin: 1em 0; padding: 12px 16px; background: #1d2327; color: #f0f0f1; font-family: monospace; font-size: 12px; line-height: 1.5; border: 1px solid #3c434a; border-radius: 4px; overflow-x: auto;">
			<div style="font-weight: 700; margin-bottom: 8px; color: #72aee6;">User Manager — Ship To Pre-Defined Addresses (Debug)</div>
			<div style="margin-bottom: 8px; font-size: 11px; color: #dba617;">Rendered via Core wp_footer (standalone — does not require Ship To init)</div>
			<table style="width: 100%; border-collapse: collapse;">
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Feature enabled</td><td><?php echo !empty($settings['checkout_ship_to_predefined_enabled']) ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Debug (show for admins) enabled</td><td><?php echo !empty($settings['checkout_ship_to_show_debug']) ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">WooCommerce active</td><td><?php echo class_exists('WooCommerce') ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Current user can manage_options</td><td><?php echo current_user_can('manage_options') ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">is_checkout()</td><td><?php echo function_exists('is_checkout') && is_checkout() ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Settings option key</td><td><code><?php echo esc_html($option_key); ?></code></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Ship To init() registered hooks</td><td><?php echo self::$init_hooks_registered ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Main hook (action + filter)</td><td><code><?php echo esc_html($hook); ?></code></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">has_action( main hook )</td><td><?php echo has_action($hook) !== false ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">has_filter( main hook )</td><td><?php echo has_filter($hook) !== false ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Parsed addresses count</td><td><?php echo count($addresses_parsed); ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Raw address list length</td><td><?php echo strlen($raw_list); ?> chars</td></tr>
			</table>
			<div style="margin-top: 12px; font-weight: 700; color: #a7aaad;">All checkout_ship_to_* settings (from DB)</div>
			<pre style="margin: 4px 0 0; padding: 8px; background: #0d1117; border-radius: 3px; white-space: pre-wrap; word-break: break-all;"><?php echo esc_html(print_r($checkout_settings, true)); ?></pre>
			<?php if (!empty($addresses_parsed)) : ?>
				<div style="margin-top: 12px; font-weight: 700; color: #a7aaad;">Parsed addresses (index => location | address | city state zip)</div>
				<pre style="margin: 4px 0 0; padding: 8px; background: #0d1117; border-radius: 3px; white-space: pre-wrap; word-break: break-all;"><?php echo esc_html(print_r($addresses_parsed, true)); ?></pre>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a detailed debug box on checkout for admins (when "Show debugging info for admins" is enabled).
	 */
	public static function render_debug_box(): void {
		if (self::$debug_box_rendered) {
			return;
		}
		if (!current_user_can('manage_options')) {
			return;
		}
		self::$debug_box_rendered = true;
		$settings = self::$settings;
		$hook = $settings['checkout_ship_to_field_hook'] ?? 'woocommerce_after_order_notes';
		$raw_list = $settings['checkout_ship_to_address_list'] ?? '';
		$address_count = count(self::$addresses);
		$option_key = class_exists('User_Manager_Core') ? User_Manager_Core::OPTION_KEY : 'user_manager_settings';
		$all_options = get_option($option_key, []);
		$checkout_keys = array_filter(array_keys($all_options), function ($k) {
			return strpos($k, 'checkout_ship_to_') === 0;
		});
		$checkout_settings = [];
		foreach ($checkout_keys as $k) {
			$v = $all_options[ $k ];
			if ($k === 'checkout_ship_to_address_list') {
				$checkout_settings[ $k ] = '(length: ' . strlen($v) . ') "' . esc_html(substr($v, 0, 200)) . (strlen($v) > 200 ? '…' : '') . '"';
			} else {
				$checkout_settings[ $k ] = is_bool($v) ? ($v ? 'true' : 'false') : (string) $v;
			}
		}
		?>
		<div id="um-shipto-debug-box" class="um-shipto-debug" style="margin: 1em 0; padding: 12px 16px; background: #1d2327; color: #f0f0f1; font-family: monospace; font-size: 12px; line-height: 1.5; border: 1px solid #3c434a; border-radius: 4px; overflow-x: auto;">
			<div style="font-weight: 700; margin-bottom: 8px; color: #72aee6;">User Manager — Ship To Pre-Defined Addresses (Debug)</div>
			<table style="width: 100%; border-collapse: collapse;">
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Feature enabled</td><td><?php echo !empty($settings['checkout_ship_to_predefined_enabled']) ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">WooCommerce active</td><td><?php echo class_exists('WooCommerce') ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Current user can manage_options</td><td><?php echo current_user_can('manage_options') ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">is_checkout()</td><td><?php echo function_exists('is_checkout') && is_checkout() ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Settings option key</td><td><code><?php echo esc_html($option_key); ?></code></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Main hook (action + filter)</td><td><code><?php echo esc_html($hook); ?></code></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">has_action( main hook )</td><td><?php echo has_action($hook) !== false ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">has_filter( main hook )</td><td><?php echo has_filter($hook) !== false ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Parsed addresses count</td><td><?php echo (int) $address_count; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Raw address list length</td><td><?php echo (int) strlen($raw_list); ?> chars</td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Dropdown already rendered</td><td><?php echo self::$dropdown_rendered ? 'yes' : 'no'; ?></td></tr>
				<tr><td style="padding: 2px 8px 2px 0; vertical-align: top; color: #a7aaad;">Ship To init() registered hooks</td><td><?php echo self::$init_hooks_registered ? 'yes' : 'no'; ?></td></tr>
			</table>
			<div style="margin-top: 12px; font-weight: 700; color: #a7aaad;">All checkout_ship_to_* settings (from DB)</div>
			<pre style="margin: 4px 0 0; padding: 8px; background: #0d1117; border-radius: 3px; white-space: pre-wrap; word-break: break-all;"><?php echo esc_html(print_r($checkout_settings, true)); ?></pre>
			<?php if ($address_count > 0) : ?>
				<div style="margin-top: 12px; font-weight: 700; color: #a7aaad;">Parsed addresses (index => location_name, address_1, city, state, zip)</div>
				<pre style="margin: 4px 0 0; padding: 8px; background: #0d1117; border-radius: 3px; white-space: pre-wrap; word-break: break-all;"><?php
					$summary = [];
					foreach (self::$addresses as $idx => $addr) {
						$summary[ $idx ] = $addr['location_name'] . ' | ' . $addr['address_1'] . ' | ' . $addr['city'] . ' ' . $addr['state'] . ' ' . $addr['zip'];
					}
					echo esc_html(print_r($summary, true));
				?></pre>
			<?php endif; ?>
			<div style="margin-top: 8px; font-size: 11px; color: #8c8f94;">Rendered via <?php echo esc_html(self::get_debug_render_source()); ?></div>
		</div>
		<?php
	}

	/**
	 * Which hook triggered the debug box (for troubleshooting).
	 */
	private static function get_debug_render_source(): string {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
		foreach ($trace as $i => $frame) {
			if (isset($frame['function']) && $frame['function'] === 'do_action' && isset($frame['args'][0])) {
				return 'do_action(' . $frame['args'][0] . ')';
			}
		}
		return 'wp_footer or unknown';
	}

	/**
	 * Render the dropdown and optional section title.
	 * Uses woocommerce_form_field when $checkout is provided for proper WC checkout integration (like the old SWH implementation).
	 *
	 * @param \WC_Checkout|null $checkout Checkout object when called from WooCommerce hook (optional).
	 */
	public static function render_dropdown($checkout = null): void {
		if (self::$dropdown_rendered) {
			return;
		}
		// Ensure we have parsed addresses (in case init ran before settings were fully loaded).
		if (empty(self::$addresses)) {
			self::parse_address_list();
		}
		$please_select = self::$settings['checkout_ship_to_please_select'] ?? __('Please select', 'user-manager');
		$field_label = self::$settings['checkout_ship_to_field_label'] ?? __('Choose your office.', 'user-manager');
		$area_title = self::$settings['checkout_ship_to_area_title'] ?? __('Shipping Location', 'user-manager');
		$required = (self::$settings['checkout_ship_to_selection_required'] ?? 'no') === 'yes';

		$options = [ 0 => $please_select ];
		foreach (self::$addresses as $idx => $addr) {
			$label = $addr['location_name'] . ' – ' . $addr['address_1'] . ($addr['address_2'] ? ', ' . $addr['address_2'] : '') . ', ' . $addr['city'] . ' ' . $addr['state'] . ' ' . $addr['zip'];
			$options[ (int) $idx ] = $label;
		}
		if (count($options) === 1) {
			$options[0] = empty(self::$addresses) ? __('No addresses configured.', 'user-manager') : $please_select;
		}

		$saved_value = $checkout && is_callable([$checkout, 'get_value']) ? $checkout->get_value('um_predefined_address_selection') : null;
		if ($saved_value === null) {
			$saved_value = 0;
		}
		?>
		<div id="um-shipto-predefined-container" class="um-shipto-predefined" style="display: block !important; visibility: visible !important; margin: 1em 0;">
			<?php if ($area_title !== '' && strtolower($area_title) !== 'blank') : ?>
				<h2 class="um-shipto-predefined-title"><?php echo esc_html($area_title); ?></h2>
			<?php endif; ?>
			<?php
			if ($checkout && function_exists('woocommerce_form_field')) {
				woocommerce_form_field('um_predefined_address_selection', [
					'type'     => 'select',
					'class'    => ['form-row-wide'],
					'label'    => $field_label,
					'required' => $required,
					'options'  => $options,
				], $saved_value);
			} else {
				?>
				<p class="form-row form-row-wide" id="um_predefined_address_selection_field">
					<label for="um_predefined_address_selection">
						<?php echo esc_html($field_label); ?>
						<?php if ($required) : ?><abbr class="required" title="required">*</abbr><?php endif; ?>
					</label>
					<span class="woocommerce-input-wrapper">
						<select name="um_predefined_address_selection" id="um_predefined_address_selection" class="select">
							<?php foreach ($options as $val => $label) : ?>
								<option value="<?php echo (int) $val; ?>" <?php selected((int) $saved_value, (int) $val); ?>><?php echo esc_html($label); ?></option>
							<?php endforeach; ?>
						</select>
					</span>
				</p>
				<?php
			}
			?>
		</div>
		<?php
		self::$dropdown_rendered = true;
	}

	/**
	 * Inline script: on change, fill shipping (and billing) fields; apply default address when empty.
	 */
	public static function maybe_inline_scripts(): void {
		if (!is_checkout()) {
			return;
		}
		$overwrite_billing = (self::$settings['checkout_ship_to_overwrite_billing'] ?? 'no') === 'yes';
		$overwrite_shipping = self::$settings['checkout_ship_to_overwrite_shipping'] ?? 'yes';
		$company_override = self::$settings['checkout_ship_to_company_override'] ?? '';
		$first_name_prefix = self::$settings['checkout_ship_to_first_name_prefix'] ?? '';
		$defaults = [
			'address_1' => self::$settings['checkout_ship_to_default_address'] ?? '',
			'city'      => self::$settings['checkout_ship_to_default_city'] ?? '',
			'state'     => self::$settings['checkout_ship_to_default_state'] ?? 'MN',
			'zip'       => self::$settings['checkout_ship_to_default_zip'] ?? '',
			'country'   => self::$settings['checkout_ship_to_default_country'] ?? 'US',
		];
		?>
		<script>
		(function() {
			var addresses = <?php echo wp_json_encode(self::$addresses); ?>;
			var overwriteBilling = <?php echo $overwrite_billing ? 'true' : 'false'; ?>;
			var overwriteShipping = <?php echo wp_json_encode($overwrite_shipping); ?>;
			var companyOverride = <?php echo wp_json_encode($company_override); ?>;
			var firstNamePrefix = <?php echo wp_json_encode($first_name_prefix); ?>;
			var defaults = <?php echo wp_json_encode($defaults); ?>;

			function applyAddressToFields(addr, prefix) {
				if (!addr) return;
				var p = prefix || '';
				var el = function(id) { return document.getElementById(p + id); };
				if (el('address_1')) el('address_1').value = addr.address_1 || '';
				if (el('address_2')) el('address_2').value = addr.address_2 || '';
				if (el('city')) el('city').value = addr.city || '';
				if (el('state')) { el('state').value = addr.state || ''; if (typeof jQuery !== 'undefined') jQuery(el('state')).trigger('change'); }
				if (el('postcode')) el('postcode').value = addr.zip || '';
				if (el('country')) { el('country').value = addr.country || 'US'; if (typeof jQuery !== 'undefined') jQuery(el('country')).trigger('change'); }
			}

			function applyDefaults(prefix) {
				applyAddressToFields(defaults, prefix);
			}

			function run() {
				var sel = document.getElementById('um_predefined_address_selection');
				if (!sel) return;
				sel.addEventListener('change', function() {
					var idx = parseInt(this.value, 10);
					var shipToDifferentEl = document.getElementById('ship-to-different-address-checkbox');
					var shipToDifferent = shipToDifferentEl && shipToDifferentEl.checked;
					var useShipping = (overwriteShipping === 'yes') || (overwriteShipping === 'yes_if_same' && !shipToDifferent);
					if (overwriteShipping === 'no') useShipping = false;

					if (idx > 0 && addresses[idx]) {
						applyAddressToFields(addresses[idx], 'shipping_');
						if (overwriteBilling) {
							applyAddressToFields(addresses[idx], 'billing_');
							if (companyOverride && document.getElementById('billing_company')) document.getElementById('billing_company').value = companyOverride;
							if (companyOverride && document.getElementById('shipping_company')) document.getElementById('shipping_company').value = companyOverride;
							var fn = document.getElementById('shipping_first_name');
							if (firstNamePrefix && fn && fn.value && fn.value.indexOf(firstNamePrefix) !== 0) fn.value = firstNamePrefix + fn.value.replace(new RegExp('^' + firstNamePrefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')), '');
						}
					} else {
						applyDefaults('shipping_');
						if (overwriteBilling) applyDefaults('billing_');
					}
				});
			}
			if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
			else run();
		})();
		</script>
		<?php
	}

	/**
	 * Validate selection when required.
	 */
	public static function validate_selection(): void {
		$required = (self::$settings['checkout_ship_to_selection_required'] ?? 'no') === 'yes';
		if (!$required) {
			return;
		}
		$val = isset($_POST['um_predefined_address_selection']) ? (int) $_POST['um_predefined_address_selection'] : 0;
		if ($val < 1) {
			$msg = self::$settings['checkout_ship_to_required_error'] ?? __('Please make a selection.', 'user-manager');
			wc_add_notice($msg, 'error');
		}
	}

	/**
	 * Override posted checkout data with the selected predefined address so order gets correct address.
	 */
	public static function override_posted_data(array $data): array {
		$idx = isset($_POST['um_predefined_address_selection']) ? (int) $_POST['um_predefined_address_selection'] : 0;
		if ($idx < 1 || !isset(self::$addresses[$idx])) {
			return $data;
		}
		$addr = self::$addresses[$idx];
		$overwrite_billing = (self::$settings['checkout_ship_to_overwrite_billing'] ?? 'no') === 'yes';
		$overwrite_shipping = self::$settings['checkout_ship_to_overwrite_shipping'] ?? 'yes';
		$ship_to_different = !empty($_POST['ship_to_different_address']);
		$apply_shipping = ($overwrite_shipping === 'yes') || ($overwrite_shipping === 'yes_if_same' && !$ship_to_different);

		if ($apply_shipping) {
			$data['shipping_address_1'] = $addr['address_1'];
			$data['shipping_address_2'] = $addr['address_2'];
			$data['shipping_city'] = $addr['city'];
			$data['shipping_state'] = $addr['state'];
			$data['shipping_postcode'] = $addr['zip'];
			$data['shipping_country'] = $addr['country'];
			$company = self::$settings['checkout_ship_to_company_override'] ?? '';
			if ($company !== '') {
				$data['shipping_company'] = $company;
			}
		}
		if ($overwrite_billing) {
			$data['billing_address_1'] = $addr['address_1'];
			$data['billing_address_2'] = $addr['address_2'];
			$data['billing_city'] = $addr['city'];
			$data['billing_state'] = $addr['state'];
			$data['billing_postcode'] = $addr['zip'];
			$data['billing_country'] = $addr['country'];
			$company = self::$settings['checkout_ship_to_company_override'] ?? '';
			if ($company !== '') {
				$data['billing_company'] = $company;
			}
		}
		return $data;
	}

	/**
	 * Apply selected address to order (meta and address on the order object).
	 */
	public static function apply_selected_address_to_order(int $order_id, array $posted): void {
		$idx = isset($_POST['um_predefined_address_selection']) ? (int) $_POST['um_predefined_address_selection'] : 0;
		if ($idx < 1 || !isset(self::$addresses[$idx])) {
			return;
		}
		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}
		$addr = self::$addresses[$idx];
		$overwrite_billing = (self::$settings['checkout_ship_to_overwrite_billing'] ?? 'no') === 'yes';
		$overwrite_shipping = self::$settings['checkout_ship_to_overwrite_shipping'] ?? 'yes';
		$ship_to_different = !empty($_POST['ship_to_different_address']);
		$apply_shipping = ($overwrite_shipping === 'yes') || ($overwrite_shipping === 'yes_if_same' && !$ship_to_different);
		$company = self::$settings['checkout_ship_to_company_override'] ?? '';

		if ($apply_shipping) {
			$order->set_shipping_address_1($addr['address_1']);
			$order->set_shipping_address_2($addr['address_2']);
			$order->set_shipping_city($addr['city']);
			$order->set_shipping_state($addr['state']);
			$order->set_shipping_postcode($addr['zip']);
			$order->set_shipping_country($addr['country']);
			if ($company !== '') {
				$order->set_shipping_company($company);
			}
		}
		if ($overwrite_billing) {
			$order->set_billing_address_1($addr['address_1']);
			$order->set_billing_address_2($addr['address_2']);
			$order->set_billing_city($addr['city']);
			$order->set_billing_state($addr['state']);
			$order->set_billing_postcode($addr['zip']);
			$order->set_billing_country($addr['country']);
			if ($company !== '') {
				$order->set_billing_company($company);
			}
		}
		$order->save();
	}

	/** Ensure Shipping Location block is never hidden by theme/plugin CSS. */
	public static function css_ensure_ship_to_visible(): void {
		if (!function_exists('is_checkout') || !is_checkout()) {
			return;
		}
		echo '<style id="um-shipto-ensure-visible">#um-shipto-predefined-container, #um-shipto-predefined-container .um-shipto-predefined-title, #um_predefined_address_selection_field { display: block !important; visibility: visible !important; opacity: 1 !important; }</style>';
	}

	public static function css_auto_hide_shipping(): void {
		if (!is_checkout()) return;
		echo '<style>.woocommerce-shipping-fields .shipping_address { display: none !important; } #ship-to-different-address { display: none !important; } #um-shipto-predefined-container { display: block !important; }</style>';
	}

	public static function css_auto_hide_company(): void {
		if (!is_checkout()) return;
		echo '<style>#billing_company_field, #shipping_company_field { display: none !important; }</style>';
	}

	public static function css_auto_hide_notes(): void {
		if (!is_checkout()) return;
		echo '<style>.woocommerce-additional-fields { display: none !important; }</style>';
	}

	public static function css_hide_coupon(): void {
		if (!is_checkout()) return;
		echo '<style>.woocommerce-form-coupon-toggle, .coupon { display: none !important; }</style>';
	}

	public static function css_single_column_checkout(): void {
		if (!is_checkout()) return;
		echo '<style>.woocommerce-checkout .col2-set { display: block !important; } .woocommerce-checkout .col2-set .col-1, .woocommerce-checkout .col2-set .col-2 { width: 100% !important; float: none !important; }</style>';
	}
}
