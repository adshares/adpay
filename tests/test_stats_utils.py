from unittest import TestCase

from twisted.internet import defer

from adpay.stats import utils as stats_utils, consts as stats_consts


class TestUserBudget(TestCase):

    @defer.inlineCallbacks
    def test_create_user_budget(self):

        budget = yield stats_utils.create_user_budget(None, 0, 'uid')
        self.assertEqual(0, budget['event_type']['default_value'])


class TestGetEventDefaultPayment(TestCase):

    def test_get_event_default_payment(self):

        pay = stats_utils.get_default_event_payment({'event_type': stats_consts.EVENT_TYPE_CONVERSION,
                                                     'event_value': 5},
                                                    10, 20)
        self.assertEqual(pay, 5)

        pay = stats_utils.get_default_event_payment({'event_type': stats_consts.EVENT_TYPE_CLICK,
                                                     'event_value': 5},
                                                    10, 20)
        self.assertEqual(pay, 10)

        pay = stats_utils.get_default_event_payment({'event_type': stats_consts.EVENT_TYPE_VIEW,
                                                     'event_value': 5},
                                                    10, 20)
        self.assertEqual(pay, 20/1000)


class TestFiltering(TestCase):

    def test_filter_event(self):
        """
        Test returning payment (or non-payment) reasons for events.

        1. Event not in paid events is accepted. The value is set to 0 outside of this function.
        2. Event in paid events is rejected, because it doesn't have a campaign.
        3. Event in paid events is rejected, because the banner doesn't exist.
        4. Event in paid events is rejected, because human_score is below the threshold.
        5. Event in paid events is rejected, because targeting is rejected.
        6. Event in paid events is accepted.

        :return:
        """

        # 1. Event not in paid events is accepted. The value is set to 0 outside of this function.
        event_doc = {'event_type': 'some_event'}

        payment_decision = stats_utils.filter_event(event_doc=event_doc,
                                                    campaign_doc=None,
                                                    banner_doc=None)

        self.assertEqual(stats_consts.EVENT_PAYMENT_ACCEPTED, payment_decision)

        # 2. Event in paid events is rejected, because campaign doesn't exist.
        event_doc = {'event_type': stats_consts.PAID_EVENT_TYPES[0],
                     'campaign_id': "this campaign does not exist"}

        payment_decision = stats_utils.filter_event(event_doc=event_doc,
                                                    campaign_doc=None,
                                                    banner_doc=None)

        self.assertEqual(stats_consts.EVENT_PAYMENT_REJECTED_CAMPAIGN_NOT_FOUND, payment_decision)

        payment_decision = stats_utils.filter_event(event_doc=event_doc,
                                                    campaign_doc={'removed': True},
                                                    banner_doc=None)

        self.assertEqual(stats_consts.EVENT_PAYMENT_REJECTED_CAMPAIGN_NOT_FOUND, payment_decision)

        # 3. Event in paid events is rejected, because the banner doesn't exist.
        event_doc = {'event_type': stats_consts.PAID_EVENT_TYPES[0],
                     'banner_id': "this banner does not exist"}

        payment_decision = stats_utils.filter_event(event_doc=event_doc,
                                                    campaign_doc={},
                                                    banner_doc=None)

        self.assertEqual(stats_consts.EVENT_PAYMENT_REJECTED_BANNER_NOT_FOUND, payment_decision)

        # 4. Event in paid events is rejected, because human_score is below the threshold.
        event_doc = {'event_type': stats_consts.PAID_EVENT_TYPES[0],
                     'human_score': -1.0}

        payment_decision = stats_utils.filter_event(event_doc=event_doc,
                                                    campaign_doc={},
                                                    banner_doc=True)

        self.assertEqual(stats_consts.EVENT_PAYMENT_REJECTED_HUMAN_SCORE_TOO_LOW, payment_decision)

        # 5. Event in paid events is rejected, because targeting is rejected.
        event_doc = {'event_type': stats_consts.PAID_EVENT_TYPES[0],
                     'human_score': 0.5,
                     'our_keywords': {'keyword': 'value'}}

        payment_decision = stats_utils.filter_event(event_doc=event_doc,
                                                    campaign_doc={'filters': {'require': {},
                                                                              'exclude': {'keyword': 'value'}}},
                                                    banner_doc=True)

        self.assertEqual(stats_consts.EVENT_PAYMENT_REJECTED_INVALID_TARGETING, payment_decision)

        # 6. Event in paid events is accepted.
        event_doc = {'event_type': stats_consts.PAID_EVENT_TYPES[0],
                     'human_score': 0.5,
                     'our_keywords': {}}

        payment_decision = stats_utils.filter_event(event_doc=event_doc,
                                                    campaign_doc={'filters': {'require': {},
                                                                              'exclude': {}}},
                                                    banner_doc=True)

        self.assertEqual(stats_consts.EVENT_PAYMENT_ACCEPTED, payment_decision)
