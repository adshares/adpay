import os

#: Twisted TCP port number
SERVER_PORT = int(os.getenv('ADPAY_SERVER_PORT'), 8065)

#: JSONRPC error code returned when payment round is not calculated yet
PAYMENTS_NOT_CALCULATED_YET = -32000

#: Debug endpoint - disable for production - disabled by default
DEBUG_ENDPOINT = bool(os.getenv('ADPAY_DEBUG_ENDPOINT', 0))
