import os

#: Twisted TCP port number, ie. AdPay server port
#:
#: `Environmental variable override: ADPAY_SERVER_PORT`
SERVER_PORT = int(os.getenv('ADPAY_SERVER_PORT', 9091))

#: JSONRPC error code returned when payment round is not calculated yet
#:
#: `Environmental variable override: ADPAY_PAYMENTS_NOT_CALCULATED_YET`
PAYMENTS_NOT_CALCULATED_YET = -32000

#: Enable an endpoint, which allows to force payment recalculation. For development use only!
#:
#: `Environmental variable override: ADPAY_DEBUG_ENDPOINT`
DEBUG_ENDPOINT = bool(os.getenv('ADPAY_DEBUG_ENDPOINT', 0))
