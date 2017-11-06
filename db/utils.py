from adpay import db

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


# Events
def get_user_events_iter(campaign_id, timestamp, uid):
    return db.get_event_collection().find(cursor=True)


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
    return []


def delete_event(event_id):
    return db.get_banner_collection().delete_many({'event_id':event_id})


# User Values (Columns: campaign_id, timestamp, uid, payment, credibility)
def user_value_uids_iter(campaign_id, timestamp):
    # Return uids from user values for the given campaign and within [timestamp-1hour, timestamp) period.
    # TODO
    return ['uid1', 'uid2']


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
    return []


def update_user_score(campaign_id, timestamp, uid, score):
    # TODO
    pass


def delete_user_scores(campaign_id, timestamp):
    pass


# Event payments
def get_payments(campaign_id, timestamp, event_id):
    # TODO
    return db.get_payment_stat_collection().find_one({'timestamp': timestamp})


def update_event_payment(campaign_id, timestamp, event_id, payment):
    # TODO
    return db.get_payment_stat_collection().replace_one({'timestamp':timestamp}, {
        'timestamp':timestamp,
        'events':payment
    }, upsert=True)


# User keywords frequency (user_id, keyword, frequency)
def get_user_keyword_frequency(user_id, keyword):
    return []


def delete_user_keyword_frequency(_id):
    pass


def update_user_keyword_frequency(user_id, keyword, frequency):
    pass


# User keywords profile



# Keywords views (keyword, frequency, updated=False)
def set_keyword_frequency_updated_flag(updated):
    pass


def get_no_updated_keyword_frequency_iter():
    pass


def get_keyword_frequency(keyword):
    pass


def delete_keyword_frequency(_id):
    pass


def update_keyword_frequency(keyword, frequency, updated):
    pass



