<?php

declare(strict_types=1);

namespace SeefStoreTheme;

final class Theme {
	private static ?self $instance = null;

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	public function boot(): void {
		add_action( 'after_setup_theme', array( $this, 'setup' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'wp_head', array( $this, 'theme_bootstrap' ), 1 );
		add_action( 'wp_head', array( $this, 'meta_tags' ), 5 );
		add_action( 'wp_head', array( $this, 'canonical_tag' ), 9 );
		add_action( 'wp_head', array( $this, 'structured_data' ), 30 );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_filter( 'document_title_separator', static fn(): string => '·' );
		add_filter( 'wp_robots', array( $this, 'filter_robots' ) );
		add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragment' ) );
		add_filter( 'woocommerce_sale_flash', array( $this, 'sale_flash' ), 10, 3 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'product_new_badge' ), 11 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'product_stock_badge' ), 12 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'product_reassurance' ), 35 );
		add_action( 'woocommerce_before_shop_loop', array( $this, 'shop_filters' ), 15 );
		add_action( 'pre_get_posts', array( $this, 'filter_shop_query' ) );
		add_filter( 'loop_shop_per_page', static fn(): int => 12 );
		add_filter( 'woocommerce_output_related_products_args', static fn( array $args ): array => array_merge( $args, array( 'posts_per_page' => 4, 'columns' => 4 ) ) );
		add_action( 'init', array( $this, 'woocommerce_wrappers' ) );
	}

	public function setup(): void {
		load_theme_textdomain( 'seef-store', get_template_directory() . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 720, 'single_image_width' => 1080, 'product_grid' => array( 'default_rows' => 3, 'min_rows' => 1, 'max_rows' => 8, 'default_columns' => 3, 'min_columns' => 1, 'max_columns' => 4 ) ) );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
		register_nav_menus( array( 'primary' => __( 'Navigation principale', 'seef-store' ), 'footer' => __( 'Navigation de pied de page', 'seef-store' ) ) );
	}

	public function assets(): void {
		$version = wp_get_theme()->get( 'Version' );
		wp_enqueue_style( 'seef-store', get_template_directory_uri() . '/assets/css/store.css', array(), $version );
		wp_enqueue_script( 'seef-store', get_template_directory_uri() . '/assets/js/store.js', array(), $version, true );
		wp_localize_script( 'seef-store', 'seefStore', array( 'menuOpen' => __( 'Ouvrir le menu', 'seef-store' ), 'menuClose' => __( 'Fermer le menu', 'seef-store' ), 'themeLight' => __( 'Activer le thème clair', 'seef-store' ), 'themeDark' => __( 'Activer le thème sombre', 'seef-store' ) ) );
	}

	public function theme_bootstrap(): void {
		echo "<script>(function(){try{var t=localStorage.getItem('seef-theme')||((window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches)?'dark':'light');document.documentElement.dataset.theme=t;}catch(e){}})();</script>\n";
	}

	public function meta_tags(): void {
		if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
			return;
		}

		$description = (string) get_bloginfo( 'description' );
		$url         = home_url( '/' );
		$image       = '';

		if ( is_singular( 'product' ) ) {
			$product = wc_get_product( get_queried_object_id() );
			if ( $product ) {
				$description = wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() );
				$image       = wp_get_attachment_image_url( $product->get_image_id(), 'full' ) ?: '';
			}
		} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
			$description = __( 'Découvrez la sélection SEEF STORE : technologie, accessoires, espace de travail et objets lifestyle pensés pour le quotidien.', 'seef-store' );
		} elseif ( is_tax( 'product_cat' ) ) {
			$term_description = term_description();
			$description      = $term_description ? wp_strip_all_tags( $term_description ) : sprintf( __( 'Découvrez notre sélection %s, choisie pour son design et sa simplicité d’usage.', 'seef-store' ), single_term_title( '', false ) );
		} elseif ( is_page() && has_excerpt() ) {
			$description = get_the_excerpt();
		}

		if ( is_singular() ) {
			$url   = get_permalink() ?: $url;
			$image = $image ?: ( get_the_post_thumbnail_url( get_queried_object_id(), 'full' ) ?: '' );
		} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
			$url = wc_get_page_permalink( 'shop' );
		} elseif ( is_tax() ) {
			$term_url = get_term_link( get_queried_object() );
			$url      = is_wp_error( $term_url ) ? $url : $term_url;
		}

		if ( ! $image && has_site_icon() ) {
			$image = get_site_icon_url( 512 );
		}

		$description = wp_trim_words( $description, 30, '' );
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( wp_get_document_title() ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
		printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'product' ) ? 'product' : 'website' );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( get_locale() ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( wp_get_document_title() ) );
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
		if ( $image ) {
			printf( '<meta property="og:image" content="%1$s">' . "\n" . '<meta name="twitter:image" content="%1$s">' . "\n", esc_url( $image ) );
		}
	}

	public function structured_data(): void {
		if ( ! is_front_page() || defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
			return;
		}

		$schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'OnlineStore',
			'name'            => get_bloginfo( 'name' ),
			'url'             => home_url( '/' ),
			'description'     => get_bloginfo( 'description' ),
			'sameAs'          => array( 'https://github.com/5eef' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => add_query_arg( array( 's' => '{search_term_string}', 'post_type' => 'product' ), wc_get_page_permalink( 'shop' ) ),
				'query-input' => 'required name=search_term_string',
			),
		);

		if ( has_site_icon() ) {
			$schema['logo'] = get_site_icon_url( 512 );
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}

	public function canonical_tag(): void {
		if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || ! function_exists( 'is_shop' ) ) {
			return;
		}

		$url = '';
		if ( is_shop() ) {
			$url = wc_get_page_permalink( 'shop' );
		} elseif ( is_product_taxonomy() ) {
			$term_url = get_term_link( get_queried_object() );
			$url      = is_wp_error( $term_url ) ? '' : $term_url;
		}

		$paged = max( 1, (int) get_query_var( 'paged' ) );
		if ( $url && $paged > 1 ) {
			$url = remove_query_arg( array( 'seef_category', 'min_price', 'max_price', 'in_stock' ), get_pagenum_link( $paged ) );
		}

		if ( $url ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
		}
	}

	/** @param array<string,bool|string> $robots @return array<string,bool|string> */
	public function filter_robots( array $robots ): array {
		if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
			return $robots;
		}

		$facet_keys = array( 'seef_category', 'min_price', 'max_price', 'in_stock' );
		foreach ( $facet_keys as $key ) {
			if ( isset( $_GET[ $key ] ) && '' !== (string) wp_unslash( $_GET[ $key ] ) ) {
				$robots['noindex'] = true;
				$robots['follow']  = true;
				unset( $robots['index'], $robots['nofollow'] );
				break;
			}
		}

		return $robots;
	}

	/** @param list<string> $classes @return list<string> */
	public function body_classes( array $classes ): array {
		$classes[] = 'seef-store';
		return $classes;
	}

	public function cart_fragment( array $fragments ): array {
		ob_start();
		?>
		<span class="seef-cart-count" aria-label="<?php esc_attr_e( 'Articles dans le panier', 'seef-store' ); ?>"><?php echo esc_html( (string) ( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ) ); ?></span>
		<?php
		$fragments['.seef-cart-count'] = (string) ob_get_clean();
		return $fragments;
	}

	public function product_stock_badge(): void {
		global $product;
		if ( $product instanceof \WC_Product ) {
			printf( '<span class="seef-stock-badge seef-stock-badge--%1$s">%2$s</span>', $product->is_in_stock() ? 'in' : 'out', esc_html( $product->is_in_stock() ? __( 'En stock', 'seef-store' ) : __( 'Rupture de stock', 'seef-store' ) ) );
		}
	}

	public function product_new_badge(): void {
		global $product;
		if ( ! $product instanceof \WC_Product || ! $product->get_date_created() ) {
			return;
		}
		if ( $product->get_date_created()->getTimestamp() >= strtotime( '-60 days' ) ) {
			echo '<span class="seef-new-badge">' . esc_html__( 'NEW', 'seef-store' ) . '</span>';
		}
	}

	/** @param mixed $post */
	public function sale_flash( string $html, $post, \WC_Product $product ): string {
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
			$percentage = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
			return sprintf( '<span class="onsale">-%d%%</span>', $percentage );
		}
		return $html;
	}

	public function product_reassurance(): void {
		?>
		<ul class="seef-product-reassurance">
			<li><span aria-hidden="true"><?php echo seef_store_icon( 'cart' ); ?></span><?php esc_html_e( 'Paiement de démonstration sans données bancaires réelles', 'seef-store' ); ?></li>
			<li><span aria-hidden="true"><?php echo seef_store_icon( 'package' ); ?></span><?php esc_html_e( 'Livraison suivie au Maroc', 'seef-store' ); ?></li>
			<li><span aria-hidden="true"><?php echo seef_store_icon( 'sparkles' ); ?></span><?php esc_html_e( 'Assistance via le formulaire sécurisé', 'seef-store' ); ?></li>
		</ul>
		<?php
	}

	public function shop_filters(): void {
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);
		$selected_category = isset( $_GET['seef_category'] ) ? sanitize_title( wp_unslash( $_GET['seef_category'] ) ) : '';
		$min_price         = isset( $_GET['min_price'] ) ? wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) : '';
		$max_price         = isset( $_GET['max_price'] ) ? wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) : '';
		$in_stock          = isset( $_GET['in_stock'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['in_stock'] ) );
		?>
		<form class="seef-shop-filters" method="get" action="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
			<strong><?php esc_html_e( 'Filtrer les produits', 'seef-store' ); ?></strong>
			<fieldset>
				<legend><?php esc_html_e( 'Catégories', 'seef-store' ); ?></legend>
				<?php if ( ! is_wp_error( $categories ) && $categories ) : ?>
					<?php foreach ( $categories as $category ) : ?>
						<label class="seef-check">
							<input type="radio" name="seef_category" value="<?php echo esc_attr( $category->slug ); ?>" <?php checked( $selected_category, $category->slug ); ?>>
							<span><?php echo esc_html( $category->name ); ?></span>
							<small><?php echo esc_html( (string) $category->count ); ?></small>
						</label>
					<?php endforeach; ?>
				<?php else : ?>
					<span class="seef-filter-empty"><?php esc_html_e( 'Aucune catégorie disponible.', 'seef-store' ); ?></span>
				<?php endif; ?>
			</fieldset>
			<label>
				<span><?php esc_html_e( 'Prix minimum', 'seef-store' ); ?></span>
				<input type="number" name="min_price" min="0" step="0.01" value="<?php echo esc_attr( $min_price ); ?>">
			</label>
			<label>
				<span><?php esc_html_e( 'Prix maximum', 'seef-store' ); ?></span>
				<input type="number" name="max_price" min="0" step="0.01" value="<?php echo esc_attr( $max_price ); ?>">
			</label>
			<label class="seef-check"><input type="checkbox" name="in_stock" value="1" <?php checked( $in_stock ); ?>><span><?php esc_html_e( 'En stock uniquement', 'seef-store' ); ?></span></label>
			<button class="seef-button" type="submit"><?php esc_html_e( 'Appliquer', 'seef-store' ); ?></button>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Réinitialiser', 'seef-store' ); ?></a>
		</form>
		<?php
	}

	public function filter_shop_query( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
			return;
		}

		$tax_query = (array) $query->get( 'tax_query' );
		$category  = isset( $_GET['seef_category'] ) ? sanitize_title( wp_unslash( $_GET['seef_category'] ) ) : '';
		if ( $category ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $category,
			);
		}
		if ( count( $tax_query ) > 1 && ! isset( $tax_query['relation'] ) ) {
			$tax_query['relation'] = 'AND';
		}
		if ( $tax_query ) {
			$query->set( 'tax_query', $tax_query );
		}

		$meta_query = (array) $query->get( 'meta_query' );
		$min_price  = isset( $_GET['min_price'] ) ? (float) wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) : 0;
		$max_price  = isset( $_GET['max_price'] ) ? (float) wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) : 0;
		if ( $min_price > 0 ) {
			$meta_query[] = array( 'key' => '_price', 'value' => $min_price, 'compare' => '>=', 'type' => 'NUMERIC' );
		}
		if ( $max_price > 0 ) {
			$meta_query[] = array( 'key' => '_price', 'value' => $max_price, 'compare' => '<=', 'type' => 'NUMERIC' );
		}
		if ( isset( $_GET['in_stock'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['in_stock'] ) ) ) {
			$meta_query[] = array( 'key' => '_stock_status', 'value' => 'instock' );
		}
		if ( $meta_query ) {
			$query->set( 'meta_query', $meta_query );
		}
	}

	public function woocommerce_wrappers(): void {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		add_action( 'woocommerce_before_main_content', static function (): void { echo '<main id="main" class="site-main seef-shell seef-shop-main">'; }, 10 );
		add_action( 'woocommerce_after_main_content', static function (): void { echo '</main>'; }, 10 );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	}
}
