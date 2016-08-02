<?php

require_once( 'transport.php' );

class WordTwitTransportWP extends WordTwitTransport {
	function WordTwitTranspotWP() {
		WordTwitTransport( 'WordPress', 'wp' );
		
		require_once( ABSPATH . WPINC. '/class-http.php' );
	}
	
	function supports_ip_address_override() {
		return false;	
	}	
	
	static function is_supported() {
		return class_exists( 'WP_Http' );	
	}
}
