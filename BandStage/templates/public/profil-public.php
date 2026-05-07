<?php
/**
 * [bandstage_profil] — vue visiteur non connecté.
 * Affiche un formulaire de connexion aux couleurs BandStage.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$band_name = esc_html( get_option( 'bs_band_name', 'BandStage' ) );
?>
<div class="bs-pr-wrap">
  <div class="bs-pr-login">

    <div class="bs-pr-login__header">
      <span class="bs-pr-login__logo">🎸</span>
      <h1 class="bs-pr-login__title"><?php echo $band_name; ?></h1>
      <p class="bs-pr-login__subtitle">
        <?php esc_html_e( 'Connectez-vous pour accéder à votre espace.', 'bandstage' ); ?>
      </p>
    </div>

    <div class="bs-pr-login__form">
      <?php
      wp_login_form( [
        'redirect'       => get_permalink( (int) get_option( 'bs_page_profil' ) ),
        'form_id'        => 'bs-login-form',
        'label_username' => __( 'Identifiant', 'bandstage' ),
        'label_password' => __( 'Mot de passe', 'bandstage' ),
        'label_remember' => __( 'Se souvenir de moi', 'bandstage' ),
        'label_log_in'   => __( 'Se connecter', 'bandstage' ),
        'remember'       => true,
      ] );
      ?>
      <?php if ( get_option( 'users_can_register' ) ) : ?>
        <p class="bs-pr-login__register">
          <a href="<?php echo esc_url( wp_registration_url() ); ?>">
            <?php esc_html_e( 'Créer un compte', 'bandstage' ); ?>
          </a>
        </p>
      <?php endif; ?>
      <p class="bs-pr-login__lost">
        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
          <?php esc_html_e( 'Mot de passe oublié ?', 'bandstage' ); ?>
        </a>
      </p>
    </div>

  </div>
</div>
