<?php
/**
 * Service Partenaires — CRUD via $wpdb (tables bandstage_partenaires + bandstage_partenaire_types).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Partenaires;

use BandStage\Core\Config;
use BandStage\Domain\Media\LogoUploader;

defined( 'ABSPATH' ) || exit;

class PartenaireService {

    public function register_ajax(): void {
        add_action( 'wp_ajax_bs_partenaire_save',        [ $this, 'ajax_save' ] );
        add_action( 'wp_ajax_bs_partenaire_delete',      [ $this, 'ajax_delete' ] );
        add_action( 'wp_ajax_bs_partenaire_type_save',   [ $this, 'ajax_type_save' ] );
        add_action( 'wp_ajax_bs_partenaire_type_delete', [ $this, 'ajax_type_delete' ] );
    }

    // -------------------------------------------------------------------------
    // Lecture
    // -------------------------------------------------------------------------

    /** @return Partenaire[] */
    public function get_all(): array {
        global $wpdb;
        $tp = Config::table_partenaires();
        $tt = Config::table_partenaire_types();

        $rows = $wpdb->get_results(
            "SELECT p.*, t.name AS type_name, t.slug AS type_slug, t.icon AS type_icon
             FROM {$tp} p
             LEFT JOIN {$tt} t ON t.id = p.type_id
             ORDER BY p.name ASC"
        );

        return array_map( [ Partenaire::class, 'from_db_row' ], $rows ?: [] );
    }

    public function get( int $id ): ?Partenaire {
        global $wpdb;
        $tp = Config::table_partenaires();
        $tt = Config::table_partenaire_types();

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT p.*, t.name AS type_name, t.slug AS type_slug, t.icon AS type_icon
             FROM {$tp} p
             LEFT JOIN {$tt} t ON t.id = p.type_id
             WHERE p.id = %d",
            $id
        ) );

        return $row ? Partenaire::from_db_row( $row ) : null;
    }

    /**
     * Retourne les partenaires groupés par type pour la vue publique.
     * @return array<string, array{label:string, icon:string, items:Partenaire[]}>
     */
    public function get_grouped_by_type(): array {
        $grouped = [];
        foreach ( $this->get_all() as $p ) {
            $key = $p->type_slug ?: '_none';
            if ( ! isset( $grouped[ $key ] ) ) {
                $grouped[ $key ] = [
                    'label' => $p->type_name ?: __( 'Autres', 'bandstage' ),
                    'icon'  => $p->type_icon,
                    'items' => [],
                ];
            }
            $grouped[ $key ]['items'][] = $p;
        }
        return $grouped;
    }

    /** @return PartenaireType[] */
    public function get_types(): array {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT * FROM " . Config::table_partenaire_types() . " ORDER BY name ASC"
        );
        return array_map( [ PartenaireType::class, 'from_db_row' ], $rows ?: [] );
    }

    // -------------------------------------------------------------------------
    // AJAX — Sauvegarder un partenaire
    // -------------------------------------------------------------------------

    public function ajax_save(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $table = Config::table_partenaires();

        $id          = absint( $_POST['partenaire_id'] ?? 0 );
        $name        = sanitize_text_field( wp_unslash( $_POST['name']        ?? '' ) );
        $description = sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) );
        $type_id     = absint( $_POST['type_id'] ?? 0 ) ?: null;
        $website     = esc_url_raw( wp_unslash( $_POST['website']     ?? '' ) );
        $email       = sanitize_email( wp_unslash( $_POST['email']       ?? '' ) );
        $phone       = sanitize_text_field( wp_unslash( $_POST['phone']       ?? '' ) );
        $numero      = sanitize_text_field( wp_unslash( $_POST['numero']      ?? '' ) );
        $nom_voie    = sanitize_text_field( wp_unslash( $_POST['nom_voie']    ?? '' ) );
        $code_postal = sanitize_text_field( wp_unslash( $_POST['code_postal'] ?? '' ) );
        $ville       = sanitize_text_field( wp_unslash( $_POST['ville']       ?? '' ) );

        if ( empty( $name ) ) {
            wp_send_json_error( [ 'message' => __( 'Le nom est obligatoire.', 'bandstage' ) ] );
        }

        // Logo
        $logo_path = '';
        if ( $id ) {
            $logo_path = (string) $wpdb->get_var(
                $wpdb->prepare( "SELECT logo_path FROM {$table} WHERE id = %d", $id )
            );
        }

        if ( ! empty( $_FILES['logo']['name'] ) && UPLOAD_ERR_OK === ( $_FILES['logo']['error'] ?? -1 ) ) {
            $result = LogoUploader::upload( $_FILES['logo'] );
            if ( is_wp_error( $result ) || empty( $result ) ) {
                wp_send_json_error( [ 'message' => is_wp_error( $result ) ? $result->get_error_message() : __( 'Échec de l\'upload.', 'bandstage' ) ] );
            }
            if ( $logo_path ) {
                LogoUploader::delete( $logo_path );
            }
            $logo_path = $result;
        } elseif ( 'remove' === ( $_POST['logo_action'] ?? '' ) ) {
            LogoUploader::delete( $logo_path );
            $logo_path = '';
        }

        $data = [
            'name'        => $name,
            'description' => $description,
            'type_id'     => $type_id,
            'website'     => $website,
            'email'       => $email,
            'phone'       => $phone,
            'numero'      => $numero,
            'nom_voie'    => $nom_voie,
            'code_postal' => $code_postal,
            'ville'       => $ville,
            'logo_path'   => $logo_path,
        ];

        if ( $id ) {
            $data['updated_at'] = current_time( 'mysql' );
            $wpdb->update( $table, $data, [ 'id' => $id ] );
        } else {
            $wpdb->insert( $table, $data );
        }

        wp_send_json_success( [
            'message'  => __( 'Partenaire enregistré.', 'bandstage' ),
            'redirect' => \BandStage\Frontend\Shortcodes::partenaires_url( 'list' ),
        ] );
    }

    // -------------------------------------------------------------------------
    // AJAX — Supprimer un partenaire
    // -------------------------------------------------------------------------

    public function ajax_delete(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $table = Config::table_partenaires();
        $id    = absint( $_POST['partenaire_id'] ?? 0 );

        if ( ! $id ) {
            wp_send_json_error( [ 'message' => __( 'Identifiant manquant.', 'bandstage' ) ] );
        }

        $logo_path = (string) $wpdb->get_var(
            $wpdb->prepare( "SELECT logo_path FROM {$table} WHERE id = %d", $id )
        );
        LogoUploader::delete( $logo_path );

        // Supprimer les lignes pivot
        $wpdb->delete( Config::table_concert_partenaires(), [ 'partenaire_id' => $id ] );
        $wpdb->delete( $table, [ 'id' => $id ] );

        wp_send_json_success( [ 'message' => __( 'Partenaire supprimé.', 'bandstage' ) ] );
    }

    // -------------------------------------------------------------------------
    // AJAX — Sauvegarder un type (manage_options)
    // -------------------------------------------------------------------------

    public function ajax_type_save(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $table = Config::table_partenaire_types();

        $id   = absint( $_POST['type_id'] ?? 0 );
        $name = sanitize_text_field( wp_unslash( $_POST['type_name'] ?? '' ) );
        $icon = sanitize_text_field( wp_unslash( $_POST['type_icon'] ?? '' ) );
        $slug = sanitize_title( $name );

        if ( empty( $name ) ) {
            wp_send_json_error( [ 'message' => __( 'Nom du type obligatoire.', 'bandstage' ) ] );
        }

        $data = [ 'name' => $name, 'slug' => $slug, 'icon' => $icon ];

        if ( $id ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
        } else {
            // Vérifier doublon de slug
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s", $slug ) );
            if ( $exists ) {
                wp_send_json_error( [ 'message' => __( 'Ce type existe déjà.', 'bandstage' ) ] );
            }
            $wpdb->insert( $table, $data );
            $id = $wpdb->insert_id;
        }

        wp_send_json_success( [
            'message' => __( 'Type enregistré.', 'bandstage' ),
            'type_id' => $id,
            'slug'    => $slug,
            'name'    => $name,
            'icon'    => $icon,
        ] );
    }

    // -------------------------------------------------------------------------
    // AJAX — Supprimer un type (manage_options)
    // -------------------------------------------------------------------------

    public function ajax_type_delete(): void {
        check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Accès refusé.', 'bandstage' ) ], 403 );
        }

        global $wpdb;
        $id = absint( $_POST['type_id'] ?? 0 );

        if ( ! $id ) {
            wp_send_json_error( [ 'message' => __( 'Identifiant manquant.', 'bandstage' ) ] );
        }

        // Les partenaires liés auront type_id mis à NULL (SET NULL en DB)
        $wpdb->update(
            Config::table_partenaires(),
            [ 'type_id' => null ],
            [ 'type_id' => $id ]
        );
        $wpdb->delete( Config::table_partenaire_types(), [ 'id' => $id ] );

        wp_send_json_success( [ 'message' => __( 'Type supprimé.', 'bandstage' ) ] );
    }
}
