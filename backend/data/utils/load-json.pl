#! /usr/bin/perl

use JSON::XS;
use File::Slurp qw( read_file );
use Digest::SHA1 qw( sha1_hex );

my $text = read_file( 'ca_refs.json' );
my $json = new JSON::XS();
my $refs = $json->decode( $text );

my $insert = { referee => [], achievement => [], refach => [] };

foreach my $uuid (sort keys %$refs) {
	my $ref = $refs->{ $uuid };
	$ref->{ fname } =~ s/'/''/g;
	$ref->{ lname } =~ s/'/''/g;
	$ref->{ gender } = lc substr( $ref->{ gender }, 0, 1 );
	add_referee( $insert, $ref );

	if( exists( $ref->{ kyorugi })) {
		add_rank_achievement( $insert, $ref, 'kyorugi' );
	}

	if( exists( $ref->{ poomsae })) {
		add_rank_achievement( $insert, $ref, 'poomsae' );
	}
}

printf "INSERT INTO referee (uuid, fname, lname, email, dob, gender, usatid) values %s;\n\n", join( ', ', @{$insert->{ referee }}); 
printf "INSERT INTO achievement (uuid, name, tags, description, info) values %s;\n\n", join( ', ', @{$insert->{ achievement }}); 
printf "INSERT INTO referee_achievement (uuid, referee, achievement, awarded) values %s;\n\n", join( ', ', @{$insert->{ refach }}); 

# ============================================================
sub add_referee {
# ============================================================
	my $i   = shift;
	my $ref = shift;
	push @{$i->{ referee }}, sprintf( "( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )", @$ref{ qw( uuid fname lname email dob gender usatid )});
}

# ============================================================
sub add_rank_achievement {
# ============================================================
	my $i          = shift;
	my $ref        = shift;
	my $discipline = shift;
	my $uuid       = uuid( "$ref->{ uuid }|$discipline" );
	my $ach        = $ref->{ $discipline };
	my $tags       = sprintf( '[ "%s", "%s", "%s" ]', $discipline, 'level', $ach->{ level });
	my $info       = sprintf( '{ "discipline" : "%s", "cert" : "%s", "level" : "%s" }', $discipline, $ach->{ cert }, $ach->{ level });
	my $desc       = sprintf( "Referee has achieved %s level rank in %s, certificate ID: %s", $ach->{ level }, ucfirst $discipline, $ach->{ cert });

	push @{$i->{ achievement }}, sprintf( "( '%s', '%s Level', '%s', '%s', '%s' )", $uuid, ucfirst $discipline, $tags, $desc, $info );

	my $rauuid     = uuid( "$ref->{ uuid }|$uuid" );

	push @{$i->{ refach }}, sprintf( "( '%s', '%s', '%s', datetime( 'now' ))", $rauuid, $ref->{ uuid }, $uuid );
}

# ============================================================
sub uuid {
# ============================================================
	my $key = shift;
	my $hex = substr( sha1_hex( $key ), 0, 32 );
	my @arr = split( //, $hex );

	foreach my $dash ( qw( 20 16 12 8 )) {
		splice( @arr, $dash, 0, '-' );
	}

	return join( '', @arr );
}
