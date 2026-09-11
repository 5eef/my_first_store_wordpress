<?php

declare(strict_types=1);

namespace SeefStore\Frontend;

use SeefStore\Support\Service;

final class LocaleSwitcher implements Service {
	public const LANGUAGES = array( 'fr' => 'fr_FR', 'en' => 'en_US', 'ar' => 'ar' );

	public function register(): void {
		add_action( 'init', array( $this, 'switch_locale' ), 0 );
		add_filter( 'language_attributes', array( $this, 'language_attributes' ) );
	}

	public function switch_locale(): void {
		$key = $this->requested_language();
		if ( isset( self::LANGUAGES[ $key ] ) ) {
			if ( isset( $_GET['seef_lang'] ) && ! headers_sent() ) {
				setcookie( 'seef_lang', $key, array( 'expires' => time() + YEAR_IN_SECONDS, 'path' => COOKIEPATH ?: '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
			}
			switch_to_locale( self::LANGUAGES[ $key ] );
		}
	}

	public function requested_language(): string {
		$value = isset( $_GET['seef_lang'] ) ? wp_unslash( $_GET['seef_lang'] ) : ( $_COOKIE['seef_lang'] ?? 'fr' );
		return sanitize_key( (string) $value );
	}

	public function language_attributes( string $output ): string {
		$key = $this->requested_language();
		if ( ! isset( self::LANGUAGES[ $key ] ) ) {
			return $output;
		}
		return sprintf( 'lang="%s" dir="%s"', esc_attr( $key ), 'ar' === $key ? 'rtl' : 'ltr' );
	}
}
