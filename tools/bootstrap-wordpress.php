<?php
/**
 * One-time, idempotent local WordPress bootstrap.
 *
 * Usage: C:\xampp\php\php.exe tools\bootstrap-wordpress.php
 */

declare(strict_types=1);

define( 'WP_INSTALLING', true );
require dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( is_blog_installed() ) {
	fwrite( STDOUT, "WordPress is already installed.\n" );
	exit( 0 );
}

$result = wp_install(
	'SEEF STORE',
	'seef_admin',
	'admin@seef-store.local',
	true,
	'',
	'SeefAdmin2026!',
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
