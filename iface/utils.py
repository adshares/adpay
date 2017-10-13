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


def add_event(eventobj):
    pass


def get_payments(payreq):
    payments = []
    for event_id, amount in [(122, 33), (122, 2333)]:
        payments.append(iface_proto.SinglePaymentResponse(event_id=event_id, amount = amount))
    return iface_proto.PaymentsResponse(payments=payments)