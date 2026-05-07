<?php
/**
 * Admin — tableau des membres WP.
 *
 * @var \BandStage\Domain\Members\Member[] $members
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap bs-admin-wrap">
  <h1><?php esc_html_e( 'Membres du site', 'bandstage' ); ?></h1>
  <table class="wp-list-table widefat fixed striped">
    <thead>
      <tr>
        <th width="50"><?php esc_html_e( 'Avatar', 'bandstage' ); ?></th>
        <th><?php esc_html_e( 'Nom', 'bandstage' ); ?></th>
        <th><?php esc_html_e( 'Email', 'bandstage' ); ?></th>
        <th><?php esc_html_e( 'Instrument', 'bandstage' ); ?></th>
        <th><?php esc_html_e( 'Ville', 'bandstage' ); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $members as $member ) : ?>
        <tr>
          <td>
            <?php if ( $member->avatar_url ) : ?>
              <img src="<?php echo esc_url( $member->avatar_url ); ?>"
                   width="40" height="40" style="border-radius:50%"
                   alt="<?php echo esc_attr( $member->display_name ); ?>">
            <?php else : ?>
              <div style="width:40px;height:40px;border-radius:50%;background:var(--wp-admin-theme-color,#2271b1);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;">
                <?php echo esc_html( $member->initials() ); ?>
              </div>
            <?php endif; ?>
          </td>
          <td><?php echo esc_html( $member->display_name ); ?></td>
          <td><?php echo esc_html( $member->email ); ?></td>
          <td><?php echo esc_html( $member->instrument ); ?></td>
          <td><?php echo esc_html( $member->city ); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
