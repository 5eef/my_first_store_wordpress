<?php
/** Template Name: SEEF Contact */
get_header();
?>
<main id="main" class="site-main">
	<section class="seef-page-hero seef-page-hero--compact"><div class="seef-shell"><p class="seef-eyebrow"><?php esc_html_e( 'Service client', 'seef-store' ); ?></p><h1><?php esc_html_e( 'Parlons de ce qui compte.', 'seef-store' ); ?></h1><p><?php esc_html_e( 'Une question sur un produit, une commande ou votre expérience ? Notre équipe est à votre écoute.', 'seef-store' ); ?></p></div></section>
	<section class="seef-section"><div class="seef-shell seef-contact-layout" data-reveal>
		<div class="seef-contact-copy"><p class="seef-eyebrow"><?php esc_html_e( 'Écrivez-nous', 'seef-store' ); ?></p><h2><?php esc_html_e( 'Nous vous répondons avec clarté.', 'seef-store' ); ?></h2><p><?php esc_html_e( 'Votre demande est contrôlée et conservée de façon privée afin que notre équipe puisse assurer son suivi.', 'seef-store' ); ?></p><dl><div><dt><?php esc_html_e( 'Disponibilité', 'seef-store' ); ?></dt><dd><?php esc_html_e( 'Lundi–vendredi, 09:00–18:00', 'seef-store' ); ?></dd></div><div><dt><?php esc_html_e( 'Délai indicatif', 'seef-store' ); ?></dt><dd><?php esc_html_e( 'Sous un jour ouvré', 'seef-store' ); ?></dd></div><div><dt><?php esc_html_e( 'Confidentialité', 'seef-store' ); ?></dt><dd><?php esc_html_e( 'N’envoyez aucune donnée personnelle ou bancaire sensible.', 'seef-store' ); ?></dd></div></dl></div>
		<div class="seef-contact-panel"><?php echo do_shortcode( '[seef_contact_form]' ); ?></div>
	</div></section>
	<section class="seef-section seef-faq"><div class="seef-shell"><div class="seef-section-heading"><div><p class="seef-eyebrow"><?php esc_html_e( 'Aide rapide', 'seef-store' ); ?></p><h2><?php esc_html_e( 'Questions fréquentes', 'seef-store' ); ?></h2></div></div><div class="seef-faq-list" data-accordion>
		<details><summary><?php esc_html_e( 'Puis-je effectuer un vrai paiement ?', 'seef-store' ); ?></summary><p><?php esc_html_e( 'Non. Cette vitrine utilise uniquement des moyens de paiement de démonstration et ne doit recevoir aucune donnée bancaire réelle.', 'seef-store' ); ?></p></details>
		<details><summary><?php esc_html_e( 'Les produits peuvent-ils être commandés ?', 'seef-store' ); ?></summary><p><?php esc_html_e( 'Le parcours permet de tester une commande complète, mais les produits et les livraisons restent fictifs.', 'seef-store' ); ?></p></details>
		<details><summary><?php esc_html_e( 'Où retrouver mes commandes ?', 'seef-store' ); ?></summary><p><?php esc_html_e( 'Connectez-vous à Mon compte puis ouvrez la section Commandes.', 'seef-store' ); ?></p></details>
		<details><summary><?php esc_html_e( 'Mon message est-il réellement conservé ?', 'seef-store' ); ?></summary><p><?php esc_html_e( 'Oui. Après validation et contrôle anti-spam, il reste privé et accessible uniquement à l’administrateur.', 'seef-store' ); ?></p></details>
	</div></div></section>
</main>
<?php get_footer(); ?>
