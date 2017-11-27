from twisted.internet import defer

from adpay.iface.tests import IfaceTestCase
from adpay.db import utils as db_utils


class InterfaceEventTestCase(IfaceTestCase):
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
        # Event without campaign shoudn't be added
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
        self.assertEqual(pre_banner_events, post_banner_events)

        # Add campaign for banner_1
        yield db_utils.update_campaign("campaign_id", 123, 234, 100, 100, 1000, {})
        yield db_utils.update_banner("banner_1", "campaign_id")

        # Test event additon with existing campaign
        response = yield self.get_response("add_events", [event_data])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        banner_events = yield self.get_banner_events('banner_1')
        self.assertEqual(len(banner_events), len(pre_banner_events) + 1)

        # Test event addition without userid
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
        yield db_utils.update_campaign("campaign_filter_id", 123, 234, 12, 34, 1000, {
            'require': [
                {
                    'keyword': 'testkey',
                    'filter': {
                        'type': '=',
                        'args': 10
                    }
                }
            ],
            'exclude': [
                {
                    'keyword': 'testkey',
                    'filter': {
                        'type': '<=',
                        'args': 5
                    }
                }
            ]
        })
        yield db_utils.update_banner("banner_filter_id", "campaign_filter_id")

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
        self.assertEqual(len(banner_events), len(pre_banner_events))

        # Test validation true.
        event_data['our_keywords'] = {'testkey': 10}
        response = yield self.get_response("add_events", [event_data])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        banner_events = yield self.get_banner_events('banner_filter_id')
        self.assertEqual(len(banner_events), len(pre_banner_events) + 1)
