<?php
/**
 * Contrôleur public — comportements globaux front-end.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Public;

defined( 'ABSPATH' ) || exit;

class PublicController {

	/**
	 * Cache la barre admin aux musiciens (Auteur).
	 * Seuls les admins (manage_options) la voient.
	 */
	public function maybe_hide_admin_bar( bool $show ): bool {
		return current_user_can( 'manage_options' );
	}
}
