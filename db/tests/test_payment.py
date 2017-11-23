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
    def test_payment(self):
        for i in range(200):
            yield db_utils.update_event_payment(
                campaign_id="campaign_id",
                timestamp=0,
                event_id=i,
                payment=i*20
            )

        counter = 0
        payments_iter = yield db_utils.get_payments_iter(0)
        while True:
            payment_doc = yield payments_iter.next()
            if not payment_doc:
                break

            self.assertEqual(payment_doc['campaign_id'], "campaign_id")
            self.assertEqual(payment_doc['payment'], payment_doc['event_id']*20)
            counter += 1
        self.assertEqual(counter, 200)
