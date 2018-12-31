from unittest import TestCase
from adpay.db import consts as db_consts
from adpay.stats import utils as stats_utils


class TestGet_event_max_payment(TestCase):
    def test_get_event_max_payment(self):

        pay = stats_utils.get_default_event_payment({'event_type': db_consts.EVENT_TYPE_CONVERSION,
                                                 'event_value': 5},
                                                    10, 20)
        self.assertEqual(pay, 5)

        pay = stats_utils.get_default_event_payment({'event_type': db_consts.EVENT_TYPE_CLICK,
                                                 'event_value': 5},
                                                    10, 20)
        self.assertEqual(pay, 10)

        pay = stats_utils.get_default_event_payment({'event_type': db_consts.EVENT_TYPE_VIEW,
                                                 'event_value': 5},
                                                    10, 20)
        self.assertEqual(pay, 20)
