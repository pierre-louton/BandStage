<?php
/**
 * Ressources front-end BandStage.
 *
 * CSS dynamique injecté DANS le shortcode (pas via wp_head).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Frontend;

defined( 'ABSPATH' ) || exit;

class Assets {

	/** Pages nécessitant l'interface Studio (wp_enqueue_media). */
	private array $studio_pages = [ 'bs_page_studio', 'bs_page_partenaires', 'bs_page_groupe', 'bs_page_concerts' ];

	public function enqueue(): void {
		if ( ! $this->is_bandstage_page() ) {
			return;
		}

		// Google Fonts
		wp_enqueue_style(
			'bandstage-fonts',
			'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Oswald:wght@600&display=swap',
			[],
			null
		);

		// CSS public
		wp_enqueue_style(
			'bandstage-public',
			BANDSTAGE_PLUGIN_URL . 'assets/css/public.css',
			[ 'bandstage-fonts' ],
			BANDSTAGE_VERSION
		);

		// JS public
		wp_enqueue_script(
			'bandstage-public',
			BANDSTAGE_PLUGIN_URL . 'assets/js/public.js',
			[],
			BANDSTAGE_VERSION,
			true
		);

		wp_localize_script( 'bandstage-public', 'BsPublic', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( BANDSTAGE_NONCE ),
			'i18n'    => [
				'posting'   => __( 'Envoi…', 'bandstage' ),
				'deleting'  => __( 'Suppression…', 'bandstage' ),
				'confirm'   => __( 'Confirmer la suppression ?', 'bandstage' ),
				'error'     => __( 'Une erreur est survenue.', 'bandstage' ),
			],
		] );

		// Pages Studio : CSS + JS + wp.media
		if ( $this->is_studio_page() ) {
			wp_enqueue_style(
				'bandstage-studio',
				BANDSTAGE_PLUGIN_URL . 'assets/css/studio.css',
				[ 'bandstage-public' ],
				BANDSTAGE_VERSION
			);
			wp_enqueue_script(
				'bandstage-studio',
				BANDSTAGE_PLUGIN_URL . 'assets/js/studio.js',
				[ 'bandstage-public', 'jquery' ],
				BANDSTAGE_VERSION,
				true
			);
			wp_enqueue_media();
		}
	}

	// -------------------------------------------------------------------------
	// CSS dynamique (inline dans le shortcode — règle critique)
	// -------------------------------------------------------------------------

	public static function maybe_inject_dynamic_css(): void {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;

		$color_start = get_option( 'bs_bg_color_start', '#1535A8' );
		$color_end   = get_option( 'bs_bg_color_end',   '#020828' );
		$accent      = get_option( 'bs_accent_color',   '#D4A820' );
		$cream       = get_option( 'bs_cream_color',    '#FAF6EB' );

		// Convertir accent hex → rgb pour --bs-accent-rgb
		[ $r, $g, $b ] = self::hex_to_rgb( $accent );

		// Accent light (10 % opacité)
		$accent_light = "rgba({$r},{$g},{$b},0.10)";

		printf(
			'<style>.bs-wrap,.bs-tc-wrap,.bs-pr-wrap,.bs-gr-wrap{background:linear-gradient(160deg,%s 0%%,%s 100%%);--bs-accent:%s;--bs-accent-rgb:%s,%s,%s;--bs-accent-light:%s;--bs-cream:%s;}</style>',
			esc_attr( $color_start ),
			esc_attr( $color_end ),
			esc_attr( $accent ),
			(int) $r, (int) $g, (int) $b,
			esc_attr( $accent_light ),
			esc_attr( $cream )
		);
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function is_bandstage_page(): bool {
		$page_options = [
			'bs_page_accueil', 'bs_page_tchache', 'bs_page_profil',
			'bs_page_studio',  'bs_page_partenaires', 'bs_page_groupe', 'bs_page_concerts',
		];
		$current_id = get_queried_object_id();
		foreach ( $page_options as $option ) {
			if ( (int) get_option( $option ) === $current_id ) {
				return true;
			}
		}

		// Fallback : la page n'est pas dans les options (créée manuellement,
		// option non synchronisée) — on détecte la présence d'un shortcode.
		$post = get_queried_object();
		if ( $post instanceof \WP_Post ) {
			$shortcodes = [
				'bandstage_homepage', 'bandstage_tchache', 'bandstage_profil',
				'bandstage_studio',   'bandstage_partenaires', 'bandstage_groupe', 'bandstage_concerts',
			];
			foreach ( $shortcodes as $tag ) {
				if ( has_shortcode( $post->post_content, $tag ) ) {
					return true;
				}
			}
		}

		return false;
	}

	private function is_studio_page(): bool {
		$current_id = get_queried_object_id();
		foreach ( $this->studio_pages as $option ) {
			if ( (int) get_option( $option ) === $current_id ) {
				return true;
			}
		}

		// Fallback : même logique que is_bandstage_page().
		$post = get_queried_object();
		if ( $post instanceof \WP_Post ) {
			$studio_shortcodes = [ 'bandstage_studio', 'bandstage_partenaires', 'bandstage_groupe', 'bandstage_concerts' ];
			foreach ( $studio_shortcodes as $tag ) {
				if ( has_shortcode( $post->post_content, $tag ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return array{0:int,1:int,2:int}
	 */
	private static function hex_to_rgb( string $hex ): array {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return [
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		];
	}
}
