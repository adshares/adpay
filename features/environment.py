# -- FILE: features/environment.py
from behave import use_fixture
from features.fixtures import *
from twisted.internet import reactor
from tests import DBTestCase


def before_tag(context, tag):
    if tag == "fixture.adpay.server":
        use_fixture(adpay_server, context)


def before_scenario(context, scenario):

    context.dtc = DBTestCase()
    context.dtc.setUp()


def after_scenario(context, scenario):
    reactor.callLater(1, reactor.stop)
    reactor.run()
    # context.dtc.tearDown()
