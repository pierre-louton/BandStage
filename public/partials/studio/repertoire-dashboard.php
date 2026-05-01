<?php
/**
 * Studio Répertoire — liste des titres.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$band_name  = (string) get_option( 'bs_band_name', 'BandStage' );
$new_url    = BandStage_Studio::repertoire_url( 'titre-edit' );
$home_url   = home_url();

// Tous les titres, triés alphabétiquement.
$titres = get_posts( array(
	'post_type'      => 'bs_titre',
	'post_status'    => 'publish',
	'posts_per_page' => 200,
	'orderby'        => 'title',
	'order'          => 'ASC',
) );

$originals = array_filter( $titres, fn($t) => 'original' === get_post_meta($t->ID,'bs_titre_type',true) );
$reprises  = array_filter( $titres, fn($t) => 'original' !== get_post_meta($t->ID,'bs_titre_type',true) );
?>

<div class="bss-wrap">

	<header class="bss-header">
		<div class="bss-header__left">
			<a href="<?php echo esc_url( $home_url ); ?>" class="bss-back-btn">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
					<path d="M13 4L7 10l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
			</a>
			<div>
				<div class="bss-header__brand">🎸 <?php esc_html_e( 'Répertoire', 'bandstage' ); ?></div>
				<div class="bss-header__sub"><?php echo esc_html( $band_name ); ?></div>
			</div>
		</div>
		<div class="bss-header__right">
			<a href="<?php echo esc_url( $new_url ); ?>" class="bss-btn bss-btn--gold bss-btn--sm">
				+ <?php esc_html_e( 'Ajouter', 'bandstage' ); ?>
			</a>
		</div>
	</header>

	<!-- Stats -->
	<div class="bss-stats">
		<div class="bss-stat">
			<div class="bss-stat__value"><?php echo esc_html( (string) count( $originals ) ); ?></div>
			<div class="bss-stat__label"><?php esc_html_e( 'Originaux', 'bandstage' ); ?></div>
		</div>
		<div class="bss-stat">
			<div class="bss-stat__value"><?php echo esc_html( (string) count( $reprises ) ); ?></div>
			<div class="bss-stat__label"><?php esc_html_e( 'Reprises', 'bandstage' ); ?></div>
		</div>
		<div class="bss-stat">
			<div class="bss-stat__value"><?php echo esc_html( (string) count( $titres ) ); ?></div>
			<div class="bss-stat__label"><?php esc_html_e( 'Total', 'bandstage' ); ?></div>
		</div>
	</div>

	<?php if ( empty( $titres ) ) : ?>
	<div class="bss-empty" style="margin:24px 14px">
		<div class="bss-empty__icon">🎵</div>
		<p><?php esc_html_e( 'Aucun titre dans le répertoire.', 'bandstage' ); ?></p>
		<a href="<?php echo esc_url( $new_url ); ?>" class="bss-btn bss-btn--gold">
			+ <?php esc_html_e( 'Ajouter le premier titre', 'bandstage' ); ?>
		</a>
	</div>
	<?php else : ?>

	<!-- Originals -->
	<?php if ( ! empty( $originals ) ) : ?>
	<div class="bss-section" style="margin-top:16px">
		<div class="bss-section-head">
			<h2 class="bss-section-title">✨ <?php esc_html_e( 'Compositions originales', 'bandstage' ); ?></h2>
		</div>
		<div class="bss-list">
			<?php foreach ( $originals as $t ) :
				$edit_url = BandStage_Studio::repertoire_url( 'titre-edit', $t->ID );
				$notes    = wp_trim_words( wp_strip_all_tags( $t->post_content ), 10, '…' );
			?>
			<div class="bss-item" id="bss-item-titre-<?php echo esc_attr( (string) $t->ID ); ?>">
				<div class="bss-item__partner-icon" style="background:rgba(92,200,200,.1);border-color:rgba(92,200,200,.2)">✨</div>
				<div class="bss-item__body">
					<div class="bss-item__title"><?php echo esc_html( get_the_title( $t ) ); ?></div>
					<?php if ( $notes ) : ?>
					<div class="bss-item__meta"><span class="bss-item__date"><?php echo esc_html( $notes ); ?></span></div>
					<?php endif; ?>
				</div>
				<div class="bss-item__actions">
					<a href="<?php echo esc_url( $edit_url ); ?>" class="bss-action-btn">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M11 2l3 3-8 8H3V10l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
					</a>
					<button type="button" class="bss-action-btn bss-action-btn--del"
					        data-id="<?php echo esc_attr( (string) $t->ID ); ?>" data-type="titre">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 4h10M5 4V2h4v2M5.5 6v5M8.5 6v5M3 4l1 8h6l1-8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- Reprises -->
	<?php if ( ! empty( $reprises ) ) : ?>
	<div class="bss-section" style="margin-top:12px">
		<div class="bss-section-head">
			<h2 class="bss-section-title">🎤 <?php esc_html_e( 'Reprises', 'bandstage' ); ?></h2>
		</div>
		<div class="bss-list">
			<?php foreach ( $reprises as $t ) :
				$edit_url = BandStage_Studio::repertoire_url( 'titre-edit', $t->ID );
				$artiste  = (string) get_post_meta( $t->ID, 'bs_titre_artiste', true );
				$annee    = (string) get_post_meta( $t->ID, 'bs_titre_annee',   true );
			?>
			<div class="bss-item" id="bss-item-titre-<?php echo esc_attr( (string) $t->ID ); ?>">
				<div class="bss-item__partner-icon" style="background:rgba(212,168,32,.08);border-color:rgba(212,168,32,.15)">🎵</div>
				<div class="bss-item__body">
					<div class="bss-item__title"><?php echo esc_html( get_the_title( $t ) ); ?></div>
					<div class="bss-item__meta">
						<?php if ( $artiste ) : ?>
						<span class="bss-badge bss-badge--type"><?php echo esc_html( $artiste ); ?></span>
						<?php endif; ?>
						<?php if ( $annee ) : ?>
						<span class="bss-item__date"><?php echo esc_html( $annee ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="bss-item__actions">
					<a href="<?php echo esc_url( $edit_url ); ?>" class="bss-action-btn">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M11 2l3 3-8 8H3V10l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
					</a>
					<button type="button" class="bss-action-btn bss-action-btn--del"
					        data-id="<?php echo esc_attr( (string) $t->ID ); ?>" data-type="titre">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 4h10M5 4V2h4v2M5.5 6v5M8.5 6v5M3 4l1 8h6l1-8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php endif; ?>

	<div class="bss-toast" id="bss-toast" role="alert" aria-live="polite"></div>
</div>
