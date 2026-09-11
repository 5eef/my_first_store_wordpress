<?php get_header(); ?>
<main id="main" class="site-main seef-shell seef-error-page"><p class="seef-eyebrow">404</p><h1><?php esc_html_e( 'Cette page s’est égarée.', 'seef-store' ); ?></h1><p><?php esc_html_e( 'Revenez à l’accueil ou poursuivez votre exploration dans la boutique.', 'seef-store' ); ?></p><a class="seef-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Retour à l’accueil', 'seef-store' ); ?></a></main>
<?php get_footer(); ?>
