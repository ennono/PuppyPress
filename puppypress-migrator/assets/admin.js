/**
 * PuppyPress Migrator: starts a job and drives it step by step.
 */
( function () {
	'use strict';

	var config = window.ppmAdmin;
	var panel = document.querySelector( '[data-ppm-progress]' );

	if ( ! config || ! panel ) {
		return;
	}

	var els = {
		title: panel.querySelector( '[data-ppm-title]' ),
		bar: panel.querySelector( '[data-ppm-bar]' ),
		stage: panel.querySelector( '[data-ppm-stage]' ),
		result: panel.querySelector( '[data-ppm-result]' ),
		log: panel.querySelector( '[data-ppm-log]' )
	};
	var running = false;
	var logTotal = 0;

	function post( data ) {
		data.append( 'nonce', config.nonce );

		return fetch( config.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' } )
			.then( function ( response ) {
				return response.json().catch( function () {
					throw new Error( config.i18n.network );
				} );
			} )
			.then( function ( json ) {
				if ( ! json || ! json.success ) {
					throw new Error( ( json && json.data && json.data.message ) || config.i18n.network );
				}
				return json.data;
			} );
	}

	function addLog( entries ) {
		entries.forEach( function ( entry ) {
			var item = document.createElement( 'li' );
			item.className = 'ppm-log__' + entry.level;
			item.textContent = entry.message;
			els.log.appendChild( item );
		} );
	}

	function setForms( disabled ) {
		document.querySelectorAll( '[data-ppm-form] button, [data-ppm-form] input, [data-ppm-form] select' ).forEach( function ( el ) {
			if ( ! el.hasAttribute( 'data-ppm-always-disabled' ) ) {
				el.disabled = disabled;
			}
		} );
	}

	function finish( state ) {
		running = false;
		setForms( false );
		els.bar.style.width = '100%';
		els.stage.textContent = '';

		if ( state.error ) {
			panel.classList.add( 'is-error' );
			els.result.textContent = config.i18n.failed + ' ' + state.error;
			return;
		}

		panel.classList.add( 'is-done' );
		els.result.textContent = config.i18n.done + ' ';

		if ( state.download ) {
			var link = document.createElement( 'a' );
			link.href = state.download;
			link.className = 'button button-primary';
			link.textContent = config.i18n.download;
			els.result.appendChild( link );
			window.location.href = state.download;
		}
	}

	function update( state ) {
		addLog( state.log );
		logTotal = state.logTotal;
		els.bar.style.width = state.percent + '%';
		els.stage.textContent = state.stage;
	}

	function step( jobId ) {
		var data = new FormData();
		data.append( 'action', 'ppm_step' );
		data.append( 'job', jobId );
		data.append( 'log', logTotal );

		post( data )
			.then( function ( state ) {
				update( state );

				if ( state.done ) {
					finish( state );
				} else {
					step( jobId );
				}
			} )
			.catch( function ( error ) {
				running = false;
				els.result.textContent = error.message + ' ';

				var retry = document.createElement( 'button' );
				retry.type = 'button';
				retry.className = 'button';
				retry.textContent = config.i18n.retry;
				retry.addEventListener( 'click', function () {
					running = true;
					els.result.textContent = '';
					step( jobId );
				} );
				els.result.appendChild( retry );
			} );
	}

	document.querySelectorAll( '[data-ppm-form]' ).forEach( function ( form ) {
		form.addEventListener( 'submit', function ( event ) {
			var type = form.getAttribute( 'data-ppm-form' );
			var data = new FormData( form );

			event.preventDefault();

			if ( running ) {
				return;
			}

			if ( 'import' === type ) {
				var file = form.querySelector( '[name="package"]' );
				var incoming = form.querySelector( '[name="incoming"]' );

				if ( ( ! file.files.length ) && ( ! incoming || ! incoming.value ) ) {
					window.alert( config.i18n.noFile );
					return;
				}

				if ( ! window.confirm( config.i18n.confirm ) ) {
					return;
				}
			}

			data.append( 'action', 'ppm_start' );
			data.append( 'type', type );

			running = true;
			logTotal = 0;
			setForms( true );
			panel.hidden = false;
			panel.classList.remove( 'is-done', 'is-error' );
			els.title.textContent = form.closest( '.ppm-card' ).querySelector( 'h2' ).textContent;
			els.log.textContent = '';
			els.result.textContent = '';
			els.bar.style.width = '0';
			els.stage.textContent = config.i18n.starting;
			panel.scrollIntoView( { behavior: 'smooth', block: 'start' } );

			post( data )
				.then( function ( state ) {
					update( state );
					step( state.job );
				} )
				.catch( function ( error ) {
					finish( { error: error.message } );
				} );
		} );
	} );

	window.addEventListener( 'beforeunload', function ( event ) {
		if ( running ) {
			event.preventDefault();
			event.returnValue = config.i18n.leaving;
		}
	} );
}() );
