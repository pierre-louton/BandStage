<?php defined( 'ABSPATH' ) || exit; ?>
<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">💬 <?php esc_html_e( 'Mini-forum Tchache', 'bandstage' ); ?></span>
		<label class="bs-toggle-wrap">
			<input type="hidden"   name="bs_tchache_enabled" value="0">
			<input type="checkbox" name="bs_tchache_enabled" value="1" class="bs-toggle-input"
			       id="bs_tchache_enabled" <?php checked( (bool) get_option( 'bs_tchache_enabled', true ) ); ?>>
			<span class="bs-toggle-track"></span>
			<span class="bs-toggle-label"><?php esc_html_e( 'Activé', 'bandstage' ); ?></span>
		</label>
	</div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_tchache_moderation"><?php esc_html_e( 'Modération', 'bandstage' ); ?></label></th>
			<td>
				<select id="bs_tchache_moderation" name="bs_tchache_moderation">
					<option value="manual" <?php selected( get_option( 'bs_tchache_moderation', 'manual' ), 'manual' ); ?>>
						<?php esc_html_e( 'Manuelle — chaque message doit être approuvé', 'bandstage' ); ?>
					</option>
					<option value="auto" <?php selected( get_option( 'bs_tchache_moderation' ), 'auto' ); ?>>
						<?php esc_html_e( 'Automatique — publication immédiate', 'bandstage' ); ?>
					</option>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Accès réservé aux membres', 'bandstage' ); ?></th>
			<td>
				<label class="bs-toggle-wrap">
					<input type="hidden"   name="bs_tchache_members_only" value="0">
					<input type="checkbox" name="bs_tchache_members_only" value="1" class="bs-toggle-input"
					       <?php checked( (bool) get_option( 'bs_tchache_members_only', false ) ); ?>>
					<span class="bs-toggle-track"></span>
					<span class="bs-toggle-label"><?php esc_html_e( 'Connexion requise pour poster', 'bandstage' ); ?></span>
				</label>
			</td>
		</tr>
		<tr>
			<th><label for="bs_tchache_max_per_day"><?php esc_html_e( 'Max messages / 24h / utilisateur', 'bandstage' ); ?></label></th>
			<td>
				<input type="number" id="bs_tchache_max_per_day" name="bs_tchache_max_per_day"
				       min="1" max="200" step="1" class="small-text"
				       value="<?php echo esc_attr( (string) absint( get_option( 'bs_tchache_max_per_day', 10 ) ) ); ?>">
				<p class="description"><?php esc_html_e( 'Limite anti-spam par session/IP. 0 = illimité.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_tchache_min_delay"><?php esc_html_e( 'Délai minimum entre messages (s)', 'bandstage' ); ?></label></th>
			<td>
				<input type="number" id="bs_tchache_min_delay" name="bs_tchache_min_delay"
				       min="0" max="3600" step="5" class="small-text"
				       value="<?php echo esc_attr( (string) absint( get_option( 'bs_tchache_min_delay', 30 ) ) ); ?>">
				<p class="description"><?php esc_html_e( 'En secondes. 30 recommandé. 0 = pas de délai.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_tchache_max_length"><?php esc_html_e( 'Longueur max d\'un message', 'bandstage' ); ?></label></th>
			<td>
				<input type="number" id="bs_tchache_max_length" name="bs_tchache_max_length"
				       min="50" max="2000" step="10" class="small-text"
				       value="<?php echo esc_attr( (string) absint( get_option( 'bs_tchache_max_length', 500 ) ) ); ?>">
				<span><?php esc_html_e( 'caractères', 'bandstage' ); ?></span>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">📧 <?php esc_html_e( 'Notifications modérateur', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Notifier à chaque nouveau message', 'bandstage' ); ?></th>
			<td>
				<label class="bs-toggle-wrap">
					<input type="hidden"   name="bs_tchache_notify_new" value="0">
					<input type="checkbox" name="bs_tchache_notify_new" value="1" class="bs-toggle-input"
					       <?php checked( (bool) get_option( 'bs_tchache_notify_new', true ) ); ?>>
					<span class="bs-toggle-track"></span>
					<span class="bs-toggle-label"><?php esc_html_e( 'Activer les emails de modération', 'bandstage' ); ?></span>
				</label>
			</td>
		</tr>
		<tr>
			<th><label for="bs_tchache_notify_email"><?php esc_html_e( 'E-mail du modérateur', 'bandstage' ); ?></label></th>
			<td>
				<input type="email" id="bs_tchache_notify_email" name="bs_tchache_notify_email"
				       class="regular-text"
				       value="<?php echo esc_attr( (string) get_option( 'bs_tchache_notify_email', get_option( 'admin_email' ) ) ); ?>">
				<p class="description">
					<?php
					printf(
						/* translators: %s: URL de la page de modération */
						esc_html__( 'Lien vers la page de modération : %s', 'bandstage' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=bandstage-tchache' ) ) . '" target="_blank">' .
						esc_html__( 'Modération Tchache', 'bandstage' ) . '</a>'
					);
					?>
				</p>
			</td>
		</tr>
	</table>
	</div>
</div>
