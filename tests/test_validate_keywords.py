from adpay.iface.filters import validate_filters
from tests import db_test_case
from copy import deepcopy


class ValidateKeywordsTestCase(db_test_case):

    def test_validate_filters(self):

        filters_dict = {u'exclude': [],
                        u'require': [{u'filter': {u'args': u'mac', u'type': u'='},
                                      u'keyword': u'platform_name'}]}

        keywords = {u'context_lorem': 1, u'locale': u'en_us', u'banner_size': u'160x600',
                    u'context_lipsum': 1, u'device_type': u'desktop', u'zone': u'website.priv/2',
                    u'tid': u'7e_dy_8i1u3gofhjxq9jgzdahwfj7g',
                    u'inframe': u'no', u'screen_width': 1920, u'context_what': 1, u'host': u'website.priv',
                    u'path': u'website.priv/', u'context_facts': 1, u'context_ipsum': 1,
                    u'context_generator': 1, u'context_lorem ipsum': 1, u'context_text': 1,
                    u'browser_name': u'firefox', u'context_information': 1,
                    u'platform_name': u'macosx', u'context_generate': 1, u'screen_height': 1080}

        self.assertFalse(validate_filters(filters_dict, keywords))

        for campaign in self.campaigns:
            filters = deepcopy(campaign['filters'])

            self.assertFalse(validate_filters(filters, filters['require'][0]))
            filters['require'] = []

            self.assertTrue(validate_filters(filters, filters['exclude'][0]))

            self.assertFalse(validate_filters(filters,
                                              {filters['exclude'][0]['keyword']: filters['exclude'][0]['filter']['args']}))
