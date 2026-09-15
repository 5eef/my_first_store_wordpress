<?php
/** Install official Arabic language packs for WordPress and active plugins. */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
require dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/translation-install.php';
require_once ABSPATH . 'wp-admin/includes/update.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

$core = wp_download_language_pack( 'ar' );
fwrite( STDOUT, 'WordPress ar: ' . ( $core ? 'installed' : 'already installed or unavailable' ) . PHP_EOL );

wp_update_plugins();
$updates = array_values(
	array_filter(
		wp_get_translation_updates(),
		static fn( object $update ): bool => 'plugin' === $update->type && 'woocommerce' === $update->slug && 'ar' === $update->language
	)
);

if ( $updates ) {
	$upgrader = new Language_Pack_Upgrader( new Automatic_Upgrader_Skin() );
	$result   = $upgrader->bulk_upgrade( $updates );
	fwrite( STDOUT, 'WooCommerce ar: ' . ( is_array( $result ) && ! in_array( false, $result, true ) ? 'installed' : 'failed' ) . PHP_EOL );
} else {
	fwrite( STDOUT, "WooCommerce ar: no pending pack (installed or unavailable)\n" );
}
