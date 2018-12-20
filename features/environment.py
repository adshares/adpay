# -- FILE: features/environment.py
from behave import use_fixture
from features.fixtures import *
from twisted.internet import reactor, defer
from tests import DBTestCase


def before_tag(context, tag):
    if tag == "fixture.adpay.server":
        use_fixture(adpay_server, context)


def before_scenario(context, scenario):

    context.dtc = DBTestCase()
    context.dfr = defer.Deferred()
    context.dfr.addCallback(context.dtc.setUp)


def after_scenario(context, scenario):

    context.dfr.addCallback(context.dtc.tearDown)


def after_all(context):
    reactor.callLater(1, reactor.stop)
    reactor.run()
