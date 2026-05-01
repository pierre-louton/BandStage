<?php
/**
 * Template public — Concerts.
 * Shortcode : [bandstage_concerts]
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

BandStage_Public::maybe_inject_dynamic_css();

$today = gmdate( 'Y-m-d' );
$band_name = esc_html( (string) get_option( 'bs_band_name', 'BandStage' ) );

// Récupère tous les concerts publiés, triés par date de concert.
$all_concerts = get_posts( array(
	'post_type'      => 'bs_concert',
	'post_status'    => 'publish',
	'posts_per_page' => 50,
	'meta_key'       => 'bs_concert_date',
	'orderby'        => 'meta_value',
	'order'          => 'ASC',
) );

// Sépare à venir / passés.
$upcoming = array();
$past     = array();

foreach ( $all_concerts as $c ) {
	$date = (string) get_post_meta( $c->ID, 'bs_concert_date', true );
	if ( $date >= $today ) {
		$upcoming[] = $c;
	} else {
		array_unshift( $past, $c ); // plus récent en premier
	}
}

// Lien Studio pour musiciens connectés.
$studio_url = '';
if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
	// L'URL pointe vers le CPT WP natif pour l'instant (Studio concerts à venir).
	$studio_url = admin_url( 'post-new.php?post_type=bs_concert' );
}

/**
 * Formate un concert en bloc HTML.
 *
 * @param WP_Post $concert     Post bs_concert.
 * @param bool    $is_past     True si le concert est passé.
 * @return string
 */
function bsc_render_concert( WP_Post $concert, bool $is_past = false ): string {
	$pid     = $concert->ID;
	$date    = (string) get_post_meta( $pid, 'bs_concert_date',    true );
	$heure   = (string) get_post_meta( $pid, 'bs_concert_heure',   true );
	$lieu    = (string) get_post_meta( $pid, 'bs_concert_lieu',    true );
	$ville   = (string) get_post_meta( $pid, 'bs_concert_ville',   true );
	$adresse = (string) get_post_meta( $pid, 'bs_concert_adresse', true );
	$billets = (string) get_post_meta( $pid, 'bs_concert_billets', true );
	$gratuit = (bool)   get_post_meta( $pid, 'bs_concert_gratuit', true );
	$desc    = get_the_content( null, false, $concert );
	$thumb   = get_the_post_thumbnail_url( $pid, 'medium' );

	$ts = $date ? strtotime( $date ) : 0;
	if ( ! $ts ) return '';

	// Décompose la date pour l'affichage vertical.
	$day   = date_i18n( 'd',   $ts );
	$month = date_i18n( 'M',   $ts );
	$year  = date_i18n( 'Y',   $ts );
	$dow   = date_i18n( 'l',   $ts ); // Lundi, Mardi…

	ob_start();
	?>
	<article class="bs-cc-card <?php echo $is_past ? 'bs-cc-card--past' : 'bs-cc-card--upcoming'; ?>"
	         id="bs-concert-<?php echo esc_attr( (string) $pid ); ?>">

		<?php if ( $thumb && ! $is_past ) : ?>
		<div class="bs-cc-card__thumb" style="background-image:url('<?php echo esc_url( $thumb ); ?>')"></div>
		<?php endif; ?>

		<div class="bs-cc-card__inner">

			<!-- Calendrier -->
			<div class="bs-cc-cal <?php echo $is_past ? 'bs-cc-cal--past' : ''; ?>">
				<div class="bs-cc-cal__day"><?php echo esc_html( $day ); ?></div>
				<div class="bs-cc-cal__month"><?php echo esc_html( $month ); ?></div>
				<div class="bs-cc-cal__year"><?php echo esc_html( $year ); ?></div>
			</div>

			<!-- Détails -->
			<div class="bs-cc-details">
				<div class="bs-cc-details__dow"><?php echo esc_html( ucfirst( $dow ) ); ?></div>
				<h2 class="bs-cc-details__title"><?php echo esc_html( get_the_title( $concert ) ); ?></h2>

				<?php if ( $lieu || $ville ) : ?>
				<div class="bs-cc-details__location">
					<svg width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
						<path d="M6.5 1a3 3 0 013 3c0 3-3 7-3 7S3.5 7 3.5 4a3 3 0 013-3z" stroke="currentColor" stroke-width="1.2"/>
						<circle cx="6.5" cy="4" r="1" fill="currentColor"/>
					</svg>
					<span>
						<?php echo esc_html( implode( ' — ', array_filter( array( $lieu, $ville ) ) ) ); ?>
					</span>
				</div>
				<?php endif; ?>

				<?php if ( $heure ) : ?>
				<div class="bs-cc-details__time">
					<svg width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
						<circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.2"/>
						<path d="M6.5 4v2.5L8.5 8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
					</svg>
					<?php echo esc_html( $heure ); ?>
				</div>
				<?php endif; ?>

				<?php if ( $adresse ) : ?>
				<div class="bs-cc-details__addr"><?php echo esc_html( $adresse ); ?></div>
				<?php endif; ?>

				<?php if ( $desc ) : ?>
				<div class="bs-cc-details__desc"><?php echo wp_kses_post( wpautop( $desc ) ); ?></div>
				<?php endif; ?>

				<!-- CTA -->
				<?php if ( ! $is_past ) : ?>
				<div class="bs-cc-details__cta">
					<?php if ( $gratuit ) : ?>
					<span class="bs-cc-free"><?php esc_html_e( 'Entrée libre', 'bandstage' ); ?> 🎉</span>
					<?php elseif ( $billets ) : ?>
					<a href="<?php echo esc_url( $billets ); ?>" target="_blank" rel="noopener noreferrer"
					   class="bs-cc-tickets-btn">
						<?php esc_html_e( 'Réserver', 'bandstage' ); ?>
						<svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
							<path d="M5 2H2v8h8V7M7 1h4v4M10 1L5 6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

		</div><!-- /.bs-cc-card__inner -->

		<?php if ( $is_past ) : ?>
		<div class="bs-cc-past-label"><?php esc_html_e( 'Passé', 'bandstage' ); ?></div>
		<?php endif; ?>

	</article>
	<?php
	return ob_get_clean();
}
?>

<div class="bs-cc-wrap">

	<!-- EN-TÊTE -->
	<header class="bs-cc-header">
		<div class="bs-cc-header__left">
			<svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
				<ellipse cx="11" cy="9" rx="4" ry="5" stroke="currentColor" stroke-width="1.4"/>
				<path d="M4 18 Q4 13 11 13 Q18 13 18 18" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" fill="none"/>
				<path d="M6 5 L5 3 M16 5 L17 3" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity=".5"/>
			</svg>
			<h1 class="bs-cc-title"><?php esc_html_e( 'Concerts', 'bandstage' ); ?></h1>
		</div>
		<?php if ( $studio_url ) : ?>
		<a href="<?php echo esc_url( $studio_url ); ?>" class="bs-cc-add-btn" title="<?php esc_attr_e( 'Ajouter un concert', 'bandstage' ); ?>">
			+ <?php esc_html_e( 'Ajouter', 'bandstage' ); ?>
		</a>
		<?php endif; ?>
	</header>

	<!-- PROCHAINS CONCERTS -->
	<?php if ( empty( $upcoming ) ) : ?>
	<div class="bs-cc-empty">
		<div class="bs-cc-empty__icon">🎤</div>
		<p><?php esc_html_e( 'Aucune date annoncée pour l\'instant.', 'bandstage' ); ?></p>
		<p class="bs-cc-empty__sub"><?php echo $band_name; ?> &mdash; <?php esc_html_e( 'restez connectés !', 'bandstage' ); ?></p>
	</div>
	<?php else : ?>
	<section class="bs-cc-upcoming">
		<div class="bs-cc-section-label"><?php esc_html_e( 'Prochaines dates', 'bandstage' ); ?></div>
		<?php foreach ( $upcoming as $concert ) : ?>
		<?php echo bsc_render_concert( $concert, false ); // phpcs:ignore ?>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>

	<!-- CONCERTS PASSÉS -->
	<?php if ( ! empty( $past ) ) : ?>
	<section class="bs-cc-past">
		<button type="button" class="bs-cc-past-toggle" id="bs-cc-past-toggle" aria-expanded="false">
			<?php printf( esc_html__( 'Concerts passés (%d)', 'bandstage' ), count( $past ) ); ?>
			<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true" class="bs-cc-chevron">
				<path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
			</svg>
		</button>
		<div class="bs-cc-past-list" id="bs-cc-past-list" hidden>
			<?php foreach ( $past as $concert ) : ?>
			<?php echo bsc_render_concert( $concert, true ); // phpcs:ignore ?>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

</div><!-- /.bs-cc-wrap -->

<script>
( function () {
	var btn  = document.getElementById( 'bs-cc-past-toggle' );
	var list = document.getElementById( 'bs-cc-past-list' );
	if ( ! btn || ! list ) return;
	btn.addEventListener( 'click', function () {
		var open = ! list.hidden;
		list.hidden = open;
		btn.setAttribute( 'aria-expanded', open ? 'false' : 'true' );
		btn.querySelector( '.bs-cc-chevron' ).style.transform = open ? '' : 'rotate(180deg)';
	} );
} )();
</script>
