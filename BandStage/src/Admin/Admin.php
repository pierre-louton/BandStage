<?php
/**
 * Administration WP — menus et pages back-office.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Admin;

defined( 'ABSPATH' ) || exit;

use BandStage\Core\Plugin;
use BandStage\Domain\Members\MemberService;
use BandStage\Domain\Tchache\TchacheService;

class Admin {

	public function register_ajax(): void {
		add_action( 'wp_ajax_bs_create_pages',  [ $this, 'ajax_create_pages' ] );
		add_action( 'wp_ajax_bs_repair_pages',  [ $this, 'ajax_repair_pages' ] );
	}

	public function ajax_create_pages(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Accès refusé.' ], 403 );
		}

		Plugin::create_missing_pages();
		wp_send_json_success( [ 'message' => 'Pages créées ou déjà existantes.' ] );
	}

	public function ajax_repair_pages(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Accès refusé.' ], 403 );
		}

		Plugin::repair_pages();
		wp_send_json_success( [ 'message' => 'Pages réparées : doublons supprimés, options mises à jour.' ] );
	}

	public function add_menus(): void {
		// Menu racine
		add_menu_page(
			__( 'BandStage', 'bandstage' ),
			__( 'BandStage', 'bandstage' ),
			'manage_options',
			'bandstage',
			[ $this, 'render_dashboard' ],
			'dashicons-format-audio',
			30
		);

		// Sous-menu : Tableau de bord
		add_submenu_page(
			'bandstage',
			__( 'Tableau de bord', 'bandstage' ),
			__( 'Tableau de bord', 'bandstage' ),
			'manage_options',
			'bandstage',
			[ $this, 'render_dashboard' ]
		);

		// Sous-menu : Tchache (modération)
		add_submenu_page(
			'bandstage',
			__( 'Modération Tchache', 'bandstage' ),
			__( 'Tchache', 'bandstage' ),
			'manage_options',
			'bandstage-tchache',
			[ $this, 'render_tchache' ]
		);

		// Sous-menu : Membres WP
		add_submenu_page(
			'bandstage',
			__( 'Membres du site', 'bandstage' ),
			__( 'Membres', 'bandstage' ),
			'manage_options',
			'bandstage-members',
			[ $this, 'render_members' ]
		);

		// Sous-menu : Réglages
		add_submenu_page(
			'bandstage',
			__( 'Réglages BandStage', 'bandstage' ),
			__( 'Réglages', 'bandstage' ),
			'manage_options',
			'bandstage-settings',
			[ $this, 'render_settings' ]
		);
	}

	public function render_dashboard(): void {
		echo '<div class="wrap"><h1>' . esc_html__( 'BandStage', 'bandstage' ) . '</h1>';
		echo '<p>' . esc_html__( 'Bienvenue dans l\'administration de BandStage.', 'bandstage' ) . '</p></div>';
	}

	public function render_tchache(): void {
		$service = new TchacheService();
		$pending = $service->get_pending();
		include BANDSTAGE_PLUGIN_DIR . 'templates/admin/pages/tchache-moderation.php';
	}

	public function render_members(): void {
		$service = new MemberService();
		$members = $service->get_band_members();
		include BANDSTAGE_PLUGIN_DIR . 'templates/admin/pages/members.php';
	}

	public function render_settings(): void {
		$settings = new SettingsPage();
		include BANDSTAGE_PLUGIN_DIR . 'templates/admin/settings/page-settings.php';
	}
}
