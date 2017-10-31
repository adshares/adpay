from twisted.internet import defer, reactor

from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils


"""
USER_VALUES = {
    'campaign_id':{
        'user_id1':{'payment':payment1, 'credibility':credibility1},
        'user_id2':{'payment':payment2, 'credibility':credibility2}
    }
}
"""
USER_VALUES = {}


def iter_user_values_uids(campaign_id):
    return USER_VALUES.get(campaign_id, {}).iterkeys()


def get_user_value_stat(campaign_id, uid):
    return USER_VALUES.get(campaign_id, {}).get(uid, {})


def get_user_payment_score(campaign_id, user_id, amount=5):
    # Find most similar users to user_id
    users = []
    for uid in iter_user_values_uids(campaign_id):
        similarity = stats_utils.get_users_similarity(uid, user_id)
        stats_utils.reverse_insort(users, (similarity, uid))
        users = users[:amount]

    # Calculate payment score for user
    score_components = []
    for similarity, uid in users:
        user_stat = get_user_value_stat(campaign_id, uid)
        if user_stat:
            score_components.append(user_stat['payment']*user_stat['credibility'])

    if not score_components:
        return 0

    return 1.0*sum(score_components)/len(score_components)


@defer.inlineCallbacks
def recalculate_payments():
    #TODO: guess which timestamp was last time calculated
    #TODO: get max payments per event
    #TODO: limit events only from selected timestamp period

    last_timestamp = 1234
    total_hour_budget = 1000.0

    events,  total_events= {}, 0
    docs, dfr = yield db_utils.get_events_iter()
    while docs:
        for event_doc in docs:
            event_id = event_doc['event_id']
            total_events+=1
            if event_id in events:
                events[event_id]+=1
            else:
                events[event_id]=1
        docs, dfr = yield dfr

    events = dict((event_id, events[event_id]*total_hour_budget/total_events) for event_id in events)

    yield db_utils.update_payments(last_timestamp, events)


def recalculate_payments_task(interval_seconds=2):
    #Recalculate payments every hour.
    recalculate_payments()
    reactor.callLater(interval_seconds, recalculate_payments_task)


def configure_tasks(interval_seconds=2):
    reactor.callLater(interval_seconds, recalculate_payments_task)
