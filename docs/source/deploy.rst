Deployment
==========

Check out code from repo:

``git clone https://github.com/adshares/adpay.git``

Run pre-build scripts:

``cd adpay && bash scripts/pre-build.sh``

Install pipenv dependencies:

``pipenv install``

or

``pipenv install --dev`` for development dependencies.

Run the server:

``pipenv run python daemon.py``
