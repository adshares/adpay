import json
import logging
import os

LOG_CONFIG_JSON_FILE = os.getenv('ADPAY_LOG_CONFIG_JSON_FILE', None)
LOG_LEVEL = os.getenv('ADPAY_LOG_LEVEL', 'DEBUG').upper()


def setup():

    if hasattr(logging, LOG_LEVEL):
        loglevel = getattr(logging, LOG_LEVEL)
    else:
        loglevel = logging.DEBUG

    # Default logging config
    logging.basicConfig(format='[%(asctime)s] %(name)-20s %(levelname)-9s %(message)s',
                        datefmt="%Y-%m-%dT%H:%M:%SZ",
                        handlers=[logging.StreamHandler()],
                        level=loglevel)

    # Override logging config if provided
    logfile_path = LOG_CONFIG_JSON_FILE
    if logfile_path and os.path.exists(logfile_path):

        with open(logfile_path, "r") as fd:
            log_config = json.load(fd)

        logging.config.dictConfig(log_config)
