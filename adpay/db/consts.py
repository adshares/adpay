import os

#: MongoDB port, ie. database connection port for AdPay application.
#:
#: `Environmental variable override: ADPAY_MONGO_DB_PORT`
MONGO_DB_PORT = int(os.getenv('ADPAY_MONGO_DB_PORT', 27017))

#: MongoDB database name, ie. database name for AdPay application.
#:
#: `Environmental variable override: ADPAY_MONGO_DB_NAME`
MONGO_DB_NAME = os.getenv('ADPAY_MONGO_DB_NAME', 'adpay')

#: MongoDB database host, ie. database host for AdPay application.
#:
#: `Environmental variable override: ADPAY_MONGO_DB_HOST`
MONGO_DB_HOST = os.getenv('ADPAY_MONGO_DB_HOST', 'localhost')
