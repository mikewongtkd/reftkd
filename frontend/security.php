<?php
	session_name( 'ocular-growth-session' );
	session_save_path( '/usr/local/app/sessions' );
	session_start();
	date_default_timezone_set( 'UTC' );

	include_once( 'security/policy.php' );
	$user = new Security\User();
?>
