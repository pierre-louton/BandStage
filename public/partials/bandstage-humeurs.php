<?php
/**
 * Template public — Section Humeurs (billets d'actualité bs_news).
 * Shortcode : [bandstage_humeurs]
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

// CSS dynamique depuis les réglages admin.
BandStage_Public::maybe_inject_dynamic_css();

$band_name  = esc_html( (string) get_option( 'bs_band_name', 'BandStage' ) );
$accent     = sanitize_hex_color( (string) get_option( 'bs_accent_color', '#D4A820' ) ) ?: '#D4A820';

// Paramètres GET pour la pagination.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$paged = max( 1, absint( $_GET['bs_page'] ?? 1 ) );
$per_page = 6;

$query = new WP_Query( array(
	'post_type'      => 'bs_news',
	'post_status'    => 'publish',
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

$total_pages = $query->max_num_pages;

// Lien vers le Studio si musicien connecté.
$studio_url = '';
if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
	$studio_url = BandStage_Studio::url( 'dashboard' );
}

// Helper initiales (protégé contre les redéclarations).
if ( ! function_exists( 'bsh_initials' ) ) :
function bsh_initials( string $name ): string {
	$parts = explode( ' ', trim( $name ) );
	$init  = mb_strtoupper( mb_substr( $parts[0], 0, 1 ) );
	if ( isset( $parts[1] ) ) {
		$init .= mb_strtoupper( mb_substr( $parts[1], 0, 1 ) );
	}
	return $init ?: '?';
}
endif;
?>

<div class="bs-hm-wrap">

	<!-- En-tête -->
	<header class="bs-hm-header">
		<div class="bs-hm-header__left">
			<svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
				<rect x="3" y="4" width="16" height="13" rx="2" stroke="currentColor" stroke-width="1.4"/>
				<line x1="3" y1="9" x2="19" y2="9" stroke="currentColor" stroke-width="1.4"/>
				<path d="M17 2v4M7 2v4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
				<path d="M14 1.5L15.5 3 14 4.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
			</svg>
			<h1 class="bs-hm-title">
				<?php esc_html_e( 'Humeurs', 'bandstage' ); ?>
				<?php if ( $query->found_posts ) : ?>
				<span class="bs-hm-count"><?php echo esc_html( (string) $query->found_posts ); ?></span>
				<?php endif; ?>
			</h1>
		</div>
		<?php if ( $studio_url ) : ?>
		<a href="<?php echo esc_url( $studio_url ); ?>" class="bs-hm-write-btn" title="<?php esc_attr_e( 'Écrire un billet', 'bandstage' ); ?>">
			<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
				<path d="M13 2l3 3-9 9H4V11l9-9z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
			</svg>
			<span><?php esc_html_e( 'Écrire', 'bandstage' ); ?></span>
		</a>
		<?php endif; ?>
	</header>

	<!-- Billets -->
	<?php if ( ! $query->have_posts() ) : ?>
	<div class="bs-hm-empty">
		<svg width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true">
			<rect x="8" y="10" width="32" height="28" rx="3" stroke="currentColor" stroke-width="1.5" fill="none" opacity=".4"/>
			<line x1="14" y1="18" x2="34" y2="18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".4"/>
			<line x1="14" y1="24" x2="28" y2="24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".3"/>
		</svg>
		<p><?php esc_html_e( 'Aucun billet pour l\'instant, revenez bientôt.', 'bandstage' ); ?></p>
	</div>

	<?php else : ?>
	<div class="bs-hm-list">
		<?php while ( $query->have_posts() ) : $query->the_post();
			$post_id   = get_the_ID();
			$author_id = (int) get_post_field( 'post_author', $post_id );
			$author    = get_the_author_meta( 'display_name', $author_id );
			$avatar_url = get_avatar_url( $author_id, array( 'size' => 40, 'default' => '404' ) );
			$has_avatar = $avatar_url && ! str_contains( (string) $avatar_url, 'gravatar.com/avatar/00000' );
			$thumb      = get_the_post_thumbnail_url( $post_id, 'medium_large' );
			$content    = get_the_content();
			$has_content = trim( strip_tags( $content ) ) !== '';
		?>
		<article class="bs-hm-card" id="bs-hm-<?php echo esc_attr( (string) $post_id ); ?>">

			<!-- Image à la une -->
			<?php if ( $thumb ) : ?>
			<div class="bs-hm-card__img" style="background-image:url('<?php echo esc_url( $thumb ); ?>')" role="img"
			     aria-label="<?php echo esc_attr( get_the_title() ); ?>"></div>
			<?php endif; ?>

			<!-- Contenu -->
			<div class="bs-hm-card__body">

				<!-- Auteur + date -->
				<div class="bs-hm-card__byline">
					<div class="bs-hm-card__avatar">
						<?php if ( $has_avatar ) : ?>
						<img src="<?php echo esc_url( $avatar_url ); ?>" alt="" width="36" height="36" loading="lazy"
						     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
						<span style="display:none"><?php echo esc_html( bsh_initials( $author ) ); ?></span>
						<?php else : ?>
						<span><?php echo esc_html( bsh_initials( $author ) ); ?></span>
						<?php endif; ?>
					</div>
					<div class="bs-hm-card__author-info">
						<span class="bs-hm-card__author"><?php echo esc_html( $author ); ?></span>
						<time class="bs-hm-card__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>">
							<?php echo esc_html( get_the_date( 'd M Y' ) ); ?>
						</time>
					</div>
				</div>

				<!-- Titre -->
				<h2 class="bs-hm-card__title"><?php the_title(); ?></h2>

				<!-- Extrait toujours visible -->
				<?php if ( has_excerpt() ) : ?>
				<p class="bs-hm-card__excerpt"><?php the_excerpt(); ?></p>
				<?php endif; ?>

				<!-- Contenu complet — déplié au tap -->
				<?php if ( $has_content ) : ?>
				<div class="bs-hm-card__content" id="bs-hm-content-<?php echo esc_attr( (string) $post_id ); ?>"
				     aria-hidden="true">
					<?php echo wp_kses_post( wpautop( $content ) ); ?>
				</div>
				<button type="button"
				        class="bs-hm-toggle"
				        data-target="bs-hm-content-<?php echo esc_attr( (string) $post_id ); ?>"
				        aria-expanded="false">
					<span class="bs-hm-toggle__label"><?php esc_html_e( 'Lire la suite', 'bandstage' ); ?></span>
					<svg class="bs-hm-toggle__chevron" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
						<path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
				<?php endif; ?>

			</div><!-- /.bs-hm-card__body -->
		</article>
		<?php endwhile; wp_reset_postdata(); ?>
	</div><!-- /.bs-hm-list -->

	<!-- Pagination -->
	<?php if ( $total_pages > 1 ) : ?>
	<nav class="bs-hm-pagination" aria-label="<?php esc_attr_e( 'Navigation des billets', 'bandstage' ); ?>">
		<?php if ( $paged > 1 ) : ?>
		<a href="<?php echo esc_url( add_query_arg( 'bs_page', $paged - 1 ) ); ?>" class="bs-hm-page-btn">
			← <?php esc_html_e( 'Plus récents', 'bandstage' ); ?>
		</a>
		<?php endif; ?>
		<span class="bs-hm-page-info">
			<?php printf( esc_html__( '%1$d / %2$d', 'bandstage' ), $paged, $total_pages ); ?>
		</span>
		<?php if ( $paged < $total_pages ) : ?>
		<a href="<?php echo esc_url( add_query_arg( 'bs_page', $paged + 1 ) ); ?>" class="bs-hm-page-btn">
			<?php esc_html_e( 'Plus anciens', 'bandstage' ); ?> →
		</a>
		<?php endif; ?>
	</nav>
	<?php endif; ?>

	<?php endif; // have_posts ?>

</div><!-- /.bs-hm-wrap -->
