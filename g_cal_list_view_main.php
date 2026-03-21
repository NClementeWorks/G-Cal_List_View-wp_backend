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

$client = new Google\Client ();
$request_login_state = 'testing000';

// Required, call the setAuthConfig function to load authorization credentials from
// client_secret.json file.
$client -> setAuthConfig ( WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/auth/client_secret_441737119463-7b5dg9f26dt469bdfa7geju9u201lfsc.apps.googleusercontent.com.json' );

// Required, to set the scope value, call the addScope function
$client -> addScope ( Google\Service\Calendar::CALENDAR_READONLY );

// Required, call the setRedirectUri function to specify a valid redirect URI for the
// provided client_id
$client -> setRedirectUri ('https://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ] . '?page=g_cal_list_view_settings' );

// Recommended, offline access will give you both an access and refresh token so that
// your app can refresh the access token without user interaction.
$client -> setAccessType ( 'offline' );

// Recommended, call the setState function. Using a state value can increase your assurance that
// an incoming connection is the result of an authentication request.
// $client -> setState ( $request_login_state );

// Optional, if your application knows which user is trying to authenticate, it can use this
// parameter to provide a hint to the Google Authentication Server.
// $client->setLoginHint('hint@example.com');

// Optional, call the setPrompt function to set "consent" will prompt the user for consent
$client -> setPrompt ( 'consent' );

// Optional, call the setIncludeGrantedScopes function with true to enable incremental
// authorization
$client -> setIncludeGrantedScopes ( true );


//
// Retrieve current refresh token
global $wpdb;
	
$token_res = $wpdb -> get_row (
	$wpdb -> prepare ( "SELECT g.setting_value FROM " . $wpdb->prefix . "g_cal_list_view g WHERE g.setting_name = 'gclient_rtk'")
);

if ( isset ( $token_res ) && !!$token_res -> setting_value ) {
	$refresh_token = $token_res -> setting_value;
	$client -> refreshToken ( $refresh_token );
	$newtoken = $client -> getAccessToken ();
}

$plugin_url = get_site_url() . '/wp-content/plugins' . $GLOBALS[ 'g_cal_list_view_plugin_folder' ];
?>
	<style>
@font-face
{
	font-family:Material Design Icons;
	src:url(<?= $plugin_url ?>/assets/materialdesignicons-webfont-CSr8KVlo.eot?v=7.4.47);
	src:url(<?= $plugin_url ?>/assets/materialdesignicons-webfont-CSr8KVlo.eot?#iefix&v=7.4.47) format("embedded-opentype"),
		url(<?= $plugin_url ?>/assets/materialdesignicons-webfont-Dp5v-WZN.woff2?v=7.4.47) format("woff2"),
		url(<?= $plugin_url ?>/assets/materialdesignicons-webfont-PXm3-2wK.woff?v=7.4.47) format("woff"),
		url(<?= $plugin_url ?>/assets/materialdesignicons-webfont-B7mPwVP_.ttf?v=7.4.47) format("truetype");
	font-weight:400;
	font-style:normal;
}

.v-field .v-field__input, .v-field .v-field__input input {
    border: 0;
    background-color: transparent;
}
	</style>
<?php
	if ( isset ( $_GET[ 'token' ] ) || ( isset ( $token_res ) && !!$token_res -> setting_value ) ) {
		
		$gclv_login_page_id = g_calendar_list_view_get_setting ( 'gclv_login_page_id' );
		$gclv_login_page = get_post ( $gclv_login_page_id );
		wp_nonce_field ( 'wp_rest', '_wpnonce', true, true );
?>
	<input id="gclv_login_url" type="hidden" value="<?= $gclv_login_page -> guid ?>" />

	<div id="g_cal_list_view_app"></div>
	<!-- END APP -->

<?php
	}
	else {
?>

	<h1>We were unable to connect to your Google Calendar</h1>
	<h3>Make sure you are logged-in:
	<a href="admin.php?page=g_cal_list_view_settings">Go to Settings >></a></h3>
	<h4>If the problem persists after you logged-in, please contact your webmaster.</h4>

<?php
	}