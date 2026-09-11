<?php

declare(strict_types=1);

namespace SeefStore\Frontend;

use SeefStore\Controllers\ContactController;
use SeefStore\Support\Service;

final class ContactForm implements Service {
	public function register(): void {
		add_shortcode( 'seef_contact_form', array( $this, 'render' ) );
	}

	public function render(): string {
		$status = sanitize_key( (string) ( $_GET['contact_status'] ?? '' ) );
		$notices = array(
			'success'      => array( 'success', __( 'Merci ! Votre message a bien été enregistré.', 'seef-store-core' ) ),
			'invalid'      => array( 'error', __( 'Certains champs doivent être corrigés.', 'seef-store-core' ) ),
			'security'     => array( 'error', __( 'La vérification de sécurité a échoué. Rechargez la page.', 'seef-store-core' ) ),
			'rate_limited' => array( 'error', __( 'Trop de messages ont été envoyés. Réessayez dans quelques minutes.', 'seef-store-core' ) ),
			'error'        => array( 'error', __( 'Le message n’a pas pu être enregistré.', 'seef-store-core' ) ),
		);

		ob_start();
		if ( isset( $notices[ $status ] ) ) {
			printf( '<div class="seef-notice seef-notice--%1$s" role="status">%2$s</div>', esc_attr( $notices[ $status ][0] ), esc_html( $notices[ $status ][1] ) );
		}
		foreach ( ContactController::feedback() as $error ) {
			printf( '<div class="seef-notice seef-notice--error" role="alert">%s</div>', esc_html( $error ) );
		}
		?>
		<form class="seef-contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
			<input type="hidden" name="action" value="seef_contact_submit">
			<?php wp_nonce_field( 'seef_contact_submit', 'seef_contact_nonce' ); ?>
			<div class="seef-honeypot" aria-hidden="true"><label>Company<input name="company" type="text" tabindex="-1" autocomplete="off"></label></div>
			<div class="seef-form-grid">
				<p><label for="seef-name"><?php esc_html_e( 'Nom complet', 'seef-store-core' ); ?></label><input id="seef-name" name="name" type="text" required minlength="2" maxlength="80" autocomplete="name"></p>
				<p><label for="seef-email"><?php esc_html_e( 'Adresse e-mail', 'seef-store-core' ); ?></label><input id="seef-email" name="email" type="email" required maxlength="160" autocomplete="email"></p>
			</div>
			<p><label for="seef-subject"><?php esc_html_e( 'Sujet', 'seef-store-core' ); ?></label><input id="seef-subject" name="subject" type="text" required minlength="3" maxlength="120"></p>
			<p><label for="seef-message"><?php esc_html_e( 'Message', 'seef-store-core' ); ?></label><textarea id="seef-message" name="message" rows="7" required minlength="10" maxlength="3000"></textarea></p>
			<button class="button seef-button" type="submit"><?php esc_html_e( 'Envoyer le message', 'seef-store-core' ); ?></button>
		</form>
		<?php
		return (string) ob_get_clean();
	}
}
