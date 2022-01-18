#!/usr/bin/env bash

# Usage: build.sh [<work-dir>]
cd ${1:-"."}

composer install --no-dev --no-interaction
if [ $? -ne 0 ]; then exit 1; fi
