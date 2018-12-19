from twisted.internet import defer

import tests
from adpay.db import utils as db_utils


class DBTestCase(tests.db_test_case):
    @defer.inlineCallbacks
    def test_campaign(self):
        cmp_doc = {"campaign_id": "campaign_id",
                   "time_start": 12345,
                   "time_end": 12347,
                   "max_cpc": 100,
                   "max_cpm": 200,
                   "budget": 1000,
                   "filters": {}}
        yield db_utils.update_campaign(cmp_doc)

        campaign_doc = yield db_utils.get_campaign("campaign_id")
        self.assertEqual(campaign_doc['time_start'], 12345)
        self.assertEqual(campaign_doc['time_end'], 12347)
        self.assertEqual(campaign_doc['max_cpc'], 100)
        self.assertEqual(campaign_doc['max_cpm'], 200)
        self.assertEqual(campaign_doc['budget'], 1000)

        yield db_utils.delete_campaign("campaign_id")
        campaign_doc = yield db_utils.get_campaign("campaign_id")
        self.assertEqual(None, campaign_doc)

        for i in range(1000):
            cmp_doc = {"campaign_id": i,
                       "time_start": 12345,
                       "time_end": 12347,
                       "max_cpc": 100,
                       "max_cpm": 200,
                       "budget": 1000,
                       "filters": {}}
            yield db_utils.update_campaign(cmp_doc)

        counter = 0
        campaign_iter = yield db_utils.get_campaign_iter()
        while True:
            campaign_doc = yield campaign_iter.next()
            if not campaign_doc:
                break
            counter += 1
        self.assertEqual(counter, 1000)
