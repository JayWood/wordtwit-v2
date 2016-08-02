<?php

class WordTwitTransport {
	var $friendly_name;
	var $slug;
	var $timeout;
	var $response_code;
	
	function WordTwitTransport( $friendly_name, $slug ) {
		$this->friendly_name = $friendly_name;
		$this->slug = $slug;
		$this->timeout = 10;
		
		$this->response_code = 0;
	}	
	
	function do_request( $url, $headers = false, $body = false, $do_post = false, $post_params = false ) {
		
	}
	
	function supports_ip_address_override() {
		return false;	
	}
	
	static function is_supported() {
		return false;	
	}	
	
	
	function set_timeout( $timeout ) {
		$this->timeout = $timeout;
	}
	
	function get_timeout() {
		return $this->timeout;	
	}	
	
	function get_response_code() {
		return $this->response_code;	
	}
}
