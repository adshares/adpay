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
CALCULATE_PAYMENTS_PERIODICALLY = bool(int(os.getenv('ADPAY_CALCULATE_TASKS', 1)))

#: Enable/disable checking if event keywords are appriopriate for campaign filters. 1 for enable, 0 for disable.
#:
#: `Environmental variable override: ADPAY_VALIDATE_CAMPAIGN_FILTERS`
VALIDATE_CAMPAIGN_FILTERS = bool(int(os.getenv('ADPAY_VALIDATE_CAMPAIGN_FILTERS', 1)))

#: Filter out events by users with threshold value and below. This value should be between [0.0, 1.0].
#:
#: `Environmental variable override: ADPAY_HUMAN_SCORE_THRESHOLD`
HUMAN_SCORE_THRESHOLD = float(os.getenv('ADPAY_HUMAN_SCORE_THRESHOLD', 0.0))

#: Click event name
#:
#: `Environmental variable override: ADPAY_EVENT_TYPE_CLICK`
EVENT_TYPE_CLICK = os.getenv('ADPAY_EVENT_TYPE_CLICK', 'click')

#: View/Impression event name
#:
#: `Environmental variable override: ADPAY_EVENT_TYPE_VIEW`
EVENT_TYPE_VIEW = os.getenv('ADPAY_EVENT_TYPE_VIEW', 'view')

#: Conversion event name
#:
#: `Environmental variable override: ADPAY_EVENT_TYPE_CONVERSION`
EVENT_TYPE_CONVERSION = os.getenv('ADPAY_EVENT_TYPE_CONVERSION', 'conversion')

#: AdPay will pay only for these event types.
PAID_EVENT_TYPES = [EVENT_TYPE_CLICK, EVENT_TYPE_VIEW, EVENT_TYPE_CONVERSION]

#: Event is ok, but payment can still be 0.
EVENT_PAYMENT_ACCEPTED = 0

#: Event is rejected, because campaign can't be found.
EVENT_PAYMENT_REJECTED_CAMPAIGN_NOT_FOUND = 1

#: Event is rejected, because human score value is too low, probably a bot.
EVENT_PAYMENT_REJECTED_HUMAN_SCORE_TOO_LOW = 2

#: Event is rejected, because user profile doesn't pass campaign filters.
EVENT_PAYMENT_REJECTED_INVALID_TARGETING = 3

#: Event is rejected, because banner can't be found.
EVENT_PAYMENT_REJECTED_BANNER_NOT_FOUND = 4

#: We don't pay for this event.
EVENT_NOT_PAYABLE = 99
