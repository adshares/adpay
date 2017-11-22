from twisted.internet import defer

from adpay.iface.tests import IfaceTestCase
from adpay.db import utils as db_utils


class InterfaceEventTestCase(IfaceTestCase):
    EVENT_DATA = {
        'event_id':'event_id',
        'event_type':'event_type',
        'user_id':'user_id',
        'human_score':0.5,
        'publisher_id':'publisher_id',
        'timestamp':45678,
        'banner_id':"banner_1",
        'our_keywords':{},
        'their_keywords':{},
        'paid_amount':None
    }

    @defer.inlineCallbacks
    def test_add_event(self):
        response = yield self.get_response("add_events", [self.EVENT_DATA])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])
