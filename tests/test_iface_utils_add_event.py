from mock import MagicMock, patch
from twisted.internet import defer
from twisted.trial.unittest import TestCase

from adpay.iface import proto as iface_proto, utils as iface_utils
from adpay.stats import consts as stats_consts


class TestAddEvent(TestCase):

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

        func_mock = MagicMock()

        # Legacy add keywords
        with patch('adpay.stats.consts.CALCULATION_METHOD', 'user_value'):
            with patch('adpay.stats.legacy.add_view_keywords', func_mock):

                    value = yield iface_utils.add_event(iface_proto.EventObject(
                            event_id=str(100),
                            event_type=stats_consts.EVENT_TYPE_CONVERSION,
                            timestamp=0,
                            user_id=str(100 % 20),
                            banner_id='1',
                            campaign_id="campaign_id",
                            our_keywords={'key': 'value'},
                            human_score=1))

                    self.assertIsNotNone(value)

        func_mock.assert_called_once_with(str(100 % 20), ['key_value'])
