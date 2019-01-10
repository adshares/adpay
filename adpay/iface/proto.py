import jsonobject


class RequireExcludeObject(jsonobject.JsonObject):
    """
    .. json:object:: RequireExcludeListObject
       :showexample:

       Require and exclude attributes are dictionary/JSON Object, where each key is a list/JSON Array of values. The values are all strings, but they can define a range, by adding a special delimiter (default: '--').

       Examples of valid key-value pairs:

       * "age": ["18--30"]
       * "interest": ["cars"]
       * "movies": ["action", "horror", "thriller"]

       :property DictProperty exclude: Dictionary of excluded keywords
       :property DictProperty require: Dictionary of required keywords

    """
    exclude = jsonobject.DictProperty()
    """Dictionary of excluded keywords"""

    require = jsonobject.DictProperty()
    """Dictionary of required keywords"""


class BannerObject(jsonobject.JsonObject):
    """
    .. json:object:: BannerObject
       :showexample:

       :property string banner_id: Unique banner identifier
       :property string banner_size: Banner size, eg. 100x400
       :propexample banner_size: 100x400
       :property JSONObject keywords: Key-value map of keywords

    """
    banner_id = jsonobject.StringProperty(required=True)
    """Main banner identifier (String)."""

    banner_size = jsonobject.StringProperty(required=True)
    """Banner size, in pixels, width x height (String)."""

    keywords = jsonobject.DictProperty()
    """Keywords (Dictionary of Strings)."""


class CampaignObject(jsonobject.JsonObject):
    """
    .. json:object:: CampaignObject
       :showexample:

       :property string advertiser_id: Unique advertiser identifier
       :propexample advertiser_id: QdDOdNbCtxdAXfmBKBds
       :property [BannerObject] banners: List of banner objects
       :property float budget: Hourly budget
       :propexample budget: 0.75
       :property string campaign_id: Unique campaign identifier
       :propexample campaign_id: BXfmBKBdsQdDOdNbCtxd
       :property RequireExcludeObject filters: Filters for campaign
       :property JSONObject keywords: Key-value map of keywords
       :property float max_cpc: Max cost per click
       :propexample max_cpc: 0.010
       :property float max_cpm: Max cost per view
       :propexample max_cpm: 0.005
       :property integer time_end: Campaign end time (epoch time, in seconds)
       :propexample time_end: 1643326642
       :property integer time_start: Campaign start time (epoch time, in seconds)
       :propexample time_start: 1543326642


    """
    advertiser_id = jsonobject.StringProperty()
    banners = jsonobject.ListProperty(BannerObject)
    budget = jsonobject.IntegerProperty()  # hourly budget
    campaign_id = jsonobject.StringProperty(required=True)
    filters = jsonobject.ObjectProperty(RequireExcludeObject, required=True)
    keywords = jsonobject.DictProperty()
    max_cpc = jsonobject.IntegerProperty()  # max cost per click
    max_cpm = jsonobject.IntegerProperty()  # max cost per view
    time_end = jsonobject.IntegerProperty(required=True)
    time_start = jsonobject.IntegerProperty(required=True)


class EventObject(jsonobject.JsonObject):
    """
    .. json:object:: EventObject
       :showexample:

       :property string banner_id: Unique banner identifier
       :property string case_id: Unique case identifier for a set of events
       :property string event_id: Unique event identifier
       :property string event_type: Event type: click, view or conversion
       :property float event_value: Custom value for event
       :propexample event_value: 0.50
       :property float human_score: Human score (0.0 for bot to 1.0 for human)
       :propexample human_score: 1.0
       :property JSONObject our_keywords: Key-value map of keywords
       :property string publisher_id: Unique publisher identifier
       :property integer timestamp: Event time (epoch time, in seconds)
       :propexample timestamp: 1543326642
       :property JSONObject their_keywords: Key-value map of keywords

    """
    banner_id = jsonobject.StringProperty(required=True)
    case_id = jsonobject.StringProperty()
    event_id = jsonobject.StringProperty(required=True)
    event_type = jsonobject.StringProperty(required=True)  # define either event is click, view or conversion
    event_value = jsonobject.IntegerProperty()
    human_score = jsonobject.FloatProperty()  # determine if user is bot (value = 0) or human (value = 1)
    our_keywords = jsonobject.DictProperty()  # adshares keywords
    publisher_id = jsonobject.StringProperty()
    timestamp = jsonobject.IntegerProperty(required=True)
    their_keywords = jsonobject.DictProperty()  # publisher keywords
    user_id = jsonobject.StringProperty()


class PaymentsRequest(jsonobject.JsonObject):
    """
    .. json:object:: PaymentsRequest
       :showexample:

       :property integer timestamp: Timestamp in epoch seconds
       :propexample timestamp: 1544454407

    """
    timestamp = jsonobject.IntegerProperty(required=True)
    """Timestamp of payment request"""


class SinglePaymentResponse(jsonobject.JsonObject):
    """
    .. json:object:: SinglePaymentResponse
       :showexample:

       :property float amount: Amount to be paid for event
       :propexample amount: 0.15498
       :property string event_id: Unique event identifier
       :property integer reason: Reason for payment rejection. This will correlate with amount, ie. `amount` > 0 when `reason` == 0.
       :propexample reason: 0

    """
    amount = jsonobject.IntegerProperty(required=True)
    """Amount to be paid for that event"""

    event_id = jsonobject.StringProperty(required=True)
    """Event identifier"""

    reason = jsonobject.IntegerProperty(required=True)
    """Payment rejection reason"""


class PaymentsResponse(jsonobject.JsonObject):
    """
    .. json:object:: PaymentsResponse
       :showexample:

       :property [SinglePaymentResponse] payments: List of payments for individual events

    """
    payments = jsonobject.ListProperty(SinglePaymentResponse)
    """List of payments for individual events"""
