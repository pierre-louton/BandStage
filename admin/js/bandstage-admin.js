/**
 * BandStage — Administration JavaScript
 * @author Pierre Beaubié
 * Dépendances : jQuery, wp-color-picker, jquery-ui-sortable
 */
/* global jQuery, wp, bandstageAdmin */
( function ( $ ) {
	'use strict';

	$( document ).ready( function () {

		// -------------------------------------------------------------------
		// COLOR PICKERS — init avec palettes prédéfinies BandStage
		// -------------------------------------------------------------------
		var bsPalette = [
			'#1535A8', '#020828', '#D4A820', '#EFC84A',
			'#F0E6CE', '#0A1448', '#5CC8C8', '#5DCAA5',
			'#3A1A0A', '#0A2A1A', '#2A1440',
		];

		$( '.bs-color-picker' ).wpColorPicker( {
			palettes: bsPalette,
			change: function ( event, ui ) {
				// Déclenche l'update de la preview de fond si c'est une couleur de fond.
				var id = $( this ).attr( 'id' );
				if ( id === 'bs_bg_color_start' || id === 'bs_bg_color_end' || id === 'bs_bg_angle' ) {
					updateBgPreview();
				}
			},
		} );

		// -------------------------------------------------------------------
		// SLIDERS RANGE — valeur dynamique + gradient de fond
		// -------------------------------------------------------------------
		function initSlider( el ) {
			var $el  = $( el );
			var min  = parseFloat( $el.attr( 'min' ) )  || 0;
			var max  = parseFloat( $el.attr( 'max' ) )  || 100;
			var val  = parseFloat( $el.val() )           || min;
			var pct  = ( ( val - min ) / ( max - min ) ) * 100;

			// Affiche la valeur courante dans le span adjacent.
			var $display = $el.next( '.bs-range-val, #bs-radius-val, #bs-speed-val' );
			if ( ! $display.length ) {
				$display = $el.siblings( '.bs-range-val, #bs-radius-val, #bs-speed-val' ).first();
			}
			if ( $display.length ) $display.text( val );

			// Peint le remplissage à gauche du thumb.
			el.style.setProperty( '--val', pct + '%' );

			$el.on( 'input change', function () {
				var v   = parseFloat( this.value );
				var p   = ( ( v - min ) / ( max - min ) ) * 100;
				this.style.setProperty( '--val', p + '%' );

				var $d = $( this ).next( '.bs-range-val, #bs-radius-val, #bs-speed-val' );
				if ( ! $d.length ) $d = $( this ).siblings( '.bs-range-val, #bs-radius-val, #bs-speed-val' ).first();
				if ( $d.length ) $d.text( v );

				// Update preview fond si c'est l'angle.
				if ( this.id === 'bs_bg_angle' ) updateBgPreview();
			} );
		}

		$( 'input[type="range"]' ).each( function () { initSlider( this ); } );

		// -------------------------------------------------------------------
		// PREVIEW LIVE du dégradé de fond
		// -------------------------------------------------------------------
		function updateBgPreview() {
			var start = $( '#bs_bg_color_start' ).val() || '#1535A8';
			var end   = $( '#bs_bg_color_end' ).val()   || '#020828';
			var angle = parseInt( $( '#bs_bg_angle' ).val(), 10 ) || 168;
			$( '#bs-bg-preview' ).css(
				'background',
				'linear-gradient(' + angle + 'deg, ' + start + ', ' + end + ')'
			);
		}
		$( '#bs_bg_color_start, #bs_bg_color_end' ).on( 'change', updateBgPreview );
		$( '#bs_bg_angle' ).on( 'input change', updateBgPreview );
		updateBgPreview();

		// -------------------------------------------------------------------
		// PREVIEW LIVE du nom de groupe
		// -------------------------------------------------------------------
		$( '#bs_band_name' ).on( 'input', function () {
			$( '#bs-name-preview' ).text( $( this ).val() || 'Mon Groupe' );
		} );

		// -------------------------------------------------------------------
		// MEDIA UPLOADER
		// -------------------------------------------------------------------
		$( document ).on( 'click', '.bs-media-select', function ( e ) {
			e.preventDefault();
			var btn       = $( this );
			var targetId  = btn.data( 'target' );
			var previewId = btn.data( 'preview' );

			var frame = wp.media( {
				title:    bandstageAdmin.strings.select_image,
				button:   { text: bandstageAdmin.strings.use_image },
				library:  { type: 'image' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$( '#' + targetId ).val( attachment.id );
				var preview = $( '#' + previewId );
				preview.removeClass( 'is-empty' );
				var imgUrl = ( attachment.sizes && attachment.sizes.thumbnail )
					? attachment.sizes.thumbnail.url
					: attachment.url;
				preview.html( '<img src="' + imgUrl + '" alt="">' );
			} );

			frame.open();
		} );

		$( document ).on( 'click', '.bs-media-remove', function ( e ) {
			e.preventDefault();
			$( '#' + $( this ).data( 'target' ) ).val( 0 );
			$( '#' + $( this ).data( 'preview' ) ).addClass( 'is-empty' ).html( '' );
		} );

		// -------------------------------------------------------------------
		// IMPORT / EXPORT JSON
		// -------------------------------------------------------------------
		$( '#bs-export-btn' ).on( 'click', function () {
			$.post( bandstageAdmin.ajax_url, {
				action: 'bs_export_settings',
				nonce:  bandstageAdmin.nonce,
			}, function ( res ) {
				if ( res.success ) {
					var blob = new Blob( [ res.data.json ], { type: 'application/json' } );
					var url  = URL.createObjectURL( blob );
					var a    = document.createElement( 'a' );
					a.href     = url;
					a.download = 'bandstage-settings.json';
					a.click();
					URL.revokeObjectURL( url );
					bsNotice( 'bs-export-notice', bandstageAdmin.strings.export_success, false );
				}
			} );
		} );

		$( '#bs-import-btn' ).on( 'click', function () {
			var file = document.getElementById( 'bs-import-file' );
			if ( ! file || ! file.files.length ) return;
			var reader = new FileReader();
			reader.onload = function ( e ) {
				$.post( bandstageAdmin.ajax_url, {
					action: 'bs_import_settings',
					nonce:  bandstageAdmin.nonce,
					json:   e.target.result,
				}, function ( res ) {
					if ( res.success ) {
						bsNotice( 'bs-import-notice', bandstageAdmin.strings.import_success, false );
						setTimeout( function () { location.reload(); }, 1500 );
					} else {
						bsNotice( 'bs-import-notice', bandstageAdmin.strings.import_error, true );
					}
				} );
			};
			reader.readAsText( file.files[ 0 ] );
		} );

		// -------------------------------------------------------------------
		// HELPER : notice inline animée
		// -------------------------------------------------------------------
		function bsNotice( id, msg, isError ) {
			var el = document.getElementById( id );
			if ( ! el ) return;
			el.textContent  = msg;
			el.className    = 'bs-notice ' + ( isError ? 'bs-notice--err' : 'bs-notice--ok' );
			el.style.display = 'flex';
			setTimeout( function () {
				el.style.opacity = '0';
				el.style.transition = 'opacity .4s';
				setTimeout( function () {
					el.style.display  = 'none';
					el.style.opacity  = '';
					el.style.transition = '';
				}, 400 );
			}, 3500 );
		}

	} );

} )( jQuery );
