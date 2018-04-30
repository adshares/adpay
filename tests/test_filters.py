import json
from twisted.trial import unittest

from adpay.iface import filters as iface_filters


class FilterTestCase(unittest.TestCase):

    def test_base_filter(self):
        filter_object = iface_filters.Filter(None)
        with self.assertRaises(NotImplementedError):
            filter_object.is_valid(None)

    def test_equal_filter(self):
        filter_object = iface_filters.EqualFilter(True)
        self.assertTrue(filter_object.is_valid(True))

    def test_greater_equal_filter(self):
        filter_object = iface_filters.GreaterEqualFilter(0)
        self.assertTrue(filter_object.is_valid(1))
        self.assertTrue(filter_object.is_valid(0))
        self.assertFalse(filter_object.is_valid(-1))

    def test_less_equal_filter(self):
        filter_object = iface_filters.LessEqualFilter(0)
        self.assertTrue(filter_object.is_valid(0))
        self.assertTrue(filter_object.is_valid(-1))
        self.assertFalse(filter_object.is_valid(1))

    def test_less_filter(self):
        filter_object = iface_filters.LessFilter(0)
        self.assertTrue(filter_object.is_valid(-1))
        self.assertFalse(filter_object.is_valid(0))
        self.assertFalse(filter_object.is_valid(1))

    def test_greater_filter(self):
        filter_object = iface_filters.GreaterFilter(0)
        self.assertTrue(filter_object.is_valid(1))
        self.assertFalse(filter_object.is_valid(0))
        self.assertFalse(filter_object.is_valid(-1))

    def test_and_filter(self):
        and_filter_object = iface_filters.AndFilter([iface_filters.EqualFilter(True),
                                                       iface_filters.EqualFilter(True)])

        self.assertTrue(and_filter_object.is_valid(True))

        and_filter_object = iface_filters.AndFilter([iface_filters.EqualFilter(True),
                                                       iface_filters.EqualFilter(False)])

        self.assertFalse(and_filter_object.is_valid(True))

    def test_or_filter(self):
        or_filter_object = iface_filters.OrFilter([iface_filters.EqualFilter(True),
                                                     iface_filters.EqualFilter(False)])

        self.assertTrue(or_filter_object.is_valid(True))

        or_filter_object = iface_filters.OrFilter([iface_filters.EqualFilter(False),
                                                     iface_filters.EqualFilter(False)])

        self.assertFalse(or_filter_object.is_valid(True))

    def test_json_filter(self):

        json_filter = iface_filters.json2filter(json.loads('{"type": "=<><><="}'))
        self.assertIsNone(json_filter)

        json_filter = iface_filters.json2filter(json.loads('{"type": "="}'))
        self.assertIsNone(json_filter)

        json_filter = iface_filters.json2filter(json.loads('{"type": "=", "args": [1]}'))
        self.assertIsNot(json_filter, None)

        json_filter = iface_filters.json2filter(json.loads('{"type": "and", "args": [{"type": "=", "args": [1]}, '
                                                             '                         {"type": "=", "args": [2]}]} '))
        self.assertIsNot(json_filter, None)
