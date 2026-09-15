<?php
/** Authenticated HTTP smoke tests for the WordPress/WooCommerce admin surfaces. */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

$admin_user = (string) getenv( 'SEEF_ADMIN_USER' );
$admin_pass = (string) getenv( 'SEEF_ADMIN_PASSWORD' );
if ( '' === $admin_user || '' === $admin_pass ) {
	fwrite( STDERR, "SEEF_ADMIN_USER and SEEF_ADMIN_PASSWORD are required.\n" );
	exit( 1 );
}

$base       = 'http://localhost/WordPress/my_first_store_wordpress';
$cookieFile = tempnam( sys_get_temp_dir(), 'seef-admin-' );
$failures   = 0;

$request = static function ( string $url, ?array $data = null ) use ( $cookieFile ): array {
	$handle = curl_init( $url );
	curl_setopt_array( $handle, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_COOKIEJAR => $cookieFile, CURLOPT_COOKIEFILE => $cookieFile, CURLOPT_TIMEOUT => 20 ) );
	if ( null !== $data ) {
		curl_setopt( $handle, CURLOPT_POST, true );
		curl_setopt( $handle, CURLOPT_POSTFIELDS, http_build_query( $data ) );
	}
	$body = (string) curl_exec( $handle );
	$code = (int) curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );
	$url  = (string) curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );
	curl_close( $handle );
	return array( $code, $body, $url );
};

$report = static function ( bool $ok, string $label ) use ( &$failures ): void {
	echo '[' . ( $ok ? 'PASS' : 'FAIL' ) . '] ' . $label . PHP_EOL;
	if ( ! $ok ) { ++$failures; }
};

$request( $base . '/wp-login.php' );
list( $code, $body, $url ) = $request(
	$base . '/wp-login.php',
	array( 'log' => $admin_user, 'pwd' => $admin_pass, 'rememberme' => 'forever', 'wp-submit' => 'Se connecter', 'redirect_to' => $base . '/wp-admin/', 'testcookie' => '1' )
);
$report( 200 === $code && str_contains( $url, '/wp-admin/' ) && str_contains( $body, 'wp-admin-bar' ), 'Demo administrator authenticates through wp-login.php' );

$screens = array(
	'Product CRUD screen'  => '/wp-admin/edit.php?post_type=product',
	'Order management'     => '/wp-admin/admin.php?page=wc-orders',
	'Customer management'  => '/wp-admin/users.php?role=customer',
	'Contact management'   => '/wp-admin/edit.php?post_type=seef_contact',
);

foreach ( $screens as $label => $path ) {
	list( $screenCode, $screenBody, $screenUrl ) = $request( $base . $path );
	$report( 200 === $screenCode && ! str_contains( $screenUrl, 'wp-login.php' ) && str_contains( $screenBody, 'wp-admin-bar' ), $label . ' is authorized and reachable' );
}

if ( is_string( $cookieFile ) && file_exists( $cookieFile ) ) { unlink( $cookieFile ); }
exit( $failures ? 1 : 0 );
