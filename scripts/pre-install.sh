#!/usr/bin/env bash

set -e

apt-get -qq -y install --no-install-recommends python python-pip

pip install pipenv
