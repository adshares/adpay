from __future__ import print_function

import json

import requests
from behave import *


@given('I want to campaign update')
def step_impl(context):
    context.request = {"jsonrpc": "2.0",
                       "id": "behave_test",
                       "method": 'campaign_update',
                       "params": []}


@given('I want to campaign delete')
def step_impl(context):
    context.request = {"jsonrpc": "2.0",
                       "id": "behave_test",
                       "method": 'campaign_delete',
                       "params": []}


@given('I want to add events')
def step_impl(context):
    context.request = {"jsonrpc": "2.0",
                       "id": "behave_test",
                       "method": 'add_events',
                       "params": []}


@given('I want to get payments')
def step_impl(context):
    context.request = {"jsonrpc": "2.0",
                       "id": "behave_test",
                       "method": 'get_payments',
                       "params": []}


@when('I provide the data')
def step_impl(context):
    context.request_data = context.text


@when('I request resource')
def step_impl(context):

    response = requests.post(context.interface_url, context.request_data)
    context.response = response.content


@then('The response should contain')
def step_impl(context):
    try:
        assert json.loads(context.response) == json.loads(context.text)
    except AssertionError as e:
        print(e)
        print("Response received: {0}".format(context.response))
        raise

    print("BEHAVE HIDES LAST PRINT")
