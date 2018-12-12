from unittest import TestCase

from adpay.iface.utils import validate_keywords


class ValidateKeywordsTestCase(TestCase):

    def test_validate_keywords(self):

        passed = validate_keywords({"require": {"cat1": ["val1"]},
                                    "exclude": {"cat1": ["val2"]}},
                                   {"cat1": ["val1"]})
        self.assertTrue(passed)

        passed = validate_keywords({"require": {"cat3": ["val1"]},
                                    "exclude": {"cat1": ["val2"]}},
                                   {"cat1": ["val1"]})
        self.assertFalse(passed)

        passed = validate_keywords({"require": {"cat1": ["val1"]},
                                    "exclude": {"cat1": ["val1"]}},
                                   {"cat1": ["val1"]})
        self.assertFalse(passed)
