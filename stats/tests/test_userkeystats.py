from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_user_keyword_stats_update(self):
        cutoff, deckay = 0.001, 0.01

        yield stats_utils.update_user_keywords_stats("user_id", ["keyword_3"], cutoff=cutoff, deckay=deckay)
        key3_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_3")

        key1_freq_doc, key2_freq_doc = None, None
        for i in range(150):
            yield stats_utils.update_user_keywords_stats("user_id", ["keyword_1", 'keyword_2'],
                                                         cutoff=cutoff, deckay=deckay)

            _key1_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_1")
            self.assertIsNotNone(_key1_freq_doc)

            _key2_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_2")
            self.assertIsNotNone(_key2_freq_doc)

            if key1_freq_doc is not None:
                self.assertEqual(deckay + (1 - deckay) * key1_freq_doc['frequency'], _key1_freq_doc['frequency'])

            if key2_freq_doc is not None:
                self.assertEqual(deckay + (1 - deckay) * key2_freq_doc['frequency'], _key1_freq_doc['frequency'])

            _key3_freq_doc = yield db_utils.get_user_keyword_frequency("user_id", "keyword_3")
            if _key3_freq_doc is not None:
                self.assertEqual(_key3_freq_doc['frequency'], key3_freq_doc['frequency'] * (1 - deckay))

            key1_freq_doc, key2_freq_doc, key3_freq_doc = _key1_freq_doc, _key2_freq_doc, _key3_freq_doc
