#!/usr/bin/env bash

set -e

HERE=$(dirname $(readlink -f "$0"))
TOP=$(dirname ${HERE})
cd ${TOP}

if [[ -v GIT_CLONE ]]
then
  git --version || apt-get install -y git

  git clone \
    --depth=1 \
    https://github.com/adshares/adpay.git \
    --branch ${BUILD_BRANCH:-master} \
    ${BUILD_PATH}/build

  cd ${BUILD_PATH}/build
fi

if [[ ${ADPAY_APP_ENV:-dev} == 'dev' ]]
then
    pipenv install --dev
elif [[ ${ADPAY_APP_ENV} == 'deploy' ]]
then
    pipenv install --deploy
else
    pipenv install
fi
