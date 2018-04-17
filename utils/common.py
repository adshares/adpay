from adpay.stats import consts as stats_consts
import math

def timestamp2hour(timestamp):
    """
    Get epoch timestamp of the hour of the `timestamp`.

    :param timestamp: timestamp
    :return: timestamp of the hour (floored)
    """
    return math.floor(1.0*timestamp/stats_consts.SECONDS_PER_HOUR)*stats_consts.SECONDS_PER_HOUR


def genkey(key, val, delimiter="_"):
    keywal = "%s%s%s" % (key, delimiter, val)
    return keywal.replace(".", "")