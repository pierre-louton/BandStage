<?php
/**
 * Entité Concert (table bandstage_concerts).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Concerts;

defined( 'ABSPATH' ) || exit;

class Concert {

    public function __construct(
        public readonly int    $id,
        public readonly string $titre,
        public readonly string $date_debut,
        public readonly string $date_fin,
        public readonly string $horaires,
        public readonly string $nom_lieu,
        public readonly string $numero,
        public readonly string $nom_voie,
        public readonly string $code_postal,
        public readonly string $ville,
        /** @var int[] IDs des partenaires associés */
        public readonly array  $partenaire_ids,
        /** Noms des partenaires séparés par des virgules (chargé par get_upcoming/get_all). */
        public readonly string $partenaire_names = '',
    ) {}

    public static function from_db_row( object $row, array $partenaire_ids = [] ): self {
        return new self(
            id:               (int)    $row->id,
            titre:            (string) $row->titre,
            date_debut:       (string) $row->date_debut,
            date_fin:         (string) ( $row->date_fin ?? '' ),
            horaires:         (string) ( $row->horaires ?? '' ),
            nom_lieu:         (string) ( $row->nom_lieu ?? '' ),
            numero:           (string) ( $row->numero ?? '' ),
            nom_voie:         (string) ( $row->nom_voie ?? '' ),
            code_postal:      (string) ( $row->code_postal ?? '' ),
            ville:            (string) ( $row->ville ?? '' ),
            partenaire_ids:   $partenaire_ids,
            partenaire_names: (string) ( $row->partenaire_names ?? '' ),
        );
    }

    /** Date(s) formatée(s) pour l'affichage. */
    public function dates_formatted(): string {
        $d = date_i18n( 'd/m/Y', strtotime( $this->date_debut ) );
        if ( $this->date_fin && $this->date_fin !== $this->date_debut ) {
            $d .= ' – ' . date_i18n( 'd/m/Y', strtotime( $this->date_fin ) );
        }
        return $d;
    }

    /** Adresse du lieu formatée sur une ligne. */
    public function address_full(): string {
        $parts = array_filter( [ $this->numero, $this->nom_voie, $this->code_postal, $this->ville ] );
        return implode( ' ', $parts );
    }
}
