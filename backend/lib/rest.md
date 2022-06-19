# RESTful API

## HTTP METHODS

### GET

	/api/v1/<class>

Retrieves all instances of the given class as an array, one row per class

	/api/v1/<class>/<uuid>

Retrieves a specific instance of the given class with the given UUID as a single object

	/api/v1/<class>?<field_1>=<comparator_1>:<value_1>[&<field_2>=<comparator_2>:<value-2>

Selects all instances of the given class that meet the query criteria.
Comparator can be any of the following: `eq` (=), `gt` (>), `gte` (>=), `lt`
(<), `lte` (<=), `ne` (!=).
