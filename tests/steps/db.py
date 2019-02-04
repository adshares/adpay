from behave import *

from adpay.db import utils
from adpay.utils.utils import timestamp2hour


def write_to_db(table, update_function):
    """
    Convert behave table format to database friendly (dictionary) data

    :param table: Behave table.
    :param update_function: Function to use to save to database.
    :return:
    """
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


@then('I have a payment round in DB timestamp "{timestamp}"')
def step_impl(context, timestamp):

    def test_doc(doc):
        assert doc is not None
        assert 'timestamp' in doc
        assert doc['timestamp'] <= timestamp

    timestamp = timestamp2hour(timestamp)
    last_round_doc = utils.get_payment_round(timestamp)
    last_round_doc.addCallback(test_doc)


@then('I have "{number}" payments for timestamp "{timestamp}" and "{event_id}"')
def step_impl(context, number, timestamp, event_id):

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

    timestamp = timestamp2hour(timestamp)
    qa = QueryAnalyzer(utils.get_payments_iter(timestamp))
    assert qa.length == int(number)
