
function doWordTwitReady() {
	wtSetupTabSwitching();
	wtCookieSetup();	
	wtSetupGlobals();
	wtLoadNews();
	wtSetupSelects();
	wtDoDashboardAjax();
	wtSavedOrReset();
	wtAccountPage();
	wtSetupTweetLog();
}

function wtSetupTweetLog() {
	var deleteAllButton = jQuery( '#tweet_log_delete_all' );
	if ( deleteAllButton.length ) {
		deleteAllButton.click( function( e ) {
			if ( !confirm( 'You are about to delete all log entries from the Tweet Log - continue?' ) ) {
				e.preventDefault();
			}
		});
	}
}

	/* Show saving div when form submit, for some postive feedback */
	jQuery( '#bnc-submit' ).live( 'click', function() {
		jQuery( '#saving-ajax' ).fadeIn( 200 );
	});

function wtAccountPage() {
	var twitterAddButton = jQuery( '#twitter-add-button' );
	if ( twitterAddButton.length ) {
		if ( !twitterAddButton.attr( 'disabled' ) ) {
			twitterAddButton.live( 'click', function( e ) {
				
				if ( !twitterAddButton.attr( 'disabled' ) ) {
					twitterAddButton.attr( 'disabled', 'disabled' );
					wtAdminAjax( 'twitter-add', {}, function( result ) {	
						window.location.href = result;
					});
				}
						
				e.preventDefault();
			});	
		}
	}	
	
	var fixHelper = function(e, ui) {
		ui.children().each( function() {
			jQuery(this).width( jQuery(this).width() );
    	});
    
    	return ui;
	};
	
	jQuery( '#wordtwit-account-list tbody' ).sortable( { 
		forceHelperSize: true, 
		forcePlaceholderSize: true, 
		axis: 'y', 
		helper: fixHelper,
		cursor: 'move',
		update: function( event, ui ) {
			var accountList = [];
			var count = 0;
			
			jQuery( '#wordtwit-account-list tbody tr' ).each( function() {
				accountList[count] = jQuery( this ).attr( 'data-name' );
				count = count + 1;
			});		

			var ajaxParams = {
				account_order: accountList.toString()
			}
			
			jQuery( 'img.account-loader' ).show();
			wtAdminAjax( 'update-account-order', ajaxParams, function( result ) {	
				jQuery( 'img.account-loader' ).hide();
			});			
		},
		start: function( event, ui ) {
			jQuery( '#wordtwit-account-list tbody tr' ).removeClass( 'alternate' );
			jQuery( '#wordtwit-account-list tbody tr' ).css( 'opacity', '0.7' );
		},
		stop: function( event, ui ) {
			jQuery( '#wordtwit-account-list tbody tr:odd' ).addClass( 'alternate' );
			setTimeout( function() { jQuery( '#wordtwit-account-list tbody tr' ).css( 'opacity', '1' ); }, 300 );
		},
		cancel: function( event, ui ) {
		
		}
	} ).disableSelection();
}

function wtSetupTabSwitching() {
	var adminTabSwitchLinks = jQuery( 'a.wordtwit-admin-switch' );
	if ( adminTabSwitchLinks.length ) {
		adminTabSwitchLinks.live( 'click', function( e ) {
			var targetTabId = '';
			var targetTabSection = '';
			var targetArea = jQuery( this ).attr( 'rel' );

			if ( targetArea == 'account' ) {
				targetTabId = 'pane-2';
				targetTabSection = 'tab-section-bncid';	
			}
			
			jQuery( 'a#' + targetTabId + ',' + 'a#' + targetTabSection ).click();				
			e.preventDefault();
		});
	}
}

function wtCookieSetup() {
	// Top menu tabs
	jQuery( '#wordtwit-top-menu li a' ).unbind( 'click' ).click( function() {
		var tabId = jQuery( this ).attr( 'id' );
		
		jQuery.cookie( 'wordtwit-tab', tabId );
		
		jQuery( '.pane-content' ).hide();
		jQuery( '#pane-content-' + tabId ).show();
		
		jQuery( '#pane-content-' + tabId + ' .left-area li a:first' ).click();
		
		jQuery( '#wordtwit-top-menu li a' ).removeClass( 'active' );
		jQuery( '#wordtwit-top-menu li a' ).removeClass( 'round-top-6' );
		
		jQuery( this ).addClass( 'active' );
		jQuery( this ).addClass( 'round-top-6' );

		return false;
	});

	// Left menu tabs
	jQuery( '#wordtwit-admin-form .left-area li a' ).unbind( 'click' ).click( function() {
		var relAttr = jQuery( this ).attr( 'rel' );
		
		jQuery.cookie( 'wordtwit-list', relAttr );
			
		jQuery( '.setting-right-section' ).hide();
		jQuery( '#setting-' + relAttr ).show();
		
		jQuery( '#wordtwit-admin-form .left-area li a' ).removeClass( 'active' );
		
		jQuery( this ).addClass( 'active' );
		
		return false;
	});
	
	// Cookie saving for tabs
	var tabCookie = jQuery.cookie( 'wordtwit-tab' );
	if ( tabCookie ) {
		var tabLink = jQuery( "#wordtwit-top-menu li a[id='" + tabCookie + "']" ); 
		jQuery( '.pane-content' ).hide();
		jQuery( '#pane-content-' + tabCookie ).show();	
		tabLink.addClass( 'active' );
		tabLink.addClass( 'round-top-6' );
		
		var listCookie = jQuery.cookie( 'wordtwit-list' );
		if ( listCookie ) {
			var menuLink = jQuery( "#wordtwit-admin-form .left-area li a[rel='" + listCookie + "']");
			jQuery( '.setting-right-section' ).hide();
			jQuery( '#setting-' + listCookie ).show();	
			jQuery( '#wordtwit-admin-form .left-area li a' ).removeClass( 'active' );	
			menuLink.click();			
		} else {
			jQuery( '#wordtwit-admin-form .left-area li a:first' ).click();
		}
	} else {
		jQuery( '#wordtwit-top-menu li a:first' ).click();
	}	
}

var wtNotifiedCustomKeyWarning = 0;

function wtSetupGlobals() {	
	jQuery.ajaxSetup ({
	    cache: false
	});		

	// Setup tooltips (new)
	jQuery( '.wordtwit-tooltip' ).hover(function(){
        // Hover over code
        var title = jQuery( this ).attr( 'title' );
        jQuery(this).data( 'tipText', title ).removeAttr( 'title' );
        jQuery( '<p class="tooltip"></p>' ).html( title ).appendTo( 'body' ).fadeIn( 330 );
	}, function() {
	        // Hover out code
	        jQuery( this ).attr('title', jQuery( this ).data( 'tipText' ) );
	        jQuery( '.tooltip' ).remove();
	}).mousemove(function(e) {
	        var mousex = e.pageX; //Get X coordinates
	        var mousey = e.pageY; //Get Y coordinates
	        jQuery( '.tooltip' ).css( { top: mousey, left: mousex } )
	});

	jQuery( '#twitboard .box-holder' ).equalHeights( 300, 450 );
		
	
	jQuery( '#contributors_can_add_accounts' ).live( 'change', function() {
		if ( jQuery( this ).attr( 'checked' ) ) {
			jQuery( '#setting_minimum_user_capability_for_account_add' ).slideDown();
		} else {
			jQuery( '#setting_minimum_user_capability_for_account_add' ).hide();
		}
	}).change();
	
	jQuery( '#url_shortener' ).live( 'change', function() {
		var currentValue = jQuery( this ).val();
		
		jQuery( '#setting_cloudapp_password, #setting_cloudapp_username, #setting_yourls_path, #setting_yourls_signature, #setting_bitly_api_key, #setting_bitly_username' ).hide();

		if ( currentValue == 'yourls' ) {
			jQuery( '#setting_yourls_path' ).slideDown();
			jQuery( '#setting_yourls_signature' ).slideDown();
		} else if ( currentValue == 'bitly' ) {
			jQuery( '#setting_bitly_api_key' ).slideDown();
			jQuery( '#setting_bitly_username' ).slideDown();	
		} else if ( currentValue == 'cloudapp' ) {
			jQuery( '#setting_cloudapp_password' ).slideDown();
			jQuery( '#setting_cloudapp_username' ).slideDown();	
		}
	}).change();
	
	jQuery( 'a#estimate-offset' ).live( 'click', function( e ) {
		jQuery( '#estimate-offset' ).append( '<span id="small-spinner"></span>' );	
		wtAdminAjax( 'estimate-offset', {}, function( result ) {	
			jQuery( '#oauth_time_offset' ).val( result );
			jQuery( '#small-spinner' ).remove();
		});
		
		e.preventDefault();
	});	
	
	jQuery( '#setting_custom_consumer_key, #custom_consumer_key' ).live( 'change', function() {
		if ( !wtNotifiedCustomKeyWarning ) {
			alert( WordTwitProCustom.custom_key_warning );
			wtNotifiedCustomKeyWarning = 1;
		}
	});
}

function wtAdminAjax( actionName, actionParams, callback ) {	
	var ajaxData = {
		action: "wordtwit_ajax",
		wordtwit_action: actionName,
		wordtwit_nonce: WordTwitProCustom.admin_nonce
	};
	
	for ( name in actionParams ) { ajaxData[name] = actionParams[name]; }

	jQuery.post( ajaxurl, ajaxData, function( result ) {
		callback( result );	
	});	
}

function wtSetupSelects() {
	jQuery( '#tweet_template' ).change( function(){
		var currentVal = jQuery( this ).val();
		if ( currentVal == 'custom' ) {
			jQuery( '#setting_custom_tweet_template' ).slideDown();
		} else {
			jQuery( '#setting_custom_tweet_template' ).hide();	
		}
	}).change();	

	jQuery( '#retweet_template' ).change( function(){
		var currentVal = jQuery( this ).val();
		if ( currentVal == 'custom' ) {
			jQuery( '#setting_custom_retweet_template' ).slideDown();
		} else {
			jQuery( '#setting_custom_retweet_template' ).hide();	
		}
	}).change();	
	
	jQuery( '#enable_utm' ).change( function(){
		if ( jQuery( this ).attr( 'checked' ) ) {
			jQuery( '#setting_utm_source, #setting_utm_medium, #setting_utm_campaign' ).slideDown();
		} else {
			jQuery( '#setting_utm_source, #setting_utm_medium, #setting_utm_campaign' ).hide();
		}
	}).change();
}

function wtLoadNews() {
	var twitBoardNews = jQuery( '#blog-news-box-ajax' );
	if ( twitBoardNews.length ) {
		wtAdminAjax( 'wordtwit-news', {}, function( response ) {
			twitBoardNews.html( response );
			jQuery( '#blog-news-box' ).removeClass( 'loading' );
			jQuery( '#twitboard .box-holder' ).equalHeights( 280, 450 );
		});
	}	
}

function wtSavedOrReset() {
	if ( jQuery( '#bnc .saved' ).length ) {
		setTimeout( function() {
			jQuery( '#bnc .saved' ).fadeOut( 200 );
		}, 1500 );
	}

	if ( jQuery( '#bnc .reset' ).length ) {
		setTimeout( function() {
			jQuery( '#bnc .reset' ).fadeOut( 200 );
		}, 1500 );
	}

	/* Reset confirmation */
	jQuery( '#bnc-submit-reset input' ).click( function() {
		var answer = confirm( WordTwitProCustom.reset_admin_settings );
		if ( answer ) {
			jQuery.cookie( 'wordtwit-tab', '' );
			jQuery.cookie( 'wordtwit-list', '' );
		} else {
			return false;	
		}
	});
}

function wtDoDashboardAjax() {
	wtAdminAjax( 'dashboard-ajax', {}, function( response ) {
		jQuery( '#touchboard-ajax' ).html( response );
	});
}

jQuery( document ).ready( function() { doWordTwitReady(); } );
