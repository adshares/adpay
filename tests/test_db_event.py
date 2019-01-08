from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts


class DBTestCase(tests.db_test_case):
    @defer.inlineCallbacks
    def test_event(self):
        # Test event add.
        for i in range(300):
            yield db_utils.update_event({
                "event_id": str(i),
                "event_type": stats_consts.EVENT_TYPE_CLICK,
                "timestamp": 0,
                "user_id": str(i % 20),
                "banner_id": '1',
                "campaign_id": "campaign_id",
                "event_value": 10,
                "our_keywords": {},
                "human_score": 1})

        # Test event select.
        counter = 0
        event_iter = yield db_utils.get_events_per_user_iter(campaign_id="campaign_id", timestamp=0, uid=u'0')
        while True:
            event_doc = yield event_iter.next()
            if not event_doc:
                break

            self.assertEqual(event_doc['user_id'], u'0')
            self.assertEqual(event_doc['timestamp'], 0)
            self.assertEqual(event_doc['campaign_id'], "campaign_id")
            counter += 1

        self.assertEqual(counter, 15)

        unique_uids = yield db_utils.get_distinct_users_from_events("campaign_id", timestamp=0)
        self.assertEqual(set(unique_uids), set([str(x) for x in range(20)]))

        # Test event deletion.
        for i in range(300):
            yield db_utils.delete_event(str(i))

        counter = 0
        event_iter = yield db_utils.get_events_per_user_iter("campaign_id", timestamp=0, uid=u'0')
        while True:
            event_doc = yield event_iter.next()
            if not event_doc:
                break
            counter += 1
        self.assertEqual(counter, 0)
