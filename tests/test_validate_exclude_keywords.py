from unittest import TestCase

from adpay.iface.utils import validate_exclude_keywords


class TestValidate_exclude_keywords(TestCase):
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
