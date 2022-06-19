<?php
include_once( '/usr/local/app/lib/uuid.php' );
include_once( '/usr/local/app/lib/db.php' );

class REST {
	private $db;
	private $table;

	function __construct( $table ) {
		$this->db    = new DB();
		$this->table = $table;
	}

	// Create
	function post( $data, $uuid = null ) {
		if( $uuid === null ) {
			$this->db->insert( $this->table, $data, $uuid );

		} else {
			$exists = $this->db->find( $this->table, $uuid );
			if( $exists ) { die( "Object with UUID {$uuid} already exists.\n" ); }
			$this->db->insert( $this->table, $data, $uuid );
		}
	}

	// Read
	function get( $parameters = null, $uuid = null ) {
		$no_parameters  = $parameters === null || (is_array( $parameters ) && count( $parameters ) == 0);
		$advanced_query = is_array( $parameters ) && array_key_exists( 'aq', $parameters );

		if( $no_parameters  ) { $parameters = $uuid; } else 
		if( $advanced_query ) { $parameters = REST::parse_advanced_query( $parameters ); }
		else                  { $parameters = REST::parse_query( $parameters ); }

		return $this->db->find( $this->table, $parameters );
	}

	// Update
	function patch( $data, $uuid ) {
		if( $uuid === null ) { die( "UUID required to update object.\n" ); }

		return $this->db->update( $this->table, $data, $uuid );
	}

	// Delete
	function delete( $uuid ) {
		if( $uuid === null ) { die( "UUID required to delete object.\n" ); }

		$this->db->delete( $this->table, $uuid );
	}

	// Retrieves the column names, data types, and if the data is categorical
	function fields() {
		$fields = $this->db->fields( $this->table );
		return json_encode( $fields );

	}

	function select( $sql ) {
		return $this->db->select( $sql );
	}

	function table() {
		return $this->table;
	}

	private static function parse_advanced_query( $parameters ) {
		return convert_uudecode( html_entity_decode( $parameters ));
	}

	private static function parse_query( $parameters ) {
		$conditions = [];
		foreach( $parameters as $field => $comparison_value ) {
			$comparator = null;
			$value      = null;
			
			if( is_array( $comparison_value )) {
				foreach( $comparison_value as $cv ) { $conditions[] = REST::parse_comparator( $field, $cv ); }
			} else {
				$conditions[] = REST::parse_comparator( $field, $comparison_value ); 
			}
		}

		return implode( ' and ', $conditions );
	}

	private static function parse_comparator( $field, $comparison_value ) {
		if( ! preg_match( '/:/', $comparison_value )) { die( "Missing comparator operator" ); }

		list( $comparator, $value ) = explode( ':', $comparison_value, 2 );
		$comparators = [ 'eq' => '=', 'gt' => '>', 'gte' => '>=', 'lt' => '<', 'lte' => '<=', 'ne' => '!=' ];

		if( ! array_key_exists( $comparator, $comparators )) { die( "Unknown comparator operator" ); }

		$c = $comparators[ $comparator ];

		if( is_numeric( $value )) { return "{$field} {$c} {$value}"; }
		else                      { return "{$field} {$c} '{$value}'"; }
	}

}
?>
