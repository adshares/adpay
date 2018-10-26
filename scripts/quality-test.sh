#!/usr/bin/env bash

TRIAL_BIN=`pipenv run which trial`
pipenv run coverage run $TRIAL_BIN tests
pipenv run coverage xml -i
