<?php
/**
 * Entité LineupMember — membre du groupe (CPT bs_band_member).
 * Zéro dépendance WordPress directe.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Lineup;

defined( 'ABSPATH' ) || exit;

class LineupMember {

	public function __construct(
		public readonly int    $id,
		public readonly string $name,
		public readonly string $role,
		public readonly string $styles,
		public readonly string $thumbnail_url,
		public readonly int    $order,
		public readonly string $social_link,
	) {}

	/**
	 * Construit l'entité depuis un WP_Post.
	 */
	public static function from_wp_post( \WP_Post $post ): self {
		return new self(
			id:            $post->ID,
			name:          $post->post_title,
			role:          (string) get_post_meta( $post->ID, 'bs_bm_role',        true ),
			styles:        (string) get_post_meta( $post->ID, 'bs_bm_styles',      true ),
			thumbnail_url: (string) get_the_post_thumbnail_url( $post->ID, 'medium' ),
			order:         (int)    $post->menu_order,
			social_link:   (string) get_post_meta( $post->ID, 'bs_bm_social_link', true ),
		);
	}
}
