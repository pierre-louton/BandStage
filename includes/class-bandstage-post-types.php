<?php
/**
 * Enregistrement des Custom Post Types et taxonomies BandStage.
 *
 * Post types :
 *   - bs_news       → Actualités (titre → ticker, contenu → Humeurs)
 *   - bs_partenaire → Partenaires (contacts locaux du groupe)
 *
 * Taxonomies :
 *   - bs_type_partenaire → Types de partenaires (configurables en admin)
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_Post_Types
 */
class BandStage_Post_Types {

	/**
	 * Enregistre tous les CPTs et taxonomies.
	 * Hook : init
	 */
	public function register(): void {
		$this->register_news();
		$this->register_partenaire();
		$this->register_concert();
		$this->register_titre();
		$this->register_type_partenaire();
	}

	// -----------------------------------------------------------------------
	// CPT : bs_news (Actualités)
	// -----------------------------------------------------------------------

	private function register_news(): void {
		$labels = array(
			'name'               => __( 'Actualités', 'bandstage' ),
			'singular_name'      => __( 'Actualité', 'bandstage' ),
			'menu_name'          => __( 'Actualités', 'bandstage' ),
			'add_new'            => __( 'Nouvelle actu', 'bandstage' ),
			'add_new_item'       => __( 'Ajouter une actualité', 'bandstage' ),
			'edit_item'          => __( 'Modifier l\'actualité', 'bandstage' ),
			'new_item'           => __( 'Nouvelle actualité', 'bandstage' ),
			'view_item'          => __( 'Voir l\'actualité', 'bandstage' ),
			'search_items'       => __( 'Rechercher', 'bandstage' ),
			'not_found'          => __( 'Aucune actualité trouvée.', 'bandstage' ),
			'not_found_in_trash' => __( 'Aucune actualité dans la corbeille.', 'bandstage' ),
		);

		register_post_type(
			'bs_news',
			array(
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => 'bandstage',     // Apparaît sous le menu BandStage.
				'show_in_rest'        => true,            // Active Gutenberg + app WP mobile.
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'actualites' ),
				'capability_type'     => 'post',
				'has_archive'         => true,
				'hierarchical'        => false,
				'menu_position'       => null,
				'supports'            => array(
					'title',        // → titre dans le ticker
					'editor',       // → contenu dans Humeurs
					'thumbnail',    // → image de la boîte
					'excerpt',      // → résumé dans la liste
					'author',
					'revisions',
				),
				'description'         => __( 'Actualités du groupe. Le titre alimente le ticker automatiquement.', 'bandstage' ),
			)
		);
	}

	// -----------------------------------------------------------------------
	// CPT : bs_partenaire (Partenaires / Contacts)
	// -----------------------------------------------------------------------

	private function register_partenaire(): void {
		$labels = array(
			'name'               => __( 'Partenaires', 'bandstage' ),
			'singular_name'      => __( 'Partenaire', 'bandstage' ),
			'menu_name'          => __( 'Partenaires', 'bandstage' ),
			'add_new'            => __( 'Nouveau partenaire', 'bandstage' ),
			'add_new_item'       => __( 'Ajouter un partenaire', 'bandstage' ),
			'edit_item'          => __( 'Modifier le partenaire', 'bandstage' ),
			'new_item'           => __( 'Nouveau partenaire', 'bandstage' ),
			'view_item'          => __( 'Voir le partenaire', 'bandstage' ),
			'search_items'       => __( 'Rechercher', 'bandstage' ),
			'not_found'          => __( 'Aucun partenaire trouvé.', 'bandstage' ),
			'not_found_in_trash' => __( 'Aucun partenaire dans la corbeille.', 'bandstage' ),
		);

		register_post_type(
			'bs_partenaire',
			array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => 'bandstage',
				'show_in_rest'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'partenaires' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'supports'           => array(
					'title',      // Nom du partenaire
					'editor',     // Description
					'thumbnail',  // Logo
					'custom-fields', // URL, téléphone, adresse via meta boxes
					'revisions',
				),
				'description'        => __( 'Partenaires du groupe : commerces, salles, institutionnels.', 'bandstage' ),
			)
		);
	}

	// -----------------------------------------------------------------------
	// CPT : bs_concert (Dates de concerts)
	// -----------------------------------------------------------------------

	private function register_concert(): void {
		$labels = array(
			'name'               => __( 'Concerts', 'bandstage' ),
			'singular_name'      => __( 'Concert', 'bandstage' ),
			'menu_name'          => __( 'Concerts', 'bandstage' ),
			'add_new'            => __( 'Nouveau concert', 'bandstage' ),
			'add_new_item'       => __( 'Ajouter un concert', 'bandstage' ),
			'edit_item'          => __( 'Modifier le concert', 'bandstage' ),
			'new_item'           => __( 'Nouveau concert', 'bandstage' ),
			'not_found'          => __( 'Aucun concert trouvé.', 'bandstage' ),
			'not_found_in_trash' => __( 'Aucun concert dans la corbeille.', 'bandstage' ),
		);

		register_post_type(
			'bs_concert',
			array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => 'bandstage',
				'show_in_rest'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'concerts' ),
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'supports'           => array( 'title', 'editor', 'thumbnail', 'revisions' ),
				'description'        => __( 'Dates et lieux des concerts du groupe.', 'bandstage' ),
			)
		);
	}

	/**
	 * Ajoute la meta box concert (date, lieu, ville, billets).
	 * Hook : add_meta_boxes
	 */
	public function add_concert_meta_boxes(): void {
		add_meta_box(
			'bs_concert_details',
			__( 'Détails du concert', 'bandstage' ),
			array( $this, 'render_concert_meta_box' ),
			'bs_concert',
			'normal',
			'high'
		);
	}

	/**
	 * Rendu de la meta box concert.
	 *
	 * @param WP_Post $post Article courant.
	 */
	public function render_concert_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'bs_concert_meta', 'bs_concert_nonce' );

		$date    = (string) get_post_meta( $post->ID, 'bs_concert_date',    true );
		$heure   = (string) get_post_meta( $post->ID, 'bs_concert_heure',   true );
		$lieu    = (string) get_post_meta( $post->ID, 'bs_concert_lieu',    true );
		$ville   = (string) get_post_meta( $post->ID, 'bs_concert_ville',   true );
		$adresse = (string) get_post_meta( $post->ID, 'bs_concert_adresse', true );
		$billets = (string) get_post_meta( $post->ID, 'bs_concert_billets', true );
		$gratuit = (bool)   get_post_meta( $post->ID, 'bs_concert_gratuit', true );
		?>
		<table class="form-table" style="margin:0">
			<tr>
				<th style="width:140px"><label for="bs_concert_date"><?php esc_html_e( 'Date', 'bandstage' ); ?> *</label></th>
				<td><input type="date" id="bs_concert_date" name="bs_concert_date"
				           value="<?php echo esc_attr( $date ); ?>" class="regular-text" required></td>
			</tr>
			<tr>
				<th><label for="bs_concert_heure"><?php esc_html_e( 'Heure', 'bandstage' ); ?></label></th>
				<td><input type="time" id="bs_concert_heure" name="bs_concert_heure"
				           value="<?php echo esc_attr( $heure ); ?>" class="small-text"
				           placeholder="20:30"></td>
			</tr>
			<tr>
				<th><label for="bs_concert_lieu"><?php esc_html_e( 'Lieu / Salle', 'bandstage' ); ?></label></th>
				<td><input type="text" id="bs_concert_lieu" name="bs_concert_lieu"
				           value="<?php echo esc_attr( $lieu ); ?>" class="widefat"
				           placeholder="<?php esc_attr_e( 'Le Café de la Danse', 'bandstage' ); ?>"></td>
			</tr>
			<tr>
				<th><label for="bs_concert_ville"><?php esc_html_e( 'Ville', 'bandstage' ); ?></label></th>
				<td><input type="text" id="bs_concert_ville" name="bs_concert_ville"
				           value="<?php echo esc_attr( $ville ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="bs_concert_adresse"><?php esc_html_e( 'Adresse', 'bandstage' ); ?></label></th>
				<td><input type="text" id="bs_concert_adresse" name="bs_concert_adresse"
				           value="<?php echo esc_attr( $adresse ); ?>" class="widefat"></td>
			</tr>
			<tr>
				<th><label for="bs_concert_billets"><?php esc_html_e( 'Lien billets', 'bandstage' ); ?></label></th>
				<td><input type="url" id="bs_concert_billets" name="bs_concert_billets"
				           value="<?php echo esc_attr( $billets ); ?>" class="widefat" placeholder="https://"></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Entrée gratuite', 'bandstage' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="bs_concert_gratuit" value="1" <?php checked( $gratuit ); ?>>
						<?php esc_html_e( 'Gratuit — masque le bouton "billets"', 'bandstage' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Sauvegarde des metas concert.
	 * Hook : save_post_bs_concert
	 *
	 * @param int $post_id ID de l'article.
	 */
	public function save_concert_meta( int $post_id ): void {
		if ( ! isset( $_POST['bs_concert_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST['bs_concert_nonce'] ), 'bs_concert_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$fields = array(
			'bs_concert_date'    => 'string',
			'bs_concert_heure'   => 'string',
			'bs_concert_lieu'    => 'string',
			'bs_concert_ville'   => 'string',
			'bs_concert_adresse' => 'string',
			'bs_concert_billets' => 'url',
		);
		foreach ( $fields as $key => $type ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = 'url' === $type
					? esc_url_raw( wp_unslash( $_POST[ $key ] ) )
					: sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
				update_post_meta( $post_id, $key, $value );
			}
		}
		update_post_meta( $post_id, 'bs_concert_gratuit', isset( $_POST['bs_concert_gratuit'] ) ? '1' : '0' );
	}

	/**
	 * Colonnes personnalisées pour bs_concert.
	 *
	 * @param array $columns Colonnes existantes.
	 * @return array
	 */
	public function concert_columns( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['bs_date']  = __( 'Date', 'bandstage' );
				$new['bs_lieu']  = __( 'Lieu', 'bandstage' );
				$new['bs_ville'] = __( 'Ville', 'bandstage' );
			}
		}
		return $new;
	}

	/**
	 * Rendu colonnes concert.
	 *
	 * @param string $column  Colonne.
	 * @param int    $post_id ID.
	 */
	public function concert_column_content( string $column, int $post_id ): void {
		$map = array(
			'bs_date'  => 'bs_concert_date',
			'bs_lieu'  => 'bs_concert_lieu',
			'bs_ville' => 'bs_concert_ville',
		);
		if ( isset( $map[ $column ] ) ) {
			$val = get_post_meta( $post_id, $map[ $column ], true );
			if ( 'bs_date' === $column && $val ) {
				$ts = strtotime( $val );
				echo $ts ? '<strong>' . esc_html( date_i18n( 'd M Y', $ts ) ) . '</strong>' : esc_html( $val );
			} else {
				echo $val ? esc_html( (string) $val ) : '–';
			}
		}
	}

	// -----------------------------------------------------------------------
	// CPT : bs_titre (Répertoire)
	// -----------------------------------------------------------------------

	private function register_titre(): void {
		$labels = array(
			'name'               => __( 'Répertoire', 'bandstage' ),
			'singular_name'      => __( 'Titre', 'bandstage' ),
			'menu_name'          => __( 'Répertoire', 'bandstage' ),
			'add_new'            => __( 'Ajouter un titre', 'bandstage' ),
			'add_new_item'       => __( 'Nouveau titre', 'bandstage' ),
			'edit_item'          => __( 'Modifier le titre', 'bandstage' ),
			'not_found'          => __( 'Aucun titre trouvé.', 'bandstage' ),
			'not_found_in_trash' => __( 'Aucun titre dans la corbeille.', 'bandstage' ),
		);

		register_post_type(
			'bs_titre',
			array(
				'labels'             => $labels,
				'public'             => false,  // Pas de page publique individuelle.
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => 'bandstage',
				'show_in_rest'       => true,
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'supports'           => array( 'title', 'editor', 'revisions' ),
				'description'        => __( 'Titres joués par le groupe (originals et reprises).', 'bandstage' ),
			)
		);
	}

	/**
	 * Meta box : détails du titre (artiste, type, année).
	 * Hook : add_meta_boxes
	 */
	public function add_titre_meta_boxes(): void {
		add_meta_box(
			'bs_titre_details',
			__( 'Détails du titre', 'bandstage' ),
			array( $this, 'render_titre_meta_box' ),
			'bs_titre',
			'normal',
			'high'
		);
	}

	/**
	 * Rendu de la meta box titre.
	 *
	 * @param WP_Post $post Post courant.
	 */
	public function render_titre_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'bs_titre_meta', 'bs_titre_nonce' );

		$artiste = (string) get_post_meta( $post->ID, 'bs_titre_artiste', true );
		$type    = (string) get_post_meta( $post->ID, 'bs_titre_type',    true );
		$annee   = (string) get_post_meta( $post->ID, 'bs_titre_annee',   true );
		?>
		<table class="form-table" style="margin:0">
			<tr>
				<th style="width:140px"><label for="bs_titre_type"><?php esc_html_e( 'Type', 'bandstage' ); ?></label></th>
				<td>
					<select id="bs_titre_type" name="bs_titre_type">
						<option value="reprise"  <?php selected( $type, 'reprise' ); ?>><?php esc_html_e( 'Reprise', 'bandstage' ); ?></option>
						<option value="original" <?php selected( $type, 'original' ); ?>><?php esc_html_e( 'Original', 'bandstage' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="bs_titre_artiste"><?php esc_html_e( 'Artiste original', 'bandstage' ); ?></label></th>
				<td>
					<input type="text" id="bs_titre_artiste" name="bs_titre_artiste"
					       value="<?php echo esc_attr( $artiste ); ?>" class="regular-text"
					       placeholder="<?php esc_attr_e( 'Ex : Jimi Hendrix', 'bandstage' ); ?>">
					<p class="description"><?php esc_html_e( 'Laissez vide pour un titre original du groupe.', 'bandstage' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="bs_titre_annee"><?php esc_html_e( 'Année (original)', 'bandstage' ); ?></label></th>
				<td>
					<input type="number" id="bs_titre_annee" name="bs_titre_annee"
					       value="<?php echo esc_attr( $annee ); ?>" class="small-text"
					       min="1900" max="2099" placeholder="1970">
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Sauvegarde metas titre.
	 * Hook : save_post_bs_titre
	 *
	 * @param int $post_id ID du post.
	 */
	public function save_titre_meta( int $post_id ): void {
		if ( ! isset( $_POST['bs_titre_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST['bs_titre_nonce'] ), 'bs_titre_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$allowed_types = array( 'reprise', 'original' );
		$type = sanitize_key( $_POST['bs_titre_type'] ?? 'reprise' );
		update_post_meta( $post_id, 'bs_titre_type',    in_array( $type, $allowed_types, true ) ? $type : 'reprise' );
		update_post_meta( $post_id, 'bs_titre_artiste', sanitize_text_field( wp_unslash( $_POST['bs_titre_artiste'] ?? '' ) ) );
		update_post_meta( $post_id, 'bs_titre_annee',   absint( $_POST['bs_titre_annee'] ?? 0 ) ?: '' );
	}

	/**
	 * Colonnes liste répertoire.
	 *
	 * @param array $columns Colonnes existantes.
	 * @return array
	 */
	public function titre_columns( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['bs_type']    = __( 'Type', 'bandstage' );
				$new['bs_artiste'] = __( 'Artiste', 'bandstage' );
				$new['bs_annee']   = __( 'Année', 'bandstage' );
			}
		}
		return $new;
	}

	/**
	 * Rendu colonnes titre.
	 *
	 * @param string $column  Colonne.
	 * @param int    $post_id ID.
	 */
	public function titre_column_content( string $column, int $post_id ): void {
		$map = array(
			'bs_artiste' => 'bs_titre_artiste',
			'bs_annee'   => 'bs_titre_annee',
		);
		if ( 'bs_type' === $column ) {
			$type = (string) get_post_meta( $post_id, 'bs_titre_type', true );
			$labels = array( 'reprise' => '🎵 Reprise', 'original' => '✨ Original' );
			echo esc_html( $labels[ $type ] ?? $type );
		} elseif ( isset( $map[ $column ] ) ) {
			$val = get_post_meta( $post_id, $map[ $column ], true );
			echo $val ? esc_html( (string) $val ) : '–';
		}
	}

	// -----------------------------------------------------------------------
	// Taxonomie : bs_type_partenaire
	// -----------------------------------------------------------------------

	private function register_type_partenaire(): void {
		$labels = array(
			'name'              => __( 'Types de partenaires', 'bandstage' ),
			'singular_name'     => __( 'Type de partenaire', 'bandstage' ),
			'search_items'      => __( 'Rechercher un type', 'bandstage' ),
			'all_items'         => __( 'Tous les types', 'bandstage' ),
			'edit_item'         => __( 'Modifier le type', 'bandstage' ),
			'update_item'       => __( 'Mettre à jour', 'bandstage' ),
			'add_new_item'      => __( 'Ajouter un type', 'bandstage' ),
			'new_item_name'     => __( 'Nom du nouveau type', 'bandstage' ),
			'menu_name'         => __( 'Types', 'bandstage' ),
		);

		register_taxonomy(
			'bs_type_partenaire',
			'bs_partenaire',
			array(
				'labels'            => $labels,
				'hierarchical'      => false,  // tags (pas catégories)
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'type-partenaire' ),
				'capabilities'      => array(
					'manage_terms' => 'manage_options',
					'edit_terms'   => 'manage_options',
					'delete_terms' => 'manage_options',
					'assign_terms' => 'edit_posts',
				),
			)
		);
	}

	// -----------------------------------------------------------------------
	// Meta boxes partenaire (URL, téléphone, adresse)
	// -----------------------------------------------------------------------

	/**
	 * Enregistre les meta boxes pour bs_partenaire.
	 * Hook : add_meta_boxes
	 */
	public function add_partenaire_meta_boxes(): void {
		add_meta_box(
			'bs_partenaire_contact',
			__( 'Coordonnées du partenaire', 'bandstage' ),
			array( $this, 'render_partenaire_meta_box' ),
			'bs_partenaire',
			'normal',
			'high'
		);
	}

	/**
	 * Rendu de la meta box partenaire.
	 *
	 * @param WP_Post $post Article courant.
	 */
	public function render_partenaire_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'bs_partenaire_meta', 'bs_partenaire_nonce' );

		$url      = (string) get_post_meta( $post->ID, 'bs_partenaire_url',      true );
		$tel      = (string) get_post_meta( $post->ID, 'bs_partenaire_tel',      true );
		$adresse  = (string) get_post_meta( $post->ID, 'bs_partenaire_adresse',  true );
		$ville    = (string) get_post_meta( $post->ID, 'bs_partenaire_ville',    true );
		$featured = (bool)   get_post_meta( $post->ID, 'bs_partenaire_featured', true );
		?>
		<table class="form-table" style="margin:0">
			<tr>
				<th style="width:140px"><label for="bs_partenaire_url"><?php esc_html_e( 'Site web', 'bandstage' ); ?></label></th>
				<td><input type="url" id="bs_partenaire_url" name="bs_partenaire_url"
				           value="<?php echo esc_attr( $url ); ?>" class="widefat" placeholder="https://"></td>
			</tr>
			<tr>
				<th><label for="bs_partenaire_tel"><?php esc_html_e( 'Téléphone', 'bandstage' ); ?></label></th>
				<td><input type="tel" id="bs_partenaire_tel" name="bs_partenaire_tel"
				           value="<?php echo esc_attr( $tel ); ?>" class="regular-text" placeholder="06 xx xx xx xx"></td>
			</tr>
			<tr>
				<th><label for="bs_partenaire_adresse"><?php esc_html_e( 'Adresse', 'bandstage' ); ?></label></th>
				<td><input type="text" id="bs_partenaire_adresse" name="bs_partenaire_adresse"
				           value="<?php echo esc_attr( $adresse ); ?>" class="widefat"
				           placeholder="<?php esc_attr_e( '12 rue de la Musique', 'bandstage' ); ?>"></td>
			</tr>
			<tr>
				<th><label for="bs_partenaire_ville"><?php esc_html_e( 'Ville', 'bandstage' ); ?></label></th>
				<td><input type="text" id="bs_partenaire_ville" name="bs_partenaire_ville"
				           value="<?php echo esc_attr( $ville ); ?>" class="regular-text"
				           placeholder="Paris"></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Mis en avant', 'bandstage' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="bs_partenaire_featured" value="1"
						       <?php checked( $featured ); ?>>
						<?php esc_html_e( 'Afficher en tête de liste', 'bandstage' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Sauvegarde des meta du partenaire.
	 * Hook : save_post_bs_partenaire
	 *
	 * @param int $post_id ID de l'article.
	 */
	public function save_partenaire_meta( int $post_id ): void {
		// Vérifications de sécurité.
		if ( ! isset( $_POST['bs_partenaire_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_POST['bs_partenaire_nonce'] ), 'bs_partenaire_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Sauvegarde des champs.
		$fields = array(
			'bs_partenaire_url'      => 'url',
			'bs_partenaire_tel'      => 'string',
			'bs_partenaire_adresse'  => 'string',
			'bs_partenaire_ville'    => 'string',
		);
		foreach ( $fields as $key => $type ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = 'url' === $type
					? esc_url_raw( wp_unslash( $_POST[ $key ] ) )
					: sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
				update_post_meta( $post_id, $key, $value );
			}
		}
		// Checkbox featured.
		update_post_meta( $post_id, 'bs_partenaire_featured', isset( $_POST['bs_partenaire_featured'] ) ? '1' : '0' );
	}

	// -----------------------------------------------------------------------
	// Colonnes personnalisées dans la liste admin
	// -----------------------------------------------------------------------

	/**
	 * Ajoute des colonnes à la liste des actualités.
	 *
	 * @param array $columns Colonnes existantes.
	 * @return array
	 */
	public function news_columns( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['bs_ticker'] = __( 'Dans le ticker', 'bandstage' );
				$new['bs_thumb']  = __( 'Image', 'bandstage' );
			}
		}
		return $new;
	}

	/**
	 * Rendu des colonnes personnalisées pour bs_news.
	 *
	 * @param string $column Colonne courante.
	 * @param int    $post_id ID de l'article.
	 */
	public function news_column_content( string $column, int $post_id ): void {
		if ( 'bs_ticker' === $column ) {
			$status = get_post_status( $post_id );
			if ( 'publish' === $status ) {
				echo '<span style="color:#2da52d;font-weight:600">✓ ' . esc_html__( 'Oui', 'bandstage' ) . '</span>';
			} else {
				echo '<span style="color:#999">–</span>';
			}
		}
		if ( 'bs_thumb' === $column ) {
			$thumb = get_the_post_thumbnail( $post_id, array( 48, 48 ) );
			echo $thumb ?: '<span style="color:#ccc;font-size:20px">🖼</span>'; // phpcs:ignore
		}
	}

	/**
	 * Colonnes partenaires avec ville et type.
	 *
	 * @param array $columns Colonnes existantes.
	 * @return array
	 */
	public function partenaire_columns( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['bs_ville']    = __( 'Ville', 'bandstage' );
				$new['bs_featured'] = __( 'Mis en avant', 'bandstage' );
			}
		}
		return $new;
	}

	/**
	 * Rendu colonnes partenaires.
	 *
	 * @param string $column  Colonne.
	 * @param int    $post_id ID.
	 */
	public function partenaire_column_content( string $column, int $post_id ): void {
		if ( 'bs_ville' === $column ) {
			$ville = get_post_meta( $post_id, 'bs_partenaire_ville', true );
			echo $ville ? esc_html( (string) $ville ) : '–';
		}
		if ( 'bs_featured' === $column ) {
			$featured = get_post_meta( $post_id, 'bs_partenaire_featured', true );
			echo $featured ? '<span style="color:#D4A820;font-size:16px">★</span>' : '–';
		}
	}

	// -----------------------------------------------------------------------
	// Colonnes triables
	// -----------------------------------------------------------------------

	/**
	 * Rend la colonne "Mis en avant" triable.
	 *
	 * @param array $columns Colonnes triables.
	 * @return array
	 */
	public function partenaire_sortable_columns( array $columns ): array {
		$columns['bs_featured'] = 'bs_featured';
		$columns['bs_ville']    = 'bs_ville';
		return $columns;
	}

	// -----------------------------------------------------------------------
	// Termes par défaut de la taxonomie
	// -----------------------------------------------------------------------

	/**
	 * Crée les types de partenaires par défaut si absents.
	 * Appelé à l'activation via BandStage_Activator.
	 */
	public static function create_default_terms(): void {
		// On enregistre d'abord la taxonomie (elle n'est peut-être pas encore enregistrée).
		$instance = new self();
		$instance->register_type_partenaire();

		$defaults = array(
			'magasins-musique'  => __( 'Magasins de musique', 'bandstage' ),
			'luthiers'          => __( 'Luthiers', 'bandstage' ),
			'salles-concerts'   => __( 'Salles de concerts', 'bandstage' ),
			'institutionnels'   => __( 'Institutionnels', 'bandstage' ),
		);

		foreach ( $defaults as $slug => $name ) {
			if ( ! term_exists( $slug, 'bs_type_partenaire' ) ) {
				wp_insert_term( $name, 'bs_type_partenaire', array( 'slug' => $slug ) );
			}
		}
	}
}
