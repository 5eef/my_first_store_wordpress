<?php
/** Footer template. */
$shop_url = seef_store_wc_url( 'shop' );
?>
<footer class="site-footer">
	<div class="seef-shell seef-footer-grid">
        <div class="seef-footer-brand"><a class="seef-brand seef-brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"><img class="seef-brand-logo seef-brand-logo--footer" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-seef-store.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></a><p><?php esc_html_e( 'Une boutique pensée pour mieux vivre avec la technologie.', 'seef-store' ); ?></p><small><?php esc_html_e( 'Contenu et témoignages de démonstration.', 'seef-store' ); ?></small></div>
        <div class="seef-footer-column"><h2 class="seef-footer-title"><?php esc_html_e( 'Explorer', 'seef-store' ); ?></h2><ul class="seef-footer-links"><li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Boutique', 'seef-store' ); ?></a></li><li><a href="<?php echo esc_url( seef_store_content_page_url( 'seef_about_page_id', '/a-propos/' ) ); ?>"><?php esc_html_e( 'À propos', 'seef-store' ); ?></a></li><li><a href="<?php echo esc_url( seef_store_content_page_url( 'seef_contact_page_id', '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'seef-store' ); ?></a></li></ul></div>
        <div class="seef-footer-column"><h2 class="seef-footer-title"><?php esc_html_e( 'Votre espace', 'seef-store' ); ?></h2><ul class="seef-footer-links"><li><a href="<?php echo esc_url( seef_store_wc_url( 'myaccount' ) ); ?>"><?php esc_html_e( 'Mon compte', 'seef-store' ); ?></a></li><li><a href="<?php echo esc_url( seef_store_wc_url( 'cart' ) ); ?>"><?php esc_html_e( 'Panier', 'seef-store' ); ?></a></li><li><a href="<?php echo esc_url( seef_store_wc_url( 'checkout' ) ); ?>"><?php esc_html_e( 'Commande', 'seef-store' ); ?></a></li></ul></div>
        <div class="seef-footer-column seef-footer-portfolio"><h2 class="seef-footer-title"><?php esc_html_e( 'Portfolio', 'seef-store' ); ?></h2><p><?php esc_html_e( 'Expérience e-commerce conçue avec WordPress et WooCommerce.', 'seef-store' ); ?></p><a href="https://github.com/5eef" rel="noopener noreferrer">GitHub / 5eef ↗</a></div>
	</div>
	<div class="seef-shell seef-footer-bottom"><span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> SEEF STORE. <?php esc_html_e( 'Tous droits réservés.', 'seef-store' ); ?></span><span><?php esc_html_e( 'Projet portfolio — démonstration sans vente réelle.', 'seef-store' ); ?></span></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
