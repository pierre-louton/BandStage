<?php
/**
 * [bandstage_partenaires] bs_view=list — gestion partenaires (Auteur+).
 *
 * @var \BandStage\Domain\Partenaires\Partenaire[] $partenaires
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$can_delete = current_user_can( 'manage_options' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <span class="bss-navbar__title"><?php esc_html_e( 'Partenaires', 'bandstage' ); ?></span>
    <a href="<?php echo esc_url( Shortcodes::partenaires_url( 'edit' ) ); ?>"
       class="bss-navbar__action bss-btn bss-btn--primary">
      <?php esc_html_e( '+ Ajouter', 'bandstage' ); ?>
    </a>
  </nav>

  <?php if ( empty( $partenaires ) ) : ?>
    <div class="bss-empty">
      <p><?php esc_html_e( 'Aucun partenaire.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bss-partenaire-list">
      <?php foreach ( $partenaires as $p ) : ?>
        <li class="bss-partenaire-item" data-id="<?php echo esc_attr( $p->id ); ?>">

          <div class="bss-partenaire-item__logo">
            <?php if ( $p->logo_url ) : ?>
              <img src="<?php echo esc_url( $p->logo_url ); ?>" alt="">
            <?php else : ?>
              <span class="bss-partenaire-item__initials">
                <?php echo esc_html( mb_strtoupper( mb_substr( $p->name, 0, 2 ) ) ); ?>
              </span>
            <?php endif; ?>
          </div>

          <div class="bss-partenaire-item__info">
            <strong><?php echo esc_html( $p->name ); ?></strong>
            <?php if ( $p->type_name ) : ?>
              <span class="bss-partenaire-item__type">
                <?php if ( $p->type_icon ) echo esc_html( $p->type_icon ) . ' '; ?>
                <?php echo esc_html( $p->type_name ); ?>
              </span>
            <?php endif; ?>
            <?php $addr = $p->address_full(); if ( $addr ) : ?>
              <em class="bss-partenaire-item__address"><?php echo esc_html( $addr ); ?></em>
            <?php endif; ?>
          </div>

          <div class="bss-partenaire-item__actions">
            <a href="<?php echo esc_url( Shortcodes::partenaires_url( 'edit', $p->id ) ); ?>"
               class="bss-btn bss-btn--sm bss-btn--ghost">
              <?php esc_html_e( 'Modifier', 'bandstage' ); ?>
            </a>
            <?php if ( $can_delete ) : ?>
              <button
                type="button"
                class="bss-btn bss-btn--sm bss-btn--danger js-partenaire-delete"
                data-id="<?php echo esc_attr( $p->id ); ?>"
                data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
                <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
              </button>
            <?php endif; ?>
          </div>

        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
