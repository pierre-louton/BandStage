<?php
/**
 * Shortcodes publics BandStage.
 *
 * Routing interne : $_GET['bs_view'] + $_GET['bs_id']
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Frontend;

defined( 'ABSPATH' ) || exit;

use BandStage\Domain\Concerts\ConcertService;
use BandStage\Domain\Lineup\LineupService;
use BandStage\Domain\News\NewsService;
use BandStage\Domain\Partenaires\PartenaireService;
use BandStage\Domain\Tchache\TchacheService;

class Shortcodes {

	public function register(): void {
		add_shortcode( 'bandstage_homepage',   [ $this, 'homepage' ] );
		add_shortcode( 'bandstage_tchache',    [ $this, 'tchache' ] );
		add_shortcode( 'bandstage_profil',     [ $this, 'profil' ] );
		add_shortcode( 'bandstage_studio',     [ $this, 'studio' ] );
		add_shortcode( 'bandstage_partenaires',[ $this, 'partenaires' ] );
		add_shortcode( 'bandstage_concerts',   [ $this, 'concerts' ] );
		add_shortcode( 'bandstage_groupe',     [ $this, 'groupe' ] );
	}

	// -------------------------------------------------------------------------
	// [bandstage_homepage] — tous visiteurs
	// -------------------------------------------------------------------------

	public function homepage(): string {
		ob_start();
		Assets::maybe_inject_dynamic_css();
		include BANDSTAGE_PLUGIN_DIR . 'templates/public/homepage.php';
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// [bandstage_tchache] — lecture publique, écriture si connecté
	// -------------------------------------------------------------------------

	public function tchache(): string {
		$service  = new TchacheService();
		$messages = $service->get_approved( 50 );
		ob_start();
		Assets::maybe_inject_dynamic_css();
		include BANDSTAGE_PLUGIN_DIR . 'templates/public/tchache.php';
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// [bandstage_profil]
	//   Visiteur  → formulaire de connexion stylisé
	//   Auteur+   → profil éditable
	// -------------------------------------------------------------------------

	public function profil(): string {
		ob_start();
		Assets::maybe_inject_dynamic_css();
		if ( ! is_user_logged_in() ) {
			include BANDSTAGE_PLUGIN_DIR . 'templates/public/profil-public.php';
		} else {
			include BANDSTAGE_PLUGIN_DIR . 'templates/public/profil.php';
		}
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// [bandstage_studio]
	//   Visiteur  → archive publique des bs_news (Humeurs)
	//   Auteur+   → CRUD actus
	// -------------------------------------------------------------------------

	public function studio(): string {
		ob_start();
		Assets::maybe_inject_dynamic_css();

		if ( ! current_user_can( 'edit_posts' ) ) {
			$news_service = new NewsService();
			$news_list    = $news_service->get_recent( 20 );
			include BANDSTAGE_PLUGIN_DIR . 'templates/public/studio-public.php';
			return ob_get_clean();
		}

		// Routing Studio (Auteur)
		$routes = include BANDSTAGE_PLUGIN_DIR . 'config/routes.php';
		$view   = sanitize_key( $_GET['bs_view'] ?? 'dashboard' );

		if ( 'edit' === $view ) {
			$post_id      = absint( $_GET['bs_id'] ?? 0 );
			$news_service = new NewsService();
			$current_news = $post_id ? $news_service->get( $post_id ) : null;
			include $routes['studio']['edit'];
		} else {
			$news_service = new NewsService();
			$news_list    = $news_service->get_recent( 50 );
			include $routes['studio']['dashboard'];
		}

		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// [bandstage_partenaires]
	//   Visiteur  → grille publique
	//   Auteur+   → CRUD partenaires
	// -------------------------------------------------------------------------

	public function partenaires(): string {
		$service = new PartenaireService();
		ob_start();
		Assets::maybe_inject_dynamic_css();

		if ( ! current_user_can( 'edit_posts' ) ) {
			$partenaires            = $service->get_grouped_by_type();
			$concerts_by_partenaire = $service->get_upcoming_concerts_by_partenaire();
			include BANDSTAGE_PLUGIN_DIR . 'templates/public/partenaires-public.php';
			return ob_get_clean();
		}

		$routes = include BANDSTAGE_PLUGIN_DIR . 'config/routes.php';
		$view   = sanitize_key( $_GET['bs_view'] ?? 'list' );

		if ( 'edit' === $view ) {
			$post_id    = absint( $_GET['bs_id'] ?? 0 );
			$partenaire = $post_id ? $service->get( $post_id ) : null;
			$types      = $service->get_types();
			include $routes['partenaires']['edit'];
		} else {
			$partenaires = $service->get_all();
			include $routes['partenaires']['list'];
		}

		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// [bandstage_concerts]
	//   Visiteur  → liste des concerts à venir
	//   Auteur+   → CRUD concerts
	// -------------------------------------------------------------------------

	public function concerts(): string {
		$service = new ConcertService();
		ob_start();
		Assets::maybe_inject_dynamic_css();

		if ( ! current_user_can( 'edit_posts' ) ) {
			$concerts = $service->get_upcoming();
			include BANDSTAGE_PLUGIN_DIR . 'templates/public/concerts-public.php';
			return ob_get_clean();
		}

		$routes = include BANDSTAGE_PLUGIN_DIR . 'config/routes.php';
		$view   = sanitize_key( $_GET['bs_view'] ?? 'list' );

		if ( 'edit' === $view ) {
			$concert_id         = absint( $_GET['bs_id'] ?? 0 );
			$concert            = $concert_id ? $service->get( $concert_id ) : null;
			$partenaire_service = new \BandStage\Domain\Partenaires\PartenaireService();
			$all_partenaires    = $partenaire_service->get_all();
			include $routes['concerts']['edit'];
		} else {
			$concerts = $service->get_all();
			include $routes['concerts']['list'];
		}

		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// [bandstage_groupe]
	//   Visiteur  → grille publique des membres du lineup
	//   Auteur+   → CRUD membres lineup
	// -------------------------------------------------------------------------

	public function groupe(): string {
		$service = new LineupService();
		ob_start();
		Assets::maybe_inject_dynamic_css();

		if ( ! current_user_can( 'edit_posts' ) ) {
			$members = $service->get_ordered();
			include BANDSTAGE_PLUGIN_DIR . 'templates/public/groupe-public.php';
			return ob_get_clean();
		}

		$routes = include BANDSTAGE_PLUGIN_DIR . 'config/routes.php';
		$view   = sanitize_key( $_GET['bs_view'] ?? 'list' );

		if ( 'edit' === $view ) {
			$post_id = absint( $_GET['bs_id'] ?? 0 );
			$member  = $post_id ? $service->get( $post_id ) : null;
			include $routes['groupe']['edit'];
		} else {
			$members = $service->get_ordered();
			include $routes['groupe']['list'];
		}

		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// URL helpers
	// -------------------------------------------------------------------------

	public static function studio_url( string $view = 'dashboard', int $post_id = 0 ): string {
		$url = get_permalink( (int) get_option( 'bs_page_studio' ) );
		if ( ! $url ) {
			return '#';
		}
		$args = [ 'bs_view' => $view ];
		if ( $post_id ) {
			$args['bs_id'] = $post_id;
		}
		return add_query_arg( $args, $url );
	}

	public static function partenaires_url( string $view = 'list', int $post_id = 0 ): string {
		$url = get_permalink( (int) get_option( 'bs_page_partenaires' ) );
		if ( ! $url ) {
			return '#';
		}
		$args = [ 'bs_view' => $view ];
		if ( $post_id ) {
			$args['bs_id'] = $post_id;
		}
		return add_query_arg( $args, $url );
	}

	public static function concerts_url( string $view = 'list', int $id = 0 ): string {
		$url = get_permalink( (int) get_option( 'bs_page_concerts' ) );
		if ( ! $url ) {
			return '#';
		}
		$args = [ 'bs_view' => $view ];
		if ( $id ) {
			$args['bs_id'] = $id;
		}
		return add_query_arg( $args, $url );
	}

	public static function groupe_url( string $view = 'list', int $post_id = 0 ): string {
		$url = get_permalink( (int) get_option( 'bs_page_groupe' ) );
		if ( ! $url ) {
			return '#';
		}
		$args = [ 'bs_view' => $view ];
		if ( $post_id ) {
			$args['bs_id'] = $post_id;
		}
		return add_query_arg( $args, $url );
	}
}
