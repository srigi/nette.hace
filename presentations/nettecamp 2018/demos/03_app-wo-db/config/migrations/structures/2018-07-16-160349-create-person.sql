CREATE TABLE person (
    uuid UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    updated_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    removed_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    PRIMARY KEY(uuid)
);
COMMENT ON COLUMN person.uuid IS '(DC2Type:uuid)';
COMMENT ON COLUMN person.created_time IS '(DC2Type:datetime_immutable)';
COMMENT ON COLUMN person.updated_time IS '(DC2Type:datetime_immutable)';
COMMENT ON COLUMN person.removed_time IS '(DC2Type:datetime_immutable)';
