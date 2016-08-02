<?php

global $wordtwit_tab_iterator;
global $wordtwit_tab;
global $wordtwit_tab_id;

global $wordtwit_tab_section_iterator;
global $wordtwit_tab_section;

global $wordtwit_tab_section_settings_iterator;
global $wordtwit_tab_section_setting;

global $wordtwit_tab_options_iterator;
global $wordtwit_tab_option;

$wordtwit_tab_iterator = false;

function wordtwit_has_tabs() {
	global $wordtwit_tab_iterator;
	global $wordtwit_pro;
	global $wordtwit_tab_id;
	
	if ( !$wordtwit_tab_iterator ) {
		$wordtwit_tab_iterator = new WordTwitArrayIterator( $wordtwit_pro->tabs );
		$wordtwit_tab_id = 0;
	}
	
	return $wordtwit_tab_iterator->have_items();	
}

function wordtwit_rewind_tab_settings() {
	global $wordtwit_tab_section_iterator;
	$wordtwit_tab_section_iterator = false;
}

function wordtwit_the_tab() {
	global $wordtwit_tab;
	global $wordtwit_tab_iterator;
	global $wordtwit_tab_id;
	global $wordtwit_tab_section_iterator;
	
	$wordtwit_tab = apply_filters( 'wordtwit_tab', $wordtwit_tab_iterator->the_item() );
	$wordtwit_tab_section_iterator = false;
	$wordtwit_tab_id++;
}

function wordtwit_the_tab_id() {
	echo wordtwit_get_tab_id();
}

function wordtwit_get_tab_id() {
	global $wordtwit_tab_id;
	return apply_filters( 'wordtwit_tab_id', $wordtwit_tab_id );	
}

function wordtwit_has_tab_sections() {
	global $wordtwit_tab;	
	global $wordtwit_tab_section_iterator;
	
	if ( !$wordtwit_tab_section_iterator ) {
		$wordtwit_tab_section_iterator = new WordTwitArrayIterator( $wordtwit_tab['settings'] );
	}
	
	return $wordtwit_tab_section_iterator->have_items();
}

function wordtwit_the_tab_section() {
	global $wordtwit_tab_section;
	global $wordtwit_tab_section_iterator;
	global $wordtwit_tab_section_settings_iterator;
		
	$wordtwit_tab_section = apply_filters( 'wordtwit_tab_section', $wordtwit_tab_section_iterator->the_item() );
	$wordtwit_tab_section_settings_iterator = false;
}

function wordtwit_the_tab_name() {
	echo wordtwit_get_tab_name();
}

function wordtwit_get_tab_name() {
	global $wordtwit_tab_section_iterator;
		
	return apply_filters( 'wordtwit_tab_name', $wordtwit_tab_section_iterator->the_key() );
}

function wordtwit_the_tab_class_name() {
	echo wordtwit_get_tab_class_name();
}

function wordtwit_get_tab_class_name() {
	return wordtwit_string_to_class( wordtwit_get_tab_name() );	
}


function wordtwit_has_tab_section_settings() {
	global $wordtwit_tab_section;
	global $wordtwit_tab_section_settings_iterator;
	
	if ( !$wordtwit_tab_section_settings_iterator ) {
		$wordtwit_tab_section_settings_iterator = new WordTwitArrayIterator( $wordtwit_tab_section[1] );
	}
	
	return $wordtwit_tab_section_settings_iterator->have_items();
}

function wordtwit_the_tab_section_setting() {
	global $wordtwit_tab_section_setting;
	global $wordtwit_tab_section_settings_iterator;
	global $wordtwit_tab_options_iterator;
		
	$wordtwit_tab_section_setting = apply_filters( 'wordtwit_tab_section_setting', $wordtwit_tab_section_settings_iterator->the_item() );
	$wordtwit_tab_options_iterator = false;
}

function wordtwit_the_tab_section_class_name() {
	echo wordtwit_get_tab_section_class_name();
}

function wordtwit_get_tab_section_class_name() {
	global $wordtwit_tab_section;
	
	return $wordtwit_tab_section[0];
}

function wordtwit_the_tab_setting_type() {
	echo wordtwit_get_tab_setting_type();
}

function wordtwit_get_tab_setting_type() {
	global $wordtwit_tab_section_setting;
	return apply_filters( 'wordtwit_tab_setting_type', $wordtwit_tab_section_setting[0] );
}

function wordtwit_the_tab_setting_name() {
	echo wordtwit_get_tab_setting_name();
}

function wordtwit_get_tab_setting_name() {
	global $wordtwit_tab_section_setting;
	
	return apply_filters( 'wordtwit_tab_setting_name', $wordtwit_tab_section_setting[1] );		
}

function wordtwit_the_tab_setting_class_name() {
	echo wordtwit_get_tab_setting_class_name();
}

function wordtwit_get_tab_setting_class_name() {
	global $wordtwit_tab_section_setting;
	
	if ( isset( $wordtwit_tab_section_setting[1] ) ) {
		return apply_filters( 'wordtwit_tab_setting_class_name', wordtwit_string_to_class( $wordtwit_tab_section_setting[1] ) );	
	} else {
		return false;	
	}	
}

function wordtwit_the_tab_setting_has_tooltip() {
	return ( strlen( wordtwit_get_tab_setting_tooltip() ) > 0 );
}

function wordtwit_the_tab_setting_tooltip() {
	echo wordtwit_get_tab_setting_tooltip();
}

function wordtwit_get_tab_setting_tooltip() {
	global $wordtwit_tab_section_setting;
	
	if ( isset( $wordtwit_tab_section_setting[3] ) ) {
		return htmlspecialchars( apply_filters( 'wordtwit_tab_setting_tooltip', $wordtwit_tab_section_setting[3] ), ENT_COMPAT, 'UTF-8' );	
	} else {
		return false;	
	}	
}


function wordtwit_the_tab_setting_desc() {
	echo wordtwit_get_tab_setting_desc();
}

function wordtwit_get_tab_setting_desc() {
	global $wordtwit_tab_section_setting;
	return apply_filters( 'wordtwit_tab_setting_desc', $wordtwit_tab_section_setting[2] );		
}

function wordtwit_the_tab_setting_value() {
	echo wordtwit_get_tab_setting_value();
}

function wordtwit_get_tab_setting_value() {
	$settings = wordtwit_get_settings();
	$name = wordtwit_get_tab_setting_name();
	if ( isset( $settings->$name ) ) {
		return $settings->$name;	
	} else {
		return false;	
	}
}

function wordtwit_the_tab_setting_is_checked() {
	return wordtwit_get_tab_setting_value();
}

function wordtwit_tab_setting_has_options() {
	global $wordtwit_tab_options_iterator;
	global $wordtwit_tab_section_setting;
	
	if ( isset( $wordtwit_tab_section_setting[4] ) ) {			
		if ( !$wordtwit_tab_options_iterator ) {
			$wordtwit_tab_options_iterator = new WordTwitArrayIterator( $wordtwit_tab_section_setting[4] );	
		}
		
		return $wordtwit_tab_options_iterator->have_items();
	} else {
		return false;	
	}
}

function wordtwit_tab_setting_the_option() {
	global $wordtwit_tab_options_iterator;
	global $wordtwit_tab_option;	
	
	$wordtwit_tab_option = apply_filters( 'wordtwit_tab_setting_option', $wordtwit_tab_options_iterator->the_item() );
}

function wordtwit_tab_setting_the_option_desc() {
	echo wordtwit_tab_setting_get_option_desc();
}	

function wordtwit_tab_setting_get_option_desc() {
	global $wordtwit_tab_option;		
	return apply_filters( 'wordtwit_tab_setting_option_desc', $wordtwit_tab_option );
}	

function wordtwit_tab_setting_the_option_key() {
	echo wordtwit_tab_setting_get_option_key();
}

function wordtwit_tab_setting_get_option_key() {
	global $wordtwit_tab_options_iterator;
	return apply_filters( 'wordtwit_tab_setting_option_key', $wordtwit_tab_options_iterator->the_key() );	
}

function wordtwit_tab_setting_is_selected() {
	return ( wordtwit_tab_setting_get_option_key() == wordtwit_get_tab_setting_value() );
}
