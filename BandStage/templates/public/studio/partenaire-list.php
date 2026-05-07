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

use BandStage\Public\Shortcodes;

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
      <p><?php esc_html_e( 'Aucun partenaire. Ajoutez le premier !', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bss-partenaire-list">
      <?php foreach ( $partenaires as $p ) : ?>
        <li class="bss-partenaire-item">
          <?php if ( $p->thumbnail ) : ?>
            <img class="bss-partenaire-item__thumb"
                 src="<?php echo esc_url( $p->thumbnail ); ?>"
                 alt="<?php echo esc_attr( $p->name ); ?>">
          <?php endif; ?>
          <div class="bss-partenaire-item__info">
            <strong><?php echo esc_html( $p->name ); ?></strong>
            <?php if ( $p->type_icon && $p->type_label ) : ?>
              <span class="bss-badge"><?php echo esc_html( $p->type_icon . ' ' . $p->type_label ); ?></span>
            <?php endif; ?>
          </div>
          <div class="bss-partenaire-item__actions">
            <a href="<?php echo esc_url( Shortcodes::partenaires_url( 'edit', $p->id ) ); ?>"
               class="bss-btn bss-btn--sm bss-btn--ghost">
              <?php esc_html_e( 'Modifier', 'bandstage' ); ?>
            </a>
            <?php if ( $can_delete ) : ?>
              <button type="button"
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
