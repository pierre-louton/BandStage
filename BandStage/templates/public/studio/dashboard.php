<?php
/**
 * [bandstage_studio] dashboard — liste des actus de l'auteur connecté.
 *
 * @var \BandStage\Domain\News\News[] $news_list
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Public\Shortcodes;

$current_uid = get_current_user_id();
$is_admin    = current_user_can( 'manage_options' );
?>
<div class="bs-wrap">

  <nav class="bss-navbar">
    <span class="bss-navbar__title"><?php esc_html_e( 'Studio', 'bandstage' ); ?></span>
    <a href="<?php echo esc_url( Shortcodes::studio_url( 'edit' ) ); ?>"
       class="bss-navbar__action bss-btn bss-btn--primary">
      <?php esc_html_e( '+ Humeur', 'bandstage' ); ?>
    </a>
  </nav>

  <?php if ( empty( $news_list ) ) : ?>
    <div class="bss-empty">
      <p><?php esc_html_e( 'Aucune actualité. Publiez la première !', 'bandstage' ); ?></p>
    </div>
  <?php else : ?>
    <ul class="bss-news-list">
      <?php foreach ( $news_list as $news ) : ?>
        <?php
        $can_edit = $is_admin || (int) $news->author_id === $current_uid;
        ?>
        <li class="bss-news-item">
          <div class="bss-news-item__meta">
            <span class="bss-badge bss-badge--<?php echo esc_attr( $news->status ); ?>">
              <?php echo 'publish' === $news->status ? esc_html__( 'Publié', 'bandstage' ) : esc_html__( 'Brouillon', 'bandstage' ); ?>
            </span>
            <time><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $news->date ) ) ); ?></time>
            <?php if ( $is_admin ) : ?>
              <span class="bss-news-item__author"><?php echo esc_html( $news->author_name ); ?></span>
            <?php endif; ?>
          </div>
          <h3 class="bss-news-item__title"><?php echo esc_html( $news->title ); ?></h3>
          <?php if ( $can_edit ) : ?>
            <div class="bss-news-item__actions">
              <a href="<?php echo esc_url( Shortcodes::studio_url( 'edit', $news->id ) ); ?>"
                 class="bss-btn bss-btn--sm bss-btn--ghost">
                <?php esc_html_e( 'Modifier', 'bandstage' ); ?>
              </a>
              <button type="button"
                      class="bss-btn bss-btn--sm bss-btn--danger js-news-delete"
                      data-id="<?php echo esc_attr( $news->id ); ?>"
                      data-nonce="<?php echo esc_attr( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>">
                <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
              </button>
            </div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
