from twisted.trial import unittest
from twisted.internet import defer, reactor
from twisted.internet.protocol import Protocol
from twisted.web.client import Agent

from adpay.iface import server as iface_server
from adpay.db import utils as db_utils
from adpay import db


class IfaceEventTestCase(unittest.TestCase):
    @defer.inlineCallbacks
    def setUp(self):
        self.conn = yield db.get_mongo_connection()
        self.db = yield db.get_mongo_db()

    @defer.inlineCallbacks
    def tearDown(self):
        yield self.conn.drop_database(self.db)
        yield db.disconnect()

    @defer.inlineCallbacks
    def test_event(self):
        class ReceiverProtocol(Protocol):
            def __init__(self, finished):
                self.finished = finished
                self.body = []

            def dataReceived(self, bytes):
                self.body.append(bytes)

            def connectionLost(self, reason):
                self.finished.callback(''.join(self.body))

        port = iface_server.configure_iface()

        client = Agent(reactor)
        response = yield client.request('POST',
                                        'http://127.0.0.1:8090')
        finished = defer.Deferred()
        response.deliverBody(ReceiverProtocol(finished))
        data = yield finished

        print data

        port.stopListening()
