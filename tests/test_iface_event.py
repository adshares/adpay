from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.iface.consts import INVALID_OBJECT


class InterfaceEventTestCase(tests.WebTestCase):

    @defer.inlineCallbacks
    def get_banner_events(self, banner_id):
        events = []
        _iter = yield db_utils.get_banner_events_iter(banner_id=banner_id, timestamp=45678)
        while True:
            event_doc = yield _iter.next()
            if not event_doc:
                break

            events.append(event_doc)
        defer.returnValue(events)

    @defer.inlineCallbacks
    def test_nocampaign_add_event(self):
        # Event without campaign shouldn't be added
        event_data = {
            'event_id': 'event_id',
            'event_type': 'event_type',
            'user_id': 'user_id',
            'human_score': 0.5,
            'publisher_id': 'publisher_id',
            'timestamp': 45678,
            'banner_id': "banner_1",
            'our_keywords': {},
            'their_keywords': {},
            'event_value': None
            }

        pre_banner_events = yield self.get_banner_events('banner_1')

        response = yield self.get_response("add_events", [event_data])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        post_banner_events = yield self.get_banner_events('banner_1')
        self.assertNotEqual(pre_banner_events, post_banner_events)

        # Add campaign for banner_1
        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 123,
                   "time_end": 234,
                   "max_cpc": 100,
                   "max_cpm": 100,
                   "budget": 1000,
                   "filters": {}}
        yield db_utils.update_campaign(cmp_doc)
        yield db_utils.update_banner({'banner_id': 'banner_1', 'campaign_id': 'campaign_id'})

        # Test event addition with existing campaign
        response = yield self.get_response("add_events", [event_data])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        banner_events = yield self.get_banner_events('banner_1')
        self.assertEqual(len(banner_events), len(pre_banner_events) + 1)

        # Test event addition without user_id
        pre_banner_events = banner_events
        del event_data['user_id']
        response = yield self.get_response("add_events", [event_data])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        banner_events = yield self.get_banner_events('banner_1')
        self.assertEqual(len(banner_events), len(pre_banner_events))

    @defer.inlineCallbacks
    def test_campaign_filters_add_event(self):
        # Add campaign with filters
        # campaign_id, time_start, time_end, max_cpc, max_cpm, budget, filters
        cmp_doc = {"campaign_id": "campaign_filter_id",
                   "time_start": 123,
                   "time_end": 234,
                   "max_cpc": 12,
                   "max_cpm": 34,
                   "budget": 1000,
                   "filters": {
                       'require': {'testkey': [10]},
                       'exclude': {'testkey': ["0--5"]}
                       }}
        yield db_utils.update_campaign(cmp_doc)
        yield db_utils.update_banner({'banner_id': 'banner_filter_id', 'campaign_id': 'campaign_filter_id'})

        event_data = {
            'event_id': 'event_id',
            'event_type': 'event_type',
            'user_id': 'user_id',
            'human_score': 0.5,
            'publisher_id': 'publisher_id',
            'timestamp': 45678,
            'banner_id': "banner_filter_id",
            'our_keywords': {'testkey': 5},
            'their_keywords': {},
            'event_value': None
            }

        pre_banner_events = yield self.get_banner_events('banner_filter_id')

        # Test validation false.
        response = yield self.get_response("add_events", [event_data])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        banner_events = yield self.get_banner_events('banner_filter_id')
        self.assertNotEqual(len(banner_events), len(pre_banner_events))

        # Test validation true.
        event_data['our_keywords'] = {'testkey': 10}
        response = yield self.get_response("add_events", [event_data])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        banner_events = yield self.get_banner_events('banner_filter_id')
        self.assertEqual(len(banner_events), len(pre_banner_events) + 1)

    @defer.inlineCallbacks
    def test_add_empty(self):
        response = yield self.get_response("add_events", [])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

    @defer.inlineCallbacks
    def test_invalid_request(self):

        response = yield self.get_response("add_events", [{'dummy_field': 0}])
        self.assertIsNotNone(response)
        self.assertTrue(response['error'])
        self.assertEqual(INVALID_OBJECT, response['error']['code'])
