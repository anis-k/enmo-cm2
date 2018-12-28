-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 18.10 to develop                                --
--                                                                          --
--                                                                          --
-- *************************************************************************--

ALTER TABLE res_letterbox DROP COLUMN IF EXISTS external_signatory_book_id;
ALTER TABLE res_letterbox ADD COLUMN external_signatory_book_id integer;

ALTER TABLE users DROP COLUMN IF EXISTS external_id;
ALTER TABLE users ADD COLUMN external_id json DEFAULT '{}';

/* Redirected Baskets */
DO $$ BEGIN
  IF (SELECT count(TABLE_NAME)  FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'user_abs') = 1 THEN
      DROP TABLE IF EXISTS redirected_baskets;
      CREATE TABLE redirected_baskets
      (
      id serial NOT NULL,
      actual_user_id INTEGER NOT NULL,
      owner_user_id INTEGER NOT NULL,
      basket_id character varying(255) NOT NULL,
      group_id INTEGER NOT NULL,
      CONSTRAINT redirected_baskets_pkey PRIMARY KEY (id),
      CONSTRAINT redirected_baskets_unique_key UNIQUE (owner_user_id, basket_id, group_id)
      )
      WITH (OIDS=FALSE);

      INSERT INTO redirected_baskets (owner_user_id, actual_user_id, basket_id, group_id) SELECT users.id, us.id, user_abs.basket_id, usergroups.id FROM usergroups, usergroup_content, user_abs, groupbasket, users, users us
        where usergroup_content.group_id = usergroups.group_id
        and usergroup_content.user_id = user_abs.user_abs
        and users.user_id = user_abs.user_abs
        and us.user_id = user_abs.new_user
        and groupbasket.group_id = usergroup_content.group_id
        and groupbasket.basket_id = user_abs.basket_id;

--       DROP TABLE IF EXISTS user_abs;
  END IF;
END$$;
UPDATE history SET table_name = 'redirected_baskets' WHERE table_name = 'user_abs';

DROP TABLE IF EXISTS configuration;
CREATE TABLE configuration
(
id serial NOT NULL,
name character varying(64) NOT NULL,
value json DEFAULT '{}' NOT NULL,
CONSTRAINT configuration_pkey PRIMARY KEY (id),
CONSTRAINT configuration_unique_key UNIQUE (name)
)
WITH (OIDS=FALSE);
INSERT INTO configuration (name, value) VALUES ('mailer', '{"type" : "smtp", "host" : "ssl://smtp.gmail.com", "port" : 465, "user" : "", "password" : "", "auth" : true, "secure" : "tls", "from" : "notifications@maarch.org"}');
