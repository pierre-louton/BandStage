<?php
/**
 * Page de modération du Tchache.
 *
 * @package BandStage
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;
$table = $wpdb->prefix . 'bandstage_messages';

// Statut courant et pagination.
$current_status = isset( $_GET['bs_status'] ) ? sanitize_key( $_GET['bs_status'] ) : 'pending'; // phpcs:ignore
$per_page       = 25;
$current_page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) ); // phpcs:ignore
$offset         = ( $current_page - 1 ) * $per_page;

// Comptages par statut.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$counts_raw = $wpdb->get_results(
	"SELECT status, COUNT(*) AS cnt FROM `{$table}` GROUP BY status",
	ARRAY_A
);
$counts = array( 'pending' => 0, 'approved' => 0, 'spam' => 0, 'deleted' => 0 );
foreach ( $counts_raw as $row ) {
	$counts[ $row['status'] ] = (int) $row['cnt'];
}
$counts['all'] = array_sum( $counts );

// Messages de la page courante.
if ( 'all' === $current_status ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$messages = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM `{$table}` ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ),
		ARRAY_A
	);
	$total = $counts['all'];
} else {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$messages = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
			$current_status, $per_page, $offset
		),
		ARRAY_A
	);
	$total = $counts[ $current_status ] ?? 0;
}

$total_pages = $total ? (int) ceil( $total / $per_page ) : 1;

// Labels des statuts.
$status_labels = array(
	'all'      => __( 'Tous', 'bandstage' ),
	'pending'  => __( 'En attente', 'bandstage' ),
	'approved' => __( 'Approuvés', 'bandstage' ),
	'spam'     => __( 'Spam', 'bandstage' ),
	'deleted'  => __( 'Supprimés', 'bandstage' ),
);
$status_colors = array(
	'pending'  => '#f0ad4e',
	'approved' => '#5cb85c',
	'spam'     => '#d9534f',
	'deleted'  => '#999',
);
?>
<div class="wrap bs-admin-wrap">
	<h1>
		💬 <?php esc_html_e( 'Modération Tchache', 'bandstage' ); ?>
		<?php if ( $counts['pending'] > 0 ) : ?>
			<span class="awaiting-mod count-<?php echo esc_attr( (string) $counts['pending'] ); ?>">
				<span class="pending-count"><?php echo esc_html( (string) $counts['pending'] ); ?></span>
			</span>
		<?php endif; ?>
	</h1>

	<?php if ( (bool) get_option( 'bs_tchache_moderation', 'manual' ) === false ) : ?>
	<div class="notice notice-info"><p>
		<?php
		printf(
			/* translators: %s: lien vers réglages */
			esc_html__( 'La modération automatique est activée — les messages sont publiés immédiatement. %s', 'bandstage' ),
			'<a href="' . esc_url( admin_url( 'admin.php?page=bandstage&tab=tchache' ) ) . '">' .
			esc_html__( 'Modifier dans les réglages', 'bandstage' ) . '</a>'
		);
		?>
	</p></div>
	<?php endif; ?>

	<!-- ================================================================
	     ONGLETS DE STATUT
	     ================================================================ -->
	<ul class="subsubsub" style="margin-bottom:8px">
		<?php foreach ( $status_labels as $slug => $label ) : ?>
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=bandstage-tchache&bs_status=' . $slug ) ); ?>"
			   class="<?php echo $current_status === $slug ? 'current' : ''; ?>">
				<?php echo esc_html( $label ); ?>
				<span class="count">(<?php echo esc_html( (string) ( $counts[ $slug ] ?? 0 ) ); ?>)</span>
			</a>
			<?php if ( $slug !== array_key_last( $status_labels ) ) echo ' | '; ?>
		</li>
		<?php endforeach; ?>
	</ul>

	<!-- ================================================================
	     ACTIONS GROUPÉES
	     ================================================================ -->
	<div class="tablenav top" style="margin-bottom:4px">
		<div class="alignleft actions bulkactions">
			<select id="bs-bulk-action">
				<option value=""><?php esc_html_e( 'Actions groupées', 'bandstage' ); ?></option>
				<option value="approved"><?php esc_html_e( 'Approuver la sélection', 'bandstage' ); ?></option>
				<option value="spam"><?php esc_html_e( 'Marquer comme spam', 'bandstage' ); ?></option>
				<option value="deleted"><?php esc_html_e( 'Supprimer', 'bandstage' ); ?></option>
			</select>
			<button type="button" class="button" id="bs-bulk-apply">
				<?php esc_html_e( 'Appliquer', 'bandstage' ); ?>
			</button>
		</div>
		<div class="tablenav-pages" style="float:right;margin-top:4px">
			<?php if ( $total_pages > 1 ) : ?>
			<span class="displaying-num">
				<?php printf( esc_html__( '%d messages', 'bandstage' ), esc_html( (string) $total ) ); ?>
			</span>
			<?php for ( $p = 1; $p <= $total_pages; $p++ ) : ?>
			<a href="<?php echo esc_url( admin_url( "admin.php?page=bandstage-tchache&bs_status={$current_status}&paged={$p}" ) ); ?>"
			   class="button button-small<?php echo $p === $current_page ? ' button-primary' : ''; ?>"
			   style="margin-left:2px">
				<?php echo esc_html( (string) $p ); ?>
			</a>
			<?php endfor; ?>
			<?php endif; ?>
		</div>
		<br class="clear">
	</div>

	<!-- ================================================================
	     TABLE DES MESSAGES
	     ================================================================ -->
	<table class="wp-list-table widefat fixed striped" id="bs-messages-table">
		<thead>
			<tr>
				<td class="check-column"><input type="checkbox" id="bs-check-all"></td>
				<th style="width:160px"><?php esc_html_e( 'Auteur', 'bandstage' ); ?></th>
				<th><?php esc_html_e( 'Message', 'bandstage' ); ?></th>
				<th style="width:120px"><?php esc_html_e( 'Date', 'bandstage' ); ?></th>
				<th style="width:110px"><?php esc_html_e( 'Statut', 'bandstage' ); ?></th>
				<th style="width:200px"><?php esc_html_e( 'Actions', 'bandstage' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $messages ) ) : ?>
			<tr>
				<td colspan="6" style="text-align:center;padding:24px;color:#999">
					<?php esc_html_e( 'Aucun message dans cette catégorie.', 'bandstage' ); ?>
				</td>
			</tr>
		<?php else : ?>
			<?php foreach ( $messages as $msg ) : ?>
			<tr id="bs-msg-<?php echo esc_attr( (string) $msg['id'] ); ?>">
				<td class="check-column">
					<input type="checkbox" class="bs-msg-check" value="<?php echo esc_attr( (string) $msg['id'] ); ?>">
				</td>
				<td>
					<?php
					$avatar = get_avatar( $msg['user_id'] ?: $msg['author_email'], 32, 'identicon', '', array( 'class' => '' ) );
					echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput
					?>
					<strong><?php echo esc_html( $msg['author_name'] ); ?></strong><br>
					<span style="font-size:11px;color:#999">
						<?php echo esc_html( $msg['author_email'] ); ?><br>
						<?php if ( $msg['ip_address'] ) : ?>
						IP: <?php echo esc_html( $msg['ip_address'] ); ?>
						<?php endif; ?>
					</span>
				</td>
				<td>
					<div class="bs-msg-content" style="max-height:80px;overflow:hidden">
						<?php echo nl2br( esc_html( $msg['content'] ) ); ?>
					</div>
					<?php if ( mb_strlen( $msg['content'] ) > 200 ) : ?>
					<a href="#" class="bs-expand-msg" data-id="<?php echo esc_attr( (string) $msg['id'] ); ?>" style="font-size:11px">
						<?php esc_html_e( 'Voir tout', 'bandstage' ); ?>
					</a>
					<?php endif; ?>
				</td>
				<td style="font-size:12px;color:#666">
					<?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $msg['created_at'] ) ) ); ?>
				</td>
				<td>
					<span style="
						display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;
						background:<?php echo esc_attr( $status_colors[ $msg['status'] ] ?? '#ccc' ); ?>22;
						color:<?php echo esc_attr( $status_colors[ $msg['status'] ] ?? '#ccc' ); ?>;
						border:1px solid <?php echo esc_attr( $status_colors[ $msg['status'] ] ?? '#ccc' ); ?>44">
						<?php echo esc_html( $status_labels[ $msg['status'] ] ?? $msg['status'] ); ?>
					</span>
				</td>
				<td>
					<div style="display:flex;gap:4px;flex-wrap:wrap">
					<?php if ( 'approved' !== $msg['status'] ) : ?>
						<button type="button" class="button button-small bs-mod-btn" style="border-color:#5cb85c;color:#3a7a3a"
						        data-id="<?php echo esc_attr( (string) $msg['id'] ); ?>" data-action="approved">
							✓ <?php esc_html_e( 'Approuver', 'bandstage' ); ?>
						</button>
					<?php endif; ?>
					<?php if ( 'spam' !== $msg['status'] ) : ?>
						<button type="button" class="button button-small bs-mod-btn" style="border-color:#f0ad4e;color:#8a6000"
						        data-id="<?php echo esc_attr( (string) $msg['id'] ); ?>" data-action="spam">
							⚑ <?php esc_html_e( 'Spam', 'bandstage' ); ?>
						</button>
					<?php endif; ?>
					<?php if ( 'deleted' !== $msg['status'] ) : ?>
						<button type="button" class="button button-small bs-mod-btn" style="border-color:#d9534f;color:#a00"
						        data-id="<?php echo esc_attr( (string) $msg['id'] ); ?>" data-action="deleted">
							✕ <?php esc_html_e( 'Suppr.', 'bandstage' ); ?>
						</button>
					<?php endif; ?>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

	<p style="color:#999;font-size:12px;margin-top:8px">
		<?php
		printf(
			/* translators: %s: lien wp-config */
			esc_html__( 'Tip : pour activer les logs d\'erreur, ajoutez define(\'WP_DEBUG_LOG\', true) dans votre wp-config.php — le log se trouve dans wp-content/debug.log.', 'bandstage' )
		);
		?>
	</p>
</div>

<script>
(function(){
	var AJAX  = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
	var NONCE = '<?php echo esc_js( wp_create_nonce( BANDSTAGE_NONCE ) ); ?>';

	function moderate( id, action, rowEl ){
		rowEl.style.opacity = '.4';
		rowEl.style.pointerEvents = 'none';

		var fd = new FormData();
		fd.append( 'action',          'bs_moderate_message' );
		fd.append( 'nonce',           NONCE );
		fd.append( 'message_id',      id );
		fd.append( 'moderate_action', action );

		fetch( AJAX, { method: 'POST', body: fd } )
			.then( function(r){ return r.json(); } )
			.then( function(res){
				if( res.success ){
					// Animation et retrait de la ligne
					rowEl.style.transition = 'opacity .3s, transform .3s';
					rowEl.style.transform  = 'translateX(20px)';
					rowEl.style.opacity    = '0';
					setTimeout( function(){ rowEl.remove(); }, 320 );
					// Mise à jour compteur dans le titre
					var badge = document.querySelector('.awaiting-mod .pending-count');
					if( badge && action !== 'approved' ){
						var n = parseInt(badge.textContent, 10) - 1;
						badge.textContent = Math.max(0, n);
					}
				} else {
					rowEl.style.opacity = '1';
					rowEl.style.pointerEvents = '';
					alert( (res.data && res.data.message) || '<?php echo esc_js( __( 'Erreur.', 'bandstage' ) ); ?>' );
				}
			});
	}

	// Boutons individuels
	document.querySelectorAll('.bs-mod-btn').forEach(function(btn){
		btn.addEventListener('click', function(){
			var id  = btn.dataset.id;
			var act = btn.dataset.action;
			var row = document.getElementById('bs-msg-' + id);
			if( act === 'deleted' && !confirm('<?php echo esc_js( __( 'Supprimer ce message ?', 'bandstage' ) ); ?>') ) return;
			moderate(id, act, row);
		});
	});

	// Sélectionner tout
	document.getElementById('bs-check-all').addEventListener('change', function(){
		document.querySelectorAll('.bs-msg-check').forEach(function(c){ c.checked = this.checked; }.bind(this));
	});

	// Actions groupées
	document.getElementById('bs-bulk-apply').addEventListener('click', function(){
		var action = document.getElementById('bs-bulk-action').value;
		if( !action ) return;
		var checked = Array.from(document.querySelectorAll('.bs-msg-check:checked'));
		if( !checked.length ) return;
		if( action === 'deleted' && !confirm('<?php echo esc_js( __( 'Supprimer les messages sélectionnés ?', 'bandstage' ) ); ?>') ) return;
		checked.forEach(function(c){
			var row = document.getElementById('bs-msg-' + c.value);
			if(row) moderate(c.value, action, row);
		});
	});

	// Expand message
	document.querySelectorAll('.bs-expand-msg').forEach(function(a){
		a.addEventListener('click', function(e){
			e.preventDefault();
			var id  = a.dataset.id;
			var div = document.querySelector('#bs-msg-' + id + ' .bs-msg-content');
			if(div) {
				div.style.maxHeight = 'none';
				a.remove();
			}
		});
	});
})();
</script>
