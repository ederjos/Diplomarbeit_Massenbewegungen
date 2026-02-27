#!/bin/bash

# lint and format backend code
./vendor/bin/sail composer run lint

# lint and format frontend code
./vendor/bin/sail npm run lint
./vendor/bin/sail npm run format
