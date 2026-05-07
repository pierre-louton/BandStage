<?php
/**
 * Service Member — profils utilisateurs WP.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Members;

defined( 'ABSPATH' ) || exit;

class MemberService {

	public function register_ajax(): void {
		add_action( 'wp_ajax_bs_member_save_profile', [ $this, 'ajax_save_profile' ] );
	}

	/**
	 * @return Member[]
	 */
	public function get_band_members(): array {
		$users = get_users( [
			'role__in' => [ 'author', 'editor', 'administrator' ],
			'orderby'  => 'display_name',
			'order'    => 'ASC',
		] );
		return array_map( [ Member::class, 'from_wp_user' ], $users );
	}

	public function get( int $user_id ): ?Member {
		$user = get_userdata( $user_id );
		return $user ? Member::from_wp_user( $user ) : null;
	}

	public function ajax_save_profile(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => __( 'Vous devez être connecté.', 'bandstage' ) ], 403 );
		}

		$user_id    = get_current_user_id();
		$bio        = sanitize_textarea_field( wp_unslash( $_POST['bio']        ?? '' ) );
		$instrument = sanitize_text_field(     wp_unslash( $_POST['instrument'] ?? '' ) );
		$city       = sanitize_text_field(     wp_unslash( $_POST['city']       ?? '' ) );

		update_user_meta( $user_id, 'bs_bio',        $bio );
		update_user_meta( $user_id, 'bs_instrument', $instrument );
		update_user_meta( $user_id, 'bs_city',       $city );

		wp_send_json_success( [ 'message' => __( 'Profil mis à jour.', 'bandstage' ) ] );
	}
}
