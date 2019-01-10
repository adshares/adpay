import datetime
import logging
import time

from twisted.internet import defer, reactor

from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts, utils as stats_utils
from adpay.utils import utils as common_utils


@defer.inlineCallbacks
def _adpay_task(timestamp=None, ignore_existing_payment_calculations=False):
    """
    Task calculate payments and update user profiles only once a hour.
    :param timestamp: Timestamp for which to calculate the payments.
    :param ignore_existing_payment_calculations: Check first if the payment is already calculated.
    """
    logger = logging.getLogger(__name__)

    # As recalculate only finished hours, take timestamp from an hour before now.
    if timestamp is None:
        yield logger.warning("No timestamp found for recalculation, using current time.")
        timestamp = int(time.time())

    timestamp = common_utils.timestamp2hour(timestamp)
    nice_time = datetime.datetime.fromtimestamp(timestamp)

    if not ignore_existing_payment_calculations:
        last_round_doc = yield db_utils.get_payment_round(timestamp)
        if last_round_doc is not None:
            yield logger.warning("Payment already calculated for {0} ({1})".format(nice_time, timestamp))
            defer.returnValue(None)

    if stats_consts.CALCULATION_METHOD == 'user_value':
        # User keywords profiles update
        yield stats_utils.update_user_keywords_profiles()

    # Calculate payments for every campaign in the round
    _iter = yield db_utils.get_campaign_iter()
    while True:
        campaign_doc = yield _iter.next()
        logger.debug(campaign_doc)
        if not campaign_doc:
            break

        # Clear campaign data and do not calculate.
        if campaign_doc['time_end'] < timestamp:
            yield logger.debug("Removing old campaign: {0}".format(campaign_doc['campaign_id']))
            yield stats_utils.delete_campaign(campaign_doc['campaign_id'])
            continue

        yield stats_utils.calculate_events_payments(campaign_doc, timestamp)
        yield logger.info("Calculated payments for {0} ({1})".format(nice_time, timestamp))

    yield db_utils.update_payment_round(timestamp)


@defer.inlineCallbacks
def force_payment_recalculation(timestamp=None):
    """
    Recalculate payments now.
    """
    logger = logging.getLogger(__name__)
    yield logger.info("Forcing payment recalculation.")
    return_value = yield _adpay_task(timestamp, ignore_existing_payment_calculations=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def adpay_task(interval_seconds=60):
    """
    Recalculate payments and schedule them again in interval_seconds.
    :param interval_seconds: time after which the task will rerun.
    """
    logger = logging.getLogger(__name__)

    if stats_consts.CALCULATE_PAYMENTS_PERIODICALLY:
        yield logger.info("Running payment recalculation task.")
        yield _adpay_task()
        yield reactor.callLater(interval_seconds, adpay_task)
    else:
        yield logger.info('Periodical recalculation disabled.')


def configure_tasks(interval_seconds=2):
    """
    Schedule payment calculation.
    :param interval_seconds:
    """
    logger = logging.getLogger(__name__)
    reactor.callLater(interval_seconds, adpay_task)
