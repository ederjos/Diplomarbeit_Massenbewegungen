#!/bin/bash
set -e

# test backend code
./vendor/bin/sail composer run test

# test frontend code
./vendor/bin/sail npm run test
