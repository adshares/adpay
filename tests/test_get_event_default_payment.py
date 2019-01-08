from unittest import TestCase

from adpay.stats import consts as stats_consts, utils as stats_utils


class TestGetEventDefaultPayment(TestCase):
    def test_get_event_default_payment(self):

        pay = stats_utils.get_default_event_payment({'event_type': stats_consts.EVENT_TYPE_CONVERSION,
                                                     'event_value': 5},
                                                    10, 20)
        self.assertEqual(pay, 5)

        pay = stats_utils.get_default_event_payment({'event_type': stats_consts.EVENT_TYPE_CLICK,
                                                     'event_value': 5},
                                                    10, 20)
        self.assertEqual(pay, 10)

        pay = stats_utils.get_default_event_payment({'event_type': stats_consts.EVENT_TYPE_VIEW,
                                                     'event_value': 5},
                                                    10, 20)
        self.assertEqual(pay, 20)
