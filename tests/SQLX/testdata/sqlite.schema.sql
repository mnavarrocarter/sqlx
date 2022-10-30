CREATE TABLE user (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    email      TEXT NOT NULL,
    password   TEXT NOT NULL,
    created_at TEXT NOT NULL
)