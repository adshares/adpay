import os

#: MongoDB port
MONGO_DB_PORT = int(os.getenv('ADPAY_MONGO_DB_PORT', '27017'))
MONGO_DB_NAME = os.getenv('ADPAY_MONGO_DB_NAME', 'adpay')
MONGO_DB_HOST = os.getenv('ADPAY_MONGO_DB_HOST', 'localhost')

#: Click event
EVENT_TYPE_CLICK = os.getenv('ADPAY_EVENT_TYPE_CLICK')
#: View (impression) event
EVENT_TYPE_VIEW = os.getenv('ADPAY_EVENT_TYPE_VIEW')
#: Conversion event
EVENT_TYPE_CONVERSION = os.getenv('ADPAY_EVENT_TYPE_CONVERSION')
