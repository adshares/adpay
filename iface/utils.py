import copy

from twisted.internet import defer

from adpay.db import consts as db_consts
from adpay.db import utils as db_utils
from adpay.iface import proto as iface_proto
from adpay.iface import filters as iface_filters
from adpay.stats import utils as stats_utils
from adpay.utils import common as common_utils


class PaymentsNotCalculatedException(Exception):
    pass


@defer.inlineCallbacks
def create_or_update_campaign(cmpobj):
    if cmpobj.max_cpm is not None:
        cmpobj.max_cpm = cmpobj.max_cpm/1000.0

    # Save changes only to database
    yield db_utils.update_campaign(
        campaign_id=cmpobj.campaign_id,
        time_start=cmpobj.time_start,
        time_end=cmpobj.time_end,
        max_cpc=cmpobj.max_cpc,
        max_cpm=cmpobj.max_cpm,
        budget=cmpobj.budget,
        filters=cmpobj.to_json()['filters']
    )

    # Delete previous banners
    yield db_utils.delete_campaign_banners(cmpobj.campaign_id)

    # Update banners for campaign
    for banner in cmpobj.banners:
        yield db_utils.update_banner(banner.banner_id, cmpobj.campaign_id)


@defer.inlineCallbacks
def delete_campaign(campaign_id):
    # Delete campaign banners
    yield db_utils.delete_campaign_banners(campaign_id)

    # Delete campaign object
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
    # Do not take into account events without user_id
    if not eventobj.user_id:
        defer.returnValue(None)

    # Conversion event must send max paid amount
    if eventobj.event_type == db_consts.EVENT_TYPE_CONVERSION and not eventobj.event_value:
            defer.returnValue(None)

    # Events are filtered by the campaign filters
    banner_doc = yield db_utils.get_banner(eventobj.banner_id)
    if not banner_doc:
        defer.returnValue(None)

    campaign_doc = yield db_utils.get_campaign(banner_doc['campaign_id'])
    if not campaign_doc:
        defer.returnValue(None)

    if not iface_filters.validate_filters(campaign_doc['filters'], eventobj.our_keywords):
        defer.returnValue(None)

    new_event_obj = copy.copy(eventobj)
    new_event_obj.campaign_id = campaign_doc['campaign_id']
    new_event_obj.keywords = eventobj.to_json()['our_keywords']

    inserted = yield db_utils.update_event(new_event_obj)

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
    events_payments = []

    # Check if payments calculation is done
    round_doc = yield db_utils.get_payment_round(payreq.timestamp)
    if not round_doc:
        raise PaymentsNotCalculatedException()

    # Collect events and theirs payment and respond to request
    _iter = yield db_utils.get_payments_iter(payreq.timestamp)
    while True:
        payment_doc = yield _iter.next()
        if not payment_doc:
            break

        events_payments.append(
            iface_proto.SinglePaymentResponse(
                event_id=payment_doc['event_id'],
                amount=payment_doc['payment']
            ))

    defer.returnValue(iface_proto.PaymentsResponse(payments=events_payments))
