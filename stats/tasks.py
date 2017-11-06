from twisted.internet import defer, reactor
from adpay.db import utils as db_utils


@defer.inlineCallbacks
def recalculate_payments():
    #TODO: guess which timestamp was last time calculated
    #TODO: get max payments per event
    #TODO: limit events only from selected timestamp period

    last_timestamp = 1234
    total_hour_budget = 1000.0

    events,  total_events= {}, 0
    docs, dfr = yield db_utils.get_user_events_iter()
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
