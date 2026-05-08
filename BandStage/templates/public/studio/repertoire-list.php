<?php
/**
 * [bandstage_references] bs_view=list — gestion du répertoire (Auteur+).
 *
 * @var \BandStage\Domain\Repertoire\Morceau[] $morceaux
 * @var \BandStage\Domain\Repertoire\Style[]   $all_styles
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <span class="bss-navbar__title"><?php esc_html_e( 'Répertoire', 'bandstage' ); ?></span>
    <a href="<?php echo esc_url( Shortcodes::references_url( 'edit' ) ); ?>"
       class="bss-navbar__action bss-btn bss-btn--primary">
      <?php esc_html_e( '+ Ajouter', 'bandstage' ); ?>
    </a>
  </nav>

  <?php if ( empty( $morceaux ) ) : ?>
    <div class="bss-empty">
      <p><?php esc_html_e( 'Aucun morceau. Ajoutez le premier !', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bss-morceau-list">
      <?php foreach ( $morceaux as $m ) : ?>
        <li class="bss-morceau-item" data-id="<?php echo esc_attr( $m->id ); ?>">
          <div class="bss-morceau-item__info">
            <?php if ( $m->icone_artiste ) : ?>
              <span class="bss-morceau-item__icon"><?php echo esc_html( $m->icone_artiste ); ?></span>
            <?php endif; ?>
            <strong><?php echo esc_html( $m->nom_artiste ); ?> — <?php echo esc_html( $m->nom_morceau ); ?></strong>
            <?php if ( $m->style_names ) : ?>
              <span class="bss-badge"><?php echo esc_html( $m->style_names ); ?></span>
            <?php endif; ?>
            <?php if ( $m->remarque ) : ?>
              <em class="bss-morceau-item__remarque"><?php echo esc_html( $m->remarque ); ?></em>
            <?php endif; ?>
          </div>
          <div class="bss-morceau-item__actions">
            <a href="<?php echo esc_url( Shortcodes::references_url( 'edit', $m->id ) ); ?>"
               class="bss-btn bss-btn--sm bss-btn--ghost">
              <?php esc_html_e( 'Modifier', 'bandstage' ); ?>
            </a>
            <button
              type="button"
              class="bss-btn bss-btn--sm bss-btn--danger js-morceau-delete"
              data-id="<?php echo esc_attr( $m->id ); ?>"
              data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
              <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
            </button>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <!-- ===== SECTION STYLES ===== -->
  <div class="bss-styles-section">
    <h3 class="bss-styles-section__title"><?php esc_html_e( 'Styles musicaux', 'bandstage' ); ?></h3>

    <table class="bss-styles-table">
      <thead>
        <tr>
          <th><?php esc_html_e( 'Style', 'bandstage' ); ?></th>
          <th><?php esc_html_e( 'Image', 'bandstage' ); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody id="bss-styles-list">
        <?php foreach ( $all_styles as $s ) : ?>
          <tr data-id="<?php echo esc_attr( $s->id ); ?>">
            <td><?php echo esc_html( $s->nom_style ); ?></td>
            <td>
              <?php if ( $s->image_url ) : ?>
                <img src="<?php echo esc_url( $s->image_url ); ?>"
                     alt="<?php echo esc_attr( $s->nom_style ); ?>"
                     class="bss-styles-table__thumb" loading="lazy">
              <?php else : ?>
                <span class="bss-styles-table__noimg">—</span>
              <?php endif; ?>
            </td>
            <td>
              <button type="button" class="bss-btn bss-btn--sm bss-btn--danger js-style-delete"
                      data-id="<?php echo esc_attr( $s->id ); ?>"
                      data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
                <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h4 class="bss-styles-section__subtitle"><?php esc_html_e( 'Ajouter un style', 'bandstage' ); ?></h4>
    <form id="bss-style-form">
      <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--grow">
          <label for="bss-style-name" class="bss-form__label"><?php esc_html_e( 'Nom du style', 'bandstage' ); ?></label>
          <input type="text" id="bss-style-name" name="nom_style" class="bss-form__input" required
                 placeholder="<?php esc_attr_e( 'Rock, Jazz, Blues…', 'bandstage' ); ?>">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bss-style-img" class="bss-form__label"><?php esc_html_e( 'URL image', 'bandstage' ); ?></label>
          <input type="url" id="bss-style-img" name="image_url" class="bss-form__input"
                 placeholder="https://…">
        </div>
      </div>
      <p><button type="submit" class="bss-btn bss-btn--primary"><?php esc_html_e( 'Ajouter', 'bandstage' ); ?></button></p>
    </form>
  </div>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
