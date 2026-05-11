<?php
/**
 * Plugin principal — singleton.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Core;

defined( 'ABSPATH' ) || exit;

use BandStage\Admin\Admin;
use BandStage\Admin\Assets      as AdminAssets;
use BandStage\Admin\PostTypes;
use BandStage\Admin\SettingsPage;
use BandStage\Domain\Concerts\ConcertService;
use BandStage\Domain\Lineup\LineupService;
use BandStage\Domain\Members\MemberService;
use BandStage\Domain\News\NewsService;
use BandStage\Domain\Notifications\NotificationService;
use BandStage\Domain\Partenaires\PartenaireService;
use BandStage\Domain\Repertoire\RepertoireService;
use BandStage\Domain\Tchache\TchacheService;
use BandStage\Frontend\Assets      as FrontendAssets;
use BandStage\Frontend\FrontendController;
use BandStage\Frontend\Shortcodes;

class Plugin {

	private static ?self $instance = null;
	private Loader $loader;

	private function __construct() {
		$this->loader = new Loader();
	}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	// -------------------------------------------------------------------------
	// Activation
	// -------------------------------------------------------------------------

	public static function activate(): void {
		if ( ! did_action( 'plugins_loaded' ) ) {
			// Charger le textdomain manuellement pour les messages d'erreur.
			load_plugin_textdomain( 'bandstage', false, dirname( plugin_basename( BANDSTAGE_PLUGIN_FILE ) ) . '/languages' );
		}

		// Vérification Elementor obligatoire.
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			wp_die(
				esc_html__( 'BandStage requiert Elementor. Veuillez l\'installer et l\'activer avant.', 'bandstage' ),
				esc_html__( 'Activation impossible', 'bandstage' ),
				[ 'back_link' => true ]
			);
		}

		// Tables DB.
		Config::create_tables();

		// CPTs — enregistrement avant flush.
		PostTypes::register_all();

		// Pages.
		self::create_pages();

		// Options par défaut.
		Config::set_default_options();

		// Types de partenaires par défaut.
		self::create_default_partner_types();

		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	// -------------------------------------------------------------------------
	// Upgrade automatique (schema DB + pages) sans réactivation
	// -------------------------------------------------------------------------

	private static function maybe_upgrade(): void {
		if ( get_option( 'bs_db_version' ) !== BANDSTAGE_VERSION ) {
			Config::create_tables();
			update_option( 'bs_db_version', BANDSTAGE_VERSION );
			delete_option( 'bs_pages_created' );
		}
		// wp_insert_post() needs $wp_rewrite (init or later) — never at plugins_loaded.
		if ( ! get_option( 'bs_pages_created' ) ) {
			add_action( 'init', [ __CLASS__, 'create_missing_pages' ], 20 );
		}
	}

	public static function create_missing_pages(): void {
		self::create_pages();
		update_option( 'bs_pages_created', '1' );
	}

	// -------------------------------------------------------------------------
	// Création des pages
	// -------------------------------------------------------------------------

	private static function create_pages(): void {
		$pages = [
			'accueil'     => [ 'title' => 'BandStage — Accueil',     'shortcode' => '[bandstage_homepage]',   'template' => 'elementor_canvas' ],
			'tchache'     => [ 'title' => 'BandStage — Tchache',     'shortcode' => '[bandstage_tchache]',    'template' => 'elementor_canvas' ],
			'profil'      => [ 'title' => 'BandStage — Mon Compte',  'shortcode' => '[bandstage_profil]',     'template' => 'elementor_canvas' ],
			'studio'      => [ 'title' => 'BandStage — Studio',      'shortcode' => '[bandstage_studio]',     'template' => 'elementor_canvas' ],
			'partenaires' => [ 'title' => 'BandStage — Partenaires', 'shortcode' => '[bandstage_partenaires]','template' => 'elementor_canvas' ],
			'concerts'    => [ 'title' => 'BandStage — Concerts',   'shortcode' => '[bandstage_concerts]',   'template' => 'elementor_canvas' ],
			'references'  => [ 'title' => 'BandStage — Répertoire',  'shortcode' => '[bandstage_references]',  'template' => 'elementor_canvas' ],
			'groupe'      => [ 'title' => 'BandStage — Le groupe',   'shortcode' => '[bandstage_groupe]',     'template' => 'elementor_canvas' ],
		];

		foreach ( $pages as $slug => $data ) {
			$option_key = 'bs_page_' . $slug;
			$page_id    = (int) get_option( $option_key, 0 );

			if ( $page_id && get_post( $page_id ) ) {
				continue; // Déjà créée.
			}

			$page_id = wp_insert_post( [
				'post_title'   => $data['title'],
				'post_name'    => 'bandstage-' . $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $data['shortcode'],
			] );

			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_post_meta( $page_id, '_wp_page_template', $data['template'] );
				update_option( $option_key, $page_id );
			}
		}
	}

	// -------------------------------------------------------------------------
	// Types de partenaires par défaut
	// -------------------------------------------------------------------------

	private static function create_default_partner_types(): void {
		global $wpdb;

		$table    = Config::table_partenaire_types();
		$defaults = [
			[ 'slug' => 'magasins-musique', 'name' => 'Magasins de musique', 'icon' => '🎸' ],
			[ 'slug' => 'luthiers',         'name' => 'Luthiers',            'icon' => '🪕' ],
			[ 'slug' => 'salles-concerts',  'name' => 'Salles de concerts',  'icon' => '🎭' ],
			[ 'slug' => 'institutionnels',  'name' => 'Institutionnels',     'icon' => '🏛️' ],
		];

		foreach ( $defaults as $type ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE slug = %s LIMIT 1", $type['slug'] ) );
			if ( ! $exists ) {
				$wpdb->insert(
					$table,
					[ 'name' => $type['name'], 'slug' => $type['slug'], 'icon' => $type['icon'] ],
					[ '%s', '%s', '%s' ]
				);
			}
		}
	}

	// -------------------------------------------------------------------------
	// Run — enregistrement de tous les hooks
	// -------------------------------------------------------------------------

	public function run(): void {
		$this->load_textdomain();
		self::maybe_upgrade();
		$this->register_post_types();
		$this->register_admin();
		$this->register_public();
		$this->register_domain_services();
		$this->loader->run();
	}

	private function load_textdomain(): void {
		load_plugin_textdomain(
			'bandstage',
			false,
			dirname( plugin_basename( BANDSTAGE_PLUGIN_FILE ) ) . '/languages'
		);
	}

	private function register_post_types(): void {
		$this->loader->add_action( 'init', PostTypes::class, 'register_all' );
	}

	private function register_admin(): void {
		if ( ! is_admin() ) {
			return;
		}
		$admin         = new Admin();
		$admin_assets  = new AdminAssets();
		$settings_page = new SettingsPage();

		$this->loader->add_action( 'admin_menu',             $admin,         'add_menus' );
		$this->loader->add_action( 'admin_enqueue_scripts',  $admin_assets,  'enqueue' );
		$this->loader->add_action( 'admin_init',             $settings_page, 'register_settings' );
		$this->loader->add_action( 'init',                  $admin,         'register_ajax' );
	}

	private function register_public(): void {
		$public_ctrl  = new FrontendController();
		$public_assets = new FrontendAssets();
		$shortcodes   = new Shortcodes();

		$this->loader->add_filter( 'show_admin_bar',       $public_ctrl,   'maybe_hide_admin_bar' );
		$this->loader->add_action( 'wp_enqueue_scripts',   $public_assets, 'enqueue' );
		$this->loader->add_action( 'init',                 $shortcodes,    'register' );
	}

	private function register_domain_services(): void {
		$services = [
			new TchacheService(),
			new NewsService(),
			new PartenaireService(),
			new ConcertService(),
			new RepertoireService(),
			new MemberService(),
			new NotificationService(),
			new LineupService(),
		];

		foreach ( $services as $service ) {
			$this->loader->add_action( 'init', $service, 'register_ajax' );
		}
	}
}
