import logging

from twisted.internet import reactor, defer
from twisted.web.server import Site

from fastjsonrpc.server import JSONRPCServer
from fastjsonrpc.jsonrpc import JSONRPCError

from adpay.iface import consts as iface_consts
from adpay.iface import utils as iface_utils
from adpay.iface import proto as iface_proto
from adpay.stats import tasks as stats_tasks


class AdPayIfaceServer(JSONRPCServer):

    def __init__(self):
        JSONRPCServer.__init__(self)
        self.logger = logging.getLogger(__name__)

    """
    JSON-RPC endpoint.
    """
    # campaign interface
    @defer.inlineCallbacks
    def jsonrpc_campaign_update(self, *campaign_data_list):
        """
        JSON-RPC campaign_update method handler.

        :param campaign_data_list:
        :return: True
        """
        if not campaign_data_list:
            yield self.logger.warning("No campaign data to update.")
        else:
            for campaign_data in campaign_data_list:
                yield self.logger.debug("Campaign update: {0}".format(campaign_data))
                yield iface_utils.create_or_update_campaign(iface_proto.CamapaignObject(campaign_data))
        defer.returnValue(True)

    @defer.inlineCallbacks
    def jsonrpc_campaign_delete(self, *campaign_id_list):
        """
        JSON-RPC campaign_delete method handler.

        :param campaign_id_list: List of campaign identifiers.
        :return: True
        """
        if not campaign_id_list:
            yield self.logger.warning("No campaign id to remove.")
        else:
            for campaign_id in campaign_id_list:
                yield self.logger.info("Campaign removal: {0}".format(campaign_id))
                yield iface_utils.delete_campaign(campaign_id)
        defer.returnValue(True)

    # events interface
    @defer.inlineCallbacks
    def jsonrpc_add_events(self, *event_data_list):
        """
        JSON-RPC add_events method handler.

        :param event_data_list: List of impression data.
        :return: True
        """
        if not event_data_list:
            yield self.logger.warning("No event data to add.")
        else:
            for event_data in event_data_list:
                yield self.logger.debug("Adding event data: {0}".format(event_data))
                yield iface_utils.add_event(iface_proto.EventObject(event_data))
        defer.returnValue(True)

    # payment interface
    @defer.inlineCallbacks
    def jsonrpc_get_payments(self, req_data):
        """
        JSON-RPC get_payments method handler

        Return payments for events from 1hour started from timestamp.

        :param req_data:
        :return: Response in JSON.
        """
        try:
            response = yield iface_utils.get_payments(iface_proto.PaymentsRequest(req_data))
            yield self.logger.info("Payments not calculated.")
        except iface_utils.PaymentsNotCalculatedException:
            yield self.logger.error("Payments not calculated yet.")
            raise JSONRPCError("Payments not calculated yet.")

        defer.returnValue(response.to_json())

    # test interface
    @defer.inlineCallbacks
    def jsonrpc_test_get_payments(self):
        """
        Force payments recalculation.

        :return: True
        """
        yield stats_tasks.force_payment_recalculation()
        defer.returnValue(True)


def configure_iface(port=iface_consts.SERVER_PORT):
    """
    Set up Twisted reactor to listen on TCP.

    :param port: Listening port.
    :return: Listening reactor.
    """
    logger = logging.getLogger(__name__)
    logger.info("Initializing interface server.")
    site = Site(AdPayIfaceServer())
    return reactor.listenTCP(port, site)
