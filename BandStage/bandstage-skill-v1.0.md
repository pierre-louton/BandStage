# BandStage — SKILL de référence v1.0.0

> **À lire en PREMIER** avant tout fichier PHP, CSS ou JS du plugin BandStage.

---

## 1. Contexte projet

| Paramètre | Valeur |
|-----------|--------|
| Plugin | BandStage v1.0.0 |
| Auteur | Pierre Beaubié |
| Text domain | `bandstage` |
| Nonce | `BANDSTAGE_NONCE = 'bandstage_nonce'` |
| Préfixe options | `bs_` |
| Stack | o2switch · WP 6.9.4 FR · Elementor 4.0.4 Free · PHP 8.3 |
| Cible | catalogue WP.org (GPL v2+, SVN) |
| Design | Mobile-first iOS |

---

## 2. Architecture de fichiers

```
bandstage/
├── bandstage.php              # Entry point — autoload PSR-4, constantes, hooks activation
├── readme.txt                 # Format WP.org obligatoire
├── uninstall.php              # Nettoyage complet
├── config/
│   └── routes.php             # Mapping bs_view → chemin absolu template
├── languages/                 # Traductions (.pot)
├── assets/
│   ├── css/
│   │   ├── public.css         # Design public, préfixe bs-
│   │   ├── studio.css         # Interface studio (Auteur+), préfixe bss-
│   │   └── admin.css          # Back-office WP, préfixe bs-admin-
│   └── js/
│       ├── public.js          # BsToast, bsAjax, Tchache, ticker
│       ├── studio.js          # CRUD news/partenaires/lineup, wp.media, drag-drop
│       └── admin.js           # wpColorPicker, types partenaires, modération Tchache
└── src/
    ├── Core/
    │   ├── Plugin.php         # Singleton, activate(), run()
    │   ├── Loader.php         # Registre actions/filtres
    │   └── Config.php         # table_messages(), table_notifications(), default_options(), create_tables()
    ├── Admin/
    │   ├── Admin.php          # Menus WP back-office
    │   ├── Assets.php         # Enqueue admin CSS/JS + wp-color-picker
    │   ├── PostTypes.php      # CPTs + taxonomie
    │   └── SettingsPage.php   # Settings API — 9 groupes, méthode group($tab)
    ├── Public/
    │   ├── PublicController.php  # maybe_hide_admin_bar()
    │   ├── Shortcodes.php        # 6 shortcodes + helpers URL
    │   └── Assets.php            # Enqueue CSS/JS public + maybe_inject_dynamic_css()
    └── Domain/
        ├── Members/
        │   ├── Member.php        # Entité utilisateur WP, initials()
        │   └── MemberService.php # get_band_members(), ajax_save_profile()
        ├── Tchache/
        │   ├── Message.php
        │   └── TchacheService.php
        ├── News/
        │   ├── News.php
        │   └── NewsService.php   # get_ticker_titles()
        ├── Partenaires/
        │   ├── Partenaire.php
        │   └── PartenaireService.php # get_grouped_by_type()
        ├── Notifications/
        │   ├── Notification.php
        │   └── NotificationService.php
        └── Lineup/               # NOUVEAU v1.0
            ├── LineupMember.php  # Entité CPT bs_band_member
            └── LineupService.php # get_ordered(), get(), ajax_save(), ajax_delete(), ajax_reorder()
```

---

## 3. Custom Post Types

| CPT | Label | Champs | show_in_menu |
|-----|-------|--------|--------------|
| `bs_news` | Actualités | title, editor, author | `'bandstage'` |
| `bs_partenaire` | Partenaires | title, editor, thumbnail | `'bandstage'` |
| `bs_band_member` | Membres du groupe | title, thumbnail, page-attributes (menu_order) | `'bandstage'` |

**Taxonomie** : `bs_type_partenaire` sur `bs_partenaire` — non hiérarchique, `show_ui: false`, meta `bs_term_icon` (emoji).

**Metas `bs_band_member`** :
- `bs_bm_role` — instrument / rôle (text)
- `bs_bm_styles` — styles préférés (text)
- thumbnail (natif WP via `set_post_thumbnail`)
- `menu_order` — ordre d'affichage (natif WP)

---

## 4. Pages & shortcodes

| Option | Page | Shortcode | Template Elementor |
|--------|------|-----------|--------------------|
| `bs_page_accueil` | BandStage — Accueil | `[bandstage_homepage]` | `elementor_canvas` |
| `bs_page_tchache` | BandStage — Tchache | `[bandstage_tchache]` | `elementor_canvas` |
| `bs_page_profil` | BandStage — Mon Compte | `[bandstage_profil]` | `elementor_canvas` |
| `bs_page_studio` | BandStage — Studio | `[bandstage_studio]` | `elementor_canvas` |
| `bs_page_partenaires` | BandStage — Partenaires | `[bandstage_partenaires]` | `elementor_canvas` |
| `bs_page_groupe` | BandStage — Le groupe | `[bandstage_groupe]` | `elementor_canvas` |

> **Règle absolue** : toutes les pages BandStage utilisent `elementor_canvas`.

---

## 5. Matrice d'accès

| Page | Visiteur non connecté | Auteur+ (`edit_posts`) | Admin (`manage_options`) |
|------|-----------------------|------------------------|--------------------------|
| Accueil | Homepage complète | Identique | Identique |
| Tchache | Lecture + zone grisée + lien connexion | Lecture + écriture | + Modération back-office |
| Mon Compte | `wp_login_form()` stylisé BandStage | Profil éditable | Profil éditable |
| Studio | Archive publique `bs_news` (Humeurs) | CRUD actus (ses propres) | CRUD actus (tous) |
| Partenaires | Grille groupée par type | CRUD partenaires | + suppression |
| Le groupe | Grille lineup | CRUD membres lineup | + suppression + drag-drop |

---

## 6. Routing interne `$_GET['bs_view']`

Fichier : `config/routes.php` — retourne un tableau `[section][view] => chemin absolu`.

| Section | `bs_view` | Template |
|---------|-----------|----------|
| studio | `dashboard` (défaut) | `studio/dashboard.php` |
| studio | `edit` | `studio/news-edit.php` |
| partenaires | `list` (défaut) | `studio/partenaire-list.php` |
| partenaires | `edit` | `studio/partenaire-edit.php` |
| groupe | `list` (défaut) | `studio/lineup-list.php` |
| groupe | `edit` | `studio/lineup-edit.php` |

---

## 7. URL helpers (Shortcodes.php — méthodes statiques)

```php
Shortcodes::studio_url(string $view = 'dashboard', int $post_id = 0): string
Shortcodes::partenaires_url(string $view = 'list', int $post_id = 0): string
Shortcodes::groupe_url(string $view = 'list', int $post_id = 0): string
```

---

## 8. AJAX actions

| action WP | Service | Accès |
|-----------|---------|-------|
| `bs_tchache_post` | TchacheService | `wp_ajax_nopriv` + `wp_ajax` |
| `bs_tchache_load` | TchacheService | `wp_ajax_nopriv` + `wp_ajax` |
| `bs_tchache_moderate` | TchacheService | `manage_options` |
| `bs_news_save` | NewsService | `edit_posts` |
| `bs_news_delete` | NewsService | `edit_posts` (auteur) ou `manage_options` |
| `bs_partenaire_save` | PartenaireService | `edit_posts` |
| `bs_partenaire_delete` | PartenaireService | `manage_options` |
| `bs_type_partenaire_add` | PartenaireService | `manage_options` |
| `bs_member_save_profile` | MemberService | `is_user_logged_in()` |
| `bs_notifications_mark_read` | NotificationService | `is_user_logged_in()` |
| `bs_lineup_save` | LineupService | `edit_posts` |
| `bs_lineup_delete` | LineupService | `manage_options` |
| `bs_lineup_reorder` | LineupService | `edit_posts` |

---

## 9. CSS — règles critiques

### Wrappers (sélecteurs)
```css
/* CSS dynamique (couleurs admin) injecté sur TOUS ces wrappers */
.bs-wrap, .bs-tc-wrap, .bs-pr-wrap, .bs-gr-wrap
```

> **Règle 12** : le wrapper de `[bandstage_groupe]` est `.bs-gr-wrap` — il doit figurer dans `maybe_inject_dynamic_css()`.

### maybe_inject_dynamic_css()
- Appelée **dans** chaque shortcode (pas via `wp_head`)
- Protégée par flag `static $done = false`
- Cible : `.bs-wrap,.bs-tc-wrap,.bs-pr-wrap,.bs-gr-wrap { background: linear-gradient(...); --bs-accent: ...; ... }`
- Ne jamais cibler `:root`

### Préfixes CSS
| Contexte | Préfixe |
|----------|---------|
| Public général | `bs-` |
| Studio front-end | `bss-` |
| Back-office WP | `bs-admin-` |

### Design tokens
- Fond : dégradé `bs_bg_color_start` → `bs_bg_color_end` (bleu outremer #1535A8 → #020828)
- Accent : `#D4A820` (or)
- Crème : `#FAF6EB`
- Typo brand : Playfair Display (serif)
- Typo UI : Oswald 600
- Radius cards : 8–16px
- Grille homepage : 2×3 (mobile), 3×2 (tablette+)

---

## 10. JS — conventions

- `BsToast.show(message, type, duration)` — toast global (public.js)
- `bsAjax(action, data)` — wrapper fetch vers `BsPublic.ajaxUrl` (public.js)
- Bouton submit hors `<form>` via attribut `form="bss-lineup-form"` (idem partenaire-edit)
- JS cible : `document.querySelector("[form='bss-lineup-form']")`
- `wp_enqueue_media()` activé sur : `bs_page_studio`, `bs_page_partenaires`, `bs_page_groupe`
- Drag-and-drop lineup : HTML5 natif (pas de lib jQuery UI)

---

## 11. Règles absolues (ne jamais enfreindre)

1. Toujours `check_ajax_referer( BANDSTAGE_NONCE, 'nonce' )` en premier dans chaque handler AJAX
2. Toujours vérifier la capacité (`current_user_can(...)`) juste après le nonce
3. Toujours `wp_send_json_error()`/`wp_send_json_success()` — jamais `echo` direct
4. CSS dynamique injecté par `maybe_inject_dynamic_css()` — jamais en `<link>` externe
5. Toutes les pages créées avec `_wp_page_template = 'elementor_canvas'`
6. Préfixe options WP : `bs_` — jamais sans préfixe
7. Autoload PSR-4 : `BandStage\` → `src/` — pas de `require` manuel
8. Text domain : `bandstage` partout
9. `from_wp_post()` / `from_wp_user()` dans l'entité — jamais de logique WP dans le constructeur
10. `menu_order` (natif WP) pour l'ordre des membres du lineup — pas de meta dédiée
11. Supprimer la vignette orpheline (bs_band_member, bs_partenaire) si plus attachée après `wp_delete_post`
12. Wrapper `[bandstage_groupe]` = `.bs-gr-wrap` (inclus dans `maybe_inject_dynamic_css()`)
13. Toutes les pages BandStage = `elementor_canvas` (pas `elementor_header-footer`, pas `default`)

---

## 12. Tables DB

```sql
-- wp_bandstage_messages
id          BIGINT UNSIGNED AUTO_INCREMENT PK
user_id     BIGINT UNSIGNED NOT NULL
content     TEXT NOT NULL
status      ENUM('pending','approved','spam') DEFAULT 'pending'
created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
INDEX: user_id, status, created_at

-- wp_bandstage_notifications
id          BIGINT UNSIGNED AUTO_INCREMENT PK
user_id     BIGINT UNSIGNED NOT NULL
type        VARCHAR(64) NOT NULL
payload     JSON
read_at     DATETIME NULL
created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
INDEX: user_id, read_at, created_at
```

---

## 13. Roadmap v1.1 (non encore implémenté)

- Post type `bs_news` : titre → ticker auto, contenu → section Humeurs ✅ (déjà fait v1.0)
- Inspiré du plugin Edito : contacts → partenaires, news → humeurs ✅ (déjà fait v1.0)
- Éventuel : envoi de notifications quotidiennes (wp-cron)
- Éventuel : éditor riche (wp_editor) dans news-edit.php
