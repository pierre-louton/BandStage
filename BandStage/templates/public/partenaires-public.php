<?php
/**
 * [bandstage_partenaires] — vue publique.
 *
 * @var array $partenaires            groupés : [ slug => [ label, icon, items[] ] ]
 * @var array $concerts_by_partenaire  concerts à venir par partenaire_id
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bs-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php esc_html_e( 'Partenaires', 'bandstage' ); ?></h1>
  </header>

  <?php if ( empty( $partenaires ) ) : ?>
    <div class="bs-empty">
      <p><?php esc_html_e( 'Aucun partenaire pour le moment.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <?php foreach ( $partenaires as $type ) : ?>
      <section class="bs-pp-section">
        <h2 class="bs-pp-section__title">
          <?php if ( $type['icon'] ) echo esc_html( $type['icon'] ) . ' '; ?>
          <?php echo esc_html( $type['label'] ); ?>
        </h2>
        <div class="bs-pp-grid">
          <?php foreach ( $type['items'] as $p ) : ?>
            <div class="bs-pp-card">
              <?php if ( $p->logo_url ) : ?>
                <img class="bs-pp-card__thumb"
                     src="<?php echo esc_url( $p->logo_url ); ?>"
                     alt="<?php echo esc_attr( $p->name ); ?>"
                     loading="lazy">
              <?php endif; ?>
              <div class="bs-pp-card__body">
                <h3 class="bs-pp-card__name"><?php echo esc_html( $p->name ); ?></h3>
                <?php if ( $p->description ) : ?>
                  <p class="bs-pp-card__desc"><?php echo esc_html( $p->description ); ?></p>
                <?php endif; ?>
                <ul class="bs-pp-card__contacts">
                  <?php $addr = $p->address_full(); if ( $addr ) : ?>
                    <li>📍 <?php echo esc_html( $addr ); ?></li>
                  <?php endif; ?>
                  <?php if ( $p->phone ) : ?>
                    <li><a href="tel:<?php echo esc_attr( $p->phone ); ?>"><?php echo esc_html( $p->phone ); ?></a></li>
                  <?php endif; ?>
                  <?php if ( $p->email ) : ?>
                    <li><a href="mailto:<?php echo esc_attr( $p->email ); ?>"><?php echo esc_html( $p->email ); ?></a></li>
                  <?php endif; ?>
                  <?php if ( $p->website ) : ?>
                    <li><a href="<?php echo esc_url( $p->website ); ?>" target="_blank" rel="noopener">🌐 <?php esc_html_e( 'Site web', 'bandstage' ); ?></a></li>
                  <?php endif; ?>
                </ul>
                <?php if ( ! empty( $concerts_by_partenaire[ $p->id ] ) ) : ?>
                  <ul class="bs-pp-card__concerts">
                    <?php foreach ( $concerts_by_partenaire[ $p->id ] as $co ) : ?>
                      <li>
                        <span class="bs-pp-card__concert-date">
                          <?php echo esc_html( date_i18n( 'j F Y', strtotime( $co->date_debut ) ) ); ?>
                        </span>
                        <?php echo esc_html( $co->titre ); ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
