import os
from dotenv import load_dotenv, find_dotenv

load_dotenv(find_dotenv())

print(os.environ)

#: Twisted TCP port number
SERVER_PORT = int(os.getenv('ADPAY_SERVER_PORT'))
