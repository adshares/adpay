from twisted.internet import defer

from adpay.db import consts as db_consts
from adpay.db import utils as db_utils
from adpay.iface import proto as iface_proto
from adpay.iface import filters as iface_filters


@defer.inlineCallbacks
def create_or_update_campaign(cmpobj):
    # Save changes only to database
    campaign_doc = cmpobj.to_json()
    del campaign_doc['banners']
    yield db_utils.update_campaign(campaign_doc)

    # Delete previous banners
    yield db_utils.delete_campaign_banners(cmpobj.campaign_id)

    for banner in cmpobj.banners:
        banner_doc = banner.to_json()
        banner_doc['campaign_id'] = cmpobj.campaign_id
        yield db_utils.update_banner(banner_doc)


@defer.inlineCallbacks
def delete_campaign(campaign_id):
    # Save changes only to database
    yield db_utils.delete_campaign(campaign_id)
    yield db_utils.delete_campaign_banners(campaign_id)


@defer.inlineCallbacks
def add_event(eventobj):
    # We do not take into account events without user_id
    if not eventobj.user_id:
        defer.returnValue(None)

    # Conversion event must send max paid amount
    if eventobj.event_type == db_consts.EVENT_TYPE_CONVERSION:
        if not eventobj.paid_amount:
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

    inserted = yield db_utils.update_event(eventobj.to_json())
    defer.returnValue(inserted)


@defer.inlineCallbacks
def get_payments(payreq):
    payments = yield db_utils.get_payments(payreq.timestamp)
    if not payments:
        defer.returnValue(iface_proto.PaymentsResponse())

    events_payments = []
    for event_id, amount in payments['events'].items():
        events_payments.append(iface_proto.SinglePaymentResponse(event_id=event_id, amount = amount))
    defer.returnValue(iface_proto.PaymentsResponse(payments=events_payments))
