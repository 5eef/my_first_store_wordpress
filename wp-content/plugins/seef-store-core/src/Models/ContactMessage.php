<?php

declare(strict_types=1);

namespace SeefStore\Models;

final class ContactMessage {
	public const POST_TYPE = 'seef_contact';

	/** @param array{name:string,email:string,subject:string,message:string} $data */
	public static function create( array $data ): int|\WP_Error {
		$post_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'private',
				'post_title'   => $data['subject'],
				'post_content' => $data['message'],
				'meta_input'   => array(
					'_seef_name'   => $data['name'],
					'_seef_email'  => $data['email'],
					'_seef_status' => 'new',
				),
			),
			true
		);

		return $post_id;
	}

	public static function status( int $post_id ): string {
		$status = (string) get_post_meta( $post_id, '_seef_status', true );
		return in_array( $status, array( 'new', 'read', 'replied', 'archived' ), true ) ? $status : 'new';
	}
}
