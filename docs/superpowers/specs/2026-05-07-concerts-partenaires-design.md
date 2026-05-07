# Design — Concerts & Partenaires en base de données

**Date :** 2026-05-07
**Projet :** BandStage WordPress Plugin
**Statut :** Approuvé

---

## Contexte

Le système de partenaires actuel repose sur le CPT WordPress `bs_partenaire` et la taxonomie `bs_type_partenaire`. Cette approche sera remplacée par des tables SQL custom plus flexibles. Un nouveau module **Concerts** sera créé dans la foulée, avec une relation 0→n vers les partenaires.

---

## Périmètre

- Suppression du CPT `bs_partenaire` et de la taxonomie `bs_type_partenaire` (coupe nette, pas de migration de données)
- Création de 4 tables custom
- Nouveau module Concerts (CRUD Studio + vue publique)
- Réécriture complète du module Partenaires (CRUD Studio + vue publique)
- Upload de logo partenaire vers `/uploads/bandstage/logos/` (hors médiathèque WP)

---

## 1. Schéma de base de données

Préfixe : `{$wpdb->prefix}bandstage_`

### `bandstage_partenaire_types`

| Colonne    | Type                     | Notes              |
|------------|--------------------------|--------------------|
| id         | BIGINT UNSIGNED PK AI    |                    |
| name       | VARCHAR(100) NOT NULL    |                    |
| slug       | VARCHAR(100) NOT NULL    | UNIQUE             |
| icon       | VARCHAR(10)              | Emoji              |
| created_at | DATETIME                 | DEFAULT NOW()      |

### `bandstage_partenaires`

| Colonne     | Type                     | Notes                          |
|-------------|--------------------------|--------------------------------|
| id          | BIGINT UNSIGNED PK AI    |                                |
| type_id     | BIGINT UNSIGNED NULL     | FK → partenaire_types (SET NULL on delete) |
| name        | VARCHAR(150) NOT NULL    |                                |
| description | TEXT                     |                                |
| logo_path   | VARCHAR(255)             | Chemin relatif à WP_CONTENT_DIR, ex: `uploads/bandstage/logos/foo.png` |
| website     | VARCHAR(255)             |                                |
| email       | VARCHAR(150)             |                                |
| phone       | VARCHAR(30)              |                                |
| numero      | VARCHAR(10)              |                                |
| nom_voie    | VARCHAR(150)             |                                |
| code_postal | VARCHAR(10)              |                                |
| ville       | VARCHAR(100)             |                                |
| created_at  | DATETIME                 | DEFAULT NOW()                  |
| updated_at  | DATETIME                 | DEFAULT NOW() ON UPDATE NOW()  |

### `bandstage_concerts`

| Colonne     | Type                     | Notes                          |
|-------------|--------------------------|--------------------------------|
| id          | BIGINT UNSIGNED PK AI    |                                |
| titre       | VARCHAR(200) NOT NULL    |                                |
| date_debut  | DATE NOT NULL            |                                |
| date_fin    | DATE NULL                | NULL si concert sur une journée |
| horaires    | VARCHAR(100)             | Ex : "20h30 – 23h00"          |
| nom_lieu    | VARCHAR(150)             |                                |
| numero      | VARCHAR(10)              |                                |
| nom_voie    | VARCHAR(150)             |                                |
| code_postal | VARCHAR(10)              |                                |
| ville       | VARCHAR(100)             |                                |
| created_at  | DATETIME                 | DEFAULT NOW()                  |
| updated_at  | DATETIME                 | DEFAULT NOW() ON UPDATE NOW()  |

### `bandstage_concert_partenaires` (pivot)

| Colonne      | Type                  | Notes                        |
|--------------|-----------------------|------------------------------|
| concert_id   | BIGINT UNSIGNED NOT NULL | FK → concerts.id           |
| partenaire_id| BIGINT UNSIGNED NOT NULL | FK → partenaires.id        |
| PRIMARY KEY  | (concert_id, partenaire_id) |                         |

---

## 2. Couche Domain

### Nouveaux fichiers

```
src/Domain/Partenaires/
  PartenaireType.php       — entité : id, name, slug, icon
  Partenaire.php           — réécrit : hydraté depuis DB row stdClass
  PartenaireService.php    — réécrit : $wpdb, CRUD partenaires + types
                             upload/suppression logo via LogoUploader
                             actions AJAX : bs_partenaire_save, bs_partenaire_delete,
                                           bs_partenaire_type_save, bs_partenaire_type_delete

src/Domain/Concerts/
  Concert.php              — entité : id, titre, dates, horaires, adresse, partenaires[]
  ConcertService.php       — CRUD concerts + gestion pivot
                             actions AJAX : bs_concert_save, bs_concert_delete

src/Domain/Media/
  LogoUploader.php         — validation (image, 2 Mo max)
                             déplacement vers wp-content/uploads/bandstage/logos/
                             suppression de l'ancien logo si remplacement
                             retourne chemin relatif stocké en DB
```

### Modifications de l'existant

| Fichier | Changement |
|---------|-----------|
| `Config::create_tables()` | Ajoute les 4 nouvelles tables via dbDelta |
| `PostTypes.php` | Supprime `bs_partenaire` et taxonomie `bs_type_partenaire` |
| `Plugin::register_domain_services()` | Enregistre `ConcertService` |
| `uninstall.php` | DROP des 4 tables + rm -rf `/uploads/bandstage/` |
| `Assets::is_bandstage_page()` | Ajoute `bs_page_concerts` |
| `Assets::is_studio_page()` | Ajoute `bs_page_concerts` |

---

## 3. Frontend & Templates

### Shortcode `[bandstage_concerts]`

| Rôle | bs_view | Condition |
|------|---------|-----------|
| Vue publique | `list` (défaut) | Tous visiteurs — concerts dont `date_debut >= TODAY()`, triés par date_debut ASC |
| Liste Studio | `list` | Auteur+ — liste avec Modifier/Supprimer |
| Formulaire | `edit` | Auteur+ — création ou édition |

### Nouveaux templates

```
templates/public/concerts-public.php          — liste publique des concerts à venir
templates/public/studio/concert-list.php      — liste Studio (Auteur+)
templates/public/studio/concert-edit.php      — formulaire concert
                                                 champs : titre, date_debut, date_fin,
                                                 horaires, nom_lieu, adresse,
                                                 multi-select partenaires (HTML natif)
```

### Templates partenaires remplacés

```
templates/public/partenaires-public.php       — adapté nouvelles colonnes + logo
templates/public/studio/partenaire-list.php   — idem
templates/public/studio/partenaire-edit.php   — upload logo direct, select type depuis DB
```

### Page WordPress créée à l'activation

| Option          | Titre                      | Shortcode               | Template         |
|-----------------|----------------------------|-------------------------|------------------|
| bs_page_concerts | BandStage — Concerts       | [bandstage_concerts]    | elementor_canvas |

### URL helpers (dans Shortcodes.php)

```php
Shortcodes::concerts_url( string $view = 'list', int $post_id = 0 ): string
```

---

## 4. Accès & sécurité

| Action | Capacité requise |
|--------|-----------------|
| Voir concerts publics | Tous |
| Voir partenaires publics | Tous |
| Créer/modifier/supprimer concert | `edit_posts` (Auteur+) |
| Créer/modifier/supprimer partenaire | `edit_posts` (Auteur+) |
| Créer/modifier/supprimer type partenaire | `manage_options` (Admin) |
| Upload logo | `edit_posts` (Auteur+) |

**Logo upload :** validation MIME côté serveur (`wp_check_filetype`), extensions autorisées : jpg, jpeg, png, webp, svg. Taille max : 2 Mo.

---

## 5. Gestion des suppressions en cascade

- Suppression d'un type partenaire → `type_id` des partenaires associés mis à NULL (SET NULL)
- Suppression d'un partenaire → suppression de ses entrées dans `concert_partenaires`
- Suppression d'un concert → suppression de ses entrées dans `concert_partenaires`
- Suppression du plugin (uninstall.php) → DROP des 4 tables + suppression du dossier `/uploads/bandstage/logos/`

---

## 6. Routes config

`config/routes.php` — ajouter :

```php
'concerts' => [
    'list' => BANDSTAGE_PLUGIN_DIR . 'templates/public/studio/concert-list.php',
    'edit' => BANDSTAGE_PLUGIN_DIR . 'templates/public/studio/concert-edit.php',
],
```

---

## 7. Ce qui est supprimé

- CPT `bs_partenaire` (enregistrement dans `PostTypes.php`)
- Taxonomie `bs_type_partenaire` (enregistrement + gestion admin)
- Toutes les données CPT existantes (coupe nette, pas de migration)
- Template `tab-partenaires.php` dans les settings admin (plus nécessaire si types gérés en Studio)

---

## Hors périmètre

- Pagination des listes publiques (concerts, partenaires)
- Export CSV
- Notifications lors d'un nouveau concert
- Gestion des concerts passés (archivage)
