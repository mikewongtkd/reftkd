# Database Class

An extensible, semi-structured ORM interface.

ORM libraries such as *fractal* use class introspection to efficiently store objects in structured data in RDBMS. 

Key ideas

- Semi-structured data
- First-level keys are represented as columns in RDB for indexing efficiency
- If the *value* of a *first-level key* is
  - Numeric: store as an SQLite3 native integer or float
  - Boolean: store as an SQLite3 native integer `0|1`
  - String: store as an SQLite3 native string
  - Array: store as a JSON string
  - Object: store as a JSON string

