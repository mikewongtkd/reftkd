<?php
include_once( '../security.php' );
include_once( '/usr/local/app/api/v1/referee.php' );

function fail( $code ) {
	http_response_code( $code );
	exit();
}

function success( $results = null ) {
	if( $results !== null ) { header( 'Content-Type: application/json; charset=utf-8' ); }
	http_response_code( 200 );
	if( $results !== null ) { echo( json_encode( $results )); }
	exit();
}

if( array_key_exists( 'login', $_GET )) {
	$email = SQLite3::escapeString( $_POST[ 'email' ]);
	$users = new Referee();
	$user  = $users->rest->select( "select * from referee where email='{$email}'" );
	$n     = count( $user );
	if( $n >  1 ) { fail( 500 ); }
	if( $n == 0 ) { fail( 401 ); }

	$user = $user[ 0 ];
	$pwok = password_verify( $_POST[ 'password' ], $user[ 'pwhash' ]);
	if( ! $pwok ) { fail( 401 ); }

	$_SESSION[ 'uuid' ]   = $user[ 'uuid' ];
	$_SESSION[ 'email' ]  = $user[ 'email' ];
	$_SESSION[ 'fname' ]  = $user[ 'fname' ];
	$_SESSION[ 'lname' ]  = $user[ 'lname' ];
	$_SESSION[ 'name' ]   = "{$user[ 'fname' ]} {$user[ 'lname' ]}";
	$_SESSION[ 'role' ]   = $user[ 'role' ];
	success( $user );

} else if( array_key_exists( 'logout', $_GET )) {

	unset( $_SESSION[ 'uuid' ]);
	unset( $_SESSION[ 'email' ]);
	unset( $_SESSION[ 'fname' ]);
	unset( $_SESSION[ 'lname' ]);
	unset( $_SESSION[ 'name' ]);
	unset( $_SESSION[ 'role' ]);

	success();
}
?>
