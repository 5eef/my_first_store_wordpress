<?php

declare(strict_types=1);

namespace SeefStore\Controllers;

use SeefStore\Models\ContactMessage;
use SeefStore\Support\Service;
use SeefStore\Validators\ContactValidator;

final class ContactController implements Service {
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'admin_post_seef_contact_submit', array( $this, 'submit' ) );
		add_action( 'admin_post_nopriv_seef_contact_submit', array( $this, 'submit' ) );
	}

	public function register_post_type(): void {
		$capabilities = array_fill_keys(
			array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts', 'create_posts' ),
			'manage_woocommerce'
		);

		register_post_type(
			ContactMessage::POST_TYPE,
			array(
				'labels' => array(
					'name'          => __( 'Messages de contact', 'seef-store-core' ),
					'singular_name' => __( 'Message de contact', 'seef-store-core' ),
					'menu_name'     => __( 'Messages clients', 'seef-store-core' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'woocommerce',
				'menu_icon'           => 'dashicons-email-alt2',
				'supports'            => array( 'title', 'editor' ),
				'capabilities'        => $capabilities,
				'map_meta_cap'        => false,
				'exclude_from_search' => true,
			)
		);
	}

	public function submit(): void {
		$request = wp_unslash( $_POST );
		$nonce   = sanitize_text_field( (string) ( $request['seef_contact_nonce'] ?? '' ) );

		if ( ! wp_verify_nonce( $nonce, 'seef_contact_submit' ) ) {
			$this->redirect( 'security' );
		}
		if ( ! empty( $request['company'] ) ) {
			$this->redirect( 'success' );
		}
		if ( $this->is_rate_limited() ) {
			$this->redirect( 'rate_limited' );
		}

		$result = ( new ContactValidator() )->validate( $request );
		if ( $result['errors'] ) {
			set_transient( $this->feedback_key(), $result['errors'], 5 * MINUTE_IN_SECONDS );
			$this->redirect( 'invalid' );
		}

		$post_id = ContactMessage::create( $result['data'] );
		if ( is_wp_error( $post_id ) ) {
			$this->redirect( 'error' );
		}

		$this->increment_rate_limit();
		$this->redirect( 'success' );
	}

	private function redirect( string $status ): never {
		$fallback = get_permalink( (int) get_option( 'seef_contact_page_id' ) ) ?: home_url( '/contact/' );
		$target   = wp_get_referer() ?: $fallback;
		$target   = remove_query_arg( array( 'contact_status' ), $target );
		wp_safe_redirect( add_query_arg( 'contact_status', sanitize_key( $status ), $target ) );
		exit;
	}

	private function client_hash(): string {
		$ip = preg_replace( '/[^0-9a-fA-F:.]/', '', (string) ( $_SERVER['REMOTE_ADDR'] ?? 'local' ) );
		return hash_hmac( 'sha256', $ip ?: 'local', wp_salt( 'nonce' ) );
	}

	private function rate_key(): string {
		return 'seef_contact_rate_' . substr( $this->client_hash(), 0, 32 );
	}

	private function feedback_key(): string {
		return 'seef_contact_feedback_' . substr( $this->client_hash(), 0, 32 );
	}

	private function is_rate_limited(): bool {
		return (int) get_transient( $this->rate_key() ) >= 4;
	}

	private function increment_rate_limit(): void {
		set_transient( $this->rate_key(), (int) get_transient( $this->rate_key() ) + 1, 10 * MINUTE_IN_SECONDS );
	}

	public static function feedback(): array {
		$ip  = preg_replace( '/[^0-9a-fA-F:.]/', '', (string) ( $_SERVER['REMOTE_ADDR'] ?? 'local' ) );
		$key = 'seef_contact_feedback_' . substr( hash_hmac( 'sha256', $ip ?: 'local', wp_salt( 'nonce' ) ), 0, 32 );
		$messages = get_transient( $key );
		delete_transient( $key );
		return is_array( $messages ) ? array_map( 'sanitize_text_field', $messages ) : array();
	}
}
