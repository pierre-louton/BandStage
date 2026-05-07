<?php
/**
 * Entité Member — utilisateur WordPress du groupe.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Members;

defined( 'ABSPATH' ) || exit;

class Member {

	public function __construct(
		public readonly int    $id,
		public readonly string $display_name,
		public readonly string $email,
		public readonly string $avatar_url,
		public readonly string $bio,
		public readonly string $instrument,
		public readonly string $city,
	) {}

	public static function from_wp_user( \WP_User $user ): self {
		$avatar_url = get_avatar_url( $user->ID, [ 'size' => 96, 'default' => '404' ] );
		if ( ! $avatar_url || str_contains( $avatar_url, 'gravatar.com/avatar/00000' ) ) {
			$avatar_url = '';
		}

		return new self(
			id:           $user->ID,
			display_name: $user->display_name,
			email:        $user->user_email,
			avatar_url:   $avatar_url,
			bio:          (string) get_user_meta( $user->ID, 'bs_bio',        true ),
			instrument:   (string) get_user_meta( $user->ID, 'bs_instrument', true ),
			city:         (string) get_user_meta( $user->ID, 'bs_city',       true ),
		);
	}

	/**
	 * Initiales CSS (2 caractères max).
	 */
	public function initials(): string {
		$parts = explode( ' ', trim( $this->display_name ) );
		$init  = '';
		foreach ( $parts as $part ) {
			$init .= mb_strtoupper( mb_substr( $part, 0, 1 ) );
			if ( strlen( $init ) >= 2 ) {
				break;
			}
		}
		return $init ?: '?';
	}
}
