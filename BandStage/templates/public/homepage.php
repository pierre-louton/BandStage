<?php
/**
 * [bandstage_homepage] — page d'accueil, tous visiteurs.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Domain\News\NewsService;

$band_name    = get_option( 'bs_band_name',    'Mon Groupe' );
$band_tagline = get_option( 'bs_band_tagline', '' );
$band_city    = get_option( 'bs_band_city',    '' );
$ticker_on    = (bool) get_option( 'bs_ticker_enabled', '1' );
$ticker_src   = get_option( 'bs_ticker_source', 'bs_news' );
$ticker_speed = (int) get_option( 'bs_ticker_speed', 24 );

// Ticker items
$ticker_items = [];
if ( $ticker_on ) {
	if ( 'manual' === $ticker_src ) {
		$raw          = get_option( 'bs_ticker_items', '' );
		$ticker_items = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
	} elseif ( 'bs_news' === $ticker_src ) {
		$ticker_items = ( new NewsService() )->get_ticker_titles( 10 );
	} else {
		$posts = get_posts( [ 'post_status' => 'publish', 'posts_per_page' => 10, 'fields' => 'ids', 'no_found_rows' => true ] );
		$ticker_items = array_map( 'get_the_title', $posts );
	}
}

// Boîtes
$boxes = [];
for ( $i = 1; $i <= 6; $i++ ) {
	$title = get_option( "bs_box_{$i}_title", '' );
	$link  = get_option( "bs_box_{$i}_link",  '' );
	$icon  = get_option( "bs_box_{$i}_icon",  '' );
	if ( $title ) {
		$boxes[] = compact( 'title', 'link', 'icon' );
	}
}
?>
<div class="bs-wrap">

  <!-- En-tête -->
  <header class="bs-header">
    <h1 class="bs-header__brand"><?php echo esc_html( $band_name ); ?></h1>
    <?php if ( $band_tagline ) : ?>
      <p class="bs-header__tagline"><?php echo esc_html( $band_tagline ); ?></p>
    <?php endif; ?>
    <?php if ( $band_city ) : ?>
      <p class="bs-header__city">📍 <?php echo esc_html( $band_city ); ?></p>
    <?php endif; ?>
  </header>

  <!-- Ticker -->
  <?php if ( $ticker_on && ! empty( $ticker_items ) ) : ?>
    <div class="bs-ticker" aria-live="polite"
         style="--bs-ticker-speed:<?php echo esc_attr( $ticker_speed ); ?>s">
      <div class="bs-ticker__track">
        <?php foreach ( $ticker_items as $item ) : ?>
          <span class="bs-ticker__item"><?php echo esc_html( $item ); ?></span>
        <?php endforeach; ?>
        <?php /* Duplication pour boucle CSS */ foreach ( $ticker_items as $item ) : ?>
          <span class="bs-ticker__item" aria-hidden="true"><?php echo esc_html( $item ); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Grille 2×3 -->
  <?php if ( ! empty( $boxes ) ) : ?>
    <div class="bs-grid">
      <?php foreach ( $boxes as $box ) : ?>
        <a href="<?php echo $box['link'] ? esc_url( $box['link'] ) : '#'; ?>"
           class="bs-box"
           <?php echo $box['link'] ? '' : 'tabindex="-1"'; ?>>
          <?php if ( $box['icon'] ) : ?>
            <span class="bs-box__icon bs-icon bs-icon--<?php echo esc_attr( $box['icon'] ); ?>" aria-hidden="true"></span>
          <?php endif; ?>
          <span class="bs-box__label"><?php echo esc_html( $box['title'] ); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
