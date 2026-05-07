<?php
/**
 * [bandstage_partenaires] bs_view=edit — formulaire partenaire.
 *
 * @var \BandStage\Domain\Partenaires\Partenaire|null $partenaire
 * @var \WP_Term[]                                    $types
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Public\Shortcodes;

$is_edit   = $partenaire !== null;
$post_id   = $is_edit ? $partenaire->id          : 0;
$name      = $is_edit ? $partenaire->name        : '';
$desc      = $is_edit ? $partenaire->description : '';
$type_slug = $is_edit ? $partenaire->type_slug   : '';
$website   = $is_edit ? $partenaire->website     : '';
$phone     = $is_edit ? $partenaire->phone       : '';
$address   = $is_edit ? $partenaire->address     : '';
$email     = $is_edit ? $partenaire->email       : '';
$thumb_url = $is_edit ? $partenaire->thumbnail   : '';
$thumb_id  = $is_edit ? get_post_thumbnail_id( $post_id ) : 0;

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
            class="bss-navbar__action bss-btn bss-btn--primary">
      <?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
    </button>
  </nav>

  <form id="bss-partenaire-form" class="bss-form"
        data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="action"  value="bs_partenaire_save">
    <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">

    <!-- Photo -->
    <div class="bss-form__group bss-form__group--media">
      <div class="bss-media-preview" id="bs-partenaire-preview">
        <?php if ( $thumb_url ) : ?>
          <img src="<?php echo esc_url( $thumb_url ); ?>" alt="">
        <?php else : ?>
          <span class="bss-media-preview__placeholder">🖼️</span>
        <?php endif; ?>
      </div>
      <input type="hidden" name="thumbnail_id" id="bs-partenaire-thumb-id"
             value="<?php echo esc_attr( $thumb_id ); ?>">
      <button type="button" class="bss-btn bss-btn--ghost js-media-select"
              data-target="bs-partenaire-thumb-id"
              data-preview="bs-partenaire-preview">
        <?php esc_html_e( 'Choisir une image', 'bandstage' ); ?>
      </button>
    </div>

    <!-- Nom -->
    <div class="bss-form__group">
      <label for="bs-p-name" class="bss-form__label"><?php esc_html_e( 'Nom', 'bandstage' ); ?> *</label>
      <input type="text" id="bs-p-name" name="name" class="bss-form__input"
             value="<?php echo esc_attr( $name ); ?>" required>
    </div>

    <!-- Type -->
    <div class="bss-form__group">
      <label for="bs-p-type" class="bss-form__label"><?php esc_html_e( 'Type', 'bandstage' ); ?></label>
      <select id="bs-p-type" name="type_slug" class="bss-form__select">
        <option value=""><?php esc_html_e( '— Choisir —', 'bandstage' ); ?></option>
        <?php foreach ( $types as $term ) : ?>
          <?php
          $icon = get_term_meta( $term->term_id, 'bs_term_icon', true );
          ?>
          <option value="<?php echo esc_attr( $term->slug ); ?>"
                  <?php selected( $term->slug, $type_slug ); ?>>
            <?php echo esc_html( $icon . ' ' . $term->name ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Description -->
    <div class="bss-form__group">
      <label for="bs-p-desc" class="bss-form__label"><?php esc_html_e( 'Description', 'bandstage' ); ?></label>
      <textarea id="bs-p-desc" name="description" class="bss-form__textarea" rows="4"><?php echo esc_textarea( wp_strip_all_tags( $desc ) ); ?></textarea>
    </div>

    <!-- Contacts -->
    <div class="bss-form__group">
      <label for="bs-p-address" class="bss-form__label"><?php esc_html_e( 'Adresse', 'bandstage' ); ?></label>
      <input type="text" id="bs-p-address" name="address" class="bss-form__input"
             value="<?php echo esc_attr( $address ); ?>">
    </div>
    <div class="bss-form__group">
      <label for="bs-p-phone" class="bss-form__label"><?php esc_html_e( 'Téléphone', 'bandstage' ); ?></label>
      <input type="tel" id="bs-p-phone" name="phone" class="bss-form__input"
             value="<?php echo esc_attr( $phone ); ?>">
    </div>
    <div class="bss-form__group">
      <label for="bs-p-email" class="bss-form__label"><?php esc_html_e( 'Email', 'bandstage' ); ?></label>
      <input type="email" id="bs-p-email" name="email" class="bss-form__input"
             value="<?php echo esc_attr( $email ); ?>">
    </div>
    <div class="bss-form__group">
      <label for="bs-p-website" class="bss-form__label"><?php esc_html_e( 'Site web', 'bandstage' ); ?></label>
      <input type="url" id="bs-p-website" name="website" class="bss-form__input"
             value="<?php echo esc_attr( $website ); ?>"
             placeholder="https://…">
    </div>

  </form>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
