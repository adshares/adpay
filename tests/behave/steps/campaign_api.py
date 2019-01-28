import json

import requests as req
from behave import *

from adpay.iface.proto import *


def row_to_object(row, cls, defaults=None):
    if not defaults:
        defaults = {}

    dict_repr = defaults

    for attr in row.headings:
        if hasattr(cls, attr):
            prop = getattr(cls, attr)
            if isinstance(prop, jsonobject.IntegerProperty):
                dict_repr[attr] = int(row[attr])
            elif isinstance(prop, jsonobject.StringProperty):
                dict_repr[attr] = str(row[attr])
            elif isinstance(prop, jsonobject.FloatProperty):
                dict_repr[attr] = float(row[attr])
            elif isinstance(prop, RequireExcludeObject):
                dict_repr[attr] = RequireExcludeObject(require={},
                                                       exclude={})
            elif isinstance(prop, jsonobject.DictProperty):
                dict_repr[attr] = json.loads(row[attr])

    return dict_repr


@given('A campaign sent through api')
def step_impl(context):
    for row in context.table:
        context.campaign = row

    context.campaign_banners = []


@given('Banners sent through api')
def step_impl(context):
    assert context.campaign is not None
    assert len(context.campaign_banners) == 0

    for row in context.table:
        context.campaign_banners.append({'banner_id': row['banner_id'],
                                         'banner_size': row['banner_size'],
                                         'keywords': {}})

    cmp_dict = row_to_object(context.campaign, CampaignObject, {'banners': context.campaign_banners})

    data = {"jsonrpc": "2.0",
            "id": "test_hit",
            "method": 'campaign_update',
            "params": [cmp_dict]}

    response = req.post(context.server_url, json=data, timeout=5)

    assert response.status_code == 200
    json_resp = json.loads(response.content)
    assert 'result' in json_resp


@given('Events sent through api')
def step_impl(context):

    event_list = []
    for row in context.table:
        event_list.append(row_to_object(row, EventObject))

    data = {"jsonrpc": "2.0",
            "id": "test_hit",
            "method": 'add_events',
            "params": event_list}

    response = req.post(context.server_url, json=data, timeout=5)
    assert response.status_code == 200
    json_resp = json.loads(response.content)
    assert 'result' in json_resp

