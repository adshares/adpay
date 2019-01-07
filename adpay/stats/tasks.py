import logging
import time, datetime

from twisted.internet import defer, reactor

from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils
from adpay.stats import consts as stats_consts
from adpay.utils import utils as common_utils


@defer.inlineCallbacks
def _adpay_task(timestamp=None, check_payment_round_exists=True):
    """
    Task calculate payments and update user profiles only once a hour.
    :param timestamp: Timestamp for which to calculate the payments.
    :param check_payment_round_exists: Check first if the payment is already calculated.
    """
    logger = logging.getLogger(__name__)

    # As recalculate only finished hours, take timestamp from an hour before now.
    if timestamp is None:
        yield logger.warning("No timestamp found for recalculation, using current time.")
        timestamp = int(time.time())
    timestamp = common_utils.timestamp2hour(timestamp)
    nice_time = datetime.datetime.fromtimestamp(timestamp)
    yield logger.info(nice_time)
    if check_payment_round_exists:
        yield logger.info('Checking if payment round exists')
        last_round_doc = yield db_utils.get_payment_round(timestamp)
        if last_round_doc is not None:
            yield logger.warning("Payment already calculated for {0}".format(timestamp))
            yield logger.warning("Payment already calculated for {0}".format(nice_time))
            defer.returnValue(None)

    if stats_consts.CALCULATION_METHOD == 'user_value':
        # User keywords profiles update
        yield stats_utils.update_user_keywords_profiles()

    # Calculate payments for every campaign in the round
    yield logger.info("Calculating payments for campaigns.")
    _iter = yield db_utils.get_campaign_iter()
    while True:
        campaign_doc = yield _iter.next()
        if not campaign_doc:
            break

        # Clear campaign data and do not calculate.
        if campaign_doc['time_end'] < timestamp:
            yield logger.debug("Removing old campaign: {0}".format(campaign_doc['campaign_id']))
            yield stats_utils.delete_campaign(campaign_doc['campaign_id'])
            continue

        yield stats_utils.calculate_events_payments(campaign_doc['campaign_id'], timestamp)

    yield db_utils.update_payment_round(timestamp)


@defer.inlineCallbacks
def force_payment_recalculation(timestamp=None):
    """
    Recalculate payments now.
    """
    logger = logging.getLogger(__name__)
    yield logger.info("Forcing payment recalculation.")
    return_value = yield _adpay_task(timestamp, check_payment_round_exists=False)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def adpay_task(interval_seconds=60):
    """
    Recalculate payments and schedule them again in interval_seconds.
    :param interval_seconds: time after which the task will rerun.
    """
    if stats_consts.CALCULATE_PAYMENTS_PERIODICALLY:
        logger = logging.getLogger(__name__)
        yield logger.info("Running payment recalculation task.")
        yield _adpay_task()
        yield reactor.callLater(interval_seconds, adpay_task)


def configure_tasks(interval_seconds=2):
    """
    Schedule payment calculation.
    :param interval_seconds:
    """
    logger = logging.getLogger(__name__)
    logger.info("Initializing the recalculation task.")
    reactor.callLater(interval_seconds, adpay_task)
