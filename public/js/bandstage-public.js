/**
 * BandStage — JavaScript front-end
 * Gère : panels, toggles, swatches, AJAX auth + reprise + préférences.
 */
/* global bandstage */
( function () {
	'use strict';

	const AJAX  = bandstage.ajax_url;
	const NONCE = bandstage.nonce;

	// -----------------------------------------------------------------------
	// PANELS
	// -----------------------------------------------------------------------
	window.bsShowPanel = function ( panelId, navId ) {
		// Ferme tous les panels
		document.querySelectorAll( '.bs-panel' ).forEach( function ( p ) {
			p.classList.remove( 'is-open' );
			p.setAttribute( 'aria-hidden', 'true' );
		} );
		// Désactive tous les items de nav
		document.querySelectorAll( '.bs-bnav__item' ).forEach( function ( b ) {
			b.classList.remove( 'is-active', 'bs-bnav__item--active' );
		} );

		if ( panelId ) {
			const panel = document.getElementById( panelId );
			if ( panel ) {
				panel.classList.add( 'is-open' );
				panel.setAttribute( 'aria-hidden', 'false' );
				panel.scrollTop = 0;
			}
		}
		if ( navId ) {
			const btn = document.getElementById( navId );
			if ( btn ) {
				btn.classList.add( 'is-active', 'bs-bnav__item--active' );
			}
		}
	};

	// Fermer avec Escape
	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key ) {
			bsShowPanel( null, 'bs-nav-home' );
		}
	} );

	// -----------------------------------------------------------------------
	// TOGGLES
	// -----------------------------------------------------------------------
	window.bsToggle = function ( btn, key ) {
		btn.classList.toggle( 'is-on' );
		const isOn = btn.classList.contains( 'is-on' );
		btn.setAttribute( 'aria-pressed', isOn ? 'true' : 'false' );

		// Persistance locale
		try {
			sessionStorage.setItem( 'bs_pref_' + key, isOn ? '1' : '0' );
		} catch ( _e ) {
			// sessionStorage indisponible (mode privé strict)
		}

		// Application immédiate de "animations"
		if ( 'anim' === key ) {
			const ticker = document.querySelector( '.bs-ticker-track' );
			if ( ticker ) {
				ticker.style.animationPlayState = isOn ? 'running' : 'paused';
			}
		}
	};

	// Restore toggle states from sessionStorage
	[ 'anim', 'concerts', 'news', 'tchache' ].forEach( function ( key ) {
		try {
			const val = sessionStorage.getItem( 'bs_pref_' + key );
			if ( null !== val ) {
				const tog = document.getElementById( 'bs-tog-' + key );
				if ( ! tog ) return;
				if ( '0' === val ) {
					tog.classList.remove( 'is-on', 'bs-toggle--on' );
					tog.setAttribute( 'aria-pressed', 'false' );
				} else {
					tog.classList.add( 'is-on', 'bs-toggle--on' );
					tog.setAttribute( 'aria-pressed', 'true' );
				}
			}
		} catch ( _e ) {}
	} );

	// -----------------------------------------------------------------------
	// SWATCHES de fond
	// -----------------------------------------------------------------------
	window.bsSetBg = function ( btn ) {
		document.querySelectorAll( '.bs-swatch' ).forEach( function ( s ) {
			s.classList.remove( 'is-sel', 'bs-swatch--sel' );
		} );
		btn.classList.add( 'is-sel', 'bs-swatch--sel' );

		const start = btn.dataset.start;
		const end   = btn.dataset.end;

		if ( start && end ) {
			const app = document.getElementById( 'bs-app' );
			if ( app ) {
				app.style.backgroundImage =
					'radial-gradient(ellipse 80% 40% at 50% 0%, rgba(60,100,255,.35) 0%, transparent 70%), ' +
					'linear-gradient(168deg, ' + start + ' 0%, ' + end + ' 100%)';
			}
			try {
				sessionStorage.setItem( 'bs_bg_start', start );
				sessionStorage.setItem( 'bs_bg_end', end );
			} catch ( _e ) {}
		}
	};

	// Restore background swatch
	( function () {
		try {
			const s = sessionStorage.getItem( 'bs_bg_start' );
			const e = sessionStorage.getItem( 'bs_bg_end' );
			if ( s && e ) {
				const app = document.getElementById( 'bs-app' );
				if ( app ) {
					app.style.backgroundImage =
						'radial-gradient(ellipse 80% 40% at 50% 0%, rgba(60,100,255,.35) 0%, transparent 70%), ' +
						'linear-gradient(168deg, ' + s + ' 0%, ' + e + ' 100%)';
				}
				// Active le swatch correspondant
				document.querySelectorAll( '.bs-swatch' ).forEach( function ( btn ) {
					if ( btn.dataset.start === s ) {
						btn.classList.add( 'is-sel', 'bs-swatch--sel' );
					} else {
						btn.classList.remove( 'is-sel', 'bs-swatch--sel' );
					}
				} );
			}
		} catch ( _e ) {}
	} )();

	// -----------------------------------------------------------------------
	// SOUS-ONGLETS (login / register)
	// -----------------------------------------------------------------------
	window.bsSubTab = function ( btn, targetId ) {
		document.querySelectorAll( '.bs-tab-mini' ).forEach( function ( t ) {
			t.classList.remove( 'is-active', 'bs-tab-mini--active' );
		} );
		btn.classList.add( 'is-active', 'bs-tab-mini--active' );

		[ 'bs-login', 'bs-register' ].forEach( function ( id ) {
			const el = document.getElementById( id );
			if ( el ) el.style.display = id === targetId ? '' : 'none';
		} );
	};

	// -----------------------------------------------------------------------
	// HELPERS AJAX
	// -----------------------------------------------------------------------
	function post( action, data, cb ) {
		const fd = new FormData();
		fd.append( 'action', action );
		fd.append( 'nonce',  NONCE );
		Object.keys( data ).forEach( function ( k ) { fd.append( k, data[ k ] ); } );

		fetch( AJAX, { method: 'POST', body: fd } )
			.then( function ( r ) { return r.json(); } )
			.then( cb )
			.catch( function () {
				cb( { success: false, data: { message: 'Erreur réseau.' } } );
			} );
	}

	function showMsg( elId, text, isError ) {
		const el = document.getElementById( elId );
		if ( ! el ) return;
		el.textContent = text;
		el.className = 'bs-msg ' + ( isError ? 'is-error' : 'is-success' );
	}

	// -----------------------------------------------------------------------
	// CONNEXION
	// -----------------------------------------------------------------------
	window.bsLogin = function () {
		const user = ( document.getElementById( 'bs-login-user' ) || {} ).value || '';
		const pass = ( document.getElementById( 'bs-login-pass' ) || {} ).value || '';
		if ( ! user || ! pass ) {
			showMsg( 'bs-auth-msg', 'Tous les champs sont requis.', true );
			return;
		}
		post( 'bs_login', { username: user, password: pass }, function ( res ) {
			if ( res.success ) {
				window.location.reload();
			} else {
				showMsg( 'bs-auth-msg', ( res.data && res.data.message ) || 'Erreur.', true );
			}
		} );
	};

	// -----------------------------------------------------------------------
	// INSCRIPTION
	// -----------------------------------------------------------------------
	window.bsRegister = function () {
		const user  = ( document.getElementById( 'bs-reg-user' )  || {} ).value || '';
		const email = ( document.getElementById( 'bs-reg-email' ) || {} ).value || '';
		const pass  = ( document.getElementById( 'bs-reg-pass' )  || {} ).value || '';
		if ( ! user || ! email || ! pass ) {
			showMsg( 'bs-auth-msg', 'Tous les champs sont requis.', true );
			return;
		}
		post( 'bs_register', { username: user, email: email, password: pass }, function ( res ) {
			if ( res.success ) {
				showMsg( 'bs-auth-msg', ( res.data && res.data.message ) || 'Compte créé.', false );
				setTimeout( function () { window.location.reload(); }, 1500 );
			} else {
				showMsg( 'bs-auth-msg', ( res.data && res.data.message ) || 'Erreur.', true );
			}
		} );
	};

	// -----------------------------------------------------------------------
	// PROPOSER UNE REPRISE
	// -----------------------------------------------------------------------
	window.bsSendReprise = function () {
		const text = ( document.getElementById( 'bs-reprise-text' ) || {} ).value || '';
		if ( ! text.trim() ) {
			showMsg( 'bs-reprise-msg', 'Veuillez saisir une suggestion.', true );
			return;
		}
		post( 'bs_send_reprise', { content: text }, function ( res ) {
			const ok = res.success;
			showMsg( 'bs-reprise-msg', ( res.data && res.data.message ) || ( ok ? 'Envoyé !' : 'Erreur.' ), ! ok );
			if ( ok ) {
				const ta = document.getElementById( 'bs-reprise-text' );
				if ( ta ) ta.value = '';
			}
		} );
	};

	// -----------------------------------------------------------------------
	// ENREGISTRER PRÉFÉRENCES
	// -----------------------------------------------------------------------
	window.bsSavePrefs = function () {
		const email    = ( document.getElementById( 'bs-notif-email' )   || {} ).value || '';
		const concerts = document.getElementById( 'bs-tog-concerts' );
		const news     = document.getElementById( 'bs-tog-news' );
		const tchache  = document.getElementById( 'bs-tog-tchache' );

		post( 'bs_save_preferences', {
			notif_email:    email,
			notif_concerts: concerts && concerts.classList.contains( 'is-on' ) ? '1' : '0',
			notif_news:     news     && news.classList.contains( 'is-on' )     ? '1' : '0',
			notif_tchache:  tchache  && tchache.classList.contains( 'is-on' )  ? '1' : '0',
		}, function ( res ) {
			const ok = res.success;
			showMsg( 'bs-prefs-msg', ( res.data && res.data.message ) || ( ok ? 'Enregistré.' : 'Erreur.' ), ! ok );
		} );
	};

} )();

// =============================================================================
// TCHACHE — Mini-forum
// =============================================================================
( function () {
	'use strict';

	var wrap = document.getElementById( 'bs-tchache' );
	if ( ! wrap ) return;

	var AJAX       = bandstage.ajax_url;
	var NONCE      = bandstage.nonce;
	var MAX_LEN    = parseInt( wrap.dataset.maxLength, 10 ) || 500;
	var LOGGED     = wrap.dataset.logged === '1';
	var msgList    = document.getElementById( 'bs-tc-messages' );
	var textarea   = document.getElementById( 'bs-tc-textarea' );
	var sendBtn    = document.getElementById( 'bs-tc-send' );
	var charCount  = document.getElementById( 'bs-tc-charcount' );
	var feedback   = document.getElementById( 'bs-tc-feedback' );
	var loadMore   = document.getElementById( 'bs-tc-loadmore' );
	var loadWrap   = document.getElementById( 'bs-tc-loadmore-wrap' );
	var countBadge = document.getElementById( 'bs-tc-count' );

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------
	function setFeedback( msg, type ) {
		if ( ! feedback ) return;
		feedback.textContent = msg;
		feedback.className = 'bs-tc-feedback' + ( type ? ' is-' + type : '' );
		if ( type === 'success' || type === 'info' ) {
			setTimeout( function () {
				feedback.textContent = '';
				feedback.className = 'bs-tc-feedback';
			}, 4000 );
		}
	}

	function post( action, data, cb ) {
		var fd = new FormData();
		fd.append( 'action', action );
		fd.append( 'nonce', NONCE );
		Object.keys( data ).forEach( function ( k ) { fd.append( k, data[ k ] ); } );
		fetch( AJAX, { method: 'POST', body: fd } )
			.then( function ( r ) { return r.json(); } )
			.then( cb )
			.catch( function () { cb( { success: false, data: { message: 'Erreur réseau.' } } ); } );
	}

	function scrollBottom() {
		if ( msgList ) msgList.scrollTop = msgList.scrollHeight;
	}

	function incrementCount() {
		if ( ! countBadge ) return;
		var n = parseInt( countBadge.textContent, 10 ) || 0;
		countBadge.textContent = n + 1;
	}

	// -----------------------------------------------------------------------
	// Compteur de caractères + auto-resize textarea
	// -----------------------------------------------------------------------
	if ( textarea ) {
		textarea.addEventListener( 'input', function () {
			var len  = textarea.value.length;
			var pct  = len / MAX_LEN;

			if ( charCount ) {
				charCount.textContent = len + ' / ' + MAX_LEN;
				charCount.className = 'bs-tc-charcount' +
					( pct >= 1 ? ' is-limit' : pct >= .8 ? ' is-warn' : '' );
			}

			// Auto-resize.
			textarea.style.height = 'auto';
			textarea.style.height = Math.min( textarea.scrollHeight, 120 ) + 'px';
		} );

		// Envoyer avec Ctrl+Enter ou Cmd+Enter.
		textarea.addEventListener( 'keydown', function ( e ) {
			if ( ( e.ctrlKey || e.metaKey ) && e.key === 'Enter' ) {
				e.preventDefault();
				if ( sendBtn && ! sendBtn.disabled ) sendBtn.click();
			}
		} );
	}

	// -----------------------------------------------------------------------
	// Construire une bulle de message HTML
	// -----------------------------------------------------------------------
	function buildMsgHtml( msg ) {
		var mine   = LOGGED && parseInt( msg.user_id, 10 ) > 0 &&
		             parseInt( msg.user_id, 10 ) === parseInt( bandstage.user_id, 10 );
		var initials = ( msg.author_name || '?' ).trim().split( ' ' )
			.map( function ( p ) { return p.charAt( 0 ).toUpperCase(); } )
			.slice( 0, 2 ).join( '' );

		var avatarHtml = '';
		if ( ! mine ) {
			avatarHtml = msg.avatar
				? '<img src="' + msg.avatar + '" alt="" width="36" height="36" loading="lazy">'
				: initials;
			avatarHtml = '<div class="bs-tc-msg__avatar">' + avatarHtml + '</div>';
		}

		var authorHtml = mine ? '' : '<div class="bs-tc-msg__author">' + escHtml( msg.author_name ) + '</div>';
		var textHtml   = escHtml( msg.content ).replace( /\n/g, '<br>' );
		var timeHtml   = '<div class="bs-tc-msg__time">' + escHtml( msg.created_at || '' ) + '</div>';

		return '<div class="bs-tc-msg' + ( mine ? ' bs-tc-msg--mine' : '' ) +
			'" data-id="' + msg.id + '">' +
			avatarHtml +
			'<div class="bs-tc-msg__bubble">' + authorHtml +
			'<div class="bs-tc-msg__text">' + textHtml + '</div>' +
			timeHtml + '</div></div>';
	}

	function escHtml( str ) {
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	// -----------------------------------------------------------------------
	// Envoyer un message
	// -----------------------------------------------------------------------
	if ( sendBtn ) {
		sendBtn.addEventListener( 'click', function () {
			if ( ! textarea ) return;
			var content = textarea.value.trim();
			if ( ! content ) {
				setFeedback( bandstage.strings.no_messages || 'Écrivez quelque chose.', 'error' );
				textarea.focus();
				return;
			}

			var nameEl  = document.getElementById( 'bs-tc-name' );
			var nameVal = nameEl ? nameEl.value.trim() : '';

			if ( ! LOGGED && ! nameVal ) {
				setFeedback( 'Indiquez votre pseudo.', 'error' );
				if ( nameEl ) nameEl.focus();
				return;
			}

			sendBtn.disabled = true;
			setFeedback( '<span class="bs-tc-spinner"></span>', '' );
			if ( feedback ) feedback.innerHTML = '<span class="bs-tc-spinner"></span>';

			var data = { content: content };
			if ( nameVal ) data.author_name = nameVal;

			post( 'bs_post_message', data, function ( res ) {
				sendBtn.disabled = false;
				if ( res.success ) {
					textarea.value = '';
					textarea.style.height = 'auto';
					if ( charCount ) {
						charCount.textContent = '0 / ' + MAX_LEN;
						charCount.className = 'bs-tc-charcount';
					}

					var status = res.data && res.data.status;
					if ( status === 'approved' ) {
						// Ajoute la bulle immédiatement (optimistic UI).
						var empty = document.getElementById( 'bs-tc-empty' );
						if ( empty ) empty.remove();
						var html = buildMsgHtml( {
							id:          'new-' + Date.now(),
							user_id:     bandstage.user_id || 0,
							author_name: LOGGED && bandstage.user_name ? bandstage.user_name : ( nameVal || 'Moi' ),
							content:     content,
							avatar:      '',
							created_at:  'À l\'instant',
						} );
						msgList.insertAdjacentHTML( 'beforeend', html );
						scrollBottom();
						incrementCount();
						setFeedback( res.data.message || 'Publié !', 'success' );
					} else {
						// Modération manuelle.
						var pending = document.createElement( 'div' );
						pending.className = 'bs-tc-pending';
						pending.textContent = res.data && res.data.message
							? res.data.message
							: 'Message en attente de modération.';
						msgList.appendChild( pending );
						scrollBottom();
						setFeedback( '', '' );
					}
				} else {
					setFeedback( ( res.data && res.data.message ) || 'Erreur.', 'error' );
				}
			} );
		} );
	}

	// -----------------------------------------------------------------------
	// Charger les messages précédents (pagination remontante)
	// -----------------------------------------------------------------------
	if ( loadMore ) {
		loadMore.addEventListener( 'click', function () {
			var page  = parseInt( loadMore.dataset.page, 10 ) || 2;
			var total = parseInt( loadMore.dataset.total, 10 ) || 0;

			loadMore.disabled = true;
			loadMore.innerHTML = '<span class="bs-tc-spinner"></span>';

			post( 'bs_load_messages', { page: page, direction: 'older' }, function ( res ) {
				loadMore.disabled = false;
				if ( res.success && res.data.messages && res.data.messages.length ) {
					var html = res.data.messages
						.slice()
						.reverse() // les plus anciens en premier
						.map( buildMsgHtml )
						.join( '' );

					// Insérer avant le premier message existant (après le bouton).
					var firstMsg = msgList.querySelector( '.bs-tc-msg' );
					if ( firstMsg ) {
						firstMsg.insertAdjacentHTML( 'beforebegin', html );
					} else {
						msgList.insertAdjacentHTML( 'beforeend', html );
					}

					if ( res.data.has_more ) {
						loadMore.dataset.page = page + 1;
						var loaded = page * 25;
						var remaining = Math.max( 0, total - loaded );
						loadMore.textContent = '↑ Voir les ' + remaining + ' messages précédents';
					} else {
						if ( loadWrap ) loadWrap.remove();
					}
				} else {
					if ( loadWrap ) loadWrap.remove();
				}
			} );
		} );
	}

	// Scroll initial vers le bas.
	scrollBottom();

} )();

// =============================================================================
// PROFIL MEMBRE
// =============================================================================
( function () {
	'use strict';

	if ( ! document.getElementById( 'bs-profil' ) ) return;

	var AJAX  = bandstage.ajax_url;
	var NONCE = bandstage.nonce;

	// -----------------------------------------------------------------------
	// Navigation par onglets
	// -----------------------------------------------------------------------
	window.bsPrTab = function ( btn, targetId ) {
		// Désactive tous les onglets du même conteneur de tabs.
		var tabs = btn.closest( '.bs-pr-tabs' );
		if ( tabs ) {
			tabs.querySelectorAll( '.bs-pr-tab' ).forEach( function ( t ) {
				t.classList.remove( 'bs-pr-tab--active' );
			} );
		}
		btn.classList.add( 'bs-pr-tab--active' );

		// Masque tous les panels frères.
		var card = btn.closest( '.bs-pr-card' );
		if ( card ) {
			card.querySelectorAll( '.bs-pr-panel' ).forEach( function ( p ) {
				p.style.display = 'none';
			} );
		}

		var target = document.getElementById( targetId );
		if ( target ) target.style.display = '';
	};

	// -----------------------------------------------------------------------
	// Toggle affichage mot de passe
	// -----------------------------------------------------------------------
	window.bsPrTogglePass = function ( inputId, btn ) {
		var input = document.getElementById( inputId );
		if ( ! input ) return;
		var isText = input.type === 'text';
		input.type = isText ? 'password' : 'text';
		btn.setAttribute( 'aria-label', isText ? 'Afficher le mot de passe' : 'Masquer le mot de passe' );
		btn.style.opacity = isText ? '.6' : '1';
	};

	// -----------------------------------------------------------------------
	// Indicateur de robustesse du mot de passe
	// -----------------------------------------------------------------------
	var regPass = document.getElementById( 'bs-pr-reg-pass' );
	var strength = document.getElementById( 'bs-pr-strength' );
	if ( regPass && strength ) {
		regPass.addEventListener( 'input', function () {
			var v = regPass.value;
			var score = 0;
			if ( v.length >= 8  ) score++;
			if ( v.length >= 12 ) score++;
			if ( /[A-Z]/.test( v ) ) score++;
			if ( /[0-9]/.test( v ) ) score++;
			if ( /[^A-Za-z0-9]/.test( v ) ) score++;

			var colors = [ '#E24B4A', '#EF9F27', '#EFC84A', '#5DCAA5', '#2DB37A' ];
			var widths = [ '20%', '40%', '60%', '80%', '100%' ];
			strength.style.setProperty( '--strength-w', widths[ Math.min( score, 4 ) ] );
			strength.style.setProperty( '--strength-c', colors[ Math.min( score, 4 ) ] );
		} );
	}

	// -----------------------------------------------------------------------
	// Compteur bio
	// -----------------------------------------------------------------------
	var bioField = document.getElementById( 'bs-pr-bio' );
	var bioCount = document.getElementById( 'bs-pr-bio-count' );
	if ( bioField && bioCount ) {
		bioField.addEventListener( 'input', function () {
			bioCount.textContent = bioField.value.length + ' / 300';
		} );
	}

	// -----------------------------------------------------------------------
	// Helper AJAX
	// -----------------------------------------------------------------------
	function post( action, data, cb ) {
		var fd = new FormData();
		fd.append( 'action', action );
		fd.append( 'nonce', NONCE );
		Object.keys( data ).forEach( function ( k ) { fd.append( k, data[ k ] ); } );
		fetch( AJAX, { method: 'POST', body: fd } )
			.then( function ( r ) { return r.json(); } )
			.then( cb )
			.catch( function () { cb( { success: false, data: { message: 'Erreur réseau.' } } ); } );
	}

	function setMsg( elId, text, type ) {
		var el = document.getElementById( elId );
		if ( ! el ) return;
		el.textContent = text;
		el.className = 'bs-pr-msg' + ( type ? ' is-' + type : '' );
		if ( type === 'success' ) {
			setTimeout( function () {
				el.textContent = '';
				el.className = 'bs-pr-msg';
			}, 4000 );
		}
	}

	function val( id ) {
		var el = document.getElementById( id );
		return el ? el.value.trim() : '';
	}

	function togOn( id ) {
		var el = document.getElementById( id );
		return el ? el.classList.contains( 'is-on' ) : false;
	}

	// -----------------------------------------------------------------------
	// Connexion
	// -----------------------------------------------------------------------
	window.bsPrLogin = function () {
		var user = val( 'bs-pr-login-user' );
		var pass = val( 'bs-pr-login-pass' );
		if ( ! user || ! pass ) {
			setMsg( 'bs-pr-login-msg', 'Renseignez tous les champs.', 'error' );
			return;
		}
		setMsg( 'bs-pr-login-msg', '…', '' );
		post( 'bs_login', { username: user, password: pass }, function ( res ) {
			if ( res.success ) {
				setMsg( 'bs-pr-login-msg', 'Connexion réussie, rechargement…', 'success' );
				setTimeout( function () { window.location.reload(); }, 1000 );
			} else {
				setMsg( 'bs-pr-login-msg', ( res.data && res.data.message ) || 'Identifiants incorrects.', 'error' );
			}
		} );
	};

	// -----------------------------------------------------------------------
	// Inscription
	// -----------------------------------------------------------------------
	window.bsPrRegister = function () {
		var user  = val( 'bs-pr-reg-user' );
		var email = val( 'bs-pr-reg-email' );
		var pass  = val( 'bs-pr-reg-pass' );
		if ( ! user || ! email || ! pass ) {
			setMsg( 'bs-pr-reg-msg', 'Tous les champs sont requis.', 'error' );
			return;
		}
		if ( pass.length < 8 ) {
			setMsg( 'bs-pr-reg-msg', 'Mot de passe trop court (8 caractères minimum).', 'error' );
			return;
		}

		var concerts = document.getElementById( 'bs-pr-reg-concerts' );
		setMsg( 'bs-pr-reg-msg', '…', '' );

		post( 'bs_register', {
			username:       user,
			email:          email,
			password:       pass,
			notif_concerts: concerts && concerts.checked ? '1' : '0',
		}, function ( res ) {
			if ( res.success ) {
				setMsg( 'bs-pr-reg-msg', ( res.data && res.data.message ) || 'Compte créé !', 'success' );
				setTimeout( function () { window.location.reload(); }, 1500 );
			} else {
				setMsg( 'bs-pr-reg-msg', ( res.data && res.data.message ) || 'Erreur.', 'error' );
			}
		} );
	};

	// -----------------------------------------------------------------------
	// Sauvegarde du profil
	// -----------------------------------------------------------------------
	window.bsPrSaveProfile = function () {
		setMsg( 'bs-pr-profile-msg', '…', '' );
		post( 'bs_update_profile', {
			display_name: val( 'bs-pr-display-name' ),
			instrument:   val( 'bs-pr-instrument' ),
			location:     val( 'bs-pr-location' ),
			bio:          ( document.getElementById( 'bs-pr-bio' ) || {} ).value || '',
		}, function ( res ) {
			setMsg( 'bs-pr-profile-msg',
				( res.data && res.data.message ) || ( res.success ? 'Profil enregistré.' : 'Erreur.' ),
				res.success ? 'success' : 'error'
			);
		} );
	};

	// -----------------------------------------------------------------------
	// Sauvegarde des préférences de notifications
	// -----------------------------------------------------------------------
	window.bsPrSavePrefs = function () {
		setMsg( 'bs-pr-prefs-msg', '…', '' );
		post( 'bs_save_preferences', {
			notif_concerts: togOn( 'bs-pr-tog-concerts' ) ? '1' : '0',
			notif_news:     togOn( 'bs-pr-tog-news' )     ? '1' : '0',
			notif_tchache:  togOn( 'bs-pr-tog-tchache' )  ? '1' : '0',
			notif_email:    val( 'bs-pr-email' ),
		}, function ( res ) {
			setMsg( 'bs-pr-prefs-msg',
				( res.data && res.data.message ) || ( res.success ? 'Préférences enregistrées.' : 'Erreur.' ),
				res.success ? 'success' : 'error'
			);
		} );
	};

	// -----------------------------------------------------------------------
	// Envoyer une reprise
	// -----------------------------------------------------------------------
	window.bsPrSendReprise = function () {
		var text = ( document.getElementById( 'bs-pr-reprise-text' ) || {} ).value || '';
		if ( text.trim().length < 5 ) {
			setMsg( 'bs-pr-reprise-msg', 'Message trop court.', 'error' );
			return;
		}
		setMsg( 'bs-pr-reprise-msg', '…', '' );
		post( 'bs_send_reprise', { content: text }, function ( res ) {
			if ( res.success ) {
				var ta = document.getElementById( 'bs-pr-reprise-text' );
				if ( ta ) ta.value = '';
			}
			setMsg( 'bs-pr-reprise-msg',
				( res.data && res.data.message ) || ( res.success ? 'Envoyé !' : 'Erreur.' ),
				res.success ? 'success' : 'error'
			);
		} );
	};

} )();

// =============================================================================
// HUMEURS — Lire la suite
// =============================================================================
( function () {
	'use strict';

	document.querySelectorAll( '.bs-hm-toggle' ).forEach( function ( btn ) {
		var targetId = btn.dataset.target;
		var content  = targetId ? document.getElementById( targetId ) : null;
		if ( ! content ) return;

		btn.addEventListener( 'click', function () {
			var isOpen = content.classList.toggle( 'is-open' );
			btn.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			content.setAttribute( 'aria-hidden', isOpen ? 'false' : 'true' );
			if ( isOpen ) {
				// Scroll doux vers la carte si nécessaire.
				btn.closest( '.bs-hm-card' ) &&
				btn.closest( '.bs-hm-card' ).scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
			}
		} );
	} );

} )();

// =============================================================================
// RÉFÉRENCES — Accordéon influences
// =============================================================================
( function () {
	'use strict';

	var accordion = document.getElementById( 'bs-rf-accordion' );
	if ( ! accordion ) return;

	var openItem = null; // Item actuellement ouvert.

	accordion.querySelectorAll( '.bs-rf-acc-trigger' ).forEach( function ( trigger ) {
		trigger.addEventListener( 'click', function () {
			var item   = trigger.closest( '.bs-rf-acc-item' );
			var bodyId = trigger.getAttribute( 'aria-controls' );
			var body   = bodyId ? document.getElementById( bodyId ) : null;
			if ( ! item || ! body ) return;

			var isOpen = item.classList.contains( 'is-open' );

			// Ferme l'item précédemment ouvert.
			if ( openItem && openItem !== item ) {
				openItem.classList.remove( 'is-open' );
				var prevTrigger = openItem.querySelector( '.bs-rf-acc-trigger' );
				var prevBody    = openItem.querySelector( '.bs-rf-acc-body' );
				if ( prevTrigger ) prevTrigger.setAttribute( 'aria-expanded', 'false' );
				if ( prevBody )    prevBody.hidden = true;
			}

			// Bascule l'item courant.
			if ( isOpen ) {
				item.classList.remove( 'is-open' );
				trigger.setAttribute( 'aria-expanded', 'false' );
				body.hidden = true;
				openItem = null;
			} else {
				item.classList.add( 'is-open' );
				trigger.setAttribute( 'aria-expanded', 'true' );
				body.hidden = false;
				openItem = item;

				// Scroll doux vers le haut de l'item.
				setTimeout( function () {
					item.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
				}, 50 );
			}
		} );
	} );

} )();

// =============================================================================
// SPLASHSCREEN
// =============================================================================
( function () {
	'use strict';

	var splash = document.getElementById( 'bs-splash' );
	if ( ! splash ) return;

	var STORAGE_KEY = 'bs_splash_hidden';
	var duration    = parseInt( splash.dataset.duration, 10 ) || 0;
	var prefToggle  = document.getElementById( 'bs-splash-pref' );

	// Restaurer la préférence sauvegardée.
	try {
		var saved = localStorage.getItem( STORAGE_KEY );
		if ( saved === '1' ) {
			// L'utilisateur a désactivé — masquer immédiatement sans animation.
			splash.style.display = 'none';
			return;
		}
	} catch ( _e ) {}

	// Synchronise le toggle avec la préférence courante (par défaut : checked).
	if ( prefToggle ) {
		prefToggle.checked = true;
		prefToggle.addEventListener( 'change', function () {
			try {
				localStorage.setItem( STORAGE_KEY, prefToggle.checked ? '0' : '1' );
			} catch ( _e ) {}
		} );
	}

	// Fermeture avec animation.
	window.bsSplashClose = function () {
		if ( splash.classList.contains( 'is-closing' ) ) return;
		splash.classList.add( 'is-closing' );
		setTimeout( function () {
			splash.style.display = 'none';
		}, 460 );
	};

	// Fermeture automatique après duration secondes.
	if ( duration > 0 ) {
		setTimeout( window.bsSplashClose, duration * 1000 );
	}

	// Fermeture au tap sur l'overlay (hors footer).
	splash.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key ) window.bsSplashClose();
	} );

	// Focus trap (accessibilité).
	splash.setAttribute( 'tabindex', '-1' );
	splash.focus();

} )();
