<?php

$email  = $argv[ 1 ];
$pwhash = password_hash( $argv[ 2 ], PASSWORD_DEFAULT );

echo( "UPDATE referee SET pwhash='{$pwhash}' WHERE email = '{$email}';\n" );

?>
