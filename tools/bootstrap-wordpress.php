<?php
/**
 * One-time, idempotent local WordPress bootstrap.
 *
 * Usage: C:\xampp\php\php.exe tools\bootstrap-wordpress.php
 */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

define( 'WP_INSTALLING', true );
require dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( is_blog_installed() ) {
	fwrite( STDOUT, "WordPress is already installed.\n" );
	exit( 0 );
}

$admin_user = sanitize_user( (string) getenv( 'SEEF_ADMIN_USER' ), true );
$admin_pass = (string) getenv( 'SEEF_ADMIN_PASSWORD' );
$generated  = '' === $admin_pass;

if ( '' === $admin_user ) {
	fwrite( STDERR, "A valid SEEF_ADMIN_USER is required.\n" );
	exit( 1 );
}
if ( $generated ) {
	$admin_pass = wp_generate_password( 24, true, true );
} elseif ( strlen( $admin_pass ) < 12 ) {
	fwrite( STDERR, "SEEF_ADMIN_PASSWORD must contain at least 12 characters.\n" );
	exit( 1 );
}

$result = wp_install(
	'SEEF STORE',
	$admin_user,
	'admin@seef-store.local',
	false,
	'',
	$admin_pass,
	'fr_FR'
);

if ( is_wp_error( $result ) ) {
	fwrite( STDERR, $result->get_error_message() . "\n" );
	exit( 1 );
}

$url = 'http://localhost/WordPress/my_first_store_wordpress';
update_option( 'siteurl', $url );
update_option( 'home', $url );
update_option( 'blogdescription', 'Objets tech et lifestyle pensés pour le quotidien.' );
update_option( 'timezone_string', 'Africa/Casablanca' );
update_option( 'date_format', 'd/m/Y' );
update_option( 'time_format', 'H:i' );
update_option( 'permalink_structure', '/%postname%/' );

fwrite( STDOUT, "WordPress installed for SEEF STORE.\n" );
if ( $generated ) {
	fwrite( STDOUT, "Generated administrator password (shown once): {$admin_pass}\n" );
}
