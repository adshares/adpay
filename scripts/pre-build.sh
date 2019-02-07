#!/usr/bin/env bash

set -e

apt-get -qq -y install --no-install-recommends python python-pip python-dev gcc

pip install pipenv
