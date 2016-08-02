<?php

include_once( 'base_shortener.php' );

class CloudAppShortener extends WordTwitBaseShortener {
	var $username;
	var $password;
	
	function CloudAppShortener( $username, $password ) {
		parent::WordTwitBaseShortener( 'cloudapp' );
		
		$this->username = $username;
		$this->password = $password;
	}
	
	function do_post_request( $request_uri, $body, $username = false, $password = false ) {
		$curl = curl_init( $request_uri );
		
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json' 
		);
		
		curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST );
		curl_setopt( $curl, CURLOPT_USERPWD, $username . ':' . $password );
		
		curl_setopt( $curl, CURLOPT_USERAGENT, 'WordTwit' . WORDTWIT_VERSION );
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'POST' );

		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );		

		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $body );

        $response = curl_exec( $curl );
        $status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );	
        curl_close( $curl );
        
        if ( $status_code != 200 ) {
			$this->response_code = WORDTWIT_FAILURE_CODE;
		} else {
			$this->response_code = 200;
			$decoded_body = json_decode( $response );
			if ( $decoded_body && isset( $decoded_body->url ) ) {	
				return $decoded_body->url;
			}
		}		
		
		return false;		
	}	
	
	function shorten( $url, $title ) {
		$body = array( 'item' => array( 'name' => $title, 'redirect_url' => $url, 'private' => false ) );
		
		$tiny_url = $this->do_post_request( 'http://my.cl.ly/items', json_encode( $body ), $this->username, $this->password );
		if ( $tiny_url ) {
			return $tiny_url;
		} else {
			return $url;	
		}
	}
}	

function wordtwit_can_use_cloud_app() {
	return ( function_exists( 'json_decode' ) && function_exists( 'curl_init' ) );	
}
