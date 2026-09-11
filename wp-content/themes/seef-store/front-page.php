<?php
get_header();
$shop_url = wc_get_page_permalink( 'shop' );
?>
<main id="main" class="site-main">
	<section class="seef-hero">
		<div class="seef-shell seef-hero-grid">
			<div class="seef-hero-copy" data-reveal>
				<p class="seef-eyebrow"><?php esc_html_e( 'Collection 2026 · Technologie essentielle', 'seef-store' ); ?></p>
				<h1><?php esc_html_e( 'Moins de bruit.', 'seef-store' ); ?><br><span><?php esc_html_e( 'Plus d’essentiel.', 'seef-store' ); ?></span></h1>
				<p class="seef-lead"><?php esc_html_e( 'Des objets tech, workspace et lifestyle choisis pour être beaux, intuitifs et réellement utiles au quotidien.', 'seef-store' ); ?></p>
				<div class="seef-actions"><a class="seef-button" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Découvrir la boutique', 'seef-store' ); ?> <span aria-hidden="true">→</span></a><a class="seef-text-link" href="#new-arrivals"><?php esc_html_e( 'Voir les nouveautés', 'seef-store' ); ?></a></div>
				<ul class="seef-hero-proof"><li><b>16</b> <?php esc_html_e( 'objets sélectionnés', 'seef-store' ); ?></li><li><b>4</b> <?php esc_html_e( 'univers', 'seef-store' ); ?></li><li><b>100%</b> <?php esc_html_e( 'expérience fluide', 'seef-store' ); ?></li></ul>
			</div>
			<div class="seef-hero-visual" aria-label="<?php esc_attr_e( 'Sélection SEEF STORE', 'seef-store' ); ?>" data-reveal>
				<div class="seef-orbit seef-orbit--one"></div><div class="seef-orbit seef-orbit--two"></div>
				<div class="seef-feature-object"><span class="seef-feature-logo">S</span><span><?php esc_html_e( 'Conçu pour votre rythme', 'seef-store' ); ?></span></div>
				<div class="seef-float-card seef-float-card--top"><span><?php echo seef_store_icon( 'sparkles' ); ?></span><b><?php esc_html_e( 'Design calme', 'seef-store' ); ?></b></div>
				<div class="seef-float-card seef-float-card--bottom"><span><?php echo seef_store_icon( 'arrow-right' ); ?></span><b><?php esc_html_e( 'Usage intuitif', 'seef-store' ); ?></b></div>
			</div>
		</div>
	</section>

	<section class="seef-section seef-categories" data-reveal>
		<div class="seef-shell"><div class="seef-section-heading"><div><p class="seef-eyebrow"><?php esc_html_e( 'Explorez autrement', 'seef-store' ); ?></p><h2><?php esc_html_e( 'Nos catégories', 'seef-store' ); ?></h2></div><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Voir tous les produits', 'seef-store' ); ?> →</a></div>
		<div class="seef-category-grid">
		<?php
		$category_icons = array( 'tech' => 'tech', 'accessoires' => 'cart', 'workspace' => 'workspace', 'lifestyle' => 'lifestyle' );
		foreach ( array( 'tech', 'accessoires', 'workspace', 'lifestyle' ) as $slug ) :
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( ! $term ) { continue; }
			?>
			<a class="seef-category-card" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><span class="seef-category-icon"><?php echo seef_store_icon( $category_icons[ $slug ] ); ?></span><span><b><?php echo esc_html( $term->name ); ?></b><small><?php echo esc_html( sprintf( _n( '%d produit', '%d produits', $term->count, 'seef-store' ), $term->count ) ); ?></small></span><i aria-hidden="true"><?php echo seef_store_icon( 'arrow-right' ); ?></i></a>
		<?php endforeach; ?>
		</div></div>
	</section>

	<section id="new-arrivals" class="seef-section seef-products-section" data-reveal>
		<div class="seef-shell"><div class="seef-section-heading"><div><p class="seef-eyebrow"><?php esc_html_e( 'Nouveautés', 'seef-store' ); ?></p><h2><?php esc_html_e( 'Nouveautés choisies pour vous', 'seef-store' ); ?></h2></div><p><?php esc_html_e( 'Des objets qui trouvent naturellement leur place.', 'seef-store' ); ?></p></div>
		<?php seef_store_product_grid( array( 'orderby' => 'date', 'order' => 'DESC' ) ); ?></div>
	</section>

	<section class="seef-editorial" data-reveal><div class="seef-shell seef-editorial-grid">
		<div class="seef-editorial-art"><span>SEEF / 01</span><div class="seef-editorial-disc"></div></div>
		<div class="seef-editorial-copy"><p class="seef-eyebrow"><?php esc_html_e( 'L’idée SEEF', 'seef-store' ); ?></p><h2><?php esc_html_e( 'La technologie disparaît quand elle est bien pensée.', 'seef-store' ); ?></h2><p><?php esc_html_e( 'Nous imaginons une sélection où la fonction reste évidente, les matériaux agréables et le design suffisamment sobre pour durer.', 'seef-store' ); ?></p><a class="seef-button seef-button--light" href="<?php echo esc_url( get_permalink( (int) get_option( 'seef_about_page_id' ) ) ); ?>"><?php esc_html_e( 'Découvrir notre approche', 'seef-store' ); ?></a></div>
	</div></section>

	<section class="seef-section seef-products-section" data-reveal><div class="seef-shell"><div class="seef-section-heading"><div><p class="seef-eyebrow"><?php esc_html_e( 'Sélection', 'seef-store' ); ?></p><h2><?php esc_html_e( 'Les essentiels SEEF', 'seef-store' ); ?></h2></div></div>
	<?php seef_store_product_grid( array( 'tax_query' => array( array( 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => 'featured' ) ) ) ); ?></div></section>

	<section class="seef-section seef-benefits" data-reveal><div class="seef-shell"><div class="seef-section-heading"><div><p class="seef-eyebrow"><?php esc_html_e( 'Notre promesse', 'seef-store' ); ?></p><h2><?php esc_html_e( 'Pourquoi SEEF STORE ?', 'seef-store' ); ?></h2></div></div><div class="seef-benefit-grid">
		<article><span>01</span><h3><?php esc_html_e( 'Livraison suivie', 'seef-store' ); ?></h3><p><?php esc_html_e( 'Une expérience de livraison claire, avec frais annoncés avant validation.', 'seef-store' ); ?></p></article>
		<article><span>02</span><h3><?php esc_html_e( 'Commande en toute sérénité', 'seef-store' ); ?></h3><p><?php esc_html_e( 'Un parcours de démonstration sécurisé, sans collecte de données bancaires réelles.', 'seef-store' ); ?></p></article>
		<article><span>03</span><h3><?php esc_html_e( 'Retours simplifiés', 'seef-store' ); ?></h3><p><?php esc_html_e( 'Un parcours client lisible et des informations faciles à retrouver.', 'seef-store' ); ?></p></article>
		<article><span>04</span><h3><?php esc_html_e( 'Support humain', 'seef-store' ); ?></h3><p><?php esc_html_e( 'Chaque demande est validée, enregistrée et consultable par l’administrateur.', 'seef-store' ); ?></p></article>
	</div></div></section>

	<section class="seef-section seef-testimonials" data-reveal><div class="seef-shell"><div class="seef-section-heading"><div><p class="seef-eyebrow"><?php esc_html_e( 'Expérience client', 'seef-store' ); ?></p><h2><?php esc_html_e( 'Pensé dans les moindres détails', 'seef-store' ); ?></h2></div><small><?php esc_html_e( 'Scénarios illustratifs — projet démo', 'seef-store' ); ?></small></div><div class="seef-testimonial-grid">
		<blockquote><p>“<?php esc_html_e( 'Une sélection courte, cohérente et beaucoup plus simple à explorer.', 'seef-store' ); ?>”</p><footer>Amal R. · <?php esc_html_e( 'profil fictif', 'seef-store' ); ?></footer></blockquote>
		<blockquote><p>“<?php esc_html_e( 'Le parcours d’achat est net, de la fiche produit jusqu’à la commande.', 'seef-store' ); ?>”</p><footer>Mehdi K. · <?php esc_html_e( 'profil fictif', 'seef-store' ); ?></footer></blockquote>
		<blockquote><p>“<?php esc_html_e( 'Le mode sombre et la version mobile donnent vraiment envie de parcourir le catalogue.', 'seef-store' ); ?>”</p><footer>Lina B. · <?php esc_html_e( 'profil fictif', 'seef-store' ); ?></footer></blockquote>
	</div></div></section>

	<section class="seef-newsletter-band" data-reveal><div class="seef-shell seef-newsletter-inner"><div><p class="seef-eyebrow"><?php esc_html_e( 'Newsletter', 'seef-store' ); ?></p><h2><?php esc_html_e( 'Une dose d’inspiration, rarement mais bien.', 'seef-store' ); ?></h2></div><?php echo do_shortcode( '[seef_newsletter_form]' ); ?></div></section>
</main>
<?php get_footer(); ?>
