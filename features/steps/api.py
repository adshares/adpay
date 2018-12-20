from behave import *
import json


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
    context.request['params'] = json.loads(context.text)


@when('I request resource')
def step_impl(context):
    context.response = context.txserver.get_response(context.request['method'], context.request['params'])


@then('The response should be "{code}"')
def step_impl(context, code):
    pass

    #def test_code(response):
    #    assert response.code == int(code)

    #context.response.addCallback(test_code)


@then('The response should contain')
def step_impl(context):

    def test_code(response, resp_text):
        assert response == json.loads(resp_text)

    resp_text = context.text
    context.response.addCallback(test_code, resp_text)
