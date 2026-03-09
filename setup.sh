#!/bin/bash
set -e

#
#   Claude Sonnet 4.6, 2026-03-09
#   "Please update setup.sh to remove the dependencies on PHP and Composer."
#

RESET="\033[0m"
BOLD="\033[1m"
RED="\033[0;31m"
GREEN="\033[0;32m"
YELLOW="\033[0;33m"

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

echo -e "${YELLOW}${BOLD}Starting setup...${RESET}\n"

cp --update=none .env.example .env

docker compose pull pgsql &
PULL_PID=$!
docker compose build --no-cache &
BUILD_PID=$!
wait $PULL_PID && wait $BUILD_PID

docker compose up -d --wait pgsql

docker compose run --rm laravel.test bash -c "
    composer install &&
    php artisan key:generate &&
    npm install &&
    npm run build &&
    php artisan migrate:fresh --force --seed
"

echo -e "\n${GREEN}${BOLD}Setup complete! You can now start the environment with ./start.sh${RESET}"
