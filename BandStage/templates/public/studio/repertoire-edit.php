<?php
/**
 * [bandstage_references] bs_view=edit — formulaire morceau (Auteur+).
 *
 * @var \BandStage\Domain\Repertoire\Morceau|null $morceau
 * @var \BandStage\Domain\Repertoire\Style[]      $all_styles
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$is_edit       = $morceau !== null;
$mid           = $is_edit ? $morceau->id            : 0;
$nom_artiste   = $is_edit ? $morceau->nom_artiste   : '';
$nom_morceau   = $is_edit ? $morceau->nom_morceau   : '';
$remarque      = $is_edit ? $morceau->remarque      : '';
$icone_artiste = $is_edit ? $morceau->icone_artiste : '';
$selected_ids  = $is_edit ? $morceau->style_ids     : [];

$page_title = $is_edit
  ? esc_html__( 'Modifier le morceau', 'bandstage' )
  : esc_html__( 'Nouveau morceau', 'bandstage' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <a href="<?php echo esc_url( Shortcodes::references_url( 'list' ) ); ?>"
       class="bss-navbar__back">← <?php esc_html_e( 'Retour', 'bandstage' ); ?></a>
    <span class="bss-navbar__title"><?php echo $page_title; ?></span>
    <button type="submit" form="bss-morceau-form"
            class="bss-navbar__action bss-btn bss-btn--primary js-morceau-save">
      <?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
    </button>
  </nav>

  <form id="bss-morceau-form" class="bss-form"
        data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="morceau_id" value="<?php echo esc_attr( $mid ); ?>">

    <!-- Artiste + icône -->
    <div class="bss-form__row">
      <div class="bss-form__group bss-form__group--sm">
        <label for="bs-m-icone" class="bss-form__label"><?php esc_html_e( 'Icône', 'bandstage' ); ?></label>
        <input type="text" id="bs-m-icone" name="icone_artiste" class="bss-form__input"
               value="<?php echo esc_attr( $icone_artiste ); ?>"
               placeholder="🎸" maxlength="10">
      </div>
      <div class="bss-form__group bss-form__group--grow">
        <label for="bs-m-artiste" class="bss-form__label"><?php esc_html_e( 'Artiste / Groupe', 'bandstage' ); ?> *</label>
        <input type="text" id="bs-m-artiste" name="nom_artiste" class="bss-form__input"
               value="<?php echo esc_attr( $nom_artiste ); ?>" required
               placeholder="<?php esc_attr_e( 'AC/DC, Nina Simone…', 'bandstage' ); ?>">
      </div>
    </div>

    <!-- Titre du morceau -->
    <div class="bss-form__group">
      <label for="bs-m-morceau" class="bss-form__label"><?php esc_html_e( 'Titre du morceau', 'bandstage' ); ?> *</label>
      <input type="text" id="bs-m-morceau" name="nom_morceau" class="bss-form__input"
             value="<?php echo esc_attr( $nom_morceau ); ?>" required
             placeholder="<?php esc_attr_e( 'Highway to Hell…', 'bandstage' ); ?>">
    </div>

    <!-- Remarque -->
    <div class="bss-form__group">
      <label for="bs-m-remarque" class="bss-form__label"><?php esc_html_e( 'Remarque', 'bandstage' ); ?></label>
      <textarea id="bs-m-remarque" name="remarque" class="bss-form__textarea" rows="3"
                placeholder="<?php esc_attr_e( 'Tonalité, arrangement particulier…', 'bandstage' ); ?>"><?php echo esc_textarea( $remarque ); ?></textarea>
    </div>

    <!-- Styles associés -->
    <?php if ( ! empty( $all_styles ) ) : ?>
      <div class="bss-form__group">
        <label for="bs-m-styles" class="bss-form__label"><?php esc_html_e( 'Styles musicaux', 'bandstage' ); ?></label>
        <select id="bs-m-styles" name="style_ids[]" multiple class="bss-form__select-multiple">
          <?php foreach ( $all_styles as $s ) : ?>
            <option value="<?php echo esc_attr( $s->id ); ?>"
              <?php echo in_array( $s->id, $selected_ids, true ) ? 'selected' : ''; ?>>
              <?php echo esc_html( $s->nom_style ); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="bss-form__hint"><?php esc_html_e( 'Ctrl+clic (ou Cmd+clic) pour sélectionner plusieurs styles.', 'bandstage' ); ?></p>
      </div>
    <?php endif; ?>

  </form>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
