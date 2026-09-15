<?php
$shop = seef_store_wc_url( 'shop' );
$seef_search_input_id = wp_unique_id( 'seef-search-' );
?>
<form role="search" method="get" class="seef-search-form" action="<?php echo esc_url( $shop ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $seef_search_input_id ); ?>"><?php esc_html_e( 'Rechercher des produits…', 'seef-store' ); ?></label>
	<span class="seef-search-icon" aria-hidden="true"><?php echo seef_store_icon( 'search' ); ?></span>
	<input id="<?php echo esc_attr( $seef_search_input_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Rechercher un produit, une catégorie…', 'seef-store' ); ?>">
	<input type="hidden" name="post_type" value="product">
	<button type="submit"><?php esc_html_e( 'Rechercher', 'seef-store' ); ?></button>
</form>
