<?php
/** Standalone checks for the explicit demo-seeding guard. */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

$seef_test_environment = 'local';

function wp_get_environment_type(): string {
	global $seef_test_environment;
	return $seef_test_environment;
}

require dirname( __DIR__ ) . '/wp-content/plugins/seef-store-core/src/Setup/DemoSeeder.php';

use SeefStore\Setup\DemoSeeder;

$failures = 0;
$check = static function ( bool $condition, string $label ) use ( &$failures ): void {
	echo '[' . ( $condition ? 'PASS' : 'FAIL' ) . '] ' . $label . PHP_EOL;
	$failures += $condition ? 0 : 1;
};

$previous = getenv( 'SEEF_DEMO_MODE' );
putenv( 'SEEF_DEMO_MODE' );
$check( false === DemoSeeder::is_allowed(), 'Demo seeding is disabled by default' );
putenv( 'SEEF_DEMO_MODE=true' );
$check( true === DemoSeeder::is_allowed(), 'Explicit demo mode is allowed locally' );
$seef_test_environment = 'production';
$check( false === DemoSeeder::is_allowed(), 'Production blocks seeding even with the flag enabled' );
$seef_test_environment = 'staging';
$check( false === DemoSeeder::is_allowed(), 'Staging blocks demo seeding' );

false === $previous ? putenv( 'SEEF_DEMO_MODE' ) : putenv( 'SEEF_DEMO_MODE=' . $previous );
exit( $failures > 0 ? 1 : 0 );
