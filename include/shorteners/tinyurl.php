<?php

include_once( 'base_shortener.php' );

class WordTwitTinyUrlShortener extends WordTwitBaseShortener {
	function WordTwitTinyUrlShortener() {
		parent::WordTwitBaseShortener( 'tinyurl' );
	}
	
	function shorten( $url ) {
		$tiny_url = $this->do_get_request( 'http://tinyurl.com/api-create.php?url=' . urlencode( $url ) );
		if ( $tiny_url ) {
			return $tiny_url;
		} else {
			return $url;	
		}
	}
}	
