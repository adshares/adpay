from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils
from adpay.stats import consts as stats_consts


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_campaign_payments(self):
        payment_percentage_cutoff = 0.5

        yield db_utils.update_campaign("campaign_id", 1234, 3456, 10, 20, 1000, {})

        yield stats_utils.calculate_events_payments("campaign_id", 3600,
                                                    payment_percentage_cutoff=payment_percentage_cutoff)

