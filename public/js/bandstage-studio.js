/**
 * BandStage Studio — JavaScript front-end
 * @author Pierre Beaubié
 */
/* global bsStudio, jQuery */
( function () {
	'use strict';

	var AJAX  = bsStudio.ajax_url;
	var NONCE = bsStudio.nonce;
	var STR   = bsStudio.strings;

	// -----------------------------------------------------------------------
	// TOAST
	// -----------------------------------------------------------------------
	function toast( msg, type ) {
		var el = document.getElementById( 'bss-toast' );
		if ( ! el ) return;
		el.textContent = msg;
		el.className = 'bss-toast bss-toast--' + ( type || 'success' ) + ' is-show';
		clearTimeout( el._t );
		el._t = setTimeout( function () {
			el.classList.remove( 'is-show' );
		}, 3000 );
	}

	// -----------------------------------------------------------------------
	// AJAX helper
	// -----------------------------------------------------------------------
	function post( action, data, cb ) {
		var fd = new FormData();
		fd.append( 'action', action );
		fd.append( 'nonce',  NONCE );
		Object.keys( data ).forEach( function ( k ) {
			if ( data[k] !== null && data[k] !== undefined ) {
				fd.append( k, data[k] );
			}
		} );
		fetch( AJAX, { method: 'POST', body: fd } )
			.then( function (r) { return r.json(); } )
			.then( cb )
			.catch( function () { cb( { success: false, data: { message: STR.error } } ); } );
	}

	// -----------------------------------------------------------------------
	// UPLOAD IMAGE (mobile camera + galerie)
	// -----------------------------------------------------------------------
	var thumbFile = document.getElementById( 'bss-thumb-file' );
	var thumbPrev = document.getElementById( 'bss-thumb-preview' );
	var thumbId   = document.getElementById( 'bss-thumb-id' );

	if ( thumbPrev && thumbFile ) {
		thumbPrev.addEventListener( 'click', function () { thumbFile.click(); } );

		thumbFile.addEventListener( 'change', function () {
			if ( ! thumbFile.files.length ) return;
			var file   = thumbFile.files[0];
			var reader = new FileReader();
			reader.onload = function (e) {
				thumbPrev.style.backgroundImage = 'url(' + e.target.result + ')';
				thumbPrev.className = 'bss-thumb-preview';
				thumbPrev.innerHTML = '<button type="button" class="bss-thumb-remove" id="bss-thumb-remove">×</button>';
				document.getElementById('bss-thumb-remove').addEventListener('click', function(ev) {
					ev.stopPropagation();
					removeThumbnail();
				});
			};
			reader.readAsDataURL( file );
			// Upload en arrière-plan via FormData WP media.
			uploadImage( file );
		} );
	}

	// Supprimer la miniature.
	document.addEventListener( 'click', function (e) {
		if ( e.target && e.target.id === 'bss-thumb-remove' ) {
			e.preventDefault();
			removeThumbnail();
		}
	} );

	function removeThumbnail() {
		if ( thumbId ) thumbId.value = '0';
		if ( thumbPrev ) {
			thumbPrev.style.backgroundImage = '';
			thumbPrev.className = 'bss-thumb-empty';
			thumbPrev.innerHTML = '<div class="bss-thumb-empty__icon">🖼</div><div class="bss-thumb-empty__label">Ajouter une image</div>';
			thumbPrev.addEventListener( 'click', function () { if (thumbFile) thumbFile.click(); } );
		}
	}

	function uploadImage( file ) {
		var fd = new FormData();
		fd.append( 'action',   'bs_upload_studio_image' );
		fd.append( 'nonce',    NONCE );
		fd.append( 'file',     file );
		fetch( AJAX, { method: 'POST', body: fd } )
			.then( function(r) { return r.json(); } )
			.then( function(res) {
				if ( res.success && thumbId ) {
					thumbId.value = res.data.attachment_id;
				}
			} );
	}

	// -----------------------------------------------------------------------
	// TYPE PARTENAIRE — sélecteur
	// -----------------------------------------------------------------------
	document.querySelectorAll( '.bss-type-option' ).forEach( function (opt) {
		opt.addEventListener( 'click', function () {
			document.querySelectorAll( '.bss-type-option' ).forEach( function (o) {
				o.classList.remove( 'is-sel' );
			} );
			opt.classList.add( 'is-sel' );
			var radio = opt.querySelector( 'input[type="radio"]' );
			if ( radio ) radio.checked = true;
		} );
	} );

	// -----------------------------------------------------------------------
	// BARRE DE FORMATAGE (actu-edit)
	// -----------------------------------------------------------------------
	document.querySelectorAll( '.bss-fmt-btn' ).forEach( function (btn) {
		btn.addEventListener( 'click', function () {
			var cmd = btn.dataset.cmd;
			if ( 'createLink' === cmd ) {
				var url = prompt( 'URL du lien :' );
				if ( url ) document.execCommand( 'createLink', false, url );
			} else {
				document.execCommand( cmd, false, null );
			}
			document.getElementById( 'bss-content' ) &&
			document.getElementById( 'bss-content' ).focus();
		} );
	} );

	// -----------------------------------------------------------------------
	// SAUVEGARDE — Actualité
	// -----------------------------------------------------------------------
	function getNewsData( status ) {
		var content = document.getElementById( 'bss-content' );
		return {
			post_id:  ( document.getElementById( 'bss-post-id' )  || {} ).value || '0',
			title:    ( document.getElementById( 'bss-title' )     || {} ).value || '',
			excerpt:  ( document.getElementById( 'bss-excerpt' )   || {} ).value || '',
			content:  content ? content.innerHTML : '',
			status:   status,
			thumb_id: ( document.getElementById( 'bss-thumb-id' )  || {} ).value || '0',
		};
	}

	function setSaving( isSaving ) {
		var pub  = document.getElementById( 'bss-publish' );
		var dft  = document.getElementById( 'bss-save-draft' );
		if ( pub ) pub.disabled = isSaving;
		if ( dft ) dft.disabled = isSaving;
		if ( pub && isSaving ) pub.textContent = STR.saving;
	}

	var publishBtn = document.getElementById( 'bss-publish' );
	var draftBtn   = document.getElementById( 'bss-save-draft' );

	if ( publishBtn ) {
		publishBtn.addEventListener( 'click', function () {
			var data = getNewsData( 'publish' );
			if ( ! data.title.trim() ) { toast( 'Le titre est obligatoire.', 'error' ); return; }
			setSaving( true );
			post( 'bs_save_news', data, function (res) {
				setSaving( false );
				if ( res.success ) {
					toast( res.data.message, 'success' );
					if ( document.getElementById( 'bss-post-id' ) && res.data.post_id ) {
						document.getElementById( 'bss-post-id' ).value = res.data.post_id;
					}
					var badge = document.getElementById( 'bss-status-badge' );
					if ( badge ) { badge.textContent = 'Publiée'; badge.className = 'bss-badge bss-badge--pub'; }
					publishBtn.textContent = 'Mettre à jour';
				} else {
					toast( ( res.data && res.data.message ) || STR.error, 'error' );
				}
			} );
		} );
	}

	if ( draftBtn ) {
		draftBtn.addEventListener( 'click', function () {
			var data = getNewsData( 'draft' );
			setSaving( true );
			post( 'bs_save_news', data, function (res) {
				setSaving( false );
				toast( res.success ? ( res.data.message || 'Brouillon enregistré.' ) : STR.error,
				       res.success ? 'success' : 'error' );
				if ( res.success && res.data.post_id && document.getElementById( 'bss-post-id' ) ) {
					document.getElementById( 'bss-post-id' ).value = res.data.post_id;
				}
			} );
		} );
	}

	// -----------------------------------------------------------------------
	// SAUVEGARDE — Partenaire
	// -----------------------------------------------------------------------
	var savePartBtn   = document.getElementById( 'bss-save-part' );
	var deletePartBtn = document.getElementById( 'bss-delete-part' );

	if ( savePartBtn ) {
		savePartBtn.addEventListener( 'click', function () {
			var title = ( document.getElementById( 'bss-title' ) || {} ).value || '';
			if ( ! title.trim() ) { toast( 'Le nom est obligatoire.', 'error' ); return; }

			var selType = document.querySelector( 'input[name="bss-type"]:checked' );
			var featuredBtn = document.getElementById( 'bss-featured' );
			var contentEl = document.getElementById( 'bss-content' );

			savePartBtn.disabled = true;
			savePartBtn.textContent = STR.saving;

			post( 'bs_save_partenaire', {
				post_id:  ( document.getElementById( 'bss-post-id' )  || {} ).value || '0',
				title:    title,
				url:      ( document.getElementById( 'bss-url' )      || {} ).value || '',
				tel:      ( document.getElementById( 'bss-tel' )      || {} ).value || '',
				adresse:  ( document.getElementById( 'bss-adresse' )  || {} ).value || '',
				ville:    ( document.getElementById( 'bss-ville' )    || {} ).value || '',
				content:  contentEl ? contentEl.value : '',
				featured: featuredBtn && featuredBtn.classList.contains('is-on') ? '1' : '0',
				type_id:  selType ? selType.value : '0',
				thumb_id: ( document.getElementById( 'bss-thumb-id' ) || {} ).value || '0',
			}, function (res) {
				savePartBtn.disabled = false;
				savePartBtn.textContent = 'Enregistrer';
				if ( res.success ) {
					toast( res.data.message, 'success' );
					if ( res.data.post_id && document.getElementById( 'bss-post-id' ) ) {
						document.getElementById( 'bss-post-id' ).value = res.data.post_id;
					}
					// Redirect vers dashboard après 1.5s
					setTimeout( function () {
						window.location.href = bsStudio.studio_url;
					}, 1500 );
				} else {
					toast( ( res.data && res.data.message ) || STR.error, 'error' );
				}
			} );
		} );
	}

	if ( deletePartBtn ) {
		deletePartBtn.addEventListener( 'click', function () {
			if ( ! confirm( STR.confirm_delete ) ) return;
			var pid = ( document.getElementById( 'bss-post-id' ) || {} ).value;
			if ( ! pid ) return;
			post( 'bs_delete_partenaire', { post_id: pid }, function (res) {
				if ( res.success ) {
					toast( res.data.message, 'success' );
					setTimeout( function () { window.location.href = bsStudio.studio_url; }, 1000 );
				} else {
					toast( ( res.data && res.data.message ) || STR.error, 'error' );
				}
			} );
		} );
	}

	// -----------------------------------------------------------------------
	// SUPPRIMER depuis le dashboard (bouton poubelle)
	// -----------------------------------------------------------------------
	document.querySelectorAll( '.bss-action-btn--del' ).forEach( function (btn) {
		btn.addEventListener( 'click', function () {
			if ( ! confirm( STR.confirm_delete ) ) return;
			var id   = btn.dataset.id;
			var type = btn.dataset.type;
			var action = 'partenaire' === type ? 'bs_delete_partenaire' : 'bs_delete_news';
			post( action, { post_id: id }, function (res) {
				if ( res.success ) {
					var row = document.getElementById( 'bss-item-' + type + '-' + id );
					if ( row ) {
						row.style.transition = 'opacity .3s, transform .3s';
						row.style.opacity    = '0';
						row.style.transform  = 'translateX(20px)';
						setTimeout( function () { row.remove(); }, 320 );
					}
					toast( res.data.message, 'success' );
				} else {
					toast( ( res.data && res.data.message ) || STR.error, 'error' );
				}
			} );
		} );
	} );

	// -----------------------------------------------------------------------
	// LIRE LA SUITE — news cards
	// -----------------------------------------------------------------------
	document.querySelectorAll( '.bss-read-more' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var target = document.getElementById( btn.dataset.target );
			if ( ! target ) return;
			target.classList.toggle( 'is-open' );
			btn.textContent = target.classList.contains( 'is-open' )
				? 'Réduire ↑'
				: 'Lire la suite…';
		} );
	} );

} )();

	// -----------------------------------------------------------------------
	// SAUVEGARDE — Titre (répertoire)
	// -----------------------------------------------------------------------
	var saveTitreBtn   = document.getElementById( 'bss-save-titre' );
	var deleteTitreBtn = document.getElementById( 'bss-delete-titre' );

	if ( saveTitreBtn ) {
		saveTitreBtn.addEventListener( 'click', function () {
			var title = ( document.getElementById( 'bss-title' ) || {} ).value || '';
			if ( ! title.trim() ) { toast( 'Le titre est obligatoire.', 'error' ); return; }

			var selType = document.querySelector( 'input[name="bss-type"]:checked' );

			saveTitreBtn.disabled = true;
			saveTitreBtn.textContent = STR.saving;

			post( 'bs_save_titre', {
				post_id: ( document.getElementById( 'bss-post-id' ) || {} ).value || '0',
				title:   title,
				type:    selType ? selType.value : 'reprise',
				artiste: ( document.getElementById( 'bss-artiste' ) || {} ).value || '',
				annee:   ( document.getElementById( 'bss-annee' )   || {} ).value || '',
				notes:   ( document.getElementById( 'bss-notes' )   || {} ).value || '',
			}, function ( res ) {
				saveTitreBtn.disabled = false;
				saveTitreBtn.textContent = 'Enregistrer';
				if ( res.success ) {
					toast( res.data.message, 'success' );
					setTimeout( function () {
						window.location.href = bsStudio.studio_url;
					}, 1000 );
				} else {
					toast( ( res.data && res.data.message ) || STR.error, 'error' );
				}
			} );
		} );
	}

	if ( deleteTitreBtn ) {
		deleteTitreBtn.addEventListener( 'click', function () {
			if ( ! confirm( STR.confirm_delete ) ) return;
			var pid = ( document.getElementById( 'bss-post-id' ) || {} ).value;
			if ( ! pid ) return;
			post( 'bs_delete_titre', { post_id: pid }, function ( res ) {
				if ( res.success ) {
					toast( res.data.message, 'success' );
					setTimeout( function () { window.location.href = bsStudio.studio_url; }, 900 );
				} else {
					toast( ( res.data && res.data.message ) || STR.error, 'error' );
				}
			} );
		} );
	}
