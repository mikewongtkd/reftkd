<?php

/*
Autoload:
	https://www.php.net/manual/en/language.oop5.autoload.php
*/
spl_autoload_register( function ( $class ) {
	$path  = preg_replace( '/\\/', '/', $class );
	$found = null;
	$files = [ "$path.php", "lib/$path.php", "include/$path.php", "include/php/$path.php", "include/app/$path.php", "/var/www/html/$path.php", "/var/www/html/lib/$path.php", "/var/www/html/include/$path.php", "/var/www/html/include/php/$path.php", "/var/www/html/include/app/$path.php" ];
	foreach( $files as $file ) {
		if( ! file_exists( $file )) { continue; }
		$found = file;
		break;
	}
	if( ! $found ) {
		die "Data model file not found! $!";
	}
	include( $found );
});

namespace OpenTKD;

/* https://book.cakephp.org/4/en/core-libraries/inflector.html */
include_once( "../../include/php/Cake/Utility/Inflector.php" );
use Cake\Utility\Inflector as Inflector;

/*
Overloading:
	https://www.php.net/manual/en/language.oop5.overloading.php

Dynamic method calling
	https://stackoverflow.com/questions/251485/dynamic-class-method-invocation-in-php

Reflection
	https://www.php.net/manual/en/class.reflection.php
*/

class DBO {
	private $type;
	private $data;
	private $created;
	private $modified;
	private $deleted;
	private static $datamodel = OpenTKD\DBO::getDataModel();

	# ============================================================
	__construct( $doc ) {
	# ============================================================
		$this->uuid     = $doc[ 'uuid' ];
		$this->type     = $doc[ 'type' ];
		$this->data     = json_decode( $doc[ 'data' ], true );
		$this->created  = $doc[ 'created' ];
		$this->modified = $doc[ 'modified' ];
		$this->deleted  = $doc[ 'deleted' ];
	}

	/*
		Dynamically instantiating an object
		https://stackoverflow.com/questions/41825895/php-how-to-dynamically-instantiate-a-class
	*/
	# ============================================================
	public static function factory( $doc ) {
	# ============================================================
		if( ! is_array( OpenTKD\DBO::$datamodel	)) {
			die "Malformed Data Model $!";
		}

		if( ! array_key_exists( $doc[ 'type' ], $datamodel )) {
			return null;
		}
		$type  = $doc[ 'type' ];
		$class = $datamodel[ $type ];

		print "DEBUG: Instantiating $class from document type $type\n";

		return new $class( $doc );
	}

	# ============================================================
	public static function get( $uuid, $deleted = false ) {
	# ============================================================
	}

	# ============================================================
	public static function getDataModel() {
	# ============================================================
		$files = [ '/usr/local/app/datamodel.json', '/usr/local/app/data/model.json' ];
		$found = null;
		foreach( $files as $file ) {
			if( ! file_exists( $file )) { continue; }
			$found = file;
			break;
		}
		if( ! $found ) {
			die "Data model file not found! $!";
		}
		$text = file_get_contents( $found );
		return json_decode( $text, true );
	}

	# ============================================================
	public static function is_uuid( $UUID ) {
	# ============================================================
		if( ! is_string( $UUID )) { return false; }
		$uuid = strtolower( $UUID );
		return preg_match( '/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/', $uuid ) ? true : false;
	}

	# ============================================================
	public function __get( $names ) {
	# ============================================================
		if( ! is_array( $data )) {
			die "500 Server Error: Malformed data object $!";
		}
		$name = Inflector::singularize( $names );
		$plural = $name != $names;

		if( $name == 'uuid'     ) { return $this->uuid; }
		if( $name == 'type'     ) { return $this->type; }
		if( $name == 'created'  ) { return $this->created; }
		if( $name == 'modified' ) { return $this->modified; }
		if( $name == 'deleted'  ) { return $this->deleted; }
		
		if( ! array_key_exists( $data, $name )) {
			return null;
		}

		if( $plural ) {
			if( is_array( $data[ $name ] )) {
				if( array_is_list( $data[ $name ])) {
					$results = array_map( function ( $x ) {
						if( OpenTKD\DBO::is_uuid( $x )) {
							return OpenTKD\DBO::get( $x );
						} else {
							return $x;
						}
					}, $data[ $name ]);
				} else {
					if( OpenTKD\DBO::is_uuid( $x )) {
						return [ OpenTKD\DBO::get( $x )];
					} else {
						return [ $x ];
					}
				}
			}
		}
	}
}
?>
