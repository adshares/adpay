from adpay import db

# Campaigns
def get_campaign(campaign_id):
    return db.get_campaign_collection().find_one({'campaign_id':campaign_id})


def update_campaign(campaign_doc):
    return db.get_campaign_collection().replace_one({'campaign_id':campaign_doc['campaign_id']},
                                                    campaign_doc, upsert=True)

def delete_campaign(campaign_id):
    return db.get_campaign_collection().delete_many({'campaign_id':campaign_id})


# Banners
def get_banners_iter():
    return db.get_banner_collection().find(cursor=True)


def get_banner(banner_id):
    return db.get_banner_collection().find_one({'banner_id':banner_id})


def update_banner(banner_doc):
    return db.get_banner_collection().replace_one({'banner_id':banner_doc['banner_id']},
                                                  banner_doc, upsert=True)

def delete_campaign_banners(campaign_id):
    return db.get_banner_collection().delete_many({'campaign_id':campaign_id})


# Events
def get_events_iter(campaign_id, timestamp, uid):
    # TODO
    return db.get_event_collection().find(cursor=True)


def update_event(event_doc):
    return db.get_event_collection().replace_one({'event_id':event_doc['event_id']},
                                                 event_doc, upsert=True)

def get_events_distinct_uids_iter(campaign_id, timestamp):
    # Return list of distinct users ids for the given campaign and within [timestamp, timestamp+1hour) period.
    #TODO
    return []


def delete_event(event_id):
    return db.get_banner_collection().delete_many({'event_id':event_id})


# Event payments
def get_payments(campaign_id, timestamp, event_id):
    # TODO
    return db.get_payment_stat_collection().find_one({'timestamp': timestamp})


def update_event_payment(campaign_id, timestamp, event_id, payment):
    # TODO
    return db.get_payment_stat_collection().replace_one({'timestamp':timestamp},
                                                        {'timestamp':timestamp, 'events':payment}, upsert=True)


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


def update_user_value(campaign_id, timestamp, uid, payment, credibility):
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
