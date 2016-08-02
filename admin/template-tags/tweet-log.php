<?php

function wordtwit_get_accounts_for_tweet( $tweet_log_id = false ) {	
	$tweet_account = false;
	
	if ( !$tweet_log_id ) {
		$tweet_log_id = $post->ID;	
	}
		
	$tweet_account = get_post_meta( $tweet_log_id, 'wordtwit_account', true );
	
	return apply_filters( 'wordtwit_accounts_for_tweet', $tweet_account );
}

function wordtwit_the_accounts_for_tweet( $tweet_log_id = false ) {
	if ( !$tweet_log_id ) {
		global $wordtwit_tweet_log_entry;	
		$tweet_log_id = $wordtwit_tweet_log_entry->ID;	
	}
	
	$account = wordtwit_get_accounts_for_tweet( $tweet_log_id );
	if ( $account ) {
		echo $account;	
	}
}

function wordtwit_get_tweet_log_filter() {
	if ( isset( $_GET['filter'] ) ) {
		return $_GET['filter'];	
	} else {
		return 'all';	
	}
}

function wordtwit_get_tweet_log_per_page() {
	return apply_filters( 'wordtwit_tweet_log_per_page', 30 );
}

function wordtwit_get_tweet_log_cur_page() {
	if ( isset( $_GET['tpage'] ) ) {
		return $_GET['tpage'];
	} else {
		return 1;	
	}	
}

function wordtwit_get_tweet_log_total_entries( $filter = false ) {
	global $wpdb;
	
	if ( $filter ) {
		$cur_filter = $filter;
	} else {
		$cur_filter = wordtwit_get_tweet_log_filter();
	}
	
	
	$mysql_and = '';
	if ( !wordtwit_user_is_admin() ) {
		// restrict Tweet log to only those that were tweeted by the author
		global $user_ID;
		get_currentuserinfo();
		$mysql_and = ' AND post_author = ' . $user_ID;
	}	
	
	switch( $cur_filter ) {
		case 'all':
			$result = $wpdb->get_row( "SELECT count(*) AS c FROM " . $wpdb->posts . " WHERE post_type = 'tweet' AND post_status IN ('future', 'publish')" . $mysql_and );
			break;	
		case 'publish':
			$result = $wpdb->get_row( "SELECT count(*) AS c FROM " . $wpdb->posts . " WHERE post_type = 'tweet' AND post_status = 'publish' AND post_content <> 'error'" . $mysql_and );
			break;
		case 'future':
			$result = $wpdb->get_row( "SELECT count(*) AS c FROM " . $wpdb->posts . " WHERE post_type = 'tweet' AND post_status = 'future'" . $mysql_and );
			break;
		case 'errors':
			$result = $wpdb->get_row( "SELECT count(*) AS c FROM " . $wpdb->posts . " WHERE post_type = 'tweet' AND post_status = 'publish' AND post_content = 'error'" . $mysql_and );
			break;			
		default:
			$result = false;
	}	
	
	if ( $result ) {
		return $result->c;	
	} else {
		return 0;	
	}
}

function wordtwit_get_tweet_log_total_pages() {
	$entries = wordtwit_get_tweet_log_total_entries();
	if ( $entries ) {
		$per_page = wordtwit_get_tweet_log_per_page();
		if ( $entries % $per_page == 0 ) {
			return floor( $entries / $per_page );
		} else {
			return floor( $entries / $per_page ) + 1;
		}	
	} else {
		return '';	
	}
}

global $wordtwit_tweet_log_entry;
global $wordtwit_tweet_log_iterator;

$wordtwit_tweet_log_entry = false;
$wordtwit_tweet_log_iterator = false;

function wordtwit_get_tweet_log_sql() {
	global $wpdb;
	
	$current_page = wordtwit_get_tweet_log_cur_page();	
	$total_pages = wordtwit_get_tweet_log_total_pages();
	$offset = ( $current_page - 1 ) * wordtwit_get_tweet_log_per_page();
	
	$filter_type = wordtwit_get_tweet_log_filter();
	switch( $filter_type ) {
		case 'all':
			$sql_and = "";
			break;	
		case 'publish':
			$sql_and = " AND post_status = 'publish' AND post_content <> 'error'";
			break;
		case 'future':
			$sql_and = " AND post_status = 'future'";
			break;
		case 'errors':
			$sql_and = " AND post_status = 'publish' AND post_content = 'error'";
			break;			
	}
	
	if ( !wordtwit_user_is_admin() ) {
		// restrict Tweet log to only those that were tweeted by the author
		global $user_ID;
		get_currentuserinfo();
		$sql_and = $sql_and . ' AND post_author = ' . $user_ID;
	}		
	
	$sql = "SELECT * FROM " . $wpdb->posts . " WHERE post_type = 'tweet'" . $sql_and . " AND post_status IN ('publish', 'future') ORDER BY post_date DESC LIMIT " . wordtwit_get_tweet_log_per_page() . " OFFSET " . $offset;
	
	return $sql;
	
}

function wordtwit_have_tweet_log() {
	global $wpdb;
	global $wordtwit_tweet_log_entry;
	global $wordtwit_tweet_log_iterator;	
	
	if ( $wordtwit_tweet_log_iterator ) {
		return $wordtwit_tweet_log_iterator->have_items();	
	} else {
		$sql = wordtwit_get_tweet_log_sql();
		$result = $wpdb->get_results( $sql );
		if ( $result ) {
			$wordtwit_tweet_log_iterator = new WordTwitArrayIterator( $result );
			return $wordtwit_tweet_log_iterator->have_items();
		}
	}
}

function wordtwit_the_tweet_log() {
	global $wordtwit_tweet_log_entry;
	global $wordtwit_tweet_log_iterator;	
	
	if ( $wordtwit_tweet_log_iterator ) {
		$wordtwit_tweet_log_entry = $wordtwit_tweet_log_iterator->the_item();	
	}
}

function wordtwit_get_tweet_log_title() {
	global $wordtwit_tweet_log_entry;
	$title = false;
	
	if ( $wordtwit_tweet_log_entry ) {
		$title = $wordtwit_tweet_log_entry->post_title;	
	}
		
	return apply_filters( 'wordtwit_tweet_log_title', $title );	
}

function wordtwit_the_tweet_log_title() {
	echo wordtwit_get_tweet_log_title();
}

function wordtwit_get_tweet_log_date() {
	global $wordtwit_tweet_log_entry;
	return apply_filters( 'wordtwit_tweet_log_date', strtotime( $wordtwit_tweet_log_entry->post_date ) );
}

function wordtwit_get_tweet_log_status() {
	global $wordtwit_tweet_log_entry;
	
	if ( $wordtwit_tweet_log_entry->post_content == 'error' ) {
		return 'error';	
	} else {
		return $wordtwit_tweet_log_entry->post_status;
	}
}

function wordtwit_get_tweet_log_id() {
	global $wordtwit_tweet_log_entry;

	return $wordtwit_tweet_log_entry->ID;
}

function wordtwit_get_tweet_log_error() {
	global $wordtwit_tweet_log_entry;

	$tweet_result = get_post_meta( $wordtwit_tweet_log_entry->ID, 'wordtwit_result', true );
	if ( $tweet_result ) {
		$result = $tweet_result->error;
	} else {
		$result = false;	
	}
	
	return apply_filters( 'wordtwit_tweet_log_error', $result );
}

function wordtwit_the_tweet_log_error() {
	echo wordtwit_get_tweet_log_error();	
}

function wordtwit_get_tweet_log_paginated_links() {
	global $wp_query, $wp_rewrite;
	if ( isset( $_GET['tpage'] ) ) { 
		$current = $_GET['tpage'];
	} else { 
		$current = 1;
	}
	
	$pagination = array(
		'base' => @add_query_arg( 'tpage','%#%' ),
		'format' => '',
		'total' => wordtwit_get_tweet_log_total_pages(),
		'current' => $current,
		'show_all' => true,
		'type' => 'plain'
	);
	
	echo paginate_links( $pagination );	
}

function wordtwit_get_tweet_log_tweet_now_link() {
	global $wordtwit_tweet_log_entry;
		
	return add_query_arg( array( 'wordtwit_tweet_action' => 'tweet_now', 'log_id' => $wordtwit_tweet_log_entry->ID, 'wordtwit_nonce' => wp_create_nonce( 'wordtwit' ) ) );
}

function wordtwit_get_tweet_log_retweet_link() {
	global $wordtwit_tweet_log_entry;
		
	return add_query_arg( array( 'wordtwit_tweet_action' => 'retweet', 'log_id' => $wordtwit_tweet_log_entry->ID, 'wordtwit_nonce' => wp_create_nonce( 'wordtwit' ), 'wordtwit_account' => wordtwit_get_accounts_for_tweet( $wordtwit_tweet_log_entry->ID ) ) );
}

function wordtwit_get_tweet_log_delete_link() {
	global $wordtwit_tweet_log_entry;
		
	return add_query_arg( array( 'wordtwit_tweet_action' => 'delete', 'log_id' => $wordtwit_tweet_log_entry->ID, 'wordtwit_nonce' => wp_create_nonce( 'wordtwit' ) ) );
}


function wordtwit_tweet_log_can_retweet() {
	global $wordtwit_tweet_log_entry;
	
	if ( $wordtwit_tweet_log_entry && $wordtwit_tweet_log_entry->ID ) {
		require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
		
		$associated_post_id = get_post_meta( $wordtwit_tweet_log_entry->ID, 'wordtwit_real_post', true );
		if ( $associated_post_id ) {
			$tweet_info = wordtwit_get_saved_tweet_info( $associated_post_id );
			if ( $tweet_info ) {
				$settings = wordtwit_get_settings();
				// we can retweet if the tweet mode is automatic and we're using a URL shortener
				if ( !$settings->url_shortener != 'wordpress' ) {
					return true;
				}
			}
		}
	}	
	
	return false;
}

