import os
from dotenv import load_dotenv

load_dotenv()

#: MongoDB port
MONGO_DB_PORT = int(os.getenv('ADPAY_MONGO_DB_PORT'))
MONGO_DB_NAME = os.getenv('ADPAY_MONGO_DB_NAME')

#: Click event
EVENT_TYPE_CLICK = os.getenv('ADPAY_EVENT_TYPE_CLICK')
#: View (impression) event
EVENT_TYPE_VIEW = os.getenv('ADPAY_EVENT_TYPE_VIEW')
#: Conversion event
EVENT_TYPE_CONVERSION = os.getenv('ADPAY_EVENT_TYPE_CONVERSION')
