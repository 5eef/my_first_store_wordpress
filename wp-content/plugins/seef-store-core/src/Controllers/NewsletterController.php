<?php

declare(strict_types=1);

namespace SeefStore\Controllers;

use SeefStore\Support\Service;

final class NewsletterController implements Service {
	private const POST_TYPE = 'seef_subscriber';
	private const RATE_LIMIT = 4;

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'admin_post_seef_newsletter_submit', array( $this, 'submit' ) );
		add_action( 'admin_post_nopriv_seef_newsletter_submit', array( $this, 'submit' ) );
		add_shortcode( 'seef_newsletter_form', array( $this, 'form' ) );
	}

	public function register_post_type(): void {
		register_post_type( self::POST_TYPE, array( 'labels' => array( 'name' => __( 'Newsletter', 'seef-store-core' ), 'singular_name' => __( 'Abonné', 'seef-store-core' ) ), 'public' => false, 'show_ui' => true, 'show_in_menu' => 'woocommerce', 'supports' => array( 'title' ), 'capability_type' => 'post', 'capabilities' => array( 'edit_posts' => 'manage_woocommerce', 'edit_others_posts' => 'manage_woocommerce', 'delete_posts' => 'manage_woocommerce', 'read_private_posts' => 'manage_woocommerce', 'publish_posts' => 'manage_woocommerce', 'create_posts' => 'do_not_allow' ), 'map_meta_cap' => true ) );
	}

	public function form(): string {
		$status_raw = $_GET['newsletter_status'] ?? '';
		$status     = is_scalar( $status_raw ) ? sanitize_key( wp_unslash( (string) $status_raw ) ) : '';
		ob_start();
		if ( 'success' === $status ) {
			echo '<p class="seef-newsletter-status" role="status">' . esc_html__( 'Inscription enregistrée — démonstration locale.', 'seef-store-core' ) . '</p>';
		} elseif ( 'invalid' === $status ) {
			echo '<p class="seef-newsletter-status" role="alert">' . esc_html__( 'Saisissez une adresse e-mail valide.', 'seef-store-core' ) . '</p>';
		} elseif ( 'rate_limited' === $status ) {
			echo '<p class="seef-newsletter-status" role="alert">' . esc_html__( 'Trop de tentatives. Réessayez dans quelques minutes.', 'seef-store-core' ) . '</p>';
		} elseif ( 'security' === $status ) {
			echo '<p class="seef-newsletter-status" role="alert">' . esc_html__( 'La vérification de sécurité a échoué. Rechargez la page.', 'seef-store-core' ) . '</p>';
		}
		?>
		<form class="seef-newsletter-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="seef_newsletter_submit"><?php wp_nonce_field( 'seef_newsletter_submit', 'seef_newsletter_nonce' ); ?>
			<div class="seef-honeypot" aria-hidden="true"><label>Company<input name="company" type="text" tabindex="-1" autocomplete="off"></label></div>
			<label class="screen-reader-text" for="seef-newsletter-email"><?php esc_html_e( 'Adresse e-mail', 'seef-store-core' ); ?></label><input id="seef-newsletter-email" type="email" name="email" required maxlength="160" placeholder="vous@exemple.com"><button type="submit"><?php esc_html_e( 'S’inscrire', 'seef-store-core' ); ?> →</button>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	public function submit(): void {
		$request   = wp_unslash( $_POST );
		$nonce_raw = $request['seef_newsletter_nonce'] ?? '';
		$email_raw = $request['email'] ?? '';
		$nonce     = is_scalar( $nonce_raw ) ? sanitize_text_field( (string) $nonce_raw ) : '';
		$email     = is_scalar( $email_raw ) ? strtolower( sanitize_email( (string) $email_raw ) ) : '';
		$status    = 'invalid';

		if ( ! wp_verify_nonce( $nonce, 'seef_newsletter_submit' ) ) {
			$status = 'security';
		} elseif ( ! empty( $request['company'] ) ) {
			$status = 'success';
		} elseif ( $this->is_rate_limited() ) {
			$status = 'rate_limited';
		} elseif ( is_email( $email ) && mb_strlen( $email ) <= 160 ) {
			$existing = get_posts( array( 'post_type' => self::POST_TYPE, 'post_status' => 'private', 'title' => $email, 'numberposts' => 1, 'fields' => 'ids' ) );
			if ( ! $existing ) {
				wp_insert_post( array( 'post_type' => self::POST_TYPE, 'post_status' => 'private', 'post_title' => $email ) );
			}
			$this->increment_rate_limit();
			$status = 'success';
		}
		$target = wp_get_referer() ?: home_url( '/' );
		wp_safe_redirect( add_query_arg( 'newsletter_status', $status, remove_query_arg( 'newsletter_status', $target ) ) );
		exit;
	}

	private function rate_key(): string {
		$ip = preg_replace( '/[^0-9a-fA-F:.]/', '', (string) ( $_SERVER['REMOTE_ADDR'] ?? 'local' ) );
		return 'seef_newsletter_rate_' . substr( hash_hmac( 'sha256', $ip ?: 'local', wp_salt( 'nonce' ) ), 0, 32 );
	}

	private function is_rate_limited(): bool {
		return (int) get_transient( $this->rate_key() ) >= self::RATE_LIMIT;
	}

	private function increment_rate_limit(): void {
		set_transient( $this->rate_key(), (int) get_transient( $this->rate_key() ) + 1, 10 * MINUTE_IN_SECONDS );
	}
}
