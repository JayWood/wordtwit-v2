/* The WordTwit Post Widget JS */

var wtEditingTweet;
var wtSlideSpeed = 330;
var wtChecker = '';

function doWordTwitWidget() {	
	if ( WordTwitLoadJS == 0 ) {
		return;	
	}

	/* Option Bar Click function (accounts, hashtags, schedule) */
	jQuery( 'ul#wt-option-bar li.toggle' ).live( 'click', function(){
		var divToShow = jQuery( this ).attr( 'id' ) + '-box';
		var thisId = jQuery( this ).attr( 'id' );
		
		if ( jQuery( this ).hasClass( 'active' ) ) {
			jQuery( this ).removeClass( 'active' );	
			jQuery( '#wt-widget-bottom-wrap' ).slideUp( wtSlideSpeed );
		} else {
			jQuery( 'ul#wt-option-bar li.toggle' ).removeClass( 'active' );
			jQuery( this ).toggleClass( 'active' );
			jQuery( '#wt-widget-bottom-wrap' ).slideDown( wtSlideSpeed );
			jQuery( '.wt-widget-bottom:not(#'+divToShow+')' ).hide();
			jQuery( '#'+divToShow ).show();
		}
	});

	/* In-Place Editing Switch */
	jQuery( 'p.wt-tweet-text' ).live( 'click', function(){
		var ajax_params = {
			post: WordTwitPostID
		}
		
		var tweetTextArea = jQuery( this );
		wtEditingTweet = tweetTextArea.html();
		
		jQuery( '#wt-post-box-spinner' ).fadeIn( 100 );
		wtAdminAjax( 'get-tweet-template', ajax_params, function( result ) { 
			//alert( result );
			
		//	var tweetText = jQuery( this ).text();
			
			var tweetText = result;

			tweetTextArea.replaceWith( '<textarea id="wt-tweet-textarea">'+tweetText+'</textarea>' );
			jQuery( '#wt-tweet-textarea' ).focus();
			
			jQuery( '#wt-post-box-spinner' ).fadeOut( 100 );
		});
	});
	
	/* Adding checkbox toggling when clicking on image or text for usability */
	jQuery( '#wt-accounts-box li span, #wt-accounts-box li img' ).live( 'click', function(){
		var chkbox = jQuery( this ).parent().find( ':checkbox' );
		chkbox.attr( 'checked', !chkbox.attr( 'checked' ) );
	});
	
	/* The mode reset link */
	jQuery( '#wt-reset-link' ).live( 'click', function( e ) {
		jQuery( '#tweet-mode-text' ).html( WordTwitProCustom.automatic ).removeClass( 'manual' ).attr( 'data-manual', '' );
		
		jQuery( '#wt-reset-link' ).hide();
		
		wtSavePostBoxData();
		
		var ajax_params = {
			post: WordTwitPostID
		}
		
		wtAdminAjax( 'update-post-data', ajax_params, function( result ) { 
			jQuery( 'p.wt-tweet-text' ).html( result );
			wtStartDraftCheck();
		});	
		e.preventDefault();
	});
	
	/* Char count updater */
	jQuery( 'textarea#wt-tweet-textarea' ).live( 'keyup', function( e ) {
		wtUpdateTweetCount();
	});
	
	/* on carriage return or clicking outside textarea, save changes */
	jQuery( 'textarea#wt-tweet-textarea' ).live( 'keydown blur', function( e ) {
		if ( e.type == 'keydown' ) {
			var tweetText = jQuery( this ).val();
			//make carriage returns save
			if ( e.keyCode == '13' ) {
				wtSwapTextarea();
				e.preventDefault();
			}
		} else {
			wtSwapTextarea();			
		}
	});	

	/* Toggle for the recent/popular hashtag cloud, cookie saving */
	jQuery( 'a#hashtag-toggle' ).live( 'click', function( e ){
		if ( jQuery( 'ul#hashtag-cloud' ).hasClass( 'open' ) ) {
			jQuery( 'ul#hashtag-cloud' ).slideUp( wtSlideSpeed ).removeClass( 'open' );
			jQuery.cookie( 'wordtwit-pro-hashcloud', null );		
		} else {
			jQuery.cookie( 'wordtwit-pro-hashcloud', 1, { expires: 365 } );		
			jQuery( 'ul#hashtag-cloud' ).addClass( 'open' ).slideDown( wtSlideSpeed );		
		}
		e.preventDefault();
	});
	
	/* Delete 'x' for added hashtags */
	jQuery( 'ul.tagchecklist .hash-delete' ).live( 'click', function( e ) {
		jQuery( this ).parents( 'li' ).remove();
		wtSavePostBoxData();
		wtUpdateHashTagClasses();

		e.preventDefault();
	});
	
	/* Status changer (will be published, do not tweet) */
	jQuery( '#wt-edit-link, #wt-status-button' ).live( 'click', function( e ) {
		jQuery( '#wt-notweet' ).slideToggle( wtSlideSpeed );		
		e.preventDefault();
	});
	
	/* input for adding Hashtags */
	jQuery( '#wt-tags-box input.button' ).live( 'click', function( e ) {
		wtSubmitHashtag();
		e.preventDefault();
	});

	/* Allows carriage returns to add hashtags */
	jQuery( '#wt-add-hashtag' ).live( 'keydown', function( e ){
		if ( e.keyCode == '13' ) {
			wtSubmitHashtag();
			e.preventDefault();
		}
	});
	
	/* Prevents post publishing if tweets are over 140 chars */
	jQuery( '#publish' ).live( 'click', function( e ) {
		var tweetCount = parseFloat( jQuery( '#wt-count' ).html() );
		if ( tweetCount > 140 ) {
			alert( WordTwitProCustom.tweet_too_long );
			jQuery( this ).removeClass( 'button-primary-disabled' );
			jQuery( '#ajax-loading' ).css( 'visibility', 'hidden' );
			e.preventDefault();
			e.stopPropagation();	
		}
	});
	
	/* fires when the status is changed */
	jQuery( '#wt-status-button' ).live( 'click', function() {
		var currentValue = jQuery( '#wt-select-status' ).val();
		if ( currentValue == 0 ) {
			jQuery( '.wt-tweet-status span' ).html( WordTwitProCustom.disabled );	
			jQuery( '#wordtwit-post-widget' ).addClass( 'disabled' );
		} else {
			if ( WordTwitTweetStatus == 0 ) {
				jQuery( '.wt-tweet-status span' ).html( WordTwitProCustom.unpublished );
			} else {
				jQuery( '.wt-tweet-status span' ).html( 'Unknown' );	
			}
			jQuery( '#wordtwit-post-widget' ).removeClass( 'disabled' );
		}
		wtSavePostBoxData();
	});
	
	/* Hashtag pool click adds to used hashtags, disables them once added */
	jQuery( '#hashtag-cloud li' ).live( 'click', function() {
		if ( !jQuery( this ).hasClass( 'disabled' ) ) {
			var hashTagName = jQuery( this ).find( '.name' );
			wtAddHashTag( hashTagName.html() );
			wtSavePostBoxData();	
		}		
	});

	/* functions to fire onReady */
	wtScheduleSelect();
	wtUpdateTweetCount();
	wtUpdateTweetDivStyle();
	wtModeToggle();
	wtSetupWidgetCookie();
	wtUpdateHashTagClasses();
	wtStartDraftCheck();
	
	jQuery( 'a.wt-tweet-now-button' ).live( 'click', function( e ) {
		if ( WordTwitProCustom.retweet_warning_enable == '1' ) {
			if ( !confirm( WordTwitProCustom.retweet_warning ) ) {
				e.preventDefault();
			}
		}
	});

	/* Don't move this above, it needs to be at the end */
	/* save the update post information whenever an account is toggled */
	jQuery( '#wt-accounts-box input, #wt-sheduling select, #wt-select-times, #wt-select-delay' ).live( 'change', function() {
		wtSavePostBoxData();
	});	

} /* end doc ready */

/* Performs the check for the latest WP draft, called by wtStartDraftCheck() interval  */
/* Will update the tweet title component with the latest saved title */
function wordTwitGetDraftText() {
	var draftText = jQuery( '#post-status-info td.autosave-info #autosave' );
	if ( draftText.length == 0 ) {
		draftText = jQuery( '#post-status-info td.autosave-info .autosave-message' ).html();	
	} else {
		draftText = draftText.html();
	}	
	
	return draftText;
}

var wordTwitDraftText = wordTwitGetDraftText();


function wtDraftSaveChecker() {
	var draftText = wordTwitGetDraftText();
	if ( draftText != wordTwitDraftText ) {
		wordTwitDraftText = draftText;
		wtSavePostBoxData();
		console.log( 'WordTwit: Draft saved, updating WordTwit tweet title.' );	
	} else {
		console.log( 'WordTwit: No WP draft title changes.' );	
	}
}

function wtStartDraftCheck() {
	if ( wtChecker == '' && !jQuery( '#wordtwit-post-widget' ).hasClass( 'disabled' ) && !jQuery( '#tweet-mode-text' ).hasClass( 'manual' ) ) {
		wtChecker = window.setInterval( 'wtDraftSaveChecker()', 1000 * 3 );  // check for new draft saves every 3 seconds
		console.log( 'WordTwit: Draft check interval set (3 seconds).' );
	}
}

function clearDraftCheck(){
	if ( wtChecker != '' ) {
		wtChecker = window.clearInterval( wtChecker );  // clear the draft checker
		wtChecker = '';
		console.log( 'WordTwit: Interval cleared.' );
	}
}

/* Per-post cookie for option bar settings access  */
function wtSetupWidgetCookie() {
	jQuery( 'ul#wt-option-bar li' ).live( 'click', function() {
		var thisId = jQuery( this ).attr( 'id' );
		if ( jQuery( this ).hasClass( 'active' ) ) {
			jQuery.cookie( 'wordtwit-pro-' + WordTwitPostID, thisId, { expires: 1 } );
		} else {
			jQuery.cookie( 'wordtwit-pro-' + WordTwitPostID, null );		
		}
	});
	
	var cookieValue = jQuery.cookie( 'wordtwit-pro-' + WordTwitPostID );
	if ( cookieValue ) {
		jQuery( '#' + cookieValue ).click();
	}	
}

/* Fired when the tweet text div is exited after in-place editing */
function wtSwapTextarea() {
	var textArea =	jQuery( '#wt-tweet-textarea' );
	var tweetText = jQuery( textArea ).val();
		
	var ajax_params = {
		post: WordTwitPostID,
		tweet_template: tweetText
	}

	jQuery( '#wt-post-box-spinner' ).fadeIn( 100 );
	wtAdminAjax( 'update-tweet-template', ajax_params, function( result ) { 
		jQuery( textArea ).replaceWith( '<p class="wt-tweet-text">'+result+'</p>' ).remove();
		if ( wtEditingTweet != result ) {
			jQuery( '#tweet-mode-text' ).html( WordTwitProCustom.manual ).addClass( 'manual' ).attr( 'data-manual', '1' );
			jQuery( '#wt-reset-link' ).show();
					
			// save the post data when the tweet text is changed
			wtSavePostBoxData();
		}
		
		jQuery( '#wt-post-box-spinner' ).fadeOut( 100 );
	});
}

/* Cleaned up hashtag additions to the used hashtags list */
function wtAddHashTag( hashTag ) {
	var cleanedUpTag = jQuery.trim( hashTag );
	var existingTag = jQuery( 'ul.tagchecklist' ).find( 'li#' + cleanedUpTag );
	if ( !existingTag.length ) {
		jQuery( 'ul.tagchecklist' ).append( '<li id="' + cleanedUpTag + '"><a href="#" class="hash-delete">X</a><span>' + cleanedUpTag + '</span></li>' );	
	}	
	
	wtUpdateHashTagClasses();
}

/* checks against the pool to add disabled classes to in-use hashtags */
function wtUpdateHashTagClasses() {
	jQuery( '#hashtag-cloud li' ).removeClass( 'disabled' );
	
	jQuery( 'ul.tagchecklist li span' ).each( function() {
		var hashTag = jQuery( this ).html();
		jQuery( '#hashtag-cloud li.' + hashTag + '-hashtag' ).addClass( 'disabled' );	
	});
}

/* the submit/save routine for adding hashtags */
function wtSubmitHashtag() {
	var newTag = jQuery( '#wt-add-hashtag' ).val();
	var trimmedTag = newTag.replace(/ /g,'');
	var splitTags = trimmedTag.split( ',' );
	
	jQuery( splitTags ).each( function() {
		wtAddHashTag( this );
	});
	
	jQuery( '#wt-add-hashtag' ).val( '' );
	wtSavePostBoxData();
}

/* looks at the length of the tweet and adds classes for warning and overage if needed */
function wtUpdateTweetCount() {
	var innerText;
	var theTextArea = jQuery( 'textarea#wt-tweet-textarea' );
	if ( theTextArea.length ) {
		innerText = theTextArea.val();
	} else {
		innerText = jQuery( 'p.wt-tweet-text' ).html();
	}

	var charCount = jQuery( '#wt-count' );

	jQuery( charCount ).html( innerText.length );

	jQuery( charCount ).removeClass();
	
	if ( innerText.length > 140 ) {
		jQuery( charCount ).addClass( 'overchars' );	
	} else if ( innerText.length > 134 ) {
		jQuery( charCount ).addClass( 'warnchars' );
	} 
}

/* Adds styling to the tweet itself in red to highlight character overage */
function wtUpdateTweetDivStyle() {
	var charCount = parseFloat( jQuery( '#wt-count' ).html() );
	if ( charCount > 140 ) {
		jQuery( 'p.wt-tweet-text' ).addClass( 'overchars' );	
	} else {
		jQuery( 'p.wt-tweet-text' ).removeClass( 'overchars' );
	}
}

/* The saving routine for the whole-shebang. */
function wtSavePostBoxData() {
	jQuery( '#wt-post-box-spinner' ).fadeIn( 100 );
	
	var ajax_params = {
		manual: 0,
		post: WordTwitPostID
	};
	
	if ( jQuery( '.wt-tweet-mode span' ).attr( 'data-manual' ) == 1 ) {
		ajax_params[ 'manual' ] = 1;
		ajax_params[ 'tweet_text' ] =  jQuery( 'p.wt-tweet-text' ).html();
		clearDraftCheck();
	}
	
	jQuery( '#wt-accounts-box input' ).each( function() {
		if ( jQuery( this ).attr( 'checked' ) ) {
			var accountName = jQuery( this ).attr( 'name' );
			ajax_params[ accountName ] = 1;
		}
	});
	
	var hashNum = 1;
	jQuery( 'ul.tagchecklist li span' ).each( function() {
		ajax_params[ 'hash_' + hashNum ] = jQuery( this ).html();
		hashNum = hashNum + 1;
	});
	
	ajax_params[ 'tweet_times' ] = jQuery( '#wt-select-times' ).val();
	if ( ajax_params[ 'tweet_times' ] > 1 ) {
		ajax_params[ 'tweet_sep_min' ] = jQuery( '#wt-select-mins' ).val();
	}
	
	ajax_params[ 'tweet_delay' ] = jQuery( '#wt-select-delay' ).val();
	ajax_params[ 'enabled' ] = jQuery( '#wt-select-status' ).val();
		
	wtAdminAjax( 'save-post-data', ajax_params, function( result ) { 
		jQuery( 'img#wt-post-box-spinner' ).fadeOut( 100 );	
		jQuery( 'p.wt-tweet-text' ).html( result );
		wtDisabledToggle();
		wtUpdateTweetCount();
		wtUpdateTweetDivStyle();
		wtModeToggle();
	});		
}

/* Adds and removes the disabled class from the widget. Needed for when the status is changed */
function wtDisabledToggle() {
	if ( jQuery( '#wordtwit-post-widget' ).hasClass( 'disabled' ) ) {
		jQuery( '#disabled-wrapper' ).hide();
	} else {
		jQuery( '#disabled-wrapper' ).show();
	}
}

/* The select toggle for when tweet 2+ times are chosen */
function wtScheduleSelect() {
	jQuery( '#wt-select-times' ).change( function() {
		switch( jQuery( this ).val() ) {
			case '1':
				jQuery( '#wt-sheduling' ).hide();
				break;
			default:
				jQuery( '#wt-sheduling' ).slideDown( wtSlideSpeed );
				break;	
		}
	}).change();
}

/* When the mode is changed their respective divs are shown/hidden */
function wtModeToggle() {
	var ajax_params = {
		post: WordTwitPostID
	};
	
	wtAdminAjax( 'are-hash-tags-enabled', ajax_params, function( result ) { 
		if ( result == 'yes' ) {
			jQuery( '.wt-automatic' ).show();
			jQuery( '.wt-manual' ).hide();			
		} else {
			jQuery( '.wt-automatic' ).hide();
			jQuery( '.wt-manual' ).show();		
		}
	});
	/*
	if ( jQuery( '#tweet-mode-text' ).hasClass( 'manual' ) ) {
		jQuery( '.wt-automatic' ).hide();
		jQuery( '.wt-manual' ).show();
	} else {
		jQuery( '.wt-automatic' ).show();
		jQuery( '.wt-manual' ).hide();	
	}
	*/
}

/* The WordTwit Admin Ajax function */
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

jQuery( document ).ready( function() { if ( jQuery( '#wordtwit-post-widget' ).length ) { doWordTwitWidget(); } } );

// will ignore console statements in browsers that don't support them.
if( typeof( console ) === 'undefined' ) {
    var console = {}
    console.log = console.error = console.info = console.debug = console.warn = console.trace = console.dir = console.dirxml = console.group = console.groupEnd = console.time = console.timeEnd = console.assert = console.profile = function() {};
}

/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
jQuery.cookie=function(b,j,m){if(typeof j!="undefined"){m=m||{};if(j===null){j="";m.expires=-1}var e="";if(m.expires&&(typeof m.expires=="number"||m.expires.toUTCString)){var f;if(typeof m.expires=="number"){f=new Date();f.setTime(f.getTime()+(m.expires*24*60*60*1000))}else{f=m.expires}e="; expires="+f.toUTCString()}var l=m.path?"; path="+(m.path):"";var g=m.domain?"; domain="+(m.domain):"";var a=m.secure?"; secure":"";document.cookie=[b,"=",encodeURIComponent(j),e,l,g,a].join("")}else{var d=null;if(document.cookie&&document.cookie!=""){var k=document.cookie.split(";");for(var h=0;h<k.length;h++){var c=jQuery.trim(k[h]);if(c.substring(0,b.length+1)==(b+"=")){d=decodeURIComponent(c.substring(b.length+1));break}}}return d}};