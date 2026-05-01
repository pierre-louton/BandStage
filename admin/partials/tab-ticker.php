<?php defined( 'ABSPATH' ) || exit; ?>
<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">📢 <?php esc_html_e( 'Bandeau défilant (Ticker)', 'bandstage' ); ?></span>
		<label class="bs-toggle-wrap">
			<input type="hidden"   name="bs_ticker_enabled" value="0">
			<input type="checkbox" name="bs_ticker_enabled" value="1" class="bs-toggle-input"
			       id="bs_ticker_enabled" <?php checked( (bool) get_option( 'bs_ticker_enabled', true ) ); ?>>
			<span class="bs-toggle-track"></span>
			<span class="bs-toggle-label"><?php esc_html_e( 'Activé', 'bandstage' ); ?></span>
		</label>
	</div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_ticker_source"><?php esc_html_e( 'Source des messages', 'bandstage' ); ?></label></th>
			<td>
				<select id="bs_ticker_source" name="bs_ticker_source">
					<option value="manual" <?php selected( get_option( 'bs_ticker_source', 'manual' ), 'manual' ); ?>>
						<?php esc_html_e( 'Saisie manuelle', 'bandstage' ); ?>
					</option>
					<option value="bs_news" <?php selected( get_option( 'bs_ticker_source' ), 'bs_news' ); ?>>
						<?php esc_html_e( 'Actualités BandStage (bs_news) — recommandé', 'bandstage' ); ?>
					</option>
					<option value="posts" <?php selected( get_option( 'bs_ticker_source' ), 'posts' ); ?>>
						<?php esc_html_e( 'Articles WordPress (titres)', 'bandstage' ); ?>
					</option>
				</select>
			</td>
		</tr>
		<tr id="bs-ticker-manual-row">
			<th><label for="bs_ticker_items"><?php esc_html_e( 'Messages manuels', 'bandstage' ); ?></label></th>
			<td>
				<textarea id="bs_ticker_items" name="bs_ticker_items" rows="6" class="large-text"
				          placeholder="<?php esc_attr_e( 'Concert · La Cigale, Paris · 15 Mai', 'bandstage' ); ?>"><?php
					echo esc_textarea( (string) get_option( 'bs_ticker_items', '' ) );
				?></textarea>
				<p class="description"><?php esc_html_e( 'Un message par ligne. Séparateurs ★ insérés automatiquement.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr id="bs-ticker-cats-row">
			<th><?php esc_html_e( 'Catégories WP', 'bandstage' ); ?></th>
			<td>
				<?php
				$saved_cats = (array) get_option( 'bs_ticker_categories', array() );
				$categories = get_categories( array( 'hide_empty' => false ) );
				foreach ( $categories as $cat ) :
				?>
				<label style="display:block;margin-bottom:4px">
					<input type="checkbox" name="bs_ticker_categories[]"
					       value="<?php echo esc_attr( (string) $cat->term_id ); ?>"
					       <?php checked( in_array( (string) $cat->term_id, array_map( 'strval', $saved_cats ), true ) ); ?>>
					<?php echo esc_html( $cat->name ); ?>
					<span style="color:#999">(<?php echo esc_html( (string) $cat->count ); ?>)</span>
				</label>
				<?php endforeach; ?>
				<p class="description"><?php esc_html_e( 'Utilisé uniquement si la source est "Articles WordPress".', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_ticker_speed"><?php esc_html_e( 'Vitesse (secondes)', 'bandstage' ); ?></label></th>
			<td>
				<input type="range" id="bs_ticker_speed" name="bs_ticker_speed" min="8" max="60" step="1"
				       value="<?php echo esc_attr( (string) absint( get_option( 'bs_ticker_speed', 24 ) ) ); ?>"
				       oninput="document.getElementById('bs-speed-val').textContent=this.value">
				<span id="bs-speed-val" class="bs-range-val"><?php echo esc_html( (string) absint( get_option( 'bs_ticker_speed', 24 ) ) ); ?></span>s
				<p class="description"><?php esc_html_e( 'Durée d\'un cycle complet. Plus la valeur est élevée, plus le défilement est lent.', 'bandstage' ); ?></p>
			</td>
		</tr>
	</table>
	</div>
</div>
<script>
(function(){
	function updateTickerRows(){
		var source = document.getElementById('bs_ticker_source').value;
		document.getElementById('bs-ticker-manual-row').style.display = source === 'manual' ? '' : 'none';
		document.getElementById('bs-ticker-cats-row').style.display   = source === 'posts'  ? '' : 'none';
	}
	document.getElementById('bs_ticker_source').addEventListener('change', updateTickerRows);
	updateTickerRows();
})();
</script>
