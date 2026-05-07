<?php
/**
 * Upload de logo partenaire vers wp-content/uploads/bandstage/logos/.
 * Hors médiathèque WordPress.
 *
 * @package BandStage
 * @author  Pierre Beaubié
 */

namespace BandStage\Domain\Media;

defined( 'ABSPATH' ) || exit;

class LogoUploader {

    private const MAX_SIZE  = 2 * 1024 * 1024; // 2 Mo
    private const ALLOWED   = [ 'jpg', 'jpeg', 'png', 'webp' ];
    private const SUBDIR    = 'bandstage/logos';

    /**
     * Déplace le fichier uploadé vers le dossier logos.
     *
     * @param array $file  Entrée de $_FILES (logo).
     * @return string|\WP_Error  Chemin relatif à wp_upload_dir()['basedir'], ex: "bandstage/logos/abc123.jpg"
     */
    public static function upload( array $file ): string|\WP_Error {
        if ( ! empty( $file['error'] ) ) {
            return new \WP_Error( 'upload_error', __( 'Erreur lors de l\'upload.', 'bandstage' ) );
        }

        if ( $file['size'] > self::MAX_SIZE ) {
            return new \WP_Error( 'file_too_large', __( 'Le fichier dépasse 2 Mo.', 'bandstage' ) );
        }

        $check = wp_check_filetype( $file['name'] );
        $ext   = strtolower( $check['ext'] ?? '' );

        if ( ! in_array( $ext, self::ALLOWED, true ) ) {
            return new \WP_Error( 'invalid_type', __( 'Type de fichier non autorisé (jpg, png, webp, svg).', 'bandstage' ) );
        }

        $upload_dir = wp_upload_dir();
        $logos_dir  = trailingslashit( $upload_dir['basedir'] ) . self::SUBDIR;

        if ( ! wp_mkdir_p( $logos_dir ) ) {
            return new \WP_Error( 'mkdir_failed', __( 'Impossible de créer le dossier de logos.', 'bandstage' ) );
        }

        $filename  = wp_unique_filename( $logos_dir, sanitize_file_name( $file['name'] ) );
        $dest_path = $logos_dir . '/' . $filename;

        if ( ! move_uploaded_file( $file['tmp_name'], $dest_path ) ) {
            return new \WP_Error( 'move_failed', __( 'Impossible de déplacer le fichier.', 'bandstage' ) );
        }

        return self::SUBDIR . '/' . $filename;
    }

    /**
     * Supprime un logo à partir de son chemin relatif stocké en DB.
     *
     * @param string $relative_path  Ex: "bandstage/logos/abc123.jpg"
     */
    public static function delete( string $relative_path ): void {
        if ( empty( $relative_path ) ) {
            return;
        }
        $upload_dir   = wp_upload_dir();
        $allowed_base = realpath( trailingslashit( $upload_dir['basedir'] ) . self::SUBDIR );
        $full_path    = realpath( trailingslashit( $upload_dir['basedir'] ) . ltrim( $relative_path, '/' ) );

        if ( $full_path === false || $allowed_base === false ) {
            return;
        }
        if ( str_starts_with( $full_path, $allowed_base . DIRECTORY_SEPARATOR ) && file_exists( $full_path ) ) {
            wp_delete_file( $full_path );
        }
    }

    /**
     * Retourne l'URL publique d'un logo.
     *
     * @param string $relative_path  Ex: "bandstage/logos/abc123.jpg"
     */
    public static function url( string $relative_path ): string {
        if ( empty( $relative_path ) ) {
            return '';
        }
        $upload_dir = wp_upload_dir();
        return trailingslashit( $upload_dir['baseurl'] ) . ltrim( $relative_path, '/' );
    }
}
