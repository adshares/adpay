from twisted.trial import unittest
from twisted.internet import defer, reactor
from twisted.internet.protocol import Protocol
from twisted.web.client import Agent

from adpay.iface import server as iface_server
from adpay.iface import consts as iface_consts
from adpay import db


class ReceiverProtocol(Protocol):
    def __init__(self, finished):
        self.finished = finished
        self.body = []

    def dataReceived(self, bytes):
        self.body.append(bytes)

    def connectionLost(self, reason):
        self.finished.callback(''.join(self.body))


class IfaceTestCase(unittest.TestCase):
    @defer.inlineCallbacks
    def setUp(self):
        self.conn = yield db.get_mongo_connection()
        self.db = yield db.get_mongo_db()

        self.port = iface_server.configure_iface()
        self.client = Agent(reactor)

    @defer.inlineCallbacks
    def tearDown(self):
        yield self.conn.drop_database(self.db)
        yield db.disconnect()

        self.port.stopListening()

    @defer.inlineCallbacks
    def get_response(self, post_data=None):
        response = yield self.client.request('POST',
                                             'http://127.0.0.1:%s' %iface_consts.SERVER_PORT)
        finished = defer.Deferred()
        response.deliverBody(ReceiverProtocol(finished))
        data = yield finished
        defer.returnValue(data)
