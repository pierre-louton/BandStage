<?php
/**
 * Partial — bulle message Tchache.
 *
 * @var \BandStage\Domain\Tchache\Message $msg
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

$is_own = is_user_logged_in() && (int) $msg->user_id === get_current_user_id();
?>
<div class="bs-tc-msg <?php echo $is_own ? 'bs-tc-msg--own' : ''; ?>"
     data-id="<?php echo esc_attr( $msg->id ); ?>">

  <div class="bs-tc-msg__avatar">
    <?php if ( $msg->avatar_url ) : ?>
      <img src="<?php echo esc_url( $msg->avatar_url ); ?>"
           alt="<?php echo esc_attr( $msg->display_name ); ?>">
    <?php else : ?>
      <div class="bs-tc-msg__initials"><?php echo esc_html( $msg->initials ); ?></div>
    <?php endif; ?>
  </div>

  <div class="bs-tc-msg__body">
    <span class="bs-tc-msg__author"><?php echo esc_html( $msg->display_name ); ?></span>
    <p class="bs-tc-msg__text"><?php echo esc_html( $msg->content ); ?></p>
    <time class="bs-tc-msg__time"
          datetime="<?php echo esc_attr( $msg->created_at ); ?>">
      <?php echo esc_html( human_time_diff( strtotime( $msg->created_at ) ) . ' ' . __( 'ago', 'bandstage' ) ); ?>
    </time>
  </div>

</div>
