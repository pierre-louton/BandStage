<?php
/**
 * Entité Morceau (table bandstage_repertoire).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Repertoire;

defined( 'ABSPATH' ) || exit;

class Morceau {

    public function __construct(
        public readonly int    $id,
        public readonly string $nom_artiste,
        public readonly string $nom_morceau,
        public readonly string $remarque,
        public readonly string $icone_artiste,
        /** @var int[] IDs des styles associés */
        public readonly array  $style_ids,
        /** Noms des styles séparés par des virgules (chargé via GROUP_CONCAT). */
        public readonly string $style_names = '',
    ) {}

    public static function from_db_row( object $row, array $style_ids = [] ): self {
        return new self(
            id:            (int)    $row->id,
            nom_artiste:   (string) $row->nom_artiste,
            nom_morceau:   (string) $row->nom_morceau,
            remarque:      (string) ( $row->remarque      ?? '' ),
            icone_artiste: (string) ( $row->icone_artiste ?? '' ),
            style_ids:     $style_ids,
            style_names:   (string) ( $row->style_names   ?? '' ),
        );
    }
}
