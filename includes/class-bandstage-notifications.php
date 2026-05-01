<?php
/**
 * Module Notifications — emails concerts et newsletter.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_Notifications
 *
 * Envoie les notifications de concerts (48h avant),
 * les newsletters d'actualités et gère l'intégration Mailchimp.
 *
 * Les tâches CRON sont planifiées par BandStage_Activator.
 * Ce module s'enregistre sur les hooks CRON via bandstage.php
 * dès qu'il est instancié.
 */
class BandStage_Notifications {

	/**
	 * Constructeur — enregistre les hooks CRON.
	 */
	public function __construct() {
		add_action( 'bandstage_send_concert_notifications', array( $this, 'send_concert_notifications' ) );
		add_action( 'bandstage_cleanup_tchache_spam',       array( $this, 'cleanup_tchache_spam' ) );
	}

	// -----------------------------------------------------------------------
	// Concerts
	// -----------------------------------------------------------------------

	/**
	 * Envoi quotidien des rappels de concerts.
	 * Hook CRON : bandstage_send_concert_notifications
	 */
	public function send_concert_notifications(): void {
		if ( ! (bool) get_option( 'bs_notif_concerts_enabled', true ) ) {
			return;
		}

		$days_before = absint( get_option( 'bs_notif_concerts_days', 2 ) );
		$target_date = gmdate( 'Y-m-d', strtotime( "+{$days_before} days" ) );

		// Récupère les articles WP de type "concert" dont la date meta correspond.
		$concerts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 20,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'     => 'bs_concert_date',
						'value'   => $target_date,
						'compare' => '=',
					),
				),
			)
		);

		if ( empty( $concerts ) ) {
			return;
		}

		// Récupère les abonnés (membres avec notif_concerts = true).
		$subscribers = $this->get_concert_subscribers();

		if ( empty( $subscribers ) ) {
			return;
		}

		foreach ( $concerts as $concert ) {
			$venue   = esc_html( get_post_meta( $concert->ID, 'bs_concert_venue', true ) );
			$subject = sprintf(
				/* translators: 1: concert title 2: number of days */
				__( 'Dans %1$d jours : %2$s', 'bandstage' ),
				$days_before,
				get_the_title( $concert )
			);
			$body = $this->build_concert_email( $concert, $venue, $days_before );

			foreach ( $subscribers as $email ) {
				wp_mail(
					$email,
					$subject,
					$body,
					$this->get_email_headers()
				);
			}
		}
	}

	// -----------------------------------------------------------------------
	// Nettoyage
	// -----------------------------------------------------------------------

	/**
	 * Supprime les messages Tchache marqués comme spam depuis plus de 30 jours.
	 * Hook CRON : bandstage_cleanup_tchache_spam
	 */
	public function cleanup_tchache_spam(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'bandstage_messages';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM `{$table}` WHERE status IN ('spam','deleted') AND created_at < %s",
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);
	}

	// -----------------------------------------------------------------------
	// Helpers privés
	// -----------------------------------------------------------------------

	/**
	 * Retourne les emails des membres abonnés aux notifications concerts.
	 *
	 * @return string[]
	 */
	private function get_concert_subscribers(): array {
		$users = get_users(
			array(
				'meta_key'   => 'bs_notif_concerts', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value' => '1',                 // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'     => array( 'user_email' ),
			)
		);

		return array_column( $users, 'user_email' );
	}

	/**
	 * Construit le corps de l'email de rappel de concert.
	 *
	 * @param WP_Post $concert     Article concert.
	 * @param string  $venue       Lieu du concert.
	 * @param int     $days_before Jours avant le concert.
	 * @return string
	 */
	private function build_concert_email( WP_Post $concert, string $venue, int $days_before ): string {
		$band_name = esc_html( (string) get_option( 'bs_band_name', get_bloginfo( 'name' ) ) );
		$title     = get_the_title( $concert );
		$url       = get_permalink( $concert );

		$body  = sprintf( "Bonjour,\n\n" );
		$body .= sprintf(
			/* translators: 1: band name 2: number of days 3: concert title */
			__( "%1\$s se produit dans %2\$d jours : %3\$s\n", 'bandstage' ),
			$band_name,
			$days_before,
			esc_html( $title )
		);

		if ( $venue ) {
			$body .= sprintf(
				/* translators: %s: venue name */
				__( "Lieu : %s\n", 'bandstage' ),
				$venue
			);
		}

		$body .= "\n" . esc_url( $url ) . "\n\n";
		$body .= sprintf(
			/* translators: %s: site name */
			__( "— L'équipe %s", 'bandstage' ),
			$band_name
		);

		return $body;
	}

	/**
	 * Retourne les en-têtes MIME pour les emails.
	 *
	 * @return string[]
	 */
	private function get_email_headers(): array {
		$from_name  = sanitize_text_field( (string) get_option( 'bs_notif_from_name', get_bloginfo( 'name' ) ) );
		$from_email = sanitize_email( (string) get_option( 'bs_notif_from_email', get_option( 'admin_email' ) ) );

		return array(
			'Content-Type: text/plain; charset=UTF-8',
			"From: {$from_name} <{$from_email}>",
		);
	}
}
