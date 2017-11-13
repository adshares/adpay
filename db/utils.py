from twisted.internet import defer
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
        self.docs_index +=1
        defer.returnValue(value)


# Campaigns
def get_campaign(campaign_id):
    return db.get_campaign_collection().find_one({'campaign_id':campaign_id})


def update_campaign(campaign_id, time_start, time_end, max_cpc, max_cpv, budget, filters):
    return db.get_campaign_collection().replace_one({'campaign_id':campaign_id},{
        'campaign_id':campaign_id,
        'time_start':time_start,
        'time_end':time_end,
        'max_cpc':max_cpc,
        'max_cpv':max_cpv,
        'budget':budget,
        'filters':filters
    }, upsert=True)


def delete_campaign(campaign_id):
    return db.get_campaign_collection().delete_many({'campaign_id':campaign_id})


# Banners
def get_banner(banner_id):
    return db.get_banner_collection().find_one({'banner_id':banner_id})


def get_banners_iter():
    return query_iterator(db.get_banner_collection().find(cursor=True))


def update_banner(banner_id, campaign_id):
    return db.get_banner_collection().replace_one({'banner_id':banner_id},{
        'banner_id':banner_id,
        'campaign_id':campaign_id
    }, upsert = True)


def delete_campaign_banners(campaign_id):
    return db.get_banner_collection().delete_many({'campaign_id':campaign_id})


# Events
def get_user_events_iter(campaign_id, timestamp, uid):
    return query_iterator(db.get_event_collection().find(cursor=True))


def update_event(event_id, event_type, timestamp, user_id, banner_id, paid_amount, keywords, human_score):
    return db.get_event_collection().replace_one({'event_id':event_id},{
        'event_id':event_id,
        'event_type':event_type,
        'timestamp':timestamp,
        'user_id':user_id,
        'banner_id':banner_id,
        'paid_amount':paid_amount,
        'keywords':keywords,
        'human_score':human_score
    }, upsert=True)


def get_events_distinct_uids_iter(campaign_id, timestamp):
    # Return list of distinct users ids for the given campaign and within [timestamp, timestamp+1hour) period.
    #TODO
    return query_iterator([])


def delete_event(event_id):
    return db.get_banner_collection().delete_many({'event_id':event_id})


# Event payments
def get_payments_iter(timestamp):
    return query_iterator(db.get_payment_stat_collection().find({'timestamp': timestamp}, cursor=True))


def update_event_payment(campaign_id, timestamp, event_id, event_payment):
    return db.get_payment_stat_collection().replace_one({'timestamp':timestamp, 'event_id':event_id}, {
        'timestamp':timestamp,
        'event_id':event_id,
        'payment':event_payment,
        'campaign_id':campaign_id
    }, upsert=True)


# Calculated payments rounds
def get_payment_round(timestamp):
    return db.get_payment_rounds_collection().find_one({
        'timestamp':timestamp
    })


def update_payment_round(timestamp):
    return db.get_payment_rounds_collection().replace_one({'timestamp':timestamp}, {'timestamp':timestamp}, upsert=True)


# User Values (Columns: campaign_id, timestamp, uid, payment, credibility)
def user_value_uids_iter(campaign_id, timestamp):
    # Return uids from user values for the given campaign and within [timestamp-1hour, timestamp) period.
    # TODO
    return query_iterator(['uid1', 'uid2'])


def get_user_value(campaign_id, timestamp, uid):
    # Return user value doc for the given campaign and within [timestamp-1hour, timestamp) period.
    # TODO
    return {'timestamp': timestamp,
            'campaign_id': campaign_id,
            'uid': uid,
            'payment': 0,
            'credibility': 0}


def update_user_value(campaign_id, timestamp, uid, payment, human_score):
    # TODO
    pass


# User scores (Columns: campaign_id, timestamp, uid, score)
def get_sorted_user_score_iter(campaign_id, timestamp, limit):
    # Return descending by score sorted list of uids limited to limit.
    # TODO
    return query_iterator([])


def update_user_score(campaign_id, timestamp, uid, score):
    # TODO
    pass


def delete_user_scores(campaign_id, timestamp):
    pass


# User keywords frequency (user_id, keyword, frequency)
def get_user_keyword_frequency(user_id, keyword):
    return []


def get_user_keyword_frequency_iter(user_id):
    return query_iterator([])


def update_user_keyword_frequency(user_id, keyword, frequency):
    pass


def get_user_keyword_frequency_distinct_userid_iter():
    # Return distinct user id.
    return query_iterator([])


def delete_user_keyword_frequency(_id):
    pass


# User keywords profiles (user_id, keyword_score_dict e.g. {'keyword1':score1, 'keyword2':score2, ...})
def get_user_profile(user_id):
    pass


def update_user_profile(user_id, profile_dict):
    pass


def delete_user_profiles():
    # Remove all user profiles
    pass


# Keywords views (keyword, frequency, updated=False)
def set_keyword_frequency_updated_flag(updated):
    pass


def get_no_updated_keyword_frequency_iter():
    pass


def update_keyword_frequency(keyword, frequency, updated):
    pass


def get_keyword_frequency(keyword):
    pass


def delete_keyword_frequency(_id):
    pass
