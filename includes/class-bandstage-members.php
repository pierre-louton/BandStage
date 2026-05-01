<?php
/**
 * Module Membres — inscription, profil, préférences.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_Members
 *
 * Gère l'inscription des membres, la mise à jour du profil
 * et les préférences de notifications.
 */
class BandStage_Members {

	/**
	 * AJAX : inscription d'un nouvel utilisateur (non connecté).
	 * Hook : wp_ajax_nopriv_bs_register
	 */
	public function ajax_register(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! (bool) get_option( 'bs_members_enabled', true ) ) {
			wp_send_json_error( array( 'message' => __( 'Les inscriptions sont désactivées.', 'bandstage' ) ) );
		}

		$username = sanitize_user( wp_unslash( $_POST['username'] ?? '' ) );
		$email    = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$password = wp_unslash( $_POST['password'] ?? '' );

		if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
			wp_send_json_error( array( 'message' => __( 'Tous les champs sont requis.', 'bandstage' ) ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Adresse e-mail invalide.', 'bandstage' ) ) );
		}

		if ( username_exists( $username ) ) {
			wp_send_json_error( array( 'message' => __( 'Ce pseudo est déjà utilisé.', 'bandstage' ) ) );
		}

		if ( email_exists( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Cette adresse e-mail est déjà utilisée.', 'bandstage' ) ) );
		}

		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
		}

		// Rôle : abonné (subscriber) — accès minimal.
		$user = new WP_User( $user_id );
		$user->set_role( 'subscriber' );

		// Métadonnées BandStage.
		update_user_meta( $user_id, 'bs_notif_concerts', true );
		update_user_meta( $user_id, 'bs_notif_news', false );
		update_user_meta( $user_id, 'bs_notif_tchache', false );
		update_user_meta( $user_id, 'bs_instrument', '' );
		update_user_meta( $user_id, 'bs_location', '' );

		// Si approbation requise, ne pas connecter automatiquement.
		$require_approval = (bool) get_option( 'bs_members_require_approval', false );

		if ( $require_approval ) {
			wp_send_json_success(
				array( 'message' => __( 'Compte créé. Vous recevrez un e-mail dès que votre compte sera approuvé.', 'bandstage' ) )
			);
		}

		// Connexion automatique.
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		wp_send_json_success(
			array(
				'message'  => __( 'Compte créé et connexion effectuée.', 'bandstage' ),
				'redirect' => home_url(),
			)
		);
	}

	/**
	 * AJAX : mise à jour du profil (connecté uniquement).
	 * Hook : wp_ajax_bs_update_profile
	 */
	public function ajax_update_profile(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Connexion requise.', 'bandstage' ) ), 401 );
		}

		$user_id    = get_current_user_id();
		$instrument = sanitize_text_field( wp_unslash( $_POST['instrument'] ?? '' ) );
		$location   = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
		$bio        = sanitize_textarea_field( wp_unslash( $_POST['bio'] ?? '' ) );

		update_user_meta( $user_id, 'bs_instrument', $instrument );
		update_user_meta( $user_id, 'bs_location', $location );
		update_user_meta( $user_id, 'bs_bio', $bio );

		// Mise à jour display_name si fourni.
		$display_name = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
		if ( $display_name ) {
			wp_update_user(
				array(
					'ID'           => $user_id,
					'display_name' => $display_name,
				)
			);
		}

		wp_send_json_success( array( 'message' => __( 'Profil mis à jour.', 'bandstage' ) ) );
	}

	/**
	 * AJAX : sauvegarde des préférences de notifications.
	 * Hook : wp_ajax_bs_save_preferences
	 */
	public function ajax_save_preferences(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Connexion requise.', 'bandstage' ) ), 401 );
		}

		$user_id = get_current_user_id();

		update_user_meta( $user_id, 'bs_notif_concerts', ! empty( $_POST['notif_concerts'] ) );
		update_user_meta( $user_id, 'bs_notif_news',     ! empty( $_POST['notif_news'] ) );
		update_user_meta( $user_id, 'bs_notif_tchache',  ! empty( $_POST['notif_tchache'] ) );

		// Préférences d'apparence (stockées par utilisateur).
		$bg_swatch = sanitize_hex_color( wp_unslash( $_POST['bg_swatch'] ?? '' ) );
		if ( $bg_swatch ) {
			update_user_meta( $user_id, 'bs_pref_bg_swatch', $bg_swatch );
		}

		wp_send_json_success( array( 'message' => __( 'Préférences enregistrées.', 'bandstage' ) ) );
	}

	/**
	 * AJAX : connexion (non connecté).
	 * Hook : wp_ajax_nopriv_bs_login
	 */
	public function ajax_login(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		$username = sanitize_user( wp_unslash( $_POST['username'] ?? '' ) );
		$password = wp_unslash( $_POST['password'] ?? '' );

		if ( empty( $username ) || empty( $password ) ) {
			wp_send_json_error( array( 'message' => __( 'Identifiants requis.', 'bandstage' ) ) );
		}

		$user = wp_authenticate( $username, $password );

		if ( is_wp_error( $user ) ) {
			wp_send_json_error( array( 'message' => __( 'Identifiants incorrects.', 'bandstage' ) ) );
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
		wp_send_json_success( array( 'message' => __( 'Connexion réussie.', 'bandstage' ) ) );
	}

	/**
	 * AJAX : envoyer une proposition de reprise par email.
	 * Hook : wp_ajax_bs_send_reprise / wp_ajax_nopriv_bs_send_reprise
	 */
	public function ajax_send_reprise(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! (bool) get_option( 'bs_reprise_enabled', true ) ) {
			wp_send_json_error( array( 'message' => __( 'Formulaire désactivé.', 'bandstage' ) ) );
		}

		$content = sanitize_textarea_field( wp_unslash( $_POST['content'] ?? '' ) );

		if ( strlen( $content ) < 5 ) {
			wp_send_json_error( array( 'message' => __( 'Message trop court.', 'bandstage' ) ) );
		}

		$to      = sanitize_email( (string) get_option( 'bs_reprise_recipient', get_option( 'admin_email' ) ) );
		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] Nouvelle proposition de reprise', 'bandstage' ),
			get_bloginfo( 'name' )
		);
		$user = wp_get_current_user();
		$from = $user->ID ? $user->display_name . ' <' . $user->user_email . '>' : __( 'Visiteur anonyme', 'bandstage' );
		$body = sprintf( "De : %s\n\n%s", $from, $content );

		wp_mail( $to, $subject, $body );

		$confirm = sanitize_textarea_field(
			(string) get_option( 'bs_reprise_confirm', __( 'Merci pour votre suggestion !', 'bandstage' ) )
		);
		wp_send_json_success( array( 'message' => $confirm ) );
	}

	/**
	 * AJAX admin : approuver un membre en attente.
	 * Hook : wp_ajax_bs_approve_member
	 */
	public function ajax_approve_member(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissions insuffisantes.', 'bandstage' ) ), 403 );
		}

		$member_id = absint( $_POST['member_id'] ?? 0 );
		if ( ! $member_id || ! get_user_by( 'id', $member_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Membre introuvable.', 'bandstage' ) ) );
		}

		delete_user_meta( $member_id, 'bs_pending_approval' );

		// Email de confirmation au membre.
		$user    = get_user_by( 'id', $member_id );
		$subject = sprintf( __( '[%s] Votre compte est approuvé', 'bandstage' ), get_bloginfo( 'name' ) );
		$body    = sprintf(
			__( "Bonjour %s,\n\nVotre compte a été approuvé. Vous pouvez maintenant participer au Tchache.\n\n%s", 'bandstage' ),
			$user->display_name,
			home_url()
		);
		wp_mail( $user->user_email, $subject, $body );

		wp_send_json_success( array( 'message' => __( 'Membre approuvé.', 'bandstage' ) ) );
	}

	/**
	 * AJAX admin : supprimer un compte membre.
	 * Hook : wp_ajax_bs_delete_member
	 */
	public function ajax_delete_member(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissions insuffisantes.', 'bandstage' ) ), 403 );
		}

		$member_id = absint( $_POST['member_id'] ?? 0 );

		// Interdit de supprimer un admin.
		if ( ! $member_id || user_can( $member_id, 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Impossible de supprimer ce compte.', 'bandstage' ) ) );
		}

		require_once ABSPATH . 'wp-admin/includes/user.php';
		$deleted = wp_delete_user( $member_id );

		if ( $deleted ) {
			wp_send_json_success( array( 'message' => __( 'Membre supprimé.', 'bandstage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Erreur lors de la suppression.', 'bandstage' ) ) );
		}
	}
}
