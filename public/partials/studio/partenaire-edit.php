<?php
/**
 * Studio — Éditeur de partenaire.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$post_id = absint( $_GET['bs_id'] ?? 0 );
$post    = $post_id ? get_post( $post_id ) : null;
$is_edit = $post && 'bs_partenaire' === $post->post_type;

$title    = $is_edit ? $post->post_title   : '';
$content  = $is_edit ? $post->post_content : '';
$url      = $is_edit ? (string) get_post_meta( $post_id, 'bs_partenaire_url',      true ) : '';
$tel      = $is_edit ? (string) get_post_meta( $post_id, 'bs_partenaire_tel',      true ) : '';
$adresse  = $is_edit ? (string) get_post_meta( $post_id, 'bs_partenaire_adresse',  true ) : '';
$ville    = $is_edit ? (string) get_post_meta( $post_id, 'bs_partenaire_ville',    true ) : '';
$featured = $is_edit ? (bool)   get_post_meta( $post_id, 'bs_partenaire_featured', true ) : false;
$thumb_id  = $is_edit ? (int) get_post_thumbnail_id( $post_id ) : 0;
$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';

// Type actuel.
$current_types = $is_edit ? wp_get_post_terms( $post_id, 'bs_type_partenaire' ) : array();
$current_type  = ( $current_types && ! is_wp_error( $current_types ) ) ? $current_types[0]->term_id : 0;

// Tous les types disponibles.
$all_types = get_terms( array(
	'taxonomy'   => 'bs_type_partenaire',
	'hide_empty' => false,
	'orderby'    => 'name',
) );

$dashboard_url = BandStage_Studio::partenaires_url();
$page_title    = $is_edit ? __( 'Modifier le partenaire', 'bandstage' ) : __( 'Nouveau partenaire', 'bandstage' );
?>

<div class="bss-wrap">

	<!-- HEADER -->
	<header class="bss-header bss-header--editor">
		<a href="<?php echo esc_url( $dashboard_url ); ?>" class="bss-back-btn" aria-label="<?php esc_attr_e( 'Retour', 'bandstage' ); ?>">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M13 4L7 10l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</a>
		<div class="bss-header__center">
			<div class="bss-header__brand"><?php echo esc_html( $page_title ); ?></div>
		</div>
		<div class="bss-header__right"></div>
	</header>

	<div class="bss-editor-body">

		<input type="hidden" id="bss-post-id"   value="<?php echo esc_attr( (string) ( $is_edit ? $post_id : 0 ) ); ?>">
		<input type="hidden" id="bss-thumb-id"  value="<?php echo esc_attr( (string) $thumb_id ); ?>">
		<input type="hidden" id="bss-post-type" value="partenaire">

		<!-- NOM -->
		<div class="bss-field-group">
			<label class="bss-label" for="bss-title">
				<?php esc_html_e( 'Nom', 'bandstage' ); ?>
				<span class="bss-required">*</span>
			</label>
			<input type="text" id="bss-title" class="bss-input"
			       value="<?php echo esc_attr( $title ); ?>"
			       placeholder="<?php esc_attr_e( 'Ex : La Guitare Bleue', 'bandstage' ); ?>"
			       maxlength="100">
		</div>

		<!-- TYPE -->
		<?php if ( ! empty( $all_types ) && ! is_wp_error( $all_types ) ) : ?>
		<div class="bss-field-group">
			<label class="bss-label" for="bss-type"><?php esc_html_e( 'Type', 'bandstage' ); ?></label>
			<div class="bss-type-select">
				<?php foreach ( $all_types as $term ) :
					$icon = (string) get_term_meta( $term->term_id, 'bs_term_icon', true );
				?>
				<label class="bss-type-option <?php echo (int) $term->term_id === $current_type ? 'is-sel' : ''; ?>">
					<input type="radio" name="bss-type" value="<?php echo esc_attr( (string) $term->term_id ); ?>"
					       <?php checked( (int) $term->term_id, $current_type ); ?> class="sr-only">
					<span class="bss-type-option__icon"><?php echo esc_html( $icon ?: '📦' ); ?></span>
					<span class="bss-type-option__label"><?php echo esc_html( $term->name ); ?></span>
				</label>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- LOGO -->
		<div class="bss-field-group">
			<label class="bss-label"><?php esc_html_e( 'Logo / Photo', 'bandstage' ); ?></label>
			<div class="bss-thumb-picker" id="bss-thumb-picker">
				<?php if ( $thumb_url ) : ?>
				<div class="bss-thumb-preview" id="bss-thumb-preview"
				     style="background-image:url('<?php echo esc_url( $thumb_url ); ?>')">
					<button type="button" class="bss-thumb-remove" id="bss-thumb-remove">×</button>
				</div>
				<?php else : ?>
				<div class="bss-thumb-empty" id="bss-thumb-preview">
					<div class="bss-thumb-empty__icon">🏪</div>
					<div class="bss-thumb-empty__label"><?php esc_html_e( 'Ajouter un logo', 'bandstage' ); ?></div>
				</div>
				<?php endif; ?>
				<input type="file" id="bss-thumb-file" accept="image/*" capture="environment"
				       style="display:none">
			</div>
		</div>

		<!-- COORDONNÉES -->
		<div class="bss-field-group">
			<div class="bss-subsection-title"><?php esc_html_e( 'Coordonnées', 'bandstage' ); ?></div>

			<label class="bss-label" for="bss-ville">📍 <?php esc_html_e( 'Ville', 'bandstage' ); ?></label>
			<input type="text" id="bss-ville" class="bss-input"
			       value="<?php echo esc_attr( $ville ); ?>"
			       placeholder="Paris, Lyon…">

			<label class="bss-label" for="bss-adresse"><?php esc_html_e( 'Adresse', 'bandstage' ); ?></label>
			<input type="text" id="bss-adresse" class="bss-input"
			       value="<?php echo esc_attr( $adresse ); ?>"
			       placeholder="12 rue de la Musique">

			<label class="bss-label" for="bss-tel">📞 <?php esc_html_e( 'Téléphone', 'bandstage' ); ?></label>
			<input type="tel" id="bss-tel" class="bss-input"
			       value="<?php echo esc_attr( $tel ); ?>"
			       placeholder="06 xx xx xx xx"
			       inputmode="tel">

			<label class="bss-label" for="bss-url">🌐 <?php esc_html_e( 'Site web', 'bandstage' ); ?></label>
			<input type="url" id="bss-url" class="bss-input"
			       value="<?php echo esc_attr( $url ); ?>"
			       placeholder="https://"
			       inputmode="url">
		</div>

		<!-- DESCRIPTION -->
		<div class="bss-field-group">
			<label class="bss-label" for="bss-content">
				<?php esc_html_e( 'Description', 'bandstage' ); ?>
				<span class="bss-label-hint"><?php esc_html_e( 'Optionnel', 'bandstage' ); ?></span>
			</label>
			<textarea id="bss-content" class="bss-textarea" rows="3"
			          placeholder="<?php esc_attr_e( 'Quelques mots sur ce partenaire…', 'bandstage' ); ?>"><?php echo esc_textarea( $content ); ?></textarea>
		</div>

		<!-- MIS EN AVANT -->
		<div class="bss-field-group">
			<label class="bss-toggle-row">
				<div>
					<div class="bss-label" style="margin:0">⭐ <?php esc_html_e( 'Mettre en avant', 'bandstage' ); ?></div>
					<p class="bss-hint"><?php esc_html_e( 'Apparaît en tête de la liste des partenaires.', 'bandstage' ); ?></p>
				</div>
				<button type="button" class="bss-toggle <?php echo $featured ? 'is-on' : ''; ?>"
				        id="bss-featured" aria-pressed="<?php echo $featured ? 'true' : 'false'; ?>"
				        onclick="this.classList.toggle('is-on');this.setAttribute('aria-pressed',this.classList.contains('is-on'))"></button>
			</label>
		</div>

	</div><!-- /.bss-editor-body -->

	<!-- BARRE D'ACTIONS FIXE EN BAS -->
	<div class="bss-action-bar">
		<?php if ( $is_edit ) : ?>
		<button type="button" class="bss-btn bss-btn--danger bss-btn--sm" id="bss-delete-part">
			<?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
		</button>
		<?php endif; ?>
		<button type="button" class="bss-btn bss-btn--gold bss-btn--lg" id="bss-save-part">
			<?php $is_edit ? esc_html_e( 'Enregistrer', 'bandstage' ) : esc_html_e( 'Ajouter', 'bandstage' ); ?>
		</button>
	</div>

	<div class="bss-toast" id="bss-toast" role="alert" aria-live="polite"></div>

</div><!-- /.bss-wrap -->
