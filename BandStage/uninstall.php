<?php
/**
 * Nettoyage complet à la désinstallation du plugin BandStage.
 * Supprime : tables DB, options, pages, meta, CPTs.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// -------------------------------------------------------------------------
// 1. Tables personnalisées
// -------------------------------------------------------------------------
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_messages" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_notifications" );

// -------------------------------------------------------------------------
// 2. Options
// -------------------------------------------------------------------------
$options = [
  'bs_band_name', 'bs_band_tagline', 'bs_band_city', 'bs_band_email',
  'bs_bg_color_start', 'bs_bg_color_end', ' bs_accent_color', 'bs_cream_color',
  'bs_ticker_enabled', 'bs_ticker_source', 'bs_ticker_items', 'bs_ticker_speed',
  'bs_tchache_enabled', 'bs_tchache_moderation', 'bs_tchache_max_length',
  'bs_page_accueil', 'bs_page_tchache', 'bs_page_profil',
  'bs_page_studio', 'bs_page_partenaires', 'bs_page_groupe',
];

for ( $i = 1; $i <= 6; $i++ ) {
  $options[] = "bs_box_{$i}_title";
  $options[] = "bs_box_{$i}_link";
  $options[] = "bs_box_{$i}_icon";
}

foreach ( $options as $opt ) {
  delete_option( $opt );
}

// -------------------------------------------------------------------------
// 3. Pages BandStage (trash + suppression définitive)
// -------------------------------------------------------------------------
$page_options = [ 'bs_page_accueil', 'bs_page_tchache', 'bs_page_profil',
                  'bs_page_studio',  'bs_page_partenaires', 'bs_page_groupe' ];

// Ces options ont déjà été supprimées, donc on cherche directement les pages.
$pages = get_posts( [
  'post_type'      => 'page',
  'post_status'    => 'any',
  'posts_per_page' => -1,
  's'              => 'BandStage —',
  'fields'         => 'ids',
] );

foreach ( $pages as $page_id ) {
  wp_delete_post( $page_id, true );
}

// -------------------------------------------------------------------------
// 4. CPT : bs_news, bs_partenaire, bs_band_member
// -------------------------------------------------------------------------
$cpts = [ 'bs_news', 'bs_partenaire', 'bs_band_member' ];

foreach ( $cpts as $cpt ) {
  $posts = get_posts( [
    'post_type'      => $cpt,
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'fields'         => 'ids',
    'no_found_rows'  => true,
  ] );
  foreach ( $posts as $post_id ) {
    wp_delete_post( $post_id, true );
  }
}

// -------------------------------------------------------------------------
// 5. Taxonomie bs_type_partenaire
// -------------------------------------------------------------------------
$terms = get_terms( [ 'taxonomy' => 'bs_type_partenaire', 'hide_empty' => false ] );
if ( ! is_wp_error( $terms ) ) {
  foreach ( $terms as $term ) {
    wp_delete_term( $term->term_id, 'bs_type_partenaire' );
  }
}

// -------------------------------------------------------------------------
// 6. User meta
// -------------------------------------------------------------------------
$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => 'bs_bio' ] );
$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => 'bs_instrument' ] );
$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => 'bs_city' ] );

flush_rewrite_rules();
