from adpay import db

########################
#### CAMPAIGNS #########
########################
def get_campaign(campaign_id):
    return db.get_campaign_collection().find_one({'campaign_id':campaign_id})


def update_campaign(campaign_doc):
    return db.get_campaign_collection().replace_one({'campaign_id':campaign_doc['campaign_id']},
                                                    campaign_doc, upsert=True)

def delete_campaign(campaign_id):
    return db.get_campaign_collection().delete_many({'campaign_id':campaign_id})

#########################
##### BANNERS ###########
#########################

def get_banners_iter():
    return db.get_banner_collection().find(cursor=True)


def get_banner(banner_id):
    return db.get_banner_collection().find_one({'banner_id':banner_id})


def update_banner(banner_doc):
    return db.get_banner_collection().replace_one({'banner_id':banner_doc['banner_id']},
                                                  banner_doc, upsert=True)

def delete_campaign_banners(campaign_id):
    return db.get_banner_collection().delete_many({'campaign_id':campaign_id})

########################
##### EVENTS  ##########
########################

def get_events_iter():
    return db.get_event_collection().find(cursor=True)


def update_event(event_doc):
    return db.get_event_collection().replace_one({'event_id':event_doc['event_id']},
                                                 event_doc, upsert=True)


def delete_event(event_id):
    return db.get_banner_collection().delete_many({'event_id':event_id})

########################
####   STATS    ########
########################

def get_payments(timestamp):
    return db.get_payment_stat_collection().find_one({'timestamp': timestamp})


def update_payments(timestamp, payments_stat):
    return db.get_payment_stat_collection().replace_one({'timestamp':timestamp},
                                                        {'timestamp':timestamp, 'events':payments_stat}, upsert=True)