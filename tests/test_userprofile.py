from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils
from adpay.stats import consts as stats_consts


class DBTestCase(tests.db_test_case):
    @defer.inlineCallbacks
    def test_user_profile(self):
        global_freq_cutoff = 0.1

        for user_id in range(10):
            user = "user_%s" % user_id
            for keyword_id in range(100):
                keyword = "keyword_%s" % keyword_id
                yield db_utils.update_user_keyword_frequency(user, keyword, (user_id+keyword_id)*1.0/100, updated=False)

        yield stats_utils.update_user_keywords_profiles(global_freq_cutoff=global_freq_cutoff)

        # As global keyword frequencies are empty, profiles should not be constructed.
        for user_id in range(10):
            user = "user_%s" % user_id
            user_profile_doc = yield db_utils.get_user_profile(user)
            if user_profile_doc:
                self.assertEqual(user_profile_doc['profile'], {})

        # Add global frequencies and recalculate profiles.
        for keyword_id in range(100):
            keyword = "keyword_%s" % keyword_id
            frequency = max([(100-3*keyword_id)*1.0/100, 0])
            yield db_utils.update_keyword_frequency(keyword, frequency)

        yield stats_utils.update_user_keywords_profiles(global_freq_cutoff=global_freq_cutoff)

        for user_id in range(10):
            user = "user_%s" % user_id
            user_profile_doc = yield db_utils.get_user_profile(user)
            self.assertEqual(len(user_profile_doc['profile']), stats_consts.MAX_USER_KEYWORDS_IN_PROFILE)

            for keyword in sorted(user_profile_doc['profile']):
                keyword_id = int(keyword.split("_")[1])

                user_keyword_freq = (user_id+keyword_id)*1.0/100
                global_keyword_freq = max([(100-3*keyword_id)*1.0/100, 0])

                self.assertLessEqual(global_keyword_freq, global_freq_cutoff)
                self.assertEqual(user_profile_doc['profile'][keyword], user_keyword_freq/(0.01 + global_keyword_freq))
