<?php
	function uuid( $key ) {
		$key    = $key ? $key : openssl_random_pseudo_bytes( 16 );
		$hash   = substr( sha1( $key ), 0, 32 );
		$hexarr = str_split( $hash );
		$dashes = [ 20, 16, 12, 8 ];
		foreach( $dashes as $i ) {
			array_splice( $hexarr, $i, 0, '-' );
		}
		$uuid   = implode( '', $hexarr );
		return $uuid;
	}
?>
