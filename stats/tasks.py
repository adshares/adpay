from twisted.internet import defer, reactor

from adpay.db import utils as db_utils
from adpay.db import consts as db_consts
from adpay.stats import utils as stats_utils


def get_user_payment_score(campaign_id, timestamp, user_id, amount=5):
    # To calculate user value take user values from the previous hour
    previous_hour = timestamp - 3600

    # Find most similar users to user_id
    users = []
    for uid in db_utils.user_value_uids_iter(campaign_id, previous_hour):
        similarity = stats_utils.get_users_similarity(uid, user_id)
        stats_utils.reverse_insort(users, (similarity, uid))
        users = users[:amount]

    # Calculate payment score for user
    score_components = []
    for similarity, uid in users:
        user_stat = db_utils.get_user_value(campaign_id, previous_hour, uid)
        if user_stat:
            score_components.append(user_stat['payment']*user_stat['credibility'])

    if not score_components:
        return 0

    return 1.0*sum(score_components)/len(score_components)


def calculate_events_payments(campaign_id, timestamp, payment_percentage_cutoff=0.5):
    campaign_doc = db_utils.get_campaign(campaign_id)
    if campaign_doc is None:
        return

    campaign_budget = campaign_doc['budget']
    campaign_cpc = campaign_doc['max_cpc']
    campaign_cpv = campaign_doc['max_cpv']

    # Saving payment scores for users.
    total_users = 0
    for uid in db_utils.get_events_distinct_uids_iter(campaign_id, timestamp):
        payment_score = get_user_payment_score(uid)
        db_utils.update_user_score(campaign_id, timestamp, uid, payment_score)
        total_users +=1

    # Limit paid users to given payment_percentage_cutoff
    limit = total_users*payment_percentage_cutoff

    total_score = 0
    for user_score_doc in db_utils.get_sorted_user_score_iter(campaign_id, timestamp, limit=limit):
        total_score+= user_score_doc['score']

    for user_score_doc in db_utils.get_sorted_user_score_iter(campaign_id, timestamp, limit=limit):
        uid = user_score_doc['uid']

        # Calculate event payments
        user_budget = 1.0*user_score_doc['score']*campaign_budget/total_score
        max_user_payment, total_user_payments = 0, 0
        for event_doc in db_utils.get_events_iter(campaign_id, timestamp, uid):
            event_payment = stats_utils.get_event_max_payment(event_doc, campaign_cpc, campaign_cpv)

            total_user_payments += event_payment
            max_user_payment = max([max_user_payment, event_payment])


        for event_doc in db_utils.get_events_iter(campaign_id, timestamp, uid):
            event_id = event_doc['event_id']
            event_payment = stats_utils.get_event_max_payment(event_doc, campaign_cpc, campaign_cpv)

            event_budget = user_budget*event_payment/total_user_payments
            yield db_utils.update_event_payment(campaign_id, timestamp, event_id, event_budget)


        # Update User Values
        user_credibility = stats_utils.get_user_credibility(uid)
        yield db_utils.update_user_value(campaign_id, timestamp, uid, max_user_payment, user_credibility)

    # Delete user scores
    yield db_utils.delete_user_scores(campaign_id, timestamp)


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
