# -- FILE: features/environment.py
from twisted.internet import reactor, defer
from tests import DBTestCase
from adpay.iface.server import configure_iface
from adpay import db
from tests import WebTestCase


def before_tag(context, tag):
    if tag == "fixture.adpay.db":
        context.apday_db_direct = True


def before_scenario(context, scenario):
    if context.apday_db_direct:

        context.dtc = DBTestCase()
        context.dfr = defer.Deferred()
        context.dfr.addCallback(context.dtc.setUp)


def after_scenario(context, scenario):
    if context.apday_db_direct:

        context.dfr.addCallback(context.dtc.tearDown)


def before_all(context):

    context.txserver = WebTestCase()
    context.txserver.setUp()
    #context.server = configure_iface()
    #context.server_url = 'http://localhost:' + str(context.server.getHost().port)

    context.apday_db_direct = False


def after_all(context):
    reactor.callLater(4, reactor.stop)
    reactor.run()
