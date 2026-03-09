#!/bin/bash
set -e

#
#   Claude Sonnet 4.6, 2026-03-09
#   "Please update setup.sh to remove the dependencies on PHP and Composer."
#

# https://en.wikipedia.org/wiki/ANSI_escape_code
RESET="\e[0m"
BOLD="\e[1m"
RED="\e[31m"
GREEN="\e[32m"
YELLOW="\e[33m"

check_docker_installed() {
    if ! command -v docker &>/dev/null; then
        echo -e "${RED}${BOLD}Error: Docker is not installed. Please install Docker first.${RESET}"
        exit 1
    fi

    if ! docker info &>/dev/null; then
        echo -e "${RED}${BOLD}Error: Docker is not running. Please start Docker first.${RESET}"
        exit 1
    fi

    if ! docker compose version &>/dev/null; then
        echo -e "${RED}${BOLD}Error: Docker Compose plugin is not available. Please install it first.${RESET}"
        exit 1
    fi
}

check_docker_installed

# usually set by sail, but since the container is built manually, these need to be set here
# https://github.com/laravel/sail/blob/2295ec1403727adbdb29a5c28e7dc347c950d011/bin/sail#L146
export WWWUSER="${WWWUSER:-$UID}"
export WWWGROUP="${WWWGROUP:-$(id -g)}"

echo -e "${YELLOW}${BOLD}Starting setup...${RESET}\n"

cp --update=none .env.example .env

docker compose pull pgsql
docker compose build --no-cache

docker compose up -d --wait pgsql

docker compose run --rm laravel.test bash -c "
    composer install &&
    php artisan key:generate &&
    npm install &&
    npm run build &&
    php artisan migrate:fresh --force --seed
"

docker compose down

echo -e "\n${GREEN}${BOLD}Setup complete! You can now start the environment with ./start.sh${RESET}"
