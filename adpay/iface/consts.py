import os

#: Twisted TCP port number, ie. AdPay server port
#:
#: `Environmental variable override: ADPAY_SERVER_PORT`
SERVER_PORT = int(os.getenv('ADPAY_SERVER_PORT', 8012))
SERVER_INTERFACE = os.getenv('ADPAY_SERVER_INTERFACE', '127.0.0.1')

#: JSONRPC error code returned when payment round is not calculated yet
PAYMENTS_NOT_CALCULATED_YET = -32000

#: JSONRPC error code return when receiving invalid JSON objects.
INVALID_OBJECT = -32010

#: Enable an endpoint, which allows to force payment recalculation. 1 for enable, 0 for disable. For development use only!
#:
#: `Environmental variable override: ADPAY_DEBUG_ENDPOINT`
DEBUG_ENDPOINT = bool(int(os.getenv('ADPAY_ENABLE_DEBUG_ENDPOINT', 0)))
