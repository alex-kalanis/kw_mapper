-- PRAGMA synchronous = OFF;
-- PRAGMA journal_mode = MEMORY;

-- BEGIN;

-- DROP TABLE IF EXISTS "d_queued_commands";
CREATE TABLE IF NOT EXISTS "d_queued_commands" (
  "qc_id" INT AUTO_INCREMENT NOT NULL PRIMARY KEY ,
  "qc_time_start" VARCHAR(20) NULL,
  "qc_time_end" VARCHAR(20) NULL,
  "qc_status" INT(1) NULL,
  "qc_command" TEXT NULL
);

-- COMMIT;
