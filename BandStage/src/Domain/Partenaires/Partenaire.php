<?php
/**
 * Entité Partenaire (table bandstage_partenaires).
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Partenaires;

use BandStage\Domain\Media\LogoUploader;

defined( 'ABSPATH' ) || exit;

class Partenaire {

    public function __construct(
        public readonly int    $id,
        public readonly ?int   $type_id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $logo_path,
        public readonly string $logo_url,
        public readonly string $website,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $numero,
        public readonly string $nom_voie,
        public readonly string $code_postal,
        public readonly string $ville,
        public readonly string $type_name,
        public readonly string $type_slug,
        public readonly string $type_icon,
    ) {}

    public static function from_db_row( object $row ): self {
        $logo_path = (string) ( $row->logo_path ?? '' );
        return new self(
            id:          (int)    $row->id,
            type_id:     isset( $row->type_id ) && $row->type_id !== null ? (int) $row->type_id : null,
            name:        (string) $row->name,
            description: (string) ( $row->description ?? '' ),
            logo_path:   $logo_path,
            logo_url:    LogoUploader::url( $logo_path ),
            website:     (string) ( $row->website ?? '' ),
            email:       (string) ( $row->email ?? '' ),
            phone:       (string) ( $row->phone ?? '' ),
            numero:      (string) ( $row->numero ?? '' ),
            nom_voie:    (string) ( $row->nom_voie ?? '' ),
            code_postal: (string) ( $row->code_postal ?? '' ),
            ville:       (string) ( $row->ville ?? '' ),
            type_name:   (string) ( $row->type_name ?? '' ),
            type_slug:   (string) ( $row->type_slug ?? '' ),
            type_icon:   (string) ( $row->type_icon ?? '' ),
        );
    }

    /** Adresse complète formatée sur une ligne. */
    public function address_full(): string {
        $parts = array_filter( [ $this->numero, $this->nom_voie, $this->code_postal, $this->ville ] );
        return implode( ' ', $parts );
    }
}
