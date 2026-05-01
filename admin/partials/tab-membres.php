<?php defined( 'ABSPATH' ) || exit; ?>
<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">👥 <?php esc_html_e( 'Gestion des membres', 'bandstage' ); ?></span>
		<label class="bs-toggle-wrap">
			<input type="hidden"   name="bs_members_enabled" value="0">
			<input type="checkbox" name="bs_members_enabled" value="1" class="bs-toggle-input"
			       id="bs_members_enabled" <?php checked( (bool) get_option( 'bs_members_enabled', true ) ); ?>>
			<span class="bs-toggle-track"></span>
			<span class="bs-toggle-label"><?php esc_html_e( 'Activé', 'bandstage' ); ?></span>
		</label>
	</div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Approbation manuelle', 'bandstage' ); ?></th>
			<td>
				<label class="bs-toggle-wrap">
					<input type="hidden"   name="bs_members_require_approval" value="0">
					<input type="checkbox" name="bs_members_require_approval" value="1" class="bs-toggle-input"
					       <?php checked( (bool) get_option( 'bs_members_require_approval', false ) ); ?>>
					<span class="bs-toggle-track"></span>
					<span class="bs-toggle-label"><?php esc_html_e( 'Les nouveaux comptes doivent être approuvés par un admin', 'bandstage' ); ?></span>
				</label>
				<p class="description"><?php esc_html_e( 'Si désactivé, l\'inscription est instantanée.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_members_avatar_type"><?php esc_html_e( 'Type d\'avatar', 'bandstage' ); ?></label></th>
			<td>
				<select id="bs_members_avatar_type" name="bs_members_avatar_type">
					<option value="gravatar" <?php selected( get_option( 'bs_members_avatar_type', 'gravatar' ), 'gravatar' ); ?>>
						<?php esc_html_e( 'Gravatar (basé sur l\'e-mail)', 'bandstage' ); ?>
					</option>
					<option value="initials" <?php selected( get_option( 'bs_members_avatar_type' ), 'initials' ); ?>>
						<?php esc_html_e( 'Initiales (généré automatiquement)', 'bandstage' ); ?>
					</option>
					<option value="upload" <?php selected( get_option( 'bs_members_avatar_type' ), 'upload' ); ?>>
						<?php esc_html_e( 'Upload — l\'utilisateur choisit sa photo', 'bandstage' ); ?>
					</option>
				</select>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">📋 <?php esc_html_e( 'Champs du profil', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Champs affichés', 'bandstage' ); ?></th>
			<td>
				<?php
				$fields = array(
					'bs_members_show_bio'        => __( 'Biographie courte', 'bandstage' ),
					'bs_members_show_instrument' => __( 'Instrument pratiqué', 'bandstage' ),
					'bs_members_show_location'   => __( 'Ville / Région', 'bandstage' ),
				);
				foreach ( $fields as $opt => $label ) :
				?>
				<label style="display:block;margin-bottom:8px" class="bs-toggle-wrap">
					<input type="hidden"   name="<?php echo esc_attr( $opt ); ?>" value="0">
					<input type="checkbox" name="<?php echo esc_attr( $opt ); ?>" value="1" class="bs-toggle-input"
					       <?php checked( (bool) get_option( $opt, true ) ); ?>>
					<span class="bs-toggle-track"></span>
					<span class="bs-toggle-label"><?php echo esc_html( $label ); ?></span>
				</label>
				<?php endforeach; ?>
				<p class="description"><?php esc_html_e( 'Ces champs apparaissent dans le formulaire de profil du panneau "Mon Compte".', 'bandstage' ); ?></p>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🔗 <?php esc_html_e( 'Accès rapide', 'bandstage' ); ?></span></div>
	<div class="bs-section__body" style="padding:14px 16px">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=bandstage-members' ) ); ?>" class="button button-secondary">
			👥 <?php esc_html_e( 'Voir la liste des membres', 'bandstage' ); ?>
		</a>
		&nbsp;
		<a href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>" class="button button-secondary" target="_blank">
			<?php esc_html_e( 'Gestion utilisateurs WP →', 'bandstage' ); ?>
		</a>
	</div>
</div>
