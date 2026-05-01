<?php
/**
 * Onglet Admin — Références (influences + répertoire).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$influences_raw   = (string) get_option( 'bs_influences', '[]' );
$influences       = json_decode( $influences_raw, true );
if ( ! is_array( $influences ) ) $influences = array();

$influences_label = (string) get_option( 'bs_influences_label', 'Nos influences' );
$repertoire_label = (string) get_option( 'bs_repertoire_label', 'Notre répertoire' );
?>

<!-- Labels de section -->
<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🏷️ <?php esc_html_e( 'Titres des sections', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_influences_label"><?php esc_html_e( 'Titre section influences', 'bandstage' ); ?></label></th>
			<td><input type="text" id="bs_influences_label" name="bs_influences_label"
			           value="<?php echo esc_attr( $influences_label ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="bs_repertoire_label"><?php esc_html_e( 'Titre section répertoire', 'bandstage' ); ?></label></th>
			<td><input type="text" id="bs_repertoire_label" name="bs_repertoire_label"
			           value="<?php echo esc_attr( $repertoire_label ); ?>" class="regular-text"></td>
		</tr>
	</table>
	</div>
</div>

<!-- Influences -->
<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">🎵 <?php esc_html_e( 'Influences musicales', 'bandstage' ); ?></span>
		<button type="button" class="button button-secondary" id="bs-add-influence">
			+ <?php esc_html_e( 'Ajouter', 'bandstage' ); ?>
		</button>
	</div>
	<div class="bs-section__body">
		<p style="padding:10px 18px 0;font-size:12px;color:#777;margin:0">
			<?php esc_html_e( 'Artistes, groupes ou genres qui ont inspiré votre musique.', 'bandstage' ); ?>
		</p>
		<div id="bs-influences-list" style="padding:12px 18px">
			<?php foreach ( $influences as $idx => $inf ) : ?>
			<div class="bs-influence-row" style="display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;padding:10px;background:#f9f9f9;border-radius:6px;border:1px solid #e0e0e0">
				<div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:8px">
					<input type="text" name="bs_influence_name[]"
					       value="<?php echo esc_attr( $inf['name'] ?? '' ); ?>"
					       placeholder="<?php esc_attr_e( 'Artiste / Groupe *', 'bandstage' ); ?>"
					       class="regular-text" style="width:100%">
					<input type="text" name="bs_influence_genre[]"
					       value="<?php echo esc_attr( $inf['genre'] ?? '' ); ?>"
					       placeholder="<?php esc_attr_e( 'Genre (Blues, Rock…)', 'bandstage' ); ?>"
					       class="regular-text" style="width:100%">
					<input type="text" name="bs_influence_comment[]"
					       value="<?php echo esc_attr( $inf['comment'] ?? '' ); ?>"
					       placeholder="<?php esc_attr_e( 'Commentaire court', 'bandstage' ); ?>"
					       class="regular-text" style="width:100%;grid-column:span 2">
					<input type="url" name="bs_influence_url[]"
					       value="<?php echo esc_attr( $inf['url'] ?? '' ); ?>"
					       placeholder="https://… (optionnel)"
					       class="regular-text" style="width:100%;grid-column:span 2">
				</div>
				<button type="button" class="button bs-remove-influence" style="flex-shrink:0;color:#d00;border-color:#d00" title="<?php esc_attr_e( 'Supprimer', 'bandstage' ); ?>">✕</button>
			</div>
			<?php endforeach; ?>
		</div>
		<!-- Champ JSON caché mis à jour avant soumission -->
		<input type="hidden" name="bs_influences" id="bs_influences_json" value="<?php echo esc_attr( $influences_raw ); ?>">
	</div>
</div>

<!-- Répertoire -->
<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🎸 <?php esc_html_e( 'Répertoire', 'bandstage' ); ?></span></div>
	<div class="bs-section__body" style="padding:14px 18px">
		<p style="font-size:13px;color:#555;margin:0 0 12px">
			<?php esc_html_e( 'Les titres sont gérés depuis la liste dédiée dans le menu BandStage.', 'bandstage' ); ?>
		</p>
		<div style="display:flex;gap:10px;flex-wrap:wrap">
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=bs_titre' ) ); ?>" class="button button-primary">
				+ <?php esc_html_e( 'Ajouter un titre', 'bandstage' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=bs_titre' ) ); ?>" class="button button-secondary">
				📋 <?php esc_html_e( 'Voir le répertoire', 'bandstage' ); ?>
			</a>
		</div>
		<p style="font-size:12px;color:#999;margin:10px 0 0">
			<?php
			$count = wp_count_posts( 'bs_titre' )->publish ?? 0;
			printf( esc_html__( '%d titre(s) dans le répertoire.', 'bandstage' ), (int) $count );
			?>
		</p>
	</div>
</div>

<script>
(function(){
	var list   = document.getElementById('bs-influences-list');
	var addBtn = document.getElementById('bs-add-influence');
	var json   = document.getElementById('bs_influences_json');

	// Template d'une ligne vide
	function newRow() {
		var row = document.createElement('div');
		row.className = 'bs-influence-row';
		row.style.cssText = 'display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;padding:10px;background:#f9f9f9;border-radius:6px;border:1px solid #e0e0e0';
		row.innerHTML =
			'<div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:8px">' +
			'<input type="text" name="bs_influence_name[]"    placeholder="Artiste / Groupe *"   class="regular-text" style="width:100%">' +
			'<input type="text" name="bs_influence_genre[]"   placeholder="Genre (Blues, Rock…)" class="regular-text" style="width:100%">' +
			'<input type="text" name="bs_influence_comment[]" placeholder="Commentaire court"    class="regular-text" style="width:100%;grid-column:span 2">' +
			'<input type="url"  name="bs_influence_url[]"     placeholder="https://… (optionnel)" class="regular-text" style="width:100%;grid-column:span 2">' +
			'</div>' +
			'<button type="button" class="button bs-remove-influence" style="flex-shrink:0;color:#d00;border-color:#d00">✕</button>';
		return row;
	}

	if (addBtn) addBtn.addEventListener('click', function(){ list.appendChild(newRow()); });

	document.addEventListener('click', function(e){
		if (e.target && e.target.classList.contains('bs-remove-influence')) {
			e.target.closest('.bs-influence-row').remove();
		}
	});

	// Avant soumission : sérialise les lignes en JSON dans le champ caché
	var form = document.querySelector('.bs-settings-form');
	if (form) form.addEventListener('submit', function(){
		var rows  = list.querySelectorAll('.bs-influence-row');
		var data  = [];
		rows.forEach(function(row){
			var inputs = row.querySelectorAll('input');
			var name   = inputs[0] ? inputs[0].value.trim() : '';
			if (!name) return;
			data.push({
				name:    name,
				genre:   inputs[1] ? inputs[1].value.trim() : '',
				comment: inputs[2] ? inputs[2].value.trim() : '',
				url:     inputs[3] ? inputs[3].value.trim() : '',
			});
		});
		json.value = JSON.stringify(data);
	});
})();
</script>
