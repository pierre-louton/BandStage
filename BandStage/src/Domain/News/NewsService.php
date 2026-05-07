<?php
/**
 * Service News — actualités du groupe.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\News;

defined( 'ABSPATH' ) || exit;

class NewsService {

	public function register_ajax(): void {
		add_action( 'wp_ajax_bs_news_save',   [ $this, 'ajax_save' ] );
		add_action( 'wp_ajax_bs_news_delete', [ $this, 'ajax_delete' ] );
	}

	// -------------------------------------------------------------------------
	// Lecture
	// -------------------------------------------------------------------------

	/** @return News[] */
	public function get_recent( int $limit = 20, string $status = 'publish' ): array {
		$posts = get_posts( [
			'post_type'      => 'bs_news',
			'post_status'    => $status,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => $limit,
			'no_found_rows'  => true,
		] );
		return array_map( [ News::class, 'from_wp_post' ], $posts );
	}

	/** @return News[] */
	public function get_by_author( int $author_id, int $limit = 20 ): array {
		$posts = get_posts( [
			'post_type'      => 'bs_news',
			'post_status'    => [ 'publish', 'draft' ],
			'author'         => $author_id,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => $limit,
			'no_found_rows'  => true,
		] );
		return array_map( [ News::class, 'from_wp_post' ], $posts );
	}

	/** @return string[] Titres pour le ticker */
	public function get_ticker_titles( int $limit = 10 ): array {
		$posts = get_posts( [
			'post_type'      => 'bs_news',
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => $limit,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );
		return array_map( 'get_the_title', $posts );
	}

	public function get( int $id ): ?News {
		$post = get_post( $id );
		return ( $post && 'bs_news' === $post->post_type ) ? News::from_wp_post( $post ) : null;
	}

	// -------------------------------------------------------------------------
	// AJAX
	// -------------------------------------------------------------------------

	public function ajax_save(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
		}

		$post_id = absint( $_POST['post_id'] ?? 0 );
		$title   = sanitize_text_field(    wp_unslash( $_POST['title']   ?? '' ) );
		$content = wp_kses_post(            wp_unslash( $_POST['content'] ?? '' ) );
		$status  = sanitize_key(            $_POST['status'] ?? 'draft' );

		if ( ! in_array( $status, [ 'publish', 'draft' ], true ) ) {
			$status = 'draft';
		}

		if ( empty( $title ) ) {
			wp_send_json_error( [ 'message' => __( 'Le titre est obligatoire.', 'bandstage' ) ] );
		}

		$data = [
			'post_title'   => $title,
			'post_content' => $content,
			'post_type'    => 'bs_news',
			'post_status'  => $status,
			'post_author'  => get_current_user_id(),
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

		wp_send_json_success( [
			'message'  => __( 'Actualité enregistrée.', 'bandstage' ),
			'post_id'  => (int) $result,
			'redirect' => \BandStage\Frontend\Shortcodes::studio_url( 'dashboard' ),
		] );
	}

	public function ajax_delete(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
		}

		$post_id = absint( $_POST['post_id'] ?? 0 );
		$post    = $post_id ? get_post( $post_id ) : null;

		if ( ! $post || 'bs_news' !== $post->post_type ) {
			wp_send_json_error( [ 'message' => __( 'Actualité introuvable.', 'bandstage' ) ] );
		}

		// Seul l'auteur ou un admin peut supprimer.
		if ( (int) $post->post_author !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Vous ne pouvez pas supprimer cette actualité.', 'bandstage' ) ], 403 );
		}

		wp_delete_post( $post_id, true );
		wp_send_json_success( [ 'message' => __( 'Actualité supprimée.', 'bandstage' ) ] );
	}
}
