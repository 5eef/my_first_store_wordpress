<?php
/** Footer template. */
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/boutique/' );
?>
<footer class="site-footer">
	<div class="seef-shell seef-footer-grid">
        <div><a
    class="seef-brand seef-brand--footer"
    href="<?php echo esc_url( home_url( '/' ) ); ?>"
    aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
>
    <img
        class="seef-brand-logo seef-brand-logo--footer"
        src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-seef-store-v3.png' ); ?>"
        alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
    >
</a><p><?php esc_html_e( 'Une boutique pensée pour mieux vivre avec la technologie.', 'seef-store' ); ?></p><small><?php esc_html_e( 'Contenu et témoignages de démonstration.', 'seef-store' ); ?></small></div>
		<div><h2><?php esc_html_e( 'Explorer', 'seef-store' ); ?></h2><ul><li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Boutique', 'seef-store' ); ?></a></li><li><a href="<?php echo esc_url( get_permalink( (int) get_option( 'seef_about_page_id' ) ) ); ?>"><?php esc_html_e( 'À propos', 'seef-store' ); ?></a></li><li><a href="<?php echo esc_url( get_permalink( (int) get_option( 'seef_contact_page_id' ) ) ); ?>"><?php esc_html_e( 'Contact', 'seef-store' ); ?></a></li></ul></div>
		<div><h2><?php esc_html_e( 'Votre espace', 'seef-store' ); ?></h2><ul><?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?><li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Mon compte', 'seef-store' ); ?></a></li><li><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Panier', 'seef-store' ); ?></a></li><li><a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Commande', 'seef-store' ); ?></a></li><?php endif; ?></ul></div>
		<div><h2><?php esc_html_e( 'Portfolio', 'seef-store' ); ?></h2><p><?php esc_html_e( 'Expérience e-commerce conçue avec WordPress et WooCommerce.', 'seef-store' ); ?></p><a href="https://github.com/5eef" rel="noopener noreferrer">GitHub / 5eef ↗</a></div>
	</div>
	<div class="seef-shell seef-footer-bottom"><span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> SEEF STORE. <?php esc_html_e( 'Tous droits réservés.', 'seef-store' ); ?></span><span><?php esc_html_e( 'Projet portfolio — démonstration sans vente réelle.', 'seef-store' ); ?></span></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
