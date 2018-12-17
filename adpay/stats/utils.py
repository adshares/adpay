import logging

from twisted.internet import defer

from adpay.db import consts as db_consts, utils as db_utils
from adpay.stats import cache as stats_cache, consts as stats_consts

#: Deferred lock for updating events
ADD_EVENT_LOCK = defer.DeferredLock()


@defer.inlineCallbacks
def get_user_profile_keywords(user_id):
    """

    :param user_id: User identifier
    :return: Unique list of keywords cut off at `stats_consts.MAX_USER_KEYWORDS_IN_PROFILE`
    """
    logger = logging.getLogger(__name__)
    user_profile_doc = yield db_utils.get_user_profile(user_id)
    if not user_profile_doc:
        yield logger.warning("User profile keywords not found.")
        defer.returnValue(None)

    yield logger.debug("User keyword profile for {0} limited to: {1}".format(user_id, stats_consts.MAX_USER_KEYWORDS_IN_PROFILE))
    yield logger.debug(list(set(user_profile_doc['profile'].keys()))[:stats_consts.MAX_USER_KEYWORDS_IN_PROFILE])

    defer.returnValue(list(set(user_profile_doc['profile'].keys()))[:stats_consts.MAX_USER_KEYWORDS_IN_PROFILE])


@defer.inlineCallbacks
def get_users_similarity(user1_id, user2_id):
    """
    Return user similarity value [0, 1] taking into account keywords similarity.
    1 - user1 is recognised as user2
    0 - user1 and user2 are completely different.

    :param user1_id: User identifier
    :param user2_id: User identifier
    :return: User similarity value [0, 1]
    """
    logger = logging.getLogger(__name__)
    yield logger.info("Measuring user similarity.")
    if user1_id == user2_id:
        yield logger.warning("It's the same user!")
        defer.returnValue(1.0)

    user1_profile_keywords = yield get_user_profile_keywords(user1_id)
    user2_profile_keywords = yield get_user_profile_keywords(user2_id)

    user1_profile_keywords = user1_profile_keywords or []
    user2_profile_keywords = user2_profile_keywords or []

    len_common_keywords = len(set(user1_profile_keywords).intersection(set(user2_profile_keywords)))

    yield logger.debug("{0} common keywords found".format(len_common_keywords))
    yield logger.debug("Similarity score value: {0}".format(1.0 * len_common_keywords / stats_consts.MAX_USER_KEYWORDS_IN_PROFILE))

    defer.returnValue(1.0 * len_common_keywords / stats_consts.MAX_USER_KEYWORDS_IN_PROFILE)


def reverse_insort(a, x, lo=0, hi=None):
    """
    (https://stackoverflow.com/a/2247433 CC-BY-SA)

    Insert item x in list a, and keep it reverse-sorted assuming a
    is reverse-sorted.

    If x is already in a, insert it to the right of the rightmost x.

    Optional args lo (default 0) and hi (default len(a)) bound the
    slice of a to be searched.

    In place operation.

    :param a: List we are sorting.
    :param x: Item we insert.
    :param lo: Lower bound of slice we're scanning.
    :param hi: Higher bound of slice we're scanning.
    """
    if lo < 0:
        raise ValueError('lo must be non-negative')

    if hi is None:
        hi = len(a)

    while lo < hi:
        mid = (lo+hi)//2
        if x > a[mid]:
            hi = mid
        else:
            lo = mid+1

    a.insert(lo, x)


def get_event_max_payment(event_doc, max_cpc, max_cpm):
    """

    :param event_doc: Event document
    :param max_cpc: Value per click
    :param max_cpm: Value per view
    :return: Payment per event
    """
    event_type, event_payment = event_doc['event_type'], 0
    if event_type == db_consts.EVENT_TYPE_CONVERSION:
        event_payment = event_doc['event_value']
    elif event_type == db_consts.EVENT_TYPE_CLICK:
        event_payment = max_cpc
    elif event_type == db_consts.EVENT_TYPE_VIEW:
        event_payment = max_cpm

    logger = logging.getLogger(__name__)
    logger.debug("Event type {0}, value: {1}".format(event_type, event_payment))

    return event_payment


@defer.inlineCallbacks
def get_user_payment_score(campaign_id, user_id, amount=5):
    """
    Payment score for user
    1. Find most similar user.
    2. Calculate scores for similar users (payment stats and human score)
    3. Get average of user payment score.

    :param campaign_id:
    :param user_id:
    :param amount: Limit to how many users we compare
    :return: User payment score.
    """
    logger = logging.getLogger(__name__)
    yield logger.info("Calculating user payment score for user {0} and campaign {1}".format(user_id, campaign_id))

    # Find most similar users to user_id
    users = []
    user_value_iter = yield db_utils.get_user_value_iter(campaign_id)
    while True:
        user_value_doc = yield user_value_iter.next()
        if user_value_doc is None:
            break

        uid = user_value_doc['user_id']
        similarity = yield get_users_similarity(uid, user_id)
        if similarity > 0:
            reverse_insort(users, (similarity, uid))
            users = users[:amount]

    # Calculate payment score for user
    score_components = []
    for similarity, uid in users:

        user_stat = yield db_utils.get_user_value(campaign_id, uid)
        if user_stat:
            yield logger.info("Getting user value from user: {0}".format(uid))
            score_components.append(user_stat['payment'] * user_stat['human_score'])

    if not score_components:
        yield logger.warning("No similar users or no scores for similar users. User score value: 0")
        defer.returnValue(0)

    yield logger.debug("User {0} payment score: {1}".format(user_id, 1.0*sum(score_components)/len(score_components)))
    defer.returnValue(1.0*sum(score_components)/len(score_components))


@defer.inlineCallbacks
def calculate_payments_for_new_users(campaign_id, timestamp, campaign_cpm):
    """
    Calculate payments for new uses. Use CPM as default payment.

    :param campaign_id:
    :param timestamp:
    :param campaign_cpm:
    :return:
    """
    logger = logging.getLogger(__name__)
    yield logger.info("Calculating payment score for new user.")

    # For new users add payments as cpv
    uids = yield db_utils.get_events_distinct_uids(campaign_id, timestamp)
    yield logger.debug("Found {0} distinct user ids".format(len(uids)))

    for uid in uids:
        max_human_score = 0

        user_events_iter = yield db_utils.get_user_events_iter(campaign_id, timestamp, uid)
        while True:
            event_doc = yield user_events_iter.next()
            if not event_doc:
                break

            max_human_score = max([max_human_score, event_doc['human_score']])

        user_value_doc = yield db_utils.get_user_value(campaign_id, uid)

        if user_value_doc is None or user_value_doc['payment'] <= campaign_cpm:
            yield logger.info("Updating user value for {0}".format(uid))
            yield db_utils.update_user_value(campaign_id, uid, campaign_cpm, max_human_score)


@defer.inlineCallbacks
def get_total_user_score(campaign_id, timestamp, limit):
    """
    Calculate total score of all users.

    :param campaign_id: Campaign identifier.
    :param timestamp: Timestamp (last hour)
    :param limit: User limit.
    :return:
    """
    logger = logging.getLogger(__name__)
    total_score = 0
    user_score_iter = yield db_utils.get_sorted_user_score_iter(campaign_id, timestamp, limit=limit)
    while True:
        user_score_doc = yield user_score_iter.next()
        if not user_score_doc:
            break

        total_score += user_score_doc['score']
    yield logger.info("Total user score is: {0}".format(total_score))
    defer.returnValue(total_score)


@defer.inlineCallbacks
def get_best_user_payments_and_humanity(campaign_id, timestamp, uid, campaign_cpc, campaign_cpm):
    """
    Return user information: best payment, total payment and max human score.

    :param campaign_id:
    :param timestamp:
    :param uid:
    :param campaign_cpc:
    :param campaign_cpm:
    :return: Tuple: max_user_payment, max_human_score, total_user_payments
    """
    logger = logging.getLogger(__name__)
    max_user_payment, max_human_score, total_user_payments = 0, 0, 0

    user_value_doc = yield db_utils.get_user_value(campaign_id, uid)
    if user_value_doc:
        max_user_payment = user_value_doc['payment']

    user_events_iter = yield db_utils.get_user_events_iter(campaign_id, timestamp, uid)
    while True:
        event_doc = yield user_events_iter.next()
        if not event_doc:
            break

        event_payment = get_event_max_payment(event_doc, campaign_cpc, campaign_cpm)

        total_user_payments += event_payment
        max_user_payment = max([max_user_payment, event_payment])
        max_human_score = max([max_human_score, event_doc['human_score']])
    yield logger.info("User {0} max_user_payment, max_human_score, total_user_payments: {1}".format(uid, (max_user_payment, max_human_score, total_user_payments)))
    defer.returnValue((max_user_payment, max_human_score, total_user_payments))


@defer.inlineCallbacks
def calculate_events_payments(campaign_id, timestamp, payment_percentage_cutoff=0.5):
    """
    For new users:
    1. Assign them max_human_score from the database and CPM value (per campaign)
    2. Assign payment score based on average of other users.
    3.

    :param campaign_id:
    :param timestamp:
    :param payment_percentage_cutoff:
    :return:
    """
    logger = logging.getLogger(__name__)
    campaign_doc = yield db_utils.get_campaign(campaign_id)
    if campaign_doc is None:
        logger.warning("Campaign not found: {0}".format(campaign_id))
        return

    campaign_budget = campaign_doc['budget']
    campaign_cpc = campaign_doc['max_cpc']
    campaign_cpm = campaign_doc['max_cpm']

    yield calculate_payments_for_new_users(campaign_id, timestamp, campaign_cpm)

    uid_count = yield update_users_score_and_payments(campaign_id, timestamp)

    # Limit paid users to given payment_percentage_cutoff
    limit = int(uid_count * payment_percentage_cutoff)

    total_score = yield get_total_user_score(campaign_id, timestamp, limit)

    user_score_iter = yield db_utils.get_sorted_user_score_iter(campaign_id, timestamp, limit=limit)
    while True:
        user_score_doc = yield user_score_iter.next()
        if not user_score_doc:
            break

        uid = user_score_doc['user_id']

        # Calculate event payments
        if total_score > 0:
            user_budget = 1.0 * campaign_budget * user_score_doc['score'] / total_score
        else:
            user_budget = 0
        max_user_payment, max_human_score, total_user_payments = yield get_best_user_payments_and_humanity(campaign_id, timestamp, uid, campaign_cpc, campaign_cpm)
        # Update User Values
        yield db_utils.update_user_value(campaign_id, uid, max_user_payment, max_human_score)

        yield update_events_payments(campaign_id, timestamp, uid, user_budget, campaign_cpc, campaign_cpm,
                                     total_user_payments)

    # Delete user scores
    yield db_utils.delete_user_scores(campaign_id, timestamp)


@defer.inlineCallbacks
def update_events_payments(campaign_id, timestamp, uid, user_budget, campaign_cpc, campaign_cpm, total_user_payments):
    """
    Update or create event payments by dividing user budget among events.

    :param campaign_id:
    :param timestamp:
    :param uid:
    :param user_budget:
    :param campaign_cpc:
    :param campaign_cpm:
    :param total_user_payments:
    :return:
    """
    logger = logging.getLogger(__name__)
    user_events_iter = yield db_utils.get_user_events_iter(campaign_id, timestamp, uid)
    while True:
        event_doc = yield user_events_iter.next()
        if event_doc is None:
            break

        max_event_payment = get_event_max_payment(event_doc, campaign_cpc, campaign_cpm)
        if total_user_payments > 0:
            event_payment = min([max_event_payment * user_budget / total_user_payments, max_event_payment])
        else:
            event_payment = 0
        yield logger.debug("campaign_id, timestamp, event_doc['event_id'], event_payment: {0}".format((campaign_id, timestamp, event_doc['event_id'], event_payment)))
        yield db_utils.update_event_payment(campaign_id, timestamp, event_doc['event_id'], event_payment)


@defer.inlineCallbacks
def update_users_score_and_payments(campaign_id, timestamp):
    """
    Saving payment scores for users.

    :param campaign_id:
    :param timestamp:
    :return:
    """
    logger = logging.getLogger(__name__)
    uids = yield db_utils.get_events_distinct_uids(campaign_id, timestamp)
    for uid in uids:
        payment_score = yield get_user_payment_score(campaign_id, uid)
        yield db_utils.update_user_score(campaign_id, timestamp, uid, payment_score)
    yield logger.info("Updates {0} user scores.".format(len(uids)))
    defer.returnValue(len(uids))


@defer.inlineCallbacks
def update_keywords_stats(recalculate_per_views=1000, cutoff=0.00001, decay=0.01):
    """
    Update global keyword frequencies.

    :param recalculate_per_views:
    :param cutoff:
    :param decay:
    :return:
    """
    logger = logging.getLogger(__name__)
    # Recalculate only every 1000 events.
    if stats_cache.EVENTS_STATS_VIEWS % recalculate_per_views:
        yield logger.warning("Updates permitted only per {0} views".format(recalculate_per_views))
        defer.returnValue(None)

    for keyword, views_counts in stats_cache.EVENTS_STATS_KEYWORDS.iteritems():

        new_keyword_frequency = 1.0 * decay * views_counts / recalculate_per_views

        keyword_frequency_doc = yield db_utils.get_keyword_frequency(keyword)
        if keyword_frequency_doc:
            new_keyword_frequency += keyword_frequency_doc['frequency'] * (1 - decay)

        yield db_utils.update_keyword_frequency(keyword, new_keyword_frequency)

    yield logger.info("Resetting keyword stats.")
    stats_cache.reset_keywords_stats()
    stats_cache.EVENTS_STATS_VIEWS = 0

    _iter = yield db_utils.get_no_updated_keyword_frequency_iter()
    while True:
        keyword_frequency_doc = yield _iter.next()
        if keyword_frequency_doc is None:
            break

        keyword = keyword_frequency_doc['keyword']
        new_keyword_frequency = keyword_frequency_doc['frequency']*(1 - decay)
        if new_keyword_frequency <= cutoff:
            yield db_utils.delete_keyword_frequency(keyword_frequency_doc['_id'])
        else:
            yield db_utils.update_keyword_frequency(keyword, new_keyword_frequency)

    yield db_utils.set_keyword_frequency_updated_flag()


@defer.inlineCallbacks
def update_user_keywords_stats(user_id, keywords_list, cutoff=0.001, decay=0.01):
    """
    Update user keyword frequencies:
    1. Add new ones.
    2. Decay old ones.

    :param user_id:
    :param keywords_list:
    :param cutoff:
    :param decay:
    :return:
    """
    logger = logging.getLogger(__name__)
    # Update new keyword to database.
    for keyword in keywords_list:
        yield logger.debug("Adding new keyowrd: {0}".format(keyword))
        frequency = decay

        user_keyword_doc = yield db_utils.get_user_keyword_frequency(user_id, keyword)
        if user_keyword_doc:
            frequency += user_keyword_doc['frequency'] * (1 - decay)

        yield db_utils.update_user_keyword_frequency(user_id, keyword, frequency)

    # Decay keywords frequencies in database.
    _iter = yield db_utils.get_user_keyword_frequency_iter(user_id)
    while True:
        user_keyword_doc = yield _iter.next()
        if user_keyword_doc is None:
            break

        if user_keyword_doc['updated']:
            continue

        keyword = user_keyword_doc['keyword']
        frequency = user_keyword_doc['frequency']*(1 - decay)

        if frequency <= cutoff:
            # Delete keyword stats for user keyword.
            yield db_utils.delete_user_keyword_frequency(user_keyword_doc['_id'])
        else:
            # Update user keyword stats.
            yield db_utils.update_user_keyword_frequency(user_id, keyword, frequency)

    yield db_utils.set_user_keyword_frequency_updated_flag(updated=False)


@defer.inlineCallbacks
def update_user_keywords_profiles(global_freq_cutoff=0.1):
    """
    There are two kind of keyword frequencies: global and user.

    1. Get user ids from user keyword frequency collection.
    2. Iterate over user keyword frequency collection.
    3. Get keywords with global frequency higher than `global_freq_cutoff`.
    4. Calculate keyword score. Score increases with user frequency and decreases with global frequency.
    5. Save a list of highest scoring keywords in user profile.

    :param global_freq_cutoff:
    :return:
    """
    logger = logging.getLogger(__name__)
    yield logger.info("Remove old keyword profile.")
    # Remove old keywords
    yield db_utils.delete_user_profiles()

    # Create new user profiles based on keyword user frequency.
    _iter_list = yield db_utils.get_user_keyword_frequency_distinct_userids()
    for user_id in _iter_list:
        user_profile_keywords = []

        user_keyword_frequency_iter = yield db_utils.get_user_keyword_frequency_iter(user_id)
        while True:
            user_keyword_doc = yield user_keyword_frequency_iter.next()
            if user_keyword_doc is None:
                break

            keyword = user_keyword_doc['keyword']

            global_keyword_doc = yield db_utils.get_keyword_frequency(keyword)
            if global_keyword_doc is None:
                continue

            # Take only that keywords which global frequency is equal or less than 0.1.
            if global_keyword_doc['frequency'] > global_freq_cutoff:
                continue

            keyword_score = 1.0*user_keyword_doc['frequency']/(0.01 + global_keyword_doc['frequency'])

            reverse_insort(user_profile_keywords, (keyword_score, keyword))
            user_profile_keywords = user_profile_keywords[:stats_consts.MAX_USER_KEYWORDS_IN_PROFILE]

        yield db_utils.update_user_profile(user_id, dict([(elem[1], elem[0]) for elem in user_profile_keywords]))


@defer.inlineCallbacks
def add_view_keywords(user_id, keywords_list):
    """
    Update cache

    :param user_id:
    :param keywords_list:
    :return:
    """
    logger = logging.getLogger(__name__)
    try:
        yield ADD_EVENT_LOCK.acquire()

        # Update user keyword stats.
        yield update_user_keywords_stats(user_id, keywords_list)

        # Update global keywords stats.
        for keyword in keywords_list:
            yield logger.info("Incrementing views.")
            stats_cache.EVENTS_STATS_KEYWORDS[keyword] += 1
            stats_cache.EVENTS_STATS_VIEWS += 1

        stats_cache.EVENTS_STATS_VIEWS += 1

        yield update_keywords_stats()
    finally:
        yield ADD_EVENT_LOCK.release()


@defer.inlineCallbacks
def delete_campaign(campaign_id):
    """
    Delete campaign document and all campaign banners

    :param campaign_id:
    :return:
    """
    # TODO: add deleting events and other objects related to campaign.
    logger = logging.getLogger(__name__)
    yield logger.info("Removing campaigns and banners.")
    yield db_utils.delete_campaign(campaign_id)
    yield db_utils.delete_campaign_banners(campaign_id)
