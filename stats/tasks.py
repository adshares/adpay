from twisted.internet import defer, reactor
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils
from adpay.stats import consts as stats_consts
import time


@defer.inlineCallbacks
def _calculate_payments(timestamp):
    campaign_iter = db_utils.get_campaign_iter()
    while True:
        campaign_doc = yield campaign_iter.next()
        if not campaign_doc:
            break

        # Do not calculate campaign which are finished
        if campaign_doc['end_timestamp'] <= timestamp - stats_consts.SECONDS_PER_HOUR:
            continue

        yield stats_utils.calculate_events_payments(campaign_doc['campaign_id'], timestamp)


@defer.inlineCallbacks
def calculate_payments():
    """
        Task calculate payments and update user profiles only once a hour.
    """

    # User keywords profiles update
    yield stats_utils.update_user_keywords_profiles()


    # Determine which timestamp should be calculated
    current_round = stats_consts.SECONDS_PER_HOUR*(int(time.time())/stats_consts.SECONDS_PER_HOUR)
    last_round_timestamp = current_round - stats_consts.SECONDS_PER_HOUR

    # If last calculated timesamp round find in database, replace last_round_timestamp
    last_round_doc = yield db_utils.get_last_round()
    if last_round_doc:
        last_round_timestamp = last_round_doc['timestamp']


    # If last_round_timestamp is not found in database, calculate last hour only.
    while True:
        last_round_timestamp += stats_consts.SECONDS_PER_HOUR
        if last_round_timestamp > current_round:
            break

        yield _calculate_payments(last_round_timestamp)
        yield db_utils.update_payment_round(last_round_timestamp)


def calculate_payments_task(interval_seconds=2):
    #Recalculate payments every hour.

    calculate_payments()
    reactor.callLater(interval_seconds, calculate_payments_task)


def configure_tasks(interval_seconds=2):
    reactor.callLater(interval_seconds, calculate_payments_task)
