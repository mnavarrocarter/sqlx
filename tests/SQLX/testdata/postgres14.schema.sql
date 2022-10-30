CREATE TABLE "user" (
    "id"         SERIAL PRIMARY KEY,
    "tenant_id"  INTEGER NOT NULL,
    "name"       VARCHAR NOT NULL,
    "email"      VARCHAR NOT NULL,
    "password"   VARCHAR NOT NULL,
    "created_at" TIMESTAMP NOT NULL
)