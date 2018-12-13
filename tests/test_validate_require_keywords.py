from unittest import TestCase

from adpay.iface.utils import validate_require_keywords


class TestValidate_require_keywords(TestCase):
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
