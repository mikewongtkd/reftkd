<?php
include_once( '../security.php' );
include_once( '/usr/local/app/api/v1/users.php' );

function fail( $code ) {
	http_response_code( $code );
	exit();
}

function success() {
	http_response_code( 200 );
	exit();
}

if( array_key_exists( 'login', $_GET )) {
	$id     = SQLite3::escapeString( $_POST[ 'id' ]);

	preg_match( '/^(\w*[A-Za-z])(\d{5})$/', $id, $match );
	$center = strtoupper( $match[ 1 ]);
	$id     = $match[ 2 ];

	$users = new User();
	$user  = $users->rest->select( "select * from user where center='{$center}' and id='{$id}'" );
	$n     = count( $user );
	if( $n >  1 ) { fail( 500 ); }
	if( $n == 0 ) { fail( 401 ); }

	$user = $user[ 0 ];
	$pwok = password_verify( $_POST[ 'password' ], $user[ 'password' ]);
	if( ! $pwok ) { fail( 401 ); }

	$_SESSION[ 'uuid' ]   = $user[ 'uuid' ];
	$_SESSION[ 'center' ] = strtoupper( $user[ 'center' ]);
	$_SESSION[ 'id' ]     = $user[ 'id' ];
	$_SESSION[ 'role' ]   = $user[ 'role' ];
	success();

} else if( array_key_exists( 'logout', $_GET )) {

	unset( $_SESSION[ 'uuid' ]);
	unset( $_SESSION[ 'center' ]);
	unset( $_SESSION[ 'id' ]);
	unset( $_SESSION[ 'role' ]);
	success();
}
?>
