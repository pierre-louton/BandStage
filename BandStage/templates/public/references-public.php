<?php
/**
 * [bandstage_references] — vue publique (morceaux groupés par style).
 *
 * @var array $grouped  [ key => [ label, image_url, items:Morceau[] ] ]
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bs-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php esc_html_e( 'Répertoire', 'bandstage' ); ?></h1>
  </header>

  <?php if ( empty( $grouped ) ) : ?>
    <div class="bs-empty">
      <p><?php esc_html_e( 'Aucun morceau pour le moment.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <?php foreach ( $grouped as $group ) : ?>
      <section class="bs-ref-section">
        <div class="bs-ref-section__header">
          <?php if ( $group['image_url'] ) : ?>
            <img class="bs-ref-section__img"
                 src="<?php echo esc_url( $group['image_url'] ); ?>"
                 alt="<?php echo esc_attr( $group['label'] ); ?>"
                 loading="lazy">
          <?php endif; ?>
          <h2 class="bs-ref-section__title"><?php echo esc_html( $group['label'] ); ?></h2>
        </div>
        <ul class="bs-ref-list">
          <?php foreach ( $group['items'] as $m ) : ?>
            <li class="bs-ref-item">
              <?php if ( $m->icone_artiste ) : ?>
                <span class="bs-ref-item__icon"><?php echo esc_html( $m->icone_artiste ); ?></span>
              <?php endif; ?>
              <span class="bs-ref-item__artiste"><?php echo esc_html( $m->nom_artiste ); ?></span>
              <span class="bs-ref-item__sep">—</span>
              <span class="bs-ref-item__morceau"><?php echo esc_html( $m->nom_morceau ); ?></span>
              <?php if ( $m->remarque ) : ?>
                <em class="bs-ref-item__remarque"><?php echo esc_html( $m->remarque ); ?></em>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
