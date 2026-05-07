<?php
/**
 * Service Notifications.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Notifications;

defined( 'ABSPATH' ) || exit;

use BandStage\Core\Config;

class NotificationService {

	public function register_ajax(): void {
		add_action( 'wp_ajax_bs_notifications_mark_read', [ $this, 'ajax_mark_read' ] );
	}

	public function create( int $user_id, string $type, array $payload = [] ): void {
		global $wpdb;
		$wpdb->insert( Config::table_notifications(), [
			'user_id'    => $user_id,
			'type'       => $type,
			'payload'    => wp_json_encode( $payload ),
			'created_at' => current_time( 'mysql' ),
		] );
	}

	/** @return Notification[] */
	public function get_unread( int $user_id ): array {
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM %i WHERE user_id = %d AND read_at IS NULL ORDER BY created_at DESC LIMIT 30',
			Config::table_notifications(), $user_id
		) );

		return array_map( function ( $row ) {
			return new Notification(
				id:         (int)   $row->id,
				user_id:    (int)   $row->user_id,
				type:       $row->type,
				payload:    (array) json_decode( $row->payload ?? '{}', true ),
				read_at:    $row->read_at,
				created_at: $row->created_at,
			);
		}, $rows );
	}

	public function mark_read( int $user_id ): void {
		global $wpdb;
		$wpdb->update(
			Config::table_notifications(),
			[ 'read_at' => current_time( 'mysql' ) ],
			[ 'user_id' => $user_id, 'read_at' => null ]
		);
	}

	public function ajax_mark_read(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [], 403 );
		}
		$this->mark_read( get_current_user_id() );
		wp_send_json_success();
	}

	public function send_daily(): void {
		// Envoi quotidien — hook via wp-cron si nécessaire.
	}
}
