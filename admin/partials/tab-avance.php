<?php defined( 'ABSPATH' ) || exit; ?>
<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🎨 <?php esc_html_e( 'CSS personnalisé', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_custom_css"><?php esc_html_e( 'CSS additionnel', 'bandstage' ); ?></label></th>
			<td>
				<textarea id="bs_custom_css" name="bs_custom_css" rows="12"
				          class="large-text code"
				          style="font-family:monospace;font-size:12px"
				          placeholder="/* Vos règles CSS personnalisées */"><?php
					echo esc_textarea( (string) get_option( 'bs_custom_css', '' ) );
				?></textarea>
				<p class="description">
					<?php esc_html_e( 'Injecté dans la page après le CSS du plugin. Utilisez les variables CSS disponibles :', 'bandstage' ); ?>
					<code>--bs-accent</code>, <code>--bs-text</code>, <code>--bs-bg-start</code>, <code>--bs-bg-end</code>, <code>--bs-radius</code>
				</p>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🐛 <?php esc_html_e( 'Débogage', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Mode debug', 'bandstage' ); ?></th>
			<td>
				<label class="bs-toggle-wrap">
					<input type="hidden"   name="bs_debug_mode" value="0">
					<input type="checkbox" name="bs_debug_mode" value="1" class="bs-toggle-input"
					       <?php checked( (bool) get_option( 'bs_debug_mode', false ) ); ?>>
					<span class="bs-toggle-track"></span>
					<span class="bs-toggle-label"><?php esc_html_e( 'Activer les logs PHP dans debug.log', 'bandstage' ); ?></span>
				</label>
				<p class="description" style="color:#c00">
					<?php esc_html_e( 'À désactiver en production. Nécessite WP_DEBUG_LOG = true dans wp-config.php.', 'bandstage' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Informations système', 'bandstage' ); ?></th>
			<td>
				<table style="font-size:12px;border-collapse:collapse">
					<?php
					$infos = array(
						'Plugin'           => BANDSTAGE_VERSION,
						'WordPress'        => get_bloginfo( 'version' ),
						'PHP'              => PHP_VERSION,
						'Table messages'   => $GLOBALS['wpdb']->prefix . 'bandstage_messages',
						'DB version'       => (string) get_option( 'bs_db_version', '—' ),
					);
					foreach ( $infos as $label => $val ) :
					?>
					<tr>
						<td style="padding:3px 12px 3px 0;color:#666;white-space:nowrap"><?php echo esc_html( $label ); ?></td>
						<td style="padding:3px 0"><code><?php echo esc_html( $val ); ?></code></td>
					</tr>
					<?php endforeach; ?>
				</table>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">💾 <?php esc_html_e( 'Import / Export des réglages', 'bandstage' ); ?></span></div>
	<div class="bs-section__body" style="padding:16px">
		<div class="bs-io-wrap">
			<div class="bs-io-block">
				<h4><?php esc_html_e( 'Exporter', 'bandstage' ); ?></h4>
				<p style="font-size:12px;color:#666"><?php esc_html_e( 'Télécharge un fichier JSON avec tous les réglages actuels (sans les données membres ni les messages).', 'bandstage' ); ?></p>
				<button type="button" id="bs-export-btn" class="button button-secondary">
					⬇ <?php esc_html_e( 'Exporter les réglages', 'bandstage' ); ?>
				</button>
				<div id="bs-export-notice" class="bs-notice" style="display:none"></div>
			</div>
			<div class="bs-io-block">
				<h4><?php esc_html_e( 'Importer', 'bandstage' ); ?></h4>
				<p style="font-size:12px;color:#666"><?php esc_html_e( 'Restaure les réglages depuis un fichier JSON exporté précédemment. Écrase les valeurs actuelles.', 'bandstage' ); ?></p>
				<input type="file" id="bs-import-file" accept=".json" style="margin-bottom:8px;display:block">
				<button type="button" id="bs-import-btn" class="button button-secondary">
					⬆ <?php esc_html_e( 'Importer', 'bandstage' ); ?>
				</button>
				<div id="bs-import-notice" class="bs-notice" style="display:none"></div>
			</div>
		</div>
	</div>
</div>

<div class="bs-section" style="border-color:#e24b4a">
	<div class="bs-section__head" style="background:#fff5f5">
		<span class="bs-section__title" style="color:#a32d2d">⚠️ <?php esc_html_e( 'Zone dangereuse', 'bandstage' ); ?></span>
	</div>
	<div class="bs-section__body" style="padding:16px">
		<p style="font-size:13px;color:#666;margin:0 0 12px">
			<?php esc_html_e( 'Ces actions sont irréversibles. Les données supprimées ne peuvent pas être récupérées.', 'bandstage' ); ?>
		</p>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=bandstage&bs_action=flush_cache' ), 'bs_flush_cache' ) ); ?>"
		   class="button button-secondary">
			🗑 <?php esc_html_e( 'Vider le cache objet', 'bandstage' ); ?>
		</a>
	</div>
</div>
