<?php
/**
 * Entité News — actualité du groupe (CPT bs_news).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\News;

defined( 'ABSPATH' ) || exit;

class News {

	public function __construct(
		public readonly int    $id,
		public readonly string $title,
		public readonly string $content,
		public readonly string $status,
		public readonly int    $author_id,
		public readonly string $author_name,
		public readonly string $date,
	) {}

	public static function from_wp_post( \WP_Post $post ): self {
		$author = get_userdata( $post->post_author );
		return new self(
			id:          $post->ID,
			title:       $post->post_title,
			content:     $post->post_content,
			status:      $post->post_status,
			author_id:   (int) $post->post_author,
			author_name: $author ? $author->display_name : '',
			date:        $post->post_date,
		);
	}
}
