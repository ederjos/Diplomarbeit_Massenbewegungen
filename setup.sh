#!/bin/bash
set -e

cp -n .env.example .env

composer install
php artisan sail:add pgsql
php artisan key:generate

./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate

./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
