<?php

include_once( '/usr/local/app/lib/restful.php' );
include_once( '/usr/local/app/lib/utils.php' );

class Referee extends RESTful {
	function __construct() {
		if( $this->rest === null ) { $this->rest = new REST( 'referee' ); }
	}

	function get( $parameters = null, $uuid = null ) {
		if( $this->rest === null ) { return null; }

		// Typical REST fulfillment
		$users = $this->rest->get( $parameters, $uuid );
		if( Utils::is_assoc_array( $users )) {
			unset( $users[ 'pwhash' ]);
			unset( $users[ 'deleted' ]);
		} else {
			foreach( $users as &$user ) {
				unset( $user[ 'pwhash' ]);
				unset( $user[ 'deleted' ]);
			}
		}
		return $users;
	}


	function patch( $data, $uuid = null ) { 
		if( $this->rest === null ) { return null; }
		if( $uuid ) {
			if( isset( $data[ 'password' ])) { $data[ 'pwhash' ] = password_hash( $data[ 'password' ], PASSWORD_DEFAULT); unset( $data[ 'password' ]); }
			$this->rest->patch( $data, $uuid );

		} else if( $uuid === null && $this->data === null ) {
			if( is_array( $data ) && isset( $data[ 'uuid' ])) { 
				if( isset( $data[ 'password' ])) { $data[ 'pwhash' ] = password_hash( $data[ 'password' ], PASSWORD_DEFAULT); unset( $data[ 'password' ]); }
				$this->rest->patch( $data, $data[ 'uuid' ]); 
			} 
			else { die( "No UUID specified for patching" ); }

		} else {
			if( ! is_array( $this->data ))       { die( "Data incomplete for patching" ); }
			if( ! isset( $this->data[ 'uuid' ])) { die( "No UUID specified for patching" ); }
			if( isset( $this->data[ 'password' ])) { $this->data[ 'pwhash' ] = password_hash( $this->data[ 'password' ], PASSWORD_DEFAULT); unset( $data[ 'password' ]); }
			$this->rest->patch( $this->data, $data[ 'uuid' ]);
		}
	}

	function post( $data ) { 
		if( $this->rest === null ) { return null; }
		if( $this->data === null ) {
			if( is_array( $data )) { 
				$uuid = isset( $data[ 'uuid' ]) ? $data[ 'uuid' ] : null;
				if( isset( $data[ 'password' ])) { $data[ 'pwhash' ] = password_hash( $data[ 'password' ], PASSWORD_DEFAULT); unset( $data[ 'password' ]); }
				$this->rest->post( $data, $uuid ); 
			}
		} else {
			if( ! is_array( $this->data )) { die( "Data incomplete for posting" ); }
			$uuid = is_array( $data ) && isset( $data[ 'uuid' ]) ? $data[ 'uuid' ] : null;
			if( isset( $this->data[ 'password' ])) { $this->data[ 'pwhash' ] = password_hash( $this->data[ 'password' ], PASSWORD_DEFAULT); unset( $data[ 'password' ]); }
			$this->rest->post( $this->data, $uuid );
		}
	}
}

?>
