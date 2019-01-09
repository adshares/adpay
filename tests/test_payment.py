from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.iface.consts import PAYMENTS_NOT_CALCULATED_YET, INVALID_OBJECT


class InterfacePaymentTestCase(tests.WebTestCase):
    @defer.inlineCallbacks
    def test_get_payments(self):
        response = yield self.get_response("get_payments", [{'timestamp': 0}])
        self.assertIsNotNone(response)
        self.assertEqual(response['error']['code'], PAYMENTS_NOT_CALCULATED_YET)

        # Add some dummy payments.
        yield db_utils.update_payment_round(7200)
        for i in range(100):
            yield db_utils.update_event_payment("campaign_id", 7200, "event_%s" % i, 100)

        response = yield self.get_response("get_payments", [{'timestamp': 7200}])
        self.assertIsNotNone(response)
        self.assertEqual(len(response['result']['payments']), 100)

        for index, payment in enumerate(response['result']['payments']):
            self.assertEqual(payment['amount'], 100)
            self.assertEqual(payment['event_id'], 'event_%s' % index)

        response = yield self.get_response("get_payments", [{'timestamp': 3600}])
        self.assertIsNotNone(response)
        self.assertEqual(response['error']['code'], PAYMENTS_NOT_CALCULATED_YET)

        response = yield self.get_response("get_payments", [{'timestamp': 11000}])
        self.assertIsNotNone(response)
        self.assertEqual(response['error']['code'], PAYMENTS_NOT_CALCULATED_YET)

        response = yield self.get_response("get_payments", [{'timestamp': 7210}])
        self.assertIsNotNone(response)
        self.assertEqual(len(response['result']['payments']), 100)

    @defer.inlineCallbacks
    def test_invalid_request(self):

        response = yield self.get_response("get_payments", [{'dummy_field': 0}])
        self.assertIsNotNone(response)
        self.assertTrue(response['error'])
        self.assertEqual(INVALID_OBJECT, response['error']['code'])
