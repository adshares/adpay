#!/usr/bin/env bash
set -e

HERE=$(dirname $(dirname $(readlink -f "$0")))
source ${HERE}/_functions.sh

pipenv install
