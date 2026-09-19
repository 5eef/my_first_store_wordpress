<?php

declare(strict_types=1);

namespace SeefStore\Setup;

use Automattic\WooCommerce\Internal\Features\FeaturesController;
use SeefStore\Models\ContactMessage;

final class DemoSeeder {
	private const SEED_VERSION = '1.0.2';

	/** @var array<string,int> */
	private static array $categories = array();

	public static function is_allowed(): bool {
		$flag = defined( 'SEEF_DEMO_MODE' ) ? SEEF_DEMO_MODE : getenv( 'SEEF_DEMO_MODE' );
		$enabled = true === $flag || in_array( strtolower( (string) $flag ), array( '1', 'true', 'yes', 'on' ), true );

		return $enabled && in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
	}

	public static function run( bool $force = false ): bool {
		if ( ! self::is_allowed() ) {
			return false;
		}
		if ( ! $force && self::SEED_VERSION === get_option( 'seef_store_seed_version' ) ) {
			return true;
		}
		if (
			! did_action( 'woocommerce_init' )
			|| ! class_exists( 'WooCommerce' )
			|| ! class_exists( 'WC_Product_Simple' )
			|| ! function_exists( 'WC' )
			|| ! WC()
			|| ! ( WC()->countries instanceof \WC_Countries )
		) {
			return false;
		}

		self::register_contact_type();
		self::configure_store();
		self::create_pages();
		self::create_categories();
		self::create_products();
		if ( ! self::create_demo_customer() ) {
			return false;
		}
		self::create_demo_order();
		self::create_menu();
		self::configure_shipping();
		update_option( 'seef_store_seed_version', self::SEED_VERSION );
		flush_rewrite_rules();

		return true;
	}

	private static function register_contact_type(): void {
		if ( ! post_type_exists( ContactMessage::POST_TYPE ) ) {
			register_post_type( ContactMessage::POST_TYPE, array( 'public' => false, 'show_ui' => true ) );
		}
	}

	private static function configure_store(): void {
		if ( function_exists( 'wc_get_container' ) && class_exists( FeaturesController::class ) ) {
			wc_get_container()->get( FeaturesController::class )->change_feature_enable( 'custom_order_tables', true );
		}
		update_option( 'blogname', 'SEEF STORE' );
		update_option( 'blogdescription', 'Objets tech et lifestyle pensés pour le quotidien.' );
		update_option( 'blog_public', 'production' === wp_get_environment_type() ? '1' : '0' );
		update_option( 'woocommerce_currency', 'MAD' );
		update_option( 'woocommerce_default_country', 'MA' );
		update_option( 'woocommerce_allowed_countries', 'specific' );
		update_option( 'woocommerce_specific_allowed_countries', array( 'MA' ) );
		update_option( 'woocommerce_ship_to_countries', 'specific' );
		update_option( 'woocommerce_specific_ship_to_countries', array( 'MA' ) );
		update_option( 'woocommerce_calc_taxes', 'no' );
		update_option( 'woocommerce_manage_stock', 'yes' );
		update_option( 'woocommerce_coming_soon', 'no' );
		update_option( 'woocommerce_store_pages_only', 'no' );
		update_option( 'woocommerce_enable_reviews', 'yes' );
		update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
		update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
		update_option( 'woocommerce_registration_generate_password', 'no' );
		update_option( 'woocommerce_bacs_settings', array( 'enabled' => 'yes', 'title' => 'Virement bancaire (démo)', 'description' => 'Commande de démonstration : aucun transfert réel ne doit être effectué.', 'instructions' => 'Coordonnées fictives réservées à cette vitrine.' ) );
		update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'title' => 'Paiement à la livraison', 'description' => 'Paiement de démonstration à la livraison.', 'instructions' => 'Aucun paiement réel dans cette expérience.', 'enable_for_methods' => array(), 'enable_for_virtual' => 'yes' ) );
		update_option(
			'woocommerce_bacs_accounts',
			array(
				array( 'account_name' => 'SEEF STORE — DÉMONSTRATION', 'account_number' => 'DEMO-0000', 'bank_name' => 'Banque de démonstration', 'sort_code' => '', 'iban' => 'DEMO000000000000', 'bic' => 'DEMO' ),
			)
		);
	}

	private static function create_pages(): void {
		$pages = array(
			'home'      => array( 'Accueil', 'accueil', '', 'default' ),
			'shop'      => array( 'Boutique', 'boutique', '', 'default' ),
			'cart'      => array( 'Panier', 'panier', '[woocommerce_cart]', 'default' ),
			'checkout'  => array( 'Commande', 'commande', '[woocommerce_checkout]', 'default' ),
			'myaccount' => array( 'Mon compte', 'mon-compte', '[woocommerce_my_account]', 'default' ),
			'about'     => array( 'À propos', 'a-propos', '', 'page-about.php' ),
			'contact'   => array( 'Contact', 'contact', '[seef_contact_form]', 'page-contact.php' ),
		);

		$ids = array();
		foreach ( $pages as $key => $page_data ) {
			list( $title, $slug, $content, $template ) = $page_data;
			$page = get_page_by_path( $slug );
			$id   = $page instanceof \WP_Post ? $page->ID : wp_insert_post(
				array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content ),
				true
			);
			if ( is_wp_error( $id ) ) {
				continue;
			}
			$legacy_content = array(
				'cart'     => '<!-- wp:woocommerce/cart /-->',
				'checkout' => '<!-- wp:woocommerce/checkout /-->',
			);
			if ( $page instanceof \WP_Post && isset( $legacy_content[ $key ] ) && trim( $page->post_content ) === $legacy_content[ $key ] ) {
				wp_update_post( array( 'ID' => $page->ID, 'post_content' => $content ) );
			}
			$ids[ $key ] = (int) $id;
			if ( 'default' !== $template ) {
				update_post_meta( (int) $id, '_wp_page_template', $template );
			}
		}

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] ?? 0 );
		update_option( 'woocommerce_shop_page_id', $ids['shop'] ?? 0 );
		update_option( 'woocommerce_cart_page_id', $ids['cart'] ?? 0 );
		update_option( 'woocommerce_checkout_page_id', $ids['checkout'] ?? 0 );
		update_option( 'woocommerce_myaccount_page_id', $ids['myaccount'] ?? 0 );
		update_option( 'seef_about_page_id', $ids['about'] ?? 0 );
		update_option( 'seef_contact_page_id', $ids['contact'] ?? 0 );
	}

	private static function create_categories(): void {
		$categories = array(
			'Tech'        => array( 'tech', 'Technologie utile, intuitive et soigneusement sélectionnée.' ),
			'Accessoires' => array( 'accessoires', 'Les détails fonctionnels qui suivent chaque journée.' ),
			'Workspace'   => array( 'workspace', 'Un espace de travail calme, organisé et inspirant.' ),
			'Lifestyle'   => array( 'lifestyle', 'Des essentiels contemporains pour un quotidien fluide.' ),
		);
		foreach ( $categories as $name => $category_data ) {
			list( $slug, $description ) = $category_data;
			$existing = term_exists( $slug, 'product_cat' );
			$result   = $existing ?: wp_insert_term( $name, 'product_cat', array( 'slug' => $slug, 'description' => $description ) );
			if ( ! is_wp_error( $result ) ) {
				self::$categories[ $slug ] = (int) ( is_array( $result ) ? $result['term_id'] : $result );
			}
		}
	}

	private static function create_products(): void {
		$products = array(
			array( 'Aura Mini Speaker', 'SEEF-TECH-001', 849, 699, 18, 'tech', '#4f7cff', 'Enceinte nomade compacte', 'Un son ample, une autonomie de 14 heures et un boîtier résistant aux éclaboussures pour accompagner chaque déplacement.', 'Aluminium recyclé' ),
			array( 'Pulse ANC Headphones', 'SEEF-TECH-002', 1890, 1590, 12, 'tech', '#635bff', 'Casque sans fil à réduction de bruit', 'Une écoute immersive avec réduction de bruit adaptative, mode transparence et coussinets souples conçus pour les longues sessions.', 'Aluminium et mousse mémoire' ),
			array( 'Arc 7-in-1 Hub', 'SEEF-TECH-003', 790, '', 27, 'tech', '#08a6c9', 'Hub USB-C précis et polyvalent', 'Sept connexions essentielles réunies dans un format compact : HDMI, données rapides, alimentation et lecteurs de cartes.', 'Aluminium anodisé' ),
			array( 'Flow Wireless Charger', 'SEEF-TECH-004', 590, 490, 21, 'tech', '#00a8a8', 'Chargeur magnétique discret', 'Une base stable et inclinée qui maintient le téléphone visible tout en assurant une recharge fiable sur le bureau.', 'Aluminium et silicone' ),
			array( 'Orbit Cable Kit', 'SEEF-ACC-001', 279, '', 34, 'accessoires', '#f08a5d', 'Kit de câbles pour chaque appareil', 'Trois câbles renforcés, des attaches magnétiques et une pochette compacte pour voyager sans nœuds ni adaptateurs oubliés.', 'Nylon tressé' ),
			array( 'Halo Key Organizer', 'SEEF-ACC-002', 320, 269, 22, 'accessoires', '#da5d87', 'Organiseur de clés silencieux', 'Un mécanisme fin qui rassemble jusqu’à six clés et protège les objets glissés dans votre poche.', 'Aluminium' ),
			array( 'Drift Tech Pouch', 'SEEF-ACC-003', 449, '', 16, 'accessoires', '#b067d1', 'Pochette organisée pour accessoires', 'Compartiments souples, ouverture complète et volume maîtrisé pour ranger chargeurs, câbles et petits appareils.', 'Textile déperlant' ),
			array( 'Loop MagSafe Stand', 'SEEF-ACC-004', 399, '', 25, 'accessoires', '#7e6eea', 'Support magnétique pliable', 'Un support de poche stable pour les appels vidéo, la lecture et la recharge en orientation portrait ou paysage.', 'Aluminium' ),
			array( 'Nook Desk Mat', 'SEEF-WORK-001', 520, 449, 20, 'workspace', '#2f78c4', 'Sous-main confortable et durable', 'Une surface douce pour clavier et souris, un dessous antidérapant et des bords cousus pour un bureau net.', 'Feutre recyclé' ),
			array( 'Beam Monitor Light', 'SEEF-WORK-002', 990, 849, 11, 'workspace', '#3468c0', 'Lampe d’écran sans reflet', 'Éclaire précisément le plan de travail sans éblouir l’écran, avec température et intensité réglables.', 'Aluminium' ),
			array( 'Rise Laptop Stand', 'SEEF-WORK-003', 760, '', 15, 'workspace', '#356a8a', 'Support ordinateur ergonomique', 'Élève l’écran à hauteur des yeux, améliore la circulation d’air et se replie facilement pour le transport.', 'Aluminium recyclé' ),
			array( 'Focus Timer', 'SEEF-WORK-004', 430, '', 19, 'workspace', '#1b8e9e', 'Minuteur de concentration tactile', 'Un geste suffit pour lancer une session sans écran, avec alertes lumineuses et sonores réglables.', 'ABS recyclé' ),
			array( 'Terra Insulated Bottle', 'SEEF-LIFE-001', 380, 329, 31, 'lifestyle', '#1d9b74', 'Gourde isotherme 600 ml', 'Garde les boissons fraîches ou chaudes, avec un bouchon étanche et une ouverture facile à nettoyer.', 'Acier inoxydable' ),
			array( 'Comet Everyday Tote', 'SEEF-LIFE-002', 490, '', 14, 'lifestyle', '#d29a32', 'Sac quotidien structuré', 'Un cabas léger avec poche rembourrée pour ordinateur 14 pouces et rangements accessibles.', 'Toile recyclée' ),
			array( 'Mori Aroma Diffuser', 'SEEF-LIFE-003', 690, 579, 13, 'lifestyle', '#8b9961', 'Diffuseur compact à lumière douce', 'Une brume silencieuse, une extinction automatique et une lumière chaude pour installer une ambiance apaisée.', 'Céramique' ),
			array( 'Nomad Travel Mug', 'SEEF-LIFE-004', 340, '', 29, 'lifestyle', '#ba704d', 'Mug de voyage anti-fuite', 'Un format adapté aux porte-gobelets, une ouverture à une main et une isolation pensée pour le trajet.', 'Acier inoxydable' ),
		);

		foreach ( $products as $index => $data ) {
			list( $name, $sku, $price, $sale, $stock, $category, $accent, $short, $description, $material ) = $data;
			$category_id = isset( self::$categories[ $category ] ) ? (int) self::$categories[ $category ] : 0;
			$existing_id = (int) wc_get_product_id_by_sku( $sku );
			if ( $existing_id > 0 ) {
				$existing_product = wc_get_product( $existing_id );
				if ( $existing_product instanceof \WC_Product && $category_id > 0 && $existing_product->get_category_ids() !== array( $category_id ) ) {
					$existing_product->set_category_ids( array( $category_id ) );
					$existing_product->save();
				}
				continue;
			}
			if ( $category_id <= 0 ) {
				continue;
			}
			$product = new \WC_Product_Simple();
			$product->set_name( $name );
			$product->set_slug( sanitize_title( $name ) );
			$product->set_sku( $sku );
			$product->set_regular_price( (string) $price );
			if ( '' !== $sale ) {
				$product->set_sale_price( (string) $sale );
			}
			$product->set_short_description( $short );
			$product->set_description( '<p>' . esc_html( $description ) . '</p><h3>Conçu pour durer</h3><p>Chaque détail associe usage intuitif, matériaux agréables et esthétique sobre. Produit fictif créé exclusivement pour la démonstration SEEF STORE.</p>' );
			$product->set_manage_stock( true );
			$product->set_stock_quantity( $stock );
			$product->set_stock_status( 'instock' );
			$product->set_category_ids( array( $category_id ) );
			$product->set_tag_ids( self::tag_ids( array( 'SEEF Selection', $sale ? 'Promotion' : 'Nouveauté' ) ) );
			$product->set_featured( $index < 6 );
			$product->set_status( 'publish' );
			$attribute = new \WC_Product_Attribute();
			$attribute->set_name( 'Matière' );
			$attribute->set_options( array( $material ) );
			$attribute->set_visible( true );
			$product->set_attributes( array( $attribute ) );
			$id = $product->save();
			$image_id = self::create_svg_attachment( $name, sanitize_title( $name ), $accent, false, $id );
			if ( $image_id ) {
				$product->set_image_id( $image_id );
			}
			if ( $index < 6 ) {
				$gallery_id = self::create_svg_attachment( $name . ' — détail', sanitize_title( $name ) . '-detail', $accent, true, $id );
				if ( $gallery_id ) {
					$product->set_gallery_image_ids( array( $gallery_id ) );
				}
			}
			$product->save();
		}
	}

	/** @param list<string> $names @return list<int> */
	private static function tag_ids( array $names ): array {
		$ids = array();
		foreach ( $names as $name ) {
			$term = term_exists( $name, 'product_tag' ) ?: wp_insert_term( $name, 'product_tag' );
			if ( ! is_wp_error( $term ) ) {
				$ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
			}
		}
		return $ids;
	}

	private static function create_svg_attachment( string $title, string $slug, string $accent, bool $detail, int $parent ): int {
		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'seef-store-products';
		wp_mkdir_p( $dir );
		$file = trailingslashit( $dir ) . $slug . '.svg';
		if ( ! file_exists( $file ) ) {
			$label = esc_html( $title );
			$shape = $detail ? '<circle cx="640" cy="480" r="235" fill="none" stroke="white" stroke-opacity=".42" stroke-width="2"/><circle cx="640" cy="480" r="155" fill="white" fill-opacity=".12"/>' : '<rect x="388" y="228" width="504" height="504" rx="96" fill="white" fill-opacity=".13"/><path d="M455 574L570 454l94 86 102-112 68 146" fill="none" stroke="white" stroke-width="18" stroke-linecap="round" stroke-linejoin="round"/>';
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="960" viewBox="0 0 1280 960" role="img" aria-labelledby="title"><title>' . $label . '</title><defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#081327"/><stop offset="1" stop-color="' . esc_attr( $accent ) . '"/></linearGradient></defs><rect width="1280" height="960" fill="url(#bg)"/>' . $shape . '<text x="64" y="82" fill="white" font-family="Arial,sans-serif" font-size="30" font-weight="700" letter-spacing="5">SEEF STORE</text><text x="64" y="884" fill="white" font-family="Arial,sans-serif" font-size="44" font-weight="700">' . $label . '</text></svg>';
			file_put_contents( $file, $svg ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- trusted generated local asset.
		}
		$url = trailingslashit( $uploads['baseurl'] ) . 'seef-store-products/' . basename( $file );
		$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_seef_asset_slug', 'meta_value' => $slug, 'fields' => 'ids', 'numberposts' => 1 ) );
		if ( $existing ) {
			return (int) $existing[0];
		}
		$id = wp_insert_attachment( array( 'post_mime_type' => 'image/svg+xml', 'post_title' => $title, 'post_status' => 'inherit', 'guid' => $url ), $file, $parent );
		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_attachment_image_alt', $title . ' — visuel de démonstration' );
			update_post_meta( $id, '_seef_asset_slug', $slug );
			return (int) $id;
		}
		return 0;
	}

	private static function create_demo_customer(): bool {
		$user = get_user_by( 'login', 'customer_demo' );
		if ( ! $user ) {
			$password  = (string) getenv( 'SEEF_DEMO_CUSTOMER_PASSWORD' );
			$generated = '' === $password;
			if ( $generated ) {
				$password = wp_generate_password( 24, true, true );
			} elseif ( strlen( $password ) < 12 ) {
				return false;
			}
			$id = wp_create_user( 'customer_demo', $password, 'customer@seef-store.local' );
			if ( is_wp_error( $id ) ) {
				return false;
			}
			if ( $generated && 'cli' === PHP_SAPI ) {
				fwrite( STDOUT, "Generated demo customer password (shown once): {$password}\n" );
			}
			$user = get_user_by( 'id', $id );
			if ( $user instanceof \WP_User ) {
				update_user_meta( $user->ID, '_seef_demo_user', 'yes' );
			}
		}
		if ( $user instanceof \WP_User && ( 'yes' === get_user_meta( $user->ID, '_seef_demo_user', true ) || 'customer@seef-store.local' === $user->user_email ) ) {
			update_user_meta( $user->ID, '_seef_demo_user', 'yes' );
			$user->set_role( 'customer' );
			update_user_meta( $user->ID, 'first_name', 'Client' );
			update_user_meta( $user->ID, 'last_name', 'Démo' );
			update_user_meta( $user->ID, 'billing_first_name', 'Client' );
			update_user_meta( $user->ID, 'billing_last_name', 'Démo' );
			update_user_meta( $user->ID, 'billing_city', 'Marrakech' );
			update_user_meta( $user->ID, 'billing_country', 'MA' );

			return true;
		}

		return false;
	}

	private static function create_demo_order(): void {
		$existing = wc_get_orders( array( 'limit' => 1, 'return' => 'ids', 'meta_key' => '_seef_demo_order', 'meta_value' => 'yes' ) );
		if ( $existing ) {
			return;
		}
		$customer = get_user_by( 'login', 'customer_demo' );
		$product  = wc_get_product( wc_get_product_id_by_sku( 'SEEF-TECH-001' ) );
		if ( ! ( $customer instanceof \WP_User ) || 'yes' !== get_user_meta( $customer->ID, '_seef_demo_user', true ) || ! ( $product instanceof \WC_Product ) ) {
			return;
		}
		$order = wc_create_order( array( 'customer_id' => $customer->ID, 'status' => 'pending', 'created_via' => 'seef-demo-seeder' ) );
		if ( is_wp_error( $order ) ) {
			return;
		}
		$order->add_product( $product, 1 );
		$order->set_address( array( 'first_name' => 'Client', 'last_name' => 'Démo', 'address_1' => 'Adresse fictive — démonstration', 'city' => 'Marrakech', 'country' => 'MA', 'email' => 'customer@seef-store.local' ), 'billing' );
		$order->set_address( array( 'first_name' => 'Client', 'last_name' => 'Démo', 'address_1' => 'Adresse fictive — démonstration', 'city' => 'Marrakech', 'country' => 'MA' ), 'shipping' );
		$order->set_payment_method( 'bacs' );
		$order->set_status( 'completed' );
		$order->add_meta_data( '_seef_demo_order', 'yes', true );
		$order->add_order_note( 'Commande fictive créée pour présenter l’historique client local.' );
		$order->calculate_totals();
		$order->save();
	}

	private static function create_menu(): void {
		$name = 'Navigation principale';
		$menu = wp_get_nav_menu_object( $name );
		$id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $name );
		if ( ! $id ) {
			return;
		}
		if ( ! wp_get_nav_menu_items( $id ) ) {
			foreach ( array( 'home' => 'Accueil', 'shop' => 'Boutique', 'about' => 'À propos', 'contact' => 'Contact' ) as $key => $label ) {
				$option = array( 'home' => 'page_on_front', 'shop' => 'woocommerce_shop_page_id', 'about' => 'seef_about_page_id', 'contact' => 'seef_contact_page_id' )[ $key ];
				wp_update_nav_menu_item( $id, 0, array( 'menu-item-title' => $label, 'menu-item-object' => 'page', 'menu-item-object-id' => (int) get_option( $option ), 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			}
		}
		$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	private static function configure_shipping(): void {
		if ( ! did_action( 'woocommerce_init' ) || ! WC() || ! ( WC()->countries instanceof \WC_Countries ) ) {
			return;
		}
		foreach ( \WC_Shipping_Zones::get_zones() as $zone_data ) {
			if ( 'Maroc — Démonstration' === $zone_data['zone_name'] ) {
				return;
			}
		}
		$zone = new \WC_Shipping_Zone();
		$zone->set_zone_name( 'Maroc — Démonstration' );
		$zone->set_zone_order( 1 );
		$zone->add_location( 'MA', 'country' );
		$zone->save();
		$flat_id = $zone->add_shipping_method( 'flat_rate' );
		$free_id = $zone->add_shipping_method( 'free_shipping' );
		update_option( 'woocommerce_flat_rate_' . $flat_id . '_settings', array( 'title' => 'Livraison standard', 'tax_status' => 'none', 'cost' => '49' ) );
		update_option( 'woocommerce_free_shipping_' . $free_id . '_settings', array( 'title' => 'Livraison offerte', 'requires' => 'min_amount', 'min_amount' => '1000', 'ignore_discounts' => 'no' ) );
	}
}
