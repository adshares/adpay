from twisted.internet import reactor, defer
from twisted.web.server import Site

from fastjsonrpc.server import JSONRPCServer

from adpay.iface import consts as iface_consts
from adpay.iface import utils as iface_utils
from adpay.iface import proto as iface_proto


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
        resposne = yield iface_utils.get_payments(iface_proto.PaymentsRequest(req_data))
        defer.returnValue(resposne.to_json())


def configure_iface(port = iface_consts.SERVER_PORT):
    site = Site(AdPayIfaceServer())
    return reactor.listenTCP(port, site)

