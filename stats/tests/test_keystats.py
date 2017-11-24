from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_keyword_stats_update(self):
        recalculate_per_views, cutoff, deckay = 1000, 0.00001, 0.01
