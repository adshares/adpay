from twisted.trial import unittest
from twisted.internet import defer

from adpay.db import utils as db_utils
from adpay import db


class DBTestCase(unittest.TestCase):
    @defer.inlineCallbacks
    def setUp(self):
        self.conn = yield db.get_mongo_connection()
        self.db = yield db.get_mongo_db()
        yield db.configure_db()

    @defer.inlineCallbacks
    def tearDown(self):
        yield self.conn.drop_database(self.db)
        yield db.disconnect()

    @defer.inlineCallbacks
    def test_round(self):
        for timestamp in range(7200, 1, -1000):
            yield db_utils.update_payment_round(timestamp)
            yield db_utils.update_payment_round(timestamp)

        _iter = yield db_utils.get_payment_round_iter()
        counter = 0
        while True:
            round_doc = yield _iter.next()
            if round_doc is None:
                break

            counter +=1
            self.assertIn(round_doc['timestamp'], [3600, 7200])
        self.assertEqual(counter, 2)

        last_round_doc = yield db_utils.get_last_round()
        self.assertEqual(last_round_doc['timestamp'], 7200)
