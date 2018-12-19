import math

from adpay.stats import consts as stats_consts


def timestamp2hour(timestamp):
    """
    Get epoch timestamp of the hour of the `timestamp`.

    :param timestamp: timestamp
    :return: timestamp of the hour (floored)
    """
    return int(math.floor(float(timestamp)/stats_consts.SECONDS_PER_HOUR)*stats_consts.SECONDS_PER_HOUR)


def genkey(key, val, delimiter="_"):
    """
    Generate keyword identifier, ex. {'animal': 'dog'} becomes 'animal_dog'

    :param key: Key
    :param val: Value
    :param delimiter: Delimiter, default "_"
    :return: Generated identifier
    """
    keywal = "%s%s%s" % (key, delimiter, val)
    return keywal.replace(".", "")

