from twisted.internet import defer

from adpay import tests as db_tests
from adpay import utils as db_utils


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_uservalue(self):
        # Test adding user value
        for i in range(100):
            yield db_utils.update_user_value(campaign_id="campaign_id",
                                             user_id=i,
                                             payment=i * 10,
                                             human_score=10)

        _iter = yield db_utils.get_user_value_iter("campaign_id")
        user_values = []
        while True:
            user_value_doc = yield _iter.next()
            if user_value_doc is None:
                break
            user_values.append(user_value_doc)
            self.assertEqual(user_value_doc['human_score'], 10)
            self.assertEqual(user_value_doc['payment'], user_value_doc['user_id'] * 10)
        self.assertEqual(len(user_values), 100)

        # Test update user value
        for i in range(100):
            yield db_utils.update_user_value(campaign_id="campaign_id",
                                             user_id=i,
                                             payment=i * 20,
                                             human_score=10)

        # Test get user value.
        for i in range(100):
            user_value_doc = yield db_utils.get_user_value("campaign_id", i)
            self.assertEqual(user_value_doc["payment"], i * 20)
