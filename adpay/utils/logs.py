import json
import logging.config
import os

from adpay.utils import consts as utils_consts


def setup():

    loglevel = getattr(logging, utils_consts.LOG_LEVEL)

    # Default logging config
    logging.basicConfig(format='[%(asctime)s] %(name)-20s %(levelname)-9s %(message)s',
                        datefmt="%Y-%m-%dT%H:%M:%SZ",
                        handlers=[logging.StreamHandler()],
                        level=loglevel)

    # Override logging config if provided
    logfile_path = utils_consts.LOG_CONFIG_JSON_FILE
    if logfile_path and os.path.exists(logfile_path):

        with open(logfile_path, "r") as fd:
            log_config = json.load(fd)

        logging.config.dictConfig(log_config)
