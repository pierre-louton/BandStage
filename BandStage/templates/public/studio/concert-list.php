<?php
/**
 * [bandstage_concerts] bs_view=list — gestion concerts (Auteur+).
 *
 * @var \BandStage\Domain\Concerts\Concert[] $concerts
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$can_delete = current_user_can( 'edit_posts' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <span class="bss-navbar__title"><?php esc_html_e( 'Concerts', 'bandstage' ); ?></span>
    <a href="<?php echo esc_url( Shortcodes::concerts_url( 'edit' ) ); ?>"
       class="bss-navbar__action bss-btn bss-btn--primary">
      <?php esc_html_e( '+ Ajouter', 'bandstage' ); ?>
    </a>
  </nav>

  <?php if ( empty( $concerts ) ) : ?>
    <div class="bss-empty">
      <p><?php esc_html_e( 'Aucun concert.', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bss-concert-list">
      <?php foreach ( $concerts as $c ) : ?>
        <li class="bss-concert-item" data-id="<?php echo esc_attr( $c->id ); ?>">
          <div class="bss-concert-item__date"><?php echo esc_html( $c->dates_formatted() ); ?></div>
          <div class="bss-concert-item__info">
            <strong><?php echo esc_html( $c->titre ); ?></strong>
            <?php if ( $c->nom_lieu ) : ?>
              <span class="bss-concert-item__lieu"><?php echo esc_html( $c->nom_lieu ); ?></span>
            <?php endif; ?>
            <?php if ( $c->horaires ) : ?>
              <em class="bss-concert-item__horaires"><?php echo esc_html( $c->horaires ); ?></em>
            <?php endif; ?>
          </div>
          <div class="bss-concert-item__actions">
            <a href="<?php echo esc_url( Shortcodes::concerts_url( 'edit', $c->id ) ); ?>"
               class="bss-btn bss-btn--sm bss-btn--ghost">
              <?php esc_html_e( 'Modifier', 'bandstage' ); ?>
            </a>
            <?php if ( $can_delete ) : ?>
              <button
                type="button"
                class="bss-btn bss-btn--sm bss-btn--danger js-concert-delete"
                data-id="<?php echo esc_attr( $c->id ); ?>"
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
