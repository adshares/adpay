from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts
from adpay.stats import utils as stats_utils


class DBTestCase(tests.db_test_case):

    @defer.inlineCallbacks
    def test_campaign_payments(self):
        payment_percentage_cutoff = 0.5
        cpv, cpc = 10, 20
        payments = yield stats_utils.calculate_events_payments_using_user_value(None,
                                                                                3600,
                                                                                payment_percentage_cutoff=payment_percentage_cutoff)
        self.assertIsNone(payments)

        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 1234,
                   "time_end": 3456,
                   "max_cpc": cpc,
                   "max_cpm": cpv,
                   "budget": 1000,
                   "filters": {'require': {},
                               'exclude': {}}}
        yield db_utils.update_campaign(cmp_doc)

        yield db_utils.update_banner({'banner_id': 'banner_id1', 'campaign_id': 'campaign_id'})
        yield db_utils.update_banner({'banner_id': 'banner_id2', 'campaign_id': 'campaign_id'})
        yield db_utils.update_banner({'banner_id': 'banner_id3', 'campaign_id': 'campaign_id'})

        # Add events for users
        yield db_utils.update_event({
            "event_id": "event1_user_id1",
            "event_type": stats_consts.EVENT_TYPE_CLICK,
            "timestamp": 3601,
            "user_id": 'user_id1',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.1,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1})

        timestamp = 3600

        if stats_consts.CALCULATION_METHOD == 'user_value':
            yield stats_utils.calculate_events_payments_using_user_value(cmp_doc, timestamp,
                                                                         payment_percentage_cutoff=payment_percentage_cutoff)
            # Check user values
            user_value_doc = yield db_utils.get_user_value_in_campaign(cmp_doc, "user_id1")
            self.assertEqual(user_value_doc['payment'], 20)
            self.assertEqual(user_value_doc['human_score'], 1)
        else:
            yield stats_utils.calculate_events_payments_default(cmp_doc, timestamp)
            _iter = yield db_utils.get_payments_iter(timestamp)
            while True:
                payment_doc = yield _iter.next()
                if not payment_doc:
                    break
                self.assertEqual(payment_doc['payment'], 20)

        yield db_utils.update_event({
            "event_id": "event2_user_id1",
            "event_type": stats_consts.EVENT_TYPE_VIEW,
            "timestamp": 3600,
            "user_id": 'user_id1',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.2,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1})

        yield db_utils.update_event({
            "event_id": "event2_user_id2",
            "event_type": stats_consts.EVENT_TYPE_CONVERSION,
            "timestamp": 3602,
            "user_id": 'user_id2',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.5,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1})

        if stats_consts.CALCULATION_METHOD == 'user_value':
            yield stats_utils.calculate_events_payments_using_user_value(cmp_doc, 3600,
                                                                         payment_percentage_cutoff=payment_percentage_cutoff)

            # Check user values
            user2_value_doc = yield db_utils.get_user_value_in_campaign("campaign_id", "user_id2")
            self.assertEqual(user2_value_doc['payment'], 10)
            self.assertEqual(user2_value_doc['human_score'], 1)
        else:
            yield stats_utils.calculate_events_payments_default(cmp_doc, timestamp)
            _iter = yield db_utils.get_payments_iter(timestamp)
            while True:
                payment_doc = yield _iter.next()
                if not payment_doc:
                    break
                if payment_doc['event_id'] == "event2_user_id1":
                    self.assertLess(payment_doc['payment'], 10)
                if payment_doc['event_id'] == "event2_user_id2":
                    self.assertLess(payment_doc['payment'], 0.01)

    @defer.inlineCallbacks
    def test_campaign_payments_more(self):
        payment_percentage_cutoff = 0.5
        cpv, cpc = 10, 20
        timestamp = 3600

        payments = yield stats_utils.calculate_events_payments_using_user_value(None,
                                                                                3600,
                                                                                payment_percentage_cutoff=payment_percentage_cutoff)
        self.assertIsNone(payments)
        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 1234,
                   "time_end": 3456,
                   "max_cpc": cpc,
                   "max_cpm": cpv,
                   "budget": 1000,
                   "filters": {'require': {},
                               'exclude': {}}}

        yield db_utils.update_campaign(cmp_doc)

        yield db_utils.update_banner({'banner_id': 'banner_id1', 'campaign_id': 'campaign_id'})
        yield db_utils.update_banner({'banner_id': 'banner_id2', 'campaign_id': 'campaign_id'})
        yield db_utils.update_banner({'banner_id': 'banner_id3', 'campaign_id': 'campaign_id'})

        # Add events for users
        yield db_utils.update_event({
            "event_id": "event2_user_id1",
            "event_type": stats_consts.EVENT_TYPE_VIEW,
            "timestamp": 3600,
            "user_id": 'user_id1',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.2,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1})

        yield db_utils.update_event({
            "event_id": "event2_user_id2",
            "event_type": stats_consts.EVENT_TYPE_CONVERSION,
            "timestamp": 3602,
            "user_id": 'user_id2',
            "banner_id": 'banner_id1',
            "campaign_id": "campaign_id",
            "event_value": 0.5,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1})

        if stats_consts.CALCULATION_METHOD == 'user_value':
            yield stats_utils.calculate_events_payments_using_user_value(cmp_doc, 3600,
                                                                         payment_percentage_cutoff=payment_percentage_cutoff)

            # Check user values
            user2_value_doc = yield db_utils.get_user_value_in_campaign("campaign_id", "user_id2")
            self.assertEqual(user2_value_doc['payment'], 10)
            self.assertEqual(user2_value_doc['human_score'], 1)
        else:
            yield stats_utils.calculate_events_payments_default(cmp_doc, timestamp)
            _iter = yield db_utils.get_payments_iter(timestamp)
            while True:
                payment_doc = yield _iter.next()
                if not payment_doc:
                    break
                if payment_doc['event_id'] == "event2_user_id1":
                    self.assertLess(payment_doc['payment'], 10)
                if payment_doc['event_id'] == "event2_user_id2":
                    self.assertLess(payment_doc['payment'], 0.025)

        # User scores should be empty.
        _iter = yield db_utils.get_sorted_user_score_iter("campaign_id", 3600, limit=1)
        user_score_doc = yield _iter.next()
        self.assertIsNone(user_score_doc)
