<?php
	class DB {
		public const FILE = '/usr/local/app/data/db.sqlite';
		private $uuid_managed;
		private static $db = null;

		function __construct( $uuid_managed = true ) {
			$this->uuid_managed = $uuid_managed;

			# Singleton connection instance
			if( self::$db == null ) { self::$db = new SQLite3( DB::FILE ); }
		}

		private static function flatten( $data ) {
			$flat = [];
			foreach( $data as $key => $value ) {
				# Numeric literal
				if( is_numeric( $value ) ) {
					$flat[ $key ] = $value;

				# Boolean literal gets converted to 0|1 for SQLite3
				} else if( is_bool( $value ) ) {
					$flat[ $key ] = $value ? 1 : 0;

				# String
				} else if( is_string( $value )) {
					$value        = SQLite3::escapeString( $value );
					$flat[ $key ] = "'{$value}'";

				# Array
				} else if( is_array( $value )) {
					$value        = SQLite3::escapeString( json_encode( $value ));
					$flat[ $key ] = "'{$value}'";

				# Object
				} else if( is_object( $value )) {
					$value        = SQLite3::escapeString( json_encode( $value ));
					$flat[ $key ] = "'{$value}'";

				} else {
					$json = json_encode( $data[ $key ]);
					die( "Unknown type for {$json}" );
				}
			}
			return $flat;
		}

		private static function get_uuid( $key ) {
			$seed = 0;
			$uuid = uuid( $key );
			while( ! self::valid_unused_uuid( $uuid )) {
				$new_key = "{$key}-{$seed}";
				$uuid = uuid( $new_key );
				$seed++;
			}
			return $uuid;
		}

		private static function has_uuid( $table ) {
			$rows = self::retrieve( 'sqlite_master', "type='table' and name = '$table'" );
			if( count( $rows ) == 0 ) { die( "Error (DB::has_uuid): Can't find table '$table'" ); }
			$row = $rows[ 0 ];
			$sql = $row[ 'sql' ];
			return preg_match( '/\buuid\b/', $sql );
		}

		private static function is_json( $data ) {
			json_decode( $data, true );
			return ( json_last_error() === JSON_ERROR_NONE );
		}

		private static function parse_create_table( $sql ) {
			$sql    = preg_replace( '/"/', '', $sql );
			$sql    = preg_replace( '/^create\s+table\s+\S+\s+\(\s*\n\t/i', '', $sql );
			$sql    = preg_replace( '/\)$/i', '', $sql );
			$sql    = preg_replace( '/\n$/ms', '', $sql );
			$fields = explode( "\n\t", $sql );
			return $fields;
		}

		private static function parse_create_view( $sql ) {
			// Get the tables from the view
			$tables  = [];
			$sql = preg_replace( '/\s+/ms', ' ', $sql );
			preg_match( '/from\s+(\S+)/i', $sql, $matches );
			$tables[]= $matches[ 1 ];
			preg_match( '/inner\s+join\s+(\S+)/i', $sql, $matches );
			foreach( range( 1, count( $matches ) - 1) as $i ) {
				$tables[] = $matches[ $i ];
			}

			// Get the fields from the view
			preg_match( '/select\s+(.*)\s+from/i', $sql, $matches );
			$fields = $matches[ 1 ];
			$fields = preg_split( '/\s*,\s*/', $fields );

			// Get the schema from the tables
			$schemas = [];
			foreach( $tables as $table ) {
				$schemas[ $table ] = DB::schema( $table );
			}

			// Match the fields with the table schemas
			foreach( $fields as &$field ) {
				foreach( array_keys( $schemas ) as $table ) {
					foreach( $schemas[ $table ] as $schema ) {
						$fieldname  = $schema[ 'name' ];
						$fieldtype  = $schema[ 'type' ];
						if( preg_match( "/^$fieldname/", $field )) {
							$field = "$fieldname $fieldtype";

						} else if( preg_match( "/^$table.$fieldname/", $field )) {
							if( preg_match( '/\s+as\s+(\S+)/', $field, $matches )) {
								$alias = $matches[ 1 ];
								$field = "$alias $fieldtype";
							}
						}
					}
				}
			}
			return $fields;
		}

		private static function retrieve( $table, $conditions = null ) {
			$where   = $conditions ? "where {$conditions}" : $conditions;
			$rows    = self::$db->query( "select * from {$table} {$where};" );
			$results = [];

			while( $row = $rows->fetchArray( SQLITE3_ASSOC )) {
				# Unflatten valid JSON strings
				foreach( $row as $key => $value ) {
					if( DB::is_json( $value )) {
						$row[ $key ] = json_decode( $value, true );
					}
				}
				array_push( $results, $row );
			}
			return $results;
		}

		private static function schema( $table ) {
			$rows    = self::$db->query( "PRAGMA table_info( '{$table}' );" );
			$columns = [];
			while( $row = $rows->fetchArray( SQLITE3_ASSOC )) {
				array_push( $columns, $row );
			}
			if( count( $columns ) == 0 ) {
				die( "Error (DB::schema): table '$table' does not exist" );
			}

			return $columns;
		}

		private static function tables() {
			$rows   = self::retrieve( 'sqlite_master', "type='table'" );
			$tables = array_map( function ( $entry ) { return $entry[ 'name' ]; }, $rows );
			return $tables;
		}

		private static function valid_unused_uuid( $uuid ) {
			# Check for uniqueness across all tables in the database
			$tables = self::tables();
			$rows   = [];
			$valid  = preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid );
			if( ! $valid ) return false;

			$unused = true;
			foreach( $tables as $table ) {
				if( ! self::has_uuid( $table )) { continue; }
				$found = self::retrieve( $table, "uuid='{$uuid}'" );
				if( count( $found ) > 0 ) { $unused = false; }
			}

			return $unused;
		}

		function create( $table, $data, $uuid = null ) {
			return $this->insert( $table, $data, $uuid );
		}

		function delete( $table, $uuid ) {
			$found = $this->find( $table, $uuid );
			if( $found === false ) { die( "Error (DB::delete): UUID '{$uuid}' not found in table '{$table}'" ); }

			$soft = $this->has_soft_delete( $table );

			if( $soft ) {
				self::$db->exec( "update {$table} set deleted=date('now') where uuid='{$uuid}'" );
			} else {
				self::$db->exec( "delete from {$table} where uuid='{$uuid}'" );
			}

			if( self::$db->lastErrorCode()) { die( self::$db->lastErrorMessage()); }
		}

		function fields( $table ) {
			$schema = $this->query( 'sqlite_master', "tbl_name='{$table}'" );
			$sql    = $schema[ 0 ][ 'sql' ];
			if( preg_match( '/^create\s+table/i', $sql )) {
				$fields = DB::parse_create_table( $sql );

			} else if( preg_match( '/^create\s+view/i', $sql )) {
				$fields = DB::parse_create_view( $sql );
			}

			$types  = [];
			foreach( $fields as $field ) {
				$type    = explode( ' ', $field );
				$entry   = [ 'name' => $type[ 0 ], 'type' => $type[ 1 ]];
				if( preg_match( '/(?:char|varchar|text)/i', $entry[ 'type' ]))                             { $entry[ 'type' ] = 'text';   }
				if( preg_match( '/(?:integer|int|smallint|tinyint|mediumint|bigint)/i', $entry[ 'type' ])) { $entry[ 'type' ] = 'int';   }
				if( preg_match( '/(?:decimal|numeric|float|double)/i', $entry[ 'type' ]))                  { $entry[ 'type' ] = 'float'; }
				if( preg_match( '/(?:int|float)/i', $entry[ 'type' ])) {
					$min = $this->select( "select min({$entry[ 'name' ]}) from {$table};" );
					$max = $this->select( "select max({$entry[ 'name' ]}) from {$table};" );
					$entry[ 'min' ] = $min[ 0 ][ "min({$entry[ 'name' ]})" ];
					$entry[ 'max' ] = $max[ 0 ][ "max({$entry[ 'name' ]})" ];
					$entry[ 'step' ] = 0.01;
				}
				$types[] = $entry;
			}
			return $types;

		}

		function find( $table, $parameters = null ) {
			$soft = $this->has_soft_delete( $table );
			if( $parameters === null || (is_string( $parameters ) && $parameters == '')) {
				if( $soft ) { $rows = $this->query( $table, "deleted is null" ); } 
				else        { $rows = $this->query( $table ); }
				return $rows;

			} else if( is_string( $parameters )) {
				$uuid = strtolower( $parameters );
				// UUID
				if( preg_match( '/^[0-9a-f]{8}\-(?:[0-9a-f]{4}\-){3}[0-9a-f]{12}$/', $uuid )) {
					if( $soft ) { $rows = $this->query( $table, "uuid='{$uuid}' and deleted is null" ); }
					else        { $rows = $this->query( $table, "uuid='{$uuid}'" ); }
					if( count( $rows ) == 0 ) { return false; }
					if( count( $rows ) > 1  ) { die( "Error (DB::select): Multiple entries found for UUID '{$uuid}' in table {$table}" ); }

					return $rows[ 0 ];

				// Query string
				} else {
					if( $soft && ! preg_match( '/deleted/', $parameters )) { $parameters .= ' and deleted is null'; }
					$rows = $this->query( $table, $parameters );
					return $rows;
				}
			}
		}

		function has_soft_delete( $table ) {
			$fields      = DB::fields( $table );
			$soft_delete = count( array_filter( $fields, function ( $x ) { return $x[ 'name' ] == 'deleted'; }));
			return $soft_delete;
		}

		function insert( $table, $data, $uuid = null ) {
			if( $uuid === null && ! $this->uuid_managed ) { die( "Error (DB::insert): No UUID specified, yet UUIDs are to be managed by the application" ); }

			if( self::valid_unused_uuid( $uuid )) {
				$data[ 'uuid' ] = $uuid;

			} else if( $this->uuid_managed ) {
				$rowid = $this->next_rowid( $table );
				$key   = "{$table}-{$rowid}";
				$uuid  = self::get_uuid( $key );
				$data[ 'uuid' ] = $uuid;

			} else {
				die( "Error (DB::insert): UUID '$uuid' invalid" );
			}

			# DB Insert Error Checking
			$data    = self::flatten( $data );
			$schema  = self::schema( $table );
			$columns = array_map( function( $column ) { return $column[ 'name' ]; }, $schema );

			foreach( $data as $key => &$value ) {
				# Column verification
				$i = array_search( $key, $columns );
				if( $i === false ) { die( "Error (DB::insert): Trying to insert data for column {$table}.{$key}, but the column does not exist" ); }

				$column = $columns[ $i ];

				# Type checking
				$type = $column[ 'type' ];
				if( $type == 'text' ) {
					if( ! is_string( $value )) { $value = SQLite3::escapeString( $value ); $value = "'{$value}'"; }

				} else if( $type == 'integer' ) {
					if( ! is_numeric( $value )) { die( "Error ( DB::insert ): Attempting to insert non-numeric '{$value}' as an integer in '{$table}.{$key}'" ); }
					$value = intval( $value );

				} else if( $type == 'real' || $type == 'numeric' ) {
					if( ! is_numeric( $value )) { die( "Error ( DB::insert ): Attempting to insert non-numeric '{$value}' as an number in '{$table}.{$key}'" ); }
					$value = floatval( $value );
				}
			}

			$columns = implode( ', ', array_keys( $data ));
			$values  = implode( ', ', array_values( $data ));
			
			self::$db->exec( "insert into {$table} ({$columns}) values ({$values});" );
			if( self::$db->lastErrorCode()) { die( self::$db->lastErrorMessage()); }

			return $this->find( $table, $uuid );
		}

		function json_query( $table, $path, $conditions ) {
			$where    = "where json_extract( data, '{$path}' ) {$conditions}";
			$rows     = self::$db->query( "select * from {$table} {$where};" );
			$results  = [];

			while( $row = $rows->fetchArray( SQLITE3_ASSOC )) {
				# Unflatten valid JSON strings
				foreach( $row as $key => $value ) {
					if( DB::is_json( $value )) {
						$row[ $key ] = json_decode( $value, true );
					}
				}
				array_push( $results, $row );
			}
			return $results;
		}

		function next_rowid( $table ) {
			$rows    = self::$db->query( "select max( rowid ) from {$table};" );
			$results = [];
			while( $row = $rows->fetchArray( SQLITE3_ASSOC )) {
				array_push( $results, $row );
			}
			return intval( $results[ 0 ][ 'rowid' ]) + 1;
		}

		function query( $table, $conditions = null ) {
			return self::retrieve( $table, $conditions );
		}

		function select( $sql ) {
			$rows    = self::$db->query( $sql );
			$results = [];
			if( $rows === false ) { die( "Error in query {$sql}: " . self::$db->lastErrorMsg()); }

			while( $row = $rows->fetchArray( SQLITE3_ASSOC )) {
				# Skip deleted fields
				if( isset( $row[ 'deleted' ]) && $row[ 'deleted' ]) { continue; }

				# Unflatten valid JSON strings
				foreach( $row as $key => $value ) {
					if( ! DB::is_json( $value )) { continue; }
					$row[ $key ] = json_decode( $value, true );
				}
				$results []= $row;
			}
			return $results;

		}

		function update( $table, $data, $uuid ) {
			if( $uuid === null ) { die( "UUID is required for update operation\n" ); }

			$exists = $this->find( $table, $uuid );
			if( ! $exists ) { die( "Object with UUID {$uuid} does not exist for updating.\n" ); }

			$flat   = DB::flatten( $data );
			$update = [];
			foreach( $flat as $key => $value ) {
				array_push( $update, "{$key}={$value}" );
			}
			$update = implode( ', ', $update );
			self::$db->exec( "update {$table} set {$update} where uuid='{$uuid}'" );
			if( self::$db->lastErrorCode()) { die( self::$db->lastErrorMessage()); }
		}
	}
?>
