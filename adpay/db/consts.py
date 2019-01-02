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

#: Click event name
#:
#: `Environmental variable override: ADPAY_EVENT_TYPE_CLICK`
EVENT_TYPE_CLICK = os.getenv('ADPAY_EVENT_TYPE_CLICK', 'click')

#: View/Impression event name
#:
#: `Environmental variable override: ADPAY_EVENT_TYPE_VIEW`
EVENT_TYPE_VIEW = os.getenv('ADPAY_EVENT_TYPE_VIEW', 'view')

#: Conversion event name
#:
#: `Environmental variable override: ADPAY_EVENT_TYPE_CONVERSION`
EVENT_TYPE_CONVERSION = os.getenv('ADPAY_EVENT_TYPE_CONVERSION', 'conversion')
