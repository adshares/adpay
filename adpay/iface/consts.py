import os
from dotenv import load_dotenv

load_dotenv()

print(os.environ)

#: Twisted TCP port number
SERVER_PORT = int(os.getenv('ADPAY_SERVER_PORT'))
