<?php
/**
 * Studio — Éditeur de titre (répertoire).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$post_id = absint( $_GET['bs_id'] ?? 0 );
$post    = $post_id ? get_post( $post_id ) : null;
$is_edit = $post && 'bs_titre' === $post->post_type;

$title   = $is_edit ? $post->post_title   : '';
$notes   = $is_edit ? $post->post_content : '';
$type    = $is_edit ? (string) get_post_meta( $post_id, 'bs_titre_type',    true ) : 'reprise';
$artiste = $is_edit ? (string) get_post_meta( $post_id, 'bs_titre_artiste', true ) : '';
$annee   = $is_edit ? (string) get_post_meta( $post_id, 'bs_titre_annee',   true ) : '';

$back_url   = BandStage_Studio::repertoire_url();
$page_title = $is_edit ? __( 'Modifier le titre', 'bandstage' ) : __( 'Nouveau titre', 'bandstage' );
?>

<div class="bss-wrap">

	<header class="bss-header bss-header--editor">
		<a href="<?php echo esc_url( $back_url ); ?>" class="bss-back-btn">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M13 4L7 10l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</a>
		<div class="bss-header__center">
			<div class="bss-header__brand"><?php echo esc_html( $page_title ); ?></div>
		</div>
		<div class="bss-header__right"></div>
	</header>

	<div class="bss-editor-body">

		<input type="hidden" id="bss-post-id" value="<?php echo esc_attr( (string) ( $is_edit ? $post_id : 0 ) ); ?>">
		<input type="hidden" id="bss-post-type" value="titre">

		<!-- TYPE — Original ou Reprise -->
		<div class="bss-field-group">
			<div class="bss-label"><?php esc_html_e( 'Type', 'bandstage' ); ?></div>
			<div class="bss-type-select">
				<label class="bss-type-option <?php echo 'reprise' === $type ? 'is-sel' : ''; ?>">
					<input type="radio" name="bss-type" value="reprise" class="sr-only"
					       <?php checked( $type, 'reprise' ); ?>>
					<span class="bss-type-option__icon">🎤</span>
					<span class="bss-type-option__label"><?php esc_html_e( 'Reprise', 'bandstage' ); ?></span>
				</label>
				<label class="bss-type-option <?php echo 'original' === $type ? 'is-sel' : ''; ?>">
					<input type="radio" name="bss-type" value="original" class="sr-only"
					       <?php checked( $type, 'original' ); ?>>
					<span class="bss-type-option__icon">✨</span>
					<span class="bss-type-option__label"><?php esc_html_e( 'Original', 'bandstage' ); ?></span>
				</label>
			</div>
		</div>

		<!-- TITRE -->
		<div class="bss-field-group">
			<label class="bss-label" for="bss-title">
				<?php esc_html_e( 'Titre de la chanson', 'bandstage' ); ?>
				<span class="bss-required">*</span>
			</label>
			<input type="text" id="bss-title" class="bss-input"
			       value="<?php echo esc_attr( $title ); ?>"
			       placeholder="<?php esc_attr_e( 'Ex : Voodoo Child', 'bandstage' ); ?>"
			       maxlength="120">
		</div>

		<!-- ARTISTE ORIGINAL (affiché uniquement si reprise) -->
		<div class="bss-field-group" id="bss-artiste-group" <?php echo 'original' === $type ? 'style="display:none"' : ''; ?>>
			<label class="bss-label" for="bss-artiste">
				<?php esc_html_e( 'Artiste original', 'bandstage' ); ?>
			</label>
			<input type="text" id="bss-artiste" class="bss-input"
			       value="<?php echo esc_attr( $artiste ); ?>"
			       placeholder="<?php esc_attr_e( 'Ex : Jimi Hendrix', 'bandstage' ); ?>"
			       maxlength="100">
		</div>

		<!-- ANNÉE -->
		<div class="bss-field-group" id="bss-annee-group" <?php echo 'original' === $type ? 'style="display:none"' : ''; ?>>
			<label class="bss-label" for="bss-annee">
				<?php esc_html_e( 'Année (original)', 'bandstage' ); ?>
				<span class="bss-label-hint"><?php esc_html_e( 'Optionnel', 'bandstage' ); ?></span>
			</label>
			<input type="number" id="bss-annee" class="bss-input"
			       value="<?php echo esc_attr( $annee ); ?>"
			       placeholder="1970" min="1900" max="2099" inputmode="numeric"
			       style="max-width:140px">
		</div>

		<!-- NOTES -->
		<div class="bss-field-group">
			<label class="bss-label" for="bss-notes">
				<?php esc_html_e( 'Notes', 'bandstage' ); ?>
				<span class="bss-label-hint"><?php esc_html_e( 'Optionnel', 'bandstage' ); ?></span>
			</label>
			<textarea id="bss-notes" class="bss-textarea bss-textarea--sm" rows="3"
			          placeholder="<?php esc_attr_e( 'Remarques, arrangement particulier…', 'bandstage' ); ?>"><?php echo esc_textarea( $notes ); ?></textarea>
		</div>

	</div>

	<div class="bss-action-bar">
		<?php if ( $is_edit ) : ?>
		<button type="button" class="bss-btn bss-btn--danger bss-btn--sm" id="bss-delete-titre">
			<?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
		</button>
		<?php endif; ?>
		<button type="button" class="bss-btn bss-btn--gold bss-btn--lg" id="bss-save-titre">
			<?php $is_edit ? esc_html_e( 'Enregistrer', 'bandstage' ) : esc_html_e( 'Ajouter au répertoire', 'bandstage' ); ?>
		</button>
	</div>

	<div class="bss-toast" id="bss-toast" role="alert" aria-live="polite"></div>

</div>

<script>
// Affiche/masque artiste+année selon le type choisi.
document.querySelectorAll('input[name="bss-type"]').forEach(function(r){
	r.addEventListener('change', function(){
		var isOrig = this.value === 'original';
		document.getElementById('bss-artiste-group').style.display = isOrig ? 'none' : '';
		document.getElementById('bss-annee-group').style.display   = isOrig ? 'none' : '';
	});
});
</script>
