<?php
/**
 * Onglet Admin — Gestion des types de partenaires.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

// Traitement des actions (ajout / suppression / renommage de termes).
// Ces actions utilisent les fonctions WP de taxonomie directement —
// elles ne passent pas par la Settings API, d'où le traitement ici.
$action_msg  = '';
$action_type = '';

if ( isset( $_POST['bs_term_action'] ) && check_admin_referer( 'bs_manage_terms' ) ) {

	$term_action = sanitize_key( $_POST['bs_term_action'] );

	// Ajouter un terme.
	if ( 'add' === $term_action ) {
		$new_name = sanitize_text_field( wp_unslash( $_POST['bs_new_term_name'] ?? '' ) );
		$new_icon = sanitize_text_field( wp_unslash( $_POST['bs_new_term_icon'] ?? '' ) );

		if ( $new_name ) {
			$result = wp_insert_term(
				$new_name,
				'bs_type_partenaire',
				array( 'slug' => sanitize_title( $new_name ) )
			);
			if ( ! is_wp_error( $result ) ) {
				if ( $new_icon ) {
					update_term_meta( $result['term_id'], 'bs_term_icon', $new_icon );
				}
				$action_msg  = __( 'Type ajouté avec succès.', 'bandstage' );
				$action_type = 'ok';
			} else {
				$action_msg  = $result->get_error_message();
				$action_type = 'err';
			}
		}
	}

	// Supprimer un terme.
	if ( 'delete' === $term_action ) {
		$term_id = absint( $_POST['bs_term_id'] ?? 0 );
		if ( $term_id ) {
			$deleted = wp_delete_term( $term_id, 'bs_type_partenaire' );
			if ( $deleted && ! is_wp_error( $deleted ) ) {
				$action_msg  = __( 'Type supprimé.', 'bandstage' );
				$action_type = 'ok';
			} else {
				$action_msg  = __( 'Impossible de supprimer ce type.', 'bandstage' );
				$action_type = 'err';
			}
		}
	}

	// Renommer un terme.
	if ( 'rename' === $term_action ) {
		$term_id      = absint( $_POST['bs_term_id'] ?? 0 );
		$new_name     = sanitize_text_field( wp_unslash( $_POST['bs_term_new_name'] ?? '' ) );
		$new_icon     = sanitize_text_field( wp_unslash( $_POST['bs_term_new_icon'] ?? '' ) );
		if ( $term_id && $new_name ) {
			$result = wp_update_term( $term_id, 'bs_type_partenaire', array( 'name' => $new_name ) );
			if ( ! is_wp_error( $result ) ) {
				update_term_meta( $term_id, 'bs_term_icon', $new_icon );
				$action_msg  = __( 'Type mis à jour.', 'bandstage' );
				$action_type = 'ok';
			}
		}
	}
}

// Récupère tous les types existants.
$terms = get_terms( array(
	'taxonomy'   => 'bs_type_partenaire',
	'hide_empty' => false,
	'orderby'    => 'name',
	'order'      => 'ASC',
) );

// Icônes disponibles (dashicons + emoji pour mobile).
$icons = array(
	''         => '—',
	'🎸'       => '🎸 Instrument',
	'🏪'       => '🏪 Magasin',
	'🔨'       => '🔨 Artisan',
	'🎭'       => '🎭 Salle',
	'🏛️'       => '🏛️ Institution',
	'📻'       => '📻 Média',
	'🍺'       => '🍺 Bar/Café',
	'🎓'       => '🎓 École',
	'🌐'       => '🌐 Web',
	'📦'       => '📦 Autre',
);
?>

<?php if ( $action_msg ) : ?>
<div class="notice notice-<?php echo 'ok' === $action_type ? 'success' : 'error'; ?> is-dismissible" style="margin-bottom:16px">
	<p><?php echo esc_html( $action_msg ); ?></p>
</div>
<?php endif; ?>

<!-- ================================================================
     SECTION : Types de partenaires existants
     ================================================================ -->
<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">
			🤝 <?php esc_html_e( 'Types de partenaires', 'bandstage' ); ?>
			<span style="font-weight:400;color:#999;font-size:12px;margin-left:6px">
				(<?php echo esc_html( (string) ( is_array( $terms ) ? count( $terms ) : 0 ) ); ?>)
			</span>
		</span>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=bs_partenaire' ) ); ?>"
		   class="button button-small">
			<?php esc_html_e( 'Voir tous les partenaires →', 'bandstage' ); ?>
		</a>
	</div>
	<div class="bs-section__body">

	<?php if ( empty( $terms ) || is_wp_error( $terms ) ) : ?>
		<p style="padding:16px 18px;color:#999;font-size:13px">
			<?php esc_html_e( 'Aucun type défini. Ajoutez-en un ci-dessous.', 'bandstage' ); ?>
		</p>
	<?php else : ?>
	<table class="wp-list-table widefat fixed striped" style="margin:0;border:none;box-shadow:none">
		<thead>
			<tr>
				<th style="width:36px"></th>
				<th><?php esc_html_e( 'Nom du type', 'bandstage' ); ?></th>
				<th style="width:130px"><?php esc_html_e( 'Slug', 'bandstage' ); ?></th>
				<th style="width:80px;text-align:center"><?php esc_html_e( 'Partenaires', 'bandstage' ); ?></th>
				<th style="width:220px"><?php esc_html_e( 'Actions', 'bandstage' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $terms as $term ) :
			$icon     = (string) get_term_meta( $term->term_id, 'bs_term_icon', true );
			$edit_url = admin_url( 'edit-tags.php?action=edit&taxonomy=bs_type_partenaire&tag_ID=' . $term->term_id );
			$list_url = admin_url( 'edit.php?post_type=bs_partenaire&bs_type_partenaire=' . $term->slug );
		?>
		<tr id="bs-term-row-<?php echo esc_attr( (string) $term->term_id ); ?>">
			<td style="font-size:18px;text-align:center"><?php echo esc_html( $icon ?: '📦' ); ?></td>
			<td>
				<strong><?php echo esc_html( $term->name ); ?></strong>

				<!-- Formulaire de renommage (inline, masqué par défaut) -->
				<div class="bs-term-edit" id="bs-edit-<?php echo esc_attr( (string) $term->term_id ); ?>"
				     style="display:none;margin-top:8px">
					<form method="post">
						<?php wp_nonce_field( 'bs_manage_terms' ); ?>
						<input type="hidden" name="bs_term_action" value="rename">
						<input type="hidden" name="bs_term_id" value="<?php echo esc_attr( (string) $term->term_id ); ?>">
						<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
							<input type="text" name="bs_term_new_name"
							       value="<?php echo esc_attr( $term->name ); ?>"
							       class="regular-text" style="width:200px" required>
							<select name="bs_term_new_icon" style="height:32px">
								<?php foreach ( $icons as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>"
								        <?php selected( $icon, $val ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
								<?php endforeach; ?>
							</select>
							<button type="submit" class="button button-primary button-small">
								<?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
							</button>
							<button type="button" class="button button-small"
							        onclick="document.getElementById('bs-edit-<?php echo esc_attr( (string) $term->term_id ); ?>').style.display='none'">
								<?php esc_html_e( 'Annuler', 'bandstage' ); ?>
							</button>
						</div>
					</form>
				</div>
			</td>
			<td><code style="font-size:11px"><?php echo esc_html( $term->slug ); ?></code></td>
			<td style="text-align:center">
				<a href="<?php echo esc_url( $list_url ); ?>" style="font-weight:600">
					<?php echo esc_html( (string) $term->count ); ?>
				</a>
			</td>
			<td>
				<div style="display:flex;gap:5px;flex-wrap:wrap">
					<button type="button" class="button button-small"
					        onclick="document.getElementById('bs-edit-<?php echo esc_attr( (string) $term->term_id ); ?>').style.display='block'">
						✏️ <?php esc_html_e( 'Renommer', 'bandstage' ); ?>
					</button>
					<a href="<?php echo esc_url( $list_url ); ?>" class="button button-small">
						📋 <?php esc_html_e( 'Partenaires', 'bandstage' ); ?>
					</a>
					<?php if ( 0 === (int) $term->count ) : ?>
					<form method="post" style="display:inline"
					      onsubmit="return confirm('<?php echo esc_js( __( 'Supprimer ce type ?', 'bandstage' ) ); ?>')">
						<?php wp_nonce_field( 'bs_manage_terms' ); ?>
						<input type="hidden" name="bs_term_action" value="delete">
						<input type="hidden" name="bs_term_id" value="<?php echo esc_attr( (string) $term->term_id ); ?>">
						<button type="submit" class="button button-small"
						        style="border-color:#d9534f;color:#a00">
							✕ <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
						</button>
					</form>
					<?php else : ?>
					<span style="font-size:11px;color:#999;padding:3px 6px" title="<?php esc_attr_e( 'Impossible de supprimer un type ayant des partenaires', 'bandstage' ); ?>">
						🔒
					</span>
					<?php endif; ?>
				</div>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
	</div>
</div>

<!-- ================================================================
     SECTION : Ajouter un nouveau type
     ================================================================ -->
<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">➕ <?php esc_html_e( 'Ajouter un type de partenaire', 'bandstage' ); ?></span>
	</div>
	<div class="bs-section__body">
	<form method="post">
		<?php wp_nonce_field( 'bs_manage_terms' ); ?>
		<input type="hidden" name="bs_term_action" value="add">
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="bs_new_term_name"><?php esc_html_e( 'Nom du type', 'bandstage' ); ?></label></th>
				<td>
					<input type="text" id="bs_new_term_name" name="bs_new_term_name"
					       class="regular-text" required
					       placeholder="<?php esc_attr_e( 'Ex : Studios d\'enregistrement', 'bandstage' ); ?>">
					<p class="description">
						<?php esc_html_e( 'Le slug est généré automatiquement. Les 4 types par défaut (magasins, luthiers, salles, institutionnels) sont créés à l\'activation.', 'bandstage' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th><label for="bs_new_term_icon"><?php esc_html_e( 'Icône', 'bandstage' ); ?></label></th>
				<td>
					<select id="bs_new_term_icon" name="bs_new_term_icon">
						<?php foreach ( $icons as $val => $label ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Affichée dans la liste des partenaires front-end.', 'bandstage' ); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Ajouter ce type', 'bandstage' ), 'secondary', 'submit', false ); ?>
	</form>
	</div>
</div>

<!-- ================================================================
     SECTION : Accès rapide
     ================================================================ -->
<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">🔗 <?php esc_html_e( 'Accès rapide', 'bandstage' ); ?></span>
	</div>
	<div class="bs-section__body" style="padding:14px 18px;display:flex;gap:10px;flex-wrap:wrap">
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=bs_partenaire' ) ); ?>"
		   class="button button-primary">
			➕ <?php esc_html_e( 'Ajouter un partenaire', 'bandstage' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=bs_partenaire' ) ); ?>"
		   class="button button-secondary">
			📋 <?php esc_html_e( 'Tous les partenaires', 'bandstage' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=bs_type_partenaire&post_type=bs_partenaire' ) ); ?>"
		   class="button button-secondary">
			🏷️ <?php esc_html_e( 'Gérer les types (WP natif)', 'bandstage' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=bs_news' ) ); ?>"
		   class="button button-secondary">
			📰 <?php esc_html_e( 'Nouvelle actualité', 'bandstage' ); ?>
		</a>
	</div>
</div>
