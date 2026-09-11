<?php
/**
 * Idempotent setup for WooCommerce, SEEF Store Core and the SEEF Store theme.
 * Usage: C:\xampp\php\php.exe tools\configure-store.php
 */

declare(strict_types=1);

require dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$woocommerce = 'woocommerce/woocommerce.php';
$core        = 'seef-store-core/seef-store-core.php';

foreach ( array( $woocommerce, $core ) as $plugin ) {
	if ( ! is_plugin_active( $plugin ) ) {
		$result = activate_plugin( $plugin );
		if ( is_wp_error( $result ) ) {
			fwrite( STDERR, sprintf( "Activation failed for %s: %s\n", $plugin, $result->get_error_message() ) );
			exit( 1 );
		}
		fwrite( STDOUT, sprintf( "Activated %s\n", $plugin ) );
	}
}

switch_theme( 'seef-store' );
SeefStore\Setup\DemoSeeder::run( true );
flush_rewrite_rules();

fwrite( STDOUT, "SEEF STORE configured.\n" );
