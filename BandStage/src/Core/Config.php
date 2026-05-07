<?php
/**
 * Configuration centrale — options par défaut et noms de tables.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Core;

defined( 'ABSPATH' ) || exit;

class Config {

	// -------------------------------------------------------------------------
	// Noms de tables (toujours passer par ces méthodes, jamais de constante)
	// -------------------------------------------------------------------------

	public static function table_messages(): string {
		global $wpdb;
		return $wpdb->prefix . 'bandstage_messages';
	}

	public static function table_notifications(): string {
		global $wpdb;
		return $wpdb->prefix . 'bandstage_notifications';
	}

	// -------------------------------------------------------------------------
	// Création des tables à l'activation
	// -------------------------------------------------------------------------

	public static function create_tables(): void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql_messages = "CREATE TABLE IF NOT EXISTS " . self::table_messages() . " (
			id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id     BIGINT(20)   UNSIGNED NOT NULL DEFAULT 0,
			content     TEXT         NOT NULL,
			status      ENUM('pending','approved','spam') NOT NULL DEFAULT 'pending',
			created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id  (user_id),
			KEY status   (status),
			KEY created_at (created_at)
		) $charset_collate;";

		$sql_notifications = "CREATE TABLE IF NOT EXISTS " . self::table_notifications() . " (
			id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id     BIGINT(20)   UNSIGNED NOT NULL,
			type        VARCHAR(64)  NOT NULL,
			payload     JSON,
			read_at     DATETIME     DEFAULT NULL,
			created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id    (user_id),
			KEY read_at    (read_at),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_messages );
		dbDelta( $sql_notifications );
	}

	// -------------------------------------------------------------------------
	// Options par défaut
	// NB : jamais de __() ici — textdomain pas encore chargé.
	// -------------------------------------------------------------------------

	public static function default_options(): array {
		return [
			// Groupe
			'bs_band_name'       => 'Mon Groupe',
			'bs_band_tagline'    => 'Rock · Blues · Soul',
			'bs_band_city'       => '',
			'bs_band_email'      => '',
			// Apparence
			'bs_bg_color_start'  => '#1535A8',
			'bs_bg_color_end'    => '#020828',
			'bs_accent_color'    => '#D4A820',
			'bs_cream_color'     => '#FAF6EB',
			// Ticker
			'bs_ticker_enabled'  => '1',
			'bs_ticker_source'   => 'bs_news',
			'bs_ticker_items'    => '',
			'bs_ticker_speed'    => '24',
			// Tchache
			'bs_tchache_enabled'     => '1',
			'bs_tchache_moderation'  => 'manual',
			'bs_tchache_max_length'  => '500',
			// Boîtes (6 boîtes)
			'bs_box_1_title' => 'Agenda',
			'bs_box_1_link'  => '',
			'bs_box_1_icon'  => 'calendar',
			'bs_box_2_title' => 'Écouter',
			'bs_box_2_link'  => '',
			'bs_box_2_icon'  => 'music',
			'bs_box_3_title' => 'Photos',
			'bs_box_3_link'  => '',
			'bs_box_3_icon'  => 'camera',
			'bs_box_4_title' => 'Vidéos',
			'bs_box_4_link'  => '',
			'bs_box_4_icon'  => 'video',
			'bs_box_5_title' => 'Contact',
			'bs_box_5_link'  => '',
			'bs_box_5_icon'  => 'mail',
			'bs_box_6_title' => 'Shop',
			'bs_box_6_link'  => '',
			'bs_box_6_icon'  => 'bag',
		];
	}

	public static function set_default_options(): void {
		foreach ( self::default_options() as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}
}
