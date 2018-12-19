from behave import *


@given('I want to create or update a campaign using HTTP API')
def step_impl(context):
    context.request = {"jsonrpc": "2.0",
                       "id": "behave_test",
                       "method": 'campaign_update',
                       "params": []}


@given('I want to delete a campaign using HTTP API')
def step_impl(context):
    context.request = {"jsonrpc": "2.0",
                       "id": "behave_test",
                       "method": 'campaign_delete',
                       "params": []}


@given('I want to add events using HTTP API')
def step_impl(context):
    context.request = {"jsonrpc": "2.0",
                       "id": "behave_test",
                       "method": 'add_events',
                       "params": []}


@given('I want to request payments using HTTP API')
def step_impl(context):
    context.request = {"jsonrpc": "2.0",
                       "id": "behave_test",
                       "method": 'get_payments',
                       "params": []}


