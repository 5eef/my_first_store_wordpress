<?php
/** Router for PHP's built-in server in CI. */

declare(strict_types=1);

$path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
$file = dirname( __DIR__ ) . '/' . ltrim( $path, '/' );

if ( '/' !== $path && is_file( $file ) ) {
	return false;
}

require dirname( __DIR__ ) . '/index.php';
