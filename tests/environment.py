import socket
from threading import Thread

from twisted.internet import reactor

from adpay.iface.consts import SERVER_PORT
from adpay.iface.server import configure_iface
from tests import MongoMockTestCase


def before_all(context):
    # Mock Mongo
    context.mock_db = MongoMockTestCase()
    context.mock_db.setUp()

    # Configure our interface url
    host = socket.gethostbyname(socket.gethostname())
    port = SERVER_PORT
    context.interface_url = 'http://{0}:{1}'.format(host, port)

    # Start listening
    context.server = configure_iface(port=port)

    # Run Twisted reactor
    Thread(target=reactor.run, args=(False,)).start()


def after_all(context):

    # Shutdown interface
    context.server.stopListening()

    # Shutdown Twisted reactor
    reactor.stop()

    # Shutdown mock Mongo
    context.mock_db.tearDown()
