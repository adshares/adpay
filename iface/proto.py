import jsonobject

class KeywordFilterObject(jsonobject.JsonObject):
    keyword = jsonobject.StringProperty()
    filter = jsonobject.DictProperty()


class RequireExcludeListObject(jsonobject.JsonObject):
    require = jsonobject.ListProperty(KeywordFilterObject)
    exclude = jsonobject.ListProperty(KeywordFilterObject)


class BannerObject(jsonobject.JsonObject):
    banner_id = jsonobject.StringProperty()
    banner_size = jsonobject.StringProperty()
    keywords = jsonobject.DictProperty()


class CamapaignObject(jsonobject.JsonObject):
    campaign_id = jsonobject.StringProperty()
    advertiser_id = jsonobject.StringProperty()
    time_start = jsonobject.IntegerProperty()
    time_end = jsonobject.IntegerProperty()
    filters = jsonobject.ObjectProperty(RequireExcludeListObject)
    keywords = jsonobject.DictProperty()
    banners = jsonobject.ListProperty(BannerObject)
    max_cpc = jsonobject.FloatProperty()        # max cost per click
    max_cpv = jsonobject.FloatProperty()        # max cost per view
    budget = jsonobject.FloatProperty()         # hourly budget


class EventObject(jsonobject.JsonObject):
    event_id = jsonobject.StringProperty()
    event_type = jsonobject.StringProperty()    # define either event is click, view or conversion
    user_id = jsonobject.StringProperty()
    publisher_id = jsonobject.StringProperty()
    timestamp = jsonobject.IntegerProperty()
    banner_id = jsonobject.StringProperty()
    our_keywords = jsonobject.DictProperty()        # adshares keywords
    their_keywords = jsonobject.DictProperty()      # publisher keywords
    paid_amount = jsonobject.FloatProperty()


class PaymentsRequest(jsonobject.JsonObject):
    timestamp = jsonobject.IntegerProperty()


class SinglePaymentResponse(jsonobject.JsonObject):
    event_id = jsonobject.StringProperty()
    amount = jsonobject.FloatProperty()


class PaymentsResponse(jsonobject.JsonObject):
    payments = jsonobject.ListProperty(SinglePaymentResponse)

