<?php
/** Portable HTTP smoke tests for local development and Linux CI. */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

$base = rtrim( (string) ( getenv( 'SEEF_BASE_URL' ) ?: 'http://localhost/WordPress/my_first_store_wordpress' ), '/' );
$failures = 0;
$report = static function ( bool $condition, string $label, string $detail = '' ) use ( &$failures ): void {
	echo '[' . ( $condition ? 'PASS' : 'FAIL' ) . '] ' . $label . ( '' !== $detail ? ' — ' . $detail : '' ) . PHP_EOL;
	$failures += $condition ? 0 : 1;
};
$request = static function ( string $url ): array {
	$handle = curl_init( $url );
	curl_setopt_array( $handle, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 20, CURLOPT_HEADER => true ) );
	$response = curl_exec( $handle );
	$error = curl_error( $handle );
	$code = (int) curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );
	$header_size = (int) curl_getinfo( $handle, CURLINFO_HEADER_SIZE );
	curl_close( $handle );
	$response = is_string( $response ) ? $response : '';
	return array( $code, substr( $response, 0, $header_size ), substr( $response, $header_size ), $error );
};

foreach ( array( '/', '/boutique/', '/a-propos/', '/contact/', '/panier/', '/commande/', '/mon-compte/', '/product/aura-mini-speaker/' ) as $path ) {
	list( $code, , $body, $error ) = $request( $base . $path );
	$report( 200 === $code && str_contains( $body, 'SEEF STORE' ), $path . ' is reachable', $error ?: 'HTTP ' . $code );
}

list( $home_code, $headers, $home ) = $request( $base . '/' );
$report( 200 === $home_code && str_contains( $home, '"@type":"OnlineStore"' ) && str_contains( $home, 'class="seef-icon' ), 'Home renders local icons and OnlineStore schema' );
foreach ( array( 'X-Content-Type-Options:', 'X-Frame-Options:', 'Referrer-Policy:', 'Permissions-Policy:' ) as $header ) {
	$report( str_contains( $headers, $header ), 'Security header ' . rtrim( $header, ':' ) );
}

list( $rtl_code, , $rtl ) = $request( $base . '/?seef_lang=ar' );
$report( 200 === $rtl_code && str_contains( $rtl, '<html lang="ar" dir="rtl">' ), 'Arabic document uses RTL' );
$report( ! str_contains( $rtl, 'Ajouter au panier' ) && str_contains( $rtl, 'الفئات' ), 'Arabic page loads WooCommerce and custom UI translations' );
list( $shop_code, , $shop ) = $request( $base . '/boutique/' );
$report( 200 === $shop_code && preg_match( '#<link rel="canonical" href="[^"]+/boutique/">#', $shop ) === 1, 'Shop archive exposes a canonical URL' );

exit( $failures > 0 ? 1 : 0 );
