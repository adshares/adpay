from twisted.internet import defer
from txmongo import filter as txfilter
from adpay import db


class query_iterator(object):
    """
        Every query with cursor = True can be iterated with simple way:

        _iter = query_iterator(query)

        while True:
            elem = yield _iter.next()

            if elem is None:
                break

            print "elem", elem
    """

    def __init__(self, query):
        self.query = query

        self.docs, self.dfr = None, None
        self.docs_index = 0

    def __iter__(self):
        return self

    def __next__(self):
        return self.next()

    @defer.inlineCallbacks
    def next(self):
        if self.docs is None:
            self.docs, self.dfr = yield self.query

        if not self.docs:
            defer.returnValue(None)

        if self.docs_index >= len(self.docs):
            self.docs, self.dfr = yield self.dfr
            self.docs_index = 0
            value = yield self.next()
            defer.returnValue(value)

        value = self.docs[self.docs_index]
        self.docs_index += 1
        defer.returnValue(value)


# Campaigns
@defer.inlineCallbacks
def get_campaign(campaign_id):
    collection = yield db.get_campaign_collection()
    return_value = yield collection.find_one({'campaign_id': campaign_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_campaign_iter():
    collection = yield db.get_campaign_collection()
    defer.returnValue(query_iterator(collection.find(cursor=True)))


@defer.inlineCallbacks
def update_campaign(campaign_id, time_start, time_end, max_cpc, max_cpv, budget, filters):
    collection = yield db.get_campaign_collection()
    return_value = yield collection.replace_one({'campaign_id': campaign_id}, {
        'campaign_id': campaign_id,
        'time_start': time_start,
        'time_end': time_end,
        'max_cpc': max_cpc,
        'max_cpv': max_cpv,
        'budget': budget,
        'filters': filters
    }, upsert=True)

    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_campaign(campaign_id):
    collection = yield db.get_campaign_collection()
    return_value = yield collection.delete_many({'campaign_id': campaign_id})
    defer.returnValue(return_value)


# Banners
@defer.inlineCallbacks
def get_banner(banner_id):
    collection = yield db.get_banner_collection()
    return_value = yield collection.find_one({'banner_id': banner_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_banners_iter():
    collection = yield db.get_banner_collection()
    defer.returnValue(query_iterator(collection.find(cursor=True)))


@defer.inlineCallbacks
def get_campaign_banners(campaign_id):
    collection = yield db.get_banner_collection()
    return_value = yield collection.find({'campaign_id': campaign_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def update_banner(banner_id, campaign_id):
    collection = yield db.get_banner_collection()
    return_value = yield collection.replace_one({'banner_id': banner_id}, {
        'banner_id': banner_id,
        'campaign_id': campaign_id
    }, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_campaign_banners(campaign_id):
    collection = yield db.get_banner_collection()
    return_value = yield collection.delete_many({'campaign_id': campaign_id})
    defer.returnValue(return_value)


# Events
@defer.inlineCallbacks
def update_event(event_id, event_type, timestamp, user_id, banner_id, campaign_id, paid_amount, keywords, human_score):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_event_collection()

    return_value = yield collection.replace_one({'event_id': event_id}, {
        'event_id': event_id,
        'event_type': event_type,
        'timestamp': timestamp,
        'user_id': user_id,
        'banner_id': banner_id,
        'campaign_id': campaign_id,
        'paid_amount': paid_amount,
        'keywords': keywords,
        'human_score': human_score
    }, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_banner_events_iter(banner_id, timestamp):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_event_collection()

    defer.returnValue(query_iterator(collection.find({
        'banner_id': banner_id,
        'timestamp': stats_utils.timestamp2hour(timestamp)
    }, cursor=True)))


@defer.inlineCallbacks
def get_user_events_iter(campaign_id, timestamp, uid):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_event_collection()

    defer.returnValue(query_iterator(collection.find({
        'user_id': uid,
        'campaign_id': campaign_id,
        'timestamp': timestamp
    }, cursor=True)))


@defer.inlineCallbacks
def get_events_distinct_uids(campaign_id, timestamp):
    # Return list of distinct users ids for the given campaign timestamp.
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_event_collection()

    return_values = yield collection.distinct(key='user_id',
                                              filter={'timestamp': timestamp, 'campaign_id': campaign_id})
    defer.returnValue(return_values)


@defer.inlineCallbacks
def delete_event(event_id):
    collection = yield db.get_event_collection()
    return_value = yield collection.delete_many({'event_id': event_id})
    defer.returnValue(return_value)


# Event payments
@defer.inlineCallbacks
def update_event_payment(campaign_id, timestamp, event_id, payment):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_payment_collection()

    return_value = yield collection.replace_one({'event_id': event_id, 'campaign_id': campaign_id}, {
        'timestamp': timestamp,
        'event_id': event_id,
        'payment': payment,
        'campaign_id': campaign_id
    }, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_payments_iter(timestamp):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_payment_collection()

    defer.returnValue(query_iterator(collection.find({
        'timestamp': timestamp
    }, cursor=True)))


# Calculated payments rounds
@defer.inlineCallbacks
def get_payment_round(timestamp):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_payment_rounds_collection()

    return_value = yield collection.find_one({'timestamp': timestamp})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def update_payment_round(timestamp):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_payment_rounds_collection()

    round_doc = {'timestamp': timestamp}
    return_value = yield collection.update(round_doc, round_doc, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_payment_round_iter():
    collection = yield db.get_payment_rounds_collection()
    defer.returnValue(query_iterator(collection.find(cursor=True)))


@defer.inlineCallbacks
def get_last_round():
    sort_filter = txfilter.sort(txfilter.DESCENDING("timestamp"))
    collection = yield db.get_payment_rounds_collection()

    return_value = yield collection.find_one(sort=sort_filter)
    defer.returnValue(return_value)


# User Values (Columns: campaign_id, timestamp, user_id, payment, credibility)
@defer.inlineCallbacks
def get_user_value_iter(campaign_id, timestamp):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_user_value_collection()

    defer.returnValue(query_iterator(collection.find({'campaign_id': campaign_id, 'timestamp': timestamp}, cursor=True)))


@defer.inlineCallbacks
def get_user_value(campaign_id, timestamp, user_id):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_user_value_collection()

    return_value = yield collection.find_one({
        'campaign_id': campaign_id,
        'timestamp': timestamp,
        'user_id': user_id
    })
    defer.returnValue(return_value)


@defer.inlineCallbacks
def update_user_value(campaign_id, timestamp, user_id, payment, human_score):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_user_value_collection()

    return_value = yield collection.replace_one({
        'campaign_id': campaign_id,
        'timestamp': timestamp,
        'user_id': user_id
    }, {
        'campaign_id': campaign_id,
        'timestamp': timestamp,
        'user_id': user_id,
        'payment': payment,
        'human_score': human_score
    }, upsert=True)
    defer.returnValue(return_value)


# User scores (Columns: campaign_id, timestamp, user_id, score)
@defer.inlineCallbacks
def get_sorted_user_score_iter(campaign_id, timestamp, limit):
    # Return descending by score sorted list of  to limit.
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_user_score_collection()

    defer.returnValue(query_iterator(collection.find({'campaign_id': campaign_id, 'timestamp': timestamp},
                                                     sort=txfilter.sort(txfilter.DESCENDING("score")),
                                                     limit=limit, cursor=True)))


@defer.inlineCallbacks
def update_user_score(campaign_id, timestamp, user_id, score):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_user_score_collection()

    return_value = yield collection.replace_one({
        'campaign_id': campaign_id,
        'timestamp': timestamp,
        'user_id': user_id
    }, {
        'campaign_id': campaign_id,
        'timestamp': timestamp,
        'user_id': user_id,
        'score': score
    }, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_user_scores(campaign_id, timestamp):
    from adpay.stats import utils as stats_utils

    timestamp = stats_utils.timestamp2hour(timestamp)
    collection = yield db.get_user_score_collection()

    return_value = yield collection.delete_many({'campaign_id': campaign_id, 'timestamp': timestamp})
    defer.returnValue(return_value)


# User keywords frequency (user_id, keyword, frequency)
@defer.inlineCallbacks
def get_user_keyword_frequency(user_id, keyword):
    collection = yield db.get_user_keyword_frequency_collection()
    return_value = yield collection.find_one({'keyword': keyword, 'user_id': user_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_user_keyword_frequency_iter(user_id):
    collection = yield db.get_user_keyword_frequency_collection()
    defer.returnValue(query_iterator(collection.find({'user_id': user_id}, cursor=True)))


@defer.inlineCallbacks
def update_user_keyword_frequency(user_id, keyword, frequency):
    collection = yield db.get_user_keyword_frequency_collection()
    return_value = yield collection.replace_one({
        'keyword': keyword,
        'user_id': user_id
    }, {
        'keyword': keyword,
        'user_id': user_id,
        'frequency': frequency
    }, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_user_keyword_frequency_distinct_userid_iter():
    # Return distinct user id.
    collection = yield db.get_user_keyword_frequency_collection()
    return_value = yield collection.distinct(key='user_id')
    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_user_keyword_frequency(_id):
    collection = yield db.get_user_keyword_frequency_collection()
    return_value = yield collection.delete_one({'_id': _id})
    defer.returnValue(return_value)


# User keywords profiles (user_id, keyword_score_dict e.g. {'keyword1':score1, 'keyword2':score2, ...})
@defer.inlineCallbacks
def get_user_profile(user_id):
    collection = yield db.get_user_profile_collection()
    return_value = yield collection.find_one({'user_id': user_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def update_user_profile(user_id, profile_dict):
    collection = yield db.get_user_profile_collection()
    return_value = yield collection.replace_one({
        'user_id': user_id
    }, {
        'user_id': user_id,
        'profile': profile_dict
    }, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_user_profiles():
    # Remove all user profiles
    collection = yield db.get_user_profile_collection()
    return_value = yield collection.delete_many()
    defer.returnValue(return_value)


# Keywords views (keyword, frequency, updated=False)
@defer.inlineCallbacks
def update_keyword_frequency(keyword, frequency, updated=False):
    collection = yield db.get_keyword_frequency_collection()
    return_value = yield collection.replace_one({
        'keyword': keyword
    }, {
        'keyword': keyword,
        'frequency': frequency,
        'updated': updated
    }, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def set_keyword_frequency_updated_flag(updated=False):
    collection = yield db.get_keyword_frequency_collection()
    return_value = yield collection.update_many({}, {"$set": {"updated": updated}})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_no_updated_keyword_frequency_iter():
    collection = yield db.get_keyword_frequency_collection()
    defer.returnValue(query_iterator(collection.find({'updated': False}, cursor=True)))


@defer.inlineCallbacks
def get_keyword_frequency(keyword):
    collection = yield db.get_keyword_frequency_collection()
    return_value = yield collection.find_one({'keyword': keyword})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_keyword_frequency(_id):
    collection = yield db.get_keyword_frequency_collection()
    return_value = yield collection.delete_many({'_id': _id})
    defer.returnValue(return_value)
