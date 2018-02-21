# adpay

## Build
ADPAY is fully implemented in python.

### Dependencies

All dependenies are listed in requirements.txt file.

#### Linux

Exmaple for Debian based systems:
```
$ sudo apt-get install python-virtualenv
```

Create virtualenv environment for adpay.
```
$ cd ~
$ virtualenv adpay
$ source ~/adpay/bin/activate

$ export VIRTUALENV_ROOT=$HOME/adpay
$ export PYTHONPATH=$HOME/adpay:$PYTHONPATH
```

Create folder for MONGO database.
```
$ mkdir -p ~/adpay/db/mongo
```


Create folders for supervisor.
```
$ mkdir -p ~/adpay/log/supervisor ~/adpay/log/adpay ~/adpay/log/mongo
$ mkdir -p ~/adpay/run/supervisor ~/adpay/run/adpay ~/adpay/run/mongo
```

Download source code and install dependencies.
```
$ git clone https://github.com/adshares/adpay.git ~/adpay/adpay
$ pip install -r ~/adpay/adpay/requirements.txt
```

Run adpay daemon.
```
$ supervisord -c ~/adpay/adpay/config/supervisord.conf
```

## Build
```
$ cd ~/adpay/adpay
$ trial db iface stats
```
## TL;DR
```
#adpay
apt-get install python-virtualenv mongodb
screen -S adpay
cd ~
virtualenv adpay
export VIRTUALENV_ROOT=$HOME/adpay
export PYTHONPATH=$HOME/adpay:$PYTHONPATH
source ~/adpay/bin/activate
mkdir -p ~/adpay/db/mongo
mkdir -p ~/adpay/log/supervisor ~/adpay/log/adpay ~/adpay/log/mongo
mkdir -p ~/adpay/run/supervisor ~/adpay/run/adpay ~/adpay/run/mongo
git clone https://github.com/adshares/adpay.git ~/adpay/adpay
pip install -r ~/adpay/adpay/requirements.txt
supervisord -c ~/adpay/adpay/config/supervisord.conf
```
