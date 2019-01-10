from twisted.internet import defer

from adpay.iface import proto as iface_proto, utils as iface_utils
from adpay.stats import consts as stats_consts
from tests import db_test_case


class TestAdd_event(db_test_case):

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
