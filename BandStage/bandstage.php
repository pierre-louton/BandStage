<?php
/**
 * Plugin Name:       BandStage
 * Plugin URI:        https://wordpress.org/plugins/bandstage/
 * Description:       Site mobile-first pour groupes de musique — Actus, Tchache, Membres, Partenaires.
 * Version:           1.1.0
 * Requires at least: 6.2
 * Requires PHP:      8.1
 * Author:            Pierre Beaubié
 * Author URI:        https://github.com/pierrebeaubie
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bandstage
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

// Constantes
define( 'BANDSTAGE_VERSION',     '1.1.0' );
define( 'BANDSTAGE_PLUGIN_FILE', __FILE__ );
define( 'BANDSTAGE_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'BANDSTAGE_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'BANDSTAGE_NONCE',       'bandstage_nonce' );
define( 'BANDSTAGE_SLUG',        'bandstage' );

// Autoload PSR-4 : BandStage\ → src/
spl_autoload_register( function ( string $class ): void {
	if ( ! str_starts_with( $class, 'BandStage\\' ) ) {
		return;
	}
	$relative = str_replace( [ 'BandStage\\', '\\' ], [ '', '/' ], $class );
	$file     = BANDSTAGE_PLUGIN_DIR . 'src/' . $relative . '.php';
	if ( is_readable( $file ) ) {
		require_once $file;
	}
} );

// Activation / désactivation
register_activation_hook( __FILE__,   [ 'BandStage\\Core\\Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'BandStage\\Core\\Plugin', 'deactivate' ] );

// Lancement
add_action( 'plugins_loaded', function (): void {
	BandStage\Core\Plugin::get_instance()->run();
} );
