<?php

if ( !class_exists( 'WP_Http' ) ) {
	include_once( ABSPATH . WPINC. '/class-http.php' );
}

require_once( 'debug.php' );
require_once( WORDTWIT_DIR . '/include/xml.php' );

define( 'WORDTWIT_OAUTH2_GPLUS_CLIENT_ID', '913050534519.apps.googleusercontent.com' );
define( 'WORDTWIT_OAUTH2_GPLUS_CLIENT_SECRET', 'upNsbSJ0l30hn3Q6ObmXPt3w' );
define( 'WORDTWIT_OAUTH2_GPLUS_SCOPE', 'https://www.googleapis.com/auth/plus.me' );

class WordTwitOAuth2 {
	function WordTwitOAuth2() {
		
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
	
	function get_auth_url() {
		return "https://accounts.google.com/o/oauth2/auth?scope=" . WORDTWIT_OAUTH2_GPLUS_SCOPE . "&client_id=" . WORDTWIT_OAUTH2_GPLUS_CLIENT_ID . "&redirect_uri=" . get_home_url( '/' ) . "&response_type=code";	
	}
}