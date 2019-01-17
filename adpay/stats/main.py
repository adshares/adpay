import logging

from twisted.internet import defer

from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts, utils as stats_utils


@defer.inlineCallbacks
def calculate_events_payments(campaign_doc, timestamp):
    """

    :param campaign_doc: Campaign document
    :param timestamp: Timestamp for time period
    :return:
    """
    logger = logging.getLogger(__name__)
    logger.debug('Updating event payments for {0} {1}'.format(campaign_doc['campaign_id'], timestamp))

    # We don't pay for the campaign-period by default
    total_payments = 0.0
    user_data = {}

    # Get all users for this campaign-period
    uids = yield db_utils.get_distinct_users_from_events(campaign_doc['campaign_id'], timestamp)
    if uids is None:
        logger.debug("No users found!")
        defer.returnValue(None)

    for uid in uids:
        user_data[uid] = {'total': 0.0,
                          'budget': {}}
        # Get default budget
        user_data[uid]['budget'] = yield stats_utils.create_user_budget(campaign_doc, timestamp, uid)

        # Calculate maximum amount to be paid to a user
        for event_type in stats_consts.PAID_EVENT_TYPES:
            user_data[uid]['total'] += user_data[uid]['budget'][event_type]['default_value']

        logger.debug(uid)

        # Get sum of all payments
        total_payments += user_data[uid]['total']

    # Calculate our modifier for maximum payment for this campaign-period
    if total_payments > 0:
        budget_modifier = min([total_payments, campaign_doc['budget']]) / total_payments

    for uid in uids:

        if total_payments > 0:

            for event_type in stats_consts.PAID_EVENT_TYPES:
                ube = user_data[uid]['budget'][event_type]
                ube['share'] = budget_modifier * user_data[uid]['total'] / total_payments

                if ube['share'] > 0:
                    ube['event_value'] = int(min([ube['default_value'],
                                                  ube['share'] * ube['default_value']]))
                user_data[uid]['budget'][event_type] = ube

        yield stats_utils.update_events_payments(campaign_doc, timestamp, uid, user_data[uid]['budget'])
