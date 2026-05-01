<?php
/**
 * Template public — Section Partenaires (vue visiteurs).
 * Inclus par BandStage_Studio::render_partenaires() quand le visiteur n'est pas musicien.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

BandStage_Public::maybe_inject_dynamic_css();

$band_name = esc_html( (string) get_option( 'bs_band_name', 'BandStage' ) );

// Filtre par type via GET.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$filter_slug = sanitize_key( $_GET['bs_type'] ?? '' );

// Tous les types disponibles.
$all_types = get_terms( array(
	'taxonomy'   => 'bs_type_partenaire',
	'hide_empty' => true,
	'orderby'    => 'name',
) );

// Query partenaires — mis en avant d'abord, puis alphabétique.
$query_args = array(
	'post_type'      => 'bs_partenaire',
	'post_status'    => 'publish',
	'posts_per_page' => 50,
	'orderby'        => 'meta_value title',
	'meta_key'       => 'bs_partenaire_featured',
	'order'          => 'DESC',
);

if ( $filter_slug ) {
	$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
		array(
			'taxonomy' => 'bs_type_partenaire',
			'field'    => 'slug',
			'terms'    => $filter_slug,
		),
	);
}

$partenaires = get_posts( $query_args );
?>

<div class="bs-pt-wrap">

	<!-- En-tête -->
	<header class="bs-pt-header">
		<div class="bs-pt-header__icon" aria-hidden="true">🤝</div>
		<div>
			<h1 class="bs-pt-title"><?php esc_html_e( 'Partenaires', 'bandstage' ); ?></h1>
			<p class="bs-pt-subtitle">
				<?php
				printf(
					/* translators: %s: nom du groupe */
					esc_html__( 'Les adresses recommandées par %s', 'bandstage' ),
					'<strong>' . esc_html( $band_name ) . '</strong>'
				);
				?>
			</p>
		</div>
	</header>

	<!-- Filtres par type -->
	<?php if ( ! empty( $all_types ) && ! is_wp_error( $all_types ) && count( $all_types ) > 1 ) : ?>
	<nav class="bs-pt-filters" aria-label="<?php esc_attr_e( 'Filtrer par type', 'bandstage' ); ?>">
		<a href="<?php echo esc_url( get_permalink() ); ?>"
		   class="bs-pt-pill <?php echo ! $filter_slug ? 'is-active' : ''; ?>">
			<?php esc_html_e( 'Tout', 'bandstage' ); ?>
		</a>
		<?php foreach ( $all_types as $term ) :
			$icon = (string) get_term_meta( $term->term_id, 'bs_term_icon', true );
		?>
		<a href="<?php echo esc_url( add_query_arg( 'bs_type', $term->slug, get_permalink() ) ); ?>"
		   class="bs-pt-pill <?php echo $filter_slug === $term->slug ? 'is-active' : ''; ?>">
			<?php if ( $icon ) echo esc_html( $icon ) . ' '; ?>
			<?php echo esc_html( $term->name ); ?>
		</a>
		<?php endforeach; ?>
	</nav>
	<?php endif; ?>

	<!-- Grille partenaires -->
	<?php if ( empty( $partenaires ) ) : ?>
	<div class="bs-pt-empty">
		<p><?php esc_html_e( 'Aucun partenaire dans cette catégorie pour l\'instant.', 'bandstage' ); ?></p>
	</div>

	<?php else : ?>
	<div class="bs-pt-grid">
		<?php foreach ( $partenaires as $part ) :
			$pid      = $part->ID;
			$url      = (string) get_post_meta( $pid, 'bs_partenaire_url',     true );
			$tel      = (string) get_post_meta( $pid, 'bs_partenaire_tel',     true );
			$adresse  = (string) get_post_meta( $pid, 'bs_partenaire_adresse', true );
			$ville    = (string) get_post_meta( $pid, 'bs_partenaire_ville',   true );
			$featured = (bool)   get_post_meta( $pid, 'bs_partenaire_featured', true );
			$thumb    = get_the_post_thumbnail_url( $pid, 'thumbnail' );
			$types    = wp_get_post_terms( $pid, 'bs_type_partenaire' );
			$type_lbl = ( $types && ! is_wp_error( $types ) ) ? $types[0]->name : '';
			$icon     = ( $types && ! is_wp_error( $types ) )
			            ? (string) get_term_meta( $types[0]->term_id, 'bs_term_icon', true )
			            : '📦';
			$desc     = wp_trim_words( wp_strip_all_tags( $part->post_content ), 20, '…' );
		?>
		<div class="bs-pt-card <?php echo $featured ? 'bs-pt-card--featured' : ''; ?>"
		     id="bs-pt-<?php echo esc_attr( (string) $pid ); ?>">

			<!-- Logo ou icône -->
			<div class="bs-pt-card__media">
				<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>"
				     alt="<?php echo esc_attr( get_the_title( $part ) ); ?>"
				     class="bs-pt-card__logo" loading="lazy">
				<?php else : ?>
				<div class="bs-pt-card__icon"><?php echo esc_html( $icon ?: '📦' ); ?></div>
				<?php endif; ?>
				<?php if ( $featured ) : ?>
				<span class="bs-pt-card__star" title="<?php esc_attr_e( 'Recommandé', 'bandstage' ); ?>">★</span>
				<?php endif; ?>
			</div>

			<!-- Infos -->
			<div class="bs-pt-card__body">
				<h2 class="bs-pt-card__name"><?php echo esc_html( get_the_title( $part ) ); ?></h2>

				<?php if ( $type_lbl ) : ?>
				<span class="bs-pt-card__type"><?php echo esc_html( $type_lbl ); ?></span>
				<?php endif; ?>

				<?php if ( $desc ) : ?>
				<p class="bs-pt-card__desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>

				<!-- Coordonnées -->
				<div class="bs-pt-card__coords">
					<?php if ( $ville || $adresse ) : ?>
					<div class="bs-pt-card__addr">
						<svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
							<path d="M6 1a3.5 3.5 0 013.5 3.5C9.5 8 6 11 6 11S2.5 8 2.5 4.5A3.5 3.5 0 016 1z" stroke="currentColor" stroke-width="1.2"/>
							<circle cx="6" cy="4.5" r="1" fill="currentColor"/>
						</svg>
						<span>
							<?php echo esc_html( implode( ', ', array_filter( array( $adresse, $ville ) ) ) ); ?>
						</span>
					</div>
					<?php endif; ?>

					<?php if ( $tel ) : ?>
					<a href="tel:<?php echo esc_attr( $tel ); ?>" class="bs-pt-card__contact">
						<svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
							<path d="M2 2.5C2 2 2.5 1 3.5 1L4.5 2.5 3.5 4A6 6 0 008 8.5l1.5-1 1.5 1c0 1-1 1.5-1.5 1.5C5 11 1 7 1 4A2 2 0 012 2.5z" stroke="currentColor" stroke-width="1.1" fill="none"/>
						</svg>
						<span><?php echo esc_html( $tel ); ?></span>
					</a>
					<?php endif; ?>
				</div>

				<!-- CTA -->
				<?php if ( $url ) : ?>
				<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"
				   class="bs-pt-card__link">
					<?php esc_html_e( 'Visiter le site', 'bandstage' ); ?>
					<svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
						<path d="M5 2H2v8h8V7M7 1h4v4M10 1L5 6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
				<?php endif; ?>
			</div>

		</div><!-- /.bs-pt-card -->
		<?php endforeach; ?>
	</div><!-- /.bs-pt-grid -->
	<?php endif; ?>

	<!-- Lien Studio pour les musiciens -->
	<?php if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) : ?>
	<div class="bs-pt-studio-link">
		<a href="<?php echo esc_url( BandStage_Studio::partenaires_url() ); ?>">
			⚙ <?php esc_html_e( 'Gérer les partenaires', 'bandstage' ); ?>
		</a>
	</div>
	<?php endif; ?>

</div><!-- /.bs-pt-wrap -->
