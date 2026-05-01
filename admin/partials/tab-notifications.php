<?php defined( 'ABSPATH' ) || exit; ?>
<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">✉️ <?php esc_html_e( 'Expéditeur des emails', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_notif_from_name"><?php esc_html_e( 'Nom d\'expéditeur', 'bandstage' ); ?></label></th>
			<td>
				<input type="text" id="bs_notif_from_name" name="bs_notif_from_name" class="regular-text"
				       value="<?php echo esc_attr( (string) get_option( 'bs_notif_from_name', get_bloginfo( 'name' ) ) ); ?>">
			</td>
		</tr>
		<tr>
			<th><label for="bs_notif_from_email"><?php esc_html_e( 'E-mail d\'expéditeur', 'bandstage' ); ?></label></th>
			<td>
				<input type="email" id="bs_notif_from_email" name="bs_notif_from_email" class="regular-text"
				       value="<?php echo esc_attr( (string) get_option( 'bs_notif_from_email', get_option( 'admin_email' ) ) ); ?>">
				<p class="description"><?php esc_html_e( 'Utilisé pour tous les emails du plugin (concerts, nouvelles, modération). Sur o2switch, préférez une adresse @votre-domaine.', 'bandstage' ); ?></p>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">🎸 <?php esc_html_e( 'Rappels de concerts', 'bandstage' ); ?></span>
		<label class="bs-toggle-wrap">
			<input type="hidden"   name="bs_notif_concerts_enabled" value="0">
			<input type="checkbox" name="bs_notif_concerts_enabled" value="1" class="bs-toggle-input"
			       <?php checked( (bool) get_option( 'bs_notif_concerts_enabled', true ) ); ?>>
			<span class="bs-toggle-track"></span>
			<span class="bs-toggle-label"><?php esc_html_e( 'Activé', 'bandstage' ); ?></span>
		</label>
	</div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_notif_concerts_days"><?php esc_html_e( 'Jours avant le concert', 'bandstage' ); ?></label></th>
			<td>
				<input type="number" id="bs_notif_concerts_days" name="bs_notif_concerts_days"
				       min="1" max="30" step="1" class="small-text"
				       value="<?php echo esc_attr( (string) absint( get_option( 'bs_notif_concerts_days', 2 ) ) ); ?>">
				<p class="description">
					<?php esc_html_e( 'Un email est envoyé automatiquement chaque matin. Pour définir la date d\'un concert, ajoutez le champ personnalisé ', 'bandstage' ); ?>
					<code>bs_concert_date</code> <?php esc_html_e( '(format YYYY-MM-DD) sur l\'article concerné.', 'bandstage' ); ?>
				</p>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">📰 <?php esc_html_e( 'Newsletter actualités', 'bandstage' ); ?></span>
		<label class="bs-toggle-wrap">
			<input type="hidden"   name="bs_notif_news_enabled" value="0">
			<input type="checkbox" name="bs_notif_news_enabled" value="1" class="bs-toggle-input"
			       <?php checked( (bool) get_option( 'bs_notif_news_enabled', false ) ); ?>>
			<span class="bs-toggle-track"></span>
			<span class="bs-toggle-label"><?php esc_html_e( 'Activé', 'bandstage' ); ?></span>
		</label>
	</div>
	<div class="bs-section__body" style="padding:12px 16px">
		<p style="color:#666;font-size:13px;margin:0">
			<?php esc_html_e( 'Lorsqu\'activée, un email est envoyé aux membres abonnés à chaque publication d\'article. Fonctionne via wp_mail.', 'bandstage' ); ?>
		</p>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🐒 <?php esc_html_e( 'Intégration Mailchimp (optionnel)', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="bs_notif_mailchimp_api_key"><?php esc_html_e( 'Clé API Mailchimp', 'bandstage' ); ?></label></th>
			<td>
				<input type="password" id="bs_notif_mailchimp_api_key" name="bs_notif_mailchimp_api_key"
				       class="regular-text"
				       value="<?php echo esc_attr( (string) get_option( 'bs_notif_mailchimp_api_key', '' ) ); ?>"
				       placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-us1" autocomplete="new-password">
				<p class="description">
					<?php esc_html_e( 'Obtenez votre clé sur ', 'bandstage' ); ?>
					<a href="https://mailchimp.com/help/about-api-keys/" target="_blank">mailchimp.com</a>.
					<?php esc_html_e( 'Laissez vide pour utiliser uniquement wp_mail.', 'bandstage' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><label for="bs_notif_mailchimp_list_id"><?php esc_html_e( 'ID de la liste', 'bandstage' ); ?></label></th>
			<td>
				<input type="text" id="bs_notif_mailchimp_list_id" name="bs_notif_mailchimp_list_id"
				       class="regular-text"
				       value="<?php echo esc_attr( (string) get_option( 'bs_notif_mailchimp_list_id', '' ) ); ?>">
				<p class="description"><?php esc_html_e( 'Visible dans Mailchimp : Audience > Paramètres de l\'audience > ID.', 'bandstage' ); ?></p>
			</td>
		</tr>
	</table>
	</div>
</div>

<div class="bs-section">
	<div class="bs-section__head"><span class="bs-section__title">🎵 <?php esc_html_e( 'Proposer une reprise', 'bandstage' ); ?></span></div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Formulaire actif', 'bandstage' ); ?></th>
			<td>
				<label class="bs-toggle-wrap">
					<input type="hidden"   name="bs_reprise_enabled" value="0">
					<input type="checkbox" name="bs_reprise_enabled" value="1" class="bs-toggle-input"
					       <?php checked( (bool) get_option( 'bs_reprise_enabled', true ) ); ?>>
					<span class="bs-toggle-track"></span>
					<span class="bs-toggle-label"><?php esc_html_e( 'Les membres peuvent proposer des reprises', 'bandstage' ); ?></span>
				</label>
			</td>
		</tr>
		<tr>
			<th><label for="bs_reprise_recipient"><?php esc_html_e( 'Destinataire', 'bandstage' ); ?></label></th>
			<td>
				<input type="email" id="bs_reprise_recipient" name="bs_reprise_recipient" class="regular-text"
				       value="<?php echo esc_attr( (string) get_option( 'bs_reprise_recipient', get_option( 'admin_email' ) ) ); ?>">
			</td>
		</tr>
		<tr>
			<th><label for="bs_reprise_confirm"><?php esc_html_e( 'Message de confirmation', 'bandstage' ); ?></label></th>
			<td>
				<textarea id="bs_reprise_confirm" name="bs_reprise_confirm" rows="3" class="large-text"><?php
					echo esc_textarea( (string) get_option( 'bs_reprise_confirm', __( 'Merci pour votre suggestion ! Le groupe vous lira avec plaisir.', 'bandstage' ) ) );
				?></textarea>
			</td>
		</tr>
	</table>
	</div>
</div>
