<?php

declare(strict_types=1);

namespace SeefStore\Security;

use SeefStore\Support\Service;

final class LoginRateLimiter implements Service {
	private const MAX_ATTEMPTS = 6;

	public function register(): void {
		add_filter( 'authenticate', array( $this, 'block' ), 5, 3 );
		add_action( 'wp_login_failed', array( $this, 'failed' ) );
		add_action( 'wp_login', array( $this, 'success' ), 10, 2 );
	}

	public function block( mixed $user, string $username, string $password ): mixed {
		if ( $username && (int) get_transient( $this->key( $username ) ) >= self::MAX_ATTEMPTS ) {
			return new \WP_Error( 'seef_login_limited', __( 'Trop de tentatives. Réessayez dans 15 minutes.', 'seef-store-core' ) );
		}
		return $user;
	}

	public function failed( string $username ): void {
		$key = $this->key( $username );
		set_transient( $key, (int) get_transient( $key ) + 1, 15 * MINUTE_IN_SECONDS );
	}

	public function success( string $username, \WP_User $user ): void {
		delete_transient( $this->key( $username ) );
	}

	private function key( string $username ): string {
		$ip = preg_replace( '/[^0-9a-fA-F:.]/', '', (string) ( $_SERVER['REMOTE_ADDR'] ?? 'local' ) );
		return 'seef_login_' . substr( hash_hmac( 'sha256', strtolower( $username ) . '|' . $ip, wp_salt( 'auth' ) ), 0, 36 );
	}
}
