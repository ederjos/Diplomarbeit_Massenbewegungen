-- Is called after docker compose up automatically to install postgis on postgresql

CREATE EXTENSION IF NOT EXISTS postgis;