<?php
/**
 * [bandstage_studio] bs_view=edit — éditeur d'actualité.
 *
 * @var \BandStage\Domain\News\News|null $current_news
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Public\Shortcodes;

$is_edit = $current_news !== null;
$post_id = $is_edit ? $current_news->id      : 0;
$title   = $is_edit ? $current_news->title   : '';
$content = $is_edit ? $current_news->content : '';
$status  = $is_edit ? $current_news->status  : 'draft';

$page_title = $is_edit
  ? esc_html__( 'Modifier l\'humeur', 'bandstage' )
  : esc_html__( 'Nouvelle humeur', 'bandstage' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <a href="<?php echo esc_url( Shortcodes::studio_url( 'dashboard' ) ); ?>"
       class="bss-navbar__back">← <?php esc_html_e( 'Retour', 'bandstage' ); ?></a>
    <span class="bss-navbar__title"><?php echo $page_title; ?></span>
    <button type="submit" form="bss-news-form"
            class="bss-navbar__action bss-btn bss-btn--primary js-news-save"
            data-status="publish">
      <?php esc_html_e( 'Publier', 'bandstage' ); ?>
    </button>
  </nav>

  <form id="bss-news-form" class="bss-form"
        data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="action"  value="bs_news_save">
    <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
    <input type="hidden" name="status"  value="publish" id="bss-news-status">

    <div class="bss-form__group">
      <label for="bss-news-title" class="bss-form__label">
        <?php esc_html_e( 'Titre', 'bandstage' ); ?> *
      </label>
      <input type="text" id="bss-news-title" name="title"
             class="bss-form__input bss-form__input--lg"
             value="<?php echo esc_attr( $title ); ?>"
             placeholder="<?php esc_attr_e( 'Titre de l\'humeur…', 'bandstage' ); ?>"
             required>
    </div>

    <div class="bss-form__group">
      <label for="bss-news-content" class="bss-form__label">
        <?php esc_html_e( 'Contenu', 'bandstage' ); ?>
      </label>
      <div id="bss-news-editor-wrap">
        <textarea id="bss-news-content" name="content"
                  class="bss-form__textarea"
                  rows="10"><?php echo esc_textarea( $content ); ?></textarea>
      </div>
    </div>

  </form>

  <?php if ( $is_edit && current_user_can( 'edit_posts' ) ) : ?>
    <div class="bss-danger-zone">
      <h4><?php esc_html_e( 'Zone de danger', 'bandstage' ); ?></h4>
      <button type="button"
              class="bss-btn bss-btn--danger js-news-delete"
              data-id="<?php echo esc_attr( $post_id ); ?>"
              data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>"
              data-redirect="<?php echo esc_url( Shortcodes::studio_url() ); ?>">
        <?php esc_html_e( 'Supprimer cette humeur', 'bandstage' ); ?>
      </button>
    </div>
  <?php endif; ?>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
