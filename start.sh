#!/bin/bash
set -e

./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
