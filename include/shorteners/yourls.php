<?php

include_once( 'base_shortener.php' );

class WordTwitYourlsShortener extends WordTwitBaseShortener {
	var $path;
	var $signature;
	
	function WordTwitYourlsShortener( $path, $signature ) {
		parent::WordTwitBaseShortener( 'yourls' );
		
		$this->path = $path;
		$this->signature = $signature;
	}
	
	function shorten( $url ) {
		$request_uri = $this->path . '?signature=' . $this->signature . '&action=shorturl&format=json&url=' . urlencode( $url );			
		
		$encoded_result = $this->do_get_request( $request_uri );
		if ( $encoded_result ) {
			$decoded_result = json_decode( $encoded_result );
			if ( $decoded_result ) {
				if ( isset( $decoded_result->statusCode ) && $decoded_result->statusCode == 200 ) {
					return $decoded_result->shorturl;
				}
			}
		} 
				
		return $url;
	}
}	
