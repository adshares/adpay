from twisted.internet import defer

import tests
from adpay.stats import cache as stats_cache, legacy as stats_legacy


class TestAddViewKeywords(tests.db_test_case):

    @defer.inlineCallbacks
    def test_add_view_keywords(self):

        self.assertEqual(0, stats_cache.EVENTS_STATS_VIEWS)
        self.assertEqual(0, len(stats_cache.EVENTS_STATS_KEYWORDS))

        iterations = 3

        for i in xrange(1, iterations+1):
            yield stats_legacy.add_view_keywords('user', ['keyword1', 'keyword2'])

            self.assertEqual(3*i, stats_cache.EVENTS_STATS_VIEWS)
            self.assertEqual(2, len(stats_cache.EVENTS_STATS_KEYWORDS))
            self.assertEqual(1*i, stats_cache.EVENTS_STATS_KEYWORDS['keyword1'])
            self.assertEqual(1*i, stats_cache.EVENTS_STATS_KEYWORDS['keyword2'])
