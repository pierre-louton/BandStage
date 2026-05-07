<?php
/**
 * [bandstage_groupe] bs_view=edit — formulaire d'édition d'un membre.
 *
 * @var \BandStage\Domain\Lineup\LineupMember|null $member  null = création
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Frontend\Shortcodes;

$is_edit       = $member !== null;
$post_id       = $is_edit ? $member->id       : 0;
$name          = $is_edit ? $member->name     : '';
$role          = $is_edit ? $member->role     : '';
$styles        = $is_edit ? $member->styles      : '';
$social_link   = $is_edit ? $member->social_link  : '';
$thumbnail_url = $is_edit ? $member->thumbnail_url : '';

$page_title = $is_edit
  ? esc_html__( 'Modifier le membre', 'bandstage' )
  : esc_html__( 'Nouveau membre', 'bandstage' );
?>
<div class="bs-gr-wrap">

  <!-- Navbar avec bouton submit hors du <form> -->
  <nav class="bss-navbar">
    <a href="<?php echo esc_url( Shortcodes::groupe_url( 'list' ) ); ?>"
       class="bss-navbar__back">← <?php esc_html_e( 'Retour', 'bandstage' ); ?></a>
    <span class="bss-navbar__title"><?php echo $page_title; ?></span>
    <button
      type="submit"
      form="bss-lineup-form"
      class="bss-navbar__action bss-btn bss-btn--primary js-lineup-save">
      <?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
    </button>
  </nav>

  <form
    id="bss-lineup-form"
    class="bss-form"
    data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="action"  value="bs_lineup_save">
    <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">

    <!-- Photo -->
    <div class="bss-form__group bss-form__group--media">
      <div class="bss-media-preview" id="bs-lineup-preview">
        <?php if ( $thumbnail_url ) : ?>
          <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="">
        <?php else : ?>
          <span class="bss-media-preview__placeholder">📷</span>
        <?php endif; ?>
      </div>
      <input type="hidden" name="thumbnail_id" id="bs-lineup-thumb-id"
             value="<?php echo esc_attr( $is_edit ? get_post_thumbnail_id( $post_id ) : '' ); ?>">
      <div class="bss-media-actions">
        <button type="button" class="bss-btn bss-btn--ghost js-media-select"
                data-target="bs-lineup-thumb-id"
                data-preview="bs-lineup-preview">
          <?php esc_html_e( 'Choisir une photo', 'bandstage' ); ?>
        </button>
        <?php if ( $thumbnail_url ) : ?>
          <button type="button" class="bss-btn bss-btn--ghost bss-btn--danger js-media-remove"
                  data-target="bs-lineup-thumb-id"
                  data-preview="bs-lineup-preview">
            <?php esc_html_e( 'Retirer', 'bandstage' ); ?>
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Nom -->
    <div class="bss-form__group">
      <label for="bs-lineup-name" class="bss-form__label">
        <?php esc_html_e( 'Nom', 'bandstage' ); ?> *
      </label>
      <input
        type="text"
        id="bs-lineup-name"
        name="name"
        class="bss-form__input"
        value="<?php echo esc_attr( $name ); ?>"
        required
        placeholder="<?php esc_attr_e( 'Prénom Nom', 'bandstage' ); ?>">
    </div>

    <!-- Rôle / instrument -->
    <div class="bss-form__group">
      <label for="bs-lineup-role" class="bss-form__label">
        <?php esc_html_e( 'Rôle / Instrument', 'bandstage' ); ?>
      </label>
      <input
        type="text"
        id="bs-lineup-role"
        name="role"
        class="bss-form__input"
        value="<?php echo esc_attr( $role ); ?>"
        placeholder="<?php esc_attr_e( 'Guitare, Chant…', 'bandstage' ); ?>">
    </div>

    <!-- Styles préférés -->
    <div class="bss-form__group">
      <label for="bs-lineup-styles" class="bss-form__label">
        <?php esc_html_e( 'Styles préférés', 'bandstage' ); ?>
      </label>
      <input
        type="text"
        id="bs-lineup-styles"
        name="styles"
        class="bss-form__input"
        value="<?php echo esc_attr( $styles ); ?>"
        placeholder="<?php esc_attr_e( 'Blues, Rock, Jazz…', 'bandstage' ); ?>">
    </div>

    <!-- Lien réseaux sociaux -->
    <div class="bss-form__group">
      <label for="bs-lineup-social-link" class="bss-form__label">
        <?php esc_html_e( 'Lien réseaux sociaux', 'bandstage' ); ?>
      </label>
      <input
        type="url"
        id="bs-lineup-social-link"
        name="social_link"
        class="bss-form__input"
        value="<?php echo esc_url( $social_link ); ?>"
        placeholder="<?php esc_attr_e( 'https://instagram.com/…', 'bandstage' ); ?>">
    </div>

  </form>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
