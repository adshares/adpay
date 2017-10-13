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


########################
####   STATS    ########
########################
def get_payments(timestamp):
    return db.get_payment_stat_collection().find_one({'timestamp': timestamp})