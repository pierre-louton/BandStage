<?php
/**
 * Déclaration des Custom Post Types et taxonomies BandStage.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Admin;

defined( 'ABSPATH' ) || exit;

class PostTypes {

	public static function register_all(): void {
		self::register_news();
		self::register_band_member();
	}

	// -------------------------------------------------------------------------
	// bs_news — Actualités (titre → ticker, contenu → Humeurs)
	// -------------------------------------------------------------------------

	private static function register_news(): void {
		register_post_type( 'bs_news', [
			'labels' => [
				'name'               => __( 'Actualités', 'bandstage' ),
				'singular_name'      => __( 'Actualité', 'bandstage' ),
				'add_new_item'       => __( 'Ajouter une actualité', 'bandstage' ),
				'edit_item'          => __( 'Modifier l\'actualité', 'bandstage' ),
				'search_items'       => __( 'Rechercher', 'bandstage' ),
				'not_found'          => __( 'Aucune actualité', 'bandstage' ),
			],
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'bandstage',
			'supports'      => [ 'title', 'editor', 'author' ],
			'capability_type' => 'post',
			'map_meta_cap'  => true,
			'rewrite'       => false,
		] );
	}

	// -------------------------------------------------------------------------
	// bs_band_member — Membres du lineup du groupe (PUBLIC)
	// -------------------------------------------------------------------------

	private static function register_band_member(): void {
		register_post_type( 'bs_band_member', [
			'labels' => [
				'name'          => __( 'Membres du groupe', 'bandstage' ),
				'singular_name' => __( 'Membre du groupe', 'bandstage' ),
				'add_new_item'  => __( 'Ajouter un membre', 'bandstage' ),
				'edit_item'     => __( 'Modifier le membre', 'bandstage' ),
				'not_found'     => __( 'Aucun membre', 'bandstage' ),
			],
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'bandstage',
			'supports'        => [ 'title', 'thumbnail', 'page-attributes' ],
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'rewrite'         => false,
			'menu_position'   => 5,
		] );
	}

}
