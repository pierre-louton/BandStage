<?php
/**
 * Admin — modération Tchache.
 *
 * @var \BandStage\Domain\Tchache\Message[] $pending
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap bs-admin-wrap">
  <h1><?php esc_html_e( 'Modération Tchache', 'bandstage' ); ?></h1>

  <?php if ( empty( $pending ) ) : ?>
    <p><?php esc_html_e( 'Aucun message en attente.', 'bandstage' ); ?></p>
  <?php else : ?>
    <table class="wp-list-table widefat fixed striped">
      <thead>
        <tr>
          <th><?php esc_html_e( 'Auteur', 'bandstage' ); ?></th>
          <th><?php esc_html_e( 'Message', 'bandstage' ); ?></th>
          <th><?php esc_html_e( 'Date', 'bandstage' ); ?></th>
          <th><?php esc_html_e( 'Actions', 'bandstage' ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $pending as $msg ) : ?>
          <tr id="bs-msg-<?php echo esc_attr( $msg->id ); ?>">
            <td><?php echo esc_html( $msg->display_name ); ?></td>
            <td><?php echo esc_html( $msg->content ); ?></td>
            <td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $msg->created_at ) ) ); ?></td>
            <td>
              <?php $nonce = wp_create_nonce( BANDSTAGE_NONCE ); ?>
              <button class="button button-primary js-moderate"
                      data-id="<?php echo esc_attr( $msg->id ); ?>"
                      data-action="approve"
                      data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <?php esc_html_e( '✓ Approuver', 'bandstage' ); ?>
              </button>
              <button class="button js-moderate"
                      data-id="<?php echo esc_attr( $msg->id ); ?>"
                      data-action="spam"
                      data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <?php esc_html_e( 'Spam', 'bandstage' ); ?>
              </button>
              <button class="button button-link-delete js-moderate"
                      data-id="<?php echo esc_attr( $msg->id ); ?>"
                      data-action="delete"
                      data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <?php esc_html_e( 'Supprimer', 'bandstage' ); ?>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
