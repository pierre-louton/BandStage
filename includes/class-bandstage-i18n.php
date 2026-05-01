<?php
/**
 * Internationalisation du plugin.
 *
 * @package BandStage
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_i18n
 *
 * Charge le domaine de traduction du plugin depuis /languages/.
 */
class BandStage_i18n {

	/**
	 * Charge le fichier de traduction .mo correspondant à la locale WP.
	 *
	 * Le dossier /languages/ doit contenir des fichiers nommés :
	 *   bandstage-{locale}.mo  (ex. bandstage-fr_FR.mo)
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'bandstage',
			false,
			dirname( plugin_basename( BANDSTAGE_PLUGIN_FILE ) ) . '/languages/'
		);
	}
}
