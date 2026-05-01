<?php
/**
 * Studio — Tableau de bord actualités (humeurs).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$user      = wp_get_current_user();
$band_name = (string) get_option( 'bs_band_name', 'BandStage' );
$initials  = mb_strtoupper( mb_substr( $user->display_name, 0, 2 ) );

// Compteurs.
$counts   = wp_count_posts( 'bs_news' );
$pub      = (int) ( $counts->publish ?? 0 );
$draft    = (int) ( $counts->draft   ?? 0 );

// Toutes les actus visibles — publiées + brouillons, les plus récentes d'abord.
$news_list = get_posts( array(
	'post_type'      => 'bs_news',
	'posts_per_page' => 20,
	'post_status'    => array( 'publish', 'draft' ),
	'orderby'        => 'modified',
	'order'          => 'DESC',
) );

$new_url = BandStage_Studio::url( 'actu-edit' );
?>

<div class="bss-wrap">

	<!-- HEADER -->
	<header class="bss-header">
		<div class="bss-header__left">
			<a href="<?php echo esc_url( home_url() ); ?>" class="bss-back-btn" aria-label="Accueil">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
					<path d="M13 4L7 10l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
			</a>
			<div>
				<div class="bss-header__brand">📰 <?php esc_html_e( 'Humeurs', 'bandstage' ); ?></div>
				<div class="bss-header__sub"><?php echo esc_html( $band_name ); ?></div>
			</div>
		</div>
		<div class="bss-header__right">
			<a href="<?php echo esc_url( $new_url ); ?>" class="bss-btn bss-btn--gold bss-btn--sm">
				+ <?php esc_html_e( 'Nouveau billet', 'bandstage' ); ?>
			</a>
		</div>
	</header>

	<!-- STATS rapides -->
	<div class="bss-stats">
		<div class="bss-stat">
			<div class="bss-stat__value"><?php echo esc_html( (string) $pub ); ?></div>
			<div class="bss-stat__label"><?php esc_html_e( 'Publiés', 'bandstage' ); ?></div>
		</div>
		<div class="bss-stat">
			<div class="bss-stat__value"><?php echo esc_html( (string) $draft ); ?></div>
			<div class="bss-stat__label"><?php esc_html_e( 'Brouillons', 'bandstage' ); ?></div>
		</div>
		<div class="bss-stat">
			<div class="bss-stat__value"><?php echo esc_html( (string) ( $pub + $draft ) ); ?></div>
			<div class="bss-stat__label"><?php esc_html_e( 'Total', 'bandstage' ); ?></div>
		</div>
	</div>

	<!-- LISTE DES BILLETS -->
	<?php if ( empty( $news_list ) ) : ?>
	<div class="bss-empty" style="margin:24px 14px">
		<div class="bss-empty__icon">✍️</div>
		<p><?php esc_html_e( 'Aucun billet pour l\'instant. Partagez une humeur !', 'bandstage' ); ?></p>
		<a href="<?php echo esc_url( $new_url ); ?>" class="bss-btn bss-btn--gold">
			+ <?php esc_html_e( 'Écrire le premier billet', 'bandstage' ); ?>
		</a>
	</div>
	<?php else : ?>

	<div class="bss-news-list">
		<?php foreach ( $news_list as $news ) :
			$status   = get_post_status( $news );
			$edit_url = BandStage_Studio::url( 'actu-edit', $news->ID );
			$thumb    = get_the_post_thumbnail_url( $news, 'medium' );
			$date     = date_i18n( 'd M Y', strtotime( $news->post_modified ) );
			$excerpt  = $news->post_excerpt
				?: wp_trim_words( wp_strip_all_tags( $news->post_content ), 30, '…' );
			$has_content = ! empty( trim( strip_tags( $news->post_content ) ) );
		?>
		<article class="bss-news-card" id="bss-item-news-<?php echo esc_attr( (string) $news->ID ); ?>">

			<!-- Image optionnelle -->
			<?php if ( $thumb ) : ?>
			<div class="bss-news-card__img" style="background-image:url('<?php echo esc_url( $thumb ); ?>')"></div>
			<?php endif; ?>

			<!-- Corps -->
			<div class="bss-news-card__body">
				<div class="bss-news-card__meta">
					<span class="bss-badge bss-badge--<?php echo 'publish' === $status ? 'pub' : 'draft'; ?>">
						<?php echo 'publish' === $status ? esc_html__( 'Publié', 'bandstage' ) : esc_html__( 'Brouillon', 'bandstage' ); ?>
					</span>
					<span class="bss-news-card__date"><?php echo esc_html( $date ); ?></span>
					<span class="bss-news-card__author"><?php echo esc_html( get_the_author_meta( 'display_name', $news->post_author ) ); ?></span>
				</div>

				<h2 class="bss-news-card__title"><?php echo esc_html( get_the_title( $news ) ); ?></h2>

				<?php if ( $excerpt ) : ?>
				<p class="bss-news-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>

				<?php if ( $has_content ) : ?>
				<div class="bss-news-card__content" id="bss-content-<?php echo esc_attr( (string) $news->ID ); ?>">
					<?php echo wp_kses_post( wpautop( $news->post_content ) ); ?>
				</div>
				<button type="button" class="bss-read-more" data-target="bss-content-<?php echo esc_attr( (string) $news->ID ); ?>">
					<?php esc_html_e( 'Lire la suite…', 'bandstage' ); ?>
				</button>
				<?php endif; ?>
			</div>

			<!-- Actions -->
			<div class="bss-news-card__actions">
				<a href="<?php echo esc_url( $edit_url ); ?>" class="bss-action-btn" title="<?php esc_attr_e( 'Modifier', 'bandstage' ); ?>">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
						<path d="M11 2l3 3-8 8H3V10l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
					</svg>
				</a>
				<button type="button" class="bss-action-btn bss-action-btn--del"
				        data-id="<?php echo esc_attr( (string) $news->ID ); ?>"
				        data-type="news">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
						<path d="M2 4h10M5 4V2h4v2M5.5 6v5M8.5 6v5M3 4l1 8h6l1-8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>

		</article>
		<?php endforeach; ?>
	</div>

	<?php endif; ?>

	<div class="bss-toast" id="bss-toast" role="alert" aria-live="polite"></div>

</div>
