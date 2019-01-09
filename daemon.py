from twisted.internet import reactor

from adpay import db
from adpay.iface import server as iface_server
from adpay.stats import tasks as stats_tasks
from adpay.utils import logs as server_logging

if __name__ == "__main__":

    # Set up logging.
    server_logging.setup()

    # Configuring database.
    db.configure_db()

    # Start http interface to communicate with others AdShares components.
    iface_server.configure_iface()

    # Configure periodical tasks.
    stats_tasks.configure_tasks()

    # Run.
    reactor.run()
