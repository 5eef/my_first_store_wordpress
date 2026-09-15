<?php
/** Integration checks against the local WordPress + WooCommerce database. */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only.' );
}

$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/WordPress/my_first_store_wordpress/';

require dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

use SeefStore\Models\ContactMessage;
use SeefStore\Controllers\NewsletterController;
use SeefStore\Frontend\ProductFilters;
use SeefStore\Setup\Activator;
use SeefStore\Setup\DemoSeeder;
use SeefStore\Validators\ContactValidator;

$passed = 0;
$failed = 0;
$results = array();

$check = static function ( bool $condition, string $label, string $detail = '' ) use ( &$passed, &$failed, &$results ): void {
	if ( $condition ) {
		++$passed;
		$results[] = array( 'PASS', $label, $detail );
	} else {
		++$failed;
		$results[] = array( 'FAIL', $label, $detail );
	}
};

try {
	$check( is_blog_installed(), 'WordPress is installed' );
	$check( get_stylesheet() === 'seef-store', 'Custom theme is active', get_stylesheet() );
	$check( is_plugin_active( 'woocommerce/woocommerce.php' ), 'WooCommerce is active' );
	$check( is_plugin_active( 'seef-store-core/seef-store-core.php' ), 'SEEF Store Core is active' );
	$check( defined( 'WC_VERSION' ), 'WooCommerce runtime loaded', defined( 'WC_VERSION' ) ? WC_VERSION : '' );

	$shipping_state = static function (): array {
		$state = array();
		foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
			$methods = array();
			foreach ( $zone['shipping_methods'] as $method ) {
				$methods[] = array( $method->id, (int) $method->get_instance_id(), $method->enabled, $method->instance_settings );
			}
			$state[] = array( (int) $zone['zone_id'], (string) $zone['zone_name'], (int) $zone['zone_order'], $zone['zone_locations'], $methods );
		}
		return $state;
	};
	$state_before_activation = array(
		'products'    => (int) wp_count_posts( 'product' )->publish,
		'pages'       => (int) wp_count_posts( 'page' )->publish,
		'users'       => (int) count_users()['total_users'],
		'orders'      => count( wc_get_orders( array( 'limit' => -1, 'return' => 'ids' ) ) ),
		'blog_public' => get_option( 'blog_public' ),
		'currency'    => get_option( 'woocommerce_currency' ),
		'bacs'        => get_option( 'woocommerce_bacs_settings' ),
		'cod'         => get_option( 'woocommerce_cod_settings' ),
		'zones'       => wp_json_encode( $shipping_state() ),
	);
	Activator::activate();
	$state_after_activation = array(
		'products'    => (int) wp_count_posts( 'product' )->publish,
		'pages'       => (int) wp_count_posts( 'page' )->publish,
		'users'       => (int) count_users()['total_users'],
		'orders'      => count( wc_get_orders( array( 'limit' => -1, 'return' => 'ids' ) ) ),
		'blog_public' => get_option( 'blog_public' ),
		'currency'    => get_option( 'woocommerce_currency' ),
		'bacs'        => get_option( 'woocommerce_bacs_settings' ),
		'cod'         => get_option( 'woocommerce_cod_settings' ),
		'zones'       => wp_json_encode( $shipping_state() ),
	);
	$activation_changes = array();
	foreach ( $state_before_activation as $state_key => $state_value ) {
		if ( $state_value !== $state_after_activation[ $state_key ] ) {
			$activation_changes[] = $state_key;
		}
	}
	$check( array() === $activation_changes && false === get_option( 'seef_store_seed_pending' ), 'Normal plugin activation does not seed or reconfigure the store', implode( ', ', $activation_changes ) );

	$previous_demo_flag = getenv( 'SEEF_DEMO_MODE' );
	putenv( 'SEEF_DEMO_MODE=false' );
	$seed_version_before = get_option( 'seef_store_seed_version' );
	$check( false === DemoSeeder::run( true ) && $seed_version_before === get_option( 'seef_store_seed_version' ), 'SEEF_DEMO_MODE=false blocks demo seeding' );
	putenv( 'SEEF_DEMO_MODE=true' );
	$expected_demo_access = in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
	$check( $expected_demo_access === DemoSeeder::is_allowed(), 'SEEF_DEMO_MODE is constrained to an authorized environment' );
	false === $previous_demo_flag ? putenv( 'SEEF_DEMO_MODE' ) : putenv( 'SEEF_DEMO_MODE=' . $previous_demo_flag );

	$published_products = (int) wp_count_posts( 'product' )->publish;
	$check( 16 === $published_products, 'Exactly 16 demo products are published', (string) $published_products );
	$check( 4 === (int) wp_count_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) ), 'Four non-empty product categories exist' );
	$check( count( wc_get_product_ids_on_sale() ) >= 6, 'Sale products exist' );

	$product_id = wc_get_product_id_by_sku( 'SEEF-TECH-001' );
	$product    = wc_get_product( $product_id );
	$check( $product instanceof WC_Product, 'Product can be loaded through WooCommerce CRUD' );
	if ( $product instanceof WC_Product ) {
		$check( '849' === $product->get_regular_price(), 'Regular price persisted', $product->get_regular_price() );
		$check( '699' === $product->get_sale_price(), 'Sale price persisted', $product->get_sale_price() );
		$check( $product->managing_stock() && $product->get_stock_quantity() > 0, 'Managed stock is available' );
		$check( (bool) $product->get_image_id(), 'Product image attachment exists' );
		$check( (bool) get_post_meta( $product->get_image_id(), '_wp_attachment_image_alt', true ), 'Product image has alt text' );
	}
	$crud_product = new WC_Product_Simple();
	$crud_product->set_name( 'SEEF CRUD Integration Product' );
	$crud_product->set_sku( 'SEEF-TEST-CRUD' );
	$crud_product->set_regular_price( '100' );
	$crud_product->set_manage_stock( true );
	$crud_product->set_stock_quantity( 3 );
	$crud_product->set_status( 'draft' );
	$crud_id = $crud_product->save();
	$check( $crud_id > 0, 'WooCommerce product CREATE works' );
	$crud_product = wc_get_product( $crud_id );
	$check( $crud_product instanceof WC_Product && '100' === $crud_product->get_regular_price(), 'WooCommerce product READ works' );
	if ( $crud_product instanceof WC_Product ) {
		$crud_product->set_regular_price( '125' );
		$crud_product->save();
		$check( '125' === wc_get_product( $crud_id )->get_regular_price(), 'WooCommerce product UPDATE works' );
		$crud_product->delete( true );
		$check( false === wc_get_product( $crud_id ), 'WooCommerce product DELETE works' );
	}
	$term = wp_insert_term( 'SEEF CRUD Category', 'product_cat', array( 'slug' => 'seef-crud-category' ) );
	$check( ! is_wp_error( $term ), 'Product category CREATE works' );
	if ( ! is_wp_error( $term ) ) {
		$term_id = (int) $term['term_id'];
		$updated = wp_update_term( $term_id, 'product_cat', array( 'description' => 'Temporary integration category.' ) );
		$check( ! is_wp_error( $updated ) && 'Temporary integration category.' === get_term( $term_id )->description, 'Product category UPDATE works' );
		wp_delete_term( $term_id, 'product_cat' );
		$check( ! term_exists( $term_id, 'product_cat' ), 'Product category DELETE works' );
	}

	foreach ( array( 'page_on_front', 'woocommerce_shop_page_id', 'woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_myaccount_page_id', 'seef_about_page_id', 'seef_contact_page_id' ) as $option ) {
		$id = (int) get_option( $option );
		$check( $id > 0 && 'publish' === get_post_status( $id ), 'Published page configured: ' . $option, (string) $id );
	}

	$customer = get_user_by( 'login', 'customer_demo' );
	$check( $customer instanceof WP_User && in_array( 'customer', $customer->roles, true ), 'Demo customer and role exist' );
	$check( $customer instanceof WP_User && strlen( $customer->user_pass ) > 30, 'Demo customer password is WordPress-hashed' );
	$demo_orders = wc_get_orders( array( 'customer_id' => $customer instanceof WP_User ? $customer->ID : 0, 'limit' => 5, 'meta_key' => '_seef_demo_order', 'meta_value' => 'yes' ) );
	$check( 1 === count( $demo_orders ) && 'completed' === $demo_orders[0]->get_status(), 'Demo customer has a completed order in history' );
	$check( is_wp_error( wp_authenticate( 'customer_demo', 'wrong-password' ) ), 'Customer authentication rejects invalid credentials' );
	if ( $customer instanceof WP_User ) {
		wp_set_current_user( $customer->ID );
		$check( ! current_user_can( 'manage_woocommerce' ), 'Customer cannot manage WooCommerce' );
	}
	$administrators = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
	$admin = $administrators[0] ?? null;
	if ( $admin instanceof WP_User ) {
		wp_set_current_user( $admin->ID );
	}
	$check( current_user_can( 'manage_woocommerce' ), 'Administrator can manage WooCommerce' );

	$validator = new ContactValidator();
	$valid = $validator->validate( array( 'name' => 'Client Démo', 'email' => 'client@example.test', 'subject' => 'Question produit', 'message' => 'Bonjour, ceci est un message de validation suffisamment long.' ) );
	$check( array() === $valid['errors'], 'Contact validator accepts valid input' );
	$invalid = $validator->validate( array( 'name' => '<script>x</script>', 'email' => 'bad', 'subject' => 'x', 'message' => 'short' ) );
	$check( count( $invalid['errors'] ) >= 3, 'Contact validator rejects malformed input' );
	$check( false === str_contains( $invalid['data']['name'], '<script>' ), 'Contact input is sanitized against HTML injection' );
	$nonce = wp_create_nonce( 'seef_contact_submit' );
	$check( 1 === wp_verify_nonce( $nonce, 'seef_contact_submit' ), 'Contact CSRF nonce verifies' );
	$check( array( '100', '900' ) === ProductFilters::normalize_price_range( '900', '100' ), 'Inverted minimum and maximum prices are normalized' );
	$newsletter = new NewsletterController();
	$check( str_contains( $newsletter->form(), 'name="company"' ), 'Newsletter form contains a honeypot' );
	$rate_key_method = new ReflectionMethod( $newsletter, 'rate_key' );
	$rate_check_method = new ReflectionMethod( $newsletter, 'is_rate_limited' );
	$newsletter_rate_key = (string) $rate_key_method->invoke( $newsletter );
	set_transient( $newsletter_rate_key, 4, MINUTE_IN_SECONDS );
	$check( true === $rate_check_method->invoke( $newsletter ), 'Newsletter rate limit blocks excessive submissions' );
	delete_transient( $newsletter_rate_key );
	$message_id = ContactMessage::create( $valid['data'] );
	$check( is_int( $message_id ) && $message_id > 0, 'Contact message persists in MariaDB' );
	if ( is_int( $message_id ) ) {
		$check( 'private' === get_post_status( $message_id ) && 'new' === ContactMessage::status( $message_id ), 'Contact message is private with new status' );
		update_post_meta( $message_id, '_seef_status', 'replied' );
		$check( 'replied' === ContactMessage::status( $message_id ), 'Contact status UPDATE works' );
		wp_delete_post( $message_id, true );
	}

	$bacs = get_option( 'woocommerce_bacs_settings', array() );
	$cod  = get_option( 'woocommerce_cod_settings', array() );
	$check( 'yes' === ( $bacs['enabled'] ?? 'no' ), 'BACS demo payment is enabled' );
	$check( 'yes' === ( $cod['enabled'] ?? 'no' ), 'Cash on delivery is enabled' );
	$zones = WC_Shipping_Zones::get_zones();
	$check( (bool) array_filter( $zones, static fn( array $zone ): bool => 'Maroc — Démonstration' === $zone['zone_name'] ), 'Morocco shipping zone exists' );
	$checkout_fields = WC()->checkout()->get_checkout_fields();
	$check( ! empty( $checkout_fields['billing']['billing_email']['required'] ), 'Checkout requires a billing e-mail' );

	if ( ! WC()->session ) {
		wc_load_cart();
	}
	WC()->cart->empty_cart();
	$check( false !== WC()->cart->add_to_cart( $product_id, 2 ), 'Product can be added to cart' );
	WC()->cart->calculate_totals();
	$check( 2 === WC()->cart->get_cart_contents_count(), 'Cart quantity updates to two' );
	$check( (float) WC()->cart->get_total( 'edit' ) > 0, 'Cart total is calculated' );
	WC()->cart->empty_cart();
	$check( 0 === WC()->cart->get_cart_contents_count(), 'Cart item removal works' );

	if ( $product instanceof WC_Product && $customer instanceof WP_User ) {
		$emails = WC()->mailer()->get_emails();
		$new_order_email = $emails['WC_Email_New_Order'] ?? null;
		$processing_email = $emails['WC_Email_Customer_Processing_Order'] ?? null;
		if ( $new_order_email ) {
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $new_order_email, 'trigger' ), 10 );
		}
		if ( $processing_email ) {
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $processing_email, 'trigger' ), 10 );
		}
		$stock_before = (int) $product->get_stock_quantity();
		$order = wc_create_order( array( 'customer_id' => $customer->ID, 'status' => 'pending', 'created_via' => 'seef-integration-test' ) );
		$order->add_product( $product, 1 );
		$order->set_address( array( 'first_name' => 'Client', 'last_name' => 'Démo', 'address_1' => '1 rue Démonstration', 'city' => 'Marrakech', 'country' => 'MA', 'email' => 'customer@seef-store.local' ), 'billing' );
		$order->set_address( array( 'first_name' => 'Client', 'last_name' => 'Démo', 'address_1' => '1 rue Démonstration', 'city' => 'Marrakech', 'country' => 'MA' ), 'shipping' );
		$order->set_payment_method( 'bacs' );
		$order->calculate_totals();
		$order->save();
		$check( $order->get_id() > 0 && 'bacs' === $order->get_payment_method(), 'Checkout-equivalent order persists with demo payment' );
		$order->update_status( 'processing', 'Integration status test.', false );
		$check( 'processing' === wc_get_order( $order->get_id() )->get_status(), 'Order status UPDATE works' );
		wc_reduce_stock_levels( $order->get_id() );
		$product = wc_get_product( $product_id );
		$check( $product && (int) $product->get_stock_quantity() === $stock_before - 1, 'Order stock reduction is coherent' );
		if ( $product ) {
			$product->set_stock_quantity( $stock_before );
			$product->save();
		}
		$order->delete( true );
		if ( $new_order_email ) {
			add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $new_order_email, 'trigger' ), 10, 2 );
		}
		if ( $processing_email ) {
			add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $processing_email, 'trigger' ), 10, 2 );
		}
	}

	$_GET['seef_lang'] = 'ar';
	$ar_switched = switch_to_locale( 'ar' );
	$check( $ar_switched && 'ar' === get_locale() && is_rtl(), 'Arabic locale pack and RTL runtime are active' );
	$arabic_translation = __( 'Boutique', 'seef-store' );
	$check( 'المتجر' === $arabic_translation, 'Arabic UI translation is active', $arabic_translation );
	$check( 'Cart' !== __( 'Cart', 'woocommerce' ), 'WooCommerce Arabic translation pack is active', __( 'Cart', 'woocommerce' ) );
	restore_previous_locale();
	$_GET['seef_lang'] = 'en';
	$check( 'Shop' === __( 'Boutique', 'seef-store' ), 'English UI translation is active' );
	unset( $_GET['seef_lang'] );

	$theme_dir = get_template_directory();
	$missing_assets = array();
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme_dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $iterator as $file ) {
		if ( ! $file instanceof SplFileInfo || ! in_array( $file->getExtension(), array( 'php', 'css', 'js' ), true ) ) {
			continue;
		}
		$contents = (string) file_get_contents( $file->getPathname() );
		preg_match_all( '#assets/(?:css|js|images)/[A-Za-z0-9._/-]+#', $contents, $matches );
		foreach ( array_unique( $matches[0] ) as $asset ) {
			if ( ! file_exists( $theme_dir . '/' . $asset ) ) {
				$missing_assets[] = $asset;
			}
		}
		preg_match_all( '#url\(["\']?\.\./images/([A-Za-z0-9._-]+)#', $contents, $css_matches );
		foreach ( array_unique( $css_matches[1] ) as $asset ) {
			if ( ! file_exists( $theme_dir . '/assets/images/' . $asset ) ) {
				$missing_assets[] = 'assets/images/' . $asset;
			}
		}
	}
	$check( array() === array_values( array_unique( $missing_assets ) ), 'Every referenced theme asset exists', implode( ', ', array_unique( $missing_assets ) ) );
} catch ( Throwable $error ) {
	++$failed;
	$results[] = array( 'FAIL', 'Unhandled exception', $error->getMessage() . ' @ ' . $error->getFile() . ':' . $error->getLine() );
}

foreach ( $results as $result ) {
	list( $status, $label, $detail ) = $result;
	printf( "[%s] %s%s\n", $status, $label, $detail ? ' — ' . $detail : '' );
}
printf( "\nSummary: %d passed, %d failed\n", $passed, $failed );
exit( $failed > 0 ? 1 : 0 );
