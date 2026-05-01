<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Fond & Dégradé -->
<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🌅 <?php esc_html_e( 'Fond & Dégradé', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Couleurs du dégradé', 'bandstage' ); ?></th>
			<td>
				<div class="bs-color-pair">
					<div class="bs-color-item">
						<label for="bs_bg_color_start"><?php esc_html_e( 'Début', 'bandstage' ); ?></label>
						<input type="text" id="bs_bg_color_start" name="bs_bg_color_start" class="bs-color-picker"
						       value="<?php echo esc_attr( (string) get_option( 'bs_bg_color_start', '#1535A8' ) ); ?>">
					</div>
					<div class="bs-color-item">
						<label for="bs_bg_color_end"><?php esc_html_e( 'Fin', 'bandstage' ); ?></label>
						<input type="text" id="bs_bg_color_end" name="bs_bg_color_end" class="bs-color-picker"
						       value="<?php echo esc_attr( (string) get_option( 'bs_bg_color_end', '#020828' ) ); ?>">
					</div>
					<div class="bs-color-item">
						<label for="bs_bg_angle"><?php esc_html_e( 'Angle (°)', 'bandstage' ); ?></label>
						<input type="number" id="bs_bg_angle" name="bs_bg_angle" min="0" max="360" step="1"
						       value="<?php echo esc_attr( (string) absint( get_option( 'bs_bg_angle', 168 ) ) ); ?>"
						       style="width:80px" class="small-text">
					</div>
				</div>
				<div id="bs-bg-preview" style="margin-top:10px;width:100%;height:48px;border-radius:6px;border:1px solid #ddd;transition:background .3s"></div>
				<p class="description"><?php esc_html_e( 'La prévisualisation se met à jour en temps réel.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_accent_color"><?php esc_html_e( 'Couleur d\'accent', 'bandstage' ); ?></label></th>
			<td>
				<input type="text" id="bs_accent_color" name="bs_accent_color" class="bs-color-picker"
				       value="<?php echo esc_attr( (string) get_option( 'bs_accent_color', '#D4A820' ) ); ?>">
				<p class="description"><?php esc_html_e( 'Utilisée pour le titre, les flèches, les badges et les boutons. Défaut : or #D4A820.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_text_color"><?php esc_html_e( 'Couleur de texte', 'bandstage' ); ?></label></th>
			<td>
				<input type="text" id="bs_text_color" name="bs_text_color" class="bs-color-picker"
				       value="<?php echo esc_attr( (string) get_option( 'bs_text_color', '#F0E6CE' ) ); ?>">
			</td>
		</tr>
	</table>
	</div>
</div>

<!-- Typographie -->
<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🔤 <?php esc_html_e( 'Typographie', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_brand_font"><?php esc_html_e( 'Police du titre', 'bandstage' ); ?></label></th>
			<td>
				<select id="bs_brand_font" name="bs_brand_font">
					<?php
					$brand_fonts = array( 'Playfair Display', 'Cinzel', 'Cormorant Garamond', 'IM Fell English', 'Libre Baskerville', 'Lora', 'Merriweather' );
					$cur = (string) get_option( 'bs_brand_font', 'Playfair Display' );
					foreach ( $brand_fonts as $f ) :
					?>
					<option value="<?php echo esc_attr( $f ); ?>" <?php selected( $cur, $f ); ?>><?php echo esc_html( $f ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Police du nom du groupe en haut de page (Google Fonts, chargée automatiquement).', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_box_font"><?php esc_html_e( 'Police des boîtes', 'bandstage' ); ?></label></th>
			<td>
				<select id="bs_box_font" name="bs_box_font">
					<?php
					$box_fonts = array( 'Oswald', 'Barlow Condensed', 'Bebas Neue', 'Rajdhani', 'Exo 2', 'Russo One', 'Teko' );
					$cur = (string) get_option( 'bs_box_font', 'Oswald' );
					foreach ( $box_fonts as $f ) :
					?>
					<option value="<?php echo esc_attr( $f ); ?>" <?php selected( $cur, $f ); ?>><?php echo esc_html( $f ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Police des titres des boîtes et des boutons (condensée recommandée).', 'bandstage' ); ?></p>
			</td>
		</tr>
	</table>
	</div>
</div>

<!-- Boîtes & Ticker -->
<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">⊞ <?php esc_html_e( 'Boîtes & Ticker', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_box_radius"><?php esc_html_e( 'Arrondi des boîtes (px)', 'bandstage' ); ?></label></th>
			<td>
				<input type="range" id="bs_box_radius" name="bs_box_radius" min="0" max="24" step="1"
				       value="<?php echo esc_attr( (string) absint( get_option( 'bs_box_radius', 8 ) ) ); ?>"
				       oninput="document.getElementById('bs-radius-val').textContent=this.value">
				<span id="bs-radius-val" class="bs-range-val"><?php echo esc_html( (string) absint( get_option( 'bs_box_radius', 8 ) ) ); ?></span> px
				<p class="description"><?php esc_html_e( '0 = angles droits, 24 = très arrondi.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Couleur fond ticker', 'bandstage' ); ?></th>
			<td>
				<div class="bs-color-pair">
					<div class="bs-color-item">
						<label for="bs_ticker_bg_color"><?php esc_html_e( 'Fond', 'bandstage' ); ?></label>
						<input type="text" id="bs_ticker_bg_color" name="bs_ticker_bg_color" class="bs-color-picker"
						       value="<?php echo esc_attr( (string) get_option( 'bs_ticker_bg_color', '#D4A820' ) ); ?>">
					</div>
					<div class="bs-color-item">
						<label for="bs_ticker_text_color"><?php esc_html_e( 'Texte', 'bandstage' ); ?></label>
						<input type="text" id="bs_ticker_text_color" name="bs_ticker_text_color" class="bs-color-picker"
						       value="<?php echo esc_attr( (string) get_option( 'bs_ticker_text_color', '#0A1240' ) ); ?>">
					</div>
				</div>
			</td>
		</tr>
	</table>
	</div>
</div>

<!-- Splashscreen -->
<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">🖼 <?php esc_html_e( 'Splashscreen d\'ouverture', 'bandstage' ); ?></span>
		<label class="bs-toggle-wrap">
			<input type="hidden"   name="bs_splash_enabled" value="0">
			<input type="checkbox" name="bs_splash_enabled" value="1" class="bs-toggle-input"
			       <?php checked( (bool) get_option( 'bs_splash_enabled', true ) ); ?>>
			<span class="bs-toggle-track"></span>
			<span class="bs-toggle-label"><?php esc_html_e( 'Activé', 'bandstage' ); ?></span>
		</label>
	</div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Image du splashscreen', 'bandstage' ); ?></th>
			<td>
				<?php
				$splash_id  = (int) get_option( 'bs_splash_image_id', 0 );
				$splash_url = $splash_id ? wp_get_attachment_image_url( $splash_id, 'medium' ) : '';
				?>
				<div class="bs-media-field">
					<input type="hidden" id="bs_splash_image_id" name="bs_splash_image_id"
					       value="<?php echo esc_attr( (string) $splash_id ); ?>">
					<div class="bs-media-preview <?php echo $splash_url ? '' : 'is-empty'; ?>"
					     id="bs_splash_preview" style="width:120px;height:120px">
						<?php if ( $splash_url ) : ?><img src="<?php echo esc_url( $splash_url ); ?>" alt=""><?php endif; ?>
					</div>
					<div class="bs-media-actions">
						<button type="button" class="button bs-media-select"
						        data-target="bs_splash_image_id" data-preview="bs_splash_preview">
							<?php esc_html_e( 'Choisir une image', 'bandstage' ); ?>
						</button>
						<button type="button" class="button bs-media-remove"
						        data-target="bs_splash_image_id" data-preview="bs_splash_preview">
							<?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
						</button>
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'Recommandé : format portrait, fond transparent ou sombre. L\'image fournie (illustration batterie) est idéale.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_splash_duration"><?php esc_html_e( 'Fermeture automatique (s)', 'bandstage' ); ?></label></th>
			<td>
				<input type="number" id="bs_splash_duration" name="bs_splash_duration"
				       value="<?php echo esc_attr( (string) absint( get_option( 'bs_splash_duration', 4 ) ) ); ?>"
				       min="0" max="30" step="1" class="small-text"> <?php esc_html_e( 'secondes', 'bandstage' ); ?>
				<p class="description"><?php esc_html_e( '0 = fermeture manuelle uniquement (tap sur l\'image ou le bouton).', 'bandstage' ); ?></p>
			</td>
		</tr>
	</table>
	</div>
</div>
