<?php
/** Fast checks for custom validation and request-normalization helpers. */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

function wp_unslash( mixed $value ): mixed {
	return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
}

function sanitize_text_field( string $value ): string {
	return trim( strip_tags( preg_replace( '/[\r\n\t]+/', ' ', $value ) ?? '' ) );
}

function sanitize_textarea_field( string $value ): string {
	return trim( strip_tags( $value ) );
}

function sanitize_email( string $value ): string {
	return (string) filter_var( $value, FILTER_SANITIZE_EMAIL );
}

function is_email( string $value ): string|false {
	return filter_var( $value, FILTER_VALIDATE_EMAIL );
}

function __( string $text, string $domain = 'default' ): string {
	return $text;
}

function sanitize_key( string $value ): string {
	return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $value ) ) ?? '';
}

function wc_format_decimal( string $value ): string {
	$value = str_replace( ',', '.', trim( $value ) );
	return is_numeric( $value ) ? $value : '';
}

function get_locale(): string {
	return 'fr_FR';
}

require dirname( __DIR__ ) . '/wp-content/plugins/seef-store-core/src/Support/Service.php';
require dirname( __DIR__ ) . '/wp-content/plugins/seef-store-core/src/Validators/ContactValidator.php';
require dirname( __DIR__ ) . '/wp-content/plugins/seef-store-core/src/Frontend/ProductFilters.php';
require dirname( __DIR__ ) . '/wp-content/plugins/seef-store-core/src/Frontend/LocaleSwitcher.php';
require dirname( __DIR__ ) . '/wp-content/plugins/seef-store-core/src/Support/Translator.php';

use SeefStore\Frontend\LocaleSwitcher;
use SeefStore\Frontend\ProductFilters;
use SeefStore\Support\Translator;
use SeefStore\Validators\ContactValidator;

set_error_handler(
	static function ( int $severity, string $message, string $file, int $line ): never {
		throw new ErrorException( $message, 0, $severity, $file, $line );
	}
);

$failures = 0;
$check = static function ( bool $condition, string $label ) use ( &$failures ): void {
	echo '[' . ( $condition ? 'PASS' : 'FAIL' ) . '] ' . $label . PHP_EOL;
	$failures += $condition ? 0 : 1;
};

try {
	$validator = new ContactValidator();
	$result = $validator->validate(
		array(
			'name'    => "  <b>Client Test</b>  ",
			'email'   => 'client@example.test',
			'subject' => '<i>Question produit</i>',
			'message' => '<script>alert(1)</script>Message suffisamment long.',
		)
	);
	$check( array() === $result['errors'], 'Valid contact fields pass validation' );
	$check( 'Client Test' === $result['data']['name'] && ! str_contains( $result['data']['message'], '<script>' ), 'Contact fields are sanitized' );

	$array_result = $validator->validate( array( 'name' => array( 'bad' ), 'email' => array( 'bad' ), 'subject' => array( 'bad' ), 'message' => array( 'bad' ) ) );
	$check( 4 === count( $array_result['errors'] ), 'Non-scalar contact fields are rejected without warnings' );

	$check( array( '100', '900' ) === ProductFilters::normalize_price_range( '900', '100' ), 'Inverted price bounds are normalized' );
	$check( array( '', '' ) === ProductFilters::normalize_price_range( array( '1' ), array( '2' ) ), 'Non-scalar price bounds are rejected' );
	$check( array( '0', '20.5' ) === ProductFilters::normalize_price_range( '-10', '20,5' ), 'Price bounds are decimal-normalized and non-negative' );

	$switcher = new LocaleSwitcher();
	$_GET['seef_lang'] = array( 'ar' );
	$check( 'fr' === $switcher->requested_language(), 'Non-scalar language query falls back safely' );
	unset( $_GET['seef_lang'] );
	$_COOKIE['seef_lang'] = 'ar';
	$check( 'ar' === $switcher->requested_language(), 'Supported language cookie is normalized' );

	$translator = new Translator();
	$_COOKIE['seef_lang'] = array( 'en' );
	$check( 'Boutique' === $translator->translate( 'Boutique', 'Boutique', 'seef-store' ), 'Non-scalar translation cookie falls back safely' );
	$_COOKIE['seef_lang'] = 'en';
	$check( 'Shop' === $translator->translate( 'Boutique', 'Boutique', 'seef-store' ), 'English custom translation is selected' );
} catch ( Throwable $error ) {
	++$failures;
	echo '[FAIL] Unhandled exception — ' . $error->getMessage() . PHP_EOL;
} finally {
	restore_error_handler();
	unset( $_GET['seef_lang'], $_COOKIE['seef_lang'] );
}

exit( $failures > 0 ? 1 : 0 );
