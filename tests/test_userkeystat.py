from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils


class DBTestCase(tests.db_test_case):
    @defer.inlineCallbacks
    def test_user_keyword_stats_update(self):
        cutoff, decay = 0.001, 0.1

        # Check stats increasing.
        key1_freq_doc, key2_freq_doc = None, None
        for i in range(100):
            yield stats_utils.update_user_keywords_stats("user_id", ["keyword_1", 'keyword_2'],
                                                         cutoff=cutoff, decay=decay)

            _key1_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_1")
            self.assertIsNotNone(_key1_freq_doc)

            _key2_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_2")
            self.assertIsNotNone(_key2_freq_doc)

            if key1_freq_doc is not None:
                self.assertEqual(decay + (1 - decay) * key1_freq_doc['frequency'], _key1_freq_doc['frequency'])

            if key2_freq_doc is not None:
                self.assertEqual(decay + (1 - decay) * key2_freq_doc['frequency'], _key1_freq_doc['frequency'])

            key1_freq_doc, key2_freq_doc = _key1_freq_doc, _key2_freq_doc

        # Check keyword frequency decay.
        yield stats_utils.update_user_keywords_stats("user_id", ["keyword_3"], cutoff=cutoff, decay=decay)
        key3_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_3")

        while True:
            yield stats_utils.update_user_keywords_stats("user_id", ["keyword_1", 'keyword_2'],
                                                         cutoff=cutoff, decay=decay)

            _key3_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_3")
            self.assertEqual(_key3_freq_doc['frequency'], key3_freq_doc['frequency'] * (1 - decay))

            if _key3_freq_doc['frequency'] * (1-decay) <= cutoff:
                break

            key3_freq_doc = _key3_freq_doc

        yield stats_utils.update_user_keywords_stats("user_id", ["keyword_1", 'keyword_2'],
                                                     cutoff=cutoff, decay=decay)
        key3_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_3")
        self.assertIsNone(key3_freq_doc)
