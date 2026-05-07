<?php
/**
 * Settings API — 9 onglets, 1 groupe par onglet.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Admin;

defined( 'ABSPATH' ) || exit;

class SettingsPage {

	private array $tabs = [
		'groupe'        => 'Groupe',
		'apparence'     => 'Apparence',
		'boites'        => 'Boîtes',
		'ticker'        => 'Ticker',
		'tchache'       => 'Tchache',
		'membres'       => 'Membres',
		'notifications' => 'Notifications',
		'partenaires'   => 'Partenaires',
		'avance'        => 'Avancé',
	];

	/**
	 * Retourne le nom du groupe Settings API pour un onglet.
	 */
	public function group( string $tab ): string {
		return 'bs_settings_' . $tab;
	}

	public function register_settings(): void {
		// ---- Groupe ----
		register_setting( 'bs_settings_groupe', 'bs_band_name',    [ 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'bs_settings_groupe', 'bs_band_tagline', [ 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'bs_settings_groupe', 'bs_band_city',    [ 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'bs_settings_groupe', 'bs_band_email',   [ 'sanitize_callback' => 'sanitize_email' ] );

		// ---- Apparence ----
		register_setting( 'bs_settings_apparence', 'bs_bg_color_start', [ 'sanitize_callback' => 'sanitize_hex_color' ] );
		register_setting( 'bs_settings_apparence', 'bs_bg_color_end',   [ 'sanitize_callback' => 'sanitize_hex_color' ] );
		register_setting( 'bs_settings_apparence', 'bs_accent_color',   [ 'sanitize_callback' => 'sanitize_hex_color' ] );
		register_setting( 'bs_settings_apparence', 'bs_cream_color',    [ 'sanitize_callback' => 'sanitize_hex_color' ] );

		// ---- Boîtes (6) ----
		for ( $i = 1; $i <= 6; $i++ ) {
			register_setting( 'bs_settings_boites', "bs_box_{$i}_title", [ 'sanitize_callback' => 'sanitize_text_field' ] );
			register_setting( 'bs_settings_boites', "bs_box_{$i}_link",  [ 'sanitize_callback' => 'esc_url_raw' ] );
			register_setting( 'bs_settings_boites', "bs_box_{$i}_icon",  [ 'sanitize_callback' => 'sanitize_text_field' ] );
		}

		// ---- Ticker ----
		register_setting( 'bs_settings_ticker', 'bs_ticker_enabled', [ 'sanitize_callback' => 'absint' ] );
		register_setting( 'bs_settings_ticker', 'bs_ticker_source',  [ 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'bs_settings_ticker', 'bs_ticker_items',   [ 'sanitize_callback' => 'sanitize_textarea_field' ] );
		register_setting( 'bs_settings_ticker', 'bs_ticker_speed',   [ 'sanitize_callback' => 'absint' ] );

		// ---- Tchache ----
		register_setting( 'bs_settings_tchache', 'bs_tchache_enabled',    [ 'sanitize_callback' => 'absint' ] );
		register_setting( 'bs_settings_tchache', 'bs_tchache_moderation', [ 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'bs_settings_tchache', 'bs_tchache_max_length', [ 'sanitize_callback' => 'absint' ] );

		// ---- Membres (réservé futur usage) ----
		// ---- Notifications ----
		// ---- Partenaires — types gérés en AJAX ----
		// ---- Avancé ----
	}

	public function get_tabs(): array {
		return $this->tabs;
	}
}
