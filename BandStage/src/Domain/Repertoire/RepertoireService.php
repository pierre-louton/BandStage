<?php
/**
 * Service Répertoire — CRUD via $wpdb (bandstage_repertoire + bandstage_references).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Repertoire;

use BandStage\Core\Config;

defined( 'ABSPATH' ) || exit;

class RepertoireService {

    public function register_ajax(): void {
        add_action( 'wp_ajax_bs_morceau_save',   [ $this, 'ajax_save' ] );
        add_action( 'wp_ajax_bs_morceau_delete', [ $this, 'ajax_delete' ] );
        add_action( 'wp_ajax_bs_style_save',     [ $this, 'ajax_style_save' ] );
        add_action( 'wp_ajax_bs_style_delete',   [ $this, 'ajax_style_delete' ] );
    }

    // -------------------------------------------------------------------------
    // Lecture
    // -------------------------------------------------------------------------

    /** @return Morceau[]  Tous les morceaux, triés par nom_artiste ASC, avec style_names. */
    public function get_all(): array {
        global $wpdb;
        $tr   = Config::table_repertoire();
        $tpiv = Config::table_rep_ref();
        $tref = Config::table_references();

        $rows = $wpdb->get_results(
            "SELECT r.*, COALESCE(GROUP_CONCAT(s.nom_style ORDER BY s.nom_style SEPARATOR ', '), '') AS style_names
             FROM {$tr} r
             LEFT JOIN {$tpiv} rr ON rr.repertoire_id = r.id
             LEFT JOIN {$tref} s  ON s.id = rr.reference_id
             GROUP BY r.id
             ORDER BY r.nom_artiste ASC, r.nom_morceau ASC"
        );

        return array_map( fn( $row ) => Morceau::from_db_row( $row ), $rows ?: [] );
    }

    /** Morceau complet avec style_ids (pour le formulaire d'édition). */
    public function get( int $id ): ?Morceau {
        global $wpdb;
        $tr   = Config::table_repertoire();
        $tpiv = Config::table_rep_ref();

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tr} WHERE id = %d", $id ) );
        if ( ! $row ) {
            return null;
        }

        $ids = $wpdb->get_col(
            $wpdb->prepare( "SELECT reference_id FROM {$tpiv} WHERE repertoire_id = %d", $id )
        );

        return Morceau::from_db_row( $row, array_map( 'intval', $ids ?: [] ) );
    }

    /**
     * Morceaux groupés par style pour la vue publique.
     * @return array<string, array{label:string, image_url:string, items:Morceau[]}>
     */
    public function get_grouped_by_style(): array {
        global $wpdb;
        $tr   = Config::table_repertoire();
        $tpiv = Config::table_rep_ref();
        $tref = Config::table_references();

        $rows = $wpdb->get_results(
            "SELECT r.*, s.id AS style_id, s.nom_style, s.image_url AS style_image_url
             FROM {$tr} r
             LEFT JOIN {$tpiv} rr ON rr.repertoire_id = r.id
             LEFT JOIN {$tref} s  ON s.id = rr.reference_id
             ORDER BY s.nom_style ASC, r.nom_artiste ASC, r.nom_morceau ASC"
        );

        $grouped  = [];
        $seen_ids = [];

        foreach ( $rows ?: [] as $row ) {
            $key = $row->style_id ? 'style_' . $row->style_id : '_none';

            if ( ! isset( $grouped[ $key ] ) ) {
                $grouped[ $key ] = [
                    'label'     => $row->nom_style ?: __( 'Sans style', 'bandstage' ),
                    'image_url' => (string) ( $row->style_image_url ?? '' ),
                    'items'     => [],
                ];
            }

            $item_key = $key . '_' . $row->id;
            if ( ! isset( $seen_ids[ $item_key ] ) ) {
                $grouped[ $key ]['items'][] = Morceau::from_db_row( $row );
                $seen_ids[ $item_key ]      = true;
            }
        }

        // Déplacer _none en dernier
        if ( isset( $grouped['_none'] ) ) {
            $none = $grouped['_none'];
            unset( $grouped['_none'] );
            $grouped['_none'] = $none;
        }

        return $grouped;
    }

    /** @return Style[] */
    public function get_styles(): array {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT * FROM " . Config::table_references() . " ORDER BY nom_style ASC"
        );
        return array_map( [ Style::class, 'from_db_row' ], $rows ?: [] );
    }

    // -------------------------------------------------------------------------
    // AJAX — Morceau
    // -------------------------------------------------------------------------

    public function ajax_save(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $table = Config::table_repertoire();
        $pivot = Config::table_rep_ref();

        $id            = absint( $_POST['morceau_id'] ?? 0 );
        $nom_artiste   = sanitize_text_field( wp_unslash( $_POST['nom_artiste']   ?? '' ) );
        $nom_morceau   = sanitize_text_field( wp_unslash( $_POST['nom_morceau']   ?? '' ) );
        $remarque      = sanitize_textarea_field( wp_unslash( $_POST['remarque']      ?? '' ) );
        $icone_artiste = sanitize_text_field( wp_unslash( $_POST['icone_artiste'] ?? '' ) );
        $style_ids     = array_map( 'absint', (array) ( $_POST['style_ids'] ?? [] ) );

        if ( empty( $nom_artiste ) ) {
            wp_send_json_error( [ 'message' => __( 'Le nom de l\'artiste est obligatoire.', 'bandstage' ) ] );
        }
        if ( empty( $nom_morceau ) ) {
            wp_send_json_error( [ 'message' => __( 'Le nom du morceau est obligatoire.', 'bandstage' ) ] );
        }

        $data = [
            'nom_artiste'   => $nom_artiste,
            'nom_morceau'   => $nom_morceau,
            'remarque'      => $remarque,
            'icone_artiste' => $icone_artiste,
        ];

        if ( $id ) {
            $data['updated_at'] = current_time( 'mysql' );
            $wpdb->update( $table, $data, [ 'id' => $id ] );
        } else {
            $wpdb->insert( $table, $data );
            $id = (int) $wpdb->insert_id;
            if ( ! $id ) {
                wp_send_json_error( [ 'message' => __( 'Erreur lors de l\'enregistrement.', 'bandstage' ) ] );
            }
        }

        // Mettre à jour le pivot
        $wpdb->delete( $pivot, [ 'repertoire_id' => $id ] );
        foreach ( $style_ids as $sid ) {
            if ( $sid > 0 ) {
                $wpdb->insert( $pivot, [ 'repertoire_id' => $id, 'reference_id' => $sid ] );
            }
        }

        wp_send_json_success( [
            'message'  => __( 'Morceau enregistré.', 'bandstage' ),
            'redirect' => \BandStage\Frontend\Shortcodes::references_url( 'list' ),
        ] );
    }

    public function ajax_delete(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $id = absint( $_POST['morceau_id'] ?? 0 );

        if ( ! $id ) {
            wp_send_json_error( [ 'message' => __( 'Identifiant manquant.', 'bandstage' ) ] );
        }

        $wpdb->delete( Config::table_rep_ref(),    [ 'repertoire_id' => $id ] );
        $wpdb->delete( Config::table_repertoire(), [ 'id'            => $id ] );

        wp_send_json_success( [ 'message' => __( 'Morceau supprimé.', 'bandstage' ) ] );
    }

    // -------------------------------------------------------------------------
    // AJAX — Style
    // -------------------------------------------------------------------------

    public function ajax_style_save(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $table     = Config::table_references();
        $nom_style = sanitize_text_field( wp_unslash( $_POST['nom_style'] ?? '' ) );
        $image_url = esc_url_raw( wp_unslash( $_POST['image_url'] ?? '' ) );

        if ( empty( $nom_style ) ) {
            wp_send_json_error( [ 'message' => __( 'Le nom du style est obligatoire.', 'bandstage' ) ] );
        }

        $result = $wpdb->insert(
            $table,
            [ 'nom_style' => $nom_style, 'image_url' => $image_url ],
            [ '%s', '%s' ]
        );

        if ( false === $result ) {
            wp_send_json_error( [ 'message' => $wpdb->last_error ?: __( 'Ce style existe déjà.', 'bandstage' ) ] );
        }

        $style_id = (int) $wpdb->insert_id;

        wp_send_json_success( [
            'message'   => __( 'Style ajouté.', 'bandstage' ),
            'style_id'  => $style_id,
            'nom_style' => $nom_style,
            'image_url' => $image_url,
        ] );
    }

    public function ajax_style_delete(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $id = absint( $_POST['style_id'] ?? 0 );

        if ( ! $id ) {
            wp_send_json_error( [ 'message' => __( 'Identifiant manquant.', 'bandstage' ) ] );
        }

        $wpdb->delete( Config::table_rep_ref(),   [ 'reference_id' => $id ] );
        $wpdb->delete( Config::table_references(), [ 'id'          => $id ] );

        wp_send_json_success( [ 'message' => __( 'Style supprimé.', 'bandstage' ) ] );
    }
}
