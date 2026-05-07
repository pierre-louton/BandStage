<?php
/**
 * Entité Message — Tchache.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Tchache;

defined( 'ABSPATH' ) || exit;

class Message {

	public function __construct(
		public readonly int    $id,
		public readonly int    $user_id,
		public readonly string $content,
		public readonly string $status,
		public readonly string $created_at,
		public readonly string $display_name,
		public readonly string $avatar_url,
		public readonly string $initials,
	) {}
}
