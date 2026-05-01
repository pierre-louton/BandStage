<?php
/**
 * Activation et désactivation du plugin.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_Activator
 *
 * Crée la table de messages, initialise les options par défaut
 * et planifie les tâches CRON lors de l'activation.
 */
class BandStage_Activator {

	/**
	 * Exécuté à l'activation du plugin.
	 */
	public static function activate(): void {
		self::create_tables();
		self::set_default_options();
		// Crée les types de partenaires par défaut.
		BandStage_Post_Types::create_default_terms();
		self::schedule_cron();
		// Les CPTs nécessitent un flush des règles de réécriture.
		flush_rewrite_rules();
	}

	/**
	 * Exécuté à la désactivation (suppression des CRON, pas des données).
	 */
	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'bandstage_send_concert_notifications' );
		wp_clear_scheduled_hook( 'bandstage_cleanup_tchache_spam' );
		flush_rewrite_rules();
	}

	// -----------------------------------------------------------------------
	// Tables
	// -----------------------------------------------------------------------

	/**
	 * Crée la table wp_bandstage_messages via dbDelta.
	 */
	private static function create_tables(): void {
		global $wpdb;

		$table   = $wpdb->prefix . 'bandstage_messages';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id      BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			author_name  VARCHAR(100) NOT NULL DEFAULT '',
			author_email VARCHAR(200) NOT NULL DEFAULT '',
			content      TEXT NOT NULL,
			status       ENUM('pending','approved','spam','deleted') NOT NULL DEFAULT 'pending',
			ip_address   VARCHAR(100) NOT NULL DEFAULT '',
			created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id    (user_id),
			KEY status     (status),
			KEY created_at (created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Stocke la version DB pour les futures migrations.
		update_option( 'bs_db_version', BANDSTAGE_VERSION );
	}

	// -----------------------------------------------------------------------
	// Options par défaut
	// -----------------------------------------------------------------------

	/**
	 * Initialise toutes les options avec leurs valeurs par défaut.
	 * N'écrase pas les options existantes (add_option).
	 */
	private static function set_default_options(): void {
		$defaults = self::get_default_options();

		foreach ( $defaults as $key => $value ) {
			// add_option ne fait rien si l'option existe déjà.
			add_option( $key, $value );
		}
	}

	/**
	 * Retourne le tableau complet des options et de leurs valeurs par défaut.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_default_options(): array {
		return array(

			// --- Groupe ---
			'bs_band_name'    => 'Mon Groupe',
			'bs_band_tagline' => 'Rock · Blues · Soul',
			'bs_band_logo_id'       => 0,
			'bs_band_bio'           => '',
			'bs_band_founded'       => '',
			'bs_band_members_label' => 'Les musiciens',

			// --- Splashscreen ---
			'bs_splash_enabled'  => true,
			'bs_splash_image_id' => 0,
			'bs_splash_duration' => 4, // secondes avant fermeture auto (0 = manuel)

			// --- Références ---
			'bs_influences'         => '[]', // JSON array of {name, genre, comment, url}
			'bs_repertoire_label'   => 'Notre répertoire',
			'bs_influences_label'   => 'Nos influences',

			// --- Apparence ---
			'bs_bg_color_start'    => '#1535A8',
			'bs_bg_color_end'      => '#020828',
			'bs_bg_angle'          => 168,
			'bs_accent_color'      => '#D4A820',
			'bs_text_color'        => '#F0E6CE',
			'bs_brand_font'        => 'Playfair Display',
			'bs_box_font'          => 'Oswald',
			'bs_box_radius'        => 8,
			'bs_ticker_bg_color'   => '#D4A820',
			'bs_ticker_text_color' => '#0A1240',
			'bs_ticker_speed'      => 24,
			'bs_custom_css'        => '',

			// --- Boîtes (6) ---
			// Clé : bs_box_{n}_{champ}, n = 1..6.
			'bs_box_1_title'       => 'Le Groupe',
			'bs_box_1_link'        => '',
			'bs_box_1_image_id'    => 0,
			'bs_box_1_color_start' => '#0E0E2A',
			'bs_box_1_color_end'   => '#2A1880',
			'bs_box_1_icon'        => 'groupe',
			'bs_box_1_enabled'     => true,

			'bs_box_2_title'       => 'Concerts',
			'bs_box_2_link'        => '',
			'bs_box_2_image_id'    => 0,
			'bs_box_2_color_start' => '#200800',
			'bs_box_2_color_end'   => '#5E2000',
			'bs_box_2_icon'        => 'concerts',
			'bs_box_2_enabled'     => true,

			'bs_box_3_title'       => 'Références',
			'bs_box_3_link'        => '',
			'bs_box_3_image_id'    => 0,
			'bs_box_3_color_start' => '#1A0A28',
			'bs_box_3_color_end'   => '#4A1468',
			'bs_box_3_icon'        => 'references',
			'bs_box_3_enabled'     => true,

			'bs_box_4_title'       => 'Humeurs',
			'bs_box_4_link'        => '',
			'bs_box_4_image_id'    => 0,
			'bs_box_4_color_start' => '#160A28',
			'bs_box_4_color_end'   => '#3A1E58',
			'bs_box_4_icon'        => 'humeurs',
			'bs_box_4_enabled'     => true,

			'bs_box_5_title'       => 'Partenaires',
			'bs_box_5_link'        => '',
			'bs_box_5_image_id'    => 0,
			'bs_box_5_color_start' => '#051A10',
			'bs_box_5_color_end'   => '#1A4A30',
			'bs_box_5_icon'        => 'partenaires',
			'bs_box_5_enabled'     => true,

			'bs_box_6_title'       => 'Tchache',
			'bs_box_6_link'        => '',
			'bs_box_6_image_id'    => 0,
			'bs_box_6_color_start' => '#041818',
			'bs_box_6_color_end'   => '#0E4848',
			'bs_box_6_icon'        => 'tchache',
			'bs_box_6_enabled'     => true,

			// --- Ticker ---
			'bs_ticker_enabled'    => true,
			'bs_ticker_source'     => 'manual',   // 'manual' | 'posts' | 'events'.
			'bs_ticker_items'      => '',          // Entrées manuelles, une par ligne.
			'bs_ticker_categories' => array(),     // IDs de catégories WP si source='posts'.

			// --- Tchache ---
			'bs_tchache_enabled'        => true,
			'bs_tchache_moderation'     => 'manual', // 'auto' | 'manual'.
			'bs_tchache_members_only'   => false,
			'bs_tchache_max_per_day'    => 10,
			'bs_tchache_min_delay'      => 30,        // Secondes entre deux messages.
			'bs_tchache_max_length'     => 500,
			'bs_tchache_notify_email'   => get_option( 'admin_email', '' ),
			'bs_tchache_notify_new'     => true,

			// --- Membres ---
			'bs_members_enabled'          => true,
			'bs_members_require_approval' => false,
			'bs_members_avatar_type'      => 'gravatar', // 'gravatar' | 'upload' | 'initials'.
			'bs_members_show_bio'         => true,
			'bs_members_show_instrument'  => true,
			'bs_members_show_location'    => false,

			// --- Notifications ---
			'bs_notif_from_email'        => get_option( 'admin_email', '' ),
			'bs_notif_from_name'         => get_option( 'blogname', '' ),
			'bs_notif_concerts_enabled'  => true,
			'bs_notif_concerts_days'     => 2,
			'bs_notif_news_enabled'      => false,
			'bs_notif_mailchimp_api_key' => '',
			'bs_notif_mailchimp_list_id' => '',

			// --- Reprise ---
			'bs_reprise_enabled'   => true,
			'bs_reprise_recipient' => get_option( 'admin_email', '' ),
			'bs_reprise_confirm'   => 'Merci pour votre suggestion ! Le groupe vous lira avec plaisir.',

			// --- Avancé ---
			'bs_debug_mode'        => false,
		);
	}

	// -----------------------------------------------------------------------
	// CRON
	// -----------------------------------------------------------------------

	/**
	 * Planifie les tâches CRON si elles ne le sont pas déjà.
	 */
	private static function schedule_cron(): void {
		if ( ! wp_next_scheduled( 'bandstage_send_concert_notifications' ) ) {
			// Exécution quotidienne à 9h du matin (heure serveur).
			wp_schedule_event( strtotime( 'today 09:00:00' ), 'daily', 'bandstage_send_concert_notifications' );
		}

		if ( ! wp_next_scheduled( 'bandstage_cleanup_tchache_spam' ) ) {
			wp_schedule_event( time(), 'weekly', 'bandstage_cleanup_tchache_spam' );
		}
	}
}

// Fonction globale pour les icônes SVG des boîtes (accessible depuis les templates).
