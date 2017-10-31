import random


def get_user_credibility(user_id):
    """
        Return user credibility value [0, 1] from aduser.
        The value determines whether user is the real user or the bot.
    """
    return random.random()


def get_users_similarity(user1_id, user2_id):
    """
        Return user similarity value [0, 1] from aduser.
        1 - user1 is recognised as user2
        0 - user1 and user2 are completely different.
    """
    return random.random()


def reverse_insort(a, x, lo=0, hi=None):
    """Insert item x in list a, and keep it reverse-sorted assuming a
    is reverse-sorted.

    If x is already in a, insert it to the right of the rightmost x.

    Optional args lo (default 0) and hi (default len(a)) bound the
    slice of a to be searched.
    """
    if lo < 0:
        raise ValueError('lo must be non-negative')
    if hi is None:
        hi = len(a)
    while lo < hi:
        mid = (lo+hi)//2
        if x > a[mid]: hi = mid
        else: lo = mid+1
    a.insert(lo, x)
