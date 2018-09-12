( function ( mw, $ ) {

	var cookieName = 'dismissSiteNotice',
		typeCookie = 'dismissNoticeType-',
		siteNoticeId = mw.config.get( 'wgSiteNoticeId' );

	// If no siteNoticeId is set, exit.
	if ( !siteNoticeId ) {
		return;
	}

	// If the user has the notice dismissal cookie set, exit.
	if ( $.cookie( cookieName ) === siteNoticeId ) {
		return;
	}

	// Check if notice has a type
	if ( $('.sitenotice-type').length ) {
		var type = $('.sitenotice-type').attr('data-noticetype');

		// If the user has the notice dismissal cookie set, exit.
		if ( $.cookie( typeCookie + type ) === 'hide' ) {
			return;
		}

		// Otherwise, show the notice ...
		mw.util.addCSS( '.client-js .mw-dismissable-notice { display: block; }' );

		// ... and enable the dismiss button.
		$( function () {
			$( '.mw-dismissable-notice-close' )
				.css( 'visibility', 'visible' )
				.find( 'a' )
				.on( 'click keypress', function ( e ) {
					if (
						e.type === 'click' ||
						e.type === 'keypress' && e.which === 13
					) {
						$( this ).closest( '.mw-dismissable-notice' ).hide();
						$.cookie( typeCookie + type, 'hide', {
							expires: 30,
							path: '/'
						} );
					}
				} );
		} );
	} else {
		// No type show notice and button

		// Otherwise, show the notice ...
		mw.util.addCSS( '.client-js .mw-dismissable-notice { display: block; }' );

		// ... and enable the dismiss button.
		$( function () {
			$( '.mw-dismissable-notice-close' )
				.css( 'visibility', 'visible' )
				.find( 'a' )
				.on( 'click keypress', function ( e ) {
					if (
						e.type === 'click' ||
						e.type === 'keypress' && e.which === 13
					) {
						$( this ).closest( '.mw-dismissable-notice' ).hide();
						$.cookie( cookieName, siteNoticeId, {
							expires: 30,
							path: '/'
						} );
					}
				} );
		} );
	}

	// Add class to close-button parent
	$('.mw-dismissable-notice-close').parent().addClass('mw-dismissable-notice-close-parent');

	// Match color of close-button to container font color
	var pcolor = $('.mw-dismissable-notice-close').parent().css('color');
	$('.mw-dismissable-notice-close').css('fill',pcolor);

}( mediaWiki, jQuery ) );
