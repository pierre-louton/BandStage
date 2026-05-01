<?php
/**
 * Template public — Références.
 * Influences musicales en accordéon, chaque influence listant
 * les titres du répertoire interprétés par cet artiste.
 * Shortcode : [bandstage_references]
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

BandStage_Public::maybe_inject_dynamic_css();

$band_name        = esc_html( (string) get_option( 'bs_band_name', 'BandStage' ) );
$influences_label = esc_html( (string) get_option( 'bs_influences_label', 'Nos influences' ) );
$repertoire_label = esc_html( (string) get_option( 'bs_repertoire_label', 'Notre répertoire' ) );

// -----------------------------------------------------------------------
// Influences
// -----------------------------------------------------------------------
$influences_raw = (string) get_option( 'bs_influences', '[]' );
$influences     = json_decode( $influences_raw, true );
if ( ! is_array( $influences ) ) {
	$influences = array();
}
// Filtre les entrées vides et trie alphabétiquement.
$influences = array_values( array_filter( $influences, function( $i ) {
	return ! empty( trim( $i['name'] ?? '' ) );
} ) );
usort( $influences, function( $a, $b ) {
	return strcasecmp( $a['name'] ?? '', $b['name'] ?? '' );
} );

// -----------------------------------------------------------------------
// Répertoire — tous les titres indexés par artiste (clé normalisée)
// -----------------------------------------------------------------------
$titres_raw = get_posts( array(
	'post_type'      => 'bs_titre',
	'post_status'    => 'publish',
	'posts_per_page' => 500,
	'orderby'        => 'title',
	'order'          => 'ASC',
) );

// Index : artiste normalisé → liste de titres.
$titres_by_artist = array();
$originals        = array(); // Titres sans artiste = compositions originales.

foreach ( $titres_raw as $t ) {
	$artiste = trim( (string) get_post_meta( $t->ID, 'bs_titre_artiste', true ) );
	$type    = (string) get_post_meta( $t->ID, 'bs_titre_type', true );
	if ( 'original' === $type || '' === $artiste ) {
		$originals[] = $t;
	} else {
		$key = mb_strtolower( $artiste );
		if ( ! isset( $titres_by_artist[ $key ] ) ) {
			$titres_by_artist[ $key ] = array( 'label' => $artiste, 'titres' => array() );
		}
		$titres_by_artist[ $key ]['titres'][] = $t;
	}
}

// Associe chaque influence à ses titres (correspondance par nom normalisé).
foreach ( $influences as &$inf ) {
	$key = mb_strtolower( trim( $inf['name'] ) );
	$inf['titres'] = isset( $titres_by_artist[ $key ] )
		? $titres_by_artist[ $key ]['titres']
		: array();
}
unset( $inf );
?>

<div class="bs-rf-wrap">

	<!-- EN-TÊTE -->
	<header class="bs-rf-header">
		<div class="bs-rf-header__icon" aria-hidden="true">🎵</div>
		<div>
			<h1 class="bs-rf-title"><?php esc_html_e( 'Références', 'bandstage' ); ?></h1>
			<p class="bs-rf-subtitle"><?php echo $band_name; ?></p>
		</div>
	</header>

	<!-- ================================================================
	     INFLUENCES — accordéon
	     ================================================================ -->
	<?php if ( ! empty( $influences ) ) : ?>
	<section class="bs-rf-section">
		<h2 class="bs-rf-section-title">
			<span class="bs-rf-section-title__line"></span>
			<?php echo $influences_label; ?>
			<span class="bs-rf-section-title__line"></span>
		</h2>

		<div class="bs-rf-accordion" id="bs-rf-accordion">
			<?php foreach ( $influences as $idx => $inf ) :
				$name     = esc_html( $inf['name'] );
				$genre    = esc_html( $inf['genre']   ?? '' );
				$comment  = esc_html( $inf['comment'] ?? '' );
				$url      = esc_url( $inf['url'] ?? '' );
				$count    = count( $inf['titres'] );
				$item_id  = 'bs-inf-' . $idx;
			?>
			<div class="bs-rf-acc-item" id="<?php echo esc_attr( $item_id ); ?>">

				<!-- En-tête cliquable -->
				<button type="button"
				        class="bs-rf-acc-trigger"
				        aria-expanded="false"
				        aria-controls="<?php echo esc_attr( $item_id . '-body' ); ?>">

					<div class="bs-rf-acc-avatar" aria-hidden="true">
						<?php echo esc_html( mb_strtoupper( mb_substr( $inf['name'], 0, 1 ) ) ); ?>
					</div>

					<div class="bs-rf-acc-info">
						<span class="bs-rf-acc-name"><?php echo $name; ?></span>
						<?php if ( $genre ) : ?>
						<span class="bs-rf-acc-genre"><?php echo $genre; ?></span>
						<?php endif; ?>
					</div>

					<div class="bs-rf-acc-right">
						<?php if ( $count > 0 ) : ?>
						<span class="bs-rf-acc-count" title="<?php printf( esc_attr__( '%d titre(s) joué(s)', 'bandstage' ), $count ); ?>">
							<?php echo esc_html( (string) $count ); ?>
						</span>
						<?php endif; ?>
						<svg class="bs-rf-acc-chevron" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
							<path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
				</button>

				<!-- Corps dépliable -->
				<div class="bs-rf-acc-body" id="<?php echo esc_attr( $item_id . '-body' ); ?>" hidden>

					<!-- Commentaire sur l'artiste -->
					<?php if ( $comment ) : ?>
					<p class="bs-rf-acc-comment"><?php echo $comment; ?></p>
					<?php endif; ?>

					<!-- Lien externe -->
					<?php if ( $url ) : ?>
					<a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer"
					   class="bs-rf-acc-link">
						<?php esc_html_e( 'En savoir plus', 'bandstage' ); ?>
						<svg width="11" height="11" viewBox="0 0 11 11" fill="none">
							<path d="M4.5 2H2v7h7V6.5M6 1h4v4M9.5 1L4 6.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
					<?php endif; ?>

					<!-- Titres joués -->
					<?php if ( ! empty( $inf['titres'] ) ) : ?>
					<div class="bs-rf-acc-titres">
						<div class="bs-rf-acc-titres-label">
							<?php
							printf(
								esc_html( _n(
									'Nous avons joué %d chanson de cet artiste',
									'Nous avons joué %d chansons de cet artiste',
									$count,
									'bandstage'
								) ),
								$count
							);
							?>
						</div>
						<?php foreach ( $inf['titres'] as $t ) :
							$annee = (string) get_post_meta( $t->ID, 'bs_titre_annee', true );
							$notes = wp_trim_words( wp_strip_all_tags( $t->post_content ), 12, '…' );
						?>
						<div class="bs-rf-acc-titre-row">
							<svg width="10" height="10" viewBox="0 0 10 10" fill="none" aria-hidden="true">
								<circle cx="5" cy="5" r="4" stroke="currentColor" stroke-width="1"/>
								<path d="M4 3.5l2 1.5-2 1.5V3.5z" fill="currentColor"/>
							</svg>
							<div class="bs-rf-acc-titre-info">
								<span class="bs-rf-acc-titre-name"><?php echo esc_html( get_the_title( $t ) ); ?></span>
								<?php if ( $annee ) : ?>
								<span class="bs-rf-acc-titre-year">(<?php echo esc_html( $annee ); ?>)</span>
								<?php endif; ?>
								<?php if ( $notes ) : ?>
								<span class="bs-rf-acc-titre-notes"><?php echo esc_html( $notes ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
					<?php elseif ( $count === 0 ) : ?>
					<p class="bs-rf-acc-no-titre"><?php esc_html_e( 'Aucun titre dans le répertoire pour cet artiste.', 'bandstage' ); ?></p>
					<?php endif; ?>

				</div><!-- /.bs-rf-acc-body -->
			</div><!-- /.bs-rf-acc-item -->
			<?php endforeach; ?>
		</div><!-- /.bs-rf-accordion -->
	</section>
	<?php endif; ?>

	<!-- ================================================================
	     COMPOSITIONS ORIGINALES (si présentes)
	     ================================================================ -->
	<?php if ( ! empty( $originals ) ) : ?>
	<section class="bs-rf-section">
		<h2 class="bs-rf-section-title">
			<span class="bs-rf-section-title__line"></span>
			✨ <?php esc_html_e( 'Compositions originales', 'bandstage' ); ?>
			<span class="bs-rf-section-title__line"></span>
		</h2>
		<div class="bs-rf-rep-list">
			<?php foreach ( $originals as $t ) :
				$notes = wp_trim_words( wp_strip_all_tags( $t->post_content ), 15, '…' );
			?>
			<div class="bs-rf-titre-row">
				<div class="bs-rf-titre-dot bs-rf-titre-dot--orig" aria-hidden="true"></div>
				<div class="bs-rf-titre-body">
					<span class="bs-rf-titre-name"><?php echo esc_html( get_the_title( $t ) ); ?></span>
					<?php if ( $notes ) : ?>
					<span class="bs-rf-titre-notes"><?php echo esc_html( $notes ); ?></span>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( empty( $influences ) && empty( $originals ) ) : ?>
	<div class="bs-rf-empty"><p><?php esc_html_e( 'Contenu à venir.', 'bandstage' ); ?></p></div>
	<?php endif; ?>

</div><!-- /.bs-rf-wrap -->
