<?php

function WORDTWIT_DEBUG( $str ) {
	global $wordtwit_debug;
	
	if ( is_object( $str ) || is_array( $str ) ) {
		ob_start();
		var_dump( $str );
		$str = ob_get_contents();
		ob_clean();
	}

	$wordtwit_debug->add_to_log( $str );
}

function wordtwit_is_debug_enabled() {
	global $wordtwit_debug;
	
	return $wordtwit_debug->is_enabled();
}

class WordTwitDebug {
	var $debug_file;
	var $log_messages;

	function WordTwitDebug() {
		$this->debug_file = false;
		$this->enable( false );
	}
	
	function is_enabled() {
		return ( $this->debug_file );	
	}

	function enable( $enable_or_disable ) {
		if ( $enable_or_disable ) {
			$this->debug_file = fopen( WP_CONTENT_DIR . '/plugins/wordtwit/debug.txt', 'a+t' );
			$this->log_messages = 0;
		} else if ( $this->debug_file ) {
			fclose( $this->debug_file );
			$this->debug_file = false;		
		}
	}

	function add_to_log( $str ) {
		if ( $this->debug_file ) {
			
			$log_string = $str;
			
			// Write the data to the log file
			fwrite( $this->debug_file, sprintf( "%12s %s\n", time(), $log_string ) );
			fflush( $this->debug_file );
			
			$this->log_messages++;
		}
	}
}

global $wordtwit_debug;
$wordtwit_debug = new WordTwitDebug;
