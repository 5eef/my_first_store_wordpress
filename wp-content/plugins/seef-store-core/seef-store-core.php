<?php
/**
 * Plugin Name: SEEF Store Core
 * Update URI: https://github.com/5eef/seef-store-core
 * Description: Business features, demo data, contact management and security for SEEF STORE.
 * Version: 1.1.2
 * Author: Youssef BOUGHIOUL
 * Text Domain: seef-store-core
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SEEF_STORE_CORE_VERSION', '1.1.2' );
define( 'SEEF_STORE_CORE_FILE', __FILE__ );
define( 'SEEF_STORE_CORE_PATH', plugin_dir_path( __FILE__ ) );

add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'SeefStore\\';
		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}

		$relative = str_replace( '\\', DIRECTORY_SEPARATOR, substr( $class, strlen( $prefix ) ) );
		$file     = SEEF_STORE_CORE_PATH . 'src/' . $relative . '.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

register_activation_hook( __FILE__, array( SeefStore\Setup\Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( SeefStore\Setup\Activator::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		SeefStore\Plugin::instance()->boot();
	},
	5
);
