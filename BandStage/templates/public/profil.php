<?php
/**
 * [bandstage_profil] — vue membre connecté.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

defined( 'ABSPATH' ) || exit;

use BandStage\Domain\Members\Member;
use BandStage\Domain\Members\MemberService;

$service = new MemberService();
$member  = $service->get( get_current_user_id() );

if ( ! $member ) {
	return;
}
?>
<div class="bs-pr-wrap">

  <header class="bs-header">
    <h1 class="bs-header__brand"><?php esc_html_e( 'Mon compte', 'bandstage' ); ?></h1>
  </header>

  <div class="bs-pr-avatar">
    <?php if ( $member->avatar_url ) : ?>
      <img src="<?php echo esc_url( $member->avatar_url ); ?>"
           alt="<?php echo esc_attr( $member->display_name ); ?>"
           class="bs-pr-avatar__img">
    <?php else : ?>
      <div class="bs-pr-avatar__initials"><?php echo esc_html( $member->initials() ); ?></div>
    <?php endif; ?>
    <h2 class="bs-pr-avatar__name"><?php echo esc_html( $member->display_name ); ?></h2>
  </div>

  <form class="bss-form" id="bss-profil-form"
        data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
    <?php wp_nonce_field( BANDSTAGE_NONCE, 'nonce' ); ?>
    <input type="hidden" name="action" value="bs_member_save_profile">

    <div class="bss-form__group">
      <label for="bs-pr-instrument" class="bss-form__label"><?php esc_html_e( 'Instrument / Rôle', 'bandstage' ); ?></label>
      <input type="text" id="bs-pr-instrument" name="instrument"
             class="bss-form__input"
             value="<?php echo esc_attr( $member->instrument ); ?>"
             placeholder="<?php esc_attr_e( 'Guitare, Chant…', 'bandstage' ); ?>">
    </div>

    <div class="bss-form__group">
      <label for="bs-pr-city" class="bss-form__label"><?php esc_html_e( 'Ville', 'bandstage' ); ?></label>
      <input type="text" id="bs-pr-city" name="city"
             class="bss-form__input"
             value="<?php echo esc_attr( $member->city ); ?>">
    </div>

    <div class="bss-form__group">
      <label for="bs-pr-bio" class="bss-form__label"><?php esc_html_e( 'Bio', 'bandstage' ); ?></label>
      <textarea id="bs-pr-bio" name="bio"
                class="bss-form__textarea"
                rows="4"><?php echo esc_textarea( $member->bio ); ?></textarea>
    </div>

    <div class="bss-form__actions">
      <button type="submit" class="bss-btn bss-btn--primary">
        <?php esc_html_e( 'Enregistrer', 'bandstage' ); ?>
      </button>
      <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"
         class="bss-btn bss-btn--ghost">
        <?php esc_html_e( 'Déconnexion', 'bandstage' ); ?>
      </a>
    </div>
  </form>

  <div class="bss-toast" id="bs-toast" aria-live="polite"></div>

</div>
