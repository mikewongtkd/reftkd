<?php
	include_once( '/usr/local/app/lib/db.php' );
	include_once( '/usr/local/app/lib/utils.php' );
	include_once( '../security.php' );
	include_once( '../security/policy.php' );

	// ============================================================
	// RESTful API
	// ============================================================
	// Note that this page implements the RESTful API frontend
	// interface. The definition and version of the API is found
	// in the backend under app/api/vn/ where vn is the version
	// number (e.g. v1) of the interface
	// ------------------------------------------------------------

	$user = new Security\User();

	// Extend the Standard PHP Library (SPL) to enable autoloading classes
	spl_autoload_register( function ( $class_name ) { include_once "/usr/local/app/api/{$class_name}.php"; });

	list( $version, $class, $uuid ) = parse_http_request();
	$object = factory_instantiate( $class, $version );

	send_rest_response( $user, $class, $object, $uuid );
	exit();

	// ============================================================
	function fail( $code ) {
	// ============================================================
		http_response_code( $code );
		exit();
	}

	// ============================================================
	function success( $code = 200 ) {
	// ============================================================
		http_response_code( $code );
		exit();
	}

	// ============================================================
	function parse_http_request() {
	// ============================================================
	// Parse the HTTP Request to identify which class is requested
	// ------------------------------------------------------------
		$paths = explode( '/', $_SERVER[ 'REQUEST_URI' ]);
		array_shift( $paths ); # Empty path, since URI starts with /
		$api     = array_shift( $paths ); # api
		$version = array_shift( $paths ); # version
		$class   = array_shift( $paths ); # class definition filename
		$uuid    = null;

		if( ! preg_match( '/^api$/',  $api     )) { fail( 400 ); }
		if( ! preg_match( '/^v\d+$/', $version )) { fail( 400 ); }

		// Clear out GET parameters for class name
		$class = preg_replace( '/\?.*$/', '', $class );

		// If there is one additional argument, it's a UUID
		if( count( $paths ) == 1 ) {
			$uuid = array_shift( $paths );

		// If there are more than one remaining arguments, the request is malformed
		} else if( count( $paths ) > 1 ) { fail( 400 ); }
		return [ $version, $class, $uuid ];
	}

	// ============================================================
	function factory_instantiate( $class, $version ) {
	// ============================================================
		$classes = array_filter( scandir( "/usr/local/app/api/{$version}" ), function ( $item ) { return preg_match( '/\.php$/i', $item ); });
		$classes = array_map( function( $item ) { return preg_replace( '/\.php$/i', '', $item ); }, $classes );
		$classes = array_values( $classes );

		if( ! in_array( $class, $classes )) { fail( 404 ); }

		include_once( "/usr/local/app/api/{$version}/{$class}.php" );
		$classes = get_declared_classes();
		$object  = null;
		foreach( $classes as $candidate ) {
			$reflector = new ReflectionClass( $candidate );
			$filename  = $reflector->getFileName();
			if( ! $filename ) { continue; } // Built-in classes do not have a filename

			if( $filename == "/usr/local/app/api/{$version}/{$class}.php" ) {
				$factory = $candidate;
				$object  = new $factory();
			}
		}

		return $object;
	}

	// ============================================================
	function send_rest_response( $user, $class, $object, $uuid ) {
	// ============================================================
		$method = strtolower( $_SERVER[ 'REQUEST_METHOD' ]);
		if( ! preg_match( '/^(?:delete|get|options|patch|post)$/i', $method )) { fail( 500 ); }

		if( $object === null ) { fail( 404 ); }

		header( 'Access-Control-Allow-Origin: *' );
		switch( $method ) {
			case 'delete':
				if( ! $user->full_access( $class, $uuid )) { fail( 401 ); }
				$object->delete( $uuid );
				success();
				break;

			case 'get':
				$rows = $object->get( $_GET, $uuid );
				if( is_int( $rows )) { fail( $rows ); } // GET can return HTTP error as well

				if( Utils::is_assoc_array( $rows )) {
					$results = filter_by_read_access_policy( $user, $class, [ $rows ]);
					$results = $results[ 0 ];

				} else {
					$results = filter_by_read_access_policy( $user, $class, $rows );
				}

				header( 'Content-Type: application/json; charset=utf-8' );
				http_response_code( 200 );
				echo( json_encode( $results ));
				break;

			case 'options':
				header( 'Access-Control-Allow-Methods: DELETE, GET, PATCH, POST' );
				success();
				break;

			case 'patch':
				if( ! $user->full_access( $class, $uuid )) { fail( 401 ); }
				$data = file_get_contents( 'php://input' );
				$data = json_decode( $data, true );
				$object->patch( $data, $uuid );
				success( 204 );
				break;

			case 'post':
				if( ! $user->full_access( $class, $uuid )) { fail( 401 ); }
				$object->post( $_POST, $uuid );
				http_response_code( 200 );
				header( 'Content-Type: application/json; charset=utf-8' );
				echo( json_encode( $_POST ));
				break;
		}
	}

	function filter_by_read_access_policy( $user, $class, $rows ) {
		$results    = [];
		$has_center = array_map( function( $table ) { return "{$table}s"; }, DB::tables_with_center());
		$has_center []= 'data'; // The 'data' table corresponds to 'emmetropization', which is a view; this line is a kludge; DB::tables_with_center() should be refactored to work with views

		foreach( $rows as $row ) {
			$access = true;
			# $access = $user->read_access( $class );
			if( $access ) { $results []= $row; }
		}
		return $results;
	}

?>

