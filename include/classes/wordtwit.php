<?php

class WordTwitPro {
	var $tabs;
	var $settings;
	
	var $post;
	var $get;
	
	var $oauth;
	
	var $transient_set;
	
	function WordTwitPro() {
		$this->tabs = array();
		$this->settings = array();

		// Fixed up get and post variables
		$this->get  = array();			
		$this->post = array();	
		
		$this->transient_set = false;
		$this->locale = false;
	}
	
	function show_post_box() {
		if ( !wordtwit_has_defined_custom_twitter_app() ) {
			include( WORDTWIT_DIR . '/include/html/post-box-no-custom-app.php' ); 
		} else {
			$accounts = wordtwit_get_accounts();
			
			if ( count( $accounts ) ) {
				include( WORDTWIT_DIR . '/include/html/post-box.php' );
			} else { 
				include( WORDTWIT_DIR . '/include/html/post-box-no-accounts.php' ); 	
			}
		}
	}

	function construct_admin_menu() {
		if ( function_exists( 'add_meta_box' ) ) {
			$post_types = array_merge( array( 'post' ), wordtwit_get_custom_post_types() );
			foreach( $post_types as $post_type ) {
				add_meta_box( 'wordtwit-box', __( 'WordTwit', 'wordtwit-pro' ), array( &$this, 'show_post_box' ), $post_type, 'side' );	
			}
		}
	}
	
	function redirect_to_options_page() {
		header( 'Location: ' . admin_url( 'admin.php?page=wordtwit-pro/admin/admin-panel.php' ) );
		die;
	}
	
	function redirect_to_account_page() {
		header( 'Location: ' . admin_url( 'admin.php?page=wordtwit_account_configuration' ) );
		die;		
	}
	
	function initialize() {
		add_action( 'init', array( &$this, 'cleanup_post_and_get' ) );
		add_action( 'init', array( &$this, 'check_directories' ) );
		add_action( 'init', array( &$this, 'process_submitted_settings' ) );
		add_action( 'init', array( &$this, 'setup_custom_taxonomies' ) );
		add_action( 'init', array( &$this, 'check_for_wordtwit_urls' ) );
		
		add_action( 'admin_head', array( &$this, 'wordtwit_admin_head' ) );
		add_action( 'admin_init', array( &$this, 'wordtwit_admin_init' ) );
		add_action( 'admin_init', array( &$this, 'check_for_new_account' ), 5 );
		add_action( 'admin_init', array( &$this, 'check_for_account_actions' ) );
		add_action( 'admin_init', array( &$this, 'check_for_tweet_log_actions' ) );
		add_action( 'admin_init', array( &$this, 'check_for_post_box_actions' ) );
		add_action( 'admin_menu', array( &$this, 'construct_admin_menu' ) );
		add_action( 'admin_footer', array( &$this, 'wordtwit_admin_footer' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'wordtwit_admin_js' ) );
//		add_action( 'install_plugins_pre_plugin-information', array( &$this, 'show_plugin_info' ) );
		
		add_action( 'publish_post', array( &$this, 'post_now_published' ) );
		add_action( 'publish_tweet', array( &$this, 'publish_tweet' ) );
		
		add_action( 'wordtwit_settings_loaded', array( &$this, 'post_settings_setup' ) );		
		add_action( 'wordtwit_settings_loaded', array( &$this, 'setup_languages' ) );		
		
		add_action( 'wp_ajax_wordtwit_ajax', array( &$this, 'admin_ajax_handler' ) );	
		
		$this->oauth = new WordTwitOAuth();			
	}
	
/*    function show_plugin_info() {
		switch( $_REQUEST[ 'plugin' ] ) {
			case 'wordtwit-pro':
				$this->setup_bnc_api();
				
				echo "<h2 style='font-family: Georgia, sans-serif; font-style: italic; font-weight: normal'>" . __( "WordTwit Changelog", "wordtwit-pro" ) . "</h2>";
				exit;
				break;
			default:
				break;
		}
    }	*/
	
	function check_for_wordtwit_urls() {
		global $wpdb;
		
		$settings = wordtwit_get_settings();
		if ( $settings->resolve_wordtwit_custom ) {
			$location = explode( '/', ltrim( $_SERVER['REQUEST_URI'], '/' ) );
			if ( count( $location ) ) {
				$possible_url = $location[ count( $location ) - 1 ];
			
				if ( $possible_url && strlen( $possible_url ) < 6 ) {		
					$tiny_url = $possible_url;
					
					$result = $wpdb->get_row( $wpdb->prepare( "SELECT original FROM " . $wpdb->prefix . "tweet_urls WHERE url = %s",  $tiny_url ) );
					if ( $result ) {
						header( "HTTP/1.1 301 Moved Permanently" ); 
						header( "Location: " . $result->original ); 
						die;				
					}
				}
			}	
		}			
	}
	
	function get_site_accounts() {
		return get_site_option( WORDTWIT_MULTISITE_SETTING_NAME );	
	}
	
	function save_site_accounts( $accounts ) {
		update_site_option( WORDTWIT_MULTISITE_SETTING_NAME, $accounts );
	}
	
	function post_settings_setup() {
		// setup custom post types
		$custom_post_types = wordtwit_get_custom_post_types();
		
		if ( count( $custom_post_types ) ) {
			foreach( $custom_post_types as $post_type ) {
				$cleaned_up_post_type = trim( $post_type );
				add_action( 'publish_' . $cleaned_up_post_type, array( &$this, 'post_now_published' ) );	
			}	
		}
				
		$settings = wordtwit_get_settings();
		// adjust oauth time if required
		if ( $settings->oauth_time_offset != 0 ) {
			$this->oauth->set_oauth_time_offset( $settings->oauth_time_offset );
		}		
		
		if ( strlen( $settings->custom_consumer_key ) && strlen( $settings->custom_consumer_secret ) ) {
			$this->oauth->set_custom_key_and_secret( $settings->custom_consumer_key, $settings->custom_consumer_secret );	
		}
		
	}
	
	function dequote_string( $str ) {
		return str_replace( '&#8220;', '"',  str_replace( '&#8221;', '"', $str ) );	
	}
	
	function get_previous_tweets_for_post( $post_id ) {
		$posted_tweets = get_post_meta( $post_id, 'wordtwit_posted_tweets', true );
		if ( !$posted_tweets || !is_array( $posted_tweets ) ) {
			$posted_tweets = array();
		}	
		
		return $posted_tweets;
	}
	
	function get_saved_tweet_result( $tweet_id ) {
		$tweet_result = get_post_meta( $tweet_id, 'wordtwit_result', true );
		return $tweet_result;
	}
	
	function save_previous_tweets_for_post( $post_id, $tweets ) {
		delete_post_meta( $post_id, 'wordtwit_posted_tweets' );
		add_post_meta( $post_id, 'wordtwit_posted_tweets', $tweets, true );	
	}
	
	function clean_up_previous_tweets( $post_id, $account_info ) {
		$old_tweets = $this->get_previous_tweets_for_post( $post_id );
		if ( $old_tweets && is_array( $old_tweets ) && count( $old_tweets ) ) {
			$deleted_tweets = array();
			
			foreach( $old_tweets as $tweet_id ) {
				$tweeted_account = get_post_meta( $tweet_id, 'wordtwit_account', true );
				if ( $tweeted_account && $tweeted_account == $account_info->screen_name ) {
					$tweet_result = $this->get_saved_tweet_result( $tweet_id );
					if ( $tweet_result->success ) {
						if ( $this->oauth->delete_status( $account_info->token, $account_info->secret, $tweet_result->created_tweet_id ) ) {
							$deleted_tweets[] = $tweet_id;
						} 
					}
				}
			}
			
			// Save all the Tweets that haven't yet been deleted
			$updated_old_tweets = array_diff( $old_tweets, $deleted_tweets );
			$this->save_previous_tweets_for_post( $post_id, $updated_old_tweets );
		}
	}
	
	function publish_tweet( $tweet_id ) {
		// used to publish tweets
		$post = get_post( $tweet_id );
		if ( $post ) {
			$associated_post_id = get_post_meta( $tweet_id, 'wordtwit_real_post', true );
			if ( $associated_post_id ) {
				require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );

				$account = get_post_meta( $tweet_id, 'wordtwit_account', true );
				if ( $account ) {	
					$settings = wordtwit_get_settings();

					$account_info = wordtwit_get_account_info( $account );
					if ( $account_info ) {
						$modified_title = $this->dequote_string( html_entity_decode( $post->post_title ) );
						$created_tweet_id = $this->oauth->update_status( $account_info->token, $account_info->secret, $modified_title );
						
						$tweet_result = new stdClass;
						$tweet_result->code = $this->oauth->get_response_code();
						$tweet_result->error = $this->oauth->get_error_message();
						$tweet_result->success = ( $tweet_result->code == 200 );
						
						// Save the created tweet ID
						if ( is_numeric( $created_tweet_id ) ) {
							$tweet_result->created_tweet_id = $created_tweet_id;
						}
						
						update_post_meta( $tweet_id, 'wordtwit_result', $tweet_result );
						
						// Check for a valid result code
						if ( $tweet_result->code != 200 ) {
							// update the post status to ERROR
							global $wpdb;
							$wpdb->query( $wpdb->prepare( "UPDATE " . $wpdb->posts . " SET post_content = 'error' WHERE ID = %d", $tweet_id ) );
						} else {
							// Check to see if we should clean up old tweets on this account
							if ( $settings->remove_old_tweets ) {
								$this->clean_up_previous_tweets( $associated_post_id, $account_info );
							}
							
							// Successful Tweet
							$posted_tweets = $this->get_previous_tweets_for_post( $associated_post_id );							
							$posted_tweets[] = $tweet_id;
							
							$this->save_previous_tweets_for_post( $associated_post_id, $posted_tweets );
						}
					}
				}					
			}
		}
	}
	
	function add_tweet_log_post( $post_id, $account, $offset = 0, $tweet_num = 1 ) {
		require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
		
		$tweet_info = wordtwit_get_saved_tweet_info( $post_id );
		$settings = wordtwit_get_settings();
		
		$account_info = wordtwit_get_account_info( $account );
		if ( $post_id && $tweet_info && $account && $account_info ) {		
			$tweet_text = wordtwit_get_post_tweet( $post_id, $tweet_num );
			
			$post = get_post( $post_id );
			
			$new_post = array(
				'post_title' => $tweet_text,
				'post_content' => 'ok',
				'post_author' => $post->post_author,
				'post_type' => 'tweet'
			);

			$should_publish = false;
			
			if ( $offset ) {
				$new_post[ 'post_status' ] = 'future';	
				$new_post[ 'post_date' ] = date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) + $offset );
			} else {
				$new_post[ 'post_status' ] = 'draft';	
				$should_publish = true;
			}				
			
			$tweet_id = wp_insert_post( $new_post );
			if ( $tweet_id ) {
				add_post_meta( $tweet_id, 'wordtwit_real_post', $post_id );
				add_post_meta( $tweet_id, 'wordtwit_account', $account );
				
				$tweet_info->tweet_log_ids[] = $tweet_id;
				
				// We have to delay publishing the post, otherwise the meta values happen after the post is published
				// which results in the tweet not going out
				if ( $should_publish ) {
					wp_publish_post( $tweet_id );
				}
				
				wordtwit_save_tweet_info( $tweet_info, $post_id );	
			}
		}	
	} 
	
	function post_now_published( $post_id, $force_it = false ) {
		$settings = wordtwit_get_settings();
				
		require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
		
		if ( $settings->allow_third_party && !$force_it ) {
			$has_tweet_info = get_post_meta( $post_id, 'wordtwit_post_info', true );
			if ( !$has_tweet_info ) {
				$force_it = true;
				
				wordtwit_save_tweet_info( wordtwit_get_saved_tweet_info( $post_id ), $post_id );
			}	
		}	
		
		$tweet_info = wordtwit_get_saved_tweet_info( $post_id );
		if ( $tweet_info ) {
			if ( !$tweet_info->enabled ) {
				// Tweet is disabled, so don't tweet anything	
				$tweet_info->status = WORDTWIT_TWEET_IS_DEFERRED;	
				$tweet_info->enabled = true;
				$tweet_info->tweet_times = 1;
				
				wordtwit_save_tweet_info( $tweet_info, $post_id );
			
				return;
			}
			
			if ( $tweet_info->status == WORDTWIT_TWEET_PUBLISHED && !$force_it && !$tweet_info->allow_third_party ) {
				// Don't tweet posts that have already been tweeted
				return;	
			}
			
			if ( $tweet_info->status == WORDTWIT_TWEET_IS_OLD && !$force_it ) {
				// Don't allow publishing posts that are old
				return;
			}
			
			if ( $tweet_info->status == WORDTWIT_TWEET_IS_DEFERRED && !$force_it ) {	
				// Don't allow publishing posts that were disabled originally, not without forcing
				return;				
			}	
			
			$tweet_counter = $tweet_info->tweet_counter;
			
			for( $i = 1; $i <= $tweet_info->tweet_times; $i++ ) {
				$post_offset = ( $tweet_info->delay + $tweet_info->separation*( $i - 1 ) ) * 60;
				
				$individual_offset = 0;
				
				foreach( $tweet_info->accounts as $account ) {
					$this->add_tweet_log_post( $post_id, $account, $post_offset + $individual_offset, $tweet_counter );
					
					$individual_offset = $individual_offset + ( 60 * $settings->stagger_tweet_time );
				}
				
				$tweet_counter = $tweet_counter + 1;
			}
	
			// change status to published, reload tweet_info since it has possibly been saved in other functions
			$tweet_info = wordtwit_get_saved_tweet_info( $post_id );
			$tweet_info->status = WORDTWIT_TWEET_PUBLISHED;	
			$tweet_info->tweet_counter = $tweet_counter;	
			
			// reset tweet times to 1 so that a retweet naturally sends out only one tweet	
			$tweet_info->tweet_times = 1;
			
			wordtwit_save_tweet_info( $tweet_info, $post_id );
		}
	}
	
	function admin_ajax_handler() {
		$this->cleanup_post_and_get();
		
		if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_wordtwit' ) || current_user_can( 'edit_posts' ) ) {
			// Check security nonce
			$wordtwit_nonce = $this->post['wordtwit_nonce'];
			
			if ( !wp_verify_nonce( $wordtwit_nonce, 'wordtwit_admin' ) ) {	
				_e( 'Security check failed', 'wordtwit-pro' );
				exit;	
			}

			$wordtwit_ajax_action = $this->post['wordtwit_action'];
			switch( $wordtwit_ajax_action ) {
				case 'twitter-add':
					wordtwit_the_twitter_authorize_url();
					break;
				case 'wordtwit-news':
					include( WORDTWIT_DIR . '/admin/ajax/news.php' );
					break;
				case 'dashboard-ajax':
					include( WORDTWIT_DIR . '/admin/ajax/dashboard-info.php' );
					break;
				case 'save-post-data':
					$this->cleanup_post_and_get();
				
					require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
					$tweet_info = wordtwit_get_saved_tweet_info( $this->post['post'] );
					if ( $this->post['manual'] == 1 ) {
						$tweet_info->manual = true;	
						$tweet_info->text = $this->post['tweet_text'];
					} else {
						$tweet_info->manual = false;
						$tweet_info->tweet_template = false;
					}
					
					$tweet_info->tweet_times = $this->post['tweet_times'];
					
					if ( $tweet_info->tweet_times > 1 ) {
						$tweet_info->separation = $this->post['tweet_sep_min'];
					} else {
						$tweet_info->separation = 60;	
					}
					
					$tweet_info->delay = $this->post['tweet_delay'];
					$tweet_info->enabled = $this->post['enabled'];
					
					$tweet_info->accounts = array();
					$tweet_info->hash_tags = array();
					foreach( $this->post as $key => $value ) {
						if ( preg_match( '#account_(.*)#i', $key, $matches ) ) {
							$tweet_info->accounts[] = $matches[1];
						}	
						
						if ( preg_match( '#hash_(.*)#i', $key, $matches ) ) {
							$tweet_info->hash_tags[] = $this->post[ $matches[0] ];
						}	
					}				
										
					update_post_meta( $this->post['post'], 'wordtwit_post_info', $tweet_info );
					echo wordtwit_get_post_tweet( $this->post['post'] );
					break;
				case 'update-post-data':
					require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
					echo wordtwit_get_post_tweet( $this->post['post'] );
					break;
				case 'estimate-offset':	
					if ( !class_exists( 'WP_Http' ) ) {
						include_once( ABSPATH . WPINC. '/class-http.php' );
					}
					
					$http = new WP_Http;
					$url = 'http://code.bravenewcode.com/time_offset/?unixtime=' . time();
					$response = $http->request( $url );
					if ( !is_wp_error( $response ) ) {
						if ( $response['response']['code'] == '200' ) {
							echo $response['body'];
						}
					}
					break;
				case 'get-tweet-template':
					require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
					$template = wordtwit_expand_tweet( wordtwit_get_post_tweet_template( $this->post['post'] ), $this->post['post'], 1, true );
					echo $template;					
					break;
				case 'update-tweet-template':
					require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
					if ( wordtwit_get_post_tweet_template( $this->post['post'] ) != $this->post['tweet_template'] ) {
						$tweet_info = wordtwit_get_saved_tweet_info( $this->post['post'] );
						if ( $tweet_info ) {
							$tweet_info->manual = true;	
							$tweet_info->tweet_template = $this->post['tweet_template'];
							
							wordtwit_save_tweet_info( $tweet_info, $this->post['post'] );
						} 
					} 
					
					echo wordtwit_get_post_tweet( $this->post['post'] );
					break;
				case 'are-hash-tags-enabled':
					require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );	
					$template = wordtwit_get_post_tweet_template( $this->post['post'] );
					if ( $template && strpos( $template, '[hashtags]' ) !== false ) {
						echo 'yes';
					} else {
						echo 'no';
					}
					break;
				case 'update-account-order':
					if ( isset( $this->post[ 'account_order' ] ) ) {
						$account_order = explode( ",", $this->post[ 'account_order' ] );
						if ( $account_order ) {
							global $user_ID;
							get_currentuserinfo();
							
							delete_user_meta( $user_ID, 'wordtwit_account_order' );
							add_user_meta( $user_ID, 'wordtwit_account_order', $account_order );
						}
					}
					break;
				default:
					break;
			}	
		}	
		die;		
	}
	
	
	function get_latest_news( $quantity = 9 ) {
		if ( !function_exists( 'fetch_feed' ) ) {
			include_once( ABSPATH . WPINC . '/feed.php' );
		}
		
		$rss = fetch_feed( 'http://www.bravenewcode.com/blog/feed/' );
		if ( !is_wp_error( $rss ) ) {
			$max_items = $rss->get_item_quantity( $quantity ); 
			$rss_items = $rss->get_items( 0, $max_items ); 
			
			return $rss_items;	
		} else {		
			return false;
		}
	}	
	
	function setup_custom_taxonomies() {
		register_post_type(
			'tweet',
			array(
				'label' => __( 'Tweet Log', 'wordtwit-pro' ),
				'name' => 'tweet',
				'public' => false
			)
		);	
	}
	
	
	function cleanup_post_and_get() {
		if ( count( $_GET ) ) {
			foreach( $_GET as $key => $value ) {
				if ( get_magic_quotes_gpc() ) {
					$this->get[ $key ] = @stripslashes( $value );	
				} else {
					$this->get[ $key ] = $value;
				}
			}	
		}	
		
		if ( count( $_POST ) ) {
			foreach( $_POST as $key => $value ) {
				if ( get_magic_quotes_gpc() ) {
					$this->post[ $key ] = @stripslashes( $value );	
				} else {
					$this->post[ $key ] = $value;	
				}
			}	
		}	
	}
		
	function check_for_post_box_actions() {
		if ( $this->is_admin_section() && isset( $this->get['wordtwit_post_action'] ) ) {
			if ( $this->verify_get_nonce() ) {
				switch( $this->get['wordtwit_post_action'] ) {
					case 'retweet':
						require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
						$this->post_now_published( $this->get['post'], true );
						break;	
					case 'publish_now':
						require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
						$this->post_now_published( $this->get['post'], true );
						break;
				}
			}
			
			header( 'Location: ' . add_query_arg( array( 'wordtwit_post_action' => NULL, 'wordtwit_nonce' => NULL ) ) );
			die;
		}		
	}
	
	function check_for_tweet_log_actions() {
		if ( $this->is_admin_section() && isset( $this->get['wordtwit_tweet_action'] ) ) {
			if ( $this->verify_get_nonce() ) {
				$settings = $this->get_settings();
				
				$wordtwit_account_action = $this->get['wordtwit_tweet_action'];
				
				switch( $wordtwit_account_action ) {
					case 'delete':
						$can_delete = false;
						if ( wordtwit_user_is_admin() ) {
							$can_delete = true;
						} else {
							global $user_ID;
							get_currentuserinfo();	
							
							$post_info = get_post( $this->get[ 'log_id' ] );
							if ( $post_info->post_author == $user_ID ) {
								$can_delete = true;	
							}
						}
						
						if ( $can_delete ) {
							wp_delete_post( $this->get[ 'log_id'], true );
						}
						break;
					case 'tweet_now':
						global $wpdb;
						$wpdb->update( 
							$wpdb->posts, 
							array( 
								'post_date_gmt' => time() - 1, 
								'post_date' => date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) ) 
							), 
							array( 'ID' => $this->get[ 'log_id' ] ) 
						);
								
						check_and_publish_future_post( $this->get[ 'log_id' ] );
						break;	
					case 'retweet':
						$associated_post_id = get_post_meta( $this->get[ 'log_id' ], 'wordtwit_real_post', true );
						if ( $associated_post_id ) {
							require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
							
							$tweet_info = wordtwit_get_saved_tweet_info( $associated_post_id );
							$tweet_counter = $tweet_info->tweet_counter;
							
							$this->add_tweet_log_post( $associated_post_id, $this->get['wordtwit_account' ], 0, $tweet_counter );
							
							$tweet_info = wordtwit_get_saved_tweet_info( $associated_post_id );
							$tweet_info->tweet_counter = $tweet_counter + 1;
							
							wordtwit_save_tweet_info( $tweet_info, $associated_post_id );
						}
						break;
				}			
			}
					
			$location = add_query_arg( array( 'log_id' => NULL, 'wordtwit_nonce' => NULL, 'wordtwit_tweet_action' => NULL, 'wordtwit_account' => NULL ) );
			header( 'Location: ' . $location );
			die; 
		}		
	}
	
	function check_for_account_actions() {
		if ( $this->is_admin_section() && isset( $this->get['wordtwit_action'] ) ) {
			if ( $this->verify_get_nonce() ) {
				$settings = $this->get_settings();
				
				$wordtwit_account_action = $this->get['wordtwit_action'];
				switch( $wordtwit_account_action ) {
					case 'delete_account':
						$account = wordtwit_get_account_info( $this->get['wordtwit_user'] );
						
						if ( $account && wordtwit_current_user_can_delete_account( $account ) ) {			
							wordtwit_delete_account( $this->get['wordtwit_user'] );
						}
						break;
					case 'refresh_account':
						$account = wordtwit_get_account_info( $this->get['wordtwit_user'] );
						if ( wordtwit_current_user_can_delete_account( $account ) ) {
							if ( $account ) {
								$user_id = $account->user_id;
								$updated_user_info = $this->oauth->get_user_info( $user_id, $account->token, $account->secret );	
								if( $updated_user_info ) {
									$account = $this->update_twitter_info( $updated_user_info, $account	);
									
									wordtwit_update_account( $this->get['wordtwit_user'], $account );						
								}
							}
						}
						break;
					case 'change_account_type':
						$account = wordtwit_get_account_info( $this->get['wordtwit_user'] );
						if ( $account && wordtwit_current_user_can_modify_account( $account ) ) {
							switch( $this->get['wordtwit_account_type'] ) {
								case 'local':
									$account->is_global = false;
									$account->is_site_wide = false;
									
									global $current_user;
									get_currentuserinfo();
									
									$account->owner = $current_user->ID;	
									
									wordtwit_delete_account( $this->get['wordtwit_user'] );
									$settings->accounts[ $this->get['wordtwit_user'] ] = $account;
								
									$this->save_settings( $settings );								
									break;
								case 'global':
									$account->owner = 0;
									$account->is_global = true;	
									$account->is_site_wide = false;
									
									wordtwit_delete_account( $this->get['wordtwit_user'] );
									$settings->accounts[ $this->get['wordtwit_user'] ] = $account;
									$this->save_settings( $settings );										
									break;	
								case 'sitewide':
									$account->owner = 0;
									$account->is_global = false;	
									$account->is_site_wide = true;
									
									wordtwit_delete_account( $this->get['wordtwit_user'] );
									$site_accounts = $this->get_site_accounts();
									$site_accounts[ $this->get['wordtwit_user'] ] = $account;
									$this->save_site_accounts( $site_accounts );
																		
									break;
							}
											
							
						}				
						break;
				}
			}
			
			$this->redirect_to_account_page();
		}
	}
	
	function check_for_new_account() {
		if ( $this->is_admin_section() && isset( $_GET['wordtwit_pro_oauth'] ) ) {	
			$settings = $this->get_settings();
			
			if ( $settings->oauth_request_token && $settings->oauth_request_token_secret ) {
			
				$access_token = $this->oauth->get_access_token( 
					$settings->oauth_request_token, 
					$settings->oauth_request_token_secret, 
					$this->get['oauth_verifier']
				);
				
				if ( $access_token && !isset( $settings->accounts[ $access_token['screen_name'] ] ) ) {
					$account = new stdClass;
					$account->token = $access_token['oauth_token'];
					$account->secret = $access_token['oauth_token_secret'];
					$account->user_id = $access_token['user_id'];
					$account->screen_name = $access_token['screen_name'];
					$account->is_default = true;
					$account->account_type = 'twitter';
					
					if ( wordtwit_user_can_make_global() ) {
						$account->owner = 0;	
					} else {
						global $current_user;
						get_currentuserinfo();
						
						$account->owner = $current_user->ID;
					}
					
					if ( wordtwit_user_can_make_global() ) {
						$account->is_global = true;
					} else {
						$account->is_global = false;
					}
					
					$account->is_site_wide = false;
					
					//$user_info = $this->oauth->get_user_info( $account->user_id );
					$user_info = $this->oauth->get_authenticated_user_information( $account->token, $account->secret );
					if ( $user_info ) {
						$account = $this->update_twitter_info( $user_info, $account );
											
						$settings->accounts[ $account->screen_name ] = $account;	
					}
					
					ksort( $settings->accounts );
					
					$this->save_settings( $settings );
				}
			}
						
			$this->redirect_to_account_page();
		}
	}
	
	function update_twitter_info( $user_info, $account ) {
		$account->id = $user_info->id;
		$account->profile_image_url = $user_info->profile_image_url;
		$account->location = $user_info->location;
		$account->utc_offset = $user_info->utc_offset;
		$account->description = $user_info->description;
		$account->followers_count = $user_info->followers_count;
		$account->name = $user_info->name;
		$account->url = $user_info->url;
		$account->statuses_count = $user_info->statuses_count;
		
		return $account;
	}
	
	function setup_languages() {		
		// Check for language override
		$settings = $this->get_settings();
		
		if ( $settings->force_locale == 'english' ) {
			// english can be forced when WordPress is set to use another language
			// useful for our own internal support
			return;	
		}
		
		$current_locale = get_locale();		
		
		if ( $settings->force_locale != 'auto' ) {
			$current_locale = $settings->force_locale;
		}
		
		if ( !empty( $current_locale ) ) {
			$current_locale = apply_filters( 'wordtwit_language', $current_locale );
			
			$use_lang_file = false;
			$custom_lang_file = WORDTWIT_CUSTOM_LANG_DIRECTORY . '/' . $current_locale . '.mo';
			
			if ( file_exists( $custom_lang_file ) && is_readable( $custom_lang_file ) ) {
				$use_lang_file = $custom_lang_file;
			} else {
				$lang_file = WORDTWIT_DIR . '/lang/' . $current_locale . '.mo';
				if ( file_exists( $lang_file ) && is_readable( $lang_file ) ) {
					$use_lang_file = $lang_file;
				}
			}
					
			if ( $use_lang_file ) {
				load_textdomain( 'wordtwit-pro', $use_lang_file );	
			}
			
			$this->locale = $current_locale;
			
			do_action( 'wordtwit_language_loaded', $this->locale );
		}
	}	
	
	function create_directory_if_not_exist( $dir ) {
		if ( !file_exists( $dir ) ) {
			// Try and make the directory
			if ( !wp_mkdir_p( $dir ) ) {
				$this->directory_creation_failure = true;
			}	
		}	
	}		
	
	function check_directories() {
		$this->create_directory_if_not_exist( WORDTWIT_CUSTOM_DIRECTORY );
		$this->create_directory_if_not_exist( WORDTWIT_CUSTOM_LANG_DIRECTORY );
	}			
	
	function is_admin_section() {
		return ( 
			is_admin() && 
			( 
				strpos( $_SERVER['REQUEST_URI'], 'wordtwit-pro' ) !== false || 
				strpos( $_SERVER['REQUEST_URI'], 'wordtwit_settings' ) !== false || 
				strpos( $_SERVER['REQUEST_URI'], 'wordtwit_account_configuration' ) !== false || 
				( strpos( $_SERVER['REQUEST_URI'], 'post.php' ) !== false && isset( $_GET['wordtwit_post_action'] ) ) || 
				( isset( $_GET['page'] ) && $_GET['page'] == 'tweet_queue' )
			) 
		);
	}

	function is_post_page() {
		global $post;

		if ( !$post ) {
			return false;
		}

		$allowable_post_types = array_merge( array( 'post' ), wordtwit_get_custom_post_types() );
		if ( !@in_array( $post->post_type, $allowable_post_types ) ) {
			return false;	
		}
		
		return ( 
			is_admin() && 
			( strpos( $_SERVER['REQUEST_URI'], 'post-new.php' ) !== false || 
			  strpos( $_SERVER['REQUEST_URI'], 'post.php' ) !== false ) 
		);
	}

	function wordtwit_admin_head() {
//		$current_scheme = get_user_option( 'admin_color' );
		$version_string = md5( WORDTWIT_VERSION );
		$minfile = WORDTWIT_DIR . '/admin/css/wordtwit-admin.min.css';
		
		if ( $this->is_admin_section() ) {			
	
			if ( file_exists( $minfile ) ) {
				echo "<link rel='stylesheet' type='text/css' href='" . WORDTWIT_URL . "/admin/css/wordtwit-admin.min.css?ver=" . $version_string . "' />\n";
			} else {
				echo "<link rel='stylesheet' type='text/css' href='" . WORDTWIT_URL . "/admin/css/wordtwit-admin.css?ver=" . $version_string . "' />\n";			
			}

//			if ( $current_scheme === 'fresh' ) {
//				echo "<link rel='stylesheet' type='text/css' href='" . WORDTWIT_URL . "/admin/css/wordtwit-admin-" . $current_scheme . ".css?ver=" . $version_string . "' />\n";
//			}
				
//			if ( eregi( "MSIE", getenv( "HTTP_USER_AGENT" ) ) || eregi( "Internet Explorer", getenv( "HTTP_USER_AGENT" ) ) ) {
//				echo "<link rel='stylesheet' type='text/css' href='" . WORDTWIT_URL . "/admin/css/wordtwit-admin-ie.css?ver=" . $version_string . "' />\n";
//			}
		} else if ( $this->is_post_page() ) {
			echo "<link rel='stylesheet' type='text/css' href='" . WORDTWIT_URL . "/admin/css/wordtwit-post-widget.css?ver=" . $version_string . "' />\n";			
		}
	}
	
	function wordtwit_admin_js() {
		// admin_enqueue_scripts
		$version_string = md5( WORDTWIT_VERSION );		
		$minfile = WORDTWIT_DIR . '/admin/js/wordtwit-admin.min.js';
		$section_name = false;

		if ( $this->is_admin_section() ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'wordtwit-plugins', WORDTWIT_URL . '/admin/js/wordtwit-plugins-min.js', array( 'jquery' ), $version_string );
			if ( file_exists( $minfile ) ) {
				wp_enqueue_script( 'wordtwit-main', WORDTWIT_URL . '/admin/js/wordtwit-admin.min.js', array( 'wordtwit-plugins', 'jquery', 'jquery-ui-sortable' ), $version_string );
			} else {
				wp_enqueue_script( 'wordtwit-main', WORDTWIT_URL . '/admin/js/wordtwit-admin.js', array( 'wordtwit-plugins', 'jquery', 'jquery-ui-sortable' ), $version_string );
			}
			
			$section_name = 'wordtwit-main';
		} else if ( $this->is_post_page() ) {
			wp_enqueue_script( 'wordtwit-post-widget', WORDTWIT_URL . '/admin/js/wordtwit-post-widget.js', array( 'jquery' ), $version_string );
			$section_name = 'wordtwit-post-widget';
		}
		
		if ( $this->is_admin_section() || $this->is_post_page() ) {	
			$js_params = array(
				'admin_nonce' => wp_create_nonce( 'wordtwit_admin' ),
				'manual' => __( 'Manual', 'wordtwit-pro' ),
				'automatic' => __( 'Automatic', 'wordtwit-pro' ),
				'tweet_too_long' => __( 'Your tweet is too long. It must be 140 characters or less.', 'wordtwit-pro' ),
				'disabled' => __( 'Disabled for this post', 'wordtwit-pro' ),
				'unpublished' => __( 'Unpublished', 'wordtwit-pro' ),
				'reset_admin_settings' => __( 'Reset all WordTwit admin settings?', 'wordtwit-pro' ) . ' ' . __( 'This operation cannot be undone.', 'wordtwit-pro' ),
				'custom_key_warning' => __( 'Warning: Changing your consumer key or secret will require you to reauthorize all of your accounts.', 'wordtwit-pro' ),
				'retweet_warning' => __( 'Are you sure you would like to publish the tweet(s) for this post?', 'wordtwit-pro' )
			);
			
			if ( isset( $_GET['post'] ) ) {
				$js_params['post'] = $_GET['post'];	
			}
			
			$settings = wordtwit_get_settings();
			if ( isset( $settings->disable_retweet_warning ) && $settings->disable_retweet_warning ) {
				$js_params['retweet_warning_enable'] = '0';
			} else {
				$js_params['retweet_warning_enable'] = '1';	
			}
			
			wp_localize_script( 
				$section_name, 
				'WordTwitProCustom', 
				$js_params
			);				
		}		
	}
	
	function wordtwit_admin_init() {
		$is_wordtwit_page = ( strpos( $_SERVER['REQUEST_URI'], 'wordtwit-pro' ) !== false ) || ( strpos( $_SERVER['REQUEST_URI'], 'wordtwit_settings' ) !== false );;
		$is_plugins_page = ( strpos( $_SERVER['REQUEST_URI'], 'plugins.php' ) !== false );
	}
	
	function wordtwit_admin_footer() {
		global $post;
		
		if ( $this->is_admin_section() || $this->is_post_page() ) {
			echo "<script type='text/javascript'>\n";			
			if ( $post && isset( $post->ID ) && $post->post_type != 'tweet' ) {
				require_once( WORDTWIT_DIR . '/include/post-box-functions.php' );
				
				echo "var WordTwitPostID = '" . $post->ID . "';\n";
				echo "var WordTwitTweetStatus = " . wordtwit_get_tweet_status() . ";\n";
				echo "var WordTwitLoadJS = '1';\n";
			} else {
				echo "var WordTwitLoadJS = '0';\n";
			}
			echo "</script>\n";				
		}
	}
	
	function get_settings() {
		// check to see if we've already loaded the settings
		if ( $this->settings ) {
			return apply_filters( 'wordtwit_settings', $this->settings );	
		}

		//update_option( WORDTWIT_SETTING_NAME, false );
		$this->settings = get_option( WORDTWIT_SETTING_NAME, false );
		if ( !is_object( $this->settings ) ) {
			$this->settings = unserialize( $this->settings );	
		}

		if ( !$this->settings ) {
			// Return default settings
			$this->settings = new WordTwitSettings;
			$defaults = apply_filters( 'wordtwit_default_settings', new WordTwitDefaultSettings );

			foreach( (array)$defaults as $name => $value ) {
				$this->settings->$name = $value;	
			}

			return apply_filters( 'wordtwit_settings', $this->settings );	
		} else {	
			// first time pulling them from the database, so update new settings with defaults
			$defaults = apply_filters( 'wordtwit_default_settings', new WordTwitDefaultSettings );
			
			// Merge settings with defaults
			foreach( (array)$defaults as $name => $value ) {
				if ( !isset( $this->settings->$name ) ) {
					$this->settings->$name = $value;	
				}
			}

			return apply_filters( 'wordtwit_settings', $this->settings );	
		}			
	}
	
	function save_settings( $settings ) {
		$settings = apply_filters( 'wordtwit_update_settings', $settings );

		$serialized_data = serialize( $settings );
				
		update_option( WORDTWIT_SETTING_NAME, $serialized_data );	
		
		$this->settings = $settings;
	}	
		
	function process_submitted_settings() {
		if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
			do_action( 'wordtwit_settings_loaded' );
			return;	
		}

		if ( isset( $this->post[ 'tweet_log_delete_all' ] ) ) {
			if ( wp_verify_nonce( $this->post[ 'tweet_log_nonce' ], 'tweet_log' ) && current_user_can( 'delete_posts') ) {
				global $wpdb;

				$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'posts WHERE post_type = %s', 'tweet' ) );
			}
		} else if ( isset( $this->post[ 'tweet_log_submit' ] ) ) {
			if ( wp_verify_nonce( $this->post[ 'tweet_log_nonce' ], 'tweet_log' ) ) {
				if ( current_user_can( 'delete_posts') ) {
					if ( $this->post[ 'tweet_action' ] == 'trash' ) {
						foreach( $this->post as $key => $value ) {
							if ( preg_match( '#delete_tweet_(.*)#i', $key, $matches ) ) {
								$tweet_to_delete = $matches[1];

								$post_info = get_post( $tweet_to_delete );
								if ( $post_info && $post_info->post_type == 'tweet' ) {
									wp_delete_post( $tweet_to_delete );	
								}	
							}
						}						
					}
				}
			}
		}
		
		if ( isset( $this->post['wordtwit-submit'] ) ) {
			$this->verify_post_nonce();
			
			$settings = $this->get_settings();
			
			$old_consumer_key = $settings->custom_consumer_key;
			$old_consumer_secret = $settings->custom_consumer_secret;
			
			foreach( (array)$settings as $name => $value ) {
				if ( isset( $this->post[ $name ] ) ) {
					
					// Remove slashes if they exist
					if ( is_string( $this->post[ $name ] ) ) {						
						$this->post[ $name ] = htmlspecialchars_decode( $this->post[ $name ] );
					}	
					
					$settings->$name = apply_filters( 'wordtwit_setting_filter_' . $name, $this->post[ $name ] );	
				} else {
					// Remove checkboxes if they don't exist as data
					if ( isset( $this->post[ $name . '-hidden' ] ) ) {
						$settings->$name = false;
					}
					
					// check to see if the hidden fields exist
					if ( isset( $this->post[ $name . '_1' ] ) ) {
						// this is an array field
						$setting_array = array();
						
						$count = 1;							
						while ( true ) {
							if ( !isset( $this->post[ $name . '_' . $count ] ) ) {
								break;	
							}	
							
							// don't add empty strings
							if ( $this->post[ $name . '_' . $count ] ) {
								$setting_array[] = $this->post[ $name . '_' . $count ];
							}
							
							$count++;
						}
						
						$settings->$name = $setting_array;	
					}
				}
			}
			
	
			if ( ( $old_consumer_key != $settings->custom_consumer_key ) || ( $old_consumer_secret != $settings->custom_consumer_secret ) ) {
				$settings->accounts = array();
			}	

			$this->save_settings( $settings );
			
			do_action( 'wordtwit_settings_saved' );
			
		} else if ( isset( $this->post['wordtwit-submit-reset'] ) ) {
			$this->verify_post_nonce();
			
			// rove the setting from the DB
			update_option( WORDTWIT_SETTING_NAME, false );
			$this->settings = false;
		} 	
		
		do_action( 'wordtwit_settings_loaded' );	
	}	
	
	function verify_post_nonce() {	 
		$nonce = $this->post['wordtwit-admin-nonce'];
		if ( !wp_verify_nonce( $nonce, 'wordtwit-post-nonce' ) ) {
			die( __( 'Unable to verify WordTwit nonce', 'wordtwit-pro' ) );	
		}		
		
		return true;
	}	
	
	function verify_get_nonce() {
		$nonce = $this->get['wordtwit_nonce'];
		if ( !wp_verify_nonce( $nonce, 'wordtwit' ) ) {
			die( __( 'Unable to verify WordTwit nonce', 'wordtwit-pro' ) );	
		}		
		
		return true;			
	}
	
	function get_twitter_auth_url() {
		$token = $this->oauth->get_request_token();
		if ( $token ) {
			$settings = $this->get_settings();
			
			$settings->oauth_request_token = $token['oauth_token'];
			$settings->oauth_request_token_secret = $token['oauth_token_secret'];

			$this->save_settings( $settings );
			
			return $this->oauth->get_auth_url( $token['oauth_token'] );
		} else {
			return false;	
		}
	}		
	
}
