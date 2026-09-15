<?php

declare(strict_types=1);

namespace SeefStore\Admin;

use SeefStore\Models\ContactMessage;
use SeefStore\Support\Service;

final class ContactAdmin implements Service {
	public function register(): void {
		add_filter( 'manage_' . ContactMessage::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . ContactMessage::POST_TYPE . '_posts_custom_column', array( $this, 'column_value' ), 10, 2 );
		add_action( 'add_meta_boxes_' . ContactMessage::POST_TYPE, array( $this, 'meta_boxes' ) );
		add_action( 'save_post_' . ContactMessage::POST_TYPE, array( $this, 'save' ) );
	}

	/** @param array<string,string> $columns @return array<string,string> */
	public function columns( array $columns ): array {
		return array(
			'cb'          => $columns['cb'] ?? '<input type="checkbox">',
			'title'       => __( 'Sujet', 'seef-store-core' ),
			'seef_sender' => __( 'Expéditeur', 'seef-store-core' ),
			'seef_status' => __( 'Statut', 'seef-store-core' ),
			'date'        => __( 'Date', 'seef-store-core' ),
		);
	}

	public function column_value( string $column, int $post_id ): void {
		if ( 'seef_sender' === $column ) {
			$name  = (string) get_post_meta( $post_id, '_seef_name', true );
			$email = (string) get_post_meta( $post_id, '_seef_email', true );
			printf( '%1$s<br><a href="mailto:%2$s">%2$s</a>', esc_html( $name ), esc_attr( $email ) );
		}
		if ( 'seef_status' === $column ) {
			$status = ContactMessage::status( $post_id );
			printf( '<span class="seef-admin-status seef-admin-status--%1$s">%2$s</span>', esc_attr( $status ), esc_html( ucfirst( $status ) ) );
		}
	}

	public function meta_boxes(): void {
		add_meta_box( 'seef_contact_details', __( 'Détails du contact', 'seef-store-core' ), array( $this, 'details_box' ), ContactMessage::POST_TYPE, 'side', 'high' );
	}

	public function details_box( \WP_Post $post ): void {
		wp_nonce_field( 'seef_contact_admin', 'seef_contact_admin_nonce' );
		$name   = (string) get_post_meta( $post->ID, '_seef_name', true );
		$email  = (string) get_post_meta( $post->ID, '_seef_email', true );
		$status = ContactMessage::status( $post->ID );
		echo '<p><strong>' . esc_html__( 'Nom', 'seef-store-core' ) . '</strong><br>' . esc_html( $name ) . '</p>';
		echo '<p><strong>' . esc_html__( 'E-mail', 'seef-store-core' ) . '</strong><br><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
		echo '<p><label for="seef-status"><strong>' . esc_html__( 'Statut', 'seef-store-core' ) . '</strong></label><br>';
		echo '<select id="seef-status" name="seef_status">';
		foreach ( array( 'new' => __( 'Nouveau', 'seef-store-core' ), 'read' => __( 'Lu', 'seef-store-core' ), 'replied' => __( 'Répondu', 'seef-store-core' ), 'archived' => __( 'Archivé', 'seef-store-core' ) ) as $value => $label ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $value ), selected( $status, $value, false ), esc_html( $label ) );
		}
		echo '</select></p>';
	}

	public function save( int $post_id ): void {
		$request = wp_unslash( $_POST );
		$nonce   = $request['seef_contact_admin_nonce'] ?? '';
		if ( ! is_scalar( $nonce ) || ! wp_verify_nonce( sanitize_text_field( (string) $nonce ), 'seef_contact_admin' ) ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$status = is_scalar( $request['seef_status'] ?? '' ) ? sanitize_key( (string) $request['seef_status'] ) : '';
		if ( in_array( $status, array( 'new', 'read', 'replied', 'archived' ), true ) ) {
			update_post_meta( $post_id, '_seef_status', $status );
		}
	}
}
