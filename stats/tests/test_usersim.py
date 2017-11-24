from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils
from adpay.stats import consts as stats_consts


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_user_similarity(self):
        yield db_utils.update_user_profile("userid_1", {'key1': 0.1, 'key2': 0.2, 'key3': 0.4})
        yield db_utils.update_user_profile("userid_2", {'key1': 0.1, 'key4': 0.8})

        user1_keywords = yield stats_utils.get_user_profile_keywords("userid_1")
        self.assertEqual(sorted(user1_keywords), ['key1', 'key2', 'key3'])

        user2_keywords = yield stats_utils.get_user_profile_keywords("userid_2")
        self.assertEqual(sorted(user2_keywords), ['key1', 'key4'])

        users_similarity = yield stats_utils.get_users_similarity("userid_1", "userid_2")
        self.assertEqual(users_similarity, 1.0 / stats_consts.MAX_USER_KEYWORDS_IN_PROFILE)
