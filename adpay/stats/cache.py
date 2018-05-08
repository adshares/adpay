from collections import defaultdict

EVENTS_STATS_VIEWS = 0
EVENTS_STATS_KEYWORDS = defaultdict(lambda: int(0))


def reset_keywords_stats():
    """
    Reset keyword stats to default/

    :return:
    """
    global EVENTS_STATS_KEYWORDS
    EVENTS_STATS_KEYWORDS = defaultdict(lambda: int(0))


def keyword_inc(keyword):
    """
    Increment keyword count

    :param keyword: keyword to be incremented
    :return:
    """
    global EVENTS_STATS_VIEWS
    EVENTS_STATS_KEYWORDS[keyword] += 1
    EVENTS_STATS_VIEWS += 1
