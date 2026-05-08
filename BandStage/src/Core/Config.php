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

	public static function table_partenaire_types(): string {
		global $wpdb;
		return $wpdb->prefix . 'bandstage_partenaire_types';
	}

	public static function table_partenaires(): string {
		global $wpdb;
		return $wpdb->prefix . 'bandstage_partenaires';
	}

	public static function table_concerts(): string {
		global $wpdb;
		return $wpdb->prefix . 'bandstage_concerts';
	}

	public static function table_concert_partenaires(): string {
		global $wpdb;
		return $wpdb->prefix . 'bandstage_concert_partenaires';
	}

	public static function table_repertoire(): string {
		global $wpdb;
		return $wpdb->prefix . 'bandstage_repertoire';
	}

	public static function table_references(): string {
		global $wpdb;
		return $wpdb->prefix . 'bandstage_references';
	}

	public static function table_rep_ref(): string {
		global $wpdb;
		return $wpdb->prefix . 'bandstage_rep_ref';
	}

	// -------------------------------------------------------------------------
	// Création des tables à l'activation
	// -------------------------------------------------------------------------

	public static function create_tables(): void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql_messages = "CREATE TABLE IF NOT EXISTS " . self::table_messages() . " (
        id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id     BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        content     TEXT NOT NULL,
        status      ENUM('pending','approved','spam') NOT NULL DEFAULT 'pending',
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY status (status),
        KEY created_at (created_at)
    ) $charset_collate;";

		$sql_notifications = "CREATE TABLE IF NOT EXISTS " . self::table_notifications() . " (
        id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id     BIGINT(20) UNSIGNED NOT NULL,
        type        VARCHAR(64) NOT NULL,
        payload     JSON,
        read_at     DATETIME DEFAULT NULL,
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY read_at (read_at),
        KEY created_at (created_at)
    ) $charset_collate;";

		$sql_partenaire_types = "CREATE TABLE IF NOT EXISTS " . self::table_partenaire_types() . " (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name       VARCHAR(100) NOT NULL,
        slug       VARCHAR(100) NOT NULL,
        icon       VARCHAR(10) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY slug (slug)
    ) $charset_collate;";

		$sql_partenaires = "CREATE TABLE IF NOT EXISTS " . self::table_partenaires() . " (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        type_id     BIGINT UNSIGNED NULL DEFAULT NULL,
        name        VARCHAR(150) NOT NULL,
        description TEXT NOT NULL,
        logo_path   VARCHAR(255) NOT NULL DEFAULT '',
        website     VARCHAR(255) NOT NULL DEFAULT '',
        email       VARCHAR(150) NOT NULL DEFAULT '',
        phone       VARCHAR(30)  NOT NULL DEFAULT '',
        numero      VARCHAR(10)  NOT NULL DEFAULT '',
        nom_voie    VARCHAR(150) NOT NULL DEFAULT '',
        code_postal VARCHAR(10)  NOT NULL DEFAULT '',
        ville       VARCHAR(100) NOT NULL DEFAULT '',
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY type_id (type_id),
        KEY name (name)
    ) $charset_collate;";

		$sql_concerts = "CREATE TABLE IF NOT EXISTS " . self::table_concerts() . " (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        titre       VARCHAR(200) NOT NULL,
        date_debut  DATE NOT NULL,
        date_fin    DATE NULL DEFAULT NULL,
        horaires    VARCHAR(100) NOT NULL DEFAULT '',
        nom_lieu    VARCHAR(150) NOT NULL DEFAULT '',
        numero      VARCHAR(10)  NOT NULL DEFAULT '',
        nom_voie    VARCHAR(150) NOT NULL DEFAULT '',
        code_postal VARCHAR(10)  NOT NULL DEFAULT '',
        ville       VARCHAR(100) NOT NULL DEFAULT '',
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY date_debut (date_debut)
    ) $charset_collate;";

		$sql_pivot = "CREATE TABLE IF NOT EXISTS " . self::table_concert_partenaires() . " (
        concert_id    BIGINT UNSIGNED NOT NULL,
        partenaire_id BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY (concert_id, partenaire_id)
    ) $charset_collate;";

		$sql_repertoire = "CREATE TABLE IF NOT EXISTS " . self::table_repertoire() . " (
        id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        nom_artiste    VARCHAR(150) NOT NULL,
        nom_morceau    VARCHAR(150) NOT NULL,
        remarque       TEXT NOT NULL,
        icone_artiste  VARCHAR(10) NOT NULL DEFAULT '',
        created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY nom_artiste (nom_artiste)
    ) $charset_collate;";

		$sql_references = "CREATE TABLE IF NOT EXISTS " . self::table_references() . " (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        nom_style  VARCHAR(100) NOT NULL,
        image_url  VARCHAR(255) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY nom_style (nom_style)
    ) $charset_collate;";

		$sql_rep_ref = "CREATE TABLE IF NOT EXISTS " . self::table_rep_ref() . " (
        repertoire_id BIGINT UNSIGNED NOT NULL,
        reference_id  BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY (repertoire_id, reference_id)
    ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_messages );
		dbDelta( $sql_notifications );
		dbDelta( $sql_partenaire_types );
		dbDelta( $sql_partenaires );
		dbDelta( $sql_concerts );
		dbDelta( $sql_pivot );
		dbDelta( $sql_repertoire );
		dbDelta( $sql_references );
		dbDelta( $sql_rep_ref );
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
