<?php

/*
Google Calendar List View
Author: Noliani Clemente Hernández
*/

defined('ABSPATH') or die("you do not have access to this page!");

require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/autoload.php';

require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/psr/http-message/src/UriInterface.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/guzzlehttp/psr7/src/Query.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/guzzlehttp/psr7/src/Uri.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/guzzlehttp/psr7/src/Utils.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/google/apiclient/src/Model.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/google/apiclient/src/Client.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/google/apiclient/src/Service.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/google/apiclient-services/src/Calendar.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/google/apiclient-services/src/Calendar/Calendar.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/google/auth/src/GetUniverseDomainInterface.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/google/auth/src/FetchAuthTokenInterface.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/google/auth/src/OAuth2.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/google/auth/src/HttpHandler/HttpHandlerFactory.php';

class G_Cal_List_View_Client {
	
	private $client;

  public function __construct() {
		$this -> client = new Google\Client ();
		// $request_login_state = 'g_cal_list_view_client';
	}
	
	public function init_client ( $redirect = '' ) {
		// Required, call the setAuthConfig function to load authorization credentials from
		// client_secret.json file.
		$this -> client -> setAuthConfig ( WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/auth/client_secret_441737119463-7b5dg9f26dt469bdfa7geju9u201lfsc.apps.googleusercontent.com.json' );

		// Required, to set the scope value, call the addScope function
		$this -> client -> addScope ( Google\Service\Calendar::CALENDAR );

		// Required, call the setRedirectUri function to specify a valid redirect URI for the
		// provided client_id
		$this -> client -> setRedirectUri ( $redirect );

		// Recommended, offline access will give you both an access and refresh token so that
		// your app can refresh the access token without user interaction.
		$this -> client -> setAccessType ( 'offline' );

		// Recommended, call the setState function. Using a state value can increase your assurance that
		// an incoming connection is the result of an authentication request.
		// $this -> client -> setState ( $request_login_state );

		// Optional, if your application knows which user is trying to authenticate, it can use this
		// parameter to provide a hint to the Google Authentication Server.
		// $this -> client->setLoginHint('hint@example.com');

		// Optional, call the setPrompt function to set "consent" will prompt the user for consent
		$this -> client -> setPrompt ( 'consent' );

		// Optional, call the setIncludeGrantedScopes function with true to enable incremental
		// authorization
		$this -> client -> setIncludeGrantedScopes ( true );

	}
	
	public function get_new_auth_token ( $refresh_token ) {
		$this -> client -> refreshToken ( $refresh_token );
		$new_token = $this -> client -> getAccessToken ();
		if ( $new_token )
			return $new_token [ 'access_token' ];
		return null;
	}
	
	public function set_session_state ( ) {
		$state = bin2hex ( random_bytes ( 16 ));
		$this -> client -> setState ( $state );
		$_SESSION[ 'state' ] = $state;
	}

	public function createAuthUrl () {
		return $this -> client -> createAuthUrl ();
	}

	public function revokeToken () {
		return $this -> client -> revokeToken ();
	}

	public function laod_access_token ( $code ) {
		$access_token = $this -> client -> fetchAccessTokenWithAuthCode ( $code );
		$this -> client -> setAccessToken ( $access_token );
		return $access_token;
	}
}