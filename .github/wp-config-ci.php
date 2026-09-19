<?php
/** Test-only WordPress configuration copied to wp-config.php by GitHub Actions. */

declare(strict_types=1);

$seef_ci_env = static function ( string $name ): string {
	$value = getenv( $name );
	if ( false === $value || '' === $value ) {
		throw new RuntimeException( sprintf( 'Required CI environment variable %s is missing.', $name ) );
	}
	return $value;
};

define( 'DB_NAME', $seef_ci_env( 'SEEF_DB_NAME' ) );
define( 'DB_USER', $seef_ci_env( 'SEEF_DB_USER' ) );
define( 'DB_PASSWORD', $seef_ci_env( 'SEEF_DB_PASSWORD' ) );
define( 'DB_HOST', $seef_ci_env( 'SEEF_DB_HOST' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// Public, disposable CI-only values. They are never used outside the job database.
define( 'AUTH_KEY', 'seef-store-ci-auth-key-not-a-secret' );
define( 'SECURE_AUTH_KEY', 'seef-store-ci-secure-auth-key-not-a-secret' );
define( 'LOGGED_IN_KEY', 'seef-store-ci-logged-in-key-not-a-secret' );
define( 'NONCE_KEY', 'seef-store-ci-nonce-key-not-a-secret' );
define( 'AUTH_SALT', 'seef-store-ci-auth-salt-not-a-secret' );
define( 'SECURE_AUTH_SALT', 'seef-store-ci-secure-auth-salt-not-a-secret' );
define( 'LOGGED_IN_SALT', 'seef-store-ci-logged-in-salt-not-a-secret' );
define( 'NONCE_SALT', 'seef-store-ci-nonce-salt-not-a-secret' );

$table_prefix = 'wp_';

define( 'WP_ENVIRONMENT_TYPE', 'development' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'DISALLOW_FILE_EDIT', true );
define( 'WP_AUTO_UPDATE_CORE', false );
// The single-process PHP test server cannot safely serve WordPress loopback cron requests.
define( 'DISABLE_WP_CRON', true );

$seef_ci_url = $seef_ci_env( 'SEEF_SITE_URL' );
define( 'WP_HOME', $seef_ci_url );
define( 'WP_SITEURL', $seef_ci_url );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
