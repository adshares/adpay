from adpay.db import consts as db_const
from twisted.internet import defer

from txmongo import filter
import txmongo


@defer.inlineCallbacks
def configure_db():
    yield get_mongo_db()

    #Creating indexes when daemon starts
    campaign_idx = filter.sort(filter.ASCENDING("campaign_id"))
    banner_idx = filter.sort(filter.ASCENDING("banner_id"))
    timestamp_idx = filter.sort(filter.ASCENDING("timestamp"))
    event_idx = filter.sort(filter.ASCENDING("event_id"))

    #Campaign indexes
    yield get_campaign_collection().create_index(campaign_idx, unique=True)

    #Banner indexes
    yield get_banner_collection().create_index(banner_idx, unique=True)

    #Timestamp indexes
    yield get_campaign_collection().create_index(timestamp_idx, unique=True)
    yield get_event_collection().create_index(timestamp_idx)

    #Event indexes
    yield get_event_collection().create_index(event_idx, unique=True)


@defer.inlineCallbacks
def get_mongo_db():
    conn = yield get_mongo_connection()
    defer.returnValue(conn.adpay)


@defer.inlineCallbacks
def get_payment_collection():
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.payments)


@defer.inlineCallbacks
def get_payment_rounds_collection():
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.payments_rounds)


@defer.inlineCallbacks
def get_campaign_collection():
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.campaign)


@defer.inlineCallbacks
def get_banner_collection():
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.banners)


@defer.inlineCallbacks
def get_event_collection():
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.events)


@defer.inlineCallbacks
def get_user_value_collection():
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.user_values)


@defer.inlineCallbacks
def get_user_score_collection():
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.user_scores)


@defer.inlineCallbacks
def get_user_keyword_frequency_collection():
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.user_keyword_frequency)


@defer.inlineCallbacks
def get_user_profile_collection():
    mongo_db = yield get_mongo_db()
    yield mongo_db.user_profile


@defer.inlineCallbacks
def get_keyword_frequency_collection():
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.keyword_frequency)


MONGO_CONNECTION = None
@defer.inlineCallbacks
def get_mongo_connection():
    global MONGO_CONNECTION
    if MONGO_CONNECTION is None:
        MONGO_CONNECTION = yield txmongo.lazyMongoConnectionPool(port=db_const.MONGO_DB_PORT)
    defer.returnValue(MONGO_CONNECTION)


@defer.inlineCallbacks
def disconnect():
    global MONGO_CONNECTION
    conn = yield get_mongo_connection()
    yield conn.disconnect()
    MONGO_CONNECTION = None
