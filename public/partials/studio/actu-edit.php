<?php
/**
 * Studio — Éditeur d'actualité.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$post_id   = absint( $_GET['bs_id'] ?? 0 );
$post      = $post_id ? get_post( $post_id ) : null;
$is_edit   = $post && 'bs_news' === $post->post_type;

$title   = $is_edit ? $post->post_title   : '';
$content = $is_edit ? $post->post_content : '';
$excerpt = $is_edit ? $post->post_excerpt : '';
$status  = $is_edit ? $post->post_status  : 'draft';
$thumb_id  = $is_edit ? (int) get_post_thumbnail_id( $post_id ) : 0;
$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';

$dashboard_url = BandStage_Studio::url( 'dashboard' );
$page_title    = $is_edit ? __( 'Modifier l\'actualité', 'bandstage' ) : __( 'Nouvelle actualité', 'bandstage' );
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
		<div class="bss-header__right">
			<?php if ( $is_edit ) : ?>
			<span class="bss-badge bss-badge--<?php echo 'publish' === $status ? 'pub' : 'draft'; ?>" id="bss-status-badge">
				<?php echo 'publish' === $status ? esc_html__( 'Publiée', 'bandstage' ) : esc_html__( 'Brouillon', 'bandstage' ); ?>
			</span>
			<?php endif; ?>
		</div>
	</header>

	<div class="bss-editor-body">

		<input type="hidden" id="bss-post-id" value="<?php echo esc_attr( (string) ( $is_edit ? $post_id : 0 ) ); ?>">
		<input type="hidden" id="bss-thumb-id" value="<?php echo esc_attr( (string) $thumb_id ); ?>">

		<!-- TITRE -->
		<div class="bss-field-group">
			<label class="bss-label" for="bss-title">
				<?php esc_html_e( 'Titre', 'bandstage' ); ?>
				<span class="bss-required">*</span>
				<span class="bss-label-hint"><?php esc_html_e( 'Apparaîtra dans le ticker', 'bandstage' ); ?></span>
			</label>
			<input type="text" id="bss-title" class="bss-input"
			       value="<?php echo esc_attr( $title ); ?>"
			       placeholder="<?php esc_attr_e( 'Ex : Concert au Café de la Danse — 14 juin', 'bandstage' ); ?>"
			       maxlength="150">
		</div>

		<!-- IMAGE -->
		<div class="bss-field-group">
			<label class="bss-label"><?php esc_html_e( 'Image', 'bandstage' ); ?></label>
			<div class="bss-thumb-picker" id="bss-thumb-picker">
				<?php if ( $thumb_url ) : ?>
				<div class="bss-thumb-preview" id="bss-thumb-preview"
				     style="background-image:url('<?php echo esc_url( $thumb_url ); ?>')">
					<button type="button" class="bss-thumb-remove" id="bss-thumb-remove"
					        aria-label="<?php esc_attr_e( 'Supprimer l\'image', 'bandstage' ); ?>">×</button>
				</div>
				<?php else : ?>
				<div class="bss-thumb-empty" id="bss-thumb-preview">
					<div class="bss-thumb-empty__icon">🖼</div>
					<div class="bss-thumb-empty__label"><?php esc_html_e( 'Ajouter une image', 'bandstage' ); ?></div>
				</div>
				<?php endif; ?>
				<input type="file" id="bss-thumb-file" accept="image/*" capture="environment"
				       style="display:none" aria-label="<?php esc_attr_e( 'Choisir une image', 'bandstage' ); ?>">
			</div>
			<p class="bss-hint"><?php esc_html_e( 'Optionnel. Appuyez sur la zone pour prendre une photo ou choisir dans la galerie.', 'bandstage' ); ?></p>
		</div>

		<!-- RÉSUMÉ -->
		<div class="bss-field-group">
			<label class="bss-label" for="bss-excerpt">
				<?php esc_html_e( 'Résumé', 'bandstage' ); ?>
				<span class="bss-label-hint"><?php esc_html_e( 'Optionnel, 1–2 phrases', 'bandstage' ); ?></span>
			</label>
			<textarea id="bss-excerpt" class="bss-textarea bss-textarea--sm" rows="2"
			          maxlength="200"
			          placeholder="<?php esc_attr_e( 'Courte accroche visible dans les listes…', 'bandstage' ); ?>"><?php echo esc_textarea( $excerpt ); ?></textarea>
		</div>

		<!-- CONTENU -->
		<div class="bss-field-group">
			<label class="bss-label" for="bss-content">
				<?php esc_html_e( 'Contenu', 'bandstage' ); ?>
				<span class="bss-label-hint"><?php esc_html_e( 'S\'affichera dans la section Humeurs', 'bandstage' ); ?></span>
			</label>
			<!-- Mini barre de formatage -->
			<div class="bss-format-bar">
				<button type="button" class="bss-fmt-btn" data-cmd="bold"        title="Gras">      <strong>G</strong></button>
				<button type="button" class="bss-fmt-btn" data-cmd="italic"      title="Italique">  <em>I</em></button>
				<button type="button" class="bss-fmt-btn" data-cmd="insertUnorderedList" title="Liste"> ☰</button>
				<button type="button" class="bss-fmt-btn" data-cmd="createLink"  title="Lien">      🔗</button>
			</div>
			<div id="bss-content" class="bss-content-editable" contenteditable="true"
			     data-placeholder="<?php esc_attr_e( 'Racontez l\'actu en détail…', 'bandstage' ); ?>"><?php echo wp_kses_post( $content ); ?></div>
		</div>

	</div><!-- /.bss-editor-body -->

	<!-- BARRE D'ACTIONS FIXE EN BAS -->
	<div class="bss-action-bar">
		<button type="button" class="bss-btn bss-btn--outline" id="bss-save-draft">
			<?php esc_html_e( 'Brouillon', 'bandstage' ); ?>
		</button>
		<button type="button" class="bss-btn bss-btn--gold bss-btn--lg" id="bss-publish">
			<?php echo 'publish' === $status ? esc_html__( 'Mettre à jour', 'bandstage' ) : esc_html__( 'Publier', 'bandstage' ); ?>
		</button>
	</div>

	<div class="bss-toast" id="bss-toast" role="alert" aria-live="polite"></div>

</div><!-- /.bss-wrap -->
