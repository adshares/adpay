from mock import MagicMock, patch
from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.iface import proto as iface_proto, utils as iface_utils
from adpay.stats import consts as stats_consts


class TestAddEvent(tests.WebTestCase):

    @defer.inlineCallbacks
    def test_add_event(self):

        value = yield iface_utils.add_event(iface_proto.EventObject(
                event_id=str(100),
                event_type=stats_consts.EVENT_TYPE_CONVERSION,
                timestamp=0,
                user_id=str(100 % 20),
                banner_id='1',
                campaign_id="campaign_id",
                our_keywords={},
                human_score=1))

        self.assertIsNotNone(value)

    @defer.inlineCallbacks
    def test_no_campaign_add_event(self):

        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 123,
                   "time_end": 234,
                   "max_cpc": 100,
                   "max_cpm": 100,
                   "budget": 1000,
                   "filters": {}}

        yield db_utils.update_campaign(cmp_doc)
        yield db_utils.update_banner({'banner_id': 'banner_1', 'campaign_id': 'campaign_id'})

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

        campaigns = MagicMock()
        campaigns.return_value = None

        with patch('adpay.db.utils.get_campaign', campaigns):
            response = yield self.get_response("add_events", [event_data])
            self.assertIsNotNone(response)
            self.assertTrue(response['result'])
