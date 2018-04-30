from unittest import TestCase
import re
from adpay.utils import utils as common_utils
from adpay.stats import utils as stats_utils
import time


class TestGenkey(TestCase):

    def test_genkey(self):
        # Test for no '.'
        generated_key = common_utils.genkey('key', 'value')
        self.assertFalse(re.search('\.', generated_key))

        generated_key = common_utils.genkey('key', '...value..')
        self.assertFalse(re.search('\.', generated_key))

    def test_timestamp2hour(self):
        ts = common_utils.timestamp2hour(time.time())
        self.assertIs(type(ts), int)

    def test_reverse_insort(self):
        with self.assertRaises(ValueError)as e:
            stats_utils.reverse_insort(0, range(20), -5)
