from twisted.internet import defer

from adpay.iface.tests import IfaceTestCase
from adpay.db import utils as db_utils

import copy

class InterfaceCampaignTestCase(IfaceTestCase):
    CAMAPAIGN_DATA = {
        'campaign_id':'campaign_id',
        'advertiser_id':'advertiser_id',
        'time_start':12345,
        'time_end':34567,
        'filters':{},
        'keywords':{},
        'banners':[
            {
                'banner_id':'banner1',
                'banner_size':'100x200',
                'keywords':{}
            },
            {
                'banner_id': 'banner2',
                'banner_size': '150x250',
                'keywords':{}
            }
        ],
        'max_cpc':10,
        'max_cpv':15,
        'budget':1000
    }

    @defer.inlineCallbacks
    def test_add_campaign(self):
        response = yield self.get_response("campaign_update", [self.CAMAPAIGN_DATA])

        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        campaign_doc = yield db_utils.get_campaign(self.CAMAPAIGN_DATA['campaign_id'])
        self.assertIsNotNone(campaign_doc)
        self.assertEqual(campaign_doc['campaign_id'], self.CAMAPAIGN_DATA['campaign_id'])
        self.assertEqual(campaign_doc['budget'], 1000)

        campaign_banners = yield db_utils.get_campaign_banners(self.CAMAPAIGN_DATA['campaign_id'])
        self.assertEqual(len(campaign_banners), 2)
        for banner_doc in campaign_banners:
            self.assertIn(banner_doc['banner_id'], [elem['banner_id'] for elem in self.CAMAPAIGN_DATA['banners']])


    @defer.inlineCallbacks
    def test_update_campaign(self):
        CHANGED_CAMPAIGN_DATA = copy.deepcopy(self.CAMAPAIGN_DATA)
        CHANGED_CAMPAIGN_DATA['budget'] = 200
        CHANGED_CAMPAIGN_DATA['banners'] = [
            {
                'banner_id':'changed',
                'banner_size':'100x200',
                'keywords':{}
            }
        ]

        response = yield self.get_response("campaign_update", [CHANGED_CAMPAIGN_DATA])
        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        campaign_doc = yield db_utils.get_campaign(CHANGED_CAMPAIGN_DATA['campaign_id'])
        self.assertIsNotNone(campaign_doc)
        self.assertEqual(campaign_doc['campaign_id'], CHANGED_CAMPAIGN_DATA['campaign_id'])
        self.assertEqual(campaign_doc['budget'], CHANGED_CAMPAIGN_DATA['budget'])

        campaign_banners = yield db_utils.get_campaign_banners(CHANGED_CAMPAIGN_DATA['campaign_id'])

        self.assertEqual(len(campaign_banners), 1)
        self.assertEqual(campaign_banners[0]['banner_id'], CHANGED_CAMPAIGN_DATA['banners'][0]['banner_id'])


    @defer.inlineCallbacks
    def test_delete_campaign(self):
        response = yield self.get_response("campaign_delete", [self.CAMAPAIGN_DATA['campaign_id']])

        self.assertIsNotNone(response)
        self.assertTrue(response['result'])

        campaign_doc = yield db_utils.get_campaign(self.CAMAPAIGN_DATA['campaign_id'])
        self.assertIsNone(campaign_doc)

        campaign_banners = yield db_utils.get_campaign_banners(self.CAMAPAIGN_DATA['campaign_id'])
        self.assertEqual(len(campaign_banners), 0)
