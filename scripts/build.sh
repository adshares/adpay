#!/usr/bin/env bash

## Shell cosmetics
bold=$(tput bold)
normal=$(tput sgr0)

env | sort

if [ ! -v TRAVIS ]; then
  # Checkout repo and change directory

  # Install git
  git --version || apt-get install -y git

  git clone \
    --depth=1 \
    https://github.com/adshares/adpanel.git \
    --branch ${ADPAY_INSTALLATION_BRANCH} \
    ${ADPAY_BUILD_PATH}/build

  cd ${ADPAY_BUILD_PATH}/build
fi

# Output versions
echo "${bold}## Installation information"
python --version
pip --version
pip freeze
echo "##${normal}"

envsubst < .env.dist | tee .env

pipenv install
