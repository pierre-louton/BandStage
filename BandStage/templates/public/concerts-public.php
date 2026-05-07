<?php
/**
 * [bandstage_concerts] — vue publique (concerts à venir).
 *
 * @var \BandStage\Domain\Concerts\Concert[] $concerts
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bs-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php esc_html_e( 'Concerts', 'bandstage' ); ?></h1>
    <p class="bs-header__tagline"><?php esc_html_e( 'Prochaines dates', 'bandstage' ); ?></p>
  </header>

  <?php if ( empty( $concerts ) ) : ?>
    <div class="bs-empty">
      <p><?php esc_html_e( 'Aucun concert à venir pour le moment.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bs-concert-list">
      <?php foreach ( $concerts as $c ) : ?>
        <li class="bs-concert-card">
          <div class="bs-concert-card__date"><?php echo esc_html( $c->dates_formatted() ); ?></div>
          <div class="bs-concert-card__body">
            <h2 class="bs-concert-card__titre"><?php echo esc_html( $c->titre ); ?></h2>
            <?php if ( $c->nom_lieu ) : ?>
              <p class="bs-concert-card__lieu">
                📍 <?php echo esc_html( $c->nom_lieu ); ?>
                <?php $addr = $c->address_full(); if ( $addr ) : ?>
                  <span class="bs-concert-card__address"><?php echo esc_html( $addr ); ?></span>
                <?php endif; ?>
              </p>
            <?php endif; ?>
            <?php if ( $c->horaires ) : ?>
              <p class="bs-concert-card__horaires">🕐 <?php echo esc_html( $c->horaires ); ?></p>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

</div>
