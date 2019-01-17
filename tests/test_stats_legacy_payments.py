import random

from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts, legacy as stats_legacy


class DBTestCase(tests.db_test_case):

    @defer.inlineCallbacks
    def test_no_campaign_payments(self):
        payment_percentage_cutoff = 0.5
        timestamp = 3600

        payments = yield stats_legacy.calculate_events_payments(None,
                                                                timestamp,
                                                                payment_percentage_cutoff=payment_percentage_cutoff)
        self.assertIsNone(payments)

    @defer.inlineCallbacks
    def test_campaign_payments(self):
        payment_percentage_cutoff = 0.5
        cpv, cpc = 10, 20
        timestamp = 3600

        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 0,
                   "time_end": 3600 + 100,
                   "max_cpc": cpc,
                   "max_cpm": cpv,
                   "budget": 100000,
                   "filters": {'require': {},
                               'exclude': {}}}

        yield db_utils.update_campaign(cmp_doc)

        # Add 3 banners for this campaign
        for i in xrange(3):
            yield db_utils.update_banner({'banner_id': 'banner_id' + str(i),
                                          'campaign_id': cmp_doc['campaign_id']})

        # Add events for users
        yield db_utils.update_event({
            'campaign_id': cmp_doc['campaign_id'],
            "event_id": "event1_user_id1",
            "event_type": stats_consts.EVENT_TYPE_CLICK,
            "timestamp": timestamp + 2,
            "user_id": 'user_id1',
            "banner_id": 'banner_id1',
            "event_value": 0.1,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1})

        yield stats_legacy.calculate_events_payments(cmp_doc,
                                                     timestamp,
                                                     payment_percentage_cutoff=payment_percentage_cutoff)
        # Check user values
        user_value_doc = yield db_utils.get_user_value_in_campaign(cmp_doc['campaign_id'], "user_id1")
        self.assertEqual(user_value_doc['payment'], 20)
        self.assertEqual(user_value_doc['human_score'], 1)

        yield db_utils.update_event({
            'campaign_id': cmp_doc['campaign_id'],
            "event_id": "event2_user_id1",
            "event_type": stats_consts.EVENT_TYPE_VIEW,
            "timestamp": timestamp + 2,
            "user_id": 'user_id1',
            "banner_id": 'banner_id1',
            "event_value": 0.2,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1})

        yield db_utils.update_event({
            'campaign_id': cmp_doc['campaign_id'],
            "event_id": "event2_user_id2",
            "event_type": stats_consts.EVENT_TYPE_CONVERSION,
            "timestamp": timestamp + 2,
            "user_id": 'user_id2',
            "banner_id": 'banner_id1',
            "event_value": 0.5,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1})

        yield stats_legacy.calculate_events_payments(cmp_doc, 3600,
                                                     payment_percentage_cutoff=payment_percentage_cutoff)

        # Check user values
        user2_value_doc = yield db_utils.get_user_value_in_campaign("campaign_id", "user_id2")
        self.assertEqual(user2_value_doc['payment'], 10)
        self.assertEqual(user2_value_doc['human_score'], 1)

    @defer.inlineCallbacks
    def test_campaign_payments_more(self):
        payment_percentage_cutoff = 0.5
        cpv, cpc = 10, 20
        timestamp = 3600

        # Add campaign
        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 0,
                   "time_end": timestamp + 100,
                   "max_cpc": cpc,
                   "max_cpm": cpv,
                   "budget": 1000,
                   "filters": {'require': {},
                               'exclude': {}}}

        yield db_utils.update_campaign(cmp_doc)

        # Add 3 banners for this campaign
        for i in xrange(3):
            yield db_utils.update_banner({'banner_id': 'banner_id' + str(i),
                                          'campaign_id': cmp_doc['campaign_id']})

        # Add events for users
        yield db_utils.update_event({
            'campaign_id': cmp_doc['campaign_id'],
            "event_id": "event2_user_id1",
            "event_type": stats_consts.EVENT_TYPE_VIEW,
            "timestamp": timestamp + 1,
            "user_id": 'user_id1',
            "banner_id": 'banner_id1',
            "event_value": 0.2,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1.0})

        yield db_utils.update_event({
            'campaign_id': cmp_doc['campaign_id'],
            "event_id": "event2_user_id2",
            "event_type": stats_consts.EVENT_TYPE_CONVERSION,
            "timestamp": timestamp + 2,
            "user_id": 'user_id2',
            "banner_id": 'banner_id1',
            "event_value": 0.5,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1.0})

        yield stats_legacy.calculate_events_payments(cmp_doc,
                                                     timestamp,
                                                     payment_percentage_cutoff=payment_percentage_cutoff)

        # Check user values
        user2_value_doc = yield db_utils.get_user_value_in_campaign(cmp_doc['campaign_id'], "user_id2")
        self.assertEqual(user2_value_doc['payment'], 10)
        self.assertEqual(user2_value_doc['human_score'], 1)

        # User scores should be empty.
        _iter = yield db_utils.get_sorted_user_score_iter(cmp_doc['campaign_id'], timestamp, limit=1)
        user_score_doc = yield _iter.next()
        self.assertIsNone(user_score_doc)

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

        for i in xrange(5):
            event['event_id'] = 'event_id_' + str(i)
            event['event_type'] = str(random.randint(1000, 1001))
            yield db_utils.update_event(event)

        # Calculate payments
        yield stats_legacy.calculate_events_payments(cmp_doc, timestamp)

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

    @defer.inlineCallbacks
    def test_zero_user_score(self):

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
            "event_id": "event_id",  # This will be modified
            "event_type": "click",  # This will be modified
            "timestamp": timestamp + 2,
            "user_id": 'test_user',
            "banner_id": 'banner_id',
            "campaign_id": "campaign_id",
            "event_value": 5,
            "our_keywords": {},
            "their_keywords": {},
            "human_score": 1.0}

        for i in xrange(5):
            event['event_id'] = 'event_id_' + str(i)
            event['event_type'] = str(random.randint(1000, 1001))
            yield db_utils.update_event(event)

        # Calculate payments
        yield stats_legacy.calculate_events_payments(cmp_doc, timestamp)

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
