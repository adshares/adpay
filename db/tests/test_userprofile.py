from twisted.internet import defer

from adpay.db import tests as db_tests
from adpay.db import utils as db_utils


class DBTestCase(db_tests.DBTestCase):

    @defer.inlineCallbacks
    def test_userprofile(self):
        for i in range(100):
            userid = "user_%s" %i
            yield db_utils.update_user_profile(userid, {'keyword1':i*0.05, 'keyword2':i*0.3})

            user_profile_doc = yield db_utils.get_user_profile(userid)
            self.assertEqual(user_profile_doc['profile']['keyword1'], i*0.05)
            self.assertEqual(user_profile_doc['profile']['keyword2'], i*0.3)

            yield db_utils.update_user_profile(userid, {'keyword1':i, 'keyword2':i*2})
            user_profile_doc = yield db_utils.get_user_profile(userid)
            self.assertEqual(user_profile_doc['profile']['keyword1'], i)
            self.assertEqual(user_profile_doc['profile']['keyword2'], i*2)


        yield db_utils.delete_user_profiles()
        for i in range(100):
            userid = "user_%s" %i
            user_profile_doc = yield db_utils.get_user_profile(userid)
            self.assertIsNone(user_profile_doc)
