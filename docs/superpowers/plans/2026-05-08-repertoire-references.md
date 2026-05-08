# Répertoire & Références — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ajouter un module Répertoire (morceaux) + Références (styles musicaux) avec CRUD Studio sur une seule page et vue publique groupée par style.

**Architecture:** 3 tables custom `$wpdb` (`bandstage_repertoire`, `bandstage_references`, `bandstage_rep_ref`). Service unique `RepertoireService` gère morceaux + styles (même pattern que `PartenaireService` pour partenaires + types). Shortcode `[bandstage_references]` route vers vue publique (visiteurs) ou Studio CRUD (Author+).

**Tech Stack:** PHP 8.1, WordPress `$wpdb`, vanilla JS (fetch + FormData), jQuery (section styles inline), no external dependencies.

---

## File Map

| Action | Path |
|--------|------|
| Modify | `BandStage/src/Core/Config.php` |
| Modify | `BandStage/src/Core/Plugin.php` |
| Modify | `BandStage/config/routes.php` |
| Modify | `BandStage/uninstall.php` |
| Modify | `BandStage/src/Frontend/Assets.php` |
| Modify | `BandStage/src/Frontend/Shortcodes.php` |
| Modify | `BandStage/assets/js/studio.js` |
| Modify | `BandStage/assets/css/public.css` |
| Modify | `BandStage/assets/css/studio.css` |
| Create | `BandStage/src/Domain/Repertoire/Style.php` |
| Create | `BandStage/src/Domain/Repertoire/Morceau.php` |
| Create | `BandStage/src/Domain/Repertoire/RepertoireService.php` |
| Create | `BandStage/templates/public/references-public.php` |
| Create | `BandStage/templates/public/studio/repertoire-list.php` |
| Create | `BandStage/templates/public/studio/repertoire-edit.php` |

---

## Task 1 — DB Tables (Config.php)

**Files:**
- Modify: `BandStage/src/Core/Config.php`

Context: `Config.php` expose des méthodes statiques pour les noms de tables et une méthode `create_tables()` qui appelle `dbDelta()`. Il faut ajouter 3 accesseurs et 3 blocs SQL.

- [ ] **Ajouter les 3 accesseurs de tables après `table_concert_partenaires()`**

```php
public static function table_repertoire(): string {
    global $wpdb;
    return $wpdb->prefix . 'bandstage_repertoire';
}

public static function table_references(): string {
    global $wpdb;
    return $wpdb->prefix . 'bandstage_references';
}

public static function table_rep_ref(): string {
    global $wpdb;
    return $wpdb->prefix . 'bandstage_rep_ref';
}
```

- [ ] **Ajouter les 3 blocs SQL dans `create_tables()` avant `require_once ABSPATH`**

```php
$sql_repertoire = "CREATE TABLE IF NOT EXISTS " . self::table_repertoire() . " (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom_artiste    VARCHAR(150) NOT NULL,
    nom_morceau    VARCHAR(150) NOT NULL,
    remarque       TEXT NOT NULL DEFAULT '',
    icone_artiste  VARCHAR(10) NOT NULL DEFAULT '',
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY nom_artiste (nom_artiste)
) $charset_collate;";

$sql_references = "CREATE TABLE IF NOT EXISTS " . self::table_references() . " (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom_style  VARCHAR(100) NOT NULL,
    image_url  VARCHAR(255) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY nom_style (nom_style)
) $charset_collate;";

$sql_rep_ref = "CREATE TABLE IF NOT EXISTS " . self::table_rep_ref() . " (
    repertoire_id BIGINT UNSIGNED NOT NULL,
    reference_id  BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (repertoire_id, reference_id)
) $charset_collate;";
```

- [ ] **Ajouter les 3 appels dbDelta() après `dbDelta( $sql_pivot );`**

```php
dbDelta( $sql_repertoire );
dbDelta( $sql_references );
dbDelta( $sql_rep_ref );
```

- [ ] **Vérifier manuellement** : activer le plugin → phpMyAdmin → les 3 tables apparaissent.

- [ ] **Commit**

```bash
git add BandStage/src/Core/Config.php
git commit -m "feat: tables bandstage_repertoire, bandstage_references, bandstage_rep_ref"
```

---

## Task 2 — Style entity

**Files:**
- Create: `BandStage/src/Domain/Repertoire/Style.php`

- [ ] **Créer `Style.php`**

```php
<?php
/**
 * Entité Style (table bandstage_references).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Repertoire;

defined( 'ABSPATH' ) || exit;

class Style {

    public function __construct(
        public readonly int    $id,
        public readonly string $nom_style,
        public readonly string $image_url,
    ) {}

    public static function from_db_row( object $row ): self {
        return new self(
            id:        (int)    $row->id,
            nom_style: (string) $row->nom_style,
            image_url: (string) ( $row->image_url ?? '' ),
        );
    }
}
```

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Repertoire/Style.php
git commit -m "feat: Style entity (bandstage_references)"
```

---

## Task 3 — Morceau entity

**Files:**
- Create: `BandStage/src/Domain/Repertoire/Morceau.php`

- [ ] **Créer `Morceau.php`**

```php
<?php
/**
 * Entité Morceau (table bandstage_repertoire).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Repertoire;

defined( 'ABSPATH' ) || exit;

class Morceau {

    public function __construct(
        public readonly int    $id,
        public readonly string $nom_artiste,
        public readonly string $nom_morceau,
        public readonly string $remarque,
        public readonly string $icone_artiste,
        /** @var int[] IDs des styles associés */
        public readonly array  $style_ids,
        /** Noms des styles séparés par des virgules (chargé via GROUP_CONCAT). */
        public readonly string $style_names = '',
    ) {}

    public static function from_db_row( object $row, array $style_ids = [] ): self {
        return new self(
            id:            (int)    $row->id,
            nom_artiste:   (string) $row->nom_artiste,
            nom_morceau:   (string) $row->nom_morceau,
            remarque:      (string) ( $row->remarque      ?? '' ),
            icone_artiste: (string) ( $row->icone_artiste ?? '' ),
            style_ids:     $style_ids,
            style_names:   (string) ( $row->style_names   ?? '' ),
        );
    }
}
```

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Repertoire/Morceau.php
git commit -m "feat: Morceau entity (bandstage_repertoire)"
```

---

## Task 4 — RepertoireService (read methods)

**Files:**
- Create: `BandStage/src/Domain/Repertoire/RepertoireService.php`

Context: Même pattern que `PartenaireService`. `get_all()` fait un GROUP_CONCAT pour les noms de styles. `get_grouped_by_style()` retourne un tableau `[style_key => [label, image_url, items[]]]` pour la vue publique, avec une clé `_none` pour les morceaux sans style.

- [ ] **Créer `RepertoireService.php` avec les méthodes de lecture**

```php
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

        // Morceaux avec leur premier style (pour le groupement)
        $rows = $wpdb->get_results(
            "SELECT r.*, s.id AS style_id, s.nom_style, s.image_url AS style_image_url
             FROM {$tr} r
             LEFT JOIN {$tpiv} rr ON rr.repertoire_id = r.id
             LEFT JOIN {$tref} s  ON s.id = rr.reference_id
             ORDER BY s.nom_style ASC, r.nom_artiste ASC, r.nom_morceau ASC"
        );

        $grouped  = [];
        $seen_ids = []; // éviter les doublons si un morceau a plusieurs styles

        foreach ( $rows ?: [] as $row ) {
            $key = $row->style_id ? 'style_' . $row->style_id : '_none';

            if ( ! isset( $grouped[ $key ] ) ) {
                $grouped[ $key ] = [
                    'label'     => $row->nom_style ?: __( 'Sans style', 'bandstage' ),
                    'image_url' => (string) ( $row->style_image_url ?? '' ),
                    'items'     => [],
                ];
            }

            // Un morceau lié à plusieurs styles apparaîtra dans chaque section,
            // mais une seule fois par section.
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
```

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Repertoire/RepertoireService.php
git commit -m "feat: RepertoireService read methods"
```

---

## Task 5 — RepertoireService (AJAX handlers)

**Files:**
- Modify: `BandStage/src/Domain/Repertoire/RepertoireService.php`

Context: Ajouter les 4 handlers AJAX à la suite des méthodes de lecture. Pattern identique à `ConcertService::ajax_save()` et `PartenaireService::ajax_type_save()`. La fermeture `}` de classe doit rester en fin de fichier.

- [ ] **Ajouter les 4 méthodes AJAX dans `RepertoireService.php`, avant la `}` de fermeture de classe**

```php
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
            wp_send_json_error( [ 'message' => __( 'Ce style existe déjà.', 'bandstage' ) ] );
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
```

- [ ] **Commit**

```bash
git add BandStage/src/Domain/Repertoire/RepertoireService.php
git commit -m "feat: RepertoireService AJAX handlers (morceau + style)"
```

---

## Task 6 — Infrastructure wiring

**Files:**
- Modify: `BandStage/src/Core/Plugin.php`
- Modify: `BandStage/config/routes.php`
- Modify: `BandStage/uninstall.php`
- Modify: `BandStage/src/Frontend/Assets.php`

### Plugin.php

- [ ] **Ajouter le `use` pour `RepertoireService` après celui de `PartenaireService`**

```php
use BandStage\Domain\Repertoire\RepertoireService;
```

- [ ] **Ajouter `new RepertoireService()` dans `register_domain_services()`**

Remplacer :
```php
$services = [
    new TchacheService(),
    new NewsService(),
    new PartenaireService(),
    new ConcertService(),
    new MemberService(),
    new NotificationService(),
    new LineupService(),
];
```
Par :
```php
$services = [
    new TchacheService(),
    new NewsService(),
    new PartenaireService(),
    new ConcertService(),
    new RepertoireService(),
    new MemberService(),
    new NotificationService(),
    new LineupService(),
];
```

- [ ] **Ajouter la page `bs_page_references` dans `create_pages()`**

Ajouter dans le tableau `$pages` :
```php
'references'  => [ 'title' => 'BandStage — Répertoire',  'shortcode' => '[bandstage_references]',  'template' => 'elementor_canvas' ],
```

### config/routes.php

- [ ] **Ajouter l'entrée `references`**

```php
'references' => [
    'list' => $tpl . 'repertoire-list.php',
    'edit' => $tpl . 'repertoire-edit.php',
],
```

### uninstall.php

- [ ] **Ajouter `bs_page_references` dans les deux tableaux `$options` et `$page_options`**

Dans `$options` (ligne ~29), ajouter `'bs_page_references'` à la fin de la ligne `bs_page_*`.

Dans `$page_options` (ligne ~41), ajouter `'bs_page_references'`.

- [ ] **Ajouter le DROP des 3 nouvelles tables après la section 4 (pivot en premier)**

```php
// -------------------------------------------------------------------------
// 5b. Tables répertoire
// -------------------------------------------------------------------------
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_rep_ref" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_repertoire" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bandstage_references" );
```

### Assets.php

- [ ] **Ajouter `bs_page_references` dans `$page_options` de `is_bandstage_page()`**

Remplacer :
```php
$page_options = [
    'bs_page_accueil', 'bs_page_tchache', 'bs_page_profil',
    'bs_page_studio',  'bs_page_partenaires', 'bs_page_groupe', 'bs_page_concerts',
];
```
Par :
```php
$page_options = [
    'bs_page_accueil', 'bs_page_tchache', 'bs_page_profil',
    'bs_page_studio',  'bs_page_partenaires', 'bs_page_groupe', 'bs_page_concerts', 'bs_page_references',
];
```

- [ ] **Ajouter `bandstage_references` dans le tableau `$shortcodes` du fallback de `is_bandstage_page()`**

```php
$shortcodes = [
    'bandstage_homepage', 'bandstage_tchache', 'bandstage_profil',
    'bandstage_studio',   'bandstage_partenaires', 'bandstage_groupe', 'bandstage_concerts', 'bandstage_references',
];
```

- [ ] **Ajouter `bs_page_references` dans `$studio_pages`** (propriété de classe ligne 18)

Remplacer :
```php
private array $studio_pages = [ 'bs_page_studio', 'bs_page_partenaires', 'bs_page_groupe', 'bs_page_concerts' ];
```
Par :
```php
private array $studio_pages = [ 'bs_page_studio', 'bs_page_partenaires', 'bs_page_groupe', 'bs_page_concerts', 'bs_page_references' ];
```

- [ ] **Ajouter `bandstage_references` dans le fallback de `is_studio_page()`**

```php
$studio_shortcodes = [ 'bandstage_studio', 'bandstage_partenaires', 'bandstage_groupe', 'bandstage_concerts', 'bandstage_references' ];
```

- [ ] **Commit**

```bash
git add BandStage/src/Core/Plugin.php BandStage/config/routes.php BandStage/uninstall.php BandStage/src/Frontend/Assets.php
git commit -m "feat: wiring RepertoireService, page références, routes, uninstall, assets"
```

---

## Task 7 — Shortcodes.php

**Files:**
- Modify: `BandStage/src/Frontend/Shortcodes.php`

- [ ] **Ajouter le `use` pour `RepertoireService` après celui de `PartenaireService`**

```php
use BandStage\Domain\Repertoire\RepertoireService;
```

- [ ] **Enregistrer le shortcode dans `register()`**

Ajouter :
```php
add_shortcode( 'bandstage_references', [ $this, 'references' ] );
```

- [ ] **Ajouter la méthode `references()` avant `groupe()`**

```php
// -------------------------------------------------------------------------
// [bandstage_references]
//   Visiteur  → liste publique des morceaux groupés par style
//   Auteur+   → CRUD morceaux + gestion styles
// -------------------------------------------------------------------------

public function references(): string {
    $service = new RepertoireService();
    ob_start();
    Assets::maybe_inject_dynamic_css();

    if ( ! current_user_can( 'edit_posts' ) ) {
        $grouped = $service->get_grouped_by_style();
        include BANDSTAGE_PLUGIN_DIR . 'templates/public/references-public.php';
        return ob_get_clean();
    }

    $routes = include BANDSTAGE_PLUGIN_DIR . 'config/routes.php';
    $view   = sanitize_key( $_GET['bs_view'] ?? 'list' );

    if ( 'edit' === $view ) {
        $morceau_id  = absint( $_GET['bs_id'] ?? 0 );
        $morceau     = $morceau_id ? $service->get( $morceau_id ) : null;
        $all_styles  = $service->get_styles();
        include $routes['references']['edit'];
    } else {
        $morceaux   = $service->get_all();
        $all_styles = $service->get_styles();
        include $routes['references']['list'];
    }

    return ob_get_clean();
}
```

- [ ] **Ajouter le helper `references_url()` avec les autres helpers**

```php
public static function references_url( string $view = 'list', int $id = 0 ): string {
    $url = get_permalink( (int) get_option( 'bs_page_references' ) );
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
git commit -m "feat: shortcode bandstage_references + references_url()"
```

---

## Task 8 — Template vue publique

**Files:**
- Create: `BandStage/templates/public/references-public.php`

- [ ] **Créer le template**

```php
<?php
/**
 * [bandstage_references] — vue publique (morceaux groupés par style).
 *
 * @var array $grouped  [ key => [ label, image_url, items:Morceau[] ] ]
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bs-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php esc_html_e( 'Répertoire', 'bandstage' ); ?></h1>
  </header>

  <?php if ( empty( $grouped ) ) : ?>
    <div class="bs-empty">
      <p><?php esc_html_e( 'Aucun morceau pour le moment.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <?php foreach ( $grouped as $group ) : ?>
      <section class="bs-ref-section">
        <div class="bs-ref-section__header">
          <?php if ( $group['image_url'] ) : ?>
            <img class="bs-ref-section__img"
                 src="<?php echo esc_url( $group['image_url'] ); ?>"
                 alt="<?php echo esc_attr( $group['label'] ); ?>"
                 loading="lazy">
          <?php endif; ?>
          <h2 class="bs-ref-section__title"><?php echo esc_html( $group['label'] ); ?></h2>
        </div>
        <ul class="bs-ref-list">
          <?php foreach ( $group['items'] as $m ) : ?>
            <li class="bs-ref-item">
              <?php if ( $m->icone_artiste ) : ?>
                <span class="bs-ref-item__icon"><?php echo esc_html( $m->icone_artiste ); ?></span>
              <?php endif; ?>
              <span class="bs-ref-item__artiste"><?php echo esc_html( $m->nom_artiste ); ?></span>
              <span class="bs-ref-item__sep">—</span>
              <span class="bs-ref-item__morceau"><?php echo esc_html( $m->nom_morceau ); ?></span>
              <?php if ( $m->remarque ) : ?>
                <em class="bs-ref-item__remarque"><?php echo esc_html( $m->remarque ); ?></em>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
```

- [ ] **Commit**

```bash
git add BandStage/templates/public/references-public.php
git commit -m "feat: template vue publique répertoire groupé par style"
```

---

## Task 9 — Template Studio list

**Files:**
- Create: `BandStage/templates/public/studio/repertoire-list.php`

- [ ] **Créer le template**

```php
<?php
/**
 * [bandstage_references] bs_view=list — gestion du répertoire (Auteur+).
 *
 * @var \BandStage\Domain\Repertoire\Morceau[] $morceaux
 * @var \BandStage\Domain\Repertoire\Style[]   $all_styles
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <span class="bss-navbar__title"><?php esc_html_e( 'Répertoire', 'bandstage' ); ?></span>
    <a href="<?php echo esc_url( Shortcodes::references_url( 'edit' ) ); ?>"
       class="bss-navbar__action bss-btn bss-btn--primary">
      <?php esc_html_e( '+ Ajouter', 'bandstage' ); ?>
    </a>
  </nav>

  <?php if ( empty( $morceaux ) ) : ?>
    <div class="bss-empty">
      <p><?php esc_html_e( 'Aucun morceau. Ajoutez le premier !', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bss-morceau-list">
      <?php foreach ( $morceaux as $m ) : ?>
        <li class="bss-morceau-item" data-id="<?php echo esc_attr( $m->id ); ?>">
          <div class="bss-morceau-item__info">
            <?php if ( $m->icone_artiste ) : ?>
              <span class="bss-morceau-item__icon"><?php echo esc_html( $m->icone_artiste ); ?></span>
            <?php endif; ?>
            <strong><?php echo esc_html( $m->nom_artiste ); ?> — <?php echo esc_html( $m->nom_morceau ); ?></strong>
            <?php if ( $m->style_names ) : ?>
              <span class="bss-badge"><?php echo esc_html( $m->style_names ); ?></span>
            <?php endif; ?>
            <?php if ( $m->remarque ) : ?>
              <em class="bss-morceau-item__remarque"><?php echo esc_html( $m->remarque ); ?></em>
            <?php endif; ?>
          </div>
          <div class="bss-morceau-item__actions">
            <a href="<?php echo esc_url( Shortcodes::references_url( 'edit', $m->id ) ); ?>"
               class="bss-btn bss-btn--sm bss-btn--ghost">
              <?php esc_html_e( 'Modifier', 'bandstage' ); ?>
            </a>
            <button
              type="button"
              class="bss-btn bss-btn--sm bss-btn--danger js-morceau-delete"
              data-id="<?php echo esc_attr( $m->id ); ?>"
              data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
              <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
            </button>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <!-- ===== SECTION STYLES ===== -->
  <div class="bss-styles-section">
    <h3 class="bss-styles-section__title"><?php esc_html_e( 'Styles musicaux', 'bandstage' ); ?></h3>

    <table class="bss-styles-table">
      <thead>
        <tr>
          <th><?php esc_html_e( 'Style', 'bandstage' ); ?></th>
          <th><?php esc_html_e( 'Image', 'bandstage' ); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody id="bss-styles-list">
        <?php foreach ( $all_styles as $s ) : ?>
          <tr data-id="<?php echo esc_attr( $s->id ); ?>">
            <td><?php echo esc_html( $s->nom_style ); ?></td>
            <td>
              <?php if ( $s->image_url ) : ?>
                <img src="<?php echo esc_url( $s->image_url ); ?>"
                     alt="<?php echo esc_attr( $s->nom_style ); ?>"
                     class="bss-styles-table__thumb" loading="lazy">
              <?php else : ?>
                <span class="bss-styles-table__noimg">—</span>
              <?php endif; ?>
            </td>
            <td>
              <button type="button" class="bss-btn bss-btn--sm bss-btn--danger js-style-delete"
                      data-id="<?php echo esc_attr( $s->id ); ?>"
                      data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
                <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h4 class="bss-styles-section__subtitle"><?php esc_html_e( 'Ajouter un style', 'bandstage' ); ?></h4>
    <form id="bss-style-form">
      <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--grow">
          <label for="bss-style-name" class="bss-form__label"><?php esc_html_e( 'Nom du style', 'bandstage' ); ?></label>
          <input type="text" id="bss-style-name" name="nom_style" class="bss-form__input" required
                 placeholder="<?php esc_attr_e( 'Rock, Jazz, Blues…', 'bandstage' ); ?>">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bss-style-img" class="bss-form__label"><?php esc_html_e( 'URL image', 'bandstage' ); ?></label>
          <input type="url" id="bss-style-img" name="image_url" class="bss-form__input"
                 placeholder="https://…">
        </div>
      </div>
      <p><button type="submit" class="bss-btn bss-btn--primary"><?php esc_html_e( 'Ajouter', 'bandstage' ); ?></button></p>
    </form>
  </div>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
```

- [ ] **Commit**

```bash
git add BandStage/templates/public/studio/repertoire-list.php
git commit -m "feat: template Studio liste répertoire + section styles"
```

---

## Task 10 — Template Studio edit

**Files:**
- Create: `BandStage/templates/public/studio/repertoire-edit.php`

- [ ] **Créer le template**

```php
<?php
/**
 * [bandstage_references] bs_view=edit — formulaire morceau (Auteur+).
 *
 * @var \BandStage\Domain\Repertoire\Morceau|null $morceau
 * @var \BandStage\Domain\Repertoire\Style[]      $all_styles
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$is_edit       = $morceau !== null;
$mid           = $is_edit ? $morceau->id            : 0;
$nom_artiste   = $is_edit ? $morceau->nom_artiste   : '';
$nom_morceau   = $is_edit ? $morceau->nom_morceau   : '';
$remarque      = $is_edit ? $morceau->remarque      : '';
$icone_artiste = $is_edit ? $morceau->icone_artiste : '';
$selected_ids  = $is_edit ? $morceau->style_ids     : [];

$page_title = $is_edit
  ? esc_html__( 'Modifier le morceau', 'bandstage' )
  : esc_html__( 'Nouveau morceau', 'bandstage' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <a href="<?php echo esc_url( Shortcodes::references_url( 'list' ) ); ?>"
       class="bss-navbar__back">← <?php esc_html_e( 'Retour', 'bandstage' ); ?></a>
    <span class="bss-navbar__title"><?php echo $page_title; ?></span>
    <button type="submit" form="bss-morceau-form"
            class="bss-navbar__action bss-btn bss-btn--primary js-morceau-save">
      <?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
    </button>
  </nav>

  <form id="bss-morceau-form" class="bss-form"
        data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="morceau_id" value="<?php echo esc_attr( $mid ); ?>">

    <!-- Artiste + icône -->
    <div class="bss-form__row">
      <div class="bss-form__group bss-form__group--sm">
        <label for="bs-m-icone" class="bss-form__label"><?php esc_html_e( 'Icône', 'bandstage' ); ?></label>
        <input type="text" id="bs-m-icone" name="icone_artiste" class="bss-form__input"
               value="<?php echo esc_attr( $icone_artiste ); ?>"
               placeholder="🎸" maxlength="10">
      </div>
      <div class="bss-form__group bss-form__group--grow">
        <label for="bs-m-artiste" class="bss-form__label"><?php esc_html_e( 'Artiste / Groupe', 'bandstage' ); ?> *</label>
        <input type="text" id="bs-m-artiste" name="nom_artiste" class="bss-form__input"
               value="<?php echo esc_attr( $nom_artiste ); ?>" required
               placeholder="<?php esc_attr_e( 'AC/DC, Nina Simone…', 'bandstage' ); ?>">
      </div>
    </div>

    <!-- Titre du morceau -->
    <div class="bss-form__group">
      <label for="bs-m-morceau" class="bss-form__label"><?php esc_html_e( 'Titre du morceau', 'bandstage' ); ?> *</label>
      <input type="text" id="bs-m-morceau" name="nom_morceau" class="bss-form__input"
             value="<?php echo esc_attr( $nom_morceau ); ?>" required
             placeholder="<?php esc_attr_e( 'Highway to Hell…', 'bandstage' ); ?>">
    </div>

    <!-- Remarque -->
    <div class="bss-form__group">
      <label for="bs-m-remarque" class="bss-form__label"><?php esc_html_e( 'Remarque', 'bandstage' ); ?></label>
      <textarea id="bs-m-remarque" name="remarque" class="bss-form__textarea" rows="3"
                placeholder="<?php esc_attr_e( 'Tonalité, arrangement particulier…', 'bandstage' ); ?>"><?php echo esc_textarea( $remarque ); ?></textarea>
    </div>

    <!-- Styles associés -->
    <?php if ( ! empty( $all_styles ) ) : ?>
      <div class="bss-form__group">
        <label for="bs-m-styles" class="bss-form__label"><?php esc_html_e( 'Styles musicaux', 'bandstage' ); ?></label>
        <select id="bs-m-styles" name="style_ids[]" multiple class="bss-form__select-multiple">
          <?php foreach ( $all_styles as $s ) : ?>
            <option value="<?php echo esc_attr( $s->id ); ?>"
              <?php echo in_array( $s->id, $selected_ids, true ) ? 'selected' : ''; ?>>
              <?php echo esc_html( $s->nom_style ); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="bss-form__hint"><?php esc_html_e( 'Ctrl+clic (ou Cmd+clic) pour sélectionner plusieurs styles.', 'bandstage' ); ?></p>
      </div>
    <?php endif; ?>

  </form>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
```

- [ ] **Commit**

```bash
git add BandStage/templates/public/studio/repertoire-edit.php
git commit -m "feat: template Studio formulaire morceau"
```

---

## Task 11 — studio.js (CRUD morceau + style)

**Files:**
- Modify: `BandStage/assets/js/studio.js`

Context: Ajouter 4 sections à la suite du fichier existant (sections 11 à 14). Pattern : delete via `bsAjax()`, save via `fetch + FormData` (pour le multi-select style_ids[]).

- [ ] **Ajouter à la fin de `studio.js`**

```js
// ============================================================
// 11. MORCEAU — delete
// ============================================================
document.querySelectorAll('.js-morceau-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm(BsPublic.i18n.confirm)) return;
    btn.disabled = true;
    const json = await bsAjax('bs_morceau_delete', { morceau_id: btn.dataset.id });

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      const row = btn.closest('.bss-morceau-item');
      if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      btn.disabled = false;
    }
  });
});

// ============================================================
// 12. MORCEAU — save (FormData pour multi-select style_ids[])
// ============================================================
(function () {
  const form = document.getElementById('bss-morceau-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.set('action', 'bs_morceau_save');
    formData.set('nonce', BsPublic.nonce);
    const btn = document.querySelector('[form="bss-morceau-form"]');
    if (btn) btn.disabled = true;

    try {
      const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' });
      const json = await res.json();
      if (btn) btn.disabled = false;
      if (json.success) {
        BsToast.show(json.data.message, 'success');
        setTimeout(() => { window.location.href = json.data.redirect; }, 900);
      } else {
        BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      }
    } catch {
      if (btn) btn.disabled = false;
      BsToast.show(BsPublic.i18n.error, 'error');
    }
  });
})();

// ============================================================
// 13. STYLE — delete
// ============================================================
(function () {
  const nonce = '<?php echo esc_js( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>';

  $(document).on('click', '.js-style-delete', async function () {
    if (!confirm(BsPublic.i18n.confirm)) return;
    const btn  = $(this);
    const form = new FormData();
    form.append('action', 'bs_style_delete');
    form.append('style_id', btn.data('id'));
    form.append('nonce', btn.data('nonce'));
    try {
      const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: form, credentials: 'same-origin' });
      const json = await res.json();
      if (json.success) {
        BsToast.show(json.data.message, 'success');
        btn.closest('tr').remove();
      } else {
        BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      }
    } catch {
      BsToast.show(BsPublic.i18n.error, 'error');
    }
  });
})();

// ============================================================
// 14. STYLE — save (inline form)
// ============================================================
(function ($) {
  const styleForm = document.getElementById('bss-style-form');
  if (!styleForm) return;

  const nonce = styleForm.querySelector('[name="nonce"]')?.value || BsPublic.nonce;

  styleForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(styleForm);
    data.set('action', 'bs_style_save');
    data.set('nonce', nonce);
    try {
      const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' });
      const json = await res.json();
      if (json.success) {
        const d   = json.data;
        const img = d.image_url
          ? `<img src="${$('<span>').text(d.image_url).html()}" class="bss-styles-table__thumb" loading="lazy">`
          : '<span class="bss-styles-table__noimg">—</span>';
        $('#bss-styles-list').append(
          `<tr data-id="${d.style_id}">
             <td>${$('<span>').text(d.nom_style).html()}</td>
             <td>${img}</td>
             <td><button type="button" class="bss-btn bss-btn--sm bss-btn--danger js-style-delete"
                 data-id="${d.style_id}" data-nonce="${nonce}">
               <?php echo esc_js( __( 'Supprimer', 'bandstage' ) ); ?></button></td>
           </tr>`
        );
        styleForm.reset();
        BsToast.show(json.data.message, 'success');
      } else {
        BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      }
    } catch {
      BsToast.show(BsPublic.i18n.error, 'error');
    }
  });
})(jQuery);
```

**Note :** Les sections 13 et 14 utilisent jQuery (déjà chargé sur les pages Studio) et `<?php ... ?>` inline — ce fichier `.js` est inclus via PHP côté WordPress, donc les tags PHP dans `.js` ne fonctionneront PAS (WordPress sert les assets statiques). Il faut utiliser `BsPublic.nonce` au lieu de `wp_create_nonce()` inline, et `BsPublic.i18n.confirm` pour les confirmations. Les labels traduits sont déjà dans `BsPublic.i18n`. Adapter le code :

```js
// ============================================================
// 13. STYLE — delete
// ============================================================
$(document).on('click', '.js-style-delete', async function () {
  if (!confirm(BsPublic.i18n.confirm)) return;
  const btn  = $(this);
  const form = new FormData();
  form.append('action', 'bs_style_delete');
  form.append('style_id', btn.data('id'));
  form.append('nonce', BsPublic.nonce);
  try {
    const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: form, credentials: 'same-origin' });
    const json = await res.json();
    if (json.success) {
      BsToast.show(json.data.message, 'success');
      btn.closest('tr').remove();
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
    }
  } catch {
    BsToast.show(BsPublic.i18n.error, 'error');
  }
});

// ============================================================
// 14. STYLE — save (inline form)
// ============================================================
(function ($) {
  const styleForm = document.getElementById('bss-style-form');
  if (!styleForm) return;

  styleForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(styleForm);
    data.set('action', 'bs_style_save');
    data.set('nonce', BsPublic.nonce);
    try {
      const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' });
      const json = await res.json();
      if (json.success) {
        const d   = json.data;
        const img = d.image_url
          ? `<img src="${$('<span>').text(d.image_url).html()}" class="bss-styles-table__thumb" loading="lazy">`
          : '<span class="bss-styles-table__noimg">—</span>';
        $('#bss-styles-list').append(
          `<tr data-id="${d.style_id}">
             <td>${$('<span>').text(d.nom_style).html()}</td>
             <td>${img}</td>
             <td><button type="button" class="bss-btn bss-btn--sm bss-btn--danger js-style-delete"
                 data-id="${d.style_id}" data-nonce="${BsPublic.nonce}">Supprimer</button></td>
           </tr>`
        );
        styleForm.reset();
        BsToast.show(json.data.message, 'success');
      } else {
        BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      }
    } catch {
      BsToast.show(BsPublic.i18n.error, 'error');
    }
  });
})(jQuery);
```

- [ ] **Commit**

```bash
git add BandStage/assets/js/studio.js
git commit -m "feat: studio.js CRUD morceau et style"
```

---

## Task 12 — CSS

**Files:**
- Modify: `BandStage/assets/css/public.css`
- Modify: `BandStage/assets/css/studio.css`

### public.css — Répertoire public

- [ ] **Ajouter à la fin de `public.css`**

```css
/* ============================================================
   RÉPERTOIRE PUBLIC
   ============================================================ */
.bs-ref-section {
  padding      : var(--bs-pad);
  max-width    : var(--bs-max-w);
  margin-inline: auto;
  margin-bottom: 32px;
}

.bs-ref-section__header {
  display    : flex;
  align-items: center;
  gap        : 14px;
  margin-bottom: 16px;
}

.bs-ref-section__img {
  width        : 56px;
  height       : 56px;
  object-fit   : cover;
  border-radius: var(--bs-r-md);
  flex-shrink  : 0;
}

.bs-ref-section__title {
  font-family   : var(--bs-font-brand);
  font-size     : 1.3rem;
  color         : var(--bs-accent);
  margin        : 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.bs-ref-list {
  list-style: none;
  margin    : 0;
  padding   : 0;
  display   : flex;
  flex-direction: column;
  gap       : 10px;
}

.bs-ref-item {
  background    : var(--bs-surface-3);
  border-radius : var(--bs-r-card);
  padding       : 12px 16px;
  display       : flex;
  align-items   : baseline;
  flex-wrap     : wrap;
  gap           : 6px;
  backdrop-filter: blur(8px);
}

.bs-ref-item__icon {
  font-size  : 1.2rem;
  flex-shrink: 0;
}

.bs-ref-item__artiste {
  font-family: var(--bs-font-ui);
  font-size  : .9rem;
  color      : var(--bs-accent);
  font-weight: 600;
}

.bs-ref-item__sep {
  color  : rgba(255,255,255,.35);
  padding: 0 2px;
}

.bs-ref-item__morceau {
  color    : var(--bs-label-inv);
  font-size: .95rem;
}

.bs-ref-item__remarque {
  font-size: .8rem;
  color    : rgba(255,255,255,.5);
  flex-basis: 100%;
  margin-top: 2px;
}
```

### studio.css — Section styles + liste morceaux

- [ ] **Ajouter à la fin de `studio.css`**

```css
/* ============================================================
   RÉPERTOIRE STUDIO — liste morceaux
   ============================================================ */
.bss-morceau-list {
  list-style: none;
  margin    : 0;
  padding   : var(--bs-pad);
  max-width : var(--bs-max-w);
  margin-inline: auto;
  display   : flex;
  flex-direction: column;
  gap       : 10px;
}

.bss-morceau-item {
  background    : var(--bs-surface-3);
  border-radius : var(--bs-r-card);
  padding       : 14px 16px;
  backdrop-filter: blur(8px);
  display       : flex;
  align-items   : center;
  gap           : 14px;
}

.bss-morceau-item__info {
  flex     : 1;
  min-width: 0;
  display  : flex;
  flex-wrap: wrap;
  align-items: center;
  gap      : 8px;
}

.bss-morceau-item__icon {
  font-size  : 1.2rem;
  flex-shrink: 0;
}

.bss-morceau-item__remarque {
  font-size : .8rem;
  color     : rgba(255,255,255,.5);
  flex-basis: 100%;
}

.bss-morceau-item__actions {
  display   : flex;
  gap       : 8px;
  flex-shrink: 0;
}

/* ============================================================
   RÉPERTOIRE STUDIO — section styles
   ============================================================ */
.bss-styles-section {
  padding      : var(--bs-pad);
  max-width    : var(--bs-max-w);
  margin-inline: auto;
  margin-top   : 32px;
  border-top   : 1px solid var(--bs-sep-light);
}

.bss-styles-section__title {
  font-family   : var(--bs-font-ui);
  font-size     : .85rem;
  letter-spacing: 1px;
  text-transform: uppercase;
  color         : var(--bs-accent);
  margin        : 0 0 16px;
}

.bss-styles-section__subtitle {
  font-family   : var(--bs-font-ui);
  font-size     : .8rem;
  letter-spacing: 1px;
  text-transform: uppercase;
  color         : rgba(255,255,255,.6);
  margin        : 20px 0 12px;
}

.bss-styles-table {
  width          : 100%;
  border-collapse: collapse;
  margin-bottom  : 16px;
  font-size      : .9rem;
  color          : var(--bs-label-inv);
}

.bss-styles-table th,
.bss-styles-table td {
  padding      : 8px 10px;
  border-bottom: 1px solid var(--bs-sep-light);
  text-align   : left;
}

.bss-styles-table th {
  font-family   : var(--bs-font-ui);
  font-size     : .75rem;
  letter-spacing: 1px;
  text-transform: uppercase;
  color         : rgba(255,255,255,.5);
}

.bss-styles-table__thumb {
  width        : 40px;
  height       : 40px;
  object-fit   : cover;
  border-radius: var(--bs-r-sm);
  display      : block;
}

.bss-styles-table__noimg {
  color: rgba(255,255,255,.3);
}
```

- [ ] **Commit**

```bash
git add BandStage/assets/css/public.css BandStage/assets/css/studio.css
git commit -m "feat: CSS répertoire public et studio"
```

---

## Self-review

**Spec coverage :**
- ✅ Tables : 3 tables créées dans Config.php (Task 1)
- ✅ Style entity : Task 2
- ✅ Morceau entity : Task 3
- ✅ RepertoireService read : Task 4
- ✅ RepertoireService AJAX : Task 5
- ✅ Plugin wiring + page activation : Task 6
- ✅ Shortcode + URL helper : Task 7
- ✅ Vue publique groupée par style + "Sans style" en dernier : Task 8
- ✅ Studio list + section styles : Task 9
- ✅ Studio edit formulaire multi-select : Task 10
- ✅ JS CRUD morceau + style : Task 11
- ✅ CSS : Task 12
- ✅ Cascade delete (morceau → pivot, style → pivot) : Tasks 5+6
- ✅ uninstall DROP pivot en premier : Task 6

**Placeholder scan :** aucun.

**Type consistency :** `references_url()` utilisé dans Tasks 7, 9, 10. `morceau_id` utilisé partout dans Task 5 et 11. `style_id` cohérent dans Tasks 5 et 11.
