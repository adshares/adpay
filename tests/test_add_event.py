from twisted.internet import defer

from adpay.db import consts as db_consts, utils as db_utils
from adpay.iface import proto as iface_proto, utils as iface_utils
from tests import db_test_case


class TestAdd_event(db_test_case):

    @defer.inlineCallbacks
    def test_add_event(self):

        no_value = yield iface_utils.add_event(iface_proto.EventObject(
                event_id=str(100),
                event_type=db_consts.EVENT_TYPE_CONVERSION,
                timestamp=0,
                user_id=str(100 % 20),
                banner_id='1',
                campaign_id="campaign_id",
                our_keywords={},
                human_score=1))

        self.assertIsNone(no_value)

        yield db_utils.update_banner({'banner_id': 'banner_id1', 'campaign_id': 'campaign_id'})

        no_campaign = yield iface_utils.add_event(iface_proto.EventObject(
                event_id=str(100),
                event_type=db_consts.EVENT_TYPE_CONVERSION,
                timestamp=0,
                user_id=str(100 % 20),
                banner_id="banner_id1",
                event_value=10,
                campaign_id="campaign_id",
                our_keywords={},
                human_score=1))

        self.assertIsNone(no_campaign)
