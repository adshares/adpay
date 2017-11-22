from twisted.internet import defer

from adpay.iface.tests import IfaceTestCase
from adpay.db import utils as db_utils


class InterfacePaymentTestCase(IfaceTestCase):
    @defer.inlineCallbacks
    def test_get_payments(self):
        response = yield self.get_response("get_payments", [{'timestamp':0}])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])
        self.assertEqual(response['result']['payments'], [])
