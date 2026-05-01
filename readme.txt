=== BandStage ===
Contributors: pierrebeaubie
Tags: music, band, homepage, mobile, forum
Requires at least: 6.2
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Homepage mobile-first pour groupes de musique — ticker, grille de sections, mini-forum, profils membres et panneau d'apparence.

== Description ==

**BandStage** transforme votre site WordPress en un hub mobile-first complet pour votre groupe de musique.

= Fonctionnalités principales =

* **Homepage adaptée mobile** — grille 2×3 de sections configurables (Le Groupe, Concerts, Références, Humeurs, Partenaires, Tchache).
* **Ticker défilant** — affichage automatique des prochains concerts et actualités, alimenté par vos articles WordPress ou par des entrées manuelles.
* **Tchache** — mini-forum intégré avec modération (automatique ou manuelle), anti-spam et notifications email.
* **Profils membres** — inscription, avatar, préférences de notifications, accès au Tchache.
* **Notifications** — alertes concerts (48h avant), newsletter actualités, via WP Mail ou intégration Mailchimp.
* **Proposer une reprise** — formulaire permettant aux fans de soumettre des idées de reprises directement au groupe.
* **Panneau d'apparence** — l'utilisateur peut personnaliser les couleurs du fond, les polices et les animations depuis le front-end.
* **Page de configuration complète** — toutes les options sont accessibles dans WP Admin sans toucher au code.

= Shortcodes disponibles =

* `[bandstage_homepage]` — affiche la page d'accueil complète (titre, ticker, grille).
* `[bandstage_tchache]` — affiche le mini-forum standalone sur n'importe quelle page.
* `[bandstage_profil]` — affiche le formulaire de connexion / profil membre.

= Multilingue =

BandStage est entièrement compatible avec les fichiers `.po`/`.mo` et le domaine de traduction `bandstage`. Toutes les chaînes sont internalisées.

== Installation ==

= Installation automatique =

1. Dans WP Admin, rendez-vous dans **Extensions > Ajouter**.
2. Recherchez « BandStage ».
3. Cliquez sur **Installer** puis **Activer**.

= Installation manuelle =

1. Téléchargez le fichier `.zip` depuis le répertoire WordPress.org.
2. Dans WP Admin : **Extensions > Ajouter > Téléverser une extension**.
3. Sélectionnez le `.zip` et cliquez sur **Installer maintenant**.
4. Activez l'extension.

= Configuration initiale =

1. Allez dans **BandStage > Réglages** dans le menu admin.
2. Renseignez le nom de votre groupe et la tagline.
3. Configurez les 6 boîtes (titre, image, lien, couleur).
4. Placez le shortcode `[bandstage_homepage]` sur la page d'accueil de votre site.
5. Optionnel : activez le Tchache et l'inscription membres.

== Frequently Asked Questions ==

= Dois-je modifier mon thème ? =

Non. BandStage fonctionne via shortcodes et peut être utilisé sur n'importe quelle page, quel que soit le thème actif. Il est recommandé d'utiliser une page avec un template « pleine largeur ».

= La homepage est-elle vraiment mobile-first ? =

Oui. La grille, le ticker et les panneaux sont conçus en priorité pour les écrans de 375 px et s'adaptent aux écrans plus larges via media queries.

= Le Tchache requiert-il un plugin tiers ? =

Non. Le Tchache est entièrement intégré à BandStage et utilise une table custom dans votre base WordPress. Aucun plugin de commentaires tiers n'est nécessaire.

= Puis-je utiliser mes propres images dans les boîtes ? =

Oui. Dans **BandStage > Réglages > Boîtes**, vous pouvez définir une image depuis la médiathèque WordPress pour chaque boîte. Un pictogramme SVG s'affiche en superposition.

= BandStage est-il compatible avec WooCommerce ? =

Il n'y a pas d'intégration spécifique, mais les deux plugins peuvent coexister sans conflit.

= Comment activer les notifications Mailchimp ? =

Dans **BandStage > Réglages > Notifications**, entrez votre clé API Mailchimp et l'ID de votre liste. Les abonnés seront synchronisés automatiquement lors de l'inscription d'un membre.

== Screenshots ==

1. Homepage sur mobile — titre, ticker et grille 2×3 sur fond bleu outremer.
2. Panneau « Mon Compte » — connexion, création de compte et formulaire « Proposer une reprise ».
3. Panneau « Paramètres » — swatches de fond, toggles de notifications et saisie email.
4. Page de configuration admin — onglets Apparence, Boîtes, Ticker, Tchache, Membres, Notifications.
5. Tchache — mini-forum avec modération et compteur de messages.

== Changelog ==

= 1.0.0 =
* Version initiale.
* Grille 2×3 configurable, ticker, Tchache, profils membres, notifications, panneau d'apparence front-end.

== Upgrade Notice ==

= 1.0.0 =
Première version stable. Aucune mise à jour de base de données requise depuis une version antérieure.
