<?php
/*
Plugin Name: Google Calendar List View
Plugin URI: https://nolianiclemente.com/g_cal_list_view
Description: Offers the ability to view and edit your Google Calendars on a spreadsheed-like UI
Version: 0.1
Author: Noliani Clemente Hernández
*/

defined('ABSPATH') or die("you do not have access to this page!");

g_cal_list_view_set_globals ();

function g_cal_list_view_set_globals () {
	$GLOBALS[ 'g_cal_list_view_plugin_folder' ] = '/G_Cal_List_View';
}

/**
 * Activate the plugin.
 */
function g_cal_list_view_init () {
	g_cal_list_view_init_db_tables ();
	g_cal_list_view_add_admin_menus (); 
}
add_action( 'init', 'g_cal_list_view_init' );

function g_cal_list_view_activate () { 
	// Clear the permalinks after the post type has been registered.
	flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'g_cal_list_view_activate' );


//
// Activation functions
//
function g_cal_list_view_init_db_tables () {

	global $wpdb;
	global $charset_collate;
	// global $db_version;
	
	$table_name = $wpdb -> prefix . "g_cal_list_view";
	$charset_collate = $wpdb->get_charset_collate();
	

	if( $wpdb -> get_var ( "SHOW TABLES LIKE '" . $table_name . "'") !=  $table_name ) {  
		$create_sql = "CREATE TABLE " . $table_name . " (
				`ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`setting_name` TEXT NULL DEFAULT NULL,
				`setting_value` TEXT NULL DEFAULT NULL,
				PRIMARY KEY (`ID`)
			)
			$charset_collate
			AUTO_INCREMENT=1;";
			
		require_once ( ABSPATH . "wp-admin/includes/upgrade.php" );
		dbDelta ( $create_sql );
	}

	//register the new table with the wpdb object
	/* if (!isset($wpdb->ratings_fansub))
	{
			$wpdb->ratings_fansub = $table_name;
			//add the shortcut so you can use $wpdb->stats
			$wpdb->tables[] = str_replace($wpdb->prefix, '', $table_name);
	} */

}

require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/g_cal_list_veiw_api.php';

function g_cal_list_view_add_admin_menus () {
	//
	// Add Admin Page for G-Cal List View custom plugin Authentication
	//
	add_action( 'admin_menu', 'g_cal_list_view_admin_menu' );
}
function g_cal_list_view_admin_menu () {
	
	//
	// Create capability to access Google Calendar List View
	$manage_gclv_calendar_capability = 'manage_gclv_calendar';
	//
	// Create role to access Google Calendar List View
	add_role ( 'gclv_manager', 'Google Calendar List View Manager', array (
		'read',
		$manage_gclv_calendar_capability,
	) );

	//
	// Add capability to access Google Calendar List View to Admin role
	get_role ( 'administrator' )->add_cap ( $manage_gclv_calendar_capability );
	
	add_menu_page (
		'G-Cal List View', // page title
		'G-Cal List View', // menu title
		// 'manage_options', // capability (access level)
		$manage_gclv_calendar_capability, // capability (access level)
		'g_cal_list_view', // menu slug
		'g_cal_list_view_admin_page', // callback function
		'dashicons-calendar', // icon
		6 // menu position
	);
	
	add_submenu_page (
		'g_cal_list_view', // parent
		'Settings', // page title
		'Settings', // menu title
		// 'manage_options', // capability (access level)
		$manage_gclv_calendar_capability, // capability (access level)
		'g_cal_list_view_settings', // menu slug
		'g_cal_list_view_settings_page' // callback function
	);
}

function g_cal_list_view_admin_page () {
	require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/g_cal_list_view_main.php';
}

function g_cal_list_view_settings_page () {
	require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/g_cal_list_view_settings.php';
}

add_action ( 'admin_enqueue_scripts', 'enqueue_g_cal_list_view_scripts' );
function enqueue_g_cal_list_view_scripts ( $hook ) {
 	if ( 'toplevel_page_g_cal_list_view' != $hook ) {
			return;
	}

	// if (function_exists('wp_enqueue_media')) {
		// wp_enqueue_media();
 	// }
	// echo get_site_url();
	$plugin_path = '/wp-content/plugins/G_Cal_List_View';
	$app_dist_path = '/assets';
	$handle_base = 'g_cal_list_view';
	
	// add_stylesheet (
		// $handle_base . '_fontawesome',
		// 'https://use.fontawesome.com/releases/v6.4.0/css/all.css'
	// );
	
	wp_enqueue_style (
		$handle_base . '_style_app', // handle
		// 'http://localhost:5173/src/assets/main.css', // source
		get_site_url() . $plugin_path . $app_dist_path . '/index-fjhaXE_k.css',
		[], // dependencies
		'1.0', // version
	);
	
	wp_enqueue_script_module (
		$handle_base . '_script_app[module]', // handle
		'http://localhost:5173/src/main.js', // source
		// get_site_url() . $plugin_path . $app_dist_path . '/index-B0jG-f3h.js', // source
		[], // dependencies
		'1.0', // version
		array ( 'in_footer' => true ) // in footer
	);
}



/**
 * Deactivation hook
 */
function g_cal_list_view_deactivate () {
	g_cal_list_view_clear_db_data ();
	//
	// Clear Google Calendar List View manager role
	remove_role ( 'gclv_manager' );
	//
	// Remove capability to access Google Calendar List View from Admin role
	get_role ( 'administrator' )->remove_cap ( $manage_gclv_calendar_capability );
	
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'g_cal_list_view_deactivate' );

//
// Deactivation functions
//
function g_cal_list_view_clear_db_data () {
	g_calendar_list_view_save_setting ( 'gclient_rtk', null );
}


/**
 * Helper functions
 */
function g_calendar_list_view_save_setting ( $setting_name, $setting_value ) {
	
	global $wpdb;
	
	//
	// validate setting row in DB
  $setting_ID = $wpdb -> get_row (
		$wpdb -> prepare ( "SELECT g.ID FROM " . $wpdb -> prefix . "g_cal_list_view g WHERE g.setting_name = '" . $setting_name . "'")
	);
	
	$setting_data = array (
		"setting_name" => $setting_name,
		"setting_value" => $setting_value,
	);
	
	//
	// if a row for the setting exists, override its value
	if ( isset ( $setting_ID )) {
		$res = $wpdb -> update ( $wpdb -> prefix . "g_cal_list_view",
			$setting_data,
			array (
				"ID" => $setting_ID -> ID,
			)
		);
	}
	//
	// if a row for the setting does NOT exist, create a new row for it
	else {
		$res = $wpdb -> insert ( $wpdb -> prefix . "g_cal_list_view",
			$setting_data,
		);
	}

}

/*
 * Add custom page to call the Google login publicly
 */
function register_gclv_login_page () {
	$labels = [
		"name" => __( "gclv_login", "gclv_login" ),
		"singular_name" => __( "gclv_login", "gclv_login" ),
		"menu_name" => __( "gclv_login", "gclv_login" ),
	];
	$args = [
		"label" => __( "gclv_login", "gclv_login" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => false,
		"show_in_rest" => false,
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => false, 
		"show_in_nav_menus" => false,
		"delete_with_user" => false,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => false,
		"query_var" => true,
		"supports" => [],
	];
	register_post_type ( "gclv_login", $args );
	
	$gclv_login_page = get_page_by_path ( 'gclv_login', OBJECT, 'gclv_login' );

	if ( !isset ( $gclv_login_page ) ) {
		wp_insert_post ( array (
			'post_status' => 'publish',
			'post_type' => 'gclv_login',
			'post_name' => 'gclv_login',
		) );
	}
}
add_action('init', 'register_gclv_login_page');

/*
 * Set Page templates for "gclv_login"
 */
add_filter( 'template_include', 'gclv_login_template' );
function gclv_login_template ( $template ) {
	return plugin_dir_path(__DIR__) . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . "\single-gclv_login.php";

/**
 * Add Shortcode
 */
add_shortcode ( 'google_calendar_list_view', 'gclv_shortcode' );
function gclv_shortcode ( $_atts = [] ) {
	
	ob_start();
	add_action ( 'wp_enqueue_scripts', 'enqueue_g_cal_list_view_scripts' );
	require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/g_cal_list_view_main.php';
	return ob_get_clean();
	
}