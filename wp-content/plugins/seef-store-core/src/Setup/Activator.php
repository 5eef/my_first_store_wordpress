<?php

declare(strict_types=1);

namespace SeefStore\Setup;

final class Activator {
	public static function activate(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( SEEF_STORE_CORE_FILE ) );
			wp_die( esc_html__( 'WooCommerce doit être actif avant SEEF Store Core.', 'seef-store-core' ) );
		}

		// Remove the legacy activation flag without ever creating demo content.
		delete_option( 'seef_store_seed_pending' );
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
