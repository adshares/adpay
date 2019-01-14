import logging

import txmongo
from twisted.internet import defer
from txmongo import filter

from adpay.db import consts as db_const


@defer.inlineCallbacks
def configure_db():
    """
    Initialize the database.

    :return:
    """
    logger = logging.getLogger(__name__)
    yield get_mongo_db()

    campaign_idx = filter.sort(filter.ASCENDING("campaign_id"))
    banner_idx = filter.sort(filter.ASCENDING("banner_id"))
    timestamp_idx = filter.sort(filter.ASCENDING("timestamp"))
    event_idx = filter.sort(filter.ASCENDING("event_id"))
    user_idx = filter.sort(filter.ASCENDING("user_id"))
    keyowrd_idx = filter.sort(filter.ASCENDING("keyword"))
    updated_idx = filter.sort(filter.ASCENDING("updated"))

    campaign_collection = yield get_campaign_collection()
    yield campaign_collection.create_index(campaign_idx, unique=True)

    banner_collection = yield get_banner_collection()
    yield banner_collection.create_index(banner_idx, unique=True)
    yield banner_collection.create_index(campaign_idx)

    event_collection = yield get_event_collection()
    yield event_collection.create_index(event_idx, unique=True)
    yield event_collection.create_index(timestamp_idx)
    yield event_collection.create_index(banner_idx)
    yield event_collection.create_index(user_idx)

    payment_collection = yield get_payment_collection()
    yield payment_collection.create_index(timestamp_idx)

    payment_round_collection = yield get_payment_rounds_collection()
    yield payment_round_collection.create_index(timestamp_idx, unique=True)

    user_value_collection = yield get_user_value_collection()
    yield user_value_collection.create_index(user_idx)
    yield user_value_collection.create_index(campaign_idx)

    user_score_collection = yield get_user_score_collection()
    yield user_score_collection.create_index(timestamp_idx)
    yield user_value_collection.create_index(campaign_idx)
    yield user_value_collection.create_index(user_idx)

    user_keyword_frequency_collection = yield get_user_keyword_frequency_collection()
    yield user_keyword_frequency_collection.create_index(user_idx)
    yield user_keyword_frequency_collection.create_index(keyowrd_idx)

    user_profile_collection = yield get_user_profile_collection()
    yield user_profile_collection.create_index(user_idx)

    keyword_frequency_collection = yield get_keyword_frequency_collection()
    yield keyword_frequency_collection.create_index(updated_idx)
    yield keyword_frequency_collection.create_index(keyowrd_idx)
    yield logger.debug('Database configured successfully.')

    # Add default campaign
    yield campaign_collection.replace_one({'campaign_id': 'not_found'},
                                          {
                                              'campaign_id': 'not_found',
                                              'time_start': 0,
                                              'time_end': 1899999999,
                                              'filters': {'require': {}, 'exclude': {}},
                                              'keywords': {},
                                              'banners': [],
                                              'max_cpc': 0,
                                              'max_cpm': 0,
                                              'budget': 0
                                              },
                                          upsert=True)


@defer.inlineCallbacks
def get_mongo_db():
    """
    :return: Database
    """
    conn = yield get_mongo_connection()
    defer.returnValue(getattr(conn, db_const.MONGO_DB_NAME))


@defer.inlineCallbacks
def get_payment_collection():
    """
    :return: Payments collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.payments)


@defer.inlineCallbacks
def get_payment_rounds_collection():
    """
    :return: Payment rounds collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.payments_rounds)


@defer.inlineCallbacks
def get_campaign_collection():
    """
    :return: Campaign collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.campaign)


@defer.inlineCallbacks
def get_banner_collection():
    """
    :return: Banner collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.banners)


@defer.inlineCallbacks
def get_event_collection():
    """
    :return: Event collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.events)


@defer.inlineCallbacks
def get_user_value_collection():
    """
    :return: User values collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.user_values)


@defer.inlineCallbacks
def get_user_score_collection():
    """
    :return: User score collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.user_scores)


@defer.inlineCallbacks
def get_user_keyword_frequency_collection():
    """
    :return: User keyword frequency collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.user_keyword_frequency)


@defer.inlineCallbacks
def get_user_profile_collection():
    """
    :return: User profile collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.user_profile)


@defer.inlineCallbacks
def get_keyword_frequency_collection():
    """
    :return: Keyword collection
    """
    mongo_db = yield get_mongo_db()
    defer.returnValue(mongo_db.keyword_frequency)


#: Global MongoDB connection
MONGO_CONNECTION = None


@defer.inlineCallbacks
def get_mongo_connection():
    """
    :return: Global MongoDB connection (MONGO_CONNECTION)
    """
    global MONGO_CONNECTION
    if MONGO_CONNECTION is None:
        MONGO_CONNECTION = yield txmongo.lazyMongoConnectionPool(port=db_const.MONGO_DB_PORT)
    defer.returnValue(MONGO_CONNECTION)


@defer.inlineCallbacks
def disconnect():
    """
    Disconnect the global connection

    :return:
    """
    global MONGO_CONNECTION
    conn = yield get_mongo_connection()
    yield conn.disconnect()
    MONGO_CONNECTION = None
