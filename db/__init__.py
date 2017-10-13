from adpay.db import const as db_const

from txmongo import filter
import txmongo

def configure_db():
    get_mongo_db()

    #Creating indexes when daemon starts
    campaign_idx = filter.sort(filter.ASCENDING("campaign_id"))
    banner_idx = filter.sort(filter.ASCENDING("banner_id"))
    timestamp_idx = filter.sort(filter.ASCENDING("timestamp"))
    event_idx = filter.sort(filter.ASCENDING("event_id"))

    #Campaign indexes
    get_campaign_collection().create_index(campaign_idx, unique=True)

    #Banner indexes
    get_banner_collection().create_index(banner_idx, unique=True)

    #Timestamp indexes
    get_campaign_collection().create_index(timestamp_idx, unique=True)
    get_event_collection().create_index(timestamp_idx)

    #Event indexes
    get_event_collection().create_index(event_idx, unique=True)


def get_mongo_db():
    return get_mongo_connection().spotree


def get_payment_stat_collection():
    return get_mongo_db().payments


def get_campaign_collection():
    return get_mongo_db().campaign


def get_banner_collection():
    return get_mongo_db().banners


def get_event_collection():
    return get_mongo_db().events


MONGO_CONNECTION = None
def get_mongo_connection():
    global MONGO_CONNECTION
    if MONGO_CONNECTION is None:
        MONGO_CONNECTION = txmongo.lazyMongoConnectionPool(port=db_const.MONGO_DB_PORT)
    return MONGO_CONNECTION


def disconnect():
    get_mongo_connection().disconnect()
