<?php

define( 'WORDTWIT_TWEET_UNPUBLISHED', 0 );
define( 'WORDTWIT_TWEET_SCHEDULED', 1 );
define( 'WORDTWIT_TWEET_PUBLISHED', 2 );
define( 'WORDTWIT_TWEET_IS_OLD', 3 );
define( 'WORDTWIT_TWEET_IS_DEFERRED', 4 );

define( 'WORDTWIT_TWEET_AUTOMATIC', 0 );
define( 'WORDTWIT_TWEET_MANUAL', 1 );

define( 'WORDTWIT_PRO_INSTALLED', 1 );

define( 'WORDTWIT_EXTENSION_DIR', WORDTWIT_DIR . '/include/extensions' );

add_action( 'wordtwit_loaded', 'wordtwit_load_extensions' );

global $wordtwit_pro;

function wordtwit_get_settings() {
	global $wordtwit_pro;

	return $wordtwit_pro->get_settings();	
}

function wordtwit_user_is_admin() {
	return ( current_user_can( 'manage_options' ) || current_user_can( 'manage_wordtwit' ) );	
}

function wordtwit_user_can_make_global() {
	return ( current_user_can( 'manage_options' ) || current_user_can( 'manage_wordtwit' ) );	
}

function wordtwit_user_can_contribute_posts() {
	return current_user_can( wordtwit_user_get_role_for_contribute_posts() );	
}

function wordtwit_user_get_role_for_contribute_posts() {
	return 'edit_posts';	
}

function wordtwit_user_can_add_account() {
	if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_wordtwit' ) ) {
		return true;	
	} else {
		$settings = wordtwit_get_settings();
		
		return ( $settings->contributors_can_add_accounts && current_user_can( $settings->minimum_user_capability_for_account_add ) );	
	}
}

function wordtwit_user_get_role_for_add_account() {
	if ( current_user_can( 'manage_options' ) ) {
		return 'manage_options';	
	} else if ( current_user_can( 'manage_wordtwit' ) ) {
		return 'manage_wordtwit';	
	} else {
		$settings = wordtwit_get_settings();
		return $settings->minimum_user_capability_for_account_add;	
	}
}

function wordtwit_user_can_modify_settings() {
	return ( current_user_can( 'manage_options' ) || current_user_can( 'manage_wordtwit' ) );
}

function wordtwit_user_get_role_for_modify_settings() {
	if ( current_user_can( 'manage_options' ) ) {
		return 'manage_options';	
	} else if ( current_user_can( 'manage_wordtwit' ) ) {
		return 'manage_wordtwit';	
	}	
}

function wordtwit_sort_accounts_for_user( $accounts ) {
	global $user_ID;
	get_currentuserinfo();
	
	if ( $user_ID ) {
		$account_order = get_user_meta( $user_ID, 'wordtwit_account_order', true );
		if ( $account_order ) {
			$new_accounts = array();
			
			foreach( $account_order as $account_name ) {
				if ( isset( $accounts[ $account_name ] ) ) {
					$new_accounts[ $account_name ] = $accounts[ $account_name ];
					unset( $accounts[ $account_name ] );
				}
				
				// We should now have a sorted list of accounts based on the user's preferences in new_accounts
				// merge in any that weren't sorted at the bottom
				if ( count ( $accounts ) ) {
					$accounts = array_merge( $new_accounts, $accounts );
				} else {
					$accounts = $new_accounts;
				}
			}
		}
	}
	
	return apply_filters( 'wordtwit_sort_accounts', $accounts );
}

function wordtwit_get_accounts() {
	$accounts = array();
	
	if ( wordtwit_is_multisite_enabled() ) {
		global $wordtwit_pro;
				
		// load site accounts
		$site_accounts = $wordtwit_pro->get_site_accounts();
		if ( $site_accounts && is_array( $site_accounts ) && count( $site_accounts ) ) {
			$accounts = $site_accounts;	
		}
	}
	
	$settings = wordtwit_get_settings();	
	if ( isset( $settings->accounts ) ) {
		$local = array();
		$global = array();
		foreach( $settings->accounts as $key => $value ) {
			if ( wordtwit_current_user_can_view_account( $value ) ) {
				if ( $value->is_global ) {
					$global[ $key ] = $value;
				} else {
					$local[ $key ] = $value;
				}	
			}
		}	
		
		ksort( $global );
		ksort( $local );
	
		$accounts = array_merge( $accounts, $global, $local );
	} 
	
	// sort accounts now
	$accounts = wordtwit_sort_accounts_for_user( $accounts );
	
	return apply_filters( 'wordtwit_accounts', $accounts );
}

function wordtwit_get_account_info( $account_name ) {
	if ( wordtwit_is_multisite_enabled() ) {
		global $wordtwit_pro;
				
		// load site accounts
		$site_accounts = $wordtwit_pro->get_site_accounts();
		if ( isset( $site_accounts[ $account_name ] ) ) {
			return $site_accounts[ $account_name ];	
		}
	}
	
	$settings = wordtwit_get_settings();	
	if ( isset( $settings->accounts[ $account_name ] ) ) {
		return $settings->accounts[ $account_name ];	
	}		
	
	return false;
}

function wordtwit_update_account( $account_name, $account_info ) {
	global $wordtwit_pro;
			
	if ( wordtwit_is_multisite_enabled() ) {
		// load site accounts
		$site_accounts = $wordtwit_pro->get_site_accounts();
		if ( isset( $site_accounts[ $account_name ] ) ) {
			$site_accounts[ $account_name ] = $account_info;
			$wordtwit_pro->save_site_accounts( $site_accounts );
			
			return;
		}
	}
	
	$settings = wordtwit_get_settings();	
	if ( isset( $settings->accounts[ $account_name ] ) ) {
		$settings->accounts[ $account_name ] = $account_info;
		$wordtwit_pro->save_settings( $settings );
		
		return;	
	}		
}

function wordtwit_delete_account( $account_name ) {
	global $wordtwit_pro;	
	
	if ( wordtwit_is_multisite_enabled() ) {			
		// load site accounts
		$site_accounts = $wordtwit_pro->get_site_accounts();
		if ( isset( $site_accounts[ $account_name ] ) ) {
			unset( $site_accounts[ $account_name ] );
			$wordtwit_pro->save_site_accounts( $site_accounts );
			
			return;
		}
	}
	
	$settings = wordtwit_get_settings();	
	if ( isset( $settings->accounts[ $account_name ] ) ) {
		unset( $settings->accounts[ $account_name ] );
		$wordtwit_pro->save_settings( $settings );
		
		return;	
	}	
}

function wordtwit_current_user_can_view_account( $account = false ) {
	global $wordtwit_account;
	
	if ( !$account ) {
		$account = $wordtwit_account;
	}
	
	if ( wordtwit_user_is_admin() || $account->is_global || $account->is_site_wide ) {
		return true;	
	} else {
		global $current_user;
		get_currentuserinfo();		
		
		return ( $current_user->ID == $account->owner );		
	}
}

function wordtwit_current_user_can_tweet_from_account( $account = false ) {
	global $wordtwit_account;
	
	if ( !$account ) {
		$account = $wordtwit_account;
	}
	
	if ( $account->is_global || $account->is_site_wide ) {
		return true;	
	} else {
		// private
		global $current_user;
		get_currentuserinfo();		
		
		return ( $current_user->ID == $account->owner );		
	}
	
	return false;
}

function wordtwit_current_user_can_delete_account( $account = false ) {
	global $wordtwit_account;
	
	if ( !$account ) {
		$account = $wordtwit_account;
	}
	
	if ( $account->is_site_wide ) {
		return is_super_admin();	
	}
	
	if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_wordtwit' ) ) {
		return true;	
	} else {
		global $current_user;
		get_currentuserinfo();		
		
		return ( $current_user->ID == $account->owner );		
	}
}

function wordtwit_current_user_can_modify_account( $account = false ) {
	global $wordtwit_account;
	
	if ( !$account ) {
		$account = $wordtwit_account;
	}
	
	if ( $account->owner ) {
		// Local account
		global $current_user;
		get_currentuserinfo();	
				
		return ( wordtwit_user_can_add_account() && ( $current_user->ID == $account->owner ) );
	} else {
		// Global account
		if ( $account->is_site_wide ) {
			return is_super_admin();	
		} else {
			return current_user_can( 'manage_options' ) || current_user_can( 'manage_wordtwit' );
		}
	}
}


function wordtwit_get_tweet_templates( $retweets = false ) {
	$tweet_templates = array(
		'title_link_hashtags' => '[title] - [link] [hashtags]',
		'title_author_short_link_hashtags' => __( '[title] by [short_author] - [link] [hashtags]', 'wordtwit-pro' ),
		'title_author_full_link_hashtags' => __( '[title] by [full_author] - [link] [hashtags]', 'wordtwit-pro' ),
		'title_nickname_link_hashtags' => __( '[title] by @[nickname_author] - [link] [hashtags]', 'wordtwit-pro' ),
		'post_type_title_link_hashtags' => __( 'New [post_type]: [title] - [link] [hashtags]', 'wordtwit-pro' ),
		'post_type_title_short_author_link_hashtags' => __( 'New [post_type]: [title] by [short_author] - [link] [hashtags]', 'wordtwit-pro' ),
		'post_type_title_full_author_link_hashtags' => __( 'New [post_type]: [title] by [full_author] - [link] [hashtags]', 'wordtwit-pro' ),		
		'custom' => __( 'Custom', 'wordtwit-pro' )
	);	
	
	return apply_filters( 'wordtwit_tweet_templates', $tweet_templates );
}

function wordtwit_get_short_post_link( $num = 1 ) {
	$settings = wordtwit_get_settings();
	
	$link = get_permalink();
	
	if ( $num > 1 ) {
		$link = add_query_arg( 'wt', $num, $link );
	}
	
	if ( $settings->enable_utm ) {
		$link = add_query_arg( array( 'utm_source' => $settings->utm_source, 'utm_campaign' => $settings->utm_campaign, 'utm_medium' => $settings->utm_medium ), $link );	
	}

	switch( $settings->url_shortener ) {
		case 'bitly':		
			require_once( WORDTWIT_DIR . '/include/shorteners/bitly.php' );
			$tinyurl = new WordTwitBitlyShortener( $settings->bitly_username, $settings->bitly_api_key );
			$link = $tinyurl->shorten( $link );
			break;
		case 'isgd':
			require_once( WORDTWIT_DIR . '/include/shorteners/isgd.php' );
			$isgd_link = new WordTwitIsgdShortener;
			$link = $isgd_link->shorten( $link );			
			break;
		case 'tinyurl':
			require_once( WORDTWIT_DIR . '/include/shorteners/tinyurl.php' );
			$tinyurl = new WordTwitTinyUrlShortener;
			$link = $tinyurl->shorten( $link );
			break;
		case 'stumbleupon':
			require_once( WORDTWIT_DIR . '/include/shorteners/stumbleupon.php' );
			$tinyurl = new WordTwitStumbleUponShortener;
			$link = $tinyurl->shorten( $link );
			break;			
		case 'cloudapp':
			/*
			global $post;
			require_once( WORDTWIT_DIR . '/include/shorteners/cloudapp.php' );
			
			if ( !isset( $post->post_status ) || $post->post_status != 'publish' ) {
				$link = home_url();
			}			
			$link_hash = md5( $link );
			
			$post_meta = get_post_meta( $post->ID, 'cloud_app_urls', true );
			if ( $post_meta && isset( $post_meta[ $link_hash ] ) ) {
				$link = $post_meta[ $link_hash ];	
			} else {
				$tinyurl = new CloudAppShortener( $settings->cloudapp_username, $settings->cloudapp_password );
				if ( isset( $post->post_status ) && $post->post_status == 'publish' ) {
					$link = $tinyurl->shorten( $link, get_the_title() );		
				} else {
					$link = $tinyurl->shorten( $link, get_bloginfo( 'name' ) );
				}
				
				$post_meta[ $link_hash ] = $link;
				
				update_post_meta( $post->ID, 'cloud_app_urls', $post_meta );
			}
			*/
			break;
		case 'yourls':
			require_once( WORDTWIT_DIR . '/include/shorteners/yourls.php' );
			
			global $post;
			$yourls_link = new WordTwitYourlsShortener( $settings->yourls_path, $settings->yourls_signature );			
			
			if ( isset( $post->post_status ) && $post->post_status == 'publish' ) {
				$link = $yourls_link->shorten( $link );	
			} else {
				$link = $yourls_link->shorten( home_url() );	
			}	
			break;
		case 'owly':
			require_once( WORDTWIT_DIR . '/include/shorteners/owly.php' );
			$tinyurl = new WordTwitOwlyShortener;
			$link = $tinyurl->shorten( $link );		
			break;
		case 'wordpress':
			$link = rtrim( get_home_url(), '/' ) . '?p=' . get_the_ID();
			
			if ( $num > 1 ) {
				$link = add_query_arg( 'wt', $num, $link );	
			}
			
			break;
	}
	
	return apply_filters( 'wordtwit_short_port_link', $link );
}

function wordtwit_get_bloginfo( $param ) {
	global $wpdb;
	$setting = false;
	
	switch( $param ) {
		case 'published_tweets':
			$result = $wpdb->get_row( "SELECT count(*) AS c FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type = 'tweet'" );
			if ( $result ) {
				$setting = $result->c;	
			}
			break;
		case 'scheduled_tweets':
			$result = $wpdb->get_row( "SELECT count(*) AS c FROM " . $wpdb->posts . " WHERE post_status = 'future' AND post_type = 'tweet'" );
			if ( $result ) {
				$setting = $result->c;	
			}		
			break;
		case 'total_accounts':
			$settings = wordtwit_get_settings();
			if ( $settings ) {
				if ( is_array( $settings->accounts ) ) {
					return count( $settings->accounts );
				} else {
					return 0;
				}		
			}
			break;
	}
	
	return $setting;	
}

function wordtwit_the_bloginfo( $param ) {
	echo wordtwit_get_bloginfo( $param );	
}

function wordtwit_is_twitter_api_up() {
	$do_check = true;
	$twitter_api_result = get_option( WORDTWIT_API_CHECK_SETTING_NAME, false );
	if ( is_object( $twitter_api_result ) ) {
		$next_check_time = $twitter_api_result->last_check_time + WORDTWIT_API_CHECK_TIME;
		if ( time() < $next_check_time ) {
			return $twitter_api_result->result;
		}
	} 
	
	$twitter_api = new WordTwitOAuth();
	
	$result = $twitter_api->get_user_info( 'bravenewcode' );	
	
	if ( $result ) {
		$twitter_api_result = new stdClass;
		$twitter_api_result->last_check_time = time();
		$twitter_api_result->result = ( $result && isset( $result['user'] ) );
		
		update_option( WORDTWIT_API_CHECK_SETTING_NAME, $twitter_api_result );
		
		return $twitter_api_result->result;
	}
}

function wordtwit_get_custom_post_types() {
	$settings = wordtwit_get_settings();
	$custom_types = array();	

	if ( strlen( $settings->custom_post_types ) ) {
		$custom_post_types = explode( "," , $settings->custom_post_types );	
		if ( $custom_post_types ) {
			foreach( $custom_post_types as $post_type ) {
				$custom_types[] = trim( $post_type );
			}	
		}	
	}
	
	return apply_filters( 'wordtwit_custom_post_types', $custom_types );	
}

function wordtwit_server_time_is_accurate() {
	if ( !class_exists( 'WP_Http' ) ) {
		include_once( ABSPATH . WPINC. '/class-http.php' );
	}	
	
	$http = new WP_Http;
	$response = $http->request( 'http://code.bravenewcode.com/time_offset/?unixtime=' . time() );
	if ( !is_wp_error( $response ) ) {
		if ( $response['response']['code'] == '200' ) {
			return ( abs( $response['body'] ) < 30 );
		}
	}	
	
	return false;
}


function wordtwit_is_multisite_enabled() {
	$settings = wordtwit_get_settings();
	if ( $settings->multisite_force_enable ) {
		return true;	
	} else {
		return ( defined( 'MULTISITE' ) && MULTISITE );
	}
}

function wordtwit_is_multisite_primary() {
	global $blog_id;
	return ( $blog_id == 1 );
}	

function wordtwit_is_multisite_secondary() {
	if ( wordtwit_is_multisite_enabled() ) {
		global $blog_id;
		
		return ( $blog_id > 1 );
	} else {
		return false;	
	}
}

function wordtwit_load_extensions() {
	if ( defined( 'WPTOUCH_PRO_INSTALLED' ) ) {
		require_once( WORDTWIT_EXTENSION_DIR . '/wptouch-pro.php' );
	}
}

function wordtwit_has_defined_custom_twitter_app() {
	$settings = wordtwit_get_settings(); 
	return ( isset( $settings->custom_consumer_key ) && ( strlen( $settings->custom_consumer_key ) > 0 ) );
}
