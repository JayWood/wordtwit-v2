<?php

include_once( 'base_shortener.php' );

class WordTwitStumbleUponShortener extends WordTwitBaseShortener {
	function WordTwitStumbleUponShortener() {
		parent::WordTwitBaseShortener( 'stumbleupon' );
	}
	
	function shorten( $url ) {
		$data = $this->do_get_request( 'http://su.pr/api/shorten?longUrl=' . urlencode( $url ) );
		if ( $data ) {
			$decoded_info = json_decode( $data );
			if ( $decoded_info && isset( $decoded_info->errorCode ) && $decoded_info->errorCode == 0 ) {
				return $decoded_info->results->$url->shortUrl;
			}
		} 
		
		return $url;	
	}
}	
