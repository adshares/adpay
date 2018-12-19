from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.db import consts as db_consts
from adpay.stats import utils as stats_utils
from adpay.iface.proto import EventObject


class DBTestCase(tests.db_test_case):

    @defer.inlineCallbacks
    def test_campaign_payments(self):
        payment_percentage_cutoff = 0.5
        cpv, cpc = 10, 20

        payments = yield stats_utils.calculate_events_payments("campaign_id",
                                                               3600,
                                                               payment_percentage_cutoff=payment_percentage_cutoff)
        self.assertIsNone(payments)
        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 1234,
                   "time_end": 3456,
                   "max_cpc": cpc,
                   "max_cpm": cpv,
                   "budget": 1000,
                   "filters": {}}
        yield db_utils.update_campaign(cmp_doc)

        yield db_utils.update_banner({'banner_id': 'banner_id1', 'campaign_id': 'campaign_id'})
        yield db_utils.update_banner({'banner_id': 'banner_id2', 'campaign_id': 'campaign_id'})
        yield db_utils.update_banner({'banner_id': 'banner_id3', 'campaign_id': 'campaign_id'})

        # Add events for users
        yield db_utils.update_event({
            "event_id": "event1_user_id1",
            "event_type": db_consts.EVENT_TYPE_CLICK,
            "timestamp": 3601,
            "user_id": 'user_id1',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.1,
            "our_keywords": {},
            "human_score": 1})

        yield stats_utils.calculate_events_payments("campaign_id", 3600,
                                                    payment_percentage_cutoff=payment_percentage_cutoff)

        # Check user values
        user_value_doc = yield db_utils.get_user_value("campaign_id", "user_id1")
        self.assertEqual(user_value_doc['payment'], 20)
        self.assertEqual(user_value_doc['human_score'], 1)

        yield db_utils.update_event({
            "event_id": "event2_user_id1",
            "event_type": db_consts.EVENT_TYPE_VIEW,
            "timestamp": 3600,
            "user_id": 'user_id1',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.2,
            "our_keywords": {},
            "human_score": 1})

        yield db_utils.update_event({
            "event_id": "event2_user_id2",
            "event_type": db_consts.EVENT_TYPE_CONVERSION,
            "timestamp": 3602,
            "user_id": 'user_id2',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.5,
            "our_keywords": {},
            "human_score": 1})

        yield stats_utils.calculate_events_payments("campaign_id", 3600,
                                                    payment_percentage_cutoff=payment_percentage_cutoff)

        # Check user values
        user2_value_doc = yield db_utils.get_user_value("campaign_id", "user_id2")
        self.assertEqual(user2_value_doc['payment'], 10)
        self.assertEqual(user2_value_doc['human_score'], 1)

    @defer.inlineCallbacks
    def test_campaign_payments_more(self):
        payment_percentage_cutoff = 0.5
        cpv, cpc = 10, 20

        payments = yield stats_utils.calculate_events_payments("campaign_id",
                                                               3600,
                                                               payment_percentage_cutoff=payment_percentage_cutoff)
        self.assertIsNone(payments)
        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 1234,
                   "time_end": 3456,
                   "max_cpc": cpc,
                   "max_cpm": cpv,
                   "budget": 1000,
                   "filters": {}}
        yield db_utils.update_campaign(cmp_doc)

        yield db_utils.update_banner({'banner_id': 'banner_id1', 'campaign_id': 'campaign_id'})
        yield db_utils.update_banner({'banner_id': 'banner_id2', 'campaign_id': 'campaign_id'})
        yield db_utils.update_banner({'banner_id': 'banner_id3', 'campaign_id': 'campaign_id'})

        # Add events for users
        yield db_utils.update_event({
            "event_id": "event2_user_id1",
            "event_type": db_consts.EVENT_TYPE_VIEW,
            "timestamp": 3600,
            "user_id": 'user_id1',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.2,
            "our_keywords": {},
            "human_score": 1})

        yield db_utils.update_event({
            "event_id": "event2_user_id2",
            "event_type": db_consts.EVENT_TYPE_CONVERSION,
            "timestamp": 3602,
            "user_id": 'user_id2',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.5,
            "our_keywords": {},
            "human_score": 1})

        yield stats_utils.calculate_events_payments("campaign_id", 3600,
                                                    payment_percentage_cutoff=payment_percentage_cutoff)

        # Check user values
        user2_value_doc = yield db_utils.get_user_value("campaign_id", "user_id2")
        self.assertEqual(user2_value_doc['payment'], 10)
        self.assertEqual(user2_value_doc['human_score'], 1)

        # Check payments
        _iter = yield db_utils.get_payments_iter(3600)
        while True:
            event_payment_doc = yield _iter.next()
            if event_payment_doc is None:
                break

            self.assertEqual(event_payment_doc['payment'], 10)
            self.assertEqual(event_payment_doc['campaign_id'], "campaign_id")

        # User scores should be empty.
        _iter = yield db_utils.get_sorted_user_score_iter("campaign_id", 3600, limit=1)
        user_score_doc = yield _iter.next()
        self.assertIsNone(user_score_doc)
