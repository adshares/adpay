from twisted.internet import reactor
from twisted.web.server import Site

from fastjsonrpc.server import JSONRPCServer

from adpay.iface import config as iface_config
from adpay.iface import utils as iface_utils
from adpay.iface import proto as iface_proto


class AdPayIfaceServer(JSONRPCServer):
    #campaign interface
    def jsonrpc_campaign_update(self, *campaign_data_list):
        for campaign_data in campaign_data_list:
            iface_utils.create_or_update_campaign(iface_proto.CamapaignObject(campaign_data))
        return True

    def jsonrpc_campaign_delete(self, *campaign_id_list):
        for campaign_id in campaign_id_list:
            iface_utils.delete_campaign(campaign_id)
        return True

    #events interface
    def jsonrpc_add_events(self, *event_data_list):
        for event_data in event_data_list:
            iface_utils.add_event(iface_proto.EventObject(event_data))
        return True

    #payment interface
    def jsonrpc_get_payments(self, req_data):
        """
            Return payments for events from 1hour started from timestamp.
        """

        def to_json(request):
            return request.to_json()

        request = iface_utils.get_payments(iface_proto.PaymentsRequest(req_data))
        request.addCallback(to_json)
        return request


def configure_iface(port = iface_config.SERVER_PORT):
    site = Site(AdPayIfaceServer())
    return reactor.listenTCP(port, site)

