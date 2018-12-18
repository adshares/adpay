from behave import *

from adpay.db import utils
from adpay.stats.tasks import _adpay_task


def write_to_db(table, update_function):
    for row in table:
        doc = {}
        for attr in row.headings:
            doc[attr] = row[attr]

        update_function(doc)


@given('Campaigns')
def step_impl(context):
    write_to_db(context.table, utils.update_campaign)


@given('Banners')
def step_impl(context):
    write_to_db(context.table, utils.update_banner)


@given('Events')
def step_impl(context):
    write_to_db(context.table, utils.update_event)


@when('I execute payment calculation for timestamp "{timestamp}"')
def step_impl(context, timestamp):
    _adpay_task(timestamp, int(timestamp))


@then('I have a payment round in DB timestamp "{timestamp}"')
def step_impl(context, timestamp):

    def test_doc(doc):
        assert doc is not None
        assert 'timestamp' in doc
        assert doc['timestamp'] <= timestamp

    last_round_doc = utils.get_payment_round(timestamp)
    last_round_doc.addCallback(test_doc)


@then('I have payments for timestamp "{timestamp}" and "{event_id}"')
def step_impl(context, timestamp, event_id):

    class QueryAnalyzer:

        def __init__(self, dfr):
            self.finished = False
            self.length = 0
            dfr.addCallback(self.test_query)

        def test_query(self, query):
            assert query is not None
            assert isinstance(query, utils.QueryIterator)

            while not self.finished:
                payment_doc = yield query.next()
                if not payment_doc:
                    self.finished = True
                self.length += 1

    qa = QueryAnalyzer(utils.get_payments_iter(timestamp))
    assert qa.length > 0
