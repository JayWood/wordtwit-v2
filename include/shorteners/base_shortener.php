<?php

if ( !class_exists( 'WP_Http' ) ) {
    include_once( ABSPATH . WPINC. '/class-http.php' );
}

define( 'WORDTWIT_FAILURE_CODE', -1 );

class WordTwitBaseShortener {
	var $name;
	var $response_code;
	
	function WordTwitBaseShortener( $name ) {
		$this->name = $name;
		$this->response_code = false;
	}
	
	function get_name() {
		return $this->name;	
	}
	
	function set_name( $name ) {
		$this->name = $name;	
	}
	
	function shorten( $url ) {
		return $url;	
	}
	
	function get_response_code() {
		return $this->response_code;	
	}
	
	function do_get_request( $request_uri ) {
		$request = new WP_Http;
		
		$result = $request->request( $request_uri );
		
		if ( is_wp_error( $result ) ) {
			$this->response_code = WORDTWIT_FAILURE_CODE;
		} else {
			if ( isset( $result['response'] ) && isset( $result['response']['code'] ) ) { 
				$this->response_code = $result['response']['code'];
				if ( $result['response']['code'] == 200 ) {
					return $result['body'];
				}		
			}
		}		
		
		return false;
	}
}
	