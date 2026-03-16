<?php

/*
Google Calendar List View
Author: Noliani Clemente Hernández
*/

/**
 * Add JSON API routes
 */
add_action( 'rest_api_init', function () {
	
	register_rest_route (
		'gclv/v1/',
		'auth_token',
		[
			'methods'  => WP_REST_SERVER::READABLE, // GET
			'callback' => function ( WP_REST_Request $req ) {
				
				$result = g_cal_list_view_do_refresh_token ( $req );
				return $result;
				
			},
			'permission_callback' => '__return_true',
		]
	);
});

/**
 * Get Google Authentication Token from Refresh Token
 */
function g_cal_list_view_do_refresh_token ( WP_REST_Request $req ) {
	global $wpdb;
	
	$refresh_token_res = $wpdb -> get_row (
		$wpdb -> prepare ( "SELECT g.setting_value FROM " . $wpdb->prefix . "g_cal_list_view g WHERE g.setting_name = 'gclient_rtk'")
	);	
	
	if ( isset ( $refresh_token_res ) && strlen ( $refresh_token_res -> setting_value ) > 0 ) {
		require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/g_cal_list_view_client.php';
		
		$client = new G_Cal_List_View_Client ();
		$client -> init_client ();

		$refresh_token = $refresh_token_res -> setting_value;
		$auth_token = $client -> get_new_auth_token ( $refresh_token );
		if ( isset ( $auth_token ) && strlen ( trim ( $auth_token ) ) > 0 ) {
			return array ( 'status' => 200, 'token' => $auth_token );
		}
		return array ( 'status' => 404, 'error' => 'No token' );
		
	}
}