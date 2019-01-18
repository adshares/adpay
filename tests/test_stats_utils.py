from unittest import TestCase

from twisted.internet import defer

from adpay.stats import utils as stats_utils


class TestUserBudget(TestCase):

    @defer.inlineCallbacks
    def test_create_user_budget(self):

        budget = yield stats_utils.create_user_budget(None, 0, 'uid')
        self.assertEqual(0, budget['event_type']['default_value'])
