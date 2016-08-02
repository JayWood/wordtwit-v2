<?php

include_once( 'base_shortener.php' );

class WordTwitIsgdShortener extends WordTwitBaseShortener {
	function WordTwitIsgdShortener() {
		parent::WordTwitBaseShortener( 'isgd' );
	}
	
	function shorten( $url ) {
		$tiny_url = $this->do_get_request( 'http://is.gd/create.php?format=simple&url=' . urlencode( $url ) );		

		if ( $tiny_url ) {
			return $tiny_url;
		} else {
			return $url;	
		}
	}
}	
