<?php
/** Full anonymous HTTP round-trip for the contact controller. */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

$base       = rtrim( (string) ( getenv( 'SEEF_BASE_URL' ) ?: 'http://localhost/WordPress/my_first_store_wordpress' ), '/' );
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

$newsletter_email = 'newsletter-roundtrip-' . time() . '@example.test';
$honeypot_email    = 'newsletter-honeypot-' . time() . '@example.test';
list( $homeCode, $homePage ) = $request( $base . '/' );
$hasNewsletterNonce = preg_match( '/name="seef_newsletter_nonce" value="([^"]+)"/', $homePage, $newsletterMatch );
$report( 200 === $homeCode && 1 === $hasNewsletterNonce, 'Newsletter form exposes a WordPress nonce' );

if ( $hasNewsletterNonce ) {
	$newsletter_data = array(
		'action'                  => 'seef_newsletter_submit',
		'seef_newsletter_nonce' => html_entity_decode( $newsletterMatch[1], ENT_QUOTES ),
		'company'                 => '',
		'email'                   => $newsletter_email,
	);
	list( $newsletterCode, , $newsletterUrl ) = $request( $base . '/wp-admin/admin-post.php', $newsletter_data );
	$report( 200 === $newsletterCode && str_contains( $newsletterUrl, 'newsletter_status=success' ), 'Valid newsletter subscription succeeds' );
	list( $duplicateCode, , $duplicateUrl ) = $request( $base . '/wp-admin/admin-post.php', $newsletter_data );
	$report( 200 === $duplicateCode && str_contains( $duplicateUrl, 'newsletter_status=success' ), 'Duplicate newsletter subscription remains idempotent' );
	$honeypot_data = $newsletter_data;
	$honeypot_data['company'] = 'Spam Company';
	$honeypot_data['email']   = $honeypot_email;
	list( $honeypotCode, , $honeypotUrl ) = $request( $base . '/wp-admin/admin-post.php', $honeypot_data );
	$report( 200 === $honeypotCode && str_contains( $honeypotUrl, 'newsletter_status=success' ), 'Newsletter honeypot safely absorbs bot submissions' );
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

$newsletter_posts = get_posts( array( 'post_type' => 'seef_subscriber', 'post_status' => 'private', 'title' => $newsletter_email, 'numberposts' => -1, 'fields' => 'ids' ) );
$honeypot_posts    = get_posts( array( 'post_type' => 'seef_subscriber', 'post_status' => 'private', 'title' => $honeypot_email, 'numberposts' => -1, 'fields' => 'ids' ) );
$report( 1 === count( $newsletter_posts ), 'Newsletter e-mail is stored only once' );
$report( 0 === count( $honeypot_posts ), 'Newsletter honeypot e-mail is not stored' );
foreach ( $newsletter_posts as $newsletter_post_id ) {
	wp_delete_post( (int) $newsletter_post_id, true );
}

if ( $hasNewsletterNonce ) {
	foreach ( array( '127.0.0.1', '::1' ) as $loopback ) {
		$rate_key = 'seef_newsletter_rate_' . substr( hash_hmac( 'sha256', $loopback, wp_salt( 'nonce' ) ), 0, 32 );
		set_transient( $rate_key, 4, MINUTE_IN_SECONDS );
	}
	$rate_data = $newsletter_data;
	$rate_data['email'] = 'newsletter-rate-' . time() . '@example.test';
	list( $rateCode, , $rateUrl ) = $request( $base . '/wp-admin/admin-post.php', $rate_data );
	$report( 200 === $rateCode && str_contains( $rateUrl, 'newsletter_status=rate_limited' ), 'Newsletter rate limit rejects excessive submissions' );
	foreach ( array( '127.0.0.1', '::1' ) as $loopback ) {
		delete_transient( 'seef_newsletter_rate_' . substr( hash_hmac( 'sha256', $loopback, wp_salt( 'nonce' ) ), 0, 32 ) );
	}
}

if ( is_string( $cookieFile ) && file_exists( $cookieFile ) ) {
	unlink( $cookieFile );
}

exit( $failures ? 1 : 0 );
