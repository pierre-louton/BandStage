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
    // AJAX handlers — implémentés dans la prochaine tâche
    // -------------------------------------------------------------------------

    public function ajax_save(): void {}
    public function ajax_delete(): void {}
    public function ajax_style_save(): void {}
    public function ajax_style_delete(): void {}
}
