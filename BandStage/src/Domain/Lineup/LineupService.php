<?php
/**
 * Service Lineup — gestion des membres du groupe (CPT bs_band_member).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Lineup;

defined( 'ABSPATH' ) || exit;

class LineupService {

	// -------------------------------------------------------------------------
	// Enregistrement des actions AJAX
	// -------------------------------------------------------------------------

	public function register_ajax(): void {
		add_action( 'wp_ajax_bs_lineup_save',   [ $this, 'ajax_save' ] );
		add_action( 'wp_ajax_bs_lineup_delete', [ $this, 'ajax_delete' ] );
		add_action( 'wp_ajax_bs_lineup_reorder',[ $this, 'ajax_reorder' ] );
	}

	// -------------------------------------------------------------------------
	// Lecture
	// -------------------------------------------------------------------------

	/**
	 * @return LineupMember[]
	 */
	public function get_ordered(): array {
		$posts = get_posts( [
			'post_type'      => 'bs_band_member',
			'post_status'    => 'publish',
			'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		] );

		return array_map( [ LineupMember::class, 'from_wp_post' ], $posts );
	}

	/**
	 * @return LineupMember|null
	 */
	public function get( int $id ): ?LineupMember {
		$post = get_post( $id );
		if ( ! $post || 'bs_band_member' !== $post->post_type ) {
			return null;
		}
		return LineupMember::from_wp_post( $post );
	}

	// -------------------------------------------------------------------------
	// AJAX — Sauvegarder (créer ou modifier)
	// -------------------------------------------------------------------------

	public function ajax_save(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
		}

		$post_id      = absint( $_POST['post_id'] ?? 0 );
		$name         = sanitize_text_field( wp_unslash( $_POST['name']   ?? '' ) );
		$role         = sanitize_text_field( wp_unslash( $_POST['role']   ?? '' ) );
		$styles       = sanitize_text_field( wp_unslash( $_POST['styles'] ?? '' ) );
		$thumbnail_id = absint( $_POST['thumbnail_id'] ?? 0 );

		if ( empty( $name ) ) {
			wp_send_json_error( [ 'message' => __( 'Le nom est obligatoire.', 'bandstage' ) ] );
		}

		$data = [
			'post_title'  => $name,
			'post_type'   => 'bs_band_member',
			'post_status' => 'publish',
		];

		if ( $post_id ) {
			$data['ID'] = $post_id;
			$result     = wp_update_post( $data, true );
		} else {
			$result = wp_insert_post( $data, true );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		$post_id = (int) $result;

		// Metas
		update_post_meta( $post_id, 'bs_bm_role',   $role );
		update_post_meta( $post_id, 'bs_bm_styles', $styles );

		// Vignette
		if ( $thumbnail_id > 0 ) {
			set_post_thumbnail( $post_id, $thumbnail_id );
		} elseif ( isset( $_POST['thumbnail_id'] ) && '0' === $_POST['thumbnail_id'] ) {
			delete_post_thumbnail( $post_id );
		}

		$member = $this->get( $post_id );

		wp_send_json_success( [
			'message'  => __( 'Membre enregistré.', 'bandstage' ),
			'post_id'  => $post_id,
			'redirect' => \BandStage\Public\Shortcodes::groupe_url( 'list' ),
			'member'   => $member ? [
				'id'            => $member->id,
				'name'          => $member->name,
				'role'          => $member->role,
				'styles'        => $member->styles,
				'thumbnail_url' => $member->thumbnail_url,
			] : null,
		] );
	}

	// -------------------------------------------------------------------------
	// AJAX — Supprimer (manage_options)
	// -------------------------------------------------------------------------

	public function ajax_delete(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
		}

		$post_id = absint( $_POST['post_id'] ?? 0 );
		if ( ! $post_id ) {
			wp_send_json_error( [ 'message' => __( 'Identifiant manquant.', 'bandstage' ) ] );
		}

		$post = get_post( $post_id );
		if ( ! $post || 'bs_band_member' !== $post->post_type ) {
			wp_send_json_error( [ 'message' => __( 'Membre introuvable.', 'bandstage' ) ] );
		}

		// Supprimer la vignette orpheline si elle n'est utilisée nulle part ailleurs.
		$thumb_id = (int) get_post_thumbnail_id( $post_id );
		wp_delete_post( $post_id, true );

		if ( $thumb_id ) {
			$attached_to = get_posts( [
				'post_type'      => 'any',
				'meta_key'       => '_thumbnail_id',
				'meta_value'     => $thumb_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			] );
			if ( empty( $attached_to ) ) {
				wp_delete_attachment( $thumb_id, true );
			}
		}

		wp_send_json_success( [ 'message' => __( 'Membre supprimé.', 'bandstage' ) ] );
	}

	// -------------------------------------------------------------------------
	// AJAX — Réordonner (drag & drop)
	// -------------------------------------------------------------------------

	public function ajax_reorder(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
		}

		$order = array_map( 'absint', (array) ( $_POST['order'] ?? [] ) );
		foreach ( $order as $position => $post_id ) {
			wp_update_post( [ 'ID' => $post_id, 'menu_order' => $position ] );
		}

		wp_send_json_success( [ 'message' => __( 'Ordre enregistré.', 'bandstage' ) ] );
	}
}
