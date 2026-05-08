# Design — Répertoire & Références (styles musicaux)

**Date :** 2026-05-08
**Projet :** BandStage WordPress Plugin
**Statut :** Approuvé

---

## Contexte

Le plugin BandStage ne dispose d'aucun module pour gérer le répertoire musical du groupe. Ce module ajoute deux tables custom (`bandstage_repertoire` et `bandstage_references`) liées par une table pivot, avec une interface Studio complète (CRUD morceaux + styles sur une seule page) et une vue publique qui présente les morceaux groupés par style musical.

---

## Périmètre

- Création de 3 tables custom
- Nouveau module `BandStage\Domain\Repertoire` (entités + service)
- Shortcode `[bandstage_references]` (vue publique + Studio CRUD)
- Page WordPress créée à l'activation
- CRUD morceaux et styles accessible aux Author+ (`edit_posts`)

---

## 1. Schéma de base de données

Préfixe : `{$wpdb->prefix}bandstage_`

### `bandstage_repertoire`

| Colonne       | Type                                         | Notes                         |
|---------------|----------------------------------------------|-------------------------------|
| id            | BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PK   |                               |
| nom_artiste   | VARCHAR(150) NOT NULL                        |                               |
| nom_morceau   | VARCHAR(150) NOT NULL                        |                               |
| remarque      | TEXT                                         |                               |
| icone_artiste | VARCHAR(10)                                  | Emoji                         |
| created_at    | DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP  |                               |
| updated_at    | DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP  | mis à jour manuellement       |

### `bandstage_references`

| Colonne    | Type                                         | Notes                         |
|------------|----------------------------------------------|-------------------------------|
| id         | BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PK   |                               |
| nom_style  | VARCHAR(100) NOT NULL                        | UNIQUE                        |
| image_url  | VARCHAR(255) NOT NULL DEFAULT ''             | URL de l'illustration         |
| created_at | DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP  |                               |

### `bandstage_rep_ref` (pivot)

| Colonne       | Type                      | Notes                          |
|---------------|---------------------------|--------------------------------|
| repertoire_id | BIGINT UNSIGNED NOT NULL  | FK → bandstage_repertoire.id   |
| reference_id  | BIGINT UNSIGNED NOT NULL  | FK → bandstage_references.id   |
| PRIMARY KEY   | (repertoire_id, reference_id) |                            |

---

## 2. Couche Domain

### Nouveaux fichiers

```
src/Domain/Repertoire/
  Morceau.php           — entité readonly : id, nom_artiste, nom_morceau, remarque,
                          icone_artiste, style_ids[], style_names
                          from_db_row(object $row, array $style_ids = []): self

  Style.php             — entité readonly : id, nom_style, image_url
                          from_db_row(object $row): self

  RepertoireService.php — service unique gérant morceaux ET styles
                          get_all(): Morceau[]           — JOIN style_names via GROUP_CONCAT
                          get(int $id): ?Morceau         — avec style_ids depuis pivot
                          get_grouped_by_style(): array  — pour vue publique
                          get_styles(): Style[]          — pour multi-select formulaire
                          ajax_save()                    — action: bs_morceau_save
                          ajax_delete()                  — action: bs_morceau_delete
                          ajax_style_save()              — action: bs_style_save
                          ajax_style_delete()            — action: bs_style_delete
```

### Modifications de l'existant

| Fichier | Changement |
|---------|-----------|
| `Config.php` | Ajoute `table_repertoire()`, `table_references()`, `table_rep_ref()` + 3 tables dans `create_tables()` |
| `Plugin.php` | Enregistre `RepertoireService` dans `register_domain_services()` ; ajoute page `bs_page_references` dans `create_pages()` |
| `uninstall.php` | DROP des 3 tables dans le bon ordre (pivot en premier) |
| `config/routes.php` | Ajoute `'references' => ['list' => …, 'edit' => …]` |
| `Assets.php` | Ajoute `bs_page_references` dans `is_bandstage_page()` et `is_studio_page()` |
| `Shortcodes.php` | Enregistre `bandstage_references`, ajoute méthode `references()` + helper `references_url()` |
| `assets/js/studio.js` | CRUD morceau (bs_morceau_save, bs_morceau_delete) + CRUD style (bs_style_save, bs_style_delete) |
| `assets/css/public.css` | Styles vue publique répertoire |
| `assets/css/studio.css` | Ajustements studio si nécessaire |

---

## 3. Shortcode `[bandstage_references]`

| bs_view | Condition | Template |
|---------|-----------|----------|
| *(absent)* | Tous visiteurs | `templates/public/references-public.php` |
| `list` | Author+ | `templates/public/studio/repertoire-list.php` |
| `edit` | Author+ | `templates/public/studio/repertoire-edit.php` |

### Helper URL

```php
Shortcodes::references_url(string $view = 'list', int $id = 0): string
```

---

## 4. Templates

### `templates/public/references-public.php`

Vue publique des morceaux groupés par style :

- Une section par style : image du style (si présente) en vignette + titre `nom_style`
- Dans chaque section : liste des morceaux → `icone_artiste nom_artiste — nom_morceau` avec `remarque` en italique si présente
- Section "Sans style" en dernier pour les morceaux sans style associé
- Si aucun morceau : message vide standard `.bs-empty`

### `templates/public/studio/repertoire-list.php`

Interface Studio (Author+) :

- Navbar : titre "Répertoire" + bouton "+ Ajouter un morceau"
- Liste des morceaux : `icone_artiste nom_artiste — nom_morceau`, badges des styles associés, boutons Modifier / Supprimer
- Section "Styles" en bas : tableau (nom_style, image_url, bouton Supprimer) + formulaire inline d'ajout (nom_style + image_url)
- Toast `.bss-toast` pour les retours AJAX

### `templates/public/studio/repertoire-edit.php`

Formulaire morceau (Author+) :

- Champs : icone_artiste (input court), nom_artiste, nom_morceau, remarque (textarea)
- Multi-select styles : `<select multiple>` avec tous les styles disponibles, valeurs pré-sélectionnées en édition
- Actions : Enregistrer + lien Retour
- `method="POST"` fictif, soumission via `fetch` + `FormData` (identique aux autres formulaires Studio)

---

## 5. Accès & sécurité

| Action | Capacité requise |
|--------|-----------------|
| Voir répertoire public | Tous |
| Créer / modifier / supprimer morceau | `edit_posts` (Author+) |
| Créer / supprimer style | `edit_posts` (Author+) |

Tous les handlers AJAX : `check_ajax_referer(BANDSTAGE_NONCE)` + `current_user_can('edit_posts')`.

---

## 6. Suppressions en cascade

- Suppression d'un morceau → DELETE ses lignes dans `bandstage_rep_ref`
- Suppression d'un style → DELETE ses lignes dans `bandstage_rep_ref`
- Désinstallation → DROP `bandstage_rep_ref` EN PREMIER, puis `bandstage_repertoire` et `bandstage_references`

---

## 7. Page créée à l'activation

| Option | Titre | Shortcode | Template |
|--------|-------|-----------|----------|
| `bs_page_references` | BandStage — Répertoire | `[bandstage_references]` | `elementor_canvas` |

---

## 8. Routes config

`config/routes.php` — ajouter :

```php
'references' => [
    'list' => BANDSTAGE_PLUGIN_DIR . 'templates/public/studio/repertoire-list.php',
    'edit' => BANDSTAGE_PLUGIN_DIR . 'templates/public/studio/repertoire-edit.php',
],
```

---

## Hors périmètre

- Upload d'image pour les styles (URL directe seulement)
- Pagination de la liste publique
- Tri manuel des morceaux (drag-and-drop)
- Recherche / filtrage dans le Studio
