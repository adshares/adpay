# -- FILE: features/environment.py
from twisted.internet import reactor, defer
from tests import DBTestCase
from adpay.iface.server import configure_iface
from adpay import db
from tests import WebTestCase


def before_tag(context, tag):
    if tag == "fixture.adpay.db":
        context.apday_db_direct = True


def before_all(context):

    context.txserver = WebTestCase()
    context.txserver.setUp()

    context.apday_db_direct = False


def after_all(context):
    reactor.callLater(2, reactor.stop)
    reactor.run()
