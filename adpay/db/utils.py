from twisted.internet import defer
from txmongo import filter as txfilter

from adpay import db
from adpay.utils import utils as common_utils


class QueryIterator(object):
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
        """
        Constructor

        :param query: Query object.
        """
        self.query = query

        self.docs, self.dfr = None, None
        self.docs_index = 0

    @defer.inlineCallbacks
    def next(self):
        """
        Generator for items.

        :return: item.
        """
        if self.docs is None:
            self.docs, self.dfr = yield self.query

        if not self.docs:
            defer.returnValue(None)

        if self.docs_index >= len(self.docs):
            self.docs, self.dfr = yield self.dfr
            self.docs_index = 0
            value = yield self.next()
            defer.returnValue(value)

        try:
            value = self.docs[self.docs_index]
            self.docs_index += 1
            defer.returnValue(value)
        except IndexError:
            raise StopIteration()


# Campaigns
@defer.inlineCallbacks
def get_campaign(campaign_id):
    """

    :param campaign_id: Campaign id
    :return: Campaign document
    """
    collection = yield db.get_campaign_collection()
    return_value = yield collection.find_one({'campaign_id': campaign_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_campaign_iter():
    """

    :return: Iterable campaign collection as a QueryIterator.
    """
    collection = yield db.get_campaign_collection()
    defer.returnValue(QueryIterator(collection.find(cursor=True)))


@defer.inlineCallbacks
def update_campaign(campaign_doc):
    """
    Update campaign data or create one if doesn't exist.

    :param campaign_doc: New campaign data, must include campaign_id to identify existing data.
    :return: deferred instance of :class:`pymongo.results.UpdateResult`.
    """
    collection = yield db.get_campaign_collection()
    return_value = yield collection.replace_one({'campaign_id': campaign_doc['campaign_id']},
                                                campaign_doc, upsert=True)

    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_campaign(campaign_id):
    """
    Remove campaign from the database.

    :param campaign_id: Main identifier.
    :return:
    """
    collection = yield db.get_campaign_collection()
    return_value = yield collection.delete_many({'campaign_id': campaign_id})
    defer.returnValue(return_value)


# Banners
@defer.inlineCallbacks
def get_banner(banner_id):
    """

    :param banner_id: Banner identifier.
    :return: Banner document.
    """
    collection = yield db.get_banner_collection()
    return_value = yield collection.find_one({'banner_id': banner_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_banners_iter():
    """

    :return: Iterable banner collection (QueryIterator)
    """
    collection = yield db.get_banner_collection()
    defer.returnValue(QueryIterator(collection.find(cursor=True)))


@defer.inlineCallbacks
def get_campaign_banners(campaign_id):
    """
    Get banners for the campaign.

    :param campaign_id: Campaign identifier.
    :return: List of banner documents.
    """
    collection = yield db.get_banner_collection()
    return_value = yield collection.find({'campaign_id': campaign_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def update_banner(banner_doc):
    """
    Update banner data or create a new one if doesn't exist.

    :param banner_doc: New banner data, must include banner_id.
    :return: deferred instance of :class:`pymongo.results.UpdateResult`.
    """
    collection = yield db.get_banner_collection()
    return_value = yield collection.replace_one({'banner_id': banner_doc['banner_id']},
                                                banner_doc, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_campaign_banners(campaign_id):
    """
    Remove banners (for a campaign) from the database.

    :param campaign_id: Campaign identifier.
    :return:
    """
    collection = yield db.get_banner_collection()
    return_value = yield collection.delete_many({'campaign_id': campaign_id})
    defer.returnValue(return_value)


# Events
@defer.inlineCallbacks
def update_event(event_doc):
    """
    Create or update an event

    :param event_obj: event JSONObject
    :return:
    """

    event_doc['timestamp'] = common_utils.timestamp2hour(event_doc['timestamp'])
    collection = yield db.get_event_collection()

    return_value = yield collection.replace_one({'event_id': event_doc['event_id']},
                                                event_doc,
                                                upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_banner_events_iter(banner_id, timestamp):
    """

    :param banner_id: Banner identifier.
    :param timestamp: Time in seconds since the epoch, used for getting the full hour timestamp.
    :return: Iterable events for the banner.
    """
    timestamp = common_utils.timestamp2hour(timestamp)
    collection = yield db.get_event_collection()

    defer.returnValue(QueryIterator(collection.find({
        'banner_id': banner_id,
        'timestamp': common_utils.timestamp2hour(timestamp)
        }, cursor=True)))


@defer.inlineCallbacks
def get_user_events_iter(campaign_id, timestamp, uid):
    """

    :param campaign_id: Campaign identifier.
    :param timestamp: Time in seconds since the epoch, used for getting the full hour timestamp.
    :param uid: User identifier.
    :return: Iterable events for the user.
    """
    timestamp = common_utils.timestamp2hour(timestamp)
    collection = yield db.get_event_collection()

    defer.returnValue(QueryIterator(collection.find({
        'user_id': uid,
        'campaign_id': campaign_id,
        'timestamp': timestamp
        }, cursor=True)))


@defer.inlineCallbacks
def get_events_distinct_uids(campaign_id, timestamp):
    """

    :param campaign_id: Campaign identifier.
    :param timestamp: Time in seconds since the epoch, used for getting the full hour timestamp.
    :return: Return list of distinct users ids for the given campaign timestamp.
    """
    timestamp = common_utils.timestamp2hour(timestamp)
    collection = yield db.get_event_collection()

    return_values = yield collection.distinct(key='user_id',
                                              filter={'timestamp': timestamp, 'campaign_id': campaign_id})
    defer.returnValue(return_values)


@defer.inlineCallbacks
def delete_event(event_id):
    """
    Remove event from collection.

    :param event_id:
    :return:
    """
    collection = yield db.get_event_collection()
    return_value = yield collection.delete_many({'event_id': event_id})
    defer.returnValue(return_value)


# Event payments
@defer.inlineCallbacks
def update_event_payment(campaign_id, timestamp, event_id, payment):
    """
    Create or update payment information for event.

    :param campaign_id:
    :param timestamp:
    :param event_id:
    :param payment:
    :return:
    """
    timestamp = common_utils.timestamp2hour(timestamp)
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
    """

    :param timestamp:
    :return: Iterable payment information.
    """
    timestamp = common_utils.timestamp2hour(timestamp)
    collection = yield db.get_payment_collection()

    defer.returnValue(QueryIterator(collection.find({
        'timestamp': timestamp
        }, cursor=True)))


# Calculated payments rounds
@defer.inlineCallbacks
def get_payment_round(timestamp):
    """

    :param timestamp:
    :return: One payment round.
    """
    timestamp = common_utils.timestamp2hour(timestamp)
    collection = yield db.get_payment_rounds_collection()

    return_value = yield collection.find_one({'timestamp': timestamp})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def update_payment_round(timestamp):
    """
    Create or update a payment round.

    :param timestamp:
    :return:
    """
    timestamp = common_utils.timestamp2hour(timestamp)
    collection = yield db.get_payment_rounds_collection()

    round_doc = {'timestamp': timestamp}
    return_value = yield collection.update(round_doc, round_doc, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_payment_round_iter():
    """

    :return: Iterable payment round collection.
    """
    collection = yield db.get_payment_rounds_collection()
    defer.returnValue(QueryIterator(collection.find(cursor=True)))


@defer.inlineCallbacks
def delete_payment_round(timestamp):
    """
    Remove the payment round from collection.

    :param timestamp:
    :return:
    """
    timestamp = common_utils.timestamp2hour(timestamp)
    collection = yield db.get_payment_rounds_collection()

    return_value = yield collection.delete_many({'timestamp': timestamp})
    defer.returnValue(return_value)


# User Values (Columns: campaign_id, user_id, payment, human_score)
@defer.inlineCallbacks
def get_user_value_iter(campaign_id):
    """

    :param campaign_id:
    :return: Iterable user value collection.
    """
    collection = yield db.get_user_value_collection()
    defer.returnValue(QueryIterator(collection.find({'campaign_id': campaign_id}, cursor=True)))


@defer.inlineCallbacks
def get_user_value(campaign_id, user_id):
    """

    :param campaign_id:
    :param user_id:
    :return: One user value document.
    """
    collection = yield db.get_user_value_collection()

    return_value = yield collection.find_one({
        'campaign_id': campaign_id,
        'user_id': user_id
        })
    defer.returnValue(return_value)


@defer.inlineCallbacks
def update_user_value(campaign_id, user_id, payment, human_score):
    """
    Create or update the user value document.

    :param campaign_id:
    :param user_id:
    :param payment:
    :param human_score:
    :return:
    """
    collection = yield db.get_user_value_collection()

    return_value = yield collection.replace_one({
        'campaign_id': campaign_id,
        'user_id': user_id
        }, {
        'campaign_id': campaign_id,
        'user_id': user_id,
        'payment': payment,
        'human_score': human_score
        }, upsert=True)
    defer.returnValue(return_value)


# User scores (Columns: campaign_id, timestamp, user_id, score)
@defer.inlineCallbacks
def get_sorted_user_score_iter(campaign_id, timestamp, limit):
    """

    :param campaign_id:
    :param timestamp:
    :param limit:
    :return: Descending by score sorted list of user score to limit.
    """
    timestamp = common_utils.timestamp2hour(timestamp)
    collection = yield db.get_user_score_collection()

    defer.returnValue(QueryIterator(collection.find({'campaign_id': campaign_id, 'timestamp': timestamp},
                                                    sort=txfilter.sort(txfilter.DESCENDING("score")),
                                                    limit=limit, cursor=True)))


@defer.inlineCallbacks
def update_user_score(campaign_id, timestamp, user_id, score):
    """
    Update user score with new score and timestamp, per campaign.

    :param campaign_id:
    :param timestamp:
    :param user_id:
    :param score:
    :return:
    """

    timestamp = common_utils.timestamp2hour(timestamp)
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
    """

    :param campaign_id:
    :param timestamp:
    :return:
    """
    timestamp = common_utils.timestamp2hour(timestamp)
    collection = yield db.get_user_score_collection()

    return_value = yield collection.delete_many({'campaign_id': campaign_id, 'timestamp': timestamp})
    defer.returnValue(return_value)


# User keywords frequency (user_id, keyword, frequency, updated=True)
@defer.inlineCallbacks
def update_user_keyword_frequency(user_id, keyword, frequency, updated=True):
    """
    Create or update user keyword frequency document.

    :param user_id:
    :param keyword:
    :param frequency:
    :param updated:
    :return:
    """
    collection = yield db.get_user_keyword_frequency_collection()
    return_value = yield collection.replace_one({
        'keyword': keyword,
        'user_id': user_id
        }, {
        'keyword': keyword,
        'user_id': user_id,
        'frequency': frequency,
        'updated': updated
        }, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def set_user_keyword_frequency_updated_flag(updated=False):
    """
    Set/update the 'updated' flag in user keyword frequency document.

    :param updated: Flag value (True/False)
    :return:
    """
    collection = yield db.get_user_keyword_frequency_collection()
    return_value = yield collection.update_many({}, {"$set": {"updated": updated}})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_user_keyword_frequency(user_id, keyword):
    """

    :param user_id:
    :param keyword:
    :return: One use keyword frequency document.
    """
    collection = yield db.get_user_keyword_frequency_collection()
    return_value = yield collection.find_one({'keyword': keyword, 'user_id': user_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_user_keyword_frequency_iter(user_id):
    """

    :param user_id:
    :return: Iterable user keyword frequency collection.
    """
    collection = yield db.get_user_keyword_frequency_collection()
    defer.returnValue(QueryIterator(collection.find({'user_id': user_id}, cursor=True)))


@defer.inlineCallbacks
def get_user_keyword_frequency_distinct_userids():
    """

    :return: Distinct user ids.
    """
    collection = yield db.get_user_keyword_frequency_collection()
    return_value = yield collection.distinct(key='user_id')
    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_user_keyword_frequency(_id):
    """
    Remove user keyword documents.

    :param _id: Mongo document _id.
    :return:
    """
    collection = yield db.get_user_keyword_frequency_collection()
    return_value = yield collection.delete_one({'_id': _id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def update_user_profile(user_id, profile_dict):
    """
    Create or update user profile.

    :param user_id: User identifier.
    :param profile_dict: Dicitonary of keyword scores, e.g. {'keyword1':score1, 'keyword2':score2, ...}
    :return:
    """
    collection = yield db.get_user_profile_collection()
    return_value = yield collection.replace_one({
        'user_id': user_id
        }, {
        'user_id': user_id,
        'profile': profile_dict
        }, upsert=True)
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_user_profile(user_id):
    """

    :param user_id:
    :return: One user profile document.
    """
    collection = yield db.get_user_profile_collection()
    return_value = yield collection.find_one({'user_id': user_id})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_user_profiles():
    """
    Remove all user profile documents.

    :return:
    """
    collection = yield db.get_user_profile_collection()
    return_value = yield collection.delete_many({})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def update_keyword_frequency(keyword, frequency, updated=True):
    """
    Create or update keyword frequency.

    :param keyword:
    :param frequency:
    :param updated:
    :return:
    """
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
    """
    Set/update the 'updated' flag in keyword frequency document.

    :param updated: Flag value (True/False)
    :return:
    """
    collection = yield db.get_keyword_frequency_collection()
    return_value = yield collection.update_many({}, {"$set": {"updated": updated}})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def get_no_updated_keyword_frequency_iter():
    """

    :return: Iterable of not updated keyword frequencies.
    """
    collection = yield db.get_keyword_frequency_collection()
    defer.returnValue(QueryIterator(collection.find({'updated': False}, cursor=True)))


@defer.inlineCallbacks
def get_keyword_frequency(keyword):
    """

    :param keyword:
    :return: One keyword frequency document.
    """
    collection = yield db.get_keyword_frequency_collection()
    return_value = yield collection.find_one({'keyword': keyword})
    defer.returnValue(return_value)


@defer.inlineCallbacks
def delete_keyword_frequency(_id):
    """
    Remove keyword frequency document.

    :param _id: Mongo document identifier.
    :return:
    """
    collection = yield db.get_keyword_frequency_collection()
    return_value = yield collection.delete_many({'_id': _id})
    defer.returnValue(return_value)
