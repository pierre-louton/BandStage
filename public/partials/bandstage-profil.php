<?php
/**
 * Template front-end du profil membre.
 * Utilisé par le shortcode [bandstage_profil].
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

if ( ! (bool) get_option( 'bs_members_enabled', true ) ) {
	return;
}

$is_logged    = is_user_logged_in();
$current_user = $is_logged ? wp_get_current_user() : null;

// Options de profil actives.
$show_bio        = (bool) get_option( 'bs_members_show_bio',        true );
$show_instrument = (bool) get_option( 'bs_members_show_instrument', true );
$show_location   = (bool) get_option( 'bs_members_show_location',   false );
$avatar_type     = (string) get_option( 'bs_members_avatar_type',   'gravatar' );

// Meta utilisateur.
$bio        = '';
$instrument = '';
$location   = '';
$notif_c    = true;
$notif_n    = false;
$notif_t    = false;

if ( $is_logged && $current_user ) {
	$bio        = (string) get_user_meta( $current_user->ID, 'bs_bio',           true );
	$instrument = (string) get_user_meta( $current_user->ID, 'bs_instrument',    true );
	$location   = (string) get_user_meta( $current_user->ID, 'bs_location',      true );
	$notif_c    = '1' === get_user_meta( $current_user->ID, 'bs_notif_concerts', true );
	$notif_n    = '1' === get_user_meta( $current_user->ID, 'bs_notif_news',     true );
	$notif_t    = '1' === get_user_meta( $current_user->ID, 'bs_notif_tchache',  true );
}

// Avatar.
$avatar_url = '';
if ( $is_logged && $current_user ) {
	if ( 'gravatar' === $avatar_type ) {
		$avatar_url = get_avatar_url( $current_user->ID, array( 'size' => 96 ) );
	} elseif ( 'initials' === $avatar_type ) {
		$avatar_url = ''; // Rendu via initiales CSS.
	}
}

/**
 * Retourne les initiales d'un nom.
 *
 * @param string $name Nom complet.
 * @return string
 */
function bsp_initials( string $name ): string {
	$parts = explode( ' ', trim( $name ) );
	$init  = mb_strtoupper( mb_substr( $parts[0], 0, 1 ) );
	if ( isset( $parts[1] ) ) {
		$init .= mb_strtoupper( mb_substr( $parts[1], 0, 1 ) );
	}
	return $init ?: '?';
}


?>

<div class="bs-pr-wrap" id="bs-profil">

	<?php if ( $is_logged && $current_user ) : ?>
	<!-- ==================================================================
	     VUE PROFIL — utilisateur connecté
	     ================================================================== -->
	<div class="bs-pr-card">

		<!-- Avatar + identité -->
		<div class="bs-pr-identity">
			<div class="bs-pr-avatar-wrap">
				<?php if ( $avatar_url ) : ?>
				<img src="<?php echo esc_url( $avatar_url ); ?>"
				     alt="<?php echo esc_attr( $current_user->display_name ); ?>"
				     class="bs-pr-avatar-img" width="96" height="96">
				<?php else : ?>
				<div class="bs-pr-avatar-initials">
					<?php echo esc_html( bsp_initials( $current_user->display_name ) ); ?>
				</div>
				<?php endif; ?>
				<div class="bs-pr-avatar-badge" title="<?php esc_attr_e( 'Membre actif', 'bandstage' ); ?>">✓</div>
			</div>
			<div class="bs-pr-identity-info">
				<div class="bs-pr-name"><?php echo esc_html( $current_user->display_name ); ?></div>
				<div class="bs-pr-email"><?php echo esc_html( $current_user->user_email ); ?></div>
				<div class="bs-pr-since">
					<?php
					printf(
						/* translators: %s: date */
						esc_html__( 'Membre depuis %s', 'bandstage' ),
						esc_html( date_i18n( 'F Y', strtotime( $current_user->user_registered ) ) )
					);
					?>
				</div>
				<?php if ( (bool) get_option( 'bs_tchache_enabled', true ) ) : ?>
				<span class="bs-pr-tchache-badge"><?php esc_html_e( 'Accès Tchache', 'bandstage' ); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<!-- Onglets internes -->
		<div class="bs-pr-tabs">
			<button class="bs-pr-tab bs-pr-tab--active" onclick="bsPrTab(this,'bs-pr-infos')" type="button">
				<?php esc_html_e( 'Mon profil', 'bandstage' ); ?>
			</button>
			<button class="bs-pr-tab" onclick="bsPrTab(this,'bs-pr-notifs')" type="button">
				<?php esc_html_e( 'Notifications', 'bandstage' ); ?>
			</button>
			<button class="bs-pr-tab" onclick="bsPrTab(this,'bs-pr-reprise')" type="button">
				<?php esc_html_e( 'Reprise', 'bandstage' ); ?>
			</button>
		</div>

		<!-- Onglet : Mon profil -->
		<div class="bs-pr-panel" id="bs-pr-infos">
			<div class="bs-pr-section-title"><?php esc_html_e( 'Informations', 'bandstage' ); ?></div>

			<label class="bs-pr-label" for="bs-pr-display-name">
				<?php esc_html_e( 'Pseudo affiché', 'bandstage' ); ?>
			</label>
			<input class="bs-pr-field" type="text" id="bs-pr-display-name"
			       value="<?php echo esc_attr( $current_user->display_name ); ?>"
			       maxlength="60">

			<?php if ( $show_instrument ) : ?>
			<label class="bs-pr-label" for="bs-pr-instrument">
				🎸 <?php esc_html_e( 'Instrument pratiqué', 'bandstage' ); ?>
			</label>
			<input class="bs-pr-field" type="text" id="bs-pr-instrument"
			       value="<?php echo esc_attr( $instrument ); ?>"
			       placeholder="<?php esc_attr_e( 'Guitare, batterie, voix…', 'bandstage' ); ?>"
			       maxlength="80">
			<?php endif; ?>

			<?php if ( $show_location ) : ?>
			<label class="bs-pr-label" for="bs-pr-location">
				📍 <?php esc_html_e( 'Ville / Région', 'bandstage' ); ?>
			</label>
			<input class="bs-pr-field" type="text" id="bs-pr-location"
			       value="<?php echo esc_attr( $location ); ?>"
			       placeholder="<?php esc_attr_e( 'Paris, Lyon, Marseille…', 'bandstage' ); ?>"
			       maxlength="80">
			<?php endif; ?>

			<?php if ( $show_bio ) : ?>
			<label class="bs-pr-label" for="bs-pr-bio">
				<?php esc_html_e( 'Quelques mots…', 'bandstage' ); ?>
			</label>
			<textarea class="bs-pr-field" id="bs-pr-bio" rows="3"
			          maxlength="300"
			          placeholder="<?php esc_attr_e( 'Fan de blues depuis toujours, je joue de la guitare dans mon garage…', 'bandstage' ); ?>"><?php echo esc_textarea( $bio ); ?></textarea>
			<div class="bs-pr-charcount" id="bs-pr-bio-count">
				<?php echo esc_html( (string) mb_strlen( $bio ) ); ?> / 300
			</div>
			<?php endif; ?>

			<button type="button" class="bs-pr-btn bs-pr-btn--gold" onclick="bsPrSaveProfile()">
				<?php esc_html_e( 'Enregistrer le profil', 'bandstage' ); ?>
			</button>
			<div class="bs-pr-msg" id="bs-pr-profile-msg"></div>

			<div class="bs-pr-divider"></div>
			<a href="<?php echo esc_url( wp_logout_url( get_permalink() ?: home_url() ) ); ?>"
			   class="bs-pr-btn bs-pr-btn--outline">
				<?php esc_html_e( 'Se déconnecter', 'bandstage' ); ?>
			</a>
		</div>

		<!-- Onglet : Notifications -->
		<div class="bs-pr-panel" id="bs-pr-notifs" style="display:none">
			<div class="bs-pr-section-title"><?php esc_html_e( 'Je veux recevoir…', 'bandstage' ); ?></div>

			<div class="bs-pr-toggle-row">
				<div>
					<div class="bs-pr-toggle-label"><?php esc_html_e( 'Dates de concerts', 'bandstage' ); ?></div>
					<div class="bs-pr-toggle-sub"><?php esc_html_e( 'Rappel 48h avant chaque concert', 'bandstage' ); ?></div>
				</div>
				<button type="button" class="bs-pr-toggle <?php echo $notif_c ? 'is-on' : ''; ?>"
				        id="bs-pr-tog-concerts" aria-pressed="<?php echo $notif_c ? 'true' : 'false'; ?>"
				        onclick="this.classList.toggle('is-on');this.setAttribute('aria-pressed',this.classList.contains('is-on'))"></button>
			</div>

			<div class="bs-pr-toggle-row">
				<div>
					<div class="bs-pr-toggle-label"><?php esc_html_e( 'Actualités du groupe', 'bandstage' ); ?></div>
					<div class="bs-pr-toggle-sub"><?php esc_html_e( 'Nouveaux billets, EP, clips…', 'bandstage' ); ?></div>
				</div>
				<button type="button" class="bs-pr-toggle <?php echo $notif_n ? 'is-on' : ''; ?>"
				        id="bs-pr-tog-news" aria-pressed="<?php echo $notif_n ? 'true' : 'false'; ?>"
				        onclick="this.classList.toggle('is-on');this.setAttribute('aria-pressed',this.classList.contains('is-on'))"></button>
			</div>

			<div class="bs-pr-toggle-row">
				<div>
					<div class="bs-pr-toggle-label"><?php esc_html_e( 'Activité Tchache', 'bandstage' ); ?></div>
					<div class="bs-pr-toggle-sub"><?php esc_html_e( 'Nouveaux messages dans le forum', 'bandstage' ); ?></div>
				</div>
				<button type="button" class="bs-pr-toggle <?php echo $notif_t ? 'is-on' : ''; ?>"
				        id="bs-pr-tog-tchache" aria-pressed="<?php echo $notif_t ? 'true' : 'false'; ?>"
				        onclick="this.classList.toggle('is-on');this.setAttribute('aria-pressed',this.classList.contains('is-on'))"></button>
			</div>

			<div class="bs-pr-section-title" style="margin-top:18px"><?php esc_html_e( 'E-mail de réception', 'bandstage' ); ?></div>
			<input class="bs-pr-field" type="email" id="bs-pr-email"
			       value="<?php echo esc_attr( $current_user->user_email ); ?>">
			<p class="bs-pr-hint"><?php esc_html_e( 'Modifie uniquement l\'adresse pour les envois BandStage, pas ton compte WP.', 'bandstage' ); ?></p>

			<button type="button" class="bs-pr-btn bs-pr-btn--gold" onclick="bsPrSavePrefs()">
				<?php esc_html_e( 'Enregistrer les préférences', 'bandstage' ); ?>
			</button>
			<div class="bs-pr-msg" id="bs-pr-prefs-msg"></div>
		</div>

		<!-- Onglet : Proposer une reprise -->
		<div class="bs-pr-panel" id="bs-pr-reprise" style="display:none">
			<?php if ( (bool) get_option( 'bs_reprise_enabled', true ) ) : ?>
			<div class="bs-pr-section-title"><?php esc_html_e( 'Proposer une reprise', 'bandstage' ); ?></div>
			<p class="bs-pr-hint">
				<?php esc_html_e( 'Vous avez une chanson qui collerait parfaitement au style du groupe ? Soumettez-la ici — les musiciens lisent toutes les suggestions !', 'bandstage' ); ?>
			</p>
			<textarea class="bs-pr-field" id="bs-pr-reprise-text" rows="4"
			          maxlength="500"
			          placeholder="<?php esc_attr_e( 'Ex : « Whole Lotta Love » de Led Zeppelin, parfait pour vos solos de guitare…', 'bandstage' ); ?>"></textarea>
			<button type="button" class="bs-pr-btn bs-pr-btn--gold" onclick="bsPrSendReprise()">
				🎵 <?php esc_html_e( 'Envoyer au groupe', 'bandstage' ); ?>
			</button>
			<div class="bs-pr-msg" id="bs-pr-reprise-msg"></div>
			<?php else : ?>
			<p class="bs-pr-hint" style="text-align:center;padding:24px 0">
				<?php esc_html_e( 'Le formulaire de reprise est temporairement désactivé.', 'bandstage' ); ?>
			</p>
			<?php endif; ?>
		</div>

	</div><!-- /.bs-pr-card -->

	<?php else : ?>
	<!-- ==================================================================
	     VUE AUTH — utilisateur non connecté
	     ================================================================== -->
	<div class="bs-pr-card">
		<div class="bs-pr-auth-header">
			<div class="bs-pr-auth-icon" aria-hidden="true">
				<svg width="40" height="40" viewBox="0 0 40 40" fill="none">
					<circle cx="20" cy="14" r="7" stroke="currentColor" stroke-width="1.5" fill="none" opacity=".8"/>
					<path d="M6 37c0-7.7 6.3-14 14-14s14 6.3 14 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none" opacity=".8"/>
				</svg>
			</div>
			<div class="bs-pr-auth-title"><?php echo esc_html( (string) get_option( 'bs_band_name', 'BandStage' ) ); ?></div>
			<div class="bs-pr-auth-sub"><?php esc_html_e( 'Rejoignez la communauté', 'bandstage' ); ?></div>
		</div>

		<!-- Onglets Connexion / Inscription -->
		<div class="bs-pr-tabs">
			<button class="bs-pr-tab bs-pr-tab--active" onclick="bsPrTab(this,'bs-pr-login')" type="button">
				<?php esc_html_e( 'Se connecter', 'bandstage' ); ?>
			</button>
			<button class="bs-pr-tab" onclick="bsPrTab(this,'bs-pr-register')" type="button">
				<?php esc_html_e( 'Créer un compte', 'bandstage' ); ?>
			</button>
		</div>

		<!-- Connexion -->
		<div class="bs-pr-panel" id="bs-pr-login">
			<label class="bs-pr-label" for="bs-pr-login-user"><?php esc_html_e( 'Pseudo ou e-mail', 'bandstage' ); ?></label>
			<input class="bs-pr-field" type="text" id="bs-pr-login-user"
			       autocomplete="username">

			<label class="bs-pr-label" for="bs-pr-login-pass"><?php esc_html_e( 'Mot de passe', 'bandstage' ); ?></label>
			<div class="bs-pr-pass-wrap">
				<input class="bs-pr-field" type="password" id="bs-pr-login-pass"
				       autocomplete="current-password">
				<button type="button" class="bs-pr-pass-toggle" aria-label="<?php esc_attr_e( 'Afficher le mot de passe', 'bandstage' ); ?>"
				        onclick="bsPrTogglePass('bs-pr-login-pass',this)">
					<svg width="16" height="16" viewBox="0 0 20 20" fill="none">
						<ellipse cx="10" cy="10" rx="8" ry="5.5" stroke="currentColor" stroke-width="1.4"/>
						<circle cx="10" cy="10" r="2.5" fill="currentColor"/>
					</svg>
				</button>
			</div>

			<div class="bs-pr-forgot">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
					<?php esc_html_e( 'Mot de passe oublié ?', 'bandstage' ); ?>
				</a>
			</div>

			<button type="button" class="bs-pr-btn bs-pr-btn--gold" onclick="bsPrLogin()">
				<?php esc_html_e( 'Se connecter', 'bandstage' ); ?>
			</button>
			<div class="bs-pr-msg" id="bs-pr-login-msg"></div>
		</div>

		<!-- Inscription -->
		<div class="bs-pr-panel" id="bs-pr-register" style="display:none">
			<label class="bs-pr-label" for="bs-pr-reg-user"><?php esc_html_e( 'Pseudo', 'bandstage' ); ?></label>
			<input class="bs-pr-field" type="text" id="bs-pr-reg-user"
			       maxlength="60" autocomplete="username"
			       placeholder="<?php esc_attr_e( 'Votre pseudo public', 'bandstage' ); ?>">

			<label class="bs-pr-label" for="bs-pr-reg-email"><?php esc_html_e( 'E-mail', 'bandstage' ); ?></label>
			<input class="bs-pr-field" type="email" id="bs-pr-reg-email"
			       autocomplete="email"
			       placeholder="<?php esc_attr_e( 'votre@email.com', 'bandstage' ); ?>">

			<label class="bs-pr-label" for="bs-pr-reg-pass"><?php esc_html_e( 'Mot de passe', 'bandstage' ); ?></label>
			<div class="bs-pr-pass-wrap">
				<input class="bs-pr-field" type="password" id="bs-pr-reg-pass"
				       autocomplete="new-password" minlength="8"
				       placeholder="<?php esc_attr_e( 'Minimum 8 caractères', 'bandstage' ); ?>">
				<button type="button" class="bs-pr-pass-toggle" aria-label="<?php esc_attr_e( 'Afficher', 'bandstage' ); ?>"
				        onclick="bsPrTogglePass('bs-pr-reg-pass',this)">
					<svg width="16" height="16" viewBox="0 0 20 20" fill="none">
						<ellipse cx="10" cy="10" rx="8" ry="5.5" stroke="currentColor" stroke-width="1.4"/>
						<circle cx="10" cy="10" r="2.5" fill="currentColor"/>
					</svg>
				</button>
			</div>
			<div class="bs-pr-strength" id="bs-pr-strength"></div>

			<?php if ( (bool) get_option( 'bs_notif_concerts_enabled', true ) ) : ?>
			<label class="bs-pr-inline-check">
				<input type="checkbox" id="bs-pr-reg-concerts" checked>
				<?php esc_html_e( 'M\'alerter pour les prochains concerts', 'bandstage' ); ?>
			</label>
			<?php endif; ?>

			<button type="button" class="bs-pr-btn bs-pr-btn--gold" onclick="bsPrRegister()">
				<?php esc_html_e( 'Créer mon compte', 'bandstage' ); ?>
			</button>
			<div class="bs-pr-msg" id="bs-pr-reg-msg"></div>

			<p class="bs-pr-hint" style="text-align:center">
				<?php esc_html_e( 'En créant un compte, vous acceptez de participer dans le respect des autres membres.', 'bandstage' ); ?>
			</p>
		</div>

	</div><!-- /.bs-pr-card -->
	<?php endif; ?>

</div><!-- /.bs-pr-wrap -->
