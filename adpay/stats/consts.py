import os

#: Seconds per hour (3600). Development use only.
#:
#: `Environmental variable override: ADPAY_SECONDS_PER_HOUR`
SECONDS_PER_HOUR = int(os.getenv('ADPAY_SECONDS_PER_HOUR', 3600))

#: Max keywords in a user profile to consider for similarity
#:
#: `Environmental variable override: ADPAY_MAX_USER_KEYWORDS_IN_PROFILE`
MAX_USER_KEYWORDS_IN_PROFILE = int(os.getenv('ADPAY_MAX_USER_KEYWORDS_IN_PROFILE', 50))

#: Choose one of two methods of calculation: 'default' and 'user_value'.
#:
#: `Environmental variable override: ADPAY_CALCULATION_METHOD`
CALCULATION_METHOD = os.getenv('ADPAY_CALCULATION_METHOD', 'default')

#: Enable/disable periodical payment calculation. 1 for enable, 0 for disable.
#:
#: `Environmental variable override: ADPAY_CALCULATE_TASKS`
CALCULATE_PAYMENTS_PERIODICALLY = bool(os.getenv('ADPAY_CALCULATE_TASKS', 1))

#: Enable/disable checking if keywords from publisher are the same as ours. 1 for enable, 0 for disable.
#:
#: `Environmental variable override: ADPAY_VALIDATE_EVENT_KEYWORD_EQUALITY`
VALIDATE_EVENT_KEYWORD_EQUALITY = bool(os.getenv('ADPAY_VALIDATE_EVENT_KEYWORD_EQUALITY', 1))

#: Enable/disable checking if event keywords are appriopriate for campaign filters. 1 for enable, 0 for disable.
#:
#: `Environmental variable override: ADPAY_VALIDATE_CAMPAIGN_FILTERS`
VALIDATE_CAMPAIGN_FILTERS = bool(os.getenv('ADPAY_VALIDATE_CAMPAIGN_FILTERS', 1))

#: Filter out events by users with threshold value and below. This value should be between [0.0, 1.0].
#:
#: `Environmental variable override: ADPAY_HUMAN_SCORE_THRESHOLD`
HUMAN_SCORE_THRESHOLD = float(os.getenv('ADPAY_HUMAN_SCORE_THRESHOLD', 0.0))
