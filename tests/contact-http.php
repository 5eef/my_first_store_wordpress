<?php
/** Full anonymous HTTP round-trip for the contact controller. */

declare(strict_types=1);

$base       = 'http://localhost/WordPress/my_first_store_wordpress';
$cookieFile = tempnam( sys_get_temp_dir(), 'seef-contact-' );
$failures   = 0;

$report = static function ( bool $ok, string $label ) use ( &$failures ): void {
	echo '[' . ( $ok ? 'PASS' : 'FAIL' ) . '] ' . $label . PHP_EOL;
	if ( ! $ok ) {
		++$failures;
	}
};

$request = static function ( string $url, ?array $data = null, bool $follow = true ) use ( $cookieFile ): array {
	$handle = curl_init( $url );
	curl_setopt_array(
		$handle,
		array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => $follow,
			CURLOPT_COOKIEJAR      => $cookieFile,
			CURLOPT_COOKIEFILE     => $cookieFile,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_HEADER         => ! $follow,
		)
	);
	if ( null !== $data ) {
		curl_setopt( $handle, CURLOPT_POST, true );
		curl_setopt( $handle, CURLOPT_POSTFIELDS, http_build_query( $data ) );
	}
	$body = (string) curl_exec( $handle );
	$code = (int) curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );
	$effectiveUrl = (string) curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );
	curl_close( $handle );
	return array( $code, $body, $effectiveUrl );
};

list( $code, $contactPage ) = $request( $base . '/contact/' );
$report( 200 === $code, 'Contact page returns HTTP 200' );
$hasNonce = preg_match( '/name="seef_contact_nonce" value="([^"]+)"/', $contactPage, $match );
$report( 1 === $hasNonce, 'Contact form exposes a WordPress nonce' );

if ( $hasNonce ) {
	list( $code, $successPage, $successUrl ) = $request(
		$base . '/wp-admin/admin-post.php',
		array(
			'action'             => 'seef_contact_submit',
			'seef_contact_nonce' => html_entity_decode( $match[1], ENT_QUOTES ),
			'company'            => '',
			'name'               => 'HTTP Integration Client',
			'email'              => 'roundtrip@example.test',
			'subject'            => 'Integration Contact Roundtrip',
			'message'            => 'Message HTTP complet destiné au test automatisé du contrôleur.',
		)
	);
	$report( 200 === $code && str_contains( $successUrl, 'contact_status=success' ) && str_contains( $successPage, 'seef-notice--success' ), 'Valid contact submission redirects to success feedback' );

	list( $badCode, $badResponse ) = $request(
		$base . '/wp-admin/admin-post.php',
		array( 'action' => 'seef_contact_submit', 'seef_contact_nonce' => 'invalid', 'name' => 'Bad', 'email' => 'bad@example.test', 'subject' => 'Invalid nonce', 'message' => 'This must never be stored.' ),
		false
	);
	$report( 302 === $badCode && str_contains( $badResponse, 'contact_status=security' ), 'Invalid nonce is rejected before persistence' );
}

$_SERVER['HTTP_HOST'] = 'localhost';
require dirname( __DIR__ ) . '/wp-load.php';
$messages = get_posts( array( 'post_type' => 'seef_contact', 'post_status' => 'private', 'title' => 'Integration Contact Roundtrip', 'numberposts' => 1 ) );
$report( 1 === count( $messages ), 'Submitted message is persisted as a private contact resource' );
if ( $messages ) {
	$report( 'roundtrip@example.test' === get_post_meta( $messages[0]->ID, '_seef_email', true ), 'Persisted e-mail is normalized' );
	wp_delete_post( $messages[0]->ID, true );
}
delete_transient( 'seef_contact_rate_' . substr( hash_hmac( 'sha256', '127.0.0.1', wp_salt( 'nonce' ) ), 0, 32 ) );

if ( is_string( $cookieFile ) && file_exists( $cookieFile ) ) {
	unlink( $cookieFile );
}

exit( $failures ? 1 : 0 );
