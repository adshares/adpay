from unittest import TestCase

from adpay.stats.utils import validate_exclude_keywords, validate_keywords, validate_require_keywords


class TestValidateExcludeKeywords(TestCase):
    def test_validate_exclude_keywords(self):
        passed = validate_exclude_keywords({"exclude": {},
                                            "require": {}},
                                           {})
        self.assertTrue(passed)

        passed = validate_exclude_keywords({"exclude": {"cat1": ["val1"]},
                                            "require": {}},
                                           {"cat1": ["val1"]})
        self.assertFalse(passed)

        passed = validate_exclude_keywords({"exclude": {"cat2": ["val1"]},
                                            "require": {}},
                                           {"cat1": ["val1"]})
        self.assertTrue(passed)

        passed = validate_exclude_keywords({"exclude": {"cat1": ["val2"]},
                                            "require": {}},
                                           {"cat1": ["val1"]})
        self.assertTrue(passed)

        passed = validate_exclude_keywords({"exclude": {"cat1": ["val0--val2"]},
                                            "require": {}},
                                           {"cat1": ["val1"]})
        self.assertFalse(passed)

        passed = validate_exclude_keywords({"exclude": {"cat1": ["val0", "val1", "val2", "val3-4"]},
                                            "require": {}},
                                           {"cat1": ["val1"]})
        self.assertFalse(passed)

        passed = validate_exclude_keywords({"exclude": {"cat1": ["val0", "valx", "val2", "val3-4"]},
                                            "require": {}},
                                           {"cat1": ["val1"]})
        self.assertTrue(passed)


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


class TestValidateRequireKeywords(TestCase):
    def test_validate_require_keywords(self):
        passed = validate_require_keywords({"require": {},
                                            "exclude": {}},
                                           {})
        self.assertTrue(passed)

        passed = validate_require_keywords({"require": {"cat1": ["val1"]},
                                            "exclude": {}},
                                           {"cat1": ["val1"]})
        self.assertTrue(passed)

        passed = validate_require_keywords({"require": {"cat2": ["val1"]},
                                            "exclude": {}},
                                           {"cat1": ["val1"]})
        self.assertFalse(passed)

        passed = validate_require_keywords({"require": {"cat1": ["val2"]},
                                            "exclude": {}},
                                           {"cat1": ["val1"]})
        self.assertFalse(passed)

        passed = validate_require_keywords({"require": {"cat1": ["val0--val2"]},
                                            "exclude": {}},
                                           {"cat1": ["val1"]})
        self.assertTrue(passed)

        passed = validate_require_keywords({"require": {"cat1": ["val0", "val1", "val2", "val3-4"]},
                                            "exclude": {}},
                                           {"cat1": ["val1"]})
        self.assertTrue(passed)

        passed = validate_require_keywords({"require": {"cat1": ["val0", "valx", "val2", "val3-4"]},
                                            "exclude": {}},
                                           {"cat1": ["val1"]})
        self.assertFalse(passed)
