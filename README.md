# AdPay
[![Build Status](https://travis-ci.org/adshares/adpay.svg?branch=master)](https://travis-ci.org/adshares/adpay)
[![Build Status](https://sonarcloud.io/api/project_badges/measure?project=adshares-adpay&metric=alert_status)](https://sonarcloud.io/dashboard?id=adshares-adpay)
[![Docs Status](https://readthedocs.org/projects/adshares-adpay/badge/?version=latest)](http://adshares-adpay.readthedocs.io/en/latest/)
## Build
AdPay is fully implemented in python.

### Dependencies

All dependencies are in Pipfile, which is managed by [Pipenv](https://pipenv.readthedocs.io/en/latest/).

Ubuntu 18.04 dependencies can be found in [pre-build](scripts/pre-build.sh) and [pre-install](scripts/pre-install.sh) scripts.

### Installation

Installation instructions can be found in the [documentation](https://adshares-adpay.readthedocs.io/en/latest/).

Please note, that you'll want to configure AdPay. Read the [configuration documentation](https://adshares-adpay.readthedocs.io/en/latest/config.html).

## TL;DR  
```
git clone https://github.com/adshares/adpay
cd adpay
bash scripts/pre-build.sh
bash scripts/pre-install.sh
pipenv install
pipenv run daemon
```

## Authors

- **Krzysztof Kuchta** - _Python programmer_
- **[Adam Włodarkiewicz](https://github.com/awlodarkiewicz)** - _Python programmer_

See also the list of [contributors](https://github.com/adshares/adpay/contributors) who participated in this project.
