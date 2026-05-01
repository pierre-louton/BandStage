<?php
/**
 * Onglet Admin — Le Groupe.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🎸 <?php esc_html_e( 'Identité du groupe', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_band_name"><?php esc_html_e( 'Nom du groupe', 'bandstage' ); ?></label></th>
			<td>
				<input type="text" id="bs_band_name" name="bs_band_name"
				       value="<?php echo esc_attr( (string) get_option( 'bs_band_name', 'Mon Groupe' ) ); ?>"
				       class="regular-text">
				<p class="description">
					<?php esc_html_e( 'Prévisualisation : ', 'bandstage' ); ?>
					<strong id="bs-name-preview"><?php echo esc_html( (string) get_option( 'bs_band_name', 'Mon Groupe' ) ); ?></strong>
				</p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_band_tagline"><?php esc_html_e( 'Tagline', 'bandstage' ); ?></label></th>
			<td>
				<input type="text" id="bs_band_tagline" name="bs_band_tagline"
				       value="<?php echo esc_attr( (string) get_option( 'bs_band_tagline', 'Rock · Blues · Soul' ) ); ?>"
				       class="regular-text" placeholder="Rock · Blues · Soul">
				<p class="description"><?php esc_html_e( 'Affiché sous le nom. Séparez les genres avec ·', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_band_founded"><?php esc_html_e( 'Année de création', 'bandstage' ); ?></label></th>
			<td>
				<input type="number" id="bs_band_founded" name="bs_band_founded"
				       value="<?php echo esc_attr( (string) get_option( 'bs_band_founded', '' ) ); ?>"
				       class="small-text" min="1900" max="2099" placeholder="2010">
			</td>
		</tr>
		<tr>
			<th><label for="bs_band_members_label"><?php esc_html_e( 'Titre section membres', 'bandstage' ); ?></label></th>
			<td>
				<input type="text" id="bs_band_members_label" name="bs_band_members_label"
				       value="<?php echo esc_attr( (string) get_option( 'bs_band_members_label', 'Les musiciens' ) ); ?>"
				       class="regular-text">
				<p class="description"><?php esc_html_e( 'Ex : "Les musiciens", "La formation", "Le crew"', 'bandstage' ); ?></p>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">📝 <?php esc_html_e( 'Présentation', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_band_bio"><?php esc_html_e( 'Biographie', 'bandstage' ); ?></label></th>
			<td>
				<textarea id="bs_band_bio" name="bs_band_bio" rows="8" class="large-text"
				          placeholder="<?php esc_attr_e( 'Présentez votre groupe : histoire, style musical, influences, projets…', 'bandstage' ); ?>"><?php
					echo esc_textarea( (string) get_option( 'bs_band_bio', '' ) );
				?></textarea>
				<p class="description"><?php esc_html_e( 'Affiché sur la page "Le Groupe". HTML basique accepté (p, strong, em, a, ul).', 'bandstage' ); ?></p>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🖼 <?php esc_html_e( 'Logo du groupe', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Logo / Photo principale', 'bandstage' ); ?></th>
			<td>
				<?php
				$logo_id  = (int) get_option( 'bs_band_logo_id', 0 );
				$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
				?>
				<div class="bs-media-field">
					<input type="hidden" id="bs_band_logo_id" name="bs_band_logo_id" value="<?php echo esc_attr( (string) $logo_id ); ?>">
					<div class="bs-media-preview <?php echo $logo_url ? '' : 'is-empty'; ?>" id="bs_band_logo_preview">
						<?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" alt=""><?php endif; ?>
					</div>
					<div class="bs-media-actions">
						<button type="button" class="button bs-media-select"
						        data-target="bs_band_logo_id" data-preview="bs_band_logo_preview">
							<?php esc_html_e( 'Sélectionner', 'bandstage' ); ?>
						</button>
						<button type="button" class="button bs-media-remove"
						        data-target="bs_band_logo_id" data-preview="bs_band_logo_preview">
							<?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
						</button>
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'PNG transparent recommandé, 600×600 px min.', 'bandstage' ); ?></p>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">👥 <?php esc_html_e( 'Membres affichés', 'bandstage' ); ?></span></div>
	<div class="bs-section__body" style="padding:14px 18px">
		<p style="font-size:13px;color:#555;margin:0 0 10px">
			<?php esc_html_e( 'Les membres affichés sur la page "Le Groupe" sont les utilisateurs WP ayant le rôle Auteur ou supérieur.', 'bandstage' ); ?>
			<?php esc_html_e( 'Le champ "Instrument" est renseigné dans leur profil (méta ', 'bandstage' ); ?>
			<code>bs_instrument</code>).
		</p>
		<a href="<?php echo esc_url( admin_url( 'users.php?role=author' ) ); ?>" class="button button-secondary" target="_blank">
			<?php esc_html_e( 'Gérer les auteurs WP →', 'bandstage' ); ?>
		</a>
	</div>
</div>
