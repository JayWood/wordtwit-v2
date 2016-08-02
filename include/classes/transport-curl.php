<?php

require_once( 'transport.php' );

class WordTwitTransportCurl extends WordTwitTransport {
	function WordTwitTransportCurl() {
		parent::WordTwitTransport( 'Curl', 'curl' );
	}
	
	function supports_ip_address_override() {
		return true;	
	}	
	
	static function is_supported() {
		return function_exists( 'curl_init' );	
	}
	
	function do_request( $url, $headers = false, $body = false, $do_post = false, $post_params = false ) {
		$ch = curl_init( $url );

		if ( $do_post ) {
			// we're doing a POST request
			curl_setopt( $ch, CURLOPT_POST, 1 );
		}

		if ( $post_params ) {
			$body_array = array();
			foreach( $post_params as $key => $value ) {
				$body_array[] = urlencode( $key ) . '=' . urlencode( $value );
			}
			
			curl_setopt( $ch, CURLOPT_POSTFIELDS, implode( $body_array, '&' ) );
		}
		
		// Check to see if we can add headers
		if ( is_array( $headers ) ) {
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers ); 	
		}
				
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		
		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->timeout );		

		$contents = curl_exec( $ch );
		$response = curl_getinfo( $ch );
		$this->response_code = $response[ 'http_code' ];
		curl_close( $ch );
		
		return $contents;
	}
}
