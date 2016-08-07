<?php

function WORDTWIT_DEBUG( $str ) {
	global $wordtwit_debug;

	$wordtwit_debug->add_to_log( $str );
}

function wordtwit_is_debug_enabled() {
	global $wordtwit_debug;

	return $wordtwit_debug->debug;
}

class WordTwitDebug {

	/**
	 * Determines if the debug is on or not.
	 * @var bool
	 */
	public $debug = false;

	function __construct() {
		// Enable WordTwit debug based on wp-config settings
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->debug = true;
		}
	}

	/**
	 * Returns the current state of the debug settings
	 *
	 * @author JayWood
	 * @return bool
	 */
	function is_enabled() {
		return $this->debug;
	}

	/**
	 * A more simplistic logging method
	 *
	 * @param mixed $str
	 *
	 * @author JayWood
	 */
	function add_to_log( $str ) {
		if ( $this->debug ) {
			error_log( 'WORDTWIT - ' . print_r( $str, 1 ) );
		}
	}
}

global $wordtwit_debug;
$wordtwit_debug = new WordTwitDebug;
