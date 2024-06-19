DROP TABLE IF EXISTS referee;

CREATE TABLE referee (
	uuid text primary key,
	fname text default null,
	lname text default null,
	email text default null,
	pwhash text default null,
	dob datetime default null,
	gender text default null,
	address text default null,
	usatid text default null,
	role text default 'referee',
	created_on datetime default (datetime( 'now' )),
	deleted datetime,
	info text default null
);

DROP TABLE IF EXISTS credit;

CREATE TABLE credit (
	uuid text primary key,
	name text not null,
	tags text default null,
	description text default null,
	status text default null,
	created_on datetime default (datetime( 'now' )),
	finished datetime default null,
	deleted datetime,
	info text default null
);

DROP TABLE IF EXISTS media;

CREATE table media (
	uuid text primary key,
	name text not null,
	description text default null,
	created_on datetime default (datetime( 'now' )),
	deleted datetime,
	info text default null
);

DROP TABLE IF EXISTS referee_achievement;

CREATE TABLE referee_achievement (
	uuid text primary key,
	referee text not null,
	achievement text not null,
	awarded datetime,
	created_on datetime default (datetime( 'now' )),
	deleted datetime,
	info text default null,

	FOREIGN KEY( referee ) REFERENCES referee( uuid ),
	FOREIGN KEY( achievement ) REFERENCES achievement( uuid )
);

DROP TABLE IF EXISTS achievement_media;

CREATE TABLE achievement_media (
	uuid text primary key,
	achievement text not null,
	media text not null,
	created_on datetime default (datetime( 'now' )),
	deleted datetime,
	info text default null,

	FOREIGN KEY( achievement ) REFERENCES achievement( uuid ),
	FOREIGN KEY( media ) REFERENCES media( uuid )
);

DROP TABLE IF EXISTS referee_media;

CREATE TABLE referee_media (
	uuid text primary key,
	referee text not null,
	media text not null,
	created_on datetime default (datetime( 'now' )),
	deleted datetime,
	info text default null,

	FOREIGN KEY( referee ) REFERENCES referee( uuid ),
	FOREIGN KEY( media ) REFERENCES media( uuid )
);
