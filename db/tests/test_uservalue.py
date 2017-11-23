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
    def test_user_value(self):

        for i in range(100):
            yield db_utils.update_user_value(campaign_id="campaign_id",
                                             timestamp=3500,
                                             user_id=i,
                                             payment=i*10,
                                             human_score=10)

