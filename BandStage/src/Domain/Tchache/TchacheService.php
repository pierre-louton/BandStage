<?php
/**
 * Service Tchache — mini-forum.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Tchache;

defined( 'ABSPATH' ) || exit;

use BandStage\Core\Config;
use BandStage\Domain\Members\Member;

class TchacheService {

	public function register_ajax(): void {
		add_action( 'wp_ajax_bs_tchache_post',        [ $this, 'ajax_post' ] );
		add_action( 'wp_ajax_nopriv_bs_tchache_post', [ $this, 'ajax_post' ] );
		add_action( 'wp_ajax_bs_tchache_load',        [ $this, 'ajax_load' ] );
		add_action( 'wp_ajax_nopriv_bs_tchache_load', [ $this, 'ajax_load' ] );
		add_action( 'wp_ajax_bs_tchache_moderate',    [ $this, 'ajax_moderate' ] );
	}

	// -------------------------------------------------------------------------
	// Lecture
	// -------------------------------------------------------------------------

	/** @return Message[] */
	public function get_approved( int $limit = 50 ): array {
		return $this->query_messages( 'approved', $limit );
	}

	/** @return Message[] */
	public function get_pending(): array {
		return $this->query_messages( 'pending', 100 );
	}

	public function count_pending(): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			'SELECT COUNT(*) FROM %i WHERE status = %s',
			Config::table_messages(), 'pending'
		) );
	}

	// -------------------------------------------------------------------------
	// AJAX
	// -------------------------------------------------------------------------

	public function ajax_post(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => __( 'Vous devez être connecté pour poster.', 'bandstage' ) ], 403 );
		}

		if ( ! get_option( 'bs_tchache_enabled', '1' ) ) {
			wp_send_json_error( [ 'message' => __( 'La Tchache est désactivée.', 'bandstage' ) ] );
		}

		$max     = (int) get_option( 'bs_tchache_max_length', 500 );
		$content = sanitize_textarea_field( wp_unslash( $_POST['content'] ?? '' ) );

		if ( empty( $content ) ) {
			wp_send_json_error( [ 'message' => __( 'Message vide.', 'bandstage' ) ] );
		}
		if ( mb_strlen( $content ) > $max ) {
			wp_send_json_error( [ 'message' => sprintf( __( 'Message trop long (max %d caractères).', 'bandstage' ), $max ) ] );
		}

		$moderation = get_option( 'bs_tchache_moderation', 'manual' );
		$status     = ( 'auto' === $moderation || current_user_can( 'manage_options' ) ) ? 'approved' : 'pending';

		global $wpdb;
		$wpdb->insert( Config::table_messages(), [
			'user_id'    => get_current_user_id(),
			'content'    => $content,
			'status'     => $status,
			'created_at' => current_time( 'mysql' ),
		] );

		if ( 'approved' === $status ) {
			wp_send_json_success( [ 'message' => __( 'Message publié.', 'bandstage' ), 'status' => 'approved' ] );
		} else {
			wp_send_json_success( [ 'message' => __( 'Message en attente de modération.', 'bandstage' ), 'status' => 'pending' ] );
		}
	}

	public function ajax_load(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		$messages = $this->get_approved( 50 );
		$html     = '';
		foreach ( $messages as $msg ) {
			ob_start();
			include BANDSTAGE_PLUGIN_DIR . 'templates/public/partials/tchache-message.php';
			$html .= ob_get_clean();
		}
		wp_send_json_success( [ 'html' => $html ] );
	}

	public function ajax_moderate(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
		}

		$msg_id = absint( $_POST['msg_id'] ?? 0 );
		$action = sanitize_key( $_POST['moderate_action'] ?? '' );

		if ( ! $msg_id || ! in_array( $action, [ 'approve', 'spam', 'delete' ], true ) ) {
			wp_send_json_error( [ 'message' => __( 'Paramètres invalides.', 'bandstage' ) ] );
		}

		global $wpdb;
		$table = Config::table_messages();

		if ( 'delete' === $action ) {
			$wpdb->delete( $table, [ 'id' => $msg_id ], [ '%d' ] );
		} else {
			$status = ( 'approve' === $action ) ? 'approved' : 'spam';
			$wpdb->update( $table, [ 'status' => $status ], [ 'id' => $msg_id ] );
		}

		wp_send_json_success( [ 'message' => __( 'Action effectuée.', 'bandstage' ) ] );
	}

	// -------------------------------------------------------------------------
	// Helpers privés
	// -------------------------------------------------------------------------

	/** @return Message[] */
	private function query_messages( string $status, int $limit ): array {
		global $wpdb;

		$rows = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM %i WHERE status = %s ORDER BY created_at DESC LIMIT %d',
			Config::table_messages(), $status, $limit
		) );

		$messages = [];
		foreach ( $rows as $row ) {
			$user         = get_userdata( (int) $row->user_id );
			$display_name = $user ? $user->display_name : __( 'Inconnu', 'bandstage' );
			$avatar_url   = '';
			$initials     = '';

			if ( $user ) {
				$member     = \BandStage\Domain\Members\Member::from_wp_user( $user );
				$avatar_url = $member->avatar_url;
				$initials   = $member->initials();
			}

			$messages[] = new Message(
				id:           (int)    $row->id,
				user_id:      (int)    $row->user_id,
				content:      (string) $row->content,
				status:       (string) $row->status,
				created_at:   (string) $row->created_at,
				display_name: $display_name,
				avatar_url:   $avatar_url,
				initials:     $initials,
			);
		}

		return $messages;
	}
}
