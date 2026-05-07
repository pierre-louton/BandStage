# Concerts & Partenaires DB — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the CPT-based partenaires with 4 custom DB tables and add a full Concerts module (CRUD Studio + public view) linked to partenaires via a pivot table.

**Architecture:** All partenaire and concert data lives in `{$wpdb->prefix}bandstage_*` tables managed via `$wpdb`. Domain entities are hydrated from `stdClass` DB rows. Logo files are uploaded directly to `wp-content/uploads/bandstage/logos/` outside the WP media library.

**Tech Stack:** PHP 8.1, WordPress `$wpdb`, vanilla JS (FormData + fetch), no external dependencies.

---

## File Map

| Action | Path |
|--------|------|
| Modify | `BandStage/src/Core/Config.php` |
| Modify | `BandStage/src/Admin/PostTypes.php` |
| Modify | `BandStage/src/Core/Plugin.php` |
| Modify | `BandStage/config/routes.php` |
| Modify | `BandStage/uninstall.php` |
| Modify | `BandStage/src/Frontend/Shortcodes.php` |
| Modify | `BandStage/src/Frontend/Assets.php` |
| Create | `BandStage/src/Domain/Partenaires/PartenaireType.php` |
| Replace | `BandStage/src/Domain/Partenaires/Partenaire.php` |
| Replace | `BandStage/src/Domain/Partenaires/PartenaireService.php` |
| Create | `BandStage/src/Domain/Media/LogoUploader.php` |
| Create | `BandStage/src/Domain/Concerts/Concert.php` |
| Create | `BandStage/src/Domain/Concerts/ConcertService.php` |
| Replace | `BandStage/templates/public/partenaires-public.php` |
| Replace | `BandStage/templates/public/studio/partenaire-list.php` |
| Replace | `BandStage/templates/public/studio/partenaire-edit.php` |
| Create | `BandStage/templates/public/concerts-public.php` |
| Create | `BandStage/templates/public/studio/concert-list.php` |
| Create | `BandStage/templates/public/studio/concert-edit.php` |
| Modify | `BandStage/assets/js/studio.js` |
| Modify | `BandStage/assets/css/public.css` |
| Modify | `BandStage/assets/css/studio.css` |
| Modify | `BandStage/templates/admin/settings/tab-partenaires.php` |

---

## Task 1 — DB Tables (Config.php)

**Files:**
- Modify: `BandStage/src/Core/Config.php`

- [ ] **Remplacer `create_tables()` par la version complète avec les 4 nouvelles tables**

```php
public static function create_tables(): void {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql_messages = "CREATE TABLE IF NOT EXISTS " . self::table_messages() . " (
        id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id     BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        content     TEXT NOT NULL,
        status      ENUM('pending','approved','spam') NOT NULL DEFAULT 'pending',
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY status (status),
        KEY created_at (created_at)
    ) $charset_collate;";

    $sql_notifications = "CREATE TABLE IF NOT EXISTS " . self::table_notifications() . " (
        id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id     BIGINT(20) UNSIGNED NOT NULL,
        type        VARCHAR(64) NOT NULL,
        payload     JSON,
        read_at     DATETIME DEFAULT NULL,
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY read_at (read_at),
        KEY created_at (created_at)
    ) $charset_collate;";

    $sql_partenaire_types = "CREATE TABLE IF NOT EXISTS " . self::table_partenaire_types() . " (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name       VARCHAR(100) NOT NULL,
        slug       VARCHAR(100) NOT NULL,
        icon       VARCHAR(10) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY slug (slug)
    ) $charset_collate;";

    $sql_partenaires = "CREATE TABLE IF NOT EXISTS " . self::table_partenaires() . " (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        type_id     BIGINT UNSIGNED NULL DEFAULT NULL,
        name        VARCHAR(150) NOT NULL,
        description TEXT NOT NULL DEFAULT '',
        logo_path   VARCHAR(255) NOT NULL DEFAULT '',
        website     VARCHAR(255) NOT NULL DEFAULT '',
        email       VARCHAR(150) NOT NULL DEFAULT '',
        phone       VARCHAR(30)  NOT NULL DEFAULT '',
        numero      VARCHAR(10)  NOT NULL DEFAULT '',
        nom_voie    VARCHAR(150) NOT NULL DEFAULT '',
        code_postal VARCHAR(10)  NOT NULL DEFAULT '',
        ville       VARCHAR(100) NOT NULL DEFAULT '',
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY type_id (type_id),
        KEY name (name)
    ) $charset_collate;";

    $sql_concerts = "CREATE TABLE IF NOT EXISTS " . self::table_concerts() . " (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        titre       VARCHAR(200) NOT NULL,
        date_debut  DATE NOT NULL,
        date_fin    DATE NULL DEFAULT NULL,
        horaires    VARCHAR(100) NOT NULL DEFAULT '',
        nom_lieu    VARCHAR(150) NOT NULL DEFAULT '',
        numero      VARCHAR(10)  NOT NULL DEFAULT '',
        nom_voie    VARCHAR(150) NOT NULL DEFAULT '',
        code_postal VARCHAR(10)  NOT NULL DEFAULT '',
        ville       VARCHAR(100) NOT NULL DEFAULT '',
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY date_debut (date_debut)
    ) $charset_collate;";

    $sql_pivot = "CREATE TABLE IF NOT EXISTS " . self::table_concert_partenaires() . " (
        concert_id    BIGINT UNSIGNED NOT NULL,
        partenaire_id BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY (concert_id, partenaire_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql_messages );
    dbDelta( $sql_notifications );
    dbDelta( $sql_partenaire_types );
    dbDelta( $sql_partenaires );
    dbDelta( $sql_concerts );
    dbDelta( $sql_pivot );
}
```

- [ ] **Ajouter les 4 accesseurs de noms de tables** (après `table_notifications()`):

```php
public static function table_partenaire_types(): string {
    global $wpdb;
    return $wpdb->prefix . 'bandstage_partenaire_types';
}

public static function table_partenaires(): string {
    global $wpdb;
    return $wpdb->prefix . 'bandstage_partenaires';
}

public static function table_concerts(): string {
    global $wpdb;
    return $wpdb->prefix . 'bandstage_concerts';
}

public static function table_concert_partenaires(): string {
    global $wpdb;
    return $wpdb->prefix . 'bandstage_concert_partenaires';
}
```

- [ ] **Vérifier** : réactiver/désactiver le plugin dans WP Admin → Plugins, puis vérifier avec un client SQL (phpMyAdmin ou Adminer) que les 6 tables existent.

- [ ] **Commit**

```bash
git add BandStage/src/Core/Config.php
git commit -m "feat: add bandstage_partenaire_types, partenaires, concerts, concert_partenaires tables"
```

---

## Task 2 — PartenaireType entity

**Files:**
- Create: `BandStage/src/Domain/Partenaires/PartenaireType.php`

- [ ] **Créer le fichier**

```php
<?php
/**
 * Entité PartenaireType — type de partenaire (table bandstage_partenaire_types).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Partenaires;

defined( 'ABSPATH' ) || exit;

class PartenaireType {

    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $icon,
    ) {}

    public static function from_db_row( object $row ): self {
        return new self(
            id:   (int)    $row->id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            icon: (string) $row->icon,
        );
    }
}
```

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Partenaires/PartenaireType.php
git commit -m "feat: PartenaireType entity"
```

---

## Task 3 — LogoUploader

**Files:**
- Create: `BandStage/src/Domain/Media/LogoUploader.php`

- [ ] **Créer le fichier**

```php
<?php
/**
 * Upload de logo partenaire vers wp-content/uploads/bandstage/logos/.
 * Hors médiathèque WordPress.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Media;

defined( 'ABSPATH' ) || exit;

class LogoUploader {

    private const MAX_SIZE  = 2 * 1024 * 1024; // 2 Mo
    private const ALLOWED   = [ 'jpg', 'jpeg', 'png', 'webp', 'svg' ];
    private const SUBDIR    = 'bandstage/logos';

    /**
     * Déplace le fichier uploadé vers le dossier logos.
     *
     * @param array $file  Entrée de $_FILES (logo).
     * @return string|\WP_Error  Chemin relatif à wp_upload_dir()['basedir'], ex: "bandstage/logos/abc123.jpg"
     */
    public static function upload( array $file ): string|\WP_Error {
        if ( ! empty( $file['error'] ) ) {
            return new \WP_Error( 'upload_error', __( 'Erreur lors de l\'upload.', 'bandstage' ) );
        }

        if ( $file['size'] > self::MAX_SIZE ) {
            return new \WP_Error( 'file_too_large', __( 'Le fichier dépasse 2 Mo.', 'bandstage' ) );
        }

        $check = wp_check_filetype( $file['name'] );
        $ext   = strtolower( $check['ext'] ?? '' );

        if ( ! in_array( $ext, self::ALLOWED, true ) ) {
            return new \WP_Error( 'invalid_type', __( 'Type de fichier non autorisé (jpg, png, webp, svg).', 'bandstage' ) );
        }

        $upload_dir = wp_upload_dir();
        $logos_dir  = trailingslashit( $upload_dir['basedir'] ) . self::SUBDIR;

        if ( ! wp_mkdir_p( $logos_dir ) ) {
            return new \WP_Error( 'mkdir_failed', __( 'Impossible de créer le dossier de logos.', 'bandstage' ) );
        }

        $filename  = wp_unique_filename( $logos_dir, sanitize_file_name( $file['name'] ) );
        $dest_path = $logos_dir . '/' . $filename;

        if ( ! move_uploaded_file( $file['tmp_name'], $dest_path ) ) {
            return new \WP_Error( 'move_failed', __( 'Impossible de déplacer le fichier.', 'bandstage' ) );
        }

        return self::SUBDIR . '/' . $filename;
    }

    /**
     * Supprime un logo à partir de son chemin relatif stocké en DB.
     *
     * @param string $relative_path  Ex: "bandstage/logos/abc123.jpg"
     */
    public static function delete( string $relative_path ): void {
        if ( empty( $relative_path ) ) {
            return;
        }
        $upload_dir = wp_upload_dir();
        $full_path  = trailingslashit( $upload_dir['basedir'] ) . ltrim( $relative_path, '/' );
        if ( file_exists( $full_path ) ) {
            wp_delete_file( $full_path );
        }
    }

    /**
     * Retourne l'URL publique d'un logo.
     *
     * @param string $relative_path  Ex: "bandstage/logos/abc123.jpg"
     */
    public static function url( string $relative_path ): string {
        if ( empty( $relative_path ) ) {
            return '';
        }
        $upload_dir = wp_upload_dir();
        return trailingslashit( $upload_dir['baseurl'] ) . ltrim( $relative_path, '/' );
    }
}
```

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Media/LogoUploader.php
git commit -m "feat: LogoUploader (upload direct hors médiathèque)"
```

---

## Task 4 — Partenaire entity (DB-based)

**Files:**
- Replace: `BandStage/src/Domain/Partenaires/Partenaire.php`

- [ ] **Réécrire entièrement le fichier**

```php
<?php
/**
 * Entité Partenaire (table bandstage_partenaires).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Partenaires;

use BandStage\Domain\Media\LogoUploader;

defined( 'ABSPATH' ) || exit;

class Partenaire {

    public function __construct(
        public readonly int    $id,
        public readonly ?int   $type_id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $logo_path,
        public readonly string $logo_url,
        public readonly string $website,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $numero,
        public readonly string $nom_voie,
        public readonly string $code_postal,
        public readonly string $ville,
        public readonly string $type_name,
        public readonly string $type_slug,
        public readonly string $type_icon,
    ) {}

    public static function from_db_row( object $row ): self {
        $logo_path = (string) ( $row->logo_path ?? '' );
        return new self(
            id:          (int)    $row->id,
            type_id:     isset( $row->type_id ) && $row->type_id !== null ? (int) $row->type_id : null,
            name:        (string) $row->name,
            description: (string) ( $row->description ?? '' ),
            logo_path:   $logo_path,
            logo_url:    LogoUploader::url( $logo_path ),
            website:     (string) ( $row->website ?? '' ),
            email:       (string) ( $row->email ?? '' ),
            phone:       (string) ( $row->phone ?? '' ),
            numero:      (string) ( $row->numero ?? '' ),
            nom_voie:    (string) ( $row->nom_voie ?? '' ),
            code_postal: (string) ( $row->code_postal ?? '' ),
            ville:       (string) ( $row->ville ?? '' ),
            type_name:   (string) ( $row->type_name ?? '' ),
            type_slug:   (string) ( $row->type_slug ?? '' ),
            type_icon:   (string) ( $row->type_icon ?? '' ),
        );
    }

    /** Adresse complète formatée sur une ligne. */
    public function address_full(): string {
        $parts = array_filter( [ $this->numero, $this->nom_voie, $this->code_postal, $this->ville ] );
        return implode( ' ', $parts );
    }
}
```

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Partenaires/Partenaire.php
git commit -m "feat: Partenaire entity DB-based (remplace CPT)"
```

---

## Task 5 — PartenaireService (DB-based)

**Files:**
- Replace: `BandStage/src/Domain/Partenaires/PartenaireService.php`

- [ ] **Réécrire entièrement le fichier**

```php
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

        if ( ! empty( $_FILES['logo']['name'] ) ) {
            if ( $logo_path ) {
                LogoUploader::delete( $logo_path );
            }
            $result = LogoUploader::upload( $_FILES['logo'] );
            if ( is_wp_error( $result ) ) {
                wp_send_json_error( [ 'message' => $result->get_error_message() ] );
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
```

- [ ] **Vérifier** : ouvrir la page Partenaires en Studio → la liste doit se charger sans erreur PHP.

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Partenaires/PartenaireService.php
git commit -m "feat: PartenaireService DB-based (remplace CPT)"
```

---

## Task 6 — Concert entity

**Files:**
- Create: `BandStage/src/Domain/Concerts/Concert.php`

- [ ] **Créer le fichier**

```php
<?php
/**
 * Entité Concert (table bandstage_concerts).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Concerts;

defined( 'ABSPATH' ) || exit;

class Concert {

    public function __construct(
        public readonly int    $id,
        public readonly string $titre,
        public readonly string $date_debut,
        public readonly string $date_fin,
        public readonly string $horaires,
        public readonly string $nom_lieu,
        public readonly string $numero,
        public readonly string $nom_voie,
        public readonly string $code_postal,
        public readonly string $ville,
        /** @var int[] IDs des partenaires associés */
        public readonly array  $partenaire_ids,
    ) {}

    public static function from_db_row( object $row, array $partenaire_ids = [] ): self {
        return new self(
            id:             (int)    $row->id,
            titre:          (string) $row->titre,
            date_debut:     (string) $row->date_debut,
            date_fin:       (string) ( $row->date_fin ?? '' ),
            horaires:       (string) ( $row->horaires ?? '' ),
            nom_lieu:       (string) ( $row->nom_lieu ?? '' ),
            numero:         (string) ( $row->numero ?? '' ),
            nom_voie:       (string) ( $row->nom_voie ?? '' ),
            code_postal:    (string) ( $row->code_postal ?? '' ),
            ville:          (string) ( $row->ville ?? '' ),
            partenaire_ids: $partenaire_ids,
        );
    }

    /** Date(s) formatée(s) pour l'affichage. */
    public function dates_formatted(): string {
        $d = date_i18n( 'd/m/Y', strtotime( $this->date_debut ) );
        if ( $this->date_fin && $this->date_fin !== $this->date_debut ) {
            $d .= ' – ' . date_i18n( 'd/m/Y', strtotime( $this->date_fin ) );
        }
        return $d;
    }

    /** Adresse du lieu formatée sur une ligne. */
    public function address_full(): string {
        $parts = array_filter( [ $this->numero, $this->nom_voie, $this->code_postal, $this->ville ] );
        return implode( ' ', $parts );
    }
}
```

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Concerts/Concert.php
git commit -m "feat: Concert entity"
```

---

## Task 7 — ConcertService

**Files:**
- Create: `BandStage/src/Domain/Concerts/ConcertService.php`

- [ ] **Créer le fichier**

```php
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
```

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Concerts/ConcertService.php
git commit -m "feat: ConcertService CRUD"
```

---

## Task 8 — Cleanup CPT + Plugin wiring

**Files:**
- Modify: `BandStage/src/Admin/PostTypes.php`
- Modify: `BandStage/src/Core/Plugin.php`
- Modify: `BandStage/config/routes.php`
- Modify: `BandStage/uninstall.php`

- [ ] **PostTypes.php** — supprimer `register_partenaire()` et `register_type_partenaire()`, et leurs appels dans `register_all()`

Remplacer `register_all()` par :
```php
public static function register_all(): void {
    self::register_news();
    self::register_band_member();
}
```
Supprimer les deux méthodes `register_partenaire()` et `register_type_partenaire()`.

- [ ] **Plugin.php** — ajouter `ConcertService` aux imports et à `register_domain_services()`

Ajouter en haut :
```php
use BandStage\Domain\Concerts\ConcertService;
```

Dans `register_domain_services()`, ajouter dans le tableau `$services` :
```php
new ConcertService(),
```

Dans `create_pages()`, ajouter dans le tableau `$pages` :
```php
'concerts' => [ 'title' => 'BandStage — Concerts', 'shortcode' => '[bandstage_concerts]', 'template' => 'elementor_canvas' ],
```

- [ ] **routes.php** — ajouter la clé concerts

```php
'concerts' => [
    'list' => $tpl . 'concert-list.php',
    'edit' => $tpl . 'concert-edit.php',
],
```

- [ ] **uninstall.php** — remplacer la section CPT/taxonomie par les nouvelles tables et le dossier logos

Remplacer les sections 4 et 5 (CPT + taxonomie) par :
```php
// -------------------------------------------------------------------------
// 4. Tables concerts & partenaires
// -------------------------------------------------------------------------
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_concert_partenaires" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_concerts" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_partenaires" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_partenaire_types" );

// -------------------------------------------------------------------------
// 5. Logos uploadés
// -------------------------------------------------------------------------
$upload_dir = wp_upload_dir();
$logos_dir  = trailingslashit( $upload_dir['basedir'] ) . 'bandstage/logos';
if ( is_dir( $logos_dir ) ) {
    array_map( 'unlink', glob( $logos_dir . '/*' ) );
    rmdir( $logos_dir );
}
```

Ajouter `'bs_page_concerts'` dans les tableaux `$options` et `$page_options`.

- [ ] **Vérifier** : aucune erreur PHP dans le log WordPress après rechargement.

- [ ] **Commit**

```bash
git add BandStage/src/Admin/PostTypes.php BandStage/src/Core/Plugin.php \
        BandStage/config/routes.php BandStage/uninstall.php
git commit -m "feat: remove CPT partenaire, wire ConcertService, add concerts route"
```

---

## Task 9 — Shortcodes.php

**Files:**
- Modify: `BandStage/src/Frontend/Shortcodes.php`

- [ ] **Ajouter l'import ConcertService** en haut du fichier (après les autres `use`) :

```php
use BandStage\Domain\Concerts\ConcertService;
```

- [ ] **Ajouter l'enregistrement du shortcode** dans `register()` :

```php
add_shortcode( 'bandstage_concerts', [ $this, 'concerts' ] );
```

- [ ] **Mettre à jour la méthode `partenaires()`** pour utiliser le service DB :

```php
public function partenaires(): string {
    $service = new PartenaireService();
    ob_start();
    Assets::maybe_inject_dynamic_css();

    if ( ! current_user_can( 'edit_posts' ) ) {
        $partenaires = $service->get_grouped_by_type();
        include BANDSTAGE_PLUGIN_DIR . 'templates/public/partenaires-public.php';
        return ob_get_clean();
    }

    $routes = include BANDSTAGE_PLUGIN_DIR . 'config/routes.php';
    $view   = sanitize_key( $_GET['bs_view'] ?? 'list' );

    if ( 'edit' === $view ) {
        $post_id    = absint( $_GET['bs_id'] ?? 0 );
        $partenaire = $post_id ? $service->get( $post_id ) : null;
        $types      = $service->get_types();
        include $routes['partenaires']['edit'];
    } else {
        $partenaires = $service->get_all();
        include $routes['partenaires']['list'];
    }

    return ob_get_clean();
}
```

- [ ] **Ajouter la méthode `concerts()`** (après `partenaires()`):

```php
public function concerts(): string {
    $service = new ConcertService();
    ob_start();
    Assets::maybe_inject_dynamic_css();

    if ( ! current_user_can( 'edit_posts' ) ) {
        $concerts = $service->get_upcoming();
        include BANDSTAGE_PLUGIN_DIR . 'templates/public/concerts-public.php';
        return ob_get_clean();
    }

    $routes = include BANDSTAGE_PLUGIN_DIR . 'config/routes.php';
    $view   = sanitize_key( $_GET['bs_view'] ?? 'list' );

    if ( 'edit' === $view ) {
        $concert_id      = absint( $_GET['bs_id'] ?? 0 );
        $concert         = $concert_id ? $service->get( $concert_id ) : null;
        $partenaire_service = new \BandStage\Domain\Partenaires\PartenaireService();
        $all_partenaires = $partenaire_service->get_all();
        include $routes['concerts']['edit'];
    } else {
        $concerts = $service->get_all();
        include $routes['concerts']['list'];
    }

    return ob_get_clean();
}
```

- [ ] **Ajouter le helper `concerts_url()`** (après `groupe_url()`):

```php
public static function concerts_url( string $view = 'list', int $id = 0 ): string {
    $url = get_permalink( (int) get_option( 'bs_page_concerts' ) );
    if ( ! $url ) {
        return '#';
    }
    $args = [ 'bs_view' => $view ];
    if ( $id ) {
        $args['bs_id'] = $id;
    }
    return add_query_arg( $args, $url );
}
```

- [ ] **Commit**

```bash
git add BandStage/src/Frontend/Shortcodes.php
git commit -m "feat: shortcode bandstage_concerts, mise à jour shortcode partenaires"
```

---

## Task 10 — Assets.php

**Files:**
- Modify: `BandStage/src/Frontend/Assets.php`

- [ ] **Ajouter `bs_page_concerts`** dans `is_bandstage_page()`, dans le tableau `$page_options` :

```php
'bs_page_concerts',
```

- [ ] **Ajouter `bs_page_concerts`** dans `$this->studio_pages` (propriété de classe) :

```php
private array $studio_pages = [ 'bs_page_studio', 'bs_page_partenaires', 'bs_page_groupe', 'bs_page_concerts' ];
```

- [ ] **Ajouter `bandstage_concerts`** dans le fallback `has_shortcode()` de `is_bandstage_page()` :

```php
$shortcodes = [
    'bandstage_homepage', 'bandstage_tchache', 'bandstage_profil',
    'bandstage_studio',   'bandstage_partenaires', 'bandstage_groupe',
    'bandstage_concerts',
];
```

Et dans `is_studio_page()` :
```php
$studio_shortcodes = [ 'bandstage_studio', 'bandstage_partenaires', 'bandstage_groupe', 'bandstage_concerts' ];
```

- [ ] **Commit**

```bash
git add BandStage/src/Frontend/Assets.php
git commit -m "feat: Assets détecte la page concerts"
```

---

## Task 11 — Templates partenaires

**Files:**
- Replace: `BandStage/templates/public/partenaires-public.php`
- Replace: `BandStage/templates/public/studio/partenaire-list.php`
- Replace: `BandStage/templates/public/studio/partenaire-edit.php`

- [ ] **Réécrire `partenaires-public.php`**

```php
<?php
/**
 * [bandstage_partenaires] — vue publique.
 *
 * @var array $partenaires  groupés : [ slug => [ label, icon, items[] ] ]
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bs-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php esc_html_e( 'Partenaires', 'bandstage' ); ?></h1>
  </header>

  <?php if ( empty( $partenaires ) ) : ?>
    <div class="bs-empty">
      <p><?php esc_html_e( 'Aucun partenaire pour le moment.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <?php foreach ( $partenaires as $type ) : ?>
      <section class="bs-pp-section">
        <h2 class="bs-pp-section__title">
          <?php if ( $type['icon'] ) echo esc_html( $type['icon'] ) . ' '; ?>
          <?php echo esc_html( $type['label'] ); ?>
        </h2>
        <div class="bs-pp-grid">
          <?php foreach ( $type['items'] as $p ) : ?>
            <div class="bs-pp-card">
              <?php if ( $p->logo_url ) : ?>
                <img class="bs-pp-card__thumb"
                     src="<?php echo esc_url( $p->logo_url ); ?>"
                     alt="<?php echo esc_attr( $p->name ); ?>"
                     loading="lazy">
              <?php endif; ?>
              <div class="bs-pp-card__body">
                <h3 class="bs-pp-card__name"><?php echo esc_html( $p->name ); ?></h3>
                <?php if ( $p->description ) : ?>
                  <p class="bs-pp-card__desc"><?php echo esc_html( $p->description ); ?></p>
                <?php endif; ?>
                <ul class="bs-pp-card__contacts">
                  <?php $addr = $p->address_full(); if ( $addr ) : ?>
                    <li>📍 <?php echo esc_html( $addr ); ?></li>
                  <?php endif; ?>
                  <?php if ( $p->phone ) : ?>
                    <li><a href="tel:<?php echo esc_attr( $p->phone ); ?>"><?php echo esc_html( $p->phone ); ?></a></li>
                  <?php endif; ?>
                  <?php if ( $p->email ) : ?>
                    <li><a href="mailto:<?php echo esc_attr( $p->email ); ?>"><?php echo esc_html( $p->email ); ?></a></li>
                  <?php endif; ?>
                  <?php if ( $p->website ) : ?>
                    <li><a href="<?php echo esc_url( $p->website ); ?>" target="_blank" rel="noopener">🌐 <?php esc_html_e( 'Site web', 'bandstage' ); ?></a></li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
```

- [ ] **Réécrire `partenaire-list.php`**

```php
<?php
/**
 * [bandstage_partenaires] bs_view=list — gestion partenaires (Auteur+).
 *
 * @var \BandStage\Domain\Partenaires\Partenaire[] $partenaires
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$can_delete = current_user_can( 'manage_options' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <span class="bss-navbar__title"><?php esc_html_e( 'Partenaires', 'bandstage' ); ?></span>
    <a href="<?php echo esc_url( Shortcodes::partenaires_url( 'edit' ) ); ?>"
       class="bss-navbar__action bss-btn bss-btn--primary">
      <?php esc_html_e( '+ Ajouter', 'bandstage' ); ?>
    </a>
  </nav>

  <?php if ( empty( $partenaires ) ) : ?>
    <div class="bss-empty">
      <p><?php esc_html_e( 'Aucun partenaire.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bss-partenaire-list">
      <?php foreach ( $partenaires as $p ) : ?>
        <li class="bss-partenaire-item" data-id="<?php echo esc_attr( $p->id ); ?>">

          <div class="bss-partenaire-item__logo">
            <?php if ( $p->logo_url ) : ?>
              <img src="<?php echo esc_url( $p->logo_url ); ?>" alt="">
            <?php else : ?>
              <span class="bss-partenaire-item__initials">
                <?php echo esc_html( mb_strtoupper( mb_substr( $p->name, 0, 2 ) ) ); ?>
              </span>
            <?php endif; ?>
          </div>

          <div class="bss-partenaire-item__info">
            <strong><?php echo esc_html( $p->name ); ?></strong>
            <?php if ( $p->type_name ) : ?>
              <span class="bss-partenaire-item__type">
                <?php if ( $p->type_icon ) echo esc_html( $p->type_icon ) . ' '; ?>
                <?php echo esc_html( $p->type_name ); ?>
              </span>
            <?php endif; ?>
            <?php $addr = $p->address_full(); if ( $addr ) : ?>
              <em class="bss-partenaire-item__address"><?php echo esc_html( $addr ); ?></em>
            <?php endif; ?>
          </div>

          <div class="bss-partenaire-item__actions">
            <a href="<?php echo esc_url( Shortcodes::partenaires_url( 'edit', $p->id ) ); ?>"
               class="bss-btn bss-btn--sm bss-btn--ghost">
              <?php esc_html_e( 'Modifier', 'bandstage' ); ?>
            </a>
            <?php if ( $can_delete ) : ?>
              <button
                type="button"
                class="bss-btn bss-btn--sm bss-btn--danger js-partenaire-delete"
                data-id="<?php echo esc_attr( $p->id ); ?>"
                data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
                <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
              </button>
            <?php endif; ?>
          </div>

        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
```

- [ ] **Réécrire `partenaire-edit.php`**

```php
<?php
/**
 * [bandstage_partenaires] bs_view=edit — formulaire partenaire.
 *
 * @var \BandStage\Domain\Partenaires\Partenaire|null $partenaire
 * @var \BandStage\Domain\Partenaires\PartenaireType[] $types
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$is_edit    = $partenaire !== null;
$pid        = $is_edit ? $partenaire->id          : 0;
$name       = $is_edit ? $partenaire->name        : '';
$desc       = $is_edit ? $partenaire->description : '';
$type_id    = $is_edit ? $partenaire->type_id     : null;
$website    = $is_edit ? $partenaire->website     : '';
$email      = $is_edit ? $partenaire->email       : '';
$phone      = $is_edit ? $partenaire->phone       : '';
$numero     = $is_edit ? $partenaire->numero      : '';
$nom_voie   = $is_edit ? $partenaire->nom_voie    : '';
$code_postal= $is_edit ? $partenaire->code_postal : '';
$ville      = $is_edit ? $partenaire->ville       : '';
$logo_url   = $is_edit ? $partenaire->logo_url    : '';
$logo_path  = $is_edit ? $partenaire->logo_path   : '';

$page_title = $is_edit
  ? esc_html__( 'Modifier le partenaire', 'bandstage' )
  : esc_html__( 'Nouveau partenaire', 'bandstage' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <a href="<?php echo esc_url( Shortcodes::partenaires_url( 'list' ) ); ?>"
       class="bss-navbar__back">← <?php esc_html_e( 'Retour', 'bandstage' ); ?></a>
    <span class="bss-navbar__title"><?php echo $page_title; ?></span>
    <button type="submit" form="bss-partenaire-form"
            class="bss-navbar__action bss-btn bss-btn--primary js-partenaire-save">
      <?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
    </button>
  </nav>

  <form id="bss-partenaire-form" class="bss-form"
        enctype="multipart/form-data"
        data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="action"       value="bs_partenaire_save">
    <input type="hidden" name="partenaire_id" value="<?php echo esc_attr( $pid ); ?>">

    <!-- Logo -->
    <div class="bss-form__group bss-form__group--logo">
      <label class="bss-form__label"><?php esc_html_e( 'Logo', 'bandstage' ); ?></label>
      <div class="bss-logo-preview" id="bs-logo-preview">
        <?php if ( $logo_url ) : ?>
          <img src="<?php echo esc_url( $logo_url ); ?>" alt="">
        <?php else : ?>
          <span class="bss-logo-preview__placeholder">🖼️</span>
        <?php endif; ?>
      </div>
      <input type="hidden" name="logo_action" id="bs-logo-action" value="">
      <input type="file" name="logo" id="bs-logo-file" accept="image/*" class="bss-form__file">
      <?php if ( $logo_url ) : ?>
        <button type="button" class="bss-btn bss-btn--ghost bss-btn--danger js-logo-remove">
          <?php esc_html_e( 'Retirer le logo', 'bandstage' ); ?>
        </button>
      <?php endif; ?>
    </div>

    <!-- Nom -->
    <div class="bss-form__group">
      <label for="bs-p-name" class="bss-form__label"><?php esc_html_e( 'Nom', 'bandstage' ); ?> *</label>
      <input type="text" id="bs-p-name" name="name" class="bss-form__input"
             value="<?php echo esc_attr( $name ); ?>" required
             placeholder="<?php esc_attr_e( 'Nom du partenaire', 'bandstage' ); ?>">
    </div>

    <!-- Type -->
    <div class="bss-form__group">
      <label for="bs-p-type" class="bss-form__label"><?php esc_html_e( 'Type', 'bandstage' ); ?></label>
      <select id="bs-p-type" name="type_id" class="bss-form__input">
        <option value=""><?php esc_html_e( '— Aucun type —', 'bandstage' ); ?></option>
        <?php foreach ( $types as $t ) : ?>
          <option value="<?php echo esc_attr( $t->id ); ?>"
            <?php selected( $type_id, $t->id ); ?>>
            <?php echo esc_html( ( $t->icon ? $t->icon . ' ' : '' ) . $t->name ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Description -->
    <div class="bss-form__group">
      <label for="bs-p-desc" class="bss-form__label"><?php esc_html_e( 'Description', 'bandstage' ); ?></label>
      <textarea id="bs-p-desc" name="description" class="bss-form__input bss-form__textarea"
                rows="4"><?php echo esc_textarea( $desc ); ?></textarea>
    </div>

    <!-- Adresse -->
    <fieldset class="bss-form__fieldset">
      <legend class="bss-form__legend"><?php esc_html_e( 'Adresse', 'bandstage' ); ?></legend>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--sm">
          <label for="bs-p-numero" class="bss-form__label"><?php esc_html_e( 'N°', 'bandstage' ); ?></label>
          <input type="text" id="bs-p-numero" name="numero" class="bss-form__input"
                 value="<?php echo esc_attr( $numero ); ?>" placeholder="12">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bs-p-voie" class="bss-form__label"><?php esc_html_e( 'Voie', 'bandstage' ); ?></label>
          <input type="text" id="bs-p-voie" name="nom_voie" class="bss-form__input"
                 value="<?php echo esc_attr( $nom_voie ); ?>"
                 placeholder="<?php esc_attr_e( 'Rue de la Paix', 'bandstage' ); ?>">
        </div>
      </div>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--sm">
          <label for="bs-p-cp" class="bss-form__label"><?php esc_html_e( 'Code postal', 'bandstage' ); ?></label>
          <input type="text" id="bs-p-cp" name="code_postal" class="bss-form__input"
                 value="<?php echo esc_attr( $code_postal ); ?>" placeholder="75001">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bs-p-ville" class="bss-form__label"><?php esc_html_e( 'Ville', 'bandstage' ); ?></label>
          <input type="text" id="bs-p-ville" name="ville" class="bss-form__input"
                 value="<?php echo esc_attr( $ville ); ?>"
                 placeholder="<?php esc_attr_e( 'Paris', 'bandstage' ); ?>">
        </div>
      </div>
    </fieldset>

    <!-- Contact -->
    <fieldset class="bss-form__fieldset">
      <legend class="bss-form__legend"><?php esc_html_e( 'Contact', 'bandstage' ); ?></legend>
      <div class="bss-form__group">
        <label for="bs-p-phone" class="bss-form__label"><?php esc_html_e( 'Téléphone', 'bandstage' ); ?></label>
        <input type="tel" id="bs-p-phone" name="phone" class="bss-form__input"
               value="<?php echo esc_attr( $phone ); ?>" placeholder="01 23 45 67 89">
      </div>
      <div class="bss-form__group">
        <label for="bs-p-email" class="bss-form__label"><?php esc_html_e( 'Email', 'bandstage' ); ?></label>
        <input type="email" id="bs-p-email" name="email" class="bss-form__input"
               value="<?php echo esc_attr( $email ); ?>"
               placeholder="contact@partenaire.fr">
      </div>
      <div class="bss-form__group">
        <label for="bs-p-web" class="bss-form__label"><?php esc_html_e( 'Site web', 'bandstage' ); ?></label>
        <input type="url" id="bs-p-web" name="website" class="bss-form__input"
               value="<?php echo esc_attr( $website ); ?>"
               placeholder="https://partenaire.fr">
      </div>
    </fieldset>

  </form>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
```

- [ ] **Commit**

```bash
git add BandStage/templates/public/partenaires-public.php \
        BandStage/templates/public/studio/partenaire-list.php \
        BandStage/templates/public/studio/partenaire-edit.php
git commit -m "feat: templates partenaires adaptés au nouveau modèle DB"
```

---

## Task 12 — Templates concerts

**Files:**
- Create: `BandStage/templates/public/concerts-public.php`
- Create: `BandStage/templates/public/studio/concert-list.php`
- Create: `BandStage/templates/public/studio/concert-edit.php`

- [ ] **Créer `concerts-public.php`**

```php
<?php
/**
 * [bandstage_concerts] — vue publique (concerts à venir).
 *
 * @var \BandStage\Domain\Concerts\Concert[] $concerts
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bs-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php esc_html_e( 'Concerts', 'bandstage' ); ?></h1>
    <p class="bs-header__tagline"><?php esc_html_e( 'Prochaines dates', 'bandstage' ); ?></p>
  </header>

  <?php if ( empty( $concerts ) ) : ?>
    <div class="bs-empty">
      <p><?php esc_html_e( 'Aucun concert à venir pour le moment.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bs-concert-list">
      <?php foreach ( $concerts as $c ) : ?>
        <li class="bs-concert-card">
          <div class="bs-concert-card__date"><?php echo esc_html( $c->dates_formatted() ); ?></div>
          <div class="bs-concert-card__body">
            <h2 class="bs-concert-card__titre"><?php echo esc_html( $c->titre ); ?></h2>
            <?php if ( $c->nom_lieu ) : ?>
              <p class="bs-concert-card__lieu">
                📍 <?php echo esc_html( $c->nom_lieu ); ?>
                <?php $addr = $c->address_full(); if ( $addr ) : ?>
                  <span class="bs-concert-card__address"><?php echo esc_html( $addr ); ?></span>
                <?php endif; ?>
              </p>
            <?php endif; ?>
            <?php if ( $c->horaires ) : ?>
              <p class="bs-concert-card__horaires">🕐 <?php echo esc_html( $c->horaires ); ?></p>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

</div>
```

- [ ] **Créer `concert-list.php`**

```php
<?php
/**
 * [bandstage_concerts] bs_view=list — gestion concerts (Auteur+).
 *
 * @var \BandStage\Domain\Concerts\Concert[] $concerts
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$can_delete = current_user_can( 'manage_options' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <span class="bss-navbar__title"><?php esc_html_e( 'Concerts', 'bandstage' ); ?></span>
    <a href="<?php echo esc_url( Shortcodes::concerts_url( 'edit' ) ); ?>"
       class="bss-navbar__action bss-btn bss-btn--primary">
      <?php esc_html_e( '+ Ajouter', 'bandstage' ); ?>
    </a>
  </nav>

  <?php if ( empty( $concerts ) ) : ?>
    <div class="bss-empty">
      <p><?php esc_html_e( 'Aucun concert.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bss-concert-list">
      <?php foreach ( $concerts as $c ) : ?>
        <li class="bss-concert-item" data-id="<?php echo esc_attr( $c->id ); ?>">
          <div class="bss-concert-item__date"><?php echo esc_html( $c->dates_formatted() ); ?></div>
          <div class="bss-concert-item__info">
            <strong><?php echo esc_html( $c->titre ); ?></strong>
            <?php if ( $c->nom_lieu ) : ?>
              <span class="bss-concert-item__lieu"><?php echo esc_html( $c->nom_lieu ); ?></span>
            <?php endif; ?>
            <?php if ( $c->horaires ) : ?>
              <em class="bss-concert-item__horaires"><?php echo esc_html( $c->horaires ); ?></em>
            <?php endif; ?>
          </div>
          <div class="bss-concert-item__actions">
            <a href="<?php echo esc_url( Shortcodes::concerts_url( 'edit', $c->id ) ); ?>"
               class="bss-btn bss-btn--sm bss-btn--ghost">
              <?php esc_html_e( 'Modifier', 'bandstage' ); ?>
            </a>
            <?php if ( $can_delete ) : ?>
              <button
                type="button"
                class="bss-btn bss-btn--sm bss-btn--danger js-concert-delete"
                data-id="<?php echo esc_attr( $c->id ); ?>"
                data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
                <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
              </button>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
```

- [ ] **Créer `concert-edit.php`**

```php
<?php
/**
 * [bandstage_concerts] bs_view=edit — formulaire concert.
 *
 * @var \BandStage\Domain\Concerts\Concert|null                $concert
 * @var \BandStage\Domain\Partenaires\Partenaire[]             $all_partenaires
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$is_edit    = $concert !== null;
$cid        = $is_edit ? $concert->id          : 0;
$titre      = $is_edit ? $concert->titre        : '';
$date_debut = $is_edit ? $concert->date_debut   : '';
$date_fin   = $is_edit ? $concert->date_fin     : '';
$horaires   = $is_edit ? $concert->horaires     : '';
$nom_lieu   = $is_edit ? $concert->nom_lieu     : '';
$numero     = $is_edit ? $concert->numero       : '';
$nom_voie   = $is_edit ? $concert->nom_voie     : '';
$code_postal= $is_edit ? $concert->code_postal  : '';
$ville      = $is_edit ? $concert->ville        : '';
$selected_ids = $is_edit ? $concert->partenaire_ids : [];

$page_title = $is_edit
  ? esc_html__( 'Modifier le concert', 'bandstage' )
  : esc_html__( 'Nouveau concert', 'bandstage' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <a href="<?php echo esc_url( Shortcodes::concerts_url( 'list' ) ); ?>"
       class="bss-navbar__back">← <?php esc_html_e( 'Retour', 'bandstage' ); ?></a>
    <span class="bss-navbar__title"><?php echo $page_title; ?></span>
    <button type="submit" form="bss-concert-form"
            class="bss-navbar__action bss-btn bss-btn--primary js-concert-save">
      <?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
    </button>
  </nav>

  <form id="bss-concert-form" class="bss-form"
        data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="action"     value="bs_concert_save">
    <input type="hidden" name="concert_id" value="<?php echo esc_attr( $cid ); ?>">

    <!-- Titre -->
    <div class="bss-form__group">
      <label for="bs-c-titre" class="bss-form__label"><?php esc_html_e( 'Titre', 'bandstage' ); ?> *</label>
      <input type="text" id="bs-c-titre" name="titre" class="bss-form__input"
             value="<?php echo esc_attr( $titre ); ?>" required
             placeholder="<?php esc_attr_e( 'Nom du concert ou de l\'événement', 'bandstage' ); ?>">
    </div>

    <!-- Dates -->
    <div class="bss-form__row">
      <div class="bss-form__group bss-form__group--grow">
        <label for="bs-c-date-debut" class="bss-form__label"><?php esc_html_e( 'Date de début', 'bandstage' ); ?> *</label>
        <input type="date" id="bs-c-date-debut" name="date_debut" class="bss-form__input"
               value="<?php echo esc_attr( $date_debut ); ?>" required>
      </div>
      <div class="bss-form__group bss-form__group--grow">
        <label for="bs-c-date-fin" class="bss-form__label"><?php esc_html_e( 'Date de fin', 'bandstage' ); ?></label>
        <input type="date" id="bs-c-date-fin" name="date_fin" class="bss-form__input"
               value="<?php echo esc_attr( $date_fin ); ?>">
      </div>
    </div>

    <!-- Horaires -->
    <div class="bss-form__group">
      <label for="bs-c-horaires" class="bss-form__label"><?php esc_html_e( 'Horaires', 'bandstage' ); ?></label>
      <input type="text" id="bs-c-horaires" name="horaires" class="bss-form__input"
             value="<?php echo esc_attr( $horaires ); ?>"
             placeholder="<?php esc_attr_e( '20h30 – 23h00', 'bandstage' ); ?>">
    </div>

    <!-- Lieu -->
    <fieldset class="bss-form__fieldset">
      <legend class="bss-form__legend"><?php esc_html_e( 'Lieu', 'bandstage' ); ?></legend>
      <div class="bss-form__group">
        <label for="bs-c-lieu" class="bss-form__label"><?php esc_html_e( 'Nom du lieu', 'bandstage' ); ?></label>
        <input type="text" id="bs-c-lieu" name="nom_lieu" class="bss-form__input"
               value="<?php echo esc_attr( $nom_lieu ); ?>"
               placeholder="<?php esc_attr_e( 'Salle des fêtes, Bar Le Rock…', 'bandstage' ); ?>">
      </div>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--sm">
          <label for="bs-c-numero" class="bss-form__label"><?php esc_html_e( 'N°', 'bandstage' ); ?></label>
          <input type="text" id="bs-c-numero" name="numero" class="bss-form__input"
                 value="<?php echo esc_attr( $numero ); ?>" placeholder="12">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bs-c-voie" class="bss-form__label"><?php esc_html_e( 'Voie', 'bandstage' ); ?></label>
          <input type="text" id="bs-c-voie" name="nom_voie" class="bss-form__input"
                 value="<?php echo esc_attr( $nom_voie ); ?>"
                 placeholder="<?php esc_attr_e( 'Rue de la Paix', 'bandstage' ); ?>">
        </div>
      </div>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--sm">
          <label for="bs-c-cp" class="bss-form__label"><?php esc_html_e( 'Code postal', 'bandstage' ); ?></label>
          <input type="text" id="bs-c-cp" name="code_postal" class="bss-form__input"
                 value="<?php echo esc_attr( $code_postal ); ?>" placeholder="75001">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bs-c-ville" class="bss-form__label"><?php esc_html_e( 'Ville', 'bandstage' ); ?></label>
          <input type="text" id="bs-c-ville" name="ville" class="bss-form__input"
                 value="<?php echo esc_attr( $ville ); ?>"
                 placeholder="<?php esc_attr_e( 'Paris', 'bandstage' ); ?>">
        </div>
      </div>
    </fieldset>

    <!-- Partenaires associés -->
    <?php if ( ! empty( $all_partenaires ) ) : ?>
      <div class="bss-form__group">
        <label for="bs-c-partenaires" class="bss-form__label">
          <?php esc_html_e( 'Partenaires associés', 'bandstage' ); ?>
        </label>
        <select id="bs-c-partenaires" name="partenaire_ids[]" multiple class="bss-form__select-multiple">
          <?php foreach ( $all_partenaires as $p ) : ?>
            <option value="<?php echo esc_attr( $p->id ); ?>"
              <?php echo in_array( $p->id, $selected_ids, true ) ? 'selected' : ''; ?>>
              <?php echo esc_html( $p->name ); ?>
              <?php if ( $p->type_name ) echo esc_html( ' — ' . $p->type_name ); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="bss-form__hint"><?php esc_html_e( 'Ctrl+clic (ou Cmd+clic) pour sélectionner plusieurs partenaires.', 'bandstage' ); ?></p>
      </div>
    <?php endif; ?>

  </form>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
```

- [ ] **Commit**

```bash
git add BandStage/templates/public/concerts-public.php \
        BandStage/templates/public/studio/concert-list.php \
        BandStage/templates/public/studio/concert-edit.php
git commit -m "feat: templates concerts (vue publique, liste studio, formulaire)"
```

---

## Task 13 — studio.js

**Files:**
- Modify: `BandStage/assets/js/studio.js`

- [ ] **Remplacer la section DELETE PARTENAIRE (section 3)** par la version qui utilise `partenaire_id` :

```javascript
// ============================================================
// 3. DELETE PARTENAIRE
// ============================================================
document.querySelectorAll('.js-partenaire-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm(BsPublic.i18n.confirm)) return;
    btn.disabled = true;
    const json = await bsAjax('bs_partenaire_delete', { partenaire_id: btn.dataset.id });

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      const row = btn.closest('.bss-partenaire-item');
      if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      btn.disabled = false;
    }
  });
});
```

- [ ] **Remplacer la section SAVE PARTENAIRE (section 4)** par la version avec FormData natif (pour l'upload de logo) :

```javascript
// ============================================================
// 4. SAVE PARTENAIRE
// ============================================================
(function () {
  const form = document.getElementById('bss-partenaire-form');
  if (!form) return;

  // Prévisualisation du logo sélectionné
  const fileInput = document.getElementById('bs-logo-file');
  const preview   = document.getElementById('bs-logo-preview');
  if (fileInput && preview) {
    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => { preview.innerHTML = `<img src="${e.target.result}" alt="">`; };
      reader.readAsDataURL(file);
    });
  }

  // Retrait du logo
  const removeBtn = form.querySelector('.js-logo-remove');
  if (removeBtn) {
    removeBtn.addEventListener('click', () => {
      if (preview) preview.innerHTML = '<span class="bss-logo-preview__placeholder">🖼️</span>';
      const actionInput = document.getElementById('bs-logo-action');
      if (actionInput) actionInput.value = 'remove';
      removeBtn.style.display = 'none';
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.set('action', 'bs_partenaire_save');
    formData.set('nonce', BsPublic.nonce);
    const btn = document.querySelector('[form="bss-partenaire-form"]');
    if (btn) btn.disabled = true;

    const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' });
    const json = await res.json();
    if (btn) btn.disabled = false;

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      setTimeout(() => { window.location.href = json.data.redirect; }, 900);
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
    }
  });
})();
```

- [ ] **Ajouter les sections 9 et 10 à la fin du fichier** (concerts) :

```javascript
// ============================================================
// 9. CONCERT — delete
// ============================================================
document.querySelectorAll('.js-concert-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm(BsPublic.i18n.confirm)) return;
    btn.disabled = true;
    const json = await bsAjax('bs_concert_delete', { concert_id: btn.dataset.id });

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      const row = btn.closest('.bss-concert-item');
      if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      btn.disabled = false;
    }
  });
});

// ============================================================
// 10. CONCERT — save (FormData natif pour le multi-select)
// ============================================================
(function () {
  const form = document.getElementById('bss-concert-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.set('action', 'bs_concert_save');
    formData.set('nonce', BsPublic.nonce);
    const btn = document.querySelector('[form="bss-concert-form"]');
    if (btn) btn.disabled = true;

    const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' });
    const json = await res.json();
    if (btn) btn.disabled = false;

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      setTimeout(() => { window.location.href = json.data.redirect; }, 900);
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
    }
  });
})();
```

- [ ] **Vérifier** : créer un concert en Studio → le formulaire se soumet sans erreur JS.

- [ ] **Commit**

```bash
git add BandStage/assets/js/studio.js
git commit -m "feat: studio.js — CRUD concerts, upload logo partenaire"
```

---

## Task 14 — CSS

**Files:**
- Modify: `BandStage/assets/css/public.css`
- Modify: `BandStage/assets/css/studio.css`

- [ ] **Ajouter à la fin de `public.css`** (section concerts publics) :

```css
/* ============================================================
   14. CONCERTS PUBLIC  (préfixe bs-concert-)
   ============================================================ */
.bs-concert-list {
  list-style : none;
  margin     : 0;
  padding    : var(--bs-pad);
  max-width  : var(--bs-max-w);
  margin-left: auto;
  margin-right: auto;
  display    : flex;
  flex-direction: column;
  gap        : 16px;
}

.bs-concert-card {
  display        : flex;
  gap            : 16px;
  background     : var(--bs-surface-3);
  border-radius  : var(--bs-r-card);
  padding        : 20px;
  backdrop-filter: blur(8px);
  align-items    : flex-start;
}

.bs-concert-card__date {
  font-family  : var(--bs-font-ui);
  font-size    : .85rem;
  letter-spacing: 1px;
  text-transform: uppercase;
  color        : var(--bs-accent);
  white-space  : nowrap;
  min-width    : 100px;
  padding-top  : 2px;
}

.bs-concert-card__body {
  flex: 1;
}

.bs-concert-card__titre {
  font-family : var(--bs-font-brand);
  font-size   : 1.2rem;
  margin      : 0 0 8px;
  color       : var(--bs-label-inv);
}

.bs-concert-card__lieu,
.bs-concert-card__horaires {
  font-size  : .88rem;
  color      : rgba(255,255,255,.75);
  margin     : 4px 0 0;
}

.bs-concert-card__address {
  display    : block;
  font-size  : .8rem;
  color      : rgba(255,255,255,.5);
  margin-top : 2px;
}
```

- [ ] **Ajouter à la fin de `studio.css`** (sections concerts + partenaires liste + form helpers) :

```css
/* ============================================================
   CONCERTS — liste studio  (préfixe bss-concert-)
   ============================================================ */
.bss-concert-list {
  list-style: none;
  margin    : 0;
  padding   : 0 var(--bs-pad) var(--bs-pad);
}

.bss-concert-item {
  display      : flex;
  align-items  : center;
  gap          : 12px;
  padding      : 14px 0;
  border-bottom: 1px solid var(--bs-sep-light);
}

.bss-concert-item__date {
  font-family  : var(--bs-font-ui);
  font-size    : .8rem;
  color        : var(--bs-accent);
  white-space  : nowrap;
  min-width    : 90px;
}

.bss-concert-item__info {
  flex       : 1;
  display    : flex;
  flex-direction: column;
  gap        : 2px;
}

.bss-concert-item__lieu {
  font-size : .82rem;
  color     : rgba(255,255,255,.6);
  display   : block;
}

.bss-concert-item__horaires {
  font-size : .78rem;
  color     : rgba(255,255,255,.45);
  display   : block;
}

.bss-concert-item__actions {
  display: flex;
  gap    : 8px;
}

/* ============================================================
   PARTENAIRES — liste studio (préfixe bss-partenaire-)
   ============================================================ */
.bss-partenaire-list {
  list-style: none;
  margin    : 0;
  padding   : 0 var(--bs-pad) var(--bs-pad);
}

.bss-partenaire-item {
  display      : flex;
  align-items  : center;
  gap          : 12px;
  padding      : 14px 0;
  border-bottom: 1px solid var(--bs-sep-light);
}

.bss-partenaire-item__logo {
  width         : 48px;
  height        : 48px;
  border-radius : var(--bs-r-sm);
  overflow      : hidden;
  flex-shrink   : 0;
  background    : rgba(255,255,255,.08);
  display       : flex;
  align-items   : center;
  justify-content: center;
}

.bss-partenaire-item__logo img {
  width     : 100%;
  height    : 100%;
  object-fit: contain;
}

.bss-partenaire-item__initials {
  font-family : var(--bs-font-ui);
  font-size   : 1rem;
  color       : var(--bs-accent);
}

.bss-partenaire-item__info {
  flex       : 1;
  display    : flex;
  flex-direction: column;
  gap        : 2px;
}

.bss-partenaire-item__type {
  font-size : .8rem;
  color     : var(--bs-accent);
  display   : block;
}

.bss-partenaire-item__address {
  font-size  : .78rem;
  color      : rgba(255,255,255,.45);
  display    : block;
}

.bss-partenaire-item__actions {
  display: flex;
  gap    : 8px;
}

/* ============================================================
   FORM — helpers communs (fieldset, row, logo preview)
   ============================================================ */
.bss-form__fieldset {
  border    : 1px solid var(--bs-sep-light);
  border-radius: var(--bs-r-md);
  padding   : 16px;
  margin    : 0 0 16px;
}

.bss-form__legend {
  font-family  : var(--bs-font-ui);
  font-size    : .8rem;
  letter-spacing: 1px;
  text-transform: uppercase;
  color        : var(--bs-accent);
  padding      : 0 8px;
}

.bss-form__row {
  display: flex;
  gap    : 12px;
}

.bss-form__group--sm  { flex: 0 0 80px; }
.bss-form__group--grow { flex: 1; }

.bss-form__hint {
  font-size  : .78rem;
  color      : rgba(255,255,255,.45);
  margin     : 6px 0 0;
}

.bss-form__file {
  display     : block;
  margin-top  : 8px;
  color       : rgba(255,255,255,.7);
  font-size   : .88rem;
}

.bss-form__select-multiple {
  width         : 100%;
  background    : rgba(255,255,255,.08);
  border        : 1px solid var(--bs-sep-light);
  border-radius : var(--bs-r-sm);
  color         : var(--bs-label-inv);
  padding       : 8px;
  font-family   : var(--bs-font-body);
  font-size     : .9rem;
  min-height    : 120px;
}

.bss-form__group--logo {
  display      : flex;
  flex-direction: column;
  align-items  : flex-start;
  gap          : 10px;
}

.bss-logo-preview {
  width         : 100px;
  height        : 100px;
  border-radius : var(--bs-r-sm);
  background    : rgba(255,255,255,.08);
  display       : flex;
  align-items   : center;
  justify-content: center;
  overflow      : hidden;
}

.bss-logo-preview img {
  width     : 100%;
  height    : 100%;
  object-fit: contain;
}

.bss-logo-preview__placeholder {
  font-size: 2rem;
}
```

- [ ] **Commit**

```bash
git add BandStage/assets/css/public.css BandStage/assets/css/studio.css
git commit -m "feat: CSS concerts publics et studio, partenaires studio, helpers form"
```

---

## Task 15 — Admin tab-partenaires.php

**Files:**
- Modify: `BandStage/templates/admin/settings/tab-partenaires.php`

- [ ] **Lire le fichier actuel** pour connaître sa structure exacte.

- [ ] **Réécrire le template** pour gérer les types via `PartenaireService::get_types()` et AJAX :

```php
<?php
/**
 * Onglet Partenaires — gestion des types (table bandstage_partenaire_types).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Domain\Partenaires\PartenaireService;

$service = new PartenaireService();
$types   = $service->get_types();
?>
<h2><?php esc_html_e( 'Types de partenaires', 'bandstage' ); ?></h2>

<table class="bs-admin-types-table widefat striped">
  <thead>
    <tr>
      <th><?php esc_html_e( 'Icône', 'bandstage' ); ?></th>
      <th><?php esc_html_e( 'Nom', 'bandstage' ); ?></th>
      <th><?php esc_html_e( 'Slug', 'bandstage' ); ?></th>
      <th></th>
    </tr>
  </thead>
  <tbody id="bs-types-list">
    <?php foreach ( $types as $t ) : ?>
      <tr data-id="<?php echo esc_attr( $t->id ); ?>">
        <td><?php echo esc_html( $t->icon ); ?></td>
        <td><?php echo esc_html( $t->name ); ?></td>
        <td><code><?php echo esc_html( $t->slug ); ?></code></td>
        <td>
          <button type="button" class="button js-type-delete"
                  data-id="<?php echo esc_attr( $t->id ); ?>"
                  data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
            <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
          </button>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3><?php esc_html_e( 'Ajouter un type', 'bandstage' ); ?></h3>
<form id="bs-type-form">
  <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
  <input type="hidden" name="type_id" value="0">
  <table class="form-table">
    <tr>
      <th><label for="bs-type-name"><?php esc_html_e( 'Nom', 'bandstage' ); ?></label></th>
      <td><input type="text" id="bs-type-name" name="type_name" class="regular-text" required></td>
    </tr>
    <tr>
      <th><label for="bs-type-icon"><?php esc_html_e( 'Icône (emoji)', 'bandstage' ); ?></label></th>
      <td><input type="text" id="bs-type-icon" name="type_icon" class="small-text" placeholder="🎸"></td>
    </tr>
  </table>
  <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Ajouter', 'bandstage' ); ?></button></p>
</form>

<script>
(function($){
  const ajaxUrl = '<?php echo esc_js( admin_url( "admin-ajax.php" ) ); ?>';

  // Ajouter un type
  $('#bs-type-form').on('submit', async function(e){
    e.preventDefault();
    const data = new FormData(this);
    data.set('action', 'bs_partenaire_type_save');
    const res  = await fetch(ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' });
    const json = await res.json();
    if (json.success) {
      const d = json.data;
      $('#bs-types-list').append(
        `<tr data-id="${d.type_id}">
           <td>${d.icon}</td>
           <td>${d.name}</td>
           <td><code>${d.slug}</code></td>
           <td><button type="button" class="button js-type-delete" data-id="${d.type_id}"
               data-nonce="${$('[name=nonce]').val()}"><?php esc_html_e('Supprimer','bandstage'); ?></button></td>
         </tr>`
      );
      this.reset();
    } else {
      alert(json.data?.message || '<?php esc_html_e( "Erreur.", "bandstage" ); ?>');
    }
  });

  // Supprimer un type
  $(document).on('click', '.js-type-delete', async function(){
    if (!confirm('<?php esc_html_e( "Supprimer ce type ?", "bandstage" ); ?>')) return;
    const btn = $(this);
    const body = new FormData();
    body.append('action', 'bs_partenaire_type_save');
    body.append('action', 'bs_partenaire_type_delete');
    body.append('type_id', btn.data('id'));
    body.append('nonce', btn.data('nonce'));
    // Correction : utiliser fetch direct
    const form = new FormData();
    form.append('action', 'bs_partenaire_type_delete');
    form.append('type_id', btn.data('id'));
    form.append('nonce', btn.data('nonce'));
    const res  = await fetch(ajaxUrl, { method: 'POST', body: form, credentials: 'same-origin' });
    const json = await res.json();
    if (json.success) {
      btn.closest('tr').remove();
    } else {
      alert(json.data?.message || '<?php esc_html_e( "Erreur.", "bandstage" ); ?>');
    }
  });
})(jQuery);
</script>
```

- [ ] **Vérifier** : WP Admin → BandStage → Réglages → onglet Partenaires → ajouter et supprimer un type.

- [ ] **Commit**

```bash
git add BandStage/templates/admin/settings/tab-partenaires.php
git commit -m "feat: tab-partenaires gère les types via bandstage_partenaire_types"
```

---

## Task 16 — Tests de bout en bout

- [ ] **Vérifier les tables** : WP Admin → désactiver / réactiver le plugin → vérifier via phpMyAdmin que les 6 tables existent.

- [ ] **Partenaires — créer** : Studio → Partenaires → Ajouter → remplir tous les champs, choisir un logo → Enregistrer → vérifier que l'entrée apparaît dans la liste.

- [ ] **Partenaires — vue publique** : naviguer sur la page `bandstage-partenaires` → vérifier affichage des cards avec logo et adresse.

- [ ] **Concerts — créer** : Studio → Concerts → Ajouter → remplir titre, date, lieu, sélectionner 1 partenaire → Enregistrer → vérifier redirection vers liste.

- [ ] **Concerts — vue publique** : naviguer sur la page `bandstage-concerts` → vérifier que le concert créé apparaît.

- [ ] **Pivot** : vérifier en DB que `bandstage_concert_partenaires` contient bien la ligne `(concert_id, partenaire_id)`.

- [ ] **Suppression en cascade** : supprimer un partenaire → vérifier que les lignes pivot sont supprimées.

- [ ] **Commit final si nécessaire**

```bash
git add -A
git commit -m "fix: corrections suite aux tests de bout en bout"
git push origin main
```
