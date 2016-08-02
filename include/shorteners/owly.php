<?php

define( 'WORDTWIT_OWLY_API_KEY', 'R1M5MG9ybVRyWHk3MTV4RThBRFRu' );

include_once( 'base_shortener.php' );

class WordTwitOwlyShortener extends WordTwitBaseShortener {
	function WordTwitOwlyShortener() {
		parent::WordTwitBaseShortener( 'owly' );
	}
	
	function shorten( $url ) {
		$json_blob = $this->do_get_request( 'http://ow.ly/api/1.0/url/shorten?apiKey=' . base64_decode( WORDTWIT_OWLY_API_KEY ) . '&longUrl=' . urlencode( $url ) );
		if ( $json_blob ) {
			$decoded_blob = json_decode( $json_blob );
			if ( $decoded_blob && isset( $decoded_blob->results ) && isset( $decoded_blob->results->shortUrl ) ) {
				return $decoded_blob->results->shortUrl;
			}
		}
		
		return $url;	
	}
}	
