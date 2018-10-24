#!/usr/bin/env bash

pipenv run coverage run `which trial` tests
pipenv run coverage xml -i
pipenv run sonar-scanner
