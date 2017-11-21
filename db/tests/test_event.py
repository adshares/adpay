from twisted.trial import unittest
from twisted.internet import defer

from adpay.db import utils as db_utils
from adpay.db import consts as db_consts
from adpay import db


class DBTestCase(unittest.TestCase):

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
        # Test event add.
        for i in range(300):
            yield db_utils.update_event(
                event_id=i,
                event_type=db_consts.EVENT_TYPE_CLICK,
                timestamp=0,
                user_id=i%20,
                banner_id=1,
                campaign_id="campaign_id",
                paid_amount=10,
                keywords="{}",
                human_score=10)

        # Test event select.
        counter = 0
        event_iter = yield db_utils.get_user_events_iter("campaign_id", timestamp=0, uid=0)
        while True:
            event_doc = yield event_iter.next()
            if not event_doc:
                break

            self.assertEqual(event_doc['user_id'], 0)
            self.assertEqual(event_doc['timestamp'], 0)
            self.assertEqual(event_doc['campaign_id'], "campaign_id")
            counter +=1
        self.assertEqual(counter, 15)

        unique_uids = yield db_utils.get_events_distinct_uids("campaign_id", timestamp=0)
        self.assertEqual(unique_uids, range(20))

        # Test event deletion.
        for i in range(300):
            yield db_utils.delete_event(i)

        counter = 0
        event_iter = yield db_utils.get_user_events_iter("campaign_id", timestamp=0, uid=0)
        while True:
            event_doc = yield event_iter.next()
            if not event_doc:
                break
            counter +=1
        self.assertEqual(counter, 0)

