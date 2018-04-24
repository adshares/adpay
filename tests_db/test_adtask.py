from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts
from adpay.stats import tasks as stats_tasks
from adpay.utils import common as common_utils


import time


class DBTestCase(tests.DBTestCase):
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