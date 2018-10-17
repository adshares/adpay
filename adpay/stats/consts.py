import os
from dotenv import load_dotenv

load_dotenv()

#: Seconds per hour (3600)
SECONDS_PER_HOUR = int(os.getenv('ADPAY_SECONDS_PER_HOUR'))

#: Max keywords in a user profile
MAX_USER_KEYWORDS_IN_PROFILE = int(os.getenv('ADPAY_MAX_USER_KEYWORDS_IN_PROFILE'))
