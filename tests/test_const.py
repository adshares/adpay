from unittest import TestCase

from adpay.stats import consts as stats_const


class TestSetConst(TestCase):

    def test_const(self):

        self.assertIsNot(stats_const.SERVER_PORT, None)
