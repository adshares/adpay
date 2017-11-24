from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils


class DBTestCase(db_tests.DBTestCase):
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
            self.assertIn(round_doc['timestamp'], [3600, 7200])
        self.assertEqual(counter, 2)
