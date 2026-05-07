<?php
/**
 * Admin — settings page layout avec onglets.
 *
 * @var \BandStage\Admin\SettingsPage $settings
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$active_tab = sanitize_key( $_GET['tab'] ?? 'groupe' );
$tabs       = $settings->get_tabs();
if ( ! array_key_exists( $active_tab, $tabs ) ) {
	$active_tab = 'groupe';
}
?>
<div class="wrap bs-admin-wrap">
  <h1><?php esc_html_e( 'Réglages BandStage', 'bandstage' ); ?></h1>

  <nav class="bs-admin-tabs">
    <?php foreach ( $tabs as $slug => $label ) : ?>
      <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'bandstage-settings', 'tab' => $slug ], admin_url( 'admin.php' ) ) ); ?>"
         class="bs-admin-tab <?php echo $active_tab === $slug ? 'bs-admin-tab--active' : ''; ?>">
        <?php echo esc_html( $label ); ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <form method="post" action="options.php" class="bs-admin-settings-form">
    <?php settings_fields( $settings->group( $active_tab ) ); ?>
    <?php include BANDSTAGE_PLUGIN_DIR . "templates/admin/settings/tab-{$active_tab}.php"; ?>
    <?php submit_button(); ?>
  </form>

</div>
