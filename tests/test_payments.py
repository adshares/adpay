from twisted.internet import defer

from adpay import tests as db_tests
from adpay import utils as db_utils
from adpay import consts as db_consts
from adpay import utils as stats_utils


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_campaign_payments(self):
        payment_percentage_cutoff = 0.5
        cpv, cpc = 10, 20

        yield db_utils.update_campaign("campaign_id", 1234, 3456, cpc, cpv, 1000, {})

        yield db_utils.update_banner("banner_id1", "campaign_id")
        yield db_utils.update_banner("banner_id2", "campaign_id")
        yield db_utils.update_banner("banner_id3", "campaign_id")

        # Add events for users
        yield db_utils.update_event("event1_user_id1", db_consts.EVENT_TYPE_CLICK,
                                    3601, "user_id1", "banner_id1", "campaign_id", 0, {}, 0.1)
        yield db_utils.update_event("event2_user_id1", db_consts.EVENT_TYPE_VIEW,
                                    3600, "user_id1", "banner_id1", "campaign_id", 0, {}, 0.2)

        yield db_utils.update_event("event1_user_id2", db_consts.EVENT_TYPE_CONVERSION,
                                    3602, "user_id2", "banner_id3", "campaign_id", 100, {}, 0.5)

        yield stats_utils.calculate_events_payments("campaign_id", 3600,
                                                    payment_percentage_cutoff=payment_percentage_cutoff)

        # Check user values
        user2_value_doc = yield db_utils.get_user_value("campaign_id", "user_id2")
        self.assertEqual(user2_value_doc['payment'], 100)
        self.assertEqual(user2_value_doc['human_score'], 0.5)

        # Check payments
        _iter = yield db_utils.get_payments_iter(3600)
        while True:
            event_payment_doc = yield _iter.next()
            if event_payment_doc is None:
                break

            self.assertEqual(event_payment_doc['payment'], 100)
            self.assertEqual(event_payment_doc['campaign_id'], "campaign_id")

        # User scores should be empty.
        _iter = yield db_utils.get_sorted_user_score_iter("campaign_id", 3600, limit=1)
        user_score_doc = yield _iter.next()
        self.assertIsNone(user_score_doc)
