<?php
/**
 * Désinstallation de BandStage.
 *
 * Exécuté uniquement lorsque l'utilisateur supprime l'extension depuis WP Admin.
 * Supprime toutes les options et tables créées par le plugin.
 *
 * @package BandStage
 */

// Sécurité — ce fichier ne doit être appelé que par WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// -------------------------------------------------------------------------
// 1. Supprimer toutes les options (wp_options).
// -------------------------------------------------------------------------
$wpdb->query(
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE 'bs\_%'"
);

// -------------------------------------------------------------------------
// 2. Supprimer les métadonnées utilisateurs (wp_usermeta).
// -------------------------------------------------------------------------
$wpdb->query(
	"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'bs\_%'"
);

// -------------------------------------------------------------------------
// 3. Supprimer la table des messages Tchache.
// -------------------------------------------------------------------------
$table = $wpdb->prefix . 'bandstage_messages';
$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

// -------------------------------------------------------------------------
// 4. Supprimer les tâches CRON planifiées.
// -------------------------------------------------------------------------
$cron_hooks = array(
	'bandstage_send_concert_notifications',
	'bandstage_cleanup_tchache_spam',
);

foreach ( $cron_hooks as $hook ) {
	$timestamp = wp_next_scheduled( $hook );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, $hook );
	}
	wp_clear_scheduled_hook( $hook );
}

// -------------------------------------------------------------------------
// 5. Vider le cache d'objet si disponible.
// -------------------------------------------------------------------------
wp_cache_flush();
