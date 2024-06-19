<?php
include_once( '/usr/local/app/lib/db.php' );
namespace Security;

class Policy {
	private static $roles         = null;
	public  static $FA_ICONS      = null;
	public  static $GROUP_RANK    = null;
	public  const  ROLE_ADMIN     = 'admin';
	public  const  ROLE_REFEREE   = 'referee';
	public  const  ROLE_ANY       = 'any';
	public  const  GROUP_ALL      = 'all';
	public  const  GROUP_OWN      = 'own';
	public  const  GROUP_SELF     = 'self';
	public  const  READ_ACCESS    = 'read';
	public  const  FULL_ACCESS    = 'full';

	function __construct() {
		if( self::$roles === null ) {
			$text   = file_get_contents( '/usr/local/app/security/policy.json' );
			$policy = json_decode( $text, true );

			self::$roles      = $policy[ 'roles' ];
			self::$FA_ICONS   = $policy[ 'icons' ];
			self::$GROUP_RANK = $policy[ 'group_rank' ];
		}
	}

	function features() {
		$features = [];
		foreach( self::$roles as $role => $permissions ) {
			$features = array_merge( $features, array_keys( $permissions ));
		}
		$features = array_unique( $features );
		sort( $features );
		return $features;
	}

	function icon( $role ) {
		if( ! array_key_exists( $role, Policy::$FA_ICONS )) { return null; }
		$icon = Policy::$FA_ICONS[ $role ];
		return "<span class=\"fas fa-{$icon}\"></span>";
	}

	function permissions( $role, $feature ) {
		$permissions = [];
		$roles       = [ $role, Policy::ROLE_ANY ];

		if( ! in_array( $feature, $this->features())) { return $permissions; }

		foreach( $roles as $role ) {
			if( $role === null ) { continue; }
			$features = self::$roles[ $role ];
			if( array_key_exists( $feature, $features )) {
				foreach( $features[ $feature] as $group => $access ) {
					$permissions[ $group ] = $access;
				}
			}
		}
		return $permissions;
	}

	function roles() {
		if( self::$roles === null ) { return null; }
		$roles = array_keys( self::$roles );
		return $roles;
	}

	function groups( $feature, $is_self = false, $is_own = false ) {
		$group_rank = Policy::$GROUP_RANK;
		$groups     = [ Policy::GROUP_ALL ];
		$is_refs    = $feature == 'referees';
		if( $is_refs && $is_self ) { $groups []= Policy::GROUP_SELF;   }
		if( $is_own              ) { $groups []= Policy::GROUP_OWN;   }

		usort( $groups, function ( $a, $b ) use ($group_rank) { return $group_rank[ $a ] - $group_rank[ $b ]; });

		return $groups;
	}
};

class User {
	public  $uuid;
	public  $id;
	public  $name;
	public  $fname;
	public  $lname;
	private $role;
	private $policy;

	function __construct() {
		$this->uuid   = User::session( 'uuid' );
		$this->email  = User::session( 'email' );
		$this->fname  = User::session( 'fname' );
		$this->lname  = User::session( 'lname' );
		$this->name   = User::session( 'name' );
		$this->role   = User::session( 'role' );
		$this->policy = new Policy();
	}

	function has( $feature, $uuid = null ) {
		$db     = new DB();
		$tables = DB::tables();
		$table  = "referee_{$feature}";
		if( ! in_array( $table, $tables )) { return false; }
		$rows = $db->select( "select * from {$table}" );
		foreach( $rows as $row ) {
			if( ! array_key_exists( $feature, $row )) { continue; }
			if( $row[ $feature ] == $uuid ) { return true; }
		}

		return false;
	}

	function is_auth() { return isset( $this->role ); }

	function access( $feature, $uuid = null ) {
		$permissions = $this->policy->permissions( $this->role, $feature );
		$is_self     = $uuid == $this->uuid;
		$does_own    = $user->has( $feature, $uuid );
		$groups      = $this->policy->groups( $feature, $is_self, $does_own );

		foreach( $groups as $group ) {
			if( ! array_key_exists( $group, $permissions )) { continue; }
			return $permissions[ $group ];
		}
		return null;
	}

	function full_access( $feature, $uuid = null ) {
		$access = $this->access( $feature, $uuid );
		return $access == Policy::FULL_ACCESS;
	}

	function read_access( $feature, $uuid = null ) {
		$access = $this->access( $feature, $uuid );
		return $access == Policy::READ_ACCESS || $access == Policy::FULL_ACCESS;
	}
	
	function permissions( $feature ) {
		$permissions = $this->policy->permissions( $this->role, $feature );
		$groups      = array_keys( Policy::$GROUP_RANK );
		$all         = [];
		foreach( $groups as $group ) {
			if( ! array_key_exists( $group, $permissions )) { continue; }
			$all[ $group ] = $permissions[ $group ];
		}

		return $all;
	}

	function redirect( $error = 'Unauthorized' ) {
		$encoded = urlencode( base64_encode( $error ));
		header( "Location: index.php?error={$encoded}" );
		exit();
	}

	function role_icon() {
		return $this->policy->icon( $this->role );
	}

	static function session( $key ) {
		if( isset( $_SESSION[ $key ])) { return $_SESSION[ $key ]; }
		return null;
	}
}

?>
