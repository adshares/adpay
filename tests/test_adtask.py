from twisted.internet import defer, reactor

import tests
from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts
from adpay.stats import tasks as stats_tasks
from adpay.utils import utils as common_utils


import time


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

        yield db_utils.update_campaign("campaign_id", 12345, 12347, 100, 200, 1000, "{}")

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

        yield db_utils.update_campaign("campaign_id", 12345, 12347, 100, 200, 1000, "{}")
        yield stats_tasks._adpay_task(0)
        yield stats_tasks._adpay_task(timestamp + 10000)

    @defer.inlineCallbacks
    def test_adpay_task_call(self):

        yield stats_tasks.adpay_task()
        ret = reactor.getDelayedCalls()
        self.assertEqual(len(ret), 1)
        call_time = ret[0].getTime()
        self.assertGreaterEqual(call_time, time.time())

        ret[0].cancel()

    def test_configure_tasks(self):

        stats_tasks.configure_tasks()
        ret = reactor.getDelayedCalls()
        self.assertEqual(len(ret), 1)
        call_time = ret[0].getTime()
        self.assertGreaterEqual(call_time, time.time())

        ret[0].cancel()
