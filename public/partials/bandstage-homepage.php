<?php
/**
 * Template de la page d'accueil BandStage.
 * Affiché via le shortcode [bandstage_homepage].
 *
 * @package BandStage
 */

defined( 'ABSPATH' ) || exit;

// -----------------------------------------------------------------------
// Données dynamiques depuis les options
// -----------------------------------------------------------------------
$bs_name    = esc_html( (string) get_option( 'bs_band_name',    'Mon Groupe' ) );
$bs_tagline = esc_html( (string) get_option( 'bs_band_tagline', 'Rock · Blues · Soul' ) );
$bs_ticker  = (bool) get_option( 'bs_ticker_enabled', true );
$bs_tchache = (bool) get_option( 'bs_tchache_enabled', true );
$bs_speed   = absint( get_option( 'bs_ticker_speed', 24 ) );

// Ticker — items
$ticker_items = array();
if ( $bs_ticker ) {
	$source = get_option( 'bs_ticker_source', 'manual' );
	if ( 'bs_news' === $source ) {
		$args = array(
			'post_type'      => 'bs_news',
			'posts_per_page' => 15,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		foreach ( get_posts( $args ) as $p ) {
			$ticker_items[] = esc_html( get_the_title( $p ) );
		}
	} elseif ( 'posts' === $source ) {
		$cats  = (array) get_option( 'bs_ticker_categories', array() );
		$args  = array(
			'posts_per_page' => 10,
			'post_status'    => 'publish',
		);
		if ( $cats ) {
			$args['category__in'] = array_map( 'absint', $cats );
		}
		foreach ( get_posts( $args ) as $p ) {
			$ticker_items[] = esc_html( get_the_title( $p ) );
		}
	} else {
		$raw = (string) get_option( 'bs_ticker_items', '' );
		foreach ( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) as $line ) {
			$ticker_items[] = esc_html( $line );
		}
	}
}
$ticker_text = $ticker_items ? implode( '<span class="bs-sep">★</span>', $ticker_items ) : '';
// Doublement pour boucle infinie
$ticker_text = $ticker_text . '<span class="bs-sep">★</span>' . $ticker_text . '<span class="bs-sep">★</span>';

// Boxes (6 max)
$boxes = array();
for ( $i = 1; $i <= 6; $i++ ) {
	if ( ! (bool) get_option( "bs_box_{$i}_enabled", true ) ) {
		continue;
	}
	$img_id  = (int) get_option( "bs_box_{$i}_image_id", 0 );
	$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
	$boxes[] = array(
		'title'       => esc_html( (string) get_option( "bs_box_{$i}_title",       '' ) ),
		'link'        => esc_url( (string) get_option( "bs_box_{$i}_link",         '#' ) ),
		'color_start' => sanitize_hex_color( (string) get_option( "bs_box_{$i}_color_start", '#111122' ) ),
		'color_end'   => sanitize_hex_color( (string) get_option( "bs_box_{$i}_color_end",   '#333366' ) ),
		'icon'        => sanitize_key( (string) get_option( "bs_box_{$i}_icon",    '' ) ),
		'img_url'     => $img_url,
		'is_tchache'  => ( 'tchache' === get_option( "bs_box_{$i}_icon" ) ),
	);
}

// Compte messages Tchache approuvés
$tchache_count = 0;
if ( $bs_tchache ) {
	global $wpdb;
	$table = $wpdb->prefix . 'bandstage_messages';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$tchache_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE status = 'approved'" );
}

// Utilisateur connecté
$current_user = wp_get_current_user();
$is_logged    = is_user_logged_in();
$display_name = $is_logged ? esc_html( $current_user->display_name ) : '';
$initials     = $display_name ? mb_strtoupper( mb_substr( $display_name, 0, 2 ) ) : '';

// -----------------------------------------------------------------------
// -----------------------------------------------------------------------

// Durée ticker comme variable CSS supplémentaire
// -----------------------------------------------------------------------
// Splashscreen
// -----------------------------------------------------------------------
$splash_enabled  = (bool) get_option( 'bs_splash_enabled', true );
$splash_image_id = (int) get_option( 'bs_splash_image_id', 0 );
$splash_duration = absint( get_option( 'bs_splash_duration', 4 ) );
$splash_img_url  = $splash_image_id ? wp_get_attachment_image_url( $splash_image_id, 'large' ) : '';


?>

<?php if ( $splash_enabled && $splash_img_url ) : ?>
<div class="bs-splash-overlay" id="bs-splash"
     data-duration="<?php echo esc_attr( (string) $splash_duration ); ?>"
     role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( get_option( 'bs_band_name', 'BandStage' ) ); ?>">

	<!-- Image : tap pour fermer -->
	<div class="bs-splash-img-wrap" onclick="bsSplashClose()" aria-label="<?php esc_attr_e( 'Fermer', 'bandstage' ); ?>">
		<img src="<?php echo esc_url( $splash_img_url ); ?>"
		     alt="<?php echo esc_attr( (string) get_option( 'bs_band_name', 'BandStage' ) ); ?>"
		     class="bs-splash-img">
	</div>

	<div class="bs-splash-footer">

		<?php if ( $splash_duration > 0 ) : ?>
		<div class="bs-splash-progress" title="<?php esc_attr_e( 'Fermeture automatique', 'bandstage' ); ?>">
			<div class="bs-splash-progress__bar" id="bs-splash-bar"
			     style="animation:bs-splash-countdown <?php echo esc_attr( (string) $splash_duration ); ?>s linear forwards"></div>
		</div>
		<?php endif; ?>

		<!-- Préférence affichage -->
		<label class="bs-splash-pref">
			<span class="bs-splash-pref__label"><?php esc_html_e( 'Afficher à l\'ouverture', 'bandstage' ); ?></span>
			<span class="bs-splash-pref__toggle">
				<input type="checkbox" id="bs-splash-pref" checked aria-label="<?php esc_attr_e( 'Afficher le splashscreen à l\'ouverture', 'bandstage' ); ?>">
				<span class="bs-splash-pref__track"></span>
			</span>
		</label>

		<button type="button" class="bs-splash-close" onclick="bsSplashClose()">
			<?php esc_html_e( 'Entrer', 'bandstage' ); ?> →
		</button>

	</div>
</div>
<?php endif; ?>

<div class="bs-wrap"
 id="bs-app" data-logged="<?php echo $is_logged ? '1' : '0'; ?>">

	<!-- ================================================================
	     EN-TÊTE
	     ================================================================ -->
	<header class="bs-header">
		<div class="bs-header__sub"><?php echo $bs_tagline; ?></div>
		<div class="bs-header__brand"><?php echo $bs_name; ?></div>
		<div class="bs-header__orn" aria-hidden="true">— ✦ —</div>
	</header>

	<!-- ================================================================
	     TICKER
	     ================================================================ -->
	<?php if ( $bs_ticker && $ticker_text ) : ?>
	<div class="bs-ticker" role="marquee" aria-live="off">
		<div class="bs-ticker-track"><?php echo $ticker_text; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>
	<?php endif; ?>

	<!-- ================================================================
	     GRILLE 2×3
	     ================================================================ -->
	<main class="bs-grid" aria-label="<?php esc_attr_e( 'Sections du site', 'bandstage' ); ?>">
		<?php foreach ( $boxes as $box ) : ?>
			<?php
			// Style du fond : image + overlay couleur, ou dégradé seul.
			if ( $box['img_url'] ) {
				$bg_style = sprintf(
					'background-image: linear-gradient(145deg, %sCC, %sCC), url("%s"); background-size: cover; background-position: center;',
					esc_attr( $box['color_start'] ),
					esc_attr( $box['color_end'] ),
					esc_url( $box['img_url'] )
				);
			} else {
				$bg_style = sprintf(
					'background: linear-gradient(145deg, %s, %s);',
					esc_attr( $box['color_start'] ),
					esc_attr( $box['color_end'] )
				);
			}
			?>
			<a class="bs-box" href="<?php echo $box['link']; ?>" aria-label="<?php echo $box['title']; ?>">
				<div class="bs-box__img" style="<?php echo esc_attr( $bg_style ); ?>">
					<?php echo bandstage_get_icon( $box['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
				<div class="bs-box__label">
					<span class="bs-box__title">
						<?php echo $box['title']; ?>
						<?php if ( $box['is_tchache'] && $tchache_count > 0 ) : ?>
							<span class="bs-badge"><?php echo esc_html( (string) $tchache_count ); ?></span>
						<?php endif; ?>
					</span>
					<span class="bs-box__arrow" aria-hidden="true">→</span>
				</div>
			</a>
		<?php endforeach; ?>
	</main>

	<!-- ================================================================
	     BARRE DE NAVIGATION BAS
	     ================================================================ -->
	<nav class="bs-bnav" aria-label="<?php esc_attr_e( 'Navigation principale', 'bandstage' ); ?>">
		<button class="bs-bnav__item bs-bnav__item--active" id="bs-nav-home" aria-current="page"
		        onclick="bsShowPanel(null,'bs-nav-home')">
			<svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
				<path d="M3 9.5L11 3l8 6.5V19a1 1 0 01-1 1H6a1 1 0 01-1-1V9.5z" stroke="currentColor" stroke-width="1.4"/>
				<path d="M8 20v-7h6v7" stroke="currentColor" stroke-width="1.4"/>
			</svg>
			<span><?php esc_html_e( 'Accueil', 'bandstage' ); ?></span>
		</button>

		<button class="bs-bnav__item" id="bs-nav-compte"
		        onclick="bsShowPanel('bs-panel-compte','bs-nav-compte')">
			<svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
				<circle cx="11" cy="7" r="4" stroke="currentColor" stroke-width="1.4"/>
				<path d="M3 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
			</svg>
			<span><?php esc_html_e( 'Mon Compte', 'bandstage' ); ?></span>
		</button>

		<button class="bs-bnav__item" id="bs-nav-params"
		        onclick="bsShowPanel('bs-panel-params','bs-nav-params')">
			<svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
				<circle cx="11" cy="11" r="3" stroke="currentColor" stroke-width="1.4"/>
				<path d="M11 2v2M11 18v2M2 11h2M18 11h2M4.9 4.9l1.4 1.4M15.7 15.7l1.4 1.4M4.9 17.1l1.4-1.4M15.7 6.3l1.4-1.4"
				      stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
			</svg>
			<span><?php esc_html_e( 'Paramètres', 'bandstage' ); ?></span>
		</button>
	</nav>

	<!-- ================================================================
	     PANEL : MON COMPTE
	     ================================================================ -->
	<div class="bs-panel" id="bs-panel-compte" role="dialog"
	     aria-label="<?php esc_attr_e( 'Mon Compte', 'bandstage' ); ?>" aria-hidden="true">
		<div class="bs-panel__handle" aria-hidden="true"></div>
		<div class="bs-panel__title"><?php esc_html_e( 'Mon Compte', 'bandstage' ); ?></div>

		<div class="bs-panel__body">
			<?php if ( $is_logged ) : ?>
			<!-- Utilisateur connecté -->
			<div class="bs-profile-row">
				<div class="bs-avatar"><?php echo esc_html( $initials ); ?></div>
				<div>
					<div class="bs-profile-name"><?php echo $display_name; ?></div>
					<div class="bs-profile-since">
						<?php
						$registered = strtotime( $current_user->user_registered );
						printf(
							/* translators: %s: date de l'inscription */
							esc_html__( 'Membre depuis %s', 'bandstage' ),
							esc_html( date_i18n( 'F Y', $registered ) )
						);
						?>
					</div>
					<?php if ( $bs_tchache ) : ?>
					<span class="bs-badge-tchache"><?php esc_html_e( 'Accès Tchache actif', 'bandstage' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="bs-section-title"><?php esc_html_e( 'Proposer une reprise', 'bandstage' ); ?></div>
			<textarea class="bs-field" id="bs-reprise-text" rows="3"
			          placeholder="<?php esc_attr_e( 'Ex : « Voodoo Child » de Hendrix, parfait pour votre style blues !', 'bandstage' ); ?>"></textarea>
			<button class="bs-btn bs-btn--gold" onclick="bsSendReprise()"><?php esc_html_e( 'Envoyer au groupe', 'bandstage' ); ?></button>
			<div class="bs-msg" id="bs-reprise-msg"></div>
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="bs-btn bs-btn--outline" style="margin-top:12px">
				<?php esc_html_e( 'Se déconnecter', 'bandstage' ); ?>
			</a>

			<?php else : ?>
			<!-- Non connecté : formulaire login / inscription -->
			<div class="bs-tabs-mini">
				<button class="bs-tab-mini bs-tab-mini--active" onclick="bsSubTab(this,'bs-login')"><?php esc_html_e( 'Se connecter', 'bandstage' ); ?></button>
				<button class="bs-tab-mini" onclick="bsSubTab(this,'bs-register')"><?php esc_html_e( 'Créer un compte', 'bandstage' ); ?></button>
			</div>

			<div id="bs-login">
				<input class="bs-field" type="text"     id="bs-login-user" placeholder="<?php esc_attr_e( 'Pseudo ou e-mail', 'bandstage' ); ?>">
				<input class="bs-field" type="password" id="bs-login-pass" placeholder="<?php esc_attr_e( 'Mot de passe', 'bandstage' ); ?>">
				<button class="bs-btn bs-btn--gold" onclick="bsLogin()"><?php esc_html_e( 'Se connecter', 'bandstage' ); ?></button>
			</div>

			<div id="bs-register" style="display:none">
				<input class="bs-field" type="text"     id="bs-reg-user"  placeholder="<?php esc_attr_e( 'Pseudo', 'bandstage' ); ?>">
				<input class="bs-field" type="email"    id="bs-reg-email" placeholder="<?php esc_attr_e( 'E-mail', 'bandstage' ); ?>">
				<input class="bs-field" type="password" id="bs-reg-pass"  placeholder="<?php esc_attr_e( 'Mot de passe (min. 8 caractères)', 'bandstage' ); ?>">
				<button class="bs-btn bs-btn--gold" onclick="bsRegister()"><?php esc_html_e( 'Créer mon compte', 'bandstage' ); ?></button>
			</div>
			<div class="bs-msg" id="bs-auth-msg"></div>
			<?php endif; ?>
		</div>

		<button class="bs-panel__close" onclick="bsShowPanel(null,'bs-nav-home')" aria-label="<?php esc_attr_e( 'Fermer', 'bandstage' ); ?>">
			↓ <?php esc_html_e( 'Fermer', 'bandstage' ); ?>
		</button>
	</div>

	<!-- ================================================================
	     PANEL : PARAMÈTRES
	     ================================================================ -->
	<div class="bs-panel" id="bs-panel-params" role="dialog"
	     aria-label="<?php esc_attr_e( 'Paramètres', 'bandstage' ); ?>" aria-hidden="true">
		<div class="bs-panel__handle" aria-hidden="true"></div>
		<div class="bs-panel__title"><?php esc_html_e( 'Paramètres', 'bandstage' ); ?></div>

		<div class="bs-panel__body">
			<!-- Apparence -->
			<div class="bs-section-title"><?php esc_html_e( 'Apparence', 'bandstage' ); ?></div>
			<div class="bs-row">
				<div>
					<div class="bs-row__label"><?php esc_html_e( 'Fond du site', 'bandstage' ); ?></div>
					<div class="bs-row__sub"><?php esc_html_e( 'Couleur dominante', 'bandstage' ); ?></div>
				</div>
				<div class="bs-swatches" id="bs-swatches">
					<button class="bs-swatch bs-swatch--sel" style="background:linear-gradient(135deg,#1535A8,#020828)"
					        data-start="#1535A8" data-end="#020828" onclick="bsSetBg(this)" aria-label="Bleu outremer"></button>
					<button class="bs-swatch" style="background:linear-gradient(135deg,#1A1A1A,#050505)"
					        data-start="#1A1A1A" data-end="#050505" onclick="bsSetBg(this)" aria-label="Noir"></button>
					<button class="bs-swatch" style="background:linear-gradient(135deg,#3A1A0A,#1A0A02)"
					        data-start="#3A1A0A" data-end="#1A0A02" onclick="bsSetBg(this)" aria-label="Brun"></button>
					<button class="bs-swatch" style="background:linear-gradient(135deg,#0A2A1A,#020E08)"
					        data-start="#0A2A1A" data-end="#020E08" onclick="bsSetBg(this)" aria-label="Forêt"></button>
				</div>
			</div>
			<div class="bs-row">
				<div>
					<div class="bs-row__label"><?php esc_html_e( 'Animations', 'bandstage' ); ?></div>
					<div class="bs-row__sub"><?php esc_html_e( 'Ticker, transitions', 'bandstage' ); ?></div>
				</div>
				<button class="bs-toggle bs-toggle--on" id="bs-tog-anim" onclick="bsToggle(this,'anim')" aria-pressed="true"></button>
			</div>

			<!-- Notifications -->
			<div class="bs-section-title"><?php esc_html_e( 'Notifications', 'bandstage' ); ?></div>
			<div class="bs-row">
				<div>
					<div class="bs-row__label"><?php esc_html_e( 'Dates de concerts', 'bandstage' ); ?></div>
					<div class="bs-row__sub"><?php esc_html_e( 'Rappel 48h avant', 'bandstage' ); ?></div>
				</div>
				<button class="bs-toggle bs-toggle--on" id="bs-tog-concerts" onclick="bsToggle(this,'concerts')" aria-pressed="true"></button>
			</div>
			<div class="bs-row">
				<div>
					<div class="bs-row__label"><?php esc_html_e( 'Actualités', 'bandstage' ); ?></div>
					<div class="bs-row__sub"><?php esc_html_e( 'Nouveaux billets, EP…', 'bandstage' ); ?></div>
				</div>
				<button class="bs-toggle" id="bs-tog-news" onclick="bsToggle(this,'news')" aria-pressed="false"></button>
			</div>
			<div class="bs-row">
				<div>
					<div class="bs-row__label"><?php esc_html_e( 'Nouveaux messages', 'bandstage' ); ?></div>
					<div class="bs-row__sub"><?php esc_html_e( 'Activité Tchache', 'bandstage' ); ?></div>
				</div>
				<button class="bs-toggle" id="bs-tog-tchache" onclick="bsToggle(this,'tchache')" aria-pressed="false"></button>
			</div>

			<!-- Email newsletter -->
			<div class="bs-section-title"><?php esc_html_e( 'Mon e-mail', 'bandstage' ); ?></div>
			<input class="bs-field" type="email" id="bs-notif-email"
			       value="<?php echo $is_logged ? esc_attr( $current_user->user_email ) : ''; ?>"
			       placeholder="<?php esc_attr_e( 'pour recevoir les news', 'bandstage' ); ?>">
			<button class="bs-btn bs-btn--gold" onclick="bsSavePrefs()"><?php esc_html_e( 'Enregistrer', 'bandstage' ); ?></button>
			<div class="bs-msg" id="bs-prefs-msg"></div>
		</div>

		<button class="bs-panel__close" onclick="bsShowPanel(null,'bs-nav-home')" aria-label="<?php esc_attr_e( 'Fermer', 'bandstage' ); ?>">
			↓ <?php esc_html_e( 'Fermer', 'bandstage' ); ?>
		</button>
	</div>

</div><!-- /.bs-wrap -->
