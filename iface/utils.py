from twisted.internet import defer

from adpay.db import consts as db_consts
from adpay.db import utils as db_utils
from adpay.iface import proto as iface_proto
from adpay.iface import filters as iface_filters
from adpay.stats import utils as stats_utils


@defer.inlineCallbacks
def create_or_update_campaign(cmpobj):
    # Save changes only to database
    yield db_utils.update_campaign(
        campaign_id=cmpobj.campaign_id,
        time_start=cmpobj.time_start,
        time_end=cmpobj.time_end,
        max_cpc=cmpobj.max_cpc,
        max_cpv=cmpobj.max_cpv,
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
    from adpay.stats import cache as stats_cache

    # Do not take into account events without user_id
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

    inserted = yield db_utils.update_event(
        event_id = eventobj.event_id,
        event_type = eventobj.event_type,
        timestamp = stats_utils.timestamp2hour(int(eventobj.timestamp)),
        user_id = eventobj.user_id,
        banner_id = eventobj.banner_id,
        campaign_id=campaign_doc['campaign_id'],
        paid_amount = eventobj.paid_amount,
        keywords = eventobj.to_json()['our_keywords'],
        human_score = eventobj.human_score
    )

    # Update global keywords cache and user keyword stats.
    stats_cache.views_inc()
    for user_keyword, user_val in eventobj.our_keywords.items():
        keyword = stats_utils.genkey(user_keyword, user_val)
        stats_cache.keyword_inc(keyword)
        #yield stats_utils.update_user_keywords_stats(eventobj.user_id, keyword)

    # Update global keywords stats.
    yield stats_utils.update_keywords_stats()

    defer.returnValue(inserted)


@defer.inlineCallbacks
def get_payments(payreq):
    events_payments = []

    # Check if payments calculation is done
    round_doc = yield db_utils.get_payment_round(payreq.timestamp)
    if not round_doc:
        defer.returnValue(iface_proto.PaymentsResponse())

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
