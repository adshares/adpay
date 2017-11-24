from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_userscore(self):
        # Test adding user scores
        for i in range(100):
            yield db_utils.update_user_score(campaign_id="campaign_id",
                                             timestamp=3500,
                                             user_id=i,
                                             score=i * 10)

        _iter = yield db_utils.get_sorted_user_score_iter("campaign_id", 3600, 50)
        last_score, counter = None, 0
        while True:
            user_score_doc = yield _iter.next()
            if user_score_doc is None:
                break
            if last_score is not None:
                self.assertGreater(last_score, user_score_doc['score'])
            last_score = user_score_doc['score']
            counter += 1
        self.assertEqual(counter, 50)

        # Test updating user scores
        for i in range(100):
            yield db_utils.update_user_score(campaign_id="campaign_id",
                                             timestamp=3500,
                                             user_id=i,
                                             score=i * 100)

        _iter = yield db_utils.get_sorted_user_score_iter("campaign_id", 3600, 50)
        while True:
            user_score_doc = yield _iter.next()
            if user_score_doc is None:
                break

            self.assertEqual(user_score_doc['user_id'] * 100, user_score_doc['score'])

        # Test deleting user scores.
        yield db_utils.delete_user_scores("campaign_id", 3600)

        _iter = yield db_utils.get_sorted_user_score_iter("campaign_id", 3600, 100)
        counter = 0
        while True:
            user_score_doc = yield _iter.next()
            if user_score_doc is None:
                break
            counter += 1
        self.assertEqual(counter, 0)
