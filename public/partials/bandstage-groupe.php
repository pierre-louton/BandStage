<?php
/**
 * Template public — Le Groupe.
 * Shortcode : [bandstage_groupe]
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

BandStage_Public::maybe_inject_dynamic_css();

$band_name     = esc_html( (string) get_option( 'bs_band_name',    'Mon Groupe' ) );
$band_tagline  = esc_html( (string) get_option( 'bs_band_tagline', '' ) );
$band_bio      = wp_kses( (string) get_option( 'bs_band_bio', '' ), array(
	'p'      => array(),
	'strong' => array(),
	'em'     => array(),
	'a'      => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
	'ul'     => array(),
	'ol'     => array(),
	'li'     => array(),
	'br'     => array(),
) );
$band_founded  = sanitize_text_field( (string) get_option( 'bs_band_founded', '' ) );
$members_label = esc_html( (string) get_option( 'bs_band_members_label', __( 'Les musiciens', 'bandstage' ) ) );
$logo_id       = (int) get_option( 'bs_band_logo_id', 0 );
$logo_url      = $logo_id ? wp_get_attachment_image_url( $logo_id, 'large' ) : '';

// Membres du groupe = utilisateurs WP avec rôle Auteur ou supérieur.
$members = get_users( array(
	'role__in' => array( 'author', 'editor', 'administrator' ),
	'orderby'  => 'registered',
	'order'    => 'ASC',
	'fields'   => 'all',
) );

if ( ! function_exists( 'bsg_initials' ) ) :
function bsg_initials( string $name ): string {
	$parts = explode( ' ', trim( $name ) );
	$i = mb_strtoupper( mb_substr( $parts[0], 0, 1 ) );
	if ( isset( $parts[1] ) ) $i .= mb_strtoupper( mb_substr( $parts[1], 0, 1 ) );
	return $i ?: '?';
}
endif;
?>

<div class="bs-gr-wrap">

	<!-- HERO -->
	<div class="bs-gr-hero">
		<?php if ( $logo_url ) : ?>
		<div class="bs-gr-hero__logo-wrap">
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $band_name ); ?>"
			     class="bs-gr-hero__logo">
		</div>
		<?php else : ?>
		<div class="bs-gr-hero__icon" aria-hidden="true">🎸</div>
		<?php endif; ?>

		<h1 class="bs-gr-hero__name"><?php echo $band_name; ?></h1>

		<?php if ( $band_tagline ) : ?>
		<p class="bs-gr-hero__tagline"><?php echo $band_tagline; ?></p>
		<?php endif; ?>

		<?php if ( $band_founded ) : ?>
		<p class="bs-gr-hero__founded">
			<?php
			printf(
				/* translators: %s: année */
				esc_html__( 'Depuis %s', 'bandstage' ),
				esc_html( $band_founded )
			);
			?>
		</p>
		<?php endif; ?>
	</div>

	<!-- BIOGRAPHIE -->
	<?php if ( $band_bio ) : ?>
	<section class="bs-gr-bio">
		<h2 class="bs-gr-section-title">
			<span class="bs-gr-section-title__line"></span>
			<?php esc_html_e( 'Notre histoire', 'bandstage' ); ?>
			<span class="bs-gr-section-title__line"></span>
		</h2>
		<div class="bs-gr-bio__content">
			<?php echo wpautop( $band_bio ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- MEMBRES -->
	<?php if ( ! empty( $members ) ) : ?>
	<section class="bs-gr-members">
		<h2 class="bs-gr-section-title">
			<span class="bs-gr-section-title__line"></span>
			<?php echo $members_label; ?>
			<span class="bs-gr-section-title__line"></span>
		</h2>
		<div class="bs-gr-members-grid">
			<?php foreach ( $members as $member ) :
				$member_id   = $member->ID;
				$avatar_url  = get_avatar_url( $member_id, array( 'size' => 120, 'default' => '404' ) );
				$has_avatar  = $avatar_url && ! str_contains( (string) $avatar_url, 'gravatar.com/avatar/00000' );
				$instrument  = (string) get_user_meta( $member_id, 'bs_instrument', true );
				$bio_short   = (string) get_user_meta( $member_id, 'bs_bio',        true );
				$location    = (string) get_user_meta( $member_id, 'bs_location',   true );
				$initials    = bsg_initials( $member->display_name );
			?>
			<div class="bs-gr-member-card">
				<!-- Avatar -->
				<div class="bs-gr-member__avatar">
					<?php if ( $has_avatar ) : ?>
					<img src="<?php echo esc_url( $avatar_url ); ?>"
					     alt="<?php echo esc_attr( $member->display_name ); ?>"
					     loading="lazy">
					<?php else : ?>
					<span class="bs-gr-member__initials"><?php echo esc_html( $initials ); ?></span>
					<?php endif; ?>
				</div>

				<!-- Infos -->
				<div class="bs-gr-member__info">
					<div class="bs-gr-member__name"><?php echo esc_html( $member->display_name ); ?></div>

					<?php if ( $instrument ) : ?>
					<div class="bs-gr-member__instrument">
						<svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
							<path d="M9 1.5l3.5 3.5-6 6-2 .5.5-2 6-6z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
							<path d="M1 13l2-1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
						</svg>
						<?php echo esc_html( $instrument ); ?>
					</div>
					<?php endif; ?>

					<?php if ( $location ) : ?>
					<div class="bs-gr-member__location">
						📍 <?php echo esc_html( $location ); ?>
					</div>
					<?php endif; ?>

					<?php if ( $bio_short ) : ?>
					<p class="bs-gr-member__bio"><?php echo esc_html( $bio_short ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

</div><!-- /.bs-gr-wrap -->
