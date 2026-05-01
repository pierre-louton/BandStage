<?php
/**
 * Template front-end du Tchache (mini-forum).
 * Utilisé par le shortcode [bandstage_tchache].
 *
 * @package BandStage
 */

defined( 'ABSPATH' ) || exit;

if ( ! (bool) get_option( 'bs_tchache_enabled', true ) ) {
	return;
}

global $wpdb;
$table = $wpdb->prefix . 'bandstage_messages';

// Réglages.
$members_only = (bool) get_option( 'bs_tchache_members_only', false );
$max_length   = absint( get_option( 'bs_tchache_max_length', 500 ) );
$is_logged    = is_user_logged_in();
$current_user = $is_logged ? wp_get_current_user() : null;
$can_post     = $is_logged || ! $members_only;

// Chargement initial des messages (25 derniers, du plus ancien au plus récent).
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$messages = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM (
			SELECT * FROM `{$table}` WHERE status = 'approved' ORDER BY created_at DESC LIMIT %d
		) sub ORDER BY created_at ASC",
		25
	),
	ARRAY_A
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$total_approved = (int) $wpdb->get_var(
	"SELECT COUNT(*) FROM `{$table}` WHERE status = 'approved'"
);

$has_more = $total_approved > 25;

/**
 * Formate une date en "il y a X".
 *
 * @param string $datetime Datetime SQL.
 * @return string
 */
if ( ! function_exists( 'bs_human_time' ) ) :
function bs_human_time( string $datetime ): string {
	$diff = time() - strtotime( $datetime );
	if ( $diff < 60 ) {
		return __( 'À l\'instant', 'bandstage' );
	}
	if ( $diff < 3600 ) {
		$m = (int) floor( $diff / 60 );
		/* translators: %d: minutes */
		return sprintf( _n( 'Il y a %d min', 'Il y a %d min', $m, 'bandstage' ), $m );
	}
	if ( $diff < 86400 ) {
		$h = (int) floor( $diff / 3600 );
		/* translators: %d: heures */
		return sprintf( _n( 'Il y a %dh', 'Il y a %dh', $h, 'bandstage' ), $h );
	}
	if ( $diff < 604800 ) {
		$d = (int) floor( $diff / 86400 );
		/* translators: %d: jours */
		return sprintf( _n( 'Il y a %d jour', 'Il y a %d jours', $d, 'bandstage' ), $d );
	}
	return date_i18n( 'd M Y', strtotime( $datetime ) );
}
endif; // function_exists bs_human_time

/**
 * Retourne les initiales d'un nom.
 */
if ( ! function_exists( 'bs_initials' ) ) :
function bs_initials( string $name ): string {
	$parts = explode( ' ', trim( $name ) );
	$init  = mb_strtoupper( mb_substr( $parts[0], 0, 1 ) );
	if ( isset( $parts[1] ) ) {
		$init .= mb_strtoupper( mb_substr( $parts[1], 0, 1 ) );
	}
	return $init ?: '?';
}
endif; // function_exists bs_initials

// CSS dynamique — reprend les variables de la homepage si disponibles.

?>

<div class="bs-tc-wrap" id="bs-tchache"
     data-max-length="<?php echo esc_attr( (string) $max_length ); ?>"
     data-members-only="<?php echo $members_only ? '1' : '0'; ?>"
     data-logged="<?php echo $is_logged ? '1' : '0'; ?>">

	<!-- ================================================================
	     EN-TÊTE
	     ================================================================ -->
	<header class="bs-tc-header">
		<div class="bs-tc-header__left">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="flex-shrink:0">
				<path d="M4 6C4 4.9 4.9 4 6 4H18C19.1 4 20 4.9 20 6V14C20 15.1 19.1 16 18 16H13L9 20V16H6C4.9 16 4 15.1 4 14V6Z"
				      stroke="currentColor" stroke-width="1.5" fill="none"/>
				<line x1="8" y1="9"  x2="16" y2="9"  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
				<line x1="8" y1="12" x2="13" y2="12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
			</svg>
			<h2 class="bs-tc-title">
				<?php esc_html_e( 'Tchache', 'bandstage' ); ?>
				<?php if ( $total_approved > 0 ) : ?>
				<span class="bs-tc-count" id="bs-tc-count"><?php echo esc_html( (string) $total_approved ); ?></span>
				<?php endif; ?>
			</h2>
		</div>
		<div class="bs-tc-header__right">
			<span class="bs-tc-online">
				<span class="bs-tc-dot"></span>
				<?php esc_html_e( 'en direct', 'bandstage' ); ?>
			</span>
		</div>
	</header>

	<!-- ================================================================
	     LISTE DES MESSAGES
	     ================================================================ -->
	<div class="bs-tc-messages" id="bs-tc-messages" role="log" aria-live="polite" aria-label="<?php esc_attr_e( 'Messages du Tchache', 'bandstage' ); ?>">

		<?php if ( $has_more ) : ?>
		<div class="bs-tc-loadmore-wrap" id="bs-tc-loadmore-wrap">
			<button type="button" class="bs-tc-loadmore" id="bs-tc-loadmore"
			        data-page="2" data-total="<?php echo esc_attr( (string) $total_approved ); ?>">
				<?php
				printf(
					/* translators: %d: nombre de messages précédents */
					esc_html__( '↑ Voir les %d messages précédents', 'bandstage' ),
					esc_html( (string) ( $total_approved - 25 ) )
				);
				?>
			</button>
		</div>
		<?php endif; ?>

		<?php if ( empty( $messages ) ) : ?>
		<div class="bs-tc-empty" id="bs-tc-empty">
			<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true">
				<path d="M6 10C6 7.8 7.8 6 10 6H30C32.2 6 34 7.8 34 10V22C34 24.2 32.2 26 30 26H22L16 33V26H10C7.8 26 6 24.2 6 22V10Z"
				      stroke="currentColor" stroke-width="1.5" fill="none" opacity=".4"/>
			</svg>
			<p><?php esc_html_e( 'Pas encore de message. Soyez le premier à tchacher !', 'bandstage' ); ?></p>
		</div>
		<?php else : ?>
		<?php foreach ( $messages as $msg ) : ?>
		<?php
		$uid    = (int) $msg['user_id'];
		// default='404' évite l'icône WP mystère — on affiche les initiales à la place.
		$avatar_url = $uid
			? get_avatar_url( $uid,                 array( 'size' => 40, 'default' => '404' ) )
			: get_avatar_url( $msg['author_email'], array( 'size' => 40, 'default' => '404' ) );
		// Si Gravatar absent (404), on n'affichera que les initiales.
		$avatar = ( $avatar_url && ! str_contains( (string) $avatar_url, 'gravatar.com/avatar/00000' ) )
		          ? $avatar_url : '';
		$initials = bs_initials( $msg['author_name'] );
		$mine     = $is_logged && $current_user && (int) $current_user->ID === $uid;
		?>
		<div class="bs-tc-msg<?php echo $mine ? ' bs-tc-msg--mine' : ''; ?>"
		     data-id="<?php echo esc_attr( (string) $msg['id'] ); ?>">
			<?php if ( ! $mine ) : ?>
			<div class="bs-tc-msg__avatar" title="<?php echo esc_attr( $msg['author_name'] ); ?>">
				<?php if ( $avatar ) : ?>
				<img src="<?php echo esc_url( $avatar ); ?>" alt="" width="36" height="36" loading="lazy"
				     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
				<span style="display:none"><?php echo esc_html( $initials ); ?></span>
				<?php else : ?>
				<span><?php echo esc_html( $initials ); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<div class="bs-tc-msg__bubble">
				<?php if ( ! $mine ) : ?>
				<div class="bs-tc-msg__author"><?php echo esc_html( $msg['author_name'] ); ?></div>
				<?php endif; ?>
				<div class="bs-tc-msg__text"><?php echo nl2br( esc_html( $msg['content'] ) ); ?></div>
				<div class="bs-tc-msg__time"><?php echo esc_html( bs_human_time( $msg['created_at'] ) ); ?></div>
			</div>
		</div>
		<?php endforeach; ?>
		<?php endif; ?>

	</div><!-- /.bs-tc-messages -->

	<!-- ================================================================
	     ZONE DE SAISIE
	     ================================================================ -->
	<div class="bs-tc-compose">

		<?php if ( ! $can_post ) : ?>
		<!-- Connexion requise -->
		<div class="bs-tc-login-prompt">
			<svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true">
				<circle cx="10" cy="7" r="3.5" stroke="currentColor" stroke-width="1.4"/>
				<path d="M3 19c0-3.9 3.1-7 7-7s7 3.1 7 7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
			</svg>
			<span><?php esc_html_e( 'Connectez-vous pour participer au Tchache', 'bandstage' ); ?></span>
		</div>

		<?php else : ?>
		<!-- Formulaire de saisie -->
		<?php if ( ! $is_logged ) : ?>
		<div class="bs-tc-anon-fields" id="bs-tc-anon">
			<input type="text" class="bs-tc-field" id="bs-tc-name"
			       placeholder="<?php esc_attr_e( 'Votre pseudo', 'bandstage' ); ?>" maxlength="60">
		</div>
		<?php else : ?>
		<div class="bs-tc-composer-user">
			<div class="bs-tc-composer-avatar">
				<?php if ( $current_user ) : ?>
				<?php $av = get_avatar_url( $current_user->ID, array( 'size' => 32 ) ); ?>
				<?php if ( $av ) : ?>
				<img src="<?php echo esc_url( $av ); ?>" alt="" width="32" height="32">
				<?php else : ?>
				<?php echo esc_html( bs_initials( $current_user->display_name ) ); ?>
				<?php endif; ?>
				<?php endif; ?>
			</div>
			<span class="bs-tc-composer-name">
				<?php echo $current_user ? esc_html( $current_user->display_name ) : ''; ?>
			</span>
		</div>
		<?php endif; ?>

		<div class="bs-tc-input-row">
			<textarea
				class="bs-tc-textarea"
				id="bs-tc-textarea"
				rows="1"
				maxlength="<?php echo esc_attr( (string) $max_length ); ?>"
				placeholder="<?php esc_attr_e( 'Votre message…', 'bandstage' ); ?>"
				aria-label="<?php esc_attr_e( 'Écrire un message', 'bandstage' ); ?>"
			></textarea>
			<button type="button" class="bs-tc-send" id="bs-tc-send" aria-label="<?php esc_attr_e( 'Envoyer', 'bandstage' ); ?>">
				<svg width="18" height="18" viewBox="0 0 20 20" fill="none">
					<path d="M2 10L18 2L10 18L8 12L2 10Z" stroke="currentColor" stroke-width="1.5"
					      stroke-linejoin="round" fill="currentColor" fill-opacity=".15"/>
				</svg>
			</button>
		</div>
		<div class="bs-tc-compose-footer">
			<span class="bs-tc-charcount" id="bs-tc-charcount">
				0 / <?php echo esc_html( (string) $max_length ); ?>
			</span>
			<span class="bs-tc-feedback" id="bs-tc-feedback"></span>
		</div>
		<?php endif; ?>

	</div><!-- /.bs-tc-compose -->

</div><!-- /.bs-tc-wrap -->
