#!/usr/bin/env bash

set -e

if [[ -v ${INSTALLATION_PATH} ]]
then
    printf "INSTALLATION_PATH not defined"
    exit 1
fi

mkdir -p ${INSTALLATION_PATH}

mv Pipfile ${INSTALLATION_PATH}/
mv Pipfile.lock ${INSTALLATION_PATH}/

mv adpay ${INSTALLATION_PATH}/
mv daemon.py ${INSTALLATION_PATH}/
