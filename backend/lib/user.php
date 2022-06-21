<?php

class User {
	public $uuid;
	public $id;
	public $center;
	public $roles;

	function __construct() {
		$this->uuid   = User::_session( 'uuid' );
		$roles        = User::_session( 'roles' );
		if( $roles ) { $this->roles = json_decode( $roles, true ); }
		else         { $this->roles = null; }
	}

	private static function _session( $key ) {
		if( isset( $_SESSION[ $key ])) { return $_SESSION[ $key ]; }
		return null;
	}
};

?>
