<?php
/**
 * [bandstage_concerts] bs_view=edit — formulaire concert.
 *
 * @var \BandStage\Domain\Concerts\Concert|null                $concert
 * @var \BandStage\Domain\Partenaires\Partenaire[]             $all_partenaires
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$is_edit    = $concert !== null;
$cid        = $is_edit ? $concert->id          : 0;
$titre      = $is_edit ? $concert->titre        : '';
$date_debut = $is_edit ? $concert->date_debut   : '';
$date_fin   = $is_edit ? $concert->date_fin     : '';
$horaires   = $is_edit ? $concert->horaires     : '';
$nom_lieu   = $is_edit ? $concert->nom_lieu     : '';
$numero     = $is_edit ? $concert->numero       : '';
$nom_voie   = $is_edit ? $concert->nom_voie     : '';
$code_postal= $is_edit ? $concert->code_postal  : '';
$ville      = $is_edit ? $concert->ville        : '';
$selected_ids = $is_edit ? $concert->partenaire_ids : [];

$page_title = $is_edit
  ? esc_html__( 'Modifier le concert', 'bandstage' )
  : esc_html__( 'Nouveau concert', 'bandstage' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <a href="<?php echo esc_url( Shortcodes::concerts_url( 'list' ) ); ?>"
       class="bss-navbar__back">← <?php esc_html_e( 'Retour', 'bandstage' ); ?></a>
    <span class="bss-navbar__title"><?php echo $page_title; ?></span>
    <button type="submit" form="bss-concert-form"
            class="bss-navbar__action bss-btn bss-btn--primary js-concert-save">
      <?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
    </button>
  </nav>

  <form id="bss-concert-form" class="bss-form"
        data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="concert_id" value="<?php echo esc_attr( $cid ); ?>">

    <!-- Titre -->
    <div class="bss-form__group">
      <label for="bs-c-titre" class="bss-form__label"><?php esc_html_e( 'Titre', 'bandstage' ); ?> *</label>
      <input type="text" id="bs-c-titre" name="titre" class="bss-form__input"
             value="<?php echo esc_attr( $titre ); ?>" required
             placeholder="<?php esc_attr_e( 'Nom du concert ou de l\'événement', 'bandstage' ); ?>">
    </div>

    <!-- Dates -->
    <div class="bss-form__row">
      <div class="bss-form__group bss-form__group--grow">
        <label for="bs-c-date-debut" class="bss-form__label"><?php esc_html_e( 'Date de début', 'bandstage' ); ?> *</label>
        <input type="date" id="bs-c-date-debut" name="date_debut" class="bss-form__input"
               value="<?php echo esc_attr( $date_debut ); ?>" required>
      </div>
      <div class="bss-form__group bss-form__group--grow">
        <label for="bs-c-date-fin" class="bss-form__label"><?php esc_html_e( 'Date de fin', 'bandstage' ); ?></label>
        <input type="date" id="bs-c-date-fin" name="date_fin" class="bss-form__input"
               value="<?php echo esc_attr( $date_fin ); ?>">
      </div>
    </div>

    <!-- Horaires -->
    <div class="bss-form__group">
      <label for="bs-c-horaires" class="bss-form__label"><?php esc_html_e( 'Horaires', 'bandstage' ); ?></label>
      <input type="text" id="bs-c-horaires" name="horaires" class="bss-form__input"
             value="<?php echo esc_attr( $horaires ); ?>"
             placeholder="<?php esc_attr_e( '20h30 – 23h00', 'bandstage' ); ?>">
    </div>

    <!-- Lieu -->
    <fieldset class="bss-form__fieldset">
      <legend class="bss-form__legend"><?php esc_html_e( 'Lieu', 'bandstage' ); ?></legend>
      <div class="bss-form__group">
        <label for="bs-c-lieu" class="bss-form__label"><?php esc_html_e( 'Nom du lieu', 'bandstage' ); ?></label>
        <input type="text" id="bs-c-lieu" name="nom_lieu" class="bss-form__input"
               value="<?php echo esc_attr( $nom_lieu ); ?>"
               placeholder="<?php esc_attr_e( 'Salle des fêtes, Bar Le Rock…', 'bandstage' ); ?>">
      </div>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--sm">
          <label for="bs-c-numero" class="bss-form__label"><?php esc_html_e( 'N°', 'bandstage' ); ?></label>
          <input type="text" id="bs-c-numero" name="numero" class="bss-form__input"
                 value="<?php echo esc_attr( $numero ); ?>" placeholder="12">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bs-c-voie" class="bss-form__label"><?php esc_html_e( 'Voie', 'bandstage' ); ?></label>
          <input type="text" id="bs-c-voie" name="nom_voie" class="bss-form__input"
                 value="<?php echo esc_attr( $nom_voie ); ?>"
                 placeholder="<?php esc_attr_e( 'Rue de la Paix', 'bandstage' ); ?>">
        </div>
      </div>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--sm">
          <label for="bs-c-cp" class="bss-form__label"><?php esc_html_e( 'Code postal', 'bandstage' ); ?></label>
          <input type="text" id="bs-c-cp" name="code_postal" class="bss-form__input"
                 value="<?php echo esc_attr( $code_postal ); ?>" placeholder="75001">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bs-c-ville" class="bss-form__label"><?php esc_html_e( 'Ville', 'bandstage' ); ?></label>
          <input type="text" id="bs-c-ville" name="ville" class="bss-form__input"
                 value="<?php echo esc_attr( $ville ); ?>"
                 placeholder="<?php esc_attr_e( 'Paris', 'bandstage' ); ?>">
        </div>
      </div>
    </fieldset>

    <!-- Partenaires associés -->
    <?php if ( ! empty( $all_partenaires ) ) : ?>
      <div class="bss-form__group">
        <label for="bs-c-partenaires" class="bss-form__label">
          <?php esc_html_e( 'Partenaires associés', 'bandstage' ); ?>
        </label>
        <select id="bs-c-partenaires" name="partenaire_ids[]" multiple class="bss-form__select-multiple">
          <?php foreach ( $all_partenaires as $p ) : ?>
            <option value="<?php echo esc_attr( $p->id ); ?>"
              <?php echo in_array( $p->id, $selected_ids, true ) ? 'selected' : ''; ?>>
              <?php echo esc_html( $p->name ); ?>
              <?php if ( $p->type_name ) echo esc_html( ' — ' . $p->type_name ); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="bss-form__hint"><?php esc_html_e( 'Ctrl+clic (ou Cmd+clic) pour sélectionner plusieurs partenaires.', 'bandstage' ); ?></p>
      </div>
    <?php endif; ?>

  </form>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
