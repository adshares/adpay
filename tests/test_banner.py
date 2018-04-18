from twisted.internet import defer

from adpay import tests as db_tests
from adpay import utils as db_utils


class DBTestCase(db_tests.DBTestCase):
    @defer.inlineCallbacks
    def test_banner(self):
        yield db_utils.update_banner("banner_id1", "campaign_id")
        yield db_utils.update_banner("banner_id2", "campaign_id")
        yield db_utils.update_banner("banner_id3", "campaign_id")

        banner1_doc = yield db_utils.get_banner("banner_id1")
        self.assertEqual(banner1_doc['banner_id'], "banner_id1")
        self.assertEqual(banner1_doc['campaign_id'], "campaign_id")

        banner2_doc = yield db_utils.get_banner("banner_id2")
        self.assertEqual(banner2_doc['banner_id'], "banner_id2")
        self.assertEqual(banner2_doc['campaign_id'], "campaign_id")

        banner_iter = yield db_utils.get_banners_iter()
        while True:
            banner_doc = yield banner_iter.next()
            if not banner_doc:
                break

            self.assertIn(banner_doc['banner_id'], ['banner_id1', 'banner_id2', 'banner_id3'])
            self.assertEqual(banner_doc['campaign_id'], "campaign_id")

        yield db_utils.delete_campaign_banners('campaign_id')

        counter = 0
        banner_iter = yield db_utils.get_banners_iter()
        while True:
            banner_doc = yield banner_iter.next()
            if not banner_doc:
                break
            counter += 1
        self.assertEqual(counter, 0)

        banner3_doc = yield db_utils.get_banner("banner_id3")
        self.assertEqual(None, banner3_doc)
