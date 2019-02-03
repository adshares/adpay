from collections import defaultdict
import logging

from twisted.internet import defer

from adpay.db import utils as db_utils
from adpay.stats import consts as stats_consts

#: Filter separator, used in range filters (see protocol or api documentation).
FILTER_SEPARATOR = '--'


def get_default_event_payment(event_doc, max_cpc, max_cpm):
    """
    This is maximum payment. Defined in campaign or, in case of custom events (eg. conversions) in event itself.

    :param event_doc: Event document
    :param max_cpc: Cost per click
    :param max_cpm: Cost per view/impression (CPM)
    :return: Payment per event
    """
    # Default payment is 0
    event_type, event_payment = event_doc['event_type'], 0

    # For conversion, use value specified in the event
    if event_type == stats_consts.EVENT_TYPE_CONVERSION:
        event_payment = event_doc['event_value']

    # For clicks, use value specified in campaign
    elif event_type == stats_consts.EVENT_TYPE_CLICK:
        event_payment = max_cpc

    # For views/impressions, use value specified in campaign, divided by 1000 (cost per mille)
    elif event_type == stats_consts.EVENT_TYPE_VIEW:
        event_payment = max_cpm / 1000

    logger = logging.getLogger(__name__)
    logger.debug("Event type {0}, default value: {1}".format(event_type, event_payment))

    return event_payment


def filter_event(event_doc, campaign_doc, banner_doc):
    """
    Filter out events that don't pass our validation conditions.
    See adpay,stats.consts module for more details.

    :param event_doc: Event document under consideration.
    :param campaign_doc: Campaign document for this event.
    :param banner_doc: Banner document for this event.
    :return: Reason status for rejection (0 - not rejected). See `adpay.stats.consts`.
    """
    logger = logging.getLogger(__name__)
    logger.debug(event_doc)

    # Accept, but don't pay for this event
    if event_doc['event_type'] not in stats_consts.PAID_EVENT_TYPES:
        return stats_consts.EVENT_PAYMENT_ACCEPTED

    # Reject, because campaign doesn't exist
    if campaign_doc is None or campaign_doc.get('removed', False):
        logger.warning("Campaign not found: {0}".format(event_doc['campaign_id']))
        return stats_consts.EVENT_PAYMENT_REJECTED_CAMPAIGN_NOT_FOUND

    # Reject, because banner doesn't exist
    if banner_doc is None:
        logger.warning("Banner not found: {0}".format(event_doc['banner_id']))
        return stats_consts.EVENT_PAYMENT_REJECTED_BANNER_NOT_FOUND

    # Reject, because human score is too low
    if event_doc['human_score'] <= stats_consts.HUMAN_SCORE_THRESHOLD:
        return stats_consts.EVENT_PAYMENT_REJECTED_HUMAN_SCORE_TOO_LOW

    # Reject, because event keywords don't pass campaign filters (invalid targeting)
    if stats_consts.VALIDATE_CAMPAIGN_FILTERS and not validate_keywords(campaign_doc['filters'],
                                                                        event_doc['our_keywords']):
        return stats_consts.EVENT_PAYMENT_REJECTED_INVALID_TARGETING

    # Accept otherwise
    return stats_consts.EVENT_PAYMENT_ACCEPTED


def validate_keywords(filters_dict, keywords):
    """
    Validate campaign filters.

    :param filters_dict: Required and excluded keywords
    :param keywords: Keywords being tested.
    :return: True or False
    """
    return validate_require_keywords(filters_dict, keywords) and validate_exclude_keywords(filters_dict, keywords)


def validate_bounds(bounds, keyword_values):
    """
    Validate if keyword value is correct.
    Value is between bounds (bounds has two elements)
    or
    Value is equal to bounds (default, bounds is assumed to have one element)

    :param bounds: Iterable (1 or 2 elements)
    :param keyword_values: Keyword value being tested.
    :return: True or False
    """
    for kv in keyword_values:
        if (len(bounds) == 2 and bounds[0] < kv < bounds[1]) \
                or (bounds[0] == kv):
            return True
    return False


def validate_require_keywords(filters_dict, keywords):
    """
    Validate campaign require filters.

    :param filters_dict: Required and excluded keywords
    :param keywords: Keywords being tested.
    :return: True or False
    """
    for category_keyword, ckvs in filters_dict.get('require').items():
        if category_keyword not in keywords:
            return False

        for category_keyword_value in ckvs:
            bounds = category_keyword_value.split(FILTER_SEPARATOR)
            if validate_bounds(bounds, keywords.get(category_keyword)):
                break
        else:
            return False

    return True


def validate_exclude_keywords(filters_dict, keywords):
    """
    Validate campaign exclude filters.

    :param filters_dict: Required and excluded keywords
    :param keywords: Keywords being tested.
    :return: True or False
    """
    for category_keyword, ckvs in filters_dict.get('exclude').items():
        if category_keyword not in keywords:
            continue

        for category_keyword_value in ckvs:
            bounds = category_keyword_value.split(FILTER_SEPARATOR)
            if validate_bounds(bounds, keywords.get(category_keyword)):
                return False

    return True


@defer.inlineCallbacks
def update_events_payments(campaign_doc, timestamp, uid, user_budget):
    """
    Update or create event payments in the database by dividing user budget among events.

    :param campaign_doc: Campaign document
    :param timestamp: Timestamp for the time period of calculation
    :param uid: User identifier
    :param user_budget: User budget
    :return:
    """
    logger = logging.getLogger(__name__)

    # Get all events for chosen campaign within chosen time period for a chosen user
    user_events_iter = yield db_utils.get_events_per_user_iter(campaign_doc['campaign_id'], timestamp, uid)
    while True:
        event_doc = yield user_events_iter.next()
        if event_doc is None:
            break

        event_type = event_doc['event_type']

        banner_doc = yield db_utils.get_banner(event_doc['banner_id'])

        payment_reason = filter_event(event_doc, campaign_doc, banner_doc)

        # Pay when not rejected
        if not payment_reason and event_type in stats_consts.PAID_EVENT_TYPES:
            event_value = int(user_budget[event_type]['event_value'])
        else:
            event_value = 0

        # Save to database
        yield db_utils.update_event_payment(campaign_doc['campaign_id'],
                                            timestamp,
                                            event_doc['event_id'],
                                            event_value,
                                            payment_reason)

        yield logger.debug("New payment ({0}, {1}): {2}, {3}. {4}, {5}".format(campaign_doc['campaign_id'],
                                                                               timestamp,
                                                                               event_doc['event_id'],
                                                                               event_type,
                                                                               event_value,
                                                                               payment_reason))


@defer.inlineCallbacks
def create_user_budget(campaign_doc, timestamp, uid):
    """
    Calculate individual user budgets.

    User budget dictionary default values for each event type are:

        {
        'default_value': 0.0,  # Default value for this event type
        'event_value': 0.0,    # Calculated event value (used later on)
        'num': 0,              # Number of events of this time in the time period
        'share': 0.0           # Share of this user for this event type in the time period
        }

    :param campaign_doc: Campaign document
    :param timestamp: Timestamp (last hour)
    :param uid:
    :return: User budget dictionary
    """
    logger = logging.getLogger(__name__)

    user_budget = defaultdict(lambda: dict(default_value=0,
                                           event_value=0.0,
                                           num=0,
                                           share=0.0))

    if campaign_doc is None:
        defer.returnValue(user_budget)

    user_events_iter = yield db_utils.get_events_per_user_iter(campaign_doc['campaign_id'], timestamp, uid)
    while True:
        event_doc = yield user_events_iter.next()
        if not event_doc:
            break

        event_type = event_doc['event_type']

        banner_doc = yield db_utils.get_banner(event_doc['banner_id'])
        if not filter_event(event_doc, campaign_doc, banner_doc) and event_type in stats_consts.PAID_EVENT_TYPES:

            user_budget[event_type]['num'] += 1
            user_budget[event_type]['default_value'] += get_default_event_payment(event_doc,
                                                                                  campaign_doc['max_cpc'],
                                                                                  campaign_doc['max_cpm'])
        else:
            logger.warning('Event type for event_id: ' + event_doc['event_id'] + ' not included in payment calculation.')

    for event_type in stats_consts.PAID_EVENT_TYPES:
        if user_budget[event_type]['num'] > 0:
            user_budget[event_type]['default_value'] /= user_budget[event_type]['num']

    yield logger.debug(user_budget)
    defer.returnValue(user_budget)


@defer.inlineCallbacks
def delete_campaign(campaign_id):
    """
    Delete campaign document and all campaign banners

    :param campaign_id:
    :return:
    """
    logger = logging.getLogger(__name__)
    yield db_utils.delete_campaign(campaign_id)
    yield db_utils.delete_campaign_banners(campaign_id)
    yield logger.info("Removed campaign {0} with banners.".format(campaign_id))
