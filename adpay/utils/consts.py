import os

#: Logging config file (optional), overrides default configuration.
#:
#: `Environmental variable override: ADPAY_LOG_CONFIG_JSON_FILE`
LOG_CONFIG_JSON_FILE = os.getenv('ADPAY_LOG_CONFIG_JSON_FILE', None)

#: Logging level
#:
#: `Environmental variable override: ADPAY_LOG_LEVEL`
LOG_LEVEL = os.getenv('ADPAY_LOG_LEVEL', 'DEBUG').upper()
