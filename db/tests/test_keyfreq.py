from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils


class DBTestCase(db_tests.DBTestCase):

    @defer.inlineCallbacks
    def test_keyfreq(self):
        # Test adding, getting and updating keyword frequency
        for i in range(100):
            keyword = "keyword%s"%i
            freq = 0.001*i

            yield db_utils.update_keyword_frequency(
                keyword=keyword,
                frequency=freq,
            )

            keyword_freq_doc = yield db_utils.get_keyword_frequency(keyword)
            self.assertEqual(keyword_freq_doc['keyword'], keyword)
            self.assertEqual(keyword_freq_doc['frequency'], freq)
            self.assertTrue(keyword_freq_doc['updated'])


            yield db_utils.update_keyword_frequency(
                keyword = keyword,
                frequency = 2*freq,
                updated=False
            )
            keyword_freq_doc = yield db_utils.get_keyword_frequency(keyword)
            self.assertEqual(keyword_freq_doc['frequency'], 2*freq)

        _iter = yield db_utils.get_no_updated_keyword_frequency_iter()
        counter = 0
        while True:
            keyword_freq_doc = yield _iter.next()
            if keyword_freq_doc is None:
                break
            counter+=1
        self.assertEqual(100, counter)

        # Set updated flag
        yield db_utils.set_keyword_frequency_updated_flag(updated=True)
        for i in range(100):
            keyword = "keyword%s"%i
            freq = 2*0.001*i

            keyword_freq_doc = yield db_utils.get_keyword_frequency(keyword)
            self.assertTrue(keyword_freq_doc['updated'])
            self.assertEqual(keyword_freq_doc['frequency'], freq)

            yield db_utils.delete_keyword_frequency(keyword_freq_doc['_id'])
            keyword_freq_doc = yield db_utils.get_keyword_frequency(keyword)
            self.assertIsNone(keyword_freq_doc)
