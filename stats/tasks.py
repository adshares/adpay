from twisted.internet import defer, reactor

def recalculate_payments():
    print "recalculating payments"


def recalculate_payments_task(interval_seconds=10):
    #Recalculate payments every hour.
    reactor.callLater(interval_seconds, recalculate_payments)


def configure_tasks(interval_seconds=10):
    reactor.callLater(interval_seconds, recalculate_payments_task)