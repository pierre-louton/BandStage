<?php
/**
 * Entité Notification.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Notifications;

defined( 'ABSPATH' ) || exit;

class Notification {

	public function __construct(
		public readonly int    $id,
		public readonly int    $user_id,
		public readonly string $type,
		public readonly array  $payload,
		public readonly ?string $read_at,
		public readonly string $created_at,
	) {}
}
