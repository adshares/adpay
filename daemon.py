import logging.config
import json
import os

from twisted.internet import reactor

from adpay.iface import server as iface_server
from adpay.stats import tasks as stats_tasks
from adpay import db


if __name__ == "__main__":

    logging.basicConfig()

    logfile_path = os.path.join(os.environ["ADPAY_ROOT"], "adpay", "config", "log_config.json")

    with open(logfile_path, "r") as fd:
        logging.config.dictConfig(json.load(fd))

    db.configure_db()
    iface_server.configure_iface()
    stats_tasks.configure_tasks()
    reactor.run()
