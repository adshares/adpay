import re
import time
from unittest import TestCase

from adpay.stats import legacy as stats_legacy
from adpay.utils import utils as common_utils


class TestUtils(TestCase):

    def test_genkey(self):
        # Test for no '.'
        generated_key = common_utils.genkey('key', 'value')
        self.assertFalse(re.search(r'\.', generated_key))

        generated_key = common_utils.genkey('key', '...value..')
        self.assertFalse(re.search(r'\.', generated_key))

    def test_timestamp2hour(self):
        ts = common_utils.timestamp2hour(time.time())
        self.assertIs(type(ts), int)

    def test_reverse_insort(self):
        with self.assertRaises(ValueError):
            stats_legacy.reverse_insort(0, range(20), -5)
