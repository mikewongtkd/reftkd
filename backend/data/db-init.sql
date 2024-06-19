DROP TABLE IF EXISTS document;

CREATE TABLE document (
	uuid text primary key,
	type text not null,
	data text default null,
	created datetime default (datetime( 'now' )),
	modified datetime default (datetime( 'now' )),
	deleted datetime default null
);
