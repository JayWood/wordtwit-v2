<?php

/* Administration panel bootstrap */
require_once( 'template-tags/tabs.php' );

add_action( 'admin_menu', 'wordtwit_admin_menu' );

function wordtwit_admin_menu() {
	$settings = wordtwit_get_settings();

	// Add the main plugin menu for WordTwit
	if ( wordtwit_user_can_modify_settings() ) {
		add_menu_page( 'WordTwit', 'WordTwit', wordtwit_user_get_role_for_modify_settings(), 'wordtwit_settings', '', WORDTWIT_URL . '/admin/images/wordtwit-admin-icon.png' );

		add_submenu_page( 'wordtwit_settings', __( 'WordTwit Settings', 'wordtwit-pro' ), __( 'WordTwit Settings', 'wordtwit-pro' ), wordtwit_user_get_role_for_modify_settings(), 'wordtwit_settings', 'wordtwit_admin_panel' );

		add_submenu_page( 'wordtwit_settings', __( 'Accounts', 'wordtwit-pro' ), __( 'Accounts', 'wordtwit-pro' ), wordtwit_user_get_role_for_add_account(), 'wordtwit_account_configuration', 'wordtwit_admin_account_configuration' );

		add_submenu_page( 'wordtwit_settings', __( 'Tweet Log', 'wordtwit-pro' ), __( 'Tweet Log', 'wordtwit-pro' ), wordtwit_user_get_role_for_add_account(), 'tweet_queue', 'wordtwit_admin_tweet_log' );
	} else if ( $settings->contributors_can_add_accounts && current_user_can( wordtwit_user_get_role_for_add_account() ) ) {
		add_menu_page( 'WordTwit', 'WordTwit', wordtwit_user_get_role_for_add_account(), 'wordtwit_account_configuration', '', WORDTWIT_URL . '/admin/images/wordtwit-admin-icon.png' );

		add_submenu_page( 'wordtwit_account_configuration', __( 'Accounts', 'wordtwit-pro' ), __( 'Accounts', 'wordtwit-pro' ), wordtwit_user_get_role_for_add_account(), 'wordtwit_account_configuration', 'wordtwit_admin_account_configuration' );

		add_submenu_page( 'wordtwit_account_configuration', __( 'Tweet Log', 'wordtwit-pro' ), __( 'Tweet Log', 'wordtwit-pro' ), wordtwit_user_get_role_for_add_account(), 'tweet_queue', 'wordtwit_admin_tweet_log' );

	} else if ( wordtwit_user_can_contribute_posts() ) {
		add_menu_page( 'WordTwit', 'WordTwit', wordtwit_user_get_role_for_contribute_posts(), 'tweet_queue', '', WORDTWIT_URL . '/admin/images/wordtwit-admin-icon.png' );

		add_submenu_page( 'tweet_queue', __( 'Tweet Log', 'wordtwit-pro' ), __( 'Tweet Log', 'wordtwit-pro' ), wordtwit_user_get_role_for_contribute_posts(), 'tweet_queue', 'wordtwit_admin_tweet_log' );
	}
}

function wordtwit_admin_panel() {
	// Setup administration tabs
	wordtwit_setup_tabs();

	// Generate tabs
	wordtwit_generate_tabs();
}

function wordtwit_admin_tweet_log() {
	include( WORDTWIT_DIR . '/admin/html/tweet-log.php' );
}

function wordtwit_admin_account_configuration() {
	include( WORDTWIT_DIR . '/admin/html/accounts.php' );
}

//! Can be used to add a tab to the settings panel
function wordtwit_add_tab( $tab_name, $class_name, $settings, $custom_page = false ) {
	global $wordtwit_pro;

	$wordtwit_pro->tabs[ $tab_name ] = array(
		'page' => $custom_page,
		'settings' => $settings,
		'class_name' => $class_name
	);
}

function wordtwit_generate_tabs() {
	include( 'html/admin-form.php' );
}

function wordtwit_string_to_class( $string ) {
	return strtolower( str_replace( '--', '-', str_replace( '+', '', str_replace( ' ', '-', $string ) ) ) );
}

function wordtwit_show_tab_settings() {
	include( 'html/tabs.php' );
}

function wordtwit_admin_get_languages() {
	$languages = array(
		'auto' => __( 'Auto-detect', 'wordtwit-pro' ),
		'da_DK' => 'Dansk',
		'de_DE' => 'Deutsch',
		'english' => 'English',
		'es_ES' => 'Español',
		'fr_FR' => 'Français',
		'sr_RS' => 'Serbian',
		'ko_KR' => '한국어/조선말',
		'it_IT' => 'Italiano',
		'ja_JP' => '日本語',
		'nl_NL' => 'Nederlands',
		'pt_PT' => 'Português',
		'ru_RU' => 'Русский язык',
		'sv_SE' => 'Svenska',
		'zh_CN' => '简体字'
	);

	return apply_filters( 'wordtwit_admin_languages', $languages );
}

function wordtwit_save_reset_notice() {
	if ( isset( $_POST[ 'wordtwit-submit' ] ) ) {
		echo ( '<div class="saved">' );
		echo __( 'Settings saved!', "wordtwit-pro" );
		echo ('</div>');
	} elseif ( isset( $_POST[ 'wordtwit-submit-reset' ] ) ) {
		echo ( '<div class="reset">' );
		echo __( 'Defaults restored', "wordtwit-pro" );
		echo ( '</div>' );
	}
}

function wordtwit_get_transport_layers() {
	$transport_layers = array(
		'default' => __( 'Default', 'wordtwit-pro' )
	);

	if ( function_exists( 'curl_init' ) ) {
		$transport_layers[ 'curl' ] = __( 'Curl', 'wordtwit-pro' );
	}

	return $transport_layers;
}

function wordtwit_get_shortener_list() {
	$shorteners = array(
		'wordpress' => 'WordPress',
		'bitly' => 'bit.ly',
		'isgd' => 'is.gd',
		'owly' => 'ow.ly',
		'tinyurl' => 'TinyURL',
		'stumbleupon' => 'StumbleUpon',
		'yourls' => 'YOURLS'
	);

	return apply_filters( 'wordtwit_shortener_list', $shorteners );
}

function wordtwit_get_default_tweet_sep_list() {
	$tweet_sep_list = array(
		'15' => sprintf( __( '%d minutes', 'wordtwit-pro' ), 15 ),
		'30' => sprintf( __( '%d minutes', 'wordtwit-pro' ), 30 ),
		'45' => sprintf( __( '%d minutes', 'wordtwit-pro' ), 45 )
	);

	for ( $h = 1; $h <= 12; $h++ ) {
		for ( $i = 0; $i <= 30; $i += 30 ) {
			$s = sprintf( _n( '%d hour', '%d hours', $h, 'wordtwit-pro' ), $h );
			if ( $i > 0 ) {
				$s = $s . sprintf( __( ', %d minutes', 'wordtwit-pro' ), $i );
			}

			$tweet_sep_list[ ( $h * 60 + $i ) ] = $s;
		}
	}

	return $tweet_sep_list;
}

function wordtwit_get_advanced_settings() {
	$advanced = array(
		array( 'section-start', 'custom-content', __( '3rd Party Publishing & Tweet Management', 'wordtwit-pro' ) ),
		array( 'checkbox', 'allow_third_party', __( 'Tweet automatically when content is generated by other plugins or third party applications via XML-RPC', 'wordtwit-pro' ), '' ),
		array( 'checkbox', 'remove_old_tweets', __( 'Clean-up multiple tweets on Twitter as new tweets are published', 'wordtwit-pro' ), __( 'When enabled, WordTwit will remove previous tweets published when new scheduled tweets from the same post go out', 'wordtwit-pro' ) ),
		array( 'section-end' ),
		array( 'section-start', 'oauth', __( 'Twitter API Compliance', 'wordtwit-pro' ) ),
		array( 'text', 'oauth_time_offset', sprintf( __( 'Tweet time offset to correct server mismatch. %sEstimate now%s', 'wordtwit-pro' ), '<a href="#" id="estimate-offset">', '</a>' ), __( 'The Twitter API requires the time on your server to be correct.  You can use this field to add or subtract an amount of time. Number is in seconds only. e.g. 3600 (equals 1 hour)', 'wordtwit-pro' ) ),
		array( 'section-end' ),
		array( 'section-start', 'custom-content', __( 'Custom Content', 'wordtwit-pro' ) ),
		array( 'text', 'custom_post_types', __( 'List of custom post types to Tweet', 'wordtwit-pro' ), __( 'Enter a comma-separated list of custom post types that will be Tweeted automatically.', 'wordtwit-pro' ) ),
		array( 'section-end' )
	);

	return $advanced;
}

function wordtwit_setup_general_tab() {
	$settings = wordtwit_get_settings();

	wordtwit_add_tab( __( 'General', 'wordtwit-pro' ), 'general',
		array(
			__( 'Overview', 'wordtwit-pro' ) => array( 'twitboard',
				array(
					array( 'section-start', 'twitboard', __( 'TwitBoard', "wordtwit-pro" ) ),
					array( 'twitboard' ),
					array( 'section-end' )
				)
			),
			__( 'Options', 'wordtwit-pro' ) => array( 'options',
				array(
					array( 'section-start', 'options', __( 'Twitter Custom Application Setup', 'wordtwit-pro' ) ),
					array( 'text', 'custom_consumer_key', __( 'API Key', 'wordtwit-pro' ), __( "You must create a custom application via Twitter and configure WordTwit with your application's credentials.", "wordtwit-pro" ) ),
					array( 'text', 'custom_consumer_secret', __( 'API Secret', 'wordtwit-pro' ), '' ),
					array( 'oauth-signup' ),
					array( 'section-end' ),
					array( 'section-start', 'general-settings', __( 'General', 'wordtwit-pro' ) ),
					array( 'list', 'force_locale', __( 'Admin panel language', 'wordtwit-pro' ), '', wordtwit_admin_get_languages() ),
					array( 'checkbox', 'resolve_wordtwit_custom', __( 'Resolve custom short URLs from WordTwit 2.x', 'wordtwit-pro' ), '' ),
					array( 'checkbox', 'contributors_can_add_accounts', __( 'Allow other WordPress users to add their own Twitter accounts', 'wordtwit-pro' ), '' ),
					array(
						'list',
						'minimum_user_capability_for_account_add',
						__( 'Minimum WordPress user level authorized to add Twitter accounts', 'wordtwit-pro' ),
						'',
						array(
							'edit_others_pages' => __( 'Editors', 'wordtwit-pro' ),
							'publish_posts' => __( 'Editors and Authors', 'wordtwit-pro' ),
							'edit_posts' => __( 'Editors, Authors, and Contributors', 'wordtwit-pro' )
						)
					),
					array(
						'list',
						'stagger_tweet_time',
						__( 'Stagger tweets to multiple accounts by this amount', 'wordtwit-pro' ),
						__( 'By default, tweets will go out simultaneously when multiple accounts are selected.  This option allows a slight time delay to be added so the tweets appear at slightly different times', 'wordtwit-pro' ),
						array(
							'0' => __( 'Not staggered', 'wordtwit-pro' ),
							'1' => __( '1 Minute', 'wordtwit-pro' ),
							'2' => __( '2 Minutes', 'wordtwit-pro' ),
							'3' => __( '3 Minutes', 'wordtwit-pro' ),
							'4' => __( '4 Minutes', 'wordtwit-pro' ),
							'5' => __( '5 Minutes', 'wordtwit-pro' )
						)
					),
					array( 'section-end' ),
					array( 'section-start', 'widget-settings', __( 'Publishing Widget', 'wordtwit-pro' ) ),
					array( 'checkbox', 'default_enable_state', __( 'Enable Tweeting by default', 'wordtwit-pro' ), __( 'When enabled, the post widget will automatically post Tweets by default', 'wordtwit-pro' ) ),
					array( 'checkbox', 'disable_retweet_warning', __( 'Disable warning message when retweeting from the post widget', 'wordtwit-pro' ), '' ),
					array( 'checkbox', 'shorten_title', __( 'Shorten post titles to maintain tweet character limit', 'wordtwit-pro' ) ),
					array(
						'list',
						'manual_tweet_behaviour',
						__( 'Behaviour of manual tweet editing', 'wordtwit-pro' ),
						__( 'The default behaviour involves editing the tweet template, but this can be changed so all tags other than [link] are expanded for easier editing', 'wordtwit-pro' ),
						array(
							'template' => __( 'No template fields are expanded','wordtwit-pro' ),
							'expanded' => __( '[link] template tag is preserved', 'wordtwit-pro' ),
							'partial' => __( '[link] and [hashtags] template tags are preserved', 'wordtwit-pro' )
						)
					),
					array(
						'list',
						'tweet_template',
						__( 'Individual tweet template', 'wordtwit-pro' ),
						'',
						wordtwit_get_tweet_templates()
					),
					array( 'text', 'custom_tweet_template', __( 'Custom tweet template', 'wordtwit-pro' ), __( 'You can enter a custom tweet template to be used here.', 'wordtwit-pro' ) . ' ' . __( 'Valid tags are [post_type], [link], [title], [full_author], [short_author], and [hashtags].', 'wordtwit-pro' ) ),
					array( 'section-end' ),
					array( 'section-start', 'tweet_defaults', __( 'Default Post Information', 'wordtwit-pro' ) ),
					array(
						'list',
						'default_tweet_times',
						__( 'Number of times to publish each Tweet on each enabled account', 'wordtwit-pro' ),
						'',
						array(
							'1' => sprintf( _n( '%d time', '%d times', 1, 'wordtwit-pro' ), 1 ),
							'2' => sprintf( _n( '%d time', '%d times', 2, 'wordtwit-pro' ), 2 ),
							'3' => sprintf( _n( '%d time', '%d times', 3, 'wordtwit-pro' ), 3 ),
							'4' => sprintf( _n( '%d time', '%d times', 4, 'wordtwit-pro' ), 4 ),
							'5' => sprintf( _n( '%d time', '%d times', 5, 'wordtwit-pro' ), 5 ),
						)
					),
					array(
						'list',
						'default_tweet_sep',
						__( 'Amount of time between Tweets', 'wordtwit-pro' ),
						'',
						wordtwit_get_default_tweet_sep_list()
					),
					array( 'section-end' ),
					array( 'section-start', 'shorteners', __( 'Shortening Method', 'wordtwit-pro' ) ),
					array(
						'list',
						'url_shortener',
						__( 'URL Shortener', 'wordtwit-pro' ),
						__( 'Long URLs will automatically be shortened using the specified URL shortener', 'wordtwit-pro' ),
						wordtwit_get_shortener_list()
					),
					array( 'text', 'yourls_path', __( 'Full URL path to yourls-api.php', 'wordtwit-pro' ) ),
					array( 'text', 'yourls_signature', __( 'Authentication signature for YOURLS', 'wordtwit-pro' ), __( 'Can be found in the YOURLS administration panel.', 'wordtwit-pro' ) ),
					array( 'text', 'bitly_username', __( 'Bit.ly username', 'wordtwit-pro' ) ),
					array( 'text', 'bitly_api_key', __( 'Bit.ly API key', 'wordtwit-pro' ) ),
					//array( 'text', 'cloudapp_username', __( 'CloudApp username', 'wordtwit-pro' ) ),
					//array( 'password', 'cloudapp_password', __( 'CloudApp password', 'wordtwit-pro' ) ),
					array( 'section-end' ),
					array( 'section-start', 'tracking-options', __( 'Tracking', 'wordtwit-pro' ) ),
					array( 'checkbox', 'enable_utm', __( 'Add UTM tracking tags to Tweeted URLs', 'wordtwit-pro' ), __( "Adds UTM tags to the URLs created by WordTwit. Requires a URL shortener other than 'WordPress' to be used.", "wordtwit-pro" )  ),
					array( 'text', 'utm_source', __( 'UTM source tag', 'wordtwit-pro' ) ),
					array( 'text', 'utm_medium', __( 'UTM medium tag', 'wordtwit-pro' ) ),
					array( 'text', 'utm_campaign', __( 'UTM campaign tag', 'wordtwit-pro' ) ),
					array( 'section-end' )
				)
			),
			/*
			__( 'Network', 'wordtwit-pro' ) => array( 'network',
				array(
					array( 'section-start', 'network-options', __( 'Network Options', 'wordtwit-pro' ) ),
					array( 'text', 'alternate_ip_address', __( 'Use alternate IP address', 'wordtwit-pro' ), '' ),
					array( 'list', 'transport_layer', __( 'Transport layer mechanism', 'wordtwit-pro' ), __( 'Can be used to force a specific connection to Twitter', 'wordtwit-pro' ),
						wordtwit_get_transport_layers()
					),
					array( 'section-end' )
				)
			), */
			__( 'Advanced', 'wordtwit-pro' ) => array( 'advanced',
				wordtwit_get_advanced_settings()
			)
		)
	);
}

function wordtwit_setup_tabs() {
	$settings = wordtwit_get_settings();

	wordtwit_setup_general_tab();
}