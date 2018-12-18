# -- FILE: features/environment.py
from behave import use_fixture
from features.fixtures import *


def before_tag(context, tag):
    if tag == "fixture.adpay.server":
        use_fixture(adpay_server, context)
