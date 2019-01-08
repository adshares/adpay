from twisted.internet import reactor

from adpay import db
from adpay.iface import server as iface_server
from adpay.stats import tasks as stats_tasks
from adpay.utils import logs as server_logging

if __name__ == "__main__":

    server_logging.setup()
    db.configure_db()
    iface_server.configure_iface()
    stats_tasks.configure_tasks()
    reactor.run()
