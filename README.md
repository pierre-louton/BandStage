=== BandStage ===
Contributors:      pierrebeaubié
Tags:              music, band, groupe, musique, tchache
Requires at least: 6.2
Tested up to:      6.9
Requires PHP:      8.1
Stable tag:        1.0.0
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Site mobile-first pour groupes de musique — Actus, Tchache, Membres, Partenaires, Lineup.

== Description ==

BandStage transforme votre site WordPress en espace numérique dédié à votre groupe de musique. Mobile-first, conçu pour être intégré dans Elementor (canvas), il propose :

* **Page d'accueil** — Ticker d'actualités + grille de navigation 2×3 entièrement configurable
* **Tchache** — Mini-forum en temps réel, lecture publique, écriture réservée aux membres connectés, modération admin
* **Studio (Humeurs)** — Les musiciens publient des actualités (humeurs) depuis le front-end
* **Le groupe (Lineup)** — Présentation publique des membres : photo, rôle, styles. Réordonnancement par glisser-déposer
* **Partenaires** — Annuaire groupé par types configurables (magasins, luthiers, salles, institutionnels…)
* **Mon Compte** — Profil éditable pour les membres connectés, connexion stylisée pour les visiteurs

Chaque page utilise le template `elementor_canvas` et s'intègre nativement à Elementor Free.

== Installation ==

1. Téléversez le dossier `bandstage` dans `/wp-content/plugins/`
2. Activez le plugin via le menu **Extensions**
3. Assurez-vous qu'Elementor est installé et activé
4. Les pages sont créées automatiquement à l'activation
5. Configurez le plugin via **BandStage > Réglages**

== Frequently Asked Questions ==

= Elementor est-il obligatoire ? =

Oui. BandStage requiert Elementor (Free ou Pro) pour le template `elementor_canvas`.

= Comment ajouter des membres au lineup public ? =

Rendez-vous sur la page « Le groupe » en étant connecté (Auteur ou supérieur). Cliquez « + Ajouter » pour créer un membre, puis réordonnez-les par glisser-déposer.

= Qui peut modérer la Tchache ? =

Les administrateurs WordPress. Un sous-menu « Tchache » est disponible dans le menu BandStage en back-office.

= Peut-on changer les couleurs ? =

Oui. Allez dans **BandStage > Réglages > Apparence** pour modifier le dégradé de fond, la couleur accent et la couleur crème.

== Screenshots ==

1. Page d'accueil avec ticker et grille 2×3
2. Tchache — mini-forum
3. Studio — liste des humeurs (Auteur)
4. Le groupe — grille publique du lineup
5. Partenaires — grille groupée par type
6. Administration — réglages

== Changelog ==

= 1.0.0 =
* Version initiale
* CPT bs_news, bs_partenaire, bs_band_member
* Shortcodes : bandstage_homepage, bandstage_tchache, bandstage_profil, bandstage_studio, bandstage_partenaires, bandstage_groupe
* Gestion complète du lineup avec drag-and-drop
* Modération Tchache en back-office
* 9 onglets de réglages

== Upgrade Notice ==

= 1.0.0 =
Première version stable.
