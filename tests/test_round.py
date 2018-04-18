from twisted.internet import defer

import tests
from adpay.db import utils as db_utils


class DBTestCase(tests.DBTestCase):
    @defer.inlineCallbacks
    def test_round(self):
        for timestamp in range(7200, 1, -1000):
            yield db_utils.update_payment_round(timestamp)
            yield db_utils.update_payment_round(timestamp)

        _iter = yield db_utils.get_payment_round_iter()
        counter = 0
        while True:
            round_doc = yield _iter.next()
            if round_doc is None:
                break

            counter += 1
            self.assertIn(round_doc['timestamp'], [0, 3600, 7200])
        self.assertEqual(counter, 3)
