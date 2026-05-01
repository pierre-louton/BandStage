<?php
/**
 * BandStage Studio — Interface de saisie front-end.
 *
 * Accessible via le shortcode [bandstage_studio].
 * Gère les vues : dashboard, éditeur d'actualité, éditeur de partenaire.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_Studio
 */
class BandStage_Studio {

	// -----------------------------------------------------------------------
	// Shortcode principal
	// -----------------------------------------------------------------------

	/**
	 * Rendu du Studio Actualités — router.
	 * Shortcode : [bandstage_studio]
	 * Gère UNIQUEMENT les actualités (humeurs).
	 * Les partenaires sont dans [bandstage_partenaires].
	 *
	 * @return string HTML complet de l'interface.
	 */
	public function render( array $atts = array() ): string {

		// Chargement des assets Studio.
		// S'assure que bandstage-public est enregistré avant d'être utilisé.
		if ( ! wp_style_is( 'bandstage-public', 'registered' ) ) {
			wp_register_style( 'bandstage-public', BANDSTAGE_PLUGIN_URL . 'public/css/bandstage-public.css', array(), BANDSTAGE_VERSION );
		}
		wp_enqueue_style( 'bandstage-public' );
		wp_enqueue_style(
			'bandstage-studio',
			BANDSTAGE_PLUGIN_URL . 'public/css/bandstage-studio.css',
			array(),
			BANDSTAGE_VERSION
		);
		wp_enqueue_script(
			'bandstage-studio',
			BANDSTAGE_PLUGIN_URL . 'public/js/bandstage-studio.js',
			array( 'jquery' ),
			BANDSTAGE_VERSION,
			true
		);
		wp_localize_script( 'bandstage-studio', 'bsStudio', array(
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( BANDSTAGE_NONCE ),
			'studio_url'  => get_permalink(),
			'strings'     => array(
				'confirm_delete'  => __( 'Supprimer définitivement ?', 'bandstage' ),
				'saving'          => __( 'Enregistrement…', 'bandstage' ),
				'saved'           => __( 'Enregistré !', 'bandstage' ),
				'error'           => __( 'Erreur, réessayez.', 'bandstage' ),
				'publish_confirm' => __( 'Publier cette actualité ?', 'bandstage' ),
			),
		) );

		// Vérification connexion.
		if ( ! is_user_logged_in() ) {
			return $this->render_login_prompt();
		}

		// Vérification droits — admin ou éditeur du groupe.
		if ( ! $this->can_access_studio() ) {
			return $this->render_access_denied();
		}

		// Routing par paramètre GET — actus uniquement.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$view = sanitize_key( $_GET['bs_view'] ?? 'dashboard' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = absint( $_GET['bs_id'] ?? 0 );

		ob_start();
		switch ( $view ) {
			case 'actu-edit':
				include BANDSTAGE_PLUGIN_DIR . 'public/partials/studio/actu-edit.php';
				break;
			default:
				include BANDSTAGE_PLUGIN_DIR . 'public/partials/studio/dashboard.php';
		}
		return ob_get_clean();
	}

	// -----------------------------------------------------------------------
	// Shortcode Partenaires
	// -----------------------------------------------------------------------

	/**
	 * Rendu du gestionnaire de partenaires.
	 * Shortcode : [bandstage_partenaires]
	 * Accessible depuis la boîte "Partenaires" de la homepage.
	 *
	 * @return string HTML complet de l'interface partenaires.
	 */
	public function render_partenaires( array $atts = array() ): string {

		// Chargement assets Studio.
		// S'assure que bandstage-public est enregistré avant d'être déclaré comme dépendance.
		if ( ! wp_style_is( 'bandstage-public', 'registered' ) ) {
			wp_register_style( 'bandstage-public', BANDSTAGE_PLUGIN_URL . 'public/css/bandstage-public.css', array(), BANDSTAGE_VERSION );
		}
		wp_enqueue_style( 'bandstage-public' );
		wp_enqueue_style(  'bandstage-studio', BANDSTAGE_PLUGIN_URL . 'public/css/bandstage-studio.css',  array(), BANDSTAGE_VERSION );
		wp_enqueue_script( 'bandstage-studio', BANDSTAGE_PLUGIN_URL . 'public/js/bandstage-studio.js', array( 'jquery' ), BANDSTAGE_VERSION, true );
		wp_localize_script( 'bandstage-studio', 'bsStudio', array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( BANDSTAGE_NONCE ),
			'studio_url' => self::partenaires_url(),
			'strings'    => array(
				'confirm_delete' => __( 'Supprimer définitivement ?', 'bandstage' ),
				'saving'         => __( 'Enregistrement…', 'bandstage' ),
				'saved'          => __( 'Enregistré !', 'bandstage' ),
				'error'          => __( 'Erreur, réessayez.', 'bandstage' ),
			),
		) );

		if ( ! is_user_logged_in() ) {
			return $this->render_login_prompt();
		}
		if ( ! $this->can_access_studio() ) {
			return $this->render_access_denied();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$view    = sanitize_key( $_GET['bs_view'] ?? 'partenaires' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = absint( $_GET['bs_id'] ?? 0 );

		ob_start();
		switch ( $view ) {
			case 'partenaire-edit':
				include BANDSTAGE_PLUGIN_DIR . 'public/partials/studio/partenaire-edit.php';
				break;
			default:
				include BANDSTAGE_PLUGIN_DIR . 'public/partials/studio/partenaires-dashboard.php';
		}
		return ob_get_clean();
	}

		// -----------------------------------------------------------------------
	// AJAX — Sauvegarder une actualité
	// -----------------------------------------------------------------------

	/**
	 * AJAX : créer ou mettre à jour une actualité bs_news.
	 * Hook : wp_ajax_bs_save_news
	 */
	public function ajax_save_news(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! $this->can_access_studio() ) {
			wp_send_json_error( array( 'message' => __( 'Permissions insuffisantes.', 'bandstage' ) ), 403 );
		}

		$post_id   = absint( $_POST['post_id'] ?? 0 );
		$title     = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$content   = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
		$excerpt   = sanitize_textarea_field( wp_unslash( $_POST['excerpt'] ?? '' ) );
		$status    = in_array( $_POST['status'] ?? '', array( 'draft', 'publish' ), true )
		             ? sanitize_key( $_POST['status'] )
		             : 'draft';
		$thumb_id  = absint( $_POST['thumb_id'] ?? 0 );

		if ( ! $title ) {
			wp_send_json_error( array( 'message' => __( 'Le titre est obligatoire.', 'bandstage' ) ) );
		}

		$data = array(
			'post_type'    => 'bs_news',
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
			'post_status'  => $status,
			'post_author'  => get_current_user_id(),
		);

		if ( $post_id ) {
			// Vérification droits sur ce post.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Vous ne pouvez pas modifier cette actualité.', 'bandstage' ) ), 403 );
			}
			$data['ID'] = $post_id;
			$result     = wp_update_post( $data, true );
		} else {
			$result = wp_insert_post( $data, true );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Image à la une.
		if ( $thumb_id ) {
			set_post_thumbnail( $result, $thumb_id );
		} elseif ( isset( $_POST['remove_thumb'] ) ) {
			delete_post_thumbnail( $result );
		}

		wp_send_json_success( array(
			'message' => 'publish' === $status
				? __( 'Actualité publiée !', 'bandstage' )
				: __( 'Brouillon enregistré.', 'bandstage' ),
			'post_id' => $result,
			'status'  => $status,
		) );
	}

	/**
	 * AJAX : supprimer une actualité.
	 * Hook : wp_ajax_bs_delete_news
	 */
	public function ajax_delete_news(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );
		if ( ! $this->can_access_studio() ) {
			wp_send_json_error( array(), 403 );
		}
		$post_id = absint( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'delete_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Action non autorisée.', 'bandstage' ) ) );
		}
		wp_trash_post( $post_id );
		wp_send_json_success( array( 'message' => __( 'Actualité supprimée.', 'bandstage' ) ) );
	}

	// -----------------------------------------------------------------------
	// AJAX — Sauvegarder un partenaire
	// -----------------------------------------------------------------------

	/**
	 * AJAX : créer ou mettre à jour un partenaire.
	 * Hook : wp_ajax_bs_save_partenaire
	 */
	public function ajax_save_partenaire(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! $this->can_access_studio() ) {
			wp_send_json_error( array( 'message' => __( 'Permissions insuffisantes.', 'bandstage' ) ), 403 );
		}

		$post_id    = absint( $_POST['post_id'] ?? 0 );
		$title      = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$content    = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
		$url        = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
		$tel        = sanitize_text_field( wp_unslash( $_POST['tel'] ?? '' ) );
		$adresse    = sanitize_text_field( wp_unslash( $_POST['adresse'] ?? '' ) );
		$ville      = sanitize_text_field( wp_unslash( $_POST['ville'] ?? '' ) );
		$featured   = ! empty( $_POST['featured'] );
		$type_id    = absint( $_POST['type_id'] ?? 0 );
		$thumb_id   = absint( $_POST['thumb_id'] ?? 0 );

		if ( ! $title ) {
			wp_send_json_error( array( 'message' => __( 'Le nom est obligatoire.', 'bandstage' ) ) );
		}

		$data = array(
			'post_type'    => 'bs_partenaire',
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
		);

		if ( $post_id ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Accès refusé.', 'bandstage' ) ), 403 );
			}
			$data['ID'] = $post_id;
			$result     = wp_update_post( $data, true );
		} else {
			$result = wp_insert_post( $data, true );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Metas coordonnées.
		update_post_meta( $result, 'bs_partenaire_url',      $url );
		update_post_meta( $result, 'bs_partenaire_tel',      $tel );
		update_post_meta( $result, 'bs_partenaire_adresse',  $adresse );
		update_post_meta( $result, 'bs_partenaire_ville',    $ville );
		update_post_meta( $result, 'bs_partenaire_featured', $featured ? '1' : '0' );

		// Taxonomy type.
		if ( $type_id ) {
			wp_set_post_terms( $result, array( $type_id ), 'bs_type_partenaire' );
		}

		// Image.
		if ( $thumb_id ) {
			set_post_thumbnail( $result, $thumb_id );
		} elseif ( isset( $_POST['remove_thumb'] ) ) {
			delete_post_thumbnail( $result );
		}

		wp_send_json_success( array(
			'message' => __( 'Partenaire enregistré.', 'bandstage' ),
			'post_id' => $result,
		) );
	}

	/**
	 * AJAX : supprimer un partenaire.
	 * Hook : wp_ajax_bs_delete_partenaire
	 */
	public function ajax_delete_partenaire(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );
		if ( ! $this->can_access_studio() ) {
			wp_send_json_error( array(), 403 );
		}
		$post_id = absint( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'delete_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Action non autorisée.', 'bandstage' ) ) );
		}
		wp_trash_post( $post_id );
		wp_send_json_success( array( 'message' => __( 'Partenaire supprimé.', 'bandstage' ) ) );
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	/**
	 * Vérifie si l'utilisateur courant peut accéder au Studio.
	 * Admin WP ou rôle avec 'edit_posts' (auteur/éditeur).
	 *
	 * @return bool
	 */
	public function can_access_studio(): bool {
		return is_user_logged_in() &&
		       ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) );
	}

	/**
	 * Retourne l'URL de base d'une page contenant un shortcode donné.
	 *
	 * @param string $shortcode Shortcode à chercher (ex: 'bandstage_studio').
	 * @param string $fallback  URL de secours.
	 * @return string
	 */
	private static function find_page_url( string $shortcode, string $fallback ): string {
		global $wpdb;
		// Recherche directe en base — plus fiable que WP_Query 's' qui cherche dans le titre.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts}
				 WHERE post_status = 'publish'
				   AND post_type   = 'page'
				   AND post_content LIKE %s
				 LIMIT 1",
				'%' . $wpdb->esc_like( $shortcode ) . '%'
			)
		);
		return $post_id ? get_permalink( (int) $post_id ) : home_url( $fallback );
	}

	/**
	 * URL du Studio Actualités avec paramètres optionnels.
	 *
	 * @param string $view    Vue cible.
	 * @param int    $post_id ID du post (0 = nouveau).
	 * @return string
	 */
	public static function url( string $view = 'dashboard', int $post_id = 0 ): string {
		$base = self::find_page_url( 'bandstage_studio', '/studio/' );
		$args = array( 'bs_view' => $view );
		if ( $post_id ) {
			$args['bs_id'] = $post_id;
		}
		return add_query_arg( $args, $base );
	}

	/**
	 * URL du gestionnaire Partenaires avec paramètres optionnels.
	 *
	 * @param string $view    Vue cible.
	 * @param int    $post_id ID du post (0 = nouveau).
	 * @return string
	 */
	public static function partenaires_url( string $view = 'partenaires', int $post_id = 0 ): string {
		$base = self::find_page_url( 'bandstage_partenaires', '/partenaires/' );
		$args = array( 'bs_view' => $view );
		if ( $post_id ) {
			$args['bs_id'] = $post_id;
		}
		return add_query_arg( $args, $base );
	}

	// -----------------------------------------------------------------------
	// Shortcode Répertoire
	// -----------------------------------------------------------------------

	/**
	 * Rendu du gestionnaire de répertoire.
	 * Shortcode : [bandstage_repertoire]
	 *
	 * @return string
	 */
	public function render_repertoire( array $atts = array() ): string {
		// S'assure que bandstage-public est enregistré avant d'être déclaré comme dépendance.
		if ( ! wp_style_is( 'bandstage-public', 'registered' ) ) {
			wp_register_style( 'bandstage-public', BANDSTAGE_PLUGIN_URL . 'public/css/bandstage-public.css', array(), BANDSTAGE_VERSION );
		}
		wp_enqueue_style( 'bandstage-public' );
		wp_enqueue_style(  'bandstage-studio', BANDSTAGE_PLUGIN_URL . 'public/css/bandstage-studio.css',  array(), BANDSTAGE_VERSION );
		wp_enqueue_script( 'bandstage-studio', BANDSTAGE_PLUGIN_URL . 'public/js/bandstage-studio.js', array( 'jquery' ), BANDSTAGE_VERSION, true );
		wp_localize_script( 'bandstage-studio', 'bsStudio', array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( BANDSTAGE_NONCE ),
			'studio_url' => get_permalink(),
			'strings'    => array(
				'confirm_delete' => __( 'Supprimer ce titre ?', 'bandstage' ),
				'saving'         => __( 'Enregistrement…', 'bandstage' ),
				'saved'          => __( 'Enregistré !', 'bandstage' ),
				'error'          => __( 'Erreur, réessayez.', 'bandstage' ),
			),
		) );

		if ( ! is_user_logged_in() )         return $this->render_login_prompt();
		if ( ! $this->can_access_studio() )  return $this->render_access_denied();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$view    = sanitize_key( $_GET['bs_view'] ?? 'repertoire' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = absint( $_GET['bs_id'] ?? 0 );

		ob_start();
		switch ( $view ) {
			case 'titre-edit':
				include BANDSTAGE_PLUGIN_DIR . 'public/partials/studio/titre-edit.php';
				break;
			default:
				include BANDSTAGE_PLUGIN_DIR . 'public/partials/studio/repertoire-dashboard.php';
		}
		return ob_get_clean();
	}

	// -----------------------------------------------------------------------
	// AJAX — Sauvegarder un titre
	// -----------------------------------------------------------------------

	/**
	 * AJAX : créer ou mettre à jour un titre du répertoire.
	 * Hook : wp_ajax_bs_save_titre
	 */
	public function ajax_save_titre(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );
		if ( ! $this->can_access_studio() ) {
			wp_send_json_error( array( 'message' => __( 'Permissions insuffisantes.', 'bandstage' ) ), 403 );
		}

		$post_id = absint( $_POST['post_id'] ?? 0 );
		$title   = sanitize_text_field( wp_unslash( $_POST['title']   ?? '' ) );
		$artiste = sanitize_text_field( wp_unslash( $_POST['artiste'] ?? '' ) );
		$annee   = absint( $_POST['annee'] ?? 0 );
		$type    = in_array( $_POST['type'] ?? '', array( 'reprise', 'original' ), true )
		           ? sanitize_key( $_POST['type'] ) : 'reprise';
		$notes   = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );

		if ( ! $title ) {
			wp_send_json_error( array( 'message' => __( 'Le titre est obligatoire.', 'bandstage' ) ) );
		}

		$data = array(
			'post_type'    => 'bs_titre',
			'post_title'   => $title,
			'post_content' => $notes,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
		);

		if ( $post_id ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Accès refusé.', 'bandstage' ) ), 403 );
			}
			$data['ID'] = $post_id;
			$result     = wp_update_post( $data, true );
		} else {
			$result = wp_insert_post( $data, true );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		update_post_meta( $result, 'bs_titre_type',    $type );
		update_post_meta( $result, 'bs_titre_artiste', $artiste );
		update_post_meta( $result, 'bs_titre_annee',   $annee ?: '' );

		wp_send_json_success( array(
			'message' => __( 'Titre enregistré.', 'bandstage' ),
			'post_id' => $result,
		) );
	}

	/**
	 * AJAX : supprimer un titre.
	 * Hook : wp_ajax_bs_delete_titre
	 */
	public function ajax_delete_titre(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );
		if ( ! $this->can_access_studio() ) {
			wp_send_json_error( array(), 403 );
		}
		$post_id = absint( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'delete_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Action non autorisée.', 'bandstage' ) ) );
		}
		wp_trash_post( $post_id );
		wp_send_json_success( array( 'message' => __( 'Titre supprimé.', 'bandstage' ) ) );
	}

		/**
	 * URL du gestionnaire Répertoire.
	 *
	 * @param string $view    Vue cible.
	 * @param int    $post_id ID du post.
	 * @return string
	 */
	public static function repertoire_url( string $view = 'repertoire', int $post_id = 0 ): string {
		$base = self::find_page_url( 'bandstage_repertoire', '/repertoire/' );
		$args = array( 'bs_view' => $view );
		if ( $post_id ) $args['bs_id'] = $post_id;
		return add_query_arg( $args, $base );
	}

	private function render_login_prompt(): string {
		ob_start();
		?>
		<div class="bss-login-prompt">
			<div class="bss-login-card">
				<div class="bss-login-icon">🎸</div>
				<h2><?php echo esc_html( (string) get_option( 'bs_band_name', 'BandStage' ) ); ?></h2>
				<p><?php esc_html_e( 'Connectez-vous pour accéder au Studio.', 'bandstage' ); ?></p>
				<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"
				   class="bss-btn bss-btn--gold">
					<?php esc_html_e( 'Se connecter', 'bandstage' ); ?>
				</a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	private function render_access_denied(): string {
		return '<div class="bss-denied"><p>'
		       . esc_html__( 'Accès réservé aux membres du groupe.', 'bandstage' )
		       . '</p></div>';
	}

	/**
	 * AJAX : upload d'une image depuis le Studio mobile.
	 * Hook : wp_ajax_bs_upload_studio_image
	 */
	public function ajax_upload_image(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );

		if ( ! $this->can_access_studio() ) {
			wp_send_json_error( array( 'message' => __( 'Accès refusé.', 'bandstage' ) ), 403 );
		}

		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Aucun fichier reçu.', 'bandstage' ) ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_handle_upload( 'file', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ) );
		}

		wp_send_json_success( array(
			'attachment_id' => $attachment_id,
			'url'           => wp_get_attachment_image_url( $attachment_id, 'medium' ),
		) );
	}
}
