<?php
/**
 * [bandstage_studio] — vue visiteur non connecté.
 * Archive publique des actualités publiées (section Humeurs).
 *
 * @var \BandStage\Domain\News\News[] $news_list
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$band_name = esc_html( get_option( 'bs_band_name', 'BandStage' ) );
?>
<div class="bs-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php echo $band_name; ?></h1>
    <p class="bs-header__section"><?php esc_html_e( 'Humeurs', 'bandstage' ); ?></p>
  </header>

  <?php if ( empty( $news_list ) ) : ?>
    <div class="bs-empty">
      <p><?php esc_html_e( 'Aucune humeur pour l\'instant.', 'bandstage' ); ?></p>
    </div>

  <?php else : ?>
    <div class="bs-humeurs">
      <?php foreach ( $news_list as $news ) : ?>
        <article class="bs-humeur-card">
          <div class="bs-humeur-card__meta">
            <span class="bs-humeur-card__author"><?php echo esc_html( $news->author_name ); ?></span>
            <time class="bs-humeur-card__date" datetime="<?php echo esc_attr( $news->date ); ?>">
              <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $news->date ) ) ); ?>
            </time>
          </div>
          <h2 class="bs-humeur-card__title"><?php echo esc_html( $news->title ); ?></h2>
          <?php if ( $news->content ) : ?>
            <div class="bs-humeur-card__content">
              <?php echo wp_kses_post( wpautop( $news->content ) ); ?>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
