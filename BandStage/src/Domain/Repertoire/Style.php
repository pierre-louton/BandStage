<?php
/**
 * Entité Style (table bandstage_references).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Repertoire;

defined( 'ABSPATH' ) || exit;

class Style {

    public function __construct(
        public readonly int    $id,
        public readonly string $nom_style,
        public readonly string $image_url,
    ) {}

    public static function from_db_row( object $row ): self {
        return new self(
            id:        (int)    $row->id,
            nom_style: (string) $row->nom_style,
            image_url: (string) ( $row->image_url ?? '' ),
        );
    }
}
