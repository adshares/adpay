from tests import db_test_case
from adpay.iface import server
from twisted.internet import defer


class TestAdPayIfaceServer(db_test_case):

    @defer.inlineCallbacks
    def test_jsonrpc_test_get_payments(self):
        srv = server.AdPayIfaceServer()
        ret = yield srv.jsonrpc_test_get_payments()
        self.assertTrue(ret)
