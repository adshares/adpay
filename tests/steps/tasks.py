from behave import *

from adpay.stats.tasks import force_payment_recalculation


@when('I execute payment calculation for timestamp "{timestamp}"')
def step_impl(context, timestamp):
    force_payment_recalculation(timestamp)
