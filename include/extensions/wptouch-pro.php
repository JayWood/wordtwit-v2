<?php

define( 'WORDTWIT_WPTOUCH_PRO_EXT', 1 );

function wordtwit_wptouch_has_accounts() {
	$accounts = wordtwit_wptouch_get_accounts();
	
	return ( count( $accounts ) );
}

function wordtwit_wptouch_get_accounts() {
	if ( !( $accounts = wp_cache_get( 'wordtwit_wptouch_accounts' ) ) ) {
	
		$wptouch_accounts = array();
			
		$accounts = wordtwit_get_accounts();
		
		foreach( $accounts as $name => $account ) {
			if ( $account->is_global || $account->is_site_wide ) {
				$accounts[ $name ] = $account;
			}	
		}	
		
		wp_cache_set( 'wordtwit_wptouch_accounts', $accounts );
	}
	
	return apply_filters( 'wordtwit_wptouch_accounts', $accounts );
}

function wordtwit_wptouch_get_tweets_for_account( $account_name ) {
	global $wordtwit_pro;
	
	$account_info = wordtwit_get_account_info( $account_name );
	if ( $account_info ) {
		$timeline = $wordtwit_pro->oauth->get_user_public_timeline( $account_name, $account_info->token, $account_info->secret );

		if ( $timeline ) {
			if ( is_array( $timeline ) && count( $timeline ) > 0 ) {
			
				$tweets = array();
				$i = 0;			
				foreach ( $timeline as $tweet ) {
					$tweets[$i]['created_at'] = $tweet->created_at;
					$tweets[$i]['text'] = $tweet->text;					
					$tweets[$i]['id'] = $tweet->id;
					$i++;
				}

				return $tweets;
			}
		}
	}
	
	return false;
}
