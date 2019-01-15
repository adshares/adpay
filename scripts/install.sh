#!/usr/bin/env bash

set -e

if [[ -v ${INSTALLATION_PATH} ]]; then

    printf "INSTALLATION_PATH not defined"
    exit 1

fi

# Create directories
mkdir -p ${INSTALLATION_PATH}

# Move Pipfiles
mv Pipfile ${INSTALLATION_PATH}/
mv Pipfile.lock ${INSTALLATION_PATH}/

# Move Python environment
mv .venv ${INSTALLATION_PATH}/

# Move AdPay & daemon launcher
mv adpay ${INSTALLATION_PATH}/
mv daemon.py ${INSTALLATION_PATH}/
