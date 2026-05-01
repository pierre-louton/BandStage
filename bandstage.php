<?php
/**
 * Plugin Name:       BandStage
 * Plugin URI:        https://github.com/VOTRE-USERNAME/bandstage
 * Description:       Homepage mobile-first pour groupes de musique — grille de sections, ticker d'actualités, mini-forum (Tchache), profils membres, notifications de concerts et panneau d'apparence entièrement configurable.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      8.1
 * Author:            Pierre Beaubié
 * Author URI:        https://github.com/pierrebeaubie
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bandstage
 * Domain Path:       /languages
 *
 * @package BandStage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BANDSTAGE_VERSION',     '1.0.0' );
define( 'BANDSTAGE_MIN_WP',      '6.2' );
define( 'BANDSTAGE_MIN_PHP',     '8.1' );
define( 'BANDSTAGE_PLUGIN_FILE', __FILE__ );
define( 'BANDSTAGE_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'BANDSTAGE_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'BANDSTAGE_SLUG',        'bandstage' );
define( 'BANDSTAGE_NONCE',       'bandstage_nonce' );

function bandstage_check_requirements(): bool {
	global $wp_version;
	if ( version_compare( PHP_VERSION, BANDSTAGE_MIN_PHP, '<' ) ) {
		add_action( 'admin_notices', static function () {
			printf( '<div class="notice notice-error"><p>%s</p></div>',
				esc_html( sprintf( __( 'BandStage requiert PHP %1$s ou supérieur. Version actuelle : %2$s.', 'bandstage' ), BANDSTAGE_MIN_PHP, PHP_VERSION ) )
			);
		} );
		return false;
	}
	if ( version_compare( $wp_version, BANDSTAGE_MIN_WP, '<' ) ) {
		add_action( 'admin_notices', static function () use ( $wp_version ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>',
				esc_html( sprintf( __( 'BandStage requiert WordPress %1$s ou supérieur. Version actuelle : %2$s.', 'bandstage' ), BANDSTAGE_MIN_WP, $wp_version ) )
			);
		} );
		return false;
	}
	return true;
}

if ( ! bandstage_check_requirements() ) {
	return;
}

// Chargement — l'Activator est requis en dehors du hook d'activation car
// BandStage_Admin::register_settings() l'utilise à chaque requête admin.
require_once BANDSTAGE_PLUGIN_DIR . 'includes/class-bandstage-activator.php';
require_once BANDSTAGE_PLUGIN_DIR . 'includes/class-bandstage-icons.php';
require_once BANDSTAGE_PLUGIN_DIR . 'includes/class-bandstage-loader.php';
require_once BANDSTAGE_PLUGIN_DIR . 'includes/class-bandstage-i18n.php';
require_once BANDSTAGE_PLUGIN_DIR . 'includes/class-bandstage-tchache.php';
require_once BANDSTAGE_PLUGIN_DIR . 'includes/class-bandstage-members.php';
require_once BANDSTAGE_PLUGIN_DIR . 'includes/class-bandstage-notifications.php';
require_once BANDSTAGE_PLUGIN_DIR . 'includes/class-bandstage-post-types.php';
require_once BANDSTAGE_PLUGIN_DIR . 'includes/class-bandstage-studio.php';
require_once BANDSTAGE_PLUGIN_DIR . 'admin/class-bandstage-admin.php';
require_once BANDSTAGE_PLUGIN_DIR . 'public/class-bandstage-public.php';

register_activation_hook( BANDSTAGE_PLUGIN_FILE, array( 'BandStage_Activator', 'activate' ) );
register_deactivation_hook( BANDSTAGE_PLUGIN_FILE, array( 'BandStage_Activator', 'deactivate' ) );

final class BandStage {

	private static ?BandStage $instance = null;
	private BandStage_Loader $loader;

	private function __construct() {
		$this->loader = new BandStage_Loader();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function __clone() {}

	public static function get_instance(): BandStage {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function set_locale(): void {
		$i18n = new BandStage_i18n();
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks(): void {
		// Custom Post Types + Taxonomies + Meta boxes.
		$cpt = new BandStage_Post_Types();
		$this->loader->add_action( 'init',              $cpt, 'register' );
		$this->loader->add_action( 'add_meta_boxes',    $cpt, 'add_partenaire_meta_boxes' );
		$this->loader->add_action( 'save_post_bs_partenaire', $cpt, 'save_partenaire_meta', 10, 1 );
		$this->loader->add_filter( 'manage_bs_news_posts_columns',          $cpt, 'news_columns' );
		$this->loader->add_action( 'manage_bs_news_posts_custom_column',     $cpt, 'news_column_content', 10, 2 );
		$this->loader->add_filter( 'manage_bs_partenaire_posts_columns',     $cpt, 'partenaire_columns' );
		$this->loader->add_action( 'manage_bs_partenaire_posts_custom_column',$cpt, 'partenaire_column_content', 10, 2 );
		$this->loader->add_filter( 'manage_edit-bs_partenaire_sortable_columns', $cpt, 'partenaire_sortable_columns' );
		$admin   = new BandStage_Admin( BANDSTAGE_VERSION );
		$tchache = new BandStage_Tchache();

		$this->loader->add_action( 'admin_enqueue_scripts', $admin,   'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin,   'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu',            $admin,   'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init',            $admin,   'register_settings' );
		$this->loader->add_action( 'wp_ajax_bs_moderate_message', $tchache, 'ajax_moderate' );
		$this->loader->add_action( 'wp_ajax_bs_export_settings',  $admin,   'ajax_export_settings' );
		$this->loader->add_action( 'wp_ajax_bs_import_settings',  $admin,   'ajax_import_settings' );
	}

	private function define_public_hooks(): void {
		$public  = new BandStage_Public( BANDSTAGE_VERSION );
		$tchache = new BandStage_Tchache();
		$members = new BandStage_Members();

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

		// Masque la barre d'admin WP pour les non-administrateurs.
		add_filter( 'show_admin_bar', array( $public, 'maybe_hide_admin_bar' ) );

		add_shortcode( 'bandstage_homepage',  array( $public, 'render_homepage' ) );
		add_shortcode( 'bandstage_groupe',      array( $public, 'render_groupe' ) );
		add_shortcode( 'bandstage_references',  array( $public, 'render_references' ) );
		add_shortcode( 'bandstage_concerts',  array( $public, 'render_concerts' ) );
		add_shortcode( 'bandstage_tchache',  array( $public, 'render_tchache' ) );
		add_shortcode( 'bandstage_profil',            array( $public, 'render_profil' ) );
		add_shortcode( 'bandstage_humeurs',           array( $public, 'render_humeurs' ) );
		add_shortcode( 'bandstage_partenaires_public', array( $public, 'render_partenaires_public' ) );

		// Studio.
		$studio = new BandStage_Studio();
		add_shortcode( 'bandstage_studio',      array( $studio, 'render' ) );
		add_shortcode( 'bandstage_partenaires', array( $studio, 'render_partenaires' ) );
		$this->loader->add_action( 'wp_ajax_bs_save_news',          $studio, 'ajax_save_news' );
		$this->loader->add_action( 'wp_ajax_bs_delete_news',        $studio, 'ajax_delete_news' );
		$this->loader->add_action( 'wp_ajax_bs_save_partenaire',    $studio, 'ajax_save_partenaire' );
		$this->loader->add_action( 'wp_ajax_bs_delete_partenaire',  $studio, 'ajax_delete_partenaire' );
		$this->loader->add_action( 'wp_ajax_bs_upload_studio_image',$studio, 'ajax_upload_image' );

		$this->loader->add_action( 'wp_ajax_bs_post_message',        $tchache, 'ajax_post_message' );
		$this->loader->add_action( 'wp_ajax_nopriv_bs_post_message',  $tchache, 'ajax_post_message' );
		$this->loader->add_action( 'wp_ajax_bs_load_messages',        $tchache, 'ajax_load_messages' );
		$this->loader->add_action( 'wp_ajax_nopriv_bs_load_messages', $tchache, 'ajax_load_messages' );
		$this->loader->add_action( 'wp_ajax_nopriv_bs_login',     $members, 'ajax_login' );
		$this->loader->add_action( 'wp_ajax_bs_send_reprise',        $members, 'ajax_send_reprise' );
		$this->loader->add_action( 'wp_ajax_nopriv_bs_send_reprise', $members, 'ajax_send_reprise' );
		$this->loader->add_action( 'wp_ajax_nopriv_bs_register',      $members, 'ajax_register' );
		$this->loader->add_action( 'wp_ajax_bs_update_profile',       $members, 'ajax_update_profile' );
		$this->loader->add_action( 'wp_ajax_bs_approve_member',   $members, 'ajax_approve_member' );
		$this->loader->add_action( 'wp_ajax_bs_delete_member',    $members, 'ajax_delete_member' );
		$this->loader->add_action( 'wp_ajax_bs_save_preferences',     $members, 'ajax_save_preferences' );
	}

	public function run(): void {
		$this->loader->run();
	}
}

BandStage::get_instance()->run();
