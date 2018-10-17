import os
from dotenv import load_dotenv, find_dotenv

load_dotenv(find_dotenv(raise_error_if_not_found=True))

print(os.environ)

print(dotenv.find_dotenv())
print(os.getcwd())

#: Twisted TCP port number
SERVER_PORT = int(os.getenv('ADPAY_SERVER_PORT'))
