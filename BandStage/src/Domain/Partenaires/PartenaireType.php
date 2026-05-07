<?php
/**
 * Entité PartenaireType — type de partenaire (table bandstage_partenaire_types).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Partenaires;

defined( 'ABSPATH' ) || exit;

class PartenaireType {

    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $icon,
    ) {}

    public static function from_db_row( object $row ): self {
        return new self(
            id:   (int)    $row->id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            icon: (string) $row->icon,
        );
    }
}
