<?php
/**
 * Service Partenaires.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Partenaires;

defined( 'ABSPATH' ) || exit;

class PartenaireService {

	public function register_ajax(): void {
		add_action( 'wp_ajax_bs_partenaire_save',   [ $this, 'ajax_save' ] );
		add_action( 'wp_ajax_bs_partenaire_delete', [ $this, 'ajax_delete' ] );
		add_action( 'wp_ajax_bs_type_partenaire_add', [ $this, 'ajax_add_type' ] );
	}

	/** @return Partenaire[] */
	public function get_all(): array {
		$posts = get_posts( [
			'post_type'      => 'bs_partenaire',
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		] );
		return array_map( [ Partenaire::class, 'from_wp_post' ], $posts );
	}

	/** @return array<string, array{type: object, items: Partenaire[]}> */
	public function get_grouped_by_type(): array {
		$partenaires = $this->get_all();
		$grouped     = [];

		foreach ( $partenaires as $p ) {
			if ( ! isset( $grouped[ $p->type_slug ] ) ) {
				$grouped[ $p->type_slug ] = [
					'label' => $p->type_label,
					'icon'  => $p->type_icon,
					'items' => [],
				];
			}
			$grouped[ $p->type_slug ]['items'][] = $p;
		}

		return $grouped;
	}

	public function get( int $id ): ?Partenaire {
		$post = get_post( $id );
		return ( $post && 'bs_partenaire' === $post->post_type ) ? Partenaire::from_wp_post( $post ) : null;
	}

	public function ajax_save(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
		}

		$post_id      = absint( $_POST['post_id']      ?? 0 );
		$name         = sanitize_text_field( wp_unslash( $_POST['name']         ?? '' ) );
		$description  = wp_kses_post(         wp_unslash( $_POST['description']  ?? '' ) );
		$type_slug    = sanitize_key(          $_POST['type_slug']    ?? '' );
		$website      = esc_url_raw(    wp_unslash( $_POST['website']      ?? '' ) );
		$phone        = sanitize_text_field( wp_unslash( $_POST['phone']        ?? '' ) );
		$address      = sanitize_text_field( wp_unslash( $_POST['address']      ?? '' ) );
		$email        = sanitize_email(       wp_unslash( $_POST['email']        ?? '' ) );
		$thumbnail_id = absint( $_POST['thumbnail_id'] ?? 0 );

		if ( empty( $name ) ) {
			wp_send_json_error( [ 'message' => __( 'Le nom est obligatoire.', 'bandstage' ) ] );
		}

		$data = [
			'post_title'   => $name,
			'post_content' => $description,
			'post_type'    => 'bs_partenaire',
			'post_status'  => 'publish',
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

		update_post_meta( $post_id, 'bs_website', $website );
		update_post_meta( $post_id, 'bs_phone',   $phone );
		update_post_meta( $post_id, 'bs_address', $address );
		update_post_meta( $post_id, 'bs_email',   $email );

		if ( $type_slug ) {
			wp_set_object_terms( $post_id, $type_slug, 'bs_type_partenaire' );
		}

		if ( $thumbnail_id > 0 ) {
			set_post_thumbnail( $post_id, $thumbnail_id );
		} elseif ( isset( $_POST['thumbnail_id'] ) && '0' === $_POST['thumbnail_id'] ) {
			delete_post_thumbnail( $post_id );
		}

		wp_send_json_success( [
			'message'  => __( 'Partenaire enregistré.', 'bandstage' ),
			'post_id'  => $post_id,
			'redirect' => \BandStage\Frontend\Shortcodes::partenaires_url( 'list' ),
		] );
	}

	public function ajax_delete(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
		}

		$post_id = absint( $_POST['post_id'] ?? 0 );
		$post    = $post_id ? get_post( $post_id ) : null;

		if ( ! $post || 'bs_partenaire' !== $post->post_type ) {
			wp_send_json_error( [ 'message' => __( 'Partenaire introuvable.', 'bandstage' ) ] );
		}

		$thumb_id = (int) get_post_thumbnail_id( $post_id );
		wp_delete_post( $post_id, true );

		if ( $thumb_id ) {
			$attached = get_posts( [
				'post_type'  => 'any', 'meta_key' => '_thumbnail_id',
				'meta_value' => $thumb_id, 'posts_per_page' => 1, 'fields' => 'ids',
			] );
			if ( empty( $attached ) ) {
				wp_delete_attachment( $thumb_id, true );
			}
		}

		wp_send_json_success( [ 'message' => __( 'Partenaire supprimé.', 'bandstage' ) ] );
	}

	public function ajax_add_type(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
		}

		$name = sanitize_text_field( wp_unslash( $_POST['type_name'] ?? '' ) );
		$icon = sanitize_text_field( wp_unslash( $_POST['type_icon'] ?? '' ) );
		$slug = sanitize_title( $name );

		if ( empty( $name ) ) {
			wp_send_json_error( [ 'message' => __( 'Nom du type obligatoire.', 'bandstage' ) ] );
		}

		if ( term_exists( $slug, 'bs_type_partenaire' ) ) {
			wp_send_json_error( [ 'message' => __( 'Ce type existe déjà.', 'bandstage' ) ] );
		}

		$result = wp_insert_term( $name, 'bs_type_partenaire', [ 'slug' => $slug ] );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		update_term_meta( $result['term_id'], 'bs_term_icon', $icon );

		wp_send_json_success( [
			'message' => __( 'Type ajouté.', 'bandstage' ),
			'slug'    => $slug,
			'name'    => $name,
			'icon'    => $icon,
		] );
	}
}
