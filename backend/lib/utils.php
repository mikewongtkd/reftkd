<?php
class Utils {
	static function is_assoc_array( array $arr ) {
		if( array() === $arr ) return false;
		return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
	}
}
?>
