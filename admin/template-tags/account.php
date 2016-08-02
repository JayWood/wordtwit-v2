<?php

function wordtwit_get_twitter_authorize_url() {
	global $wordtwit_pro;
	
	return apply_filters( 'wordtwit_twitter_authorize_url', $wordtwit_pro->get_twitter_auth_url() );
}

function wordtwit_the_twitter_authorize_url() {
	echo wordtwit_get_twitter_authorize_url();	
}

function wordtwit_get_account_delete_url() {
	return apply_filters( 
		'wordtwit_account_delete_url', 
		add_query_arg( 
			array( 
				'wordtwit_action' => 'delete_account',
				'wordtwit_user' => wordtwit_get_account_screen_name(), 
				'wordtwit_nonce' => wp_create_nonce( 'wordtwit' ) 
			) 
		) 
	);	
}

function wordtwit_the_account_delete_url() {
	echo wordtwit_get_account_delete_url();
}

function wordtwit_get_account_refresh_url() {
	return apply_filters( 
		'wordtwit_account_refresh_url', 
		add_query_arg( 
			array( 
				'wordtwit_action' => 'refresh_account',
				'wordtwit_user' => wordtwit_get_account_screen_name(), 
				'wordtwit_nonce' => wp_create_nonce( 'wordtwit' ) 
			) 
		) 
	);		
}

function wordtwit_the_account_refresh_url() {
	echo wordtwit_get_account_refresh_url();
}

function wordtwit_get_account_owner() {
	global $wordtwit_account;
	
	if ( $wordtwit_account->owner == 0 ) {
		return __( 'Administrator', 'wordtwit-pro' );	
	} else {
		$user_data = get_userdata( $wordtwit_account->owner );
		global $current_user;
		get_currentuserinfo();		
		
		if ( $current_user->ID == $user_data->ID ) {
			return __( 'Me', 'wordtwit-pro' );	
		} else if ( strlen( $user_data->display_name ) ) {
			return $user_data->display_name;
		} else {
			return $user_data->user_login;
		}	
	}
}

function wordtwit_can_change_account_type() {
	global $wordtwit_account;	
	global $current_user;
	get_currentuserinfo();	
	
	if ( $wordtwit_account->owner == 0 ) {
		// Account is global and was created by an administrator
		// that means only another administrator can change the account type
		return current_user_can( 'manage_options' );
	} else {
		// This account is owned by a non-administrator
		// to prevent an admin hijacking a normal user's Twitter account, admin's can't promote these accounts
		return ( $current_user->ID == $wordtwit_account->owner );
	}	
}

function wordtwit_the_account_owner() {
	echo wordtwit_get_account_owner();
}


function wordtwit_get_account_type_change_url( $change_to ) {
	return apply_filters( 
		'wordtwit_account_type_change_url', 
		add_query_arg( 
			array( 
				'wordtwit_action' => 'change_account_type',
				'wordtwit_user' => wordtwit_get_account_screen_name(), 
				'wordtwit_account_type' => $change_to,
				'wordtwit_nonce' => wp_create_nonce( 'wordtwit' ) 
			) 
		) 
	);		
}

function wordtwit_the_account_type_change_url( $change_to ) {
	echo wordtwit_get_account_type_change_url( $change_to );
}

global $wordtwit_accounts_iterator;
global $wordtwit_account;

$wordtwit_accounts_iterator = false;

function wordtwit_has_accounts() {
	global $wordtwit_accounts_iterator;
	
	if ( !$wordtwit_accounts_iterator ) {	
		$wordtwit_accounts_iterator = new WordTwitArrayIterator( wordtwit_get_accounts() );	
	} 		
	
	return $wordtwit_accounts_iterator->have_items();
}

function wordtwit_get_account_count() {
	return count( wordtwit_get_accounts() );
}

function wordtwit_the_account() {
	global $wordtwit_account;
	global $wordtwit_accounts_iterator;
	
	$wordtwit_account = $wordtwit_accounts_iterator->the_item();
}

function wordtwit_the_account_screen_name() {
	echo wordtwit_get_account_screen_name();
}

function wordtwit_get_account_screen_name() {
	global $wordtwit_account;
	
	return apply_filters( 'wordtwit_account_screen_name', $wordtwit_account->screen_name );
}

function wordtwit_get_account_avatar() {
	global $wordtwit_account;
	
	return apply_filters( 'wordtwit_account_avatar', $wordtwit_account->profile_image_url );

}

function wordtwit_the_account_avatar() {
	echo wordtwit_get_account_avatar();	
}

function wordtwit_get_account_location() {
	global $wordtwit_account;
	
	if ( is_array( $wordtwit_account->location ) ) {
		return __( 'Unknown', 'wordtwit-pro' );	
	} else {
		return apply_filters( 'wordtwit_account_location', $wordtwit_account->location );	
	}
}

function wordtwit_the_account_location() {
	echo wordtwit_get_account_location();	
}

function wordtwit_account_is_default() {
	global $wordtwit_account;
	
	return $wordtwit_account->is_default;	
}

function wordtwit_get_account_followers() {
	global $wordtwit_account;
	
	return apply_filters( 'wordtwit_account_followers', $wordtwit_account->followers_count );
}

function wordtwit_the_account_followers() {
	echo wordtwit_get_account_followers();	
}


function wordtwit_get_account_status_updates() {
	global $wordtwit_account;
	
	return apply_filters( 'wordtwit_account_status_updates', $wordtwit_account->statuses_count );
}

function wordtwit_the_account_status_updates() {
	echo wordtwit_get_account_status_updates();	
}

function wordtwit_get_account_url() {
	global $wordtwit_account;
	
	return apply_filters( 'wordtwit_account_url', $wordtwit_account->url );		
}

function wordtwit_the_account_url() {
	echo wordtwit_get_account_url();
}

function wordtwit_is_account_global() {
	global $wordtwit_account;

	return $wordtwit_account->is_global;		
}

function wordtwit_is_account_site_wide() {
	global $wordtwit_account;
	
	if ( isset( $wordtwit_account->is_site_wide ) ) {
		return $wordtwit_account->is_site_wide;		
	} else {
		return false;	
	}
}
