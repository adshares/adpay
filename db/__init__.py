from txmongo import filter
import txmongo

def configure_db():
    get_mongo_db()

    #Creating indexes when daemon starts
    campaign_idx = filter.sort(filter.ASCENDING("campaign_id"))
    timestamp_idx = filter.sort(filter.ASCENDING("timestamp"))

    #Campaign collection
    get_campaign_collection().create_index(campaign_idx, unique=True)

    #Stats collection
    get_campaign_collection().create_index(timestamp_idx, unique=True)


def get_mongo_db():
    return get_mongo_connection().spotree


def get_payment_stat_collection():
    return get_mongo_db().payments


def get_campaign_collection():
    return get_mongo_db().campaign


MONGO_CONNECTION = None
def get_mongo_connection():
    global MONGO_CONNECTION
    if MONGO_CONNECTION is None:
        print "Starting lazy connection"
        MONGO_CONNECTION = txmongo.lazyMongoConnectionPool()
    return MONGO_CONNECTION


def disconnect():
    get_mongo_connection().disconnect()
