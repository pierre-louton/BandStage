<?php
/**
 * Fonctionnalités côté public (front-end).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_Public
 */
class BandStage_Public {

	private string $version;

	/** Évite d'injecter le bloc <style> plusieurs fois par page. */
	private static bool $css_injected = false;

	public function __construct( string $version ) {
		$this->version = $version;
	}

	// -----------------------------------------------------------------------
	// Assets (enqueues)
	// -----------------------------------------------------------------------

	public function enqueue_styles(): void {
		// On enregistre d'abord (wp_register_style) pour que les autres modules
		// (Studio, etc.) puissent le déclarer comme dépendance sans erreur,
		// même s'ils sont chargés avant wp_enqueue_scripts.
		wp_register_style(
			'bandstage-public',
			BANDSTAGE_PLUGIN_URL . 'public/css/bandstage-public.css',
			array(),
			$this->version
		);
		wp_enqueue_style( 'bandstage-public' );

		// Google Fonts dynamiques selon les réglages.
		$brand_font = sanitize_text_field( (string) get_option( 'bs_brand_font', 'Playfair Display' ) );
		$box_font   = sanitize_text_field( (string) get_option( 'bs_box_font', 'Oswald' ) );
		$fonts      = array_unique( array( $brand_font, $box_font, 'DM Sans' ) );
		$families   = implode( '&family=', array_map(
			static fn( $f ) => rawurlencode( $f ) . ':wght@400;500;600;700;900',
			$fonts
		) );

		wp_enqueue_style(
			'bandstage-fonts',
			'https://fonts.googleapis.com/css2?family=' . $families . '&display=swap',
			array(),
			null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		);
	}

	public function enqueue_scripts(): void {
		wp_enqueue_script(
			'bandstage-public',
			BANDSTAGE_PLUGIN_URL . 'public/js/bandstage-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			'bandstage-public',
			'bandstage',
			array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( BANDSTAGE_NONCE ),
				'logged_in' => is_user_logged_in(),
				'user_id'   => get_current_user_id(),
				'user_name' => is_user_logged_in() ? wp_get_current_user()->display_name : '',
				'strings'   => array(
					'send'           => __( 'Envoyer', 'bandstage' ),
					'sending'        => __( 'Envoi…', 'bandstage' ),
					'load_more'      => __( 'Charger plus', 'bandstage' ),
					'no_messages'    => __( 'Aucun message pour l\'instant.', 'bandstage' ),
					'login_required' => __( 'Connectez-vous pour participer.', 'bandstage' ),
				),
			)
		);
	}

	// -----------------------------------------------------------------------
	// Shortcodes
	// -----------------------------------------------------------------------

	public function render_homepage( array $atts = array() ): string {
		ob_start();
		self::maybe_inject_dynamic_css(); // ← inline, avant tout HTML
		include BANDSTAGE_PLUGIN_DIR . 'public/partials/bandstage-homepage.php';
		return ob_get_clean();
	}

	public function render_tchache( array $atts = array() ): string {
		ob_start();
		self::maybe_inject_dynamic_css();
		include BANDSTAGE_PLUGIN_DIR . 'public/partials/bandstage-tchache.php';
		return ob_get_clean();
	}

	public function render_profil( array $atts = array() ): string {
		ob_start();
		self::maybe_inject_dynamic_css();
		include BANDSTAGE_PLUGIN_DIR . 'public/partials/bandstage-profil.php';
		return ob_get_clean();
	}

	// -----------------------------------------------------------------------
	// CSS dynamique
	// -----------------------------------------------------------------------

	/**
	 * Injecte le bloc <style> inline une seule fois par page.
	 *
	 * POURQUOI INLINE et non via wp_head :
	 * Les shortcodes sont traités par the_content(), qui s'exécute APRÈS wp_head.
	 * Un add_action('wp_head') déclenché depuis un shortcode arrive donc trop tard —
	 * wp_head est déjà passé. La seule solution fiable est d'injecter le <style>
	 * directement dans la sortie du shortcode.
	 * Un <style> dans le body est valide HTML5 et supporté par tous les navigateurs.
	 */
	public static function maybe_inject_dynamic_css(): void {
		if ( self::$css_injected ) {
			return;
		}
		self::$css_injected = true;
		self::render_dynamic_css();
	}

	/**
	 * Génère le bloc <style> avec les valeurs issues des réglages admin.
	 * Cible directement .bs-wrap (et variantes) pour surcharger les valeurs
	 * statiques du fichier CSS — écrire sur :root ne suffit pas car .bs-wrap
	 * redéfinit les variables localement avec une spécificité supérieure.
	 */
	public static function render_dynamic_css(): void {
		$bg_start   = sanitize_hex_color( (string) get_option( 'bs_bg_color_start', '#1535A8' ) ) ?: '#1535A8';
		$bg_end     = sanitize_hex_color( (string) get_option( 'bs_bg_color_end',   '#020828' ) ) ?: '#020828';
		$bg_angle   = absint( get_option( 'bs_bg_angle', 168 ) );
		$accent     = sanitize_hex_color( (string) get_option( 'bs_accent_color', '#D4A820' ) ) ?: '#D4A820';
		$text_color = sanitize_hex_color( (string) get_option( 'bs_text_color',   '#F0E6CE' ) ) ?: '#F0E6CE';
		$brand_font = sanitize_text_field( (string) get_option( 'bs_brand_font', 'Playfair Display' ) );
		$box_font   = sanitize_text_field( (string) get_option( 'bs_box_font',   'Oswald' ) );
		$box_radius = absint( get_option( 'bs_box_radius', 8 ) );
		$ticker_bg  = sanitize_hex_color( (string) get_option( 'bs_ticker_bg_color',   '#D4A820' ) ) ?: '#D4A820';
		$ticker_txt = sanitize_hex_color( (string) get_option( 'bs_ticker_text_color', '#0A1240' ) ) ?: '#0A1240';
		$ticker_spd = absint( get_option( 'bs_ticker_speed', 24 ) );
		$custom_css = wp_strip_all_tags( (string) get_option( 'bs_custom_css', '' ) );

		// Dégradé de fond complet (halo + gradient directionnel).
		$bg_image = sprintf(
			'radial-gradient(ellipse 80%% 40%% at 50%% 0%%, rgba(60,100,255,.35) 0%%, transparent 70%%), linear-gradient(%ddeg, %s 0%%, %s 100%%)',
			$bg_angle,
			$bg_start,
			$bg_end
		);

		// Calcul couleur accent allégée pour le halo du titre.
		$accent_r = hexdec( substr( ltrim( $accent, '#' ), 0, 2 ) );
		$accent_g = hexdec( substr( ltrim( $accent, '#' ), 2, 2 ) );
		$accent_b = hexdec( substr( ltrim( $accent, '#' ), 4, 2 ) );

		echo '<style id="bandstage-dynamic-css">' . "\n";

		// Variables CSS sur tous les conteneurs BandStage.
		echo ".bs-wrap,.bs-tc-wrap,.bs-pr-wrap{\n";
		printf( "--bs-bg-start:%s;\n",                esc_html( $bg_start ) );
		printf( "--bs-bg-end:%s;\n",                  esc_html( $bg_end ) );
		printf( "--bs-bg-angle:%sdeg;\n",             esc_html( (string) $bg_angle ) );
		printf( "--bs-accent:%s;\n",                  esc_html( $accent ) );
		printf( "--bs-text:%s;\n",                    esc_html( $text_color ) );
		printf( "--bs-brand-font:\"%s\",Georgia,serif;\n",    esc_html( $brand_font ) );
		printf( "--bs-box-font:\"%s\",Arial,sans-serif;\n",   esc_html( $box_font ) );
		printf( "--bs-radius:%spx;\n",                esc_html( (string) $box_radius ) );
		printf( "--bs-ticker-bg:%s;\n",               esc_html( $ticker_bg ) );
		printf( "--bs-ticker-txt:%s;\n",              esc_html( $ticker_txt ) );
		echo "}\n";

		// Fond appliqué directement — le background avec multi-gradient
		// ne peut pas être piloté par une seule variable CSS.
		printf( ".bs-wrap{background:%s;}\n", $bg_image ); // phpcs:ignore WordPress.Security.EscapeOutput

		// Titre — couleur et ombre depuis l'accent.
		printf(
			".bs-header__brand{color:%s;text-shadow:0 0 40px rgba(%d,%d,%d,.45);}\n",
			esc_html( $accent ),
			$accent_r, $accent_g, $accent_b
		);

		// Ticker.
		printf( ".bs-ticker{background:%s;}\n", esc_html( $ticker_bg ) );
		printf( ".bs-ticker-track{color:%s;animation-duration:%ds;}\n",
			esc_html( $ticker_txt ),
			$ticker_spd
		);

		// Boîtes — arrondi.
		printf( ".bs-box{border-radius:%spx;}\n", esc_html( (string) $box_radius ) );

		// Accent sur les flèches, badges, boutons.
		printf( ".bs-box__arrow,.bs-badge,.bs-btn--gold{color:inherit;}\n" );
		printf( ".bs-btn--gold,.bs-badge,.bs-tc-count{background:%s;}\n", esc_html( $accent ) );
		printf( ".bs-box__arrow{color:%s;}\n", esc_html( $accent ) );
		printf( ".bs-bnav__item--active,.bs-bnav__item.is-active{color:%s;}\n", esc_html( $accent ) );
		printf( ".bs-tc-send{background:%s;}\n", esc_html( $accent ) );
		printf( ".bs-pr-btn--gold{background:%s;}\n", esc_html( $accent ) );

		// CSS personnalisé admin.
		if ( $custom_css ) {
			echo $custom_css . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		echo '</style>' . "\n";
	}

	/**
	 * Shortcode [bandstage_references] — influences et répertoire.
	 *
	 * @param array $atts Attributs du shortcode.
	 * @return string
	 */
	public function render_references( array $atts = array() ): string {
		ob_start();
		self::maybe_inject_dynamic_css();
		include BANDSTAGE_PLUGIN_DIR . 'public/partials/bandstage-references.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode [bandstage_groupe] — présentation du groupe.
	 *
	 * @param array $atts Attributs du shortcode.
	 * @return string
	 */
	public function render_groupe( array $atts = array() ): string {
		ob_start();
		self::maybe_inject_dynamic_css();
		include BANDSTAGE_PLUGIN_DIR . 'public/partials/bandstage-groupe.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode [bandstage_concerts] — dates de concerts.
	 *
	 * @param array $atts Attributs du shortcode.
	 * @return string
	 */
	public function render_concerts( array $atts = array() ): string {
		ob_start();
		self::maybe_inject_dynamic_css();
		include BANDSTAGE_PLUGIN_DIR . 'public/partials/bandstage-concerts.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode [bandstage_humeurs] — billets d'humeur publics.
	 *
	 * @param array $atts Attributs du shortcode.
	 * @return string
	 */
	public function render_humeurs( array $atts = array() ): string {
		ob_start();
		self::maybe_inject_dynamic_css();
		include BANDSTAGE_PLUGIN_DIR . 'public/partials/bandstage-humeurs.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode [bandstage_partenaires_public] — partenaires côté visiteurs.
	 * Distinct de [bandstage_partenaires] qui gère le Studio (musiciens).
	 *
	 * @param array $atts Attributs du shortcode.
	 * @return string
	 */
	public function render_partenaires_public( array $atts = array() ): string {
		ob_start();
		self::maybe_inject_dynamic_css();
		include BANDSTAGE_PLUGIN_DIR . 'public/partials/bandstage-partenaires-public.php';
		return ob_get_clean();
	}

	// -----------------------------------------------------------------------
	// Barre d'admin WP
	// -----------------------------------------------------------------------

	/**
	 * Masque la barre d'administration pour les subscribers (membres BandStage).
	 *
	 * @param bool $show Valeur courante.
	 * @return bool
	 */
	/**
	 * Masque la barre admin WP pour tous sauf les administrateurs.
	 * Les Auteurs (musiciens) utilisent le Studio front-end, pas le WP Admin.
	 */
	public function maybe_hide_admin_bar( bool $show ): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		// Seuls les admins WP voient la barre d'admin.
		return current_user_can( 'manage_options' );
	}
}
