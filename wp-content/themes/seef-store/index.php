<?php get_header(); ?>
<main id="main" class="site-main seef-shell seef-content-page">
	<header><h1><?php echo esc_html( is_search() ? sprintf( __( 'Résultats pour « %s »', 'seef-store' ), get_search_query() ) : get_bloginfo( 'name' ) ); ?></h1></header>
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?><article <?php post_class( 'seef-post-card' ); ?>><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article><?php endwhile; the_posts_pagination(); else : ?><p><?php esc_html_e( 'Aucun contenu trouvé.', 'seef-store' ); ?></p><?php endif; ?>
</main>
<?php get_footer(); ?>
