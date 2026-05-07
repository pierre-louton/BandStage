<?php
/**
 * [bandstage_groupe] — vue publique (tous visiteurs).
 * Grille des membres du groupe : photo, nom, rôle, styles.
 *
 * @var \BandStage\Domain\Lineup\LineupMember[] $members
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$band_name    = esc_html( get_option( 'bs_band_name', 'BandStage' ) );
$band_tagline = esc_html( get_option( 'bs_band_tagline', '' ) );
?>
<div class="bs-gr-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php echo $band_name; ?></h1>
    <?php if ( $band_tagline ) : ?>
      <p class="bs-header__tagline"><?php echo $band_tagline; ?></p>
    <?php endif; ?>
    <p class="bs-header__section"><?php esc_html_e( 'Le groupe', 'bandstage' ); ?></p>
  </header>

  <?php if ( empty( $members ) ) : ?>
    <div class="bs-empty">
      <p><?php esc_html_e( 'Aucun membre renseigné pour le moment.', 'bandstage' ); ?></p>
    </div>

  <?php else : ?>
    <div class="bs-lineup-grid">
      <?php foreach ( $members as $member ) : ?>
        <div class="bs-lineup-card">

          <div class="bs-lineup-card__photo">
            <?php if ( $member->thumbnail_url ) : ?>
              <img
                src="<?php echo esc_url( $member->thumbnail_url ); ?>"
                alt="<?php echo esc_attr( $member->name ); ?>"
                loading="lazy"
              >
            <?php else : ?>
              <div class="bs-lineup-card__initials" aria-hidden="true">
                <?php echo esc_html( mb_strtoupper( mb_substr( $member->name, 0, 2 ) ) ); ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="bs-lineup-card__info">
            <h2 class="bs-lineup-card__name"><?php echo esc_html( $member->name ); ?></h2>
            <?php if ( $member->role ) : ?>
              <p class="bs-lineup-card__role"><?php echo esc_html( $member->role ); ?></p>
            <?php endif; ?>
            <?php if ( $member->styles ) : ?>
              <p class="bs-lineup-card__styles"><?php echo esc_html( $member->styles ); ?></p>
            <?php endif; ?>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
