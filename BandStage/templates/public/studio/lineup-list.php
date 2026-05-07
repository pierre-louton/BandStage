<?php
/**
 * [bandstage_groupe] bs_view=list — gestion du lineup (Auteur+).
 *
 * @var \BandStage\Domain\Lineup\LineupMember[] $members
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$can_delete = current_user_can( 'manage_options' );
?>
<div class="bs-gr-wrap">

  <nav class="bss-navbar">
    <span class="bss-navbar__title"><?php esc_html_e( 'Le groupe', 'bandstage' ); ?></span>
    <a href="<?php echo esc_url( Shortcodes::groupe_url( 'edit' ) ); ?>"
       class="bss-navbar__action bss-btn bss-btn--primary">
      <?php esc_html_e( '+ Ajouter', 'bandstage' ); ?>
    </a>
  </nav>

  <?php if ( empty( $members ) ) : ?>
    <div class="bss-empty">
      <p><?php esc_html_e( 'Aucun membre. Ajoutez le premier !', 'bandstage' ); ?></p>
    </div>

  <?php else : ?>
    <ul class="bss-lineup-list" id="bs-lineup-sortable" data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
      <?php foreach ( $members as $member ) : ?>
        <li class="bss-lineup-item" data-id="<?php echo esc_attr( $member->id ); ?>">

          <span class="bss-lineup-item__handle" title="<?php esc_attr_e( 'Déplacer', 'bandstage' ); ?>">⠿</span>

          <div class="bss-lineup-item__photo">
            <?php if ( $member->thumbnail_url ) : ?>
              <img src="<?php echo esc_url( $member->thumbnail_url ); ?>"
                   alt="<?php echo esc_attr( $member->name ); ?>">
            <?php else : ?>
              <div class="bss-lineup-item__initials">
                <?php echo esc_html( mb_strtoupper( mb_substr( $member->name, 0, 2 ) ) ); ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="bss-lineup-item__info">
            <strong><?php echo esc_html( $member->name ); ?></strong>
            <?php if ( $member->role ) : ?>
              <span class="bss-lineup-item__role"><?php echo esc_html( $member->role ); ?></span>
            <?php endif; ?>
            <?php if ( $member->styles ) : ?>
              <em class="bss-lineup-item__styles"><?php echo esc_html( $member->styles ); ?></em>
            <?php endif; ?>
          </div>

          <div class="bss-lineup-item__actions">
            <a href="<?php echo esc_url( Shortcodes::groupe_url( 'edit', $member->id ) ); ?>"
               class="bss-btn bss-btn--sm bss-btn--ghost">
              <?php esc_html_e( 'Modifier', 'bandstage' ); ?>
            </a>
            <?php if ( $can_delete ) : ?>
              <button
                type="button"
                class="bss-btn bss-btn--sm bss-btn--danger js-lineup-delete"
                data-id="<?php echo esc_attr( $member->id ); ?>"
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
