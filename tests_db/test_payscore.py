from twisted.internet import defer

import tests
from adpay.db import utils as db_utils
from adpay.stats import utils as stats_utils
from adpay.stats import consts as stats_consts


class DBTestCase(tests.DBTestCase):
    @defer.inlineCallbacks
    def test_user_payment_score(self):
        # No user_id in stats.
        user_payment_score = yield stats_utils.get_user_payment_score("campaign_id", "user_id1")
        self.assertEqual(user_payment_score, 0)

        # Only user_id in stats.
        yield db_utils.update_user_value("campaign_id", "user_id1", payment=20, human_score=0.5)

        user_payment_score = yield stats_utils.get_user_payment_score("campaign_id", "user_id1")
        self.assertEqual(user_payment_score, 20 * 0.5)

        # Second user with similarity 0.02
        yield db_utils.update_user_profile("user_id1", {'keyword1': 10.0, 'keyword2': 20.0})
        yield db_utils.update_user_profile("user_id2", {'keyword1': 3.0, 'keyword3': 1.0})

        user_similarity = yield stats_utils.get_users_similarity("user_id1", "user_id2")
        self.assertEqual(user_similarity, 1.0 / stats_consts.MAX_USER_KEYWORDS_IN_PROFILE)

        yield db_utils.update_user_value("campaign_id", "user_id2", payment=50, human_score=0.8)

        user_payment_score1 = yield stats_utils.get_user_payment_score("campaign_id", "user_id1")
        user_payment_score2 = yield stats_utils.get_user_payment_score("campaign_id", "user_id2")
        self.assertEqual(user_payment_score1, user_payment_score2)

        score_components = [0.5 * 20, 0.8 * 50]
        self.assertEqual(user_payment_score1, sum(score_components) / 2.0)
