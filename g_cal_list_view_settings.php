<?php

/*
Google Calendar List View
Author: Noliani Clemente Hernández
*/

defined('ABSPATH') or die("you do not have access to this page!");

require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/autoload.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/g_cal_list_view_client.php';

$client = new G_Cal_List_View_Client ();
$redirect = $_SERVER[ 'REQUEST_SCHEME' ] . '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ] . '?page=g_cal_list_view_settings';
// echo $redirect;
$client -> init_client ( $redirect );

global $wpdb;

//
// Log out
//
if ( isset ( $_GET [ 'logout' ] ) ) {
	g_calendar_list_view_save_setting ( 'gclient_rtk', null );
}

$refresh_token_res = $wpdb -> get_row (
	$wpdb -> prepare ( "SELECT g.setting_value FROM " . $wpdb->prefix . "g_cal_list_view g WHERE g.setting_name = 'gclient_rtk'")
);

if ( isset ( $refresh_token_res ) && strlen ( $refresh_token_res -> setting_value ) > 0 ) {
	$refresh_token = $refresh_token_res -> setting_value;
	$auth_token = $client -> get_new_auth_token ( $refresh_token );
}

if ( isset ( $auth_token ) && strlen ( $auth_token ) > 0 ) {
}
else if (!isset($_GET['code']) && !isset($_GET['error']))
{
	$client -> set_session_state ();
  $auth_url = $client -> createAuthUrl ();
}

// Get refresh token from Google Client code
/*  https://127.0.0.1/edsa-NC/wp-admin/admin.php
		page=g_cal_list_view_settings
		state=...
		code=...
		scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fcalendar.readonly
*/
// and store refresh token in DB
else if ( isset ( $_GET[ 'code' ] ) ) {
	$accessToken = $client -> laod_access_token ( $_GET['code'] );
	g_calendar_list_view_save_setting ( 'gclient_rtk', $accessToken[ "refresh_token" ] );
}
else if ( !isset ( $_GET[ 'code' ] ) && !isset ( $_GET[ 'error' ] ) ) {
}
?>
	<style>
.log_btn {
    font-size: 1.5rem;
    padding: .5rem 1rem;
    padding-top: .25rem;
    margin: 1rem .5rem;
}
	</style>
	<div class="wrap">
		<h2><strong>My Google Calendar List View: Settings</strong></h2>
		
<?php

	if ( isset ( $auth_token ) && strlen ( $auth_token ) > 0 ) {
	?>
		<h1>HOORAY!! You are logged in</h1>
		<h3><a href="admin.php?page=g_cal_list_view">View Your Calendar Events >></a></h3>
		<button class="log_btn" onclick="logout_google_client()">
			Logout
		</button>
		<script type="text/javascript">
		function logout_google_client () {
			window.location.href="<?= get_site_url () ?>/wp-admin/admin.php?page=g_cal_list_view_settings&logout=true";
		}
		</script>
<?php 
	} 
	else if (!isset($_GET['code']) && !isset($_GET['error'])) {
	?>
		<h2>You are NOT logged in</h2>
		<button class="log_btn" onclick="open_login_popup()">
			Login to Google Calendar
		</button>
		<script type="text/javascript">
		function open_login_popup () {
			window.open('<?= filter_var ( $auth_url, FILTER_SANITIZE_URL ) ?>', 'Google Authentication','height=600,width=400')
			if ( window.focus ) { newwindow.focus() }
		}
		</script>
<?php 
	} 
	else if ( isset ( $_GET[ 'code' ] )) {
	?>
		<h2>Redirecting...</h2>
		<script type="text/javascript">
			window.opener.location.href="<?= get_site_url () ?>/wp-admin/admin.php?page=g_cal_list_view_settings&token=true";
			self.close();
		</script>
<?php 
	}
	else {
		?>
		<h2>We are sorry, there was an error logging you in. Please try again later.</h2>
		<h3>Thank you for your patience</h3>
		<h4>If the problem persists, please contact your webmaster.</h4>
<?php
	}
	?>
	</div>
	