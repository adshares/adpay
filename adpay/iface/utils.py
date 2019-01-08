import logging

from twisted.internet import defer

from adpay.db import utils as db_utils
from adpay.iface import proto as iface_proto
from adpay.stats import consts as stats_consts, utils as stats_utils
from adpay.utils import utils as common_utils


class PaymentsNotCalculatedException(Exception):
    pass


@defer.inlineCallbacks
def create_or_update_campaign(cmpobj):
    """
    Create or update campaign. Removes old banners and adds new ones.

    :param cmpobj:
    :return:
    """

    logger = logging.getLogger(__name__)

    if cmpobj.max_cpm is not None:
        cmpobj.max_cpm = cmpobj.max_cpm/1000.0

    yield logger.info("Updating campaign: {0}".format(cmpobj.campaign_id))
    yield logger.debug("Updating campaign: {0}".format(cmpobj))

    campaign_doc = cmpobj.to_json()
    del campaign_doc['banners']
    yield db_utils.update_campaign(campaign_doc)

    # Delete previous banners
    yield logger.info("Removing campaign banners for: {0}".format(cmpobj.campaign_id))
    yield db_utils.delete_campaign_banners(cmpobj.campaign_id)

    # Update banners for campaign
    yield logger.info("Updating banners for campaign: {0}".format(cmpobj.campaign_id))

    for banner in cmpobj.banners:
        banner_doc = banner.to_json()
        banner_doc['campaign_id'] = cmpobj.campaign_id
        yield logger.debug("Updating banner: {0}".format(banner))
        yield db_utils.update_banner(banner_doc)


@defer.inlineCallbacks
def delete_campaign(campaign_id):
    """
    Remove campaign.

    :param campaign_id:
    :return:
    """
    logger = logging.getLogger(__name__)

    # Delete campaign banners
    yield logger.info("Removing campaign banners for {0}".format(campaign_id))
    yield db_utils.delete_campaign_banners(campaign_id)

    # Delete campaign object

    yield logger.info("Removing campaign for {0}".format(campaign_id))
    yield db_utils.delete_campaign(campaign_id)


@defer.inlineCallbacks
def add_event(eventobj):
    """
    Insert event object into the database, if conditions are met:
    * user_id must be provided
    * if event is a conversion, event_value must be provided
    * banner must be in the database
    * campaign must be in the database
    * campaign filters must match our keywords

    Update keywords and view statistics.

    :param eventobj:
    :return:
    """
    logger = logging.getLogger(__name__)

    # Do not take into account events without user_id
    if not eventobj.user_id:
        yield logger.warning("Ignoring event update - No user identifier found.")
        defer.returnValue(None)

    # Conversion event must send max paid amount
    if eventobj.event_type == stats_consts.EVENT_TYPE_CONVERSION and not eventobj.event_value:
        yield logger.warning("Ignoring event update - No event value for conversion event.")
        defer.returnValue(None)

    # Events are filtered by the campaign filters
    banner_doc = yield db_utils.get_banner(eventobj.banner_id)
    if not banner_doc:
        yield logger.warning("Ignoring event update - No banner found.")
        defer.returnValue(None)

    campaign_doc = yield db_utils.get_campaign(banner_doc['campaign_id'])
    if not campaign_doc:
        yield logger.warning("Ignoring event update - No campaign found.")
        defer.returnValue(None)

    #if not iface_filters.validate_filters(campaign_doc['filters'], eventobj.our_keywords):
    #    yield logger.warning("Ignoring event update - Keywords not validated.")
    #    defer.returnValue(None)

    event_doc = eventobj.to_json()
    event_doc['campaign_id'] = campaign_doc['campaign_id']
    event_doc['keywords'] = event_doc['our_keywords']

    inserted = yield db_utils.update_event(event_doc)

    # Update global keywords cache and user keyword stats.
    view_view_keywords = []
    for user_keyword, user_val in eventobj.our_keywords.items():
        view_view_keywords.append(common_utils.genkey(user_keyword, user_val))
    yield stats_utils.add_view_keywords(eventobj.user_id, view_view_keywords)

    defer.returnValue(inserted)


@defer.inlineCallbacks
def get_payments(payreq):
    """
    1. Check if payment calculation for last round is done.
    2. Get payment interations per event

    :param payreq:
    :return:
    """
    logger = logging.getLogger(__name__)
    yield logger.info("Calculating payments.")
    events_payments = []

    # Check if payments calculation is done
    round_doc = yield db_utils.get_payment_round(payreq.timestamp)
    if not round_doc:
        yield logger.error("Payments not calculated yet.")
        raise PaymentsNotCalculatedException()

    # Collect events and theirs payment and respond to request
    _iter = yield db_utils.get_payments_iter(payreq.timestamp)
    while True:
        payment_doc = yield _iter.next()
        if not payment_doc:
            break

        events_payments.append(
            iface_proto.SinglePaymentResponse(event_id=payment_doc['event_id'],
                                              amount=payment_doc['payment']))

    yield logger.debug(events_payments)
    defer.returnValue(iface_proto.PaymentsResponse(payments=events_payments))
