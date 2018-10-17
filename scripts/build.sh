#!/usr/bin/env bash

if [ ! -z "$TRAVIS" ]; then
  # Checkout repo and change directory

  # Install git
  apt-get install -y git

  git clone https://github.com/adshares/adpay.git /build/adpay
  cd /build/adpay
fi

pip install -r requirements.txt

cp .env.dist .env
