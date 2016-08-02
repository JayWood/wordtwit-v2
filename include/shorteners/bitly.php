<?php

include_once( 'base_shortener.php' );

class WordTwitBitlyShortener extends WordTwitBaseShortener {
	var $api_key;
	var $login;
	
	function WordTwitBitlyShortener( $login, $api_key ) {
		parent::WordTwitBaseShortener( 'bitly' );
		
		$this->login = $login;
		$this->api_key = $api_key;
	}
	
	function shorten( $url ) {
		$request_uri = 'http://api.bitly.com/v3/shorten?format=json' . 
			'&longUrl=' . urlencode( $url ) . 
			'&apiKey=' . $this->api_key . 
			'&login=' . $this->login;
		
		$encoded_data = $this->do_get_request( $request_uri );
		if ( $encoded_data ) {
			$decoded_result = json_decode( $encoded_data );
			if ( $decoded_result && $decoded_result->status_code == 200 && isset( $decoded_result->data ) && isset( $decoded_result->data->url ) ) {
				return $decoded_result->data->url;	
			}
		}
		
		return $url;	
	}
}	
