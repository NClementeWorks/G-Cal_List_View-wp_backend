<?php

/*
Google Calendar List View
Author: Noliani Clemente Hernández
*/

require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/g_cal_list_view_client.php';
		
$api_base = 'gclv/v1/';
$client = new G_Cal_List_View_Client ();
$client -> init_client ();


/**
 * Add JSON API routes
 */
add_action( 'rest_api_init', function () use ( $api_base, $client ) {
	
	register_rest_route (
		$api_base,
		'auth_token',
		[
			'methods'  => WP_REST_SERVER::READABLE, // GET
			'callback' => function ( WP_REST_Request $req ) use ( $client ) {
				
				$refresh_token_header = $req -> get_header ( 'GCLV-RTK' );
				$redirect = $req -> get_header ( 'GCLV-RD_URL' );
				
				$result = g_cal_list_view_do_refresh_token (
					$req,
					$client,
					$refresh_token_header,
					$redirect
				);
				// return array_merge ( $result, array ( 'custom_header' => $refresh_token_header ) );
				return $result;
				
			},
			'permission_callback' => '__return_true',
		]
	);
	
	register_rest_route (
		$api_base,
		'auth_url',
		[
			'methods'  => WP_REST_SERVER::READABLE, // GET
			'callback' => function ( WP_REST_Request $req ) use ( $client ) {
				
				$result = g_cal_list_view_get_auth_url ( $req, $client );
				return $result;
				
			},
			'permission_callback' => '__return_true',
		]
	);
});



		
/**
 * Get Google Authentication Token from Refresh Token
 */
function g_cal_list_view_do_refresh_token ( WP_REST_Request $req, $client, $refresh_token_header, $redirect ) {
	
	if ( isset ( $refresh_token_header ) && $refresh_token_header !== null ) {

		$client -> init_client ( $redirect );
		
		if ( $refresh_token_header === '_DB' ) {
			global $wpdb;
		
			$refresh_token_res = $wpdb -> get_row (
				$wpdb -> prepare ( "SELECT g.setting_value FROM " . $wpdb -> prefix . "g_cal_list_view g WHERE g.setting_name = 'gclient_rtk'" )
			);
			
			if ( isset ( $refresh_token_res ) )
				$refresh_token = $refresh_token_res -> setting_value;
		}
		
		else {
			$refresh_token = $refresh_token_header;
		}
	
		if ( isset ( $refresh_token ) && $refresh_token !== null && strlen ( $refresh_token ) > 0 ) {

			try {
				$auth_token = $client -> get_new_auth_token ( $refresh_token );
			}
			catch ( Exception $err ) {
				return array ( 'status' => 500, 'error' => $err );
			}
			
			if ( isset ( $auth_token ) && strlen ( trim ( $auth_token ) ) > 0 ) {
				return array ( 'status' => 200, 'token' => $auth_token );
			}
			
			return array ( 'status' => 500, 'error' => 'Unable to generate auth token' );
		}
		
		return array ( 'status' => 404, 'error' => 'No refresh token found' );
	}
	
	return array ( 'status' => 403, 'error' => 'Forbidden' );
}



function g_cal_list_view_get_auth_url ( $req, $client ) {
	
	$client -> set_session_state ();
	$auth_url = $client -> createAuthUrl ();
	
	return array ( 'status' => 200, 'url' => $auth_url );
	
}