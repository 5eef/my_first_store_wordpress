<?php

declare(strict_types=1);

namespace SeefStore\Security;

use SeefStore\Support\Service;

final class Headers implements Service {
	public function register(): void {
		add_action( 'send_headers', array( $this, 'send' ) );
	}

	public function send(): void {
		if ( headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
	}
}
