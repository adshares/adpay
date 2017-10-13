from twisted.internet import defer
from adpay.db import utils as db_utils
from adpay.iface import protocol as iface_proto


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
    yield db_utils.update_event(eventobj.to_json())


@defer.inlineCallbacks
def get_payments(payreq):
    payments = yield db_utils.get_payments(payreq.timestamp)
    if not payments:
        defer.returnValue(iface_proto.PaymentsResponse())

    events_payments = []
    for event_id, amount in payments['events'].items():
        events_payments.append(iface_proto.SinglePaymentResponse(event_id=event_id, amount = amount))
    defer.returnValue(iface_proto.PaymentsResponse(payments=events_payments))