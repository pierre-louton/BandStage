<?php
/**
 * Ressources back-office WP.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Admin;

defined( 'ABSPATH' ) || exit;

class Assets {

	public function enqueue( string $hook ): void {
		if ( ! str_contains( $hook, 'bandstage' ) && ! str_contains( $hook, 'bs_' ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'bandstage-admin',
			BANDSTAGE_PLUGIN_URL . 'assets/css/admin.css',
			[],
			BANDSTAGE_VERSION
		);

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script(
			'bandstage-admin',
			BANDSTAGE_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery', 'wp-color-picker' ],
			BANDSTAGE_VERSION,
			true
		);

		wp_localize_script( 'bandstage-admin', 'BsAdmin', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( BANDSTAGE_NONCE ),
			'i18n'    => [
				'confirm_delete' => __( 'Supprimer définitivement ?', 'bandstage' ),
				'saving'         => __( 'Enregistrement…', 'bandstage' ),
			],
		] );
	}
}
