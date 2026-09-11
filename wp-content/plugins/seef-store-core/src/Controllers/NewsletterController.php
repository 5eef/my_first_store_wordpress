<?php

declare(strict_types=1);

namespace SeefStore\Controllers;

use SeefStore\Support\Service;

final class NewsletterController implements Service {
	private const POST_TYPE = 'seef_subscriber';

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
		$status = sanitize_key( (string) ( $_GET['newsletter_status'] ?? '' ) );
		ob_start();
		if ( 'success' === $status ) {
			echo '<p class="seef-newsletter-status" role="status">' . esc_html__( 'Inscription enregistrée — démonstration locale.', 'seef-store-core' ) . '</p>';
		} elseif ( 'invalid' === $status ) {
			echo '<p class="seef-newsletter-status" role="alert">' . esc_html__( 'Saisissez une adresse e-mail valide.', 'seef-store-core' ) . '</p>';
		}
		?>
		<form class="seef-newsletter-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="seef_newsletter_submit"><?php wp_nonce_field( 'seef_newsletter_submit', 'seef_newsletter_nonce' ); ?>
			<label class="screen-reader-text" for="seef-newsletter-email"><?php esc_html_e( 'Adresse e-mail', 'seef-store-core' ); ?></label><input id="seef-newsletter-email" type="email" name="email" required maxlength="160" placeholder="vous@exemple.com"><button type="submit"><?php esc_html_e( 'S’inscrire', 'seef-store-core' ); ?> →</button>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	public function submit(): void {
		$nonce = sanitize_text_field( (string) ( $_POST['seef_newsletter_nonce'] ?? '' ) );
		$email = sanitize_email( (string) ( $_POST['email'] ?? '' ) );
		$status = 'invalid';
		if ( wp_verify_nonce( $nonce, 'seef_newsletter_submit' ) && is_email( $email ) && mb_strlen( $email ) <= 160 ) {
		$existing = get_posts( array( 'post_type' => self::POST_TYPE, 'post_status' => 'private', 'title' => $email, 'numberposts' => 1, 'fields' => 'ids' ) );
			if ( ! $existing ) {
				wp_insert_post( array( 'post_type' => self::POST_TYPE, 'post_status' => 'private', 'post_title' => $email ) );
			}
			$status = 'success';
		}
		$target = wp_get_referer() ?: home_url( '/' );
		wp_safe_redirect( add_query_arg( 'newsletter_status', $status, remove_query_arg( 'newsletter_status', $target ) ) );
		exit;
	}
}
