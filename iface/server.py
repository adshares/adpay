from twisted.internet import reactor, defer
from twisted.web.server import Site

from fastjsonrpc.server import JSONRPCServer

from adpay.iface import consts as iface_consts
from adpay.iface import utils as iface_utils
from adpay.iface import proto as iface_proto
from adpay.stats import tasks as stats_tasks


class AdPayIfaceServer(JSONRPCServer):
    #campaign interface
    @defer.inlineCallbacks
    def jsonrpc_campaign_update(self, *campaign_data_list):
        for campaign_data in campaign_data_list:
            yield iface_utils.create_or_update_campaign(iface_proto.CamapaignObject(campaign_data))
        defer.returnValue(True)

    @defer.inlineCallbacks
    def jsonrpc_campaign_delete(self, *campaign_id_list):
        for campaign_id in campaign_id_list:
            yield iface_utils.delete_campaign(campaign_id)
        defer.returnValue(True)

    #events interface
    @defer.inlineCallbacks
    def jsonrpc_add_events(self, *event_data_list):
        for event_data in event_data_list:
            yield iface_utils.add_event(iface_proto.EventObject(event_data))
        defer.returnValue(True)

    #payment interface
    @defer.inlineCallbacks
    def jsonrpc_get_payments(self, req_data):
        """
            Return payments for events from 1hour started from timestamp.
        """
        response = yield iface_utils.get_payments(iface_proto.PaymentsRequest(req_data))
        defer.returnValue(response.to_json())

    #test interface
    @defer.inlineCallbacks
    def jsonrpc_test_get_payments(self, *args, **kwgs):
        """
            Force payments recalculation.
        """
        return_value = yield stats_tasks.force_payment_recalculation()
        defer.returnValue(True)


def configure_iface(port = iface_consts.SERVER_PORT):
    site = Site(AdPayIfaceServer())
    return reactor.listenTCP(port, site)

