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

       :property DictProperty require: Dicitonary of required keywords
       :property DictProperty exclude: Dictionary of excluded keywords

    """

    require = jsonobject.DictProperty()
    """Dictionary of required keywords"""

    exclude = jsonobject.DictProperty()
    """Dictionary of excluded keywords"""


class BannerObject(jsonobject.JsonObject):
    """
    .. json:object:: BannerObject
       :showexample:

       :property string banner_id: Unique banner identifier
       :property JSONObject keywords: Key-value map of keywords
       :property string banner_size: Banner size, eg. 100x400
       :propexample banner_size: 100x400

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

       :property string campaign_id: Unique campaign identifier
       :propexample campaign_id: BXfmBKBdsQdDOdNbCtxd
       :property string advertiser_id: Unique advertiser identifier
       :propexample advertiser_id: QdDOdNbCtxdAXfmBKBds
       :property integer time_start: Campaign start time (epoch time, in seconds)
       :propexample time_start: 1543326642
       :property integer time_end: Campaign end time (epoch time, in seconds)
       :propexample time_end: 1643326642
       :property RequireExcludeObject filters: Filters for campaign
       :property JSONObject keywords: Key-value map of keywords
       :property [BannerObject] banners: List of banner objects
       :property float max_cpc: Max cost per click
       :propexample max_cpc: 0.010
       :property float max_cpm: Max cost per view
       :propexample max_cpm: 0.005
       :property float budget: Hourly budget
       :propexample budget: 0.75

    """
    campaign_id = jsonobject.StringProperty(required=True)
    advertiser_id = jsonobject.StringProperty()
    time_start = jsonobject.IntegerProperty(required=True)
    time_end = jsonobject.IntegerProperty(required=True)
    filters = jsonobject.ObjectProperty(RequireExcludeObject, required=True)
    keywords = jsonobject.DictProperty()
    banners = jsonobject.ListProperty(BannerObject, required=True)
    max_cpc = jsonobject.FloatProperty()  # max cost per click
    max_cpm = jsonobject.FloatProperty()  # max cost per view
    budget = jsonobject.FloatProperty()  # hourly budget


class EventObject(jsonobject.JsonObject):
    """
    .. json:object:: EventObject
       :showexample:

       :property string event_id: Unique event identifier
       :property string event_type: Event type: click, view or conversion
       :property float human_score: Human score (0.0 for bot to 1.0 for human)
       :propexample human_score: 1.0
       :property string publisher_id: Unique publisher identifier
       :property integer timestamp: Event time (epoch time, in seconds)
       :propexample timestamp: 1543326642
       :property string banner_id: Unique banner identifier
       :property JSONObject our_keywords: Key-value map of keywords
       :property JSONObject their_keywords: Key-value map of keywords
       :property float event_value: Custom value for event
       :propexample event_value: 0.50

    """
    event_id = jsonobject.StringProperty(required=True)
    event_type = jsonobject.StringProperty(required=True)  # define either event is click, view or conversion
    user_id = jsonobject.StringProperty()
    human_score = jsonobject.FloatProperty()  # determine if user is bot (value = 0) or human (value = 1)
    publisher_id = jsonobject.StringProperty()
    timestamp = jsonobject.IntegerProperty(required=True)
    banner_id = jsonobject.StringProperty()
    our_keywords = jsonobject.DictProperty()  # adshares keywords
    their_keywords = jsonobject.DictProperty()  # publisher keywords
    event_value = jsonobject.FloatProperty()


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

       :property string event_id: Unique event identifier
       :property float amount: Amount to be paid for event
       :propexample amount: 0.15498

    """
    event_id = jsonobject.StringProperty(required=True)
    """Event identifier"""

    amount = jsonobject.FloatProperty(required=True)
    """Amount to be paid for that event"""


class PaymentsResponse(jsonobject.JsonObject):
    """
    .. json:object:: PaymentsResponse
       :showexample:

       :property [SinglePaymentResponse] payments: List of payments for individual events

    """
    payments = jsonobject.ListProperty(SinglePaymentResponse, required=True)
    """List of payments for individual events"""
