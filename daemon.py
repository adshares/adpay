from twisted.internet import reactor
from twisted.python import log
import sys

from adpay import server as iface_server
from adpay import tasks as stats_tasks
from adpay import db

log.startLogging(sys.stdout)


if __name__ == "__main__":
    db.configure_db()
    iface_server.configure_iface()
    stats_tasks.configure_tasks()
    reactor.run()
