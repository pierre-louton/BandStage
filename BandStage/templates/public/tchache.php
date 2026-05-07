<?php
/**
 * [bandstage_tchache] — mini-forum.
 * Lecture publique ; écriture réservée aux connectés.
 *
 * @var \BandStage\Domain\Tchache\Message[] $messages
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$enabled    = (bool) get_option( 'bs_tchache_enabled', '1' );
$max_length = (int) get_option( 'bs_tchache_max_length', 500 );
$logged_in  = is_user_logged_in();
?>
<div class="bs-tc-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php esc_html_e( 'Tchache', 'bandstage' ); ?></h1>
  </header>

  <?php if ( ! $enabled ) : ?>
    <p class="bs-tc-disabled"><?php esc_html_e( 'La Tchache est momentanément désactivée.', 'bandstage' ); ?></p>

  <?php else : ?>

    <!-- Zone messages -->
    <div class="bs-tc-messages" id="bs-tc-messages">
      <?php if ( empty( $messages ) ) : ?>
        <p class="bs-tc-empty"><?php esc_html_e( 'Aucun message pour l\'instant. Soyez le premier !', 'bandstage' ); ?></p>
      <?php else : ?>
        <?php foreach ( $messages as $msg ) : ?>
          <?php include BANDSTAGE_PLUGIN_DIR . 'templates/public/partials/tchache-message.php'; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Zone de saisie -->
    <?php if ( $logged_in ) : ?>
      <form class="bs-tc-form" id="bs-tc-form" novalidate>
        <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
        <textarea
          name="content"
          class="bs-tc-form__textarea"
          placeholder="<?php esc_attr_e( 'Votre message…', 'bandstage' ); ?>"
          maxlength="<?php echo esc_attr( $max_length ); ?>"
          rows="3"
          required></textarea>
        <div class="bs-tc-form__footer">
          <span class="bs-tc-form__counter">
            <span id="bs-tc-count">0</span>/<?php echo esc_html( $max_length ); ?>
          </span>
          <button type="submit" class="bss-btn bss-btn--primary">
            <?php esc_html_e( 'Envoyer', 'bandstage' ); ?>
          </button>
        </div>
      </form>

    <?php else : ?>
      <!-- Visiteur non connecté — zone grisée avec lien connexion (exception tchache autorisée) -->
      <div class="bs-tc-locked">
        <p class="bs-tc-locked__msg">
          <?php esc_html_e( 'Connectez-vous pour participer à la Tchache.', 'bandstage' ); ?>
        </p>
        <a href="<?php echo esc_url( get_permalink( (int) get_option( 'bs_page_profil' ) ) ); ?>"
           class="bss-btn bss-btn--primary">
          <?php esc_html_e( 'Se connecter', 'bandstage' ); ?>
        </a>
      </div>
    <?php endif; ?>

  <?php endif; ?>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
