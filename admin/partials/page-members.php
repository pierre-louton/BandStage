<?php
/**
 * Page de gestion des membres BandStage.
 *
 * @package BandStage
 */

defined( 'ABSPATH' ) || exit;

// Paramètres URL.
$current_filter = isset( $_GET['bs_filter'] ) ? sanitize_key( $_GET['bs_filter'] ) : 'all'; // phpcs:ignore
$per_page       = 25;
$current_page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) ); // phpcs:ignore
$search         = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore

// Requête membres WP qui ont au moins une meta bs_.
// On récupère les subscribers ou tout utilisateur ayant interagi avec BandStage.
$user_query_args = array(
	'role__in'   => array( 'subscriber' ),
	'number'     => $per_page,
	'offset'     => ( $current_page - 1 ) * $per_page,
	'orderby'    => 'registered',
	'order'      => 'DESC',
	'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
		array(
			'key'     => 'bs_notif_concerts',
			'compare' => 'EXISTS',
		),
	),
);

if ( $search ) {
	$user_query_args['search']         = '*' . $search . '*';
	$user_query_args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
}

// Filtre statut d'approbation (user_status : 0=actif, 1=bloqué dans WP).
if ( 'pending' === $current_filter ) {
	$user_query_args['meta_query'][] = array(
		'key'   => 'bs_pending_approval',
		'value' => '1',
	);
} elseif ( 'active' === $current_filter ) {
	$user_query_args['meta_query'][] = array(
		'relation' => 'OR',
		array( 'key' => 'bs_pending_approval', 'compare' => 'NOT EXISTS' ),
		array( 'key' => 'bs_pending_approval', 'value' => '0' ),
	);
}

$user_query    = new WP_User_Query( $user_query_args );
$members       = $user_query->get_results();
$total_members = $user_query->get_total();
$total_pages   = $total_members ? (int) ceil( $total_members / $per_page ) : 1;

// Comptages rapides.
$count_all     = ( new WP_User_Query( array( 'role__in' => array( 'subscriber' ), 'meta_query' => array( array( 'key' => 'bs_notif_concerts', 'compare' => 'EXISTS' ) ), 'count_total' => true, 'number' => 1 ) ) )->get_total();
$count_pending = ( new WP_User_Query( array( 'role__in' => array( 'subscriber' ), 'meta_key' => 'bs_pending_approval', 'meta_value' => '1' ) ) )->get_total();
?>
<div class="wrap bs-admin-wrap">
	<h1>
		👥 <?php esc_html_e( 'Membres BandStage', 'bandstage' ); ?>
		<?php if ( $count_pending > 0 ) : ?>
			<span class="awaiting-mod">
				<span class="pending-count"><?php echo esc_html( (string) $count_pending ); ?></span>
			</span>
		<?php endif; ?>
		<a href="<?php echo esc_url( admin_url( 'user-new.php' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Ajouter un membre', 'bandstage' ); ?>
		</a>
	</h1>

	<!-- ================================================================
	     FILTRE + RECHERCHE
	     ================================================================ -->
	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
		<input type="hidden" name="page" value="bandstage-members">
		<ul class="subsubsub" style="margin-bottom:8px">
			<?php
			$filters = array(
				'all'     => array( 'label' => __( 'Tous', 'bandstage' ),           'count' => $count_all ),
				'active'  => array( 'label' => __( 'Actifs', 'bandstage' ),         'count' => $count_all - $count_pending ),
				'pending' => array( 'label' => __( 'En attente', 'bandstage' ),     'count' => $count_pending ),
			);
			$last = array_key_last( $filters );
			foreach ( $filters as $slug => $data ) :
			?>
			<li>
				<a href="<?php echo esc_url( admin_url( "admin.php?page=bandstage-members&bs_filter={$slug}" ) ); ?>"
				   class="<?php echo $current_filter === $slug ? 'current' : ''; ?>">
					<?php echo esc_html( $data['label'] ); ?>
					<span class="count">(<?php echo esc_html( (string) $data['count'] ); ?>)</span>
				</a>
				<?php if ( $slug !== $last ) echo ' | '; ?>
			</li>
			<?php endforeach; ?>
		</ul>

		<div class="tablenav top" style="margin-bottom:4px">
			<div class="alignleft actions bulkactions">
				<select id="bs-member-bulk-action" name="bs_bulk_action">
					<option value=""><?php esc_html_e( 'Actions groupées', 'bandstage' ); ?></option>
					<option value="approve"><?php esc_html_e( 'Approuver', 'bandstage' ); ?></option>
					<option value="delete"><?php esc_html_e( 'Supprimer le compte WP', 'bandstage' ); ?></option>
				</select>
				<button type="button" class="button" id="bs-member-bulk-apply">
					<?php esc_html_e( 'Appliquer', 'bandstage' ); ?>
				</button>
			</div>
			<div class="alignright">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
				       placeholder="<?php esc_attr_e( 'Rechercher un membre…', 'bandstage' ); ?>"
				       style="width:220px">
				<input type="submit" class="button" value="<?php esc_attr_e( 'Rechercher', 'bandstage' ); ?>">
			</div>
			<br class="clear">
		</div>
	</form>

	<!-- ================================================================
	     TABLE DES MEMBRES
	     ================================================================ -->
	<table class="wp-list-table widefat fixed striped" id="bs-members-table">
		<thead>
			<tr>
				<td class="check-column"><input type="checkbox" id="bs-member-check-all"></td>
				<th style="width:52px"><?php esc_html_e( 'Avatar', 'bandstage' ); ?></th>
				<th><?php esc_html_e( 'Pseudo / E-mail', 'bandstage' ); ?></th>
				<th style="width:130px"><?php esc_html_e( 'Inscrit le', 'bandstage' ); ?></th>
				<th style="width:80px;text-align:center"><?php esc_html_e( 'Concerts', 'bandstage' ); ?></th>
				<th style="width:80px;text-align:center"><?php esc_html_e( 'Actus', 'bandstage' ); ?></th>
				<th style="width:80px;text-align:center"><?php esc_html_e( 'Tchache', 'bandstage' ); ?></th>
				<th style="width:80px;text-align:center"><?php esc_html_e( 'Statut', 'bandstage' ); ?></th>
				<th style="width:160px"><?php esc_html_e( 'Actions', 'bandstage' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $members ) ) : ?>
			<tr>
				<td colspan="9" style="text-align:center;padding:24px;color:#999">
					<?php esc_html_e( 'Aucun membre trouvé.', 'bandstage' ); ?>
				</td>
			</tr>
		<?php else : ?>
			<?php foreach ( $members as $user ) : ?>
			<?php
			$is_pending  = '1' === get_user_meta( $user->ID, 'bs_pending_approval', true );
			$notif_c     = '1' === get_user_meta( $user->ID, 'bs_notif_concerts', true );
			$notif_n     = '1' === get_user_meta( $user->ID, 'bs_notif_news',     true );
			$notif_t     = '1' === get_user_meta( $user->ID, 'bs_notif_tchache',  true );
			$instrument  = (string) get_user_meta( $user->ID, 'bs_instrument', true );
			?>
			<tr id="bs-member-<?php echo esc_attr( (string) $user->ID ); ?>">
				<td class="check-column">
					<input type="checkbox" class="bs-member-check" value="<?php echo esc_attr( (string) $user->ID ); ?>">
				</td>
				<td>
					<?php echo get_avatar( $user->ID, 36, 'identicon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</td>
				<td>
					<strong>
						<a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $user->ID ) ); ?>">
							<?php echo esc_html( $user->display_name ); ?>
						</a>
					</strong>
					<br>
					<span style="color:#666;font-size:12px"><?php echo esc_html( $user->user_email ); ?></span>
					<?php if ( $instrument ) : ?>
					<br><span style="color:#999;font-size:11px">🎸 <?php echo esc_html( $instrument ); ?></span>
					<?php endif; ?>
				</td>
				<td style="font-size:12px;color:#666">
					<?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $user->user_registered ) ) ); ?>
				</td>
				<td style="text-align:center">
					<?php echo $notif_c ? '<span style="color:#5cb85c;font-size:16px">✓</span>' : '<span style="color:#ddd">–</span>'; // phpcs:ignore ?>
				</td>
				<td style="text-align:center">
					<?php echo $notif_n ? '<span style="color:#5cb85c;font-size:16px">✓</span>' : '<span style="color:#ddd">–</span>'; // phpcs:ignore ?>
				</td>
				<td style="text-align:center">
					<?php echo $notif_t ? '<span style="color:#5cb85c;font-size:16px">✓</span>' : '<span style="color:#ddd">–</span>'; // phpcs:ignore ?>
				</td>
				<td style="text-align:center">
					<?php if ( $is_pending ) : ?>
					<span style="background:#f0ad4e22;color:#8a6000;border:1px solid #f0ad4e44;border-radius:12px;font-size:11px;padding:2px 8px;font-weight:600">
						<?php esc_html_e( 'En attente', 'bandstage' ); ?>
					</span>
					<?php else : ?>
					<span style="background:#5cb85c22;color:#2a6a2a;border:1px solid #5cb85c44;border-radius:12px;font-size:11px;padding:2px 8px;font-weight:600">
						<?php esc_html_e( 'Actif', 'bandstage' ); ?>
					</span>
					<?php endif; ?>
				</td>
				<td>
					<div style="display:flex;gap:4px;flex-wrap:wrap">
					<?php if ( $is_pending ) : ?>
						<button type="button" class="button button-small bs-member-approve"
						        data-id="<?php echo esc_attr( (string) $user->ID ); ?>"
						        style="border-color:#5cb85c;color:#2a6a2a">
							✓ <?php esc_html_e( 'Approuver', 'bandstage' ); ?>
						</button>
					<?php endif; ?>
						<a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $user->ID ) ); ?>"
						   class="button button-small">
							<?php esc_html_e( 'Profil WP', 'bandstage' ); ?>
						</a>
						<button type="button" class="button button-small bs-member-delete"
						        data-id="<?php echo esc_attr( (string) $user->ID ); ?>"
						        data-name="<?php echo esc_attr( $user->display_name ); ?>"
						        style="border-color:#d9534f;color:#a00">
							✕
						</button>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

	<!-- Pagination -->
	<?php if ( $total_pages > 1 ) : ?>
	<div class="tablenav bottom">
		<div class="tablenav-pages" style="float:right;margin:8px 0">
			<span class="displaying-num">
				<?php printf( esc_html__( '%d membres', 'bandstage' ), esc_html( (string) $total_members ) ); ?>
			</span>
			<?php for ( $p = 1; $p <= $total_pages; $p++ ) : ?>
			<a href="<?php echo esc_url( admin_url( "admin.php?page=bandstage-members&bs_filter={$current_filter}&paged={$p}" . ( $search ? '&s=' . rawurlencode( $search ) : '' ) ) ); ?>"
			   class="button button-small<?php echo $p === $current_page ? ' button-primary' : ''; ?>"
			   style="margin-left:2px">
				<?php echo esc_html( (string) $p ); ?>
			</a>
			<?php endfor; ?>
		</div>
		<br class="clear">
	</div>
	<?php endif; ?>

	<!-- Résumé infos -->
	<p style="color:#999;font-size:12px;margin-top:12px">
		<?php
		esc_html_e( 'Seuls les abonnés WordPress ayant créé un compte via BandStage apparaissent ici. ', 'bandstage' );
		printf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'users.php' ) ),
			esc_html__( 'Voir tous les utilisateurs WP →', 'bandstage' )
		);
		?>
	</p>
</div>

<script>
(function(){
	var AJAX  = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
	var NONCE = '<?php echo esc_js( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>';

	// Sélectionner tout
	document.getElementById('bs-member-check-all').addEventListener('change', function(){
		document.querySelectorAll('.bs-member-check').forEach(function(c){ c.checked = this.checked; }.bind(this));
	});

	// Approuver un membre
	document.querySelectorAll('.bs-member-approve').forEach(function(btn){
		btn.addEventListener('click', function(){
			var id  = btn.dataset.id;
			var row = document.getElementById('bs-member-' + id);
			var fd  = new FormData();
			fd.append('action',    'bs_approve_member');
			fd.append('nonce',     NONCE);
			fd.append('member_id', id);
			fetch(AJAX, {method:'POST', body:fd})
				.then(function(r){ return r.json(); })
				.then(function(res){
					if(res.success){
						btn.remove();
						var badge = row.querySelector('span[style*="f0ad4e"]');
						if(badge) badge.outerHTML = '<span style="background:#5cb85c22;color:#2a6a2a;border:1px solid #5cb85c44;border-radius:12px;font-size:11px;padding:2px 8px;font-weight:600"><?php echo esc_js( __( 'Actif', 'bandstage' ) ); ?></span>';
					}
				});
		});
	});

	// Supprimer un membre
	document.querySelectorAll('.bs-member-delete').forEach(function(btn){
		btn.addEventListener('click', function(){
			var id   = btn.dataset.id;
			var name = btn.dataset.name;
			if(!confirm('<?php echo esc_js( __( 'Supprimer le compte de', 'bandstage' ) ); ?> "' + name + '" ?')) return;
			var row = document.getElementById('bs-member-' + id);
			var fd  = new FormData();
			fd.append('action',    'bs_delete_member');
			fd.append('nonce',     NONCE);
			fd.append('member_id', id);
			fetch(AJAX, {method:'POST', body:fd})
				.then(function(r){ return r.json(); })
				.then(function(res){
					if(res.success){
						row.style.transition = 'opacity .3s';
						row.style.opacity    = '0';
						setTimeout(function(){ row.remove(); }, 320);
					} else {
						alert((res.data && res.data.message) || '<?php echo esc_js( __( 'Erreur.', 'bandstage' ) ); ?>');
					}
				});
		});
	});

	// Actions groupées
	document.getElementById('bs-member-bulk-apply').addEventListener('click', function(){
		var action  = document.getElementById('bs-member-bulk-action').value;
		if(!action) return;
		var checked = Array.from(document.querySelectorAll('.bs-member-check:checked'));
		if(!checked.length) return;
		if(action === 'delete' && !confirm('<?php echo esc_js( __( 'Supprimer les membres sélectionnés ?', 'bandstage' ) ); ?>')) return;
		var ajax_action = action === 'approve' ? 'bs_approve_member' : 'bs_delete_member';
		checked.forEach(function(c){
			var id  = c.value;
			var row = document.getElementById('bs-member-' + id);
			var fd  = new FormData();
			fd.append('action',    ajax_action);
			fd.append('nonce',     NONCE);
			fd.append('member_id', id);
			fetch(AJAX, {method:'POST', body:fd})
				.then(function(r){ return r.json(); })
				.then(function(res){
					if(res.success && action === 'delete'){
						row.style.transition = 'opacity .3s';
						row.style.opacity    = '0';
						setTimeout(function(){ row.remove(); }, 320);
					}
				});
		});
	});
})();
</script>
