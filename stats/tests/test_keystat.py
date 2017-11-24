from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils
from adpay.stats import cache as stats_cache


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_keyword_stats_update(self):
        recalculate_per_views, cutoff, deckay = 1, 0.001, 0.1

        # Check stats increasing.
        key1_freq_doc, key2_freq_doc = None, None
        for i in range(100):
            stats_cache.keyword_inc("keyword_1")
            stats_cache.keyword_inc("keyword_2")
            stats_cache.views_inc()

            yield stats_utils.update_keywords_stats(recalculate_per_views=recalculate_per_views,
                                                    cutoff=cutoff,
                                                    deckay=deckay)

            _key1_freq_doc = yield db_utils.get_keyword_frequency("keyword_1")
            self.assertIsNotNone(_key1_freq_doc)

            _key2_freq_doc = yield db_utils.get_keyword_frequency("keyword_2")
            self.assertIsNotNone(_key2_freq_doc)

            if key1_freq_doc is not None:
                self.assertEqual(deckay + (1 - deckay) * key1_freq_doc['frequency'], _key1_freq_doc['frequency'])

            if key2_freq_doc is not None:
                self.assertEqual(deckay + (1 - deckay) * key2_freq_doc['frequency'], _key1_freq_doc['frequency'])

            key1_freq_doc, key2_freq_doc = _key1_freq_doc, _key2_freq_doc

        # Check keyword frequency deckay.
        stats_cache.keyword_inc("keyword_3")
        stats_cache.views_inc()
        yield stats_utils.update_keywords_stats(recalculate_per_views=recalculate_per_views,
                                                cutoff=cutoff,
                                                deckay=deckay)
        key3_freq_doc = yield db_utils.get_keyword_frequency("keyword_3")

        while True:
            stats_cache.keyword_inc("keyword_1")
            stats_cache.keyword_inc("keyword_2")
            stats_cache.views_inc()

            yield stats_utils.update_keywords_stats(recalculate_per_views=recalculate_per_views,
                                                    cutoff=cutoff,
                                                    deckay=deckay)

            _key3_freq_doc = yield db_utils.get_keyword_frequency("keyword_3")
            self.assertEqual(_key3_freq_doc['frequency'], key3_freq_doc['frequency'] * (1 - deckay))

            if _key3_freq_doc['frequency'] * (1-deckay) <= cutoff:
                break

            key3_freq_doc = _key3_freq_doc

        stats_cache.keyword_inc("keyword_1")
        stats_cache.keyword_inc("keyword_2")
        stats_cache.views_inc()

        yield stats_utils.update_keywords_stats(recalculate_per_views=recalculate_per_views,
                                                cutoff=cutoff,
                                                deckay=deckay)
        key3_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_3")
        self.assertIsNone(key3_freq_doc)
