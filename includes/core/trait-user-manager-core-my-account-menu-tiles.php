<?php
/**
 * My Account Menu Tiles helpers.
 */

if (!defined('ABSPATH')) {
	exit;
}

trait User_Manager_Core_My_Account_Menu_Tiles_Trait {

	/**
	 * Register frontend hooks for My Account Menu Tiles add-on.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	public static function maybe_boot_my_account_menu_tiles(array $settings): void {
		if (empty($settings['my_account_menu_tiles_enabled'])) {
			return;
		}

		add_action('woocommerce_account_dashboard', [__CLASS__, 'render_my_account_menu_tiles'], 20);
	}

	/**
	 * Render tile layout of WooCommerce account endpoints on dashboard endpoint.
	 */
	public static function render_my_account_menu_tiles(): void {
		if (!function_exists('wc_get_account_menu_items') || !function_exists('wc_get_account_endpoint_url')) {
			return;
		}

		$items = wc_get_account_menu_items();
		if (!is_array($items) || empty($items)) {
			return;
		}

		$settings = self::get_settings();
		$tiles_per_row = isset($settings['my_account_menu_tiles_per_row']) ? absint($settings['my_account_menu_tiles_per_row']) : 4;
		if ($tiles_per_row < 1) {
			$tiles_per_row = 4;
		}

		$min_height = isset($settings['my_account_menu_tiles_min_height']) ? absint($settings['my_account_menu_tiles_min_height']) : 80;
		if ($min_height < 1) {
			$min_height = 80;
		}
		?>
		<style>
		.um-my-account-tiles-wrapper {
			margin: 30px 0;
		}
		.um-my-account-tiles {
			display: grid;
			grid-template-columns: repeat(<?php echo esc_attr((string) $tiles_per_row); ?>, 1fr);
			gap: 15px;
		}
		.um-my-account-tile {
			background-color: #f5f5f5;
			border-radius: 5px;
			padding: 30px 20px;
			text-align: center;
			text-decoration: none;
			display: flex;
			align-items: center;
			justify-content: center;
			transition: background-color 0.3s ease;
			min-height: <?php echo esc_attr((string) $min_height); ?>px;
		}
		.um-my-account-tile:hover {
			background-color: #e8e8e8;
			text-decoration: none;
		}
		@media (max-width: 768px) {
			.um-my-account-tiles {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		@media (max-width: 480px) {
			.um-my-account-tiles {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		</style>
		<div class="um-my-account-tiles-wrapper">
			<div class="um-my-account-tiles">
				<?php foreach ($items as $endpoint => $label) : ?>
					<?php
					$endpoint = sanitize_key((string) $endpoint);
					$url = wc_get_account_endpoint_url($endpoint);
					?>
					<a href="<?php echo esc_url($url); ?>" class="um-my-account-tile um-my-account-tile-<?php echo esc_attr($endpoint); ?>">
						<?php echo esc_html((string) $label); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}

