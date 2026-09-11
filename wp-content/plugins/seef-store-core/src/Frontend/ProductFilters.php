<?php

declare(strict_types=1);

namespace SeefStore\Frontend;

use SeefStore\Support\Service;

final class ProductFilters implements Service {
	public function register(): void {
		add_action( 'woocommerce_before_shop_loop', array( $this, 'render' ), 4 );
		add_action( 'pre_get_posts', array( $this, 'apply' ), 30 );
	}

	public function apply( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! ( is_shop() || is_product_taxonomy() ) ) {
			return;
		}

		$category = sanitize_title( (string) ( $_GET['seef_category'] ?? '' ) );
		if ( $category ) {
			$tax_query   = (array) $query->get( 'tax_query' );
			$tax_query[] = array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $category );
			$query->set( 'tax_query', $tax_query );
		}

		$min = isset( $_GET['min_price'] ) ? wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) : '';
		$max = isset( $_GET['max_price'] ) ? wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) : '';
		$meta_query = (array) $query->get( 'meta_query' );
		if ( '' !== $min || '' !== $max ) {
			$range = array( 'key' => '_price', 'type' => 'DECIMAL(10,2)' );
			if ( '' !== $min && '' !== $max ) {
				$range += array( 'value' => array( max( 0, (float) $min ), max( 0, (float) $max ) ), 'compare' => 'BETWEEN' );
			} elseif ( '' !== $min ) {
				$range += array( 'value' => max( 0, (float) $min ), 'compare' => '>=' );
			} else {
				$range += array( 'value' => max( 0, (float) $max ), 'compare' => '<=' );
			}
			$meta_query[] = $range;
		}
		if ( isset( $_GET['in_stock'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['in_stock'] ) ) ) {
			$meta_query[] = array( 'key' => '_stock_status', 'value' => 'instock' );
		}
		$query->set( 'meta_query', $meta_query );
	}

	public function render(): void {
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}
		$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
		?>
		<form class="seef-shop-filters" method="get" action="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">
			<strong><?php esc_html_e( 'Filtrer la boutique', 'seef-store-core' ); ?></strong>
			<label><span class="screen-reader-text"><?php esc_html_e( 'Toutes les catégories', 'seef-store-core' ); ?></span><select name="seef_category"><option value=""><?php esc_html_e( 'Toutes les catégories', 'seef-store-core' ); ?></option>
			<?php foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) : ?>
				<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( sanitize_title( (string) ( $_GET['seef_category'] ?? '' ) ), $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
			<?php endforeach; ?></select></label>
			<label><span class="screen-reader-text"><?php esc_html_e( 'Prix minimum', 'seef-store-core' ); ?></span><input type="number" min="0" step="1" name="min_price" placeholder="<?php esc_attr_e( 'Prix minimum', 'seef-store-core' ); ?>" value="<?php echo esc_attr( (string) ( $_GET['min_price'] ?? '' ) ); ?>"></label>
			<label><span class="screen-reader-text"><?php esc_html_e( 'Prix maximum', 'seef-store-core' ); ?></span><input type="number" min="0" step="1" name="max_price" placeholder="<?php esc_attr_e( 'Prix maximum', 'seef-store-core' ); ?>" value="<?php echo esc_attr( (string) ( $_GET['max_price'] ?? '' ) ); ?>"></label>
			<label class="seef-check"><input type="checkbox" name="in_stock" value="1" <?php checked( (string) ( $_GET['in_stock'] ?? '' ), '1' ); ?>> <?php esc_html_e( 'En stock uniquement', 'seef-store-core' ); ?></label>
			<button type="submit" class="button"><?php esc_html_e( 'Appliquer', 'seef-store-core' ); ?></button>
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'Réinitialiser', 'seef-store-core' ); ?></a>
		</form>
		<?php
	}
}
