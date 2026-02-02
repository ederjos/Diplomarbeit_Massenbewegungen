#!/bin/bash
set -e

cp -n .env.example .env

composer install
php artisan sail:add pgsql
php artisan key:generate

./vendor/bin/sail up -d
sleep 10
./vendor/bin/sail artisan migrate:fresh --seed

./vendor/bin/sail npm install

./vendor/bin/sail stop
