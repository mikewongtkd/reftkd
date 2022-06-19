<?php

include_once( '/usr/local/app/lib/rest.php' );

// ============================================================
class RESTful {
// ============================================================
	public $rest = null;

	// ========================================================
	function delete( $uuid ) {
	// ========================================================
		if( $this->rest === null ) { return null; }
		$this->rest->delete( $uuid );
	}

	// ========================================================
	function get( $parameters = null, $uuid = null ) {
	// ========================================================
		if( $this->rest === null ) { return null; }
		return $this->rest->get( $parameters, $uuid );
	}

	// ========================================================
	function patch( $data, $uuid = null ) { 
	// ========================================================
		if( $this->rest === null ) { return null; }
		if( $uuid ) {
			$this->rest->patch( $data, $uuid );

		} else if( $uuid === null && $this->data === null ) {
			if( is_array( $data ) && isset( $data[ 'uuid' ])) { $this->rest->patch( $data, $data[ 'uuid' ]); } 
			else { die( "No UUID specified for patching" ); }

		} else {
			if( ! is_array( $this->data ))       { die( "Data incomplete for patching" ); }
			if( ! isset( $this->data[ 'uuid' ])) { die( "No UUID specified for patching" ); }
			$this->rest->patch( $this->data, $data[ 'uuid' ]);
		}
	}

	// ========================================================
	function post( $data ) { 
	// ========================================================
		if( $this->rest === null ) { return null; }
		if( $this->data === null ) {
			if( is_array( $data )) { 
				$uuid = isset( $data[ 'uuid' ]) ? $data[ 'uuid' ] : null;
				$this->rest->post( $data, $uuid ); 
			}
		} else {
			if( ! is_array( $this->data )) { die( "Data incomplete for posting" ); }
			$uuid = is_array( $data ) && isset( $data[ 'uuid' ]) ? $data[ 'uuid' ] : null;
			$this->rest->post( $this->data, $uuid );
		}
	}
}

?>
