from collections import defaultdict

EVENTS_STATS_KEYWORDS = defaultdict(lambda: int(0))


def reset_keywords_stats():
    global EVENTS_STATS_KEYWORDS
    EVENTS_STATS_KEYWORDS = defaultdict(lambda: int(0))


def get_keyword_stats_iter():
    return EVENTS_STATS_KEYWORDS.iteritems()


def keyword_inc(keyword):
    global EVENTS_STATS_VIEWS
    EVENTS_STATS_KEYWORDS[keyword] += 1
    EVENTS_STATS_VIEWS += 1


EVENTS_STATS_VIEWS = 0


def reset_views_stats():
    global EVENTS_STATS_VIEWS
    EVENTS_STATS_VIEWS = 0


def get_views_stats():
    return EVENTS_STATS_VIEWS


def views_inc():
    global EVENTS_STATS_VIEWS
    EVENTS_STATS_VIEWS += 1
