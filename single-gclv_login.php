<?php

/*
 * Template Name: My custom post template
 * Template Post Type: post
 */
 // get_header();
 
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/vendor/autoload.php';
require_once WP_PLUGIN_DIR . $GLOBALS[ 'g_cal_list_view_plugin_folder' ] . '/g_cal_list_view_client.php';

$client = new G_Cal_List_View_Client ();
$client -> init_client ( $_SERVER[ 'REDIRECT_URL' ] );

$token_label = 'gclv_rtk';

?>
	<div class="wrap">
		<h2><strong>My Google Calendar List View: Login</strong></h2>
		
	<?php
	if ( isset ( $_GET[ 'login' ] )) {
		$client -> set_session_state ();
		$auth_url = $client -> createAuthUrl ();
		?>
		<h2>Redirecting to Google login...</h2>
		<script type="text/javascript">
			window.location.href = "<?= filter_var ( $auth_url, FILTER_SANITIZE_URL ) ?>"
		</script>
	<?php 
	}
	else if ( isset ( $_GET[ 'code' ] )) {
		$accessToken = $client -> laod_access_token ();
		?>
		<h2>Finalizing...</h2>
		<script type="text/javascript">
			localStorage.setItem ( '<?= $token_label ?>', '<?= $accessToken [ "refresh_token" ] ?>' )
			self.close ()
		</script>
	<?php 
	}
	else if ( isset ( $_GET [ 'logout' ] ) ) {
		$client -> revokeToken ();
		?>
		<h2>Loging out...</h2>
		<script type="text/javascript">
			localStorage.removeItem ( '<?= $token_label ?>' )
			self.close ()
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
	