import random

from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts, main as stats_default


class DBTestCase(tests.db_test_case):

    @defer.inlineCallbacks
    def test_campaign_payments(self):
        cpv, cpc = 10, 20

        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 0,
                   "time_end": 3600 + 100,
                   "max_cpc": cpc,
                   "max_cpm": cpv,
                   "budget": 1000,
                   "filters": {'require': {},
                               'exclude': {}}}
        yield db_utils.update_campaign(cmp_doc)

        yield db_utils.update_banner({'banner_id': 'banner_id1',
                                      'campaign_id': 'campaign_id'})

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

        yield stats_default.calculate_events_payments(cmp_doc, timestamp)
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

        yield stats_default.calculate_events_payments(cmp_doc, timestamp)
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

        cpv, cpc = 10, 20
        timestamp = 3600

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

        yield stats_default.calculate_events_payments(cmp_doc, timestamp)
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

    @defer.inlineCallbacks
    def test_budget(self):
        """
        Test for hourly budget constraint. Total payments can't be higher than the campaign budget.
        """

        timestamp = 3600

        # Add campaign with one banner
        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 0,
                   "time_end": 3600 + 100,
                   "max_cpc": 5,
                   "max_cpm": 5000,
                   "budget": 10,
                   "filters": {'require': {},
                               'exclude': {}}}

        yield db_utils.update_campaign(cmp_doc)
        yield db_utils.update_banner({'banner_id': 'banner_id',
                                      'campaign_id': 'campaign_id'})

        # Add all kind of paid events
        event = {
            "event_id": "event_id",         # This will be modified
            "event_type": "click",          # This will be modified
            "timestamp": timestamp + 2,
            "user_id": 'test_user',
            "banner_id": 'banner_id',
            "campaign_id": "campaign_id",
            "event_value": 5,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1.0}

        for i, event_type in enumerate(stats_consts.PAID_EVENT_TYPES):
            event['event_id'] = 'event_id_' + str(i)
            event['event_type'] = event_type
            yield db_utils.update_event(event)

        # Calculate payments
        yield stats_default.calculate_events_payments(cmp_doc, timestamp)

        # Check payments
        total_payments = 0
        _iter = yield db_utils.get_payments_iter(timestamp)
        while True:
            payment_doc = yield _iter.next()
            if not payment_doc:
                break
            total_payments += payment_doc['payment']

        self.assertLessEqual(total_payments, cmp_doc['budget'])

    @defer.inlineCallbacks
    def test_non_payable_events(self):
        """
        Test for non payable events.
        """

        timestamp = 3600

        # Add campaign with one banner
        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 0,
                   "time_end": 3600 + 100,
                   "max_cpc": 5,
                   "max_cpm": 5000,
                   "budget": 10,
                   "filters": {'require': {},
                               'exclude': {}}}

        yield db_utils.update_campaign(cmp_doc)
        yield db_utils.update_banner({'banner_id': 'banner_id',
                                      'campaign_id': 'campaign_id'})

        # Add 5 events
        event = {
            "event_id": "event_id",         # This will be modified
            "event_type": "click",          # This will be modified
            "timestamp": timestamp + 2,
            "user_id": 'test_user',
            "banner_id": 'banner_id',
            "campaign_id": "campaign_id",
            "event_value": 5,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1.0}

        for i in range(5):
            event['event_id'] = 'event_id_' + str(i)
            event['event_type'] = str(random.randint(1000, 1001))
            yield db_utils.update_event(event)

        # Calculate payments
        yield stats_default.calculate_events_payments(cmp_doc, timestamp)

        # Check payments
        _iter = yield db_utils.get_payments_iter(timestamp)
        while True:
            payment_doc = yield _iter.next()
            if not payment_doc:
                break

            # Check payment reason is accepted
            self.assertEqual(0, payment_doc['reason'])
            # But payment is 0
            self.assertEqual(0, payment_doc['payment'])
