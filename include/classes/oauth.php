<?php

if ( !class_exists( 'WP_Http' ) ) {
	include_once( ABSPATH . WPINC. '/class-http.php' );
}

require_once( 'debug.php' );

define( 'WORDTWIT_OAUTH_CONSUMER_KEY', '2KwgBVycTRipJf4EO918Aw' );
define( 'WORDTWIT_OAUTH_REQUEST_URL', 'https://api.twitter.com/oauth/request_token' );
define( 'WORDTWIT_OAUTH_ACCESS_URL', 'https://api.twitter.com/oauth/access_token' );
define( 'WORDTWIT_OAUTH_AUTHORIZE_URL', 'https://api.twitter.com/oauth/authorize' );
define( 'WORDTWIT_OAUTH_REALM', 'https://twitter.com/' );

class WordTwitOAuth {
	var $duplicate_tweet;
	var $consumer_key;
	var $consumer_secret;
	var $can_use_curl;
	var $response_code;
	var $oauth_time_offset;
	var $error_message;

	function WordTwitOAuth() {
		$this->duplicate_tweet = false;
		$this->response_code = false;
		$this->error_message = false;

		$this->setup();

		$this->oauth_time_offset = 0;
	}

	function get_response_code() {
		return $this->response_code;
	}

	function get_error_message() {
		return $this->error_message;
	}

	function set_oauth_time_offset( $offset ) {
		$this->oauth_time_offset = $offset;
	}

	function set_custom_key_and_secret( $key, $secret ) {
		$this->consumer_key = $key;
		$this->consumer_secret = $secret;
	}

	function encode( $string ) {
   		return str_replace( '+', ' ', str_replace( '%7E', '~', rawurlencode( $string ) ) );
	}

	function create_signature_base_string( $get_method, $base_url, $params ) {
		if ( $get_method ) {
			$base_string = "GET&";
		} else {
			$base_string = "POST&";
		}

		$base_string .= $this->encode( $base_url ) . "&";

		// Sort the parameters
		ksort( $params );

		$encoded_params = array();
		foreach( $params as $key => $value ) {
			$encoded_params[] = $this->encode( $key ) . '=' . $this->encode( $value );
		}

		$base_string = $base_string . $this->encode( implode( $encoded_params, "&" ) );

		WORDTWIT_DEBUG( 'Signature base string is: ' . $base_string );

		return $base_string;
	}

	function params_to_query_string( $params ) {
		$query_string = array();
		foreach( $params as $key => $value ) {
			$query_string[ $key ] = $key . '=' . $value;
		}

		ksort( $query_string );

		return implode( '&', $query_string );
	}

	function do_get_request( $url ) {
		WORDTWIT_DEBUG( 'Doing GET request via do_get_request using WP API' );

		$request = new WP_Http;
		$result = $request->request( $url );

		// check for an error condition
		if ( !is_wp_error( $result ) ) {
			$this->response_code = $result[ 'response' ][ 'code' ];
			WORDTWIT_DEBUG( '..request returned status code of ' . $this->response_code );

			if ( $result['response']['code'] == '200' ) {
				return $result['body'];
			}
		} else {
			WORDTWIT_DEBUG( "..WP transport returned an error, " . $result->get_error_message() );
		}

		return false;
	}

	function do_request( $url, $oauth_header, $body_params = '', $use_get_request = false ) {
		WORDTWIT_DEBUG( 'Doing POST request, OAUTH header is ' . $oauth_header );

		$request = new WP_Http;

		WORDTWIT_DEBUG( '..using WP transport' );

		$params = array();
		if ( $use_get_request ) {
			$params['method'] = 'GET';

			$get_params = array();
			foreach( $body_params as $key => $value ) {
				$get_params[] = $key . '=' . urlencode( $value );
			}

			$url = $url . '?' . implode( '&', $get_params );
		} else {
			if ( $body_params ) {
				foreach( $body_params as $key => $value ) {
					$body_params[ $key ] = $value;
				}

				$params['body'] = $body_params;
			}

			$params['method'] = 'POST';
			$params['headers'] = array( 'Authorization' => $oauth_header );
		}

		$params['timeout'] = 10;

		$result = $request->request( $url, $params );

		if ( !is_wp_error( $result ) ) {
			WORDTWIT_DEBUG( '..WP transport returned a status code of ' . $result['response']['code'] );

			$this->response_code = $result['response']['code'];

			if ( $result['response']['code'] == '200' ) {
				WORDTWIT_DEBUG( '..RESPONSE was ' . print_r( $result[ 'body' ], true ) );

				return $result['body'];
			} else {
				WORDTWIT_DEBUG( '..RESPONSE was ' . print_r( $result[ 'response' ], true ) );

  				switch( $result['response']['code'] ) {
  					case 403:
  						$this->duplicate_tweet = true;
  						break;
  				}

				$error_message_found = preg_match( '#<error>(.*)</error>#i', $result[ 'body' ], $matches );
				if ( $error_message_found ) {
					$this->error_message = $matches[1];
				}
			}
		} else {
			WORDTWIT_DEBUG( "..WP transport returned an error, " . $result->get_error_message() );
		}

		return false;
	}

	function get_nonce() {
		return md5( mt_rand() + mt_rand() );
	}

	function parse_params( $string_params ) {
		$good_params = array();

		$params = explode( '&', $string_params );
		foreach( $params as $param ) {
			$keyvalue = explode( '=', $param );
			$good_params[ $keyvalue[0] ] = $keyvalue[1];
		}

		return $good_params;
	}

	function hmac_sha1( $key, $data ) {
		if ( function_exists( 'hash_hmac' ) ) {
			$hash = hash_hmac( 'sha1', $data, $key, true );

			return $hash;
		} else {
			$blocksize = 64;
			$hashfunc = 'sha1';
			if ( strlen( $key ) >$blocksize ) {
				$key = pack( 'H*', $hashfunc( $key ) );
			}

			$key = str_pad( $key, $blocksize, chr(0x00) );
			$ipad = str_repeat( chr( 0x36 ), $blocksize );
			$opad = str_repeat( chr( 0x5c ), $blocksize );
			$hash = pack( 'H*', $hashfunc( ( $key^$opad ).pack( 'H*',$hashfunc( ($key^$ipad).$data ) ) ) );

			return $hash;
		}
	}

	function do_oauth( $url, $params, $token_secret = '', $use_get_request = false ) {
		$sig_string = $this->create_signature_base_string( $use_get_request, $url, $params );

		$hash = $this->hmac_sha1( $this->consumer_secret . '&' . $token_secret, $sig_string );
		$sig = base64_encode( $hash );

		if ( !$use_get_request ) {
			$params['oauth_signature'] = $sig;

			$header = "OAuth ";
			$all_params = array();
			$other_params = array();
			foreach( $params as $key => $value ) {
				if ( strpos( $key, 'oauth_' ) !== false ) {
					$all_params[] = $key . '="' . $this->encode( $value ) . '"';
				} else {
					$other_params[ $key ] = $value;
				}
			}

			$header .= implode( $all_params, ", " );

			return $this->do_request( $url, $header, $other_params, $use_get_request );
		} else {
			$params['oauth_signature'] = $sig;

			return $this->do_request( $url, '', $params, $use_get_request );
		}
	}

	function get_oauth_params() {
		$params = array();

		$params['oauth_consumer_key'] = $this->consumer_key;
		$params['oauth_signature_method'] = 'HMAC-SHA1';
		$params['oauth_timestamp'] = time() + $this->oauth_time_offset;
		$params['oauth_nonce'] = $this->get_nonce();
		$params['oauth_version'] = '1.0';

		return $params;
	}

	function get_request_token() {
		$params = $this->get_oauth_params();

		WORDTWIT_DEBUG( 'In function get_request_token' );

		$params['oauth_callback'] = add_query_arg( 'wordtwit_pro_oauth', 1, admin_url( 'admin.php?page=wordtwit_account_configuration' ) );

		if ( wordtwit_is_debug_enabled() ) {
			WORDTWIT_DEBUG( '..params are ' . print_r( $params, true ) );
		}

		$result = $this->do_oauth( WORDTWIT_OAUTH_REQUEST_URL, $params );
		if ( $result ) {
			$new_params = $this->parse_params( $result );
			return $new_params;
		}
	}

	function get_access_token( $token, $token_secret, $verifier ) {
		$params = $this->get_oauth_params();

		WORDTWIT_DEBUG( 'In function get_access_token' );

		$params['oauth_token'] = $token;
		$params['oauth_verifier'] = $verifier;

		if ( wordtwit_is_debug_enabled() ) {
			WORDTWIT_DEBUG( '..params are ' . print_r( $params, true ) );
		}

		$result = $this->do_oauth( WORDTWIT_OAUTH_ACCESS_URL, $params, $token_secret );
		if ( $result ) {
			$new_params = $this->parse_params( $result );
			return $new_params;
		}
	}

	function update_status( $token, $token_secret, $status ) {
		$params = $this->get_oauth_params();

		WORDTWIT_DEBUG( 'In function update_status' );

		$params['oauth_token'] = $token;
		$params['status'] = $status;

		if ( wordtwit_is_debug_enabled() ) {
			WORDTWIT_DEBUG( '..params are ' . print_r( $params, true ) );
		}

		$url = 'https://api.twitter.com/1.1/statuses/update.json';

		$result = $this->do_oauth( $url, $params, $token_secret );
		if ( $result ) {
			$new_params = json_decode( $result );

			if ( is_object( $new_params ) && isset( $new_params->id ) ) {
				// return the tweet ID
				return $new_params->id;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	function delete_status( $token, $token_secret, $status_id ) {
		$params = $this->get_oauth_params();

		WORDTWIT_DEBUG( 'In function update_status' );

		$params['oauth_token'] = $token;

		if ( wordtwit_is_debug_enabled() ) {
			WORDTWIT_DEBUG( '..params are ' . print_r( $params, true ) );
		}

		$url = 'https://api.twitter.com/1.1/statuses/destroy/' . $status_id . '.json';

		$result = $this->do_oauth( $url, $params, $token_secret );
		if ( $result ) {
			return true;
		} else {
			return false;
		}
	}

	function get_authenticated_user_information( $token, $token_secret ) {
		$params = $this->get_oauth_params();

		WORDTWIT_DEBUG( 'In function get_authenticated_user_information' );

		$params['oauth_token'] = $token;

		$url = 'https://api.twitter.com/1.1/account/verify_credentials.json';

		$result = $this->do_oauth( $url, $params, $token_secret, true );
		if ( $result ) {
			$new_params = json_decode( $result );
			return $new_params;
		} else {
			return false;
		}
	}

	function get_user_public_timeline( $username, $token, $token_secret ) {
		$params = $this->get_oauth_params();

		WORDTWIT_DEBUG( 'In function get_user_timeline' );

		$params['oauth_token'] = $token;
		$params['screen_name'] = $username;
		$params['count'] = 10;
		$params['include_rts'] = 0;

		$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';

		$result = $this->do_oauth( $url, $params, $token_secret, true );
		if ( $result ) {
			$new_params = json_decode( $result );
			return $new_params;
		} else {
			return false;
		}
	}

	function was_duplicate_tweet() {
		return $this->duplicate_tweet;
	}

	function get_auth_url( $token ) {
		return WORDTWIT_OAUTH_AUTHORIZE_URL . '?oauth_token=' . $token;
	}

	function get_user_info( $user_id, $token, $token_secret ) {
		$params = $this->get_oauth_params();

		WORDTWIT_DEBUG( 'In function get_user_info' );
		WORDTWIT_DEBUG( 'Refreshing... ' . $user_id . ' / ' . $token . ' / ' . $token_secret );

		$params['oauth_token'] = $token;
		$params['user_id'] = $user_id;

		$url = 'https://api.twitter.com/1.1/users/show.json';

		$result = $this->do_oauth( $url, $params, $token_secret, true );
		if ( $result ) {
			$new_params = json_decode( $result );
			return $new_params;
		}
	}

	function setup() {
		// Default consumer key and secret
		$this->consumer_key = false;
		$this->consumer_secret = false;
	}
}