#!/usr/bin/env bash

set -e

TRIAL_BIN=`pipenv run which trial`
pipenv run coverage run $TRIAL_BIN tests
pipenv run coverage xml -i
