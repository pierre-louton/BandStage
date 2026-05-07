<?php
/**
 * Service Concerts — CRUD via $wpdb.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Concerts;

use BandStage\Core\Config;

defined( 'ABSPATH' ) || exit;

class ConcertService {

    public function register_ajax(): void {
        add_action( 'wp_ajax_bs_concert_save',   [ $this, 'ajax_save' ] );
        add_action( 'wp_ajax_bs_concert_delete', [ $this, 'ajax_delete' ] );
    }

    // -------------------------------------------------------------------------
    // Lecture
    // -------------------------------------------------------------------------

    /** @return Concert[]  Concerts à venir (date_debut >= aujourd'hui), triés ASC. */
    public function get_upcoming(): array {
        global $wpdb;
        $table = Config::table_concerts();
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE date_debut >= %s ORDER BY date_debut ASC",
                current_time( 'Y-m-d' )
            )
        );
        return array_map( fn( $r ) => Concert::from_db_row( $r ), $rows ?: [] );
    }

    /** @return Concert[]  Tous les concerts, triés par date_debut DESC. */
    public function get_all(): array {
        global $wpdb;
        $table = Config::table_concerts();
        $rows  = $wpdb->get_results(
            "SELECT * FROM {$table} ORDER BY date_debut DESC"
        );
        return array_map( fn( $r ) => Concert::from_db_row( $r ), $rows ?: [] );
    }

    /** Concert complet avec partenaire_ids. */
    public function get( int $id ): ?Concert {
        global $wpdb;
        $table = Config::table_concerts();
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        if ( ! $row ) {
            return null;
        }
        $pivot = Config::table_concert_partenaires();
        $ids   = $wpdb->get_col(
            $wpdb->prepare( "SELECT partenaire_id FROM {$pivot} WHERE concert_id = %d", $id )
        );
        return Concert::from_db_row( $row, array_map( 'intval', $ids ?: [] ) );
    }

    // -------------------------------------------------------------------------
    // AJAX — Sauvegarder
    // -------------------------------------------------------------------------

    public function ajax_save(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $table = Config::table_concerts();
        $pivot = Config::table_concert_partenaires();

        $id          = absint( $_POST['concert_id'] ?? 0 );
        $titre       = sanitize_text_field( wp_unslash( $_POST['titre']       ?? '' ) );
        $date_debut  = sanitize_text_field( wp_unslash( $_POST['date_debut']  ?? '' ) );
        $date_fin    = sanitize_text_field( wp_unslash( $_POST['date_fin']    ?? '' ) ) ?: null;
        $horaires    = sanitize_text_field( wp_unslash( $_POST['horaires']    ?? '' ) );
        $nom_lieu    = sanitize_text_field( wp_unslash( $_POST['nom_lieu']    ?? '' ) );
        $numero      = sanitize_text_field( wp_unslash( $_POST['numero']      ?? '' ) );
        $nom_voie    = sanitize_text_field( wp_unslash( $_POST['nom_voie']    ?? '' ) );
        $code_postal = sanitize_text_field( wp_unslash( $_POST['code_postal'] ?? '' ) );
        $ville       = sanitize_text_field( wp_unslash( $_POST['ville']       ?? '' ) );
        $partenaire_ids = array_map( 'absint', (array) ( $_POST['partenaire_ids'] ?? [] ) );

        if ( empty( $titre ) ) {
            wp_send_json_error( [ 'message' => __( 'Le titre est obligatoire.', 'bandstage' ) ] );
        }
        if ( empty( $date_debut ) ) {
            wp_send_json_error( [ 'message' => __( 'La date de début est obligatoire.', 'bandstage' ) ] );
        }
        if ( ! \DateTime::createFromFormat( 'Y-m-d', $date_debut ) ) {
            wp_send_json_error( [ 'message' => __( 'Format de date de début invalide.', 'bandstage' ) ] );
        }
        if ( ! empty( $date_fin ) && ! \DateTime::createFromFormat( 'Y-m-d', $date_fin ) ) {
            wp_send_json_error( [ 'message' => __( 'Format de date de fin invalide.', 'bandstage' ) ] );
        }

        $data = [
            'titre'       => $titre,
            'date_debut'  => $date_debut,
            'date_fin'    => $date_fin,
            'horaires'    => $horaires,
            'nom_lieu'    => $nom_lieu,
            'numero'      => $numero,
            'nom_voie'    => $nom_voie,
            'code_postal' => $code_postal,
            'ville'       => $ville,
        ];

        if ( $id ) {
            $data['updated_at'] = current_time( 'mysql' );
            $wpdb->update( $table, $data, [ 'id' => $id ] );
        } else {
            $wpdb->insert( $table, $data );
            $id = (int) $wpdb->insert_id;
        }

        // Mettre à jour la table pivot
        $wpdb->delete( $pivot, [ 'concert_id' => $id ] );
        foreach ( $partenaire_ids as $pid ) {
            if ( $pid > 0 ) {
                $wpdb->insert( $pivot, [ 'concert_id' => $id, 'partenaire_id' => $pid ] );
            }
        }

        wp_send_json_success( [
            'message'  => __( 'Concert enregistré.', 'bandstage' ),
            'redirect' => \BandStage\Frontend\Shortcodes::concerts_url( 'list' ),
        ] );
    }

    // -------------------------------------------------------------------------
    // AJAX — Supprimer
    // -------------------------------------------------------------------------

    public function ajax_delete(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $id = absint( $_POST['concert_id'] ?? 0 );

        if ( ! $id ) {
            wp_send_json_error( [ 'message' => __( 'Identifiant manquant.', 'bandstage' ) ] );
        }

        $wpdb->delete( Config::table_concert_partenaires(), [ 'concert_id' => $id ] );
        $wpdb->delete( Config::table_concerts(), [ 'id' => $id ] );

        wp_send_json_success( [ 'message' => __( 'Concert supprimé.', 'bandstage' ) ] );
    }
}
