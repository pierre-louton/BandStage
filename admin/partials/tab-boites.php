<?php
defined( 'ABSPATH' ) || exit;

$icons = array(
	'groupe'      => array( 'label' => __( 'Groupe',      'bandstage' ), 'dash' => 'dashicons-groups' ),
	'concerts'    => array( 'label' => __( 'Concerts',    'bandstage' ), 'dash' => 'dashicons-calendar-alt' ),
	'references'  => array( 'label' => __( 'Références',  'bandstage' ), 'dash' => 'dashicons-album' ),
	'humeurs'     => array( 'label' => __( 'Humeurs',     'bandstage' ), 'dash' => 'dashicons-edit' ),
	'partenaires' => array( 'label' => __( 'Partenaires', 'bandstage' ), 'dash' => 'dashicons-store' ),
	'tchache'     => array( 'label' => __( 'Tchache',     'bandstage' ), 'dash' => 'dashicons-format-chat' ),
);

for ( $i = 1; $i <= 6; $i++ ) :
	$prefix  = "bs_box_{$i}";
	$enabled = (bool) get_option( "{$prefix}_enabled", true );
	$title   = (string) get_option( "{$prefix}_title",       '' );
	$link    = (string) get_option( "{$prefix}_link",        '' );
	$img_id  = (int)    get_option( "{$prefix}_image_id",    0 );
	$c_start = (string) get_option( "{$prefix}_color_start", '#111122' );
	$c_end   = (string) get_option( "{$prefix}_color_end",   '#333366' );
	$icon    = (string) get_option( "{$prefix}_icon",        '' );
	$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
?>
<div class="bs-section">
	<div class="bs-section__head">
		<span class="bs-section__title">
			<span class="bs-box-num"><?php echo esc_html( (string) $i ); ?></span>
			<?php printf( esc_html__( 'Boîte %d', 'bandstage' ), $i ); ?>
			<?php if ( $title ) echo ' — <em>' . esc_html( $title ) . '</em>'; ?>
		</span>
		<label class="bs-toggle-wrap" title="<?php esc_attr_e( 'Activer / désactiver cette boîte', 'bandstage' ); ?>">
			<input type="hidden"   name="<?php echo esc_attr( "{$prefix}_enabled" ); ?>" value="0">
			<input type="checkbox" name="<?php echo esc_attr( "{$prefix}_enabled" ); ?>" value="1"
			       class="bs-toggle-input" id="<?php echo esc_attr( "{$prefix}_enabled" ); ?>"
			       <?php checked( $enabled ); ?>>
			<span class="bs-toggle-track"></span>
			<span class="bs-toggle-label"><?php esc_html_e( 'Activée', 'bandstage' ); ?></span>
		</label>
	</div>
	<div class="bs-section__body">
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="<?php echo esc_attr( "{$prefix}_title" ); ?>"><?php esc_html_e( 'Titre', 'bandstage' ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( "{$prefix}_title" ); ?>"
			           name="<?php echo esc_attr( "{$prefix}_title" ); ?>"
			           value="<?php echo esc_attr( $title ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="<?php echo esc_attr( "{$prefix}_link" ); ?>"><?php esc_html_e( 'Lien (URL)', 'bandstage' ); ?></label></th>
			<td><input type="url" id="<?php echo esc_attr( "{$prefix}_link" ); ?>"
			           name="<?php echo esc_attr( "{$prefix}_link" ); ?>"
			           value="<?php echo esc_attr( $link ); ?>" class="regular-text"
			           placeholder="https://"></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Image de fond', 'bandstage' ); ?></th>
			<td>
				<div class="bs-media-field">
					<input type="hidden" id="<?php echo esc_attr( "{$prefix}_image_id" ); ?>"
					       name="<?php echo esc_attr( "{$prefix}_image_id" ); ?>"
					       value="<?php echo esc_attr( (string) $img_id ); ?>">
					<div class="bs-media-preview <?php echo $img_url ? '' : 'is-empty'; ?>"
					     id="<?php echo esc_attr( "{$prefix}_preview" ); ?>">
						<?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt=""><?php endif; ?>
					</div>
					<div class="bs-media-actions">
						<button type="button" class="button bs-media-select"
						        data-target="<?php echo esc_attr( "{$prefix}_image_id" ); ?>"
						        data-preview="<?php echo esc_attr( "{$prefix}_preview" ); ?>">
							<?php esc_html_e( 'Choisir une image', 'bandstage' ); ?>
						</button>
						<button type="button" class="button bs-media-remove"
						        data-target="<?php echo esc_attr( "{$prefix}_image_id" ); ?>"
						        data-preview="<?php echo esc_attr( "{$prefix}_preview" ); ?>">
							<?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
						</button>
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'Visible en transparence sous la couleur. Recommandé : 600×400 px.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Couleurs de fond', 'bandstage' ); ?></th>
			<td>
				<div class="bs-color-pair">
					<div class="bs-color-item">
						<label><?php esc_html_e( 'Départ', 'bandstage' ); ?></label>
						<input type="text" name="<?php echo esc_attr( "{$prefix}_color_start" ); ?>"
						       class="bs-color-picker" value="<?php echo esc_attr( $c_start ); ?>">
					</div>
					<div class="bs-color-item">
						<label><?php esc_html_e( 'Arrivée', 'bandstage' ); ?></label>
						<input type="text" name="<?php echo esc_attr( "{$prefix}_color_end" ); ?>"
						       class="bs-color-picker" value="<?php echo esc_attr( $c_end ); ?>">
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'Dégradé affiché si pas d\'image, ou en superposition sur l\'image.', 'bandstage' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Pictogramme', 'bandstage' ); ?></th>
			<td>
				<div class="bs-icon-select">
					<?php foreach ( $icons as $key => $data ) : ?>
					<input type="radio" class="bs-icon-option"
					       id="<?php echo esc_attr( "{$prefix}_icon_{$key}" ); ?>"
					       name="<?php echo esc_attr( "{$prefix}_icon" ); ?>"
					       value="<?php echo esc_attr( $key ); ?>"
					       <?php checked( $icon, $key ); ?>>
					<label for="<?php echo esc_attr( "{$prefix}_icon_{$key}" ); ?>">
						<span class="dashicons <?php echo esc_attr( $data['dash'] ); ?>"></span>
						<?php echo esc_html( $data['label'] ); ?>
					</label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
	</table>
	</div>
</div>
<?php endfor; ?>
