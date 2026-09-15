<?php
/** Theme helper checks without WooCommerce loaded. */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

function home_url( string $path = '/' ): string {
	return 'https://example.test' . $path;
}

function get_option( string $option ): mixed {
	return 0;
}

function get_permalink( int $post_id ): false {
	return false;
}

require dirname( __DIR__ ) . '/wp-content/themes/seef-store/inc/template-tags.php';

$failures = 0;
$check = static function ( bool $condition, string $label ) use ( &$failures ): void {
	echo '[' . ( $condition ? 'PASS' : 'FAIL' ) . '] ' . $label . PHP_EOL;
	$failures += $condition ? 0 : 1;
};

$check( 'https://example.test/boutique/' === seef_store_wc_url( 'shop' ), 'Shop helper falls back without WooCommerce' );
$check( 'https://example.test/panier/' === seef_store_wc_url( 'cart' ), 'Cart helper falls back without WooCommerce' );
$check( 'https://example.test/commande/' === seef_store_wc_url( 'checkout' ), 'Checkout helper falls back without WooCommerce' );
$check( 'https://example.test/mon-compte/' === seef_store_wc_url( 'myaccount' ), 'Account helper falls back without WooCommerce' );
$check( 0 === seef_store_cart_count(), 'Cart count helper is safe without WooCommerce' );

exit( $failures > 0 ? 1 : 0 );
