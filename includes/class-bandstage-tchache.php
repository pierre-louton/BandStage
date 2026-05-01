<?php
/**
 * Module Tchache — mini-forum avec modération.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_Tchache
 *
 * Gère les messages du mini-forum : lecture, écriture,
 * modération (manuelle ou automatique) et anti-spam.
 */
class BandStage_Tchache {

	// -----------------------------------------------------------------------
	// AJAX — Public
	// -----------------------------------------------------------------------

	/**
	 * AJAX : poste un nouveau message (connecté ou non selon réglages).
	 * Hook : wp_ajax_bs_post_message / wp_ajax_nopriv_bs_post_message
	 */
	public function ajax_post_message(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		$members_only = (bool) get_option( 'bs_tchache_members_only', false );

		if ( $members_only && ! is_user_logged_in() ) {
			wp_send_json_error(
				array( 'message' => __( 'Vous devez être connecté pour participer au Tchache.', 'bandstage' ) ),
				401
			);
		}

		$content = sanitize_textarea_field( wp_unslash( $_POST['content'] ?? '' ) );
		$max_len = absint( get_option( 'bs_tchache_max_length', 500 ) );

		if ( empty( $content ) ) {
			wp_send_json_error( array( 'message' => __( 'Le message ne peut pas être vide.', 'bandstage' ) ) );
		}

		if ( mb_strlen( $content ) > $max_len ) {
			wp_send_json_error(
				array(
					/* translators: %d: maximum number of characters */
					'message' => sprintf( __( 'Message trop long (maximum %d caractères).', 'bandstage' ), $max_len ),
				)
			);
		}

		// Anti-spam : délai minimum entre deux messages.
		$min_delay = absint( get_option( 'bs_tchache_min_delay', 30 ) );
		$last_post = isset( $_SESSION['bs_last_post'] ) ? (int) $_SESSION['bs_last_post'] : 0;

		if ( $last_post && ( time() - $last_post ) < $min_delay ) {
			wp_send_json_error(
				array(
					/* translators: %d: number of seconds to wait */
					'message' => sprintf( __( 'Merci de patienter %d secondes avant de poster à nouveau.', 'bandstage' ), $min_delay ),
				)
			);
		}

		$user    = wp_get_current_user();
		$user_id = $user->ID ?? 0;

		$moderation = get_option( 'bs_tchache_moderation', 'manual' );
		$status     = ( 'auto' === $moderation ) ? 'approved' : 'pending';

		$inserted = $this->insert_message(
			array(
				'user_id'      => $user_id,
				'author_name'  => $user_id ? $user->display_name : sanitize_text_field( wp_unslash( $_POST['author_name'] ?? __( 'Anonyme', 'bandstage' ) ) ),
				'author_email' => $user_id ? $user->user_email : sanitize_email( wp_unslash( $_POST['author_email'] ?? '' ) ),
				'content'      => $content,
				'status'       => $status,
				'ip_address'   => $this->get_client_ip(),
			)
		);

		if ( ! $inserted ) {
			wp_send_json_error( array( 'message' => __( 'Erreur lors de l\'enregistrement du message.', 'bandstage' ) ) );
		}

		// Mise à jour session anti-spam.
		if ( ! session_id() ) {
			session_start();
		}
		$_SESSION['bs_last_post'] = time();

		// Notification email au modérateur si modération manuelle.
		if ( 'manual' === $moderation && (bool) get_option( 'bs_tchache_notify_new', true ) ) {
			$this->notify_moderator( $content );
		}

		$message = 'approved' === $status
			? __( 'Message publié.', 'bandstage' )
			: __( 'Message envoyé, en attente de modération.', 'bandstage' );

		wp_send_json_success( array( 'message' => $message, 'status' => $status ) );
	}

	/**
	 * AJAX : charge les messages approuvés (pagination).
	 * Hook : wp_ajax_bs_load_messages / wp_ajax_nopriv_bs_load_messages
	 */
	public function ajax_load_messages(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		$page     = max( 1, absint( $_POST['page'] ?? 1 ) );
		$per_page = 20;
		$offset   = ( $page - 1 ) * $per_page;

		global $wpdb;
		$table = $wpdb->prefix . 'bandstage_messages';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$messages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, user_id, author_name, content, created_at
				 FROM   `{$table}`
				 WHERE  status = 'approved'
				 ORDER  BY created_at DESC
				 LIMIT  %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM `{$table}` WHERE status = 'approved'"
		);

		// Sanitise les données en sortie.
		$safe = array_map(
			static function ( array $row ): array {
				return array(
					'id'          => (int) $row['id'],
					'author_name' => esc_html( $row['author_name'] ),
					'content'     => esc_html( $row['content'] ),
					'created_at'  => esc_html( $row['created_at'] ),
					'avatar'      => get_avatar_url( (int) $row['user_id'] ?: $row['author_name'], array( 'size' => 40 ) ),
				);
			},
			$messages ?: array()
		);

		wp_send_json_success(
			array(
				'messages'  => $safe,
				'total'     => $total,
				'has_more'  => ( $offset + $per_page ) < $total,
			)
		);
	}

	// -----------------------------------------------------------------------
	// AJAX — Admin
	// -----------------------------------------------------------------------

	/**
	 * AJAX admin : approuver, rejeter ou marquer comme spam un message.
	 * Hook : wp_ajax_bs_moderate_message
	 */
	public function ajax_moderate(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissions insuffisantes.', 'bandstage' ) ), 403 );
		}

		$id     = absint( $_POST['message_id'] ?? 0 );
		$action = sanitize_key( $_POST['moderate_action'] ?? '' );

		$allowed = array( 'approved', 'deleted', 'spam' );
		if ( ! $id || ! in_array( $action, $allowed, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Paramètres invalides.', 'bandstage' ) ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'bandstage_messages';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$updated = $wpdb->update(
			$table,
			array( 'status' => $action ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			wp_send_json_error( array( 'message' => __( 'Erreur lors de la mise à jour.', 'bandstage' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Message mis à jour.', 'bandstage' ) ) );
	}

	// -----------------------------------------------------------------------
	// Helpers privés
	// -----------------------------------------------------------------------

	/**
	 * Insère un message dans la table.
	 *
	 * @param array $data Données du message.
	 * @return bool True si l'insertion a réussi.
	 */
	private function insert_message( array $data ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'bandstage_messages';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->insert(
			$table,
			array(
				'user_id'      => absint( $data['user_id'] ?? 0 ),
				'author_name'  => sanitize_text_field( $data['author_name'] ?? '' ),
				'author_email' => sanitize_email( $data['author_email'] ?? '' ),
				'content'      => sanitize_textarea_field( $data['content'] ?? '' ),
				'status'       => sanitize_key( $data['status'] ?? 'pending' ),
				'ip_address'   => sanitize_text_field( $data['ip_address'] ?? '' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Envoie une notification email au modérateur.
	 *
	 * @param string $content Contenu du message.
	 */
	private function notify_moderator( string $content ): void {
		$to      = sanitize_email( (string) get_option( 'bs_tchache_notify_email', get_option( 'admin_email' ) ) );
		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] Nouveau message Tchache en attente de modération', 'bandstage' ),
			get_bloginfo( 'name' )
		);
		$body = sprintf(
			/* translators: 1: message content 2: admin URL */
			__( "Un nouveau message attend votre approbation :\n\n%1\$s\n\nModérer : %2\$s", 'bandstage' ),
			esc_html( $content ),
			admin_url( 'admin.php?page=bandstage-tchache' )
		);

		wp_mail( $to, $subject, $body );
	}

	/**
	 * Récupère l'IP cliente de manière sécurisée.
	 *
	 * @return string
	 */
	private function get_client_ip(): string {
		$keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		foreach ( $keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				// Prend la première IP si liste.
				$ip = explode( ',', $ip )[0];
				if ( filter_var( trim( $ip ), FILTER_VALIDATE_IP ) ) {
					return trim( $ip );
				}
			}
		}
		return '';
	}
}
