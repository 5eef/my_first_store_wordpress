<?php
/**
 * Idempotent setup for WooCommerce, SEEF Store Core and the SEEF Store theme.
 * Usage: C:\xampp\php\php.exe tools\configure-store.php
 */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

require dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$seed_phase  = in_array( '--seed', $argv, true );
$woocommerce = 'woocommerce/woocommerce.php';
$core        = 'seef-store-core/seef-store-core.php';

if ( $seed_phase ) {
	if ( ! is_plugin_active( $woocommerce ) || ! is_plugin_active( $core ) ) {
		fwrite( STDERR, "WooCommerce and SEEF Store Core must be active before seeding.\n" );
		exit( 1 );
	}
	if ( ! did_action( 'woocommerce_init' ) || ! function_exists( 'WC' ) || ! WC() || ! ( WC()->countries instanceof WC_Countries ) ) {
		fwrite( STDERR, "WooCommerce did not finish initializing in the fresh WordPress process.\n" );
		exit( 1 );
	}
	if ( ! SeefStore\Setup\DemoSeeder::is_allowed() ) {
		fwrite( STDERR, "Demo seeding requires SEEF_DEMO_MODE=true and WP_ENVIRONMENT_TYPE=local or development.\n" );
		exit( 1 );
	}
	if ( ! SeefStore\Setup\DemoSeeder::run( true ) ) {
		fwrite( STDERR, "Demo seeding did not complete.\n" );
		exit( 1 );
	}
	flush_rewrite_rules();
	fwrite( STDOUT, "SEEF STORE demo data configured.\n" );
	exit( 0 );
}

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
flush_rewrite_rules();

$command = array( PHP_BINARY, __FILE__, '--seed' );
$pipes   = array();
$process = proc_open(
	$command,
	array( 0 => STDIN, 1 => STDOUT, 2 => STDERR ),
	$pipes,
	dirname( __DIR__ ),
	null,
	array( 'bypass_shell' => true )
);

if ( ! is_resource( $process ) ) {
	fwrite( STDERR, "Unable to start the fresh WordPress seeding process.\n" );
	exit( 1 );
}

$status = proc_close( $process );
exit( $status );
