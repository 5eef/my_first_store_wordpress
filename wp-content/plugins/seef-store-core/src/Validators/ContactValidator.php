<?php

declare(strict_types=1);

namespace SeefStore\Validators;

final class ContactValidator {
	/**
	 * @param array<string,mixed> $input
	 * @return array{data:array{name:string,email:string,subject:string,message:string},errors:list<string>}
	 */
	public function validate( array $input ): array {
		$data = array(
			'name'    => sanitize_text_field( (string) ( $input['name'] ?? '' ) ),
			'email'   => sanitize_email( (string) ( $input['email'] ?? '' ) ),
			'subject' => sanitize_text_field( (string) ( $input['subject'] ?? '' ) ),
			'message' => sanitize_textarea_field( (string) ( $input['message'] ?? '' ) ),
		);
		$errors = array();

		if ( mb_strlen( $data['name'] ) < 2 || mb_strlen( $data['name'] ) > 80 ) {
			$errors[] = __( 'Le nom doit contenir entre 2 et 80 caractères.', 'seef-store-core' );
		}
		if ( ! is_email( $data['email'] ) || mb_strlen( $data['email'] ) > 160 ) {
			$errors[] = __( 'Veuillez saisir une adresse e-mail valide.', 'seef-store-core' );
		}
		if ( mb_strlen( $data['subject'] ) < 3 || mb_strlen( $data['subject'] ) > 120 ) {
			$errors[] = __( 'Le sujet doit contenir entre 3 et 120 caractères.', 'seef-store-core' );
		}
		if ( mb_strlen( $data['message'] ) < 10 || mb_strlen( $data['message'] ) > 3000 ) {
			$errors[] = __( 'Le message doit contenir entre 10 et 3000 caractères.', 'seef-store-core' );
		}

		return array( 'data' => $data, 'errors' => $errors );
	}
}
