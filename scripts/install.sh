#!/usr/bin/env bash

set -e

# Create directories
mkdir -p ${INSTALLATION_PATH}

mv Pipfile ${INSTALLATION_PATH}/
mv Pipfile.lock ${INSTALLATION_PATH}/

mv .venv ${INSTALLATION_PATH}/

mv .env ${INSTALLATION_PATH}/

mv adpay ${INSTALLATION_PATH}/
mv config ${INSTALLATION_PATH}/
mv daemon.py ${INSTALLATION_PATH}/
