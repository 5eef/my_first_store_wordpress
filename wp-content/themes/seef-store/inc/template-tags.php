<?php

declare(strict_types=1);

if ( ! function_exists( 'seef_store_wc_url' ) ) {
	function seef_store_wc_url( string $page ): string {
		$fallbacks = array(
			'shop'      => '/boutique/',
			'cart'      => '/panier/',
			'checkout'  => '/commande/',
			'myaccount' => '/mon-compte/',
		);
		$fallback = home_url( $fallbacks[ $page ] ?? '/' );

		if ( ! function_exists( 'wc_get_page_permalink' ) ) {
			return $fallback;
		}

		$url = wc_get_page_permalink( $page );
		return is_string( $url ) && '' !== $url ? $url : $fallback;
	}
}

if ( ! function_exists( 'seef_store_content_page_url' ) ) {
	function seef_store_content_page_url( string $option, string $fallback_path ): string {
		$page_id = (int) get_option( $option );
		$url     = $page_id > 0 ? get_permalink( $page_id ) : false;

		return is_string( $url ) && '' !== $url ? $url : home_url( $fallback_path );
	}
}

if ( ! function_exists( 'seef_store_cart_count' ) ) {
	function seef_store_cart_count(): int {
		$woocommerce = function_exists( 'WC' ) ? WC() : null;
		return is_object( $woocommerce ) && isset( $woocommerce->cart ) && $woocommerce->cart
			? (int) $woocommerce->cart->get_cart_contents_count()
			: 0;
	}
}

if ( ! function_exists( 'seef_store_product_grid' ) ) {
	/** @param array<string,mixed> $args */
	function seef_store_product_grid( array $args ): void {
		if ( ! function_exists( 'wc_get_template_part' ) || ! post_type_exists( 'product' ) ) {
			return;
		}
		$query = new WP_Query( array_merge( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 4 ), $args ) );
		if ( $query->have_posts() ) {
			echo '<ul class="products columns-4 seef-product-grid">';
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			echo '</ul>';
		}
		wp_reset_postdata();
	}
}

if ( ! function_exists( 'seef_store_language_url' ) ) {
	function seef_store_language_url( string $language ): string {
		return add_query_arg( 'seef_lang', sanitize_key( $language ), remove_query_arg( 'seef_lang' ) );
	}
}

if ( ! function_exists( 'seef_store_icon' ) ) {
	/**
	 * Return a self-contained interface icon.
	 *
	 * Keeping the paths in the theme avoids relying on an icon font or a CDN.
	 */
	function seef_store_icon( string $name, string $class = '' ): string {
		$icons = array(
			'arrow-right' => '<path d="M5 12h14M14 6l6 6-6 6"/>',
			'cart'        => '<path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>',
			'chevron'     => '<path d="m7 10 5 5 5-5"/>',
			'grid'        => '<rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/>',
			'lifestyle'   => '<path d="M12 21s7-4.2 7-11a4 4 0 0 0-7-2.8A4 4 0 0 0 5 10c0 6.8 7 11 7 11Z"/>',
			'menu'        => '<path d="M4 7h16M4 12h16M4 17h16"/>',
			'moon'        => '<path d="M20 15.2A8.5 8.5 0 0 1 8.8 4 8.5 8.5 0 1 0 20 15.2Z"/>',
			'package'     => '<path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="M4 7v10l8 4 8-4V7M12 11v10"/>',
			'search'      => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/>',
			'sparkles'    => '<path d="m12 3 1.2 3.8L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.2L12 3ZM5 14l.8 2.2L8 17l-2.2.8L5 20l-.8-2.2L2 17l2.2-.8L5 14ZM19 13l.7 1.8 1.8.7-1.8.7L19 18l-.7-1.8-1.8-.7 1.8-.7L19 13Z"/>',
			'sun'         => '<circle cx="12" cy="12" r="3.5"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"/>',
			'tech'        => '<rect x="4" y="5" width="16" height="11" rx="2"/><path d="M8 20h8M12 16v4"/>',
			'user'        => '<circle cx="12" cy="8" r="3.5"/><path d="M5 21a7 7 0 0 1 14 0"/>',
			'workspace'   => '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M8 6V4h8v2M3 11h18M10 11v2h4v-2"/>',
		);

		if ( ! isset( $icons[ $name ] ) ) {
			return '';
		}

		$classes = trim( 'seef-icon ' . sanitize_html_class( $class ) );
		return sprintf(
			'<svg class="%1$s" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">%2$s</svg>',
			esc_attr( $classes ),
			$icons[ $name ]
		);
	}
}

if ( ! function_exists( 'seef_store_menu_fallback' ) ) {
	function seef_store_menu_fallback(): void {
		$links = array(
			home_url( '/' ) => __( 'Accueil', 'seef-store' ),
			seef_store_wc_url( 'shop' ) => __( 'Boutique', 'seef-store' ),
			seef_store_content_page_url( 'seef_about_page_id', '/a-propos/' ) => __( 'À propos', 'seef-store' ),
			seef_store_content_page_url( 'seef_contact_page_id', '/contact/' ) => __( 'Contact', 'seef-store' ),
		);
		echo '<ul class="seef-menu">';
		foreach ( $links as $url => $label ) {
			printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( (string) $url ), esc_html( $label ) );
		}
		echo '</ul>';
	}
}
