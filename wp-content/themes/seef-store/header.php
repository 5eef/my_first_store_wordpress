<?php
/** Header template. */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-seef-store-v3.png?v=3' ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Aller au contenu', 'seef-store' ); ?></a>
<div class="seef-announcement"><div class="seef-shell"><span><?php esc_html_e( 'Livraison offerte dès 1 000 MAD', 'seef-store' ); ?></span><span><?php esc_html_e( 'Expérience e-commerce de démonstration — aucun paiement réel', 'seef-store' ); ?></span></div></div>
<header class="site-header" data-site-header>
	<div class="seef-shell seef-header-inner">
		<a
         class="seef-brand"
         href="<?php echo esc_url( home_url( '/' ) ); ?>"
		>
         <img
             class="seef-brand-logo"
             src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-seef-store-v3.png' ); ?>"
             alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
         >
     </a>
		<div class="seef-desktop-search"><?php get_search_form(); ?></div>
		<div class="seef-header-actions">
			<button class="seef-menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation"><?php echo seef_store_icon( 'menu' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Ouvrir le menu', 'seef-store' ); ?></span></button>
			<button class="seef-search-toggle" type="button" aria-expanded="false" aria-controls="header-search" aria-label="<?php esc_attr_e( 'Rechercher', 'seef-store' ); ?>"><?php echo seef_store_icon( 'search' ); ?></button>
			<div class="seef-language" aria-label="<?php esc_attr_e( 'Langue', 'seef-store' ); ?>">
				<a href="<?php echo esc_url( seef_store_language_url( 'fr' ) ); ?>" lang="fr">FR</a><a href="<?php echo esc_url( seef_store_language_url( 'en' ) ); ?>" lang="en">EN</a><a href="<?php echo esc_url( seef_store_language_url( 'ar' ) ); ?>" lang="ar">ع</a>
			</div>
			<button class="seef-theme-toggle" type="button" aria-label="<?php esc_attr_e( 'Changer le thème', 'seef-store' ); ?>"><?php echo seef_store_icon( 'sun', 'seef-icon-sun' ); ?><?php echo seef_store_icon( 'moon', 'seef-icon-moon' ); ?></button>
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
			<a class="seef-icon-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'Mon compte', 'seef-store' ); ?>"><?php echo seef_store_icon( 'user' ); ?></a>
			<a class="seef-icon-link seef-cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Panier', 'seef-store' ); ?>"><?php echo seef_store_icon( 'cart' ); ?><span class="seef-cart-count"><?php echo esc_html( (string) ( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ) ); ?></span></a>
			<?php endif; ?>
		</div>
	</div>
	<div class="seef-header-nav">
		<div class="seef-shell seef-nav-inner">
			<div class="seef-category-menu">
				<a class="seef-category-trigger" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/boutique/' ) ); ?>"><?php echo seef_store_icon( 'grid' ); ?><?php esc_html_e( 'Catégories', 'seef-store' ); ?><?php echo seef_store_icon( 'chevron', 'seef-icon-chevron' ); ?></a>
				<div class="seef-category-dropdown">
					<?php $header_categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ) ); ?>
					<?php if ( ! is_wp_error( $header_categories ) && $header_categories ) : ?>
						<?php foreach ( $header_categories as $category ) : ?>
							<a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><span><?php echo esc_html( $category->name ); ?></span><small><?php echo esc_html( sprintf( _n( '%d produit', '%d produits', (int) $category->count, 'seef-store' ), (int) $category->count ) ); ?></small></a>
						<?php endforeach; ?>
					<?php else : ?>
						<span><?php esc_html_e( 'Aucune catégorie disponible.', 'seef-store' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
			<nav id="primary-navigation" class="seef-primary-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'seef-store' ); ?>">
				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'seef-menu', 'fallback_cb' => 'seef_store_menu_fallback', 'depth' => 1 ) ); ?>
				<div class="seef-mobile-language"><a href="<?php echo esc_url( seef_store_language_url( 'fr' ) ); ?>" lang="fr">Français</a><a href="<?php echo esc_url( seef_store_language_url( 'en' ) ); ?>" lang="en">English</a><a href="<?php echo esc_url( seef_store_language_url( 'ar' ) ); ?>" lang="ar">العربية</a></div>
				<div class="seef-mobile-search"><?php get_search_form(); ?></div>
			</nav>
			<a class="seef-nav-offer" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/boutique/' ) ); ?>"><span><?php esc_html_e( 'Nouveau', 'seef-store' ); ?></span><?php esc_html_e( 'Sélection 2026', 'seef-store' ); ?> →</a>
		</div>
	</div>
	<div id="header-search" class="seef-header-search" hidden><div class="seef-shell"><?php get_search_form(); ?></div></div>
</header>
