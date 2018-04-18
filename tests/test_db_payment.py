from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_payment(self):
        for i in range(200):
            yield db_utils.update_event_payment(
                campaign_id="campaign_id",
                timestamp=0,
                event_id=i,
                payment=i * 20
            )

        counter = 0
        payments_iter = yield db_utils.get_payments_iter(0)
        while True:
            payment_doc = yield payments_iter.next()
            if not payment_doc:
                break

            self.assertEqual(payment_doc['campaign_id'], "campaign_id")
            self.assertEqual(payment_doc['payment'], payment_doc['event_id'] * 20)
            counter += 1
        self.assertEqual(counter, 200)
