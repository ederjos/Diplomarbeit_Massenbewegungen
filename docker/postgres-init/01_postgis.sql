-- Is called after docker compose up automatically
-- to install postgis on postgresql

-- Since the installation of magellan and the creation of postgis
-- extension inside a migration, this wouldn't be necessary anymore
-- but it doesn't hurt to have it here as well
-- in case someone wants to use the DB without running the migrations
CREATE EXTENSION IF NOT EXISTS postgis;