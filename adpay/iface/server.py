import logging
from datetime import datetime

from fastjsonrpc.jsonrpc import JSONRPCError
from fastjsonrpc.server import JSONRPCServer
from jsonobject.exceptions import BadValueError
from twisted.internet import defer, reactor
from twisted.web.server import Site

from adpay.iface import consts as iface_consts, proto as iface_proto, utils as iface_utils
from adpay.stats import tasks as stats_tasks


class AdPayIfaceServer(JSONRPCServer):
    """
    JSON-RPC endpoint.
    """
    def __init__(self):
        JSONRPCServer.__init__(self)
        self.logger = logging.getLogger(__name__)

    # campaign interface
    @defer.inlineCallbacks
    def jsonrpc_campaign_update(self, *campaigns):
        """
        JSON-RPC campaign_update method handler.

        :param campaigns: Variable number of campaigns. See *args.
        :return: True
        """
        if not campaigns:
            yield self.logger.warning("No campaign data to update.")
        else:
            for campaign_data in campaigns:
                yield self.logger.debug("Received campaign update: {0}".format(campaign_data))
                try:
                    yield iface_utils.create_or_update_campaign(iface_proto.CampaignObject(campaign_data))
                except BadValueError as e:
                    raise JSONRPCError(e, iface_consts.INVALID_OBJECT)

        defer.returnValue(True)

    @defer.inlineCallbacks
    def jsonrpc_campaign_delete(self, *campaign_ids):
        """
        JSON-RPC campaign_delete method handler.

        :param campaign_ids: Variable number of campaign identifiers. See *args.
        :return: True
        """
        if not campaign_ids:
            yield self.logger.warning("No campaign id to remove.")
        else:
            for campaign_id in campaign_ids:
                yield self.logger.info("Received campaign removal request: {0}".format(campaign_id))
                yield iface_utils.delete_campaign(campaign_id)
        defer.returnValue(True)

    # events interface
    @defer.inlineCallbacks
    def jsonrpc_add_events(self, *events):
        """
        JSON-RPC add_events method handler.

        :param events: Variable number of events. See *args.
        :return: True
        """
        if not events:
            yield self.logger.warning("No event data to add.")
        else:
            for event_data in events:
                yield self.logger.debug("Received event data: {0}".format(event_data))
                try:
                    yield iface_utils.add_event(iface_proto.EventObject(event_data))
                    yield self.logger.debug("Received event time: {0}".format(datetime.fromtimestamp(event_data['timestamp'])))
                except BadValueError as e:
                    raise JSONRPCError(e, iface_consts.INVALID_OBJECT)
        defer.returnValue(True)

    # payment interface
    @defer.inlineCallbacks
    def jsonrpc_get_payments(self, payment_request):
        """
        JSON-RPC get_payments method handler

        Return payments for events from 1hour started before timestamp.

        :param payment_request: Payment request
        :return: Response in JSON.
        """
        try:
            response = yield iface_utils.get_payments(iface_proto.PaymentsRequest(payment_request))
            yield self.logger.info("Payments request received and responded with {0} elements.".format(len(response.payments)))
        except iface_utils.PaymentsNotCalculatedException:
            yield self.logger.error("Payments request received, but payments not calculated yet.")
            raise JSONRPCError("Payments not calculated yet.", iface_consts.PAYMENTS_NOT_CALCULATED_YET)
        except BadValueError as e:
            raise JSONRPCError(e, iface_consts.INVALID_OBJECT)
        defer.returnValue(response.to_json())

    # test interface
    @defer.inlineCallbacks
    def jsonrpc_debug_force_payment_recalculation(self, payment_request):
        """
        Force payments recalculation.

        :return: True or False (if disabled)
        """
        if iface_consts.DEBUG_ENDPOINT:
            try:
                pay_request = iface_proto.PaymentsRequest(payment_request)
                yield stats_tasks.force_payment_recalculation(pay_request.timestamp)
                defer.returnValue(True)
            except BadValueError as e:
                raise JSONRPCError(e, iface_consts.INVALID_OBJECT)
        else:
            defer.returnValue(False)


def configure_iface(port=iface_consts.SERVER_PORT):
    """
    Set up Twisted reactor to listen on TCP.

    :param port: Listening port.
    :return: Listening reactor.
    """
    logger = logging.getLogger(__name__)
    logger.info("Initializing AdPay interface server on port: {0}.".format(port))
    site = Site(AdPayIfaceServer())
    return reactor.listenTCP(port, site)
