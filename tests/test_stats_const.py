from unittest import TestCase

from adpay.stats import consts as stats_const


class TestSetConst(TestCase):

    def test_const(self):

        self.assertIsNot(stats_const.SECONDS_PER_HOUR, None)
        self.assertEqual(stats_const.MAX_USER_KEYWORDS_IN_PROFILE, None)
