DROP TABLE IF EXISTS "secrets";
DROP TABLE IF EXISTS "letters";
DROP TABLE IF EXISTS "templates";
DROP TABLE IF EXISTS "campaign_tags";
DROP TABLE IF EXISTS "campaign_groups";
DROP TABLE IF EXISTS "campaigns";
DROP TABLE IF EXISTS "client_groups";
DROP TABLE IF EXISTS "clients";
DROP TABLE IF EXISTS "tags";
DROP TABLE IF EXISTS "groups";
DROP TABLE IF EXISTS "settings";

DROP TYPE IF EXISTS "setting_type";
CREATE TYPE "setting_type" AS ENUM ('string', 'integer', 'float', 'boolean', 'timestamp');

DROP TYPE IF EXISTS "campaign_status";
CREATE TYPE "campaign_status" AS ENUM ('created', 'tested', 'queued', 'started', 'paused', 'finished', 'archived');

DROP TYPE IF EXISTS "letter_status";
CREATE TYPE "letter_status" AS ENUM ('created', 'sent', 'skipped', 'failed');

CREATE TABLE "settings" (
    "id" serial NOT NULL,
    "name" character varying(255) NOT NULL,
    "type" setting_type NOT NULL,
    "value_string" character varying(255) NULL,
    "value_integer" int NULL,
    "value_float" float NULL,
    "value_boolean" boolean NULL,
    "value_datetime" timestamp NULL,
    CONSTRAINT "settings_pk" PRIMARY KEY ("id"),
    CONSTRAINT "settings_name_unique" UNIQUE ("name")
);

CREATE TABLE "groups" (
    "id" serial NOT NULL,
    "name" character varying(255) NOT NULL,
    CONSTRAINT "groups_pk" PRIMARY KEY ("id"),
    CONSTRAINT "groups_name_unique" UNIQUE ("name")
);

CREATE TABLE "tags" (
    "id" serial NOT NULL,
    "name" character varying(255) NOT NULL,
    "descr" text NULL,
    CONSTRAINT "tags_pk" PRIMARY KEY ("id"),
    CONSTRAINT "tags_name_unique" UNIQUE ("name")
);

CREATE TABLE "clients" (
    "id" serial NOT NULL,
    "email" character varying(255) NOT NULL,
    "bounced" boolean NOT NULL,
    CONSTRAINT "clients_pk" PRIMARY KEY ("id"),
    CONSTRAINT "clients_email_unique" UNIQUE ("email")
);

CREATE TABLE "client_groups" (
    "id" serial NOT NULL,
    "client_id" int NOT NULL,
    "group_id" int NOT NULL,
    CONSTRAINT "client_groups_pk" PRIMARY KEY ("id"),
    CONSTRAINT "client_groups_client_fk" FOREIGN KEY ("client_id")
        REFERENCES "clients" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT "client_groups_group_fk" FOREIGN KEY ("group_id")
        REFERENCES "groups" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE "campaigns" (
    "id" serial NOT NULL,
    "name" character varying(255) NOT NULL,
    "status" campaign_status NOT NULL,
    "when_deadline" timestamp NULL,
    "when_created" timestamp NULL,
    "when_started" timestamp NULL,
    "when_finished" timestamp NULL,
    CONSTRAINT "campaign_pk" PRIMARY KEY ("id")
);

CREATE TABLE "campaign_groups" (
    "id" serial NOT NULL,
    "campaign_id" int NOT NULL,
    "group_id" int NOT NULL,
    CONSTRAINT "campaign_groups_pk" PRIMARY KEY ("id"),
    CONSTRAINT "campaign_groups_campaign_fk" FOREIGN KEY ("campaign_id")
        REFERENCES "campaigns" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT "campaign_groups_group_fk" FOREIGN KEY ("group_id")
        REFERENCES "groups" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE "campaign_tags" (
    "id" serial NOT NULL,
    "campaign_id" int NOT NULL,
    "tag_id" int NOT NULL,
    CONSTRAINT "campaign_tags_pk" PRIMARY KEY ("id"),
    CONSTRAINT "campaign_tags_client_fk" FOREIGN KEY ("campaign_id")
        REFERENCES "campaigns" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT "campaign_tags_tag_fk" FOREIGN KEY ("tag_id")
        REFERENCES "tags" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE "templates" (
    "id" serial NOT NULL,
    "campaign_id" int NOT NULL,
    "subject" text NULL,
    "headers" text NULL,
    "body" text NULL,
    CONSTRAINT "templates_pk" PRIMARY KEY ("id"),
    CONSTRAINT "templates_campaign_fk" FOREIGN KEY ("campaign_id")
        REFERENCES "campaigns" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE "letters" (
    "id" serial NOT NULL,
    "template_id" int NOT NULL,
    "client_id" int NOT NULL,
    "status" letter_status NOT NULL,
    "when_created" timestamp NOT NULL,
    "when_processed" timestamp NULL,
    "message_id" character varying(255) NOT NULL,
    "from_address" text NOT NULL,
    "to_address" text NOT NULL,
    "subject" text NOT NULL,
    "headers" text NOT NULL,
    "body" text NOT NULL,
    CONSTRAINT "letters_pk" PRIMARY KEY ("id"),
    CONSTRAINT "letters_template_fk" FOREIGN KEY ("template_id")
        REFERENCES "templates" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT "letters_client_fk" FOREIGN KEY ("client_id")
        REFERENCES "clients" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT "letters_message_id_unique" UNIQUE ("message_id")
);

CREATE TABLE "secrets" (
    "id" serial NOT NULL,
    "campaign_id" int NOT NULL,
    "client_id" int NOT NULL,
    "data_form" character varying(255) NOT NULL,
    "secret_key" character varying(255) NOT NULL,
    "when_opened" timestamp NULL,
    "when_saved" timestamp NULL,
    CONSTRAINT "secrets_pk" PRIMARY KEY ("id"),
    CONSTRAINT "secrets_form_unique" UNIQUE ("campaign_id", "client_id", "data_form"),
    CONSTRAINT "secrets_key_unique" UNIQUE ("secret_key"),
    CONSTRAINT "secrets_secret_key_unique" UNIQUE ("secret_key"),
    CONSTRAINT "secrets_campaign_fk" FOREIGN KEY ("campaign_id")
        REFERENCES "campaigns" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT "secrets_client_fk" FOREIGN KEY ("client_id")
        REFERENCES "clients" ("id")
        ON UPDATE CASCADE ON DELETE CASCADE
);
