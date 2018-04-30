from twisted.internet import defer, reactor
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils
from adpay.utils import utils as common_utils
import time


@defer.inlineCallbacks
def _adpay_task(timestamp=None, check_payment_round_exists=True):
    """
        Task calculate payments and update user profiles only once a hour.
    """
    # As recalculate only finished hours, take timestamp from an hour before now.
    if timestamp is None:
        timestamp = int(time.time())
    timestamp = common_utils.timestamp2hour(timestamp)

    if not check_payment_round_exists:
        last_round_doc = yield db_utils.get_payment_round(timestamp)
        if last_round_doc is not None:
            defer.returnValue(None)

    # User keywords profiles update
    yield stats_utils.update_user_keywords_profiles()

    # Calculate payments for every campaign in the round
    _iter = yield db_utils.get_campaign_iter()
    while True:
        campaign_doc = yield _iter.next()
        if not campaign_doc:
            break

        # Clear campaign data and do not calculate.
        if campaign_doc['time_end'] < timestamp:
            yield stats_utils.delete_campaign(campaign_doc['campaign_id'])
            continue

        yield stats_utils.calculate_events_payments(campaign_doc['campaign_id'], timestamp)

    yield db_utils.update_payment_round(timestamp)


@defer.inlineCallbacks
def force_payment_recalculation():
    return_value = yield _adpay_task(check_payment_round_exists=False)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def adpay_task(interval_seconds=60):

    yield _adpay_task()
    yield reactor.callLater(interval_seconds, adpay_task)


def configure_tasks(interval_seconds=2):
    reactor.callLater(interval_seconds, adpay_task)
