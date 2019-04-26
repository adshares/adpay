import time

from mock import MagicMock, patch
from twisted.internet import defer, reactor

import tests
from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts, tasks as stats_tasks
from adpay.utils import utils as common_utils


class DBTestCase(tests.db_test_case):
    @defer.inlineCallbacks
    def get_payment_rounds(self):
        _iter = yield db_utils.get_payment_round_iter()
        rounds = []
        while True:
            payment_round_doc = yield _iter.next()
            if payment_round_doc is None:
                break
            rounds.append(payment_round_doc)
        defer.returnValue(rounds)

    @defer.inlineCallbacks
    def test_adpay_task(self):
        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 12345,
                   "time_end": 12347,
                   "max_cpc": 100,
                   "max_cpm": 200,
                   "budget": 1000,
                   "filters": {}}
        yield db_utils.update_campaign(cmp_doc)

        timestamp = int(time.time()) - stats_consts.SECONDS_PER_HOUR

        payment_round = yield db_utils.get_payment_round(timestamp)
        payment_rounds = yield self.get_payment_rounds()
        self.assertIsNone(payment_round)
        self.assertEqual(len(payment_rounds), 0)

        yield stats_tasks._adpay_task(timestamp)
        yield stats_tasks._adpay_task(timestamp)

        payment_round = yield db_utils.get_payment_round(timestamp)
        payment_rounds = yield self.get_payment_rounds()
        self.assertEqual(payment_round['timestamp'], common_utils.timestamp2hour(timestamp))
        self.assertEqual(len(payment_rounds), 1)

        yield stats_tasks._adpay_task(timestamp, False)

        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 12345,
                   "time_end": 12347,
                   "max_cpc": 100,
                   "max_cpm": 200,
                   "budget": 1000,
                   "filters": {}}
        yield db_utils.update_campaign(cmp_doc)

        yield stats_tasks._adpay_task(0)
        yield stats_tasks._adpay_task(timestamp + 10000)

    @defer.inlineCallbacks
    def test_adpay_task_call(self):

        # Disabled
        with patch('adpay.stats.consts.CALCULATE_PAYMENTS_PERIODICALLY', 0):
            yield stats_tasks.adpay_task()
            ret = reactor.getDelayedCalls()
            self.assertEqual(len(ret), 0)

        # Enabled
        with patch('adpay.stats.consts.CALCULATE_PAYMENTS_PERIODICALLY', 1):
            yield stats_tasks.adpay_task()
            ret = reactor.getDelayedCalls()
            self.assertEqual(len(ret), 1)
            call_time = ret[0].getTime()
            self.assertGreaterEqual(call_time, time.time())

            ret[0].cancel()

    def test_calculate_events_payments(self):
        """
        Make sure proper calculation functions are called.

        :return:
        """
        with patch('adpay.stats.consts.CALCULATION_METHOD', 'default'):
            calc_function = MagicMock()
            with patch('adpay.stats.main.calculate_events_payments', calc_function):

                stats_tasks.calculate_events_payments(campaign_doc=None,
                                                      timestamp=0)
            calc_function.assert_called_once()

        with patch('adpay.stats.consts.CALCULATION_METHOD', 'user_value'):
            calc_function = MagicMock()
            keyword_update_function = MagicMock()
            with patch('adpay.stats.legacy.calculate_events_payments', calc_function):
                with patch('adpay.stats.legacy.update_user_keywords_profiles', keyword_update_function):

                    stats_tasks.calculate_events_payments(campaign_doc=None,
                                                          timestamp=0,
                                                          payment_percentage_cutoff=0.5)

            calc_function.assert_called_once()
            keyword_update_function.assert_called_once()

    def test_configure_tasks(self):

        stats_tasks.configure_tasks()
        ret = reactor.getDelayedCalls()
        self.assertEqual(len(ret), 2)
        call_time = ret[0].getTime()
        self.assertGreaterEqual(call_time, time.time())

        ret[0].cancel()
        ret[1].cancel()

