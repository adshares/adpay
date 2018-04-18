from twisted.internet import defer

from adpay import tests as db_tests
from adpay import utils as db_utils


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_userkeywordfreq(self):
        for i in range(150):
            keyword = 'keyword_%s' % (i % 20)
            user_id = "user_%s" % i
            freq = i * 0.001

            yield db_utils.update_user_keyword_frequency(user_id, keyword, freq)
            user_keyword_freq_doc = yield db_utils.get_user_keyword_frequency(user_id, keyword)
            self.assertEqual(user_keyword_freq_doc['frequency'], freq)

            yield db_utils.update_user_keyword_frequency(user_id, keyword, 2 * freq)
            user_keyword_freq_doc = yield db_utils.get_user_keyword_frequency(user_id, keyword)
            self.assertEqual(user_keyword_freq_doc['frequency'], 2 * freq)

            yield db_utils.delete_user_keyword_frequency(user_keyword_freq_doc['_id'])
            user_keyword_freq_doc = yield db_utils.get_user_keyword_frequency(user_id, keyword)
            self.assertIsNone(user_keyword_freq_doc)

        for i in range(100):
            user_id = "user_%s" % (i % 20)
            keyword = 'keyword_%s' % i
            freq = i * 0.001
            yield db_utils.update_user_keyword_frequency(user_id, keyword, freq)

        distinct_uids = yield db_utils.get_user_keyword_frequency_distinct_userids()
        self.assertFalse(set(distinct_uids) - set([u"user_%s" % i for i in range(20)]))
