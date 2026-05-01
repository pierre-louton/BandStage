<?php
/**
 * Fonctionnalités côté administration.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

/**
 * BandStage_Admin
 */
class BandStage_Admin {

	private string $version;
	private array $tabs = array();

	/**
	 * Schéma des options : option_name => [ tab, type ]
	 * type : string | text | bool | int | color | url | email | css | array
	 *
	 * @var array<string, array{tab:string, type:string}>
	 */
	private array $schema = array();

	public function __construct( string $version ) {
		$this->version = $version;

		// Libellés des onglets (pas de __() ici — appelé trop tôt via register_settings)
		// Les libellés sont traduits dans render_settings_page() qui s'exécute sous admin_menu.
		$this->tabs = array(
			'groupe'        => array( 'label' => 'Groupe',        'icon' => '🎸' ),
			'apparence'     => array( 'label' => 'Apparence',     'icon' => '🎨' ),
			'boites'        => array( 'label' => 'Boîtes',        'icon' => '⊞' ),
			'ticker'        => array( 'label' => 'Ticker',        'icon' => '📢' ),
			'tchache'       => array( 'label' => 'Tchache',       'icon' => '💬' ),
			'membres'       => array( 'label' => 'Membres',       'icon' => '👥' ),
			'notifications' => array( 'label' => 'Notifications', 'icon' => '🔔' ),
			'references'    => array( 'label' => 'Références',   'icon' => '🎵' ),
			'partenaires'   => array( 'label' => 'Partenaires',   'icon' => '🤝' ),
			'avance'        => array( 'label' => 'Avancé',        'icon' => '⚙' ),
		);

		$this->build_schema();
	}

	// -----------------------------------------------------------------------
	// Schéma des options (tab + type de sanitisation)
	// -----------------------------------------------------------------------

	/**
	 * Construit le schéma associant chaque option à son onglet et son type.
	 * C'est la source de vérité pour register_settings() et sanitize_option().
	 */
	private function build_schema(): void {
		// --- Groupe ---
		$groupe = array(
			'bs_band_name'    => 'string',
			'bs_band_tagline' => 'string',
			'bs_band_logo_id' => 'int',
		);

		// --- Apparence ---
		$apparence = array(
			'bs_bg_color_start'    => 'color',
			'bs_bg_color_end'      => 'color',
			'bs_bg_angle'          => 'int',
			'bs_accent_color'      => 'color',
			'bs_text_color'        => 'color',
			'bs_brand_font'        => 'string',
			'bs_box_font'          => 'string',
			'bs_box_radius'        => 'int',
			'bs_ticker_bg_color'   => 'color',

			// Splashscreen.
			'bs_splash_enabled'    => 'bool',
			'bs_splash_image_id'   => 'int',
			'bs_splash_duration'   => 'int',
			'bs_ticker_text_color' => 'color',
		);

		// --- Boîtes ---
		$boites = array();
		for ( $i = 1; $i <= 6; $i++ ) {
			$boites[ "bs_box_{$i}_title" ]       = 'string';
			$boites[ "bs_box_{$i}_link" ]        = 'url';
			$boites[ "bs_box_{$i}_image_id" ]    = 'int';
			$boites[ "bs_box_{$i}_color_start" ] = 'color';
			$boites[ "bs_box_{$i}_color_end" ]   = 'color';
			$boites[ "bs_box_{$i}_icon" ]        = 'string';
			$boites[ "bs_box_{$i}_enabled" ]     = 'bool';
		}

		// --- Ticker ---
		$ticker = array(
			'bs_ticker_enabled'    => 'bool',
			'bs_ticker_source'     => 'string',
			'bs_ticker_items'      => 'text',
			'bs_ticker_categories' => 'array',
			'bs_ticker_speed'      => 'int',
		);

		// --- Tchache ---
		$tchache = array(
			'bs_tchache_enabled'      => 'bool',
			'bs_tchache_moderation'   => 'string',
			'bs_tchache_members_only' => 'bool',
			'bs_tchache_max_per_day'  => 'int',
			'bs_tchache_min_delay'    => 'int',
			'bs_tchache_max_length'   => 'int',
			'bs_tchache_notify_email' => 'email',
			'bs_tchache_notify_new'   => 'bool',
		);

		// --- Membres ---
		$membres = array(
			'bs_members_enabled'          => 'bool',
			'bs_members_require_approval' => 'bool',
			'bs_members_avatar_type'      => 'string',
			'bs_members_show_bio'         => 'bool',
			'bs_members_show_instrument'  => 'bool',
			'bs_members_show_location'    => 'bool',
		);

		// --- Notifications ---
		$notifications = array(
			'bs_notif_from_name'         => 'string',
			'bs_notif_from_email'        => 'email',
			'bs_notif_concerts_enabled'  => 'bool',
			'bs_notif_concerts_days'     => 'int',
			'bs_notif_news_enabled'      => 'bool',
			'bs_notif_mailchimp_api_key' => 'string',
			'bs_notif_mailchimp_list_id' => 'string',
			'bs_reprise_enabled'         => 'bool',
			'bs_reprise_recipient'       => 'email',
			'bs_reprise_confirm'         => 'text',
		);

		// --- Références ---
		$references = array(
			'bs_influences'       => 'text',   // JSON sérialisé par JS avant soumission.
			'bs_influences_label' => 'string',
			'bs_repertoire_label' => 'string',
		);

		// --- Avancé ---
		$avance = array(
			'bs_custom_css'  => 'css',
			'bs_debug_mode'  => 'bool',
		);

		// Compilation dans $this->schema.
		foreach ( compact( 'groupe', 'apparence', 'boites', 'ticker', 'tchache', 'membres', 'notifications', 'references', 'avance' ) as $tab => $options ) {
			foreach ( $options as $key => $type ) {
				$this->schema[ $key ] = array( 'tab' => $tab, 'type' => $type );
			}
		}
	}

	/**
	 * Retourne le nom du groupe Settings API pour un onglet donné.
	 */
	private function group( string $tab ): string {
		return 'bs_settings_' . $tab;
	}

	// -----------------------------------------------------------------------
	// Assets
	// -----------------------------------------------------------------------

	public function enqueue_styles( string $hook ): void {
		if ( ! $this->is_plugin_page( $hook ) ) {
			return;
		}
		wp_enqueue_style(
			'bandstage-admin',
			BANDSTAGE_PLUGIN_URL . 'admin/css/bandstage-admin.css',
			array( 'wp-color-picker' ),
			$this->version
		);
		wp_enqueue_media();
	}

	public function enqueue_scripts( string $hook ): void {
		if ( ! $this->is_plugin_page( $hook ) ) {
			return;
		}
		wp_enqueue_script(
			'bandstage-admin',
			BANDSTAGE_PLUGIN_URL . 'admin/js/bandstage-admin.js',
			array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ),
			$this->version,
			true
		);
		wp_localize_script(
			'bandstage-admin',
			'bandstageAdmin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( BANDSTAGE_NONCE ),
				'strings'  => array(
					'select_image'   => __( 'Sélectionner une image', 'bandstage' ),
					'use_image'      => __( 'Utiliser cette image', 'bandstage' ),
					'export_success' => __( 'Réglages exportés.', 'bandstage' ),
					'import_success' => __( 'Réglages importés.', 'bandstage' ),
					'import_error'   => __( 'Erreur : JSON invalide.', 'bandstage' ),
				),
			)
		);
	}

	// -----------------------------------------------------------------------
	// Menu Admin
	// -----------------------------------------------------------------------

	public function add_plugin_admin_menu(): void {
		add_menu_page(
			__( 'BandStage', 'bandstage' ),
			'BandStage',
			'manage_options',
			'bandstage',
			array( $this, 'render_settings_page' ),
			'dashicons-format-audio',
			58
		);

		// Les CPTs (Actualités, Partenaires, Concerts, Répertoire) apparaissent
		// automatiquement sous ce menu via show_in_menu => 'bandstage' dans register_post_type().
		// On ajoute UNIQUEMENT les pages spécifiques au plugin.
		add_submenu_page( 'bandstage', __( 'Réglages', 'bandstage' ),  __( 'Réglages', 'bandstage' ),  'manage_options', 'bandstage',          array( $this, 'render_settings_page' ) );
		add_submenu_page( 'bandstage', __( 'Tchache', 'bandstage' ),   __( 'Tchache', 'bandstage' ),   'manage_options', 'bandstage-tchache',  array( $this, 'render_tchache_page' ) );
		add_submenu_page( 'bandstage', __( 'Membres', 'bandstage' ),   __( 'Membres', 'bandstage' ),   'manage_options', 'bandstage-members',  array( $this, 'render_members_page' ) );
	}

	// -----------------------------------------------------------------------
	// Settings API — UN GROUPE PAR ONGLET
	// -----------------------------------------------------------------------

	/**
	 * Enregistre les options par groupe d'onglet.
	 * Ainsi, sauvegarder l'onglet "boites" n'affecte QUE les options de cet onglet.
	 */
	public function register_settings(): void {
		$defaults = BandStage_Activator::get_default_options();

		foreach ( $this->schema as $option_name => $meta ) {
			register_setting(
				$this->group( $meta['tab'] ),  // ← groupe spécifique à l'onglet
				$option_name,
				array(
					'sanitize_callback' => function ( $value ) use ( $option_name, $meta ) {
						return $this->sanitize_by_type( $value, $meta['type'], $option_name );
					},
					'default' => $defaults[ $option_name ] ?? '',
				)
			);
		}
	}

	/**
	 * Sanitise une valeur selon son type déclaré dans le schéma.
	 *
	 * @param mixed  $value       Valeur brute.
	 * @param string $type        Type du schéma.
	 * @param string $option_name Nom de l'option (pour logs debug).
	 * @return mixed
	 */
	private function sanitize_by_type( $value, string $type, string $option_name ) {
		switch ( $type ) {
			case 'bool':
				return (bool) $value;

			case 'int':
				return absint( $value );

			case 'color':
				$sanitized = sanitize_hex_color( (string) $value );
				return $sanitized ?: '';

			case 'url':
				return esc_url_raw( (string) $value );

			case 'email':
				return sanitize_email( (string) $value );

			case 'text':
				return sanitize_textarea_field( (string) $value );

			case 'css':
				// Autorise les règles CSS mais retire tout script.
				return wp_strip_all_tags( (string) $value );

			case 'array':
				if ( ! is_array( $value ) ) {
					return array();
				}
				return array_map( 'absint', $value );

			case 'string':
			default:
				return sanitize_text_field( (string) $value );
		}
	}

	// -----------------------------------------------------------------------
	// Rendu des pages
	// -----------------------------------------------------------------------

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'bandstage' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = sanitize_key( $_GET['tab'] ?? 'groupe' );
		if ( ! array_key_exists( $active_tab, $this->tabs ) ) {
			$active_tab = 'groupe';
		}

		// Libellés traduits (on est bien sous admin_menu maintenant).
		$tab_labels = array(
			'groupe'        => __( 'Groupe',        'bandstage' ),
			'apparence'     => __( 'Apparence',     'bandstage' ),
			'boites'        => __( 'Boîtes',        'bandstage' ),
			'ticker'        => __( 'Ticker',        'bandstage' ),
			'tchache'       => __( 'Tchache',       'bandstage' ),
			'membres'       => __( 'Membres',       'bandstage' ),
			'notifications' => __( 'Notifications', 'bandstage' ),
			'references'    => __( 'Références',   'bandstage' ),
			'partenaires'   => __( 'Partenaires',   'bandstage' ),
			'avance'        => __( 'Avancé',        'bandstage' ),
		);
		?>
		<div class="wrap bs-admin-wrap">
			<h1>
				<span class="bs-logo">🎸</span>
				<?php esc_html_e( 'BandStage — Configuration', 'bandstage' ); ?>
				<span class="bs-version">v<?php echo esc_html( BANDSTAGE_VERSION ); ?></span>
			</h1>

			<?php settings_errors( $this->group( $active_tab ) ); ?>

			<nav class="bs-tabs" aria-label="<?php esc_attr_e( 'Sections', 'bandstage' ); ?>">
				<?php foreach ( $this->tabs as $tab_id => $tab ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=bandstage&tab=' . $tab_id ) ); ?>"
					   class="bs-tab<?php echo $active_tab === $tab_id ? ' bs-tab--active' : ''; ?>"
					   aria-current="<?php echo $active_tab === $tab_id ? 'page' : 'false'; ?>">
						<?php echo esc_html( $tab_labels[ $tab_id ] ?? $tab['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="options.php" class="bs-settings-form" enctype="multipart/form-data">
				<?php
				// ↓ Groupe spécifique à l'onglet actif — seules ses options sont sauvegardées.
				settings_fields( $this->group( $active_tab ) );
				$this->render_tab( $active_tab );
				submit_button( __( 'Enregistrer les modifications', 'bandstage' ) );
				?>
			</form>

			<div class="bs-shortcode-box">
				<h3><?php esc_html_e( 'Shortcodes disponibles', 'bandstage' ); ?></h3>
				<code>[bandstage_homepage]</code> — <?php esc_html_e( 'Page d\'accueil complète', 'bandstage' ); ?><br>
				<code>[bandstage_tchache]</code>  — <?php esc_html_e( 'Mini-forum seul', 'bandstage' ); ?><br>
				<code>[bandstage_profil]</code>   — <?php esc_html_e( 'Formulaire membre / connexion', 'bandstage' ); ?>
			</div>
		</div>
		<?php
	}

	private function render_tab( string $tab ): void {
		$partial = BANDSTAGE_PLUGIN_DIR . "admin/partials/tab-{$tab}.php";
		if ( file_exists( $partial ) ) {
			include $partial;
		} else {
			echo '<p>' . esc_html__( 'Onglet non trouvé.', 'bandstage' ) . '</p>';
		}
	}

	public function render_tchache_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'bandstage' ) );
		}
		include BANDSTAGE_PLUGIN_DIR . 'admin/partials/page-tchache-moderation.php';
	}

	public function render_members_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'bandstage' ) );
		}
		include BANDSTAGE_PLUGIN_DIR . 'admin/partials/page-members.php';
	}

	// -----------------------------------------------------------------------
	// AJAX — Import / Export
	// -----------------------------------------------------------------------

	public function ajax_export_settings(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissions insuffisantes.', 'bandstage' ) ), 403 );
		}

		$defaults = BandStage_Activator::get_default_options();
		$export   = array( '_bandstage_version' => BANDSTAGE_VERSION, '_export_date' => gmdate( 'Y-m-d H:i:s' ) );
		foreach ( array_keys( $defaults ) as $key ) {
			$export[ $key ] = get_option( $key, $defaults[ $key ] );
		}
		wp_send_json_success( array( 'json' => wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ) );
	}

	public function ajax_import_settings(): void {
		check_ajax_referer( BANDSTAGE_NONCE, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissions insuffisantes.', 'bandstage' ) ), 403 );
		}

		$raw  = sanitize_textarea_field( wp_unslash( $_POST['json'] ?? '' ) );
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			wp_send_json_error( array( 'message' => __( 'JSON invalide.', 'bandstage' ) ) );
		}

		foreach ( $this->schema as $option_name => $meta ) {
			if ( array_key_exists( $option_name, $data ) ) {
				update_option( $option_name, $this->sanitize_by_type( $data[ $option_name ], $meta['type'], $option_name ) );
			}
		}
		wp_send_json_success( array( 'message' => __( 'Réglages importés.', 'bandstage' ) ) );
	}

	// -----------------------------------------------------------------------
	// Helper
	// -----------------------------------------------------------------------

	private function is_plugin_page( string $hook ): bool {
		return in_array( $hook, array(
			'toplevel_page_bandstage',
			'bandstage_page_bandstage-tchache',
			'bandstage_page_bandstage-members',
		), true );
	}
}
