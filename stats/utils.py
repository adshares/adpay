from twisted.internet import defer

from adpay.stats import cache as stats_cache
from adpay.stats import consts as stats_consts
from adpay.db import consts as db_consts
from adpay.db import utils as db_utils

import math


ADD_EVENT_LOCK = defer.DeferredLock()


def timestamp2hour(timestamp):
    return math.ceil(1.0*timestamp/stats_consts.SECONDS_PER_HOUR)*stats_consts.SECONDS_PER_HOUR


def genkey(key, val, delimiter="_"):
    keywal = "%s%s%s" % (key, delimiter, val)
    return keywal.replace(".", "")


@defer.inlineCallbacks
def get_user_profile_keywords(user_id):
    user_profile_doc = yield db_utils.get_user_profile(user_id)
    if not user_profile_doc:
        defer.returnValue(None)

    defer.returnValue(list(set(user_profile_doc['profile'].keys()))[:stats_consts.MAX_USER_KEYWORDS_IN_PROFILE])


@defer.inlineCallbacks
def get_users_similarity(user1_id, user2_id):
    """
        Return user similarity value [0, 1] taking into account keywords similarity.
        1 - user1 is recognised as user2
        0 - user1 and user2 are completely different.
    """

    if user1_id == user2_id:
        defer.returnValue(1.0)

    user1_profile_keywords = yield get_user_profile_keywords(user1_id)
    user2_profile_keywords = yield get_user_profile_keywords(user2_id)

    user1_profile_keywords = user1_profile_keywords or []
    user2_profile_keywords = user2_profile_keywords or []

    common_keyowrds = len(set(user1_profile_keywords) & set(user2_profile_keywords))
    defer.returnValue(1.0*common_keyowrds/stats_consts.MAX_USER_KEYWORDS_IN_PROFILE)


def reverse_insort(a, x, lo=0, hi=None):
    """Insert item x in list a, and keep it reverse-sorted assuming a
    is reverse-sorted.

    If x is already in a, insert it to the right of the rightmost x.

    Optional args lo (default 0) and hi (default len(a)) bound the
    slice of a to be searched.
    """
    if lo < 0:
        raise ValueError('lo must be non-negative')
    if hi is None:
        hi = len(a)
    while lo < hi:
        mid = (lo+hi)//2
        if x > a[mid]: hi = mid
        else: lo = mid+1
    a.insert(lo, x)


def get_event_max_payment(event_doc, max_cpc, max_cpv):
    event_type, event_payment = event_doc['event_type'], 0
    if event_type == db_consts.EVENT_TYPE_CONVERSION:
        event_payment = event_doc['paid_amount']
    elif event_type == db_consts.EVENT_TYPE_CLICK:
        event_payment = max_cpc
    elif event_type == db_consts.EVENT_TYPE_VIEW:
        event_payment = max_cpv
    return event_payment


@defer.inlineCallbacks
def get_user_payment_score(campaign_id, user_id, amount=5):
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
            score_components.append(user_stat['payment']*user_stat['human_score'])

    if not score_components:
        defer.returnValue(0)

    defer.returnValue(1.0*sum(score_components)/len(score_components))


@defer.inlineCallbacks
def calculate_events_payments(campaign_id, timestamp, payment_percentage_cutoff=0.5):
    campaign_doc = yield db_utils.get_campaign(campaign_id)
    if campaign_doc is None:
        return

    campaign_budget = campaign_doc['budget']
    campaign_cpc = campaign_doc['max_cpc']
    campaign_cpv = campaign_doc['max_cpv']

    # For new users add payments as cpv
    uids = yield db_utils.get_events_distinct_uids(campaign_id, timestamp)
    for uid in uids:
        max_human_score = 0

        user_events_iter = yield db_utils.get_user_events_iter(campaign_id, timestamp, uid)
        while True:
            event_doc = yield user_events_iter.next()
            if not event_doc:
                break

            max_human_score = max([max_human_score, event_doc['human_score']])

        user_value_doc = yield db_utils.get_user_value(campaign_id, uid)
        if user_value_doc is None or user_value_doc['payment'] <= campaign_cpv:
            yield db_utils.update_user_value(campaign_id, uid, campaign_cpv, max_human_score)

    # Saving payment scores for users.
    total_users = 0
    uids = yield db_utils.get_events_distinct_uids(campaign_id, timestamp)
    for uid in uids:
        payment_score = yield get_user_payment_score(campaign_id, uid)
        yield db_utils.update_user_score(campaign_id, timestamp, uid, payment_score)
        total_users += 1

    # Limit paid users to given payment_percentage_cutoff
    limit = int(total_users*payment_percentage_cutoff)

    total_score = 0
    user_score_iter = yield db_utils.get_sorted_user_score_iter(campaign_id, timestamp, limit=limit)
    while True:
        user_score_doc = yield user_score_iter.next()
        if not user_score_doc:
            break

        total_score += user_score_doc['score']

    user_score_iter = yield db_utils.get_sorted_user_score_iter(campaign_id, timestamp, limit=limit)
    while True:
        user_score_doc = yield user_score_iter.next()
        if not user_score_doc:
            break

        uid = user_score_doc['user_id']

        # Calculate event payments
        user_budget = 1.0*user_score_doc['score']*campaign_budget/total_score

        max_user_payment, max_human_score, total_user_payments = 0, 0, 0

        user_value_doc = yield db_utils.get_user_value(campaign_id, uid)
        if user_value_doc is not None:
            max_user_payment = user_value_doc['payment']

        user_events_iter = yield db_utils.get_user_events_iter(campaign_id, timestamp, uid)
        while True:
            event_doc = yield user_events_iter.next()
            if not event_doc:
                break

            event_payment = get_event_max_payment(event_doc, campaign_cpc, campaign_cpv)

            total_user_payments += event_payment
            max_user_payment = max([max_user_payment, event_payment])
            max_human_score = max([max_human_score, event_doc['human_score']])

        user_events_iter = yield db_utils.get_user_events_iter(campaign_id, timestamp, uid)
        while True:
            event_doc = yield user_events_iter.next()
            if event_doc is None:
                break

            event_id = event_doc['event_id']
            event_payment = get_event_max_payment(event_doc, campaign_cpc, campaign_cpv)

            event_payment = user_budget*event_payment/total_user_payments
            yield db_utils.update_event_payment(campaign_id, timestamp, event_id, event_payment)

        # Update User Values
        yield db_utils.update_user_value(campaign_id, uid, max_user_payment, max_human_score)

    # Delete user scores
    yield db_utils.delete_user_scores(campaign_id, timestamp)


@defer.inlineCallbacks
def update_keywords_stats(recalculate_per_views=1000, cutoff=0.00001, deckay=0.01):
    # Recalculate only every 1000 events.
    if stats_cache.get_views_stats() % recalculate_per_views:
        defer.returnValue(None)

    for keyword, views_counts in stats_cache.get_keyword_stats_iter():
        keyword_frequency_doc = yield db_utils.get_keyword_frequency(keyword)

        old_keyword_freq = 0
        if keyword_frequency_doc:
            old_keyword_freq = keyword_frequency_doc['frequency']

        new_keyword_frequency = 1.0*deckay*views_counts/recalculate_per_views + old_keyword_freq*(1-deckay)
        yield db_utils.update_keyword_frequency(keyword, new_keyword_frequency)
    stats_cache.reset_keywords_stats()
    stats_cache.reset_views_stats()

    _iter = yield db_utils.get_no_updated_keyword_frequency_iter()
    while True:
        keyword_frequency_doc = yield _iter.next()
        if keyword_frequency_doc is None:
            break

        keyword = keyword_frequency_doc['keyword']
        new_keyword_frequency = keyword_frequency_doc['frequency']*(1-deckay)
        if new_keyword_frequency <= cutoff:
            yield db_utils.delete_keyword_frequency(keyword_frequency_doc['_id'])
        else:
            yield db_utils.update_keyword_frequency(keyword, new_keyword_frequency)

    yield db_utils.set_keyword_frequency_updated_flag()


@defer.inlineCallbacks
def update_user_keywords_stats(user_id, keywords_list, cutoff=0.001, deckay=0.01):
    # Update new keyword to database.
    for keyword in keywords_list:
        user_keyword_doc = yield db_utils.get_user_keyword_frequency(user_id, keyword)

        old_keyword_frequency = 0
        if user_keyword_doc is not None:
            old_keyword_frequency = user_keyword_doc['frequency']

        frequency = deckay + old_keyword_frequency*(1 - deckay)
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
        frequency = user_keyword_doc['frequency']*(1-deckay)

        if frequency <= cutoff:
            # Delete keyword stats for user keyword.
            yield db_utils.delete_user_keyword_frequency(user_keyword_doc['_id'])
        else:
            # Update user keyword stats.
            yield db_utils.update_user_keyword_frequency(user_id, keyword, frequency)

    yield db_utils.set_user_keyword_frequency_updated_flag(updated=False)


@defer.inlineCallbacks
def update_user_keywords_profiles(global_freq_cutoff=0.1):
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
    try:
        yield ADD_EVENT_LOCK.acquire()

        # Update user keyword stats.
        yield update_user_keywords_stats(user_id, keywords_list)

        # Update global keywords stats.
        for keyword in keywords_list:
            stats_cache.keyword_inc(keyword)
        stats_cache.views_inc()
        yield update_keywords_stats()
    finally:
        yield ADD_EVENT_LOCK.release()


@defer.inlineCallbacks
def delete_campaign(campaign_id):
    # TODO: add deleting events and other objects related to campaign.

    yield db_utils.delete_campaign(campaign_id)
    yield db_utils.delete_campaign_banners(campaign_id)
