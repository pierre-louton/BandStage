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
  'bs_bg_color_start', 'bs_bg_color_end', 'bs_accent_color', 'bs_cream_color',
  'bs_ticker_enabled', 'bs_ticker_source', 'bs_ticker_items', 'bs_ticker_speed',
  'bs_tchache_enabled', 'bs_tchache_moderation', 'bs_tchache_max_length',
  'bs_page_accueil', 'bs_page_tchache', 'bs_page_profil',
  'bs_page_studio', 'bs_page_partenaires', 'bs_page_concerts', 'bs_page_groupe', 'bs_page_references',
];

for ( $i = 1; $i <= 6; $i++ ) {
  $options[] = "bs_box_{$i}_title";
  $options[] = "bs_box_{$i}_link";
  $options[] = "bs_box_{$i}_icon";
}

// -------------------------------------------------------------------------
// 3. Pages créées à l'activation — collect IDs before deleting options
// -------------------------------------------------------------------------
$page_options = [ 'bs_page_accueil', 'bs_page_tchache', 'bs_page_profil',
                  'bs_page_studio',  'bs_page_partenaires', 'bs_page_concerts', 'bs_page_groupe', 'bs_page_references' ];

$page_ids_to_delete = [];
foreach ( $page_options as $opt ) {
  $id = (int) get_option( $opt );
  if ( $id > 0 ) {
    $page_ids_to_delete[] = $id;
  }
}

// 2. Options générales
foreach ( $options as $opt ) {
  delete_option( $opt );
}
foreach ( $page_options as $opt ) {
  delete_option( $opt );
}

// Delete pages by exact ID
foreach ( $page_ids_to_delete as $page_id ) {
  wp_delete_post( $page_id, true );
}

// -------------------------------------------------------------------------
// 4. Tables concerts & partenaires
// -------------------------------------------------------------------------
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_concert_partenaires" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_concerts" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_partenaires" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_partenaire_types" );

// -------------------------------------------------------------------------
// 5b. Tables répertoire
// -------------------------------------------------------------------------
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_rep_ref" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_repertoire" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_references" );

// -------------------------------------------------------------------------
// 5. Logos uploadés
// -------------------------------------------------------------------------
$upload_dir    = wp_upload_dir();
$bandstage_dir = trailingslashit( $upload_dir['basedir'] ) . 'bandstage';
$logos_dir     = $bandstage_dir . '/logos';
if ( is_dir( $logos_dir ) ) {
    array_map( 'unlink', glob( $logos_dir . '/*' ) ?: [] );
    rmdir( $logos_dir );
}
if ( is_dir( $bandstage_dir ) ) {
    @rmdir( $bandstage_dir );
}

// -------------------------------------------------------------------------
// 6. User meta
// -------------------------------------------------------------------------
$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => 'bs_bio' ] );
$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => 'bs_instrument' ] );
$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => 'bs_city' ] );

flush_rewrite_rules();
