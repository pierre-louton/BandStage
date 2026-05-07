<?php
/**
 * [bandstage_partenaires] bs_view=edit — formulaire partenaire.
 *
 * @var \BandStage\Domain\Partenaires\Partenaire|null $partenaire
 * @var \BandStage\Domain\Partenaires\PartenaireType[] $types
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$is_edit    = $partenaire !== null;
$pid        = $is_edit ? $partenaire->id          : 0;
$name       = $is_edit ? $partenaire->name        : '';
$desc       = $is_edit ? $partenaire->description : '';
$type_id    = $is_edit ? $partenaire->type_id     : null;
$website    = $is_edit ? $partenaire->website     : '';
$email      = $is_edit ? $partenaire->email       : '';
$phone      = $is_edit ? $partenaire->phone       : '';
$numero     = $is_edit ? $partenaire->numero      : '';
$nom_voie   = $is_edit ? $partenaire->nom_voie    : '';
$code_postal= $is_edit ? $partenaire->code_postal : '';
$ville      = $is_edit ? $partenaire->ville       : '';
$logo_url   = $is_edit ? $partenaire->logo_url    : '';
$logo_path  = $is_edit ? $partenaire->logo_path   : '';

$page_title = $is_edit
  ? esc_html__( 'Modifier le partenaire', 'bandstage' )
  : esc_html__( 'Nouveau partenaire', 'bandstage' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <a href="<?php echo esc_url( Shortcodes::partenaires_url( 'list' ) ); ?>"
       class="bss-navbar__back">← <?php esc_html_e( 'Retour', 'bandstage' ); ?></a>
    <span class="bss-navbar__title"><?php echo $page_title; ?></span>
    <button type="submit" form="bss-partenaire-form"
            class="bss-navbar__action bss-btn bss-btn--primary js-partenaire-save">
      <?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
    </button>
  </nav>

  <form id="bss-partenaire-form" class="bss-form"
        enctype="multipart/form-data"
        data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="partenaire_id" value="<?php echo esc_attr( $pid ); ?>">

    <!-- Logo -->
    <div class="bss-form__group bss-form__group--logo">
      <label class="bss-form__label"><?php esc_html_e( 'Logo', 'bandstage' ); ?></label>
      <div class="bss-logo-preview" id="bs-logo-preview">
        <?php if ( $logo_url ) : ?>
          <img src="<?php echo esc_url( $logo_url ); ?>" alt="">
        <?php else : ?>
          <span class="bss-logo-preview__placeholder">🖼️</span>
        <?php endif; ?>
      </div>
      <input type="hidden" name="logo_action" id="bs-logo-action" value="">
      <input type="file" name="logo" id="bs-logo-file" accept="image/*" class="bss-form__file">
      <?php if ( $logo_url ) : ?>
        <button type="button" class="bss-btn bss-btn--ghost bss-btn--danger js-logo-remove">
          <?php esc_html_e( 'Retirer le logo', 'bandstage' ); ?>
        </button>
      <?php endif; ?>
    </div>

    <!-- Nom -->
    <div class="bss-form__group">
      <label for="bs-p-name" class="bss-form__label"><?php esc_html_e( 'Nom', 'bandstage' ); ?> *</label>
      <input type="text" id="bs-p-name" name="name" class="bss-form__input"
             value="<?php echo esc_attr( $name ); ?>" required
             placeholder="<?php esc_attr_e( 'Nom du partenaire', 'bandstage' ); ?>">
    </div>

    <!-- Type -->
    <div class="bss-form__group">
      <label for="bs-p-type" class="bss-form__label"><?php esc_html_e( 'Type', 'bandstage' ); ?></label>
      <select id="bs-p-type" name="type_id" class="bss-form__input">
        <option value=""><?php esc_html_e( '— Aucun type —', 'bandstage' ); ?></option>
        <?php foreach ( $types as $t ) : ?>
          <option value="<?php echo esc_attr( $t->id ); ?>"
            <?php selected( $type_id, $t->id ); ?>>
            <?php echo esc_html( ( $t->icon ? $t->icon . ' ' : '' ) . $t->name ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Description -->
    <div class="bss-form__group">
      <label for="bs-p-desc" class="bss-form__label"><?php esc_html_e( 'Description', 'bandstage' ); ?></label>
      <textarea id="bs-p-desc" name="description" class="bss-form__input bss-form__textarea"
                rows="4"><?php echo esc_textarea( $desc ); ?></textarea>
    </div>

    <!-- Adresse -->
    <fieldset class="bss-form__fieldset">
      <legend class="bss-form__legend"><?php esc_html_e( 'Adresse', 'bandstage' ); ?></legend>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--sm">
          <label for="bs-p-numero" class="bss-form__label"><?php esc_html_e( 'N°', 'bandstage' ); ?></label>
          <input type="text" id="bs-p-numero" name="numero" class="bss-form__input"
                 value="<?php echo esc_attr( $numero ); ?>" placeholder="12">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bs-p-voie" class="bss-form__label"><?php esc_html_e( 'Voie', 'bandstage' ); ?></label>
          <input type="text" id="bs-p-voie" name="nom_voie" class="bss-form__input"
                 value="<?php echo esc_attr( $nom_voie ); ?>"
                 placeholder="<?php esc_attr_e( 'Rue de la Paix', 'bandstage' ); ?>">
        </div>
      </div>
      <div class="bss-form__row">
        <div class="bss-form__group bss-form__group--sm">
          <label for="bs-p-cp" class="bss-form__label"><?php esc_html_e( 'Code postal', 'bandstage' ); ?></label>
          <input type="text" id="bs-p-cp" name="code_postal" class="bss-form__input"
                 value="<?php echo esc_attr( $code_postal ); ?>" placeholder="75001">
        </div>
        <div class="bss-form__group bss-form__group--grow">
          <label for="bs-p-ville" class="bss-form__label"><?php esc_html_e( 'Ville', 'bandstage' ); ?></label>
          <input type="text" id="bs-p-ville" name="ville" class="bss-form__input"
                 value="<?php echo esc_attr( $ville ); ?>"
                 placeholder="<?php esc_attr_e( 'Paris', 'bandstage' ); ?>">
        </div>
      </div>
    </fieldset>

    <!-- Contact -->
    <fieldset class="bss-form__fieldset">
      <legend class="bss-form__legend"><?php esc_html_e( 'Contact', 'bandstage' ); ?></legend>
      <div class="bss-form__group">
        <label for="bs-p-phone" class="bss-form__label"><?php esc_html_e( 'Téléphone', 'bandstage' ); ?></label>
        <input type="tel" id="bs-p-phone" name="phone" class="bss-form__input"
               value="<?php echo esc_attr( $phone ); ?>" placeholder="01 23 45 67 89">
      </div>
      <div class="bss-form__group">
        <label for="bs-p-email" class="bss-form__label"><?php esc_html_e( 'Email', 'bandstage' ); ?></label>
        <input type="email" id="bs-p-email" name="email" class="bss-form__input"
               value="<?php echo esc_attr( $email ); ?>"
               placeholder="contact@partenaire.fr">
      </div>
      <div class="bss-form__group">
        <label for="bs-p-web" class="bss-form__label"><?php esc_html_e( 'Site web', 'bandstage' ); ?></label>
        <input type="url" id="bs-p-web" name="website" class="bss-form__input"
               value="<?php echo esc_attr( $website ); ?>"
               placeholder="https://partenaire.fr">
      </div>
    </fieldset>

  </form>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
