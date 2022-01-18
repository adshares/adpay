#!/usr/bin/env bash

# Usage: migrate.sh [<work-dir>]
cd ${1:-"."}

bin/console doctrine:migrations:migrate --no-interaction
if [ $? -ne 0 ]; then exit 1; fi
