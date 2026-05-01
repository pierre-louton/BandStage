<?php
/**
 * Studio Partenaires — liste et gestion.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$user      = wp_get_current_user();
$band_name = (string) get_option( 'bs_band_name', 'BandStage' );
$initials  = mb_strtoupper( mb_substr( $user->display_name, 0, 2 ) );

// Tous les types.
$all_types = get_terms( array(
	'taxonomy'   => 'bs_type_partenaire',
	'hide_empty' => false,
	'orderby'    => 'name',
) );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$filter_type = sanitize_key( $_GET['bs_type'] ?? '' );

$query_args = array(
	'post_type'      => 'bs_partenaire',
	'posts_per_page' => 50,
	'post_status'    => 'publish',
	'orderby'        => 'meta_value title',
	'meta_key'       => 'bs_partenaire_featured',
	'order'          => 'DESC',
);

if ( $filter_type ) {
	$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
		array(
			'taxonomy' => 'bs_type_partenaire',
			'field'    => 'slug',
			'terms'    => $filter_type,
		),
	);
}

$partenaires = get_posts( $query_args );

$new_url       = BandStage_Studio::partenaires_url( 'partenaire-edit' );
$homepage_url  = home_url();
?>

<div class="bss-wrap">

	<header class="bss-header">
		<div class="bss-header__left">
			<a href="<?php echo esc_url( $homepage_url ); ?>" class="bss-back-btn" aria-label="Accueil">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
					<path d="M13 4L7 10l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
			</a>
			<div>
				<div class="bss-header__brand">🤝 <?php esc_html_e( 'Partenaires', 'bandstage' ); ?></div>
				<div class="bss-header__sub"><?php echo esc_html( $band_name ); ?></div>
			</div>
		</div>
		<div class="bss-header__right">
			<a href="<?php echo esc_url( $new_url ); ?>" class="bss-btn bss-btn--gold bss-btn--sm">
				+ <?php esc_html_e( 'Nouveau', 'bandstage' ); ?>
			</a>
		</div>
	</header>

	<!-- Filtres par type -->
	<?php if ( ! empty( $all_types ) && ! is_wp_error( $all_types ) ) : ?>
	<div class="bss-filter-bar">
		<a href="<?php echo esc_url( get_permalink() ); ?>"
		   class="bss-filter-pill <?php echo ! $filter_type ? 'is-active' : ''; ?>">
			<?php esc_html_e( 'Tous', 'bandstage' ); ?>
		</a>
		<?php foreach ( $all_types as $term ) :
			$icon = (string) get_term_meta( $term->term_id, 'bs_term_icon', true );
		?>
		<a href="<?php echo esc_url( add_query_arg( 'bs_type', $term->slug, get_permalink() ) ); ?>"
		   class="bss-filter-pill <?php echo $filter_type === $term->slug ? 'is-active' : ''; ?>">
			<?php echo esc_html( ( $icon ? $icon . ' ' : '' ) . $term->name ); ?>
		</a>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<!-- Liste partenaires -->
	<div class="bss-section" style="margin-top:12px">
		<?php if ( empty( $partenaires ) ) : ?>
		<div class="bss-empty">
			<div class="bss-empty__icon">🤝</div>
			<p><?php esc_html_e( 'Aucun partenaire pour l\'instant.', 'bandstage' ); ?></p>
			<a href="<?php echo esc_url( $new_url ); ?>" class="bss-btn bss-btn--gold">
				+ <?php esc_html_e( 'Ajouter le premier', 'bandstage' ); ?>
			</a>
		</div>
		<?php else : ?>
		<div class="bss-list">
			<?php foreach ( $partenaires as $part ) :
				$edit_url = BandStage_Studio::partenaires_url( 'partenaire-edit', $part->ID );
				$thumb    = get_the_post_thumbnail_url( $part, array( 56, 56 ) );
				$ville    = (string) get_post_meta( $part->ID, 'bs_partenaire_ville', true );
				$tel      = (string) get_post_meta( $part->ID, 'bs_partenaire_tel',   true );
				$site_url = (string) get_post_meta( $part->ID, 'bs_partenaire_url',   true );
				$featured = (bool)   get_post_meta( $part->ID, 'bs_partenaire_featured', true );
				$types    = wp_get_post_terms( $part->ID, 'bs_type_partenaire' );
				$type_lbl = ( $types && ! is_wp_error( $types ) ) ? $types[0]->name : '';
				$icon     = ( $types && ! is_wp_error( $types ) )
				            ? (string) get_term_meta( $types[0]->term_id, 'bs_term_icon', true )
				            : '📦';
			?>
			<div class="bss-item" id="bss-item-partenaire-<?php echo esc_attr( (string) $part->ID ); ?>">
				<?php if ( $thumb ) : ?>
				<div class="bss-item__thumb" style="background-image:url('<?php echo esc_url( $thumb ); ?>')"></div>
				<?php else : ?>
				<div class="bss-item__partner-icon"><?php echo esc_html( $icon ?: '📦' ); ?></div>
				<?php endif; ?>

				<div class="bss-item__body">
					<div class="bss-item__title">
						<?php echo esc_html( get_the_title( $part ) ); ?>
						<?php if ( $featured ) echo '<span class="bss-star">★</span>'; // phpcs:ignore ?>
					</div>
					<div class="bss-item__meta">
						<?php if ( $type_lbl ) : ?>
						<span class="bss-badge bss-badge--type"><?php echo esc_html( $type_lbl ); ?></span>
						<?php endif; ?>
						<?php if ( $ville ) : ?>
						<span class="bss-item__date">📍 <?php echo esc_html( $ville ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( $tel || $site_url ) : ?>
					<div class="bss-item__links">
						<?php if ( $tel ) : ?>
						<a href="tel:<?php echo esc_attr( $tel ); ?>" class="bss-item__link">📞</a>
						<?php endif; ?>
						<?php if ( $site_url ) : ?>
						<a href="<?php echo esc_url( $site_url ); ?>" target="_blank" rel="noopener" class="bss-item__link">🌐</a>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>

				<div class="bss-item__actions">
					<a href="<?php echo esc_url( $edit_url ); ?>" class="bss-action-btn">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
							<path d="M11 2l3 3-8 8H3V10l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
						</svg>
					</a>
					<button type="button" class="bss-action-btn bss-action-btn--del"
					        data-id="<?php echo esc_attr( (string) $part->ID ); ?>"
					        data-type="partenaire">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
							<path d="M2 4h10M5 4V2h4v2M5.5 6v5M8.5 6v5M3 4l1 8h6l1-8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>

	<div class="bss-toast" id="bss-toast" role="alert" aria-live="polite"></div>
</div>
