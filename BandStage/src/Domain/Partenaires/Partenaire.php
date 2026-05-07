<?php
/**
 * Entité Partenaire (CPT bs_partenaire).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Partenaires;

defined( 'ABSPATH' ) || exit;

class Partenaire {

	public function __construct(
		public readonly int    $id,
		public readonly string $name,
		public readonly string $description,
		public readonly string $status,
		public readonly string $type_slug,
		public readonly string $type_label,
		public readonly string $type_icon,
		public readonly string $thumbnail,
		public readonly string $website,
		public readonly string $phone,
		public readonly string $address,
		public readonly string $email,
	) {}

	public static function from_wp_post( \WP_Post $post ): self {
		$terms = get_the_terms( $post->ID, 'bs_type_partenaire' );
		$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;

		return new self(
			id:          $post->ID,
			name:        $post->post_title,
			description: $post->post_content,
			status:      $post->post_status,
			type_slug:   $term ? $term->slug  : '',
			type_label:  $term ? $term->name  : '',
			type_icon:   $term ? (string) get_term_meta( $term->term_id, 'bs_term_icon', true ) : '',
			thumbnail:   (string) get_the_post_thumbnail_url( $post->ID, 'medium' ),
			website:     (string) get_post_meta( $post->ID, 'bs_website', true ),
			phone:       (string) get_post_meta( $post->ID, 'bs_phone',   true ),
			address:     (string) get_post_meta( $post->ID, 'bs_address', true ),
			email:       (string) get_post_meta( $post->ID, 'bs_email',   true ),
		);
	}
}
